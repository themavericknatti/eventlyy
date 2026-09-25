<?php

namespace App\Actions;

use App\Models\Event;
use App\Models\Ticket;
use Ace\Application;

class IssueTicketAction
{
    /**
     * Issue a ticket manually for an event.
     */
    public function execute(array $data, Event $event, Ticket $ticket): bool
    {
        $db = Application::$app->db->pdo;

        $ticketTypeInput = trim($data['ticket_type'] ?? '');

        // Find ticket type by ID (if numeric) or by Name
        if (is_numeric($ticketTypeInput) && (int)$ticketTypeInput > 0) {
            $stmt = $db->prepare("SELECT id, price FROM ticket_types WHERE event_id = :event_id AND id = :id LIMIT 1");
            $stmt->execute(['event_id' => $event->id, 'id' => (int)$ticketTypeInput]);
        } else {
            $stmt = $db->prepare("SELECT id, price FROM ticket_types WHERE event_id = :event_id AND name = :name LIMIT 1");
            $stmt->execute(['event_id' => $event->id, 'name' => $ticketTypeInput ?: 'General Admission']);
        }
        $tt = $stmt->fetch();

        // Fallback: if specific ticket type not found, pick first available ticket type for event
        if (!$tt) {
            $stmt = $db->prepare("SELECT id, price FROM ticket_types WHERE event_id = :event_id LIMIT 1");
            $stmt->execute(['event_id' => $event->id]);
            $tt = $stmt->fetch();
        }

        if (!$tt) {
            $ticket->addError('ticket_type_id', 'No ticket types exist for this event.');
            return false;
        }

        $ticketId = Ticket::generateCode();
        $paymentStatus = $data['payment_status'] ?? 'Paid';
        $isComped = ($paymentStatus === 'Comped');
        $price = $isComped ? 0.00 : (float)$tt['price'];
        $statusDetail = $isComped ? 'Free ticket issued by host' : 'Paid ticket issued by host';

        $ticketData = [
            'id' => $ticketId,
            'event_id' => $event->id,
            'ticket_type_id' => $tt['id'],
            'attendee_name' => trim($data['attendee_name'] ?? ''),
            'attendee_email' => trim($data['attendee_email'] ?? ''),
            'price' => $price,
            'status' => $paymentStatus,
            'status_detail' => $statusDetail,
            'checked_in' => 0,
            'is_issued' => 1
        ];

        $ticket->loadData($ticketData);

        $saved = $ticket->save();

        if ($saved && !empty($ticket->attendee_email) && filter_var($ticket->attendee_email, FILTER_VALIDATE_EMAIL)) {
            try {
                $ttName = $tt['name'] ?? 'General Admission';
                $eventDateStr = !empty($event->date) ? date('D, M d, Y • g:i A', strtotime($event->date)) : 'TBA';
                $methodLabel = $isComped ? 'Complimentary VIP Pass' : 'Direct Host Issue';

                \Ace\Mail::send(
                    $ticket->attendee_email,
                    'Your Admission Pass - ' . $event->name,
                    'emails/issued_ticket',
                    [
                        'attendee_name' => $ticket->attendee_name,
                        'event_name' => $event->name,
                        'event_date' => $eventDateStr,
                        'event_location' => $event->location ?? 'Venue',
                        'payment_method' => $methodLabel,
                        'total_amount' => (float)$ticket->price,
                        'is_issued' => true,
                        'header_title' => 'Official Admission Pass Issued!',
                        'header_subtitle' => 'Your event admission pass has been issued and is ready for entry',
                        'greeting_text' => 'Your official admission pass for <strong>' . e($event->name) . '</strong> has been issued by the event host via <strong>' . e(site_setting('site_title', 'Evently')) . '</strong>. Your scannable pass and details are below.',
                        'tickets' => [
                            [
                                'ticket_code' => $ticket->id,
                                'attendee_name' => $ticket->attendee_name,
                                'ticket_type' => $ttName,
                                'price' => (float)$ticket->price,
                                'status' => $ticket->status,
                                'checked_in' => false
                            ]
                        ]
                    ]
                );
            } catch (\Exception $e) {
                error_log("Failed to send issued ticket confirmation email: " . $e->getMessage());
            }
        }

        return $saved;
    }
}
