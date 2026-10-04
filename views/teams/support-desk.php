<?php

/**
 * Team Support Desk Page
 *
 * Workstation task view for guest support & help desk staff.
 * Features attendee lookup, ticket verification, check-in overrides,
 * cross-event pass resolution, and instant gate pass issuance.
 * Fully responsive across Mobile, Tablet, and Desktop.
 *
 * @package Evently
 * @subpackage Views/Teams
 * @author Your Name <Celio Natti>
 * @license MIT License
 * @version 2.0.0
 */

$eventTime = !empty($event['date']) ? strtotime($event['date']) : null;
$assignments = $assignments ?? [];
?>

@extends('layouts/team')

@section('title')
Guest & Support Desk - <?= e($event['name'] ?? 'Evently') ?>
@endsection

@section('style')
<style>
/* ═══════════════════════════════════════════════════════════════════
   Support Desk Scoped Component System (.sd-*)
   ═══════════════════════════════════════════════════════════════════ */
.sd-wrapper {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
    width: 100%;
}

/* ── Hero & Event Workspace Banner ── */
.sd-hero {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1.25rem;
    flex-wrap: wrap;
    background: rgb(var(--card));
    border: 1px solid rgba(var(--muted), 0.16);
    border-radius: 20px;
    padding: 1.4rem 1.6rem;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04);
}

.sd-hero-left {
    display: flex;
    flex-direction: column;
    gap: 0.4rem;
    flex: 1;
    min-width: 260px;
}

.sd-badge-row {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    flex-wrap: wrap;
}

.sd-panel-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    background: rgba(var(--brand), 0.12);
    color: rgb(var(--brand));
    border: 1px solid rgba(var(--brand), 0.25);
    padding: 0.3rem 0.75rem;
    border-radius: 9999px;
    font-size: 0.8rem;
    font-weight: 700;
    letter-spacing: 0.02em;
}

.sd-live-status {
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    font-size: 0.78rem;
    font-weight: 600;
    color: #10b981;
}

.sd-pulse-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #10b981;
    animation: sdPulse 2s infinite ease-in-out;
}

@keyframes sdPulse {
    0%, 100% { opacity: 1; transform: scale(1); }
    50% { opacity: 0.4; transform: scale(0.85); }
}

.sd-hero-title {
    margin: 0;
    font-size: clamp(1.3rem, 2.8vw, 1.85rem);
    font-weight: 800;
    color: rgb(var(--text));
    letter-spacing: -0.02em;
    line-height: 1.25;
}

.sd-hero-meta {
    display: flex;
    align-items: center;
    gap: 0.65rem;
    flex-wrap: wrap;
    font-size: 0.88rem;
    color: rgb(var(--muted));
}

.sd-hero-meta span {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
}

.sd-meta-divider {
    opacity: 0.35;
}

.sd-hero-actions {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    flex-wrap: wrap;
}

.sd-btn-issue {
    background: linear-gradient(135deg, #10b981, #059669);
    color: #ffffff;
    font-weight: 800;
    font-size: 0.9rem;
    padding: 0.65rem 1.25rem;
    border-radius: 12px;
    border: none;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    box-shadow: 0 4px 14px rgba(16, 185, 129, 0.35);
    transition: transform 0.18s ease, box-shadow 0.18s ease;
}

.sd-btn-issue:hover {
    transform: translateY(-1px);
    box-shadow: 0 6px 18px rgba(16, 185, 129, 0.45);
}

.sd-btn-issue:active {
    transform: translateY(0);
}

.sd-switcher-box {
    background: rgb(var(--bg));
    border: 1px solid rgba(var(--muted), 0.18);
    border-radius: 12px;
    padding: 0.4rem 0.75rem;
    display: flex;
    flex-direction: column;
    gap: 0.2rem;
}

.sd-switcher-label {
    font-size: 0.68rem;
    text-transform: uppercase;
    font-weight: 800;
    letter-spacing: 0.05em;
    color: rgb(var(--muted));
}

.sd-switcher-select {
    background: transparent;
    border: none;
    font-size: 0.85rem;
    font-weight: 700;
    color: rgb(var(--text));
    outline: none;
    cursor: pointer;
    padding-right: 0.5rem;
}

/* ── Cross-Event Match Banner ── */
.sd-cross-match {
    background: rgba(59, 130, 246, 0.08);
    border: 1px solid rgba(59, 130, 246, 0.28);
    border-radius: 16px;
    padding: 1rem 1.25rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    flex-wrap: wrap;
}

.sd-cross-match-left {
    display: flex;
    align-items: center;
    gap: 0.85rem;
    flex: 1;
}

.sd-cross-match-icon {
    font-size: 1.6rem;
    line-height: 1;
}

.sd-cross-match-info {
    font-size: 0.88rem;
    color: rgb(var(--text));
    line-height: 1.45;
}

.sd-cross-match-code {
    background: rgba(59, 130, 246, 0.15);
    color: #2563eb;
    font-family: monospace;
    font-weight: 800;
    padding: 0.15rem 0.45rem;
    border-radius: 6px;
}

.sd-cross-match-btn {
    background: #2563eb;
    color: #ffffff;
    font-weight: 700;
    font-size: 0.85rem;
    padding: 0.55rem 1.1rem;
    border-radius: 10px;
    border: none;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    transition: background 0.18s ease;
}

.sd-cross-match-btn:hover {
    background: #1d4ed8;
}

/* ── Quick Stats Grid ── */
.sd-stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1rem;
}

