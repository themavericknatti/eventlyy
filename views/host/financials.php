<?php

/**
 * Host Financials & Payouts View
 *
 * Comprehensive financials, per-event revenue breakdown, and payout transactions history.
 *
 * @package Evently
 * @subpackage Views/Host
 * @author Celio Natti
 * @version 1.0.0
 */

$siteName = site_setting('site_title', 'Eventlyy');
$bankConfigured = !empty($profile['payout_bank']) && !empty($profile['payout_account']);
?>

@extends('layouts/host')

@section('title')
Financials &amp; Payouts - <?= e($siteName) ?> Host
@endsection

@section('content')
<!-- Page Header -->
<div class="event-mgmt-header" style="margin-bottom:1.5rem;">
    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:1rem; margin-bottom:1rem;">
        <a href="{{ route('host/dashboard') }}" class="back-link" style="display:inline-flex; align-items:center; gap:0.4rem; font-weight:600; text-decoration:none;">
            ← Back to Dashboard
        </a>
        <div style="display:flex; gap:0.75rem; align-items:center; flex-wrap:wrap;">
            <a href="{{ route('host/settings') }}" class="admin-action-btn" style="padding:0.45rem 0.9rem; font-size:0.84rem; border-radius:8px; display:inline-flex; align-items:center; gap:0.35rem; text-decoration:none;">
                ⚙️ Payout Settings
            </a>
        </div>
    </div>

    <section class="page-hero" style="padding:1.5rem; background:rgb(var(--card)); border:1px solid rgba(var(--muted), 0.15); border-radius:16px;">
        <div style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:1.25rem;">
            <div>
                <span class="panel-badge" style="background:rgba(16, 185, 129, 0.12); color:#10b981; border:1px solid rgba(16, 185, 129, 0.25);">💳 Host Financials &amp; Payouts</span>
                <h1 style="margin:0.35rem 0 0.5rem; font-size:clamp(1.5rem, 2.5vw, 2rem);">Earnings, Revenue &amp; Withdrawals</h1>
                <p class="lede" style="margin:0; font-size:0.92rem; color:rgb(var(--muted));">
                    Track your total net earnings, per-event ticket revenues, fee deductions, and withdrawal transactions.
                </p>
            </div>
            
            <!-- Linked Bank Badge -->
            <div style="background:rgba(var(--muted), 0.05); border:1px solid rgba(var(--muted), 0.15); border-radius:12px; padding:0.85rem 1.1rem; min-width:240px;">
                <span style="font-size:0.75rem; text-transform:uppercase; font-weight:700; color:rgb(var(--muted)); letter-spacing:0.04em; display:block; margin-bottom:0.25rem;">Destination Payout Bank</span>
                <?php if ($bankConfigured): ?>
                    <div style="font-weight:700; color:rgb(var(--text)); font-size:0.95rem; display:flex; align-items:center; gap:0.4rem;">
                        <span>🏦</span> <?= e($profile['payout_bank']) ?>
                    </div>
                    <div style="font-size:0.82rem; color:rgb(var(--muted)); font-family:monospace; margin-top:2px;">
                        Acc: <?= e($profile['payout_account']) ?>
                        <span style="color:#10b981; font-weight:700; margin-left:4px;">● Verified</span>
                    </div>
                <?php else: ?>
                    <div style="font-size:0.85rem; color:#ef4444; font-weight:600; display:flex; align-items:center; gap:0.35rem;">
                        <span>⚠️</span> Bank Not Configured
                    </div>
                    <a href="{{ route('host/settings') }}" style="font-size:0.78rem; color:rgb(var(--brand)); font-weight:700; text-decoration:underline; display:block; margin-top:2px;">Set up payout account →</a>
                <?php endif; ?>
            </div>
        </div>
    </section>
</div>

