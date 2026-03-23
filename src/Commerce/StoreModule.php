<?php

namespace Bootpay\ServerPhp\Commerce;

use Bootpay\ServerPhp\BootpayCommerceApi;

class StoreModule
{
    private $bootpay;

    public function __construct(BootpayCommerceApi $bootpay)
    {
        $this->bootpay = $bootpay;
    }

    /**
     * 가맹점 기본 정보 조회
     * @return object
     */
    public function info()
    {
        return $this->bootpay->get('store');
    }

    /**
     * 가맹점 상세 정보 조회
     * @return object
     */
    public function detail()
    {
        return $this->bootpay->get('store/detail');
    }
}