.sd-stat-card {
    background: rgb(var(--card));
    border: 1px solid rgba(var(--muted), 0.14);
    border-radius: 18px;
    padding: 1.25rem 1.35rem;
    display: flex;
    flex-direction: column;
    gap: 0.4rem;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.03);
    position: relative;
    overflow: hidden;
}

.sd-stat-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: rgba(var(--muted), 0.15);
}

.sd-stat-card.stat-green::before { background: #10b981; }
.sd-stat-card.stat-amber::before { background: #f59e0b; }
.sd-stat-card.stat-brand::before { background: rgb(var(--brand)); }

.sd-stat-label {
    font-size: 0.78rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: rgb(var(--muted));
}

.sd-stat-val {
    font-size: clamp(1.4rem, 2.5vw, 1.85rem);
    font-weight: 800;
    color: rgb(var(--text));
    line-height: 1.2;
}

.sd-stat-card.stat-green .sd-stat-val { color: #10b981; }
.sd-stat-card.stat-amber .sd-stat-val { color: #d97706; }
.sd-stat-card.stat-brand .sd-stat-val { color: rgb(var(--brand)); }

.sd-stat-sub {
    font-size: 0.78rem;
    color: rgb(var(--muted));
}

/* ── Search Toolbar ── */
.sd-search-card {
    background: rgb(var(--card));
    border: 1px solid rgba(var(--muted), 0.16);
    border-radius: 18px;
    padding: 1.25rem 1.4rem;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.03);
}

.sd-search-form {
    display: flex;
    align-items: center;
    gap: 0.85rem;
    flex-wrap: wrap;
}

.sd-search-input-wrap {
    flex: 1;
    min-width: 280px;
    position: relative;
}

.sd-search-input {
    width: 100%;
    box-sizing: border-box;
    padding: 0.75rem 1.15rem 0.75rem 2.6rem;
    border-radius: 12px;
    border: 1px solid rgba(var(--muted), 0.24);
    background: rgb(var(--bg));
    color: rgb(var(--text));
    font-size: 16px; /* Prevents auto-zoom on iOS Safari */
    font-weight: 500;
    outline: none;
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
}

.sd-search-input:focus {
    border-color: rgb(var(--brand));
    box-shadow: 0 0 0 3px rgba(var(--brand), 0.15);
}

.sd-search-icon {
    position: absolute;
    left: 0.95rem;
    top: 50%;
    transform: translateY(-50%);
    color: rgb(var(--muted));
    font-size: 1rem;
    pointer-events: none;
}

.sd-search-btn {
    padding: 0.75rem 1.5rem;
    border-radius: 12px;
    background: rgb(var(--brand));
    color: #ffffff;
    font-weight: 700;
    font-size: 0.9rem;
    border: none;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    transition: opacity 0.18s ease;
}

.sd-search-btn:hover {
    opacity: 0.92;
}

.sd-clear-btn {
    padding: 0.75rem 1.2rem;
    border-radius: 12px;
    background: rgba(var(--muted), 0.12);
    color: rgb(var(--text));
    font-weight: 700;
    font-size: 0.9rem;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    transition: background 0.18s ease;
}

.sd-clear-btn:hover {
    background: rgba(var(--muted), 0.2);
}

/* ── Attendee Table & Card Container ── */
.sd-content-card {
    background: rgb(var(--card));
    border: 1px solid rgba(var(--muted), 0.16);
    border-radius: 20px;
    padding: 1.5rem;
    box-shadow: 0 4px 18px rgba(0, 0, 0, 0.03);
}

.sd-card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid rgba(var(--muted), 0.12);
    margin-bottom: 1.25rem;
    flex-wrap: wrap;
}

.sd-card-title {
    margin: 0;
    font-size: 1.2rem;
    font-weight: 800;
    color: rgb(var(--text));
}

.sd-card-subtitle {
    margin: 0.25rem 0 0;
    font-size: 0.85rem;
    color: rgb(var(--muted));
}

/* ── Desktop Table ── */
.sd-table-wrap {
    width: 100%;
    overflow-x: auto;
}

.sd-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.9rem;
    text-align: left;
}

.sd-table th {
    padding: 0.75rem 1rem;
    font-size: 0.75rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: rgb(var(--muted));
    border-bottom: 1px solid rgba(var(--muted), 0.15);
    background: rgba(var(--muted), 0.02);
}

.sd-table td {
    padding: 1rem;
    border-bottom: 1px solid rgba(var(--muted), 0.08);
    vertical-align: middle;
}

.sd-table tr:last-child td {
    border-bottom: none;
}

.sd-table tr:hover td {
    background: rgba(var(--muted), 0.03);
}

/* ── Attendee Cell ── */
.sd-attendee-cell {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.sd-avatar {
    width: 40px;
    height: 40px;
    border-radius: 12px;
    background: linear-gradient(135deg, rgba(var(--brand), 0.2), rgba(var(--brand), 0.06));
    border: 1px solid rgba(var(--brand), 0.25);
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 800;
    font-size: 0.92rem;
    color: rgb(var(--brand));
    flex-shrink: 0;
}

.sd-attendee-name {
    font-weight: 700;
    color: rgb(var(--text));
    font-size: 0.95rem;
}

.sd-attendee-email {
    font-size: 0.8rem;
    color: rgb(var(--muted));
    word-break: break-all;
}

.sd-code-badge {
    display: inline-block;
    background: rgba(var(--muted), 0.12);
    border: 1px solid rgba(var(--muted), 0.18);
    padding: 0.2rem 0.55rem;
    border-radius: 8px;
    font-weight: 700;
    font-family: monospace;
    font-size: 0.84rem;
    letter-spacing: 0.5px;
    color: rgb(var(--text));
}

.sd-tier-sub {
    font-size: 0.78rem;
    color: rgb(var(--muted));
    margin-top: 0.25rem;
}

/* ── Status Pills ── */
.sd-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    padding: 0.3rem 0.65rem;
    border-radius: 8px;
    font-size: 0.78rem;
    font-weight: 700;
    text-transform: capitalize;
}

.sd-badge-paid {
    background: rgba(16, 185, 129, 0.12);
    color: #10b981;
    border: 1px solid rgba(16, 185, 129, 0.25);
}

.sd-badge-free {
    background: rgba(59, 130, 246, 0.12);
    color: #3b82f6;
    border: 1px solid rgba(59, 130, 246, 0.25);
}

.sd-badge-pending {
    background: rgba(245, 158, 11, 0.12);
    color: #d97706;
    border: 1px solid rgba(245, 158, 11, 0.25);
}

.sd-badge-in {
    background: rgba(16, 185, 129, 0.12);
    color: #10b981;
    border: 1px solid rgba(16, 185, 129, 0.25);
}

.sd-badge-not-in {
    background: rgba(var(--muted), 0.12);
    color: rgb(var(--muted));
    border: 1px solid rgba(var(--muted), 0.2);
}

/* ── Action Buttons ── */
.sd-btn-action {
    padding: 0.45rem 0.95rem;
    border-radius: 9px;
    font-size: 0.82rem;
    font-weight: 700;
    cursor: pointer;
    border: none;
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    transition: opacity 0.18s ease, transform 0.18s ease;
}

.sd-btn-action:hover {
    opacity: 0.9;
    transform: translateY(-1px);
}

.sd-btn-override {
    background: rgb(var(--brand));
    color: #ffffff;
}

.sd-btn-reset {
    background: rgba(245, 158, 11, 0.15);
    color: #d97706;
    border: 1px solid rgba(245, 158, 11, 0.35);
}

/* ── Mobile & Tablet Cards ── */
.sd-cards-list {
    display: none;
    flex-direction: column;
    gap: 0.85rem;
}

.sd-mobile-card {
    background: rgb(var(--bg));
    border: 1px solid rgba(var(--muted), 0.16);
    border-radius: 16px;
    padding: 1.15rem;
    display: flex;
    flex-direction: column;
    gap: 0.85rem;
    transition: border-color 0.2s ease;
}

.sd-mobile-card.card-checked-in {
    border-left: 4px solid #10b981;
}

.sd-card-top {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 0.65rem;
}

.sd-card-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.65rem;
    padding: 0.75rem;
    background: rgba(var(--muted), 0.05);
    border-radius: 12px;
}

