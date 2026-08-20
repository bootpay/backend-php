<?php

/*
 * Access Token 요청 예제입니다.
 */
require_once '../vendor/autoload.php';
require_once __DIR__ . '/../config.php';

// require_once __DIR__.'/../src/BootpayApi.php';

use Bootpay\ServerPhp\BootpayApi;

setupActiveBootpayApi();

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