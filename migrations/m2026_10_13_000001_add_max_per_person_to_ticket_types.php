<?php

use Ace\Migration;

class m2026_10_13_000001_add_max_per_person_to_ticket_types extends Migration
{
    public function up(): void
    {
        $cols = $this->pdo->query("SHOW COLUMNS FROM `ticket_types`")->fetchAll(\PDO::FETCH_COLUMN);
        if (!in_array('max_per_person', $cols)) {
            $this->pdo->exec("ALTER TABLE `ticket_types` ADD COLUMN `max_per_person` INT UNSIGNED DEFAULT NULL AFTER `admits`");
        }
    }

    public function down(): void
    {
        $this->pdo->exec("ALTER TABLE `ticket_types` DROP COLUMN IF EXISTS `max_per_person`");
    }
}
