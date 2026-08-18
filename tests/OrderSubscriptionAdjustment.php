<?php
/*
 * 구독 계약변경 / 가감산 조정항목 / 구독 빌(회차) 조회 예제입니다.
 * PUT order_subscriptions/{order_subscription_id}
 * POST|PUT|DELETE order_subscriptions/{order_subscription_id}/adjustments
 * GET order_subscription_bills
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
    // PUT order_subscriptions/{order_subscription_id} (바뀐 값만 전달합니다)
    $updated = BootpayCommerceApi::orderSubscriptionUpdate($orderSubscriptionId, array(
        'order_name' => '변경된 구독 상품명',
        'quantity' => 2,
        'total_subscription_duration' => 12,
        'service_end_at' => '2027-08-14 23:59:59'
    ));
    var_dump($updated);

    // POST order_subscriptions/{order_subscription_id}/adjustments
    // type 미전달시 서버가 price > 0 이면 SETUP_PRICE, 아니면 PERIOD_DISCOUNT로 자동 판정합니다.
    $adjustment = BootpayCommerceApi::orderSubscriptionAdjustmentCreate($orderSubscriptionId, array(
        'name' => '설치비',
        'price' => 50000,
        'duration' => 1,
        'tax_free_price' => 0
    ));
    var_dump($adjustment);

    // PUT order_subscriptions/{order_subscription_id}/adjustments (해당 회차의 조정항목을 통째로 교체합니다)
    $replaced = BootpayCommerceApi::orderSubscriptionAdjustmentUpdate(
        $orderSubscriptionId,
        1,
        array(
            array('name' => '설치비', 'price' => 30000, 'tax_free_price' => 0),
            array('name' => '첫달 할인', 'price' => -10000, 'tax_free_price' => 0)
        )
    );
    var_dump($replaced);

    // DELETE order_subscriptions/{order_subscription_id}/adjustments
    $adjustmentId = isset($adjustment->data->id) ? $adjustment->data->id : 1;
    $deleted = BootpayCommerceApi::orderSubscriptionAdjustmentDelete($orderSubscriptionId, $adjustmentId);
    var_dump($deleted);

    // GET order_subscription_bills (경로가 언더스코어입니다)
    $bills = BootpayCommerceApi::orderSubscriptionBillList(array(
        'order_subscription_id' => $orderSubscriptionId,
        'page' => 1,
        'limit' => 20
    ));
    var_dump($bills);
} catch (Exception $e) {
    echo($e->getMessage());
}
