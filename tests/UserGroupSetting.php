<?php
/*
 * 회원그룹 구매한도 / 구독 합산청구 설정 예제입니다.
 * PUT user-groups/{user_group_id}/limit, PUT user-groups/{user_group_id}/aggregate-transaction
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
    // PUT user-groups/{user_group_id}/limit
    // ⚠️ 구매한도는 회원그룹 수정 API로는 반영되지 않으며 이 전용 API로만 변경됩니다.
    $limit = BootpayCommerceApi::userGroupLimit(1, array(
        'use_limit' => 1,
        'limit_month_purchase' => 1000000,
        'limit_week_purchase' => 300000,
        'limit_message' => '월 구매한도를 초과하였습니다.'
    ));
    var_dump($limit);

    // PUT user-groups/{user_group_id}/aggregate-transaction
    $aggregate = BootpayCommerceApi::userGroupAggregateTransaction(1, array(
        'use_subscription_aggregate_transaction' => 1,
        'subscription_month_day' => 15,
        'subscription_week_day' => 1
    ));
    var_dump($aggregate);
} catch (Exception $e) {
    echo($e->getMessage());
}
