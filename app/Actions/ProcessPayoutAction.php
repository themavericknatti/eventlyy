<?php

namespace App\Actions;

use Ace\Application;
use Ace\PaystackService;
use Exception;

class ProcessPayoutAction
{
    /**
     * Process, flag, or unflag payout records.
     * 
     * @param string $action Action name ('process_payout', 'flag_account', 'unflag_account')
     * @param int|string $payoutId Payout record ID
     * @param string $mode Payout mode ('paystack' or 'manual')
     * @return string Flash string in format 'type:message'
     */
    public function execute(string $action, int|string $payoutId, string $mode = 'paystack'): string
    {
        if (!$payoutId) {
            return 'error:Payout ID is missing.';
        }

        $db = Application::$app->db->pdo;

        if ($action === 'process_payout') {
            // 1. Fetch payout record and host bank profile
            $stmt = $db->prepare("
                SELECT p.*, u.name as host_name, u.email as host_email,
                       hp.payout_bank, hp.payout_account, hp.payout_routing
                FROM payouts p
                INNER JOIN users u ON p.organizer_id = u.id
                LEFT JOIN host_profiles hp ON u.id = hp.user_id
                WHERE p.id = :id
                LIMIT 1
            ");
            $stmt->execute(['id' => $payoutId]);
            $payout = $stmt->fetch();

            if (!$payout) {
                return 'error:Payout record not found.';
            }

            $amount = (float)$payout['amount'];

            // 2. If Manual Disbursement Mode selected by Admin
            if ($mode === 'manual') {
                $stmt = $db->prepare("UPDATE payouts SET status = 'processed' WHERE id = :id");
                $stmt->execute(['id' => $payoutId]);

                // Record payout transaction
                $this->recordPayoutTransaction($payout, 'manual', 'MANUAL_' . uniqid());

                return 'success:Payout of ' . format_price($amount) . ' marked as manually disbursed!';
            }

            // 3. Check bank details
            $bankName = trim($payout['payout_bank'] ?? '');
            $accountNumber = trim($payout['payout_account'] ?? '');

            if (empty($bankName) || empty($accountNumber)) {
                return 'error:Host payout bank details are incomplete (Bank name or account number missing).';
            }

            // 4. Flutterwave Direct Transfer Mode
            if ($mode === 'flutterwave') {
                try {
                    $flw = new \Ace\FlutterwaveService();

                    if (!$flw->isConfigured()) {
                        $stmt = $db->prepare("UPDATE payouts SET status = 'processed' WHERE id = :id");
                        $stmt->execute(['id' => $payoutId]);
                        $this->recordPayoutTransaction($payout, 'manual', 'MANUAL_' . uniqid());
                        return 'warning:Flutterwave Secret Key is not set in .env. Payout marked as manually processed.';
                    }

                    $bankCode = $flw->resolveBankCode($bankName);
                    $transferData = $flw->initiateTransfer(
                        $amount,
                        $accountNumber,
                        $bankCode,
                        "Payout #" . $payoutId . " to " . $payout['host_name']
                    );

                    $reference = $transferData['reference'] ?? ('FLW_TRF_' . uniqid());

                    $stmt = $db->prepare("UPDATE payouts SET status = 'processed' WHERE id = :id");
                    $stmt->execute(['id' => $payoutId]);

                    $this->recordPayoutTransaction($payout, 'flutterwave', $reference);

                    return 'success:Flutterwave Direct Transfer initiated successfully! Ref: ' . $reference;

                } catch (\Throwable $e) {
                    error_log("Flutterwave Payout Error: " . $e->getMessage());
                    return 'error:Flutterwave Transfer Failed: ' . $e->getMessage() . ' You can retry or choose Manual Disburse.';
                }
            }

            // 5. Paystack Direct Transfer Mode (Default)
            try {
                $paystack = new PaystackService();

                if (!$paystack->isConfigured()) {
                    // Fallback to manual if Paystack key is missing
                    $stmt = $db->prepare("UPDATE payouts SET status = 'processed' WHERE id = :id");
                    $stmt->execute(['id' => $payoutId]);
                    $this->recordPayoutTransaction($payout, 'manual', 'MANUAL_' . uniqid());
                    return 'warning:Paystack Secret Key is not set in .env. Payout marked as manually processed.';
                }

                // Step A: Resolve Bank Code
                $bankCode = $paystack->resolveBankCode($bankName);

                // Step B: Create Transfer Recipient
                $recipientData = $paystack->createTransferRecipient(
                    $payout['host_name'],
                    $accountNumber,
                    $bankCode
                );

                $recipientCode = $recipientData['recipient_code'] ?? '';
                if (empty($recipientCode)) {
                    throw new Exception("Could not generate Paystack transfer recipient code.");
                }

                // Step C: Initiate Transfer
                $transferData = $paystack->initiateTransfer(
                    $amount,
                    $recipientCode,
                    "Payout #" . $payoutId . " to " . $payout['host_name']
                );

                $reference = $transferData['reference'] ?? ('TRF_' . uniqid());

                // Update payout status
                $stmt = $db->prepare("UPDATE payouts SET status = 'processed' WHERE id = :id");
                $stmt->execute(['id' => $payoutId]);

                // Record transaction
                $this->recordPayoutTransaction($payout, 'paystack', $reference);

                return 'success:Paystack Direct Transfer initiated successfully! Ref: ' . $reference;

            } catch (\Throwable $e) {
                error_log("Paystack Payout Error: " . $e->getMessage());
                $errMsg = $e->getMessage();
                if (str_contains(strtolower($errMsg), 'not enabled') || str_contains(strtolower($errMsg), 'transfer')) {
                    return 'error:Paystack Notice: ' . $errMsg . '. To use Paystack Direct Transfers, log in to your Paystack Dashboard -> click "Transfers" -> click "Enable Transfers". For testing, you can also choose "Manual Mark as Paid".';
                }
                return 'error:Paystack Transfer Failed: ' . $errMsg . ' (You can also choose "Manual Mark as Paid").';
            }

        } elseif ($action === 'flag_account') {
            $stmt = $db->prepare("UPDATE payouts SET status = 'failed' WHERE id = :id");
            $stmt->execute(['id' => $payoutId]);
            return 'warning:Payout account flagged and frozen.';

        } elseif ($action === 'unflag_account') {
            $stmt = $db->prepare("UPDATE payouts SET status = 'pending' WHERE id = :id");
            $stmt->execute(['id' => $payoutId]);
            return 'success:Hold resolved. Account restored to Ready.';
        }

        return 'error:Invalid action.';
    }

    /**
     * Record a transaction log entry for the payout.
     */
    private function recordPayoutTransaction(array $payout, string $method, string $reference): void
    {
        try {
            $db = Application::$app->db->pdo;
            $stmt = $db->prepare("
                INSERT INTO transactions 
                (reference, event_id, user_id, payer_name, payer_email, type, payment_method, subtotal_amount, fee_amount, extra_charge_amount, total_amount, status)
                VALUES 
                (:ref, :event_id, :user_id, :name, :email, 'payout', :method, :subtotal_amount, 0, 0, :total_amount, 'completed')
            ");
            $stmt->execute([
                'ref' => $reference,
                'event_id' => $payout['event_id'],
                'user_id' => $payout['organizer_id'],
                'name' => $payout['host_name'] ?? 'Host',
                'email' => $payout['host_email'] ?? 'host@evently.com',
                'method' => $method,
                'subtotal_amount' => $payout['amount'],
                'total_amount' => $payout['amount'],
            ]);
        } catch (\Throwable $e) {
            // Ignore transaction audit logging error to avoid blocking payout status update
            error_log("Failed to log payout transaction: " . $e->getMessage());
        }
    }
}
