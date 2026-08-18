<?php
/*
 * 회원 정보 수정 예제입니다.
 * PUT users/{user_id}
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
    // PUT users/{user_id} (바뀐 값만 전달합니다)
    $response = BootpayCommerceApi::userUpdate(1, array(
        'name' => '홍길동',
        'phone' => '01000000000',
        'email' => 'test@bootpay.co.kr',
        'nickname' => '길동이',
        'bank_username' => '홍길동',
        'bank_account' => '1234567890',
        'bank_code' => '004',
        // 사업자 정보는 group으로 중첩 전달합니다
        'group' => array(
            'company_name' => '부트페이',
            'business_number' => '1234567890',
            'registration_number' => '1101110000000'
        )
    ));
    var_dump($response);
} catch (Exception $e) {
    echo($e->getMessage());
}
