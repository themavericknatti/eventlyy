<?php

namespace App\Controllers\Api;

use Ace\Application;
use Ace\Controller;
use Ace\Request;
use Ace\PaystackService;
use App\Services\PaymentService;

/**
 * EventApiController
 *
 * RESTful Headless Ticketing & Checkout API.
 * Enables external hosts and third-party websites on custom domains to:
 * 1. Fetch real-time event details, ticket tiers, and availability.
 * 2. Validate discount/coupon codes with real-time fee calculation.
 * 3. Initiate ticket checkouts (both Free orders and Paystack-hosted payments).
 * 4. Verify transaction status and retrieve issued attendee tickets.
 *
 * Includes full Cross-Origin Resource Sharing (CORS) support.
 */
class EventApiController extends Controller
{
    /**
     * Send standard JSON response with CORS headers.
     */
    protected function jsonResponse(array $data, int $statusCode = 200): void
    {
        if (!headers_sent()) {
            header('Content-Type: application/json; charset=utf-8');
            header('Access-Control-Allow-Origin: *');
            header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
            header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-Evently-Key');
        }
        http_response_code($statusCode);
        echo json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
        exit;
    }

    /**
     * GET /api/v1/events/{id}
     *
     * Retrieve complete event details, available ticket tiers, pricing policies, and integration links.
     * Identifier can be event ID, slug, or custom_url.
     */
    public function getEvent(Request $request, $id = null)
    {
        $id = $id ?: ($request->getBody()['id'] ?? null);

        if (!$id) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Event identifier (ID, slug, or custom URL) is required.'
            ], 400);
        }

        $db = Application::$app->db->pdo;

        // Resolve event
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
            WHERE e.id = :id_num OR e.slug = :slug OR e.custom_url = :custom_url
            LIMIT 1
        ");
        $stmt->execute([
            'id_num' => is_numeric($id) ? (int)$id : 0,
            'slug' => (string)$id,
            'custom_url' => (string)$id
        ]);
        $event = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$event) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Event not found.'
            ], 404);
        }

        // Gatekeeper: Host must have an approved, active Custom Domain / API integration
        if (!$this->isDomainAccessActive((int)$event['id'])) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Custom Domain & API access has not been activated for this event. The event host must request and activate access from their host dashboard.'
            ], 403);
        }

        // Check if event is concluded or cancelled
        $eventDate = strtotime($event['date'] ?? '');
        $liveWindowEnd = $eventDate ? $eventDate + (event_completion_hours() * 3600) : 0;
        $isCompleted = ($eventDate && time() > $liveWindowEnd) || in_array(strtolower($event['status'] ?? ''), ['completed', 'past', 'cancelled']);

        // Fetch ticket tiers
        $ttStmt = $db->prepare("
            SELECT tt.id, tt.name, tt.price, tt.capacity,
                   (SELECT COUNT(*) FROM tickets t WHERE t.ticket_type_id = tt.id AND t.status IN ('Paid', 'Comped')) as sold
            FROM ticket_types tt
            WHERE tt.event_id = :event_id
            ORDER BY tt.price ASC
        ");
        $ttStmt->execute(['event_id' => $event['id']]);
        $rawTiers = $ttStmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];

        $ticketTiers = [];
        $totalCapacity = 0;
        $totalSold = 0;

        foreach ($rawTiers as $tier) {
            $cap = (int)$tier['capacity'];
            $sold = (int)$tier['sold'];
            $remaining = max(0, $cap - $sold);
            $isSoldOut = ($cap > 0 && $remaining <= 0);

            $totalCapacity += $cap;
            $totalSold += $sold;

            $ticketTiers[] = [
                'id' => (int)$tier['id'],
                'name' => $tier['name'],
                'price' => (float)$tier['price'],
                'formatted_price' => format_price((float)$tier['price']),
                'capacity' => $cap,
                'sold' => $sold,
                'remaining' => $remaining,
                'is_sold_out' => $isSoldOut,
                'is_available' => (!$isSoldOut && !$isCompleted && $event['status'] === 'published')
            ];
        }

        // Build absolute links
        $baseUrl = rtrim($_ENV['APP_URL'] ?? ('http://' . ($_SERVER['HTTP_HOST'] ?? 'localhost')), '/');
        $eventSlug = !empty($event['custom_url']) ? $event['custom_url'] : ($event['slug'] ?: $event['id']);

        $imagePath = $event['image'] ?? '';
        $imageUrl = '';
        if (!empty($imagePath)) {
            $imageUrl = str_starts_with($imagePath, 'http') ? $imagePath : $baseUrl . '/' . ltrim($imagePath, '/');
        }

        $platformFeePercent = (float)get_setting('platform_fee', 5);
        $extraCharge = (float)get_setting('extra_charge', 100.00);

        $this->jsonResponse([
            'success' => true,
            'event' => [
                'id' => (int)$event['id'],
                'name' => $event['name'],
                'slug' => $event['slug'],
                'custom_url' => $event['custom_url'],
                'category' => $event['category'] ?? 'General',
                'description' => $event['description'],
                'date' => $event['date'],
                'formatted_date' => $eventDate ? date('l, M d, Y • g:i A', $eventDate) : null,
                'location' => $event['location'],
                'image_url' => $imageUrl,
                'status' => $event['status'],
                'is_sales_open' => (!$isCompleted && $event['status'] === 'published'),
                'is_completed' => $isCompleted,
                'organizer' => [
                    'name' => $event['organizer_name'],
                    'rating' => (float)($event['organizer_rating'] ?? 5.0),
                    'bio' => $event['organizer_bio'] ?? null
                ]
            ],
            'tickets' => $ticketTiers,
            'pricing_rules' => [
                'currency' => get_setting('currency', 'NGN'),
                'currency_symbol' => get_currency_symbol(),
                'pass_fee_to_buyer' => !empty($event['pass_fee']),
                'platform_fee_percent' => !empty($event['pass_fee']) ? $platformFeePercent : 0.00,
                'extra_charge' => !empty($event['pass_fee']) ? $extraCharge : 0.00
            ],
            'links' => [
                'api_checkout' => $baseUrl . '/api/v1/events/' . $eventSlug . '/checkout',
                'api_validate_promo' => $baseUrl . '/api/v1/checkout/validate-promo',
                'api_verify_order' => $baseUrl . '/api/v1/checkout/verify/{reference}',
                'web_checkout' => $baseUrl . '/checkout/' . $eventSlug,
                'web_flyer' => $baseUrl . '/f/' . $event['id'],
                'embed_iframe' => $baseUrl . '/embed/' . $eventSlug
            ]
        ]);
    }

    /**
     * POST /api/v1/checkout/validate-promo
     *
     * Validate coupon code for an event and return real-time discounted subtotal.
     */
    public function validatePromo(Request $request)
    {
        $data = $request->getBody();
        $code = strtoupper(trim((string)($data['code'] ?? '')));
        $eventId = $data['event_id'] ?? null;
        $subtotal = (float)($data['subtotal'] ?? 0);

        if (empty($code)) {
            $this->jsonResponse(['success' => false, 'message' => 'Promo code cannot be empty.'], 400);
        }

        $db = Application::$app->db->pdo;

        // Resolve event
        $eventStmt = $db->prepare("SELECT id, pass_fee, organizer_id FROM events WHERE id = :id_num OR slug = :slug OR custom_url = :custom_url LIMIT 1");
        $eventStmt->execute([
            'id_num' => is_numeric($eventId) ? (int)$eventId : 0,
            'slug' => (string)$eventId,
            'custom_url' => (string)$eventId
        ]);
        $event = $eventStmt->fetch(\PDO::FETCH_ASSOC);

        if (!$event) {
            $this->jsonResponse(['success' => false, 'message' => 'Event not found.'], 404);
        }

        // Validate promo code
        $stmt = $db->prepare("
            SELECT p.* 
            FROM promo_codes p
            WHERE p.code = :code AND p.status = 'active'
            AND (p.event_id = :event_id OR (p.event_id IS NULL AND p.host_id = :host_id))
            AND (p.expires_at IS NULL OR p.expires_at > NOW())
            AND p.used_count < p.max_uses
            LIMIT 1
        ");
        $stmt->execute([
            'code' => $code,
            'event_id' => $event['id'],
            'host_id' => $event['organizer_id']
        ]);
        $promo = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$promo) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Invalid, expired, or fully redeemed coupon code.'
            ], 422);
        }

        $discount = 0.00;
        if ($promo['discount_type'] === 'percentage') {
            $discount = $subtotal * ((float)$promo['discount_value'] / 100);
        } else {
            $discount = min($subtotal, (float)$promo['discount_value']);
        }

        $netSubtotal = max(0, $subtotal - $discount);
        $platformFeePercent = (float)get_setting('platform_fee', 5);
        $fee = (!empty($event['pass_fee']) && $netSubtotal > 0) ? ($netSubtotal * ($platformFeePercent / 100)) : 0.00;
        $extraCharge = $netSubtotal > 0 ? (float)get_setting('extra_charge', 100.00) : 0.00;
        $newTotal = $netSubtotal > 0 ? max(0, $netSubtotal + $fee + $extraCharge) : 0.00;

        $this->jsonResponse([
            'success' => true,
            'code' => $promo['code'],
            'discount_type' => $promo['discount_type'],
            'discount_value' => (float)$promo['discount_value'],
            'discount_amount' => round($discount, 2),
            'formatted_discount' => format_price($discount),
            'net_subtotal' => round($netSubtotal, 2),
            'fee_amount' => round($fee, 2),
            'extra_charge' => round($extraCharge, 2),
            'new_total' => round($newTotal, 2),
            'formatted_new_total' => format_price($newTotal),
            'is_free' => ($newTotal <= 0.00),
            'message' => 'Coupon code applied successfully!'
        ]);
    }

    /**
     * POST /api/v1/events/{id}/checkout
     * or POST /api/v1/checkout
     *
     * Process external domain ticket purchase.
     * Handles:
     * - Attendee validation
     * - Ticket inventory reservation & sold count verification
     * - Promo code discount calculation
     * - Referral attribution
     * - Free ticket immediate fulfillment
     * - Paid ticket Paystack transaction initialization with custom external return callback
     */
    public function checkout(Request $request, $id = null)
    {
        $data = $request->getBody();
        $eventId = $id ?: ($data['event_id'] ?? null);

        $name = trim((string)($data['name'] ?? ''));
        $email = trim((string)($data['email'] ?? ''));
        $phone = trim((string)($data['phone'] ?? ''));
        $externalCallbackUrl = trim((string)($data['callback_url'] ?? ($data['redirect_url'] ?? '')));
        $promoCode = strtoupper(trim((string)($data['promo_code'] ?? '')));
        $refCode = trim((string)($data['ref'] ?? ''));

        // Validate basic inputs
        if (empty($name)) {
            $this->jsonResponse(['success' => false, 'message' => 'Attendee full name is required.'], 422);
        }

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->jsonResponse(['success' => false, 'message' => 'A valid email address is required.'], 422);
        }

        // Validate tickets requested
        $qtyMap = $data['tickets'] ?? ($data['qty'] ?? []);
        if (!is_array($qtyMap) || empty($qtyMap)) {
            $this->jsonResponse(['success' => false, 'message' => 'Please select at least one ticket to purchase.'], 422);
        }

        $db = Application::$app->db->pdo;

        // Resolve event
        $stmt = $db->prepare("
            SELECT e.*, u.name as organizer_name, u.email as organizer_email 
            FROM events e 
            INNER JOIN users u ON e.organizer_id = u.id
            WHERE e.id = :id_num OR e.slug = :slug OR e.custom_url = :custom_url 
            LIMIT 1
        ");
        $stmt->execute([
            'id_num' => is_numeric($eventId) ? (int)$eventId : 0,
            'slug' => (string)$eventId,
            'custom_url' => (string)$eventId
        ]);
        $event = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$event) {
            $this->jsonResponse(['success' => false, 'message' => 'Event not found.'], 404);
        }

        // Gatekeeper: Host must have an approved, active Custom Domain / API integration
        if (!$this->isDomainAccessActive((int)$event['id'])) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Custom Domain & API access has not been activated for this event. The event host must request and activate access from their host dashboard.'
            ], 403);
        }

        // Check if sales are open
        $eventDate = strtotime($event['date'] ?? '');
        $liveWindowEnd = $eventDate ? $eventDate + (event_completion_hours() * 3600) : 0;
        $isCompleted = ($eventDate && time() > $liveWindowEnd) || in_array(strtolower($event['status'] ?? ''), ['completed', 'past', 'cancelled']);

        if ($isCompleted || $event['status'] !== 'published') {
            $this->jsonResponse(['success' => false, 'message' => 'Ticket sales are closed for this event.'], 400);
        }

        // Fetch ticket types and verify inventory
        $ttStmt = $db->prepare("
            SELECT tt.*,
                   (SELECT COUNT(*) FROM tickets t WHERE t.ticket_type_id = tt.id AND t.status IN ('Paid', 'Comped')) as sold
            FROM ticket_types tt
            WHERE tt.event_id = :event_id
        ");
        $ttStmt->execute(['event_id' => $event['id']]);
        $availableTiers = $ttStmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];

        $tiersById = [];
        foreach ($availableTiers as $t) {
            $tiersById[(int)$t['id']] = $t;
        }

        $subtotal = 0.00;
        $totalItemsCount = 0;
        $cleanQtyMap = [];
        $priceMap = [];

        foreach ($qtyMap as $tId => $quantity) {
            $tId = (int)$tId;
            $quantity = (int)$quantity;

            if ($quantity <= 0) continue;

            if (!isset($tiersById[$tId])) {
                $this->jsonResponse(['success' => false, 'message' => "Ticket tier ID [{$tId}] is not valid for this event."], 422);
            }

            $tier = $tiersById[$tId];
            $capacity = (int)$tier['capacity'];
            $sold = (int)$tier['sold'];
            $remaining = max(0, $capacity - $sold);

            if ($capacity > 0 && $quantity > $remaining) {
                $this->jsonResponse([
                    'success' => false,
                    'message' => "Insufficient ticket quantity for '{$tier['name']}'. Only {$remaining} remaining."
                ], 422);
            }

            $cleanQtyMap[$tId] = $quantity;
            $priceMap[$tId] = (float)$tier['price'];
            $subtotal += ((float)$tier['price'] * $quantity);
            $totalItemsCount += $quantity;
        }

        if ($totalItemsCount <= 0) {
            $this->jsonResponse(['success' => false, 'message' => 'Please select at least 1 ticket.'], 422);
        }

        // Apply promo code discount if provided
        $discountAmount = 0.00;
        $validatedPromoCode = null;

        if (!empty($promoCode)) {
            $promoStmt = $db->prepare("
                SELECT p.* 
                FROM promo_codes p
                WHERE p.code = :code AND p.status = 'active'
                AND (p.event_id = :event_id OR (p.event_id IS NULL AND p.host_id = :host_id))
                AND (p.expires_at IS NULL OR p.expires_at > NOW())
                AND p.used_count < p.max_uses
                LIMIT 1
            ");
            $promoStmt->execute([
                'code' => $promoCode,
                'event_id' => $event['id'],
                'host_id' => $event['organizer_id']
            ]);
            $promo = $promoStmt->fetch(\PDO::FETCH_ASSOC);

            if ($promo) {
                $validatedPromoCode = $promo['code'];
                if ($promo['discount_type'] === 'percentage') {
                    $discountAmount = $subtotal * ((float)$promo['discount_value'] / 100);
                } else {
                    $discountAmount = min($subtotal, (float)$promo['discount_value']);
                }
            }
        }

        // Calculate fee policy
        $passFee = !empty($event['pass_fee']);
        $platformFeePercent = (float)get_setting('platform_fee', 5);
        $netSubtotal = max(0, $subtotal - $discountAmount);
        $fee = ($passFee && $netSubtotal > 0) ? ($netSubtotal * ($platformFeePercent / 100)) : 0.00;
        $extraCharge = $netSubtotal > 0 ? (float)get_setting('extra_charge', 100.00) : 0.00;
        $totalPaid = $netSubtotal > 0 ? max(0, $netSubtotal + $fee + $extraCharge) : 0.00;

        // Resolve referral
        $referralId = null;
        if (!empty($refCode)) {
            $refStmt = $db->prepare("SELECT id FROM event_referrals WHERE event_id = :event_id AND referral_code = :code AND status = 'active' LIMIT 1");
            $refStmt->execute(['event_id' => $event['id'], 'code' => strtolower($refCode)]);
            $refRow = $refStmt->fetch(\PDO::FETCH_ASSOC);
            if ($refRow) {
                $referralId = (int)$refRow['id'];
            }
        }

        // Unique transaction reference
        $reference = 'EVT-' . $event['id'] . '-' . strtoupper(bin2hex(random_bytes(6)));

        // ════════════════════════════════════════════════════════════════
        // CASE 1: FREE ORDER ($totalPaid <= 0)
        // Issue tickets immediately without gateway redirect
        // ════════════════════════════════════════════════════════════════
        if ($totalPaid <= 0.00) {
            $db->beginTransaction();
            try {
                $createdTickets = [];

                foreach ($cleanQtyMap as $ttId => $qty) {
                    $tier = $tiersById[$ttId];
                    for ($i = 0; $i < $qty; $i++) {
                        $ticketCode = 'EV-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));
                        $insertStmt = $db->prepare("
                            INSERT INTO tickets (id, event_id, ticket_type_id, attendee_name, attendee_email, price, status, purchase_date)
                            VALUES (:id, :event_id, :ticket_type_id, :name, :email, :price, 'Paid', NOW())
                        ");
                        $insertStmt->execute([
                            'id' => $ticketCode,
                            'event_id' => $event['id'],
                            'ticket_type_id' => $ttId,
                            'name' => $name,
                            'email' => $email,
                            'price' => $tier['price']
                        ]);

                        $createdTickets[] = [
                            'ticket_code' => $ticketCode,
                            'ticket_type' => $tier['name'],
                            'price' => (float)$tier['price']
                        ];
                    }
                }

                // Record transaction
                $trxStmt = $db->prepare("
                    INSERT INTO transactions (reference, event_id, payer_name, payer_email, type, payment_method, subtotal_amount, fee_amount, extra_charge_amount, referral_id, promo_code, discount_amount, total_amount, status, created_at, updated_at)
                    VALUES (:reference, :event_id, :name, :email, 'payment', 'free', :subtotal, :fee, :extra, :ref_id, :promo, :discount, 0.00, 'completed', NOW(), NOW())
                ");
                $trxStmt->execute([
                    'reference' => $reference,
                    'event_id' => $event['id'],
                    'name' => $name,
                    'email' => $email,
                    'subtotal' => $subtotal,
                    'fee' => $fee,
                    'extra' => $extraCharge,
                    'ref_id' => $referralId,
                    'promo' => $validatedPromoCode,
                    'discount' => $discountAmount
                ]);

                // Increment promo code used count
                if (!empty($validatedPromoCode)) {
                    $db->prepare("UPDATE promo_codes SET used_count = used_count + 1 WHERE code = :code")
                       ->execute(['code' => $validatedPromoCode]);
                }

                // Attribute referral sales
                if (!empty($referralId)) {
                    \App\Models\EventReferral::recordSale($referralId, count($createdTickets), $subtotal);
                }

                $db->commit();

                // Send email confirmation
                try {
                    \Ace\Mail::send(
                        $email,
                        'Your Ticket Confirmation - ' . $event['name'],
                        'emails/ticket_confirmation',
                        [
                            'attendee_name' => $name,
                            'event_name' => $event['name'],
                            'event_date' => $eventDate ? date('D, M d, Y • g:i A', $eventDate) : '',
                            'event_location' => $event['location'],
                            'payment_method' => 'FREE',
                            'total_amount' => 0.00,
                            'tickets' => $createdTickets
                        ]
                    );
                } catch (\Throwable $mailErr) {
                    error_log("API Checkout: Email dispatch failed: " . $mailErr->getMessage());
                }

                $redirectUrl = null;
                if (!empty($externalCallbackUrl)) {
                    $sep = str_contains($externalCallbackUrl, '?') ? '&' : '?';
                    $redirectUrl = $externalCallbackUrl . $sep . 'reference=' . urlencode($reference) . '&status=success';
                }

                $this->jsonResponse([
                    'success' => true,
                    'is_free' => true,
                    'payment_required' => false,
                    'reference' => $reference,
                    'message' => 'Tickets issued successfully.',
                    'event' => [
                        'id' => (int)$event['id'],
                        'name' => $event['name'],
                        'date' => $event['date'],
                        'location' => $event['location']
                    ],
                    'attendee' => [
                        'name' => $name,
                        'email' => $email,
                        'phone' => $phone
                    ],
                    'tickets' => $createdTickets,
                    'redirect_url' => $redirectUrl
                ], 200);

            } catch (\Throwable $e) {
                $db->rollBack();
                $this->jsonResponse(['success' => false, 'message' => 'Order processing failed: ' . $e->getMessage()], 500);
            }
        }

        // ════════════════════════════════════════════════════════════════
        // CASE 2: PAID ORDER ($totalPaid > 0) -> PAYSTACK GATEWAY
        // ════════════════════════════════════════════════════════════════
        try {
            $paystack = new PaystackService();

            $baseUrl = rtrim($_ENV['APP_URL'] ?? ('http://' . ($_SERVER['HTTP_HOST'] ?? 'localhost')), '/');
            $callbackUrl = $baseUrl . '/checkout/paystack/callback';

            // Complete Order Payload for Session, DB, and Gateway
            $orderPayload = [
                'event_id' => (int)$event['id'],
                'event_name' => $event['name'],
                'event_date' => $event['date'],
                'event_location' => $event['location'] ?? '',
                'attendee_name' => $name,
                'attendee_email' => $email,
                'attendee_phone' => $phone,
                'total_amount' => $totalPaid,
                'subtotal' => $subtotal,
                'qty_map' => $cleanQtyMap,
                'price_map' => $priceMap,
                'reference' => $reference,
                'pass_fee' => !empty($event['pass_fee']),
                'promo_code' => $validatedPromoCode,
                'discount_amount' => $discountAmount,
                'referral_id' => $referralId,
                'external_callback_url' => $externalCallbackUrl ?: null
            ];

            $storedPayloadJson = json_encode(['order_data' => $orderPayload, 'initialized_at' => date('Y-m-d H:i:s')]);

            // Save pending transaction in database
            $trxStmt = $db->prepare("
                INSERT INTO transactions (reference, event_id, payer_name, payer_email, type, payment_method, subtotal_amount, fee_amount, extra_charge_amount, referral_id, promo_code, discount_amount, total_amount, status, gateway_response, created_at, updated_at)
                VALUES (:reference, :event_id, :name, :email, 'payment', 'paystack', :subtotal, :fee, :extra, :ref_id, :promo, :discount, :total, 'pending', :gateway_response, NOW(), NOW())
                ON DUPLICATE KEY UPDATE 
                    payer_name = VALUES(payer_name),
                    payer_email = VALUES(payer_email),
                    total_amount = VALUES(total_amount),
                    gateway_response = VALUES(gateway_response),
                    updated_at = NOW()
            ");
            $trxStmt->execute([
                'reference' => $reference,
                'event_id' => $event['id'],
                'name' => $name,
                'email' => $email,
                'subtotal' => $subtotal,
                'fee' => $fee,
                'extra' => $extraCharge,
                'ref_id' => $referralId,
                'promo' => $validatedPromoCode,
                'discount' => $discountAmount,
                'total' => $totalPaid,
                'gateway_response' => $storedPayloadJson
            ]);

            // Initialize transaction with Paystack API
            $paystackData = $paystack->initializeTransaction(
                $email,
                $totalPaid,
                $callbackUrl,
                $reference,
                $orderPayload
            );

            $this->jsonResponse([
                'success' => true,
                'is_free' => false,
                'payment_required' => true,
                'reference' => $reference,
                'amount' => round($totalPaid, 2),
                'formatted_amount' => format_price($totalPaid),
                'currency' => get_setting('currency', 'NGN'),
                'authorization_url' => $paystackData['authorization_url'] ?? '',
                'access_code' => $paystackData['access_code'] ?? '',
                'external_callback_url' => $externalCallbackUrl ?: null,
                'message' => 'Payment initialized. Redirect customer to authorization_url.'
            ], 200);

        } catch (\Throwable $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Payment gateway initialization failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/v1/checkout/verify/{reference}
     *
     * Verify payment status for a transaction reference and retrieve issued tickets.
     */
    public function verifyOrder(Request $request, $reference = null)
    {
        $ref = $reference ?: ($request->getBody()['reference'] ?? null);

        if (!$ref) {
            $this->jsonResponse(['success' => false, 'message' => 'Transaction reference is required.'], 400);
        }

        $paymentService = new PaymentService();
        $result = $paymentService->finalizeSuccessfulPayment($ref);

        if ($result['success']) {
            $event = $result['event'] ?? [];
            $tx = $result['transaction'] ?? [];
            $tickets = $result['tickets'] ?? [];

            $formattedTickets = [];
            foreach ($tickets as $t) {
                $formattedTickets[] = [
                    'ticket_code' => $t['id'] ?? ($t['ticket_code'] ?? ''),
                    'ticket_type' => $t['ticket_type'] ?? ($t['ticket_type_name'] ?? 'General Admission'),
                    'attendee_name' => $t['attendee_name'] ?? ($tx['payer_name'] ?? ''),
                    'attendee_email' => $t['attendee_email'] ?? ($tx['payer_email'] ?? ''),
                    'price' => (float)($t['price'] ?? 0),
                    'status' => $t['status'] ?? 'Paid'
                ];
            }

            $this->jsonResponse([
                'success' => true,
                'reference' => $ref,
                'status' => 'completed',
                'order' => [
                    'reference' => $ref,
                    'payer_name' => $tx['payer_name'] ?? 'Attendee',
                    'payer_email' => $tx['payer_email'] ?? '',
                    'total_amount' => (float)($tx['total_amount'] ?? 0),
                    'formatted_total' => format_price((float)($tx['total_amount'] ?? 0)),
                    'payment_method' => strtoupper($tx['payment_method'] ?? 'PAYSTACK'),
                    'created_at' => $tx['created_at'] ?? date('Y-m-d H:i:s')
                ],
                'event' => [
                    'id' => (int)($event['id'] ?? 0),
                    'name' => $event['name'] ?? 'Event',
                    'date' => $event['date'] ?? null,
                    'location' => $event['location'] ?? ''
                ],
                'tickets' => $formattedTickets
            ]);
        }

        $this->jsonResponse([
            'success' => false,
            'reference' => $ref,
            'status' => 'pending_or_failed',
            'message' => $result['message'] ?? 'Payment has not been confirmed yet.'
        ], 400);
    }

    /**
     * GET /embed/{id}
     *
     * Render a self-contained, iframe-embeddable checkout widget.
     * Hosts embed this via <iframe src="https://yourdomain.com/embed/my-event-slug"></iframe>
     * on their own websites to sell tickets directly.
     */
    public function embedCheckout(Request $request, $id = null): string
    {
        $id = $id ?: ($request->getBody()['id'] ?? null);

        if (!$id) {
            return $this->renderEmbedError('Event identifier is required.');
        }

        $db = Application::$app->db->pdo;

        $stmt = $db->prepare("
            SELECT e.*, u.name as organizer_name
            FROM events e
            INNER JOIN users u ON e.organizer_id = u.id
            WHERE e.id = :id_num OR e.slug = :slug OR e.custom_url = :custom_url
            LIMIT 1
        ");
        $stmt->execute([
            'id_num' => is_numeric($id) ? (int)$id : 0,
            'slug' => (string)$id,
            'custom_url' => (string)$id
        ]);
        $event = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$event) {
            return $this->renderEmbedError('Event not found.');
        }

        // Gatekeeper: Host must have an approved, active Custom Domain / API integration
        if (!$this->isDomainAccessActive((int)$event['id'])) {
            return $this->renderEmbedError('Custom Domain & Embed Widget access has not been activated for this event. The event organizer must request and activate access from their event dashboard.');
        }

        $eventDate = strtotime($event['date'] ?? '');
        $liveWindowEnd = $eventDate ? $eventDate + (event_completion_hours() * 3600) : 0;
        $isCompleted = ($eventDate && time() > $liveWindowEnd) || in_array(strtolower($event['status'] ?? ''), ['completed', 'past', 'cancelled']);

        // Fetch ticket tiers
        $ttStmt = $db->prepare("
            SELECT tt.*, (SELECT COUNT(*) FROM tickets t WHERE t.ticket_type_id = tt.id AND t.status IN ('Paid', 'Comped')) as sold
            FROM ticket_types tt WHERE tt.event_id = :event_id ORDER BY tt.price ASC
        ");
        $ttStmt->execute(['event_id' => $event['id']]);
        $ticketTypes = $ttStmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];

        $baseUrl = rtrim($_ENV['APP_URL'] ?? ('http://' . ($_SERVER['HTTP_HOST'] ?? 'localhost')), '/');
        $eventSlug = !empty($event['custom_url']) ? $event['custom_url'] : ($event['slug'] ?: $event['id']);

        $imagePath = $event['image'] ?? '';
        $imageUrl = '';
        if (!empty($imagePath)) {
            $imageUrl = str_starts_with($imagePath, 'http') ? $imagePath : $baseUrl . '/' . ltrim($imagePath, '/');
        }

        $platformFeePercent = (float)get_setting('platform_fee', 5);
        $extraCharge = (float)get_setting('extra_charge', 100.00);
        $passFee = !empty($event['pass_fee']);
        $currencySymbol = get_currency_symbol();

        $this->setLayout('embed');

        return $this->render('embed/checkout', [
            'event' => $event,
            'eventDate' => $eventDate,
            'isCompleted' => $isCompleted,
            'ticketTypes' => $ticketTypes,
            'imageUrl' => $imageUrl,
            'baseUrl' => $baseUrl,
            'eventSlug' => $eventSlug,
            'platformFeePercent' => $platformFeePercent,
            'extraCharge' => $extraCharge,
            'passFee' => $passFee,
            'currencySymbol' => $currencySymbol,
            'apiCheckoutUrl' => $baseUrl . '/api/v1/events/' . $eventSlug . '/checkout',
            'apiPromoUrl' => $baseUrl . '/api/v1/checkout/validate-promo'
        ]);
    }

    /**
     * Render a minimal HTML error for the embed iframe.
     */
    private function renderEmbedError(string $message): string
    {
        http_response_code(404);
        return '<!DOCTYPE html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
        <style>body{font-family:system-ui,sans-serif;display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0;background:#fafafa;color:#64748b;text-align:center;padding:2rem}
        .err{max-width:320px}.err h2{font-size:1.1rem;color:#334155;margin-bottom:.5rem}.err p{font-size:.9rem}</style></head>
        <body><div class="err"><h2>⚠️ Unavailable</h2><p>' . htmlspecialchars($message) . '</p></div></body></html>';
    }

    /**
     * Check whether Custom Domain & API access has been approved and activated by an admin.
     */
    protected function isDomainAccessActive(int $eventId): bool
    {
        $db = Application::$app->db->pdo;
        $stmt = $db->prepare("
            SELECT id FROM event_domain_requests 
            WHERE event_id = :event_id AND status = 'approved_active' 
            LIMIT 1
        ");
        $stmt->execute(['event_id' => $eventId]);
        return (bool)$stmt->fetchColumn();
    }
}
