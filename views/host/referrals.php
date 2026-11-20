<?php

/**
 * Host Event Referral & Affiliate Tracking Hub (Advanced)
 *
 * @package Evently
 * @subpackage Views/Host
 * @version 2.0.0
 */

$siteName = site_setting('site_title', 'Eventlyy');
?>

@extends('layouts/host')

@section('title')
Referral & Affiliate Tracking - <?= e($event->name) ?> - <?= e($siteName) ?>
@endsection

@section('content')
<div class="referrals-page-wrapper">
    <!-- Top Breadcrumb & Actions Bar -->
    <div class="ref-top-bar">
        <a href="{{ route('host/event/management/' . $event->id) }}" class="back-link">
            <span>←</span> Back to Event Management
        </a>
        <div class="ref-top-actions">
            <a href="{{ route('host/event/referrals/' . $event->id . '/export') }}" class="admin-action-btn export-btn" title="Export CSV Report">
                <span>📥</span> Export CSV
            </a>
            <a href="{{ route('host/event/edit/' . $event->id) }}" class="admin-action-btn edit-event-btn">
                <span>✏️</span> Edit Event
            </a>
            <button type="button" id="open-referral-modal-btn" class="checkout-btn add-ref-btn">
                <span>+</span> Add Promoter Link
            </button>
        </div>
    </div>

    <?php if (!empty($isAdmin) && !empty($organizerName)): ?>
        <div class="admin-banner-pill">
            <span style="font-size:1.15rem;">🛡️</span>
            <div style="font-size:0.86rem; line-height:1.45;">
                <span style="font-weight:700; color:#6366f1;">Admin Mode</span>
                <span style="color:var(--text-muted); margin:0 0.35rem;">·</span>
                <span style="font-weight:600;">Event by <?= e($organizerName) ?></span>
                <span style="opacity:0.5; margin:0 0.25rem;">·</span>
                <span style="opacity:0.7;"><?= e($organizerEmail) ?></span>
            </div>
        </div>
    <?php endif; ?>

    <!-- Event Hero Banner -->
    <section class="page-hero ref-hero-banner">
        <div>
            <div class="ref-badge-wrapper">
                <span class="panel-badge ref-panel-badge">🔗 Referral &amp; Affiliate Hub</span>
                <?php if (!empty($event->flyer_page_enabled)): ?>
                    <span class="ref-dest-badge flyer-dest">
                        🎨 Directing to Flyer Page (<code>/f/...</code>)
                    </span>
                <?php else: ?>
                    <span class="ref-dest-badge standard-dest">
                        📖 Directing to Standard Event Page
                    </span>
                <?php endif; ?>
                <span class="ref-badge-sub">Track promoter traffic, sales attribution &amp; commissions</span>
            </div>
            <h1 class="ref-event-title"><?= e($event->name) ?></h1>
            <p class="lede ref-event-meta">
                <span>📅 <?= date('M d, Y • g:i A', strtotime($event->date)) ?></span>
                <span class="ref-meta-dot">•</span>
                <span>📍 <?= e($event->location) ?></span>
            </p>
        </div>
    </section>

    <!-- Analytics Stats Grid -->
    <section class="host-summary-grid ref-stats-grid">
        <article class="summary-card">
            <span class="card-label">Total Promoters</span>
            <h3 class="card-value"><?= number_format($totalPromoters) ?></h3>
            <p class="card-meta">Active affiliate tracking links</p>
        </article>

        <article class="summary-card">
            <span class="card-label">Traffic (Clicks / Unique)</span>
            <h3 class="card-value">
                <?= number_format($totalVisits) ?>
                <span class="unique-badge"><?= number_format($totalUniqueVisits) ?> unique</span>
            </h3>
            <p class="card-meta">Deduplicated visitor clicks</p>
        </article>

        <article class="summary-card highlight-green">
            <span class="card-label">Tickets Sold via Links</span>
            <h3 class="card-value"><?= number_format($totalTicketsSold) ?></h3>
            <p class="card-meta">
                Overall Conv. Rate: <strong><?= number_format($conversionRate, 1) ?>%</strong>
            </p>
        </article>

        <article class="summary-card highlight-brand">
            <span class="card-label">Gross Revenue Attributed</span>
            <h3 class="card-value"><?= format_price($totalRevenue) ?></h3>
            <p class="card-meta">Total gross ticket sales</p>
        </article>

        <article class="summary-card highlight-amber">
            <span class="card-label">Commission Earned &amp; Due</span>
            <h3 class="card-value"><?= format_price($totalCommissionEarned) ?></h3>
            <p class="card-meta">
                Balance Due: <strong class="<?= $totalBalanceDue > 0 ? 'text-amber' : 'text-green' ?>"><?= format_price($totalBalanceDue) ?></strong>
                (Paid: <?= format_price($totalCommissionPaid) ?>)
            </p>
        </article>
    </section>

    <?php if ($topPromoter): ?>
        <div class="top-promoter-banner">
            <div class="top-promoter-info">
                <span class="top-promoter-trophy">🏆</span>
                <div>
                    <span class="top-promoter-eyebrow">Top Performing Promoter</span>
                    <h4 class="top-promoter-name">
                        <?= e($topPromoter['promoter_name']) ?>
                        <span class="top-promoter-code">(@<?= e($topPromoter['referral_code']) ?>)</span>
                    </h4>
                </div>
            </div>
            <div class="top-promoter-metrics">
                <div class="promoter-metric-item">
                    <strong><?= number_format($topPromoter['tickets_sold']) ?></strong>
                    <span>tickets sold</span>
                </div>
                <div class="promoter-metric-item">
                    <strong class="metric-revenue"><?= format_price($topPromoter['revenue_generated']) ?></strong>
                    <span>revenue</span>
                </div>
                <div class="promoter-metric-item">
                    <strong class="metric-comm"><?= format_price($topPromoter['commission_earned']) ?></strong>
                    <span>commission</span>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Promoters & Referral Links Section -->
    <section class="form-card ref-content-card">
        <div class="ref-card-header">
            <div>
                <h2 class="ref-section-title">Promoter Tracking Links</h2>
                <p class="ref-section-desc">Manage your affiliates, track sales, view orders, and settle commission payouts</p>
            </div>
            <?php if (!empty($referrals)): ?>
                <!-- Search & Filters -->
                <div class="ref-filters-toolbar">
                    <div class="ref-search-box">
                        <span class="search-icon">🔍</span>
                        <input type="text" id="promoter-search" placeholder="Search promoter name, code, phone..." class="ref-search-input" />
                    </div>
                    <div class="ref-filter-selects">
                        <select id="status-filter" class="ref-filter-dropdown">
                            <option value="all">All Status</option>
                            <option value="active">Active Only</option>
                            <option value="inactive">Inactive Only</option>
                        </select>
                        <select id="payout-filter" class="ref-filter-dropdown">
                            <option value="all">All Payouts</option>
                            <option value="unpaid">Unpaid Due</option>
                            <option value="partial">Partially Paid</option>
                            <option value="paid">Fully Settled</option>
                        </select>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <?php if (empty($referrals)): ?>
            <div class="empty-state ref-empty-state">
                <span class="empty-icon">🔗</span>
                <h3>No Referral Links Created Yet</h3>
                <p>
                    Generate unique referral links for your event promoters, influencers, and ticket ambassadors.
                    Track clicks, verify exact ticket sales, and manage commissions effortlessly.
                </p>
                <button type="button" class="checkout-btn open-referral-modal-trigger">
                    + Create First Referral Link
                </button>
            </div>
        <?php else: ?>
            <!-- Desktop Table View (>= 920px) -->
            <div class="ref-desktop-table-wrapper scrollable-list">
                <table class="tickets-table ref-table" role="grid" id="promoters-table">
                    <thead>
                        <tr>
                            <th>Promoter</th>
                            <th>Tracking Link &amp; Share</th>
                            <th style="text-align: center;">Traffic</th>
                            <th style="text-align: center;">Sales (Conv.)</th>
                            <th style="text-align: right;">Revenue</th>
                            <th style="text-align: right;">Commission</th>
                            <th style="text-align: center;">Payout</th>
                            <th style="text-align: center;">Status</th>
                            <th style="text-align: right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($referrals as $ref): ?>
                            <?php
                                $refUrl = $eventBaseUrl . '?ref=' . urlencode($ref['referral_code']);
                                $isActive = $ref['status'] === 'active';
                                $payoutStatus = $ref['payout_status'] ?? 'unpaid';
                                $waText = "Hey " . $ref['promoter_name'] . "! Here is your official promoter link for " . $event->name . ": " . $refUrl . " 🎟️ Earn on every ticket sold!";
                                $waUrl = "https://wa.me/" . (!empty($ref['promoter_phone']) ? preg_replace('/[^0-9]/', '', $ref['promoter_phone']) : '') . "?text=" . urlencode($waText);
                            ?>
                            <tr class="promoter-row" 
                                data-name="<?= strtolower(e($ref['promoter_name'])) ?>" 
                                data-code="<?= strtolower(e($ref['referral_code'])) ?>" 
                                data-phone="<?= strtolower(e($ref['promoter_phone'] ?? '')) ?>"
                                data-status="<?= e($ref['status']) ?>"
                                data-payout="<?= e($payoutStatus) ?>">
                                <td>
                                    <div class="promoter-cell">
                                        <div class="promoter-avatar">
                                            <?= strtoupper(substr($ref['promoter_name'], 0, 1)) ?>
                                        </div>
                                        <div>
                                            <div class="promoter-cell-name"><?= e($ref['promoter_name']) ?></div>
                                            <div class="promoter-cell-meta">
                                                <span class="promoter-cell-code">@<?= e($ref['referral_code']) ?></span>
                                                <?php if (!empty($ref['promoter_phone'])): ?>
                                                    <span class="contact-pill phone-pill" title="Phone">📞 <?= e($ref['promoter_phone']) ?></span>
                                                <?php endif; ?>
                                                <?php if (!empty($ref['promoter_email'])): ?>
                                                    <span class="contact-pill email-pill" title="Email">✉️ <?= e($ref['promoter_email']) ?></span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="ref-link-box">
                                        <input type="text" readonly value="<?= e($refUrl) ?>" class="ref-link-input" />
                                        <button type="button" class="copy-ref-btn" data-url="<?= e($refUrl) ?>" title="Copy Tracking Link">
                                            📋 Copy
                                        </button>
                                        <a href="<?= e($waUrl) ?>" target="_blank" rel="noopener" class="wa-share-btn" title="Share via WhatsApp">
                                            💬
                                        </a>
                                        <button type="button" class="qr-view-btn" data-url="<?= e($refUrl) ?>" data-name="<?= e($ref['promoter_name']) ?>" title="Show QR Code">
                                            📱
                                        </button>
                                    </div>
                                </td>
                                <td style="text-align: center;">
                                    <div class="traffic-cell">
                                        <span class="traffic-total"><?= number_format($ref['visits_count']) ?></span>
                                        <span class="traffic-sub"><?= number_format($ref['unique_visits']) ?> unique</span>
                                    </div>
                                </td>
                                <td style="text-align: center;">
                                    <div class="sales-cell">
                                        <span class="tickets-sold-pill <?= $ref['tickets_sold'] > 0 ? 'has-sales' : '' ?>">
                                            <?= number_format($ref['tickets_sold']) ?>
                                        </span>
                                        <span class="conv-sub"><?= number_format($ref['conversion_rate'], 1) ?>% conv.</span>
                                    </div>
                                </td>
                                <td style="text-align: right; font-weight: 700; color: <?= $ref['revenue_generated'] > 0 ? 'rgb(var(--brand))' : 'inherit' ?>;">
                                    <?= format_price($ref['revenue_generated']) ?>
                                </td>
                                <td style="text-align: right;">
                                    <div class="comm-cell">
                                        <span class="comm-earned"><?= format_price($ref['commission_earned']) ?></span>
                                        <span class="comm-rate-pill">
                                            <?= ($ref['commission_type'] ?? 'percentage') === 'fixed' ? format_price($ref['commission_rate']) . '/tkt' : number_format($ref['commission_rate'], 1) . '%' ?>
                                        </span>
                                    </div>
                                </td>
                                <td style="text-align: center;">
                                    <?php if ($ref['commission_earned'] <= 0): ?>
                                        <span class="payout-badge none">—</span>
                                    <?php elseif ($ref['balance_due'] <= 0): ?>
                                        <span class="payout-badge paid" title="Paid <?= format_price($ref['commission_paid']) ?>">
                                            ✓ Settled
                                        </span>
                                    <?php elseif ($ref['commission_paid'] > 0): ?>
                                        <span class="payout-badge partial" title="Due <?= format_price($ref['balance_due']) ?>">
                                            Partial
                                        </span>
                                    <?php else: ?>
                                        <span class="payout-badge unpaid" title="Due <?= format_price($ref['balance_due']) ?>">
                                            Due <?= format_price($ref['balance_due']) ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align: center;">
                                    <?php if ($isActive): ?>
                                        <span class="ref-status-badge active">
                                            <span class="status-dot green"></span> Active
                                        </span>
                                    <?php else: ?>
                                        <span class="ref-status-badge inactive">
                                            <span class="status-dot gray"></span> Inactive
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align: right;">
                                    <div class="ref-actions-group">
                                        <!-- View Orders -->
                                        <button type="button" class="admin-action-btn view-orders-btn" 
                                                data-ref-id="<?= $ref['id'] ?>" 
                                                data-name="<?= e($ref['promoter_name']) ?>" 
                                                title="View Attributed Orders">
                                            👁️ Orders
                                        </button>

                                        <!-- Record Payout -->
                                        <?php if ($ref['balance_due'] > 0 || $ref['commission_earned'] > 0): ?>
                                            <button type="button" class="admin-action-btn record-payout-btn"
                                                    data-ref-id="<?= $ref['id'] ?>"
                                                    data-name="<?= e($ref['promoter_name']) ?>"
                                                    data-earned="<?= $ref['commission_earned'] ?>"
                                                    data-paid="<?= $ref['commission_paid'] ?>"
                                                    data-due="<?= $ref['balance_due'] ?>"
                                                    title="Settle Payout">
                                                💰 Pay
                                            </button>
                                        <?php endif; ?>

                                        <!-- Edit Promoter -->
                                        <button type="button" class="admin-action-btn edit-ref-btn"
                                                data-ref='<?= json_encode($ref, JSON_HEX_APOS | JSON_HEX_QUOT) ?>'
                                                title="Edit Promoter">
                                            ✏️
                                        </button>

                                        <!-- Toggle Status -->
                                        <form method="POST" action="{{ route('host/event/referrals/' . $event->id . '/toggle/' . $ref['id']) }}" style="display: inline; margin: 0;">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="admin-action-btn" title="<?= $isActive ? 'Pause link' : 'Activate link' ?>">
                                                <?= $isActive ? '⏸️' : '▶️' ?>
                                            </button>
                                        </form>

                                        <!-- Delete -->
                                        <form method="POST" action="{{ route('host/event/referrals/' . $event->id . '/delete/' . $ref['id']) }}" style="display: inline; margin: 0;" onsubmit="return confirm('Are you sure you want to delete this referral link? Past transaction records will be preserved.');">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="admin-action-btn danger" title="Delete link">
                                                🗑️
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Mobile & Tablet Card View (<= 919px) -->
            <div class="ref-mobile-cards-wrapper" id="promoters-cards">
                <?php foreach ($referrals as $ref): ?>
                    <?php
                        $refUrl = $eventBaseUrl . '?ref=' . urlencode($ref['referral_code']);
                        $isActive = $ref['status'] === 'active';
                        $payoutStatus = $ref['payout_status'] ?? 'unpaid';
                        $waText = "Hey " . $ref['promoter_name'] . "! Here is your official promoter link for " . $event->name . ": " . $refUrl . " 🎟️ Earn on every ticket sold!";
                        $waUrl = "https://wa.me/" . (!empty($ref['promoter_phone']) ? preg_replace('/[^0-9]/', '', $ref['promoter_phone']) : '') . "?text=" . urlencode($waText);
                    ?>
                    <article class="ref-mobile-card <?= $isActive ? 'card-active' : 'card-inactive' ?>"
                             data-name="<?= strtolower(e($ref['promoter_name'])) ?>" 
                             data-code="<?= strtolower(e($ref['referral_code'])) ?>" 
                             data-phone="<?= strtolower(e($ref['promoter_phone'] ?? '')) ?>"
                             data-status="<?= e($ref['status']) ?>"
                             data-payout="<?= e($payoutStatus) ?>">
                        
                        <div class="ref-m-header">
                            <div class="ref-m-promoter">
                                <div class="promoter-avatar">
                                    <?= strtoupper(substr($ref['promoter_name'], 0, 1)) ?>
                                </div>
                                <div>
                                    <h4 class="ref-m-name"><?= e($ref['promoter_name']) ?></h4>
                                    <span class="promoter-cell-code">@<?= e($ref['referral_code']) ?></span>
                                </div>
                            </div>
                            <div style="display:flex; align-items:center; gap:0.4rem;">
                                <?php if ($ref['commission_earned'] > 0 && $ref['balance_due'] > 0): ?>
                                    <span class="payout-badge unpaid">Due <?= format_price($ref['balance_due']) ?></span>
                                <?php elseif ($ref['commission_earned'] > 0): ?>
                                    <span class="payout-badge paid">Settled</span>
                                <?php endif; ?>

                                <?php if ($isActive): ?>
                                    <span class="ref-status-badge active"><span class="status-dot green"></span> Active</span>
                                <?php else: ?>
                                    <span class="ref-status-badge inactive"><span class="status-dot gray"></span> Paused</span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- 1-Tap Copy & Share Bar -->
                        <div class="ref-m-link-row">
                            <input type="text" readonly value="<?= e($refUrl) ?>" class="ref-link-input" />
                            <button type="button" class="copy-ref-btn" data-url="<?= e($refUrl) ?>">
                                📋 Copy
                            </button>
                            <a href="<?= e($waUrl) ?>" target="_blank" rel="noopener" class="wa-share-btn" title="Share via WhatsApp">
                                💬
                            </a>
                            <button type="button" class="qr-view-btn" data-url="<?= e($refUrl) ?>" data-name="<?= e($ref['promoter_name']) ?>" title="QR Code">
                                📱
                            </button>
                        </div>

                        <!-- 4 Mobile Metric Chips -->
                        <div class="ref-m-chips-grid">
                            <div class="ref-m-chip">
                                <span class="chip-label">Traffic</span>
                                <span class="chip-value"><?= number_format($ref['visits_count']) ?> <small style="font-size:0.75rem; font-weight:normal; opacity:0.75;">(<?= number_format($ref['unique_visits']) ?> uniq)</small></span>
                            </div>
                            <div class="ref-m-chip">
                                <span class="chip-label">Tickets Sold</span>
                                <span class="chip-value <?= $ref['tickets_sold'] > 0 ? 'text-green' : '' ?>">
                                    <?= number_format($ref['tickets_sold']) ?>
                                    <small style="font-size:0.75rem; font-weight:normal; opacity:0.75;">(<?= number_format($ref['conversion_rate'], 1) ?>%)</small>
                                </span>
                            </div>
                            <div class="ref-m-chip">
                                <span class="chip-label">Revenue</span>
                                <span class="chip-value text-brand"><?= format_price($ref['revenue_generated']) ?></span>
                            </div>
                            <div class="ref-m-chip">
                                <span class="chip-label">Commission</span>
                                <span class="chip-value text-amber">
                                    <?= format_price($ref['commission_earned']) ?>
                                    <small style="font-size:0.72rem; font-weight:normal; opacity:0.8;">(<?= ($ref['commission_type'] ?? 'percentage') === 'fixed' ? format_price($ref['commission_rate']) . '/ea' : number_format($ref['commission_rate'], 1) . '%' ?>)</small>
                                </span>
                            </div>
                        </div>

                        <!-- Mobile Action Buttons -->
                        <div class="ref-m-actions">
                            <button type="button" class="checkout-btn secondary ref-m-btn view-orders-btn"
                                    data-ref-id="<?= $ref['id'] ?>"
                                    data-name="<?= e($ref['promoter_name']) ?>">
                                👁️ Orders (<?= number_format($ref['tickets_sold']) ?>)
                            </button>

                            <?php if ($ref['balance_due'] > 0): ?>
                                <button type="button" class="checkout-btn ref-m-btn record-payout-btn"
                                        style="background:#f59e0b; color:#fff;"
                                        data-ref-id="<?= $ref['id'] ?>"
                                        data-name="<?= e($ref['promoter_name']) ?>"
                                        data-earned="<?= $ref['commission_earned'] ?>"
                                        data-paid="<?= $ref['commission_paid'] ?>"
                                        data-due="<?= $ref['balance_due'] ?>">
                                    💰 Pay <?= format_price($ref['balance_due']) ?>
                                </button>
                            <?php endif; ?>

                            <button type="button" class="checkout-btn secondary ref-m-btn-icon edit-ref-btn"
                                    data-ref='<?= json_encode($ref, JSON_HEX_APOS | JSON_HEX_QUOT) ?>'
                                    title="Edit">
                                ✏️
                            </button>

                            <form method="POST" action="{{ route('host/event/referrals/' . $event->id . '/toggle/' . $ref['id']) }}" style="margin: 0;">
                                <?= csrf_field() ?>
                                <button type="submit" class="checkout-btn secondary ref-m-btn-icon" title="<?= $isActive ? 'Pause' : 'Activate' ?>">
                                    <?= $isActive ? '⏸️' : '▶️' ?>
                                </button>
                            </form>

                            <form method="POST" action="{{ route('host/event/referrals/' . $event->id . '/delete/' . $ref['id']) }}" style="margin: 0;" onsubmit="return confirm('Delete this referral link?');">
                                <?= csrf_field() ?>
                                <button type="submit" class="checkout-btn danger ref-m-btn-icon" title="Delete">
                                    🗑️
                                </button>
                            </form>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</div>

