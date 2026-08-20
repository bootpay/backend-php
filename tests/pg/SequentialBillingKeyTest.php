<?php

namespace Bootpay\ServerPhp\Test\Pg;

use Bootpay\ServerPhp\BootpayApi;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

/**
 * lookupSequentialBillingKey 시그니처 검증 (네트워크 미사용)
 *
 * BootpayApi 의 request 계층이 private static 이라 wire-format 캡처가 불가하다.
 * URL 규약(GET subscribe/sequential_billing_key/{billing_key}?widget_key=&user_id=)의
 * 라이브 검증은 tests/pg/LookupSequentialBillingKey.php (development 전용 스크립트)로 수행한다.
 */
class SequentialBillingKeyTest extends TestCase
{
    public function testLookupSequentialBillingKeyExistsWithThreeRequiredParameters()
    {
        $this->assertTrue(method_exists(BootpayApi::class, 'lookupSequentialBillingKey'));

        $method = new ReflectionMethod(BootpayApi::class, 'lookupSequentialBillingKey');
        $this->assertTrue($method->isStatic());
        $this->assertTrue($method->isPublic());
        $this->assertEquals(3, $method->getNumberOfRequiredParameters());

        $names = array_map(function ($param) {
            return $param->getName();
        }, $method->getParameters());
        $this->assertEquals(array('widgetKey', 'billingKey', 'userId'), $names);
    }
}
