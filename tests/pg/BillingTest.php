<?php

namespace Bootpay\ServerPhp\Test\Pg;

use Bootpay\ServerPhp\Test\TestConfig;
use Bootpay\ServerPhp\BootpayApi;

class BillingTest extends TestConfig
{
    public static function setUpBeforeClass(): void
    {
        self::setupPgApiWithToken();
    }

    public function testLookupSubscribeBillingKey()
    {
        $response = BootpayApi::lookupSubscribeBillingKey(self::TEST_RECEIPT_ID_BILLING);
        $this->printResponse('PG lookupSubscribeBillingKey', $response);

        $this->assertNotNull($response);
    }

    public function testLookupBillingKey()
    {
        $response = BootpayApi::lookupBillingKey(self::TEST_BILLING_KEY);
        $this->printResponse('PG lookupBillingKey', $response);

        $this->assertNotNull($response);
    }

    public function testRequestSubscribeBillingKey()
    {
        // This test uses dummy card data and is expected to fail at PG level,
        // but should successfully communicate with the API.
        $response = BootpayApi::requestSubscribeBillingKey(array(
            'pg' => 'nicepay',
            'subscription_id' => 'test_sub_' . time(),
            'order_name' => 'PHPUnit Test Subscription',
            'card_no' => '4111111111111111',
            'card_pw' => '12',
            'card_identity_no' => '900101',
            'card_expire_year' => '30',
            'card_expire_month' => '12'
        ));
        $this->printResponse('PG requestSubscribeBillingKey', $response);

        $this->assertNotNull($response);
    }

    public function testRequestSubscribePayment()
    {
        // Expected to fail with invalid billing_key, but tests the API call path
        $response = BootpayApi::requestSubscribePayment(array(
            'billing_key' => self::TEST_BILLING_KEY,
            'order_name' => 'PHPUnit Test Payment',
            'price' => 1000,
            'order_id' => 'phpunit_order_' . time(),
            'tax_free' => 0
        ));
        $this->printResponse('PG requestSubscribePayment', $response);

        $this->assertNotNull($response);
    }

    public function testSubscribePaymentReserve()
    {
        $response = BootpayApi::subscribePaymentReserve(array(
            'billing_key' => self::TEST_BILLING_KEY,
            'order_name' => 'PHPUnit Reserve Test',
            'price' => 1000,
            'order_id' => 'phpunit_reserve_' . time(),
            'reserve_execute_at' => date('Y-m-d\TH:i:sP', strtotime('+1 day'))
        ));
        $this->printResponse('PG subscribePaymentReserve', $response);

        $this->assertNotNull($response);
    }

    public function testSubscribePaymentReserveLookup()
    {
        $response = BootpayApi::subscribePaymentReserveLookup(self::TEST_RESERVE_ID);
        $this->printResponse('PG subscribePaymentReserveLookup', $response);

        $this->assertNotNull($response);
    }

    public function testCancelSubscribeReserve()
    {
        $response = BootpayApi::cancelSubscribeReserve(self::TEST_RESERVE_ID);
        $this->printResponse('PG cancelSubscribeReserve', $response);

        $this->assertNotNull($response);
    }

    public function testDestroyBillingKey()
    {
        $response = BootpayApi::destroyBillingKey(self::TEST_BILLING_KEY);
        $this->printResponse('PG destroyBillingKey', $response);

        $this->assertNotNull($response);
    }

    public function testRequestSubscribeAutomaticTransferBillingKey()
    {
        $response = BootpayApi::requestSubscribeAutomaticTransferBillingKey(array(
            'pg' => 'nicepay',
            'order_name' => 'PHPUnit Auto Transfer Test',
            'subscription_id' => 'test_auto_' . time(),
            'auth_type' => 'ARS',
            'username' => 'TestUser',
            'bank_name' => 'KB',
            'bank_account' => '12345678901234',
            'identity_no' => '900101',
            'phone' => '01012345678'
        ));
        $this->printResponse('PG requestSubscribeAutomaticTransferBillingKey', $response);

        $this->assertNotNull($response);
    }

    public function testPublishAutomaticTransferBillingKey()
    {
        $response = BootpayApi::publishAutomaticTransferBillingKey(self::TEST_RECEIPT_ID_TRANSFER);
        $this->printResponse('PG publishAutomaticTransferBillingKey', $response);

        $this->assertNotNull($response);
    }
}
