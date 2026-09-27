<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Identity\Application\RegisterUser;
use App\Identity\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class ApiTestCase extends WebTestCase
{
    protected const PASSWORD = 'Senha-de-teste-forte-2026';

    protected KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient(server: ['HTTPS' => 'on']);
    }

    /**
     * @param array<string, mixed>|null $body
     */
    protected function requestJson(string $method, string $uri, ?array $body = null, ?string $token = null): void
    {
        $server = [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT' => 'application/json',
        ];

        if (null !== $token) {
            $server['HTTP_AUTHORIZATION'] = 'Bearer '.$token;
        }

        $this->client->request(
            $method,
            $uri,
            server: $server,
            content: null === $body ? null : json_encode($body, \JSON_THROW_ON_ERROR),
        );
    }

    /**
     * @return array<mixed>
     */
    protected function responseData(): array
    {
        $data = json_decode((string) $this->client->getResponse()->getContent(), true, flags: \JSON_THROW_ON_ERROR);
        self::assertIsArray($data);

        return $data;
    }

    protected function createUser(string $email, bool $admin = false): void
    {
        $container = static::getContainer();
        $user = $container->get(RegisterUser::class)($email, 'Usuário de Teste', self::PASSWORD);

        if ($admin) {
            $user->promoteToAdmin();
            $container->get(UserRepository::class)->save($user);
        }
    }

    protected function login(string $email): string
    {
        $this->requestJson('POST', '/api/login', ['email' => $email, 'password' => self::PASSWORD]);
        self::assertResponseIsSuccessful();

        $token = $this->responseData()['token'] ?? null;
        self::assertIsString($token);

        return $token;
    }

    protected function adminToken(): string
    {
        $this->createUser('admin@example.com', admin: true);

        return $this->login('admin@example.com');
    }

    protected function userToken(): string
    {
        $this->createUser('cliente@example.com');

        return $this->login('cliente@example.com');
    }
}
