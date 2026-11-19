<?php

namespace App\Controllers;

use Ace\Application;
use Ace\Controller;
use Ace\Request;
use Ace\Middlewares\AuthMiddleware;
use App\Services\LayawayService;

class LayawayController extends Controller
{
    protected LayawayService $layawayService;

    public function __construct()
    {
        // Public endpoints exempt from AuthMiddleware: callback, cron, and public credit checking
        $action = Application::$app->request->getPath();
        $publicPaths = ['/layaway/callback', '/cron/expire-layaways', '/layaway/credits', '/layaway/check-credits'];
        $isPublic = false;
        foreach ($publicPaths as $p) {
            if (str_contains($action, $p)) {
                $isPublic = true;
                break;
            }
        }
        if (!$isPublic) {
            $this->registerMiddleware(new AuthMiddleware());
        }
        $this->layawayService = new LayawayService();
    }

    /**
     * Attendee Layaway Dashboard
     * Displays all active installment payment plans and credit balance.
     */
    public function dashboard(Request $request): string
    {
        $user = Application::$app->user;
        if (!$user) {
            return redirect('/login') ?: '';
        }

        $layaways = $this->layawayService->getUserLayaways((int)$user->id, $user->email);
        $credits = $this->layawayService->getUserCredits($user->email, (int)$user->id);

        return $this->render('layaway/dashboard', [
            'title' => 'My Layaway Plans & Credits - ' . site_setting('site_title', 'Eventlyy'),
            'user' => $user,
            'layaways' => $layaways,
            'credits' => $credits
        ]);
    }

    /**
     * Public Event Credits Hub & Lookup (accessible by guests and registered users)
     */
    public function publicCreditsView(Request $request): string
    {
        $user = Application::$app->user;
        $email = $user ? $user->email : trim((string)($request->getBody()['email'] ?? ($_GET['email'] ?? '')));
        
        $credits = [];
        $totalBalance = 0.00;

        if (!empty($email)) {
            $userId = $user ? (int)$user->id : null;
            $credits = \App\Models\LayawayCredit::findAvailableByEmail($email, $userId);
            $totalBalance = \App\Models\LayawayCredit::getTotalAvailable($email, $userId);
        }

        return $this->render('layaway/credits', [
            'title' => 'Event Credits Hub - ' . site_setting('site_title', 'Eventlyy'),
            'user' => $user,
            'email' => $email,
            'credits' => $credits,
            'totalBalance' => $totalBalance
        ]);
    }

    /**
     * AJAX endpoint to check available credits by email
     */
    public function checkCreditsAjax(Request $request)
    {
        header('Content-Type: application/json');
        $data = $request->getBody();
        $email = trim((string)($data['email'] ?? ''));

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode([
                'success' => false,
                'message' => 'Please provide a valid email address.'
            ]);
            exit;
        }

        $user = Application::$app->user;
        $userId = ($user && strtolower($user->email) === strtolower($email)) ? (int)$user->id : null;
        
        $credits = \App\Models\LayawayCredit::findAvailableByEmail($email, $userId);
        $total = \App\Models\LayawayCredit::getTotalAvailable($email, $userId);

