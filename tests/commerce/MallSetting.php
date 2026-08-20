<?php
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../config.php';

use Bootpay\ServerPhp\BootpayCommerceApi;

// 몰 설정 조회/수정 (supervisor 전용) — development 전용 라이브 스크립트
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
    // 몰 설정 조회
    $response = $bootpay->mallSetting->getMallSetting();
    print_r($response);

    // 몰 설정 수정 (null 값은 전송되지 않는다)
    // $response = $bootpay->mallSetting->updateMallSetting(array(
    //     'name' => '테스트몰',
    //     'use_cart' => true
    // ));
    // print_r($response);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
