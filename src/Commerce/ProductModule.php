<?php

namespace Bootpay\ServerPhp\Commerce;

use Bootpay\ServerPhp\BootpayCommerceApi;

class ProductModule
{
    private $bootpay;

    public function __construct(BootpayCommerceApi $bootpay)
    {
        $this->bootpay = $bootpay;
    }

    /**
     * 상품 목록 조회
     * @param array|null $params 조회 파라미터
     * @return object
     */
    public function getList($params = null)
    {
        $queryString = $this->buildQueryString($params);
        return $this->bootpay->get('products' . $queryString);
    }

    /**
     * 상품 생성 (이미지 포함)
     * @param array $product 상품 정보
     * @param array|null $imagePaths 이미지 파일 경로 배열
     * @return object
     */
    public function create($product, $imagePaths = null)
    {
        return $this->bootpay->requestMultipart('POST', 'products', $product, $imagePaths);
    }

    /**
     * 상품 상세 조회
     * @param string $productId 상품 ID
     * @return object
     */
    public function detail($productId)
    {
        return $this->bootpay->get("products/{$productId}");
    }

    /**
     * 상품 수정
     * @param array $product 상품 정보
     * @return object
     * @throws \Exception
     */
    public function update($product)
    {
        if (!isset($product['product_id']) || empty($product['product_id'])) {
            throw new \Exception('product_id is required');
        }
        $productId = $product['product_id'];
        return $this->bootpay->put("products/{$productId}", $product);
    }

    /**
     * 상품 상태 변경
     * @param array $params 상태 변경 파라미터
     * @return object
     * @throws \Exception
     */
    public function status($params)
    {
        if (!isset($params['product_id']) || empty($params['product_id'])) {
            throw new \Exception('product_id is required');
        }
        $productId = $params['product_id'];
        return $this->bootpay->put("products/{$productId}/status", $params);
    }

    /**
     * 상품 삭제
     * @param string $productId 상품 ID
     * @return object
     */
    public function delete($productId)
    {
        return $this->bootpay->delete("products/{$productId}");
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
        if (isset($params['type'])) {
            $query['type'] = $params['type'];
        }
        if (isset($params['period_type'])) {
            $query['period_type'] = $params['period_type'];
        }
        if (isset($params['s_at'])) {
            $query['s_at'] = $params['s_at'];
        }
        if (isset($params['e_at'])) {
            $query['e_at'] = $params['e_at'];
        }
        if (isset($params['category_code'])) {
            $query['category_code'] = $params['category_code'];
        }

        if (empty($query)) {
            return '';
        }

        return '?' . http_build_query($query);
    }
}
