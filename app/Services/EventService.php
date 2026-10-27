<?php

namespace App\Services;

use App\Models\Event;
use Ace\Application;

class EventService
{
    /**
     * Fetch all published events for the public welcome page with search, location, category filters, and pagination.
     */
    public function getPublicEvents(string $search = '', string $location = '', string $category = '', int $page = 1, int $limit = 6): array
    {
        $db = Application::$app->db->pdo;
        $completionHours = event_completion_hours();
        $conditions = [
            "e.status = 'published'",
            "e.date >= NOW() - INTERVAL {$completionHours} HOUR",
            "(
                e.recurrence_group_id IS NULL 
                OR e.recurrence_group_id = ''
                OR e.id = (
                    SELECT sub.id 
                    FROM events sub 
                    WHERE sub.recurrence_group_id = e.recurrence_group_id 
                      AND sub.status = 'published'
                      AND sub.date >= NOW() - INTERVAL {$completionHours} HOUR
                    ORDER BY sub.date ASC, sub.recurrence_sequence ASC, sub.id ASC
                    LIMIT 1
                )
            )"
        ];
        $params = [];

        if (!empty($search)) {
            $conditions[] = "(e.name LIKE :s1 OR e.description LIKE :s2 OR e.location LIKE :s3)";
            $params['s1'] = "%$search%";
            $params['s2'] = "%$search%";
            $params['s3'] = "%$search%";
        }

        if (!empty($location)) {
            $conditions[] = "e.location LIKE :location";
            $params['location'] = "%$location%";
        }

        if (!empty($category) && strtolower($category) !== 'all') {
            $conditions[] = "(e.category = :category OR e.category LIKE :cat_like)";
            $params['category'] = $category;
            $params['cat_like'] = "%$category%";
        }

        $whereClause = implode(' AND ', $conditions);

        // 1. Get total count
        $countStmt = $db->prepare("
            SELECT COUNT(*) 
            FROM events e
            WHERE {$whereClause}
        ");
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();

        // 2. Offset calculation
        $page = max(1, $page);
        $limit = max(1, $limit);
        $offset = ($page - 1) * $limit;
        $totalPages = max(1, (int)ceil($total / $limit));

        // 3. Fetch paginated events
        $stmt = $db->prepare("
            SELECT e.*, 
                   u.name as organizer_name,
                   tt.min_price,
                   IFNULL(t.tickets_sold, 0) as tickets_sold,
                   IFNULL(r.avg_rating, 0) as avg_rating
            FROM events e
            INNER JOIN users u ON e.organizer_id = u.id
            LEFT JOIN (
                SELECT event_id, MIN(price) as min_price 
                FROM ticket_types 
                GROUP BY event_id
            ) tt ON tt.event_id = e.id
            LEFT JOIN (
                SELECT event_id, COUNT(*) as tickets_sold 
                FROM tickets 
                WHERE status IN ('Paid', 'Comped') 
                GROUP BY event_id
            ) t ON t.event_id = e.id
            LEFT JOIN (
                SELECT event_id, AVG(rating) as avg_rating 
                FROM event_reviews 
                GROUP BY event_id
            ) r ON r.event_id = e.id
            WHERE {$whereClause}
            ORDER BY e.is_featured DESC, tickets_sold DESC, e.date ASC
            LIMIT :limit OFFSET :offset
        ");

        foreach ($params as $key => $val) {
            $stmt->bindValue(":$key", $val, \PDO::PARAM_STR);
        }
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();

        $events = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];

        return [
            'events' => $events,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'totalPages' => $totalPages
        ];
    }

