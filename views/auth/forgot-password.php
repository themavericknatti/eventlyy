<?php

/**
 * Forgot Password View
 *
 * Professional password recovery request form with theme-adaptive styling.
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
Forgot Password - <?= e($siteName) ?>
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
    <!-- Left Showcase: Reassurance & Help -->
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
                    Account Recovery
                </span>
                <h2>We'll get you back to the front row.</h2>
                <p class="showcase-lede">Lost your key? Don't worry. Enter your registered email address and we'll transmit a secure, one-click password recovery link immediately.</p>
            </div>

            <!-- Recovery Security Card -->
            <div class="auth-ticket-preview recovery-preview" aria-hidden="true">
                <div class="ticket-header-strip">
                    <span class="ticket-type-tag">ENCRYPTED RECOVERY • 60-MIN VALIDITY</span>
                    <span class="ticket-live-dot"><span class="pulsing-dot"></span> SECURE</span>
                </div>
                <div class="ticket-body">
                    <div class="ticket-meta">
                        <span class="ticket-eyebrow">Recovery Flow</span>
                        <h4 class="ticket-event-title">Fast, Safe Re-entry</h4>
                        <p class="ticket-venue">
                            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                            End-to-End Cryptographic Token
                        </p>
                    </div>
                    <div class="ticket-qr-box">
                        <div class="ticket-qr-matrix">
                            <div class="qr-corner top-left"></div>
                            <div class="qr-corner top-right"></div>
                            <div class="qr-corner bottom-left"></div>
                            <div class="qr-pattern-grid"></div>
                        </div>
                        <span class="ticket-qr-label">Token Auth</span>
                    </div>
                </div>
                <div class="ticket-stub-perforation">
                    <span class="perforation-notch left"></span>
                    <span class="perforation-line"></span>
                    <span class="perforation-notch right"></span>
                </div>
                <div class="ticket-footer-strip">
                    <span>Check spam or junk if not received</span>
                    <span>Single-use token</span>
                </div>
            </div>

            <!-- Trust Strip -->
            <div class="auth-trust-strip">
                <div class="trust-stat">
                    <strong>Instant</strong>
                    <span>Email Dispatch</span>
                </div>
                <div class="trust-divider"></div>
                <div class="trust-stat">
                    <strong>60 Mins</strong>
                    <span>Token Expiry</span>
                </div>
                <div class="trust-divider"></div>
                <div class="trust-stat">
                    <strong>100%</strong>
                    <span>Secure Reset</span>
                </div>
            </div>
        </div>
    </section>

    <!-- Right Panel: Form Card -->
    <div class="auth-card">
        <div class="auth-card-header">
            <h1>Reset your password</h1>
            <p>Enter your email below and we'll send you a password recovery link.</p>
        </div>

        <div class="auth-message" aria-live="polite" style="display:none;"></div>

        <form id="forgot-form" method="POST" action="{{ route('forgot-password') }}" novalidate>
            @csrf
            
            <div class="auth-field-group">
                <label for="forgot-email" class="auth-label">Email Address</label>
                <div class="auth-input-wrapper">
                    <span class="auth-input-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                            <polyline points="22,6 12,13 2,6"></polyline>
                        </svg>
                    </span>
                    <input type="email" name="email" id="forgot-email" class="auth-input" value="{{ old('email') }}" required placeholder="you@example.com" autocomplete="email" />
                </div>
                @error('email')
                    <span class="text-error-message">{{ $message }}</span>
                @enderror
            </div>

            <button type="submit" class="auth-submit-btn">
                <span>Send Recovery Link</span>
                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="22" y1="2" x2="11" y2="13"></line>
                    <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                </svg>
            </button>
        </form>

        <div class="auth-card-divider">
            <span>Remembered your password?</span>
        </div>

        <div class="auth-foot">
            <a href="{{ route('login') }}" class="auth-secondary-btn">
                Back to Sign In
            </a>
        </div>
    </div>
</div>
@endsection
