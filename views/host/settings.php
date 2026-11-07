<?php

/**
 * Host Settings View
 *
 * Professional Host Command Center for Account Profile, Settlement Bank Configuration,
 * Performance Metrics, and Visual Preferences.
 *
 * @package Evently
 * @subpackage Views/Host
 * @version 2.1.0
 */

$siteName = site_setting('site_title', 'Eventlyy');
$themePrimary = function_exists('theme_primary_color') ? theme_primary_color() : '#108a55';
$themeSecondary = function_exists('theme_secondary_color') ? theme_secondary_color() : '#1aae6c';
$themePrimaryRgb = function_exists('theme_primary_color') ? implode(' ', sscanf(ltrim($themePrimary, '#'), '%02x%02x%02x')) : '16 138 85';

$banksList = $supportedBanks ?? \Ace\PaystackService::getSupportedBanks();
$currentBank = $profile['payout_bank'] ?? '';
$payoutAccount = $profile['payout_account'] ?? '';
$isBankConfigured = !empty($currentBank) && !empty($payoutAccount);
?>

@extends('layouts/host')

@section('title')
Host Settings - Manage Your <?= e($siteName) ?> Account
@endsection

@section('content')
<div class="hs-shell">

    <!-- ─── BREADCRUMB & EXECUTIVE HEADER ─── -->
    <header class="hs-header-card">
        <div class="hs-header-top">
            <nav class="hs-breadcrumb" aria-label="Breadcrumb">
                <a href="{{ route('host/dashboard') }}">Host Dashboard</a>
                <span class="hs-bc-sep">/</span>
                <span class="hs-bc-current">Account Settings</span>
            </nav>

            <div class="hs-header-badges">
                <span class="hs-badge hs-badge--status">● Host Verified</span>
                <?php if ($isBankConfigured): ?>
                    <span class="hs-badge hs-badge--success">✓ Payouts Linked</span>
                <?php else: ?>
                    <span class="hs-badge hs-badge--warning">⚠️ Settlement Pending</span>
                <?php endif; ?>
            </div>
        </div>

        <div class="hs-header-body">
            <div class="hs-header-info">
                <h1 class="hs-page-title">Host Account &amp; Payout Settings</h1>
                <p class="hs-page-desc">Manage your organizer profile, automated bank settlement destination, and account preferences.</p>
            </div>
            <div class="hs-header-actions">
                <a href="{{ route('host/financials') }}" class="hs-btn hs-btn-outline">
                    <span>💳</span> Payouts &amp; Settlements →
                </a>
                <a href="{{ route('host/dashboard') }}" class="hs-btn hs-btn-ghost">
                    <span>←</span> Dashboard
                </a>
            </div>
        </div>
    </header>

    <!-- ─── TOP 4 HOST PERFORMANCE BENTO CARDS ─── -->
    <div class="hs-kpi-grid">
        <!-- Rating Card -->
        <div class="hs-kpi-card">
            <div class="hs-kpi-top">
                <span class="hs-kpi-icon">⭐</span>
                <span class="hs-badge hs-badge--neutral">Reputation</span>
            </div>
            <div class="hs-kpi-body">
                <div class="hs-kpi-val"><?= number_format((float)($profile['rating'] ?? 5.00), 1) ?> <span class="hs-kpi-subval">/ 5.0</span></div>
                <div class="hs-kpi-label">Host Trust Score</div>
            </div>
            <div class="hs-kpi-foot">
                <span>Verified by attendee feedback</span>
            </div>
        </div>

        <!-- Live Events Card -->
        <div class="hs-kpi-card">
            <div class="hs-kpi-top">
                <span class="hs-kpi-icon">🎪</span>
                <span class="hs-badge hs-badge--brand">Live Active</span>
            </div>
            <div class="hs-kpi-body">
                <div class="hs-kpi-val"><?= (int)($liveEventsCount ?? 0) ?></div>
                <div class="hs-kpi-label">Published Events</div>
            </div>
            <div class="hs-kpi-foot">
                <span>Currently open for bookings</span>
            </div>
        </div>

        <!-- Next Payout Card -->
        <div class="hs-kpi-card">
            <div class="hs-kpi-top">
                <span class="hs-kpi-icon">💰</span>
                <span class="hs-badge hs-badge--success">Escrow Settle</span>
            </div>
            <div class="hs-kpi-body">
                <div class="hs-kpi-val hs-text-success"><?= format_price($pendingPayout ?? 0) ?></div>
                <div class="hs-kpi-label">Upcoming Payout Balance</div>
            </div>
            <div class="hs-kpi-foot">
                <span>Disbursed automatically on completion</span>
            </div>
        </div>

        <!-- Checked-in Attendees Card -->
        <div class="hs-kpi-card">
            <div class="hs-kpi-top">
                <span class="hs-kpi-icon">🎟️</span>
                <span class="hs-badge hs-badge--neutral">Gate Turnout</span>
            </div>
            <div class="hs-kpi-body">
                <div class="hs-kpi-val"><?= number_format((int)($checkedInCount ?? 0)) ?></div>
                <div class="hs-kpi-label">Verified Check-Ins</div>
            </div>
            <div class="hs-kpi-foot">
                <span>Total successful admissions</span>
            </div>
        </div>
    </div>

    <!-- ─── MAIN SETTINGS WORKSPACE ─── -->
    <div class="hs-workspace-grid">
        
        <!-- Left Column: Primary Settings Form -->
        <div class="hs-workspace-main">
            <div class="hs-card">
                <div class="hs-card-header">
                    <div class="hs-card-title-group">
                        <span class="hs-card-icon">🏛️</span>
                        <div>
                            <h2 class="hs-card-title">Profile &amp; Bank Settlement Details</h2>
                            <p class="hs-card-sub">This information is displayed on your public event pages and used for automated bank transfers.</p>
                        </div>
                    </div>
                </div>

                <div class="hs-card-body">
                    <form method="POST" action="{{ route('host/settings') }}" class="hs-form">
                        <?= csrf_field() ?>

                        <!-- Public Identity Group -->
                        <div class="hs-form-section">
                            <h3 class="hs-section-title">Organizer Public Profile</h3>
                            
                            <div class="hs-field-grid">
                                <div class="hs-field">
                                    <label for="host_name" class="hs-label">Host / Organization Name <span class="hs-req">*</span></label>
                                    <input type="text" id="host_name" name="host_name" class="hs-input" placeholder="e.g. Lagos Creative Collective" value="<?= e($user->name) ?>" required />
                                </div>

                                <div class="hs-field">
                                    <label for="phone_number" class="hs-label">Direct Contact Phone</label>
                                    <input type="tel" id="phone_number" name="phone_number" class="hs-input" placeholder="+234 (800) 123-4567" value="<?= e($user->phone ?? '') ?>" />
                                </div>
                            </div>

                            <div class="hs-field hs-mt-3">
                                <label for="email" class="hs-label">Registered Account Email</label>
                                <div class="hs-input-lock-wrap">
                                    <input type="email" id="email" class="hs-input is-locked" value="<?= e($user->email) ?>" disabled />
                                    <span class="hs-lock-icon" title="Email is locked for account security">🔒</span>
                                </div>
                                <span class="hs-help-text">Contact platform administration if you need to update your registered host email address.</span>
                            </div>

                            <div class="hs-field hs-mt-3">
                                <label for="bio" class="hs-label">Host Bio &amp; About Story</label>
                                <textarea id="bio" name="bio" class="hs-textarea" rows="3" placeholder="Tell attendees about your experience, past festivals, brand mission, and what to expect..."><?= e($profile['bio'] ?? '') ?></textarea>
                            </div>
                        </div>

                        <!-- Banking & Payout Settlement Group -->
                        <div class="hs-form-section hs-mt-6">
                            <div class="hs-section-header">
                                <div>
                                    <h3 class="hs-section-title">🏦 Bank Settlement Account</h3>
                                    <p class="hs-section-desc">Net ticket revenues from your events will be routed directly to this verified NUBAN account.</p>
                                </div>
                                <?php if ($isBankConfigured): ?>
                                    <span class="hs-badge hs-badge--success">Active Destination</span>
                                <?php endif; ?>
                            </div>

                            <div class="hs-field-grid">
                                <div class="hs-field">
                                    <label for="settings-payout-bank" class="hs-label">Payout Bank (Paystack Supported) <span class="hs-req">*</span></label>
                                    <select name="payout_bank" id="settings-payout-bank" class="hs-select" required>
                                        <option value="">-- Choose Your Bank --</option>
                                        <?php foreach ($banksList as $b): ?>
                                            <option value="<?= e($b['name']) ?>" data-code="<?= e($b['code']) ?>" <?= (stripos($currentBank, $b['name']) !== false || $currentBank === $b['name']) ? 'selected' : '' ?>>
                                                <?= e($b['name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="hs-field">
                                    <label for="payout_account" class="hs-label">Account Number (10-Digit NUBAN) <span class="hs-req">*</span></label>
                                    <input type="text" id="payout_account" name="payout_account" class="hs-input hs-font-mono" placeholder="e.g. 0123456789" maxlength="10" pattern="[0-9]{10}" value="<?= e($payoutAccount) ?>" required />
                                </div>
                            </div>

                            <input type="hidden" name="payout_routing" id="settings-payout-routing" value="<?= e($profile['payout_routing'] ?? '') ?>" />

                            <div class="hs-notice-box hs-mt-3">
                                <span class="hs-notice-icon">🛡️</span>
                                <div class="hs-notice-text">
                                    <strong>Automated Payout Safeguards:</strong> Transfers are triggered upon event completion window expiration or through early escrow release requests approved by platform administrators.
                                </div>
                            </div>
                        </div>

                        <!-- Save Submit Button -->
                        <div class="hs-form-foot hs-mt-6">
                            <button type="submit" class="hs-btn hs-btn-primary hs-btn-lg">
                                <span>💾</span> Save Host Settings
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Right Column: Account Specs & Preferences -->
        <div class="hs-workspace-side">
            
            <!-- Host Specs Card -->
            <div class="hs-card">
                <div class="hs-card-header">
                    <div class="hs-card-title-group">
                        <span class="hs-card-icon">📋</span>
                        <div>
                            <h3 class="hs-card-title">Account Specifications</h3>
                            <p class="hs-card-sub">Core account status and parameters.</p>
                        </div>
                    </div>
                </div>
                <div class="hs-card-body hs-p-0">
                    <div class="hs-specs-list">
                        <div class="hs-spec-item">
                            <span>Host Member Since</span>
                            <strong><?= date('M j, Y', strtotime($user->created_at ?? 'now')) ?></strong>
                        </div>
                        <div class="hs-spec-item">
                            <span>Account Status</span>
                            <span class="hs-badge hs-badge--success">● Active Host</span>
                        </div>
                        <div class="hs-spec-item">
                            <span>Platform Timezone</span>
                            <strong><?= e(config('timezone', 'Africa/Lagos')) ?></strong>
                        </div>
                        <div class="hs-spec-item">
                            <span>Payout Method</span>
                            <strong>Direct NUBAN Wire</strong>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Appearance & Theme Toggle Card -->
            <div class="hs-card">
                <div class="hs-card-header">
                    <div class="hs-card-title-group">
                        <span class="hs-card-icon">🌓</span>
                        <div>
                            <h3 class="hs-card-title">Display Preferences</h3>
                            <p class="hs-card-sub">Switch between dark and light workspace themes.</p>
                        </div>
                    </div>
                </div>
                <div class="hs-card-body">
                    <p class="hs-theme-desc">Your theme preference is saved automatically across all host and client pages on this device.</p>
                    <button type="button" class="hs-btn hs-btn-theme-toggle" data-theme-toggle="true" aria-label="Toggle theme mode">
                        <span>🌓</span> Toggle Dark / Light Theme
                    </button>
                </div>
            </div>

        </div>

    </div>

</div>

<!-- ─── MODERN RESPONSIVE STYLES ─── -->
<style>
.hs-shell {
    --hs-primary: var(--primary, <?= $themePrimary ?>);
    --hs-primary-hover: var(--primary-hover, <?= $themeSecondary ?>);
    --hs-primary-light: rgba(var(--primary-rgb, <?= $themePrimaryRgb ?>), 0.12);
    --hs-primary-border: rgba(var(--primary-rgb, <?= $themePrimaryRgb ?>), 0.28);
    
    /* Default / Light Mode */
    --hs-bg-card: rgb(var(--card, 255 255 255));
    --hs-bg-subtle: #f8fafc;
    --hs-bg-subtle-hover: #f1f5f9;
    --hs-border: var(--border-color, #e2e8f0);
    --hs-text-main: rgb(var(--text, 15 23 42));
    --hs-text-muted: rgb(var(--muted, 100 116 139));
    --hs-pill-bg: #f1f5f9;
    --hs-pill-text: #475569;
    --hs-input-bg: #ffffff;
    --hs-input-border: var(--border-color, #cbd5e1);
    --hs-radius: 12px;
    --hs-radius-sm: 8px;
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
    margin-bottom: 3rem;
}

/* ─── FULL DARK MODE SUPPORT ─── */
:root[data-theme='dark'] .hs-shell,
[data-theme='dark'] .hs-shell,
body[data-theme='dark'] .hs-shell {
    --hs-bg-card: rgb(var(--card, 18 21 27));
    --hs-bg-subtle: rgba(255, 255, 255, 0.04);
    --hs-bg-subtle-hover: rgba(255, 255, 255, 0.08);
    --hs-border: rgba(255, 255, 255, 0.09);
    --hs-text-main: rgb(var(--text, 243 245 247));
    --hs-text-muted: rgb(var(--muted, 152 162 173));
    --hs-pill-bg: rgba(255, 255, 255, 0.08);
    --hs-pill-text: #e2e8f0;
    --hs-input-bg: rgba(255, 255, 255, 0.05);
    --hs-input-border: rgba(255, 255, 255, 0.12);
    --hs-primary-light: rgba(var(--primary-rgb, <?= $themePrimaryRgb ?>), 0.22);
    --hs-primary-border: rgba(var(--primary-rgb, <?= $themePrimaryRgb ?>), 0.4);
}

/* Header & Breadcrumbs */
.hs-header-card {
    background: var(--hs-bg-card);
    border: 1px solid var(--hs-border);
    border-radius: var(--hs-radius);
    padding: 1.5rem;
    box-shadow: 0 1px 3px rgba(0,0,0,0.04);
}
:root[data-theme='dark'] .hs-header-card,
[data-theme='dark'] .hs-header-card {
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.35);
}
.hs-header-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 0.75rem;
    margin-bottom: 0.75rem;
}
.hs-breadcrumb {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    font-size: 0.85rem;
    color: var(--hs-text-muted);
}
.hs-breadcrumb a {
    color: var(--hs-primary);
    text-decoration: none;
    font-weight: 500;
}
.hs-breadcrumb a:hover {
    text-decoration: underline;
}
.hs-bc-sep {
    color: var(--hs-border);
}
.hs-bc-current {
    color: var(--hs-text-main);
    font-weight: 600;
}
.hs-header-badges {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 0.5rem;
}
.hs-header-body {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 1.5rem;
    flex-wrap: wrap;
}
.hs-header-info {
    flex: 1 1 450px;
}
.hs-page-title {
    font-size: 1.7rem;
    font-weight: 800;
    color: var(--hs-text-main);
    line-height: 1.25;
    margin: 0 0 0.35rem 0;
}
.hs-page-desc {
    font-size: 0.9rem;
    color: var(--hs-text-muted);
    margin: 0;
}
.hs-header-actions {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex-wrap: wrap;
}

/* Bento KPI Grid */
.hs-kpi-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 1rem;
}
.hs-kpi-card {
    background: var(--hs-bg-card);
    border: 1px solid var(--hs-border);
    border-radius: var(--hs-radius);
    padding: 1.25rem;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    box-shadow: 0 1px 3px rgba(0,0,0,0.03);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
:root[data-theme='dark'] .hs-kpi-card,
[data-theme='dark'] .hs-kpi-card {
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.35);
}
.hs-kpi-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.06);
}
.hs-kpi-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 0.75rem;
}
.hs-kpi-icon {
    font-size: 1.35rem;
}
.hs-kpi-val {
    font-size: 1.75rem;
    font-weight: 800;
    color: var(--hs-text-main);
    line-height: 1.1;
    margin-bottom: 0.2rem;
}
.hs-kpi-subval {
    font-size: 1rem;
    font-weight: 500;
    color: var(--hs-text-muted);
}
.hs-kpi-label {
    font-size: 0.82rem;
    color: var(--hs-text-muted);
    font-weight: 500;
}
.hs-kpi-foot {
    margin-top: 1rem;
    padding-top: 0.75rem;
    border-top: 1px solid var(--hs-border);
    font-size: 0.76rem;
    color: var(--hs-text-muted);
}

