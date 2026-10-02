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
                // Optional: Handle Paystack refund notification
                if (!empty($reference)) {
                    error_log("Paystack Webhook refund.processed for ref [{$reference}]");
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
