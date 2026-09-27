<?php

declare(strict_types=1);

namespace App\Shared\Controller\Response;

use OpenApi\Attributes as OA;

final readonly class PaginationMeta
{
    public function __construct(
        #[OA\Property(example: 1)]
        public int $page,
        #[OA\Property(example: 20)]
        public int $limit,
        #[OA\Property(example: 42)]
        public int $total,
        #[OA\Property(example: 3)]
        public int $totalPages,
    ) {
    }
}
