<?php

namespace App\Services;

use Ace\Application;

class AdminService
{
    /**
     * Get comprehensive dashboard aggregates, analytics, financial metrics, and recent activity audit.
     */
    public function getDashboardData(): array
    {
        $db = Application::$app->db->pdo;
        AutoPayoutService::ensureTicketsPassFeeColumn($db);

        // 1. User & Host Counts
        $totalUsers = (int)$db->query("SELECT COUNT(*) FROM users")->fetchColumn();
        
        $readyHosts = (int)$db->query("
            SELECT COUNT(*) 
            FROM host_profiles 
            WHERE payout_bank IS NOT NULL AND payout_bank != '' 
              AND payout_account IS NOT NULL AND payout_account != ''
        ")->fetchColumn();

        $totalHosts = (int)$db->query("
            SELECT COUNT(DISTINCT user_id) 
            FROM user_roles ur 
            INNER JOIN roles r ON ur.role_id = r.id 
            WHERE r.slug = 'event-organizer'
        ")->fetchColumn();

        $pendingHosts = max(0, $totalHosts - $readyHosts);

        // 2. Event Counts & Breakdown (count recurring series as one unique event)
        $completionHours = event_completion_hours();
        $recurDedup = "(
            e.recurrence_group_id IS NULL 
            OR e.recurrence_group_id = ''
            OR e.recurrence_sequence = 1
        )";

        $totalEvents = (int)$db->query("SELECT COUNT(*) FROM events e WHERE e.status = 'published' AND {$recurDedup}")->fetchColumn();
        $totalOccurrences = (int)$db->query("SELECT COUNT(*) FROM events WHERE status = 'published'")->fetchColumn();
        
        $liveEvents = (int)$db->query("
            SELECT COUNT(*) FROM events e
            WHERE e.status = 'published' AND e.date >= NOW() - INTERVAL {$completionHours} HOUR
              AND {$recurDedup}
        ")->fetchColumn();

        $completedEvents = (int)$db->query("
            SELECT COUNT(*) FROM events e
            WHERE (e.status IN ('completed', 'past') OR (e.status = 'published' AND e.date < NOW() - INTERVAL {$completionHours} HOUR))
              AND {$recurDedup}
        ")->fetchColumn();

        $draftEvents = (int)$db->query("SELECT COUNT(*) FROM events e WHERE e.status = 'draft' AND {$recurDedup}")->fetchColumn();

        // 3. Ticket Counts
        $totalTickets = (int)$db->query("SELECT COUNT(*) FROM tickets WHERE status IN ('Paid', 'Comped')")->fetchColumn();

        // 4. Financial Analytics
        $completionHours = event_completion_hours();

        // Total online tickets sold price across all events (pure sold ticket prices, excluding comped/issued)
        $totalTicketSales = (float)$db->query("
            SELECT COALESCE(SUM(t.price), 0.00)
            FROM tickets t
            WHERE t.status = 'Paid'
              AND (t.is_issued = 0 OR t.is_issued IS NULL)
        ")->fetchColumn();

        // Total nominal valuation of host-issued / complimentary passes (handled offline by hosts)
        $totalIssuedNominal = (float)$db->query("
            SELECT COALESCE(SUM(t.price), 0.00)
            FROM tickets t
            WHERE t.status IN ('Paid', 'Comped')
              AND t.is_issued = 1
        ")->fetchColumn();

        // Platform Profit (retained convenience fees & absorbed 5% fees)
        $platformProfit = (float)$db->query("
            SELECT COALESCE(SUM(fee_amount + extra_charge_amount), 0.00)
            FROM transactions
            WHERE type = 'payment' AND status = 'completed'
        ")->fetchColumn();

        if ($platformProfit == 0 && $totalTicketSales > 0) {
            $platformProfit = (float)$db->query("
                SELECT COALESCE(SUM(
                    CASE WHEN COALESCE(t.pass_fee, e.pass_fee) = 1 THEN t.price * 0.05 + 100 ELSE t.price * 0.05 END
                ), 0.00)
                FROM tickets t
                INNER JOIN events e ON t.event_id = e.id
                WHERE t.status = 'Paid'
                  AND (t.is_issued = 0 OR t.is_issued IS NULL)
            ")->fetchColumn();
        }

        // Gross Platform Volume (Gross inflow through platform = Ticket Sales + Platform Charges)
        $grossRevenue = (float)$db->query("
            SELECT COALESCE(SUM(total_amount), 0.00)
            FROM transactions
            WHERE type = 'payment' AND status = 'completed'
        ")->fetchColumn();

        if ($grossRevenue == 0) {
            $grossRevenue = $totalTicketSales + $platformProfit;
        }

        // Total Host Share (Net Entitlement across all events based strictly on ticket sold price)
        $totalHostShare = (float)$db->query("
            SELECT COALESCE(SUM(
                CASE WHEN COALESCE(t.pass_fee, e.pass_fee) = 1 THEN t.price ELSE t.price * 0.95 END
            ), 0.00)
            FROM tickets t
            INNER JOIN events e ON t.event_id = e.id
            WHERE t.status = 'Paid'
              AND (t.is_issued = 0 OR t.is_issued IS NULL)
        ")->fetchColumn();

        // Activity & Power Voting Metrics
        $actStats = $db->query("
            SELECT 
                COALESCE(SUM(total_amount), 0.00) as total_activity_sales,
                COALESCE(SUM(fee_amount), 0.00) as total_activity_fees,
                COUNT(*) as total_activity_orders
            FROM transactions
            WHERE payment_method = 'paystack_powervote' AND status = 'completed'
        ")->fetch(\PDO::FETCH_ASSOC) ?: [];
        $totalActivitySales = (float)($actStats['total_activity_sales'] ?? 0.00);
        $totalActivityFees = (float)($actStats['total_activity_fees'] ?? 0.00);
        $totalActivityOrders = (int)($actStats['total_activity_orders'] ?? 0);
        $activityHostShare = max(0.00, $totalActivitySales - $totalActivityFees);
        $totalHostShare += $activityHostShare;

        // Disbursed Host Payouts (Processed / Completed payouts)
        $disbursedPayouts = (float)$db->query("
            SELECT COALESCE(SUM(amount), 0.00)
            FROM payouts
            WHERE status IN ('processed', 'completed')
        ")->fetchColumn();

        // Pending Payout Requests (Submitted by hosts, queued for admin approval)
        $pendingPayoutRequests = (float)$db->query("
            SELECT COALESCE(SUM(amount), 0.00)
            FROM payouts
            WHERE status = 'pending'
        ")->fetchColumn();

        $pendingPayoutsCount = (int)$db->query("
            SELECT COUNT(*)
            FROM payouts
            WHERE status = 'pending'
        ")->fetchColumn();

        // Per-event calculations to determine exact Escrow Custody vs Withdrawable Completed balances
        $stmtEvents = $db->query("
            SELECT e.id, e.pass_fee, e.date, e.status,
                   COALESCE((
                       SELECT SUM(t.price) FROM tickets t 
                       WHERE t.event_id = e.id AND t.status = 'Paid' AND (t.is_issued = 0 OR t.is_issued IS NULL)
                   ), 0.00) as ticket_sales,
                   COALESCE((
                       SELECT SUM(CASE WHEN COALESCE(t.pass_fee, e.pass_fee) = 1 THEN t.price ELSE t.price * 0.95 END) FROM tickets t 
                       WHERE t.event_id = e.id AND t.status = 'Paid' AND (t.is_issued = 0 OR t.is_issued IS NULL)
                   ), 0.00) as net_ticket_sales,
                   COALESCE((
                       SELECT SUM(tx.total_amount - tx.fee_amount) FROM transactions tx
                       WHERE tx.event_id = e.id AND tx.payment_method = 'paystack_powervote' AND tx.status = 'completed'
                   ), 0.00) as activity_net
            FROM events e
        ");
        $allEvents = $stmtEvents->fetchAll(\PDO::FETCH_ASSOC) ?: [];

        $totalCompletedNetRevenue = 0.00;
        $totalUpcomingNetRevenue = 0.00;
        $now = time();

        foreach ($allEvents as $evRow) {
            $tSales = (float)$evRow['ticket_sales'];
            $actNet = (float)($evRow['activity_net'] ?? 0.00);
            $evNet = round((float)($evRow['net_ticket_sales'] ?? (!empty($evRow['pass_fee']) ? $tSales : round($tSales * 0.95, 2))), 2) + $actNet;
            $evTime = strtotime($evRow['date']);
            $isPassed = ($evTime + ($completionHours * 3600)) < $now || in_array($evRow['status'], ['completed', 'past']);

            if ($isPassed) {
                $totalCompletedNetRevenue += $evNet;
            } else {
                $totalUpcomingNetRevenue += $evNet;
            }
        }

        // Payout deductions for completed vs upcoming events
        $completedPayoutsDeduction = (float)$db->query("
            SELECT COALESCE(SUM(p.amount), 0.00)
            FROM payouts p
            INNER JOIN events e ON p.event_id = e.id
            WHERE p.status IN ('processed', 'completed', 'pending')
              AND (e.status IN ('completed', 'past') OR (e.status = 'published' AND e.date < NOW() - INTERVAL {$completionHours} HOUR))
        ")->fetchColumn();

        $escrowPayoutsDeduction = (float)$db->query("
            SELECT COALESCE(SUM(p.amount), 0.00)
            FROM payouts p
            INNER JOIN events e ON p.event_id = e.id
            WHERE p.status IN ('processed', 'completed', 'pending')
              AND e.status = 'published' AND e.date >= NOW() - INTERVAL {$completionHours} HOUR
        ")->fetchColumn();

        // Available Withdrawable Balance for completed events
        $withdrawableHostBalance = max(0.00, round($totalCompletedNetRevenue - $completedPayoutsDeduction, 2));

        // Available Escrow Balance for upcoming events (active escrow custody)
        $totalEscrowBalance = max(0.00, round($totalUpcomingNetRevenue - $escrowPayoutsDeduction, 2));

        // Total undisbursed host funds in platform custody
        $totalUndisbursedHostFunds = max(0.00, round($totalHostShare - $disbursedPayouts, 2));

        // 5. Monthly Analytics (Past 12 Months Timeline for Charts)
        $monthlyLabels = [];
        $monthlyRevenue = [];
        $monthlyProfit = [];
        $monthlyUsers = [];
        $monthlyTickets = [];
        $monthlyPayouts = [];

        for ($i = 11; $i >= 0; $i--) {
            $monthKey = date('Y-m', strtotime("-$i months"));
            $monthlyLabels[] = date('M', strtotime("-$i months"));
            
            $revStmt = $db->prepare("
                SELECT COALESCE(SUM(total_amount), 0) FROM transactions 
                WHERE type = 'payment' AND status = 'completed'
                  AND DATE_FORMAT(created_at, '%Y-%m') = :m
            ");
            $revStmt->execute(['m' => $monthKey]);
            $monthlyRevenue[] = (float)$revStmt->fetchColumn();

            $profStmt = $db->prepare("
                SELECT COALESCE(SUM(fee_amount + extra_charge_amount), 0) FROM transactions 
                WHERE status = 'completed' AND DATE_FORMAT(created_at, '%Y-%m') = :m
            ");
            $profStmt->execute(['m' => $monthKey]);
            $monthlyProfit[] = (float)$profStmt->fetchColumn();

            $userStmt = $db->prepare("
                SELECT COUNT(*) FROM users WHERE DATE_FORMAT(created_at, '%Y-%m') = :m
            ");
            $userStmt->execute(['m' => $monthKey]);
            $monthlyUsers[] = (int)$userStmt->fetchColumn();

            $ticketStmt = $db->prepare("
                SELECT COUNT(*) FROM tickets 
                WHERE status IN ('Paid', 'Comped') AND DATE_FORMAT(purchase_date, '%Y-%m') = :m
            ");
            $ticketStmt->execute(['m' => $monthKey]);
            $monthlyTickets[] = (int)$ticketStmt->fetchColumn();

            $payoutStmt = $db->prepare("
                SELECT COALESCE(SUM(amount), 0) FROM payouts 
                WHERE status IN ('processed', 'completed') AND DATE_FORMAT(created_at, '%Y-%m') = :m
            ");
            $payoutStmt->execute(['m' => $monthKey]);
            $monthlyPayouts[] = (float)$payoutStmt->fetchColumn();
        }

        // 5.5. Revenue Report Aggregates
        $todayRevenue = (float)$db->query("
            SELECT COALESCE(SUM(total_amount), 0) FROM transactions WHERE type = 'payment' AND status = 'completed' AND DATE(created_at) = CURDATE()
        ")->fetchColumn();

        $todayFees = (float)$db->query("
            SELECT COALESCE(SUM(fee_amount + extra_charge_amount), 0) FROM transactions WHERE status = 'completed' AND DATE(created_at) = CURDATE()
        ")->fetchColumn();

        $weekRevenue = (float)$db->query("
            SELECT COALESCE(SUM(total_amount), 0) FROM transactions WHERE type = 'payment' AND status = 'completed' AND YEARWEEK(created_at, 1) = YEARWEEK(CURDATE(), 1)
        ")->fetchColumn();

        $weekFees = (float)$db->query("
            SELECT COALESCE(SUM(fee_amount + extra_charge_amount), 0) FROM transactions WHERE status = 'completed' AND YEARWEEK(created_at, 1) = YEARWEEK(CURDATE(), 1)
        ")->fetchColumn();

        $monthRevenue = (float)$db->query("
            SELECT COALESCE(SUM(total_amount), 0) FROM transactions WHERE type = 'payment' AND status = 'completed' AND MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())
        ")->fetchColumn();

        $monthFees = (float)$db->query("
            SELECT COALESCE(SUM(fee_amount + extra_charge_amount), 0) FROM transactions WHERE status = 'completed' AND MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())
        ")->fetchColumn();

        $monthPayouts = (float)$db->query("
            SELECT COALESCE(SUM(amount), 0) FROM payouts WHERE status IN ('processed', 'completed') AND MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())
        ")->fetchColumn();

        $totalTransactions = (int)$db->query("
            SELECT COUNT(*) FROM transactions WHERE status = 'completed'
        ")->fetchColumn();

        $avgTransactionValue = (float)$db->query("
            SELECT COALESCE(AVG(total_amount), 0) FROM transactions WHERE type = 'payment' AND status = 'completed'
        ")->fetchColumn();

        // 6. Recent Published Events (enriched, deduplicating recurring series)
        $stmt = $db->prepare("
            SELECT e.*, u.name as organizer_name, u.email as organizer_email,
                   (SELECT COUNT(*) FROM tickets t WHERE t.event_id = e.id AND t.status IN ('Paid', 'Comped') AND (t.is_issued = 0 OR t.is_issued IS NULL)) as sold,
                   (SELECT COALESCE(SUM(t.price), 0) FROM tickets t WHERE t.event_id = e.id AND t.status IN ('Paid', 'Comped') AND (t.is_issued = 0 OR t.is_issued IS NULL)) as total_sales,
                   (SELECT SUM(capacity) FROM ticket_types tt WHERE tt.event_id = e.id) as capacity
            FROM events e
            INNER JOIN users u ON e.organizer_id = u.id
            WHERE e.status = 'published'
              AND (e.recurrence_group_id IS NULL OR e.recurrence_group_id = '' OR e.recurrence_sequence = 1)
            ORDER BY e.created_at DESC
            LIMIT 6
        ");
        $stmt->execute();
        $recentEvents = $stmt->fetchAll() ?: [];

        // 7. Recent Hosts & Organizers (enriched)
        $stmt = $db->prepare("
            SELECT u.*, hp.payout_bank, hp.payout_account, hp.rating,
                   (SELECT COUNT(*) FROM events ev WHERE ev.organizer_id = u.id AND (ev.recurrence_group_id IS NULL OR ev.recurrence_group_id = '' OR ev.recurrence_sequence = 1)) as total_events_count,
                   (SELECT COUNT(*) FROM events ev WHERE ev.organizer_id = u.id AND ev.status = 'published' AND (ev.recurrence_group_id IS NULL OR ev.recurrence_group_id = '' OR ev.recurrence_sequence = 1)) as active_events_count,
                   (SELECT COALESCE(SUM(tk.price), 0) FROM tickets tk INNER JOIN events ev ON tk.event_id = ev.id WHERE ev.organizer_id = u.id AND tk.status IN ('Paid', 'Comped') AND (tk.is_issued = 0 OR tk.is_issued IS NULL)) as host_total_sales
            FROM users u
            INNER JOIN user_roles ur ON u.id = ur.user_id
            INNER JOIN roles r ON ur.role_id = r.id
            LEFT JOIN host_profiles hp ON u.id = hp.user_id
            WHERE r.slug = 'event-organizer'
            ORDER BY u.created_at DESC
            LIMIT 6
        ");
        $stmt->execute();
        $recentHosts = $stmt->fetchAll() ?: [];

        // 8. Recent Audit Trail / Financial Transactions
        $auditTrail = $db->query("
            SELECT t.*, e.name as event_name
            FROM transactions t
            LEFT JOIN events e ON t.event_id = e.id
            ORDER BY t.created_at DESC
            LIMIT 8
        ")->fetchAll() ?: [];

        // 8.5. Real-Time Ticket Purchases & Attendee Activity Stream
        $recentTicketPurchases = $db->query("
            SELECT t.id, t.attendee_name, t.attendee_email, t.price, t.status, t.status_detail,
                   t.purchase_date, t.checked_in, t.checked_in_at, t.event_id,
                   e.name as event_name, e.slug as event_slug, e.custom_url as event_custom_url,
                   tt.name as ticket_tier, u.name as buyer_name
            FROM tickets t
            LEFT JOIN events e ON t.event_id = e.id
            LEFT JOIN ticket_types tt ON t.ticket_type_id = tt.id
            LEFT JOIN users u ON t.user_id = u.id
            WHERE t.is_issued = 0 OR t.is_issued IS NULL
            ORDER BY t.purchase_date DESC, t.id DESC
            LIMIT 12
        ")->fetchAll() ?: [];

        $todayTickets = (int)$db->query("SELECT COUNT(*) FROM tickets WHERE status IN ('Paid', 'Comped') AND (is_issued = 0 OR is_issued IS NULL) AND DATE(purchase_date) = CURDATE()")->fetchColumn();

        // 9. Real-Time Operational Queue & Alert Metrics
        $checkedInTickets = (int)$db->query("SELECT COUNT(*) FROM tickets WHERE status IN ('Paid', 'Comped') AND checked_in = 1")->fetchColumn();
        $checkInRate = $totalTickets > 0 ? round(($checkedInTickets / $totalTickets) * 100, 1) : 0;
        
        $unresolvedSupport = (int)$db->query("SELECT COUNT(*) FROM support_inquiries WHERE status IN ('new', 'read')")->fetchColumn();
        
        // Auto-reconcile expired staffing requests for concluded events before counting
        (new \App\Services\TeamService())->cleanupExpiredStaffRequests();
        $completionHours = event_completion_hours();
        $pendingStaffRequestsCount = (int)$db->query("
            SELECT COUNT(*) 
            FROM event_staff_requests esr
            JOIN events e ON esr.event_id = e.id
            WHERE esr.status IN ('pending', 'partially_fulfilled')
              AND e.status = 'published'
              AND (e.date IS NULL OR e.date >= NOW() - INTERVAL {$completionHours} HOUR)
        ")->fetchColumn();
        $pendingPayoutsCount = (int)$db->query("SELECT COUNT(*) FROM payouts WHERE status = 'pending'")->fetchColumn();
        $todayLiveEvents = (int)$db->query("SELECT COUNT(*) FROM events WHERE status = 'published' AND (DATE(date) = CURDATE() OR (date <= NOW() AND date >= NOW() - INTERVAL " . event_completion_hours() . " HOUR))")->fetchColumn();
        $cancelledEvents = (int)$db->query("SELECT COUNT(*) FROM events WHERE status = 'cancelled'")->fetchColumn();
        $totalStaffCount = (int)$db->query("SELECT COUNT(*) FROM event_staff WHERE status = 'active'")->fetchColumn();
        $totalBroadcastsCount = (int)$db->query("SELECT COUNT(*) FROM admin_broadcast_logs")->fetchColumn();
        $activeDeployments = (int)$db->query("SELECT COUNT(*) FROM event_staff_assignments")->fetchColumn();
        $migrationsCount = (int)$db->query("SELECT COUNT(*) FROM migrations")->fetchColumn();

        // 10. Growth Velocity & Ratios
        $platformProfitRate = $grossRevenue > 0 ? round(($platformProfit / $grossRevenue) * 100, 1) : 0;
        $hostVerificationRate = $totalHosts > 0 ? round(($readyHosts / $totalHosts) * 100, 1) : 0;

        $lastMonthKey = date('Y-m', strtotime('-1 month'));
        $lastMonthRevStmt = $db->prepare("SELECT COALESCE(SUM(total_amount), 0) FROM transactions WHERE type = 'payment' AND status = 'completed' AND DATE_FORMAT(created_at, '%Y-%m') = :m");
        $lastMonthRevStmt->execute(['m' => $lastMonthKey]);
        $lastMonthRevenue = (float)$lastMonthRevStmt->fetchColumn();
        $revenueGrowth = $lastMonthRevenue > 0 ? round((($monthRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100, 1) : ($monthRevenue > 0 ? 100.0 : 0.0);

        $lastMonthUserStmt = $db->prepare("SELECT COUNT(*) FROM users WHERE DATE_FORMAT(created_at, '%Y-%m') = :m");
        $lastMonthUserStmt->execute(['m' => $lastMonthKey]);
        $lastMonthUsers = (int)$lastMonthUserStmt->fetchColumn();
        $thisMonthUsers = end($monthlyUsers) ?: 0;
        $userGrowth = $lastMonthUsers > 0 ? round((($thisMonthUsers - $lastMonthUsers) / $lastMonthUsers) * 100, 1) : ($thisMonthUsers > 0 ? 100.0 : 0.0);

        $lastMonthTicketStmt = $db->prepare("SELECT COUNT(*) FROM tickets WHERE status IN ('Paid', 'Comped') AND DATE_FORMAT(purchase_date, '%Y-%m') = :m");
        $lastMonthTicketStmt->execute(['m' => $lastMonthKey]);
        $lastMonthTickets = (int)$lastMonthTicketStmt->fetchColumn();
        $thisMonthTickets = end($monthlyTickets) ?: 0;
        $ticketGrowth = $lastMonthTickets > 0 ? round((($thisMonthTickets - $lastMonthTickets) / $lastMonthTickets) * 100, 1) : ($thisMonthTickets > 0 ? 100.0 : 0.0);

        // 11. System Diagnostics for Super Admin
        $currentUser = Application::$app->user;
        $isSuperAdmin = false;
        if ($currentUser) {
            $isSuperAdmin = (method_exists($currentUser, 'isSuperAdmin') && $currentUser->isSuperAdmin()) 
                         || (method_exists($currentUser, 'hasRole') && $currentUser->hasRole('super-admin'))
                         || hasRole('super-admin');
        }

        $dbServerVersion = 'MySQL';
        try {
            $dbServerVersion = $db->getAttribute(\PDO::ATTR_SERVER_VERSION);
        } catch (\Throwable $e) {}

        $systemDiagnostics = [
            'php_version' => PHP_VERSION,
            'db_driver' => 'MySQL / MariaDB',
            'db_version' => $dbServerVersion,
            'environment' => Application::$app->config['env'] ?? 'production',
            'maintenance_mode' => site_setting('maintenance_mode', 'no'),
            'migrations_count' => $migrationsCount,
            'storage_writable' => is_writable(Application::$ROOT_DIR . '/storage'),
            'currency' => setting('currency', 'NGN'),
            'active_gateway' => setting('default_gateway', 'paystack'),
        ];

        return [
            'is_super_admin' => $isSuperAdmin,
            'admin_user' => $currentUser,
            'total_users' => $totalUsers,
            'total_hosts' => $totalHosts,
            'ready_hosts' => $readyHosts,
            'pending_hosts' => $pendingHosts,
            'host_verification_rate' => $hostVerificationRate,
            'total_events' => $totalEvents,
            'total_occurrences' => $totalOccurrences,
            'live_events' => $liveEvents,
            'completed_events' => $completedEvents,
            'draft_events' => $draftEvents,
            'cancelled_events' => $cancelledEvents,
            'total_tickets' => $totalTickets,
            'checked_in_tickets' => $checkedInTickets,
            'check_in_rate' => $checkInRate,
            'gross_revenue' => $grossRevenue,
            'total_ticket_sales' => $totalTicketSales,
            'total_activity_sales' => $totalActivitySales,
            'total_activity_fees' => $totalActivityFees,
            'total_activity_orders' => $totalActivityOrders,
            'total_issued_nominal' => $totalIssuedNominal,
            'platform_profit' => $platformProfit,
            'platform_profit_rate' => $platformProfitRate,
            'total_host_share' => $totalHostShare,
            'disbursed_payouts' => $disbursedPayouts,
            'pending_payouts' => $pendingPayoutRequests,
            'pending_requests_amount' => $pendingPayoutRequests,
            'escrow_revenue' => $totalEscrowBalance,
            'gross_escrow_revenue' => $totalUpcomingNetRevenue,
            'withdrawable_host_balance' => $withdrawableHostBalance,
            'total_undisbursed_host_funds' => $totalUndisbursedHostFunds,
            'unresolved_support' => $unresolvedSupport,
            'pending_staff_requests_count' => $pendingStaffRequestsCount,
            'pending_payouts_count' => $pendingPayoutsCount,
            'today_live_events' => $todayLiveEvents,
            'total_staff_count' => $totalStaffCount,
            'total_broadcasts_count' => $totalBroadcastsCount,
            'active_deployments' => $activeDeployments,
            'revenue_growth' => $revenueGrowth,
            'user_growth' => $userGrowth,
            'ticket_growth' => $ticketGrowth,
            'system_diagnostics' => $systemDiagnostics,
            'monthly_labels' => $monthlyLabels,
            'monthly_revenue' => $monthlyRevenue,
            'monthly_profit' => $monthlyProfit,
            'monthly_users' => $monthlyUsers,
            'monthly_tickets' => $monthlyTickets,
            'monthly_payouts' => $monthlyPayouts,
            'today_revenue' => $todayRevenue,
            'today_fees' => $todayFees,
            'week_revenue' => $weekRevenue,
            'week_fees' => $weekFees,
            'month_revenue' => $monthRevenue,
            'month_fees' => $monthFees,
            'month_payouts_total' => $monthPayouts,
            'total_transactions' => $totalTransactions,
            'avg_transaction_value' => $avgTransactionValue,
            'recent_events' => $recentEvents,
            'recent_hosts' => $recentHosts,
            'audit_trail' => $auditTrail,
            'recent_ticket_purchases' => $recentTicketPurchases,
            'today_tickets' => $todayTickets,
        ];
    }
}
