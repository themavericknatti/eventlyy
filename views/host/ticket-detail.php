<?php

/**
 * Host Ticket Detail View - Executive Boarding Pass Architecture
 *
 * @package Eventlyy
 * @subpackage Views/Host
 */

$siteName = site_setting('site_title', 'Eventlyy');
$ticket = $ticket ?? [];
$event = $event ?? [];

$themePrimary = function_exists('theme_primary_color') ? theme_primary_color() : '#108a55';
$themeSecondary = function_exists('theme_secondary_color') ? theme_secondary_color() : '#0284c7';

$isEventStarted = !empty($ticket['event_date']) && strtotime($ticket['event_date']) <= time();
$isCheckedIn = !empty($ticket['checked_in']);
$isRefunded = in_array($ticket['status'] ?? '', ['Cancelled', 'Refunded']);
$isComped = ($ticket['status'] === 'Comped') || !empty($ticket['is_issued']);
$ticketId = $ticket['id'] ?? '';
$attendeeName = $ticket['attendee_name'] ?? 'Guest';
$initial = strtoupper(substr($attendeeName, 0, 1) ?: 'A');
?>

@extends('layouts/host')

@section('title')
Ticket #<?= e($ticketId) ?> - <?= e($siteName) ?> Host Portal
@endsection

