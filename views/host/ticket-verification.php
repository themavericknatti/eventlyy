<?php

/**
 * Host & Admin Ticket Verification View
 *
 * Provides real-time camera QR scanning, manual code entry, and live checked-in attendance logging.
 * Fully responsive across mobile, tablet, and desktop viewports.
 *
 * @package Evently
 * @subpackage Views/Host
 * @author Celio Natti
 * @version 2.0.0
 */

$siteName = site_setting('site_title', 'Eventlyy');
$checkedInCount = count($verified_tickets ?? []);
?>

@extends('layouts/host')

@section('title')
<?= !empty($isAdmin) ? 'Admin' : 'Host' ?> Ticket Verification - <?= e($siteName) ?>
@endsection

@section('content')
<style>
/* ════════════════════════════════════════════════════════════════
   TICKET VERIFICATION — FULLY RESPONSIVE SUITE (MOBILE / TABLET / DESKTOP)
   ════════════════════════════════════════════════════════════════ */

.tv-page-hero {
    margin-bottom: 1.25rem;
    padding: clamp(0.75rem, 2vw, 1.5rem) 0;
}

.tv-hero-content {
    display: flex;
    flex-direction: column;
    gap: 0.35rem;
}

.tv-hero-badge-wrap {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.tv-live-indicator {
    font-size: 0.72rem;
    font-weight: 700;
    color: #10b981;
    background: rgba(16, 185, 129, 0.12);
    border: 1px solid rgba(16, 185, 129, 0.25);
    padding: 0.15rem 0.55rem;
    border-radius: 99px;
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
}

/* Mobile & Tablet Segmented View Switcher (Hidden on Desktop) */
.tv-mobile-nav {
    display: none;
    background: rgba(var(--text), 0.04);
    border: 1px solid rgba(var(--muted), 0.16);
    padding: 0.35rem;
    border-radius: 14px;
    margin-bottom: 1.25rem;
    gap: 0.35rem;
}

.tv-tab-btn {
    flex: 1;
    padding: 0.65rem 0.75rem;
    font-size: 0.86rem;
    font-weight: 700;
    font-family: inherit;
    border-radius: 10px;
    border: none;
    background: transparent;
    color: rgb(var(--muted));
    cursor: pointer;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.4rem;
    white-space: nowrap;
}

.tv-tab-btn.active {
    background: rgb(var(--card));
    color: rgb(var(--brand));
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.tv-tab-badge {
    font-size: 0.7rem;
    padding: 0.1rem 0.45rem;
    border-radius: 99px;
    background: rgba(var(--brand), 0.14);
    color: rgb(var(--brand));
    font-weight: 800;
}

/* Main Layout Grid */
.ticket-verification-layout {
    display: grid;
    grid-template-columns: 1.18fr 0.82fr;
    gap: 1.5rem;
    align-items: start;
    max-width: 1240px;
    margin: 0 auto 2rem;
    width: 100%;
    box-sizing: border-box;
}

.verification-main-panel {
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
    min-width: 0;
}

/* High-Contrast Event Selector Dropdown */
.tv-event-select {
    width: 100% !important;
    min-height: 48px;
    padding: 0.65rem 2.2rem 0.65rem 1rem !important;
    font-size: 0.92rem !important;
    font-weight: 700 !important;
    font-family: inherit !important;
    border-radius: 12px !important;
    border: 1.5px solid rgba(var(--brand), 0.35) !important;
    background-color: #ffffff !important;
    color: #0f172a !important;
    cursor: pointer;
    outline: none;
    appearance: none;
    -webkit-appearance: none;
    -moz-appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%23108a55' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E") !important;
    background-repeat: no-repeat !important;
    background-position: right 0.75rem center !important;
    background-size: 15px !important;
    box-shadow: 0 1px 4px rgba(0, 0, 0, 0.05);
    transition: all 0.2s ease;
}

.tv-event-select:hover {
    border-color: rgb(var(--brand)) !important;
    box-shadow: 0 0 0 3px rgba(var(--brand), 0.12);
}

.tv-event-select:focus {
    border-color: rgb(var(--brand)) !important;
    box-shadow: 0 0 0 3px rgba(var(--brand), 0.2);
}

.tv-event-select option {
    background-color: #ffffff !important;
    color: #0f172a !important;
    font-weight: 600;
    padding: 10px 14px;
}

:root[data-theme='dark'] .tv-event-select {
    background-color: #1e293b !important;
    color: #f8fafc !important;
    border-color: rgba(46, 204, 133, 0.45) !important;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%232ecc85' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E") !important;
}

:root[data-theme='dark'] .tv-event-select option {
    background-color: #1e293b !important;
    color: #f8fafc !important;
}

/* 3-Column Compact Metrics Dashboard Across All Screen Sizes */
.verification-stats-row {
    display: grid !important;
    grid-template-columns: repeat(3, 1fr) !important;
    gap: 0.65rem !important;
    margin-top: 0.85rem !important;
}

.verification-stat-card {
    background: rgba(var(--brand), 0.05) !important;
    border: 1px solid rgba(var(--brand), 0.15) !important;
    border-radius: 14px !important;
    padding: 0.75rem 0.5rem !important;
    text-align: center;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
}

.verification-stat-card .num {
    font-size: clamp(1.2rem, 3.2vw, 1.75rem) !important;
    font-weight: 900 !important;
    color: rgb(var(--brand)) !important;
    margin-top: 0.15rem !important;
    line-height: 1.2;
}

.verification-stat-card .lbl {
    font-size: clamp(0.65rem, 1.8vw, 0.72rem) !important;
    font-weight: 800 !important;
    text-transform: uppercase;
    letter-spacing: 0.04em !important;
    color: rgb(var(--muted)) !important;
    display: block;
    line-height: 1.25;
}

/* Scanner & Manual Input Actions */
.input-action-group {
    display: flex;
    gap: 0.5rem;
    align-items: stretch;
    width: 100%;
}

.input-action-group input#ticket-code-input {
    flex: 1;
    min-width: 0;
    min-height: 48px;
    font-size: 1rem !important; /* 16px font-size prevents mobile Safari zoom */
    font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
    font-weight: 700;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    padding: 0.65rem 0.95rem;
    border-radius: 12px;
}

.input-action-group button#verify-ticket-btn {
    min-height: 48px;
    padding: 0 1.35rem;
    font-size: 0.95rem;
    font-weight: 800;
    border-radius: 12px;
    flex-shrink: 0;
    cursor: pointer;
}

