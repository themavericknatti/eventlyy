<?php

/**
 * Host Ticket Detail View
 *
 * This file contains the HTML markup for the host ticket detail page.
 *
 * @package Evently
 * @subpackage Views/Host
 *
 * @author Your Name <Celio Natti>
 * @license MIT License
 * @version 1.0.0
 */

$siteName = site_setting('site_title', 'Eventlyy');
$ticket = $ticket ?? [];
$event = $event ?? [];

?>

@extends('layouts/host')

@section('title')
Host Ticket Detail - View Your <?= e($siteName) ?> Tickets
@endsection

@section('content')
<div class="host-ticket-wrapper">
    <div class="page-hero" style="margin-bottom:1rem;">
        <div>
            <span class="panel-badge">Host Ticket</span>
            <h1>Ticket Details</h1>
            <p class="lede">Review the attendee ticket and manage actions for this order.</p>
        </div>
    </div>

    <!-- Breadcrumb back link -->
    <a class="back-link" href="{{ route('host/event/management/{id}', ['id' => $event['id']]) }}" id="back-link" style="display:inline-flex; align-items:center; gap:0.4rem; font-weight:700; text-decoration:none; color:rgb(var(--brand)); font-size:0.88rem; margin-bottom:1.25rem;">
        ← Back to event
    </a>

    <section class="ticket-detail-container" id="ticket-card">
        <!-- Left side: The ticket body -->
        <div class="ticket-body">
            <div class="ticket-header-row">
                <div class="ticket-title-block">
                    <span class="ticket-label-badge">Ticket Pass</span>
                    <h2 id="ticket-id-display">{{ $ticket['id'] }}</h2>
                </div>
                
                <!-- Status badges group -->
                <div class="ticket-badges-wrap">
                    <?php
                    $statusClass = 'pending';
                    $ticketStatus = $ticket['status'] ?? '';
                    if (in_array($ticketStatus, ['Paid', 'Comped'])) {
                        $statusClass = 'confirmed';
                    } elseif ($ticketStatus === 'Cancelled') {
                        $statusClass = 'cancelled';
                    }
                    ?>
                    <span id="ticket-status" class="status-badge <?= $statusClass ?>"><?= strtoupper($ticketStatus ?: 'Unknown') ?></span>
                    
                    <?php if (!empty($ticket['checked_in'])): ?>
                        <span id="checkin-status" class="status-badge confirmed" style="background:rgba(16, 185, 129, 0.12); color:#059669; border:1px solid rgba(16, 185, 129, 0.25);">✓ Checked In (<?= date('h:i A', strtotime($ticket['checked_in_at'])) ?>)</span>
                    <?php else: ?>
                        <span id="checkin-status" class="status-badge pending" style="background:rgba(245, 158, 11, 0.12); color:#d97706; border:1px solid rgba(245, 158, 11, 0.25);">⏳ Awaiting Entry</span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="ticket-details-grid">
                <!-- Event Info -->
                <div class="detail-item full-width">
                    <span class="detail-label">Event</span>
                    <div class="detail-value event-name-val">
                        <a id="event-link" href="{{ route('host/event/management/{id}', ['id' => $event['id']]) }}">{{ $event['name'] }}</a>
                    </div>
                </div>

                <!-- Holder Info -->
                <div class="detail-item">
                    <span class="detail-label">Attendee / Holder</span>
                    <div class="detail-value" id="holder-name"><?= e($ticket['attendee_name'] ?? 'Guest') ?></div>
                    <div class="detail-subtext">
                        <a id="holder-email" href="mailto:<?= e($ticket['attendee_email'] ?? '') ?>"><?= e($ticket['attendee_email'] ?? '') ?></a>
                        <span id="holder-phone">—</span>
                    </div>
                </div>

                <!-- Ticket Type & Quantity -->
                <div class="detail-item">
                    <span class="detail-label">Ticket Type &amp; Qty</span>
                    <div class="detail-value">
                        <span id="ticket-type"><?= e($ticket['ticket_type'] ?? 'General Admission') ?></span> &times;
                        <span id="ticket-qty">1</span>
                    </div>
                </div>

                <!-- Price & Purchased At -->
                <div class="detail-item">
                    <span class="detail-label">Total Price</span>
                    <div class="detail-value" id="ticket-price" style="color:rgb(var(--brand)); font-weight:800;"><?= format_price($ticket['price'] ?? 0) ?></div>
                </div>

                <div class="detail-item">
                    <span class="detail-label">Purchased On</span>
                    <div class="detail-value" id="ticket-date"><?= isset($ticket['purchase_date']) ? date('M d, Y \a\t h:i A', strtotime($ticket['purchase_date'])) : 'N/A' ?></div>
                </div>
            </div>

            <div class="ticket-status-note">
                <span class="info-icon">ℹ</span>
                <span id="status-detail"><?= e($ticket['status_detail'] ?? 'Payment confirmed') ?></span>
            </div>

            <?php if (!empty($ticket['is_issued'])): ?>
                <div class="host-issued-badge">
                    🏷️ Issued by Host<?php if (($ticket['status_detail'] ?? '') === 'Free ticket issued by host'): ?> (Free)<?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Right side: The tear-off ticket stub (QR & Actions) -->
        <div class="ticket-stub">
            <div class="stub-divider">
                <div class="stub-cutout top"></div>
                <div class="stub-cutout bottom"></div>
            </div>
            <div class="stub-content">
                <div class="ticket-qr" id="ticket-qr">
                    <img id="qr-img" src="<?= ticket_qr_url($ticket['id'], 220) ?>"
                        alt="QR code for Ticket <?= e($ticket['id']) ?>" width="150" height="150">
                    <span class="qr-caption">Scan for entry</span>
                </div>

                <div class="card-actions">
                    <?php 
                        $isEventStarted = !empty($ticket['event_date']) && strtotime($ticket['event_date']) <= time();
                        $isCheckedIn = !empty($ticket['checked_in']);
                        $isRefunded = in_array($ticket['status'] ?? '', ['Cancelled', 'Refunded']);
                    ?>

                    <!-- Check-in / Uncheck Toggle Button -->
                    <?php if (!$isRefunded): ?>
                        <?php if ($isCheckedIn): ?>
                            <button type="button" id="action-uncheck" class="admin-action-btn btn-action-uncheck" data-action="uncheck" data-action-url="<?= route('host/event/ticket/' . $ticket['id'] . '/action') ?>" data-csrf-token="<?= csrf_token() ?>" title="Undo check-in">
                                <span>↩️</span>
                                Uncheck Ticket
                            </button>
                        <?php else: ?>
                            <button type="button" id="action-checkin" class="admin-action-btn primary btn-action-checkin" data-action="check_in" data-action-url="<?= route('host/event/ticket/' . $ticket['id'] . '/action') ?>" data-csrf-token="<?= csrf_token() ?>" title="Check in this attendee">
                                <span>🎟️</span>
                                Check In Ticket
                            </button>
                        <?php endif; ?>
                    <?php endif; ?>

                    <button type="button" id="action-resend" class="admin-action-btn btn-action-resend" data-action="resend" data-action-url="<?= route('host/event/ticket/' . $ticket['id'] . '/action') ?>" data-csrf-token="<?= csrf_token() ?>" title="Resend confirmation email with ticket PDF">
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5">
                            <path d="M4 4v5h.582m15.356 2A8.001 8.001 0 1121.21 8H17" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        Resend Email
                    </button>

                    <?php if ($isRefunded): ?>
                        <button type="button" class="admin-action-btn danger" disabled style="opacity:0.5; cursor:not-allowed;" title="Ticket is already refunded">
                            Refunded
                        </button>
                    <?php elseif ($isCheckedIn): ?>
                        <button type="button" class="admin-action-btn danger" disabled style="opacity:0.5; cursor:not-allowed;" title="Cannot refund a ticket that has already been checked in (uncheck first if needed)">
                            Refund (Checked In)
                        </button>
                    <?php elseif ($isEventStarted): ?>
                        <button type="button" class="admin-action-btn danger" disabled style="opacity:0.5; cursor:not-allowed;" title="Cannot refund tickets after the event has started">
                            Refund (Event Started)
                        </button>
                    <?php else: ?>
                        <button type="button" id="action-refund" class="admin-action-btn danger" data-action="refund" data-action-url="<?= route('host/event/ticket/' . $ticket['id'] . '/action') ?>" data-csrf-token="<?= csrf_token() ?>" title="Process refund for this ticket">
                            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5">
                                <path d="M9 15L3 9m0 0l6-6M3 9h12a6 6 0 010 12h-3" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            Refund
                        </button>
                    <?php endif; ?>

                    <button type="button" id="action-flag" class="admin-action-btn btn-action-flag" data-action="flag" data-action-url="<?= route('host/event/ticket/' . $ticket['id'] . '/action') ?>" data-csrf-token="<?= csrf_token() ?>" title="Flag ticket for administrative review">
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5">
                            <path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        Flag
                    </button>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Confirmation modal (hidden by default) -->
