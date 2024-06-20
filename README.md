
# FIB Payment SDK

The FIB Payment SDK provides seamless integration with the FIB payment system, empowering developers to streamline payment transactions and facilitate secure refunds within their applications.

## Features

- **Payment Transactions**: Enable users to make payments securely through the FIB payment system, handling transactions effortlessly within your application.
- **Refund Processing**: Process refunds securely through the FIB payment system, managing transactions efficiently within your application.

## Installation

To integrate the SDK into your project, install it via Composer:

```bash
composer require First-Iraqi-Bank/fib-php-payment-sdk
```

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


### Usage the SDK

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

    // #TODO: Store the payment details in a database or cache. These details will be used for other functionalities such as handling callback URLs, checking payment status, and canceling payments.
    // Refund Payment
    // Example: Save payment details in a database or cache for later use
    // You can use any storage method such as a relational database, NoSQL database, or an in-memory cache
    
    // Return the payment details to the end user to proceed with the payment
    return $paymentDetails;
    }  catch (Exception $e) {
        echo "Error: " . $e->getMessage();
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
    $paymentStatus = $paymentService->checkPaymentStatus($paymentId);
    echo "Payment Status: " . $paymentStatus;
```
#### Refunding a Payment
To refund a payment, use the refund method. This method also requires the paymentId.

```php
    $refundResponse = $paymentService->refund($paymentId);
```

#### Cancelling a Payment
To cancel a payment, use the cancel method. This method requires the paymentId.

```php
    $cancelResponse = $paymentService->cancel($paymentId);
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
phpunit
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

We use SemVer for versioning. For the versions available, see the tags on this repository.

### FAQ

#### Q: How do I get an API key for the FIB Payment system?

A: Please contact our support team at support@fib-payment.com to request an API key.

#### Q: Can I use this SDK in a production environment?

A: Yes, the SDK is designed for use in production environments, but please ensure you have configured it correctly and have got the necessary credentials.
