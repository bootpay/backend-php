<?php
/*
 * 카드 정기결제 실행 예제입니다.
 */
require_once '../../vendor/autoload.php';
require_once __DIR__ . '/../config.php';

use Bootpay\ServerPhp\BootpayApi;

setupActiveBootpayApi();

$token = BootpayApi::getAccessToken();
if (!isset($token->error_code)) {
    try {
        $response = BootpayApi::requestSubscribeCardPayment(array(
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
