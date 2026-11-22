<?php

use Ace\Migration;

class m2026_11_22_000002_fix_event_reviews_ticket_id_type extends Migration
{
    public function up(): void
    {
        // Ticket IDs are VARCHAR strings like 'EV-F05B0CB6', not integers.
        // The original migration created ticket_id as INT which causes
        // "Incorrect integer value" errors when inserting review records.
        $this->pdo->exec("
            ALTER TABLE `event_reviews`
            MODIFY COLUMN `ticket_id` VARCHAR(20) DEFAULT NULL
        ");
    }

    public function down(): void
    {
        $this->pdo->exec("
            ALTER TABLE `event_reviews`
            MODIFY COLUMN `ticket_id` INT DEFAULT NULL
        ");
    }
}
