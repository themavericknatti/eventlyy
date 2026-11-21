<?php

use Ace\Migration;

class m2026_11_20_000003_add_power_voting_to_event_polls extends Migration
{
    public function up(): void
    {
        // 1. Update poll_type ENUM to include 'power' and set default vote_cost to 100.00
        $this->pdo->exec("
            ALTER TABLE `event_polls` 
            MODIFY COLUMN `poll_type` ENUM('free', 'paid', 'power') NOT NULL DEFAULT 'power',
            MODIFY COLUMN `vote_cost` DECIMAL(10, 2) NOT NULL DEFAULT 100.00,
            ADD COLUMN IF NOT EXISTS `allow_power_votes` TINYINT(1) NOT NULL DEFAULT 1 AFTER `vote_cost`;
        ");

        // 2. Ensure existing polls have allow_power_votes = 1 and poll_type = 'power' if vote_cost was set
        $this->pdo->exec("
            UPDATE `event_polls` 
            SET `poll_type` = 'power', `vote_cost` = 100.00, `allow_power_votes` = 1 
            WHERE `vote_cost` <= 0.00;
        ");
    }

    public function down(): void
    {
        $this->pdo->exec("
            ALTER TABLE `event_polls` 
            MODIFY COLUMN `poll_type` ENUM('free', 'paid') NOT NULL DEFAULT 'free',
            MODIFY COLUMN `vote_cost` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
            DROP COLUMN IF EXISTS `allow_power_votes`;
        ");
    }
}
