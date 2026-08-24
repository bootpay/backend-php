<?php

namespace Bootpay\ServerPhp\Test\Pg;

use Bootpay\ServerPhp\BootpayApi;
use Bootpay\ServerPhp\Test\WireEchoTestCase;
use ReflectionProperty;

require_once __DIR__ . '/../WireEchoTestCase.php';

/**
 * PG 공통 계층(curl)이 실제로 전송하는 wire 검증 — 로컬 echo 서버 사용, 외부 호출 없음.
 * BootpayApi 는 request 가 private static 이라 mock 주입이 불가하므로
 * API_URL 을 echo 서버로 돌려 실제 요청 URL·헤더를 검증한다.
 */
class PgHttpWireTest extends WireEchoTestCase
{
    /** @var array 원본 API_URL (테스트 후 복원) */
    private static $originalApiUrl;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        $prop = self::apiUrlProperty();
        self::$originalApiUrl = $prop->getValue();
        $redirected = self::$originalApiUrl;
        $redirected['development'] = self::echoServerUrl('/v2');
        $prop->setValue(null, $redirected);
    }

    public static function tearDownAfterClass(): void
    {
        self::apiUrlProperty()->setValue(null, self::$originalApiUrl);
        parent::tearDownAfterClass();
    }

    private static function apiUrlProperty()
    {
        $prop = new ReflectionProperty(BootpayApi::class, 'API_URL');
        if (PHP_VERSION_ID < 80100) {
            $prop->setAccessible(true);
        }
        return $prop;
    }

    public function testLookupSequentialBillingKeyWireFormat()
    {
        BootpayApi::setClientKeyConfiguration('ck', 'sk', 'development');

        $res = BootpayApi::lookupSequentialBillingKey('widget key', 'bk-1', 'user/1');

        $this->assertEquals('GET', $res->method);
        // NodeJS 와 동일하게 쿼리 값은 RFC3986(%20) 인코딩, billing_key 는 경로에 위치
        $this->assertEquals(
            '/v2/subscribe/sequential_billing_key/bk-1?widget_key=widget%20key&user_id=user%2F1',
            $res->uri
        );
        $this->assertEquals('Basic ' . base64_encode('ck:sk'), self::echoHeader($res, 'Authorization'));
        $this->assertEquals('application/json', self::echoHeader($res, 'Content-Type'));
    }

    public function testExistingReceiptPaymentWireUnchanged()
    {
        BootpayApi::setClientKeyConfiguration('ck', 'sk', 'development');

        $res = BootpayApi::receiptPayment('receipt-1');

        $this->assertEquals('GET', $res->method);
        $this->assertEquals('/v2/receipt/receipt-1', $res->uri);
        $this->assertEquals('Basic ' . base64_encode('ck:sk'), self::echoHeader($res, 'Authorization'));
    }

    public function testLegacyCredentialsFetchTokenThenSendBearerAuthorization()
    {
        BootpayApi::setConfiguration('application-id', 'private-key', 'development');

        $tokenResponse = BootpayApi::getAccessToken();
        $res = BootpayApi::receiptPayment('receipt-legacy');

        $this->assertEquals('echo-test-token', $tokenResponse->access_token);
        $this->assertEquals('Bearer echo-test-token', self::echoHeader($res, 'Authorization'));
    }
}
