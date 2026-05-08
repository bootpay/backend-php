<?php
/*
 * 빌링키 조회 예제입니다.
 */
require_once '../../vendor/autoload.php';
require_once __DIR__ . '/../config.php';

use Bootpay\ServerPhp\BootpayApi;

setupActiveBootpayApi();

$token = BootpayApi::getAccessToken();
if (!isset($token->error_code)) {
    $response = BootpayApi::lookupBillingKey(TEST_DATA['billing_key']);
    var_dump($response);
}
