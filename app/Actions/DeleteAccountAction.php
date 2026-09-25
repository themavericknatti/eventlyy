<?php

/**
 * DeleteAccountAction
 *
 * Handles GDPR-compliant user account deletion & anonymization.
 *
 * @package Evently
 * @subpackage Actions
 * @author Celio Natti
 * @license MIT License
 * @version 1.0.0
 */

namespace App\Actions;

use Ace\Application;
use Exception;

class DeleteAccountAction
{
    private string $error = '';

    /**
     * Execute GDPR-compliant user account deletion.
     *
     * @param array $data Form data containing 'confirm_password' and 'confirm_text'
     * @param object $user The authenticated user model
     * @return bool
     */
    public function execute(array $data, object $user): bool
    {
        $password = $data['confirm_password'] ?? '';
        $confirmText = strtoupper(trim($data['confirm_text'] ?? ''));

        // 1. Password Verification
        $db = Application::$app->db->pdo;
        $stmt = $db->prepare("SELECT password FROM users WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $user->id]);
        $row = $stmt->fetch();

        if (!$row || !password_verify($password, $row['password'])) {
            $this->error = 'Current password is incorrect. Account deletion aborted.';
            return false;
        }

        // 2. Safety phrase verification ('DELETE')
        if ($confirmText !== 'DELETE') {
            $this->error = 'Please type DELETE in capital letters to confirm account deletion.';
            return false;
        }

        // 3. Perform GDPR-friendly deletion & anonymization in a transaction
        try {
            $db->beginTransaction();

            $userId = (int)$user->id;

            // A. Anonymize tickets purchased by this user (GDPR Right to be Forgotten + Sales Count Integrity)
            $stmt = $db->prepare("
                UPDATE tickets 
                SET user_id = NULL,
                    attendee_name = 'Anonymized User',
                    attendee_email = 'deleted@anonymized.local'
                WHERE user_id = :user_id
            ");
            $stmt->execute(['user_id' => $userId]);

            // B. Anonymize transactions made by this user (Tax & Financial Compliance)
            $stmt = $db->prepare("
                UPDATE transactions 
                SET user_id = NULL,
                    payer_name = 'Anonymized Customer',
                    payer_email = 'deleted@anonymized.local'
                WHERE user_id = :user_id
            ");
            $stmt->execute(['user_id' => $userId]);

            // C. Delete event staff assignments
            $stmt = $db->prepare("DELETE FROM event_staff_assignments WHERE user_id = :user_id");
            $stmt->execute(['user_id' => $userId]);

            // D. Delete host profile
            $stmt = $db->prepare("DELETE FROM host_profiles WHERE user_id = :user_id");
            $stmt->execute(['user_id' => $userId]);

            // E. Delete user roles
            $stmt = $db->prepare("DELETE FROM user_roles WHERE user_id = :user_id");
            $stmt->execute(['user_id' => $userId]);

            // F. Handle organizer's events (if any)
            // Delete unpublished draft events; keep published events to prevent breaking attendee ticket access
            $stmt = $db->prepare("DELETE FROM events WHERE organizer_id = :user_id AND status = 'draft'");
            $stmt->execute(['user_id' => $userId]);

            // G. Finally, delete the user account from users table
            $stmt = $db->prepare("DELETE FROM users WHERE id = :user_id");
            $stmt->execute(['user_id' => $userId]);

            $db->commit();
            return true;

        } catch (Exception $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            error_log("DeleteAccountAction error: " . $e->getMessage());
            $this->error = 'Failed to delete account due to a database error. Please contact support.';
            return false;
        }
    }

    /**
     * Get the error message from the last failed execution.
     */
    public function getError(): string
    {
        return $this->error;
    }
}
