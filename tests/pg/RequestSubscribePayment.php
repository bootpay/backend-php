<?php
/*
 * 정기결제 실행 예제입니다.
 */
require_once '../../vendor/autoload.php';
require_once __DIR__ . '/../config.php';

use Bootpay\ServerPhp\BootpayApi;

$keys = getPgKeys();

BootpayApi::setConfiguration(
    $keys['application_id'],
    $keys['private_key']
);

$token = BootpayApi::getAccessToken();
if (!isset($token->error_code)) {
    try {
        $response = BootpayApi::requestSubscribePayment(array(
            'billing_key' => TEST_DATA['billing_key'],
            'order_name' => '테스트결제',
            'price' => 1000,
            'order_id' => time()
        ));
        var_dump($response);
    } catch (Exception $e) {
        echo($e->getMessage());
    }
}
