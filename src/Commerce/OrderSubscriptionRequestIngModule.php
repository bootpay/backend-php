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
     * POST /v1/order_subscriptions/requests/ing/pause
     * @param array $params 일시정지 파라미터 (idempotency_key 는 Idempotency-Key 헤더로 전송된다)
     * @return object
     */
    public function pause($params)
    {
        $idempotencyKey = isset($params['idempotency_key']) ? $params['idempotency_key'] : null;
        unset($params['idempotency_key']);
        return $this->bootpay->post(
            'order_subscriptions/requests/ing/pause',
            $this->compact($params),
            $this->userHeaders($idempotencyKey)
        );
    }

    /**
     * 정기구독 재개
     * PUT /v1/order_subscriptions/requests/ing/resume
     * ⚠️ requests/ing 계열 중 유일하게 PUT 이다. 오타로 보고 POST 로 바꾸지 말 것.
     * @param array $params 재개 파라미터 (idempotency_key 는 Idempotency-Key 헤더로 전송된다)
     * @return object
     */
    public function resume($params)
    {
        $idempotencyKey = isset($params['idempotency_key']) ? $params['idempotency_key'] : null;
        unset($params['idempotency_key']);
        return $this->bootpay->put(
            'order_subscriptions/requests/ing/resume',
            $this->compact($params),
            $this->userHeaders($idempotencyKey)
        );
    }

    /**
     * 중도인수 요청
     * POST /v1/order_subscriptions/requests/ing/purchase
     * @param array $params 중도인수 파라미터 (idempotency_key 는 Idempotency-Key 헤더로 전송된다)
     * @return object
     */
    public function purchase($params)
    {
        $idempotencyKey = isset($params['idempotency_key']) ? $params['idempotency_key'] : null;
        unset($params['idempotency_key']);
        return $this->bootpay->post(
            'order_subscriptions/requests/ing/purchase',
            $this->compact($params),
            $this->userHeaders($idempotencyKey)
        );
    }

    /**
     * 구독 이전/승계 요청
     * POST /v1/order_subscriptions/requests/ing/transfer
     * @param array $params 이전/승계 파라미터 (idempotency_key 는 Idempotency-Key 헤더로 전송된다)
     * @return object
     */
    public function transfer($params)
    {
        $idempotencyKey = isset($params['idempotency_key']) ? $params['idempotency_key'] : null;
        unset($params['idempotency_key']);
        return $this->bootpay->post(
            'order_subscriptions/requests/ing/transfer',
            $this->compact($params),
            $this->userHeaders($idempotencyKey)
        );
    }

    /**
     * 해지 수수료 계산
     * GET /v1/order_subscriptions/requests/ing/calculate_termination_fee
     * @param string|null $orderSubscriptionId 정기구독 ID (선택)
     * @param string|null $orderNumber 주문번호 (선택)
     * @param string|null $idempotencyKey 미지정시 자동 생성
     * @return object
     * @throws \Exception
     */
    public function calculateTerminationFee($orderSubscriptionId = null, $orderNumber = null, $idempotencyKey = null)
    {
        if ($orderSubscriptionId === null && $orderNumber === null) {
            throw new \Exception('orderSubscriptionId or orderNumber is required');
        }

        $query = array();
        if ($orderSubscriptionId !== null) {
            $query['order_subscription_id'] = $orderSubscriptionId;
        }
        if ($orderNumber !== null) {
            $query['order_number'] = $orderNumber;
        }

        $queryString = http_build_query($query);
        return $this->bootpay->get(
            "order_subscriptions/requests/ing/calculate_termination_fee?{$queryString}",
            $this->userHeaders($idempotencyKey)
        );
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
     * POST /v1/order_subscriptions/requests/ing/termination
     * @param array $params 해지 파라미터 (idempotency_key 는 Idempotency-Key 헤더로 전송된다)
     * @return object
     */
    public function termination($params)
    {
        $idempotencyKey = isset($params['idempotency_key']) ? $params['idempotency_key'] : null;
        unset($params['idempotency_key']);
        return $this->bootpay->post(
            'order_subscriptions/requests/ing/termination',
            $this->compact($params),
            $this->userHeaders($idempotencyKey)
        );
    }

    /**
     * null 값을 제거한다. (NodeJS SDK 의 compact 와 동일 동작)
     */
    private function compact($payload)
    {
        $result = array();
        foreach ((array)$payload as $key => $value) {
            if ($value !== null) {
                $result[$key] = $value;
            }
        }
        // 빈 배열은 JSON 인코딩 시 [] 가 되므로 {} 로 전송되도록 객체로 변환
        return empty($result) ? new \stdClass() : $result;
    }

    /**
     * requests/ing 요청 헤더 — 구매자가 올리는 요청이므로 user scope 다.
     * Idempotency-Key 는 미지정시 매 호출마다 생성된다.
     */
    private function userHeaders($idempotencyKey = null)
    {
        return array(
            'Idempotency-Key: ' . ($idempotencyKey ?: BootpayCommerceApi::generateIdempotencyKey()),
            'BOOTPAY-ROLE: user'
        );
    }
}
