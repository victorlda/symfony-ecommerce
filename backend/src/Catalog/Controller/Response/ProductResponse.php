<?php

declare(strict_types=1);

namespace App\Catalog\Controller\Response;

use App\Catalog\Entity\Product;
use OpenApi\Attributes as OA;

final readonly class ProductResponse
{
    public function __construct(
        #[OA\Property(format: 'uuid', example: '01a0e4a1-a692-7c53-9cc0-add061fe64e8')]
        public string $id,
        #[OA\Property(example: 'CAM-001')]
        public string $sku,
        #[OA\Property(example: 'Camiseta Básica Preta')]
        public string $name,
        #[OA\Property(example: 'camiseta-basica-preta-cam-001')]
        public string $slug,
        #[OA\Property(example: 'Camiseta 100% algodão')]
        public ?string $description,
        #[OA\Property(description: 'Preço em centavos', example: 4990)]
        public int $priceInCents,
        public bool $active,
        #[OA\Property(format: 'date-time')]
        public string $createdAt,
        #[OA\Property(format: 'date-time')]
        public string $updatedAt,
    ) {
    }

    public static function fromEntity(Product $product): self
    {
        return new self(
            id: $product->getId()->toRfc4122(),
            sku: $product->getSku(),
            name: $product->getName(),
            slug: $product->getSlug(),
            description: $product->getDescription(),
            priceInCents: $product->getPriceInCents(),
            active: $product->isActive(),
            createdAt: $product->getCreatedAt()->format(\DATE_ATOM),
            updatedAt: $product->getUpdatedAt()->format(\DATE_ATOM),
        );
    }
}
