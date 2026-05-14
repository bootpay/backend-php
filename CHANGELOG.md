### 2.4.1
* PHP 8.5 호환성: `curl_close()` 호출 제거 (PHP 8.0+ 부터 no-op, PHP 8.5 부터 deprecated). 동작 변화 없음.
  * `src/BootpayApi.php`, `src/BootpayCommerceApi.php` (json + multipart).

### 2.4.0
* Commerce: 5개 모듈 신규 추가 (NodeJS SDK parity).
  * `Category`, `Coupon`, `Point`, `Cart`, `OrderSubscriptionRequest` 모듈을 `src/Commerce/` 에 추가.
  * `BootpayCommerceApi` facade 에 5개 모듈 wire-up.
  * `list` 가 PHP 예약어 충돌이 있는 위치에서는 `getList()` 네이밍 사용.
* Commerce: user-group URL parity 수정 — `/add_user` → `/user`, `/remove_user` → `/user/{userId}`.
* chore: `vendor/` 디렉토리 gitignore 처리 및 트래킹 해제 (composer build artifact).

### 2.3.0
* 인증: client_key/secret_key Basic Auth 지원 (PG + Commerce 공통).
  * 기존 application_id/private_key Bearer 방식 하위 호환 유지.
  * `BootpayApi::setClientKeyConfiguration($clientKey, $secretKey, $mode)` 추가 — application_id/private_key 와 같이 쓸 경우 ck/sk 가 우선.
  * ck/sk 모드는 매 요청 자동 Basic Auth 헤더 부착 — `getAccessToken()` 은 합성 응답 (`{access_token: '', expire_in: 0}`) 을 반환하며 `request/token` 호출이 발생하지 않음.
* Commerce: `BootpayCommerceApi` 의 모든 호출이 ck/sk Basic Auth 사용.
* `getUserWallets`, `requestWalletPayment` `@deprecated` 처리 (`E_USER_DEPRECATED`) — 다음 메이저 버전에서 제거 예정.
* 테스트 인프라: `.env` / `BOOTPAY_AUTH_MODE=new|legacy` / `BOOTPAY_ENV` 토글로 ck/sk · legacy 양쪽 검증. `tests/pg/LegacyCompatibilityTest.php` 단위 테스트 추가.

### 2.2.0
Commerce API 추가

### 2.0.0
v1 -> v2 update