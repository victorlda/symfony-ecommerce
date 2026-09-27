<?php

declare(strict_types=1);

namespace App\Tests\Functional\Catalog;

use App\Tests\Functional\ApiTestCase;

final class AdminProductListTest extends ApiTestCase
{
    public function testAdminCanFilterArchivedProducts(): void
    {
        $token = $this->adminToken();
        $this->createProduct($token, 'adm-001', 'Produto Ativo');
        $archivedId = $this->createProduct($token, 'adm-002', 'Produto Arquivado');
        $this->requestJson('DELETE', '/api/products/'.$archivedId, token: $token);

        $this->requestJson('GET', '/api/admin/products?status=archived', token: $token);

        self::assertResponseIsSuccessful();
        $data = $this->responseData()['data'];
        self::assertIsArray($data);
        self::assertCount(1, $data);
        self::assertSame('ADM-002', $data[0]['sku']);
    }

    public function testAllStatusReturnsActiveAndArchived(): void
    {
        $token = $this->adminToken();
        $this->createProduct($token, 'adm-001', 'Produto Ativo');
        $archivedId = $this->createProduct($token, 'adm-002', 'Produto Arquivado');
        $this->requestJson('DELETE', '/api/products/'.$archivedId, token: $token);

        $this->requestJson('GET', '/api/admin/products', token: $token);

        $data = $this->responseData()['data'];
        self::assertIsArray($data);
        self::assertCount(2, $data);
    }

    public function testSearchesByNameOrSku(): void
    {
        $token = $this->adminToken();
        $this->createProduct($token, 'adm-001', 'Camiseta Azul');
        $this->createProduct($token, 'adm-002', 'Calça Jeans');

        $this->requestJson('GET', '/api/admin/products?q=camiseta', token: $token);
        $byName = $this->responseData()['data'];
        self::assertIsArray($byName);
        self::assertCount(1, $byName);

        $this->requestJson('GET', '/api/admin/products?q=adm-002', token: $token);
        $bySku = $this->responseData()['data'];
        self::assertIsArray($bySku);
        self::assertCount(1, $bySku);
    }

    public function testRegularUserIsForbidden(): void
    {
        $this->requestJson('GET', '/api/admin/products', token: $this->userToken());

        self::assertResponseStatusCodeSame(403);
    }

    public function testRejectsInvalidStatus(): void
    {
        $this->requestJson('GET', '/api/admin/products?status=qualquer', token: $this->adminToken());

        self::assertResponseStatusCodeSame(422);
    }

    private function createProduct(string $token, string $sku, string $name): string
    {
        $this->requestJson('POST', '/api/products', [
            'sku' => $sku,
            'name' => $name,
            'priceInCents' => 1990,
        ], $token);
        self::assertResponseStatusCodeSame(201);

        $id = $this->responseData()['id'];
        self::assertIsString($id);

        return $id;
    }
}