/* Workspace Grid */
.hs-workspace-grid {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 1.5rem;
    align-items: start;
}
.hs-workspace-main,
.hs-workspace-side {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

/* Cards */
.hs-card {
    background: var(--hs-bg-card);
    border: 1px solid var(--hs-border);
    border-radius: var(--hs-radius);
    box-shadow: 0 1px 3px rgba(0,0,0,0.03);
    overflow: hidden;
}
:root[data-theme='dark'] .hs-card,
[data-theme='dark'] .hs-card {
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.35);
}
.hs-card-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1.25rem 1.5rem;
    border-bottom: 1px solid var(--hs-border);
    background: var(--hs-bg-subtle);
}
.hs-card-title-group {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}
.hs-card-icon {
    font-size: 1.35rem;
}
.hs-card-title {
    font-size: 1.1rem;
    font-weight: 700;
    color: var(--hs-text-main);
    margin: 0;
}
.hs-card-sub {
    font-size: 0.8rem;
    color: var(--hs-text-muted);
    margin: 0.15rem 0 0 0;
}
.hs-card-body {
    padding: 1.5rem;
}

/* Forms */
.hs-form-section {
    display: flex;
    flex-direction: column;
}
.hs-section-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 1rem;
    margin-bottom: 1rem;
}
.hs-section-title {
    font-size: 1rem;
    font-weight: 700;
    color: var(--hs-text-main);
    margin: 0 0 0.25rem 0;
}
.hs-section-desc {
    font-size: 0.82rem;
    color: var(--hs-text-muted);
    margin: 0;
}
.hs-field-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem 1.25rem;
}
.hs-field {
    display: flex;
    flex-direction: column;
    gap: 0.35rem;
}
.hs-label {
    font-size: 0.82rem;
    font-weight: 600;
    color: var(--hs-text-main);
}
.hs-req {
    color: #ef4444;
}
.hs-input,
.hs-select,
.hs-textarea {
    width: 100%;
    padding: 0.65rem 0.95rem;
    border-radius: var(--hs-radius-sm);
    border: 1px solid var(--hs-input-border);
    background: var(--hs-input-bg);
    color: var(--hs-text-main);
    font-size: 0.9rem;
    font-family: inherit;
    transition: border-color 0.15s ease, box-shadow 0.15s ease;
    box-sizing: border-box;
}
.hs-input:focus,
.hs-select:focus,
.hs-textarea:focus {
    outline: none;
    border-color: var(--hs-primary);
    box-shadow: 0 0 0 3px var(--hs-primary-light);
}
.hs-input.is-locked {
    opacity: 0.7;
    background: var(--hs-bg-subtle);
    cursor: not-allowed;
}
.hs-input-lock-wrap {
    position: relative;
    display: flex;
    align-items: center;
}
.hs-lock-icon {
    position: absolute;
    right: 0.85rem;
    opacity: 0.6;
    pointer-events: none;
}
.hs-help-text {
    font-size: 0.76rem;
    color: var(--hs-text-muted);
    margin-top: 0.2rem;
}
.hs-font-mono {
    font-family: ui-monospace, monospace;
    letter-spacing: 0.05em;
}

