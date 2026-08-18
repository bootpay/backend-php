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

    /**
     * 기본 헤더를 생성한다
     * multipart 전송시에는 boundary를 curl이 직접 생성해야 하므로 Content-Type을 지정하지 않는다
     * Comment by GOSOMI
     * @date: 2026-02-23
     */
    private static function createHeaders($headers = null, $useJsonContentType = true)
    {
        !isset($headers) && $headers = array();

        $auth = '';
        if (strlen(self::$token)) {
            $auth = 'Bearer ' . self::$token;
        } else {
            $auth = 'Basic ' . base64_encode(self::$clientKey . ':' . self::$secretKey);
        }

        $defaultHeaders = array(
            'Accept: application/json',
            'Authorization: ' . $auth,
            'BOOTPAY-ROLE: user'
        );
        if ($useJsonContentType) {
            array_unshift($defaultHeaders, 'Content-Type: application/json');
        }

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

    /**
     * Bootpay-Role을 지정한 헤더 생성
     * role: user, manager, supervisor
     * Comment by GOSOMI
     * @date: 2026-08-18
     */
    private static function roleHeaders($role, $idempotencyKey = null)
    {
        $headers = self::idempotencyHeaders($idempotencyKey);
        $headers[] = 'Bootpay-Role: ' . $role;
        return $headers;
    }

    private static function supervisorHeaders($idempotencyKey = null)
    {
        return self::roleHeaders('supervisor', $idempotencyKey);
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
            // 본문이 없는 POST/PUT은 null이나 빈 배열([])이 아닌 빈 객체({})로 전송한다
            $payload = isset($data) ? $data : new \stdClass();
            if (is_array($payload) && empty($payload)) {
                $payload = new \stdClass();
            }
            curl_setopt($channel, CURLOPT_POSTFIELDS, json_encode($payload));
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

    /**
     * multipart/form-data 전송 (파일 업로드용)
     * Comment by GOSOMI
     * @date: 2026-08-18
     * ⚠️ Content-Type을 직접 지정하지 않는다. 지정하면 curl이 붙이는 boundary가 사라져 본문이 깨진다.
     *    기존 request()는 json 고정이라 손대지 않고 별도 메서드로 둔다.
     */
    private static function postMultipart($url, $form = array(), $headers = null)
    {
        !isset($headers) && $headers = array();

        $channel = curl_init(self::entrypoints($url));
        curl_setopt($channel, CURLOPT_URL, self::entrypoints($url));
        curl_setopt($channel, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($channel, CURLOPT_HTTPHEADER, self::createHeaders($headers, false));
        curl_setopt($channel, CURLOPT_POSTFIELDS, $form);
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

    /**
     * multipart 파일값 정규화 - 파일 경로(String) 또는 CURLFile을 받는다
     * Comment by GOSOMI
     * @date: 2026-08-18
     */
    private static function multipartFile($value)
    {
        if ($value instanceof \CURLFile) {
            return $value;
        }
        return new \CURLFile($value);
    }

    /**
     * multipart 일반값 정규화 - 배열/객체는 JSON, 나머지는 문자열로 전송한다
     * Comment by GOSOMI
     * @date: 2026-08-18
     */
    private static function multipartValue($value)
    {
        if (is_array($value) || is_object($value)) {
            return json_encode($value);
        }
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }
        return (string)$value;
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
     * 회원 로그인 (POST /users/login)
     * Comment by GOSOMI
     * @date: 2026-02-23
     * @date: 2026-08-18 uri 'user/login' -> 'users/login' 로 정정.
     *        v1에는 단수 user/* 라우트가 없다. 로그인은 POST /v1/users/login 이다.
     *        ⚠️ POST /v1/users/session 은 resource :session 이 만들어낸 라우트일 뿐 create 액션이 없다.
     *        ⚠️ 서버는 login_id·password만 읽는다. corporate_type은 전달돼도 무시된다.
     */
    public static function userLogin($loginId, $password, $corporateType = 0, $idempotencyKey = null)
    {
        return self::request(
            'POST',
            'users/login',
            self::compact(array(
                'login_id' => $loginId,
                'password' => $password,
                'corporate_type' => $corporateType
            )),
            self::idempotencyHeaders($idempotencyKey)
        );
    }

    /**
     * 회원 세션 조회 (GET /users/session)
     * Comment by GOSOMI
     * @date: 2026-02-23
     * @date: 2026-08-18 uri 'user/session' -> 'users/session' 로 정정 (GET /v1/users/session)
     */
    public static function userSession($userJwt = null, $idempotencyKey = null)
    {
        return self::request(
            'GET',
            'users/session',
            null,
            self::userJwtHeaders($userJwt, $idempotencyKey)
        );
    }

    /**
     * 회원 로그아웃 (DELETE /users/session)
     * Comment by GOSOMI
     * @date: 2026-02-23
     * @date: 2026-08-18 uri 'user/session' -> 'users/session' 로 정정 (DELETE /v1/users/session)
     */
    public static function userLogout($userJwt, $idempotencyKey = null)
    {
        return self::request(
            'DELETE',
            'users/session',
            null,
            self::userJwtHeaders($userJwt, $idempotencyKey)
        );
    }

    /**
     * 회원가입 (POST /users/join) - 일반 회원가입용
     * 전달된 값(null이 아닌 값)만 서버로 전송되며, 별도로 전달한 확장 필드도 함께 전송된다.
     * Comment by GOSOMI
     * @date: 2026-02-23
     * @date: 2026-08-18 uri 'user/join' -> 'users/join' 로 정정 (POST /v1/users/join)
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
            'users/join',
            $payload,
            self::idempotencyHeaders($idempotencyKey)
        );
    }

    /**
     * 회원가입 중복 확인 (GET /users/join/{type}) - key를 인자로 받는 일반형
     * type: email-exist, id-exist, phone-exist, uid-exist, group-business-number-exist
     * Comment by GOSOMI
     * @date: 2026-02-23
     * @date: 2026-08-18 uri 'user/join/{type}' -> 'users/join/{type}' 로 정정 (GET /v1/users/join/:id)
     *        ⚠️ uidExist 등 전용형과 기능이 겹치지만 둘 다 유지한다.
     *        일반형은 서버에 새 key가 생겨도 SDK 수정 없이 쓸 수 있다.
     */
    public static function userJoinCheck($type, $pk, $idempotencyKey = null)
    {
        return self::request(
            'GET',
            'users/join/' . $type . self::query(array('pk' => $pk)),
            null,
            self::idempotencyHeaders($idempotencyKey)
        );
    }

    /**
     * 외부 uid(ex_uid) 중복검사 (GET /users/join/uid-exist)
     * Comment by GOSOMI
     * @date: 2026-08-18
     * email/id/phone/group-business-number 전용형과 같은 패턴이다.
     */
    public static function uidExist($uid, $idempotencyKey = null)
    {
        return self::request(
            'GET',
            'users/join/uid-exist' . self::query(array('pk' => $uid)),
            null,
            self::roleHeaders('user', $idempotencyKey)
        );
    }

    /**
     * 회원 정보를 수정한다 (PUT /users/{user_id})
     * Comment by GOSOMI
     * @date: 2026-08-18
     * 바뀐 값만 전달하면 된다. 사업자 정보는
     * group => array('company_name' => ..., 'business_number' => ..., 'registration_number' => ...) 로 중첩 전달한다.
     */
    public static function userUpdate($userId, $userParameters = array(), $idempotencyKey = null)
    {
        !isset($userParameters) && $userParameters = array();
        if (isset($userParameters['idempotency_key'])) {
            $idempotencyKey = $userParameters['idempotency_key'];
            unset($userParameters['idempotency_key']);
        }
        return self::request(
            'PUT',
            'users/' . $userId,
            self::compact($userParameters),
            self::roleHeaders('user', $idempotencyKey)
        );
    }

    // Product
    /**
     * 상품 목록을 조회한다 (GET /products)
     * page, limit, category_id, sort, keyword를 전달할 수 있으며
     * user_jwt, idempotency_key는 헤더로 전송된다.
     * Comment by GOSOMI
     * @date: 2026-02-23
     * @date: 2026-08-18 ⚠️ keyword는 서버(v1/products_controller#index)가 읽지 않는다.
     *        컨트롤러는 page/limit/category_id/ex_uid/sort만 사용 - keyword를 보내도 조용히 무시된다.
     *        하위호환 때문에 인자는 남겨두되, 검색이 필요하면 서버 지원 추가가 선행되어야 한다.
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
     * @date: 2026-08-18 productDetail과 uri·동작이 같다(차이는 user_jwt 인자 유무).
     *        중복이지만 기존 사용자가 있을 수 있어 제거하지 않는다 - 신규 코드는 productDetail을 쓸 것.
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

    /**
     * 상품을 등록한다 (POST /products)
     * Comment by GOSOMI
     * @date: 2026-08-18
     * images가 있으면 multipart, 없으면 JSON으로 전송한다.
     * 여기 명시하지 않은 값도 전달한 그대로 서버로 함께 전송된다.
     * @throws \Exception
     */
    public static function productCreate($productParameters, $idempotencyKey = null)
    {
        !isset($productParameters) && $productParameters = array();
        if (isset($productParameters['idempotency_key'])) {
            $idempotencyKey = $productParameters['idempotency_key'];
            unset($productParameters['idempotency_key']);
        }
        if (!isset($productParameters['name']) || !strlen($productParameters['name'])) {
            return self::exception('상품명을 입력해주세요.');
        }
        $images = isset($productParameters['images']) ? $productParameters['images'] : null;
        unset($productParameters['images']);

        $payload = self::compact($productParameters);
        $headers = self::roleHeaders('manager', $idempotencyKey);

        if (isset($images) && count((array)$images)) {
            $form = array();
            foreach ($payload as $key => $value) {
                $form[$key] = self::multipartValue($value);
            }
            foreach (array_values((array)$images) as $index => $image) {
                $form['images[' . $index . ']'] = self::multipartFile($image);
            }
            return self::postMultipart('products', $form, $headers);
        }
        return self::request(
            'POST',
            'products',
            $payload,
            $headers
        );
    }

    /**
     * 상품을 수정한다 (PUT /products/{product_id})
     * Comment by GOSOMI
     * @date: 2026-08-18
     * 바뀐 값만 전달하면 된다. category_id는 키 존재 여부로 '해제 의사'를 판별하므로 주의한다.
     */
    public static function productUpdate($productId, $productParameters = array(), $idempotencyKey = null)
    {
        !isset($productParameters) && $productParameters = array();
        if (isset($productParameters['idempotency_key'])) {
            $idempotencyKey = $productParameters['idempotency_key'];
            unset($productParameters['idempotency_key']);
        }
        return self::request(
            'PUT',
            'products/' . $productId,
            self::compact($productParameters),
            self::roleHeaders('manager', $idempotencyKey)
        );
    }

    /**
     * 상품을 삭제한다 (DELETE /products/{product_id})
     * Comment by GOSOMI
     * @date: 2026-08-18
     */
    public static function productDelete($productId, $idempotencyKey = null)
    {
        return self::request(
            'DELETE',
            'products/' . $productId,
            null,
            self::roleHeaders('manager', $idempotencyKey)
        );
    }

    /**
     * 상품 판매/노출 상태를 변경한다 (PUT /products/{product_id}/status)
     * Comment by GOSOMI
     * @date: 2026-08-18
     * status_sale, status_display, status_frozen, status_review, use_display_period,
     * display_start_at, display_end_at, use_sale_period, sale_start_at, sale_end_at를 전달할 수 있다.
     * 재고(stock)는 여기가 아니라 productUpdate로 변경한다.
     */
    public static function productStatus($productId, $statusParameters = array(), $idempotencyKey = null)
    {
        !isset($statusParameters) && $statusParameters = array();
        if (isset($statusParameters['idempotency_key'])) {
            $idempotencyKey = $statusParameters['idempotency_key'];
            unset($statusParameters['idempotency_key']);
        }
        return self::request(
            'PUT',
            'products/' . $productId . '/status',
            self::compact($statusParameters),
            self::roleHeaders('manager', $idempotencyKey)
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

    // Invoice
    /**
     * 청구서 목록을 조회한다 (GET /invoices)
     * Comment by GOSOMI
     * @date: 2026-08-18
     * page, limit, keyword, cs_type, user_id, product_type, css_at, cse_at를 전달할 수 있으며
     * idempotency_key는 헤더로 전송된다.
     * 응답은 array('list' => ..., 'count' => N) 구조다. 서버 기본 limit은 24이다.
     */
    public static function invoiceList($params = array())
    {
        !isset($params) && $params = array();
        $idempotencyKey = isset($params['idempotency_key']) ? $params['idempotency_key'] : null;
        unset($params['idempotency_key']);

        $params = array_merge(
            array('page' => 1, 'limit' => 24),
            $params
        );
        return self::request(
            'GET',
            'invoices' . self::query($params),
            null,
            self::roleHeaders('user', $idempotencyKey)
        );
    }

    /**
     * 청구서 상세를 조회한다 (GET /invoices/{invoice_id})
     * Comment by GOSOMI
     * @date: 2026-08-18
     */
    public static function invoiceDetail($invoiceId, $idempotencyKey = null)
    {
        return self::request(
            'GET',
            'invoices/' . $invoiceId,
            null,
            self::roleHeaders('user', $idempotencyKey)
        );
    }

    /**
     * 청구서를 재안내한다 (POST /invoices/{invoice_id}/notify)
     * Comment by GOSOMI
     * @date: 2026-08-18
     * send_types 미전달시 서버가 빈 배열로 처리한다.
     * ⚠️ 실제 고객에게 알림이 발송되므로 테스트 호출시 주의한다.
     */
    public static function invoiceNotify($invoiceId, $sendTypes = null, $idempotencyKey = null)
    {
        return self::request(
            'POST',
            'invoices/' . $invoiceId . '/notify',
            self::compact(array('send_types' => $sendTypes)),
            self::roleHeaders('user', $idempotencyKey)
        );
    }

    // User Group
    /**
     * 그룹 구매한도를 설정한다 (PUT /user-groups/{user_group_id}/limit)
     * Comment by GOSOMI
     * @date: 2026-08-18
     * use_limit, limit_month_purchase, limit_week_purchase, limit_message를 전달할 수 있다.
     * ⚠️ 회원그룹 수정 API로는 한도가 반영되지 않는다. 서버가 한도 관련 파라미터를 명시적으로 제거하기 때문에
     *    한도는 이 전용 라우트로만 변경된다. 서버 scope는 manager:limit이다.
     */
    public static function userGroupLimit($userGroupId, $limitParameters = array(), $idempotencyKey = null)
    {
        !isset($limitParameters) && $limitParameters = array();
        if (isset($limitParameters['idempotency_key'])) {
            $idempotencyKey = $limitParameters['idempotency_key'];
            unset($limitParameters['idempotency_key']);
        }
        return self::request(
            'PUT',
            'user-groups/' . $userGroupId . '/limit',
            self::compact($limitParameters),
            self::roleHeaders('manager', $idempotencyKey)
        );
    }

    /**
     * 그룹 구독 합산청구(정산주기) 설정을 변경한다 (PUT /user-groups/{user_group_id}/aggregate-transaction)
     * Comment by GOSOMI
     * @date: 2026-08-18
     * use_subscription_aggregate_transaction, subscription_month_day, subscription_week_day를 전달할 수 있다.
     */
    public static function userGroupAggregateTransaction($userGroupId, $aggregateParameters = array(), $idempotencyKey = null)
    {
        !isset($aggregateParameters) && $aggregateParameters = array();
        if (isset($aggregateParameters['idempotency_key'])) {
            $idempotencyKey = $aggregateParameters['idempotency_key'];
            unset($aggregateParameters['idempotency_key']);
        }
        return self::request(
            'PUT',
            'user-groups/' . $userGroupId . '/aggregate-transaction',
            self::compact($aggregateParameters),
            self::roleHeaders('manager', $idempotencyKey)
        );
    }

    // Order Cancel
    /**
     * 주문 취소 요청 내역을 조회한다 (GET /order/cancel)
     * Comment by GOSOMI
     * @date: 2026-08-18
     * order_number 또는 order_id로 필터링하며, 둘 다 없으면 전체를 조회한다.
     * 승인/반려/철회에 넘길 order_cancellation_request_id를 여기서 얻는다.
     */
    public static function orderCancelList($orderNumber = null, $orderId = null, $idempotencyKey = null)
    {
        return self::request(
            'GET',
            'order/cancel' . self::query(array(
                'order_number' => $orderNumber,
                'order_id' => $orderId
            )),
            null,
            self::roleHeaders('user', $idempotencyKey)
        );
    }

    /**
     * (구매자) 주문 취소 요청을 철회한다 (PUT /order/cancel/{order_cancellation_request_id}/withdraw)
     * Comment by GOSOMI
     * @date: 2026-08-18
     * ⚠️ DELETE order/cancel/{id} 와는 다른 라우트다. 매뉴얼이 문서화한 쪽은 withdraw이므로 이쪽을 쓴다.
     */
    public static function orderCancelWithdraw($orderCancellationRequestId, $idempotencyKey = null)
    {
        return self::request(
            'PUT',
            'order/cancel/' . $orderCancellationRequestId . '/withdraw',
            null,
            self::roleHeaders('user', $idempotencyKey)
        );
    }

    // Order Subscription
    /**
     * 구독 계약 내용을 변경한다 (PUT /order_subscriptions/{order_subscription_id})
     * Comment by GOSOMI
     * @date: 2026-08-18
     * product_id, product_option_id, order_name, total_subscription_duration, quantity, address_id,
     * username, phone, email, use_free_trial, free_trial_day, service_start_at, service_end_at를 전달할 수 있다.
     * 바뀐 값만 전달하면 되며 나머지는 서버가 그대로 유지한다.
     */
    public static function orderSubscriptionUpdate($orderSubscriptionId, $subscriptionParameters = array(), $idempotencyKey = null)
    {
        !isset($subscriptionParameters) && $subscriptionParameters = array();
        if (isset($subscriptionParameters['idempotency_key'])) {
            $idempotencyKey = $subscriptionParameters['idempotency_key'];
            unset($subscriptionParameters['idempotency_key']);
        }
        return self::request(
            'PUT',
            'order_subscriptions/' . $orderSubscriptionId,
            self::compact($subscriptionParameters),
            self::supervisorHeaders($idempotencyKey)
        );
    }

    /**
     * 가감산 조정항목을 추가한다 (POST /order_subscriptions/{order_subscription_id}/adjustments)
     * Comment by GOSOMI
     * @date: 2026-08-18
     * ⚠️ /adjustments 한 경로에 POST·PUT·DELETE 세 동사가 걸려 있으므로 메서드를 혼동하지 않는다.
     * type 미전달시 서버가 price > 0 이면 SETUP_PRICE, 아니면 PERIOD_DISCOUNT로 자동 판정한다.
     */
    public static function orderSubscriptionAdjustmentCreate($orderSubscriptionId, $adjustmentParameters = array(), $idempotencyKey = null)
    {
        !isset($adjustmentParameters) && $adjustmentParameters = array();
        if (isset($adjustmentParameters['idempotency_key'])) {
            $idempotencyKey = $adjustmentParameters['idempotency_key'];
            unset($adjustmentParameters['idempotency_key']);
        }
        return self::request(
            'POST',
            'order_subscriptions/' . $orderSubscriptionId . '/adjustments',
            self::compact(array_merge(
                array('price' => 0, 'duration' => 1, 'tax_free_price' => 0),
                $adjustmentParameters
            )),
            self::supervisorHeaders($idempotencyKey)
        );
    }

    /**
     * 특정 회차의 조정항목을 통째로 교체한다 (PUT /order_subscriptions/{order_subscription_id}/adjustments)
     * Comment by GOSOMI
     * @date: 2026-08-18
     * adjustments는 배열이며, 서버는 duration(회차) 단위로 갈아끼운다.
     */
    public static function orderSubscriptionAdjustmentUpdate($orderSubscriptionId, $duration = 1, $adjustments = array(), $idempotencyKey = null)
    {
        return self::request(
            'PUT',
            'order_subscriptions/' . $orderSubscriptionId . '/adjustments',
            self::compact(array(
                'duration' => $duration,
                'adjustments' => $adjustments
            )),
            self::supervisorHeaders($idempotencyKey)
        );
    }

    /**
     * 조정항목을 삭제한다 (DELETE /order_subscriptions/{order_subscription_id}/adjustments)
     * Comment by GOSOMI
     * @date: 2026-08-18
     */
    public static function orderSubscriptionAdjustmentDelete($orderSubscriptionId, $orderSubscriptionAdjustmentId, $idempotencyKey = null)
    {
        return self::request(
            'DELETE',
            'order_subscriptions/' . $orderSubscriptionId . '/adjustments',
            array('order_subscription_adjustment_id' => $orderSubscriptionAdjustmentId),
            self::supervisorHeaders($idempotencyKey)
        );
    }

    /**
     * 구독 빌(회차) 목록을 조회한다 (GET /order_subscription_bills)
     * Comment by GOSOMI
     * @date: 2026-08-18
     * order_subscription_id, page, limit, status를 전달할 수 있다.
     * ⚠️ 경로가 order_subscription_bills로 언더스코어다 (하이픈 아님).
     */
    public static function orderSubscriptionBillList($params = array())
    {
        !isset($params) && $params = array();
        $idempotencyKey = isset($params['idempotency_key']) ? $params['idempotency_key'] : null;
        unset($params['idempotency_key']);

        $params = array_merge(
            array('page' => 1, 'limit' => 20),
            $params
        );
        return self::request(
            'GET',
            'order_subscription_bills' . self::query($params),
            null,
            self::roleHeaders('user', $idempotencyKey)
        );
    }

    // Order Subscription Requests (requests/ing)
    /**
     * ⚠️ requests/ing 계열은 동사가 제각각이다. 라우트 정본 기준으로
     *    pause · purchase · termination · transfer는 POST, resume만 PUT, 수수료 계산은 GET이다.
     *    "오타겠지" 하며 resume을 POST로 바꾸지 않는다.
     * ⚠️ supervisorRequestOrderSubscription* (PUT order_subscriptions/{id}/*) 와는 다른 라우트다.
     *    이쪽은 구매자가 "요청"을 올리는 면, 저쪽은 관리자가 즉시 실행하는 면이다.
     */

    /**
     * 구독 일시중지 요청 (POST /order_subscriptions/requests/ing/pause)
     * Comment by GOSOMI
     * @date: 2026-08-18
     * reason, paused_at, expected_resume_at을 전달할 수 있다.
     */
    public static function orderSubscriptionRequestsIngPause($orderSubscriptionId, $pauseParameters = array(), $idempotencyKey = null)
    {
        !isset($pauseParameters) && $pauseParameters = array();
        if (isset($pauseParameters['idempotency_key'])) {
            $idempotencyKey = $pauseParameters['idempotency_key'];
            unset($pauseParameters['idempotency_key']);
        }
        return self::request(
            'POST',
            'order_subscriptions/requests/ing/pause',
            self::compact(array_merge(
                array('order_subscription_id' => $orderSubscriptionId),
                $pauseParameters
            )),
            self::roleHeaders('user', $idempotencyKey)
        );
    }

    /**
     * 구독 재개 요청 (PUT /order_subscriptions/requests/ing/resume)
     * Comment by GOSOMI
     * @date: 2026-08-18
     * ⚠️ requests/ing 계열 중 유일하게 PUT이다.
     */
    public static function orderSubscriptionRequestsIngResume($orderSubscriptionId, $reason = null, $idempotencyKey = null)
    {
        return self::request(
            'PUT',
            'order_subscriptions/requests/ing/resume',
            self::compact(array(
                'order_subscription_id' => $orderSubscriptionId,
                'reason' => $reason
            )),
            self::roleHeaders('user', $idempotencyKey)
        );
    }

    /**
     * 중도인수 요청 (POST /order_subscriptions/requests/ing/purchase)
     * Comment by GOSOMI
     * @date: 2026-08-18
     * price, tax_free_price, reason을 전달할 수 있다.
     */
    public static function orderSubscriptionRequestsIngPurchase($orderSubscriptionId, $purchaseParameters = array(), $idempotencyKey = null)
    {
        !isset($purchaseParameters) && $purchaseParameters = array();
        if (isset($purchaseParameters['idempotency_key'])) {
            $idempotencyKey = $purchaseParameters['idempotency_key'];
            unset($purchaseParameters['idempotency_key']);
        }
        return self::request(
            'POST',
            'order_subscriptions/requests/ing/purchase',
            self::compact(array_merge(
                array('order_subscription_id' => $orderSubscriptionId),
                $purchaseParameters
            )),
            self::roleHeaders('user', $idempotencyKey)
        );
    }

    /**
     * 중도해지 요청 (POST /order_subscriptions/requests/ing/termination)
     * Comment by GOSOMI
     * @date: 2026-08-18
     * order_number, reason, termination_fee, last_bill_refund_price, final_fee, service_end_at을 전달할 수 있다.
     */
    public static function orderSubscriptionRequestsIngTermination($orderSubscriptionId, $terminationParameters = array(), $idempotencyKey = null)
    {
        !isset($terminationParameters) && $terminationParameters = array();
        if (isset($terminationParameters['idempotency_key'])) {
            $idempotencyKey = $terminationParameters['idempotency_key'];
            unset($terminationParameters['idempotency_key']);
        }
        return self::request(
            'POST',
            'order_subscriptions/requests/ing/termination',
            self::compact(array_merge(
                array('order_subscription_id' => $orderSubscriptionId),
                $terminationParameters
            )),
            self::roleHeaders('user', $idempotencyKey)
        );
    }

    /**
     * 구독 이전/승계 요청 (POST /order_subscriptions/requests/ing/transfer)
     * Comment by GOSOMI
     * @date: 2026-08-18
     * new_user_id, new_username, new_user_email, new_user_phone, new_user_address, wallet_id, reason을 전달할 수 있다.
     */
    public static function orderSubscriptionRequestsIngTransfer($orderSubscriptionId, $transferParameters = array(), $idempotencyKey = null)
    {
        !isset($transferParameters) && $transferParameters = array();
        if (isset($transferParameters['idempotency_key'])) {
            $idempotencyKey = $transferParameters['idempotency_key'];
            unset($transferParameters['idempotency_key']);
        }
        return self::request(
            'POST',
            'order_subscriptions/requests/ing/transfer',
            self::compact(array_merge(
                array('order_subscription_id' => $orderSubscriptionId),
                $transferParameters
            )),
            self::roleHeaders('user', $idempotencyKey)
        );
    }

    /**
     * 중도해지 수수료 사전계산 (GET /order_subscriptions/requests/ing/calculate_termination_fee)
     * Comment by GOSOMI
     * @date: 2026-08-18
     * 해지 요청 전에 예상 수수료를 미리 확인할 때 사용한다.
     */
    public static function orderSubscriptionCalculateTerminationFee($orderSubscriptionId, $orderNumber = null, $idempotencyKey = null)
    {
        return self::request(
            'GET',
            'order_subscriptions/requests/ing/calculate_termination_fee' . self::query(array(
                'order_subscription_id' => $orderSubscriptionId,
                'order_number' => $orderNumber
            )),
            null,
            self::roleHeaders('user', $idempotencyKey)
        );
    }

    // Order Subscription Requests (order-subscription-requests)
    /**
     * 구독 변경요청 목록 (GET /order-subscription-requests)
     * Comment by GOSOMI
     * @date: 2026-08-18
     * project_id, order_subscription_id, page, limit, keyword, s_at, e_at, status, request_type,
     * user_id, user_group_id를 전달할 수 있다.
     * project_id를 전달하면 supervisor 모드(프로젝트 전체 검색), 없으면 본인 요청만 조회한다.
     * ⚠️ 하이픈 경로다. order_subscriptions · order_subscription_bills는 언더스코어이므로 혼동하지 않는다.
     */
    public static function orderSubscriptionRequestList($params = array())
    {
        !isset($params) && $params = array();
        $idempotencyKey = isset($params['idempotency_key']) ? $params['idempotency_key'] : null;
        unset($params['idempotency_key']);

        $params = array_merge(
            array('page' => 1, 'limit' => 20),
            $params
        );
        $projectId = isset($params['project_id']) ? $params['project_id'] : null;
        return self::request(
            'GET',
            'order-subscription-requests' . self::query($params),
            null,
            self::roleHeaders(isset($projectId) && strlen($projectId) ? 'supervisor' : 'user', $idempotencyKey)
        );
    }

    /**
     * 구독 변경요청 상세 (GET /order-subscription-requests/{request_history_id})
     * Comment by GOSOMI
     * @date: 2026-08-18
     */
    public static function orderSubscriptionRequestDetail($requestHistoryId, $projectId = null, $idempotencyKey = null)
    {
        return self::request(
            'GET',
            'order-subscription-requests/' . $requestHistoryId . self::query(array('project_id' => $projectId)),
            null,
            self::roleHeaders(isset($projectId) && strlen($projectId) ? 'supervisor' : 'user', $idempotencyKey)
        );
    }

    /**
     * 구독 변경요청 승인/반려 (PUT /order-subscription-requests/{request_history_id})
     * Comment by GOSOMI
     * @date: 2026-08-18
     * ⚠️ 승인과 반려는 별도 액션이 아니다. approval => 'approve' | 'reject' 파라미터로 갈린다.
     *    서버가 action을 예약어로 사용하기 때문에 키 이름이 approval이다.
     * reason, price, tax_free_price, termination_fee, last_bill_refund_price, final_fee, service_end_at을
     * 함께 전달할 수 있다.
     * @throws \Exception
     */
    public static function orderSubscriptionRequestUpdate($requestHistoryId, $approval, $updateParameters = array(), $idempotencyKey = null)
    {
        if (!isset($approval) || !strlen($approval)) {
            return self::exception('approval(approve 또는 reject)을 입력해주세요.');
        }
        !isset($updateParameters) && $updateParameters = array();
        if (isset($updateParameters['idempotency_key'])) {
            $idempotencyKey = $updateParameters['idempotency_key'];
            unset($updateParameters['idempotency_key']);
        }
        return self::request(
            'PUT',
            'order-subscription-requests/' . $requestHistoryId,
            self::compact(array_merge(
                $updateParameters,
                array('approval' => $approval)
            )),
            self::supervisorHeaders($idempotencyKey)
        );
    }

    // Webhook
    /**
     * 테스트 웹훅을 발송한다 (POST /webhook/test)
     * Comment by GOSOMI
     * @date: 2026-08-18
     */
    public static function sendTestWebhook($headerContentType = null, $idempotencyKey = null)
    {
        return self::request(
            'POST',
            'webhook/test',
            self::compact(array('header_content_type' => $headerContentType)),
            self::idempotencyHeaders($idempotencyKey)
        );
    }
}
