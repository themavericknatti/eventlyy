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
}