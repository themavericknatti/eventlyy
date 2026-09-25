<?php

use Ace\Migration;

class m2026_07_29_000001_create_support_inquiries_table extends Migration
{
    public function up(): void
    {
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS `support_inquiries` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `name` VARCHAR(255) NOT NULL,
                `email` VARCHAR(255) NOT NULL,
                `subject` VARCHAR(100) NOT NULL DEFAULT 'general',
                `message` TEXT NOT NULL,
                `status` ENUM('new', 'read', 'resolved') NOT NULL DEFAULT 'new',
                `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");
    }

    public function down(): void
    {
        $this->pdo->exec("DROP TABLE IF EXISTS `support_inquiries`;");
    }
}
