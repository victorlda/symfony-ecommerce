<?php

declare(strict_types=1);

namespace App\Identity\Controller;

use App\Identity\Application\InvalidRefreshToken;
use App\Identity\Application\RefreshTokenService;
use App\Identity\Security\RefreshTokenCookie;
use App\Shared\Controller\ApiController;
use App\Shared\Http\ProblemResponse;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Nelmio\ApiDocBundle\Attribute\Security;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[OA\Tag(name: 'Autenticação')]
final class RefreshTokenController extends ApiController
{
    public function __construct(
        private readonly RefreshTokenService $refreshTokens,
        private readonly JWTTokenManagerInterface $jwtManager,
    ) {
    }

    /**
     * Renova o token de acesso usando o cookie refresh_token.
     *
     * O refresh token é rotacionado a cada uso: o anterior deixa de valer e um novo cookie é enviado.
     * Reapresentar um token já usado encerra a sessão inteira.
     */
    #[Route('/api/auth/refresh', name: 'api_auth_refresh', methods: ['POST'], format: 'json')]
    #[Security(name: null)]
    #[OA\Response(
        response: 200,
        description: 'Novo token de acesso',
        content: new OA\JsonContent(properties: [new OA\Property(property: 'token', type: 'string')]),
    )]
    #[OA\Response(response: 401, description: 'Refresh token ausente, inválido, expirado ou revogado')]
    public function __invoke(Request $request): JsonResponse
    {
        $plainToken = $request->cookies->get(RefreshTokenCookie::NAME);

        try {
            if (!\is_string($plainToken) || '' === $plainToken) {
                throw new InvalidRefreshToken();
            }

            $result = $this->refreshTokens->rotate($plainToken);
        } catch (InvalidRefreshToken $e) {
            $response = ProblemResponse::create(401, 'refresh_token_invalid', 'Não autenticado', $e->getMessage());
            RefreshTokenCookie::clear($response);

            return $response;
        }

        $response = $this->json(['token' => $this->jwtManager->create($result['user'])]);
        RefreshTokenCookie::attach($response, $result['token'], $result['expiresAt']);

        return $response;
    }
}
