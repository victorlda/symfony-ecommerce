<?php

declare(strict_types=1);

namespace App\Tests\Functional\Catalog;

use App\Tests\Functional\ApiTestCase;

final class ProductApiTest extends ApiTestCase
{
    private const PRODUCT = [
        'sku' => 'tst-001',
        'name' => 'Produto de Teste',
        'priceInCents' => 1990,
        'description' => 'Descrição do produto',
    ];

    public function testAdminCanCreateProduct(): void
    {
        $this->requestJson('POST', '/api/products', self::PRODUCT, $this->adminToken());

        self::assertResponseStatusCodeSame(201);
        self::assertSame('TST-001', $this->responseData()['sku']);
        self::assertStringStartsWith('/api/products/', (string) $this->client->getResponse()->headers->get('Location'));
    }

    public function testRegularUserCannotCreateProduct(): void
    {
        $this->requestJson('POST', '/api/products', self::PRODUCT, $this->userToken());

        self::assertResponseStatusCodeSame(403);
    }

    public function testAnonymousCannotCreateProduct(): void
    {
        $this->requestJson('POST', '/api/products', self::PRODUCT);

        self::assertResponseStatusCodeSame(401);
    }

    public function testDuplicateSkuReturnsConflict(): void
    {
        $token = $this->adminToken();
        $this->requestJson('POST', '/api/products', self::PRODUCT, $token);
        $this->requestJson('POST', '/api/products', self::PRODUCT, $token);

        self::assertResponseStatusCodeSame(409);
    }

    public function testCatalogIsPublic(): void
    {
        $this->requestJson('GET', '/api/products');

        self::assertResponseIsSuccessful();
    }

    public function testArchivedProductDisappearsFromCatalog(): void
    {
        $token = $this->adminToken();
        $this->requestJson('POST', '/api/products', self::PRODUCT, $token);
        $id = $this->responseData()['id'];
        self::assertIsString($id);

        $this->requestJson('DELETE', '/api/products/'.$id, token: $token);
        self::assertResponseStatusCodeSame(204);

        $this->requestJson('GET', '/api/products/'.$id);
        self::assertResponseStatusCodeSame(404);

        $this->requestJson('GET', '/api/products');
        $meta = $this->responseData()['meta'];
        self::assertIsArray($meta);
        self::assertSame(0, $meta['total']);
    }

    public function testArchivedProductCanBeRestored(): void
    {
        $token = $this->adminToken();
        $this->requestJson('POST', '/api/products', self::PRODUCT, $token);
        $id = $this->responseData()['id'];
        self::assertIsString($id);
        $this->requestJson('DELETE', '/api/products/'.$id, token: $token);

        $this->requestJson('POST', '/api/products/'.$id.'/restore', token: $token);
        self::assertResponseIsSuccessful();
        self::assertTrue($this->responseData()['active']);

        $this->requestJson('GET', '/api/products/'.$id);
        self::assertResponseIsSuccessful();
    }

    public function testRegularUserCannotRestoreProduct(): void
    {
        $adminToken = $this->adminToken();
        $this->requestJson('POST', '/api/products', self::PRODUCT, $adminToken);
        $id = $this->responseData()['id'];
        self::assertIsString($id);

        $this->requestJson('POST', '/api/products/'.$id.'/restore', token: $this->userToken());

        self::assertResponseStatusCodeSame(403);
    }
}
