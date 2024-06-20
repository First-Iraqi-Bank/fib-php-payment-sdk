<?php
  
  require_once __DIR__ . '/vendor/autoload.php';
  
  use Dotenv\Dotenv;
  use FirstIraqiBank\FIBPaymentSDK\FIBAuthIntegrationService;

// Specify the directory where the .env file is located
  $dotenv = Dotenv::createImmutable(__DIR__);

// Load environment variables from the .env file
  $dotenv->load();

  
// Instantiate the service with the loaded configuration
  $authService = new FIBAuthIntegrationService();
  
  try {
    // Call the getToken() method from your service
    $token = $authService->getToken();
    echo "Access Token: " . $token;
  } catch (Exception $e) {
    echo "Error: " . $e->getMessage();
  }
