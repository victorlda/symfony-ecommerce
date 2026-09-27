<?php

declare(strict_types=1);

namespace App\Tests\Functional\Identity;

use App\Tests\Functional\ApiTestCase;

final class AuthenticationTest extends ApiTestCase
{
    public function testLoginReturnsTokenThatAuthenticatesTheUser(): void
    {
        $this->createUser('victor@example.com');
        $token = $this->login('VICTOR@example.com');

        $this->requestJson('GET', '/api/me', token: $token);

        self::assertResponseIsSuccessful();
        self::assertSame('victor@example.com', $this->responseData()['email']);
    }

    public function testLoginFailsWithWrongPassword(): void
    {
        $this->createUser('victor@example.com');

        $this->requestJson('POST', '/api/login', ['email' => 'victor@example.com', 'password' => 'senha-errada']);

        self::assertResponseStatusCodeSame(401);
    }

    public function testMeRequiresToken(): void
    {
        $this->requestJson('GET', '/api/me');

        self::assertResponseStatusCodeSame(401);
    }
}
