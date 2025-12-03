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

    // 로그인
    $response = $bootpay->user->login('test_user', 'test_password');
    print_r($response);

    // 사용자 토큰 발급
    // $response = $bootpay->user->token('user_id_here');
    // print_r($response);

    // 중복 체크
    // $response = $bootpay->user->checkExist('login_id', 'test_user');
    // print_r($response);

    // 본인인증 데이터 조회
    // $response = $bootpay->user->authenticationData('stand_id_here');
    // print_r($response);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
