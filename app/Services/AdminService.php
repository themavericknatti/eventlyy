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
        $grossRevenue = (float)$db->query("
            SELECT COALESCE(SUM(total_amount), 0) FROM transactions WHERE type = 'payment' AND status = 'completed'
        ")->fetchColumn();

        if ($grossRevenue == 0) {
            $grossRevenue = (float)$db->query("
                SELECT COALESCE(SUM(price), 0) 
                FROM tickets 
                WHERE status = 'Paid'
                  AND (is_issued = 0 OR is_issued IS NULL)
            ")->fetchColumn();
        }

        $platformProfit = (float)$db->query("
            SELECT COALESCE(SUM(fee_amount + extra_charge_amount), 0) FROM transactions WHERE type = 'payment' AND status = 'completed'
        ")->fetchColumn();

        if ($platformProfit == 0 && $grossRevenue > 0) {
            $platformProfit = (float)$db->query("
                SELECT COALESCE(SUM(t.price * 0.05), 0)
                FROM tickets t
                INNER JOIN events e ON t.event_id = e.id
                WHERE t.status = 'Paid'
                  AND (t.is_issued = 0 OR t.is_issued IS NULL)
            ")->fetchColumn();
        }

        $totalHostShare = (float)$db->query("
            SELECT COALESCE(SUM(
                CASE WHEN e.pass_fee = 1 THEN t.price ELSE t.price * 0.95 END
            ), 0)
            FROM tickets t
            INNER JOIN events e ON t.event_id = e.id
            WHERE t.status = 'Paid'
              AND (t.is_issued = 0 OR t.is_issued IS NULL)
        ")->fetchColumn();

        $disbursedPayouts = (float)$db->query("
            SELECT COALESCE(SUM(amount), 0) FROM payouts WHERE status IN ('processed', 'completed')
        ")->fetchColumn();

        $pendingPayoutRequests = (float)$db->query("
            SELECT COALESCE(SUM(amount), 0) FROM payouts WHERE status = 'pending'
        ")->fetchColumn();

        $escrowRevenue = (float)$db->query("
            SELECT COALESCE(SUM(
                CASE WHEN e.pass_fee = 1 THEN t.price ELSE t.price * 0.95 END
            ), 0)
            FROM tickets t
            INNER JOIN events e ON t.event_id = e.id
            WHERE t.status = 'Paid'
              AND (t.is_issued = 0 OR t.is_issued IS NULL)
              AND e.status = 'published'
              AND e.date >= NOW() - INTERVAL " . event_completion_hours() . " HOUR
        ")->fetchColumn();

        $totalUndisbursedHostFunds = max(0.00, $totalHostShare - $disbursedPayouts);
        $withdrawableHostBalance = max(0.00, $totalUndisbursedHostFunds - $escrowRevenue);
        $totalEscrowBalance = max(0.00, min($totalUndisbursedHostFunds, $escrowRevenue));

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
        $pendingStaffRequestsCount = (int)$db->query("SELECT COUNT(*) FROM event_staff_requests WHERE status IN ('pending', 'partially_fulfilled')")->fetchColumn();
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
            'platform_profit' => $platformProfit,
            'platform_profit_rate' => $platformProfitRate,
            'disbursed_payouts' => $disbursedPayouts,
            'pending_payouts' => $totalEscrowBalance,
            'escrow_revenue' => $escrowRevenue,
            'pending_requests_amount' => $pendingPayoutRequests,
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
