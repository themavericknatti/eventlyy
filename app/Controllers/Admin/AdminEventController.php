<?php

/**
 * AdminEventController
 *
 * This controller handles admin event-related routes.
 *
 * @package Evently
 * @subpackage Controllers/AdminEvent
 * @author Your Name <Celio Natti>
 * @license MIT License
 * @version 1.0.0
 */

namespace App\Controllers\Admin;

use Ace\Application;
use Ace\Controller;
use Ace\Request;
use App\Middlewares\AdminMiddleware;
use App\Services\AdminEventService;
use App\Actions\DeleteEventAction;

class AdminEventController extends Controller
{
    protected AdminEventService $eventService;
    protected DeleteEventAction $deleteEventAction;

    /**
     * AdminEventController constructor.
     * Registers the AdminMiddleware and initializes event service and action.
     */
    public function __construct()
    {
        $this->registerMiddleware(new AdminMiddleware());
        $this->eventService = new AdminEventService();
        $this->deleteEventAction = new DeleteEventAction();
    }

    public function events(Request $request)
    {
        // Query stats via AdminEventService
        $stats = $this->eventService->getEventStats();

        // Get filter inputs
        $search = $request->getBody()['q'] ?? '';
        $status = $request->getBody()['status'] ?? '';

        // Fetch events queue via AdminEventService
        $events = $this->eventService->getFilteredEvents($search, $status);

        return $this->render('admin/events', array_merge($stats, [
            'events' => $events,
            'search' => $search,
            'status' => $status
        ]));
    }

    public function deleteEvent(Request $request)
    {
        $data = $request->getBody();
        $eventId = $data['event_id'] ?? '';

        if (!$eventId) {
            Application::$app->session->setFlash('error', 'Event ID is missing.');
            return redirect('/admin/events');
        }

        // Delete the event via DeleteEventAction
        if ($this->deleteEventAction->execute($eventId)) {
            Application::$app->session->setFlash('success', 'Event deleted successfully!');
        } else {
            Application::$app->session->setFlash('error', 'Failed to delete event.');
        }

        return redirect('/admin/events');
    }

    public function toggleFeatured(Request $request)
    {
        $data = $request->getBody();
        $eventId = $data['event_id'] ?? '';

        if (!$eventId) {
            Application::$app->session->setFlash('error', 'Event ID is required.');
            return back();
        }

        $db = Application::$app->db->pdo;
        $stmt = $db->prepare("SELECT id, name, is_featured FROM events WHERE id = :id");
        $stmt->execute(['id' => $eventId]);
        $event = $stmt->fetch();

        if (!$event) {
            Application::$app->session->setFlash('error', 'Event not found.');
            return back();
        }

        $newFeatured = $event['is_featured'] ? 0 : 1;
        $updateStmt = $db->prepare("UPDATE events SET is_featured = :feat WHERE id = :id");
        $updateStmt->execute(['feat' => $newFeatured, 'id' => $eventId]);

        $msg = $newFeatured 
            ? "Event '{$event['name']}' is now featured on the homepage carousel!" 
            : "Event '{$event['name']}' is no longer featured on the carousel.";
        
        Application::$app->session->setFlash('success', $msg);
        return back();
    }

    public function eventDetail(Request $request, int|string $id)
    {
        // Fetch event details via AdminEventService
        $details = $this->eventService->getEventDetails($id);

        if (!$details) {
            throw new \Exception("Event not found.", 404);
        }

        return $this->render('admin/event-detail', array_merge([
            'title' => 'Event Detail - ' . $details['event']['name']
        ], $details));
    }

    /**
     * Admin action to 1-Click Duplicate an event.
     */
    public function duplicateEvent(Request $request, int|string $id)
    {
        $duplicateAction = new \App\Actions\DuplicateEventAction();
        // Admins have full access across all hosts
        $clonedId = $duplicateAction->execute($id, null);

        if ($clonedId) {
            Application::$app->session->setFlash('success', 'Event successfully duplicated as a new draft!');
            return redirect('/admin/events');
        }

        Application::$app->session->setFlash('error', $duplicateAction->getError() ?: 'Failed to duplicate event.');
        return back();
    }

