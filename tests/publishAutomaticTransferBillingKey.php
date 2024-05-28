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
if (!$token->error_code) {
    $response = BootpayApi::publishAutomaticTransferBillingKey("665534d0707116122ea322d5");
    var_dump($response);
}

// object(stdClass)#4 (16) {
//   ["receipt_id"]=>
//   string(24) "665534d0707116122ea322d5"
//   ["subscription_id"]=>
//   string(10) "1716860112"
//   ["gateway_url"]=>
//   string(24) "https://gw.bootpay.co.kr"
//   ["metadata"]=>
//   object(stdClass)#5 (0) {
//   }
//   ["pg"]=>
//   string(21) "나이스페이먼츠"
//   ["method"]=>
//   string(18) "계좌자동이체"
//   ["method_symbol"]=>
//   string(23) "automatic_transfer_rest"
//   ["method_origin"]=>
//   string(18) "계좌자동이체"
//   ["method_origin_symbol"]=>
//   string(23) "automatic_transfer_rest"
//   ["published_at"]=>
//   string(25) "2024-05-28T10:44:17+09:00"
//   ["requested_at"]=>
//   string(25) "2024-05-28T10:35:12+09:00"
//   ["status_locale"]=>
//   string(21) "빌링키발급완료"
//   ["status"]=>
//   int(11)
//   ["billing_key"]=>
//   string(24) "665536f1707116122ea322ea"
//   ["billing_data"]=>
//   object(stdClass)#6 (4) {
//     ["bank_name"]=>
//     string(6) "국민"
//     ["bank_code"]=>
//     string(3) "004"
//     ["bank_account"]=>
//     string(16) "0000000000000000"
//     ["username"]=>
//     string(7) "윤태*"
//   }
//   ["billing_expire_at"]=>
//   string(25) "2099-12-31T23:59:59+09:00"
// }
