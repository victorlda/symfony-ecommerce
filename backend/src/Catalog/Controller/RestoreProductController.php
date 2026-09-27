<?php

declare(strict_types=1);

namespace App\Catalog\Controller;

use App\Catalog\Application\ProductNotFound;
use App\Catalog\Application\RestoreProduct;
use App\Catalog\Controller\Response\ProductResponse;
use App\Shared\Controller\ApiController;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Uid\Uuid;

#[OA\Tag(name: 'Produtos')]
#[IsGranted('ROLE_ADMIN')]
final class RestoreProductController extends ApiController
{
    public function __construct(
        private readonly RestoreProduct $restoreProduct,
    ) {
    }

    /**
     * Reativa um produto arquivado, devolvendo-o ao catálogo (somente administradores).
     */
    #[Route('/api/products/{id}/restore', name: 'api_products_restore', requirements: ['id' => Requirement::UUID], methods: ['POST'], format: 'json')]
    #[OA\Response(response: 200, description: 'Produto reativado', content: new Model(type: ProductResponse::class))]
    #[OA\Response(response: 401, description: 'Não autenticado')]
    #[OA\Response(response: 403, description: 'Sem permissão de administrador')]
    #[OA\Response(response: 404, description: 'Produto não encontrado')]
    public function __invoke(Uuid $id): JsonResponse
    {
        try {
            $product = ($this->restoreProduct)($id);
        } catch (ProductNotFound $e) {
            throw new NotFoundHttpException($e->getMessage(), $e);
        }

        return $this->json(ProductResponse::fromEntity($product));
    }
}
