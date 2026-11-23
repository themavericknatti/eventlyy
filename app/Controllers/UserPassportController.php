<?php

namespace App\Controllers;

use Ace\Application;
use Ace\Controller;
use Ace\Request;
use Ace\Middlewares\AuthMiddleware;
use App\Services\EventActivityService;
use App\Models\LayawayCredit;

class UserPassportController extends Controller
{
    protected EventActivityService $service;

    public function __construct()
    {
        $this->registerMiddleware(new AuthMiddleware(['index', 'prestige']));
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

        // Retroactively award ticket badges based on harder thresholds:
        // First Drop Pioneer requires attending/claiming at least 2 tickets
        // Nightlife Explorer requires attending at least 5 events
        // Festival Veteran requires attending at least 10 events
        if ($ticketCount >= 2) {
            $this->service->checkAndAwardBadge($userId, 'first_drop');
        }
        if ($ticketCount >= 5) {
            $this->service->checkAndAwardBadge($userId, 'nightlife_explorer');
        }
        if ($ticketCount >= 10) {
            $this->service->checkAndAwardBadge($userId, 'festival_legend');
        }

        // Re-fetch badges if newly unlocked
        $badges = $this->service->getUserBadges($userId);

        // Fetch full gamified progression (XP, Level, Rank, Next Threshold, Prestige Tier)
        $progression = $this->service->getUserPassportProgress($userId);

        return $this->render('user/passport', [
            'title' => 'My Event Passport & Badges — ' . site_setting('site_title', 'Eventlyy'),
            'user' => $user,
            'badges' => $badges,
            'credits' => $credits,
            'totalCredits' => $totalCredits,
            'ticketCount' => $ticketCount,
            'progression' => $progression
        ]);
    }

    /**
     * POST: Activate Prestige Rebirth when Level 6 Living Legend is conquered.
     */
    public function prestige(Request $request): void
    {
        $user = Application::$app->user;
        $userId = (int)$user->id;

        $res = $this->service->activatePrestige($userId);
        if ($res['success']) {
            Application::$app->session->setFlash('success', $res['message']);
        } else {
            Application::$app->session->setFlash('error', $res['message']);
        }

        redirect('/user/passport');
    }
}