<!-- ═══════════════════════════════════════════════════════════════════
     MODAL 1: Create Referral Link Modal
     ═══════════════════════════════════════════════════════════════════ -->
<div id="referral-modal-backdrop" class="ref-modal-backdrop">
    <div class="ref-modal-card">
        <div class="ref-modal-header">
            <div>
                <h3 class="ref-modal-title">Create Promoter Link</h3>
                <p class="ref-modal-subtitle">Generate a tracked link for a promoter, ambassador, or influencer</p>
            </div>
            <button type="button" id="close-referral-modal-btn" class="ref-modal-close" aria-label="Close">&times;</button>
        </div>

        <form id="create-referral-form" method="POST" action="{{ route('host/event/referrals/' . $event->id . '/create') }}" novalidate>
            <?= csrf_field() ?>

            <div class="ref-form-group">
                <label for="modal-promoter-name">
                    Promoter / Influencer Name <span style="color:#ef4444;">*</span>
                </label>
                <input type="text" name="promoter_name" id="modal-promoter-name" required placeholder="e.g. John Doe, DJ Max" class="ref-input" />
            </div>

            <div class="ref-form-row">
                <div class="ref-form-group" style="flex: 1;">
                    <label for="modal-promoter-phone">Phone / WhatsApp (Optional)</label>
                    <input type="tel" name="promoter_phone" id="modal-promoter-phone" placeholder="e.g. 08012345678" class="ref-input" />
                </div>
                <div class="ref-form-group" style="flex: 1;">
                    <label for="modal-promoter-email">Email Address (Optional)</label>
                    <input type="email" name="promoter_email" id="modal-promoter-email" placeholder="e.g. promoter@gmail.com" class="ref-input" />
                </div>
            </div>

            <div class="ref-form-group">
                <label for="modal-referral-code">Custom Referral Code (Optional)</label>
                <input type="text" name="referral_code" id="modal-referral-code" placeholder="e.g. djmax, sarahvip (leave blank to auto-generate)" class="ref-input" />
                <small class="ref-input-help">Letters, numbers, hyphens, and underscores only.</small>
            </div>

            <div class="ref-form-row">
                <div class="ref-form-group" style="flex: 1.2;">
                    <label for="modal-commission-type">Commission Model</label>
                    <select name="commission_type" id="modal-commission-type" class="ref-input">
                        <option value="percentage">Percentage (%) of Ticket Price</option>
                        <option value="fixed">Fixed Amount (₦) per Ticket Sold</option>
                    </select>
                </div>
                <div class="ref-form-group" style="flex: 1;">
                    <label for="modal-commission-rate" id="modal-commission-label">Commission Rate (%)</label>
                    <input type="number" name="commission_rate" id="modal-commission-rate" value="0.00" min="0" max="100000" step="0.5" placeholder="0.00" class="ref-input" />
                </div>
            </div>

            <div class="ref-form-group">
                <label for="modal-notes">Internal Notes (Optional)</label>
                <textarea name="notes" id="modal-notes" rows="2" placeholder="e.g. Bank details: GTBank 0123456789, agreed on 50 tickets minimum" class="ref-input" style="resize:vertical;"></textarea>
            </div>

            <!-- Live Link Preview Box -->
            <div class="ref-preview-box">
                <div class="ref-preview-label">Live Link Preview:</div>
                <div id="modal-link-preview" class="ref-preview-url">
                    <?= e($eventBaseUrl) ?>?ref=<span id="preview-code-tag">promoter</span>
                </div>
            </div>

            <div class="ref-modal-actions">
                <button type="submit" class="checkout-btn ref-submit-btn">
                    Create Promoter Link
                </button>
                <button type="button" id="cancel-referral-modal-btn" class="checkout-btn secondary ref-cancel-btn">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════════════════
     MODAL 2: Edit Promoter Modal
     ═══════════════════════════════════════════════════════════════════ -->
