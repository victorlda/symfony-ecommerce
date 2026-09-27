# Symfony E-commerce

[![CI](https://github.com/victorlda/symfony-ecommerce/actions/workflows/ci.yml/badge.svg)](https://github.com/victorlda/symfony-ecommerce/actions/workflows/ci.yml)
[![OpenAPI](https://img.shields.io/badge/OpenAPI-documentação-85EA2D?logo=swagger&logoColor=black)](https://petstore.swagger.io/?url=https://raw.githubusercontent.com/victorlda/symfony-ecommerce/main/docs/openapi.json)
![PHP](https://img.shields.io/badge/PHP-8.4-777BB4?logo=php&logoColor=white)
![Symfony](https://img.shields.io/badge/Symfony-8-000000?logo=symfony&logoColor=white)
![PostgreSQL](https://img.shields.io/badge/PostgreSQL-18-4169E1?logo=postgresql&logoColor=white)
![PHPStan](https://img.shields.io/badge/PHPStan-nível%208-brightgreen)

API de e-commerce construída com Symfony 8, PHP 8.4, FrankenPHP e PostgreSQL 18, organizada como um monólito modular.

## Documentação da API

A especificação OpenAPI fica em [`docs/openapi.json`](docs/openapi.json) e é verificada no CI a cada push, garantindo que esteja sempre em sincronia com o código.

- **Online:** clique no selo **OpenAPI** acima para abrir no Swagger UI, sem instalar nada.
- **Local:** com o projeto rodando, acesse http://localhost:8000/api/doc.

## Stack

- PHP 8.4 + Symfony 8 + FrankenPHP
- PostgreSQL 18 + Doctrine ORM
- Autenticação JWT (LexikJWTAuthenticationBundle)
- Documentação OpenAPI (NelmioApiDocBundle)
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
