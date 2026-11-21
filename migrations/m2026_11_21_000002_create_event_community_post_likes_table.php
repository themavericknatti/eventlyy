<?php

use Ace\Migration;

class m2026_11_21_000002_create_event_community_post_likes_table extends Migration
{
    public function up(): void
    {
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS `event_community_post_likes` (
                `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
                `post_id` BIGINT NOT NULL,
                `user_id` BIGINT NULL,
                `ip_address` VARCHAR(45) NOT NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX `idx_post_user` (`post_id`, `user_id`),
                INDEX `idx_post_ip` (`post_id`, `ip_address`),
                FOREIGN KEY (`post_id`) REFERENCES `event_community_posts`(`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
    }

    public function down(): void
    {
        $this->pdo->exec("DROP TABLE IF EXISTS `event_community_post_likes`;");
    }
}
