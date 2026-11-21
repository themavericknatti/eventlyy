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
    public function finalizeSuccessfulPayment(string $reference, array $gatewayData = [], bool $forceResend = false): array
    {
        if (function_exists('ignore_user_abort')) {
            @ignore_user_abort(true);
        }
        if (function_exists('set_time_limit')) {
            @set_time_limit(180);
        }

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

            // Fetch any existing order addons for this transaction
            $existingAddonsStmt = $db->prepare("
                SELECT oa.*, ea.name as addon_name, ea.description as addon_description 
                FROM order_addons oa 
                JOIN event_addons ea ON ea.id = oa.addon_id 
                WHERE oa.transaction_id = :tx_id
            ");
            $existingAddonsStmt->execute(['tx_id' => $tx['id'] ?? 0]);
            $existingAddons = $existingAddonsStmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];

            // If forceResend is requested, re-dispatch the confirmation & passes
            if ($forceResend && !empty($attendeeEmail) && !empty($existingEvent) && !empty($existingTickets)) {
                $orderData = [];
                if (!empty($tx['gateway_response'])) {
                    $parsed = json_decode($tx['gateway_response'], true);
                    $orderData = $parsed['order_data'] ?? [];
                }
                $this->dispatchOrderEmails($reference, $existingEvent, $existingTickets, $attendeeName, $attendeeEmail, (float)($tx['total_amount'] ?? 0), $orderData);
            }

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
                'tickets' => $existingTickets,
                'addons' => $existingAddons
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
        $phone = trim($orderData['attendee_phone'] ?? ($orderData['payer_phone'] ?? ($tx['payer_phone'] ?? ($gatewayData['customer']['phone'] ?? ''))));
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

            $admits = max(1, (int)($tt['admits'] ?? 1));
            $existingUnits = $admits > 1 ? (int)ceil($existingCount / $admits) : $existingCount;

            if (($existingUnits + $qty) > $maxPerPerson) {
                $remaining = max(0, $maxPerPerson - $existingUnits);
                return [
                    'success' => false,
                    'message' => "Purchase limit exceeded for \"{$tt['name']}\": maximum {$maxPerPerson} per person (you already have {$existingUnits}, requested {$qty}, allowed {$remaining} more)."
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
                    $attPhone = $attendees[$ttId][$i]['phone'] ?? ($attendees[$i]['phone'] ?? $phone);
                    if (empty($attName)) $attName = $name;
                    if (empty($attEmail)) $attEmail = $email;
                    if (empty($attPhone)) $attPhone = $phone;

                    $seatLabel = $admits > 1 ? " (Guest " . ($i + 1) . " of " . $totalPassesForTier . ")" : "";
                    $typeNameDisplay = $tt['name'] . $seatLabel;

                    $isLayawayOrder = !empty($orderData['is_layaway']);
                    $ticketStatus = $isLayawayOrder ? 'Layaway' : 'Paid';
                    $ticketStatusDetail = $isLayawayOrder 
                        ? ('Layaway Reservation | Paystack Ref: ' . $reference) 
                        : ('Paystack Ref: ' . $reference);

                    $securityPin = str_pad((string)random_int(1000, 9999), 4, '0', STR_PAD_LEFT);

                    $ticketPassFee = isset($orderData['pass_fee']) ? ($orderData['pass_fee'] ? 1 : 0) : (!empty($event['pass_fee']) ? 1 : 0);
                    $insertStmt = $db->prepare("
                        INSERT INTO tickets (id, event_id, ticket_type_id, user_id, attendee_name, attendee_email, attendee_phone, price, pass_fee, status, status_detail, security_pin, purchase_date, checked_in)
                        VALUES (:id, :event_id, :ticket_type_id, :user_id, :name, :email, :phone, :price, :pass_fee, :status, :status_detail, :security_pin, NOW(), 0)
                    ");
                    $insertStmt->execute([
                        'id' => $ticketCode,
                        'event_id' => $eventId,
                        'ticket_type_id' => $tt['id'],
                        'user_id' => $userId,
                        'name' => $attName,
                        'email' => $attEmail,
                        'phone' => $attPhone ?: null,
                        'price' => $seatPrice,
                        'pass_fee' => $ticketPassFee,
                        'status' => $ticketStatus,
                        'status_detail' => $ticketStatusDetail,
                        'security_pin' => $securityPin
                    ]);

                    $createdTickets[] = [
                        'ticket_code' => $ticketCode,
                        'attendee_name' => $attName,
                        'attendee_email' => $attEmail,
                        'attendee_phone' => $attPhone,
                        'ticket_type' => $typeNameDisplay,
                        'base_ticket_type' => $tt['name'],
                        'price' => $seatPrice,
                        'status' => $ticketStatus,
                        'checked_in' => false,
                        'security_pin' => $securityPin
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

            // If layaway, insert completed initial layaway_payments
            if (!empty($orderData['is_layaway']) && !empty($createdTickets)) {
                $depositTicketTotal = (float)($orderData['deposit_ticket_amount'] ?? 0);
                if ($depositTicketTotal <= 0) {
                    $depPaid = (float)($orderData['deposit_amount'] ?? ($orderData['total_amount'] ?? $totalAmount));
                    $feeDeduct = !empty($event['pass_fee']) ? ($calculatedSubtotal * ($platformFeePercent / 100)) : 0.00;
                    $extraDeduct = (float)site_setting('extra_charge', 100.00);
                    $depositTicketTotal = max(0, $depPaid - $feeDeduct - $extraDeduct);
                }
                $depositPerTicket = round($depositTicketTotal / count($createdTickets), 2);
                foreach ($createdTickets as $idx => $t) {
                    $lpStmt = $db->prepare("
                        INSERT INTO layaway_payments (layaway_ticket_id, event_id, user_id, payer_email, amount, payment_method, reference, status, gateway_response, created_at)
                        VALUES (:ticket_id, :event_id, :user_id, :email, :amount, 'paystack', :ref, 'completed', :resp, NOW())
                    ");
                    $lpStmt->execute([
                        'ticket_id' => $t['ticket_code'],
                        'event_id' => $eventId,
                        'user_id' => $userId,
                        'email' => $email,
                        'amount' => $depositPerTicket,
                        'ref' => $reference . '-P' . ($idx + 1),
                        'resp' => json_encode([
                            'note' => 'Initial checkout deposit on Paystack (Charges added at checkout)', 
                            'order_ref' => $reference,
                            'total_charged_at_checkout' => $totalAmount,
                            'fee_amount' => $feeAmount,
                            'extra_charge_amount' => $extraCharge
                        ])
                    ]);
                }
            }

            // If event credits were applied to partially cover this order, finalize deduction now
            if (!empty($orderData['applied_credits_list']) && is_array($orderData['applied_credits_list'])) {
                try {
                    $layawayService = new \App\Services\LayawayService();
                    foreach ($orderData['applied_credits_list'] as $crItem) {
                        $layawayService->applyCredit((int)$crItem['credit_id'], $email, (float)$crItem['deduct']);
                    }
                } catch (\Throwable $e) {
                    error_log("Failed to finalize applied credits on payment completion: " . $e->getMessage());
                }
            }

            $txSubtotal = (!empty($orderData['is_layaway']) && !empty($orderData['deposit_ticket_amount']))
                ? (float)$orderData['deposit_ticket_amount']
                : $calculatedSubtotal;

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
                        payer_phone = COALESCE(:payer_phone, payer_phone),
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
                    'payer_phone' => $phone ?: null,
                    'subtotal_amount' => $txSubtotal,
                    'fee_amount' => $feeAmount,
                    'extra_charge_amount' => $extraCharge,
                    'referral_id' => $referralId,
                    'total_amount' => $totalAmount,
                    'gateway_response' => $finalGatewayResponse,
                    'reference' => $reference
                ]);
            } else {
                $insertTxStmt = $db->prepare("
                    INSERT INTO transactions (reference, event_id, user_id, payer_name, payer_email, payer_phone, type, payment_method, subtotal_amount, fee_amount, extra_charge_amount, referral_id, promo_code, discount_amount, total_amount, status, gateway_response, created_at, updated_at)
                    VALUES (:reference, :event_id, :user_id, :payer_name, :payer_email, :payer_phone, 'payment', 'paystack', :subtotal_amount, :fee_amount, :extra_charge_amount, :referral_id, :promo_code, :discount_amount, :total_amount, 'completed', :gateway_response, NOW(), NOW())
                ");
                $insertTxStmt->execute([
                    'reference' => $reference,
                    'event_id' => $eventId,
                    'user_id' => $userId,
                    'payer_name' => $name,
                    'payer_email' => $email,
                    'payer_phone' => $phone ?: null,
                    'subtotal_amount' => $txSubtotal,
                    'fee_amount' => $feeAmount,
                    'extra_charge_amount' => $extraCharge,
                    'referral_id' => $referralId,
                    'promo_code' => $promoCode,
                    'discount_amount' => $discountAmount,
                    'total_amount' => $totalAmount,
                    'gateway_response' => $finalGatewayResponse
                ]);
            }

            // Attribute promo code or trivia reward code usage
            if (!empty($promoCode)) {
                try {
                    $tSubStmt = $db->prepare("
                        SELECT s.id 
                        FROM event_trivia_submissions s
                        INNER JOIN event_trivia t ON t.id = s.trivia_id
                        WHERE t.event_id = :event_id 
                        AND UPPER(t.reward_promo_code) = :code 
                        AND s.is_winner = 1 
                        AND s.redeemed_at IS NULL
                        AND (
                            (:uid IS NOT NULL AND s.user_id = :uid2)
                            OR (:email_check != '' AND LOWER(s.player_email) = :email_match)
                        )
                        ORDER BY s.id DESC LIMIT 1
                    ");
                    $tSubStmt->execute([
                        'event_id' => $eventId,
                        'code' => $promoCode,
                        'uid' => $userId,
                        'uid2' => $userId,
                        'email_check' => strtolower(trim($email)),
                        'email_match' => strtolower(trim($email))
                    ]);
                    $tSubId = $tSubStmt->fetchColumn();

                    $txIdStmt = $db->prepare("SELECT id FROM transactions WHERE reference = :ref LIMIT 1");
                    $txIdStmt->execute(['ref' => $reference]);
                    $finalDbTxId = $txIdStmt->fetchColumn() ?: null;

                    if ($tSubId) {
                        $db->prepare("UPDATE event_trivia_submissions SET redeemed_at = NOW(), transaction_id = :tx_id WHERE id = :sub_id")
                           ->execute(['tx_id' => $finalDbTxId, 'sub_id' => $tSubId]);
                    } else {
                        $promoIncStmt = $db->prepare("UPDATE promo_codes SET used_count = used_count + 1 WHERE code = :code");
                        $promoIncStmt->execute(['code' => $promoCode]);
                    }
                } catch (\Throwable $pe) {
                    error_log("Failed to record promo/trivia usage for {$promoCode}: " . $pe->getMessage());
                }
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

            // Record purchased Add-Ons & Merchandise
            $selectedAddons = $orderData['selected_addons'] ?? [];
            if (!empty($selectedAddons) && is_array($selectedAddons)) {
                $txIdStmt = $db->prepare("SELECT id FROM transactions WHERE reference = :ref LIMIT 1");
                $txIdStmt->execute(['ref' => $reference]);
                $dbTxId = $txIdStmt->fetchColumn() ?: null;

                foreach ($selectedAddons as $sa) {
                    $addonId = (int)($sa['addon_id'] ?? 0);
                    $addonQty = (int)($sa['quantity'] ?? 0);
                    $addonUnitPrice = (float)($sa['unit_price'] ?? 0);
                    $addonTotalPrice = (float)($sa['total_price'] ?? ($addonUnitPrice * $addonQty));

                    if ($addonId > 0 && $addonQty > 0) {
                        $oaStmt = $db->prepare("
                            INSERT INTO order_addons (transaction_id, event_id, addon_id, user_id, buyer_name, buyer_email, quantity, unit_price, total_price, redemption_status, created_at)
                            VALUES (:tx_id, :event_id, :addon_id, :user_id, :buyer_name, :buyer_email, :quantity, :unit_price, :total_price, 'pending', NOW())
                        ");
                        $oaStmt->execute([
                            'tx_id' => $dbTxId,
                            'event_id' => $eventId,
                            'addon_id' => $addonId,
                            'user_id' => $userId,
                            'buyer_name' => $name,
                            'buyer_email' => $email,
                            'quantity' => $addonQty,
                            'unit_price' => $addonUnitPrice,
                            'total_price' => $addonTotalPrice
                        ]);

                        // Increment sold_count in event_addons
                        $db->prepare("UPDATE event_addons SET sold_count = sold_count + :qty WHERE id = :addon_id")
                           ->execute(['qty' => $addonQty, 'addon_id' => $addonId]);
                    }
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

        // 3. Dispatch Ticket / Layaway Confirmation and Individual Guest Passes Resiliently
        $this->dispatchOrderEmails($reference, $event, $createdTickets, $name, $email, $totalAmount, $orderData);

        // Clear session pending order if set
        if (class_exists('\Ace\Application') && Application::$app->session) {
            Application::$app->session->remove('pending_order');
        }

        // Fetch fresh transaction record from DB to guarantee accurate details
        $finalTxStmt = $db->prepare("SELECT * FROM transactions WHERE reference = :ref LIMIT 1");
        $finalTxStmt->execute(['ref' => $reference]);
        $finalTx = $finalTxStmt->fetch(\PDO::FETCH_ASSOC) ?: ($tx ?: []);

        // Fetch purchased add-ons for return
        $purchasedAddonsStmt = $db->prepare("
            SELECT oa.*, ea.name as addon_name, ea.description as addon_description 
            FROM order_addons oa 
            JOIN event_addons ea ON ea.id = oa.addon_id 
            WHERE oa.transaction_id = :tx_id
        ");
        $purchasedAddonsStmt->execute(['tx_id' => $finalTx['id'] ?? 0]);
        $purchasedAddons = $purchasedAddonsStmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];

        return [
            'success' => true,
            'message' => 'Payment verified, tickets issued, and platform revenue recorded.',
            'reference' => $reference,
            'event' => $event,
            'transaction' => $finalTx,
            'attendee_name' => $name,
            'attendee_email' => $email,
            'total_amount' => $totalAmount,
            'tickets' => $createdTickets,
            'addons' => $purchasedAddons
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
                return $this->finalizeSuccessfulPayment($reference, $verification, true);
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
            $errorMessage = $e->getMessage();

            // When Paystack explicitly rejects the reference (e.g. 'Transaction reference not found',
            // 'Reference not valid', or 400 error), update the transaction status to 'failed'
            // so that phantom/invalid references do not stay stuck in 'pending' indefinitely.
            try {
                $db->prepare("UPDATE transactions SET status = 'failed', gateway_response = :resp, updated_at = NOW() WHERE reference = :ref AND status = 'pending'")
                   ->execute([
                       'resp' => json_encode([
                           'error' => $errorMessage,
                           'failed_at' => date('Y-m-d H:i:s'),
                           'verified_by' => 'gateway_reverify'
                       ]),
                       'ref' => $reference
                   ]);
            } catch (\Throwable $dbErr) {
                error_log("PaymentService: Failed to mark transaction {$reference} as failed: " . $dbErr->getMessage());
            }

            return [
                'success' => false,
                'status' => 'failed',
                'message' => 'Paystack Verification: ' . $errorMessage . '. Transaction marked as failed.'
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

    /**
     * Dispatch ticket confirmation and individual guest pass emails resiliently.
     * Uses a single persistent SMTP connection (Keep-Alive) with automated retry and fallback.
     */
    public function dispatchOrderEmails(
        string $reference,
        array $event,
        array $tickets,
        string $buyerName,
        string $buyerEmail,
        float $totalAmount,
        array $orderData = []
    ): array {
        if (function_exists('ignore_user_abort')) {
            @ignore_user_abort(true);
        }
        if (function_exists('set_time_limit')) {
            @set_time_limit(180);
        }

        $formattedDate = !empty($event['date']) ? date('D, M d, Y • g:i A', strtotime($event['date'])) : 'TBA';
        $messagesToSend = [];
        $isLayaway = !empty($orderData['is_layaway']);

        // Check if any ticket has 'Layaway' status if orderData wasn't passed
        if (!$isLayaway && !empty($tickets)) {
            foreach ($tickets as $t) {
                if (($t['status'] ?? '') === 'Layaway') {
                    $isLayaway = true;
                    break;
                }
            }
        }

        // 1. Primary Buyer Confirmation Message
        if (!empty($buyerEmail) && filter_var($buyerEmail, FILTER_VALIDATE_EMAIL)) {
            if ($isLayaway) {
                $depPaid = (float)($orderData['deposit_amount'] ?? $totalAmount);
                $fullTotal = (float)($orderData['total_full_amount'] ?? $totalAmount);
                $remBalance = max(0, $fullTotal - $depPaid);
                $minPct = (int)($event['partial_payment_min_percent'] ?? 25);

                $messagesToSend[] = [
                    'to' => $buyerEmail,
                    'name' => $buyerName,
                    'subject' => 'Your Layaway Reservation Confirmed - ' . ($event['name'] ?? 'Event'),
                    'view' => 'emails/layaway_confirmation',
                    'params' => [
                        'attendee_name' => $buyerName,
                        'event_name' => $event['name'] ?? 'Event',
                        'event_date' => $formattedDate,
                        'event_location' => $event['location'] ?? 'Venue',
                        'ticket_type' => count($tickets) . ' Ticket(s) Reserved',
                        'total_price' => $fullTotal,
                        'deposit_paid' => $depPaid,
                        'remaining_balance' => $remBalance,
                        'min_percent' => $minPct,
                        'ticket_code' => $tickets[0]['ticket_code'] ?? ($tickets[0]['id'] ?? $reference),
                        'tickets' => $tickets,
                        'addons' => $orderData['selected_addons'] ?? []
                    ]
                ];
            } else {
                $messagesToSend[] = [
                    'to' => $buyerEmail,
                    'name' => $buyerName,
                    'subject' => 'Your Tickets for ' . ($event['name'] ?? 'Event'),
                    'view' => 'emails/ticket_confirmation',
                    'params' => [
                        'attendee_name' => $buyerName,
                        'event_name' => $event['name'] ?? 'Event',
                        'event_date' => $formattedDate,
                        'event_location' => $event['location'] ?? 'Venue',
                        'payment_method' => 'Paystack Card / Bank Transfer',
                        'total_amount' => $totalAmount,
                        'tickets' => $tickets,
                        'addons' => $orderData['selected_addons'] ?? []
                    ]
                ];
            }
        }

        // 2. Individual Guest Passes
        $guestPassesByEmail = [];
        foreach ($tickets as $ticket) {
            $gEmail = strtolower(trim($ticket['attendee_email'] ?? ''));
            if (!empty($gEmail) && filter_var($gEmail, FILTER_VALIDATE_EMAIL) && $gEmail !== strtolower($buyerEmail)) {
                $guestPassesByEmail[$gEmail][] = $ticket;
            }
        }

        foreach ($guestPassesByEmail as $gEmail => $gTickets) {
            $gName = $gTickets[0]['attendee_name'] ?? 'Valued Guest';
            $messagesToSend[] = [
                'to' => $gEmail,
                'name' => $gName,
                'subject' => 'Your Admission Pass for ' . ($event['name'] ?? 'Event'),
                'view' => 'emails/issued_ticket',
                'params' => [
                    'header_title' => 'Your Event Admission Pass',
                    'header_subtitle' => 'Official admission pass reserved by ' . ($buyerName ?: 'a friend'),
                    'greeting_text' => 'An admission pass for <strong>' . e($event['name'] ?? 'your event') . '</strong> has been reserved for you by <strong>' . e($buyerName) . '</strong>. Your scannable pass and admission details are below.',
                    'attendee_name' => $gName,
                    'event_name' => $event['name'] ?? 'Event',
                    'event_date' => $formattedDate,
                    'event_location' => $event['location'] ?? 'Venue',
                    'payment_method' => 'Paystack Card / Bank Transfer',
                    'tickets' => $gTickets
                ]
            ];
        }

        // Queue all messages for background dispatch, with fallback to direct send
        try {
            \Ace\EmailQueue::queueMessages($messagesToSend, $reference, 5);
            \Ace\EmailQueue::processAfterResponse();
            $result = [
                'total' => count($messagesToSend),
                'sent' => count($messagesToSend),
                'failed' => 0,
                'queued' => true
            ];
        } catch (\Throwable $qErr) {
            // Direct send fallback if queue table is not available
            error_log("EmailQueue fallback in dispatchOrderEmails for {$reference}: " . $qErr->getMessage());
            $result = \Ace\Mail::sendMessages($messagesToSend);
        }

        if (isset($result['failed']) && $result['failed'] > 0) {
            error_log("PaymentService: Notice: Some emails encountered issues for ref {$reference}: " . json_encode($result));
        }

        return $result;
    }

    /**
     * Resend all ticket passes for an existing order by reference or ticket code.
     * Supports paid events (Paystack), free events (direct EVT-), layaway orders, and ticket code lookups.
     */
    public function resendOrderTickets(string $reference, ?string $targetEmail = null): array
    {
        $db = Application::$app->db->pdo;
        $reference = trim($reference);

        if (empty($reference)) {
            return ['success' => false, 'message' => 'Please provide a valid order reference or ticket code.'];
        }

        $tx = null;
        $tickets = [];

        // 1. Try finding by transaction reference directly
        $stmt = $db->prepare("SELECT * FROM transactions WHERE reference = :reference LIMIT 1");
        $stmt->execute(['reference' => $reference]);
        $tx = $stmt->fetch(\PDO::FETCH_ASSOC);

        // 2. If not found in transactions, check if the reference is a ticket code (e.g. EV-XXXXXXXX)
        if (!$tx) {
            $tCodeStmt = $db->prepare("
                SELECT t.*, tt.name as ticket_type, t.id as ticket_code 
                FROM tickets t 
                LEFT JOIN ticket_types tt ON t.ticket_type_id = tt.id 
                WHERE t.id = :ref_id OR t.status_detail LIKE :ref_like
                LIMIT 1
            ");
            $tCodeStmt->execute([
                'ref_id' => $reference,
                'ref_like' => '%' . $reference . '%'
            ]);
            $matchedTicket = $tCodeStmt->fetch(\PDO::FETCH_ASSOC);

            if ($matchedTicket) {
                // Try to extract transaction reference from status_detail (e.g. "Ref: EVT-..." or "Paystack Ref: ...")
                if (!empty($matchedTicket['status_detail']) && preg_match('/(?:Ref:\s*)([A-Za-z0-9\-_]+)/i', $matchedTicket['status_detail'], $mRef)) {
                    $extractedRef = trim($mRef[1]);
                    $stmt2 = $db->prepare("SELECT * FROM transactions WHERE reference = :ref LIMIT 1");
                    $stmt2->execute(['ref' => $extractedRef]);
                    $tx = $stmt2->fetch(\PDO::FETCH_ASSOC);
                }

                // If still no transaction record, synthesize a transaction context from ticket
                if (!$tx) {
                    $tx = [
                        'reference' => $reference,
                        'event_id' => $matchedTicket['event_id'],
                        'payer_name' => $matchedTicket['attendee_name'],
                        'payer_email' => $matchedTicket['attendee_email'],
                        'total_amount' => (float)$matchedTicket['price'],
                        'payment_method' => 'FREE',
                        'gateway_response' => null
                    ];
                }
            }
        }

        if (!$tx) {
            return ['success' => false, 'message' => 'Order reference not found. Please verify your reference number.'];
        }

        // 3. Find all tickets belonging to this order
        $resolvedRef = $tx['reference'] ?? $reference;
        $existingTicketsStmt = $db->prepare("
            SELECT t.*, tt.name as ticket_type, t.id as ticket_code 
            FROM tickets t 
            LEFT JOIN ticket_types tt ON t.ticket_type_id = tt.id 
            WHERE t.status_detail LIKE :ref1 OR t.status_detail LIKE :ref2 OR t.id = :tx_id
            ORDER BY t.id ASC
        ");
        $existingTicketsStmt->execute([
            'ref1' => '%' . $resolvedRef . '%',
            'ref2' => '%' . $reference . '%',
            'tx_id' => $reference
        ]);
        $tickets = $existingTicketsStmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];

        // 4. Fallback for older free tickets without status_detail: match by event_id and buyer email around transaction time
        if (empty($tickets) && !empty($tx['event_id']) && !empty($tx['payer_email'])) {
            $fallbackTStmt = $db->prepare("
                SELECT t.*, tt.name as ticket_type, t.id as ticket_code 
                FROM tickets t 
                LEFT JOIN ticket_types tt ON t.ticket_type_id = tt.id 
                WHERE t.event_id = :event_id 
                  AND LOWER(t.attendee_email) = :email
                  AND (t.status_detail IS NULL OR t.status_detail = '' OR t.status_detail LIKE :ref_like)
                ORDER BY t.id ASC
            ");
            $fallbackTStmt->execute([
                'event_id' => $tx['event_id'],
                'email' => strtolower(trim($tx['payer_email'])),
                'ref_like' => '%' . $resolvedRef . '%'
            ]);
            $tickets = $fallbackTStmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];

            // If found via fallback, link them to the reference so future lookups are instantaneous
            if (!empty($tickets)) {
                $ticketIds = array_column($tickets, 'ticket_code');
                $placeholders = implode(',', array_fill(0, count($ticketIds), '?'));
                $statusDetailText = 'Linked Ref: ' . $resolvedRef;
                $updateDetailStmt = $db->prepare("UPDATE tickets SET status_detail = ? WHERE id IN ($placeholders)");
                $updateDetailStmt->execute(array_merge([$statusDetailText], $ticketIds));
            }
        }

        if (empty($tickets)) {
            return ['success' => false, 'message' => 'No tickets found for this order.'];
        }

        $eventStmt = $db->prepare("SELECT * FROM events WHERE id = :event_id LIMIT 1");
        $eventStmt->execute(['event_id' => $tx['event_id'] ?? ($tickets[0]['event_id'] ?? 0)]);
        $event = $eventStmt->fetch(\PDO::FETCH_ASSOC) ?: [];

        $buyerName = !empty($tx['payer_name']) ? $tx['payer_name'] : ($tickets[0]['attendee_name'] ?? 'Attendee');
        $buyerEmail = !empty($targetEmail) ? trim($targetEmail) : (!empty($tx['payer_email']) ? $tx['payer_email'] : ($tickets[0]['attendee_email'] ?? ''));

        $orderData = [];
        if (!empty($tx['gateway_response'])) {
            $parsed = json_decode($tx['gateway_response'], true);
            $orderData = $parsed['order_data'] ?? [];
        }

        $dispatch = $this->dispatchOrderEmails($resolvedRef, $event, $tickets, $buyerName, $buyerEmail, (float)($tx['total_amount'] ?? 0), $orderData);

        return [
            'success' => true,
            'sent' => $dispatch['sent'] ?? count($tickets),
            'failed' => $dispatch['failed'] ?? 0,
            'message' => 'Your admission passes have been re-sent to ' . $buyerEmail . '! Please check your inbox and spam folder.'
        ];
    }
}
