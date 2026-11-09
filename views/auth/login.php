<?php

/**
 * Login View
 *
 * Professional, human-crafted authentication experience for Eventlyy.
 * Responsive across mobile, tablet, and desktop with theme-adaptive duo-tone styling.
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
Sign In - <?= e($siteName) ?>
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
    <!-- Left Showcase: Editorial Brand Experience & Interactive Pass Mockup -->
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
                    Live Event Pass
                </span>
                <h2>Your ticket to unforgettable live experiences.</h2>
                <p class="showcase-lede">Sign in to access your digital tickets, manage upcoming events, and keep all your bookings at your fingertips.</p>
            </div>

            <!-- Human-Designed Digital Pass Mockup -->
            <div class="auth-ticket-preview" aria-hidden="true">
                <div class="ticket-header-strip">
                    <span class="ticket-type-tag">VIP ACCESS • ALL STAGES</span>
                    <span class="ticket-live-dot"><span class="pulsing-dot"></span> CONFIRMED</span>
                </div>
                <div class="ticket-body">
                    <div class="ticket-meta">
                        <span class="ticket-eyebrow">Upcoming Show</span>
                        <h4 class="ticket-event-title">Soundwave World Tour</h4>
                        <p class="ticket-venue">
                            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                            Metro Arena • Main Floor, Row A
                        </p>
                    </div>
                    <div class="ticket-qr-box">
                        <div class="ticket-qr-matrix">
                            <div class="qr-corner top-left"></div>
                            <div class="qr-corner top-right"></div>
                            <div class="qr-corner bottom-left"></div>
                            <div class="qr-pattern-grid"></div>
                        </div>
                        <span class="ticket-qr-label">Tap to scan</span>
                    </div>
                </div>
                <div class="ticket-stub-perforation">
                    <span class="perforation-notch left"></span>
                    <span class="perforation-line"></span>
                    <span class="perforation-notch right"></span>
                </div>
                <div class="ticket-footer-strip">
                    <span>Gate: North Entrance</span>
                    <span>Ticket #EV-88429</span>
                </div>
            </div>

            <!-- Social Proof Strip -->
            <div class="auth-trust-strip">
                <div class="trust-stat">
                    <strong>120,000+</strong>
                    <span>Tickets Booked</span>
                </div>
                <div class="trust-divider"></div>
                <div class="trust-stat">
                    <strong>Direct</strong>
                    <span>Host Payouts</span>
                </div>
                <div class="trust-divider"></div>
                <div class="trust-stat">
                    <strong>Bank-Grade</strong>
                    <span>Encrypted Safety</span>
                </div>
            </div>
        </div>
    </section>

    <!-- Right Panel: Refined Form Card -->
    <div class="auth-card">
        <div class="auth-card-header">
            <h1>Welcome back</h1>
            <p>Please enter your credentials to access your account.</p>
        </div>

        <div class="auth-message" aria-live="polite" style="display:none;"></div>

        <form id="login-form" method="POST" action="{{ route('login') }}" novalidate>
            @csrf
            
            <div class="auth-field-group">
                <label for="login-email" class="auth-label">Email Address</label>
                <div class="auth-input-wrapper">
                    <span class="auth-input-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                            <polyline points="22,6 12,13 2,6"></polyline>
                        </svg>
                    </span>
                    <input type="email" name="email" id="login-email" class="auth-input" value="{{ old('email') }}" required placeholder="you@example.com" autocomplete="email" />
                </div>
                @error('email')
                    <span class="text-error-message">{{ $message }}</span>
                @enderror
            </div>

            <div class="auth-field-group">
                <label for="login-password" class="auth-label">Password</label>
                <div class="auth-input-wrapper password-input-wrapper">
                    <span class="auth-input-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                            <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                        </svg>
                    </span>
                    <input type="password" name="password" id="login-password" class="auth-input has-toggle" required placeholder="••••••••" autocomplete="current-password" />
                    <button type="button" class="password-toggle-btn" aria-label="Show password" aria-pressed="false" title="Show password">
                        <svg class="eye-show" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        <svg class="eye-hide" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:none;"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                    </button>
                </div>
                @error('password')
                    <span class="text-error-message">{{ $message }}</span>
                @enderror
            </div>

            <div class="auth-options">
                <label class="auth-checkbox-label">
                    <input type="checkbox" name="remember" id="remember-me" value="1" {{ old('remember') ? 'checked' : '' }}>
                    <span class="custom-checkbox"></span>
                    <span class="checkbox-text">Keep me signed in</span>
                </label>
                <a href="{{ route('forgot-password') }}" class="auth-link-forgot">Forgot password?</a>
            </div>

            <button type="submit" class="auth-submit-btn">
                <span>Sign In to Account</span>
                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                    <polyline points="12 5 19 12 12 19"></polyline>
                </svg>
            </button>
        </form>

        <div class="auth-card-divider">
            <span>New to <?= e($siteName) ?>?</span>
        </div>

        <div class="auth-foot">
            <a href="{{ route('register') }}" class="auth-secondary-btn">
                Create Free Account
            </a>
        </div>
    </div>
</div>
@endsection
