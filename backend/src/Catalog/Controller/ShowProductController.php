<?php

declare(strict_types=1);

namespace App\Catalog\Controller;

use App\Catalog\Controller\Response\ProductResponse;
use App\Catalog\Repository\ProductRepository;
use App\Shared\Controller\ApiController;
use Nelmio\ApiDocBundle\Attribute\Model;
use Nelmio\ApiDocBundle\Attribute\Security;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Uid\Uuid;

#[OA\Tag(name: 'Produtos')]
final class ShowProductController extends ApiController
{
    public function __construct(
        private readonly ProductRepository $products,
    ) {
    }

    /**
     * Retorna os detalhes de um produto ativo.
     */
    #[Route('/api/products/{id}', name: 'api_products_show', requirements: ['id' => Requirement::UUID], methods: ['GET'], format: 'json')]
    #[Security(name: null)]
    #[OA\Response(response: 200, description: 'Produto encontrado', content: new Model(type: ProductResponse::class))]
    #[OA\Response(response: 404, description: 'Produto não encontrado ou arquivado')]
    public function __invoke(Uuid $id): JsonResponse
    {
        $product = $this->products->find($id);

        if (null === $product || !$product->isActive()) {
            throw new NotFoundHttpException('Produto não encontrado.');
        }

        return $this->json(ProductResponse::fromEntity($product));
    }
}
