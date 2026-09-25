<?php

namespace App\Services;

use Ace\Application;

class AdminAttendeeService
{
    /**
     * Get summary statistics for attendees (both registered and guest buyers).
     */
    public function getAttendeeStats(): array
    {
        $db = Application::$app->db->pdo;

        $uniqueAttendees = (int)$db->query("SELECT COUNT(DISTINCT attendee_email) FROM tickets WHERE status IN ('Paid', 'Comped')")->fetchColumn();
        
        $guestAttendees = (int)$db->query("SELECT COUNT(DISTINCT attendee_email) FROM tickets WHERE user_id IS NULL AND status IN ('Paid', 'Comped')")->fetchColumn();
        
        $registeredAttendees = (int)$db->query("SELECT COUNT(DISTINCT attendee_email) FROM tickets WHERE user_id IS NOT NULL AND status IN ('Paid', 'Comped')")->fetchColumn();
        
        $totalPaidTickets = (int)$db->query("SELECT COUNT(*) FROM tickets WHERE status IN ('Paid', 'Comped')")->fetchColumn();

        return [
            'unique_attendees' => $uniqueAttendees,
            'guest_attendees' => $guestAttendees,
            'registered_attendees' => $registeredAttendees,
            'total_paid_tickets' => $totalPaidTickets
        ];
    }

    /**
     * Get a filtered and paginated list of attendees grouped by unique email.
     */
    public function getFilteredAttendees(string $search = '', string $type = '', string $eventId = '', int $page = 1, int $limit = 10): array
    {
        $db = Application::$app->db->pdo;

        $conditions = ["t.status IN ('Paid', 'Comped')"];
        $binds = [];

        if (!empty($search)) {
            $conditions[] = "(t.attendee_name LIKE ? OR t.attendee_email LIKE ? OR e.name LIKE ?)";
            $binds[] = "%$search%";
            $binds[] = "%$search%";
            $binds[] = "%$search%";
        }

        if ($type === 'guest') {
            $conditions[] = "t.user_id IS NULL";
        } elseif ($type === 'registered') {
            $conditions[] = "t.user_id IS NOT NULL";
        }

        if (!empty($eventId)) {
            $conditions[] = "t.event_id = ?";
            $binds[] = $eventId;
        }

        $whereClause = implode(' AND ', $conditions);

        // 1. Get total unique email contacts count
        $countStmt = $db->prepare("
            SELECT COUNT(DISTINCT t.attendee_email) 
            FROM tickets t
            LEFT JOIN events e ON t.event_id = e.id
            WHERE {$whereClause}
        ");
        $countStmt->execute($binds);
        $total = (int)$countStmt->fetchColumn();

        // 2. Calculate offset
        $offset = ($page - 1) * $limit;

        // 3. Get paginated grouped records
        $stmt = $db->prepare("
            SELECT t.attendee_email,
                   MAX(t.attendee_name) as attendee_name,
                   MAX(t.user_id) as user_id,
                   COUNT(t.id) as total_tickets,
                   COUNT(DISTINCT t.event_id) as total_events,
                   SUM(t.price) as total_spent,
                   MAX(t.purchase_date) as last_purchase_date,
                   GROUP_CONCAT(DISTINCT e.name) as event_names,
                   CASE WHEN MAX(t.user_id) IS NOT NULL THEN 'Registered User' ELSE 'Guest Attendee' END as attendee_type
            FROM tickets t
            LEFT JOIN events e ON t.event_id = e.id
            WHERE {$whereClause}
            GROUP BY t.attendee_email
            ORDER BY last_purchase_date DESC
            LIMIT ? OFFSET ?
        ");

        $paramIdx = 1;
        foreach ($binds as $val) {
            $stmt->bindValue($paramIdx++, $val, \PDO::PARAM_STR);
        }
        $stmt->bindValue($paramIdx++, $limit, \PDO::PARAM_INT);
        $stmt->bindValue($paramIdx++, $offset, \PDO::PARAM_INT);
        $stmt->execute();
        $items = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];

        return [
            'items' => $items,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'totalPages' => (int)ceil($total / $limit)
        ];
    }

    /**
     * Get all grouped attendee records for CSV export (unpaginated).
     */
    public function getAllAttendeesForExport(string $search = '', string $type = '', string $eventId = ''): array
    {
        $db = Application::$app->db->pdo;

        $conditions = ["t.status IN ('Paid', 'Comped')"];
        $binds = [];

        if (!empty($search)) {
            $conditions[] = "(t.attendee_name LIKE ? OR t.attendee_email LIKE ? OR e.name LIKE ?)";
            $binds[] = "%$search%";
            $binds[] = "%$search%";
            $binds[] = "%$search%";
        }

        if ($type === 'guest') {
            $conditions[] = "t.user_id IS NULL";
        } elseif ($type === 'registered') {
            $conditions[] = "t.user_id IS NOT NULL";
        }

        if (!empty($eventId)) {
            $conditions[] = "t.event_id = ?";
            $binds[] = $eventId;
        }

        $whereClause = implode(' AND ', $conditions);

        $stmt = $db->prepare("
            SELECT t.attendee_email,
                   MAX(t.attendee_name) as attendee_name,
                   CASE WHEN MAX(t.user_id) IS NOT NULL THEN 'Registered User' ELSE 'Guest Attendee' END as attendee_type,
                   COUNT(t.id) as total_tickets,
                   COUNT(DISTINCT t.event_id) as total_events,
                   SUM(t.price) as total_spent,
                   MAX(t.purchase_date) as last_purchase_date,
                   GROUP_CONCAT(DISTINCT e.name) as event_names
            FROM tickets t
            LEFT JOIN events e ON t.event_id = e.id
            WHERE {$whereClause}
            GROUP BY t.attendee_email
            ORDER BY last_purchase_date DESC
        ");
        $stmt->execute($binds);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Get events list for selector filter.
     */
    public function getEventsForFilter(): array
    {
        $db = Application::$app->db->pdo;
        $stmt = $db->query("SELECT id, name FROM events ORDER BY name ASC");
        return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
    }
}
