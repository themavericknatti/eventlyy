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
            Application::$app->session->setFlash('success', 'Event successfully created and published!');
            return Application::$app->response->redirect('/host/dashboard');
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

        // Format event date & time for Flatpickr inputs
        $dateTime = strtotime($event->date);
        $eventDate = $dateTime ? date('Y-m-d', $dateTime) : '';
        $eventTime = $dateTime ? date('H:i', $dateTime) : '';

        // Format venue & city from location field ("Venue, City")
        $locationParts = explode(',', $event->location, 2);
        $eventVenue = trim($locationParts[0] ?? '');
        $eventCity = trim($locationParts[1] ?? '');

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

        return $this->render('host/edit-event', [
            'title' => 'Edit Event - ' . $event->name,
            'event' => $event,
            'eventDate' => $eventDate,
            'eventTime' => $eventTime,
            'eventVenue' => $eventVenue,
            'eventCity' => $eventCity,
            'ticketTypes' => $ticketTypes,
            'eventGuests' => $eventGuests,
            'categories' => $this->categories,
            'isAdmin' => $isAdmin,
            'organizerName' => $organizerName,
            'organizerEmail' => $organizerEmail
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
        
        $occupancyPercentage = $totalCapacity > 0 ? (($ticketsSold + $ticketsIssued) / $totalCapacity) * 100 : 0;
        $avgPrice = $ticketsSold > 0 ? ($totalRevenue / $ticketsSold) : 0;

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

        return $this->render('host/event-management', [
            'title' => 'Manage Event - ' . $event->name,
            'event' => $event,
            'eventId' => $event->id,
            'tickets' => $tickets,
            'ticketTypes' => $ticketTypes,
            'totalRevenue' => $totalRevenue,
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
            'organizerEmail' => $organizerEmail
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
            WHERE event_id = :event_id AND status = 'Paid'
        ");
        $stmt->execute(['event_id' => $event->id]);
        $attendees = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        if (empty($attendees)) {
            Application::$app->session->setFlash('error', 'No registered attendees with paid tickets found for this event.');
            return back();
        }

        $sentCount = 0;
        foreach ($attendees as $att) {
            try {
                \Ace\Mail::send(
                    $att['attendee_email'],
                    'Event Reminder - ' . $event->name,
                    'emails/event_reminder',
                    [
                        'attendee_name' => $att['attendee_name'],
                        'event_name' => $event->name,
                        'event_date' => date('D, M d, Y • g:i A', strtotime($event->date)),
                        'event_location' => $event->location
                    ]
                );
                $sentCount++;
            } catch (\Exception $e) {
                error_log("Reminder email failed for {$att['attendee_email']}: " . $e->getMessage());
            }
        }

        Application::$app->session->setFlash('success', "Event reminder emails successfully sent to {$sentCount} attendee(s)!");
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
            SELECT t.id, t.attendee_name, t.attendee_email, 
                   tt.name as ticket_tier, t.price, t.status, t.status_detail, t.checked_in, 
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
            SELECT t.id, t.attendee_name, t.attendee_email, 
                   tt.name as ticket_tier, t.price, t.status, t.status_detail, t.checked_in, 
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

        // 4. Payout record if any
        $payoutStmt = $db->prepare("SELECT * FROM payouts WHERE event_id = :event_id ORDER BY id DESC LIMIT 1");
        $payoutStmt->execute(['event_id' => $event->id]);
        $payout = $payoutStmt->fetch(\PDO::FETCH_ASSOC) ?: null;

        // Split tickets into Online vs Issued/Comp
        $onlineTickets = [];
        $issuedTickets = [];
        $onlineGross = 0;
        $gateCashGross = 0;
        $compNominal = 0;
        $onlineCheckedIn = 0;
        $issuedCheckedIn = 0;

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
                $onlineTickets[] = $t;
            }
        }
        unset($t);

        // Totals & balances:
        // Host Net Earnings (Organizer Payout Share) is funded strictly by online ticket purchases on the platform.
        // Gate cash and direct issued tickets are retained/handled offline by the host and excluded from platform payout transfers.
        $platformFeePercent = (float)site_setting('platform_fee', 5);
        $isPassFee = !empty($event->pass_fee);
        $totalPlatformFee = $isPassFee ? 0.00 : round($onlineGross * ($platformFeePercent / 100), 2);
        $netHostEarnings = $isPassFee ? $onlineGross : max(0.00, $onlineGross - $totalPlatformFee);
        $totalGrossRevenue = $onlineGross;

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
            'onlineGross' => $onlineGross,
            'gateCashGross' => $gateCashGross,
            'compNominal' => $compNominal,
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

        $stmt = $db->prepare("
            SELECT t.id, t.attendee_name, t.attendee_email, 
                   tt.name as ticket_tier, t.price, t.status, t.status_detail, t.checked_in, 
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
        fputcsv($out, ['TOTAL TICKETS', count($tickets), '', '', '', 'TOTAL GROSS VALUE', number_format($totalRev, 2)]);

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

        // Fetch all tickets with ticket type tier name
        $stmt = $db->prepare("
            SELECT t.id, t.attendee_name, t.attendee_email, 
                   tt.name as ticket_tier, t.price, t.status, t.status_detail, t.checked_in, 
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
        $stmt = $db->prepare("SELECT COUNT(*) FROM tickets WHERE event_id = :event_id AND status IN ('Paid', 'Comped')");
        $stmt->execute(['event_id' => $event->id]);
        $paidTicketsCount = (int)$stmt->fetchColumn();

        if ($paidTicketsCount > 0 && !$isAdmin) {
            Application::$app->session->setFlash('error', 'This event cannot be deleted because ticket payments have already been received from attendees.');
            return back();
        }

        // Perform event deletion and cleanup associated tables and images via DeleteEventAction
        $deleteAction = new \App\Actions\DeleteEventAction();
        if ($deleteAction->execute($event->id, $isAdmin ? null : $user->id)) {
            Application::$app->session->setFlash('success', 'Event "' . $event->name . '" was successfully deleted.');
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