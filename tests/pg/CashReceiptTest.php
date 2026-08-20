<?php

namespace Bootpay\ServerPhp\Test\Pg;

use Bootpay\ServerPhp\Test\TestConfig;
use Bootpay\ServerPhp\BootpayApi;

class CashReceiptTest extends TestConfig
{
    public static function setUpBeforeClass(): void
    {
        self::setupPgApiWithToken();
    }

    public function testCashReceiptPublishOnReceipt()
    {
        $response = BootpayApi::cashReceiptPublishOnReceipt(array(
            'receipt_id' => self::TEST_RECEIPT_ID_CASH,
            'identity_no' => '01012345678',
            'cash_receipt_type' => '소득공제',
            'username' => 'TestUser',
            'email' => 'test@example.com',
            'phone' => '01012345678'
        ));
        $this->printResponse('PG cashReceiptPublishOnReceipt', $response);

        $this->assertNotNull($response);
    }

    public function testCashReceiptCancelOnReceipt()
    {
        $response = BootpayApi::cashReceiptCancelOnReceipt(
            self::TEST_RECEIPT_ID_CASH,
            array(
                'cancel_username' => 'TestUser',
                'cancel_message' => 'PHPUnit test cancel'
            )
        );
        $this->printResponse('PG cashReceiptCancelOnReceipt', $response);

        $this->assertNotNull($response);
    }

    public function testRequestCashReceipt()
    {
        $response = BootpayApi::requestCashReceipt(array(
            'pg' => 'nicepay',
            'price' => 1000,
            'order_name' => 'PHPUnit Cash Receipt Test',
            'cash_receipt_type' => '소득공제',
            'identity_no' => '01012345678',
            'order_id' => 'phpunit_cash_' . time(),
            'tax_free' => 0
        ));
        $this->printResponse('PG requestCashReceipt', $response);

        $this->assertNotNull($response);
    }

    public function testCancelCashReceipt()
    {
        $response = BootpayApi::cancelCashReceipt(
            self::TEST_RECEIPT_ID_CASH,
            array(
                'cancel_username' => 'TestUser',
                'cancel_message' => 'PHPUnit test cancel'
            )
        );
        $this->printResponse('PG cancelCashReceipt', $response);

        $this->assertNotNull($response);
    }

    public function testShippingStart()
    {
        $response = BootpayApi::shippingStart(array(
            'receipt_id' => self::TEST_RECEIPT_ID_ESCROW,
            'tracking_number' => '1234567890',
            'delivery_corp' => 'CJ대한통운',
            'shipping_prepayment' => true,
            'user' => array(
                'username' => 'TestUser',
                'phone' => '01012345678'
            ),
            'company' => array(
                'name' => 'Test Company',
                'phone' => '021234567'
            )
        ));
        $this->printResponse('PG shippingStart', $response);

        $this->assertNotNull($response);
    }
}