<!-- Primary Financial Stats Grid -->
<section class="host-summary-grid" style="grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
    <!-- Lifetime Net Earnings -->
    <article class="summary-card">
        <span class="card-label">Lifetime Net Earnings</span>
        <h3 class="card-value" style="color:rgb(var(--text));"><?= format_price($totalNetRevenue ?? 0) ?></h3>
        <?php if (($totalPlatformFees ?? 0) > 0): ?>
            <p class="card-meta" style="font-size:0.78rem;">Gross sales: <?= format_price($totalRevenue ?? 0) ?></p>
            <p class="card-meta" style="font-size:0.75rem; color:#ef4444;">− <?= format_price($totalPlatformFees ?? 0) ?> platform fee (5%)</p>
        <?php else: ?>
            <p class="card-meta" style="font-size:0.78rem;">100% of all ticket sales</p>
        <?php endif; ?>
    </article>

    <!-- Available Completed Balance -->
    <article class="summary-card" style="display:flex; flex-direction:column; justify-content:space-between; border-color: rgba(16, 185, 129, 0.3); background: rgba(16, 185, 129, 0.04);">
        <div>
            <span class="card-label" style="color: #10b981; font-weight: 700;">Available to Withdraw</span>
            <h3 class="card-value" style="color: #10b981;"><?= format_price($availableBalance ?? 0) ?></h3>
            <p class="card-meta" style="font-size:0.78rem;">From completed events</p>
        </div>
        <form method="POST" action="{{ route('host/withdraw') }}" style="margin-top:0.75rem; width:100%;">
            <?= csrf_field() ?>
            <input type="hidden" name="withdraw_type" value="completed">
            <?php if (($availableBalance ?? 0.00) > 0 && $bankConfigured): ?>
                <button type="submit" class="checkout-btn" style="padding:0.5rem 1rem; font-size:0.85rem; height:auto; width:100%; text-align:center;">
                    💸 Request Standard Payout
                </button>
            <?php else: ?>
                <button type="button" class="checkout-btn secondary" disabled style="padding:0.5rem 1rem; font-size:0.85rem; height:auto; width:100%; opacity:0.5; cursor:not-allowed;">
                    <?= !$bankConfigured ? 'Configure Bank First' : 'No Balance Available' ?>
                </button>
            <?php endif; ?>
        </form>
    </article>

    <!-- Escrow Balance (Upcoming Events) -->
    <article class="summary-card" style="display:flex; flex-direction:column; justify-content:space-between; border-color: rgba(251, 146, 60, 0.3); background: rgba(251, 146, 60, 0.04);">
        <div>
            <span class="card-label" style="color: #ea580c; font-weight: 700;">🔒 Escrow Balance (Live/Upcoming)</span>
            <h3 class="card-value" style="color: #ea580c;"><?= format_price($escrowRevenue ?? 0) ?></h3>
            <p class="card-meta" style="font-size:0.78rem;">Withdrawable: <strong><?= format_price($availableEscrowBalance ?? 0) ?></strong></p>
        </div>
        <div style="margin-top:0.75rem; width:100%;">
            <?php if (($availableEscrowBalance ?? 0.00) > 0 && $bankConfigured): ?>
                <button type="button" id="financials-escrow-btn" class="checkout-btn" style="padding:0.5rem 1rem; font-size:0.85rem; height:auto; width:100%; text-align:center; background:#ea580c; border-color:#ea580c;">
                    ⚡ Request Early Escrow Release
                </button>
            <?php else: ?>
                <button type="button" class="checkout-btn secondary" disabled style="padding:0.5rem 1rem; font-size:0.85rem; height:auto; width:100%; opacity:0.5; cursor:not-allowed;">
                    <?= !$bankConfigured ? 'Configure Bank First' : 'No Escrow Available' ?>
                </button>
            <?php endif; ?>
        </div>
    </article>

    <!-- Disbursed to Bank -->
    <article class="summary-card">
        <span class="card-label">Total Disbursed (Paid Out)</span>
        <h3 class="card-value" style="color:#0284c7;"><?= format_price($disbursedPayouts ?? 0) ?></h3>
        <p class="card-meta" style="font-size:0.78rem;">Sent to your bank account</p>
    </article>
</section>

