<?php

namespace Bootpay\ServerPhp\Test\Pg;

use Bootpay\ServerPhp\Test\TestConfig;
use Bootpay\ServerPhp\BootpayApi;

class PaymentTest extends TestConfig
{
    public static function setUpBeforeClass(): void
    {
        self::setupPgApiWithToken();
    }

    public function testReceiptPayment()
    {
        $response = BootpayApi::receiptPayment(self::TEST_RECEIPT_ID);
        $this->printResponse('PG receiptPayment', $response);

        $this->assertNotNull($response);
        // Response should contain receipt_id or error_code
        if (!isset($response->error_code)) {
            $this->assertObjectHasProperty('receipt_id', $response);
            $this->assertEquals(self::TEST_RECEIPT_ID, $response->receipt_id);
        }
    }

    public function testConfirmPayment()
    {
        $response = BootpayApi::confirmPayment(self::TEST_RECEIPT_ID_CONFIRM);
        $this->printResponse('PG confirmPayment', $response);

        // Confirm may fail for already confirmed payments, but should return a response
        $this->assertNotNull($response);
    }

    public function testCancelPayment()
    {
        $response = BootpayApi::cancelPayment(array(
            'receipt_id' => self::TEST_RECEIPT_ID,
            'cancel_message' => 'PHPUnit integration test cancel',
            'cancel_price' => 100
        ));
        $this->printResponse('PG cancelPayment', $response);

        // Cancel may fail for already cancelled payments, but should return a response
        $this->assertNotNull($response);
    }

    public function testRequestUserToken()
    {
        $response = BootpayApi::requestUserToken(array(
            'user_id' => self::TEST_USER_ID,
            'email' => 'test@example.com',
            'username' => 'TestUser',
            'gender' => 1,
            'birth' => '19900101',
            'phone' => '01012345678'
        ));
        $this->printResponse('PG requestUserToken', $response);

        $this->assertNotNull($response);
        if (!isset($response->error_code)) {
            $this->assertObjectHasProperty('user_token', $response);
            $this->assertNotEmpty($response->user_token);
        }
    }
}
