<?php

/**
 * AuthController
 *
 * Handles authentication routes including login, registration, email verification, password reset, and logout.
 *
 * @package Evently
 * @subpackage Controllers/Auth
 * @author Your Name <Celio Natti>
 * @license MIT License
 * @version 2.0.0
 */

namespace App\Controllers;

use Ace\Application;
use Ace\Controller;
use Ace\Middlewares\CsrfMiddleware;
use Ace\Middlewares\GuestMiddleware;
use Ace\Request;
use Ace\Mail;
use App\Actions\RegisterUserAction;
use App\Models\User;
use App\Services\AuthService;

class AuthController extends Controller
{
    protected AuthService $authService;
    protected RegisterUserAction $registerUser;

    public function __construct(AuthService $authService, RegisterUserAction $registerUser)
    {
        $this->authService = $authService;
        $this->registerUser = $registerUser;

        $this->registerMiddleware(new GuestMiddleware([
            'loginView',
            'login',
            'registerView',
            'register',
            'forgotPasswordView',
            'forgotPassword',
            'resetPasswordView',
            'resetPassword'
        ]));

        $this->registerMiddleware(new CsrfMiddleware([
            'login',
            'register',
            'resendVerification',
            'forgotPassword',
            'resetPassword'
        ]));
    }

    /**
     * Display login view
     */
    public function loginView(Request $request): string
    {
        $user = new User();
        return $this->render('auth/login', [
            'user' => $user,
            'model' => $user
        ]);
    }

    /**
     * Handle user login
     */
    public function login(Request $request)
    {
        $user = new User();
        $data = $request->getBody();

        if (!$this->authService->login($data, $user)) {
            return $this->render('auth/login', [
                'user' => $user,
                'model' => $user
            ]);
        }

        Application::$app->session->setFlash('success', 'Login successful! Welcome back.');
        
        $loggedInUser = Application::$app->user;
        if ($loggedInUser && (method_exists($loggedInUser, 'isAdmin') && $loggedInUser->isAdmin() || method_exists($loggedInUser, 'isSuperAdmin') && $loggedInUser->isSuperAdmin() || hasRole('admin') || hasRole('super-admin'))) {
            return Application::$app->response->redirect('/admin/dashboard');
        }
        if ($loggedInUser && $loggedInUser->isHost()) {
            return Application::$app->response->redirect('/host/dashboard');
        }

        return Application::$app->response->redirect('/');
    }

    /**
     * Display registration view
     */
    public function registerView(Request $request): string
    {
        $user = new User();
        return $this->render('auth/register', [
            'user' => $user,
            'model' => $user
        ]);
    }

    /**
     * Handle user registration
     */
    public function register(Request $request)
    {
        $user = new User();
        $data = $request->getBody();

        if (!$this->registerUser->execute($data, $user)) {
            return $this->render('auth/register', [
                'user' => $user,
                'model' => $user
            ]);
        }

        Application::$app->session->setFlash('success', 'Registration successful! A verification link has been sent to your email. Please verify your account before hosting events.');
        return Application::$app->response->redirect('/login');
    }

    /**
     * Handle Email Verification Link
     */
    public function verifyEmail(Request $request)
    {
        $token = trim($request->getString('token'));
        $email = trim($request->getString('email'));

        if (empty($token) || empty($email)) {
            Application::$app->session->setFlash('error', 'Invalid or missing email verification link.');
            return Application::$app->response->redirect('/resend-verification');
        }

        $tokenHash = hash('sha256', $token);
        $db = Application::$app->db->pdo;

        $stmt = $db->prepare("
            SELECT * FROM email_verifications 
            WHERE email = :email AND token_hash = :token_hash AND expires_at > NOW()
            LIMIT 1
        ");
        $stmt->execute(['email' => $email, 'token_hash' => $tokenHash]);
        $record = $stmt->fetch();

        if (!$record) {
            // Check if user is already verified (e.g. clicked earlier, duplicate click, or already verified by admin)
            $existingUser = User::findOne(['email' => $email]);
            if ($existingUser && $existingUser->isVerified()) {
                Application::$app->session->setFlash('success', '🎉 Your email address is already verified! You can sign in and start hosting events.');
                return Application::$app->response->redirect('/login');
            }

            Application::$app->session->setFlash('error', 'The email verification link is invalid or has expired. Please request a new verification link below.');
            return Application::$app->response->redirect('/resend-verification');
        }

        $user = User::findOne(['email' => $email]);
        if ($user) {
            $user->markEmailAsVerified();
        }

        // Delete used verification token
        $del = $db->prepare("DELETE FROM email_verifications WHERE email = :email");
        $del->execute(['email' => $email]);

        Application::$app->session->setFlash('success', '🎉 Your email address has been verified successfully! You can now log in and create live events.');
        return Application::$app->response->redirect('/login');
    }

    /**
     * Display resend verification view
     */
    public function resendVerificationView(Request $request): string
    {
        return $this->render('auth/resend-verification');
    }

    /**
     * Handle resend verification email request
     */
    public function resendVerification(Request $request)
    {
        $email = trim($request->getString('email'));

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Application::$app->session->setFlash('error', 'Please enter a valid email address.');
            return $this->render('auth/resend-verification');
        }

        $user = User::findOne(['email' => $email]);
        if (!$user) {
            // Flash success to avoid email enumeration
            Application::$app->session->setFlash('success', 'If an unverified account exists for this email, a new verification link has been sent.');
            return Application::$app->response->redirect('/login');
        }

        if ($user->isVerified()) {
            Application::$app->session->setFlash('success', 'Your email address is already verified! Please sign in to continue.');
            return Application::$app->response->redirect('/login');
        }

        // Generate new token
        $token = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $token);
        $expiresAt = date('Y-m-d H:i:s', strtotime('+24 hours'));

