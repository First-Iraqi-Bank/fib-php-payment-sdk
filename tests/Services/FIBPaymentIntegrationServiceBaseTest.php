<?php

namespace FirstIraqiBank\FIBPaymentSDK\Tests\Services;

use FirstIraqiBank\FIBPaymentSDK\Services\FIBAuthIntegrationService;
use FirstIraqiBank\FIBPaymentSDK\Services\FIBPaymentIntegrationService;

use FirstIraqiBank\FIBPaymentSDK\Tests\BaseTestCase;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use ReflectionClass;

class FIBPaymentIntegrationServiceBaseTest extends BaseTestCase
{
    public function setUp(): void
    {
        parent::setUp();
    }

    private function createMockAuthService(): FIBAuthIntegrationService
    {
        $mockAuth = $this->createMock(FIBAuthIntegrationService::class);
        $mockAuth->method('getToken')->willReturn('fake-access-token');
        return $mockAuth;
    }

    private function createMockHttpClient(int $statusCode, array $responseBody): Client
    {
        $mockClient = $this->createMock(Client::class);
        $mockClient->method('request')->willReturn(new Response($statusCode, [], json_encode($responseBody)));
        return $mockClient;
    }

    private function setPrivateProperty($object, string $property, $value): void
    {
        $reflection = new ReflectionClass($object);
        $prop = $reflection->getProperty($property);
        $prop->setAccessible(true);
        $prop->setValue($object, $value);
    }

    public function test_create_payment()
    {
        $mockAuth = $this->createMockAuthService();
        $mockClient = $this->createMockHttpClient(201, ['status' => 'created']);

        $service = new FIBPaymentIntegrationService($mockAuth);
        $this->setPrivateProperty($service, 'httpClient', $mockClient);

        $response = $service->createPayment(1000, 'https://example.com/callback', 'Test payment');

        $this->assertEquals(201, $response->getStatusCode());
    }
  
  public function test_check_payment_status()
  {
    $mockAuth = $this->createMockAuthService();
    $mockClient = $this->createMockHttpClient(200, ['status' => 'paid']);
    
    $service = new FIBPaymentIntegrationService($mockAuth);
    $this->setPrivateProperty($service, 'httpClient', $mockClient);
    
    $response = $service->checkPaymentStatus('payment_id');
    $status = $response['status'] ?? null;
    
    $this->assertEquals('paid', $status);
  }


    public function test_refund()
    {
        $mockAuth = $this->createMockAuthService();
        $mockClient = $this->createMockHttpClient(200, ['status' => 'refunded']);

        $service = new FIBPaymentIntegrationService($mockAuth);
        $this->setPrivateProperty($service, 'httpClient', $mockClient);

        $response = $service->refund('payment_id');
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_cancel()
    {
        $mockAuth = $this->createMockAuthService();
        $mockClient = $this->createMockHttpClient(200, ['status' => 'canceled']);

        $service = new FIBPaymentIntegrationService($mockAuth);
        $this->setPrivateProperty($service, 'httpClient', $mockClient);

        $response = $service->cancel('payment_id');
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_handle_bad_request()
    {
        $mockAuth = $this->createMockAuthService();
        $mockClient = $this->createMockHttpClient(400, ['error' => 'Invalid request']);

        $service = new FIBPaymentIntegrationService($mockAuth);
        $this->setPrivateProperty($service, 'httpClient', $mockClient);

        $response = $service->createPayment(1000, 'https://example.com/callback', 'Test payment');
        $statusCode = $response['status_code'] ?? null;
        $message = $response['message']['error'] ?? null;

        $this->assertEquals(400, $statusCode);
        $this->assertEquals('Invalid request', $message);
    }

    public function test_handle_retry_failure()
    {
        $mockAuth = $this->createMockAuthService();
        $mockClient = $this->createMock(Client::class);
        $mockClient->method('request')->willThrowException(new \GuzzleHttp\Exception\RequestException('Request failed', new \GuzzleHttp\Psr7\Request('POST', 'test')));

        $service = new FIBPaymentIntegrationService($mockAuth);
        $this->setPrivateProperty($service, 'httpClient', $mockClient);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Failed to POST request due to: Request failed');

        $service->createPayment(1000, 'https://example.com/callback', 'Test payment');
    }

    public function test_handle_max_attempts_failure()
    {
        $mockAuth = $this->createMockAuthService();
        $mockClient = $this->createMock(Client::class);
        $mockClient->method('request')->willReturn(new Response(500));

        $service = new FIBPaymentIntegrationService($mockAuth);
        $this->setPrivateProperty($service, 'httpClient', $mockClient);

        $response = $service->createPayment(1000, 'https://example.com/callback', 'Test payment');

        $this->assertNull($response);
    }

    public function test_handle_callback()
    {
        $mockAuth = $this->createMockAuthService();
        $mockClient = $this->createMockHttpClient(200, ['status' => 'success']);

        $service = new FIBPaymentIntegrationService($mockAuth);
        $this->setPrivateProperty($service, 'httpClient', $mockClient);

        $service->handleCallback('payment_id', 'success');

        // Assert that handleCallback does not throw any exception
        $this->assertTrue(true);
    }

}
