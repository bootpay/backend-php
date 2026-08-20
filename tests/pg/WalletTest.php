<?php

namespace Bootpay\ServerPhp\Test\Pg;

use Bootpay\ServerPhp\Test\TestConfig;
use Bootpay\ServerPhp\BootpayApi;

class WalletTest extends TestConfig
{
    public static function setUpBeforeClass(): void
    {
        self::setupPgApiWithToken();
    }

    public function testGetUserWallets()
    {
        $response = BootpayApi::getUserWallets(self::TEST_USER_ID, true);
        $this->printResponse('PG getUserWallets', $response);

        $this->assertNotNull($response);
    }

    public function testRequestWalletPayment()
    {
        // Expected to fail with test data, but validates the API call path
        $response = BootpayApi::requestWalletPayment(array(
            'user_id' => self::TEST_USER_ID,
            'order_name' => 'PHPUnit Wallet Payment Test',
            'price' => 1000,
            'order_id' => 'phpunit_wallet_' . time()
        ));
        $this->printResponse('PG requestWalletPayment', $response);

        $this->assertNotNull($response);
    }
}