    /**
     * Fetch featured / high-importance published events for the hero carousel.
     * Prioritizes admin-selected featured events (is_featured = 1), high ticket sales, top host ratings, and upcoming dates.
     */
    public function getFeaturedEvents(int $limit = 5): array
    {
        $db = Application::$app->db->pdo;
        $completionHours = event_completion_hours();
        $stmt = $db->prepare("
            SELECT e.*, 
                   u.name as organizer_name,
                   tt.min_price,
                   IFNULL(t.tickets_sold, 0) as tickets_sold,
                   IFNULL(r.avg_rating, 0) as avg_rating,
                   IFNULL(r.review_count, 0) as review_count
            FROM events e
            INNER JOIN users u ON e.organizer_id = u.id
            LEFT JOIN (
                SELECT event_id, MIN(price) as min_price 
                FROM ticket_types 
                GROUP BY event_id
            ) tt ON tt.event_id = e.id
            LEFT JOIN (
                SELECT event_id, COUNT(*) as tickets_sold 
                FROM tickets 
                WHERE status IN ('Paid', 'Comped') 
                GROUP BY event_id
            ) t ON t.event_id = e.id
            LEFT JOIN (
                SELECT event_id, AVG(rating) as avg_rating, COUNT(*) as review_count 
                FROM event_reviews 
                GROUP BY event_id
            ) r ON r.event_id = e.id
            WHERE e.status = 'published' 
              AND e.date >= NOW() - INTERVAL {$completionHours} HOUR
              AND (
                  e.recurrence_group_id IS NULL 
                  OR e.recurrence_group_id = ''
                  OR e.id = (
                      SELECT sub.id 
                      FROM events sub 
                      WHERE sub.recurrence_group_id = e.recurrence_group_id 
                        AND sub.status = 'published'
                        AND sub.date >= NOW() - INTERVAL {$completionHours} HOUR
                      ORDER BY sub.date ASC, sub.recurrence_sequence ASC, sub.id ASC
                      LIMIT 1
                  )
              )
            ORDER BY e.is_featured DESC, tickets_sold DESC, avg_rating DESC, e.date ASC
            LIMIT :limit
        ");
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Fetch distinct cities, states, and regions for search filter dropdown.
     * Extracts and normalizes city/state/region from full venue addresses so the dropdown stays clean and concise.
     */
    public function getPublicLocations(): array
    {
        $db = Application::$app->db->pdo;
        $stmt = $db->query("SELECT DISTINCT location FROM events WHERE status = 'published' AND location IS NOT NULL AND location != ''");
        $rawLocations = $stmt->fetchAll(\PDO::FETCH_COLUMN) ?: [];

        $locations = [];
        foreach ($rawLocations as $loc) {
            $loc = trim($loc);
            if (empty($loc)) continue;

            // If location has commas ("Venue, City, State" or "Venue, City"), extract the city/region parts
            if (str_contains($loc, ',')) {
                $parts = array_map('trim', explode(',', $loc));
                // Extract primary region (last part, e.g. "Lagos", "New York", "San Francisco")
                $region = end($parts);
                if (!empty($region)) {
                    $locations[] = $region;
                }
                // If 3 parts (e.g. "Club 1602, Ikeja, Lagos"), also capture the middle area/city if distinct
                if (count($parts) >= 3 && !empty($parts[1]) && strtolower($parts[1]) !== strtolower($region)) {
                    $locations[] = $parts[1];
                }
            } else {
                $locations[] = $loc;
            }
        }

        $uniqueLocations = array_values(array_unique($locations));
        sort($uniqueLocations, SORT_NATURAL | SORT_FLAG_CASE);
        return $uniqueLocations;
    }

    /**
     * Fetch all events for a host with aggregate details (or all platform events if admin).
     * Ordered smartly: Live happening now -> Upcoming active (soonest first) -> Draft -> Completed/Past (most recent first).
     */
    public function getHostEvents(int|string $hostId, bool $isAdmin = false): array
    {
        $db = Application::$app->db->pdo;
        $completionHours = event_completion_hours();
        $whereClause = $isAdmin ? "1=1" : "e.organizer_id = :organizer_id";

        // Deduplicate recurring series to display only 1 representative occurrence
        $whereClause .= " AND (
            e.recurrence_group_id IS NULL 
            OR e.recurrence_group_id = ''
            OR e.id = (
                SELECT sub.id
                FROM events sub
                WHERE sub.recurrence_group_id = e.recurrence_group_id
                ORDER BY 
                    CASE 
                        WHEN sub.date >= NOW() - INTERVAL {$completionHours} HOUR THEN 1 
                        ELSE 2 
                    END ASC,
                    CASE 
                        WHEN sub.date >= NOW() - INTERVAL {$completionHours} HOUR THEN sub.date 
                    END ASC,
                    sub.date DESC,
                    sub.id ASC
                LIMIT 1
            )
        )";

        $stmt = $db->prepare("
            SELECT e.*, 
                   IFNULL(rec_t.series_tickets_sold, IFNULL(t.tickets_sold, 0)) as tickets_sold,
                   IFNULL(tt.total_capacity, 0) as total_capacity,
                   IFNULL(rec_t.series_revenue, IFNULL(t.revenue, 0.00)) as revenue,
                   u.name as organizer_name,
                   u.email as organizer_email
            FROM events e
            INNER JOIN users u ON e.organizer_id = u.id
            LEFT JOIN (
                SELECT event_id, 
                       COUNT(*) as tickets_sold, 
                       SUM(price) as revenue 
                FROM tickets 
                WHERE status IN ('Paid', 'Comped') 
                GROUP BY event_id
            ) t ON t.event_id = e.id
            LEFT JOIN (
                SELECT 
                    ev.recurrence_group_id,
                    COUNT(*) as series_tickets_sold,
                    SUM(t.price) as series_revenue
                FROM tickets t
                INNER JOIN events ev ON t.event_id = ev.id
                WHERE t.status IN ('Paid', 'Comped')
                  AND ev.recurrence_group_id IS NOT NULL AND ev.recurrence_group_id != ''
                GROUP BY ev.recurrence_group_id
            ) rec_t ON rec_t.recurrence_group_id = e.recurrence_group_id
            LEFT JOIN (
                SELECT event_id, SUM(capacity) as total_capacity 
                FROM ticket_types 
                GROUP BY event_id
            ) tt ON tt.event_id = e.id
            WHERE {$whereClause}
            ORDER BY 
                CASE
                    -- 1. Live Happening Now (date <= NOW() AND date >= NOW() - INTERVAL X HOUR AND status = 'published')
                    WHEN e.status = 'published' AND e.date <= NOW() AND e.date >= NOW() - INTERVAL {$completionHours} HOUR THEN 1
                    -- 2. Upcoming Active Events (date > NOW() AND status = 'published')
                    WHEN e.status = 'published' AND e.date > NOW() THEN 2
                    -- 3. Draft / Other Non-completed Events
                    WHEN e.status NOT IN ('completed', 'cancelled') AND e.date >= NOW() THEN 3
                    -- 4. Completed or Past Events
                    ELSE 4
                END ASC,
                -- For upcoming/live: chronological (nearest date first)
                CASE WHEN e.date > NOW() THEN e.date END ASC,
                -- For past/completed: reverse chronological (most recently completed first)
                e.date DESC,
                e.id DESC
        ");
        if (!$isAdmin) {
            $stmt->execute(['organizer_id' => $hostId]);
        } else {
            $stmt->execute();
        }
        return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Find a host event or throw an exception (supports admin override).
     */
    public function findHostEvent(int|string $eventId, int|string $hostId, bool $isAdmin = false): ?Event
    {
        if ($isAdmin) {
            return Event::findOne(['id' => $eventId]);
        }
        return Event::findOne(['id' => $eventId, 'organizer_id' => $hostId]);
    }

    /**
     * Fetch attendees (tickets) for an event with dynamic search and offset pagination.
     */
    public function getEventTickets(int|string $eventId, string $search = '', int $page = 1, int $limit = 10): array
    {
        $db = Application::$app->db->pdo;
        $searchQuery = "";
        $countBinds = [$eventId];
        
        if (!empty($search)) {
            $searchQuery = " AND (t.attendee_name LIKE ? OR t.attendee_email LIKE ? OR t.id LIKE ?)";
            $countBinds[] = "%$search%";
            $countBinds[] = "%$search%";
            $countBinds[] = "%$search%";
        }

        // 1. Get total count — only Paid and Comped tickets (matching "Tickets Sold" stats)
        $countStmt = $db->prepare("
            SELECT COUNT(*) 
            FROM tickets t
            INNER JOIN ticket_types tt ON t.ticket_type_id = tt.id
            WHERE t.event_id = ? AND t.status IN ('Paid', 'Comped') {$searchQuery}
        ");
        $countStmt->execute($countBinds);
        $total = (int)$countStmt->fetchColumn();

        // 2. Calculate offset
        $offset = ($page - 1) * $limit;

        // 3. Fetch paginated records — only Paid and Comped tickets
        $stmt = $db->prepare("
            SELECT t.*, tt.name as ticket_tier
            FROM tickets t
            INNER JOIN ticket_types tt ON t.ticket_type_id = tt.id
            WHERE t.event_id = ? AND t.status IN ('Paid', 'Comped') {$searchQuery}
            ORDER BY t.purchase_date DESC
            LIMIT ? OFFSET ?
        ");
        
        $stmt->bindValue(1, $eventId, \PDO::PARAM_INT);
        $paramIdx = 2;
        if (!empty($search)) {
            $stmt->bindValue($paramIdx++, "%$search%", \PDO::PARAM_STR);
            $stmt->bindValue($paramIdx++, "%$search%", \PDO::PARAM_STR);
            $stmt->bindValue($paramIdx++, "%$search%", \PDO::PARAM_STR);
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
     * Fetch ticket types breakdown for an event.
     * Separates organic sales from host-issued tickets.
     */
    public function getEventTicketTypesBreakdown(int|string $eventId): array
    {
        $db = Application::$app->db->pdo;
        $stmt = $db->prepare("
            SELECT tt.*, 
                   IFNULL(s.sold, 0) as sold,
                   IFNULL(s.revenue, 0.00) as revenue,
                   IFNULL(i.issued, 0) as issued,
                   IFNULL(i.nominal_value, 0.00) as issued_nominal
            FROM ticket_types tt
            LEFT JOIN (
                SELECT ticket_type_id, 
                       COUNT(*) as sold, 
                       COALESCE(SUM(price), 0.00) as revenue 
                FROM tickets 
                WHERE status IN ('Paid', 'Comped') AND (is_issued = 0 OR is_issued IS NULL)
                GROUP BY ticket_type_id
            ) s ON s.ticket_type_id = tt.id
            LEFT JOIN (
                SELECT ticket_type_id, 
                       COUNT(*) as issued, 
                       COALESCE(SUM(price), 0.00) as nominal_value 
                FROM tickets 
                WHERE status IN ('Paid', 'Comped') AND is_issued = 1
                GROUP BY ticket_type_id
            ) i ON i.ticket_type_id = tt.id
            WHERE tt.event_id = :event_id
        ");
        $stmt->execute(['event_id' => $eventId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Get staffing assistance request for an event, if any.
     */
    public function getEventStaffRequest(int|string $eventId): ?array
    {
        $db = Application::$app->db->pdo;
        $stmt = $db->prepare("
            SELECT * FROM event_staff_requests 
            WHERE event_id = :event_id 
            ORDER BY id DESC LIMIT 1
        ");
        $stmt->execute(['event_id' => $eventId]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        if ($row && !empty($row['roles_requested'])) {
            $row['roles_array'] = is_string($row['roles_requested']) ? json_decode($row['roles_requested'], true) : $row['roles_requested'];
        }
        return $row ?: null;
    }

    /**
     * Save or update staffing assistance request for an event.
     */
    public function saveEventStaffRequest(int|string $eventId, int|string $hostId, array $rolesRequested, string $notes = ''): bool
    {
        $db = Application::$app->db->pdo;
        $existing = $this->getEventStaffRequest($eventId);
        $json = json_encode($rolesRequested);

        if ($existing) {
            $stmt = $db->prepare("
                UPDATE event_staff_requests 
                SET roles_requested = :roles, notes = :notes, status = 'pending', updated_at = NOW()
                WHERE id = :id
            ");
            return $stmt->execute([
                'roles' => $json,
                'notes' => $notes,
                'id' => $existing['id']
            ]);
        } else {
            $stmt = $db->prepare("
                INSERT INTO event_staff_requests (event_id, host_id, roles_requested, notes, status)
                VALUES (:event_id, :host_id, :roles, :notes, 'pending')
            ");
            return $stmt->execute([
                'event_id' => $eventId,
                'host_id' => $hostId,
                'roles' => $json,
                'notes' => $notes
            ]);
        }
    }

    /**
     * Get active assigned staff members for an event.
     */
    public function getEventAssignedStaff(int|string $eventId): array
    {
        $db = Application::$app->db->pdo;
        $stmt = $db->prepare("
            SELECT esa.id as assignment_id, esa.assigned_at,
                   es.id as staff_id, es.name as staff_name, es.email as staff_email, es.status as staff_status,
                   r.id as role_id, r.name as role_name, r.slug as role_slug, r.description as role_description
            FROM event_staff_assignments esa
            INNER JOIN event_staff es ON esa.staff_id = es.id
            INNER JOIN roles r ON esa.role_id = r.id
            WHERE esa.event_id = :event_id
            ORDER BY esa.assigned_at DESC
        ");
        $stmt->execute(['event_id' => $eventId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
    }
}
