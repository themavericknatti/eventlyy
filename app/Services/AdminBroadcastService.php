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
                $recipientEmail = trim($recipientEmail);

                // 1. Check registered users
                $stmt = $db->prepare("SELECT email, name FROM users WHERE email = :email AND status = 'active' LIMIT 1");
                $stmt->execute(['email' => $recipientEmail]);
                $row = $stmt->fetch(\PDO::FETCH_ASSOC);
                if ($row) {
                    return [$row];
                }

                // 2. Check support inquiries (e.g. guests / inquiries)
                try {
                    $stmtSupport = $db->prepare("SELECT email, name FROM support_inquiries WHERE email = :email ORDER BY id DESC LIMIT 1");
                    $stmtSupport->execute(['email' => $recipientEmail]);
                    $supportRow = $stmtSupport->fetch(\PDO::FETCH_ASSOC);
                    if ($supportRow && !empty($supportRow['name'])) {
                        return [$supportRow];
                    }
                } catch (\Throwable $e) {}

                // 3. Check event tickets / attendees
                try {
                    $stmtTicket = $db->prepare("SELECT attendee_email as email, attendee_name as name FROM event_tickets WHERE attendee_email = :email ORDER BY id DESC LIMIT 1");
                    $stmtTicket->execute(['email' => $recipientEmail]);
                    $ticketRow = $stmtTicket->fetch(\PDO::FETCH_ASSOC);
                    if ($ticketRow && !empty($ticketRow['name'])) {
                        return [$ticketRow];
                    }
                } catch (\Throwable $e) {}

                // 4. Fallback for any valid email address
                if (filter_var($recipientEmail, FILTER_VALIDATE_EMAIL)) {
                    return [[
                        'email' => $recipientEmail,
                        'name' => 'Valued Customer',
                    ]];
                }

                return [];

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

        // Build unique queue reference and determine queue priority
        $reference = 'BROADCAST-' . $adminId . '-' . time();
        $queuePriority = match (strtolower($priority)) {
            'urgent', 'high' => 2,
            'important' => 5,
            'low' => 20,
            default => 10,
        };

        // Prepare message payloads for the asynchronous background EmailQueue
        $messages = [];
        $validRecipients = [];
        foreach ($recipients as $recipient) {
            $email = trim($recipient['email'] ?? '');
            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                continue;
            }
            $name = $recipient['name'] ?? 'User';
            $validRecipients[] = $recipient;

            $messages[] = [
                'to' => $email,
                'name' => $name,
                'subject' => $subject,
                'view' => 'emails/admin_broadcast',
                'params' => [
                    'recipientName' => $name,
                    'subject' => $subject,
                    'messageBody' => $message,
                    'ctaText' => $ctaText,
                    'ctaUrl' => $ctaUrl,
                    'priority' => $priority,
                    'siteName' => $siteName,
                ],
            ];
        }

        // Batch queue all messages in email_queue table
        $queuedCount = 0;
        if (!empty($messages)) {
            $queuedCount = \Ace\EmailQueue::queueMessages($messages, $reference, $queuePriority);
        }

        // Prepare insert statement for broadcast history logs
        $stmt = $db->prepare("
            INSERT INTO admin_broadcast_logs 
            (admin_id, audience_type, recipient_count, recipient_email, recipient_name, subject, message, cta_text, cta_url, priority, status, sent_at)
            VALUES (:admin_id, :audience_type, :recipient_count, :email, :name, :subject, :message, :cta_text, :cta_url, :priority, :status, NOW())
        ");

        // Log each recipient outcome to database
        foreach ($recipients as $recipient) {
            $email = $recipient['email'];
            $isValid = filter_var($email, FILTER_VALIDATE_EMAIL);
            $status = $isValid ? 'queued' : 'failed';

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

        // Trigger immediate background worker dispatch after HTTP response is flushed
        if ($queuedCount > 0) {
            \Ace\EmailQueue::processAfterResponse(100);
        }

        return [
            'total' => $totalRecipients,
            'queued' => $queuedCount,
            'sent' => $queuedCount,
            'failed' => max(0, $totalRecipients - $queuedCount),
            'reference' => $reference,
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
            // Search active users as well as recent support inquiry contacts
            try {
                $stmt = $db->prepare("
                    SELECT email, name FROM (
                        SELECT email, name FROM users 
                        WHERE status = 'active' AND (name LIKE :q1 OR email LIKE :q2)
                        UNION
                        SELECT email, name FROM support_inquiries
                        WHERE (name LIKE :q3 OR email LIKE :q4)
                    ) AS combined
                    WHERE email IS NOT NULL AND email != ''
                    GROUP BY email
                    ORDER BY name ASC 
                    LIMIT 10
                ");
                $stmt->execute([
                    'q1' => $searchTerm,
                    'q2' => $searchTerm,
                    'q3' => $searchTerm,
                    'q4' => $searchTerm,
                ]);
            } catch (\Throwable $e) {
                $stmt = $db->prepare("
                    SELECT email, name FROM users 
                    WHERE status = 'active' AND (name LIKE :q1 OR email LIKE :q2)
                    ORDER BY name ASC LIMIT 10
                ");
                $stmt->execute(['q1' => $searchTerm, 'q2' => $searchTerm]);
            }
        }

        return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
    }
}