@section('content')
<div class="htd-shell">

    <!-- ─── TOP BREADCRUMB & CONTEXT HEADER ─── -->
    <header class="htd-header">
        <div class="htd-header-left">
            <a href="{{ route('host/event/management/{id}', ['id' => $event['id']]) }}" class="htd-back-pill">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="15 18 9 12 15 6" />
                </svg>
                <span>Back to <?= e($event['name'] ?? 'Event') ?></span>
            </a>
            <div class="htd-title-group">
                <div class="htd-badge-row">
                    <span class="htd-pill htd-pill--brand">🎟️ Digital Pass</span>
                    <?php if (!empty($ticket['is_issued'])): ?>
                        <span class="htd-pill htd-pill--indigo">🏷️ Host Issued / Comp</span>
                    <?php endif; ?>
                    <?php if ($isRefunded): ?>
                        <span class="htd-pill htd-pill--danger">Refunded</span>
                    <?php elseif ($isCheckedIn): ?>
                        <span class="htd-pill htd-pill--success">✓ Gate Admitted</span>
                    <?php else: ?>
                        <span class="htd-pill htd-pill--warning">⏳ Awaiting Admission</span>
                    <?php endif; ?>
                </div>
                <h1 class="htd-page-title">
                    Pass <span class="htd-mono">#<?= e($ticketId) ?></span>
                </h1>
                <p class="htd-page-sub">
                    Issued for <strong><?= e($event['name'] ?? 'Event') ?></strong>
                    <?php if (!empty($event['date'])): ?>
                        &bull; <?= date('D, M j, Y \a\t g:i A', strtotime($event['date'])) ?>
                    <?php endif; ?>
                </p>
            </div>
        </div>

        <div class="htd-header-actions">
            <button type="button" class="htd-btn htd-btn--subtle" onclick="window.print()">
                <span>🖨️</span> Print Pass
            </button>
            <a href="{{ route('host/event/management/{id}', ['id' => $event['id']]) }}#tab-attendees" class="htd-btn htd-btn--outline">
                <span>📋</span> All Attendees
            </a>
        </div>
    </header>

    <!-- ─── EXECUTIVE BOARDING PASS WRAPPER ─── -->
    <article class="htd-ticket-card" id="ticket-pass-card">
        
        <!-- LEFT: Primary Ticket Voucher -->
        <div class="htd-ticket-main">
            
            <!-- Event Hero Banner inside ticket -->
            <div class="htd-event-strip">
                <div class="htd-event-info">
                    <span class="htd-kicker">Event &amp; Venue</span>
                    <h2 class="htd-event-name">
                        <a href="{{ route('host/event/management/{id}', ['id' => $event['id']]) }}"><?= e($event['name'] ?? 'Event') ?></a>
                    </h2>
                    <?php if (!empty($event['location'])): ?>
                        <p class="htd-event-venue">📍 <?= e($event['location']) ?></p>
                    <?php endif; ?>
                </div>
                <div class="htd-event-status">
                    <?php if ($isRefunded): ?>
                        <div class="htd-gate-badge is-refunded">
                            <span class="htd-gate-icon">✕</span>
                            <div>
                                <strong>Ticket Cancelled</strong>
                                <span>Refunded &bull; Void</span>
                            </div>
                        </div>
                    <?php elseif ($isCheckedIn): ?>
                        <div class="htd-gate-badge is-checked">
                            <span class="htd-gate-icon">✓</span>
                            <div>
                                <strong>Admitted at Gate</strong>
                                <span><?= !empty($ticket['checked_in_at']) ? date('h:i A \o\n M j', strtotime($ticket['checked_in_at'])) : 'Verified Entry' ?></span>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="htd-gate-badge is-awaiting">
                            <span class="htd-gate-icon">⏳</span>
                            <div>
                                <strong>Awaiting Entry</strong>
                                <span>Ready to Scan</span>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Attendee Profile Block -->
            <div class="htd-attendee-row">
                <div class="htd-avatar-wrap">
                    <div class="htd-avatar"><?= $initial ?></div>
                </div>
                <div class="htd-attendee-meta">
                    <span class="htd-kicker">Ticket Holder</span>
                    <h3 class="htd-attendee-name"><?= e($attendeeName) ?></h3>
                    <div class="htd-contact-links">
                        <a href="mailto:<?= e($ticket['attendee_email'] ?? '') ?>" class="htd-link">
                            <?= e($ticket['attendee_email'] ?? 'No email') ?>
                        </a>
                        <span class="htd-bullet">&bull;</span>
                        <span class="htd-user-tier">
                            <?= !empty($ticket['user_id']) ? 'Verified Platform Member' : 'Guest Attendee' ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- 4-Tile Order Metrics Bento -->
            <div class="htd-metrics-bento">
                <div class="htd-metric-box">
                    <span class="htd-metric-lbl">Ticket Tier</span>
                    <div class="htd-metric-val"><?= e($ticket['ticket_type'] ?? 'Standard Pass') ?></div>
                    <span class="htd-metric-sub">Tier Quota: 1 Pass</span>
                </div>

                <div class="htd-metric-box">
                    <span class="htd-metric-lbl">Order Value</span>
                    <div class="htd-metric-val htd-text-success"><?= format_price($ticket['price'] ?? 0) ?></div>
                    <span class="htd-metric-sub">
                        <?= !empty($ticket['is_issued']) ? 'Complimentary' : 'Verified Payment' ?>
                    </span>
                </div>

                <div class="htd-metric-box">
                    <span class="htd-metric-lbl">Ticket Pass Code</span>
                    <div class="htd-metric-val htd-mono htd-copy-val" id="copy-pass-code" title="Click to copy code">
                        <span><?= e($ticketId) ?></span>
                        <button type="button" class="htd-copy-icon" aria-label="Copy code">📋</button>
                    </div>
                    <span class="htd-metric-sub" id="copy-feedback">1-click copy code</span>
                </div>

                <div class="htd-metric-box">
                    <span class="htd-metric-lbl">Purchase Date</span>
                    <div class="htd-metric-val" style="font-size:0.95rem;">
                        <?= !empty($ticket['purchase_date']) ? date('M j, Y', strtotime($ticket['purchase_date'])) : 'N/A' ?>
                    </div>
                    <span class="htd-metric-sub">
                        <?= !empty($ticket['purchase_date']) ? date('h:i A', strtotime($ticket['purchase_date'])) : '' ?>
                    </span>
                </div>
            </div>

            <!-- Security & Lifecycle Banner -->
            <div class="htd-security-bar">
                <div class="htd-sec-item">
                    <span class="htd-sec-icon">🛡️</span>
                    <div>
                        <strong>Payment Status:</strong>
                        <span><?= e($ticket['status_detail'] ?? ($ticket['status'] ?? 'Confirmed')) ?></span>
                    </div>
                </div>
                <div class="htd-sec-item">
                    <span class="htd-sec-icon">🔐</span>
                    <div>
                        <strong>Audit Ledger:</strong>
                        <span><?= !empty($ticket['is_issued']) ? 'Host Manual Gateway' : 'Online Gateway Settlement' ?></span>
                    </div>
                </div>
            </div>

        </div>

        <!-- PERFORATION DIVIDER (Horizontal on Mobile, Vertical on Desktop) -->
        <div class="htd-perforation" aria-hidden="true">
            <div class="htd-scallop top"></div>
            <div class="htd-perf-line"></div>
            <div class="htd-scallop bottom"></div>
        </div>

        <!-- RIGHT: Tear-off Stub (QR & Controls) -->
        <div class="htd-ticket-stub">
            
            <div class="htd-stub-head">
                <span class="htd-stub-badge">Gate Admission</span>
                <span class="htd-stub-tag"><?= strtoupper(substr($ticket['ticket_type'] ?? 'PASS', 0, 10)) ?></span>
            </div>

            <!-- Scannable High-Contrast QR Code -->
            <div class="htd-qr-container">
                <div class="htd-qr-box">
                    <img src="<?= ticket_qr_url($ticketId, 220) ?>" alt="QR Pass #<?= e($ticketId) ?>" width="150" height="150" loading="eager" />
                </div>
                <span class="htd-qr-note">Scan with Ticket Scanner</span>
            </div>

            <!-- Quick Management Controls -->
            <div class="htd-controls-stack">
                
                <!-- Check-in / Uncheck Toggle -->
                <?php if (!$isRefunded): ?>
                    <?php if ($isCheckedIn): ?>
                        <button type="button" class="htd-action-btn htd-btn--warning" id="btn-toggle-checkin" data-ticket-id="<?= e($ticketId) ?>" data-action="uncheck" data-action-url="<?= route('host/event/ticket/' . $ticketId . '/action') ?>">
                            <span>↩️</span> Undo Check-In
                        </button>
                    <?php else: ?>
                        <button type="button" class="htd-action-btn htd-btn--primary" id="btn-toggle-checkin" data-ticket-id="<?= e($ticketId) ?>" data-action="check_in" data-action-url="<?= route('host/event/ticket/' . $ticketId . '/action') ?>">
                            <span>🎟️</span> Check In Attendee
                        </button>
                    <?php endif; ?>
                <?php endif; ?>

                <!-- Resend Email -->
                <button type="button" class="htd-action-btn htd-btn--indigo" id="btn-resend-email" data-ticket-id="<?= e($ticketId) ?>" data-action-url="<?= route('host/event/ticket/' . $ticketId . '/action') ?>">
                    <span>✉️</span> Resend Ticket Email
                </button>

                <!-- Refund Button -->
                <?php if ($isRefunded): ?>
                    <button type="button" class="htd-action-btn htd-btn--disabled" disabled>
                        <span>✓</span> Ticket Refunded
                    </button>
                <?php elseif ($isCheckedIn): ?>
                    <button type="button" class="htd-action-btn htd-btn--disabled" disabled title="Uncheck ticket first to allow refund">
                        <span>🔒</span> Checked In (No Refund)
                    </button>
                <?php elseif ($isEventStarted): ?>
                    <button type="button" class="htd-action-btn htd-btn--disabled" disabled title="Event has already started">
                        <span>⏱️</span> Event Started
                    </button>
                <?php else: ?>
                    <button type="button" class="htd-action-btn htd-btn--danger" id="btn-refund-ticket" data-ticket-id="<?= e($ticketId) ?>" data-action-url="<?= route('host/event/ticket/' . $ticketId . '/action') ?>">
                        <span>💸</span> Refund Ticket
                    </button>
                <?php endif; ?>

            </div>

            <!-- Stub Footer -->
            <div class="htd-stub-foot">
                <span class="htd-mono">ID: <?= e(substr($ticketId, 0, 14)) ?></span>
                <span>• Active</span>
            </div>

        </div>

    </article>

