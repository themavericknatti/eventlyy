<?php

use Ace\Migration;

class m2026_10_01_000001_create_event_custom_domain_requests_table extends Migration
{
    public function up(): void
    {
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS `event_domain_requests` (
                `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
                `event_id` BIGINT NOT NULL,
                `host_id` BIGINT NOT NULL,
                `requested_domain` VARCHAR(255) NOT NULL,
                `integration_type` ENUM('both', 'custom_domain', 'api_access') NOT NULL DEFAULT 'both',
                `fee_amount` DECIMAL(10, 2) NOT NULL DEFAULT 10000.00,
                `payment_status` ENUM('unpaid', 'paid', 'waived') NOT NULL DEFAULT 'unpaid',
                `payment_reference` VARCHAR(100) NULL,
                `status` ENUM('pending', 'approved_active', 'rejected') NOT NULL DEFAULT 'pending',
                `dns_cname_target` VARCHAR(255) NULL,
                `host_notes` TEXT NULL,
                `admin_notes` TEXT NULL,
                `activated_at` TIMESTAMP NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX `idx_domain_req_event` (`event_id`),
                INDEX `idx_domain_req_host` (`host_id`),
                INDEX `idx_domain_req_status` (`status`),
                FOREIGN KEY (`event_id`) REFERENCES `events`(`id`) ON DELETE CASCADE,
                FOREIGN KEY (`host_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
    }

    public function down(): void
    {
        $this->pdo->exec("DROP TABLE IF EXISTS `event_domain_requests`");
    }
}
