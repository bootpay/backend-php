<?php
/*
 * 결제 검증 관련 예제입니다.
 */
require_once '../vendor/autoload.php'; 
use \Bootpay\BackendPhp\BootpayApi; 

$receiptId = '612c31000199430036b5165d';

$bootpay = BootpayApi::setConfig(
    '5b8f6a4d396fa665fdc2b5ea',
    'rm6EYECr6aroQVG2ntW0A6LpWnkTgP4uQ3H18sDDUYw='
);

$response = $bootpay->requestAccessToken();

if ($response->status === 200) {
    $result = $bootpay->verify($receiptId);
    var_dump($result);
}