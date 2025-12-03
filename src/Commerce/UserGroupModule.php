<?php

namespace Bootpay\ServerPhp\Commerce;

use Bootpay\ServerPhp\BootpayCommerceApi;

class UserGroupModule
{
    private $bootpay;

    public function __construct(BootpayCommerceApi $bootpay)
    {
        $this->bootpay = $bootpay;
    }

    /**
     * 사용자 그룹 생성
     * @param array $userGroup 그룹 정보
     * @return object
     */
    public function create($userGroup)
    {
        return $this->bootpay->post('user-groups', $userGroup);
    }

    /**
     * 사용자 그룹 목록 조회
     * @param array|null $params 조회 파라미터
     * @return object
     */
    public function getList($params = null)
    {
        $queryString = $this->buildQueryString($params);
        return $this->bootpay->get('user-groups' . $queryString);
    }

    /**
     * 사용자 그룹 상세 조회
     * @param string $userGroupId 그룹 ID
     * @return object
     */
    public function detail($userGroupId)
    {
        return $this->bootpay->get("user-groups/{$userGroupId}");
    }

    /**
     * 사용자 그룹 수정
     * @param array $userGroup 그룹 정보
     * @return object
     * @throws \Exception
     */
    public function update($userGroup)
    {
        if (!isset($userGroup['user_group_id']) || empty($userGroup['user_group_id'])) {
            throw new \Exception('user_group_id is required');
        }
        $userGroupId = $userGroup['user_group_id'];
        return $this->bootpay->put("user-groups/{$userGroupId}", $userGroup);
    }

    /**
     * 그룹에 사용자 추가
     * @param string $userGroupId 그룹 ID
     * @param string $userId 사용자 ID
     * @return object
     */
    public function userCreate($userGroupId, $userId)
    {
        return $this->bootpay->post("user-groups/{$userGroupId}/add_user", array('user_id' => $userId));
    }

    /**
     * 그룹에서 사용자 제거
     * @param string $userGroupId 그룹 ID
     * @param string $userId 사용자 ID
     * @return object
     */
    public function userDelete($userGroupId, $userId)
    {
        return $this->bootpay->delete("user-groups/{$userGroupId}/remove_user?user_id={$userId}");
    }

    /**
     * 그룹 제한 설정
     * @param array $params 제한 설정 파라미터
     * @return object
     * @throws \Exception
     */
    public function limit($params)
    {
        if (!isset($params['user_group_id']) || empty($params['user_group_id'])) {
            throw new \Exception('user_group_id is required');
        }
        $userGroupId = $params['user_group_id'];
        return $this->bootpay->put("user-groups/{$userGroupId}/limit", $params);
    }

    /**
     * 그룹 거래 집계 조회
     * @param array $params 집계 파라미터
     * @return object
     * @throws \Exception
     */
    public function aggregateTransaction($params)
    {
        if (!isset($params['user_group_id']) || empty($params['user_group_id'])) {
            throw new \Exception('user_group_id is required');
        }
        $userGroupId = $params['user_group_id'];
        return $this->bootpay->put("user-groups/{$userGroupId}/aggregate-transaction", $params);
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
        if (isset($params['corporate_type'])) {
            $query['corporate_type'] = $params['corporate_type'];
        }

        if (empty($query)) {
            return '';
        }

        return '?' . http_build_query($query);
    }
}
