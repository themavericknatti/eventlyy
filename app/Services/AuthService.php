<?php

/**
 * AuthService
 *
 * This service handles authentication-related logic, login, logout, password resets, and email verification.
 *
 * @package Evently
 * @subpackage Services/Auth
 * @author Your Name <Celio Natti>
 * @license MIT License
 * @version 1.0.0
 */

namespace App\Services;

use App\Models\User;
use Ace\Application;
use Ace\Mail;
use App\Services\RateLimiterService;

class AuthService
{
    /**
     * Attempt to log in a user with the provided credentials.
     *
     * @param array $credentials The user's login credentials (email and password).
     * @param User $user The User model instance to populate with errors if login fails.
     * @return bool True if login is successful, false otherwise.
     */
    public function login(array $credentials, User $user): bool
    {
        $user->loadData($credentials);

        if (empty($credentials['email'])) {
            $user->addError('email', 'Email is required.');
        }
        if (empty($credentials['password'])) {
            $user->addError('password', 'Password is required.');
        }

        if (!empty($user->errors)) {
            return false;
        }

        $rateLimiter = new RateLimiterService();
        $ip = RateLimiterService::getClientIp();
        $email = $user->email ?? ($credentials['email'] ?? '');

        // Check if locked out
        $remainingSeconds = $rateLimiter->getRemainingLockoutSeconds($ip, $email);
        if ($remainingSeconds > 0) {
            $minutes = (int)ceil($remainingSeconds / 60);
            $user->addError('email', "Too many failed login attempts. Account temporarily locked for security. Please try again in {$minutes} minute(s).");
            return false;
        }

        $dbUser = User::findOne(['email' => $user->email]);
        if ($dbUser && password_verify($credentials['password'], $dbUser->password)) {
            if ($dbUser->status === 'suspended') {
                $user->addError('email', 'Your account has been suspended. Please contact support.');
                return false;
            }

            // Clear failed login attempts on successful login
            $rateLimiter->clear($ip, $email);

            $remember = !empty($credentials['remember']);
            Application::$app->login($dbUser, $remember);
            return true;
        }

        // Record failed attempt hit
        $hitResult = $rateLimiter->hit($ip, $email);
        $attemptsLeft = RateLimiterService::MAX_ATTEMPTS - $hitResult['attempts'];

        if ($hitResult['locked']) {
            $user->addError('email', "Too many failed login attempts. Account temporarily locked for security. Please try again in 15 minutes.");
        } elseif ($attemptsLeft > 0) {
            $user->addError('email', "Invalid email or password. ({$attemptsLeft} attempt(s) remaining before lockout)");
        } else {
            $user->addError('email', 'Invalid email or password.');
        }

        return false;
    }

    /**
     * Send password reset email link
     */
    public function sendPasswordResetLink(string $email): bool
    {
        $user = User::findOne(['email' => $email]);
        if (!$user) {
            // Return true to avoid leaking registered email addresses
            return true;
        }

        $token = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $token);

        $db = Application::$app->db->pdo;

        // Clear existing reset tokens for this email
        $del = $db->prepare("DELETE FROM password_resets WHERE email = :email");
        $del->execute(['email' => $email]);

        // Store hashed token
        $stmt = $db->prepare("INSERT INTO password_resets (email, token, created_at) VALUES (:email, :token, NOW())");
        $stmt->execute(['email' => $email, 'token' => $tokenHash]);

        $resetUrl = \url('/reset-password?token=' . $token . '&email=' . urlencode($email));

        try {
            Mail::send(
                $email,
                'Reset Your Password - ' . site_setting('site_title', 'Evently'),
                'emails/reset_password',
                [
                    'name' => $user->name,
                    'reset_url' => $resetUrl
                ]
            );
        } catch (\Exception $e) {
            error_log("Failed to send password reset email: " . $e->getMessage());
            return false;
        }

        return true;
    }

    /**
     * Reset user password using token
     */
    public function resetPassword(string $email, string $token, string $newPassword): bool
    {
        if (empty($email) || empty($token) || strlen($newPassword) < 6) {
            return false;
        }

        $tokenHash = hash('sha256', $token);
        $db = Application::$app->db->pdo;

        // Check if token exists and is created within the last 1 hour
        $stmt = $db->prepare("
            SELECT * FROM password_resets
            WHERE email = :email AND token = :token AND created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
            LIMIT 1
        ");
        $stmt->execute(['email' => $email, 'token' => $tokenHash]);
        $record = $stmt->fetch();

        if (!$record) {
            return false;
        }

        $user = User::findOne(['email' => $email]);
        if (!$user) {
            return false;
        }

        // Update user password
        $user->password = password_hash($newPassword, PASSWORD_BCRYPT);
        $user->save();

        // Delete used reset token
        $del = $db->prepare("DELETE FROM password_resets WHERE email = :email");
        $del->execute(['email' => $email]);

        return true;
    }

    /**
     * Log out the currently authenticated user.
     */
    public function logout(): void
    {
        Application::$app->logout();
    }
}