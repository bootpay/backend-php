<?php
/*
 * 예약 결제 등록 -> 조회 -> 취소 예제입니다.
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
        // 예약 결제 등록
        $response = BootpayApi::subscribePaymentReserve(array(
            'billing_key' => TEST_DATA['billing_key'],
            'order_name' => '테스트결제',
            'price' => 1000,
            'order_id' => time(),
            'user' => array(
                'phone' => '01000000000',
                'username' => '홍길동',
                'email' => 'test@bootpay.co.kr'
            ),
            'reserve_execute_at' => date("Y-m-d\TH:i:sP", time() + 60)
        ));

        if (!isset($response->error_code)) {
            // 예약 조회
            $lookup = BootpayApi::subscribePaymentReserveLookup($response->reserve_id);
            var_dump($lookup);

            // 예약 취소
            $cancel = BootpayApi::cancelSubscribeReserve($response->reserve_id);
            var_dump($cancel);
        } else {
            var_dump($response);
        }
    } catch (Exception $e) {
        echo($e->getMessage());
    }
}
