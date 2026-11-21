<?php

namespace App\Services;

use Ace\Application;
use Ace\UploadService;
use PDO;

/**
 * SponsorService
 *
 * Manages platform-level sponsorship partnerships, brand representations,
 * infinite ticker tape displays, and text/image styling configurations.
 *
 * @package Eventlyy\Services
 * @version 1.0.0
 */
class SponsorService
{
    protected PDO $db;

    public function __construct()
    {
        if (isset(Application::$app->db->pdo)) {
            $this->db = Application::$app->db->pdo;
        } else {
            $rootDir = defined('ROOT_DIR') ? ROOT_DIR : dirname(__DIR__, 2);
            $dbConfig = require $rootDir . '/config/database.php';
            $dsn = "mysql:host=" . ($dbConfig['host'] ?? '127.0.0.1') . 
                   ";port=" . ($dbConfig['port'] ?? '3306') . 
                   ";dbname=" . ($dbConfig['database'] ?? 'eventlyy') . ";charset=utf8mb4";
            $this->db = new PDO(
                $dsn,
                $dbConfig['username'] ?? 'root',
                $dbConfig['password'] ?? '',
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
        }
    }

    /**
     * Get all active sponsors for public display (marquee / tape).
     *
     * @return array
     */
    public function getActiveSponsors(): array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM platform_sponsors 
            WHERE is_active = 1 
            ORDER BY sort_order ASC, id ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Get all sponsors for admin management.
     *
     * @return array
     */
    public function getAllSponsors(): array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM platform_sponsors 
            ORDER BY sort_order ASC, id ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Find single sponsor by ID.
     *
     * @param int $id
     * @return array|null
     */
    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM platform_sponsors WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Create a new sponsor.
     *
     * @param array $data
     * @param array|null $file
     * @return array [success => bool, message => string, id => ?int]
     */
    public function createSponsor(array $data, ?array $file = null): array
    {
        $name = trim($data['name'] ?? '');
        $type = in_array($data['type'] ?? '', ['image', 'text']) ? $data['type'] : 'image';
        $websiteUrl = trim($data['website_url'] ?? '') ?: null;
        $badgeText = trim($data['badge_text'] ?? '') ?: null;
        $sortOrder = (int)($data['sort_order'] ?? 0);
        $isActive = isset($data['is_active']) ? (int)(bool)$data['is_active'] : 1;

        if (empty($name)) {
            return ['success' => false, 'message' => 'Sponsor name is required.'];
        }

        $imageUrl = null;
        $textContent = null;
        $textColor = '#ffffff';
        $textFont = 'sans';
        $textStyle = 'bold';

        if ($type === 'image') {
            // Check file upload first
            if ($file && !empty($file['name']) && $file['error'] === UPLOAD_ERR_OK) {
                $uploadRes = $this->handleLogoUpload($file);
                if (!$uploadRes['success']) {
                    return ['success' => false, 'message' => $uploadRes['error']];
                }
                $imageUrl = $uploadRes['url'];
            } elseif (!empty($data['image_url'])) {
                $imageUrl = trim($data['image_url']);
            }

            if (empty($imageUrl)) {
                return ['success' => false, 'message' => 'Please upload a sponsor logo or provide an image URL.'];
            }
        } else {
            // Text sponsor
            $textContent = trim($data['text_content'] ?? '') ?: $name;
            $textColor = $this->sanitizeColor($data['text_color'] ?? '#ffffff');
            $textFont = in_array($data['text_font'] ?? '', ['sans', 'serif', 'display', 'mono', 'script']) ? $data['text_font'] : 'sans';
            $textStyle = in_array($data['text_style'] ?? '', ['bold', 'black', 'italic', 'black_italic', 'bold_caps', 'neon']) ? $data['text_style'] : 'bold';
        }

        $stmt = $this->db->prepare("
            INSERT INTO platform_sponsors 
            (name, type, image_url, text_content, text_color, text_font, text_style, badge_text, website_url, sort_order, is_active, created_at, updated_at)
            VALUES (:name, :type, :image_url, :text_content, :text_color, :text_font, :text_style, :badge_text, :website_url, :sort_order, :is_active, NOW(), NOW())
        ");
        $stmt->execute([
            'name' => $name,
            'type' => $type,
            'image_url' => $imageUrl,
            'text_content' => $textContent,
            'text_color' => $textColor,
            'text_font' => $textFont,
            'text_style' => $textStyle,
            'badge_text' => $badgeText,
            'website_url' => $websiteUrl,
            'sort_order' => $sortOrder,
            'is_active' => $isActive
        ]);

        return ['success' => true, 'message' => 'Sponsor added successfully!', 'id' => (int)$this->db->lastInsertId()];
    }

    /**
     * Update an existing sponsor.
     *
     * @param int $id
     * @param array $data
     * @param array|null $file
     * @return array
     */
    public function updateSponsor(int $id, array $data, ?array $file = null): array
    {
        $existing = $this->find($id);
        if (!$existing) {
            return ['success' => false, 'message' => 'Sponsor not found.'];
        }

        $name = trim($data['name'] ?? $existing['name']);
        $type = in_array($data['type'] ?? '', ['image', 'text']) ? $data['type'] : $existing['type'];
        $websiteUrl = trim($data['website_url'] ?? '') ?: null;
        $badgeText = trim($data['badge_text'] ?? '') ?: null;
        $sortOrder = isset($data['sort_order']) ? (int)$data['sort_order'] : (int)$existing['sort_order'];
        $isActive = isset($data['is_active']) ? (int)(bool)$data['is_active'] : (int)$existing['is_active'];

        if (empty($name)) {
            return ['success' => false, 'message' => 'Sponsor name cannot be empty.'];
        }

        $imageUrl = $existing['image_url'];
        $textContent = $existing['text_content'];
        $textColor = $existing['text_color'];
        $textFont = $existing['text_font'];
        $textStyle = $existing['text_style'];

        if ($type === 'image') {
            if ($file && !empty($file['name']) && $file['error'] === UPLOAD_ERR_OK) {
                $uploadRes = $this->handleLogoUpload($file);
                if (!$uploadRes['success']) {
                    return ['success' => false, 'message' => $uploadRes['error']];
                }
                $this->deleteLocalFile($existing['image_url']);
                $imageUrl = $uploadRes['url'];
            } elseif (!empty($data['image_url'])) {
                $newUrl = trim($data['image_url']);
                if ($newUrl !== $existing['image_url']) {
                    $this->deleteLocalFile($existing['image_url']);
                    $imageUrl = $newUrl;
                }
            }

            if (empty($imageUrl)) {
                return ['success' => false, 'message' => 'Please upload a sponsor logo or provide an image link.'];
            }
        } else {
            $textContent = trim($data['text_content'] ?? '') ?: $name;
            $textColor = $this->sanitizeColor($data['text_color'] ?? '#ffffff');
            $textFont = in_array($data['text_font'] ?? '', ['sans', 'serif', 'display', 'mono', 'script']) ? $data['text_font'] : 'sans';
            $textStyle = in_array($data['text_style'] ?? '', ['bold', 'black', 'italic', 'black_italic', 'bold_caps', 'neon']) ? $data['text_style'] : 'bold';
        }

        $stmt = $this->db->prepare("
            UPDATE platform_sponsors 
            SET name = :name,
                type = :type,
                image_url = :image_url,
                text_content = :text_content,
                text_color = :text_color,
                text_font = :text_font,
                text_style = :text_style,
                badge_text = :badge_text,
                website_url = :website_url,
                sort_order = :sort_order,
                is_active = :is_active,
                updated_at = NOW()
            WHERE id = :id
        ");
        $stmt->execute([
            'id' => $id,
            'name' => $name,
            'type' => $type,
            'image_url' => $imageUrl,
            'text_content' => $textContent,
            'text_color' => $textColor,
            'text_font' => $textFont,
            'text_style' => $textStyle,
            'badge_text' => $badgeText,
            'website_url' => $websiteUrl,
            'sort_order' => $sortOrder,
            'is_active' => $isActive
        ]);

        return ['success' => true, 'message' => 'Sponsor updated successfully!'];
    }

    /**
     * Delete a sponsor.
     *
     * @param int $id
     * @return array
     */
    public function deleteSponsor(int $id): array
    {
        $existing = $this->find($id);
        if (!$existing) {
            return ['success' => false, 'message' => 'Sponsor not found.'];
        }

        if (!empty($existing['image_url'])) {
            $this->deleteLocalFile($existing['image_url']);
        }

        $stmt = $this->db->prepare("DELETE FROM platform_sponsors WHERE id = :id");
        $stmt->execute(['id' => $id]);

        return ['success' => true, 'message' => 'Sponsor deleted successfully.'];
    }

    /**
     * Toggle sponsor active status.
     *
     * @param int $id
     * @return array
     */
    public function toggleStatus(int $id): array
    {
        $existing = $this->find($id);
        if (!$existing) {
            return ['success' => false, 'message' => 'Sponsor not found.'];
        }

        $newStatus = $existing['is_active'] ? 0 : 1;
        $stmt = $this->db->prepare("UPDATE platform_sponsors SET is_active = :status, updated_at = NOW() WHERE id = :id");
        $stmt->execute(['status' => $newStatus, 'id' => $id]);

        return ['success' => true, 'is_active' => $newStatus, 'message' => $newStatus ? 'Sponsor activated.' : 'Sponsor paused.'];
    }

    /**
     * Reorder sponsors.
     *
     * @param array $orderedIds [id1, id2, ...]
     * @return bool
     */
    public function reorder(array $orderedIds): bool
    {
        $stmt = $this->db->prepare("UPDATE platform_sponsors SET sort_order = :order WHERE id = :id");
        foreach ($orderedIds as $index => $id) {
            $stmt->execute(['order' => $index + 1, 'id' => (int)$id]);
        }
        return true;
    }

    /**
     * Handle logo file upload.
     */
    protected function handleLogoUpload(array $file): array
    {
        $uploadService = new UploadService();
        $targetDir = public_dir('assets/img/sponsors/');
        UploadService::ensureDirectory($targetDir);

        $allowedExtensions = ['png', 'jpg', 'jpeg', 'webp', 'svg', 'gif'];
        $maxBytes = 3 * 1024 * 1024; // 3MB

        $res = $uploadService->uploadFile($file, $targetDir, $allowedExtensions, $maxBytes, 800, 400, 90);
        if (!$res['success']) {
            return ['success' => false, 'error' => $res['error'] ?? 'Upload failed.'];
        }

        return ['success' => true, 'url' => 'assets/img/sponsors/' . $res['filename']];
    }

    /**
     * Clean up a local asset file.
     */
    protected function deleteLocalFile(?string $path): void
    {
        if (empty($path) || str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return;
        }

        $fullPath = public_dir(ltrim($path, '/'));
        if (is_file($fullPath)) {
            @unlink($fullPath);
        }
    }

    /**
     * Sanitize color to valid hex code.
     */
    protected function sanitizeColor(?string $color): string
    {
        $color = trim((string)$color);
        if (preg_match('/^#[a-f0-9]{3,8}$/i', $color)) {
            return $color;
        }
        return '#ffffff';
    }
}
