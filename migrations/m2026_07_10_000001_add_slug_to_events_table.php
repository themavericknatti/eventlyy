<?php

use Ace\Migration;

class m2026_07_10_000001_add_slug_to_events_table extends Migration
{
    public function up(): void
    {
        $cols = $this->pdo->query("SHOW COLUMNS FROM events")->fetchAll(\PDO::FETCH_COLUMN);
        if (!in_array('slug', $cols)) {
            $this->pdo->exec("ALTER TABLE `events` ADD COLUMN `slug` VARCHAR(255) UNIQUE NULL AFTER `name`");
        }
    }

    public function down(): void
    {
        $this->pdo->exec("
            ALTER TABLE `events` DROP COLUMN `slug`;
        ");
    }
}
