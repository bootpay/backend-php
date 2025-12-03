# Bootpay Server Side Package for PHP [![Packagist Version](https://img.shields.io/packagist/v/bootpay/server-php)](https://packagist.org/packages/bootpay/server-php)

## Bootpay PHP Server Side Library

부트페이 공식 PHP 라이브러리 입니다 (서버사이드 용)

PHP 언어로 작성된 어플리케이션, 프레임워크 등에서 사용가능합니다.

* PG 결제창 연동은 클라이언트 라이브러리에서 수행됩니다. (Javascript, Android, iOS, React Native, Flutter 등)
* 결제 검증 및 취소, 빌링키 발급, 본인인증 등의 수행은 서버사이드에서 진행됩니다. (Java, PHP, Python, Ruby, Node.js, Go, ASP.NET 등)

## 목차

### PG API
- [사용하기](#사용하기)
  - [1. 토큰 발급](#1-토큰-발급)
  - [2. 결제 단건 조회](#2-결제-단건-조회)
  - [3. 결제 취소 (전액 취소 / 부분 취소)](#3-결제-취소-전액-취소--부분-취소)
  - [4. 자동/빌링/정기 결제](#4-자동빌링정기-결제)
    - [4-1. 카드 빌링키 발급](#4-1-카드-빌링키-발급)
    - [4-2. 계좌 빌링키 발급](#4-2-계좌-빌링키-발급)
    - [4-3. 결제 요청하기](#4-3-결제-요청하기)
    - [4-4. 결제 예약하기](#4-4-결제-예약하기)
    - [4-5. 예약 조회하기](#4-5-예약-조회하기)
    - [4-6. 예약 취소하기](#4-6-예약-취소하기)
    - [4-7. 빌링키 삭제하기](#4-7-빌링키-삭제하기)
    - [4-8. 빌링키 조회하기](#4-8-빌링키-조회하기)
  - [5. 회원 토큰 발급요청](#5-회원-토큰-발급요청)
  - [6. 서버 승인 요청](#6-서버-승인-요청)
  - [7. 본인 인증 결과 조회](#7-본인-인증-결과-조회)
  - [8. 에스크로 이용시 PG사로 배송정보 보내기](#8-에스크로-이용시-pg사로-배송정보-보내기)
  - [9. 현금영수증](#9-현금영수증)
    - [9-1. 현금영수증 발행하기](#9-1-현금영수증-발행하기)
    - [9-2. 현금영수증 발행 취소](#9-2-현금영수증-발행-취소)
    - [9-3. 별건 현금영수증 발행](#9-3-별건-현금영수증-발행)
    - [9-4. 별건 현금영수증 발행 취소](#9-4-별건-현금영수증-발행-취소)

### Commerce API
- [Commerce API 사용하기](#commerce-api-사용하기)
  - [1. Commerce 토큰 발급](#1-commerce-토큰-발급)
  - [2. 사용자 관리](#2-사용자-관리)
  - [3. 사용자 그룹 관리](#3-사용자-그룹-관리)
  - [4. 상품 관리](#4-상품-관리)
  - [5. 주문 관리](#5-주문-관리)
  - [6. 청구서 관리](#6-청구서-관리)
  - [7. 정기구독 관리](#7-정기구독-관리)

### 기타
- [Example 프로젝트](#example-프로젝트)
- [Documentation](#documentation)
- [기술문의](#기술문의)
- [License](#license)

---

## 설치하기

### Composer로 설치

```bash
composer require bootpay/server-php
```

### 요구사항

- PHP >= 5.3.0
- ext-json

---

# PG API

## 사용하기

```php
<?php
require_once 'vendor/autoload.php';

use Bootpay\ServerPhp\BootpayApi;

BootpayApi::setConfiguration(
    '5b8f6a4d396fa665fdc2b5ea',  // application_id
    'rm6EYECr6aroQVG2ntW0A6LpWnkTgP4uQ3H18sDDUYw='  // private_key
);

$response = BootpayApi::getAccessToken();
var_dump($response);
```

> 함수 단위의 샘플 코드는 [tests/pg](https://github.com/bootpay/backend-php/tree/main/tests/pg) 폴더를 참조하세요.

## 1. 토큰 발급

부트페이와 서버간 통신을 하기 위해서는 부트페이 서버로부터 토큰을 발급받아야 합니다.
발급된 토큰은 30분간 유효하며, 최초 발급일로부터 30분이 지날 경우 토큰 발급 함수를 재호출 해주셔야 합니다.

```php
<?php
require_once 'vendor/autoload.php';

use Bootpay\ServerPhp\BootpayApi;

BootpayApi::setConfiguration(
    '5b8f6a4d396fa665fdc2b5ea',
    'rm6EYECr6aroQVG2ntW0A6LpWnkTgP4uQ3H18sDDUYw='
);

$response = BootpayApi::getAccessToken();
var_dump($response);
```

## 2. 결제 단건 조회

결제창 및 정기결제에서 승인/취소된 결제건에 대하여 올바른 결제건인지 서버간 통신으로 결제검증을 합니다.

```php
$token = BootpayApi::getAccessToken();
if (!$token->error_code) {
    $response = BootpayApi::receiptPayment('receipt_id_here');
    var_dump($response);
}
```

## 3. 결제 취소 (전액 취소 / 부분 취소)

price를 지정하지 않으면 전액취소 됩니다.

```php
$token = BootpayApi::getAccessToken();
if (!$token->error_code) {
    $response = BootpayApi::cancelPayment(array(
        'receipt_id' => 'receipt_id_here',
        'cancel_price' => 1000,           // 부분취소 금액 (없으면 전액취소)
        'cancel_tax_free' => 0,
        'cancel_username' => '관리자',
        'cancel_message' => '테스트 결제 취소'
    ));
    var_dump($response);
}
```

## 4. 자동/빌링/정기 결제

### 4-1. 카드 빌링키 발급

REST API 방식으로 고객으로부터 카드 정보를 전달하여, PG사에게 빌링키를 발급받을 수 있습니다.

```php
$token = BootpayApi::getAccessToken();
if (!$token->error_code) {
    $response = BootpayApi::getSubscribeBillingKey(
        'nicepay',                    // PG사
        'subscription_' . time(),     // subscription_id
        '30일 정기권 결제',            // order_name
        '5570********1074',           // card_no
        '12',                         // card_pw (앞 2자리)
        '25',                         // card_expire_year
        '12',                         // card_expire_month
        '901012'                      // card_identity_no (생년월일 또는 사업자번호)
    );
    var_dump($response);
}
```

### 4-2. 계좌 빌링키 발급

```php
$token = BootpayApi::getAccessToken();
if (!$token->error_code) {
    $response = BootpayApi::requestSubscribeAutomaticTransferBillingKey(array(
        'pg' => 'nicepay',
        'subscription_id' => 'subscription_' . time(),
        'order_name' => '자동이체 등록',
        'auth_type' => 'ARS',
        'username' => '홍길동',
        'bank_name' => '국민은행',
        'bank_account' => '12345678901234',
        'identity_no' => '901012'
    ));
    var_dump($response);
}
```

### 4-3. 결제 요청하기

발급된 빌링키로 원하는 시점에 결제 승인 요청을 합니다.

```php
$token = BootpayApi::getAccessToken();
if (!$token->error_code) {
    $response = BootpayApi::requestSubscribeCardPayment(array(
        'billing_key' => 'billing_key_here',
        'order_name' => '정기결제 테스트',
        'price' => 1000,
        'order_id' => 'order_' . time()
    ));
    var_dump($response);
}
```

### 4-4. 결제 예약하기

```php
$token = BootpayApi::getAccessToken();
if (!$token->error_code) {
    $response = BootpayApi::subscribePaymentReserve(array(
        'billing_key' => 'billing_key_here',
        'order_name' => '예약결제 테스트',
        'price' => 1000,
        'order_id' => 'order_' . time(),
        'reserve_execute_at' => date('Y-m-d H:i:s', strtotime('+1 day'))
    ));
    var_dump($response);
}
```

### 4-5. 예약 조회하기

```php
$token = BootpayApi::getAccessToken();
if (!$token->error_code) {
    $response = BootpayApi::subscribePaymentReserveLookup('reserve_id_here');
    var_dump($response);
}
```

### 4-6. 예약 취소하기

```php
$token = BootpayApi::getAccessToken();
if (!$token->error_code) {
    $response = BootpayApi::cancelSubscribeReserve('reserve_id_here');
    var_dump($response);
}
```

### 4-7. 빌링키 삭제하기

```php
$token = BootpayApi::getAccessToken();
if (!$token->error_code) {
    $response = BootpayApi::destroyBillingKey('billing_key_here');
    var_dump($response);
}
```

### 4-8. 빌링키 조회하기

```php
$token = BootpayApi::getAccessToken();
if (!$token->error_code) {
    $response = BootpayApi::lookupSubscribeBillingKey('receipt_id_here');
    var_dump($response);
}
```

## 5. 회원 토큰 발급요청

부트페이에서 제공하는 간편결제창, 생체인증 기반의 결제 사용을 위해 사용자 토큰을 발급합니다.

```php
$token = BootpayApi::getAccessToken();
if (!$token->error_code) {
    $response = BootpayApi::requestUserToken(array(
        'user_id' => 'user_123',
        'phone' => '01012345678'
    ));
    var_dump($response);
}
```

## 6. 서버 승인 요청

```php
$token = BootpayApi::getAccessToken();
if (!$token->error_code) {
    $response = BootpayApi::confirmPayment('receipt_id_here');
    var_dump($response);
}
```

## 7. 본인 인증 결과 조회

```php
$token = BootpayApi::getAccessToken();
if (!$token->error_code) {
    $response = BootpayApi::certificate('receipt_id_here');
    var_dump($response);
}
```

## 8. 에스크로 이용시 PG사로 배송정보 보내기

```php
$token = BootpayApi::getAccessToken();
if (!$token->error_code) {
    $response = BootpayApi::shippingStart(array(
        'receipt_id' => 'receipt_id_here',
        'tracking_number' => '1234567890',
        'delivery_corp' => 'CJ대한통운',
        'user' => array(
            'username' => '홍길동',
            'phone' => '01012345678'
        )
    ));
    var_dump($response);
}
```

## 9. 현금영수증

### 9-1. 현금영수증 발행하기

기존 결제건에 대해 현금영수증을 발행합니다.

```php
$token = BootpayApi::getAccessToken();
if (!$token->error_code) {
    $response = BootpayApi::cashReceiptPublishOnReceipt(array(
        'receipt_id' => 'receipt_id_here',
        'identity_no' => '01012345678',
        'cash_receipt_type' => '소득공제'
    ));
    var_dump($response);
}
```

### 9-2. 현금영수증 발행 취소

```php
$token = BootpayApi::getAccessToken();
if (!$token->error_code) {
    $response = BootpayApi::cashReceiptCancelOnReceipt('receipt_id_here');
    var_dump($response);
}
```

### 9-3. 별건 현금영수증 발행

결제 건과 별개로 현금영수증을 발행합니다.

```php
$token = BootpayApi::getAccessToken();
if (!$token->error_code) {
    $response = BootpayApi::requestCashReceipt(array(
        'pg' => 'nicepay',
        'price' => 1000,
        'order_name' => '테스트 상품',
        'order_id' => 'order_' . time(),
        'cash_receipt_type' => '소득공제',
        'identity_no' => '01012345678'
    ));
    var_dump($response);
}
```

### 9-4. 별건 현금영수증 발행 취소

```php
$token = BootpayApi::getAccessToken();
if (!$token->error_code) {
    $response = BootpayApi::cancelCashReceipt('receipt_id_here');
    var_dump($response);
}
```

---

# Commerce API

## Commerce API 사용하기

```php
<?php
require_once 'vendor/autoload.php';

use Bootpay\ServerPhp\BootpayCommerceApi;

$bootpay = new BootpayCommerceApi(
    'your_client_key',
    'your_secret_key',
    'production'  // 또는 'development'
);

try {
    // 토큰 발급
    $bootpay->getAccessToken();

    // API 호출 예시
    $response = $bootpay->user->getList(array('page' => 1, 'limit' => 10));
    print_r($response);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
```

> 함수 단위의 샘플 코드는 [tests/commerce](https://github.com/bootpay/backend-php/tree/main/tests/commerce) 폴더를 참조하세요.

## 1. Commerce 토큰 발급

Commerce API 사용을 위한 토큰을 발급받습니다.

```php
$bootpay = new BootpayCommerceApi(
    'your_client_key',
    'your_secret_key',
    'production'
);

$bootpay->getAccessToken();
```

## 2. 사용자 관리

### 사용자 토큰 발급
```php
$response = $bootpay->user->token('user_id_here');
```

### 사용자 가입
```php
$response = $bootpay->user->join(array(
    'login_id' => 'test_user',
    'login_pw' => 'password123',
    'username' => '홍길동',
    'email' => 'test@example.com',
    'phone' => '01012345678'
));
```

### 사용자 목록 조회
```php
$response = $bootpay->user->getList(array(
    'page' => 1,
    'limit' => 10
));
```

### 사용자 상세 조회
```php
$response = $bootpay->user->detail('user_id_here');
```

### 사용자 정보 수정
```php
$response = $bootpay->user->update(array(
    'user_id' => 'user_id_here',
    'username' => '변경된 이름'
));
```

### 사용자 삭제
```php
$response = $bootpay->user->delete('user_id_here');
```

## 3. 사용자 그룹 관리

### 그룹 생성
```php
$response = $bootpay->userGroup->create(array(
    'company_name' => '테스트 회사',
    'business_number' => '1234567890',
    'ceo_name' => '홍길동',
    'corporate_type' => 2
));
```

### 그룹 목록 조회
```php
$response = $bootpay->userGroup->getList(array(
    'page' => 1,
    'limit' => 10
));
```

### 그룹 상세 조회
```php
$response = $bootpay->userGroup->detail('user_group_id_here');
```

### 그룹 수정
```php
$response = $bootpay->userGroup->update(array(
    'user_group_id' => 'user_group_id_here',
    'company_name' => '변경된 회사명'
));
```

## 4. 상품 관리

### 상품 목록 조회
```php
$response = $bootpay->product->getList(array(
    'page' => 1,
    'limit' => 10
));
```

### 상품 생성
```php
$response = $bootpay->product->create(array(
    'name' => '테스트 상품',
    'price' => 10000,
    'type' => 1
));
```

### 상품 상세 조회
```php
$response = $bootpay->product->detail('product_id_here');
```

### 상품 수정
```php
$response = $bootpay->product->update(array(
    'product_id' => 'product_id_here',
    'name' => '변경된 상품명'
));
```

### 상품 삭제
```php
$response = $bootpay->product->delete('product_id_here');
```

## 5. 주문 관리

### 주문 목록 조회
```php
$response = $bootpay->order->getList(array(
    'page' => 1,
    'limit' => 10
));
```

### 주문 상세 조회
```php
$response = $bootpay->order->detail('order_id_here');
```

### 주문 취소 요청
```php
$response = $bootpay->orderCancel->request(array(
    'order_id' => 'order_id_here',
    'cancel_reason' => '고객 요청'
));
```

## 6. 청구서 관리

### 청구서 목록 조회
```php
$response = $bootpay->invoice->getList(array(
    'page' => 1,
    'limit' => 10
));
```

### 청구서 생성
```php
$response = $bootpay->invoice->create(array(
    'user_id' => 'user_id_here',
    'price' => 10000,
    'order_name' => '청구서 테스트'
));
```

### 청구서 알림 발송
```php
$response = $bootpay->invoice->notify('invoice_id_here', array(
    'send_types' => array(1)  // 1: SMS
));
```

## 7. 정기구독 관리

### 정기구독 목록 조회
```php
$response = $bootpay->orderSubscription->getList(array(
    'page' => 1,
    'limit' => 10
));
```

### 정기구독 상세 조회
```php
$response = $bootpay->orderSubscription->detail('order_subscription_id_here');
```

### 정기구독 일시정지
```php
$response = $bootpay->orderSubscription->requestIng->pause(array(
    'order_subscription_id' => 'order_subscription_id_here'
));
```

### 정기구독 재개
```php
$response = $bootpay->orderSubscription->requestIng->resume(array(
    'order_subscription_id' => 'order_subscription_id_here'
));
```

### 해지 수수료 계산
```php
$response = $bootpay->orderSubscription->requestIng->calculateTerminationFee(array(
    'order_subscription_id' => 'order_subscription_id_here'
));
```

### 정기구독 해지
```php
$response = $bootpay->orderSubscription->requestIng->termination(array(
    'order_subscription_id' => 'order_subscription_id_here'
));
```

## Role 설정

Commerce API에서는 역할(Role)에 따라 접근 권한이 달라집니다.

```php
// 매니저 역할로 설정
$bootpay->asManager();

// 사용자 역할로 설정
$bootpay->asUser();

// 파트너 역할로 설정
$bootpay->asPartner();

// 역할 초기화
$bootpay->clearRole();
```

---

## Example 프로젝트

[적용한 샘플 프로젝트](https://github.com/bootpay/backend-php-example)를 참조해주세요

## Documentation

[부트페이 개발매뉴얼](https://docs.bootpay.co.kr/next/)을 참조해주세요

## 기술문의

[부트페이 홈페이지](https://www.bootpay.co.kr) 우측 하단 채팅을 통해 기술문의 주세요!

## License

[MIT License](https://opensource.org/licenses/MIT).
