<?php

declare(strict_types=1);

namespace App\Tests\Functional\Identity;

use App\Identity\Security\RefreshTokenCookie;
use App\Tests\Functional\ApiTestCase;
use Symfony\Component\BrowserKit\Cookie as BrowserCookie;
use Symfony\Component\HttpFoundation\Cookie;

final class RefreshTokenTest extends ApiTestCase
{
    public function testLoginSetsSecureRefreshCookie(): void
    {
        $this->createUser('victor@example.com');
        $this->login('victor@example.com');

        $cookie = $this->responseRefreshCookie();
        self::assertNotNull($cookie);
        self::assertTrue($cookie->isHttpOnly());
        self::assertTrue($cookie->isSecure());
        self::assertSame(RefreshTokenCookie::PATH, $cookie->getPath());
        self::assertSame(Cookie::SAMESITE_STRICT, $cookie->getSameSite());
    }

    public function testRefreshReturnsNewAccessTokenAndRotatesCookie(): void
    {
        $this->createUser('victor@example.com');
        $this->login('victor@example.com');
        $previous = $this->storedRefreshToken();

        $this->requestJson('POST', '/api/auth/refresh');

        self::assertResponseIsSuccessful();
        $token = $this->responseData()['token'] ?? null;
        self::assertIsString($token);
        self::assertNotSame($previous, $this->storedRefreshToken());

        $this->requestJson('GET', '/api/me', token: $token);
        self::assertResponseIsSuccessful();
    }

    public function testReusingRotatedTokenRevokesWholeSession(): void
    {
        $this->createUser('victor@example.com');
        $this->login('victor@example.com');
        $stolen = $this->storedRefreshToken();

        $this->requestJson('POST', '/api/auth/refresh');
        self::assertResponseIsSuccessful();
        $legitimate = $this->storedRefreshToken();

        $this->useRefreshToken($stolen);
        $this->requestJson('POST', '/api/auth/refresh');
        self::assertResponseStatusCodeSame(401);
        self::assertSame('refresh_token_invalid', $this->responseData()['code']);

        $this->useRefreshToken($legitimate);
        $this->requestJson('POST', '/api/auth/refresh');
        self::assertResponseStatusCodeSame(401);
    }

    public function testRefreshWithoutCookieFails(): void
    {
        $this->requestJson('POST', '/api/auth/refresh');

        self::assertResponseStatusCodeSame(401);
        self::assertSame('refresh_token_invalid', $this->responseData()['code']);
    }

    public function testLogoutRevokesRefreshToken(): void
    {
        $this->createUser('victor@example.com');
        $this->login('victor@example.com');
        $token = $this->storedRefreshToken();

        $this->requestJson('POST', '/api/auth/logout');
        self::assertResponseStatusCodeSame(204);

        $this->useRefreshToken($token);
        $this->requestJson('POST', '/api/auth/refresh');
        self::assertResponseStatusCodeSame(401);
    }

    private function responseRefreshCookie(): ?Cookie
    {
        foreach ($this->client->getResponse()->headers->getCookies() as $cookie) {
            if (RefreshTokenCookie::NAME === $cookie->getName()) {
                return $cookie;
            }
        }

        return null;
    }

    private function storedRefreshToken(): string
    {
        $value = $this->client->getCookieJar()->get(RefreshTokenCookie::NAME, RefreshTokenCookie::PATH, 'localhost')?->getValue();
        self::assertIsString($value);

        return $value;
    }

    private function useRefreshToken(string $value): void
    {
        $this->client->getCookieJar()->set(
            new BrowserCookie(RefreshTokenCookie::NAME, $value, null, RefreshTokenCookie::PATH, 'localhost', true, true),
        );
    }
}
