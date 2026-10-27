<?php

/**
 * Admin Payouts
 *
 * This file contains the HTML markup for the admin payouts page of the application.
 *
 * @package Evently
 * @subpackage Views/Admin
 * @author Celio Natti
 * @license MIT License
 * @version 2.0.0
 */

// Initialize default variables if not provided
$pending_payouts = $pending_payouts ?? 0;
$completed_payouts = $completed_payouts ?? 0;
$pending_count = $pending_count ?? 0;
$completed_count = $completed_count ?? 0;
$hosts_ready = $hosts_ready ?? 0;
$hosts_flagged = $hosts_flagged ?? 0;
$total_payouts_count = $total_payouts_count ?? 0;
$payouts = $payouts ?? [];
$search = $search ?? '';
$status = $status ?? '';
$type = $type ?? '';
$page = $page ?? 1;
$totalPages = $totalPages ?? 1;
$siteName = site_setting('site_title', 'Eventlyy');

?>

@extends('layouts/admin')

@section('title')
Admin Payouts - Manage Your <?= e($siteName) ?> Payouts
@endsection

@section('content')
<style>
/* ═══════════════════════════════════════════════════════════
   Admin Payouts — Streamlined Responsive Layout
   ═══════════════════════════════════════════════════════════ */

/* Stats Grid */
.admin-users-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.au-stat-card {
    background: rgb(var(--card));
    border: 1px solid rgba(var(--muted), 0.14);
    border-radius: 18px;
    padding: 1.2rem 1.35rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.03);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.au-stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.06);
}

/* Toolbar */
.admin-users-toolbar {
    margin-bottom: 1.5rem;
}

.au-toolbar-form {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    flex-wrap: wrap;
    background: rgb(var(--card));
    border: 1px solid rgba(var(--muted), 0.14);
    border-radius: 18px;
    padding: 0.85rem 1.15rem;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.03);
}

.au-search-wrap {
    flex: 1;
    min-width: 240px;
    position: relative;
    display: flex;
    align-items: center;
}

.au-search-wrap input[type="search"] {
    width: 100%;
    padding: 0.55rem 0.85rem 0.55rem 2.3rem;
    border-radius: 11px;
    border: 1px solid rgba(var(--muted), 0.18);
    background: rgba(var(--card), 0.6);
    color: rgb(var(--text));
    font-size: 0.88rem;
    font-family: inherit;
}

.au-search-icon {
    position: absolute;
    left: 0.75rem;
    color: rgb(var(--muted));
    pointer-events: none;
}

.au-filter-group {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.au-filter-group select {
    padding: 0.55rem 0.85rem;
    border-radius: 11px;
    border: 1px solid rgba(var(--muted), 0.18);
    background: rgb(var(--card));
    color: rgb(var(--text));
    font-size: 0.85rem;
    font-family: inherit;
    cursor: pointer;
}

/* Table Section Container */
.admin-users-table-section {
    background: rgb(var(--card));
    border: 1px solid rgba(var(--muted), 0.14);
    border-radius: 22px;
    padding: 1.5rem;
    box-shadow: 0 2px 14px rgba(0, 0, 0, 0.03);
    margin-bottom: 2rem;
}

.au-table-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
    margin-bottom: 1.25rem;
    flex-wrap: wrap;
}

/* Streamlined Table */
.au-table-wrap {
    width: 100%;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}

.au-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    font-size: 0.9rem;
    text-align: left;
}

