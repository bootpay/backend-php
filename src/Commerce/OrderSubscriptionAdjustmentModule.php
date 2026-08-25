<?php

namespace Bootpay\ServerPhp\Commerce;

use Bootpay\ServerPhp\BootpayCommerceApi;

/**
 * 구독 가감산 조정항목 모듈
 *
 * ⚠️ /adjustments 한 경로에 POST · PUT · DELETE 세 동사가 걸려 있다.
 *    경로만 보고 메서드를 유추하지 말 것.
 */
class OrderSubscriptionAdjustmentModule
{
    private $bootpay;

    public function __construct(BootpayCommerceApi $bootpay)
    {
        $this->bootpay = $bootpay;
    }

    /**
     * 가감산 조정항목 추가 (supervisor 전용)
     * POST /v1/order_subscriptions/{order_subscription_id}/adjustments
     * type 미전달시 서버가 price > 0 이면 SETUP_PRICE, 아니면 PERIOD_DISCOUNT 로 자동 판정한다.
     *
     * 회차 지정 방법 3가지 (아래로 갈수록 넓다):
     *   - duration: 5                                → 5회차 한 건만
     *   - duration_from: 3, duration_to: 7           → 3~7회차 각각 한 건씩 (총 5건)
     *   - duration_from: 3, is_unlimited: true       → 3회차부터 계약 끝까지 (레코드는 1건, duration_to 는 무시)
     * 상한은 계약 총회차이며, 총회차가 무제한인 계약은 60회차까지다.
     * 이미 결제가 끝난 회차는 거절된다. 범위 중 한 회차라도 최종 금액이 음수면 전부 거절된다 (부분 반영 없음).
     *
     * @param string $orderSubscriptionId 정기구독 ID
     * @param array $adjustment 조정 정보 (name/price/duration/tax_free_price/type/duration_from/duration_to/is_unlimited)
     *                          price/duration/tax_free_price 미지정시 각각 0 / 1 / 0
     * @param string|null $idempotencyKey 미지정시 자동 생성
     * @return object
     */
    public function create($orderSubscriptionId, $adjustment, $idempotencyKey = null)
    {
        $payload = array_merge(array(
            'price' => 0,
            'duration' => 1,
            'tax_free_price' => 0
        ), (array)$adjustment);
        return $this->bootpay->post(
            "order_subscriptions/{$orderSubscriptionId}/adjustments",
            $this->compact($payload),
            $this->supervisorHeaders($idempotencyKey)
        );
    }

    /**
     * 특정 회차의 조정항목을 통째로 교체 (supervisor 전용)
     * PUT /v1/order_subscriptions/{order_subscription_id}/adjustments
     * 서버는 duration(회차) 단위로 adjustments 배열을 갈아끼운다.
     * @param array $params 수정 파라미터 (order_subscription_id 필수, duration 미지정시 1,
     *                      adjustments 배열 지원 / idempotency_key 는 Idempotency-Key 헤더로 전송된다)
     * @return object
     * @throws \Exception
     */
    public function update($params)
    {
        if (!isset($params['order_subscription_id']) || empty($params['order_subscription_id'])) {
            throw new \Exception('order_subscription_id is required');
        }
        $orderSubscriptionId = $params['order_subscription_id'];
        $idempotencyKey = isset($params['idempotency_key']) ? $params['idempotency_key'] : null;
        unset($params['order_subscription_id'], $params['idempotency_key']);
        if (!isset($params['duration'])) {
            $params['duration'] = 1;
        }
        return $this->bootpay->put(
            "order_subscriptions/{$orderSubscriptionId}/adjustments",
            $this->compact($params),
            $this->supervisorHeaders($idempotencyKey)
        );
    }

    /**
     * 조정항목 삭제 (supervisor 전용)
     * DELETE /v1/order_subscriptions/{order_subscription_id}/adjustments
     * ⚠️ 대상 ID 는 query 가 아니라 body 로 보낸다.
     * @param string $orderSubscriptionId 정기구독 ID
     * @param string $orderSubscriptionAdjustmentId 조정 ID
     * @param string|null $idempotencyKey 미지정시 자동 생성
     * @return object
     */
    public function delete($orderSubscriptionId, $orderSubscriptionAdjustmentId, $idempotencyKey = null)
    {
        return $this->bootpay->delete(
            "order_subscriptions/{$orderSubscriptionId}/adjustments",
            $this->supervisorHeaders($idempotencyKey),
            array('order_subscription_adjustment_id' => $orderSubscriptionAdjustmentId)
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
     * 조정항목 API 요청 헤더 — 서버가 supervisor scope 를 요구한다.
     * Idempotency-Key 는 미지정시 매 호출마다 생성된다.
     */
    private function supervisorHeaders($idempotencyKey = null)
    {
        return array(
            'Idempotency-Key: ' . ($idempotencyKey ?: BootpayCommerceApi::generateIdempotencyKey()),
            'BOOTPAY-ROLE: supervisor'
        );
    }
}