</div>

<!-- Confirmation Modal for Destructive Actions -->
<div class="htd-modal-backdrop hidden" id="htd-confirm-modal" role="dialog" aria-modal="true" aria-labelledby="htd-modal-title">
    <div class="htd-modal-card">
        <div class="htd-modal-header">
            <span class="htd-modal-icon">⚠️</span>
            <div>
                <h3 id="htd-modal-title" class="htd-modal-title">Confirm Action</h3>
                <p id="htd-modal-desc" class="htd-modal-desc">Are you sure you wish to proceed?</p>
            </div>
        </div>
        <div class="htd-modal-actions">
            <button type="button" class="htd-btn htd-btn--subtle" id="htd-modal-cancel">Cancel</button>
            <button type="button" class="htd-btn htd-btn--danger" id="htd-modal-confirm">Yes, Proceed</button>
        </div>
    </div>
</div>

<!-- Toast Container -->
<div id="htd-toast-container" class="htd-toast-container" aria-live="polite"></div>

<!-- ─── MODERN RESPONSIVE STYLES ─── -->
<style>
.htd-shell {
    --htd-primary: var(--primary, <?= $themePrimary ?>);
    --htd-primary-hover: var(--primary-hover, <?= $themeSecondary ?>);
    --htd-primary-rgb: var(--primary-rgb, 16, 138, 85);
    
    /* Surface Colors (Light) */
    --htd-card-bg: rgb(var(--card, 255 255 255));
    --htd-subtle-bg: #f8fafc;
    --htd-border: var(--border-color, #e2e8f0);
    --htd-text: rgb(var(--text, 15 23 42));
    --htd-muted: rgb(var(--muted, 100 116 139));
    --htd-radius: 20px;
    
    max-width: 960px;
    margin: 0 auto 3.5rem;
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

/* Dark Mode Overrides */
:root[data-theme='dark'] .htd-shell,
[data-theme='dark'] .htd-shell,
body[data-theme='dark'] .htd-shell {
    --htd-card-bg: rgb(var(--card, 18 21 27));
    --htd-subtle-bg: rgba(255, 255, 255, 0.04);
    --htd-border: rgba(255, 255, 255, 0.09);
}

/* ─── Header ─── */
.htd-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    flex-wrap: wrap;
    gap: 1.25rem;
}
.htd-back-pill {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    font-size: 0.84rem;
    font-weight: 700;
    color: var(--htd-primary);
    text-decoration: none;
    margin-bottom: 0.6rem;
    transition: transform 0.15s ease;
}
.htd-back-pill:hover {
    transform: translateX(-3px);
}
.htd-title-group {
    display: flex;
    flex-direction: column;
    gap: 0.35rem;
}
.htd-badge-row {
    display: flex;
    align-items: center;
    gap: 0.45rem;
    flex-wrap: wrap;
}
.htd-pill {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    font-size: 0.72rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    padding: 0.25rem 0.65rem;
    border-radius: 20px;
}
.htd-pill--brand {
    background: rgba(var(--htd-primary-rgb), 0.12);
    color: var(--htd-primary);
    border: 1px solid rgba(var(--htd-primary-rgb), 0.25);
}
.htd-pill--indigo {
    background: rgba(99, 102, 241, 0.12);
    color: #6366f1;
    border: 1px solid rgba(99, 102, 241, 0.25);
}
.htd-pill--success {
    background: rgba(16, 185, 129, 0.12);
    color: #10b981;
    border: 1px solid rgba(16, 185, 129, 0.25);
}
.htd-pill--warning {
    background: rgba(245, 158, 11, 0.12);
    color: #f59e0b;
    border: 1px solid rgba(245, 158, 11, 0.25);
}
.htd-pill--danger {
    background: rgba(239, 68, 68, 0.12);
    color: #ef4444;
    border: 1px solid rgba(239, 68, 68, 0.25);
}

.htd-page-title {
    font-size: clamp(1.4rem, 2.5vw, 1.85rem);
    font-weight: 900;
    margin: 0;
    color: var(--htd-text);
    line-height: 1.2;
}
.htd-mono {
    font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
}
.htd-page-sub {
    font-size: 0.88rem;
    color: var(--htd-muted);
    margin: 0;
}
.htd-header-actions {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex-wrap: wrap;
}

/* ─── Buttons ─── */
.htd-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.45rem;
    padding: 0.55rem 1rem;
    font-size: 0.84rem;
    font-weight: 700;
    border-radius: 10px;
    cursor: pointer;
    text-decoration: none;
    transition: all 0.18s ease;
    border: 1px solid transparent;
}
.htd-btn--subtle {
    background: var(--htd-subtle-bg);
    color: var(--htd-text);
    border-color: var(--htd-border);
}
.htd-btn--subtle:hover {
    background: rgba(var(--htd-primary-rgb), 0.08);
    border-color: var(--htd-primary);
}
.htd-btn--outline {
    background: transparent;
    color: var(--htd-primary);
    border-color: rgba(var(--htd-primary-rgb), 0.35);
}
.htd-btn--outline:hover {
    background: rgba(var(--htd-primary-rgb), 0.1);
}

