<?php

declare(strict_types=1);

namespace App\Catalog\Controller;

use App\Catalog\Controller\Response\ProductResponse;
use App\Catalog\Repository\ProductRepository;
use App\Shared\Controller\ApiController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Uid\Uuid;

final class ShowProductController extends ApiController
{
    public function __construct(
        private readonly ProductRepository $products,
    ) {
    }

    #[Route('/api/products/{id}', name: 'api_products_show', requirements: ['id' => Requirement::UUID], methods: ['GET'], format: 'json')]
    public function __invoke(Uuid $id): JsonResponse
    {
        $product = $this->products->find($id);

        if (null === $product || !$product->isActive()) {
            throw new NotFoundHttpException('Produto não encontrado.');
        }

        return $this->json(ProductResponse::fromEntity($product));
    }
}
