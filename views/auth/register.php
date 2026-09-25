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
            <span class="brand-letter"><?= e(mb_substr($siteName, 0, 1)) ?></span>
            <span><?= e($siteName) ?> Platform</span>
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
                <select name="role" id="reg-role" style="width:100%; padding:0.85rem 1rem; border-radius:14px; border:1px solid rgba(var(--muted),0.18); background:rgb(var(--card)); color:rgb(var(--text)); font-family:inherit; font-size:0.95rem;">
                    <option value="user">Attendee / Ticket Buyer</option>
                    <option value="event-organizer">Event Host &amp; Organizer</option>
                </select>
            </label>
            <label>Password
                <input type="password" name="password" id="reg-password" required placeholder="At least 8 characters"
                    autocomplete="new-password">
            </label>
            <label>Confirm Password
                <input type="password" name="passwordConfirm" id="reg-password-confirmation" required placeholder="At least 8 characters"
                    autocomplete="new-password">
            </label>

            <button type="submit" class="admin-action-btn primary"
                style="width:100%; height:48px; font-weight:700; font-size:0.95rem; margin-top:1rem; border-radius:14px; justify-content:center;">
                Create Account
            </button>
        </form>

        <div class="auth-foot">
            <p>Already have an account? <a href="{{ route('login') }}" class="auth-link">Sign in</a></p>
        </div>
    </div>
</div>
@endsection