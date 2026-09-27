<?php

declare(strict_types=1);

namespace App\Tests\Unit\Catalog\Entity;

use App\Catalog\Entity\Product;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ProductTest extends TestCase
{
    public function testNormalizesSkuAndStartsActive(): void
    {
        $product = new Product(' cam-001 ', 'Camiseta', 'camiseta-cam-001', 4990);

        self::assertSame('CAM-001', $product->getSku());
        self::assertTrue($product->isActive());
    }

    #[DataProvider('invalidPrices')]
    public function testRejectsInvalidPrice(int $price): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new Product('CAM-001', 'Camiseta', 'camiseta-cam-001', $price);
    }

    /**
     * @return iterable<string, array{int}>
     */
    public static function invalidPrices(): iterable
    {
        yield 'zero' => [0];
        yield 'negativo' => [-100];
    }

    public function testUpdateChangesDataButKeepsSkuAndSlug(): void
    {
        $product = new Product('CAM-001', 'Camiseta', 'camiseta-cam-001', 4990);

        $product->update('Camiseta Premium', 5990, 'Nova descrição');

        self::assertSame('Camiseta Premium', $product->getName());
        self::assertSame(5990, $product->getPriceInCents());
        self::assertSame('CAM-001', $product->getSku());
        self::assertSame('camiseta-cam-001', $product->getSlug());
    }

    public function testArchiveIsIdempotent(): void
    {
        $product = new Product('CAM-001', 'Camiseta', 'camiseta-cam-001', 4990);

        $product->archive();
        $updatedAt = $product->getUpdatedAt();
        $product->archive();

        self::assertFalse($product->isActive());
        self::assertSame($updatedAt, $product->getUpdatedAt());
    }
}
