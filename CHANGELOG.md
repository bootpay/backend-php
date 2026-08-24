### 2.6.0

#### Commerce scope(BOOTPAY-ROLE) 정합성 (동작 변경)

서버(commerce-api)가 `scope_invalid!` 로 supervisor / manager scope 를 요구하는 10개 엔드포인트가 `BOOTPAY-ROLE: user` 로 나가고 있었다. 요청 단위로 올바른 scope 를 붙인다. Java SDK 3.3.0 · Ruby SDK 와 같은 규약이다.

- `orderSubscription` — `supervisorApprove` / `supervisorReject` / `supervisorTerminate` / `supervisorPause` / `supervisorResume` → **supervisor**
- `category` — `create` / `update` / `destroy` → **supervisor**
- `userGroup` — `userCreate` / `userDelete` → **manager**

부수 효과로 이 10개 호출에 `Idempotency-Key` 가 자동 부착된다 (다른 supervisor 메서드·Ruby SDK 와 동일). 요청 경로·바디는 변경 없다.
⚠️ 그동안 이 API 들은 올바른 키로도 scope 오류로 거절됐다. 우회하려고 role 을 직접 조작하던 코드가 있다면 제거해도 된다.

- 파라미터 배열에 `idempotency_key` (optional) 를 추가했다. 지정하면 그 값이 `Idempotency-Key` 헤더로 나가고 바디에는 실리지 않는다. `category->destroy($categoryId, $idempotencyKey = null)` / `userGroup->userCreate($userGroupId, $userId, $idempotencyKey = null)` / `userGroup->userDelete(...)` 는 선택 인자로 받는다.

#### 알 수 없는 mode 는 production 폴백 (버그 수정)

- `BootpayApi` · `BootpayCommerceApi` 가 `$API_URL[$mode]` 를 그대로 인덱싱해서, mode 오타 하나에 Warning 이 뜨고 base URL 이 빈 문자열로 붕괴 → 요청 URL 이 `/{path}` 가 되어 curl errno 3 (`URL rejected: No host part in the URL`) 이 났다. production 은 `display_errors=Off` 라 Warning 이 보이지 않아 **mode 와 무관해 보이는 curl 에러만 남았다.**
- 이제 알 수 없는 mode 는 `production` 으로 폴백한다 (Go · Java SDK 와 같은 규칙).

#### 테스트

- `tests/commerce/CommerceScopeTest.php` 신설 (offline 스위트에 등록) — 10개 엔드포인트의 scope·Idempotency-Key 와 mode 폴백 회귀.
- `CommerceHttpWireTest` 의 `BOOTPAY-SDK-VERSION` 단정에서 버전 리터럴을 걷어내고 `composer.json` 의 패키지 버전을 따라가는지로 바꿨다 (버전 올릴 때마다 깨지던 테스트).

### 2.5.0

NodeJS SDK 2.9.0 parity.

* PG: `BootpayApi::lookupSequentialBillingKey($widgetKey, $billingKey, $userId)` 추가 — `GET subscribe/sequential_billing_key/{billing_key}?widget_key=&user_id=` (우선순위/순차 결제 빌링키 조회).
* Commerce: 신규 모듈 2종 추가.
  * `MallSettingModule` (`mallSetting`) — `getMallSetting`/`detail`, `updateMallSetting`/`update` (supervisor 전용, update 는 flatten 바디에 null 값 미전송).
  * `WebhookModule` (`webhook`) — `sendTest([...])` (`POST webhook/test`, `header_content_type` 파라미터).
* Commerce: 수시결제(온디맨드) charge_key 결제/해지 추가 (supervisor 전용).
  * `orderSubscription->supervisorCharge($params)` — `POST order_subscriptions/charge`. charge_key 는 body 로만 전송 (URL/query 금지).
  * `orderSubscription->supervisorChargeRevoke($params)` — `DELETE order_subscriptions/charge` (body 전송). 해지 후 해당 키로 재결제 불가.
  * 두 endpoint 모두 `Idempotency-Key` 헤더 자동 생성 (`idempotency_key` 파라미터로 직접 지정 가능).
* Commerce: 쇼핑몰(V1 Mall API) 회원 endpoint 추가 — 복수형 `users/...` 경로 (v1 에 단수 `user/...` 라우트 없음).
  * `user->userLogin`/`userSession`/`userLogout`/`userJoin`/`userJoinCheck`/`uidExist`.
  * 세션이 필요한 호출은 회원 JWT 를 `Bootpay-User-JWT` 헤더로 전달 (값이 있을 때만 부착).
* Commerce: 상품 조회 Mall API 추가.
  * `product->products($params)` — `page`/`limit` 기본값 1/20, `category_id`/`sort` 파라미터 및 `user_jwt` 지원.
  * `product->productDetail($productId, $userJwt, $idempotencyKey)`.
