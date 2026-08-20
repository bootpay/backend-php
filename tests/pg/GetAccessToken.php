<?php
/*
 * Access Token 요청 예제입니다.
 */
require_once '../../vendor/autoload.php';
require_once __DIR__ . '/../config.php';

use Bootpay\ServerPhp\BootpayApi;

setupActiveBootpayApi();

$response = BootpayApi::getAccessToken();
var_dump($response);
