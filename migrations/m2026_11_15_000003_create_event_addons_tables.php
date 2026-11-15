<?php

use Ace\Migration;

class m2026_11_15_000003_create_event_addons_tables extends Migration
{
    public function up(): void
    {
        // 1. Table for host-created Add-Ons / Merchandise
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS `event_addons` (
                `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
                `event_id` BIGINT NOT NULL,
                `name` VARCHAR(255) NOT NULL,
                `description` TEXT NULL,
                `price` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
                `quantity` INT NULL,
                `sold_count` INT NOT NULL DEFAULT 0,
                `image` VARCHAR(255) NULL,
                `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
                INDEX `idx_event_addons_event` (`event_id`),
                FOREIGN KEY (`event_id`) REFERENCES `events`(`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");

        // 2. Table for attendee purchased add-on orders / items
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS `order_addons` (
                `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
                `transaction_id` BIGINT NULL,
                `event_id` BIGINT NOT NULL,
                `addon_id` BIGINT NOT NULL,
                `user_id` BIGINT NULL,
                `buyer_name` VARCHAR(255) NULL,
                `buyer_email` VARCHAR(255) NULL,
                `quantity` INT NOT NULL DEFAULT 1,
                `unit_price` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
                `total_price` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
                `redemption_status` ENUM('pending', 'fulfilled', 'cancelled') NOT NULL DEFAULT 'pending',
                `redeemed_at` TIMESTAMP NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX `idx_order_addons_event` (`event_id`),
                INDEX `idx_order_addons_transaction` (`transaction_id`),
                INDEX `idx_order_addons_email` (`buyer_email`),
                FOREIGN KEY (`event_id`) REFERENCES `events`(`id`) ON DELETE CASCADE,
                FOREIGN KEY (`addon_id`) REFERENCES `event_addons`(`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");
    }

    public function down(): void
    {
        $this->pdo->exec("DROP TABLE IF EXISTS `order_addons`;");
        $this->pdo->exec("DROP TABLE IF EXISTS `event_addons`;");
    }
}
