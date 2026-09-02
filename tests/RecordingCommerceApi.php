<?php

namespace Bootpay\ServerPhp\Test;

use Bootpay\ServerPhp\BootpayCommerceApi;

/**
 * wire-format 검증용 mock — 실제 HTTP 요청 대신 method/url/body/헤더를 기록한다.
 * 모듈이 공통 계층(request/requestMultipart)에 넘기는 값 그대로를 캡처하므로
 * 네트워크 없이 요청 규약(endpoint, Idempotency-Key, BOOTPAY-ROLE, body 구성)을 검증할 수 있다.
 */
class RecordingCommerceApi extends BootpayCommerceApi
{
    /** @var array<int, array> */
    public $calls = array();

    /** @var mixed 다음 호출이 반환할 응답 */
    public $nextResponse = null;

    public function request($method, $url, $data = null, $headers = null, $useBasicAuth = false)
    {
        $this->calls[] = array(
            'type' => 'json',
            'method' => $method,
            'url' => $url,
            'data' => $data,
            'headers' => $headers === null ? array() : $headers
        );
        return $this->nextResponse;
    }

    public function requestMultipart($method, $url, $data = null, $files = null, $headers = null)
    {
        $this->calls[] = array(
            'type' => 'multipart',
            'method' => $method,
            'url' => $url,
            'data' => $data,
            'files' => $files,
            'headers' => $headers === null ? array() : $headers
        );
        return $this->nextResponse;
    }

    public function requestRaw($method, $url, $headers = null)
    {
        $this->calls[] = array(
            'type' => 'raw',
            'method' => $method,
            'url' => $url,
            'data' => null,
            'headers' => $headers === null ? array() : $headers
        );
        return $this->nextResponse;
    }

    public function lastCall()
    {
        return end($this->calls);
    }

    /**
     * 기록된 호출에서 헤더 값을 찾는다 (이름 대소문자 무시). 없으면 null.
     */
    public static function headerValue($call, $name)
    {
        foreach ($call['headers'] as $header) {
            $pos = strpos($header, ':');
            if ($pos === false) {
                continue;
            }
            if (strcasecmp(trim(substr($header, 0, $pos)), $name) === 0) {
                return trim(substr($header, $pos + 1));
            }
        }
        return null;
    }
}
