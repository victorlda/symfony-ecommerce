<?php

declare(strict_types=1);

namespace App\Catalog\Controller;

use App\Catalog\Application\ArchiveProduct;
use App\Catalog\Application\ProductNotFound;
use App\Shared\Controller\ApiController;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Uid\Uuid;

#[OA\Tag(name: 'Produtos')]
#[IsGranted('ROLE_ADMIN')]
final class ArchiveProductController extends ApiController
{
    public function __construct(
        private readonly ArchiveProduct $archiveProduct,
    ) {
    }

    /**
     * Arquiva um produto, removendo-o do catálogo (somente administradores).
     *
     * O produto não é apagado do banco, para preservar o histórico de pedidos.
     */
    #[Route('/api/products/{id}', name: 'api_products_archive', requirements: ['id' => Requirement::UUID], methods: ['DELETE'], format: 'json')]
    #[OA\Response(response: 204, description: 'Produto arquivado')]
    #[OA\Response(response: 401, description: 'Não autenticado')]
    #[OA\Response(response: 403, description: 'Sem permissão de administrador')]
    #[OA\Response(response: 404, description: 'Produto não encontrado')]
    public function __invoke(Uuid $id): Response
    {
        try {
            ($this->archiveProduct)($id);
        } catch (ProductNotFound $e) {
            throw new NotFoundHttpException($e->getMessage(), $e);
        }

        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}
