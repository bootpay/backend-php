<?php

namespace Bootpay\ServerPhp\Test\Commerce;

use Bootpay\ServerPhp\Test\TestConfig;
use Bootpay\ServerPhp\BootpayCommerceApi;

class OrderTest extends TestConfig
{
    /** @var BootpayCommerceApi */
    private static $api;

    public static function setUpBeforeClass(): void
    {
        self::$api = self::createCommerceApiWithToken();
    }

    // ---- Order ----

    public function testOrderList()
    {
        $response = self::$api->order->getList(array(
            'page' => 1,
            'limit' => 10
        ));
        $this->printResponse('Commerce order.list', $response);

        $this->assertNotNull($response);
    }

    public function testOrderListWithFilter()
    {
        $response = self::$api->order->getList(array(
            'page' => 1,
            'limit' => 5,
            'keyword' => 'test'
        ));
        $this->printResponse('Commerce order.list (keyword)', $response);

        $this->assertNotNull($response);
    }

    public function testOrderDetail()
    {
        $listResponse = self::$api->order->getList(array(
            'page' => 1,
            'limit' => 1
        ));

        if (isset($listResponse->items) && count($listResponse->items) > 0) {
            $orderId = $listResponse->items[0]->order_id;
            $response = self::$api->order->detail($orderId);
            $this->printResponse('Commerce order.detail', $response);

            $this->assertNotNull($response);
        } else {
            $this->markTestSkipped('No orders available for detail test');
        }
    }

    // ---- OrderCancel ----

    public function testOrderCancelList()
    {
        $response = self::$api->orderCancel->getList();
        $this->printResponse('Commerce orderCancel.list', $response);

        $this->assertNotNull($response);
    }

    // ---- OrderSubscription ----

    public function testOrderSubscriptionList()
    {
        $response = self::$api->orderSubscription->getList(array(
            'page' => 1,
            'limit' => 10
        ));
        $this->printResponse('Commerce orderSubscription.list', $response);

        $this->assertNotNull($response);
    }

    public function testOrderSubscriptionDetail()
    {
        $listResponse = self::$api->orderSubscription->getList(array(
            'page' => 1,
            'limit' => 1
        ));

        if (isset($listResponse->items) && count($listResponse->items) > 0) {
            $subscriptionId = $listResponse->items[0]->order_subscription_id;
            $response = self::$api->orderSubscription->detail($subscriptionId);
            $this->printResponse('Commerce orderSubscription.detail', $response);

            $this->assertNotNull($response);
        } else {
            $this->markTestSkipped('No order subscriptions available for detail test');
        }
    }

    // ---- OrderSubscriptionBill ----

    public function testOrderSubscriptionBillList()
    {
        $response = self::$api->orderSubscriptionBill->getList(array(
            'page' => 1,
            'limit' => 10
        ));
        $this->printResponse('Commerce orderSubscriptionBill.list', $response);

        $this->assertNotNull($response);
    }

    public function testOrderSubscriptionBillDetail()
    {
        $listResponse = self::$api->orderSubscriptionBill->getList(array(
            'page' => 1,
            'limit' => 1
        ));

        if (isset($listResponse->items) && count($listResponse->items) > 0) {
            $billId = $listResponse->items[0]->order_subscription_bill_id;
            $response = self::$api->orderSubscriptionBill->detail($billId);
            $this->printResponse('Commerce orderSubscriptionBill.detail', $response);

            $this->assertNotNull($response);
        } else {
            $this->markTestSkipped('No order subscription bills available for detail test');
        }
    }

    // ---- Invoice ----

    public function testInvoiceList()
    {
        $response = self::$api->invoice->getList(array(
            'page' => 1,
            'limit' => 10
        ));
        $this->printResponse('Commerce invoice.list', $response);

        $this->assertNotNull($response);
    }

    public function testInvoiceCreate()
    {
        $response = self::$api->invoice->create(array(
            'order_name' => 'PHPUnit Invoice Test ' . time(),
            'price' => 10000
        ));
        $this->printResponse('Commerce invoice.create', $response);

        $this->assertNotNull($response);
    }

    public function testInvoiceDetail()
    {
        $listResponse = self::$api->invoice->getList(array(
            'page' => 1,
            'limit' => 1
        ));

        if (isset($listResponse->items) && count($listResponse->items) > 0) {
            $invoiceId = $listResponse->items[0]->invoice_id;
            $response = self::$api->invoice->detail($invoiceId);
            $this->printResponse('Commerce invoice.detail', $response);

            $this->assertNotNull($response);
        } else {
            $this->markTestSkipped('No invoices available for detail test');
        }
    }
}
