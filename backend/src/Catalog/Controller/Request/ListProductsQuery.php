<?php

declare(strict_types=1);

namespace App\Catalog\Controller\Request;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class ListProductsQuery
{
    public function __construct(
        #[Assert\Positive]
        public int $page = 1,

        #[Assert\Range(min: 1, max: 100)]
        public int $limit = 20,
    ) {
    }
}
