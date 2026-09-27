<?php

declare(strict_types=1);

namespace App\Identity\Application;

use App\Identity\Entity\User;
use App\Identity\Repository\UserRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final readonly class RegisterUser
{
    public function __construct(
        private UserRepository $users,
        private UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    public function __invoke(string $email, string $name, string $plainPassword): User
    {
        if (null !== $this->users->findOneByEmail($email)) {
            throw new EmailAlreadyInUse($email);
        }

        $user = new User($email, $name);
        $user->changePassword($this->passwordHasher->hashPassword($user, $plainPassword));

        try {
            $this->users->save($user);
        } catch (UniqueConstraintViolationException) {
            throw new EmailAlreadyInUse($email);
        }

        return $user;
    }
}
