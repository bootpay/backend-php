<?php
/*
 * 회원 로그인 / 세션 조회 / 로그아웃 예제입니다.
 * POST users/login, GET users/session, DELETE users/session
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
    // POST users/login
    $login = BootpayCommerceApi::userLogin(
        'test@bootpay.co.kr',
        'test1234!',
        0
    );
    var_dump($login);

    $userJwt = isset($login->data->token) ? $login->data->token : null;

    // GET users/session
    $session = BootpayCommerceApi::userSession($userJwt);
    var_dump($session);

    // DELETE users/session
    $logout = BootpayCommerceApi::userLogout($userJwt);
    var_dump($logout);
} catch (Exception $e) {
    echo($e->getMessage());
}
