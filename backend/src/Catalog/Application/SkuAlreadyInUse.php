<?php

declare(strict_types=1);

namespace App\Catalog\Application;

final class SkuAlreadyInUse extends \DomainException
{
    public function __construct(string $sku)
    {
        parent::__construct(\sprintf('O SKU "%s" já está em uso.', $sku));
    }
}
