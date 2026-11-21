<?php

/**
 * Host Event Accounting & Financial Settlement Report
 *
 * Comprehensive post-event accounting and reconciliation view displaying Paystack-verified
 * online ticket purchases, online merchandise & add-on sales, complimentary/gate passes,
 * and dual-perspective reconciliation (plain-English summary + GAAP audit ledger).
 *
 * @package Evently
 * @subpackage Views/Host
 * @author Celio Natti
 * @version 4.0.0
 */

$currency = get_currency_symbol();
$formattedEventDate = !empty($event->date) ? date('l, F d, Y • h:i A', strtotime($event->date)) : 'Date TBA';

// Ensure collections are plain arrays
$onlineTickets = is_array($onlineTickets) ? $onlineTickets : (method_exists($onlineTickets, 'toArray') ? $onlineTickets->toArray() : iterator_to_array($onlineTickets));
$issuedTickets = is_array($issuedTickets) ? $issuedTickets : (method_exists($issuedTickets, 'toArray') ? $issuedTickets->toArray() : iterator_to_array($issuedTickets));
$allTickets = is_array($allTickets) ? $allTickets : (method_exists($allTickets, 'toArray') ? $allTickets->toArray() : iterator_to_array($allTickets));
$merchOrders = is_array($merchOrders ?? null) ? $merchOrders : [];
$allEventPayouts = is_array($allEventPayouts ?? null) ? $allEventPayouts : [];

$merchTotalSales = (float)($merchTotalSales ?? 0.00);
$merchTotalQty = (int)($merchTotalQty ?? 0);
$merchFulfilledQty = (int)($merchFulfilledQty ?? 0);
$merchPendingQty = (int)($merchPendingQty ?? 0);
$totalDisbursedPayouts = (float)($totalDisbursedPayouts ?? 0.00);
$pendingPayoutAmount = (float)($pendingPayoutAmount ?? 0.00);
$remainingHostBalance = (float)($remainingHostBalance ?? 0.00);
?>

@extends('layouts/host')

@section('title')
Accounting Report - <?= e($event->name) ?>
@endsection

@section('content')
<style>
/* ════════════════════════════════════════════════════════════════
   HOST ACCOUNTING REPORT — RESPONSIVE & DUAL-AUDIT STYLES
   ════════════════════════════════════════════════════════════════ */

.accounting-wrapper {
    max-width: 1360px;
    margin: 0 auto 3.5rem;
    padding: 0 0.5rem;
}

/* 1. Top Context Navigation */
.acc-topbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1rem;
    margin-bottom: 1.25rem;
}

.acc-back-link {
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    font-weight: 700;
    font-size: 0.88rem;
    color: var(--vn-ink, rgb(var(--text)));
    text-decoration: none;
    padding: 0.45rem 0.85rem;
    border-radius: 10px;
    background: var(--vn-parchment-warm, rgba(var(--muted), 0.08));
    border: 1px solid var(--vn-border, rgba(var(--muted), 0.2));
    transition: all 0.2s ease;
}

