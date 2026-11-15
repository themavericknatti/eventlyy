<?php

use Ace\Migration;

class m2026_11_15_000002_add_phone_and_whatsapp_delivery_support extends Migration
{
    public function up(): void
    {
        // 1. Add attendee_phone to tickets table if not exists
        try {
            $this->pdo->exec("
                ALTER TABLE `tickets` 
                ADD COLUMN `attendee_phone` VARCHAR(50) NULL DEFAULT NULL AFTER `attendee_email`,
                ADD INDEX `idx_tickets_phone` (`attendee_phone`);
            ");
        } catch (\Throwable $e) {
            // Already exists or index exists
        }

        // 2. Add payer_phone to transactions table if not exists
        try {
            $this->pdo->exec("
                ALTER TABLE `transactions` 
                ADD COLUMN `payer_phone` VARCHAR(50) NULL DEFAULT NULL AFTER `payer_email`;
            ");
        } catch (\Throwable $e) {
            // Already exists
        }

        // 3. Add whatsapp_delivery_enabled to events table if not exists
        try {
            $this->pdo->exec("
                ALTER TABLE `events` 
                ADD COLUMN `whatsapp_delivery_enabled` TINYINT(1) NOT NULL DEFAULT 1 AFTER `flyer_page_enabled`;
            ");
        } catch (\Throwable $e) {
            // Already exists
        }
    }

    public function down(): void
    {
        try {
            $this->pdo->exec("ALTER TABLE `tickets` DROP COLUMN `attendee_phone`;");
        } catch (\Throwable $e) {}

        try {
            $this->pdo->exec("ALTER TABLE `transactions` DROP COLUMN `payer_phone`;");
        } catch (\Throwable $e) {}

        try {
            $this->pdo->exec("ALTER TABLE `events` DROP COLUMN `whatsapp_delivery_enabled`;");
        } catch (\Throwable $e) {}
    }
}
