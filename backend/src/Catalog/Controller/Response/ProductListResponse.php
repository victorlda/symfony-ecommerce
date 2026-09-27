<?php

declare(strict_types=1);

namespace App\Catalog\Controller\Response;

use App\Shared\Controller\Response\PaginationMeta;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;

final readonly class ProductListResponse
{
    /**
     * @param list<ProductResponse> $data
     */
    public function __construct(
        #[OA\Property(type: 'array', items: new OA\Items(ref: new Model(type: ProductResponse::class)))]
        public array $data,
        #[OA\Property(ref: new Model(type: PaginationMeta::class))]
        public PaginationMeta $meta,
    ) {
    }
}
