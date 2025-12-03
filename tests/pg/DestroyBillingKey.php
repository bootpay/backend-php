<?php
/*
 * 빌링키 삭제 예제입니다.
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
        $response = BootpayApi::destroyBillingKey(TEST_DATA['billing_key']);
        var_dump($response);
    } catch (Exception $e) {
        echo($e->getMessage());
    }
}
