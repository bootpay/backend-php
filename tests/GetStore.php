<?php
/*
 * 가맹점 정보 조회 예제입니다.
 * GET store, GET store/detail
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
    // GET store
    $store = BootpayCommerceApi::getStore();
    var_dump($store);

    // GET store/detail
    $storeDetail = BootpayCommerceApi::getStoreDetail();
    var_dump($storeDetail);
} catch (Exception $e) {
    echo($e->getMessage());
}
