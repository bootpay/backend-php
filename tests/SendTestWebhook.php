<?php
/*
 * 테스트 웹훅 발송 예제입니다.
 * POST webhook/test
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
    // POST webhook/test
    $response = BootpayCommerceApi::sendTestWebhook('application/json');
    var_dump($response);

    // header_content_type 없이도 호출할 수 있습니다.
    $defaultResponse = BootpayCommerceApi::sendTestWebhook();
    var_dump($defaultResponse);
} catch (Exception $e) {
    echo($e->getMessage());
}