<!-- Secondary Stats Row -->
<section class="host-summary-grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 2rem;">
    <article class="summary-card" style="min-height:auto; padding:1.1rem 1.25rem;">
        <span class="card-label" style="font-size:0.82rem;">Pending / Processing Payouts</span>
        <h4 style="margin:0; font-size:1.25rem; font-weight:700; color:#d97706;"><?= format_price($pendingPayouts ?? 0) ?></h4>
        <p class="card-meta" style="font-size:0.75rem;">Awaiting admin transfer</p>
    </article>
    <article class="summary-card" style="min-height:auto; padding:1.1rem 1.25rem;">
        <span class="card-label" style="font-size:0.82rem;">Total Platform Fee Deductions</span>
        <h4 style="margin:0; font-size:1.25rem; font-weight:700; color:rgb(var(--muted));"><?= format_price($totalPlatformFees ?? 0) ?></h4>
        <p class="card-meta" style="font-size:0.75rem;">5% fee where host absorbed fee</p>
    </article>
    <article class="summary-card" style="min-height:auto; padding:1.1rem 1.25rem;">
        <span class="card-label" style="font-size:0.82rem;">Online Tickets Sold</span>
        <h4 style="margin:0; font-size:1.25rem; font-weight:700; color:rgb(var(--text));"><?= number_format($totalTicketsSold ?? 0) ?></h4>
        <p class="card-meta" style="font-size:0.75rem;">Online platform purchases</p>
    </article>
    <article class="summary-card" style="min-height:auto; padding:1.1rem 1.25rem;">
        <span class="card-label" style="font-size:0.82rem;">Events Hosted</span>
        <h4 style="margin:0; font-size:1.25rem; font-weight:700; color:rgb(var(--text));"><?= number_format($totalEventsCount ?? 0) ?></h4>
        <p class="card-meta" style="font-size:0.75rem;">All-time created events</p>
    </article>
</section>

<!-- Interactive Navigation Tabs -->
<div class="dashboard-tabs" style="margin-bottom:1.5rem;">
    <button type="button" class="tab-btn active" id="tab-btn-events" onclick="switchFinancialTab('events')">
        📊 Per-Event Earnings Breakdown (<?= count($eventFinancials ?? []) ?>)
    </button>
    <button type="button" class="tab-btn" id="tab-btn-history" onclick="switchFinancialTab('history')">
        💳 Withdrawal &amp; Payout History (<?= count($payoutHistory ?? []) ?>)
    </button>
    <button type="button" class="tab-btn" id="tab-btn-guide" onclick="switchFinancialTab('guide')">
        ℹ️ How Payouts &amp; Fees Work
    </button>
</div>

