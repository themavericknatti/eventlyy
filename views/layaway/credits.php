<?php

/**
 * Event Credits Hub
 *
 * Public view where attendees (both registered and guests) can check their
 * available event credits, view balances, and learn how to redeem them on any event.
 *
 * @package Eventlyy
 * @subpackage Views/Layaway
 */

$user = $user ?? \Ace\Application::$app->user;
$email = $email ?? '';
$credits = $credits ?? [];
$totalBalance = (float)($totalBalance ?? 0.00);
$siteTitle = site_setting('site_title', 'Eventlyy');
?>

@extends('layouts/main')

@section('title')
Event Credits Hub — <?= e($siteTitle) ?>
@endsection

@section('content')
<style>
.credits-wrap {
    max-width: 860px;
    margin: 2.5rem auto 4.5rem;
    padding: 0 1.25rem;
}
.credits-hero {
    text-align: center;
    background: radial-gradient(100% 100% at 50% 0%, rgba(245, 158, 11, 0.12) 0%, rgba(var(--surface), 0.5) 100%);
    border: 1px solid rgba(245, 158, 11, 0.25);
    border-radius: 20px;
    padding: 2.5rem 1.5rem;
    margin-bottom: 2.25rem;
    box-shadow: 0 10px 30px rgba(0,0,0,0.03);
}
.credits-icon {
    font-size: 3rem;
    display: inline-block;
    margin-bottom: 0.75rem;
}
.credits-title {
    font-size: 1.85rem;
    font-weight: 800;
    color: rgb(var(--text));
    margin: 0 0 0.5rem;
}
.credits-sub {
    font-size: 1rem;
    color: rgb(var(--text));
    opacity: 0.75;
    max-width: 580px;
    margin: 0 auto;
    line-height: 1.55;
}
.credits-lookup-card {
    background: rgb(var(--surface));
    border: 1px solid rgba(var(--text), 0.1);
    border-radius: 16px;
    padding: 1.75rem;
    margin-bottom: 2rem;
    box-shadow: 0 4px 18px rgba(0,0,0,0.03);
}
.credits-lookup-form {
    display: flex;
    gap: 0.75rem;
    flex-wrap: wrap;
    margin-top: 1rem;
}
.credits-input {
    flex: 1;
    min-width: 260px;
    padding: 0.85rem 1.15rem;
    border-radius: 12px;
    border: 1.5px solid rgba(var(--text), 0.18);
    background: rgb(var(--bg));
    color: rgb(var(--text));
    font-size: 0.95rem;
}
.credits-btn {
    background: #d97706;
    color: #ffffff;
    font-weight: 700;
    font-size: 0.95rem;
    border: none;
    border-radius: 12px;
    padding: 0.85rem 1.75rem;
    cursor: pointer;
    transition: all 0.2s ease;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
}
.credits-btn:hover {
    background: #b45309;
    color: #ffffff;
    transform: translateY(-1px);
}
.balance-card {
    background: linear-gradient(135deg, rgba(245, 158, 11, 0.15) 0%, rgba(217, 119, 6, 0.08) 100%);
    border: 2px solid rgba(245, 158, 11, 0.35);
    border-radius: 18px;
    padding: 2rem;
    margin-bottom: 2rem;
    text-align: center;
}
.balance-label {
    font-size: 0.82rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: #92400e;
    margin-bottom: 0.5rem;
    display: block;
}
.balance-val {
    font-size: 2.75rem;
    font-weight: 900;
    color: #78350f;
    margin-bottom: 0.5rem;
    line-height: 1;
}
.balance-sub {
    font-size: 0.9rem;
    color: #92400e;
    opacity: 0.9;
}
.credit-item {
    background: rgb(var(--surface));
    border: 1px solid rgba(var(--text), 0.1);
    border-left: 5px solid #d97706;
    border-radius: 14px;
    padding: 1.25rem 1.5rem;
    margin-bottom: 1rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1rem;
}
.credit-item__name {
    font-size: 1.1rem;
    font-weight: 700;
    color: rgb(var(--text));
    margin: 0 0 0.3rem;
}
.credit-item__meta {
    font-size: 0.85rem;
    color: rgb(var(--text));
    opacity: 0.7;
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
}
.credit-item__amount {
    font-size: 1.35rem;
    font-weight: 800;
    color: #b45309;
    text-align: right;
}
.instructions-card {
    background: rgba(var(--brand), 0.05);
    border: 1px solid rgba(var(--brand), 0.2);
    border-radius: 16px;
    padding: 1.5rem;
    margin-top: 2rem;
}
.step-list {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 1rem;
    margin-top: 1rem;
}
.step-item {
    background: rgb(var(--surface));
    border-radius: 12px;
    padding: 1rem;
    border: 1px solid rgba(var(--text), 0.08);
}
.step-num {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 28px;
    height: 28px;
    background: #d97706;
    color: #fff;
    font-weight: 800;
    font-size: 0.85rem;
    border-radius: 50%;
    margin-bottom: 0.5rem;
}
</style>

