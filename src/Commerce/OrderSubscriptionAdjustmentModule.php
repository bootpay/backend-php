<?php

namespace Bootpay\ServerPhp\Commerce;

use Bootpay\ServerPhp\BootpayCommerceApi;

class OrderSubscriptionAdjustmentModule
{
    private $bootpay;

    public function __construct(BootpayCommerceApi $bootpay)
    {
        $this->bootpay = $bootpay;
    }

    /**
     * 정기구독 조정 생성
     * @param string $orderSubscriptionId 정기구독 ID
     * @param array $adjustment 조정 정보
     * @return object
     */
    public function create($orderSubscriptionId, $adjustment)
    {
        return $this->bootpay->post("order_subscriptions/{$orderSubscriptionId}/adjustments", $adjustment);
    }

    /**
     * 정기구독 조정 수정
     * @param array $params 수정 파라미터
     * @return object
     * @throws \Exception
     */
    public function update($params)
    {
        if (!isset($params['order_subscription_id']) || empty($params['order_subscription_id'])) {
            throw new \Exception('order_subscription_id is required');
        }
        $orderSubscriptionId = $params['order_subscription_id'];
        return $this->bootpay->put("order_subscriptions/{$orderSubscriptionId}/adjustments", $params);
    }

    /**
     * 정기구독 조정 삭제
     * @param string $orderSubscriptionId 정기구독 ID
     * @param string $orderSubscriptionAdjustmentId 조정 ID
     * @return object
     */
    public function delete($orderSubscriptionId, $orderSubscriptionAdjustmentId)
    {
        return $this->bootpay->delete(
            "order_subscriptions/{$orderSubscriptionId}/adjustments?order_subscription_adjustment_id={$orderSubscriptionAdjustmentId}"
        );
    }
}
