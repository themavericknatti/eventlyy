<?php

namespace App\Controllers\Host;

use Ace\Application;
use Ace\Controller;
use Ace\Request;
use App\Middlewares\HostMiddleware;
use App\Models\EventReferral;

class HostReferralController extends Controller
{
    public function __construct()
    {
        $this->registerMiddleware(new HostMiddleware());
    }

    /**
     * Show the Referral & Affiliate Tracking Hub for an event.
     */
    public function index(Request $request, int|string $eventId): string
    {
        $user = Application::$app->user;
        $db = Application::$app->db->pdo;

        // Fetch event and verify host authorization
        $stmt = $db->prepare("SELECT * FROM events WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => (int)$eventId]);
        $event = $stmt->fetch();

        if (!$event) {
            Application::$app->session->setFlash('error', 'Event not found.');
            redirect('/host/dashboard');
            return '';
        }

        if ($event['organizer_id'] != $user->id && !hasRole('admin') && !hasRole('super-admin')) {
            Application::$app->session->setFlash('error', 'Unauthorized access to this event.');
            redirect('/host/dashboard');
            return '';
        }

        // Fetch all referrals for this event
        $refStmt = $db->prepare("
            SELECT * 
            FROM event_referrals 
            WHERE event_id = :event_id 
            ORDER BY tickets_sold DESC, revenue_generated DESC, id DESC
        ");
        $refStmt->execute(['event_id' => $event['id']]);
        $rawReferrals = $refStmt->fetchAll() ?: [];

        $referrals = [];
        $totalCommissionEarned = 0.00;
        $totalCommissionPaid = 0.00;

        foreach ($rawReferrals as $ref) {
            $commEarned = (float)($ref['commission_earned'] ?? 0.00);
            $commPaid = (float)($ref['commission_paid'] ?? 0.00);
            $rate = (float)($ref['commission_rate'] ?? 0.00);
            $type = $ref['commission_type'] ?? 'percentage';
            $revenue = (float)($ref['revenue_generated'] ?? 0.00);
            $sold = (int)($ref['tickets_sold'] ?? 0);

            // Backfill or calculate commission earned if not recorded
            if ($commEarned <= 0.00 && $rate > 0 && ($revenue > 0 || $sold > 0)) {
                if ($type === 'fixed') {
                    $commEarned = round($sold * $rate, 2);
                } else {
                    $commEarned = round(($revenue * $rate) / 100, 2);
                }
            }

            $balanceDue = max(0.00, round($commEarned - $commPaid, 2));

            $uVisits = (int)($ref['unique_visits'] ?? $ref['visits_count']);
            $vCount = (int)$ref['visits_count'];
            $convRate = $uVisits > 0 ? ($sold / $uVisits) * 100 : ($vCount > 0 ? ($sold / $vCount) * 100 : 0.0);

            $ref['commission_earned'] = $commEarned;
            $ref['commission_paid'] = $commPaid;
            $ref['balance_due'] = $balanceDue;
            $ref['conversion_rate'] = $convRate;
            $ref['unique_visits'] = $uVisits;

            $totalCommissionEarned += $commEarned;
            $totalCommissionPaid += $commPaid;

            $referrals[] = $ref;
        }

        // Compute aggregate analytics
        $totalPromoters = count($referrals);
        $totalVisits = array_sum(array_column($referrals, 'visits_count'));
        $totalUniqueVisits = array_sum(array_column($referrals, 'unique_visits'));
        $totalTicketsSold = array_sum(array_column($referrals, 'tickets_sold'));
        $totalRevenue = array_sum(array_column($referrals, 'revenue_generated'));
        $totalBalanceDue = max(0.00, round($totalCommissionEarned - $totalCommissionPaid, 2));
        $topPromoter = !empty($referrals) && $referrals[0]['tickets_sold'] > 0 ? $referrals[0] : null;
        $conversionRate = $totalUniqueVisits > 0 ? ($totalTicketsSold / $totalUniqueVisits) * 100 : ($totalVisits > 0 ? ($totalTicketsSold / $totalVisits) * 100 : 0.0);

        // Construct canonical event URL base for referral links
        if (!empty($event['flyer_page_enabled'])) {
            $eventBaseUrl = event_flyer_url($event);
        } else {
            $eventBaseUrl = event_url($event);
        }

        // Fetch organizer info for admin context
        $isAdmin = (method_exists($user, 'isAdmin') && $user->isAdmin()) || (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) || hasRole('admin') || hasRole('super-admin');
        $organizerName = null;
        $organizerEmail = null;
        if ($isAdmin && $event['organizer_id'] != $user->id) {
            $orgStmt = $db->prepare("SELECT name, email FROM users WHERE id = :id LIMIT 1");
            $orgStmt->execute(['id' => $event['organizer_id']]);
            $org = $orgStmt->fetch(\PDO::FETCH_ASSOC);
            $organizerName = $org['name'] ?? 'Unknown';
            $organizerEmail = $org['email'] ?? '';
        }

        return $this->render('host/referrals', [
            'title' => 'Referral & Affiliate Tracking - ' . $event['name'],
            'event' => (object)$event,
            'referrals' => $referrals,
            'totalPromoters' => $totalPromoters,
            'totalVisits' => $totalVisits,
            'totalUniqueVisits' => $totalUniqueVisits,
            'totalTicketsSold' => $totalTicketsSold,
            'totalRevenue' => $totalRevenue,
            'totalCommissionEarned' => $totalCommissionEarned,
            'totalCommissionPaid' => $totalCommissionPaid,
            'totalBalanceDue' => $totalBalanceDue,
            'topPromoter' => $topPromoter,
            'conversionRate' => $conversionRate,
            'eventBaseUrl' => $eventBaseUrl,
            'isAdmin' => $isAdmin,
            'organizerName' => $organizerName,
            'organizerEmail' => $organizerEmail
        ]);
    }

    /**
     * Create a new referral link for a promoter.
     */
    public function create(Request $request, int|string $eventId): void
    {
        $user = Application::$app->user;
        $db = Application::$app->db->pdo;
        $data = $request->getBody();

        // Verify event ownership
        $stmt = $db->prepare("SELECT * FROM events WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => (int)$eventId]);
        $event = $stmt->fetch();

        if (!$event || ($event['organizer_id'] != $user->id && !hasRole('admin') && !hasRole('super-admin'))) {
            Application::$app->session->setFlash('error', 'Unauthorized access.');
            redirect('/host/dashboard');
            return;
        }

        $promoterName = trim($data['promoter_name'] ?? '');
        $promoterEmail = trim($data['promoter_email'] ?? '') ?: null;
        $promoterPhone = trim($data['promoter_phone'] ?? '') ?: null;
        $customCode = trim($data['referral_code'] ?? '');
        $commissionType = in_array($data['commission_type'] ?? '', ['fixed', 'percentage']) ? $data['commission_type'] : 'percentage';
        $commissionRate = max(0, (float)($data['commission_rate'] ?? 0.00));
        $notes = trim($data['notes'] ?? '') ?: null;

        if (empty($promoterName)) {
            Application::$app->session->setFlash('error', 'Please provide the promoter or ambassador name.');
            redirect('/host/event/referrals/' . $event['id']);
            return;
        }

        // Determine referral code
        if (!empty($customCode)) {
            $referralCode = strtolower(preg_replace('/[^a-zA-Z0-9_\-]+/', '', $customCode));
            if (empty($referralCode)) {
                $referralCode = EventReferral::generateCode($promoterName, (int)$event['id']);
            }
        } else {
            $referralCode = EventReferral::generateCode($promoterName, (int)$event['id']);
        }

        // Check uniqueness across all referrals
        $checkStmt = $db->prepare("SELECT id FROM event_referrals WHERE referral_code = :code LIMIT 1");
        $checkStmt->execute(['code' => $referralCode]);
        if ($checkStmt->fetch()) {
            Application::$app->session->setFlash('error', "The referral code '{$referralCode}' is already taken. Please choose another or leave empty to auto-generate.");
            redirect('/host/event/referrals/' . $event['id']);
            return;
        }

        // Insert new referral
        $insStmt = $db->prepare("
            INSERT INTO event_referrals (
                event_id, host_id, promoter_name, promoter_email, promoter_phone, 
                referral_code, commission_type, commission_rate, notes, status, created_at
            ) VALUES (
                :event_id, :host_id, :promoter_name, :promoter_email, :promoter_phone,
                :referral_code, :commission_type, :commission_rate, :notes, 'active', NOW()
            )
        ");
        $insStmt->execute([
            'event_id' => $event['id'],
            'host_id' => $user->id,
            'promoter_name' => $promoterName,
            'promoter_email' => $promoterEmail,
            'promoter_phone' => $promoterPhone,
            'referral_code' => $referralCode,
            'commission_type' => $commissionType,
            'commission_rate' => $commissionRate,
            'notes' => $notes
        ]);

        Application::$app->session->setFlash('success', "Referral link for '{$promoterName}' created successfully! Code: {$referralCode}");
        redirect('/host/event/referrals/' . $event['id']);
    }

    /**
     * Update an existing promoter referral link.
     */
    public function update(Request $request, int|string $eventId, int|string $refId): void
    {
        $user = Application::$app->user;
        $db = Application::$app->db->pdo;
        $data = $request->getBody();

        $stmt = $db->prepare("SELECT * FROM event_referrals WHERE id = :id AND event_id = :event_id LIMIT 1");
        $stmt->execute(['id' => (int)$refId, 'event_id' => (int)$eventId]);
        $referral = $stmt->fetch();

        if (!$referral || ($referral['host_id'] != $user->id && !hasRole('admin') && !hasRole('super-admin'))) {
            Application::$app->session->setFlash('error', 'Referral link not found or unauthorized.');
            redirect('/host/event/referrals/' . $eventId);
            return;
        }

        $promoterName = trim($data['promoter_name'] ?? '');
        $promoterEmail = trim($data['promoter_email'] ?? '') ?: null;
        $promoterPhone = trim($data['promoter_phone'] ?? '') ?: null;
        $commissionType = in_array($data['commission_type'] ?? '', ['fixed', 'percentage']) ? $data['commission_type'] : 'percentage';
        $commissionRate = max(0, (float)($data['commission_rate'] ?? 0.00));
        $notes = trim($data['notes'] ?? '') ?: null;
        $status = in_array($data['status'] ?? '', ['active', 'inactive']) ? $data['status'] : $referral['status'];

        if (empty($promoterName)) {
            Application::$app->session->setFlash('error', 'Promoter name cannot be empty.');
            redirect('/host/event/referrals/' . $eventId);
            return;
        }

        // Check if referral code is being changed
        $customCode = trim($data['referral_code'] ?? '');
        $newCode = $referral['referral_code'];
        if (!empty($customCode)) {
            $sanitized = strtolower(preg_replace('/[^a-zA-Z0-9_\-]+/', '', $customCode));
            if (!empty($sanitized) && $sanitized !== $referral['referral_code']) {
                $checkStmt = $db->prepare("SELECT id FROM event_referrals WHERE referral_code = :code AND id != :id LIMIT 1");
                $checkStmt->execute(['code' => $sanitized, 'id' => $referral['id']]);
                if ($checkStmt->fetch()) {
                    Application::$app->session->setFlash('error', "The referral code '{$sanitized}' is already in use by another promoter.");
                    redirect('/host/event/referrals/' . $eventId);
                    return;
                }
                $newCode = $sanitized;
            }
        }

        // Recalculate commission_earned if rate or type changed
        $commEarned = (float)$referral['commission_earned'];
        $rev = (float)$referral['revenue_generated'];
        $sold = (int)$referral['tickets_sold'];
        if ($commissionType === 'fixed') {
            $commEarned = round($sold * $commissionRate, 2);
        } else {
            $commEarned = round(($rev * $commissionRate) / 100, 2);
        }

        $commPaid = (float)$referral['commission_paid'];
        $payoutStatus = 'unpaid';
        if ($commPaid >= $commEarned && $commEarned > 0) {
            $payoutStatus = 'paid';
        } elseif ($commPaid > 0) {
            $payoutStatus = 'partial';
        }

        $upStmt = $db->prepare("
            UPDATE event_referrals 
            SET promoter_name = :name,
                promoter_email = :email,
                promoter_phone = :phone,
                referral_code = :code,
                commission_type = :type,
                commission_rate = :rate,
                commission_earned = :earned,
                payout_status = :payout_status,
                notes = :notes,
                status = :status
            WHERE id = :id
        ");
        $upStmt->execute([
            'name' => $promoterName,
            'email' => $promoterEmail,
            'phone' => $promoterPhone,
            'code' => $newCode,
            'type' => $commissionType,
            'rate' => $commissionRate,
            'earned' => $commEarned,
            'payout_status' => $payoutStatus,
            'notes' => $notes,
            'status' => $status,
            'id' => $referral['id']
        ]);

        Application::$app->session->setFlash('success', "Promoter '{$promoterName}' updated successfully.");
        redirect('/host/event/referrals/' . $eventId);
    }

    /**
     * Record a commission payout settlement to a promoter.
     */
    public function recordPayout(Request $request, int|string $eventId, int|string $refId): void
    {
        $user = Application::$app->user;
        $db = Application::$app->db->pdo;
        $data = $request->getBody();

        $stmt = $db->prepare("SELECT * FROM event_referrals WHERE id = :id AND event_id = :event_id LIMIT 1");
        $stmt->execute(['id' => (int)$refId, 'event_id' => (int)$eventId]);
        $referral = $stmt->fetch();

        if (!$referral || ($referral['host_id'] != $user->id && !hasRole('admin') && !hasRole('super-admin'))) {
            Application::$app->session->setFlash('error', 'Referral link not found or unauthorized.');
            redirect('/host/event/referrals/' . $eventId);
            return;
        }

        $amount = (float)($data['amount'] ?? 0.00);
        $note = trim($data['note'] ?? '');

        if ($amount <= 0) {
            Application::$app->session->setFlash('error', 'Please enter a valid payout amount greater than zero.');
            redirect('/host/event/referrals/' . $eventId);
            return;
        }

        $success = EventReferral::recordPayout((int)$referral['id'], $amount, $note);
        if ($success) {
            Application::$app->session->setFlash('success', "Payout of " . format_price($amount) . " recorded for {$referral['promoter_name']}.");
        } else {
            Application::$app->session->setFlash('error', 'Failed to record payout. Please try again.');
        }

        redirect('/host/event/referrals/' . $eventId);
    }

    /**
     * Get transaction order details attributed to a specific referral link (AJAX/JSON).
     */
    public function orders(Request $request, int|string $eventId, int|string $refId): void
    {
        header('Content-Type: application/json');
        $user = Application::$app->user;
        $db = Application::$app->db->pdo;

        $stmt = $db->prepare("SELECT * FROM event_referrals WHERE id = :id AND event_id = :event_id LIMIT 1");
        $stmt->execute(['id' => (int)$refId, 'event_id' => (int)$eventId]);
        $referral = $stmt->fetch();

        if (!$referral || ($referral['host_id'] != $user->id && !hasRole('admin') && !hasRole('super-admin'))) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }

        // Fetch transactions linked to this referral
        $txStmt = $db->prepare("
            SELECT t.id, t.reference, t.payer_name, t.payer_email, t.payer_phone,
                   t.subtotal_amount, t.total_amount, t.payment_method, t.status, t.created_at,
                   (SELECT COUNT(*) FROM tickets tk WHERE tk.status_detail LIKE CONCAT('%', t.reference, '%')) as ticket_count
            FROM transactions t
            WHERE t.referral_id = :ref_id AND t.event_id = :event_id
            ORDER BY t.id DESC
        ");
        $txStmt->execute(['ref_id' => $referral['id'], 'event_id' => (int)$eventId]);
        $orders = $txStmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];

        // Calculate commission on each order
        $type = $referral['commission_type'] ?? 'percentage';
        $rate = (float)($referral['commission_rate'] ?? 0.00);

        foreach ($orders as &$order) {
            $tCount = max(1, (int)($order['ticket_count'] ?: 1));
            $subtotal = (float)$order['subtotal_amount'];
            if ($type === 'fixed') {
                $order['commission'] = round($tCount * $rate, 2);
            } else {
                $order['commission'] = round(($subtotal * $rate) / 100, 2);
            }
            $order['formatted_date'] = date('M d, Y • g:i A', strtotime($order['created_at']));
            $order['formatted_amount'] = format_price((float)$order['total_amount']);
            $order['formatted_commission'] = format_price((float)$order['commission']);
        }

        echo json_encode([
            'success' => true,
            'promoter' => [
                'name' => $referral['promoter_name'],
                'code' => $referral['referral_code'],
                'tickets_sold' => (int)$referral['tickets_sold'],
                'revenue_generated' => format_price((float)$referral['revenue_generated']),
                'commission_earned' => format_price((float)$referral['commission_earned']),
                'commission_paid' => format_price((float)$referral['commission_paid']),
                'balance_due' => format_price(max(0, (float)$referral['commission_earned'] - (float)$referral['commission_paid'])),
                'payout_status' => $referral['payout_status'] ?? 'unpaid'
            ],
            'orders' => $orders
        ]);
        exit;
    }

    /**
     * Export promoter performance data to CSV.
     */
    public function export(Request $request, int|string $eventId): void
    {
        $user = Application::$app->user;
        $db = Application::$app->db->pdo;

        $stmt = $db->prepare("SELECT * FROM events WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => (int)$eventId]);
        $event = $stmt->fetch();

        if (!$event || ($event['organizer_id'] != $user->id && !hasRole('admin') && !hasRole('super-admin'))) {
            Application::$app->session->setFlash('error', 'Unauthorized.');
            redirect('/host/dashboard');
            return;
        }

        $refStmt = $db->prepare("
            SELECT * FROM event_referrals 
            WHERE event_id = :event_id 
            ORDER BY tickets_sold DESC, revenue_generated DESC
        ");
        $refStmt->execute(['event_id' => $event['id']]);
        $referrals = $refStmt->fetchAll() ?: [];

        $filename = 'promoters_' . preg_replace('/[^a-zA-Z0-9_\-]+/', '_', strtolower($event['name'])) . '_' . date('Y-m-d') . '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');

        // CSV Header
        fputcsv($output, [
            'Promoter Name',
            'Referral Code',
            'Phone',
            'Email',
            'Status',
            'Total Visits',
            'Unique Visitors',
            'Tickets Sold',
            'Gross Revenue (NGN)',
            'Commission Type',
            'Commission Rate',
            'Commission Earned (NGN)',
            'Commission Paid (NGN)',
            'Balance Due (NGN)',
            'Payout Status',
            'Created Date'
        ]);

        foreach ($referrals as $ref) {
            $commEarned = (float)($ref['commission_earned'] ?? 0.00);
            $commPaid = (float)($ref['commission_paid'] ?? 0.00);
            $rate = (float)($ref['commission_rate'] ?? 0.00);
            $type = $ref['commission_type'] ?? 'percentage';
            $revenue = (float)($ref['revenue_generated'] ?? 0.00);
            $sold = (int)($ref['tickets_sold'] ?? 0);

            if ($commEarned <= 0.00 && $rate > 0 && ($revenue > 0 || $sold > 0)) {
                $commEarned = ($type === 'fixed') ? round($sold * $rate, 2) : round(($revenue * $rate) / 100, 2);
            }

            $balanceDue = max(0.00, round($commEarned - $commPaid, 2));

            fputcsv($output, [
                $ref['promoter_name'],
                $ref['referral_code'],
                $ref['promoter_phone'] ?? '',
                $ref['promoter_email'] ?? '',
                ucfirst($ref['status']),
                $ref['visits_count'],
                $ref['unique_visits'] ?? $ref['visits_count'],
                $ref['tickets_sold'],
                number_format($revenue, 2, '.', ''),
                ucfirst($type),
                $type === 'percentage' ? ($rate . '%') : ('₦' . number_format($rate, 2)),
                number_format($commEarned, 2, '.', ''),
                number_format($commPaid, 2, '.', ''),
                number_format($balanceDue, 2, '.', ''),
                ucfirst($ref['payout_status'] ?? 'unpaid'),
                $ref['created_at']
            ]);
        }

        fclose($output);
        exit;
    }

    /**
     * Toggle a referral link active/inactive status.
     */
    public function toggle(Request $request, int|string $eventId, int|string $refId): void
    {
        $user = Application::$app->user;
        $db = Application::$app->db->pdo;

        $stmt = $db->prepare("SELECT * FROM event_referrals WHERE id = :id AND event_id = :event_id LIMIT 1");
        $stmt->execute(['id' => (int)$refId, 'event_id' => (int)$eventId]);
        $referral = $stmt->fetch();

        if (!$referral || ($referral['host_id'] != $user->id && !hasRole('admin') && !hasRole('super-admin'))) {
            Application::$app->session->setFlash('error', 'Referral link not found or unauthorized.');
            redirect('/host/event/referrals/' . $eventId);
            return;
        }

        $newStatus = $referral['status'] === 'active' ? 'inactive' : 'active';
        $upStmt = $db->prepare("UPDATE event_referrals SET status = :status WHERE id = :id");
        $upStmt->execute(['status' => $newStatus, 'id' => $referral['id']]);

        $statusLabel = $newStatus === 'active' ? 'activated' : 'paused';
        Application::$app->session->setFlash('info', "Referral link for '{$referral['promoter_name']}' has been {$statusLabel}.");
        redirect('/host/event/referrals/' . $eventId);
    }

    /**
     * Delete a referral link.
     */
    public function delete(Request $request, int|string $eventId, int|string $refId): void
    {
        $user = Application::$app->user;
        $db = Application::$app->db->pdo;

        $stmt = $db->prepare("SELECT * FROM event_referrals WHERE id = :id AND event_id = :event_id LIMIT 1");
        $stmt->execute(['id' => (int)$refId, 'event_id' => (int)$eventId]);
        $referral = $stmt->fetch();

        if (!$referral || ($referral['host_id'] != $user->id && !hasRole('admin') && !hasRole('super-admin'))) {
            Application::$app->session->setFlash('error', 'Referral link not found or unauthorized.');
            redirect('/host/event/referrals/' . $eventId);
            return;
        }

        $delStmt = $db->prepare("DELETE FROM event_referrals WHERE id = :id");
        $delStmt->execute(['id' => $referral['id']]);

        Application::$app->session->setFlash('success', "Referral link for '{$referral['promoter_name']}' deleted.");
        redirect('/host/event/referrals/' . $eventId);
    }
}
