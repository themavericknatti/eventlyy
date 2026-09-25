<?php

/**
 * RegisterUserAction
 *
 * This action handles user registration logic.
 *
 * @package Evently
 * @subpackage Actions/Auth
 * @author Your Name <Celio Natti>
 * @license MIT License
 * @version 1.0.0
 */

namespace App\Actions;

use App\Models\User;
use Ace\Mail;

class RegisterUserAction
{
    /**
     * Execute the user registration action.
     *
     * @param array $data The user registration data.
     * @param User $user The user model instance.
     * @return bool Returns true if registration is successful, false otherwise.
     */
    public function execute(array $data, User $user): bool
    {
        $user->loadData($data);
        if ($user->validate() && $user->save()) {
            // Assign default role if specified, default to 'user'
            $roleSlug = !empty($data['role']) && in_array($data['role'], ['event-organizer', 'host']) ? 'event-organizer' : 'user';
            $user->assignRole($roleSlug);

            // Generate secure verification token
            $token = bin2hex(random_bytes(32));
            $tokenHash = hash('sha256', $token);
            $expiresAt = date('Y-m-d H:i:s', strtotime('+24 hours'));

            try {
                $db = \Ace\Application::$app->db->pdo;
                
                // Clear existing verifications for this email
                $delStmt = $db->prepare("DELETE FROM email_verifications WHERE email = :email");
                $delStmt->execute(['email' => $user->email]);

                $stmt = $db->prepare("
                    INSERT INTO email_verifications (email, token_hash, expires_at, created_at)
                    VALUES (:email, :token_hash, :expires_at, NOW())
                ");
                $stmt->execute([
                    'email' => $user->email,
                    'token_hash' => $tokenHash,
                    'expires_at' => $expiresAt
                ]);

                $verificationUrl = \url('/verify-email?token=' . $token . '&email=' . urlencode($user->email));

                // Send welcome & verification email
                Mail::send(
                    $user->email,
                    'Welcome to ' . site_setting('site_title', 'Evently') . ' - Verify Your Account',
                    'emails/welcome',
                    [
                        'name' => $user->name,
                        'email' => $user->email,
                        'verification_url' => $verificationUrl
                    ]
                );
            } catch (\Exception $e) {
                error_log("Failed to process verification email: " . $e->getMessage());
            }

            return true;
        }
        return false;
    }
}