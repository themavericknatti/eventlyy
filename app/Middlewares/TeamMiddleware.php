<?php

namespace App\Middlewares;

use Ace\Application;
use Ace\Middleware;

/**
 * Middleware that restricts access to authenticated team staff only.
 * Denies access to workstation task pages if no active staff session exists.
 */
class TeamMiddleware extends Middleware
{
    protected function run(): void
    {
        $staff = Application::$app->session->get('team_staff');
        $event = Application::$app->session->get('team_event');

        if (!$staff || !$event) {
            Application::$app->session->setFlash('error', 'Access denied. Please log in to access the staff workstation.');
            Application::$app->response->redirect('/teams/login');
            exit;
        }

        // Verify staff member is still active in database
        $db = Application::$app->db->pdo;
        $stmt = $db->prepare("SELECT status FROM event_staff WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $staff['id']]);
        $status = $stmt->fetchColumn();

        if ($status !== 'active') {
            Application::$app->session->remove('team_staff');
            Application::$app->session->remove('team_event');
            Application::$app->session->remove('team_role');
            Application::$app->session->setFlash('error', 'Access denied. Your staff account has been deactivated.');
            Application::$app->response->redirect('/teams/login');
            exit;
        }
    }
}
