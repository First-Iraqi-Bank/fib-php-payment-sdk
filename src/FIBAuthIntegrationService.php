<?php
  
  namespace FirstIraqiBank\FIBPaymentSDK;
  
  use Exception;
  use GuzzleHttp\Client;
  use Monolog\Handler\StreamHandler;
  use Monolog\Logger;
  
  class FIBAuthIntegrationService
  {
    protected string $account;
    protected array $config;
    protected Logger $logger;
    protected Client $httpClient;
    
    public function __construct()
    {
      // Load configuration
      $config = require __DIR__ . '/config/fib.php';
      $this->account = $config['auth_account'] ?? 'default';
      $this->config = $config;
      $this->logger = new Logger('fib');
      $this->logger->pushHandler(new StreamHandler(__DIR__ . '/../logs/fib.log', Logger::ERROR));
      $this->httpClient = new Client();
    }
    
    /**
     * @throws Exception
     */
    public function getToken(): string
    {
      
      try {
        $response = $this->retry(function () {
          return $this->httpClient->post($this->config['login'], [
            'auth' => [
              $this->config[$this->account]['client_id'],
              $this->config[$this->account]['secret'],
            ],
            'form_params' => [
              'grant_type' => $this->config['grant'],
            ],
            'verify' => false,
          ]);
        });
        
        $responseBody = json_decode($response->getBody(), true);
        
        if ($response->getStatusCode() === 200 && isset($responseBody['access_token'])) {
          return $responseBody['access_token'];
        }
        
        $this->logger->error('Failed to retrieve access token from FIB Payment API.', [
          'response' => $response->getBody()->getContents(),
        ]);
        throw new Exception('Failed to retrieve access token.');
      } catch (Exception $e) {
        $this->logger->error('Error occurred while retrieving access token from FIB Payment API.', [
          'message' => $e->getMessage(),
          'trace' => $e->getTraceAsString(),
        ]);
        throw $e;
      }
    }
    
    /**
     * @throws Exception
     */
    private function retry(callable $callback)
    {
      $maxAttempts = 3;
      $attempt = 0;
      $lastException = null;
      
      while ($attempt < $maxAttempts) {
        try {
          return $callback();
        } catch (Exception $e) {
          $lastException = $e;
          $attempt++;
          
          if ($attempt < $maxAttempts) {
            usleep(100 * 1000); // Delay in milliseconds before retrying
          }
        }
      }
      
      // If all attempts fail, throw the last caught exception
      if ($lastException !== null) {
        throw $lastException;
      } else {
        throw new Exception("Retry function failed without catching any exceptions.");
      }
    }
    
    
  }
