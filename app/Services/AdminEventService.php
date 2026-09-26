<?php

namespace App\Services;

use Ace\Application;
use App\Models\Event;

class AdminEventService
{
    /**
     * Get statistics for all events in the system.
     */
    public function getEventStats(): array
    {
        $db = Application::$app->db->pdo;

        $totalEvents = $db->query("SELECT COUNT(*) FROM events")->fetchColumn();
        $activeEvents = $db->query("SELECT COUNT(*) FROM events WHERE status = 'published' AND date >= NOW() - INTERVAL " . event_completion_hours() . " HOUR")->fetchColumn();
        $ticketsSold = $db->query("SELECT COUNT(*) FROM tickets WHERE status IN ('Paid', 'Comped') AND (is_issued = 0 OR is_issued IS NULL)")->fetchColumn();
        $ticketsIssued = $db->query("SELECT COUNT(*) FROM tickets WHERE status IN ('Paid', 'Comped') AND is_issued = 1")->fetchColumn();
        $totalGross = (float)$db->query("
            SELECT COALESCE(SUM(total_amount), 0) FROM transactions WHERE type = 'payment' AND status = 'completed'
        ")->fetchColumn();

        if ($totalGross == 0) {
            $totalGross = (float)$db->query("
                SELECT COALESCE(SUM(price), 0) FROM tickets WHERE status IN ('Paid', 'Comped') AND (is_issued = 0 OR is_issued IS NULL)
            ")->fetchColumn();
        }

        // Platform profit directly calculated from completed transactions table
        $platformProfit = $db->query("SELECT COALESCE(SUM(fee_amount + extra_charge_amount), 0) FROM transactions WHERE status = 'completed'")->fetchColumn() ?: 0.00;

        return [
            'total_events' => (int)$totalEvents,
            'active_events' => (int)$activeEvents,
            'tickets_sold' => (int)$ticketsSold,
            'tickets_issued' => (int)$ticketsIssued,
            'total_gross' => (float)$totalGross,
            'total_platform_profit' => (float)$platformProfit
        ];
    }

    /**
     * Get a filtered list of events.
     */
    public function getFilteredEvents(string $search = '', string $status = ''): array
    {
        $db = Application::$app->db->pdo;

        $conditions = ["1=1"];
        $params = [];

        if (!empty($search)) {
            $conditions[] = "(e.name LIKE :search OR e.location LIKE :search OR u.email LIKE :search)";
            $params['search'] = "%$search%";
        }

        if ($status === 'active') {
            $conditions[] = "e.status = 'published' AND e.date >= NOW() - INTERVAL " . event_completion_hours() . " HOUR";
        } elseif ($status === 'draft') {
            $conditions[] = "e.status = 'draft'";
        } elseif ($status === 'past') {
            $conditions[] = "e.status = 'completed' OR e.date < NOW() - INTERVAL " . event_completion_hours() . " HOUR";
        }

        $whereClause = implode(' AND ', $conditions);

        $stmt = $db->prepare("
            SELECT e.*, u.email as organizer_email,
                   (SELECT COUNT(*) FROM tickets t WHERE t.event_id = e.id AND t.status IN ('Paid', 'Comped') AND (t.is_issued = 0 OR t.is_issued IS NULL)) as sold,
                   (SELECT COUNT(*) FROM tickets t WHERE t.event_id = e.id AND t.status IN ('Paid', 'Comped') AND t.is_issued = 1) as issued,
                   (SELECT SUM(capacity) FROM ticket_types tt WHERE tt.event_id = e.id) as capacity,
                   COALESCE(
                       NULLIF((SELECT SUM(tr.total_amount) FROM transactions tr WHERE tr.event_id = e.id AND tr.type = 'payment' AND tr.status = 'completed'), 0),
                       (SELECT SUM(t.price) FROM tickets t WHERE t.event_id = e.id AND t.status IN ('Paid', 'Comped') AND (t.is_issued = 0 OR t.is_issued IS NULL)),
                       0
                   ) as gross_revenue,
                   (SELECT COALESCE(SUM(t.price), 0) FROM tickets t WHERE t.event_id = e.id AND t.status IN ('Paid', 'Comped') AND (t.is_issued = 0 OR t.is_issued IS NULL)) as ticket_sales_subtotal,
                   (SELECT COALESCE(SUM(CASE WHEN e.pass_fee = 1 THEN t.price ELSE t.price * 0.95 END), 0) FROM tickets t WHERE t.event_id = e.id AND t.status IN ('Paid', 'Comped') AND (t.is_issued = 0 OR t.is_issued IS NULL)) as host_earnings,
                   (SELECT COALESCE(SUM(tr.fee_amount + tr.extra_charge_amount), 0) FROM transactions tr WHERE tr.event_id = e.id AND tr.status = 'completed') as platform_profit
            FROM events e
            INNER JOIN users u ON e.organizer_id = u.id
            WHERE {$whereClause}
            ORDER BY e.created_at DESC
        ");
        $stmt->execute($params);
        return $stmt->fetchAll() ?: [];
    }

    /**
     * Get full details for a specific event including ticket sales and host earnings.
     */
    public function getEventDetails(int|string $id): ?array
    {
        $db = Application::$app->db->pdo;

        // Fetch event with organizer name & email
        $stmt = $db->prepare("
            SELECT e.*, u.name as organizer_name, u.email as organizer_email
            FROM events e
            INNER JOIN users u ON e.organizer_id = u.id
            WHERE e.id = :id
        ");
        $stmt->execute(['id' => $id]);
        $event = $stmt->fetch();

        if (!$event) {
            return null;
        }

        // Fetch ticket types breakdown with sales & exact revenue per tier
        $stmt = $db->prepare("
            SELECT tt.*, 
                   (SELECT COUNT(*) FROM tickets t WHERE t.ticket_type_id = tt.id AND t.status IN ('Paid', 'Comped') AND (t.is_issued = 0 OR t.is_issued IS NULL)) as sold,
                   (SELECT COUNT(*) FROM tickets t WHERE t.ticket_type_id = tt.id AND t.status IN ('Paid', 'Comped') AND t.is_issued = 1) as issued,
                   (SELECT COALESCE(SUM(t.price), 0) FROM tickets t WHERE t.ticket_type_id = tt.id AND t.status IN ('Paid', 'Comped') AND (t.is_issued = 0 OR t.is_issued IS NULL)) as tier_revenue
            FROM ticket_types tt
            WHERE tt.event_id = :event_id
        ");
        $stmt->execute(['event_id' => $id]);
        $ticketTypesRaw = $stmt->fetchAll() ?: [];

        $ticketsSold = 0;
        $ticketsIssued = 0;
        $totalCapacity = 0;
        $ticketTypes = [];

        foreach ($ticketTypesRaw as $tt) {
            $sold = (int)$tt['sold'];
            $issued = (int)($tt['issued'] ?? 0);
            $cap = (int)$tt['capacity'];
            $tierRevenue = (float)($tt['tier_revenue'] ?? ($sold * $tt['price']));
            $pct = $cap > 0 ? min(100, round((($sold + $issued) / $cap) * 100)) : 0;

            $ticketsSold += $sold;
            $ticketsIssued += $issued;
            $totalCapacity += $cap;

            $tt['sold'] = $sold;
            $tt['issued'] = $issued;
            $tt['capacity'] = $cap;
            $tt['revenue'] = $tierRevenue;
            $tt['percentage'] = $pct;
            $ticketTypes[] = $tt;
        }

        // Exact online ticket sales face value
        $ticketSubtotalStmt = $db->prepare("
            SELECT COALESCE(SUM(price), 0)
            FROM tickets
            WHERE event_id = :event_id AND status IN ('Paid', 'Comped')
              AND (is_issued = 0 OR is_issued IS NULL)
        ");
        $ticketSubtotalStmt->execute(['event_id' => $id]);
        $ticketSalesSubtotal = (float)$ticketSubtotalStmt->fetchColumn();

        // Nominal value of issued / complimentary tickets (handled offline by host)
        $issuedNominalStmt = $db->prepare("
            SELECT COALESCE(SUM(price), 0)
            FROM tickets
            WHERE event_id = :event_id AND status IN ('Paid', 'Comped')
              AND is_issued = 1
        ");
        $issuedNominalStmt->execute(['event_id' => $id]);
        $issuedNominalSubtotal = (float)$issuedNominalStmt->fetchColumn();

        // Total gross transactions volume paid by buyers on platform
        $grossStmt = $db->prepare("
            SELECT COALESCE(SUM(total_amount), 0)
            FROM transactions
            WHERE event_id = :event_id AND type = 'payment' AND status = 'completed'
        ");
        $grossStmt->execute(['event_id' => $id]);
        $grossRevenue = (float)$grossStmt->fetchColumn();
        if ($grossRevenue == 0) {
            $grossRevenue = $ticketSalesSubtotal;
        }

        // Host net earnings for this event (platform payout share - online ticket purchases only)
        $hostEarningsStmt = $db->prepare("
            SELECT COALESCE(SUM(CASE WHEN e.pass_fee = 1 THEN t.price ELSE t.price * 0.95 END), 0)
            FROM tickets t
            INNER JOIN events e ON t.event_id = e.id
            WHERE t.event_id = :event_id AND t.status IN ('Paid', 'Comped')
              AND (t.is_issued = 0 OR t.is_issued IS NULL)
        ");
        $hostEarningsStmt->execute(['event_id' => $id]);
        $hostEarnings = (float)$hostEarningsStmt->fetchColumn();

        // Platform profit directly calculated from completed transactions for this event
        $profitStmt = $db->prepare("
            SELECT COALESCE(SUM(fee_amount + extra_charge_amount), 0)
            FROM transactions
            WHERE event_id = :event_id AND status = 'completed'
        ");
        $profitStmt->execute(['event_id' => $id]);
        $platformProfit = (float)$profitStmt->fetchColumn();

        if ($platformProfit == 0 && $grossRevenue > $hostEarnings) {
            $platformProfit = max(0.00, $grossRevenue - $hostEarnings);
        }

        // Event payout tracking
        $disbursedStmt = $db->prepare("
            SELECT COALESCE(SUM(amount), 0)
            FROM payouts
            WHERE event_id = :event_id AND status IN ('processed', 'completed')
        ");
        $disbursedStmt->execute(['event_id' => $id]);
        $disbursedPayouts = (float)$disbursedStmt->fetchColumn();

        $pendingPayoutStmt = $db->prepare("
            SELECT COALESCE(SUM(amount), 0)
            FROM payouts
            WHERE event_id = :event_id AND status IN ('pending', 'failed')
        ");
        $pendingPayoutStmt->execute(['event_id' => $id]);
        $pendingPayouts = (float)$pendingPayoutStmt->fetchColumn();

        $remainingBalance = max(0.00, $hostEarnings - $disbursedPayouts);

        // Fetch attendees for this event
        $attendeeStmt = $db->prepare("
            SELECT t.id as ticket_id, t.attendee_name, t.attendee_email, t.user_id, t.price,
                   t.status as ticket_status, t.checked_in, t.purchase_date, t.is_issued,
                   tt.name as ticket_type_name,
                   CASE 
                       WHEN t.is_issued = 1 THEN 'Issued / Comp Ticket'
                       WHEN t.user_id IS NOT NULL THEN 'Registered User' 
                       ELSE 'Guest Attendee' 
                   END as attendee_type
            FROM tickets t
            LEFT JOIN ticket_types tt ON t.ticket_type_id = tt.id
            WHERE t.event_id = :event_id AND t.status IN ('Paid', 'Comped')
            ORDER BY t.purchase_date DESC
        ");
        $attendeeStmt->execute(['event_id' => $id]);
        $attendees = $attendeeStmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];

        return [
            'event' => $event,
            'ticketTypes' => $ticketTypes,
            'tickets_sold' => $ticketsSold,
            'tickets_issued' => $ticketsIssued,
            'issued_nominal' => $issuedNominalSubtotal,
            'total_capacity' => $totalCapacity,
            'total_revenue' => $grossRevenue,
            'gross_revenue' => $grossRevenue,
            'host_earnings' => $hostEarnings,
            'platform_profit' => $platformProfit,
            'disbursed_payouts' => $disbursedPayouts,
            'pending_payouts' => $pendingPayouts,
            'remaining_balance' => $remainingBalance,
            'attendees' => $attendees
        ];
    }
}
