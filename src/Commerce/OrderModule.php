<?php

namespace Bootpay\ServerPhp\Commerce;

use Bootpay\ServerPhp\BootpayCommerceApi;

class OrderModule
{
    private $bootpay;

    public function __construct(BootpayCommerceApi $bootpay)
    {
        $this->bootpay = $bootpay;
    }

    /**
     * 주문 목록 조회
     * @param array|null $params 조회 파라미터
     * @return object
     */
    public function getList($params = null)
    {
        $queryString = $this->buildQueryString($params);
        return $this->bootpay->get('orders' . $queryString);
    }

    /**
     * 주문 상세 조회
     * @param string $orderId 주문 ID
     * @return object
     */
    public function detail($orderId)
    {
        return $this->bootpay->get("orders/{$orderId}");
    }

    /**
     * 월별 주문 조회
     * @param string $userGroupId 사용자 그룹 ID
     * @param string $searchDate 검색 날짜 (YYYY-MM 형식)
     * @return object
     */
    public function month($userGroupId, $searchDate)
    {
        $query = http_build_query(array(
            'user_group_id' => $userGroupId,
            'search_date' => $searchDate
        ));
        return $this->bootpay->get("orders/month?{$query}");
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
        if (isset($params['user_id'])) {
            $query['user_id'] = $params['user_id'];
        }
        if (isset($params['user_group_id'])) {
            $query['user_group_id'] = $params['user_group_id'];
        }
        if (isset($params['cs_type'])) {
            $query['cs_type'] = $params['cs_type'];
        }
        if (isset($params['css_at'])) {
            $query['css_at'] = $params['css_at'];
        }
        if (isset($params['cse_at'])) {
            $query['cse_at'] = $params['cse_at'];
        }
        if (isset($params['subscription_billing_type'])) {
            $query['subscription_billing_type'] = $params['subscription_billing_type'];
        }
        if (isset($params['status']) && is_array($params['status'])) {
            $query['status'] = implode(',', $params['status']);
        }
        if (isset($params['payment_status']) && is_array($params['payment_status'])) {
            $query['payment_status'] = implode(',', $params['payment_status']);
        }
        if (isset($params['order_subscription_ids']) && is_array($params['order_subscription_ids'])) {
            $query['order_subscription_ids'] = implode(',', $params['order_subscription_ids']);
        }

        if (empty($query)) {
            return '';
        }

        return '?' . http_build_query($query);
    }
}
