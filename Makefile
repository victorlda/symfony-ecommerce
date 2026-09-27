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
