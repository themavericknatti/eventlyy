<?php

/**
 * Host Executive Dashboard
 *
 * Streamlined, high-performance host management portal with segmented tab navigation,
 * real-time sales analytics, live & upcoming event spotlight, ticket tier insights,
 * orders stream, and payout settlements.
 *
 * @package Evently
 * @subpackage Views/Host
 * @author Celio Natti
 * @version 3.0.0
 */

$siteName = site_setting('site_title', 'Eventlyy');

// Cash Flow Distribution Math
$totalTracked = ($disbursedPayouts ?? 0) + ($availableBalance ?? 0) + ($escrowRevenue ?? 0) + ($pendingPayouts ?? 0);
$disbursedPct = $totalTracked > 0 ? round((($disbursedPayouts ?? 0) / $totalTracked) * 100, 1) : 0;
$availablePct = $totalTracked > 0 ? round((($availableBalance ?? 0) / $totalTracked) * 100, 1) : 0;
$escrowPct = $totalTracked > 0 ? round((($escrowRevenue ?? 0) / $totalTracked) * 100, 1) : 0;
$pendingPct = $totalTracked > 0 ? round((($pendingPayouts ?? 0) / $totalTracked) * 100, 1) : 0;

// Monthly chart calculations
$monthlyTicketsSum = 0;
$monthlyGrossSum = 0;
$monthlyNetSum = 0;
$maxMonthlyRev = 1;
if (!empty($monthlyTrends)) {
    foreach ($monthlyTrends as $mt) {
        $monthlyTicketsSum += (int)($mt['tickets'] ?? 0);
        $monthlyGrossSum += (float)($mt['gross'] ?? 0);
        $monthlyNetSum += (float)($mt['net'] ?? 0);
        if (($mt['gross'] ?? 0) > $maxMonthlyRev) {
            $maxMonthlyRev = (float)$mt['gross'];
        }
    }
}

$maxTierRev = 1;
if (!empty($tierBreakdown)) {
    foreach ($tierBreakdown as $tb) {
        if (($tb['total_revenue'] ?? 0) > $maxTierRev) {
            $maxTierRev = (float)$tb['total_revenue'];
        }
    }
}

// Active Tab determination from query
$activeTab = $_GET['tab'] ?? 'overview';
$validTabs = ['overview', 'events', 'analytics', 'orders', 'payouts'];
if (!in_array($activeTab, $validTabs)) {
    $activeTab = 'overview';
}
?>

@extends('layouts/host')

@section('title')
Host Dashboard — Manage Your <?= e($siteName) ?> Events
@endsection

@section('content')
<style>
/* ════════════════════════════════════════════════════════════════
   HOST EXECUTIVE DASHBOARD — COMPLETE VINTAGE & RESPONSIVE STYLES
   ════════════════════════════════════════════════════════════════ */

/* ── 1. Hero Section Styling ── */
.admin-dashboard-hero {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1.5rem;
    padding: 1.75rem 2rem;
    border-radius: 22px;
    background: var(--vn-parchment-card) !important;
    border: 1.5px solid var(--vn-border) !important;
    box-shadow: var(--vn-shadow-elevated) !important;
    position: relative;
    overflow: hidden;
    margin-bottom: 1.5rem;
}

.hero-content-wrap {
    flex: 1 1 340px;
    min-width: 0;
}

.hero-badge-row {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex-wrap: wrap;
    margin-bottom: 0.6rem;
}

.super-admin-crown-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    background: linear-gradient(135deg, var(--vn-brass) 0%, var(--vn-forest) 100%);
    color: #fff;
    font-size: 0.76rem;
    font-weight: 800;
    padding: 0.3rem 0.75rem;
    border-radius: 20px;
    letter-spacing: 0.02em;
    box-shadow: 0 2px 10px rgba(184, 134, 11, 0.35);
}

.hero-status-pill {
    background: var(--vn-parchment-warm);
    color: var(--vn-ink);
    font-size: 0.72rem;
    font-weight: 700;
    padding: 0.25rem 0.6rem;
    border-radius: 20px;
    border: 1px solid var(--vn-border);
}

.hero-clock-pill {
    font-size: 0.75rem;
    color: var(--vn-ink-muted);
    font-weight: 600;
    margin-left: auto;
}

.hero-title {
    margin: 0.2rem 0 0.4rem;
    font-family: var(--vn-font-serif) !important;
    font-size: clamp(1.4rem, 2.5vw, 1.95rem);
    font-weight: normal;
    letter-spacing: -0.01em;
    color: var(--vn-ink) !important;
}

.hero-subtitle {
    margin: 0;
    font-size: 0.92rem;
    color: var(--vn-ink-muted);
    line-height: 1.5;
    max-width: 680px;
}

.hero-actions-wrap {
    display: flex;
    gap: 0.65rem;
    align-items: center;
    flex-wrap: wrap;
}

.hero-action-btn {
    padding: 0.65rem 1.15rem;
    font-size: 0.88rem;
    border-radius: 12px;
    font-weight: 700;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    text-align: center;
    gap: 0.45rem;
    min-height: 42px;
    box-sizing: border-box;
    transition: all 0.2s ease;
}

/* ── 2. Card Containers & Sections ── */
.form-card {
    background: var(--vn-parchment-card) !important;
    border: 1.5px solid var(--vn-border) !important;
    border-radius: var(--vn-radius-lg) !important;
    box-shadow: var(--vn-shadow-elevated) !important;
    color: var(--vn-ink) !important;
    display: block !important;
    width: 100% !important;
    max-width: 100% !important;
    box-sizing: border-box !important;
}

.edb-section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1rem;
    margin-bottom: 1.25rem;
}

.edb-section-title {
    font-family: var(--vn-font-serif) !important;
    font-size: 1.25rem;
    font-weight: normal;
    color: var(--vn-ink);
    margin: 0;
}

.edb-section-subtext {
    font-size: 0.82rem;
    color: var(--vn-ink-muted);
    margin: 0.2rem 0 0;
}

.edb-search-wrap {
    position: relative;
    display: inline-flex;
    align-items: center;
}

.edb-search-icon {
    position: absolute;
    left: 0.75rem;
    font-size: 0.82rem;
    color: var(--vn-ink-muted);
    pointer-events: none;
}

.edb-search-input {
    background: var(--vn-parchment-warm);
    border: 1.5px solid var(--vn-border);
    border-radius: 10px;
    padding: 0.5rem 0.85rem 0.5rem 2.2rem;
    font-size: 0.85rem;
    color: var(--vn-ink);
    outline: none;
    transition: border-color 0.2s, box-shadow 0.2s;
    min-width: 220px;
}

.edb-search-input:focus {
    border-color: var(--vn-brass);
    box-shadow: 0 0 0 3px var(--vn-brass-glow);
}

/* ── 3. Spotlight Card ── */
.host-spotlight-card {
    margin-bottom: 2rem;
    padding: 1.5rem 1.75rem;
    background: var(--vn-parchment-card) !important;
    border: 1.5px solid var(--vn-border) !important;
    border-radius: 20px;
    box-shadow: var(--vn-shadow-elevated) !important;
}

.host-spotlight-wrap {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1.5rem;
}

.host-spotlight-info {
    display: flex;
    gap: 1.25rem;
    align-items: center;
    flex: 1;
    min-width: 280px;
}

.host-spotlight-thumb {
    position: relative;
    width: 96px;
    height: 96px;
    border-radius: 16px;
    overflow: hidden;
    flex-shrink: 0;
    box-shadow: 0 6px 18px rgba(0, 0, 0, 0.12);
    border: 1.5px solid var(--vn-border);
}

.host-spotlight-thumb img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.host-spotlight-metrics {
    display: flex;
    gap: 1rem;
    align-items: center;
    flex-wrap: wrap;
}

.host-meter-card {
    background: var(--vn-parchment-warm) !important;
    border: 1.5px solid var(--vn-border) !important;
    border-radius: 14px;
    padding: 0.85rem 1.15rem;
    min-width: 150px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);
}

.host-spotlight-actions {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    min-width: 130px;
}

/* ── 4. Primary Financial Summary Cards Grid ── */
.host-summary-grid {
    display: grid !important;
    grid-template-columns: repeat(4, 1fr) !important;
    gap: 1.15rem;
    margin-bottom: 2rem;
}

.summary-card {
    background: var(--vn-parchment-card) !important;
    border: 1.5px solid var(--vn-border) !important;
    border-radius: 18px;
    padding: 1.35rem 1.45rem;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    box-shadow: var(--vn-shadow-elevated) !important;
    transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
}

.summary-card:hover {
    transform: translateY(-2px);
    border-color: var(--vn-brass) !important;
    box-shadow: var(--vn-shadow-floating) !important;
}

.card-label {
    font-size: 0.78rem;
    font-weight: 700;
    color: var(--vn-ink-muted);
    text-transform: uppercase;
    letter-spacing: 0.04em;
    display: block;
    margin-bottom: 0.35rem;
}

.card-value {
    font-family: var(--vn-font-serif) !important;
    font-size: clamp(1.45rem, 2.2vw, 1.95rem);
    font-weight: 900;
    color: var(--vn-brass) !important;
    margin: 0 0 0.35rem;
    line-height: 1.2;
}

.card-meta {
    font-size: 0.8rem;
    color: var(--vn-ink-muted);
    margin: 0;
}

/* ── 5. Cash Flow Visual Health Bar ── */
.host-cashflow-card {
    margin-bottom: 2rem;
    padding: 1.25rem 1.5rem;
    background: var(--vn-parchment-card) !important;
    border: 1.5px solid var(--vn-border) !important;
    border-radius: 18px;
    box-shadow: var(--vn-shadow-elevated) !important;
}

.host-cashflow-bar {
    display: flex;
    width: 100%;
    height: 12px;
    border-radius: 8px;
    overflow: hidden;
    background: var(--vn-border);
    margin-bottom: 0.85rem;
}

.host-cashflow-legend {
    display: flex;
    gap: 1.25rem;
    flex-wrap: wrap;
    font-size: 0.8rem;
}

/* ── 6. Category Filter Tabs ── */
.dashboard-tabs {
    display: flex;
    gap: 0.5rem;
    overflow-x: auto;
    white-space: nowrap;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: none;
    padding-bottom: 0.35rem;
    margin-bottom: 1.35rem;
    border-bottom: 1.5px solid var(--vn-border);
}

.dashboard-tabs::-webkit-scrollbar {
    display: none;
}

.dashboard-tabs .tab-btn {
    padding: 0.55rem 1.15rem;
    font-family: var(--vn-font-sans) !important;
    font-size: 0.88rem;
    font-weight: 700;
    border-radius: 12px;
    border: 1px solid var(--vn-border);
    background: var(--vn-parchment-warm);
    color: var(--vn-ink);
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    transition: all 0.2s ease;
    white-space: nowrap;
    flex-shrink: 0;
}

.dashboard-tabs .tab-btn:hover {
    background: var(--vn-parchment-card);
    border-color: var(--vn-brass);
    color: var(--vn-brass);
}

