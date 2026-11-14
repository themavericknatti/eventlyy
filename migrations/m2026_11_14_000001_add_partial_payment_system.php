<?php

use Ace\Migration;

class m2026_11_14_000001_add_partial_payment_system extends Migration
{
    public function up(): void
    {
        // 1. Create layaway_payments table
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS `layaway_payments` (
                `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
                `layaway_ticket_id` VARCHAR(50) NOT NULL,
                `event_id` BIGINT NOT NULL,
                `user_id` BIGINT NULL,
                `payer_email` VARCHAR(255) NOT NULL,
                `amount` DECIMAL(10, 2) NOT NULL,
                `payment_method` VARCHAR(50) DEFAULT 'paystack',
                `reference` VARCHAR(100) NOT NULL UNIQUE,
                `status` ENUM('pending', 'completed', 'failed') DEFAULT 'pending',
                `gateway_response` TEXT NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_layaway_ticket (`layaway_ticket_id`),
                INDEX idx_layaway_email (`payer_email`),
                FOREIGN KEY (`layaway_ticket_id`) REFERENCES `tickets`(`id`) ON DELETE CASCADE,
                FOREIGN KEY (`event_id`) REFERENCES `events`(`id`) ON DELETE CASCADE,
                FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");

        // 2. Create layaway_credits table
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS `layaway_credits` (
                `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
                `user_id` BIGINT NULL,
                `email` VARCHAR(255) NOT NULL,
                `original_ticket_id` VARCHAR(50) NOT NULL,
                `original_event_id` BIGINT NOT NULL,
                `amount` DECIMAL(10, 2) NOT NULL,
                `remaining_amount` DECIMAL(10, 2) NOT NULL,
                `status` ENUM('available', 'used', 'partially_used', 'expired') DEFAULT 'available',
                `used_on_event_id` BIGINT NULL,
                `expires_at` TIMESTAMP NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_credit_email_status (`email`, `status`),
                FOREIGN KEY (`original_event_id`) REFERENCES `events`(`id`) ON DELETE CASCADE,
                FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");

        // 3. Update tickets table status ENUM to include 'Layaway' and 'Expired'
        try {
            $this->pdo->exec("
                ALTER TABLE `tickets` 
                MODIFY COLUMN `status` ENUM('Pending', 'Paid', 'Comped', 'Cancelled', 'Layaway', 'Expired') DEFAULT 'Pending';
            ");
        } catch (\Throwable $e) {
            error_log("Migration tickets status update notice: " . $e->getMessage());
        }

        // 4. Update events table for partial payment toggle and minimum deposit percent
        try {
            $this->pdo->exec("
                ALTER TABLE `events`
                ADD COLUMN `allow_partial_payment` TINYINT(1) NOT NULL DEFAULT 0 AFTER `pass_fee`,
                ADD COLUMN `partial_payment_min_percent` INT NOT NULL DEFAULT 25 AFTER `allow_partial_payment`;
            ");
        } catch (\Throwable $e) {
            error_log("Migration events partial payment columns notice: " . $e->getMessage());
        }
    }

    public function down(): void
    {
        $this->pdo->exec("DROP TABLE IF EXISTS `layaway_payments`");
        $this->pdo->exec("DROP TABLE IF EXISTS `layaway_credits`");
        try {
            $this->pdo->exec("
                ALTER TABLE `events`
                DROP COLUMN `allow_partial_payment`,
                DROP COLUMN `partial_payment_min_percent`;
            ");
        } catch (\Throwable $e) {}
    }
}