<div id="edit-referral-modal-backdrop" class="ref-modal-backdrop">
    <div class="ref-modal-card">
        <div class="ref-modal-header">
            <div>
                <h3 class="ref-modal-title">Edit Promoter Details</h3>
                <p class="ref-modal-subtitle">Update commission terms, contact info, or link status</p>
            </div>
            <button type="button" id="close-edit-modal-btn" class="ref-modal-close" aria-label="Close">&times;</button>
        </div>

        <form id="edit-referral-form" method="POST" action="" novalidate>
            <?= csrf_field() ?>

            <div class="ref-form-group">
                <label for="edit-promoter-name">Promoter / Influencer Name <span style="color:#ef4444;">*</span></label>
                <input type="text" name="promoter_name" id="edit-promoter-name" required class="ref-input" />
            </div>

            <div class="ref-form-row">
                <div class="ref-form-group" style="flex: 1;">
                    <label for="edit-promoter-phone">Phone / WhatsApp</label>
                    <input type="tel" name="promoter_phone" id="edit-promoter-phone" class="ref-input" />
                </div>
                <div class="ref-form-group" style="flex: 1;">
                    <label for="edit-promoter-email">Email Address</label>
                    <input type="email" name="promoter_email" id="edit-promoter-email" class="ref-input" />
                </div>
            </div>

            <div class="ref-form-row">
                <div class="ref-form-group" style="flex: 1;">
                    <label for="edit-referral-code">Referral Code</label>
                    <input type="text" name="referral_code" id="edit-referral-code" class="ref-input" />
                </div>
                <div class="ref-form-group" style="flex: 1;">
                    <label for="edit-status">Status</label>
                    <select name="status" id="edit-status" class="ref-input">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive / Paused</option>
                    </select>
                </div>
            </div>

            <div class="ref-form-row">
                <div class="ref-form-group" style="flex: 1.2;">
                    <label for="edit-commission-type">Commission Model</label>
                    <select name="commission_type" id="edit-commission-type" class="ref-input">
                        <option value="percentage">Percentage (%) of Ticket Price</option>
                        <option value="fixed">Fixed Amount (₦) per Ticket Sold</option>
                    </select>
                </div>
                <div class="ref-form-group" style="flex: 1;">
                    <label for="edit-commission-rate" id="edit-commission-label">Commission Rate (%)</label>
                    <input type="number" name="commission_rate" id="edit-commission-rate" min="0" max="100000" step="0.5" class="ref-input" />
                </div>
            </div>

            <div class="ref-form-group">
                <label for="edit-notes">Internal Notes / Payment Records</label>
                <textarea name="notes" id="edit-notes" rows="3" class="ref-input" style="resize:vertical;"></textarea>
            </div>

            <div class="ref-modal-actions">
                <button type="submit" class="checkout-btn ref-submit-btn">
                    Save Changes
                </button>
                <button type="button" id="cancel-edit-modal-btn" class="checkout-btn secondary ref-cancel-btn">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════════════════
     MODAL 3: Record Commission Payout Settlement Modal
     ═══════════════════════════════════════════════════════════════════ -->
