<?php
  
  return [
    'login' => $_ENV['FIB_BASE_URL'] . '/auth/realms/fib-online-shop/protocol/openid-connect/token',
    'base_url' => $_ENV['FIB_BASE_URL'] . '/protected/v1',
    'grant' => $_ENV['FIB_GRANT_TYPE'] ?? 'client_credentials',
    'refundable_for' => $_ENV['FIB_REFUNDABLE_FOR'] ?? 'P7D',
    'currency' => $_ENV['FIB_CURRENCY'] ?? 'IQD',
    'callback' => $_ENV['FIB_CALLBACK_URL'],
    'auth_account' => $_ENV['FIB_ACCOUNT'] ?? 'default',
    'clients' => [
      'default' => [
        'client_id' => $_ENV['FIB_CLIENT_ID'],
        'secret' => $_ENV['FIB_CLIENT_SECRET'],
      ],
    ],
  ];
