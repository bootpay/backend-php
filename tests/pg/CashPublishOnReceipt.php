<?php
/*
 * 결제건 현금영수증 발행 예제입니다.
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
        $response = BootpayApi::cashReceiptPublishOnReceipt(
            array(
                'receipt_id' => TEST_DATA['receipt_id_cash'],
                'username' => '테스트',
                'email' => 'test@bootpay.co.kr',
                'phone' => '01000000000',
                'identity_no' => '01000000000',
                'cash_receipt_type' => '소득공제'
            )
        );
        var_dump($response);
    } catch (Exception $e) {
        echo($e->getMessage());
    }
}