<div id="payout-modal-backdrop" class="ref-modal-backdrop">
    <div class="ref-modal-card">
        <div class="ref-modal-header">
            <div>
                <h3 class="ref-modal-title">Record Commission Payout</h3>
                <p class="ref-modal-subtitle">Log a payout made to your promoter to keep financial balances in sync</p>
            </div>
            <button type="button" id="close-payout-modal-btn" class="ref-modal-close" aria-label="Close">&times;</button>
        </div>

        <form id="payout-referral-form" method="POST" action="" novalidate>
            <?= csrf_field() ?>

            <div class="payout-summary-box">
                <div class="payout-sum-item">
                    <span>Promoter</span>
                    <strong id="payout-promoter-name">—</strong>
                </div>
                <div class="payout-sum-item">
                    <span>Total Earned</span>
                    <strong id="payout-total-earned">₦0.00</strong>
                </div>
                <div class="payout-sum-item">
                    <span>Already Paid</span>
                    <strong id="payout-already-paid">₦0.00</strong>
                </div>
                <div class="payout-sum-item highlight">
                    <span>Balance Outstanding</span>
                    <strong id="payout-balance-due" style="color:#f59e0b;">₦0.00</strong>
                </div>
            </div>

            <div class="ref-form-group">
                <label for="payout-amount">Payout Amount (₦) <span style="color:#ef4444;">*</span></label>
                <input type="number" name="amount" id="payout-amount" required min="1" step="0.01" class="ref-input" style="font-size:1.15rem; font-weight:700;" />
            </div>

            <div class="ref-form-group">
                <label for="payout-note">Payment Reference / Notes</label>
                <input type="text" name="note" id="payout-note" placeholder="e.g. Bank Transfer via GTBank Ref #987654" class="ref-input" />
                <small class="ref-input-help">This will be appended to the promoter's payment audit history.</small>
            </div>

            <div class="ref-modal-actions">
                <button type="submit" class="checkout-btn ref-submit-btn" style="background:#10b981;">
                    Confirm &amp; Record Payout
                </button>
                <button type="button" id="cancel-payout-modal-btn" class="checkout-btn secondary ref-cancel-btn">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════════════════
     MODAL 4: Attributed Orders Breakdown Modal (AJAX)
     ═══════════════════════════════════════════════════════════════════ -->
<div id="orders-modal-backdrop" class="ref-modal-backdrop">
    <div class="ref-modal-card" style="max-width: 750px;">
        <div class="ref-modal-header">
            <div>
                <h3 class="ref-modal-title">Attributed Orders — <span id="orders-promoter-title">Promoter</span></h3>
                <p class="ref-modal-subtitle">Direct transactions generated through this referral tracking link</p>
            </div>
            <button type="button" id="close-orders-modal-btn" class="ref-modal-close" aria-label="Close">&times;</button>
        </div>

        <div id="orders-loading-state" style="text-align:center; padding:2.5rem 1rem; color:rgb(var(--muted));">
            <span class="btn-spinner" style="width:24px; height:24px; border-width:3px;"></span>
            <p style="margin-top:0.75rem;">Loading orders data...</p>
        </div>

        <div id="orders-content-box" style="display:none;">
            <div class="orders-summary-banner">
                <div><span>Total Tickets Sold:</span> <strong id="orders-sum-tickets">0</strong></div>
                <div><span>Total Revenue:</span> <strong id="orders-sum-rev">₦0.00</strong></div>
                <div><span>Commission Earned:</span> <strong id="orders-sum-comm" style="color:#10b981;">₦0.00</strong></div>
            </div>

            <div class="orders-table-wrapper scrollable-list" style="max-height:360px; overflow-y:auto; margin-top:1rem; border:1px solid rgba(var(--muted),0.15); border-radius:12px;">
                <table class="tickets-table" style="font-size:0.85rem; width:100%;">
                    <thead>
                        <tr>
                            <th>Order Ref</th>
                            <th>Buyer</th>
                            <th style="text-align:center;">Tickets</th>
                            <th style="text-align:right;">Amount</th>
                            <th style="text-align:right;">Commission</th>
                            <th style="text-align:right;">Date</th>
                        </tr>
                    </thead>
                    <tbody id="orders-table-body">
                        <!-- Populated dynamically via JS -->
                    </tbody>
                </table>
            </div>
        </div>

        <div class="ref-modal-actions" style="margin-top:1.5rem;">
            <button type="button" id="close-orders-btn-bottom" class="checkout-btn secondary" style="width:100%;">
                Close
            </button>
        </div>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════════════════
     MODAL 5: QR Code Display Modal
     ═══════════════════════════════════════════════════════════════════ -->
<div id="qr-modal-backdrop" class="ref-modal-backdrop">
    <div class="ref-modal-card" style="max-width: 420px; text-align: center;">
        <div class="ref-modal-header" style="justify-content: flex-end; margin-bottom: 0.5rem;">
            <button type="button" id="close-qr-modal-btn" class="ref-modal-close" aria-label="Close">&times;</button>
        </div>

        <h3 class="ref-modal-title" id="qr-promoter-title" style="margin-bottom:0.25rem;">Promoter QR Pass</h3>
        <p class="ref-modal-subtitle" style="margin-bottom:1.25rem;">Scan this QR code to open the tracked referral link</p>

        <div style="background:#ffffff; padding:1.25rem; border-radius:16px; display:inline-block; box-shadow:0 10px 25px rgba(0,0,0,0.1); margin-bottom:1.25rem;">
            <img id="qr-image-display" src="" alt="Promoter QR Code" style="width:240px; height:240px; display:block;" />
        </div>

        <div id="qr-link-url" style="font-size:0.8rem; color:rgb(var(--brand)); word-break:break-all; font-family:monospace; margin-bottom:1.25rem;"></div>

        <div class="ref-modal-actions">
            <a id="qr-download-btn" href="" download="promoter_qrcode.png" class="checkout-btn" style="flex:1; text-decoration:none; justify-content:center; display:inline-flex; align-items:center; gap:0.4rem;">
                ⬇️ Download QR Code
            </a>
            <button type="button" id="cancel-qr-modal-btn" class="checkout-btn secondary ref-cancel-btn">
                Close
            </button>
        </div>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════════════════
     CSS STYLES
     ═══════════════════════════════════════════════════════════════════ -->
<style>
.referrals-page-wrapper {
    width: 100%;
    max-width: 1240px;
    margin: 0 auto;
}

