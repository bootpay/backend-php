<?php
/*
 * 정기결제 빌링키 조회 예제입니다.
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
    $response = BootpayApi::lookupSubscribeBillingKey(TEST_DATA['receipt_id_billing']);
    var_dump($response);
}
