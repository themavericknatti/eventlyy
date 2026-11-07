<?php

/**
 * Resend Email Verification View
 *
 * Professional view allowing users to request a new email verification link.
 * Responsive with theme-adaptive styling.
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
Resend Verification - <?= e($siteName) ?>
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
        <?php if (!empty($siteLogo)): ?>
            <img src="<?= e(asset($siteLogo)) ?>" alt="<?= e($siteName) ?>" class="auth-top-logo" />
        <?php else: ?>
            <span class="auth-top-monogram"><?= e(strtoupper(mb_substr($siteName, 0, 1))) ?></span>
            <span class="auth-top-name"><?= e($siteName) ?></span>
        <?php endif; ?>
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
                <?php if (!empty($siteLogo)): ?>
                    <img src="<?= e(asset($siteLogo)) ?>" alt="<?= e($siteName) ?>" class="auth-brand-logo" />
                <?php else: ?>
                    <span class="brand-letter"><?= e(strtoupper(mb_substr($siteName, 0, 1))) ?></span>
                    <span class="brand-title"><?= e($siteName) ?></span>
                <?php endif; ?>
            </div>

            <div class="auth-showcase-hero">
                <span class="showcase-pill">
                    <svg viewBox="0 0 24 24" width="14" height="14" fill="currentColor">
                        <path d="M12 2l2.4 7.4h7.6l-6.2 4.5 2.4 7.4-6.2-4.5-6.2 4.5 2.4-7.4-6.2-4.5h7.6z"/>
                    </svg>
                    Email Verification
                </span>
                <h2>Unlock the full power of <?= e($siteName) ?>.</h2>
                <p class="showcase-lede">Email confirmation validates your account security, enables direct ticket hosting, and unlocks automated bank payouts for your events.</p>
            </div>

            <!-- Verification Ticket Card -->
            <div class="auth-ticket-preview resend-preview" aria-hidden="true">
                <div class="ticket-header-strip">
                    <span class="ticket-type-tag">IDENTITY VERIFICATION • 24-HR LINK</span>
                    <span class="ticket-live-dot"><span class="pulsing-dot"></span> PENDING CHECK</span>
                </div>
                <div class="ticket-body">
                    <div class="ticket-meta">
                        <span class="ticket-eyebrow">Unlocked Capabilities</span>
                        <h4 class="ticket-event-title">Host &amp; Organizer Suite</h4>
                        <p class="ticket-venue">
                            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                            Direct Stripe &amp; Bank Deposits
                        </p>
                    </div>
                    <div class="ticket-qr-box">
                        <div class="ticket-qr-matrix">
                            <div class="qr-corner top-left"></div>
                            <div class="qr-corner top-right"></div>
                            <div class="qr-corner bottom-left"></div>
                            <div class="qr-pattern-grid"></div>
                        </div>
                        <span class="ticket-qr-label">Awaiting Link</span>
                    </div>
                </div>
                <div class="ticket-stub-perforation">
                    <span class="perforation-notch left"></span>
                    <span class="perforation-line"></span>
                    <span class="perforation-notch right"></span>
                </div>
                <div class="ticket-footer-strip">
                    <span>One-click email activation</span>
                    <span>No spam guarantee</span>
                </div>
            </div>

            <!-- Trust Strip -->
            <div class="auth-trust-strip">
                <div class="trust-stat">
                    <strong>24 Hours</strong>
                    <span>Link Validity</span>
                </div>
                <div class="trust-divider"></div>
                <div class="trust-stat">
                    <strong>1-Click</strong>
                    <span>Activation</span>
                </div>
                <div class="trust-divider"></div>
                <div class="trust-stat">
                    <strong>Protected</strong>
                    <span>Account Security</span>
                </div>
            </div>
        </div>
    </section>

    <!-- Right Panel: Resend Form Card -->
    <div class="auth-card">
        <div class="auth-card-header">
            <h1>Resend verification</h1>
            <p>Enter your email address and we'll dispatch a fresh verification link immediately.</p>
        </div>

        <div class="auth-message" aria-live="polite" style="display:none;"></div>

        <form id="resend-verification-form" method="POST" action="{{ route('resend-verification') }}" novalidate>
            @csrf
            
            <div class="auth-field-group">
                <label for="resend-email" class="auth-label">Email Address</label>
                <div class="auth-input-wrapper">
                    <span class="auth-input-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                            <polyline points="22,6 12,13 2,6"></polyline>
                        </svg>
                    </span>
                    <input type="email" name="email" id="resend-email" class="auth-input" value="<?= e(app()->user ? app()->user->email : '') ?>" required placeholder="you@example.com" autocomplete="email" />
                </div>
                @error('email')
                    <span class="text-error-message">{{ $message }}</span>
                @enderror
            </div>

            <button type="submit" class="auth-submit-btn">
                <span>Send Verification Link</span>
                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="22" y1="2" x2="11" y2="13"></line>
                    <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                </svg>
            </button>
        </form>

        <div class="auth-card-divider">
            <span>Ready to sign in?</span>
        </div>

        <div class="auth-foot">
            <a href="{{ route('login') }}" class="auth-secondary-btn">
                Back to Sign In
            </a>
        </div>
    </div>
</div>
@endsection
