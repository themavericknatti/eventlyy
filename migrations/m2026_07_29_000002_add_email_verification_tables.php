<?php

use Ace\Migration;

class m2026_07_29_000002_add_email_verification_tables extends Migration
{
    public function up(): void
    {
        // Add email_verified_at to users table if it doesn't exist
        $this->pdo->exec("
            ALTER TABLE `users` 
            ADD COLUMN `email_verified_at` TIMESTAMP NULL DEFAULT NULL AFTER `status`;
        ");

        // Create email_verifications table
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS `email_verifications` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `email` VARCHAR(255) NOT NULL,
                `token_hash` VARCHAR(255) NOT NULL,
                `expires_at` DATETIME NOT NULL,
                `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                INDEX `idx_email_token` (`email`, `token_hash`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");
    }

    public function down(): void
    {
        $this->pdo->exec("DROP TABLE IF EXISTS `email_verifications`");
        $this->pdo->exec("ALTER TABLE `users` DROP COLUMN IF EXISTS `email_verified_at`");
    }
}
