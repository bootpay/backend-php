<?php
/*
 * 에스크로 배송 시작 예제입니다.
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
        $response = BootpayApi::shippingStart(
            array(
                'receipt_id' => TEST_DATA['receipt_id_escrow'],
                'tracking_number' => '123456',
                'delivery_corp' => 'CJ대한통운',
                'user' => array(
                    'username' => '홍길동',
                    'phone' => '01000000000',
                    'zipcode' => '08490',
                    'address' => '서울특별시 종로구'
                ),
                'company' => array(
                    'name' => '테스트가맹점',
                    'zipcode' => '08490',
                    'addr1' => '서울특별시 종로구',
                    'addr2' => '종로빌딩 3층'
                )
            )
        );
        var_dump($response);
    } catch (Exception $e) {
        echo($e->getMessage());
    }
}
