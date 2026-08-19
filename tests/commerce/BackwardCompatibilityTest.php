<?php

namespace Bootpay\ServerPhp\Test\Commerce;

use Bootpay\ServerPhp\BootpayApi;
use Bootpay\ServerPhp\BootpayCommerceApi;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

/**
 * v2.4.1 공개 API 표면 동결 계약 — 2.5.0 변경이 기존 호출을 깨지 않는지 검증한다.
 *
 * 규칙: 기존 public 메서드는 (1) 계속 존재하고 (2) public 이며
 * (3) 필수 파라미터 수가 2.4.1 보다 늘어나면 안 된다 (선택 인자 추가만 허용).
 * 아래 표는 v2.4.1 (commit afc5293) 의 실제 시그니처에서 동결한 것이다.
 */
class BackwardCompatibilityTest extends TestCase
{
    /** BootpayApi(PG) — 메서드명 => v2.4.1 필수 파라미터 수 */
    private static $pgContract = array(
        'setConfiguration' => 2,
        'setClientKeyConfiguration' => 2,
        'getAccessToken' => 0,
        'receiptPayment' => 1,
        'cancelPayment' => 1,
        'certificate' => 1,
        'confirmPayment' => 1,
        'lookupSubscribeBillingKey' => 1,
        'requestSubscribeBillingKey' => 1,
        'requestSubscribeCardPayment' => 1,
        'destroyBillingKey' => 1,
        'requestUserToken' => 1,
        'subscribePaymentReserve' => 1,
        'cancelSubscribeReserve' => 1,
        'shippingStart' => 1,
        'requestSubscribePayment' => 1,
        'requestSubscribeAutomaticTransferBillingKey' => 1,
        'publishAutomaticTransferBillingKey' => 1,
        'lookupBillingKey' => 1,
        'subscribePaymentReserveLookup' => 1,
        'requestAuthentication' => 1,
        'confirmAuthentication' => 1,
        'realarmAuthentication' => 1,
        'cashReceiptPublishOnReceipt' => 1,
        'cashReceiptCancelOnReceipt' => 1,
        'requestCashReceipt' => 1,
        'cancelCashReceipt' => 1,
        'getUserWallets' => 1,
        'requestWalletPayment' => 1
    );

    /** BootpayCommerceApi facade — 메서드명 => v2.4.1 필수 파라미터 수 */
    private static $commerceApiContract = array(
        'setConfiguration' => 2,
        'setToken' => 1,
        'getToken' => 0,
        'hasToken' => 0,
        'setRole' => 1,
        'getRole' => 0,
        'withRole' => 1,
        'asUser' => 0,
        'asManager' => 0,
        'asPartner' => 0,
        'asVendor' => 0,
        'asSupervisor' => 0,
        'clearRole' => 0,
        'request' => 2,
        'requestMultipart' => 2,
        'get' => 1,
        'post' => 1,
        'postWithBasicAuth' => 1,
        'put' => 1,
        'delete' => 1,
        'getAccessToken' => 0,
        'withToken' => 0
    );

    /** Commerce 모듈 — 클래스 => (메서드명 => v2.4.1 필수 파라미터 수) */
    private static $moduleContract = array(
        'user' => array(
            'token' => 1,
            'join' => 1,
            'checkExist' => 2,
            'authenticationData' => 1,
            'login' => 2,
            'getList' => 0,
            'detail' => 1,
            'update' => 1,
            'delete' => 1
        ),
        'userGroup' => array(
            'create' => 1,
            'getList' => 0,
            'detail' => 1,
            'update' => 1,
            'userCreate' => 2,
            'userDelete' => 2,
            'limit' => 1,
            'aggregateTransaction' => 1
        ),
        'product' => array(
            'getList' => 0,
            'create' => 1,
            'detail' => 1,
            'update' => 1,
            'status' => 1,
            'delete' => 1
        ),
        'invoice' => array(
            'getList' => 0,
            'create' => 1,
            'notify' => 2, // 2.5.0 에서 1 로 완화 (sendTypes 선택 인자화) — 완화는 호환
            'detail' => 1
        ),
        'order' => array(
            'getList' => 0,
            'detail' => 1,
            'month' => 2
        ),
        'orderCancel' => array(
            'getList' => 0,
            'request' => 1,
            'withdraw' => 1,
            'approve' => 1,
            'reject' => 1
        ),
        'orderSubscription' => array(
            'getList' => 0,
            'detail' => 1,
            'update' => 1,
            'supervisorApprove' => 1,
            'supervisorReject' => 1,
            'supervisorTerminate' => 1,
            'supervisorPause' => 1,
            'supervisorResume' => 1
        ),
        'orderSubscriptionBill' => array(
            'getList' => 0,
            'detail' => 1,
            'update' => 1
        ),
        'orderSubscriptionAdjustment' => array(
            'create' => 2,
            'update' => 1,
            'delete' => 2
        ),
        'orderSubscriptionRequest' => array(
            'getList' => 0,
            'detail' => 1,
            'update' => 1
        ),
        'store' => array(
            'info' => 0,
            'detail' => 0
        ),
        'category' => array(
            'getList' => 0,
            'detail' => 1,
            'create' => 1,
            'update' => 1,
            'destroy' => 1
        ),
        'coupon' => array(
            'getList' => 0,
            'available' => 0,
            'download' => 1
        ),
        'point' => array(
            'balance' => 0,
            'transactions' => 0
        ),
        'cart' => array(
            'orderPreview' => 0
        )
    );

