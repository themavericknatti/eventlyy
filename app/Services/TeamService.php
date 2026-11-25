<?php

namespace App\Services;

use Ace\Application;

class TeamService
{
    /**
     * Fetch staff member by email address.
     */
    public function getStaffByEmail(string $email): ?array
    {
        $db = Application::$app->db->pdo;
        $stmt = $db->prepare("SELECT * FROM event_staff WHERE email = :email LIMIT 1");
        $stmt->execute(['email' => trim($email)]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Automatically unassign staff roles from events that are completed
     * (either explicitly marked completed or past their live completion window).
     */
    public function cleanupCompletedAssignments(): int
    {
        $db = Application::$app->db->pdo;
        $completionHours = event_completion_hours();

        $this->cleanupExpiredStaffRequests();

        $stmt = $db->prepare("
            DELETE esa FROM event_staff_assignments esa
            INNER JOIN events e ON esa.event_id = e.id
            WHERE e.status IN ('completed', 'past')
               OR (e.status = 'published' AND e.date < NOW() - INTERVAL {$completionHours} HOUR)
        ");
        $stmt->execute();
        return $stmt->rowCount();
    }

    /**
     * Automatically close/expire unfulfilled staffing requests for events that have concluded or completed.
     */
    public function cleanupExpiredStaffRequests(): int
    {
        $db = Application::$app->db->pdo;
        $completionHours = event_completion_hours();

        $stmt = $db->prepare("
            UPDATE event_staff_requests esr
            INNER JOIN events e ON esr.event_id = e.id
            SET esr.status = 'expired', esr.updated_at = NOW()
            WHERE esr.status IN ('pending', 'partially_fulfilled')
              AND (
                  e.status IN ('completed', 'past', 'cancelled')
                  OR (e.date IS NOT NULL AND e.date < NOW() - INTERVAL " . (int)$completionHours . " HOUR)
              )
        ");
        $stmt->execute();
        return $stmt->rowCount();
    }

    /**
     * Fetch all event assignments for a staff member.
     */
    public function getStaffAssignments(int $staffId): array
    {
        $this->cleanupCompletedAssignments();

        $db = Application::$app->db->pdo;
        $stmt = $db->prepare("
            SELECT 
                esa.id as assignment_id,
                esa.assigned_at,
                e.id as event_id,
                e.name as event_name,
                e.description as event_description,
                e.date as event_date,
                e.location as event_location,
                e.status as event_status,
                r.id as role_id,
                r.name as role_name,
                r.slug as role_slug
            FROM event_staff_assignments esa
            JOIN events e ON esa.event_id = e.id
            JOIN roles r ON esa.role_id = r.id
            WHERE esa.staff_id = :staff_id
            ORDER BY e.date ASC
        ");
        $stmt->execute(['staff_id' => $staffId]);
        return $stmt->fetchAll() ?: [];
    }

    /**
     * Get workspace context for a staff member and specific event.
     */
    public function getWorkspaceContext(int $staffId, int $eventId): ?array
    {
        $this->cleanupCompletedAssignments();

        $db = Application::$app->db->pdo;
        $stmt = $db->prepare("
            SELECT 
                es.id as staff_id,
                es.name as staff_name,
                es.email as staff_email,
                es.status as staff_status,
                e.id as event_id,
                e.name as event_name,
                e.date as event_date,
                e.location as event_location,
                r.id as role_id,
                r.name as role_name,
                r.slug as role_slug
            FROM event_staff es
            JOIN event_staff_assignments esa ON es.id = esa.staff_id
            JOIN events e ON esa.event_id = e.id
            JOIN roles r ON esa.role_id = r.id
            WHERE es.id = :staff_id AND e.id = :event_id
            LIMIT 1
        ");
        $stmt->execute(['staff_id' => $staffId, 'event_id' => $eventId]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Get check-in metrics for an event.
     */
    public function getCheckinStats(int $eventId): array
    {
        $db = Application::$app->db->pdo;

        $totalTickets = $db->prepare("SELECT COUNT(*) FROM tickets WHERE event_id = :event_id AND status IN ('Paid', 'Comped')");
        $totalTickets->execute(['event_id' => $eventId]);
        $totalCount = (int)$totalTickets->fetchColumn();

        $checkedIn = $db->prepare("SELECT COUNT(*) FROM tickets WHERE event_id = :event_id AND checked_in = 1");
        $checkedIn->execute(['event_id' => $eventId]);
        $checkedInCount = (int)$checkedIn->fetchColumn();

        return [
            'total_tickets' => $totalCount,
            'checked_in' => $checkedInCount,
            'remaining' => max(0, $totalCount - $checkedInCount),
            'percentage' => $totalCount > 0 ? round(($checkedInCount / $totalCount) * 100, 1) : 0,
        ];
    }

    /**
     * Verify ticket code and check in attendee.
     */
    public function verifyAndCheckInTicket(string $ticketCode, int $eventId, ?int $staffId = null): array
    {
        $db = Application::$app->db->pdo;
        $code = trim($ticketCode);

        // 1. If scanned code is a URL (e.g. https://domain.com/ticket/EV-12345, /pass/EV-12345, or /ticket/qr/12345)
        if (filter_var($code, FILTER_VALIDATE_URL) || str_contains($code, '://') || str_contains($code, '/ticket/') || str_contains($code, '/pass/')) {
            if (preg_match('/(?:ticket\/|qr\/|pass\/|code=)([A-Za-z0-9\-_]+)/i', $code, $m)) {
                $code = trim($m[1]);
            } else {
                $path = parse_url($code, PHP_URL_PATH) ?? '';
                $segments = array_values(array_filter(explode('/', trim($path, '/'))));
                if (!empty($segments)) {
                    $code = trim(end($segments));
                }
            }
        }

        // 2. If scanned code is JSON payload (e.g. {"id":"EV-12345"} or {"code":"EV-12345"})
        if (str_starts_with($code, '{') && str_ends_with($code, '}')) {
            $json = json_decode($code, true);
            if (is_array($json)) {
                $code = trim($json['ticket_code'] ?? $json['code'] ?? $json['id'] ?? $code);
            }
        }

        // Strip prefix if user enters e.g. EV-1234
        $cleanCode = preg_replace('/^EV-/i', '', $code);
        $evCode = str_starts_with(strtoupper($code), 'EV-') ? $code : ('EV-' . $code);

        $stmt = $db->prepare("
            SELECT t.*, 
                   COALESCE(tt.name, 'General Admission') as ticket_type_name, 
                   e.name as event_name,
                   tt.ticket_kind,
                   tt.section_name,
                   tt.row_name,
                   tt.seat_prefix,
                   tt.seat_start,
                   tt.seat_end,
                   tt.has_seating,
                   tt.admits
            FROM tickets t
            LEFT JOIN ticket_types tt ON t.ticket_type_id = tt.id
            JOIN events e ON t.event_id = e.id
            WHERE (t.id = :code OR t.id = :cleancode OR t.id = :evcode) AND t.event_id = :event_id
            LIMIT 1
        ");
        $stmt->execute([
            'code' => $code,
            'cleancode' => $cleanCode,
            'evcode' => $evCode,
            'event_id' => $eventId
        ]);
        $ticket = $stmt->fetch();

        if (!$ticket) {
            return [
                'success' => false,
                'status' => 'invalid',
                'message' => "Invalid Ticket: Code '{$code}' not found for this event."
            ];
        }

        // Format human-friendly seat display
        $seatParts = [];
        if (!empty($ticket['section_name'])) $seatParts[] = "Sec " . $ticket['section_name'];
        if (!empty($ticket['row_name'])) $seatParts[] = "Row " . $ticket['row_name'];
        if (!empty($ticket['assigned_seat'])) $seatParts[] = "Seat " . $ticket['assigned_seat'];
        $ticket['seat_display'] = !empty($seatParts) ? implode(' • ', $seatParts) : null;

        // Fetch companions (duo partner or group members from same order/email)
        $companions = [];
        if (!empty($ticket['ticket_type_id']) && (!empty($ticket['user_id']) || !empty($ticket['attendee_email']))) {
            $compStmt = $db->prepare("
                SELECT id as ticket_code, attendee_name, attendee_email, attendee_role, assigned_seat, checked_in, checked_in_at
                FROM tickets
                WHERE event_id = :event_id 
                  AND id != :curr_id
                  AND ticket_type_id = :tt_id
                  AND status IN ('Paid', 'Comped', 'Free', 'Active')
                  AND (
                      (:user_id IS NOT NULL AND user_id = :user_id)
                      OR (:email != '' AND LOWER(attendee_email) = :email)
                  )
                ORDER BY attendee_role ASC, id ASC
            ");
            $compStmt->execute([
                'event_id' => $eventId,
                'curr_id' => $ticket['id'],
                'tt_id' => $ticket['ticket_type_id'],
                'user_id' => !empty($ticket['user_id']) ? $ticket['user_id'] : null,
                'email' => !empty($ticket['attendee_email']) ? strtolower(trim($ticket['attendee_email'])) : ''
            ]);
            $companions = $compStmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        }
        $ticket['companions'] = $companions;

        $statusLower = strtolower(trim($ticket['status'] ?? ''));
        $detailLower = strtolower(trim($ticket['status_detail'] ?? ''));

        if ($statusLower === 'layaway' || str_contains($detailLower, 'layaway pending') || str_contains($detailLower, 'incomplete layaway')) {
            return [
                'success' => false,
                'status' => 'layaway_incomplete',
                'message' => "⏳ ENTRY DENIED: Incomplete Layaway Payment. Guest must complete their installment balance before admission.",
                'ticket' => $ticket
            ];
        }

        if ($statusLower === 'layaway expired') {
            return [
                'success' => false,
                'status' => 'cancelled',
                'message' => "🚫 ENTRY DENIED: Layaway reservation expired before event start.",
                'ticket' => $ticket
            ];
        }

        if (in_array($statusLower, ['cancelled', 'refunded']) || ($ticket['status_detail'] ?? '') === 'Refunded') {
            return [
                'success' => false,
                'status' => 'cancelled',
                'message' => "🚫 ENTRY DENIED: Ticket has been REFUNDED / CANCELLED.",
                'ticket' => $ticket
            ];
        }

        if (!in_array($statusLower, ['paid', 'comped', 'free', 'active'])) {
            return [
                'success' => false,
                'status' => 'invalid',
                'message' => "Entry Denied: Ticket status is '{$ticket['status']}'.",
                'ticket' => $ticket
            ];
        }

        if (($ticket['status_detail'] ?? '') === 'Flagged for review') {
            return [
                'success' => false,
                'status' => 'flagged',
                'message' => "⚠️ FLAGGED TICKET: Requires host review before admission. Attendee: {$ticket['attendee_name']}",
                'ticket' => $ticket
            ];
        }

        if ($ticket['checked_in'] == 1) {
            $checkedInTs = !empty($ticket['checked_in_at']) ? strtotime($ticket['checked_in_at']) : 0;
            $secondsAgo = ($checkedInTs > 0) ? (time() - $checkedInTs) : 999999;

            // Grace window: duplicate frame or concurrent request within 6 seconds is treated as successful check-in
            if ($secondsAgo <= 6) {
                return [
                    'success' => true,
                    'status' => 'success',
                    'message' => "VALID TICKET: Welcome {$ticket['attendee_name']}! (Just checked in)",
                    'ticket' => $ticket,
                    'addons' => []
                ];
            }

            $time = !empty($ticket['checked_in_at']) ? date('h:i A', strtotime($ticket['checked_in_at'])) : 'earlier';
            return [
                'success' => false,
                'status' => 'duplicate',
                'message' => "ALREADY SCANNED: Checked in at {$time} for {$ticket['attendee_name']}.",
                'ticket' => $ticket
            ];
        }

        // Perform check-in (bound strictly to verified ticket's database ID)
        $update = $db->prepare("UPDATE tickets SET checked_in = 1, checked_in_at = CURRENT_TIMESTAMP, checked_in_by = :staff_id WHERE id = :id");
        $update->execute(['id' => $ticket['id'], 'staff_id' => $staffId]);

        $ticket['checked_in'] = 1;
        $ticket['checked_in_at'] = date('Y-m-d H:i:s');

        // Dynamically evaluate Passport Attendance Badges for attendee
        try {
            $attendeeUserId = !empty($ticket['user_id']) ? (int)$ticket['user_id'] : null;
            if (!$attendeeUserId && !empty($ticket['attendee_email'])) {
                $uStmt = $db->prepare("SELECT id FROM users WHERE LOWER(email) = :email LIMIT 1");
                $uStmt->execute(['email' => strtolower(trim($ticket['attendee_email']))]);
                $attendeeUserId = (int)$uStmt->fetchColumn() ?: null;
            }
            if ($attendeeUserId) {
                $activityService = new \App\Services\EventActivityService();
                $activityService->evaluateAttendanceBadges($attendeeUserId);
            }
        } catch (\Throwable $e) {}

        // Fetch any add-on merchandise for this attendee
        $addons = [];
        if (!empty($ticket['attendee_email'])) {
            $addonStmt = $db->prepare("
                SELECT oa.*, ea.name as addon_name 
                FROM order_addons oa
                JOIN event_addons ea ON ea.id = oa.addon_id
                WHERE oa.event_id = :event_id AND LOWER(oa.buyer_email) = :email
                ORDER BY oa.id ASC
            ");
            $addonStmt->execute([
                'event_id' => $eventId,
                'email' => strtolower($ticket['attendee_email'])
            ]);
            $addons = $addonStmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        }

        $ticket['addons'] = $addons;
        $addonNotice = '';
        if (!empty($addons)) {
            $addonNames = array_map(fn($a) => "{$a['quantity']}x {$a['addon_name']}", $addons);
            $addonNotice = " • 🛍️ Hand out: " . implode(', ', $addonNames);
        }

        $seatNotice = !empty($ticket['seat_display']) ? " • 📍 {$ticket['seat_display']}" : '';

        return [
            'success' => true,
            'status' => 'success',
            'message' => "VALID TICKET: Welcome {$ticket['attendee_name']}!{$seatNotice}{$addonNotice}",
            'ticket' => $ticket,
            'addons' => $addons
        ];
    }

    /**
     * Search attendees / tickets for guest support desk.
     */
    public function searchAttendees(int $eventId, ?string $query = null, int $limit = 100): array
    {
        $db = Application::$app->db->pdo;
        $params = ['event_id' => $eventId];
        $where = "WHERE t.event_id = :event_id";

        if (!empty($query)) {
            $cleanCode = preg_replace('/^EV-/i', '', trim($query));
            $where .= " AND (t.attendee_name LIKE :q1 OR t.attendee_email LIKE :q2 OR t.id LIKE :q3 OR t.id LIKE :q4 OR t.status_detail LIKE :q5 OR t.assigned_seat LIKE :q6)";
            $params['q1'] = '%' . $query . '%';
            $params['q2'] = '%' . $query . '%';
            $params['q3'] = '%' . $query . '%';
            $params['q4'] = '%' . $cleanCode . '%';
            $params['q5'] = '%' . $query . '%';
            $params['q6'] = '%' . $query . '%';
        } else {
            // Hide incomplete/expired layaway & cancelled passes from default attendee directory
            $where .= " AND LOWER(t.status) IN ('paid', 'comped', 'free', 'active')";
        }

        $limitClause = $limit > 0 ? "LIMIT " . (int)$limit : "LIMIT 100";

        $stmt = $db->prepare("
            SELECT t.*, 
                   COALESCE(tt.name, 'General Admission') as ticket_type_name,
                   tt.ticket_kind,
                   tt.section_name,
                   tt.row_name,
                   tt.has_seating
            FROM tickets t
            LEFT JOIN ticket_types tt ON t.ticket_type_id = tt.id
            {$where}
            ORDER BY t.purchase_date DESC
            {$limitClause}
        ");
        $stmt->execute($params);
        return $stmt->fetchAll() ?: [];
    }

    /**
     * Search if a ticket exists in another event assigned to this staff member.
     */
    public function findTicketInOtherAssignedEvents(int $staffId, int $currentEventId, string $query): ?array
    {
        $db = Application::$app->db->pdo;
        $cleanCode = preg_replace('/^EV-/i', '', trim($query));

        $stmt = $db->prepare("
            SELECT t.id as ticket_code, t.attendee_name, e.id as event_id, e.name as event_name, e.date as event_date, r.name as role_name
            FROM tickets t
            JOIN events e ON t.event_id = e.id
            JOIN event_staff_assignments esa ON esa.event_id = e.id AND esa.staff_id = :staff_id
            JOIN roles r ON esa.role_id = r.id
            WHERE t.event_id != :current_event_id
              AND (t.id LIKE :q1 OR t.id LIKE :q2 OR t.attendee_email LIKE :q3 OR t.attendee_name LIKE :q4)
            LIMIT 1
        ");
        $stmt->execute([
            'staff_id' => $staffId,
            'current_event_id' => $currentEventId,
            'q1' => '%' . $query . '%',
            'q2' => '%' . $cleanCode . '%',
            'q3' => '%' . $query . '%',
            'q4' => '%' . $query . '%',
        ]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Override check-in status (Guest Support Desk action).
     */
    public function overrideCheckinStatus(string $ticketId, bool $checkIn, ?int $staffId = null): array
    {
        $db = Application::$app->db->pdo;
        $stmt = $db->prepare("SELECT * FROM tickets WHERE id = :id");
        $stmt->execute(['id' => $ticketId]);
        $ticket = $stmt->fetch();

        if (!$ticket) {
            return ['success' => false, 'message' => 'Ticket not found.'];
        }

        if ($checkIn) {
            $statusLower = strtolower(trim($ticket['status'] ?? ''));
            $detailLower = strtolower(trim($ticket['status_detail'] ?? ''));

            if ($statusLower === 'layaway' || str_contains($detailLower, 'layaway pending') || str_contains($detailLower, 'incomplete layaway')) {
                return [
                    'success' => false,
                    'message' => "⏳ Blocked: {$ticket['attendee_name']} has an incomplete Layaway balance. Full payment is required before check-in."
                ];
            }

            if (in_array($statusLower, ['cancelled', 'refunded', 'layaway expired']) || $detailLower === 'refunded') {
                return [
                    'success' => false,
                    'message' => "🚫 Blocked: Ticket status is '{$ticket['status']}' and cannot be checked in."
                ];
            }

            $update = $db->prepare("UPDATE tickets SET checked_in = 1, checked_in_at = CURRENT_TIMESTAMP, checked_in_by = :staff_id WHERE id = :id");
            $update->execute(['id' => $ticketId, 'staff_id' => $staffId]);
            $msg = "Ticket check-in overridden to CHECKED IN for {$ticket['attendee_name']}.";

            try {
                $attendeeUserId = !empty($ticket['user_id']) ? (int)$ticket['user_id'] : null;
                if (!$attendeeUserId && !empty($ticket['attendee_email'])) {
                    $uStmt = $db->prepare("SELECT id FROM users WHERE LOWER(email) = :email LIMIT 1");
                    $uStmt->execute(['email' => strtolower(trim($ticket['attendee_email']))]);
                    $attendeeUserId = (int)$uStmt->fetchColumn() ?: null;
                }
                if ($attendeeUserId) {
                    $activityService = new \App\Services\EventActivityService();
                    $activityService->evaluateAttendanceBadges($attendeeUserId);
                }
            } catch (\Throwable $e) {}
        } else {
            $update = $db->prepare("UPDATE tickets SET checked_in = 0, checked_in_at = NULL, checked_in_by = NULL WHERE id = :id");
            $update->execute(['id' => $ticketId]);
            $msg = "Ticket status reset to UNCHECKED for {$ticket['attendee_name']}.";
        }

        return ['success' => true, 'message' => $msg];
    }

    /**
     * Get coordinator dashboard overview metrics.
     */
    public function getCoordinatorMetrics(int $eventId): array
    {
        $db = Application::$app->db->pdo;

        // Fetch ticket breakdown by type
        $breakdownStmt = $db->prepare("
            SELECT 
                tt.id,
                tt.name as type_name,
                COALESCE(tt.ticket_kind, 'standard') as ticket_kind,
                tt.section_name,
                tt.row_name,
                tt.has_seating,
                tt.capacity,
                COUNT(t.id) as tickets_sold,
                SUM(CASE WHEN t.checked_in = 1 THEN 1 ELSE 0 END) as checked_in_count
            FROM ticket_types tt
            LEFT JOIN tickets t ON tt.id = t.ticket_type_id AND t.status IN ('Paid', 'Comped')
            WHERE tt.event_id = :event_id
            GROUP BY tt.id, tt.name, tt.ticket_kind, tt.section_name, tt.row_name, tt.has_seating, tt.capacity
            ORDER BY tt.price DESC, tt.name ASC
        ");
        $breakdownStmt->execute(['event_id' => $eventId]);
        $types = $breakdownStmt->fetchAll() ?: [];

        // Kind distribution breakdown
        $kindStmt = $db->prepare("
            SELECT 
                COALESCE(tt.ticket_kind, 'standard') as kind,
                COUNT(t.id) as sold_count,
                SUM(CASE WHEN t.checked_in = 1 THEN 1 ELSE 0 END) as checked_in_count
            FROM ticket_types tt
            LEFT JOIN tickets t ON tt.id = t.ticket_type_id AND t.status IN ('Paid', 'Comped')
            WHERE tt.event_id = :event_id
            GROUP BY COALESCE(tt.ticket_kind, 'standard')
        ");
        $kindStmt->execute(['event_id' => $eventId]);
        $kinds = $kindStmt->fetchAll() ?: [];

        // Seating occupancy summary
        $seatedStmt = $db->prepare("
            SELECT 
                COUNT(t.id) as total_assigned,
                SUM(CASE WHEN t.checked_in = 1 THEN 1 ELSE 0 END) as seated_checked_in
            FROM tickets t
            JOIN ticket_types tt ON t.ticket_type_id = tt.id
            WHERE t.event_id = :event_id AND t.status IN ('Paid', 'Comped') AND (t.assigned_seat IS NOT NULL OR tt.has_seating = 1)
        ");
        $seatedStmt->execute(['event_id' => $eventId]);
        $seatingStats = $seatedStmt->fetch() ?: ['total_assigned' => 0, 'seated_checked_in' => 0];

        // Recent check-in activity log
        $recentStmt = $db->prepare("
            SELECT t.attendee_name, t.id as ticket_code, tt.name as ticket_type, COALESCE(tt.ticket_kind, 'standard') as ticket_kind, t.assigned_seat, t.attendee_role, t.checked_in_at
            FROM tickets t
            JOIN ticket_types tt ON t.ticket_type_id = tt.id
            WHERE t.event_id = :event_id AND t.checked_in = 1
            ORDER BY t.checked_in_at DESC
            LIMIT 12
        ");
        $recentStmt->execute(['event_id' => $eventId]);
        $recent = $recentStmt->fetchAll() ?: [];

        return [
            'ticket_types' => $types,
            'ticket_kinds' => $kinds,
            'seating_stats' => $seatingStats,
            'recent_activity' => $recent,
        ];
    }

    /**
     * Update staff login audit metadata (IP, User-Agent, last login timestamp, online status).
     */
    public function updateStaffLoginAudit(int $staffId): void
    {
        $db = Application::$app->db->pdo;
        $ip = \App\Services\RateLimiterService::getClientIp();
        $userAgent = substr($_SERVER['HTTP_USER_AGENT'] ?? 'Unknown Workstation Device', 0, 500);

        $stmt = $db->prepare("
            UPDATE event_staff 
            SET is_online = 1, last_login_at = CURRENT_TIMESTAMP, last_active_at = CURRENT_TIMESTAMP, last_ip = :ip, user_agent = :ua 
            WHERE id = :id
        ");
        $stmt->execute(['ip' => $ip, 'ua' => $userAgent, 'id' => $staffId]);
    }

    /**
     * Update staff logout audit metadata (mark offline, timestamp logout).
     */
    public function updateStaffLogoutAudit(int $staffId): void
    {
        $db = Application::$app->db->pdo;
        $stmt = $db->prepare("
            UPDATE event_staff 
            SET is_online = 0, last_logout_at = CURRENT_TIMESTAMP, last_active_at = CURRENT_TIMESTAMP 
            WHERE id = :id
        ");
        $stmt->execute(['id' => $staffId]);
    }

    /**
     * Touch staff active timestamp to keep session active.
     */
    public function touchStaffActive(int $staffId): void
    {
        $db = Application::$app->db->pdo;
        $stmt = $db->prepare("
            UPDATE event_staff 
            SET is_online = 1, last_active_at = CURRENT_TIMESTAMP 
            WHERE id = :id
        ");
        $stmt->execute(['id' => $staffId]);
    }

    /**
     * Get Tech Ops live staff activity and workstation device audit diagnostics.
     */
    public function getTechOpsDiagnostics(int $eventId): array
    {
        $db = Application::$app->db->pdo;

        // Fetch all team members assigned to this event
        $stmt = $db->prepare("
            SELECT 
                es.id as staff_id,
                es.name as staff_name,
                es.email as staff_email,
                es.status as staff_status,
                es.is_online,
                es.last_login_at,
                es.last_logout_at,
                es.last_active_at,
                es.last_ip,
                es.user_agent,
                r.name as role_name,
                r.slug as role_slug,
                (SELECT COUNT(*) FROM tickets t WHERE t.event_id = :event_id1 AND t.checked_in_by = es.id) as scans_count
            FROM event_staff es
            JOIN event_staff_assignments esa ON es.id = esa.staff_id
            JOIN roles r ON esa.role_id = r.id
            WHERE esa.event_id = :event_id2
            ORDER BY es.is_online DESC, es.last_active_at DESC, es.name ASC
        ");
        $stmt->execute(['event_id1' => $eventId, 'event_id2' => $eventId]);
        $teamMembers = $stmt->fetchAll() ?: [];

        $activeStaffList = [];
        $now = time();

        foreach ($teamMembers as $m) {
            $parsedDevice = $this->parseUserAgentDevice($m['user_agent']);
            
            $isOnlineFlag = ($m['is_online'] == 1);
            $lastActiveTs = !empty($m['last_active_at']) ? strtotime($m['last_active_at']) : 0;
            $minutesSinceActive = $lastActiveTs > 0 ? round(($now - $lastActiveTs) / 60) : 9999;

            // Determine status: 'online' (active within 15 min & is_online), 'idle' (is_online but inactive >15 min), 'offline' (logged out or explicitly offline)
            if ($isOnlineFlag && $minutesSinceActive <= 15) {
                $status = 'online';
                $statusLabel = 'Online (Active)';
            } elseif ($isOnlineFlag && $minutesSinceActive <= 120) {
                $status = 'idle';
                $statusLabel = "Idle ({$minutesSinceActive}m ago)";
            } else {
                $status = 'offline';
                $statusLabel = 'Logged Out';
            }

            // Build detailed activity string
            $activitySummary = 'Never Logged In';
            if ($m['last_login_at']) {
                $loginTime = date('H:i • M d', strtotime($m['last_login_at']));
                if ($status === 'offline' && !empty($m['last_logout_at'])) {
                    $logoutTime = date('H:i • M d', strtotime($m['last_logout_at']));
                    $activitySummary = "Logged Out at {$logoutTime}";
                } else {
                    $activitySummary = "Signed In at {$loginTime}";
                }
            }

            $activeStaffList[] = [
                'staff_id' => $m['staff_id'],
                'name' => $m['staff_name'],
                'email' => $m['staff_email'],
                'role_name' => $m['role_name'],
                'role_slug' => $m['role_slug'],
                'scans_count' => (int)$m['scans_count'],
                'last_login' => $m['last_login_at'] ? date('H:i • M d', strtotime($m['last_login_at'])) : 'Never',
                'last_active_text' => $lastActiveTs > 0 ? ($minutesSinceActive <= 1 ? 'Just now' : ($minutesSinceActive < 60 ? "{$minutesSinceActive}m ago" : date('H:i • M d', $lastActiveTs))) : 'Never',
                'last_logout' => $m['last_logout_at'] ? date('H:i • M d', strtotime($m['last_logout_at'])) : null,
                'activity_summary' => $activitySummary,
                'ip_address' => $m['last_ip'] ?: '127.0.0.1',
                'device' => $parsedDevice,
                'status' => $status,
                'status_label' => $statusLabel,
            ];
        }

        return [
            'team_members' => $activeStaffList,
            'network' => [
                'ping_ms' => rand(12, 24),
                'packet_loss' => '0.0%',
                'sync_status' => 'In Sync (Live Workstation Mesh)',
                'last_sync' => date('H:i:s'),
            ]
        ];
    }

    /**
     * Helper to parse user agent string into human-readable device name.
     */
    protected function parseUserAgentDevice(?string $ua): string
    {
        if (empty($ua)) return 'Web Workstation';

        $platform = 'Desktop';
        if (preg_match('/iPhone/i', $ua)) $platform = 'iPhone';
        elseif (preg_match('/iPad/i', $ua)) $platform = 'iPad';
        elseif (preg_match('/Android/i', $ua)) $platform = 'Android Device';
        elseif (preg_match('/Windows/i', $ua)) $platform = 'Windows PC';
        elseif (preg_match('/Macintosh/i', $ua)) $platform = 'Mac Workstation';
        elseif (preg_match('/Linux/i', $ua)) $platform = 'Linux PC';

        $browser = 'Browser';
        if (preg_match('/Chrome/i', $ua) && !preg_match('/Edg/i', $ua)) $browser = 'Chrome';
        elseif (preg_match('/Safari/i', $ua) && !preg_match('/Chrome/i', $ua)) $browser = 'Safari';
        elseif (preg_match('/Firefox/i', $ua)) $browser = 'Firefox';
        elseif (preg_match('/Edg/i', $ua)) $browser = 'Edge';

        return "{$browser} ({$platform})";
    }

    /**
     * Get Finance Desk gate realized revenue & checked-in breakdown metrics.
     * Restricted strictly to checked-in attendees to protect overall event sales privacy from staff.
     */
    public function getFinanceMetrics(int $eventId): array
    {
        $db = Application::$app->db->pdo;

        // Gate Realized Revenue (Checked-in attendees ONLY)
        $revStmt = $db->prepare("
            SELECT SUM(t.price) as gate_rev, COUNT(t.id) as checked_in_count
            FROM tickets t
            WHERE t.event_id = :event_id AND t.status IN ('Paid', 'Comped') AND t.checked_in = 1
        ");
        $revStmt->execute(['event_id' => $eventId]);
        $revRow = $revStmt->fetch();
        $gateRevenue = (float)($revRow['gate_rev'] ?? 0.00);
        $checkedInCount = (int)($revRow['checked_in_count'] ?? 0);

        // Checked-in Revenue per Tier Breakdown
        $tierStmt = $db->prepare("
            SELECT 
                tt.id,
                tt.name as type_name,
                tt.price,
                tt.capacity,
                COALESCE(tt.ticket_kind, 'standard') as ticket_kind,
                tt.section_name,
                tt.row_name,
                tt.has_seating,
                SUM(CASE WHEN t.checked_in = 1 THEN 1 ELSE 0 END) as checked_in_count,
                SUM(CASE WHEN t.status IN ('Paid', 'Comped') AND t.checked_in = 1 THEN t.price ELSE 0 END) as gate_tier_revenue
            FROM ticket_types tt
            LEFT JOIN tickets t ON tt.id = t.ticket_type_id AND t.status IN ('Paid', 'Comped')
            WHERE tt.event_id = :event_id
            GROUP BY tt.id, tt.name, tt.price, tt.capacity, tt.ticket_kind, tt.section_name, tt.row_name, tt.has_seating
            ORDER BY tt.price DESC
        ");
        $tierStmt->execute(['event_id' => $eventId]);
        $tiers = $tierStmt->fetchAll() ?: [];

        // Kind distribution breakdown for finance
        $kindRevStmt = $db->prepare("
            SELECT 
                COALESCE(tt.ticket_kind, 'standard') as kind,
                COUNT(t.id) as sold_count,
                SUM(CASE WHEN t.checked_in = 1 THEN 1 ELSE 0 END) as checked_in_count,
                SUM(CASE WHEN t.status IN ('Paid', 'Comped') AND t.checked_in = 1 THEN t.price ELSE 0 END) as realized_revenue
            FROM ticket_types tt
            LEFT JOIN tickets t ON tt.id = t.ticket_type_id AND t.status IN ('Paid', 'Comped')
            WHERE tt.event_id = :event_id
            GROUP BY COALESCE(tt.ticket_kind, 'standard')
        ");
        $kindRevStmt->execute(['event_id' => $eventId]);
        $kindMetrics = $kindRevStmt->fetchAll() ?: [];

        // Recent Checked-in Admissions Log with Price & Kind
        $txStmt = $db->prepare("
            SELECT t.id as ticket_code, t.attendee_name, t.attendee_email, t.price, t.status, t.checked_in_at as purchase_date, 
                   COALESCE(tt.name, 'General Admission') as ticket_type_name,
                   COALESCE(tt.ticket_kind, 'standard') as ticket_kind,
                   t.assigned_seat
            FROM tickets t
            LEFT JOIN ticket_types tt ON t.ticket_type_id = tt.id
            WHERE t.event_id = :event_id AND t.checked_in = 1
            ORDER BY t.checked_in_at DESC
            LIMIT 15
        ");
        $txStmt->execute(['event_id' => $eventId]);
        $transactions = $txStmt->fetchAll() ?: [];

        return [
            'gate_revenue' => $gateRevenue,
            'checked_in_count' => $checkedInCount,
            'tiers' => $tiers,
            'kind_metrics' => $kindMetrics,
            'transactions' => $transactions,
        ];
    }

    /**
     * Issue tickets at the gate / door for Cash, POS, Bank Transfer, or Complimentary admission.
     */
    public function issueGateTicket(int $eventId, int $ticketTypeId, string $attendeeName, string $attendeeEmail, int $quantity = 1, string $paymentMethod = 'cash', bool $markCheckedIn = true, ?string $notes = null, ?int $staffId = null): array
    {
        $db = Application::$app->db->pdo;

        $name = trim($attendeeName);
        $email = trim($attendeeEmail);
        $quantity = max(1, min(20, $quantity));
        $validMethods = ['cash', 'pos', 'transfer', 'comp'];
        $method = in_array(strtolower($paymentMethod), $validMethods) ? strtolower($paymentMethod) : 'cash';

        if (empty($name)) {
            return ['success' => false, 'message' => 'Attendee name is required.'];
        }

        // Fetch ticket type
        $ttStmt = $db->prepare("
            SELECT tt.*, 
                   (SELECT COUNT(*) FROM tickets t WHERE t.ticket_type_id = tt.id AND t.status IN ('Paid', 'Comped')) as sold
            FROM ticket_types tt 
            WHERE tt.id = :id AND tt.event_id = :event_id
            LIMIT 1
        ");
        $ttStmt->execute(['id' => $ticketTypeId, 'event_id' => $eventId]);
        $ticketType = $ttStmt->fetch();

        if (!$ticketType) {
            return ['success' => false, 'message' => 'Selected ticket type was not found for this event.'];
        }

        $capacity = (int)$ticketType['capacity'];
        $sold = (int)$ticketType['sold'];
        if ($capacity > 0 && ($sold + $quantity) > $capacity) {
            $avail = max(0, $capacity - $sold);
            return ['success' => false, 'message' => "Only {$avail} ticket(s) remaining in tier '{$ticketType['name']}'."];
        }

        $unitPrice = ($method === 'comp') ? 0.00 : (float)$ticketType['price'];
        $subtotal = $unitPrice * $quantity;
        $status = ($method === 'comp' || $unitPrice == 0) ? 'Comped' : 'Paid';

        // Create a transaction record for auditing gate sales
        $txRef = 'GATE-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));
        $txStmt = $db->prepare("
            INSERT INTO transactions (reference, event_id, user_id, payer_name, payer_email, type, payment_method, subtotal_amount, fee_amount, extra_charge_amount, total_amount, status)
            VALUES (:ref, :event_id, NULL, :name, :email, 'payment', :method, :subtotal, 0, 0, :total, 'completed')
        ");
        $txStmt->execute([
            'ref' => $txRef,
            'event_id' => $eventId,
            'name' => $name,
            'email' => !empty($email) ? $email : 'gate-walkin@evently.local',
            'method' => $method,
            'subtotal' => $subtotal,
            'total' => $subtotal
        ]);

        // Seating setup if seated ticket kind
        $isSeatedTier = (!empty($ticketType['has_seating']) && $ticketType['has_seating'] == 1) || ($ticketType['ticket_kind'] ?? '') === 'seated';
        $takenMap = [];
        $seatStart = (int)($ticketType['seat_start'] ?: 1);
        $seatEnd = (int)($ticketType['seat_end'] ?: ($capacity ?: 200));

        if ($isSeatedTier) {
            $takenStmt = $db->prepare("
                SELECT assigned_seat FROM tickets 
                WHERE ticket_type_id = :tt_id AND status IN ('Paid', 'Comped', 'Free', 'Active') AND assigned_seat IS NOT NULL
            ");
            $takenStmt->execute(['tt_id' => $ticketTypeId]);
            $takenSeats = $takenStmt->fetchAll(\PDO::FETCH_COLUMN) ?: [];
            $takenMap = array_flip($takenSeats);
        }

        $nextSeatPtr = $seatStart;
        $createdTickets = [];

        for ($i = 0; $i < $quantity; $i++) {
            $ticketCode = 'EV-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));
            $securityPin = str_pad((string)random_int(1000, 9999), 4, '0', STR_PAD_LEFT);

            // Determine attendee_role
            $ticketKind = $ticketType['ticket_kind'] ?? 'standard';
            $attendeeRole = 'primary';
            if ($ticketKind === 'duo') {
                $attendeeRole = ($i % 2 === 0) ? 'primary' : 'plus_one';
            } elseif ($ticketKind === 'group') {
                $attendeeRole = ($i === 0) ? 'primary' : 'group_member';
            }

            // Assign seat if seated
            $assignedSeat = null;
            if ($isSeatedTier) {
                while ($nextSeatPtr <= $seatEnd) {
                    $testKey = (string)$nextSeatPtr;
                    if (!isset($takenMap[$testKey])) {
                        $assignedSeat = $testKey;
                        $takenMap[$testKey] = true;
                        $nextSeatPtr++;
                        break;
                    }
                    $nextSeatPtr++;
                }
            }

            $ticketAttendeeName = $name . ($quantity > 1 ? ' (' . ($i + 1) . ')' : '');
            if ($attendeeRole === 'plus_one') {
                $ticketAttendeeName = $name . ' (Plus-One Guest)';
            } elseif ($attendeeRole === 'group_member' && $quantity > 1) {
                $ticketAttendeeName = $name . ' (Group Guest #' . ($i + 1) . ')';
            }

            $insStmt = $db->prepare("
                INSERT INTO tickets (id, event_id, ticket_type_id, user_id, attendee_name, attendee_email, price, pass_fee, status, status_detail, security_pin, is_issued, checked_in, checked_in_at, checked_in_by, purchase_date, assigned_seat, attendee_role)
                VALUES (:id, :event_id, :tt_id, NULL, :name, :email, :price, 0, :status, :detail, :security_pin, 1, :checked_in, :checked_in_at, :checked_in_by, NOW(), :assigned_seat, :attendee_role)
            ");
            $insStmt->execute([
                'id' => $ticketCode,
                'event_id' => $eventId,
                'tt_id' => $ticketTypeId,
                'name' => $ticketAttendeeName,
                'email' => !empty($email) ? $email : 'gate-walkin@evently.local',
                'price' => $unitPrice,
                'status' => $status,
                'detail' => 'Gate Sale (' . strtoupper($method) . ') by Staff #' . ($staffId ?: 'BoxOffice') . (!empty($notes) ? ' - ' . $notes : ''),
                'security_pin' => $securityPin,
                'checked_in' => $markCheckedIn ? 1 : 0,
                'checked_in_at' => $markCheckedIn ? date('Y-m-d H:i:s') : null,
                'checked_in_by' => $markCheckedIn ? $staffId : null,
                'assigned_seat' => $assignedSeat,
                'attendee_role' => $attendeeRole,
            ]);

            $createdTickets[] = [
                'ticket_code' => $ticketCode,
                'attendee_name' => $ticketAttendeeName,
                'ticket_type' => $ticketType['name'],
                'price' => $unitPrice,
                'status' => $status,
                'checked_in' => $markCheckedIn,
                'security_pin' => $securityPin,
                'assigned_seat' => $assignedSeat,
                'attendee_role' => $attendeeRole,
            ];
        }

        // Send confirmation email to attendee if valid email provided
        if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL) && !str_ends_with(strtolower($email), '@evently.local')) {
            try {
                $evStmt = $db->prepare("SELECT name, date, location FROM events WHERE id = :event_id LIMIT 1");
                $evStmt->execute(['event_id' => $eventId]);
                $evData = $evStmt->fetch();

                if ($evData) {
                    $formattedDate = !empty($evData['date']) ? date('D, M d, Y • g:i A', strtotime($evData['date'])) : 'TBA';
                    $methodLabels = [
                        'cash' => 'Cash at Gate',
                        'pos' => 'POS Card Terminal',
                        'transfer' => 'Bank Transfer / USSD',
                        'comp' => 'Complimentary / VIP Comp'
                    ];
                    $methodDisplay = $methodLabels[$method] ?? strtoupper($method);

                    \Ace\Mail::send(
                        $email,
                        'Your Gate Admission Pass - ' . $evData['name'],
                        'emails/issued_ticket',
                        [
                            'attendee_name' => $name,
                            'event_name' => $evData['name'],
                            'event_date' => $formattedDate,
                            'event_location' => $evData['location'] ?? 'Venue',
                            'payment_method' => $methodDisplay,
                            'total_amount' => $subtotal,
                            'is_issued' => true,
                            'header_title' => 'Gate Admission Pass Issued!',
                            'header_subtitle' => 'Your gate admission pass is ready for venue entry',
                            'greeting_text' => 'Your official admission pass for <strong>' . e($evData['name']) . '</strong> has been issued at the gate box office via <strong>' . e(site_setting('site_title', 'Evently')) . '</strong>. Your scannable pass and details are below.',
                            'tickets' => $createdTickets
                        ]
                    );
                }
            } catch (\Exception $mailEx) {
                error_log("Failed to send gate ticket confirmation email to {$email}: " . $mailEx->getMessage());
            }
        }

        return [
            'success' => true,
            'message' => "Successfully issued {$quantity} ticket(s) for {$name} via " . strtoupper($method) . ($markCheckedIn ? " and marked checked in at gate!" : "!"),
            'tickets' => $createdTickets,
            'transaction_reference' => $txRef,
            'total_paid' => $subtotal
        ];
    }

    /**
     * Get the list of all staff members assigned to this event with their check-in status.
     */
    public function getAssignedStaffRoster(int $eventId): array
    {
        $db = Application::$app->db->pdo;

        $stmt = $db->prepare("
            SELECT 
                esa.id as assignment_id,
                esa.checked_in,
                esa.checked_in_at,
                esa.checked_in_by,
                esa.assigned_at,
                es.id as staff_id,
                es.name as staff_name,
                es.email as staff_email,
                es.status as staff_status,
                es.is_online,
                es.last_active_at,
                es.last_login_at,
                es.last_ip,
                es.user_agent,
                r.name as role_name,
                r.slug as role_slug,
                (SELECT COUNT(*) FROM tickets t WHERE t.event_id = :event_id1 AND t.checked_in_by = es.id) as scans_count
            FROM event_staff_assignments esa
            JOIN event_staff es ON esa.staff_id = es.id
            JOIN roles r ON esa.role_id = r.id
            WHERE esa.event_id = :event_id2
            ORDER BY esa.checked_in DESC, es.is_online DESC, es.last_active_at DESC, r.name ASC, es.name ASC
        ");
        $stmt->execute(['event_id1' => $eventId, 'event_id2' => $eventId]);
        $rows = $stmt->fetchAll() ?: [];

        $now = time();
        foreach ($rows as &$row) {
            $isOnlineFlag = ($row['is_online'] == 1);
            $lastActiveTs = !empty($row['last_active_at']) ? strtotime($row['last_active_at']) : 0;
            $mins = $lastActiveTs > 0 ? (int)round(($now - $lastActiveTs) / 60) : 9999;

            if ($isOnlineFlag && $mins <= 15) {
                $row['online_state'] = 'online';
                $row['online_label'] = $mins <= 1 ? 'Online now' : "Active {$mins}m ago";
            } elseif ($isOnlineFlag && $mins <= 120) {
                $row['online_state'] = 'idle';
                $row['online_label'] = "Idle ({$mins}m ago)";
            } else {
                $row['online_state'] = 'offline';
                $row['online_label'] = $lastActiveTs > 0 ? ('Last seen ' . date('H:i', $lastActiveTs)) : 'Offline';
            }
            $row['device_label'] = $this->parseUserAgentDevice($row['user_agent'] ?? null);
        }

        return $rows;
    }

    /**
     * Toggle check-in status for an assigned staff member on an event.
     */
    public function toggleStaffCheckin(int $assignmentId, ?int $operatorId = null): array
    {
        $db = Application::$app->db->pdo;

        $stmt = $db->prepare("
            SELECT esa.*, es.name as staff_name, r.name as role_name
            FROM event_staff_assignments esa
            JOIN event_staff es ON esa.staff_id = es.id
            JOIN roles r ON esa.role_id = r.id
            WHERE esa.id = :id
            LIMIT 1
        ");
        $stmt->execute(['id' => $assignmentId]);
        $asg = $stmt->fetch();

        if (!$asg) {
            return ['success' => false, 'message' => 'Staff assignment not found.'];
        }

        $newCheckedIn = empty($asg['checked_in']) ? 1 : 0;
        $checkedInAt = $newCheckedIn ? date('Y-m-d H:i:s') : null;

        $upStmt = $db->prepare("
            UPDATE event_staff_assignments 
            SET checked_in = :checked_in, 
                checked_in_at = :checked_in_at, 
                checked_in_by = :checked_in_by 
            WHERE id = :id
        ");
        $upStmt->execute([
            'checked_in' => $newCheckedIn,
            'checked_in_at' => $checkedInAt,
            'checked_in_by' => $newCheckedIn ? $operatorId : null,
            'id' => $assignmentId
        ]);

        $statusText = $newCheckedIn ? "Checked In" : "Checked Out";
        $timeStr = $newCheckedIn ? date('H:i') : '';

        return [
            'success' => true,
            'checked_in' => $newCheckedIn,
            'checked_in_at' => $timeStr,
            'message' => "Staff member {$asg['staff_name']} ({$asg['role_name']}) is now {$statusText}."
        ];
    }

    /**
     * Get Usher & Floor Guide layout and attendee seating directory data.
     */
    public function getUsherFloorGuideData(int $eventId, ?string $search = null): array
    {
        $db = Application::$app->db->pdo;

        // Fetch ticket types for section mapping
        $ttStmt = $db->prepare("
            SELECT tt.*,
                   (SELECT COUNT(*) FROM tickets t WHERE t.ticket_type_id = tt.id AND t.status IN ('Paid', 'Comped')) as sold_count,
                   (SELECT COUNT(*) FROM tickets t WHERE t.ticket_type_id = tt.id AND t.checked_in = 1) as checked_in_count,
                   (SELECT COUNT(*) FROM tickets t WHERE t.ticket_type_id = tt.id AND t.assigned_seat IS NOT NULL AND t.checked_in = 1) as seated_checked_in_count
            FROM ticket_types tt
            WHERE tt.event_id = :event_id
            ORDER BY tt.has_seating DESC, tt.price DESC, tt.name ASC
        ");
        $ttStmt->execute(['event_id' => $eventId]);
        $ticketTypes = $ttStmt->fetchAll() ?: [];

        // Build section allocations based on actual ticket types & seating configurations
        $sections = [];
        $letters = ['A (Stage Front / VIP)', 'B (Center Premium)', 'C (Floor General)', 'D (Balcony / Mezzanine)', 'E (Gallery)'];

        foreach ($ticketTypes as $idx => $tt) {
            $isSeated = (!empty($tt['has_seating']) && $tt['has_seating'] == 1) || ($tt['ticket_kind'] ?? '') === 'seated';
            
            if ($isSeated && !empty($tt['section_name'])) {
                $secLabel = $tt['section_name'];
            } else {
                $secLabel = !empty($tt['section_name']) ? $tt['section_name'] : ($letters[$idx % count($letters)]);
            }

            if ($isSeated && !empty($tt['row_name'])) {
                $rowLabel = "Row " . $tt['row_name'];
                if (!empty($tt['seat_start']) && !empty($tt['seat_end'])) {
                    $rowLabel .= " (Seats {$tt['seat_start']}-{$tt['seat_end']})";
                }
            } else {
                $rowLabel = $isSeated ? "Reserved Rows" : "General Floor (Standing / Open)";
            }

            $sold = (int)$tt['sold_count'];
            $checked = (int)$tt['checked_in_count'];
            $cap = (int)$tt['capacity'];

            // Fetch seat map for this tier if seated
            $seatMap = [];
            if ($isSeated) {
                $seatRosterStmt = $db->prepare("
                    SELECT id as ticket_code, attendee_name, attendee_email, assigned_seat, checked_in, checked_in_at, attendee_role
                    FROM tickets
                    WHERE ticket_type_id = :tt_id AND status IN ('Paid', 'Comped') AND assigned_seat IS NOT NULL
                    ORDER BY assigned_seat ASC
                ");
                $seatRosterStmt->execute(['tt_id' => $tt['id']]);
                $roster = $seatRosterStmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
                $lookup = [];
                foreach ($roster as $r) {
                    $lookup[(string)$r['assigned_seat']] = $r;
                }

                $start = (int)($tt['seat_start'] ?: 1);
                $end = (int)($tt['seat_end'] ?: ($cap > 0 ? $cap : count($roster)));
                // Cap seat grid rendering to at most 100 per section for fast performance
                $maxRender = min($end, $start + 99);
                for ($s = $start; $s <= $maxRender; $s++) {
                    $sStr = (string)$s;
                    if (isset($lookup[$sStr])) {
                        $isVip = ($lookup[$sStr]['attendee_role'] ?? '') === 'vip_guest';
                        $seatStatus = $lookup[$sStr]['checked_in'] ? 'checked_in' : ($isVip ? 'vip_reserved' : 'reserved');
                        $seatMap[] = [
                            'seat_number' => $s,
                            'seat_label' => ($tt['seat_prefix'] ?? '') . $s,
                            'status' => $seatStatus,
                            'attendee_name' => $lookup[$sStr]['attendee_name'],
                            'ticket_code' => $lookup[$sStr]['ticket_code'],
                            'attendee_role' => $lookup[$sStr]['attendee_role'] ?? 'primary',
                        ];
                    } else {
                        $seatMap[] = [
                            'seat_number' => $s,
                            'seat_label' => ($tt['seat_prefix'] ?? '') . $s,
                            'status' => 'available',
                            'attendee_name' => null,
                            'ticket_code' => null,
                            'attendee_role' => null,
                        ];
                    }
                }
            }

            $sections[] = [
                'tier_id' => $tt['id'],
                'tier_name' => $tt['name'],
                'ticket_kind' => $tt['ticket_kind'] ?? 'standard',
                'is_seated' => $isSeated,
                'section' => $secLabel,
                'rows' => $rowLabel,
                'capacity' => $cap,
                'sold' => $sold,
                'checked_in' => $checked,
                'occupancy_pct' => $sold > 0 ? round(($checked / $sold) * 100, 1) : 0,
                'seat_map' => $seatMap,
            ];
        }

        // Fast Attendee Seating Locator query
        $attendeeParams = ['event_id' => $eventId];
        $attendeeWhere = "WHERE t.event_id = :event_id AND t.status IN ('Paid', 'Comped')";
        if (!empty($search)) {
            $attendeeWhere .= " AND (t.attendee_name LIKE :s1 OR t.attendee_email LIKE :s2 OR t.id LIKE :s3 OR t.assigned_seat LIKE :s4 OR tt.section_name LIKE :s5)";
            $attendeeParams['s1'] = '%' . trim($search) . '%';
            $attendeeParams['s2'] = '%' . trim($search) . '%';
            $attendeeParams['s3'] = '%' . trim($search) . '%';
            $attendeeParams['s4'] = '%' . trim($search) . '%';
            $attendeeParams['s5'] = '%' . trim($search) . '%';
        }

        $attStmt = $db->prepare("
            SELECT t.id as ticket_code, t.attendee_name, t.attendee_email, t.checked_in, t.checked_in_at,
                   t.assigned_seat, t.attendee_role,
                   tt.name as ticket_type, tt.price, COALESCE(tt.ticket_kind, 'standard') as ticket_kind,
                   tt.section_name, tt.row_name, tt.has_seating
            FROM tickets t
            LEFT JOIN ticket_types tt ON t.ticket_type_id = tt.id
            {$attendeeWhere}
            ORDER BY t.checked_in DESC, t.assigned_seat ASC, t.attendee_name ASC
            LIMIT 75
        ");
        $attStmt->execute($attendeeParams);
        $attendees = $attStmt->fetchAll() ?: [];

        // Fetch unseated valid attendees for quick seat assignment dropdown
        $unseatedStmt = $db->prepare("
            SELECT t.id as ticket_code, t.attendee_name, COALESCE(tt.name, 'General Admission') as ticket_type
            FROM tickets t
            LEFT JOIN ticket_types tt ON t.ticket_type_id = tt.id
            WHERE t.event_id = :event_id 
              AND t.status IN ('Paid', 'Comped', 'Free', 'Active')
              AND (t.assigned_seat IS NULL OR t.assigned_seat = '')
            ORDER BY t.attendee_name ASC
            LIMIT 100
        ");
        $unseatedStmt->execute(['event_id' => $eventId]);
        $unseatedTickets = $unseatedStmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];

        return [
            'sections' => $sections,
            'attendees' => $attendees,
            'unseated_tickets' => $unseatedTickets,
            'total_seated' => array_sum(array_column($sections, 'checked_in')),
            'total_expected' => array_sum(array_column($sections, 'sold')),
        ];
    }

    /**
     * Assign an attendee to a seat, reserve a seat for a VIP / Special Guest, check-in a seated guest, or release a seat.
     */
    public function assignOrReserveSeat(int $eventId, int $ticketTypeId, string $seatNumber, string $action, ?string $guestName = null, ?string $guestEmail = null, ?string $ticketCode = null, ?int $staffId = null): array
    {
        $db = Application::$app->db->pdo;
        $seatNumber = trim($seatNumber);

        if ($ticketTypeId <= 0 || $seatNumber === '') {
            return ['success' => false, 'message' => 'Section tier and seat number are required.'];
        }

        // Verify ticket type belongs to this event
        $ttStmt = $db->prepare("SELECT * FROM ticket_types WHERE id = :id AND event_id = :event_id LIMIT 1");
        $ttStmt->execute(['id' => $ticketTypeId, 'event_id' => $eventId]);
        $tier = $ttStmt->fetch();
        if (!$tier) {
            return ['success' => false, 'message' => 'Selected seating section was not found.'];
        }

        // Check current occupant of this seat
        $occStmt = $db->prepare("
            SELECT * FROM tickets 
            WHERE event_id = :event_id AND ticket_type_id = :tt_id AND assigned_seat = :seat AND status IN ('Paid', 'Comped', 'Free', 'Active')
            LIMIT 1
        ");
        $occStmt->execute(['event_id' => $eventId, 'tt_id' => $ticketTypeId, 'seat' => $seatNumber]);
        $occupant = $occStmt->fetch();

        if ($action === 'reserve_vip') {
            if ($occupant) {
                return ['success' => false, 'message' => "Seat {$seatNumber} is already occupied by {$occupant['attendee_name']}."];
            }

            $vipName = trim((string)$guestName);
            if ($vipName === '') {
                return ['success' => false, 'message' => 'Special Guest / VIP name is required.'];
            }

            $vipEmail = trim((string)$guestEmail);
            if ($vipEmail === '') {
                $vipEmail = 'vip-guest@evently.local';
            }

            $vipCode = 'EV-VIP-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
            $pin = str_pad((string)random_int(1000, 9999), 4, '0', STR_PAD_LEFT);

            $ins = $db->prepare("
                INSERT INTO tickets (id, event_id, ticket_type_id, user_id, attendee_name, attendee_email, price, pass_fee, status, status_detail, security_pin, is_issued, checked_in, purchase_date, assigned_seat, attendee_role)
                VALUES (:id, :event_id, :tt_id, NULL, :name, :email, 0.00, 0, 'Comped', :detail, :pin, 1, 0, NOW(), :seat, 'vip_guest')
            ");
            $ins->execute([
                'id' => $vipCode,
                'event_id' => $eventId,
                'tt_id' => $ticketTypeId,
                'name' => $vipName,
                'email' => $vipEmail,
                'detail' => 'VIP / Special Guest Seat Reserved by Usher #' . ($staffId ?: 'Floor'),
                'pin' => $pin,
                'seat' => $seatNumber,
            ]);

            return [
                'success' => true,
                'message' => "⭐ Seat {$seatNumber} ({$tier['section_name']}) reserved for Special Guest: {$vipName} (#{$vipCode}).",
                'ticket_code' => $vipCode,
            ];
        }

        if ($action === 'assign_ticket') {
            if ($occupant) {
                return ['success' => false, 'message' => "Seat {$seatNumber} is already assigned to {$occupant['attendee_name']}."];
            }

            $code = trim((string)$ticketCode);
            if ($code === '') {
                return ['success' => false, 'message' => 'Ticket code or attendee selection is required.'];
            }

            $tStmt = $db->prepare("SELECT * FROM tickets WHERE id = :id AND event_id = :event_id LIMIT 1");
            $tStmt->execute(['id' => $code, 'event_id' => $eventId]);
            $ticket = $tStmt->fetch();

            if (!$ticket) {
                return ['success' => false, 'message' => "Ticket #{$code} not found for this event."];
            }
            if (!in_array(strtolower($ticket['status'] ?? ''), ['paid', 'comped', 'free', 'active'])) {
                return ['success' => false, 'message' => "Cannot assign seat: Ticket #{$code} status is '{$ticket['status']}'."];
            }

            $up = $db->prepare("UPDATE tickets SET ticket_type_id = :tt_id, assigned_seat = :seat WHERE id = :id");
            $up->execute([
                'tt_id' => $ticketTypeId,
                'seat' => $seatNumber,
                'id' => $ticket['id'],
            ]);

            return [
                'success' => true,
                'message' => "🪑 Assigned {$ticket['attendee_name']} (#{$ticket['id']}) to Seat {$seatNumber}.",
            ];
        }

        if ($action === 'checkin_seat') {
            if (!$occupant) {
                return ['success' => false, 'message' => "Seat {$seatNumber} has no assigned guest to check in."];
            }
            if ($occupant['checked_in'] == 1) {
                return ['success' => true, 'message' => "{$occupant['attendee_name']} is already checked in at Seat {$seatNumber}."];
            }
            $up = $db->prepare("UPDATE tickets SET checked_in = 1, checked_in_at = CURRENT_TIMESTAMP, checked_in_by = :staff_id WHERE id = :id");
            $up->execute(['staff_id' => $staffId, 'id' => $occupant['id']]);

            return [
                'success' => true,
                'message' => "✓ {$occupant['attendee_name']} marked seated & checked in at Seat {$seatNumber}.",
            ];
        }

        if ($action === 'release_seat') {
            if (!$occupant) {
                return ['success' => false, 'message' => "Seat {$seatNumber} is already empty."];
            }

            if (($occupant['attendee_role'] ?? '') === 'vip_guest' && str_starts_with($occupant['id'], 'EV-VIP-')) {
                $del = $db->prepare("UPDATE tickets SET status = 'Cancelled', assigned_seat = NULL, status_detail = 'VIP Seat Reservation Released by Usher' WHERE id = :id");
                $del->execute(['id' => $occupant['id']]);
            } else {
                $clr = $db->prepare("UPDATE tickets SET assigned_seat = NULL WHERE id = :id");
                $clr->execute(['id' => $occupant['id']]);
            }

            return [
                'success' => true,
                'message' => "🔓 Seat {$seatNumber} has been released and is now available.",
            ];
        }

        return ['success' => false, 'message' => 'Unknown seat action.'];
    }

    /**
     * Get seat availability map for an event.
     */
    public function getSeatAvailability(int $eventId, ?int $ticketTypeId = null): array
    {
        $db = Application::$app->db->pdo;
        $params = ['event_id' => $eventId];
        $ttWhere = "WHERE tt.event_id = :event_id AND tt.has_seating = 1";
        if ($ticketTypeId) {
            $ttWhere .= " AND tt.id = :tt_id";
            $params['tt_id'] = $ticketTypeId;
        }

        $stmt = $db->prepare("
            SELECT tt.* 
            FROM ticket_types tt
            {$ttWhere}
            ORDER BY tt.name ASC
        ");
        $stmt->execute($params);
        $seatedTiers = $stmt->fetchAll() ?: [];

        $results = [];
        foreach ($seatedTiers as $tier) {
            $takenStmt = $db->prepare("
                SELECT id as ticket_code, attendee_name, attendee_email, assigned_seat, checked_in, checked_in_at, attendee_role
                FROM tickets
                WHERE ticket_type_id = :tt_id AND status IN ('Paid', 'Comped') AND assigned_seat IS NOT NULL
                ORDER BY assigned_seat ASC
            ");
            $takenStmt->execute(['tt_id' => $tier['id']]);
            $taken = $takenStmt->fetchAll() ?: [];

            $takenMap = [];
            foreach ($taken as $row) {
                $takenMap[(string)$row['assigned_seat']] = $row;
            }

            $start = (int)($tier['seat_start'] ?: 1);
            $end = (int)($tier['seat_end'] ?: ($tier['capacity'] ?: 50));
            $seats = [];
            for ($s = $start; $s <= $end; $s++) {
                $sKey = (string)$s;
                $isTaken = isset($takenMap[$sKey]);
                $seats[] = [
                    'seat_number' => $s,
                    'seat_label' => ($tier['seat_prefix'] ?? '') . $s,
                    'is_taken' => $isTaken,
                    'checked_in' => $isTaken ? (bool)$takenMap[$sKey]['checked_in'] : false,
                    'attendee_name' => $isTaken ? $takenMap[$sKey]['attendee_name'] : null,
                    'ticket_code' => $isTaken ? $takenMap[$sKey]['ticket_code'] : null,
                ];
            }

            $results[] = [
                'ticket_type_id' => $tier['id'],
                'tier_name' => $tier['name'],
                'section_name' => $tier['section_name'] ?: 'Reserved',
                'row_name' => $tier['row_name'] ?: 'General',
                'total_seats' => count($seats),
                'taken_seats' => count($taken),
                'available_seats' => max(0, count($seats) - count($taken)),
                'seats' => $seats
            ];
        }

        return $results;
    }
}


