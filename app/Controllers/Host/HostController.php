<?php

/**
 * HostController
 *
 * This controller handles host-related routes.
 *
 * @package Evently
 * @subpackage Controllers/Host
 * @author Your Name <Celio Natti>
 * @license MIT License
 * @version 1.0.0
 */

namespace App\Controllers\Host;

use Ace\Application;
use Ace\Controller;
use Ace\Request;
use App\Middlewares\HostMiddleware;
use App\Services\HostService;
use App\Services\EventService;

class HostController extends Controller
{
    protected HostService $hostService;
    protected EventService $eventService;

    /**
     * HostController constructor.
     * Registers the HostMiddleware and initializes services.
     */
    public function __construct()
    {
        $this->registerMiddleware(new HostMiddleware());
        $this->hostService = new HostService();
        $this->eventService = new EventService();
    }

    /**
     * Display the host dashboard.
     *
     * @return string
     */
    public function dashboard()
    {
        $user = Application::$app->user;
        $isAdmin = (method_exists($user, 'isAdmin') && $user->isAdmin()) || (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) || hasRole('admin') || hasRole('super-admin');

        // Sync auto-payouts and event completions
        (new \App\Services\AutoPayoutService())->processAutoPayouts();

        // Fetch stats via HostService
        $stats = $this->hostService->getDashboardStats($user->id);

        // Fetch events list via EventService (supports all events if admin)
        $events = $this->eventService->getHostEvents($user->id, $isAdmin);

        // Fetch recent payout transactions
        $recentPayouts = $this->hostService->getHostPayoutTransactions($user->id, 5);

        // Fetch visual analytics & meaningful insights
        $monthlyTrends = $this->hostService->getMonthlyRevenueTrend($user->id);
        $tierBreakdown = $this->hostService->getTicketTierBreakdown($user->id);
        $recentOrders = $this->hostService->getRecentTicketOrders($user->id, 8);
        $nextEvent = $this->hostService->getNextUpcomingEvent($user->id);
        $lastCompletedEvent = $this->hostService->getLastCompletedEvent($user->id);

        return $this->render('host/dashboard', array_merge($stats, [
            'events' => $events,
            'recentPayouts' => $recentPayouts,
            'monthlyTrends' => $monthlyTrends,
            'tierBreakdown' => $tierBreakdown,
            'recentOrders' => $recentOrders,
            'nextEvent' => $nextEvent,
            'lastCompletedEvent' => $lastCompletedEvent,
            'isAdmin' => $isAdmin,
            'currentUserId' => $user->id
        ]));
    }

    /**
     * Display the host financials, earnings breakdown, and payout transactions.
     *
     * @return string
     */
    public function financials()
    {
        $user = Application::$app->user;
        $db = Application::$app->db->pdo;

        // Sync auto-payouts
        (new \App\Services\AutoPayoutService())->processAutoPayouts();

        // Fetch overall stats
        $stats = $this->hostService->getDashboardStats($user->id);

        // Fetch per-event financials
        $eventFinancials = $this->hostService->getHostEventsFinancials($user->id);

        // Fetch full payout history
        $payoutHistory = $this->hostService->getHostPayoutTransactions($user->id, 100);

        // Fetch all online tickets purchased across host's events
        $allPurchasedTickets = $this->hostService->getHostOnlineTickets($user->id, 300);

        // Fetch all online merchandise & add-ons orders across host's events
        $allMerchOrders = $this->hostService->getHostMerchandiseOrders($user->id, 300);

        // Calculate merchandise aggregate stats
        $merchStats = $this->hostService->getHostMerchandiseStats($user->id);

        // Fetch host bank profile
        $stmt = $db->prepare("SELECT * FROM host_profiles WHERE user_id = :user_id LIMIT 1");
        $stmt->execute(['user_id' => $user->id]);
        $profile = $stmt->fetch(\PDO::FETCH_ASSOC) ?: [];

        return $this->render('host/financials', array_merge($stats, [
            'eventFinancials' => $eventFinancials,
            'payoutHistory' => $payoutHistory,
            'allPurchasedTickets' => $allPurchasedTickets,
            'allMerchOrders' => $allMerchOrders,
            'merchStats' => $merchStats,
            'profile' => $profile,
            'currentUserId' => $user->id
        ]));
    }

