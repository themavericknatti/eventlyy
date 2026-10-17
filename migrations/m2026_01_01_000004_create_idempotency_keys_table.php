<?php

use Ace\Migration;

class m2026_01_01_000004_create_idempotency_keys_table extends Migration
{
    public function up(): void
    {
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS `idempotency_keys` (
                `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
                `idempotency_key` VARCHAR(255) NOT NULL,
                `scope` VARCHAR(128) NOT NULL DEFAULT 'global',
                `request_hash` VARCHAR(64) NOT NULL,
                `status` ENUM('pending', 'completed') NOT NULL DEFAULT 'pending',
                `response_code` INT NULL DEFAULT NULL,
                `response_body` LONGTEXT NULL DEFAULT NULL,
                `response_headers` TEXT NULL DEFAULT NULL,
                `locked_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `expires_at` TIMESTAMP NOT NULL,
                `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                UNIQUE INDEX `idx_idempotency_scope_key` (`scope`, `idempotency_key`),
                INDEX `idx_idempotency_expires_at` (`expires_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");
    }

    public function down(): void
    {
        $this->pdo->exec("DROP TABLE IF EXISTS `idempotency_keys`");
    }
}