.sd-grid-item {
    display: flex;
    flex-direction: column;
    gap: 0.15rem;
}

.sd-grid-label {
    font-size: 0.68rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: rgb(var(--muted));
}

.sd-mobile-action {
    width: 100%;
}

.sd-mobile-action button {
    width: 100%;
    height: 44px;
    justify-content: center;
    border-radius: 12px;
    font-size: 0.88rem;
    font-weight: 700;
}

/* ── Empty State ── */
.sd-empty-state {
    text-align: center;
    padding: 3.5rem 1.5rem;
    background: rgba(var(--muted), 0.03);
    border-radius: 16px;
    border: 1px dashed rgba(var(--muted), 0.2);
}

.sd-empty-icon {
    font-size: 3rem;
    display: block;
    margin-bottom: 0.75rem;
}

.sd-empty-title {
    margin: 0 0 0.4rem;
    font-size: 1.2rem;
    font-weight: 800;
    color: rgb(var(--text));
}

.sd-empty-desc {
    margin: 0 0 1.25rem;
    font-size: 0.88rem;
    color: rgb(var(--muted));
    max-width: 480px;
    margin-left: auto;
    margin-right: auto;
}

/* ═══════════════════════════════════════════════════════════════════
   RESPONSIVENESS (Desktop, Tablet, Mobile)
   ═══════════════════════════════════════════════════════════════════ */

