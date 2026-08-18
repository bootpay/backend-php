<?php
/*
 * 상품 등록 / 수정 / 상태변경 / 삭제 예제입니다.
 * POST products, PUT products/{product_id}, PUT products/{product_id}/status, DELETE products/{product_id}
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
    // POST products (images가 없으면 JSON으로 전송됩니다)
    $product = BootpayCommerceApi::productCreate(array(
        'name' => '테스트 상품',
        'display_price' => 10000,
        'desc' => '상품 설명입니다',
        'category_id' => 1,
        'stock' => 100,
        'status_sale' => 1,
        'status_display' => 1
    ));
    var_dump($product);

    // POST products (images가 있으면 multipart/form-data로 전송됩니다)
    // 파일 경로 문자열 또는 CURLFile 객체를 전달할 수 있습니다.
    $productWithImage = BootpayCommerceApi::productCreate(array(
        'name' => '이미지가 있는 테스트 상품',
        'display_price' => 20000,
        'images' => array('./sample1.jpg', './sample2.jpg')
    ));
    var_dump($productWithImage);

    $productId = isset($product->data->id) ? $product->data->id : 1;

    // PUT products/{product_id} (바뀐 값만 전달합니다)
    $updated = BootpayCommerceApi::productUpdate($productId, array(
        'display_price' => 12000,
        'stock' => 50
    ));
    var_dump($updated);

    // PUT products/{product_id}/status (재고는 productUpdate로 변경합니다)
    $status = BootpayCommerceApi::productStatus($productId, array(
        'status_sale' => 1,
        'status_display' => 0,
        'use_sale_period' => 1,
        'sale_start_at' => '2026-08-01 00:00:00',
        'sale_end_at' => '2026-12-31 23:59:59'
    ));
    var_dump($status);

    // DELETE products/{product_id}
    $deleted = BootpayCommerceApi::productDelete($productId);
    var_dump($deleted);
} catch (Exception $e) {
    echo($e->getMessage());
}
