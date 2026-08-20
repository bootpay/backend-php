<?php

namespace Bootpay\ServerPhp\Test\Commerce;

use Bootpay\ServerPhp\Test\TestConfig;
use Bootpay\ServerPhp\BootpayCommerceApi;

class StoreTest extends TestConfig
{
    /** @var BootpayCommerceApi */
    private static $api;

    public static function setUpBeforeClass(): void
    {
        self::$api = self::createCommerceApiWithToken();
    }

    public function testStoreInfo()
    {
        $response = self::$api->store->info();
        $this->printResponse('Commerce store.info', $response);

        $this->assertNotNull($response);
    }

    public function testStoreDetail()
    {
        $response = self::$api->store->detail();
        $this->printResponse('Commerce store.detail', $response);

        $this->assertNotNull($response);
    }

    public function testStoreInfoWithManagerRole()
    {
        self::$api->asManager();
        $response = self::$api->store->info();
        $this->printResponse('Commerce store.info (manager)', $response);
        self::$api->clearRole();

        $this->assertNotNull($response);
    }
}
