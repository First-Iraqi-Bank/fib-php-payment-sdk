<?php
  
  namespace FirstIraqiBank\FIBPaymentSDK\Services;
  
  use Exception;
  use GuzzleHttp\Client;
  use GuzzleHttp\Exception\GuzzleException;
  use Monolog\Handler\StreamHandler;
  use Monolog\Logger;
  use Psr\Http\Message\ResponseInterface;
  
  class FIBAuthIntegrationService
  {
    protected array $account;
    protected array $config;
    protected Logger $logger;
    protected Client $httpClient;
    
    public function __construct(array $config = null)
    {
      // Load configuration
      $this->config = $config ?? $this->loadConfig();
      $this->account = $this->config['clients'][$this->config['auth_account'] ?? 'default'];
      $this->logger = $this->initializeLogger();
      $this->httpClient = new Client();
    }
    
    protected function loadConfig(): array
    {
      return require __DIR__ . '/../config/fib.php';
    }
    
    protected function initializeLogger(): Logger
    {
      $logger = new Logger('fib');
      $logger->pushHandler(new StreamHandler(__DIR__ . '/../logs/fib.log', Logger::ERROR));
      return $logger;
    }
    
    /**
     * @throws Exception
     */
    public function getToken(): string
    {
      try {
        $response = $this->retryRequest(fn() => $this->requestAccessToken());
        $responseBody = json_decode($response->getBody(), true);
        
        if ($response->getStatusCode() === 200 && isset($responseBody['access_token'])) {
          return $responseBody['access_token'];
        }
        
        $this->logError('Failed to retrieve access token from FIB Payment API.', $response->getBody()->getContents());
        throw new Exception('Failed to retrieve access token.');
      } catch (Exception $e) {
        $this->logException('Error occurred while retrieving access token from FIB Payment API.', $e);
        throw $e;
      }
    }
    
    /**
     * @throws GuzzleException
     */
    protected function requestAccessToken(): ResponseInterface
    {
      return $this->httpClient->post($this->config['login'], [
        'auth' => [
          $this->account['client_id'],
          $this->account['secret'],
        ],
        'form_params' => [
          'grant_type' => $this->config['grant'],
        ],
        'verify' => false,
      ]);
    }
    
    /**
     * @throws Exception
     * @return ResponseInterface|mixed
     */
    protected function retryRequest(callable $callback, int $maxAttempts = 3, int $delay = 100)
    {
      $attempt = 0;
      $lastException = null;
      
      while ($attempt < $maxAttempts) {
        try {
          return $callback();
        } catch (Exception $e) {
          $lastException = $e;
          $attempt++;
          if ($attempt < $maxAttempts) {
            usleep($delay * 1000); // Delay in milliseconds before retrying
          }
        }
      }
      
      throw $lastException ?? new Exception("Retry function failed without catching any exceptions.");
    }
    
    protected function logError(string $message, string $responseContent): void
    {
      $this->logger->error($message, ['response' => $responseContent]);
    }
    
    protected function logException(string $message, Exception $exception): void
    {
      $this->logger->error($message, [
        'message' => $exception->getMessage(),
        'trace' => $exception->getTraceAsString(),
      ]);
    }
  }
