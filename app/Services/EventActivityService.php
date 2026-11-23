<?php

namespace App\Services;

use Ace\Application;
use App\Models\LayawayCredit;
use PDO;

class EventActivityService
{
    protected PDO $db;

    public function __construct()
    {
        $this->db = Application::$app->db->pdo;
    }

    // ═══════════════════════════════════════════════════════════════════
    // 1. EVENT POLLS & VOTING CONTESTS
    // ═══════════════════════════════════════════════════════════════════

    /**
     * Get all active and completed polls for an event.
     */
    public function getEventPolls(int $eventId): array
    {
        $stmt = $this->db->prepare("
            SELECT p.*,
                   (SELECT COUNT(*) FROM event_poll_options WHERE poll_id = p.id) as options_count
            FROM event_polls p
            WHERE p.event_id = :event_id
            ORDER BY p.status = 'active' DESC, p.id DESC
        ");
        $stmt->execute(['event_id' => $eventId]);
        $polls = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        foreach ($polls as &$poll) {
            $poll['options'] = $this->getPollOptions((int)$poll['id']);
        }
        return $polls;
    }

    /**
     * Get details of a single poll including its options and percentages.
     */
    public function getPoll(int $pollId): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM event_polls WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $pollId]);
        $poll = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$poll) return null;

        $poll['options'] = $this->getPollOptions($pollId);
        return $poll;
    }

    /**
     * Get options for a poll sorted by votes count (leaderboard style).
     */
    public function getPollOptions(int $pollId): array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM event_poll_options 
            WHERE poll_id = :poll_id 
            ORDER BY votes_count DESC, display_order ASC, id ASC
        ");
        $stmt->execute(['poll_id' => $pollId]);
        $options = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $totalVotes = array_sum(array_column($options, 'votes_count'));
        foreach ($options as &$opt) {
            $opt['percentage'] = $totalVotes > 0 ? round(($opt['votes_count'] / $totalVotes) * 100, 1) : 0;
        }
        return $options;
    }

    /**
     * Check if a registered user has already voted in a free poll within the last 24 hours.
     */
    public function hasVotedFree(int $pollId, ?int $userId): bool
    {
        if (!$userId) return false;

        $stmt = $this->db->prepare("
            SELECT id FROM event_poll_votes 
            WHERE poll_id = :poll_id AND user_id = :user_id 
              AND (amount_paid = 0 OR amount_paid IS NULL)
              AND created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
            LIMIT 1
        ");
        $stmt->execute(['poll_id' => $pollId, 'user_id' => $userId]);
        return (bool)$stmt->fetch();
    }

    /**
     * Cast a free vote in a poll (reserved for registered and verified users).
     */
    public function castVote(int $pollId, int $optionId, array $voterData): array
    {
        $poll = $this->getPoll($pollId);
        if (!$poll || $poll['status'] !== 'active') {
            return ['success' => false, 'message' => 'This poll is currently not accepting votes.'];
        }

        if ($poll['poll_type'] === 'paid') {
            return ['success' => false, 'message' => 'This is an exclusive contest where votes are cast via Power Vote packs. Please choose your vote bundle to vote!'];
        }

        $userId = $voterData['user_id'] ?? null;
        if (!$userId) {
            return ['success' => false, 'message' => 'Free voting is reserved for registered and verified attendees. Guests can boost their nominee using ⚡ Power Vote!'];
        }

        $ip = $voterData['ip_address'] ?? '127.0.0.1';
        $voterName = trim($voterData['name'] ?? 'Attendee');
        $voterEmail = trim($voterData['email'] ?? '');
        $qty = 1;
        $amountPaid = 0.00;
        $reference = null;

        // Check deduplication (1 free vote per 24 hours per user account)
        if ($this->hasVotedFree($pollId, $userId)) {
            return ['success' => false, 'message' => 'You have already cast your free vote for today! Use ⚡ Power Vote to boost your nominee with more vote slots.'];
        }

        $this->db->beginTransaction();
        try {
            // Record the vote
            $stmt = $this->db->prepare("
                INSERT INTO event_poll_votes 
                (poll_id, option_id, user_id, voter_name, voter_email, ip_address, votes_quantity, amount_paid, payment_reference, created_at)
                VALUES (:poll_id, :opt_id, :user_id, :name, :email, :ip, :qty, :amount, :ref, NOW())
            ");
            $stmt->execute([
                'poll_id' => $pollId,
                'opt_id' => $optionId,
                'user_id' => $userId,
                'name' => $voterName,
                'email' => $voterEmail,
                'ip' => $ip,
                'qty' => $qty,
                'amount' => $amountPaid,
                'ref' => $reference
            ]);

            // Increment option votes
            $this->db->prepare("UPDATE event_poll_options SET votes_count = votes_count + :qty WHERE id = :id")
                     ->execute(['qty' => $qty, 'id' => $optionId]);

            // Increment poll total votes and revenue
            $this->db->prepare("UPDATE event_polls SET total_votes = total_votes + :qty, total_revenue = total_revenue + :amount WHERE id = :id")
                     ->execute(['qty' => $qty, 'amount' => $amountPaid, 'id' => $pollId]);

            // Award badge to user if logged in
            if ($userId) {
                $this->checkAndAwardBadge((int)$userId, 'first_vote', (int)$poll['event_id']);
            }

            $this->db->commit();
            return ['success' => true, 'message' => "Your free vote has been cast successfully!"];
        } catch (\Throwable $e) {
            $this->db->rollBack();
            return ['success' => false, 'message' => 'Error recording vote: ' . $e->getMessage()];
        }
    }

    /**
     * Initialize a Power Vote purchase via Paystack.
     */
    public function initializePowerVote(int $pollId, int $optionId, int $quantity, array $voterData): array
    {
        $poll = $this->getPoll($pollId);
        if (!$poll || $poll['status'] !== 'active') {
            return ['success' => false, 'message' => 'This poll is currently not accepting votes.'];
        }

        if (empty($poll['allow_power_votes']) && $poll['poll_type'] === 'free') {
            return ['success' => false, 'message' => 'Power voting is not enabled on this poll.'];
        }

        // Validate option
        $optStmt = $this->db->prepare("SELECT * FROM event_poll_options WHERE id = :opt_id AND poll_id = :poll_id LIMIT 1");
        $optStmt->execute(['opt_id' => $optionId, 'poll_id' => $pollId]);
        $option = $optStmt->fetch(PDO::FETCH_ASSOC);
        if (!$option) {
            return ['success' => false, 'message' => 'Invalid nominee / option selected.'];
        }

        $quantity = max(1, (int)$quantity);
        $costPerVote = (float)(!empty($poll['vote_cost']) && $poll['vote_cost'] > 0 ? $poll['vote_cost'] : 100.00);
        $totalAmount = round($quantity * $costPerVote, 2);

        $voterEmail = trim($voterData['email'] ?? '');
        $voterName = trim($voterData['name'] ?? 'Power Voter');
        $userId = $voterData['user_id'] ?? null;

        if (empty($voterEmail)) {
            return ['success' => false, 'message' => 'Please provide a valid email address for receipt and verification.'];
        }

        // Unique transaction reference
        $reference = 'PV-' . $poll['id'] . '-' . $optionId . '-' . strtoupper(bin2hex(random_bytes(6)));

        // Build callback URL
        $appUrl = rtrim($_ENV['APP_URL'] ?? 'http://localhost', '/');
        $evtStmt = $this->db->prepare("SELECT slug, id FROM events WHERE id = :id LIMIT 1");
        $evtStmt->execute(['id' => $poll['event_id']]);
        $evt = $evtStmt->fetch(PDO::FETCH_ASSOC) ?: ['slug' => $poll['event_id'], 'id' => $poll['event_id']];
        $slug = $evt['slug'] ?: $evt['id'];
        $callbackUrl = $appUrl . '/event/' . $slug . '/vote/paystack/callback?ref=' . urlencode($reference);

        $activityFeeRate = (float)site_setting('activity_fee_percent', 10.0);
        $platformFee = round(($totalAmount * $activityFeeRate) / 100, 2);
        $hostNetEarnings = round($totalAmount - $platformFee, 2);

        $metadata = [
            'type' => 'power_vote',
            'poll_id' => $pollId,
            'poll_title' => $poll['title'],
            'option_id' => $optionId,
            'option_title' => $option['title'],
            'quantity' => $quantity,
            'event_id' => (int)$poll['event_id'],
            'voter_name' => $voterName,
            'voter_email' => $voterEmail,
            'user_id' => $userId,
            'cost_per_vote' => $costPerVote,
            'total_amount' => $totalAmount,
            'activity_fee_rate' => $activityFeeRate,
            'platform_fee' => $platformFee,
            'host_net_earnings' => $hostNetEarnings
        ];

        // 1. Create a pending record in transactions table
        try {
            $txStmt = $this->db->prepare("
                INSERT INTO transactions 
                (reference, event_id, user_id, payer_name, payer_email, type, payment_method, subtotal_amount, fee_amount, total_amount, status, gateway_response, created_at, updated_at)
                VALUES (:ref, :event_id, :user_id, :name, :email, 'payment', 'paystack_powervote', :subtotal, :fee, :total, 'pending', :gw_resp, NOW(), NOW())
            ");
            $txStmt->execute([
                'ref' => $reference,
                'event_id' => (int)$poll['event_id'],
                'user_id' => $userId,
                'name' => $voterName,
                'email' => $voterEmail,
                'subtotal' => $totalAmount,
                'fee' => $platformFee,
                'total' => $totalAmount,
                'gw_resp' => json_encode(['order_data' => $metadata])
            ]);
        } catch (\Throwable $e) {
            error_log("Failed to insert pending Power Vote transaction: " . $e->getMessage());
        }

        // 2. Initialize with Paystack
        try {
            $paystack = new \Ace\PaystackService();
            $paystackData = $paystack->initializeTransaction($voterEmail, $totalAmount, $callbackUrl, $reference, $metadata);

            $publicKey = setting('paystack_public_key') ?: ($_ENV['PAYSTACK_PUBLIC_KEY'] ?? '');

            return [
                'success' => true,
                'reference' => $reference,
                'authorization_url' => $paystackData['authorization_url'] ?? '',
                'access_code' => $paystackData['access_code'] ?? '',
                'public_key' => $publicKey,
                'amount' => $totalAmount,
                'amount_kobo' => (int)round($totalAmount * 100),
                'quantity' => $quantity,
                'option_title' => $option['title'],
                'voter_email' => $voterEmail,
                'voter_name' => $voterName,
                'message' => 'Power Vote initialized successfully.'
            ];
        } catch (\Throwable $e) {
            return ['success' => false, 'message' => 'Payment initialization failed: ' . $e->getMessage()];
        }
    }

    /**
     * Fulfill a Power Vote payment and cast the purchased vote bundle idempotently.
     */
    public function fulfillPowerVote(string $reference): array
    {
        $reference = trim($reference);
        if (empty($reference)) {
            return ['success' => false, 'message' => 'Missing transaction reference.'];
        }

        // Check if already fulfilled in event_poll_votes (Idempotency protection)
        $chkStmt = $this->db->prepare("SELECT * FROM event_poll_votes WHERE payment_reference = :ref LIMIT 1");
        $chkStmt->execute(['ref' => $reference]);
        $existingVote = $chkStmt->fetch(PDO::FETCH_ASSOC);
        if ($existingVote) {
            return [
                'success' => true,
                'already_fulfilled' => true,
                'message' => 'Your Power Votes have already been credited and counted!',
                'votes_quantity' => (int)$existingVote['votes_quantity'],
                'poll_id' => (int)$existingVote['poll_id'],
                'option_id' => (int)$existingVote['option_id']
            ];
        }

        // Verify with Paystack
        $paystack = new \Ace\PaystackService();
        try {
            $verification = $paystack->verifyTransaction($reference);
        } catch (\Throwable $e) {
            return ['success' => false, 'message' => 'Payment verification error: ' . $e->getMessage()];
        }

        if (($verification['status'] ?? '') !== 'success') {
            $errMsg = $verification['gateway_response'] ?? 'Payment was not completed.';
            return ['success' => false, 'message' => 'Payment unsuccessful: ' . $errMsg];
        }

        // Extract metadata from verification or pending transactions table
        $meta = $verification['metadata'] ?? [];
        if (empty($meta['poll_id'])) {
            $txStmt = $this->db->prepare("SELECT gateway_response FROM transactions WHERE reference = :ref LIMIT 1");
            $txStmt->execute(['ref' => $reference]);
            $txRow = $txStmt->fetch(PDO::FETCH_ASSOC);
            if ($txRow && !empty($txRow['gateway_response'])) {
                $parsed = json_decode($txRow['gateway_response'], true);
                $meta = $parsed['order_data'] ?? $parsed;
            }
        }

        $pollId = (int)($meta['poll_id'] ?? 0);
        $optionId = (int)($meta['option_id'] ?? 0);
        $quantity = max(1, (int)($meta['quantity'] ?? 1));
        $amountPaid = (float)(($verification['amount'] ?? 0) / 100); // Kobo to Naira
        if ($amountPaid <= 0 && isset($meta['total_amount'])) {
            $amountPaid = (float)$meta['total_amount'];
        }

        $userId = !empty($meta['user_id']) ? (int)$meta['user_id'] : null;
        $voterName = $meta['voter_name'] ?? ($verification['customer']['first_name'] ?? 'Power Voter');
        $voterEmail = $meta['voter_email'] ?? ($verification['customer']['email'] ?? '');
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

        $this->db->beginTransaction();
        try {
            // 1. Insert into event_poll_votes
            $voteStmt = $this->db->prepare("
                INSERT INTO event_poll_votes 
                (poll_id, option_id, user_id, voter_name, voter_email, ip_address, votes_quantity, amount_paid, payment_reference, created_at)
                VALUES (:poll_id, :opt_id, :user_id, :name, :email, :ip, :qty, :amount, :ref, NOW())
            ");
            $voteStmt->execute([
                'poll_id' => $pollId,
                'opt_id' => $optionId,
                'user_id' => $userId,
                'name' => $voterName,
                'email' => $voterEmail,
                'ip' => $ip,
                'qty' => $quantity,
                'amount' => $amountPaid,
                'ref' => $reference
            ]);

            // 2. Increment option votes
            $this->db->prepare("UPDATE event_poll_options SET votes_count = votes_count + :qty WHERE id = :id")
                     ->execute(['qty' => $quantity, 'id' => $optionId]);

            // 3. Increment poll total votes and revenue
            $this->db->prepare("UPDATE event_polls SET total_votes = total_votes + :qty, total_revenue = total_revenue + :amount WHERE id = :id")
                     ->execute(['qty' => $quantity, 'amount' => $amountPaid, 'id' => $pollId]);

            // 4. Update transactions table to completed and record exact platform fee
            $activityFeeRate = (float)site_setting('activity_fee_percent', 10.0);
            $platformFee = round(($amountPaid * $activityFeeRate) / 100, 2);

            $this->db->prepare("
                UPDATE transactions 
                SET status = 'completed', fee_amount = :fee, gateway_response = :resp, updated_at = NOW() 
                WHERE reference = :ref
            ")->execute([
                'fee' => $platformFee,
                'resp' => json_encode($verification),
                'ref' => $reference
            ]);

            // 5. Award badge to user if logged in
            if ($userId) {
                $this->checkAndAwardBadge((int)$userId, 'power_voter', (int)($meta['event_id'] ?? 0));
            }

            $this->db->commit();

            return [
                'success' => true,
                'message' => "⚡ Boom! {$quantity} Power Votes successfully cast for your nominee!",
                'votes_quantity' => $quantity,
                'amount_paid' => $amountPaid,
                'poll_id' => $pollId,
                'option_id' => $optionId
            ];
        } catch (\Throwable $e) {
            $this->db->rollBack();
            return ['success' => false, 'message' => 'Database error fulfilling vote: ' . $e->getMessage()];
        }
    }

    // ═══════════════════════════════════════════════════════════════════
    // 2. EVENT TRIVIA & GUESS GAME
    // ═══════════════════════════════════════════════════════════════════

    /**
     * Get active trivia for an event.
     */
    public function getEventTrivia(int $eventId): ?array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM event_trivia 
            WHERE event_id = :event_id AND status = 'active' 
            ORDER BY id DESC LIMIT 1
        ");
        $stmt->execute(['event_id' => $eventId]);
        $trivia = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$trivia) return null;

        $trivia['questions'] = $this->getTriviaQuestions((int)$trivia['id'], false);
        return $trivia;
    }

    /**
     * Get questions for a trivia game.
     * When $withCorrect is false, the correct option is hidden from attendees to prevent cheating.
     */
    public function getTriviaQuestions(int $triviaId, bool $withCorrect = false): array
    {
        $cols = "id, trivia_id, question, option_a, option_b, option_c, option_d, display_order";
        if ($withCorrect) {
            $cols .= ", correct_option, explanation";
        }

        $stmt = $this->db->prepare("SELECT {$cols} FROM event_trivia_questions WHERE trivia_id = :id ORDER BY display_order ASC, id ASC");
        $stmt->execute(['id' => $triviaId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Process player trivia submission and determine win/reward.
     */
    public function submitTrivia(int $triviaId, array $submittedAnswers, array $playerData): array
    {
        $stmt = $this->db->prepare("SELECT * FROM event_trivia WHERE id = :id AND status = 'active' LIMIT 1");
        $stmt->execute(['id' => $triviaId]);
        $trivia = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$trivia) {
            return ['success' => false, 'message' => 'Trivia game not found or inactive.'];
        }

        $questions = $this->getTriviaQuestions($triviaId, true);
        if (empty($questions)) {
            return ['success' => false, 'message' => 'This trivia game has no questions yet.'];
        }

        $score = 0;
        $total = count($questions);
        $review = [];

        foreach ($questions as $q) {
            $qId = (int)$q['id'];
            $selected = strtoupper(trim($submittedAnswers[$qId] ?? ''));
            $correct = strtoupper(trim($q['correct_option']));
            $isCorrect = ($selected === $correct);

            if ($isCorrect) {
                $score++;
            }

            $review[] = [
                'question' => $q['question'],
                'selected' => $selected,
                'correct' => $correct,
                'is_correct' => $isCorrect,
                'explanation' => $q['explanation'] ?? ''
            ];
        }

        $passScore = (int)$trivia['pass_score'];
        $isWinner = ($score >= $passScore);
        $playerName = trim($playerData['name'] ?? 'Trivia Champ');
        $playerEmail = trim($playerData['email'] ?? '');
        $userId = $playerData['user_id'] ?? null;
        $timeTaken = (int)($playerData['time_taken'] ?? 0);

        // Record submission
        $subStmt = $this->db->prepare("
            INSERT INTO event_trivia_submissions 
            (trivia_id, user_id, player_name, player_email, score, total_questions, is_winner, time_taken_seconds, created_at)
            VALUES (:t_id, :u_id, :name, :email, :score, :total, :winner, :time, NOW())
        ");
        $subStmt->execute([
            't_id' => $triviaId,
            'u_id' => $userId,
            'name' => $playerName,
            'email' => $playerEmail,
            'score' => $score,
            'total' => $total,
            'winner' => $isWinner ? 1 : 0,
            'time' => $timeTaken
        ]);

        // Increment trivia counters
        $this->db->prepare("UPDATE event_trivia SET plays_count = plays_count + 1" . ($isWinner ? ", wins_count = wins_count + 1" : "") . " WHERE id = :id")
                 ->execute(['id' => $triviaId]);

        // Award badge to user if won and logged in
        if ($isWinner && $userId) {
            $this->checkAndAwardBadge((int)$userId, 'trivia_master', (int)$trivia['event_id']);
        }

        return [
            'success' => true,
            'score' => $score,
            'total' => $total,
            'is_winner' => $isWinner,
            'pass_score' => $passScore,
            'reward_promo_code' => $isWinner ? ($trivia['reward_promo_code'] ?? null) : null,
            'reward_discount_type' => $trivia['reward_discount_type'] ?? 'percentage',
            'reward_discount_value' => (float)($trivia['reward_discount_value'] ?? 10.00),
            'reward_description' => $trivia['reward_description'] ?? 'Exclusive Ticket Discount',
            'review' => $review
        ];
    }

    // ═══════════════════════════════════════════════════════════════════
    // 3. INTERACTIVE DJ REQUESTS & SPEAKER Q&A
    // ═══════════════════════════════════════════════════════════════════

    /**
     * Get requests (songs or Q&A) for an event sorted by upvotes and pinned status,
     * including user_voted flag if user or IP has upvoted.
     */
    public function getEventRequests(int $eventId, string $type = 'song', ?int $userId = null, ?string $ip = null): array
    {
        $joinCondition = $userId 
            ? "up.request_id = r.id AND up.user_id = :user_id"
            : "up.request_id = r.id AND up.user_id IS NULL AND up.ip_address = :ip";

        $params = [
            'event_id' => $eventId,
            'type' => $type
        ];

        if ($userId) {
            $params['user_id'] = $userId;
        } else {
            $params['ip'] = $ip ?: '127.0.0.1';
        }

        $stmt = $this->db->prepare("
            SELECT r.*,
                   u.name as user_name,
                   (CASE WHEN up.id IS NOT NULL THEN 1 ELSE 0 END) as user_voted
            FROM event_requests r
            LEFT JOIN users u ON r.user_id = u.id
            LEFT JOIN event_request_upvotes up ON {$joinCondition}
            WHERE r.event_id = :event_id AND r.type = :type AND r.status != 'hidden'
            ORDER BY r.is_pinned DESC, r.upvotes_count DESC, r.id DESC
        ");
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Submit a song request or Q&A question.
     */
    public function submitRequest(int $eventId, array $data, ?int $userId = null): array
    {
        $type = in_array($data['type'] ?? '', ['song', 'qa']) ? $data['type'] : 'song';
        $title = trim($data['title'] ?? '');
        $artistOrSpeaker = trim($data['artist_or_speaker'] ?? '') ?: null;
        $requesterName = trim($data['requester_name'] ?? 'Attendee');
        $requesterEmail = trim($data['requester_email'] ?? '') ?: null;
        $note = trim($data['note'] ?? '') ?: null;

        if (empty($title)) {
            return ['success' => false, 'message' => $type === 'song' ? 'Please provide a song title.' : 'Please enter your question.'];
        }

        $stmt = $this->db->prepare("
            INSERT INTO event_requests 
            (event_id, user_id, type, requester_name, requester_email, title, artist_or_speaker, note, upvotes_count, status, created_at)
            VALUES (:event_id, :user_id, :type, :name, :email, :title, :artist, :note, 1, 'approved', NOW())
        ");
        $stmt->execute([
            'event_id' => $eventId,
            'user_id' => $userId,
            'type' => $type,
            'name' => $requesterName,
            'email' => $requesterEmail,
            'title' => $title,
            'artist' => $artistOrSpeaker,
            'note' => $note
        ]);

        $newId = (int)$this->db->lastInsertId();

        // Award badge
        if ($userId) {
            $this->checkAndAwardBadge($userId, 'song_suggester', $eventId);
        }

        return ['success' => true, 'message' => $type === 'song' ? 'Track suggested successfully!' : 'Question submitted for the panel!'];
    }

    /**
     * Upvote a song request or Q&A question with vote switching & toggling.
     * Allows 1 vote per category (song vs qa) per attendee:
     * - Clicking the already-voted item toggles it off.
     * - Clicking another item switches the vote from the previous to the new one.
     */
    public function upvoteRequest(int $requestId, ?int $userId, string $ip): array
    {
        $reqStmt = $this->db->prepare("SELECT id, event_id, type, upvotes_count FROM event_requests WHERE id = :id LIMIT 1");
        $reqStmt->execute(['id' => $requestId]);
        $request = $reqStmt->fetch(PDO::FETCH_ASSOC);
        if (!$request) {
            return ['success' => false, 'message' => 'Request not found.'];
        }

        $eventId = (int)$request['event_id'];
        $type = $request['type'];

        // Check if this user/device already has an active upvote in this category (song or qa)
        if ($userId) {
            $existingStmt = $this->db->prepare("
                SELECT u.id as upvote_id, u.request_id 
                FROM event_request_upvotes u
                INNER JOIN event_requests r ON u.request_id = r.id
                WHERE r.event_id = :event_id AND r.type = :type AND u.user_id = :uid
                LIMIT 1
            ");
            $existingStmt->execute(['event_id' => $eventId, 'type' => $type, 'uid' => $userId]);
        } else {
            $existingStmt = $this->db->prepare("
                SELECT u.id as upvote_id, u.request_id 
                FROM event_request_upvotes u
                INNER JOIN event_requests r ON u.request_id = r.id
                WHERE r.event_id = :event_id AND r.type = :type AND u.user_id IS NULL AND u.ip_address = :ip
                LIMIT 1
            ");
            $existingStmt->execute(['event_id' => $eventId, 'type' => $type, 'ip' => $ip]);
        }
        $existing = $existingStmt->fetch(PDO::FETCH_ASSOC);

        // Case 1: Toggling OFF the current vote
        if ($existing && (int)$existing['request_id'] === $requestId) {
            $this->db->prepare("DELETE FROM event_request_upvotes WHERE id = :id")->execute(['id' => $existing['upvote_id']]);
            $this->db->prepare("UPDATE event_requests SET upvotes_count = GREATEST(0, upvotes_count - 1) WHERE id = :id")->execute(['id' => $requestId]);
            $newCount = (int)$this->db->query("SELECT upvotes_count FROM event_requests WHERE id = {$requestId}")->fetchColumn();

            return [
                'success' => true,
                'action' => 'unvoted',
                'request_id' => $requestId,
                'new_count' => $newCount,
                'switched_from' => null,
                'message' => 'Vote removed.'
            ];
        }

        // Case 2: Switching vote from another request in this category
        if ($existing && (int)$existing['request_id'] !== $requestId) {
            $oldReqId = (int)$existing['request_id'];
            $this->db->prepare("DELETE FROM event_request_upvotes WHERE id = :id")->execute(['id' => $existing['upvote_id']]);
            $this->db->prepare("UPDATE event_requests SET upvotes_count = GREATEST(0, upvotes_count - 1) WHERE id = :id")->execute(['id' => $oldReqId]);
            $oldReqCount = (int)$this->db->query("SELECT upvotes_count FROM event_requests WHERE id = {$oldReqId}")->fetchColumn();

            $this->db->prepare("
                INSERT INTO event_request_upvotes (request_id, user_id, ip_address, created_at)
                VALUES (:id, :uid, :ip, NOW())
            ")->execute(['id' => $requestId, 'uid' => $userId, 'ip' => $ip]);

            $this->db->prepare("UPDATE event_requests SET upvotes_count = upvotes_count + 1 WHERE id = :id")->execute(['id' => $requestId]);
            $newCount = (int)$this->db->query("SELECT upvotes_count FROM event_requests WHERE id = {$requestId}")->fetchColumn();

            return [
                'success' => true,
                'action' => 'switched',
                'request_id' => $requestId,
                'new_count' => $newCount,
                'switched_from' => $oldReqId,
                'old_count' => $oldReqCount,
                'message' => 'Vote switched to this track!'
            ];
        }

        // Case 3: Casting a fresh vote
        $this->db->prepare("
            INSERT INTO event_request_upvotes (request_id, user_id, ip_address, created_at)
            VALUES (:id, :uid, :ip, NOW())
        ")->execute(['id' => $requestId, 'uid' => $userId, 'ip' => $ip]);

        $this->db->prepare("UPDATE event_requests SET upvotes_count = upvotes_count + 1 WHERE id = :id")->execute(['id' => $requestId]);
        $newCount = (int)$this->db->query("SELECT upvotes_count FROM event_requests WHERE id = {$requestId}")->fetchColumn();

        return [
            'success' => true,
            'action' => 'voted',
            'request_id' => $requestId,
            'new_count' => $newCount,
            'switched_from' => null,
            'message' => 'Vote recorded!'
        ];
    }

    // ═══════════════════════════════════════════════════════════════════
    // 4. COMMUNITY HYPE WALL & SQUAD FINDER
    // ═══════════════════════════════════════════════════════════════════

    /**
     * Get community posts for an event with category filter and viewer like status.
     */
    public function getCommunityPosts(int $eventId, string $category = 'all', int $limit = 40, ?int $userId = null, ?string $ip = null): array
    {
        $joinCondition = $userId 
            ? "l.post_id = p.id AND l.user_id = :viewer_user_id"
            : "l.post_id = p.id AND l.user_id IS NULL AND l.ip_address = :viewer_ip";

        $query = "
            SELECT p.*,
                   u.name as registered_name,
                   (CASE WHEN l.id IS NOT NULL THEN 1 ELSE 0 END) as user_liked
            FROM event_community_posts p
            LEFT JOIN users u ON p.user_id = u.id
            LEFT JOIN event_community_post_likes l ON {$joinCondition}
            WHERE p.event_id = :event_id AND p.status = 'visible'
        ";
        $params = ['event_id' => $eventId];

        if ($userId) {
            $params['viewer_user_id'] = $userId;
        } else {
            $params['viewer_ip'] = $ip ?: '127.0.0.1';
        }

        if ($category !== 'all' && in_array($category, ['hype', 'squad', 'carpool', 'general'])) {
            $query .= " AND p.category = :category";
            $params['category'] = $category;
        }

        $query .= " ORDER BY p.id DESC LIMIT :limit";

        $stmt = $this->db->prepare($query);
        foreach ($params as $k => $v) {
            $stmt->bindValue(':' . ltrim($k, ':'), $v);
        }
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Create a post on the event's Hype Wall / Squad Finder.
     */
    public function createCommunityPost(int $eventId, array $data, ?int $userId = null): array
    {
        $message = trim($data['message'] ?? '');
        $authorName = trim($data['author_name'] ?? 'Attendee');
        $authorEmail = trim($data['author_email'] ?? '') ?: null;
        $category = in_array($data['category'] ?? '', ['hype', 'squad', 'carpool', 'general']) ? $data['category'] : 'hype';
        $locationArea = trim($data['location_area'] ?? '') ?: null;
        $contactHandle = trim($data['contact_handle'] ?? '') ?: null;

        if (empty($message)) {
            return ['success' => false, 'message' => 'Please write a message to share on the wall.'];
        }

        // Check if user is a verified ticket holder for this event
        $isTicketHolder = 0;
        if ($userId) {
            $tCheck = $this->db->prepare("
                SELECT id FROM tickets 
                WHERE event_id = :event_id AND (user_id = :user_id OR attendee_email = :email) AND status IN ('Paid', 'Comped')
                LIMIT 1
            ");
            $tCheck->execute(['event_id' => $eventId, 'user_id' => $userId, 'email' => $authorEmail ?: '']);
            if ($tCheck->fetch()) {
                $isTicketHolder = 1;
            }
        }

        $stmt = $this->db->prepare("
            INSERT INTO event_community_posts 
            (event_id, user_id, author_name, author_email, category, message, location_area, contact_handle, is_ticket_holder, likes_count, status, created_at)
            VALUES (:event_id, :user_id, :name, :email, :category, :message, :area, :contact, :ticket_holder, 0, 'visible', NOW())
        ");
        $stmt->execute([
            'event_id' => $eventId,
            'user_id' => $userId,
            'name' => $authorName,
            'email' => $authorEmail,
            'category' => $category,
            'message' => $message,
            'area' => $locationArea,
            'contact' => $contactHandle,
            'ticket_holder' => $isTicketHolder
        ]);

        if ($userId) {
            $this->checkAndAwardBadge($userId, 'community_voice', $eventId);
        }

        return ['success' => true, 'message' => 'Posted to the Hype Wall!'];
    }

    /**
     * Toggle like / love emoji on a Hype Wall community post.
     * Allows 1 like per user / IP address:
     * - First click: likes the post (+1).
     * - Second click: unlikes the post (-1).
     */
    public function toggleCommunityPostLike(int $postId, ?int $userId = null, string $ip = '127.0.0.1'): array
    {
        // 1. Verify post exists
        $postStmt = $this->db->prepare("SELECT id, likes_count FROM event_community_posts WHERE id = :id LIMIT 1");
        $postStmt->execute(['id' => $postId]);
        $post = $postStmt->fetch(PDO::FETCH_ASSOC);
        if (!$post) {
            return ['success' => false, 'message' => 'Post not found.'];
        }

        // 2. Check if already liked by this user or IP
        if ($userId) {
            $chkStmt = $this->db->prepare("
                SELECT id FROM event_community_post_likes 
                WHERE post_id = :pid AND user_id = :uid 
                LIMIT 1
            ");
            $chkStmt->execute(['pid' => $postId, 'uid' => $userId]);
        } else {
            $chkStmt = $this->db->prepare("
                SELECT id FROM event_community_post_likes 
                WHERE post_id = :pid AND user_id IS NULL AND ip_address = :ip 
                LIMIT 1
            ");
            $chkStmt->execute(['pid' => $postId, 'ip' => $ip]);
        }
        $existingLike = $chkStmt->fetch(PDO::FETCH_ASSOC);

        // Case A: UNLIKE (toggle off)
        if ($existingLike) {
            $this->db->prepare("DELETE FROM event_community_post_likes WHERE id = :id")
                     ->execute(['id' => $existingLike['id']]);
            $this->db->prepare("UPDATE event_community_posts SET likes_count = GREATEST(0, likes_count - 1) WHERE id = :id")
                     ->execute(['id' => $postId]);
            $newCount = (int)$this->db->query("SELECT likes_count FROM event_community_posts WHERE id = {$postId}")->fetchColumn();

            return [
                'success' => true,
                'action' => 'unliked',
                'liked' => false,
                'likes' => $newCount,
                'message' => 'Unliked.'
            ];
        }

        // Case B: LIKE (toggle on)
        $insStmt = $this->db->prepare("
            INSERT INTO event_community_post_likes (post_id, user_id, ip_address, created_at)
            VALUES (:pid, :uid, :ip, NOW())
        ");
        $insStmt->execute([
            'pid' => $postId,
            'uid' => $userId,
            'ip' => $ip
        ]);

        $this->db->prepare("UPDATE event_community_posts SET likes_count = likes_count + 1 WHERE id = :id")
                 ->execute(['id' => $postId]);
        $newCount = (int)$this->db->query("SELECT likes_count FROM event_community_posts WHERE id = {$postId}")->fetchColumn();

        return [
            'success' => true,
            'action' => 'liked',
            'liked' => true,
            'likes' => $newCount,
            'message' => 'Loved! ❤️'
        ];
    }

    /**
     * Backward-compatible alias for like.
     */
    public function likeCommunityPost(int $postId): int
    {
        $res = $this->toggleCommunityPostLike($postId);
        return (int)($res['likes'] ?? 0);
    }

    // ═══════════════════════════════════════════════════════════════════
    // 5. GAMIFIED EVENT PASSPORT & BADGES
    // ═══════════════════════════════════════════════════════════════════

    /**
     * Get comprehensive passport profile progression: XP, Level, Rank Title, Next Level Progress, and Prestige.
     */
    public function getUserPassportProgress(int $userId): array
    {
        $stmt = $this->db->prepare("
            SELECT id, name, email, passport_xp, passport_level, prestige_tier, prestige_unlocked_at 
            FROM users 
            WHERE id = :id 
            LIMIT 1
        ");
        $stmt->execute(['id' => $userId]);
        $userRow = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$userRow) {
            return [
                'xp' => 0,
                'level' => 1,
                'prestige' => 0,
                'rank_title' => 'Eventlyy Citizen',
                'rank_tier_key' => 'citizen',
                'progress_percent' => 0,
                'next_level_xp' => 500,
                'current_level_min_xp' => 0,
                'can_prestige' => false
            ];
        }

        $xp = (int)($userRow['passport_xp'] ?? 0);
        $prestige = (int)($userRow['prestige_tier'] ?? 0);

        // Tiered XP Curves:
        // Level 1: 0 - 500 XP (Eventlyy Citizen)
        // Level 2: 500 - 1,200 XP (Culture Scout)
        // Level 3: 1,200 - 2,500 XP (Nightlife Explorer)
        // Level 4: 2,500 - 4,500 XP (Festival Headliner)
        // Level 5: 4,500 - 7,500 XP (VIP Cultural Icon)
        // Level 6+: 7,500+ XP (Living Legend)
        $thresholds = [
            1 => ['min' => 0, 'max' => 500, 'title' => 'Eventlyy Citizen', 'tier' => 'citizen'],
            2 => ['min' => 500, 'max' => 1200, 'title' => 'Culture Scout', 'tier' => 'scout'],
            3 => ['min' => 1200, 'max' => 2500, 'title' => 'Nightlife Explorer', 'tier' => 'explorer'],
            4 => ['min' => 2500, 'max' => 4500, 'title' => 'Festival Headliner', 'tier' => 'headliner'],
            5 => ['min' => 4500, 'max' => 7500, 'title' => 'VIP Cultural Icon', 'tier' => 'vip'],
            6 => ['min' => 7500, 'max' => 12000, 'title' => 'Living Legend', 'tier' => 'legend']
        ];

        $currentLevel = 1;
        $rankTitle = 'Eventlyy Citizen';
        $rankTier = 'citizen';
        $minXp = 0;
        $maxXp = 500;

        foreach ($thresholds as $lvl => $info) {
            if ($xp >= $info['min']) {
                $currentLevel = $lvl;
                $rankTitle = $info['title'];
                $rankTier = $info['tier'];
                $minXp = $info['min'];
                $maxXp = $info['max'];
            }
        }

        if ($currentLevel >= 6) {
            // Level 6 Living Legend is max tier before Prestige Rebirth
            $minXp = 7500;
            $maxXp = 7500;
            $levelSpan = 7500;
            $levelXp = $xp;
            $progressPercent = 100;
            $xpNeeded = 0;
            $nextRankTitle = 'Prestige Rebirth';
        } else {
            $nextLevel = $currentLevel + 1;
            $nextRankTitle = $thresholds[$nextLevel]['title'] ?? 'Living Legend';
            $levelSpan = max(1, $maxXp - $minXp);
            $levelXp = max(0, min($levelSpan, $xp - $minXp));
            $progressPercent = min(100, max(0, round(($levelXp / $levelSpan) * 100)));
            $xpNeeded = max(0, $maxXp - $xp);
        }

        // Sync with users table if level changed
        if ((int)$userRow['passport_level'] !== $currentLevel) {
            $up = $this->db->prepare("UPDATE users SET passport_level = :lvl WHERE id = :id");
            $up->execute(['lvl' => $currentLevel, 'id' => $userId]);
        }

        return [
            'xp' => $xp,
            'level' => $currentLevel,
            'prestige' => $prestige,
            'rank_title' => $rankTitle,
            'rank_tier_key' => $rankTier,
            'next_rank_title' => $nextRankTitle,
            'progress_percent' => $progressPercent,
            'level_xp' => $levelXp,
            'level_span' => $levelSpan,
            'xp_needed' => $xpNeeded,
            'next_level_xp' => $maxXp,
            'current_level_min_xp' => $minXp,
            'can_prestige' => ($currentLevel >= 6 && $xp >= 7500)
        ];
    }

    /**
     * Add XP to a user's passport.
     */
    public function awardPassportXp(int $userId, int $xpAmount): void
    {
        if ($xpAmount <= 0) return;
        $stmt = $this->db->prepare("UPDATE users SET passport_xp = passport_xp + :xp WHERE id = :id");
        $stmt->execute(['xp' => $xpAmount, 'id' => $userId]);
    }

    /**
     * Trigger Prestige Rebirth: resets active XP/level cycle, elevates Prestige Tier, and awards massive Event Credits!
     */
    public function activatePrestige(int $userId): array
    {
        $prog = $this->getUserPassportProgress($userId);
        if (!$prog['can_prestige']) {
            return [
                'success' => false,
                'message' => 'You must reach Living Legend rank (Level 6 with 7,500+ XP) to unlock Prestige Rebirth!'
            ];
        }

        $newPrestige = $prog['prestige'] + 1;
        $rewardCredits = 500.00 * $newPrestige; // e.g. ₦500 for Prestige I, ₦1,000 for Prestige II

        // Reset XP back to baseline, increment prestige
        $stmt = $this->db->prepare("
            UPDATE users 
            SET passport_xp = 0, 
                passport_level = 1, 
                prestige_tier = :prestige, 
                prestige_unlocked_at = NOW() 
            WHERE id = :id
        ");
        $stmt->execute(['prestige' => $newPrestige, 'id' => $userId]);

        // Award bonus prestige credits into LayawayCredit model
        $userRow = $this->db->query("SELECT email FROM users WHERE id = {$userId}")->fetch(PDO::FETCH_ASSOC);
        if ($userRow && !empty($userRow['email'])) {
            try {
                $insCredit = $this->db->prepare("
                    INSERT INTO layaway_credits 
                    (user_id, email, original_ticket_id, original_event_id, amount, remaining_amount, status, created_at, updated_at)
                    VALUES (:u_id, :email, :t_id, NULL, :amt, :rem, 'available', NOW(), NOW())
                ");
                $insCredit->execute([
                    'u_id' => $userId,
                    'email' => $userRow['email'],
                    't_id' => 'PRESTIGE-STAR-' . $newPrestige . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 6)),
                    'amt' => $rewardCredits,
                    'rem' => $rewardCredits
                ]);
            } catch (\Throwable $e) {}
        }

        // Award exclusive Prestige Crown badge
        $this->checkAndAwardBadge($userId, 'prestige_ascendant');

        return [
            'success' => true,
            'new_prestige' => $newPrestige,
            'reward_credits' => $rewardCredits,
            'message' => "Congratulations! You have ascended to Prestige Tier {$newPrestige} and earned " . format_price($rewardCredits) . " in loyalty Event Credits!"
        ];
    }

    /**
     * Evaluate and award attendance badges based on verified, physically attended purchased tickets.
     * Strict rules:
     * - Only tickets with status = 'Paid' AND price > 0 (strictly paid purchases; free events/tickets are excluded).
     * - (is_issued = 0 OR is_issued IS NULL) - excludes comped/free issued passes.
     * - checked_in = 1 (physically scanned at venue gate; un-scanned tickets do NOT count).
     * - Event has commenced/happened (date <= NOW()).
     * - Excludes virtual/online live streams.
     * - Distinct paid event thresholds:
     *   - first_drop: >= 3 distinct attended paid events
     *   - nightlife_explorer: >= 6 distinct attended paid events
     *   - festival_legend: >= 12 distinct attended paid events
     *   - vip_enthusiast: >= 1 attended paid VIP/Table tier ticket
     */
    public function evaluateAttendanceBadges(int $userId): array
    {
        $uStmt = $this->db->prepare("SELECT email FROM users WHERE id = :id LIMIT 1");
        $uStmt->execute(['id' => $userId]);
        $userEmail = $uStmt->fetchColumn() ?: '';

        // Count distinct physically attended paid events (not virtual, gate check-in confirmed, price > 0)
        $stmt = $this->db->prepare("
            SELECT COUNT(DISTINCT t.event_id)
            FROM tickets t
            INNER JOIN events e ON t.event_id = e.id
            WHERE (t.user_id = :uid OR (t.attendee_email IS NOT NULL AND LOWER(t.attendee_email) = :email))
              AND t.status = 'Paid'
              AND t.price > 0
              AND (t.is_issued = 0 OR t.is_issued IS NULL)
              AND t.checked_in = 1
              AND e.date <= NOW()
              AND (e.location IS NULL OR (
                  LOWER(e.location) NOT LIKE '%virtual%' 
                  AND LOWER(e.location) NOT LIKE '%online%' 
                  AND LOWER(e.location) NOT LIKE '%stream%' 
                  AND LOWER(e.location) NOT LIKE '%webinar%'
                  AND LOWER(e.location) NOT LIKE '%zoom%'
              ))
        ");
        $stmt->execute([
            'uid' => $userId,
            'email' => strtolower(trim($userEmail))
        ]);
        $attendedCount = (int)$stmt->fetchColumn();

        // Count VIP/Table tier attended paid tickets (price > 0, checked_in = 1)
        $vipStmt = $this->db->prepare("
            SELECT COUNT(*)
            FROM tickets t
            INNER JOIN events e ON t.event_id = e.id
            INNER JOIN ticket_types tt ON t.ticket_type_id = tt.id
            WHERE (t.user_id = :uid OR (t.attendee_email IS NOT NULL AND LOWER(t.attendee_email) = :email))
              AND t.status = 'Paid'
              AND t.price > 0
              AND (t.is_issued = 0 OR t.is_issued IS NULL)
              AND t.checked_in = 1
              AND (
                  LOWER(tt.name) LIKE '%vip%' 
                  OR LOWER(tt.name) LIKE '%table%' 
                  OR LOWER(tt.name) LIKE '%vvip%'
                  OR LOWER(tt.name) LIKE '%backstage%'
              )
        ");
        $vipStmt->execute([
            'uid' => $userId,
            'email' => strtolower(trim($userEmail))
        ]);
        $vipAttendedCount = (int)$vipStmt->fetchColumn();

        $awarded = [];
        if ($attendedCount >= 3) {
            if ($this->checkAndAwardBadge($userId, 'first_drop')) {
                $awarded[] = 'first_drop';
            }
        }
        if ($attendedCount >= 6) {
            if ($this->checkAndAwardBadge($userId, 'nightlife_explorer')) {
                $awarded[] = 'nightlife_explorer';
            }
        }
        if ($attendedCount >= 12) {
            if ($this->checkAndAwardBadge($userId, 'festival_legend')) {
                $awarded[] = 'festival_legend';
            }
        }
        if ($vipAttendedCount >= 1) {
            if ($this->checkAndAwardBadge($userId, 'vip_enthusiast')) {
                $awarded[] = 'vip_enthusiast';
            }
        }

        return [
            'attended_count' => $attendedCount,
            'vip_attended_count' => $vipAttendedCount,
            'newly_awarded' => $awarded
        ];
    }

    /**
     * Get all unlocked badges for a user.
     */
    public function getUserBadges(int $userId): array
    {
        $stmt = $this->db->prepare("
            SELECT b.*, e.name as event_name, e.slug as event_slug
            FROM user_passport_badges b
            LEFT JOIN events e ON b.event_id = e.id
            WHERE b.user_id = :user_id
            ORDER BY b.id DESC
        ");
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Check and award a badge if not already earned, with optional Event Credits reward and XP!
     */
    public function checkAndAwardBadge(int $userId, string $badgeKey, ?int $eventId = null): bool
    {
        $badgeDefinitions = [
            'first_drop' => [
                'title' => 'First Drop Pioneer',
                'icon' => '🎟️',
                'desc' => 'Purchased paid ticket & attended at least 3 physical events (scanned at gate).',
                'credits' => 50.00,
                'xp' => 150,
                'category' => 'ticketing'
            ],
            'nightlife_explorer' => [
                'title' => 'Nightlife Explorer',
                'icon' => '🌙',
                'desc' => 'Purchased paid tickets & attended 6 or more physical events at the venue.',
                'credits' => 100.00,
                'xp' => 350,
                'category' => 'ticketing'
            ],
            'festival_legend' => [
                'title' => 'Festival Veteran',
                'icon' => '👑',
                'desc' => 'Purchased paid tickets & attended 12 or more physical events at the venue.',
                'credits' => 150.00,
                'xp' => 600,
                'category' => 'ticketing'
            ],
            'first_vote' => [
                'title' => 'Culture Voter',
                'icon' => '🗳️',
                'desc' => 'Cast votes across 3 or more event polls or award contests.',
                'credits' => 25.00,
                'xp' => 120,
                'category' => 'community'
            ],
            'poll_regular' => [
                'title' => 'Ballot Champion',
                'icon' => '🏆',
                'desc' => 'Cast 8 or more votes in community award polls.',
                'credits' => 50.00,
                'xp' => 250,
                'category' => 'community'
            ],
            'trivia_master' => [
                'title' => 'Trivia Mastermind',
                'icon' => '🧠',
                'desc' => 'Scored a winning round on an event trivia game.',
                'credits' => 50.00,
                'xp' => 200,
                'category' => 'knowledge'
            ],
            'trivia_genius' => [
                'title' => 'Speed Genius',
                'icon' => '⚡',
                'desc' => 'Achieved winning streaks in 5 or more trivia challenges.',
                'credits' => 75.00,
                'xp' => 350,
                'category' => 'knowledge'
            ],
            'song_suggester' => [
                'title' => 'Setlist Curator',
                'icon' => '🎵',
                'desc' => 'Suggested songs or speaker Q&A topics that received crowd upvotes.',
                'credits' => 25.00,
                'xp' => 120,
                'category' => 'stage'
            ],
            'community_voice' => [
                'title' => 'Hype Starter',
                'icon' => '📣',
                'desc' => 'Started verified discussion threads on event hype walls.',
                'credits' => 25.00,
                'xp' => 120,
                'category' => 'social'
            ],
            'social_butterfly' => [
                'title' => 'Hype Conductor',
                'icon' => '🔥',
                'desc' => 'Active community leader: posted 5 or more discussion or carpool threads.',
                'credits' => 50.00,
                'xp' => 300,
                'category' => 'social'
            ],
            'power_voter' => [
                'title' => 'Power Voter',
                'icon' => '⚡',
                'desc' => 'Purchased a Power Vote pack to boost your nominee.',
                'credits' => 50.00,
                'xp' => 250,
                'category' => 'supporter'
            ],
            'vip_enthusiast' => [
                'title' => 'VIP Connoisseur',
                'icon' => '💎',
                'desc' => 'Purchased and attended an event with a paid VIP or Table tier pass.',
                'credits' => 75.00,
                'xp' => 400,
                'category' => 'ticketing'
            ],
            'prestige_ascendant' => [
                'title' => 'Ascendant Star',
                'icon' => '✨',
                'desc' => 'Conquered Level 6 Living Legend and completed Prestige Rebirth.',
                'credits' => 250.00,
                'xp' => 800,
                'category' => 'prestige'
            ]
        ];

        if (!isset($badgeDefinitions[$badgeKey])) return false;
        $def = $badgeDefinitions[$badgeKey];

        // Fetch user's current prestige tier cycle
        $userRow = $this->db->query("SELECT prestige_tier, email FROM users WHERE id = {$userId}")->fetch(PDO::FETCH_ASSOC);
        $prestigeCycle = (int)($userRow['prestige_tier'] ?? 0);

        // Check if already earned in this prestige cycle
        $chk = $this->db->prepare("SELECT id FROM user_passport_badges WHERE user_id = :u_id AND badge_key = :key AND prestige_cycle = :cycle LIMIT 1");
        $chk->execute(['u_id' => $userId, 'key' => $badgeKey, 'cycle' => $prestigeCycle]);
        if ($chk->fetch()) return false;

        // Insert badge
        $ins = $this->db->prepare("
            INSERT INTO user_passport_badges 
            (user_id, badge_key, badge_title, badge_icon, badge_desc, event_id, prestige_cycle, reward_credits, created_at)
            VALUES (:u_id, :key, :title, :icon, :desc, :e_id, :cycle, :credits, NOW())
        ");
        $ins->execute([
            'u_id' => $userId,
            'key' => $badgeKey,
            'title' => $def['title'],
            'icon' => $def['icon'],
            'desc' => $def['desc'],
            'e_id' => $eventId,
            'cycle' => $prestigeCycle,
            'credits' => $def['credits']
        ]);

        // Award passport XP
        $xpToAdd = (int)($def['xp'] ?? 200);
        $this->awardPassportXp($userId, $xpToAdd);

        // If credits awarded and user exists, issue Event Credit into LayawayCredit model
        if ($def['credits'] > 0) {
            if ($userRow && !empty($userRow['email'])) {
                try {
                    $stmt = $this->db->prepare("
                        INSERT INTO layaway_credits 
                        (user_id, email, original_ticket_id, original_event_id, amount, remaining_amount, status, created_at, updated_at)
                        VALUES (:u_id, :email, :ticket_id, :e_id, :amt, :rem, 'available', NOW(), NOW())
                    ");
                    $stmt->execute([
                        'u_id' => $userId,
                        'email' => $userRow['email'],
                        'ticket_id' => 'REWARD-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 8)),
                        'e_id' => $eventId,
                        'amt' => $def['credits'],
                        'rem' => $def['credits'],
                    ]);
                } catch (\Throwable $e) {
                    // Non-blocking
                }
            }
        }

        return true;
    }

    /**
     * Get aggregate engagement summary metrics across all 4 activity pillars for an event.
     * Used for dynamic live badge indicators on the public event page.
     */
    public function getEventEngagementMetrics(int $eventId): array
    {
        $metrics = [
            'polls' => [
                'total_polls' => 0,
                'total_votes' => 0,
                'has_active' => false,
                'badge' => null
            ],
            'trivia' => [
                'has_trivia' => false,
                'plays_count' => 0,
                'badge' => null
            ],
            'requests' => [
                'total_requests' => 0,
                'total_upvotes' => 0,
                'badge' => null
            ],
            'wall' => [
                'total_posts' => 0,
                'total_likes' => 0,
                'badge' => null
            ],
            'total_interactions' => 0
        ];

        try {
            // 1. Polls engagement
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as poll_count,
                       COALESCE(SUM(total_votes), 0) as vote_sum,
                       COALESCE(SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END), 0) as active_count
                FROM event_polls
                WHERE event_id = :event_id
            ");
            $stmt->execute(['event_id' => $eventId]);
            $pollData = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($pollData && (int)$pollData['poll_count'] > 0) {
                $votes = (int)$pollData['vote_sum'];
                $pollCount = (int)$pollData['poll_count'];
                $metrics['polls']['total_polls'] = $pollCount;
                $metrics['polls']['total_votes'] = $votes;
                $metrics['polls']['has_active'] = (int)$pollData['active_count'] > 0;

                if ($votes > 0) {
                    $metrics['polls']['badge'] = $votes . ($votes === 1 ? ' vote cast' : ' votes cast');
                } else {
                    $metrics['polls']['badge'] = $pollCount . ($pollCount === 1 ? ' active poll' : ' active polls');
                }
            }

            // 2. Trivia engagement
            $stmt = $this->db->prepare("
                SELECT id, plays_count, status
                FROM event_trivia
                WHERE event_id = :event_id
                LIMIT 1
            ");
            $stmt->execute(['event_id' => $eventId]);
            $triviaData = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($triviaData) {
                $plays = (int)$triviaData['plays_count'];
                $metrics['trivia']['has_trivia'] = true;
                $metrics['trivia']['plays_count'] = $plays;

                if ($plays > 0) {
                    $metrics['trivia']['badge'] = $plays . ($plays === 1 ? ' player competed' : ' players competed');
                } else {
                    $metrics['trivia']['badge'] = 'Quiz challenge live';
                }
            }

            // 3. Requests & Q&A engagement
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as req_count,
                       COALESCE(SUM(upvotes_count), 0) as upvote_sum
                FROM event_requests
                WHERE event_id = :event_id AND status != 'hidden'
            ");
            $stmt->execute(['event_id' => $eventId]);
            $reqData = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($reqData && (int)$reqData['req_count'] > 0) {
                $reqCount = (int)$reqData['req_count'];
                $upvotes = (int)$reqData['upvote_sum'];
                $metrics['requests']['total_requests'] = $reqCount;
                $metrics['requests']['total_upvotes'] = $upvotes;

                if ($upvotes > $reqCount) {
                    $metrics['requests']['badge'] = $upvotes . ' upvotes • ' . $reqCount . ($reqCount === 1 ? ' suggestion' : ' suggestions');
                } else {
                    $metrics['requests']['badge'] = $reqCount . ($reqCount === 1 ? ' song suggested' : ' songs suggested');
                }
            }

            // 4. Community Hype Wall & Discussions engagement
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as post_count,
                       COALESCE(SUM(likes_count), 0) as like_sum
                FROM event_community_posts
                WHERE event_id = :event_id AND status = 'visible'
            ");
            $stmt->execute(['event_id' => $eventId]);
            $wallData = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($wallData && (int)$wallData['post_count'] > 0) {
                $postCount = (int)$wallData['post_count'];
                $likes = (int)$wallData['like_sum'];
                $metrics['wall']['total_posts'] = $postCount;
                $metrics['wall']['total_likes'] = $likes;

                if ($likes > 0) {
                    $metrics['wall']['badge'] = $postCount . ($postCount === 1 ? ' post' : ' posts') . ' • ' . $likes . ($likes === 1 ? ' hype reaction' : ' hype reactions');
                } else {
                    $metrics['wall']['badge'] = $postCount . ($postCount === 1 ? ' discussion topic' : ' discussion topics');
                }
            }

            // Total community interactions combined
            $metrics['total_interactions'] =
                $metrics['polls']['total_votes'] +
                $metrics['trivia']['plays_count'] +
                $metrics['requests']['total_requests'] +
                $metrics['requests']['total_upvotes'] +
                $metrics['wall']['total_posts'] +
                $metrics['wall']['total_likes'];

        } catch (\Throwable $e) {
            // Failsafe gracefully returns default array
        }

        return $metrics;
    }
}
