<?php

declare(strict_types=1);

namespace App\Catalog\Application;

use App\Catalog\Entity\Product;
use App\Catalog\Repository\ProductRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Component\String\Slugger\SluggerInterface;

final readonly class CreateProduct
{
    public function __construct(
        private ProductRepository $products,
        private SluggerInterface $slugger,
    ) {
    }

    public function __invoke(string $sku, string $name, int $priceInCents, ?string $description): Product
    {
        if (null !== $this->products->findOneBySku($sku)) {
            throw new SkuAlreadyInUse($sku);
        }

        $slug = $this->slugger->slug($name.' '.$sku)->lower()->toString();
        $product = new Product($sku, $name, $slug, $priceInCents, $description);

        try {
            $this->products->save($product);
        } catch (UniqueConstraintViolationException) {
            throw new SkuAlreadyInUse($sku);
        }

        return $product;
    }
}
