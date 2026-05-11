<?php

namespace Bootpay\ServerPhp\Commerce;

use Bootpay\ServerPhp\BootpayCommerceApi;

/**
 * V1 OrderSubscription Request 조회/승인 모듈
 *
 * 본인 모드 (user role): project_id 없이 호출 -> 본인 요청 목록/단건
 * 슈퍼바이저 모드 (supervisor role): project_id 포함 -> 프로젝트 전체 + update (승인/거절)
 *
 * 구매자측 요청 생성 (pause/resume/termination 등) 은
 * `orderSubscription.requestIng.*` 모듈을 사용한다.
 */
class OrderSubscriptionRequestModule
{
    private $bootpay;

    public function __construct(BootpayCommerceApi $bootpay)
    {
        $this->bootpay = $bootpay;
    }

    /**
     * 요청 목록 조회 (user / supervisor 공용)
     * @param array|null $params 조회 파라미터
     * @return object
     */
    public function getList($params = null)
    {
        $queryString = $this->buildQueryString($params);
        return $this->bootpay->get('order-subscription-requests' . $queryString);
    }

    /**
     * 요청 단건 조회 (user / supervisor 공용)
     * @param string $orderSubscriptionRequestHistoryId 요청 이력 ID
     * @param string|null $projectId supervisor 모드에서 사용
     * @return object
     */
    public function detail($orderSubscriptionRequestHistoryId, $projectId = null)
    {
        $query = array();
        if ($projectId !== null && $projectId !== '') {
            $query['project_id'] = $projectId;
        }
        $queryString = empty($query) ? '' : '?' . http_build_query($query);
        return $this->bootpay->get("order-subscription-requests/{$orderSubscriptionRequestHistoryId}" . $queryString);
    }

    /**
     * 요청 승인/거절 (supervisor 전용)
     * @param array $params 승인/거절 파라미터 (order_subscription_request_history_id 필수)
     * @return object
     * @throws \Exception
     */
    public function update($params)
    {
        if (!isset($params['order_subscription_request_history_id']) || empty($params['order_subscription_request_history_id'])) {
            throw new \Exception('order_subscription_request_history_id is required');
        }
        $historyId = $params['order_subscription_request_history_id'];
        $body = $params;
        unset($body['order_subscription_request_history_id']);
        return $this->bootpay->put("order-subscription-requests/{$historyId}", $body);
    }

    private function buildQueryString($params)
    {
        if ($params === null || empty($params)) {
            return '';
        }

        $query = array();
        if (isset($params['project_id'])) {
            $query['project_id'] = $params['project_id'];
        }
        if (isset($params['page'])) {
            $query['page'] = $params['page'];
        }
        if (isset($params['limit'])) {
            $query['limit'] = $params['limit'];
        }
        if (isset($params['request_type'])) {
            $query['request_type'] = $params['request_type'];
        }
        if (isset($params['status'])) {
            $query['status'] = $params['status'];
        }
        if (isset($params['s_at'])) {
            $query['s_at'] = $params['s_at'];
        }
        if (isset($params['e_at'])) {
            $query['e_at'] = $params['e_at'];
        }
        if (isset($params['keyword'])) {
            $query['keyword'] = $params['keyword'];
        }

        if (empty($query)) {
            return '';
        }

        return '?' . http_build_query($query);
    }
}