<div id="modal-backdrop" class="modal-backdrop hidden">
    <div class="modal" id="confirm-modal">
        <h3>Confirm Action</h3>
        <p id="confirm-message">Are you sure?</p>
        <div class="modal-actions">
            <button type="button" id="confirm-yes" class="admin-action-btn primary">Yes, proceed</button>
            <button type="button" id="confirm-no" class="admin-action-btn">Cancel</button>
        </div>
    </div>
</div>

<div id="toast-container" aria-live="polite" aria-atomic="true"></div>

<style>
/* ════════════════════════════════════════════════════════════════
   HOST TICKET DETAIL RESPONSIVE & MOBILE-CENTERING STYLES
   ════════════════════════════════════════════════════════════════ */

.host-ticket-wrapper {
    width: 100%;
    max-width: 880px;
    margin: 0 auto 3rem auto;
    box-sizing: border-box;
    min-width: 0;
}

.host-ticket-wrapper * {
    box-sizing: border-box;
    min-width: 0;
}

/* Ticket Detail Container */
.ticket-detail-container {
    max-width: 880px;
    width: 100%;
    margin: 1.5rem auto;
    border-radius: 24px;
    background: var(--bg-card, #ffffff);
    box-shadow: 0 16px 40px rgba(0, 0, 0, 0.06);
    display: flex;
    overflow: hidden;
    border: 1px solid rgba(var(--muted), 0.14);
    position: relative;
}

/* Ticket Main Body */
.ticket-body {
    flex: 1 1 auto;
    padding: 2.25rem;
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
    min-width: 0;
}

.ticket-header-row {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    flex-wrap: wrap;
    gap: 1rem;
    border-bottom: 1px dashed rgba(var(--muted), 0.18);
    padding-bottom: 1.25rem;
    width: 100%;
}

.ticket-title-block {
    min-width: 0;
}

.ticket-label-badge {
    display: inline-block;
    font-size: 0.74rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: rgb(var(--brand));
    margin-bottom: 0.25rem;
}

#ticket-id-display {
    font-size: clamp(1.4rem, 3.2vw, 2.1rem);
    margin: 0;
    font-weight: 800;
    letter-spacing: -0.02em;
    color: var(--text-primary, #0f172a);
    word-break: break-all;
    line-height: 1.2;
}

.ticket-badges-wrap {
    display: flex;
    align-items: center;
    gap: 0.45rem;
    flex-wrap: wrap;
}

.status-badge {
    font-size: 0.76rem;
    font-weight: 700;
    padding: 0.35rem 0.8rem;
    border-radius: 20px;
    white-space: nowrap;
    display: inline-flex;
    align-items: center;
    line-height: 1.3;
}

.status-badge.confirmed {
    background: rgba(16, 185, 129, 0.12);
    color: #059669;
    border: 1px solid rgba(16, 185, 129, 0.25);
}

.status-badge.pending {
    background: rgba(245, 158, 11, 0.12);
    color: #d97706;
    border: 1px solid rgba(245, 158, 11, 0.25);
}

.status-badge.cancelled {
    background: rgba(239, 68, 68, 0.12);
    color: #ef4444;
    border: 1px solid rgba(239, 68, 68, 0.25);
    text-decoration: line-through;
}

/* Details Grid */
.ticket-details-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1.25rem;
    width: 100%;
}