/* Notice Box */
.hs-notice-box {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
    padding: 0.85rem 1rem;
    background: var(--hs-bg-subtle);
    border: 1px solid var(--hs-border);
    border-radius: var(--hs-radius-sm);
    font-size: 0.82rem;
    color: var(--hs-text-muted);
}
.hs-notice-icon {
    font-size: 1.15rem;
}
.hs-notice-text strong {
    color: var(--hs-text-main);
}

/* Buttons */
.hs-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.4rem;
    padding: 0.55rem 1rem;
    border-radius: var(--hs-radius-sm);
    font-size: 0.85rem;
    font-weight: 600;
    text-decoration: none;
    border: 1px solid transparent;
    cursor: pointer;
    transition: all 0.15s ease-in-out;
}
.hs-btn-primary {
    background: var(--hs-primary);
    color: #ffffff !important;
}
.hs-btn-primary:hover {
    background: var(--hs-primary-hover);
    box-shadow: 0 2px 8px rgba(var(--primary-rgb, <?= $themePrimaryRgb ?>), 0.35);
}
.hs-btn-lg {
    padding: 0.75rem 1.5rem;
    font-size: 0.95rem;
    border-radius: 10px;
}
.hs-btn-outline {
    background: transparent;
    color: var(--hs-primary);
    border-color: var(--hs-primary-border);
}
.hs-btn-outline:hover {
    background: var(--hs-primary-light);
}
.hs-btn-ghost {
    background: transparent;
    color: var(--hs-text-muted);
}
.hs-btn-ghost:hover {
    background: var(--hs-bg-subtle);
    color: var(--hs-text-main);
}
.hs-btn-theme-toggle {
    width: 100%;
    height: 48px;
    border-radius: var(--hs-radius-sm);
    font-size: 0.9rem;
    font-weight: 600;
    background: var(--hs-bg-subtle);
    border: 1px solid var(--hs-border);
    color: var(--hs-text-main);
}
.hs-btn-theme-toggle:hover {
    background: var(--hs-bg-subtle-hover);
    border-color: var(--hs-primary);
}

