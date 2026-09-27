<?php

declare(strict_types=1);

namespace App\Catalog\Controller;

use App\Catalog\Controller\Request\ListProductsQuery;
use App\Catalog\Controller\Response\ProductResponse;
use App\Catalog\Repository\ProductRepository;
use App\Shared\Controller\ApiController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;

final class ListProductsController extends ApiController
{
    public function __construct(
        private readonly ProductRepository $products,
    ) {
    }

    #[Route('/api/products', name: 'api_products_list', methods: ['GET'], format: 'json')]
    public function __invoke(#[MapQueryString] ListProductsQuery $query = new ListProductsQuery()): JsonResponse
    {
        $result = $this->products->findActivePaginated($query->page, $query->limit);

        return $this->json([
            'data' => array_map(ProductResponse::fromEntity(...), $result['items']),
            'meta' => [
                'page' => $query->page,
                'limit' => $query->limit,
                'total' => $result['total'],
                'totalPages' => (int) ceil($result['total'] / $query->limit),
            ],
        ]);
    }
}
