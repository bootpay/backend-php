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
     * 알 수 없는 mode 는 production 으로 폴백한다.
     * 예전에는 $API_URL[$mode] 를 그대로 인덱싱해 Warning + URL 붕괴(curl errno 3)가 났다.
     */
    public function testUnknownModeFallsBackToProduction()
    {
        $resolve = new ReflectionMethod(BootpayCommerceApi::class, 'resolveApiUrl');
        if (PHP_VERSION_ID < 80100) {
            $resolve->setAccessible(true);
        }
        $this->assertSame('https://api.bootapi.com/v1', $resolve->invoke(null, 'produciton'));
        $this->assertSame('https://api.bootapi.com/v1', $resolve->invoke(null, null));
        $this->assertSame('https://dev-api.bootapi.com/v1', $resolve->invoke(null, 'development'));

        $pgResolve = new ReflectionMethod(BootpayApi::class, 'resolveApiUrl');
        if (PHP_VERSION_ID < 80100) {
            $pgResolve->setAccessible(true);
        }
        $this->assertSame('https://api.bootpay.co.kr/v2', $pgResolve->invoke(null, 'prod'));
        $this->assertSame('https://api.bootpay.co.kr/v2', $pgResolve->invoke(null, ''));
        $this->assertSame('https://dev-api.bootpay.co.kr/v2', $pgResolve->invoke(null, 'development'));
    }
}
