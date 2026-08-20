<?php

namespace Bootpay\ServerPhp\Commerce;

use Bootpay\ServerPhp\BootpayCommerceApi;

class MallSettingModule
{
    private $bootpay;

    public function __construct(BootpayCommerceApi $bootpay)
    {
        $this->bootpay = $bootpay;
    }

    /**
     * 몰 설정 조회
     * GET /v1/mall-setting
     * supervisor scope 토큰 전용
     * @param string|null $idempotencyKey 미지정시 자동 생성
     * @return object
     */
    public function getMallSetting($idempotencyKey = null)
    {
        return $this->bootpay->get('mall-setting', $this->supervisorHeaders($idempotencyKey));
    }

    /**
     * 몰 설정 조회 (getMallSetting 별칭)
     * @param string|null $idempotencyKey 미지정시 자동 생성
     * @return object
     */
    public function detail($idempotencyKey = null)
    {
        return $this->getMallSetting($idempotencyKey);
    }

    /**
     * 몰 설정 수정
     * PUT /v1/mall-setting
     * supervisor scope 토큰 전용
     * 요청 바디는 flatten 형식이며 전달된 값(non-null)만 서버로 전송된다.
     * @param array $params 수정할 설정값
     * @param string|null $idempotencyKey 미지정시 자동 생성
     * @return object
     */
    public function updateMallSetting($params, $idempotencyKey = null)
    {
        return $this->bootpay->put('mall-setting', $this->compact($params), $this->supervisorHeaders($idempotencyKey));
    }

    /**
     * 몰 설정 수정 (updateMallSetting 별칭)
     * @param array $params 수정할 설정값
     * @param string|null $idempotencyKey 미지정시 자동 생성
     * @return object
     */
    public function update($params, $idempotencyKey = null)
    {
        return $this->updateMallSetting($params, $idempotencyKey);
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
     * supervisor 전용 요청 헤더
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
