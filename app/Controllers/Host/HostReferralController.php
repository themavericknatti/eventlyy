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
        $referrals = $refStmt->fetchAll() ?: [];

        // Compute analytics
        $totalPromoters = count($referrals);
        $totalVisits = array_sum(array_column($referrals, 'visits_count'));
        $totalTicketsSold = array_sum(array_column($referrals, 'tickets_sold'));
        $totalRevenue = array_sum(array_column($referrals, 'revenue_generated'));
        $topPromoter = !empty($referrals) && $referrals[0]['tickets_sold'] > 0 ? $referrals[0] : null;
        $conversionRate = $totalVisits > 0 ? ($totalTicketsSold / $totalVisits) * 100 : 0.0;

        // Construct canonical event URL base for referral links:
        // When flyer page is enabled, promoter links prioritize the immersive flyer link (/f/{slug});
        // Otherwise, they fall back to the standard event page (/event/{slug}).
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
            'totalTicketsSold' => $totalTicketsSold,
            'totalRevenue' => $totalRevenue,
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
        $customCode = trim($data['referral_code'] ?? '');
        $commissionRate = max(0, min(100, (float)($data['commission_rate'] ?? 0.00)));

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
            INSERT INTO event_referrals (event_id, host_id, promoter_name, referral_code, commission_rate, status, created_at)
            VALUES (:event_id, :host_id, :promoter_name, :referral_code, :commission_rate, 'active', NOW())
        ");
        $insStmt->execute([
            'event_id' => $event['id'],
            'host_id' => $user->id,
            'promoter_name' => $promoterName,
            'referral_code' => $referralCode,
            'commission_rate' => $commissionRate
        ]);

        Application::$app->session->setFlash('success', "Referral link for '{$promoterName}' created successfully! Code: {$referralCode}");
        redirect('/host/event/referrals/' . $event['id']);
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
