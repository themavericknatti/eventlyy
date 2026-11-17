<?php

use Ace\Migration;

class m2026_11_17_000001_add_expired_status_to_event_staff_requests extends Migration
{
    public function up(): void
    {
        // 1. Expand the ENUM to include 'expired'
        $this->pdo->exec("
            ALTER TABLE `event_staff_requests` 
            MODIFY COLUMN `status` ENUM('pending', 'partially_fulfilled', 'fulfilled', 'declined', 'expired') 
            NOT NULL DEFAULT 'pending'
        ");

        // 2. Automatically mark existing unfulfilled requests for ended/completed events as 'expired'
        $completionHours = function_exists('event_completion_hours') ? event_completion_hours() : 12;
        $this->pdo->exec("
            UPDATE `event_staff_requests` esr
            INNER JOIN `events` e ON esr.event_id = e.id
            SET esr.status = 'expired', esr.updated_at = NOW()
            WHERE esr.status IN ('pending', 'partially_fulfilled')
              AND (
                  e.status IN ('completed', 'past', 'cancelled')
                  OR (e.date IS NOT NULL AND e.date < NOW() - INTERVAL " . (int)$completionHours . " HOUR)
              )
        ");
    }

    public function down(): void
    {
        // Revert any 'expired' requests back to 'declined' before shrinking enum
        $this->pdo->exec("
            UPDATE `event_staff_requests` 
            SET `status` = 'declined' 
            WHERE `status` = 'expired'
        ");

        $this->pdo->exec("
            ALTER TABLE `event_staff_requests` 
            MODIFY COLUMN `status` ENUM('pending', 'partially_fulfilled', 'fulfilled', 'declined') 
            NOT NULL DEFAULT 'pending'
        ");
    }
}
