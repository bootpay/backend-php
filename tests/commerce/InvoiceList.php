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

    // 청구서 목록 조회
    $response = $bootpay->invoice->getList(array(
        'page' => 1,
        'limit' => 10
    ));
    print_r($response);

    // 청구서 상세 조회
    // $response = $bootpay->invoice->detail('invoice_id_here');
    // print_r($response);

    // 청구서 알림 발송
    // $response = $bootpay->invoice->notify('invoice_id_here', array(1, 3)); // SMS, Email
    // print_r($response);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
