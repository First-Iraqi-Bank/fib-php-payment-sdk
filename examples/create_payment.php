<?php
  require_once __DIR__ . '/../vendor/autoload.php';
  
  use Dotenv\Dotenv;
  use FirstIraqiBank\FIBPaymentSDK\Services\FIBAuthIntegrationService;
  use FirstIraqiBank\FIBPaymentSDK\Services\FIBPaymentIntegrationService;

// Load environment variables from the .env file
  $dotenv = Dotenv::createImmutable(__DIR__.'/..');
  $dotenv->load();

// Function to create a payment and return its ID
  function createPayment(): string
  {
    // Initialize the authentication service
    $authService = new FIBAuthIntegrationService();
    
    // Initialize the payment integration service
    $paymentService = new FIBPaymentIntegrationService($authService);
    
    try {
      // Create a new payment
      $paymentResponse = $paymentService->createPayment(1000, 'http://localhost/callback', 'Test payment description');
      $paymentData = json_decode($paymentResponse->getBody(), true);
      
      // This should typically be saved in a database or cache for real implementations
      // Example: $databaseService->storePaymentDetails($paymentData);
      
      // Return the payment ID
      return $paymentData['paymentId'];
    } catch (Exception $e) {
      // Handle any errors
      throw new Exception("Error creating payment: " . $e->getMessage());
    }
  }
