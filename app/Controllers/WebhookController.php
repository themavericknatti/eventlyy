<?php

namespace App\Controllers;

use Ace\Controller;
use Ace\Request;
use Ace\PaystackService;
use App\Services\PaymentService;

class WebhookController extends Controller
{
    private PaymentService $paymentService;
    private PaystackService $paystackService;

    public function __construct()
    {
        $this->paymentService = new PaymentService();
        $this->paystackService = new PaystackService();
    }

    /**
     * Handle Paystack Webhook (Server-to-Server)
     * URL: /webhook/paystack
     */
    public function paystack(Request $request)
    {
        // Only accept POST requests
        if (strtoupper($request->method()) !== 'POST') {
            http_response_code(405);
            header('Content-Type: application/json');
            echo json_encode(['status' => false, 'message' => 'Method Not Allowed']);
            exit;
        }

        // Retrieve raw JSON input and signature
        $input = @file_get_contents('php://input') ?: '';
        $signature = $_SERVER['HTTP_X_PAYSTACK_SIGNATURE'] ?? '';

        // Validate webhook signature
        if (empty($input) || empty($signature) || !$this->paystackService->verifyWebhookSignature($input, $signature)) {
            error_log("Paystack Webhook: Invalid signature or empty payload.");
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(['status' => false, 'message' => 'Invalid Webhook Signature']);
            exit;
        }

        $event = json_decode($input, true);
        if (!$event || !isset($event['event'])) {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(['status' => false, 'message' => 'Invalid JSON event']);
            exit;
        }

        // Process Event
        $eventType = $event['event'];
        $eventData = $event['data'] ?? [];
        $reference = $eventData['reference'] ?? '';

        switch ($eventType) {
            case 'charge.success':
                if (!empty($reference)) {
                    if (str_starts_with($reference, 'DOM-')) {
                        // Custom domain activation fee payment
                        $db = \Ace\Application::$app->db->pdo;
                        $db->prepare("UPDATE event_domain_requests SET payment_status = 'paid' WHERE payment_reference = :ref")->execute(['ref' => $reference]);
                        $db->prepare("UPDATE transactions SET status = 'completed', updated_at = NOW() WHERE reference = :ref")->execute(['ref' => $reference]);
                        error_log("Paystack Webhook: Custom domain fee marked as paid for ref [{$reference}]");
                    } else {
                        $eventData['source'] = 'paystack_webhook';
                        $result = $this->paymentService->finalizeSuccessfulPayment($reference, $eventData);
                        error_log("Paystack Webhook charge.success for ref [{$reference}]: " . ($result['message'] ?? 'Processed'));
                    }
                }
                break;

            case 'refund.processed':
                // Handle Paystack refund notification
                if (!empty($reference)) {
                    error_log("Paystack Webhook refund.processed for ref [{$reference}]");
                    try {
                        $db = \Ace\Application::$app->db->pdo;
                        $origRef = $eventData['transaction_reference'] ?? $reference;
                        $trxStmt = $db->prepare("SELECT * FROM transactions WHERE reference = :ref OR reference = :orig_ref LIMIT 1");
                        $trxStmt->execute([
                            'ref' => $reference,
                            'orig_ref' => $origRef
                        ]);
                        $trx = $trxStmt->fetch();

                        if ($trx && $trx['status'] !== 'refunded') {
                            $db->prepare("UPDATE transactions SET status = 'refunded', updated_at = NOW() WHERE id = :id")->execute(['id' => $trx['id']]);

                            // Find non-cancelled tickets matching this transaction or event and payer
                            $tStmt = $db->prepare("
                                SELECT t.*, tt.name as ticket_type, e.name as event_name, u.name as host_name, u.email as host_email
                                FROM tickets t
                                INNER JOIN events e ON t.event_id = e.id
                                LEFT JOIN ticket_types tt ON t.ticket_type_id = tt.id
                                LEFT JOIN users u ON e.organizer_id = u.id
                                WHERE t.event_id = :event_id 
                                  AND (t.attendee_email = :email OR t.user_id = :user_id)
                                  AND t.status != 'Cancelled'
                            ");
                            $tStmt->execute([
                                'event_id' => $trx['event_id'],
                                'email' => $trx['payer_email'],
                                'user_id' => $trx['user_id'] ?? 0
                            ]);
                            $tickets = $tStmt->fetchAll();

                            foreach ($tickets as $t) {
                                $db->prepare("UPDATE tickets SET status = 'Cancelled', status_detail = 'Refunded' WHERE id = :id")->execute(['id' => $t['id']]);

                                // Send Refund Confirmation Email to Attendee
                                if (!empty($t['attendee_email'])) {
                                    try {
                                        \Ace\Mail::send(
                                            $t['attendee_email'],
                                            'Ticket Refund Notification - ' . $t['event_name'],
                                            'emails/ticket_refund',
                                            [
                                                'attendee_name' => $t['attendee_name'],
                                                'event_name' => $t['event_name'],
                                                'refund_amount' => (float)$t['price'],
                                                'reference' => $trx['reference'],
                                                'ticket_code' => $t['id'],
                                                'ticket_type' => $t['ticket_type'] ?? 'Standard Admission'
                                            ]
                                        );
                                    } catch (\Throwable $mEx) {
                                        error_log("Webhook refund email error (attendee): " . $mEx->getMessage());
                                    }
                                }

                                // Send Refund Alert Email to Host
                                if (!empty($t['host_email'])) {
                                    try {
                                        \Ace\Mail::send(
                                            $t['host_email'],
                                            'Ticket Refund Notice - ' . $t['event_name'],
                                            'emails/host_ticket_refund',
                                            [
                                                'host_name' => $t['host_name'] ?? 'Organizer',
                                                'event_name' => $t['event_name'],
                                                'event_id' => $t['event_id'],
                                                'attendee_name' => $t['attendee_name'],
                                                'attendee_email' => $t['attendee_email'],
                                                'ticket_code' => $t['id'],
                                                'ticket_type' => $t['ticket_type'] ?? 'Standard Admission',
                                                'refund_amount' => (float)$t['price'],
                                                'reference' => $trx['reference'],
                                                'refund_date' => date('Y-m-d H:i:s')
                                            ]
                                        );
                                    } catch (\Throwable $mEx) {
                                        error_log("Webhook refund alert error (host): " . $mEx->getMessage());
                                    }
                                }
                            }
                        }
                    } catch (\Throwable $whEx) {
                        error_log("Error processing Paystack refund.processed webhook: " . $whEx->getMessage());
                    }
                }
                break;

            default:
                // Other events logged and acknowledged
                error_log("Paystack Webhook received event: {$eventType}");
                break;
        }

        // Return HTTP 200 OK immediately to Paystack
        http_response_code(200);
        header('Content-Type: application/json');
        echo json_encode(['status' => true, 'message' => 'Webhook received and processed successfully']);
        exit;
    }

    /**
     * Webhook Status / Health Check
     */
    public function healthCheck(Request $request)
    {
        header('Content-Type: application/json');
        echo json_encode([
            'status' => true,
            'service' => 'Evently Payment Webhook Gateway',
            'paystack_configured' => $this->paystackService->isConfigured(),
            'timestamp' => date('Y-m-d H:i:s')
        ]);
        exit;
    }
}
