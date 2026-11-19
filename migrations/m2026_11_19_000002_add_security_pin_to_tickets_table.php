<?php

use Ace\Migration;

class m2026_11_19_000002_add_security_pin_to_tickets_table extends Migration
{
    public function up(): void
    {
        // 1. Add security_pin column if not already present
        $cols = $this->pdo->query("SHOW COLUMNS FROM `tickets`")->fetchAll(\PDO::FETCH_COLUMN);

        if (!in_array('security_pin', $cols)) {
            $this->pdo->exec("
                ALTER TABLE `tickets` 
                ADD COLUMN `security_pin` VARCHAR(10) NULL DEFAULT NULL AFTER `status_detail`,
                ADD INDEX `idx_tickets_security_pin` (`security_pin`)
            ");
        } else {
            // Ensure index exists
            try {
                $indices = $this->pdo->query("SHOW INDEX FROM `tickets` WHERE Key_name = 'idx_tickets_security_pin'")->fetchAll();
                if (empty($indices)) {
                    $this->pdo->exec("ALTER TABLE `tickets` ADD INDEX `idx_tickets_security_pin` (`security_pin`)");
                }
            } catch (\Throwable $e) {}
        }

        // 2. Backfill existing tickets that do not have a 4-digit security PIN
        try {
            $ticketsWithoutPin = $this->pdo->query("
                SELECT id FROM `tickets` 
                WHERE `security_pin` IS NULL OR `security_pin` = ''
            ")->fetchAll(\PDO::FETCH_COLUMN);

            if (!empty($ticketsWithoutPin)) {
                $upStmt = $this->pdo->prepare("UPDATE `tickets` SET `security_pin` = :pin WHERE `id` = :id");
                foreach ($ticketsWithoutPin as $ticketId) {
                    $pin = str_pad((string)random_int(1000, 9999), 4, '0', STR_PAD_LEFT);
                    $upStmt->execute(['pin' => $pin, 'id' => $ticketId]);
                }
            }
        } catch (\Throwable $e) {
            // Non-blocking backfill error
        }
    }

    public function down(): void
    {
        try {
            $this->pdo->exec("ALTER TABLE `tickets` DROP COLUMN `security_pin`");
        } catch (\Throwable $e) {}
    }
}
