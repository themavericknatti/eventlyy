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
     * Delete all events in a recurring series and completely purge the series images directory.
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

        // Collect all distinct images used in this series
        $seriesImages = [];
        foreach ($events as $evt) {
            if (!empty($evt['image'])) {
                $seriesImages[] = $evt['image'];
            }
        }
        $seriesImages = array_unique($seriesImages);

        $allDeleted = true;
        foreach ($events as $evt) {
            if (!$this->deleteSingleEventRecord($evt)) {
                $allDeleted = false;
            }
        }

        // Clean up any legacy or external images if they are no longer referenced by any remaining event
        foreach ($seriesImages as $img) {
            $this->deleteImageFileIfUnused($img);
        }

        // Completely remove the entire recurring series directory and any images/files stored inside it
        $this->deleteSeriesDirectory($recurrenceGroupId);

        return $allDeleted;
    }

    /**
     * Internal helper to delete an individual event record, its relationships, and its unshared image.
     */
    protected function deleteSingleEvent(array $event): bool
    {
        $deleted = $this->deleteSingleEventRecord($event);

        if ($deleted) {
            // Safely delete this occurrence's image ONLY if no other event in the database is using it
            if (!empty($event['image'])) {
                $this->deleteImageFileIfUnused($event['image']);
            }

            // If this occurrence belonged to a recurrence group, check if any occurrences remain
            if (!empty($event['recurrence_group_id'])) {
                $db = Application::$app->db->pdo;
                $remainStmt = $db->prepare("SELECT COUNT(*) FROM events WHERE recurrence_group_id = :gid");
                $remainStmt->execute(['gid' => $event['recurrence_group_id']]);
                if ((int)$remainStmt->fetchColumn() === 0) {
                    // No occurrences left in the series, purge the entire series folder
                    $this->deleteSeriesDirectory($event['recurrence_group_id']);
                }
            }
        }

        return $deleted;
    }

    /**
     * Delete child relational records and the event record from the database.
     */
    protected function deleteSingleEventRecord(array $event): bool
    {
        $db = Application::$app->db->pdo;
        $eventId = $event['id'];

        // Clean up associated relational data
        $db->prepare("DELETE FROM ticket_types WHERE event_id = :event_id")->execute(['event_id' => $eventId]);
        $db->prepare("DELETE FROM tickets WHERE event_id = :event_id")->execute(['event_id' => $eventId]);
        $db->prepare("DELETE FROM event_staff_assignments WHERE event_id = :event_id")->execute(['event_id' => $eventId]);
        $db->prepare("DELETE FROM promo_codes WHERE event_id = :event_id")->execute(['event_id' => $eventId]);
        $db->prepare("DELETE FROM event_reviews WHERE event_id = :event_id")->execute(['event_id' => $eventId]);
        $db->prepare("DELETE FROM event_guests WHERE event_id = :event_id")->execute(['event_id' => $eventId]);
        $db->prepare("DELETE FROM event_referrals WHERE event_id = :event_id")->execute(['event_id' => $eventId]);
        $db->prepare("DELETE FROM payouts WHERE event_id = :event_id AND status = 'pending'")->execute(['event_id' => $eventId]);

        // Delete the event database record
        $deleteStmt = $db->prepare("DELETE FROM events WHERE id = :id");
        return (bool)$deleteStmt->execute(['id' => $eventId]);
    }

    /**
     * Safely delete a local image file from disk ONLY if no other event references it.
     *
     * @param string|null $imagePath
     * @param int|string $excludeEventId Optional event ID to exclude from reference check
     */
    public function deleteImageFileIfUnused(?string $imagePath, int|string $excludeEventId = 0): void
    {
        if (empty($imagePath)) {
            return;
        }

        // Do not attempt to delete remote CDN or external URLs (e.g. Unsplash placeholders)
        if (str_starts_with($imagePath, 'http://') || str_starts_with($imagePath, 'https://')) {
            return;
        }

        $db = Application::$app->db->pdo;
        $query = "SELECT COUNT(*) FROM events WHERE image = :img";
        $params = ['img' => $imagePath];
        if ($excludeEventId > 0) {
            $query .= " AND id != :ex_id";
            $params['ex_id'] = $excludeEventId;
        }

        $stmt = $db->prepare($query);
        $stmt->execute($params);
        $count = (int)$stmt->fetchColumn();

        if ($count === 0) {
            $this->deleteImageFile($imagePath);
        }
    }

    /**
     * Safely delete a local image file from disk unconditionally.
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

    /**
     * Recursively delete an entire recurring series folder and all files/images within it.
     *
     * @param string $recurrenceGroupId
     * @return bool
     */
    public function deleteSeriesDirectory(string $recurrenceGroupId): bool
    {
        $cleanGid = preg_replace('/[^a-zA-Z0-9_\-]/', '', $recurrenceGroupId);
        if (empty($cleanGid)) {
            return false;
        }

        $dir = function_exists('public_dir') 
            ? public_dir('uploads/events/' . $cleanGid) 
            : (Application::$ROOT_DIR . '/public/uploads/events/' . $cleanGid);

        if (!is_dir($dir)) {
            return false;
        }

        return $this->deleteDirectoryRecursively($dir);
    }

    /**
     * Recursive filesystem folder purge.
     */
    protected function deleteDirectoryRecursively(string $dir): bool
    {
        if (!is_dir($dir)) {
            return false;
        }

        $items = @scandir($dir);
        if ($items !== false) {
            foreach ($items as $item) {
                if ($item === '.' || $item === '..') {
                    continue;
                }
                $path = $dir . DIRECTORY_SEPARATOR . $item;
                if (is_dir($path) && !is_link($path)) {
                    $this->deleteDirectoryRecursively($path);
                } else {
                    @unlink($path);
                }
            }
        }

        return @rmdir($dir);
    }
}
