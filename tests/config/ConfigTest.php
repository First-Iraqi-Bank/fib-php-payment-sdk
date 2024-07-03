<?php

namespace FirstIraqiBank\FIBPaymentSDK\Tests\Config;

use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    protected function setUp(): void
    {
        // Set environment variables for testing purposes
        $_ENV['FIB_BASE_URL'] = 'https://example.com';
        $_ENV['FIB_GRANT_TYPE'] = 'client_credentials';
        $_ENV['FIB_REFUNDABLE_FOR'] = 'P7D';
        $_ENV['FIB_CURRENCY'] = 'IQD';
        $_ENV['FIB_CALLBACK_URL'] = 'https://example.com/callback';
        $_ENV['FIB_ACCOUNT'] = 'default';
        $_ENV['FIB_CLIENT_ID'] = 'client_id';
        $_ENV['FIB_CLIENT_SECRET'] = 'client_secret';
    }

    public function test_config_array_has_all_expected_keys()
    {
        // Load the configuration array from fib.php
        $config = require __DIR__ . '/../../src/config/fib.php';

        // Define the expected top-level keys
        $expectedKeys = [
            'login',
            'base_url',
            'grant',
            'refundable_for',
            'currency',
            'callback',
            'auth_account',
            'clients',
        ];

        // Check if all expected keys are present in the config array
        foreach ($expectedKeys as $key) {
            $this->assertArrayHasKey($key, $config, "Key '{$key}' is missing in the config array.");
        }

        // Check if 'clients' array contains the 'default' key
        $this->assertArrayHasKey('default', $config['clients'], "Key 'default' is missing in the 'clients' array.");

        // Check if 'clients.default' contains 'client_id' and 'secret' keys
        $expectedClientKeys = ['client_id', 'secret'];
        foreach ($expectedClientKeys as $key) {
            $this->assertArrayHasKey($key, $config['clients']['default'], "Key '{$key}' is missing in the 'clients.default' array.");
        }

        // Additional check: Ensure 'clients' contains only 'default' key
        $this->assertCount(1, $config['clients'], "The 'clients' array should contain only one key, 'default'.");

        // Additional check: Validate values if necessary
        $this->assertStringContainsString('https://', $config['login'], "The 'login' URL should contain 'https://'.");
        $this->assertStringContainsString('https://', $config['base_url'], "The 'base_url' should contain 'https://'.");
        $this->assertSame('client_credentials', $config['grant'], "The 'grant' type should be 'client_credentials'.");
        $this->assertSame('P7D', $config['refundable_for'], "The 'refundable_for' period should be 'P7D'.");
        $this->assertSame('IQD', $config['currency'], "The 'currency' should be 'IQD'.");
        $this->assertStringContainsString('https://', $config['callback'], "The 'callback' URL should contain 'https://'.");
        $this->assertSame('default', $config['auth_account'], "The 'auth_account' should be 'default'.");
    }
}
