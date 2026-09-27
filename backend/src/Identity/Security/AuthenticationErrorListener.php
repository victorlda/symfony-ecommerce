<?php

declare(strict_types=1);

namespace App\Identity\Security;

use App\Shared\Http\ProblemResponse;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationFailureEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Events;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

final readonly class AuthenticationErrorListener
{
    private const TITLE = 'Não autenticado';
    private const HEADERS = ['WWW-Authenticate' => 'Bearer'];

    #[AsEventListener(event: Events::AUTHENTICATION_FAILURE)]
    public function onInvalidCredentials(AuthenticationFailureEvent $event): void
    {
        $event->setResponse(ProblemResponse::create(401, 'invalid_credentials', self::TITLE, 'E-mail ou senha inválidos.'));
    }

    #[AsEventListener(event: Events::JWT_NOT_FOUND)]
    public function onTokenMissing(AuthenticationFailureEvent $event): void
    {
        $event->setResponse(ProblemResponse::create(401, 'token_missing', self::TITLE, 'Token de autenticação não informado.', headers: self::HEADERS));
    }

    #[AsEventListener(event: Events::JWT_INVALID)]
    public function onTokenInvalid(AuthenticationFailureEvent $event): void
    {
        $event->setResponse(ProblemResponse::create(401, 'token_invalid', self::TITLE, 'Token de autenticação inválido.', headers: self::HEADERS));
    }

    #[AsEventListener(event: Events::JWT_EXPIRED)]
    public function onTokenExpired(AuthenticationFailureEvent $event): void
    {
        $event->setResponse(ProblemResponse::create(401, 'token_expired', self::TITLE, 'Token de autenticação expirado.', headers: self::HEADERS));
    }
}
