#!/usr/bin/env bash
set -Eeuo pipefail
trap 'echo "[ERR] line:$LINENO"; exit 1' ERR

REPO=${REPO:-sombathoudom/cms-project}
RATE_LIMIT_SLEEP=${RATE_LIMIT_SLEEP:-2}
MAX_RETRY=${MAX_RETRY:-5}
CSV_FILE="issues.csv"
LEDGER_FILE="issue-tracing.md"

export REPO
export CSV_FILE
export LEDGER_FILE

usage() {
  cat <<'EOF'
Usage: gh_issues.sh [--try-run|--execute|--verify]
EOF
}

if [[ $# -ne 1 ]]; then
  usage
  exit 1
fi

MODE="$1"
if [[ "$MODE" != "--try-run" && "$MODE" != "--execute" && "$MODE" != "--verify" ]]; then
  usage
  exit 1
fi

log() {
  printf '[INFO] %s\n' "$*"
}

warn() {
  printf '[WARN] %s\n' "$*"
}

err() {
  printf '[ERR] %s\n' "$*" >&2
}

require_gh() {
  if ! command -v gh >/dev/null 2>&1; then
    warn "GitHub CLI not found. Attempting installation..."
    if command -v apt-get >/dev/null 2>&1; then
      apt-get update -y >/dev/null
      apt-get install -y gh >/dev/null
    else
      err "Automatic installation of GitHub CLI is not supported on this system."
      err "Please install GitHub CLI manually and re-run."
      exit 1
    fi
  fi
  gh --version >/dev/null
}

check_repo() {
  local remote_url
  if git remote get-url origin >/dev/null 2>&1; then
    remote_url=$(git remote get-url origin)
    log "Detected git remote: ${remote_url}"
  else
    if [[ "$REPO" == "<YOUR/REPO>" ]]; then
      err "⚠️ No remote repository configured. Please set REPO=<owner>/<repo> and re-run."
      exit 1
    fi
    warn "Git remote not set. Using REPO environment variable: ${REPO}"
  fi
  if [[ "$REPO" == "<YOUR/REPO>" ]]; then
    err "REPO variable must be set to <owner>/<repo>."
    exit 1
  fi
  if ! gh repo view "$REPO" >/dev/null 2>&1; then
    err "Unable to access repository ${REPO}. Please verify permissions and try again."
    exit 1
  fi
}

ensure_auth() {
  if ! gh auth status --hostname github.com >/dev/null 2>&1; then
    warn "GitHub CLI is not authenticated. Starting device flow..."
    local auth_output
    if ! auth_output=$(timeout 5s gh auth login --hostname github.com --git-protocol https --scopes repo --web 2>&1); then
      err "Authentication via gh auth login did not complete. Please run 'gh auth login --hostname github.com --git-protocol https --scopes repo --web' manually and re-run."
      printf '%s\n' "$auth_output" >&2
      exit 1
    fi
    printf '%s\n' "$auth_output"
  fi
  gh auth status --hostname github.com >/dev/null
}

lint_csv() {
  python - <<'PY'
import csv
import os
import re
from collections import Counter

path = os.environ['CSV_FILE']
if not os.path.exists(path):
    raise SystemExit("issues.csv not found")

required_headings = [
    "### Summary",
    "### Scope",
    "### Acceptance Criteria",
    "### Notes",
]

ids = Counter()
titles = Counter()
seen_ids = set()
seen_titles = set()

with open(path, encoding='utf-8') as fh:
    reader = csv.DictReader(fh)
    required_fields = ['id', 'title', 'body', 'labels', 'milestone']
    if reader.fieldnames != required_fields:
        raise SystemExit('CSV header mismatch')
    for idx, row in enumerate(reader, start=2):
        for field in required_fields:
            if not row[field].strip():
                raise SystemExit(f'Row {idx}: field "{field}" is empty')
        id_value = row['id']
        title = row['title']
        body = row['body']
        labels = row['labels']
        milestone = row['milestone']
        if not re.fullmatch(r'E\d+-F\d+-I\d+', id_value):
            raise SystemExit(f'Row {idx}: invalid id format {id_value}')
        if not title.startswith(f"{id_value}: "):
            raise SystemExit(f'Row {idx}: title must start with "{id_value}: "')
        for heading in required_headings:
            if heading not in body:
                raise SystemExit(f'Row {idx}: missing heading {heading!r}')
        if any(line.strip() == '' for line in body.splitlines() if line.strip() == ''):
            pass
        label_parts = labels.split(';')
        if len(label_parts) != 4:
            raise SystemExit(f'Row {idx}: labels must contain exactly 4 entries')
        expected_prefixes = {'epic', 'type', 'priority', 'area'}
        seen_prefixes = set()
        for part in label_parts:
            if ':' not in part:
                raise SystemExit(f'Row {idx}: invalid label format {part}')
            prefix, value = part.split(':', 1)
            if prefix not in expected_prefixes:
                raise SystemExit(f'Row {idx}: unexpected label prefix {prefix}')
            if prefix in seen_prefixes:
                raise SystemExit(f'Row {idx}: duplicate label prefix {prefix}')
            seen_prefixes.add(prefix)
            if prefix == 'priority' and value not in {'P0', 'P1', 'P2', 'P3'}:
                raise SystemExit(f'Row {idx}: invalid priority {value}')
            if prefix != 'priority' and not re.fullmatch(r'[A-Z][A-Za-z0-9]*', value):
                raise SystemExit(f'Row {idx}: label value for {prefix} must be CamelCase, got {value}')
        if milestone not in {'Week 1', 'Week 2', 'Week 3', 'Week 4'}:
            raise SystemExit(f'Row {idx}: invalid milestone {milestone}')
        ids[id_value] += 1
        titles[title] += 1

for id_value, count in ids.items():
    if count > 1:
        raise SystemExit(f'Duplicate id detected: {id_value}')
for title, count in titles.items():
    if count > 1:
        raise SystemExit(f'Duplicate title detected: {title}')

print('CSV lint: PASS')
PY
}

run_with_retry() {
  local attempt=1
  local delay=$RATE_LIMIT_SLEEP
  while true; do
    if "$@"; then
      return 0
    fi
    if (( attempt >= MAX_RETRY )); then
      return 1
    fi
    sleep "$delay"
    delay=$(( delay * 2 ))
    attempt=$(( attempt + 1 ))
  done
}

fetch_issues_json() {
  python - <<'PY'
import csv
import base64
import json
import os
import sys

path = os.environ['CSV_FILE']

try:
    with open(path, encoding='utf-8') as fh:
        reader = csv.DictReader(fh)
        for row in reader:
            body_b64 = base64.b64encode(row['body'].encode('utf-8')).decode('ascii')
            payload = {
                'id': row['id'],
                'title': row['title'],
                'body_b64': body_b64,
                'labels': row['labels'].split(';'),
                'milestone': row['milestone'],
            }
            print(json.dumps(payload))
except BrokenPipeError:
    pass
PY
}

load_ledger_ids() {
  if [[ -f "$LEDGER_FILE" ]]; then
    awk -F'|' 'NR>2 {gsub(/^ +| +$/,"", $2); print $2}' "$LEDGER_FILE" | tail -n +1 || true
  fi
}

ensure_labels() {
  local label_json existing
  mapfile -t existing < <(run_with_retry gh label list --repo "$REPO" --limit 200 --json name --jq '.[].name' 2>/dev/null || true)
  if [[ ${#existing[@]} -eq 0 ]]; then
    warn "Could not fetch existing labels or none exist yet."
  fi
  local required_labels
  mapfile -t required_labels < <(python - <<'PY'
import csv
import os
labels = set()
with open(os.environ['CSV_FILE'], encoding='utf-8') as fh:
    reader = csv.DictReader(fh)
    for row in reader:
        labels.update(row['labels'].split(';'))
for label in sorted(labels):
    print(label)
PY
)
  for label in "${required_labels[@]}"; do
    local found=false
    for existing_label in "${existing[@]}"; do
      if [[ "$label" == "$existing_label" ]]; then
        found=true
        break
      fi
    done
    if [[ "$found" == false ]]; then
      log "Creating missing label: $label"
      run_with_retry gh label create "$label" --repo "$REPO" >/dev/null 2>&1 || warn "Label $label may already exist or failed to create"
    fi
  done
}

ensure_milestones() {
  mapfile -t existing < <(run_with_retry gh api repos/"$REPO"/milestones --paginate --jq '.[].title' 2>/dev/null || true)
  for milestone in "Week 1" "Week 2" "Week 3" "Week 4"; do
    local found=false
    for existing_milestone in "${existing[@]}"; do
      if [[ "$milestone" == "$existing_milestone" ]]; then
        found=true
        break
      fi
    done
    if [[ "$found" == false ]]; then
      log "Creating milestone: $milestone"
      run_with_retry gh api repos/"$REPO"/milestones -f title="$milestone" >/dev/null 2>&1 || warn "Milestone $milestone may already exist or failed to create"
    fi
  done
}

get_existing_issue_by_title() {
  local title="$1"
  local result
  result=$(run_with_retry gh issue list --repo "$REPO" --state all --search "\"$title\" in:title" --json number,title,state --limit 1 --jq 'if length > 0 then .[0] else empty end' 2>/dev/null || true)
  if [[ -n "$result" ]]; then
    printf '%s\n' "$result"
    return 0
  fi
  return 1
}

append_to_ledger() {
  local id="$1" title="$2" labels="$3" milestone="$4" number="$5" state="$6" timestamp
  timestamp=$(date -u +'%Y-%m-%dT%H:%M:%SZ')
  if grep -F "| $id |" "$LEDGER_FILE" >/dev/null 2>&1; then
    return
  fi
  printf '| %s | %s | %s | %s | %s | %s | %s |\n' "$id" "$title" "$labels" "$milestone" "$number" "$state" "$timestamp" >> "$LEDGER_FILE"
}

perform_try_run() {
  local ledger_ids existing_titles
  mapfile -t ledger_ids < <(load_ledger_ids)
  log "Ledger contains ${#ledger_ids[@]} entries"
  local created=0 skipped=0
  while IFS= read -r line; do
    [[ -z "$line" ]] && continue
    local id title body_b64 labels_json milestone
    id=$(python -c 'import json,sys; data=json.loads(sys.argv[1]); print(data["id"])' "$line")
    title=$(python -c 'import json,sys; data=json.loads(sys.argv[1]); print(data["title"])' "$line")
    labels_json=$(python -c 'import json,sys; data=json.loads(sys.argv[1]); print(";".join(data["labels"]))' "$line")
    milestone=$(python -c 'import json,sys; data=json.loads(sys.argv[1]); print(data["milestone"])' "$line")
    local skip_reason=""
    for existing_id in "${ledger_ids[@]}"; do
      if [[ "$existing_id" == "$id" ]]; then
        skip_reason="ledger"
        break
      fi
    done
    if [[ -z "$skip_reason" ]]; then
      if get_existing_issue_by_title "$title" >/dev/null; then
        skip_reason="github"
      fi
    fi
    if [[ -n "$skip_reason" ]]; then
      ((skipped+=1))
      log "SKIP [$skip_reason]: $title"
    else
      ((created+=1))
      log "CREATE: $title [labels: $labels_json | milestone: $milestone]"
    fi
  done < <(fetch_issues_json)
  log "Summary: To Create=$created, Skipped=$skipped"
}

perform_execute() {
  local ledger_ids
  mapfile -t ledger_ids < <(load_ledger_ids)
  local created=0 skipped=0 failed=0
  while IFS= read -r line; do
    [[ -z "$line" ]] && continue
    local id title body labels milestone body_decoded
    id=$(python -c 'import json,sys; data=json.loads(sys.argv[1]); print(data["id"])' "$line")
    title=$(python -c 'import json,sys; data=json.loads(sys.argv[1]); print(data["title"])' "$line")
    labels=$(python -c 'import json,sys; data=json.loads(sys.argv[1]); print(";".join(data["labels"]))' "$line")
    milestone=$(python -c 'import json,sys; data=json.loads(sys.argv[1]); print(data["milestone"])' "$line")
    body_decoded=$(python -c 'import json,sys,base64; data=json.loads(sys.argv[1]); print(base64.b64decode(data["body_b64"]).decode("utf-8"))' "$line")

    local already=false
    for existing_id in "${ledger_ids[@]}"; do
      if [[ "$existing_id" == "$id" ]]; then
        already=true
        break
      fi
    done
    if [[ "$already" == true ]]; then
      ((skipped+=1))
      log "SKIP [ledger]: $title"
      continue
    fi
    if get_existing_issue_by_title "$title" >/dev/null; then
      ((skipped+=1))
      log "SKIP [github]: $title"
      continue
    fi

    IFS=';' read -r -a label_array <<< "$labels"
    local label_args=()
    for lbl in "${label_array[@]}"; do
      label_args+=(--label "$lbl")
    done
    local tmpfile
    tmpfile=$(mktemp)
    printf '%s' "$body_decoded" > "$tmpfile"
    if output=$(run_with_retry gh issue create --repo "$REPO" --title "$title" --milestone "$milestone" "${label_args[@]}" --body-file "$tmpfile" 2>&1); then
      local number state issue_info
      number=$(python - "$output" <<'PY'
import re
import sys

text = sys.argv[1]
matches = re.findall(r'issues/(\d+)', text)
if matches:
    print(matches[-1])
PY
)
      state="OPEN"
      if [[ -z "$number" ]]; then
        issue_info=$(get_existing_issue_by_title "$title" || true)
        if [[ -n "$issue_info" ]]; then
          number=$(python -c 'import json,sys; data=json.loads(sys.stdin.read()); print(data.get("number",""))' <<<"$issue_info")
          state=$(python -c 'import json,sys; data=json.loads(sys.stdin.read()); print(data.get("state",""))' <<<"$issue_info")
        else
          number="?"
          state="OPEN"
        fi
      fi
      append_to_ledger "$id" "$title" "$labels" "$milestone" "${number:-?}" "${state:-OPEN}"
      ((created+=1))
      log "CREATED: $title (#${number:-?})"
    else
      ((failed+=1))
      err "FAILED: $title"
      printf '%s\n' "$output" >&2
    fi
    rm -f "$tmpfile"
  done < <(fetch_issues_json)
  log "Execute summary: Created=$created, Skipped=$skipped, Failed=$failed"
  if (( failed > 0 )); then
    exit 1
  fi
}

perform_verify() {
  python - <<'PY'
import os

ledger_path = os.environ['LEDGER_FILE']
if not os.path.exists(ledger_path):
    raise SystemExit('issue-tracing.md missing')

print('| ID   | Title | Labels | Milestone | Issue # | State |')
print('|------|-------|--------|-----------|---------|-------|')
with open(ledger_path, encoding='utf-8') as fh:
    for line in fh:
        line = line.rstrip('\n')
        if line.startswith('|') and not line.startswith('|----') and 'ID' not in line:
            print(line)
PY
}

if [[ "$MODE" == "--verify" ]]; then
  perform_verify
  exit 0
fi

require_gh
check_repo
ensure_auth
lint_csv
ensure_labels
ensure_milestones

case "$MODE" in
  --try-run)
    perform_try_run
    ;;
  --execute)
    perform_execute
    ;;
esac