.dashboard-tabs .tab-btn.active {
    background: var(--vn-brass) !important;
    color: #ffffff !important;
    border-color: var(--vn-brass-light) !important;
    box-shadow: 0 4px 14px var(--vn-brass-glow) !important;
}

.tab-badge {
    background: rgba(0, 0, 0, 0.12);
    color: inherit;
    font-size: 0.72rem;
    font-weight: 800;
    padding: 2px 7px;
    border-radius: 20px;
}

.tab-btn.active .tab-badge {
    background: rgba(255, 255, 255, 0.25);
    color: #ffffff;
}

/* ── 7. Events Grid & Cards ── */
.host-events-grid {
    display: grid !important;
    grid-template-columns: repeat(auto-fill, minmax(290px, 1fr)) !important;
    gap: 1.35rem;
}

.host-event-card {
    background: var(--vn-parchment-card) !important;
    border: 1.5px solid var(--vn-border) !important;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: var(--vn-shadow-elevated) !important;
    display: flex;
    flex-direction: column;
    transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
}

.host-event-card:hover {
    transform: translateY(-3px);
    box-shadow: var(--vn-shadow-floating) !important;
    border-color: var(--vn-brass) !important;
}

.event-card-image {
    position: relative;
    width: 100%;
    height: 160px;
    overflow: hidden;
    background: #0f172a;
}

.event-card-content {
    padding: 1.15rem;
    display: flex;
    flex-direction: column;
    flex: 1;
}

.event-card-content h3 {
    margin: 0 0 0.4rem;
    font-family: var(--vn-font-serif) !important;
    font-size: 1.15rem;
    font-weight: normal;
    color: var(--vn-ink) !important;
    line-height: 1.3;
}

.event-card-meta {
    font-size: 0.8rem;
    color: var(--vn-ink-muted);
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
    margin: 0 0 0.85rem;
}

.event-stats-mini {
    display: flex;
    justify-content: space-between;
    background: var(--vn-parchment-warm);
    border: 1px solid var(--vn-border);
    border-radius: 10px;
    padding: 0.6rem 0.85rem;
    margin-bottom: 0.75rem;
}

.stat-mini .label {
    font-size: 0.72rem;
    color: var(--vn-ink-muted);
    text-transform: uppercase;
    font-weight: 700;
    display: block;
}

.stat-mini .value {
    font-size: 0.92rem;
    font-weight: 800;
    font-family: var(--vn-font-serif);
    color: var(--vn-ink);
}

.progress-bar {
    width: 100%;
    height: 6px;
    background: var(--vn-border);
    border-radius: 3px;
    overflow: hidden;
    margin-bottom: 1rem;
}

.progress-fill {
    height: 100%;
    background: var(--vn-forest);
    border-radius: 3px;
}

/* Card Action Buttons */
.card-actions {
    display: flex;
    gap: 0.45rem;
    flex-wrap: wrap;
    margin-top: auto;
    padding-top: 0.75rem;
    border-top: 1px solid var(--vn-border);
}

.host-action-btn {
    padding: 0.45rem 0.75rem;
    font-size: 0.8rem;
    font-weight: 700;
    border-radius: 9px;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.35rem;
    border: 1.5px solid var(--vn-border);
    background: var(--vn-parchment-warm);
    color: var(--vn-ink);
    cursor: pointer;
    transition: all 0.2s ease;
    flex: 1;
    min-width: 65px;
    white-space: nowrap;
}

.host-action-btn:hover {
    background: var(--vn-parchment-card);
    border-color: var(--vn-brass);
    color: var(--vn-brass);
}

.host-action-btn.manage-btn {
    background: var(--vn-brass-glow);
    color: var(--vn-brass);
    border-color: var(--vn-brass);
}

.host-action-btn.verify-btn {
    background: var(--vn-forest-tint);
    color: var(--vn-forest);
    border-color: var(--vn-forest);
}

.host-action-btn.delete-btn {
    background: rgba(239, 68, 68, 0.08);
    color: #ef4444;
    border-color: rgba(239, 68, 68, 0.22);
}

.host-action-btn.delete-btn:hover {
    background: rgba(239, 68, 68, 0.18);
    border-color: rgba(239, 68, 68, 0.35);
}

/* ── 8. Visual Analytics Grid & Financial Banner ── */
.host-analytics-grid {
    display: grid !important;
    grid-template-columns: repeat(2, 1fr) !important;
    gap: 1.25rem;
    margin-bottom: 2rem;
}

.host-financials-banner {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1.25rem;
    padding: 1.5rem 1.75rem;
    background: linear-gradient(135deg, var(--vn-parchment-warm) 0%, var(--vn-parchment-card) 100%) !important;
    border: 1.5px solid var(--vn-border) !important;
    border-radius: var(--vn-radius-lg) !important;
    box-shadow: var(--vn-shadow-elevated) !important;
}

/* ── 9. Data Tables (Orders & Payouts) ── */
.admin-table-wrap,
.host-orders-table-wrap,
.host-payouts-table-wrap {
    background: var(--vn-parchment-card);
    border: 1.5px solid var(--vn-border);
    border-radius: 16px;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    box-shadow: var(--vn-shadow-elevated);
}

.admin-table {
    width: 100%;
    border-collapse: collapse;
    font-family: var(--vn-font-sans);
    font-size: 0.85rem;
    color: var(--vn-ink);
}

.admin-table th {
    background: var(--vn-parchment-warm) !important;
    color: var(--vn-ink-soft) !important;
    font-weight: 700 !important;
    text-transform: uppercase !important;
    letter-spacing: 0.04em !important;
    font-size: 0.78rem !important;
    border-bottom: 2px solid var(--vn-border-dark) !important;
    padding: 0.85rem 1rem !important;
    text-align: left;
    white-space: nowrap;
}

.admin-table td {
    padding: 0.85rem 1rem !important;
    border-bottom: 1px solid var(--vn-border) !important;
    color: var(--vn-ink) !important;
    vertical-align: middle;
}

.admin-table tr:hover td {
    background: var(--vn-parchment-subtle) !important;
}

.admin-table tr:last-child td {
    border-bottom: none !important;
}

/* ── 10. Mobile Cards (Order & Payout) ── */
.host-orders-cards-mobile,
.host-payouts-cards-mobile {
    display: none;
    flex-direction: column;
    gap: 0.85rem;
}

.host-order-mobile-card,
.host-payout-mobile-card {
    background: var(--vn-parchment-card) !important;
    border: 1.5px solid var(--vn-border) !important;
    border-radius: 14px;
    padding: 1rem;
    box-shadow: var(--vn-shadow-elevated) !important;
}

/* ── 11. Responsive Media Queries ── */
@media (max-width: 900px) and (min-width: 601px) {
    .host-summary-grid {
        grid-template-columns: repeat(2, 1fr) !important;
    }
    .host-events-grid {
        grid-template-columns: repeat(2, 1fr) !important;
    }
    .host-analytics-grid {
        grid-template-columns: 1fr !important;
    }
    .host-spotlight-wrap {
        flex-direction: column;
        align-items: stretch;
    }
    .host-spotlight-metrics {
        justify-content: space-between;
    }
    .host-meter-card {
        flex: 1;
    }
    .host-spotlight-actions {
        flex-direction: row;
        width: 100%;
    }
    .host-spotlight-actions a {
        flex: 1;
        padding: 0.6rem 1rem !important;
    }
}

