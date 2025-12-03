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

    // 해지 수수료 계산
    $feeResponse = $bootpay->orderSubscription->requestIng->calculateTerminationFee('subscription_id_here');
    print_r($feeResponse);

    // 또는 주문번호로 해지 수수료 계산
    // $feeResponse = $bootpay->orderSubscription->requestIng->calculateTerminationFeeByOrderNumber('ORDER_123');
    // print_r($feeResponse);

    // 정기구독 해지
    // $response = $bootpay->orderSubscription->requestIng->termination(array(
    //     'order_subscription_id' => 'subscription_id_here',
    //     'termination_fee' => 5000,
    //     'reason' => '해지 사유',
    //     'service_end_at' => date('Y-m-d H:i:s')
    // ));
    // print_r($response);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
