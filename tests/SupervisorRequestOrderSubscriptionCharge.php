<?php
/*
 * 수시결제(온디맨드) charge_key 즉시 결제 / 해지 예제입니다.
 * charge_key는 body로만 전송됩니다. (URL/query 금지 - 액세스 로그 노출 방지)
 */
require_once '../vendor/autoload.php';
// require_once __DIR__.'/../src/BootpayCommerceApi.php';

use Bootpay\ServerPhp\BootpayCommerceApi;

BootpayCommerceApi::setConfiguration(
    'QIzXk4M3EeD-6B1GTfmGHA',
    'vRle44QfyBj7nzJlBbeebqkbtlJVRTS2DQa9Adpz3d8=',
    'development'
);

try {
    // POST order_subscriptions/charge
    $response = BootpayCommerceApi::supervisorRequestOrderSubscriptionCharge(array(
        'charge_key' => 'ck_26071438549224114186',
        'price' => 1000,
        'tax_free_price' => 0,
        'user' => array(
            'id' => 'test_user_id',
            'username' => '홍길동',
            'phone' => '01000000000',
            'email' => 'test@bootpay.co.kr'
        ),
        'metadata' => array(
            'order_id' => 'test_order_id'
        )
    ));
    var_dump($response);

    // DELETE order_subscriptions/charge
    $revoke = BootpayCommerceApi::supervisorRequestOrderSubscriptionChargeRevoke(array(
        'charge_key' => 'ck_26071438549224114186',
        'user' => array(
            'id' => 'test_user_id'
        )
    ));
    var_dump($revoke);
} catch (Exception $e) {
    echo($e->getMessage());
}
