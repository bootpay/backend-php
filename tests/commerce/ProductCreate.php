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

    // 상품 생성 (이미지 없이)
    $response = $bootpay->product->create(array(
        'name' => '테스트 상품',
        'description' => '테스트 상품 설명',
        'display_price' => 10000,
        'type' => 1,
        'status_display' => true,
        'status_sale' => true
    ));
    print_r($response);

    // 상품 생성 (이미지 포함)
    // $response = $bootpay->product->create(
    //     array(
    //         'name' => '테스트 상품',
    //         'display_price' => 10000
    //     ),
    //     array('/path/to/image1.jpg', '/path/to/image2.jpg')
    // );
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