.acc-back-link:hover {
    color: var(--vn-brass, #b8860b);
    border-color: var(--vn-brass, #b8860b);
}

.acc-actions-wrap {
    display: flex;
    align-items: center;
    gap: 0.65rem;
    flex-wrap: wrap;
}

.acc-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.4rem;
    font-size: 0.84rem;
    font-weight: 700;
    padding: 0.52rem 1.05rem;
    border-radius: 10px;
    text-decoration: none;
    border: 1px solid transparent;
    cursor: pointer;
    transition: all 0.2s ease;
    white-space: nowrap;
    font-family: inherit;
}

.acc-btn--primary {
    background: var(--vn-forest, #0B7366);
    color: #fff !important;
}

.acc-btn--primary:hover {
    filter: brightness(1.1);
    transform: translateY(-1px);
}

.acc-btn--outline {
    background: var(--vn-parchment-card, rgb(var(--card)));
    color: var(--vn-ink, rgb(var(--text))) !important;
    border-color: var(--vn-border, rgba(var(--muted), 0.25));
}

.acc-btn--outline:hover {
    background: var(--vn-parchment-warm, rgba(var(--muted), 0.08));
    border-color: var(--vn-brass, #b8860b);
    color: var(--vn-brass, #b8860b) !important;
}

/* 2. Hero Header Card */
.acc-hero-card {
    background: var(--vn-parchment-card, rgb(var(--card))) !important;
    border: 1.5px solid var(--vn-border, rgba(var(--muted), 0.18)) !important;
    border-radius: 20px !important;
    padding: 1.75rem 2rem;
    box-shadow: var(--vn-shadow-elevated, 0 4px 20px rgba(0,0,0,0.04));
    margin-bottom: 1.5rem;
    position: relative;
    overflow: hidden;
}

.acc-hero-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.3rem 0.75rem;
    border-radius: 20px;
    background: rgba(11, 115, 102, 0.12);
    color: var(--vn-forest, #0B7366);
    font-size: 0.78rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin-bottom: 0.65rem;
}

.acc-hero-title {
    font-family: var(--vn-font-serif, serif) !important;
    font-size: clamp(1.4rem, 2.5vw, 1.95rem);
    font-weight: normal;
    color: var(--vn-ink, rgb(var(--text))) !important;
    margin: 0 0 0.5rem;
    line-height: 1.25;
}

.acc-hero-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 1.25rem;
    font-size: 0.86rem;
    color: var(--vn-ink-muted, rgb(var(--muted)));
}

/* 3. Plain-English Summary Strip (for Non-Accountants) */
.plain-english-strip {
    background: linear-gradient(135deg, rgba(16, 185, 129, 0.06) 0%, rgba(11, 115, 102, 0.09) 100%);
    border: 1.5px solid rgba(16, 185, 129, 0.28);
    border-radius: 16px;
    padding: 1.25rem 1.5rem;
    margin-bottom: 1.75rem;
}

.plain-strip-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 0.5rem;
    margin-bottom: 0.75rem;
}

.plain-strip-title {
    font-size: 0.88rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: #065f46;
    display: flex;
    align-items: center;
    gap: 0.45rem;
}

.plain-strip-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 0.85rem;
}

.plain-strip-item {
    background: var(--vn-parchment-card, #ffffff);
    border: 1px solid rgba(16, 185, 129, 0.2);
    border-radius: 12px;
    padding: 0.75rem 1rem;
}

.plain-strip-label {
    font-size: 0.74rem;
    font-weight: 700;
    color: var(--vn-ink-muted, #64748b);
    text-transform: uppercase;
    letter-spacing: 0.03em;
    display: block;
    margin-bottom: 0.25rem;
}

.plain-strip-value {
    font-size: 1.15rem;
    font-weight: 800;
    color: var(--vn-ink, #0f172a);
    font-family: var(--vn-font-serif, sans-serif);
}

.plain-strip-sub {
    font-size: 0.72rem;
    color: var(--vn-ink-muted, #64748b);
    margin-top: 0.2rem;
}

/* 4. Primary KPI Metric Cards */
.acc-kpi-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1rem;
    margin-bottom: 2rem;
}

.acc-kpi-card {
    background: var(--vn-parchment-card, rgb(var(--card))) !important;
    border: 1.5px solid var(--vn-border, rgba(var(--muted), 0.16)) !important;
    border-radius: 16px !important;
    padding: 1.25rem 1.35rem;
    box-shadow: var(--vn-shadow-elevated, 0 2px 10px rgba(0,0,0,0.03));
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.acc-kpi-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(0,0,0,0.06);
}

.acc-kpi-label {
    font-size: 0.78rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: var(--vn-ink-muted, rgb(var(--muted)));
    display: block;
    margin-bottom: 0.4rem;
}

.acc-kpi-value {
    font-size: clamp(1.35rem, 2vw, 1.75rem);
    font-weight: 900;
    font-family: var(--vn-font-serif, sans-serif);
    margin: 0 0 0.35rem;
    line-height: 1.2;
}

.acc-kpi-sub {
    font-size: 0.78rem;
    color: var(--vn-ink-muted, rgb(var(--muted)));
    margin: 0;
}

/* 5. Tab Navigation Bar */
.acc-tabs-bar {
    display: flex;
    gap: 0.5rem;
    border-bottom: 2px solid var(--vn-border, rgba(var(--muted), 0.16));
    margin-bottom: 1.75rem;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: none;
    white-space: nowrap;
    padding-bottom: 2px;
}

.acc-tabs-bar::-webkit-scrollbar {
    display: none;
}

.acc-tab-btn {
    padding: 0.75rem 1.35rem;
    font-size: 0.92rem;
    font-weight: 700;
    border: none;
    background: transparent;
    cursor: pointer;
    color: var(--vn-ink-muted, rgb(var(--muted)));
    border-bottom: 3px solid transparent;
    margin-bottom: -2px;
    flex-shrink: 0;
    white-space: nowrap;
    transition: all 0.2s ease;
    font-family: inherit;
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
}

.acc-tab-btn:hover {
    color: var(--vn-forest, #0B7366);
}

.acc-tab-btn.active {
    color: var(--vn-forest, #0B7366);
    border-bottom-color: var(--vn-forest, #0B7366);
}

/* 6. Content Section Cards & Tables */
.acc-card {
    background: var(--vn-parchment-card, rgb(var(--card))) !important;
    border: 1.5px solid var(--vn-border, rgba(var(--muted), 0.16)) !important;
    border-radius: 20px !important;
    padding: 1.5rem 1.75rem;
    box-shadow: var(--vn-shadow-elevated, 0 4px 18px rgba(0,0,0,0.03));
    margin-bottom: 2rem;
}

.acc-card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1rem;
    margin-bottom: 1.25rem;
}

.acc-card-title {
    font-family: var(--vn-font-serif, serif);
    font-size: 1.25rem;
    color: var(--vn-ink, rgb(var(--text)));
    margin: 0;
    font-weight: normal;
}

.acc-card-sub {
    font-size: 0.82rem;
    color: var(--vn-ink-muted, rgb(var(--muted)));
    margin: 0.25rem 0 0;
}

.acc-search-input {
    padding: 0.5rem 0.95rem;
    border-radius: 11px;
    border: 1px solid var(--vn-border, rgba(var(--muted), 0.22));
    background: var(--vn-parchment-warm, rgba(var(--muted), 0.05));
    color: var(--vn-ink, rgb(var(--text)));
    font-size: 0.84rem;
    min-width: 250px;
    max-width: 100%;
    font-family: inherit;
    outline: none;
    transition: border-color 0.2s ease;
}

.acc-search-input:focus {
    border-color: var(--vn-brass, #b8860b);
}

/* Table Design */
.acc-table-wrap {
    width: 100%;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    border-radius: 12px;
    border: 1px solid var(--vn-border, rgba(var(--muted), 0.14));
}

.acc-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.86rem;
    text-align: left;
}

.acc-table th {
    background: var(--vn-parchment-warm, rgba(var(--muted), 0.08)) !important;
    color: var(--vn-ink-soft, rgb(var(--muted))) !important;
    font-size: 0.76rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    padding: 0.85rem 1rem;
    border-bottom: 1.5px solid var(--vn-border, rgba(var(--muted), 0.2));
    white-space: nowrap;
}

.acc-table td {
    padding: 0.85rem 1rem;
    border-bottom: 1px solid var(--vn-border, rgba(var(--muted), 0.08));
    color: var(--vn-ink, rgb(var(--text)));
    vertical-align: middle;
}

.acc-table tr:hover td {
    background: var(--vn-parchment-subtle, rgba(var(--muted), 0.03));
}

.acc-table tfoot td {
    background: var(--vn-parchment-warm, rgba(var(--muted), 0.08));
    border-top: 2px solid var(--vn-border-dark, rgba(var(--muted), 0.25));
    font-weight: 800;
    padding: 0.95rem 1rem;
}

/* Mobile Stream Cards */
.acc-mobile-stream {
    display: none;
    flex-direction: column;
    gap: 0.85rem;
}

.acc-item-card {
    background: var(--vn-parchment-warm, rgba(var(--muted), 0.04));
    border: 1px solid var(--vn-border, rgba(var(--muted), 0.16));
    border-radius: 14px;
    padding: 1.1rem;
    display: flex;
    flex-direction: column;
    gap: 0.65rem;
}

/* Reconciliation Statement Rows */
.reconcile-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.85rem 1.15rem;
    border-bottom: 1px solid var(--vn-border, rgba(var(--muted), 0.08));
    font-size: 0.88rem;
    gap: 0.75rem;
}

.reconcile-final-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.25rem 1.15rem;
    background: rgba(11, 115, 102, 0.08);
    border-top: 2px solid var(--vn-forest, #0B7366);
    border-bottom: 2px solid var(--vn-forest, #0B7366);
    gap: 0.75rem;
}

/* ════════════════════════════════════════════════════════════════
   RESPONSIVE BREAKPOINTS (TABLET & MOBILE)
   ════════════════════════════════════════════════════════════════ */

/* Tablet (769px to 1024px) */
@media (max-width: 1024px) {
    .acc-kpi-grid {
        grid-template-columns: repeat(2, 1fr) !important;
    }
}

/* Mobile (<= 768px) */
@media (max-width: 768px) {
    .acc-table-wrap {
        display: none !important;
    }
    .acc-mobile-stream {
        display: flex !important;
    }
    .acc-kpi-grid {
        grid-template-columns: 1fr !important;
        gap: 0.75rem !important;
    }
    .plain-strip-grid {
        grid-template-columns: 1fr !important;
    }
    .acc-hero-card {
        padding: 1.25rem 1rem !important;
    }
    .acc-card {
        padding: 1.15rem 1rem !important;
    }
    .reconcile-row {
        flex-direction: column !important;
        align-items: flex-start !important;
        gap: 0.35rem !important;
        padding: 0.75rem 0.85rem !important;
    }
    .reconcile-final-row {
        flex-direction: column !important;
        align-items: flex-start !important;
        gap: 0.5rem !important;
        padding: 1rem 0.85rem !important;
    }
    .acc-topbar {
        flex-direction: column !important;
        align-items: stretch !important;
    }
    .acc-actions-wrap {
        width: 100% !important;
        display: grid !important;
        grid-template-columns: 1fr 1fr !important;
        gap: 0.5rem !important;
    }
    .acc-btn {
        width: 100% !important;
        justify-content: center !important;
        padding: 0.6rem !important;
    }
    .acc-search-input {
        width: 100% !important;
    }
}
</style>

<div class="accounting-wrapper">

    <!-- ════════════════════════════════════════════════════════════════
         1. TOP CONTEXT BAR & ACTIONS
         ════════════════════════════════════════════════════════════════ -->
    <div class="acc-topbar no-print">
        <a href="{{ route('host/event/management/' . $event->id) }}" class="acc-back-link">
            <span>←</span> Back to Event Management
        </a>
        <div class="acc-actions-wrap">
            <a href="{{ route('host/event/accounting/' . $event->id . '/export') }}" class="acc-btn acc-btn--primary">
                <span>📥</span> Export CSV / Excel
            </a>
            <button type="button" onclick="window.print()" class="acc-btn acc-btn--outline">
                <span>🖨️</span> Print / Save PDF
            </button>
        </div>
    </div>

    <!-- Admin Audit Mode Banner -->
    <?php if (!empty($isAdmin) && !empty($organizerName)): ?>
        <div class="no-print" style="display:flex; align-items:center; gap:0.65rem; background:rgba(99,102,241,0.09); border:1px solid rgba(99,102,241,0.25); border-radius:14px; padding:0.75rem 1.25rem; margin-bottom:1.25rem;">
            <span style="font-size:1.25rem;">🛡️</span>
            <div style="font-size:0.86rem; line-height:1.45;">
                <strong style="color:#6366f1;">Admin Audit Inspector:</strong>
                <span> Viewing event accounting for organizer <strong><?= e($organizerName) ?></strong> (<?= e($organizerEmail) ?>)</span>
            </div>
        </div>
    <?php endif; ?>

    <!-- ════════════════════════════════════════════════════════════════
         2. HERO HEADER
         ════════════════════════════════════════════════════════════════ -->
    <section class="acc-hero-card">
        <div style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:1.25rem;">
            <div>
                <span class="acc-hero-badge">
                    <span>📊</span> Official Event Settlement &amp; Accounting Ledger
                </span>
                <h1 class="acc-hero-title"><?= e($event->name) ?></h1>
                <div class="acc-hero-meta">
                    <span>🗓️ <?= e($formattedEventDate) ?></span>
                    <span>📍 <?= e($event->location ?: 'Venue TBA') ?></span>
                    <span>🏷️ <?= e($event->category ?: 'General') ?></span>
                    <span>🕒 Ledger Audit: <?= date('M d, Y • g:i A') ?></span>
                </div>
            </div>
            <div class="print-only-brand" style="display:none; text-align:right;">
                <strong style="font-size:1.3rem; color:var(--vn-forest, #0B7366);">Eventlyy</strong>
                <div style="font-size:0.8rem; color:#64748b;">Official Financial Settlement</div>
            </div>
        </div>
    </section>

    <!-- ════════════════════════════════════════════════════════════════
         3. PLAIN-ENGLISH SUMMARY STRIP (FOR NON-ACCOUNTANTS)
         ════════════════════════════════════════════════════════════════ -->
    <section class="plain-english-strip no-print">
        <div class="plain-strip-header">
            <span class="plain-strip-title">
                <span>💡</span> Plain-English Executive Summary (Easy to Understand)
            </span>
            <span style="font-size:0.78rem; color:#065f46; font-weight:600;">
                All amounts verified against platform payment gateway records
            </span>
        </div>
        <div class="plain-strip-grid">
            <div class="plain-strip-item">
                <span class="plain-strip-label">Ticket Buyers Paid Online</span>
                <div class="plain-strip-value" style="color:#0B7366;"><?= $currency ?><?= number_format($onlineGross, 2) ?></div>
                <div class="plain-strip-sub"><?= count($onlineTickets) ?> online tickets verified</div>
            </div>
            <div class="plain-strip-item">
                <span class="plain-strip-label">Your Net Ticket Share</span>
                <div class="plain-strip-value" style="color:#10b981;"><?= $currency ?><?= number_format($netHostEarnings, 2) ?></div>
                <div class="plain-strip-sub"><?= $totalPlatformFee > 0 ? "After {$platformFeePercent}% fee ({$currency}" . number_format($totalPlatformFee, 2) . ")" : "100% face value (buyer covered fee)" ?></div>
            </div>
            <div class="plain-strip-item">
                <span class="plain-strip-label">Online Merch &amp; Add-Ons</span>
                <div class="plain-strip-value" style="color:var(--vn-brass, #b8860b);"><?= $currency ?><?= number_format($merchTotalSales, 2) ?></div>
                <div class="plain-strip-sub"><?= $merchTotalQty ?> item<?= $merchTotalQty !== 1 ? 's' : '' ?> sold (<?= $merchFulfilledQty ?> collected)</div>
            </div>
            <div class="plain-strip-item">
                <span class="plain-strip-label">Disbursed to Your Bank</span>
                <div class="plain-strip-value" style="color:#0284c7;"><?= $currency ?><?= number_format($totalDisbursedPayouts, 2) ?></div>
                <div class="plain-strip-sub">Remaining: <strong><?= $currency ?><?= number_format($remainingHostBalance, 2) ?></strong></div>
            </div>
        </div>
    </section>

    <!-- ════════════════════════════════════════════════════════════════
         4. PRIMARY KPI METRIC CARDS
         ════════════════════════════════════════════════════════════════ -->
    <div class="acc-kpi-grid">
        <!-- 1. Online Ticket Sales -->
        <article class="acc-kpi-card" style="border-left: 4.5px solid #10b981 !important;">
            <div>
                <span class="acc-kpi-label">Online Ticket Sales (Face Value)</span>
                <h3 class="acc-kpi-value" style="color:#10b981;"><?= $currency ?><?= number_format($onlineGross, 2) ?></h3>
            </div>
            <p class="acc-kpi-sub">
                <?= count($onlineTickets) ?> Online Tickets Sold · Gate Comps: <?= $currency ?><?= number_format($gateCashGross + $compNominal, 2) ?>
            </p>
        </article>

        <!-- 2. Host Net Earnings (Ticket Entitlement) -->
        <article class="acc-kpi-card" style="border-left: 4.5px solid var(--vn-forest, #0B7366) !important;">
            <div>
                <span class="acc-kpi-label">Host Total Net Share</span>
                <h3 class="acc-kpi-value" style="color:var(--vn-forest, #0B7366);"><?= $currency ?><?= number_format($netHostEarnings, 2) ?></h3>
            </div>
            <p class="acc-kpi-sub">
                Tickets: <?= $currency ?><?= number_format($netTicketEarnings ?? $netHostEarnings, 2) ?><?= ($activityNet ?? 0) > 0 ? " · Activities: +{$currency}" . number_format($activityNet, 2) : '' ?>
            </p>
        </article>

        <!-- 3. Online Merchandise Sales -->
        <article class="acc-kpi-card" style="border-left: 4.5px solid var(--vn-brass, #b8860b) !important;">
            <div>
                <span class="acc-kpi-label">Online Merch &amp; Add-Ons</span>
                <h3 class="acc-kpi-value" style="color:var(--vn-brass, #b8860b);"><?= $currency ?><?= number_format($merchTotalSales, 2) ?></h3>
            </div>
            <p class="acc-kpi-sub">
                <?= $merchTotalQty ?> item<?= $merchTotalQty !== 1 ? 's' : '' ?> sold (<?= $merchPendingQty ?> pending pickup)
            </p>
        </article>

        <!-- 4. Payout Settlement Status -->
        <article class="acc-kpi-card" style="border-left: 4.5px solid #0284c7 !important;">
            <div>
                <span class="acc-kpi-label">Payout Status &amp; Disbursed</span>
                <h3 class="acc-kpi-value" style="color:#0284c7;"><?= $currency ?><?= number_format($totalDisbursedPayouts, 2) ?></h3>
            </div>
            <p class="acc-kpi-sub">
                <?php if ($pendingPayoutAmount > 0): ?>
                    <span style="color:#d97706; font-weight:700;">⏳ <?= $currency ?><?= number_format($pendingPayoutAmount, 2) ?> queued in review</span>
                <?php elseif ($remainingHostBalance > 0): ?>
                    <span style="color:#10b981; font-weight:700;">⚡ <?= $currency ?><?= number_format($remainingHostBalance, 2) ?> unrequested balance</span>
                <?php else: ?>
                    <span style="color:#0284c7; font-weight:700;">✓ 100% settled to bank</span>
                <?php endif; ?>
            </p>
        </article>
    </div>

    <!-- ════════════════════════════════════════════════════════════════
         5. INTERACTIVE TAB NAVIGATION
         ════════════════════════════════════════════════════════════════ -->
    <div class="acc-tabs-bar no-print">
        <button type="button" class="acc-tab-btn active" data-tab="tab-online">
            <span>💳</span> Online Tickets Purchased (<?= count($onlineTickets) ?>)
        </button>
        <button type="button" class="acc-tab-btn" data-tab="tab-merch">
            <span>🛍️</span> Online Merch &amp; Add-Ons (<?= count($merchOrders) ?>)
        </button>
        <button type="button" class="acc-tab-btn" data-tab="tab-issued">
            <span>🎟️</span> Direct Gate &amp; Comp Passes (<?= count($issuedTickets) ?>)
        </button>
        <button type="button" class="acc-tab-btn" data-tab="tab-reconciliation">
            <span>⚖️</span> Executive Accounting Reconciliation
        </button>
    </div>

    <!-- ════════════════════════════════════════════════════════════════
         TAB 1: PURCHASED ONLINE TICKETS (ITEMIZED & TOTAL)
         ════════════════════════════════════════════════════════════════ -->
    <div class="acc-tab-panel" id="tab-online">
        <section class="acc-card">
            <div class="acc-card-header">
                <div>
                    <h3 class="acc-card-title">💳 Itemized Online Ticket Purchases</h3>
                    <p class="acc-card-sub">
                        Every individual ticket purchased online through Paystack gateway for this event (Total: <?= count($onlineTickets) ?> tickets)
                    </p>
                </div>
                <div class="no-print">
                    <input type="text" id="search-online-tickets" class="acc-search-input" placeholder="🔍 Search attendee, email, tier, or ref...">
                </div>
            </div>

            <!-- Online Tickets Subtotal Banner -->
            <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:0.75rem; background:rgba(16, 185, 129, 0.08); border:1px solid rgba(16, 185, 129, 0.22); border-radius:12px; padding:0.85rem 1.25rem; margin-bottom:1.25rem;">
                <div>
                    <span style="font-size:0.76rem; font-weight:800; text-transform:uppercase; letter-spacing:0.04em; color:#065f46;">Total Online Tickets Verified</span>
                    <div style="font-size:1.15rem; font-weight:800; color:#065f46;"><?= count($onlineTickets) ?> Attendee Tickets</div>
                </div>
                <div style="text-align:right;">
                    <span style="font-size:0.76rem; font-weight:800; text-transform:uppercase; letter-spacing:0.04em; color:#065f46;">Total Ticket Sales Value</span>
                    <div style="font-size:1.35rem; font-weight:900; color:#10b981; font-family:var(--vn-font-serif, sans-serif);"><?= $currency ?><?= number_format($onlineGross, 2) ?></div>
                </div>
            </div>

            <?php if (empty($onlineTickets)): ?>
                <div style="text-align:center; padding:3.5rem 1rem; color:var(--vn-ink-muted, #64748b);">
                    <div style="font-size:2.8rem; margin-bottom:0.5rem;">💳</div>
                    <strong style="font-size:1.05rem;">No online ticket purchases recorded yet.</strong>
                    <p style="font-size:0.85rem; margin-top:0.25rem;">When attendees purchase tickets online, they will appear here with verification references.</p>
                </div>
            <?php else: ?>
                <!-- Desktop Table View -->
                <div class="acc-table-wrap">
                    <table class="acc-table" id="table-online-tickets">
                        <thead>
                            <tr>
                                <th>Ticket Code</th>
                                <th>Attendee</th>
                                <th>Tier</th>
                                <th>Payment Ref</th>
                                <th>Gateway Status</th>
                                <th style="text-align:right;">Sold Price (<?= e($currency) ?>)</th>
                                <th style="text-align:center;">Gate Check-In</th>
                                <th>Purchase Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($onlineTickets as $ot): ?>
                                <tr>
                                    <td style="font-family:monospace; font-weight:800; color:var(--vn-forest, #0B7366);">
                                        #<?= e($ot['id']) ?>
                                    </td>
                                    <td>
                                        <strong><?= e($ot['attendee_name'] ?: 'Guest Attendee') ?></strong>
                                        <div style="font-size:0.76rem; color:var(--vn-ink-muted, #64748b);"><?= e($ot['attendee_email']) ?></div>
                                    </td>
                                    <td>
                                        <span style="display:inline-block; padding:0.2rem 0.55rem; border-radius:6px; background:rgba(var(--muted),0.12); font-weight:700; font-size:0.76rem;">
                                            <?= e($ot['ticket_tier'] ?? 'General') ?>
                                        </span>
                                    </td>
                                    <td style="font-family:monospace; font-size:0.78rem; color:#6366f1;">
                                        <?= e($ot['extracted_ref'] ?? 'N/A') ?>
                                    </td>
                                    <td>
                                        <span style="display:inline-flex; align-items:center; gap:0.3rem; color:#10b981; font-weight:700; font-size:0.78rem;">
                                            <span>●</span> <?= e($ot['gateway_status'] ?? 'Paid') ?>
                                        </span>
                                    </td>
                                    <td style="text-align:right; font-weight:800; font-size:0.92rem; color:var(--vn-ink, #0f172a);">
                                        <?= number_format((float)$ot['price'], 2) ?>
                                    </td>
                                    <td style="text-align:center;">
                                        <?php if (!empty($ot['checked_in'])): ?>
                                            <span style="color:#10b981; font-weight:800; font-size:0.78rem; background:rgba(16,185,129,0.12); padding:0.2rem 0.55rem; border-radius:6px;">✓ Checked In</span>
                                        <?php else: ?>
                                            <span style="color:var(--vn-ink-muted, #64748b); font-size:0.78rem;">Awaiting Entry</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="font-size:0.78rem; color:var(--vn-ink-muted, #64748b); white-space:nowrap;">
                                        <?= !empty($ot['purchase_date']) ? date('M d, Y • H:i', strtotime($ot['purchase_date'])) : 'N/A' ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="5" style="text-transform:uppercase; letter-spacing:0.04em;">
                                    TOTAL ONLINE TICKET SALES (<?= count($onlineTickets) ?> Purchases)
                                </td>
                                <td style="text-align:right; font-size:1.1rem; color:#10b981;">
                                    <?= e($currency) ?><?= number_format($onlineGross, 2) ?>
                                </td>
                                <td style="text-align:center; font-size:0.8rem; color:var(--vn-ink-muted, #64748b);">
                                    <?= count(array_filter($onlineTickets, fn($x) => !empty($x['checked_in']))) ?> verified at door
                                </td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <!-- Mobile Card Stream (Displays on <= 768px) -->
                <div class="acc-mobile-stream" id="cards-online-tickets">
                    <?php foreach ($onlineTickets as $ot): ?>
                        <div class="acc-item-card" data-search="<?= strtolower(e($ot['id'] . ' ' . ($ot['attendee_name'] ?? '') . ' ' . ($ot['attendee_email'] ?? '') . ' ' . ($ot['extracted_ref'] ?? '') . ' ' . ($ot['ticket_tier'] ?? ''))) ?>">
                            <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:0.5rem;">
                                <div>
                                    <span style="font-family:monospace; font-weight:800; color:var(--vn-forest, #0B7366); font-size:0.84rem;">Ticket #<?= e($ot['id']) ?></span>
                                    <h4 style="margin:0.2rem 0 0; font-size:0.96rem; font-weight:800;"><?= e($ot['attendee_name'] ?: 'Guest Attendee') ?></h4>
                                    <div style="font-size:0.76rem; color:var(--vn-ink-muted, #64748b); word-break:break-all;"><?= e($ot['attendee_email']) ?></div>
                                </div>
                                <div style="text-align:right; flex-shrink:0;">
                                    <div style="font-weight:900; font-size:1.05rem; color:#10b981; font-family:var(--vn-font-serif, sans-serif);"><?= e($currency) ?><?= number_format((float)$ot['price'], 2) ?></div>
                                    <span style="display:inline-block; padding:0.2rem 0.5rem; border-radius:6px; background:rgba(var(--muted),0.12); font-weight:700; font-size:0.72rem; margin-top:0.25rem;">
                                        <?= e($ot['ticket_tier'] ?? 'General') ?>
                                    </span>
                                </div>
                            </div>
                            <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:0.4rem; font-size:0.78rem; border-top:1px dashed var(--vn-border, rgba(var(--muted),0.16)); padding-top:0.5rem; margin-top:0.25rem;">
                                <div>
                                    <?php if (!empty($ot['checked_in'])): ?>
                                        <span style="color:#10b981; font-weight:800;">✓ Checked In</span>
                                    <?php else: ?>
                                        <span style="color:var(--vn-ink-muted, #64748b);">Awaiting Entry</span>
                                    <?php endif; ?>
                                    <span style="margin:0 0.35rem; color:rgba(var(--muted),0.4);">•</span>
                                    <span style="color:#10b981; font-weight:700;">● <?= e($ot['gateway_status'] ?? 'Paid') ?></span>
                                </div>
                                <div style="color:var(--vn-ink-muted, #64748b); font-size:0.74rem;">
                                    <?= !empty($ot['purchase_date']) ? date('M d, Y • H:i', strtotime($ot['purchase_date'])) : 'N/A' ?>
                                </div>
                            </div>
                            <?php if (!empty($ot['extracted_ref'])): ?>
                                <div style="font-size:0.72rem; color:#6366f1; font-family:monospace; word-break:break-all;">
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
         TAB 2: ONLINE MERCHANDISE & ADD-ON SALES (ITEMIZED & TOTAL)
         ════════════════════════════════════════════════════════════════ -->
    <div class="acc-tab-panel" id="tab-merch" style="display:none;">
        <section class="acc-card">
            <div class="acc-card-header">
                <div>
                    <h3 class="acc-card-title">🛍️ Online Merchandise &amp; Add-On Sales</h3>
                    <p class="acc-card-sub">
                        Attendee orders for official event merchandise, VIP passes, and pre-purchased party add-ons (Total: <?= count($merchOrders) ?> orders)
                    </p>
                </div>
                <div class="no-print">
                    <input type="text" id="search-merch-orders" class="acc-search-input" placeholder="🔍 Search buyer, item name, or status...">
                </div>
            </div>

            <!-- Merch Subtotal Summary Grid -->
            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(180px, 1fr)); gap:0.75rem; background:rgba(184, 134, 11, 0.08); border:1px solid rgba(184, 134, 11, 0.22); border-radius:12px; padding:1rem 1.25rem; margin-bottom:1.5rem;">
                <div>
                    <span style="font-size:0.74rem; font-weight:800; text-transform:uppercase; letter-spacing:0.04em; color:var(--vn-brass, #b8860b);">Total Merch Revenue</span>
                    <div style="font-size:1.35rem; font-weight:900; color:var(--vn-brass, #b8860b); font-family:var(--vn-font-serif, sans-serif);"><?= $currency ?><?= number_format($merchTotalSales, 2) ?></div>
                </div>
                <div>
                    <span style="font-size:0.74rem; font-weight:800; text-transform:uppercase; letter-spacing:0.04em; color:var(--vn-ink-muted, #64748b);">Total Items Sold</span>
                    <div style="font-size:1.15rem; font-weight:800; color:var(--vn-ink, #0f172a);"><?= $merchTotalQty ?> Unit<?= $merchTotalQty !== 1 ? 's' : '' ?></div>
                </div>
                <div>
                    <span style="font-size:0.74rem; font-weight:800; text-transform:uppercase; letter-spacing:0.04em; color:#10b981;">Collected / Fulfilled</span>
                    <div style="font-size:1.15rem; font-weight:800; color:#10b981;"><?= $merchFulfilledQty ?> Unit<?= $merchFulfilledQty !== 1 ? 's' : '' ?></div>
                </div>
                <div>
                    <span style="font-size:0.74rem; font-weight:800; text-transform:uppercase; letter-spacing:0.04em; color:#d97706;">Pending Pickup</span>
                    <div style="font-size:1.15rem; font-weight:800; color:#d97706;"><?= $merchPendingQty ?> Unit<?= $merchPendingQty !== 1 ? 's' : '' ?></div>
                </div>
            </div>

            <?php if (empty($merchOrders)): ?>
                <div style="text-align:center; padding:3.5rem 1rem; color:var(--vn-ink-muted, #64748b);">
                    <div style="font-size:2.8rem; margin-bottom:0.5rem;">🛍️</div>
                    <strong style="font-size:1.05rem;">No merchandise or add-on sales recorded for this event.</strong>
                    <p style="font-size:0.85rem; margin-top:0.25rem;">When attendees add merchandise, t-shirts, caps, or VIP perks to their cart, orders will appear here.</p>
                </div>
            <?php else: ?>
                <!-- Desktop Table View -->
                <div class="acc-table-wrap">
                    <table class="acc-table" id="table-merch-orders">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Item Purchased</th>
                                <th>Buyer Details</th>
                                <th style="text-align:right;">Unit Price (<?= e($currency) ?>)</th>
                                <th style="text-align:center;">Qty</th>
                                <th style="text-align:right;">Total Paid (<?= e($currency) ?>)</th>
                                <th style="text-align:center;">Fulfillment Status</th>
                                <th>Order Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($merchOrders as $mo): ?>
                                <tr>
                                    <td style="font-family:monospace; font-weight:800; color:var(--vn-brass, #b8860b);">
                                        #MR-<?= e($mo['id']) ?>
                                    </td>
                                    <td>
                                        <div style="display:flex; align-items:center; gap:0.65rem;">
                                            <?php if (!empty($mo['addon_image'])): ?>
                                                <img src="<?= e($mo['addon_image']) ?>" alt="merch" style="width:36px; height:36px; border-radius:8px; object-fit:cover; border:1px solid var(--vn-border, #e2e8f0);">
                                            <?php else: ?>
                                                <span style="font-size:1.4rem;">🛍️</span>
                                            <?php endif; ?>
                                            <div>
                                                <strong><?= e($mo['addon_name'] ?: 'Merchandise Item') ?></strong>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <strong><?= e($mo['buyer_name'] ?: 'Guest Buyer') ?></strong>
                                        <div style="font-size:0.76rem; color:var(--vn-ink-muted, #64748b);"><?= e($mo['buyer_email']) ?></div>
                                    </td>
                                    <td style="text-align:right; font-weight:700;">
                                        <?= number_format((float)($mo['unit_price'] ?? 0), 2) ?>
                                    </td>
                                    <td style="text-align:center; font-weight:800; font-size:0.95rem;">
                                        <?= (int)($mo['quantity'] ?? 1) ?>
                                    </td>
                                    <td style="text-align:right; font-weight:900; color:var(--vn-brass, #b8860b); font-size:0.95rem;">
                                        <?= number_format((float)($mo['total_price'] ?? 0), 2) ?>
                                    </td>
                                    <td style="text-align:center;">
                                        <?php if (($mo['redemption_status'] ?? '') === 'fulfilled'): ?>
                                            <span style="color:#10b981; font-weight:800; font-size:0.78rem; background:rgba(16,185,129,0.12); padding:0.2rem 0.55rem; border-radius:6px;">✓ Fulfilled</span>
                                        <?php elseif (($mo['redemption_status'] ?? '') === 'cancelled'): ?>
                                            <span style="color:#ef4444; font-weight:800; font-size:0.78rem; background:rgba(239,68,68,0.12); padding:0.2rem 0.55rem; border-radius:6px;">Cancelled</span>
                                        <?php else: ?>
                                            <span style="color:#d97706; font-weight:800; font-size:0.78rem; background:rgba(217,119,6,0.12); padding:0.2rem 0.55rem; border-radius:6px;">⏳ Pending Pickup</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="font-size:0.78rem; color:var(--vn-ink-muted, #64748b); white-space:nowrap;">
                                        <?= !empty($mo['created_at']) ? date('M d, Y • H:i', strtotime($mo['created_at'])) : 'N/A' ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="4" style="text-transform:uppercase; letter-spacing:0.04em;">
                                    TOTAL ONLINE MERCHANDISE SALES (<?= count($merchOrders) ?> Orders)
                                </td>
                                <td style="text-align:center; font-size:1rem; color:var(--vn-ink, #0f172a);">
                                    <?= $merchTotalQty ?> Items
                                </td>
                                <td style="text-align:right; font-size:1.15rem; color:var(--vn-brass, #b8860b);">
                                    <?= e($currency) ?><?= number_format($merchTotalSales, 2) ?>
                                </td>
                                <td colspan="2" style="text-align:center; font-size:0.8rem; color:var(--vn-ink-muted, #64748b);">
                                    <?= $merchFulfilledQty ?> collected · <?= $merchPendingQty ?> pending pickup
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <!-- Mobile Card Stream -->
                <div class="acc-mobile-stream" id="cards-merch-orders">
                    <?php foreach ($merchOrders as $mo): ?>
                        <div class="acc-item-card" data-search="<?= strtolower(e($mo['id'] . ' ' . ($mo['addon_name'] ?? '') . ' ' . ($mo['buyer_name'] ?? '') . ' ' . ($mo['buyer_email'] ?? '') . ' ' . ($mo['redemption_status'] ?? ''))) ?>">
                            <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:0.5rem;">
                                <div style="display:flex; gap:0.65rem; align-items:center;">
                                    <?php if (!empty($mo['addon_image'])): ?>
                                        <img src="<?= e($mo['addon_image']) ?>" alt="merch" style="width:42px; height:42px; border-radius:10px; object-fit:cover; border:1px solid var(--vn-border, #e2e8f0);">
                                    <?php endif; ?>
                                    <div>
                                        <span style="font-family:monospace; font-weight:800; color:var(--vn-brass, #b8860b); font-size:0.82rem;">#MR-<?= e($mo['id']) ?></span>
                                        <h4 style="margin:0.2rem 0 0; font-size:0.96rem; font-weight:800;"><?= e($mo['addon_name'] ?: 'Merchandise Item') ?></h4>
                                        <div style="font-size:0.76rem; color:var(--vn-ink-muted, #64748b);"><?= e($mo['buyer_name'] ?: 'Guest') ?> (<?= e($mo['buyer_email']) ?>)</div>
                                    </div>
                                </div>
                                <div style="text-align:right; flex-shrink:0;">
                                    <div style="font-weight:900; font-size:1.05rem; color:var(--vn-brass, #b8860b); font-family:var(--vn-font-serif, sans-serif);"><?= e($currency) ?><?= number_format((float)($mo['total_price'] ?? 0), 2) ?></div>
                                    <div style="font-size:0.74rem; color:var(--vn-ink-muted, #64748b); font-weight:700;">Qty: <?= (int)($mo['quantity'] ?? 1) ?> × <?= e($currency) ?><?= number_format((float)($mo['unit_price'] ?? 0), 2) ?></div>
                                </div>
                            </div>
                            <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:0.4rem; font-size:0.78rem; border-top:1px dashed var(--vn-border, rgba(var(--muted),0.16)); padding-top:0.5rem; margin-top:0.25rem;">
                                <div>
                                    <?php if (($mo['redemption_status'] ?? '') === 'fulfilled'): ?>
                                        <span style="color:#10b981; font-weight:800;">✓ Collected / Fulfilled</span>
                                    <?php elseif (($mo['redemption_status'] ?? '') === 'cancelled'): ?>
                                        <span style="color:#ef4444; font-weight:800;">Cancelled</span>
                                    <?php else: ?>
                                        <span style="color:#d97706; font-weight:800;">⏳ Pending Pickup</span>
                                    <?php endif; ?>
                                </div>
                                <div style="color:var(--vn-ink-muted, #64748b); font-size:0.74rem;">
                                    <?= !empty($mo['created_at']) ? date('M d, Y • H:i', strtotime($mo['created_at'])) : 'N/A' ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </div>

    <!-- ════════════════════════════════════════════════════════════════
         TAB 3: DIRECT GATE & COMP PASSES
         ════════════════════════════════════════════════════════════════ -->
    <div class="acc-tab-panel" id="tab-issued" style="display:none;">
        <section class="acc-card">
            <div class="acc-card-header">
                <div>
                    <h3 class="acc-card-title">🎟️ Direct Gate &amp; Complimentary Passes</h3>
                    <p class="acc-card-sub">
                        Host complimentary VIP passes and on-site door gate box-office sales (Total: <?= count($issuedTickets) ?> passes)
                    </p>
                </div>
                <div class="no-print">
                    <input type="text" id="search-issued-tickets" class="acc-search-input" placeholder="🔍 Search recipient, type, or note...">
                </div>
            </div>

            <!-- Gate Cash Explanatory Callout -->
            <div style="display:flex; align-items:flex-start; gap:0.75rem; background:rgba(99, 102, 241, 0.08); border:1px solid rgba(99, 102, 241, 0.22); border-radius:12px; padding:0.95rem 1.25rem; margin-bottom:1.25rem;">
                <span style="font-size:1.35rem; line-height:1;">ℹ️</span>
                <div style="font-size:0.84rem; line-height:1.45; color:var(--vn-ink, #1e293b);">
                    <strong>Accounting Policy Note:</strong> Direct gate-issued tickets and on-site door sales are collected offline in physical cash directly by the event host. 
                    They are tracked here strictly for door scan verification and attendance auditing, and are <strong>excluded from platform online bank payouts</strong>.
                </div>
            </div>

            <?php if (empty($issuedTickets)): ?>
                <div style="text-align:center; padding:3.5rem 1rem; color:var(--vn-ink-muted, #64748b);">
                    <div style="font-size:2.8rem; margin-bottom:0.5rem;">🎟️</div>
                    <strong style="font-size:1.05rem;">No issued or complimentary passes recorded.</strong>
                    <p style="font-size:0.85rem; margin-top:0.25rem;">Tickets issued manually by the host or through the gate cash box office terminal will appear here.</p>
                </div>
            <?php else: ?>
                <!-- Desktop Table View -->
                <div class="acc-table-wrap">
                    <table class="acc-table" id="table-issued-tickets">
                        <thead>
                            <tr>
                                <th>Pass Code</th>
                                <th>Recipient / Guest</th>
                                <th>Tier</th>
                                <th>Issuance Type</th>
                                <th>Audit / Terminal Notes</th>
                                <th style="text-align:right;">Nominal Value (<?= e($currency) ?>)</th>
                                <th style="text-align:center;">Gate Turnout</th>
                                <th>Issue Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($issuedTickets as $it): ?>
                                <tr>
                                    <td style="font-family:monospace; font-weight:800; color:var(--vn-forest, #0B7366);">
                                        #<?= e($it['id']) ?>
                                    </td>
                                    <td>
                                        <strong><?= e($it['attendee_name'] ?: 'Guest Pass') ?></strong>
                                        <div style="font-size:0.76rem; color:var(--vn-ink-muted, #64748b);"><?= e($it['attendee_email']) ?></div>
                                    </td>
                                    <td>
                                        <span style="display:inline-block; padding:0.2rem 0.55rem; border-radius:6px; background:rgba(var(--muted),0.12); font-weight:700; font-size:0.76rem;">
                                            <?= e($it['ticket_tier'] ?? 'General') ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span style="font-weight:700; font-size:0.78rem; color:<?= str_contains($it['issuance_type'] ?? '', 'Gate') ? '#10b981' : '#6366f1' ?>;">
                                            <?= e($it['issuance_type'] ?? 'Host Direct') ?>
                                        </span>
                                    </td>
                                    <td style="font-size:0.78rem; color:var(--vn-ink-muted, #64748b);">
                                        <?= e($it['status_detail'] ?: 'Direct issuance by organizer') ?>
                                    </td>
                                    <td style="text-align:right; font-weight:800; font-size:0.92rem;">
                                        <?= number_format((float)$it['price'], 2) ?>
                                    </td>
                                    <td style="text-align:center;">
                                        <?php if (!empty($it['checked_in'])): ?>
                                            <span style="color:#10b981; font-weight:800; font-size:0.78rem; background:rgba(16,185,129,0.12); padding:0.2rem 0.55rem; border-radius:6px;">✓ Checked In</span>
                                        <?php else: ?>
                                            <span style="color:var(--vn-ink-muted, #64748b); font-size:0.78rem;">Awaiting Entry</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="font-size:0.78rem; color:var(--vn-ink-muted, #64748b); white-space:nowrap;">
                                        <?= !empty($it['purchase_date']) ? date('M d, Y • H:i', strtotime($it['purchase_date'])) : 'N/A' ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="5" style="text-transform:uppercase; letter-spacing:0.04em;">
                                    TOTAL DIRECT ISSUED PASSES (<?= count($issuedTickets) ?> Passes · Gate Cash Retained: <?= e($currency) ?><?= number_format($gateCashGross, 2) ?>)
                                </td>
                                <td style="text-align:right; font-size:1.1rem; color:#6366f1;">
                                    <?= e($currency) ?><?= number_format($gateCashGross + $compNominal, 2) ?>
                                </td>
                                <td style="text-align:center; font-size:0.8rem; color:var(--vn-ink-muted, #64748b);">
                                    <?= count(array_filter($issuedTickets, fn($x) => !empty($x['checked_in']))) ?> verified at door
                                </td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <!-- Mobile Card Stream -->
                <div class="acc-mobile-stream" id="cards-issued-tickets">
                    <?php foreach ($issuedTickets as $it): ?>
                        <div class="acc-item-card" data-search="<?= strtolower(e($it['id'] . ' ' . ($it['attendee_name'] ?? '') . ' ' . ($it['attendee_email'] ?? '') . ' ' . ($it['issuance_type'] ?? '') . ' ' . ($it['ticket_tier'] ?? ''))) ?>">
                            <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:0.5rem;">
                                <div>
                                    <span style="font-family:monospace; font-weight:800; color:var(--vn-forest, #0B7366); font-size:0.84rem;">Pass #<?= e($it['id']) ?></span>
                                    <h4 style="margin:0.2rem 0 0; font-size:0.96rem; font-weight:800;"><?= e($it['attendee_name'] ?: 'Guest Pass') ?></h4>
                                    <div style="font-size:0.76rem; color:var(--vn-ink-muted, #64748b);"><?= e($it['attendee_email']) ?></div>
                                </div>
                                <div style="text-align:right; flex-shrink:0;">
                                    <div style="font-weight:900; font-size:1.05rem; color:#6366f1; font-family:var(--vn-font-serif, sans-serif);"><?= e($currency) ?><?= number_format((float)$it['price'], 2) ?></div>
                                    <span style="display:inline-block; padding:0.2rem 0.5rem; border-radius:6px; background:rgba(var(--muted),0.12); font-weight:700; font-size:0.72rem; margin-top:0.25rem;">
                                        <?= e($it['ticket_tier'] ?? 'General') ?>
                                    </span>
                                </div>
                            </div>
                            <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:0.4rem; font-size:0.78rem; border-top:1px dashed var(--vn-border, rgba(var(--muted),0.16)); padding-top:0.5rem; margin-top:0.25rem;">
                                <div>
                                    <?php if (!empty($it['checked_in'])): ?>
                                        <span style="color:#10b981; font-weight:800;">✓ Checked In</span>
                                    <?php else: ?>
                                        <span style="color:var(--vn-ink-muted, #64748b);">Awaiting Entry</span>
                                    <?php endif; ?>
                                    <span style="margin:0 0.35rem; color:rgba(var(--muted),0.4);">•</span>
                                    <span style="font-weight:700; color:<?= str_contains($it['issuance_type'] ?? '', 'Gate') ? '#10b981' : '#6366f1' ?>;">
                                        <?= e($it['issuance_type'] ?? 'Host Direct') ?>
                                    </span>
                                </div>
                                <div style="color:var(--vn-ink-muted, #64748b); font-size:0.74rem;">
                                    <?= !empty($it['purchase_date']) ? date('M d, Y • H:i', strtotime($it['purchase_date'])) : 'N/A' ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </div>

    <!-- ════════════════════════════════════════════════════════════════
         TAB 4: EXECUTIVE FINANCIAL RECONCILIATION & BALANCE LEDGER
         ════════════════════════════════════════════════════════════════ -->
    <div class="acc-tab-panel" id="tab-reconciliation" style="display:none;">
        <section class="acc-card" style="max-width:960px; margin-left:auto; margin-right:auto;">
            <div style="border-bottom:2px solid var(--vn-border, rgba(var(--muted), 0.16)); padding-bottom:1.25rem; margin-bottom:1.75rem; text-align:center;">
                <h2 style="margin:0 0 0.4rem; font-size:1.35rem; font-family:var(--vn-font-serif, serif); font-weight:normal; text-transform:uppercase; letter-spacing:0.04em; color:var(--vn-forest, #0B7366);">
                    Executive Financial Reconciliation Statement
                </h2>
                <p style="margin:0; font-size:0.86rem; color:var(--vn-ink-muted, rgb(var(--muted)));">
                    Complete accounting audit reconciling ticket receipts, merchandise sales, platform charges, and net bank settlements.
                </p>
            </div>

            <!-- SECTION 1: EVENT PROFILE & TICKETING CONFIG -->
            <div style="margin-bottom:1.75rem;">
                <h4 style="margin:0 0 0.65rem; font-size:0.84rem; font-weight:800; text-transform:uppercase; letter-spacing:0.05em; color:var(--vn-ink-muted, rgb(var(--muted)));">
                    1. Event Profile &amp; Ticketing Configuration
                </h4>
                <div style="border:1px solid var(--vn-border, rgba(var(--muted), 0.16)); border-radius:12px; overflow:hidden;">
                    <div class="reconcile-row">
                        <span style="color:var(--vn-ink-muted, #64748b);">Event Title</span>
                        <strong><?= e($event->name) ?></strong>
                    </div>
                    <div class="reconcile-row">
                        <span style="color:var(--vn-ink-muted, #64748b);">Scheduled Date &amp; Time</span>
                        <span><?= e($formattedEventDate) ?></span>
                    </div>
                    <div class="reconcile-row">
                        <span style="color:var(--vn-ink-muted, #64748b);">Venue &amp; Location</span>
                        <span><?= e($event->location ?: 'Venue TBA') ?></span>
                    </div>
                    <div class="reconcile-row">
                        <span style="color:var(--vn-ink-muted, #64748b);">Platform Fee Policy</span>
                        <span><?= !empty($event->pass_fee) ? 'Passed to ticket buyers (100% face value to host)' : 'Absorbed by host (5% deducted from ticket face value)' ?></span>
                    </div>
                </div>
            </div>

            <!-- SECTION 2: ATTENDANCE & INVENTORY AUDIT -->
            <div style="margin-bottom:1.75rem;">
                <h4 style="margin:0 0 0.65rem; font-size:0.84rem; font-weight:800; text-transform:uppercase; letter-spacing:0.05em; color:var(--vn-ink-muted, rgb(var(--muted)));">
                    2. Ticket Inventory &amp; Door Attendance Audit
                </h4>
                <div style="border:1px solid var(--vn-border, rgba(var(--muted), 0.16)); border-radius:12px; overflow:hidden;">
                    <div class="reconcile-row">
                        <span style="color:var(--vn-ink-muted, #64748b);">Total Venue Capacity</span>
                        <strong><?= number_format($totalCapacity) ?> tickets</strong>
                    </div>
                    <div class="reconcile-row">
                        <span style="color:var(--vn-ink-muted, #64748b);">Online Paystack Purchases</span>
                        <strong><?= number_format(count($onlineTickets)) ?> tickets</strong>
                    </div>
                    <div class="reconcile-row">
                        <span style="color:var(--vn-ink-muted, #64748b);">Host Gate &amp; Comp Passes</span>
                        <strong><?= number_format(count($issuedTickets)) ?> tickets</strong>
                    </div>
                    <div class="reconcile-row">
                        <span style="color:var(--vn-ink-muted, #64748b);">Total Attendance Roster</span>
                        <strong style="color:var(--vn-forest, #0B7366);"><?= number_format($totalTicketsSoldOrIssued) ?> attendees (<?= $occupancyRate ?>% capacity taken)</strong>
                    </div>
                    <div class="reconcile-row">
                        <span style="color:var(--vn-ink-muted, #64748b);">Gate Scanner Turnout</span>
                        <strong style="color:#10b981;"><?= number_format($totalCheckedIn) ?> verified attendees (<?= $turnoutRate ?>% verified entrance)</strong>
                    </div>
                </div>
            </div>

            <!-- SECTION 3: FORMAL RECONCILIATION LEDGER -->
            <div style="margin-bottom:1.75rem;">
                <h4 style="margin:0 0 0.65rem; font-size:0.84rem; font-weight:800; text-transform:uppercase; letter-spacing:0.05em; color:var(--vn-ink-muted, rgb(var(--muted)));">
                    3. Platform Financial Reconciliation Ledger
                </h4>
                <div style="border:1px solid var(--vn-border, rgba(var(--muted), 0.16)); border-radius:12px; overflow:hidden;">
                    <!-- Line 1: Online Ticket Sales -->
                    <div class="reconcile-row">
                        <div>
                            <strong>Online Ticket Sales (Face Value)</strong>
                            <div style="font-size:0.75rem; color:var(--vn-ink-muted, #64748b);"><?= count($onlineTickets) ?> online attendees processed</div>
                        </div>
                        <strong style="font-size:1rem; color:#10b981;"><?= e($currency) ?><?= number_format($onlineGross, 2) ?></strong>
                    </div>

                    <!-- Line 2: Platform Fee -->
                    <?php if ($totalPlatformFee > 0): ?>
                        <div class="reconcile-row" style="color:#ef4444;">
                            <div>
                                <span>Less: Platform Fee (5% absorbed by host)</span>
                                <div style="font-size:0.75rem; color:var(--vn-ink-muted, #64748b);">Deducted from gross ticket price</div>
                            </div>
                            <strong>− <?= e($currency) ?><?= number_format($totalPlatformFee, 2) ?></strong>
                        </div>
                    <?php else: ?>
                        <div class="reconcile-row" style="color:var(--vn-ink-muted, #64748b);">
                            <div>
                                <span>Less: Platform Service Fee</span>
                                <div style="font-size:0.75rem;">Fee covered 100% by ticket buyers at checkout</div>
                            </div>
                            <span><?= e($currency) ?>0.00 (Buyer Paid)</span>
                        </div>
                    <?php endif; ?>

                    <!-- Line 3: Net Host Ticket Entitlement -->
                    <div class="reconcile-row" style="background:rgba(var(--muted), 0.04);">
                        <div>
                            <strong style="color:var(--vn-forest, #0B7366);">Host Net Ticket Entitlement (Base Payout Share)</strong>
                            <div style="font-size:0.75rem; color:var(--vn-ink-muted, #64748b);">Eligible ticket funds for host bank disbursement</div>
                        </div>
                        <strong style="font-size:1.1rem; color:var(--vn-forest, #0B7366); font-family:var(--vn-font-serif, sans-serif);">
                            <?= e($currency) ?><?= number_format($netTicketEarnings ?? $netHostEarnings, 2) ?>
                        </strong>
                    </div>

                    <!-- Line 4: Online Merchandise & Add-Ons -->
                    <div class="reconcile-row">
                        <div>
                            <strong>Plus: Online Merchandise &amp; Add-On Sales</strong>
                            <div style="font-size:0.75rem; color:var(--vn-ink-muted, #64748b);"><?= $merchTotalQty ?> item<?= $merchTotalQty !== 1 ? 's' : '' ?> sold (<?= $merchFulfilledQty ?> collected, <?= $merchPendingQty ?> pending)</div>
                        </div>
                        <strong style="font-size:1rem; color:var(--vn-brass, #b8860b);"><?= e($currency) ?><?= number_format($merchTotalSales, 2) ?></strong>
                    </div>

                    <!-- Line 4.5: Power Voting & Event Activities -->
                    <?php if (($activityGross ?? 0) > 0): ?>
                        <div class="reconcile-row" style="color:#8b5cf6;">
                            <div>
                                <strong>Plus: Power Voting Poll Activities (Host Share)</strong>
                                <div style="font-size:0.75rem; color:var(--vn-ink-muted, #64748b);"><?= $activityOrdersCount ?> purchase orders · Gross <?= e($currency) ?><?= number_format($activityGross, 2) ?> − Platform Fee <?= e($currency) ?><?= number_format($activityFee, 2) ?></div>
                            </div>
                            <strong style="font-size:1rem; color:#8b5cf6;">+ <?= e($currency) ?><?= number_format($activityNet, 2) ?></strong>
                        </div>
                    <?php endif; ?>

                    <!-- Line 5: Prior Disbursed -->
                    <div class="reconcile-row" style="color:#0284c7;">
                        <div>
                            <span>Less: Prior Completed Bank Disbursements</span>
                            <div style="font-size:0.75rem; color:var(--vn-ink-muted, #64748b);">Funds already wired to host's bank account</div>
                        </div>
                        <strong>− <?= e($currency) ?><?= number_format($totalDisbursedPayouts, 2) ?></strong>
                    </div>

                    <!-- Line 6: Pending Payout Claims -->
                    <?php if ($pendingPayoutAmount > 0): ?>
                        <div class="reconcile-row" style="color:#d97706;">
                            <div>
                                <span>Less: Pending Withdrawal Claims Queued</span>
                                <div style="font-size:0.75rem; color:var(--vn-ink-muted, #64748b);">Currently undergoing admin approval</div>
                            </div>
                            <strong>− <?= e($currency) ?><?= number_format($pendingPayoutAmount, 2) ?></strong>
                        </div>
                    <?php endif; ?>

                    <!-- FINAL BALANCE ROW -->
                    <div class="reconcile-final-row">
                        <div>
                            <div style="font-size:0.95rem; font-weight:800; color:var(--vn-forest, #0B7366);">REMAINING / UNREQUESTED HOST BALANCE</div>
                            <div style="font-size:0.76rem; color:var(--vn-ink-muted, #64748b);">Available for immediate withdrawal or held in escrow custody</div>
                        </div>
                        <div style="font-size:1.6rem; font-weight:900; color:var(--vn-forest, #0B7366); font-family:var(--vn-font-serif, sans-serif);">
                            <?= e($currency) ?><?= number_format($remainingHostBalance, 2) ?>
                        </div>
                    </div>

                    <!-- OFFLINE DIRECT PASSES AUDIT (EXCLUDED FROM PLATFORM DISBURSEMENT) -->
                    <div style="padding:1rem 1.15rem; background:rgba(var(--muted), 0.04); border-top:1px dashed var(--vn-border, rgba(var(--muted), 0.16)); font-size:0.82rem;">
                        <span style="font-weight:800; color:var(--vn-ink-muted, #64748b); text-transform:uppercase; font-size:0.74rem; letter-spacing:0.04em; display:block; margin-bottom:0.45rem;">
                            Direct Host / Door Passes (Retained Directly by Host — Excluded from Bank Payout)
                        </span>
                        <div style="display:flex; justify-content:space-between; margin-bottom:0.35rem;">
                            <span style="color:var(--vn-ink-muted, #64748b);">On-Site Gate Cash Door Sales (Host Pocket):</span>
                            <strong><?= e($currency) ?><?= number_format($gateCashGross, 2) ?></strong>
                        </div>
                        <div style="display:flex; justify-content:space-between;">
                            <span style="color:var(--vn-ink-muted, #64748b);">Complimentary / Free VIP Passes Nominal Valuation:</span>
                            <span><?= e($currency) ?><?= number_format($compNominal, 2) ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sign-Off Audit Footer -->
            <div style="margin-top:2rem; padding-top:1.25rem; border-top:1px dashed var(--vn-border, rgba(var(--muted), 0.2)); display:flex; justify-content:space-between; font-size:0.78rem; color:var(--vn-ink-muted, #64748b); flex-wrap:wrap; gap:1rem;">
                <div>Audit Reference: <code>ACC-<?= $event->id ?>-<?= strtoupper(substr(md5($event->id . $event->date), 0, 8)) ?></code></div>
                <div>Authorized Automated Ledger · Eventlyy Financial Services</div>
            </div>
        </section>
    </div>

</div>

<!-- ════════════════════════════════════════════════════════════════
     PRINT STYLING & INTERACTIVE JAVASCRIPT
     ════════════════════════════════════════════════════════════════ -->
<style>
@media print {
    .no-print, nav, .sidebar, header, footer, .acc-topbar, .plain-english-strip {
        display: none !important;
    }
    .print-only-brand {
        display: block !important;
    }
    .acc-tab-panel {
        display: block !important;
        page-break-after: always;
    }
    .acc-card, .acc-hero-card {
        box-shadow: none !important;
        border: 1px solid #ccc !important;
        background: #fff !important;
    }
    body {
        background: #fff !important;
        color: #000 !important;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Tab Switching
    const tabBtns = document.querySelectorAll('.acc-tab-btn');
    const tabPanels = document.querySelectorAll('.acc-tab-panel');

    tabBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const targetId = this.getAttribute('data-tab');
            
            tabBtns.forEach(b => b.classList.remove('active'));
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

    // 2. Search Online Tickets
    const searchOnline = document.getElementById('search-online-tickets');
    if (searchOnline) {
        searchOnline.addEventListener('input', function() {
            const term = this.value.toLowerCase().trim();
            const rows = document.querySelectorAll('#table-online-tickets tbody tr');
            rows.forEach(r => {
                const text = r.textContent.toLowerCase();
                r.style.display = text.includes(term) ? '' : 'none';
            });
            const cards = document.querySelectorAll('#cards-online-tickets .acc-item-card');
            cards.forEach(c => {
                const text = (c.getAttribute('data-search') || c.textContent).toLowerCase();
                c.style.display = text.includes(term) ? '' : 'none';
            });
        });
    }

    // 3. Search Merch Orders
    const searchMerch = document.getElementById('search-merch-orders');
    if (searchMerch) {
        searchMerch.addEventListener('input', function() {
            const term = this.value.toLowerCase().trim();
            const rows = document.querySelectorAll('#table-merch-orders tbody tr');
            rows.forEach(r => {
                const text = r.textContent.toLowerCase();
                r.style.display = text.includes(term) ? '' : 'none';
            });
            const cards = document.querySelectorAll('#cards-merch-orders .acc-item-card');
            cards.forEach(c => {
                const text = (c.getAttribute('data-search') || c.textContent).toLowerCase();
                c.style.display = text.includes(term) ? '' : 'none';
            });
        });
    }

    // 4. Search Issued Passes
    const searchIssued = document.getElementById('search-issued-tickets');
    if (searchIssued) {
        searchIssued.addEventListener('input', function() {
            const term = this.value.toLowerCase().trim();
            const rows = document.querySelectorAll('#table-issued-tickets tbody tr');
            rows.forEach(r => {
                const text = r.textContent.toLowerCase();
                r.style.display = text.includes(term) ? '' : 'none';
            });
            const cards = document.querySelectorAll('#cards-issued-tickets .acc-item-card');
            cards.forEach(c => {
                const text = (c.getAttribute('data-search') || c.textContent).toLowerCase();
                c.style.display = text.includes(term) ? '' : 'none';
            });
        });
    }
});
</script>
@endsection
