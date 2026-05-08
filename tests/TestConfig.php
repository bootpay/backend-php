<?php

namespace Bootpay\ServerPhp\Test;

use PHPUnit\Framework\TestCase;
use Bootpay\ServerPhp\BootpayApi;
use Bootpay\ServerPhp\BootpayCommerceApi;

/**
 * Base test class for Bootpay SDK integration tests.
 *
 * Reads BOOTPAY_ENV env var (default: "production") to select keys.
 */

function loadBootpayDotEnv() {
    foreach ([__DIR__ . '/../.env', __DIR__ . '/.env'] as $file) {
        if (!file_exists($file)) continue;
        foreach (file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            $line = trim($line);
            if ($line === '' || strpos($line, '#') === 0 || strpos($line, '=') === false) continue;
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            $value = trim($value, '"\'');
            if (getenv($key) === false) putenv($key . '=' . $value);
        }
    }
}

function bootpayEnv($key, $fallback) {
    $value = getenv($key);
    return $value === false || $value === '' ? $fallback : $value;
}

loadBootpayDotEnv();

class TestConfig extends TestCase
{
    // PG/Commerce API keys 는 .env / 환경변수 로 주입한다 (.env.example 참고)
    // 새 ck/sk 와 별개로 legacy application_id/private_key 도 .env 로 노출하여 두 인증 모드를 모두 테스트한다.

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
        return getenv('BOOTPAY_ENV') ?: 'production';
    }

    /**
     * Get active PG auth mode: 'new' (ck/sk) or 'legacy' (application_id/private_key).
     * 매 실행 시 BOOTPAY_AUTH_MODE 환경변수로 토글한다.
     */
    protected static function getAuthMode(): string
    {
        $mode = strtolower(getenv('BOOTPAY_AUTH_MODE') ?: 'new');
        return $mode === '' ? 'new' : $mode;
    }

    /**
     * Configure PG API with appropriate keys (read from .env / environment variables).
     * BOOTPAY_AUTH_MODE 에 따라 ck/sk (default) 또는 legacy application_id/private_key 사용.
     */
    protected static function setupPgApi(): void
    {
        if (self::getAuthMode() === 'legacy') {
            self::setupPgApiLegacy();
            return;
        }
        $env = self::getEnv();
        echo "[BOOTPAY_AUTH_MODE=new] PG: client_key/secret_key (Basic Auth) | env={$env}" . PHP_EOL;
        if ($env === 'production') {
            BootpayApi::setClientKeyConfiguration(
                bootpayEnv('BOOTPAY_PG_CLIENT_KEY_PROD', ''),
                bootpayEnv('BOOTPAY_PG_SECRET_KEY_PROD', ''),
                'production'
            );
        } else {
            BootpayApi::setClientKeyConfiguration(
                bootpayEnv('BOOTPAY_PG_CLIENT_KEY_DEV', ''),
                bootpayEnv('BOOTPAY_PG_SECRET_KEY_DEV', ''),
                'development'
            );
        }
    }

    /**
     * Configure PG API and get access token if needed.
     * legacy 모드에서만 실제 토큰 발급. ck/sk 모드에서는 setupPgApi 만 호출 (Basic Auth 가 매 요청 처리).
     */
    protected static function setupPgApiWithToken(): void
    {
        self::setupPgApi();
        if (self::getAuthMode() === 'legacy') {
            $response = BootpayApi::getAccessToken();
            if (isset($response->error_code)) {
                throw new \RuntimeException('Failed to get PG access token: ' . ($response->message ?? 'unknown error'));
            }
        }
    }

    /**
     * Configure PG API with legacy application_id/private_key (호환성 검증용)
     */
    protected static function setupPgApiLegacy(): void
    {
        $env = self::getEnv();
        echo "[BOOTPAY_AUTH_MODE=legacy] PG: application_id/private_key (Bearer) | env={$env}" . PHP_EOL;
        if ($env === 'production') {
            BootpayApi::setConfiguration(
                bootpayEnv('BOOTPAY_PG_APPLICATION_ID_PROD', ''),
                bootpayEnv('BOOTPAY_PG_PRIVATE_KEY_PROD', ''),
                'production'
            );
        } else {
            BootpayApi::setConfiguration(
                bootpayEnv('BOOTPAY_PG_APPLICATION_ID_DEV', ''),
                bootpayEnv('BOOTPAY_PG_PRIVATE_KEY_DEV', ''),
                'development'
            );
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
                bootpayEnv('BOOTPAY_COMMERCE_CLIENT_KEY_PROD', ''),
                bootpayEnv('BOOTPAY_COMMERCE_SECRET_KEY_PROD', ''),
                'production'
            );
        } else {
            return new BootpayCommerceApi(
                bootpayEnv('BOOTPAY_COMMERCE_CLIENT_KEY_DEV', ''),
                bootpayEnv('BOOTPAY_COMMERCE_SECRET_KEY_DEV', ''),
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
