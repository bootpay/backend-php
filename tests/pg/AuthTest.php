<?php

namespace Bootpay\ServerPhp\Test\Pg;

use Bootpay\ServerPhp\Test\TestConfig;
use Bootpay\ServerPhp\BootpayApi;

class AuthTest extends TestConfig
{
    public static function setUpBeforeClass(): void
    {
        self::setupPgApiWithToken();
    }

    public function testCertificate()
    {
        $response = BootpayApi::certificate(self::TEST_CERTIFICATE_RECEIPT_ID);
        $this->printResponse('PG certificate', $response);

        $this->assertNotNull($response);
    }

    public function testRequestAuthentication()
    {
        $response = BootpayApi::requestAuthentication(array(
            'authentication_id' => 'test_auth_' . time(),
            'pg' => 'danal',
            'method' => 'sms',
            'username' => 'TestUser',
            'identity_no' => '900101',
            'carrier' => 'SKT',
            'phone' => '01012345678',
            'client_ip' => '127.0.0.1',
            'order_name' => 'PHPUnit Auth Test'
        ));
        $this->printResponse('PG requestAuthentication', $response);

        $this->assertNotNull($response);
    }

    public function testConfirmAuthentication()
    {
        // Expected to fail with invalid receipt_id, but tests the API call path
        $response = BootpayApi::confirmAuthentication(
            self::TEST_CERTIFICATE_RECEIPT_ID,
            '123456'
        );
        $this->printResponse('PG confirmAuthentication', $response);

        $this->assertNotNull($response);
    }

    public function testRealarmAuthentication()
    {
        $response = BootpayApi::realarmAuthentication(self::TEST_CERTIFICATE_RECEIPT_ID);
        $this->printResponse('PG realarmAuthentication', $response);

        $this->assertNotNull($response);
    }
}
