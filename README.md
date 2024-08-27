
# FIB Payment SDK

The FIB Payment SDK provides seamless integration with the FIB payment system, empowering developers to streamline payment transactions and facilitate secure refunds within their applications.

# FIB Payment SDK

**Table of Contents**
- [Features](#features)
- [Installation](#installation)
  - [Composer Installation](#composer-installation)
  - [Alternative Installation (Without Composer)](#alternative-installation-without-composer)
- [Configuration](#configuration)
- [Usage](#usage)
  - [Creating a Payment](#creating-a-payment)
  - [Checking Payment Status](#checking-payment-status)
  - [Refunding a Payment](#refunding-a-payment)
  - [Cancelling a Payment](#cancelling-a-payment)
  - [Handling Payment Callbacks](#handling-payment-callbacks)
- [FIB Payment Documentation](#fib-payment-documentation)
- [Testing](#testing)
- [Contributing](#contributing)
- [License](#license)
- [Support](#support)
- [Acknowledgments](#acknowledgments)
- [Versioning](#versioning)
- [FAQ](#faq)


## Features

- **Payment Transactions**: Enable users to make payments securely through the FIB payment system, handling transactions effortlessly within your application.
- **Refund Processing**: Process refunds securely through the FIB payment system, managing transactions efficiently within your application.
- **Payment Status Checking**: Check the status of payments to ensure accurate transaction tracking.
- **Payment Cancellation**: Cancel payments securely through the FIB payment system, providing flexibility and control over payment transactions.


## Installation

To integrate the SDK into your project, install it via Composer:

```bash
composer require First-Iraqi-Bank/fib-php-payment-sdk
```

## Alternative Installation (Without Composer)
If your project prefers not to use Composer for dependency management, you can manually include the FIB Payment SDK by following these steps:

- Clone the Repository: Clone the FIB Payment SDK repository directly into your project directory or any preferred 
  location: 

```bash
 git clone https://github.com/First-Iraqi-Bank/fib-php-payment-sdk.git
```

- Include in Your Project:
Copy or move the cloned fib-php-payment-sdk directory into your project structure. You can place it wherever it suits your project best.

- Autoloading (if applicable):
If your project uses autoloading, ensure that the src directory of the SDK is included in your autoloader configuration. For example, if using PSR-4 autoloading, you might add the following to your composer.json or equivalent autoloading setup:

```json
{
    "autoload": {
        "psr-4": {
            "FIB\\PaymentSDK\\": "path/to/fib-php-payment-sdk/src"
        }
    }
}
```

- Usage: After including the SDK in your project, you can use its classes and functionalities directly in your PHP 
  files.

### Notes
- Ensure that the SDK repository URL (https://github.com/First-Iraqi-Bank/fib-php-payment-sdk.git) is correct and 
accessible.
- Manually managing dependencies may require additional effort to keep the SDK updated with the latest changes and 
  fixes.
- Consider using Composer for managing dependencies whenever possible, as it simplifies dependency management and 
  ensures compatibility with other packages.

### Configuration

To configure the SDK, you need to set the following environment variables:

- `FIB_API_KEY`: Your FIB payment API key.
- `FIB_API_SECRET`: Your FIB payment API secret.
- `FIB_BASE_URL`: The base URL for the FIB payment API (default: https://api.fibpayment.com).
- `FIB_GRANT_TYPE`: The grant type for authentication with the FIB payment API (default: client_credentials).
- `FIB_REFUNDABLE_FOR`: The period for which transactions can be refunded (default: P7D, which stands for 7 days).
- `FIB_CURRENCY`: The currency used for transactions with the FIB payment system (default: IQD).
- `FIB_CALLBACK_URL`: The callback URL for handling payment notifications from the FIB payment system.
- `FIB_ACCOUNT`: The FIB payment account identifier.

Make sure to set these environment variables appropriately in your application's environment configuration.


### Usage of the SDK

Below is a basic example of how to use the SDK:

#### Ensure Dependencies are Installed:
Make sure you have installed all required dependencies using Composer:
```bash
      composer install
```
#### Set Up Environment Variables:
   Create a .env file in the root directory of your project and configure the necessary environment variables. Refer to the .env.example file for the required variables.


#### Create a Payment Example Usage
- To create a payment, use the createPayment method. This method will return the payment details which you can store in a database or cache for later use in other functionalities like callback URL handling, checking payment status, cancelling payment, and refunding payment.

```php
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
        // Payment details (example using an associative array)
        //    $paymentData = [
        //      'fib_payment_id' => $paymentData['paymentId'],
        //      'readable_code' => $paymentData['readableCode'],
        //      'personal_app_link' => $paymentData['personalAppLink'],
        //      'valid_until' => $paymentData['validUntil'],
        //    ];
        // Example: $databaseService->storePaymentDetails($paymentData);
        
        // Return the payment ID
        return $paymentData['paymentId'];
      } catch (Exception $e) {
        // Handle any errors
        throw new Exception("Error creating payment: " . $e->getMessage());
      }
  }

```

- Storing Payment Details: Once you receive the payment details from the createPayment method, you can store them in a database or a cache.
  This allows you to retrieve and use these details for further actions such as checking the payment status, processing refunds, or handling payment callbacks.

    - Database: Save the payment details in a relational database (e.g., MySQL, PostgreSQL) or a NoSQL database (e.g.,
  MongoDB).
    - Cache: Use an in-memory cache (e.g., Redis, Memcached) to store the payment details for quick access.

- Returning Payment Details
After storing the payment details, return them to the end user.
  The returned details include:

    - fib_payment_id: The unique identifier for the payment.
    - readable_code: A readable code for the payment.
    - personal_app_link: A link for the end user to proceed with the payment in the personal app.
    - valid_until: The expiration time for the payment.

By following these steps, you ensure that the payment details are securely stored and easily accessible for further processing.


#### Checking the Payment Status
To check the status of a payment, use the checkPaymentStatus method. This method requires the paymentId which was returned when the payment was created.

```php

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
      $paymentId = retrievePaymentId();    
    // This $paymentId should be retrieved from your database or cache in real implementations
    // Example: $paymentId = $databaseService->retrievePaymentId();
    
    // Initialize the authentication service
    $authService = new FIBAuthIntegrationService();
    
    // Initialize the payment integration service
    $paymentService = new FIBPaymentIntegrationService($authService);
    
    // Check Payment Status
    $response = $paymentService->checkPaymentStatus($paymentId);
    echo "Payment Status: " . $response['status'] ?? null . PHP_EOL;
    
    // Example: Store payment details in a database or cache for real implementations
    // $databaseService->storePaymentDetails($paymentDetails);
    
  } catch (Exception $e) {
    echo "Error checking payment status: " . $e->getMessage() . PHP_EOL;
  }


```
#### Refunding a Payment
To refund a payment, use the refund method. This method also requires the paymentId.

```php

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
    $paymentId = retrievePaymentId();
    
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


```

#### Cancelling a Payment
To cancel a payment, use the cancel method. This method requires the paymentId.

```php

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
    $paymentId = retrievePaymentId();
    
    // This $paymentId should be retrieved from your database or cache in real implementations
    // Example: $paymentId = $databaseService->retrievePaymentId();
    
    // Initialize the authentication service
    $authService = new FIBAuthIntegrationService();
    
    // Initialize the payment integration service
    $paymentService = new FIBPaymentIntegrationService($authService);
    
    // cancel Payment
    $response = $paymentService->cancel($paymentId);
    // Check if the cancellation was successful
    if (in_array($response->getStatusCode(), [200, 201, 202, 204])) {
      echo "Cancel Payment Status: Successful\n";
    } else {
      echo "Cancel Payment Status: Failed with status code " . $response->getStatusCode() . "\n";
    }
    
    
    // Example: update payment details in a database or cache for real implementations
    // $databaseService->updatePaymentDetails($paymentDetails);
    
  } catch (Exception $e) {
    echo "Error Refunding payment: " . $e->getMessage() . PHP_EOL;
  }



```

#### Handling Payment Callbacks
To handle payment callbacks, ensure your application has a POST API or URL that FIB can call to notify your application about payment status updates.

Callback URL Requirements
Your callback URL should be able to handle POST requests with a request body containing two properties:

id: This represents the payment ID associated with the callback.
Status: This indicates the current status of the payment. Refer to the "Check Payment Status" section of this documentation for more details. The status returned should mirror the data structure returned by the check status endpoint.

```php
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

// Define your callback endpoint
$app->post('/callback', function (Request $request, Response $response) {
    $payload = $request->getParsedBody();

    // Validate incoming payload
    $paymentId = $payload['id'] ?? null;
    $status = $payload['status'] ?? null;

    if (!$paymentId || !$status) {
        return $response->withStatus(400)->withJson([
            'error' => 'Invalid callback payload',
        ]);
    }

    // Process the callback
    try {
        $paymentService->handleCallback($paymentId, $status);
        //TODO: Implement your callback handling logic here

        return $response->withJson([
            'message' => 'Callback processed successfully',
        ]);
    } catch (Exception $e) {
        return $response->withStatus(500)->withJson([
            'error' => 'Failed to process callback: ' . $e->getMessage(),
        ]);
    }
});

```
##### Notes
-  /callback with your actual endpoint URL.
- Ensure your callback endpoint is accessible to FIB and handles errors gracefully.
- Implement the handleCallback method in your FIBPaymentIntegrationService class to handle the payment status update internally.

### FIB Payment Documentation

For comprehensive details on FIB Online Payment, please refer to the [full documentation](https://documenter.getpostman.com/view/18377702/UVCB93tc).


### Testing

To ensure the SDK functions correctly, run tests using PHPUnit:

```bash
vendor/bin/phpunit --testdox tests
```

### Contributing

Contributions are welcome! Please read `CONTRIBUTING.md` for details on our code of conduct, and the process for submitting pull requests.

### License

This project is licensed under the MIT License. See the [LICENSE.md](LICENSE.md) file for details.


### Support

For support, please contact support@fib-payment.com or visit our website.

### Acknowledgments

Thanks to the FIB Payment development team for their contributions. This SDK uses the cURL library for API requests.

### Versioning

We use semantic versioning (SemVer) principles for subsequent releases (v0.2.0, v0.3.0, etc.).
For the versions available, see the tags on this repository.



### FAQ

#### Q: How do I get an API key for the FIB Payment system?

A: Please contact our support team at support@fib-payment.com to request an API key.

#### Q: Can I use this SDK in a production environment?

A: Yes, the SDK is designed for use in production environments, but please ensure you have configured it correctly and have got the necessary credentials.

