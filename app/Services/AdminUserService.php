<?php

namespace App\Services;

use Ace\Application;

class AdminUserService
{
    /**
     * Get statistics for all users.
     */
    public function getUserStats(): array
    {
        $db = Application::$app->db->pdo;

        $totalUsers = (int)$db->query("SELECT COUNT(*) FROM users")->fetchColumn();
        
        $totalHosts = (int)$db->query("
            SELECT COUNT(DISTINCT u.id) 
            FROM users u
            INNER JOIN user_roles ur ON u.id = ur.user_id 
            INNER JOIN roles r ON ur.role_id = r.id 
            WHERE r.slug = 'event-organizer'
        ")->fetchColumn();

        $verifiedHosts = (int)$db->query("
            SELECT COUNT(DISTINCT u.id) 
            FROM users u
            INNER JOIN user_roles ur ON u.id = ur.user_id 
            INNER JOIN roles r ON ur.role_id = r.id 
            WHERE r.slug = 'event-organizer' AND u.email_verified_at IS NOT NULL
        ")->fetchColumn();

        $unverifiedHosts = (int)$db->query("
            SELECT COUNT(DISTINCT u.id) 
            FROM users u
            INNER JOIN user_roles ur ON u.id = ur.user_id 
            INNER JOIN roles r ON ur.role_id = r.id 
            WHERE r.slug = 'event-organizer' AND u.email_verified_at IS NULL
        ")->fetchColumn();

        $verifiedAccounts = (int)$db->query("SELECT COUNT(*) FROM users WHERE email_verified_at IS NOT NULL")->fetchColumn();
        $unverifiedAccounts = (int)$db->query("SELECT COUNT(*) FROM users WHERE email_verified_at IS NULL")->fetchColumn();
        $suspendedUsers = (int)$db->query("SELECT COUNT(*) FROM users WHERE status = 'suspended'")->fetchColumn();

        return [
            'total_users' => $totalUsers,
            'total_hosts' => $totalHosts,
            'verified_hosts' => $verifiedHosts,
            'unverified_hosts' => $unverifiedHosts,
            'verified_accounts' => $verifiedAccounts,
            'unverified_accounts' => $unverifiedAccounts,
            'suspended_users' => $suspendedUsers
        ];
    }

    /**
     * Get a filtered and paginated list of users.
     */
    public function getFilteredUsers(string $search = '', string $role = '', string $status = '', string $verification = '', int $page = 1, int $limit = 10): array
    {
        $db = Application::$app->db->pdo;

        $conditions = ["1=1"];
        $binds = [];

        if (!empty($search)) {
            $conditions[] = "(u.name LIKE ? OR u.email LIKE ?)";
            $binds[] = "%$search%";
            $binds[] = "%$search%";
        }

        if ($role === 'host') {
            $conditions[] = "r.slug = 'event-organizer'";
        } elseif ($role === 'unverified_host') {
            $conditions[] = "r.slug = 'event-organizer' AND u.email_verified_at IS NULL";
        } elseif ($role === 'verified_host') {
            $conditions[] = "r.slug = 'event-organizer' AND u.email_verified_at IS NOT NULL";
        } elseif ($role === 'attendee') {
            $conditions[] = "r.slug = 'user'";
        } elseif ($role === 'admin') {
            $conditions[] = "r.slug IN ('admin', 'super-admin')";
        }

        if ($status === 'active') {
            $conditions[] = "u.status = 'active'";
        } elseif ($status === 'suspended') {
            $conditions[] = "u.status = 'suspended'";
        }

        if ($verification === 'verified') {
            $conditions[] = "u.email_verified_at IS NOT NULL";
        } elseif ($verification === 'unverified') {
            $conditions[] = "u.email_verified_at IS NULL";
        } elseif ($verification === 'unverified_hosts') {
            $conditions[] = "r.slug = 'event-organizer' AND u.email_verified_at IS NULL";
        }

        $whereClause = implode(' AND ', $conditions);

        // 1. Get total count
        $countStmt = $db->prepare("
            SELECT COUNT(DISTINCT u.id) 
            FROM users u
            LEFT JOIN user_roles ur ON u.id = ur.user_id
            LEFT JOIN roles r ON ur.role_id = r.id
            WHERE {$whereClause}
        ");
        $countStmt->execute($binds);
        $total = (int)$countStmt->fetchColumn();

        // 2. Calculate offset
        $offset = ($page - 1) * $limit;

        // 3. Get paginated records
        $stmt = $db->prepare("
            SELECT u.id, u.name, u.email, u.phone, u.status, u.email_verified_at, u.created_at,
                   r.name as role_name, r.slug as role_slug
            FROM users u
            LEFT JOIN user_roles ur ON u.id = ur.user_id
            LEFT JOIN roles r ON ur.role_id = r.id
            WHERE {$whereClause}
            ORDER BY u.created_at DESC
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

        return [
            'items' => $items,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'totalPages' => (int)ceil($total / $limit)
        ];
    }

    /**
     * Get details for a specific user.
     */
    public function getUserDetail(int|string $userId): ?array
    {
        $db = Application::$app->db->pdo;

        // Fetch basic user and role info
        $stmt = $db->prepare("
            SELECT u.id, u.name, u.email, u.phone, u.status, u.email_verified_at, u.created_at,
                   r.name as role_name, r.slug as role_slug
            FROM users u
            LEFT JOIN user_roles ur ON u.id = ur.user_id
            LEFT JOIN roles r ON ur.role_id = r.id
            WHERE u.id = :id
            LIMIT 1
        ");
        $stmt->execute(['id' => $userId]);
        $user = $stmt->fetch();

        if (!$user) {
            return null;
        }

        $user['joined_at'] = !empty($user['created_at']) ? date('M d, Y', strtotime($user['created_at'])) : 'N/A';

        // Add email verification info
        $user['is_verified'] = !empty($user['email_verified_at']);
        $user['email_verified_at_formatted'] = !empty($user['email_verified_at'])
            ? date('M d, Y \a\t h:i A', strtotime($user['email_verified_at']))
            : null;
        // Hosts cannot create events unless verified
        $isHost = ($user['role_slug'] ?? '') === 'event-organizer';
        $user['can_create_events'] = !$isHost || $user['is_verified'];

        $roleSlug = $user['role_slug'] ?? '';
        $stats = [];

        if ($roleSlug === 'event-organizer') {
            // Host details
            // Fetch profile
            $profileStmt = $db->prepare("SELECT * FROM host_profiles WHERE user_id = :id LIMIT 1");
            $profileStmt->execute(['id' => $userId]);
            $profile = $profileStmt->fetch() ?: [];

            // Fetch events count
            $evtCountStmt = $db->prepare("SELECT COUNT(*) FROM events WHERE organizer_id = :id");
            $evtCountStmt->execute(['id' => $userId]);
            $totalEvents = (int)$evtCountStmt->fetchColumn();

            // Fetch tickets sold and total revenue (online platform purchases only)
            $ticketsRevenueStmt = $db->prepare("
                SELECT COUNT(CASE WHEN (t.is_issued = 0 OR t.is_issued IS NULL) THEN t.id END) as sold_count,
                       SUM(CASE WHEN (t.is_issued = 0 OR t.is_issued IS NULL) THEN (CASE WHEN e.pass_fee = 1 THEN t.price ELSE t.price * 0.95 END) ELSE 0 END) as revenue
                FROM tickets t
                INNER JOIN events e ON t.event_id = e.id
                WHERE e.organizer_id = :id AND t.status IN ('Paid', 'Comped')
            ");
            $ticketsRevenueStmt->execute(['id' => $userId]);
            $ticketData = $ticketsRevenueStmt->fetch();
            $ticketsSold = (int)($ticketData['sold_count'] ?? 0);
            $totalRevenue = (float)($ticketData['revenue'] ?? 0.00);

            // Fetch payout info
            $payoutsStmt = $db->prepare("SELECT SUM(amount) FROM payouts WHERE organizer_id = :id AND status = 'processed'");
            $payoutsStmt->execute(['id' => $userId]);
            $disbursed = (float)($payoutsStmt->fetchColumn() ?: 0.00);

            $pendingPayoutsStmt = $db->prepare("SELECT SUM(amount) FROM payouts WHERE organizer_id = :id AND status IN ('pending', 'failed')");
            $pendingPayoutsStmt->execute(['id' => $userId]);
            $pending = (float)($pendingPayoutsStmt->fetchColumn() ?: 0.00);

            $availableBalance = max(0.00, $totalRevenue - $disbursed - $pending);

            // Fetch payout history
            $payoutsHistoryStmt = $db->prepare("
                SELECT p.id, p.amount, p.status, p.created_at, e.name as event_name
                FROM payouts p
                LEFT JOIN events e ON p.event_id = e.id
                WHERE p.organizer_id = :id
                ORDER BY p.created_at DESC
                LIMIT 5
            ");
            $payoutsHistoryStmt->execute(['id' => $userId]);
            $payoutsHistory = $payoutsHistoryStmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];

            // Fetch events history
            $eventsHistoryStmt = $db->prepare("
                SELECT e.id, e.name, e.date, e.status,
                       (SELECT COUNT(*) FROM tickets t WHERE t.event_id = e.id AND t.status IN ('Paid', 'Comped') AND (t.is_issued = 0 OR t.is_issued IS NULL)) as tickets_sold
                FROM events e
                WHERE e.organizer_id = :id
                ORDER BY e.date DESC
                LIMIT 5
            ");
            $eventsHistoryStmt->execute(['id' => $userId]);
            $eventsHistory = $eventsHistoryStmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];

            $stats = [
                'bio' => $profile['bio'] ?? 'No bio set.',
                'rating' => (float)($profile['rating'] ?? 5.00),
                'payout_bank' => $profile['payout_bank'] ?? 'Not set',
                'payout_account' => !empty($profile['payout_account']) ? '****' . substr($profile['payout_account'], -4) : 'Not set',
                'payout_routing' => !empty($profile['payout_routing']) ? '****' . substr($profile['payout_routing'], -4) : 'Not set',
                'total_events' => $totalEvents,
                'tickets_sold' => $ticketsSold,
                'total_revenue' => $totalRevenue,
                'total_revenue_formatted' => format_price($totalRevenue),
                'disbursed_payouts' => $disbursed,
                'disbursed_payouts_formatted' => format_price($disbursed),
                'pending_payouts' => $pending,
                'pending_payouts_formatted' => format_price($pending),
                'available_balance' => $availableBalance,
                'available_balance_formatted' => format_price($availableBalance),
                'payouts_history' => array_map(function($p) {
                    $p['amount_formatted'] = format_price($p['amount']);
                    return $p;
                }, $payoutsHistory),
                'events_history' => $eventsHistory
            ];
        } else {
            // Attendee details
            $ticketsCountStmt = $db->prepare("SELECT COUNT(*) FROM tickets WHERE user_id = :id AND status IN ('Paid', 'Comped')");
            $ticketsCountStmt->execute(['id' => $userId]);
            $ticketsBought = (int)$ticketsCountStmt->fetchColumn();

            $totalSpentStmt = $db->prepare("SELECT SUM(price) FROM tickets WHERE user_id = :id AND status IN ('Paid', 'Comped')");
            $totalSpentStmt->execute(['id' => $userId]);
            $totalSpent = (float)($totalSpentStmt->fetchColumn() ?: 0.00);

            $lastPurchaseStmt = $db->prepare("SELECT MAX(purchase_date) FROM tickets WHERE user_id = :id");
            $lastPurchaseStmt->execute(['id' => $userId]);
            $lastPurchase = $lastPurchaseStmt->fetchColumn();

            $stats = [
                'tickets_bought' => $ticketsBought,
                'total_spent' => $totalSpent,
                'total_spent_formatted' => format_price($totalSpent),
                'last_purchase_date' => $lastPurchase ? date('M d, Y H:i', strtotime($lastPurchase)) : 'N/A'
            ];
        }

        return [
            'user' => $user,
            'stats' => $stats
        ];
    }
}
