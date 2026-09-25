<?php

use Ace\Migration;

class m2026_08_16_000001_create_event_referrals_table extends Migration
{
    public function up(): void
    {
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS `event_referrals` (
                `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
                `event_id` BIGINT NOT NULL,
                `host_id` BIGINT NOT NULL,
                `promoter_name` VARCHAR(255) NOT NULL,
                `referral_code` VARCHAR(64) NOT NULL UNIQUE,
                `commission_rate` DECIMAL(5, 2) NOT NULL DEFAULT 0.00,
                `visits_count` INT UNSIGNED NOT NULL DEFAULT 0,
                `tickets_sold` INT UNSIGNED NOT NULL DEFAULT 0,
                `revenue_generated` DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
                `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX `idx_event_referrals_event` (`event_id`),
                INDEX `idx_event_referrals_host` (`host_id`),
                INDEX `idx_event_referrals_code` (`referral_code`),
                FOREIGN KEY (`event_id`) REFERENCES `events`(`id`) ON DELETE CASCADE,
                FOREIGN KEY (`host_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");

        // Add referral_id to transactions table if not exists
        try {
            $this->pdo->exec("
                ALTER TABLE `transactions` 
                ADD COLUMN `referral_id` BIGINT NULL AFTER `extra_charge_amount`,
                ADD INDEX `idx_transactions_referral` (`referral_id`),
                ADD CONSTRAINT `fk_transactions_referral` FOREIGN KEY (`referral_id`) REFERENCES `event_referrals`(`id`) ON DELETE SET NULL;
            ");
        } catch (\PDOException $e) {
            // Column might already exist or handled gracefully
        }
    }

    public function down(): void
    {
        try {
            $this->pdo->exec("ALTER TABLE `transactions` DROP FOREIGN KEY `fk_transactions_referral`");
            $this->pdo->exec("ALTER TABLE `transactions` DROP COLUMN `referral_id`");
        } catch (\PDOException $e) {}

        $this->pdo->exec("DROP TABLE IF EXISTS `event_referrals`");
    }
}
