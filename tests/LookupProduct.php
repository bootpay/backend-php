<?php
/*
 * 상품 목록 / 상품 상세 조회 예제입니다.
 * GET products, GET products/{product_id}
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
    // GET products?page=1&limit=20
    $products = BootpayCommerceApi::products(array(
        'page' => 1,
        'limit' => 20,
        'keyword' => '테스트'
    ));
    var_dump($products);

    // GET products/{product_id}
    $productDetail = BootpayCommerceApi::productDetail('66542dfb4d18d5fc7b43e1b6');
    var_dump($productDetail);

    // GET products/{product_id} (Bootpay-User-JWT 없이 조회)
    $product = BootpayCommerceApi::lookupProduct('66542dfb4d18d5fc7b43e1b6');
    var_dump($product);
} catch (Exception $e) {
    echo($e->getMessage());
}
