<?php

namespace App\Actions;

use App\Models\Event;
use App\Models\TicketType;
use Ace\Application;

class UpdateEventAction
{
    /**
     * Update and validate existing event model and its ticket types.
     */
    public function execute(array $data, int|string $hostId, Event $event): bool
    {
        $db = Application::$app->db->pdo;

        // Ensure host owns this event or user is an admin
        $isAdmin = hasRole('admin') || hasRole('super-admin');
        if ($event->organizer_id != $hostId && !$isAdmin) {
            $event->addError('organizer_id', 'Unauthorized event modification attempt.');
            return false;
        }

        // 1. Combine date and time
        $eventDate = $data['event_date'] ?? '';
        $eventTime = $data['event_time'] ?? '';
        $dateTimeStr = ($eventDate && $eventTime) ? "{$eventDate} {$eventTime}:00" : ($event->date ?? '');

        // Pass fee checkbox setting
        $passFee = isset($data['pass_fee']) ? 1 : 0;

        // 2. Handle custom_url & secure slug
        $customUrl = trim($data['custom_url'] ?? '');
        $eventName = trim($data['event_name'] ?? '');

        $slug = !empty($event->slug) ? $event->slug : generate_event_slug($eventName ?: $event->name, $event->id);

        if (empty($customUrl)) {
            $customUrl = $slug;
        } else {
            $customUrl = slugify($customUrl);
        }

        // Ensure slug uniqueness (excluding current event ID)
        $customUrl = $this->ensureUniqueSlugExcludingCurrent($customUrl, $event->id);

        // Location formatting: Venue, City
        $venue = trim($data['event_venue'] ?? '');
        $city = trim($data['event_city'] ?? '');
        $location = ($venue && $city) ? "{$venue}, {$city}" : ($venue ?: $city);
        if (empty($location)) {
            $location = $event->location;
        }

        $oldImage = $event->image ?? '';
        $newImageUploaded = false;
        $imagePath = $oldImage;
        if (isset($_FILES['event_image']) && $_FILES['event_image']['error'] !== UPLOAD_ERR_NO_FILE) {
            $uploadService = new \Ace\UploadService();
            $targetDir = function_exists('public_dir') ? public_dir('uploads/events') : (Application::$ROOT_DIR . '/public/uploads/events');
            $result = $uploadService->uploadFile($_FILES['event_image'], $targetDir, ['jpg', 'jpeg', 'png', 'webp', 'gif'], 5242880, 1600, 1200, 82);

            if (!$result['success']) {
                $event->addError('image', 'Event cover image error: ' . $result['error']);
                return false;
            }

            $imagePath = '/uploads/events/' . $result['filename'];
            $newImageUploaded = true;
        }

        $category = trim($data['event_category'] ?? $data['category'] ?? $event->category ?? 'Concerts');

        $flyerPageEnabled = isset($data['flyer_page_enabled']) ? (!empty($data['flyer_page_enabled']) ? 1 : 0) : ($event->flyer_page_enabled ?? 0);

        $eventData = [
            'name' => $eventName ?: $event->name,
            'description' => $data['event_description'] ?? $event->description,
            'category' => $category,
            'date' => $dateTimeStr,
            'location' => $location,
            'pass_fee' => $passFee,
            'slug' => $slug,
            'custom_url' => $customUrl,
            'flyer_page_enabled' => $flyerPageEnabled,
            'image' => $imagePath
        ];

        $event->loadData($eventData);

        if (!$event->validate()) {
            return false;
        }

        try {
            $db->beginTransaction();

            // Direct SQL update to update events record
            $updateStmt = $db->prepare("
                UPDATE events 
                SET name = :name, 
                    description = :description, 
                    category = :category,
                    date = :date, 
                    location = :location, 
                    pass_fee = :pass_fee, 
                    slug = :slug,
                    custom_url = :custom_url,
                    flyer_page_enabled = :flyer_page_enabled,
                    image = :image,
                    updated_at = NOW()
                WHERE id = :id
            ");
            
            $success = $updateStmt->execute([
                'name' => $event->name,
                'description' => $event->description,
                'category' => $event->category,
                'date' => $event->date,
                'location' => $event->location,
                'pass_fee' => $event->pass_fee,
                'slug' => $slug,
                'custom_url' => $event->custom_url,
                'flyer_page_enabled' => $flyerPageEnabled,
                'image' => $event->image,
                'id' => $event->id
            ]);

            if (!$success) {
                $db->rollBack();
                $event->addError('id', 'Failed to update event in database.');
                return false;
            }

            // 3. Process Ticket Types update / insertion
            $ticketIds = $data['ticket-id'] ?? [];
            $ticketNames = $data['ticket-name'] ?? [];
            $ticketPrices = $data['ticket-price'] ?? [];
            $ticketQtys = $data['ticket-qty'] ?? [];
            $ticketAdmits = $data['ticket-admits'] ?? [];
            $ticketMaxPerPerson = $data['ticket-max-per-person'] ?? [];

            $submittedIds = [];

            foreach ($ticketNames as $index => $name) {
                $name = trim($name);
                if (empty($name)) continue;

                $price = isset($ticketPrices[$index]) ? (float)$ticketPrices[$index] : 0.00;
                $qty = isset($ticketQtys[$index]) ? (int)$ticketQtys[$index] : 0;
                $existingId = isset($ticketIds[$index]) ? (int)$ticketIds[$index] : 0;
                $admits = isset($ticketAdmits[$index]) && is_numeric($ticketAdmits[$index]) ? max(1, (int)$ticketAdmits[$index]) : 1;
                $maxPerPerson = isset($ticketMaxPerPerson[$index]) && is_numeric($ticketMaxPerPerson[$index]) && (int)$ticketMaxPerPerson[$index] > 0 ? (int)$ticketMaxPerPerson[$index] : null;

                // Smart detection: e.g. "Table for 5" or "Booth for 6"
                if ($admits === 1 && preg_match('/(?:table|booth|group|cabana)\s+(?:for|of)\s+(\d+)/i', $name, $matches)) {
                    $admits = max(1, (int)$matches[1]);
                }

                if ($existingId > 0) {
                    // Verify capacity is not reduced below existing sold/issued tickets
                    $takenStmt = $db->prepare("
                        SELECT COUNT(*) FROM tickets 
                        WHERE ticket_type_id = :id AND status IN ('Paid', 'Comped')
                    ");
                    $takenStmt->execute(['id' => $existingId]);
                    $takenCount = (int)$takenStmt->fetchColumn();

                    if ($qty < $takenCount) {
                        $db->rollBack();
                        $event->addError('id', "Capacity for ticket tier '{$name}' cannot be reduced below {$takenCount} (the number of tickets already sold or issued).");
                        return false;
                    }

                    // Update existing ticket type
                    $stmt = $db->prepare("
                        UPDATE ticket_types 
                        SET name = :name, price = :price, capacity = :capacity, admits = :admits, max_per_person = :max_per_person 
                        WHERE id = :id AND event_id = :event_id
                    ");
                    $stmt->execute([
                        'name' => $name,
                        'price' => $price,
                        'capacity' => $qty,
                        'admits' => $admits,
                        'max_per_person' => $maxPerPerson,
                        'id' => $existingId,
                        'event_id' => $event->id
                    ]);
                    $submittedIds[] = $existingId;
                } else {
                    // Insert new ticket type
                    $stmt = $db->prepare("
                        INSERT INTO ticket_types (event_id, name, price, capacity, admits, max_per_person) 
                        VALUES (:event_id, :name, :price, :capacity, :admits, :max_per_person)
                    ");
                    $stmt->execute([
                        'event_id' => $event->id,
                        'name' => $name,
                        'price' => $price,
                        'capacity' => $qty,
                        'admits' => $admits,
                        'max_per_person' => $maxPerPerson
                    ]);
                    $submittedIds[] = (int)$db->lastInsertId();
                }
            }

            // Clean up deleted ticket types that have 0 sold tickets
            if (!empty($submittedIds)) {
                $inClause = implode(',', array_map('intval', $submittedIds));
                $deleteStmt = $db->prepare("
                    DELETE FROM ticket_types 
                    WHERE event_id = :event_id 
                      AND id NOT IN ({$inClause})
                      AND (SELECT COUNT(*) FROM tickets t WHERE t.ticket_type_id = ticket_types.id AND t.status IN ('Paid', 'Comped')) = 0
                ");
                $deleteStmt->execute(['event_id' => $event->id]);
            }

            // 4. Process Special Guests (replace all)
            $guestNames = $data['guest-name'] ?? [];
            $guestRoles = $data['guest-role'] ?? [];
            $guestPhones = $data['guest-phone'] ?? [];
            $guestSocials = $data['guest-social'] ?? [];

            // Delete existing guests for this event and re-insert
            $db->prepare("DELETE FROM event_guests WHERE event_id = :event_id")->execute(['event_id' => $event->id]);

            foreach ($guestNames as $index => $gName) {
                $gName = trim($gName);
                if (empty($gName)) continue;

                $stmt = $db->prepare("INSERT INTO event_guests (event_id, name, role, phone, social) VALUES (:event_id, :name, :role, :phone, :social)");
                $stmt->execute([
                    'event_id' => $event->id,
                    'name' => $gName,
                    'role' => trim($guestRoles[$index] ?? ''),
                    'phone' => trim($guestPhones[$index] ?? ''),
                    'social' => trim($guestSocials[$index] ?? '')
                ]);
            }

            $db->commit();

            // Clean up old image from disk if a new one was uploaded
            if ($newImageUploaded && !empty($oldImage) && $oldImage !== $imagePath) {
                (new DeleteEventAction())->deleteImageFile($oldImage);
            }

            return true;
        } catch (\Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            \Ace\Logger::error("Event update failed for event {$event->id}: " . $e->getMessage());
            $event->addError('id', 'An unexpected database error occurred while saving: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Ensure slug uniqueness excluding current event.
     */
    private function ensureUniqueSlugExcludingCurrent(string $slug, int|string $currentEventId): string
    {
        $db = Application::$app->db->pdo;
        $originalSlug = $slug;
        $counter = 0;

        while (true) {
            $stmt = $db->prepare("SELECT COUNT(*) FROM events WHERE custom_url = :slug AND id != :id");
            $stmt->execute(['slug' => $slug, 'id' => $currentEventId]);

            if ($stmt->fetchColumn() == 0) {
                break;
            }

            $counter++;
            $slug = $originalSlug . '-' . $counter;
        }

        return $slug;
    }
}
