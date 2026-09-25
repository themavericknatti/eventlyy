<?php

/**
 * Resend Email Verification View
 *
 * This view allows users to request a new account email verification link.
 *
 * @package Evently
 * @subpackage Views/Auth
 * @author Your Name <Celio Natti>
 * @license MIT License
 * @version 1.0.0
 */

?>

@extends('layouts/auth')

@section('title')
Resend Email Verification - <?= e(site_setting('site_title', 'Evently')) ?>
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
    <!-- Left Panel: Brand info -->
    <section class="auth-panel">
        <div class="auth-brand-badge">
            <span class="brand-letter"><?= strtoupper(substr(site_setting('site_title', 'Evently'), 0, 1)) ?></span>
            <span><?= e(site_setting('site_title', 'Evently')) ?> Platform</span>
        </div>
        <div class="auth-panel-hero">
            <span class="panel-badge">Email Verification</span>
            <h2>Verify your account email</h2>
            <p class="lede">Verification unlocks event hosting, gate pass management, and automated host payouts on <?= e(site_setting('site_title', 'Evently')) ?>.</p>
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
                    <strong>Required for Event Hosting</strong>
                    <span>Verify your account to create and publish event listings.</span>
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
                    <strong>Secure 24-Hour Link</strong>
                    <span>Verification tokens automatically expire for your safety.</span>
                </div>
            </li>
        </ul>
    </section>

    <!-- Right Panel: Form Card -->
    <div class="auth-card">
        <div class="auth-card-header">
            <h1>Resend verification</h1>
            <p class="lede" style="margin-top:0.25rem;">Enter your email address and we'll transmit a new verification link immediately.</p>
        </div>

        <form id="resend-verification-form" method="POST" action="{{ route('resend-verification') }}">
            @csrf
            <label>Email Address
                <input type="email" name="email" id="resend-email" value="<?= e(app()->user ? app()->user->email : '') ?>" required placeholder="you@example.com"
                    autocomplete="email">
            </label>

            <button type="submit" class="admin-action-btn primary"
                style="width:100%; height:48px; font-weight:700; font-size:0.95rem; margin-top:1.5rem; border-radius:14px; justify-content:center;">
                Send Verification Link →
            </button>
        </form>

        <div class="auth-foot" style="justify-content:center;">
            <a href="{{ route('login') }}" class="auth-link" style="font-size:0.88rem;">Back to sign in</a>
        </div>
    </div>
</div>
@endsection
