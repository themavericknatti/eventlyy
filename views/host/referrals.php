<?php

/**
 * Host Event Referral & Affiliate Tracking Hub
 *
 * @package Evently
 * @subpackage Views/Host
 * @version 1.0.0
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
            <a href="{{ route('host/event/edit/' . $event->id) }}" class="admin-action-btn edit-event-btn">
                <span>✏️</span> Edit Event
            </a>
            <button type="button" id="open-referral-modal-btn" class="checkout-btn add-ref-btn">
                <span>+</span> Add Promoter Link
            </button>
        </div>
    </div>

    <?php if (!empty($isAdmin) && !empty($organizerName)): ?>
        <div style="display:flex; align-items:center; gap:0.6rem; background:rgba(99,102,241,0.06); border:1px solid rgba(99,102,241,0.18); border-radius:12px; padding:0.6rem 1rem; margin-bottom:1rem;">
            <span style="font-size:1.1rem;">🛡️</span>
            <div style="font-size:0.84rem; line-height:1.45;">
                <span style="font-weight:700; color:#6366f1;">Admin View</span>
                <span style="color:var(--text-muted); margin:0 0.25rem;">·</span>
                <span style="font-weight:600;">Event by <?= e($organizerName) ?></span>
                <span style="opacity:0.5; margin:0 0.2rem;">·</span>
                <span style="opacity:0.65;"><?= e($organizerEmail) ?></span>
            </div>
        </div>
    <?php endif; ?>

    <!-- Event Hero Banner -->
    <section class="page-hero ref-hero-banner">
        <div>
            <div class="ref-badge-wrapper" style="display:flex; align-items:center; gap:0.5rem; flex-wrap:wrap; margin-bottom:0.5rem;">
                <span class="panel-badge ref-panel-badge">🔗 Referral Hub</span>
                <?php if (!empty($event->flyer_page_enabled)): ?>
                    <span style="font-size:0.76rem; font-weight:800; background:rgba(99,102,241,0.15); color:#6366f1; border:1px solid rgba(99,102,241,0.3); padding:0.25rem 0.65rem; border-radius:10px; display:inline-flex; align-items:center; gap:4px;">
                        🎨 Links directing to Flyer Page (<code>/f/...</code>)
                    </span>
                <?php else: ?>
                    <span style="font-size:0.76rem; font-weight:800; background:rgba(var(--muted),0.12); color:rgb(var(--muted)); padding:0.25rem 0.65rem; border-radius:10px; display:inline-flex; align-items:center; gap:4px;">
                        📖 Links directing to Standard Event Page
                    </span>
                <?php endif; ?>
                <span class="ref-badge-sub">Tracking links for promoters &amp; influencers</span>
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
            <p class="card-meta">Active affiliate links</p>
        </article>

        <article class="summary-card">
            <span class="card-label">Link Visits / Clicks</span>
            <h3 class="card-value"><?= number_format($totalVisits) ?></h3>
            <p class="card-meta">Total referral traffic</p>
        </article>

        <article class="summary-card highlight-green">
            <span class="card-label">Tickets Sold via Links</span>
            <h3 class="card-value"><?= number_format($totalTicketsSold) ?></h3>
            <p class="card-meta">
                Conv. Rate: <strong><?= number_format($conversionRate, 1) ?>%</strong>
            </p>
        </article>

        <article class="summary-card highlight-brand">
            <span class="card-label">Referral Gross Revenue</span>
            <h3 class="card-value"><?= format_price($totalRevenue) ?></h3>
            <p class="card-meta">From promoter sales</p>
        </article>
    </section>

    <?php if ($topPromoter): ?>
        <div class="top-promoter-banner">
            <div class="top-promoter-info">
                <span class="top-promoter-trophy">🏆</span>
                <div>
                    <span class="top-promoter-eyebrow">Top Performing Promoter</span>
                    <h4 class="top-promoter-name"><?= e($topPromoter['promoter_name']) ?> <span class="top-promoter-code">(@<?= e($topPromoter['referral_code']) ?>)</span></h4>
                </div>
            </div>
            <div class="top-promoter-metrics">
                <div class="promoter-metric-item"><strong><?= number_format($topPromoter['tickets_sold']) ?></strong> <span>tickets</span></div>
                <div class="promoter-metric-item"><strong class="metric-revenue"><?= format_price($topPromoter['revenue_generated']) ?></strong> <span>revenue</span></div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Promoters & Referral Links Section -->
    <section class="form-card ref-content-card">
        <div class="ref-card-header">
            <div>
                <h2 class="ref-section-title">Promoter Referral Links</h2>
                <p class="ref-section-desc">Share unique links with each promoter to track individual performance</p>
            </div>
        </div>

        <?php if (empty($referrals)): ?>
            <div class="empty-state ref-empty-state">
                <span class="empty-icon">🔗</span>
                <h3>No Referral Links Created Yet</h3>
                <p>
                    Generate unique referral links for your event promoters, influencers, and ticket ambassadors to track exactly how many tickets each person sells.
                </p>
                <button type="button" class="checkout-btn open-referral-modal-trigger">
                    + Create First Referral Link
                </button>
            </div>
        <?php else: ?>
            <!-- Desktop Table View (>= 851px) -->
            <div class="ref-desktop-table-wrapper scrollable-list">
                <table class="tickets-table" role="grid">
                    <thead>
                        <tr>
                            <th>Promoter</th>
                            <th>Referral Link</th>
                            <th style="text-align: center;">Visits</th>
                            <th style="text-align: center;">Tickets Sold</th>
                            <th style="text-align: right;">Revenue</th>
                            <th style="text-align: center;">Commission</th>
                            <th style="text-align: center;">Status</th>
                            <th style="text-align: right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($referrals as $ref): ?>
                            <?php
                                $refUrl = $eventBaseUrl . '?ref=' . urlencode($ref['referral_code']);
                                $isActive = $ref['status'] === 'active';
                            ?>
                            <tr>
                                <td>
                                    <div class="promoter-cell">
                                        <div class="promoter-avatar">
                                            <?= strtoupper(substr($ref['promoter_name'], 0, 1)) ?>
                                        </div>
                                        <div>
                                            <div class="promoter-cell-name"><?= e($ref['promoter_name']) ?></div>
                                            <span class="promoter-cell-code">ref=<?= e($ref['referral_code']) ?></span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="ref-link-box">
                                        <input type="text" readonly value="<?= e($refUrl) ?>" class="ref-link-input" />
                                        <button type="button" class="copy-ref-btn" data-url="<?= e($refUrl) ?>" title="Copy to Clipboard">
                                            📋 Copy
                                        </button>
                                    </div>
                                </td>
                                <td style="text-align: center; font-weight: 600;">
                                    <?= number_format($ref['visits_count']) ?>
                                </td>
                                <td style="text-align: center;">
                                    <span class="tickets-sold-pill <?= $ref['tickets_sold'] > 0 ? 'has-sales' : '' ?>">
                                        <?= number_format($ref['tickets_sold']) ?>
                                    </span>
                                </td>
                                <td style="text-align: right; font-weight: 700; color: <?= $ref['revenue_generated'] > 0 ? 'rgb(var(--brand))' : 'inherit' ?>;">
                                    <?= format_price($ref['revenue_generated']) ?>
                                </td>
                                <td style="text-align: center;">
                                    <?php if ($ref['commission_rate'] > 0): ?>
                                        <span class="commission-badge">
                                            <?= number_format($ref['commission_rate'], 1) ?>%
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
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
                                        <form method="POST" action="{{ route('host/event/referrals/' . $event->id . '/toggle/' . $ref['id']) }}" style="display: inline; margin: 0;">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="admin-action-btn" title="<?= $isActive ? 'Pause link' : 'Activate link' ?>">
                                                <?= $isActive ? '⏸️ Pause' : '▶️ Activate' ?>
                                            </button>
                                        </form>
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

            <!-- Mobile & Tablet Card View (<= 850px) -->
            <div class="ref-mobile-cards-wrapper">
                <?php foreach ($referrals as $ref): ?>
                    <?php
                        $refUrl = $eventBaseUrl . '?ref=' . urlencode($ref['referral_code']);
                        $isActive = $ref['status'] === 'active';
                    ?>
                    <article class="ref-mobile-card <?= $isActive ? 'card-active' : 'card-inactive' ?>">
                        <div class="ref-m-header">
                            <div class="ref-m-promoter">
                                <div class="promoter-avatar">
                                    <?= strtoupper(substr($ref['promoter_name'], 0, 1)) ?>
                                </div>
                                <div>
                                    <h4 class="ref-m-name"><?= e($ref['promoter_name']) ?></h4>
                                    <span class="promoter-cell-code">ref=<?= e($ref['referral_code']) ?></span>
                                </div>
                            </div>
                            <div>
                                <?php if ($isActive): ?>
                                    <span class="ref-status-badge active">
                                        <span class="status-dot green"></span> Active
                                    </span>
                                <?php else: ?>
                                    <span class="ref-status-badge inactive">
                                        <span class="status-dot gray"></span> Inactive
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- 1-Tap Copy Link Bar -->
                        <div class="ref-m-link-row">
                            <input type="text" readonly value="<?= e($refUrl) ?>" class="ref-link-input" />
                            <button type="button" class="copy-ref-btn" data-url="<?= e($refUrl) ?>">
                                📋 Copy
                            </button>
                        </div>

                        <!-- 4 Mobile Metric Chips -->
                        <div class="ref-m-chips-grid">
                            <div class="ref-m-chip">
                                <span class="chip-label">Visits</span>
                                <span class="chip-value"><?= number_format($ref['visits_count']) ?></span>
                            </div>
                            <div class="ref-m-chip">
                                <span class="chip-label">Sold</span>
                                <span class="chip-value <?= $ref['tickets_sold'] > 0 ? 'text-green' : '' ?>"><?= number_format($ref['tickets_sold']) ?></span>
                            </div>
                            <div class="ref-m-chip">
                                <span class="chip-label">Revenue</span>
                                <span class="chip-value text-brand"><?= format_price($ref['revenue_generated']) ?></span>
                            </div>
                            <div class="ref-m-chip">
                                <span class="chip-label">Commission</span>
                                <span class="chip-value"><?= $ref['commission_rate'] > 0 ? number_format($ref['commission_rate'], 1) . '%' : '—' ?></span>
                            </div>
                        </div>

                        <!-- Mobile Action Buttons -->
                        <div class="ref-m-actions">
                            <form method="POST" action="{{ route('host/event/referrals/' . $event->id . '/toggle/' . $ref['id']) }}" style="flex: 1; margin: 0;">
                                <?= csrf_field() ?>
                                <button type="submit" class="checkout-btn secondary ref-m-btn">
                                    <?= $isActive ? '⏸️ Pause Link' : '▶️ Activate Link' ?>
                                </button>
                            </form>
                            <form method="POST" action="{{ route('host/event/referrals/' . $event->id . '/delete/' . $ref['id']) }}" style="flex: 0 0 auto; margin: 0;" onsubmit="return confirm('Are you sure you want to delete this referral link? Past transaction records will be preserved.');">
                                <?= csrf_field() ?>
                                <button type="submit" class="checkout-btn danger ref-m-btn-del" title="Delete link">
                                    🗑️ Delete
                                </button>
                            </form>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</div>

<!-- Create Referral Modal -->
<div id="referral-modal-backdrop" class="ref-modal-backdrop">
    <div class="ref-modal-card">
        
        <div class="ref-modal-header">
            <div>
                <h3 class="ref-modal-title">Create Referral Link</h3>
                <p class="ref-modal-subtitle">Generate a tracked link for a promoter or ambassador</p>
            </div>
            <button type="button" id="close-referral-modal-btn" class="ref-modal-close" aria-label="Close">&times;</button>
        </div>

        <form id="create-referral-form" method="POST" action="{{ route('host/event/referrals/' . $event->id . '/create') }}" novalidate>
            <?= csrf_field() ?>

            <div class="ref-form-group">
                <label for="modal-promoter-name">
                    Promoter / Influencer Name <span style="color:#ef4444;">*</span>
                </label>
                <input type="text" name="promoter_name" id="modal-promoter-name" required placeholder="e.g. John Doe, Sarah Tech" class="ref-input" />
            </div>

            <div class="ref-form-group">
                <label for="modal-referral-code">
                    Custom Referral Code (Optional)
                </label>
                <input type="text" name="referral_code" id="modal-referral-code" placeholder="e.g. johnvip, sarah26 (leave blank to auto-generate)" class="ref-input" />
                <small class="ref-input-help">
                    Letters, numbers, hyphens, and underscores only.
                </small>
            </div>

            <div class="ref-form-group">
                <label for="modal-commission-rate">
                    Agreed Commission Rate (%) (Optional)
                </label>
                <input type="number" name="commission_rate" id="modal-commission-rate" value="0.00" min="0" max="100" step="0.5" placeholder="0.00" class="ref-input" />
                <small class="ref-input-help">
                    For your internal promoter payout reporting.
                </small>
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
                    Create Referral Link
                </button>
                <button type="button" id="cancel-referral-modal-btn" class="checkout-btn secondary ref-cancel-btn">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<style>
/* ═══════════════════════════════════════════════════════════════════
   Host Referral Hub Responsive Styles
   ═══════════════════════════════════════════════════════════════════ */
