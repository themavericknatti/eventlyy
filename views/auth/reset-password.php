<?php

/**
 * Reset Password View
 *
 * Professional password update view with token validation,
 * interactive password meter, and theme-adaptive styling.
 *
 * @package Evently
 * @subpackage Views/Auth
 * @author Celio Natti
 * @license MIT License
 * @version 2.0.0
 */

$siteName = site_setting('site_title', 'Eventlyy');
$siteLogo = site_setting('site_logo');
?>

@extends('layouts/auth')

@section('title')
Reset Password - <?= e($siteName) ?>
@endsection

@section('content')
<div class="auth-top-bar">
    <a href="{{ route('login') }}" class="auth-back-link" title="Return to sign in">
        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
            <line x1="19" y1="12" x2="5" y2="12"></line>
            <polyline points="12 19 5 12 12 5"></polyline>
        </svg>
        <span>Back to sign in</span>
    </a>

    <div class="auth-top-brand" aria-hidden="true">
        <?= site_logo_render('auth-top', 'auth-top-logo') ?>
    </div>

    <div class="auth-top-actions">
        <button id="theme-toggle" data-theme-toggle="true" class="auth-theme-btn" aria-label="Toggle dark/light theme" aria-pressed="false" title="Switch theme mode">
            <span class="theme-icon-indicator">🌓</span>
        </button>
    </div>
</div>

<div class="auth-split">
    <!-- Left Showcase -->
    <section class="auth-showcase" aria-label="Platform Highlights">
        <div class="auth-showcase-content">
            <div class="auth-brand-badge">
                <?= site_logo_render('auth-badge', 'auth-brand-logo') ?>
                <?php if (empty(site_setting('site_logo')) && empty(site_setting('site_logo_dark'))): ?>
                    <span class="brand-title"><?= e($siteName) ?></span>
                <?php endif; ?>
            </div>

            <div class="auth-showcase-hero">
                <span class="showcase-pill">
                    <svg viewBox="0 0 24 24" width="14" height="14" fill="currentColor">
                        <path d="M12 2l2.4 7.4h7.6l-6.2 4.5 2.4 7.4-6.2-4.5-6.2 4.5 2.4-7.4-6.2-4.5h7.6z"/>
                    </svg>
                    Secure Credentials
                </span>
                <h2>Set a strong key. Back to the show.</h2>
                <p class="showcase-lede">Choose a strong, updated password to safeguard your event tickets, host payouts, and personal profile information.</p>
            </div>

            <!-- Credentials Ticket Card -->
            <div class="auth-ticket-preview reset-preview" aria-hidden="true">
                <div class="ticket-header-strip">
                    <span class="ticket-type-tag">CREDENTIALS UPDATE • ACTIVE TOKEN</span>
                    <span class="ticket-live-dot"><span class="pulsing-dot"></span> ENCRYPTED</span>
                </div>
                <div class="ticket-body">
                    <div class="ticket-meta">
                        <span class="ticket-eyebrow">Security Standard</span>
                        <h4 class="ticket-event-title">Argon2 / Bcrypt Salting</h4>
                        <p class="ticket-venue">
                            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                            Zero plain-text storage
                        </p>
                    </div>
                    <div class="ticket-qr-box">
                        <div class="ticket-qr-matrix">
                            <div class="qr-corner top-left"></div>
                            <div class="qr-corner top-right"></div>
                            <div class="qr-corner bottom-left"></div>
                            <div class="qr-pattern-grid"></div>
                        </div>
                        <span class="ticket-qr-label">Pass Verified</span>
                    </div>
                </div>
                <div class="ticket-stub-perforation">
                    <span class="perforation-notch left"></span>
                    <span class="perforation-line"></span>
                    <span class="perforation-notch right"></span>
                </div>
                <div class="ticket-footer-strip">
                    <span>Account: <?= e($email ?? $_GET['email'] ?? 'Active Member') ?></span>
                    <span>1-Click Sign-in</span>
                </div>
            </div>

            <!-- Trust Strip -->
            <div class="auth-trust-strip">
                <div class="trust-stat">
                    <strong>256-bit</strong>
                    <span>Encryption</span>
                </div>
                <div class="trust-divider"></div>
                <div class="trust-stat">
                    <strong>Instant</strong>
                    <span>Access Reset</span>
                </div>
                <div class="trust-divider"></div>
                <div class="trust-stat">
                    <strong>Active</strong>
                    <span>Session Lock</span>
                </div>
            </div>
        </div>
    </section>

    <!-- Right Panel: Reset Password Form Card -->
    <div class="auth-card">
        <div class="auth-card-header">
            <h1>Set a new password</h1>
            <p>Please enter your updated secret password below.</p>
        </div>

        <div class="auth-message" aria-live="polite" style="display:none;"></div>

        <form id="reset-form" method="POST" action="{{ route('reset-password') }}" novalidate>
            @csrf
            <input type="hidden" name="action" value="reset_password" />
            <input type="hidden" name="token" value="<?= e($token ?? $_GET['token'] ?? '') ?>" />
            <input type="hidden" name="email" value="<?= e($email ?? $_GET['email'] ?? '') ?>" />

            <div class="auth-field-group">
                <label for="reset-password" class="auth-label">New Password</label>
                <div class="auth-input-wrapper password-input-wrapper">
                    <span class="auth-input-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                            <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                        </svg>
                    </span>
                    <input type="password" name="password" id="reset-password" class="auth-input has-toggle" required placeholder="At least 8 characters" autocomplete="new-password" />
                    <button type="button" class="password-toggle-btn" aria-label="Show password" aria-pressed="false" title="Show password">
                        <svg class="eye-show" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        <svg class="eye-hide" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:none;"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                    </button>
                </div>

                <!-- Password Strength Meter -->
                <div class="pw-strength-container" id="pw-strength-container" aria-live="polite">
                    <div class="pw-strength-bars">
                        <span class="pw-bar" data-level="1"></span>
                        <span class="pw-bar" data-level="2"></span>
                        <span class="pw-bar" data-level="3"></span>
                        <span class="pw-bar" data-level="4"></span>
                    </div>
                    <div class="pw-strength-caption">
                        <span class="pw-strength-text" id="pw-strength-text">Password strength</span>
                    </div>
                </div>

                @error('password')
                    <span class="text-error-message">{{ $message }}</span>
                @enderror
            </div>

            <div class="auth-field-group">
                <label for="reset-password-confirm" class="auth-label">Confirm New Password</label>
                <div class="auth-input-wrapper password-input-wrapper">
                    <span class="auth-input-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                        </svg>
                    </span>
                    <input type="password" name="password_confirm" id="reset-password-confirm" class="auth-input has-toggle" required placeholder="Repeat new password" autocomplete="new-password" />
                    <button type="button" class="password-toggle-btn" aria-label="Show password" aria-pressed="false" title="Show password">
                        <svg class="eye-show" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        <svg class="eye-hide" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:none;"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                    </button>
                </div>
                <div class="pw-match-badge" id="pw-match-badge" style="display:none;"></div>
                @error('password_confirm')
                    <span class="text-error-message">{{ $message }}</span>
                @enderror
            </div>

            <button type="submit" class="auth-submit-btn">
                <span>Save New Password &amp; Sign In</span>
                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
            </button>
        </form>

        <div class="auth-card-divider">
            <span>Or cancel request</span>
        </div>

        <div class="auth-foot">
            <a href="{{ route('login') }}" class="auth-secondary-btn">
                Back to Sign In
            </a>
        </div>
    </div>
</div>
@endsection
