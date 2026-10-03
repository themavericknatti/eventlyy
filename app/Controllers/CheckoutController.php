<?php

/**
 * CheckoutController
 *
 * Handles checkout preparation, ticket pricing & fee calculations, payment gateway integration (Paystack),
 * ticket generation, and order confirmation.
 *
 * @package Evently
 * @subpackage Controllers/Checkout
 * @author Your Name <Celio Natti>
 * @license MIT License
 * @version 1.0.0
 */

namespace App\Controllers;

use Ace\Application;
use Ace\Controller;
use Ace\Request;
use Ace\PaystackService;

class CheckoutController extends Controller
{
    /**
     * Display Checkout Page with Event Details and Order Summary.
     */
    public function checkout(Request $request, $event_id = null): string
    {
        $data = $request->getBody();
        $eventId = $event_id ?: ($data['event_id'] ?? null);

        $db = Application::$app->db->pdo;

        // If no event_id, pick first active published event
        if (!$eventId) {
            $eventId = $db->query("SELECT id FROM events WHERE status = 'published' ORDER BY id ASC LIMIT 1")->fetchColumn();
        }

        if (!$eventId) {
            Application::$app->session->setFlash('error', 'No active event available for checkout.');
            return redirect('/') ?: '';
        }

        // Fetch event info
        $stmt = $db->prepare("
            SELECT e.*, u.name as organizer_name, u.email as organizer_email
            FROM events e
            INNER JOIN users u ON e.organizer_id = u.id
            WHERE e.id = :id_num OR e.slug = :slug OR e.custom_url = :custom_url
        ");
        $stmt->execute([
            'id_num' => is_numeric($eventId) ? (int)$eventId : 0,
            'slug' => $eventId,
            'custom_url' => $eventId
        ]);
        $event = $stmt->fetch();

        if (!$event) {
            throw new \Exception("Event not found.", 404);
        }

        $eventDate = strtotime($event['date'] ?? '');
        $liveWindowEnd = $eventDate ? $eventDate + (event_completion_hours() * 3600) : 0;
        $isCompleted = ($eventDate && time() > $liveWindowEnd) || in_array(strtolower($event['status'] ?? ''), ['completed', 'past', 'cancelled']);
        if ($isCompleted) {
            Application::$app->session->setFlash('error', 'Ticket sales are closed for concluded events.');
            redirect(event_url($event));
            return '';
        }

        // Fetch ticket types for event
        $stmt = $db->prepare("
            SELECT tt.*,
                   (SELECT COUNT(*) FROM tickets t WHERE t.ticket_type_id = tt.id AND t.status IN ('Paid', 'Comped')) as sold
            FROM ticket_types tt
            WHERE tt.event_id = :event_id
            ORDER BY tt.price ASC
        ");
        $stmt->execute(['event_id' => $event['id']]);
        $ticketTypes = $stmt->fetchAll() ?: [];

        // Parse ticket quantities from GET or POST
        $qtyMap = $data['qty'] ?? [];
        $selectedItems = [];
        $subtotal = 0.00;

        foreach ($ticketTypes as $index => $tt) {
            $ttId = $tt['id'];
            $qty = isset($qtyMap[$ttId]) ? (int)$qtyMap[$ttId] : 0;

            if ($qty > 0) {
                $itemSubtotal = (float)$tt['price'] * $qty;
                $subtotal += $itemSubtotal;
                $selectedItems[] = [
                    'ticket_type_id' => $tt['id'],
                    'name' => $tt['name'],
                    'price' => (float)$tt['price'],
                    'qty' => $qty,
                    'subtotal' => $itemSubtotal
                ];
            }
        }

        // Fee calculation: dynamic platform fee from admin settings (default 5%)
        $passFee = !empty($event['pass_fee']);
        $platformFeePercent = (float)get_setting('platform_fee', 5);
        $fee = ($passFee && $subtotal > 0) ? ($subtotal * ($platformFeePercent / 100)) : 0.00;
        $extraCharge = $subtotal > 0 ? (float)get_setting('extra_charge', 100.00) : 0.00;
        $totalAmount = $subtotal > 0 ? max(0, $subtotal + $fee + $extraCharge) : 0.00;

        // Current logged-in user defaults if available
        $currentUser = Application::$app->user;

        // Track referral if ?ref= is present in checkout query string
        $refCode = trim($_GET['ref'] ?? ($data['ref'] ?? ''));
        if (!empty($refCode)) {
            $refStmt = $db->prepare("SELECT id FROM event_referrals WHERE event_id = :event_id AND referral_code = :code AND status = 'active' LIMIT 1");
            $refStmt->execute(['event_id' => $event['id'], 'code' => strtolower($refCode)]);
            $referral = $refStmt->fetch();
            if ($referral) {
                \App\Models\EventReferral::recordVisit((int)$referral['id']);
                Application::$app->session->set('event_ref_' . $event['id'], (int)$referral['id']);
            }
        }

        return $this->render('checkout', [
            'title' => 'Checkout - ' . $event['name'],
            'event' => $event,
            'selectedItems' => $selectedItems,
            'subtotal' => $subtotal,
            'fee' => $fee,
            'passFee' => $passFee,
            'platformFeePercent' => $platformFeePercent,
            'extraCharge' => $extraCharge,
            'totalAmount' => $totalAmount,
            'currentUser' => $currentUser,
            'qtyMap' => $qtyMap,
            'refCode' => $refCode
        ]);
    }

    /**
     * Display Dedicated Flyer Checkout Page matching the cinema flyer poster aesthetic.
     */
    public function flyerCheckout(Request $request, $event_id = null): string
    {
        $data = $request->getBody();
        $eventId = $event_id ?: ($data['event_id'] ?? null);

        $db = Application::$app->db->pdo;

        if (!$eventId) {
            $eventId = $db->query("SELECT id FROM events WHERE status = 'published' AND flyer_page_enabled = 1 ORDER BY id ASC LIMIT 1")->fetchColumn();
        }

        if (!$eventId) {
            Application::$app->session->setFlash('error', 'No active event available for checkout.');
            return redirect('/') ?: '';
        }

        // Fetch event info
        $stmt = $db->prepare("
            SELECT e.*, u.name as organizer_name, u.email as organizer_email
            FROM events e
            INNER JOIN users u ON e.organizer_id = u.id
            WHERE e.id = :id_num OR e.slug = :slug OR e.custom_url = :custom_url
        ");
        $stmt->execute([
            'id_num' => is_numeric($eventId) ? (int)$eventId : 0,
            'slug' => $eventId,
            'custom_url' => $eventId
        ]);
        $event = $stmt->fetch();

        if (!$event) {
            throw new \Exception("Event not found.", 404);
        }

        // If flyer page is not enabled for this event, redirect to standard checkout
        if (empty($event['flyer_page_enabled'])) {
            return redirect('/checkout/' . (!empty($event['custom_url']) ? $event['custom_url'] : ($event['slug'] ?? $event['id']))) ?: '';
        }

        $eventDate = strtotime($event['date'] ?? '');
        $liveWindowEnd = $eventDate ? $eventDate + (event_completion_hours() * 3600) : 0;
        $isCompleted = ($eventDate && time() > $liveWindowEnd) || in_array(strtolower($event['status'] ?? ''), ['completed', 'past', 'cancelled']);
        if ($isCompleted) {
            Application::$app->session->setFlash('error', 'Ticket sales are closed for concluded events.');
            redirect(event_flyer_url($event));
            return '';
        }

        // Fetch ticket types
        $stmt = $db->prepare("
            SELECT tt.*,
                   (SELECT COUNT(*) FROM tickets t WHERE t.ticket_type_id = tt.id AND t.status IN ('Paid', 'Comped')) as sold
            FROM ticket_types tt
            WHERE tt.event_id = :event_id
            ORDER BY tt.price ASC
        ");
        $stmt->execute(['event_id' => $event['id']]);
        $ticketTypes = $stmt->fetchAll() ?: [];

        // Parse ticket quantities from GET or POST
        $qtyMap = $data['qty'] ?? ($_GET['qty'] ?? []);
        $selectedItems = [];
        $subtotal = 0.00;

        foreach ($ticketTypes as $index => $tt) {
            $ttId = $tt['id'];
            $qty = isset($qtyMap[$ttId]) ? (int)$qtyMap[$ttId] : 0;

            if ($qty > 0) {
                $itemSubtotal = (float)$tt['price'] * $qty;
                $subtotal += $itemSubtotal;
                $selectedItems[] = [
                    'ticket_type_id' => $tt['id'],
                    'name' => $tt['name'],
                    'price' => (float)$tt['price'],
                    'qty' => $qty,
                    'subtotal' => $itemSubtotal
                ];
            }
        }

        // If no tickets were selected yet, default to 1 ticket of the first tier
        if (empty($selectedItems) && !empty($ticketTypes)) {
            $firstTier = $ticketTypes[0];
            $qtyMap[$firstTier['id']] = 1;
            $subtotal = (float)$firstTier['price'];
            $selectedItems[] = [
                'ticket_type_id' => $firstTier['id'],
                'name' => $firstTier['name'],
                'price' => (float)$firstTier['price'],
                'qty' => 1,
                'subtotal' => $subtotal
            ];
        }

        // Fee calculations
        $passFee = !empty($event['pass_fee']);
        $platformFeePercent = (float)get_setting('platform_fee', 5);
        $fee = ($passFee && $subtotal > 0) ? ($subtotal * ($platformFeePercent / 100)) : 0.00;
        $extraCharge = $subtotal > 0 ? (float)get_setting('extra_charge', 100.00) : 0.00;
        $totalAmount = $subtotal > 0 ? max(0, $subtotal + $fee + $extraCharge) : 0.00;

        $currentUser = Application::$app->user;

        // Track referral
        $refCode = trim($_GET['ref'] ?? ($data['ref'] ?? ''));
        if (!empty($refCode)) {
            $refStmt = $db->prepare("SELECT id FROM event_referrals WHERE event_id = :event_id AND referral_code = :code AND status = 'active' LIMIT 1");
            $refStmt->execute(['event_id' => $event['id'], 'code' => strtolower($refCode)]);
            $referral = $refStmt->fetch();
            if ($referral) {
                \App\Models\EventReferral::recordVisit((int)$referral['id']);
                Application::$app->session->set('event_ref_' . $event['id'], (int)$referral['id']);
            }
        }

        return $this->render('flyer-checkout', [
            'title' => 'Checkout — ' . $event['name'],
            'event' => (object)$event,
            'selectedItems' => $selectedItems,
            'ticketTypes' => $ticketTypes,
            'subtotal' => $subtotal,
            'fee' => $fee,
            'passFee' => $passFee,
            'platformFeePercent' => $platformFeePercent,
            'extraCharge' => $extraCharge,
            'totalAmount' => $totalAmount,
            'currentUser' => $currentUser,
            'qtyMap' => $qtyMap,
            'refCode' => $refCode,
            'flyerUrl' => event_flyer_url($event)
        ]);
    }

    /**
     * Process Checkout Form Submission.
     * Routes to Paystack initialization or direct ticket issuance depending on payment method.
     */
    public function processCheckout(Request $request, $event_id = null)
    {
        $data = $request->getBody();
        $eventId = $event_id ?: ($data['event_id'] ?? null);
        $name = trim($data['name'] ?? '');
        $email = trim($data['email'] ?? '');
        $paymentMethod = $data['payment_method'] ?? 'paystack';

        if (empty($name) || empty($email)) {
            Application::$app->session->setFlash('error', 'Please provide your full name and email address.');
            redirect('/checkout/' . $eventId);
            return;
        }

        $db = Application::$app->db->pdo;

        // Verify event
        $stmt = $db->prepare("SELECT * FROM events WHERE id = :id_num OR slug = :slug OR custom_url = :custom_url LIMIT 1");
        $stmt->execute([
            'id_num' => is_numeric($eventId) ? (int)$eventId : 0,
            'slug' => $eventId,
            'custom_url' => $eventId
        ]);
        $event = $stmt->fetch();

        if (!$event) {
            Application::$app->session->setFlash('error', 'Invalid event selection.');
            redirect('/');
            return;
        }

        $eventDate = strtotime($event['date'] ?? '');
        $liveWindowEnd = $eventDate ? $eventDate + (event_completion_hours() * 3600) : 0;
        $isCompleted = ($eventDate && time() > $liveWindowEnd) || in_array(strtolower($event['status'] ?? ''), ['completed', 'past', 'cancelled']);
        if ($isCompleted) {
            Application::$app->session->setFlash('error', 'Ticket sales are closed for concluded events.');
            redirect(event_url($event));
            return;
        }

        // Fetch ticket types
        $stmt = $db->prepare("SELECT * FROM ticket_types WHERE event_id = :event_id");
        $stmt->execute(['event_id' => $event['id']]);
        $ticketTypes = $stmt->fetchAll() ?: [];

        // Calculate subtotal amount
        $subtotal = 0.00;
        $hasTickets = false;
        $qtyMap = $data['qty'] ?? $data['tickets'] ?? [];

        foreach ($ticketTypes as $tt) {
            $qty = isset($qtyMap[$tt['id']]) ? (int)$qtyMap[$tt['id']] : 0;
            if ($qty > 0) {
                $hasTickets = true;
                $subtotal += (float)$tt['price'] * $qty;
            }
        }

        if (!$hasTickets) {
            Application::$app->session->setFlash('error', 'Please select at least 1 ticket to complete purchase.');
            redirect(event_url($event));
            return;
        }

        // Check and apply promo code
        $promoCode = strtoupper(trim($data['promo_code'] ?? ''));
        $discountAmount = 0.00;

        if (!empty($promoCode)) {
            $promoStmt = $db->prepare("
                SELECT p.* 
                FROM promo_codes p
                INNER JOIN events e ON e.id = :event_id
                WHERE p.code = :code AND p.status = 'active'
                AND (p.event_id = e.id OR (p.event_id IS NULL AND p.host_id = e.organizer_id))
                AND (p.expires_at IS NULL OR p.expires_at > NOW())
                AND p.used_count < p.max_uses
                LIMIT 1
            ");
            $promoStmt->execute(['code' => $promoCode, 'event_id' => $event['id']]);
            $promo = $promoStmt->fetch();

            if ($promo) {
                if ($promo['discount_type'] === 'percentage') {
                    $discountAmount = $subtotal * ((float)$promo['discount_value'] / 100);
                } else {
                    $discountAmount = min($subtotal, (float)$promo['discount_value']);
                }
            } else {
                $promoCode = null; // Invalid or expired promo code reset
            }
        }

        $platformFeePercent = (float)get_setting('platform_fee', 5);
        $fee = (!empty($event['pass_fee']) && $subtotal > 0) ? ($subtotal * ($platformFeePercent / 100)) : 0.00;
        $netSubtotal = max(0, $subtotal - $discountAmount);
        $extraCharge = $netSubtotal > 0 ? (float)get_setting('extra_charge', 100.00) : 0.00;
        $totalPaid = $netSubtotal > 0 ? max(0, $netSubtotal + $fee + $extraCharge) : 0.00;

        // Track referral if passed in checkout submission
        $refCode = trim($data['ref'] ?? ($_POST['ref'] ?? ($_GET['ref'] ?? '')));
        if (!empty($refCode)) {
            $refStmt = $db->prepare("SELECT id FROM event_referrals WHERE event_id = :event_id AND referral_code = :code AND status = 'active' LIMIT 1");
            $refStmt->execute(['event_id' => $event['id'], 'code' => strtolower($refCode)]);
            $referral = $refStmt->fetch();
            if ($referral) {
                Application::$app->session->set('event_ref_' . $event['id'], (int)$referral['id']);
            }
        }

        // ── Direct Free Ticket Issuance (0 or 0.00 total) ───────
        // Free tickets bypass Paystack and all external payment gateways entirely!
        if ($totalPaid <= 0.00) {
            return $this->issueTicketsDirectly($event, $name, $email, 'free', $qtyMap, $ticketTypes, $promoCode, $discountAmount, $refCode);
        }

        // ── Paystack Payment Gateway ────────────────────────────
        if ($paymentMethod === 'paystack') {
            return $this->initializePaystack($event, $name, $email, $totalPaid, $qtyMap, $promoCode, $discountAmount, $refCode);
        }

        // ── Direct / Fallback (Stripe/Flutterwave placeholder) ──
        return $this->issueTicketsDirectly($event, $name, $email, $paymentMethod, $qtyMap, $ticketTypes, $promoCode, $discountAmount, $refCode);
    }

    /**
     * AJAX endpoint to validate a promo code during checkout
     */
    public function validatePromo(Request $request): string
    {
        $data = $request->getBody();
        $code = strtoupper(trim($data['code'] ?? ''));
        $eventId = (int)($data['event_id'] ?? 0);
        $subtotal = (float)($data['subtotal'] ?? 0);

        header('Content-Type: application/json');

        if (empty($code)) {
            return json_encode(['success' => false, 'message' => 'Please enter a coupon code.']);
        }

        $db = Application::$app->db->pdo;
        $stmt = $db->prepare("
            SELECT p.* 
            FROM promo_codes p
            INNER JOIN events e ON e.id = :event_id
            WHERE p.code = :code AND p.status = 'active'
            AND (p.event_id = e.id OR (p.event_id IS NULL AND p.host_id = e.organizer_id))
            AND (p.expires_at IS NULL OR p.expires_at > NOW())
            AND p.used_count < p.max_uses
            LIMIT 1
        ");
        $stmt->execute(['code' => $code, 'event_id' => $eventId]);
        $promo = $stmt->fetch();

        if (!$promo) {
            return json_encode(['success' => false, 'message' => 'Invalid or expired coupon code.']);
        }

        $discount = 0.00;
        if ($promo['discount_type'] === 'percentage') {
            $discount = $subtotal * ((float)$promo['discount_value'] / 100);
        } else {
            $discount = min($subtotal, (float)$promo['discount_value']);
        }

        // Fetch event to calculate pass fee if applicable
        $eventStmt = $db->prepare("SELECT pass_fee FROM events WHERE id = :id");
        $eventStmt->execute(['id' => $eventId]);
        $eventData = $eventStmt->fetch();
        $platformFeePercent = (float)get_setting('platform_fee', 5);
        $netSubtotal = max(0, $subtotal - $discount);
        $fee = (!empty($eventData['pass_fee']) && $netSubtotal > 0) ? ($netSubtotal * ($platformFeePercent / 100)) : 0.00;
        $extraCharge = $netSubtotal > 0 ? (float)get_setting('extra_charge', 100.00) : 0.00;

        $newTotal = $netSubtotal > 0 ? max(0, $netSubtotal + $fee + $extraCharge) : 0.00;

        return json_encode([
            'success' => true,
            'code' => $promo['code'],
            'discount' => round($discount, 2),
            'formatted_discount' => format_price($discount),
            'new_total' => round($newTotal, 2),
            'formatted_new_total' => format_price($newTotal),
            'is_free' => ($newTotal <= 0.00),
            'message' => 'Coupon code applied successfully!'
        ]);
    }

    /**
     * Initialize Paystack Transaction and redirect user to Paystack hosted checkout.
     */
    private function initializePaystack(array $event, string $name, string $email, float $totalAmount, array $qtyMap, ?string $promoCode = null, float $discountAmount = 0.00, ?string $refCode = null)
    {
        try {
            $paystack = new PaystackService();

            // Generate a unique order reference
            $reference = 'EVT-' . $event['id'] . '-' . strtoupper(bin2hex(random_bytes(6)));

            // Build callback URL
            $appUrl = rtrim($_ENV['APP_URL'] ?? 'http://localhost', '/');
            $callbackUrl = $appUrl . '/checkout/paystack/callback';

            // Save pending transaction record in transactions table
            $db = Application::$app->db->pdo;
            $currentUser = Application::$app->user;
            $userId = $currentUser ? $currentUser->id : null;
            $referralId = Application::$app->session->get('event_ref_' . $event['id']) ?: null;
            if (!$referralId && !empty($refCode)) {
                $refStmt = $db->prepare("SELECT id FROM event_referrals WHERE event_id = :event_id AND referral_code = :code AND status = 'active' LIMIT 1");
                $refStmt->execute(['event_id' => $event['id'], 'code' => strtolower($refCode)]);
                $refRow = $refStmt->fetch();
                if ($refRow) {
                    $referralId = (int)$refRow['id'];
                }
            }

            $subtotal = 0.00;
            $stmt = $db->prepare("SELECT * FROM ticket_types WHERE event_id = :event_id");
            $stmt->execute(['event_id' => $event['id']]);
            $ticketTypes = $stmt->fetchAll() ?: [];

            $priceMap = [];
            foreach ($ticketTypes as $tt) {
                $qty = isset($qtyMap[$tt['id']]) ? (int)$qtyMap[$tt['id']] : 0;
                if ($qty > 0) {
                    $subtotal += (float)$tt['price'] * $qty;
                    $priceMap[$tt['id']] = (float)$tt['price'];
                }
            }

            // Prepare complete order payload for persistence in DB, Session, and Gateway Metadata
            $orderPayload = [
                'event_id' => (int)$event['id'],
                'event_name' => $event['name'],
                'event_date' => $event['date'],
                'event_location' => $event['location'] ?? '',
                'attendee_name' => $name,
                'attendee_email' => $email,
                'total_amount' => $totalAmount,
                'subtotal' => $subtotal,
                'qty_map' => $qtyMap,
                'price_map' => $priceMap,
                'reference' => $reference,
                'pass_fee' => !empty($event['pass_fee']),
                'promo_code' => $promoCode,
                'discount_amount' => $discountAmount,
                'referral_id' => $referralId,
                'user_id' => $userId
            ];

            Application::$app->session->set('pending_order', $orderPayload);
            
            $platformFeePercent = (float)site_setting('platform_fee', 5);
            $fee = !empty($event['pass_fee']) ? ($subtotal * ($platformFeePercent / 100)) : 0.00;
            $extraCharge = (float)site_setting('extra_charge', 100.00);

            $storedPayloadJson = json_encode(['order_data' => $orderPayload, 'initialized_at' => date('Y-m-d H:i:s')]);

            $trxStmt = $db->prepare("
                INSERT INTO transactions (reference, event_id, user_id, payer_name, payer_email, type, payment_method, subtotal_amount, fee_amount, extra_charge_amount, referral_id, promo_code, discount_amount, total_amount, status, gateway_response, created_at, updated_at)
                VALUES (:reference, :event_id, :user_id, :payer_name, :payer_email, 'payment', 'paystack', :subtotal_amount, :fee_amount, :extra_charge_amount, :referral_id, :promo_code, :discount_amount, :total_amount, 'pending', :gateway_response, NOW(), NOW())
                ON DUPLICATE KEY UPDATE 
                    event_id = VALUES(event_id),
                    user_id = VALUES(user_id),
                    payer_name = VALUES(payer_name),
                    payer_email = VALUES(payer_email),
                    subtotal_amount = VALUES(subtotal_amount),
                    fee_amount = VALUES(fee_amount),
                    extra_charge_amount = VALUES(extra_charge_amount),
                    referral_id = VALUES(referral_id),
                    promo_code = VALUES(promo_code),
                    discount_amount = VALUES(discount_amount),
                    total_amount = VALUES(total_amount),
                    status = 'pending',
                    gateway_response = VALUES(gateway_response),
                    updated_at = NOW()
            ");
            $trxStmt->execute([
                'reference' => $reference,
                'event_id' => $event['id'],
                'user_id' => $userId,
                'payer_name' => $name,
                'payer_email' => $email,
                'subtotal_amount' => $subtotal,
                'fee_amount' => $fee,
                'extra_charge_amount' => $extraCharge,
                'referral_id' => $referralId,
                'promo_code' => $promoCode,
                'discount_amount' => $discountAmount,
                'total_amount' => $totalAmount,
                'gateway_response' => $storedPayloadJson
            ]);

            // Initialize Paystack transaction with embedded metadata
            $paystackData = $paystack->initializeTransaction($email, $totalAmount, $callbackUrl, $reference, $orderPayload);

            // Redirect to Paystack payment page
            redirect($paystackData['authorization_url']);
        } catch (\Exception $e) {
            Application::$app->session->setFlash('error', 'Payment initialization failed: ' . $e->getMessage());
            return redirect('/checkout/' . $event['id']);
        }
    }

    /**
     * Paystack Callback — Verify transaction and issue tickets.
     */
    public function paystackCallback(Request $request)
    {
        $data = $request->getBody();
        $reference = trim((string)($data['reference'] ?? ($data['trxref'] ?? '')));

        if (empty($reference)) {
            Application::$app->session->setFlash('error', 'Missing transaction reference from payment provider.');
            return redirect('/');
        }

        $paymentService = new \App\Services\PaymentService();
        $result = $paymentService->finalizeSuccessfulPayment($reference);

        // Check if transaction was initiated via API from an external host domain
        $externalCallback = null;
        if (!empty($result['transaction']['gateway_response'])) {
            $parsedTrx = json_decode($result['transaction']['gateway_response'], true);
            $externalCallback = $parsedTrx['order_data']['external_callback_url'] ?? ($parsedTrx['external_callback_url'] ?? null);
        }
        if (!$externalCallback && Application::$app->session) {
            $pending = Application::$app->session->get('pending_order');
            if (!empty($pending['external_callback_url'])) {
                $externalCallback = $pending['external_callback_url'];
            }
        }
        if (!$externalCallback) {
            $db = Application::$app->db->pdo;
            $txStmt = $db->prepare("SELECT gateway_response FROM transactions WHERE reference = :ref LIMIT 1");
            $txStmt->execute(['ref' => $reference]);
            $rawGw = $txStmt->fetchColumn();
            if ($rawGw) {
                $parsedGw = json_decode($rawGw, true);
                $externalCallback = $parsedGw['order_data']['external_callback_url'] ?? ($parsedGw['external_callback_url'] ?? null);
            }
        }

        if ($result['success']) {
            $event = $result['event'] ?? [];
            $eventId = $event['id'] ?? 0;
            $tx = $result['transaction'] ?? [];

            // Resolve attendee name and email with multi-layer fallback
            $attendeeName = !empty($result['attendee_name']) && $result['attendee_name'] !== 'Attendee' 
                ? $result['attendee_name'] 
                : (!empty($tx['payer_name']) && $tx['payer_name'] !== 'Attendee' ? $tx['payer_name'] : '');
            $attendeeEmail = !empty($result['attendee_email']) ? $result['attendee_email'] : ($tx['payer_email'] ?? '');
            $totalAmount = (float)(!empty($result['total_amount']) ? $result['total_amount'] : ($tx['total_amount'] ?? 0));

            // Session pending_order fallback
            $pending = Application::$app->session ? Application::$app->session->get('pending_order') : null;
            if (empty($attendeeName) && !empty($pending['attendee_name'])) {
                $attendeeName = $pending['attendee_name'];
            }
            if (empty($attendeeEmail) && !empty($pending['attendee_email'])) {
                $attendeeEmail = $pending['attendee_email'];
            }

            // Ticket rows fallback
            if (empty($attendeeName) && !empty($result['tickets'][0]['attendee_name'])) {
                $attendeeName = $result['tickets'][0]['attendee_name'];
            }
            if (empty($attendeeEmail) && !empty($result['tickets'][0]['attendee_email'])) {
                $attendeeEmail = $result['tickets'][0]['attendee_email'];
            }

            if (empty($attendeeName)) {
                $attendeeName = 'Attendee';
            }

            Application::$app->session->set('order_success', [
                'event_id' => $eventId,
                'event_name' => $event['name'] ?? 'Event',
                'event_date' => $event['date'] ?? '',
                'event_location' => $event['location'] ?? '',
                'attendee_name' => $attendeeName,
                'attendee_email' => $attendeeEmail,
                'payment_method' => 'PAYSTACK',
                'payment_reference' => $reference,
                'total_amount' => $totalAmount,
                'tickets' => $result['tickets'] ?? []
            ]);

            // Redirect to external host domain if callback URL was specified
            if (!empty($externalCallback)) {
                $sep = str_contains($externalCallback, '?') ? '&' : '?';
                return redirect($externalCallback . $sep . 'reference=' . urlencode($reference) . '&status=success');
            }

            return redirect('/success?ref=' . urlencode($reference));
        }

        // Redirect to external host domain on failure if callback was specified
        if (!empty($externalCallback)) {
            $sep = str_contains($externalCallback, '?') ? '&' : '?';
            return redirect($externalCallback . $sep . 'reference=' . urlencode($reference) . '&status=error&message=' . urlencode($result['message'] ?? 'Payment verification failed.'));
        }

        Application::$app->session->setFlash('error', $result['message'] ?? 'Payment verification failed.');
        return redirect('/');
    }

    /**
     * Public Self-Service Ticket Recovery View (Disconnection Protection)
     */
    public function verifyOrderView(Request $request, $reference = null)
    {
        $ref = $reference ?: ($request->getBody()['reference'] ?? '');
        return $this->render('checkout/verify-order', [
            'reference' => $ref
        ]);
    }

    /**
     * Public Self-Service Ticket Recovery Action
     */
    public function verifyOrderPost(Request $request)
    {
        $data = $request->getBody();
        $reference = trim((string)($data['reference'] ?? ''));

        if (empty($reference)) {
            Application::$app->session->setFlash('error', 'Please provide a valid payment reference.');
            return redirect('/order/verify');
        }

        $paymentService = new \App\Services\PaymentService();
        $result = $paymentService->reverifyTransaction($reference);

        if ($result['success']) {
            Application::$app->session->setFlash('success', 'Your payment was successfully verified! Your tickets have been activated and emailed to you.');
            return redirect('/order/verify/' . urlencode($reference));
        }

        Application::$app->session->setFlash('error', $result['message'] ?? 'Payment verification was not successful on Paystack.');
        return redirect('/order/verify/' . urlencode($reference));
    }

    /**
     * Automated Cron: Reconcile any pending transactions
     */
    public function reconcilePaymentsCron(Request $request)
    {
        $paymentService = new \App\Services\PaymentService();
        $result = $paymentService->reconcilePendingTransactions(24);

        header('Content-Type: application/json');
        echo json_encode([
            'status' => true,
            'summary' => $result,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
        exit;
    }

    /**
     * Direct ticket issuance for non-gateway payments (Stripe placeholder, etc.)
     */
    private function issueTicketsDirectly(array $event, string $name, string $email, string $paymentMethod, array $qtyMap, array $ticketTypes, ?string $promoCode = null, float $discountAmount = 0.00, ?string $refCode = null)
    {
        $db = Application::$app->db->pdo;
        $eventId = $event['id'];
        $currentUser = Application::$app->user;
        $userId = $currentUser ? $currentUser->id : null;

        $createdTickets = [];
        $subtotal = 0.00;

        $db->beginTransaction();
        try {
            foreach ($ticketTypes as $tt) {
                $ttId = $tt['id'];
                $qty = isset($qtyMap[$ttId]) ? (int)$qtyMap[$ttId] : 0;

                if ($qty > 0) {
                    $itemSubtotal = (float)$tt['price'] * $qty;
                    $subtotal += $itemSubtotal;

                    for ($i = 0; $i < $qty; $i++) {
                        $ticketCode = 'EV-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));

                        $insertStmt = $db->prepare("
                            INSERT INTO tickets (id, event_id, ticket_type_id, user_id, attendee_name, attendee_email, price, status, purchase_date)
                            VALUES (:id, :event_id, :ticket_type_id, :user_id, :name, :email, :price, 'Paid', NOW())
                        ");
                        $insertStmt->execute([
                            'id' => $ticketCode,
                            'event_id' => $eventId,
                            'ticket_type_id' => $ttId,
                            'user_id' => $userId,
                            'name' => $name,
                            'email' => $email,
                            'price' => $tt['price']
                        ]);

                        $createdTickets[] = [
                            'ticket_code' => $ticketCode,
                            'ticket_type' => $tt['name'],
                            'price' => (float)$tt['price']
                        ];
                    }
                }
            }

            if (empty($createdTickets)) {
                $db->rollBack();
                Application::$app->session->setFlash('error', 'Please select at least 1 ticket to complete purchase.');
                redirect(event_url($event));
                return;
            }

            $platformFeePercent = (float)get_setting('platform_fee', 5);
            $fee = (!empty($event['pass_fee']) && $subtotal > 0) ? ($subtotal * ($platformFeePercent / 100)) : 0.00;
            $netSubtotal = max(0, $subtotal - $discountAmount);
            $extraCharge = $netSubtotal > 0 ? (float)get_setting('extra_charge', 100.00) : 0.00;
            $totalPaid = $netSubtotal > 0 ? max(0, $netSubtotal + $fee + $extraCharge) : 0.00;
            $isFree = ($totalPaid <= 0.00);
            $paymentMethodStr = $isFree ? 'FREE' : strtoupper($paymentMethod);

            $referralId = Application::$app->session->get('event_ref_' . $eventId) ?: null;
            if (!$referralId && !empty($refCode)) {
                $refStmt = $db->prepare("SELECT id FROM event_referrals WHERE event_id = :event_id AND referral_code = :code AND status = 'active' LIMIT 1");
                $refStmt->execute(['event_id' => $eventId, 'code' => strtolower($refCode)]);
                $refRow = $refStmt->fetch();
                if ($refRow) {
                    $referralId = (int)$refRow['id'];
                }
            }

            $reference = 'EVT-' . $eventId . '-' . strtoupper(bin2hex(random_bytes(6)));

            // Record completed transaction
            $trxStmt = $db->prepare("
                INSERT INTO transactions (reference, event_id, user_id, payer_name, payer_email, type, payment_method, subtotal_amount, fee_amount, extra_charge_amount, referral_id, promo_code, discount_amount, total_amount, status)
                VALUES (:reference, :event_id, :user_id, :payer_name, :payer_email, 'payment', :payment_method, :subtotal_amount, :fee_amount, :extra_charge_amount, :referral_id, :promo_code, :discount_amount, :total_amount, 'completed')
            ");
            $trxStmt->execute([
                'reference' => $reference,
                'event_id' => $eventId,
                'user_id' => $userId,
                'payer_name' => $name,
                'payer_email' => $email,
                'payment_method' => strtolower($paymentMethodStr),
                'subtotal_amount' => $subtotal,
                'fee_amount' => $fee,
                'extra_charge_amount' => $extraCharge,
                'referral_id' => $referralId,
                'promo_code' => $promoCode,
                'discount_amount' => $discountAmount,
                'total_amount' => $totalPaid
            ]);

            // Increment used_count for promo code if applied
            if (!empty($promoCode)) {
                $promoIncStmt = $db->prepare("UPDATE promo_codes SET used_count = used_count + 1 WHERE code = :code");
                $promoIncStmt->execute(['code' => $promoCode]);
            }

            // Attribute ticket sales to referral if present
            if (!empty($referralId)) {
                \App\Models\EventReferral::recordSale((int)$referralId, count($createdTickets), (float)$subtotal);
                Application::$app->session->remove('event_ref_' . $eventId);
            }

            $db->commit();

            // Send confirmation email
            try {
                \Ace\Mail::send(
                    $email,
                    'Your Ticket Confirmation - ' . $event['name'],
                    'emails/ticket_confirmation',
                    [
                        'attendee_name' => $name,
                        'event_name' => $event['name'],
                        'event_date' => date('D, M d, Y • g:i A', strtotime($event['date'])),
                        'event_location' => $event['location'],
                        'payment_method' => $paymentMethodStr,
                        'total_amount' => $totalPaid,
                        'tickets' => $createdTickets
                    ]
                );
            } catch (\Exception $mailEx) {
                error_log("Failed to send ticket confirmation email: " . $mailEx->getMessage());
            }

            Application::$app->session->set('order_success', [
                'event_id' => $event['id'],
                'event_name' => $event['name'],
                'event_date' => $event['date'],
                'event_location' => $event['location'],
                'attendee_name' => $name,
                'attendee_email' => $email,
                'payment_method' => $paymentMethodStr,
                'total_amount' => $totalPaid,
                'tickets' => $createdTickets
            ]);

            return redirect('/success');
        } catch (\Exception $e) {
            $db->rollBack();
            Application::$app->session->setFlash('error', 'Checkout processing failed: ' . $e->getMessage());
            return redirect('/checkout/' . $eventId);
        }
    }

    /**
     * Render Order Success Confirmation Page.
     */
    public function success(Request $request): string
    {
        $order = Application::$app->session->get('order_success');
        $ref = trim((string)($_GET['ref'] ?? ($order['payment_reference'] ?? '')));

        $db = Application::$app->db->pdo;

        // If order in session is missing or has placeholder 'Attendee', restore from database
        if (!empty($ref) && (!$order || empty($order['attendee_name']) || $order['attendee_name'] === 'Attendee')) {
            $txStmt = $db->prepare("SELECT * FROM transactions WHERE reference = :ref LIMIT 1");
            $txStmt->execute(['ref' => $ref]);
            $tx = $txStmt->fetch(\PDO::FETCH_ASSOC);

            if ($tx) {
                // Fetch event
                $eventStmt = $db->prepare("SELECT * FROM events WHERE id = :event_id LIMIT 1");
                $eventStmt->execute(['event_id' => $tx['event_id']]);
                $event = $eventStmt->fetch(\PDO::FETCH_ASSOC) ?: [];

                // Fetch tickets with ticket_type name
                $tStmt = $db->prepare("
                    SELECT t.*, tt.name as ticket_type, t.id as ticket_code 
                    FROM tickets t 
                    LEFT JOIN ticket_types tt ON t.ticket_type_id = tt.id 
                    WHERE t.status_detail LIKE :ref1 OR t.status_detail LIKE :ref2 
                    ORDER BY t.id ASC
                ");
                $tStmt->execute([
                    'ref1' => '%' . $ref . '%',
                    'ref2' => $ref
                ]);
                $tickets = $tStmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];

                $payerName = !empty($tx['payer_name']) && $tx['payer_name'] !== 'Attendee' 
                    ? $tx['payer_name'] 
                    : (!empty($tickets[0]['attendee_name']) && $tickets[0]['attendee_name'] !== 'Attendee' ? $tickets[0]['attendee_name'] : 'Attendee');
                $payerEmail = !empty($tx['payer_email']) ? $tx['payer_email'] : (!empty($tickets[0]['attendee_email']) ? $tickets[0]['attendee_email'] : '');

                $order = [
                    'event_id' => $tx['event_id'],
                    'event_name' => $event['name'] ?? 'Event',
                    'event_date' => $event['date'] ?? '',
                    'event_location' => $event['location'] ?? '',
                    'attendee_name' => $payerName,
                    'attendee_email' => $payerEmail,
                    'payment_method' => strtoupper($tx['payment_method'] ?? 'PAYSTACK'),
                    'payment_reference' => $ref,
                    'total_amount' => (float)($tx['total_amount'] ?? 0),
                    'tickets' => !empty($tickets) ? $tickets : ($order['tickets'] ?? [])
                ];

                Application::$app->session->set('order_success', $order);
            }
        }

        if (!$order) {
            redirect('/');
            return '';
        }

        return $this->render('success', [
            'title' => 'Purchase Success - ' . ($order['event_name'] ?? 'Event'),
            'order' => $order
        ]);
    }
}