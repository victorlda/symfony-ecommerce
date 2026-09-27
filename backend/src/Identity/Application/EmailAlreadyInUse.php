<?php

declare(strict_types=1);

namespace App\Identity\Application;

final class EmailAlreadyInUse extends \DomainException
{
    public function __construct(string $email)
    {
        parent::__construct(sprintf('O e-mail "%s" já está em uso.', $email));
    }
}
