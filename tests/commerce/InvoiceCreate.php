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

    // 청구서 생성
    $response = $bootpay->invoice->create(array(
        'title' => '테스트 청구서',
        'name' => '테스트 상품',
        'price' => 10000,
        'tax_free_price' => 0,
        'expired_at' => date('Y-m-d H:i:s', strtotime('+7 days')),
        'send_types' => array(1) // SMS
    ));
    print_r($response);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
