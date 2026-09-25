<?php

use Ace\Migration;

class m2026_07_29_000003_create_promo_codes_and_refunds_tables extends Migration
{
    public function up(): void
    {
        // 1. Create promo_codes table
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS `promo_codes` (
                `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
                `event_id` BIGINT NULL,
                `code` VARCHAR(50) NOT NULL UNIQUE,
                `discount_type` ENUM('percentage', 'fixed') DEFAULT 'percentage',
                `discount_value` DECIMAL(10, 2) NOT NULL,
                `max_uses` INT DEFAULT 100,
                `used_count` INT DEFAULT 0,
                `expires_at` DATETIME NULL,
                `status` ENUM('active', 'inactive') DEFAULT 'active',
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (`event_id`) REFERENCES `events`(`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");

        // 2. Add promo_code and discount_amount to transactions table if missing
        $columns = $this->pdo->query("SHOW COLUMNS FROM `transactions`")->fetchAll(PDO::FETCH_COLUMN);
        
        if (!in_array('promo_code', $columns)) {
            $this->pdo->exec("ALTER TABLE `transactions` ADD COLUMN `promo_code` VARCHAR(50) NULL AFTER `extra_charge_amount`");
        }

        if (!in_array('discount_amount', $columns)) {
            $this->pdo->exec("ALTER TABLE `transactions` ADD COLUMN `discount_amount` DECIMAL(10, 2) NOT NULL DEFAULT 0.00 AFTER `promo_code`");
        }
    }

    public function down(): void
    {
        $this->pdo->exec("DROP TABLE IF EXISTS `promo_codes`");
    }
}
