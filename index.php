<?php
  
  require_once __DIR__ . '/vendor/autoload.php';
  
  use Dotenv\Dotenv;
  use FirstIraqiBank\FIBPaymentSDK\Services\FIBAuthIntegrationService;
  use FirstIraqiBank\FIBPaymentSDK\Services\FIBPaymentIntegrationService;

// Load environment variables from the .env file
  $dotenv = Dotenv::createImmutable(__DIR__);
  $dotenv->load();

// Initialize the authentication service
  $authService = new FIBAuthIntegrationService();

// Initialize the payment integration service
  $paymentService = new FIBPaymentIntegrationService($authService);
  
  try {
    // Create a new payment
    $paymentResponse = $paymentService->createPayment(1000, 'http://localhost/callback', 'Test payment description');
    // Extract payment details from the response
    $paymentData = json_decode($paymentResponse->getBody(), true);
    
    // Payment details (example using an associative array)
    $paymentDetails = [
      'fib_payment_id' => $paymentData['paymentId'],
      'readable_code' => $paymentData['readableCode'],
      'personal_app_link' => $paymentData['personalAppLink'],
      'valid_until' => $paymentData['validUntil'],
    ];

//    var_dump($paymentDetails);
    // #TODO: Store the payment details in a database or cache. These details will be used for other functionalities.
    // Refund Payment
    // Example: Save payment details in a database or cache for later use
    // You can use any storage method such as a relational database, NoSQL database, or an in-memory cache
    
    echo "Payment Details:" . PHP_EOL ;
    echo "FIB Payment ID: " . $paymentDetails['fib_payment_id'] . PHP_EOL;
    echo "Readable Code: " . $paymentDetails['readable_code'] . PHP_EOL;
    echo "Personal App Link: " . $paymentDetails['personal_app_link'] . PHP_EOL;
    echo "Valid Until: " . $paymentDetails['valid_until'] . PHP_EOL;
    echo  PHP_EOL . PHP_EOL . PHP_EOL . PHP_EOL . PHP_EOL . PHP_EOL . PHP_EOL;
    
    
    // Check Payment Status
    $paymentId = $paymentDetails['fib_payment_id'];
    $status = $paymentService->checkPaymentStatus($paymentId);
    echo "Payment Status: " . $status . PHP_EOL;
    echo  PHP_EOL . PHP_EOL . PHP_EOL . PHP_EOL . PHP_EOL . PHP_EOL . PHP_EOL;
    
//     Refund Payment
    $refundResponse = $paymentService->refund($paymentId);
    echo "Refund Status Code: " . $refundResponse->getStatusCode() . PHP_EOL;
    echo  PHP_EOL . PHP_EOL . PHP_EOL . PHP_EOL . PHP_EOL . PHP_EOL . PHP_EOL;

    
    // Cancel Payment
    $cancelResponse = $paymentService->cancel($paymentId);
    echo "Cancel Status Code: " . $cancelResponse->getStatusCode() . PHP_EOL;
    
    // Handling Payment Callbacks
    // This would typically be implemented in your web application where the callback URL receives POST requests
    // Example:
    // $paymentId = $_POST['id'];
    // $status = $_POST['status'];
    // $paymentService->handleCallback($paymentId, $status);
    
    
    // Return the payment details to the end user to proceed with the payment
//    return $paymentDetails;
    
  } catch (Exception $e) {
    echo "Error: " . $e->getMessage();
  }