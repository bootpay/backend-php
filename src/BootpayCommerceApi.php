<?php

namespace Bootpay\ServerPhp;

use Bootpay\ServerPhp\Commerce\UserModule;
use Bootpay\ServerPhp\Commerce\UserGroupModule;
use Bootpay\ServerPhp\Commerce\ProductModule;
use Bootpay\ServerPhp\Commerce\InvoiceModule;
use Bootpay\ServerPhp\Commerce\OrderModule;
use Bootpay\ServerPhp\Commerce\OrderCancelModule;
use Bootpay\ServerPhp\Commerce\OrderSubscriptionModule;
use Bootpay\ServerPhp\Commerce\OrderSubscriptionBillModule;
use Bootpay\ServerPhp\Commerce\OrderSubscriptionAdjustmentModule;

class BootpayCommerceApi
{
    private $token = '';
    private $clientKey = '';
    private $secretKey = '';
    private $mode = 'production';
    private $role = 'user';

    private static $API_URL = array(
        'development' => 'https://dev-api.bootapi.com/v1',
        'stage' => 'https://stage-api.bootapi.com/v1',
        'production' => 'https://api.bootapi.com/v1'
    );

    private static $postMethods = array('POST', 'PUT');
    private static $apiVersion = '1.0.0';
    private static $sdkVersion = '1.0.0';

    public $user;
    public $userGroup;
    public $product;
    public $invoice;
    public $order;
    public $orderCancel;
    public $orderSubscription;
    public $orderSubscriptionBill;
    public $orderSubscriptionAdjustment;

    public function __construct($clientKey = null, $secretKey = null, $mode = 'production')
    {
        if ($clientKey !== null && $secretKey !== null) {
            $this->setConfiguration($clientKey, $secretKey, $mode);
        }
        $this->initModules();
    }

    private function initModules()
    {
        $this->user = new UserModule($this);
        $this->userGroup = new UserGroupModule($this);
        $this->product = new ProductModule($this);
        $this->invoice = new InvoiceModule($this);
        $this->order = new OrderModule($this);
        $this->orderCancel = new OrderCancelModule($this);
        $this->orderSubscription = new OrderSubscriptionModule($this);
        $this->orderSubscriptionBill = new OrderSubscriptionBillModule($this);
        $this->orderSubscriptionAdjustment = new OrderSubscriptionAdjustmentModule($this);
    }

    public function setConfiguration($clientKey, $secretKey, $mode = 'production')
    {
        $this->clientKey = $clientKey;
        $this->secretKey = $secretKey;
        $this->mode = $mode;
        return $this;
    }

    public function setToken($token)
    {
        $this->token = $token;
        return $this;
    }

    public function getToken()
    {
        return $this->token;
    }

    public function hasToken()
    {
        return !empty($this->token);
    }

    public function setRole($role)
    {
        $this->role = $role;
        return $this;
    }

    public function getRole()
    {
        return $this->role;
    }

    public function withRole($role)
    {
        $this->role = $role;
        return $this;
    }

    public function asUser()
    {
        return $this->withRole('user');
    }

    public function asManager()
    {
        return $this->withRole('manager');
    }

    public function asPartner()
    {
        return $this->withRole('partner');
    }

    public function asVendor()
    {
        return $this->withRole('vendor');
    }

    public function asSupervisor()
    {
        return $this->withRole('supervisor');
    }

    public function clearRole()
    {
        $this->role = 'user';
        return $this;
    }

    private function entrypoints($url)
    {
        return implode('/', array(self::$API_URL[$this->mode], $url));
    }

    private function getBasicAuthHeader()
    {
        if (!empty($this->clientKey) && !empty($this->secretKey)) {
            $credentials = $this->clientKey . ':' . $this->secretKey;
            return 'Basic ' . base64_encode($credentials);
        }
        return '';
    }

    private function createHeaders($headers = null, $useBasicAuth = false)
    {
        if (!isset($headers)) {
            $headers = array();
        }

        $defaultHeaders = array(
            'Content-Type: application/json',
            'Accept: application/json',
            'Accept-Charset: utf-8',
            'BOOTPAY-SDK-VERSION: ' . self::$sdkVersion,
            'BOOTPAY-API-VERSION: ' . self::$apiVersion,
            'BOOTPAY-SDK-TYPE: 303',
            'BOOTPAY-ROLE: ' . $this->role
        );

        if ($useBasicAuth) {
            $defaultHeaders[] = 'Authorization: ' . $this->getBasicAuthHeader();
        } elseif (!empty($this->token)) {
            $defaultHeaders[] = 'Authorization: Bearer ' . $this->token;
        }

        return array_merge($defaultHeaders, $headers);
    }

