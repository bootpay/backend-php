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