.au-table th {
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

.au-table td {
    padding: 0.95rem 1rem;
    border-bottom: 1px solid rgba(var(--muted), 0.08);
    color: rgb(var(--text));
    vertical-align: middle;
}

.au-table tr:hover td {
    background: rgba(var(--muted), 0.03);
}

/* Host Cell */
.payout-host-cell {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.payout-host-name {
    font-weight: 800;
    color: rgb(var(--text));
    font-size: 0.92rem;
    display: block;
    line-height: 1.25;
}

.payout-host-email {
    font-size: 0.78rem;
    color: rgb(var(--muted));
    display: block;
    margin-top: 2px;
}

/* Payout Type Badges */
.payout-type-pill {
    font-size: 0.72rem;
    font-weight: 800;
    padding: 0.25rem 0.65rem;
    border-radius: 20px;
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    white-space: nowrap;
}

.payout-type-pill--escrow {
    background: rgba(251, 146, 60, 0.14);
    color: #ea580c;
    border: 1px solid rgba(251, 146, 60, 0.3);
}

.payout-type-pill--auto {
    background: rgba(59, 130, 246, 0.12);
    color: #2563eb;
    border: 1px solid rgba(59, 130, 246, 0.28);
}

.payout-type-pill--standard {
    background: rgba(16, 185, 129, 0.1);
    color: #059669;
    border: 1px solid rgba(16, 185, 129, 0.25);
}

/* Status Badges */
.payout-status-pill {
    font-size: 0.74rem;
    font-weight: 800;
    padding: 0.28rem 0.75rem;
    border-radius: 20px;
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    white-space: nowrap;
}

.payout-status-pill--completed {
    background: rgba(16, 185, 129, 0.12);
    color: #059669;
    border: 1px solid rgba(16, 185, 129, 0.28);
}

.payout-status-pill--ready {
    background: rgba(245, 158, 11, 0.12);
    color: #d97706;
    border: 1px solid rgba(245, 158, 11, 0.28);
}

.payout-status-pill--flagged {
    background: rgba(239, 68, 68, 0.12);
    color: #dc2626;
    border: 1px solid rgba(239, 68, 68, 0.28);
}

/* Actions in Table */
.payout-actions-cell {
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    flex-wrap: nowrap;
}

.payout-btn-process {
    padding: 0.45rem 0.85rem;
    font-size: 0.82rem;
    font-weight: 800;
    border-radius: 10px;
    background: rgb(var(--brand));
    color: #ffffff !important;
    border: none;
    cursor: pointer;
    white-space: nowrap;
    transition: all 0.2s ease;
    box-shadow: 0 2px 8px rgba(var(--brand), 0.25);
}

.payout-btn-process:hover {
    filter: brightness(1.08);
    transform: translateY(-1px);
}

.payout-btn-flag {
    padding: 0.45rem 0.75rem;
    font-size: 0.82rem;
    font-weight: 700;
    border-radius: 10px;
    background: rgba(239, 68, 68, 0.1);
    color: #ef4444 !important;
    border: 1px solid rgba(239, 68, 68, 0.22);
    cursor: pointer;
    white-space: nowrap;
    transition: all 0.2s ease;
}

.payout-btn-flag:hover {
    background: rgba(239, 68, 68, 0.18);
    border-color: rgba(239, 68, 68, 0.4);
}

.payout-btn-unflag {
    padding: 0.45rem 0.85rem;
    font-size: 0.82rem;
    font-weight: 700;
    border-radius: 10px;
    background: rgba(59, 130, 246, 0.12);
    color: #2563eb !important;
    border: 1px solid rgba(59, 130, 246, 0.28);
    cursor: pointer;
    white-space: nowrap;
    transition: all 0.2s ease;
}

.payout-btn-unflag:hover {
    background: rgba(59, 130, 246, 0.2);
}

.payout-badge-disbursed {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    font-size: 0.78rem;
    font-weight: 800;
    color: #059669;
    padding: 0.35rem 0.8rem;
    background: rgba(16, 185, 129, 0.1);
    border-radius: 20px;
    border: 1px solid rgba(16, 185, 129, 0.25);
    white-space: nowrap;
}

/* ── Mobile Card Polish ── */
.payout-mobile-card {
    background: rgba(var(--card), 0.7);
    border: 1px solid rgba(var(--muted), 0.14);
    border-radius: 18px;
    padding: 1.15rem;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.03);
}

.payout-card-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 0.5rem;
    margin-top: 0.55rem;
    font-size: 0.85rem;
}

.payout-card-label {
    color: rgb(var(--muted));
    font-size: 0.8rem;
}

.payout-card-value {
    color: rgb(var(--text));
    font-weight: 700;
    text-align: right;
}

.payout-mobile-actions {
    display: flex;
    gap: 0.5rem;
    margin-top: 0.95rem;
    padding-top: 0.85rem;
    border-top: 1px solid rgba(var(--muted), 0.1);
}

.payout-mobile-actions button,
.payout-mobile-actions span {
    flex: 1;
    justify-content: center;
    text-align: center;
    padding: 0.55rem 0.85rem;
}

/* Modal Polish */
.modal-card {
    width: 100%;
    max-width: min(92vw, 520px);
    background: rgb(var(--card));
    color: rgb(var(--text));
    border: 1px solid rgba(var(--muted), 0.2);
    border-radius: 22px;
    padding: 1.5rem;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.4);
    box-sizing: border-box;
}

/* ══ Tablet (<= 768px) ══ */
@media (max-width: 768px) {
    .au-table-wrap {
        display: none;
    }
    .au-cards-list {
        display: flex;
        flex-direction: column;
        gap: 0.85rem;
    }
    .au-toolbar-form {
        flex-direction: column;
        align-items: stretch;
    }
    .au-search-wrap,
    .au-filter-group select,
    .au-toolbar-form button,
    .au-toolbar-form a {
        width: 100%;
    }
    .au-filter-group {
        flex-direction: column;
        width: 100%;
    }
}

/* ══ Desktop (> 768px) ══ */
@media (min-width: 769px) {
    .au-cards-list {
        display: none;
    }
}
</style>

<section class="page-hero">
    <div>
        <span class="panel-badge">Admin — Payouts</span>
        <h1>Payout Coordination &amp; History</h1>
        <p class="lede">Review payout readiness, inspect host bank details, track completed disbursements, and process new requests.</p>
    </div>
</section>

