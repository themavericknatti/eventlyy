<?php

namespace App\Controllers;

use Ace\Application;
use Ace\Controller;
use Ace\Request;
use App\Services\EventActivityService;
use PDO;

class EventActivityController extends Controller
{
    protected EventActivityService $service;

    public function __construct()
    {
        $this->service = new EventActivityService();
    }

    /**
     * Resolve event by numeric ID, custom URL, or slug.
     */
    protected function getEvent(int|string $id): ?array
    {
        $db = Application::$app->db->pdo;
        if (is_numeric($id)) {
            $stmt = $db->prepare("SELECT * FROM events WHERE id = :id LIMIT 1");
            $stmt->execute(['id' => (int)$id]);
        } else {
            $stmt = $db->prepare("SELECT * FROM events WHERE slug = :slug OR custom_url = :custom_url LIMIT 1");
            $stmt->execute(['slug' => $id, 'custom_url' => $id]);
        }
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * The Interactive Event Activity Hub (Polls, Trivia, DJ Setlist, Hype Wall).
     */
    public function hub(Request $request, int|string $id): string
    {
        $event = $this->getEvent($id);
        if (!$event) {
            Application::$app->session->setFlash('error', 'Event not found.');
            redirect('/');
            return '';
        }

        $eventId = (int)$event['id'];
        $tab = $request->getString('tab', 'wall');

        $user = Application::$app->user;
        $userId = $user ? (int)$user->id : null;
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

        // Fetch all activities
        $polls = $this->service->getEventPolls($eventId);
        $trivia = $this->service->getEventTrivia($eventId);
        $songRequests = $this->service->getEventRequests($eventId, 'song', $userId, $ip);
        $qaRequests = $this->service->getEventRequests($eventId, 'qa', $userId, $ip);
        $communityPosts = $this->service->getCommunityPosts($eventId, 'all');

        return $this->render('event-activity-hub', [
            'title' => 'Community & Live Activities — ' . $event['name'],
            'event' => (object)$event,
            'activeTab' => $tab,
            'polls' => $polls,
            'trivia' => $trivia,
            'songRequests' => $songRequests,
            'qaRequests' => $qaRequests,
            'communityPosts' => $communityPosts,
            'user' => $user
        ]);
    }

    /**
     * Cast vote in a poll.
     */
    public function vote(Request $request, int|string $id, int|string $pollId): void
    {
        $event = $this->getEvent($id);
        if (!$event) {
            redirect('/');
            return;
        }

        $data = $request->getBody();
        $optionId = (int)($data['option_id'] ?? 0);
        $user = Application::$app->user;

        if (!$optionId) {
            Application::$app->session->setFlash('error', 'Please select a nominee or option to vote.');
            redirect('/event/' . ($event['slug'] ?? $event['id']) . '/activities?tab=polls');
            return;
        }

        // Only registered & verified attendees can cast the free daily vote
        if (!$user) {
            Application::$app->session->setFlash('error', 'Free voting is reserved for registered users. Please sign in or use ⚡ Power Vote to boost your nominee!');
            redirect('/event/' . ($event['slug'] ?? $event['id']) . '/activities?tab=polls');
            return;
        }

        if (!$user->isVerified()) {
            Application::$app->session->setFlash('error', 'Please verify your email address to unlock your daily free vote, or use ⚡ Power Vote to boost your nominee!');
            redirect('/event/' . ($event['slug'] ?? $event['id']) . '/activities?tab=polls');
            return;
        }

        $voterData = [
            'user_id' => (int)$user->id,
            'name' => $user->name,
            'email' => $user->email,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
            'quantity' => 1,
            'amount_paid' => 0.00
        ];

        $res = $this->service->castVote((int)$pollId, $optionId, $voterData);

        if ($res['success']) {
            Application::$app->session->setFlash('success', $res['message']);
        } else {
            Application::$app->session->setFlash('error', $res['message']);
        }

        redirect('/event/' . ($event['slug'] ?? $event['id']) . '/activities?tab=polls');
    }

    /**
     * Submit Trivia Answer (AJAX JSON).
     */
    public function submitTrivia(Request $request, int|string $id): void
    {
        header('Content-Type: application/json');
        $event = $this->getEvent($id);
        if (!$event) {
            echo json_encode(['success' => false, 'message' => 'Event not found']);
            exit;
        }

        $data = json_decode(file_get_contents('php://input'), true) ?: $request->getBody();
        $triviaId = (int)($data['trivia_id'] ?? 0);
        $answers = $data['answers'] ?? [];
        $user = Application::$app->user;

        $playerData = [
            'name' => $user ? $user->name : trim($data['name'] ?? 'Attendee'),
            'email' => $user ? $user->email : trim($data['email'] ?? ''),
            'user_id' => $user ? (int)$user->id : null,
            'time_taken' => (int)($data['time_taken'] ?? 0)
        ];

        $result = $this->service->submitTrivia($triviaId, $answers, $playerData);
        echo json_encode($result);
        exit;
    }

    /**
     * Submit Song Request or Speaker Q&A (AJAX JSON).
     */
    public function submitRequest(Request $request, int|string $id): void
    {
        header('Content-Type: application/json');
        $event = $this->getEvent($id);
        if (!$event) {
            echo json_encode(['success' => false, 'message' => 'Event not found']);
            exit;
        }

        $data = json_decode(file_get_contents('php://input'), true) ?: $request->getBody();
        $user = Application::$app->user;

        if ($user) {
            $data['requester_name'] = $user->name;
            $data['requester_email'] = $user->email;
        }

        $result = $this->service->submitRequest((int)$event['id'], $data, $user ? (int)$user->id : null);
        echo json_encode($result);
        exit;
    }

    /**
     * Upvote a Request (AJAX JSON).
     */
    public function upvoteRequest(Request $request, int|string $id, int|string $reqId): void
    {
        header('Content-Type: application/json');
        $user = Application::$app->user;
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

        $result = $this->service->upvoteRequest((int)$reqId, $user ? (int)$user->id : null, $ip);
        echo json_encode($result);
        exit;
    }

    /**
     * Post on Hype Wall / Squad Finder (AJAX JSON or Form POST).
     */
    public function postCommunity(Request $request, int|string $id): void
    {
        $event = $this->getEvent($id);
        if (!$event) {
            redirect('/');
            return;
        }

        $data = $request->getBody();
        $user = Application::$app->user;

        if ($user) {
            $data['author_name'] = $user->name;
            $data['author_email'] = $user->email;
        }

        $res = $this->service->createCommunityPost((int)$event['id'], $data, $user ? (int)$user->id : null);

        if ($request->isAjax()) {
            header('Content-Type: application/json');
            echo json_encode($res);
            exit;
        }

        if ($res['success']) {
            Application::$app->session->setFlash('success', $res['message']);
        } else {
            Application::$app->session->setFlash('error', $res['message']);
        }

        redirect('/event/' . ($event['slug'] ?? $event['id']) . '/activities?tab=wall');
    }

    /**
     * Like a Hype Wall Post (AJAX JSON).
     */
    public function likeCommunity(Request $request, int|string $id, int|string $postId): void
    {
        header('Content-Type: application/json');
        $newCount = $this->service->likeCommunityPost((int)$postId);
        echo json_encode(['success' => true, 'likes' => $newCount]);
        exit;
    }

    // ═══════════════════════════════════════════════════════════════════
    // ⚡ POWER VOTE ENDPOINTS
    // ═══════════════════════════════════════════════════════════════════

    /**
     * Initialize Power Vote payment (AJAX JSON → returns Paystack data).
     * POST /event/{slug}/vote/{pollId}/power/init
     */
    public function initPowerVote(Request $request, int|string $id, int|string $pollId): void
    {
        header('Content-Type: application/json');
        $event = $this->getEvent($id);
        if (!$event) {
            echo json_encode(['success' => false, 'message' => 'Event not found.']);
            exit;
        }

        $data = json_decode(file_get_contents('php://input'), true) ?: $request->getBody();
        $user = Application::$app->user;

        $optionId = (int)($data['option_id'] ?? 0);
        $quantity = max(1, (int)($data['quantity'] ?? 1));

        if (!$optionId) {
            echo json_encode(['success' => false, 'message' => 'Please select a nominee / option to vote for.']);
            exit;
        }

        $voterData = [
            'user_id' => $user ? (int)$user->id : null,
            'name'    => $user ? $user->name : trim($data['voter_name'] ?? 'Fan'),
            'email'   => $user ? $user->email : trim($data['voter_email'] ?? ''),
        ];

        if (empty($voterData['email'])) {
            echo json_encode(['success' => false, 'message' => 'Email address is required for payment receipt.']);
            exit;
        }

        $result = $this->service->initializePowerVote((int)$pollId, $optionId, $quantity, $voterData);
        echo json_encode($result);
        exit;
    }

    /**
     * Verify Power Vote payment after Paystack redirect (browser callback).
     * GET /event/{slug}/vote/paystack/callback?ref=PV-...
     */
    public function powerVoteCallback(Request $request, int|string $id): void
    {
        $event = $this->getEvent($id);
        $slug = $event ? ($event['slug'] ?? $event['id']) : $id;

        $reference = trim($request->getString('reference', '') ?: $request->getString('ref', '') ?: $request->getString('trxref', ''));

        if (empty($reference)) {
            Application::$app->session->setFlash('error', 'Missing payment reference.');
            redirect('/event/' . $slug . '/activities?tab=polls');
            return;
        }

        $result = $this->service->fulfillPowerVote($reference);

        if ($result['success']) {
            $qty = $result['votes_quantity'] ?? 0;
            Application::$app->session->setFlash('success', "⚡ {$qty} Power Votes successfully credited!");
        } else {
            Application::$app->session->setFlash('error', $result['message'] ?? 'Power Vote payment failed.');
        }

        redirect('/event/' . $slug . '/activities?tab=polls');
    }

    /**
     * Verify Power Vote payment inline (AJAX JSON — from Paystack.setup onClose).
     * POST /event/{slug}/vote/power/verify
     */
    public function verifyPowerVote(Request $request, int|string $id): void
    {
        header('Content-Type: application/json');
        $data = json_decode(file_get_contents('php://input'), true) ?: $request->getBody();
        $reference = trim($data['reference'] ?? '');

        if (empty($reference)) {
            echo json_encode(['success' => false, 'message' => 'Missing transaction reference.']);
            exit;
        }

        $result = $this->service->fulfillPowerVote($reference);
        echo json_encode($result);
        exit;
    }
}
