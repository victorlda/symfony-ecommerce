<?php

declare(strict_types=1);

namespace App\Identity\Controller;

use App\Identity\Application\EmailAlreadyInUse;
use App\Identity\Application\RegisterUser;
use App\Identity\Controller\Request\RegisterUserRequest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\Routing\Attribute\Route;

final class RegisterUserController extends AbstractController
{
    public function __construct(
        private readonly RegisterUser $registerUser,
    ) {
    }

    #[Route('/api/users', name: 'api_users_register', methods: ['POST'], format: 'json')]
    public function __invoke(#[MapRequestPayload] RegisterUserRequest $request): JsonResponse
    {
        try {
            $user = ($this->registerUser)($request->email, $request->name, $request->password);
        } catch (EmailAlreadyInUse $e) {
            throw new ConflictHttpException($e->getMessage(), $e);
        }

        return $this->json([
            'id' => $user->getId()->toRfc4122(),
            'email' => $user->getEmail(),
            'name' => $user->getName(),
            'createdAt' => $user->getCreatedAt()->format(\DATE_ATOM),
        ], Response::HTTP_CREATED);
    }
}