<!-- ======================================================== -->
<!-- TAB 1: PER-EVENT EARNINGS BREAKDOWN                      -->
<!-- ======================================================== -->
<div id="tab-content-events" class="financial-tab-panel">
    <section class="form-card" style="padding:1.5rem; margin-bottom:2rem;">
        <div class="card-header" style="margin-bottom:1.25rem; border-bottom:none; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:0.75rem;">
            <div>
                <h2 style="margin:0; font-size:1.25rem;">Event Revenue &amp; Settlement Tally</h2>
                <p class="section-note" style="margin:0.25rem 0 0;">Exact gross ticket sales, platform fee policy, disbursements, and unrequested balances per event.</p>
            </div>
            <input type="text" id="event-search-input" placeholder="Search events..." onkeyup="filterEventsTable()" style="padding:0.5rem 0.85rem; border-radius:8px; border:1px solid rgba(var(--muted),0.2); background:rgb(var(--card)); color:rgb(var(--text)); font-size:0.85rem; min-width:220px;" />
        </div>

        <?php if (empty($eventFinancials)): ?>
            <div class="empty-state" style="padding:2.5rem 1rem; text-align:center;">
                <span style="font-size:2.5rem; display:block; margin-bottom:0.75rem;">🎟️</span>
                <p style="margin:0 0 1rem; font-weight:600; color:rgb(var(--muted));">No events found in your account.</p>
                <a href="{{ route('host/event/create') }}" class="checkout-btn" style="display:inline-block; text-decoration:none; padding:0.6rem 1.25rem;">Create Your First Event</a>
            </div>
        <?php else: ?>
            <!-- Desktop Table -->
            <div class="admin-table-wrap" style="overflow-x:auto;">
                <table class="admin-table" id="events-breakdown-table" style="width:100%; border-collapse:collapse;">
                    <thead>
                        <tr>
                            <th style="min-width:220px;">Event</th>
                            <th>Status &amp; Fee Policy</th>
                            <th>Tickets</th>
                            <th>Gross Sales</th>
                            <th>Host Net Share</th>
                            <th>Disbursed So Far</th>
                            <th>Remaining Balance</th>
                            <th style="text-align:right;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($eventFinancials as $ev): ?>
                            <?php 
                                $isPassFee = (int)($ev['pass_fee'] ?? 1) === 1;
                                $hasRemainder = ($ev['unrequested_balance'] ?? 0) > 0;
                            ?>
                            <tr class="event-breakdown-row" data-search="<?= strtolower(e($ev['name'] . ' ' . $ev['location'])) ?>">
                                <td>
                                    <strong style="color:rgb(var(--text)); font-size:0.95rem; display:block;"><?= e($ev['name']) ?></strong>
                                    <span style="font-size:0.78rem; color:rgb(var(--muted)); display:block; margin-top:2px;">
                                        📅 <?= date('M d, Y', strtotime($ev['date'])) ?> · <?= e($ev['location']) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($ev['state_badge'] === 'completed'): ?>
                                        <span style="background:rgba(100, 116, 139, 0.12); color:rgb(100, 116, 139); font-size:0.72rem; font-weight:700; padding:2px 7px; border-radius:6px; display:inline-block;">
                                            ✓ Completed
                                        </span>
                                    <?php elseif ($ev['state_badge'] === 'live'): ?>
                                        <span style="background:rgba(239, 68, 68, 0.12); color:#ef4444; font-size:0.72rem; font-weight:700; padding:2px 7px; border-radius:6px; display:inline-block;">
                                            🔴 Live Now
                                        </span>
                                    <?php else: ?>
                                        <span style="background:rgba(16, 185, 129, 0.12); color:#10b981; font-size:0.72rem; font-weight:700; padding:2px 7px; border-radius:6px; display:inline-block;">
                                            📅 Upcoming
                                        </span>
                                    <?php endif; ?>

                                    <?php if ($isPassFee): ?>
                                        <span style="font-size:0.72rem; color:rgb(var(--muted)); display:block; margin-top:3px;" title="Buyer covered 5% fee at checkout">🎟️ Net 100% (Buyer paid fee)</span>
                                    <?php else: ?>
                                        <span style="font-size:0.72rem; color:#8b5cf6; font-weight:600; display:block; margin-top:3px;" title="5% platform fee deducted from ticket price">🏷️ Net 95% (Host absorbed fee)</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong style="color:rgb(var(--text));"><?= number_format($ev['ticket_count']) ?></strong>
                                    <span style="font-size:0.75rem; color:rgb(var(--muted)); display:block;">online sold</span>
                                    <?php if (($ev['issued_count'] ?? 0) > 0): ?>
                                        <span style="font-size:0.72rem; color:#6366f1; display:block;">+<?= number_format($ev['issued_count']) ?> issued</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong style="color:rgb(var(--text));"><?= format_price($ev['gross_sales']) ?></strong>
                                </td>
                                <td>
                                    <strong style="color:#10b981; font-size:0.95rem;"><?= format_price($ev['net_sales']) ?></strong>
                                    <?php if ($ev['platform_fees'] > 0): ?>
                                        <span style="font-size:0.72rem; color:#ef4444; display:block;">−<?= format_price($ev['platform_fees']) ?> fee</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong><?= format_price($ev['total_disbursed']) ?></strong>
                                    <?php if ($ev['escrow_disbursed'] > 0): ?>
                                        <span style="font-size:0.72rem; color:#ea580c; display:block;">⚡ <?= format_price($ev['escrow_disbursed']) ?> via escrow</span>
                                    <?php endif; ?>
                                    <?php if ($ev['pending_amount'] > 0): ?>
                                        <span style="font-size:0.72rem; color:#d97706; display:block;">⏳ <?= format_price($ev['pending_amount']) ?> pending</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($hasRemainder): ?>
                                        <strong style="color:#d97706; font-size:0.95rem;"><?= format_price($ev['unrequested_balance']) ?></strong>
                                        <span style="font-size:0.72rem; color:#ea580c; display:block; font-weight:600;">⚡ Unrequested</span>
                                    <?php else: ?>
                                        <span style="color:#059669; font-weight:700; font-size:0.85rem;">✓ Fully Settled</span>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align:right;">
                                    <a href="{{ route('host/event/management/' . $ev['id']) }}" class="admin-action-btn" style="padding:0.35rem 0.75rem; font-size:0.8rem; border-radius:6px; text-decoration:none; display:inline-block;">
                                        Manage →
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>
</div>

