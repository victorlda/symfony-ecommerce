<?php

declare(strict_types=1);

namespace App\Catalog\Controller\Response;

use App\Catalog\Entity\Product;

final class ProductResponse
{
    /**
     * @return array<string, mixed>
     */
    public static function fromEntity(Product $product): array
    {
        return [
            'id' => $product->getId()->toRfc4122(),
            'sku' => $product->getSku(),
            'name' => $product->getName(),
            'slug' => $product->getSlug(),
            'description' => $product->getDescription(),
            'priceInCents' => $product->getPriceInCents(),
            'active' => $product->isActive(),
            'createdAt' => $product->getCreatedAt()->format(\DATE_ATOM),
            'updatedAt' => $product->getUpdatedAt()->format(\DATE_ATOM),
        ];
    }
}
