<?php

use Ace\Migration;

class m2026_08_05_000002_add_host_id_to_promo_codes extends Migration
{
    public function up(): void
    {
        $cols = $this->pdo->query("SHOW COLUMNS FROM promo_codes")->fetchAll(\PDO::FETCH_COLUMN);

        if (!in_array('host_id', $cols)) {
            $this->pdo->exec("ALTER TABLE `promo_codes` ADD COLUMN `host_id` BIGINT NULL AFTER `id`");
            $this->pdo->exec("ALTER TABLE `promo_codes` ADD INDEX `idx_promo_host` (`host_id`)");
        }

        // Backfill: set host_id from the linked event's organizer_id for existing promo codes
        $this->pdo->exec("
            UPDATE promo_codes p
            INNER JOIN events e ON p.event_id = e.id
            SET p.host_id = e.organizer_id
            WHERE p.host_id IS NULL AND p.event_id IS NOT NULL
        ");
    }

    public function down(): void
    {
        $this->pdo->exec("ALTER TABLE `promo_codes` DROP COLUMN `host_id`");
    }
}
