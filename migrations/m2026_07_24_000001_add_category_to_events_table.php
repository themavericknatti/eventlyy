<?php

use Ace\Migration;

class m2026_07_24_000001_add_category_to_events_table extends Migration
{
    public function up(): void
    {
        $this->pdo->exec("
            ALTER TABLE `events` ADD COLUMN `category` VARCHAR(100) DEFAULT 'Concerts' AFTER `location`;
        ");
    }

    public function down(): void
    {
        $this->pdo->exec("
            ALTER TABLE `events` DROP COLUMN `category`;
        ");
    }
}