<!-- Summary Stats -->
<section class="admin-users-stats">
    <div class="au-stat-card" style="border-left: 4px solid #f59e0b;">
        <span class="au-stat-icon au-stat-icon--warning">
            <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="12" y1="1" x2="12" y2="23" />
                <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" />
            </svg>
        </span>
        <div class="au-stat-body">
            <span class="au-stat-num"><?= format_price($pending_payouts) ?></span>
            <span class="au-stat-lbl">Pending (<?= (int)$pending_count ?> queued)</span>
        </div>
    </div>
    <div class="au-stat-card" style="border-left: 4px solid #10b981;">
        <span class="au-stat-icon au-stat-icon--success">
            <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
                <polyline points="22 4 12 14.01 9 11.01" />
            </svg>
        </span>
        <div class="au-stat-body">
            <span class="au-stat-num"><?= format_price($completed_payouts) ?></span>
            <span class="au-stat-lbl">Disbursed (<?= (int)$completed_count ?> completed)</span>
        </div>
    </div>
    <div class="au-stat-card">
        <span class="au-stat-icon au-stat-icon--brand">
            <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                <circle cx="8.5" cy="7" r="4" />
                <polyline points="17 11 19 13 23 9" />
            </svg>
        </span>
        <div class="au-stat-body">
            <span class="au-stat-num"><?= (int)$hosts_ready ?></span>
            <span class="au-stat-lbl">Hosts Ready to Disburse</span>
        </div>
    </div>
    <div class="au-stat-card">
        <span class="au-stat-icon au-stat-icon--warning">
            <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10" />
                <line x1="12" y1="8" x2="12" y2="12" />
                <line x1="12" y1="16" x2="12.01" y2="16" />
            </svg>
        </span>
        <div class="au-stat-body">
            <span class="au-stat-num"><?= (int)$hosts_flagged ?></span>
            <span class="au-stat-lbl">On Hold / Flagged</span>
        </div>
    </div>
</section>

<!-- Toolbar: Search & Filter -->
<section class="admin-users-toolbar">
    <form method="GET" action="" class="au-toolbar-form">
        <div class="au-search-wrap">
            <svg class="au-search-icon" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="11" cy="11" r="8" />
                <line x1="21" y1="21" x2="16.65" y2="16.65" />
            </svg>
            <input type="search" name="q" id="payouts-search" placeholder="Search by host, email, bank, account, or event..." aria-label="Search payouts" value="<?= e($search ?? '') ?>" />
        </div>
        <div class="au-filter-group">
            <select name="status" id="payouts-status-filter" aria-label="Filter by status">
                <option value="">All Statuses (Pending, Completed, Flagged)</option>
                <option value="completed" <?= in_array($status ?? '', ['completed', 'processed']) ? 'selected' : '' ?>>Completed / Disbursed</option>
                <option value="pending" <?= in_array($status ?? '', ['pending', 'ready']) ? 'selected' : '' ?>>Pending / Ready to Pay</option>
                <option value="flagged" <?= in_array($status ?? '', ['flagged', 'failed']) ? 'selected' : '' ?>>Flagged / On Hold</option>
            </select>
            <select name="type" id="payouts-type-filter" aria-label="Filter by payout type">
                <option value="">All Payout Types</option>
                <option value="completed" <?= ($type ?? '') === 'completed' ? 'selected' : '' ?>>Standard Payouts</option>
                <option value="escrow" <?= ($type ?? '') === 'escrow' ? 'selected' : '' ?>>Escrow Early Withdrawals</option>
            </select>
        </div>
        <button type="submit" class="admin-action-btn primary" style="padding: 0.55rem 1.15rem; border-radius: 11px; font-weight: 700; cursor: pointer;">
            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="22 12 18 12 15 21 9 3 6 12 2 12" />
            </svg>
            Filter
        </button>
        <?php if (!empty($search) || !empty($status) || !empty($type)): ?>
            <a href="?" class="admin-action-btn" title="Clear all search and filter conditions" style="padding: 0.55rem 0.85rem; border-radius: 11px;">Clear</a>
        <?php endif; ?>
    </form>
</section>

