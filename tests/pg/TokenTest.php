<?php

namespace Bootpay\ServerPhp\Test\Pg;

use Bootpay\ServerPhp\Test\TestConfig;
use Bootpay\ServerPhp\BootpayApi;

class TokenTest extends TestConfig
{
    public function testGetAccessTokenCkSkReturnsSyntheticEmpty()
    {
        // ck/sk 모드: HTTP 호출 없이 합성 응답을 반환한다.
        // BOOTPAY_AUTH_MODE 토글과 무관하게 ck/sk 동작을 검증해야 하므로 직접 setClientKeyConfiguration 호출.
        $env = self::getEnv();
        if ($env === 'production') {
            $clientKey = \Bootpay\ServerPhp\Test\bootpayEnv('BOOTPAY_PG_CLIENT_KEY_PROD', '');
            $secretKey = \Bootpay\ServerPhp\Test\bootpayEnv('BOOTPAY_PG_SECRET_KEY_PROD', '');
        } else {
            $clientKey = \Bootpay\ServerPhp\Test\bootpayEnv('BOOTPAY_PG_CLIENT_KEY_DEV', '');
            $secretKey = \Bootpay\ServerPhp\Test\bootpayEnv('BOOTPAY_PG_SECRET_KEY_DEV', '');
        }
        // 빈 ck/sk 는 getAccessToken 이 legacy 분기로 빠져 실서버 request/token 을 호출한다 — 키 없으면 skip.
        if ($clientKey === '' || $secretKey === '') {
            $this->markTestSkipped('PG client_key/secret_key 미설정 — 합성 응답 경로 검증 불가');
        }
        BootpayApi::setClientKeyConfiguration($clientKey, $secretKey, $env === 'production' ? 'production' : 'development');

        $response = BootpayApi::getAccessToken();
        $this->printResponse('PG getAccessToken (ck/sk)', $response);

        $this->assertNotNull($response);
        $this->assertObjectHasProperty('access_token', $response);
        $this->assertSame('', $response->access_token);
        $this->assertSame(0, $response->expire_in);
        $this->assertEmpty(BootpayApi::$token);
    }

    public function testGetAccessTokenLegacyIssuesRealToken()
    {
        // legacy application_id/private_key 모드: request/token 호출 후 실제 토큰을 발급한다.
        self::setupPgApiLegacy();

        $response = BootpayApi::getAccessToken();
        $this->printResponse('PG getAccessToken (legacy)', $response);

        $this->assertNotNull($response);
        $this->assertObjectNotHasProperty('error_code', $response);
        $this->assertObjectHasProperty('access_token', $response);
        $this->assertNotEmpty($response->access_token);
        $this->assertGreaterThan(0, $response->expire_in);
        $this->assertSame($response->access_token, BootpayApi::$token);
    }
}
