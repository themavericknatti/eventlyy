<?php

/**
 * Host Promo Codes & Discounts View
 *
 * Allows event organizers to create, manage, and inspect coupon discount codes.
 *
 * @package Evently
 * @subpackage Views/Host
 * @author Celio Natti
 * @version 2.0.0
 */

$siteName = site_setting('site_title', 'Eventlyy');
?>

@extends('layouts/host')

@section('title')
Promo Codes &amp; Discounts - <?= e($siteName) ?> Host
@endsection

@section('content')
<style>
/* ═══════════════════════════════════════════════════════════
   Host Promo Codes — Responsive Polish & Layout Refactor
   ═══════════════════════════════════════════════════════════ */

/* Top Navigation Bar */
.promo-top-nav {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1rem;
    margin-bottom: 1.25rem;
}

.promo-back-link {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    font-weight: 700;
    font-size: 0.88rem;
    color: rgb(var(--text));
    text-decoration: none;
    transition: color 0.2s ease;
}

.promo-back-link:hover {
    color: rgb(var(--brand));
}

/* Page Hero Card */
.promo-hero-card {
    padding: 1.5rem 1.75rem;
    background: rgb(var(--card));
    border: 1px solid rgba(var(--muted), 0.16);
    border-radius: 20px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
    margin-bottom: 1.75rem;
}

.promo-hero-card h1 {
    margin: 0.35rem 0 0.45rem;
    font-size: clamp(1.5rem, 2.5vw, 2.1rem);
    font-weight: 900;
}

/* Page Grid: Form on left, List on right */
.promo-page-grid {
    display: grid;
    grid-template-columns: 340px minmax(0, 1fr);
    gap: 1.5rem;
    align-items: start;
    margin-bottom: 2rem;
    min-width: 0;
}

/* Form Card */
.promo-form-section {
    background: rgb(var(--card));
    border: 1px solid rgba(var(--muted), 0.15);
    border-radius: 22px;
    padding: 1.5rem;
    box-shadow: 0 4px 18px rgba(0, 0, 0, 0.03);
}

.promo-form-section h3 {
    margin: 0 0 1.25rem;
    font-size: 1.2rem;
    font-weight: 800;
    color: rgb(var(--text));
    display: flex;
    align-items: center;
    gap: 0.45rem;
}

.promo-field-group {
    display: flex;
    flex-direction: column;
    gap: 0.35rem;
    margin-bottom: 1rem;
}

.promo-label {
    font-size: 0.8rem;
    font-weight: 700;
    color: rgb(var(--muted));
    text-transform: uppercase;
    letter-spacing: 0.03em;
}

.promo-input,
.promo-select {
    width: 100%;
    padding: 0.65rem 0.95rem;
    border-radius: 12px;
    border: 1px solid rgba(var(--muted), 0.2);
    background: rgba(var(--card), 0.6);
    color: rgb(var(--text));
    font-size: 0.9rem;
    font-family: inherit;
    box-sizing: border-box;
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
}

.promo-input:focus,
.promo-select:focus {
    outline: none;
    border-color: rgb(var(--brand));
    box-shadow: 0 0 0 3px rgba(var(--brand), 0.15);
}

.promo-form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.75rem;
    margin-bottom: 1rem;
}

.promo-submit-btn {
    width: 100%;
    height: 46px;
    border-radius: 12px;
    background: rgb(var(--brand));
    color: #ffffff !important;
    font-size: 0.92rem;
    font-weight: 800;
    border: none;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.45rem;
    transition: all 0.2s ease;
    box-shadow: 0 4px 14px rgba(var(--brand), 0.3);
    margin-top: 0.5rem;
    font-family: inherit;
}

.promo-submit-btn:hover {
    filter: brightness(1.08);
    transform: translateY(-1px);
}

/* List Card */
.promo-list-section {
    background: rgb(var(--card));
    border: 1px solid rgba(var(--muted), 0.15);
    border-radius: 22px;
    padding: 1.5rem;
    box-shadow: 0 4px 18px rgba(0, 0, 0, 0.03);
    min-width: 0;
    max-width: 100%;
    overflow: hidden;
}

.promo-list-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.25rem;
    flex-wrap: wrap;
    gap: 0.5rem;
}

.promo-list-header h3 {
    margin: 0;
    font-size: 1.2rem;
    font-weight: 800;
    color: rgb(var(--text));
}

/* Code Pills */
.promo-code-pill {
    background: rgba(var(--brand), 0.1);
    color: rgb(var(--brand));
    border: 1px solid rgba(var(--brand), 0.25);
    padding: 0.25rem 0.65rem;
    border-radius: 8px;
    font-family: monospace;
    font-size: 0.88rem;
    font-weight: 800;
    letter-spacing: 0.5px;
    display: inline-block;
    white-space: nowrap;
}

