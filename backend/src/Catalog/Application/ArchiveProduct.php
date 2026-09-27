<?php

declare(strict_types=1);

namespace App\Catalog\Application;

use App\Catalog\Repository\ProductRepository;
use Symfony\Component\Uid\Uuid;

final readonly class ArchiveProduct
{
    public function __construct(
        private ProductRepository $products,
    ) {
    }

    public function __invoke(Uuid $id): void
    {
        $product = $this->products->find($id) ?? throw new ProductNotFound($id);

        $product->archive();
        $this->products->save($product);
    }
}
