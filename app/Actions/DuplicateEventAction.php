<?php

namespace App\Actions;

use Ace\Application;

class DuplicateEventAction
{
    protected string $error = '';

    public function getError(): string
    {
        return $this->error;
    }

    /**
     * Duplicate an existing event along with its ticket tiers and special guests.
     *
     * @param int|string $eventId The ID of the event to clone
     * @param int|string|null $organizerId Optional host ID constraint (null for admin)
     * @return int|false The new event ID if successful, false on failure
     */
    public function execute(int|string $eventId, int|string|null $organizerId = null): int|false
    {
        $db = Application::$app->db->pdo;

        // 1. Fetch original event
        $query = "SELECT * FROM events WHERE id = :id";
        $params = ['id' => (int)$eventId];
        if ($organizerId !== null) {
            $query .= " AND organizer_id = :org_id";
            $params['org_id'] = (int)$organizerId;
        }

        $stmt = $db->prepare($query);
        $stmt->execute($params);
        $original = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$original) {
            $this->error = 'Event not found or access denied.';
            return false;
        }

        // 2. Prepare cloned event data
        $clonedName = trim($original['name']) . ' (Copy)';

        // Ensure unique slug and custom_url
        $baseSlug = generate_event_slug($clonedName);
        $uniqueSlug = $this->ensureUniqueSlug($baseSlug);

        // Date calculation: if original event date is in the past, schedule for 7 days ahead at same time
        $origTime = strtotime($original['date'] ?? '');
        $now = time();
        if ($origTime && $origTime < $now) {
            $timeOfDay = date('H:i:s', $origTime);
            $newDate = date('Y-m-d', strtotime('+7 days')) . ' ' . $timeOfDay;
        } else {
            $newDate = !empty($original['date']) ? $original['date'] : date('Y-m-d H:i:s', strtotime('+7 days 18:00:00'));
        }

        try {
            $db->beginTransaction();

            // 3. Insert cloned event record
            $insertEventStmt = $db->prepare("
                INSERT INTO events (
                    organizer_id, name, description, date, location, image, 
                    status, pass_fee, slug, custom_url, category, 
                    flyer_page_enabled, is_featured, created_at, updated_at
                ) VALUES (
                    :organizer_id, :name, :description, :date, :location, :image, 
                    'draft', :pass_fee, :slug, :custom_url, :category, 
                    :flyer_page_enabled, 0, NOW(), NOW()
                )
            ");

            $insertEventStmt->execute([
                'organizer_id' => $original['organizer_id'],
                'name' => $clonedName,
                'description' => $original['description'] ?? '',
                'date' => $newDate,
                'location' => $original['location'] ?? '',
                'image' => $original['image'] ?? null,
                'pass_fee' => $original['pass_fee'] ?? 0,
                'slug' => $uniqueSlug,
                'custom_url' => $uniqueSlug,
                'category' => $original['category'] ?? 'Concerts',
                'flyer_page_enabled' => $original['flyer_page_enabled'] ?? 0,
            ]);

            $clonedEventId = (int)$db->lastInsertId();

            if (!$clonedEventId) {
                throw new \Exception("Failed to obtain ID for cloned event.");
            }

            // 4. Duplicate ticket types / tiers
            $tiersStmt = $db->prepare("SELECT * FROM ticket_types WHERE event_id = :event_id ORDER BY id ASC");
            $tiersStmt->execute(['event_id' => $original['id']]);
            $ticketTiers = $tiersStmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];

            $insertTierStmt = $db->prepare("
                INSERT INTO ticket_types (event_id, name, price, capacity, created_at)
                VALUES (:event_id, :name, :price, :capacity, NOW())
            ");

            foreach ($ticketTiers as $tier) {
                $insertTierStmt->execute([
                    'event_id' => $clonedEventId,
                    'name' => $tier['name'],
                    'price' => $tier['price'],
                    'capacity' => $tier['capacity']
                ]);
            }

            // 5. Duplicate special guests / speakers / performers
            $guestsStmt = $db->prepare("SELECT * FROM event_guests WHERE event_id = :event_id ORDER BY id ASC");
            $guestsStmt->execute(['event_id' => $original['id']]);
            $guests = $guestsStmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];

            $insertGuestStmt = $db->prepare("
                INSERT INTO event_guests (event_id, name, role, phone, social)
                VALUES (:event_id, :name, :role, :phone, :social)
            ");

            foreach ($guests as $g) {
                $insertGuestStmt->execute([
                    'event_id' => $clonedEventId,
                    'name' => $g['name'],
                    'role' => $g['role'] ?? '',
                    'phone' => $g['phone'] ?? '',
                    'social' => $g['social'] ?? ''
                ]);
            }

            $db->commit();
            return $clonedEventId;
        } catch (\Exception $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            error_log("DuplicateEventAction failed: " . $e->getMessage());
            $this->error = 'Failed to duplicate event: ' . $e->getMessage();
            return false;
        }
    }

    /**
     * Ensure unique custom_url / slug in events table.
     */
    private function ensureUniqueSlug(string $slug): string
    {
        $db = Application::$app->db->pdo;
        $originalSlug = $slug;
        $counter = 0;

        while (true) {
            $stmt = $db->prepare("SELECT COUNT(*) FROM events WHERE slug = :slug OR custom_url = :custom_url");
            $stmt->execute(['slug' => $slug, 'custom_url' => $slug]);

            if ($stmt->fetchColumn() == 0) {
                break;
            }

            $counter++;
            $slug = $originalSlug . '-' . $counter;
        }

        return $slug;
    }
}
