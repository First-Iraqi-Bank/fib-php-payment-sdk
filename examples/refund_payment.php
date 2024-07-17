<?php
  require_once __DIR__ . '/../vendor/autoload.php';
  require_once __DIR__ . '/create_payment.php'; // Include the file with createPayment() function
  
  use Dotenv\Dotenv;
  use FirstIraqiBank\FIBPaymentSDK\Services\FIBAuthIntegrationService;
  use FirstIraqiBank\FIBPaymentSDK\Services\FIBPaymentIntegrationService;

// Load environment variables from the .env file
  $dotenv = Dotenv::createImmutable(__DIR__.'/..');
  $dotenv->load();
  
  try {
    // Get the payment ID
    $paymentId = createPayment();
    
    // This $paymentId should be retrieved from your database or cache in real implementations
    // Example: $paymentId = $databaseService->retrievePaymentId();
    
    // Initialize the authentication service
    $authService = new FIBAuthIntegrationService();
    
    // Initialize the payment integration service
    $paymentService = new FIBPaymentIntegrationService($authService);
    
    // Refund Payment
    $response = $paymentService->refund($paymentId);
    echo "Refund Payment Status: " . $response['status_code'] . PHP_EOL;

    
    // Example: update payment details in a database or cache for real implementations
    // $databaseService->updatePaymentDetails($paymentDetails);
    
  } catch (Exception $e) {
    echo "Error Refunding payment: " . $e->getMessage() . PHP_EOL;
  }
