<?php

declare(strict_types=1);

namespace App\Catalog\Application;

use App\Catalog\Entity\Product;
use App\Catalog\Repository\ProductRepository;
use Symfony\Component\Uid\Uuid;

final readonly class RestoreProduct
{
    public function __construct(
        private ProductRepository $products,
    ) {
    }

    public function __invoke(Uuid $id): Product
    {
        $product = $this->products->find($id) ?? throw new ProductNotFound($id);

        $product->restore();
        $this->products->save($product);

        return $product;
    }
}
