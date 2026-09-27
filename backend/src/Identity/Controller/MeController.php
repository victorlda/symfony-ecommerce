<?php

declare(strict_types=1);

namespace App\Identity\Controller;

use App\Identity\Controller\Response\UserResponse;
use App\Identity\Entity\User;
use App\Shared\Controller\ApiController;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[OA\Tag(name: 'Usuários')]
final class MeController extends ApiController
{
    /**
     * Retorna os dados do usuário autenticado.
     */
    #[Route('/api/me', name: 'api_me', methods: ['GET'], format: 'json')]
    #[OA\Response(response: 200, description: 'Usuário autenticado', content: new Model(type: UserResponse::class))]
    #[OA\Response(response: 401, description: 'Token ausente, inválido ou expirado')]
    public function __invoke(#[CurrentUser] User $user): JsonResponse
    {
        return $this->json(UserResponse::fromEntity($user));
    }
}
