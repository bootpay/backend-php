<?php

namespace Bootpay\ServerPhp\Test\Commerce;

use Bootpay\ServerPhp\BootpayApi;
use Bootpay\ServerPhp\BootpayCommerceApi;
use Bootpay\ServerPhp\Test\RecordingCommerceApi;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

require_once __DIR__ . '/../RecordingCommerceApi.php';

/**
 * commerce-api 가 supervisor / manager scope 를 요구하는 엔드포인트의 BOOTPAY-ROLE 검증.
 * 헤더를 붙이지 않으면 인스턴스 기본값 user 로 조용히 나가고 서버가 scope_invalid! 로 거절한다.
 * (네트워크 미사용)
 */
class CommerceScopeTest extends TestCase
{
    /** @var RecordingCommerceApi */
    private $api;

    protected function setUp(): void
    {
        $this->api = new RecordingCommerceApi('ck', 'sk', 'development');
    }

    private function assertLastCall($method, $url, $role)
    {
        $call = $this->api->lastCall();
        $this->assertSame($method, $call['method']);
        $this->assertSame($url, $call['url']);
        $this->assertSame($role, RecordingCommerceApi::headerValue($call, 'BOOTPAY-ROLE'));
        $this->assertNotEmpty(RecordingCommerceApi::headerValue($call, 'Idempotency-Key'));
    }

    public function testSupervisorSubscriptionActionsSendSupervisorRole()
    {
        $this->api->orderSubscription->supervisorApprove('s1', array('reason' => '승인'));
        $this->assertLastCall('PUT', 'order_subscriptions/s1/approve', 'supervisor');

        $this->api->orderSubscription->supervisorReject('s1', array('reason' => '반려'));
        $this->assertLastCall('PUT', 'order_subscriptions/s1/reject', 'supervisor');

        $this->api->orderSubscription->supervisorTerminate('s1', array('reason' => '해지'));
        $this->assertLastCall('PUT', 'order_subscriptions/s1/terminate', 'supervisor');

        $this->api->orderSubscription->supervisorPause('s1', array('paused_at' => '2026-01-01'));
        $this->assertLastCall('PUT', 'order_subscriptions/s1/pause', 'supervisor');

        $this->api->orderSubscription->supervisorResume('s1');
        $this->assertLastCall('PUT', 'order_subscriptions/s1/resume', 'supervisor');
    }

    public function testCategoryWritesSendSupervisorRole()
    {
        $this->api->category->create(array('name' => '카테고리'));
        $this->assertLastCall('POST', 'categories', 'supervisor');

        $this->api->category->update(array('category_id' => 'c1', 'name' => '변경'));
        $this->assertLastCall('PUT', 'categories/c1', 'supervisor');

        $this->api->category->destroy('c1');
        $this->assertLastCall('DELETE', 'categories/c1', 'supervisor');
    }

    public function testUserGroupMembershipSendsManagerRole()
    {
        $this->api->userGroup->userCreate('g1', 'u1');
        $this->assertLastCall('POST', 'user-groups/g1/user', 'manager');

        $this->api->userGroup->userDelete('g1', 'u1');
        $this->assertLastCall('DELETE', 'user-groups/g1/user/u1', 'manager');
    }

    public function testExplicitIdempotencyKeyIsForwardedAndKeptOutOfBody()
    {
        $this->api->category->create(array('name' => '카테고리', 'idempotency_key' => 'fixed-key'));
        $call = $this->api->lastCall();

        $this->assertSame('fixed-key', RecordingCommerceApi::headerValue($call, 'Idempotency-Key'));
        $this->assertSame(array('name' => '카테고리'), $call['data']);

        $this->api->userGroup->userCreate('g1', 'u1', 'member-key');
        $this->assertSame('member-key', RecordingCommerceApi::headerValue($this->api->lastCall(), 'Idempotency-Key'));

        $this->api->orderSubscription->supervisorApprove('s1', array('reason' => '승인', 'idempotency_key' => 'approve-key'));
        $call = $this->api->lastCall();
        $this->assertSame('approve-key', RecordingCommerceApi::headerValue($call, 'Idempotency-Key'));
        $this->assertSame(array('reason' => '승인'), $call['data']);
    }

    /**
     * 알 수 없는 mode 는 production 으로 폴백하되 경고를 남긴다.
     *
     * 예전에는 $API_URL[$mode] 를 그대로 인덱싱해 Warning + URL 붕괴(curl errno 3)가 났다.
     * 26-08-24: 폴백으로 바꾸되, 결제 SDK 에서 오타가 조용히 production 으로 나가면
     * 실거래가 발생하므로 E_USER_WARNING 을 함께 낸다.
     */
    public function testUnknownModeFallsBackToProductionWithWarning()
    {
        $cases = array(
            array(BootpayCommerceApi::class, 'produciton', 'https://api.bootapi.com/v1'),
            array(BootpayCommerceApi::class, null,         'https://api.bootapi.com/v1'),
            array(BootpayApi::class,         'prod',       'https://api.bootpay.co.kr/v2'),
            array(BootpayApi::class,         '',           'https://api.bootpay.co.kr/v2'),
        );

        foreach ($cases as $case) {
            list($class, $mode, $expected) = $case;
            $warnings = array();
            set_error_handler(function ($no, $str) use (&$warnings) {
                $warnings[] = $str;
                return true;
            }, E_USER_WARNING);

            $resolve = new ReflectionMethod($class, 'resolveApiUrl');
            if (PHP_VERSION_ID < 80100) {
                $resolve->setAccessible(true);
            }
            $actual = $resolve->invoke(null, $mode);
            restore_error_handler();

            $this->assertSame($expected, $actual, 'production 으로 폴백해야 한다');
            $this->assertCount(1, $warnings, '알 수 없는 mode 는 경고를 남겨야 한다');
            $this->assertStringContainsString('알 수 없는 mode', $warnings[0]);
        }
    }

    /** 정상 mode 는 경고 없이 해당 URL 을 돌려준다 */
    public function testKnownModeEmitsNoWarning()
    {
        $warnings = array();
        set_error_handler(function ($no, $str) use (&$warnings) {
            $warnings[] = $str;
            return true;
        }, E_USER_WARNING);

        $commerce = new ReflectionMethod(BootpayCommerceApi::class, 'resolveApiUrl');
        $pg = new ReflectionMethod(BootpayApi::class, 'resolveApiUrl');
        if (PHP_VERSION_ID < 80100) {
            $commerce->setAccessible(true);
            $pg->setAccessible(true);
        }
        $this->assertSame('https://dev-api.bootapi.com/v1', $commerce->invoke(null, 'development'));
        $this->assertSame('https://dev-api.bootpay.co.kr/v2', $pg->invoke(null, 'development'));
        restore_error_handler();

        $this->assertSame(array(), $warnings, '정상 mode 에는 경고가 없어야 한다');
    }

    /**
     * PSR-4: requestIng 모듈이 자기 파일에서 단독으로 autoload 되어야 한다.
     * 26-08-24 이전에는 OrderSubscriptionModule.php 안에 같이 들어 있어,
     * 그 클래스를 직접 참조하면 autoload 가 실패할 수 있었다.
     */
    public function testRequestIngModuleIsAutoloadableOnItsOwn()
    {
        $fqcn = 'Bootpay\\ServerPhp\\Commerce\\OrderSubscriptionRequestIngModule';
        $this->assertTrue(class_exists($fqcn), 'PSR-4 로 단독 autoload 되어야 한다');
        $file = (new \ReflectionClass($fqcn))->getFileName();
        $this->assertSame('OrderSubscriptionRequestIngModule.php', basename($file));
    }
}
