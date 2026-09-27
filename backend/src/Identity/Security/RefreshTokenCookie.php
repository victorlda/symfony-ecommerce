<?php

declare(strict_types=1);

namespace App\Identity\Security;

use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Response;

final class RefreshTokenCookie
{
    public const NAME = 'refresh_token';
    public const PATH = '/api/auth';

    public static function attach(Response $response, string $token, \DateTimeImmutable $expiresAt): void
    {
        $response->headers->setCookie(Cookie::create(
            name: self::NAME,
            value: $token,
            expire: $expiresAt,
            path: self::PATH,
            secure: true,
            httpOnly: true,
            sameSite: Cookie::SAMESITE_STRICT,
        ));
    }

    public static function clear(Response $response): void
    {
        $response->headers->clearCookie(self::NAME, self::PATH, null, true, true, Cookie::SAMESITE_STRICT);
    }
}