.referrals-page-wrapper {
    width: 100%;
    max-width: 1200px;
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
}

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
    padding: 0.25rem 0.6rem;
    border-radius: 999px;
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

.ref-meta-dot {
    opacity: 0.5;
}

/* Stats Grid */
.ref-stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
    gap: 1rem;
    margin-bottom: 1.75rem;
}

.ref-stats-grid .summary-card {
    border-radius: 16px;
    padding: 1.25rem;
}

.ref-stats-grid .highlight-green {
    border-color: rgba(16, 185, 129, 0.25);
    background: rgba(16, 185, 129, 0.03);
}

.ref-stats-grid .highlight-green .card-label,
.ref-stats-grid .highlight-green .card-value {
    color: #10b981;
}

.ref-stats-grid .highlight-brand {
    border-color: rgba(var(--brand), 0.25);
    background: rgba(var(--brand), 0.03);
}

.ref-stats-grid .highlight-brand .card-label,
.ref-stats-grid .highlight-brand .card-value {
    color: rgb(var(--brand));
}

/* Top Promoter Spotlight */
.top-promoter-banner {
    margin-bottom: 1.5rem;
    padding: 1rem 1.25rem;
    border-radius: 16px;
    background: linear-gradient(135deg, rgba(245, 158, 11, 0.1), rgba(245, 158, 11, 0.03));
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
    gap: 1.25rem;
    align-items: center;
    font-size: 0.9rem;
}