@media (max-width: 768px) {
    .host-orders-table-wrap,
    .host-payouts-table-wrap {
        display: none !important;
    }
    .host-orders-cards-mobile,
    .host-payouts-cards-mobile {
        display: flex !important;
    }
    .hero-actions-wrap {
        width: 100%;
        display: grid;
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 600px) {
    .admin-dashboard-hero {
        padding: 1.25rem 1.15rem;
        border-radius: 18px;
    }
    .hero-title {
        font-size: 1.3rem !important;
    }
    .hero-clock-pill {
        margin-left: 0;
        width: 100%;
    }
    .hero-actions-wrap {
        grid-template-columns: 1fr;
        width: 100%;
    }
    .hero-action-btn {
        width: 100%;
        justify-content: center;
        padding: 0.65rem 0.85rem;
    }
    .host-spotlight-card {
        padding: 1.15rem;
    }
    .host-spotlight-info {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.85rem;
        min-width: 0 !important;
        width: 100% !important;
    }
    .host-spotlight-thumb {
        width: 100%;
        height: 140px;
    }
    .host-spotlight-metrics {
        width: 100%;
        flex-direction: column;
        gap: 0.75rem;
    }
    .host-meter-card {
        width: 100%;
        box-sizing: border-box;
    }
    .host-spotlight-actions {
        width: 100%;
        min-width: 0 !important;
        flex-direction: row;
    }
    .host-spotlight-actions a {
        flex: 1;
        padding: 0.65rem 1rem !important;
    }
    .host-summary-grid {
        grid-template-columns: 1fr !important;
        gap: 0.85rem;
    }
    .host-analytics-grid {
        grid-template-columns: 1fr !important;
    }
    .host-events-grid {
        grid-template-columns: 1fr !important;
    }
    .card-actions {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
    }
    .card-actions .host-action-btn {
        width: 100%;
        padding: 0.55rem 0.5rem;
    }
    .edb-search-wrap,
    .edb-search-input {
        width: 100%;
    }
}

@media (max-width: 380px) {
    .host-spotlight-actions {
        flex-direction: column !important;
    }
    .card-actions {
        grid-template-columns: 1fr !important;
    }
}
</style>

<!-- ════════════════════════════════════════════════════════════════
     1. HOST HERO BANNER & QUICK ACTIONS
     ════════════════════════════════════════════════════════════════ -->
<section class="admin-dashboard-hero" style="margin-bottom: 1.25rem;">
    <div class="hero-content-wrap">
        <div class="hero-badge-row">
            <span class="super-admin-crown-badge" style="background: linear-gradient(135deg, var(--vn-brass) 0%, var(--vn-forest) 100%);">
                <span>🎪</span>
                <span>Host Operations Hub</span>
            </span>
            <?php if (!empty($isAdmin)): ?>
                <span class="hero-status-pill" style="color:var(--vn-brass); font-weight:700;">Admin View</span>
            <?php endif; ?>
            <span class="hero-clock-pill" id="host-live-clock">🕒 <?= date('M d, Y • h:i A') ?></span>
        </div>

        <h1 class="hero-title">
            <?php if (!empty($isAdmin)): ?>
                All Platform Events (Administrator Mode)
            <?php else: ?>
                Welcome back, <?= e(user_first_name()) ?>! 👋
            <?php endif; ?>
        </h1>
        <p class="hero-subtitle">
            <?php if (!empty($isAdmin)): ?>
                Viewing as <strong>Admin</strong>. Manage events across the platform, monitor ticket sales velocity, and review host earnings.
            <?php else: ?>
                Monitor your ticket sales velocity, check-in gate attendance, and event payouts in real time.
            <?php endif; ?>
        </p>
    </div>

    <!-- Quick Actions -->
    <div class="hero-actions-wrap">
        <a href="{{ route('host/event/create') }}" class="admin-action-btn primary hero-action-btn">
            <span>➕</span>
            <span>Create Event</span>
        </a>
        <a href="{{ route('host/event/ticket-verification') }}" class="admin-action-btn hero-action-btn" style="background: var(--vn-parchment-warm); border: 1.5px solid var(--vn-border); color: var(--vn-ink);">
            <span>🎟️</span>
            <span>Verify Tickets</span>
        </a>
        <a href="{{ route('host/financials') }}" class="admin-action-btn hero-action-btn" style="background: var(--vn-parchment-warm); border: 1.5px solid var(--vn-border); color: var(--vn-ink);">
            <span>💳</span>
            <span>Financials</span>
        </a>
    </div>
</section>

<!-- ════════════════════════════════════════════════════════════════
     2. EXECUTIVE SEGMENTED NAVIGATION TABS
     ════════════════════════════════════════════════════════════════ -->
<div class="edb-tabs-container">
    <nav class="edb-tabs-bar" role="tablist" aria-label="Host Dashboard Views">
        <button type="button" class="edb-tab-btn <?= $activeTab === 'overview' ? 'active' : '' ?>" data-tab-btn="overview" role="tab" aria-selected="<?= $activeTab === 'overview' ? 'true' : 'false' ?>">
            <span>📊</span>
            <span>Overview &amp; Spotlight</span>
        </button>

        <button type="button" class="edb-tab-btn <?= $activeTab === 'events' ? 'active' : '' ?>" data-tab-btn="events" role="tab" aria-selected="<?= $activeTab === 'events' ? 'true' : 'false' ?>">
            <span>🎪</span>
            <span>My Events</span>
            <span class="edb-tab-badge"><?= (int)($totalEventsCount ?? count($events ?? [])) ?></span>
        </button>

        <button type="button" class="edb-tab-btn <?= $activeTab === 'analytics' ? 'active' : '' ?>" data-tab-btn="analytics" role="tab" aria-selected="<?= $activeTab === 'analytics' ? 'true' : 'false' ?>">
            <span>📈</span>
            <span>Sales Analytics</span>
        </button>

        <button type="button" class="edb-tab-btn <?= $activeTab === 'orders' ? 'active' : '' ?>" data-tab-btn="orders" role="tab" aria-selected="<?= $activeTab === 'orders' ? 'true' : 'false' ?>">
            <span>🎟️</span>
            <span>Recent Orders</span>
            <?php if (!empty($recentOrders)): ?>
                <span class="edb-tab-badge"><?= count($recentOrders) ?></span>
            <?php endif; ?>
        </button>

        <button type="button" class="edb-tab-btn <?= $activeTab === 'payouts' ? 'active' : '' ?>" data-tab-btn="payouts" role="tab" aria-selected="<?= $activeTab === 'payouts' ? 'true' : 'false' ?>">
            <span>💳</span>
            <span>Payouts &amp; Escrow</span>
            <?php if (($availableBalance ?? 0) > 0 || ($availableEscrowBalance ?? 0) > 0): ?>
                <span class="edb-tab-badge" style="background:#10b981; color:#fff;">Funds Ready</span>
            <?php endif; ?>
        </button>
    </nav>
</div>

<!-- ════════════════════════════════════════════════════════════════
     TAB 1: HOST OVERVIEW & SPOTLIGHT
     ════════════════════════════════════════════════════════════════ -->
<div id="tab-overview" class="edb-tab-pane <?= $activeTab === 'overview' ? '' : 'hidden' ?>" data-tab-content="overview" role="tabpanel">

    <!-- Brand New Host Welcome & Onboarding Card (if 0 events) -->
    <?php if (($totalEventsCount ?? 0) === 0): ?>
    <section class="form-card" style="margin-bottom:2rem; padding:1.75rem; background:linear-gradient(135deg, var(--vn-brass-glow) 0%, var(--vn-parchment-card) 100%); border:1.5px dashed var(--vn-brass); border-radius:var(--vn-radius-lg);">
        <div style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:1.25rem; margin-bottom:1.25rem;">
            <div style="max-width:620px;">
                <div style="display:inline-flex; align-items:center; gap:0.4rem; background:var(--vn-brass-glow); color:var(--vn-brass); font-weight:700; font-size:0.78rem; padding:0.25rem 0.75rem; border-radius:20px; margin-bottom:0.6rem;">
                    🚀 Welcome to the Creator Portal
                </div>
                <h2 style="margin:0 0 0.4rem; font-size:clamp(1.2rem, 2vw, 1.5rem); font-family:var(--vn-font-serif); font-weight:normal; color:var(--vn-ink);">Ready to host your first event?</h2>
                <p style="margin:0; font-size:0.9rem; color:var(--vn-ink-muted); line-height:1.5;">Create customized ticket tiers, accept online payments, track live attendee check-ins with QR codes, and receive direct bank payouts.</p>
            </div>
            <a href="{{ route('host/event/create') }}" class="admin-action-btn primary" style="padding:0.75rem 1.5rem; font-size:0.95rem;">
                <span>➕</span> Create Your First Event
            </a>
        </div>

        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(min(100%, 200px), 1fr)); gap:1rem; margin-top:1.25rem; padding-top:1.25rem; border-top:1px solid var(--vn-border);">
            <div style="display:flex; gap:0.75rem; align-items:flex-start;">
                <div style="width:32px; height:32px; border-radius:8px; background:var(--vn-brass-glow); color:var(--vn-brass); display:flex; align-items:center; justify-content:center; font-weight:800; font-size:0.85rem; flex-shrink:0;">1</div>
                <div>
                    <strong style="font-size:0.88rem; color:var(--vn-ink); display:block; margin-bottom:0.15rem;">Set Up Details</strong>
                    <span style="font-size:0.78rem; color:var(--vn-ink-muted);">Add ticket tiers (VIP, Regular), pricing, dates &amp; venue location.</span>
                </div>
            </div>
            <div style="display:flex; gap:0.75rem; align-items:flex-start;">
                <div style="width:32px; height:32px; border-radius:8px; background:var(--vn-forest-tint); color:var(--vn-forest); display:flex; align-items:center; justify-content:center; font-weight:800; font-size:0.85rem; flex-shrink:0;">2</div>
                <div>
                    <strong style="font-size:0.88rem; color:var(--vn-ink); display:block; margin-bottom:0.15rem;">Share &amp; Sell Tickets</strong>
                    <span style="font-size:0.78rem; color:var(--vn-ink-muted);">Get your unique public event page, custom URL, and referral links.</span>
                </div>
            </div>
            <div style="display:flex; gap:0.75rem; align-items:flex-start;">
                <div style="width:32px; height:32px; border-radius:8px; background:rgba(59, 130, 246, 0.15); color:#2563eb; display:flex; align-items:center; justify-content:center; font-weight:800; font-size:0.85rem; flex-shrink:0;">3</div>
                <div>
                    <strong style="font-size:0.88rem; color:var(--vn-ink); display:block; margin-bottom:0.15rem;">Check-In &amp; Payouts</strong>
                    <span style="font-size:0.78rem; color:var(--vn-ink-muted);">Scan QR passes sub-second at the gate and withdraw directly to bank.</span>
                </div>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Next / Live Event Spotlight Card -->
    <?php if (!empty($nextEvent)): ?>
    <section class="form-card host-spotlight-card" style="margin-bottom:2rem;">
        <div class="host-spotlight-wrap">
            <div class="host-spotlight-info">
                <?php $spotlightImg = !empty($nextEvent['image']) ? $nextEvent['image'] : 'https://images.unsplash.com/photo-1501281668745-f7f57925c3b4?w=800&q=60&auto=format&fit=crop'; ?>
                <div class="host-spotlight-thumb">
                    <img src="<?= e($spotlightImg) ?>" alt="<?= e($nextEvent['name']) ?>" />
                    <?php if (!empty($nextEvent['is_live_now'])): ?>
                        <span style="position:absolute; top:6px; right:6px; background:#ef4444; color:#fff; font-size:0.65rem; font-weight:800; padding:2px 6px; border-radius:4px; text-transform:uppercase;">LIVE</span>
                    <?php endif; ?>
                </div>

                <div style="flex:1; min-width:200px;">
                    <div style="display:flex; align-items:center; gap:0.5rem; flex-wrap:wrap; margin-bottom:0.35rem;">
                        <?php if (!empty($nextEvent['is_live_now'])): ?>
                            <span style="background:rgba(239, 68, 68, 0.15); color:#ef4444; border:1px solid rgba(239, 68, 68, 0.3); font-size:0.75rem; font-weight:800; padding:2px 8px; border-radius:20px; display:inline-flex; align-items:center; gap:4px;">
                                <span style="display:inline-block; width:6px; height:6px; border-radius:50%; background:#ef4444; animation:pulseDot 1.5s infinite;"></span> Live Happening Now
                            </span>
                        <?php else: ?>
                            <span style="background:var(--vn-forest-tint); color:var(--vn-forest); border:1px solid rgba(27, 67, 50, 0.25); font-size:0.75rem; font-weight:700; padding:2px 8px; border-radius:20px;">
                                📅 Next Upcoming Event
                            </span>
                        <?php endif; ?>
                        <span style="font-size:0.8rem; color:var(--vn-ink-muted);">📍 <?= e($nextEvent['location']) ?></span>
                    </div>

                    <h3 style="margin:0 0 0.3rem; font-size:1.25rem; font-family:var(--vn-font-serif); font-weight:normal; color:var(--vn-ink);"><?= e($nextEvent['name']) ?></h3>
                    <p style="margin:0; font-size:0.85rem; color:var(--vn-ink-muted);">
                        🗓️ <?= date('l, M d, Y • g:i A', $nextEvent['timestamp']) ?>
                    </p>
                </div>
            </div>

            <!-- Live Metrics / Gauges -->
            <div class="host-spotlight-metrics">
                <div class="host-meter-card">
                    <div style="display:flex; justify-content:space-between; font-size:0.75rem; font-weight:700; color:var(--vn-ink-muted); margin-bottom:0.25rem;">
                        <span>Ticket Sales</span>
                        <span style="color:var(--vn-ink);"><?= (float)($nextEvent['capacity_pct'] ?? 0) ?>%</span>
                    </div>
                    <div style="font-size:1.1rem; font-weight:800; color:var(--vn-ink);"><?= number_format((float)($nextEvent['sold_count'] ?? 0)) ?> <span style="font-size:0.75rem; font-weight:500; color:var(--vn-ink-muted);">/ <?= number_format((float)($nextEvent['total_capacity'] ?? 0)) ?></span></div>
                    <div style="width:100%; height:5px; background:var(--vn-border); border-radius:3px; overflow:hidden; margin-top:4px;">
                        <div style="width:<?= (float)($nextEvent['capacity_pct'] ?? 0) ?>%; height:100%; background:var(--vn-brass); border-radius:3px;"></div>
                    </div>
                </div>

                <div class="host-meter-card">
                    <span style="font-size:0.75rem; font-weight:700; color:var(--vn-ink-muted); display:block; margin-bottom:0.25rem;">Door Check-Ins</span>
                    <div style="font-size:1.1rem; font-weight:800; color:#10b981;"><?= number_format((float)($nextEvent['checked_in_count'] ?? 0)) ?> <span style="font-size:0.75rem; font-weight:500; color:var(--vn-ink-muted);">verified</span></div>
                    <span style="font-size:0.72rem; color:var(--vn-ink-muted);">Gate scan velocity</span>
                </div>

                <div class="host-meter-card">
                    <span style="font-size:0.75rem; font-weight:700; color:var(--vn-ink-muted); display:block; margin-bottom:0.25rem;">Event Revenue</span>
                    <div style="font-size:1.1rem; font-weight:800; color:var(--vn-brass);"><?= format_price($nextEvent['revenue'] ?? 0) ?></div>
                    <span style="font-size:0.72rem; color:var(--vn-ink-muted);">Total net earnings</span>
                </div>
            </div>

            <!-- Spotlight Actions -->
            <div class="host-spotlight-actions">
                <a href="{{ route('host/event/ticket-verification/' . $nextEvent['id']) }}" class="admin-action-btn primary" style="font-size:0.85rem; padding:0.5rem 1rem;">
                    <span>🎟️</span> Scan Passes
                </a>
                <a href="{{ route('host/event/management/' . $nextEvent['id']) }}" class="admin-action-btn" style="font-size:0.85rem; padding:0.5rem 1rem; background:var(--vn-parchment-warm); border:1.5px solid var(--vn-border);">
                    <span>⚙️</span> Manage
                </a>
                <a href="{{ route('event/' . ($nextEvent['custom_url'] ?: $nextEvent['slug'])) }}" target="_blank" class="admin-action-btn" style="font-size:0.85rem; padding:0.5rem 0.85rem; background:var(--vn-parchment-warm); border:1.5px solid var(--vn-border);" title="Open Public Page">
                    ↗
                </a>
            </div>
        </div>
    </section>
    <?php elseif (!empty($lastCompletedEvent)): ?>
    <!-- Most Recent Completed Event Spotlight -->
    <section class="form-card host-spotlight-card" style="margin-bottom:2rem; background:linear-gradient(135deg, var(--vn-parchment-card) 0%, var(--vn-forest-tint) 100%); border-color:rgba(27, 67, 50, 0.25);">
        <div class="host-spotlight-wrap">
            <div class="host-spotlight-info">
                <?php $spotlightImg = !empty($lastCompletedEvent['image']) ? $lastCompletedEvent['image'] : 'https://images.unsplash.com/photo-1501281668745-f7f57925c3b4?w=800&q=60&auto=format&fit=crop'; ?>
                <div class="host-spotlight-thumb">
                    <img src="<?= e($spotlightImg) ?>" alt="<?= e($lastCompletedEvent['name']) ?>" />
                </div>

                <div style="flex:1; min-width:200px;">
                    <div style="display:flex; align-items:center; gap:0.5rem; flex-wrap:wrap; margin-bottom:0.35rem;">
                        <span style="background:var(--vn-forest-tint); color:var(--vn-forest); border:1px solid rgba(27, 67, 50, 0.25); font-size:0.75rem; font-weight:700; padding:2px 8px; border-radius:20px;">
                            ✓ Latest Completed Event
                        </span>
                        <span style="font-size:0.8rem; color:var(--vn-ink-muted);">📍 <?= e($lastCompletedEvent['location']) ?></span>
                    </div>

                    <h3 style="margin:0 0 0.3rem; font-size:1.25rem; font-family:var(--vn-font-serif); font-weight:normal; color:var(--vn-ink);"><?= e($lastCompletedEvent['name']) ?></h3>
                    <p style="margin:0; font-size:0.85rem; color:var(--vn-ink-muted);">
                        Held on <?= !empty($lastCompletedEvent['timestamp']) ? date('M d, Y', $lastCompletedEvent['timestamp']) : '—' ?> · Generated <strong style="color:var(--vn-brass);"><?= format_price($lastCompletedEvent['net_sales'] ?? ($lastCompletedEvent['revenue'] ?? 0)) ?></strong> net
                    </p>
                </div>
            </div>

            <div class="host-spotlight-metrics">
                <div class="host-meter-card" style="text-align:center;">
                    <span style="font-size:0.75rem; font-weight:700; color:var(--vn-ink-muted); display:block; margin-bottom:0.25rem;">Attendance</span>
                    <div style="font-size:1.1rem; font-weight:800; color:var(--vn-ink);"><?= number_format((float)($lastCompletedEvent['sold_count'] ?? 0)) ?> <span style="font-size:0.75rem; font-weight:500; color:var(--vn-ink-muted);">tickets</span></div>
                </div>

                <div class="host-meter-card" style="text-align:center;">
                    <span style="font-size:0.75rem; font-weight:700; color:var(--vn-ink-muted); display:block; margin-bottom:0.25rem;">Net Earnings</span>
                    <div style="font-size:1.1rem; font-weight:800; color:var(--vn-forest);"><?= format_price($lastCompletedEvent['net_sales'] ?? ($lastCompletedEvent['revenue'] ?? 0)) ?></div>
                </div>
            </div>

            <div class="host-spotlight-actions">
                <a href="{{ route('host/event/management/' . $lastCompletedEvent['id']) }}" class="admin-action-btn primary" style="font-size:0.85rem; padding:0.5rem 1rem;">
                    <span>📊</span> View Summary
                </a>
                <a href="{{ route('event/' . ($lastCompletedEvent['custom_url'] ?: $lastCompletedEvent['slug'])) }}" target="_blank" class="admin-action-btn" style="font-size:0.85rem; padding:0.5rem 0.85rem; background:var(--vn-parchment-warm); border:1.5px solid var(--vn-border);" title="Open Public Page">
                    ↗
                </a>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Primary Financial Summary Cards -->
    <section class="host-summary-grid" style="margin-bottom:2rem;">
        <article class="summary-card">
            <div>
                <span class="card-label">Total Net Earnings</span>
                <h3 id="total-revenue" class="card-value"><?= format_price($totalNetRevenue ?? ($totalRevenue ?? 0)) ?></h3>
            </div>
            <?php if (($totalPlatformFees ?? 0) > 0): ?>
                <div>
                    <p class="card-meta" style="font-size:0.78rem;">Gross sales: <?= format_price($totalRevenue ?? 0) ?></p>
                    <p class="card-meta" style="font-size:0.75rem; color: #ef4444; font-weight: 600;">− <?= format_price($totalPlatformFees ?? 0) ?> platform fee (5%)</p>
                </div>
            <?php else: ?>
                <p class="card-meta">From online ticket sales</p>
            <?php endif; ?>
        </article>

        <article class="summary-card" style="border-color: rgba(16, 185, 129, 0.28); background: var(--vn-forest-tint);">
            <div>
                <span class="card-label" style="color: #10b981;">Available Balance</span>
                <h3 id="available-balance" class="card-value" style="color: #10b981;"><?= format_price($availableBalance ?? 0) ?></h3>
                <p class="card-meta" style="font-size:0.78rem;">From completed events</p>
            </div>
            <form method="POST" action="{{ route('host/withdraw') }}" style="margin-top:0.85rem; width:100%;">
                <?= csrf_field() ?>
                <input type="hidden" name="withdraw_type" value="completed">
                <?php if (($availableBalance ?? 0.00) > 0): ?>
                    <button type="submit" class="admin-action-btn primary" style="width:100%; justify-content:center; padding:0.55rem 1rem; font-size:0.85rem;">
                        💸 Request Payout
                    </button>
                <?php else: ?>
                    <button type="button" class="admin-action-btn" disabled style="width:100%; justify-content:center; padding:0.55rem 1rem; font-size:0.85rem; opacity:0.5; cursor:not-allowed;">
                        No Balance Available
                    </button>
                <?php endif; ?>
            </form>
        </article>

        <article class="summary-card" style="border-color: rgba(251, 146, 60, 0.28); background: var(--vn-brass-glow);">
            <div>
                <span class="card-label" style="color: var(--vn-brass);">🔒 Escrow (Active Events)</span>
                <h3 id="escrow-revenue" class="card-value" style="color: var(--vn-brass);"><?= format_price($escrowRevenue ?? 0) ?></h3>
                <p class="card-meta" style="font-size:0.78rem;">Withdrawable: <strong><?= format_price($availableEscrowBalance ?? 0) ?></strong></p>
            </div>
            <div style="margin-top:0.85rem; width:100%;">
                <?php if (($availableEscrowBalance ?? 0.00) > 0): ?>
                    <button type="button" onclick="switchHostTab('payouts')" class="admin-action-btn primary" style="width:100%; justify-content:center; padding:0.55rem 1rem; font-size:0.85rem; background:var(--vn-brass);">
                        ⚡ Early Escrow Payout
                    </button>
                <?php else: ?>
                    <button type="button" class="admin-action-btn" disabled style="width:100%; justify-content:center; padding:0.55rem 1rem; font-size:0.85rem; opacity:0.5; cursor:not-allowed;">
                        No Escrow Available
                    </button>
                <?php endif; ?>
            </div>
        </article>

        <article class="summary-card">
            <div>
                <span class="card-label">Withdrawn (Disbursed)</span>
                <h3 id="disbursed-payouts" class="card-value" style="color:#0284c7;"><?= format_price($disbursedPayouts ?? 0) ?></h3>
            </div>
            <p class="card-meta">Sent directly to your bank account</p>
        </article>
    </section>

    <!-- Cash Flow Visual Health Bar -->
    <?php if ($totalTracked > 0): ?>
    <section class="host-cashflow-card" style="margin-bottom:2rem;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.65rem; flex-wrap:wrap; gap:0.5rem;">
            <span style="font-size:0.82rem; font-weight:800; color:var(--vn-ink-muted); text-transform:uppercase; letter-spacing:0.04em;">📊 Funds Distribution &amp; Cash Flow Health</span>
            <span style="font-size:0.82rem; font-weight:800; color:var(--vn-ink);">Total Tracked: <?= format_price($totalTracked ?? 0) ?></span>
        </div>

        <div class="host-cashflow-bar">
            <?php if (($disbursedPct ?? 0) > 0): ?>
                <div style="width:<?= $disbursedPct ?>%; background:#0284c7;" title="Disbursed: <?= $disbursedPct ?>% (<?= format_price($disbursedPayouts ?? 0) ?>)"></div>
            <?php endif; ?>
            <?php if (($availablePct ?? 0) > 0): ?>
                <div style="width:<?= $availablePct ?>%; background:#10b981;" title="Available: <?= $availablePct ?>% (<?= format_price($availableBalance ?? 0) ?>)"></div>
            <?php endif; ?>
            <?php if (($escrowPct ?? 0) > 0): ?>
                <div style="width:<?= $escrowPct ?>%; background:var(--vn-brass);" title="Escrow: <?= $escrowPct ?>% (<?= format_price($escrowRevenue ?? 0) ?>)"></div>
            <?php endif; ?>
            <?php if (($pendingPct ?? 0) > 0): ?>
                <div style="width:<?= $pendingPct ?>%; background:#d97706;" title="Pending: <?= $pendingPct ?>% (<?= format_price($pendingPayouts ?? 0) ?>)"></div>
            <?php endif; ?>
        </div>

        <div class="host-cashflow-legend">
            <div style="display:flex; align-items:center; gap:0.35rem;">
                <span style="width:9px; height:9px; border-radius:50%; background:#0284c7;"></span>
                <span style="color:var(--vn-ink-muted);">Disbursed:</span>
                <strong><?= $disbursedPct ?>%</strong>
            </div>
            <div style="display:flex; align-items:center; gap:0.35rem;">
                <span style="width:9px; height:9px; border-radius:50%; background:#10b981;"></span>
                <span style="color:var(--vn-ink-muted);">Available:</span>
                <strong><?= $availablePct ?>%</strong>
            </div>
            <div style="display:flex; align-items:center; gap:0.35rem;">
                <span style="width:9px; height:9px; border-radius:50%; background:var(--vn-brass);"></span>
                <span style="color:var(--vn-ink-muted);">In Escrow:</span>
                <strong><?= $escrowPct ?>%</strong>
            </div>
            <?php if ($pendingPct > 0): ?>
                <div style="display:flex; align-items:center; gap:0.35rem;">
                    <span style="width:9px; height:9px; border-radius:50%; background:#d97706;"></span>
                    <span style="color:var(--vn-ink-muted);">Pending Review:</span>
                    <strong><?= $pendingPct ?>%</strong>
                </div>
            <?php endif; ?>
        </div>
    </section>
    <?php endif; ?>

    <?php if (!empty($events)): ?>
    <!-- Quick Events Snapshot on Overview Tab -->
    <section class="form-card" style="margin-bottom:2rem; padding:1.5rem; border-radius:var(--vn-radius-lg);">
        <div class="edb-section-header">
            <div class="edb-section-title-wrap">
                <h3 class="edb-section-title">🎪 Events Portfolio Snapshot</h3>
                <p class="edb-section-subtext">Recent events and active door verification shortcuts</p>
            </div>
            <button type="button" onclick="switchHostTab('events')" class="admin-action-btn" style="background:var(--vn-parchment-warm); border:1.5px solid var(--vn-border); font-size:0.85rem; padding:0.45rem 0.95rem; cursor:pointer;">
                View All Events (<?= count($events) ?>) →
            </button>
        </div>

        <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(min(100%, 280px), 1fr)); gap:1rem;">
            <?php 
                $snapCount = 0;
                foreach ($events as $ev): 
                    if (++$snapCount > 3) break;
                    $evTime = strtotime($ev['date']);
                    $evSold = (int)($ev['tickets_sold'] ?? 0);
                    $evCap = (int)($ev['total_capacity'] ?? 0);
                    $evRev = (float)($ev['revenue'] ?? 0);
                    $evImg = !empty($ev['image']) ? $ev['image'] : 'https://images.unsplash.com/photo-1501281668745-f7f57925c3b4?w=800&q=60&auto=format&fit=crop';
            ?>
                <div style="background:var(--vn-parchment-warm); border:1.5px solid var(--vn-border); border-radius:14px; padding:1rem; display:flex; flex-direction:column; justify-content:space-between; gap:0.75rem;">
                    <div style="display:flex; gap:0.75rem; align-items:center;">
                        <img src="<?= e($evImg) ?>" alt="" style="width:48px; height:48px; border-radius:10px; object-fit:cover; border:1px solid var(--vn-border); flex-shrink:0;">
                        <div style="min-width:0; flex:1;">
                            <strong style="color:var(--vn-ink); font-size:0.95rem; font-family:var(--vn-font-serif); display:block; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;"><?= e($ev['name']) ?></strong>
                            <span style="font-size:0.75rem; color:var(--vn-ink-muted);">📅 <?= date('M d, Y', $evTime) ?></span>
                        </div>
                    </div>
                    <div style="display:flex; justify-content:space-between; align-items:center; font-size:0.82rem; border-top:1px dashed var(--vn-border); padding-top:0.5rem;">
                        <span style="color:var(--vn-ink-muted);">Sales: <strong style="color:var(--vn-ink);"><?= number_format($evSold) ?> / <?= number_format($evCap) ?></strong></span>
                        <strong style="color:var(--vn-brass);"><?= format_price($evRev) ?></strong>
                    </div>
                    <div style="display:flex; gap:0.4rem;">
                        <a href="{{ route('host/event/management/' . $ev['id']) }}" class="host-action-btn" style="flex:1; padding:0.4rem 0.6rem; font-size:0.78rem;">
                            ⚙️ Manage
                        </a>
                        <a href="{{ route('host/event/ticket-verification/' . $ev['id']) }}" class="host-action-btn verify-btn" style="flex:1; padding:0.4rem 0.6rem; font-size:0.78rem;">
                            🎟️ Scan
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

