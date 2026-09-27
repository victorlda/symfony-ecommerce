<?php

declare(strict_types=1);

namespace App\Identity\Controller;

use App\Identity\Application\RefreshTokenService;
use App\Identity\Security\RefreshTokenCookie;
use App\Shared\Controller\ApiController;
use Nelmio\ApiDocBundle\Attribute\Security;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[OA\Tag(name: 'Autenticação')]
final class LogoutController extends ApiController
{
    public function __construct(
        private readonly RefreshTokenService $refreshTokens,
    ) {
    }

    /**
     * Encerra a sessão, revogando o refresh token e removendo o cookie.
     *
     * O token de acesso atual continua válido até expirar (no máximo 15 minutos), por isso o cliente deve descartá-lo.
     */
    #[Route('/api/auth/logout', name: 'api_auth_logout', methods: ['POST'], format: 'json')]
    #[Security(name: null)]
    #[OA\Response(response: 204, description: 'Sessão encerrada')]
    public function __invoke(Request $request): Response
    {
        $plainToken = $request->cookies->get(RefreshTokenCookie::NAME);

        if (\is_string($plainToken) && '' !== $plainToken) {
            $this->refreshTokens->revoke($plainToken);
        }

        $response = new Response(null, Response::HTTP_NO_CONTENT);
        RefreshTokenCookie::clear($response);

        return $response;
    }
}
