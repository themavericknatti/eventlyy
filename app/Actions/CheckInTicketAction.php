<?php

namespace App\Actions;

use Ace\Application;

class CheckInTicketAction
{
    /**
     * Check in a ticket.
     * Returns an array with outcome details.
     */
    public function execute(string $ticketCode, ?string $selectedEventId, int|string $hostId, bool $isAdmin = false): array
    {
        $db = Application::$app->db->pdo;
        $code = trim($ticketCode);
        $cleanCode = preg_replace('/^EV-/i', '', $code);
        $evCode = str_starts_with(strtoupper($code), 'EV-') ? $code : ('EV-' . $code);

        // Fetch ticket details with event and ticket type
        $stmt = $db->prepare("
            SELECT t.*, e.organizer_id, e.name as event_name, e.location as event_location, tt.name as ticket_type
            FROM tickets t
            INNER JOIN events e ON t.event_id = e.id
            INNER JOIN ticket_types tt ON t.ticket_type_id = tt.id
            WHERE t.id = :id OR t.id = :cleancode OR t.id = :evcode
            LIMIT 1
        ");
        $stmt->execute(['id' => $code, 'cleancode' => $cleanCode, 'evcode' => $evCode]);
        $ticket = $stmt->fetch();

        // 1. Check if ticket exists and belongs to organizer's event (or bypass for admin)
        if (!$ticket || (!$isAdmin && $ticket['organizer_id'] != $hostId)) {
            return [
                'status' => 'error',
                'flash_type' => 'error',
                'flash_message' => 'Invalid ticket code or access denied.',
                'result' => [
                    'status' => 'error',
                    'type' => 'invalid',
                    'message' => 'Ticket code ' . $ticketCode . ' is invalid or does not belong to your events.',
                    'code' => $ticketCode
                ]
            ];
        }

        // 2. Check if ticket belongs to the selected event
        if ($selectedEventId && $ticket['event_id'] != $selectedEventId) {
            return [
                'status' => 'error',
                'flash_type' => 'error',
                'flash_message' => 'Ticket is for a different event.',
                'result' => [
                    'status' => 'error',
                    'type' => 'invalid',
                    'message' => 'This ticket is registered for another event: ' . $ticket['event_name'],
                    'code' => $ticketCode
                ]
            ];
        }

        // 3. Check if ticket is refunded / cancelled
        if (in_array($ticket['status'], ['Cancelled', 'Refunded']) || ($ticket['status_detail'] ?? '') === 'Refunded') {
            return [
                'status' => 'error',
                'flash_type' => 'error',
                'flash_message' => '🚫 ENTRY DENIED: Ticket ' . $ticketCode . ' has been REFUNDED / CANCELLED and is invalid for entry.',
                'result' => [
                    'status' => 'error',
                    'type' => 'cancelled',
                    'message' => 'This ticket was refunded or cancelled and cannot be used for admission.',
                    'code' => $ticketCode,
                    'ticket' => $ticket
                ]
            ];
        }

        // 4. Check if ticket is flagged for review
        if (($ticket['status_detail'] ?? '') === 'Flagged for review') {
            return [
                'status' => 'warning',
                'flash_type' => 'warning',
                'flash_message' => '⚠️ FLAG ALERT: Ticket ' . $ticketCode . ' is FLAGGED FOR REVIEW! Direct attendee to Host / Support desk.',
                'result' => [
                    'status' => 'warning',
                    'type' => 'flagged',
                    'message' => '⚠️ FLAGGED TICKET: This ticket requires host review before admission. Attendee: ' . $ticket['attendee_name'],
                    'code' => $ticketCode,
                    'ticket' => $ticket
                ]
            ];
        }

        // 5. Check if already checked in
        if ($ticket['checked_in']) {
            return [
                'status' => 'error',
                'flash_type' => 'warning',
                'flash_message' => 'Ticket code ' . $ticketCode . ' is already checked in.',
                'result' => [
                    'status' => 'error',
                    'type' => 'duplicate',
                    'message' => 'This ticket was already checked in today at ' . date('h:i A', strtotime($ticket['checked_in_at'])),
                    'code' => $ticketCode,
                    'ticket' => $ticket
                ]
            ];
        }

        // 4. Perform check-in
        $now = date('Y-m-d H:i:s');
        $stmt = $db->prepare("UPDATE tickets SET checked_in = 1, checked_in_at = :now WHERE id = :id");
        $stmt->execute([
            'now' => $now,
            'id' => $ticketCode
        ]);

        $ticket['checked_in'] = 1;
        $ticket['checked_in_at'] = $now;

        return [
            'status' => 'success',
            'flash_type' => 'success',
            'flash_message' => 'Ticket ' . $ticketCode . ' checked in successfully!',
            'result' => [
                'status' => 'success',
                'type' => 'success',
                'message' => 'Verified successfully.',
                'code' => $ticketCode,
                'ticket' => $ticket
            ]
        ];
    }
}
