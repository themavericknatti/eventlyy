<?php

/**
 * Host Financials & Payouts View
 *
 * Comprehensive financials, per-event revenue breakdown, and payout transactions history.
 *
 * @package Evently
 * @subpackage Views/Host
 * @author Celio Natti
 * @version 2.0.0
 */

$siteName = site_setting('site_title', 'Eventlyy');
$bankConfigured = !empty($profile['payout_bank']) && !empty($profile['payout_account']);
?>

@extends('layouts/host')

@section('title')
Financials &amp; Payouts - <?= e($siteName) ?> Host
@endsection

@section('content')
<style>
/* ═══════════════════════════════════════════════════════════
   Host Financials — Responsive Polish & Layout Refactor
   ═══════════════════════════════════════════════════════════ */

/* Top Navigation Bar */
.fin-top-nav {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1rem;
    margin-bottom: 1.25rem;
}

.fin-back-link {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    font-weight: 700;
    font-size: 0.88rem;
    color: rgb(var(--text));
    text-decoration: none;
    transition: color 0.2s ease;
}

.fin-back-link:hover {
    color: rgb(var(--brand));
}

/* Page Hero Card */
.fin-hero-card {
    padding: 1.5rem 1.75rem;
    background: rgb(var(--card));
    border: 1px solid rgba(var(--muted), 0.16);
    border-radius: 20px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
    margin-bottom: 1.75rem;
}

.fin-hero-wrap {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    flex-wrap: wrap;
    gap: 1.5rem;
}

.fin-hero-info {
    flex: 1;
    min-width: 280px;
}

/* Linked Bank Destination Box */
.fin-bank-box {
    background: rgba(var(--muted), 0.05);
    border: 1px solid rgba(var(--muted), 0.16);
    border-radius: 14px;
    padding: 0.95rem 1.25rem;
    min-width: 260px;
}

/* Primary Financial Summary Cards Grid */
.fin-primary-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1rem;
    margin-bottom: 1.25rem;
}

.fin-card {
    background: rgb(var(--card));
    border: 1px solid rgba(var(--muted), 0.15);
    border-radius: 18px;
    padding: 1.25rem 1.35rem;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.03);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.fin-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.06);
}

.fin-card-label {
    font-size: 0.8rem;
    font-weight: 700;
    color: rgb(var(--muted));
    text-transform: uppercase;
    letter-spacing: 0.03em;
    display: block;
    margin-bottom: 0.35rem;
}

.fin-card-value {
    font-size: clamp(1.4rem, 2.2vw, 1.85rem);
    font-weight: 900;
    color: rgb(var(--text));
    margin: 0 0 0.35rem;
    line-height: 1.2;
}

.fin-card-meta {
    font-size: 0.8rem;
    color: rgb(var(--muted));
    margin: 0;
}

/* Secondary Metrics Grid */
.fin-secondary-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1rem;
    margin-bottom: 2rem;
}

.fin-mini-card {
    background: rgba(var(--card), 0.7);
    border: 1px solid rgba(var(--muted), 0.12);
    border-radius: 16px;
    padding: 1rem 1.2rem;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);
}

.fin-mini-value {
    margin: 0.2rem 0;
    font-size: 1.25rem;
    font-weight: 800;
    color: rgb(var(--text));
}

/* ── Interactive Navigation Tabs ── */
.fin-tabs-bar {
    display: flex;
    gap: 0.5rem;
    overflow-x: auto;
    white-space: nowrap;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: none;
    padding-bottom: 0.35rem;
    margin-bottom: 1.5rem;
    border-bottom: 1px solid rgba(var(--muted), 0.12);
}

.fin-tabs-bar::-webkit-scrollbar {
    display: none;
}

.fin-tabs-bar .tab-btn {
    padding: 0.6rem 1.25rem;
    font-size: 0.88rem;
    font-weight: 700;
    border-radius: 12px;
    border: 1px solid rgba(var(--muted), 0.15);
    background: rgba(var(--muted), 0.06);
    color: rgb(var(--text));
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    transition: all 0.2s ease;
    white-space: nowrap;
    flex-shrink: 0;
    font-family: inherit;
}

.fin-tabs-bar .tab-btn:hover {
    background: rgba(var(--brand), 0.1);
    color: rgb(var(--brand));
    border-color: rgba(var(--brand), 0.25);
}

