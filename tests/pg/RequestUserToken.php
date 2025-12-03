<?php
/*
 * 사용자 토큰 발급 예제입니다.
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
        $response = BootpayApi::requestUserToken(array(
            'user_id' => TEST_DATA['user_id'],
            'phone' => '01012345678'
        ));
        var_dump($response);
    } catch (Exception $e) {
        echo($e->getMessage());
    }
}
