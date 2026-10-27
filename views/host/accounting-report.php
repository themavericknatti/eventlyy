<?php

/**
 * Host Event Accounting & Financial Settlement Report
 *
 * Comprehensive post-event accounting and reconciliation view displaying Paystack-verified
 * online ticket purchases, complimentary/gate-issued tickets, and executive financial balance.
 *
 * @package Evently
 * @subpackage Views/Host
 */

$currency = get_currency_symbol();
$formattedEventDate = !empty($event->date) ? date('l, F d, Y • h:i A', strtotime($event->date)) : 'Date TBA';

// Ensure ticket collections are plain arrays for array_filter/count compatibility
$onlineTickets = is_array($onlineTickets) ? $onlineTickets : (method_exists($onlineTickets, 'toArray') ? $onlineTickets->toArray() : iterator_to_array($onlineTickets));
$issuedTickets = is_array($issuedTickets) ? $issuedTickets : (method_exists($issuedTickets, 'toArray') ? $issuedTickets->toArray() : iterator_to_array($issuedTickets));
$allTickets = is_array($allTickets) ? $allTickets : (method_exists($allTickets, 'toArray') ? $allTickets->toArray() : iterator_to_array($allTickets));
?>

@extends('layouts/host')

@section('title')
Accounting Report - <?= e($event->name) ?>
@endsection

@section('content')
<style>
/* ─── Host Accounting Report Mobile Responsiveness ─── */
.accounting-topbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.report-hero-card {
    padding: 1.75rem;
    border-radius: 20px;
    margin-bottom: 1.5rem;
    background: var(--card-bg, rgb(var(--card)));
    border: 1px solid rgba(var(--muted), 0.15);
}

.accounting-kpi-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 1rem;
    margin-bottom: 1.75rem;
}

.accounting-tabs-bar {
    display: flex;
    gap: 0.5rem;
    border-bottom: 2px solid rgba(var(--muted), 0.15);
    margin-bottom: 1.5rem;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: none;
    white-space: nowrap;
    padding-bottom: 2px;
}

.accounting-tabs-bar::-webkit-scrollbar {
    display: none;
}

.accounting-tab-btn {
    padding: 0.75rem 1.25rem;
    font-size: 0.92rem;
    font-weight: 700;
    border: none;
    background: transparent;
    cursor: pointer;
    color: rgb(var(--muted));
    border-bottom: 3px solid transparent;
    margin-bottom: -2px;
    flex-shrink: 0;
    white-space: nowrap;
    transition: all 0.2s ease;
    font-family: inherit;
}

.accounting-tab-btn.active {
    color: rgb(var(--brand));
    border-bottom-color: rgb(var(--brand));
}

.accounting-table-wrap {
    width: 100%;
    max-width: 100%;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}

.accounting-cards-mobile {
    display: none;
    flex-direction: column;
    gap: 0.75rem;
}

.accounting-ticket-card {
    background: rgba(var(--muted), 0.03);
    border: 1px solid rgba(var(--muted), 0.14);
    border-radius: 14px;
    padding: 1rem;
    display: flex;
    flex-direction: column;
    gap: 0.6rem;
}

.reconcile-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem 1rem;
    border-bottom: 1px solid rgba(var(--muted), 0.08);
    font-size: 0.88rem;
    gap: 0.75rem;
}

.reconcile-final-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.1rem 1rem;
    background: rgba(var(--brand), 0.08);
    border-top: 2px solid rgb(var(--brand));
    gap: 0.75rem;
}

@media (max-width: 768px) {
    .accounting-table-wrap {
        display: none !important;
    }
    .accounting-cards-mobile {
        display: flex !important;
    }
}

