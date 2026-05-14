<?php
/**
 * Bootpay SDK 테스트 설정
 *
 * 환경에 맞는 키를 선택하여 사용합니다.
 */


function loadBootpayDotEnv() {
    foreach ([__DIR__ . '/../.env', __DIR__ . '/.env'] as $file) {
        if (!file_exists($file)) continue;
        foreach (file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            $line = trim($line);
            if ($line === '' || strpos($line, '#') === 0 || strpos($line, '=') === false) continue;
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            $value = trim($value, '"\'');
            if (getenv($key) === false) putenv($key . '=' . $value);
        }
    }
}

function bootpayEnv($key, $fallback) {
    $value = getenv($key);
    return $value === false || $value === '' ? $fallback : $value;
}

loadBootpayDotEnv();

// =====================================================
// PG/Commerce API 키 - .env / 환경변수 로 주입한다 (.env.example 참고)
// =====================================================

// PG (ck/sk)
define('PG_PROD_CLIENT_KEY', bootpayEnv('BOOTPAY_PG_CLIENT_KEY_PROD', ''));
define('PG_PROD_SECRET_KEY', bootpayEnv('BOOTPAY_PG_SECRET_KEY_PROD', ''));
define('PG_DEV_CLIENT_KEY', bootpayEnv('BOOTPAY_PG_CLIENT_KEY_DEV', ''));
define('PG_DEV_SECRET_KEY', bootpayEnv('BOOTPAY_PG_SECRET_KEY_DEV', ''));
// PG legacy application_id/private_key (호환성 검증용)
define('PG_PROD_APPLICATION_ID', bootpayEnv('BOOTPAY_PG_APPLICATION_ID_PROD', ''));
define('PG_PROD_PRIVATE_KEY', bootpayEnv('BOOTPAY_PG_PRIVATE_KEY_PROD', ''));
define('PG_DEV_APPLICATION_ID', bootpayEnv('BOOTPAY_PG_APPLICATION_ID_DEV', ''));
define('PG_DEV_PRIVATE_KEY', bootpayEnv('BOOTPAY_PG_PRIVATE_KEY_DEV', ''));

// Commerce (ck/sk)
define('COMMERCE_PROD_CLIENT_KEY', bootpayEnv('BOOTPAY_COMMERCE_CLIENT_KEY_PROD', ''));
define('COMMERCE_PROD_SECRET_KEY', bootpayEnv('BOOTPAY_COMMERCE_SECRET_KEY_PROD', ''));
define('COMMERCE_DEV_CLIENT_KEY', bootpayEnv('BOOTPAY_COMMERCE_CLIENT_KEY_DEV', ''));
define('COMMERCE_DEV_SECRET_KEY', bootpayEnv('BOOTPAY_COMMERCE_SECRET_KEY_DEV', ''));

// =====================================================
// 현재 사용할 환경 설정 (development / production)
// =====================================================
define('CURRENT_ENV', bootpayEnv('BOOTPAY_ENV', 'production'));

// PG 인증 방식: 'new' (client_key/secret_key) 또는 'legacy' (application_id/private_key)
// 매 실행 시 BOOTPAY_AUTH_MODE 환경변수로 토글한다.
define('BOOTPAY_AUTH_MODE', strtolower(bootpayEnv('BOOTPAY_AUTH_MODE', 'new')));

// =====================================================
// 테스트 데이터 (Java SDK 예제와 동일)
// =====================================================
const TEST_DATA = [
    // 결제 조회/취소용
    'receipt_id' => '628b2206d01c7e00209b6087',
    // 서버 승인용
    'receipt_id_confirm' => '62876963d01c7e00209b6028',
    // 현금영수증용
    'receipt_id_cash' => '62e0f11f1fc192036b1b3c92',
    // 에스크로용
    'receipt_id_escrow' => '628ae7ffd01c7e001e9b6066',
    // 빌링키 조회용 (receipt_id 기반)
    'receipt_id_billing' => '62c7ccebcf9f6d001b3adcd4',
    // 계좌 빌링키 발급용
    'receipt_id_transfer' => '66541bc4ca4517e69343e24c',
    // 빌링키
    'billing_key' => '628b2644d01c7e00209b6092',
    'billing_key_2' => '66542dfb4d18d5fc7b43e1b6',
    // 예약 결제 ID
    'reserve_id' => '6490149ca575b40024f0b70d',
    'reserve_id_2' => '628b316cd01c7e00219b6081',
    // 사용자 ID
    'user_id' => '1234',
    // 본인인증 receipt_id
    'certificate_receipt_id' => '61b009aaec81b4057e7f6ecd',
];

// 환경에 따른 키 반환 함수 (ck/sk)
function getPgKeys() {
    if (CURRENT_ENV === 'development') {
        return [
            'client_key' => PG_DEV_CLIENT_KEY,
            'secret_key' => PG_DEV_SECRET_KEY,
            'mode' => 'development'
        ];
    }
    return [
        'client_key' => PG_PROD_CLIENT_KEY,
        'secret_key' => PG_PROD_SECRET_KEY,
        'mode' => 'production'
    ];
}

// PG legacy application_id/private_key 반환
function getPgLegacyKeys() {
    if (CURRENT_ENV === 'development') {
        return [
            'application_id' => PG_DEV_APPLICATION_ID,
            'private_key' => PG_DEV_PRIVATE_KEY,
            'mode' => 'development'
        ];
    }
    return [
        'application_id' => PG_PROD_APPLICATION_ID,
        'private_key' => PG_PROD_PRIVATE_KEY,
        'mode' => 'production'
    ];
}

// AUTH_MODE 에 따라 활성화된 PG config 반환
function getActivePgConfig() {
    return BOOTPAY_AUTH_MODE === 'legacy' ? getPgLegacyKeys() : getPgKeys();
}

// AUTH_MODE 에 따라 BootpayApi 정적 setter 호출
function setupActiveBootpayApi() {
    $cfg = getActivePgConfig();
    if (BOOTPAY_AUTH_MODE === 'legacy') {
        echo "[BOOTPAY_AUTH_MODE=legacy] PG: application_id/private_key (Bearer) | env=" . CURRENT_ENV . PHP_EOL;
        \Bootpay\ServerPhp\BootpayApi::setConfiguration(
            $cfg['application_id'],
            $cfg['private_key'],
            $cfg['mode']
        );
    } else {
        echo "[BOOTPAY_AUTH_MODE=new] PG: client_key/secret_key (Basic Auth) | env=" . CURRENT_ENV . PHP_EOL;
        \Bootpay\ServerPhp\BootpayApi::setClientKeyConfiguration(
            $cfg['client_key'],
            $cfg['secret_key'],
            $cfg['mode']
        );
    }
}

function getCommerceKeys() {
    if (CURRENT_ENV === 'development') {
        return [
            'client_key' => COMMERCE_DEV_CLIENT_KEY,
            'secret_key' => COMMERCE_DEV_SECRET_KEY,
            'mode' => 'development'
        ];
    }
    return [
        'client_key' => COMMERCE_PROD_CLIENT_KEY,
        'secret_key' => COMMERCE_PROD_SECRET_KEY,
        'mode' => 'production'
    ];
}
