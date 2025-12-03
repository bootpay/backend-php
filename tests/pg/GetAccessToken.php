<?php
/*
 * Access Token 요청 예제입니다.
 */
require_once '../../vendor/autoload.php';
require_once __DIR__ . '/../config.php';

use Bootpay\ServerPhp\BootpayApi;

$keys = getPgKeys();

BootpayApi::setConfiguration(
    $keys['application_id'],
    $keys['private_key']
);

$response = BootpayApi::getAccessToken();
var_dump($response);
