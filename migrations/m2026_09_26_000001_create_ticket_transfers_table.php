<?php

use Ace\Migration;

class m2026_09_26_000001_create_ticket_transfers_table extends Migration
{
    public function up(): void
    {
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS `ticket_transfers` (
                `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
                `ticket_id` VARCHAR(50) NOT NULL,
                `previous_ticket_id` VARCHAR(50) NOT NULL,
                `event_id` BIGINT NOT NULL,
                `sender_user_id` BIGINT NULL,
                `sender_name` VARCHAR(255) NOT NULL,
                `sender_email` VARCHAR(255) NOT NULL,
                `recipient_user_id` BIGINT NULL,
                `recipient_name` VARCHAR(255) NOT NULL,
                `recipient_email` VARCHAR(255) NOT NULL,
                `transfer_note` TEXT NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX `idx_ticket_transfers_ticket` (`ticket_id`),
                INDEX `idx_ticket_transfers_prev` (`previous_ticket_id`),
                INDEX `idx_ticket_transfers_event` (`event_id`),
                INDEX `idx_ticket_transfers_sender` (`sender_user_id`),
                INDEX `idx_ticket_transfers_recipient` (`recipient_user_id`),
                INDEX `idx_ticket_transfers_recipient_email` (`recipient_email`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");
    }

    public function down(): void
    {
        $this->pdo->exec("DROP TABLE IF EXISTS `ticket_transfers`");
    }
}
