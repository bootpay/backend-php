<?php

namespace Bootpay\ServerPhp\Commerce;

use Bootpay\ServerPhp\BootpayCommerceApi;

class InvoiceModule
{
    private $bootpay;

    public function __construct(BootpayCommerceApi $bootpay)
    {
        $this->bootpay = $bootpay;
    }

    /**
     * 청구서 목록 조회
     * @param array|null $params 조회 파라미터
     * @return object
     */
    public function getList($params = null)
    {
        $queryString = $this->buildQueryString($params);
        return $this->bootpay->get('invoices' . $queryString);
    }

    /**
     * 청구서 생성
     * @param array $invoice 청구서 정보
     * @return object
     */
    public function create($invoice)
    {
        return $this->bootpay->post('invoices', $invoice);
    }

    /**
     * 청구서 알림 발송
     * @param string $invoiceId 청구서 ID
     * @param array $sendTypes 발송 타입 배열 (예: [1, 2] - SMS, Email 등)
     * @return object
     */
    public function notify($invoiceId, $sendTypes)
    {
        return $this->bootpay->post("invoices/{$invoiceId}/notify", array('send_types' => $sendTypes));
    }

    /**
     * 청구서 상세 조회
     * @param string $invoiceId 청구서 ID
     * @return object
     */
    public function detail($invoiceId)
    {
        return $this->bootpay->get("invoices/{$invoiceId}");
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

        if (empty($query)) {
            return '';
        }

        return '?' . http_build_query($query);
    }
}
