<?php

namespace App\Controllers\Host;

use Ace\Application;
use Ace\Controller;
use Ace\Request;
use App\Middlewares\HostMiddleware;
use App\Services\EventActivityService;
use PDO;

class HostActivityController extends Controller
{
    protected EventActivityService $service;

    public function __construct()
    {
        $this->registerMiddleware(new HostMiddleware());
        $this->service = new EventActivityService();
    }

    /**
     * Verify event ownership or admin privileges.
     */
    protected function getAuthorizedEvent(int|string $eventId): ?array
    {
        $user = Application::$app->user;
        $db = Application::$app->db->pdo;

        if (is_numeric($eventId)) {
            $stmt = $db->prepare("SELECT * FROM events WHERE id = :id LIMIT 1");
            $stmt->execute(['id' => (int)$eventId]);
        } else {
            $stmt = $db->prepare("SELECT * FROM events WHERE slug = :slug OR custom_url = :custom_url LIMIT 1");
            $stmt->execute(['slug' => $eventId, 'custom_url' => $eventId]);
        }
        $event = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$event) return null;

        $isAdmin = (method_exists($user, 'isAdmin') && $user->isAdmin()) || 
                   (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) || 
                   hasRole('admin') || hasRole('super-admin');

        if ($event['organizer_id'] != $user->id && !$isAdmin) {
            return null;
        }

