<?php
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../config.php';

use Bootpay\ServerPhp\BootpayCommerceApi;

// 수시결제(온디맨드) charge_key 결제/해지 (supervisor 전용) — development 전용 라이브 스크립트
// charge_key 는 body 로만 전송된다 (URL/query 금지)
if (CURRENT_ENV !== 'development') {
    echo "BOOTPAY_ENV=development 에서만 실행합니다 (production 호출 금지). 현재: " . CURRENT_ENV . "\n";
    exit(0);
}

$keys = getCommerceKeys();
$bootpay = new BootpayCommerceApi(
    $keys['client_key'],
    $keys['secret_key'],
    $keys['mode']
);

try {
    // 즉시 결제
    $response = $bootpay->orderSubscription->supervisorCharge(array(
        'charge_key' => 'charge_key_here',
        'price' => 1000
    ));
    print_r($response);

    // 해지 — 해지 이후 해당 키로의 재결제는 불가능하다
    // $response = $bootpay->orderSubscription->supervisorChargeRevoke(array(
    //     'charge_key' => 'charge_key_here'
    // ));
    // print_r($response);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
