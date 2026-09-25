<?php

namespace App\Middlewares;

use Ace\Application;
use Ace\Middleware;

/**
 * SuperAdminMiddleware
 *
 * Restricts access strictly to users with the 'super-admin' role.
 */
class SuperAdminMiddleware extends Middleware
{
    protected function run(): void
    {
        if (Application::isGuest()) {
            Application::$app->session->setFlash('error', 'Please login to access this page.');
            Application::$app->response->redirect('/login');
            exit;
        }

        $user = Application::$app->user;

        if (!$user || !method_exists($user, 'isSuperAdmin') || !$user->isSuperAdmin()) {
            throw new \Exception("Forbidden. Super Admin access required.", 403);
        }
    }
}
