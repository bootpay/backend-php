<?php

namespace Bootpay\ServerPhp\Commerce;

use Bootpay\ServerPhp\BootpayCommerceApi;

class UserModule
{
    private $bootpay;

    public function __construct(BootpayCommerceApi $bootpay)
    {
        $this->bootpay = $bootpay;
    }

    /**
     * 사용자 토큰 발급
     * @param string $userId 사용자 ID
     * @return object
     */
    public function token($userId)
    {
        return $this->bootpay->post('users/login/token', array('user_id' => $userId));
    }

    /**
     * 회원가입
     * @param array $user 사용자 정보
     * @return object
     */
    public function join($user)
    {
        return $this->bootpay->post('users/join', $user);
    }

    /**
     * 중복 체크
     * @param string $key 체크할 필드 (login_id, phone, email 등)
     * @param string $value 체크할 값
     * @return object
     */
    public function checkExist($key, $value)
    {
        $encodedValue = urlencode($value);
        return $this->bootpay->get("users/join/{$key}?pk={$encodedValue}");
    }

    /**
     * 본인인증 데이터 조회
     * @param string $standId 인증 ID
     * @return object
     */
    public function authenticationData($standId)
    {
        return $this->bootpay->get("users/authenticate/{$standId}");
    }

    /**
     * 로그인
     * @param string $loginId 로그인 ID
     * @param string $loginPw 비밀번호
     * @return object
     */
    public function login($loginId, $loginPw)
    {
        return $this->bootpay->post('users/login', array(
            'login_id' => $loginId,
            'login_pw' => $loginPw
        ));
    }

    /**
     * 회원 로그인 (V1 Mall API)
     * POST /v1/users/login
     * v1 에는 단수 user/* 라우트가 없다. 로그인은 v1/users/login#create 다.
     * ⚠️ 서버(LoginService)는 login_id/password 만 읽는다. corporate_type 은 전달돼도 무시된다.
     * @param array $params 로그인 파라미터 (login_id, password / corporate_type 미지정시 0,
     *                      idempotency_key 는 Idempotency-Key 헤더로 전송된다)
     * @return object
     */
    public function userLogin($params)
    {
        $idempotencyKey = isset($params['idempotency_key']) ? $params['idempotency_key'] : null;
        unset($params['idempotency_key']);
        if (!isset($params['corporate_type'])) {
            $params['corporate_type'] = 0;
        }
        return $this->bootpay->post('users/login', $this->compact($params), $this->mallHeaders(null, $idempotencyKey));
    }

    /**
     * 회원 세션 조회 (V1 Mall API)
     * GET /v1/users/session
     * @param string|null $userJwt 로그인시 발급받은 회원 JWT
     * @param string|null $idempotencyKey 미지정시 자동 생성
     * @return object
     */
    public function userSession($userJwt = null, $idempotencyKey = null)
    {
        return $this->bootpay->get('users/session', $this->mallHeaders($userJwt, $idempotencyKey));
    }

    /**
     * 회원 로그아웃 (V1 Mall API)
     * DELETE /v1/users/session
     * @param string $userJwt 로그인시 발급받은 회원 JWT
     * @param string|null $idempotencyKey 미지정시 자동 생성
     * @return object
     */
    public function userLogout($userJwt, $idempotencyKey = null)
    {
        return $this->bootpay->delete('users/session', $this->mallHeaders($userJwt, $idempotencyKey));
    }

    /**
     * 회원가입 (V1 Mall API) — 일반 회원가입용
     * POST /v1/users/join
     * ⚠️ join($user) 과 같은 엔드포인트를 부른다. 중복이 아니라 용도가 다르다 —
     *    이쪽은 password/corporate_type/group 을 쓰는 일반 회원가입, 저쪽은 uid/login_email/login_pw 를 쓰는 외부 uid 연동 가입이다.
     *    서버가 파라미터 조합으로 분기하므로 둘 다 유지한다.
     * @param array $params 회원가입 파라미터 (corporate_type 미지정시 0, null 값은 전송하지 않는다,
     *                      idempotency_key 는 Idempotency-Key 헤더로 전송된다)
     * @return object
     */
    public function userJoin($params)
    {
        $idempotencyKey = isset($params['idempotency_key']) ? $params['idempotency_key'] : null;
        unset($params['idempotency_key']);
        if (!isset($params['corporate_type'])) {
            $params['corporate_type'] = 0;
        }
        return $this->bootpay->post('users/join', $this->compact($params), $this->mallHeaders(null, $idempotencyKey));
    }

