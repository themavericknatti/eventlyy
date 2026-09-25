<?php

namespace App\Actions;

use Ace\Application;
use App\Models\User;

class ManageUserStatusAction
{
    /**
     * Suspend or reactivate user accounts with role hierarchy protection.
     */
    public function execute(string $action, int|string $targetUserId, int|string $currentAdminId): array
    {
        if (!$targetUserId) {
            return ['status' => 'error', 'message' => 'User ID is missing.'];
        }

        // 1. Logged-in user cannot suspend or modify their own active session account
        if ($targetUserId == $currentAdminId) {
            return ['status' => 'error', 'message' => 'You cannot suspend or modify your own active account.'];
        }

        $db = Application::$app->db->pdo;

        // Fetch target user's roles
        $targetUser = User::findOne(['id' => $targetUserId]);
        if (!$targetUser) {
            return ['status' => 'error', 'message' => 'Target user not found.'];
        }

        // Fetch current logged-in admin user model
        $currentAdmin = User::findOne(['id' => $currentAdminId]);
        if (!$currentAdmin) {
            return ['status' => 'error', 'message' => 'Unauthorized admin user.'];
        }

        $isCurrentSuperAdmin = $currentAdmin->isSuperAdmin();
        $isTargetSuperAdmin = $targetUser->isSuperAdmin();
        $isTargetAdmin = $targetUser->isAdmin() || $isTargetSuperAdmin;

        // 2. Protection: Ordinary admins cannot modify Super Admins
        if ($isTargetSuperAdmin && !$isCurrentSuperAdmin) {
            return ['status' => 'error', 'message' => 'Super Admin accounts are protected. Only Super Admins can perform actions on this account.'];
        }

        // 3. Protection: Ordinary admins cannot modify other Admins
        if ($isTargetAdmin && !$isCurrentSuperAdmin) {
            return ['status' => 'error', 'message' => 'Admin accounts are protected. Only a Super Admin can modify or suspend another Admin.'];
        }

        if ($action === 'suspend') {
            $stmt = $db->prepare("UPDATE users SET status = 'suspended' WHERE id = :id");
            $stmt->execute(['id' => $targetUserId]);
            return ['status' => 'warning', 'message' => 'User account has been suspended successfully.'];
        } elseif ($action === 'reactivate') {
            $stmt = $db->prepare("UPDATE users SET status = 'active' WHERE id = :id");
            $stmt->execute(['id' => $targetUserId]);
            return ['status' => 'success', 'message' => 'User account has been reactivated successfully.'];
        } elseif ($action === 'verify_email') {
            // Admin manually verifies the user's email
            if (!empty($targetUser->email_verified_at)) {
                return ['status' => 'info', 'message' => 'This user\'s email is already verified.'];
            }
            $stmt = $db->prepare("UPDATE users SET email_verified_at = :now WHERE id = :id");
            $stmt->execute(['now' => date('Y-m-d H:i:s'), 'id' => $targetUserId]);
            return ['status' => 'success', 'message' => 'Email has been manually verified for ' . e($targetUser->name) . '. They can now create events.'];
        } elseif ($action === 'resend_verification') {
            // Resend verification email to the user
            if (!empty($targetUser->email_verified_at)) {
                return ['status' => 'info', 'message' => 'This user\'s email is already verified. No need to resend.'];
            }
            
            // Generate secure verification token
            $token = bin2hex(random_bytes(32));
            $tokenHash = hash('sha256', $token);
            $expiresAt = date('Y-m-d H:i:s', strtotime('+24 hours'));

            try {
                // Clear existing verifications for this email
                $delStmt = $db->prepare("DELETE FROM email_verifications WHERE email = :email");
                $delStmt->execute(['email' => $targetUser->email]);

                $stmt = $db->prepare("
                    INSERT INTO email_verifications (email, token_hash, expires_at, created_at)
                    VALUES (:email, :token_hash, :expires_at, NOW())
                ");
                $stmt->execute([
                    'email' => $targetUser->email,
                    'token_hash' => $tokenHash,
                    'expires_at' => $expiresAt
                ]);

                $verificationUrl = \url('/verify-email?token=' . $token . '&email=' . urlencode($targetUser->email));

                \Ace\Mail::send(
                    $targetUser->email,
                    'Verify Your Account - ' . site_setting('site_title', 'Evently'),
                    'emails/welcome',
                    [
                        'name' => $targetUser->name,
                        'email' => $targetUser->email,
                        'verification_url' => $verificationUrl
                    ]
                );

                return ['status' => 'success', 'message' => 'Verification email has been resent to ' . e($targetUser->email) . '.'];
            } catch (\Throwable $e) {
                error_log("Failed to send admin-triggered verification email: " . $e->getMessage());
                return ['status' => 'warning', 'message' => 'Verification token was generated, but failed to deliver email: ' . $e->getMessage()];
            }
        }

        return ['status' => 'error', 'message' => 'Invalid user management action.'];
    }
}
