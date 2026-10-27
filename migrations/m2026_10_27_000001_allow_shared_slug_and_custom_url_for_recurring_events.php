<?php

use Ace\Migration;

class m2026_10_27_000001_allow_shared_slug_and_custom_url_for_recurring_events extends Migration
{
    public function up(): void
    {
        // Change slug and custom_url unique indexes to regular non-unique indexes
        // so occurrences within the same recurring series can share the exact same clean URL.
        try {
            $this->pdo->exec("ALTER TABLE `events` DROP INDEX `slug`");
        } catch (\Throwable $e) {}

        try {
            $this->pdo->exec("ALTER TABLE `events` ADD INDEX `idx_events_slug` (`slug`)");
        } catch (\Throwable $e) {}

        try {
            $this->pdo->exec("ALTER TABLE `events` DROP INDEX `custom_url`");
        } catch (\Throwable $e) {}

        try {
            $this->pdo->exec("ALTER TABLE `events` ADD INDEX `idx_events_custom_url` (`custom_url`)");
        } catch (\Throwable $e) {}
    }

    public function down(): void
    {
        try {
            $this->pdo->exec("ALTER TABLE `events` DROP INDEX `idx_events_slug`");
            $this->pdo->exec("ALTER TABLE `events` ADD UNIQUE INDEX `slug` (`slug`)");
        } catch (\Throwable $e) {}

        try {
            $this->pdo->exec("ALTER TABLE `events` DROP INDEX `idx_events_custom_url`");
            $this->pdo->exec("ALTER TABLE `events` ADD UNIQUE INDEX `custom_url` (`custom_url`)");
        } catch (\Throwable $e) {}
    }
}
