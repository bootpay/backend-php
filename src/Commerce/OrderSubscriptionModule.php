<?php

namespace Bootpay\ServerPhp\Commerce;

use Bootpay\ServerPhp\BootpayCommerceApi;

class OrderSubscriptionRequestIngModule
{
    private $bootpay;

    public function __construct(BootpayCommerceApi $bootpay)
    {
        $this->bootpay = $bootpay;
    }

    /**
     * 정기구독 일시정지
     * @param array $params 일시정지 파라미터
     * @return object
     */
    public function pause($params)
    {
        return $this->bootpay->post('order_subscriptions/requests/ing/pause', $params);
    }

    /**
     * 정기구독 재개
     * @param array $params 재개 파라미터
     * @return object
     */
    public function resume($params)
    {
        return $this->bootpay->put('order_subscriptions/requests/ing/resume', $params);
    }

    /**
     * 해지 수수료 계산
     * @param string|null $orderSubscriptionId 정기구독 ID (선택)
     * @param string|null $orderNumber 주문번호 (선택)
     * @return object
     * @throws \Exception
     */
    public function calculateTerminationFee($orderSubscriptionId = null, $orderNumber = null)
    {
        if ($orderSubscriptionId === null && $orderNumber === null) {
            throw new \Exception('orderSubscriptionId or orderNumber is required');
        }

        $query = array();
        if ($orderSubscriptionId !== null) {
            $query['order_subscription_id'] = $orderSubscriptionId;
        } elseif ($orderNumber !== null) {
            $query['order_number'] = $orderNumber;
        }

        $queryString = http_build_query($query);
        return $this->bootpay->get("order_subscriptions/requests/ing/calculate_termination_fee?{$queryString}");
    }

    /**
     * 주문번호로 해지 수수료 계산
     * @param string $orderNumber 주문번호
     * @return object
     */
    public function calculateTerminationFeeByOrderNumber($orderNumber)
    {
        return $this->calculateTerminationFee(null, $orderNumber);
    }

    /**
     * 정기구독 해지
     * @param array $params 해지 파라미터
     * @return object
     */
    public function termination($params)
    {
        return $this->bootpay->post('order_subscriptions/requests/ing/termination', $params);
    }
}

class OrderSubscriptionModule
{
    private $bootpay;
    public $requestIng;

    public function __construct(BootpayCommerceApi $bootpay)
    {
        $this->bootpay = $bootpay;
        $this->requestIng = new OrderSubscriptionRequestIngModule($bootpay);
    }

    /**
     * 정기구독 목록 조회
     * @param array|null $params 조회 파라미터
     * @return object
     */
    public function getList($params = null)
    {
        $queryString = $this->buildQueryString($params);
        return $this->bootpay->get('order_subscriptions' . $queryString);
    }

    /**
     * 정기구독 상세 조회
     * @param string $orderSubscriptionId 정기구독 ID
     * @return object
     */
    public function detail($orderSubscriptionId)
    {
        return $this->bootpay->get("order_subscriptions/{$orderSubscriptionId}");
    }

    /**
     * 정기구독 수정
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
        return $this->bootpay->put("order_subscriptions/{$orderSubscriptionId}", $params);
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
        if (isset($params['s_at'])) {
            $query['s_at'] = $params['s_at'];
        }
        if (isset($params['e_at'])) {
            $query['e_at'] = $params['e_at'];
        }
        if (isset($params['request_type'])) {
            $query['request_type'] = $params['request_type'];
        }
        if (isset($params['user_group_id'])) {
            $query['user_group_id'] = $params['user_group_id'];
        }
        if (isset($params['user_id'])) {
            $query['user_id'] = $params['user_id'];
        }

        if (empty($query)) {
            return '';
        }

        return '?' . http_build_query($query);
    }
}
