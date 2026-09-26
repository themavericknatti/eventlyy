<?php

/**
 * ProfileController
 *
 * This controller handles the profile page for host/organizer users.
 *
 * @package Evently
 * @subpackage Controllers/Profile
 * @author Celio Natti
 * @license MIT License
 * @version 1.0.0
 */

namespace App\Controllers;

use Ace\Application;
use Ace\Controller;
use Ace\Request;
use Ace\Middlewares\AuthMiddleware;
use App\Actions\UpdateProfileAction;
use App\Actions\ChangePasswordAction;
use App\Actions\DeleteAccountAction;

class ProfileController extends Controller
{
    protected UpdateProfileAction $updateProfileAction;
    protected ChangePasswordAction $changePasswordAction;
    protected DeleteAccountAction $deleteAccountAction;

    public function __construct()
    {
        // Allow any authenticated user to access their profile
        $this->registerMiddleware(new AuthMiddleware());
        $this->updateProfileAction = new UpdateProfileAction();
        $this->changePasswordAction = new ChangePasswordAction();
        $this->deleteAccountAction = new DeleteAccountAction();
    }

    /**
     * Render the profile page with real user data
     */
    public function profile(Request $request): string
    {
        $user = Application::$app->user;
        $db = Application::$app->db->pdo;

        // Determine if the user is a host (has events to manage)
        $isHost = method_exists($user, 'isHost') && $user->isHost();

        // ── Host Data ──
        $profile = [];
        $events = [];
        $totalEventsHosted = 0;
        $payoutConfigured = false;
        $supportedBanks = [];

        if ($isHost) {
            // 1. Fetch host profile
            $stmt = $db->prepare("SELECT * FROM host_profiles WHERE user_id = :user_id LIMIT 1");
            $stmt->execute(['user_id' => $user->id]);
            $profile = $stmt->fetch() ?: [];

            // 2. Fetch user's events with ticket stats
            $stmt = $db->prepare("
                SELECT 
                    e.*,
                    COALESCE(sold.count, 0) as tickets_sold,
                    COALESCE(cap.total, 0) as total_capacity
                FROM events e
                LEFT JOIN (
                    SELECT event_id, COUNT(*) as count 
                    FROM tickets 
                    WHERE status IN ('Paid', 'Comped') 
                    GROUP BY event_id
                ) sold ON sold.event_id = e.id
                LEFT JOIN (
                    SELECT event_id, SUM(capacity) as total 
                    FROM ticket_types 
                    GROUP BY event_id
                ) cap ON cap.event_id = e.id
                WHERE e.organizer_id = :user_id
                ORDER BY e.date DESC
            ");
            $stmt->execute(['user_id' => $user->id]);
            $events = $stmt->fetchAll() ?: [];

            // 3. Compute stats
            $totalEventsHosted = count($events);
            $payoutConfigured = !empty($profile['payout_bank']) && !empty($profile['payout_account']);

            $supportedBanks = \Ace\PaystackService::getSupportedBanks();
        }

        // ── Purchased Tickets (for all user roles, with Pagination) ──
        $purchasedTickets = [];
        $ticketPage = max(1, (int)($request->getBody()['ticket_page'] ?? ($_GET['ticket_page'] ?? 1)));
        $perPage = 5;
        
        $countStmt = $db->prepare("
            SELECT COUNT(*)
            FROM tickets t
            INNER JOIN events e ON t.event_id = e.id
            WHERE t.user_id = :user_id
              AND t.status IN ('Paid', 'Comped')
              AND e.date >= NOW() - INTERVAL 5 DAY
        ");
        $countStmt->execute(['user_id' => $user->id]);
        $totalTickets = (int)$countStmt->fetchColumn();

        $totalPages = max(1, (int)ceil($totalTickets / $perPage));
        if ($ticketPage > $totalPages) {
            $ticketPage = $totalPages;
        }
        $offset = ($ticketPage - 1) * $perPage;

        $stmt = $db->prepare("
            SELECT 
                t.id AS ticket_id,
                t.attendee_name,
                t.attendee_email,
                t.price,
                t.status AS ticket_status,
                t.purchase_date,
                t.checked_in,
                t.checked_in_at,
                e.id AS event_id,
                e.name AS event_name,
                e.date AS event_date,
                e.location AS event_location,
                e.image AS event_image,
                tt.name AS ticket_type
            FROM tickets t
            INNER JOIN events e ON t.event_id = e.id
            INNER JOIN ticket_types tt ON t.ticket_type_id = tt.id
            WHERE t.user_id = :user_id
              AND t.status IN ('Paid', 'Comped')
              AND e.date >= NOW() - INTERVAL 5 DAY
            ORDER BY e.date ASC, t.purchase_date DESC
            LIMIT :limit OFFSET :offset
        ");
        $stmt->bindValue(':user_id', $user->id, \PDO::PARAM_INT);
        $stmt->bindValue(':limit', $perPage, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();
        $purchasedTickets = $stmt->fetchAll() ?: [];

        $memberSince = !empty($user->created_at) ? date('M Y', strtotime($user->created_at)) : date('M Y');

        return $this->render('profile', [
            'title' => 'Evently - Your Profile and Account Settings.',
            'user' => $user,
            'profile' => $profile,
            'events' => $events,
            'total_events_hosted' => $totalEventsHosted,
            'payout_configured' => $payoutConfigured,
            'member_since' => $memberSince,
            'supportedBanks' => $supportedBanks,
            'isHost' => $isHost,
            'purchasedTickets' => $purchasedTickets,
            'ticketPage' => $ticketPage,
            'totalPages' => $totalPages,
            'totalTickets' => $totalTickets,
            'perPage' => $perPage,
        ]);
    }

    /**
     * Handle profile update form submission
     */
    public function updateProfile(Request $request)
    {
        $user = Application::$app->user;
        $data = $request->getBody();

        if ($this->updateProfileAction->execute($data, $user)) {
            Application::$app->session->setFlash('success', 'Profile updated successfully!');
        } else {
            Application::$app->session->setFlash('error', 'Failed to update profile. Please check your inputs.');
        }

        return Application::$app->response->redirect('/profile');
    }

    /**
     * Handle password change form submission
     */
    public function changePassword(Request $request)
    {
        $user = Application::$app->user;
        $data = $request->getBody();

        if ($this->changePasswordAction->execute($data, $user)) {
            Application::$app->session->setFlash('success', 'Password changed successfully!');
        } else {
            $error = $this->changePasswordAction->getError();
            Application::$app->session->setFlash('error', $error ?: 'Failed to change password.');
        }

        return Application::$app->response->redirect('/profile');
    }

    /**
     * Handle GDPR-compliant account deletion form submission
     */
    public function deleteAccount(Request $request)
    {
        $user = Application::$app->user;
        $data = $request->getBody();

        if ($this->deleteAccountAction->execute($data, $user)) {
            Application::$app->logout();
            Application::$app->session->setFlash('success', 'Your account and personal data have been permanently deleted per GDPR guidelines.');
            return Application::$app->response->redirect('/');
        }

        $error = $this->deleteAccountAction->getError();
        Application::$app->session->setFlash('error', $error ?: 'Failed to delete account.');
        return Application::$app->response->redirect('/profile');
    }

    /**
     * Handle attendee ticket transfer to another person
     */
    public function transferTicket(Request $request)
    {
        $user = Application::$app->user;
        $data = $request->getBody();

        $action = new \App\Actions\TransferTicketAction();
        $success = $action->execute($data, $user);

        $isAjax = $request->isAjax() || str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') || !empty($data['is_ajax']);

        if ($isAjax) {
            header('Content-Type: application/json');
            if ($success) {
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Ticket successfully transferred to ' . htmlspecialchars($data['recipient_name'] ?? 'recipient') . '! A new digital QR pass has been emailed to them.'
                ]);
            } else {
                http_response_code(422);
                echo json_encode([
                    'status' => 'error',
                    'message' => $action->getError() ?: 'Failed to transfer ticket. Please check the details.'
                ]);
            }
            exit;
        }

        if ($success) {
            Application::$app->session->setFlash('success', 'Ticket successfully transferred to ' . htmlspecialchars($data['recipient_name'] ?? 'recipient') . '! A new digital QR pass has been emailed to them.');
        } else {
            $error = $action->getError();
            Application::$app->session->setFlash('error', $error ?: 'Failed to transfer ticket. Please check your inputs.');
        }

        return Application::$app->response->redirect('/profile?tab=tickets');
    }
}