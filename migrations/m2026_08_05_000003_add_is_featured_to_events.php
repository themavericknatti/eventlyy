<?php

use Ace\Migration;

class m2026_08_05_000003_add_is_featured_to_events extends Migration
{
    public function up(): void
    {
        $cols = $this->pdo->query("SHOW COLUMNS FROM events")->fetchAll(\PDO::FETCH_COLUMN);

        if (!in_array('is_featured', $cols)) {
            $this->pdo->exec("ALTER TABLE `events` ADD COLUMN `is_featured` TINYINT(1) NOT NULL DEFAULT 0 AFTER `status`");
            $this->pdo->exec("ALTER TABLE `events` ADD INDEX `idx_events_is_featured` (`is_featured`)");
        }
    }

    public function down(): void
    {
        $this->pdo->exec("ALTER TABLE `events` DROP COLUMN `is_featured`");
    }
}
