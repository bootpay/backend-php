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

    // 사용자 그룹 생성
    $response = $bootpay->userGroup->create(array(
        'company_name' => '테스트 회사',
        'business_number' => '1234567890',
        'ceo_name' => '홍길동',
        'phone' => '0212345678',
        'email' => 'company@example.com',
        'corporate_type' => 2 // 법인
    ));
    print_r($response);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
