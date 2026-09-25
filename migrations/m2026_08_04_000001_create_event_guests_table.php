<?php

use Ace\Migration;

class m2026_08_04_000001_create_event_guests_table extends Migration
{
    public function up(): void
    {
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS `event_guests` (
                `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
                `event_id` BIGINT NOT NULL,
                `name` VARCHAR(255) NOT NULL,
                `role` VARCHAR(100) NULL,
                `phone` VARCHAR(50) NULL,
                `social` VARCHAR(255) NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (`event_id`) REFERENCES `events`(`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");
    }

    public function down(): void
    {
        $this->pdo->exec("
            DROP TABLE IF EXISTS `event_guests`;
        ");
    }
}
