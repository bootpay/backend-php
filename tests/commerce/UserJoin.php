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

    // 회원가입
    $response = $bootpay->user->join(array(
        'login_id' => 'test_user_' . time(),
        'login_pw' => 'test_password',
        'name' => '테스트 사용자',
        'phone' => '01012345678',
        'email' => 'test@example.com'
    ));
    print_r($response);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
