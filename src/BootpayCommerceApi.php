<?php

namespace Bootpay\ServerPhp;

class BootpayCommerceApi
{
    private static $token = '';
    private static $clientKey = '';
    private static $secretKey = '';
    private static $mode = 'production';

    private static $API_URL = array(
        'development' => 'https://dev-api.bootapi.com/v1',
        'stage' => 'https://stage-api.bootapi.com/v1',
        'production' => 'https://api.bootapi.com/v1'
    );

    private static function entrypoints($url)
    {
        return implode('/', array(self::$API_URL[self::$mode], ltrim($url, '/')));
    }

    private static function createHeaders($headers = null)
    {
        !isset($headers) && $headers = array();

        $auth = '';
        if (strlen(self::$token)) {
            $auth = 'Bearer ' . self::$token;
        } else {
            $auth = 'Basic ' . base64_encode(self::$clientKey . ':' . self::$secretKey);
        }

        return array_merge($headers, array(
            'Content-Type: application/json',
            'Accept: application/json',
            'Authorization: ' . $auth,
            'BOOTPAY-ROLE: user'
        ));
    }

    private static function request($method, $url, $data = null, $headers = null)
    {
        !isset($headers) && $headers = array();

        $channel = curl_init(self::entrypoints($url));
        curl_setopt($channel, CURLOPT_URL, self::entrypoints($url));
        curl_setopt($channel, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($channel, CURLOPT_HTTPHEADER, self::createHeaders($headers));

        if (in_array($method, array('POST', 'PUT'))) {
            curl_setopt($channel, CURLOPT_POSTFIELDS, json_encode($data));
        }

        curl_setopt($channel, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($channel);
        $errno = curl_errno($channel);
        $errMsg = curl_error($channel);
        if ($errno) {
            throw new \Exception('error: ' . $errno . ', msg: ' . $errMsg);
        }
        curl_close($channel);

        return json_decode(trim($response));
    }

    public static function setConfiguration($clientKey, $secretKey, $mode = 'production')
    {
        self::$clientKey = $clientKey;
        self::$secretKey = $secretKey;
        self::$mode = $mode;
    }

    public static function setToken($token)
    {
        self::$token = $token;
    }

    // Store
    public static function getStore()
    {
        return self::request('GET', 'store');
    }

    public static function storeInfo()
    {
        return self::getStore();
    }

    public static function getStoreDetail()
    {
        return self::request('GET', 'store/detail');
    }

    public static function storeDetail()
    {
        return self::getStoreDetail();
    }

    // User
    public static function userLogin($loginId, $loginPw)
    {
        return self::request('POST', 'users/login', array(
            'login_id' => $loginId,
            'login_pw' => $loginPw
        ));
    }

    public static function userJoin($user)
    {
        return self::request('POST', 'users/join', $user);
    }

    public static function userJoinCheck($type, $pk)
    {
        return self::request('GET', 'users/join/' . $type . '?pk=' . urlencode($pk));
    }

    // Product
    public static function products($params = array())
    {
        $query = '';
        if (!empty($params)) {
            $query = '?' . http_build_query($params);
        }
        return self::request('GET', 'products' . $query);
    }

    public static function productDetail($productId)
    {
        return self::request('GET', 'products/' . $productId);
    }
}