.promo-discount-badge {
    background: rgba(16, 185, 129, 0.12);
    color: #059669;
    border: 1px solid rgba(16, 185, 129, 0.25);
    padding: 0.2rem 0.6rem;
    border-radius: 20px;
    font-weight: 800;
    font-size: 0.8rem;
    display: inline-block;
    white-space: nowrap;
}

/* Table */
.promo-table-wrap {
    width: 100%;
    max-width: 100%;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    border-radius: 12px;
}

.promo-table-wrap::-webkit-scrollbar {
    height: 6px;
}

.promo-table-wrap::-webkit-scrollbar-track {
    background: rgba(var(--muted), 0.06);
    border-radius: 4px;
}

.promo-table-wrap::-webkit-scrollbar-thumb {
    background: rgba(var(--muted), 0.24);
    border-radius: 4px;
}

.promo-table {
    width: 100%;
    min-width: 620px;
    border-collapse: separate;
    border-spacing: 0;
    font-size: 0.88rem;
    text-align: left;
}

.promo-table th {
    background: rgba(var(--muted), 0.05);
    color: rgb(var(--muted));
    font-size: 0.76rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    padding: 0.85rem 1rem;
    border-bottom: 1px solid rgba(var(--muted), 0.15);
    white-space: nowrap;
}

.promo-table td {
    padding: 0.95rem 1rem;
    border-bottom: 1px solid rgba(var(--muted), 0.08);
    color: rgb(var(--text));
    vertical-align: middle;
}

.promo-table tr:hover td {
    background: rgba(var(--muted), 0.03);
}

.promo-cell-event {
    max-width: 180px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    font-size: 0.86rem;
    font-weight: 600;
    color: rgb(var(--text));
}

/* Sticky Action Column so Delete button is always visible */
.promo-table th.promo-col-action,
.promo-table td.promo-col-action {
    text-align: right;
    width: 105px;
    min-width: 105px;
    white-space: nowrap;
    position: sticky;
    right: 0;
    background: rgb(var(--card));
    z-index: 2;
    box-shadow: -4px 0 8px -2px rgba(0, 0, 0, 0.05);
}

.promo-table th.promo-col-action {
    background: rgba(var(--card), 0.98);
}

.promo-table tr:hover td.promo-col-action {
    background: rgb(var(--card));
}

.promo-delete-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.35rem;
    padding: 0.42rem 0.8rem;
    font-size: 0.8rem;
    font-weight: 700;
    border-radius: 8px;
    background: rgba(239, 68, 68, 0.1);
    color: #ef4444 !important;
    border: 1px solid rgba(239, 68, 68, 0.28);
    cursor: pointer;
    transition: all 0.2s ease;
    font-family: inherit;
    white-space: nowrap;
    visibility: visible !important;
    opacity: 1 !important;
    line-height: 1.2;
}

.promo-delete-btn:hover {
    background: rgba(239, 68, 68, 0.2);
    border-color: rgba(239, 68, 68, 0.45);
    color: #dc2626 !important;
    transform: translateY(-1px);
}

.promo-delete-btn--mobile {
    width: 100%;
    justify-content: center;
    padding: 0.6rem;
    font-size: 0.86rem;
    border-radius: 10px;
}

/* Mobile Card View */
.promo-cards-list {
    display: none;
}

.promo-card {
    background: rgba(var(--card), 0.7);
    border: 1px solid rgba(var(--muted), 0.14);
    border-radius: 16px;
    padding: 1.15rem;
    display: flex;
    flex-direction: column;
    gap: 0.65rem;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.02);
}

.promo-card-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.5rem;
    flex-wrap: wrap;
    padding-bottom: 0.5rem;
    border-bottom: 1px solid rgba(var(--muted), 0.1);
}

.promo-card-detail {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.25rem 0;
    font-size: 0.85rem;
}

.promo-card-label {
    font-size: 0.78rem;
    color: rgb(var(--muted));
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.4px;
}

.promo-card-value {
    font-size: 0.88rem;
    font-weight: 600;
    color: rgb(var(--text));
}

.promo-card-footer {
    margin-top: 0.35rem;
    padding-top: 0.65rem;
    border-top: 1px solid rgba(var(--muted), 0.1);
}

/* Empty State */
.promo-empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 0.35rem;
    padding: 3rem 1rem;
    text-align: center;
    color: rgb(var(--muted));
}

/* ══ Large Tablet Breakpoint (681px - 1024px) ══ */
@media (max-width: 1024px) and (min-width: 681px) {
    .promo-page-grid {
        grid-template-columns: 1fr;
        gap: 1.5rem;
    }
}