    public function testPgApiSurfaceIsBackwardCompatible()
    {
        foreach (self::$pgContract as $method => $requiredIn241) {
            $this->assertTrue(
                method_exists(BootpayApi::class, $method),
                "BootpayApi::{$method} 이 제거되면 안 된다"
            );
            $ref = new ReflectionMethod(BootpayApi::class, $method);
            $this->assertTrue($ref->isPublic() && $ref->isStatic(), "BootpayApi::{$method} 은 public static 이어야 한다");
            $this->assertLessThanOrEqual(
                $requiredIn241,
                $ref->getNumberOfRequiredParameters(),
                "BootpayApi::{$method} 에 필수 파라미터가 추가되면 기존 호출이 깨진다"
            );
        }
    }

    public function testCommerceApiSurfaceIsBackwardCompatible()
    {
        foreach (self::$commerceApiContract as $method => $requiredIn241) {
            $this->assertTrue(
                method_exists(BootpayCommerceApi::class, $method),
                "BootpayCommerceApi::{$method} 이 제거되면 안 된다"
            );
            $ref = new ReflectionMethod(BootpayCommerceApi::class, $method);
            $this->assertTrue($ref->isPublic(), "BootpayCommerceApi::{$method} 은 public 이어야 한다");
            $this->assertLessThanOrEqual(
                $requiredIn241,
                $ref->getNumberOfRequiredParameters(),
                "BootpayCommerceApi::{$method} 에 필수 파라미터가 추가되면 기존 호출이 깨진다"
            );
        }
    }

    public function testCommerceModuleSurfaceIsBackwardCompatible()
    {
        $api = new BootpayCommerceApi('ck', 'sk', 'development');

        foreach (self::$moduleContract as $property => $methods) {
            $this->assertTrue(property_exists($api, $property), "BootpayCommerceApi->{$property} 모듈이 제거되면 안 된다");
            $module = $api->{$property};
            $this->assertIsObject($module, "BootpayCommerceApi->{$property} 는 모듈 인스턴스여야 한다");

            foreach ($methods as $method => $requiredIn241) {
                $this->assertTrue(
                    method_exists($module, $method),
                    get_class($module) . "::{$method} 이 제거되면 안 된다"
                );
                $ref = new ReflectionMethod($module, $method);
                $this->assertTrue($ref->isPublic(), get_class($module) . "::{$method} 은 public 이어야 한다");
                $this->assertLessThanOrEqual(
                    $requiredIn241,
                    $ref->getNumberOfRequiredParameters(),
                    get_class($module) . "::{$method} 에 필수 파라미터가 추가되면 기존 호출이 깨진다"
                );
            }
        }
    }

    public function testRequestIngSurfaceIsBackwardCompatible()
    {
        $api = new BootpayCommerceApi('ck', 'sk', 'development');
        $ing = $api->orderSubscription->requestIng;
        $this->assertIsObject($ing);

        $contract = array(
            'pause' => 1,
            'resume' => 1,
            'calculateTerminationFee' => 0,
            'calculateTerminationFeeByOrderNumber' => 1,
            'termination' => 1
        );
        foreach ($contract as $method => $requiredIn241) {
            $this->assertTrue(method_exists($ing, $method), "requestIng::{$method} 이 제거되면 안 된다");
            $ref = new ReflectionMethod($ing, $method);
            $this->assertLessThanOrEqual($requiredIn241, $ref->getNumberOfRequiredParameters());
        }
    }

    public function testNewModulesAreWiredUp()
    {
        $api = new BootpayCommerceApi('ck', 'sk', 'development');
        $this->assertInstanceOf('Bootpay\ServerPhp\Commerce\MallSettingModule', $api->mallSetting);
        $this->assertInstanceOf('Bootpay\ServerPhp\Commerce\WebhookModule', $api->webhook);
    }
}
