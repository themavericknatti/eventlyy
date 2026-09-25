<?php

return [
    'name' => $_ENV['APP_NAME'] ?? 'Eventlyy Ticket Master',
    'env' => $_ENV['APP_ENV'] ?? 'production',
    'url' => $_ENV['APP_URL'] ?? 'https://eventlyy.com/',
    'timezone' => $_ENV['APP_TIMEZONE'] ?? 'Africa/Lagos',
    
    // Auth configuration
    'userClass' => \App\Models\User::class,
];

