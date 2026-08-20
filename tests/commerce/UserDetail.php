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

    // 사용자 상세 조회
    $response = $bootpay->user->detail('user_id_here');
    print_r($response);

    // 사용자 정보 수정
    // $response = $bootpay->user->update(array(
    //     'user_id' => 'user_id_here',
    //     'name' => '변경된 이름',
    //     'phone' => '01098765432'
    // ));
    // print_r($response);

    // 사용자 삭제
    // $response = $bootpay->user->delete('user_id_here');
    // print_r($response);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
