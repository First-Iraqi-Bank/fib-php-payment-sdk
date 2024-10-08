<?php
  
  namespace FirstIraqiBank\FIBPaymentSDK\Tests\Config;
  
  use FirstIraqiBank\FIBPaymentSDK\Tests\BaseTestCase;
  
  class ConfigBaseTest extends BaseTestCase
  {
    // Define a constant for "https://"
    private const HTTPS_PREFIX = 'https://';
    
    public function setUp(): void
    {
      parent::setUp();
    }
    
    public function test_config_array_has_all_expected_keys()
    {
      // Load the configuration array from fib.php
      $config = require __DIR__ . '/../../src/config/fib.php';
      
      // Verify that $config is an array
      $this->assertTrue(is_array($config), "The configuration should be loaded as an array.");
      
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
      
      // Additional check: Ensure 'clients' contains only 'default' key if necessary
      $this->assertCount(1, $config['clients'], "The 'clients' array should contain only one key, 'default'.");
      
      // Additional check: Validate values if necessary
      $this->assertStringContainsString(self::HTTPS_PREFIX, $config['login'], "The 'login' URL should contain '" . self::HTTPS_PREFIX . "'.");
      $this->assertStringContainsString(self::HTTPS_PREFIX, $config['base_url'], "The 'base_url' should contain '" . self::HTTPS_PREFIX . "'.");
      $this->assertSame('client_credentials', $config['grant'], "The 'grant' type should be 'client_credentials'.");
      $this->assertSame('P7D', $config['refundable_for'], "The 'refundable_for' period should be 'P7D'.");
      $this->assertSame('IQD', $config['currency'], "The 'currency' should be 'IQD'.");
      $this->assertStringContainsString(self::HTTPS_PREFIX, $config['callback'], "The 'callback' URL should contain '" . self::HTTPS_PREFIX . "'.");
      $this->assertSame('default', $config['auth_account'], "The 'auth_account' should be 'default'.");
    }
  }
