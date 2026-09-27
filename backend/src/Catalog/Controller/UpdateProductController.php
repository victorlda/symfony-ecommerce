<?php

declare(strict_types=1);

namespace App\Catalog\Controller;

use App\Catalog\Application\ProductNotFound;
use App\Catalog\Application\UpdateProduct;
use App\Catalog\Controller\Request\UpdateProductRequest;
use App\Catalog\Controller\Response\ProductResponse;
use App\Shared\Controller\ApiController;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Uid\Uuid;

#[OA\Tag(name: 'Produtos')]
#[IsGranted('ROLE_ADMIN')]
final class UpdateProductController extends ApiController
{
    public function __construct(
        private readonly UpdateProduct $updateProduct,
    ) {
    }

    /**
     * Edita nome, preço e descrição de um produto (somente administradores).
     *
     * O SKU e o slug não podem ser alterados.
     */
    #[Route('/api/products/{id}', name: 'api_products_update', requirements: ['id' => Requirement::UUID], methods: ['PUT'], format: 'json')]
    #[OA\Response(response: 200, description: 'Produto atualizado', content: new Model(type: ProductResponse::class))]
    #[OA\Response(response: 401, description: 'Não autenticado')]
    #[OA\Response(response: 403, description: 'Sem permissão de administrador')]
    #[OA\Response(response: 404, description: 'Produto não encontrado')]
    #[OA\Response(response: 422, description: 'Dados inválidos')]
    public function __invoke(Uuid $id, #[MapRequestPayload] UpdateProductRequest $request): JsonResponse
    {
        try {
            $product = ($this->updateProduct)($id, $request->name, $request->priceInCents, $request->description);
        } catch (ProductNotFound $e) {
            throw new NotFoundHttpException($e->getMessage(), $e);
        }

        return $this->json(ProductResponse::fromEntity($product));
    }
}