        $db = Application::$app->db->pdo;
        $del = $db->prepare("DELETE FROM email_verifications WHERE email = :email");
        $del->execute(['email' => $email]);

        $stmt = $db->prepare("
            INSERT INTO email_verifications (email, token_hash, expires_at, created_at)
            VALUES (:email, :token_hash, :expires_at, NOW())
        ");
        $stmt->execute([
            'email' => $email,
            'token_hash' => $tokenHash,
            'expires_at' => $expiresAt
        ]);

        $verificationUrl = \url('/verify-email?token=' . $token . '&email=' . urlencode($email));

        try {
            Mail::send(
                $email,
                'Verify Your Account - ' . site_setting('site_title', 'Evently'),
                'emails/welcome',
                [
                    'name' => $user->name,
                    'email' => $user->email,
                    'verification_url' => $verificationUrl
                ]
            );
        } catch (\Exception $e) {
            error_log("Failed to resend verification email: " . $e->getMessage());
        }

        Application::$app->session->setFlash('success', 'A new verification link has been transmitted to your email address. Please check your inbox.');
        return Application::$app->response->redirect('/login');
    }

    /**
     * Display forgot password view
     */
    public function forgotPasswordView(Request $request): string
    {
        return $this->render('auth/forgot-password');
    }

    /**
     * Handle forgot password request
     */
    public function forgotPassword(Request $request)
    {
        $email = trim($request->getString('email'));

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Application::$app->session->setFlash('error', 'Please enter a valid email address.');
            return $this->render('auth/forgot-password');
        }

        $this->authService->sendPasswordResetLink($email);

        Application::$app->session->setFlash('success', 'If your email is registered in our platform, a password reset link has been sent to your inbox.');
        return Application::$app->response->redirect('/login');
    }

    /**
     * Display reset password view
     */
    public function resetPasswordView(Request $request): string
    {
        $token = trim($request->getString('token'));
        $email = trim($request->getString('email'));

        return $this->render('auth/reset-password', [
            'token' => $token,
            'email' => $email
        ]);
    }

    /**
     * Handle reset password submission
     */
    public function resetPassword(Request $request)
    {
        $token = trim($request->getString('token'));
        $email = trim($request->getString('email'));
        $password = $request->getString('password');
        $passwordConfirm = $request->getString('password_confirm');

        if (empty($password) || strlen($password) < 6) {
            Application::$app->session->setFlash('error', 'Password must be at least 6 characters long.');
            return $this->render('auth/reset-password', ['token' => $token, 'email' => $email]);
        }

        if ($password !== $passwordConfirm) {
            Application::$app->session->setFlash('error', 'Password confirmation does not match.');
            return $this->render('auth/reset-password', ['token' => $token, 'email' => $email]);
        }

        if (!$this->authService->resetPassword($email, $token, $password)) {
            Application::$app->session->setFlash('error', 'Invalid or expired password reset token. Please request a new reset link.');
            return Application::$app->response->redirect('/forgot-password');
        }

        Application::$app->session->setFlash('success', 'Your password has been reset successfully! You can now sign in with your new password.');
        return Application::$app->response->redirect('/login');
    }

    /**
     * Handle user logout
     */
    public function logout(Request $request)
    {
        $this->authService->logout();
        Application::$app->session->setFlash('success', 'You have been logged out successfully.');
        return Application::$app->response->redirect('/');
    }
}