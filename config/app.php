<?php

return [
    'name' => $_ENV['APP_NAME'] ?? 'Ace Application',
    'env' => $_ENV['APP_ENV'] ?? 'local',
    'url' => $_ENV['APP_URL'] ?? 'http://localhost:8000',
    'timezone' => $_ENV['APP_TIMEZONE'] ?? 'UTC',
    
    // Frontend CSS Framework: 'tailwind', 'bootstrap', or 'none' (Zero npm required, instant CDN)
    'css_framework' => $_ENV['CSS_FRAMEWORK'] ?? 'tailwind',
    
    // Auth configuration
    'userClass' => \App\Models\User::class,
];