</div>

<!-- ════════════════════════════════════════════════════════════════
     TAB 2: MY EVENTS DIRECTORY
     ════════════════════════════════════════════════════════════════ -->
<div id="tab-events" class="edb-tab-pane <?= $activeTab === 'events' ? '' : 'hidden' ?>" data-tab-content="events" role="tabpanel">

    <section class="form-card" style="border-radius:var(--vn-radius-lg); padding:1.5rem; margin-bottom:2rem;">
        <div class="edb-section-header">
            <div class="edb-section-title-wrap">
                <h2 class="edb-section-title">
                    <?= !empty($isAdmin) ? 'All Platform Events' : 'Your Events Portfolio' ?>
                    <span style="font-size:0.92rem; font-weight:600; color:var(--vn-ink-muted);">
                        (<?= (int)($totalEventsCount ?? 0) ?> unique<?php if (($totalOccurrencesCount ?? 0) > ($totalEventsCount ?? 0)): ?>, <?= (int)$totalOccurrencesCount ?> total dates<?php endif; ?>)
                    </span>
                </h2>
                <p class="edb-section-subtext">Manage ticket sales, edit event details, and view recurrence series</p>
            </div>
            <a href="{{ route('host/event/create') }}" class="admin-action-btn primary" style="font-size:0.85rem; padding:0.5rem 1rem;">
                <span>➕</span> Create Event
            </a>
        </div>

        <!-- Category Filter Tabs -->
        <div class="dashboard-tabs" style="margin-bottom:1.5rem;">
            <button class="tab-btn active" data-filter="all">All Events <span class="tab-badge" id="badge-all">0</span></button>
            <button class="tab-btn" data-filter="live">🔴 Live Now <span class="tab-badge" id="badge-live">0</span></button>
            <button class="tab-btn" data-filter="upcoming">📅 Upcoming <span class="tab-badge" id="badge-upcoming">0</span></button>
            <button class="tab-btn" data-filter="past">✓ Past / Completed <span class="tab-badge" id="badge-past">0</span></button>
            <?php if (!empty($isAdmin)): ?>
                <button class="tab-btn" data-filter="mine">🙋 My Events <span class="tab-badge" id="badge-mine">0</span></button>
                <button class="tab-btn" data-filter="others">👥 Other Hosts <span class="tab-badge" id="badge-others">0</span></button>
            <?php endif; ?>
        </div>

        <!-- Grid of Event Cards -->
        <div id="events-grid" class="host-events-grid">
            @foreach($events as $event)
                <?php
                $eventTime = strtotime($event['date']);
                $now = time();
                $liveWindowEnd = $eventTime + (event_completion_hours() * 3600);

                if (($event['status'] ?? '') === 'completed' || $now > $liveWindowEnd) {
                    $status = 'past';
                    $statusLabel = '✓ Completed';
                } elseif ($now >= $eventTime && $now <= $liveWindowEnd) {
                    $status = 'live';
                    $statusLabel = '🔴 Live Now';
                } else {
                    $status = 'upcoming';
                    $statusLabel = '📅 Upcoming';
                }
                $sold = $event['tickets_sold'] ?? 0;
                $capacity = $event['total_capacity'] ?? 0;
                $percent = $capacity > 0 ? min(100, round(($sold / $capacity) * 100)) : 0;
                $revenue = $event['revenue'] ?? 0.00;
                $imageUrl = !empty($event['image']) ? $event['image'] : 'https://images.unsplash.com/photo-1501281668745-f7f57925c3b4?w=800&q=60&auto=format&fit=crop';
                $isOwnEvent = !empty($currentUserId) && ($event['organizer_id'] ?? '') == $currentUserId;
                $ownershipFilter = $isOwnEvent ? 'mine' : 'others';
                ?>
                <article class="host-event-card {{ $status === 'live' ? 'live-card' : ($status === 'past' ? 'past-card' : '') }}" data-status="{{ $status }}" data-owner="{{ $ownershipFilter }}">
                    <div class="event-card-image">
                        <div class="image-blur" style="background-image: url('<?= e($imageUrl) ?>');"></div>
                        <img src="<?= e($imageUrl) ?>" alt="<?= e($event['name']) ?>" class="image-full" />
                        <span class="status-badge {{ $status }}">{{ $statusLabel }}</span>
                        <?php if (!empty($isAdmin) && $isOwnEvent): ?>
                            <span style="position:absolute; top:0.5rem; right:0.5rem; background:rgba(16,185,129,0.92); color:#fff; font-size:0.7rem; font-weight:700; padding:0.2rem 0.55rem; border-radius:6px;">Your Event</span>
                        <?php endif; ?>
                    </div>
                    <div class="event-card-content">
                        <h3>{{ $event['name'] }}</h3>
                        <?php if (!empty($event['is_recurring'])): ?>
                            <div style="margin-bottom: 0.4rem; display: flex; align-items: center; justify-content: space-between; gap: 0.4rem; flex-wrap: wrap;">
                                <span style="display:inline-flex; align-items:center; gap:0.3rem; font-size:0.74rem; font-weight:700; color:#818cf8; background:rgba(99,102,241,0.12); border:1px solid rgba(99,102,241,0.25); border-radius:6px; padding:0.18rem 0.55rem;">
                                    🔄 Recurring (<?= e($event['recurrence_count'] ?? '') ?> Dates)
                                </span>
                                <button type="button" class="btn-series-dates" onclick="openSeriesOccurrencesModal('<?= $event['id'] ?>', '<?= addslashes(e($event['name'])) ?>')" style="background: rgba(99,102,241,0.08); border: 1px solid rgba(99,102,241,0.25); color: #818cf8; font-size: 0.72rem; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 0.25rem; padding: 0.16rem 0.45rem; border-radius: 5px;">
                                    <span>📅 All Dates ▾</span>
                                </button>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($isAdmin) && !$isOwnEvent): ?>
                            <div style="display:inline-flex; align-items:center; gap:0.35rem; background:rgba(99,102,241,0.08); border:1px solid rgba(99,102,241,0.18); color:#6366f1; font-size:0.75rem; font-weight:600; padding:0.2rem 0.6rem; border-radius:6px; margin-bottom:0.35rem;">
                                <span>👤</span> <?= e($event['organizer_name'] ?? 'Unknown') ?>
                            </div>
                        <?php endif; ?>
                        <p class="event-card-meta">
                            <span>📅 <?= date('M d, Y • g:i A', $eventTime) ?></span>
                            <span>📍 {{ $event['location'] }}</span>
                        </p>
                        <div class="event-stats-mini">
                            <div class="stat-mini">
                                <span class="label">Tickets Sold</span>
                                <span class="value"><?= number_format($sold) ?> / <?= number_format($capacity) ?></span>
                            </div>
                            <div class="stat-mini">
                                <span class="label">Revenue</span>
                                <span class="value"><?= format_price($revenue) ?></span>
                            </div>
                        </div>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: <?= $percent ?>%; {{ $status === 'live' ? 'background: #ef4444;' : ($status === 'past' ? 'background: var(--vn-border-dark);' : '') }}"></div>
                        </div>
                        <div class="card-actions">
                            @if($status === 'past')
                                <a href="{{ route('host/event/management/' . $event['id']) }}" class="host-action-btn summary-btn">
                                    <span>📊</span> Summary
                                </a>
                                <form method="POST" action="{{ route('host/event/duplicate/' . $event['id']) }}" style="display:contents;" onsubmit="return confirm('Clone this event? A new draft with the same tickets and settings will be created.');">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="host-action-btn" title="Duplicate Event">
                                        <span>📋</span> Clone
                                    </button>
                                </form>
                            @else
                                <a href="{{ route('host/event/management/' . $event['id']) }}" class="host-action-btn manage-btn <?= $status === 'live' ? 'live-btn' : '' ?>">
                                    <span>⚙️</span> Manage
                                </a>
                                <a href="{{ route('host/event/ticket-verification/' . $event['id']) }}" class="host-action-btn verify-btn <?= $status === 'live' ? 'live-primary-btn' : '' ?>">
                                    <span>🎟️</span> Scan
                                </a>
                                <a href="{{ route('host/event/edit/' . $event['id']) }}" class="host-action-btn edit-btn">
                                    <span>✏️</span> Edit
                                </a>
                                <form method="POST" action="{{ route('host/event/duplicate/' . $event['id']) }}" style="display:contents;" onsubmit="return confirm('Clone this event?');">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="host-action-btn" title="Clone Event">
                                        <span>📋</span>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </article>
            @endforeach
        </div>

        <div id="empty-events" class="empty-state" style="display:none; text-align:center; padding:3rem 1rem;">
            <p style="color:var(--vn-ink-muted); font-size:0.95rem; margin-bottom:1rem;">No events found for this filter category.</p>
            <a href="{{ route('host/event/create') }}" class="admin-action-btn primary">Create An Event</a>
        </div>
    </section>

