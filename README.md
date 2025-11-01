# CMS Platform Skeleton

This repository contains a production-ready Laravel 11 skeleton preconfigured with role-based access control, content management primitives, Filament 3 admin resources, and DevOps automation. It is designed to compile, migrate, and run from a fresh clone with no manual tweaks.

## How to Run Locally

```bash
composer install
npm install && npm run build
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

> Requires Docker services (MySQL, Redis, Meilisearch) or the provided Docker stack. For containerized development run `docker compose up --build` and interact with the app on <http://localhost:8080>.

## Quality Tooling

- **Static Analysis:** `vendor/bin/phpstan analyse`
- **Code Style:** `vendor/bin/pint --test`
- **Tests:** `vendor/bin/pest`
- **Scout Search:** Laravel Scout with Meilisearch driver placeholders ready for configuration.

## Services and Architecture

- Modular domain folders for authentication, content, media, taxonomy, workflow, settings, API, and security concerns.
- UUID primary keys and soft deletes across content-centric tables.
- Spatie Permission integration with Admin/Agent/Viewer roles and Gate super-admin bypass.
- Filament resources for Tickets, Contacts, and Knowledge Base articles with tenant-aware filters and Scout search.
- Healthcheck endpoint at `/health` reporting database and Redis status in strict JSON.

## Rich Text Editing & Preview

- Knowledge Base articles use the Filament Tiptap editor with curated toolbar controls, image attachments, and 60-second auto-save intervals.
- Auto-save writes revision history, enforces RBAC, and emits audit logs while surfacing status indicators directly in the editor UI.
- Editors can generate signed preview links from the API or Filament UI; each link expires automatically and renders a read-only view at `/preview/content/{id}/{token}`.

## Media Library Integration

- The editor toolbar now includes a tenant-aware media picker that lists existing uploads, supports keyword and MIME filters, and allows direct uploads with RBAC enforcement.
- Selected assets prompt for alt text and display order; the API responds with responsive `<figure>` markup that is appended to the active Tiptap editor session.
- Back-end endpoints:
  - `GET /api/v1/admin/media` – paginated library listing with standard error schema and tenant scoping.
  - `POST /api/v1/admin/media` – authenticated upload endpoint honoring file validation rules.
  - `POST /api/v1/admin/content/{content}/media` – embeds a media asset, records `media_usages` metadata, and returns markup for insertion.
- All requests emit structured JSON logs with correlation identifiers, and audit entries capture upload and embed actions for compliance trails.

## Docker Stack

| Service     | Description                               |
|-------------|-------------------------------------------|
| app         | PHP-FPM container for artisan and queue   |
| queue       | Supervisor-driven queue worker            |
| nginx       | Public web tier serving the Laravel app   |
| db          | MySQL 8.0 with seeded demo credentials    |
| redis       | Redis 7 cache and queue backend           |
| meilisearch | Meilisearch v1 search index               |

Configuration overrides live in `.env.docker`.

## Continuous Integration

GitHub Actions workflow (`ci.yml`) enforces Pint, PHPStan, and Pest with a PHP 8.3/8.4 matrix across MySQL and Postgres, uploading coverage artifacts. All jobs must pass before merge.

## Security

- Strict password policies enforced via validation rules.
- Audit logging of user actions and access logs for compliance review.
- Healthcheck failures propagate 503 responses to surface dependency outages quickly.

## Audit Logging

- User create, update, delete, and restore events automatically produce audit log entries with correlation identifiers and redacted sensitive fields.
- Administrators can review activity in Filament under **Security → Audit Logs** with action and date filters.
- The authenticated API at `GET /api/v1/admin/audit-logs` returns paginated JSON including actor details and enforces tenant scoping, RBAC, and the standard error schema.

## DO NOT USE IN PRODUCTION

Seed data, demo roles, and credentials are provided strictly for local development and verification.

## Documentation

- `docs/coverage-ledger.md` – Feature to model/endpoint/test mapping.
- `docs/phase_5.md` – Phase 5 issue implementation and closure runbook, including GitHub automation usage.