<!-- Payout Queue Section -->
<section class="admin-users-table-section">
    <div class="au-table-header">
        <div>
            <h2 style="font-size: 1.25rem; margin: 0;">Payout History &amp; Queue</h2>
            <p style="margin: 0.2rem 0 0; font-size: 0.82rem; color: rgb(var(--muted));">
                Showing all payout records across statuses. Full settlement breakdowns and bank details are accessible in the processing dialog.
            </p>
        </div>
        <a href="<?= route('admin/payouts/export', ['q' => $search ?? '', 'status' => $status ?? '', 'type' => $type ?? '']) ?>" class="admin-action-btn" title="Export matching payouts to CSV format" style="padding: 0.45rem 0.95rem; font-size: 0.82rem; border-radius: 10px;">
            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4" />
                <polyline points="7 10 12 15 17 10" />
                <line x1="12" y1="15" x2="12" y2="3" />
            </svg>
            Export CSV
        </a>
    </div>

    <!-- Desktop Streamlined Table View -->
    <div class="au-table-wrap">
        <table class="au-table">
            <thead>
                <tr>
                    <th>Host</th>
                    <th>Event &amp; Type</th>
                    <th>Payout Amount</th>
                    <th>Bank Destination</th>
                    <th>Status</th>
                    <th>Date Requested</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody id="payouts-tbody">
                <?php if (empty($payouts)): ?>
                    <tr>
                        <td colspan="7" class="text-center" style="padding:3rem 1rem; text-align:center;">
                            <div style="color:rgb(var(--muted)); font-size:0.95rem;">
                                <p style="font-size:1.15rem; font-weight:700; margin-bottom:0.35rem; color:rgb(var(--text));">No payouts found</p>
                                <span>No payout records match the selected filter criteria.</span>
                            </div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($payouts as $p): 
                        $hue = abs(crc32($p['host_name'] ?? 'H')) % 360;
                        $initials = strtoupper(substr($p['host_name'] ?? 'H', 0, 2));
                        $isCompleted = in_array($p['status'], ['processed', 'completed']);
                        $isFlagged = ($p['status'] === 'failed');
                        $isPending = ($p['status'] === 'pending');
                        $bankName = $p['payout_bank'] ?: 'Not Configured';
                        $accNum = $p['payout_account'] ? '•••• ' . substr($p['payout_account'], -4) : 'N/A';
                        $isEscrow = (($p['type'] ?? 'completed') === 'escrow');
                        $isAuto = (($p['type'] ?? '') === 'auto_completed');
                        $eventName = $p['event_name'] ?? '';
                    ?>
                    <tr>
                        <td>
                            <div class="payout-host-cell">
                                <span class="au-avatar" style="--avatar-hue:<?= $hue ?>"><?= $initials ?></span>
                                <div>
                                    <span class="payout-host-name"><?= e($p['host_name'] ?? 'Organizer') ?></span>
                                    <span class="payout-host-email"><?= e($p['host_email'] ?? 'N/A') ?></span>
                                    <span style="font-size:0.7rem; color:rgb(var(--muted)); font-family:monospace;">ID: #<?= (int)$p['id'] ?></span>
                                </div>
                            </div>
                        </td>
                        <td>
                            <strong style="display:block; font-size:0.88rem; color:rgb(var(--text)); line-height:1.3;" title="<?= e($eventName) ?>">
                                <?= e(!empty($eventName) ? mb_strimwidth($eventName, 0, 26, '...') : 'General Settlement') ?>
                            </strong>
                            <div style="margin-top: 4px; display:flex; align-items:center; gap:0.4rem; flex-wrap:wrap;">
                                <?php if ($isEscrow): ?>
                                    <span class="payout-type-pill payout-type-pill--escrow" title="Early withdrawal request for upcoming/active event">
                                        ⚡ Escrow
                                    </span>
                                <?php elseif ($isAuto): ?>
                                    <span class="payout-type-pill payout-type-pill--auto" title="Automatically generated upon event completion">
                                        🤖 Auto
                                    </span>
                                <?php else: ?>
                                    <span class="payout-type-pill payout-type-pill--standard">
                                        ✅ Standard
                                    </span>
                                <?php endif; ?>
                                <?php if (!empty($p['event_id']) && isset($p['event_remaining_balance'])): ?>
                                    <span style="font-size:0.72rem; color:rgb(var(--muted));">
                                        (Bal: <?= format_price($p['event_remaining_balance']) ?>)
                                    </span>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td>
                            <strong style="color:rgb(var(--text)); font-size:1.02rem; font-weight:800; display:block;">
                                <?= format_price($p['amount']) ?>
                            </strong>
                            <?php if (!empty($p['is_partial']) && ($p['event_prior_disbursed'] ?? 0) > 0): ?>
                                <span style="font-size:0.7rem; color:#6366f1; font-weight:600; display:block; margin-top:2px;" title="Prior disbursements have been made for this event">
                                    🛡️ <?= format_price($p['event_prior_disbursed']) ?> prior
                                </span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div style="font-size:0.86rem; font-weight:700; color:rgb(var(--text));">
                                <?= e($bankName) ?>
                            </div>
                            <span style="font-family:monospace; font-size:0.8rem; color:rgb(var(--muted));">
                                <?= e($accNum) ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($isCompleted): ?>
                                <span class="payout-status-pill payout-status-pill--completed">
                                    <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
                                    Completed
                                </span>
                            <?php elseif ($isFlagged): ?>
                                <span class="payout-status-pill payout-status-pill--flagged">
                                    <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                                    Flagged
                                </span>
                            <?php else: ?>
                                <span class="payout-status-pill payout-status-pill--ready">
                                    <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                    Ready to Pay
                                </span>
                            <?php endif; ?>
                        </td>
                        <td style="font-size:0.84rem; white-space:nowrap;">
                            <?= date('M d, Y', strtotime($p['created_at'])) ?><br />
                            <span style="font-size:0.75rem; color:rgb(var(--muted));"><?= date('h:i A', strtotime($p['created_at'])) ?></span>
                        </td>
                        <td style="text-align: right;">
                            <div class="payout-actions-cell" style="justify-content: flex-end;">
                                <?php if ($isCompleted): ?>
                                    <span class="payout-badge-disbursed">
                                        ✓ Disbursed
                                    </span>
                                <?php elseif ($isFlagged): ?>
                                    <button type="button" class="payout-btn-unflag au-unflag-btn" data-payout-id="<?= $p['id'] ?>" data-host-name="<?= e($p['host_name'] ?? 'Host') ?>">
                                        Resolve / Unflag
                                    </button>
                                <?php else: ?>
                                    <button type="button" class="payout-btn-process au-process-btn" 
                                        data-payout-id="<?= $p['id'] ?>"
                                        data-host-name="<?= e($p['host_name'] ?? 'Host') ?>" 
                                        data-amount="<?= format_price($p['amount']) ?>"
                                        data-bank="<?= e($p['payout_bank'] ?: 'Not Configured') ?>" 
                                        data-account="<?= e($p['payout_account'] ?: 'Not Configured') ?>"
                                        data-routing="<?= e($p['payout_routing'] ?: 'N/A') ?>"
                                        data-payout-type="<?= e($p['type'] ?? 'completed') ?>"
                                        data-pass-fee="<?= isset($p['pass_fee']) ? (int)$p['pass_fee'] : 1 ?>"
                                        data-event-name="<?= e($p['event_name'] ?? 'N/A') ?>"
                                        data-event-tickets="<?= (int)($p['event_tickets_count'] ?? 0) ?>"
                                        data-event-gross="<?= number_format($p['event_gross_sales'] ?? 0, 2, '.', '') ?>"
                                        data-event-net="<?= number_format($p['event_net_sales'] ?? 0, 2, '.', '') ?>"
                                        data-event-prior="<?= number_format($p['event_prior_disbursed'] ?? 0, 2, '.', '') ?>"
                                        data-event-remaining="<?= number_format($p['event_remaining_balance'] ?? 0, 2, '.', '') ?>"
                                        data-is-partial="<?= !empty($p['is_partial']) ? '1' : '0' ?>">
                                        Process Payout
                                    </button>
                                    <button type="button" class="payout-btn-flag au-flag-btn" data-payout-id="<?= $p['id'] ?>" data-host-name="<?= e($p['host_name'] ?? 'Host') ?>">
                                        Flag
                                    </button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Mobile Card View (<= 768px) -->
    <div class="au-cards-list" id="payouts-cards">
        <?php if (!empty($payouts)): ?>
            <?php foreach ($payouts as $p): 
                $hue = abs(crc32($p['host_name'] ?? 'H')) % 360;
                $initials = strtoupper(substr($p['host_name'] ?? 'H', 0, 2));
                $isCompleted = in_array($p['status'], ['processed', 'completed']);
                $isFlagged = ($p['status'] === 'failed');
                $isPending = ($p['status'] === 'pending');
                $bankName = $p['payout_bank'] ?: 'Not Configured';
                $accNum = $p['payout_account'] ? '•••• ' . substr($p['payout_account'], -4) : 'N/A';
                $isEscrow = (($p['type'] ?? 'completed') === 'escrow');
                $isAuto = (($p['type'] ?? '') === 'auto_completed');
                $eventName = $p['event_name'] ?? '';
            ?>
            <article class="payout-mobile-card au-user-card">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 0.5rem; margin-bottom: 0.75rem;">
                    <div class="payout-host-cell">
                        <span class="au-avatar" style="--avatar-hue:<?= $hue ?>"><?= $initials ?></span>
                        <div>
                            <span class="payout-host-name"><?= e($p['host_name'] ?? 'Organizer') ?></span>
                            <span class="payout-host-email"><?= e($p['host_email'] ?? 'N/A') ?></span>
                        </div>
                    </div>
                    <?php if ($isCompleted): ?>
                        <span class="payout-status-pill payout-status-pill--completed">✓ Completed</span>
                    <?php elseif ($isFlagged): ?>
                        <span class="payout-status-pill payout-status-pill--flagged">Flagged</span>
                    <?php else: ?>
                        <span class="payout-status-pill payout-status-pill--ready">Ready to Pay</span>
                    <?php endif; ?>
                </div>

                <div class="payout-card-row">
                    <span class="payout-card-label">Payout Amount:</span>
                    <span class="payout-card-value" style="color: rgb(var(--brand)); font-size: 1.05rem; font-weight: 800;">
                        <?= format_price($p['amount']) ?>
                        <?php if (!empty($p['is_partial']) && ($p['event_prior_disbursed'] ?? 0) > 0): ?>
                            <span style="font-size:0.7rem; color:#6366f1; font-weight:600; display:block; margin-top:2px;">
                                🛡️ <?= format_price($p['event_prior_disbursed']) ?> prior disbursed
                            </span>
                        <?php endif; ?>
                    </span>
                </div>

                <?php if (!empty($eventName)): ?>
                    <div class="payout-card-row">
                        <span class="payout-card-label">Event:</span>
                        <span class="payout-card-value">
                            <?= e(mb_strimwidth($eventName, 0, 24, '...')) ?>
                            <?php if (!empty($p['event_id']) && isset($p['event_remaining_balance'])): ?>
                                <span style="font-size:0.72rem; color:rgb(var(--muted)); display:block;">
                                    Remaining: <?= format_price($p['event_remaining_balance']) ?>
                                </span>
                            <?php endif; ?>
                        </span>
                    </div>
                <?php endif; ?>

                <div class="payout-card-row">
                    <span class="payout-card-label">Destination:</span>
                    <span class="payout-card-value">🏦 <?= e($bankName) ?> (<?= e($accNum) ?>)</span>
                </div>

                <div class="payout-card-row">
                    <span class="payout-card-label">Type &amp; Date:</span>
                    <span class="payout-card-value" style="font-size: 0.78rem;">
                        <?php if ($isEscrow): ?>
                            <span class="payout-type-pill payout-type-pill--escrow">⚡ Escrow</span>
                        <?php elseif ($isAuto): ?>
                            <span class="payout-type-pill payout-type-pill--auto">🤖 Auto</span>
                        <?php else: ?>
                            <span class="payout-type-pill payout-type-pill--standard">✅ Standard</span>
                        <?php endif; ?>
                        · <?= date('M d, Y', strtotime($p['created_at'])) ?>
                    </span>
                </div>

                <div class="payout-mobile-actions">
                    <?php if ($isCompleted): ?>
                        <span class="payout-badge-disbursed">
                            ✓ Disbursed
                        </span>
                    <?php elseif ($isFlagged): ?>
                        <button type="button" class="payout-btn-unflag au-unflag-btn" data-payout-id="<?= $p['id'] ?>" data-host-name="<?= e($p['host_name'] ?? 'Host') ?>">
                            Resolve / Unflag
                        </button>
                    <?php else: ?>
                        <button type="button" class="payout-btn-process au-process-btn" 
                            data-payout-id="<?= $p['id'] ?>"
                            data-host-name="<?= e($p['host_name'] ?? 'Host') ?>" 
                            data-amount="<?= format_price($p['amount']) ?>"
                            data-bank="<?= e($p['payout_bank'] ?: 'Not Configured') ?>" 
                            data-account="<?= e($p['payout_account'] ?: 'Not Configured') ?>"
                            data-routing="<?= e($p['payout_routing'] ?: 'N/A') ?>"
                            data-payout-type="<?= e($p['type'] ?? 'completed') ?>"
                            data-pass-fee="<?= isset($p['pass_fee']) ? (int)$p['pass_fee'] : 1 ?>"
                            data-event-name="<?= e($p['event_name'] ?? 'N/A') ?>"
                            data-event-tickets="<?= (int)($p['event_tickets_count'] ?? 0) ?>"
                            data-event-gross="<?= number_format($p['event_gross_sales'] ?? 0, 2, '.', '') ?>"
                            data-event-net="<?= number_format($p['event_net_sales'] ?? 0, 2, '.', '') ?>"
                            data-event-prior="<?= number_format($p['event_prior_disbursed'] ?? 0, 2, '.', '') ?>"
                            data-event-remaining="<?= number_format($p['event_remaining_balance'] ?? 0, 2, '.', '') ?>"
                            data-is-partial="<?= !empty($p['is_partial']) ? '1' : '0' ?>">
                            Process Payout
                        </button>
                        <button type="button" class="payout-btn-flag au-flag-btn" data-payout-id="<?= $p['id'] ?>" data-host-name="<?= e($p['host_name'] ?? 'Host') ?>">
                            Flag
                        </button>
                    <?php endif; ?>
                </div>
            </article>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Empty State -->
    <div id="payouts-empty" class="empty-state <?= !empty($payouts) ? 'hidden' : '' ?>" style="text-align:center; padding:2rem 1rem; color:rgb(var(--muted));">
        <p>No payout accounts found matching your search.</p>
    </div>

    <!-- Pagination -->
    <?php if (($totalPages ?? 1) > 1): ?>
    <nav class="au-pagination" aria-label="Payouts pagination" id="payouts-pagination">
        <?php if ($page > 1): ?>
            <a href="?page=<?= $page - 1 ?>&q=<?= urlencode($search ?? '') ?>&status=<?= urlencode($status ?? '') ?>&type=<?= urlencode($type ?? '') ?>" class="au-page-btn">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="15 18 9 12 15 6" />
                </svg>
                Prev
            </a>
        <?php else: ?>
            <span class="au-page-btn disabled" aria-disabled="true">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="15 18 9 12 15 6" />
                </svg>
                Prev
            </span>
        <?php endif; ?>

        <?php foreach (pagination_range($page, $totalPages) as $pNum): ?>
            <?php if ($pNum === '...'): ?>
                <span class="au-page-ellipsis" aria-hidden="true">&hellip;</span>
            <?php elseif ($pNum === $page): ?>
                <span class="au-page-num active" aria-current="page"><?= $pNum ?></span>
            <?php else: ?>
                <a href="?page=<?= $pNum ?>&q=<?= urlencode($search ?? '') ?>&status=<?= urlencode($status ?? '') ?>&type=<?= urlencode($type ?? '') ?>" class="au-page-num"><?= $pNum ?></a>
            <?php endif; ?>
        <?php endforeach; ?>

        <?php if ($page < $totalPages): ?>
            <a href="?page=<?= $page + 1 ?>&q=<?= urlencode($search ?? '') ?>&status=<?= urlencode($status ?? '') ?>&type=<?= urlencode($type ?? '') ?>" class="au-page-btn">
                Next
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="9 18 15 12 9 6" />
                </svg>
            </a>
        <?php else: ?>
            <span class="au-page-btn disabled" aria-disabled="true">
                Next
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="9 18 15 12 9 6" />
                </svg>
            </span>
        <?php endif; ?>
    </nav>
    <?php endif; ?>
