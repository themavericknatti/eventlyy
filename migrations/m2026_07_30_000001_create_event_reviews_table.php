<?php

use Ace\Migration;

class m2026_07_30_000001_create_event_reviews_table extends Migration
{
    public function up(): void
    {
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS `event_reviews` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `event_id` INT NOT NULL,
                `organizer_id` INT NOT NULL,
                `ticket_id` INT DEFAULT NULL,
                `attendee_name` VARCHAR(255) NOT NULL,
                `attendee_email` VARCHAR(255) NOT NULL,
                `rating` TINYINT NOT NULL DEFAULT 5,
                `comment` TEXT DEFAULT NULL,
                `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                INDEX (`event_id`),
                INDEX (`organizer_id`),
                UNIQUE KEY `unique_event_attendee_review` (`event_id`, `attendee_email`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");
    }

    public function down(): void
    {
        $this->pdo->exec("DROP TABLE IF EXISTS `event_reviews`;");
    }
}
