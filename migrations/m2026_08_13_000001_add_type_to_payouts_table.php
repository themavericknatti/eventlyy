<?php

use Ace\Migration;

class m2026_08_13_000001_add_type_to_payouts_table extends Migration
{
    public function up(): void
    {
        $cols = $this->pdo->query("SHOW COLUMNS FROM `payouts`")->fetchAll(\PDO::FETCH_COLUMN);

        if (!in_array('type', $cols)) {
            $this->pdo->exec("ALTER TABLE `payouts` ADD COLUMN `type` VARCHAR(20) NOT NULL DEFAULT 'completed' AFTER `amount`");
        }
    }

    public function down(): void
    {
        $this->pdo->exec("ALTER TABLE `payouts` DROP COLUMN `type`");
    }
}