</section>

<!-- Confirm Process Payout Modal (Full Breakdown Dialog) -->
<div class="modal-overlay hidden" id="process-modal" role="dialog" aria-modal="true" aria-labelledby="process-modal-title">
    <div class="modal-card">
        <h3 id="process-modal-title" style="margin: 0 0 0.5rem; font-size: 1.25rem;">Process Host Payout</h3>
        <p style="margin: 0 0 1rem; font-size: 0.9rem; color: rgb(var(--muted));">
            You are processing a payout of <strong id="process-payout-amount" style="color:rgb(var(--brand)); font-size:1.15rem;">$0.00</strong> to <strong id="process-host-name" style="color:rgb(var(--text));">this host</strong>.
        </p>

        <!-- Fee Deduction Policy Notice Banner -->
        <div id="process-fee-banner" style="background:rgba(var(--muted), 0.06); border:1px solid rgba(var(--muted), 0.16); border-radius:12px; padding:0.75rem 0.95rem; margin-bottom: 0.85rem; font-size:0.84rem;">
            <div id="process-fee-desc" style="color:rgb(var(--text)); line-height:1.45;">—</div>
        </div>

        <!-- Escrow Release Alert Banner -->
        <div id="process-escrow-warning" style="display:none; background:rgba(251, 146, 60, 0.12); border:1px solid rgba(251, 146, 60, 0.3); border-radius:12px; padding:0.85rem 1rem; margin-bottom: 0.85rem;">
            <div style="display:flex; gap:0.55rem; align-items:flex-start;">
                <span style="font-size:1.2rem; flex-shrink:0;">⚡</span>
                <div>
                    <strong style="color:#c2410c; font-size:0.88rem; display:block;">Early Escrow Release Request</strong>
                    <p style="margin:0.25rem 0 0; font-size:0.8rem; color:#9a3412; line-height:1.45;">
                        This payout is an <strong>early escrow withdrawal</strong> for an active/upcoming event (<span id="process-event-title" style="font-weight:700;">—</span>).
                    </p>
                </div>
            </div>
        </div>

        <!-- Event Revenue & Settlement Breakdown -->
        <div id="process-revenue-breakdown" style="display:none; background:rgba(59, 130, 246, 0.06); border:1px solid rgba(59, 130, 246, 0.2); border-radius:12px; padding:0.85rem 1rem; margin-bottom: 0.85rem;">
            <strong style="color:#1d4ed8; font-size:0.85rem; display:block; margin-bottom:0.5rem;">📊 Event Revenue &amp; Settlement Breakdown</strong>
            <table style="width:100%; font-size:0.82rem; border-collapse:collapse; color:rgb(var(--text));">
                <tr>
                    <td style="padding:3px 0; color:rgb(var(--muted));">Tickets Sold</td>
                    <td style="padding:3px 0; text-align:right; font-weight:700;" id="process-tickets-sold">—</td>
                </tr>
                <tr>
                    <td style="padding:3px 0; color:rgb(var(--muted));">Gross Event Sales</td>
                    <td style="padding:3px 0; text-align:right; font-weight:700;" id="process-gross-sales">—</td>
                </tr>
                <tr style="border-bottom:1px solid rgba(59, 130, 246, 0.15);">
                    <td style="padding:3px 0; color:rgb(var(--muted));">Net Event Sales (Host Earnings)</td>
                    <td style="padding:3px 0; text-align:right; font-weight:800; color:#1d4ed8;" id="process-net-sales">—</td>
                </tr>
                <tr>
                    <td style="padding:3px 0; color:rgb(var(--muted));">Prior Disbursed</td>
                    <td style="padding:3px 0; text-align:right; font-weight:700;" id="process-prior-disbursed">—</td>
                </tr>
                <tr style="border-bottom:1px solid rgba(59, 130, 246, 0.15);">
                    <td style="padding:3px 0; font-weight:700;">This Payout</td>
                    <td style="padding:3px 0; text-align:right; font-weight:800; color:rgb(var(--brand));" id="process-this-amount">—</td>
                </tr>
                <tr>
                    <td style="padding:3px 0; font-weight:700;">Remaining Balance</td>
                    <td style="padding:3px 0; text-align:right; font-weight:800;" id="process-remaining">—</td>
                </tr>
            </table>
            <div id="process-partial-badge" style="display:none; margin-top:0.5rem;">
                <span style="background:rgba(217, 119, 6, 0.15); color:#d97706; border:1px solid rgba(217, 119, 6, 0.3); font-size:0.72rem; font-weight:800; padding:2px 7px; border-radius:6px;">⚠ Partial Settlement — prior payouts already disbursed for this event</span>
            </div>
        </div>

        <form method="POST" action="{{ route('admin/payouts') }}">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="process_payout" />
            <input type="hidden" name="payout_id" id="process-payout-id" value="" />

            <div style="margin: 1rem 0; background: rgba(var(--muted), 0.05); padding: 0.85rem 1rem; border-radius: 12px; border: 1px solid rgba(var(--muted), 0.15);">
                <label style="font-weight: 700; font-size: 0.82rem; display: block; margin-bottom: 0.5rem; color: rgb(var(--text)); text-transform:uppercase; letter-spacing:0.03em;">Disbursement Method</label>
                
                <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer; font-size: 0.88rem; margin-bottom: 0.55rem;">
                    <input type="radio" name="mode" value="paystack" checked />
                    <span>⚡ <strong>Paystack Direct Transfer</strong> (Instant NUBAN Transfer)</span>
                </label>

                <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer; font-size: 0.88rem; margin-bottom: 0.55rem;">
                    <input type="radio" name="mode" value="flutterwave" />
                    <span>🦋 <strong>Flutterwave Direct Transfer</strong> (Instant Bank Payout)</span>
                </label>

                <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer; font-size: 0.88rem;">
                    <input type="radio" name="mode" value="manual" />
                    <span>📝 <strong>Manual Mark as Paid</strong> (Bank App / Cash / Offline)</span>
                </label>
            </div>

            <!-- Manual Bank Details Panel (shown when Manual is selected) -->
            <div id="manual-bank-details" style="display:none; margin: 0 0 1rem 0; background: rgba(16, 185, 129, 0.06); padding: 0.95rem 1rem; border-radius: 12px; border: 1px solid rgba(16, 185, 129, 0.2);">
                <p style="font-weight: 800; font-size: 0.82rem; color: #10b981; margin: 0 0 0.65rem; text-transform: uppercase; letter-spacing: 0.5px;">🏦 Transfer Funds To This Account</p>
                <div style="display: grid; grid-template-columns: auto 1fr; gap: 0.35rem 1rem; font-size: 0.88rem;">
                    <span style="color: rgb(var(--muted)); font-weight: 600;">Bank:</span>
                    <span id="manual-bank-name" style="font-weight: 700; color: rgb(var(--text));">—</span>
                    <span style="color: rgb(var(--muted)); font-weight: 600;">Account No:</span>
                    <span id="manual-account-number" style="font-family: monospace; font-weight: 700; font-size: 0.95rem; color: rgb(var(--text)); letter-spacing: 1px;">—</span>
                    <span style="color: rgb(var(--muted)); font-weight: 600;">Routing:</span>
                    <span id="manual-routing-number" style="font-family: monospace; font-weight: 700; color: rgb(var(--text));">—</span>
                    <span style="color: rgb(var(--muted)); font-weight: 600;">Amount:</span>
                    <span id="manual-payout-amount" style="font-weight: 800; font-size: 1.05rem; color: #10b981;">—</span>
                </div>
                <p style="margin: 0.65rem 0 0; font-size: 0.78rem; color: rgb(var(--muted)); line-height: 1.4;">
                    Transfer the exact amount above via your bank app or internet banking, then click <strong>"Confirm Transfer Complete"</strong> below to mark this payout as processed.
                </p>
            </div>

            <div class="modal-actions" style="display: flex; gap: 0.75rem; justify-content: flex-end; margin-top: 1.25rem;">
                <button type="button" class="admin-action-btn" id="process-cancel-btn" style="padding: 0.55rem 1.15rem; border-radius: 11px;">Cancel</button>
                <button type="submit" class="admin-action-btn primary" id="process-submit-btn" style="padding: 0.55rem 1.35rem; border-radius: 11px; font-weight: 800;">Confirm &amp; Disburse</button>
            </div>
        </form>
    </div>
