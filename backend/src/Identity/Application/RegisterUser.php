<?php

declare(strict_types=1);

namespace App\Identity\Application;

use App\Identity\Entity\User;
use App\Identity\Repository\UserRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;

final readonly class RegisterUser
{
    public function __construct(
        private UserRepository $users,
        private PasswordHasherFactoryInterface $hasherFactory,
    ) {
    }

    public function __invoke(string $email, string $name, string $plainPassword): User
    {
        if (null !== $this->users->findOneByEmail($email)) {
            throw new EmailAlreadyInUse($email);
        }

        $hashedPassword = $this->hasherFactory->getPasswordHasher(User::class)->hash($plainPassword);
        $user = new User($email, $name, $hashedPassword);

        try {
            $this->users->save($user);
        } catch (UniqueConstraintViolationException) {
            throw new EmailAlreadyInUse($email);
        }

        return $user;
    }
}
