<?php
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../config.php';

use Bootpay\ServerPhp\BootpayCommerceApi;

// 테스트 웹훅 발송 — development 전용 라이브 스크립트
// POST /v1/webhook/test
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
    // header_content_type 미지정 — 서버 기본값으로 발송
    $response = $bootpay->webhook->sendTest();
    print_r($response);

    // Content-Type 지정 발송
    $response = $bootpay->webhook->sendTest(array('header_content_type' => 1));
    print_r($response);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
