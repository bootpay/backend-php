<?php

namespace Bootpay\ServerPhp\Test\Commerce;

use Bootpay\ServerPhp\Test\TestConfig;
use Bootpay\ServerPhp\BootpayCommerceApi;

class TokenTest extends TestConfig
{
    public function testGetAccessToken()
    {
        $api = self::createCommerceApi();

        $response = $api->getAccessToken();
        $this->printResponse('Commerce getAccessToken', $response);

        $this->assertNotNull($response);
        $this->assertTrue($api->hasToken());
        $this->assertNotEmpty($api->getToken());
    }

    public function testWithToken()
    {
        $api = self::createCommerceApi();

        $result = $api->withToken();
        $this->printResponse('Commerce withToken', $result);

        // withToken() returns $this for method chaining
        $this->assertInstanceOf(BootpayCommerceApi::class, $result);
        $this->assertTrue($api->hasToken());
    }

    public function testRoleSettings()
    {
        $api = self::createCommerceApi();

        $this->assertEquals('user', $api->getRole());

        $api->asManager();
        $this->assertEquals('manager', $api->getRole());

        $api->asPartner();
        $this->assertEquals('partner', $api->getRole());

        $api->asVendor();
        $this->assertEquals('vendor', $api->getRole());

        $api->asSupervisor();
        $this->assertEquals('supervisor', $api->getRole());

        $api->clearRole();
        $this->assertEquals('user', $api->getRole());
    }
}
