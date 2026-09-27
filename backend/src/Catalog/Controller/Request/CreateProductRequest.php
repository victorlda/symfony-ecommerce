<?php

declare(strict_types=1);

namespace App\Catalog\Controller\Request;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class CreateProductRequest
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Length(max: 64)]
        #[Assert\Regex(pattern: '/^[A-Za-z0-9-]+$/', message: 'O SKU deve conter apenas letras, números e hífens.')]
        public string $sku = '',

        #[Assert\NotBlank]
        #[Assert\Length(min: 2, max: 200)]
        public string $name = '',

        #[Assert\Positive]
        #[Assert\LessThanOrEqual(100_000_000)]
        public int $priceInCents = 0,

        #[Assert\Length(max: 5000)]
        public ?string $description = null,
    ) {
    }
}
