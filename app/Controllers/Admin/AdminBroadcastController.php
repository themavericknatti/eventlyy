<?php

namespace App\Controllers\Admin;

use Ace\Application;
use Ace\Controller;
use Ace\Request;
use App\Middlewares\AdminMiddleware;
use App\Services\AdminBroadcastService;

class AdminBroadcastController extends Controller
{
    private AdminBroadcastService $broadcastService;

    public function __construct()
    {
        $this->registerMiddleware(new AdminMiddleware());
        $this->broadcastService = new AdminBroadcastService();
    }

    public function index()
    {
        $page = (int)($_GET['page'] ?? 1);
        if ($page < 1) $page = 1;
        $history = $this->broadcastService->getBroadcastHistory($page);

        return $this->render('admin/communications', [
            'title' => 'Communications Center',
            'history' => $history,
        ]);
    }

    public function send(Request $request)
    {
        $user = Application::$app->user;
        $body = $request->getBody();

        $audienceType = trim($body['audience_type'] ?? '');
        $subject = trim($body['subject'] ?? '');
        $message = trim($body['message'] ?? '');
        $ctaText = trim($body['cta_text'] ?? '');
        $ctaUrl = trim($body['cta_url'] ?? '');
        $priority = trim($body['priority'] ?? 'normal');
        $recipientEmail = trim($body['recipient_email'] ?? '');

        // Validation
        if (empty($audienceType) || empty($subject) || empty($message)) {
            Application::$app->session->setFlash('error', 'Audience, subject, and message are required.');
            return back();
        }

        $validAudiences = ['all_users', 'all_hosts', 'single_host', 'single_user'];
        if (!in_array($audienceType, $validAudiences)) {
            Application::$app->session->setFlash('error', 'Invalid audience type selected.');
            return back();
        }

        if (in_array($audienceType, ['single_host', 'single_user']) && empty($recipientEmail)) {
            Application::$app->session->setFlash('error', 'Please specify a recipient email for individual messages.');
            return back();
        }

        $result = $this->broadcastService->sendBroadcast(
            $user->id,
            $audienceType,
            $subject,
            $message,
            $ctaText,
            $ctaUrl,
            $priority,
            $recipientEmail ?: null
        );

        if ($result['sent'] > 0) {
            Application::$app->session->setFlash('success', "Successfully sent {$result['sent']} email(s)!" . ($result['failed'] > 0 ? " ({$result['failed']} failed)" : ''));
        } else {
            Application::$app->session->setFlash('error', 'Failed to send emails. Please check your mail configuration and try again.');
        }

        return back();
    }

    public function searchRecipients(Request $request)
    {
        $query = trim($_GET['q'] ?? '');
        $type = trim($_GET['type'] ?? 'all');

        if (strlen($query) < 2) {
            header('Content-Type: application/json');
            echo json_encode([]);
            exit;
        }

        $results = $this->broadcastService->searchRecipients($query, $type);
        header('Content-Type: application/json');
        echo json_encode($results);
        exit;
    }
}
