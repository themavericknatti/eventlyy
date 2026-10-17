<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Payment Gateway Driver
    |--------------------------------------------------------------------------
    |
    | Supported: "paystack", "flutterwave", "stripe", "crypto"
    |
    */
    'default' => $_ENV['PAYMENT_DEFAULT_DRIVER'] ?? 'paystack',

    /*
    |--------------------------------------------------------------------------
    | Payment Gateways Configuration
    |--------------------------------------------------------------------------
    */
    'gateways' => [
        'paystack' => [
            'public_key'  => $_ENV['PAYSTACK_PUBLIC_KEY'] ?? '',
            'secret_key'  => $_ENV['PAYSTACK_SECRET_KEY'] ?? '',
            'currency'    => $_ENV['PAYSTACK_CURRENCY'] ?? 'NGN',
            'base_url'    => 'https://api.paystack.co',
        ],

        'flutterwave' => [
            'public_key'  => $_ENV['FLW_PUBLIC_KEY'] ?? '',
            'secret_key'  => $_ENV['FLW_SECRET_KEY'] ?? '',
            'enc_key'     => $_ENV['FLW_ENCRYPTION_KEY'] ?? '',
            'currency'    => $_ENV['FLW_CURRENCY'] ?? 'NGN',
            'base_url'    => 'https://api.flutterwave.com/v3',
        ],

        'stripe' => [
            'public_key'  => $_ENV['STRIPE_PUBLIC_KEY'] ?? '',
            'secret_key'  => $_ENV['STRIPE_SECRET_KEY'] ?? '',
            'webhook_secret' => $_ENV['STRIPE_WEBHOOK_SECRET'] ?? '',
            'currency'    => $_ENV['STRIPE_CURRENCY'] ?? 'usd',
            'base_url'    => 'https://api.stripe.com/v1',
        ],

        'crypto' => [
            // Provider: "nowpayments", "coinbase", or generic webhook
            'provider'       => $_ENV['CRYPTO_PROVIDER'] ?? 'nowpayments',
            'api_key'        => $_ENV['CRYPTO_API_KEY'] ?? '',
            'ipn_secret'     => $_ENV['CRYPTO_IPN_SECRET'] ?? '',
            'currency'       => $_ENV['CRYPTO_DEFAULT_COIN'] ?? 'USDTTRC20', // or BTC, ETH, SOL, etc.
            'fiat_currency'  => $_ENV['CRYPTO_FIAT_CURRENCY'] ?? 'USD',
            'base_url'       => 'https://api.nowpayments.io/v1',
            'sandbox'        => (bool)($_ENV['CRYPTO_SANDBOX'] ?? false),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Idempotency Settings for Payments
    |--------------------------------------------------------------------------
    |
    | When enabled, payment initializations, charge captures, and webhook
    | processing automatically record idempotency keys to prevent duplicate
    | billing or multi-crediting accounts.
    |
    */
    'idempotency' => [
        'enabled' => true,
        'prefix'  => 'pay_idemp_',
        'ttl'     => 86400, // 24 hours
    ],
];
