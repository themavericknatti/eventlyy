<?php

use Ace\Migration;

class m2026_01_01_000003_create_jobs_table extends Migration
{
    public function up(): void
    {
        // Table for active / pending background jobs
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS `jobs` (
                `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
                `queue` VARCHAR(255) NOT NULL DEFAULT 'default',
                `payload` LONGTEXT NOT NULL,
                `attempts` TINYINT UNSIGNED NOT NULL DEFAULT 0,
                `reserved_at` TIMESTAMP NULL DEFAULT NULL,
                `available_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                INDEX `idx_jobs_queue_reserved_available` (`queue`, `reserved_at`, `available_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");

        // Table for permanently failed jobs
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS `failed_jobs` (
                `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
                `queue` VARCHAR(255) NOT NULL,
                `payload` LONGTEXT NOT NULL,
                `exception` LONGTEXT NOT NULL,
                `failed_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                INDEX `idx_failed_jobs_queue` (`queue`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");
    }

    public function down(): void
    {
        $this->pdo->exec("DROP TABLE IF EXISTS `failed_jobs`");
        $this->pdo->exec("DROP TABLE IF EXISTS `jobs`");
    }
}
