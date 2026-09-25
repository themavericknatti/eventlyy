<?php

/**
 * ChangePasswordAction
 *
 * Handles password change requests with current password verification.
 *
 * @package Evently
 * @subpackage Actions
 * @author Celio Natti
 * @license MIT License
 * @version 1.0.0
 */

namespace App\Actions;

use Ace\Application;

class ChangePasswordAction
{
    private string $error = '';

    /**
     * Execute the password change.
     *
     * @param array $data  Form data (current_password, new_password, confirm_password)
     * @param object $user The authenticated user model
     * @return bool
     */
    public function execute(array $data, object $user): bool
    {
        $currentPassword = $data['current_password'] ?? '';
        $newPassword = $data['new_password'] ?? '';
        $confirmPassword = $data['confirm_password'] ?? '';

        // Validate current password
        if (empty($currentPassword)) {
            $this->error = 'Current password is required.';
            return false;
        }

        // Fetch user's current hashed password
        $db = Application::$app->db->pdo;
        $stmt = $db->prepare("SELECT password FROM users WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $user->id]);
        $row = $stmt->fetch();

        if (!$row || !password_verify($currentPassword, $row['password'])) {
            $this->error = 'Current password is incorrect.';
            return false;
        }

        // Validate new password
        if (strlen($newPassword) < 6) {
            $this->error = 'New password must be at least 6 characters.';
            return false;
        }

        if ($newPassword !== $confirmPassword) {
            $this->error = 'New password and confirmation do not match.';
            return false;
        }

        // Update password
        try {
            $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
            $stmt = $db->prepare("UPDATE users SET password = :password WHERE id = :id");
            $stmt->execute([
                'password' => $hashedPassword,
                'id' => $user->id,
            ]);
            return true;
        } catch (\Exception $e) {
            error_log("ChangePasswordAction error: " . $e->getMessage());
            $this->error = 'An unexpected error occurred. Please try again.';
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
