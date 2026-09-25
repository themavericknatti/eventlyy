<?php

namespace App\Middlewares;

use Ace\Application;
use Ace\Middleware;

/**
 * Convenience middleware that restricts access to admin users only.
 * Uses the RBAC system under the hood via User::hasRole('admin').
 */
class AdminMiddleware extends Middleware
{
    protected function run(): void
    {
        if (Application::isGuest()) {
            Application::$app->session->setFlash('error', 'Please login to access this page.');
            Application::$app->response->redirect('/login');
            exit;
        }

        $user = Application::$app->user;
        $isAdmin = false;

        if (method_exists($user, 'hasRole')) {
            $isAdmin = $user->hasRole('admin');
        } elseif (isset($user->role)) {
            $isAdmin = $user->role === 'admin';
        }

        if (!$isAdmin) {
            Application::$app->session->setFlash('error', 'You do not have permission to access this area.');
            Application::$app->response->redirect('/');
            exit;
        }

        // if (!Application::$app->user->hasRole('admin')) {
        //     throw new \Exception("Forbidden. Admin access required.", 403);
        // }
    }
}

