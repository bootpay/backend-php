<?php
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../config.php';

use Bootpay\ServerPhp\BootpayCommerceApi;

// 상품 조회 (V1 Mall API) — development 전용 라이브 스크립트
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
    // 상품 목록 — GET products (page/limit 기본 1/20)
    $response = $bootpay->product->products(array(
        'page' => 1,
        'limit' => 5
        // 'category_id' => 'category_id_here',
        // 'sort' => '-created_at',
        // 'user_jwt' => 'user_jwt_here'
    ));
    print_r($response);

    // 상품 상세 — GET products/{product_id}
    if (isset($response->data->items[0]->product_id)) {
        $detail = $bootpay->product->productDetail($response->data->items[0]->product_id);
        print_r($detail);
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
