<?php

/**
 * Layaway & Partial Payment Hub
 *
 * Attendee dashboard to track installment payment progress, pay remaining balances, and manage event credits.
 *
 * @package Eventlyy
 * @subpackage Views/Layaway
 * @version 1.0.0
 */

$user = $user ?? Application::$app->user;
$layaways = $layaways ?? [];
$credits = $credits ?? [];
$siteTitle = site_setting('site_title', 'Eventlyy');
?>

@extends('layouts/main')

@section('title')
My Layaway Plans & Credits — <?= e($siteTitle) ?>
@endsection

@section('content')
<style>
.layaway-container {
    max-width: 1000px;
    margin: 2rem auto 4rem;
    padding: 0 1.25rem;
}
.layaway-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1rem;
    margin-bottom: 2rem;
    padding-bottom: 1.5rem;
    border-bottom: 1px solid rgba(var(--text), 0.1);
}
.layaway-title {
    font-size: 1.75rem;
    font-weight: 800;
    color: rgb(var(--text));
    margin: 0 0 0.25rem;
}
.layaway-sub {
    font-size: 0.95rem;
    color: rgb(var(--text));
    opacity: 0.7;
    margin: 0;
}
.layaway-back-link {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    color: rgb(var(--brand));
    font-weight: 600;
    font-size: 0.9rem;
    text-decoration: none;
}
.layaway-card {
    background: rgb(var(--surface));
    border: 1px solid rgba(var(--text), 0.1);
    border-radius: 16px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
    box-shadow: 0 4px 16px rgba(0,0,0,0.03);
    transition: transform 0.2s, box-shadow 0.2s;
}
.layaway-card-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    flex-wrap: wrap;
    gap: 1rem;
    margin-bottom: 1.25rem;
}
.layaway-event-title {
    font-size: 1.25rem;
    font-weight: 700;
    color: rgb(var(--text));
    margin: 0 0 0.35rem;
}
.layaway-event-meta {
    font-size: 0.85rem;
    color: rgb(var(--text));
    opacity: 0.75;
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
}
.layaway-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    padding: 0.3rem 0.75rem;
    border-radius: 999px;
    font-size: 0.8rem;
    font-weight: 700;
    background: rgba(99, 102, 241, 0.12);
    color: #6366f1;
}
.layaway-progress-wrap {
    margin: 1.25rem 0;
    background: rgba(var(--text), 0.04);
    border-radius: 12px;
    padding: 1rem 1.25rem;
}
.progress-bar-track {
    height: 12px;
    background: rgba(var(--text), 0.1);
    border-radius: 999px;
    overflow: hidden;
    margin: 0.5rem 0;
}
.progress-bar-fill {
    height: 100%;
    background: linear-gradient(90deg, #6366f1, #10b981);
    border-radius: 999px;
    transition: width 0.4s ease;
}
.layaway-metrics {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 0.9rem;
    color: rgb(var(--text));
    flex-wrap: wrap;
    gap: 0.5rem;
}
.layaway-action-box {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1rem;
    margin-top: 1.25rem;
    padding-top: 1.25rem;
    border-top: 1px solid rgba(var(--text), 0.08);
}
.layaway-pay-form {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex-wrap: wrap;
}
.layaway-input {
    padding: 0.65rem 0.85rem;
    border: 1px solid rgba(var(--text), 0.2);
    border-radius: 10px;
    background: rgb(var(--bg));
    color: rgb(var(--text));
    font-size: 0.9rem;
    width: 130px;
}
.layaway-pay-btn {
    background: rgb(var(--brand));
    color: #fff;
    border: none;
    padding: 0.7rem 1.25rem;
    border-radius: 10px;
    font-weight: 700;
    font-size: 0.9rem;
    cursor: pointer;
    transition: opacity 0.2s;
}
.layaway-pay-btn:hover {
    opacity: 0.9;
}
.credit-card {
    background: rgba(245, 158, 11, 0.06);
    border: 1px solid rgba(245, 158, 11, 0.25);
    border-radius: 14px;
    padding: 1.25rem;
    margin-bottom: 1rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1rem;
}
.history-toggle-btn {
    background: transparent;
    border: none;
    color: rgb(var(--brand));
    font-size: 0.85rem;
    font-weight: 600;
    cursor: pointer;
    padding: 0;
}
.history-table {
    width: 100%;
    margin-top: 0.75rem;
    font-size: 0.85rem;
    border-collapse: collapse;
}
.history-table th, .history-table td {
    padding: 0.5rem;
    text-align: left;
    border-bottom: 1px solid rgba(var(--text), 0.06);
}
.empty-state {
    text-align: center;
    padding: 3rem 1.5rem;
    background: rgb(var(--surface));
    border-radius: 16px;
    border: 1px dashed rgba(var(--text), 0.2);
}
</style>

<div class="layaway-container">
    <!-- Header -->
    <div class="layaway-header">
        <div>
            <h1 class="layaway-title">💳 My Layaway Plans &amp; Credits</h1>
            <p class="layaway-sub">Pay for your tickets bit-by-bit before the event starts.</p>
        </div>
        <a href="/profile?tab=tickets" class="layaway-back-link">
            ← Back to Tickets
        </a>
    </div>

    <!-- Active Available Credits Banner (if any) -->
    <?php if (!empty($credits)): ?>
    <div style="margin-bottom: 2rem;">
        <h2 style="font-size: 1.2rem; font-weight: 800; color: rgb(var(--text)); margin-bottom: 0.75rem; display: flex; align-items: center; gap: 0.4rem;">
            <span>✨</span> Available Event Credits
        </h2>
        <?php foreach ($credits as $cr): ?>
        <div class="credit-card">
            <div>
                <strong style="font-size: 1.1rem; color: #b45309; display: block;">
                    <?= format_price((float)$cr['remaining_amount']) ?> Credit
                </strong>
                <small style="color: rgb(var(--text)); opacity: 0.8;">
                    From past event: <strong><?= e($cr['original_event_name'] ?? 'Concluded Event') ?></strong>
                    <?php if (!empty($cr['expires_at'])): ?>
                        • Expires <?= date('M d, Y', strtotime($cr['expires_at'])) ?>
                    <?php endif; ?>
                </small>
            </div>
            <div>
                <?php if (!empty($layaways)): ?>
                    <form action="/layaway/apply-credit" method="POST" style="display: flex; gap: 0.5rem; align-items: center;">
                        <?= csrf_field() ?>
                        <input type="hidden" name="credit_id" value="<?= (int)$cr['id'] ?>">
                        <select name="ticket_id" class="layaway-input" style="width: auto;" required>
                            <option value="">Apply to plan...</option>
                            <?php foreach ($layaways as $ly): ?>
                                <?php if (!$ly['progress']['is_complete']): ?>
                                    <option value="<?= e($ly['ticket_id']) ?>"><?= e($ly['event_name']) ?> (Needs <?= format_price($ly['progress']['remaining']) ?>)</option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" class="layaway-pay-btn" style="background: #d97706;">Apply Credit</button>
                    </form>
                <?php else: ?>
                    <a href="/#explore" class="layaway-pay-btn" style="background: #d97706; text-decoration: none; display: inline-block;">Browse Events →</a>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- Layaway Plans Section -->
    <h2 style="font-size: 1.2rem; font-weight: 800; color: rgb(var(--text)); margin-bottom: 1rem;">
        Active Payment Plans (<?= count($layaways) ?>)
    </h2>

    <?php if (empty($layaways)): ?>
        <div class="empty-state">
            <span style="font-size: 3rem; display: block; margin-bottom: 0.75rem;">🎟️</span>
            <h3 style="margin: 0 0 0.5rem; color: rgb(var(--text)); font-size: 1.25rem;">No Active Layaway Plans</h3>
            <p style="color: rgb(var(--text)); opacity: 0.7; max-width: 450px; margin: 0 auto 1.5rem;">
                When an event offers partial payment, you can reserve your ticket with a deposit and pay the rest bit-by-bit.
            </p>
            <a href="/#explore" class="layaway-pay-btn" style="text-decoration: none; display: inline-block;">Browse Available Events →</a>
        </div>
    <?php else: ?>
        <?php foreach ($layaways as $layaway): ?>
            <?php 
                $prog = $layaway['progress']; 
                $ticketId = $layaway['ticket_id'];
                $eventDate = strtotime($layaway['event_date']);
                $isExpired = $eventDate && time() > $eventDate;
            ?>
            <div class="layaway-card">
                <div class="layaway-card-header">
                    <div>
                        <span class="layaway-badge">
                            ⏳ Layaway Plan • Pass Ref: <?= e($ticketId) ?>
                        </span>
                        <h3 class="layaway-event-title" style="margin-top: 0.5rem;">
                            <?= e($layaway['event_name']) ?>
                        </h3>
                        <div class="layaway-event-meta">
                            <span>📅 <?= date('D, M d, Y • g:i A', $eventDate) ?></span>
                            <span>📍 <?= e($layaway['event_location']) ?></span>
                            <span>🎫 <?= e($layaway['ticket_type']) ?></span>
                        </div>
                    </div>
                    <div style="text-align: right;">
                        <span style="font-size: 0.8rem; opacity: 0.7; display: block;">Total Ticket Price</span>
                        <strong style="font-size: 1.2rem; color: rgb(var(--text));">
                            <?= format_price($prog['total_price']) ?>
                        </strong>
                        <?php if ($layaway['days_until_event'] > 0): ?>
                            <span style="display: block; font-size: 0.78rem; color: #6366f1; font-weight: 600; margin-top: 0.2rem;">
                                ⏳ <?= $layaway['days_until_event'] ?> days left
                            </span>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Progress Bar -->
                <div class="layaway-progress-wrap">
                    <div class="layaway-metrics">
                        <span><strong><?= format_price($prog['total_paid']) ?></strong> paid so far</span>
                        <span style="font-weight: 700; color: #6366f1;"><?= $prog['percentage'] ?>% Completed</span>
                    </div>
                    <div class="progress-bar-track">
                        <div class="progress-bar-fill" style="width: <?= $prog['percentage'] ?>%;"></div>
                    </div>
                    <div class="layaway-metrics" style="font-size: 0.85rem; opacity: 0.8;">
                        <span>Balance Remaining: <strong style="color: #ef4444;"><?= format_price($prog['remaining']) ?></strong></span>
                        <span>Reserved on: <?= date('M d, Y', strtotime($layaway['purchase_date'])) ?></span>
                    </div>
                </div>

                <!-- Action row -->
                <div class="layaway-action-box">
                    <div>
                        <button type="button" class="history-toggle-btn" onclick="toggleHistory('hist-<?= e($ticketId) ?>')">
                            📜 View Payment History (<?= count($prog['payments']) ?> payment<?= count($prog['payments']) === 1 ? '' : 's' ?>) ▾
                        </button>
                    </div>

                    <?php if (!$isExpired && !$prog['is_complete']): ?>
                    <div>
                        <form action="/layaway/pay" method="POST" class="layaway-pay-form">
                            <?= csrf_field() ?>
                            <input type="hidden" name="ticket_id" value="<?= e($ticketId) ?>">
                            <label style="font-size: 0.85rem; opacity: 0.8;">Pay Amount:</label>
                            <input type="number" name="amount" class="layaway-input" 
                                   min="100" max="<?= ceil($prog['remaining']) ?>" 
                                   step="any" value="<?= ceil($prog['remaining']) ?>" required>
                            <button type="submit" class="layaway-pay-btn">Pay Next Bit →</button>
                        </form>
                    </div>
                    <?php elseif ($prog['is_complete']): ?>
                        <span style="color: #10b981; font-weight: 700;">✅ Fully Paid — Check your ticket pass in profile!</span>
                    <?php else: ?>
                        <span style="color: #ef4444; font-weight: 600; font-size: 0.85rem;">⚠️ Event date passed. Funds will be converted to credit.</span>
                    <?php endif; ?>
                </div>

                <!-- Expandable Payment History -->
                <div id="hist-<?= e($ticketId) ?>" style="display: none; margin-top: 1rem; border-top: 1px dashed rgba(var(--text), 0.1); padding-top: 0.75rem;">
                    <table class="history-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Method</th>
                                <th>Reference</th>
                                <th>Amount</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($prog['payments'] as $p): ?>
                            <tr>
                                <td><?= date('M d, Y • g:i A', strtotime($p['created_at'])) ?></td>
                                <td><?= ucfirst(e($p['payment_method'])) ?></td>
                                <td><code><?= e($p['reference']) ?></code></td>
                                <td><strong><?= format_price((float)$p['amount']) ?></strong></td>
                                <td><span style="color: #10b981; font-weight: 700;">✓ Completed</span></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Terms reminder -->
                <div style="margin-top: 0.75rem; font-size: 0.75rem; color: rgb(var(--text)); opacity: 0.6;">
                    🔒 Notice: Tickets are issued once fully paid. Payments are non-refundable after event start time, but protectively converted to credit if uncompleted.
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<script>
function toggleHistory(id) {
    var el = document.getElementById(id);
    if (el) {
        el.style.display = (el.style.display === 'none' || el.style.display === '') ? 'block' : 'none';
    }
}
</script>
@endsection
