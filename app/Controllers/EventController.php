<?php

/**
 * EventController
 *
 * Handles public event pages with secure, detailed slug-based URLs.
 * Obfuscates internal database integer IDs (e.g. /event/2 -> /event/tech-innovators-summit-b39e997d).
 *
 * @package Evently
 * @subpackage Controllers/Event
 * @license MIT License
 * @version 2.0.0
 */

namespace App\Controllers;

use Ace\Application;
use Ace\Controller;
use Ace\Request;

class EventController extends Controller
{
    /**
     * Render the single event detail page by slug, custom URL, or legacy ID.
     */
    public function event(Request $request, int|string $id): string
    {
        $db = Application::$app->db->pdo;

        // 1. Resolve event by ID, slug, or custom_url
        if (is_numeric($id)) {
            $stmt = $db->prepare("
                SELECT e.*, 
                       u.name as organizer_name, 
                       u.email as organizer_email, 
                       u.phone as organizer_phone,
                       hp.bio as organizer_bio, 
                       hp.rating as organizer_rating
                FROM events e
                INNER JOIN users u ON e.organizer_id = u.id
                LEFT JOIN host_profiles hp ON hp.user_id = u.id
                WHERE e.id = :id
            ");
            $stmt->execute(['id' => (int)$id]);
            $event = $stmt->fetch();
        } else {
            $stmt = $db->prepare("
                SELECT e.*, 
                       u.name as organizer_name, 
                       u.email as organizer_email, 
                       u.phone as organizer_phone,
                       hp.bio as organizer_bio, 
                       hp.rating as organizer_rating
                FROM events e
                INNER JOIN users u ON e.organizer_id = u.id
                LEFT JOIN host_profiles hp ON hp.user_id = u.id
                WHERE e.slug = :slug OR e.custom_url = :custom_url
            ");
            $stmt->execute(['slug' => $id, 'custom_url' => $id]);
            $event = $stmt->fetch();
        }

        if (!$event) {
            throw new \Exception("Event not found.", 404);
        }

        $internalId = (int)$event['id'];

        // 2. Track referral link clicks if ?ref= parameter is provided
        $refCode = trim($_GET['ref'] ?? ($request->getBody()['ref'] ?? ''));
        if (!empty($refCode)) {
            $refStmt = $db->prepare("SELECT id FROM event_referrals WHERE event_id = :event_id AND referral_code = :code AND status = 'active' LIMIT 1");
            $refStmt->execute(['event_id' => $internalId, 'code' => strtolower($refCode)]);
            $referral = $refStmt->fetch();
            if ($referral) {
                \App\Models\EventReferral::recordVisit((int)$referral['id']);
                Application::$app->session->set('event_ref_' . $internalId, (int)$referral['id']);
            }
        }

        // 3. Perform 301 Canonical Permanent Redirect to custom URL or slug if accessed by numeric ID
        if (is_numeric($id)) {
            $canonicalSlug = !empty($event['custom_url']) ? $event['custom_url'] : ($event['slug'] ?? '');
            if (!empty($canonicalSlug)) {
                $redirectUrl = route('event/{id}', ['id' => $canonicalSlug]);
                if (!empty($_SERVER['QUERY_STRING'])) {
                    $redirectUrl .= '?' . $_SERVER['QUERY_STRING'];
                }
                redirect($redirectUrl, 301);
                return '';
            }
        }

        // 4. Fetch ticket types for this event using internal ID
        $stmt = $db->prepare("
            SELECT tt.*,
                   (SELECT COUNT(*) FROM tickets t WHERE t.ticket_type_id = tt.id AND t.status IN ('Paid', 'Comped')) as sold
            FROM ticket_types tt
            WHERE tt.event_id = :event_id
            ORDER BY tt.price ASC
        ");
        $stmt->execute(['event_id' => $internalId]);
        $ticketTypes = $stmt->fetchAll() ?: [];

        // Fetch special guests for this event
        $guestStmt = $db->prepare("SELECT * FROM event_guests WHERE event_id = :event_id ORDER BY id ASC");
        $guestStmt->execute(['event_id' => $internalId]);
        $eventGuests = $guestStmt->fetchAll() ?: [];

        return $this->render('event', [
            'title' => $event['name'] . ' - Evently',
            'event' => (object)$event,
            'ticketTypes' => $ticketTypes,
            'eventGuests' => $eventGuests,
            'refCode' => $refCode
        ]);
    }

    /**
     * Render single event page by custom URL or slug.
     */
    public function eventBySlug(Request $request, string $slug): string
    {
        return $this->event($request, $slug);
    }

    /**
     * Render the dedicated immersive flyer ticket page.
     * Accessible via /f/{slug} or /flyer/{slug}.
     * Only available when the host has enabled the flyer page for the event.
     */
    public function flyerPage(Request $request, int|string $id): string
    {
        $db = Application::$app->db->pdo;

        // 1. Resolve event by ID, slug, or custom_url
        if (is_numeric($id)) {
            $stmt = $db->prepare("
                SELECT e.*, 
                       u.name as organizer_name, 
                       u.email as organizer_email,
                       hp.bio as organizer_bio,
                       hp.rating as organizer_rating
                FROM events e
                INNER JOIN users u ON e.organizer_id = u.id
                LEFT JOIN host_profiles hp ON hp.user_id = u.id
                WHERE e.id = :id
            ");
            $stmt->execute(['id' => (int)$id]);
            $event = $stmt->fetch();
        } else {
            $stmt = $db->prepare("
                SELECT e.*, 
                       u.name as organizer_name, 
                       u.email as organizer_email,
                       hp.bio as organizer_bio,
                       hp.rating as organizer_rating
                FROM events e
                INNER JOIN users u ON e.organizer_id = u.id
                LEFT JOIN host_profiles hp ON hp.user_id = u.id
                WHERE e.slug = :slug OR e.custom_url = :custom_url
            ");
            $stmt->execute(['slug' => $id, 'custom_url' => $id]);
            $event = $stmt->fetch();
        }

        if (!$event) {
            throw new \Exception("Event not found.", 404);
        }

        // 2. Check if flyer page is enabled by the host
        if (empty($event['flyer_page_enabled'])) {
            // Flyer page not enabled — redirect to the standard event page
            redirect(event_url($event));
            return '';
        }

        $internalId = (int)$event['id'];

        // 3. Canonical redirect: numeric ID -> clean slug URL
        if (is_numeric($id)) {
            $canonicalSlug = !empty($event['custom_url']) ? $event['custom_url'] : ($event['slug'] ?? '');
            if (!empty($canonicalSlug)) {
                $redirectUrl = route('f/{id}', ['id' => $canonicalSlug]);
                if (!empty($_SERVER['QUERY_STRING'])) {
                    $redirectUrl .= '?' . $_SERVER['QUERY_STRING'];
                }
                redirect($redirectUrl, 301);
                return '';
            }
        }

        // 4. Track referral link clicks
        $refCode = trim($_GET['ref'] ?? ($request->getBody()['ref'] ?? ''));
        if (!empty($refCode)) {
            $refStmt = $db->prepare("SELECT id FROM event_referrals WHERE event_id = :event_id AND referral_code = :code AND status = 'active' LIMIT 1");
            $refStmt->execute(['event_id' => $internalId, 'code' => strtolower($refCode)]);
            $referral = $refStmt->fetch();
            if ($referral) {
                \App\Models\EventReferral::recordVisit((int)$referral['id']);
                Application::$app->session->set('event_ref_' . $internalId, (int)$referral['id']);
            }
        }

        // 5. Fetch ticket types
        $stmt = $db->prepare("
            SELECT tt.*,
                   (SELECT COUNT(*) FROM tickets t WHERE t.ticket_type_id = tt.id AND t.status IN ('Paid', 'Comped')) as sold
            FROM ticket_types tt
            WHERE tt.event_id = :event_id
            ORDER BY tt.price ASC
        ");
        $stmt->execute(['event_id' => $internalId]);
        $ticketTypes = $stmt->fetchAll() ?: [];

        // 6. Event status
        $eventDate = strtotime($event['date'] ?? '');
        $liveWindowEnd = $eventDate ? $eventDate + (event_completion_hours() * 3600) : 0;
        $isCompleted = ($eventDate && time() > $liveWindowEnd) || in_array(strtolower($event['status'] ?? ''), ['completed', 'past', 'cancelled']);

        return $this->render('flyer', [
            'title' => $event['name'] . ' — Get Tickets',
            'event' => (object)$event,
            'ticketTypes' => $ticketTypes,
            'refCode' => $refCode,
            'isCompleted' => $isCompleted,
        ]);
    }

    /**
     * Render attendee feedback & rating form.
     */
    public function feedbackForm(Request $request, int|string $id): string
    {
        $db = Application::$app->db->pdo;

        // Resolve event by ID, slug, or custom_url
        if (is_numeric($id)) {
            $stmt = $db->prepare("SELECT e.*, u.name as organizer_name FROM events e INNER JOIN users u ON e.organizer_id = u.id WHERE e.id = :id");
            $stmt->execute(['id' => (int)$id]);
        } else {
            $stmt = $db->prepare("SELECT e.*, u.name as organizer_name FROM events e INNER JOIN users u ON e.organizer_id = u.id WHERE e.slug = :slug OR e.custom_url = :custom_url");
            $stmt->execute(['slug' => $id, 'custom_url' => $id]);
        }
        $event = $stmt->fetch();

        if (!$event) {
            throw new \Exception("Event not found.", 404);
        }

        return $this->render('event-feedback', [
            'event' => (object)$event,
            'submitted' => false,
            'errors' => []
        ]);
    }

    /**
     * Handle submission of attendee feedback & rating.
     */
    public function submitFeedback(Request $request, int|string $id): string
    {
        $db = Application::$app->db->pdo;

        if (is_numeric($id)) {
            $stmt = $db->prepare("SELECT e.*, u.name as organizer_name FROM events e INNER JOIN users u ON e.organizer_id = u.id WHERE e.id = :id");
            $stmt->execute(['id' => (int)$id]);
        } else {
            $stmt = $db->prepare("SELECT e.*, u.name as organizer_name FROM events e INNER JOIN users u ON e.organizer_id = u.id WHERE e.slug = :slug OR e.custom_url = :custom_url");
            $stmt->execute(['slug' => $id, 'custom_url' => $id]);
        }
        $event = $stmt->fetch();

        if (!$event) {
            throw new \Exception("Event not found.", 404);
        }

        $reviewModel = new \App\Models\EventReview();
        $action = new \App\Actions\SubmitEventReviewAction();

        $success = $action->execute($request->getBody(), (object)$event, $reviewModel);

        return $this->render('event-feedback', [
            'event' => (object)$event,
            'submitted' => $success,
            'errors' => $success ? [] : $reviewModel->getErrors()
        ]);
    }

    /**
     * Download or open iCalendar (.ics) format file for Apple Calendar, Outlook, and mobile calendar apps.
     */
    public function calendarIcs(Request $request, int|string $id): void
    {
        $db = Application::$app->db->pdo;

        if (is_numeric($id)) {
            $stmt = $db->prepare("SELECT * FROM events WHERE id = :id LIMIT 1");
            $stmt->execute(['id' => (int)$id]);
        } else {
            $stmt = $db->prepare("SELECT * FROM events WHERE slug = :slug OR custom_url = :custom_url LIMIT 1");
            $stmt->execute(['slug' => $id, 'custom_url' => $id]);
        }
        $event = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$event) {
            http_response_code(404);
            echo "Event not found";
            exit;
        }

        $times = event_calendar_times($event);
        $siteTitle = site_setting('site_title', 'Eventlyy');
        $eventUrl = event_url($event);
        $cleanDesc = str_replace(["\r\n", "\n", "\r"], "\\n", addcslashes(strip_tags((string)$event['description']), ",;"));
        $cleanSummary = addcslashes((string)$event['name'], ",;");
        $cleanLocation = addcslashes((string)$event['location'], ",;");
        $nowUtc = gmdate('Ymd\THis\Z');
        $domain = parse_url($eventUrl, PHP_URL_HOST) ?: 'eventlyy.com';

        $ics = "BEGIN:VCALENDAR\r\n"
             . "VERSION:2.0\r\n"
             . "PRODID:-//{$siteTitle}//Event Ticketing//EN\r\n"
             . "CALSCALE:GREGORIAN\r\n"
             . "METHOD:PUBLISH\r\n"
             . "BEGIN:VEVENT\r\n"
             . "UID:eventlyy-event-{$event['id']}@{$domain}\r\n"
             . "DTSTAMP:{$nowUtc}\r\n"
             . "DTSTART:{$times['start_utc']}\r\n"
             . "DTEND:{$times['end_utc']}\r\n"
             . "SUMMARY:{$cleanSummary}\r\n"
             . "DESCRIPTION:{$cleanDesc}\\n\\nEvent Link: {$eventUrl}\r\n"
             . "LOCATION:{$cleanLocation}\r\n"
             . "URL:{$eventUrl}\r\n"
             . "STATUS:CONFIRMED\r\n"
             . "END:VEVENT\r\n"
             . "END:VCALENDAR\r\n";

        $filename = preg_replace('/[^a-zA-Z0-9_\-]/', '_', (string)$event['name']) . '.ics';

        header('Content-Type: text/calendar; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($ics));
        header('Connection: close');

        echo $ics;
        exit;
    }
}