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
