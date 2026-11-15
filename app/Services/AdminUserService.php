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

            // Use HostService for unified, lifecycle-aware calculations
            $hostService = new \App\Services\HostService();
            $hostStats = $hostService->getDashboardStats($userId);

            $totalEvents = (int)($hostStats['totalEventsCount'] ?? 0);
            $ticketsSold = (int)($hostStats['totalTicketsSold'] ?? 0);
            $grossRevenue = (float)($hostStats['totalRevenue'] ?? 0.00);
            $netEarnings = (float)($hostStats['totalNetRevenue'] ?? 0.00);
            $platformProfit = (float)($hostStats['totalPlatformFees'] ?? 0.00);
            $disbursed = (float)($hostStats['disbursedPayouts'] ?? 0.00);
            $pending = (float)($hostStats['pendingPayouts'] ?? 0.00);
            $availableBalance = (float)($hostStats['availableBalance'] ?? 0.00);
            $escrowBalance = (float)($hostStats['availableEscrowBalance'] ?? 0.00);

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
                'total_revenue' => $grossRevenue,
                'total_revenue_formatted' => format_price($grossRevenue),
                'net_earnings' => $netEarnings,
                'net_earnings_formatted' => format_price($netEarnings),
                'platform_profit' => $platformProfit,
                'platform_profit_formatted' => format_price($platformProfit),
                'disbursed_payouts' => $disbursed,
                'disbursed_payouts_formatted' => format_price($disbursed),
                'pending_payouts' => $pending,
                'pending_payouts_formatted' => format_price($pending),
                'escrow_balance' => $escrowBalance,
                'escrow_balance_formatted' => format_price($escrowBalance),
                'available_balance' => $availableBalance,
                'available_balance_formatted' => format_price($availableBalance),
                'total_available' => ($availableBalance + $escrowBalance),
                'total_available_formatted' => format_price($availableBalance + $escrowBalance),
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

    /**
     * Get ALL data belonging to a specific user across all platform tables.
     * Implements NDPA 2023 & GDPR Right to Access / Data Subject Access Request (DSAR).
     */
    public function getFullUserData(int|string $userId): ?array
    {
        $db = Application::$app->db->pdo;

        // 1. Account info
        $userStmt = $db->prepare("SELECT id, name, email, phone, status, email_verified_at, created_at FROM users WHERE id = :id LIMIT 1");
        $userStmt->execute(['id' => $userId]);
        $user = $userStmt->fetch(\PDO::FETCH_ASSOC);

        if (!$user) {
            return null;
        }

        $userEmail = $user['email'];

        // 2. Roles
        $rolesStmt = $db->prepare("
            SELECT r.id, r.name, r.slug
            FROM user_roles ur
            JOIN roles r ON ur.role_id = r.id
            WHERE ur.user_id = :id
        ");
        $rolesStmt->execute(['id' => $userId]);
        $roles = $rolesStmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];

        // 3. Host Profile
        $hostProfileStmt = $db->prepare("SELECT bio, payout_bank, payout_account, payout_routing, rating FROM host_profiles WHERE user_id = :id LIMIT 1");
        $hostProfileStmt->execute(['id' => $userId]);
        $hostProfile = $hostProfileStmt->fetch(\PDO::FETCH_ASSOC) ?: null;

        // 4. Events Hosted (as Organizer)
        $eventsStmt = $db->prepare("
            SELECT e.id, e.name, e.slug, e.description, e.date, e.location, e.category, e.status, 
                   e.is_featured, e.flyer_page_enabled, e.whatsapp_delivery_enabled, e.pass_fee, 
                   e.allow_partial_payment, e.created_at,
                   (SELECT COUNT(*) FROM tickets t WHERE t.event_id = e.id AND t.status IN ('Paid', 'Comped')) as tickets_sold,
                   (SELECT COALESCE(SUM(tr.total_amount), 0) FROM transactions tr WHERE tr.event_id = e.id AND tr.status = 'successful') as gross_sales
            FROM events e
            WHERE e.organizer_id = :id
            ORDER BY e.created_at DESC
        ");
        $eventsStmt->execute(['id' => $userId]);
        $hostedEvents = $eventsStmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];

        // 5. Tickets Purchased (as Attendee)
        $ticketsStmt = $db->prepare("
            SELECT t.id, t.event_id, e.name as event_name, e.date as event_date, e.location as event_location,
                   tt.name as ticket_tier_name, t.attendee_name, t.attendee_email, t.attendee_phone,
                   t.price, t.status, t.status_detail, t.purchase_date, t.checked_in, t.checked_in_at
            FROM tickets t
            JOIN events e ON t.event_id = e.id
            LEFT JOIN ticket_types tt ON t.ticket_type_id = tt.id
            WHERE t.user_id = :id OR t.attendee_email = :email
            ORDER BY t.purchase_date DESC
        ");
        $ticketsStmt->execute(['id' => $userId, 'email' => $userEmail]);
        $tickets = $ticketsStmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];

        // 6. Transactions & Payment Receipts
        $transactionsStmt = $db->prepare("
            SELECT tr.id, tr.reference, tr.event_id, e.name as event_name, tr.payer_name, tr.payer_email, tr.payer_phone,
                   tr.type, tr.payment_method, tr.subtotal_amount, tr.fee_amount, tr.extra_charge_amount, 
                   tr.discount_amount, tr.total_amount, tr.status, tr.created_at
            FROM transactions tr
            LEFT JOIN events e ON tr.event_id = e.id
            WHERE tr.user_id = :id OR tr.payer_email = :email
            ORDER BY tr.created_at DESC
        ");
        $transactionsStmt->execute(['id' => $userId, 'email' => $userEmail]);
        $transactions = $transactionsStmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];

        // 7. Layaway Payments
        $layawayStmt = $db->prepare("
            SELECT lp.id, lp.layaway_ticket_id, lp.event_id, e.name as event_name, lp.amount, 
                   lp.payment_method, lp.reference, lp.status, lp.created_at
            FROM layaway_payments lp
            LEFT JOIN events e ON lp.event_id = e.id
            WHERE lp.user_id = :id OR lp.payer_email = :email
            ORDER BY lp.created_at DESC
        ");
        $layawayStmt->execute(['id' => $userId, 'email' => $userEmail]);
        $layawayPayments = $layawayStmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];

        // 8. Layaway Credits
        $creditsStmt = $db->prepare("
            SELECT lc.id, lc.original_ticket_id, lc.original_event_id, e.name as original_event_name,
                   lc.amount, lc.remaining_amount, lc.status, lc.expires_at, lc.created_at
            FROM layaway_credits lc
            LEFT JOIN events e ON lc.original_event_id = e.id
            WHERE lc.user_id = :id OR lc.email = :email
            ORDER BY lc.created_at DESC
        ");
        $creditsStmt->execute(['id' => $userId, 'email' => $userEmail]);
        $layawayCredits = $creditsStmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];

        // 9. Ticket Transfers (Sent & Received)
        $transfersStmt = $db->prepare("
            SELECT tt.id, tt.ticket_id, tt.event_id, e.name as event_name, 
                   tt.sender_name, tt.sender_email, tt.recipient_name, tt.recipient_email, 
                   tt.transfer_note, tt.created_at,
                   CASE WHEN tt.sender_user_id = :c_uid OR tt.sender_email = :c_email THEN 'sent' ELSE 'received' END as direction
            FROM ticket_transfers tt
            LEFT JOIN events e ON tt.event_id = e.id
            WHERE tt.sender_user_id = :s_uid OR tt.recipient_user_id = :r_uid OR tt.sender_email = :s_email OR tt.recipient_email = :r_email
            ORDER BY tt.created_at DESC
        ");
        $transfersStmt->execute([
            'c_uid' => $userId, 'c_email' => $userEmail,
            's_uid' => $userId, 'r_uid' => $userId,
            's_email' => $userEmail, 'r_email' => $userEmail
        ]);
        $ticketTransfers = $transfersStmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];

        // 10. Event Reviews
        $reviewsStmt = $db->prepare("
            SELECT er.id, er.event_id, e.name as event_name, er.rating, er.comment, er.created_at
            FROM event_reviews er
            LEFT JOIN events e ON er.event_id = e.id
            WHERE er.attendee_email = :email
            ORDER BY er.created_at DESC
        ");
        $reviewsStmt->execute(['email' => $userEmail]);
        $reviews = $reviewsStmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];

        // 11. Payouts (if Host)
        $payoutsStmt = $db->prepare("
            SELECT p.id, p.event_id, e.name as event_name, p.amount, p.type, p.status, p.created_at
            FROM payouts p
            LEFT JOIN events e ON p.event_id = e.id
            WHERE p.organizer_id = :id
            ORDER BY p.created_at DESC
        ");
        $payoutsStmt->execute(['id' => $userId]);
        $payouts = $payoutsStmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];

        // 12. Custom Domain Requests
        $domainRequestsStmt = $db->prepare("
            SELECT dr.id, dr.event_id, e.name as event_name, dr.requested_domain, dr.integration_type, 
                   dr.fee_amount, dr.payment_status, dr.status, dr.dns_cname_target, dr.created_at
            FROM event_domain_requests dr
            LEFT JOIN events e ON dr.event_id = e.id
            WHERE dr.host_id = :id
            ORDER BY dr.created_at DESC
        ");
        $domainRequestsStmt->execute(['id' => $userId]);
        $domainRequests = $domainRequestsStmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];

        // 13. Support Inquiries
        $supportStmt = $db->prepare("
            SELECT id, name, email, subject, message, status, created_at
            FROM support_inquiries
            WHERE email = :email
            ORDER BY created_at DESC
        ");
        $supportStmt->execute(['email' => $userEmail]);
        $supportInquiries = $supportStmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];

        // Summary calculations
        $totalSpent = 0.0;
        foreach ($tickets as $t) {
            if (in_array($t['status'], ['Paid', 'Comped'])) {
                $totalSpent += (float)$t['price'];
            }
        }

        $totalRevenue = 0.0;
        foreach ($hostedEvents as $he) {
            $totalRevenue += (float)($he['gross_sales'] ?? 0);
        }

        return [
            'compliance_meta' => [
                'standard' => 'Nigeria Data Protection Act (NDPA) 2023 & GDPR Right of Access',
                'exported_at' => date('Y-m-d H:i:s'),
                'export_reference' => 'DSAR-' . strtoupper(bin2hex(random_bytes(4))),
                'platform' => site_setting('site_title', 'Eventlyy'),
            ],
            'user' => $user,
            'roles' => $roles,
            'host_profile' => $hostProfile,
            'metrics' => [
                'tickets_purchased_count' => count($tickets),
                'total_amount_spent' => $totalSpent,
                'total_amount_spent_formatted' => format_price($totalSpent),
                'events_hosted_count' => count($hostedEvents),
                'total_revenue_generated' => $totalRevenue,
                'total_revenue_generated_formatted' => format_price($totalRevenue),
                'transactions_count' => count($transactions),
                'layaway_payments_count' => count($layawayPayments),
                'support_inquiries_count' => count($supportInquiries),
            ],
            'tickets' => $tickets,
            'transactions' => $transactions,
            'layaway_payments' => $layawayPayments,
            'layaway_credits' => $layawayCredits,
            'ticket_transfers' => $ticketTransfers,
            'hosted_events' => $hostedEvents,
            'payouts' => $payouts,
            'custom_domain_requests' => $domainRequests,
            'event_reviews' => $reviews,
            'support_inquiries' => $supportInquiries,
        ];
    }

    /**
     * Delete or anonymize all records belonging to a user.
     * Implements NDPA 2023 & GDPR Right to Erasure ("Right to be Forgotten").
     */
    public function deleteUserData(int|string $targetUserId, int|string $currentAdminId, bool $force = false): array
    {
        $db = Application::$app->db->pdo;

        if (!$targetUserId) {
            return ['status' => 'error', 'message' => 'User ID is missing.'];
        }

        // 1. Self-protection: Current admin cannot delete their own active session account
        if ($targetUserId == $currentAdminId) {
            return ['status' => 'error', 'message' => 'You cannot delete your own active account.'];
        }

        // 2. Fetch current admin user model & verify Super Admin privileges
        $currentAdmin = \App\Models\User::findOne(['id' => $currentAdminId]);
        if (!$currentAdmin || !$currentAdmin->isSuperAdmin()) {
            return ['status' => 'error', 'message' => 'Unauthorized. Only Super Administrators can purge user data and delete accounts under NDPA 2023.'];
        }

        // 3. Fetch target user
        $targetUser = \App\Models\User::findOne(['id' => $targetUserId]);
        if (!$targetUser) {
            return ['status' => 'error', 'message' => 'Target user not found.'];
        }

        // 4. Protection: Cannot delete the primary Super Admin account (id=1)
        if ($targetUser->isSuperAdmin() && $targetUser->id == 1) {
            return ['status' => 'error', 'message' => 'The Primary Super Administrator account cannot be deleted.'];
        }

        $userId = (int)$targetUser->id;
        $userEmail = $targetUser->email;

        // 5. Host Safety Check: Are there active/upcoming published events with attendees?
        $activeEventsStmt = $db->prepare("
            SELECT e.id, e.name, e.date,
                   (SELECT COUNT(*) FROM tickets t WHERE t.event_id = e.id AND t.status IN ('Paid', 'Comped')) as sold_tickets
            FROM events e
            WHERE e.organizer_id = :id AND e.status = 'published' AND e.date >= CURDATE()
        ");
        $activeEventsStmt->execute(['id' => $userId]);
        $activeEvents = $activeEventsStmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];

        $hasActiveTickets = false;
        foreach ($activeEvents as $ae) {
            if ($ae['sold_tickets'] > 0) {
                $hasActiveTickets = true;
                break;
            }
        }

        if ($hasActiveTickets && !$force) {
            return [
                'status' => 'error',
                'message' => "Cannot delete user. This host has active upcoming published event(s) with sold tickets. Cancel, complete, or reassign those events first to protect attendee admissions."
            ];
        }

        // 6. Execute complete deletion and anonymization in a database transaction
        try {
            $db->beginTransaction();

            // A. Remove authentication & token sessions
            try { $db->prepare("DELETE FROM remember_tokens WHERE user_id = :id")->execute(['id' => $userId]); } catch (\Throwable $e) {}
            try { $db->prepare("DELETE FROM email_verifications WHERE email = :email")->execute(['email' => $userEmail]); } catch (\Throwable $e) {}
            try { $db->prepare("DELETE FROM password_resets WHERE email = :email")->execute(['email' => $userEmail]); } catch (\Throwable $e) {}
            try { $db->prepare("DELETE FROM login_attempts WHERE email = :email")->execute(['email' => $userEmail]); } catch (\Throwable $e) {}

            // B. Delete layaway records
            try { $db->prepare("DELETE FROM layaway_credits WHERE user_id = :id OR email = :email")->execute(['id' => $userId, 'email' => $userEmail]); } catch (\Throwable $e) {}
            try { $db->prepare("DELETE FROM layaway_payments WHERE user_id = :id OR payer_email = :email")->execute(['id' => $userId, 'email' => $userEmail]); } catch (\Throwable $e) {}

            // C. Delete event reviews written by user
            try { $db->prepare("DELETE FROM event_reviews WHERE attendee_email = :email")->execute(['email' => $userEmail]); } catch (\Throwable $e) {}

            // D. Delete support inquiries
            try { $db->prepare("DELETE FROM support_inquiries WHERE email = :email")->execute(['email' => $userEmail]); } catch (\Throwable $e) {}

            // E. Delete ticket transfers involving this user
            try { $db->prepare("DELETE FROM ticket_transfers WHERE sender_user_id = :id1 OR recipient_user_id = :id2 OR sender_email = :email1 OR recipient_email = :email2")->execute(['id1' => $userId, 'id2' => $userId, 'email1' => $userEmail, 'email2' => $userEmail]); } catch (\Throwable $e) {}

            // F. Delete staff assignments
            try { $db->prepare("DELETE FROM event_staff_assignments WHERE staff_id = :id")->execute(['id' => $userId]); } catch (\Throwable $e) {}

            // G. Delete host profile
            try { $db->prepare("DELETE FROM host_profiles WHERE user_id = :id")->execute(['id' => $userId]); } catch (\Throwable $e) {}

            // H. Delete custom domain requests
            try { $db->prepare("DELETE FROM event_domain_requests WHERE host_id = :id")->execute(['id' => $userId]); } catch (\Throwable $e) {}

            // I. Delete user roles
            try { $db->prepare("DELETE FROM user_roles WHERE user_id = :id")->execute(['id' => $userId]); } catch (\Throwable $e) {}

            // J. Anonymize tickets purchased by this user (Wipes personal information permanently while preserving gate admission counts)
            $db->prepare("
                UPDATE tickets 
                SET user_id = NULL,
                    attendee_name = 'Anonymized Attendee',
                    attendee_email = 'deleted@anonymized.local',
                    attendee_phone = NULL
                WHERE user_id = :id OR attendee_email = :email
            ")->execute(['id' => $userId, 'email' => $userEmail]);

            // K. Anonymize financial transactions (Wipes payer identity while preserving tax/accounting ledgers as mandated by law)
            $db->prepare("
                UPDATE transactions 
                SET user_id = NULL,
                    payer_name = 'Anonymized Customer',
                    payer_email = 'deleted@anonymized.local',
                    payer_phone = NULL
                WHERE user_id = :id OR payer_email = :email
            ")->execute(['id' => $userId, 'email' => $userEmail]);

            // L. Handle hosted events:
            // Delete draft events
            try { $db->prepare("DELETE FROM events WHERE organizer_id = :id AND status = 'draft'")->execute(['id' => $userId]); } catch (\Throwable $e) {}
            // For completed/past events, anonymize organizer link
            try { $db->prepare("UPDATE events SET organizer_id = NULL WHERE organizer_id = :id")->execute(['id' => $userId]); } catch (\Throwable $e) {}

            // M. Payouts anonymize
            try { $db->prepare("UPDATE payouts SET organizer_id = NULL WHERE organizer_id = :id")->execute(['id' => $userId]); } catch (\Throwable $e) {}

            // N. Finally, permanently delete the user account from users table
            $db->prepare("DELETE FROM users WHERE id = :id")->execute(['id' => $userId]);

            $db->commit();

            return [
                'status' => 'success',
                'message' => "All personal records, credentials, and data belonging to User #{$userId} (" . htmlspecialchars($userEmail) . ") have been permanently deleted and anonymized in compliance with NDPA 2023."
            ];

        } catch (\Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            error_log("deleteUserData error: " . $e->getMessage());
            return [
                'status' => 'error',
                'message' => 'Failed to delete user records due to database error: ' . $e->getMessage()
            ];
        }
    }
}
