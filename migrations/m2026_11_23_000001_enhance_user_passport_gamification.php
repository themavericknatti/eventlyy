<?php

use Ace\Migration;

class m2026_11_23_000001_enhance_user_passport_gamification extends Migration
{
    public function up(): void
    {
        // 1. Add XP, Level, and Prestige Tier tracking to users table
        $this->pdo->exec("
            ALTER TABLE `users`
            ADD COLUMN `passport_xp` INT UNSIGNED NOT NULL DEFAULT 0 AFTER `status`,
            ADD COLUMN `passport_level` INT UNSIGNED NOT NULL DEFAULT 1 AFTER `passport_xp`,
            ADD COLUMN `prestige_tier` INT UNSIGNED NOT NULL DEFAULT 0 AFTER `passport_level`,
            ADD COLUMN `prestige_unlocked_at` DATETIME NULL AFTER `prestige_tier`;
        ");

        // 2. Allow repetitive milestone/prestige badges by dropping strict single-badge unique key
        // We drop `uk_user_badge` (`user_id`, `badge_key`) and replace with an index to allow seasonal or prestige badges
        try {
            $this->pdo->exec("ALTER TABLE `user_passport_badges` DROP INDEX `uk_user_badge`;");
        } catch (\Throwable $e) {
            // Ignore if index doesn't exist
        }

        $this->pdo->exec("
            ALTER TABLE `user_passport_badges`
            ADD COLUMN `prestige_cycle` INT UNSIGNED NOT NULL DEFAULT 0 AFTER `event_id`,
            ADD INDEX `idx_user_badge_cycle` (`user_id`, `badge_key`, `prestige_cycle`);
        ");
    }

    public function down(): void
    {
        try {
            $this->pdo->exec("
                ALTER TABLE `users`
                DROP COLUMN `prestige_unlocked_at`,
                DROP COLUMN `prestige_tier`,
                DROP COLUMN `passport_level`,
                DROP COLUMN `passport_xp`;
            ");
        } catch (\Throwable $e) {}

        try {
            $this->pdo->exec("
                ALTER TABLE `user_passport_badges`
                DROP COLUMN `prestige_cycle`,
                DROP INDEX `idx_user_badge_cycle`,
                ADD UNIQUE KEY `uk_user_badge` (`user_id`, `badge_key`);
            ");
        } catch (\Throwable $e) {}
    }
}
