<?php
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../config.php';

use Bootpay\ServerPhp\BootpayCommerceApi;

$keys = getCommerceKeys();
$bootpay = new BootpayCommerceApi(
    $keys['client_key'],
    $keys['secret_key'],
    $keys['mode']
);

try {
    // 토큰 발급
    $bootpay->getAccessToken();

    // 정기구독 조정 생성
    $response = $bootpay->orderSubscriptionAdjustment->create(
        'subscription_id_here',
        array(
            'duration' => 3,
            'price' => -1000,
            'name' => '할인 조정',
            'type' => 1
        )
    );
    print_r($response);

    // 3~7회차에 각각 한 건씩 조정 추가 (총 5건)
    // $response = $bootpay->orderSubscriptionAdjustment->create(
    //     'subscription_id_here',
    //     array(
    //         'duration_from' => 3,
    //         'duration_to' => 7,
    //         'price' => -1000,
    //         'name' => '3~7회차 할인'
    //     )
    // );
    // print_r($response);

    // 3회차부터 계약 끝까지 조정 추가 (레코드 1건, duration_to 는 무시된다)
    // $response = $bootpay->orderSubscriptionAdjustment->create(
    //     'subscription_id_here',
    //     array(
    //         'duration_from' => 3,
    //         'is_unlimited' => true,
    //         'price' => -1000,
    //         'name' => '3회차 이후 상시 할인'
    //     )
    // );
    // print_r($response);

    // 정기구독 조정 수정
    // $response = $bootpay->orderSubscriptionAdjustment->update(array(
    //     'order_subscription_id' => 'subscription_id_here',
    //     'order_subscription_adjustment_id' => 'adjustment_id_here',
    //     'price' => -2000
    // ));
    // print_r($response);

    // 정기구독 조정 삭제
    // $response = $bootpay->orderSubscriptionAdjustment->delete(
    //     'subscription_id_here',
    //     'adjustment_id_here'
    // );
    // print_r($response);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