@media (max-width: 640px) {
    .accounting-topbar {
        flex-direction: column !important;
        align-items: stretch !important;
        gap: 0.75rem !important;
    }

    .accounting-topbar > div {
        width: 100% !important;
        display: grid !important;
        grid-template-columns: 1fr 1fr !important;
        gap: 0.5rem !important;
    }

    .accounting-topbar .admin-action-btn {
        width: 100% !important;
        justify-content: center !important;
        text-align: center !important;
        padding: 0.55rem !important;
    }

    .report-hero-card {
        padding: 1.25rem 1rem !important;
        border-radius: 16px !important;
    }

    .report-hero-card h1 {
        font-size: 1.35rem !important;
    }

    .accounting-kpi-grid {
        grid-template-columns: 1fr !important;
        gap: 0.75rem !important;
    }

    .reconcile-row {
        flex-direction: column !important;
        align-items: flex-start !important;
        gap: 0.3rem !important;
        padding: 0.75rem 0.85rem !important;
    }

    .reconcile-final-row {
        flex-direction: column !important;
        align-items: flex-start !important;
        gap: 0.5rem !important;
        padding: 1rem 0.85rem !important;
    }

    .reconcile-final-row > div:last-child {
        font-size: 1.3rem !important;
    }
}
</style>
<div class="accounting-report-wrapper" style="margin-bottom: 3rem;">

    <!-- ════════════════════════════════════════════════════════════════
         1. TOP CONTEXT BAR & ACTIONS
         ════════════════════════════════════════════════════════════════ -->
    <div class="accounting-topbar no-print" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:1rem; margin-bottom:1.5rem;">
        <a href="{{ route('host/event/management/' . $event->id) }}" class="back-link" style="display:inline-flex; align-items:center; gap:0.4rem; font-weight:700; text-decoration:none; color:rgb(var(--brand)); font-size:0.9rem;">
            ← Back to Event Management
        </a>
        <div style="display:flex; align-items:center; gap:0.75rem; flex-wrap:wrap;">
            <a href="{{ route('host/event/accounting/' . $event->id . '/export') }}" class="admin-action-btn" style="text-decoration:none; font-size:0.85rem; padding:0.5rem 1rem; border-radius:10px; font-weight:700; background:rgba(var(--brand), 0.1); color:rgb(var(--brand)); border:1px solid rgba(var(--brand), 0.25);">
                <span>📥</span> Export CSV
            </a>
            <button type="button" onclick="window.print()" class="admin-action-btn" style="font-size:0.85rem; padding:0.5rem 1rem; border-radius:10px; font-weight:700; background:rgba(99,102,241,0.1); color:#6366f1; border:1px solid rgba(99,102,241,0.25); cursor:pointer;">
                <span>🖨️</span> Print / Save PDF
            </button>
        </div>
    </div>

    <!-- Admin View Notice -->
    <?php if (!empty($isAdmin) && !empty($organizerName)): ?>
        <div class="no-print" style="display:flex; align-items:center; gap:0.6rem; background:rgba(99,102,241,0.08); border:1px solid rgba(99,102,241,0.22); border-radius:14px; padding:0.65rem 1.1rem; margin-bottom:1.25rem;">
            <span style="font-size:1.15rem;">🛡️</span>
            <div style="font-size:0.85rem; line-height:1.45;">
                <strong style="color:#6366f1;">Admin Audit Mode:</strong>
                <span> Viewing accounting for event by <strong><?= e($organizerName) ?></strong> (<?= e($organizerEmail) ?>)</span>
            </div>
        </div>
    <?php endif; ?>

    <!-- ════════════════════════════════════════════════════════════════
         2. REPORT HERO HEADER
         ════════════════════════════════════════════════════════════════ -->
    <section class="form-card report-hero-card" style="border-radius:20px; margin-bottom:1.5rem;">
        <div style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:1.25rem;">
            <div>
                <div style="display:inline-flex; align-items:center; gap:0.4rem; padding:0.25rem 0.65rem; border-radius:20px; background:rgba(var(--brand), 0.1); color:rgb(var(--brand)); font-size:0.78rem; font-weight:700; text-transform:uppercase; letter-spacing:0.04em; margin-bottom:0.5rem;">
                    <span>📊</span> Official Settlement &amp; Audit Report
                </div>
                <h1 style="margin:0 0 0.5rem; font-size:1.75rem; font-weight:800;"><?= e($event->name) ?></h1>
                <div style="display:flex; gap:1.25rem; flex-wrap:wrap; font-size:0.88rem; color:rgb(var(--muted));">
                    <span>🗓️ <?= e($formattedEventDate) ?></span>
                    <span>📍 <?= e($event->location ?: 'Venue TBA') ?></span>
                    <span>🏷️ <?= e($event->category ?: 'General') ?></span>
                    <span>🕒 Generated: <?= date('M d, Y • g:i A') ?></span>
                </div>
            </div>
            <div class="print-only-brand" style="display:none; text-align:right;">
                <strong style="font-size:1.2rem; color:#0B7366;">Eventlyy</strong>
                <div style="font-size:0.8rem; color:#64748b;">Official Financial Statement</div>
            </div>
        </div>
    </section>

    <!-- ════════════════════════════════════════════════════════════════
         3. EXECUTIVE KPI CARDS
         ════════════════════════════════════════════════════════════════ -->
    <div class="accounting-kpi-grid" style="display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:1rem; margin-bottom:1.75rem;">
        
        <!-- Gross Online Revenue -->
        <div class="form-card" style="padding:1.25rem; border-radius:16px; border-left:4px solid #10b981;">
            <div style="font-size:0.8rem; font-weight:700; color:rgb(var(--muted)); text-transform:uppercase; letter-spacing:0.04em;">Platform Ticket Sales</div>
            <div style="font-size:1.6rem; font-weight:800; margin:0.35rem 0; color:#10b981;">
                <?= $currency ?><?= number_format($onlineGross, 2) ?>
            </div>
            <div style="font-size:0.76rem; color:rgb(var(--muted));">
                <?= count($onlineTickets) ?> Online Purchases · Gate/Comp: <?= $currency ?><?= number_format($gateCashGross + $compNominal, 2) ?>
            </div>
        </div>

        <!-- Net Host Balance -->
        <div class="form-card" style="padding:1.25rem; border-radius:16px; border-left:4px solid #0B7366;">
            <div style="font-size:0.8rem; font-weight:700; color:rgb(var(--muted)); text-transform:uppercase; letter-spacing:0.04em;">Host Net Earnings (Payout)</div>
            <div style="font-size:1.6rem; font-weight:800; margin:0.35rem 0; color:#0B7366;">
                <?= $currency ?><?= number_format($netHostEarnings, 2) ?>
            </div>
            <div style="font-size:0.76rem; color:rgb(var(--muted));">
                <?php if ($totalPlatformFee > 0): ?>
                    After <?= $platformFeePercent ?>% platform fee (<?= $currency ?><?= number_format($totalPlatformFee, 2) ?>)
                <?php else: ?>
                    100% of online ticket sales (Buyer covered fee)
                <?php endif; ?>
            </div>
        </div>

        <!-- Total Tickets -->
        <div class="form-card" style="padding:1.25rem; border-radius:16px; border-left:4px solid #6366f1;">
            <div style="font-size:0.8rem; font-weight:700; color:rgb(var(--muted)); text-transform:uppercase; letter-spacing:0.04em;">Total Accounted Tickets</div>
            <div style="font-size:1.6rem; font-weight:800; margin:0.35rem 0; color:#6366f1;">
                <?= number_format($totalTicketsSoldOrIssued) ?>
            </div>
            <div style="font-size:0.76rem; color:rgb(var(--muted));">
                <?= count($onlineTickets) ?> Online · <?= count($issuedTickets) ?> Issued / Comp
            </div>
        </div>

        <!-- Gate Turnout -->
        <div class="form-card" style="padding:1.25rem; border-radius:16px; border-left:4px solid #f59e0b;">
            <div style="font-size:0.8rem; font-weight:700; color:rgb(var(--muted)); text-transform:uppercase; letter-spacing:0.04em;">Gate Checked-In Turnout</div>
            <div style="font-size:1.6rem; font-weight:800; margin:0.35rem 0; color:#f59e0b;">
                <?= number_format($totalCheckedIn) ?> <span style="font-size:0.95rem; font-weight:600; opacity:0.85;">(<?= $turnoutRate ?>%)</span>
            </div>
            <div style="font-size:0.76rem; color:rgb(var(--muted));">
                Verified admissions at event entrance
            </div>
        </div>

        <!-- Settlement Status -->
        <div class="form-card" style="padding:1.25rem; border-radius:16px; border-left:4px solid #8b5cf6;">
            <div style="font-size:0.8rem; font-weight:700; color:rgb(var(--muted)); text-transform:uppercase; letter-spacing:0.04em;">Settlement Status</div>
            <div style="font-size:1.15rem; font-weight:800; margin:0.6rem 0; color:#8b5cf6; text-transform:capitalize;">
                <?= !empty($payout) ? e($payout['status']) : 'Pending Settlement' ?>
            </div>
            <div style="font-size:0.76rem; color:rgb(var(--muted));">
                <?= !empty($payout['reference']) ? 'Ref: ' . e($payout['reference']) : 'Ready for reconciliation' ?>
            </div>
        </div>
    </div>

    <!-- ════════════════════════════════════════════════════════════════
         4. INTERACTIVE TAB NAVIGATION
         ════════════════════════════════════════════════════════════════ -->
    <div class="accounting-tabs-bar no-print" style="display:flex; gap:0.5rem; border-bottom:2px solid rgba(var(--muted), 0.15); margin-bottom:1.5rem;">
        <button type="button" class="accounting-tab-btn active" data-tab="tab-online" style="padding:0.75rem 1.25rem; font-size:0.92rem; font-weight:700; border:none; background:transparent; cursor:pointer; color:rgb(var(--brand)); border-bottom:3px solid rgb(var(--brand)); margin-bottom:-2px;">
            💳 Online &amp; Paystack Sales (<?= count($onlineTickets) ?>)
        </button>
        <button type="button" class="accounting-tab-btn" data-tab="tab-issued" style="padding:0.75rem 1.25rem; font-size:0.92rem; font-weight:700; border:none; background:transparent; cursor:pointer; color:rgb(var(--muted)); border-bottom:3px solid transparent; margin-bottom:-2px;">
            🎟️ Issued &amp; Comp Tickets (<?= count($issuedTickets) ?>)
        </button>
        <button type="button" class="accounting-tab-btn" data-tab="tab-reconciliation" style="padding:0.75rem 1.25rem; font-size:0.92rem; font-weight:700; border:none; background:transparent; cursor:pointer; color:rgb(var(--muted)); border-bottom:3px solid transparent; margin-bottom:-2px;">
            ⚖️ Executive Reconciliation &amp; Balances
        </button>
    </div>

    <!-- ════════════════════════════════════════════════════════════════
         TAB 1: ONLINE & PAYSTACK SALES
         ════════════════════════════════════════════════════════════════ -->
    <div class="accounting-tab-panel" id="tab-online">
        <section class="form-card" style="padding:1.5rem; border-radius:20px; margin-bottom:2rem;">
            
            <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:1rem; margin-bottom:1.25rem;">
                <div>
                    <h3 style="margin:0; font-size:1.15rem; font-weight:800;">Online &amp; Paystack Sales Audit</h3>
                    <p style="margin:0.25rem 0 0; font-size:0.82rem; color:rgb(var(--muted));">
                        Verified online purchases processed via Paystack gateway (<?= count($onlineTickets) ?> tickets)
                    </p>
                </div>
                <div class="no-print">
                    <input type="text" id="search-online-table" placeholder="🔍 Search name, email, or ref..." style="padding:0.45rem 0.85rem; border-radius:10px; border:1px solid rgba(var(--muted),0.25); font-size:0.82rem; width:260px; max-width:100%;">
                </div>
            </div>

            <?php if (empty($onlineTickets)): ?>
                <div style="text-align:center; padding:3rem 1rem; color:rgb(var(--muted));">
                    <div style="font-size:2.5rem; margin-bottom:0.5rem;">💳</div>
                    <strong>No online ticket purchases recorded yet.</strong>
                    <p style="font-size:0.85rem; margin-top:0.25rem;">Online ticket sales through Paystack will appear here with verification references.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive accounting-table-wrap" style="overflow-x:auto;">
                    <table class="data-table" id="online-sales-table" style="width:100%; border-collapse:collapse; font-size:0.85rem;">
                        <thead>
                            <tr style="background:rgba(var(--muted), 0.05); text-align:left; border-bottom:2px solid rgba(var(--muted), 0.15);">
                                <th style="padding:0.75rem 0.85rem; font-weight:700;">Ticket Code</th>
                                <th style="padding:0.75rem 0.85rem; font-weight:700;">Attendee</th>
                                <th style="padding:0.75rem 0.85rem; font-weight:700;">Tier</th>
                                <th style="padding:0.75rem 0.85rem; font-weight:700;">Paystack Reference</th>
                                <th style="padding:0.75rem 0.85rem; font-weight:700;">Gateway Status</th>
                                <th style="padding:0.75rem 0.85rem; font-weight:700; text-align:right;">Amount (<?= e($currency) ?>)</th>
                                <th style="padding:0.75rem 0.85rem; font-weight:700; text-align:center;">Gate Status</th>
                                <th style="padding:0.75rem 0.85rem; font-weight:700;">Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($onlineTickets as $ot): ?>
                                <tr style="border-bottom:1px solid rgba(var(--muted), 0.08);">
                                    <td style="padding:0.65rem 0.85rem; font-family:monospace; font-weight:700; color:rgb(var(--brand));">
                                        <?= e($ot['id']) ?>
                                    </td>
                                    <td style="padding:0.65rem 0.85rem;">
                                        <strong><?= e($ot['attendee_name'] ?: 'Guest') ?></strong>
                                        <div style="font-size:0.76rem; color:rgb(var(--muted));"><?= e($ot['attendee_email']) ?></div>
                                    </td>
                                    <td style="padding:0.65rem 0.85rem;">
                                        <span class="badge" style="background:rgba(var(--muted),0.12); font-weight:600; font-size:0.76rem; padding:0.2rem 0.5rem; border-radius:6px;">
                                            <?= e($ot['ticket_tier'] ?? 'General') ?>
                                        </span>
                                    </td>
                                    <td style="padding:0.65rem 0.85rem; font-family:monospace; font-size:0.78rem; color:#6366f1;">
                                        <?= e($ot['extracted_ref']) ?>
                                    </td>
                                    <td style="padding:0.65rem 0.85rem;">
                                        <span style="display:inline-flex; align-items:center; gap:0.3rem; color:#10b981; font-weight:600; font-size:0.78rem;">
                                            <span>●</span> <?= e($ot['gateway_status']) ?>
                                        </span>
                                    </td>
                                    <td style="padding:0.65rem 0.85rem; text-align:right; font-weight:700;">
                                        <?= number_format((float)$ot['price'], 2) ?>
                                    </td>
                                    <td style="padding:0.65rem 0.85rem; text-align:center;">
                                        <?php if (!empty($ot['checked_in'])): ?>
                                            <span style="color:#10b981; font-weight:700; font-size:0.78rem;">✓ Checked In</span>
                                        <?php else: ?>
                                            <span style="color:rgb(var(--muted)); font-size:0.78rem;">Awaiting Entry</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="padding:0.65rem 0.85rem; font-size:0.78rem; color:rgb(var(--muted));">
                                        <?= !empty($ot['purchase_date']) ? date('M d, Y • H:i', strtotime($ot['purchase_date'])) : 'N/A' ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr style="background:rgba(var(--muted), 0.06); font-weight:800; border-top:2px solid rgba(var(--muted), 0.2);">
                                <td colspan="5" style="padding:0.85rem; font-size:0.9rem;">TOTAL ONLINE PAYSTACK SALES (<?= count($onlineTickets) ?> Tickets)</td>
                                <td style="padding:0.85rem; text-align:right; font-size:1rem; color:#10b981;">
                                    <?= e($currency) ?><?= number_format($onlineGross, 2) ?>
                                </td>
                                <td style="padding:0.85rem; text-align:center; font-size:0.82rem; color:rgb(var(--muted));">
                                    <?= count(array_filter($onlineTickets, fn($x) => !empty($x['checked_in']))) ?> checked in
                                </td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <!-- Mobile Card Stream (Displays on screens <= 768px) -->
                <div class="accounting-cards-mobile" id="online-tickets-cards">
                    <?php foreach ($onlineTickets as $ot): ?>
                        <div class="accounting-ticket-card" data-search="<?= strtolower(e($ot['id'] . ' ' . ($ot['attendee_name'] ?? '') . ' ' . ($ot['attendee_email'] ?? '') . ' ' . ($ot['extracted_ref'] ?? ''))) ?>">
                            <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:0.5rem;">
                                <div>
                                    <span style="font-family:monospace; font-weight:700; color:rgb(var(--brand)); font-size:0.88rem;"><?= e($ot['id']) ?></span>
                                    <h4 style="margin:0.25rem 0 0; font-size:0.95rem; font-weight:700;"><?= e($ot['attendee_name'] ?: 'Guest') ?></h4>
                                    <div style="font-size:0.78rem; color:rgb(var(--muted)); word-break:break-all;"><?= e($ot['attendee_email']) ?></div>
                                </div>
                                <div style="text-align:right; flex-shrink:0;">
                                    <div style="font-weight:800; font-size:1rem; color:#10b981;"><?= e($currency) ?><?= number_format((float)$ot['price'], 2) ?></div>
                                    <span class="badge" style="background:rgba(var(--muted),0.12); font-weight:600; font-size:0.74rem; padding:0.2rem 0.5rem; border-radius:6px; display:inline-block; margin-top:0.25rem;">
                                        <?= e($ot['ticket_tier'] ?? 'General') ?>
                                    </span>
                                </div>
                            </div>
                            <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:0.5rem; font-size:0.78rem; border-top:1px dashed rgba(var(--muted),0.15); padding-top:0.5rem; margin-top:0.25rem;">
                                <div>
                                    <?php if (!empty($ot['checked_in'])): ?>
                                        <span style="color:#10b981; font-weight:700;">✓ Checked In</span>
                                    <?php else: ?>
                                        <span style="color:rgb(var(--muted));">Awaiting Entry</span>
                                    <?php endif; ?>
                                    <span style="margin: 0 0.35rem; color:rgba(var(--muted),0.4);">•</span>
                                    <span style="color:#10b981; font-weight:600;">● <?= e($ot['gateway_status']) ?></span>
                                </div>
                                <div style="color:rgb(var(--muted));">
                                    <?= !empty($ot['purchase_date']) ? date('M d, Y • H:i', strtotime($ot['purchase_date'])) : 'N/A' ?>
                                </div>
                            </div>
                            <?php if (!empty($ot['extracted_ref'])): ?>
                                <div style="font-size:0.74rem; color:#6366f1; font-family:monospace; word-break:break-all;">
                                    Ref: <?= e($ot['extracted_ref']) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </div>

    <!-- ════════════════════════════════════════════════════════════════
         TAB 2: ISSUED & COMP TICKETS
         ════════════════════════════════════════════════════════════════ -->
    <div class="accounting-tab-panel" id="tab-issued" style="display:none;">
        <section class="form-card" style="padding:1.5rem; border-radius:20px; margin-bottom:2rem;">
            
            <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:1rem; margin-bottom:1.25rem;">
                <div>
                    <h3 style="margin:0; font-size:1.15rem; font-weight:800;">Issued, Complimentary &amp; Gate Tickets</h3>
                    <p style="margin:0.25rem 0 0; font-size:0.82rem; color:rgb(var(--muted));">
                        Includes host complimentary passes, VIP invitations, and on-site gate box-office sales (<?= count($issuedTickets) ?> tickets)
                    </p>
                </div>
                <div class="no-print">
                    <input type="text" id="search-issued-table" placeholder="🔍 Search recipient or ref..." style="padding:0.45rem 0.85rem; border-radius:10px; border:1px solid rgba(var(--muted),0.25); font-size:0.82rem; width:260px; max-width:100%;">
                </div>
            </div>

            <?php if (empty($issuedTickets)): ?>
                <div style="text-align:center; padding:3rem 1rem; color:rgb(var(--muted));">
                    <div style="font-size:2.5rem; margin-bottom:0.5rem;">🎟️</div>
                    <strong>No issued or complimentary tickets recorded.</strong>
                    <p style="font-size:0.85rem; margin-top:0.25rem;">Tickets issued manually or through the gate cash box office terminal will appear here.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive accounting-table-wrap" style="overflow-x:auto;">
                    <table class="data-table" id="issued-tickets-table" style="width:100%; border-collapse:collapse; font-size:0.85rem;">
                        <thead>
                            <tr style="background:rgba(var(--muted), 0.05); text-align:left; border-bottom:2px solid rgba(var(--muted), 0.15);">
                                <th style="padding:0.75rem 0.85rem; font-weight:700;">Ticket Code</th>
                                <th style="padding:0.75rem 0.85rem; font-weight:700;">Recipient</th>
                                <th style="padding:0.75rem 0.85rem; font-weight:700;">Tier</th>
                                <th style="padding:0.75rem 0.85rem; font-weight:700;">Issuance Type</th>
                                <th style="padding:0.75rem 0.85rem; font-weight:700;">Audit / Terminal Notes</th>
                                <th style="padding:0.75rem 0.85rem; font-weight:700; text-align:right;">Nominal Value (<?= e($currency) ?>)</th>
                                <th style="padding:0.75rem 0.85rem; font-weight:700; text-align:center;">Gate Status</th>
                                <th style="padding:0.75rem 0.85rem; font-weight:700;">Issue Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($issuedTickets as $it): ?>
                                <tr style="border-bottom:1px solid rgba(var(--muted), 0.08);">
                                    <td style="padding:0.65rem 0.85rem; font-family:monospace; font-weight:700; color:rgb(var(--brand));">
                                        <?= e($it['id']) ?>
                                    </td>
                                    <td style="padding:0.65rem 0.85rem;">
                                        <strong><?= e($it['attendee_name'] ?: 'Guest') ?></strong>
                                        <div style="font-size:0.76rem; color:rgb(var(--muted));"><?= e($it['attendee_email']) ?></div>
                                    </td>
                                    <td style="padding:0.65rem 0.85rem;">
                                        <span class="badge" style="background:rgba(var(--muted),0.12); font-weight:600; font-size:0.76rem; padding:0.2rem 0.5rem; border-radius:6px;">
                                            <?= e($it['ticket_tier'] ?? 'General') ?>
                                        </span>
                                    </td>
                                    <td style="padding:0.65rem 0.85rem;">
                                        <span style="font-size:0.78rem; font-weight:700; color:<?= str_contains($it['issuance_type'], 'Gate') ? '#10b981' : '#6366f1' ?>;">
                                            <?= e($it['issuance_type']) ?>
                                        </span>
                                    </td>
                                    <td style="padding:0.65rem 0.85rem; font-size:0.78rem; color:rgb(var(--muted));">
                                        <?= e($it['status_detail'] ?: 'Direct issuance by organizer') ?>
                                    </td>
                                    <td style="padding:0.65rem 0.85rem; text-align:right; font-weight:700;">
                                        <?= number_format((float)$it['price'], 2) ?>
                                    </td>
                                    <td style="padding:0.65rem 0.85rem; text-align:center;">
                                        <?php if (!empty($it['checked_in'])): ?>
                                            <span style="color:#10b981; font-weight:700; font-size:0.78rem;">✓ Checked In</span>
                                        <?php else: ?>
                                            <span style="color:rgb(var(--muted)); font-size:0.78rem;">Awaiting Entry</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="padding:0.65rem 0.85rem; font-size:0.78rem; color:rgb(var(--muted));">
                                        <?= !empty($it['purchase_date']) ? date('M d, Y • H:i', strtotime($it['purchase_date'])) : 'N/A' ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr style="background:rgba(var(--muted), 0.06); font-weight:800; border-top:2px solid rgba(var(--muted), 0.2);">
                                <td colspan="5" style="padding:0.85rem; font-size:0.9rem;">
                                    TOTAL ISSUED TICKETS (<?= count($issuedTickets) ?> Tickets · Gate Cash Collected: <?= e($currency) ?><?= number_format($gateCashGross, 2) ?>)
                                </td>
                                <td style="padding:0.85rem; text-align:right; font-size:1rem; color:#6366f1;">
                                    <?= e($currency) ?><?= number_format($gateCashGross + $compNominal, 2) ?>
                                </td>
                                <td style="padding:0.85rem; text-align:center; font-size:0.82rem; color:rgb(var(--muted));">
                                    <?= count(array_filter($issuedTickets, fn($x) => !empty($x['checked_in']))) ?> checked in
                                </td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <!-- Mobile Card Stream (Displays on screens <= 768px) -->
                <div class="accounting-cards-mobile" id="issued-tickets-cards">
                    <?php foreach ($issuedTickets as $it): ?>
                        <div class="accounting-ticket-card" data-search="<?= strtolower(e($it['id'] . ' ' . ($it['attendee_name'] ?? '') . ' ' . ($it['attendee_email'] ?? '') . ' ' . ($it['issuance_type'] ?? ''))) ?>">
                            <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:0.5rem;">
                                <div>
                                    <span style="font-family:monospace; font-weight:700; color:rgb(var(--brand)); font-size:0.88rem;"><?= e($it['id']) ?></span>
                                    <h4 style="margin:0.25rem 0 0; font-size:0.95rem; font-weight:700;"><?= e($it['attendee_name'] ?: 'Guest') ?></h4>
                                    <div style="font-size:0.78rem; color:rgb(var(--muted)); word-break:break-all;"><?= e($it['attendee_email']) ?></div>
                                </div>
                                <div style="text-align:right; flex-shrink:0;">
                                    <div style="font-weight:800; font-size:1rem; color:#6366f1;"><?= e($currency) ?><?= number_format((float)$it['price'], 2) ?></div>
                                    <span class="badge" style="background:rgba(var(--muted),0.12); font-weight:600; font-size:0.74rem; padding:0.2rem 0.5rem; border-radius:6px; display:inline-block; margin-top:0.25rem;">
                                        <?= e($it['ticket_tier'] ?? 'General') ?>
                                    </span>
                                </div>
                            </div>
                            <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:0.5rem; font-size:0.78rem; border-top:1px dashed rgba(var(--muted),0.15); padding-top:0.5rem; margin-top:0.25rem;">
                                <div>
                                    <?php if (!empty($it['checked_in'])): ?>
                                        <span style="color:#10b981; font-weight:700;">✓ Checked In</span>
                                    <?php else: ?>
                                        <span style="color:rgb(var(--muted));">Awaiting Entry</span>
                                    <?php endif; ?>
                                    <span style="margin: 0 0.35rem; color:rgba(var(--muted),0.4);">•</span>
                                    <span style="font-weight:700; color:<?= str_contains($it['issuance_type'], 'Gate') ? '#10b981' : '#6366f1' ?>;">
                                        <?= e($it['issuance_type']) ?>
                                    </span>
                                </div>
                                <div style="color:rgb(var(--muted));">
                                    <?= !empty($it['purchase_date']) ? date('M d, Y • H:i', strtotime($it['purchase_date'])) : 'N/A' ?>
                                </div>
                            </div>
                            <?php if (!empty($it['status_detail'])): ?>
                                <div style="font-size:0.74rem; color:rgb(var(--muted));">
                                    <?= e($it['status_detail']) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </div>

    <!-- ════════════════════════════════════════════════════════════════
         TAB 3: EXECUTIVE RECONCILIATION & BALANCES
         ════════════════════════════════════════════════════════════════ -->
    <div class="accounting-tab-panel" id="tab-reconciliation" style="display:none;">
        <section class="form-card" style="padding:2rem; border-radius:20px; margin-bottom:2rem; max-width:900px; margin-left:auto; margin-right:auto;">
            
            <div style="border-bottom:2px solid rgba(var(--muted), 0.15); padding-bottom:1.25rem; margin-bottom:1.75rem; text-align:center;">
                <h2 style="margin:0 0 0.4rem; font-size:1.35rem; font-weight:800; text-transform:uppercase; letter-spacing:0.04em; color:rgb(var(--brand));">
                    Executive Financial Reconciliation Statement
                </h2>
                <p style="margin:0; font-size:0.85rem; color:rgb(var(--muted));">
                    Comprehensive accounting of all ticket receipts, platform fees, and net settlement balances
                </p>
            </div>

            <!-- SECTION 1: EVENT PROFILE -->
            <div style="margin-bottom:1.75rem;">
                <h4 style="margin:0 0 0.65rem; font-size:0.88rem; font-weight:800; text-transform:uppercase; letter-spacing:0.05em; color:rgb(var(--muted));">
                    1. Event &amp; Organizer Profile
                </h4>
                <div style="border:1px solid rgba(var(--muted), 0.15); border-radius:12px; overflow:hidden;">
                    <div class="reconcile-row" style="border-bottom:1px solid rgba(var(--muted),0.08);">
                        <span style="color:rgb(var(--muted));">Event Title</span>
                        <strong><?= e($event->name) ?></strong>
                    </div>
                    <div class="reconcile-row" style="border-bottom:1px solid rgba(var(--muted),0.08);">
                        <span style="color:rgb(var(--muted));">Scheduled Date &amp; Time</span>
                        <span><?= e($formattedEventDate) ?></span>
                    </div>
                    <div class="reconcile-row" style="border-bottom:1px solid rgba(var(--muted),0.08);">
                        <span style="color:rgb(var(--muted));">Venue &amp; Location</span>
                        <span><?= e($event->location ?: 'Venue TBA') ?></span>
                    </div>
                    <div class="reconcile-row">
                        <span style="color:rgb(var(--muted));">Ticketing Fee Configuration</span>
                        <span><?= !empty($event->pass_fee) ? 'Platform fee passed to ticket buyers' : 'Platform fee absorbed by host' ?></span>
                    </div>
                </div>
            </div>

            <!-- SECTION 2: TICKET INVENTORY & ATTENDANCE -->
            <div style="margin-bottom:1.75rem;">
                <h4 style="margin:0 0 0.65rem; font-size:0.88rem; font-weight:800; text-transform:uppercase; letter-spacing:0.05em; color:rgb(var(--muted));">
                    2. Ticket Inventory &amp; Attendance Audit
                </h4>
                <div style="border:1px solid rgba(var(--muted), 0.15); border-radius:12px; overflow:hidden;">
                    <div class="reconcile-row" style="border-bottom:1px solid rgba(var(--muted),0.08);">
                        <span style="color:rgb(var(--muted));">Total Event Capacity</span>
                        <strong><?= number_format($totalCapacity) ?> tickets</strong>
                    </div>
                    <div class="reconcile-row" style="border-bottom:1px solid rgba(var(--muted),0.08);">
                        <span style="color:rgb(var(--muted));">Online / Paystack Sold Tickets</span>
                        <strong><?= number_format(count($onlineTickets)) ?> tickets</strong>
                    </div>
                    <div class="reconcile-row" style="border-bottom:1px solid rgba(var(--muted),0.08);">
                        <span style="color:rgb(var(--muted));">Host &amp; Gate Issued Tickets</span>
                        <strong><?= number_format(count($issuedTickets)) ?> tickets</strong>
                    </div>
                    <div class="reconcile-row" style="border-bottom:1px solid rgba(var(--muted),0.08);">
                        <span style="color:rgb(var(--muted));">Total Attendance Roster</span>
                        <strong style="color:rgb(var(--brand));"><?= number_format($totalTicketsSoldOrIssued) ?> attendees (<?= $occupancyRate ?>% capacity taken)</strong>
                    </div>
                    <div class="reconcile-row">
                        <span style="color:rgb(var(--muted));">Gate Scanner Turnout</span>
                        <strong style="color:#10b981;"><?= number_format($totalCheckedIn) ?> verified attendees (<?= $turnoutRate ?>% turnout)</strong>
                    </div>
                </div>
            </div>

            <!-- SECTION 3: FINANCIAL RECONCILIATION -->
            <div style="margin-bottom:1.75rem;">
                <h4 style="margin:0 0 0.65rem; font-size:0.88rem; font-weight:800; text-transform:uppercase; letter-spacing:0.05em; color:rgb(var(--muted));">
                    3. Financial Settlement &amp; Revenue Reconciliation
                </h4>
                <div style="border:1px solid rgba(var(--muted), 0.15); border-radius:12px; overflow:hidden;">
                    <div class="reconcile-row" style="border-bottom:1px solid rgba(var(--muted),0.08);">
                        <span>Gross Online / Paystack Purchases</span>
                        <strong><?= e($currency) ?><?= number_format($onlineGross, 2) ?></strong>
                    </div>
                    <?php if ($totalPlatformFee > 0): ?>
                    <div class="reconcile-row" style="border-bottom:1px solid rgba(var(--muted),0.08); color:#ef4444;">
                        <span>Platform Ticketing Fee (<?= $platformFeePercent ?>% absorbed by host)</span>
                        <strong>-<?= e($currency) ?><?= number_format($totalPlatformFee, 2) ?></strong>
                    </div>
                    <?php else: ?>
                    <div class="reconcile-row" style="border-bottom:1px solid rgba(var(--muted),0.08); color:rgb(var(--muted));">
                        <span>Platform Ticketing Fee</span>
                        <span>Covered by ticket buyer (+5% fee paid at checkout)</span>
                    </div>
                    <?php endif; ?>
                    <div class="reconcile-row" style="border-bottom:1px solid rgba(var(--muted),0.08);">
                        <span>Host Settlement Status</span>
                        <strong style="text-transform:capitalize; color:#6366f1;"><?= !empty($payout) ? e($payout['status']) : 'Pending Settlement' ?></strong>
                    </div>
                    
                    <!-- FINAL BALANCE ROW -->
                    <div class="reconcile-final-row">
                        <div>
                            <div style="font-size:0.95rem; font-weight:800; color:rgb(var(--brand));">ORGANIZER PAYOUT ENTITLEMENT (PLATFORM SHARE)</div>
                            <div style="font-size:0.76rem; color:rgb(var(--muted));">Online sales amount approved &amp; reconciled for host bank transfer</div>
                        </div>
                        <div style="font-size:1.5rem; font-weight:900; color:rgb(var(--brand));">
                            <?= e($currency) ?><?= number_format($netHostEarnings, 2) ?>
                        </div>
                    </div>

                    <!-- OFFLINE RECEIPTS AUDIT (EXCLUDED FROM PLATFORM DISBURSEMENT) -->
                    <div style="padding:0.85rem 1rem; background:rgba(var(--muted), 0.03); border-top:1px dashed rgba(var(--muted), 0.15); font-size:0.82rem;">
                        <span style="font-weight:700; color:rgb(var(--muted)); text-transform:uppercase; font-size:0.74rem; letter-spacing:0.04em; display:block; margin-bottom:0.4rem;">
                            Direct Host / Gate Issuance (Retained Directly by Host — Excluded from Payout)
                        </span>
                        <div style="display:flex; justify-content:space-between; margin-bottom:0.3rem;">
                            <span style="color:rgb(var(--muted));">On-site Gate Cash Receipts (Host Pocket):</span>
                            <strong><?= e($currency) ?><?= number_format($gateCashGross, 2) ?></strong>
                        </div>
                        <div style="display:flex; justify-content:space-between;">
                            <span style="color:rgb(var(--muted));">Complimentary / Free Pass Valuation:</span>
                            <span><?= e($currency) ?><?= number_format($compNominal, 2) ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sign-off Footer -->
            <div style="margin-top:2rem; padding-top:1.25rem; border-top:1px dashed rgba(var(--muted), 0.2); display:flex; justify-content:space-between; font-size:0.78rem; color:rgb(var(--muted)); flex-wrap:wrap; gap:1rem;">
                <div>Report Reference: <code>ACC-<?= $event->id ?>-<?= strtoupper(substr(md5($event->id . $event->date), 0, 8)) ?></code></div>
                <div>Authorized Platform Statement · Eventlyy Automated Ledger</div>
            </div>
        </section>
    </div>

