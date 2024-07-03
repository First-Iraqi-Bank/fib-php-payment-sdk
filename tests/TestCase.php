<?php

namespace FirstIraqiBank\FIBPaymentSDK\Tests;

class TestCase extends \PHPUnit\Framework\TestCase
{

    public function setUp(): void
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


}