/* ─── The Boarding Pass Ticket Card ─── */
.htd-ticket-card {
    background: var(--htd-card-bg);
    border: 1px solid var(--htd-border);
    border-radius: var(--htd-radius);
    display: flex;
    position: relative;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
    overflow: hidden;
}

/* Main Voucher Area (Left) */
.htd-ticket-main {
    flex: 1 1 auto;
    padding: 2rem;
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
    min-width: 0;
}

.htd-kicker {
    font-size: 0.72rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: var(--htd-muted);
    display: block;
    margin-bottom: 0.25rem;
}

/* Event Strip */
.htd-event-strip {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    flex-wrap: wrap;
    gap: 1rem;
    padding-bottom: 1.25rem;
    border-bottom: 1px solid var(--htd-border);
}
.htd-event-name {
    font-size: 1.25rem;
    font-weight: 800;
    margin: 0;
    line-height: 1.3;
}
.htd-event-name a {
    color: var(--htd-text);
    text-decoration: none;
}
.htd-event-name a:hover {
    color: var(--htd-primary);
}
.htd-event-venue {
    font-size: 0.82rem;
    color: var(--htd-muted);
    margin: 0.25rem 0 0;
}

/* Gate Badge */
.htd-gate-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.65rem;
    padding: 0.6rem 0.95rem;
    border-radius: 12px;
}
.htd-gate-badge strong {
    display: block;
    font-size: 0.84rem;
    line-height: 1.2;
}
.htd-gate-badge span {
    font-size: 0.74rem;
    opacity: 0.85;
}
.htd-gate-icon {
    font-size: 1.1rem;
    font-weight: 900;
}
.htd-gate-badge.is-checked {
    background: rgba(16, 185, 129, 0.12);
    color: #10b981;
    border: 1px solid rgba(16, 185, 129, 0.25);
}
.htd-gate-badge.is-awaiting {
    background: rgba(245, 158, 11, 0.12);
    color: #f59e0b;
    border: 1px solid rgba(245, 158, 11, 0.25);
}
.htd-gate-badge.is-refunded {
    background: rgba(239, 68, 68, 0.12);
    color: #ef4444;
    border: 1px solid rgba(239, 68, 68, 0.25);
}

