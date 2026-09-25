<?php

/**
 * AdminPayoutController
 *
 * This controller handles admin payout-related routes.
 *
 * @package Evently
 * @subpackage Controllers/AdminPayout
 * @author Your Name <Celio Natti>
 * @license MIT License
 * @version 1.0.0
 */

namespace App\Controllers\Admin;

use Ace\Application;
use Ace\Controller;
use Ace\Request;
use App\Middlewares\AdminMiddleware;
use App\Services\AdminPayoutService;
use App\Services\AutoPayoutService;
use App\Actions\ProcessPayoutAction;

class AdminPayoutController extends Controller
{
    protected AdminPayoutService $payoutService;
    protected AutoPayoutService $autoPayoutService;
    protected ProcessPayoutAction $processPayoutAction;

    /**
     * AdminPayoutController constructor.
     * Registers the AdminMiddleware and initializes payout service and action.
     */
    public function __construct()
    {
        $this->registerMiddleware(new AdminMiddleware());
        $this->payoutService = new AdminPayoutService();
        $this->autoPayoutService = new AutoPayoutService();
        $this->processPayoutAction = new ProcessPayoutAction();
    }

    public function payouts(Request $request)
    {
        // 0. Automatically process event completion and unrequested payout requests
        $this->autoPayoutService->processAutoPayouts();

        // Query stats via AdminPayoutService
        $stats = $this->payoutService->getPayoutStats();

        // Get filter inputs
        $search = $request->getBody()['q'] ?? '';
        $status = $request->getBody()['status'] ?? '';
        $type = $request->getBody()['type'] ?? '';
        $page = (int)($request->getBody()['page'] ?? 1);
        if ($page < 1) $page = 1;
        $limit = 10;

        // Fetch payouts queue via AdminPayoutService
        $paginated = $this->payoutService->getFilteredPayouts($search, $status, $type, $page, $limit);
        $payouts = $paginated['items'];
        $totalPages = $paginated['totalPages'];

        return $this->render('admin/payouts', array_merge($stats, [
            'payouts' => $payouts,
            'search' => $search,
            'status' => $status,
            'type' => $type,
            'page' => $page,
            'totalPages' => $totalPages
        ]));
    }

    public function runAutoPayoutsCron(Request $request)
    {
        $result = $this->autoPayoutService->processAutoPayouts();
        return Application::$app->response->json([
            'status' => 'success',
            'message' => 'Auto-payout job executed successfully',
            'data' => $result
        ]);
    }

    public function processAction(Request $request)
    {
        $data = $request->getBody();
        $action = $data['action'] ?? '';
        $payoutId = $data['payout_id'] ?? '';

        if (!$payoutId) {
            Application::$app->session->setFlash('error', 'Payout ID is missing.');
            return redirect('/admin/payouts');
        }

        $mode = $data['mode'] ?? 'paystack';

        // Execute action via ProcessPayoutAction
        $outcome = $this->processPayoutAction->execute($action, $payoutId, $mode);
        
        // Parse outcome e.g. "success:Payout disbursed successfully!"
        $parts = explode(':', (string)$outcome, 2);
        $flashType = count($parts) === 2 ? $parts[0] : 'info';
        $message = count($parts) === 2 ? $parts[1] : $outcome;
        Application::$app->session->setFlash($flashType, $message);

        return redirect('/admin/payouts');
    }

    /**
     * Export matching payouts as CSV.
     */
    public function exportCsv(Request $request)
    {
        $search = $request->getBody()['q'] ?? '';
        $status = $request->getBody()['status'] ?? '';
        $type = $request->getBody()['type'] ?? '';

        $payouts = $this->payoutService->getAllPayoutsForExport($search, $status, $type);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="evently_payouts_export_' . date('Y-m-d_H-i') . '.csv"');

        $output = fopen('php://output', 'w');
        // Write UTF-8 BOM for Microsoft Excel compatibility
        fputs($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

        fputcsv($output, [
            'Payout ID',
            'Host Name',
            'Host Email',
            'Bank Name',
            'Account Number',
            'Routing / Sort Code',
            'Payout Type',
            'Event Name',
            'Amount',
            'Status',
            'Date Created'
        ]);

        foreach ($payouts as $p) {
            $isEscrow = (($p['type'] ?? 'completed') === 'escrow');
            $typeLabel = $isEscrow ? 'Escrow (Early Release)' : 'Standard Payout';
            $statusLabel = match($p['status']) {
                'processed', 'completed' => 'Completed / Disbursed',
                'failed' => 'Flagged / On Hold',
                default => 'Pending / Ready to Pay'
            };

            fputcsv($output, [
                '#' . $p['id'],
                $p['host_name'] ?? 'N/A',
                $p['host_email'] ?? 'N/A',
                $p['payout_bank'] ?: 'Not Configured',
                $p['payout_account'] ? "'" . $p['payout_account'] : 'N/A',
                $p['payout_routing'] ?: 'N/A',
                $typeLabel,
                $p['event_name'] ?? 'N/A',
                format_price($p['amount']),
                $statusLabel,
                date('Y-m-d H:i:s', strtotime($p['created_at']))
            ]);
        }

        fclose($output);
        exit;
    }
}