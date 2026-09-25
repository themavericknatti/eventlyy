<?php

namespace App\Services;

use Ace\Application;

class AdminPayoutService
{
    /**
     * Get statistics for payouts.
     */
    public function getPayoutStats(): array
    {
        $db = Application::$app->db->pdo;

        $pendingPayouts = (float)($db->query("SELECT SUM(amount) FROM payouts WHERE status = 'pending'")->fetchColumn() ?: 0.00);
        $completedPayouts = (float)($db->query("SELECT SUM(amount) FROM payouts WHERE status IN ('processed', 'completed')")->fetchColumn() ?: 0.00);
        
        $pendingCount = (int)$db->query("SELECT COUNT(*) FROM payouts WHERE status = 'pending'")->fetchColumn();
        $completedCount = (int)$db->query("SELECT COUNT(*) FROM payouts WHERE status IN ('processed', 'completed')")->fetchColumn();

        $hostsReady = (int)$db->query("
            SELECT COUNT(DISTINCT p.organizer_id)
            FROM payouts p
            INNER JOIN host_profiles hp ON p.organizer_id = hp.user_id
            WHERE p.status = 'pending' 
              AND hp.payout_bank IS NOT NULL AND hp.payout_bank != ''
              AND hp.payout_account IS NOT NULL AND hp.payout_account != ''
        ")->fetchColumn();

        $hostsFlagged = (int)$db->query("SELECT COUNT(DISTINCT organizer_id) FROM payouts WHERE status = 'failed'")->fetchColumn();
        $totalPayoutsCount = (int)$db->query("SELECT COUNT(*) FROM payouts")->fetchColumn();

        return [
            'pending_payouts' => $pendingPayouts,
            'completed_payouts' => $completedPayouts,
            'pending_count' => $pendingCount,
            'completed_count' => $completedCount,
            'hosts_ready' => $hostsReady,
            'hosts_flagged' => $hostsFlagged,
            'total_payouts_count' => $totalPayoutsCount
        ];
    }

    /**
     * Get a filtered and paginated list of payouts.
     */
    public function getFilteredPayouts(string $search = '', string $status = '', string $payoutType = '', int $page = 1, int $limit = 10): array
    {
        $db = Application::$app->db->pdo;

        $conditions = [];
        $binds = [];

        if (!empty($search)) {
            $conditions[] = "(u.name LIKE ? OR u.email LIKE ? OR hp.payout_bank LIKE ? OR hp.payout_account LIKE ? OR e.name LIKE ? OR p.id = ?)";
            $binds[] = "%$search%";
            $binds[] = "%$search%";
            $binds[] = "%$search%";
            $binds[] = "%$search%";
            $binds[] = "%$search%";
            $binds[] = $search;
        }

        if ($status === 'flagged' || $status === 'failed') {
            $conditions[] = "p.status = 'failed'";
        } elseif ($status === 'ready' || $status === 'pending') {
            $conditions[] = "p.status = 'pending'";
        } elseif ($status === 'completed' || $status === 'processed') {
            $conditions[] = "p.status IN ('processed', 'completed')";
        }

        if (!empty($payoutType)) {
            $conditions[] = "p.type = ?";
            $binds[] = $payoutType;
        }

        $whereClause = !empty($conditions) ? implode(' AND ', $conditions) : '1=1';

        // 1. Get total count
        $countStmt = $db->prepare("
            SELECT COUNT(*)
            FROM payouts p
            INNER JOIN users u ON p.organizer_id = u.id
            LEFT JOIN events e ON p.event_id = e.id
            LEFT JOIN host_profiles hp ON u.id = hp.user_id
            WHERE {$whereClause}
        ");
        $countStmt->execute($binds);
        $total = (int)$countStmt->fetchColumn();

        // 2. Calculate offset
        $offset = ($page - 1) * $limit;

        // 3. Get paginated records
        $stmt = $db->prepare("
            SELECT p.*, u.name as host_name, u.email as host_email,
                   e.name as event_name, e.status as event_status, e.date as event_date, e.pass_fee,
                   hp.payout_bank, hp.payout_account, hp.payout_routing
            FROM payouts p
            INNER JOIN users u ON p.organizer_id = u.id
            LEFT JOIN events e ON p.event_id = e.id
            LEFT JOIN host_profiles hp ON u.id = hp.user_id
            WHERE {$whereClause}
            ORDER BY p.created_at DESC, p.id DESC
            LIMIT ? OFFSET ?
        ");

        $paramIdx = 1;
        foreach ($binds as $val) {
            $stmt->bindValue($paramIdx++, $val, \PDO::PARAM_STR);
        }
        $stmt->bindValue($paramIdx++, $limit, \PDO::PARAM_INT);
        $stmt->bindValue($paramIdx++, $offset, \PDO::PARAM_INT);
        $stmt->execute();
        $items = $stmt->fetchAll() ?: [];

        $items = $this->enrichPayoutsWithEventMetrics($items);

        return [
            'items' => $items,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'totalPages' => (int)ceil($total / $limit)
        ];
    }

    /**
     * Get all matching payouts for CSV export.
     */
    public function getAllPayoutsForExport(string $search = '', string $status = '', string $payoutType = ''): array
    {
        $db = Application::$app->db->pdo;

        $conditions = [];
        $binds = [];

        if (!empty($search)) {
            $conditions[] = "(u.name LIKE ? OR u.email LIKE ? OR hp.payout_bank LIKE ? OR hp.payout_account LIKE ? OR e.name LIKE ? OR p.id = ?)";
            $binds[] = "%$search%";
            $binds[] = "%$search%";
            $binds[] = "%$search%";
            $binds[] = "%$search%";
            $binds[] = "%$search%";
            $binds[] = $search;
        }

        if ($status === 'flagged' || $status === 'failed') {
            $conditions[] = "p.status = 'failed'";
        } elseif ($status === 'ready' || $status === 'pending') {
            $conditions[] = "p.status = 'pending'";
        } elseif ($status === 'completed' || $status === 'processed') {
            $conditions[] = "p.status IN ('processed', 'completed')";
        }

        if (!empty($payoutType)) {
            $conditions[] = "p.type = ?";
            $binds[] = $payoutType;
        }

        $whereClause = !empty($conditions) ? implode(' AND ', $conditions) : '1=1';

        $stmt = $db->prepare("
            SELECT p.*, u.name as host_name, u.email as host_email,
                   e.name as event_name, e.status as event_status, e.date as event_date, e.pass_fee,
                   hp.payout_bank, hp.payout_account, hp.payout_routing
            FROM payouts p
            INNER JOIN users u ON p.organizer_id = u.id
            LEFT JOIN events e ON p.event_id = e.id
            LEFT JOIN host_profiles hp ON u.id = hp.user_id
            WHERE {$whereClause}
            ORDER BY p.created_at DESC, p.id DESC
        ");
        $stmt->execute($binds);
        $items = $stmt->fetchAll() ?: [];

        return $this->enrichPayoutsWithEventMetrics($items);
    }

    /**
     * Enrich payout records with real-time event revenue, prior disbursements, and remainder balance.
     */
    protected function enrichPayoutsWithEventMetrics(array $items): array
    {
        $db = Application::$app->db->pdo;

        foreach ($items as &$item) {
            $eid = (int)($item['event_id'] ?? 0);
            if ($eid > 0) {
                // Fetch event revenue stats
                $tStmt = $db->prepare("
                    SELECT COUNT(t.id) as ticket_count,
                           COALESCE(SUM(t.price), 0) as gross_sales,
                           COALESCE(SUM(CASE WHEN e.pass_fee = 1 THEN t.price ELSE t.price * 0.95 END), 0) as net_sales
                    FROM events e
                    LEFT JOIN tickets t ON e.id = t.event_id AND t.status IN ('Paid', 'Comped')
                    WHERE e.id = :eid
                    GROUP BY e.id
                ");
                $tStmt->execute(['eid' => $eid]);
                $tData = $tStmt->fetch(\PDO::FETCH_ASSOC);

                $item['event_tickets_count'] = (int)($tData['ticket_count'] ?? 0);
                $item['event_gross_sales'] = (float)($tData['gross_sales'] ?? 0.00);
                $item['event_net_sales'] = (float)($tData['net_sales'] ?? 0.00);

                // Prior disbursements for this event excluding current payout
                $pStmt = $db->prepare("
                    SELECT COALESCE(SUM(amount), 0)
                    FROM payouts
                    WHERE event_id = :eid AND status IN ('processed', 'completed') AND id != :current_id
                ");
                $pStmt->execute(['eid' => $eid, 'current_id' => $item['id']]);
                $item['event_prior_disbursed'] = (float)($pStmt->fetchColumn() ?: 0.00);

                // Total disbursed including all completed
                $totStmt = $db->prepare("
                    SELECT COALESCE(SUM(amount), 0)
                    FROM payouts
                    WHERE event_id = :eid AND status IN ('processed', 'completed')
                ");
                $totStmt->execute(['eid' => $eid]);
                $item['event_total_disbursed'] = (float)($totStmt->fetchColumn() ?: 0.00);

                $item['is_partial'] = ($item['event_prior_disbursed'] > 0 || (round($item['amount'], 2) < round($item['event_net_sales'] - 0.01, 2)));
                $item['event_remaining_balance'] = max(0.00, round($item['event_net_sales'] - $item['event_total_disbursed'], 2));
            } else {
                $item['event_tickets_count'] = 0;
                $item['event_gross_sales'] = (float)$item['amount'];
                $item['event_net_sales'] = (float)$item['amount'];
                $item['event_prior_disbursed'] = 0.00;
                $item['event_total_disbursed'] = (float)$item['amount'];
                $item['is_partial'] = false;
                $item['event_remaining_balance'] = 0.00;
            }
        }
        unset($item);

        return $items;
    }
}