.fin-tabs-bar .tab-btn.active {
    background: rgb(var(--brand));
    color: #ffffff;
    border-color: rgb(var(--brand));
    box-shadow: 0 3px 10px rgba(var(--brand), 0.25);
}

/* Action Buttons */
.fin-btn {
    padding: 0.55rem 1.15rem;
    font-size: 0.86rem;
    font-weight: 700;
    border-radius: 10px;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.4rem;
    cursor: pointer;
    transition: all 0.2s ease;
    border: none;
    font-family: inherit;
}

.fin-btn--primary {
    background: rgb(var(--brand));
    color: #ffffff !important;
    box-shadow: 0 4px 12px rgba(var(--brand), 0.25);
}

.fin-btn--primary:hover {
    filter: brightness(1.08);
    transform: translateY(-1px);
}

.fin-btn--escrow {
    background: #ea580c;
    color: #ffffff !important;
    box-shadow: 0 4px 12px rgba(234, 88, 12, 0.25);
}

.fin-btn--escrow:hover {
    filter: brightness(1.08);
    transform: translateY(-1px);
}

.fin-btn--disabled {
    background: rgba(var(--muted), 0.1);
    color: rgb(var(--muted)) !important;
    border: 1px solid rgba(var(--muted), 0.15);
    cursor: not-allowed;
    opacity: 0.6;
}

/* Tables Section Polish */
.fin-table-card {
    background: rgb(var(--card));
    border: 1px solid rgba(var(--muted), 0.14);
    border-radius: 22px;
    padding: 1.5rem;
    box-shadow: 0 4px 18px rgba(0, 0, 0, 0.03);
    margin-bottom: 2rem;
}

.fin-table-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
    margin-bottom: 1.25rem;
    flex-wrap: wrap;
}

.fin-table-search {
    padding: 0.5rem 0.95rem;
    border-radius: 11px;
    border: 1px solid rgba(var(--muted), 0.18);
    background: rgba(var(--card), 0.6);
    color: rgb(var(--text));
    font-size: 0.85rem;
    min-width: 230px;
    font-family: inherit;
}

.fin-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    font-size: 0.88rem;
    text-align: left;
}

