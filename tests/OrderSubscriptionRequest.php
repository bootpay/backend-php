<?php
/*
 * 구독 진행중 요청(일시중지·재개·중도인수·중도해지·이전) 및
 * 구독 변경요청 목록/상세/승인·반려 예제입니다.
 *
 * POST order_subscriptions/requests/ing/pause
 * PUT  order_subscriptions/requests/ing/resume         ← 이것만 PUT입니다
 * POST order_subscriptions/requests/ing/purchase
 * POST order_subscriptions/requests/ing/termination
 * POST order_subscriptions/requests/ing/transfer
 * GET  order_subscriptions/requests/ing/calculate_termination_fee
 * GET|PUT order-subscription-requests (하이픈 경로입니다)
 */
require_once '../vendor/autoload.php';
// require_once __DIR__.'/../src/BootpayCommerceApi.php';

use Bootpay\ServerPhp\BootpayCommerceApi;

BootpayCommerceApi::setConfiguration(
    'QIzXk4M3EeD-6B1GTfmGHA',
    'vRle44QfyBj7nzJlBbeebqkbtlJVRTS2DQa9Adpz3d8=',
    'development'
);

$orderSubscriptionId = 'os_26081400000001';

try {
    // POST order_subscriptions/requests/ing/pause
    $pause = BootpayCommerceApi::orderSubscriptionRequestsIngPause($orderSubscriptionId, array(
        'reason' => '장기 출장으로 인한 일시중지',
        'paused_at' => '2026-09-01 00:00:00',
        'expected_resume_at' => '2026-12-01 00:00:00'
    ));
    var_dump($pause);

    // PUT order_subscriptions/requests/ing/resume
    $resume = BootpayCommerceApi::orderSubscriptionRequestsIngResume($orderSubscriptionId, '조기 복귀');
    var_dump($resume);

    // POST order_subscriptions/requests/ing/purchase (중도인수)
    $purchase = BootpayCommerceApi::orderSubscriptionRequestsIngPurchase($orderSubscriptionId, array(
        'price' => 300000,
        'tax_free_price' => 0,
        'reason' => '잔여 회차 일괄 인수'
    ));
    var_dump($purchase);

    // GET order_subscriptions/requests/ing/calculate_termination_fee (해지 전 예상 수수료 확인)
    $fee = BootpayCommerceApi::orderSubscriptionCalculateTerminationFee($orderSubscriptionId);
    var_dump($fee);

    // POST order_subscriptions/requests/ing/termination (중도해지)
    $termination = BootpayCommerceApi::orderSubscriptionRequestsIngTermination($orderSubscriptionId, array(
        'reason' => '서비스 미사용',
        'termination_fee' => 50000,
        'last_bill_refund_price' => 10000,
        'service_end_at' => '2026-09-30 23:59:59'
    ));
    var_dump($termination);

    // POST order_subscriptions/requests/ing/transfer (구독 이전/승계)
    $transfer = BootpayCommerceApi::orderSubscriptionRequestsIngTransfer($orderSubscriptionId, array(
        'new_user_id' => 'user_0002',
        'new_username' => '김철수',
        'new_user_email' => 'new@bootpay.co.kr',
        'new_user_phone' => '01011112222',
        'reason' => '가족 승계'
    ));
    var_dump($transfer);

    // GET order-subscription-requests (project_id를 전달하면 supervisor 모드로 프로젝트 전체를 검색합니다)
    $requests = BootpayCommerceApi::orderSubscriptionRequestList(array(
        'order_subscription_id' => $orderSubscriptionId,
        'page' => 1,
        'limit' => 20
    ));
    var_dump($requests);

    $requestHistoryId = isset($requests->data->list[0]->id) ? $requests->data->list[0]->id : 1;

    // GET order-subscription-requests/{request_history_id}
    $requestDetail = BootpayCommerceApi::orderSubscriptionRequestDetail($requestHistoryId);
    var_dump($requestDetail);

    // PUT order-subscription-requests/{request_history_id}
    // 승인과 반려는 별도 API가 아니라 approval => 'approve' | 'reject' 로 구분합니다.
    $approved = BootpayCommerceApi::orderSubscriptionRequestUpdate($requestHistoryId, 'approve', array(
        'reason' => '승인 처리합니다'
    ));
    var_dump($approved);
} catch (Exception $e) {
    echo($e->getMessage());
}
