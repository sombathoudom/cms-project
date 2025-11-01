# Phase 5 – Issue Implementation Runbook

This guide documents the exact steps required to select, implement, and close work items created during Phases 1–4. The automation is **never** executed implicitly; operators must run it in order, confirm each stage succeeds, and then merge their pull requests.

## 1. Prerequisites
- Ensure `REPO=sombathoudom/cms-project` is exported in your shell.
- Confirm a valid GitHub authentication context by running:
  ```bash
  gh repo view "$REPO"
  gh auth status --hostname github.com
  ```
- Work from a clean git tree (no untracked or unstaged changes).

## 2. Import Remaining Issues
The repository ships with `issues.csv` and the `gh_issues.sh` helper. Run all modes in order:
```bash
./gh_issues.sh --try-run
./gh_issues.sh --execute
./gh_issues.sh --verify
```
If any step reports missing authentication, rate limits, or lint failures, fix the underlying problem and rerun until it passes. Issues are only created when `--execute` completes without errors.

> **Note:** The script is idempotent. Re-running `--execute` after issues exist will skip them while updating the ledger.

## 3. Select Work in Phase 5
1. Review `TRACEABILITY.md` to avoid duplicate picks.
2. Use `gh issue list --milestone "Week 3" --label priority:P1` (or your target milestone/priority) to find the next issue.
3. Log the selected issue ID in your working notes before touching code.

## 4. Implement the Issue
- Follow the acceptance criteria from `issues.csv` and the Phase 3 scaffolding plan.
- Update migrations, models, policies, API, Filament resources, tests, and documentation as required.
- Append a traceability row to `TRACEABILITY.md`.
- Update `docs/coverage-ledger.md` if new endpoints or models are introduced.

## 5. Validate Locally
Run the full suite before committing:
```bash
./vendor/bin/pint --test
./vendor/bin/phpstan analyse
php artisan test
```
Repeat migrations when schema changes:
```bash
php artisan migrate
```

## 6. Commit and Pull Request
1. Commit with a conventional message referencing the GitHub issue number, for example:
   ```
   feat(content): #123 implement tenant audit log export
   ```
2. Push your branch and open a pull request.
3. Ensure the PR description references the issue and summarises the changes.
4. After merge, close the GitHub issue (or rely on `Fixes #123` in the PR description).

## 7. Close the Issue
- Confirm the change is merged into `main`.
- Verify GitHub shows the issue as closed; if not, close it manually referencing the commit/PR.

Keeping this runbook ensures Phase 5 remains deterministic and auditable. Every ticket stays open until the operator executes these steps and merges the corresponding pull request.
