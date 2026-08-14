<?php

namespace Bootpay\ServerPhp;

class BootpayCommerceApi
{
    private static $token = '';
    private static $clientKey = '';
    private static $secretKey = '';
    private static $mode = 'production';

    private static $API_URL = array(
        'development' => 'https://dev-api.bootapi.com/v1',
        'stage' => 'https://stage-api.bootapi.com/v1',
        'production' => 'https://api.bootapi.com/v1'
    );

    private static function entrypoints($url)
    {
        return implode('/', array(self::$API_URL[self::$mode], ltrim($url, '/')));
    }

    private static function createHeaders($headers = null)
    {
        !isset($headers) && $headers = array();

        $auth = '';
        if (strlen(self::$token)) {
            $auth = 'Bearer ' . self::$token;
        } else {
            $auth = 'Basic ' . base64_encode(self::$clientKey . ':' . self::$secretKey);
        }

        $defaultHeaders = array(
            'Content-Type: application/json',
            'Accept: application/json',
            'Authorization: ' . $auth,
            'BOOTPAY-ROLE: user'
        );

        // 호출측에서 동일한 이름의 헤더를 지정하면 기본 헤더를 대체한다 (예: Bootpay-Role: supervisor)
        foreach ($defaultHeaders as $defaultHeader) {
            if (!self::hasHeader($headers, $defaultHeader)) {
                $headers[] = $defaultHeader;
            }
        }

        return $headers;
    }

    private static function hasHeader($headers, $header)
    {
        $name = strtolower(strstr($header, ':', true));
        foreach ($headers as $item) {
            if (strtolower(strstr($item, ':', true)) === $name) {
                return true;
            }
        }
        return false;
    }