.detail-item {
    min-width: 0;
}

.detail-item.full-width {
    grid-column: span 2;
}

.detail-label {
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: rgb(var(--muted));
    display: block;
    margin-bottom: 0.3rem;
}

.detail-value {
    font-size: 1.02rem;
    font-weight: 700;
    color: var(--text-primary, #1e293b);
    line-height: 1.35;
    word-break: break-word;
    overflow-wrap: anywhere;
}

.detail-value a {
    color: rgb(var(--brand));
    text-decoration: none;
    font-weight: 700;
}

.detail-value a:hover {
    text-decoration: underline;
}

.detail-subtext {
    display: flex;
    flex-direction: column;
    gap: 0.15rem;
    margin-top: 0.35rem;
    font-size: 0.85rem;
    color: rgb(var(--muted));
    word-break: break-word;
    overflow-wrap: anywhere;
}

.detail-subtext a {
    color: var(--text-primary, #1e293b);
    opacity: 0.85;
    text-decoration: none;
}

.detail-subtext a:hover {
    color: rgb(var(--brand));
    opacity: 1;
    text-decoration: underline;
}

.ticket-status-note {
    display: flex;
    align-items: center;
    gap: 0.65rem;
    padding: 0.85rem 1.15rem;
    border-radius: 12px;
    background: rgba(var(--brand), 0.05);
    border: 1px solid rgba(var(--brand), 0.15);
    font-size: 0.85rem;
    color: rgb(var(--brand));
    font-weight: 600;
    word-break: break-word;
}

.info-icon {
    font-size: 1.1rem;
    flex-shrink: 0;
}

.host-issued-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    background: rgba(99, 102, 241, 0.1);
    color: #4f46e5;
    font-size: 0.78rem;
    font-weight: 700;
    padding: 0.35rem 0.85rem;
    border-radius: 8px;
    border: 1px solid rgba(99, 102, 241, 0.25);
    width: fit-content;
}

/* ─── Tear-off Ticket Stub ─── */
.ticket-stub {
    width: 290px;
    background: rgba(var(--muted), 0.03);
    display: flex;
    position: relative;
    border-left: 1px dashed rgba(var(--muted), 0.18);
    flex-shrink: 0;
}

.stub-divider {
    position: absolute;
    left: -1px;
    top: 0;
    bottom: 0;
    width: 2px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    pointer-events: none;
}

.stub-cutout {
    width: 24px;
    height: 24px;
    background: rgb(var(--bg));
    border-radius: 50%;
    position: absolute;
    left: -12px;
    z-index: 10;
    border: 1px solid rgba(var(--muted), 0.15);
}

.stub-cutout.top {
    top: -12px;
}

.stub-cutout.bottom {
    bottom: -12px;
}

.stub-content {
    flex: 1;
    padding: 2rem 1.5rem;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 1.35rem;
    width: 100%;
}

.ticket-qr {
    background: #ffffff;
    padding: 0.65rem;
    border-radius: 14px;
    border: 1px solid rgba(var(--muted), 0.18);
    text-align: center;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.04);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    width: auto;
}