* Commerce: 구독 변경 요청 endpoint 추가 — `orderSubscription->requestIng->purchase()` (중도인수) / `transfer()` (이전/승계).
* Commerce: multipart 전송 정비 — `product->create()` 는 이미지가 없으면 JSON, 있으면 multipart(`images[0]`, `images[1]` … 인덱싱)로 전송. multipart 요청에도 요청별 헤더(Idempotency-Key, BOOTPAY-ROLE)가 병합되며 Content-Type 은 지정하지 않아 boundary 가 유실되지 않는다.
  * multipart 의 boolean 필드는 `'true'`/`'false'` 문자열로 직렬화 (curl 기본 직렬화는 `false`→`""` 로 서버에서 nil 로 읽히는 문제가 있었다 — NodeJS `String(bool)` 동등).
* Commerce: 인자·응답 규약 정정.
  * `invoice->getList` 응답 data 는 `{ list, count }` — phpdoc 정정, `limit` 기본값 24, `cs_type`/`user_id`/`product_type`/`css_at`/`cse_at` 파라미터 추가.
  * `invoice->notify` 의 `$sendTypes` 를 선택 인자로 변경 (미전달시 서버가 빈 배열로 처리).
  * `orderCancel->approve`/`reject`/`withdraw` 인자명을 `order_cancellation_request_id` 로 통일 (구 이름 `order_cancel_request_history_id` 도 계속 지원). `withdraw` 는 문자열/배열 모두 허용.
  * `orderSubscriptionAdjustment->delete` 는 대상 ID 를 query 가 아니라 body 로 전송.
  * `orderSubscriptionAdjustment->update` 에 `adjustments` 배열 지원 (서버는 `duration` 회차 단위로 교체), `duration` 기본값 1.
  * `userGroup->limit` 에 `limit_month_purchase`/`limit_week_purchase` 지원 명시 (서버 정식 인자명; `update` 로는 한도가 반영되지 않는다).
  * `order->getList` 에 `search_date_from`/`search_date_to` 추가 (`css_at`/`cse_at` 는 서버 별칭으로 계속 지원).
  * `orderSubscription->getList` 에 `search_date_from`/`search_date_to`/`status` 추가.
  * `orderSubscriptionRequest->getList` 에 `order_subscription_id`/`user_id`/`user_group_id` 추가, `page`/`limit` 기본값 1/20.
  * `orderSubscriptionBill->getList` 에 `page`/`limit` 기본값 1/20 적용 (NodeJS HEAD 동작 미러링).
* Commerce: 서버가 요구하는 `BOOTPAY-ROLE` scope 를 endpoint 별로 명시 — 상품 쓰기/그룹 한도는 `manager`, 구독 계약변경·조정항목·요청 승인·charge·mallSetting 은 `supervisor`, 나머지는 `user`.
  * `orderSubscriptionRequest->getList`/`detail` 은 `project_id` 가 있으면 `supervisor`, 없으면 `user`.
  * 요청별로 지정된 `BOOTPAY-ROLE` 헤더를 공통 계층이 기본 role 과 중복 전송하지 않도록 수정 (`mergeHeaders`).
  * `store->getStore`/`getStoreDetail` (`info`/`detail` 별칭 유지) 에 `Idempotency-Key` 헤더 부착 (인자로 직접 지정 가능).
* 공통: `BootpayCommerceApi::delete()` 에 optional `$data` 인자 추가 (DELETE body 지원, 기존 호출 불변). `generateIdempotencyKey()` (uuid v4) 추가.
* 테스트: 네트워크 없는 wire-format 검증 인프라 추가.
  * mock 계층 — `tests/RecordingCommerceApi.php` + `tests/commerce/CommerceWireFormatTest.php` (모듈이 공통 계층에 넘기는 URL·헤더·바디 검증).
  * 실전송 계층 — `tests/wire-echo-server.php` + `tests/WireEchoTestCase.php` + `tests/commerce/CommerceHttpWireTest.php` + `tests/pg/PgHttpWireTest.php` (로컬 echo 서버로 curl 이 실제 전송하는 wire 검증: multipart boundary 유지, `images[n]` 인덱싱, DELETE body, 헤더 병합 결과).
  * 하위호환 — `tests/commerce/BackwardCompatibilityTest.php` (v2.4.1 공개 API 표면 동결 계약: 메서드 존재·public·필수 파라미터 수 미증가). `tests/echo-suite-bootstrap.php` 로 기존 라이브 테스트 스위트를 echo 서버로 회귀 실행 가능.
  * 신규 endpoint 라이브 스크립트 추가 (development 전용).
  * phpunit 안전 구조 — `phpunit.xml` 에 `defaultTestSuite="offline"` 도입: 무인자 `vendor/bin/phpunit` 은 오프라인 스위트만 실행 (외부 요청 0건). 라이브 테스트(`PG`/`Commerce` 스위트)는 `TestConfig::requireLiveEnvironment()` 가드로 `BOOTPAY_ENV=development` 가 아니면 skip (production 실호출 방지).

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