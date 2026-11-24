<?php

/**
 * HostEventController
 *
 * This controller handles host event-related routes including event creation, management, editing, and manual ticket issuance.
 *
 * @package Evently
 * @subpackage Controllers/HostEvent
 * @author Your Name <Celio Natti>
 * @license MIT License
 * @version 1.0.0
 */

namespace App\Controllers\Host;

use Ace\Application;
use Ace\Controller;
use Ace\Request;
use App\Middlewares\HostMiddleware;
use App\Models\Event;
use App\Models\Ticket;
use App\Services\EventService;
use App\Actions\CreateEventAction;
use App\Actions\UpdateEventAction;
use App\Actions\IssueTicketAction;
use App\Actions\SendFeedbackRequestAction;

class HostEventController extends Controller
{
    protected EventService $eventService;
    protected CreateEventAction $createEventAction;
    protected UpdateEventAction $updateEventAction;
    protected IssueTicketAction $issueTicketAction;
    protected SendFeedbackRequestAction $sendFeedbackRequestAction;

    public function __construct()
    {
        // Register the HostMiddleware for all methods in this controller
        $this->registerMiddleware(new HostMiddleware());
        $this->eventService = new EventService();
        $this->createEventAction = new CreateEventAction();
        $this->updateEventAction = new UpdateEventAction();
        $this->issueTicketAction = new IssueTicketAction();
        $this->sendFeedbackRequestAction = new SendFeedbackRequestAction();
    }

    protected array $categories = [
        'Concerts',
        'Tech',
        'Comedy',
        'Sports',
        'Theater',
        'Family',
        'Art & Wine',
        'Parties & Festivals',
        'Workshops & Classes',
        'Networking'
    ];

    public function createEvent()
    {
        // Render the create event view for hosts
        return $this->render('host/create-event', [
            'title' => 'Create Event',
            'categories' => $this->categories
        ]);
    }

    public function storeEvent(Request $request)
    {
        $user = Application::$app->user;
        $data = $request->getBody();
        $event = new Event();

        if ($this->createEventAction->execute($data, $user->id, $event)) {
            Application::$app->session->setFlash('success', 'Event successfully created! Now configure your ticket options, seating arrangements, and guest lineup.');
            return Application::$app->response->redirect('/host/event/' . $event->id . '/tickets-setup');
        }

        Application::$app->session->setFlash('error', 'Event validation failed: ' . implode(', ', $event->getErrorMessages()));
        return $this->render('host/create-event', [
            'title' => 'Create Event',
            'model' => $event,
            'categories' => $this->categories
        ]);
    }

    private function isEventCompleted(Event $event): bool
    {
        $eventDate = strtotime($event->date ?? '');
        $liveWindowEnd = $eventDate ? $eventDate + (event_completion_hours() * 3600) : 0;
        return ($eventDate && time() > $liveWindowEnd) || in_array($event->status ?? '', ['completed', 'past']);
    }

    /**
     * Dedicated Step 2 / Studio: Tickets, Seating Arrangements, Lineup & Addons Hub
     */
    public function ticketsSetup(Request $request, int|string $id)
    {
        $user = Application::$app->user;
        $isAdmin = (method_exists($user, 'isAdmin') && $user->isAdmin()) || (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) || hasRole('admin') || hasRole('super-admin');
        $event = $this->eventService->findHostEvent($id, $user->id, $isAdmin);
        if (!$event) {
            throw new \Exception("Event not found or access denied.", 404);
        }

        if ($this->isEventCompleted($event)) {
            Application::$app->session->setFlash('error', 'Completed events cannot be edited.');
            return Application::$app->response->redirect('/host/event/management/' . $event->id);
        }

        // Fetch ticket types breakdown via EventService
        $ticketTypes = $this->eventService->getEventTicketTypesBreakdown($event->id);

        // Fetch existing special guests
        $db = Application::$app->db->pdo;
        $guestStmt = $db->prepare("SELECT * FROM event_guests WHERE event_id = :event_id ORDER BY id ASC");
        $guestStmt->execute(['event_id' => $event->id]);
        $eventGuests = $guestStmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];

