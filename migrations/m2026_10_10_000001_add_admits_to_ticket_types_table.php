<?php

use Ace\Migration;

class m2026_10_10_000001_add_admits_to_ticket_types_table extends Migration
{
    public function up(): void
    {
        $cols = $this->pdo->query("SHOW COLUMNS FROM `ticket_types`")->fetchAll(\PDO::FETCH_COLUMN);
        if (!in_array('admits', $cols)) {
            $this->pdo->exec("ALTER TABLE `ticket_types` ADD COLUMN `admits` INT NOT NULL DEFAULT 1 AFTER `capacity`");
        }
    }

    public function down(): void
    {
        $this->pdo->exec("ALTER TABLE `ticket_types` DROP COLUMN IF EXISTS `admits`");
    }
}
