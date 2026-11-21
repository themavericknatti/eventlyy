<?php

/**
 * Host Financials & Payouts View
 *
 * Comprehensive financials, per-event revenue breakdown, itemized online ticket sales,
 * online merchandise sales, and payout transactions history.
 *
 * @package Evently
 * @subpackage Views/Host
 * @author Celio Natti
 * @version 3.0.0
 */

$siteName = site_setting('site_title', 'Eventlyy');
$bankConfigured = !empty($profile['payout_bank']) && !empty($profile['payout_account']);

$allPurchasedTickets = is_array($allPurchasedTickets ?? null) ? $allPurchasedTickets : [];
$allMerchOrders = is_array($allMerchOrders ?? null) ? $allMerchOrders : [];
$merchStats = is_array($merchStats ?? null) ? $merchStats : [
    'total_sales' => 0.00,
    'total_items' => 0,
    'fulfilled_items' => 0,
    'pending_items' => 0,
    'orders_count' => 0
];

// Cash flow health distribution math
$totalTracked = ($disbursedPayouts ?? 0) + ($availableBalance ?? 0) + ($escrowRevenue ?? 0) + ($pendingPayouts ?? 0);
$disbursedPct = $totalTracked > 0 ? round((($disbursedPayouts ?? 0) / $totalTracked) * 100, 1) : 0;
$availablePct = $totalTracked > 0 ? round((($availableBalance ?? 0) / $totalTracked) * 100, 1) : 0;
$escrowPct = $totalTracked > 0 ? round((($escrowRevenue ?? 0) / $totalTracked) * 100, 1) : 0;
$pendingPct = $totalTracked > 0 ? round((($pendingPayouts ?? 0) / $totalTracked) * 100, 1) : 0;
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
    color: var(--vn-ink, rgb(var(--text)));
    text-decoration: none;
    transition: color 0.2s ease;
}

