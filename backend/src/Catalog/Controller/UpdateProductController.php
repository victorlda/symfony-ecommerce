<?php

declare(strict_types=1);

namespace App\Catalog\Controller;

use App\Catalog\Application\ProductNotFound;
use App\Catalog\Application\UpdateProduct;
use App\Catalog\Controller\Request\UpdateProductRequest;
use App\Catalog\Controller\Response\ProductResponse;
use App\Shared\Controller\ApiController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Uid\Uuid;

#[IsGranted('ROLE_ADMIN')]
final class UpdateProductController extends ApiController
{
    public function __construct(
        private readonly UpdateProduct $updateProduct,
    ) {
    }

    #[Route('/api/products/{id}', name: 'api_products_update', requirements: ['id' => Requirement::UUID], methods: ['PUT'], format: 'json')]
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
