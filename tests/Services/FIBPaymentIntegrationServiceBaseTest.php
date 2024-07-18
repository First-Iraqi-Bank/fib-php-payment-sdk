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
}
