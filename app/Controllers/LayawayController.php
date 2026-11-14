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
        // Public endpoints exempt from AuthMiddleware: callback and cron
        $action = Application::$app->request->getPath();
        if (!str_contains($action, '/layaway/callback') && !str_contains($action, '/cron/expire-layaways')) {
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
