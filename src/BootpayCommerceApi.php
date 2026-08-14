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

    /**
     * Idempotency-Key 헤더 생성
     * Comment by GOSOMI
     * @date: 2026-02-23
     */
    private static function idempotencyHeaders($idempotencyKey = null)
    {
        return array(
            'Idempotency-Key: ' . (isset($idempotencyKey) && strlen($idempotencyKey) ? $idempotencyKey : self::uuid())
        );
    }

    /**
     * 회원 JWT를 함께 전달하는 헤더 생성 (Bootpay-User-JWT)
     * user_jwt가 없으면 헤더를 전송하지 않는다
     * Comment by GOSOMI
     * @date: 2026-02-23
     */
    private static function userJwtHeaders($userJwt = null, $idempotencyKey = null)
    {
        $headers = self::idempotencyHeaders($idempotencyKey);
        if (isset($userJwt) && strlen($userJwt)) {
            $headers[] = 'Bootpay-User-JWT: ' . $userJwt;
        }
        return $headers;
    }

    private static function supervisorHeaders($idempotencyKey = null)
    {
        $headers = self::idempotencyHeaders($idempotencyKey);
        $headers[] = 'Bootpay-Role: supervisor';
        return $headers;
    }

    /**
     * null이 아닌 값만 query string으로 변환한다
     * Comment by GOSOMI
     * @date: 2026-02-23
     */
    private static function query($params)
    {
        $compacted = self::compact($params);
        if (empty($compacted)) {
            return '';
        }
        return '?' . http_build_query($compacted);
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
    /**
     * 가맹점 기본 정보를 조회한다 (GET /store)
     * Comment by GOSOMI
     * @date: 2026-02-23
     */
    public static function getStore($idempotencyKey = null)
    {
        return self::request(
            'GET',
            'store',
            null,
            self::idempotencyHeaders($idempotencyKey)
        );
    }

    public static function storeInfo($idempotencyKey = null)
    {
        return self::getStore($idempotencyKey);
    }

    /**
     * 가맹점 상세 정보를 조회한다 (GET /store/detail)
     * Comment by GOSOMI
     * @date: 2026-02-23
     */
    public static function getStoreDetail($idempotencyKey = null)
    {
        return self::request(
            'GET',
            'store/detail',
            null,
            self::idempotencyHeaders($idempotencyKey)
        );
    }

    public static function storeDetail($idempotencyKey = null)
    {
        return self::getStoreDetail($idempotencyKey);
    }

    // User
    /**
     * 회원 로그인 (POST /user/login)
     * Comment by GOSOMI
     * @date: 2026-02-23
     */
    public static function userLogin($loginId, $password, $corporateType = 0, $idempotencyKey = null)
    {
        return self::request(
            'POST',
            'user/login',
            self::compact(array(
                'login_id' => $loginId,
                'password' => $password,
                'corporate_type' => $corporateType
            )),
            self::idempotencyHeaders($idempotencyKey)
        );
    }

    /**
     * 회원 세션 조회 (GET /user/session)
     * Comment by GOSOMI
     * @date: 2026-02-23
     */
    public static function userSession($userJwt = null, $idempotencyKey = null)
    {
        return self::request(
            'GET',
            'user/session',
            null,
            self::userJwtHeaders($userJwt, $idempotencyKey)
        );
    }

    /**
     * 회원 로그아웃 (DELETE /user/session)
     * Comment by GOSOMI
     * @date: 2026-02-23
     */
    public static function userLogout($userJwt, $idempotencyKey = null)
    {
        return self::request(
            'DELETE',
            'user/session',
            null,
            self::userJwtHeaders($userJwt, $idempotencyKey)
        );
    }

    /**
     * 회원가입 (POST /user/join)
     * 전달된 값(null이 아닌 값)만 서버로 전송되며, 별도로 전달한 확장 필드도 함께 전송된다.
     * Comment by GOSOMI
     * @date: 2026-02-23
     */
    public static function userJoin($userParameters, $idempotencyKey = null)
    {
        !isset($userParameters) && $userParameters = array();
        if (isset($userParameters['idempotency_key'])) {
            $idempotencyKey = $userParameters['idempotency_key'];
            unset($userParameters['idempotency_key']);
        }
        $payload = self::compact(array(
            'login_id' => isset($userParameters['login_id']) ? $userParameters['login_id'] : null,
            'password' => isset($userParameters['password']) ? $userParameters['password'] : null,
            'name' => isset($userParameters['name']) ? $userParameters['name'] : null,
            'email' => isset($userParameters['email']) ? $userParameters['email'] : null,
            'phone' => isset($userParameters['phone']) ? $userParameters['phone'] : null,
            'nickname' => isset($userParameters['nickname']) ? $userParameters['nickname'] : null,
            'gender' => isset($userParameters['gender']) ? $userParameters['gender'] : null,
            'birth' => isset($userParameters['birth']) ? $userParameters['birth'] : null,
            'corporate_type' => isset($userParameters['corporate_type']) ? $userParameters['corporate_type'] : 0,
            'group' => isset($userParameters['group']) ? $userParameters['group'] : null
        ));
        foreach (self::compact($userParameters) as $key => $value) {
            if (!array_key_exists($key, $payload)) {
                $payload[$key] = $value;
            }
        }
        return self::request(
            'POST',
            'user/join',
            $payload,
            self::idempotencyHeaders($idempotencyKey)
        );
    }

    /**
     * 회원가입 중복 확인 (GET /user/join/{type})
     * type: email-exist, id-exist, phone-exist, group-business-number-exist
     * Comment by GOSOMI
     * @date: 2026-02-23
     */
    public static function userJoinCheck($type, $pk, $idempotencyKey = null)
    {
        return self::request(
            'GET',
            'user/join/' . $type . self::query(array('pk' => $pk)),
            null,
            self::idempotencyHeaders($idempotencyKey)
        );
    }

    // Product
    /**
     * 상품 목록을 조회한다 (GET /products)
     * page, limit, category_id, sort, keyword를 전달할 수 있으며
     * user_jwt, idempotency_key는 헤더로 전송된다.
     * Comment by GOSOMI
     * @date: 2026-02-23
     */
    public static function products($params = array())
    {
        !isset($params) && $params = array();
        $userJwt = isset($params['user_jwt']) ? $params['user_jwt'] : null;
        $idempotencyKey = isset($params['idempotency_key']) ? $params['idempotency_key'] : null;
        unset($params['user_jwt'], $params['idempotency_key']);

        $params = array_merge(
            array('page' => 1, 'limit' => 20),
            $params
        );
        return self::request(
            'GET',
            'products' . self::query($params),
            null,
            self::userJwtHeaders($userJwt, $idempotencyKey)
        );
    }

    /**
     * 상품 상세를 조회한다 (GET /products/{product_id})
     * Comment by GOSOMI
     * @date: 2026-02-23
     */
    public static function productDetail($productId, $userJwt = null, $idempotencyKey = null)
    {
        return self::request(
            'GET',
            'products/' . $productId,
            null,
            self::userJwtHeaders($userJwt, $idempotencyKey)
        );
    }

    /**
     * 상품 정보를 가져온다 (GET /products/{product_id})
     * Comment by GOSOMI
     * @date: 2025-10-10
     */
    public static function lookupProduct($productId, $idempotencyKey = null)
    {
        return self::request(
            'GET',
            'products/' . $productId,
            null,
            self::idempotencyHeaders($idempotencyKey)
        );
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
