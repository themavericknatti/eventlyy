<?php

namespace App\Actions;

use Ace\Application;
use Ace\Mail;
use App\Models\Ticket;
use App\Models\User;

class TransferTicketAction
{
    protected string $error = '';

    public function getError(): string
    {
        return $this->error;
    }

    /**
     * Execute ticket transfer to a new attendee.
     *
     * @param array $data Input data containing ticket_id, recipient_name, recipient_email, transfer_note
     * @param User $sender The authenticated user performing the transfer
     * @return bool True if successful, false otherwise
     */
    public function execute(array $data, User $sender): bool
    {
        $db = Application::$app->db->pdo;

        $ticketCode = trim((string)($data['ticket_id'] ?? ''));
        $recipientName = trim((string)($data['recipient_name'] ?? ''));
        $recipientEmail = strtolower(trim((string)($data['recipient_email'] ?? '')));
        $transferNote = trim((string)($data['transfer_note'] ?? ''));

        // 1. Basic input validation
        if (empty($ticketCode)) {
            $this->error = 'Ticket code is required.';
            return false;
        }

        if (empty($recipientName)) {
            $this->error = 'Recipient full name is required.';
            return false;
        }

        if (empty($recipientEmail) || !filter_var($recipientEmail, FILTER_VALIDATE_EMAIL)) {
            $this->error = 'A valid recipient email address is required.';
            return false;
        }

        if ($recipientEmail === strtolower(trim((string)$sender->email))) {
            $this->error = 'You cannot transfer a ticket to your own email address.';
            return false;
        }

        // 2. Fetch ticket with event and tier details
        $stmt = $db->prepare("
            SELECT t.*, 
                   e.id as event_id,
                   e.name as event_name, 
                   e.date as event_date, 
                   e.location as event_location, 
                   e.image as event_image,
                   e.slug as event_slug,
                   e.custom_url as event_custom_url,
                   tt.name as ticket_type_name
            FROM tickets t
            INNER JOIN events e ON t.event_id = e.id
            INNER JOIN ticket_types tt ON t.ticket_type_id = tt.id
            WHERE t.id = :ticket_id
            LIMIT 1
        ");
        $stmt->execute(['ticket_id' => $ticketCode]);
        $ticket = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$ticket) {
            $this->error = 'Ticket not found or invalid.';
            return false;
        }

        // 3. Ownership verification: sender must own the ticket
        $isOwner = ((int)$ticket['user_id'] === (int)$sender->id) 
            || (strtolower(trim((string)$ticket['attendee_email'])) === strtolower(trim((string)$sender->email)));

        if (!$isOwner) {
            $this->error = 'You do not have permission to transfer this ticket.';
            return false;
        }

        // 4. Status checks: cannot transfer cancelled or refunded tickets
        if (in_array($ticket['status'], ['Cancelled', 'Refunded']) || ($ticket['status_detail'] ?? '') === 'Refunded') {
            $this->error = 'This ticket has been cancelled or refunded and cannot be transferred.';
            return false;
        }

        // 5. Gate check: cannot transfer if already checked in
        if (!empty($ticket['checked_in'])) {
            $this->error = 'This ticket has already been checked in at the event gate and cannot be transferred.';
            return false;
        }

        // 6. Event timing check: cannot transfer for past events
        $eventTimestamp = strtotime($ticket['event_date']);
        if ($eventTimestamp && $eventTimestamp < time()) {
            $this->error = 'Cannot transfer a ticket for an event that has already concluded.';
            return false;
        }

        // 7. Atomic transfer execution inside a DB transaction
        $oldTicketCode = $ticket['id'];
        $newTicketCode = Ticket::generateCode();

        try {
            $db->beginTransaction();

            // Check if recipient already has an account on Eventlyy
            $recipStmt = $db->prepare("SELECT id, name, email FROM users WHERE LOWER(email) = :email LIMIT 1");
            $recipStmt->execute(['email' => $recipientEmail]);
            $recipientUser = $recipStmt->fetch(\PDO::FETCH_ASSOC);
            $recipientUserId = $recipientUser ? (int)$recipientUser['id'] : null;

            // Re-assign ticket with new code and recipient information
            $updateStmt = $db->prepare("
                UPDATE tickets 
                SET id = :new_code,
                    user_id = :user_id,
                    attendee_name = :attendee_name,
                    attendee_email = :attendee_email,
                    status_detail = :status_detail
                WHERE id = :old_code
            ");
            $updateStmt->execute([
                'new_code' => $newTicketCode,
                'user_id' => $recipientUserId,
                'attendee_name' => $recipientName,
                'attendee_email' => $recipientEmail,
                'status_detail' => 'Transferred from ' . $sender->name,
                'old_code' => $oldTicketCode
            ]);

            // Save transfer audit record
            $auditStmt = $db->prepare("
                INSERT INTO ticket_transfers (
                    ticket_id, previous_ticket_id, event_id,
                    sender_user_id, sender_name, sender_email,
                    recipient_user_id, recipient_name, recipient_email,
                    transfer_note, created_at
                ) VALUES (
                    :ticket_id, :previous_ticket_id, :event_id,
                    :sender_user_id, :sender_name, :sender_email,
                    :recipient_user_id, :recipient_name, :recipient_email,
                    :transfer_note, NOW()
                )
            ");
            $auditStmt->execute([
                'ticket_id' => $newTicketCode,
                'previous_ticket_id' => $oldTicketCode,
                'event_id' => $ticket['event_id'],
                'sender_user_id' => $sender->id,
                'sender_name' => $sender->name,
                'sender_email' => $sender->email,
                'recipient_user_id' => $recipientUserId,
                'recipient_name' => $recipientName,
                'recipient_email' => $recipientEmail,
                'transfer_note' => !empty($transferNote) ? $transferNote : null
            ]);

            $db->commit();
        } catch (\Exception $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            error_log("Ticket transfer transaction failed: " . $e->getMessage());
            $this->error = 'Failed to process ticket transfer. Please try again.';
            return false;
        }

        // 8. Dispatch notification emails (gracefully catches SMTP errors so transfer is not undone)
        $siteTitle = site_setting('site_title', 'Eventlyy');

        // Email to Recipient with new QR code
        try {
            Mail::send(
                $recipientEmail,
                "🎟️ You've received a ticket for " . $ticket['event_name'] . " from " . $sender->name,
                'emails/ticket_transferred_recipient',
                [
                    'recipientName' => $recipientName,
                    'senderName' => $sender->name,
                    'senderEmail' => $sender->email,
                    'event' => $ticket,
                    'ticketCode' => $newTicketCode,
                    'ticketType' => $ticket['ticket_type_name'],
                    'transferNote' => $transferNote,
                    'qrUrl' => ticket_qr_url($newTicketCode, 260)
                ]
            );
        } catch (\Exception $e) {
            error_log("Failed to send recipient transfer email: " . $e->getMessage());
        }

        // Email to Sender confirming transfer and deactivation of old pass
        try {
            Mail::send(
                $sender->email,
                "✓ Ticket Transfer Confirmed — " . $ticket['event_name'],
                'emails/ticket_transferred_sender',
                [
                    'senderName' => $sender->name,
                    'recipientName' => $recipientName,
                    'recipientEmail' => $recipientEmail,
                    'event' => $ticket,
                    'oldTicketCode' => $oldTicketCode,
                    'ticketType' => $ticket['ticket_type_name']
                ]
            );
        } catch (\Exception $e) {
            error_log("Failed to send sender transfer email: " . $e->getMessage());
        }

        return true;
    }
}
