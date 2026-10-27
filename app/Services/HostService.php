<?php

namespace App\Services;

use Ace\Application;

class HostService
{
    /**
     * Get host dashboard statistics.
     */
    public function getDashboardStats(int|string $hostId): array
    {
        $db = Application::$app->db->pdo;

        // 1. Total Events count (count recurring series as one unique event)
        $stmt = $db->prepare("
            SELECT COUNT(*) FROM events e
            WHERE e.organizer_id = :organizer_id
              AND (
                  e.recurrence_group_id IS NULL 
                  OR e.recurrence_group_id = ''
                  OR e.recurrence_sequence = 1
              )
        ");
        $stmt->execute(['organizer_id' => $hostId]);
        $totalEventsCount = (int)$stmt->fetchColumn();

        // 1b. Total occurrences count (all individual event rows including recurring)
        $stmt = $db->prepare("SELECT COUNT(*) FROM events WHERE organizer_id = :organizer_id");
        $stmt->execute(['organizer_id' => $hostId]);
        $totalOccurrencesCount = (int)$stmt->fetchColumn();

        // 2. Total Gross Revenue (All time across all events - ticket face value of online purchases)
        $stmt = $db->prepare("
            SELECT SUM(t.price) FROM tickets t
            INNER JOIN events e ON t.event_id = e.id
            WHERE e.organizer_id = :organizer_id 
              AND t.status IN ('Paid', 'Comped')
              AND (t.is_issued = 0 OR t.is_issued IS NULL)
        ");
        $stmt->execute(['organizer_id' => $hostId]);
        $totalRevenue = (float)($stmt->fetchColumn() ?: 0.00);

        // 2b. Total Net Revenue (What the host is actually entitled to withdraw, after platform fee deduction)
        $stmt = $db->prepare("
            SELECT SUM(
                CASE WHEN e.pass_fee = 1 THEN t.price ELSE t.price * 0.95 END
            )
            FROM tickets t
            INNER JOIN events e ON t.event_id = e.id
            WHERE e.organizer_id = :organizer_id 
              AND t.status IN ('Paid', 'Comped')
              AND (t.is_issued = 0 OR t.is_issued IS NULL)
        ");
        $stmt->execute(['organizer_id' => $hostId]);
        $totalNetRevenue = (float)($stmt->fetchColumn() ?: 0.00);

        // 2c. Total platform fees deducted from this host's earnings
        $totalPlatformFees = max(0.00, $totalRevenue - $totalNetRevenue);

        // 3. Completed Events Net Share Revenue (Online ticket purchases for ended events)
        $stmt = $db->prepare("
            SELECT SUM(
                CASE WHEN e.pass_fee = 1 THEN t.price ELSE t.price * 0.95 END
            )
            FROM tickets t
            INNER JOIN events e ON t.event_id = e.id
            WHERE e.organizer_id = :organizer_id 
              AND t.status IN ('Paid', 'Comped')
              AND (t.is_issued = 0 OR t.is_issued IS NULL)
              AND (e.status IN ('completed', 'past') OR (e.status = 'published' AND e.date < NOW() - INTERVAL " . event_completion_hours() . " HOUR))
        ");
        $stmt->execute(['organizer_id' => $hostId]);
        $completedNetRevenue = (float)($stmt->fetchColumn() ?: 0.00);

        // 4. Upcoming Live Events Net Share Revenue (Online purchases locked in escrow until event completes)
        $stmt = $db->prepare("
            SELECT SUM(
                CASE WHEN e.pass_fee = 1 THEN t.price ELSE t.price * 0.95 END
            )
            FROM tickets t
            INNER JOIN events e ON t.event_id = e.id
            WHERE e.organizer_id = :organizer_id 
              AND t.status IN ('Paid', 'Comped')
              AND (t.is_issued = 0 OR t.is_issued IS NULL)
              AND e.date >= NOW() - INTERVAL " . event_completion_hours() . " HOUR AND e.status = 'published'
        ");
        $stmt->execute(['organizer_id' => $hostId]);
        $grossEscrowRevenue = (float)($stmt->fetchColumn() ?: 0.00);

        // 5. Tickets Sold (Online purchases)
        $stmt = $db->prepare("
            SELECT COUNT(*) FROM tickets t
            INNER JOIN events e ON t.event_id = e.id
            WHERE e.organizer_id = :organizer_id 
              AND t.status IN ('Paid', 'Comped')
              AND (t.is_issued = 0 OR t.is_issued IS NULL)
        ");
        $stmt->execute(['organizer_id' => $hostId]);
        $totalTicketsSold = (int)$stmt->fetchColumn();

        // 5b. Host Issued Tickets (Manual/Gate/Comp)
        $stmt = $db->prepare("
            SELECT COUNT(*) FROM tickets t
            INNER JOIN events e ON t.event_id = e.id
            WHERE e.organizer_id = :organizer_id 
              AND t.status IN ('Paid', 'Comped')
              AND t.is_issued = 1
        ");
        $stmt->execute(['organizer_id' => $hostId]);
        $totalTicketsIssued = (int)$stmt->fetchColumn();

        // 6. Active Events Count (unique series only)
        $completionHours = event_completion_hours();
        $stmt = $db->prepare("
            SELECT COUNT(*) FROM events e
            WHERE e.organizer_id = :organizer_id 
              AND e.date >= NOW() - INTERVAL {$completionHours} HOUR 
              AND e.status = 'published'
              AND (
                  e.recurrence_group_id IS NULL 
                  OR e.recurrence_group_id = ''
                  OR e.id = (
                      SELECT sub.id FROM events sub 
                      WHERE sub.recurrence_group_id = e.recurrence_group_id 
                        AND sub.status = 'published'
                        AND sub.date >= NOW() - INTERVAL {$completionHours} HOUR
                      ORDER BY sub.date ASC, sub.recurrence_sequence ASC, sub.id ASC
                      LIMIT 1
                  )
              )
        ");
        $stmt->execute(['organizer_id' => $hostId]);
        $activeEventsCount = (int)$stmt->fetchColumn();

        // 7. Payout Details
        $stmt = $db->prepare("SELECT SUM(amount) FROM payouts WHERE organizer_id = :organizer_id AND status IN ('processed', 'completed')");
        $stmt->execute(['organizer_id' => $hostId]);
        $disbursedPayouts = (float)($stmt->fetchColumn() ?: 0.00);

        $stmt = $db->prepare("SELECT SUM(amount) FROM payouts WHERE organizer_id = :organizer_id AND status IN ('pending', 'failed')");
        $stmt->execute(['organizer_id' => $hostId]);
        $pendingPayouts = (float)($stmt->fetchColumn() ?: 0.00);

        $totalPayoutsDeduction = $disbursedPayouts + $pendingPayouts;

        // Completed events net balance available
        $availableBalance = max(0.00, $completedNetRevenue - $totalPayoutsDeduction);

        // Escrow revenue available / remaining after payouts drawn from escrow
        $usedEscrow = max(0.00, $totalPayoutsDeduction - $completedNetRevenue);
        $remainingEscrowBalance = max(0.00, $grossEscrowRevenue - $usedEscrow);
        $availableEscrowBalance = $remainingEscrowBalance;

        // Total combined withdrawable balance (Completed + Escrow)
        $totalAvailableBalance = max(0.00, ($completedNetRevenue + $grossEscrowRevenue) - $totalPayoutsDeduction);

        return [
            'totalEventsCount' => $totalEventsCount,
            'totalOccurrencesCount' => $totalOccurrencesCount,
            'totalRevenue' => $totalRevenue,
            'totalNetRevenue' => $totalNetRevenue,
            'totalPlatformFees' => $totalPlatformFees,
            'completedNetRevenue' => $completedNetRevenue,
            'grossEscrowRevenue' => $grossEscrowRevenue,
            'escrowRevenue' => $remainingEscrowBalance,
            'totalTicketsSold' => $totalTicketsSold,
            'totalTicketsIssued' => $totalTicketsIssued,
            'activeEventsCount' => $activeEventsCount,
            'disbursedPayouts' => $disbursedPayouts,
            'pendingPayouts' => $pendingPayouts,
            'availableBalance' => $availableBalance,
            'availableEscrowBalance' => $availableEscrowBalance,
            'totalAvailableBalance' => $totalAvailableBalance
        ];
    }

    /**
     * Get host statistics for settings view.
     */
    public function getSettingsStats(int|string $hostId): array
    {
        $db = Application::$app->db->pdo;

        $stmt = $db->prepare("SELECT COUNT(*) FROM events WHERE organizer_id = :organizer_id AND status = 'published' AND date >= NOW() - INTERVAL " . event_completion_hours() . " HOUR");
        $stmt->execute(['organizer_id' => $hostId]);
        $liveEventsCount = (int)$stmt->fetchColumn();

        $stmt = $db->prepare("SELECT SUM(amount) FROM payouts WHERE organizer_id = :organizer_id AND status = 'pending'");
        $stmt->execute(['organizer_id' => $hostId]);
        $pendingPayout = (float)($stmt->fetchColumn() ?: 0.00);

        $stmt = $db->prepare("SELECT COUNT(*) FROM tickets t INNER JOIN events e ON t.event_id = e.id WHERE e.organizer_id = :organizer_id AND t.checked_in = 1");
        $stmt->execute(['organizer_id' => $hostId]);
        $checkedInCount = (int)$stmt->fetchColumn();

        return [
            'liveEventsCount' => $liveEventsCount,
            'pendingPayout' => $pendingPayout,
            'checkedInCount' => $checkedInCount
        ];
    }

    /**
     * Get detailed per-event financial breakdown for a host.
     */
    public function getHostEventsFinancials(int|string $hostId): array
    {
        $db = Application::$app->db->pdo;

        $completionHours = event_completion_hours();
        $stmt = $db->prepare("
            SELECT e.id, e.name, e.date, e.status, e.pass_fee, e.location, e.image,
                   COUNT(CASE WHEN (t.is_issued = 0 OR t.is_issued IS NULL) THEN t.id END) as ticket_count,
                   COUNT(CASE WHEN t.is_issued = 1 THEN t.id END) as issued_count,
                   COALESCE(SUM(CASE WHEN (t.is_issued = 0 OR t.is_issued IS NULL) THEN t.price ELSE 0 END), 0) as gross_sales,
                   COALESCE(SUM(CASE WHEN (t.is_issued = 0 OR t.is_issued IS NULL) THEN (CASE WHEN e.pass_fee = 1 THEN t.price ELSE t.price * 0.95 END) ELSE 0 END), 0) as net_sales,
                   COALESCE(SUM(CASE WHEN t.is_issued = 1 THEN t.price ELSE 0 END), 0) as issued_nominal
            FROM events e
            LEFT JOIN tickets t ON e.id = t.event_id AND t.status IN ('Paid', 'Comped')
            WHERE e.organizer_id = :host_id
            GROUP BY e.id
            ORDER BY 
                CASE
                    -- 1. Live Happening Now
                    WHEN e.status = 'published' AND e.date <= NOW() AND e.date >= NOW() - INTERVAL {$completionHours} HOUR THEN 1
                    -- 2. Upcoming Active Events
                    WHEN e.status = 'published' AND e.date > NOW() THEN 2
                    -- 3. Draft / Non-completed
                    WHEN e.status NOT IN ('completed', 'cancelled') AND e.date >= NOW() THEN 3
                    -- 4. Completed or Past Events
                    ELSE 4
                END ASC,
                CASE WHEN e.date > NOW() THEN e.date END ASC,
                e.date DESC,
                e.id DESC
        ");
        $stmt->execute(['host_id' => $hostId]);
        $events = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];

        $now = time();
        $completionHours = event_completion_hours();

        foreach ($events as &$evt) {
            $eid = (int)$evt['id'];
            $evtDate = strtotime($evt['date'] ?? '');
            $liveWindowEnd = $evtDate ? $evtDate + ($completionHours * 3600) : 0;

            if (($evt['status'] ?? '') === 'completed' || ($evtDate && $now > $liveWindowEnd)) {
                $evt['state_badge'] = 'completed';
                $evt['state_label'] = '✓ Completed';
            } elseif ($evtDate && $now >= $evtDate && $now <= $liveWindowEnd) {
                $evt['state_badge'] = 'live';
                $evt['state_label'] = '🔴 Live Now';
            } else {
                $evt['state_badge'] = 'upcoming';
                $evt['state_label'] = '📅 Upcoming';
            }

            // Payouts breakdown for this event
            $pStmt = $db->prepare("
                SELECT 
                    COALESCE(SUM(CASE WHEN type = 'escrow' AND status IN ('processed', 'completed') THEN amount ELSE 0 END), 0) as escrow_disbursed,
                    COALESCE(SUM(CASE WHEN type != 'escrow' AND status IN ('processed', 'completed') THEN amount ELSE 0 END), 0) as completed_disbursed,
                    COALESCE(SUM(CASE WHEN status IN ('processed', 'completed') THEN amount ELSE 0 END), 0) as total_disbursed,
                    COALESCE(SUM(CASE WHEN status = 'pending' THEN amount ELSE 0 END), 0) as pending_amount
                FROM payouts
                WHERE event_id = :event_id
            ");
            $pStmt->execute(['event_id' => $eid]);
            $pData = $pStmt->fetch(\PDO::FETCH_ASSOC) ?: [];

            $evt['gross_sales'] = (float)$evt['gross_sales'];
            $evt['net_sales'] = (float)$evt['net_sales'];
            $evt['issued_count'] = (int)($evt['issued_count'] ?? 0);
            $evt['issued_nominal'] = (float)($evt['issued_nominal'] ?? 0.00);
            $evt['platform_fees'] = max(0.00, $evt['gross_sales'] - $evt['net_sales']);
            $evt['escrow_disbursed'] = (float)($pData['escrow_disbursed'] ?? 0.00);
            $evt['completed_disbursed'] = (float)($pData['completed_disbursed'] ?? 0.00);
            $evt['total_disbursed'] = (float)($pData['total_disbursed'] ?? 0.00);
            $evt['pending_amount'] = (float)($pData['pending_amount'] ?? 0.00);
            $evt['unrequested_balance'] = max(0.00, round($evt['net_sales'] - ($evt['total_disbursed'] + $evt['pending_amount']), 2));
        }
        unset($evt);

        return $events;
    }

    /**
     * Get full payout and withdrawal transaction history for a host.
     */
    public function getHostPayoutTransactions(int|string $hostId, int $limit = 50): array
    {
        $db = Application::$app->db->pdo;

        $stmt = $db->prepare("
            SELECT p.*, e.name as event_name, e.date as event_date, e.pass_fee, e.status as event_status,
                   hp.payout_bank, hp.payout_account
            FROM payouts p
            LEFT JOIN events e ON p.event_id = e.id
            LEFT JOIN host_profiles hp ON p.organizer_id = hp.user_id
            WHERE p.organizer_id = :host_id
            ORDER BY p.created_at DESC, p.id DESC
            LIMIT :limit
        ");
        $stmt->bindValue(':host_id', $hostId, \PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Get monthly sales trend for the last 6 months.
     */
    public function getMonthlyRevenueTrend(int|string $hostId): array
    {
        $db = Application::$app->db->pdo;

        // Generate last 6 months
        $months = [];
        for ($i = 5; $i >= 0; $i--) {
            $key = date('Y-m', strtotime("-$i months"));
            $label = date('M Y', strtotime("-$i months"));
            $months[$key] = [
                'key' => $key,
                'label' => $label,
                'short_label' => date('M', strtotime("-$i months")),
                'gross' => 0.00,
                'net' => 0.00,
                'tickets' => 0
            ];
        }

        $stmt = $db->prepare("
            SELECT DATE_FORMAT(COALESCE(t.purchase_date, NOW()), '%Y-%m') as month_key,
                   COUNT(t.id) as ticket_count,
                   COALESCE(SUM(t.price), 0) as gross_sales,
                   COALESCE(SUM(CASE WHEN e.pass_fee = 1 THEN t.price ELSE t.price * 0.95 END), 0) as net_sales
            FROM tickets t
            INNER JOIN events e ON t.event_id = e.id
            WHERE e.organizer_id = :host_id
              AND t.status IN ('Paid', 'Comped')
              AND (t.is_issued = 0 OR t.is_issued IS NULL)
              AND COALESCE(t.purchase_date, NOW()) >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
            GROUP BY DATE_FORMAT(COALESCE(t.purchase_date, NOW()), '%Y-%m')
        ");
        $stmt->execute(['host_id' => $hostId]);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];

        foreach ($rows as $row) {
            $mKey = $row['month_key'];
            if (isset($months[$mKey])) {
                $months[$mKey]['gross'] = (float)$row['gross_sales'];
                $months[$mKey]['net'] = (float)$row['net_sales'];
                $months[$mKey]['tickets'] = (int)$row['ticket_count'];
            }
        }

        return array_values($months);
    }

    /**
     * Get ticket tier sales breakdown.
     */
    public function getTicketTierBreakdown(int|string $hostId): array
    {
        $db = Application::$app->db->pdo;

        $stmt = $db->prepare("
            SELECT COALESCE(tt.name, 'Standard') as tier_name,
                   COUNT(t.id) as tickets_sold,
                   COALESCE(SUM(t.price), 0) as total_revenue
            FROM tickets t
            INNER JOIN events e ON t.event_id = e.id
            LEFT JOIN ticket_types tt ON t.ticket_type_id = tt.id
            WHERE e.organizer_id = :host_id
              AND t.status IN ('Paid', 'Comped')
              AND (t.is_issued = 0 OR t.is_issued IS NULL)
            GROUP BY COALESCE(tt.name, 'Standard')
            ORDER BY total_revenue DESC
            LIMIT 5
        ");
        $stmt->execute(['host_id' => $hostId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Get recent ticket purchase stream.
     */
    public function getRecentTicketOrders(int|string $hostId, int $limit = 8): array
    {
        $db = Application::$app->db->pdo;

        $stmt = $db->prepare("
            SELECT t.id, t.attendee_name, t.attendee_email, t.price, t.status, t.checked_in, t.purchase_date,
                   e.id as event_id, e.name as event_name, e.status as event_status, e.date as event_date,
                   COALESCE(tt.name, 'General Admission') as tier_name
            FROM tickets t
            INNER JOIN events e ON t.event_id = e.id
            LEFT JOIN ticket_types tt ON t.ticket_type_id = tt.id
            WHERE e.organizer_id = :host_id
              AND t.status IN ('Paid', 'Comped')
            ORDER BY t.purchase_date DESC, t.id DESC
            LIMIT :limit
        ");
        $stmt->bindValue(':host_id', $hostId, \PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Get next upcoming or currently live event with live check-in and sales progress.
     */
    public function getNextUpcomingEvent(int|string $hostId): ?array
    {
        $db = Application::$app->db->pdo;
        $completionHours = event_completion_hours();

        $stmt = $db->prepare("
            SELECT e.id, e.name, e.date, e.location, e.image, e.status, e.pass_fee,
                   COUNT(CASE WHEN (t.is_issued = 0 OR t.is_issued IS NULL) THEN t.id END) as sold_count,
                   COUNT(CASE WHEN t.is_issued = 1 THEN t.id END) as issued_count,
                   COALESCE(SUM(CASE WHEN (t.is_issued = 0 OR t.is_issued IS NULL) THEN t.price ELSE 0 END), 0) as gross_sales,
                   COALESCE(SUM(CASE WHEN (t.is_issued = 0 OR t.is_issued IS NULL) THEN (CASE WHEN e.pass_fee = 1 THEN t.price ELSE t.price * 0.95 END) ELSE 0 END), 0) as net_sales,
                   COALESCE(SUM(CASE WHEN t.checked_in = 1 THEN 1 ELSE 0 END), 0) as checked_in_count,
                   (SELECT COALESCE(SUM(capacity), 0) FROM ticket_types WHERE event_id = e.id) as total_capacity
            FROM events e
            LEFT JOIN tickets t ON e.id = t.event_id AND t.status IN ('Paid', 'Comped')
            WHERE e.organizer_id = :host_id
              AND e.status = 'published'
              AND e.date >= NOW() - INTERVAL " . (int)$completionHours . " HOUR
            GROUP BY e.id
            ORDER BY e.date ASC
            LIMIT 1
        ");
        $stmt->execute(['host_id' => $hostId]);
        $event = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$event) {
            return null;
        }

        $eventDate = strtotime($event['date']);
        $now = time();
        $liveWindowEnd = $eventDate + ($completionHours * 3600);

        $event['is_live_now'] = ($now >= $eventDate && $now <= $liveWindowEnd);
        $event['timestamp'] = $eventDate;
        $event['sold_count'] = (int)$event['sold_count'];
        $event['issued_count'] = (int)($event['issued_count'] ?? 0);
        $event['total_capacity'] = (int)$event['total_capacity'];
        $event['checked_in_count'] = (int)$event['checked_in_count'];
        $event['capacity_pct'] = $event['total_capacity'] > 0 ? min(100, round((($event['sold_count'] + $event['issued_count']) / $event['total_capacity']) * 100, 1)) : 0;
        $event['checkin_pct'] = ($event['sold_count'] + $event['issued_count']) > 0 ? min(100, round(($event['checked_in_count'] / ($event['sold_count'] + $event['issued_count'])) * 100, 1)) : 0;

        return $event;
    }

    /**
     * Get the most recently completed event with final revenue and attendance stats.
     */
    public function getLastCompletedEvent(int|string $hostId): ?array
    {
        $db = Application::$app->db->pdo;
        $completionHours = event_completion_hours();

        $stmt = $db->prepare("
            SELECT e.id, e.name, e.date, e.location, e.image, e.status, e.pass_fee,
                   COUNT(CASE WHEN (t.is_issued = 0 OR t.is_issued IS NULL) THEN t.id END) as sold_count,
                   COUNT(CASE WHEN t.is_issued = 1 THEN t.id END) as issued_count,
                   COALESCE(SUM(CASE WHEN (t.is_issued = 0 OR t.is_issued IS NULL) THEN t.price ELSE 0 END), 0) as gross_sales,
                   COALESCE(SUM(CASE WHEN (t.is_issued = 0 OR t.is_issued IS NULL) THEN (CASE WHEN e.pass_fee = 1 THEN t.price ELSE t.price * 0.95 END) ELSE 0 END), 0) as net_sales,
                   COALESCE(SUM(CASE WHEN t.checked_in = 1 THEN 1 ELSE 0 END), 0) as checked_in_count,
                   (SELECT COALESCE(SUM(capacity), 0) FROM ticket_types WHERE event_id = e.id) as total_capacity
            FROM events e
            LEFT JOIN tickets t ON e.id = t.event_id AND t.status IN ('Paid', 'Comped')
            WHERE e.organizer_id = :host_id
              AND (e.status IN ('completed', 'past') OR (e.status = 'published' AND e.date < NOW() - INTERVAL " . (int)$completionHours . " HOUR))
            GROUP BY e.id
            ORDER BY e.date DESC, e.id DESC
            LIMIT 1
        ");
        $stmt->execute(['host_id' => $hostId]);
        $event = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$event) {
            return null;
        }

        $event['timestamp'] = strtotime($event['date']);
        $event['sold_count'] = (int)$event['sold_count'];
        $event['total_capacity'] = (int)$event['total_capacity'];
        $event['checked_in_count'] = (int)$event['checked_in_count'];
        $event['capacity_pct'] = $event['total_capacity'] > 0 ? min(100, round(($event['sold_count'] / $event['total_capacity']) * 100, 1)) : 0;
        $event['checkin_pct'] = $event['sold_count'] > 0 ? min(100, round(($event['checked_in_count'] / $event['sold_count']) * 100, 1)) : 0;

        return $event;
    }
}
