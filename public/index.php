<?php

define('ACE_PUBLIC_DIR', __DIR__);
define('ROOT_DIR', dirname(__DIR__));

require_once ROOT_DIR . '/vendor/autoload.php';

use Ace\Application;

$rootDir = ROOT_DIR;

/**
 * Load .env file
 */
$envFile = $rootDir . '/.env';

// Auto-create .env from .env.example on fresh install if missing
if (!file_exists($envFile) && file_exists($rootDir . '/.env.example')) {
    @copy($rootDir . '/.env.example', $envFile);
}

if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lines as $line) {
        $line = trim($line);

        // Skip comments
        if ($line === '' || strpos($line, '#') === 0) {
            continue;
        }

        // Only process KEY=VALUE lines
        if (strpos($line, '=') === false) {
            continue;
        }

        [$key, $value] = explode('=', $line, 2);

        $key = trim($key);
        $value = trim($value);

        // Remove surrounding quotes
        if (
            strlen($value) >= 2 &&
            (
                ($value[0] === '"' && $value[strlen($value) - 1] === '"') ||
                ($value[0] === "'" && $value[strlen($value) - 1] === "'")
            )
        ) {
            $value = substr($value, 1, -1);
        }

        $_ENV[$key] = $value;
        putenv($key . '=' . $value);
    }
}

// Load configurations
$appConfig = require_once $rootDir . '/config/app.php';
$dbConfig = require_once $rootDir . '/config/database.php';

$config = array_merge($appConfig, ['db' => $dbConfig]);

// Instantiate Application
$app = new Application($rootDir, $config);

// Run database migrations (silently skip on initial boot if database is not reachable yet)
if ($app->db) {
    try {
        $app->db->runMigrations();
    } catch (\Throwable $e) {
        // Silently continue so initial static page displays without crashing
    }
}

// Load application routes
$router = $app->router;
require_once $rootDir . '/routes/web.php';

// Load API routes (auto-prefixed with /api, CORS + API key middleware applied)
$apiRoutesFile = $rootDir . '/routes/api.php';
if (file_exists($apiRoutesFile)) {
    $router->group('/api', function ($router) use ($rootDir, $apiRoutesFile) {
        // Apply CORS middleware to all API routes
        $router->group('', function ($router) use ($apiRoutesFile) {
            require_once $apiRoutesFile;
        }, [\Ace\Middlewares\CorsMiddleware::class]);
    });
}

// Run the Application
$app->run();