/* Tablet (680px – 1024px) */
@media (max-width: 1024px) {
    .sd-stats-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 0.85rem;
    }
    .sd-hero {
        padding: 1.25rem;
    }
}

/* Mobile & Narrow Tablets (≤ 768px) */
@media (max-width: 768px) {
    .sd-table-wrap {
        display: none !important;
    }
    .sd-cards-list {
        display: flex !important;
    }
    .sd-hero {
        flex-direction: column;
        align-items: stretch;
        gap: 1rem;
    }
    .sd-hero-actions {
        width: 100%;
        flex-direction: column;
        align-items: stretch;
    }
    .sd-btn-issue {
        width: 100%;
        justify-content: center;
        height: 46px;
    }
    .sd-switcher-box {
        width: 100%;
        box-sizing: border-box;
    }
    .sd-search-form {
        flex-direction: column;
        align-items: stretch;
    }
    .sd-search-input-wrap {
        min-width: 100%;
    }
    .sd-search-btn,
    .sd-clear-btn {
        width: 100%;
        justify-content: center;
        height: 46px;
        box-sizing: border-box;
    }
    .sd-content-card {
        padding: 1rem;
        border-radius: 16px;
    }
    .sd-stats-grid {
        grid-template-columns: 1fr 1fr;
        gap: 0.65rem;
    }
    .sd-stat-card {
        padding: 1rem;
        border-radius: 14px;
    }
    .sd-stat-val {
        font-size: 1.35rem;
    }
    .sd-cross-match {
        flex-direction: column;
        align-items: stretch;
    }
    .sd-cross-match-btn {
        width: 100%;
        justify-content: center;
        height: 42px;
    }
}

/* Small Phones (≤ 420px) */
@media (max-width: 420px) {
    .sd-stats-grid {
        grid-template-columns: 1fr;
        gap: 0.5rem;
    }
    .sd-stat-card {
        padding: 0.85rem;
    }
    .sd-card-grid {
        grid-template-columns: 1fr;
    }
    .sd-hero-title {
        font-size: 1.25rem;
    }
}
</style>
@endsection

