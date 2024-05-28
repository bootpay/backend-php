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
    $response = BootpayApi::lookupBillingKey('66542dfb4d18d5fc7b43e1b6');
    var_dump($response);
}


// object(stdClass)#4 (10) {
//   ["billing_key"]=>
//   string(24) "66542dfb4d18d5fc7b43e1b6"
//   ["pg"]=>
//   string(21) "나이스페이먼츠"
//   ["method"]=>
//   string(18) "계좌자동이체"
//   ["method_symbol"]=>
//   string(23) "automatic_transfer_rest"
//   ["billing_data"]=>
//   object(stdClass)#5 (4) {
//     ["bank_name"]=>
//     string(6) "국민"
//     ["bank_code"]=>
//     string(3) "004"
//     ["bank_account"]=>
//     string(16) "0000000000000000"
//     ["username"]=>
//     string(7) "윤태*"
//   }
//   ["version"]=>
//   int(2)
//   ["sandbox"]=>
//   int(1)
//   ["expire_at"]=>
//   string(25) "2099-12-31T23:59:59+09:00"
//   ["published_at"]=>
//   string(25) "2024-05-27T15:53:47+09:00"
//   ["status"]=>
//   int(1)
// }