</div>

<!-- ════════════════════════════════════════════════════════════════
     TAB 3: SALES ANALYTICS & TRENDS
     ════════════════════════════════════════════════════════════════ -->
<div id="tab-analytics" class="edb-tab-pane <?= $activeTab === 'analytics' ? '' : 'hidden' ?>" data-tab-content="analytics" role="tabpanel">

    <section class="host-analytics-grid" style="margin-bottom:2rem;">
        <!-- Column 1: 6-Month Sales Trend Chart -->
        <article class="form-card" style="padding:1.35rem 1.5rem; border-radius:var(--vn-radius-lg);">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.25rem;">
                <div>
                    <h3 style="margin:0; font-size:1.15rem; font-family:var(--vn-font-serif); font-weight:normal; color:var(--vn-ink);">📈 6-Month Sales Trend</h3>
                    <p style="margin:0.2rem 0 0; font-size:0.8rem; color:var(--vn-ink-muted);">Monthly revenue velocity &amp; ticket volume</p>
                </div>
                <span class="edb-tab-badge" style="background:var(--vn-forest-tint); color:var(--vn-forest);">
                    <?= $monthlyTicketsSum ?> tickets
                </span>
            </div>

            <?php if (!empty($monthlyTrends)): ?>
                <div style="display:flex; align-items:flex-end; justify-content:space-between; height:150px; gap:0.6rem; padding-top:1.5rem; border-bottom:1px solid var(--vn-border);">
                    <?php foreach ($monthlyTrends as $mt): ?>
                        <?php
                            $heightPct = $maxMonthlyRev > 0 ? max(8, round(($mt['gross'] / $maxMonthlyRev) * 100)) : 8;
                            $isCurrent = $mt['key'] === date('Y-m');
                        ?>
                        <div style="flex:1; display:flex; flex-direction:column; align-items:center; height:100%; justify-content:flex-end;">
                            <span style="font-size:0.68rem; color:var(--vn-ink-muted); font-weight:600; margin-bottom:4px;"><?= $mt['tickets'] > 0 ? $mt['tickets'] : '—' ?></span>
                            <div style="width:100%; max-width:36px; height:<?= $heightPct ?>%; background:<?= $isCurrent ? 'var(--vn-brass)' : 'var(--vn-brass-glow)' ?>; border:1px solid var(--vn-brass); border-radius:6px 6px 2px 2px; transition:all 0.2s;" title="<?= e($mt['label']) ?>: <?= format_price($mt['gross']) ?> (<?= $mt['tickets'] ?> tickets)"></div>
                            <span style="font-size:0.72rem; font-weight:<?= $isCurrent ? '800' : '600' ?>; color:<?= $isCurrent ? 'var(--vn-brass)' : 'var(--vn-ink-muted)' ?>; margin-top:6px;"><?= e($mt['short_label']) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div style="display:flex; justify-content:space-between; font-size:0.78rem; color:var(--vn-ink-muted); margin-top:0.65rem; flex-wrap:wrap; gap:0.4rem;">
                    <span>Total 6-Month Gross: <strong><?= format_price($monthlyGrossSum) ?></strong></span>
                    <span>Net: <strong><?= format_price($monthlyNetSum) ?></strong></span>
                </div>
            <?php else: ?>
                <p style="color:var(--vn-ink-muted); font-size:0.85rem; text-align:center; padding:2rem 0;">No sales recorded in the last 6 months.</p>
            <?php endif; ?>
        </article>

        <!-- Column 2: Ticket Tier Breakdown -->
        <article class="form-card" style="padding:1.35rem 1.5rem; border-radius:var(--vn-radius-lg);">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.25rem;">
                <div>
                    <h3 style="margin:0; font-size:1.15rem; font-family:var(--vn-font-serif); font-weight:normal; color:var(--vn-ink);">🏷️ Top Ticket Tiers</h3>
                    <p style="margin:0.2rem 0 0; font-size:0.8rem; color:var(--vn-ink-muted);">Revenue distribution across ticket categories</p>
                </div>
            </div>

            <?php if (!empty($tierBreakdown)): ?>
                <div style="display:flex; flex-direction:column; gap:0.95rem;">
                    <?php foreach ($tierBreakdown as $tb): ?>
                        <?php $tierPct = $maxTierRev > 0 ? round(($tb['total_revenue'] / $maxTierRev) * 100) : 0; ?>
                        <div>
                            <div style="display:flex; justify-content:space-between; font-size:0.82rem; font-weight:600; margin-bottom:3px; flex-wrap:wrap; gap:0.35rem;">
                                <span style="color:var(--vn-ink); font-weight:700; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; max-width:200px;"><?= e($tb['tier_name']) ?></span>
                                <span style="color:var(--vn-ink-muted);"><?= number_format((float)($tb['tickets_sold'] ?? 0)) ?> sold · <strong style="color:var(--vn-brass);"><?= format_price($tb['total_revenue'] ?? 0) ?></strong></span>
                            </div>
                            <div style="width:100%; height:6px; background:var(--vn-border); border-radius:3px; overflow:hidden;">
                                <div style="width:<?= $tierPct ?>%; height:100%; background:linear-gradient(90deg, var(--vn-brass), #f59e0b); border-radius:3px;"></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p style="color:var(--vn-ink-muted); font-size:0.85rem; text-align:center; padding:2rem 0;">No ticket tier data available yet.</p>
            <?php endif; ?>
        </article>
    </section>

    <!-- Financials Navigation Banner -->
    <section class="host-financials-banner">
        <div style="display:flex; align-items:center; gap:0.95rem;">
            <div style="width:44px; height:44px; border-radius:12px; background:var(--vn-brass-glow); display:flex; align-items:center; justify-content:center; font-size:1.4rem; flex-shrink:0;">
                💳
            </div>
            <div>
                <h4 style="margin:0 0 0.2rem; font-size:1.1rem; font-family:var(--vn-font-serif); font-weight:normal; color:var(--vn-ink);">Detailed Earnings &amp; Withdrawal History</h4>
                <p style="margin:0; font-size:0.82rem; color:var(--vn-ink-muted);">Track exact ticket revenues per event, fee deductions, escrow releases, and auto-settlements.</p>
            </div>
        </div>
        <a href="{{ route('host/financials') }}" class="admin-action-btn primary">
            <span>📊</span> View Full Financials →
        </a>
    </section>