/* Specs List */
.hs-specs-list {
    display: flex;
    flex-direction: column;
}
.hs-spec-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0.85rem 1.5rem;
    border-bottom: 1px solid var(--hs-border);
    font-size: 0.85rem;
}
.hs-spec-item:last-child {
    border-bottom: none;
}
.hs-spec-item span {
    color: var(--hs-text-muted);
}
.hs-spec-item strong {
    color: var(--hs-text-main);
}
.hs-theme-desc {
    font-size: 0.82rem;
    color: var(--hs-text-muted);
    margin: 0 0 1rem 0;
    line-height: 1.5;
}

/* Badges */
.hs-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    padding: 0.2rem 0.55rem;
    border-radius: 9999px;
    font-size: 0.74rem;
    font-weight: 600;
}
.hs-badge--status {
    background: var(--hs-primary-light);
    color: var(--hs-primary);
}
.hs-badge--brand {
    background: var(--hs-primary-light);
    color: var(--hs-primary);
}
.hs-badge--success {
    background: #ecfdf5;
    color: #059669;
}
:root[data-theme='dark'] .hs-badge--success,
[data-theme='dark'] .hs-badge--success {
    background: rgba(16, 185, 129, 0.15);
    color: #34d399;
}
.hs-badge--warning {
    background: #fffbeb;
    color: #d97706;
}
:root[data-theme='dark'] .hs-badge--warning,
[data-theme='dark'] .hs-badge--warning {
    background: rgba(245, 158, 11, 0.15);
    color: #fbbf24;
}
.hs-badge--neutral {
    background: var(--hs-pill-bg);
    color: var(--hs-pill-text);
}

/* Utilities */
.hs-mt-3 { margin-top: 0.75rem; }
.hs-mt-6 { margin-top: 1.5rem; }
.hs-p-0 { padding: 0 !important; }
.hs-text-success { color: #059669 !important; }
:root[data-theme='dark'] .hs-text-success,
[data-theme='dark'] .hs-text-success { color: #34d399 !important; }

/* Responsive */
@media (max-width: 1024px) {
    .hs-workspace-grid {
        grid-template-columns: 1fr;
    }
}
@media (max-width: 768px) {
    .hs-field-grid {
        grid-template-columns: 1fr;
    }
    .hs-header-body {
        flex-direction: column;
    }
    .hs-header-actions {
        width: 100%;
    }
    .hs-header-actions .hs-btn {
        flex: 1;
    }
    .hs-btn-lg {
        width: 100%;
    }
}
@media (max-width: 480px) {
    .hs-kpi-grid {
        grid-template-columns: 1fr;
    }
}
</style>
@endsection
