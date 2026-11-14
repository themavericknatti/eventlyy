<?php

namespace App\Services;

use Ace\Application;
use Ace\PaystackService;
use App\Models\Ticket;
use App\Models\LayawayPayment;
use App\Models\LayawayCredit;
use Exception;

class LayawayService
{
    /**
     * Create initial Layaway order for tickets.
     */
    public function createLayaway(
        array $event,
        string $name,
        string $email,
        array $qtyMap,
        array $ticketTypes,
        float $depositAmount,
        ?int $userId = null,
        ?string $promoCode = null,
        float $discountAmount = 0.00,
        ?string $refCode = null,
        array $attendees = [],
        float $fee = 0.00,
        float $extraCharge = 0.00
    ): array {
        $db = Application::$app->db->pdo;
        $eventId = (int)$event['id'];

        if (empty($event['allow_partial_payment'])) {
            return ['success' => false, 'message' => 'This event does not accept partial/installment payments.'];
        }

        $minPercent = (int)($event['partial_payment_min_percent'] ?? 25);

        // Fetch ticket types indexed by id
        $ticketTypesById = [];
        foreach ($ticketTypes as $tt) {
            $ticketTypesById[$tt['id']] = $tt;
        }

        // Calculate total price of selected tickets
        $totalTicketPrice = 0.00;
        $totalPassCount = 0;
        foreach ($qtyMap as $ttId => $qty) {
            $qty = (int)$qty;
            if ($qty > 0 && isset($ticketTypesById[$ttId])) {
                $totalTicketPrice += (float)$ticketTypesById[$ttId]['price'] * $qty;
                $admits = max(1, (int)($ticketTypesById[$ttId]['admits'] ?? 1));
                $totalPassCount += $qty * $admits;
            }
        }

        if ($totalTicketPrice <= 0) {
            return ['success' => false, 'message' => 'No valid paid tickets selected for layaway plan.'];
        }

        $minRequiredDeposit = round($totalTicketPrice * ($minPercent / 100), 2);
        if ($depositAmount < $minRequiredDeposit) {
            return [
                'success' => false, 
                'message' => "Minimum deposit required is " . format_price($minRequiredDeposit) . " ({$minPercent}%)."
            ];
        }

        // Pass fee & extra charge calculations for deposit
        $platformFeePercent = (float)site_setting('platform_fee', 5);
        $passFee = !empty($event['pass_fee']);
        if ($fee <= 0 && $passFee) {
            $fee = round($totalTicketPrice * ($platformFeePercent / 100), 2);
        }
        if ($extraCharge <= 0 && $totalTicketPrice > 0) {
            $extraCharge = (float)site_setting('extra_charge', 100.00);
        }
        $totalDepositCharged = $depositAmount + $fee + $extraCharge;

        $createdTickets = [];
        $reference = 'LAY-' . $eventId . '-' . strtoupper(bin2hex(random_bytes(5)));

        $db->beginTransaction();
        try {
            foreach ($qtyMap as $ttId => $qty) {
                $qty = (int)$qty;
                if ($qty <= 0 || !isset($ticketTypesById[$ttId])) continue;

                $tt = $ticketTypesById[$ttId];
                $tierUnitPrice = (float)$tt['price'];
                $tierTotalPrice = $tierUnitPrice * $qty;
                $admits = max(1, (int)($tt['admits'] ?? 1));
                $totalPassesForTier = $qty * $admits;
                $seatPrice = round($tierTotalPrice / $totalPassesForTier, 2);

                for ($i = 0; $i < $totalPassesForTier; $i++) {
                    $ticketCode = 'LAY-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));

                    $attName = $attendees[$ttId][$i]['name'] ?? ($attendees[$i]['name'] ?? $name);
                    $attEmail = $attendees[$ttId][$i]['email'] ?? ($attendees[$i]['email'] ?? $email);
                    if (empty($attName)) $attName = $name;
                    if (empty($attEmail)) $attEmail = $email;

                    $insertStmt = $db->prepare("
                        INSERT INTO tickets (id, event_id, ticket_type_id, user_id, attendee_name, attendee_email, price, status, status_detail, purchase_date, checked_in)
                        VALUES (:id, :event_id, :ticket_type_id, :user_id, :name, :email, :price, 'Layaway', :status_detail, NOW(), 0)
                    ");
                    $statusDetail = "Layaway Reservation - Plan Total: " . format_price($seatPrice) . " | Ref: " . $reference;
                    $insertStmt->execute([
                        'id' => $ticketCode,
                        'event_id' => $eventId,
                        'ticket_type_id' => $tt['id'],
                        'user_id' => $userId,
                        'name' => $attName,
                        'email' => $attEmail,
                        'price' => $seatPrice,
                        'status_detail' => $statusDetail
                    ]);

                    $createdTickets[] = [
                        'ticket_code' => $ticketCode,
                        'attendee_name' => $attName,
                        'attendee_email' => $attEmail,
                        'ticket_type' => $tt['name'],
                        'price' => $seatPrice,
                        'status' => 'Layaway'
                    ];
                }
            }

            if (empty($createdTickets)) {
                $db->rollBack();
                return ['success' => false, 'message' => 'No tickets could be reserved.'];
            }

            // Distribute deposit proportionally across tickets
            $depositPerTicket = round($depositAmount / count($createdTickets), 2);
            foreach ($createdTickets as $idx => $t) {
                $paymentRef = $reference . '-P' . ($idx + 1);
                $payStmt = $db->prepare("
                    INSERT INTO layaway_payments (layaway_ticket_id, event_id, user_id, payer_email, amount, payment_method, reference, status, gateway_response, created_at)
                    VALUES (:ticket_id, :event_id, :user_id, :email, :amount, 'deposit', :ref, 'completed', :gateway, NOW())
                ");
                $payStmt->execute([
                    'ticket_id' => $t['ticket_code'],
                    'event_id' => $eventId,
                    'user_id' => $userId,
                    'email' => $email,
                    'amount' => $depositPerTicket,
                    'ref' => $paymentRef,
                    'gateway' => json_encode(['note' => 'Initial checkout deposit'])
                ]);
            }

            // Record transaction for accounting
            $trxStmt = $db->prepare("
                INSERT INTO transactions (reference, event_id, user_id, payer_name, payer_email, type, payment_method, subtotal_amount, fee_amount, extra_charge_amount, total_amount, status, created_at)
                VALUES (:reference, :event_id, :user_id, :payer_name, :payer_email, 'payment', 'layaway_deposit', :subtotal_amount, :fee_amount, :extra_charge_amount, :total_amount, 'completed', NOW())
            ");
            $trxStmt->execute([
                'reference' => $reference,
                'event_id' => $eventId,
                'user_id' => $userId,
                'payer_name' => $name,
                'payer_email' => $email,
                'subtotal_amount' => $depositAmount,
                'fee_amount' => $fee,
                'extra_charge_amount' => $extraCharge,
                'total_amount' => $totalDepositCharged
            ]);

            $db->commit();
        } catch (\Throwable $e) {
            $db->rollBack();
            error_log("Failed to create layaway plan: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to initialize layaway plan: ' . $e->getMessage()];
        }

        $remainingBalance = max(0, $totalTicketPrice - $depositAmount);

        // Send Layaway Confirmation Email
        $formattedDate = !empty($event['date']) ? date('D, M d, Y • g:i A', strtotime($event['date'])) : 'TBA';
        try {
            \Ace\Mail::send(
                $email,
                'Your Layaway Reservation Confirmed - ' . $event['name'],
                'emails/layaway_confirmation',
                [
                    'attendee_name' => $name,
                    'event_name' => $event['name'],
                    'event_date' => $formattedDate,
                    'event_location' => $event['location'] ?? 'Venue TBA',
                    'ticket_type' => count($createdTickets) . ' Ticket(s) Reserved',
                    'total_price' => $totalTicketPrice,
                    'deposit_paid' => $depositAmount,
                    'remaining_balance' => $remainingBalance,
                    'min_percent' => $minPercent,
                    'ticket_code' => $createdTickets[0]['ticket_code'] ?? $reference,
                    'tickets' => $createdTickets
                ]
            );
        } catch (\Throwable $e) {
            error_log("Failed to send layaway confirmation email: " . $e->getMessage());
        }

        return [
            'success' => true,
            'message' => 'Layaway reservation successful! Your deposit has been recorded.',
            'reference' => $reference,
            'deposit_paid' => $depositAmount,
            'remaining_balance' => $remainingBalance,
            'tickets' => $createdTickets
        ];
    }

    /**
     * Initialize Paystack transaction for an installment payment.
     */
    public function initializeInstallmentPayment(string $ticketId, float $amount, string $email): array
    {
        $db = Application::$app->db->pdo;

        // Fetch ticket with event info
        $stmt = $db->prepare("
            SELECT t.*, e.name as event_name, e.date as event_date, e.status as event_status
            FROM tickets t
            INNER JOIN events e ON t.event_id = e.id
            WHERE t.id = :id LIMIT 1
        ");
        $stmt->execute(['id' => $ticketId]);
        $ticket = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$ticket) {
            return ['success' => false, 'message' => 'Ticket reservation not found.'];
        }

        if ($ticket['status'] !== 'Layaway') {
            return ['success' => false, 'message' => 'This ticket is not active on a layaway payment plan. Current status: ' . $ticket['status']];
        }

        $eventDate = strtotime($ticket['event_date']);
        if ($eventDate && time() > $eventDate) {
            return ['success' => false, 'message' => 'Event has already commenced. Layaway payments can no longer be accepted.'];
        }

        $totalPrice = (float)$ticket['price'];
        $totalPaid = LayawayPayment::getTotalPaid($ticketId);
        $remaining = max(0, $totalPrice - $totalPaid);

        if ($remaining <= 0) {
            // Already paid up
            $this->markTicketAsFullyPaid($ticketId);
            return ['success' => false, 'message' => 'This ticket is already fully paid!'];
        }

        if ($amount <= 0) {
            return ['success' => false, 'message' => 'Payment amount must be greater than zero.'];
        }

        // Cap amount at remaining balance
        $paymentAmount = min($amount, $remaining);

        // Generate unique Paystack reference
        $reference = 'INST-' . substr($ticketId, -6) . '-' . strtoupper(bin2hex(random_bytes(4)));

        $appUrl = rtrim($_ENV['APP_URL'] ?? 'http://localhost', '/');
        $callbackUrl = $appUrl . '/layaway/callback';

        $currentUser = Application::$app->user;
        $userId = $currentUser ? $currentUser->id : ($ticket['user_id'] ?? null);

        // Record pending installment payment
        $insertStmt = $db->prepare("
            INSERT INTO layaway_payments (layaway_ticket_id, event_id, user_id, payer_email, amount, payment_method, reference, status, created_at)
            VALUES (:ticket_id, :event_id, :user_id, :email, :amount, 'paystack', :reference, 'pending', NOW())
        ");
        $insertStmt->execute([
            'ticket_id' => $ticketId,
            'event_id' => $ticket['event_id'],
            'user_id' => $userId,
            'email' => $email,
            'amount' => $paymentAmount,
            'reference' => $reference
        ]);

        try {
            $paystack = new PaystackService();
            $meta = [
                'type' => 'layaway_installment',
                'ticket_id' => $ticketId,
                'event_id' => $ticket['event_id'],
                'user_id' => $userId,
                'email' => $email,
                'installment_amount' => $paymentAmount
            ];
            $paystackData = $paystack->initializeTransaction($email, $paymentAmount, $callbackUrl, $reference, $meta);

            return [
                'success' => true,
                'authorization_url' => $paystackData['authorization_url'],
                'reference' => $reference,
                'amount' => $paymentAmount
            ];
        } catch (\Throwable $e) {
            error_log("Failed to initialize installment on Paystack: " . $e->getMessage());
            return ['success' => false, 'message' => 'Payment initialization failed: ' . $e->getMessage()];
        }
    }

    /**
     * Finalize installment payment after Paystack callback or webhook.
     */
    public function finalizeInstallmentPayment(string $reference): array
    {
        $db = Application::$app->db->pdo;
        $reference = trim($reference);

        $stmt = $db->prepare("SELECT * FROM layaway_payments WHERE reference = :ref LIMIT 1");
        $stmt->execute(['ref' => $reference]);
        $payment = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$payment) {
            return ['success' => false, 'message' => 'Installment payment record not found for reference ' . $reference];
        }

        if ($payment['status'] === 'completed') {
            return [
                'success' => true,
                'already_completed' => true,
                'message' => 'Installment payment was already verified and recorded.',
                'ticket_id' => $payment['layaway_ticket_id']
            ];
        }

        // Verify with Paystack
        try {
            $paystack = new PaystackService();
            $verification = $paystack->verifyTransaction($reference);
            if (!isset($verification['status']) || $verification['status'] !== 'success') {
                return ['success' => false, 'message' => 'Paystack returned status: ' . ($verification['status'] ?? 'failed')];
            }
        } catch (\Throwable $e) {
            error_log("Paystack verification error for installment {$reference}: " . $e->getMessage());
            return ['success' => false, 'message' => 'Could not verify payment with gateway: ' . $e->getMessage()];
        }

        $ticketId = $payment['layaway_ticket_id'];

        $db->beginTransaction();
        try {
            // Update payment record to completed
            $updateStmt = $db->prepare("
                UPDATE layaway_payments 
                SET status = 'completed', gateway_response = :resp, updated_at = NOW() 
                WHERE reference = :ref
            ");
            $updateStmt->execute([
                'resp' => json_encode($verification),
                'ref' => $reference
            ]);

            // Record transaction in transactions table
            $trxStmt = $db->prepare("
                INSERT INTO transactions (reference, event_id, user_id, payer_name, payer_email, type, payment_method, subtotal_amount, total_amount, status, gateway_response, created_at)
                VALUES (:reference, :event_id, :user_id, 'Attendee', :email, 'payment', 'paystack_layaway', :amount, :total_amount, 'completed', :resp, NOW())
            ");
            $trxStmt->execute([
                'reference' => $reference,
                'event_id' => $payment['event_id'],
                'user_id' => $payment['user_id'],
                'email' => $payment['payer_email'],
                'amount' => $payment['amount'],
                'total_amount' => $payment['amount'],
                'resp' => json_encode($verification)
            ]);

            $db->commit();
        } catch (\Throwable $e) {
            $db->rollBack();
            error_log("Failed to complete layaway payment: " . $e->getMessage());
            return ['success' => false, 'message' => 'Database error recording payment.'];
        }

        // Check overall ticket progress
        $progress = $this->getLayawayProgress($ticketId);

        // Fetch ticket & event info for emails
        $tStmt = $db->prepare("
            SELECT t.*, e.name as event_name, e.date as event_date, e.location as event_location, tt.name as ticket_type_name
            FROM tickets t
            INNER JOIN events e ON t.event_id = e.id
            INNER JOIN ticket_types tt ON t.ticket_type_id = tt.id
            WHERE t.id = :id LIMIT 1
        ");
        $tStmt->execute(['id' => $ticketId]);
        $ticket = $tStmt->fetch(\PDO::FETCH_ASSOC);

        $formattedDate = !empty($ticket['event_date']) ? date('D, M d, Y • g:i A', strtotime($ticket['event_date'])) : 'TBA';

        if ($progress['is_complete']) {
            // Upgrade ticket status to Paid!
            $this->markTicketAsFullyPaid($ticketId);

            // Send layaway_completed email
            try {
                \Ace\Mail::send(
                    $ticket['attendee_email'],
                    '🎉 Your Ticket is Fully Paid & Confirmed! - ' . $ticket['event_name'],
                    'emails/layaway_completed',
                    [
                        'attendee_name' => $ticket['attendee_name'],
                        'event_name' => $ticket['event_name'],
                        'event_date' => $formattedDate,
                        'event_location' => $ticket['event_location'] ?? 'Venue TBA',
                        'ticket_code' => $ticket['id'],
                        'ticket_type' => $ticket['ticket_type_name'],
                        'total_amount' => (float)$ticket['price']
                    ]
                );
            } catch (\Throwable $e) {
                error_log("Failed to send layaway completed email: " . $e->getMessage());
            }

            return [
                'success' => true,
                'is_fully_paid' => true,
                'ticket_id' => $ticketId,
                'message' => 'Congratulations! Your layaway plan is 100% complete. Your official admission pass is now active!'
            ];
        }

        // Installment received, not yet complete
        try {
            \Ace\Mail::send(
                $ticket['attendee_email'],
                'Payment Received on Layaway Plan - ' . $ticket['event_name'],
                'emails/layaway_payment_received',
                [
                    'attendee_name' => $ticket['attendee_name'],
                    'event_name' => $ticket['event_name'],
                    'event_date' => $formattedDate,
                    'amount_paid' => (float)$payment['amount'],
                    'total_paid' => $progress['total_paid'],
                    'total_price' => $progress['total_price'],
                    'remaining_balance' => $progress['remaining'],
                    'percentage' => $progress['percentage'],
                    'ticket_code' => $ticketId
                ]
            );
        } catch (\Throwable $e) {
            error_log("Failed to send installment payment email: " . $e->getMessage());
        }

        return [
            'success' => true,
            'is_fully_paid' => false,
            'ticket_id' => $ticketId,
            'progress' => $progress,
            'message' => 'Installment of ' . format_price((float)$payment['amount']) . ' received! Remaining balance: ' . format_price($progress['remaining']) . '.'
        ];
    }

    /**
     * Mark a layaway ticket as fully paid and active for entry.
     */
    public function markTicketAsFullyPaid(string $ticketId): bool
    {
        $db = Application::$app->db->pdo;
        $stmt = $db->prepare("
            UPDATE tickets 
            SET status = 'Paid', 
                status_detail = CONCAT(COALESCE(status_detail, ''), ' | Layaway Completed on ', NOW()) 
            WHERE id = :id AND status = 'Layaway'
        ");
        return $stmt->execute(['id' => $ticketId]);
    }

    /**
     * Get detailed progress metrics for a layaway ticket.
     */
    public function getLayawayProgress(string $ticketId): array
    {
        $db = Application::$app->db->pdo;
        $stmt = $db->prepare("SELECT price FROM tickets WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $ticketId]);
        $totalPrice = (float)$stmt->fetchColumn();

        $totalPaid = LayawayPayment::getTotalPaid($ticketId);
        $remaining = max(0, $totalPrice - $totalPaid);
        $percentage = $totalPrice > 0 ? min(100, round(($totalPaid / $totalPrice) * 100)) : 0;
        $payments = LayawayPayment::findByTicketId($ticketId);

        return [
            'total_price' => $totalPrice,
            'total_paid' => $totalPaid,
            'remaining' => $remaining,
            'percentage' => $percentage,
            'is_complete' => ($remaining <= 0.00),
            'payments' => $payments
        ];
    }

    /**
     * Get all active layaways for a user.
     */
    public function getUserLayaways(int $userId, ?string $email = null): array
    {
        $db = Application::$app->db->pdo;
        $sql = "
            SELECT 
                t.id AS ticket_id,
                t.attendee_name,
                t.attendee_email,
                t.price AS total_price,
                t.status AS ticket_status,
                t.purchase_date,
                e.id AS event_id,
                e.name AS event_name,
                e.date AS event_date,
                e.location AS event_location,
                e.image AS event_image,
                tt.name AS ticket_type
            FROM tickets t
            INNER JOIN events e ON t.event_id = e.id
            INNER JOIN ticket_types tt ON t.ticket_type_id = tt.id
            WHERE (t.user_id = :user_id " . ($email ? "OR LOWER(t.attendee_email) = LOWER(:email)" : "") . ")
              AND t.status = 'Layaway'
            ORDER BY e.date ASC
        ";
        $stmt = $db->prepare($sql);
        $params = ['user_id' => $userId];
        if ($email) {
            $params['email'] = $email;
        }
        $stmt->execute($params);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];

        $result = [];
        foreach ($rows as $row) {
            $row['progress'] = $this->getLayawayProgress($row['ticket_id']);
            $eventTimestamp = strtotime($row['event_date']);
            $row['days_until_event'] = $eventTimestamp ? max(0, ceil(($eventTimestamp - time()) / 86400)) : 0;
            $row['is_past_due'] = $eventTimestamp ? (time() > $eventTimestamp) : false;
            $result[] = $row;
        }

        return $result;
    }

    /**
     * Fetch user credits available.
     */
    public function getUserCredits(string $email, ?int $userId = null): array
    {
        return LayawayCredit::findAvailableByEmail($email, $userId);
    }

    /**
     * Expire incomplete layaways whose event has already started.
     * Non-refundable: converts all money paid into an event credit usable on another event!
     */
    public function expireUnpaidLayaways(): array
    {
        $db = Application::$app->db->pdo;

        // Find tickets in 'Layaway' status where the event date has passed
        $stmt = $db->query("
            SELECT t.id AS ticket_id, t.user_id, t.attendee_name, t.attendee_email, t.price, 
                   e.id AS event_id, e.name AS event_name, e.date AS event_date
            FROM tickets t
            INNER JOIN events e ON t.event_id = e.id
            WHERE t.status = 'Layaway' 
              AND e.date < NOW()
        ");
        $expiredCandidates = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];

        $expiredCount = 0;
        $creditsCreated = 0;
        $totalCreditAmount = 0.00;

        foreach ($expiredCandidates as $candidate) {
            $ticketId = $candidate['ticket_id'];
            $totalPaid = LayawayPayment::getTotalPaid($ticketId);

            $db->beginTransaction();
            try {
                // Mark ticket as Expired
                $uStmt = $db->prepare("
                    UPDATE tickets 
                    SET status = 'Expired', 
                        status_detail = CONCAT(COALESCE(status_detail, ''), ' | Expired unpaid on ', NOW()) 
                    WHERE id = :id
                ");
                $uStmt->execute(['id' => $ticketId]);

                // If money was paid, convert it to a LayawayCredit record valid for 1 year!
                if ($totalPaid > 0) {
                    $cStmt = $db->prepare("
                        INSERT INTO layaway_credits (user_id, email, original_ticket_id, original_event_id, amount, remaining_amount, status, expires_at, created_at)
                        VALUES (:user_id, :email, :orig_ticket, :orig_event, :amt, :rem, 'available', DATE_ADD(NOW(), INTERVAL 1 YEAR), NOW())
                    ");
                    $cStmt->execute([
                        'user_id' => $candidate['user_id'],
                        'email' => $candidate['attendee_email'],
                        'orig_ticket' => $ticketId,
                        'orig_event' => $candidate['event_id'],
                        'amt' => $totalPaid,
                        'rem' => $totalPaid
                    ]);
                    $creditsCreated++;
                    $totalCreditAmount += $totalPaid;

                    // Send layaway_expired notification email with credit details
                    $expiresAtStr = date('M d, Y', strtotime('+1 year'));
                    try {
                        \Ace\Mail::send(
                            $candidate['attendee_email'],
                            'Event Pass Expired - Event Credit Available (' . format_price($totalPaid) . ')',
                            'emails/layaway_expired',
                            [
                                'attendee_name' => $candidate['attendee_name'],
                                'event_name' => $candidate['event_name'],
                                'original_event_date' => date('D, M d, Y', strtotime($candidate['event_date'])),
                                'total_paid' => $totalPaid,
                                'credit_amount' => $totalPaid,
                                'credit_expires_at' => $expiresAtStr
                            ]
                        );
                    } catch (\Throwable $mEx) {
                        error_log("Failed to send layaway credit expiry email: " . $mEx->getMessage());
                    }
                }

                $db->commit();
                $expiredCount++;
            } catch (\Throwable $e) {
                $db->rollBack();
                error_log("Error expiring layaway ticket {$ticketId}: " . $e->getMessage());
            }
        }

        return [
            'expired_count' => $expiredCount,
            'credits_created' => $creditsCreated,
            'total_credit_value' => $totalCreditAmount
        ];
    }

    /**
     * Apply credit toward a layaway ticket installment or checkout.
     */
    public function applyCredit(int $creditId, string $email, float $amount): array
    {
        $db = Application::$app->db->pdo;
        $stmt = $db->prepare("
            SELECT * FROM layaway_credits 
            WHERE id = :id AND LOWER(email) = LOWER(:email) AND status IN ('available', 'partially_used') 
            LIMIT 1
        ");
        $stmt->execute(['id' => $creditId, 'email' => $email]);
        $credit = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$credit) {
            return ['success' => false, 'message' => 'Valid credit not found.'];
        }

        $rem = (float)$credit['remaining_amount'];
        if ($rem <= 0) {
            return ['success' => false, 'message' => 'This credit has no remaining balance.'];
        }

        $deduct = min($amount, $rem);
        $newRem = $rem - $deduct;
        $newStatus = $newRem <= 0.005 ? 'used' : 'partially_used';

        $uStmt = $db->prepare("
            UPDATE layaway_credits 
            SET remaining_amount = :rem, status = :status, updated_at = NOW() 
            WHERE id = :id
        ");
        $uStmt->execute(['rem' => $newRem, 'status' => $newStatus, 'id' => $creditId]);

        return [
            'success' => true,
            'deducted' => $deduct,
            'remaining_credit' => $newRem
        ];
    }
}
