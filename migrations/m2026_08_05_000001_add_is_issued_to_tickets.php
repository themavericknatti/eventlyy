<?php

use Ace\Migration;

class m2026_08_05_000001_add_is_issued_to_tickets extends Migration
{
    public function up(): void
    {
        $cols = $this->pdo->query("SHOW COLUMNS FROM tickets")->fetchAll(\PDO::FETCH_COLUMN);

        if (!in_array('is_issued', $cols)) {
            $this->pdo->exec("ALTER TABLE `tickets` ADD COLUMN `is_issued` TINYINT(1) NOT NULL DEFAULT 0 AFTER `checked_in_at`");
        }

        // Backfill: mark existing manually-issued tickets
        $this->pdo->exec("UPDATE `tickets` SET `is_issued` = 1 WHERE `status_detail` LIKE '%issued by host%' OR `status_detail` LIKE '%Manually issued%'");
    }

    public function down(): void
    {
        $this->pdo->exec("ALTER TABLE `tickets` DROP COLUMN `is_issued`");
    }
}
