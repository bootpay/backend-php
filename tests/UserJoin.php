<?php
/*
 * 회원가입 / 회원가입 중복확인 예제입니다.
 * POST users/join, GET users/join/{type}?pk={pk}
 */
require_once '../vendor/autoload.php';
// require_once __DIR__.'/../src/BootpayCommerceApi.php';

use Bootpay\ServerPhp\BootpayCommerceApi;

BootpayCommerceApi::setConfiguration(
    'QIzXk4M3EeD-6B1GTfmGHA',
    'vRle44QfyBj7nzJlBbeebqkbtlJVRTS2DQa9Adpz3d8=',
    'development'
);

try {
    // GET users/join/id-exist?pk=test@bootpay.co.kr
    // type: email-exist, id-exist, phone-exist, uid-exist, group-business-number-exist
    $exist = BootpayCommerceApi::userJoinCheck('id-exist', 'test@bootpay.co.kr');
    var_dump($exist);

    // POST users/join (전달한 값만 서버로 전송됩니다)
    $join = BootpayCommerceApi::userJoin(array(
        'login_id' => 'test@bootpay.co.kr',
        'password' => 'test1234!',
        'name' => '홍길동',
        'email' => 'test@bootpay.co.kr',
        'phone' => '01000000000',
        'nickname' => '길동이',
        'gender' => 1,
        'birth' => '1990-01-01',
        'corporate_type' => 0
    ));
    var_dump($join);

    // GET users/join/uid-exist?pk=ex_user_0001 (외부 uid 중복검사 전용형)
    $uidExist = BootpayCommerceApi::uidExist('ex_user_0001');
    var_dump($uidExist);
} catch (Exception $e) {
    echo($e->getMessage());
}
