<?php

declare(strict_types=1);

namespace App\Catalog\Entity;

use App\Catalog\Repository\ProductRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
#[ORM\Table(name: 'products')]
#[ORM\Index(name: 'idx_products_active_created', columns: ['active', 'created_at'])]
class Product
{
    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    private Uuid $id;

    #[ORM\Column(length: 64, unique: true)]
    private string $sku;

    #[ORM\Column(length: 200)]
    private string $name;

    #[ORM\Column(length: 255, unique: true)]
    private string $slug;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description;

    #[ORM\Column]
    private int $priceInCents;

    #[ORM\Column]
    private bool $active = true;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column]
    private \DateTimeImmutable $updatedAt;

    public function __construct(
        string $sku,
        string $name,
        string $slug,
        int $priceInCents,
        ?string $description = null,
    ) {
        if ($priceInCents <= 0) {
            throw new \InvalidArgumentException('O preço deve ser maior que zero.');
        }

        $this->id = Uuid::v7();
        $this->sku = mb_strtoupper(trim($sku));
        $this->name = trim($name);
        $this->slug = $slug;
        $this->priceInCents = $priceInCents;
        $this->description = $description;
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = $this->createdAt;
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getSku(): string
    {
        return $this->sku;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getPriceInCents(): int
    {
        return $this->priceInCents;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function update(string $name, int $priceInCents, ?string $description): void
    {
        if ($priceInCents <= 0) {
            throw new \InvalidArgumentException('O preço deve ser maior que zero.');
        }

        $this->name = trim($name);
        $this->priceInCents = $priceInCents;
        $this->description = $description;
        $this->touch();
    }

    public function archive(): void
    {
        if (!$this->active) {
            return;
        }

        $this->active = false;
        $this->touch();
    }

    private function touch(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }
}
