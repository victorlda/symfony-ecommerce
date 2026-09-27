<?php

declare(strict_types=1);

namespace App\Catalog\Controller\Request;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class AdminListProductsQuery
{
    public function __construct(
        #[Assert\Choice(choices: ['all', 'active', 'archived'])]
        public string $status = 'all',

        #[Assert\Length(max: 100)]
        public ?string $q = null,

        #[Assert\Positive]
        public int $page = 1,

        #[Assert\Range(min: 1, max: 100)]
        public int $limit = 20,
    ) {
    }

    public function activeFilter(): ?bool
    {
        return match ($this->status) {
            'active' => true,
            'archived' => false,
            default => null,
        };
    }
}
