<?php

namespace App\Actions;

use Ace\Application;

class SaveHostSettingsAction
{
    /**
     * Update user details and host profile info.
     */
    public function execute(array $data, mixed $user): bool
    {
        $db = Application::$app->db->pdo;

        try {
            $db->beginTransaction();

            // 1. Update users table (name, phone)
            $stmt = $db->prepare("UPDATE users SET name = :name, phone = :phone WHERE id = :id");
            $stmt->execute([
                'name' => $data['host_name'] ?? $user->name,
                'phone' => $data['phone_number'] ?? ($user->phone ?? ''),
                'id' => $user->id
            ]);

            // 2. Update host_profiles table using VALUES() syntax to avoid PDO duplicate parameter binding error
            $stmt = $db->prepare("
                INSERT INTO host_profiles (user_id, bio, payout_bank, payout_account, payout_routing)
                VALUES (:user_id, :bio, :payout_bank, :payout_account, :payout_routing)
                ON DUPLICATE KEY UPDATE 
                    bio = VALUES(bio),
                    payout_bank = VALUES(payout_bank),
                    payout_account = VALUES(payout_account),
                    payout_routing = VALUES(payout_routing)
            ");
            $payoutBank = trim($data['payout_bank'] ?? '');
            $payoutAccount = trim($data['payout_account'] ?? '');
            $payoutRouting = trim($data['payout_routing'] ?? '');

            if (!empty($payoutBank) && empty($payoutRouting)) {
                $paystack = new \Ace\PaystackService();
                $payoutRouting = $paystack->resolveBankCode($payoutBank);
            }

            $stmt->execute([
                'user_id' => $user->id,
                'bio' => $data['bio'] ?? '',
                'payout_bank' => $payoutBank,
                'payout_account' => $payoutAccount,
                'payout_routing' => $payoutRouting
            ]);

            $db->commit();
            return true;
        } catch (\Exception $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            return false;
        }
    }
}
