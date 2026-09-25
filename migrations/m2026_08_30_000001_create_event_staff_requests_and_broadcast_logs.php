<?php

use Ace\Migration;

class m2026_08_30_000001_create_event_staff_requests_and_broadcast_logs extends Migration
{
    public function up(): void
    {
        // 1. Create event_staff_requests table
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS `event_staff_requests` (
                `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
                `event_id` BIGINT NOT NULL,
                `host_id` BIGINT NOT NULL,
                `roles_requested` JSON NOT NULL,
                `notes` TEXT NULL,
                `status` ENUM('pending', 'partially_fulfilled', 'fulfilled', 'declined') NOT NULL DEFAULT 'pending',
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX `idx_staff_req_event` (`event_id`),
                INDEX `idx_staff_req_host` (`host_id`),
                INDEX `idx_staff_req_status` (`status`),
                FOREIGN KEY (`event_id`) REFERENCES `events`(`id`) ON DELETE CASCADE,
                FOREIGN KEY (`host_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        // 2. Create admin_broadcast_logs table
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS `admin_broadcast_logs` (
                `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
                `admin_id` BIGINT NOT NULL,
                `audience_type` ENUM('all_users', 'all_hosts', 'single_host', 'single_user') NOT NULL,
                `recipient_count` INT NOT NULL DEFAULT 1,
                `recipient_email` VARCHAR(255) NULL,
                `recipient_name` VARCHAR(255) NULL,
                `subject` VARCHAR(255) NOT NULL,
                `message` LONGTEXT NOT NULL,
                `cta_text` VARCHAR(100) NULL,
                `cta_url` VARCHAR(255) NULL,
                `priority` ENUM('normal', 'urgent', 'important', 'announcement') NOT NULL DEFAULT 'normal',
                `status` ENUM('sent', 'failed', 'partial') NOT NULL DEFAULT 'sent',
                `sent_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX `idx_broadcast_admin` (`admin_id`),
                INDEX `idx_broadcast_audience` (`audience_type`),
                INDEX `idx_broadcast_sent` (`sent_at`),
                FOREIGN KEY (`admin_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
    }

    public function down(): void
    {
        $this->pdo->exec("DROP TABLE IF EXISTS `admin_broadcast_logs`");
        $this->pdo->exec("DROP TABLE IF EXISTS `event_staff_requests`");
    }
}