.promoter-metric-item strong {
    font-size: 1.05rem;
}

.promoter-metric-item span {
    color: rgb(var(--muted));
    font-size: 0.82rem;
}

.metric-revenue {
    color: #10b981;
}

/* Table Section */
.ref-content-card {
    padding: 1.5rem;
    border-radius: 20px;
}

.ref-card-header {
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
    max-width: 420px;
    margin: 0 auto 1.5rem;
    line-height: 1.5;
}

.ref-empty-state .open-referral-modal-trigger {
    height: 42px;
    padding: 0 1.5rem;
    font-size: 0.92rem;
    font-weight: 700;
    border-radius: 12px;
    border: none;
    cursor: pointer;
}

/* Promoter Cells */
.promoter-cell {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.promoter-avatar {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    background: linear-gradient(135deg, rgba(var(--brand), 0.2), rgba(var(--brand), 0.05));
    border: 1px solid rgba(var(--brand), 0.25);
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 800;
    font-size: 0.85rem;
    color: rgb(var(--brand));
    flex-shrink: 0;
}

.promoter-cell-name {
    font-weight: 700;
    font-size: 0.92rem;
}

.promoter-cell-code {
    font-size: 0.75rem;
    color: rgb(var(--muted));
    font-family: monospace;
}

/* Link Box & Copy Button */
.ref-link-box {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    max-width: 260px;
}

.ref-link-input {
    font-size: 0.8rem;
    padding: 0.45rem 0.65rem;
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

.copy-ref-btn {
    padding: 0.45rem 0.75rem;
    font-size: 0.78rem;
    font-weight: 700;
    border-radius: 8px;
    border: 1px solid rgba(var(--brand), 0.3);
    background: rgba(var(--brand), 0.08);
    color: rgb(var(--brand));
    cursor: pointer;
    white-space: nowrap;
    transition: all 0.2s ease;
    flex-shrink: 0;
}

.copy-ref-btn:hover {
    background: rgb(var(--brand));
    color: #ffffff;
}

.copy-ref-btn.copied {
    background: #10b981 !important;
    border-color: #10b981 !important;
    color: #ffffff !important;
}

.tickets-sold-pill {
    font-weight: 800;
    font-size: 0.95rem;
}

.tickets-sold-pill.has-sales {
    color: #10b981;
}

.commission-badge {
    background: rgba(245, 158, 11, 0.12);
    color: #d97706;
    padding: 0.25rem 0.55rem;
    border-radius: 6px;
    font-weight: 700;
    font-size: 0.78rem;
}

.ref-status-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    font-size: 0.78rem;
    font-weight: 700;
    padding: 0.25rem 0.6rem;
    border-radius: 20px;
    white-space: nowrap;
}

.ref-status-badge.active {
    background: rgba(16, 185, 129, 0.12);
    color: #10b981;
}

.ref-status-badge.inactive {
    background: rgba(100, 116, 139, 0.15);
    color: rgb(100, 116, 139);
}

.status-dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
}

