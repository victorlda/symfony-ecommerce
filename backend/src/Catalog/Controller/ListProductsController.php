<?php

declare(strict_types=1);

namespace App\Catalog\Controller;

use App\Catalog\Controller\Request\ListProductsQuery;
use App\Catalog\Controller\Response\ProductListResponse;
use App\Catalog\Controller\Response\ProductResponse;
use App\Catalog\Repository\ProductRepository;
use App\Shared\Controller\ApiController;
use App\Shared\Controller\Response\PaginationMeta;
use Nelmio\ApiDocBundle\Attribute\Model;
use Nelmio\ApiDocBundle\Attribute\Security;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;

#[OA\Tag(name: 'Produtos')]
final class ListProductsController extends ApiController
{
    public function __construct(
        private readonly ProductRepository $products,
    ) {
    }

    /**
     * Lista os produtos ativos do catálogo, com paginação.
     */
    #[Route('/api/products', name: 'api_products_list', methods: ['GET'], format: 'json')]
    #[Security(name: null)]
    #[OA\Response(response: 200, description: 'Página de produtos', content: new Model(type: ProductListResponse::class))]
    #[OA\Response(response: 422, description: 'Parâmetros de paginação inválidos')]
    public function __invoke(#[MapQueryString] ListProductsQuery $query = new ListProductsQuery()): JsonResponse
    {
        $result = $this->products->findActivePaginated($query->page, $query->limit);

        return $this->json(new ProductListResponse(
            data: array_map(ProductResponse::fromEntity(...), $result['items']),
            meta: new PaginationMeta(
                page: $query->page,
                limit: $query->limit,
                total: $result['total'],
                totalPages: (int) ceil($result['total'] / $query->limit),
            ),
        ));
    }
}