/* Camera Scanner Container */
.scanner-container {
    border-radius: 16px;
    overflow: hidden;
    margin-bottom: 0.85rem;
    background: #000000;
    border: 1px solid rgba(var(--muted), 0.2);
}

.scanner-preview-wrapper {
    width: 100%;
    max-height: 320px;
    margin: 0 auto;
    position: relative;
    overflow: hidden;
    background: #000;
    border-radius: 14px;
}

.scanner-preview-wrapper video {
    width: 100% !important;
    height: 100% !important;
    object-fit: cover !important;
    -webkit-transform: translateZ(0);
    transform: translateZ(0);
}

/* Result Ticket Cards (Fluid & High-Contrast) */
.ticket-display-card {
    border-radius: 16px;
    border: 1.5px solid rgba(var(--muted), 0.2);
    background: rgb(var(--card));
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
    overflow: hidden;
    margin-top: 1rem;
}

.ticket-display-card.ticket-verified {
    border-color: rgba(16, 185, 129, 0.4);
}

.ticket-display-card.ticket-error {
    border-color: rgba(225, 29, 72, 0.4);
}

.ticket-details-grid {
    display: grid !important;
    grid-template-columns: repeat(auto-fit, minmax(115px, 1fr)) !important;
    gap: 0.55rem !important;
    margin-top: 0.75rem;
}

.ticket-detail-item {
    padding: 0.65rem 0.75rem !important;
    border-radius: 10px !important;
    background: rgba(var(--text), 0.03) !important;
    border: 1px solid rgba(var(--muted), 0.12) !important;
}