/* Attendee Block */
.htd-attendee-row {
    display: flex;
    align-items: center;
    gap: 1rem;
}
.htd-avatar {
    width: 48px;
    height: 48px;
    border-radius: 14px;
    background: linear-gradient(135deg, var(--htd-primary), var(--htd-primary-hover));
    color: #fff;
    font-weight: 800;
    font-size: 1.25rem;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 12px rgba(var(--htd-primary-rgb), 0.25);
    flex-shrink: 0;
}
.htd-attendee-name {
    font-size: 1.15rem;
    font-weight: 800;
    margin: 0;
    color: var(--htd-text);
}
.htd-contact-links {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex-wrap: wrap;
    font-size: 0.84rem;
    margin-top: 0.2rem;
}
.htd-link {
    color: var(--htd-primary);
    text-decoration: none;
    font-weight: 600;
}
.htd-link:hover {
    text-decoration: underline;
}
.htd-bullet {
    color: var(--htd-muted);
}
.htd-user-tier {
    color: var(--htd-muted);
}

/* Bento Metrics Grid */
.htd-metrics-bento {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 0.85rem;
}
.htd-metric-box {
    background: var(--htd-subtle-bg);
    border: 1px solid var(--htd-border);
    border-radius: 14px;
    padding: 0.85rem 1rem;
    display: flex;
    flex-direction: column;
    gap: 0.2rem;
}
.htd-metric-lbl {
    font-size: 0.72rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: var(--htd-muted);
}
.htd-metric-val {
    font-size: 1.1rem;
    font-weight: 800;
    color: var(--htd-text);
    word-break: break-all;
}
.htd-metric-sub {
    font-size: 0.74rem;
    color: var(--htd-muted);
}
.htd-text-success {
    color: #10b981 !important;
}

