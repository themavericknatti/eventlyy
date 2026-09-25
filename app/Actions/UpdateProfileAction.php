<?php

/**
 * UpdateProfileAction
 *
 * Handles updating host profile information (personal info + payout details).
 *
 * @package Evently
 * @subpackage Actions
 * @author Celio Natti
 * @license MIT License
 * @version 1.0.0
 */

namespace App\Actions;

use Ace\Application;

class UpdateProfileAction
{
    /**
     * Execute the profile update.
     *
     * @param array $data  Form data from POST request
     * @param object $user The authenticated user model
     * @return bool
     */
    public function execute(array $data, object $user): bool
    {
        $db = Application::$app->db->pdo;

        try {
            $db->beginTransaction();

            // 1. Update users table (name, phone)
            $name = trim($data['name'] ?? '');
            $phone = trim($data['phone'] ?? '');

            if (empty($name)) {
                $db->rollBack();
                return false;
            }

            $stmt = $db->prepare("UPDATE users SET name = :name, phone = :phone WHERE id = :id");
            $stmt->execute([
                'name' => $name,
                'phone' => $phone,
                'id' => $user->id,
            ]);

            // 2. Upsert host_profiles (bio, payout details)
            $bio = trim($data['bio'] ?? '');
            $payoutBank = trim($data['payout_bank'] ?? '');
            $payoutAccount = trim($data['payout_account'] ?? '');
            $payoutRouting = trim($data['payout_routing'] ?? '');

            if (!empty($payoutBank) && empty($payoutRouting)) {
                $paystack = new \Ace\PaystackService();
                $payoutRouting = $paystack->resolveBankCode($payoutBank);
            }

            $stmt = $db->prepare("
                INSERT INTO host_profiles (user_id, bio, payout_bank, payout_account, payout_routing)
                VALUES (:user_id, :bio, :payout_bank, :payout_account, :payout_routing)
                ON DUPLICATE KEY UPDATE
                    bio = VALUES(bio),
                    payout_bank = VALUES(payout_bank),
                    payout_account = VALUES(payout_account),
                    payout_routing = VALUES(payout_routing)
            ");
            $stmt->execute([
                'user_id' => $user->id,
                'bio' => $bio,
                'payout_bank' => $payoutBank,
                'payout_account' => $payoutAccount,
                'payout_routing' => $payoutRouting,
            ]);

            $db->commit();
            return true;
        } catch (\Exception $e) {
            $db->rollBack();
            error_log("UpdateProfileAction error: " . $e->getMessage());
            return false;
        }
    }
}
