<?php
/**
 * Bootpay SDK 테스트 설정
 *
 * 환경에 맞는 키를 선택하여 사용합니다.
 */

// =====================================================
// PG API 키
// =====================================================

// Production 환경
const PG_PROD_APPLICATION_ID = '5b8f6a4d396fa665fdc2b5ea';
const PG_PROD_PRIVATE_KEY = 'rm6EYECr6aroQVG2ntW0A6LpWnkTgP4uQ3H18sDDUYw=';

// Development 환경
const PG_DEV_APPLICATION_ID = '59bfc738e13f337dbd6ca48a';
const PG_DEV_PRIVATE_KEY = 'pDc0NwlkEX3aSaHTp/PPL/i8vn5E/CqRChgyEp/gHD0=';

// =====================================================
// Commerce API 키
// =====================================================

// Production 환경
const COMMERCE_PROD_CLIENT_KEY = 'sEN72kYZBiyMNytA8nUGxQ';
const COMMERCE_PROD_SECRET_KEY = 'rnZLJamENRgfwTccwmI_Uu9cxsPpAV9X2W-Htg73yfU=';

// Development 환경
const COMMERCE_DEV_CLIENT_KEY = 'hxS-Up--5RvT6oU6QJE0JA';
const COMMERCE_DEV_SECRET_KEY = 'r5zxvDcQJiAP2PBQ0aJjSHQtblNmYFt6uFoEMhti_mg=';

// =====================================================
// 현재 사용할 환경 설정 (development / production)
// =====================================================
const CURRENT_ENV = 'production';

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

// 환경에 따른 키 반환 함수
function getPgKeys() {
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
