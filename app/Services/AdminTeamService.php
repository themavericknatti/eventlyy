<?php

namespace App\Services;

use Ace\Application;

class AdminTeamService
{
    /**
     * Get statistics for team staff.
     */
    public function getStaffStats(): array
    {
        (new TeamService())->cleanupCompletedAssignments();

        $db = Application::$app->db->pdo;

        $totalStaff = (int)$db->query("SELECT COUNT(*) FROM event_staff")->fetchColumn();
        
        // Assigned staff = count of unique staff_ids in active event_staff_assignments
        $assignedStaff = (int)$db->query("SELECT COUNT(DISTINCT staff_id) FROM event_staff_assignments")->fetchColumn();
        
        $unassignedStaff = max(0, $totalStaff - $assignedStaff);
        $activeEventsStaff = (int)$db->query("SELECT COUNT(DISTINCT event_id) FROM event_staff_assignments")->fetchColumn();
        $inactiveStaff = (int)$db->query("SELECT COUNT(*) FROM event_staff WHERE status = 'inactive'")->fetchColumn();
        $activeStaff = max(0, $totalStaff - $inactiveStaff);

        $assignedPercent = $totalStaff > 0 ? round(($assignedStaff / $totalStaff) * 100) : 0;
        $unassignedPercent = $totalStaff > 0 ? round(($unassignedStaff / $totalStaff) * 100) : 0;
        $inactivePercent = $totalStaff > 0 ? round(($inactiveStaff / $totalStaff) * 100) : 0;

        // Fetch count of active assignments per role for visual distribution breakdown
        $rolesBreakdownStmt = $db->query("
            SELECT r.id, r.name, r.slug, COUNT(esa.id) as assignment_count
            FROM roles r
            LEFT JOIN event_staff_assignments esa ON r.id = esa.role_id
            WHERE r.slug NOT IN ('admin', 'super-admin', 'user')
            GROUP BY r.id, r.name, r.slug
            ORDER BY assignment_count DESC, r.name ASC
        ");
        $rolesBreakdown = $rolesBreakdownStmt->fetchAll() ?: [];

        return [
            'total_staff' => $totalStaff,
            'assigned_staff' => $assignedStaff,
            'unassigned_staff' => $unassignedStaff,
            'active_staff' => $activeStaff,
            'inactive_staff' => $inactiveStaff,
            'active_events_staff' => $activeEventsStaff,
            'assigned_percent' => $assignedPercent,
            'unassigned_percent' => $unassignedPercent,
            'inactive_percent' => $inactivePercent,
            'roles_breakdown' => $rolesBreakdown,
        ];
    }

    /**
     * Fetch paginated staff members with filters.
     */
    public function getStaffPaginated(int $page = 1, int $perPage = 10, ?string $search = null, ?string $roleId = null, ?string $status = null, ?string $eventId = null): array
    {
        (new TeamService())->cleanupCompletedAssignments();

        $db = Application::$app->db->pdo;
        $page = max(1, $page);
        $offset = ($page - 1) * $perPage;

        $where = [];
        $params = [];

        if (!empty($search)) {
            $where[] = "(es.name LIKE :search1 OR es.email LIKE :search2)";
            $params['search1'] = '%' . $search . '%';
            $params['search2'] = '%' . $search . '%';
        }

        if (!empty($status)) {
            $where[] = "es.status = :status";
            $params['status'] = $status;
        }

        if (!empty($roleId)) {
            $where[] = "es.id IN (SELECT staff_id FROM event_staff_assignments WHERE role_id = :role_id)";
            $params['role_id'] = $roleId;
        }

        if (!empty($eventId)) {
            if ($eventId === 'unassigned') {
                $where[] = "es.id NOT IN (SELECT staff_id FROM event_staff_assignments)";
            } else {
                $where[] = "es.id IN (SELECT staff_id FROM event_staff_assignments WHERE event_id = :event_id)";
                $params['event_id'] = $eventId;
            }
        }

        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        // Total count for pagination
        $countStmt = $db->prepare("SELECT COUNT(*) FROM event_staff es {$whereClause}");
        $countStmt->execute($params);
        $totalItems = (int)$countStmt->fetchColumn();
        $totalPages = max(1, (int)ceil($totalItems / $perPage));

        // Query staff items
        $query = "
            SELECT 
                es.*,
                (SELECT COUNT(*) FROM event_staff_assignments esa WHERE esa.staff_id = es.id) as assignment_count,
                (
                    SELECT GROUP_CONCAT(CONCAT(e.name, ' (', r.name, ')') SEPARATOR ', ')
                    FROM event_staff_assignments esa2
                    JOIN events e ON esa2.event_id = e.id
                    JOIN roles r ON esa2.role_id = r.id
                    WHERE esa2.staff_id = es.id
                ) as assignments_summary
            FROM event_staff es
            {$whereClause}
            ORDER BY es.name ASC
            LIMIT :limit OFFSET :offset
        ";

        $stmt = $db->prepare($query);
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }
        $stmt->bindValue(':limit', $perPage, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();
        $items = $stmt->fetchAll() ?: [];

        return [
            'staff' => $items,
            'total_items' => $totalItems,
            'current_page' => $page,
            'per_page' => $perPage,
            'total_pages' => $totalPages,
        ];
    }

    /**
     * Fetch all matching staff records for CSV export.
     */
    public function getAllStaffForExport(?string $search = null, ?string $roleId = null, ?string $status = null, ?string $eventId = null): array
    {
        (new TeamService())->cleanupCompletedAssignments();

        $db = Application::$app->db->pdo;
        $where = [];
        $params = [];

        if (!empty($search)) {
            $where[] = "(es.name LIKE :search1 OR es.email LIKE :search2)";
            $params['search1'] = '%' . $search . '%';
            $params['search2'] = '%' . $search . '%';
        }

        if (!empty($status)) {
            $where[] = "es.status = :status";
            $params['status'] = $status;
        }

        if (!empty($roleId)) {
            $where[] = "es.id IN (SELECT staff_id FROM event_staff_assignments WHERE role_id = :role_id)";
            $params['role_id'] = $roleId;
        }

        if (!empty($eventId)) {
            if ($eventId === 'unassigned') {
                $where[] = "es.id NOT IN (SELECT staff_id FROM event_staff_assignments)";
            } else {
                $where[] = "es.id IN (SELECT staff_id FROM event_staff_assignments WHERE event_id = :event_id)";
                $params['event_id'] = $eventId;
            }
        }

        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        $query = "
            SELECT 
                es.*,
                (SELECT COUNT(*) FROM event_staff_assignments esa WHERE esa.staff_id = es.id) as assignment_count,
                (
                    SELECT GROUP_CONCAT(CONCAT(e.name, ' [', r.name, ']') SEPARATOR '; ')
                    FROM event_staff_assignments esa2
                    JOIN events e ON esa2.event_id = e.id
                    JOIN roles r ON esa2.role_id = r.id
                    WHERE esa2.staff_id = es.id
                ) as assignments_summary
            FROM event_staff es
            {$whereClause}
            ORDER BY es.name ASC
        ";

        $stmt = $db->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll() ?: [];
    }

    /**
     * Fetch detail for a single staff member, including upcoming events for quick modal assignment.
     */
    public function getStaffDetail(int|string $staffId): ?array
    {
        $db = Application::$app->db->pdo;

        $stmt = $db->prepare("SELECT * FROM event_staff WHERE id = :id");
        $stmt->execute(['id' => $staffId]);
        $staff = $stmt->fetch();

        if (!$staff) {
            return null;
        }

        $assignmentsStmt = $db->prepare("
            SELECT 
                esa.id as assignment_id,
                esa.assigned_at,
                e.id as event_id,
                e.name as event_name,
                e.date as event_date,
                e.location as event_location,
                r.id as role_id,
                r.name as role_name,
                r.slug as role_slug
            FROM event_staff_assignments esa
            JOIN events e ON esa.event_id = e.id
            JOIN roles r ON esa.role_id = r.id
            WHERE esa.staff_id = :staff_id
            ORDER BY esa.assigned_at DESC
        ");
        $assignmentsStmt->execute(['staff_id' => $staffId]);
        $assignments = $assignmentsStmt->fetchAll() ?: [];

        $upcomingEvents = $this->getUpcomingEvents();
        $roles = $this->getAllRoles();

        return [
            'staff' => $staff,
            'assignments' => $assignments,
            'upcoming_events' => $upcomingEvents,
            'roles' => $roles,
        ];
    }

    /**
     * Fetch upcoming active events (excluding completed, cancelled, past, or draft events).
     */
    public function getUpcomingEvents(): array
    {
        $db = Application::$app->db->pdo;
        $completionHours = event_completion_hours();

        $stmt = $db->query("
            SELECT id, name, date, status 
            FROM events 
            WHERE status = 'published'
              AND (date IS NULL OR date >= NOW() - INTERVAL {$completionHours} HOUR)
            ORDER BY date ASC, name ASC
        ");
        return $stmt->fetchAll() ?: [];
    }

    /**
     * Fetch all upcoming events for assignment.
     */
    public function getAllEvents(): array
    {
        return $this->getUpcomingEvents();
    }

    /**
     * Fetch all non-admin & non-user RBAC roles (for event staff assignment).
     */
    public function getAllRoles(): array
    {
        $db = Application::$app->db->pdo;
        return $db->query("SELECT id, name, slug, description FROM roles WHERE slug NOT IN ('admin', 'super-admin', 'user') ORDER BY name ASC")->fetchAll() ?: [];
    }

    /**
     * Fetch pending event staffing assistance requests from hosts.
     */
    public function getPendingStaffRequests(): array
    {
        $db = Application::$app->db->pdo;
        $completionHours = event_completion_hours();

        $stmt = $db->query("
            SELECT 
                esr.id,
                esr.event_id,
                esr.host_id,
                esr.roles_requested,
                esr.notes,
                esr.status,
                esr.created_at,
                e.name as event_name,
                e.date as event_date,
                e.location as event_location,
                u.name as host_name,
                u.email as host_email
            FROM event_staff_requests esr
            JOIN events e ON esr.event_id = e.id
            JOIN users u ON esr.host_id = u.id
            WHERE esr.status IN ('pending', 'partially_fulfilled')
              AND e.status = 'published'
              AND (e.date IS NULL OR e.date >= NOW() - INTERVAL {$completionHours} HOUR)
            ORDER BY esr.created_at DESC
        ");
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        foreach ($rows as &$row) {
            if (!empty($row['roles_requested'])) {
                $row['roles_array'] = is_string($row['roles_requested']) ? json_decode($row['roles_requested'], true) : $row['roles_requested'];
            } else {
                $row['roles_array'] = [];
            }
        }
        return $rows;
    }
}