        return $event;
    }

    /**
     * Host Event Activities Management Center.
     */
    public function index(Request $request, int|string $id): string
    {
        $event = $this->getAuthorizedEvent($id);
        if (!$event) {
            Application::$app->session->setFlash('error', 'Unauthorized access to this event.');
            redirect('/host/dashboard');
            return '';
        }

        $eventId = (int)$event['id'];
        $polls = $this->service->getEventPolls($eventId);
        $trivia = $this->service->getEventTrivia($eventId);
        $songRequests = $this->service->getEventRequests($eventId, 'song');
        $qaRequests = $this->service->getEventRequests($eventId, 'qa');
        $communityPosts = $this->service->getCommunityPosts($eventId, 'all', 100);

        return $this->render('host/activities', [
            'title' => 'Activities & Engagement Studio — ' . $event['name'],
            'event' => (object)$event,
            'polls' => $polls,
            'trivia' => $trivia,
            'songRequests' => $songRequests,
            'qaRequests' => $qaRequests,
            'communityPosts' => $communityPosts
        ]);
    }

    /**
     * Create a new poll with nominees/options.
     */
    public function createPoll(Request $request, int|string $id): void
    {
        $event = $this->getAuthorizedEvent($id);
        if (!$event) {
            redirect('/host/dashboard');
            return;
        }

        $data = $request->getBody();
        $title = trim($data['title'] ?? '');
        $description = trim($data['description'] ?? '');
        $options = $data['options'] ?? [];
        $pollType = in_array($data['poll_type'] ?? '', ['free', 'power', 'paid']) ? $data['poll_type'] : 'power';
        $voteCost = max(50.00, (float)($data['vote_cost'] ?? 100.00));
        $allowPowerVotes = ($pollType === 'free') ? 0 : 1;
        if ($pollType === 'free') {
            $voteCost = 0.00;
        }

        if (empty($title) || empty($options) || !is_array($options)) {
            Application::$app->session->setFlash('error', 'Please provide a poll title and at least two options.');
            redirect('/host/event/' . $event['id'] . '/activities');
            return;
        }

        $db = Application::$app->db->pdo;
        $db->beginTransaction();
        try {
            $stmt = $db->prepare("
                INSERT INTO event_polls (event_id, title, description, poll_type, vote_cost, allow_power_votes, status, created_at)
                VALUES (:event_id, :title, :desc, :poll_type, :vote_cost, :allow_power, 'active', NOW())
            ");
            $stmt->execute([
                'event_id' => $event['id'],
                'title' => $title,
                'desc' => $description,
                'poll_type' => $pollType,
                'vote_cost' => $voteCost,
                'allow_power' => $allowPowerVotes
            ]);
            $pollId = (int)$db->lastInsertId();

            $optStmt = $db->prepare("
                INSERT INTO event_poll_options (poll_id, title, subtitle, display_order, created_at)
                VALUES (:poll_id, :title, :sub, :ord, NOW())
            ");

            $order = 0;
            foreach ($options as $optTitle) {
                $optTitle = trim($optTitle);
                if (empty($optTitle)) continue;
                $optStmt->execute([
                    'poll_id' => $pollId,
                    'title' => $optTitle,
                    'sub' => null,
                    'ord' => ++$order
                ]);
            }

            $db->commit();
            Application::$app->session->setFlash('success', "Poll '{$title}' created with {$order} options!");
        } catch (\Throwable $e) {
            $db->rollBack();
            Application::$app->session->setFlash('error', 'Error creating poll: ' . $e->getMessage());
        }

        redirect('/host/event/' . $event['id'] . '/activities');
    }

    /**
     * Close or activate a poll.
     */
    public function togglePoll(Request $request, int|string $id, int|string $pollId): void
    {
        $event = $this->getAuthorizedEvent($id);
        if (!$event) {
            redirect('/host/dashboard');
            return;
        }

        $db = Application::$app->db->pdo;
        $stmt = $db->prepare("SELECT status FROM event_polls WHERE id = :id AND event_id = :event_id LIMIT 1");
        $stmt->execute(['id' => (int)$pollId, 'event_id' => (int)$event['id']]);
        $current = $stmt->fetchColumn();

        if ($current) {
            $newStatus = $current === 'active' ? 'closed' : 'active';
            $db->prepare("UPDATE event_polls SET status = :status WHERE id = :id")
               ->execute(['status' => $newStatus, 'id' => (int)$pollId]);
            Application::$app->session->setFlash('success', "Poll status changed to " . ucfirst($newStatus));
        }

        redirect('/host/event/' . $event['id'] . '/activities');
    }

    /**
     * Create a trivia game with questions.
     */
    public function createTrivia(Request $request, int|string $id): void
    {
        $event = $this->getAuthorizedEvent($id);
        if (!$event) {
            redirect('/host/dashboard');
            return;
        }

        $data = $request->getBody();
        $title = trim($data['title'] ?? '');
        $description = trim($data['description'] ?? '');
        $passScore = max(1, (int)($data['pass_score'] ?? 3));
        $rawCode = trim($data['reward_promo_code'] ?? '');
        $rewardCode = !empty($rawCode) ? strtoupper(preg_replace('/[^A-Za-z0-9_-]/', '', $rawCode)) : null;
        $rewardDesc = trim($data['reward_description'] ?? 'Exclusive ticket discount!') ?: null;
        $rewardType = in_array($data['reward_discount_type'] ?? '', ['percentage', 'fixed']) ? $data['reward_discount_type'] : 'percentage';
        $rewardValue = max(0.01, (float)($data['reward_discount_value'] ?? 10.00));
        $questions = $data['questions'] ?? [];

        if (empty($title) || empty($questions) || !is_array($questions)) {
            Application::$app->session->setFlash('error', 'Please provide a trivia title and at least one question.');
            redirect('/host/event/' . $event['id'] . '/activities');
            return;
        }

        $db = Application::$app->db->pdo;
        $db->beginTransaction();
        try {
            // Deactivate previous active trivia games for this event
            $db->prepare("UPDATE event_trivia SET status = 'inactive' WHERE event_id = :event_id")
               ->execute(['event_id' => $event['id']]);

            $stmt = $db->prepare("
                INSERT INTO event_trivia (event_id, title, description, pass_score, reward_promo_code, reward_discount_type, reward_discount_value, reward_description, status, created_at)
                VALUES (:event_id, :title, :desc, :score, :code, :dtype, :dval, :rdesc, 'active', NOW())
            ");
            $stmt->execute([
                'event_id' => $event['id'],
                'title' => $title,
                'desc' => $description,
                'score' => $passScore,
                'code' => $rewardCode,
                'dtype' => $rewardType,
                'dval' => $rewardValue,
                'rdesc' => $rewardDesc
            ]);
            $triviaId = (int)$db->lastInsertId();

            $qStmt = $db->prepare("
                INSERT INTO event_trivia_questions 
                (trivia_id, question, option_a, option_b, option_c, option_d, correct_option, display_order)
                VALUES (:t_id, :q, :a, :b, :c, :d, :correct, :ord)
            ");

            $qCount = 0;
            foreach ($questions as $qItem) {
                $qText = trim($qItem['question'] ?? '');
                $optA = trim($qItem['option_a'] ?? '');
                $optB = trim($qItem['option_b'] ?? '');
                $optC = trim($qItem['option_c'] ?? '');
                $optD = trim($qItem['option_d'] ?? '');
                $correct = strtoupper(trim($qItem['correct_option'] ?? 'A'));

                if (empty($qText) || empty($optA) || empty($optB)) continue;

                $qStmt->execute([
                    't_id' => $triviaId,
                    'q' => $qText,
                    'a' => $optA,
                    'b' => $optB,
                    'c' => $optC ?: null,
                    'd' => $optD ?: null,
                    'correct' => in_array($correct, ['A', 'B', 'C', 'D']) ? $correct : 'A',
                    'ord' => ++$qCount
                ]);
            }

            $db->commit();
            Application::$app->session->setFlash('success', "Trivia game '{$title}' created with {$qCount} questions!");
        } catch (\Throwable $e) {
            $db->rollBack();
            Application::$app->session->setFlash('error', 'Error creating trivia: ' . $e->getMessage());
        }

        redirect('/host/event/' . $event['id'] . '/activities');
    }

    /**
     * Delete a trivia game.
     */
    public function deleteTrivia(Request $request, int|string $id, int|string $triviaId): void
    {
        $event = $this->getAuthorizedEvent($id);
        if (!$event) {
            redirect('/host/dashboard');
            return;
        }

        $db = Application::$app->db->pdo;
        $db->prepare("DELETE FROM event_trivia WHERE id = :id AND event_id = :event_id")
           ->execute(['id' => (int)$triviaId, 'event_id' => (int)$event['id']]);

        Application::$app->session->setFlash('success', "Trivia challenge removed.");
        redirect('/host/event/' . $event['id'] . '/activities');
    }

    /**
     * Moderate a song request or Q&A (mark as played/answered or hide).
     */
    public function moderateRequest(Request $request, int|string $id, int|string $reqId): void
    {
        $event = $this->getAuthorizedEvent($id);
        if (!$event) {
            redirect('/host/dashboard');
            return;
        }

        $data = $request->getBody();
        $status = in_array($data['status'] ?? '', ['approved', 'played', 'hidden']) ? $data['status'] : 'played';

        $db = Application::$app->db->pdo;
        $db->prepare("UPDATE event_requests SET status = :status WHERE id = :id AND event_id = :event_id")
           ->execute(['status' => $status, 'id' => (int)$reqId, 'event_id' => (int)$event['id']]);

        Application::$app->session->setFlash('success', "Item status updated to " . ucfirst($status));
        redirect('/host/event/' . $event['id'] . '/activities');
    }

    /**
     * Moderate a Hype Wall post (hide or delete).
     */
    public function moderateCommunity(Request $request, int|string $id, int|string $postId): void
    {
        $event = $this->getAuthorizedEvent($id);
        if (!$event) {
            redirect('/host/dashboard');
            return;
        }

        $db = Application::$app->db->pdo;
        $db->prepare("DELETE FROM event_community_posts WHERE id = :id AND event_id = :event_id")
           ->execute(['id' => (int)$postId, 'event_id' => (int)$event['id']]);

        Application::$app->session->setFlash('success', "Community post removed.");
        redirect('/host/event/' . $event['id'] . '/activities');
    }
}
