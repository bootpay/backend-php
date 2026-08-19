<?php
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../config.php';

use Bootpay\ServerPhp\BootpayCommerceApi;

// 구독 변경요청 — 중도인수(purchase) / 이전·승계(transfer) — development 전용 라이브 스크립트
if (CURRENT_ENV !== 'development') {
    echo "BOOTPAY_ENV=development 에서만 실행합니다 (production 호출 금지). 현재: " . CURRENT_ENV . "\n";
    exit(0);
}

$keys = getCommerceKeys();
$bootpay = new BootpayCommerceApi(
    $keys['client_key'],
    $keys['secret_key'],
    $keys['mode']
);

try {
    // 중도인수 요청 — POST order_subscriptions/requests/ing/purchase
    $response = $bootpay->orderSubscription->requestIng->purchase(array(
        'order_number' => 'order_number_here',
        'price' => 10000,
        'reason' => 'PHP SDK 중도인수 테스트'
    ));
    print_r($response);

    // 이전/승계 요청 — POST order_subscriptions/requests/ing/transfer
    // $response = $bootpay->orderSubscription->requestIng->transfer(array(
    //     'order_subscription_id' => 'order_subscription_id_here',
    //     'new_user_id' => 'new_user_id_here',
    //     'reason' => 'PHP SDK 승계 테스트'
    // ));
    // print_r($response);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