.ticket-qr img {
    width: 150px;
    height: 150px;
    display: block;
    image-rendering: pixelated;
    border-radius: 6px;
}

.qr-caption {
    display: block;
    margin-top: 0.4rem;
    font-size: 0.72rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: rgb(var(--muted));
}

/* Card Actions Container & Buttons */
.card-actions {
    display: flex;
    flex-direction: column;
    gap: 0.55rem;
    width: 100%;
}

.card-actions .admin-action-btn {
    width: 100% !important;
    min-height: 42px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.45rem;
    padding: 0.65rem 1rem;
    font-size: 0.85rem;
    font-weight: 700;
    border-radius: 12px;
    cursor: pointer;
    text-align: center;
    transition: all 0.2s ease;
    border: 1px solid transparent;
}

.btn-action-uncheck {
    background: rgba(245, 158, 11, 0.12) !important;
    color: #d97706 !important;
    border-color: rgba(245, 158, 11, 0.3) !important;
}

.btn-action-uncheck:hover {
    background: rgba(245, 158, 11, 0.2) !important;
    transform: translateY(-1px);
}

.btn-action-checkin {
    background: rgb(var(--brand)) !important;
    color: #ffffff !important;
    border-color: transparent !important;
}

.btn-action-checkin:hover {
    filter: brightness(1.1);
    transform: translateY(-1px);
}