/* Checked-In Log Side Panel */
.verification-side-log {
    background: rgb(var(--card));
    border-radius: 20px;
    border: 1px solid rgba(var(--muted), 0.15);
    padding: 1.25rem;
    display: flex;
    flex-direction: column;
    gap: 1rem;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
    min-width: 0;
}

.log-scroll-area {
    max-height: clamp(340px, 55vh, 520px);
    overflow-y: auto;
    -webkit-overflow-scrolling: touch;
    padding-right: 0.25rem;
    display: flex;
    flex-direction: column;
    gap: 0.65rem;
}

.verified-ticket-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem 0.95rem;
    background: rgba(var(--text), 0.02);
    border: 1px solid rgba(var(--muted), 0.12);
    border-left: 4px solid rgb(var(--brand));
    border-radius: 12px;
}

.verified-ticket-item:hover {
    background: rgba(var(--brand), 0.04);
}

/* ════════════════════════════════════════════════════════════════
   RESPONSIVE BREAKPOINTS (TABLET & MOBILE)
   ════════════════════════════════════════════════════════════════ */

/* Tablet & Mobile Breakpoint (<= 960px) */
@media (max-width: 960px) {
    .tv-mobile-nav {
        display: flex;
    }

    .ticket-verification-layout {
        grid-template-columns: 1fr !important;
        gap: 1rem !important;
    }

    /* By default on tablet/mobile, tab-switching governs visibility */
    .verification-main-panel.tv-hidden-mobile,
    .verification-side-log.tv-hidden-mobile {
        display: none !important;
    }

    .verification-main-panel.tv-active-mobile {
        display: flex !important;
    }

    .verification-side-log.tv-active-mobile {
        display: flex !important;
    }

    .log-scroll-area {
        max-height: 60vh;
    }
}

/* Small Smartphone Breakpoint (<= 520px) */
@media (max-width: 520px) {
    .verification-stats-row {
        gap: 0.4rem !important;
    }

    .verification-stat-card {
        padding: 0.6rem 0.35rem !important;
    }

    .verification-stat-card .lbl {
        font-size: 0.62rem !important;
        letter-spacing: 0.02em !important;
    }

    .section-title-row {
        flex-direction: column;
        align-items: stretch !important;
        gap: 0.65rem;
    }

    .scanner-toggle {
        display: flex;
        width: 100%;
        gap: 0.4rem;
    }

    .scanner-toggle button {
        flex: 1;
        justify-content: center;
        padding: 0.55rem 0.75rem !important;
        font-size: 0.82rem !important;
    }

    .ticket-card-header {
        flex-direction: column;
        align-items: flex-start !important;
        gap: 0.5rem;
    }
}

/* --- Vintage Theme Enhancements for Ticket Verification --- */
.verification-card, .tv-history-panel {
    background: var(--vn-parchment-card) !important;
    border: 1.5px solid var(--vn-border) !important;
    box-shadow: var(--vn-shadow-elevated) !important;
}
.tv-page-hero h1 {
    font-family: var(--vn-font-serif) !important;
    color: var(--vn-ink) !important;
}
.tv-stat-card {
    background: var(--vn-parchment-card) !important;
    border: 1.5px solid var(--vn-border) !important;
    box-shadow: var(--vn-shadow-elevated) !important;
}
.tv-stat-val {
    font-family: var(--vn-font-serif) !important;
    color: var(--vn-brass) !important;
}
.verified-attendee-card {
    background: var(--vn-parchment-warm) !important;
    border: 1.5px solid var(--vn-border) !important;
}
.tv-tab-btn.active {
    background: var(--vn-brass) !important;
    color: #fff !important;
}
</style>

<!-- Hero Section -->
<section class="page-hero tv-page-hero">
    <div class="tv-hero-content">
        <div class="tv-hero-badge-wrap">
            <span class="panel-badge"><?= !empty($isAdmin) ? 'Admin Control' : 'Host Dashboard' ?></span>
            <?php if (!empty($selected_event_id)): ?>
                <span class="tv-live-indicator">● Live Verification Active</span>
            <?php endif; ?>
        </div>
        <h1 style="margin: 0.2rem 0; font-size: clamp(1.4rem, 4vw, 2.1rem);">Ticket Verification</h1>
        <p class="lede" style="margin: 0; font-size: 0.92rem;">Scan QR codes or enter ticket IDs for real-time check-in and entry control.</p>
    </div>
