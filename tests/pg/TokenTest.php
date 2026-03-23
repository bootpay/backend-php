<?php

namespace Bootpay\ServerPhp\Test\Pg;

use Bootpay\ServerPhp\Test\TestConfig;
use Bootpay\ServerPhp\BootpayApi;

class TokenTest extends TestConfig
{
    public function testGetAccessToken()
    {
        self::setupPgApi();

        $response = BootpayApi::getAccessToken();
        $this->printResponse('PG getAccessToken', $response);

        $this->assertNotNull($response);
        $this->assertObjectHasProperty('access_token', $response);
        $this->assertNotEmpty($response->access_token);
    }

    public function testGetAccessTokenSetsStaticToken()
    {
        self::setupPgApi();

        $response = BootpayApi::getAccessToken();
        $this->assertNotNull($response);
        $this->assertNotEmpty(BootpayApi::$token);
        $this->assertEquals($response->access_token, BootpayApi::$token);
    }
}
