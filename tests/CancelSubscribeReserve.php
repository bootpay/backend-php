<?php
/*
 * Access Token 요청 예제입니다.
 */
require_once '../vendor/autoload.php';

// require_once __DIR__.'/../src/BootpayApi.php';

use Bootpay\ServerPhp\BootpayApi;

BootpayApi::setConfiguration(
    '59b731f084382614ebf72215',
    'WwDv0UjfwFa04wYG0LJZZv1xwraQnlhnHE375n52X0U='
);


$token = BootpayApi::getAccessToken();
if (!$token->error_code) {
    try {
        $response = BootpayApi::subscribePaymentReserve(array(
            'billing_key' => '6406ef293049c8001ff5afd3',
            'order_name' => '테스트결제',
            'price' => 1000,
            'order_id' => time(),
            'user' => array(
                'phone' => '01000000000',
                'username' => '홍길동',
                'email' => 'test@bootpay.co.kr'
            ),
            'reserve_execute_at' => date("Y-m-d H:i:s \U\T\C", time() + 5)
        ));

        if (!$response->error_code) {
            $lookup = BootpayApi::subscribePaymentReserveLookup($response->reserve_id);
            var_dump($lookup);
            $cancel = BootpayApi::cancelSubscribeReserve($response->reserve_id);
            var_dump($cancel);
        }
    } catch (Exception $e) {
        echo($e->getMessage());
    }
}