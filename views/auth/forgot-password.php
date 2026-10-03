<?php

/**
 * Forgot Password View
 *
 * This view displays the forgot password form for users.
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
Forgot Password - <?= e($siteName) ?>
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
            <span class="panel-badge">Forgot password</span>
            <h2>Reset your password easily</h2>
            <p class="lede">We'll send a recovery link to your registered email address so you can regain access
                immediately without interruptions.</p>
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
                    <strong>Secure recovery flow</strong>
                    <span>Encrypted token links ensure safe password modification.</span>
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
                    <strong>Clear status guidance</strong>
                    <span>Immediate confirmations notify you once email passes through.</span>
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
                    <strong>Fast mobile-friendly layout</strong>
                    <span>Request recoveries easily on any smartphone or tablet.</span>
                </div>
            </li>
        </ul>
    </section>

    <!-- Right Panel: Form Card -->
    <div class="auth-card">
        <div class="auth-card-header">
            <h1>Reset password</h1>
            <p class="lede" style="margin-top:0.25rem;">Enter your email address below and we'll transmit a password
                recovery link.</p>
        </div>

        <div class="auth-message" aria-live="polite" style="display:none;"></div>

        <form id="forgot-form" method="POST" action="{{ route('forgot-password') }}">
            @csrf
            <label>Email Address
                <input type="email" name="email" id="forgot-email" required placeholder="you@example.com"
                    autocomplete="email">
            </label>

            <button type="submit" class="admin-action-btn primary auth-submit-btn">
                Send Reset Link
            </button>
        </form>

        <div class="auth-foot">
            <a href="{{ route('login') }}" class="auth-link" style="font-size:0.88rem;">Back to sign in</a>
        </div>
    </div>
</div>
@endsection