.fin-table th {
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

.fin-table td {
    padding: 0.95rem 1rem;
    border-bottom: 1px solid rgba(var(--muted), 0.08);
    color: rgb(var(--text));
    vertical-align: middle;
}

.fin-table tr:hover td {
    background: rgba(var(--muted), 0.03);
}

/* ══ Tablet Breakpoint (601px - 900px) ══ */
@media (max-width: 900px) and (min-width: 601px) {
    .fin-primary-grid {
        grid-template-columns: repeat(2, 1fr) !important;
    }

    .fin-secondary-grid {
        grid-template-columns: repeat(2, 1fr) !important;
    }

    .fin-hero-wrap {
        flex-direction: column;
    }

    .fin-bank-box {
        width: 100%;
        box-sizing: border-box;
    }

    .fin-table-header {
        flex-direction: column;
        align-items: stretch;
    }

    .fin-table-search {
        width: 100%;
    }
}

/* ══ Mobile Breakpoint (<= 600px) ══ */
@media (max-width: 600px) {
    .fin-top-nav {
        flex-direction: column;
        align-items: stretch;
    }

    .fin-hero-card {
        padding: 1.15rem;
    }

    .fin-hero-wrap {
        flex-direction: column;
        gap: 1rem;
    }

    .fin-hero-info {
        min-width: 0 !important;
        width: 100% !important;
    }

    .fin-bank-box {
        min-width: 0 !important;
        width: 100% !important;
        box-sizing: border-box;
    }

    .fin-primary-grid {
        grid-template-columns: 1fr !important;
        gap: 0.85rem;
    }

    .fin-secondary-grid {
        grid-template-columns: repeat(2, 1fr) !important;
        gap: 0.65rem;
    }

    .fin-mini-card {
        padding: 0.85rem;
    }

    .fin-table-card {
        padding: 1rem;
        border-radius: 18px;
    }

    .fin-table-header {
        flex-direction: column;
        align-items: stretch;
    }

    .fin-table-search {
        min-width: 0 !important;
        width: 100% !important;
    }
}

@media (max-width: 400px) {
    .fin-secondary-grid {
        grid-template-columns: 1fr !important;
    }
}
</style>

<!-- Top Navigation Bar -->
<div class="fin-top-nav">
    <a href="{{ route('host/dashboard') }}" class="fin-back-link">
        ← Back to Dashboard
    </a>
    <a href="{{ route('host/settings') }}" class="admin-action-btn" style="padding:0.45rem 0.95rem; font-size:0.84rem; border-radius:10px; display:inline-flex; align-items:center; gap:0.35rem; text-decoration:none; font-weight:700;">
        ⚙️ Payout Settings
    </a>
</div>

<!-- Page Hero Card -->
<section class="fin-hero-card">
    <div class="fin-hero-wrap">
        <div class="fin-hero-info">
            <span class="panel-badge" style="background:rgba(16, 185, 129, 0.12); color:#10b981; border:1px solid rgba(16, 185, 129, 0.25);">💳 Host Financials &amp; Payouts</span>
            <h1 style="margin:0.35rem 0 0.5rem; font-size:clamp(1.5rem, 2.5vw, 2.1rem); font-weight:900;">Earnings, Revenue &amp; Withdrawals</h1>
            <p class="lede" style="margin:0; font-size:0.92rem; color:rgb(var(--muted));">
                Track your total net earnings, per-event ticket revenues, fee deductions, and withdrawal transactions in real time.
            </p>
        </div>
        
        <!-- Linked Bank Badge -->
        <div class="fin-bank-box">
            <span style="font-size:0.75rem; text-transform:uppercase; font-weight:800; color:rgb(var(--muted)); letter-spacing:0.04em; display:block; margin-bottom:0.3rem;">Destination Payout Bank</span>
            <?php if ($bankConfigured): ?>
                <div style="font-weight:800; color:rgb(var(--text)); font-size:1rem; display:flex; align-items:center; gap:0.4rem;">
                    <span>🏦</span> <?= e($profile['payout_bank']) ?>
                </div>
                <div style="font-size:0.84rem; color:rgb(var(--muted)); font-family:monospace; margin-top:3px;">
                    Acc: <?= e($profile['payout_account']) ?>
                    <span style="color:#10b981; font-weight:800; margin-left:4px;">● Verified</span>
                </div>
            <?php else: ?>
                <div style="font-size:0.88rem; color:#ef4444; font-weight:700; display:flex; align-items:center; gap:0.35rem;">
                    <span>⚠️</span> Bank Not Configured
                </div>
                <a href="{{ route('host/settings') }}" style="font-size:0.8rem; color:rgb(var(--brand)); font-weight:700; text-decoration:underline; display:block; margin-top:3px;">Set up payout account →</a>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Primary Financial Stats Grid -->
<section class="fin-primary-grid">
    <!-- Lifetime Net Earnings -->
    <article class="fin-card">
        <div>
            <span class="fin-card-label">Lifetime Net Earnings</span>
            <h3 class="fin-card-value" style="color:rgb(var(--text));"><?= format_price($totalNetRevenue ?? 0) ?></h3>
        </div>
        <?php if (($totalPlatformFees ?? 0) > 0): ?>
            <div>
                <p class="fin-card-meta" style="font-size:0.78rem;">Gross sales: <?= format_price($totalRevenue ?? 0) ?></p>
                <p class="fin-card-meta" style="font-size:0.75rem; color:#ef4444; font-weight:600;">− <?= format_price($totalPlatformFees ?? 0) ?> platform fee (5%)</p>
            </div>
        <?php else: ?>
            <p class="fin-card-meta" style="font-size:0.78rem;">100% of all ticket sales</p>
        <?php endif; ?>
    </article>

    <!-- Available Completed Balance -->
    <article class="fin-card" style="border-color: rgba(16, 185, 129, 0.28); background: rgba(16, 185, 129, 0.04);">
        <div>
            <span class="fin-card-label" style="color: #10b981;">Available to Withdraw</span>
            <h3 class="fin-card-value" style="color: #10b981;"><?= format_price($availableBalance ?? 0) ?></h3>
            <p class="fin-card-meta" style="font-size:0.78rem;">From completed events</p>
        </div>
        <form method="POST" action="{{ route('host/withdraw') }}" style="margin-top:0.85rem; width:100%;">
            <?= csrf_field() ?>
            <input type="hidden" name="withdraw_type" value="completed">
            <?php if (($availableBalance ?? 0.00) > 0 && $bankConfigured): ?>
                <button type="submit" class="fin-btn fin-btn--primary" style="width:100%;">
                    💸 Request Standard Payout
                </button>
            <?php else: ?>
                <button type="button" class="fin-btn fin-btn--disabled" disabled style="width:100%;">
                    <?= !$bankConfigured ? 'Configure Bank First' : 'No Balance Available' ?>
                </button>
            <?php endif; ?>
        </form>
    </article>

    <!-- Escrow Balance (Upcoming Events) -->
    <article class="fin-card" style="border-color: rgba(251, 146, 60, 0.28); background: rgba(251, 146, 60, 0.04);">
        <div>
            <span class="fin-card-label" style="color: #ea580c;">🔒 Escrow Balance (Upcoming)</span>
            <h3 class="fin-card-value" style="color: #ea580c;"><?= format_price($escrowRevenue ?? 0) ?></h3>
            <p class="fin-card-meta" style="font-size:0.78rem;">Withdrawable: <strong><?= format_price($availableEscrowBalance ?? 0) ?></strong></p>
        </div>
        <div style="margin-top:0.85rem; width:100%;">
            <?php if (($availableEscrowBalance ?? 0.00) > 0 && $bankConfigured): ?>
                <button type="button" id="financials-escrow-btn" class="fin-btn fin-btn--escrow" style="width:100%;">
                    ⚡ Request Early Escrow Release
                </button>
            <?php else: ?>
                <button type="button" class="fin-btn fin-btn--disabled" disabled style="width:100%;">
                    <?= !$bankConfigured ? 'Configure Bank First' : 'No Escrow Available' ?>
                </button>
            <?php endif; ?>
        </div>
    </article>

    <!-- Disbursed to Bank -->
    <article class="fin-card">
        <div>
            <span class="fin-card-label">Total Disbursed (Paid Out)</span>
            <h3 class="fin-card-value" style="color:#0284c7;"><?= format_price($disbursedPayouts ?? 0) ?></h3>
        </div>
        <p class="fin-card-meta" style="font-size:0.78rem;">Sent directly to your bank account</p>
    </article>
</section>

<!-- Secondary Stats Row -->
<section class="fin-secondary-grid">
    <article class="fin-mini-card">
        <span class="fin-card-label" style="font-size:0.76rem;">Pending Payouts</span>
        <h4 class="fin-mini-value" style="color:#d97706;"><?= format_price($pendingPayouts ?? 0) ?></h4>
        <p class="fin-card-meta" style="font-size:0.75rem;">Awaiting admin transfer</p>
    </article>
    <article class="fin-mini-card">
        <span class="fin-card-label" style="font-size:0.76rem;">Platform Fee Deductions</span>
        <h4 class="fin-mini-value" style="color:rgb(var(--muted));"><?= format_price($totalPlatformFees ?? 0) ?></h4>
        <p class="fin-card-meta" style="font-size:0.75rem;">5% fee where absorbed</p>
    </article>
    <article class="fin-mini-card">
        <span class="fin-card-label" style="font-size:0.76rem;">Online Tickets Sold</span>
        <h4 class="fin-mini-value"><?= number_format($totalTicketsSold ?? 0) ?></h4>
        <p class="fin-card-meta" style="font-size:0.75rem;">Online platform purchases</p>
    </article>
    <article class="fin-mini-card">
        <span class="fin-card-label" style="font-size:0.76rem;">Events Hosted</span>
        <h4 class="fin-mini-value"><?= number_format($totalEventsCount ?? 0) ?></h4>
        <p class="fin-card-meta" style="font-size:0.75rem;"><?= (($totalOccurrencesCount ?? 0) > ($totalEventsCount ?? 0)) ? '(' . ($totalOccurrencesCount) . ' dates across series)' : 'All-time created events' ?></p>
    </article>
</section>

<!-- Interactive Navigation Tabs -->
<div class="fin-tabs-bar dashboard-tabs">
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
    <section class="fin-table-card">
        <div class="fin-table-header">
            <div>
                <h2 style="margin:0; font-size:1.25rem; font-weight:800;">Event Revenue &amp; Settlement Tally</h2>
                <p class="section-note" style="margin:0.25rem 0 0; font-size:0.82rem; color:rgb(var(--muted));">Exact gross ticket sales, platform fee policy, disbursements, and unrequested balances per event.</p>
            </div>
            <input type="text" id="event-search-input" class="fin-table-search" placeholder="Search events..." onkeyup="filterEventsTable()" />
        </div>

        <?php if (empty($eventFinancials)): ?>
            <div class="empty-state" style="padding:3rem 1rem; text-align:center;">
                <span style="font-size:2.5rem; display:block; margin-bottom:0.75rem;">🎟️</span>
                <p style="margin:0 0 1rem; font-weight:600; color:rgb(var(--muted));">No events found in your account.</p>
                <a href="{{ route('host/event/create') }}" class="fin-btn fin-btn--primary">Create Your First Event</a>
            </div>
        <?php else: ?>
            <div class="admin-table-wrap" style="overflow-x:auto;">
                <table class="fin-table" id="events-breakdown-table">
                    <thead>
                        <tr>
                            <th style="min-width:200px;">Event</th>
                            <th>Status &amp; Policy</th>
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
                                    <strong style="color:rgb(var(--text)); font-size:0.92rem; display:block;"><?= e($ev['name']) ?></strong>
                                    <span style="font-size:0.76rem; color:rgb(var(--muted)); display:block; margin-top:2px;">
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
                                        <span style="font-size:0.72rem; color:rgb(var(--muted)); display:block; margin-top:3px;" title="Buyer covered 5% fee at checkout">🎟️ Net 100% (Buyer paid)</span>
                                    <?php else: ?>
                                        <span style="font-size:0.72rem; color:#8b5cf6; font-weight:600; display:block; margin-top:3px;" title="5% platform fee deducted from ticket price">🏷️ Net 95% (Host fee)</span>
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
                                        <span style="font-size:0.72rem; color:#ea580c; display:block;">⚡ <?= format_price($ev['escrow_disbursed']) ?> escrow</span>
                                    <?php endif; ?>
                                    <?php if ($ev['pending_amount'] > 0): ?>
                                        <span style="font-size:0.72rem; color:#d97706; display:block;">⏳ <?= format_price($ev['pending_amount']) ?> pending</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($hasRemainder): ?>
                                        <strong style="color:#d97706; font-size:0.95rem;"><?= format_price($ev['unrequested_balance']) ?></strong>
                                        <span style="font-size:0.72rem; color:#ea580c; display:block; font-weight:700;">⚡ Unrequested</span>
                                    <?php else: ?>
                                        <span style="color:#059669; font-weight:700; font-size:0.82rem;">✓ Fully Settled</span>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align:right;">
                                    <a href="{{ route('host/event/management/' . $ev['id']) }}" class="admin-action-btn" style="padding:0.4rem 0.75rem; font-size:0.8rem; border-radius:8px; text-decoration:none; display:inline-block; font-weight:700;">
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
    <section class="fin-table-card">
        <div class="fin-table-header">
            <div>
                <h2 style="margin:0; font-size:1.25rem; font-weight:800;">Withdrawal &amp; Payout Transactions</h2>
                <p class="section-note" style="margin:0.25rem 0 0; font-size:0.82rem; color:rgb(var(--muted));">All payout requests, escrow withdrawals, and auto-settlements processed to your bank.</p>
            </div>
            <input type="text" id="payout-search-input" class="fin-table-search" placeholder="Search payouts..." onkeyup="filterPayoutsTable()" />
        </div>

        <?php if (empty($payoutHistory)): ?>
            <div class="empty-state" style="padding:3rem 1rem; text-align:center;">
                <span style="font-size:2.5rem; display:block; margin-bottom:0.75rem;">💸</span>
                <p style="margin:0 0 1rem; font-weight:600; color:rgb(var(--muted));">No withdrawal requests recorded yet.</p>
                <?php if (($availableBalance ?? 0) > 0): ?>
                    <form method="POST" action="{{ route('host/withdraw') }}" style="display:inline-block;">
                        <?= csrf_field() ?>
                        <input type="hidden" name="withdraw_type" value="completed">
                        <button type="submit" class="fin-btn fin-btn--primary">Request Payout Now</button>
                    </form>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="admin-table-wrap" style="overflow-x:auto;">
                <table class="fin-table" id="payouts-history-table">
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
                                        <strong style="color:rgb(var(--text)); font-size:0.9rem; display:block;"><?= e($p['event_name']) ?></strong>
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
                                    <div style="font-size:0.86rem; font-weight:700; color:rgb(var(--text));">
                                        🏦 <?= e($p['payout_bank'] ?? ($profile['payout_bank'] ?? 'Bank')) ?>
                                    </div>
                                    <div style="font-size:0.76rem; color:rgb(var(--muted)); font-family:monospace;">
                                        Acc: <?= e($p['payout_account'] ?? ($profile['payout_account'] ?? '—')) ?>
                                    </div>
                                </td>
                                <td>
                                    <?php if ($isCompleted): ?>
                                        <span style="background:rgba(16, 185, 129, 0.12); color:#059669; border:1px solid rgba(16, 185, 129, 0.28); font-size:0.74rem; font-weight:800; padding:3px 9px; border-radius:20px; display:inline-flex; align-items:center; gap:4px;">
                                            <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg> Disbursed
                                        </span>
                                    <?php elseif ($isFailed): ?>
                                        <span style="background:rgba(239, 68, 68, 0.12); color:#dc2626; border:1px solid rgba(239, 68, 68, 0.25); font-size:0.74rem; font-weight:800; padding:3px 9px; border-radius:20px; display:inline-flex; align-items:center; gap:4px;">
                                            ⚠️ Flagged / On Hold
                                        </span>
                                    <?php else: ?>
                                        <span style="background:rgba(245, 158, 11, 0.12); color:#b45309; border:1px solid rgba(245, 158, 11, 0.25); font-size:0.74rem; font-weight:800; padding:3px 9px; border-radius:20px; display:inline-flex; align-items:center; gap:4px;">
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
    <section class="fin-table-card">
        <h2 style="margin:0 0 0.5rem; font-size:1.3rem; font-weight:900;">Understanding Your Host Earnings &amp; Payouts</h2>
        <p class="section-note" style="margin:0 0 1.5rem; font-size:0.86rem; color:rgb(var(--muted));">Everything you need to know about ticket revenue, platform fee policies, escrow security, and withdrawals.</p>

        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(260px, 1fr)); gap:1.25rem;">
            <!-- Standard Payouts -->
            <div style="background:rgba(16, 185, 129, 0.04); border:1px solid rgba(16, 185, 129, 0.2); border-radius:16px; padding:1.25rem;">
                <div style="font-size:1.6rem; margin-bottom:0.5rem;">💸</div>
                <h3 style="margin:0 0 0.4rem; font-size:1.05rem; font-weight:800; color:#059669;">1. Standard Event Payouts</h3>
                <p style="margin:0; font-size:0.86rem; line-height:1.55; color:rgb(var(--text));">
                    Once your event ends and is marked completed, <strong>100% of your remaining net ticket sales</strong> become immediately available in your <strong>Available Balance</strong> to request a payout straight to your bank account.
                </p>
            </div>

            <!-- Auto-Requested Payouts -->
            <div style="background:rgba(59, 130, 246, 0.04); border:1px solid rgba(59, 130, 246, 0.2); border-radius:16px; padding:1.25rem;">
                <div style="font-size:1.6rem; margin-bottom:0.5rem;">🤖</div>
                <h3 style="margin:0 0 0.4rem; font-size:1.05rem; font-weight:800; color:#2563eb;">2. Automatic Payout Generation</h3>
                <p style="margin:0; font-size:0.86rem; line-height:1.55; color:rgb(var(--text));">
                    If an event concludes and you forget to manually request a payout, the system <strong>automatically generates an auto-payout request</strong> on your behalf so our admin team can process and transfer your funds without delay.
                </p>
            </div>

            <!-- Escrow Early Release -->
            <div style="background:rgba(251, 146, 60, 0.04); border:1px solid rgba(251, 146, 60, 0.2); border-radius:16px; padding:1.25rem;">
                <div style="font-size:1.6rem; margin-bottom:0.5rem;">⚡</div>
                <h3 style="margin:0 0 0.4rem; font-size:1.05rem; font-weight:800; color:#ea580c;">3. Early Escrow Release</h3>
                <p style="margin:0; font-size:0.86rem; line-height:1.55; color:rgb(var(--text));">
                    Need upfront capital to pay for venue deposits, sound equipment, or artists? You can request an <strong>early escrow withdrawal</strong> on active/upcoming events. (Subject to terms of service and refund liability).
                </p>
            </div>

            <!-- Fee Settlement Policy -->
            <div style="background:rgba(139, 92, 246, 0.04); border:1px solid rgba(139, 92, 246, 0.2); border-radius:16px; padding:1.25rem;">
                <div style="font-size:1.6rem; margin-bottom:0.5rem;">🏷️</div>
                <h3 style="margin:0 0 0.4rem; font-size:1.05rem; font-weight:800; color:#7c3aed;">4. 5% Platform Fee Settlement</h3>
                <p style="margin:0; font-size:0.86rem; line-height:1.55; color:rgb(var(--text));">
                    When creating an event:
                    <br>• <strong>Pass fee to buyer (`pass_fee = 1`):</strong> Attendee pays the 5% fee during checkout; you receive 100% of ticket price.
                    <br>• <strong>Absorb fee (`pass_fee = 0`):</strong> 5% platform fee is deducted from ticket sales; you receive 95% net share.
                </p>
            </div>
        </div>
    </section>