</section>

<!-- Mobile & Tablet Segmented View Switcher -->
<div class="tv-mobile-nav" role="tablist" aria-label="Ticket verification views">
    <button type="button" class="tv-tab-btn active" id="tv-tab-verify" role="tab" aria-selected="true" onclick="switchTvMobileTab('verify')">
        📷 Scan &amp; Verify
    </button>
    <button type="button" class="tv-tab-btn" id="tv-tab-log" role="tab" aria-selected="false" onclick="switchTvMobileTab('log')">
        📋 Checked-In Log <span class="tv-tab-badge" id="tv-tab-log-badge"><?= $checkedInCount ?></span>
    </button>
</div>

<div class="ticket-verification-layout">

    <!-- Left Column: Settings, Stats, and Verification Actions -->
    <div class="verification-main-panel tv-active-mobile" id="verification-main-panel">

        <!-- Event Selection & Metrics -->
        <section class="form-card" style="padding: clamp(1rem, 2.5vw, 1.5rem);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem;">
                <h2 style="margin:0; font-size: 1.15rem; font-weight: 800;">Select Event</h2>
                <?php if (!empty($isAdmin)): ?>
                    <span style="font-size: 0.74rem; font-weight: 700; color: #6366f1; background: rgba(99,102,241,0.12); padding: 0.2rem 0.55rem; border-radius: 6px;">
                        Platform Overview
                    </span>
                <?php endif; ?>
            </div>
            <form id="event-select-form" method="GET" action="">
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="event-select" style="font-size: 0.82rem; font-weight: 700; color: rgb(var(--muted));">Active Event:</label>
                    <select id="event-select" name="event_id" class="tv-event-select" onchange="this.form.submit()" aria-label="Select event for verification">
                        <?php if (empty($events)): ?>
                            <option value="">-- No Events Available --</option>
                        <?php else: ?>
                            <option value="">-- Select Event --</option>
                            <?php foreach ($events as $event): ?>
                                <?php 
                                    $isMine = !empty($currentUserId) && ($event['organizer_id'] ?? '') == $currentUserId;
                                    $eventLabel = e($event['name']);
                                    if (!empty($isAdmin)) {
                                        $eventLabel .= $isMine ? ' 👑 (My Event)' : ' — Host: ' . e($event['organizer_name'] ?? 'Organizer');
                                    }
                                ?>
                                <option value="<?= $event['id'] ?>" <?= $event['id'] == $selected_event_id ? 'selected' : '' ?>><?= $eventLabel ?></option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                    <noscript>
                        <button type="submit" class="btn-secondary" style="margin-top: 0.5rem; width: 100%;">Select Event</button>
                    </noscript>
                </div>
            </form>

            <!-- Metrics Dashboard (Always 3-col on all viewports) -->
            <div id="event-stats" class="verification-stats-row">
                <div class="verification-stat-card">
                    <span class="lbl">Total Capacity</span>
                    <span id="total-tickets-count" class="num"><?= (int)($total_tickets_count ?? 0) ?></span>
                </div>
                <div class="verification-stat-card">
                    <span class="lbl">Checked In</span>
                    <span id="verified-tickets-count" class="num"><?= (int)($verified_tickets_count ?? 0) ?></span>
                </div>
                <div class="verification-stat-card">
                    <span class="lbl">Awaiting Entry</span>
                    <span id="remaining-tickets-count" class="num"><?= (int)($remaining_tickets_count ?? 0) ?></span>
                </div>
            </div>
        </section>

        <!-- Verification Panel -->
        <section class="form-card" id="verification-section" style="padding: clamp(1rem, 2.5vw, 1.5rem);">
            <div class="section-title-row">
                <h2 style="font-size: 1.15rem; font-weight: 800;">Scan &amp; Verify</h2>
                <div class="scanner-toggle">
                    <button type="button" id="toggle-scanner-btn" class="btn-secondary" title="Use Camera Scanner">
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor"
                            stroke-width="2.5" style="vertical-align:middle;margin-right:4px;">
                            <path
                                d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"
                                stroke-linecap="round" stroke-linejoin="round" />
                            <path d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        Camera Scanner
                    </button>
                    <button type="button" id="toggle-manual-input-btn" class="btn-secondary hidden" title="Use Manual Entry">
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor"
                            stroke-width="2.5" style="vertical-align:middle;margin-right:4px;">
                            <path
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                                stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        Manual Entry
                    </button>
                </div>
            </div>

            <!-- Camera Scanner View -->
            <div id="scanner-view" class="scanner-container hidden">
                <div class="scanner-preview-wrapper">
                    <div id="qr-scanner" class="qr-scanner"></div>
                </div>
                <div class="scanner-info" style="padding: 0.65rem; text-align: center;">
                    <p style="margin: 0; font-weight: 700; font-size: 0.88rem;">Point camera at attendee QR code</p>
                    <small style="color: rgb(var(--muted));">Center code within the frame to verify automatically</small>
                </div>
                <div id="scanner-status" class="scanner-status"></div>
                <div class="scanner-photo-fallback" style="padding: 0.65rem 0.85rem 0.95rem; text-align: center; border-top: 1px dashed rgba(var(--muted), 0.2); background: rgba(var(--card), 0.5);">
                    <label class="btn-secondary" style="display: inline-flex; align-items: center; justify-content: center; gap: 0.45rem; padding: 0.55rem 1.15rem; font-size: 0.85rem; font-weight: 700; border-radius: 11px; cursor: pointer; width: 100%; box-sizing: border-box; background: rgba(var(--brand), 0.08); border-color: rgba(var(--brand), 0.25); color: rgb(var(--brand));">
                        <svg viewBox="0 0 24 24" width="17" height="17" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                            <circle cx="8.5" cy="8.5" r="1.5"/>
                            <polyline points="21 15 16 10 5 21"/>
                        </svg>
                        Snap Photo / Upload QR Image
                        <input type="file" id="qr-photo-input" accept="image/*" capture="environment" onchange="scanQrFromPhoto(this)" style="display:none;">
                    </label>
                    <span style="font-size: 0.74rem; color: rgb(var(--muted)); display: block; margin-top: 0.35rem;">
                        Instant photo capture fallback for iPhone Safari and restricted mobile browsers.
                    </span>
                </div>
            </div>

            <!-- Manual Code Entry View -->
            <div id="manual-input-view" class="form-group" style="margin-bottom: 0;">
                <form id="manual-verify-form" method="POST" action="{{ route('host/event/ticket-verification') }}">
                    <?= csrf_field() ?>
                    <input type="hidden" name="event_id" value="<?= $selected_event_id ?>" />
                    <label for="ticket-code-input" style="font-size: 0.82rem; font-weight: 700; color: rgb(var(--muted));">
                        Scan or enter alphanumeric ticket code:
                    </label>
                    <div class="input-action-group">
                        <input id="ticket-code-input" name="ticket_code" type="text"
                            placeholder="e.g. EV-XXXXXXXX or TKT-XXXXXX" required autocomplete="off"
                            aria-label="Ticket code input" />
                        <button type="submit" id="verify-ticket-btn" class="btn-primary">Verify Ticket</button>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 0.5rem; flex-wrap: wrap; gap: 0.5rem;">
                        <small class="form-hint" style="margin: 0; font-size: 0.78rem;">
                            Instant check-in updates real-time capacity and entry counters.
                        </small>
                        <label class="btn-secondary" style="display: inline-flex; align-items: center; gap: 0.35rem; padding: 0.35rem 0.75rem; font-size: 0.78rem; font-weight: 700; border-radius: 9px; cursor: pointer; background: rgba(var(--brand), 0.06); border-color: rgba(var(--brand), 0.2); color: rgb(var(--brand));" title="Take a photo of QR pass">
                            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                                <circle cx="8.5" cy="8.5" r="1.5"/>
                                <polyline points="21 15 16 10 5 21"/>
                            </svg>
                            Snap QR Photo
                            <input type="file" accept="image/*" capture="environment" onchange="scanQrFromPhoto(this)" style="display:none;">
                        </label>
                    </div>
                </form>
            </div>

            <!-- Verification Result display container -->
            <?php
            $verification_result = \Ace\Application::$app->session->getFlash('verification_result');
            $hasResult = !empty($verification_result);
            $isSuccess = $hasResult && ($verification_result['status'] === 'success');
            $t = $isSuccess ? ($verification_result['ticket'] ?? []) : [];
            ?>
            <div id="verification-result" class="verification-result <?= $hasResult ? '' : 'hidden' ?>" 
                 data-status="<?= e($verification_result['status'] ?? '') ?>" 
                 data-type="<?= e($verification_result['type'] ?? '') ?>">

                <!-- Success Ticket Card Template -->
                <div id="ticket-success-card" class="ticket-display-card ticket-verified <?= $isSuccess ? '' : 'hidden' ?>">
                    <div class="ticket-card-header" style="padding: 1rem 1.25rem; display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <div class="ticket-event-name" id="ticket-success-event-title" style="font-weight: 800; font-size: 1.05rem;"><?= e($t['event_name'] ?? 'Live Event') ?></div>
                            <div class="ticket-event-meta" id="ticket-success-event-location" style="font-size: 0.8rem; color: rgb(var(--muted));"><?= e($t['event_location'] ?? 'Event Venue') ?></div>
                        </div>
                        <span class="ticket-status-badge badge-verified" style="font-weight: 800; font-size: 0.78rem; padding: 0.25rem 0.65rem;">✓ Checked In</span>
                    </div>
                    <hr class="ticket-perforation" style="margin: 0;" />
                    <div class="ticket-card-body" style="padding: 1.15rem 1.25rem;">
                        <div class="ticket-attendee-row" style="display: flex; align-items: center; gap: 0.85rem; margin-bottom: 0.95rem;">
                            <div class="ticket-avatar" id="ticket-success-avatar"><?= strtoupper(substr($t['attendee_name'] ?? 'U', 0, 2)) ?></div>
                            <div class="ticket-attendee-info">
                                <h3 id="ticket-success-attendee-name" style="margin: 0; font-size: 1.1rem; font-weight: 800;"><?= e($t['attendee_name'] ?? 'Attendee') ?></h3>
                                <p id="ticket-success-ticket-type" style="margin: 2px 0 0; font-size: 0.84rem; color: rgb(var(--muted));"><?= e($t['ticket_type'] ?? 'General Admission') ?> Ticket</p>
                            </div>
                        </div>
                        <div class="ticket-details-grid">
                            <div class="ticket-detail-item">
                                <div class="detail-label">Ticket Code</div>
                                <div class="detail-value" id="ticket-success-code" style="font-family: monospace; font-weight: 700;"><?= e($t['id'] ?? '') ?></div>
                            </div>
                            <div class="ticket-detail-item">
                                <div class="detail-label">Purchase Date</div>
                                <div class="detail-value" id="ticket-success-date"><?= !empty($t['purchase_date']) ? date('M d, Y', strtotime($t['purchase_date'])) : 'Today' ?></div>
                            </div>
                            <div class="ticket-detail-item">
                                <div class="detail-label">Verified At</div>
                                <div class="detail-value" id="ticket-success-time"><?= !empty($t['checked_in_at']) ? date('h:i A', strtotime($t['checked_in_at'])) : date('h:i A') ?></div>
                            </div>
                            <div class="ticket-detail-item">
                                <div class="detail-label">Status</div>
                                <div class="detail-value" style="color: #10b981; font-weight: 800;">Admitted</div>
                            </div>
                        </div>
                    </div>
                    <div class="ticket-barcode-section">
                        <div class="ticket-barcode" id="ticket-success-barcode">
                            <span style="height:25px;width:3px"></span>
                            <span style="height:20px;width:2px"></span>
                            <span style="height:28px;width:3px"></span>
                            <span style="height:22px;width:1px"></span>
                            <span style="height:24px;width:2px"></span>
                            <span style="height:26px;width:3px"></span>
                        </div>
                        <div class="ticket-barcode-code" id="ticket-success-barcode-code"><?= e($t['id'] ?? '') ?></div>
                    </div>
                </div>

                <!-- Warning / Error Ticket Card Template -->
                <div id="ticket-error-card" class="ticket-display-card ticket-error <?= ($hasResult && !$isSuccess) ? '' : 'hidden' ?>">
                    <div class="ticket-card-header" style="padding: 1rem 1.25rem; display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <div class="ticket-event-name" id="ticket-error-event-title" style="font-weight: 800; font-size: 1.05rem;">Verification Warning</div>
                            <div class="ticket-event-meta" id="ticket-error-event-location" style="font-size: 0.8rem; color: rgb(var(--muted));">Event Entry Desk</div>
                        </div>
                        <span class="ticket-status-badge badge-invalid" id="ticket-error-badge" style="font-weight: 800; font-size: 0.78rem;"><?= e(ucfirst($verification_result['type'] ?? 'warning')) ?></span>
                    </div>
                    <div class="ticket-error-card" style="padding: 1.5rem 1.25rem; text-align: center;">
                        <span class="ticket-error-icon" id="ticket-error-icon" style="font-size: 2.5rem; display: block; margin-bottom: 0.5rem;">⚠️</span>
                        <h3 id="ticket-error-title" style="margin: 0 0 0.4rem; font-size: 1.15rem; font-weight: 800;"><?= e($verification_result['message'] ?? 'Check-in failed') ?></h3>
                        <p id="ticket-error-message" style="margin: 0; font-size: 0.88rem; color: rgb(var(--muted));">Ticket Code: <strong id="ticket-error-code-val" style="font-family: monospace; color: rgb(var(--text));"><?= e($verification_result['code'] ?? '') ?></strong></p>
                    </div>
                </div>

                <div style="display:flex; gap:0.5rem; margin-top:0.75rem; flex-wrap: wrap;">
                    <button type="button" id="clear-result-btn" class="btn-secondary" style="flex:1; min-height: 44px; font-weight: 700;">Clear Result</button>
                    <button type="button" id="undo-checkin-btn" class="btn-secondary <?= ($isSuccess && !empty($t['id'])) ? '' : 'hidden' ?>" style="background:rgba(245, 158, 11, 0.12); color:#d97706; border:1px solid rgba(245, 158, 11, 0.3); font-weight:700; min-height: 44px;" onclick="removeVerifiedTicket(document.getElementById('ticket-success-code').textContent.trim())">
                        ↩️ Undo Check-In
                    </button>
                </div>
            </div>

            <div id="no-event-message" class="empty-state <?= $selected_event_id ? 'hidden' : '' ?>">
                <p style="font-weight: 600;">Please select an active event to start check-in.</p>
            </div>
        </section>
    </div>

    <!-- Right Column: Checked-In Log list -->
    <aside class="verification-side-log" id="tickets-log-section">
        <div class="log-header-row" style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h2 style="margin: 0; font-size: 1.15rem; font-weight: 800;">Checked In Log</h2>
                <span style="font-size: 0.78rem; color: rgb(var(--muted));">
                    <span id="log-count-display"><?= $checkedInCount ?></span> attendee<?= $checkedInCount === 1 ? '' : 's' ?> checked in
                </span>
            </div>
            <a href="#" id="export-log-btn" class="btn-secondary log-export-btn" onclick="alert('Checked-in attendee log export will download in CSV format.'); return false;">
                <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5"
                    style="vertical-align:middle;margin-right:4px;">
                    <path d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" stroke-linecap="round"
                        stroke-linejoin="round" />
                </svg>
                Export
            </a>
        </div>

        <div class="tickets-log-controls">
            <div class="search-box">
                <input id="search-verified-tickets" type="text" placeholder="Search attendee name or code..."
                    aria-label="Search checked-in log" style="font-size: 0.9rem;" />
            </div>
        </div>

        <!-- Checked in list scrolling block -->
        <div id="verified-tickets-list" class="verified-tickets-list scrollable-list log-scroll-area">
            <?php foreach ($verified_tickets as $vTicket): ?>
            <article class="verified-ticket-item" data-code="<?= e($vTicket['id']) ?>" id="verified-item-<?= e($vTicket['id']) ?>">
                <div class="ticket-info" style="min-width: 0; flex: 1;">
                    <h4 style="margin: 0 0 2px; font-size: 0.92rem; font-weight: 800; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; color: rgb(var(--text));"><?= e($vTicket['attendee_name']) ?></h4>
                    <p class="ticket-meta" style="margin: 0; font-size: 0.8rem; color: rgb(var(--muted));">
                        Code: <strong style="font-family: monospace; color: rgb(var(--text));"><?= e($vTicket['id']) ?></strong> &bull; <?= e($vTicket['ticket_type']) ?>
                    </p>
                    <p class="ticket-time" style="margin: 2px 0 0; font-size: 0.76rem; color: #10b981; font-weight: 700;">
                        ✓ Checked in at <?= date('h:i A', strtotime($vTicket['checked_in_at'])) ?>
                    </p>
                </div>
                <button class="btn-icon remove-checkin-btn" aria-label="Undo check-in status" title="Undo check-in (revert to awaiting entry)"
                    onclick="removeVerifiedTicket('<?= e($vTicket['id']) ?>')">✕</button>
            </article>
            <?php endforeach; ?>
        </div>

        <div id="empty-log" class="empty-state <?= !empty($verified_tickets) ? 'hidden' : '' ?>" style="padding: 2.5rem 1rem;">
            <span style="font-size: 2rem; display: block; margin-bottom: 0.5rem;">📋</span>
            <p style="font-weight: 600; color: rgb(var(--muted));">No attendees checked in yet today.</p>
        </div>
    </aside>