.ref-top-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.ref-top-actions {
    display: flex;
    gap: 0.75rem;
    flex-wrap: wrap;
    align-items: center;
}

.ref-top-actions .export-btn,
.ref-top-actions .edit-event-btn {
    text-decoration: none;
    padding: 0.5rem 0.95rem;
    border-radius: 10px;
    font-size: 0.85rem;
    font-weight: 600;
    background: rgba(var(--muted), 0.08);
    border: 1px solid rgba(var(--muted), 0.2);
    color: rgb(var(--text));
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    transition: all 0.2s ease;
}

.ref-top-actions .export-btn:hover {
    background: rgba(16, 185, 129, 0.12);
    color: #10b981;
    border-color: rgba(16, 185, 129, 0.3);
}

.ref-top-actions .add-ref-btn {
    height: 38px;
    padding: 0 1.25rem;
    font-size: 0.88rem;
    font-weight: 700;
    border-radius: 10px;
    border: none;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
}

.admin-banner-pill {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    background: rgba(99,102,241,0.06);
    border: 1px solid rgba(99,102,241,0.18);
    border-radius: 12px;
    padding: 0.65rem 1rem;
    margin-bottom: 1.25rem;
}

.ref-hero-banner {
    margin-bottom: 1.5rem;
}

.ref-badge-wrapper {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    margin-bottom: 0.4rem;
    flex-wrap: wrap;
}

.ref-panel-badge {
    background: rgba(16, 185, 129, 0.12);
    color: #10b981;
    border: 1px solid rgba(16, 185, 129, 0.25);
    font-weight: 700;
    font-size: 0.8rem;
    padding: 0.25rem 0.65rem;
    border-radius: 999px;
}

.ref-dest-badge {
    font-size: 0.76rem;
    font-weight: 700;
    padding: 0.25rem 0.65rem;
    border-radius: 10px;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}

.flyer-dest {
    background: rgba(99, 102, 241, 0.12);
    color: #6366f1;
    border: 1px solid rgba(99, 102, 241, 0.25);
}

.standard-dest {
    background: rgba(var(--muted), 0.12);
    color: rgb(var(--muted));
    border: 1px solid rgba(var(--muted), 0.2);
}

.ref-badge-sub {
    font-size: 0.85rem;
    color: rgb(var(--muted));
}

.ref-event-title {
    margin: 0;
    font-size: clamp(1.35rem, 3vw, 1.85rem);
    font-weight: 800;
    letter-spacing: -0.02em;
}

.ref-event-meta {
    margin: 0.35rem 0 0;
    font-size: 0.9rem;
    color: rgb(var(--muted));
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.ref-meta-dot { opacity: 0.5; }

/* Stats Grid */
.ref-stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    margin-bottom: 1.75rem;
}

.ref-stats-grid .summary-card {
    border-radius: 16px;
    padding: 1.25rem;
}

.unique-badge {
    font-size: 0.72rem;
    font-weight: 700;
    background: rgba(var(--muted), 0.12);
    color: rgb(var(--muted));
    padding: 0.15rem 0.45rem;
    border-radius: 6px;
    vertical-align: middle;
    margin-left: 0.35rem;
}

.highlight-amber {
    border-color: rgba(245, 158, 11, 0.25);
    background: rgba(245, 158, 11, 0.03);
}

.highlight-amber .card-label,
.highlight-amber .card-value {
    color: #d97706;
}

