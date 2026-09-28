.RECIPEPREFIX = >
UID := $(shell id -u)
GID := $(shell id -g)
EXEC = docker compose exec -u $(UID):$(GID) php

up:
> docker compose up -d --build

down:
> docker compose down

logs:
> docker compose logs -f

sh:
> $(EXEC) bash

composer:
> $(EXEC) composer $(c)

console:
> $(EXEC) php bin/console $(c)

jwt:
> $(EXEC) php bin/console lexik:jwt:generate-keypair --skip-if-exists

test-db:
> $(EXEC) php bin/console --env=test doctrine:database:create --if-not-exists
> $(EXEC) php bin/console --env=test doctrine:migrations:migrate --no-interaction

test:
> $(EXEC) vendor/bin/phpunit

stan:
> $(EXEC) vendor/bin/phpstan analyse --memory-limit=1G

cs:
> $(EXEC) vendor/bin/php-cs-fixer fix --dry-run --diff

cs-fix:
> $(EXEC) vendor/bin/php-cs-fixer fix

qa: cs stan test docs

docs:
> docker compose exec -T php php bin/console nelmio:apidoc:dump --format=json > docs/openapi.json

.PHONY: up down logs sh composer console jwt test-db test stan cs cs-fix qa docs

npm:
> docker compose exec frontend npm $(c)

npx:
> docker compose exec frontend npx $(c)

front-logs:
> docker compose logs -f frontend

.PHONY: npm npx front-logs

# Regenera o OpenAPI a partir do backend e os tipos TypeScript a partir do OpenAPI
types: docs
> docker compose exec frontend npm run api:types

front-qa:
> docker compose exec frontend npm run lint
> docker compose exec frontend npm run build

.PHONY: types front-qa
