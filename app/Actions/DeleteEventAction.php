<?php

namespace App\Actions;

use Ace\Application;

class DeleteEventAction
{
    /**
     * Delete an event by ID including its associated image file and child records.
     *
     * @param int|string $eventId
     * @param int|string|null $organizerId Optional host ID constraint
     * @return bool
     */
    public function execute(int|string $eventId, int|string|null $organizerId = null): bool
    {
        if (!$eventId) {
            return false;
        }

        $db = Application::$app->db->pdo;

        // 1. Fetch event to obtain image path and verify ownership if host ID is provided
        $query = "SELECT * FROM events WHERE id = :id";
        $params = ['id' => $eventId];
        if ($organizerId !== null) {
            $query .= " AND organizer_id = :org_id";
            $params['org_id'] = $organizerId;
        }

        $stmt = $db->prepare($query);
        $stmt->execute($params);
        $event = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$event) {
            return false;
        }

        // 2. Clean up associated relational data
        $db->prepare("DELETE FROM ticket_types WHERE event_id = :event_id")->execute(['event_id' => $eventId]);
        $db->prepare("DELETE FROM tickets WHERE event_id = :event_id")->execute(['event_id' => $eventId]);
        $db->prepare("DELETE FROM event_staff_assignments WHERE event_id = :event_id")->execute(['event_id' => $eventId]);
        $db->prepare("DELETE FROM promo_codes WHERE event_id = :event_id")->execute(['event_id' => $eventId]);
        $db->prepare("DELETE FROM event_reviews WHERE event_id = :event_id")->execute(['event_id' => $eventId]);
        $db->prepare("DELETE FROM payouts WHERE event_id = :event_id AND status = 'pending'")->execute(['event_id' => $eventId]);

        // 3. Delete the event database record
        $deleteStmt = $db->prepare("DELETE FROM events WHERE id = :id");
        $deleted = $deleteStmt->execute(['id' => $eventId]);

        // 4. Safely delete the event image from disk if DB delete succeeded
        if ($deleted && !empty($event['image'])) {
            $this->deleteImageFile($event['image']);
        }

        return (bool)$deleted;
    }

    /**
     * Safely delete a local image file from disk.
     *
     * @param string|null $imagePath
     */
    public function deleteImageFile(?string $imagePath): void
    {
        if (empty($imagePath)) {
            return;
        }

        // Do not attempt to delete remote CDN or external URLs (e.g. Unsplash placeholders)
        if (str_starts_with($imagePath, 'http://') || str_starts_with($imagePath, 'https://')) {
            return;
        }

        $cleanPath = ltrim($imagePath, '/\\');
        $fullPath = function_exists('public_dir') ? public_dir($cleanPath) : (Application::$ROOT_DIR . '/' . $cleanPath);

        if (file_exists($fullPath) && is_file($fullPath)) {
            @unlink($fullPath);
        }
    }
}
