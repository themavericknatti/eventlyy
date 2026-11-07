<?php

/**
 * AdminAttendeeController
 *
 * This controller handles admin attendee and ticket buyer directory routes.
 *
 * @package Evently
 * @subpackage Controllers/Admin
 * @author Celio Natti
 * @license MIT License
 * @version 1.0.0
 */

namespace App\Controllers\Admin;

use Ace\Controller;
use Ace\Request;
use App\Middlewares\AdminMiddleware;
use App\Services\AdminAttendeeService;

class AdminAttendeeController extends Controller
{
    protected AdminAttendeeService $attendeeService;

    public function __construct()
    {
        $this->registerMiddleware(new AdminMiddleware());
        $this->attendeeService = new AdminAttendeeService();
    }

    public function index(Request $request)
    {
        $stats = $this->attendeeService->getAttendeeStats();

        $search = $request->getBody()['q'] ?? '';
        $type = $request->getBody()['type'] ?? '';
        $eventId = $request->getBody()['event_id'] ?? '';
        $page = (int)($request->getBody()['page'] ?? 1);
        if ($page < 1) $page = 1;
        $limit = 15;

        $paginated = $this->attendeeService->getFilteredAttendees($search, $type, $eventId, $page, $limit);
        $attendees = $paginated['items'];
        $totalPages = $paginated['totalPages'];
        $events = $this->attendeeService->getEventsForFilter();

        return $this->render('admin/attendees', array_merge($stats, [
            'attendees' => $attendees,
            'search' => $search,
            'type' => $type,
            'eventId' => $eventId,
            'page' => $page,
            'totalPages' => $totalPages,
            'events' => $events
        ]));
    }

    public function exportCsv(Request $request)
    {
        $search = $request->getBody()['q'] ?? '';
        $type = $request->getBody()['type'] ?? '';
        $eventId = $request->getBody()['event_id'] ?? '';

        $attendees = $this->attendeeService->getAllAttendeesForExport($search, $type, $eventId);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="evently_attendee_contacts_' . date('Y-m-d_H-i') . '.csv"');

        $output = fopen('php://output', 'w');
        // Write UTF-8 BOM for Microsoft Excel compatibility
        fputs($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

        fputcsv($output, [
            'Attendee Name',
            'Attendee Email',
            'Account Type',
            'Total Tickets Purchased',
            'Events Attended',
            'Total Amount Spent',
            'Events List',
            'Most Recent Purchase Date'
        ]);

        foreach ($attendees as $row) {
            fputcsv($output, [
                $row['attendee_name'],
                $row['attendee_email'],
                $row['attendee_type'],
                $row['total_tickets'],
                $row['total_events'],
                format_price($row['total_spent']),
                $row['event_names'] ?? 'N/A',
                date('Y-m-d H:i:s', strtotime($row['last_purchase_date']))
            ]);
        }

        fclose($output);
        exit;
    }

    public function exportPdf(Request $request)
    {
        $search = $request->getBody()['q'] ?? '';
        $type = $request->getBody()['type'] ?? '';
        $eventId = $request->getBody()['event_id'] ?? '';

        $db = \Ace\Application::$app->db->pdo;
        $event = null;
        $host = null;

        if (!empty($eventId)) {
            $evtStmt = $db->prepare("SELECT * FROM events WHERE id = :id LIMIT 1");
            $evtStmt->execute(['id' => $eventId]);
            $eventRow = $evtStmt->fetch(\PDO::FETCH_ASSOC);
            if ($eventRow) {
                $event = (object)$eventRow;
                $hostStmt = $db->prepare("SELECT name, email FROM users WHERE id = :id LIMIT 1");
                $hostStmt->execute(['id' => $event->organizer_id]);
                $host = $hostStmt->fetch(\PDO::FETCH_ASSOC) ?: [];
            }

            $stmt = $db->prepare("
                SELECT t.id, t.attendee_name, t.attendee_email, 
                       tt.name as ticket_tier, t.price, t.status, t.status_detail, t.checked_in, 
                       t.checked_in_at, t.purchase_date
                FROM tickets t
                LEFT JOIN ticket_types tt ON t.ticket_type_id = tt.id
                WHERE t.event_id = :event_id
                ORDER BY t.purchase_date DESC
            ");
            $stmt->execute(['event_id' => $eventId]);
            $attendees = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        } else {
            $attendees = $this->attendeeService->getAllAttendeesForExport($search, $type, '');
        }

        return $this->render('reports/attendee-pdf', [
            'event' => $event,
            'attendees' => $attendees,
            'host' => $host,
            'search' => $search,
            'type' => $type
        ]);
    }
}
