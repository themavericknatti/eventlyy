<?php

namespace App\Services;

use Ace\Application;
use Ace\PaystackService;
use Exception;

class PaymentService
{
    /**
     * Finalize and fulfill a successful payment idempotently.
     * Works whether triggered by:
     * 1) Paystack Webhook (server-to-server)
     * 2) Browser redirect callback
     * 3) Manual host / admin verification tool
     * 4) Background automated reconciler
     *
     * @param string $reference Transaction reference
     * @param array $gatewayData Optional raw Paystack transaction verification/webhook data
     * @return array Result containing success status, tickets, and event info
     */
    public function finalizeSuccessfulPayment(string $reference, array $gatewayData = []): array
    {
        $db = Application::$app->db->pdo;
        $reference = trim($reference);

        if (empty($reference)) {
            return ['success' => false, 'message' => 'Missing transaction reference.'];
        }

        // 1. Check if transaction is already completed in database (Idempotency protection)
        $stmt = $db->prepare("SELECT * FROM transactions WHERE reference = :reference LIMIT 1");
        $stmt->execute(['reference' => $reference]);
        $tx = $stmt->fetch(\PDO::FETCH_ASSOC);

        // If already completed and tickets exist, return success immediately without duplicating
        if ($tx && $tx['status'] === 'completed') {
            $existingTicketsStmt = $db->prepare("
                SELECT t.*, tt.name as ticket_type, t.id as ticket_code 
                FROM tickets t 
                LEFT JOIN ticket_types tt ON t.ticket_type_id = tt.id 
                WHERE t.status_detail LIKE :ref OR t.id = :tx_id
            ");
            $existingTicketsStmt->execute([
                'ref' => '%' . $reference . '%',
                'tx_id' => $reference
            ]);
            $existingTickets = $existingTicketsStmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];

            $attendeeName = !empty($tx['payer_name']) ? $tx['payer_name'] : (!empty($existingTickets[0]['attendee_name']) ? $existingTickets[0]['attendee_name'] : 'Attendee');
            $attendeeEmail = !empty($tx['payer_email']) ? $tx['payer_email'] : (!empty($existingTickets[0]['attendee_email']) ? $existingTickets[0]['attendee_email'] : '');

            $eventStmt = $db->prepare("SELECT * FROM events WHERE id = :event_id LIMIT 1");
            $eventStmt->execute(['event_id' => $tx['event_id'] ?? ($existingTickets[0]['event_id'] ?? 0)]);
            $existingEvent = $eventStmt->fetch(\PDO::FETCH_ASSOC) ?: [];

            return [
                'success' => true,
                'already_completed' => true,
                'message' => 'Transaction was already completed and fulfilled.',
                'reference' => $reference,
                'transaction' => $tx,
                'event' => $existingEvent,
                'attendee_name' => $attendeeName,
                'attendee_email' => $attendeeEmail,
                'total_amount' => (float)($tx['total_amount'] ?? 0),
                'tickets' => $existingTickets
            ];
        }

        // 2. Resolve Order Metadata
        $orderData = [];

        // Check A: Database transaction stored gateway_response JSON
        if (!empty($tx['gateway_response'])) {
            $storedJson = json_decode($tx['gateway_response'], true);
            if (is_array($storedJson) && (!empty($storedJson['qty_map']) || !empty($storedJson['order_data']))) {
                $orderData = $storedJson['order_data'] ?? $storedJson;
            }
        }

        // Check B: Paystack Gateway Data metadata
        if (empty($orderData['qty_map']) && !empty($gatewayData['metadata'])) {
            $meta = is_array($gatewayData['metadata']) ? $gatewayData['metadata'] : json_decode($gatewayData['metadata'], true);
            if (is_array($meta) && !empty($meta['qty_map'])) {
                $orderData = array_merge($orderData, $meta);
            }
        }

        // Check C: Active PHP Session (if called from user browser)
        if (empty($orderData['qty_map']) && class_exists('\Ace\Application') && Application::$app->session) {
            $sessionOrder = Application::$app->session->get('pending_order');
            if (is_array($sessionOrder) && ($sessionOrder['reference'] ?? '') === $reference) {
                $orderData = array_merge($orderData, $sessionOrder);
            }
        }

        // Check D: Query Paystack Verify API directly if order data is still incomplete
        if (empty($orderData['qty_map']) || empty($gatewayData)) {
            try {
                $paystack = new PaystackService();
                $verifyRes = $paystack->verifyTransaction($reference);
                if (isset($verifyRes['status']) && $verifyRes['status'] === 'success') {
                    $gatewayData = $verifyRes;
                    if (!empty($verifyRes['metadata'])) {
                        $meta = is_array($verifyRes['metadata']) ? $verifyRes['metadata'] : json_decode($verifyRes['metadata'], true);
                        if (is_array($meta)) {
                            $orderData = array_merge($orderData, $meta);
                        }
                    }
                } else {
                    return [
                        'success' => false,
                        'message' => 'Paystack transaction status is ' . ($verifyRes['status'] ?? 'unknown') . ', not successful.'
                    ];
                }
            } catch (\Throwable $ex) {
                error_log("Paystack verification exception for {$reference}: " . $ex->getMessage());
            }
        }

        // Validate that we have the target event
        $eventId = (int)($orderData['event_id'] ?? ($tx['event_id'] ?? 0));
        if ($eventId <= 0) {
            return [
                'success' => false,
                'message' => 'Could not determine event ID for transaction reference ' . $reference
            ];
        }

        // Fetch event information
        $eventStmt = $db->prepare("SELECT * FROM events WHERE id = :event_id LIMIT 1");
        $eventStmt->execute(['event_id' => $eventId]);
        $event = $eventStmt->fetch(\PDO::FETCH_ASSOC);

        if (!$event) {
            return ['success' => false, 'message' => 'Associated event not found in database.'];
        }

        // Attendee Details
        $name = trim($orderData['attendee_name'] ?? ($tx['payer_name'] ?? ($gatewayData['customer']['first_name'] ?? 'Attendee')));
        $email = trim($orderData['attendee_email'] ?? ($tx['payer_email'] ?? ($gatewayData['customer']['email'] ?? '')));
        $userId = !empty($orderData['user_id']) ? (int)$orderData['user_id'] : (!empty($tx['user_id']) ? (int)$tx['user_id'] : null);
        $qtyMap = $orderData['qty_map'] ?? [];
        $attendees = $orderData['attendees'] ?? [];

        // Fallback: If qtyMap is missing but subtotal exists, assign to the event's primary ticket type
        if (empty($qtyMap)) {
            $ttFallback = $db->query("SELECT id, price FROM ticket_types WHERE event_id = {$eventId} ORDER BY price ASC LIMIT 1")->fetch(\PDO::FETCH_ASSOC);
            if ($ttFallback) {
                $qtyMap[$ttFallback['id']] = 1;
            }
        }

        // Fetch ticket types for event
        $ttStmt = $db->prepare("SELECT * FROM ticket_types WHERE event_id = :event_id");
        $ttStmt->execute(['event_id' => $eventId]);
        $ticketTypes = $ttStmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        $ticketTypesById = [];
        foreach ($ticketTypes as $tt) {
            $ticketTypesById[$tt['id']] = $tt;
        }

        $createdTickets = [];
        $calculatedSubtotal = 0.00;

        // Enforce per-person purchase limit (max_per_person) before issuing tickets
        $buyerEmailLower = strtolower(trim($email));
        foreach ($qtyMap as $ttId => $qty) {
            $qty = (int)$qty;
            if ($qty <= 0 || !isset($ticketTypesById[$ttId])) continue;

            $tt = $ticketTypesById[$ttId];
            $maxPerPerson = !empty($tt['max_per_person']) ? (int)$tt['max_per_person'] : 0;
            if ($maxPerPerson <= 0) continue; // 0 or NULL = unlimited

            $existingStmt = $db->prepare("
                SELECT COUNT(*) FROM tickets 
                WHERE ticket_type_id = :tt_id 
                AND LOWER(attendee_email) = :email 
                AND status IN ('Paid', 'Comped')
            ");
            $existingStmt->execute(['tt_id' => $ttId, 'email' => $buyerEmailLower]);
            $existingCount = (int)$existingStmt->fetchColumn();

            if (($existingCount + $qty) > $maxPerPerson) {
                $remaining = max(0, $maxPerPerson - $existingCount);
                return [
                    'success' => false,
                    'message' => "Purchase limit exceeded for \"{$tt['name']}\": maximum {$maxPerPerson} per person (you already have {$existingCount}, requested {$qty}, allowed {$remaining} more)."
                ];
            }
        }

        $db->beginTransaction();
        try {
            // Generate individual ticket rows
            foreach ($qtyMap as $ttId => $qty) {
                $qty = (int)$qty;
                if ($qty <= 0 || !isset($ticketTypesById[$ttId])) {
                    continue;
                }

                $tt = $ticketTypesById[$ttId];
                $priceMap = $orderData['price_map'] ?? [];
                $tierUnitPrice = isset($priceMap[$ttId]) ? (float)$priceMap[$ttId] : (float)$tt['price'];
                $tierTotalPrice = $tierUnitPrice * $qty;
                $admits = max(1, (int)($tt['admits'] ?? 1));
                $totalPassesForTier = $qty * $admits;
                $seatPrice = round($tierTotalPrice / $totalPassesForTier, 2);

                for ($i = 0; $i < $totalPassesForTier; $i++) {
                    $ticketCode = 'EV-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));

                    $attName = $attendees[$ttId][$i]['name'] ?? ($attendees[$i]['name'] ?? $name);
                    $attEmail = $attendees[$ttId][$i]['email'] ?? ($attendees[$i]['email'] ?? $email);
                    if (empty($attName)) $attName = $name;
                    if (empty($attEmail)) $attEmail = $email;

                    $seatLabel = $admits > 1 ? " (Guest " . ($i + 1) . " of " . $totalPassesForTier . ")" : "";
                    $typeNameDisplay = $tt['name'] . $seatLabel;

                    $insertStmt = $db->prepare("
                        INSERT INTO tickets (id, event_id, ticket_type_id, user_id, attendee_name, attendee_email, price, status, status_detail, purchase_date, checked_in)
                        VALUES (:id, :event_id, :ticket_type_id, :user_id, :name, :email, :price, 'Paid', :status_detail, NOW(), 0)
                    ");
                    $insertStmt->execute([
                        'id' => $ticketCode,
                        'event_id' => $eventId,
                        'ticket_type_id' => $tt['id'],
                        'user_id' => $userId,
                        'name' => $attName,
                        'email' => $attEmail,
                        'price' => $seatPrice,
                        'status_detail' => 'Paystack Ref: ' . $reference
                    ]);

                    $createdTickets[] = [
                        'ticket_code' => $ticketCode,
                        'attendee_name' => $attName,
                        'attendee_email' => $attEmail,
                        'ticket_type' => $typeNameDisplay,
                        'base_ticket_type' => $tt['name'],
                        'price' => $seatPrice,
                        'status' => 'Paid',
                        'checked_in' => false
                    ];
                    $calculatedSubtotal += $seatPrice;
                }
            }

            // Fee calculations
            $platformFeePercent = (float)site_setting('platform_fee', 5);
            $feeAmount = !empty($event['pass_fee']) ? ($calculatedSubtotal * ($platformFeePercent / 100)) : 0.00;
            $extraCharge = (float)site_setting('extra_charge', 100.00);
            $totalAmount = (float)($orderData['total_amount'] ?? ($tx['total_amount'] ?? ($calculatedSubtotal + $feeAmount + $extraCharge)));
            $referralId = !empty($orderData['referral_id']) ? (int)$orderData['referral_id'] : (!empty($tx['referral_id']) ? (int)$tx['referral_id'] : null);
            if (!$referralId && class_exists('\Ace\Application') && Application::$app->session) {
                $referralId = Application::$app->session->get('event_ref_' . $eventId) ?: null;
            }
            if (!$referralId && !empty($_COOKIE['event_ref_' . $eventId])) {
                $referralId = (int)$_COOKIE['event_ref_' . $eventId];
            }
            $promoCode = $orderData['promo_code'] ?? ($tx['promo_code'] ?? null);
            $discountAmount = (float)($orderData['discount_amount'] ?? ($tx['discount_amount'] ?? 0.00));

            // Merge gateway response payload for audit trail
            $finalGatewayResponse = json_encode([
                'source' => !empty($gatewayData['source']) ? $gatewayData['source'] : 'verified_webhook_callback',
                'gateway_data' => $gatewayData,
                'order_data' => $orderData,
                'finalized_at' => date('Y-m-d H:i:s'),
                'tickets_issued_count' => count($createdTickets)
            ]);

            // Insert or Update Transactions table record
            if ($tx) {
                $updateTxStmt = $db->prepare("
                    UPDATE transactions 
                    SET status = 'completed',
                        subtotal_amount = :subtotal_amount,
                        fee_amount = :fee_amount,
                        extra_charge_amount = :extra_charge_amount,
                        referral_id = COALESCE(:referral_id, referral_id),
                        total_amount = :total_amount,
                        gateway_response = :gateway_response,
                        updated_at = NOW()
                    WHERE reference = :reference
                ");
                $updateTxStmt->execute([
                    'subtotal_amount' => $calculatedSubtotal,
                    'fee_amount' => $feeAmount,
                    'extra_charge_amount' => $extraCharge,
                    'referral_id' => $referralId,
                    'total_amount' => $totalAmount,
                    'gateway_response' => $finalGatewayResponse,
                    'reference' => $reference
                ]);
            } else {
                $insertTxStmt = $db->prepare("
                    INSERT INTO transactions (reference, event_id, user_id, payer_name, payer_email, type, payment_method, subtotal_amount, fee_amount, extra_charge_amount, referral_id, promo_code, discount_amount, total_amount, status, gateway_response, created_at, updated_at)
                    VALUES (:reference, :event_id, :user_id, :payer_name, :payer_email, 'payment', 'paystack', :subtotal_amount, :fee_amount, :extra_charge_amount, :referral_id, :promo_code, :discount_amount, :total_amount, 'completed', :gateway_response, NOW(), NOW())
                ");
                $insertTxStmt->execute([
                    'reference' => $reference,
                    'event_id' => $eventId,
                    'user_id' => $userId,
                    'payer_name' => $name,
                    'payer_email' => $email,
                    'subtotal_amount' => $calculatedSubtotal,
                    'fee_amount' => $feeAmount,
                    'extra_charge_amount' => $extraCharge,
                    'referral_id' => $referralId,
                    'promo_code' => $promoCode,
                    'discount_amount' => $discountAmount,
                    'total_amount' => $totalAmount,
                    'gateway_response' => $finalGatewayResponse
                ]);
            }

            // Promoter referral attribution — update event_referrals tickets_sold & revenue_generated
            if ($referralId) {
                try {
                    \App\Models\EventReferral::recordSale((int)$referralId, count($createdTickets), (float)$calculatedSubtotal);
                    if (class_exists('\Ace\Application') && Application::$app->session) {
                        Application::$app->session->remove('event_ref_' . $eventId);
                    }
                    if (!headers_sent()) {
                        @setcookie('event_ref_' . $eventId, '', time() - 3600, '/');
                        @setcookie('event_ref_code_' . $eventId, '', time() - 3600, '/');
                    }
                } catch (\Throwable $e) {
                    error_log("Failed to update event referral #{$referralId}: " . $e->getMessage());
                }
            }

            $db->commit();
        } catch (\Throwable $e) {
            $db->rollBack();
            error_log("Database transaction failed in finalizeSuccessfulPayment for {$reference}: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to issue tickets: ' . $e->getMessage()
            ];
        }

        // 3. Dispatch Ticket Confirmation Email with Organization Header & QR Code to primary buyer
        $formattedDate = !empty($event['date']) ? date('D, M d, Y • g:i A', strtotime($event['date'])) : 'TBA';
        if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            try {
                \Ace\Mail::send(
                    $email,
                    'Your Tickets for ' . $event['name'],
                    'emails/ticket_confirmation',
                    [
                        'attendee_name' => $name,
                        'event_name' => $event['name'],
                        'event_date' => $formattedDate,
                        'event_location' => $event['location'] ?? 'Venue',
                        'payment_method' => 'Paystack Card / Bank Transfer',
                        'total_amount' => $totalAmount,
                        'tickets' => $createdTickets
                    ]
                );
            } catch (\Throwable $e) {
                error_log("Failed to send ticket confirmation email to {$email}: " . $e->getMessage());
            }
        }

        // 4. Dispatch Individual Pass Emails to Guest Attendees
        $guestPassesByEmail = [];
        foreach ($createdTickets as $ticket) {
            $gEmail = strtolower(trim($ticket['attendee_email'] ?? ''));
            if (!empty($gEmail) && filter_var($gEmail, FILTER_VALIDATE_EMAIL) && $gEmail !== strtolower($email)) {
                $guestPassesByEmail[$gEmail][] = $ticket;
            }
        }

        foreach ($guestPassesByEmail as $gEmail => $gTickets) {
            // Brief pause between SMTP dispatches to prevent rate-limiting or session rejection
            sleep(1);
            try {
                $gName = $gTickets[0]['attendee_name'] ?? 'Valued Guest';
                $sent = \Ace\Mail::send(
                    $gEmail,
                    'Your Admission Pass for ' . $event['name'],
                    'emails/issued_ticket',
                    [
                        'header_title' => 'Your Event Admission Pass',
                        'header_subtitle' => 'Official admission pass reserved by ' . ($name ?: 'a friend'),
                        'greeting_text' => 'An admission pass for <strong>' . e($event['name']) . '</strong> has been reserved for you by <strong>' . e($name) . '</strong>. Your scannable pass and admission details are below.',
                        'attendee_name' => $gName,
                        'event_name' => $event['name'],
                        'event_date' => $formattedDate,
                        'event_location' => $event['location'] ?? 'Venue',
                        'payment_method' => 'Paystack Card / Bank Transfer',
                        'tickets' => $gTickets
                    ]
                );
                if (!$sent) {
                    error_log("PaymentService: Guest ticket email to {$gEmail} failed: " . \Ace\Mail::getLastError());
                }
            } catch (\Throwable $e) {
                error_log("Failed to send guest ticket email to {$gEmail}: " . $e->getMessage());
            }
        }

        // Clear session pending order if set
        if (class_exists('\Ace\Application') && Application::$app->session) {
            Application::$app->session->remove('pending_order');
        }

        // Fetch fresh transaction record from DB to guarantee accurate details
        $finalTxStmt = $db->prepare("SELECT * FROM transactions WHERE reference = :ref LIMIT 1");
        $finalTxStmt->execute(['ref' => $reference]);
        $finalTx = $finalTxStmt->fetch(\PDO::FETCH_ASSOC) ?: ($tx ?: []);

        return [
            'success' => true,
            'message' => 'Payment verified, tickets issued, and platform revenue recorded.',
            'reference' => $reference,
            'event' => $event,
            'transaction' => $finalTx,
            'attendee_name' => $name,
            'attendee_email' => $email,
            'total_amount' => $totalAmount,
            'tickets' => $createdTickets
        ];
    }

    /**
     * Re-verify an individual transaction with Paystack and fulfill if paid.
     */
    public function reverifyTransaction(string $reference): array
    {
        $db = Application::$app->db->pdo;
        $reference = trim($reference);

        try {
            $paystack = new PaystackService();
            $verification = $paystack->verifyTransaction($reference);

            if (isset($verification['status']) && $verification['status'] === 'success') {
                return $this->finalizeSuccessfulPayment($reference, $verification);
            }

            // If not successful, update status in transactions table if pending
            $paystackStatus = $verification['status'] ?? 'failed';
            $db->prepare("UPDATE transactions SET status = 'failed', gateway_response = :resp, updated_at = NOW() WHERE reference = :ref AND status = 'pending'")
               ->execute([
                   'resp' => json_encode($verification),
                   'ref' => $reference
               ]);

            return [
                'success' => false,
                'status' => $paystackStatus,
                'message' => "Paystack returned status '{$paystackStatus}'. Payment was not successful."
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => 'Paystack Verification error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Background Payment Reconciler:
     * Automatically verifies and recovers any pending transactions from the last X hours.
     */
    public function reconcilePendingTransactions(int $hours = 24): array
    {
        $db = Application::$app->db->pdo;
        $stmt = $db->prepare("
            SELECT reference, event_id, payer_name, payer_email, total_amount, created_at
            FROM transactions
            WHERE status = 'pending'
              AND created_at >= NOW() - INTERVAL :hours HOUR
            ORDER BY created_at DESC
            LIMIT 50
        ");
        $stmt->execute(['hours' => $hours]);
        $pending = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];

        $recovered = [];
        $failed = [];

        foreach ($pending as $item) {
            $res = $this->reverifyTransaction($item['reference']);
            if ($res['success']) {
                $recovered[] = $item['reference'];
            } else {
                $failed[] = [
                    'reference' => $item['reference'],
                    'reason' => $res['message']
                ];
            }
        }

        return [
            'total_scanned' => count($pending),
            'recovered_count' => count($recovered),
            'recovered_references' => $recovered,
            'failed_count' => count($failed),
            'failed_details' => $failed
        ];
    }
}
