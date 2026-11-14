<?php

use Ace\Migration;

class m2026_11_15_000001_create_email_queue_table extends Migration
{
    public function up(): void
    {
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS `email_queue` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `recipient_email` VARCHAR(255) NOT NULL,
                `recipient_name` VARCHAR(255) DEFAULT '',
                `subject` VARCHAR(500) NOT NULL,
                `view_template` VARCHAR(255) NOT NULL,
                `view_params` LONGTEXT NOT NULL,
                `status` ENUM('pending','processing','sent','failed') DEFAULT 'pending',
                `attempts` INT DEFAULT 0,
                `max_attempts` INT DEFAULT 3,
                `last_error` TEXT NULL,
                `priority` INT DEFAULT 10,
                `reference` VARCHAR(100) NULL,
                `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                `processed_at` DATETIME NULL,
                INDEX idx_status_priority (`status`, `priority`, `created_at`),
                INDEX idx_reference (`reference`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");
    }

    public function down(): void
    {
        $this->pdo->exec("DROP TABLE IF EXISTS `email_queue`");
    }
}