    /**
     * 회원가입 중복 확인 (V1 Mall API) — key 를 인자로 받는 일반형
     * GET /v1/users/join/{type}?pk={pk}
     * ⚠️ uidExist 등 전용형과 기능이 겹치지만 둘 다 유지한다.
     *    일반형은 서버에 새 key 가 생겨도 SDK 수정 없이 쓸 수 있다.
     * @param string $type email-exist, id-exist, phone-exist, uid-exist, group-business-number-exist
     * @param string $pk 중복 확인할 값
     * @param string|null $idempotencyKey 미지정시 자동 생성
     * @return object
     */
    public function userJoinCheck($type, $pk, $idempotencyKey = null)
    {
        $encodedPk = urlencode($pk);
        return $this->bootpay->get("users/join/{$type}?pk={$encodedPk}", $this->mallHeaders(null, $idempotencyKey));
    }

    /**
     * 외부 uid(ex_uid) 중복 검사
     * GET /v1/users/join/uid-exist?pk={uid}
     * email-exist / id-exist / phone-exist / group-business-number-exist 와 같은 전용형이다.
     * @param string $uid 중복 확인할 외부 uid
     * @param string|null $idempotencyKey 미지정시 자동 생성
     * @return object
     */
    public function uidExist($uid, $idempotencyKey = null)
    {
        $encodedUid = urlencode($uid);
        $headers = $this->mallHeaders(null, $idempotencyKey);
        $headers[] = 'BOOTPAY-ROLE: user';
        return $this->bootpay->get("users/join/uid-exist?pk={$encodedUid}", $headers);
    }

    /**
     * 사용자 목록 조회
     * @param array|null $params 조회 파라미터
     * @return object
     */
    public function getList($params = null)
    {
        $queryString = $this->buildQueryString($params);
        return $this->bootpay->get('users' . $queryString);
    }

    /**
     * 사용자 상세 조회
     * @param string $userId 사용자 ID
     * @return object
     */
    public function detail($userId)
    {
        return $this->bootpay->get("users/{$userId}");
    }

    /**
     * 사용자 정보 수정
     * @param array $user 사용자 정보
     * @return object
     * @throws \Exception
     */
    public function update($user)
    {
        if (!isset($user['user_id']) || empty($user['user_id'])) {
            throw new \Exception('user_id is required');
        }
        $userId = $user['user_id'];
        return $this->bootpay->put("users/{$userId}", $user);
    }

    /**
     * 사용자 삭제 (회원탈퇴)
     * @param string $userId 사용자 ID
     * @return object
     */
    public function delete($userId)
    {
        return $this->bootpay->delete("users/{$userId}");
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
     * V1 Mall API 요청 헤더
     * Idempotency-Key 는 미지정시 매 호출마다 생성되고, Bootpay-User-JWT 는 값이 있을 때만 붙는다.
     */
    private function mallHeaders($userJwt = null, $idempotencyKey = null)
    {
        $headers = array(
            'Idempotency-Key: ' . ($idempotencyKey ?: BootpayCommerceApi::generateIdempotencyKey())
        );
        if ($userJwt !== null && $userJwt !== '') {
            $headers[] = 'Bootpay-User-JWT: ' . $userJwt;
        }
        return $headers;
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
        if (isset($params['member_type'])) {
            $query['member_type'] = $params['member_type'];
        }
        if (isset($params['type'])) {
            $query['type'] = $params['type'];
        }

        if (empty($query)) {
            return '';
        }

        return '?' . http_build_query($query);
    }
}
