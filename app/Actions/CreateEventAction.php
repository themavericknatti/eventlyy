<?php

namespace App\Actions;

use App\Models\Event;
use App\Models\TicketType;
use Ace\Application;

class CreateEventAction
{
    /**
     * Create and validate event model and ticket types.
     */
    public function execute(array $data, int|string $hostId, Event $event): bool
    {
        // 1. Combine date and time
        $eventDate = $data['event_date'] ?? '';
        $eventTime = $data['event_time'] ?? '';
        $dateTimeStr = ($eventDate && $eventTime) ? "{$eventDate} {$eventTime}:00" : '';

        // Check if pass_fee is set in input (checkbox)
        $passFee = isset($data['pass_fee']) ? 1 : 0;

        // 2. Handle custom_url & secure slug
        $customUrl = trim($data['custom_url'] ?? '');
        $eventName = $data['event_name'] ?? '';

        $slug = generate_event_slug($eventName);

        if (empty($customUrl)) {
            // Auto-generate from event name
            $customUrl = $slug;
        } else {
            // Sanitize user-provided custom URL
            $customUrl = slugify($customUrl);
        }

        // Ensure uniqueness by appending suffix if needed
        $customUrl = $this->ensureUniqueSlug($customUrl);

        // Handle event image upload via UploadService
        $imagePath = '';
        if (isset($_FILES['event_image']) && $_FILES['event_image']['error'] !== UPLOAD_ERR_NO_FILE) {
            $uploadService = new \Ace\UploadService();
            $targetDir = function_exists('public_dir') ? public_dir('uploads/events') : (Application::$ROOT_DIR . '/public/uploads/events');
            $result = $uploadService->uploadFile($_FILES['event_image'], $targetDir, ['jpg', 'jpeg', 'png', 'webp', 'gif'], 5242880, 1600, 1200, 82);

            if (!$result['success']) {
                $event->addError('image', 'Event cover image error: ' . $result['error']);
                return false;
            }

            $imagePath = '/uploads/events/' . $result['filename'];
        }

        $category = trim($data['event_category'] ?? $data['category'] ?? 'Concerts');

		$flyerPageEnabled = !empty($data['flyer_page_enabled']) ? 1 : 0;

        $eventData = [
            'name' => $eventName,
            'description' => $data['event_description'] ?? '',
            'category' => $category,
            'date' => $dateTimeStr,
            'location' => ($data['event_venue'] ?? '') . ', ' . ($data['event_city'] ?? ''),
            'organizer_id' => $hostId,
            'status' => 'published', // auto-publish for now
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

        if (!$event->save()) {
            $event->addError('id', 'Failed to save event to database.');
            return false;
        }

        // 3. Save ticket types
        $db = Application::$app->db->pdo;
        $ticketNames = $data['ticket-name'] ?? [];
        $ticketPrices = $data['ticket-price'] ?? [];
        $ticketQtys = $data['ticket-qty'] ?? [];
        $ticketAdmits = $data['ticket-admits'] ?? [];
        $ticketMaxPerPerson = $data['ticket-max-per-person'] ?? [];

        foreach ($ticketNames as $index => $name) {
            $name = trim((string)$name);
            if (empty($name)) continue;

            $price = $ticketPrices[$index] ?? 0.00;
            $qty = $ticketQtys[$index] ?? 0;
            $admits = isset($ticketAdmits[$index]) && is_numeric($ticketAdmits[$index]) ? max(1, (int)$ticketAdmits[$index]) : 1;
            $maxPerPerson = isset($ticketMaxPerPerson[$index]) && is_numeric($ticketMaxPerPerson[$index]) && (int)$ticketMaxPerPerson[$index] > 0 ? (int)$ticketMaxPerPerson[$index] : null;

            // Smart detection: e.g. "VIP Table for 5" or "Table of 8"
            if ($admits === 1 && preg_match('/(?:table|booth|group|cabana)\s+(?:for|of)\s+(\d+)/i', $name, $matches)) {
                $admits = max(1, (int)$matches[1]);
            }

            // Using model insertion pattern
            $ticketType = new TicketType();
            $ticketType->loadData([
                'event_id' => $event->id,
                'name' => $name,
                'price' => $price,
                'capacity' => $qty
            ]);

            if ($ticketType->validate()) {
                $stmt = $db->prepare("INSERT INTO ticket_types (event_id, name, price, capacity, admits, max_per_person) VALUES (:event_id, :name, :price, :capacity, :admits, :max_per_person)");
                $stmt->execute([
                    'event_id' => $event->id,
                    'name' => $name,
                    'price' => $price,
                    'capacity' => $qty,
                    'admits' => $admits,
                    'max_per_person' => $maxPerPerson
                ]);
            }
        }

        // 4. Save special guests
        $guestNames = $data['guest-name'] ?? [];
        $guestRoles = $data['guest-role'] ?? [];
        $guestPhones = $data['guest-phone'] ?? [];
        $guestSocials = $data['guest-social'] ?? [];

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

		// 5. Save staffing assistance request if opted-in
        if (!empty($data['request_staffing'])) {
            $staffRolesInput = $data['staff_roles'] ?? [];
            $rolesRequested = [];
            
            $roleMeta = [
                'ticket-scanner' => ['name' => 'Ticket Scanner', 'icon' => '🎫', 'role_id' => 5],
                'organizer-staff' => ['name' => 'Organizer Staff / Usher', 'icon' => '🏷️', 'role_id' => 4],
                'customer-support' => ['name' => 'Customer Support Lead', 'icon' => '🎧', 'role_id' => 8],
                'technical-ops' => ['name' => 'Technical Operations', 'icon' => '⚙️', 'role_id' => 9],
                'finance-manager' => ['name' => 'Finance & Box Office', 'icon' => '💼', 'role_id' => 7],
            ];

            foreach ($staffRolesInput as $slug => $item) {
                if (!empty($item['selected'])) {
                    $qty = max(1, min(20, (int)($item['qty'] ?? 1)));
                    $meta = $roleMeta[$slug] ?? ['name' => ucwords(str_replace('-', ' ', $slug)), 'icon' => '👤', 'role_id' => 4];
                    $rolesRequested[] = [
                        'slug' => $slug,
                        'role_id' => $meta['role_id'],
                        'name' => $meta['name'],
                        'icon' => $meta['icon'],
                        'quantity' => $qty,
                    ];
                }
            }

            if (!empty($rolesRequested)) {
                $notes = trim($data['staff_notes'] ?? '');
                $stmt = $db->prepare("INSERT INTO event_staff_requests (event_id, host_id, roles_requested, notes, status) VALUES (:event_id, :host_id, :roles_requested, :notes, 'pending')");
                $stmt->execute([
                    'event_id' => $event->id,
                    'host_id' => $hostId,
                    'roles_requested' => json_encode($rolesRequested),
                    'notes' => $notes,
                ]);
            }
        }

        return true;
    }

    /**
     * Ensure a slug is unique in the events table.
     * Appends -1, -2, etc. if collisions are found.
     */
    private function ensureUniqueSlug(string $slug): string
    {
        $db = Application::$app->db->pdo;
        $originalSlug = $slug;
        $counter = 0;

        while (true) {
            $stmt = $db->prepare("SELECT COUNT(*) FROM events WHERE custom_url = :slug");
            $stmt->execute(['slug' => $slug]);

            if ($stmt->fetchColumn() == 0) {
                break;
            }

            $counter++;
            $slug = $originalSlug . '-' . $counter;
        }

        return $slug;
    }
}

