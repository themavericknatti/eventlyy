<?php

namespace App\Controllers;

use Ace\Controller;
use Ace\Request;
use App\Services\TicketQrService;

class TicketController extends Controller
{
    protected TicketQrService $qrService;

    public function __construct()
    {
        $this->qrService = new TicketQrService();
    }

    /**
     * Stream a high-resolution, branded ticket QR code with the Eventlyy logo.
     * Features HTTP caching (30 days, ETag, 304 Not Modified) for ultra-fast performance.
     *
     * @param Request $request
     * @param string $code Ticket admission code
     */
    public function qrCode(Request $request, string $code): void
    {
        $code = trim($code);
        if (empty($code)) {
            http_response_code(400);
            header('Content-Type: text/plain');
            echo 'Invalid ticket code';
            exit;
        }

        // ── Validate ticket existence in database ──
        $cleanCode = preg_replace('/^EV-/i', '', $code);
        $evCode = str_starts_with(strtoupper($code), 'EV-') ? $code : ('EV-' . $code);

        $db = \Ace\Application::$app->db->pdo;
        $stmt = $db->prepare("
            SELECT id FROM tickets 
            WHERE id = :code OR id = :cleancode OR id = :evcode 
            LIMIT 1
        ");
        $stmt->execute(['code' => $code, 'cleancode' => $cleanCode, 'evcode' => $evCode]);
        $ticketId = $stmt->fetchColumn();

        if (!$ticketId) {
            http_response_code(404);
            header('Content-Type: text/plain');
            echo 'Ticket not found';
            exit;
        }

        // Optional size query parameter: ?s=260
        $size = (int)($request->getInt('s') ?: 240);
        $size = max(120, min(800, $size));

        // Generate or retrieve cached clean, high-contrast QR PNG
        $png = $this->qrService->generateQrPng((string)$ticketId, $size);

        if (empty($png)) {
            http_response_code(500);
            header('Content-Type: text/plain');
            echo 'Failed to generate QR code';
            exit;
        }

        $etag = '"' . md5($png) . '"';

        // HTTP Caching: 304 Not Modified if browser/client already has it
        if (isset($_SERVER['HTTP_IF_NONE_MATCH']) && trim($_SERVER['HTTP_IF_NONE_MATCH']) === $etag) {
            http_response_code(304);
            exit;
        }

        // Clean any preceding output buffers to avoid corrupting image bytes
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        header('Content-Type: image/png');
        header('Content-Length: ' . strlen($png));
        header('Cache-Control: public, max-age=2592000, immutable'); // 30 days
        header('ETag: ' . $etag);
        header('X-Content-Type-Options: nosniff');

        echo $png;
        exit;
    }

    /**
     * Display the mobile-first VIP Digital Admission Pass for WhatsApp & mobile attendees.
     * Accessible without login so attendees can present their ticket at the gate from WhatsApp.
     *
     * @param Request $request
     * @param string $code Ticket code (e.g. EV-XXXXXXXX)
     * @return string
     */
    public function viewPass(Request $request, string $code): string
    {
        $code = trim($code);
        if (empty($code)) {
            \Ace\Application::$app->session->setFlash('error', 'Invalid ticket pass link.');
            redirect('/');
            return '';
        }

        $db = \Ace\Application::$app->db->pdo;
        $stmt = $db->prepare("
            SELECT t.*, 
                   e.id as event_id, e.name as event_name, e.slug as event_slug, e.custom_url as event_custom_url,
                   e.date as event_date, e.location as event_location, e.image as event_image, e.category as event_category,
                   e.status as event_status, e.organizer_id as event_organizer_id,
                   tt.name as ticket_tier, tt.admits as ticket_admits,
                   u.name as organizer_name
            FROM tickets t
            INNER JOIN events e ON t.event_id = e.id
            LEFT JOIN ticket_types tt ON t.ticket_type_id = tt.id
            LEFT JOIN users u ON e.organizer_id = u.id
            WHERE t.id = :code
            LIMIT 1
        ");
        $stmt->execute(['code' => $code]);
        $ticket = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$ticket) {
            \Ace\Application::$app->session->setFlash('error', 'Ticket pass not found or reference has expired.');
            redirect('/');
            return '';
        }

        // Fetch any purchased add-ons / merchandise for this order / attendee
        $addons = [];
        $txRef = '';
        if (preg_match('/Ref:\s*(EVT-[A-Z0-9\-]+|LAY-[A-Z0-9\-]+)/i', $ticket['status_detail'] ?? '', $m)) {
            $txRef = trim($m[1]);
        }

        if (!empty($txRef)) {
            $addonStmt = $db->prepare("
                SELECT oa.*, ea.name as addon_name, ea.description as addon_description, ea.image as addon_image 
                FROM order_addons oa
                JOIN event_addons ea ON ea.id = oa.addon_id
                JOIN transactions tr ON tr.id = oa.transaction_id
                WHERE tr.reference = :ref
                ORDER BY oa.id ASC
            ");
            $addonStmt->execute(['ref' => $txRef]);
            $addons = $addonStmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        }

        if (empty($addons) && !empty($ticket['attendee_email'])) {
            $addonStmt = $db->prepare("
                SELECT oa.*, ea.name as addon_name, ea.description as addon_description, ea.image as addon_image 
                FROM order_addons oa
                JOIN event_addons ea ON ea.id = oa.addon_id
                WHERE oa.event_id = :event_id AND LOWER(oa.buyer_email) = :email
                ORDER BY oa.id ASC
            ");
            $addonStmt->execute([
                'event_id' => $ticket['event_id'],
                'email' => strtolower($ticket['attendee_email'])
            ]);
            $addons = $addonStmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        }

        // Determine if pass should be unlocked
        $isUnlocked = false;
        $currentUser = \Ace\Application::$app->user;
        if ($currentUser) {
            $isOwner = ((int)($ticket['user_id'] ?? 0) === (int)$currentUser->id)
                || (strtolower($currentUser->email) === strtolower($ticket['attendee_email'] ?? ''));
            $isStaff = (($currentUser->role ?? '') === 'admin')
                || ((int)($ticket['event_organizer_id'] ?? 0) === (int)$currentUser->id);
            if ($isOwner || $isStaff) {
                $isUnlocked = true;
            }
        }
        if (\Ace\Application::$app->session->get('pass_unlocked_' . $code)) {
            $isUnlocked = true;
        }

        return $this->render('ticket/pass', [
            'title' => 'Admission Pass — ' . $ticket['event_name'],
            'ticket' => $ticket,
            'addons' => $addons,
            'isUnlocked' => $isUnlocked
        ]);
    }

    /**
     * POST: Unlock a pass by verifying the 4-digit security PIN.
     * Supports both JSON/AJAX calls (for inline success page reveal) and standard POST form submits.
     */
    public function unlockPass(Request $request, string $code)
    {
        $code = trim($code);
        $isAjax = $request->isAjax();

        if (empty($code)) {
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Invalid ticket pass link.']);
                exit;
            }
            \Ace\Application::$app->session->setFlash('error', 'Invalid ticket pass link.');
            redirect('/');
            return;
        }

        // Rate limit: max 5 PIN attempts per ticket per session
        $attemptKey = 'pin_attempts_' . $code;
        $attempts = (int)\Ace\Application::$app->session->get($attemptKey);
        if ($attempts >= 5) {
            $msg = 'Too many incorrect PIN attempts. Please request your PIN via email and try again.';
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => $msg, 'locked_out' => true]);
                exit;
            }
            \Ace\Application::$app->session->setFlash('error', $msg);
            redirect('/pass/' . urlencode($code));
            return;
        }

