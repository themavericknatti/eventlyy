<?php

use Ace\Migration;

class m2026_01_01_000001_create_users_table extends Migration
{
    public function up(): void
    {
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS `users` (
                `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
                `name` VARCHAR(255) NOT NULL,
                `email` VARCHAR(255) NOT NULL UNIQUE,
                `phone` VARCHAR(30) NULL,
                `password` VARCHAR(255) NOT NULL,
                `status` ENUM('active', 'suspended') DEFAULT 'active',
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");
    }

    public function down(): void
    {
        $this->pdo->exec("DROP TABLE IF EXISTS `users`");
    }
}