.htd-copy-val {
    display: flex;
    align-items: center;
    gap: 0.35rem;
    cursor: pointer;
}
.htd-copy-icon {
    background: none;
    border: none;
    cursor: pointer;
    padding: 0;
    font-size: 0.85rem;
    opacity: 0.6;
    transition: opacity 0.15s ease;
}
.htd-copy-val:hover .htd-copy-icon {
    opacity: 1;
}

/* Security Bar */
.htd-security-bar {
    background: rgba(var(--htd-primary-rgb), 0.04);
    border: 1px solid rgba(var(--htd-primary-rgb), 0.18);
    border-radius: 12px;
    padding: 0.75rem 1rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 0.75rem;
    font-size: 0.82rem;
}
.htd-sec-item {
    display: flex;
    align-items: center;
    gap: 0.45rem;
    color: var(--htd-text);
}
.htd-sec-item span {
    color: var(--htd-muted);
}

/* ─── Perforation Divider ─── */
.htd-perforation {
    width: 24px;
    position: relative;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: space-between;
    pointer-events: none;
    flex-shrink: 0;
}
.htd-perf-line {
    position: absolute;
    top: 16px;
    bottom: 16px;
    left: 50%;
    transform: translateX(-50%);
    border-left: 2px dashed rgba(var(--htd-muted), 0.3);
}
.htd-scallop {
    width: 26px;
    height: 26px;
    border-radius: 50%;
    background: rgb(var(--bg, 248 250 252));
    border: 1px solid var(--htd-border);
    position: absolute;
    left: 50%;
    transform: translateX(-50%);
    z-index: 5;
}
:root[data-theme='dark'] .htd-scallop,
[data-theme='dark'] .htd-scallop {
    background: rgb(var(--bg, 11 13 18));
}
.htd-scallop.top {
    top: -13px;
}
.htd-scallop.bottom {
    bottom: -13px;
}

/* ─── Right Stub ─── */
.htd-ticket-stub {
    width: 300px;
    background: var(--htd-subtle-bg);
    padding: 2rem 1.5rem;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: space-between;
    gap: 1.25rem;
    flex-shrink: 0;
}
.htd-stub-head {
    width: 100%;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.htd-stub-badge {
    font-size: 0.72rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: var(--htd-muted);
}
.htd-stub-tag {
    font-size: 0.68rem;
    font-weight: 800;
    padding: 0.2rem 0.45rem;
    border-radius: 6px;
    background: rgba(var(--htd-primary-rgb), 0.12);
    color: var(--htd-primary);
}

.htd-qr-container {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.45rem;
}
.htd-qr-box {
    background: #ffffff;
    padding: 0.55rem;
    border-radius: 12px;
    box-shadow: 0 4px 14px rgba(0, 0, 0, 0.06);
    border: 1px solid rgba(0, 0, 0, 0.08);
}
.htd-qr-box img {
    display: block;
    border-radius: 4px;
}
.htd-qr-note {
    font-size: 0.72rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: var(--htd-muted);
}

/* Action Controls */
.htd-controls-stack {
    width: 100%;
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}
.htd-action-btn {
    width: 100%;
    min-height: 40px;
    padding: 0.55rem 0.85rem;
    border-radius: 10px;
    font-size: 0.82rem;
    font-weight: 800;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.4rem;
    cursor: pointer;
    border: 1px solid transparent;
    transition: all 0.15s ease;
}
.htd-btn--primary {
    background: var(--htd-primary);
    color: #ffffff;
}
.htd-btn--primary:hover {
    background: var(--htd-primary-hover);
    transform: translateY(-1px);
}
.htd-btn--warning {
    background: rgba(245, 158, 11, 0.12);
    color: #d97706;
    border-color: rgba(245, 158, 11, 0.28);
}
.htd-btn--warning:hover {
    background: rgba(245, 158, 11, 0.2);
}
.htd-btn--indigo {
    background: rgba(99, 102, 241, 0.1);
    color: #4f46e5;
    border-color: rgba(99, 102, 241, 0.25);
}
.htd-btn--indigo:hover {
    background: rgba(99, 102, 241, 0.18);
}
.htd-btn--danger {
    background: rgba(239, 68, 68, 0.1);
    color: #dc2626;
    border-color: rgba(239, 68, 68, 0.25);
}
.htd-btn--danger:hover {
    background: rgba(239, 68, 68, 0.18);
}
.htd-btn--disabled {
    background: rgba(var(--htd-muted), 0.08);
    color: var(--htd-muted);
    border-color: rgba(var(--htd-muted), 0.15);
    cursor: not-allowed;
    opacity: 0.75;
}

.htd-stub-foot {
    font-size: 0.72rem;
    color: var(--htd-muted);
    display: flex;
    align-items: center;
    gap: 0.4rem;
}

/* ─── Confirmation Modal ─── */
.htd-modal-backdrop {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.65);
    backdrop-filter: blur(4px);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1rem;
    z-index: 9999;
}
.htd-modal-backdrop.hidden {
    display: none !important;
}
.htd-modal-card {
    background: var(--htd-card-bg);
    border: 1px solid var(--htd-border);
    border-radius: 16px;
    max-width: 440px;
    width: 100%;
    padding: 1.5rem;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.25);
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
}
.htd-modal-header {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
}
.htd-modal-icon {
    font-size: 1.5rem;
}
.htd-modal-title {
    margin: 0;
    font-size: 1.1rem;
    font-weight: 800;
    color: var(--htd-text);
}
.htd-modal-desc {
    margin: 0.25rem 0 0;
    font-size: 0.86rem;
    color: var(--htd-muted);
    line-height: 1.45;
}
.htd-modal-actions {
    display: flex;
    justify-content: flex-end;
    gap: 0.5rem;
}

