<?php
/*
 * 몰 설정 조회/수정 예제입니다. (supervisor 전용)
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
    // GET mall-setting
    $response = BootpayCommerceApi::getMallSetting();
    var_dump($response);

    // PUT mall-setting (전달한 값만 갱신됩니다)
    $updated = BootpayCommerceApi::updateMallSetting(array(
        'name' => '부트페이 테스트 몰',
        'description' => '부트페이 SDK 테스트로 갱신된 몰 설명',
        'use_notice' => true,
        'use_qna' => true,
        'use_faq' => true,
        'customer_service_center_operation_time' => array(
            'mon' => array('use' => true, 'start_hour' => 9, 'start_minute' => 0, 'end_hour' => 18, 'end_minute' => 0),
            'tue' => array('use' => true, 'start_hour' => 9, 'start_minute' => 0, 'end_hour' => 18, 'end_minute' => 0),
            'wed' => array('use' => true, 'start_hour' => 9, 'start_minute' => 0, 'end_hour' => 18, 'end_minute' => 0),
            'thu' => array('use' => true, 'start_hour' => 9, 'start_minute' => 0, 'end_hour' => 18, 'end_minute' => 0),
            'fri' => array('use' => true, 'start_hour' => 9, 'start_minute' => 0, 'end_hour' => 18, 'end_minute' => 0),
            'sat' => array('use' => false, 'start_hour' => 0, 'start_minute' => 0, 'end_hour' => 0, 'end_minute' => 0),
            'sun' => array('use' => false, 'start_hour' => 0, 'start_minute' => 0, 'end_hour' => 0, 'end_minute' => 0)
        )
    ));
    var_dump($updated);
} catch (Exception $e) {
    echo($e->getMessage());
}
