<?php

use Ace\Migration;

class m2026_07_08_000001_create_event_tables extends Migration
{
    public function up(): void
    {
        // 1. Create events table
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS `events` (
                `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
                `organizer_id` BIGINT NOT NULL,
                `name` VARCHAR(255) NOT NULL,
                `description` TEXT NOT NULL,
                `date` DATETIME NOT NULL,
                `location` VARCHAR(255) NOT NULL,
                `image` VARCHAR(255) NULL,
                `status` ENUM('draft', 'published', 'cancelled') DEFAULT 'draft',
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (`organizer_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");

        // 2. Create ticket_types table
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS `ticket_types` (
                `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
                `event_id` BIGINT NOT NULL,
                `name` VARCHAR(100) NOT NULL,
                `price` DECIMAL(10, 2) NOT NULL,
                `capacity` INT NOT NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (`event_id`) REFERENCES `events`(`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");

        // 3. Create tickets table
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS `tickets` (
                `id` VARCHAR(50) PRIMARY KEY,
                `event_id` BIGINT NOT NULL,
                `ticket_type_id` BIGINT NOT NULL,
                `user_id` BIGINT NULL,
                `attendee_name` VARCHAR(255) NOT NULL,
                `attendee_email` VARCHAR(255) NOT NULL,
                `price` DECIMAL(10, 2) NOT NULL,
                `status` ENUM('Pending', 'Paid', 'Comped', 'Cancelled') DEFAULT 'Pending',
                `status_detail` VARCHAR(255) NULL,
                `purchase_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `checked_in` TINYINT(1) DEFAULT 0,
                `checked_in_at` TIMESTAMP NULL,
                FOREIGN KEY (`event_id`) REFERENCES `events`(`id`) ON DELETE CASCADE,
                FOREIGN KEY (`ticket_type_id`) REFERENCES `ticket_types`(`id`) ON DELETE CASCADE,
                FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");

        // 4. Create payouts table
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS `payouts` (
                `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
                `organizer_id` BIGINT NOT NULL,
                `event_id` BIGINT NOT NULL,
                `amount` DECIMAL(10, 2) NOT NULL,
                `status` ENUM('pending', 'processed', 'failed') DEFAULT 'pending',
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (`organizer_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
                FOREIGN KEY (`event_id`) REFERENCES `events`(`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");

        // 5. Create host_profiles table
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS `host_profiles` (
                `user_id` BIGINT PRIMARY KEY,
                `bio` TEXT NULL,
                `payout_bank` VARCHAR(100) NULL,
                `payout_account` VARCHAR(100) NULL,
                `payout_routing` VARCHAR(100) NULL,
                `rating` DECIMAL(3, 2) DEFAULT 5.00,
                FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");
    }

    public function down(): void
    {
        $this->pdo->exec("DROP TABLE IF EXISTS `host_profiles`");
        $this->pdo->exec("DROP TABLE IF EXISTS `payouts`");
        $this->pdo->exec("DROP TABLE IF EXISTS `tickets`");
        $this->pdo->exec("DROP TABLE IF EXISTS `ticket_types`");
        $this->pdo->exec("DROP TABLE IF EXISTS `events`");
    }
}
