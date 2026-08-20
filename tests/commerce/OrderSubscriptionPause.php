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

    // 정기구독 일시정지
    $response = $bootpay->orderSubscription->requestIng->pause(array(
        'order_subscription_id' => 'subscription_id_here',
        'reason' => '일시정지 사유',
        'paused_at' => date('Y-m-d H:i:s'),
        'expected_resume_at' => date('Y-m-d H:i:s', strtotime('+1 month'))
    ));
    print_r($response);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