.text-amber { color: #d97706 !important; }
.text-green { color: #10b981 !important; }
.text-brand { color: rgb(var(--brand)) !important; }

/* Top Promoter Spotlight */
.top-promoter-banner {
    margin-bottom: 1.5rem;
    padding: 1rem 1.25rem;
    border-radius: 16px;
    background: linear-gradient(135deg, rgba(245, 158, 11, 0.12), rgba(245, 158, 11, 0.03));
    border: 1px solid rgba(245, 158, 11, 0.25);
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 1rem;
}

.top-promoter-info {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.top-promoter-trophy {
    font-size: 1.6rem;
    flex-shrink: 0;
}

.top-promoter-eyebrow {
    font-size: 0.75rem;
    text-transform: uppercase;
    font-weight: 800;
    color: #d97706;
    letter-spacing: 0.5px;
    display: block;
}

.top-promoter-name {
    margin: 0;
    font-size: 1.05rem;
    font-weight: 700;
    color: rgb(var(--text));
}

.top-promoter-code {
    font-weight: 400;
    font-size: 0.85rem;
    color: rgb(var(--muted));
}

.top-promoter-metrics {
    display: flex;
    gap: 1.5rem;
    align-items: center;
    font-size: 0.9rem;
}

.promoter-metric-item strong {
    font-size: 1.05rem;
    display: block;
}

.promoter-metric-item span {
    color: rgb(var(--muted));
    font-size: 0.8rem;
}

.metric-revenue { color: #10b981; }
.metric-comm { color: #d97706; }

/* Content Card & Toolbar */
.ref-content-card {
    padding: 1.5rem;
    border-radius: 20px;
}

.ref-card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1rem;
    margin-bottom: 1.25rem;
}

.ref-section-title {
    margin: 0;
    font-size: 1.25rem;
    font-weight: 700;
}

.ref-section-desc {
    margin: 0.25rem 0 0;
    font-size: 0.85rem;
    color: rgb(var(--muted));
}

.ref-filters-toolbar {
    display: flex;
    gap: 0.65rem;
    align-items: center;
    flex-wrap: wrap;
}

.ref-search-box {
    position: relative;
    display: flex;
    align-items: center;
}

.ref-search-box .search-icon {
    position: absolute;
    left: 0.75rem;
    font-size: 0.85rem;
    pointer-events: none;
    opacity: 0.6;
}

.ref-search-input {
    padding: 0.45rem 0.75rem 0.45rem 2.1rem;
    border-radius: 10px;
    font-size: 0.84rem;
    border: 1px solid rgba(var(--muted), 0.2);
    background: rgb(var(--card));
    color: rgb(var(--text));
    width: 220px;
}

.ref-filter-selects {
    display: flex;
    gap: 0.5rem;
}

.ref-filter-dropdown {
    padding: 0.45rem 0.75rem;
    border-radius: 10px;
    font-size: 0.84rem;
    border: 1px solid rgba(var(--muted), 0.2);
    background: rgb(var(--card));
    color: rgb(var(--text));
}

/* Empty State */
.ref-empty-state {
    text-align: center;
    padding: 3.5rem 1.5rem;
    background: rgba(var(--muted), 0.03);
    border: 1px dashed rgba(var(--muted), 0.2);
    border-radius: 16px;
}

.ref-empty-state .empty-icon {
    font-size: 3rem;
    display: block;
    margin-bottom: 0.75rem;
}

.ref-empty-state h3 {
    margin: 0 0 0.5rem;
    font-size: 1.15rem;
    font-weight: 700;
}

.ref-empty-state p {
    font-size: 0.88rem;
    color: rgb(var(--muted));
    max-width: 440px;
    margin: 0 auto 1.5rem;
    line-height: 1.5;
}

/* Table Cells */
.promoter-cell {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.promoter-avatar {
    width: 38px;
    height: 38px;
    border-radius: 10px;
    background: linear-gradient(135deg, rgba(var(--brand), 0.2), rgba(var(--brand), 0.05));
    border: 1px solid rgba(var(--brand), 0.25);
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 800;
    font-size: 0.9rem;
    color: rgb(var(--brand));
    flex-shrink: 0;
}

.promoter-cell-name {
    font-weight: 700;
    font-size: 0.92rem;
}

.promoter-cell-meta {
    display: flex;
    gap: 0.35rem;
    align-items: center;
    flex-wrap: wrap;
    margin-top: 0.15rem;
}

.promoter-cell-code {
    font-size: 0.75rem;
    color: rgb(var(--muted));
    font-family: monospace;
}

.contact-pill {
    font-size: 0.7rem;
    background: rgba(var(--muted), 0.08);
    padding: 0.1rem 0.4rem;
    border-radius: 4px;
    color: rgb(var(--muted));
}

/* Link Box & Buttons */
.ref-link-box {
    display: flex;
    align-items: center;
    gap: 0.35rem;
    max-width: 290px;
}

.ref-link-input {
    font-size: 0.78rem;
    padding: 0.4rem 0.55rem;
    border-radius: 8px;
    border: 1px solid rgba(var(--muted), 0.2);
    background: rgba(var(--muted), 0.06);
    color: rgb(var(--text));
    width: 100%;
    text-overflow: ellipsis;
    white-space: nowrap;
    overflow: hidden;
    font-family: monospace;
    box-sizing: border-box;
}

.copy-ref-btn, .wa-share-btn, .qr-view-btn {
    padding: 0.4rem 0.55rem;
    font-size: 0.76rem;
    font-weight: 700;
    border-radius: 8px;
    cursor: pointer;
    white-space: nowrap;
    transition: all 0.2s ease;
    flex-shrink: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
}

.copy-ref-btn {
    border: 1px solid rgba(var(--brand), 0.3);
    background: rgba(var(--brand), 0.08);
    color: rgb(var(--brand));
}

.copy-ref-btn:hover { background: rgb(var(--brand)); color: #fff; }
.copy-ref-btn.copied { background: #10b981 !important; border-color: #10b981 !important; color: #fff !important; }

.wa-share-btn {
    border: 1px solid rgba(37, 211, 102, 0.3);
    background: rgba(37, 211, 102, 0.1);
    color: #25d366;
}

.wa-share-btn:hover { background: #25d366; color: #fff; }

.qr-view-btn {
    border: 1px solid rgba(99, 102, 241, 0.3);
    background: rgba(99, 102, 241, 0.1);
    color: #6366f1;
}

.qr-view-btn:hover { background: #6366f1; color: #fff; }

.traffic-cell, .sales-cell, .comm-cell {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.1rem;
}

.traffic-total { font-weight: 700; font-size: 0.92rem; }
.traffic-sub, .conv-sub { font-size: 0.72rem; color: rgb(var(--muted)); }

.tickets-sold-pill { font-weight: 800; font-size: 0.95rem; }
.tickets-sold-pill.has-sales { color: #10b981; }

.comm-cell { align-items: flex-end; }
.comm-earned { font-weight: 700; font-size: 0.92rem; color: #d97706; }
.comm-rate-pill {
    font-size: 0.72rem;
    background: rgba(245, 158, 11, 0.12);
    color: #d97706;
    padding: 0.1rem 0.4rem;
    border-radius: 4px;
    font-weight: 600;
}

/* Payout Badges */
.payout-badge {
    font-size: 0.74rem;
    font-weight: 700;
    padding: 0.2rem 0.55rem;
    border-radius: 20px;
    white-space: nowrap;
}

.payout-badge.unpaid { background: rgba(239, 68, 68, 0.12); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.25); }
.payout-badge.partial { background: rgba(245, 158, 11, 0.12); color: #d97706; border: 1px solid rgba(245, 158, 11, 0.25); }
.payout-badge.paid { background: rgba(16, 185, 129, 0.12); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.25); }
.payout-badge.none { color: rgb(var(--muted)); }

.ref-status-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    font-size: 0.75rem;
    font-weight: 700;
    padding: 0.2rem 0.55rem;
    border-radius: 20px;
    white-space: nowrap;
}

.ref-status-badge.active { background: rgba(16, 185, 129, 0.12); color: #10b981; }
.ref-status-badge.inactive { background: rgba(100, 116, 139, 0.15); color: rgb(100, 116, 139); }

.status-dot { width: 6px; height: 6px; border-radius: 50%; }
.status-dot.green { background: #10b981; }
.status-dot.gray { background: rgb(100, 116, 139); }

.ref-actions-group {
    display: inline-flex;
    gap: 0.35rem;
    align-items: center;
}

.ref-actions-group .admin-action-btn {
    padding: 0.35rem 0.55rem;
    font-size: 0.74rem;
    border-radius: 8px;
    cursor: pointer;
}

.ref-actions-group .record-payout-btn {
    background: rgba(245, 158, 11, 0.12);
    color: #d97706;
    border: 1px solid rgba(245, 158, 11, 0.25);
    font-weight: 700;
}

.ref-actions-group .record-payout-btn:hover {
    background: #d97706;
    color: #fff;
}

.ref-actions-group .view-orders-btn {
    background: rgba(99, 102, 241, 0.1);
    color: #6366f1;
    border: 1px solid rgba(99, 102, 241, 0.25);
    font-weight: 600;
}

.ref-actions-group .view-orders-btn:hover {
    background: #6366f1;
    color: #fff;
}

/* Mobile Cards Setup */
.ref-mobile-cards-wrapper {
    display: none;
    gap: 1rem;
    flex-direction: column;
}

.ref-mobile-card {
    background: rgba(var(--muted), 0.03);
    border: 1px solid rgba(var(--muted), 0.15);
    border-radius: 16px;
    padding: 1.15rem;
    display: flex;
    flex-direction: column;
    gap: 0.85rem;
    box-sizing: border-box;
}

.ref-mobile-card.card-active { border-left: 4px solid #10b981; }
.ref-mobile-card.card-inactive { border-left: 4px solid rgb(100, 116, 139); opacity: 0.85; }

.ref-m-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 0.5rem;
}

.ref-m-promoter {
    display: flex;
    align-items: center;
    gap: 0.65rem;
}

.ref-m-name { margin: 0; font-size: 0.98rem; font-weight: 700; }

.ref-m-link-row {
    display: flex;
    gap: 0.4rem;
    align-items: center;
}

.ref-m-chips-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 0.5rem;
}

.ref-m-chip {
    background: rgb(var(--card));
    border: 1px solid rgba(var(--muted), 0.12);
    border-radius: 10px;
    padding: 0.6rem 0.75rem;
    display: flex;
    flex-direction: column;
    gap: 0.15rem;
}

.ref-m-chip .chip-label {
    font-size: 0.72rem;
    font-weight: 700;
    color: rgb(var(--muted));
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

.ref-m-chip .chip-value {
    font-size: 0.95rem;
    font-weight: 800;
}

.ref-m-actions {
    display: flex;
    gap: 0.45rem;
    align-items: center;
    margin-top: 0.25rem;
    flex-wrap: wrap;
}

.ref-m-btn {
    flex: 1;
    height: 38px;
    font-size: 0.82rem;
    font-weight: 600;
    border-radius: 10px;
    justify-content: center;
    display: inline-flex;
    align-items: center;
}

.ref-m-btn-icon {
    height: 38px;
    width: 38px;
    padding: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 10px;
    flex-shrink: 0;
}

/* Modals */
.ref-modal-backdrop {
    display: none;
    position: fixed;
    inset: 0;
    z-index: 99999;
    background: rgba(15, 23, 42, 0.65);
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    align-items: center;
    justify-content: center;
    padding: 1rem;
    box-sizing: border-box;
}

.ref-modal-card {
    background: rgb(var(--card));
    color: rgb(var(--text));
    border-radius: 24px;
    border: 1px solid rgba(var(--muted), 0.2);
    box-shadow: 0 25px 60px rgba(0, 0, 0, 0.35);
    width: 100%;
    max-width: 520px;
    max-height: 90vh;
    overflow-y: auto;
    padding: 2rem;
    box-sizing: border-box;
    animation: modalScale 0.25s cubic-bezier(0.16, 1, 0.3, 1);
}

.ref-modal-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1.5rem;
}

.ref-modal-title { margin: 0; font-size: 1.25rem; font-weight: 800; }
.ref-modal-subtitle { margin: 0.25rem 0 0; font-size: 0.85rem; color: rgb(var(--muted)); }
.ref-modal-close { background: none; border: none; font-size: 1.5rem; cursor: pointer; color: rgb(var(--muted)); padding: 0; }

.ref-form-group { margin-bottom: 1.15rem; }
.ref-form-row { display: flex; gap: 0.85rem; }
.ref-form-group label { display: block; font-size: 0.88rem; font-weight: 600; margin-bottom: 0.4rem; }

.ref-input {
    width: 100%;
    box-sizing: border-box;
    padding: 0.7rem 0.95rem;
    border-radius: 12px;
    font-size: 0.95rem;
    border: 1px solid rgba(var(--muted), 0.2);
    background: rgb(var(--card));
    color: rgb(var(--text));
}

.ref-input-help { display: block; margin-top: 0.3rem; font-size: 0.78rem; color: rgb(var(--muted)); }

.ref-preview-box {
    margin-bottom: 1.5rem;
    padding: 0.85rem 1rem;
    border-radius: 12px;
    background: rgba(var(--muted), 0.06);
    border: 1px solid rgba(var(--muted), 0.15);
}

.ref-preview-label { font-size: 0.75rem; text-transform: uppercase; font-weight: 700; color: rgb(var(--muted)); margin-bottom: 0.25rem; }
.ref-preview-url { font-size: 0.82rem; color: rgb(var(--brand)); word-break: break-all; font-family: monospace; font-weight: 600; }

.ref-modal-actions { display: flex; gap: 0.75rem; margin-top: 1.5rem; }
.ref-submit-btn { flex: 1; height: 46px; font-size: 0.95rem; font-weight: 700; border-radius: 12px; border: none; cursor: pointer; }
.ref-cancel-btn { flex: 0 0 auto; height: 46px; padding: 0 1.25rem; font-size: 0.92rem; font-weight: 600; border-radius: 12px; cursor: pointer; }

/* Payout summary box inside modal */
.payout-summary-box {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 0.65rem;
    padding: 1rem;
    background: rgba(var(--muted), 0.06);
    border: 1px solid rgba(var(--muted), 0.15);
    border-radius: 14px;
    margin-bottom: 1.25rem;
}

.payout-sum-item { display: flex; flex-direction: column; gap: 0.15rem; }
.payout-sum-item span { font-size: 0.75rem; color: rgb(var(--muted)); text-transform: uppercase; font-weight: 600; }
.payout-sum-item strong { font-size: 0.98rem; }
.payout-sum-item.highlight strong { font-size: 1.15rem; font-weight: 800; }

/* Orders breakdown inside modal */
.orders-summary-banner {
    display: flex;
    justify-content: space-around;
    padding: 0.75rem 1rem;
    background: rgba(var(--muted), 0.06);
    border-radius: 10px;
    font-size: 0.85rem;
}

@keyframes modalScale {
    from { transform: scale(0.92); opacity: 0; }
    to { transform: scale(1); opacity: 1; }
}

/* Responsive */
@media (max-width: 919px) {
    .ref-desktop-table-wrapper { display: none !important; }
    .ref-mobile-cards-wrapper { display: flex !important; }
    .ref-stats-grid { grid-template-columns: repeat(2, 1fr); gap: 0.85rem; }
    .top-promoter-banner { flex-direction: column; align-items: flex-start; gap: 0.85rem; }
    .top-promoter-metrics { width: 100%; justify-content: space-between; border-top: 1px dashed rgba(245, 158, 11, 0.25); padding-top: 0.75rem; }
}

@media (max-width: 600px) {
    .ref-top-bar { flex-direction: column; align-items: stretch; gap: 0.85rem; }
    .ref-top-actions { display: grid; grid-template-columns: 1fr 1fr; width: 100%; }
    .ref-top-actions .add-ref-btn { grid-column: span 2; }
    .ref-stats-grid { grid-template-columns: 1fr; }
    .ref-form-row { flex-direction: column; gap: 0; }
    .ref-content-card { padding: 1.15rem 1rem; }
    .ref-filters-toolbar { flex-direction: column; width: 100%; align-items: stretch; }
    .ref-search-input { width: 100%; }
    .ref-filter-selects { width: 100%; }
    .ref-filter-dropdown { flex: 1; }
}
</style>

<!-- ═══════════════════════════════════════════════════════════════════
     JAVASCRIPT INTERACTIVITY & HANDLERS
     ═══════════════════════════════════════════════════════════════════ -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const eventBaseUrl = <?= json_encode($eventBaseUrl) ?>;
    const eventId = <?= (int)$event->id ?>;

    // ── 1. Create Modal ──────────────────────────────────────────────
    const createModal = document.getElementById('referral-modal-backdrop');
    const openCreateBtns = [
        document.getElementById('open-referral-modal-btn'),
        ...document.querySelectorAll('.open-referral-modal-trigger')
    ].filter(Boolean);
    const closeCreateBtn = document.getElementById('close-referral-modal-btn');
    const cancelCreateBtn = document.getElementById('cancel-referral-modal-btn');
    const createNameInput = document.getElementById('modal-promoter-name');
    const createCodeInput = document.getElementById('modal-referral-code');
    const createPreviewTag = document.getElementById('preview-code-tag');
    const createForm = document.getElementById('create-referral-form');
    const createTypeSelect = document.getElementById('modal-commission-type');
    const createLabel = document.getElementById('modal-commission-label');

    function openCreate() {
        if (!createModal) return;
        createModal.style.display = 'flex';
        if (createNameInput) createNameInput.focus();
    }

    function closeCreate() {
        if (!createModal) return;
        createModal.style.display = 'none';
        if (createForm) createForm.reset();
        if (createPreviewTag) createPreviewTag.textContent = 'promoter';
    }

    openCreateBtns.forEach(btn => btn && btn.addEventListener('click', openCreate));
    if (closeCreateBtn) closeCreateBtn.addEventListener('click', closeCreate);
    if (cancelCreateBtn) cancelCreateBtn.addEventListener('click', closeCreate);

    if (createModal) {
        createModal.addEventListener('click', function (e) {
            if (e.target === createModal) closeCreate();
        });
    }

    if (createTypeSelect) {
        createTypeSelect.addEventListener('change', function () {
            createLabel.textContent = this.value === 'fixed' ? 'Fixed Amount (₦) per Ticket' : 'Commission Rate (%)';
        });
    }

    function updateCreatePreview() {
        if (!createPreviewTag) return;
        const custom = createCodeInput ? createCodeInput.value.trim().toLowerCase().replace(/[^a-z0-9_\-]+/g, '') : '';
        if (custom) {
            createPreviewTag.textContent = custom;
        } else if (createNameInput && createNameInput.value.trim()) {
            const raw = createNameInput.value.trim().toLowerCase().replace(/[^a-z0-9]+/g, '').substring(0, 12);
            createPreviewTag.textContent = raw || 'promoter';
        } else {
            createPreviewTag.textContent = 'promoter';
        }
    }

    if (createNameInput) createNameInput.addEventListener('input', updateCreatePreview);
    if (createCodeInput) createCodeInput.addEventListener('input', updateCreatePreview);

    // ── 2. Edit Modal ────────────────────────────────────────────────
    const editModal = document.getElementById('edit-referral-modal-backdrop');
    const closeEditBtn = document.getElementById('close-edit-modal-btn');
    const cancelEditBtn = document.getElementById('cancel-edit-modal-btn');
    const editForm = document.getElementById('edit-referral-form');
    const editNameInput = document.getElementById('edit-promoter-name');
    const editPhoneInput = document.getElementById('edit-promoter-phone');
    const editEmailInput = document.getElementById('edit-promoter-email');
    const editCodeInput = document.getElementById('edit-referral-code');
    const editStatusSelect = document.getElementById('edit-status');
    const editTypeSelect = document.getElementById('edit-commission-type');
    const editRateInput = document.getElementById('edit-commission-rate');
    const editNotesInput = document.getElementById('edit-notes');
    const editLabel = document.getElementById('edit-commission-label');

    function closeEdit() {
        if (!editModal) return;
        editModal.style.display = 'none';
    }

    if (closeEditBtn) closeEditBtn.addEventListener('click', closeEdit);
    if (cancelEditBtn) cancelEditBtn.addEventListener('click', closeEdit);
    if (editModal) {
        editModal.addEventListener('click', function (e) {
            if (e.target === editModal) closeEdit();
        });
    }

    if (editTypeSelect) {
        editTypeSelect.addEventListener('change', function () {
            editLabel.textContent = this.value === 'fixed' ? 'Fixed Amount (₦) per Ticket' : 'Commission Rate (%)';
        });
    }

    document.querySelectorAll('.edit-ref-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const raw = this.getAttribute('data-ref');
            if (!raw) return;
            try {
                const ref = JSON.parse(raw);
                editForm.action = '/host/event/referrals/' + eventId + '/update/' + ref.id;
                editNameInput.value = ref.promoter_name || '';
                editPhoneInput.value = ref.promoter_phone || '';
                editEmailInput.value = ref.promoter_email || '';
                editCodeInput.value = ref.referral_code || '';
                editStatusSelect.value = ref.status || 'active';
                editTypeSelect.value = ref.commission_type || 'percentage';
                editRateInput.value = parseFloat(ref.commission_rate || 0);
                editNotesInput.value = ref.notes || '';

                if (editLabel) {
                    editLabel.textContent = (ref.commission_type === 'fixed') ? 'Fixed Amount (₦) per Ticket' : 'Commission Rate (%)';
                }

                editModal.style.display = 'flex';
                editNameInput.focus();
            } catch (err) {
                console.error('Failed to parse promoter data', err);
            }
        });
    });

    // ── 3. Record Payout Modal ───────────────────────────────────────
    const payoutModal = document.getElementById('payout-modal-backdrop');
    const closePayoutBtn = document.getElementById('close-payout-modal-btn');
    const cancelPayoutBtn = document.getElementById('cancel-payout-modal-btn');
    const payoutForm = document.getElementById('payout-referral-form');
    const payoutPromoterName = document.getElementById('payout-promoter-name');
    const payoutTotalEarned = document.getElementById('payout-total-earned');
    const payoutAlreadyPaid = document.getElementById('payout-already-paid');
    const payoutBalanceDue = document.getElementById('payout-balance-due');
    const payoutAmountInput = document.getElementById('payout-amount');

    function closePayout() {
        if (!payoutModal) return;
        payoutModal.style.display = 'none';
    }

    if (closePayoutBtn) closePayoutBtn.addEventListener('click', closePayout);
    if (cancelPayoutBtn) cancelPayoutBtn.addEventListener('click', closePayout);
    if (payoutModal) {
        payoutModal.addEventListener('click', function (e) {
            if (e.target === payoutModal) closePayout();
        });
    }

    document.querySelectorAll('.record-payout-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const refId = this.getAttribute('data-ref-id');
            const name = this.getAttribute('data-name');
            const earned = parseFloat(this.getAttribute('data-earned') || 0);
            const paid = parseFloat(this.getAttribute('data-paid') || 0);
            const due = parseFloat(this.getAttribute('data-due') || 0);

            payoutForm.action = '/host/event/referrals/' + eventId + '/payout/' + refId;
            payoutPromoterName.textContent = name;
            payoutTotalEarned.textContent = '₦' + earned.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            payoutAlreadyPaid.textContent = '₦' + paid.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            payoutBalanceDue.textContent = '₦' + due.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            payoutAmountInput.value = due > 0 ? due.toFixed(2) : '';

            payoutModal.style.display = 'flex';
            payoutAmountInput.focus();
        });
    });

    // ── 4. Orders Breakdown Modal (AJAX) ─────────────────────────────
    const ordersModal = document.getElementById('orders-modal-backdrop');
    const closeOrdersBtn = document.getElementById('close-orders-modal-btn');
    const closeOrdersBtnBottom = document.getElementById('close-orders-btn-bottom');
    const ordersPromoterTitle = document.getElementById('orders-promoter-title');
    const ordersLoading = document.getElementById('orders-loading-state');
    const ordersContent = document.getElementById('orders-content-box');
    const ordersTableBody = document.getElementById('orders-table-body');
    const ordersSumTickets = document.getElementById('orders-sum-tickets');
    const ordersSumRev = document.getElementById('orders-sum-rev');
    const ordersSumComm = document.getElementById('orders-sum-comm');

    function closeOrders() {
        if (!ordersModal) return;
        ordersModal.style.display = 'none';
    }

    if (closeOrdersBtn) closeOrdersBtn.addEventListener('click', closeOrders);
    if (closeOrdersBtnBottom) closeOrdersBtnBottom.addEventListener('click', closeOrders);
    if (ordersModal) {
        ordersModal.addEventListener('click', function (e) {
            if (e.target === ordersModal) closeOrders();
        });
    }

    document.querySelectorAll('.view-orders-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const refId = this.getAttribute('data-ref-id');
            const name = this.getAttribute('data-name');

            ordersPromoterTitle.textContent = name;
            ordersLoading.style.display = 'block';
            ordersContent.style.display = 'none';
            ordersModal.style.display = 'flex';

            fetch('/host/event/referrals/' + eventId + '/orders/' + refId)
                .then(r => r.json())
                .then(data => {
                    ordersLoading.style.display = 'none';
                    if (!data.success) {
                        alert(data.message || 'Failed to load orders.');
                        closeOrders();
                        return;
                    }

                    ordersSumTickets.textContent = (data.promoter.tickets_sold || 0).toLocaleString();
                    ordersSumRev.textContent = data.promoter.revenue_generated;
                    ordersSumComm.textContent = data.promoter.commission_earned;

                    ordersTableBody.innerHTML = '';
                    if (!data.orders || data.orders.length === 0) {
                        ordersTableBody.innerHTML = '<tr><td colspan="6" style="text-align:center; padding:2rem; color:rgb(var(--muted));">No completed ticket purchases recorded via this referral link yet.</td></tr>';
                    } else {
                        data.orders.forEach(order => {
                            const tr = document.createElement('tr');
                            tr.innerHTML = `
                                <td>
                                    <span style="font-family:monospace; font-weight:700;">${order.reference}</span>
                                </td>
                                <td>
                                    <div><strong>${order.payer_name || 'Anonymous'}</strong></div>
                                    <div style="font-size:0.75rem; color:rgb(var(--muted));">${order.payer_email || ''}</div>
                                </td>
                                <td style="text-align:center; font-weight:700;">${order.ticket_count || 1}</td>
                                <td style="text-align:right; font-weight:700;">${order.formatted_amount}</td>
                                <td style="text-align:right; font-weight:700; color:#10b981;">${order.formatted_commission}</td>
                                <td style="text-align:right; font-size:0.78rem; color:rgb(var(--muted));">${order.formatted_date}</td>
                            `;
                            ordersTableBody.appendChild(tr);
                        });
                    }

                    ordersContent.style.display = 'block';
                })
                .catch(err => {
                    console.error('Orders fetch error', err);
                    ordersLoading.style.display = 'none';
                    alert('Unable to retrieve orders at this time.');
                    closeOrders();
                });
        });
    });

    // ── 5. QR Code Modal ─────────────────────────────────────────────
    const qrModal = document.getElementById('qr-modal-backdrop');
    const closeQrBtn = document.getElementById('close-qr-modal-btn');
    const cancelQrBtn = document.getElementById('cancel-qr-modal-btn');
    const qrImg = document.getElementById('qr-image-display');
    const qrLinkUrl = document.getElementById('qr-link-url');
    const qrDownloadBtn = document.getElementById('qr-download-btn');
    const qrPromoterTitle = document.getElementById('qr-promoter-title');

    function closeQr() {
        if (!qrModal) return;
        qrModal.style.display = 'none';
    }

    if (closeQrBtn) closeQrBtn.addEventListener('click', closeQr);
    if (cancelQrBtn) cancelQrBtn.addEventListener('click', closeQr);
    if (qrModal) {
        qrModal.addEventListener('click', function (e) {
            if (e.target === qrModal) closeQr();
        });
    }

    document.querySelectorAll('.qr-view-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const url = this.getAttribute('data-url');
            const name = this.getAttribute('data-name');
            if (!url) return;

            const qrSrc = 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&margin=10&data=' + encodeURIComponent(url);
            qrImg.src = qrSrc;
            qrPromoterTitle.textContent = name + ' — QR Pass';
            qrLinkUrl.textContent = url;
            qrDownloadBtn.href = qrSrc;
            qrDownloadBtn.setAttribute('download', name.toLowerCase().replace(/[^a-z0-9]+/g, '_') + '_qr.png');

            qrModal.style.display = 'flex';
        });
    });

    // ── 6. Clipboard Copy ────────────────────────────────────────────
    function copyText(text, inputEl) {
        if (navigator.clipboard && window.isSecureContext) {
            return navigator.clipboard.writeText(text);
        }
        return new Promise((resolve, reject) => {
            try {
                if (inputEl) {
                    inputEl.focus();
                    inputEl.select();
                    inputEl.setSelectionRange(0, 99999);
                    if (document.execCommand('copy')) return resolve();
                }
                const t = document.createElement('textarea');
                t.value = text;
                t.style.position = 'fixed';
                t.style.left = '-9999px';
                document.body.appendChild(t);
                t.focus();
                t.select();
                const ok = document.execCommand('copy');
                document.body.removeChild(t);
                ok ? resolve() : reject(new Error('execCommand copy failed'));
            } catch (e) {
                reject(e);
            }
        });
    }

    document.querySelectorAll('.copy-ref-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const url = this.getAttribute('data-url');
            if (!url) return;
            const inputEl = this.closest('div')?.querySelector('.ref-link-input');

            copyText(url, inputEl).then(() => {
                const orig = this.innerHTML;
                this.innerHTML = '✓ Copied!';
                this.classList.add('copied');
                setTimeout(() => {
                    this.innerHTML = orig;
                    this.classList.remove('copied');
                }, 2000);
            }).catch(() => {
                if (inputEl) { inputEl.focus(); inputEl.select(); }
                prompt('Copy this referral link:', url);
            });
        });
    });

    document.querySelectorAll('.ref-link-input').forEach(input => {
        input.addEventListener('click', function () { this.select(); });
    });

    // ── 7. Instant Search & Filtering ────────────────────────────────
    const searchInput = document.getElementById('promoter-search');
    const statusFilter = document.getElementById('status-filter');
    const payoutFilter = document.getElementById('payout-filter');
    const tableRows = document.querySelectorAll('#promoters-table tbody tr.promoter-row');
    const mobileCards = document.querySelectorAll('#promoters-cards article.ref-mobile-card');

    function applyFilters() {
        const query = searchInput ? searchInput.value.trim().toLowerCase() : '';
        const st = statusFilter ? statusFilter.value : 'all';
        const po = payoutFilter ? payoutFilter.value : 'all';

        function checkMatch(el) {
            const name = el.getAttribute('data-name') || '';
            const code = el.getAttribute('data-code') || '';
            const phone = el.getAttribute('data-phone') || '';
            const status = el.getAttribute('data-status') || '';
            const payout = el.getAttribute('data-payout') || '';

            const matchesQuery = !query || name.includes(query) || code.includes(query) || phone.includes(query);
            const matchesStatus = (st === 'all') || (status === st);
            const matchesPayout = (po === 'all') || (payout === po);

            return matchesQuery && matchesStatus && matchesPayout;
        }

        tableRows.forEach(row => {
            row.style.display = checkMatch(row) ? '' : 'none';
        });

        mobileCards.forEach(card => {
            card.style.display = checkMatch(card) ? 'flex' : 'none';
        });
    }

    if (searchInput) searchInput.addEventListener('input', applyFilters);
    if (statusFilter) statusFilter.addEventListener('change', applyFilters);
    if (payoutFilter) payoutFilter.addEventListener('change', applyFilters);
});
</script>
@endsection
