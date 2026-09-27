<?php

declare(strict_types=1);

namespace App\Tests\Unit\Identity\Entity;

use App\Identity\Entity\User;
use PHPUnit\Framework\TestCase;

final class UserTest extends TestCase
{
    private const HASH = 'hash-fake';

    public function testNormalizesEmailAndName(): void
    {
        $user = new User('  Victor@Example.COM ', ' Victor ', self::HASH);

        self::assertSame('victor@example.com', $user->getEmail());
        self::assertSame('Victor', $user->getName());
    }

    public function testRejectsEmptyEmail(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new User('   ', 'Victor', self::HASH);
    }

    public function testAlwaysHasRoleUser(): void
    {
        $user = new User('victor@example.com', 'Victor', self::HASH);

        self::assertSame(['ROLE_USER'], $user->getRoles());
    }

    public function testPromoteToAdminIsIdempotent(): void
    {
        $user = new User('victor@example.com', 'Victor', self::HASH);

        $user->promoteToAdmin();
        $user->promoteToAdmin();

        self::assertEqualsCanonicalizing(['ROLE_ADMIN', 'ROLE_USER'], $user->getRoles());
    }
}
