<?php
/*
 * client key(basic authentication) 우선 인증 예제입니다.
 * client key가 설정되어 있으면 access token을 발급받지 않습니다.
 */
require_once '../vendor/autoload.php';
// require_once __DIR__.'/../src/BootpayApi.php';

use Bootpay\ServerPhp\BootpayApi;

BootpayApi::setConfiguration(
    '5b8f6a4d396fa665fdc2b5ea',
    'rm6EYECr6aroQVG2ntW0A6LpWnkTgP4uQ3H18sDDUYw=',
    'production',
    'QIzXk4M3EeD-6B1GTfmGHA',
    'vRle44QfyBj7nzJlBbeebqkbtlJVRTS2DQa9Adpz3d8='
);

$response = BootpayApi::basicOrGetAccessToken();
var_dump($response);
