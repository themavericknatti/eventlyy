<?php

use Ace\Migration;

class m2026_11_24_000001_add_seating_and_ticket_kind_options extends Migration
{
    public function up(): void
    {
        // 1. Add seating & ticket kind columns to ticket_types
        $ttCols = $this->pdo->query("SHOW COLUMNS FROM `ticket_types`")->fetchAll(\PDO::FETCH_COLUMN);

        if (!in_array('ticket_kind', $ttCols)) {
            $this->pdo->exec("
                ALTER TABLE `ticket_types` 
                ADD COLUMN `ticket_kind` ENUM('standard', 'duo', 'group', 'seated') NOT NULL DEFAULT 'standard' AFTER `name`,
                ADD COLUMN `section_name` VARCHAR(100) NULL AFTER `admits`,
                ADD COLUMN `row_name` VARCHAR(50) NULL AFTER `section_name`,
                ADD COLUMN `seat_prefix` VARCHAR(20) NULL AFTER `row_name`,
                ADD COLUMN `seat_start` INT NULL AFTER `seat_prefix`,
                ADD COLUMN `seat_end` INT NULL AFTER `seat_start`,
                ADD COLUMN `has_seating` TINYINT(1) NOT NULL DEFAULT 0 AFTER `seat_end`,
                ADD COLUMN `description` TEXT NULL AFTER `has_seating`;
            ");
        }

        // 2. Add assigned_seat & attendee_role columns to tickets
        $tCols = $this->pdo->query("SHOW COLUMNS FROM `tickets`")->fetchAll(\PDO::FETCH_COLUMN);

        if (!in_array('assigned_seat', $tCols)) {
            $this->pdo->exec("
                ALTER TABLE `tickets` 
                ADD COLUMN `assigned_seat` VARCHAR(100) NULL AFTER `status_detail`,
                ADD COLUMN `attendee_role` ENUM('primary', 'plus_one', 'group_member') NOT NULL DEFAULT 'primary' AFTER `assigned_seat`;
            ");
        }
    }

    public function down(): void
    {
        try {
            $this->pdo->exec("
                ALTER TABLE `ticket_types` 
                DROP COLUMN IF EXISTS `ticket_kind`,
                DROP COLUMN IF EXISTS `section_name`,
                DROP COLUMN IF EXISTS `row_name`,
                DROP COLUMN IF EXISTS `seat_prefix`,
                DROP COLUMN IF EXISTS `seat_start`,
                DROP COLUMN IF EXISTS `seat_end`,
                DROP COLUMN IF EXISTS `has_seating`,
                DROP COLUMN IF EXISTS `description`;
            ");
        } catch (\Throwable $e) {}

        try {
            $this->pdo->exec("
                ALTER TABLE `tickets` 
                DROP COLUMN IF EXISTS `assigned_seat`,
                DROP COLUMN IF EXISTS `attendee_role`;
            ");
        } catch (\Throwable $e) {}
    }
}
