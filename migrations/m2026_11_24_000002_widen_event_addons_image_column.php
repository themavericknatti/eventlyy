<?php

use Ace\Migration;

class m2026_11_24_000002_widen_event_addons_image_column extends Migration
{
    public function up(): void
    {
        $this->pdo->exec("
            ALTER TABLE `event_addons` 
            MODIFY `image` TEXT NULL;
        ");
    }

    public function down(): void
    {
        $this->pdo->exec("
            ALTER TABLE `event_addons` 
            MODIFY `image` VARCHAR(255) NULL;
        ");
    }
}
