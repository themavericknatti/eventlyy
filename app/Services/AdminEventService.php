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
        $completionHours = event_completion_hours();
        $recurDedup = "(e.recurrence_group_id IS NULL OR e.recurrence_group_id = '' OR e.recurrence_sequence = 1)";

        $totalEvents = $db->query("SELECT COUNT(*) FROM events e WHERE {$recurDedup}")->fetchColumn();
        $totalOccurrences = $db->query("SELECT COUNT(*) FROM events")->fetchColumn();
        $activeEvents = $db->query("SELECT COUNT(*) FROM events e WHERE e.status = 'published' AND e.date >= NOW() - INTERVAL {$completionHours} HOUR AND {$recurDedup}")->fetchColumn();
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
            'total_occurrences' => (int)$totalOccurrences,
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
        $completionHours = event_completion_hours();

        $conditions = ["1=1"];
        $params = [];

        if (!empty($search)) {
            $conditions[] = "(e.name LIKE :search OR e.location LIKE :search OR u.email LIKE :search)";
            $params['search'] = "%$search%";
        }

        if ($status === 'active') {
            $conditions[] = "e.status = 'published' AND e.date >= NOW() - INTERVAL " . $completionHours . " HOUR";
        } elseif ($status === 'draft') {
            $conditions[] = "e.status = 'draft'";
        } elseif ($status === 'past') {
            $conditions[] = "e.status = 'completed' OR e.date < NOW() - INTERVAL " . $completionHours . " HOUR";
        }

        // Deduplicate recurring series to display only 1 representative occurrence
        $conditions[] = "(
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

        $whereClause = implode(' AND ', $conditions);

        $stmt = $db->prepare("
            SELECT e.*, u.email as organizer_email,
                   (CASE 
                       WHEN e.recurrence_group_id IS NOT NULL AND e.recurrence_group_id != '' THEN
                           (SELECT COUNT(*) FROM tickets t INNER JOIN events ev ON t.event_id = ev.id WHERE ev.recurrence_group_id = e.recurrence_group_id AND t.status IN ('Paid', 'Comped') AND (t.is_issued = 0 OR t.is_issued IS NULL))
                       ELSE
                           (SELECT COUNT(*) FROM tickets t WHERE t.event_id = e.id AND t.status IN ('Paid', 'Comped') AND (t.is_issued = 0 OR t.is_issued IS NULL))
                   END) as sold,
                   (CASE 
                       WHEN e.recurrence_group_id IS NOT NULL AND e.recurrence_group_id != '' THEN
                           (SELECT COUNT(*) FROM tickets t INNER JOIN events ev ON t.event_id = ev.id WHERE ev.recurrence_group_id = e.recurrence_group_id AND t.status IN ('Paid', 'Comped') AND t.is_issued = 1)
                       ELSE
                           (SELECT COUNT(*) FROM tickets t WHERE t.event_id = e.id AND t.status IN ('Paid', 'Comped') AND t.is_issued = 1)
                   END) as issued,
                   (SELECT SUM(capacity) FROM ticket_types tt WHERE tt.event_id = e.id) as capacity,
                   (CASE 
                       WHEN e.recurrence_group_id IS NOT NULL AND e.recurrence_group_id != '' THEN
                           COALESCE(
                               NULLIF((SELECT SUM(tr.total_amount) FROM transactions tr INNER JOIN events ev ON tr.event_id = ev.id WHERE ev.recurrence_group_id = e.recurrence_group_id AND tr.type = 'payment' AND tr.status = 'completed'), 0),
                               (SELECT SUM(t.price) FROM tickets t INNER JOIN events ev ON t.event_id = ev.id WHERE ev.recurrence_group_id = e.recurrence_group_id AND t.status IN ('Paid', 'Comped') AND (t.is_issued = 0 OR t.is_issued IS NULL)),
                               0
                           )
                       ELSE
                           COALESCE(
                               NULLIF((SELECT SUM(tr.total_amount) FROM transactions tr WHERE tr.event_id = e.id AND tr.type = 'payment' AND tr.status = 'completed'), 0),
                               (SELECT SUM(t.price) FROM tickets t WHERE t.event_id = e.id AND t.status IN ('Paid', 'Comped') AND (t.is_issued = 0 OR t.is_issued IS NULL)),
                               0
                           )
                   END) as gross_revenue,
                   (CASE 
                       WHEN e.recurrence_group_id IS NOT NULL AND e.recurrence_group_id != '' THEN
                           (SELECT COALESCE(SUM(t.price), 0) FROM tickets t INNER JOIN events ev ON t.event_id = ev.id WHERE ev.recurrence_group_id = e.recurrence_group_id AND t.status IN ('Paid', 'Comped') AND (t.is_issued = 0 OR t.is_issued IS NULL))
                       ELSE
                           (SELECT COALESCE(SUM(t.price), 0) FROM tickets t WHERE t.event_id = e.id AND t.status IN ('Paid', 'Comped') AND (t.is_issued = 0 OR t.is_issued IS NULL))
                   END) as ticket_sales_subtotal,
                   (CASE 
                       WHEN e.recurrence_group_id IS NOT NULL AND e.recurrence_group_id != '' THEN
                           (SELECT COALESCE(SUM(CASE WHEN ev.pass_fee = 1 THEN t.price ELSE t.price * 0.95 END), 0) FROM tickets t INNER JOIN events ev ON t.event_id = ev.id WHERE ev.recurrence_group_id = e.recurrence_group_id AND t.status IN ('Paid', 'Comped') AND (t.is_issued = 0 OR t.is_issued IS NULL))
                       ELSE
                           (SELECT COALESCE(SUM(CASE WHEN e.pass_fee = 1 THEN t.price ELSE t.price * 0.95 END), 0) FROM tickets t WHERE t.event_id = e.id AND t.status IN ('Paid', 'Comped') AND (t.is_issued = 0 OR t.is_issued IS NULL))
                   END) as host_earnings,
                   (CASE 
                       WHEN e.recurrence_group_id IS NOT NULL AND e.recurrence_group_id != '' THEN
                           (SELECT COALESCE(SUM(tr.fee_amount + tr.extra_charge_amount), 0) FROM transactions tr INNER JOIN events ev ON tr.event_id = ev.id WHERE ev.recurrence_group_id = e.recurrence_group_id AND tr.status = 'completed')
                       ELSE
                           (SELECT COALESCE(SUM(tr.fee_amount + tr.extra_charge_amount), 0) FROM transactions tr WHERE tr.event_id = e.id AND tr.status = 'completed')
                   END) as platform_profit
            FROM events e
            INNER JOIN users u ON e.organizer_id = u.id
            WHERE {$whereClause}
            ORDER BY e.created_at DESC
        ");
        $stmt->execute($params);
        $events = $stmt->fetchAll() ?: [];

        // Enrich recurring events with their full occurrences list for table dropdown & modal
        foreach ($events as &$ev) {
            $ev['series_occurrences'] = [];
            if (!empty($ev['recurrence_group_id'])) {
                $occStmt = $db->prepare("
                    SELECT id, name, recurrence_sequence, recurrence_count, date, status
                    FROM events
                    WHERE recurrence_group_id = :rg
                    ORDER BY recurrence_sequence ASC, date ASC
                ");
                $occStmt->execute(['rg' => $ev['recurrence_group_id']]);
                $ev['series_occurrences'] = $occStmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
            }
        }
        unset($ev);

        return $events;
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
        // Total online ticket purchases subtotal (cash collected by app from buyers)
        $ticketSubtotalStmt = $db->prepare("
            SELECT COALESCE(SUM(price), 0)
            FROM tickets
            WHERE event_id = :event_id AND status = 'Paid'
              AND (is_issued = 0 OR is_issued IS NULL)
        ");
        $ticketSubtotalStmt->execute(['event_id' => $id]);
        $ticketSalesSubtotal = (float)$ticketSubtotalStmt->fetchColumn();

        // Nominal value of issued / complimentary tickets (handled offline by host)
        $issuedNominalStmt = $db->prepare("
            SELECT COALESCE(SUM(price), 0)
            FROM tickets
            WHERE event_id = :event_id AND (is_issued = 1 OR status = 'Comped')
        ");
        $issuedNominalStmt->execute(['event_id' => $id]);
        $issuedNominalSubtotal = (float)$issuedNominalStmt->fetchColumn();

        // Host net earnings for this event (platform payout entitlement - online ticket purchases only)
        $hostEarningsStmt = $db->prepare("
            SELECT COALESCE(SUM(CASE WHEN e.pass_fee = 1 THEN t.price ELSE t.price * 0.95 END), 0)
            FROM tickets t
            INNER JOIN events e ON t.event_id = e.id
            WHERE t.event_id = :event_id AND t.status = 'Paid'
              AND (t.is_issued = 0 OR t.is_issued IS NULL)
        ");
        $hostEarningsStmt->execute(['event_id' => $id]);
        $hostEarnings = (float)$hostEarningsStmt->fetchColumn();

        // Platform profit directly calculated from completed payment transactions for this event
        $profitStmt = $db->prepare("
            SELECT COALESCE(SUM(fee_amount + extra_charge_amount), 0)
            FROM transactions
            WHERE event_id = :event_id AND type = 'payment' AND status = 'completed'
        ");
        $profitStmt->execute(['event_id' => $id]);
        $platformProfit = (float)$profitStmt->fetchColumn();

        if ($platformProfit == 0 && $ticketSalesSubtotal > 0) {
            $platformProfitStmt = $db->prepare("
                SELECT COALESCE(SUM(t.price * 0.05), 0)
                FROM tickets t
                WHERE t.event_id = :event_id AND t.status = 'Paid'
                  AND (t.is_issued = 0 OR t.is_issued IS NULL)
            ");
            $platformProfitStmt->execute(['event_id' => $id]);
            $platformProfit = (float)$platformProfitStmt->fetchColumn();
        }

        // Total gross transactions volume paid by buyers on platform
        $grossStmt = $db->prepare("
            SELECT COALESCE(SUM(total_amount), 0)
            FROM transactions
            WHERE event_id = :event_id AND type = 'payment' AND status = 'completed'
        ");
        $grossStmt->execute(['event_id' => $id]);
        $grossRevenue = (float)$grossStmt->fetchColumn();
        if ($grossRevenue == 0) {
            $grossRevenue = $hostEarnings + $platformProfit;
        }

        // Event payout tracking
        $disbursedStmt = $db->prepare("
            SELECT COALESCE(SUM(amount), 0)
            FROM payouts
            WHERE event_id = :event_id AND status IN ('processed', 'completed')
        ");
        $disbursedStmt->execute(['event_id' => $id]);
        $disbursedPayouts = (float)$disbursedStmt->fetchColumn();

        $earlyEscrowStmt = $db->prepare("
            SELECT COALESCE(SUM(amount), 0)
            FROM payouts
            WHERE event_id = :event_id AND type = 'escrow' AND status IN ('processed', 'completed')
        ");
        $earlyEscrowStmt->execute(['event_id' => $id]);
        $earlyEscrowDisbursed = (float)$earlyEscrowStmt->fetchColumn();

        $autoPayoutsDisbursedStmt = $db->prepare("
            SELECT COALESCE(SUM(amount), 0)
            FROM payouts
            WHERE event_id = :event_id AND type != 'escrow' AND status IN ('processed', 'completed')
        ");
        $autoPayoutsDisbursedStmt->execute(['event_id' => $id]);
        $autoPayoutsDisbursed = (float)$autoPayoutsDisbursedStmt->fetchColumn();

        $pendingPayoutStmt = $db->prepare("
            SELECT COALESCE(SUM(amount), 0)
            FROM payouts
            WHERE event_id = :event_id AND status = 'pending'
        ");
        $pendingPayoutStmt->execute(['event_id' => $id]);
        $pendingPayouts = (float)$pendingPayoutStmt->fetchColumn();

        $payoutsHistoryStmt = $db->prepare("
            SELECT p.*, hp.payout_bank, hp.payout_account
            FROM payouts p
            LEFT JOIN host_profiles hp ON p.organizer_id = hp.user_id
            WHERE p.event_id = :event_id
            ORDER BY p.created_at DESC
        ");
        $payoutsHistoryStmt->execute(['event_id' => $id]);
        $eventPayouts = $payoutsHistoryStmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];

        $eventTimestamp = strtotime($event['date']);
        $completionHours = event_completion_hours();
        $isEventPassed = ($eventTimestamp + ($completionHours * 3600)) < time();

        $remainingBalance = max(0.00, $hostEarnings - $disbursedPayouts);
        $uncommittedBalance = max(0.00, $hostEarnings - $disbursedPayouts - $pendingPayouts);
        $availableToDisburse = $isEventPassed ? $uncommittedBalance : 0.00;
        $escrowCustodyBalance = !$isEventPassed ? $uncommittedBalance : 0.00;

        // Fetch recurring series occurrences if this event is part of a series
        $recurringOccurrences = [];
        if (!empty($event['recurrence_group_id'])) {
            $occStmt = $db->prepare("
                SELECT id, name, date, recurrence_sequence, status
                FROM events
                WHERE recurrence_group_id = :rg
                ORDER BY recurrence_sequence ASC, date ASC
            ");
            $occStmt->execute(['rg' => $event['recurrence_group_id']]);
            $recurringOccurrences = $occStmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        }

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

        // Fetch host profile & payout readiness
        $hostBankName = '';
        $hostBankAccount = '';
        $hostPayoutReady = 'Not Set';
        if (!empty($event['organizer_id'])) {
            try {
                $hpStmt = $db->prepare("
                    SELECT hp.payout_bank, hp.payout_account, u.status as user_status
                    FROM host_profiles hp
                    LEFT JOIN users u ON hp.user_id = u.id
                    WHERE hp.user_id = :uid
                ");
                $hpStmt->execute(['uid' => $event['organizer_id']]);
                $hp = $hpStmt->fetch(\PDO::FETCH_ASSOC);
                if ($hp) {
                    $hostBankName = $hp['payout_bank'] ?? '';
                    $hostBankAccount = $hp['payout_account'] ?? '';
                    if (($hp['user_status'] ?? '') === 'suspended') {
                        $hostPayoutReady = 'Flagged / Frozen';
                    } elseif (!empty($hp['payout_bank']) && !empty($hp['payout_account'])) {
                        $hostPayoutReady = 'Ready to Disburse';
                    } else {
                        $hostPayoutReady = 'Incomplete Details';
                    }
                }
            } catch (\Exception $e) {
                $hostPayoutReady = 'Not Set';
            }
        }

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
            'early_escrow_disbursed' => $earlyEscrowDisbursed,
            'auto_payouts_disbursed' => $autoPayoutsDisbursed,
            'pending_payouts' => $pendingPayouts,
            'remaining_balance' => $remainingBalance,
            'available_to_disburse' => $availableToDisburse,
            'escrow_custody_balance' => $escrowCustodyBalance,
            'uncommitted_balance' => $uncommittedBalance,
            'is_event_passed' => $isEventPassed,
            'event_payouts' => $eventPayouts,
            'recurring_occurrences' => $recurringOccurrences,
            'attendees' => $attendees,
            'host_bank_name' => $hostBankName,
            'host_bank_account' => $hostBankAccount,
            'host_payout_ready' => $hostPayoutReady
        ];
    }
}
