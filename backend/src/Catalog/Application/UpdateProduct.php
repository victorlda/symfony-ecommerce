<?php

declare(strict_types=1);

namespace App\Catalog\Application;

use App\Catalog\Entity\Product;
use App\Catalog\Repository\ProductRepository;
use Symfony\Component\Uid\Uuid;

final readonly class UpdateProduct
{
    public function __construct(
        private ProductRepository $products,
    ) {
    }

    public function __invoke(Uuid $id, string $name, int $priceInCents, ?string $description): Product
    {
        $product = $this->products->find($id) ?? throw new ProductNotFound($id);

        $product->update($name, $priceInCents, $description);
        $this->products->save($product);

        return $product;
    }
}
