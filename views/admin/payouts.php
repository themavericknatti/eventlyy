<?php

/**
 * Admin Payouts
 *
 * This file contains the HTML markup for the admin payouts page of the application.
 *
 * @package Evently
 * @subpackage Views/Admin

 * @author Your Name <Celio Natti>
 * @license MIT License
 * @version 1.0.0
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
<section class="page-hero">
    <div>
        <span class="panel-badge">Admin — Payouts</span>
        <h1>Payout Coordination & History</h1>
        <p class="lede">Review payout readiness, inspect host bank details, track completed disbursements, and process new requests.</p>
    </div>
</section>

<!-- Summary Stats -->
<section class="admin-users-stats">
    <div class="au-stat-card" style="border-left: 4px solid #f59e0b;">
        <span class="au-stat-icon au-stat-icon--warning">
            <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round">
                <line x1="12" y1="1" x2="12" y2="23" />
                <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" />
            </svg>
        </span>
        <div class="au-stat-body">
            <span class="au-stat-num"><?= format_price($pending_payouts) ?></span>
            <span class="au-stat-lbl">Pending Payouts (<?= (int)$pending_count ?> queued)</span>
        </div>
    </div>
    <div class="au-stat-card" style="border-left: 4px solid #10b981;">
        <span class="au-stat-icon au-stat-icon--success">
            <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round">
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
            <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round">
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
            <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round">
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
            <svg class="au-search-icon" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor"
                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="11" cy="11" r="8" />
                <line x1="21" y1="21" x2="16.65" y2="16.65" />
            </svg>
            <input type="search" name="q" id="payouts-search"
                placeholder="Search by host name, email, bank, account, or event..." aria-label="Search payouts" value="<?= e($search ?? '') ?>" />
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
        <button type="submit" class="admin-action-btn primary">
            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5"
                stroke-linecap="round" stroke-linejoin="round">
                <polyline points="22 12 18 12 15 21 9 3 6 12 2 12" />
            </svg>
            Filter
        </button>
        <?php if (!empty($search) || !empty($status) || !empty($type)): ?>
            <a href="?" class="admin-action-btn" title="Clear all search and filter conditions">Clear</a>
        <?php endif; ?>
    </form>
</section>

<!-- Payout Queue Section -->
<section class="admin-users-table-section">
    <div class="au-table-header">
        <div>
            <h2>Payout History &amp; Queue</h2>
            <p style="margin: 0.15rem 0 0; font-size: 0.8rem; color: rgb(var(--muted));">
                Showing all payout records across statuses.
            </p>
        </div>
        <a href="<?= route('admin/payouts/export', ['q' => $search ?? '', 'status' => $status ?? '', 'type' => $type ?? '']) ?>" class="admin-action-btn" title="Export matching payouts to CSV format">
            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5"
                stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4" />
                <polyline points="7 10 12 15 17 10" />
                <line x1="12" y1="15" x2="12" y2="3" />
            </svg>
            Export CSV
        </a>
    </div>

    <!-- Desktop Table View -->
    <div class="au-table-wrap">
        <table class="au-table">
            <thead>
                <tr>
                    <th>Host</th>
                    <th>Bank Account</th>
                    <th>Account Details</th>
                    <th>Payout Amount</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th class="au-th-actions">Actions</th>
                </tr>
            </thead>
            <tbody id="payouts-tbody">
                <?php if (empty($payouts)): ?>
                    <tr>
                        <td colspan="7" class="text-center" style="padding:2.5rem 1rem;">
                            <div style="color:rgb(var(--muted)); font-size:0.95rem;">
                                <p style="font-size:1.1rem; font-weight:600; margin-bottom:0.25rem;">No payouts found</p>
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
                        $routing = $p['payout_routing'] ?: 'N/A';
                        $isEscrow = (($p['type'] ?? 'completed') === 'escrow');
                        $eventName = $p['event_name'] ?? '';
                    ?>
                    <tr>
                        <td>
                            <div class="au-user-cell">
                                <span class="au-avatar" style="--avatar-hue:<?= $hue ?>"><?= $initials ?></span>
                                <div>
                                    <span class="au-user-name"><?= e($p['host_name'] ?? 'Organizer') ?></span>
                                    <span class="au-card-email"><?= e($p['host_email'] ?? 'N/A') ?></span>
                                    <span style="font-size:0.72rem; color:rgb(var(--muted)); font-family:monospace;">ID: #<?= (int)$p['id'] ?></span>
                                </div>
                            </div>
                        </td>
                        <td class="au-date-cell">
                            <strong><?= e($bankName) ?></strong><br />
                            <span style="font-size:0.78rem;color:rgb(var(--muted))">Checking Account</span>
                        </td>
                        <td>
                            <span style="font-family:monospace;font-size:0.88rem;line-height:1.4;display:block;">
                                Acc: <strong><?= e($accNum) ?></strong><br />
                                Rout: <?= e($routing) ?>
                            </span>
                        </td>
                        <td>
                            <strong style="color:rgb(var(--text));font-size:0.98rem;display:block;"><?= format_price($p['amount']) ?></strong>
                            <?php if ($isEscrow): ?>
                                <span style="background:rgba(251, 146, 60, 0.15); color:#ea580c; border:1px solid rgba(251, 146, 60, 0.3); font-size:0.72rem; font-weight:800; padding:2px 7px; border-radius:6px; display:inline-flex; align-items:center; gap:3px; margin-top:3px;" title="Early withdrawal request for an upcoming/active event">
                                    ⚡ Escrow (Early Release)
                                </span>
                            <?php elseif (($p['type'] ?? '') === 'auto_completed'): ?>
                                <span style="background:rgba(59, 130, 246, 0.12); color:#2563eb; border:1px solid rgba(59, 130, 246, 0.3); font-size:0.72rem; font-weight:800; padding:2px 7px; border-radius:6px; display:inline-flex; align-items:center; gap:3px; margin-top:3px;" title="Automatically generated by system upon event completion">
                                    🤖 Auto-Requested
                                </span>
                            <?php else: ?>
                                <span style="background:rgba(16, 185, 129, 0.1); color:#059669; border:1px solid rgba(16, 185, 129, 0.2); font-size:0.72rem; font-weight:800; padding:2px 7px; border-radius:6px; display:inline-flex; align-items:center; gap:3px; margin-top:3px;">
                                    ✅ Standard Payout
                                </span>
                            <?php endif; ?>
                            <?php if (isset($p['pass_fee']) && (int)$p['pass_fee'] === 0): ?>
                                <span style="font-size:0.72rem; color:#8b5cf6; display:block; margin-top:2px; font-weight:600;" title="5% platform fee deducted from ticket sales for this event">🏷️ Net 95% (Host absorbed 5% fee)</span>
                            <?php else: ?>
                                <span style="font-size:0.72rem; color:rgb(var(--muted)); display:block; margin-top:2px;" title="Buyer covered platform fees">🎟️ Net 100% (Buyer paid fee)</span>
                            <?php endif; ?>
                            <?php if (!empty($eventName)): ?>
                                <span style="font-size:0.74rem; color:rgb(var(--muted)); display:block; margin-top:2px;" title="<?= e($eventName) ?>">Event: <?= e(mb_strimwidth($eventName, 0, 24, '...')) ?></span>
                            <?php endif; ?>
                            <?php if (isset($p['event_net_sales'])): ?>
                                <span style="font-size:0.7rem; color:rgb(var(--muted)); display:block; margin-top:3px; font-family:monospace; line-height:1.35;" title="Event Revenue: <?= format_price($p['event_net_sales']) ?>, Prior Disbursed: <?= format_price($p['event_prior_disbursed'] ?? 0) ?>">
                                    📊 Event Rev: <?= format_price($p['event_net_sales']) ?>
                                    <?php if (($p['event_prior_disbursed'] ?? 0) > 0): ?>
                                        <br>Prior Paid: <?= format_price($p['event_prior_disbursed']) ?>
                                    <?php endif; ?>
                                    <?php if (!empty($p['is_partial'])): ?>
                                        <br><span style="color:#d97706; font-weight:700;">⚠ Partial</span>
                                    <?php endif; ?>
                                </span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($isCompleted): ?>
                                <span class="au-status-badge au-status--active" style="background:rgba(16, 185, 129, 0.12); color:#059669; border:1px solid rgba(16, 185, 129, 0.28); font-weight:700;">
                                    <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="3" style="display:inline-block; vertical-align:-1px; margin-right:3px;"><polyline points="20 6 9 17 4 12"/></svg> Completed
                                </span>
                            <?php elseif ($isFlagged): ?>
                                <span class="au-status-badge au-status--suspended" style="font-weight:700;">
                                    <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" style="display:inline-block; vertical-align:-1px; margin-right:3px;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg> Flagged
                                </span>
                            <?php else: ?>
                                <span class="au-status-badge" style="background:rgba(245, 158, 11, 0.12); color:#d97706; border:1px solid rgba(245, 158, 11, 0.28); font-weight:700;">
                                    <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" style="display:inline-block; vertical-align:-1px; margin-right:3px;"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg> Ready to Pay
                                </span>
                            <?php endif; ?>
                        </td>
                        <td class="au-date-cell">
                            <?= date('M d, Y', strtotime($p['created_at'])) ?><br />
                            <span style="font-size:0.75rem; color:rgb(var(--muted))"><?= date('h:i A', strtotime($p['created_at'])) ?></span>
                        </td>
                        <td>
                            <div class="admin-actions">
                                <?php if ($isCompleted): ?>
                                    <span style="display:inline-flex; align-items:center; gap:4px; font-size:0.78rem; font-weight:700; color:#059669; padding:4px 9px; background:rgba(16,185,129,0.08); border-radius:6px; border:1px solid rgba(16,185,129,0.2);">
                                        ✓ Disbursed
                                    </span>
                                <?php elseif ($isFlagged): ?>
                                    <button type="button" class="admin-action-btn primary au-unflag-btn" data-payout-id="<?= $p['id'] ?>"
                                        data-host-name="<?= e($p['host_name'] ?? 'Host') ?>">
                                        Resolve / Unflag
                                    </button>
                                <?php else: ?>
                                     <button type="button" class="admin-action-btn primary au-process-btn" data-payout-id="<?= $p['id'] ?>"
                                         data-host-name="<?= e($p['host_name'] ?? 'Host') ?>" data-amount="<?= format_price($p['amount']) ?>"
                                         data-bank="<?= e($p['payout_bank'] ?: 'Not Configured') ?>" data-account="<?= e($p['payout_account'] ?: 'Not Configured') ?>"
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
                                     <button type="button" class="admin-action-btn danger au-flag-btn" data-payout-id="<?= $p['id'] ?>"
                                         data-host-name="<?= e($p['host_name'] ?? 'Host') ?>">
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

    <!-- Mobile Card View -->
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
                $eventName = $p['event_name'] ?? '';
            ?>
            <article class="au-user-card">
                <div class="au-card-top">
                    <div class="au-user-cell">
                        <span class="au-avatar" style="--avatar-hue:<?= $hue ?>"><?= $initials ?></span>
                        <div>
                            <span class="au-user-name"><?= e($p['host_name'] ?? 'Organizer') ?></span>
                            <span class="au-card-email"><?= e($p['host_email'] ?? 'N/A') ?></span>
                            <span style="font-size:0.72rem; color:rgb(var(--muted)); font-family:monospace;">ID: #<?= (int)$p['id'] ?></span>
                        </div>
                    </div>
                    <?php if ($isCompleted): ?>
                        <span class="au-status-badge au-status--active" style="background:rgba(16, 185, 129, 0.12); color:#059669; border:1px solid rgba(16, 185, 129, 0.28); font-weight:700;">✓ Completed</span>
                    <?php elseif ($isFlagged): ?>
                        <span class="au-status-badge au-status--suspended" style="font-weight:700;">Flagged</span>
                    <?php else: ?>
                        <span class="au-status-badge" style="background:rgba(245, 158, 11, 0.12); color:#d97706; border:1px solid rgba(245, 158, 11, 0.28); font-weight:700;">Ready to Pay</span>
                    <?php endif; ?>
                </div>
                <div class="au-card-meta" style="flex-direction: column; align-items: flex-start; gap: 0.35rem;">
                    <span class="au-card-date">🏦 <?= e($bankName) ?> (<?= e($accNum) ?>)</span>
                    <div style="display:flex; align-items:center; gap:0.5rem; flex-wrap:wrap;">
                        <span style="font-weight:700;font-size:0.95rem;color:rgb(var(--text))">Amount: <?= format_price($p['amount']) ?></span>
                        <?php if ($isEscrow): ?>
                            <span style="background:rgba(251, 146, 60, 0.15); color:#ea580c; border:1px solid rgba(251, 146, 60, 0.3); font-size:0.72rem; font-weight:800; padding:2px 7px; border-radius:6px;">⚡ Escrow</span>
                        <?php elseif (($p['type'] ?? '') === 'auto_completed'): ?>
                            <span style="background:rgba(59, 130, 246, 0.12); color:#2563eb; border:1px solid rgba(59, 130, 246, 0.3); font-size:0.72rem; font-weight:800; padding:2px 7px; border-radius:6px;">🤖 Auto-Requested</span>
                        <?php else: ?>
                            <span style="background:rgba(16, 185, 129, 0.1); color:#059669; border:1px solid rgba(16, 185, 129, 0.2); font-size:0.72rem; font-weight:800; padding:2px 7px; border-radius:6px;">✅ Standard</span>
                        <?php endif; ?>
                    </div>
                    <?php if (isset($p['pass_fee']) && (int)$p['pass_fee'] === 0): ?>
                        <span style="font-size:0.72rem; color:#8b5cf6; font-weight:600;">🏷️ Net 95% (Host absorbed 5% fee)</span>
                    <?php else: ?>
                        <span style="font-size:0.72rem; color:rgb(var(--muted));">🎟️ Net 100% (Buyer paid fee)</span>
                    <?php endif; ?>
                    <?php if (!empty($eventName)): ?>
                        <span style="font-size:0.76rem; color:rgb(var(--muted));">Event: <?= e($eventName) ?></span>
                    <?php endif; ?>
                    <?php if (isset($p['event_net_sales'])): ?>
                        <span style="font-size:0.72rem; color:rgb(var(--muted)); font-family:monospace; line-height:1.3;">
                            📊 Event Rev: <?= format_price($p['event_net_sales']) ?>
                            <?php if (($p['event_prior_disbursed'] ?? 0) > 0): ?>
                                · Prior: <?= format_price($p['event_prior_disbursed']) ?>
                            <?php endif; ?>
                            <?php if (!empty($p['is_partial'])): ?>
                                <span style="color:#d97706; font-weight:700;">⚠ Partial</span>
                            <?php endif; ?>
                        </span>
                    <?php endif; ?>
                    <span style="font-size:0.74rem; color:rgb(var(--muted));">Requested: <?= date('M d, Y h:i A', strtotime($p['created_at'])) ?></span>
                </div>
                <div class="admin-actions au-card-actions">
                    <?php if ($isCompleted): ?>
                        <span style="display:inline-flex; align-items:center; gap:4px; font-size:0.78rem; font-weight:700; color:#059669; padding:4px 9px; background:rgba(16,185,129,0.08); border-radius:6px; border:1px solid rgba(16,185,129,0.2);">
                            ✓ Disbursed
                        </span>
                    <?php elseif ($isFlagged): ?>
                        <button type="button" class="admin-action-btn primary au-unflag-btn" data-payout-id="<?= $p['id'] ?>"
                            data-host-name="<?= e($p['host_name'] ?? 'Host') ?>">Resolve / Unflag</button>
                    <?php else: ?>
                        <button type="button" class="admin-action-btn primary au-process-btn" data-payout-id="<?= $p['id'] ?>"
                            data-host-name="<?= e($p['host_name'] ?? 'Host') ?>" data-amount="<?= format_price($p['amount']) ?>"
                            data-bank="<?= e($p['payout_bank'] ?: 'Not Configured') ?>" data-account="<?= e($p['payout_account'] ?: 'Not Configured') ?>"
                            data-routing="<?= e($p['payout_routing'] ?: 'N/A') ?>"
                            data-payout-type="<?= e($p['type'] ?? 'completed') ?>"
                            data-pass-fee="<?= isset($p['pass_fee']) ? (int)$p['pass_fee'] : 1 ?>"
                            data-event-name="<?= e($p['event_name'] ?? 'N/A') ?>"
                            data-event-tickets="<?= (int)($p['event_tickets_count'] ?? 0) ?>"
                            data-event-gross="<?= number_format($p['event_gross_sales'] ?? 0, 2, '.', '') ?>"
                            data-event-net="<?= number_format($p['event_net_sales'] ?? 0, 2, '.', '') ?>"
                            data-event-prior="<?= number_format($p['event_prior_disbursed'] ?? 0, 2, '.', '') ?>"
                            data-event-remaining="<?= number_format($p['event_remaining_balance'] ?? 0, 2, '.', '') ?>"
                            data-is-partial="<?= !empty($p['is_partial']) ? '1' : '0' ?>">Process Payout</button>
                        <button type="button" class="admin-action-btn danger au-flag-btn" data-payout-id="<?= $p['id'] ?>"
                            data-host-name="<?= e($p['host_name'] ?? 'Host') ?>">Flag</button>
                    <?php endif; ?>
                </div>
            </article>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Empty State -->
    <div id="payouts-empty" class="empty-state <?= !empty($payouts) ? 'hidden' : '' ?>">
        <p>No payout accounts found matching your search.</p>
    </div>

    <!-- Pagination -->
    <?php if (($totalPages ?? 1) > 1): ?>
    <nav class="au-pagination" aria-label="Payouts pagination" id="payouts-pagination">
        <?php if ($page > 1): ?>
            <a href="?page=<?= $page - 1 ?>&q=<?= urlencode($search ?? '') ?>&status=<?= urlencode($status ?? '') ?>&type=<?= urlencode($type ?? '') ?>" class="au-page-btn">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5"
                    stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="15 18 9 12 15 6" />
                </svg>
                Prev
            </a>
        <?php else: ?>
            <span class="au-page-btn disabled" aria-disabled="true">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5"
                    stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="15 18 9 12 15 6" />
                </svg>
                Prev
            </span>
        <?php endif; ?>

        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <?php if ($i === $page): ?>
                <span class="au-page-num active" aria-current="page"><?= $i ?></span>
            <?php else: ?>
                <a href="?page=<?= $i ?>&q=<?= urlencode($search ?? '') ?>&status=<?= urlencode($status ?? '') ?>&type=<?= urlencode($type ?? '') ?>" class="au-page-num"><?= $i ?></a>
            <?php endif; ?>
        <?php endfor; ?>

        <?php if ($page < $totalPages): ?>
            <a href="?page=<?= $page + 1 ?>&q=<?= urlencode($search ?? '') ?>&status=<?= urlencode($status ?? '') ?>&type=<?= urlencode($type ?? '') ?>" class="au-page-btn">
                Next
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5"
                    stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="9 18 15 12 9 6" />
                </svg>
            </a>
        <?php else: ?>
            <span class="au-page-btn disabled" aria-disabled="true">
                Next
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5"
                    stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="9 18 15 12 9 6" />
                </svg>
            </span>
        <?php endif; ?>
    </nav>
    <?php endif; ?>
</section>

<!-- Confirm Process Payout Modal -->
<div class="modal-overlay hidden" id="process-modal" role="dialog" aria-modal="true"
    aria-labelledby="process-modal-title">
    <div class="modal-card" style="max-width: 480px;">
        <h3 id="process-modal-title">Process Host Payout</h3>
        <p>You are processing a payout of <strong id="process-payout-amount" style="color:rgb(var(--brand));font-size:1.1rem;">$0.00</strong> to <strong id="process-host-name">this host</strong>.</p>

        <!-- Fee Deduction Policy Notice Banner -->
        <div id="process-fee-banner" style="background:rgba(var(--muted), 0.06); border:1px solid rgba(var(--border), 0.7); border-radius:10px; padding:0.65rem 0.85rem; margin: 0.75rem 0 0.85rem; font-size:0.82rem;">
            <div id="process-fee-desc" style="color:rgb(var(--text)); line-height:1.4;">—</div>
        </div>

        <!-- Escrow Release Alert Banner -->
        <div id="process-escrow-warning" style="display:none; background:rgba(251, 146, 60, 0.12); border:1px solid rgba(251, 146, 60, 0.3); border-radius:12px; padding:0.85rem 1rem; margin: 0.85rem 0 1rem;">
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
        <div id="process-revenue-breakdown" style="display:none; background:rgba(59, 130, 246, 0.06); border:1px solid rgba(59, 130, 246, 0.2); border-radius:12px; padding:0.85rem 1rem; margin: 0.85rem 0 1rem;">
            <strong style="color:#1d4ed8; font-size:0.85rem; display:block; margin-bottom:0.5rem;">📊 Event Revenue & Settlement Breakdown</strong>
            <table style="width:100%; font-size:0.8rem; border-collapse:collapse; color:rgb(var(--text));">
                <tr>
                    <td style="padding:3px 0;">Tickets Sold</td>
                    <td style="padding:3px 0; text-align:right; font-weight:600;" id="process-tickets-sold">—</td>
                </tr>
                <tr>
                    <td style="padding:3px 0;">Gross Event Sales</td>
                    <td style="padding:3px 0; text-align:right; font-weight:600;" id="process-gross-sales">—</td>
                </tr>
                <tr style="border-bottom:1px solid rgba(59, 130, 246, 0.15);">
                    <td style="padding:3px 0;">Net Event Sales (Host Earnings)</td>
                    <td style="padding:3px 0; text-align:right; font-weight:700; color:#1d4ed8;" id="process-net-sales">—</td>
                </tr>
                <tr>
                    <td style="padding:3px 0;">Prior Disbursed</td>
                    <td style="padding:3px 0; text-align:right; font-weight:600;" id="process-prior-disbursed">—</td>
                </tr>
                <tr style="border-bottom:1px solid rgba(59, 130, 246, 0.15);">
                    <td style="padding:3px 0; font-weight:700;">This Payout</td>
                    <td style="padding:3px 0; text-align:right; font-weight:700; color:rgb(var(--brand));" id="process-this-amount">—</td>
                </tr>
                <tr>
                    <td style="padding:3px 0; font-weight:700;">Remaining After This Payout</td>
                    <td style="padding:3px 0; text-align:right; font-weight:700;" id="process-remaining">—</td>
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

            <div style="margin: 1.25rem 0; background: rgba(var(--muted), 0.05); padding: 1rem; border-radius: 12px; border: 1px solid rgba(var(--muted), 0.15);">
                <label style="font-weight: 600; font-size: 0.85rem; display: block; margin-bottom: 0.5rem; color: rgb(var(--text));">Disbursement Method</label>
                
                <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer; font-size: 0.9rem; margin-bottom: 0.65rem;">
                    <input type="radio" name="mode" value="paystack" checked />
                    <span>⚡ <strong>Paystack Direct Transfer</strong> (Instant NUBAN Transfer)</span>
                </label>

                <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer; font-size: 0.9rem; margin-bottom: 0.65rem;">
                    <input type="radio" name="mode" value="flutterwave" />
                    <span>🦋 <strong>Flutterwave Direct Transfer</strong> (Instant Bank Payout)</span>
                </label>

                <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer; font-size: 0.9rem;">
                    <input type="radio" name="mode" value="manual" />
                    <span>📝 <strong>Manual Mark as Paid</strong> (Bank App / Cash / Offline)</span>
                </label>
            </div>

            <!-- Manual Bank Details Panel (shown when Manual is selected) -->
            <div id="manual-bank-details" style="display:none; margin: 0 0 1.25rem 0; background: rgba(16, 185, 129, 0.06); padding: 1rem 1.15rem; border-radius: 12px; border: 1px solid rgba(16, 185, 129, 0.2);">
                <p style="font-weight: 700; font-size: 0.85rem; color: #10b981; margin: 0 0 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">🏦 Transfer Funds To This Account</p>
                <div style="display: grid; grid-template-columns: auto 1fr; gap: 0.35rem 1rem; font-size: 0.9rem;">
                    <span style="color: var(--text-muted); font-weight: 600;">Bank:</span>
                    <span id="manual-bank-name" style="font-weight: 700; color: rgb(var(--text));">—</span>
                    <span style="color: var(--text-muted); font-weight: 600;">Account No:</span>
                    <span id="manual-account-number" style="font-family: monospace; font-weight: 700; font-size: 1rem; color: rgb(var(--text)); letter-spacing: 1px;">—</span>
                    <span style="color: var(--text-muted); font-weight: 600;">Routing:</span>
                    <span id="manual-routing-number" style="font-family: monospace; font-weight: 700; color: rgb(var(--text));">—</span>
                    <span style="color: var(--text-muted); font-weight: 600;">Amount:</span>
                    <span id="manual-payout-amount" style="font-weight: 800; font-size: 1.05rem; color: #10b981;">—</span>
                </div>
                <p style="margin: 0.75rem 0 0; font-size: 0.78rem; color: var(--text-muted); line-height: 1.4;">
                    Transfer the exact amount above via your bank app or internet banking, then click <strong>"Confirm Transfer Complete"</strong> below to mark this payout as processed.
                </p>
            </div>

            <div class="modal-actions">
                <button type="button" class="admin-action-btn" id="process-cancel-btn">Cancel</button>
                <button type="submit" class="admin-action-btn primary" id="process-submit-btn">Confirm &amp; Disburse</button>
            </div>
        </form>
    </div>
</div>

<!-- Confirm Flag Host Payout Modal -->
<div class="modal-overlay hidden" id="flag-modal" role="dialog" aria-modal="true" aria-labelledby="flag-modal-title">
    <div class="modal-card">
        <h3 id="flag-modal-title">Flag Payout Account</h3>
        <p>Are you sure you want to flag <strong id="flag-host-name">this host</strong>'s payout account? All pending
            disbursements will be frozen until resolved.</p>
        <form method="POST" action="{{ route('admin/payouts') }}">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="flag_account" />
            <input type="hidden" name="payout_id" id="flag-payout-id" value="" />
            <div class="modal-actions">
                <button type="button" class="admin-action-btn" id="flag-cancel-btn">Cancel</button>
                <button type="submit" class="admin-action-btn danger">Yes, Flag Account</button>
            </div>
        </form>
    </div>
</div>

<!-- Confirm Resolve / Unflag Host Modal -->
<div class="modal-overlay hidden" id="unflag-modal" role="dialog" aria-modal="true"
    aria-labelledby="unflag-modal-title">
    <div class="modal-card">
        <h3 id="unflag-modal-title">Resolve Payout Issue</h3>
        <p>Are you sure you want to resolve the hold on <strong id="unflag-host-name">this host</strong>'s account?
            Their status will be restored to "Ready".</p>
        <form method="POST" action="{{ route('admin/payouts') }}">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="unflag_account" />
            <input type="hidden" name="payout_id" id="unflag-payout-id" value="" />
            <div class="modal-actions">
                <button type="button" class="admin-action-btn" id="unflag-cancel-btn">Cancel</button>
                <button type="submit" class="admin-action-btn primary">Resolve Hold</button>
            </div>
        </form>
    </div>
</div>
@endsection