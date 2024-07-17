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
     * Makes a request to the given URL with the specified method and data.
     *
     * @param string $method
     * @param string $url
     * @param array $data
     * @return array|null|ResponseInterface
     * @throws Exception
     */
    private function request(string $method, string $url, array $data = [])
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
                    'http_errors' => false
                ];

                if ($method === 'POST') {
                    $options['json'] = $data;
                }

                $response = $this->httpClient->request($method, $url, $options);

                if (in_array($response->getStatusCode(), [200, 201, 202, 204])) {
                    return $response;
                }

                if (in_array($response->getStatusCode(), [400, 402 , 406])) {
                    return [
                        'status_code' => $response->getStatusCode(),
                        'message' => json_decode($response->getBody()->getContents(), true)
                    ];
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
     * Makes a GET request to the given URL.
     *
     * @param string $url
     * @return array|null
     * @throws Exception
     */
    private function getRequest(string $url): ?array
    {
        $response = $this->request('GET', $url);
        return $response ? json_decode($response->getBody()->getContents(), true) : null;
    }

    /**
     * Makes a POST request to the given URL with the specified data.
     *
     * @param string $url
     * @param array $data
     * @return array|null|ResponseInterface
     * @throws Exception
     */
    private function postRequest(string $url, array $data = [])
    {
        return $this->request('POST', $url, $data);
    }

    /**
     * Creates a payment with the given amount, callback URL, and description.
     *
     * @param int $amount
     * @param mixed $callback
     * @param mixed $description
     * @return array|null|ResponseInterface
     * @throws Exception
     */
    public function createPayment(int $amount, $callback = null, $description = null)
    {
        $data = $this->getPaymentData($amount, $callback, $description);
        return $this->postRequest("{$this->baseUrl}/payments", $data);
    }

    /**
     * Checks the status of a payment with the given ID.
     *
     * @param mixed $paymentId
     * @return array|null
     * @throws Exception
     */
    public function checkPaymentStatus($paymentId): ?array
    {
        return $this->getRequest("{$this->baseUrl}/payments/{$paymentId}/status");
    }

    /**
     * Handles the callback for a payment.
     *
     * @param string $paymentId
     * @param string $status
     */
    public function handleCallback(string $paymentId, string $status): void
    {
        // TODO: handle the callback implementation
    }

    /**
     * Gets the payment data with the given amount, callback URL, and description.
     *
     * @param int $amount
     * @param mixed $callback
     * @param mixed $description
     * @return array
     */
    public function getPaymentData(int $amount, $callback = null, $description = null): array
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
     * Refunds a payment with the given ID.
     *
     * @param string $paymentId
     * @return array|null|ResponseInterface
     * @throws Exception
     */
    public function refund(string $paymentId)
    {
        return $this->postRequest("{$this->baseUrl}/payments/{$paymentId}/refund");
    }

    /**
     * Cancels a payment with the given ID.
     *
     * @param string $paymentId
     * @return array|null|ResponseInterface
     * @throws Exception
     */
    public function cancel(string $paymentId)
    {
        return $this->postRequest("{$this->baseUrl}/payments/{$paymentId}/cancel");
    }
}
