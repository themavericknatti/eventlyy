<?php

use Ace\Migration;

class m2026_07_31_000002_add_completed_status_to_events extends Migration
{
    public function up(): void
    {
        $this->pdo->exec("
            ALTER TABLE `events` 
            MODIFY COLUMN `status` ENUM('draft', 'published', 'cancelled', 'completed') DEFAULT 'draft'
        ");
    }

    public function down(): void
    {
        $this->pdo->exec("
            ALTER TABLE `events` 
            MODIFY COLUMN `status` ENUM('draft', 'published', 'cancelled') DEFAULT 'draft'
        ");
    }
}