<!-- ======================================================== -->
<!-- TAB 2: WITHDRAWAL & PAYOUT TRANSACTIONS HISTORY          -->
<!-- ======================================================== -->
<div id="tab-content-history" class="financial-tab-panel" style="display:none;">
    <section class="form-card" style="padding:1.5rem; margin-bottom:2rem;">
        <div class="card-header" style="margin-bottom:1.25rem; border-bottom:none; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:0.75rem;">
            <div>
                <h2 style="margin:0; font-size:1.25rem;">Withdrawal &amp; Payout Transactions</h2>
                <p class="section-note" style="margin:0.25rem 0 0;">All payout requests, escrow withdrawals, and auto-settlements processed to your bank.</p>
            </div>
            <input type="text" id="payout-search-input" placeholder="Search payouts..." onkeyup="filterPayoutsTable()" style="padding:0.5rem 0.85rem; border-radius:8px; border:1px solid rgba(var(--muted),0.2); background:rgb(var(--card)); color:rgb(var(--text)); font-size:0.85rem; min-width:220px;" />
        </div>

        <?php if (empty($payoutHistory)): ?>
            <div class="empty-state" style="padding:2.5rem 1rem; text-align:center;">
                <span style="font-size:2.5rem; display:block; margin-bottom:0.75rem;">💸</span>
                <p style="margin:0 0 1rem; font-weight:600; color:rgb(var(--muted));">No withdrawal requests recorded yet.</p>
                <?php if (($availableBalance ?? 0) > 0): ?>
                    <form method="POST" action="{{ route('host/withdraw') }}" style="display:inline-block;">
                        <?= csrf_field() ?>
                        <input type="hidden" name="withdraw_type" value="completed">
                        <button type="submit" class="checkout-btn" style="padding:0.6rem 1.25rem;">Request Payout Now</button>
                    </form>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="admin-table-wrap" style="overflow-x:auto;">
                <table class="admin-table" id="payouts-history-table" style="width:100%; border-collapse:collapse;">
                    <thead>
                        <tr>
                            <th>Ref #</th>
                            <th>Event Attribution</th>
                            <th>Withdrawal Type</th>
                            <th>Amount</th>
                            <th>Destination Bank</th>
                            <th>Status</th>
                            <th style="text-align:right;">Date Requested</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($payoutHistory as $p): ?>
                            <?php
                                $type = $p['type'] ?? 'completed';
                                $status = $p['status'] ?? 'pending';
                                $isCompleted = in_array($status, ['processed', 'completed']);
                                $isPending = in_array($status, ['pending', 'ready']);
                                $isFailed = in_array($status, ['failed', 'flagged']);
                            ?>
                            <tr class="payout-history-row" data-search="<?= strtolower(e(($p['id'] ?? '') . ' ' . ($p['event_name'] ?? '') . ' ' . $type . ' ' . $status)) ?>">
                                <td>
                                    <strong style="color:rgb(var(--text)); font-family:monospace; font-size:0.92rem;">#<?= e($p['id']) ?></strong>
                                </td>
                                <td>
                                    <?php if (!empty($p['event_name'])): ?>
                                        <strong style="color:rgb(var(--text)); font-size:0.92rem; display:block;"><?= e($p['event_name']) ?></strong>
                                        <span style="font-size:0.75rem; color:rgb(var(--muted)); display:block; margin-top:2px;">
                                            Event ID: #<?= e($p['event_id']) ?>
                                        </span>
                                    <?php else: ?>
                                        <span style="color:rgb(var(--muted)); font-style:italic;">General Balance</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($type === 'escrow'): ?>
                                        <span style="background:rgba(251, 146, 60, 0.15); color:#ea580c; border:1px solid rgba(251, 146, 60, 0.3); font-size:0.72rem; font-weight:800; padding:3px 8px; border-radius:6px; display:inline-flex; align-items:center; gap:4px;">
                                            ⚡ Escrow Early Release
                                        </span>
                                    <?php elseif ($type === 'auto_completed'): ?>
                                        <span style="background:rgba(59, 130, 246, 0.12); color:#2563eb; border:1px solid rgba(59, 130, 246, 0.3); font-size:0.72rem; font-weight:800; padding:3px 8px; border-radius:6px; display:inline-flex; align-items:center; gap:4px;">
                                            🤖 Auto-Requested
                                        </span>
                                    <?php else: ?>
                                        <span style="background:rgba(16, 185, 129, 0.1); color:#059669; border:1px solid rgba(16, 185, 129, 0.2); font-size:0.72rem; font-weight:800; padding:3px 8px; border-radius:6px; display:inline-flex; align-items:center; gap:4px;">
                                            ✅ Standard Payout
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong style="color:rgb(var(--text)); font-size:1.02rem;"><?= format_price($p['amount']) ?></strong>
                                </td>
                                <td>
                                    <div style="font-size:0.85rem; font-weight:600; color:rgb(var(--text));">
                                        🏦 <?= e($p['payout_bank'] ?? ($profile['payout_bank'] ?? 'Bank')) ?>
                                    </div>
                                    <div style="font-size:0.75rem; color:rgb(var(--muted)); font-family:monospace;">
                                        Acc: <?= e($p['payout_account'] ?? ($profile['payout_account'] ?? '—')) ?>
                                    </div>
                                </td>
                                <td>
                                    <?php if ($isCompleted): ?>
                                        <span style="background:rgba(16, 185, 129, 0.12); color:#059669; border:1px solid rgba(16, 185, 129, 0.28); font-size:0.76rem; font-weight:700; padding:3px 9px; border-radius:20px; display:inline-flex; align-items:center; gap:4px;">
                                            <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg> Disbursed
                                        </span>
                                    <?php elseif ($isFailed): ?>
                                        <span style="background:rgba(239, 68, 68, 0.12); color:#dc2626; border:1px solid rgba(239, 68, 68, 0.25); font-size:0.76rem; font-weight:700; padding:3px 9px; border-radius:20px; display:inline-flex; align-items:center; gap:4px;">
                                            ⚠️ Flagged / On Hold
                                        </span>
                                    <?php else: ?>
                                        <span style="background:rgba(245, 158, 11, 0.12); color:#b45309; border:1px solid rgba(245, 158, 11, 0.25); font-size:0.76rem; font-weight:700; padding:3px 9px; border-radius:20px; display:inline-flex; align-items:center; gap:4px;">
                                            ⏳ In Review
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align:right; font-size:0.82rem; color:rgb(var(--muted));">
                                    <?= date('M d, Y • g:i A', strtotime($p['created_at'])) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>
