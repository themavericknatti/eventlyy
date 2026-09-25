<?php

use Ace\Migration;

class m2026_07_31_000001_create_login_attempts_table extends Migration
{
    public function up(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS `login_attempts` (
            `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `ip_address` VARCHAR(45) NOT NULL,
            `email` VARCHAR(255) NOT NULL,
            `attempts` INT UNSIGNED NOT NULL DEFAULT 1,
            `last_attempt_at` DATETIME NOT NULL,
            `locked_until` DATETIME NULL DEFAULT NULL,
            KEY `idx_ip_email` (`ip_address`, `email`),
            KEY `idx_ip` (`ip_address`),
            KEY `idx_locked` (`locked_until`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

        $this->pdo->exec($sql);
    }

    public function down(): void
    {
        $this->pdo->exec("DROP TABLE IF EXISTS `login_attempts`;");
    }
}