</div>

<!-- ════════════════════════════════════════════════════════════════
     TAB 4: RECENT ORDERS & ATTENDEE STREAM
     ════════════════════════════════════════════════════════════════ -->
<div id="tab-orders" class="edb-tab-pane <?= $activeTab === 'orders' ? '' : 'hidden' ?>" data-tab-content="orders" role="tabpanel">

    <section class="form-card" style="border-radius:var(--vn-radius-lg); padding:1.5rem; margin-bottom:2rem;">
        <div class="edb-section-header">
            <div class="edb-section-title-wrap">
                <h2 class="edb-section-title">⚡ Recent Ticket Orders</h2>
                <p class="edb-section-subtext">Latest attendee registrations across all your hosted events</p>
            </div>
            <div class="edb-search-wrap">
                <span class="edb-search-icon">🔍</span>
                <input type="text" id="hostOrderSearch" class="edb-search-input" placeholder="Filter attendee or event...">
            </div>
        </div>

        <?php if (empty($recentOrders)): ?>
            <div style="text-align:center; padding:3rem 1rem; color:var(--vn-ink-muted);">
                <span style="font-size:2rem; display:block; margin-bottom:0.5rem;">🎟️</span>
                <p>No ticket orders recorded yet. Promote your events to start selling passes!</p>
            </div>
        <?php else: ?>
            <div class="admin-table-wrap host-orders-table-wrap" style="overflow-x:auto;">
                <table class="admin-table" style="width:100%; border-collapse:collapse;">
                    <thead>
                        <tr>
                            <th>Attendee</th>
                            <th>Event</th>
                            <th>Ticket Tier</th>
                            <th>Amount</th>
                            <th>Check-In Status</th>
                            <th style="text-align:right;">Purchased On</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentOrders as $ro): ?>
                            <?php
                                $isCompletedEvent = in_array($ro['event_status'] ?? '', ['completed', 'past']);
                                $purchasedTime = !empty($ro['purchase_date']) ? strtotime($ro['purchase_date']) : null;
                            ?>
                            <tr class="host-order-row">
                                <td>
                                    <div style="display:flex; align-items:center; gap:0.6rem;">
                                        <div style="width:34px; height:34px; border-radius:50%; background:var(--vn-brass-glow); color:var(--vn-brass); border:1px solid var(--vn-brass); display:flex; align-items:center; justify-content:center; font-weight:800; font-size:0.78rem; flex-shrink:0;">
                                            <?= strtoupper(substr($ro['attendee_name'] ?: 'U', 0, 1)) ?>
                                        </div>
                                        <div>
                                            <strong style="color:var(--vn-ink); font-size:0.9rem; display:block;"><?= e($ro['attendee_name']) ?></strong>
                                            <span style="font-size:0.76rem; color:var(--vn-ink-muted);"><?= e($ro['attendee_email']) ?></span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div style="display:flex; align-items:center; gap:0.4rem; flex-wrap:wrap;">
                                        <strong style="color:var(--vn-ink); font-size:0.88rem;"><?= e($ro['event_name']) ?></strong>
                                        <?php if ($isCompletedEvent): ?>
                                            <span style="background:var(--vn-forest-tint); color:var(--vn-forest); font-size:0.7rem; font-weight:700; padding:1px 7px; border-radius:4px;">✓ Completed</span>
                                        <?php else: ?>
                                            <span style="background:var(--vn-brass-glow); color:var(--vn-brass); font-size:0.7rem; font-weight:700; padding:1px 7px; border-radius:4px;">📅 Active</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <span style="background:var(--vn-parchment-warm); border:1px solid var(--vn-border); color:var(--vn-ink); font-weight:700; font-size:0.74rem; padding:2px 8px; border-radius:6px;">
                                        <?= e($ro['tier_name']) ?>
                                    </span>
                                </td>
                                <td>
                                    <strong style="color:var(--vn-ink); font-size:0.92rem;"><?= format_price($ro['price'] ?? 0) ?></strong>
                                </td>
                                <td>
                                    <?php if (!empty($ro['checked_in'])): ?>
                                        <span style="background:var(--vn-forest-tint); color:var(--vn-forest); font-size:0.74rem; font-weight:800; padding:2px 8px; border-radius:20px; display:inline-flex; align-items:center; gap:3px;">
                                            ✓ Checked In
                                        </span>
                                    <?php else: ?>
                                        <span style="background:var(--vn-parchment-warm); color:var(--vn-ink-muted); font-size:0.74rem; font-weight:600; padding:2px 8px; border-radius:20px;">
                                            ⏳ Gate Awaiting
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align:right; font-size:0.82rem; color:var(--vn-ink-muted); white-space:nowrap;">
                                    <?= $purchasedTime ? date('M d, Y • g:i A', $purchasedTime) : '—' ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Mobile Cards -->
            <div class="host-orders-cards-mobile">
                <?php foreach ($recentOrders as $ro):
                    $isCompletedEvent = in_array($ro['event_status'] ?? '', ['completed', 'past']);
                    $purchasedTime = !empty($ro['purchase_date']) ? strtotime($ro['purchase_date']) : null;
                ?>
                <div class="host-order-mobile-card">
                    <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:0.5rem; margin-bottom:0.55rem;">
                        <div style="display:flex; align-items:center; gap:0.55rem; min-width:0;">
                            <div style="width:34px; height:34px; border-radius:50%; background:var(--vn-brass-glow); color:var(--vn-brass); border:1px solid var(--vn-brass); display:flex; align-items:center; justify-content:center; font-weight:800; font-size:0.78rem; flex-shrink:0;">
                                <?= strtoupper(substr($ro['attendee_name'] ?: 'U', 0, 1)) ?>
                            </div>
                            <div style="min-width:0;">
                                <strong style="color:var(--vn-ink); font-size:0.88rem; display:block; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;"><?= e($ro['attendee_name']) ?></strong>
                                <span style="font-size:0.75rem; color:var(--vn-ink-muted); display:block; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;"><?= e($ro['attendee_email']) ?></span>
                            </div>
                        </div>
                        <div>
                            <strong style="font-size:0.95rem; color:var(--vn-brass); white-space:nowrap;"><?= format_price($ro['price'] ?? 0) ?></strong>
                        </div>
                    </div>
                    <div style="background:var(--vn-parchment-subtle); border-radius:8px; padding:0.5rem 0.65rem; margin-bottom:0.5rem; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:0.35rem;">
                        <span style="font-size:0.82rem; font-weight:600; color:var(--vn-ink);"><?= e($ro['event_name']) ?></span>
                        <span style="background:var(--vn-parchment-warm); color:var(--vn-ink); font-weight:700; font-size:0.72rem; padding:2px 7px; border-radius:5px;">
                            <?= e($ro['tier_name']) ?>
                        </span>
                    </div>
                    <div style="display:flex; justify-content:space-between; align-items:center; font-size:0.75rem; color:var(--vn-ink-muted); border-top:1px solid var(--vn-border); padding-top:0.4rem;">
                        <span>
                            <?php if (!empty($ro['checked_in'])): ?>
                                <span style="background:var(--vn-forest-tint); color:var(--vn-forest); font-weight:800; padding:1px 6px; border-radius:12px;">✓ Checked In</span>
                            <?php else: ?>
                                <span>⏳ Gate Pending</span>
                            <?php endif; ?>
                        </span>
                        <span><?= $purchasedTime ? date('M d, Y • g:i A', $purchasedTime) : '—' ?></span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>

