<?php

declare(strict_types=1);

namespace App\Identity\Controller;

use App\Identity\Application\EmailAlreadyInUse;
use App\Identity\Application\RegisterUser;
use App\Identity\Controller\Request\RegisterUserRequest;
use App\Identity\Controller\Response\UserResponse;
use App\Shared\Controller\ApiController;
use Nelmio\ApiDocBundle\Attribute\Model;
use Nelmio\ApiDocBundle\Attribute\Security;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\Routing\Attribute\Route;

#[OA\Tag(name: 'Usuários')]
final class RegisterUserController extends ApiController
{
    public function __construct(
        private readonly RegisterUser $registerUser,
    ) {
    }

    /**
     * Cadastra um novo usuário.
     */
    #[Route('/api/users', name: 'api_users_register', methods: ['POST'], format: 'json')]
    #[Security(name: null)]
    #[OA\Response(response: 201, description: 'Usuário cadastrado', content: new Model(type: UserResponse::class))]
    #[OA\Response(response: 409, description: 'E-mail já cadastrado')]
    #[OA\Response(response: 422, description: 'Dados inválidos')]
    public function __invoke(#[MapRequestPayload] RegisterUserRequest $request): JsonResponse
    {
        try {
            $user = ($this->registerUser)($request->email, $request->name, $request->password);
        } catch (EmailAlreadyInUse $e) {
            throw new ConflictHttpException($e->getMessage(), $e);
        }

        return $this->json(UserResponse::fromEntity($user), Response::HTTP_CREATED);
    }
}
