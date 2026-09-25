<?php

/**
 * Login View
 *
 * This view displays the login form for users.
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
Login to <?= e($siteName) ?> - Access Your Account and Manage Your Events
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
            <span class="panel-badge">Welcome back</span>
            <h2>Sign in and manage your tickets</h2>
            <p class="lede">Access your events, review orders, and keep your ticketing experience smooth and secure.</p>
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
                    <strong>Modern login flow</strong>
                    <span>Fast sign-in with instant error feedback.</span>
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
                    <strong>Centralized bookings</strong>
                    <span>Keep track of your purchased event tickets.</span>
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
                    <strong>Responsive design</strong>
                    <span>A seamless dashboard experience on all screen sizes.</span>
                </div>
            </li>
        </ul>
    </section>

    <!-- Right Panel: Form Card -->
    <div class="auth-card">
        <div class="auth-card-header">
            <h1>Sign in</h1>
            <p>Please enter your email and password to log in.</p>
        </div>

        <div class="auth-message" aria-live="polite" style="display:none;"></div>

        <form id="login-form" method="POST" action="{{ route('login') }}" novalidate>
            @csrf
            <label>Email Address
                <input type="email" name="email" id="login-email" value="{{ old('email') }}" required
                    placeholder="you@example.com" autocomplete="email">
                @error('email')
                <span class="text-error-message">{{ $message }}</span>
                @enderror
            </label>
            <label>Password
                <input type="password" name="password" id="login-password" required placeholder="••••••••"
                    autocomplete="current-password">
                @error('password')
                <span class="text-error-message">{{ $message }}</span>
                @enderror
            </label>

            <div class="auth-options">
                <label class="auth-checkbox-label">
                    <input type="checkbox" name="remember" id="remember-me" value="1" {{ old('remember') ? 'checked' : '' }}>
                    <span>Remember me</span>
                </label>
                <a href="{{ route('forgot-password') }}" class="auth-link" style="font-weight:500;">Forgot password?</a>
            </div>

            <button type="submit" class="admin-action-btn primary"
                style="width:100%; height:48px; font-weight:700; font-size:0.95rem; border-radius:14px; justify-content:center;">
                Sign In
            </button>
        </form>

        <div class="auth-foot" style="justify-content:center;">
            <p>Don't have an account? <a href="{{ route('register') }}" class="auth-link">Create one</a></p>
        </div>
    </div>
</div>
@endsection