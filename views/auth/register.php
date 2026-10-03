<?php

/**
 * Register View
 *
 * This view displays the registration form for users.
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
Register for <?= e($siteName) ?> - Create Your Account and Start Managing Events
@endsection

@section('content')
<div class="auth-top-bar">
    <a href="{{ route('/') }}" class="auth-back-link">
        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5"
            stroke-linecap="round" stroke-linejoin="round">
            <line x1="19" y1="12" x2="5" y2="12" />
            <polyline points="12 19 5 12 12 5" />
        </svg>
        Back to home
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
            <span class="panel-badge">Create Account</span>
            <h2>Join <?= e($siteName) ?> in seconds</h2>
            <p class="lede">Set up your account and manage events, ticket distribution, and payment payouts from one
                polished dashboard.</p>
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
                    <strong>Fast host registration</strong>
                    <span>Instantly verify and configure bank payouts.</span>
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
                    <strong>Interactive features</strong>
                    <span>Live scanning console and physical ticket pass.</span>
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
                    <strong>Fully responsive</strong>
                    <span>Access your admin panel on desktop, tablet, or phone.</span>
                </div>
            </li>
        </ul>
    </section>

    <!-- Right Panel: Form Card -->
    <div class="auth-card">
        <div class="auth-card-header">
            <h1>Create account</h1>
            <p>Get started with your free organizer account today.</p>
        </div>

        <div class="auth-message" aria-live="polite" style="display:none;"></div>

        <form id="register-form" method="POST" action="{{ route('register') }}">
            @csrf
            <label>Full Name
                <input type="text" name="name" id="reg-name" value="{{ old('name') }}" required placeholder="Jane Doe" autocomplete="name">
            </label>
            <label>Email Address
                <input type="email" name="email" id="reg-email" value="{{ old('email') }}" required placeholder="you@example.com"
                    autocomplete="email">
            </label>
            <label>Account Type
                <select name="role" id="reg-role">
                    <option value="user">Attendee / Ticket Buyer</option>
                    <option value="event-organizer">Event Host &amp; Organizer</option>
                </select>
            </label>
            <label>Password
                <div class="password-input-wrapper">
                    <input type="password" name="password" id="reg-password" required placeholder="At least 8 characters"
                        autocomplete="new-password">
                    <button type="button" class="password-toggle-btn" aria-label="Show password" aria-pressed="false" title="Show password">
                        <svg class="eye-show" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        <svg class="eye-hide" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:none;"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                    </button>
                </div>
            </label>
            <label>Confirm Password
                <div class="password-input-wrapper">
                    <input type="password" name="passwordConfirm" id="reg-password-confirmation" required placeholder="At least 8 characters"
                        autocomplete="new-password">
                    <button type="button" class="password-toggle-btn" aria-label="Show password" aria-pressed="false" title="Show password">
                        <svg class="eye-show" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        <svg class="eye-hide" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:none;"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                    </button>
                </div>
            </label>

            <button type="submit" class="admin-action-btn primary auth-submit-btn">
                Create Account
            </button>
        </form>

        <div class="auth-foot">
            <p>Already have an account? <a href="{{ route('login') }}" class="auth-link">Sign in</a></p>
        </div>
    </div>
</div>
@endsection