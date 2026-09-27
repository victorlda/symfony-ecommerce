<?php

declare(strict_types=1);

namespace App\Catalog\Application;

use Symfony\Component\Uid\Uuid;

final class ProductNotFound extends \DomainException
{
    public function __construct(Uuid $id)
    {
        parent::__construct(sprintf('Produto "%s" não encontrado.', $id->toRfc4122()));
    }
}