</div>

<!-- Confirm Flag Host Payout Modal -->
<div class="modal-overlay hidden" id="flag-modal" role="dialog" aria-modal="true" aria-labelledby="flag-modal-title">
    <div class="modal-card">
        <h3 id="flag-modal-title" style="margin: 0 0 0.5rem; font-size: 1.25rem;">Flag Payout Account</h3>
        <p style="margin: 0 0 1.25rem; font-size: 0.9rem; color: rgb(var(--muted)); line-height: 1.5;">
            Are you sure you want to flag <strong id="flag-host-name" style="color:rgb(var(--text));">this host</strong>'s payout account? All pending disbursements will be frozen until resolved.
        </p>
        <form method="POST" action="{{ route('admin/payouts') }}">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="flag_account" />
            <input type="hidden" name="payout_id" id="flag-payout-id" value="" />
            <div class="modal-actions" style="display: flex; gap: 0.75rem; justify-content: flex-end;">
                <button type="button" class="admin-action-btn" id="flag-cancel-btn" style="padding: 0.55rem 1.15rem; border-radius: 11px;">Cancel</button>
                <button type="submit" class="admin-action-btn danger" style="padding: 0.55rem 1.15rem; border-radius: 11px; font-weight: 800;">Yes, Flag Account</button>
            </div>
        </form>
    </div>
