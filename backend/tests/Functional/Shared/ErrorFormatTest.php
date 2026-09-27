<?php

declare(strict_types=1);

namespace App\Tests\Functional\Shared;

use App\Tests\Functional\ApiTestCase;

final class ErrorFormatTest extends ApiTestCase
{
    public function testValidationErrorsListFieldsWithoutEchoingValues(): void
    {
        $this->requestJson('POST', '/api/users', [
            'email' => 'nao-e-email',
            'name' => 'Victor',
            'password' => 'segredo123',
        ]);

        self::assertResponseStatusCodeSame(422);
        self::assertResponseHeaderSame('Content-Type', 'application/problem+json');

        $data = $this->responseData();
        self::assertSame('validation_failed', $data['code']);
        self::assertIsArray($data['errors']);
        self::assertContains('email', array_column($data['errors'], 'field'));
        self::assertStringNotContainsString('segredo123', (string) $this->client->getResponse()->getContent());
    }

    public function testForbiddenDoesNotRevealRequiredRole(): void
    {
        $this->requestJson('POST', '/api/products', ['sku' => 'x-1', 'name' => 'Produto', 'priceInCents' => 100], $this->userToken());

        self::assertResponseStatusCodeSame(403);
        self::assertSame('forbidden', $this->responseData()['code']);
        self::assertStringNotContainsString('ROLE_ADMIN', (string) $this->client->getResponse()->getContent());
    }

    public function testDomainErrorKeepsBusinessMessage(): void
    {
        $this->createUser('repetido@example.com');

        $this->requestJson('POST', '/api/users', [
            'email' => 'repetido@example.com',
            'name' => 'Outra Pessoa',
            'password' => self::PASSWORD,
        ]);

        self::assertResponseStatusCodeSame(409);
        $data = $this->responseData();
        self::assertSame('conflict', $data['code']);
        self::assertIsString($data['detail']);
        self::assertStringContainsString('já está em uso', $data['detail']);
    }

    public function testUnknownRouteReturnsNotFound(): void
    {
        $this->requestJson('GET', '/api/rota-inexistente');

        self::assertResponseStatusCodeSame(404);
        self::assertSame('not_found', $this->responseData()['code']);
    }

    public function testMissingToken(): void
    {
        $this->requestJson('GET', '/api/me');

        self::assertResponseStatusCodeSame(401);
        self::assertSame('token_missing', $this->responseData()['code']);
    }

    public function testInvalidToken(): void
    {
        $this->requestJson('GET', '/api/me', token: 'token-invalido');

        self::assertResponseStatusCodeSame(401);
        self::assertSame('token_invalid', $this->responseData()['code']);
    }

    public function testInvalidCredentials(): void
    {
        $this->createUser('victor@example.com');

        $this->requestJson('POST', '/api/login', ['email' => 'victor@example.com', 'password' => 'senha-errada']);

        self::assertResponseStatusCodeSame(401);
        self::assertSame('invalid_credentials', $this->responseData()['code']);
    }
}