    public function request($method, $url, $data = null, $headers = null, $useBasicAuth = false)
    {
        if (!isset($headers)) {
            $headers = array();
        }

        $isPost = in_array($method, self::$postMethods);
        $fullUrl = $this->entrypoints($url);

        $channel = curl_init($fullUrl);
        curl_setopt($channel, CURLOPT_URL, $fullUrl);
        curl_setopt($channel, CURLOPT_HTTPHEADER, $this->createHeaders($headers, $useBasicAuth));

        if ($isPost) {
            curl_setopt($channel, CURLOPT_POST, true);
            if ($data !== null) {
                curl_setopt($channel, CURLOPT_POSTFIELDS, json_encode($data));
            }
        }

        if (in_array($method, array('DELETE', 'PUT'))) {
            curl_setopt($channel, CURLOPT_CUSTOMREQUEST, $method);
            if ($data !== null && $method === 'PUT') {
                curl_setopt($channel, CURLOPT_POSTFIELDS, json_encode($data));
            }
        }

        curl_setopt($channel, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($channel);
        $errno = curl_errno($channel);
        $errMsg = curl_error($channel);

        if ($errno) {
            curl_close($channel);
            throw new \Exception('error: ' . $errno . ', msg: ' . $errMsg);
        }

        curl_close($channel);
        $json = json_decode(trim($response));

        return $json;
    }

    public function requestMultipart($method, $url, $data = null, $files = null, $headers = null)
    {
        $fullUrl = $this->entrypoints($url);

        $channel = curl_init($fullUrl);
        curl_setopt($channel, CURLOPT_URL, $fullUrl);

        $multipartHeaders = array(
            'Accept: application/json',
            'Accept-Charset: utf-8',
            'BOOTPAY-SDK-VERSION: ' . self::$sdkVersion,
            'BOOTPAY-API-VERSION: ' . self::$apiVersion,
            'BOOTPAY-SDK-TYPE: 303',
            'BOOTPAY-ROLE: ' . $this->role
        );

        if (!empty($this->token)) {
            $multipartHeaders[] = 'Authorization: Bearer ' . $this->token;
        }

        curl_setopt($channel, CURLOPT_HTTPHEADER, $multipartHeaders);
        curl_setopt($channel, CURLOPT_POST, true);

        $postData = array();

        if ($data !== null) {
            foreach ($data as $key => $value) {
                if (is_array($value) || is_object($value)) {
                    $postData[$key] = json_encode($value);
                } else {
                    $postData[$key] = $value;
                }
            }
        }

        if ($files !== null && is_array($files)) {
            foreach ($files as $index => $filePath) {
                if (file_exists($filePath)) {
                    $postData["images[$index]"] = new \CURLFile($filePath);
                }
            }
        }

        curl_setopt($channel, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($channel, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($channel);
        $errno = curl_errno($channel);
        $errMsg = curl_error($channel);

        if ($errno) {
            curl_close($channel);
            throw new \Exception('error: ' . $errno . ', msg: ' . $errMsg);
        }

        curl_close($channel);
        $json = json_decode(trim($response));

        return $json;
    }

    public function get($url, $headers = null)
    {
        return $this->request('GET', $url, null, $headers);
    }

    public function post($url, $data = null, $headers = null)
    {
        return $this->request('POST', $url, $data, $headers);
    }

    public function postWithBasicAuth($url, $data = null, $headers = null)
    {
        return $this->request('POST', $url, $data, $headers, true);
    }

    public function put($url, $data = null, $headers = null)
    {
        return $this->request('PUT', $url, $data, $headers);
    }

    public function delete($url, $headers = null)
    {
        return $this->request('DELETE', $url, null, $headers);
    }

    /**
     * 액세스 토큰 발급
     * client_key/secret_key로 인증
     */
    public function getAccessToken()
    {
        $response = $this->postWithBasicAuth('request/token', array(
            'client_key' => $this->clientKey,
            'secret_key' => $this->secretKey
        ));

        if (isset($response->access_token)) {
            $this->token = $response->access_token;
        }

        return $response;
    }

    /**
     * 토큰을 발급받아 설정합니다. (메서드 체이닝 지원)
     */
    public function withToken()
    {
        $this->getAccessToken();
        return $this;
    }
}