.btn-action-resend {
    background: rgba(99, 102, 241, 0.1) !important;
    color: #4f46e5 !important;
    border-color: rgba(99, 102, 241, 0.25) !important;
}

.btn-action-resend:hover {
    background: rgba(99, 102, 241, 0.18) !important;
    transform: translateY(-1px);
}

.btn-action-flag {
    background: rgba(var(--muted), 0.08) !important;
    color: var(--text-primary, #1e293b) !important;
    border-color: rgba(var(--muted), 0.2) !important;
}

.btn-action-flag:hover {
    background: rgba(var(--muted), 0.15) !important;
    transform: translateY(-1px);
}

/* ════════════════════════════════════════════════════════════════
   TABLET & iPAD RESPONSIVENESS (≤ 860px)
   ════════════════════════════════════════════════════════════════ */
@media (max-width: 860px) {
    .ticket-detail-container {
        flex-direction: column;
        border-radius: 20px;
    }

    .ticket-body {
        padding: 1.75rem 1.5rem;
        gap: 1.25rem;
    }

    .ticket-stub {
        width: 100%;
        border-left: none;
        border-top: 2px dashed rgba(var(--muted), 0.18);
    }

    .stub-divider {
        left: 0;
        right: 0;
        top: -1px;
        bottom: auto;
        height: 2px;
        width: 100%;
        flex-direction: row;
        justify-content: space-between;
    }

    .stub-cutout.top {
        top: -12px;
        left: -12px;
    }

    .stub-cutout.bottom {
        top: -12px;
        right: -12px;
        bottom: auto;
        left: auto;
    }

    .stub-content {
        padding: 1.75rem 1.5rem;
        gap: 1.25rem;
    }

    .card-actions {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 0.65rem;
        width: 100%;
    }
}

/* ════════════════════════════════════════════════════════════════
   MOBILE RESPONSIVENESS (≤ 600px)
   ════════════════════════════════════════════════════════════════ */
@media (max-width: 600px) {
    .ticket-body {
        padding: 1.25rem 1rem;
        gap: 1.15rem;
    }

    .ticket-header-row {
        flex-direction: column;
        align-items: stretch;
        gap: 0.75rem;
    }

    .ticket-badges-wrap {
        width: 100%;
    }

    .ticket-details-grid {
        grid-template-columns: 1fr;
        gap: 1rem;
    }

    .detail-item.full-width {
        grid-column: span 1;
    }

    .stub-content {
        padding: 1.35rem 1rem;
    }

    .ticket-qr img {
        width: 140px;
        height: 140px;
    }
}

/* ════════════════════════════════════════════════════════════════
   EXTRA-SMALL MOBILE (≤ 460px)
   ════════════════════════════════════════════════════════════════ */
@media (max-width: 460px) {
    .card-actions {
        grid-template-columns: 1fr;
        gap: 0.5rem;
    }

    .modal {
        width: calc(100% - 1.5rem);
        padding: 1.25rem;
    }
}
</style>
@endsection
