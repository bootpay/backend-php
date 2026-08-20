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

    // 정기구독 목록 조회
    $response = $bootpay->orderSubscription->getList(array(
        'page' => 1,
        'limit' => 10
    ));
    print_r($response);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
