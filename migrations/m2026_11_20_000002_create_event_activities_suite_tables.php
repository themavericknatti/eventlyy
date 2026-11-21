<?php

use Ace\Migration;

class m2026_11_20_000002_create_event_activities_suite_tables extends Migration
{
    public function up(): void
    {
        // 1. Event Polls & Voting Contests
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS `event_polls` (
                `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
                `event_id` BIGINT NOT NULL,
                `title` VARCHAR(255) NOT NULL,
                `description` TEXT NULL,
                `poll_type` ENUM('free', 'paid') NOT NULL DEFAULT 'free',
                `vote_cost` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
                `allow_multiple_options` TINYINT(1) NOT NULL DEFAULT 0,
                `starts_at` DATETIME NULL,
                `ends_at` DATETIME NULL,
                `status` ENUM('active', 'closed', 'draft') NOT NULL DEFAULT 'active',
                `total_votes` INT UNSIGNED NOT NULL DEFAULT 0,
                `total_revenue` DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX `idx_polls_event` (`event_id`),
                INDEX `idx_polls_status` (`status`),
                FOREIGN KEY (`event_id`) REFERENCES `events`(`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");

        // 2. Poll Nominees / Options
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS `event_poll_options` (
                `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
                `poll_id` BIGINT NOT NULL,
                `title` VARCHAR(255) NOT NULL,
                `subtitle` VARCHAR(255) NULL,
                `image_url` VARCHAR(500) NULL,
                `votes_count` INT UNSIGNED NOT NULL DEFAULT 0,
                `display_order` INT NOT NULL DEFAULT 0,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX `idx_poll_opts_poll` (`poll_id`),
                FOREIGN KEY (`poll_id`) REFERENCES `event_polls`(`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");

        // 3. Poll Votes Cast
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS `event_poll_votes` (
                `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
                `poll_id` BIGINT NOT NULL,
                `option_id` BIGINT NOT NULL,
                `user_id` BIGINT NULL,
                `voter_name` VARCHAR(150) NULL,
                `voter_email` VARCHAR(255) NULL,
                `ip_address` VARCHAR(64) NULL,
                `votes_quantity` INT NOT NULL DEFAULT 1,
                `amount_paid` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
                `payment_reference` VARCHAR(100) NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX `idx_votes_poll` (`poll_id`),
                INDEX `idx_votes_opt` (`option_id`),
                INDEX `idx_votes_ip` (`poll_id`, `ip_address`),
                FOREIGN KEY (`poll_id`) REFERENCES `event_polls`(`id`) ON DELETE CASCADE,
                FOREIGN KEY (`option_id`) REFERENCES `event_poll_options`(`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");

        // 4. Event Trivia & Guess Game
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS `event_trivia` (
                `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
                `event_id` BIGINT NOT NULL,
                `title` VARCHAR(255) NOT NULL,
                `description` TEXT NULL,
                `pass_score` INT NOT NULL DEFAULT 3,
                `reward_promo_code` VARCHAR(64) NULL,
                `reward_description` VARCHAR(255) NULL,
                `time_limit_seconds` INT NOT NULL DEFAULT 60,
                `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
                `plays_count` INT UNSIGNED NOT NULL DEFAULT 0,
                `wins_count` INT UNSIGNED NOT NULL DEFAULT 0,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX `idx_trivia_event` (`event_id`),
                FOREIGN KEY (`event_id`) REFERENCES `events`(`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");

        // 5. Trivia Questions
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS `event_trivia_questions` (
                `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
                `trivia_id` BIGINT NOT NULL,
                `question` TEXT NOT NULL,
                `option_a` VARCHAR(255) NOT NULL,
                `option_b` VARCHAR(255) NOT NULL,
                `option_c` VARCHAR(255) NULL,
                `option_d` VARCHAR(255) NULL,
                `correct_option` CHAR(1) NOT NULL,
                `explanation` TEXT NULL,
                `display_order` INT NOT NULL DEFAULT 0,
                INDEX `idx_trivia_q_trivia` (`trivia_id`),
                FOREIGN KEY (`trivia_id`) REFERENCES `event_trivia`(`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");

        // 6. Trivia Submissions
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS `event_trivia_submissions` (
                `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
                `trivia_id` BIGINT NOT NULL,
                `user_id` BIGINT NULL,
                `player_name` VARCHAR(150) NOT NULL,
                `player_email` VARCHAR(255) NOT NULL,
                `score` INT NOT NULL DEFAULT 0,
                `total_questions` INT NOT NULL DEFAULT 0,
                `is_winner` TINYINT(1) NOT NULL DEFAULT 0,
                `time_taken_seconds` INT NOT NULL DEFAULT 0,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX `idx_trivia_sub_trivia` (`trivia_id`),
                FOREIGN KEY (`trivia_id`) REFERENCES `event_trivia`(`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");

        // 7. Interactive DJ Song Requests & Speaker Q&A
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS `event_requests` (
                `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
                `event_id` BIGINT NOT NULL,
                `user_id` BIGINT NULL,
                `type` ENUM('song', 'qa') NOT NULL DEFAULT 'song',
                `requester_name` VARCHAR(150) NOT NULL,
                `requester_email` VARCHAR(255) NULL,
                `title` VARCHAR(255) NOT NULL,
                `artist_or_speaker` VARCHAR(255) NULL,
                `note` TEXT NULL,
                `upvotes_count` INT UNSIGNED NOT NULL DEFAULT 1,
                `status` ENUM('pending', 'approved', 'played', 'hidden') NOT NULL DEFAULT 'approved',
                `is_pinned` TINYINT(1) NOT NULL DEFAULT 0,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX `idx_req_event` (`event_id`, `type`),
                FOREIGN KEY (`event_id`) REFERENCES `events`(`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");

        // 8. Request Upvotes
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS `event_request_upvotes` (
                `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
                `request_id` BIGINT NOT NULL,
                `user_id` BIGINT NULL,
                `ip_address` VARCHAR(64) NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY `uk_req_upvote_user` (`request_id`, `user_id`),
                INDEX `idx_req_upvote_ip` (`request_id`, `ip_address`),
                FOREIGN KEY (`request_id`) REFERENCES `event_requests`(`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");

        // 9. Community Hype Wall & Squad Finder
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS `event_community_posts` (
                `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
                `event_id` BIGINT NOT NULL,
                `user_id` BIGINT NULL,
                `author_name` VARCHAR(150) NOT NULL,
                `author_email` VARCHAR(255) NULL,
                `category` ENUM('hype', 'squad', 'carpool', 'general') NOT NULL DEFAULT 'hype',
                `message` TEXT NOT NULL,
                `location_area` VARCHAR(150) NULL,
                `contact_handle` VARCHAR(100) NULL,
                `is_ticket_holder` TINYINT(1) NOT NULL DEFAULT 0,
                `likes_count` INT UNSIGNED NOT NULL DEFAULT 0,
                `status` ENUM('visible', 'hidden') NOT NULL DEFAULT 'visible',
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX `idx_comm_event` (`event_id`, `category`),
                FOREIGN KEY (`event_id`) REFERENCES `events`(`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");

        // 10. Gamified Event Passports & Badges
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS `user_passport_badges` (
                `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
                `user_id` BIGINT NOT NULL,
                `badge_key` VARCHAR(64) NOT NULL,
                `badge_title` VARCHAR(150) NOT NULL,
                `badge_icon` VARCHAR(30) NOT NULL,
                `badge_desc` VARCHAR(255) NOT NULL,
                `event_id` BIGINT NULL,
                `reward_credits` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX `idx_badge_user` (`user_id`),
                UNIQUE KEY `uk_user_badge` (`user_id`, `badge_key`),
                FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");
    }

    public function down(): void
    {
        $this->pdo->exec("DROP TABLE IF EXISTS `user_passport_badges`");
        $this->pdo->exec("DROP TABLE IF EXISTS `event_community_posts`");
        $this->pdo->exec("DROP TABLE IF EXISTS `event_request_upvotes`");
        $this->pdo->exec("DROP TABLE IF EXISTS `event_requests`");
        $this->pdo->exec("DROP TABLE IF EXISTS `event_trivia_submissions`");
        $this->pdo->exec("DROP TABLE IF EXISTS `event_trivia_questions`");
        $this->pdo->exec("DROP TABLE IF EXISTS `event_trivia`");
        $this->pdo->exec("DROP TABLE IF EXISTS `event_poll_votes`");
        $this->pdo->exec("DROP TABLE IF EXISTS `event_poll_options`");
        $this->pdo->exec("DROP TABLE IF EXISTS `event_polls`");
    }
}
