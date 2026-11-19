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
        $queueStats = \Ace\EmailQueue::getStatus();

        $prefill = [
            'to' => trim($_GET['to'] ?? ''),
            'name' => trim($_GET['name'] ?? ''),
            'subject' => trim($_GET['subject'] ?? ''),
            'inquiry_id' => (int)($_GET['inquiry_id'] ?? 0),
        ];

        return $this->render('admin/communications', [
            'title' => 'Communications Center',
            'history' => $history,
            'queueStats' => $queueStats,
            'prefill' => $prefill,
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
        $inquiryId = (int)($body['inquiry_id'] ?? 0);

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

        if ($result['total'] === 0) {
            Application::$app->session->setFlash('warning', $result['error'] ?? 'No active recipients found for the selected audience.');
        } elseif (($result['queued'] ?? 0) > 0 || ($result['sent'] ?? 0) > 0) {
            $count = $result['queued'] ?? $result['sent'];
            $msg = "🚀 Successfully queued {$count} email(s) into the background delivery queue!";
            if (!empty($result['failed']) && $result['failed'] > 0) {
                $msg .= " ({$result['failed']} invalid recipients skipped)";
            }

            // If replying to a support inquiry, resolve the inquiry
            if ($inquiryId > 0) {
                try {
                    $db = Application::$app->db->pdo;
                    $updateStmt = $db->prepare("UPDATE support_inquiries SET status = 'resolved' WHERE id = :id");
                    $updateStmt->execute(['id' => $inquiryId]);
                    $msg .= " Support Inquiry #{$inquiryId} marked as resolved.";
                } catch (\Throwable $e) {}

                Application::$app->session->setFlash('success', $msg);
                return redirect('/admin/support');
            }

            Application::$app->session->setFlash('success', $msg);
        } else {
            $errDetail = !empty($result['error']) ? ': ' . $result['error'] : '. Please check your configuration and try again.';
            Application::$app->session->setFlash('error', 'Failed to queue broadcast emails' . $errDetail);
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
