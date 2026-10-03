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
            <?php $siteLogo = site_setting('site_logo'); ?>
            <?php if (!empty($siteLogo)): ?>
                <img src="<?= e(asset($siteLogo)) ?>" alt="<?= e($siteName) ?>" class="auth-brand-logo" />
            <?php else: ?>
                <span class="brand-letter"><?= e(strtoupper(mb_substr($siteName, 0, 1))) ?></span>
                <span><?= e($siteName) ?></span>
            <?php endif; ?>
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
                <div class="password-input-wrapper">
                    <input type="password" name="password" id="reset-password" required placeholder="At least 8 characters"
                        autocomplete="new-password">
                    <button type="button" class="password-toggle-btn" aria-label="Show password" aria-pressed="false" title="Show password">
                        <svg class="eye-show" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        <svg class="eye-hide" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:none;"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                    </button>
                </div>
            </label>
            <label>Confirm Password
                <div class="password-input-wrapper">
                    <input type="password" name="password_confirm" id="reset-password-confirm" required
                        placeholder="Confirm password" autocomplete="new-password">
                    <button type="button" class="password-toggle-btn" aria-label="Show password" aria-pressed="false" title="Show password">
                        <svg class="eye-show" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        <svg class="eye-hide" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:none;"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                    </button>
                </div>
            </label>

            <button type="submit" class="admin-action-btn primary auth-submit-btn">
                Reset Password
            </button>
        </form>

        <div class="auth-foot">
            <a href="{{ route('login') }}" class="auth-link" style="font-size:0.88rem;">Back to sign in</a>
        </div>
    </div>
</div>
@endsection