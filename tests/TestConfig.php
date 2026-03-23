<?php

namespace Bootpay\ServerPhp\Test;

use PHPUnit\Framework\TestCase;
use Bootpay\ServerPhp\BootpayApi;
use Bootpay\ServerPhp\BootpayCommerceApi;

/**
 * Base test class for Bootpay SDK integration tests.
 *
 * Reads BOOTPAY_ENV env var (default: "development") to select keys.
 */
class TestConfig extends TestCase
{
    // PG API keys
    const PG_DEV_APPLICATION_ID = '59bfc738e13f337dbd6ca48a';
    const PG_DEV_PRIVATE_KEY = 'pDc0NwlkEX3aSaHTp/PPL/i8vn5E/CqRChgyEp/gHD0=';
    const PG_PROD_APPLICATION_ID = '5b8f6a4d396fa665fdc2b5ea';
    const PG_PROD_PRIVATE_KEY = 'rm6EYECr6aroQVG2ntW0A6LpWnkTgP4uQ3H18sDDUYw=';

    // Commerce API keys
    const COMMERCE_DEV_CLIENT_KEY = 'hxS-Up--5RvT6oU6QJE0JA';
    const COMMERCE_DEV_SECRET_KEY = 'r5zxvDcQJiAP2PBQ0aJjSHQtblNmYFt6uFoEMhti_mg=';
    const COMMERCE_PROD_CLIENT_KEY = 'sEN72kYZBiyMNytA8nUGxQ';
    const COMMERCE_PROD_SECRET_KEY = 'rnZLJamENRgfwTccwmI_Uu9cxsPpAV9X2W-Htg73yfU=';

    // Test data IDs
    const TEST_RECEIPT_ID = '628b2206d01c7e00209b6087';
    const TEST_RECEIPT_ID_CONFIRM = '62876963d01c7e00209b6028';
    const TEST_RECEIPT_ID_CASH = '62e0f11f1fc192036b1b3c92';
    const TEST_RECEIPT_ID_ESCROW = '628ae7ffd01c7e001e9b6066';
    const TEST_RECEIPT_ID_BILLING = '62c7ccebcf9f6d001b3adcd4';
    const TEST_RECEIPT_ID_TRANSFER = '66541bc4ca4517e69343e24c';
    const TEST_BILLING_KEY = '628b2644d01c7e00209b6092';
    const TEST_BILLING_KEY_2 = '66542dfb4d18d5fc7b43e1b6';
    const TEST_RESERVE_ID = '6490149ca575b40024f0b70d';
    const TEST_USER_ID = '1234';
    const TEST_CERTIFICATE_RECEIPT_ID = '61b009aaec81b4057e7f6ecd';

    /**
     * Get current environment mode
     */
    protected static function getEnv(): string
    {
        return getenv('BOOTPAY_ENV') ?: 'development';
    }

    /**
     * Configure PG API with appropriate keys
     */
    protected static function setupPgApi(): void
    {
        $env = self::getEnv();
        if ($env === 'production') {
            BootpayApi::setConfiguration(
                self::PG_PROD_APPLICATION_ID,
                self::PG_PROD_PRIVATE_KEY,
                'production'
            );
        } else {
            BootpayApi::setConfiguration(
                self::PG_DEV_APPLICATION_ID,
                self::PG_DEV_PRIVATE_KEY,
                'development'
            );
        }
    }

    /**
     * Configure PG API and get access token
     */
    protected static function setupPgApiWithToken(): void
    {
        self::setupPgApi();
        $response = BootpayApi::getAccessToken();
        if (isset($response->error_code)) {
            throw new \RuntimeException('Failed to get PG access token: ' . ($response->message ?? 'unknown error'));
        }
    }

    /**
     * Create and configure Commerce API instance
     */
    protected static function createCommerceApi(): BootpayCommerceApi
    {
        $env = self::getEnv();
        if ($env === 'production') {
            return new BootpayCommerceApi(
                self::COMMERCE_PROD_CLIENT_KEY,
                self::COMMERCE_PROD_SECRET_KEY,
                'production'
            );
        } else {
            return new BootpayCommerceApi(
                self::COMMERCE_DEV_CLIENT_KEY,
                self::COMMERCE_DEV_SECRET_KEY,
                'development'
            );
        }
    }

    /**
     * Create Commerce API instance with token
     */
    protected static function createCommerceApiWithToken(): BootpayCommerceApi
    {
        $api = self::createCommerceApi();
        $response = $api->getAccessToken();
        if (!$api->hasToken()) {
            throw new \RuntimeException('Failed to get Commerce access token');
        }
        return $api;
    }

    /**
     * Print response for debugging
     */
    protected function printResponse($label, $response): void
    {
        echo "\n[$label] " . json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
    }
}