</div>

<!-- ════════════════════════════════════════════════════════════════
     TAB 5: PAYOUTS & ESCROW SETTLEMENTS
     ════════════════════════════════════════════════════════════════ -->
<div id="tab-payouts" class="edb-tab-pane <?= $activeTab === 'payouts' ? '' : 'hidden' ?>" data-tab-content="payouts" role="tabpanel">

    <!-- Escrow Agreement & Early Withdrawal Card -->
    <?php if (($availableEscrowBalance ?? 0.00) > 0): ?>
    <section class="form-card" style="border-radius:var(--vn-radius-lg); padding:1.5rem; margin-bottom:2rem; border-left:4px solid var(--vn-brass); background:var(--vn-parchment-card);">
        <div class="edb-section-header">
            <div class="edb-section-title-wrap">
                <h3 class="edb-section-title">⚡ Early Escrow Payout Request</h3>
                <p class="edb-section-subtext">Access ticket sales funds from active upcoming events before the date occurs</p>
            </div>
            <span class="edb-tab-badge" style="background:var(--vn-brass-glow); color:var(--vn-brass); font-size:0.85rem; padding:0.35rem 0.75rem;">
                Available: <?= format_price($availableEscrowBalance ?? 0) ?>
            </span>
        </div>

        <form method="POST" action="{{ route('host/withdraw') }}" onsubmit="return confirm('Request early escrow release? Funds will be reviewed and disbursed to your verified bank account.');">
            <?= csrf_field() ?>
            <input type="hidden" name="withdraw_type" value="escrow">
            <input type="hidden" name="escrow_agreed" value="1">
            <div style="background:var(--vn-parchment-warm); border:1px solid var(--vn-border); border-radius:var(--vn-radius-sm); padding:1rem; margin-bottom:1rem; font-size:0.85rem; color:var(--vn-ink-muted); line-height:1.5;">
                <label style="display:flex; align-items:flex-start; gap:0.6rem; cursor:pointer;">
                    <input type="checkbox" name="escrow_agreement" value="1" required style="margin-top:3px;">
                    <span>I confirm that these events are actively taking place as scheduled. If an event is cancelled or significantly altered, I agree to refund ticket purchasers or return disbursed escrow advances in full.</span>
                </label>
            </div>
            <button type="submit" class="admin-action-btn primary" style="padding:0.65rem 1.5rem; font-size:0.9rem;">
                Submit Early Escrow Payout Request (<?= format_price($availableEscrowBalance ?? 0) ?>) →
            </button>
        </form>
    </section>
    <?php endif; ?>

    <!-- Recent Payout Settlements History -->
    <section class="form-card" style="border-radius:var(--vn-radius-lg); padding:1.5rem; margin-bottom:2rem;">
        <div class="edb-section-header">
            <div class="edb-section-title-wrap">
                <h2 class="edb-section-title">Recent Withdrawals &amp; Payouts</h2>
                <p class="edb-section-subtext">Your latest bank settlements, escrow advances, and transaction audit state</p>
            </div>
            <a href="{{ route('host/financials') }}" class="admin-action-btn" style="background:var(--vn-parchment-warm); border:1.5px solid var(--vn-border); font-size:0.82rem;">
                All Financials →
            </a>
        </div>

        <?php if (empty($recentPayouts)): ?>
            <div style="text-align:center; padding:3rem 1rem; color:var(--vn-ink-muted);">
                <span style="font-size:2rem; display:block; margin-bottom:0.5rem;">💳</span>
                <p>No payout requests recorded yet.</p>
            </div>
        <?php else: ?>
            <div class="admin-table-wrap host-payouts-table-wrap" style="overflow-x:auto;">
                <table class="admin-table" style="width:100%; border-collapse:collapse;">
                    <thead>
                        <tr>
                            <th>Ref #</th>
                            <th>Event</th>
                            <th>Type</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th style="text-align:right;">Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentPayouts as $rp): ?>
                            <?php
                                $isCompleted = in_array($rp['status'] ?? '', ['processed', 'completed']);
                                $isFailed = in_array($rp['status'] ?? '', ['failed', 'flagged']);
                            ?>
                            <tr>
                                <td><strong style="font-family:monospace; color:var(--vn-brass); font-size:0.9rem;">#<?= e($rp['id']) ?></strong></td>
                                <td><strong style="color:var(--vn-ink); font-size:0.88rem;"><?= e(!empty($rp['event_name']) ? $rp['event_name'] : 'General Balance') ?></strong></td>
                                <td>
                                    <?php if (($rp['type'] ?? '') === 'escrow'): ?>
                                        <span style="background:var(--vn-brass-glow); color:var(--vn-brass); border:1px solid var(--vn-brass); font-size:0.72rem; font-weight:800; padding:2px 7px; border-radius:6px;">⚡ Escrow</span>
                                    <?php elseif (($rp['type'] ?? '') === 'auto_completed'): ?>
                                        <span style="background:var(--vn-forest-tint); color:var(--vn-forest); font-size:0.72rem; font-weight:800; padding:2px 7px; border-radius:6px;">🤖 Auto</span>
                                    <?php else: ?>
                                        <span style="background:var(--vn-parchment-warm); color:var(--vn-ink); font-size:0.72rem; font-weight:700; padding:2px 7px; border-radius:6px;">Standard</span>
                                    <?php endif; ?>
                                </td>
                                <td><strong style="color:var(--vn-ink); font-size:0.92rem;"><?= format_price($rp['amount'] ?? 0) ?></strong></td>
                                <td>
                                    <?php if ($isCompleted): ?>
                                        <span style="background:var(--vn-forest-tint); color:var(--vn-forest); font-size:0.72rem; font-weight:800; padding:2px 7px; border-radius:20px;">✓ Settled</span>
                                    <?php elseif ($isFailed): ?>
                                        <span style="background:rgba(239, 68, 68, 0.12); color:#ef4444; font-size:0.72rem; font-weight:800; padding:2px 7px; border-radius:20px;">Failed</span>
                                    <?php else: ?>
                                        <span style="background:var(--vn-brass-glow); color:var(--vn-brass); font-size:0.72rem; font-weight:800; padding:2px 7px; border-radius:20px;">⏳ Pending</span>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align:right; font-size:0.82rem; color:var(--vn-ink-muted); white-space:nowrap;">
                                    <?= date('M d, Y', strtotime($rp['created_at'] ?? 'now')) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>

