<?php

use Ace\Migration;

class m2026_08_25_000001_update_payouts_status_enum extends Migration
{
    public function up(): void
    {
        $this->pdo->exec("
            ALTER TABLE `payouts` 
            MODIFY COLUMN `status` ENUM('pending', 'processed', 'completed', 'failed') DEFAULT 'pending'
        ");
    }

    public function down(): void
    {
        $this->pdo->exec("
            ALTER TABLE `payouts` 
            MODIFY COLUMN `status` ENUM('pending', 'processed', 'failed') DEFAULT 'pending'
        ");
    }
}
