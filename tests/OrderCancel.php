<?php
/*
 * 주문 취소 요청 내역 조회 / 취소 요청 철회 예제입니다.
 * GET order/cancel, PUT order/cancel/{order_cancellation_request_id}/withdraw
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
    // GET order/cancel (order_number 또는 order_id로 필터링하며 둘 다 없으면 전체 조회입니다)
    $list = BootpayCommerceApi::orderCancelList('20260814000001');
    var_dump($list);

    // 승인/반려/철회에 사용할 order_cancellation_request_id를 목록에서 얻습니다.
    $requestId = isset($list->data[0]->id) ? $list->data[0]->id : 1;

    // PUT order/cancel/{order_cancellation_request_id}/withdraw (구매자가 취소 요청을 철회합니다)
    $withdraw = BootpayCommerceApi::orderCancelWithdraw($requestId);
    var_dump($withdraw);
} catch (Exception $e) {
    echo($e->getMessage());
}
