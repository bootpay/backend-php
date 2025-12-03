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

    // 정기구독 청구 목록 조회
    $response = $bootpay->orderSubscriptionBill->getList(array(
        'page' => 1,
        'limit' => 10,
        'order_subscription_id' => 'subscription_id_here'
    ));
    print_r($response);

    // 정기구독 청구 상세 조회
    // $response = $bootpay->orderSubscriptionBill->detail('bill_id_here');
    // print_r($response);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
