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
    // 토큰 발급 및 role 설정 (메서드 체이닝)
    $bootpay->withToken()
            ->asManager();

    echo "Current Role: " . $bootpay->getRole() . "\n";

    // 매니저 권한으로 주문 목록 조회
    $response = $bootpay->order->getList(array(
        'page' => 1,
        'limit' => 10
    ));
    print_r($response);

    // role 변경
    $bootpay->asUser();
    echo "Changed Role: " . $bootpay->getRole() . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