</div>

<!-- ════════════════════════════════════════════════════════════════
     RECURRING SERIES OCCURRENCES MODAL
     ════════════════════════════════════════════════════════════════ -->
<div id="series-occurrences-modal" style="display:none; position:fixed; inset:0; z-index:9999; background:rgba(0,0,0,0.65); backdrop-filter:blur(8px); -webkit-backdrop-filter:blur(8px); justify-content:center; align-items:center; padding:1rem;">
    <div style="width:100%; max-width:min(94vw, 560px); max-height:calc(100dvh - 2rem); overflow-y:auto; background:var(--vn-parchment-card); border-radius:var(--vn-radius-lg); box-shadow:var(--vn-shadow-elevated); border:1.5px solid var(--vn-border); margin:auto; box-sizing:border-box;">
        <div style="padding:1.35rem 1.6rem 1rem; border-bottom:1px solid var(--vn-border); display:flex; justify-content:space-between; align-items:center;">
            <div>
                <h3 id="series-modal-title" style="margin:0; font-size:1.15rem; font-family:var(--vn-font-serif); font-weight:normal; color:var(--vn-ink);">Recurring Event Dates</h3>
                <p id="series-modal-subtitle" style="margin:0.2rem 0 0; font-size:0.8rem; color:var(--vn-ink-muted);">Choose any occurrence to manage or edit</p>
            </div>
            <button type="button" onclick="closeSeriesOccurrencesModal()" style="background:none; border:none; font-size:1.5rem; cursor:pointer; color:var(--vn-ink-muted); line-height:1;" aria-label="Close">&times;</button>
        </div>
        <div id="series-modal-body" style="padding:1.25rem 1.6rem; min-height: 120px;">
            <div style="text-align: center; color: var(--vn-ink-muted); padding: 2rem 0;">Loading dates...</div>
        </div>
        <div style="padding:0.9rem 1.6rem; border-top:1px solid var(--vn-border); display:flex; justify-content:flex-end;">
            <button type="button" onclick="closeSeriesOccurrencesModal()" class="admin-action-btn" style="padding:0.5rem 1.2rem; font-size:0.85rem; border-radius:8px; background:var(--vn-parchment-warm); border:1.5px solid var(--vn-border);">Close</button>
        </div>
    </div>
</div>

<!-- ════════════════════════════════════════════════════════════════
     INTERACTIVE JAVASCRIPT: TAB SWITCHING, EVENT FILTER & MODALS
     ════════════════════════════════════════════════════════════════ -->
<script>
// ─── Executive Tab Switching ───
function switchHostTab(tabId) {
    const panes = document.querySelectorAll('[data-tab-content]');
    const btns = document.querySelectorAll('[data-tab-btn]');

    panes.forEach(p => p.classList.add('hidden'));
    btns.forEach(b => {
        b.classList.remove('active');
        b.setAttribute('aria-selected', 'false');
    });

    const targetPane = document.getElementById('tab-' + tabId);
    const targetBtn = document.querySelector(`[data-tab-btn="${tabId}"]`);

    if (targetPane) targetPane.classList.remove('hidden');
    if (targetBtn) {
        targetBtn.classList.add('active');
        targetBtn.setAttribute('aria-selected', 'true');
    }

    // Trigger Chart.js recalculation if charts exist
    window.dispatchEvent(new Event('resize'));

    // Update URL query without page reload
    const url = new URL(window.location);
    url.searchParams.set('tab', tabId);
    window.history.replaceState({}, '', url);
}

document.addEventListener('DOMContentLoaded', function() {
    // ─── Bind Tab Click Handlers ───
    document.querySelectorAll('[data-tab-btn]').forEach(btn => {
        btn.addEventListener('click', function() {
            const tabId = this.getAttribute('data-tab-btn');
            switchHostTab(tabId);
        });
    });

    // ─── Live Clock Updater ───
    const clockEl = document.getElementById('host-live-clock');
    if (clockEl) {
        setInterval(() => {
            const now = new Date();
            clockEl.textContent = '🕒 ' + now.toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' }) + ' • ' + now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: true });
        }, 30000);
    }

    // ─── Host Events Filtering ───
    const eventCards = Array.from(document.querySelectorAll('.host-event-card'));
    const tabButtons = document.querySelectorAll('.dashboard-tabs .tab-btn');
    const emptyState = document.getElementById('empty-events');

    function updateTabBadges() {
        let counts = { all: eventCards.length, live: 0, upcoming: 0, past: 0, mine: 0, others: 0 };
        eventCards.forEach(card => {
            const status = card.getAttribute('data-status');
            const owner = card.getAttribute('data-owner');
            if (counts[status] !== undefined) counts[status]++;
            if (counts[owner] !== undefined) counts[owner]++;
        });

        for (const [key, count] of Object.entries(counts)) {
            const badge = document.getElementById(`badge-${key}`);
            if (badge) badge.textContent = count;
        }
    }

    function renderEvents(filter) {
        let visibleCount = 0;
        eventCards.forEach(card => {
            let show = false;
            if (filter === 'all') {
                show = true;
            } else if (filter === 'mine' || filter === 'others') {
                show = (card.getAttribute('data-owner') === filter);
            } else {
                show = (card.getAttribute('data-status') === filter);
            }

            card.style.display = show ? 'flex' : 'none';
            if (show) visibleCount++;
        });

        if (emptyState) {
            emptyState.style.display = (visibleCount === 0) ? 'block' : 'none';
        }
    }

    tabButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            tabButtons.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            const filter = this.getAttribute('data-filter') || 'all';
            renderEvents(filter);
        });
    });

    updateTabBadges();

    // ─── Recent Orders Live Filter ───
    const orderSearch = document.getElementById('hostOrderSearch');
    const orderRows = document.querySelectorAll('.host-order-row');
    if (orderSearch && orderRows.length) {
        orderSearch.addEventListener('input', function() {
            const query = this.value.toLowerCase().trim();
            orderRows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(query) ? '' : 'none';
            });
        });
    }
});

// ─── Recurring Series Occurrences Modal ───
function openSeriesOccurrencesModal(eventId, eventName) {
    const modal = document.getElementById('series-occurrences-modal');
    const title = document.getElementById('series-modal-title');
    const body = document.getElementById('series-modal-body');
    if (!modal) return;

    title.textContent = eventName ? 'Series Dates: ' + eventName : 'Recurring Event Dates';
    body.innerHTML = '<div style="text-align: center; color: var(--vn-ink-muted); padding: 2rem 0;">⏳ Loading dates...</div>';
    modal.style.display = 'flex';

    fetch('/host/event/' + eventId + '/occurrences')
        .then(res => res.json())
        .then(data => {
            if (!data.occurrences || data.occurrences.length === 0) {
                body.innerHTML = '<div style="text-align: center; color: var(--vn-ink-muted); padding: 1.5rem 0;">No other occurrences found for this series.</div>';
                return;
            }

            let html = '<div style="display:flex; flex-direction:column; gap:0.65rem;">';
            data.occurrences.forEach((occ) => {
                const isCurrent = occ.id == data.current_id;
                const occDate = new Date(occ.date);
                const isPast = occDate.getTime() < Date.now();
                const dateFormatted = occDate.toLocaleDateString(undefined, { weekday: 'short', month: 'short', day: 'numeric', year: 'numeric' });
                const timeFormatted = occDate.toLocaleTimeString(undefined, { hour: '2-digit', minute: '2-digit' });

                html += `
                    <div style="display:flex; align-items:center; justify-content:space-between; gap:0.75rem; padding:0.75rem 0.95rem; background: ${isCurrent ? 'var(--vn-brass-glow)' : 'var(--vn-parchment-warm)'}; border: 1.5px solid ${isCurrent ? 'var(--vn-brass)' : 'var(--vn-border)'}; border-radius:8px; flex-wrap:wrap;">
                        <div>
                            <div style="display:flex; align-items:center; gap:0.4rem;">
                                <strong style="font-size:0.88rem; color:var(--vn-ink);">#${occ.recurrence_sequence || '?'} — ${dateFormatted}</strong>
                                ${isCurrent ? '<span style="font-size:0.7rem; font-weight:700; background:var(--vn-brass); color:#fff; padding:0.1rem 0.4rem; border-radius:4px;">Next / Active</span>' : ''}
                                ${isPast ? '<span style="font-size:0.7rem; color:var(--vn-ink-muted); background:var(--vn-border); padding:0.1rem 0.4rem; border-radius:4px;">Past</span>' : '<span style="font-size:0.7rem; color:var(--vn-forest); background:var(--vn-forest-tint); padding:0.1rem 0.4rem; border-radius:4px; font-weight:600;">Upcoming</span>'}
                            </div>
                            <div style="font-size:0.78rem; color:var(--vn-ink-muted); margin-top:2px;">
                                ⏰ ${timeFormatted} &bull; Status: ${occ.status || 'Active'}
                            </div>
                        </div>
                        <div style="display:inline-flex; align-items:center; gap:0.4rem;">
                            <a href="/host/event/edit/${occ.id}" class="admin-action-btn" style="padding:0.35rem 0.7rem; font-size:0.78rem; font-weight:700; background:var(--vn-brass); color:#fff; border-radius:6px; text-decoration:none;">
                                ✏️ Edit
                            </a>
                            <a href="/host/event/management/${occ.id}" class="admin-action-btn" style="padding:0.35rem 0.7rem; font-size:0.78rem; font-weight:600; background:var(--vn-parchment-card); color:var(--vn-ink); border:1px solid var(--vn-border); border-radius:6px; text-decoration:none;">
                                ⚙️ Manage
                            </a>
                        </div>
                    </div>
                `;
            });
            html += '</div>';
            body.innerHTML = html;
        })
        .catch(err => {
            body.innerHTML = '<div style="text-align: center; color: #ef4444; padding: 1.5rem 0;">Failed to load series dates. Please try again.</div>';
        });
}

function closeSeriesOccurrencesModal() {
    const modal = document.getElementById('series-occurrences-modal');
    if (modal) modal.style.display = 'none';
}

document.addEventListener('click', function(e) {
    const modal = document.getElementById('series-occurrences-modal');
    if (modal && e.target === modal) closeSeriesOccurrencesModal();
});
</script>
@endsection
