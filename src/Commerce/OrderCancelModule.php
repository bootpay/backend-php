<?php

namespace Bootpay\ServerPhp\Commerce;

use Bootpay\ServerPhp\BootpayCommerceApi;

class OrderCancelModule
{
    private $bootpay;

    public function __construct(BootpayCommerceApi $bootpay)
    {
        $this->bootpay = $bootpay;
    }

    /**
     * 취소 요청 목록 조회
     * @param array|null $params 조회 파라미터
     * @return object
     */
    public function getList($params = null)
    {
        $queryString = $this->buildQueryString($params);
        return $this->bootpay->get('order/cancel' . $queryString);
    }

    /**
     * 취소 요청
     * @param array $params 취소 요청 파라미터
     * @return object
     */
    public function request($params)
    {
        return $this->bootpay->post('order/cancel', $params);
    }

    /**
     * 취소 요청 철회
     * @param string $orderCancelRequestHistoryId 취소 요청 이력 ID
     * @return object
     */
    public function withdraw($orderCancelRequestHistoryId)
    {
        return $this->bootpay->put("order/cancel/{$orderCancelRequestHistoryId}/withdraw", array());
    }

    /**
     * 취소 승인
     * @param array $params 취소 승인 파라미터
     * @return object
     * @throws \Exception
     */
    public function approve($params)
    {
        if (!isset($params['order_cancel_request_history_id']) || empty($params['order_cancel_request_history_id'])) {
            throw new \Exception('order_cancel_request_history_id is required');
        }
        $historyId = $params['order_cancel_request_history_id'];
        return $this->bootpay->put("order/cancel/{$historyId}/approve", $params);
    }

    /**
     * 취소 거절
     * @param array $params 취소 거절 파라미터
     * @return object
     * @throws \Exception
     */
    public function reject($params)
    {
        if (!isset($params['order_cancel_request_history_id']) || empty($params['order_cancel_request_history_id'])) {
            throw new \Exception('order_cancel_request_history_id is required');
        }
        $historyId = $params['order_cancel_request_history_id'];
        return $this->bootpay->put("order/cancel/{$historyId}/reject", $params);
    }

    private function buildQueryString($params)
    {
        if ($params === null || empty($params)) {
            return '';
        }

        $query = array();
        if (isset($params['order_id'])) {
            $query['order_id'] = $params['order_id'];
        }
        if (isset($params['order_number'])) {
            $query['order_number'] = $params['order_number'];
        }

        if (empty($query)) {
            return '';
        }

        return '?' . http_build_query($query);
    }
}
