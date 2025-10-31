.PHONY: up down migrate seed reset test lint pint stan reindex logs ci-check

up:
	docker compose up -d --build

down:
	docker compose down

migrate:
	docker compose exec app php artisan migrate

seed:
	docker compose exec app php artisan db:seed

reset:
	docker compose exec app php artisan migrate:fresh --seed

test:
	docker compose exec app vendor/bin/pest

lint:
	docker compose exec app vendor/bin/pint --test

pint:
	docker compose exec app vendor/bin/pint

stan:
	docker compose exec app vendor/bin/phpstan analyse

reindex:
	docker compose exec app php artisan scout:import "App\Domains\Content\Models\Content"

logs:
	docker compose logs -f app

ci-check: lint stan test