/* ══ Mobile Breakpoint (<= 680px) ══ */
@media (max-width: 680px) {
    .promo-top-nav {
        flex-direction: column;
        align-items: stretch;
    }

    .promo-hero-card {
        padding: 1.15rem;
    }

    .promo-page-grid {
        grid-template-columns: 1fr;
        gap: 1.25rem;
    }

    .promo-form-section,
    .promo-list-section {
        padding: 1.15rem;
        border-radius: 18px;
    }

    .promo-form-row {
        grid-template-columns: 1fr;
        gap: 1rem;
    }

    /* Switch from table to cards */
    .promo-table-wrap {
        display: none !important;
    }

    .promo-cards-list {
        display: flex;
        flex-direction: column;
        gap: 0.85rem;
    }
}
</style>

<!-- Top Navigation Bar -->
<div class="promo-top-nav">
    <a href="{{ route('host/dashboard') }}" class="promo-back-link">
        ← Back to Dashboard
    </a>
</div>

<!-- Page Hero Card -->
<section class="promo-hero-card">
    <span class="panel-badge" style="background:rgba(var(--brand), 0.12); color:rgb(var(--brand)); border:1px solid rgba(var(--brand), 0.25);">🏷️ Marketing &amp; Sales</span>
    <h1>Promo Codes &amp; Discounts</h1>
    <p class="lede" style="margin:0; font-size:0.92rem; color:rgb(var(--muted));">
        Create custom discount coupons, limited-time percentage offers, and fixed rebates for your event tickets.
    </p>
</section>

