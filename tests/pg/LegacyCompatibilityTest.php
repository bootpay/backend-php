<?php

namespace Bootpay\ServerPhp\Test\Pg;

use Bootpay\ServerPhp\Test\TestConfig;
use Bootpay\ServerPhp\BootpayApi;
use ReflectionClass;

class LegacyCompatibilityTest extends TestConfig
{
    public function testLegacySetConfigurationStillWorks()
    {
        BootpayApi::setConfiguration('legacy_application_id', 'legacy_private_key', 'development');

        $ref = new ReflectionClass(BootpayApi::class);

        $applicationId = $ref->getProperty('applicationId');
        $applicationId->setAccessible(true);
        $privateKey = $ref->getProperty('privateKey');
        $privateKey->setAccessible(true);
        $clientKey = $ref->getProperty('clientKey');
        $clientKey->setAccessible(true);
        $secretKey = $ref->getProperty('secretKey');
        $secretKey->setAccessible(true);
        $mode = $ref->getProperty('mode');
        $mode->setAccessible(true);

        $this->assertEquals('legacy_application_id', $applicationId->getValue());
        $this->assertEquals('legacy_private_key', $privateKey->getValue());
        $this->assertEquals('', $clientKey->getValue());
        $this->assertEquals('', $secretKey->getValue());
        $this->assertEquals('development', $mode->getValue());
    }

    public function testLegacyTokenRequestHeadersOmitAuthorizationBeforeToken()
    {
        BootpayApi::setConfiguration('legacy_application_id', 'legacy_private_key', 'development');

        $ref = new ReflectionClass(BootpayApi::class);
        $createHeaders = $ref->getMethod('createHeaders');
        $createHeaders->setAccessible(true);

        $headers = $createHeaders->invoke(null);

        $this->assertNotContains('Authorization: ', $headers);
    }

    public function testClientKeyHeadersUseBasicAuthorization()
    {
        BootpayApi::setClientKeyConfiguration('ck', 'sk', 'development');

        $ref = new ReflectionClass(BootpayApi::class);
        $createHeaders = $ref->getMethod('createHeaders');
        $createHeaders->setAccessible(true);

        $headers = $createHeaders->invoke(null);

        $this->assertContains('Authorization: Basic ' . base64_encode('ck:sk'), $headers);
    }

    /**
     * @dataProvider incompletePgCredentials
     */
    public function testIncompleteCredentialPairsAreRejectedBeforeRequest($method, $first, $second)
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('함께 입력해주세요.');

        call_user_func(array(BootpayApi::class, $method), $first, $second, 'development');
    }

    public static function incompletePgCredentials()
    {
        return array(
            'client key only' => array('setClientKeyConfiguration', 'ck', ''),
            'secret key only' => array('setClientKeyConfiguration', '', 'sk'),
            'application id only' => array('setConfiguration', 'application-id', ''),
            'private key only' => array('setConfiguration', '', 'private-key')
        );
    }
}
