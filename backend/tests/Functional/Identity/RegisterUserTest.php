<?php

declare(strict_types=1);

namespace App\Tests\Functional\Identity;

use App\Tests\Functional\ApiTestCase;

final class RegisterUserTest extends ApiTestCase
{
    public function testRegistersUserWithoutExposingPassword(): void
    {
        $this->requestJson('POST', '/api/users', [
            'email' => 'Nova@Example.com',
            'name' => 'Nova Pessoa',
            'password' => self::PASSWORD,
        ]);

        self::assertResponseStatusCodeSame(201);
        $data = $this->responseData();
        self::assertSame('nova@example.com', $data['email']);
        self::assertArrayNotHasKey('password', $data);
    }

    public function testRejectsDuplicateEmailIgnoringCase(): void
    {
        $this->createUser('duplicado@example.com');

        $this->requestJson('POST', '/api/users', [
            'email' => 'DUPLICADO@example.com',
            'name' => 'Outra Pessoa',
            'password' => self::PASSWORD,
        ]);

        self::assertResponseStatusCodeSame(409);
    }

    public function testRejectsInvalidData(): void
    {
        $this->requestJson('POST', '/api/users', [
            'email' => 'nao-e-email',
            'name' => 'A',
            'password' => '123',
        ]);

        self::assertResponseStatusCodeSame(422);
    }
}
