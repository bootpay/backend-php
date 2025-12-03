<?php
/*
 * 결제 취소 예제입니다.
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
        $response = BootpayApi::cancelPayment(
            array(
                'receipt_id' => TEST_DATA['receipt_id'],
                'cancel_price' => 1000,
                'cancel_tax_free' => '0',
                'cancel_id' => uniqid(),
                'cancel_username' => '관리자',
                'cancel_message' => '테스트 결제 취소',
                'refund' => array(
                    'bank_account' => '',
                    'bank_username' => '',
                    'bank_code' => ''
                )
            )
        );
        var_dump($response);
    } catch (Exception $e) {
        echo($e->getMessage());
    }
}
