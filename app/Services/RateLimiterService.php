<?php

namespace App\Services;

use Ace\Application;

class RateLimiterService
{
    public const MAX_ATTEMPTS = 5;
    public const LOCKOUT_MINUTES = 15;

    /**
     * Get the client IP address.
     */
    public static function getClientIp(): string
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        }
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            return trim($ips[0]);
        }
        return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    }

    /**
     * Check if an IP / email combination is currently locked out.
     * Returns remaining lockout seconds if locked, or 0 if not locked.
     */
    public function getRemainingLockoutSeconds(string $ip, string $email): int
    {
        $db = Application::$app->db->pdo;

        // Cleanup attempts older than 1 hour where locked_until is past
        $db->exec("DELETE FROM login_attempts WHERE locked_until < NOW() - INTERVAL 1 HOUR OR (locked_until IS NULL AND last_attempt_at < NOW() - INTERVAL 30 MINUTE)");

        $stmt = $db->prepare("
            SELECT locked_until 
            FROM login_attempts 
            WHERE (ip_address = :ip OR email = :email) 
              AND locked_until > NOW() 
            ORDER BY locked_until DESC 
            LIMIT 1
        ");
        $stmt->execute(['ip' => $ip, 'email' => strtolower(trim($email))]);
        $lockedUntilStr = $stmt->fetchColumn();

        if ($lockedUntilStr) {
            $remaining = strtotime($lockedUntilStr) - time();
            return max(0, $remaining);
        }

        return 0;
    }

    /**
     * Record a failed login attempt.
     */
    public function hit(string $ip, string $email): array
    {
        $db = Application::$app->db->pdo;
        $cleanEmail = strtolower(trim($email));

        $stmt = $db->prepare("
            SELECT id, attempts, locked_until 
            FROM login_attempts 
            WHERE ip_address = :ip AND email = :email 
            LIMIT 1
        ");
        $stmt->execute(['ip' => $ip, 'email' => $cleanEmail]);
        $record = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($record) {
            $newAttempts = (int)$record['attempts'] + 1;
            $lockedUntil = null;

            if ($newAttempts >= self::MAX_ATTEMPTS) {
                $lockedUntil = date('Y-m-d H:i:s', time() + (self::LOCKOUT_MINUTES * 60));
            }

            $update = $db->prepare("
                UPDATE login_attempts 
                SET attempts = :attempts, 
                    last_attempt_at = NOW(), 
                    locked_until = :locked_until 
                WHERE id = :id
            ");
            $update->execute([
                'attempts' => $newAttempts,
                'locked_until' => $lockedUntil,
                'id' => $record['id']
            ]);

            return [
                'attempts' => $newAttempts,
                'locked' => $newAttempts >= self::MAX_ATTEMPTS,
                'remaining_seconds' => $newAttempts >= self::MAX_ATTEMPTS ? (self::LOCKOUT_MINUTES * 60) : 0
            ];
        } else {
            $insert = $db->prepare("
                INSERT INTO login_attempts (ip_address, email, attempts, last_attempt_at) 
                VALUES (:ip, :email, 1, NOW())
            ");
            $insert->execute(['ip' => $ip, 'email' => $cleanEmail]);

            return [
                'attempts' => 1,
                'locked' => false,
                'remaining_seconds' => 0
            ];
        }
    }

    /**
     * Clear failed attempts for a given IP and email after successful authentication.
     */
    public function clear(string $ip, string $email): void
    {
        $db = Application::$app->db->pdo;
        $stmt = $db->prepare("DELETE FROM login_attempts WHERE ip_address = :ip OR email = :email");
        $stmt->execute(['ip' => $ip, 'email' => strtolower(trim($email))]);
    }
}