    /**
     * Idempotency Key 생성 (UUID v4)
     * Comment by GOSOMI
     * @date: 2026-07-23
     */
    private static function uuid()
    {
        return sprintf(
            '%04x%04x-%04x-4%03x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff),
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }

    private static function supervisorHeaders($idempotencyKey = null)
    {
        return array(
            'Idempotency-Key: ' . (isset($idempotencyKey) && strlen($idempotencyKey) ? $idempotencyKey : self::uuid()),
            'Bootpay-Role: supervisor'
        );
    }

    private static function compact($payload)
    {
        $compacted = array();
        foreach ($payload as $key => $value) {
            if (!is_null($value)) {
                $compacted[$key] = $value;
            }
        }
        return $compacted;
    }

    private static function exception($message)
    {
        throw new \Exception($message);
    }

    private static function request($method, $url, $data = null, $headers = null)
    {
        !isset($headers) && $headers = array();

        $channel = curl_init(self::entrypoints($url));
        curl_setopt($channel, CURLOPT_URL, self::entrypoints($url));
        curl_setopt($channel, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($channel, CURLOPT_HTTPHEADER, self::createHeaders($headers));

        if (in_array($method, array('POST', 'PUT')) || (isset($data) && !in_array($method, array('GET', 'HEAD')))) {
            curl_setopt($channel, CURLOPT_POSTFIELDS, json_encode($data));
        }

        curl_setopt($channel, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($channel);
        $errno = curl_errno($channel);
        $errMsg = curl_error($channel);
        if ($errno) {
            throw new \Exception('error: ' . $errno . ', msg: ' . $errMsg);
        }
        curl_close($channel);

        return json_decode(trim($response));
    }

    public static function setConfiguration($clientKey, $secretKey, $mode = 'production')
    {
        self::$clientKey = $clientKey;
        self::$secretKey = $secretKey;
        self::$mode = $mode;
    }

    public static function setToken($token)
    {
        self::$token = $token;
    }

    // Store
    public static function getStore()
    {
        return self::request('GET', 'store');
    }

    public static function storeInfo()
    {
        return self::getStore();
    }

    public static function getStoreDetail()
    {
        return self::request('GET', 'store/detail');
    }

    public static function storeDetail()
    {
        return self::getStoreDetail();
    }

    // User
    public static function userLogin($loginId, $loginPw)
    {
        return self::request('POST', 'users/login', array(
            'login_id' => $loginId,
            'login_pw' => $loginPw
        ));
    }

    public static function userJoin($user)
    {
        return self::request('POST', 'users/join', $user);
    }

    public static function userJoinCheck($type, $pk)
    {
        return self::request('GET', 'users/join/' . $type . '?pk=' . urlencode($pk));
    }

    // Product
    public static function products($params = array())
    {
        $query = '';
        if (!empty($params)) {
            $query = '?' . http_build_query($params);
        }
        return self::request('GET', 'products' . $query);
    }

    public static function productDetail($productId)
    {
        return self::request('GET', 'products/' . $productId);
    }

    // Mall Setting
    /**
     * 몰 설정 조회 (GET /mall-setting)
     * supervisor scope 전용
     * @date: 2026-05-04
     */
    public static function getMallSetting($idempotencyKey = null)
    {
        return self::request(
            'GET',
            'mall-setting',
            null,
            self::supervisorHeaders($idempotencyKey)
        );
    }

    /**
     * 몰 설정 수정 (PUT /mall-setting)
     * supervisor scope 전용
     * 요청 바디는 flatten 형식이며 전달된 값(null이 아닌 값)만 서버로 전송된다.
     * @date: 2026-05-04
     */
    public static function updateMallSetting($mallSettingParameters, $idempotencyKey = null)
    {
        !isset($mallSettingParameters) && $mallSettingParameters = array();
        if (isset($mallSettingParameters['idempotency_key'])) {
            $idempotencyKey = $mallSettingParameters['idempotency_key'];
            unset($mallSettingParameters['idempotency_key']);
        }
        return self::request(
            'PUT',
            'mall-setting',
            self::compact($mallSettingParameters),
            self::supervisorHeaders($idempotencyKey)
        );
    }

    // Supervisor
    /**
     * 수시결제(온디맨드) charge_key 즉시 결제
     * charge_key는 body로만 전송한다 (URL/query 금지 - 액세스 로그 노출 방지)
     * Comment by GOSOMI
     * @date: 2026-07-23
     * @throws \Exception
     */
    public static function supervisorRequestOrderSubscriptionCharge($chargeParameters)
    {
        if (!isset($chargeParameters['charge_key']) || !strlen($chargeParameters['charge_key'])) {
            return self::exception('charge_key를 입력해주세요.');
        }
        if (!isset($chargeParameters['price'])) {
            return self::exception('결제금액을 입력해주세요.');
        }
        return self::request(
            'POST',
            'order_subscriptions/charge',
            self::compact(array(
                'charge_key' => $chargeParameters['charge_key'],
                'price' => $chargeParameters['price'],
                'tax_free_price' => isset($chargeParameters['tax_free_price']) ? $chargeParameters['tax_free_price'] : null,
                'user' => isset($chargeParameters['user']) ? $chargeParameters['user'] : null,
                'metadata' => isset($chargeParameters['metadata']) ? $chargeParameters['metadata'] : null
            )),
            self::supervisorHeaders(isset($chargeParameters['idempotency_key']) ? $chargeParameters['idempotency_key'] : null)
        );
    }

    /**
     * 수시결제(온디맨드) charge_key 해지
     * 해지 이후 해당 키로의 재결제는 불가능하다
     * Comment by GOSOMI
     * @date: 2026-07-23
     * @throws \Exception
     */
    public static function supervisorRequestOrderSubscriptionChargeRevoke($revokeParameters)
    {
        if (!isset($revokeParameters['charge_key']) || !strlen($revokeParameters['charge_key'])) {
            return self::exception('charge_key를 입력해주세요.');
        }
        return self::request(
            'DELETE',
            'order_subscriptions/charge',
            self::compact(array(
                'charge_key' => $revokeParameters['charge_key'],
                'user' => isset($revokeParameters['user']) ? $revokeParameters['user'] : null
            )),
            self::supervisorHeaders(isset($revokeParameters['idempotency_key']) ? $revokeParameters['idempotency_key'] : null)
        );
    }
}
