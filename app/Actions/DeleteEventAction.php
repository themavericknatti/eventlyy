<?php

namespace App\Actions;

use Ace\Application;

class DeleteEventAction
{
    /**
     * Delete an event by ID (or its entire recurring series) including its associated image file and child records.
     *
     * @param int|string $eventId
     * @param int|string|null $organizerId Optional host ID constraint
     * @param bool $deleteSeries If true and event is part of a series, delete all events in the series
     * @return bool
     */
    public function execute(int|string $eventId, int|string|null $organizerId = null, bool $deleteSeries = false): bool
    {
        if (!$eventId) {
            return false;
        }

        $db = Application::$app->db->pdo;

        // 1. Fetch event to obtain details and verify ownership if host ID is provided
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

        // If user requested deleting the whole recurring series
        if ($deleteSeries && !empty($event['recurrence_group_id'])) {
            return $this->deleteSeries($event['recurrence_group_id'], $organizerId);
        }

        return $this->deleteSingleEvent($event);
    }

    /**
     * Delete all events in a recurring series.
     *
     * @param string $recurrenceGroupId
     * @param int|string|null $organizerId
     * @return bool
     */
    public function deleteSeries(string $recurrenceGroupId, int|string|null $organizerId = null): bool
    {
        $db = Application::$app->db->pdo;
        $query = "SELECT * FROM events WHERE recurrence_group_id = :gid";
        $params = ['gid' => $recurrenceGroupId];
        if ($organizerId !== null) {
            $query .= " AND organizer_id = :org_id";
            $params['org_id'] = $organizerId;
        }

        $stmt = $db->prepare($query);
        $stmt->execute($params);
        $events = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];

        if (empty($events)) {
            return false;
        }

        $allDeleted = true;
        foreach ($events as $evt) {
            if (!$this->deleteSingleEvent($evt)) {
                $allDeleted = false;
            }
        }

        return $allDeleted;
    }

    /**
     * Internal helper to delete an individual event record and its relationships.
     */
    protected function deleteSingleEvent(array $event): bool
    {
        $db = Application::$app->db->pdo;
        $eventId = $event['id'];

        // Clean up associated relational data
        $db->prepare("DELETE FROM ticket_types WHERE event_id = :event_id")->execute(['event_id' => $eventId]);
        $db->prepare("DELETE FROM tickets WHERE event_id = :event_id")->execute(['event_id' => $eventId]);
        $db->prepare("DELETE FROM event_staff_assignments WHERE event_id = :event_id")->execute(['event_id' => $eventId]);
        $db->prepare("DELETE FROM promo_codes WHERE event_id = :event_id")->execute(['event_id' => $eventId]);
        $db->prepare("DELETE FROM event_reviews WHERE event_id = :event_id")->execute(['event_id' => $eventId]);
        $db->prepare("DELETE FROM payouts WHERE event_id = :event_id AND status = 'pending'")->execute(['event_id' => $eventId]);

        // Delete the event database record
        $deleteStmt = $db->prepare("DELETE FROM events WHERE id = :id");
        $deleted = $deleteStmt->execute(['id' => $eventId]);

        // Safely delete the event image from disk if DB delete succeeded
        // Check if other events in DB are sharing this exact image path before deleting
        if ($deleted && !empty($event['image'])) {
            $countStmt = $db->prepare("SELECT COUNT(*) FROM events WHERE image = :img");
            $countStmt->execute(['img' => $event['image']]);
            if ((int)$countStmt->fetchColumn() === 0) {
                $this->deleteImageFile($event['image']);
            }
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
