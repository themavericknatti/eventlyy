<?php

use Ace\Migration;

class m2026_07_28_000001_create_transactions_table extends Migration
{
    public function up(): void
    {
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS `transactions` (
                `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
                `reference` VARCHAR(100) NOT NULL UNIQUE,
                `event_id` BIGINT NOT NULL,
                `user_id` BIGINT NULL,
                `payer_name` VARCHAR(255) NOT NULL,
                `payer_email` VARCHAR(255) NOT NULL,
                `type` ENUM('payment', 'refund', 'payout', 'fee') DEFAULT 'payment',
                `payment_method` VARCHAR(50) DEFAULT 'paystack',
                `subtotal_amount` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
                `fee_amount` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
                `extra_charge_amount` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
                `total_amount` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
                `status` ENUM('pending', 'completed', 'failed', 'refunded', 'partially_refunded') DEFAULT 'pending',
                `gateway_response` TEXT NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (`event_id`) REFERENCES `events`(`id`) ON DELETE CASCADE,
                FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");
    }

    public function down(): void
    {
        $this->pdo->exec("DROP TABLE IF EXISTS `transactions`");
    }
}