        // Fetch existing add-ons & merchandise
        $addonStmt = $db->prepare("SELECT * FROM event_addons WHERE event_id = :event_id AND status != 'deleted' ORDER BY id ASC");
        $addonStmt->execute(['event_id' => $event->id]);
        $eventAddons = $addonStmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];

        return $this->render('host/tickets-setup', [
            'title' => 'Tickets & Seating Setup — ' . $event->name,
            'event' => $event,
            'ticketTypes' => $ticketTypes,
            'eventGuests' => $eventGuests,
            'eventAddons' => $eventAddons,
            'isAdmin' => $isAdmin
        ]);
    }

    /**
     * Save all ticket tiers, seating sections, special guests, and addons
     */
    public function saveTicketsSetup(Request $request, int|string $id)
    {
        $user = Application::$app->user;
        $isAdmin = (method_exists($user, 'isAdmin') && $user->isAdmin()) || (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) || hasRole('admin') || hasRole('super-admin');
        $event = $this->eventService->findHostEvent($id, $user->id, $isAdmin);
        if (!$event) {
            throw new \Exception("Event not found or access denied.", 404);
        }

        if ($this->isEventCompleted($event)) {
            Application::$app->session->setFlash('error', 'Completed events cannot be edited.');
            return Application::$app->response->redirect('/host/event/management/' . $event->id);
        }

        $db = Application::$app->db->pdo;
        $data = $request->getBody();

        // 1. Process Ticket Types
        $ticketIds = $data['ticket-id'] ?? [];
        $ticketNames = $data['ticket-name'] ?? [];
        $ticketPrices = $data['ticket-price'] ?? [];
        $ticketQtys = $data['ticket-qty'] ?? [];
        $ticketAdmits = $data['ticket-admits'] ?? [];
        $ticketMaxPerPerson = $data['ticket-max-per-person'] ?? [];
        $ticketKinds = $data['ticket-kind'] ?? [];
        $ticketSections = $data['ticket-section'] ?? [];
        $ticketRows = $data['ticket-row'] ?? [];
        $ticketSeatPrefixes = $data['ticket-seat-prefix'] ?? [];
        $ticketSeatStarts = $data['ticket-seat-start'] ?? [];
        $ticketSeatEnds = $data['ticket-seat-end'] ?? [];
        $ticketHasSeatings = $data['ticket-has-seating'] ?? [];
        $ticketDescriptions = $data['ticket-description'] ?? [];

        $submittedIds = [];

        foreach ($ticketNames as $index => $name) {
            $name = trim((string)$name);
            if (empty($name)) continue;

            $price = isset($ticketPrices[$index]) ? max(0, (float)$ticketPrices[$index]) : 0.00;
            $qty = isset($ticketQtys[$index]) ? max(0, (int)$ticketQtys[$index]) : 0;
            $admits = isset($ticketAdmits[$index]) && is_numeric($ticketAdmits[$index]) ? max(1, (int)$ticketAdmits[$index]) : 1;
            $maxPerPerson = isset($ticketMaxPerPerson[$index]) && is_numeric($ticketMaxPerPerson[$index]) && (int)$ticketMaxPerPerson[$index] > 0 ? (int)$ticketMaxPerPerson[$index] : null;

            $kind = in_array($ticketKinds[$index] ?? '', ['standard', 'duo', 'group', 'seated']) ? $ticketKinds[$index] : 'standard';
            if ($kind === 'duo') {
                $admits = max(2, $admits);
            }

            $sectionName = trim((string)($ticketSections[$index] ?? '')) ?: null;
            $rowName = trim((string)($ticketRows[$index] ?? '')) ?: null;
            $seatPrefix = trim((string)($ticketSeatPrefixes[$index] ?? '')) ?: null;
            $seatStart = isset($ticketSeatStarts[$index]) && is_numeric($ticketSeatStarts[$index]) ? (int)$ticketSeatStarts[$index] : null;
            $seatEnd = isset($ticketSeatEnds[$index]) && is_numeric($ticketSeatEnds[$index]) ? (int)$ticketSeatEnds[$index] : null;
            $hasSeating = (!empty($ticketHasSeatings[$index]) || $kind === 'seated' || !empty($sectionName)) ? 1 : 0;
            $description = trim((string)($ticketDescriptions[$index] ?? '')) ?: null;

            // Default seatStart to 1 if seating is active and seatEnd or section is provided
            if ($hasSeating && $seatStart === null && ($seatEnd !== null || !empty($sectionName) || !empty($rowName))) {
                $seatStart = 1;
            }
            if ($hasSeating && $seatEnd === null && $seatStart !== null && $qty > 0) {
                $seatEnd = $seatStart + $qty - 1;
            }

            // Auto-calculate capacity from seat range if specified and qty is zero
            if ($hasSeating && $seatStart !== null && $seatEnd !== null && $seatEnd >= $seatStart && $qty <= 0) {
                $qty = ($seatEnd - $seatStart) + 1;
            }

            // Auto-detect table/booth capacity
            if ($admits === 1 && preg_match('/(?:table|booth|group|cabana)\s+(?:for|of)\s+(\d+)/i', $name, $matches)) {
                $admits = max(1, (int)$matches[1]);
                if ($kind === 'standard') {
                    $kind = 'group';
                }
            }

            $existingId = $ticketIds[$index] ?? null;

            if ($existingId && is_numeric($existingId)) {
                $updateStmt = $db->prepare("
                    UPDATE ticket_types 
                    SET name = :name, 
                        ticket_kind = :ticket_kind,
                        price = :price, 
                        capacity = :capacity, 
                        admits = :admits, 
                        max_per_person = :max_per_person,
                        section_name = :section_name,
                        row_name = :row_name,
                        seat_prefix = :seat_prefix,
                        seat_start = :seat_start,
                        seat_end = :seat_end,
                        has_seating = :has_seating,
                        description = :description
                    WHERE id = :id AND event_id = :event_id
                ");
                $updateStmt->execute([
                    'name' => $name,
                    'ticket_kind' => $kind,
                    'price' => $price,
                    'capacity' => $qty,
                    'admits' => $admits,
                    'max_per_person' => $maxPerPerson,
                    'section_name' => $sectionName,
                    'row_name' => $rowName,
                    'seat_prefix' => $seatPrefix,
                    'seat_start' => $seatStart,
                    'seat_end' => $seatEnd,
                    'has_seating' => $hasSeating,
                    'description' => $description,
                    'id' => $existingId,
                    'event_id' => $event->id
                ]);
                $submittedIds[] = (int)$existingId;
            } else {
                $insertStmt = $db->prepare("
                    INSERT INTO ticket_types (
                        event_id, name, ticket_kind, price, capacity, admits, 
                        max_per_person, section_name, row_name, seat_prefix, 
                        seat_start, seat_end, has_seating, description
                    ) VALUES (
                        :event_id, :name, :ticket_kind, :price, :capacity, :admits, 
                        :max_per_person, :section_name, :row_name, :seat_prefix, 
                        :seat_start, :seat_end, :has_seating, :description
                    )
                ");
                $insertStmt->execute([
                    'event_id' => $event->id,
                    'name' => $name,
                    'ticket_kind' => $kind,
                    'price' => $price,
                    'capacity' => $qty,
                    'admits' => $admits,
                    'max_per_person' => $maxPerPerson,
                    'section_name' => $sectionName,
                    'row_name' => $rowName,
                    'seat_prefix' => $seatPrefix,
                    'seat_start' => $seatStart,
                    'seat_end' => $seatEnd,
                    'has_seating' => $hasSeating,
                    'description' => $description
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
                  AND (SELECT COUNT(*) FROM tickets t WHERE t.ticket_type_id = ticket_types.id AND t.status IN ('Paid', 'Comped', 'Layaway')) = 0
            ");
            $deleteStmt->execute(['event_id' => $event->id]);
        }

        // 2. Process Special Guests
        $guestNames = $data['guest-name'] ?? [];
        $guestRoles = $data['guest-role'] ?? [];
        $guestPhones = $data['guest-phone'] ?? [];
        $guestSocials = $data['guest-social'] ?? [];

        $db->prepare("DELETE FROM event_guests WHERE event_id = :event_id")->execute(['event_id' => $event->id]);
        foreach ($guestNames as $index => $gName) {
            $gName = trim((string)$gName);
            if (empty($gName)) continue;
            $gStmt = $db->prepare("INSERT INTO event_guests (event_id, name, role, phone, social) VALUES (:event_id, :name, :role, :phone, :social)");
            $gStmt->execute([
                'event_id' => $event->id,
                'name' => $gName,
                'role' => trim($guestRoles[$index] ?? ''),
                'phone' => trim($guestPhones[$index] ?? ''),
                'social' => trim($guestSocials[$index] ?? '')
            ]);
        }

        // 3. Process Add-ons & Merchandise
        $addonIds = $data['addon-id'] ?? [];
        $addonNames = $data['addon-name'] ?? [];
        $addonPrices = $data['addon-price'] ?? [];
        $addonQtys = $data['addon-quantity'] ?? [];
        $addonDescs = $data['addon-description'] ?? [];
        $addonExistingImages = $data['addon-image-existing'] ?? [];
        $addonImageUrls = $data['addon-image-url'] ?? [];
        $submittedAddonIds = [];

        $addonsTargetDir = function_exists('public_dir') ? public_dir('uploads/addons') : (Application::$ROOT_DIR . '/public/uploads/addons');
        \Ace\UploadService::ensureDirectory($addonsTargetDir);
        $uploadService = new \Ace\UploadService();

        foreach ($addonNames as $index => $aName) {
            $aName = trim((string)$aName);
            if (empty($aName)) continue;

            $existingAddonId = $addonIds[$index] ?? null;
            $aPrice = isset($addonPrices[$index]) && is_numeric($addonPrices[$index]) ? max(0, (float)$addonPrices[$index]) : 0.00;
            $aQty = isset($addonQtys[$index]) && is_numeric($addonQtys[$index]) && (int)$addonQtys[$index] > 0 ? (int)$addonQtys[$index] : null;
            $aDesc = trim($addonDescs[$index] ?? '');

            // Determine image from URL input, existing image, or file upload
            $urlInput = !empty($addonImageUrls[$index]) ? trim((string)$addonImageUrls[$index]) : '';
            $imagePath = !empty($urlInput) ? $urlInput : (!empty($addonExistingImages[$index]) ? $addonExistingImages[$index] : null);

            if (isset($_FILES['addon-image']['name'][$index]) && $_FILES['addon-image']['error'][$index] === UPLOAD_ERR_OK) {
                $fileItem = [
                    'name' => $_FILES['addon-image']['name'][$index],
                    'type' => $_FILES['addon-image']['type'][$index],
                    'tmp_name' => $_FILES['addon-image']['tmp_name'][$index],
                    'error' => $_FILES['addon-image']['error'][$index],
                    'size' => $_FILES['addon-image']['size'][$index]
                ];
                $uploadRes = $uploadService->uploadFile($fileItem, $addonsTargetDir, ['jpg', 'jpeg', 'png', 'webp', 'gif'], 5 * 1024 * 1024);
                if ($uploadRes['success']) {
                    $imagePath = '/uploads/addons/' . $uploadRes['filename'];
                }
            }

            if ($existingAddonId && is_numeric($existingAddonId)) {
                $updateAddonStmt = $db->prepare("
                    UPDATE event_addons 
                    SET name = :name, 
                        description = :desc, 
                        price = :price, 
                        quantity = :qty, 
                        image = :img 
                    WHERE id = :id AND event_id = :event_id
                ");
                $updateAddonStmt->execute([
                    'name' => $aName,
                    'desc' => $aDesc,
                    'price' => $aPrice,
                    'qty' => $aQty,
                    'img' => $imagePath,
                    'id' => $existingAddonId,
                    'event_id' => $event->id
                ]);
                $submittedAddonIds[] = (int)$existingAddonId;
            } else {
                $insertAddonStmt = $db->prepare("
                    INSERT INTO event_addons (event_id, name, description, price, quantity, image, sold_count, status)
                    VALUES (:event_id, :name, :desc, :price, :qty, :img, 0, 'active')
                ");
                $insertAddonStmt->execute([
                    'event_id' => $event->id,
                    'name' => $aName,
                    'desc' => $aDesc,
                    'price' => $aPrice,
                    'qty' => $aQty,
                    'img' => $imagePath
                ]);
                $submittedAddonIds[] = (int)$db->lastInsertId();
            }
        }

        if (!empty($submittedAddonIds)) {
            $inClause = implode(',', array_map('intval', $submittedAddonIds));
            $db->prepare("DELETE FROM event_addons WHERE event_id = :event_id AND id NOT IN ({$inClause}) AND sold_count = 0")
               ->execute(['event_id' => $event->id]);
            $db->prepare("UPDATE event_addons SET status = 'archived' WHERE event_id = :event_id AND id NOT IN ({$inClause}) AND sold_count > 0")
               ->execute(['event_id' => $event->id]);
        }

        Application::$app->session->setFlash('success', 'Tickets, seating arrangements, and lineup saved successfully!');
        if (!empty($data['save_action']) && $data['save_action'] === 'finish') {
            return Application::$app->response->redirect('/host/event/management/' . $event->id);
        }
        return Application::$app->response->redirect('/host/event/' . $event->id . '/tickets-setup');
    }

    public function editEvent(Request $request, int|string $id)
    {
        $user = Application::$app->user;
        $isAdmin = (method_exists($user, 'isAdmin') && $user->isAdmin()) || (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) || hasRole('admin') || hasRole('super-admin');
        $event = $this->eventService->findHostEvent($id, $user->id, $isAdmin);
        if (!$event) {
            throw new \Exception("Event not found or access denied.", 404);
        }

        if ($this->isEventCompleted($event)) {
            Application::$app->session->setFlash('error', 'Completed events cannot be edited.');
            return Application::$app->response->redirect('/host/event/management/' . $event->id);
        }

        // Fetch ticket types breakdown via EventService
        $ticketTypes = $this->eventService->getEventTicketTypesBreakdown($event->id);

        // Fetch existing special guests
        $db = Application::$app->db->pdo;
        $guestStmt = $db->prepare("SELECT * FROM event_guests WHERE event_id = :event_id ORDER BY id ASC");
        $guestStmt->execute(['event_id' => $event->id]);
        $eventGuests = $guestStmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];

        // Fetch existing add-ons & merchandise
        $addonStmt = $db->prepare("SELECT * FROM event_addons WHERE event_id = :event_id AND status != 'deleted' ORDER BY id ASC");
        $addonStmt->execute(['event_id' => $event->id]);
        $eventAddons = $addonStmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];

        // Format event date & time for Flatpickr inputs
        $dateTime = strtotime($event->date);
        $eventDate = $dateTime ? date('Y-m-d', $dateTime) : '';
        $eventTime = $dateTime ? date('H:i', $dateTime) : '';

        // Format venue & city from location field ("Venue, City")
        $locationParts = explode(',', $event->location, 2);
        $eventVenue = trim($locationParts[0] ?? '');
        $eventCity = trim($locationParts[1] ?? '');

        $db = Application::$app->db->pdo;

        // Fetch organizer info for admin context
        $organizerName = null;
        $organizerEmail = null;
        if ($isAdmin && $event->organizer_id != $user->id) {
            $orgStmt = $db->prepare("SELECT name, email FROM users WHERE id = :id LIMIT 1");
            $orgStmt->execute(['id' => $event->organizer_id]);
            $org = $orgStmt->fetch(\PDO::FETCH_ASSOC);
            $organizerName = $org['name'] ?? 'Unknown';
            $organizerEmail = $org['email'] ?? '';
        }

        // Fetch existing staffing request if any
        $staffStmt = $db->prepare("SELECT * FROM event_staff_requests WHERE event_id = :event_id ORDER BY id DESC LIMIT 1");
        $staffStmt->execute(['event_id' => $event->id]);
        $staffRequest = $staffStmt->fetch(\PDO::FETCH_ASSOC) ?: null;
        $staffRolesData = [];
        $staffNotes = '';
        if ($staffRequest) {
            $staffNotes = $staffRequest['notes'] ?? '';
            $decodedRoles = json_decode($staffRequest['roles_requested'] ?? '[]', true) ?: [];
            foreach ($decodedRoles as $r) {
                if (!empty($r['slug'])) {
                    $staffRolesData[$r['slug']] = [
                        'selected' => 1,
                        'qty' => (int)($r['quantity'] ?? 1)
                    ];
                }
            }
        }

        // Fetch recurring series occurrences if applicable
        $recurringOccurrences = [];
        if (!empty($event->is_recurring) && !empty($event->recurrence_group_id)) {
            $recStmt = $db->prepare("
                SELECT id, name, date, recurrence_sequence, recurrence_count, status
                FROM events
                WHERE recurrence_group_id = :gid
                ORDER BY date ASC, recurrence_sequence ASC
            ");
            $recStmt->execute(['gid' => $event->recurrence_group_id]);
            $recurringOccurrences = $recStmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        }

        return $this->render('host/edit-event', [
            'title' => 'Edit Event - ' . $event->name,
            'event' => $event,
            'eventDate' => $eventDate,
            'eventTime' => $eventTime,
            'eventVenue' => $eventVenue,
            'eventCity' => $eventCity,
            'ticketTypes' => $ticketTypes,
            'eventGuests' => $eventGuests,
            'eventAddons' => $eventAddons,
            'recurringOccurrences' => $recurringOccurrences,
            'categories' => $this->categories,
            'isAdmin' => $isAdmin,
            'organizerName' => $organizerName,
            'organizerEmail' => $organizerEmail,
            'staffRequest' => $staffRequest,
            'staffRolesData' => $staffRolesData,
            'staffNotes' => $staffNotes
        ]);
    }

    /**
     * Return JSON list of all occurrences in a recurring series for modals and switchers.
     */
    public function getOccurrencesJson(Request $request, int|string $id)
    {
        $user = Application::$app->user;
        if (!$user) {
            return Application::$app->response->json(['error' => 'Unauthorized'], 401);
        }
        $isAdmin = (method_exists($user, 'isAdmin') && $user->isAdmin()) || (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) || hasRole('admin') || hasRole('super-admin');
        $event = $this->eventService->findHostEvent($id, $user->id, $isAdmin);
        if (!$event) {
            return Application::$app->response->json(['error' => 'Event not found'], 404);
        }

        $recurringOccurrences = [];
        if (!empty($event->is_recurring) && !empty($event->recurrence_group_id)) {
            $db = Application::$app->db->pdo;
            $recStmt = $db->prepare("
                SELECT id, name, date, recurrence_sequence, recurrence_count, status
                FROM events
                WHERE recurrence_group_id = :gid
                ORDER BY date ASC, recurrence_sequence ASC
            ");
            $recStmt->execute(['gid' => $event->recurrence_group_id]);
            $recurringOccurrences = $recStmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        }

        return Application::$app->response->json([
            'success' => true,
            'current_id' => (int)$event->id,
            'event_name' => $event->name,
            'is_recurring' => !empty($event->is_recurring),
            'recurrence_group_id' => $event->recurrence_group_id,
            'occurrences' => $recurringOccurrences
        ]);
    }

    public function updateEvent(Request $request, int|string $id)
    {
        $user = Application::$app->user;
        $isAdmin = (method_exists($user, 'isAdmin') && $user->isAdmin()) || (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) || hasRole('admin') || hasRole('super-admin');
        $event = $this->eventService->findHostEvent($id, $user->id, $isAdmin);
        if (!$event) {
            throw new \Exception("Event not found or access denied.", 404);
        }

        if ($this->isEventCompleted($event)) {
            Application::$app->session->setFlash('error', 'Completed events cannot be edited.');
            return Application::$app->response->redirect('/host/event/management/' . $event->id);
        }

        $data = $request->getBody();

        if ($this->updateEventAction->execute($data, $user->id, $event)) {
            Application::$app->session->setFlash('success', 'Event successfully updated!');
            if (!empty($data['save_and_tickets'])) {
                return Application::$app->response->redirect('/host/event/' . $event->id . '/tickets-setup');
            }
            return Application::$app->response->redirect('/host/event/management/' . $event->id);
        }

        $errorList = $event->getErrorMessages();
        Application::$app->session->setFlash('error', 'Failed to update event: ' . (empty($errorList) ? 'Validation failed.' : implode(', ', $errorList)));
        
        // Fetch organizer info for admin context
        $organizerName = null;
        $organizerEmail = null;
        if ($isAdmin && $event->organizer_id != $user->id) {
            $db = Application::$app->db->pdo;
            $orgStmt = $db->prepare("SELECT name, email FROM users WHERE id = :id LIMIT 1");
            $orgStmt->execute(['id' => $event->organizer_id]);
            $org = $orgStmt->fetch(\PDO::FETCH_ASSOC);
            $organizerName = $org['name'] ?? 'Unknown';
            $organizerEmail = $org['email'] ?? '';
        }

        // Fetch ticket types breakdown via EventService
        $ticketTypes = $this->eventService->getEventTicketTypesBreakdown($event->id);

        // Fetch existing special guests
        $db = Application::$app->db->pdo;
        $guestStmt = $db->prepare("SELECT * FROM event_guests WHERE event_id = :event_id ORDER BY id ASC");
        $guestStmt->execute(['event_id' => $event->id]);
        $eventGuests = $guestStmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];

        // Fetch existing add-ons & merchandise
        $addonStmt = $db->prepare("SELECT * FROM event_addons WHERE event_id = :event_id AND status != 'deleted' ORDER BY id ASC");
        $addonStmt->execute(['event_id' => $event->id]);
        $eventAddons = $addonStmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];

        // Format event date & time for Flatpickr inputs
        $dateTime = strtotime($event->date ?? '');
        $eventDate = $dateTime ? date('Y-m-d', $dateTime) : ($data['event_date'] ?? '');
        $eventTime = $dateTime ? date('H:i', $dateTime) : ($data['event_time'] ?? '');

        // Format venue & city from location field ("Venue, City")
        $locationParts = explode(',', $event->location ?? '', 2);
        $eventVenue = trim($locationParts[0] ?? ($data['event_venue'] ?? ''));
        $eventCity = trim($locationParts[1] ?? ($data['event_city'] ?? ''));

        return $this->render('host/edit-event', [
            'title' => 'Edit Event - ' . $event->name,
            'event' => $event,
            'model' => $event,
            'eventDate' => $eventDate,
            'eventTime' => $eventTime,
            'eventVenue' => $eventVenue,
            'eventCity' => $eventCity,
            'ticketTypes' => $ticketTypes,
            'eventGuests' => $eventGuests,
            'eventAddons' => $eventAddons,
            'categories' => $this->categories,
            'isAdmin' => $isAdmin,
            'organizerName' => $organizerName,
            'organizerEmail' => $organizerEmail
        ]);
    }

    public function manageEvent(Request $request, int|string $id)
    {
        $user = Application::$app->user;
        $isAdmin = (method_exists($user, 'isAdmin') && $user->isAdmin()) || (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) || hasRole('admin') || hasRole('super-admin');
        $event = $this->eventService->findHostEvent($id, $user->id, $isAdmin);
        if (!$event) {
            throw new \Exception("Event not found or access denied.", 404);
        }

        $search = trim($request->getBody()['search'] ?? '');
        $page = (int)($request->getBody()['page'] ?? 1);
        if ($page < 1) $page = 1;
        $limit = 10; // Number of items per page
        $activeTab = $request->getBody()['tab'] ?? (!empty($search) || $page > 1 ? 'attendees' : 'overview');

        // Fetch attendees (tickets) via EventService
        $paginated = $this->eventService->getEventTickets($event->id, $search, $page, $limit);
        $tickets = $paginated['items'];
        $totalPages = $paginated['totalPages'];

        // Fetch ticket types breakdown via EventService
        $ticketTypes = $this->eventService->getEventTicketTypesBreakdown($event->id);

        // Calculate aggregates (organic sales only — excludes host-issued tickets)
        $totalRevenue = 0;
        $ticketsSold = 0;
        $ticketsIssued = 0;
        $totalCapacity = 0;

        foreach ($ticketTypes as $tt) {
            $totalCapacity += (int)$tt['capacity'];
            $ticketsSold += (int)$tt['sold'];
            $ticketsIssued += (int)($tt['issued'] ?? 0);
            $totalRevenue += (float)($tt['revenue'] ?? 0.00);
        }
        
        $db = Application::$app->db->pdo;
        $discStmt = $db->prepare("SELECT COALESCE(SUM(discount_amount), 0.00) FROM transactions WHERE event_id = :event_id AND status = 'completed' AND type = 'payment'");
        $discStmt->execute(['event_id' => $event->id]);
        $eventDiscounts = (float)$discStmt->fetchColumn();
        $netTicketRevenue = max(0.00, $totalRevenue - $eventDiscounts);

        $occupancyPercentage = $totalCapacity > 0 ? (($ticketsSold + $ticketsIssued) / $totalCapacity) * 100 : 0;
        $avgPrice = $ticketsSold > 0 ? ($netTicketRevenue / $ticketsSold) : 0;

        // Layaway reserved funds (incomplete layaways; NOT accessible until fully paid)
        $layStmt = $db->prepare("
            SELECT COALESCE(SUM(lp.amount), 0.00) as reserved_amount,
                   COUNT(DISTINCT t.id) as layaway_count
            FROM layaway_payments lp
            INNER JOIN tickets t ON lp.layaway_ticket_id = t.id
            WHERE t.event_id = :event_id
              AND lp.status = 'completed'
              AND t.status = 'Layaway'
        ");
        $layStmt->execute(['event_id' => $event->id]);
        $layData = $layStmt->fetch(\PDO::FETCH_ASSOC) ?: [];
        $layawayReservedAmount = (float)($layData['reserved_amount'] ?? 0.00);
        $layawayActiveCount = (int)($layData['layaway_count'] ?? 0);

        // Fetch event reviews / feedback
        $db = Application::$app->db->pdo;
        $stmt = $db->prepare("
            SELECT * FROM event_reviews 
            WHERE event_id = :event_id 
            ORDER BY created_at DESC
        ");
        $stmt->execute(['event_id' => $event->id]);
        $reviews = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];

        $avgRating = 0;
        if (!empty($reviews)) {
            $totalRating = array_sum(array_column($reviews, 'rating'));
            $avgRating = round($totalRating / count($reviews), 2);
        }

        // Fetch team members assigned to this event
        $teamStmt = $db->prepare("
            SELECT 
                esa.id as assignment_id,
                esa.checked_in,
                esa.checked_in_at,
                es.id as staff_id,
                es.name as staff_name,
                es.email as staff_email,
                es.status as staff_status,
                es.is_online,
                r.name as role_name,
                r.slug as role_slug,
                esa.assigned_at
            FROM event_staff_assignments esa
            JOIN event_staff es ON esa.staff_id = es.id
            JOIN roles r ON esa.role_id = r.id
            WHERE esa.event_id = :event_id
            ORDER BY esa.checked_in DESC, r.name ASC, es.name ASC
        ");
        $teamStmt->execute(['event_id' => $event->id]);
        $teamMembers = $teamStmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];

        // Fetch organizer info for admin context
        $organizerName = null;
        $organizerEmail = null;
        if ($isAdmin && $event->organizer_id != $user->id) {
            $orgStmt = $db->prepare("SELECT name, email FROM users WHERE id = :id LIMIT 1");
            $orgStmt->execute(['id' => $event->organizer_id]);
            $org = $orgStmt->fetch(\PDO::FETCH_ASSOC);
            $organizerName = $org['name'] ?? 'Unknown';
            $organizerEmail = $org['email'] ?? '';
        }

        // Fetch staffing assistance request and assigned staff
        $staffRequest = $this->eventService->getEventStaffRequest($event->id);
        $assignedStaff = $this->eventService->getEventAssignedStaff($event->id);

        // Fetch any pending transactions for this event (allowing one-click verification)
        $pendingTxStmt = $db->prepare("
            SELECT * FROM transactions 
            WHERE event_id = :event_id AND status = 'pending'
            ORDER BY created_at DESC
            LIMIT 15
        ");
        $pendingTxStmt->execute(['event_id' => $event->id]);
        $pendingTransactions = $pendingTxStmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];

        // Fetch Custom Domain / API integration request for this event
        $domainReqStmt = $db->prepare("
            SELECT * FROM event_domain_requests 
            WHERE event_id = :event_id AND host_id = :host_id 
            ORDER BY created_at DESC LIMIT 1
        ");
        $domainReqStmt->execute(['event_id' => $event->id, 'host_id' => $event->organizer_id]);
        $domainRequest = $domainReqStmt->fetch(\PDO::FETCH_ASSOC) ?: null;

        // Fetch recurring series occurrences if applicable
        $recurringOccurrences = [];
        if (!empty($event->is_recurring) && !empty($event->recurrence_group_id)) {
            $recStmt = $db->prepare("
                SELECT id, name, date, recurrence_sequence, recurrence_count, status
                FROM events
                WHERE recurrence_group_id = :gid
                ORDER BY date ASC, recurrence_sequence ASC
            ");
            $recStmt->execute(['gid' => $event->recurrence_group_id]);
            $recurringOccurrences = $recStmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        }

        // Fetch event add-ons and sales metrics
        $addonSummaryStmt = $db->prepare("
            SELECT 
                ea.*,
                COALESCE(SUM(oa.quantity), 0) as total_units_sold,
                COALESCE(SUM(oa.total_price), 0.00) as total_addon_revenue
            FROM event_addons ea
            LEFT JOIN order_addons oa ON oa.addon_id = ea.id AND oa.redemption_status != 'cancelled'
            WHERE ea.event_id = :event_id
            GROUP BY ea.id
            ORDER BY ea.id ASC
        ");
        $addonSummaryStmt->execute(['event_id' => $event->id]);
        $eventAddons = $addonSummaryStmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];

        $totalAddonsSold = 0;
        $totalAddonsRevenue = 0.0;
        foreach ($eventAddons as $a) {
            $totalAddonsSold += (int)($a['total_units_sold'] ?? 0);
            $totalAddonsRevenue += (float)($a['total_addon_revenue'] ?? 0.0);
        }

        // Fetch individual purchased add-ons / buyers list for this event
        $purchasedAddonsStmt = $db->prepare("
            SELECT 
                oa.*,
                ea.name as addon_name,
                ea.image as addon_image,
                ea.price as addon_current_price,
                tx.reference as transaction_reference,
                tx.status as transaction_status
            FROM order_addons oa
            JOIN event_addons ea ON ea.id = oa.addon_id
            LEFT JOIN transactions tx ON tx.id = oa.transaction_id
            WHERE oa.event_id = :event_id
            ORDER BY oa.created_at DESC
        ");
        $purchasedAddonsStmt->execute(['event_id' => $event->id]);
        $purchasedAddons = $purchasedAddonsStmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];

        $collectedAddonsCount = 0;
        $pendingAddonsCount = 0;
        foreach ($purchasedAddons as $pa) {
            if (($pa['redemption_status'] ?? '') === 'fulfilled') {
                $collectedAddonsCount += (int)$pa['quantity'];
            } else {
                $pendingAddonsCount += (int)$pa['quantity'];
            }
        }

        return $this->render('host/event-management', [
            'title' => 'Manage Event - ' . $event->name,
            'event' => $event,
            'eventId' => $event->id,
            'tickets' => $tickets,
            'ticketTypes' => $ticketTypes,
            'eventAddons' => $eventAddons,
            'totalAddonsSold' => $totalAddonsSold,
            'totalAddonsRevenue' => $totalAddonsRevenue,
            'purchasedAddons' => $purchasedAddons,
            'collectedAddonsCount' => $collectedAddonsCount,
            'pendingAddonsCount' => $pendingAddonsCount,
            'recurringOccurrences' => $recurringOccurrences,
            'totalRevenue' => $netTicketRevenue,
            'nominalTicketRevenue' => $totalRevenue,
            'eventDiscounts' => $eventDiscounts,
            'ticketsSold' => $ticketsSold,
            'ticketsIssued' => $ticketsIssued,
            'totalCapacity' => $totalCapacity,
            'occupancyPercentage' => $occupancyPercentage,
            'avgPrice' => $avgPrice,
            'search' => $search,
            'page' => $page,
            'totalPages' => $totalPages,
            'reviews' => $reviews,
            'avgRating' => $avgRating,
            'teamMembers' => $teamMembers,
            'staffRequest' => $staffRequest,
            'assignedStaff' => $assignedStaff,
            'pendingTransactions' => $pendingTransactions,
            'domainRequest' => $domainRequest,
            'isAdmin' => $isAdmin,
            'activeTab' => $activeTab,
            'organizerName' => $organizerName,
            'organizerEmail' => $organizerEmail,
            'layawayReservedAmount' => $layawayReservedAmount,
            'layawayActiveCount' => $layawayActiveCount
        ]);
    }

    public function requestStaff(Request $request, int|string $id)
    {
        $user = Application::$app->user;
        $isAdmin = (method_exists($user, 'isAdmin') && $user->isAdmin()) || (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) || hasRole('admin') || hasRole('super-admin');
        $event = $this->eventService->findHostEvent($id, $user->id, $isAdmin);
        if (!$event) {
            throw new \Exception("Event not found or access denied.", 404);
        }

        $body = $request->getBody();
        $rolesRequested = [];

        $roleMap = [
            'ticket-scanner' => ['role_id' => 5, 'name' => 'Ticket Scanner', 'icon' => '🎫'],
            'organizer-staff' => ['role_id' => 4, 'name' => 'Organizer Staff / Usher', 'icon' => '🏷️'],
            'customer-support' => ['role_id' => 8, 'name' => 'Customer Support Lead', 'icon' => '🎧'],
            'technical-ops' => ['role_id' => 9, 'name' => 'Technical Operations', 'icon' => '⚙️'],
            'finance-manager' => ['role_id' => 7, 'name' => 'Finance & Box Office', 'icon' => '💼'],
        ];

        foreach ($roleMap as $slug => $info) {
            $key = 'role_' . str_replace('-', '_', $slug);
            $qtyKey = 'qty_' . str_replace('-', '_', $slug);
            if (!empty($body[$key])) {
                $rolesRequested[] = [
                    'slug' => $slug,
                    'role_id' => $info['role_id'],
                    'name' => $info['name'],
                    'icon' => $info['icon'],
                    'quantity' => max(1, (int)($body[$qtyKey] ?? 1)),
                ];
            }
        }

        if (empty($rolesRequested)) {
            Application::$app->session->setFlash('error', 'Please select at least one staff role to request.');
            return redirect('/host/event/management/' . $event->id . '?tab=staffing#tab-staffing');
        }

        $notes = trim($body['staff_notes'] ?? '');
        $this->eventService->saveEventStaffRequest($event->id, $user->id, $rolesRequested, $notes);

        Application::$app->session->setFlash('success', 'Staffing assistance request submitted successfully! The admin team will review and assign staff members.');
        return redirect('/host/event/management/' . $event->id . '?tab=staffing#tab-staffing');
    }

    public function sendFeedbackRequests(Request $request, int|string $id)
    {
        $user = Application::$app->user;
        $isAdmin = (method_exists($user, 'isAdmin') && $user->isAdmin()) || (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) || hasRole('admin') || hasRole('super-admin');
        $event = $this->eventService->findHostEvent($id, $user->id, $isAdmin);
        if (!$event) {
            throw new \Exception("Event not found or access denied.", 404);
        }

        $sentCount = $this->sendFeedbackRequestAction->execute($event);

        if ($sentCount > 0) {
            Application::$app->session->setFlash('success', "Feedback request emails successfully sent to {$sentCount} attendee(s)!");
        } else {
            Application::$app->session->setFlash('error', 'No eligible attendees found to send feedback requests to.');
        }

        return back();
    }

    public function addTicketManually(Request $request, int|string $id)
    {
        $user = Application::$app->user;
        $isAdmin = (method_exists($user, 'isAdmin') && $user->isAdmin()) || (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) || hasRole('admin') || hasRole('super-admin');
        $event = $this->eventService->findHostEvent($id, $user->id, $isAdmin);
        if (!$event) {
            throw new \Exception("Event not found.", 404);
        }

        if ($this->isEventCompleted($event)) {
            Application::$app->session->setFlash('error', 'Cannot issue tickets for completed events.');
            return back();
        }

        $data = $request->getBody();
        $ticket = new Ticket();

        if ($this->issueTicketAction->execute($data, $event, $ticket)) {
            Application::$app->session->setFlash('success', 'Ticket successfully issued to ' . $ticket->attendee_name . '!');
        } else {
            Application::$app->session->setFlash('error', 'Failed to issue ticket: ' . implode(', ', $ticket->getErrorMessages()));
        }

        return redirect('/host/event/management/' . $event->id . '?tab=attendees#tab-attendees');
    }

    public function sendReminders(Request $request, int|string $id)
    {
        $user = Application::$app->user;
        $isAdmin = (method_exists($user, 'isAdmin') && $user->isAdmin()) || (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) || hasRole('admin') || hasRole('super-admin');
        $event = $this->eventService->findHostEvent($id, $user->id, $isAdmin);
        if (!$event) {
            throw new \Exception("Event not found or access denied.", 404);
        }

        $db = Application::$app->db->pdo;
        $stmt = $db->prepare("
            SELECT DISTINCT attendee_name, attendee_email 
            FROM tickets 
            WHERE event_id = :event_id AND status IN ('Paid', 'Comped')
        ");
        $stmt->execute(['event_id' => $event->id]);
        $attendees = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        if (empty($attendees)) {
            Application::$app->session->setFlash('error', 'No registered attendees found for this event.');
            return back();
        }

        $emailMessages = [];
        $formattedDate = !empty($event->date) ? date('D, M d, Y • g:i A', strtotime($event->date)) : 'TBA';

        foreach ($attendees as $att) {
            if (!empty($att['attendee_email'])) {
                $emailMessages[] = [
                    'to' => $att['attendee_email'],
                    'name' => $att['attendee_name'] ?? 'Attendee',
                    'subject' => 'Event Reminder - ' . $event->name,
                    'view' => 'emails/event_reminder',
                    'params' => [
                        'attendee_name' => $att['attendee_name'],
                        'event_name' => $event->name,
                        'event_date' => $formattedDate,
                        'event_location' => $event->location
                    ]
                ];
            }
        }

        try {
            \Ace\EmailQueue::queueMessages($emailMessages, 'REMIND-EVT-' . $event->id, 5);
            \Ace\EmailQueue::processAfterResponse();
        } catch (\Throwable $e) {
            error_log("Failed to queue reminder emails: " . $e->getMessage());
        }

        $sentCount = count($emailMessages);
        Application::$app->session->setFlash('success', "Event reminder emails queued in background for {$sentCount} attendee(s)!");
        return back();
    }

    /**
     * Broadcast Event Day Alerts (WhatsApp & Email) to registered attendees.
     */
    public function sendEventDayAlerts(Request $request, int|string $id)
    {
        $user = Application::$app->user;
        $isAdmin = (method_exists($user, 'isAdmin') && $user->isAdmin()) || (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) || hasRole('admin') || hasRole('super-admin');
        $event = $this->eventService->findHostEvent($id, $user->id, $isAdmin);
        if (!$event) {
            throw new \Exception("Event not found or access denied.", 404);
        }

        $data = $request->getBody();
        $alertType = trim($data['alert_type'] ?? 'gates_open');
        $customMessage = trim($data['custom_message'] ?? '');
        $channels = $data['channels'] ?? ['whatsapp', 'email'];
        if (is_string($channels)) {
            $channels = explode(',', $channels);
        }

        $db = Application::$app->db->pdo;
        $stmt = $db->prepare("
            SELECT t.id, t.attendee_name, t.attendee_email, t.attendee_phone, tt.name as ticket_tier
            FROM tickets t
            LEFT JOIN ticket_types tt ON t.ticket_type_id = tt.id
            WHERE t.event_id = :event_id AND t.status IN ('Paid', 'Comped')
            ORDER BY t.id ASC
        ");
        $stmt->execute(['event_id' => $event->id]);
        $attendees = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];

        if (empty($attendees)) {
            Application::$app->session->setFlash('error', 'No registered attendees found to alert.');
            return back();
        }

        $emailMessages = [];
        $waDispatched = 0;
        $siteTitle = site_setting('site_title', 'Eventlyy');
        $eventArr = [
            'id' => $event->id,
            'name' => $event->name,
            'date' => $event->date,
            'location' => $event->location
        ];

        $alertTitles = [
            'gates_open'  => '🚪 Gates Are Open - ' . $event->name,
            'reminder_2h' => '⏳ 2 Hours to Event - ' . $event->name,
            'parking'     => '🚗 Parking & Directions - ' . $event->name,
            'schedule'    => '📍 Important Schedule Update - ' . $event->name,
            'custom'      => '📢 Event Day Alert - ' . $event->name,
        ];
        $emailSubject = $alertTitles[$alertType] ?? ('📢 Event Alert - ' . $event->name);
        $formattedDate = !empty($event->date) ? date('D, M d, Y • g:i A', strtotime($event->date)) : 'TBA';

        foreach ($attendees as $att) {
            // 1. Email Channel
            if (in_array('email', $channels) && !empty($att['attendee_email'])) {
                $emailMessages[] = [
                    'to' => $att['attendee_email'],
                    'name' => $att['attendee_name'] ?? 'Attendee',
                    'subject' => $emailSubject,
                    'view' => 'emails/event_reminder',
                    'params' => [
                        'attendee_name' => $att['attendee_name'],
                        'ticket_code' => $att['id'] ?? '',
                        'event_name' => $event->name,
                        'event_date' => $formattedDate,
                        'event_location' => $event->location,
                        'custom_message' => $customMessage,
                        'alert_type' => $alertType
                    ]
                ];
            }

            // 2. WhatsApp Channel
            if (in_array('whatsapp', $channels)) {
                $phone = trim($att['attendee_phone'] ?? '');
                $waText = \App\Services\WhatsAppService::buildEventDayAlertMessage($eventArr, $alertType, $customMessage, $att, $siteTitle);
                
                if (!empty($phone)) {
                    $waRes = \App\Services\WhatsAppService::dispatch($phone, $waText);
                    if ($waRes['success']) {
                        $waDispatched++;
                    }
                }
            }
        }

        // Queue all emails in background
        if (!empty($emailMessages)) {
            try {
                \Ace\EmailQueue::queueMessages($emailMessages, 'ALERT-EVT-' . $event->id, 3);
                \Ace\EmailQueue::processAfterResponse();
            } catch (\Throwable $e) {
                error_log("Failed to queue alert emails: " . $e->getMessage());
            }
        }

        $emailCount = count($emailMessages);
        $totalAtt = count($attendees);
        $flashMsg = "Event Day Alert successfully dispatched to {$totalAtt} attendee(s)! ";
        if (in_array('email', $channels)) {
            $flashMsg .= "Emails queued: {$emailCount}. ";
        }
        if (in_array('whatsapp', $channels)) {
            $flashMsg .= "WhatsApp alerts dispatched/ready: {$totalAtt}.";
        }

        Application::$app->session->setFlash('success', $flashMsg);
        return back();
    }

    public function exportAttendees(Request $request, int|string $id)
    {
        $user = Application::$app->user;
        $isAdmin = (method_exists($user, 'isAdmin') && $user->isAdmin()) || (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) || hasRole('admin') || hasRole('super-admin');
        $event = $this->eventService->findHostEvent($id, $user->id, $isAdmin);
        if (!$event) {
            throw new \Exception("Event not found or access denied.", 404);
        }

        $db = Application::$app->db->pdo;
        $stmt = $db->prepare("
            SELECT t.id, t.attendee_name, t.attendee_email, t.attendee_phone,
                   tt.name as ticket_tier, t.price, t.pass_fee, t.status, t.status_detail, t.checked_in, 
                   t.checked_in_at, t.purchase_date
            FROM tickets t
            INNER JOIN ticket_types tt ON t.ticket_type_id = tt.id
            WHERE t.event_id = :event_id
            ORDER BY t.purchase_date DESC
        ");
        $stmt->execute(['event_id' => $event->id]);
        $attendees = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];

        $filename = "attendees_" . preg_replace('/[^a-zA-Z0-9_-]/', '_', strtolower($event->name)) . "_" . date('Ymd_His') . ".csv";

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');
        // Add UTF-8 BOM for Excel compatibility
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        // CSV Header
        fputcsv($output, [
            'Ticket Code / ID',
            'Attendee Name',
            'Attendee Email',
            'Attendee Phone / WhatsApp',
            'Ticket Tier',
            'Price Paid',
            'Payment Status',
            'Status Detail',
            'Checked In',
            'Checked In Time',
            'Purchase Date'
        ]);

        foreach ($attendees as $row) {
            fputcsv($output, [
                $row['id'],
                $row['attendee_name'],
                $row['attendee_email'],
                $row['attendee_phone'] ?? '',
                $row['ticket_tier'],
                number_format((float)$row['price'], 2),
                $row['status'],
                $row['status_detail'] ?? '',
                $row['checked_in'] ? 'Yes' : 'No',
                $row['checked_in_at'] ? date('Y-m-d H:i:s', strtotime($row['checked_in_at'])) : 'N/A',
                !empty($row['purchase_date']) ? date('Y-m-d H:i:s', strtotime($row['purchase_date'])) : 'N/A'
            ]);
        }

        fclose($output);
        exit;
    }

    public function exportAttendeesPdf(Request $request, int|string $id)
    {
        $user = Application::$app->user;
        $isAdmin = (method_exists($user, 'isAdmin') && $user->isAdmin()) || (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) || hasRole('admin') || hasRole('super-admin');
        $event = $this->eventService->findHostEvent($id, $user->id, $isAdmin);
        if (!$event) {
            throw new \Exception("Event not found or access denied.", 404);
        }

        $db = Application::$app->db->pdo;
        $stmt = $db->prepare("
            SELECT t.id, t.attendee_name, t.attendee_email, t.attendee_phone,
                   tt.name as ticket_tier, t.price, t.pass_fee, t.status, t.status_detail, t.checked_in, 
                   t.checked_in_at, t.purchase_date
            FROM tickets t
            INNER JOIN ticket_types tt ON t.ticket_type_id = tt.id
            WHERE t.event_id = :event_id
            ORDER BY t.purchase_date DESC
        ");
        $stmt->execute(['event_id' => $event->id]);
        $attendees = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];

        $hostStmt = $db->prepare("SELECT name, email FROM users WHERE id = :id LIMIT 1");
        $hostStmt->execute(['id' => $event->organizer_id]);
        $host = $hostStmt->fetch(\PDO::FETCH_ASSOC) ?: [];

        return $this->render('reports/attendee-pdf', [
            'event' => $event,
            'attendees' => $attendees,
            'host' => $host
        ]);
    }

    public function accountingReport(Request $request, int|string $id)
    {
        $user = Application::$app->user;
        $isAdmin = (method_exists($user, 'isAdmin') && $user->isAdmin()) || (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) || hasRole('admin') || hasRole('super-admin');
        $event = $this->eventService->findHostEvent($id, $user->id, $isAdmin);
        if (!$event) {
            throw new \Exception("Event not found or access denied.", 404);
        }

        $db = Application::$app->db->pdo;

        // 1. Fetch tickets with tier name
        $stmt = $db->prepare("
            SELECT t.id, t.attendee_name, t.attendee_email, t.attendee_phone,
                   tt.name as ticket_tier, t.price, t.pass_fee, t.status, t.status_detail, t.checked_in, 
                   t.checked_in_at, t.purchase_date, t.is_issued
            FROM tickets t
            LEFT JOIN ticket_types tt ON t.ticket_type_id = tt.id
            WHERE t.event_id = :event_id
            ORDER BY t.purchase_date DESC
        ");
        $stmt->execute(['event_id' => $event->id]);
        $allTickets = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];

        // 2. Fetch transactions
        $txStmt = $db->prepare("
            SELECT * FROM transactions 
            WHERE event_id = :event_id 
            ORDER BY created_at DESC
        ");
        $txStmt->execute(['event_id' => $event->id]);
        $transactions = $txStmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];

        $txMap = [];
        foreach ($transactions as $tx) {
            if (!empty($tx['reference'])) {
                $txMap[$tx['reference']] = $tx;
            }
        }

        // 3. Ticket tiers
        $ttStmt = $db->prepare("SELECT * FROM ticket_types WHERE event_id = :event_id");
        $ttStmt->execute(['event_id' => $event->id]);
        $ticketTypes = $ttStmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];

        // 4. Payout records for this event
        $payoutsStmt = $db->prepare("SELECT * FROM payouts WHERE event_id = :event_id ORDER BY created_at DESC, id DESC");
        $payoutsStmt->execute(['event_id' => $event->id]);
        $allEventPayouts = $payoutsStmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        $payout = $allEventPayouts[0] ?? null;

        $totalDisbursedPayouts = 0.00;
        $pendingPayoutAmount = 0.00;
        foreach ($allEventPayouts as $p) {
            if (in_array($p['status'] ?? '', ['processed', 'completed'])) {
                $totalDisbursedPayouts += (float)($p['amount'] ?? 0.00);
            } elseif (($p['status'] ?? '') === 'pending') {
                $pendingPayoutAmount += (float)($p['amount'] ?? 0.00);
            }
        }

        // 5. Fetch merchandise & add-ons orders for this event
        $merchStmt = $db->prepare("
            SELECT oa.*, ea.name as addon_name, ea.image as addon_image, ea.price as catalog_price
            FROM order_addons oa
            LEFT JOIN event_addons ea ON oa.addon_id = ea.id
            WHERE oa.event_id = :event_id
            ORDER BY oa.created_at DESC, oa.id DESC
        ");
        $merchStmt->execute(['event_id' => $event->id]);
        $merchOrders = $merchStmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];

        $merchTotalSales = 0.00;
        $merchTotalQty = 0;
        $merchFulfilledQty = 0;
        $merchPendingQty = 0;

        foreach ($merchOrders as $mo) {
            if (($mo['redemption_status'] ?? '') !== 'cancelled') {
                $merchTotalSales += (float)($mo['total_price'] ?? 0.00);
                $qty = (int)($mo['quantity'] ?? 1);
                $merchTotalQty += $qty;
                if (($mo['redemption_status'] ?? '') === 'fulfilled') {
                    $merchFulfilledQty += $qty;
                } else {
                    $merchPendingQty += $qty;
                }
            }
        }

        // Split tickets into Online vs Issued/Comp
        $onlineTickets = [];
        $issuedTickets = [];
        $onlineGross = 0;
        $gateCashGross = 0;
        $compNominal = 0;
        $onlineCheckedIn = 0;
        $issuedCheckedIn = 0;
        $totalPlatformFee = 0.00;
        $netTicketEarnings = 0.00;
        $platformFeePercent = (float)site_setting('platform_fee', 5);

        foreach ($allTickets as &$t) {
            $isComp = ($t['status'] === 'Comped') || !empty($t['is_issued']);
            $statusDetail = $t['status_detail'] ?? '';
            $isGateSale = str_contains($statusDetail, 'Gate Sale');
            $price = (float)($t['price'] ?? 0);
            $checkedIn = !empty($t['checked_in']);

            // Parse Paystack reference
            $ref = 'N/A';
            if (preg_match('/(?:Paystack\s*Ref:\s*|Ref:\s*)([A-Za-z0-9_-]+)/i', $statusDetail, $m)) {
                $ref = $m[1];
            } elseif (!empty($t['reference'])) {
                $ref = $t['reference'];
            }
            $t['extracted_ref'] = $ref;
            $t['gateway_status'] = 'Verified (Paid)';
            if (isset($txMap[$ref])) {
                $tx = $txMap[$ref];
                $t['gateway_status'] = ucfirst($tx['status'] ?? 'completed') . ' (' . strtoupper($tx['payment_method'] ?? 'paystack') . ')';
            } elseif (in_array($t['status'] ?? '', ['Cancelled', 'Refunded'])) {
                $t['gateway_status'] = 'Refunded / Cancelled';
            }

            if ($isComp || $isGateSale) {
                if (str_contains($statusDetail, 'Gate Sale (CASH)')) {
                    $t['issuance_type'] = 'Gate Box Office (CASH)';
                    $gateCashGross += $price;
                } elseif ($price == 0 || $t['status'] === 'Comped' || str_contains($statusDetail, 'Free')) {
                    $t['issuance_type'] = 'Complimentary (Free)';
                    $compNominal += $price;
                } else {
                    $t['issuance_type'] = 'Host Direct Issuance';
                    $gateCashGross += $price;
                }
                if ($checkedIn) $issuedCheckedIn++;
                $issuedTickets[] = $t;
            } else {
                $onlineGross += $price;
                if ($checkedIn) $onlineCheckedIn++;
                $ticketPassFee = isset($t['pass_fee']) ? (int)$t['pass_fee'] : (!empty($event->pass_fee) ? 1 : 0);
                $tFee = ($ticketPassFee === 1) ? 0.00 : round($price * ($platformFeePercent / 100), 2);
                $tNet = max(0.00, $price - $tFee);
                $t['platform_fee'] = $tFee;
                $t['host_net'] = $tNet;
                $totalPlatformFee += $tFee;
                $netTicketEarnings += $tNet;
                $onlineTickets[] = $t;
            }
        }
        unset($t);

        // Totals & balances:
        // Host Net Earnings (Organizer Payout Share) is funded by online ticket purchases and activity power votes on the platform.
        // Gate cash and direct issued tickets are retained/handled offline by the host and excluded from platform payout transfers.
        $actStmt = $db->prepare("
            SELECT COALESCE(SUM(total_amount), 0.00) as gross_activity,
                   COALESCE(SUM(fee_amount), 0.00) as fee_activity,
                   COUNT(*) as count_activity
            FROM transactions
            WHERE event_id = :event_id 
              AND payment_method = 'paystack_powervote' 
              AND status = 'completed'
        ");
        $actStmt->execute(['event_id' => $event->id]);
        $actRow = $actStmt->fetch(\PDO::FETCH_ASSOC) ?: [];
        $activityGross = (float)($actRow['gross_activity'] ?? 0.00);
        $activityFee = (float)($actRow['fee_activity'] ?? 0.00);
        $activityNet = max(0.00, $activityGross - $activityFee);
        $activityOrdersCount = (int)($actRow['count_activity'] ?? 0);

        $isPassFee = !empty($event->pass_fee);
        $netHostEarnings = round($netTicketEarnings + $activityNet, 2);
        $totalGrossRevenue = $onlineGross + $activityGross;
        $remainingHostBalance = max(0.00, round($netHostEarnings - ($totalDisbursedPayouts + $pendingPayoutAmount), 2));

        $totalCapacity = 0;
        foreach ($ticketTypes as $tt) {
            $totalCapacity += (int)($tt['capacity'] ?? 0);
        }

        $totalTicketsSoldOrIssued = count($allTickets);
        $totalCheckedIn = $onlineCheckedIn + $issuedCheckedIn;
        $occupancyRate = $totalCapacity > 0 ? round(($totalTicketsSoldOrIssued / $totalCapacity) * 100, 1) : 0;
        $turnoutRate = $totalTicketsSoldOrIssued > 0 ? round(($totalCheckedIn / $totalTicketsSoldOrIssued) * 100, 1) : 0;

        // Organizer info for admin view
        $organizerName = null;
        $organizerEmail = null;
        if ($isAdmin && $event->organizer_id != $user->id) {
            $orgStmt = $db->prepare("SELECT name, email FROM users WHERE id = :id LIMIT 1");
            $orgStmt->execute(['id' => $event->organizer_id]);
            $org = $orgStmt->fetch(\PDO::FETCH_ASSOC);
            $organizerName = $org['name'] ?? 'Unknown';
            $organizerEmail = $org['email'] ?? '';
        }

        return $this->render('host/accounting-report', [
            'title' => 'Financial Accounting Report - ' . $event->name,
            'event' => $event,
            'onlineTickets' => $onlineTickets,
            'issuedTickets' => $issuedTickets,
            'allTickets' => $allTickets,
            'ticketTypes' => $ticketTypes,
            'transactions' => $transactions,
            'payout' => $payout,
            'allEventPayouts' => $allEventPayouts,
            'totalDisbursedPayouts' => $totalDisbursedPayouts,
            'pendingPayoutAmount' => $pendingPayoutAmount,
            'remainingHostBalance' => $remainingHostBalance,
            'merchOrders' => $merchOrders,
            'merchTotalSales' => $merchTotalSales,
            'merchTotalQty' => $merchTotalQty,
            'merchFulfilledQty' => $merchFulfilledQty,
            'merchPendingQty' => $merchPendingQty,
            'onlineGross' => $onlineGross,
            'gateCashGross' => $gateCashGross,
            'compNominal' => $compNominal,
            'netTicketEarnings' => $netTicketEarnings,
            'activityGross' => $activityGross,
            'activityFee' => $activityFee,
            'activityNet' => $activityNet,
            'activityOrdersCount' => $activityOrdersCount,
            'totalGrossRevenue' => $totalGrossRevenue,
            'platformFeePercent' => $platformFeePercent,
            'totalPlatformFee' => $totalPlatformFee,
            'netHostEarnings' => $netHostEarnings,
            'totalCapacity' => $totalCapacity,
            'totalTicketsSoldOrIssued' => $totalTicketsSoldOrIssued,
            'totalCheckedIn' => $totalCheckedIn,
            'occupancyRate' => $occupancyRate,
            'turnoutRate' => $turnoutRate,
            'isAdmin' => $isAdmin,
            'organizerName' => $organizerName,
            'organizerEmail' => $organizerEmail
        ]);
    }

    public function exportAccountingCsv(Request $request, int|string $id)
    {
        $user = Application::$app->user;
        $isAdmin = (method_exists($user, 'isAdmin') && $user->isAdmin()) || (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) || hasRole('admin') || hasRole('super-admin');
        $event = $this->eventService->findHostEvent($id, $user->id, $isAdmin);
        if (!$event) {
            throw new \Exception("Event not found or access denied.", 404);
        }

        $db = Application::$app->db->pdo;
        \App\Services\AutoPayoutService::reconcileDiscountedTickets($db);

        $stmt = $db->prepare("
            SELECT t.id, t.attendee_name, t.attendee_email, t.attendee_phone,
                   tt.name as ticket_tier, t.price, t.pass_fee, t.status, t.status_detail, t.checked_in, 
                   t.checked_in_at, t.purchase_date, t.is_issued
            FROM tickets t
            LEFT JOIN ticket_types tt ON t.ticket_type_id = tt.id
            WHERE t.event_id = :event_id
            ORDER BY t.purchase_date DESC
        ");
        $stmt->execute(['event_id' => $event->id]);
        $tickets = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];

        $filename = "accounting_summary_" . preg_replace('/[^a-zA-Z0-9_-]/', '_', strtolower($event->name)) . "_" . date('Ymd_His') . ".csv";

        if (ob_get_level()) {
            ob_end_clean();
        }

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $out = fopen('php://output', 'w');
        fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM

        // Section 1: Event Summary
        fputcsv($out, ['=== EVENT FINANCIAL ACCOUNTING REPORT ===']);
        fputcsv($out, ['Event Name', $event->name]);
        fputcsv($out, ['Event Date', $event->date]);
        fputcsv($out, ['Venue', $event->location]);
        fputcsv($out, ['Export Date', date('Y-m-d H:i:s')]);
        fputcsv($out, []);

        // Section 2: Tickets Data
        fputcsv($out, ['Ticket Code', 'Attendee / Recipient', 'Email', 'Ticket Tier', 'Channel / Issuance', 'Paystack / Audit Ref', 'Price', 'Status', 'Checked In', 'Date']);
        $totalRev = 0;
        foreach ($tickets as $t) {
            $isComp = ($t['status'] === 'Comped') || !empty($t['is_issued']);
            $detail = $t['status_detail'] ?? '';
            $channel = ($isComp || str_contains($detail, 'Gate Sale')) ? 'Issued / Gate' : 'Online (Paystack)';
            $price = (float)($t['price'] ?? 0);
            $totalRev += $price;

            fputcsv($out, [
                $t['id'],
                $t['attendee_name'],
                $t['attendee_email'],
                $t['ticket_tier'] ?? 'General',
                $channel,
                $detail ?: 'Direct',
                number_format($price, 2),
                $t['status'],
                !empty($t['checked_in']) ? 'Yes' : 'No',
                $t['purchase_date']
            ]);
        }

        fputcsv($out, []);
        fputcsv($out, ['TOTAL TICKETS', count($tickets), '', '', '', 'TOTAL TICKET GROSS', number_format($totalRev, 2)]);
        fputcsv($out, []);

        // Section 3: Online Merchandise & Add-Ons Sales Data
        $merchStmt = $db->prepare("
            SELECT oa.*, ea.name as addon_name 
            FROM order_addons oa 
            LEFT JOIN event_addons ea ON oa.addon_id = ea.id 
            WHERE oa.event_id = :event_id 
            ORDER BY oa.created_at DESC
        ");
        $merchStmt->execute(['event_id' => $event->id]);
        $merchRows = $merchStmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];

        fputcsv($out, ['=== ONLINE MERCHANDISE & ADD-ONS SALES ===']);
        fputcsv($out, ['Order ID', 'Item Name', 'Buyer Name', 'Buyer Email', 'Unit Price', 'Quantity', 'Total Amount', 'Redemption Status', 'Date']);
        $totalMerchSales = 0;
        $totalMerchQty = 0;
        foreach ($merchRows as $m) {
            $totalPrice = (float)($m['total_price'] ?? 0);
            $qty = (int)($m['quantity'] ?? 1);
            if (($m['redemption_status'] ?? '') !== 'cancelled') {
                $totalMerchSales += $totalPrice;
                $totalMerchQty += $qty;
            }
            fputcsv($out, [
                $m['id'],
                $m['addon_name'] ?: 'Merchandise Item',
                $m['buyer_name'] ?: 'Guest',
                $m['buyer_email'] ?: 'N/A',
                number_format((float)($m['unit_price'] ?? 0), 2),
                $qty,
                number_format($totalPrice, 2),
                ucfirst($m['redemption_status'] ?? 'pending'),
                $m['created_at'] ?? 'N/A'
            ]);
        }
        fputcsv($out, []);
        fputcsv($out, ['TOTAL MERCH ORDERS', count($merchRows), 'TOTAL ITEMS SOLD', $totalMerchQty, '', '', number_format($totalMerchSales, 2)]);
        fputcsv($out, []);

        // Section 4: Power Voting Activity Revenue
        $actStmt = $db->prepare("
            SELECT reference, payer_name, payer_email, subtotal_amount, fee_amount, total_amount, status, created_at 
            FROM transactions 
            WHERE event_id = :event_id AND payment_method = 'paystack_powervote' 
            ORDER BY created_at DESC
        ");
        $actStmt->execute(['event_id' => $event->id]);
        $actTxRows = $actStmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];

        fputcsv($out, ['=== EVENT ACTIVITIES & POWER VOTING SALES ===']);
        fputcsv($out, ['Audit Reference', 'Voter Name', 'Voter Email', 'Gross Raised', 'Platform Fee', 'Host Net Share', 'Status', 'Date']);
        $totalActGross = 0;
        $totalActFee = 0;
        $totalActNet = 0;
        foreach ($actTxRows as $atx) {
            $gross = (float)($atx['total_amount'] ?? 0);
            $fee = (float)($atx['fee_amount'] ?? 0);
            $net = max(0, $gross - $fee);
            if (($atx['status'] ?? '') === 'completed') {
                $totalActGross += $gross;
                $totalActFee += $fee;
                $totalActNet += $net;
            }
            fputcsv($out, [
                $atx['reference'],
                $atx['payer_name'] ?: 'Guest Voter',
                $atx['payer_email'] ?: 'N/A',
                number_format($gross, 2),
                number_format($fee, 2),
                number_format($net, 2),
                ucfirst($atx['status'] ?? 'pending'),
                $atx['created_at'] ?? 'N/A'
            ]);
        }
        fputcsv($out, []);
        fputcsv($out, ['TOTAL ACTIVITY TRANSACTIONS', count($actTxRows), '', number_format($totalActGross, 2), number_format($totalActFee, 2), number_format($totalActNet, 2)]);

        fclose($out);
        exit;
    }

    public function exportAccountingReport(Request $request, int|string $id)
    {
        $user = Application::$app->user;
        $isAdmin = (method_exists($user, 'isAdmin') && $user->isAdmin()) || (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) || hasRole('admin') || hasRole('super-admin');
        $event = $this->eventService->findHostEvent($id, $user->id, $isAdmin);
        if (!$event) {
            throw new \Exception("Event not found or access denied.", 404);
        }

        $db = Application::$app->db->pdo;
        \App\Services\AutoPayoutService::reconcileDiscountedTickets($db);

        // Fetch all tickets with ticket type tier name
        $stmt = $db->prepare("
            SELECT t.id, t.attendee_name, t.attendee_email, t.attendee_phone,
                   tt.name as ticket_tier, t.price, t.pass_fee, t.status, t.status_detail, t.checked_in, 
                   t.checked_in_at, t.purchase_date, t.is_issued
            FROM tickets t
            LEFT JOIN ticket_types tt ON t.ticket_type_id = tt.id
            WHERE t.event_id = :event_id
            ORDER BY t.purchase_date DESC
        ");
        $stmt->execute(['event_id' => $event->id]);
        $tickets = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];

        // Fetch all transactions for this event
        $txStmt = $db->prepare("
            SELECT * FROM transactions 
            WHERE event_id = :event_id 
            ORDER BY created_at DESC
        ");
        $txStmt->execute(['event_id' => $event->id]);
        $transactions = $txStmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];

        // Fetch ticket types
        $ttStmt = $db->prepare("SELECT * FROM ticket_types WHERE event_id = :event_id");
        $ttStmt->execute(['event_id' => $event->id]);
        $ticketTypes = $ttStmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];

        // Fetch payout if any
        $payoutStmt = $db->prepare("SELECT * FROM payouts WHERE event_id = :event_id ORDER BY id DESC LIMIT 1");
        $payoutStmt->execute(['event_id' => $event->id]);
        $payout = $payoutStmt->fetch(\PDO::FETCH_ASSOC) ?: null;

        $exportService = new \App\Services\AccountingExportService();
        $filePath = $exportService->generateEventWorkbook($event, $tickets, $transactions, $ticketTypes, $payout);

        $downloadFilename = "Eventlyy_Accounting_" . preg_replace('/[^a-zA-Z0-9_-]/', '_', strtolower($event->name)) . "_" . date('Ymd_His') . ".xlsx";

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $downloadFilename . '"');
        header('Content-Length: ' . filesize($filePath));
        header('Pragma: no-cache');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');

        if (ob_get_level()) {
            ob_end_clean();
        }

        readfile($filePath);
        @unlink($filePath);
        exit;
    }

    public function markEventCompleted(Request $request, int|string $id)
    {
        $user = Application::$app->user;
        $isAdmin = (method_exists($user, 'isAdmin') && $user->isAdmin()) || (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) || hasRole('admin') || hasRole('super-admin');
        $event = $this->eventService->findHostEvent($id, $user->id, $isAdmin);
        if (!$event) {
            throw new \Exception("Event not found or access denied.", 404);
        }

        $db = Application::$app->db->pdo;
        if ($isAdmin) {
            $stmt = $db->prepare("UPDATE events SET status = 'completed', updated_at = NOW() WHERE id = :id");
            $stmt->execute(['id' => $event->id]);
        } else {
            $stmt = $db->prepare("UPDATE events SET status = 'completed', updated_at = NOW() WHERE id = :id AND organizer_id = :org_id");
            $stmt->execute(['id' => $event->id, 'org_id' => $user->id]);
        }

        // Automatically unassign all team member roles for this completed event
        $unassignStmt = $db->prepare("DELETE FROM event_staff_assignments WHERE event_id = :event_id");
        $unassignStmt->execute(['event_id' => $event->id]);

        // Process any immediate auto-payouts if delay is 0 or triggers are pending
        if (auto_payout_enabled() && auto_payout_delay_minutes() === 0) {
            (new \App\Services\AutoPayoutService())->processAutoPayouts();
        }

        Application::$app->session->setFlash('success', 'Event marked as Completed! All team member roles assigned to this event have been automatically unassigned.');
        return back();
    }

    public function toggleFlyerPage(Request $request, int|string $id)
    {
        $user = Application::$app->user;
        $isAdmin = (method_exists($user, 'isAdmin') && $user->isAdmin()) || (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) || hasRole('admin') || hasRole('super-admin');
        $event = $this->eventService->findHostEvent($id, $user->id, $isAdmin);
        if (!$event) {
            throw new \Exception("Event not found or access denied.", 404);
        }

        $db = Application::$app->db->pdo;
        $newState = empty($event->flyer_page_enabled) ? 1 : 0;

        if ($isAdmin) {
            $stmt = $db->prepare("UPDATE events SET flyer_page_enabled = :state, updated_at = NOW() WHERE id = :id");
            $stmt->execute(['state' => $newState, 'id' => $event->id]);
        } else {
            $stmt = $db->prepare("UPDATE events SET flyer_page_enabled = :state, updated_at = NOW() WHERE id = :id AND organizer_id = :org_id");
            $stmt->execute(['state' => $newState, 'id' => $event->id, 'org_id' => $user->id]);
        }

        $flyerUrl = event_flyer_url($event);
        if ($newState) {
            Application::$app->session->setFlash('success', "Dedicated Flyer Page activated! Your clean ticket link ({$flyerUrl}) is now live.");
        } else {
            Application::$app->session->setFlash('info', 'Dedicated Flyer Page has been disabled for this event. Visitors to the flyer link will be redirected to the main event page.');
        }

        return redirect('/host/event/management/' . $event->id . '?tab=settings#tab-settings');
    }

    /**
     * AJAX Action: Toggle check-in status for an assigned team member.
     */
    public function toggleStaffCheckin(Request $request, int|string $id)
    {
        $user = Application::$app->user;
        $db = Application::$app->db->pdo;
        $data = $request->getBody();
        $assignmentId = (int)($data['assignment_id'] ?? 0);

        $isAdmin = (method_exists($user, 'isAdmin') && $user->isAdmin()) || (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) || hasRole('admin') || hasRole('super-admin');
        $event = $this->eventService->findHostEvent($id, $user->id, $isAdmin);

        if (!$event) {
            return Application::$app->response->json(['success' => false, 'message' => 'Event not found or unauthorized.'], 403);
        }

        $teamService = new \App\Services\TeamService();
        $res = $teamService->toggleStaffCheckin($assignmentId, (int)$user->id);

        return Application::$app->response->json($res);
    }

    public function deleteEvent(Request $request, int|string $id)
    {
        $user = Application::$app->user;
        $isAdmin = (method_exists($user, 'isAdmin') && $user->isAdmin()) || (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) || hasRole('admin') || hasRole('super-admin');
        $event = $this->eventService->findHostEvent($id, $user->id, $isAdmin);
        if (!$event) {
            Application::$app->session->setFlash('error', 'Event not found or access denied.');
            return Application::$app->response->redirect('/host/dashboard');
        }

        // Check 1: Event must not be completed
        if ($this->isEventCompleted($event) || $event->status === 'completed') {
            Application::$app->session->setFlash('error', 'Completed events cannot be deleted.');
            return back();
        }

        // Check 2: Event must have NO ticket payments from attendees (unless admin override)
        $db = Application::$app->db->pdo;
        $deleteSeries = !empty($request->getBody()['delete_series']);

        if ($deleteSeries && !empty($event->recurrence_group_id)) {
            $stmt = $db->prepare("
                SELECT COUNT(*) FROM tickets t 
                INNER JOIN events e ON t.event_id = e.id 
                WHERE e.recurrence_group_id = :gid AND t.status IN ('Paid', 'Comped')
            ");
            $stmt->execute(['gid' => $event->recurrence_group_id]);
            $paidTicketsCount = (int)$stmt->fetchColumn();

            if ($paidTicketsCount > 0 && !$isAdmin) {
                Application::$app->session->setFlash('error', 'The series cannot be deleted because ticket payments have already been received for one or more occurrences.');
                return back();
            }
        } else {
            $stmt = $db->prepare("SELECT COUNT(*) FROM tickets WHERE event_id = :event_id AND status IN ('Paid', 'Comped')");
            $stmt->execute(['event_id' => $event->id]);
            $paidTicketsCount = (int)$stmt->fetchColumn();

            if ($paidTicketsCount > 0 && !$isAdmin) {
                Application::$app->session->setFlash('error', 'This event cannot be deleted because ticket payments have already been received from attendees.');
                return back();
            }
        }

        // Perform event deletion and cleanup associated tables and images via DeleteEventAction
        $deleteAction = new \App\Actions\DeleteEventAction();
        if ($deleteAction->execute($event->id, $isAdmin ? null : $user->id, $deleteSeries)) {
            $msg = $deleteSeries ? 'Recurring event series was successfully deleted.' : 'Event "' . $event->name . '" was successfully deleted.';
            Application::$app->session->setFlash('success', $msg);
        } else {
            Application::$app->session->setFlash('error', 'Failed to delete event.');
        }

        return Application::$app->response->redirect('/host/dashboard');
    }

    /**
     * Re-verify a transaction with Paystack and fulfill if paid.
     */
    public function reverifyPayment(Request $request, string $ref)
    {
        $user = Application::$app->user;
        if (!$user) {
            return redirect('/login');
        }

        $paymentService = new \App\Services\PaymentService();
        $result = $paymentService->reverifyTransaction($ref);

        if ($result['success']) {
            Application::$app->session->setFlash('success', 'Payment verified successfully! Tickets issued and platform revenue updated.');
        } else {
            Application::$app->session->setFlash('error', $result['message'] ?? 'Paystack verification was not successful.');
        }

        $referer = $_SERVER['HTTP_REFERER'] ?? '';
        if ($referer && !str_contains($referer, 'tab=')) {
            $separator = str_contains($referer, '?') ? '&' : '?';
            return redirect($referer . $separator . 'tab=attendees#tab-attendees');
        }

        return back();
    }

    /**
     * Dismiss / Cancel a pending transaction that was abandoned or failed.
     */
    public function dismissTransaction(Request $request, string $ref)
    {
        $user = Application::$app->user;
        if (!$user) {
            return redirect('/login');
        }

        $ref = trim($ref);
        $db = Application::$app->db->pdo;

        // Verify transaction exists and belongs to host's event (or user is admin)
        $isAdmin = (method_exists($user, 'isAdmin') && $user->isAdmin()) 
                || (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) 
                || hasRole('admin') || hasRole('super-admin');

        $stmt = $db->prepare("
            SELECT t.id, t.reference, t.event_id, t.status, e.organizer_id 
            FROM transactions t
            LEFT JOIN events e ON t.event_id = e.id
            WHERE t.reference = :ref
            LIMIT 1
        ");
        $stmt->execute(['ref' => $ref]);
        $tx = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$tx) {
            Application::$app->session->setFlash('error', 'Transaction not found.');
            return back();
        }

        if (!$isAdmin && (int)$tx['organizer_id'] !== (int)$user->id) {
            Application::$app->session->setFlash('error', 'You are not authorized to dismiss transactions for this event.');
            return back();
        }

        if ($tx['status'] === 'completed') {
            Application::$app->session->setFlash('error', 'Completed transactions cannot be dismissed.');
            return back();
        }

        $upStmt = $db->prepare("
            UPDATE transactions 
            SET status = 'failed', 
                gateway_response = :resp, 
                updated_at = NOW() 
            WHERE reference = :ref AND status = 'pending'
        ");
        $upStmt->execute([
            'resp' => json_encode([
                'dismissed_by' => $user->id,
                'dismissed_at' => date('Y-m-d H:i:s'),
                'note' => 'Manually dismissed by host or admin'
            ]),
            'ref' => $ref
        ]);

        Application::$app->session->setFlash('success', 'Pending transaction #' . $ref . ' was dismissed and removed from pending orders.');

        $referer = $_SERVER['HTTP_REFERER'] ?? '';
        if ($referer && !str_contains($referer, 'tab=')) {
            $separator = str_contains($referer, '?') ? '&' : '?';
            return redirect($referer . $separator . 'tab=attendees#tab-attendees');
        }

        return back();
    }

    /**
     * 1-Click Duplicate / Clone an Event.
     * Clones event settings, ticket tiers, and guest performers into a new draft event.
     */
    public function duplicateEvent(Request $request, int|string $id)
    {
        $user = Application::$app->user;
        $isAdmin = (method_exists($user, 'isAdmin') && $user->isAdmin()) 
                || (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) 
                || hasRole('admin') || hasRole('super-admin');

        $duplicateAction = new \App\Actions\DuplicateEventAction();
        $hostIdConstraint = $isAdmin ? null : $user->id;

        $clonedId = $duplicateAction->execute($id, $hostIdConstraint);

        $isAjax = (method_exists($request, 'isAjax') && $request->isAjax()) 
            || (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') 
            || str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json');

        if ($clonedId) {
            $message = 'Event successfully duplicated! You can now adjust the date, tickets, and publish when ready.';
            Application::$app->session->setFlash('success', $message);

            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode([
                    'status' => 'success',
                    'message' => $message,
                    'cloned_id' => $clonedId,
                    'redirect_url' => '/host/event/edit/' . $clonedId
                ]);
                exit;
            }

            return Application::$app->response->redirect('/host/event/edit/' . $clonedId);
        }

        $errorMsg = $duplicateAction->getError() ?: 'Failed to duplicate event.';
        Application::$app->session->setFlash('error', $errorMsg);

        if ($isAjax) {
            http_response_code(422);
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => $errorMsg]);
            exit;
        }

        return back();
    }

    /**
     * POST /host/event/management/{id}/addon-redeem
     *
     * Toggle the redemption/collection status of a purchased add-on item.
     */
    public function toggleAddonRedemption(Request $request, int|string $id)
    {
        $user = Application::$app->user;
        $isAdmin = (method_exists($user, 'isAdmin') && $user->isAdmin()) || (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) || hasRole('admin') || hasRole('super-admin');
        $event = $this->eventService->findHostEvent($id, $user->id, $isAdmin);

        if (!$event) {
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Event not found']);
                return;
            }
            Application::$app->session->setFlash('error', 'Event not found.');
            Application::$app->response->redirect('/host/events');
            return;
        }

        $orderAddonId = (int)($request->getBody()['order_addon_id'] ?? 0);
        $action = $request->getBody()['action'] ?? ''; // 'fulfill' or 'unfulfill'

        if (!$orderAddonId || !in_array($action, ['fulfill', 'unfulfill'], true)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid request']);
            return;
        }

        $db = Application::$app->db->pdo;

        // Verify this order_addon belongs to this event
        $checkStmt = $db->prepare("SELECT id, redemption_status FROM order_addons WHERE id = :id AND event_id = :event_id");
        $checkStmt->execute(['id' => $orderAddonId, 'event_id' => $event->id]);
        $orderAddon = $checkStmt->fetch(\PDO::FETCH_ASSOC);

        if (!$orderAddon) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Add-on order not found']);
            return;
        }

        $newStatus = ($action === 'fulfill') ? 'fulfilled' : 'pending';
        $redeemedAt = ($action === 'fulfill') ? date('Y-m-d H:i:s') : null;

        $updateStmt = $db->prepare("UPDATE order_addons SET redemption_status = :status, redeemed_at = :redeemed_at WHERE id = :id");
        $updateStmt->execute([
            'status' => $newStatus,
            'redeemed_at' => $redeemedAt,
            'id' => $orderAddonId,
        ]);

        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'new_status' => $newStatus,
                'redeemed_at' => $redeemedAt,
                'message' => ($action === 'fulfill') ? 'Marked as collected' : 'Reverted to pending',
            ]);
            return;
        }

        Application::$app->session->setFlash('success', ($action === 'fulfill') ? 'Add-on marked as collected.' : 'Add-on reverted to pending.');
        Application::$app->response->redirect('/host/event/management/' . $event->id . '?tab=merchandise');
    }

    /**
     * POST /host/event/request-domain/{id}
     *
     * Host submits a request for Custom Domain / API integration.
     * Admin must review, approve, and activate before the host gets access.
     */
    public function requestCustomDomain(Request $request, int|string $id)
    {
        $user = Application::$app->user;
        $isAdmin = (method_exists($user, 'isAdmin') && $user->isAdmin()) || (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) || hasRole('admin') || hasRole('super-admin');
        $event = $this->eventService->findHostEvent($id, $user->id, $isAdmin);

        if (!$event) {
            throw new \Exception("Event not found or access denied.", 404);
        }

        $domainFeatureEnabled = (site_setting('custom_domain_enabled', '0') === '1');
        if (!$domainFeatureEnabled && !$isAdmin) {
            Application::$app->session->setFlash('error', 'Custom Domain & API integration is currently unavailable for event organizers.');
            return redirect('/host/event/management/' . $event->id);
        }

        $db = Application::$app->db->pdo;
        $body = $request->getBody();

        // Check if there's already an active or pending request for this event
        $existingStmt = $db->prepare("
            SELECT id, status FROM event_domain_requests 
            WHERE event_id = :event_id AND host_id = :host_id 
            AND status IN ('pending', 'approved_active')
            LIMIT 1
        ");
        $existingStmt->execute(['event_id' => $event->id, 'host_id' => $user->id]);
        $existing = $existingStmt->fetch(\PDO::FETCH_ASSOC);

        if ($existing) {
            $msg = $existing['status'] === 'approved_active'
                ? 'Your Custom Domain & API access is already active for this event.'
                : 'You already have a pending request for this event. Please wait for admin approval.';
            Application::$app->session->setFlash('warning', $msg);
            return redirect('/host/event/management/' . $event->id . '?tab=api-embed#tab-api-embed');
        }

        $requestedDomain = trim($body['requested_domain'] ?? '');
        $integrationType = trim($body['integration_type'] ?? 'both');
        $hostNotes = trim($body['host_notes'] ?? '');

        if (empty($requestedDomain)) {
            Application::$app->session->setFlash('error', 'Please enter your website domain (e.g. tickets.yourbrand.com).');
            return redirect('/host/event/management/' . $event->id . '?tab=api-embed#tab-api-embed');
        }

        if (!in_array($integrationType, ['both', 'custom_domain', 'api_access'])) {
            $integrationType = 'both';
        }

        $fee = (float)get_setting('custom_domain_fee', 10000.00);

        $insertStmt = $db->prepare("
            INSERT INTO event_domain_requests 
                (event_id, host_id, requested_domain, integration_type, fee_amount, payment_status, status, host_notes, created_at)
            VALUES 
                (:event_id, :host_id, :domain, :type, :fee, 'unpaid', 'pending', :notes, NOW())
        ");
        $insertStmt->execute([
            'event_id' => $event->id,
            'host_id' => $user->id,
            'domain' => $requestedDomain,
            'type' => $integrationType,
            'fee' => $fee,
            'notes' => $hostNotes
        ]);

        $requestId = (int)$db->lastInsertId();

        // If host opted to pay setup fee immediately
        if (!empty($body['pay_now']) && $fee > 0) {
            return $this->initiateDomainPayment($requestId, (int)$event->id, $requestedDomain, $fee, $user);
        }

        Application::$app->session->setFlash('success', 'Your Custom Domain & API request has been submitted! You can pay the activation fee now or wait for admin review.');
        return redirect('/host/event/management/' . $event->id . '?tab=api-embed#tab-api-embed');
    }

    /**
     * POST /host/event/domain-request/{id}/pay
     *
     * Host initiates online payment for an existing custom domain request.
     */
    public function payCustomDomainFee(Request $request, int|string $id)
    {
        $user = Application::$app->user;
        $db = Application::$app->db->pdo;

        $stmt = $db->prepare("SELECT * FROM event_domain_requests WHERE id = :id AND host_id = :host_id LIMIT 1");
        $stmt->execute(['id' => $id, 'host_id' => $user->id]);
        $domainReq = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$domainReq) {
            Application::$app->session->setFlash('error', 'Custom domain request not found or access denied.');
            return back();
        }

        $isAdmin = (method_exists($user, 'isAdmin') && $user->isAdmin()) || (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) || hasRole('admin') || hasRole('super-admin');
        $domainFeatureEnabled = (site_setting('custom_domain_enabled', '0') === '1');
        if (!$domainFeatureEnabled && !$isAdmin) {
            Application::$app->session->setFlash('error', 'Custom Domain & API integration is currently unavailable for event organizers.');
            return redirect('/host/event/management/' . ($domainReq['event_id'] ?? ''));
        }

        if ($domainReq['payment_status'] === 'paid') {
            Application::$app->session->setFlash('info', 'This request has already been paid for.');
            return redirect('/host/event/management/' . $domainReq['event_id'] . '?tab=api-embed#tab-api-embed');
        }

        $fee = (float)$domainReq['fee_amount'];
        if ($fee <= 0) {
            $upd = $db->prepare("UPDATE event_domain_requests SET payment_status = 'paid' WHERE id = :id");
            $upd->execute(['id' => $domainReq['id']]);
            Application::$app->session->setFlash('success', 'Fee waived / marked as paid.');
            return redirect('/host/event/management/' . $domainReq['event_id'] . '?tab=api-embed#tab-api-embed');
        }

        return $this->initiateDomainPayment((int)$domainReq['id'], (int)$domainReq['event_id'], $domainReq['requested_domain'], $fee, $user);
    }

    /**
     * Helper to initiate Paystack or active gateway transaction for domain setup fee.
     */
    private function initiateDomainPayment(int $requestId, int $eventId, string $domain, float $fee, $user)
    {
        $db = Application::$app->db->pdo;
        $reference = 'DOM-' . $requestId . '-' . strtoupper(bin2hex(random_bytes(5)));
        $callbackUrl = rtrim($_ENV['APP_URL'] ?? ('http://' . ($_SERVER['HTTP_HOST'] ?? 'localhost')), '/') . '/host/event/domain-request/callback';

        // Update payment reference on the request
        $upd = $db->prepare("UPDATE event_domain_requests SET payment_reference = :ref WHERE id = :id");
        $upd->execute(['ref' => $reference, 'id' => $requestId]);

        // Record pending transaction
        $trxStmt = $db->prepare("
            INSERT INTO transactions (reference, event_id, user_id, payer_name, payer_email, type, payment_method, subtotal_amount, fee_amount, total_amount, status, created_at, updated_at)
            VALUES (:ref, :event_id, :user_id, :name, :email, 'custom_domain_fee', 'paystack', :subtotal, 0, :total, 'pending', NOW(), NOW())
            ON DUPLICATE KEY UPDATE status = 'pending', total_amount = VALUES(total_amount), updated_at = NOW()
        ");
        $trxStmt->execute([
            'ref' => $reference,
            'event_id' => $eventId,
            'user_id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'subtotal' => $fee,
            'total' => $fee
        ]);

        $defaultGateway = site_setting('default_gateway', 'paystack');

        try {
            if ($defaultGateway === 'flutterwave') {
                $flw = new \Ace\FlutterwaveService();
                $link = $flw->initializePayment($user->email, $fee, $callbackUrl, $reference);
                return redirect($link);
            } else {
                $paystack = new \Ace\PaystackService();
                $paystackData = $paystack->initializeTransaction(
                    $user->email,
                    $fee,
                    $callbackUrl,
                    $reference,
                    [
                        'type' => 'custom_domain_fee',
                        'domain_request_id' => $requestId,
                        'event_id' => $eventId,
                        'domain' => $domain
                    ]
                );
                return redirect($paystackData['authorization_url']);
            }
        } catch (\Exception $e) {
            Application::$app->session->setFlash('error', 'Payment gateway error: ' . $e->getMessage());
            return redirect('/host/event/management/' . $eventId . '?tab=api-embed#tab-api-embed');
        }
    }

    /**
     * GET /host/event/domain-request/callback
     *
     * Handles gateway redirect after paying domain setup fee.
     */
    public function domainPaymentCallback(Request $request)
    {
        $reference = $_GET['reference'] ?? ($_GET['trxref'] ?? ($_GET['tx_ref'] ?? ''));

        if (empty($reference)) {
            Application::$app->session->setFlash('error', 'No transaction reference found in callback.');
            return redirect('/host/events');
        }

        $db = Application::$app->db->pdo;
        $stmt = $db->prepare("SELECT * FROM event_domain_requests WHERE payment_reference = :ref LIMIT 1");
        $stmt->execute(['ref' => $reference]);
        $domainReq = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$domainReq) {
            Application::$app->session->setFlash('error', 'Associated domain request not found for this transaction.');
            return redirect('/host/events');
        }

        $eventId = (int)$domainReq['event_id'];

        // If already verified
        if ($domainReq['payment_status'] === 'paid') {
            Application::$app->session->setFlash('success', 'Your payment is already confirmed and request is under review.');
            return redirect('/host/event/management/' . $eventId . '?tab=api-embed#tab-api-embed');
        }

        // Verify with Paystack / Flutterwave
        $isSuccessful = false;
        try {
            $paystack = new \Ace\PaystackService();
            $verifyResult = $paystack->verifyTransaction($reference);
            $payStatus = strtolower($verifyResult['status'] ?? ($verifyResult['data']['status'] ?? ''));
            if ($payStatus === 'success') {
                $isSuccessful = true;
            }
        } catch (\Throwable $e) {
            error_log("Domain payment verification exception: " . $e->getMessage());
            // Check Flutterwave fallback if applicable
            if (isset($_GET['status']) && $_GET['status'] === 'successful') {
                $isSuccessful = true;
            }
        }

        if ($isSuccessful) {
            $upd = $db->prepare("UPDATE event_domain_requests SET payment_status = 'paid' WHERE id = :id");
            $upd->execute(['id' => $domainReq['id']]);

            $txUpd = $db->prepare("UPDATE transactions SET status = 'completed', updated_at = NOW() WHERE reference = :ref");
            $txUpd->execute(['ref' => $reference]);

            Application::$app->session->setFlash('success', '🎉 Payment of ' . format_price((float)$domainReq['fee_amount']) . ' confirmed! Your Custom Domain request is marked as Paid and pending Admin review.');
        } else {
            Application::$app->session->setFlash('warning', 'Payment could not be verified automatically. You can retry anytime.');
        }

        return redirect('/host/event/management/' . $eventId . '?tab=api-embed#tab-api-embed');
    }
}