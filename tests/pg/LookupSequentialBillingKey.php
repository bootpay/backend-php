<?php
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../config.php';

use Bootpay\ServerPhp\BootpayApi;

// 우선순위(순차) 결제 빌링키 조회 — development 전용 라이브 스크립트
// GET /v2/subscribe/sequential_billing_key/{billing_key}?widget_key=&user_id=
if (CURRENT_ENV !== 'development') {
    echo "BOOTPAY_ENV=development 에서만 실행합니다 (production 호출 금지). 현재: " . CURRENT_ENV . "\n";
    exit(0);
}

setupActiveBootpayApi();

try {
    if (BOOTPAY_AUTH_MODE === 'legacy') {
        $token = BootpayApi::getAccessToken();
        print_r($token);
    }

    $response = BootpayApi::lookupSequentialBillingKey(
        'widget_key_here',
        TEST_DATA['billing_key'],
        TEST_DATA['user_id']
    );
    print_r($response);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
