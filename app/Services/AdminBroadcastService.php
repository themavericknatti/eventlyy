<?php

namespace App\Services;

use Ace\Application;
use Ace\Logger;
use Ace\Mail;

class AdminBroadcastService
{
    /**
     * Get recipients based on audience type.
     * @param string $audienceType One of: all_users, all_hosts, single_host, single_user
     * @param string|null $recipientEmail Required for single_host / single_user
     * @return array Array of ['email' => ..., 'name' => ...]
     */
    public function getRecipients(string $audienceType, ?string $recipientEmail = null): array
    {
        $db = Application::$app->db->pdo;

        switch ($audienceType) {
            case 'all_users':
                $stmt = $db->query("SELECT email, name FROM users WHERE status = 'active' ORDER BY name ASC");
                return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];

            case 'all_hosts':
                $stmt = $db->query("
                    SELECT DISTINCT u.email, u.name 
                    FROM users u 
                    LEFT JOIN user_roles ur ON u.id = ur.user_id 
                    WHERE u.status = 'active' 
                    AND (ur.role_id = 3 OR u.id IN (SELECT DISTINCT organizer_id FROM events))
                    ORDER BY u.name ASC
                ");
                return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];

            case 'single_host':
            case 'single_user':
                if (empty($recipientEmail)) return [];
                $stmt = $db->prepare("SELECT email, name FROM users WHERE email = :email AND status = 'active' LIMIT 1");
                $stmt->execute(['email' => $recipientEmail]);
                $row = $stmt->fetch(\PDO::FETCH_ASSOC);
                return $row ? [$row] : [];

            default:
                return [];
        }
    }

    /**
     * Send broadcast and log each message.
     */
    public function sendBroadcast(
        int $adminId,
        string $audienceType,
        string $subject,
        string $message,
        string $ctaText = '',
        string $ctaUrl = '',
        string $priority = 'normal',
        ?string $recipientEmail = null
    ): array {
        // Prevent timeout during long-running broadcast email sends in production
        @set_time_limit(0);
        @ini_set('max_execution_time', '0');

        $recipients = $this->getRecipients($audienceType, $recipientEmail);
        $totalRecipients = count($recipients);

        if ($totalRecipients === 0) {
            return [
                'total' => 0,
                'sent' => 0,
                'failed' => 0,
                'error' => 'No active recipients found for the selected audience.',
            ];
        }

        $siteName = site_setting('site_title', 'Eventlyy');
        $db = Application::$app->db->pdo;

        // Prepare insert statement once outside the loop for high performance
        $stmt = $db->prepare("
            INSERT INTO admin_broadcast_logs 
            (admin_id, audience_type, recipient_count, recipient_email, recipient_name, subject, message, cta_text, cta_url, priority, status, sent_at)
            VALUES (:admin_id, :audience_type, :recipient_count, :email, :name, :subject, :message, :cta_text, :cta_url, :priority, :status, NOW())
        ");

        $batchResult = Mail::sendBatch(
            $recipients,
            $subject,
            'emails/admin_broadcast',
            function (array $recipient) use ($subject, $message, $ctaText, $ctaUrl, $priority, $siteName) {
                return [
                    'recipientName' => $recipient['name'] ?? 'User',
                    'subject' => $subject,
                    'messageBody' => $message,
                    'ctaText' => $ctaText,
                    'ctaUrl' => $ctaUrl,
                    'priority' => $priority,
                    'siteName' => $siteName,
                ];
            }
        );

        $sentCount = $batchResult['sent'];
        $failedCount = $batchResult['failed'];
        $errors = $batchResult['errors'] ?? [];

        // Log each recipient outcome to database
        foreach ($recipients as $recipient) {
            $email = $recipient['email'];
            $isFailed = isset($errors[$email]);
            $status = $isFailed ? 'failed' : 'sent';

            try {
                $stmt->execute([
                    'admin_id' => $adminId,
                    'audience_type' => $audienceType,
                    'recipient_count' => $totalRecipients,
                    'email' => $email,
                    'name' => $recipient['name'] ?? '',
                    'subject' => $subject,
                    'message' => $message,
                    'cta_text' => $ctaText,
                    'cta_url' => $ctaUrl,
                    'priority' => $priority,
                    'status' => $status,
                ]);
            } catch (\Throwable $e) {
                Logger::error("Failed to write broadcast log for {$email}: {$e->getMessage()}");
            }
        }

        return [
            'total' => $totalRecipients,
            'sent' => $sentCount,
            'failed' => $failedCount,
            'last_error' => Mail::getLastError(),
        ];
    }

    /**
     * Get broadcast history with pagination.
     */
    public function getBroadcastHistory(int $page = 1, int $limit = 20): array
    {
        $db = Application::$app->db->pdo;
        $offset = ($page - 1) * $limit;

        $stmt = $db->prepare("
            SELECT 
                bl.id,
                bl.admin_id,
                u.name as admin_name,
                bl.audience_type,
                bl.recipient_count,
                bl.recipient_email,
                bl.recipient_name,
                bl.subject,
                bl.message,
                bl.cta_text,
                bl.cta_url,
                bl.priority,
                bl.status,
                bl.sent_at
            FROM admin_broadcast_logs bl
            LEFT JOIN users u ON bl.admin_id = u.id
            ORDER BY bl.sent_at DESC
            LIMIT :lim OFFSET :off
        ");
        $stmt->bindValue(':lim', $limit, \PDO::PARAM_INT);
        $stmt->bindValue(':off', $offset, \PDO::PARAM_INT);
        $stmt->execute();
        $items = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];

        $countStmt = $db->query("SELECT COUNT(*) FROM admin_broadcast_logs");
        $total = (int)$countStmt->fetchColumn();

        return [
            'items' => $items,
            'total' => $total,
            'totalPages' => max(1, ceil($total / $limit)),
            'page' => $page,
        ];
    }

    /**
     * Search users/hosts for autocomplete.
     */
    public function searchRecipients(string $query, string $type = 'all'): array
    {
        $db = Application::$app->db->pdo;
        $searchTerm = "%" . $query . "%";

        if ($type === 'host') {
            $stmt = $db->prepare("
                SELECT DISTINCT u.email, u.name 
                FROM users u 
                LEFT JOIN user_roles ur ON u.id = ur.user_id 
                WHERE u.status = 'active' 
                AND (ur.role_id = 3 OR u.id IN (SELECT DISTINCT organizer_id FROM events))
                AND (u.name LIKE :q1 OR u.email LIKE :q2)
                ORDER BY u.name ASC
                LIMIT 10
            ");
            $stmt->execute(['q1' => $searchTerm, 'q2' => $searchTerm]);
        } else {
            $stmt = $db->prepare("
                SELECT email, name FROM users 
                WHERE status = 'active' AND (name LIKE :q1 OR email LIKE :q2)
                ORDER BY name ASC LIMIT 10
            ");
            $stmt->execute(['q1' => $searchTerm, 'q2' => $searchTerm]);
        }

        return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
    }
}
