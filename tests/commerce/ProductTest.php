<?php

namespace Bootpay\ServerPhp\Test\Commerce;

use Bootpay\ServerPhp\Test\TestConfig;
use Bootpay\ServerPhp\BootpayCommerceApi;

class ProductTest extends TestConfig
{
    /** @var BootpayCommerceApi */
    private static $api;

    public static function setUpBeforeClass(): void
    {
        self::$api = self::createCommerceApiWithToken();
    }

    public function testProductList()
    {
        $response = self::$api->product->getList(array(
            'page' => 1,
            'limit' => 10
        ));
        $this->printResponse('Commerce product.list', $response);

        $this->assertNotNull($response);
    }

    public function testProductListWithFilter()
    {
        $response = self::$api->product->getList(array(
            'page' => 1,
            'limit' => 5,
            'keyword' => 'test'
        ));
        $this->printResponse('Commerce product.list (keyword)', $response);

        $this->assertNotNull($response);
    }

    public function testProductCreate()
    {
        $response = self::$api->product->create(array(
            'name' => 'PHPUnit Test Product ' . time(),
            'price' => 10000,
            'type' => 1
        ));
        $this->printResponse('Commerce product.create', $response);

        $this->assertNotNull($response);
    }

    public function testProductDetail()
    {
        // First get a product from the list
        $listResponse = self::$api->product->getList(array(
            'page' => 1,
            'limit' => 1
        ));

        if (isset($listResponse->items) && count($listResponse->items) > 0) {
            $productId = $listResponse->items[0]->product_id;
            $response = self::$api->product->detail($productId);
            $this->printResponse('Commerce product.detail', $response);

            $this->assertNotNull($response);
        } else {
            $this->markTestSkipped('No products available for detail test');
        }
    }

    public function testProductUpdate()
    {
        // First get a product from the list
        $listResponse = self::$api->product->getList(array(
            'page' => 1,
            'limit' => 1
        ));

        if (isset($listResponse->items) && count($listResponse->items) > 0) {
            $productId = $listResponse->items[0]->product_id;
            $response = self::$api->product->update(array(
                'product_id' => $productId,
                'name' => 'PHPUnit Updated Product ' . time()
            ));
            $this->printResponse('Commerce product.update', $response);

            $this->assertNotNull($response);
        } else {
            $this->markTestSkipped('No products available for update test');
        }
    }

    public function testProductStatus()
    {
        $listResponse = self::$api->product->getList(array(
            'page' => 1,
            'limit' => 1
        ));

        if (isset($listResponse->items) && count($listResponse->items) > 0) {
            $productId = $listResponse->items[0]->product_id;
            $response = self::$api->product->status(array(
                'product_id' => $productId,
                'status' => 1
            ));
            $this->printResponse('Commerce product.status', $response);

            $this->assertNotNull($response);
        } else {
            $this->markTestSkipped('No products available for status test');
        }
    }
}
