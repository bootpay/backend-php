<?php

namespace Bootpay\ServerPhp\Commerce;

use Bootpay\ServerPhp\BootpayCommerceApi;

class CategoryModule
{
    private $bootpay;

    public function __construct(BootpayCommerceApi $bootpay)
    {
        $this->bootpay = $bootpay;
    }

    /**
     * 카테고리 트리 조회
     * @return object
     */
    public function getList()
    {
        return $this->bootpay->get('categories');
    }

    /**
     * 카테고리 단건 조회
     * @param string $categoryId 카테고리 ID
     * @return object
     */
    public function detail($categoryId)
    {
        return $this->bootpay->get("categories/{$categoryId}");
    }

    /**
     * 카테고리 생성
     * @param array $params 카테고리 생성 파라미터
     * @return object
     */
    public function create($params)
    {
        return $this->bootpay->post('categories', $params);
    }

    /**
     * 카테고리 수정
     * @param array $params 카테고리 수정 파라미터 (category_id 필수)
     * @return object
     * @throws \Exception
     */
    public function update($params)
    {
        if (!isset($params['category_id']) || empty($params['category_id'])) {
            throw new \Exception('category_id is required');
        }
        $categoryId = $params['category_id'];
        $body = $params;
        unset($body['category_id']);
        return $this->bootpay->put("categories/{$categoryId}", $body);
    }

    /**
     * 카테고리 삭제
     * @param string $categoryId 카테고리 ID
     * @return object
     */
    public function destroy($categoryId)
    {
        return $this->bootpay->delete("categories/{$categoryId}");
    }
}
