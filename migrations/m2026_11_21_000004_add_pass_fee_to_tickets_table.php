<?php

use Ace\Migration;

class m2026_11_21_000004_add_pass_fee_to_tickets_table extends Migration
{
    public function up(): void
    {
        // 1. Add pass_fee column to tickets table if it does not already exist
        $cols = $this->pdo->query("SHOW COLUMNS FROM `tickets`")->fetchAll(\PDO::FETCH_COLUMN);

        if (!in_array('pass_fee', $cols)) {
            $this->pdo->exec("
                ALTER TABLE `tickets` 
                ADD COLUMN `pass_fee` TINYINT(1) NOT NULL DEFAULT 1 AFTER `price`
            ");
        }

        // 2. Backfill existing tickets to match the fee mode of their respective events
        try {
            $this->pdo->exec("
                UPDATE `tickets` t
                INNER JOIN `events` e ON t.event_id = e.id
                SET t.pass_fee = COALESCE(e.pass_fee, 1)
            ");
        } catch (\Throwable $e) {
            // Non-blocking backfill
        }
    }

    public function down(): void
    {
        try {
            $cols = $this->pdo->query("SHOW COLUMNS FROM `tickets`")->fetchAll(\PDO::FETCH_COLUMN);
            if (in_array('pass_fee', $cols)) {
                $this->pdo->exec("ALTER TABLE `tickets` DROP COLUMN `pass_fee`");
            }
        } catch (\Throwable $e) {
            // Ignore error on rollback
        }
    }
}
