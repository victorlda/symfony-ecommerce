<?php

declare(strict_types=1);

namespace App\Identity\Controller\Response;

use App\Identity\Entity\User;
use OpenApi\Attributes as OA;

final readonly class UserResponse
{
    /**
     * @param list<string> $roles
     */
    public function __construct(
        #[OA\Property(format: 'uuid', example: '01a0e48a-ca48-7ed4-bcc9-e6654d42873d')]
        public string $id,
        #[OA\Property(format: 'email', example: 'victor@example.com')]
        public string $email,
        #[OA\Property(example: 'Victor')]
        public string $name,
        #[OA\Property(type: 'array', items: new OA\Items(type: 'string'), example: ['ROLE_USER'])]
        public array $roles,
        #[OA\Property(format: 'date-time')]
        public string $createdAt,
    ) {
    }

    public static function fromEntity(User $user): self
    {
        return new self(
            id: $user->getId()->toRfc4122(),
            email: $user->getEmail(),
            name: $user->getName(),
            roles: $user->getRoles(),
            createdAt: $user->getCreatedAt()->format(\DATE_ATOM),
        );
    }
}
