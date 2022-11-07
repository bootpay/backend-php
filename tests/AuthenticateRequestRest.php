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
        $response = BootpayApi::requestAuthentication(
            array(
                'pg' => '다날',
                'method' => '본인인증',
                'authentication_id' => time(),
                'order_name' => '본인인증명',
                'username' => '[ 인증받을 사람명 ]',
                'phone' => '[ 소유주 전화번호 ]',
                'identity_no' => '[ 주민등록번호 앞 7자리 (생년월일 + 성별) ]',
                'carrier' => '[ 통신사 ]',
                'authenticate_type' => 'sms'
            )
        );
        var_dump($response);
    } catch (Exception $e) {
        echo($e->getMessage());
    }
}
