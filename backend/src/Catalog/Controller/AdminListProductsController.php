<?php

declare(strict_types=1);

namespace App\Catalog\Controller;

use App\Catalog\Controller\Request\AdminListProductsQuery;
use App\Catalog\Controller\Response\ProductListResponse;
use App\Catalog\Controller\Response\ProductResponse;
use App\Catalog\Repository\ProductRepository;
use App\Shared\Controller\ApiController;
use App\Shared\Controller\Response\PaginationMeta;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[OA\Tag(name: 'Produtos (admin)')]
#[IsGranted('ROLE_ADMIN')]
final class AdminListProductsController extends ApiController
{
    public function __construct(
        private readonly ProductRepository $products,
    ) {
    }

    /**
     * Lista todos os produtos, incluindo os arquivados (somente administradores).
     *
     * Permite filtrar por status e buscar por parte do nome ou do SKU.
     */
    #[Route('/api/admin/products', name: 'api_admin_products_list', methods: ['GET'], format: 'json')]
    #[OA\Response(response: 200, description: 'Página de produtos', content: new Model(type: ProductListResponse::class))]
    #[OA\Response(response: 401, description: 'Não autenticado')]
    #[OA\Response(response: 403, description: 'Sem permissão de administrador')]
    #[OA\Response(response: 422, description: 'Parâmetros inválidos')]
    public function __invoke(#[MapQueryString(validationFailedStatusCode: Response::HTTP_UNPROCESSABLE_ENTITY)] AdminListProductsQuery $query = new AdminListProductsQuery()): JsonResponse
    {
        $result = $this->products->findForAdmin($query->activeFilter(), $query->q, $query->page, $query->limit);

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