/* ─── Toast Container ─── */
.htd-toast-container {
    position: fixed;
    bottom: 1.5rem;
    right: 1.5rem;
    z-index: 10000;
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}
.htd-toast {
    padding: 0.75rem 1.15rem;
    border-radius: 10px;
    color: #fff;
    font-size: 0.86rem;
    font-weight: 700;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
    display: flex;
    align-items: center;
    gap: 0.45rem;
    animation: htdSlideIn 0.2s ease;
}
@keyframes htdSlideIn {
    from { transform: translateY(12px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}
.htd-toast--success { background: #059669; }
.htd-toast--error { background: #dc2626; }
.htd-toast--info { background: #2563eb; }

/* ─── RESPONSIVE BREAKPOINTS ─── */
@media (max-width: 860px) {
    .htd-ticket-card {
        flex-direction: column;
    }
    .htd-perforation {
        width: 100%;
        height: 24px;
        flex-direction: row;
    }
    .htd-perf-line {
        top: 50%;
        bottom: auto;
        left: 16px;
        right: 16px;
        width: auto;
        height: 2px;
        transform: translateY(-50%);
        border-left: none;
        border-top: 2px dashed rgba(var(--htd-muted), 0.3);
    }
    .htd-scallop {
        top: 50%;
        transform: translateY(-50%);
    }
    .htd-scallop.top {
        left: -13px;
        top: 50%;
    }
    .htd-scallop.bottom {
        right: -13px;
        left: auto;
        bottom: auto;
        top: 50%;
    }
    .htd-ticket-stub {
        width: 100%;
        padding: 1.75rem 1.5rem;
    }
    .htd-controls-stack {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 0.5rem;
    }
}

@media (max-width: 600px) {
    .htd-ticket-main {
        padding: 1.25rem;
    }
    .htd-metrics-bento {
        grid-template-columns: 1fr;
    }
    .htd-security-bar {
        flex-direction: column;
        align-items: flex-start;
    }
    .htd-controls-stack {
        grid-template-columns: 1fr;
    }
}
</style>

<!-- ─── INTERACTIVE PASS JAVASCRIPT ─── -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const csrfToken = "<?= csrf_token() ?>";

    function showToast(msg, type = 'success') {
        const c = document.getElementById('htd-toast-container');
        if (!c) return;
        const t = document.createElement('div');
        t.className = `htd-toast htd-toast--${type}`;
        t.textContent = msg;
        c.appendChild(t);
        setTimeout(() => {
            t.style.opacity = '0';
            t.style.transition = 'opacity 0.25s ease';
            setTimeout(() => t.remove(), 250);
        }, 3200);
    }

    // 1-Click Code Copy
    const copyWrap = document.getElementById('copy-pass-code');
    if (copyWrap) {
        copyWrap.addEventListener('click', function() {
            const code = this.querySelector('span')?.textContent?.trim() || '';
            if (!code) return;
            navigator.clipboard.writeText(code).then(() => {
                const fb = document.getElementById('copy-feedback');
                if (fb) fb.textContent = '✓ Copied to clipboard!';
                showToast('Ticket code copied to clipboard!', 'info');
                setTimeout(() => {
                    if (fb) fb.textContent = '1-click copy code';
                }, 2500);
            });
        });
    }

    // Check-in / Uncheck Toggle Button
    const checkinBtn = document.getElementById('btn-toggle-checkin');
    if (checkinBtn) {
        checkinBtn.addEventListener('click', function() {
            const btn = this;
            const action = btn.dataset.action;
            const url = btn.dataset.actionUrl;

            btn.disabled = true;
            btn.innerHTML = '<span>⏳</span> Processing...';

            const formData = new FormData();
            formData.append('_token', csrfToken);
            formData.append('action', action);

            fetch(url, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(r => r.json())
            .then(data => {
                btn.disabled = false;
                if (data.success) {
                    showToast(data.message || 'Admission status updated!', 'success');
                    // Reload to update gate status badge cleanly
                    setTimeout(() => window.location.reload(), 400);
                } else {
                    showToast(data.message || 'Failed to update check-in.', 'error');
                }
            })
            .catch(() => {
                btn.disabled = false;
                showToast('Connection error. Please try again.', 'error');
            });
        });
    }

    // Resend Email Button
    const resendBtn = document.getElementById('btn-resend-email');
    if (resendBtn) {
        resendBtn.addEventListener('click', function() {
            const btn = this;
            const url = btn.dataset.actionUrl;

            btn.disabled = true;
            btn.innerHTML = '<span>⏳</span> Sending Email...';

            const formData = new FormData();
            formData.append('_token', csrfToken);
            formData.append('action', 'resend');

            fetch(url, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(r => r.json())
            .then(data => {
                btn.disabled = false;
                btn.innerHTML = '<span>✉️</span> Resend Ticket Email';
                if (data.success) {
                    showToast(data.message || 'Ticket pass resent successfully!', 'success');
                } else {
                    showToast(data.message || 'Failed to resend email.', 'error');
                }
            })
            .catch(() => {
                btn.disabled = false;
                btn.innerHTML = '<span>✉️</span> Resend Ticket Email';
                showToast('Network error while resending pass.', 'error');
            });
        });
    }

    // Refund Confirmation Modal Trigger
    const refundBtn = document.getElementById('btn-refund-ticket');
    const modal = document.getElementById('htd-confirm-modal');
    const modalCancel = document.getElementById('htd-modal-cancel');
    const modalConfirm = document.getElementById('htd-modal-confirm');

    if (refundBtn && modal) {
        refundBtn.addEventListener('click', function() {
            document.getElementById('htd-modal-title').textContent = 'Refund Ticket Order';
            document.getElementById('htd-modal-desc').textContent = 'Are you sure you want to refund this ticket? The ticket will be marked as Cancelled/Void and gateway refund requested.';
            modal.classList.remove('hidden');
        });

        if (modalCancel) {
            modalCancel.addEventListener('click', () => modal.classList.add('hidden'));
        }

        if (modalConfirm) {
            modalConfirm.addEventListener('click', function() {
                modal.classList.add('hidden');
                refundBtn.disabled = true;
                refundBtn.innerHTML = '<span>⏳</span> Processing Refund...';

                const formData = new FormData();
                formData.append('_token', csrfToken);
                formData.append('action', 'refund');

                fetch(refundBtn.dataset.actionUrl, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: formData
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        showToast(data.message || 'Ticket refunded successfully!', 'success');
                        setTimeout(() => window.location.reload(), 600);
                    } else {
                        refundBtn.disabled = false;
                        refundBtn.innerHTML = '<span>💸</span> Refund Ticket';
                        showToast(data.message || 'Refund failed.', 'error');
                    }
                })
                .catch(() => {
                    refundBtn.disabled = false;
                    refundBtn.innerHTML = '<span>💸</span> Refund Ticket';
                    showToast('Connection error while processing refund.', 'error');
                });
            });
        }
    }
});
</script>
@endsection
