<?php

namespace App\Services;

use Ace\Application;

class TicketService
{
    /**
     * Fetch ticket detail for host/admin check.
     */
    public function getTicketDetail(string $ticketId, int|string $hostId, bool $isAdmin = false): ?array
    {
        $db = Application::$app->db->pdo;
        $stmt = $db->prepare("
            SELECT t.*, tt.name as ticket_type, e.name as event_name, e.date as event_date, e.organizer_id
            FROM tickets t
            INNER JOIN ticket_types tt ON t.ticket_type_id = tt.id
            INNER JOIN events e ON t.event_id = e.id
            WHERE t.id = :id
        ");
        $stmt->execute(['id' => $ticketId]);
        $ticket = $stmt->fetch();
        if (!$ticket || (!$isAdmin && $ticket['organizer_id'] != $hostId)) {
            return null;
        }
        return $ticket;
    }

    /**
     * Get details of host events to display on ticket verification page dropdown (or all events if admin).
     */
    public function getHostEventsForSelector(int|string $hostId, bool $isAdmin = false): array
    {
        $db = Application::$app->db->pdo;
        if ($isAdmin) {
            $stmt = $db->query("
                SELECT e.id, e.name, e.organizer_id, u.name as organizer_name 
                FROM events e 
                INNER JOIN users u ON e.organizer_id = u.id 
                ORDER BY e.date DESC
            ");
            return $stmt->fetchAll() ?: [];
        }
        $stmt = $db->prepare("SELECT id, name, organizer_id FROM events WHERE organizer_id = :organizer_id ORDER BY date DESC");
        $stmt->execute(['organizer_id' => $hostId]);
        return $stmt->fetchAll() ?: [];
    }

    /**
     * Get checked-in tickets list and capacity/check-in statistics.
     */
    public function getVerificationData(int|string $eventId): array
    {
        $db = Application::$app->db->pdo;

        // Checked-in tickets list
        $stmt = $db->prepare("
            SELECT t.*, tt.name as ticket_type
            FROM tickets t
            INNER JOIN ticket_types tt ON t.ticket_type_id = tt.id
            WHERE t.event_id = :event_id AND t.checked_in = 1
            ORDER BY t.checked_in_at DESC
        ");
        $stmt->execute(['event_id' => $eventId]);
        $verifiedTickets = $stmt->fetchAll() ?: [];

        // Total capacity of all ticket types for this event
        $stmt = $db->prepare("SELECT SUM(capacity) FROM ticket_types WHERE event_id = :event_id");
        $stmt->execute(['event_id' => $eventId]);
        $totalTicketsCount = (int)$stmt->fetchColumn();

        // Total tickets sold
        $stmt = $db->prepare("SELECT COUNT(*) FROM tickets WHERE event_id = :event_id");
        $stmt->execute(['event_id' => $eventId]);
        $soldTicketsCount = (int)$stmt->fetchColumn();

        $verifiedTicketsCount = count($verifiedTickets);
        $remainingTicketsCount = max(0, $soldTicketsCount - $verifiedTicketsCount);

        return [
            'verified_tickets' => $verifiedTickets,
            'total_tickets_count' => $totalTicketsCount,
            'verified_tickets_count' => $verifiedTicketsCount,
            'remaining_tickets_count' => $remainingTicketsCount
        ];
    }
}
