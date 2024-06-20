<?php
  
  namespace FirstIraqiBank\FIBPaymentSDK\Services\Contracts;
  
  interface FIBPaymentIntegrationServiceInterface
  {
    public function createPayment(int $amount, $callback, $description);
    public function checkPaymentStatus($paymentId);
    public function handleCallback(string $paymentId,  string $status);
    public function refund(string $paymentId);
    public function cancel(string $paymentId);
    
  }
