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

## DO NOT USE IN PRODUCTION

Seed data, demo roles, and credentials are provided strictly for local development and verification.