@section('content')
<div class="sd-wrapper">
    <!-- Active Event Context Banner -->
    <header class="sd-hero">
        <div class="sd-hero-left">
            <div class="sd-badge-row">
                <span class="sd-panel-badge">🎧 Workstation — Customer Support</span>
                <span class="sd-live-status">
                    <span class="sd-pulse-dot"></span>
                    Live Workspace
                </span>
            </div>
            <h1 class="sd-hero-title"><?= e($event['name'] ?? 'Event Workspace') ?></h1>
            <div class="sd-hero-meta">
                <?php if ($eventTime): ?>
                    <span>📅 <?= date('D, M d, Y • g:i A', $eventTime) ?></span>
                    <span class="sd-meta-divider">•</span>
                <?php endif; ?>
                <span>📍 <?= e($event['location'] ?? 'Venue') ?></span>
            </div>
        </div>

        <div class="sd-hero-actions">
            <button type="button" id="btn-open-box-office-support" class="sd-btn-issue">
                <span>🎟️</span> Issue Gate Ticket
            </button>
            <?php if (count($assignments) > 1): ?>
                <div class="sd-switcher-box">
                    <span class="sd-switcher-label">Switch Event</span>
                    <form method="POST" action="{{ route('teams/switch-event') }}" style="margin:0;">
                        <?= csrf_field() ?>
                        <select name="event_id" onchange="this.form.submit()" class="sd-switcher-select">
                            <?php foreach ($assignments as $asg): ?>
                                <option value="<?= $asg['event_id'] ?>" <?= $asg['event_id'] == $event['id'] ? 'selected' : '' ?>>
                                    <?= e($asg['event_name']) ?> (<?= e($asg['role_name']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </header>

    <!-- Quick Stats Grid -->
    <?php if (!empty($stats)): ?>
        <section class="sd-stats-grid">
            <article class="sd-stat-card">
                <span class="sd-stat-label">Total Issued</span>
                <span class="sd-stat-val"><?= number_format($stats['total_tickets']) ?></span>
                <span class="sd-stat-sub">Paid &amp; verified tickets</span>
            </article>
            <article class="sd-stat-card stat-green">
                <span class="sd-stat-label">Checked In</span>
                <span class="sd-stat-val"><?= number_format($stats['checked_in']) ?></span>
                <span class="sd-stat-sub">Admitted at gate</span>
            </article>
            <article class="sd-stat-card stat-amber">
                <span class="sd-stat-label">Remaining Expected</span>
                <span class="sd-stat-val"><?= number_format($stats['remaining']) ?></span>
                <span class="sd-stat-sub">Not checked in yet</span>
            </article>
            <article class="sd-stat-card stat-brand">
                <span class="sd-stat-label">Attendance Rate</span>
                <span class="sd-stat-val"><?= number_format($stats['percentage'], 1) ?>%</span>
                <span class="sd-stat-sub">Of total passes</span>
            </article>
        </section>
    <?php endif; ?>

    <!-- Cross-Event Match Notification -->
    <?php if (!empty($other_match)): ?>
        <div class="sd-cross-match">
            <div class="sd-cross-match-left">
                <span class="sd-cross-match-icon">💡</span>
                <div class="sd-cross-match-info">
                    <strong>Pass Registered for Another Assigned Event:</strong><br />
                    Ticket <span class="sd-cross-match-code"><?= e($other_match['ticket_code']) ?></span> for <strong><?= e($other_match['attendee_name']) ?></strong> is active on <strong><?= e($other_match['event_name']) ?></strong>.
                </div>
            </div>
            <form method="POST" action="{{ route('teams/switch-event') }}" style="margin:0; flex-shrink:0;">
                <?= csrf_field() ?>
                <input type="hidden" name="event_id" value="<?= $other_match['event_id'] ?>">
                <button type="submit" class="sd-cross-match-btn">
                    Switch to <?= e($other_match['event_name']) ?> →
                </button>
            </form>
        </div>
    <?php endif; ?>

    <!-- Search Toolbar -->
    <section class="sd-search-card">
        <form method="GET" action="{{ route('teams/support-desk') }}" class="sd-search-form">
            <div class="sd-search-input-wrap">
                <span class="sd-search-icon">🔍</span>
                <input type="text" name="q" value="<?= e($query ?? '') ?>" placeholder="Search attendee by name, email, or ticket code (e.g. EV-1001 or Alex)..."
                    class="sd-search-input">
            </div>
            <button type="submit" class="sd-search-btn">
                <span>🔍</span> Search Pass
            </button>
            <?php if (!empty($query)): ?>
                <a href="{{ route('teams/support-desk') }}" class="sd-clear-btn">Clear</a>
            <?php endif; ?>
        </form>
    </section>

    <!-- Attendees / Tickets Section -->
    <section class="sd-content-card">
        <div class="sd-card-header">
            <div>
                <h2 class="sd-card-title">Attendee Admission Passes</h2>
                <p class="sd-card-subtitle">
                    <?php if (!empty($query)): ?>
                        Showing results matching "<strong><?= e($query) ?></strong>" (<?= count($tickets ?? []) ?> found)
                    <?php else: ?>
                        Displaying latest <?= count($tickets ?? []) ?> purchased admission passes for this event
                    <?php endif; ?>
                </p>
            </div>
        </div>

        <?php if (empty($tickets)): ?>
            <div class="sd-empty-state">
                <span class="sd-empty-icon">🎟️</span>
                <h3 class="sd-empty-title">No Admission Passes Found</h3>
                <p class="sd-empty-desc">
                    <?php if (!empty($query)): ?>
                        No tickets match "<strong><?= e($query) ?></strong>" in this event. Check spelling or try searching by ticket code.
                    <?php else: ?>
                        No tickets have been issued or purchased for this event yet.
                    <?php endif; ?>
                </p>
                <?php if (!empty($query)): ?>
                    <a href="{{ route('teams/support-desk') }}" class="sd-clear-btn" style="display:inline-flex;">
                        View All Attendees
                    </a>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <!-- Desktop Table View (>= 769px) -->
            <div class="sd-table-wrap">
                <table class="sd-table">
                    <thead>
                        <tr>
                            <th style="width: 28%;">Attendee</th>
                            <th style="width: 22%;">Ticket Code &amp; Tier</th>
                            <th style="width: 15%;">Payment</th>
                            <th style="width: 18%;">Check-In Status</th>
                            <th style="width: 17%; text-align: right;">Gate Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tickets as $t):
                            $isCheckedIn = ($t['checked_in'] == 1);
                        ?>
                        <tr>
                            <td>
                                <div class="sd-attendee-cell">
                                    <div class="sd-avatar">
                                        <?= strtoupper(substr($t['attendee_name'] ?? 'A', 0, 1)) ?>
                                    </div>
                                    <div>
                                        <div class="sd-attendee-name"><?= e($t['attendee_name']) ?></div>
                                        <div class="sd-attendee-email"><?= e($t['attendee_email']) ?></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="sd-code-badge"><?= e($t['id']) ?></span>
                                <div class="sd-tier-sub">
                                    <?= e($t['ticket_type_name'] ?? 'General Admission') ?>
                                    <?php if (!empty($t['price'])): ?>
                                        <span>(<?= format_price($t['price']) ?>)</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <?php if (strcasecmp($t['status'], 'Paid') === 0): ?>
                                    <span class="sd-badge sd-badge-paid">Paid</span>
                                <?php elseif (strcasecmp($t['status'], 'Comped') === 0 || strcasecmp($t['status'], 'Free') === 0): ?>
                                    <span class="sd-badge sd-badge-free"><?= e(ucfirst($t['status'])) ?></span>
                                <?php else: ?>
                                    <span class="sd-badge sd-badge-pending"><?= e(ucfirst($t['status'])) ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($isCheckedIn): ?>
                                    <span class="sd-badge sd-badge-in">✓ Checked In</span>
                                    <?php if (!empty($t['checked_in_at'])): ?>
                                        <div class="sd-tier-sub">
                                            <?= date('M d, H:i', strtotime($t['checked_in_at'])) ?>
                                        </div>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="sd-badge sd-badge-not-in">Not Arrived</span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: right;">
                                <form method="POST" action="{{ route('teams/support-action') }}" style="display:inline; margin:0;">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="action" value="override_checkin" />
                                    <input type="hidden" name="ticket_id" value="<?= $t['id'] ?>" />
                                    <input type="hidden" name="check_in" value="<?= $isCheckedIn ? '0' : '1' ?>" />
                                    <button type="submit" class="sd-btn-action <?= $isCheckedIn ? 'sd-btn-reset' : 'sd-btn-override' ?>">
                                        <?= $isCheckedIn ? '↩ Reset' : '✓ Override Entry' ?>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Mobile & Tablet Cards View (<= 768px) -->
            <div class="sd-cards-list">
                <?php foreach ($tickets as $t):
                    $isCheckedIn = ($t['checked_in'] == 1);
                ?>
                <article class="sd-mobile-card <?= $isCheckedIn ? 'card-checked-in' : '' ?>">
                    <div class="sd-card-top">
                        <div class="sd-attendee-cell">
                            <div class="sd-avatar">
                                <?= strtoupper(substr($t['attendee_name'] ?? 'A', 0, 1)) ?>
                            </div>
                            <div>
                                <div class="sd-attendee-name"><?= e($t['attendee_name']) ?></div>
                                <div class="sd-attendee-email"><?= e($t['attendee_email']) ?></div>
                            </div>
                        </div>
                        <div>
                            <?php if ($isCheckedIn): ?>
                                <span class="sd-badge sd-badge-in">Checked In</span>
                            <?php else: ?>
                                <span class="sd-badge sd-badge-not-in">Not Arrived</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="sd-card-grid">
                        <div class="sd-grid-item">
                            <span class="sd-grid-label">Pass Code</span>
                            <span class="sd-code-badge"><?= e($t['id']) ?></span>
                        </div>
                        <div class="sd-grid-item">
                            <span class="sd-grid-label">Tier</span>
                            <span style="font-weight:700; font-size:0.85rem; color:rgb(var(--text));">
                                <?= e($t['ticket_type_name'] ?? 'General Admission') ?>
                            </span>
                        </div>
                        <div class="sd-grid-item">
                            <span class="sd-grid-label">Payment</span>
                            <span style="font-weight:700; font-size:0.82rem; color:<?= strcasecmp($t['status'], 'Paid') === 0 ? '#10b981' : 'inherit' ?>;">
                                <?= e(ucfirst($t['status'])) ?>
                            </span>
                        </div>
                        <div class="sd-grid-item">
                            <span class="sd-grid-label">Purchased</span>
                            <span style="font-size:0.82rem; color:rgb(var(--muted));">
                                <?= !empty($t['purchase_date']) ? date('M d, Y', strtotime($t['purchase_date'])) : '—' ?>
                            </span>
                        </div>
                    </div>

                    <div class="sd-mobile-action">
                        <form method="POST" action="{{ route('teams/support-action') }}" style="width:100%; margin:0;">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="override_checkin" />
                            <input type="hidden" name="ticket_id" value="<?= $t['id'] ?>" />
                            <input type="hidden" name="check_in" value="<?= $isCheckedIn ? '0' : '1' ?>" />
                            <button type="submit" class="sd-btn-action <?= $isCheckedIn ? 'sd-btn-reset' : 'sd-btn-override' ?>">
                                <?= $isCheckedIn ? '↩ Reset Check-In Status' : '🎟️ Override Check-In &amp; Admit' ?>
                            </button>
                        </form>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</div>

<!-- ════════════════════════════════════════════════════════════════
     GATE BOX OFFICE & TICKET SALES MODAL (SUPPORT DESK)
     ════════════════════════════════════════════════════════════════ -->
<div id="modal-box-office-support" class="team-modal-backdrop">
    <div class="team-modal-dialog">
        <div class="team-modal-header">
            <div>
                <h3 class="team-modal-title">
                    <span>🎟️</span> Issue Gate / Support Ticket
                </h3>
                <p class="team-modal-subtitle">Instant ticket issuance for attendee assistance at gate</p>
            </div>
            <button type="button" id="btn-close-box-office-support" class="team-modal-close-btn" aria-label="Close modal">✕</button>
        </div>

        <form id="form-gate-sale-support">
            <?= csrf_field() ?>
            
            <div style="margin-bottom:1rem;">
                <label class="team-modal-label">Ticket Tier *</label>
                <select name="ticket_type_id" required class="team-modal-select">
                    <?php foreach ($all_ticket_types ?? [] as $tt): 
                        $cap = (int)($tt['capacity'] ?? 0);
                        $sold = (int)($tt['sold_count'] ?? 0);
                        $avail = $cap > 0 ? max(0, $cap - $sold) : 'Unlimited';
                    ?>
                        <option value="<?= $tt['id'] ?>">
                            <?= e($tt['name']) ?> — <?= (float)$tt['price'] > 0 ? format_price($tt['price']) : 'Free' ?> (Avail: <?= $avail ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:0.75rem; margin-bottom:1rem;">
                <div>
                    <label class="team-modal-label">Attendee Name *</label>
                    <input type="text" name="attendee_name" required placeholder="Jane Doe" class="team-modal-input" />
                </div>
                <div>
                    <label class="team-modal-label">Attendee Email</label>
                    <input type="email" name="attendee_email" placeholder="jane@example.com" class="team-modal-input" />
                </div>
            </div>

            <div style="display:grid; grid-template-columns:1fr 1.3fr; gap:0.75rem; margin-bottom:1rem;">
                <div>
                    <label class="team-modal-label">Quantity</label>
                    <input type="number" name="quantity" value="1" min="1" max="10" required class="team-modal-input" />
                </div>
                <div>
                    <label class="team-modal-label">Payment Method</label>
                    <select name="payment_method" class="team-modal-select">
                        <option value="cash">💵 Cash at Gate</option>
                        <option value="pos">💳 POS Card Terminal</option>
                        <option value="transfer">📱 Bank Transfer / USSD</option>
                        <option value="comp">🎁 Complimentary / VIP Comp</option>
                    </select>
                </div>
            </div>

            <div class="team-modal-notice">
                <input type="checkbox" name="mark_checked_in" id="chk-support-gate-in" value="1" checked style="width:18px; height:18px; accent-color:rgb(var(--brand)); cursor:pointer;" />
                <label for="chk-support-gate-in" style="font-size:0.85rem; font-weight:700; color:rgb(var(--text)); cursor:pointer; margin:0;">
                    Check attendee in immediately
                </label>
            </div>

            <div id="support-sale-status" style="font-size:0.85rem; margin-bottom:0.75rem; display:none;"></div>

            <div style="display:flex; justify-content:flex-end; gap:0.6rem; margin-top:1.25rem;">
                <button type="button" id="btn-cancel-box-office-support" class="checkout-btn secondary" style="padding:0.75rem 1.25rem; border-radius:12px;">Cancel</button>
                <button type="submit" id="btn-submit-support-sale" class="checkout-btn" style="padding:0.75rem 1.5rem; border-radius:12px; background:#10b981; color:#fff; font-weight:800; border:none; cursor:pointer;">
                    ✓ Issue Ticket
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const modalSupp = document.getElementById('modal-box-office-support');
    const btnOpenSupp = document.getElementById('btn-open-box-office-support');
    const btnCloseSupp = document.getElementById('btn-close-box-office-support');
    const btnCancelSupp = document.getElementById('btn-cancel-box-office-support');
    const formSupp = document.getElementById('form-gate-sale-support');
    const statusSupp = document.getElementById('support-sale-status');
    const btnSubSupp = document.getElementById('btn-submit-support-sale');

    if (btnOpenSupp && modalSupp) {
        btnOpenSupp.addEventListener('click', () => { modalSupp.style.display = 'flex'; });
        btnCloseSupp.addEventListener('click', () => { modalSupp.style.display = 'none'; });
        btnCancelSupp.addEventListener('click', () => { modalSupp.style.display = 'none'; });
        modalSupp.addEventListener('click', (e) => {
            if (e.target === modalSupp) modalSupp.style.display = 'none';
        });
    }

    if (formSupp) {
        formSupp.addEventListener('submit', function(e) {
            e.preventDefault();
            btnSubSupp.disabled = true;
            btnSubSupp.textContent = 'Issuing...';
            statusSupp.style.display = 'none';

            const csrfToken = "<?= csrf_token() ?>";
            const formData = new FormData(formSupp);
            const payload = {
                ticket_type_id: formData.get('ticket_type_id'),
                attendee_name: formData.get('attendee_name'),
                attendee_email: formData.get('attendee_email'),
                quantity: formData.get('quantity'),
                payment_method: formData.get('payment_method'),
                mark_checked_in: formData.get('mark_checked_in') ? 1 : 0,
                csrf_token: csrfToken
            };

            fetch('<?= route('/teams/issue-gate-ticket') ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify(payload)
            })
            .then(r => r.json())
            .then(data => {
                btnSubSupp.disabled = false;
                btnSubSupp.textContent = '✓ Issue Ticket';
                statusSupp.style.display = 'block';

                if (data.success) {
                    statusSupp.innerHTML = `<span style="color:#10b981; font-weight:800;">✓ ${data.message}</span>`;
                    setTimeout(() => {
                        modalSupp.style.display = 'none';
                        window.location.reload();
                    }, 1500);
                } else {
                    statusSupp.innerHTML = `<span style="color:#ef4444; font-weight:800;">✕ ${data.message}</span>`;
                }
            })
            .catch(() => {
                btnSubSupp.disabled = false;
                btnSubSupp.textContent = '✓ Issue Ticket';
                statusSupp.style.display = 'block';
                statusSupp.innerHTML = `<span style="color:#ef4444; font-weight:800;">✕ Connection error. Please try again.</span>`;
            });
        });
    }
});
</script>
@endsection