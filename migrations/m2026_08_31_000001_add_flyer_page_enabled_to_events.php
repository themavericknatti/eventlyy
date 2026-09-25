<?php

use Ace\Migration;

class m2026_08_31_000001_add_flyer_page_enabled_to_events extends Migration
{
    public function up(): void
    {
         $this->pdo->exec("
            ALTER TABLE `events` ADD COLUMN `flyer_page_enabled` TINYINT(1) NOT NULL DEFAULT 0 AFTER `is_featured`;
        ");
    }

    public function down(): void
    {
        $this->pdo->exec("
            ALTER TABLE `events` DROP COLUMN `flyer_page_enabled`;
        ");
    }
}
