<?php
/*
 * 결제건 현금영수증 취소 예제입니다.
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
        $response = BootpayApi::cashReceiptCancelOnReceipt(
            array(
                'receipt_id' => TEST_DATA['receipt_id_cash'],
                'cancel_username' => '관리자',
                'cancel_message' => '일반적인 취소'
            )
        );
        var_dump($response);
    } catch (Exception $e) {
        print($e->getMessage());
    }
}
