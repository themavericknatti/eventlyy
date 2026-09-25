<?php

/**
 * Reset Password View
 *
 * This view displays the reset password form for users.
 *
 * @package Evently
 * @subpackage Views/Auth
 * @author Your Name <Celio Natti>
 * @license MIT License
 * @version 1.0.0
 */

$siteName = site_setting('site_title', 'Eventlyy');
?>

@extends('layouts/auth')

@section('title')
Reset Password - <?= e($siteName) ?>
@endsection

@section('content')
<div class="auth-top-bar">
    <a href="{{ route('login') }}" class="auth-back-link">
        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5"
            stroke-linecap="round" stroke-linejoin="round">
            <line x1="19" y1="12" x2="5" y2="12" />
            <polyline points="12 19 5 12 12 5" />
        </svg>
        Back to sign in
    </a>
    <button id="theme-toggle" data-theme-toggle="true" class="auth-theme-btn" aria-label="Toggle theme"
        aria-pressed="false">🌓</button>
</div>

<div class="auth-split">
    <!-- Left Panel: Brand info / Promo -->
    <section class="auth-panel">
        <div class="auth-brand-badge">
            <span class="brand-letter"><?= e(mb_substr($siteName, 0, 1)) ?></span>
            <span><?= e($siteName) ?> Platform</span>
        </div>
        <div class="auth-panel-hero">
            <span class="panel-badge">Reset password</span>
            <h2>Choose a new secure password</h2>
            <p class="lede">Update your account credentials and get back to managing your events quickly and safely.</p>
        </div>

        <ul class="auth-features-list">
            <li>
                <span class="feature-icon">
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5"
                        stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20 6 9 17 4 12" />
                    </svg>
                </span>
                <div>
                    <strong>Strong password security</strong>
                    <span>Always use unique symbols and casing to lock down access.</span>
                </div>
            </li>
            <li>
                <span class="feature-icon">
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5"
                        stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20 6 9 17 4 12" />
                    </svg>
                </span>
                <div>
                    <strong>Instant confirmation</strong>
                    <span>Immediate visual feedback once your password resets.</span>
                </div>
            </li>
            <li>
                <span class="feature-icon">
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5"
                        stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20 6 9 17 4 12" />
                    </svg>
                </span>
                <div>
                    <strong>Touch-optimized fields</strong>
                    <span>Clear fields and large input labels for effortless access.</span>
                </div>
            </li>
        </ul>
    </section>

    <!-- Right Panel: Form Card -->
    <div class="auth-card">
        <div class="auth-card-header">
            <h1>Set a new password</h1>
            <p class="lede" style="margin-top:0.25rem;">Enter and confirm your updated secret password below.</p>
        </div>

        <div class="auth-message" aria-live="polite" style="display:none;"></div>

        <form id="reset-form" method="POST" action="{{ route('reset-password') }}">
            @csrf
            <input type="hidden" name="action" value="reset_password" />
            <input type="hidden" name="token" value="<?= e($token ?? $_GET['token'] ?? '') ?>" />
            <input type="hidden" name="email" value="<?= e($email ?? $_GET['email'] ?? '') ?>" />

            <label>New Password
                <input type="password" name="password" id="reset-password" required placeholder="At least 8 characters"
                    autocomplete="new-password">
            </label>
            <label>Confirm Password
                <input type="password" name="password_confirm" id="reset-password-confirm" required
                    placeholder="Confirm password" autocomplete="new-password">
            </label>

            <button type="submit" class="admin-action-btn primary"
                style="width:100%; height:48px; font-weight:700; font-size:0.95rem; margin-top:1.5rem; border-radius:14px; justify-content:center;">
                Reset Password
            </button>
        </form>

        <div class="auth-foot" style="justify-content:center;">
            <a href="{{ route('login') }}" class="auth-link" style="font-size:0.88rem;">Back to sign in</a>
        </div>
    </div>
</div>
@endsection