<div class="promo-page-grid">
    <!-- Create Promo Code Form -->
    <div class="promo-form-section">
        <h3><span>✨</span> Create New Coupon</h3>
        <form method="POST" action="{{ route('host/promo-codes/create') }}">
            @csrf
            <div class="promo-field-group">
                <label class="promo-label">Coupon Code</label>
                <input type="text" name="code" class="promo-input" placeholder="e.g. EARLYBIRD10" required style="text-transform:uppercase; font-family:monospace; font-weight:700; letter-spacing:1px;" autocomplete="off">
            </div>

            <div class="promo-field-group">
                <label class="promo-label">Apply to Event</label>
                <select name="event_id" class="promo-select">
                    <option value=""><?= !empty($isAdmin) ? 'All Platform Events (Global)' : 'All My Events (Global)' ?></option>
                    <?php foreach ($events as $e): ?>
                        <?php 
                            $isMine = !empty($currentUserId) && ($e['organizer_id'] ?? '') == $currentUserId;
                            $label = e($e['name']);
                            if (!empty($isAdmin)) {
                                $label .= $isMine ? ' 👑 (My Event)' : ' — Host: ' . e($e['organizer_name'] ?? 'Organizer');
                            }
                        ?>
                        <option value="<?= $e['id'] ?>"><?= $label ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="promo-form-row">
                <div class="promo-field-group" style="margin-bottom:0;">
                    <label class="promo-label">Discount Type</label>
                    <select name="discount_type" class="promo-select">
                        <option value="percentage">Percentage (%)</option>
                        <option value="fixed">Fixed Amount (<?= e(get_currency_symbol()) ?>)</option>
                    </select>
                </div>
                <div class="promo-field-group" style="margin-bottom:0;">
                    <label class="promo-label">Discount Value</label>
                    <input type="number" step="0.01" name="discount_value" class="promo-input" placeholder="10" required>
                </div>
            </div>

            <div class="promo-form-row">
                <div class="promo-field-group" style="margin-bottom:0;">
                    <label class="promo-label">Max Uses</label>
                    <input type="number" name="max_uses" class="promo-input" value="100" min="1">
                </div>
                <div class="promo-field-group" style="margin-bottom:0;">
                    <label class="promo-label">Expiry Date (Optional)</label>
                    <input type="date" name="expires_at" class="promo-input">
                </div>
            </div>

            <button type="submit" class="promo-submit-btn">
                <span>➕</span> Create Promo Code →
            </button>
        </form>
    </div>

    <!-- Active Promo Codes List -->
    <div class="promo-list-section">
        <div class="promo-list-header">
            <h3>Existing Promo Codes (<?= count($promoCodes ?? []) ?>)</h3>
        </div>

        <?php if (empty($promoCodes)): ?>
            <div class="promo-empty-state">
                <span style="font-size:2.8rem; display:block; margin-bottom:0.5rem;">🏷️</span>
                <p style="font-weight:700; color:rgb(var(--text)); font-size:1.05rem; margin:0 0 0.25rem;">No promo codes created yet</p>
                <p style="font-size:0.86rem; color:rgb(var(--muted)); margin:0;">Create your first coupon using the form on the left to start offering ticket discounts.</p>
            </div>
        <?php else: ?>
            <!-- Desktop Table View -->
            <div class="promo-table-wrap">
                <table class="promo-table">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <?php if (!empty($isAdmin)): ?>
                                <th>Host / Organizer</th>
                            <?php endif; ?>
                            <th>Event Attribution</th>
                            <th>Discount</th>
                            <th>Used / Max</th>
                            <th>Expiry</th>
                            <th class="promo-col-action">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($promoCodes as $p): ?>
                            <?php 
                                $isMine = !empty($currentUserId) && (($p['host_id'] ?? '') == $currentUserId || ($p['organizer_id'] ?? '') == $currentUserId);
                                $isPercentage = ($p['discount_type'] === 'percentage');
                                $discountDisplay = $isPercentage ? ((float)$p['discount_value'] . '% OFF') : (format_price($p['discount_value']) . ' OFF');
                            ?>
                            <tr>
                                <td>
                                    <strong class="promo-code-pill"><?= e($p['code']) ?></strong>
                                </td>
                                <?php if (!empty($isAdmin)): ?>
                                    <td style="font-size:0.84rem; white-space:nowrap;">
                                        <?php if ($isMine): ?>
                                            <span style="display:inline-flex; align-items:center; gap:0.25rem; font-weight:800; color:#10b981;">👑 You</span>
                                        <?php else: ?>
                                            <span style="display:inline-flex; align-items:center; gap:0.25rem; font-weight:600; color:rgb(var(--text));">👤 <?= e($p['host_name'] ?? 'Organizer') ?></span>
                                        <?php endif; ?>
                                    </td>
                                <?php endif; ?>
                                <td class="promo-cell-event" title="<?= e($p['event_name'] ?: 'All Events (Global)') ?>">
                                    <?= e($p['event_name'] ?: 'All Events (Global)') ?>
                                </td>
                                <td style="white-space:nowrap;">
                                    <span class="promo-discount-badge">
                                        <?= e($discountDisplay) ?>
                                    </span>
                                </td>
                                <td style="white-space:nowrap;">
                                    <strong style="color:rgb(var(--text));"><?= (int)$p['used_count'] ?></strong>
                                    <span style="font-size:0.78rem; color:rgb(var(--muted));">/ <?= (int)$p['max_uses'] ?></span>
                                </td>
                                <td style="font-size:0.82rem; color:rgb(var(--muted)); white-space:nowrap;">
                                    <?= $p['expires_at'] ? date('M d, Y', strtotime($p['expires_at'])) : 'No Expiry' ?>
                                </td>
                                <td class="promo-col-action">
                                    <form method="POST" action="{{ route('host/promo-codes/delete') }}" onsubmit="return confirm('Delete this promo code? Attendees will no longer be able to apply this discount.');" style="margin:0; display:inline-block;">
                                        @csrf
                                        <input type="hidden" name="id" value="<?= $p['id'] ?>">
                                        <button type="submit" class="promo-delete-btn" title="Delete promo code">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                                            <span>Delete</span>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Mobile Card List View (<= 680px) -->
            <div class="promo-cards-list">
                <?php foreach ($promoCodes as $p): ?>
                    <?php 
                        $isPercentage = ($p['discount_type'] === 'percentage');
                        $discountDisplay = $isPercentage ? ((float)$p['discount_value'] . '% OFF') : (format_price($p['discount_value']) . ' OFF');
                    ?>
                    <article class="promo-card">
                        <div class="promo-card-header">
                            <strong class="promo-code-pill"><?= e($p['code']) ?></strong>
                            <span class="promo-discount-badge"><?= e($discountDisplay) ?></span>
                        </div>
                        <div class="promo-card-detail">
                            <span class="promo-card-label">Event</span>
                            <span class="promo-card-value"><?= e($p['event_name'] ?: 'All Events (Global)') ?></span>
                        </div>
                        <div class="promo-card-detail">
                            <span class="promo-card-label">Usage Count</span>
                            <span class="promo-card-value"><?= (int)$p['used_count'] ?> / <?= (int)$p['max_uses'] ?> used</span>
                        </div>
                        <div class="promo-card-detail">
                            <span class="promo-card-label">Expires</span>
                            <span class="promo-card-value"><?= $p['expires_at'] ? date('M d, Y', strtotime($p['expires_at'])) : 'No Expiry' ?></span>
                        </div>
                        <div class="promo-card-footer">
                            <form method="POST" action="{{ route('host/promo-codes/delete') }}" onsubmit="return confirm('Delete this promo code? Attendees will no longer be able to apply this discount.');" style="margin:0;">
                                @csrf
                                <input type="hidden" name="id" value="<?= $p['id'] ?>">
                                <button type="submit" class="promo-delete-btn promo-delete-btn--mobile">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                                    <span>Delete Promo Code</span>
                                </button>
                            </form>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
@endsection