</div>

<script>
/**
 * Tablet & Mobile Segmented Tab Switcher Logic
 */
function switchTvMobileTab(tab) {
    const mainPanel = document.getElementById('verification-main-panel');
    const logSection = document.getElementById('tickets-log-section');
    const verifyTabBtn = document.getElementById('tv-tab-verify');
    const logTabBtn = document.getElementById('tv-tab-log');

    if (!mainPanel || !logSection) return;

    if (tab === 'verify') {
        verifyTabBtn.classList.add('active');
        verifyTabBtn.setAttribute('aria-selected', 'true');
        logTabBtn.classList.remove('active');
        logTabBtn.setAttribute('aria-selected', 'false');

        mainPanel.classList.remove('tv-hidden-mobile');
        mainPanel.classList.add('tv-active-mobile');
        logSection.classList.add('tv-hidden-mobile');
        logSection.classList.remove('tv-active-mobile');
    } else {
        logTabBtn.classList.add('active');
        logTabBtn.setAttribute('aria-selected', 'true');
        verifyTabBtn.classList.remove('active');
        verifyTabBtn.setAttribute('aria-selected', 'false');

        logSection.classList.remove('tv-hidden-mobile');
        logSection.classList.add('tv-active-mobile');
        mainPanel.classList.add('tv-hidden-mobile');
        mainPanel.classList.remove('tv-active-mobile');
    }
}

// Initial check for mobile viewport tab states
function handleTvResize() {
    const isMobile = window.innerWidth <= 960;
    const mainPanel = document.getElementById('verification-main-panel');
    const logSection = document.getElementById('tickets-log-section');

    if (!mainPanel || !logSection) return;

    if (!isMobile) {
        // Desktop: Always show both panels side-by-side
        mainPanel.classList.remove('tv-hidden-mobile', 'tv-active-mobile');
        logSection.classList.remove('tv-hidden-mobile', 'tv-active-mobile');
    } else {
        // Mobile/Tablet: ensure active tab state is respected
        const verifyTabBtn = document.getElementById('tv-tab-verify');
        if (verifyTabBtn && verifyTabBtn.classList.contains('active')) {
            switchTvMobileTab('verify');
        } else {
            switchTvMobileTab('log');
        }
    }
}

window.addEventListener('resize', handleTvResize);
document.addEventListener('DOMContentLoaded', handleTvResize);
</script>
@endsection
