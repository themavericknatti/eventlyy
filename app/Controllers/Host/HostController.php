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

        // Fetch host bank profile
        $stmt = $db->prepare("SELECT * FROM host_profiles WHERE user_id = :user_id LIMIT 1");
        $stmt->execute(['user_id' => $user->id]);
        $profile = $stmt->fetch(\PDO::FETCH_ASSOC) ?: [];

        return $this->render('host/financials', array_merge($stats, [
            'eventFinancials' => $eventFinancials,
            'payoutHistory' => $payoutHistory,
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

        if ($withdrawType === 'escrow') {
            if (empty($_POST['escrow_agreed']) && empty($_POST['escrow_agreement'])) {
                Application::$app->session->setFlash('error', 'You must accept the Escrow Release Agreement terms before requesting an escrow payout.');
                return Application::$app->response->redirect('/host/dashboard');
            }
            $maxAmount = $stats['availableEscrowBalance'] ?? 0.00;
            $typeLabel = 'Escrow Funds (Early Withdrawal)';
        } else {
            $maxAmount = $stats['availableBalance'] ?? 0.00;
            $typeLabel = 'Completed Events Balance';
        }

        if ($maxAmount <= 0) {
            Application::$app->session->setFlash('error', 'You have no available funds to withdraw for the selected option (' . $typeLabel . ').');
            return Application::$app->response->redirect('/host/dashboard');
        }

        $payoutType = ($withdrawType === 'escrow') ? 'escrow' : 'completed';

        // 3. Find all host events matching the withdrawal type with unrequested balances
        if ($withdrawType === 'escrow') {
            $eventsStmt = $db->prepare("
                SELECT e.id, e.name, e.pass_fee,
                       SUM(CASE WHEN e.pass_fee = 1 THEN t.price ELSE t.price * 0.95 END) as net_sales
                FROM events e
                INNER JOIN tickets t ON e.id = t.event_id AND t.status IN ('Paid', 'Comped')
                    AND (t.is_issued = 0 OR t.is_issued IS NULL)
                WHERE e.organizer_id = :org_id
                  AND e.status = 'published'
                  AND e.date >= NOW() - INTERVAL " . event_completion_hours() . " HOUR
                GROUP BY e.id
                ORDER BY e.date ASC
            ");
        } else {
            $eventsStmt = $db->prepare("
                SELECT e.id, e.name, e.pass_fee,
                       SUM(CASE WHEN e.pass_fee = 1 THEN t.price ELSE t.price * 0.95 END) as net_sales
                FROM events e
                INNER JOIN tickets t ON e.id = t.event_id AND t.status IN ('Paid', 'Comped')
                    AND (t.is_issued = 0 OR t.is_issued IS NULL)
                WHERE e.organizer_id = :org_id
                  AND (e.status IN ('completed', 'past') OR (e.status = 'published' AND e.date < NOW() - INTERVAL " . event_completion_hours() . " HOUR))
                GROUP BY e.id
                ORDER BY e.id ASC
            ");
        }
        $eventsStmt->execute(['org_id' => $user->id]);
        $eligibleEvents = $eventsStmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];

        $totalRequested = 0.00;
        $createdCount = 0;

        foreach ($eligibleEvents as $evt) {
            $payoutsStmt = $db->prepare("
                SELECT COALESCE(SUM(amount), 0)
                FROM payouts
                WHERE event_id = :event_id AND status IN ('pending', 'processed', 'completed')
            ");
            $payoutsStmt->execute(['event_id' => $evt['id']]);
            $priorPayouts = (float)$payoutsStmt->fetchColumn();

            $unrequested = round(max(0.00, (float)$evt['net_sales'] - $priorPayouts), 2);
            if ($unrequested <= 0.00) {
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
                'amount' => $unrequested,
                'type' => $payoutType
            ]);

            $totalRequested += $unrequested;
            $createdCount++;
        }

        if ($createdCount === 0 || $totalRequested <= 0.00) {
            Application::$app->session->setFlash('error', 'All funds for your eligible events have already been requested or disbursed.');
            return Application::$app->response->redirect('/host/dashboard');
        }

        Application::$app->session->setFlash('success', 'Withdrawal request of ' . format_price($totalRequested) . ' (' . $typeLabel . ') across ' . $createdCount . ' event(s) submitted successfully!');
        return Application::$app->response->redirect('/host/dashboard');
    }
}