<?php

namespace App\Actions;

use Ace\Application;
use Ace\Mail;

class SendFeedbackRequestAction
{
    public function execute(object $event): int
    {
        $db = Application::$app->db->pdo;

        // Fetch distinct attendees with paid/comped tickets for this event
        $stmt = $db->prepare("
            SELECT DISTINCT attendee_name, attendee_email 
            FROM tickets 
            WHERE event_id = :event_id AND status IN ('Paid', 'Comped')
        ");
        $stmt->execute(['event_id' => $event->id]);
        $attendees = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        if (empty($attendees)) {
            return 0;
        }

        $sentCount = 0;
        $feedbackUrl = url('/event/' . ($event->slug ?? $event->id) . '/feedback');

        foreach ($attendees as $att) {
            try {
                Mail::send(
                    $att['attendee_email'],
                    'Rate Your Experience: ' . $event->name,
                    'emails/feedback_request',
                    [
                        'attendee_name' => $att['attendee_name'],
                        'event_name' => $event->name,
                        'event_date' => date('D, M d, Y', strtotime($event->date)),
                        'feedback_url' => $feedbackUrl
                    ]
                );
                $sentCount++;
            } catch (\Exception $e) {
                error_log("Feedback request email failed for {$att['attendee_email']}: " . $e->getMessage());
            }
        }

        return $sentCount;
    }
}
