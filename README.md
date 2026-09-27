# Symfony E-commerce

![CI](https://github.com/victorlda/symfony-ecommerce/actions/workflows/ci.yml/badge.svg)

API de e-commerce construída com Symfony 8, PHP 8.4, FrankenPHP e PostgreSQL 18, organizada como um monólito modular.

## Stack

- PHP 8.4 + Symfony 8 + FrankenPHP
- PostgreSQL 18 + Doctrine ORM
- Autenticação JWT (LexikJWTAuthenticationBundle)
- PHPUnit, PHPStan e PHP CS Fixer
- Docker Compose e GitHub Actions

## Como rodar

```bash
make up
docker compose exec -u $(id -u):$(id -g) php composer install
make jwt
make console c="doctrine:migrations:migrate --no-interaction"
make test-db
make qa
```

A API fica disponível em http://localhost:8000.
