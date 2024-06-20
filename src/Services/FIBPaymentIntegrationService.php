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
    
    public function __construct(
      FIBAuthIntegrationService $fibAuthIntegrationService
    )
    {
      $this->fibAuthIntegrationService = $fibAuthIntegrationService;
      $this->config = require __DIR__ . '/../config/fib.php';
      $this->baseUrl = $this->config['base_url'];
    }
    
    /**
     * @throws Exception
     */
    private function postRequest(string $url, array $data=[]): ?ResponseInterface
    {
      $token = $this->fibAuthIntegrationService->getToken();
      $httpClient = new Client();
      
      $attempt = 0;
      $maxAttempts = 3;
      $retryDelay = 100; // milliseconds
      
      while ($attempt < $maxAttempts) {
        try {
          $response = $httpClient->post($url, [
            'headers' => [
              'Authorization' => 'Bearer ' . $token,
              'Content-Type' => 'application/json',
            ],
            'json' => $data,
            'verify' => false,
          ]);
          
          // Check if the request was successful
          if (in_array($response->getStatusCode(), [200, 201])) {
            return $response;
          }


          // Retry if unsuccessful (you may adjust retry conditions)
          $attempt++;
          usleep($retryDelay * 1000); // Delay in milliseconds before retrying
          
        } catch (GuzzleException $e) {
          // Log error if request fails
          error_log('Failed to post request to FIB Payment API. URL: ' . $url . ', Data: ' . json_encode($data) . ', Error: ' . $e->getMessage());
          
          // Throw an exception or handle retry logic as needed
          throw new Exception('Failed to post request due to: ' . $e->getMessage());
        }
      }
      
      // If all attempts fail, log the response body and return null or handle accordingly
      error_log('Failed to post request after ' . $maxAttempts . ' attempts. URL: ' . $url . ', Data: ' . json_encode($data));
      
      return null; // Or throw an exception, handle error condition as per your requirement
    }
    
    /**
     * @throws Exception
     */
    private function getRequest(string $url)
    {
      $token = $this->fibAuthIntegrationService->getToken();
      $httpClient = new Client();
      
      $attempt = 0;
      $maxAttempts = 3;
      $retryDelay = 100; // milliseconds
      
      while ($attempt < $maxAttempts) {
        try {
          $response = $httpClient->get($url, [
            'headers' => [
              'Authorization' => 'Bearer ' . $token,
            ],
            'verify' => false,
          ]);
          
          // Check if the request was successful
          if ($response->getStatusCode() === 200) {
            return json_decode($response->getBody()->getContents(), true);
          }
          
          // Retry if unsuccessful (you may adjust retry conditions)
          $attempt++;
          usleep($retryDelay * 1000); // Delay in milliseconds before retrying
          
        } catch (GuzzleException $e) {
          // Log error if request fails
          error_log('Failed to get request from FIB Payment API. URL: ' . $url . ', Error: ' . $e->getMessage());
          
          // Throw an exception or handle retry logic as needed
          throw new Exception('Failed to get request due to: ' . $e->getMessage());
        }
      }
      
      // If all attempts fail, log the response body and return null or handle accordingly
      error_log('Failed to get request after ' . $maxAttempts . ' attempts. URL: ' . $url);
      
      return null; // Or throw an exception, handle error condition as per your requirement
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
      return $this->getRequest( "{$this->baseUrl}/payments/{$paymentId}/status")['status'];
    }
    
    public function handleCallback(string $paymentId,  string $status): void
    {
      #TODO:: handle the callback implementation
    }
    
    public function getPaymentData(int $amount, string $callback = null, $description = null): array
    {
      return [
        'monetaryValue' => [
          'amount' => $amount,
          'currency' => $this->config['currency'],
        ],
        'statusCallbackUrl' => $callback ?? $this->config['callback'],
        'description' => $description?? '',
        'refundableFor' => $this->config['refundable_for'],
      ];
    }
    
    /**
     * @throws Exception
     */
    public function refund(string $paymentId): ?ResponseInterface
    {
      return  $this->postRequest("{$this->baseUrl}/payments/{$paymentId}/refund");
    }
    /**
     * @throws Exception
     */
    public function cancel(string $paymentId): ?ResponseInterface
    {
      return  $this->postRequest("{$this->baseUrl}/payments/{$paymentId}/cancel");
    }
  }
