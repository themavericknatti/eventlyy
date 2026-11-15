<?php

/**
 * HostTicketController
 *
 * This controller handles host ticket-related routes.
 *
 * @package Evently
 * @subpackage Controllers/HostTicket
 * @author Your Name <Celio Natti>
 * @license MIT License
 * @version 1.0.0
 */

namespace App\Controllers\Host;

use Ace\Application;
use Ace\Controller;
use Ace\Request;
use App\Middlewares\HostMiddleware;
use App\Services\TicketService;
use App\Actions\CheckInTicketAction;

class HostTicketController extends Controller
{
    protected TicketService $ticketService;
    protected CheckInTicketAction $checkInTicketAction;

    public function __construct()
    {
        // Register the HostMiddleware for all methods in this controller
        $this->registerMiddleware(new HostMiddleware());
        $this->ticketService = new TicketService();
        $this->checkInTicketAction = new CheckInTicketAction();
    }

    public function ticketDetail(Request $request, int|string $id)
    {
        $user = Application::$app->user;
        $isAdmin = (method_exists($user, 'isAdmin') && $user->isAdmin()) || (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) || hasRole('admin') || hasRole('super-admin');
        $ticket = $this->ticketService->getTicketDetail($id, $user->id, $isAdmin);

        $isJson = $request->isAjax() 
            || str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json')
            || str_contains($_SERVER['CONTENT_TYPE'] ?? '', 'application/json')
            || !empty($request->getBody()['ajax']);

        if (!$ticket) {
            if ($isJson) {
                return Application::$app->response->json(['success' => false, 'error' => 'Ticket not found or access denied.'], 404);
            }
            throw new \Exception("Ticket not found or access denied.", 404);
        }

        // Fetch any add-on merchandise purchased by this attendee/buyer
        $db = Application::$app->db->pdo;
        $addons = [];
        if (!empty($ticket['attendee_email']) && !empty($ticket['event_id'])) {
            $addonStmt = $db->prepare("
                SELECT oa.*, ea.name as addon_name, ea.description as addon_desc, ea.image as addon_image
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

        if ($isJson) {
            $isEventStarted = !empty($ticket['event_date']) && strtotime($ticket['event_date']) <= time();
            $isCheckedIn = !empty($ticket['checked_in']);
            $isRefunded = in_array($ticket['status'] ?? '', ['Cancelled', 'Refunded']);

            return Application::$app->response->json([
                'success' => true,
                'addons' => $addons,
                'ticket' => [
                    'id' => $ticket['id'],
                    'attendee_name' => $ticket['attendee_name'] ?? 'Guest',
                    'attendee_email' => $ticket['attendee_email'] ?? '',
                    'attendee_phone' => $ticket['attendee_phone'] ?? '',
                    'pass_url' => url('/pass/' . $ticket['id']),
                    'whatsapp_url' => !empty($ticket['attendee_phone']) ? \App\Services\WhatsAppService::generateDirectWhatsAppUrl(
                        $ticket['attendee_phone'], 
                        \App\Services\WhatsAppService::buildTicketPassMessage([
                            'name' => $ticket['event_name'],
                            'date' => $ticket['event_date'] ?? null,
                            'location' => $ticket['event_location'] ?? null
                        ], $ticket)
                    ) : null,
                    'user_id' => $ticket['user_id'] ?? null,
                    'is_user' => !empty($ticket['user_id']),
                    'ticket_type' => $ticket['ticket_type'] ?? 'General Admission',
                    'price' => (float)($ticket['price'] ?? 0),
                    'price_formatted' => format_price($ticket['price'] ?? 0),
                    'status' => $ticket['status'] ?? 'Paid',
                    'status_detail' => $ticket['status_detail'] ?? 'Payment confirmed',
                    'is_issued' => !empty($ticket['is_issued']),
                    'checked_in' => $isCheckedIn,
                    'checked_in_at' => $ticket['checked_in_at'] ?? null,
                    'checked_in_time' => !empty($ticket['checked_in_at']) ? date('h:i A', strtotime($ticket['checked_in_at'])) : null,
                    'purchase_date' => $ticket['purchase_date'] ?? null,
                    'purchase_date_formatted' => !empty($ticket['purchase_date']) ? date('M d, Y \a\t h:i A', strtotime($ticket['purchase_date'])) : 'N/A',
                    'qr_url' => ticket_qr_url($ticket['id'], 220),
                    'is_refunded' => $isRefunded,
                    'is_event_started' => $isEventStarted,
                    'action_url' => route('host/event/ticket/' . $ticket['id'] . '/action'),
                    'detail_url' => route('host/event/ticket/' . $ticket['id'])
                ],
                'event' => [
                    'id' => $ticket['event_id'],
                    'name' => $ticket['event_name'],
                    'date' => $ticket['event_date'] ?? null,
                    'date_formatted' => !empty($ticket['event_date']) ? date('D, M j, Y \a\t g:i A', strtotime($ticket['event_date'])) : '',
                    'location' => $ticket['event_location'] ?? ''
                ]
            ]);
        }

        return $this->render('host/ticket-detail', [
            'title' => 'Ticket Detail - ' . $ticket['id'],
            'ticket' => $ticket,
            'addons' => $addons,
            'event' => [
                'id' => $ticket['event_id'],
                'name' => $ticket['event_name'],
                'date' => $ticket['event_date'] ?? null,
                'location' => $ticket['event_location'] ?? null
            ]
        ]);
    }

    public function verifyTicket(Request $request, int|string|null $id = null)
    {
        $user = Application::$app->user;
        $isAdmin = (method_exists($user, 'isAdmin') && $user->isAdmin()) || (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) || hasRole('admin') || hasRole('super-admin');

        // Fetch events for selector dropdown via TicketService (supports all events if admin)
        $events = $this->ticketService->getHostEventsForSelector($user->id, $isAdmin);

        // Support event_id from query params or path params
        $selectedEventId = $id ?: ($request->getBody()['event_id'] ?? null);

        // If no event selected but events exist, default to the first event
        if (!$selectedEventId && !empty($events)) {
            $selectedEventId = $events[0]['id'];
        }

        $verifiedTickets = [];
        $totalTicketsCount = 0;
        $verifiedTicketsCount = 0;
        $remainingTicketsCount = 0;

        if ($selectedEventId) {
            $verifData = $this->ticketService->getVerificationData($selectedEventId);
            $verifiedTickets = $verifData['verified_tickets'];
            $totalTicketsCount = $verifData['total_tickets_count'];
            $verifiedTicketsCount = $verifData['verified_tickets_count'];
            $remainingTicketsCount = $verifData['remaining_tickets_count'];
        }

        return $this->render('host/ticket-verification', [
            'title' => 'Ticket Verification',
            'events' => $events,
            'selected_event_id' => $selectedEventId,
            'verified_tickets' => $verifiedTickets,
            'total_tickets_count' => $totalTicketsCount,
            'verified_tickets_count' => $verifiedTicketsCount,
            'remaining_tickets_count' => $remainingTicketsCount,
            'isAdmin' => $isAdmin,
            'currentUserId' => $user->id
        ]);
    }

    public function checkIn(Request $request)
    {
        $user = Application::$app->user;
        $isAdmin = (method_exists($user, 'isAdmin') && $user->isAdmin()) || (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) || hasRole('admin') || hasRole('super-admin');
        $data = $request->getBody();
        $ticketCode = trim($data['ticket_code'] ?? $data['code'] ?? '');
        $selectedEventId = $data['event_id'] ?? null;

        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        $isJson = str_contains($accept, 'application/json') || str_contains($contentType, 'application/json') || (!empty($data['ajax']));

        if (empty($ticketCode)) {
            if ($isJson) {
                return Application::$app->response->json(['success' => false, 'status' => 'error', 'message' => 'Please enter a ticket code.'], 400);
            }
            Application::$app->session->setFlash('error', 'Please enter a ticket code.');
            return redirect('/host/event/ticket-verification/' . $selectedEventId);
        }

        // Execute action via CheckInTicketAction (with admin bypass)
        $result = $this->checkInTicketAction->execute($ticketCode, $selectedEventId, $user->id, $isAdmin);

        if ($isJson) {
            $isSuccess = ($result['status'] === 'success');
            return Application::$app->response->json([
                'success' => $isSuccess,
                'status' => $result['result']['type'] ?? ($isSuccess ? 'success' : 'error'),
                'message' => $result['result']['message'] ?? $result['flash_message'],
                'code' => $result['result']['code'] ?? $ticketCode,
                'ticket' => $result['result']['ticket'] ?? null,
                'result' => $result['result']
            ]);
        }

        Application::$app->session->setFlash($result['flash_type'], $result['flash_message']);
        Application::$app->session->setFlash('verification_result', $result['result']);

        $targetEventId = $selectedEventId;
        if ($result['status'] === 'success' && isset($result['result']['ticket']['event_id'])) {
            $targetEventId = $result['result']['ticket']['event_id'];
        }

        return redirect('/host/event/ticket-verification/' . $targetEventId);
    }

    public function ticketAction(Request $request, int|string $id)
    {
        $user = Application::$app->user;
        $isAdmin = (method_exists($user, 'isAdmin') && $user->isAdmin()) || (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) || hasRole('admin') || hasRole('super-admin');
        $data = $request->getBody();
        $action = $data['action'] ?? '';

        $ticket = $this->ticketService->getTicketDetail($id, $user->id, $isAdmin);
        if (!$ticket) {
            return Application::$app->response->json(['success' => false, 'message' => 'Ticket not found or access denied.'], 404);
        }

        $db = Application::$app->db->pdo;

        $isJson = str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') || str_contains($_SERVER['CONTENT_TYPE'] ?? '', 'application/json');
        $tabRedirect = !empty($ticket['event_id']) ? ('/host/event/management/' . $ticket['event_id'] . '?tab=attendees#tab-attendees') : null;

        if ($action === 'check_in') {
            if (in_array($ticket['status'], ['Cancelled', 'Refunded']) || ($ticket['status_detail'] ?? '') === 'Refunded') {
                $msg = 'Cannot check in a refunded or cancelled ticket.';
                if (!$isJson) {
                    Application::$app->session->setFlash('error', $msg);
                    return $tabRedirect ? redirect($tabRedirect) : back();
                }
                return Application::$app->response->json(['success' => false, 'message' => $msg], 400);
            }

            $now = date('Y-m-d H:i:s');
            $stmt = $db->prepare("UPDATE tickets SET checked_in = 1, checked_in_at = :now, checked_in_by = NULL WHERE id = :id");
            $stmt->execute(['now' => $now, 'id' => $id]);

            $msg = 'Ticket ' . $id . ' (' . $ticket['attendee_name'] . ') checked in successfully!';
            if (!$isJson) {
                Application::$app->session->setFlash('success', $msg);
                return $tabRedirect ? redirect($tabRedirect) : back();
            }
            return Application::$app->response->json([
                'success' => true,
                'message' => $msg,
                'checked_in' => 1,
                'checked_in_at' => $now,
                'formatted_time' => date('h:i A', strtotime($now))
            ]);
        }

        if ($action === 'uncheck' || $action === 'undo_check_in') {
            $stmt = $db->prepare("UPDATE tickets SET checked_in = 0, checked_in_at = NULL, checked_in_by = NULL WHERE id = :id");
            $stmt->execute(['id' => $id]);

            $msg = 'Check-in status for ticket ' . $id . ' (' . $ticket['attendee_name'] . ') has been undone. Attendee is now awaiting entry.';
            if (!$isJson) {
                Application::$app->session->setFlash('success', $msg);
                return $tabRedirect ? redirect($tabRedirect) : back();
            }
            return Application::$app->response->json([
                'success' => true,
                'message' => $msg,
                'checked_in' => 0,
                'checked_in_at' => null
            ]);
        }

        if ($action === 'resend') {
            $attendeeEmail = trim((string)($ticket['attendee_email'] ?? ''));
            if (empty($attendeeEmail) || !filter_var($attendeeEmail, FILTER_VALIDATE_EMAIL)) {
                $errMsg = 'Attendee does not have a valid email address on file.';
                if (!$isJson) {
                    Application::$app->session->setFlash('error', $errMsg);
                    return $tabRedirect ? redirect($tabRedirect) : back();
                }
                return Application::$app->response->json(['success' => false, 'message' => $errMsg], 400);
            }

            try {
                $db = Application::$app->db->pdo;
                $evStmt = $db->prepare("SELECT name, date, location FROM events WHERE id = :event_id LIMIT 1");
                $evStmt->execute(['event_id' => $ticket['event_id']]);
                $evRow = $evStmt->fetch();

                $evName = $evRow['name'] ?? ($ticket['event_name'] ?? 'Event');
                $evDate = !empty($evRow['date']) ? date('D, M d, Y • g:i A', strtotime($evRow['date'])) : (!empty($ticket['purchase_date']) ? date('D, M d, Y', strtotime($ticket['purchase_date'])) : 'TBD');
                $evLocation = $evRow['location'] ?? ($ticket['event_location'] ?? 'Venue');
                $isIssued = !empty($ticket['is_issued']);
                $payMethod = $isIssued ? 'Host Issued Pass' : (strtoupper($ticket['payment_method'] ?? 'Online Payment'));

                // Find all tickets belonging to this order/attendee for this event
                $ticketsToSend = [];
                if (!empty($ticket['status_detail']) && preg_match('/(?:Ref:\s*)([A-Za-z0-9\-_]+)/i', $ticket['status_detail'], $mRef)) {
                    $ref = trim($mRef[1]);
                    $tStmt = $db->prepare("
                        SELECT t.*, tt.name as ticket_type, t.id as ticket_code
                        FROM tickets t
                        LEFT JOIN ticket_types tt ON t.ticket_type_id = tt.id
                        WHERE t.event_id = :event_id AND (t.status_detail LIKE :ref OR t.id = :t_id)
                        ORDER BY t.id ASC
                    ");
                    $tStmt->execute([
                        'event_id' => $ticket['event_id'],
                        'ref' => '%' . $ref . '%',
                        't_id' => $ticket['id']
                    ]);
                    $found = $tStmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
                    if (!empty($found)) {
                        foreach ($found as $tItem) {
                            $ticketsToSend[] = [
                                'ticket_code' => $tItem['ticket_code'] ?? $tItem['id'],
                                'attendee_name' => $tItem['attendee_name'] ?? $ticket['attendee_name'],
                                'ticket_type' => $tItem['ticket_type'] ?? 'Standard Admission',
                                'price' => (float)($tItem['price'] ?? 0),
                                'status' => $tItem['status'] ?? 'Paid',
                                'checked_in' => !empty($tItem['checked_in'])
                            ];
                        }
                    }
                }

                if (empty($ticketsToSend)) {
                    $ticketsToSend[] = [
                        'ticket_code' => $ticket['id'],
                        'attendee_name' => $ticket['attendee_name'] ?? 'Attendee',
                        'ticket_type' => $ticket['ticket_type'] ?? 'Standard Admission',
                        'price' => (float)($ticket['price'] ?? 0),
                        'status' => $ticket['status'] ?? 'Paid',
                        'checked_in' => !empty($ticket['checked_in'])
                    ];
                }

                // Fetch any add-on merchandise for this buyer/attendee
                $addonStmt = $db->prepare("
                    SELECT oa.*, ea.name as addon_name, ea.description as addon_desc, ea.image as addon_image
                    FROM order_addons oa
                    JOIN event_addons ea ON ea.id = oa.addon_id
                    WHERE oa.event_id = :event_id AND LOWER(oa.buyer_email) = :email
                    ORDER BY oa.id ASC
                ");
                $addonStmt->execute([
                    'event_id' => $ticket['event_id'],
                    'email' => strtolower($attendeeEmail)
                ]);
                $addons = $addonStmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];

                // Calculate total
                $totalAmount = 0.0;
                foreach ($ticketsToSend as $tItem) {
                    $totalAmount += (float)($tItem['price'] ?? 0);
                }
                foreach ($addons as $addonItem) {
                    $totalAmount += (float)($addonItem['total_price'] ?? 0);
                }

                $emailParams = [
                    'attendee_name' => $ticket['attendee_name'] ?? 'Valued Guest',
                    'event_name' => $evName,
                    'event_date' => $evDate,
                    'event_location' => $evLocation,
                    'payment_method' => $payMethod,
                    'total_amount' => $totalAmount,
                    'tickets' => $ticketsToSend,
                    'addons' => $addons,
                    'is_issued' => $isIssued,
                ];

                $emailSubject = 'Your Admission Pass - ' . $evName;
                $queueRef = 'RESEND-TKT-' . $ticket['id'] . '-' . time();
                $queued = false;

                try {
                    \Ace\EmailQueue::queue(
                        $attendeeEmail,
                        $emailSubject,
                        'emails/ticket_confirmation',
                        $emailParams,
                        $queueRef,
                        1 // High priority for manual host resend
                    );
                    \Ace\EmailQueue::processAfterResponse();
                    $queued = true;
                } catch (\Throwable $qErr) {
                    error_log("EmailQueue failed for ticket {$ticket['id']}: " . $qErr->getMessage());
                    // Fallback to direct send if queue fails
                    try {
                        $sent = \Ace\Mail::send(
                            $attendeeEmail,
                            $emailSubject,
                            'emails/ticket_confirmation',
                            $emailParams
                        );
                        $queued = $sent;
                    } catch (\Throwable $mailErr) {
                        error_log("Direct Mail::send also failed for ticket {$ticket['id']}: " . $mailErr->getMessage());
                        $queued = false;
                    }
                }

                if (!$queued) {
                    $errMsg = 'Failed to queue or send ticket email. Please check mail configuration.';
                    if (!$isJson) {
                        Application::$app->session->setFlash('error', $errMsg);
                        return $tabRedirect ? redirect($tabRedirect) : back();
                    }
                    return Application::$app->response->json(['success' => false, 'message' => $errMsg], 500);
                }

                $msg = 'Ticket pass email successfully queued for ' . $attendeeEmail . '!';
                if (!$isJson) {
                    Application::$app->session->setFlash('success', $msg);
                    return $tabRedirect ? redirect($tabRedirect) : back();
                }
                return Application::$app->response->json(['success' => true, 'message' => $msg]);

            } catch (\Exception $e) {
                error_log("Failed to resend ticket email: " . $e->getMessage());
                $errMsg = 'Error dispatching ticket email: ' . $e->getMessage();
                if (!$isJson) {
                    Application::$app->session->setFlash('error', $errMsg);
                    return $tabRedirect ? redirect($tabRedirect) : back();
                }
                return Application::$app->response->json(['success' => false, 'message' => $errMsg], 500);
            }
        }

        if ($action === 'refund') {
            if ($ticket['status'] === 'Cancelled') {
                $msg = 'Ticket is already refunded/cancelled.';
                if (!$isJson) {
                    Application::$app->session->setFlash('error', $msg);
                    return $tabRedirect ? redirect($tabRedirect) : back();
                }
                return Application::$app->response->json(['success' => false, 'message' => $msg], 400);
            }

            if (!empty($ticket['checked_in'])) {
                $msg = 'Ticket has already been checked in and cannot be refunded.';
                if (!$isJson) {
                    Application::$app->session->setFlash('error', $msg);
                    return $tabRedirect ? redirect($tabRedirect) : back();
                }
                return Application::$app->response->json(['success' => false, 'message' => $msg], 400);
            }

            $eventDate = !empty($ticket['event_date']) ? strtotime($ticket['event_date']) : 0;
            if ($eventDate > 0 && $eventDate <= time()) {
                $msg = 'Refunds are disabled because the event has already started.';
                if (!$isJson) {
                    Application::$app->session->setFlash('error', $msg);
                    return $tabRedirect ? redirect($tabRedirect) : back();
                }
                return Application::$app->response->json(['success' => false, 'message' => $msg], 400);
            }

            // Find matching transaction for Paystack refund API
            $trxStmt = $db->prepare("
                SELECT * FROM transactions 
                WHERE event_id = :event_id AND (payer_email = :email OR user_id = :user_id) 
                ORDER BY created_at DESC LIMIT 1
            ");
            $trxStmt->execute([
                'event_id' => $ticket['event_id'],
                'email' => $ticket['attendee_email'],
                'user_id' => $ticket['user_id'] ?? 0
            ]);
            $transaction = $trxStmt->fetch();

            $paystackNote = '';
            if ($transaction && strtolower($transaction['payment_method'] ?? '') === 'paystack') {
                $paystack = new \Ace\PaystackService();
                if ($paystack->isConfigured()) {
                    try {
                        $paystack->refundTransaction(
                            $transaction['reference'],
                            (float)$ticket['price'],
                            "Refund for Ticket #{$id} ({$ticket['attendee_name']})"
                        );
                        $paystackNote = ' Paystack gateway refund request submitted successfully.';
                    } catch (\Exception $paystackErr) {
                        \Ace\Logger::error("Paystack Refund API error: " . $paystackErr->getMessage());
                        $paystackNote = ' (Paystack note: ' . $paystackErr->getMessage() . ')';
                    }
                }
            }

            // Update ticket status to Cancelled and status_detail to Refunded
            $stmt = $db->prepare("UPDATE tickets SET status = 'Cancelled', status_detail = 'Refunded' WHERE id = :id");
            $stmt->execute(['id' => $id]);

            // Update transaction status to refunded
            if ($transaction) {
                $upTrx = $db->prepare("UPDATE transactions SET status = 'refunded' WHERE id = :trx_id");
                $upTrx->execute(['trx_id' => $transaction['id']]);
            }

            $refundRef = !empty($transaction['reference']) ? $transaction['reference'] : ('TKT-' . $ticket['id']);

            // 1. Send Refund Confirmation Email to Attendee
            try {
                \Ace\Mail::send(
                    $ticket['attendee_email'],
                    'Ticket Refund Notification - ' . $ticket['event_name'],
                    'emails/ticket_refund',
                    [
                        'attendee_name' => $ticket['attendee_name'],
                        'event_name' => $ticket['event_name'],
                        'refund_amount' => (float)$ticket['price'],
                        'reference' => $refundRef,
                        'ticket_code' => $ticket['id'],
                        'ticket_type' => $ticket['ticket_type'] ?? 'Standard Admission'
                    ]
                );
            } catch (\Exception $mailEx) {
                error_log("Failed to send refund email: " . $mailEx->getMessage());
            }

            // 2. Send Refund Alert Email to Event Host
            if (!empty($ticket['host_email'])) {
                try {
                    \Ace\Mail::send(
                        $ticket['host_email'],
                        'Ticket Refund Notice - ' . $ticket['event_name'],
                        'emails/host_ticket_refund',
                        [
                            'host_name' => $ticket['host_name'] ?? 'Organizer',
                            'event_name' => $ticket['event_name'],
                            'event_id' => $ticket['event_id'],
                            'attendee_name' => $ticket['attendee_name'],
                            'attendee_email' => $ticket['attendee_email'],
                            'ticket_code' => $ticket['id'],
                            'ticket_type' => $ticket['ticket_type'] ?? 'Standard Admission',
                            'refund_amount' => (float)$ticket['price'],
                            'reference' => $refundRef,
                            'refund_date' => date('Y-m-d H:i:s')
                        ]
                    );
                } catch (\Exception $hostMailEx) {
                    error_log("Failed to send host refund alert email: " . $hostMailEx->getMessage());
                }
            }

            $msg = 'Ticket refunded successfully and email notification sent.' . $paystackNote;
            if (!$isJson) {
                Application::$app->session->setFlash('success', $msg);
                return $tabRedirect ? redirect($tabRedirect) : back();
            }

            return Application::$app->response->json([
                'success' => true, 
                'message' => $msg,
                'new_status' => 'CANCELLED',
                'status_detail' => 'Refunded'
            ]);
        }

        if ($action === 'flag') {
            // Update status_detail to Flagged for review
            $stmt = $db->prepare("UPDATE tickets SET status_detail = 'Flagged for review' WHERE id = :id");
            $stmt->execute(['id' => $id]);

            return Application::$app->response->json([
                'success' => true, 
                'message' => 'Ticket flagged for review successfully.',
                'status_detail' => 'Flagged for review'
            ]);
        }

        return Application::$app->response->json(['success' => false, 'message' => 'Invalid action.'], 400);
    }
}