<?php

use Ace\Migration;

class m2026_11_21_000003_add_discount_details_to_event_trivia extends Migration
{
    public function up(): void
    {
        // 1. Add discount configurations to event_trivia
        $triviaCols = $this->pdo->query("SHOW COLUMNS FROM `event_trivia`")->fetchAll(PDO::FETCH_COLUMN);

        if (!in_array('reward_discount_type', $triviaCols)) {
            $this->pdo->exec("ALTER TABLE `event_trivia` ADD COLUMN `reward_discount_type` ENUM('percentage', 'fixed') NOT NULL DEFAULT 'percentage' AFTER `reward_promo_code`");
        }

        if (!in_array('reward_discount_value', $triviaCols)) {
            $this->pdo->exec("ALTER TABLE `event_trivia` ADD COLUMN `reward_discount_value` DECIMAL(10, 2) NOT NULL DEFAULT 10.00 AFTER `reward_discount_type`");
        }

        // 2. Add redemption tracking to event_trivia_submissions
        $subCols = $this->pdo->query("SHOW COLUMNS FROM `event_trivia_submissions`")->fetchAll(PDO::FETCH_COLUMN);

        if (!in_array('redeemed_at', $subCols)) {
            $this->pdo->exec("ALTER TABLE `event_trivia_submissions` ADD COLUMN `redeemed_at` DATETIME NULL DEFAULT NULL AFTER `time_taken_seconds`");
        }

        if (!in_array('transaction_id', $subCols)) {
            $this->pdo->exec("ALTER TABLE `event_trivia_submissions` ADD COLUMN `transaction_id` BIGINT NULL DEFAULT NULL AFTER `redeemed_at`");
        }
    }

    public function down(): void
    {
        $triviaCols = $this->pdo->query("SHOW COLUMNS FROM `event_trivia`")->fetchAll(PDO::FETCH_COLUMN);
        if (in_array('reward_discount_value', $triviaCols)) {
            $this->pdo->exec("ALTER TABLE `event_trivia` DROP COLUMN `reward_discount_value`");
        }
        if (in_array('reward_discount_type', $triviaCols)) {
            $this->pdo->exec("ALTER TABLE `event_trivia` DROP COLUMN `reward_discount_type`");
        }

        $subCols = $this->pdo->query("SHOW COLUMNS FROM `event_trivia_submissions`")->fetchAll(PDO::FETCH_COLUMN);
        if (in_array('transaction_id', $subCols)) {
            $this->pdo->exec("ALTER TABLE `event_trivia_submissions` DROP COLUMN `transaction_id`");
        }
        if (in_array('redeemed_at', $subCols)) {
            $this->pdo->exec("ALTER TABLE `event_trivia_submissions` DROP COLUMN `redeemed_at`");
        }
    }
}