</div>

<!-- ======================================================== -->
<!-- TAB 3: HOW PAYOUTS & FEE SETTLEMENTS WORK                -->
<!-- ======================================================== -->
<div id="tab-content-guide" class="financial-tab-panel" style="display:none;">
    <section class="form-card" style="padding:1.75rem; margin-bottom:2rem;">
        <h2 style="margin:0 0 0.5rem; font-size:1.3rem;">Understanding Your Host Earnings &amp; Payouts</h2>
        <p class="section-note" style="margin:0 0 1.5rem;">Everything you need to know about ticket revenue, platform fee policies, escrow security, and withdrawals.</p>

        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(280px, 1fr)); gap:1.25rem;">
            <!-- Standard Payouts -->
            <div style="background:rgba(16, 185, 129, 0.04); border:1px solid rgba(16, 185, 129, 0.2); border-radius:14px; padding:1.25rem;">
                <div style="font-size:1.5rem; margin-bottom:0.5rem;">💸</div>
                <h3 style="margin:0 0 0.4rem; font-size:1.05rem; font-weight:700; color:#059669;">1. Standard Event Payouts</h3>
                <p style="margin:0; font-size:0.85rem; line-height:1.55; color:rgb(var(--text));">
                    Once your event ends and is marked completed, <strong>100% of your remaining net ticket sales</strong> become immediately available in your <strong>Available Balance</strong> to request a payout straight to your bank account.
                </p>
            </div>

            <!-- Auto-Requested Payouts -->
            <div style="background:rgba(59, 130, 246, 0.04); border:1px solid rgba(59, 130, 246, 0.2); border-radius:14px; padding:1.25rem;">
                <div style="font-size:1.5rem; margin-bottom:0.5rem;">🤖</div>
                <h3 style="margin:0 0 0.4rem; font-size:1.05rem; font-weight:700; color:#2563eb;">2. Automatic Payout Generation</h3>
                <p style="margin:0; font-size:0.85rem; line-height:1.55; color:rgb(var(--text));">
                    If an event concludes and you forget to manually request a payout, the system <strong>automatically generates an auto-payout request</strong> on your behalf so our admin team can process and transfer your funds without delay.
                </p>
            </div>

            <!-- Escrow Early Release -->
            <div style="background:rgba(251, 146, 60, 0.04); border:1px solid rgba(251, 146, 60, 0.2); border-radius:14px; padding:1.25rem;">
                <div style="font-size:1.5rem; margin-bottom:0.5rem;">⚡</div>
                <h3 style="margin:0 0 0.4rem; font-size:1.05rem; font-weight:700; color:#ea580c;">3. Early Escrow Release</h3>
                <p style="margin:0; font-size:0.85rem; line-height:1.55; color:rgb(var(--text));">
                    Need upfront capital to pay for venue deposits, sound equipment, or artists? You can request an <strong>early escrow withdrawal</strong> on active/upcoming events. (Subject to terms of service and refund liability).
                </p>
            </div>

            <!-- Fee Settlement Policy -->
            <div style="background:rgba(139, 92, 246, 0.04); border:1px solid rgba(139, 92, 246, 0.2); border-radius:14px; padding:1.25rem;">
                <div style="font-size:1.5rem; margin-bottom:0.5rem;">🏷️</div>
                <h3 style="margin:0 0 0.4rem; font-size:1.05rem; font-weight:700; color:#7c3aed;">4. 5% Platform Fee Settlement</h3>
                <p style="margin:0; font-size:0.85rem; line-height:1.55; color:rgb(var(--text));">
                    When creating an event:
                    <br>• <strong>Pass fee to buyer (`pass_fee = 1`):</strong> Attendee pays the 5% fee during checkout; you receive 100% of ticket price.
                    <br>• <strong>Absorb fee (`pass_fee = 0`):</strong> 5% platform fee is deducted from ticket sales; you receive 95% net share.
                </p>
            </div>
        </div>
    </section>
