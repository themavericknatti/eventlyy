<?php

namespace App\Middlewares;

use Ace\Application;
use Ace\Middleware;

/**
 * Convenience middleware that restricts access to host users only.
 * Uses the RBAC system under the hood via User::hasRole('event-organizer').
 * Also verifies that the user's email address has been verified before hosting events.
 */
class HostMiddleware extends Middleware
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
        $isHost = false;

        if (method_exists($user, 'isAdmin') && $user->isAdmin()) {
            $isAdmin = true;
        } elseif (method_exists($user, 'hasRole') && $user->hasRole('admin')) {
            $isAdmin = true;
        }

        if (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) {
            $isAdmin = true;
        } elseif (method_exists($user, 'hasRole') && $user->hasRole('super-admin')) {
            $isAdmin = true;
        }

        if ($isAdmin) {
            $isHost = true;
        } elseif (method_exists($user, 'isHost')) {
            $isHost = $user->isHost();
        } elseif (method_exists($user, 'hasRole')) {
            $isHost = $user->hasRole('event-organizer');
        } elseif (isset($user->role)) {
            $isHost = in_array($user->role, ['event-organizer', 'host', 'admin', 'super-admin']);
        }

        if (!$isHost) {
            Application::$app->session->setFlash('error', 'You do not have permission to access the host dashboard. Please register as an event organizer to get started.');
            Application::$app->response->redirect('/');
            exit;
        }

        // Email Verification Enforcement (Bypassed for Admins/Super Admins)
        if (!$isAdmin && method_exists($user, 'isVerified') && !$user->isVerified()) {
            // If they are attempting to create or edit an event, block and request email verification
            $currentUri = Application::$app->request->getPath();
            if (str_contains($currentUri, '/host/event/create') || str_contains($currentUri, '/host/event/edit')) {
                Application::$app->session->setFlash('error', 'Please verify your email address before creating or editing events. Check your inbox for the verification link.');
                Application::$app->response->redirect('/resend-verification');
                exit;
            }
        }
    }
}
