<?php
/*
 * Access Token 요청 예제입니다.
 */
require_once '../vendor/autoload.php';

// require_once __DIR__.'/../src/BootpayApi.php';

use Bootpay\ServerPhp\BootpayApi;

//BootpayApi::setConfiguration(
//    '5b8f6a4d396fa665fdc2b5ea',
//    'rm6EYECr6aroQVG2ntW0A6LpWnkTgP4uQ3H18sDDUYw='
//);

BootpayApi::setConfiguration(
    '59bfc738e13f337dbd6ca48a',
    'pDc0NwlkEX3aSaHTp/PPL/i8vn5E/CqRChgyEp/gHD0=',
    'development'
);

$token = BootpayApi::getAccessToken();
// var_dump($token);

if (!$token->error_code) {
    try {
        $response = BootpayApi::cancelCashReceipt(
            array(
                'receipt_id' => '62f211911fc192036b4f6f8d',
                'cancel_username' => '시스템',
                'cancel_message' => '테스트'
            )
        );
        var_dump($response);
    } catch (Exception $e) {
        echo($e->getMessage());
    }
}