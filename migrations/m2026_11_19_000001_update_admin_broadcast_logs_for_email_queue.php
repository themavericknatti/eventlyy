<?php

use Ace\Migration;

class m2026_11_19_000001_update_admin_broadcast_logs_for_email_queue extends Migration
{
    public function up(): void
    {
        // 1. Expand status ENUM to include 'queued' for asynchronous email queue dispatch
        try {
            $this->pdo->exec("
                ALTER TABLE `admin_broadcast_logs` 
                MODIFY COLUMN `status` ENUM('queued', 'sent', 'failed', 'partial') NOT NULL DEFAULT 'queued'
            ");
        } catch (\Throwable $e) {
            // Already updated or modified
        }

        // 2. Add composite index on (recipient_email, status) to optimize EmailQueue status sync
        try {
            $indices = $this->pdo->query("SHOW INDEX FROM `admin_broadcast_logs` WHERE Key_name = 'idx_broadcast_email_status'")->fetchAll();
            if (empty($indices)) {
                $this->pdo->exec("
                    ALTER TABLE `admin_broadcast_logs` 
                    ADD INDEX `idx_broadcast_email_status` (`recipient_email`, `status`)
                ");
            }
        } catch (\Throwable $e) {
            // Index already exists
        }
    }

    public function down(): void
    {
        try {
            $this->pdo->exec("
                ALTER TABLE `admin_broadcast_logs` 
                DROP INDEX `idx_broadcast_email_status`
            ");
        } catch (\Throwable $e) {}

        try {
            // Revert any 'queued' records to 'sent' before shrinking the enum
            $this->pdo->exec("
                UPDATE `admin_broadcast_logs` 
                SET `status` = 'sent' 
                WHERE `status` = 'queued'
            ");

            $this->pdo->exec("
                ALTER TABLE `admin_broadcast_logs` 
                MODIFY COLUMN `status` ENUM('sent', 'failed', 'partial') NOT NULL DEFAULT 'sent'
            ");
        } catch (\Throwable $e) {}
    }
}
