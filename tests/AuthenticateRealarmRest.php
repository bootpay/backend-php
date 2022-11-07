<?php

/*
 * Access Token 요청 예제입니다.
 */
require_once '../vendor/autoload.php';

// require_once __DIR__.'/../src/BootpayApi.php';

use Bootpay\ServerPhp\BootpayApi;

BootpayApi::setConfiguration(
    '5b8f6a4d396fa665fdc2b5ea',
    'rm6EYECr6aroQVG2ntW0A6LpWnkTgP4uQ3H18sDDUYw='
);

$token = BootpayApi::getAccessToken();
var_dump($token);

if (!$token->error_code) {
    try {
        $response = BootpayApi::realarmAuthentication(
            '6368c2dad01c7e002c1cc0e9'
        );
        var_dump($response);
    } catch (Exception $e) {
        echo($e->getMessage());
    }
}