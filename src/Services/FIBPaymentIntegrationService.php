<?php

namespace FirstIraqiBank\FIBPaymentSDK\Services;

use FirstIraqiBank\FIBPaymentSDK\Services\Contracts\FIBPaymentIntegrationServiceInterface;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;

class FIBPaymentIntegrationService implements FIBPaymentIntegrationServiceInterface
{
    protected string $baseUrl;
    protected array $config;
    private FIBAuthIntegrationService $fibAuthIntegrationService;
    private Client $httpClient;
    private int $maxAttempts = 3;
    private int $retryDelay = 100; // milliseconds

    public function __construct(FIBAuthIntegrationService $fibAuthIntegrationService)
    {
        $this->fibAuthIntegrationService = $fibAuthIntegrationService;
        $this->config = require __DIR__ . '/../config/fib.php';
        $this->baseUrl = $this->config['base_url'];
        $this->httpClient = new Client();
    }

    /**
     * @throws Exception
     */
    private function request(string $method, string $url, array $data = []): ?ResponseInterface
    {
        $token = $this->fibAuthIntegrationService->getToken();


        for ($attempt = 0; $attempt < $this->maxAttempts; $attempt++) {
            try {
                $options = [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $token,
                        'Content-Type' => 'application/json',
                    ],
                    'verify' => false,
                ];

                if ($method === 'POST') {
                    $options['json'] = $data;
                }

                $response = $this->httpClient->request($method, $url, $options);

                if (in_array($response->getStatusCode(), [200, 201 , 202 , 204])) {
                    return $response;
                }

                usleep($this->retryDelay * 1000); // Delay in milliseconds before retrying
            } catch (GuzzleException $e) {
                error_log("Failed to {$method} request to FIB Payment API. URL: $url, Data: " . json_encode($data) . ", Error: " . $e->getMessage());
                throw new Exception("Failed to {$method} request due to: " . $e->getMessage());
            }
        }

        error_log("Failed to {$method} request after {$this->maxAttempts} attempts. URL: $url, Data: " . json_encode($data));
        return null; // Or throw an exception, handle error condition as per your requirement
    }

    /**
     * @throws Exception
     */
    private function getRequest(string $url)
    {
        $response = $this->request('GET', $url);
        return $response ? json_decode($response->getBody()->getContents(), true) : null;
    }

    /**
     * @throws Exception
     */
    private function postRequest(string $url, array $data = []): ?ResponseInterface
    {
        return $this->request('POST', $url, $data);
    }

    /**
     * @throws Exception
     */
    public function createPayment(int $amount, $callback = null, $description = null): ?ResponseInterface
    {
        $data = $this->getPaymentData($amount, $callback, $description);
        return $this->postRequest("{$this->baseUrl}/payments", $data);
    }

    /**
     * @throws Exception
     */
    public function checkPaymentStatus($paymentId)
    {
        return $this->getRequest("{$this->baseUrl}/payments/{$paymentId}/status")['status'];
    }

    public function handleCallback(string $paymentId, string $status): void
    {
        // TODO: handle the callback implementation
    }

    public function getPaymentData(int $amount, string $callback = null, $description = null): array
    {
        return [
            'monetaryValue' => [
                'amount' => $amount,
                'currency' => $this->config['currency'],
            ],
            'statusCallbackUrl' => $callback ?? $this->config['callback'],
            'description' => $description ?? '',
            'refundableFor' => $this->config['refundable_for'],
        ];
    }

    /**
     * @throws Exception
     */
    public function refund(string $paymentId): ?ResponseInterface
    {
        return $this->postRequest("{$this->baseUrl}/payments/{$paymentId}/refund");
    }

    /**
     * @throws Exception
     */
    public function cancel(string $paymentId): ?ResponseInterface
    {
        return $this->postRequest("{$this->baseUrl}/payments/{$paymentId}/cancel");
    }
}

