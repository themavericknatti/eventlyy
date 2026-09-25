<?php

namespace App\Actions;

use Ace\Application;
use App\Models\EventReview;

class SubmitEventReviewAction
{
    public function execute(array $data, object $event, EventReview $reviewModel): bool
    {
        $db = Application::$app->db->pdo;

        $reviewModel->loadData($data);
        $reviewModel->event_id = $event->id;
        $reviewModel->organizer_id = $event->organizer_id;

        if (!$reviewModel->validate()) {
            return false;
        }

        $rating = (int)$reviewModel->rating;
        if ($rating < 1 || $rating > 5) {
            $reviewModel->addError('rating', 'Rating must be between 1 and 5 stars.');
            return false;
        }

        // Check if ticket exists for this attendee email and event to ensure valid ticket holder
        $stmt = $db->prepare("
            SELECT id FROM tickets 
            WHERE event_id = :event_id AND attendee_email = :email AND status IN ('Paid', 'Comped')
            LIMIT 1
        ");
        $stmt->execute([
            'event_id' => $event->id,
            'email' => $reviewModel->attendee_email
        ]);
        $ticket = $stmt->fetch();

        if (!$ticket) {
            $reviewModel->addError('attendee_email', 'No ticket purchase found under this email address for this event.');
            return false;
        }

        $reviewModel->ticket_id = $ticket['id'];

        // Check for existing review by this email for this event
        $stmt = $db->prepare("
            SELECT id FROM event_reviews 
            WHERE event_id = :event_id AND attendee_email = :email
            LIMIT 1
        ");
        $stmt->execute([
            'event_id' => $event->id,
            'email' => $reviewModel->attendee_email
        ]);
        if ($stmt->fetch()) {
            $reviewModel->addError('attendee_email', 'You have already submitted a review for this event.');
            return false;
        }

        // Insert review record into event_reviews
        $stmt = $db->prepare("
            INSERT INTO event_reviews (event_id, organizer_id, ticket_id, attendee_name, attendee_email, rating, comment, created_at)
            VALUES (:event_id, :organizer_id, :ticket_id, :name, :email, :rating, :comment, NOW())
        ");

        $success = $stmt->execute([
            'event_id' => $event->id,
            'organizer_id' => $event->organizer_id,
            'ticket_id' => $reviewModel->ticket_id,
            'name' => $reviewModel->attendee_name,
            'email' => $reviewModel->attendee_email,
            'rating' => $rating,
            'comment' => $reviewModel->comment ?? ''
        ]);

        if ($success) {
            // Recalculate average host rating in host_profiles
            $stmt = $db->prepare("
                UPDATE host_profiles 
                SET rating = (
                    SELECT ROUND(AVG(rating), 2) 
                    FROM event_reviews 
                    WHERE organizer_id = :org_id_sub
                )
                WHERE user_id = :org_id_where
            ");
            $stmt->execute([
                'org_id_sub' => $event->organizer_id,
                'org_id_where' => $event->organizer_id
            ]);
        }

        return $success;
    }
}
