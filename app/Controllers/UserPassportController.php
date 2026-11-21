<?php

namespace App\Controllers;

use Ace\Application;
use Ace\Controller;
use Ace\Request;
use App\Middlewares\AuthMiddleware;
use App\Services\EventActivityService;
use App\Models\LayawayCredit;

class UserPassportController extends Controller
{
    protected EventActivityService $service;

    public function __construct()
    {
        $this->registerMiddleware(new AuthMiddleware(['index']));
        $this->service = new EventActivityService();
    }

    /**
     * Display the User's Gamified Event Passport, Badges, and Loyalty Credits.
     */
    public function index(Request $request): string
    {
        $user = Application::$app->user;
        $userId = (int)$user->id;

        // Fetch user badges
        $badges = $this->service->getUserBadges($userId);

        // Fetch user total active Event Credits
        $credits = LayawayCredit::findAvailableByEmail($user->email, $userId);
        $totalCredits = LayawayCredit::getTotalAvailable($user->email, $userId);

        // Fetch user ticket count for stats
        $db = Application::$app->db->pdo;
        $tStmt = $db->prepare("
            SELECT COUNT(*) FROM tickets 
            WHERE (user_id = :uid OR attendee_email = :email) AND status IN ('Paid', 'Comped')
        ");
        $tStmt->execute(['uid' => $userId, 'email' => $user->email]);
        $ticketCount = (int)$tStmt->fetchColumn();

        return $this->render('user/passport', [
            'title' => 'My Event Passport & Badges — ' . site_setting('site_title', 'Eventlyy'),
            'user' => $user,
            'badges' => $badges,
            'credits' => $credits,
            'totalCredits' => $totalCredits,
            'ticketCount' => $ticketCount
        ]);
    }
}
