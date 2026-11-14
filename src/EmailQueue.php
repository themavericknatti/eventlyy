<?php

namespace Ace;

class EmailQueue
{
    /**
     * Get the PDO instance from the application.
     */
    private static function pdo(): \PDO
    {
        return Application::$app->db->pdo;
    }

    /**
     * Queue a single email for background delivery.
     * Returns the queue row ID.
     */
    public static function queue(
        string $to,
        string $subject,
        string $view,
        array $params = [],
        ?string $reference = null,
        int $priority = 10
    ): int {
        $pdo = self::pdo();

        // Split "Name <email>" format if provided
        $name = '';
        $email = trim($to);
        if (preg_match('/^(.+?)\s*<([^>]+)>$/', $email, $m)) {
            $name = trim($m[1]);
            $email = trim($m[2]);
        }

        $stmt = $pdo->prepare("
            INSERT INTO email_queue (recipient_email, recipient_name, subject, view_template, view_params, reference, priority)
            VALUES (:email, :name, :subject, :view, :params, :reference, :priority)
        ");
        $stmt->execute([
            ':email'     => $email,
            ':name'      => $name,
            ':subject'   => $subject,
            ':view'      => $view,
            ':params'    => json_encode($params, JSON_UNESCAPED_UNICODE),
            ':reference' => $reference,
            ':priority'  => $priority,
        ]);

        return (int) $pdo->lastInsertId();
    }

    /**
     * Queue multiple emails at once (batch insert).
     *
     * $messages format: [['to'=>..., 'name'=>..., 'subject'=>..., 'view'=>..., 'params'=>[...]], ...]
     * Returns count of queued items.
     */
    public static function queueMessages(array $messages, ?string $reference = null, int $priority = 10): int
    {
        if (empty($messages)) {
            return 0;
        }

        $pdo = self::pdo();
        $count = 0;

        $stmt = $pdo->prepare("
            INSERT INTO email_queue (recipient_email, recipient_name, subject, view_template, view_params, reference, priority)
            VALUES (:email, :name, :subject, :view, :params, :reference, :priority)
        ");

        foreach ($messages as $msg) {
            $to = trim($msg['to'] ?? '');
            $name = trim($msg['name'] ?? '');
            $subject = $msg['subject'] ?? '';
            $view = $msg['view'] ?? '';
            $params = $msg['params'] ?? [];

            if (empty($to) || empty($view)) {
                continue;
            }

            // Handle "Name <email>" format
            if (empty($name) && preg_match('/^(.+?)\s*<([^>]+)>$/', $to, $m)) {
                $name = trim($m[1]);
                $to = trim($m[2]);
            }

            $stmt->execute([
                ':email'     => $to,
                ':name'      => $name,
                ':subject'   => $subject,
                ':view'      => $view,
                ':params'    => json_encode($params, JSON_UNESCAPED_UNICODE),
                ':reference' => $reference,
                ':priority'  => $priority,
            ]);

            $count++;
        }

        return $count;
    }

    /**
     * Process pending emails from the queue (up to $limit).
     * Sends via Mail::send() with retry handling.
     * Marks as 'sent' or 'failed' with last_error.
     *
     * @return array ['processed'=>int, 'sent'=>int, 'failed'=>int]
     */
    public static function processQueue(int $limit = 20): array
    {
        $pdo = self::pdo();
        $result = ['processed' => 0, 'sent' => 0, 'failed' => 0];

        // Claim a batch by updating status to 'processing'
        $claimStmt = $pdo->prepare("
            UPDATE email_queue
            SET status = 'processing', attempts = attempts + 1
            WHERE status = 'pending' AND attempts < max_attempts
            ORDER BY priority ASC, created_at ASC
            LIMIT :lim
        ");
        $claimStmt->bindValue(':lim', $limit, \PDO::PARAM_INT);
        $claimStmt->execute();

        $claimed = $claimStmt->rowCount();
        if ($claimed === 0) {
            return $result;
        }

        // Fetch the claimed rows
        $fetchStmt = $pdo->prepare("
            SELECT id, recipient_email, recipient_name, subject, view_template, view_params
            FROM email_queue
            WHERE status = 'processing'
            ORDER BY priority ASC, created_at ASC
            LIMIT :lim
        ");
        $fetchStmt->bindValue(':lim', $limit, \PDO::PARAM_INT);
        $fetchStmt->execute();

        $rows = $fetchStmt->fetchAll(\PDO::FETCH_ASSOC);

        $successStmt = $pdo->prepare("
            UPDATE email_queue SET status = 'sent', processed_at = NOW(), last_error = NULL WHERE id = :id
        ");
        $failStmt = $pdo->prepare("
            UPDATE email_queue
            SET status = CASE WHEN attempts >= max_attempts THEN 'failed' ELSE 'pending' END,
                last_error = :error
            WHERE id = :id
        ");

        foreach ($rows as $row) {
            $result['processed']++;

            $params = json_decode($row['view_params'], true);
            if (!is_array($params)) {
                $params = [];
            }

            try {
                $sent = Mail::send(
                    $row['recipient_email'],
                    $row['subject'],
                    $row['view_template'],
                    $params
                );

                if ($sent) {
                    $successStmt->execute([':id' => $row['id']]);
                    $result['sent']++;
                } else {
                    $error = Mail::getLastError() ?: 'Mail::send() returned false';
                    $failStmt->execute([':error' => $error, ':id' => $row['id']]);
                    $result['failed']++;
                }
            } catch (\Throwable $e) {
                $failStmt->execute([':error' => $e->getMessage(), ':id' => $row['id']]);
                $result['failed']++;
                error_log("EmailQueue: Failed to send email #{$row['id']}: " . $e->getMessage());
            }
        }

        return $result;
    }

    /**
     * Process queue in the background after PHP output is sent.
     * Uses register_shutdown_function + output buffer flushing.
     * Call this once per request to trigger background processing.
     */
    public static function processAfterResponse(int $limit = 20): void
    {
        static $registered = false;
        if ($registered) {
            return;
        }
        $registered = true;

        register_shutdown_function(function () use ($limit) {
            // Try to close the connection to the client first
            if (function_exists('fastcgi_finish_request')) {
                fastcgi_finish_request();
            } else {
                // Apache/mod_php: flush output and send Connection: close
                if (!headers_sent()) {
                    header('Connection: close');
                }
                // Flush all output buffers
                while (ob_get_level() > 0) {
                    ob_end_flush();
                }
                flush();
            }

            // Now process emails in background
            ignore_user_abort(true);
            set_time_limit(120);

            try {
                self::processQueue($limit);
            } catch (\Throwable $e) {
                error_log("EmailQueue background processing error: " . $e->getMessage());
            }
        });
    }

    /**
     * Get queue stats for a reference.
     *
     * @return array ['total'=>int, 'pending'=>int, 'processing'=>int, 'sent'=>int, 'failed'=>int]
     */
    public static function getStatus(string $reference): array
    {
        $pdo = self::pdo();

        $stmt = $pdo->prepare("
            SELECT
                COUNT(*) as total,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status = 'processing' THEN 1 ELSE 0 END) as processing,
                SUM(CASE WHEN status = 'sent' THEN 1 ELSE 0 END) as sent,
                SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed
            FROM email_queue
            WHERE reference = :ref
        ");
        $stmt->execute([':ref' => $reference]);

        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        return [
            'total'      => (int) ($row['total'] ?? 0),
            'pending'    => (int) ($row['pending'] ?? 0),
            'processing' => (int) ($row['processing'] ?? 0),
            'sent'       => (int) ($row['sent'] ?? 0),
            'failed'     => (int) ($row['failed'] ?? 0),
        ];
    }
}
