<?php

namespace FirstIraqiBank\FIBPaymentSDK\Tests\Services;

use Exception;
use FirstIraqiBank\FIBPaymentSDK\Services\FIBAuthIntegrationService;
use FirstIraqiBank\FIBPaymentSDK\Tests\BaseTestCase;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Monolog\Handler\TestHandler;
use Monolog\Logger;
use ReflectionClass;
use ReflectionException;

class FIBAuthIntegrationServiceBaseTest extends BaseTestCase
{
    public function setUp(): void
    {
        parent::setUp();
    }

    private function create_mock_http_client(): Client
    {
        $responseBody = ['access_token' => 'fake-access-token'];
        $mockClient = $this->createMock(Client::class);
        $mockClient->method('post')->willReturn(new Response(200, [], json_encode($responseBody)));
        return $mockClient;
    }

    /**
     * @throws ReflectionException
     */
    private function set_private_property($object, string $property, $value): void
    {
        $reflection = new ReflectionClass($object);
        $propertyReflection = $reflection->getProperty($property);
        $propertyReflection->setAccessible(true);
        $propertyReflection->setValue($object, $value);
    }

    public function test_constructor()
    {
        $service = new FIBAuthIntegrationService();
        $this->assertInstanceOf(FIBAuthIntegrationService::class, $service);
    }

    /**
     * @throws Exception
     */
    public function test_get_token_success()
    {
        $mockClient = $this->create_mock_http_client();
        $service = new FIBAuthIntegrationService();
        $this->set_private_property($service, 'httpClient', $mockClient);

        $token = $service->getToken();
        $this->assertEquals('fake-access-token', $token);
    }

    /**
     * @throws ReflectionException
     */
    public function test_get_token_exception()
    {
        $mockClient = $this->createMock(Client::class);
        $mockClient->method('post')->willThrowException(new RequestException('Error Communicating with Server', new Request('POST', 'test')));
        $service = new FIBAuthIntegrationService();
        $this->set_private_property($service, 'httpClient', $mockClient);

        $this->expectException(Exception::class);
        $service->getToken();
    }

    /**
     * @throws ReflectionException
     */
    public function test_get_token_empty_response()
    {
        $mockClient = $this->createMock(Client::class);
        $mockClient->method('post')->willReturn(new Response(200, [], ''));
        $service = new FIBAuthIntegrationService();
        $this->set_private_property($service, 'httpClient', $mockClient);

        $this->expectException(Exception::class);
        $service->getToken();
    }

    /**
     * @throws ReflectionException
     */
    public function test_retry_mechanism_failure()
    {
        $mockClient = $this->createMock(Client::class);
        $mockClient->method('post')->willReturn(new Response(500, [], ''));
        $service = new FIBAuthIntegrationService();
        $this->set_private_property($service, 'httpClient', $mockClient);

        $this->expectException(Exception::class);
        $service->getToken();
    }

    /**
     * @throws ReflectionException
     */
    public function test_retry_mechanism_max_attempts_reached()
    {
        $mockClient = $this->createMock(Client::class);
        $mockClient->method('post')->willThrowException(new RequestException('Server Error', new Request('POST', 'test')));
        $service = new FIBAuthIntegrationService();
        $this->set_private_property($service, 'httpClient', $mockClient);

        $this->expectException(Exception::class);
        $service->getToken();
    }

    /**
     * @throws ReflectionException
     */
    public function test_logging_failure()
    {
        $mockClient = $this->createMock(Client::class);
        $mockClient->method('post')->willThrowException(new RequestException('Error Communicating with Server', new Request('POST', 'test')));

        $testHandler = new TestHandler();
        $logger = new Logger('test');
        $logger->pushHandler($testHandler);

        $service = new FIBAuthIntegrationService();
        $this->set_private_property($service, 'httpClient', $mockClient);
        $this->set_private_property($service, 'logger', $logger);

        try {
            $service->getToken();
        } catch (Exception $e) {
            // expected exception
        }

        $this->assertTrue($testHandler->hasErrorRecords());
    }
}
