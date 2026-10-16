<?php

/**
 * API Routes
 *
 * All routes defined here are automatically:
 *   - Prefixed with /api
 *   - Protected by CORS middleware
 *   - Protected by API key authentication middleware
 *   - Free from CSRF verification
 *   - Stateless (no session)
 *
 * Usage:
 *   $router->get('/users', [App\Controllers\Api\UserController::class, 'index']);
 *   // Resolves to: GET /api/users
 *
 * Versioning:
 *   $router->group('/v1', function($router) {
 *       $router->get('/users', [App\Controllers\Api\V1\UserController::class, 'index']);
 *   });
 *   // Resolves to: GET /api/v1/users
 */

/** @var \Ace\Router $router */

// Health check endpoint (no authentication required — defined outside the api group middleware)
// Access: GET /api/health
$router->get('/health', function() {
    return json_encode([
        'status'  => 'ok',
        'message' => 'API is running',
        'time'    => date('Y-m-d H:i:s'),
        'version' => '1.0.0',
    ]);
});