</div>

<!-- ════════════════════════════════════════════════════════════════
     PRINT STYLING & INTERACTIVE TAB SCRIPT
     ════════════════════════════════════════════════════════════════ -->
<style>
@media print {
    .no-print, nav, .sidebar, header, footer, .mgmt-topbar {
        display: none !important;
    }
    .print-only-brand {
        display: block !important;
    }
    .accounting-tab-panel {
        display: block !important;
        page-break-after: always;
    }
    .form-card {
        box-shadow: none !important;
        border: 1px solid #ccc !important;
    }
    body {
        background: #fff !important;
        color: #000 !important;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Tab switching
    const tabBtns = document.querySelectorAll('.accounting-tab-btn');
    const tabPanels = document.querySelectorAll('.accounting-tab-panel');

    tabBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const targetId = this.getAttribute('data-tab');
            
            tabBtns.forEach(b => {
                b.style.color = 'rgb(var(--muted))';
                b.style.borderBottomColor = 'transparent';
                b.classList.remove('active');
            });
            this.style.color = 'rgb(var(--brand))';
            this.style.borderBottomColor = 'rgb(var(--brand))';
            this.classList.add('active');

            tabPanels.forEach(p => {
                if (p.id === targetId) {
                    p.style.display = 'block';
                } else {
                    p.style.display = 'none';
                }
            });
        });
    });

    // Quick filter for Online Sales table & mobile cards
    const searchOnline = document.getElementById('search-online-table');
    if (searchOnline) {
        searchOnline.addEventListener('input', function() {
            const term = this.value.toLowerCase().trim();
            const rows = document.querySelectorAll('#online-sales-table tbody tr');
            rows.forEach(r => {
                const text = r.textContent.toLowerCase();
                r.style.display = text.includes(term) ? '' : 'none';
            });
            const cards = document.querySelectorAll('#online-tickets-cards .accounting-ticket-card');
            cards.forEach(c => {
                const text = (c.getAttribute('data-search') || c.textContent).toLowerCase();
                c.style.display = text.includes(term) ? '' : 'none';
            });
        });
    }

    // Quick filter for Issued Tickets table & mobile cards
    const searchIssued = document.getElementById('search-issued-table');
    if (searchIssued) {
        searchIssued.addEventListener('input', function() {
            const term = this.value.toLowerCase().trim();
            const rows = document.querySelectorAll('#issued-tickets-table tbody tr');
            rows.forEach(r => {
                const text = r.textContent.toLowerCase();
                r.style.display = text.includes(term) ? '' : 'none';
            });
            const cards = document.querySelectorAll('#issued-tickets-cards .accounting-ticket-card');
            cards.forEach(c => {
                const text = (c.getAttribute('data-search') || c.textContent).toLowerCase();
                c.style.display = text.includes(term) ? '' : 'none';
            });
        });
    }
});
</script>
@endsection
