<?php

declare(strict_types=1);

namespace App\Identity\Application;

final class InvalidRefreshToken extends \DomainException
{
    public function __construct()
    {
        parent::__construct('Sessão expirada. Faça login novamente.');
    }
}
