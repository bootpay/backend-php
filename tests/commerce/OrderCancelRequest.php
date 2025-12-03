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

    // 취소 요청 목록 조회
    $response = $bootpay->orderCancel->getList(array(
        'order_number' => 'ORDER_123'
    ));
    print_r($response);

    // 취소 요청
    // $response = $bootpay->orderCancel->request(array(
    //     'order_number' => 'ORDER_123',
    //     'request_cancel_parameters' => array(
    //         'cancel_reason' => '고객 요청',
    //         'cancel_type' => 1
    //     )
    // ));
    // print_r($response);

    // 취소 승인
    // $response = $bootpay->orderCancel->approve(array(
    //     'order_cancel_request_history_id' => 'history_id_here',
    //     'cancel_reason' => '승인 완료'
    // ));
    // print_r($response);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
