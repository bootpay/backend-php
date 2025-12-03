<?php

namespace Bootpay\ServerPhp\Commerce;

use Bootpay\ServerPhp\BootpayCommerceApi;

class OrderSubscriptionBillModule
{
    private $bootpay;

    public function __construct(BootpayCommerceApi $bootpay)
    {
        $this->bootpay = $bootpay;
    }

    /**
     * 정기구독 청구 목록 조회
     * @param array|null $params 조회 파라미터
     * @return object
     */
    public function getList($params = null)
    {
        $queryString = $this->buildQueryString($params);
        return $this->bootpay->get('order_subscription_bills' . $queryString);
    }

    /**
     * 정기구독 청구 상세 조회
     * @param string $orderSubscriptionBillId 청구 ID
     * @return object
     */
    public function detail($orderSubscriptionBillId)
    {
        return $this->bootpay->get("order_subscription_bills/{$orderSubscriptionBillId}");
    }

    /**
     * 정기구독 청구 수정
     * @param array $orderSubscriptionBill 청구 정보
     * @return object
     * @throws \Exception
     */
    public function update($orderSubscriptionBill)
    {
        if (!isset($orderSubscriptionBill['order_subscription_bill_id']) || empty($orderSubscriptionBill['order_subscription_bill_id'])) {
            throw new \Exception('order_subscription_bill_id is required');
        }
        $billId = $orderSubscriptionBill['order_subscription_bill_id'];
        return $this->bootpay->put("order_subscription_bills/{$billId}", $orderSubscriptionBill);
    }

    private function buildQueryString($params)
    {
        if ($params === null || empty($params)) {
            return '';
        }

        $query = array();
        if (isset($params['page'])) {
            $query['page'] = $params['page'];
        }
        if (isset($params['limit'])) {
            $query['limit'] = $params['limit'];
        }
        if (isset($params['keyword'])) {
            $query['keyword'] = $params['keyword'];
        }
        if (isset($params['order_subscription_id'])) {
            $query['order_subscription_id'] = $params['order_subscription_id'];
        }
        if (isset($params['status']) && is_array($params['status'])) {
            $query['status'] = implode(',', $params['status']);
        }

        if (empty($query)) {
            return '';
        }

        return '?' . http_build_query($query);
    }
}
