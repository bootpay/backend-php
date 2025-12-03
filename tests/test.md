# Bootpay PHP SDK 테스트 가이드

```bash
composer instal
```


## 폴더 구조

```
tests/
├── config.php          # 공통 설정 (API 키, 환경 설정, 테스트 데이터)
├── pg/                 # PG API 테스트
│   ├── GetAccessToken.php
│   ├── LookupReceiptPayment.php
│   ├── CancelReceipt.php
│   ├── Certificate.php
│   └── ...
└── commerce/           # Commerce API 테스트
    └── ...
```

## 환경 설정

`tests/config.php`에서 환경을 설정합니다:

```php
// development 또는 production
const CURRENT_ENV = 'production';
```

## 테스트 실행 방법

### 1. Composer 설치 확인

프로젝트 루트에서 의존성을 설치합니다:

```bash
cd /Users/taesupyoon/bootpay/server/sdk/php
composer install
```

### 2. PG API 테스트

`tests/pg/` 폴더로 이동 후 개별 파일 실행:

```bash
cd tests/pg

# 토큰 발급 테스트
php GetAccessToken.php

# 결제 조회 테스트
php LookupReceiptPayment.php

# 결제 취소 테스트
php CancelReceipt.php

# 본인인증 결과 조회 테스트
php Certificate.php

# 빌링키 테스트
php GetBillingKey.php
php LookupBillingKey.php
php LookupSubscribeBillingKey.php
php DestroyBillingKey.php

# 정기결제 테스트
php RequestSubscribeCardPayment.php
php RequestSubscribePayment.php
php SubscribePaymentReserve.php
php CancelSubscribeReserve.php

# 계좌 자동이체 빌링키
php RequestSubscribeAutomaticTransferBillingKey.php
php publishAutomaticTransferBillingKey.php

# 현금영수증 테스트
php RequestCashReceipt.php
php CancelCashReceipt.php
php CashPublishOnReceipt.php
php CashCancelOnReceipt.php

# 에스크로 테스트
php ShippingStart.php

# 사용자 토큰 테스트
php RequestUserToken.php

# 서버 승인 테스트
php ConfirmPayment.php
```

### 3. Commerce API 테스트

`tests/commerce/` 폴더로 이동 후 실행:

```bash
cd tests/commerce

# 개별 테스트 파일 실행
php TestFile.php
```

## PG API 테스트 목록

| 파일명 | 설명 |
|--------|------|
| `GetAccessToken.php` | 토큰 발급 |
| `LookupReceiptPayment.php` | 결제 조회 |
| `CancelReceipt.php` | 결제 취소 |
| `Certificate.php` | 본인인증 결과 조회 |
| `ConfirmPayment.php` | 서버 승인 |
| `GetBillingKey.php` | 빌링키 발급 |
| `LookupBillingKey.php` | 빌링키 조회 |
| `LookupSubscribeBillingKey.php` | 정기결제 빌링키 조회 |
| `DestroyBillingKey.php` | 빌링키 삭제 |
| `RequestSubscribeCardPayment.php` | 카드 정기결제 실행 |
| `RequestSubscribePayment.php` | 정기결제 실행 |
| `SubscribePaymentReserve.php` | 예약 결제 등록 |
| `CancelSubscribeReserve.php` | 예약 결제 취소 |
| `RequestSubscribeAutomaticTransferBillingKey.php` | 계좌 자동이체 빌링키 요청 |
| `publishAutomaticTransferBillingKey.php` | 계좌 자동이체 빌링키 발급 |
| `RequestCashReceipt.php` | 현금영수증 발급 |
| `CancelCashReceipt.php` | 현금영수증 취소 |
| `CashPublishOnReceipt.php` | 결제건 현금영수증 발급 |
| `CashCancelOnReceipt.php` | 결제건 현금영수증 취소 |
| `ShippingStart.php` | 에스크로 배송 시작 |
| `RequestUserToken.php` | 사용자 토큰 발급 |
| `AuthenticateRequestRest.php` | 본인인증 요청 |
| `AuthenticateConfirmRest.php` | 본인인증 확인 |
| `AuthenticateRealarmRest.php` | 본인인증 재알림 |

## 주의사항

1. **실제 결제 테스트**: `CancelReceipt.php`, `LookupReceiptPayment.php` 등은 실제 receipt_id가 필요합니다.
2. **환경 선택**: `config.php`의 `CURRENT_ENV`로 development/production 환경을 선택합니다.
3. **순서 의존성**: 일부 테스트는 다른 테스트 결과(receipt_id, billing_key 등)가 필요합니다.
4. **테스트 데이터**: `config.php`의 `TEST_DATA` 배열에서 테스트에 사용할 ID 값들을 관리합니다.
