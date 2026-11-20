<?php

use Ace\Migration;

class m2026_11_20_000001_enhance_event_referrals_table extends Migration
{
    public function up(): void
    {
        try {
            $this->pdo->exec("
                ALTER TABLE `event_referrals`
                ADD COLUMN `unique_visits` INT UNSIGNED NOT NULL DEFAULT 0 AFTER `visits_count`,
                ADD COLUMN `promoter_email` VARCHAR(255) NULL AFTER `promoter_name`,
                ADD COLUMN `promoter_phone` VARCHAR(50) NULL AFTER `promoter_email`,
                ADD COLUMN `commission_type` ENUM('percentage', 'fixed') NOT NULL DEFAULT 'percentage' AFTER `referral_code`,
                ADD COLUMN `commission_earned` DECIMAL(12, 2) NOT NULL DEFAULT 0.00 AFTER `revenue_generated`,
                ADD COLUMN `commission_paid` DECIMAL(12, 2) NOT NULL DEFAULT 0.00 AFTER `commission_earned`,
                ADD COLUMN `payout_status` ENUM('unpaid', 'partial', 'paid') NOT NULL DEFAULT 'unpaid' AFTER `commission_paid`,
                ADD COLUMN `notes` TEXT NULL AFTER `status`;
            ");

            // Backfill existing rows commission_earned based on historical revenue_generated and commission_rate
            $this->pdo->exec("
                UPDATE `event_referrals`
                SET commission_earned = ROUND((revenue_generated * commission_rate) / 100, 2),
                    unique_visits = visits_count
                WHERE commission_earned = 0.00 AND revenue_generated > 0 AND commission_rate > 0;
            ");
        } catch (\PDOException $e) {
            // Log or ignore if columns already exist
        }
    }

    public function down(): void
    {
        try {
            $this->pdo->exec("
                ALTER TABLE `event_referrals`
                DROP COLUMN `unique_visits`,
                DROP COLUMN `promoter_email`,
                DROP COLUMN `promoter_phone`,
                DROP COLUMN `commission_type`,
                DROP COLUMN `commission_earned`,
                DROP COLUMN `commission_paid`,
                DROP COLUMN `payout_status`,
                DROP COLUMN `notes`;
            ");
        } catch (\PDOException $e) {}
    }
}