</div>

<!-- Confirm Resolve / Unflag Host Modal -->
<div class="modal-overlay hidden" id="unflag-modal" role="dialog" aria-modal="true" aria-labelledby="unflag-modal-title">
    <div class="modal-card">
        <h3 id="unflag-modal-title" style="margin: 0 0 0.5rem; font-size: 1.25rem;">Resolve Payout Issue</h3>
        <p style="margin: 0 0 1.25rem; font-size: 0.9rem; color: rgb(var(--muted)); line-height: 1.5;">
            Are you sure you want to resolve the hold on <strong id="unflag-host-name" style="color:rgb(var(--text));">this host</strong>'s account? Their status will be restored to "Ready to Pay".
        </p>
        <form method="POST" action="{{ route('admin/payouts') }}">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="unflag_account" />
            <input type="hidden" name="payout_id" id="unflag-payout-id" value="" />
            <div class="modal-actions" style="display: flex; gap: 0.75rem; justify-content: flex-end;">
                <button type="button" class="admin-action-btn" id="unflag-cancel-btn" style="padding: 0.55rem 1.15rem; border-radius: 11px;">Cancel</button>
                <button type="submit" class="admin-action-btn primary" style="padding: 0.55rem 1.15rem; border-radius: 11px; font-weight: 800;">Resolve Hold</button>
            </div>
        </form>
    </div>
</div>
@endsection