</div>

<!-- Escrow Agreement Modal -->
<div id="escrow-modal-backdrop" style="display:none; position:fixed; inset:0; z-index:9999; background:rgba(0,0,0,0.55); backdrop-filter:blur(6px); -webkit-backdrop-filter:blur(6px); animation: escrowFadeIn 0.2s ease; overflow-y:auto; -webkit-overflow-scrolling:touch; justify-content:center; align-items:flex-start; padding:1rem;">
    <div style="width:92%; max-width:520px; max-height:calc(100dvh - 2rem); overflow-y:auto; -webkit-overflow-scrolling:touch; background:rgb(var(--card)); border-radius:20px; box-shadow:0 25px 60px rgba(0,0,0,0.25); border:1px solid rgba(var(--muted), 0.15); animation: escrowSlideIn 0.25s ease; margin:auto 0; box-sizing:border-box;">
        <!-- Modal Header -->
        <div style="padding:1.5rem 1.75rem 0; display:flex; justify-content:space-between; align-items:flex-start;">
            <div>
                <h3 style="margin:0; font-size:1.15rem; font-weight:800; color:rgb(var(--text));">⚡ Escrow Funds Release Agreement</h3>
                <p style="margin:0.35rem 0 0; font-size:0.82rem; color:rgb(var(--muted));">Please review and accept the terms below</p>
            </div>
            <button type="button" id="escrow-modal-close" style="background:none; border:none; font-size:1.4rem; cursor:pointer; color:rgb(var(--muted)); padding:0; line-height:1;" aria-label="Close">&times;</button>
        </div>

        <!-- Modal Body: Key Terms -->
        <div style="padding:1.25rem 1.75rem; font-size:0.88rem; line-height:1.6; color:rgb(var(--text));">
            <div style="background:rgba(251, 146, 60, 0.08); border:1px solid rgba(251, 146, 60, 0.25); border-radius:12px; padding:0.85rem 1rem; margin-bottom:1rem;">
                <p style="margin:0; font-size:0.82rem; color:#ea580c; font-weight:600;">
                    ⚠️ <strong>Important Notice:</strong> Early escrow releases allow you to withdraw funds from upcoming events to assist with upfront logistics and planning.
                </p>
            </div>

            <h4 style="margin:0 0 0.5rem; font-size:0.92rem; font-weight:700;">Host Obligations &amp; Conditions:</h4>
            <ul style="margin:0 0 1rem; padding-left:1.25rem; font-size:0.84rem; color:rgb(var(--muted));">
                <li style="margin-bottom:0.35rem;">You agree to host the event as described and fulfill all commitments to ticket holders.</li>
                <li style="margin-bottom:0.35rem;">If the event is <strong>cancelled, postponed indefinitely, or significantly altered</strong>, you are personally liable to refund all disbursed funds.</li>
                <li style="margin-bottom:0.35rem;">Eventlyy reserves the right to recover disbursed funds from your linked bank account or future event payouts.</li>
                <li>Admin approval is required for all escrow disbursements before funds are sent.</li>
            </ul>

            <!-- Acceptance Checkbox -->
            <form method="POST" action="{{ route('host/withdraw') }}" id="escrow-withdrawal-form">
                <?= csrf_field() ?>
                <input type="hidden" name="withdraw_type" value="escrow">

                <label style="display:flex; align-items:flex-start; gap:0.65rem; cursor:pointer; font-size:0.84rem; font-weight:600; user-select:none; margin-bottom:1.25rem; padding:0.75rem; border-radius:10px; background:rgba(var(--muted),0.05); border:1px solid rgba(var(--muted),0.12);">
                    <input type="checkbox" id="escrow-agree-checkbox" name="escrow_agreed" value="1" required style="margin-top:0.2rem; width:16px; height:16px; cursor:pointer;">
                    <span>I understand and agree to the Escrow Release Terms and accept full liability for refunding ticket holders if my event is cancelled.</span>
                </label>

                <!-- Actions -->
                <div style="display:flex; gap:0.75rem; justify-content:flex-end;">
                    <button type="button" id="escrow-modal-cancel" class="checkout-btn secondary" style="padding:0.6rem 1.25rem; font-size:0.88rem; height:auto;">
                        Cancel
                    </button>
                    <button type="submit" id="escrow-confirm-btn" class="checkout-btn" disabled style="padding:0.6rem 1.25rem; font-size:0.88rem; height:auto; background:#ea580c; border-color:#ea580c; opacity:0.5; cursor:not-allowed;">
                        Accept &amp; Request Payout
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
@keyframes escrowFadeIn { from { opacity: 0; } to { opacity: 1; } }
@keyframes escrowSlideIn { from { transform: translateY(-10px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
</style>

<script>
function switchFinancialTab(tabKey) {
    document.querySelectorAll('.financial-tab-panel').forEach(function(p) { p.style.display = 'none'; });
    document.querySelectorAll('.dashboard-tabs .tab-btn').forEach(function(b) { b.classList.remove('active'); });

    var panel = document.getElementById('tab-content-' + tabKey);
    var btn = document.getElementById('tab-btn-' + tabKey);

    if (panel) panel.style.display = 'block';
    if (btn) btn.classList.add('active');
}

function filterEventsTable() {
    var query = (document.getElementById('event-search-input').value || '').toLowerCase().trim();
    var rows = document.querySelectorAll('.event-breakdown-row');
    rows.forEach(function(row) {
        var text = row.getAttribute('data-search') || '';
        row.style.display = text.indexOf(query) !== -1 ? '' : 'none';
    });
}

function filterPayoutsTable() {
    var query = (document.getElementById('payout-search-input').value || '').toLowerCase().trim();
    var rows = document.querySelectorAll('.payout-history-row');
    rows.forEach(function(row) {
        var text = row.getAttribute('data-search') || '';
        row.style.display = text.indexOf(query) !== -1 ? '' : 'none';
    });
}

document.addEventListener('DOMContentLoaded', function () {
    const escrowBtn = document.getElementById('financials-escrow-btn');
    const modalBackdrop = document.getElementById('escrow-modal-backdrop');
    const closeBtn = document.getElementById('escrow-modal-close');
    const cancelBtn = document.getElementById('escrow-modal-cancel');
    const checkbox = document.getElementById('escrow-agree-checkbox');
    const confirmBtn = document.getElementById('escrow-confirm-btn');

    function openEscrowModal() {
        if (modalBackdrop) modalBackdrop.style.display = 'flex';
    }

    function closeEscrowModal() {
        if (modalBackdrop) modalBackdrop.style.display = 'none';
        if (checkbox) checkbox.checked = false;
        if (confirmBtn) {
            confirmBtn.disabled = true;
            confirmBtn.style.opacity = '0.5';
            confirmBtn.style.cursor = 'not-allowed';
        }
    }

    if (escrowBtn) escrowBtn.addEventListener('click', openEscrowModal);
    if (closeBtn) closeBtn.addEventListener('click', closeEscrowModal);
    if (cancelBtn) cancelBtn.addEventListener('click', closeEscrowModal);

    if (modalBackdrop) {
        modalBackdrop.addEventListener('click', function (e) {
            if (e.target === modalBackdrop) closeEscrowModal();
        });
    }

    if (checkbox && confirmBtn) {
        checkbox.addEventListener('change', function () {
            confirmBtn.disabled = !this.checked;
            confirmBtn.style.opacity = this.checked ? '1' : '0.5';
            confirmBtn.style.cursor = this.checked ? 'pointer' : 'not-allowed';
        });
    }
});
</script>
@endsection