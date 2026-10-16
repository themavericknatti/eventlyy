<?php

/**
 * CORS Configuration
 *
 * Controls Cross-Origin Resource Sharing for API routes.
 * Frontend apps (React, Next.js, Nuxt.js, Vue) use these headers
 * to communicate with your Ace backend API.
 *
 * Quick Setup:
 *   1. Add your frontend URL to 'allowed_origins'
 *   2. Set CORS_ALLOWED_ORIGINS in .env for environment-specific overrides
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Allowed Origins
    |--------------------------------------------------------------------------
    | Origins that are allowed to make cross-origin requests.
    | Use ['*'] to allow all origins (not recommended for production).
    | Use specific URLs like ['http://localhost:3000', 'https://myapp.com']
    |
    */
    'allowed_origins' => array_map('trim', explode(',',
        $_ENV['CORS_ALLOWED_ORIGINS'] ?? 'http://localhost:3000,http://localhost:5173,http://localhost:8080'
    )),

    /*
    |--------------------------------------------------------------------------
    | Allowed HTTP Methods
    |--------------------------------------------------------------------------
    */
    'allowed_methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],

    /*
    |--------------------------------------------------------------------------
    | Allowed Headers
    |--------------------------------------------------------------------------
    | Headers the client is allowed to send.
    |
    */
    'allowed_headers' => [
        'Content-Type',
        'Authorization',
        'X-Api-Key',
        'X-Requested-With',
        'Accept',
        'Origin',
    ],

    /*
    |--------------------------------------------------------------------------
    | Exposed Headers
    |--------------------------------------------------------------------------
    | Headers that the browser is allowed to read from the response.
    |
    */
    'exposed_headers' => [
        'X-Request-Id',
        'X-RateLimit-Limit',
        'X-RateLimit-Remaining',
    ],

    /*
    |--------------------------------------------------------------------------
    | Max Age (seconds)
    |--------------------------------------------------------------------------
    | How long preflight results can be cached by the browser.
    |
    */
    'max_age' => 86400,

    /*
    |--------------------------------------------------------------------------
    | Allow Credentials
    |--------------------------------------------------------------------------
    | Set to true if your API uses cookies/sessions for cross-origin requests.
    | Note: When true, allowed_origins cannot be ['*'].
    |
    */
    'allow_credentials' => false,
];
