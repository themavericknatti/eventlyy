<?php

use Ace\Migration;

class m2026_07_08_050000_add_pass_fee_to_events_table extends Migration
{
    public function up(): void
    {
        $this->pdo->exec("
            ALTER TABLE `events` ADD COLUMN `pass_fee` TINYINT(1) DEFAULT 1 AFTER `status`;
        ");
    }

    public function down(): void
    {
        $this->pdo->exec("
            ALTER TABLE `events` DROP COLUMN `pass_fee`;
        ");
    }
}
