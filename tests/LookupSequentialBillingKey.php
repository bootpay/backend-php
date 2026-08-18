<?php
/*
 * 우선순위 빌링키 조회 예제입니다.
 */
require_once '../vendor/autoload.php';
// require_once __DIR__.'/../src/BootpayApi.php';

use Bootpay\ServerPhp\BootpayApi;

BootpayApi::setConfiguration(
    '5b8f6a4d396fa665fdc2b5ea',
    'rm6EYECr6aroQVG2ntW0A6LpWnkTgP4uQ3H18sDDUYw='
);


$token = BootpayApi::getAccessToken();
if (!$token->error_code) {
    // GET subscribe/sequential_billing_key/{billing_key}?widget_key={widget_key}&user_id={user_id}
    $response = BootpayApi::lookupSequentialBillingKey(
        '5b8f6a4d396fa665fdc2b5eb',
        '66542dfb4d18d5fc7b43e1b6',
        'gosomi1'
    );
    var_dump($response);

    // user_id 없이도 호출 가능합니다 (기존 방식 그대로 동작합니다)
    $legacy = BootpayApi::lookupSequentialBillingKey(
        '5b8f6a4d396fa665fdc2b5eb',
        '66542dfb4d18d5fc7b43e1b6'
    );
    var_dump($legacy);
}
