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

        // 1. Fetch user genuine purchased ticket count (excluding comped/issued)
        $db = Application::$app->db->pdo;
        $tStmt = $db->prepare("
            SELECT COUNT(*) FROM tickets 
            WHERE (user_id = :uid OR LOWER(attendee_email) = :email) 
              AND status = 'Paid' 
              AND (is_issued = 0 OR is_issued IS NULL)
        ");
        $tStmt->execute(['uid' => $userId, 'email' => strtolower(trim($user->email))]);
        $ticketCount = (int)$tStmt->fetchColumn();

        // 2. Strict Attendance Badge Evaluation:
        // Requires genuinely purchased tickets, physical gate check-in (checked_in = 1),
        // excludes virtual/online live streams, and requires distinct events attended (>=3, >=6, >=12).
        $attendanceStats = $this->service->evaluateAttendanceBadges($userId);
        $attendedCount = $attendanceStats['attended_count'] ?? 0;

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
            'attendedCount' => $attendedCount,
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
