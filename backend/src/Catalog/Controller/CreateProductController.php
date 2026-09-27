<?php

declare(strict_types=1);

namespace App\Catalog\Controller;

use App\Catalog\Application\CreateProduct;
use App\Catalog\Application\SkuAlreadyInUse;
use App\Catalog\Controller\Request\CreateProductRequest;
use App\Catalog\Controller\Response\ProductResponse;
use App\Shared\Controller\ApiController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
final class CreateProductController extends ApiController
{
    public function __construct(
        private readonly CreateProduct $createProduct,
    ) {
    }

    #[Route('/api/products', name: 'api_products_create', methods: ['POST'], format: 'json')]
    public function __invoke(#[MapRequestPayload] CreateProductRequest $request): JsonResponse
    {
        try {
            $product = ($this->createProduct)(
                $request->sku,
                $request->name,
                $request->priceInCents,
                $request->description,
            );
        } catch (SkuAlreadyInUse $e) {
            throw new ConflictHttpException($e->getMessage(), $e);
        }

        return $this->json(
            ProductResponse::fromEntity($product),
            Response::HTTP_CREATED,
            ['Location' => $this->generateUrl('api_products_show', ['id' => $product->getId()->toRfc4122()])],
        );
    }
}