    /**
     * Handle host withdrawal requests.
     */
    public function requestWithdrawal(Request $request)
    {
        $user = Application::$app->user;
        $db = Application::$app->db->pdo;

        // 1. Fetch host profile to check bank configuration
        $stmt = $db->prepare("SELECT * FROM host_profiles WHERE user_id = :user_id LIMIT 1");
        $stmt->execute(['user_id' => $user->id]);
        $profile = $stmt->fetch();

        if (!$profile || empty($profile['payout_bank']) || empty($profile['payout_account'])) {
            Application::$app->session->setFlash('error', 'Please configure your Payout Bank and Account Number in Host Settings first.');
            return Application::$app->response->redirect('/host/settings');
        }

        // 2. Fetch stats to calculate balances
        $stats = $this->hostService->getDashboardStats($user->id);
        $withdrawType = trim($_POST['withdraw_type'] ?? 'completed');
        $selectedEventId = !empty($_POST['event_id']) ? (int)$_POST['event_id'] : null;

        if ($withdrawType === 'escrow') {
            if (empty($_POST['escrow_agreed']) && empty($_POST['escrow_agreement'])) {
                Application::$app->session->setFlash('error', 'You must accept the Escrow Release Agreement terms before requesting an escrow payout.');
                return Application::$app->response->redirect('/host/financials');
            }
            $maxAmount = $stats['availableEscrowBalance'] ?? 0.00;
            $typeLabel = 'Escrow Funds (Early Withdrawal)';
        } else {
            $maxAmount = $stats['availableBalance'] ?? 0.00;
            $typeLabel = 'Completed Events Balance';
        }

        // For single-event withdrawals, recalculate max from that specific event
        if ($selectedEventId) {
            $completionHoursPreCheck = event_completion_hours();
            $singleStmt = $db->prepare("
                SELECT e.id, e.pass_fee,
                       COALESCE((SELECT SUM(t.price) FROM tickets t WHERE t.event_id = e.id AND t.status = 'Paid' AND (t.is_issued = 0 OR t.is_issued IS NULL)), 0.00) as ticket_sales,
                       COALESCE((SELECT SUM(CASE WHEN COALESCE(t.pass_fee, e.pass_fee) = 1 THEN t.price ELSE t.price * 0.95 END) FROM tickets t WHERE t.event_id = e.id AND t.status = 'Paid' AND (t.is_issued = 0 OR t.is_issued IS NULL)), 0.00) as net_sales
                FROM events e
                WHERE e.id = :event_id AND e.organizer_id = :org_id
                LIMIT 1
            ");
            $singleStmt->execute(['event_id' => $selectedEventId, 'org_id' => $user->id]);
            $singleEvt = $singleStmt->fetch(\PDO::FETCH_ASSOC);

            if ($singleEvt) {
                $sTicketSales = (float)$singleEvt['ticket_sales'];
                // Payout and escrow withdraw is strictly for the online ticket sold price
                // Platform charges and the 5% fee are NEVER added
                $sHostNet = isset($singleEvt['net_sales']) ? round((float)$singleEvt['net_sales'], 2) : (!empty($singleEvt['pass_fee']) ? $sTicketSales : round($sTicketSales * 0.95, 2));
                $sPriorStmt = $db->prepare("SELECT COALESCE(SUM(amount), 0) FROM payouts WHERE event_id = :eid AND status IN ('pending', 'processed', 'completed')");
                $sPriorStmt->execute(['eid' => $selectedEventId]);
                $sPrior = (float)$sPriorStmt->fetchColumn();
                $maxAmount = round(max(0.00, $sHostNet - $sPrior), 2);
            }
        }

        if ($maxAmount <= 0) {
            if ($withdrawType === 'escrow' && ($stats['layawayPendingRevenue'] ?? 0) > 0) {
                Application::$app->session->setFlash('info', 'You have no available funds to withdraw for Early Escrow Release. You currently have ' . format_price($stats['layawayPendingRevenue']) . ' in active layaway plan deposits, which remain safely locked in escrow reserve until attendees complete full payment.');
            } else {
                Application::$app->session->setFlash('error', 'You have no available funds to withdraw for the selected option (' . $typeLabel . ').');
            }
            return Application::$app->response->redirect('/host/financials');
        }

        $payoutType = ($withdrawType === 'escrow') ? 'escrow' : 'completed';

        // 3. Find all host events matching the withdrawal type with unrequested balances
        $completionHours = event_completion_hours();
        $eventFilter = $selectedEventId ? " AND e.id = " . (int)$selectedEventId : "";

        if ($withdrawType === 'escrow') {
            $eventsStmt = $db->prepare("
                SELECT e.id, e.name, e.pass_fee,
                       COALESCE((
                           SELECT SUM(t.price) FROM tickets t 
                           WHERE t.event_id = e.id AND t.status = 'Paid' AND (t.is_issued = 0 OR t.is_issued IS NULL)
                       ), 0.00) as ticket_sales,
                       COALESCE((
                           SELECT SUM(CASE WHEN COALESCE(t.pass_fee, e.pass_fee) = 1 THEN t.price ELSE t.price * 0.95 END) FROM tickets t 
                           WHERE t.event_id = e.id AND t.status = 'Paid' AND (t.is_issued = 0 OR t.is_issued IS NULL)
                       ), 0.00) as net_sales
                FROM events e
                WHERE e.organizer_id = :org_id
                  AND e.status = 'published'
                  AND e.date >= NOW() - INTERVAL " . (int)$completionHours . " HOUR
                  {$eventFilter}
                ORDER BY e.date ASC
            ");
        } else {
            $eventsStmt = $db->prepare("
                SELECT e.id, e.name, e.pass_fee,
                       COALESCE((
                           SELECT SUM(t.price) FROM tickets t 
                           WHERE t.event_id = e.id AND t.status = 'Paid' AND (t.is_issued = 0 OR t.is_issued IS NULL)
                       ), 0.00) as ticket_sales,
                       COALESCE((
                           SELECT SUM(CASE WHEN COALESCE(t.pass_fee, e.pass_fee) = 1 THEN t.price ELSE t.price * 0.95 END) FROM tickets t 
                           WHERE t.event_id = e.id AND t.status = 'Paid' AND (t.is_issued = 0 OR t.is_issued IS NULL)
                       ), 0.00) as net_sales
                FROM events e
                WHERE e.organizer_id = :org_id
                  AND (e.status IN ('completed', 'past') OR (e.status = 'published' AND e.date < NOW() - INTERVAL " . (int)$completionHours . " HOUR))
                  {$eventFilter}
                ORDER BY e.id ASC
            ");
        }
        $eventsStmt->execute(['org_id' => $user->id]);
        $eligibleEvents = $eventsStmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];

        $totalRequested = 0.00;
        $createdCount = 0;
        $remainingQuota = $maxAmount;

        foreach ($eligibleEvents as $evt) {
            if ($remainingQuota <= 0.00) {
                break;
            }

            // Payout entitlement is strictly for online ticket sold price (excluding host-issued/comp passes and platform charges)
            $ticketSales = (float)$evt['ticket_sales'];
            $evtNet = isset($evt['net_sales']) ? round((float)$evt['net_sales'], 2) : (!empty($evt['pass_fee']) ? $ticketSales : round($ticketSales * 0.95, 2));

            $payoutsStmt = $db->prepare("
                SELECT COALESCE(SUM(amount), 0)
                FROM payouts
                WHERE event_id = :event_id AND status IN ('pending', 'processed', 'completed')
            ");
            $payoutsStmt->execute(['event_id' => $evt['id']]);
            $priorPayouts = (float)$payoutsStmt->fetchColumn();

            $unrequested = round(max(0.00, $evtNet - $priorPayouts), 2);
            $amountToRequest = round(min($unrequested, $remainingQuota), 2);
            if ($amountToRequest <= 0.00) {
                continue;
            }

            // Create payout request for this specific event
            $insStmt = $db->prepare("
                INSERT INTO payouts (organizer_id, event_id, amount, type, status, created_at)
                VALUES (:org_id, :evt_id, :amount, :type, 'pending', NOW())
            ");
            $insStmt->execute([
                'org_id' => $user->id,
                'evt_id' => $evt['id'],
                'amount' => $amountToRequest,
                'type' => $payoutType
            ]);

            $totalRequested += $amountToRequest;
            $remainingQuota -= $amountToRequest;
            $createdCount++;
        }

        if ($createdCount === 0 || $totalRequested <= 0.00) {
            Application::$app->session->setFlash('error', 'All funds for your eligible events have already been requested or disbursed.');
            return Application::$app->response->redirect('/host/financials');
        }

        $eventNote = $selectedEventId ? ' for this event' : ' across ' . $createdCount . ' event(s)';
        Application::$app->session->setFlash('success', 'Withdrawal request of ' . format_price($totalRequested) . ' (' . $typeLabel . ')' . $eventNote . ' submitted successfully!');
        return Application::$app->response->redirect('/host/financials');
    }
}