</div>

<!-- Escrow Agreement Modal -->
<div id="escrow-modal-backdrop" style="display:none; position:fixed; inset:0; z-index:9999; background:rgba(0,0,0,0.6); backdrop-filter:blur(8px); -webkit-backdrop-filter:blur(8px); animation: escrowFadeIn 0.2s ease; overflow-y:auto; -webkit-overflow-scrolling:touch; justify-content:center; align-items:center; padding:1rem;">
    <div style="width:100%; max-width:min(92vw, 520px); max-height:calc(100dvh - 2rem); overflow-y:auto; -webkit-overflow-scrolling:touch; background:rgb(var(--card)); border-radius:22px; box-shadow:0 25px 60px rgba(0,0,0,0.3); border:1px solid rgba(var(--muted), 0.2); animation: escrowSlideIn 0.25s ease; margin:auto; box-sizing:border-box;">
        <!-- Modal Header -->
        <div style="padding:1.5rem 1.75rem 0.5rem; display:flex; justify-content:space-between; align-items:flex-start;">
            <div>
                <h3 style="margin:0; font-size:1.2rem; font-weight:800; color:rgb(var(--text));">⚡ Escrow Funds Release Agreement</h3>
                <p style="margin:0.25rem 0 0; font-size:0.82rem; color:rgb(var(--muted));">Please review and accept the terms below</p>
            </div>
            <button type="button" id="escrow-modal-close" style="background:none; border:none; font-size:1.5rem; cursor:pointer; color:rgb(var(--muted)); padding:0 0.35rem; line-height:1;" aria-label="Close">&times;</button>
        </div>

        <!-- Modal Body: Key Terms -->
        <div style="padding:1.25rem 1.75rem 1.5rem; font-size:0.88rem; line-height:1.6; color:rgb(var(--text));">
            <div style="background:rgba(251, 146, 60, 0.08); border:1px solid rgba(251, 146, 60, 0.25); border-radius:12px; padding:0.85rem 1rem; margin-bottom:1rem;">
                <p style="margin:0; font-size:0.82rem; color:#ea580c; font-weight:600;">
                    ⚠️ <strong>Important Notice:</strong> Early escrow releases allow you to withdraw funds from upcoming events to assist with upfront logistics and planning.
                </p>
            </div>

            <h4 style="margin:0 0 0.5rem; font-size:0.9rem; font-weight:800;">Host Obligations &amp; Conditions:</h4>
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

                <label style="display:flex; align-items:flex-start; gap:0.65rem; cursor:pointer; font-size:0.84rem; font-weight:600; user-select:none; margin-bottom:1.25rem; padding:0.75rem; border-radius:12px; background:rgba(var(--muted),0.06); border:1px solid rgba(var(--muted),0.15);">
                    <input type="checkbox" id="escrow-agree-checkbox" name="escrow_agreed" value="1" required style="margin-top:0.2rem; width:16px; height:16px; cursor:pointer;">
                    <span>I understand and agree to the Escrow Release Terms and accept full liability for refunding ticket holders if my event is cancelled.</span>
                </label>

                <!-- Actions -->
                <div style="display:flex; gap:0.75rem; justify-content:flex-end;">
                    <button type="button" id="escrow-modal-cancel" class="admin-action-btn" style="padding:0.6rem 1.25rem; font-size:0.88rem; border-radius:11px;">
                        Cancel
                    </button>
                    <button type="submit" id="escrow-confirm-btn" class="fin-btn fin-btn--escrow" disabled style="padding:0.6rem 1.25rem; font-size:0.88rem; opacity:0.5; cursor:not-allowed;">
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