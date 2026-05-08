<?php
/*
 * 서버 승인 예제입니다.
 */
require_once '../../vendor/autoload.php';
require_once __DIR__ . '/../config.php';

use Bootpay\ServerPhp\BootpayApi;

setupActiveBootpayApi();

$token = BootpayApi::getAccessToken();
if (!isset($token->error_code)) {
    try {
        $response = BootpayApi::confirmPayment(TEST_DATA['receipt_id_confirm']);
        var_dump($response);
    } catch (Exception $e) {
        echo($e->getMessage());
    }
}
