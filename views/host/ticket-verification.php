<?php

/**
 * Host Ticket Verification View
 *
 * This file contains the HTML markup for the host ticket verification page.
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
Host Ticket Verification - Verify Your <?= e($siteName) ?> Tickets
@endsection

@section('content')
<section class="page-hero">
    <div>
        <span class="panel-badge">Host Dashboard</span>
        <h1>Ticket Verification</h1>
        <p class="lede">Verify ticket codes and manage attendee entry for your events.</p>
    </div>
</section>

<div class="ticket-verification-layout">

    <!-- Left Column: Settings, Stats, and Verification Actions -->
    <div class="verification-main-panel">

        <!-- Event Selection & Metrics -->
        <section class="form-card">
            <h2>Select Event</h2>
            <form id="event-select-form" method="GET" action="">
                <div class="form-group">
                    <label for="event-select">Active Event:</label>
                    <select id="event-select" name="event_id" onchange="this.form.submit()" aria-label="Select event">
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
                    <noscript><button type="submit" class="btn-secondary"
                            style="margin-top: 0.5rem; width: 100%;">Select Event</button></noscript>
                </div>
            </form>

            <!-- Metrics Dashboard -->
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
                    <span class="lbl">Remaining (Awaiting Entry)</span>
                    <span id="remaining-tickets-count" class="num"><?= (int)($remaining_tickets_count ?? 0) ?></span>
                </div>
            </div>
        </section>

        <!-- Verification Panel -->
        <section class="form-card" id="verification-section">
            <div class="section-title-row">
                <h2>Scan &amp; Verify</h2>
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
                    <button type="button" id="toggle-manual-input-btn" class="btn-secondary hidden"
                        title="Use Manual Entry">
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
                <div class="scanner-info">
                    <p>Point device camera at a ticket QR code</p>
                    <small>Align code within the camera scanner frame</small>
                </div>
                <div id="scanner-status" class="scanner-status"></div>
            </div>

            <!-- Manual Code Entry View -->
            <div id="manual-input-view" class="form-group">
                <form id="manual-verify-form" method="POST" action="{{ route('host/event/ticket-verification') }}">
                    <?= csrf_field() ?>
                    <input type="hidden" name="event_id" value="<?= $selected_event_id ?>" />
                    <label for="ticket-code-input">Scan or enter ticket code:</label>
                    <div class="input-action-group">
                        <input id="ticket-code-input" name="ticket_code" type="text"
                            placeholder="Enter alphanumeric code..." required autocomplete="off"
                            aria-label="Ticket code input" />
                        <button type="submit" id="verify-ticket-btn" class="btn-primary">Verify</button>
                    </div>
                    <small class="form-hint">Submit to trigger server check-in validation</small>
                </form>
            </div>

            <!-- Verification Result display container (PHP condition placeholder) -->
            <?php
            $verification_result = \Ace\Application::$app->session->getFlash('verification_result');
            ?>
            <?php if ($verification_result): ?>
            <div id="verification-result" class="verification-result">

                <?php if ($verification_result['status'] === 'success'): 
                    $t = $verification_result['ticket'];
                ?>
                <!-- Success Ticket Card Template -->
                <div id="ticket-success-card" class="ticket-display-card ticket-verified">
                    <div class="ticket-card-header">
                        <div>
                            <div class="ticket-event-name" id="ticket-success-event-title"><?= e($t['event_name']) ?></div>
                            <div class="ticket-event-meta" id="ticket-success-event-location"><?= e($t['event_location']) ?></div>
                        </div>
                        <span class="ticket-status-badge badge-verified">✓ Checked In</span>
                    </div>
                    <hr class="ticket-perforation" />
                    <div class="ticket-card-body">
                        <div class="ticket-attendee-row">
                            <div class="ticket-avatar" id="ticket-success-avatar"><?= strtoupper(substr($t['attendee_name'] ?? 'U', 0, 2)) ?></div>
                            <div class="ticket-attendee-info">
                                <h3 id="ticket-success-attendee-name"><?= e($t['attendee_name']) ?></h3>
                                <p id="ticket-success-ticket-type"><?= e($t['ticket_type']) ?> Ticket</p>
                            </div>
                        </div>
                        <div class="ticket-details-grid">
                            <div class="ticket-detail-item">
                                <div class="detail-label">Ticket Code</div>
                                <div class="detail-value" id="ticket-success-code"><?= e($t['id']) ?></div>
                            </div>
                            <div class="ticket-detail-item">
                                <div class="detail-label">Event Date</div>
                                <div class="detail-value" id="ticket-success-date"><?= date('M d, Y', strtotime($t['purchase_date'])) ?></div>
                            </div>
                            <div class="ticket-detail-item">
                                <div class="detail-label">Verified At</div>
                                <div class="detail-value" id="ticket-success-time"><?= date('h:i A', strtotime($t['checked_in_at'])) ?></div>
                            </div>
                            <div class="ticket-detail-item">
                                <div class="detail-label">Status</div>
                                <div class="detail-value">Success</div>
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
                        <div class="ticket-barcode-code" id="ticket-success-barcode-code"><?= e($t['id']) ?></div>
                    </div>
                </div>
                <?php else: ?>
                <!-- Error Ticket Card Template -->
                <div id="ticket-error-card" class="ticket-display-card ticket-error">
                    <div class="ticket-card-header">
                        <div>
                            <div class="ticket-event-name" id="ticket-error-event-title">Verification Warning</div>
                            <div class="ticket-event-meta" id="ticket-error-event-location">Event Entry Desk</div>
                        </div>
                        <span class="ticket-status-badge badge-invalid" id="ticket-error-badge"><?= e(ucfirst($verification_result['type'] ?? 'error')) ?></span>
                    </div>
                    <div class="ticket-error-card">
                        <span class="ticket-error-icon" id="ticket-error-icon">⚠️</span>
                        <h3 id="ticket-error-title"><?= e($verification_result['message'] ?? 'Check-in failed') ?></h3>
                        <p id="ticket-error-message">Ticket Code: <strong><?= e($verification_result['code']) ?></strong></p>
                    </div>
                </div>
                <?php endif; ?>

                <div style="display:flex; gap:0.5rem; margin-top:0.75rem;">
                    <button type="button" id="clear-result-btn" class="btn-secondary" style="flex:1;">Clear Result</button>
                    <?php if (($verification_result['status'] ?? '') === 'success' && !empty($t['id'])): ?>
                        <button type="button" class="btn-secondary" style="background:rgba(245, 158, 11, 0.12); color:#d97706; border:1px solid rgba(245, 158, 11, 0.3); font-weight:700;" onclick="removeVerifiedTicket('<?= e($t['id']) ?>')">
                            ↩️ Undo Check-In
                        </button>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <div id="no-event-message" class="empty-state <?= $selected_event_id ? 'hidden' : '' ?>">
                <p>Please select an active event to start check-in.</p>
            </div>
        </section>
    </div>

    <!-- Right Column: Checked-In Log list -->
    <aside class="verification-side-log" id="tickets-log-section">
        <div class="log-header-row">
            <h2>Checked In Log</h2>
            <!-- PHP: link dynamically -->
            <a href="#" id="export-log-btn" class="btn-secondary log-export-btn" onclick="alert('Export functionality coming soon!'); return false;">
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
                <input id="search-verified-tickets" type="text" placeholder="Search attendee or code..."
                    aria-label="Search checked-in log" />
            </div>
        </div>

        <!-- Checked in list scrolling block -->
        <div id="verified-tickets-list" class="verified-tickets-list scrollable-list log-scroll-area">
            <?php foreach ($verified_tickets as $vTicket): ?>
            <article class="verified-ticket-item" data-code="<?= e($vTicket['id']) ?>" id="verified-item-<?= e($vTicket['id']) ?>">
                <div class="ticket-info">
                    <h4><?= e($vTicket['attendee_name']) ?></h4>
                    <p class="ticket-meta">Code: <strong><?= e($vTicket['id']) ?></strong> | <?= e($vTicket['ticket_type']) ?></p>
                    <p class="ticket-time">Checked in at <?= date('h:i A', strtotime($vTicket['checked_in_at'])) ?></p>
                </div>
                <button class="btn-icon remove-checkin-btn" aria-label="Undo check-in status" title="Undo check-in (revert to awaiting entry)"
                    onclick="removeVerifiedTicket('<?= e($vTicket['id']) ?>')">✕</button>
            </article>
            <?php endforeach; ?>
        </div>

        <div id="empty-log" class="empty-state <?= !empty($verified_tickets) ? 'hidden' : '' ?>">
            <p>No attendees checked in yet today.</p>
        </div>
    </aside>

</div>
@endsection