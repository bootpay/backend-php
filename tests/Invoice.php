<?php
/*
 * 청구서 목록 조회 / 상세 조회 / 재안내 예제입니다.
 * GET invoices, GET invoices/{invoice_id}, POST invoices/{invoice_id}/notify
 */
require_once '../vendor/autoload.php';
// require_once __DIR__.'/../src/BootpayCommerceApi.php';

use Bootpay\ServerPhp\BootpayCommerceApi;

BootpayCommerceApi::setConfiguration(
    'QIzXk4M3EeD-6B1GTfmGHA',
    'vRle44QfyBj7nzJlBbeebqkbtlJVRTS2DQa9Adpz3d8=',
    'development'
);

try {
    // GET invoices (응답은 list, count 구조이며 서버 기본 limit은 24입니다)
    $invoices = BootpayCommerceApi::invoiceList(array(
        'page' => 1,
        'limit' => 24,
        'keyword' => '홍길동',
        'css_at' => '2026-08-01',
        'cse_at' => '2026-08-31'
    ));
    var_dump($invoices);

    $invoiceId = isset($invoices->data->list[0]->id) ? $invoices->data->list[0]->id : 1;

    // GET invoices/{invoice_id}
    $invoice = BootpayCommerceApi::invoiceDetail($invoiceId);
    var_dump($invoice);

    // POST invoices/{invoice_id}/notify
    // ⚠️ 실제 고객에게 알림이 발송되므로 테스트 호출시 주의하세요.
    $notify = BootpayCommerceApi::invoiceNotify($invoiceId, array('sms', 'email'));
    var_dump($notify);
} catch (Exception $e) {
    echo($e->getMessage());
}
