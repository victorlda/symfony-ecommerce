<?php

declare(strict_types=1);

namespace App\Identity\Command;

use App\Identity\Repository\UserRepository;
use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'app:user:promote-admin', description: 'Concede o perfil ROLE_ADMIN a um usuário')]
final readonly class PromoteAdminCommand
{
    public function __construct(
        private UserRepository $users,
    ) {
    }

    public function __invoke(
        SymfonyStyle $io,
        #[Argument(description: 'E-mail do usuário')] string $email,
    ): int {
        $user = $this->users->findOneByEmail($email);

        if (null === $user) {
            $io->error(sprintf('Usuário "%s" não encontrado.', $email));

            return Command::FAILURE;
        }

        $user->promoteToAdmin();
        $this->users->save($user);

        $io->success(sprintf('%s agora é administrador.', $user->getEmail()));

        return Command::SUCCESS;
    }
}
