<?php

use Ace\Migration;

class m2026_07_23_000001_create_event_staff_assignments_table extends Migration
{
    public function up(): void
    {
        // 1. Add status column to event_staff if it doesn't exist
        $this->pdo->exec("
            ALTER TABLE `event_staff` 
            ADD COLUMN IF NOT EXISTS `status` ENUM('active', 'inactive') DEFAULT 'active' AFTER `email`;
        ");

        // 2. Create event_staff_assignments table
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS `event_staff_assignments` (
                `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
                `staff_id` BIGINT NOT NULL,
                `event_id` BIGINT NOT NULL,
                `role_id` INT NOT NULL,
                `assigned_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY `unique_staff_event_role` (`staff_id`, `event_id`),
                FOREIGN KEY (`staff_id`) REFERENCES `event_staff`(`id`) ON DELETE CASCADE,
                FOREIGN KEY (`event_id`) REFERENCES `events`(`id`) ON DELETE CASCADE,
                FOREIGN KEY (`role_id`) REFERENCES `roles`(`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");

        // 3. Migrate existing assignments if event_id column exists
        try {
            $hasEventId = $this->pdo->query("SHOW COLUMNS FROM `event_staff` LIKE 'event_id'")->fetch();
            if ($hasEventId) {
                // Get default staff role ID from roles table
                $defaultRoleStmt = $this->pdo->query("SELECT id FROM `roles` WHERE `slug` = 'organizer-staff' LIMIT 1");
                $defaultRoleId = $defaultRoleStmt->fetchColumn();
                if (!$defaultRoleId) {
                    $defaultRoleId = $this->pdo->query("SELECT id FROM `roles` LIMIT 1")->fetchColumn() ?: 1;
                }

                $existingStaff = $this->pdo->query("SELECT id, event_id FROM `event_staff` WHERE `event_id` IS NOT NULL")->fetchAll();
                $insertStmt = $this->pdo->prepare("
                    INSERT IGNORE INTO `event_staff_assignments` (`staff_id`, `event_id`, `role_id`) 
                    VALUES (:staff_id, :event_id, :role_id)
                ");

                foreach ($existingStaff as $row) {
                    $insertStmt->execute([
                        'staff_id' => $row['id'],
                        'event_id' => $row['event_id'],
                        'role_id' => $defaultRoleId
                    ]);
                }

                // Drop foreign key and column from event_staff
                try {
                    $this->pdo->exec("ALTER TABLE `event_staff` DROP FOREIGN KEY `event_staff_ibfk_1`;");
                } catch (\Throwable $e) {
                    // Ignore if FK constraint name differs
                }
                $this->pdo->exec("ALTER TABLE `event_staff` DROP COLUMN `event_id`, DROP COLUMN IF EXISTS `role`;");
            }
        } catch (\Throwable $e) {
            // Log or ignore during migrations if column already altered
        }
    }

    public function down(): void
    {
        $this->pdo->exec("DROP TABLE IF EXISTS `event_staff_assignments`");
        try {
            $this->pdo->exec("ALTER TABLE `event_staff` ADD COLUMN `role` VARCHAR(100) NULL, ADD COLUMN `event_id` BIGINT NULL");
        } catch (\Throwable $e) {}
    }
}
