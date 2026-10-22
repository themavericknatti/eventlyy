<?php

use Ace\Migration;

class m2026_10_22_000001_add_recurrence_to_events_table extends Migration
{
    public function up(): void
    {
        $cols = $this->pdo->query("SHOW COLUMNS FROM `events`")->fetchAll(\PDO::FETCH_COLUMN);

        if (!in_array('is_recurring', $cols)) {
            $this->pdo->exec("ALTER TABLE `events` ADD COLUMN `is_recurring` TINYINT(1) NOT NULL DEFAULT 0 AFTER `flyer_page_enabled`");
        }

        if (!in_array('recurrence_type', $cols)) {
            $this->pdo->exec("ALTER TABLE `events` ADD COLUMN `recurrence_type` VARCHAR(50) NOT NULL DEFAULT 'none' AFTER `is_recurring`");
        }

        if (!in_array('recurrence_interval', $cols)) {
            $this->pdo->exec("ALTER TABLE `events` ADD COLUMN `recurrence_interval` INT NOT NULL DEFAULT 1 AFTER `recurrence_type`");
        }

        if (!in_array('recurrence_end_date', $cols)) {
            $this->pdo->exec("ALTER TABLE `events` ADD COLUMN `recurrence_end_date` DATETIME NULL AFTER `recurrence_interval`");
        }

        if (!in_array('recurrence_count', $cols)) {
            $this->pdo->exec("ALTER TABLE `events` ADD COLUMN `recurrence_count` INT NULL AFTER `recurrence_end_date`");
        }

        if (!in_array('recurrence_group_id', $cols)) {
            $this->pdo->exec("ALTER TABLE `events` ADD COLUMN `recurrence_group_id` VARCHAR(64) NULL AFTER `recurrence_count`");
        }

        if (!in_array('recurrence_sequence', $cols)) {
            $this->pdo->exec("ALTER TABLE `events` ADD COLUMN `recurrence_sequence` INT NOT NULL DEFAULT 1 AFTER `recurrence_group_id`");
        }

        // Add performance indexes if missing
        try {
            $this->pdo->exec("ALTER TABLE `events` ADD INDEX `idx_events_is_recurring` (`is_recurring`)");
        } catch (\Throwable $e) {}

        try {
            $this->pdo->exec("ALTER TABLE `events` ADD INDEX `idx_events_recurrence_group` (`recurrence_group_id`)");
        } catch (\Throwable $e) {}
    }

    public function down(): void
    {
        $cols = $this->pdo->query("SHOW COLUMNS FROM `events`")->fetchAll(\PDO::FETCH_COLUMN);

        try {
            $this->pdo->exec("ALTER TABLE `events` DROP INDEX `idx_events_is_recurring`");
        } catch (\Throwable $e) {}

        try {
            $this->pdo->exec("ALTER TABLE `events` DROP INDEX `idx_events_recurrence_group`");
        } catch (\Throwable $e) {}

        $toDrop = [
            'recurrence_sequence',
            'recurrence_group_id',
            'recurrence_count',
            'recurrence_end_date',
            'recurrence_interval',
            'recurrence_type',
            'is_recurring'
        ];

        foreach ($toDrop as $col) {
            if (in_array($col, $cols)) {
                $this->pdo->exec("ALTER TABLE `events` DROP COLUMN `{$col}`");
            }
        }
    }
}
