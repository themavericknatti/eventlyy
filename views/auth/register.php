<?php

/**
 * Register View
 *
 * Professional registration with visual role selector (Attendee vs Host),
 * real-time password strength meter, and theme-adaptive styling.
 *
 * @package Evently
 * @subpackage Views/Auth
 * @author Celio Natti
 * @license MIT License
 * @version 2.0.0
 */

$siteName = site_setting('site_title', 'Eventlyy');
$siteLogo = site_setting('site_logo');
$selectedRole = old('role', 'user');
if (!in_array($selectedRole, ['user', 'event-organizer'])) {
    $selectedRole = 'user';
}
?>

@extends('layouts/auth')

@section('title')
Create Account - <?= e($siteName) ?>
@endsection

@section('content')
<div class="auth-top-bar">
    <a href="{{ route('/') }}" class="auth-back-link" title="Return to Eventlyy homepage">
        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
            <line x1="19" y1="12" x2="5" y2="12"></line>
            <polyline points="12 19 5 12 12 5"></polyline>
        </svg>
        <span>Back to events</span>
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
    <!-- Left Showcase: Visual Experience for New Members -->
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
                    Join The Community
                </span>
                <h2>Experience the stage. Or take command of it.</h2>
                <p class="showcase-lede">Whether you are securing front-row tickets or selling out multi-city tours, Eventlyy brings simplicity, speed, and reliability to every stage.</p>
            </div>

            <!-- Host & Attendee Showcase Card -->
            <div class="auth-ticket-preview organizer-preview" aria-hidden="true">
                <div class="ticket-header-strip">
                    <span class="ticket-type-tag">ORGANIZER SUITE • ALL-IN-ONE</span>
                    <span class="ticket-live-dot"><span class="pulsing-dot"></span> LIVE READY</span>
                </div>
                <div class="ticket-body">
                    <div class="organizer-metric-row">
                        <div class="org-metric">
                            <span class="metric-label">Tickets Sold</span>
                            <span class="metric-val">1,420 <small>passes</small></span>
                        </div>
                        <div class="org-metric-divider"></div>
                        <div class="org-metric">
                            <span class="metric-label">Payout Status</span>
                            <span class="metric-val status-green">✓ Automated</span>
                        </div>
                    </div>
                    <div class="organizer-feature-badges">
                        <span class="org-pill">🎟️ Custom Flyer Designer</span>
                        <span class="org-pill">📱 Mobile Gate Scanner</span>
                        <span class="org-pill">📊 Real-Time Analytics</span>
                    </div>
                </div>
                <div class="ticket-stub-perforation">
                    <span class="perforation-notch left"></span>
                    <span class="perforation-line"></span>
                    <span class="perforation-notch right"></span>
                </div>
                <div class="ticket-footer-strip">
                    <span>Zero upfront setup fees</span>
                    <span>Instant activation</span>
                </div>
            </div>

            <!-- Social Proof -->
            <div class="auth-trust-strip">
                <div class="trust-stat">
                    <strong>100%</strong>
                    <span>Verified Tickets</span>
                </div>
                <div class="trust-divider"></div>
                <div class="trust-stat">
                    <strong>0%</strong>
                    <span>Hidden Fees</span>
                </div>
                <div class="trust-divider"></div>
                <div class="trust-stat">
                    <strong>24/7</strong>
                    <span>Fast Support</span>
                </div>
            </div>
        </div>
    </section>

    <!-- Right Panel: Registration Form Card -->
    <div class="auth-card">
        <div class="auth-card-header">
            <h1>Create your account</h1>
            <p>Start booking tickets or publishing your own live events today.</p>
        </div>

        <div class="auth-message" aria-live="polite" style="display:none;"></div>

        <form id="register-form" method="POST" action="{{ route('register') }}" novalidate>
            @csrf

            <!-- Visual Segmented Role Selector -->
            <div class="auth-field-group">
                <label class="auth-label">I want to:</label>
                <input type="hidden" name="role" id="reg-role" value="<?= e($selectedRole) ?>">
                <div class="auth-role-picker" role="radiogroup" aria-label="Select account role">
                    <button type="button" class="role-card-option <?= $selectedRole === 'user' ? 'active' : '' ?>" data-role="user" role="radio" aria-checked="<?= $selectedRole === 'user' ? 'true' : 'false' ?>">
                        <div class="role-icon-box">🎟️</div>
                        <div class="role-text-box">
                            <strong>Attendee</strong>
                            <span>Discover events, book tickets &amp; store mobile passes</span>
                        </div>
                        <div class="role-check-indicator" aria-hidden="true">
                            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="20 6 9 17 4 12"></polyline>
                            </svg>
                        </div>
                    </button>

                    <button type="button" class="role-card-option <?= $selectedRole === 'event-organizer' ? 'active' : '' ?>" data-role="event-organizer" role="radio" aria-checked="<?= $selectedRole === 'event-organizer' ? 'true' : 'false' ?>">
                        <div class="role-icon-box">🎙️</div>
                        <div class="role-text-box">
                            <strong>Event Host</strong>
                            <span>Publish events, sell tickets &amp; receive direct bank payouts</span>
                        </div>
                        <div class="role-check-indicator" aria-hidden="true">
                            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="20 6 9 17 4 12"></polyline>
                            </svg>
                        </div>
                    </button>
                </div>
                @error('role')
                    <span class="text-error-message">{{ $message }}</span>
                @enderror
            </div>

            <div class="auth-field-group">
                <label for="reg-name" class="auth-label">Full Name</label>
                <div class="auth-input-wrapper">
                    <span class="auth-input-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                    </span>
                    <input type="text" name="name" id="reg-name" class="auth-input" value="{{ old('name') }}" required placeholder="Jane Doe" autocomplete="name" />
                </div>
                @error('name')
                    <span class="text-error-message">{{ $message }}</span>
                @enderror
            </div>

            <div class="auth-field-group">
                <label for="reg-email" class="auth-label">Email Address</label>
                <div class="auth-input-wrapper">
                    <span class="auth-input-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                            <polyline points="22,6 12,13 2,6"></polyline>
                        </svg>
                    </span>
                    <input type="email" name="email" id="reg-email" class="auth-input" value="{{ old('email') }}" required placeholder="you@example.com" autocomplete="email" />
                </div>
                @error('email')
                    <span class="text-error-message">{{ $message }}</span>
                @enderror
            </div>

            <div class="auth-field-group">
                <label for="reg-password" class="auth-label">Password</label>
                <div class="auth-input-wrapper password-input-wrapper">
                    <span class="auth-input-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                            <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                        </svg>
                    </span>
                    <input type="password" name="password" id="reg-password" class="auth-input has-toggle" required placeholder="At least 8 characters" autocomplete="new-password" />
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
                <label for="reg-password-confirmation" class="auth-label">Confirm Password</label>
                <div class="auth-input-wrapper password-input-wrapper">
                    <span class="auth-input-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                        </svg>
                    </span>
                    <input type="password" name="passwordConfirm" id="reg-password-confirmation" class="auth-input has-toggle" required placeholder="Repeat your password" autocomplete="new-password" />
                    <button type="button" class="password-toggle-btn" aria-label="Show password" aria-pressed="false" title="Show password">
                        <svg class="eye-show" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        <svg class="eye-hide" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:none;"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                    </button>
                </div>
                <div class="pw-match-badge" id="pw-match-badge" style="display:none;"></div>
                @error('passwordConfirm')
                    <span class="text-error-message">{{ $message }}</span>
                @enderror
            </div>

            <p class="auth-legal-note">
                By creating an account, you agree to our <a href="{{ route('terms') }}" target="_blank" class="auth-inline-link">Terms of Service</a> and <a href="{{ route('privacy') }}" target="_blank" class="auth-inline-link">Privacy Policy</a>.
            </p>

            <button type="submit" class="auth-submit-btn">
                <span>Create Free Account</span>
                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                    <polyline points="12 5 19 12 12 19"></polyline>
                </svg>
            </button>
        </form>

        <div class="auth-card-divider">
            <span>Already have an account?</span>
        </div>

        <div class="auth-foot">
            <a href="{{ route('login') }}" class="auth-secondary-btn">
                Sign In Instead
            </a>
        </div>
    </div>
</div>
@endsection