        $db = \Ace\Application::$app->db->pdo;
        $stmt = $db->prepare("SELECT id, security_pin, attendee_name, attendee_email FROM tickets WHERE id = :code LIMIT 1");
        $stmt->execute(['code' => $code]);
        $ticket = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$ticket) {
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Ticket not found.']);
                exit;
            }
            \Ace\Application::$app->session->setFlash('error', 'Ticket not found.');
            redirect('/');
            return;
        }

        $data = $request->getBody();
        $pin = trim((string)($data['pin'] ?? ''));

        if (empty($ticket['security_pin']) || $pin !== $ticket['security_pin']) {
            \Ace\Application::$app->session->set($attemptKey, $attempts + 1);
            $remaining = 5 - ($attempts + 1);
            $msg = $remaining > 0 ? "Incorrect PIN. {$remaining} attempt(s) remaining." : "Too many incorrect PIN attempts. Please check your email.";
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => $msg, 'remaining' => $remaining]);
                exit;
            }
            \Ace\Application::$app->session->setFlash('error', $msg);
            redirect('/pass/' . urlencode($code));
            return;
        }

        // PIN correct — unlock
        \Ace\Application::$app->session->set('pass_unlocked_' . $code, true);
        \Ace\Application::$app->session->set($attemptKey, 0); // reset attempts

        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'message' => 'Pass unlocked! Your admission code and QR pass are now revealed.',
                'ticket_code' => $ticket['id'],
                'qr_url' => url('/ticket/qr/' . urlencode($ticket['id']) . '?s=220'),
                'qr_url_large' => url('/ticket/qr/' . urlencode($ticket['id']) . '?s=300'),
                'pass_url' => url('/pass/' . urlencode($ticket['id']))
            ]);
            exit;
        }

        \Ace\Application::$app->session->setFlash('success', 'Pass unlocked! Your QR code is now visible.');
        redirect('/pass/' . urlencode($code));
    }

    /**
     * POST AJAX: Resend security PIN to the ticket holder's email.
     */
    public function resendPinAjax(Request $request, string $code): string
    {
        header('Content-Type: application/json');
        $code = trim($code);

        if (empty($code)) {
            return json_encode(['success' => false, 'message' => 'Invalid ticket code.']);
        }

        $db = \Ace\Application::$app->db->pdo;
        $stmt = $db->prepare("
            SELECT t.id, t.attendee_name, t.attendee_email, t.security_pin,
                   e.name as event_name
            FROM tickets t
            INNER JOIN events e ON t.event_id = e.id
            WHERE t.id = :code
            LIMIT 1
        ");
        $stmt->execute(['code' => $code]);
        $ticket = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$ticket || empty($ticket['attendee_email'])) {
            return json_encode(['success' => false, 'message' => 'Ticket not found or no email associated.']);
        }

        if (empty($ticket['security_pin'])) {
            // Generate a PIN if missing (legacy tickets)
            $newPin = str_pad((string)random_int(1000, 9999), 4, '0', STR_PAD_LEFT);
            $db->prepare("UPDATE tickets SET security_pin = :pin WHERE id = :id")
               ->execute(['pin' => $newPin, 'id' => $ticket['id']]);
            $ticket['security_pin'] = $newPin;
        }

        try {
            $passUrl = url('/pass/' . $ticket['id']);
            \Ace\Mail::send(
                $ticket['attendee_email'],
                'Your Pass Security PIN - ' . site_setting('site_title', 'Eventlyy'),
                'emails/ticket_pin',
                [
                    'ticketCode' => $ticket['id'],
                    'attendeeName' => $ticket['attendee_name'] ?? 'Valued Attendee',
                    'eventName' => $ticket['event_name'] ?? 'Your Event',
                    'securityPin' => $ticket['security_pin'],
                    'passUrl' => $passUrl
                ]
            );
        } catch (\Throwable $e) {
            return json_encode(['success' => false, 'message' => 'Failed to send PIN email. Please try again.']);
        }

        $masked = preg_replace('/(?<=.{2}).(?=.*@)/', '*', $ticket['attendee_email']);
        return json_encode([
            'success' => true,
            'message' => 'Security PIN sent to ' . $masked
        ]);
    }
}
