<?php

/**
 * Host Settings View
 *
 * This file contains the HTML markup for the host settings page.
 *
 * @package Evently
 * @subpackage Views/Host
 * @author Your Name <Celio Natti>
 * @license MIT License
 * @version 1.0.0
 */

$siteName = site_setting('site_title', 'Eventlyy');
?>

@extends('layouts/host')

@section('title')
Host Settings - Manage Your <?= e($siteName) ?> Account
@endsection

@section('content')
<section class="page-hero">
    <div>
        <span class="panel-badge">Host settings</span>
        <h1>Manage your host account</h1>
        <p class="lede">Update your profile, payout details, and notification preferences in one place.</p>
    </div>
</section>

<section class="host-summary-grid">
    <article class="summary-card">
        <span class="card-label">Host rating</span>
        <h3 class="card-value"><?= number_format($profile['rating'] ?? 5.00, 1) ?> / 5</h3>
        <p class="card-meta">Trusted by attendees</p>
    </article>
    <article class="summary-card">
        <span class="card-label">Live events</span>
        <h3 class="card-value"><?= (int)$liveEventsCount ?> active</h3>
        <p class="card-meta">Upcoming events currently publishing</p>
    </article>
    <article class="summary-card">
        <span class="card-label">Next payout</span>
        <h3 class="card-value"><?= format_price($pendingPayout) ?></h3>
        <p class="card-meta">Expected this week</p>
    </article>
    <article class="summary-card">
        <span class="card-label">Checked-In Attendees</span>
        <h3 class="card-value"><?= (int)$checkedInCount ?></h3>
        <p class="card-meta">Total successful check-ins</p>
    </article>
</section>

<section class="form-card" style="margin-bottom: 1.5rem;">
    <div class="card-header" style="margin-bottom: 1.25rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem;">
        <div>
            <h2>Profile &amp; payout details</h2>
            <p class="section-note" style="margin: 0;">This information will help your attendees and ensure payouts are processed smoothly.</p>
        </div>
        <a href="{{ route('host/financials') }}" class="admin-action-btn" style="padding: 0.5rem 1rem; font-size: 0.85rem; border-radius: 8px; text-decoration: none; display: inline-flex; align-items: center; gap: 0.35rem;">
            💳 View Financials &amp; Payouts History →
        </a>
    </div>

    <form method="POST" action="{{ route('host/settings') }}" style="display: flex; flex-direction: column; gap: 1.25rem;">
        <?= csrf_field() ?>
        
        <label>
            <span>Host name</span>
            <input type="text" name="host_name" placeholder="Your name or company" value="<?= e($user->name) ?>" required />
        </label>
        
        <label>
            <span>Email address</span>
            <input type="email" name="email" placeholder="host@example.com" value="<?= e($user->email) ?>" disabled />
            <small class="muted" style="color: rgb(var(--muted)); font-size: 0.8rem; margin-top: 0.25rem;">Contact support to change your account email.</small>
        </label>
        
        <label>
            <span>Phone number</span>
            <input type="tel" name="phone_number" placeholder="(555) 123-4567" value="<?= e($user->phone ?? '') ?>" />
        </label>
        
        <label>
            <span>Bio</span>
            <textarea name="bio" placeholder="Write a short bio about yourself or your organization..." rows="3"><?= e($profile['bio'] ?? '') ?></textarea>
        </label>
        
        <?php 
        $banksList = $supportedBanks ?? \Ace\PaystackService::getSupportedBanks();
        $currentBank = $profile['payout_bank'] ?? '';
        ?>
        <label>
            <span>Payout Bank (Paystack Supported)</span>
            <select name="payout_bank" id="settings-payout-bank" required style="width:100%; box-sizing:border-box; padding:0.85rem 1rem; border-radius:12px; border:1px solid rgba(var(--muted),0.2); background:rgb(var(--card)); color:rgb(var(--text)); font-size:0.95rem;">
                <option value="">-- Select Your Bank --</option>
                <?php foreach ($banksList as $b): ?>
                    <option value="<?= e($b['name']) ?>" data-code="<?= e($b['code']) ?>" <?= (stripos($currentBank, $b['name']) !== false || $currentBank === $b['name']) ? 'selected' : '' ?>>
                        <?= e($b['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>
        
        <label>
            <span>Payout Account Number (10-Digit NUBAN)</span>
            <input type="text" name="payout_account" placeholder="e.g. 0123456789" maxlength="10" value="<?= e($profile['payout_account'] ?? '') ?>" required />
        </label>
        <input type="hidden" name="payout_routing" id="settings-payout-routing" value="<?= e($profile['payout_routing'] ?? '') ?>" />
        
        <div class="form-notes">
            <p class="section-note" style="margin: 0; font-size: 0.85rem; color: rgb(var(--muted));">Payments are automatically routed to your verified payout account.</p>
        </div>
        
        <button type="submit" class="checkout-btn" style="width: 100%; height: 48px; font-size: 1rem; border-radius: 12px; border: none; cursor: pointer;">Save settings</button>
    </form>
</section>

<section class="form-card" style="margin-bottom: 1.5rem;">
    <div class="card-header" style="margin-bottom: 1.25rem;">
        <h2>Account Information</h2>
        <p class="section-note" style="margin: 0;">Quick reference for your host account details.</p>
    </div>
    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(180px, 1fr)); gap:1rem; width:100%;">
        <div style="padding:1.15rem; border-radius:14px; background:rgba(var(--muted),0.05); border:1px solid rgba(var(--muted),0.12); box-sizing:border-box;">
            <h3 style="font-size:0.85rem; margin:0 0 0.35rem; color:rgb(var(--muted)); font-weight:600;">Member Since</h3>
            <p style="margin:0; font-weight:700; font-size:1.1rem; color:rgb(var(--text));"><?= date('F j, Y', strtotime($user->created_at ?? 'now')) ?></p>
        </div>
        <div style="padding:1.15rem; border-radius:14px; background:rgba(var(--muted),0.05); border:1px solid rgba(var(--muted),0.12); box-sizing:border-box;">
            <h3 style="font-size:0.85rem; margin:0 0 0.35rem; color:rgb(var(--muted)); font-weight:600;">Account Status</h3>
            <p style="color: #10b981; margin:0; font-weight:700; font-size:1.1rem;">● Active</p>
        </div>
        <div style="padding:1.15rem; border-radius:14px; background:rgba(var(--muted),0.05); border:1px solid rgba(var(--muted),0.12); box-sizing:border-box;">
            <h3 style="font-size:0.85rem; margin:0 0 0.35rem; color:rgb(var(--muted)); font-weight:600;">Timezone</h3>
            <p style="margin:0; font-weight:700; font-size:1.1rem; color:rgb(var(--text));"><?= e(config('timezone', 'UTC')) ?></p>
        </div>
    </div>
</section>

<section class="form-card">
    <div class="card-header" style="margin-bottom: 1.25rem;">
        <h2>Preferences</h2>
        <p class="section-note" style="margin: 0;">Choose your visual interface style.</p>
    </div>
    <div>
        <button type="button" class="checkout-btn secondary" data-theme-toggle="true" aria-label="Toggle theme"
            style="display:flex; align-items:center; justify-content:center; gap:0.5rem; width:100%; height:48px; border-radius:12px; font-size:0.95rem; font-weight:600; border:1px solid rgba(var(--muted),0.18); cursor:pointer;">
            🌓 Toggle Dark / Light Mode
        </button>
    </div>
</section>
@endsection