.status-dot.green { background: #10b981; }
.status-dot.gray { background: rgb(100, 116, 139); }

.ref-actions-group {
    display: inline-flex;
    gap: 0.4rem;
    align-items: center;
}

.ref-actions-group .admin-action-btn {
    padding: 0.35rem 0.65rem;
    font-size: 0.75rem;
    border-radius: 8px;
    cursor: pointer;
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

.ref-mobile-card.card-active {
    border-left: 4px solid #10b981;
}

.ref-mobile-card.card-inactive {
    border-left: 4px solid rgb(100, 116, 139);
    opacity: 0.85;
}

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

.ref-m-name {
    margin: 0;
    font-size: 0.98rem;
    font-weight: 700;
}

.ref-m-link-row {
    display: flex;
    gap: 0.5rem;
    align-items: center;
}

.ref-m-link-row .ref-link-input {
    font-size: 0.85rem;
    padding: 0.55rem 0.75rem;
    border-radius: 10px;
}

.ref-m-link-row .copy-ref-btn {
    padding: 0.55rem 1rem;
    font-size: 0.85rem;
    border-radius: 10px;
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

.text-green { color: #10b981 !important; }
.text-brand { color: rgb(var(--brand)) !important; }

.ref-m-actions {
    display: flex;
    gap: 0.6rem;
    align-items: center;
    margin-top: 0.25rem;
}

.ref-m-btn {
    width: 100%;
    height: 38px;
    font-size: 0.85rem;
    font-weight: 600;
    border-radius: 10px;
    text-align: center;
    justify-content: center;
    display: inline-flex;
    align-items: center;
}

.ref-m-btn-del {
    height: 38px;
    padding: 0 0.9rem;
    font-size: 0.85rem;
    font-weight: 600;
    border-radius: 10px;
    background: rgba(239, 68, 68, 0.1);
    color: #ef4444;
    border: 1px solid rgba(239, 68, 68, 0.3);
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
}

/* Modal Styles */
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
    max-width: 500px;
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

.ref-modal-title {
    margin: 0;
    font-size: 1.25rem;
    font-weight: 800;
}

.ref-modal-subtitle {
    margin: 0.25rem 0 0;
    font-size: 0.85rem;
    color: rgb(var(--muted));
}

.ref-modal-close {
    background: none;
    border: none;
    font-size: 1.5rem;
    line-height: 1;
    cursor: pointer;
    color: rgb(var(--muted));
    padding: 0;
}

.ref-form-group {
    margin-bottom: 1.15rem;
}

.ref-form-group label {
    display: block;
    font-size: 0.92rem;
    font-weight: 600;
    margin-bottom: 0.4rem;
}

.ref-input {
    width: 100%;
    box-sizing: border-box;
    padding: 0.75rem 1rem;
    border-radius: 12px;
    font-size: 1rem; /* iOS zoom prevention */
    border: 1px solid rgba(var(--muted), 0.2);
    background: rgb(var(--card));
    color: rgb(var(--text));
}

.ref-input-help {
    display: block;
    margin-top: 0.3rem;
    font-size: 0.8rem;
    color: rgb(var(--muted));
    font-weight: 400;
}

.ref-preview-box {
    margin-bottom: 1.5rem;
    padding: 0.85rem 1rem;
    border-radius: 12px;
    background: rgba(var(--muted), 0.06);
    border: 1px solid rgba(var(--muted), 0.15);
}

.ref-preview-label {
    font-size: 0.75rem;
    text-transform: uppercase;
    font-weight: 700;
    color: rgb(var(--muted));
    margin-bottom: 0.25rem;
}

.ref-preview-url {
    font-size: 0.82rem;
    color: rgb(var(--brand));
    word-break: break-all;
    font-family: monospace;
    font-weight: 600;
}

.ref-modal-actions {
    display: flex;
    gap: 0.75rem;
    margin-top: 1.5rem;
}

.ref-submit-btn {
    flex: 1;
    height: 46px;
    font-size: 0.95rem;
    font-weight: 700;
    border-radius: 12px;
    border: none;
    cursor: pointer;
}

.ref-cancel-btn {
    flex: 0 0 auto;
    height: 46px;
    padding: 0 1.25rem;
    font-size: 0.92rem;
    font-weight: 600;
    border-radius: 12px;
    cursor: pointer;
}

@keyframes modalScale {
    from { transform: scale(0.92); opacity: 0; }
    to { transform: scale(1); opacity: 1; }
}

/* ═══════════════════════════════════════════════════════════════════
   Responsive Breakpoints for Tablets & Mobile Devices
   ═══════════════════════════════════════════════════════════════════ */
@media (max-width: 850px) {
    .ref-desktop-table-wrapper {
        display: none !important;
    }
    .ref-mobile-cards-wrapper {
        display: flex !important;
    }
    .ref-stats-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 0.85rem;
    }
    .top-promoter-banner {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.85rem;
    }
    .top-promoter-metrics {
        width: 100%;
        justify-content: space-between;
        border-top: 1px dashed rgba(245, 158, 11, 0.25);
        padding-top: 0.75rem;
    }
}

@media (max-width: 600px) {
    .ref-top-bar {
        flex-direction: column;
        align-items: stretch;
        gap: 0.85rem;
    }
    .ref-top-actions {
        display: grid;
        grid-template-columns: 1fr 1.3fr;
        width: 100%;
    }
    .ref-top-actions .edit-event-btn,
    .ref-top-actions .add-ref-btn {
        width: 100%;
        justify-content: center;
        text-align: center;
        box-sizing: border-box;
    }
    .ref-stats-grid {
        grid-template-columns: 1fr;
    }
    .ref-content-card {
        padding: 1.15rem 1rem;
        border-radius: 16px;
    }
    .ref-m-chips-grid {
        grid-template-columns: 1fr 1fr;
    }
    .ref-modal-card {
        padding: 1.5rem 1.25rem;
        border-radius: 20px;
    }
    .ref-modal-actions {
        flex-direction: column;
    }
    .ref-cancel-btn {
        width: 100%;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('referral-modal-backdrop');
    const openBtn = document.getElementById('open-referral-modal-btn');
    const closeBtn = document.getElementById('close-referral-modal-btn');
    const cancelBtn = document.getElementById('cancel-referral-modal-btn');
    const triggers = document.querySelectorAll('.open-referral-modal-trigger');
    const nameInput = document.getElementById('modal-promoter-name');
    const codeInput = document.getElementById('modal-referral-code');
    const previewTag = document.getElementById('preview-code-tag');
    const form = document.getElementById('create-referral-form');

    function openModal() {
        if (!modal) return;
        modal.style.display = 'flex';
        if (nameInput) nameInput.focus();
    }

    function closeModal() {
        if (!modal) return;
        modal.style.display = 'none';
        if (form) form.reset();
        if (previewTag) previewTag.textContent = 'promoter';
    }

    if (openBtn) openBtn.addEventListener('click', openModal);
    triggers.forEach(btn => btn.addEventListener('click', openModal));
    if (closeBtn) closeBtn.addEventListener('click', closeModal);
    if (cancelBtn) cancelBtn.addEventListener('click', closeModal);

    if (modal) {
        modal.addEventListener('click', function (e) {
            if (e.target === modal) closeModal();
        });
    }

    // Live link preview updater
    function updatePreview() {
        if (!previewTag) return;
        const custom = codeInput ? codeInput.value.trim().toLowerCase().replace(/[^a-z0-9_\-]+/g, '') : '';
        if (custom) {
            previewTag.textContent = custom;
        } else if (nameInput && nameInput.value.trim()) {
            const raw = nameInput.value.trim().toLowerCase().replace(/[^a-z0-9]+/g, '').substring(0, 10);
            previewTag.textContent = raw || 'promoter';
        } else {
            previewTag.textContent = 'promoter';
        }
    }

    if (nameInput) nameInput.addEventListener('input', updatePreview);
    if (codeInput) codeInput.addEventListener('input', updatePreview);

    // Form submit validation & loading state
    if (form) {
        form.addEventListener('submit', function (e) {
            if (!nameInput || !nameInput.value.trim()) {
                e.preventDefault();
                nameInput.classList.add('invalid');
                nameInput.focus();
                return;
            }
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="btn-spinner"></span> Creating Link...';
            }
        });
    }

    // Universal Robust Clipboard Copy Function (supports HTTP / Localhost & HTTPS)
    function copyTextToClipboard(text, inputEl) {
        if (navigator.clipboard && window.isSecureContext) {
            return navigator.clipboard.writeText(text);
        }
        return new Promise((resolve, reject) => {
            try {
                if (inputEl) {
                    inputEl.focus();
                    inputEl.select();
                    inputEl.setSelectionRange(0, 99999);
                    const successful = document.execCommand('copy');
                    if (successful) {
                        resolve();
                        return;
                    }
                }
                const tempArea = document.createElement('textarea');
                tempArea.value = text;
                tempArea.style.position = 'fixed';
                tempArea.style.left = '-9999px';
                tempArea.style.top = '-9999px';
                tempArea.style.opacity = '0';
                document.body.appendChild(tempArea);
                tempArea.focus();
                tempArea.select();
                tempArea.setSelectionRange(0, 99999);
                const successful = document.execCommand('copy');
                document.body.removeChild(tempArea);
                if (successful) {
                    resolve();
                } else {
                    reject(new Error('execCommand copy failed'));
                }
            } catch (err) {
                reject(err);
            }
        });
    }

    // 1-Click Clipboard Copy Handler
    document.querySelectorAll('.copy-ref-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const url = this.getAttribute('data-url');
            if (!url) return;

            const inputEl = this.closest('div')?.querySelector('.ref-link-input');

            copyTextToClipboard(url, inputEl).then(() => {
                const originalHtml = this.innerHTML;
                this.innerHTML = '✓ Copied!';
                this.classList.add('copied');
                setTimeout(() => {
                    this.innerHTML = originalHtml;
                    this.classList.remove('copied');
                }, 2000);
            }).catch(err => {
                console.warn('Clipboard write failed, using prompt fallback', err);
                if (inputEl) {
                    inputEl.focus();
                    inputEl.select();
                }
                prompt('Copy this referral link:', url);
            });
        });
    });

    // Auto-select text on input click
    document.querySelectorAll('.ref-link-input').forEach(input => {
        input.addEventListener('click', function () {
            this.select();
        });
    });
});
</script>
@endsection
