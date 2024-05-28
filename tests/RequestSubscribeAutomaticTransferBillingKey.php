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
    $response = BootpayApi::requestSubscribeAutomaticTransferBillingKey(array(
        'pg' => '나이스페이',
        'order_name' => '테스트결제',
        'subscription_id' => time(),
        'username' => '홍길동',
        'bank_name' => '국민',
        'bank_account' => '675123412342472',
        'identity_no' => '901014',
        'cash_receipt_identity_no' => '01012341234',
        'phone' => '01012341234',
        'user' => array(
            'phone' => '01000000000',
            'username' => '홍길동',
            'email' => 'test@bootpay.co.kr'
        ),
        'reserve_execute_at' => date("Y-m-d H:i:s \U\T\C", time() + 5)
    ));
    var_dump($response);
}


// object(stdClass)#4 (20) {
//   ["receipt_id"]=>
//   string(24) "665534d0707116122ea322d5"
//   ["order_id"]=>
//   string(10) "1716860112"
//   ["price"]=>
//   int(0)
//   ["tax_free"]=>
//   int(0)
//   ["cancelled_price"]=>
//   int(0)
//   ["cancelled_tax_free"]=>
//   int(0)
//   ["order_name"]=>
//   string(15) "테스트결제"
//   ["company_name"]=>
//   string(9) "윤태섭"
//   ["gateway_url"]=>
//   string(24) "https://gw.bootpay.co.kr"
//   ["metadata"]=>
//   object(stdClass)#5 (0) {
//   }
//   ["sandbox"]=>
//   bool(true)
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
//   ["requested_at"]=>
//   string(25) "2024-05-28T10:35:12+09:00"
//   ["status_locale"]=>
//   string(33) "자동결제빌링키발급이전"
//   ["currency"]=>
//   string(3) "KRW"
//   ["status"]=>
//   int(41)
// }