        echo json_encode([
            'success' => true,
            'email' => $email,
            'total_available' => $total,
            'formatted_total' => format_price($total),
            'count' => count($credits),
            'credits' => array_map(function($c) {
                return [
                    'id' => (int)$c['id'],
                    'remaining_amount' => (float)$c['remaining_amount'],
                    'formatted_amount' => format_price((float)$c['remaining_amount']),
                    'original_event_name' => $c['original_event_name'] ?? 'Concluded Event',
                    'expires_at' => !empty($c['expires_at']) ? date('M d, Y', strtotime($c['expires_at'])) : null
                ];
            }, $credits)
        ]);
        exit;
    }

    /**
     * Submit an installment payment toward a layaway ticket.
     */
    public function makePayment(Request $request)
    {
        $data = $request->getBody();
        $ticketId = trim((string)($data['ticket_id'] ?? ''));
        $amount = (float)($data['amount'] ?? 0);

        $user = Application::$app->user;
        if (!$user) {
            Application::$app->session->setFlash('error', 'Please log in to make a payment.');
            return redirect('/login');
        }

        if (empty($ticketId) || $amount <= 0) {
            Application::$app->session->setFlash('error', 'Please specify a valid payment amount.');
            return redirect('/layaway');
        }

        $res = $this->layawayService->initializeInstallmentPayment($ticketId, $amount, $user->email);

        if (!$res['success']) {
            Application::$app->session->setFlash('error', $res['message'] ?? 'Could not initialize payment.');
            return redirect('/layaway');
        }

        // Redirect to Paystack checkout
        return redirect($res['authorization_url']);
    }

    /**
     * Paystack Redirect Callback for Layaway Installment Payment.
     */
    public function paymentCallback(Request $request)
    {
        $data = $request->getBody();
        $reference = trim((string)($data['reference'] ?? ($data['trxref'] ?? '')));

        if (empty($reference)) {
            Application::$app->session->setFlash('error', 'Missing transaction reference from payment provider.');
            return redirect('/layaway');
        }

        $result = $this->layawayService->finalizeInstallmentPayment($reference);

        if ($result['success']) {
            if (!empty($result['is_fully_paid'])) {
                Application::$app->session->setFlash('success', '🎉 Payment complete! Your ticket has been issued and is now active for admission.');
                return redirect('/profile?tab=tickets');
            }

            Application::$app->session->setFlash('success', $result['message'] ?? 'Installment payment verified successfully!');
            return redirect('/layaway');
        }

        Application::$app->session->setFlash('error', $result['message'] ?? 'Payment verification failed.');
        return redirect('/layaway');
    }

    /**
     * Apply credit toward a layaway ticket.
     */
    public function applyCredit(Request $request)
    {
        $data = $request->getBody();
        $creditId = (int)($data['credit_id'] ?? 0);
        $ticketId = trim((string)($data['ticket_id'] ?? ''));
        $user = Application::$app->user;

        if (!$user) {
            return redirect('/login');
        }

        if ($creditId <= 0 || empty($ticketId)) {
            Application::$app->session->setFlash('error', 'Invalid credit or ticket selection.');
            return redirect('/layaway');
        }

        $progress = $this->layawayService->getLayawayProgress($ticketId);
        $needed = $progress['remaining'];

        if ($needed <= 0) {
            Application::$app->session->setFlash('error', 'Ticket is already fully paid.');
            return redirect('/layaway');
        }

        $res = $this->layawayService->applyCredit($creditId, $user->email, $needed);

        if (!$res['success']) {
            Application::$app->session->setFlash('error', $res['message'] ?? 'Failed to apply credit.');
            return redirect('/layaway');
        }

        // Record completed payment row
        $db = Application::$app->db->pdo;
        $ref = 'CREDIT-' . substr(bin2hex(random_bytes(4)), 0, 8);
        $payStmt = $db->prepare("
            INSERT INTO layaway_payments (layaway_ticket_id, event_id, user_id, payer_email, amount, payment_method, reference, status, gateway_response, created_at)
            SELECT :ticket_id, event_id, :user_id, :email, :amt, 'event_credit', :ref, 'completed', :resp, NOW()
            FROM tickets WHERE id = :t_id LIMIT 1
        ");
        $payStmt->execute([
            'ticket_id' => $ticketId,
            'user_id' => $user->id,
            'email' => $user->email,
            'amt' => $res['deducted'],
            'ref' => $ref,
            'resp' => json_encode(['note' => 'Applied layaway credit #' . $creditId]),
            't_id' => $ticketId
        ]);

        // Check if ticket is now fully paid
        $newProgress = $this->layawayService->getLayawayProgress($ticketId);
        if ($newProgress['is_complete']) {
            $this->layawayService->markTicketAsFullyPaid($ticketId);
            Application::$app->session->setFlash('success', 'Credit of ' . format_price($res['deducted']) . ' applied! Ticket is now 100% paid and active!');
            return redirect('/profile?tab=tickets');
        }

        Application::$app->session->setFlash('success', 'Credit of ' . format_price($res['deducted']) . ' applied successfully! Remaining balance: ' . format_price($newProgress['remaining']));
        return redirect('/layaway');
    }

    /**
     * Cron endpoint to expire unpaid layaways whose event has commenced.
     * Converts all funds paid into 1-year event credits.
     */
    public function expireCron(Request $request)
    {
        $summary = $this->layawayService->expireUnpaidLayaways();

        header('Content-Type: application/json');
        echo json_encode([
            'status' => true,
            'summary' => $summary,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
        exit;
    }
}