    /**
     * List all domain requests with joins to events and users tables.
     */
    public function domainRequests(Request $request)
    {
        $statusFilter = $request->getBody()['status'] ?? 'all';
        $pdo = Application::$app->db->pdo;

        // Fetch counts
        $countsQuery = $pdo->query("SELECT status, COUNT(*) as count FROM event_domain_requests GROUP BY status");
        $rawCounts = $countsQuery->fetchAll(\PDO::FETCH_ASSOC);
        
        $counts = ['all' => 0, 'pending' => 0, 'approved_active' => 0, 'rejected' => 0];
        foreach ($rawCounts as $row) {
            $counts[$row['status']] = (int) $row['count'];
            $counts['all'] += (int) $row['count'];
        }

        // Fetch requests
        $sql = "SELECT r.*, e.name as event_name, e.slug as event_slug, e.custom_url as event_custom_url,
                       u.name as host_name, u.email as host_email 
                FROM event_domain_requests r
                LEFT JOIN events e ON r.event_id = e.id
                LEFT JOIN users u ON r.host_id = u.id";
        
        $params = [];
        if ($statusFilter !== 'all') {
            $sql .= " WHERE r.status = :status";
            $params['status'] = $statusFilter;
        }
        
        $sql .= " ORDER BY r.created_at DESC";
        
        $stmt = $pdo->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue(":$k", $v);
        }
        $stmt->execute();
        $requests = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return $this->render('admin/domain-requests', [
            'title' => '🌐 Custom Domain & API Requests',
            'requests' => $requests,
            'statusFilter' => $statusFilter,
            'counts' => $counts
        ]);
    }

    /**
     * Approve a domain request.
     */
    public function approveDomainRequest(Request $request, int|string $id)
    {
        $body = $request->getBody();
        $adminNotes = $body['admin_notes'] ?? '';
        $dnsTarget = trim($body['dns_cname_target'] ?? '');
        $waiveFee = isset($body['waive_fee']) ? true : false;
        
        $pdo = Application::$app->db->pdo;
        
        $sql = "UPDATE event_domain_requests 
                SET status = 'approved_active', 
                    activated_at = NOW(), 
                    admin_notes = :notes";
        $params = ['notes' => $adminNotes, 'id' => $id];

        if (!empty($dnsTarget)) {
            $sql .= ", dns_cname_target = :dns";
            $params['dns'] = $dnsTarget;
        }

        if ($waiveFee) {
            $sql .= ", payment_status = 'waived'";
        }
        $sql .= " WHERE id = :id";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        Application::$app->session->setFlash('success', 'Domain request approved successfully.');
        return back();
    }

    /**
     * Reject a domain request.
     */
    public function rejectDomainRequest(Request $request, int|string $id)
    {
        $body = $request->getBody();
        $adminNotes = $body['admin_notes'] ?? '';
        
        $pdo = Application::$app->db->pdo;
        $stmt = $pdo->prepare("UPDATE event_domain_requests SET status = 'rejected', admin_notes = :notes WHERE id = :id");
        $stmt->execute([
            'notes' => $adminNotes,
            'id' => $id
        ]);
        
        Application::$app->session->setFlash('success', 'Domain request rejected.');
        return back();
    }

    /**
     * Admin action to manually mark a domain setup fee as paid.
     */
    public function markDomainRequestPaid(Request $request, int|string $id)
    {
        $pdo = Application::$app->db->pdo;
        $stmt = $pdo->prepare("UPDATE event_domain_requests SET payment_status = 'paid' WHERE id = :id");
        $stmt->execute(['id' => $id]);

        $pdo->prepare("UPDATE transactions SET status = 'completed', updated_at = NOW() WHERE reference = (SELECT payment_reference FROM event_domain_requests WHERE id = :id)")->execute(['id' => $id]);

        Application::$app->session->setFlash('success', 'Domain setup fee marked as Paid successfully.');
        return back();
    }

    /**
     * Admin action to re-verify domain payment with Paystack gateway.
     */
    public function verifyDomainPayment(Request $request, int|string $id)
    {
        $pdo = Application::$app->db->pdo;
        $stmt = $pdo->prepare("SELECT * FROM event_domain_requests WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $req = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$req || empty($req['payment_reference'])) {
            Application::$app->session->setFlash('error', 'No payment reference found for this request.');
            return back();
        }

        try {
            $paystack = new \Ace\PaystackService();
            $result = $paystack->verifyTransaction($req['payment_reference']);
            $status = strtolower($result['status'] ?? ($result['data']['status'] ?? ''));

            if ($status === 'success') {
                $pdo->prepare("UPDATE event_domain_requests SET payment_status = 'paid' WHERE id = :id")->execute(['id' => $id]);
                $pdo->prepare("UPDATE transactions SET status = 'completed', updated_at = NOW() WHERE reference = :ref")->execute(['ref' => $req['payment_reference']]);
                Application::$app->session->setFlash('success', 'Payment verified as Successful with Paystack! Status updated to Paid.');
            } else {
                Application::$app->session->setFlash('warning', 'Gateway returned status: ' . ($status ?: 'unknown'));
            }
        } catch (\Throwable $e) {
            Application::$app->session->setFlash('error', 'Gateway verification failed: ' . $e->getMessage());
        }

        return back();
    }
}