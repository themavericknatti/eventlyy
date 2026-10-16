<?php

use Ace\Migration;

class m2026_01_01_000002_create_api_keys_table extends Migration
{
    public function up(): void
    {
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS `api_keys` (
                `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
                `name` VARCHAR(255) NOT NULL,
                `key_hash` VARCHAR(64) NOT NULL UNIQUE,
                `prefix` VARCHAR(8) NOT NULL,
                `permissions` TEXT NULL,
                `last_used_at` TIMESTAMP NULL DEFAULT NULL,
                `expires_at` TIMESTAMP NULL DEFAULT NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `is_active` TINYINT(1) DEFAULT 1,
                INDEX `idx_api_keys_prefix` (`prefix`),
                INDEX `idx_api_keys_is_active` (`is_active`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");
    }

    public function down(): void
    {
        $this->pdo->exec("DROP TABLE IF EXISTS `api_keys`");
    }
}