.fin-back-link:hover {
    color: var(--vn-brass, #b8860b);
}

/* Page Hero Card */
.fin-hero-card {
    padding: 1.5rem 1.75rem;
    background: var(--vn-parchment-card, rgb(var(--card))) !important;
    border: 1.5px solid var(--vn-border, rgba(var(--muted), 0.16)) !important;
    border-radius: 20px;
    box-shadow: var(--vn-shadow-elevated, 0 4px 20px rgba(0, 0, 0, 0.03)) !important;
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

.fin-hero-info h1 {
    font-family: var(--vn-font-serif, serif) !important;
    color: var(--vn-ink, rgb(var(--text))) !important;
    margin: 0.35rem 0 0.5rem;
    font-size: clamp(1.5rem, 2.5vw, 2.1rem);
    font-weight: normal;
}

/* Linked Bank Destination Box */
.fin-bank-box {
    background: var(--vn-parchment-warm, rgba(var(--muted), 0.05)) !important;
    border: 1.5px solid var(--vn-border, rgba(var(--muted), 0.16)) !important;
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
    background: var(--vn-parchment-card, rgb(var(--card))) !important;
    border: 1.5px solid var(--vn-border, rgba(var(--muted), 0.15)) !important;
    border-radius: 18px;
    padding: 1.25rem 1.35rem;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    box-shadow: var(--vn-shadow-elevated, 0 2px 10px rgba(0, 0, 0, 0.03)) !important;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.fin-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.06) !important;
}

.fin-card-label {
    font-size: 0.78rem;
    font-weight: 800;
    color: var(--vn-ink-muted, rgb(var(--muted)));
    text-transform: uppercase;
    letter-spacing: 0.04em;
    display: block;
    margin-bottom: 0.4rem;
}

.fin-card-value {
    font-size: clamp(1.4rem, 2.2vw, 1.85rem);
    font-weight: 900;
    font-family: var(--vn-font-serif, sans-serif) !important;
    color: var(--vn-brass, rgb(var(--text))) !important;
    margin: 0 0 0.35rem;
    line-height: 1.2;
}

.fin-card-meta {
    font-size: 0.8rem;
    color: var(--vn-ink-muted, rgb(var(--muted)));
    margin: 0;
}

/* Secondary Metrics Grid */
.fin-secondary-grid {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 0.85rem;
    margin-bottom: 1.5rem;
}

.fin-mini-card {
    background: var(--vn-parchment-card, rgba(var(--card), 0.7)) !important;
    border: 1.5px solid var(--vn-border, rgba(var(--muted), 0.12)) !important;
    border-radius: 16px;
    padding: 0.95rem 1.15rem;
    box-shadow: var(--vn-shadow-elevated, 0 2px 8px rgba(0, 0, 0, 0.02)) !important;
}

.fin-mini-value {
    margin: 0.2rem 0;
    font-size: 1.2rem;
    font-weight: 800;
    font-family: var(--vn-font-serif, sans-serif);
    color: var(--vn-ink, rgb(var(--text)));
}

/* Cash Flow Health Bar */
.fin-cashflow-card {
    background: var(--vn-parchment-card, rgb(var(--card))) !important;
    border: 1.5px solid var(--vn-border, rgba(var(--muted), 0.14)) !important;
    border-radius: 18px;
    padding: 1.15rem 1.5rem;
    margin-bottom: 1.75rem;
    box-shadow: var(--vn-shadow-elevated, 0 2px 10px rgba(0,0,0,0.03));
}

.fin-cashflow-bar {
    height: 10px;
    border-radius: 5px;
    background: var(--vn-border, rgba(var(--muted), 0.2));
    display: flex;
    overflow: hidden;
    margin-bottom: 0.85rem;
}

.fin-cashflow-legend {
    display: flex;
    gap: 1.25rem;
    flex-wrap: wrap;
    font-size: 0.8rem;
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
    border-bottom: 2px solid var(--vn-border, rgba(var(--muted), 0.12));
}

.fin-tabs-bar::-webkit-scrollbar {
    display: none;
}

.fin-tabs-bar .tab-btn {
    padding: 0.65rem 1.25rem;
    font-size: 0.88rem;
    font-weight: 700;
    border-radius: 12px;
    border: 1px solid var(--vn-border, rgba(var(--muted), 0.15));
    background: var(--vn-parchment-warm, rgba(var(--muted), 0.06));
    color: var(--vn-ink, rgb(var(--text)));
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
    background: var(--vn-parchment-card, rgba(var(--brand), 0.1));
    color: var(--vn-forest, rgb(var(--brand)));
    border-color: var(--vn-brass, rgba(var(--brand), 0.25));
}

.fin-tabs-bar .tab-btn.active {
    background: var(--vn-brass, #b8860b) !important;
    color: #ffffff !important;
    border-color: var(--vn-brass, #b8860b) !important;
    box-shadow: 0 3px 12px var(--vn-brass-glow, rgba(184, 134, 11, 0.3)) !important;
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
    background: var(--vn-forest, #0B7366) !important;
    color: #ffffff !important;
    box-shadow: 0 4px 12px rgba(11, 115, 102, 0.25);
}

.fin-btn--primary:hover {
    filter: brightness(1.08);
    transform: translateY(-1px);
}

.fin-btn--escrow {
    background: var(--vn-brass, #b8860b) !important;
    color: #ffffff !important;
    box-shadow: 0 4px 12px var(--vn-brass-glow, rgba(184, 134, 11, 0.25));
}

.fin-btn--escrow:hover {
    filter: brightness(1.08);
    transform: translateY(-1px);
}

.fin-btn--disabled {
    background: rgba(var(--muted), 0.1) !important;
    color: var(--vn-ink-muted, rgb(var(--muted))) !important;
    border: 1px solid var(--vn-border, rgba(var(--muted), 0.15));
    cursor: not-allowed;
    opacity: 0.6;
}

/* Tables Section Polish */
.fin-table-card {
    background: var(--vn-parchment-card, rgb(var(--card))) !important;
    border: 1.5px solid var(--vn-border, rgba(var(--muted), 0.14)) !important;
    border-radius: 22px;
    padding: 1.5rem;
    box-shadow: var(--vn-shadow-elevated, 0 4px 18px rgba(0, 0, 0, 0.03)) !important;
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

.fin-table-header h2, .fin-table-header h3 {
    font-family: var(--vn-font-serif, serif) !important;
    color: var(--vn-ink, rgb(var(--text))) !important;
    margin: 0;
    font-weight: normal;
}

.fin-table-search {
    padding: 0.5rem 0.95rem;
    border-radius: 11px;
    border: 1px solid var(--vn-border, rgba(var(--muted), 0.2));
    background: var(--vn-parchment-warm, rgba(var(--card), 0.6));
    color: var(--vn-ink, rgb(var(--text)));
    font-size: 0.85rem;
    min-width: 240px;
    font-family: inherit;
    outline: none;
}

.fin-table-search:focus {
    border-color: var(--vn-brass, #b8860b);
}

.fin-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    font-size: 0.88rem;
    text-align: left;
}

.fin-table th {
    background: var(--vn-parchment-warm, rgba(var(--muted), 0.06)) !important;
    color: var(--vn-ink-soft, rgb(var(--muted))) !important;
    font-size: 0.76rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    padding: 0.85rem 1rem;
    border-bottom: 2px solid var(--vn-border-dark, rgba(var(--muted), 0.2)) !important;
    white-space: nowrap;
}

.fin-table td {
    padding: 0.95rem 1rem;
    border-bottom: 1px solid var(--vn-border, rgba(var(--muted), 0.08)) !important;
    color: var(--vn-ink, rgb(var(--text))) !important;
    vertical-align: middle;
}

.fin-table tr:hover td {
    background: var(--vn-parchment-subtle, rgba(var(--muted), 0.03)) !important;
}

.fin-table tfoot td {
    background: var(--vn-parchment-warm, rgba(var(--muted), 0.06));
    border-top: 2px solid var(--vn-border-dark, rgba(var(--muted), 0.2));
    font-weight: 800;
    padding: 0.95rem 1rem;
}

/* Mobile Stream Cards */
.fin-mobile-stream {
    display: none;
    flex-direction: column;
    gap: 0.85rem;
}

.fin-item-card {
    background: var(--vn-parchment-warm, rgba(var(--muted), 0.04));
    border: 1px solid var(--vn-border, rgba(var(--muted), 0.16));
    border-radius: 14px;
    padding: 1.1rem;
    display: flex;
    flex-direction: column;
    gap: 0.65rem;
}

/* ═══════════════════════════════════════════════════════════
   Responsive Breakpoints (Desktop, Tablet, Mobile)
   ═══════════════════════════════════════════════════════════ */

/* Tablet (769px - 1024px) */
@media (max-width: 1024px) {
    .fin-primary-grid {
        grid-template-columns: repeat(2, 1fr) !important;
    }
    .fin-secondary-grid {
        grid-template-columns: repeat(3, 1fr) !important;
    }
}

/* Mobile (<= 768px) */
@media (max-width: 768px) {
    .fin-table-wrap {
        display: none !important;
    }
    .fin-mobile-stream {
        display: flex !important;
    }
    .fin-primary-grid {
        grid-template-columns: 1fr !important;
        gap: 0.85rem !important;
    }
    .fin-secondary-grid {
        grid-template-columns: 1fr 1fr !important;
        gap: 0.65rem !important;
    }
    .fin-hero-card {
        padding: 1.25rem 1rem !important;
    }
    .fin-hero-wrap {
        flex-direction: column !important;
        gap: 1rem !important;
    }
    .fin-bank-box {
        width: 100% !important;
        box-sizing: border-box !important;
    }
    .fin-table-card {
        padding: 1.15rem 1rem !important;
    }
    .fin-top-nav {
        flex-direction: column !important;
        align-items: stretch !important;
    }
    .fin-table-search {
        width: 100% !important;
    }
}

@media (max-width: 480px) {
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
            <span class="panel-badge" style="background:rgba(16, 185, 129, 0.12); color:#10b981; border:1px solid rgba(16, 185, 129, 0.25);">💳 Host Financials &amp; Settlements</span>
            <h1>Earnings, Revenue &amp; Withdrawals</h1>
            <p class="lede" style="margin:0; font-size:0.92rem; color:var(--vn-ink-muted, rgb(var(--muted)));">
                Track your net online ticket earnings, merchandise revenue, escrow balances, and direct bank payout settlements in real time.
            </p>
        </div>
        
        <!-- Linked Bank Destination Box -->
        <div class="fin-bank-box">
            <span style="font-size:0.75rem; text-transform:uppercase; font-weight:800; color:var(--vn-ink-muted, rgb(var(--muted))); letter-spacing:0.04em; display:block; margin-bottom:0.3rem;">Destination Payout Bank</span>
            <?php if ($bankConfigured): ?>
                <div style="font-weight:800; color:var(--vn-ink, rgb(var(--text))); font-size:1rem; display:flex; align-items:center; gap:0.4rem;">
                    <span>🏦</span> <?= e($profile['payout_bank']) ?>
                </div>
                <div style="font-size:0.84rem; color:var(--vn-ink-muted, rgb(var(--muted))); font-family:monospace; margin-top:3px;">
                    Acc: <?= e($profile['payout_account']) ?>
                    <span style="color:#10b981; font-weight:800; margin-left:4px;">● Verified</span>
                </div>
            <?php else: ?>
                <div style="font-size:0.88rem; color:#ef4444; font-weight:700; display:flex; align-items:center; gap:0.35rem;">
                    <span>⚠️</span> Bank Not Configured
                </div>
                <a href="{{ route('host/settings') }}" style="font-size:0.8rem; color:var(--vn-forest, rgb(var(--brand))); font-weight:700; text-decoration:underline; display:block; margin-top:3px;">Set up payout account →</a>
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
            <h3 class="fin-card-value"><?= format_price($totalNetRevenue ?? 0) ?></h3>
        </div>
        <?php if (($totalPlatformFees ?? 0) > 0): ?>
            <div>
                <p class="fin-card-meta" style="font-size:0.78rem;">Ticket sales: <?= format_price($totalRevenue ?? 0) ?></p>
                <p class="fin-card-meta" style="font-size:0.75rem; color:#ef4444; font-weight:600;">− <?= format_price($totalPlatformFees ?? 0) ?> platform fee (5% absorbed)</p>
            </div>
        <?php else: ?>
            <p class="fin-card-meta" style="font-size:0.78rem;">100% of online ticket sold price</p>
        <?php endif; ?>
    </article>

    <!-- Available Completed Balance -->
    <article class="fin-card" style="border-color: rgba(16, 185, 129, 0.28); background: rgba(16, 185, 129, 0.04);">
        <div>
            <span class="fin-card-label" style="color: #10b981;">Available to Withdraw</span>
            <h3 class="fin-card-value" style="color: #10b981 !important;"><?= format_price($availableBalance ?? 0) ?></h3>
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
            <span class="fin-card-label" style="color: #ea580c;">🔒 Escrow Custody (Upcoming)</span>
            <h3 class="fin-card-value" style="color: #ea580c !important;"><?= format_price($escrowRevenue ?? 0) ?></h3>
            <p class="fin-card-meta" style="font-size:0.78rem;">Withdrawable: <strong><?= format_price($availableEscrowBalance ?? 0) ?></strong></p>
            <?php if (($layawayPendingRevenue ?? 0) > 0): ?>
            <p class="fin-card-meta" style="font-size:0.74rem; color:#b45309; margin-top:0.25rem;">
                ⏳ <?= format_price($layawayPendingRevenue) ?> layaway reserved (<?= (int)($layawayPendingCount ?? 0) ?> plan<?= (int)($layawayPendingCount ?? 0) !== 1 ? 's' : '' ?>) — locked until full payment
            </p>
            <?php endif; ?>
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
            <h3 class="fin-card-value" style="color:#0284c7 !important;"><?= format_price($disbursedPayouts ?? 0) ?></h3>
        </div>
        <p class="fin-card-meta" style="font-size:0.78rem;">Sent directly to your bank account</p>
    </article>
</section>

<!-- Secondary Stats Row -->
<section class="fin-secondary-grid">
    <article class="fin-mini-card">
        <span class="fin-card-label" style="font-size:0.74rem;">Online Ticket Sales</span>
        <h4 class="fin-mini-value" style="color:#10b981;"><?= format_price($totalRevenue ?? 0) ?></h4>
        <p class="fin-card-meta" style="font-size:0.74rem;"><?= number_format($totalTicketsSold ?? 0) ?> online tickets</p>
    </article>
    <article class="fin-mini-card">
        <span class="fin-card-label" style="font-size:0.74rem;">Online Merch Sales</span>
        <h4 class="fin-mini-value" style="color:var(--vn-brass, #b8860b);"><?= format_price($merchStats['total_sales'] ?? 0) ?></h4>
        <p class="fin-card-meta" style="font-size:0.74rem;"><?= (int)($merchStats['total_items'] ?? 0) ?> items sold</p>
    </article>
    <article class="fin-mini-card">
        <span class="fin-card-label" style="font-size:0.74rem;">Pending Payouts</span>
        <h4 class="fin-mini-value" style="color:#d97706;"><?= format_price($pendingPayouts ?? 0) ?></h4>
        <p class="fin-card-meta" style="font-size:0.74rem;">Awaiting admin transfer</p>
    </article>
    <article class="fin-mini-card">
        <span class="fin-card-label" style="font-size:0.74rem;">Direct Gate Passes</span>
        <h4 class="fin-mini-value" style="color:#6366f1;"><?= number_format($totalTicketsIssued ?? 0) ?></h4>
        <p class="fin-card-meta" style="font-size:0.74rem;">Offline gate/comp passes</p>
    </article>
    <article class="fin-mini-card">
        <span class="fin-card-label" style="font-size:0.74rem;">Events Portfolio</span>
        <h4 class="fin-mini-value"><?= number_format($totalEventsCount ?? 0) ?></h4>
        <p class="fin-card-meta" style="font-size:0.74rem;"><?= (($totalOccurrencesCount ?? 0) > ($totalEventsCount ?? 0)) ? '(' . ($totalOccurrencesCount) . ' series dates)' : 'All-time created' ?></p>
    </article>
</section>

<!-- Cash Flow Visual Health Bar -->
<?php if ($totalTracked > 0): ?>
<section class="fin-cashflow-card">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.65rem; flex-wrap:wrap; gap:0.5rem;">
        <span style="font-size:0.82rem; font-weight:800; color:var(--vn-ink-muted, rgb(var(--muted))); text-transform:uppercase; letter-spacing:0.04em;">
            📊 Funds Distribution &amp; Cash Flow Health
        </span>
        <span style="font-size:0.84rem; font-weight:800; color:var(--vn-ink, rgb(var(--text)));">
            Total Accounted: <?= format_price($totalTracked ?? 0) ?>
        </span>
    </div>

    <div class="fin-cashflow-bar">
        <?php if ($disbursedPct > 0): ?>
            <div style="width:<?= $disbursedPct ?>%; background:#0284c7;" title="Disbursed: <?= $disbursedPct ?>% (<?= format_price($disbursedPayouts ?? 0) ?>)"></div>
        <?php endif; ?>
        <?php if ($availablePct > 0): ?>
            <div style="width:<?= $availablePct ?>%; background:#10b981;" title="Available: <?= $availablePct ?>% (<?= format_price($availableBalance ?? 0) ?>)"></div>
        <?php endif; ?>
        <?php if ($escrowPct > 0): ?>
            <div style="width:<?= $escrowPct ?>%; background:var(--vn-brass, #b8860b);" title="Escrow Custody: <?= $escrowPct ?>% (<?= format_price($escrowRevenue ?? 0) ?>)"></div>
        <?php endif; ?>
        <?php if ($pendingPct > 0): ?>
            <div style="width:<?= $pendingPct ?>%; background:#d97706;" title="Pending Review: <?= $pendingPct ?>% (<?= format_price($pendingPayouts ?? 0) ?>)"></div>
        <?php endif; ?>
    </div>

    <div class="fin-cashflow-legend">
        <div style="display:flex; align-items:center; gap:0.35rem;">
            <span style="width:9px; height:9px; border-radius:50%; background:#0284c7;"></span>
            <span style="color:var(--vn-ink-muted, rgb(var(--muted)));">Disbursed:</span>
            <strong><?= $disbursedPct ?>%</strong>
        </div>
        <div style="display:flex; align-items:center; gap:0.35rem;">
            <span style="width:9px; height:9px; border-radius:50%; background:#10b981;"></span>
            <span style="color:var(--vn-ink-muted, rgb(var(--muted)));">Available:</span>
            <strong><?= $availablePct ?>%</strong>
        </div>
        <div style="display:flex; align-items:center; gap:0.35rem;">
            <span style="width:9px; height:9px; border-radius:50%; background:var(--vn-brass, #b8860b);"></span>
            <span style="color:var(--vn-ink-muted, rgb(var(--muted)));">In Escrow:</span>
            <strong><?= $escrowPct ?>%</strong>
        </div>
        <?php if ($pendingPct > 0): ?>
            <div style="display:flex; align-items:center; gap:0.35rem;">
                <span style="width:9px; height:9px; border-radius:50%; background:#d97706;"></span>
                <span style="color:var(--vn-ink-muted, rgb(var(--muted)));">Pending Review:</span>
                <strong><?= $pendingPct ?>%</strong>
            </div>
        <?php endif; ?>
    </div>
</section>
<?php endif; ?>

<!-- Interactive Navigation Tabs -->
<div class="fin-tabs-bar dashboard-tabs">
    <button type="button" class="tab-btn active" id="tab-btn-events" onclick="switchFinancialTab('events')">
        🎪 Per-Event Breakdown (<?= count($eventFinancials ?? []) ?>)
    </button>
    <button type="button" class="tab-btn" id="tab-btn-tickets" onclick="switchFinancialTab('tickets')">
        💳 Purchased Online Tickets (<?= count($allPurchasedTickets) ?>)
    </button>
    <button type="button" class="tab-btn" id="tab-btn-merch" onclick="switchFinancialTab('merch')">
        🛍️ Online Merch Sales (<?= count($allMerchOrders) ?>)
    </button>
    <button type="button" class="tab-btn" id="tab-btn-history" onclick="switchFinancialTab('history')">
        💸 Payout History (<?= count($payoutHistory ?? []) ?>)
    </button>
    <button type="button" class="tab-btn" id="tab-btn-guide" onclick="switchFinancialTab('guide')">
        ℹ️ How Payouts &amp; Accounting Work
    </button>
</div>

<!-- ======================================================== -->
<!-- TAB 1: PER-EVENT EARNINGS BREAKDOWN                      -->
<!-- ======================================================== -->
<div id="tab-content-events" class="financial-tab-panel">
    <section class="fin-table-card">
        <div class="fin-table-header">
            <div>
                <h2>Event Revenue &amp; Settlement Tally</h2>
                <p style="margin:0.25rem 0 0; font-size:0.82rem; color:var(--vn-ink-muted, rgb(var(--muted)));">
                    Exact online ticket sold price, host net share, disbursed amounts, and unrequested balances per event.
                </p>
            </div>
            <input type="text" id="event-search-input" class="fin-table-search" placeholder="🔍 Search events..." onkeyup="filterEventsTable()" />
        </div>

        <?php if (empty($eventFinancials)): ?>
            <div style="padding:3rem 1rem; text-align:center;">
                <span style="font-size:2.8rem; display:block; margin-bottom:0.75rem;">🎟️</span>
                <p style="margin:0 0 1rem; font-weight:700; color:var(--vn-ink-muted, rgb(var(--muted)));">No events found in your account.</p>
                <a href="{{ route('host/event/create') }}" class="fin-btn fin-btn--primary">Create Your First Event</a>
            </div>
        <?php else: ?>
            <div class="fin-table-wrap" style="overflow-x:auto;">
                <table class="fin-table" id="events-breakdown-table">
                    <thead>
                        <tr>
                            <th style="min-width:200px;">Event</th>
                            <th>Status &amp; Policy</th>
                            <th>Tickets</th>
                            <th style="text-align:right;">Ticket Sold Price</th>
                            <th style="text-align:right;">Host Net Share</th>
                            <th style="text-align:right;">Disbursed</th>
                            <th style="text-align:right;">Remaining</th>
                            <th style="text-align:right;">Actions</th>
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
                                    <strong style="color:var(--vn-ink, rgb(var(--text))); font-size:0.92rem; display:block;"><?= e($ev['name']) ?></strong>
                                    <span style="font-size:0.76rem; color:var(--vn-ink-muted, rgb(var(--muted))); display:block; margin-top:2px;">
                                        📅 <?= date('M d, Y', strtotime($ev['date'])) ?> · <?= e($ev['location']) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($ev['state_badge'] === 'completed'): ?>
                                        <span style="background:rgba(100, 116, 139, 0.12); color:rgb(100, 116, 139); font-size:0.72rem; font-weight:800; padding:2px 7px; border-radius:6px; display:inline-block;">
                                            ✓ Completed
                                        </span>
                                    <?php elseif ($ev['state_badge'] === 'live'): ?>
                                        <span style="background:rgba(239, 68, 68, 0.12); color:#ef4444; font-size:0.72rem; font-weight:800; padding:2px 7px; border-radius:6px; display:inline-block;">
                                            🔴 Live Now
                                        </span>
                                    <?php else: ?>
                                        <span style="background:rgba(16, 185, 129, 0.12); color:#10b981; font-size:0.72rem; font-weight:800; padding:2px 7px; border-radius:6px; display:inline-block;">
                                            📅 Upcoming
                                        </span>
                                    <?php endif; ?>

                                    <?php if ($isPassFee): ?>
                                        <span style="font-size:0.72rem; color:var(--vn-ink-muted, rgb(var(--muted))); display:block; margin-top:3px;" title="Buyer covered 5% fee at checkout">🎟️ Net 100% (Buyer paid)</span>
                                    <?php else: ?>
                                        <span style="font-size:0.72rem; color:#8b5cf6; font-weight:700; display:block; margin-top:3px;" title="5% platform fee deducted from ticket price">🏷️ Net 95% (Host fee)</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong style="color:var(--vn-ink, rgb(var(--text)));"><?= number_format($ev['ticket_count']) ?></strong>
                                    <span style="font-size:0.75rem; color:var(--vn-ink-muted, rgb(var(--muted))); display:block;">online sold</span>
                                    <?php if (($ev['issued_count'] ?? 0) > 0): ?>
                                        <span style="font-size:0.72rem; color:#6366f1; display:block;">+<?= number_format($ev['issued_count']) ?> issued</span>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align:right;">
                                    <strong style="color:var(--vn-ink, rgb(var(--text)));"><?= format_price($ev['ticket_sales']) ?></strong>
                                    <?php if (($ev['activity_sales'] ?? 0) > 0): ?>
                                        <span style="font-size:0.72rem; color:#8b5cf6; display:block;" title="Power Vote Poll Activities">+<?= format_price($ev['activity_sales']) ?> activities</span>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align:right;">
                                    <strong style="color:#10b981; font-size:0.95rem;"><?= format_price($ev['net_sales']) ?></strong>
                                    <?php if ($ev['platform_fees'] > 0): ?>
                                        <span style="font-size:0.72rem; color:#ef4444; display:block;">−<?= format_price($ev['platform_fees']) ?> fee</span>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align:right;">
                                    <strong><?= format_price($ev['total_disbursed']) ?></strong>
                                    <?php if ($ev['escrow_disbursed'] > 0): ?>
                                        <span style="font-size:0.72rem; color:#ea580c; display:block;">⚡ <?= format_price($ev['escrow_disbursed']) ?> escrow</span>
                                    <?php endif; ?>
                                    <?php if ($ev['pending_amount'] > 0): ?>
                                        <span style="font-size:0.72rem; color:#d97706; display:block;">⏳ <?= format_price($ev['pending_amount']) ?> pending</span>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align:right;">
                                    <?php if ($hasRemainder): ?>
                                        <strong style="color:#d97706; font-size:0.95rem;"><?= format_price($ev['unrequested_balance']) ?></strong>
                                        <span style="font-size:0.72rem; color:#ea580c; display:block; font-weight:700;">⚡ Unrequested</span>
                                    <?php else: ?>
                                        <span style="color:#059669; font-weight:800; font-size:0.82rem;">✓ Settled</span>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align:right;">
                                    <div style="display:inline-flex; gap:0.4rem; align-items:center; flex-wrap:wrap; justify-content:flex-end;">
                                        <a href="{{ route('host/event/accounting/' . $ev['id']) }}" class="admin-action-btn" style="padding:0.35rem 0.65rem; font-size:0.78rem; border-radius:8px; text-decoration:none; font-weight:700;" title="View Complete Accounting Audit Report">
                                            📊 Report
                                        </a>
                                        <?php if ($hasRemainder && $bankConfigured): ?>
                                            <?php if ($ev['state_badge'] === 'completed'): ?>
                                                <form method="POST" action="{{ route('host/withdraw') }}" style="display:inline-block; margin:0;">
                                                    <?= csrf_field() ?>
                                                    <input type="hidden" name="withdraw_type" value="completed">
                                                    <input type="hidden" name="event_id" value="<?= (int)$ev['id'] ?>">
                                                    <button type="submit" class="fin-btn fin-btn--primary" style="padding:0.35rem 0.65rem; font-size:0.76rem; border-radius:8px;" title="Withdraw <?= format_price($ev['unrequested_balance']) ?>">
                                                        💸 Withdraw
                                                    </button>
                                                </form>
                                            <?php elseif ($ev['state_badge'] === 'upcoming' || $ev['state_badge'] === 'live'): ?>
                                                <button type="button" class="fin-btn fin-btn--escrow per-event-escrow-btn" data-event-id="<?= (int)$ev['id'] ?>" data-event-name="<?= e($ev['name']) ?>" data-event-amount="<?= format_price($ev['unrequested_balance']) ?>" style="padding:0.35rem 0.65rem; font-size:0.76rem; border-radius:8px;" title="Request early escrow release">
                                                    ⚡ Escrow
                                                </button>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Mobile Stream Cards -->
            <div class="fin-mobile-stream" id="events-mobile-stream">
                <?php foreach ($eventFinancials as $ev): ?>
                    <div class="fin-item-card" data-search="<?= strtolower(e($ev['name'] . ' ' . $ev['location'])) ?>">
                        <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:0.5rem;">
                            <div>
                                <h4 style="margin:0; font-size:1rem; font-weight:800;"><?= e($ev['name']) ?></h4>
                                <div style="font-size:0.76rem; color:var(--vn-ink-muted, rgb(var(--muted))); margin-top:2px;">
                                    📅 <?= date('M d, Y', strtotime($ev['date'])) ?> · <?= e($ev['location']) ?>
                                </div>
                            </div>
                            <div style="text-align:right;">
                                <div style="font-weight:900; font-size:1.05rem; color:#10b981; font-family:var(--vn-font-serif, sans-serif);"><?= format_price($ev['net_sales']) ?></div>
                                <span style="font-size:0.72rem; color:var(--vn-ink-muted, rgb(var(--muted)));">Host Net Share</span>
                            </div>
                        </div>

                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:0.5rem; font-size:0.8rem; border-top:1px dashed var(--vn-border, rgba(var(--muted),0.15)); padding-top:0.5rem;">
                            <div>
                                <span style="color:var(--vn-ink-muted, rgb(var(--muted))); display:block; font-size:0.72rem;">Tickets Sold:</span>
                                <strong><?= number_format($ev['ticket_count']) ?> online</strong>
                            </div>
                            <div>
                                <span style="color:var(--vn-ink-muted, rgb(var(--muted))); display:block; font-size:0.72rem;">Gross Sold Price:</span>
                                <strong><?= format_price($ev['ticket_sales']) ?></strong>
                                <?php if (($ev['activity_sales'] ?? 0) > 0): ?>
                                    <span style="font-size:0.72rem; color:#8b5cf6; display:block;">+<?= format_price($ev['activity_sales']) ?> activities</span>
                                <?php endif; ?>
                            </div>
                            <div>
                                <span style="color:var(--vn-ink-muted, rgb(var(--muted))); display:block; font-size:0.72rem;">Disbursed So Far:</span>
                                <strong style="color:#0284c7;"><?= format_price($ev['total_disbursed']) ?></strong>
                            </div>
                            <div>
                                <span style="color:var(--vn-ink-muted, rgb(var(--muted))); display:block; font-size:0.72rem;">Remaining:</span>
                                <strong style="color:<?= ($ev['unrequested_balance'] ?? 0) > 0 ? '#d97706' : '#059669' ?>;">
                                    <?= ($ev['unrequested_balance'] ?? 0) > 0 ? format_price($ev['unrequested_balance']) : '✓ Settled' ?>
                                </strong>
                            </div>
                        </div>

                        <div style="display:flex; justify-content:space-between; align-items:center; gap:0.5rem; border-top:1px solid var(--vn-border, rgba(var(--muted),0.1)); padding-top:0.5rem; margin-top:0.25rem;">
                            <a href="{{ route('host/event/accounting/' . $ev['id']) }}" class="admin-action-btn" style="flex:1; justify-content:center; padding:0.45rem; font-size:0.8rem; border-radius:8px; text-decoration:none; font-weight:700;">
                                📊 Full Accounting Report →
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</div>

<!-- ======================================================== -->
<!-- TAB 2: ALL PURCHASED ONLINE TICKETS                      -->
<!-- ======================================================== -->
<div id="tab-content-tickets" class="financial-tab-panel" style="display:none;">
    <section class="fin-table-card">
        <div class="fin-table-header">
            <div>
                <h2>💳 All Purchased Online Tickets</h2>
                <p style="margin:0.25rem 0 0; font-size:0.82rem; color:var(--vn-ink-muted, rgb(var(--muted)));">
                    Complete stream of all individual online tickets bought by attendees across your events (Total: <?= count($allPurchasedTickets) ?> tickets).
                </p>
            </div>
            <input type="text" id="ticket-search-input" class="fin-table-search" placeholder="🔍 Search attendee, email, or event..." onkeyup="filterTicketsTable()" />
        </div>

        <!-- Tickets Summary Strip -->
        <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:0.75rem; background:rgba(16, 185, 129, 0.08); border:1px solid rgba(16, 185, 129, 0.22); border-radius:12px; padding:0.85rem 1.25rem; margin-bottom:1.25rem;">
            <div>
                <span style="font-size:0.74rem; font-weight:800; text-transform:uppercase; letter-spacing:0.04em; color:#065f46;">Total Online Tickets Purchased</span>
                <div style="font-size:1.15rem; font-weight:800; color:#065f46;"><?= count($allPurchasedTickets) ?> Verified Tickets</div>
            </div>
            <div style="text-align:right;">
                <span style="font-size:0.74rem; font-weight:800; text-transform:uppercase; letter-spacing:0.04em; color:#065f46;">Total Face Value</span>
                <div style="font-size:1.35rem; font-weight:900; color:#10b981; font-family:var(--vn-font-serif, sans-serif);"><?= format_price($totalRevenue ?? 0) ?></div>
            </div>
        </div>

        <?php if (empty($allPurchasedTickets)): ?>
            <div style="padding:3.5rem 1rem; text-align:center; color:var(--vn-ink-muted, #64748b);">
                <span style="font-size:2.8rem; display:block; margin-bottom:0.5rem;">💳</span>
                <strong style="font-size:1.05rem;">No online tickets purchased yet.</strong>
                <p style="font-size:0.85rem; margin-top:0.25rem;">When attendees purchase tickets online through Paystack, every ticket will be itemized here.</p>
            </div>
        <?php else: ?>
            <div class="fin-table-wrap" style="overflow-x:auto;">
                <table class="fin-table" id="all-tickets-table">
                    <thead>
                        <tr>
                            <th>Ticket Code</th>
                            <th>Attendee</th>
                            <th>Event Attribution</th>
                            <th>Tier</th>
                            <th style="text-align:right;">Sold Price</th>
                            <th style="text-align:center;">Gate Status</th>
                            <th>Purchase Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($allPurchasedTickets as $t): ?>
                            <tr class="ticket-row" data-search="<?= strtolower(e(($t['id'] ?? '') . ' ' . ($t['attendee_name'] ?? '') . ' ' . ($t['attendee_email'] ?? '') . ' ' . ($t['event_name'] ?? '') . ' ' . ($t['ticket_tier'] ?? ''))) ?>">
                                <td style="font-family:monospace; font-weight:800; color:var(--vn-forest, #0B7366);">
                                    #<?= e($t['id']) ?>
                                </td>
                                <td>
                                    <strong><?= e($t['attendee_name'] ?: 'Guest Attendee') ?></strong>
                                    <div style="font-size:0.76rem; color:var(--vn-ink-muted, rgb(var(--muted)));"><?= e($t['attendee_email']) ?></div>
                                </td>
                                <td>
                                    <strong style="color:var(--vn-ink, rgb(var(--text)));"><?= e($t['event_name'] ?? 'Event') ?></strong>
                                </td>
                                <td>
                                    <span style="display:inline-block; padding:0.2rem 0.5rem; border-radius:6px; background:rgba(var(--muted),0.12); font-weight:700; font-size:0.74rem;">
                                        <?= e($t['ticket_tier'] ?? 'General') ?>
                                    </span>
                                </td>
                                <td style="text-align:right; font-weight:800; color:var(--vn-ink, rgb(var(--text)));">
                                    <?= format_price($t['price']) ?>
                                </td>
                                <td style="text-align:center;">
                                    <?php if (!empty($t['checked_in'])): ?>
                                        <span style="color:#10b981; font-weight:800; font-size:0.76rem; background:rgba(16,185,129,0.12); padding:0.2rem 0.55rem; border-radius:6px;">✓ Checked In</span>
                                    <?php else: ?>
                                        <span style="color:var(--vn-ink-muted, #64748b); font-size:0.76rem;">Awaiting Entry</span>
                                    <?php endif; ?>
                                </td>
                                <td style="font-size:0.78rem; color:var(--vn-ink-muted, rgb(var(--muted))); white-space:nowrap;">
                                    <?= !empty($t['purchase_date']) ? date('M d, Y • H:i', strtotime($t['purchase_date'])) : 'N/A' ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="4" style="text-transform:uppercase; letter-spacing:0.04em;">
                                TOTAL ONLINE TICKETS (<?= count($allPurchasedTickets) ?> Purchases)
                            </td>
                            <td style="text-align:right; font-size:1.1rem; color:#10b981;">
                                <?= format_price($totalRevenue ?? 0) ?>
                            </td>
                            <td colspan="2" style="text-align:center; font-size:0.8rem; color:var(--vn-ink-muted, #64748b);">
                                <?= count(array_filter($allPurchasedTickets, fn($x) => !empty($x['checked_in']))) ?> verified at door
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <!-- Mobile Card Stream -->
            <div class="fin-mobile-stream" id="tickets-mobile-stream">
                <?php foreach ($allPurchasedTickets as $t): ?>
                    <div class="fin-item-card ticket-card" data-search="<?= strtolower(e(($t['id'] ?? '') . ' ' . ($t['attendee_name'] ?? '') . ' ' . ($t['attendee_email'] ?? '') . ' ' . ($t['event_name'] ?? '') . ' ' . ($t['ticket_tier'] ?? ''))) ?>">
                        <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:0.5rem;">
                            <div>
                                <span style="font-family:monospace; font-weight:800; color:var(--vn-forest, #0B7366); font-size:0.82rem;">Ticket #<?= e($t['id']) ?></span>
                                <h4 style="margin:0.2rem 0 0; font-size:0.96rem; font-weight:800;"><?= e($t['attendee_name'] ?: 'Guest') ?></h4>
                                <div style="font-size:0.76rem; color:var(--vn-ink-muted, #64748b);"><?= e($t['attendee_email']) ?></div>
                                <div style="font-size:0.78rem; font-weight:700; color:var(--vn-ink, #0f172a); margin-top:0.2rem;">🎪 <?= e($t['event_name'] ?? 'Event') ?></div>
                            </div>
                            <div style="text-align:right; flex-shrink:0;">
                                <div style="font-weight:900; font-size:1.05rem; color:#10b981; font-family:var(--vn-font-serif, sans-serif);"><?= format_price($t['price']) ?></div>
                                <span style="display:inline-block; padding:0.2rem 0.5rem; border-radius:6px; background:rgba(var(--muted),0.12); font-weight:700; font-size:0.72rem; margin-top:0.25rem;">
                                    <?= e($t['ticket_tier'] ?? 'General') ?>
                                </span>
                            </div>
                        </div>
                        <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:0.4rem; font-size:0.78rem; border-top:1px dashed var(--vn-border, rgba(var(--muted),0.16)); padding-top:0.5rem; margin-top:0.25rem;">
                            <div>
                                <?php if (!empty($t['checked_in'])): ?>
                                    <span style="color:#10b981; font-weight:800;">✓ Checked In</span>
                                <?php else: ?>
                                    <span style="color:var(--vn-ink-muted, #64748b);">Awaiting Entry</span>
                                <?php endif; ?>
                            </div>
                            <div style="color:var(--vn-ink-muted, #64748b); font-size:0.74rem;">
                                <?= !empty($t['purchase_date']) ? date('M d, Y • H:i', strtotime($t['purchase_date'])) : 'N/A' ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</div>

<!-- ======================================================== -->
<!-- TAB 3: ALL ONLINE MERCHANDISE & ADD-ON SALES             -->
<!-- ======================================================== -->
<div id="tab-content-merch" class="financial-tab-panel" style="display:none;">
    <section class="fin-table-card">
        <div class="fin-table-header">
            <div>
                <h2>🛍️ Online Merchandise &amp; Add-On Orders</h2>
                <p style="margin:0.25rem 0 0; font-size:0.82rem; color:var(--vn-ink-muted, rgb(var(--muted)));">
                    All merchandise, souvenirs, and event perks purchased online across your events (Total: <?= count($allMerchOrders) ?> orders).
                </p>
            </div>
            <input type="text" id="merch-search-input" class="fin-table-search" placeholder="🔍 Search buyer, item, or event..." onkeyup="filterMerchTable()" />
        </div>

        <!-- Merch Summary KPI Strip -->
        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(180px, 1fr)); gap:0.75rem; background:rgba(184, 134, 11, 0.08); border:1px solid rgba(184, 134, 11, 0.22); border-radius:12px; padding:1rem 1.25rem; margin-bottom:1.5rem;">
            <div>
                <span style="font-size:0.74rem; font-weight:800; text-transform:uppercase; letter-spacing:0.04em; color:var(--vn-brass, #b8860b);">Total Merch Revenue</span>
                <div style="font-size:1.35rem; font-weight:900; color:var(--vn-brass, #b8860b); font-family:var(--vn-font-serif, sans-serif);"><?= format_price($merchStats['total_sales'] ?? 0) ?></div>
            </div>
            <div>
                <span style="font-size:0.74rem; font-weight:800; text-transform:uppercase; letter-spacing:0.04em; color:var(--vn-ink-muted, #64748b);">Total Items Sold</span>
                <div style="font-size:1.15rem; font-weight:800; color:var(--vn-ink, #0f172a);"><?= (int)($merchStats['total_items'] ?? 0) ?> Units</div>
            </div>
            <div>
                <span style="font-size:0.74rem; font-weight:800; text-transform:uppercase; letter-spacing:0.04em; color:#10b981;">Fulfilled / Collected</span>
                <div style="font-size:1.15rem; font-weight:800; color:#10b981;"><?= (int)($merchStats['fulfilled_items'] ?? 0) ?> Units</div>
            </div>
            <div>
                <span style="font-size:0.74rem; font-weight:800; text-transform:uppercase; letter-spacing:0.04em; color:#d97706;">Pending Pickup</span>
                <div style="font-size:1.15rem; font-weight:800; color:#d97706;"><?= (int)($merchStats['pending_items'] ?? 0) ?> Units</div>
            </div>
        </div>

        <?php if (empty($allMerchOrders)): ?>
            <div style="padding:3.5rem 1rem; text-align:center; color:var(--vn-ink-muted, #64748b);">
                <span style="font-size:2.8rem; display:block; margin-bottom:0.5rem;">🛍️</span>
                <strong style="font-size:1.05rem;">No merchandise or add-on orders recorded yet.</strong>
                <p style="font-size:0.85rem; margin-top:0.25rem;">When attendees add official merchandise or VIP add-ons to their cart, orders will be detailed here.</p>
            </div>
        <?php else: ?>
            <div class="fin-table-wrap" style="overflow-x:auto;">
                <table class="fin-table" id="all-merch-table">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Item Name</th>
                            <th>Event Attribution</th>
                            <th>Buyer Details</th>
                            <th style="text-align:right;">Unit Price</th>
                            <th style="text-align:center;">Qty</th>
                            <th style="text-align:right;">Total Paid</th>
                            <th style="text-align:center;">Status</th>
                            <th>Order Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($allMerchOrders as $mo): ?>
                            <tr class="merch-row" data-search="<?= strtolower(e(($mo['id'] ?? '') . ' ' . ($mo['addon_name'] ?? '') . ' ' . ($mo['buyer_name'] ?? '') . ' ' . ($mo['buyer_email'] ?? '') . ' ' . ($mo['event_name'] ?? ''))) ?>">
                                <td style="font-family:monospace; font-weight:800; color:var(--vn-brass, #b8860b);">
                                    #MR-<?= e($mo['id']) ?>
                                </td>
                                <td>
                                    <div style="display:flex; align-items:center; gap:0.6rem;">
                                        <?php if (!empty($mo['addon_image'])): ?>
                                            <img src="<?= e($mo['addon_image']) ?>" alt="merch" style="width:34px; height:34px; border-radius:8px; object-fit:cover; border:1px solid var(--vn-border, #e2e8f0);">
                                        <?php else: ?>
                                            <span>🛍️</span>
                                        <?php endif; ?>
                                        <strong><?= e($mo['addon_name'] ?: 'Merchandise Item') ?></strong>
                                    </div>
                                </td>
                                <td>
                                    <strong style="color:var(--vn-ink, rgb(var(--text)));"><?= e($mo['event_name'] ?? 'Event') ?></strong>
                                </td>
                                <td>
                                    <strong><?= e($mo['buyer_name'] ?: 'Guest Buyer') ?></strong>
                                    <div style="font-size:0.76rem; color:var(--vn-ink-muted, rgb(var(--muted)));"><?= e($mo['buyer_email']) ?></div>
                                </td>
                                <td style="text-align:right; font-weight:700;">
                                    <?= format_price($mo['unit_price'] ?? 0) ?>
                                </td>
                                <td style="text-align:center; font-weight:800; font-size:0.95rem;">
                                    <?= (int)($mo['quantity'] ?? 1) ?>
                                </td>
                                <td style="text-align:right; font-weight:900; color:var(--vn-brass, #b8860b);">
                                    <?= format_price($mo['total_price'] ?? 0) ?>
                                </td>
                                <td style="text-align:center;">
                                    <?php if (($mo['redemption_status'] ?? '') === 'fulfilled'): ?>
                                        <span style="color:#10b981; font-weight:800; font-size:0.76rem; background:rgba(16,185,129,0.12); padding:0.2rem 0.55rem; border-radius:6px;">✓ Fulfilled</span>
                                    <?php elseif (($mo['redemption_status'] ?? '') === 'cancelled'): ?>
                                        <span style="color:#ef4444; font-weight:800; font-size:0.76rem; background:rgba(239,68,68,0.12); padding:0.2rem 0.55rem; border-radius:6px;">Cancelled</span>
                                    <?php else: ?>
                                        <span style="color:#d97706; font-weight:800; font-size:0.76rem; background:rgba(217,119,6,0.12); padding:0.2rem 0.55rem; border-radius:6px;">⏳ Pending</span>
                                    <?php endif; ?>
                                </td>
                                <td style="font-size:0.78rem; color:var(--vn-ink-muted, rgb(var(--muted))); white-space:nowrap;">
                                    <?= !empty($mo['created_at']) ? date('M d, Y • H:i', strtotime($mo['created_at'])) : 'N/A' ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="5" style="text-transform:uppercase; letter-spacing:0.04em;">
                                TOTAL ONLINE MERCHANDISE SALES (<?= count($allMerchOrders) ?> Orders)
                            </td>
                            <td style="text-align:center; font-size:1rem; color:var(--vn-ink, #0f172a);">
                                <?= (int)($merchStats['total_items'] ?? 0) ?> Items
                            </td>
                            <td style="text-align:right; font-size:1.15rem; color:var(--vn-brass, #b8860b);">
                                <?= format_price($merchStats['total_sales'] ?? 0) ?>
                            </td>
                            <td colspan="2" style="text-align:center; font-size:0.8rem; color:var(--vn-ink-muted, #64748b);">
                                <?= (int)($merchStats['fulfilled_items'] ?? 0) ?> collected · <?= (int)($merchStats['pending_items'] ?? 0) ?> pending
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <!-- Mobile Card Stream -->
            <div class="fin-mobile-stream" id="merch-mobile-stream">
                <?php foreach ($allMerchOrders as $mo): ?>
                    <div class="fin-item-card merch-card" data-search="<?= strtolower(e(($mo['id'] ?? '') . ' ' . ($mo['addon_name'] ?? '') . ' ' . ($mo['buyer_name'] ?? '') . ' ' . ($mo['buyer_email'] ?? '') . ' ' . ($mo['event_name'] ?? ''))) ?>">
                        <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:0.5rem;">
                            <div style="display:flex; gap:0.6rem; align-items:center;">
                                <?php if (!empty($mo['addon_image'])): ?>
                                    <img src="<?= e($mo['addon_image']) ?>" alt="merch" style="width:38px; height:38px; border-radius:8px; object-fit:cover; border:1px solid var(--vn-border, #e2e8f0);">
                                <?php endif; ?>
                                <div>
                                    <span style="font-family:monospace; font-weight:800; color:var(--vn-brass, #b8860b); font-size:0.82rem;">#MR-<?= e($mo['id']) ?></span>
                                    <h4 style="margin:0.2rem 0 0; font-size:0.96rem; font-weight:800;"><?= e($mo['addon_name'] ?: 'Merchandise Item') ?></h4>
                                    <div style="font-size:0.76rem; color:var(--vn-ink-muted, #64748b);"><?= e($mo['buyer_name'] ?: 'Guest') ?></div>
                                    <div style="font-size:0.78rem; font-weight:700; color:var(--vn-ink, #0f172a); margin-top:0.2rem;">🎪 <?= e($mo['event_name'] ?? 'Event') ?></div>
                                </div>
                            </div>
                            <div style="text-align:right; flex-shrink:0;">
                                <div style="font-weight:900; font-size:1.05rem; color:var(--vn-brass, #b8860b); font-family:var(--vn-font-serif, sans-serif);"><?= format_price($mo['total_price'] ?? 0) ?></div>
                                <div style="font-size:0.74rem; color:var(--vn-ink-muted, #64748b); font-weight:700;">Qty: <?= (int)($mo['quantity'] ?? 1) ?> × <?= format_price($mo['unit_price'] ?? 0) ?></div>
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

<!-- ======================================================== -->
<!-- TAB 4: WITHDRAWAL & PAYOUT TRANSACTIONS HISTORY          -->
<!-- ======================================================== -->
<div id="tab-content-history" class="financial-tab-panel" style="display:none;">
    <section class="fin-table-card">
        <div class="fin-table-header">
            <div>
                <h2>Withdrawal &amp; Payout Transactions</h2>
                <p style="margin:0.25rem 0 0; font-size:0.82rem; color:var(--vn-ink-muted, rgb(var(--muted)));">
                    All payout requests, early escrow withdrawals, and automatic settlements wired to your bank.
                </p>
            </div>
            <input type="text" id="payout-search-input" class="fin-table-search" placeholder="🔍 Search payouts..." onkeyup="filterPayoutsTable()" />
        </div>

        <?php if (empty($payoutHistory)): ?>
            <div style="padding:3.5rem 1rem; text-align:center;">
                <span style="font-size:2.8rem; display:block; margin-bottom:0.75rem;">💸</span>
                <p style="margin:0 0 1rem; font-weight:700; color:var(--vn-ink-muted, rgb(var(--muted)));">No withdrawal requests recorded yet.</p>
                <?php if (($availableBalance ?? 0) > 0 && $bankConfigured): ?>
                    <form method="POST" action="{{ route('host/withdraw') }}" style="display:inline-block;">
                        <?= csrf_field() ?>
                        <input type="hidden" name="withdraw_type" value="completed">
                        <button type="submit" class="fin-btn fin-btn--primary">Request Payout Now</button>
                    </form>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="fin-table-wrap" style="overflow-x:auto;">
                <table class="fin-table" id="payouts-history-table">
                    <thead>
                        <tr>
                            <th>Ref #</th>
                            <th>Event Attribution</th>
                            <th>Withdrawal Type</th>
                            <th style="text-align:right;">Amount</th>
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
                                    <strong style="color:var(--vn-ink, rgb(var(--text))); font-family:monospace; font-size:0.92rem;">#<?= e($p['id']) ?></strong>
                                </td>
                                <td>
                                    <?php if (!empty($p['event_name'])): ?>
                                        <strong style="color:var(--vn-ink, rgb(var(--text))); font-size:0.9rem; display:block;"><?= e($p['event_name']) ?></strong>
                                        <span style="font-size:0.75rem; color:var(--vn-ink-muted, rgb(var(--muted))); display:block; margin-top:2px;">
                                            Event ID: #<?= e($p['event_id']) ?>
                                        </span>
                                    <?php else: ?>
                                        <span style="color:var(--vn-ink-muted, rgb(var(--muted))); font-style:italic;">General Portfolio Balance</span>
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
                                <td style="text-align:right;">
                                    <strong style="color:var(--vn-ink, rgb(var(--text))); font-size:1.02rem;"><?= format_price($p['amount']) ?></strong>
                                </td>
                                <td>
                                    <div style="font-size:0.86rem; font-weight:700; color:var(--vn-ink, rgb(var(--text)));">
                                        🏦 <?= e($p['payout_bank'] ?? ($profile['payout_bank'] ?? 'Bank')) ?>
                                    </div>
                                    <div style="font-size:0.76rem; color:var(--vn-ink-muted, rgb(var(--muted))); font-family:monospace;">
                                        Acc: <?= e($p['payout_account'] ?? ($profile['payout_account'] ?? '—')) ?>
                                    </div>
                                </td>
                                <td>
                                    <?php if ($isCompleted): ?>
                                        <span style="background:rgba(16, 185, 129, 0.12); color:#059669; border:1px solid rgba(16, 185, 129, 0.28); font-size:0.74rem; font-weight:800; padding:3px 9px; border-radius:20px; display:inline-flex; align-items:center; gap:4px;">
                                            ✓ Disbursed
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
                                <td style="text-align:right; font-size:0.82rem; color:var(--vn-ink-muted, rgb(var(--muted)));">
                                    <?= date('M d, Y • g:i A', strtotime($p['created_at'])) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Mobile Card Stream -->
            <div class="fin-mobile-stream" id="payouts-mobile-stream">
                <?php foreach ($payoutHistory as $p): ?>
                    <?php
                        $type = $p['type'] ?? 'completed';
                        $status = $p['status'] ?? 'pending';
                        $isCompleted = in_array($status, ['processed', 'completed']);
                    ?>
                    <div class="fin-item-card payout-card" data-search="<?= strtolower(e(($p['id'] ?? '') . ' ' . ($p['event_name'] ?? '') . ' ' . $type . ' ' . $status)) ?>">
                        <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:0.5rem;">
                            <div>
                                <span style="font-family:monospace; font-weight:800; color:var(--vn-forest, #0B7366); font-size:0.84rem;">Payout #<?= e($p['id']) ?></span>
                                <h4 style="margin:0.2rem 0 0; font-size:0.96rem; font-weight:800;"><?= e($p['event_name'] ?: 'General Portfolio Balance') ?></h4>
                                <div style="font-size:0.76rem; color:var(--vn-ink-muted, #64748b);">🏦 <?= e($p['payout_bank'] ?? 'Bank') ?> · Acc: <?= e($p['payout_account'] ?? '—') ?></div>
                            </div>
                            <div style="text-align:right;">
                                <div style="font-weight:900; font-size:1.1rem; color:var(--vn-ink, #0f172a); font-family:var(--vn-font-serif, sans-serif);"><?= format_price($p['amount']) ?></div>
                                <?php if ($isCompleted): ?>
                                    <span style="color:#059669; font-weight:800; font-size:0.74rem;">✓ Disbursed</span>
                                <?php else: ?>
                                    <span style="color:#b45309; font-weight:800; font-size:0.74rem;">⏳ In Review</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div style="font-size:0.74rem; color:var(--vn-ink-muted, #64748b); border-top:1px dashed var(--vn-border, rgba(var(--muted),0.15)); padding-top:0.4rem; margin-top:0.25rem;">
                            Requested on <?= date('M d, Y • g:i A', strtotime($p['created_at'])) ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</div>

<!-- ======================================================== -->
<!-- TAB 5: HOW PAYOUTS & FEE SETTLEMENTS WORK                -->
<!-- ======================================================== -->
<div id="tab-content-guide" class="financial-tab-panel" style="display:none;">
    <section class="fin-table-card">
        <h2>Understanding Your Host Earnings &amp; Accounting</h2>
        <p style="margin:0 0 1.5rem; font-size:0.86rem; color:var(--vn-ink-muted, rgb(var(--muted)));">
            Everything you need to know about ticket revenue, platform fee policies, escrow security, and withdrawals.
        </p>

        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(260px, 1fr)); gap:1.25rem;">
            <!-- 1. Standard Payouts -->
            <div style="background:rgba(16, 185, 129, 0.04); border:1px solid rgba(16, 185, 129, 0.2); border-radius:16px; padding:1.25rem;">
                <div style="font-size:1.6rem; margin-bottom:0.5rem;">💸</div>
                <h3 style="margin:0 0 0.4rem; font-size:1.05rem; font-weight:800; color:#059669;">1. Standard Event Payouts</h3>
                <p style="margin:0; font-size:0.86rem; line-height:1.55; color:var(--vn-ink, rgb(var(--text)));">
                    Once your event concludes, <strong>100% of your remaining net ticket sales</strong> move from Escrow Custody into your <strong>Available Balance</strong>. You can withdraw directly to your verified bank account at any time.
                </p>
            </div>

            <!-- 2. Auto-Requested Payouts -->
            <div style="background:rgba(59, 130, 246, 0.04); border:1px solid rgba(59, 130, 246, 0.2); border-radius:16px; padding:1.25rem;">
                <div style="font-size:1.6rem; margin-bottom:0.5rem;">🤖</div>
                <h3 style="margin:0 0 0.4rem; font-size:1.05rem; font-weight:800; color:#2563eb;">2. Automatic Payout Generation</h3>
                <p style="margin:0; font-size:0.86rem; line-height:1.55; color:var(--vn-ink, rgb(var(--text)));">
                    If an event concludes and you haven't manually requested a payout, our automated service <strong>queues an auto-payout request</strong> on your behalf so you receive your event earnings without having to remember.
                </p>
            </div>

            <!-- 3. Escrow Early Release -->
            <div style="background:rgba(251, 146, 60, 0.04); border:1px solid rgba(251, 146, 60, 0.2); border-radius:16px; padding:1.25rem;">
                <div style="font-size:1.6rem; margin-bottom:0.5rem;">⚡</div>
                <h3 style="margin:0 0 0.4rem; font-size:1.05rem; font-weight:800; color:#ea580c;">3. Early Escrow Release</h3>
                <p style="margin:0; font-size:0.86rem; line-height:1.55; color:var(--vn-ink, rgb(var(--text)));">
                    Need upfront capital to pay for venue deposits, equipment hire, or artist bookings before the event date? You can request an <strong>early escrow withdrawal</strong> on active events, subject to refund liability terms.
                </p>
            </div>

            <!-- 4. Pure Ticket Sold Price Payout Policy -->
            <div style="background:rgba(139, 92, 246, 0.04); border:1px solid rgba(139, 92, 246, 0.2); border-radius:16px; padding:1.25rem;">
                <div style="font-size:1.6rem; margin-bottom:0.5rem;">🏷️</div>
                <h3 style="margin:0 0 0.4rem; font-size:1.05rem; font-weight:800; color:#7c3aed;">4. Pure Ticket Sold Price Policy</h3>
                <p style="margin:0; font-size:0.86rem; line-height:1.55; color:var(--vn-ink, rgb(var(--text)));">
                    Payout and escrow withdraw amounts are calculated strictly from the <strong>actual ticket sold price</strong>:
                    <br>• <strong>Buyer Paid Fee (`pass_fee = 1`):</strong> Attendee pays the 5% platform fee and convenience charge at checkout. You receive 100% of your ticket sold price. Platform charges are never added to or deducted from your payout.
                    <br>• <strong>Host Absorbed Fee (`pass_fee = 0`):</strong> 5% platform fee is deducted from your ticket price; you receive 95% net share.
                    <br>• <strong>Direct Host-Issued Passes:</strong> Complimentary or gate cash tickets are handled offline directly by you and excluded from platform bank payout transfers.
                </p>
            </div>
        </div>

        <!-- Mathematical Balance Guarantee -->
        <div style="margin-top:1.75rem; background:var(--vn-parchment-warm, rgba(var(--muted),0.06)); border:1.5px solid var(--vn-border, rgba(var(--muted),0.18)); border-radius:16px; padding:1.25rem 1.5rem;">
            <h4 style="margin:0 0 0.45rem; font-size:0.95rem; font-weight:800; color:var(--vn-ink, rgb(var(--text)));">
                ⚖️ The Reconciled Balance Formula (Accounting Integrity)
            </h4>
            <p style="margin:0 0 0.75rem; font-size:0.85rem; color:var(--vn-ink-muted, rgb(var(--muted)));">
                Your total host net earnings always exactly equal your funds across all settlement stages:
            </p>
            <div style="background:var(--vn-parchment-card, #fff); border:1px solid var(--vn-border, rgba(var(--muted),0.2)); border-radius:10px; padding:0.75rem 1rem; font-family:monospace; font-size:0.88rem; font-weight:700; color:var(--vn-forest, #0B7366); overflow-x:auto;">
                Total Host Net Share = Disbursed (Bank) + Available (Completed) + Escrow (Upcoming) + Pending (In Review)
            </div>
        </div>
    </section>
</div>

<!-- Escrow Agreement Modal -->
<div id="escrow-modal-backdrop" style="display:none; position:fixed; inset:0; z-index:9999; background:rgba(0,0,0,0.6); backdrop-filter:blur(8px); -webkit-backdrop-filter:blur(8px); animation: escrowFadeIn 0.2s ease; overflow-y:auto; -webkit-overflow-scrolling:touch; justify-content:center; align-items:center; padding:1rem;">
    <div style="width:100%; max-width:min(92vw, 520px); max-height:calc(100dvh - 2rem); overflow-y:auto; -webkit-overflow-scrolling:touch; background:var(--vn-parchment-card, rgb(var(--card))); border-radius:22px; box-shadow:0 25px 60px rgba(0,0,0,0.3); border:1.5px solid var(--vn-border, rgba(var(--muted), 0.2)); animation: escrowSlideIn 0.25s ease; margin:auto; box-sizing:border-box;">
        <!-- Modal Header -->
        <div style="padding:1.5rem 1.75rem 0.5rem; display:flex; justify-content:space-between; align-items:flex-start;">
            <div>
                <h3 style="margin:0; font-size:1.2rem; font-weight:800; color:var(--vn-ink, rgb(var(--text)));">⚡ Escrow Funds Release Agreement</h3>
                <p style="margin:0.25rem 0 0; font-size:0.82rem; color:var(--vn-ink-muted, rgb(var(--muted)));">Please review and accept the terms below</p>
            </div>
            <button type="button" id="escrow-modal-close" style="background:none; border:none; font-size:1.5rem; cursor:pointer; color:var(--vn-ink-muted, rgb(var(--muted))); padding:0 0.35rem; line-height:1;" aria-label="Close">&times;</button>
        </div>

        <!-- Modal Body: Key Terms -->
        <div style="padding:1.25rem 1.75rem 1.5rem; font-size:0.88rem; line-height:1.6; color:var(--vn-ink, rgb(var(--text)));">
            <div style="background:rgba(251, 146, 60, 0.08); border:1px solid rgba(251, 146, 60, 0.25); border-radius:12px; padding:0.85rem 1rem; margin-bottom:1rem;">
                <p style="margin:0; font-size:0.82rem; color:#ea580c; font-weight:600;">
                    ⚠️ <strong>Important Notice:</strong> Early escrow releases allow you to withdraw funds from upcoming events to assist with upfront logistics and planning.
                </p>
            </div>

            <h4 style="margin:0 0 0.5rem; font-size:0.9rem; font-weight:800;">Host Obligations &amp; Conditions:</h4>
            <ul style="margin:0 0 1rem; padding-left:1.25rem; font-size:0.84rem; color:var(--vn-ink-muted, rgb(var(--muted)));">
                <li style="margin-bottom:0.35rem;">You agree to host the event as described and fulfill all commitments to ticket holders.</li>
                <li style="margin-bottom:0.35rem;">If the event is <strong>cancelled, postponed indefinitely, or significantly altered</strong>, you are personally liable to refund all disbursed funds.</li>
                <li style="margin-bottom:0.35rem;">Eventlyy reserves the right to recover disbursed funds from your linked bank account or future event payouts.</li>
                <li>Admin approval is required for all escrow disbursements before funds are sent.</li>
            </ul>

            <!-- Acceptance Checkbox -->
            <form method="POST" action="{{ route('host/withdraw') }}" id="escrow-withdrawal-form">
                <?= csrf_field() ?>
                <input type="hidden" name="withdraw_type" value="escrow">
                <input type="hidden" name="event_id" id="escrow-event-id" value="">

                <?php if (($layawayPendingRevenue ?? 0) > 0): ?>
                <div style="background:rgba(251, 191, 36, 0.08); border:1px solid rgba(251, 191, 36, 0.25); border-radius:10px; padding:0.75rem 1rem; margin-bottom:1rem; font-size:0.82rem; color:#92400e; display:flex; align-items:flex-start; gap:0.5rem;">
                    <span>⏳</span>
                    <span><strong><?= format_price($layawayPendingRevenue) ?></strong> from <?= (int)($layawayPendingCount ?? 0) ?> layaway plan<?= (int)($layawayPendingCount ?? 0) !== 1 ? 's' : '' ?> is <strong>not included</strong> in your withdrawable escrow. These deposits are locked until attendees complete full payment.</span>
                </div>
                <?php endif; ?>

                <label style="display:flex; align-items:flex-start; gap:0.65rem; cursor:pointer; font-size:0.84rem; font-weight:600; user-select:none; margin-bottom:1.25rem; padding:0.75rem; border-radius:12px; background:var(--vn-parchment-warm, rgba(var(--muted),0.06)); border:1px solid var(--vn-border, rgba(var(--muted),0.15));">
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
    var cards = document.querySelectorAll('#events-mobile-stream .fin-item-card');
    cards.forEach(function(card) {
        var text = card.getAttribute('data-search') || '';
        card.style.display = text.indexOf(query) !== -1 ? '' : 'none';
    });
}

function filterTicketsTable() {
    var query = (document.getElementById('ticket-search-input').value || '').toLowerCase().trim();
    var rows = document.querySelectorAll('#all-tickets-table tbody tr');
    rows.forEach(function(row) {
        var text = (row.getAttribute('data-search') || row.textContent).toLowerCase();
        row.style.display = text.indexOf(query) !== -1 ? '' : 'none';
    });
    var cards = document.querySelectorAll('#tickets-mobile-stream .ticket-card');
    cards.forEach(function(card) {
        var text = (card.getAttribute('data-search') || card.textContent).toLowerCase();
        card.style.display = text.indexOf(query) !== -1 ? '' : 'none';
    });
}

function filterMerchTable() {
    var query = (document.getElementById('merch-search-input').value || '').toLowerCase().trim();
    var rows = document.querySelectorAll('#all-merch-table tbody tr');
    rows.forEach(function(row) {
        var text = (row.getAttribute('data-search') || row.textContent).toLowerCase();
        row.style.display = text.indexOf(query) !== -1 ? '' : 'none';
    });
    var cards = document.querySelectorAll('#merch-mobile-stream .merch-card');
    cards.forEach(function(card) {
        var text = (card.getAttribute('data-search') || card.textContent).toLowerCase();
        card.style.display = text.indexOf(query) !== -1 ? '' : 'none';
    });
}

function filterPayoutsTable() {
    var query = (document.getElementById('payout-search-input').value || '').toLowerCase().trim();
    var rows = document.querySelectorAll('.payout-history-row');
    rows.forEach(function(row) {
        var text = (row.getAttribute('data-search') || row.textContent).toLowerCase();
        row.style.display = text.indexOf(query) !== -1 ? '' : 'none';
    });
    var cards = document.querySelectorAll('#payouts-mobile-stream .payout-card');
    cards.forEach(function(card) {
        var text = (card.getAttribute('data-search') || card.textContent).toLowerCase();
        card.style.display = text.indexOf(query) !== -1 ? '' : 'none';
    });
}

document.addEventListener('DOMContentLoaded', function () {
    const escrowBtn = document.getElementById('financials-escrow-btn');
    const modalBackdrop = document.getElementById('escrow-modal-backdrop');
    const closeBtn = document.getElementById('escrow-modal-close');
    const cancelBtn = document.getElementById('escrow-modal-cancel');
    const checkbox = document.getElementById('escrow-agree-checkbox');
    const confirmBtn = document.getElementById('escrow-confirm-btn');
    const eventIdInput = document.getElementById('escrow-event-id');

    function openEscrowModal(eventId, eventName) {
        if (eventIdInput) eventIdInput.value = eventId || '';
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
        if (eventIdInput) eventIdInput.value = '';
    }

    // Global escrow button (all upcoming events)
    if (escrowBtn) escrowBtn.addEventListener('click', function() { openEscrowModal('', ''); });

    // Per-event escrow buttons in the table
    document.querySelectorAll('.per-event-escrow-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            openEscrowModal(this.getAttribute('data-event-id'), this.getAttribute('data-event-name'));
        });
    });

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
