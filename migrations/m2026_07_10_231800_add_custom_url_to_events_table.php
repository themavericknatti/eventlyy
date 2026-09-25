<?php

use Ace\Migration;

class m2026_07_10_231800_add_custom_url_to_events_table extends Migration
{
    public function up(): void
    {
        $this->pdo->exec("
            ALTER TABLE `events` ADD COLUMN `custom_url` VARCHAR(255) UNIQUE NULL AFTER `location`;
        ");
    }

    public function down(): void
    {
        $this->pdo->exec("
            ALTER TABLE `events` DROP COLUMN `custom_url`;
        ");
    }
}