<div class="credits-wrap">
    <!-- Hero Header -->
    <div class="credits-hero">
        <span class="credits-icon">🎫</span>
        <h1 class="credits-title">Event Credits Hub</h1>
        <p class="credits-sub">
            Your partial payments and uncompleted layaway deposits are 100% protected. Redeem your credits toward tickets or layaway reservations for any event on <?= e($siteTitle) ?>.
        </p>
    </div>

    <!-- Email Search / Switch Lookup Form -->
    <div class="credits-lookup-card">
        <label for="credits-email" style="font-size:0.95rem; font-weight:700; color:rgb(var(--text)); display:block;">
            🔍 Look up credits by email address:
        </label>
        <form method="GET" action="<?= \url('/layaway/credits') ?>" class="credits-lookup-form">
            <input type="email" id="credits-email" name="email" value="<?= e($email) ?>" class="credits-input" placeholder="Enter your attendee email (e.g. attendee@example.com)" required>
            <button type="submit" class="credits-btn">
                <span>Check Credits</span> &rarr;
            </button>
        </form>
    </div>

    <?php if (!empty($email)): ?>
        <?php if (!empty($credits)): ?>
            <!-- Total Balance Card -->
            <div class="balance-card">
                <span class="balance-label">Total Usable Event Credit Balance</span>
                <div class="balance-val"><?= format_price($totalBalance) ?></div>
                <div class="balance-sub">
                    Available for email <strong><?= e($email) ?></strong> &bull; Valid for 1 year from issue date
                </div>
            </div>

            <!-- List of Credits -->
            <div style="margin-bottom: 2rem;">
                <h2 style="font-size:1.25rem; font-weight:800; color:rgb(var(--text)); margin-bottom:1rem; display:flex; align-items:center; gap:0.5rem;">
                    <span>✨</span> Your Active Credits (<?= count($credits) ?>)
                </h2>

                <?php foreach ($credits as $cr): ?>
                <div class="credit-item">
                    <div>
                        <h3 class="credit-item__name">
                            Credit from <?= e($cr['original_event_name'] ?? 'Concluded Event') ?>
                        </h3>
                        <div class="credit-item__meta">
                            <span>🎫 Original Ticket: <code><?= e($cr['original_ticket_id']) ?></code></span>
                            <?php if (!empty($cr['expires_at'])): ?>
                                <span>⏳ Valid Until: <strong><?= date('M d, Y', strtotime($cr['expires_at'])) ?></strong></span>
                            <?php endif; ?>
                            <span style="color:#10b981; font-weight:700;">● Active &amp; Ready</span>
                        </div>
                    </div>
                    <div>
                        <div class="credit-item__amount">
                            <?= format_price((float)$cr['remaining_amount']) ?>
                        </div>
                        <?php if ((float)$cr['amount'] > (float)$cr['remaining_amount']): ?>
                            <small style="font-size:0.75rem; color:rgb(var(--text)); opacity:0.6; display:block; text-align:right;">
                                Original: <?= format_price((float)$cr['amount']) ?>
                            </small>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>

                <div style="text-align:center; margin-top:1.5rem;">
                    <a href="/#explore" class="credits-btn" style="padding:1rem 2.25rem; font-size:1.05rem;">
                        🎟️ Browse Events &amp; Redeem Credits Now &rarr;
                    </a>
                </div>
            </div>

            <!-- Guest Callout: Prompt Registration -->
            <?php if (!$user): ?>
            <div style="background:rgba(99, 102, 241, 0.08); border:1px solid rgba(99, 102, 241, 0.25); border-radius:16px; padding:1.25rem 1.5rem; margin-top:2rem; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:1rem;">
                <div>
                    <strong style="color:#4f46e5; font-size:1rem; display:block; margin-bottom:0.25rem;">
                        💡 Create a free account to manage your credits
                    </strong>
                    <span style="font-size:0.88rem; color:rgb(var(--text)); opacity:0.8;">
                        Sign up with <strong><?= e($email) ?></strong> to link your credits, view tickets, and receive SMS updates.
                    </span>
                </div>
                <a href="<?= \url('/register') ?>" class="credits-btn" style="background:#4f46e5; padding:0.65rem 1.25rem; font-size:0.88rem;">
                    Create Account &rarr;
                </a>
            </div>
            <?php endif; ?>

        <?php else: ?>
            <!-- Empty state for searched email -->
            <div style="text-align:center; padding:3rem 1.5rem; background:rgb(var(--surface)); border-radius:16px; border:1px dashed rgba(var(--text), 0.2); margin-bottom:2rem;">
                <span style="font-size:2.5rem; display:block; margin-bottom:0.75rem;">🔍</span>
                <h3 style="margin:0 0 0.5rem; font-size:1.25rem; color:rgb(var(--text));">No Active Credits Found</h3>
                <p style="color:rgb(var(--text)); opacity:0.75; max-width:480px; margin:0 auto 1.5rem; font-size:0.95rem; line-height:1.5;">
                    There are no available event credits associated with <strong><?= e($email) ?></strong>. If your layaway plan was 100% completed, your official admission pass was already issued to your email.
                </p>
                <a href="/#explore" class="credits-btn" style="text-decoration:none;">
                    Browse All Events →
                </a>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <!-- How it works guide -->
    <div class="instructions-card">
        <h3 style="margin:0; font-size:1.15rem; font-weight:800; color:rgb(var(--text));">
            💡 How to Redeem Your Event Credits on Any Event:
        </h3>
        <div class="step-list">
            <div class="step-item">
                <span class="step-num">1</span>
                <strong style="display:block; font-size:0.9rem; color:rgb(var(--text)); margin-bottom:0.25rem;">Find Any Event</strong>
                <p style="margin:0; font-size:0.82rem; color:rgb(var(--text)); opacity:0.75; line-height:1.4;">
                    Browse any upcoming concert, festival, conference, or workshop on <?= e($siteTitle) ?>.
                </p>
            </div>
            <div class="step-item">
                <span class="step-num">2</span>
                <strong style="display:block; font-size:0.9rem; color:rgb(var(--text)); margin-bottom:0.25rem;">Go to Checkout</strong>
                <p style="margin:0; font-size:0.82rem; color:rgb(var(--text)); opacity:0.75; line-height:1.4;">
                    Select your tickets and enter your email address at checkout.
                </p>
            </div>
            <div class="step-item">
                <span class="step-num">3</span>
                <strong style="display:block; font-size:0.9rem; color:rgb(var(--text)); margin-bottom:0.25rem;">Apply Credit</strong>
                <p style="margin:0; font-size:0.82rem; color:rgb(var(--text)); opacity:0.75; line-height:1.4;">
                    Click "Apply Event Credit". The credit reduces your total, or covers 100% with instant ticket issuance!
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
