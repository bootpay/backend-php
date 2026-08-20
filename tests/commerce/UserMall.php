<?php
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../config.php';

use Bootpay\ServerPhp\BootpayCommerceApi;

// 쇼핑몰(V1 Mall API) 회원 로그인/세션/가입/중복확인 — development 전용 라이브 스크립트
// 복수형 users/... 경로 (v1 에 단수 user/... 라우트 없음)
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
    // 회원 로그인 — POST users/login
    $response = $bootpay->user->userLogin(array(
        'login_id' => 'test_user',
        'password' => 'test_password'
    ));
    print_r($response);

    $userJwt = isset($response->data->user_jwt) ? $response->data->user_jwt : null;

    if ($userJwt) {
        // 세션 조회 — GET users/session (Bootpay-User-JWT 헤더)
        $session = $bootpay->user->userSession($userJwt);
        print_r($session);

        // 로그아웃 — DELETE users/session
        $logout = $bootpay->user->userLogout($userJwt);
        print_r($logout);
    }

    // 가입 중복 확인 — GET users/join/{type}?pk=
    $exists = $bootpay->user->userJoinCheck('email-exist', 'test@bootpay.co.kr');
    print_r($exists);

    // 외부 uid 중복 확인 — GET users/join/uid-exist?pk=
    $uidExists = $bootpay->user->uidExist('external-uid-001');
    print_r($uidExists);

    // 일반 회원가입 — POST users/join (null 값은 전송되지 않는다)
    // $join = $bootpay->user->userJoin(array(
    //     'login_id' => 'new_user',
    //     'password' => 'password',
    //     'name' => '테스터'
    // ));
    // print_r($join);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
