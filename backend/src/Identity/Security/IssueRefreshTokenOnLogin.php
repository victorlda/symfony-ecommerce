<?php

declare(strict_types=1);

namespace App\Identity\Security;

use App\Identity\Application\RefreshTokenService;
use App\Identity\Entity\User;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Events;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener(event: Events::AUTHENTICATION_SUCCESS)]
final readonly class IssueRefreshTokenOnLogin
{
    public function __construct(
        private RefreshTokenService $refreshTokens,
    ) {
    }

    public function __invoke(AuthenticationSuccessEvent $event): void
    {
        $user = $event->getUser();

        if (!$user instanceof User) {
            return;
        }

        [$token, $expiresAt] = $this->refreshTokens->issue($user);
        RefreshTokenCookie::attach($event->getResponse(), $token, $expiresAt);
    }
}
