<?php

use Ace\Migration;

class m2026_11_21_000001_create_platform_sponsors_table extends Migration
{
    public function up(): void
    {
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS `platform_sponsors` (
                `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `name` VARCHAR(150) NOT NULL,
                `type` ENUM('image', 'text') NOT NULL DEFAULT 'image',
                `image_url` VARCHAR(500) NULL,
                `text_content` VARCHAR(255) NULL,
                `text_color` VARCHAR(30) NOT NULL DEFAULT '#ffffff',
                `text_font` VARCHAR(50) NOT NULL DEFAULT 'sans',
                `text_style` VARCHAR(50) NOT NULL DEFAULT 'bold',
                `badge_text` VARCHAR(60) NULL,
                `website_url` VARCHAR(500) NULL,
                `sort_order` INT NOT NULL DEFAULT 0,
                `is_active` TINYINT(1) NOT NULL DEFAULT 1,
                `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX `idx_active_order` (`is_active`, `sort_order`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        // Seed initial sample sponsors showcasing diverse styling presets
        $this->pdo->exec("
            INSERT INTO `platform_sponsors` 
            (`name`, `type`, `image_url`, `text_content`, `text_color`, `text_font`, `text_style`, `badge_text`, `website_url`, `sort_order`, `is_active`, `created_at`, `updated_at`)
            VALUES 
            ('Red Bull', 'text', NULL, 'RED BULL', '#ff1e42', 'display', 'black_italic', 'ENERGY PARTNER', 'https://redbull.com', 1, 1, NOW(), NOW()),
            ('Spotify', 'text', NULL, 'Spotify', '#1db954', 'sans', 'bold', 'AUDIO STREAMING', 'https://spotify.com', 2, 1, NOW(), NOW()),
            ('Pioneer DJ', 'text', NULL, 'PIONEER DJ', '#00d2ff', 'mono', 'black', 'OFFICIAL AUDIO GEAR', 'https://pioneerdj.com', 3, 1, NOW(), NOW()),
            ('Heineken', 'text', NULL, 'HEINEKEN ★', '#00c853', 'display', 'bold_caps', 'BEVERAGE PATRON', 'https://heineken.com', 4, 1, NOW(), NOW()),
            ('Vogue', 'text', NULL, 'VOGUE', '#facc15', 'serif', 'bold_caps', 'OFFICIAL MEDIA', 'https://vogue.com', 5, 1, NOW(), NOW()),
            ('Beats', 'text', NULL, 'beats', '#e11d48', 'sans', 'black', 'SOUND PARTNER', 'https://beatsbydre.com', 6, 1, NOW(), NOW()),
            ('Solana', 'text', NULL, 'SOLANA', '#9945ff', 'display', 'neon', 'COMMUNITY PARTNER', 'https://solana.com', 7, 1, NOW(), NOW())
            ON DUPLICATE KEY UPDATE `id`=`id`;
        ");
    }

    public function down(): void
    {
        $this->pdo->exec("DROP TABLE IF EXISTS `platform_sponsors`;");
    }
}
