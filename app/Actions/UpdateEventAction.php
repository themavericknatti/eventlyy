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

        $flyerPageEnabled = !empty($data['flyer_page_enabled']) ? 1 : 0;

        // Recurrence parameters & conversion check
        $wasRecurring = !empty($event->is_recurring) && !empty($event->recurrence_group_id);
        $convertRecurring = !$wasRecurring && !empty($data['is_recurring']);

        $occurrences = [];
        $recurrenceGroupId = $event->recurrence_group_id ?? null;
        $totalOccurrences = $event->recurrence_count ?? null;
        $recurrenceType = $event->recurrence_type ?? 'none';
        $recurrenceInterval = $event->recurrence_interval ?? 1;
        $recurrenceEndDate = $event->recurrence_end_date ?? null;
        $isRecurring = $event->is_recurring ?? 0;
        $recurrenceSequence = $event->recurrence_sequence ?? 1;

        if ($convertRecurring) {
            $recType = in_array($data['recurrence_type'] ?? '', ['daily', 'weekly', 'biweekly', 'monthly']) 
                ? $data['recurrence_type'] 
                : 'weekly';
            $recInterval = max(1, min(52, (int)($data['recurrence_interval'] ?? 1)));
            $recEndType = $data['recurrence_end_type'] ?? 'count';
            $recCount = isset($data['recurrence_count']) && is_numeric($data['recurrence_count']) 
                ? max(2, min(52, (int)$data['recurrence_count'])) 
                : 4;
            $recEndDate = !empty($data['recurrence_end_date']) ? date('Y-m-d 23:59:59', strtotime($data['recurrence_end_date'])) : null;

            try {
                $startDateObj = new \DateTime($dateTimeStr);
                $occurrences[] = $startDateObj->format('Y-m-d H:i:s');
                $maxOccurrences = 52;
                $targetCount = ($recEndType === 'count') ? $recCount : $maxOccurrences;
                $currentDate = clone $startDateObj;

                for ($i = 2; $i <= $targetCount; $i++) {
                    if ($recType === 'daily') {
                        $currentDate->modify("+{$recInterval} day");
                    } elseif ($recType === 'weekly') {
                        $currentDate->modify("+{$recInterval} week");
                    } elseif ($recType === 'biweekly') {
                        $weeks = $recInterval * 2;
                        $currentDate->modify("+{$weeks} week");
                    } elseif ($recType === 'monthly') {
                        $currentDate->modify("+{$recInterval} month");
                    }

                    if ($recEndType === 'date' && $recEndDate) {
                        if ($currentDate->getTimestamp() > strtotime($recEndDate)) {
                            break;
                        }
                    }

                    $occurrences[] = $currentDate->format('Y-m-d H:i:s');
                }

                $calcTotal = count($occurrences);
                if ($calcTotal > 1) {
                    $isRecurring = 1;
                    $recurrenceType = $recType;
                    $recurrenceInterval = $recInterval;
                    $recurrenceEndDate = $recEndDate;
                    $totalOccurrences = $calcTotal;
                    $recurrenceGroupId = 'REC-' . strtoupper(bin2hex(random_bytes(6)));
                    $recurrenceSequence = 1;
                }
            } catch (\Exception $e) {
                // Ignore schedule generation errors and proceed as non-recurring
            }
        }

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
            'image' => $imagePath,
            'is_recurring' => $isRecurring,
            'recurrence_type' => $recurrenceType,
            'recurrence_interval' => $recurrenceInterval,
            'recurrence_end_date' => $recurrenceEndDate,
            'recurrence_count' => $totalOccurrences,
            'recurrence_group_id' => $recurrenceGroupId,
            'recurrence_sequence' => $recurrenceSequence
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
                    is_recurring = :is_recurring,
                    recurrence_type = :recurrence_type,
                    recurrence_interval = :recurrence_interval,
                    recurrence_end_date = :recurrence_end_date,
                    recurrence_count = :recurrence_count,
                    recurrence_group_id = :recurrence_group_id,
                    recurrence_sequence = :recurrence_sequence,
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
                'is_recurring' => $isRecurring,
                'recurrence_type' => $recurrenceType,
                'recurrence_interval' => $recurrenceInterval,
                'recurrence_end_date' => $recurrenceEndDate,
                'recurrence_count' => $totalOccurrences,
                'recurrence_group_id' => $recurrenceGroupId,
                'recurrence_sequence' => $recurrenceSequence,
                'id' => $event->id
            ]);

            if (!$success) {
                $db->rollBack();
                $event->addError('id', 'Failed to update event in database.');
                return false;
            }

            // Propagate changes to future occurrences if requested
            $recurrenceUpdateScope = $data['recurrence_update_scope'] ?? 'this_only';
            if ($wasRecurring && $recurrenceUpdateScope === 'all_future' && !empty($event->recurrence_group_id)) {
                $futureStmt = $db->prepare("
                    UPDATE events 
                    SET name = :name, 
                        description = :description, 
                        category = :category,
                        location = :location, 
                        pass_fee = :pass_fee,
                        flyer_page_enabled = :flyer_page_enabled,
                        image = :image,
                        updated_at = NOW()
                    WHERE recurrence_group_id = :group_id 
                      AND recurrence_sequence > :current_sequence
                ");
                $futureStmt->execute([
                    'name' => $event->name,
                    'description' => $event->description,
                    'category' => $event->category,
                    'location' => $event->location,
                    'pass_fee' => $event->pass_fee,
                    'flyer_page_enabled' => $flyerPageEnabled,
                    'image' => $event->image,
                    'group_id' => $event->recurrence_group_id,
                    'current_sequence' => (int)($event->recurrence_sequence ?? 1)
                ]);
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
                $admits = isset($ticketAdmits[$index]) && is_numeric($ticketAdmits[$index]) ? max(1, (int)$ticketAdmits[$index]) : 1;
                $maxPerPerson = isset($ticketMaxPerPerson[$index]) && is_numeric($ticketMaxPerPerson[$index]) && (int)$ticketMaxPerPerson[$index] > 0 ? (int)$ticketMaxPerPerson[$index] : null;

                // Auto-detect table/booth capacity if admits wasn't manually set
                if ($admits === 1 && preg_match('/(?:table|booth|group|cabana)\s+(?:for|of)\s+(\d+)/i', $name, $matches)) {
                    $admits = max(1, (int)$matches[1]);
                }

                $existingId = $ticketIds[$index] ?? null;

                if ($existingId && is_numeric($existingId)) {
                    // Update existing tier
                    $updateTierStmt = $db->prepare("
                        UPDATE ticket_types 
                        SET name = :name, 
                            price = :price, 
                            capacity = :capacity, 
                            admits = :admits, 
                            max_per_person = :max_per_person 
                        WHERE id = :id AND event_id = :event_id
                    ");
                    $updateTierStmt->execute([
                        'name' => $name,
                        'price' => $price,
                        'capacity' => $qty,
                        'admits' => $admits,
                        'max_per_person' => $maxPerPerson,
                        'id' => $existingId,
                        'event_id' => $event->id
                    ]);
                    $submittedIds[] = (int)$existingId;
                } else {
                    // Create newly added tier
                    $insertTierStmt = $db->prepare("
                        INSERT INTO ticket_types (event_id, name, price, capacity, admits, max_per_person) 
                        VALUES (:event_id, :name, :price, :capacity, :admits, :max_per_person)
                    ");
                    $insertTierStmt->execute([
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

            // 5. If converted to recurring series, spawn future occurrences (seq 2..N)
            if ($convertRecurring && count($occurrences) > 1) {
                // Fetch current ticket types for cloning
                $currTicketsStmt = $db->prepare("SELECT name, price, capacity, admits, max_per_person FROM ticket_types WHERE event_id = :event_id");
                $currTicketsStmt->execute(['event_id' => $event->id]);
                $clonableTickets = $currTicketsStmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];

                // Fetch current guests for cloning
                $currGuestsStmt = $db->prepare("SELECT name, role, phone, social FROM event_guests WHERE event_id = :event_id");
                $currGuestsStmt->execute(['event_id' => $event->id]);
                $clonableGuests = $currGuestsStmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];

                for ($seq = 2; $seq <= $totalOccurrences; $seq++) {
                    $occDate = $occurrences[$seq - 1];
                    $dateObj = new \DateTime($occDate);
                    $dateSuffix = $dateObj->format('Y-m-d');
                    $occSlug = generate_event_slug(($eventName ?: $event->name) . '-' . $dateSuffix);
                    $occCustomUrl = $this->ensureUniqueSlugExcludingCurrent($customUrl . '-' . $dateSuffix, 0);

                    $occEvent = new Event();
                    $occEventData = [
                        'name' => $eventName ?: $event->name,
                        'description' => $data['event_description'] ?? $event->description,
                        'category' => $category,
                        'date' => $occDate,
                        'location' => $location,
                        'organizer_id' => $event->organizer_id,
                        'status' => 'published',
                        'pass_fee' => $passFee,
                        'slug' => $occSlug,
                        'custom_url' => $occCustomUrl,
                        'flyer_page_enabled' => $flyerPageEnabled,
                        'image' => $imagePath,
                        'is_recurring' => 1,
                        'recurrence_type' => $recurrenceType,
                        'recurrence_interval' => $recurrenceInterval,
                        'recurrence_end_date' => $recurrenceEndDate,
                        'recurrence_count' => $totalOccurrences,
                        'recurrence_group_id' => $recurrenceGroupId,
                        'recurrence_sequence' => $seq
                    ];

                    $occEvent->loadData($occEventData);
                    if ($occEvent->save()) {
                        // Clone ticket types
                        foreach ($clonableTickets as $tRow) {
                            $stmt = $db->prepare("INSERT INTO ticket_types (event_id, name, price, capacity, admits, max_per_person) VALUES (:event_id, :name, :price, :capacity, :admits, :max_per_person)");
                            $stmt->execute([
                                'event_id' => $occEvent->id,
                                'name' => $tRow['name'],
                                'price' => $tRow['price'],
                                'capacity' => $tRow['capacity'],
                                'admits' => $tRow['admits'],
                                'max_per_person' => $tRow['max_per_person']
                            ]);
                        }

                        // Clone special guests
                        foreach ($clonableGuests as $gRow) {
                            $stmt = $db->prepare("INSERT INTO event_guests (event_id, name, role, phone, social) VALUES (:event_id, :name, :role, :phone, :social)");
                            $stmt->execute([
                                'event_id' => $occEvent->id,
                                'name' => $gRow['name'],
                                'role' => $gRow['role'],
                                'phone' => $gRow['phone'],
                                'social' => $gRow['social']
                            ]);
                        }
                    }
                }
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
