<?php

/**
 * Host Dashboard
 *
 * Enhanced dashboard with visual analytics, live & completed event spotlight,
 * cash flow distribution, ticket tier insights, and recent orders.
 *
 * @package Evently
 * @subpackage Views/Host
 * @author Celio Natti
 * @version 2.0.0
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
?>

@extends('layouts/host')

@section('title')
Host Dashboard - Manage Your <?= e($siteName) ?> Events
@endsection

@section('content')
<style>
/* ═══════════════════════════════════════════════════════════
   Host Dashboard — Responsive Polish & Layout Refactor
   ═══════════════════════════════════════════════════════════ */

/* Hero & Quick Actions */
.host-hero-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1.25rem;
    margin-bottom: 1.75rem;
}

.host-hero-actions {
    display: flex;
    gap: 0.6rem;
    align-items: center;
    flex-wrap: wrap;
}

.host-hero-btn {
    padding: 0.55rem 1.15rem;
    font-size: 0.88rem;
    font-weight: 700;
    border-radius: 11px;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    transition: all 0.2s ease;
    white-space: nowrap;
}

.host-hero-btn--primary {
    background: rgb(var(--brand));
    color: #ffffff !important;
    border: none;
    box-shadow: 0 4px 14px rgba(var(--brand), 0.3);
}

.host-hero-btn--primary:hover {
    filter: brightness(1.08);
    transform: translateY(-1px);
}

.host-hero-btn--secondary {
    background: rgba(var(--card), 0.9);
    color: rgb(var(--text)) !important;
    border: 1px solid rgba(var(--muted), 0.2);
}

.host-hero-btn--secondary:hover {
    background: rgba(var(--muted), 0.1);
    border-color: rgba(var(--brand), 0.3);
    color: rgb(var(--brand)) !important;
}

/* ── Spotlight Card (Next / Live / Completed) ── */
.host-spotlight-card {
    margin-bottom: 1.75rem;
    padding: 1.35rem 1.6rem;
    background: linear-gradient(135deg, rgba(var(--card), 1) 0%, rgba(var(--muted), 0.04) 100%);
    border: 1px solid rgba(var(--muted), 0.18);
    border-radius: 20px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04);
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
    box-shadow: 0 6px 18px rgba(0, 0, 0, 0.15);
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
    background: rgb(var(--card));
    border: 1px solid rgba(var(--muted), 0.15);
    border-radius: 14px;
    padding: 0.75rem 1.1rem;
    min-width: 145px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);
}

.host-spotlight-actions {
    display: flex;
    flex-direction: column;
    gap: 0.45rem;
    min-width: 120px;
}

/* ── Primary Financial Summary Cards Grid ── */
.host-summary-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr) !important;
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.summary-card {
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

.summary-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.06);
}

.card-label {
    font-size: 0.82rem;
    font-weight: 700;
    color: rgb(var(--muted));
    text-transform: uppercase;
    letter-spacing: 0.03em;
    display: block;
    margin-bottom: 0.35rem;
}

.card-value {
    font-size: clamp(1.45rem, 2.2vw, 1.95rem);
    font-weight: 900;
    color: rgb(var(--text));
    margin: 0 0 0.35rem;
    line-height: 1.2;
}

.card-meta {
    font-size: 0.8rem;
    color: rgb(var(--muted));
    margin: 0;
}

/* ── Cash Flow Bar ── */
.host-cashflow-card {
    margin-bottom: 1.75rem;
    padding: 1.15rem 1.35rem;
    background: rgb(var(--card));
    border: 1px solid rgba(var(--muted), 0.14);
    border-radius: 18px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.03);
}

.host-cashflow-bar {
    display: flex;
    width: 100%;
    height: 12px;
    border-radius: 8px;
    overflow: hidden;
    background: rgba(var(--muted), 0.12);
    margin-bottom: 0.85rem;
}

.host-cashflow-legend {
    display: flex;
    gap: 1.25rem;
    flex-wrap: wrap;
    font-size: 0.8rem;
}

/* ── Visual Analytics Grid ── */
.host-analytics-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr) !important;
    gap: 1.25rem;
    margin-bottom: 1.75rem;
}

/* ── Category Filter Tabs (App-Style Scrollable Bar) ── */
.dashboard-tabs {
    display: flex;
    gap: 0.5rem;
    overflow-x: auto;
    white-space: nowrap;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: none;
    padding-bottom: 0.35rem;
    margin-bottom: 1.35rem;
    border-bottom: 1px solid rgba(var(--muted), 0.12);
}

.dashboard-tabs::-webkit-scrollbar {
    display: none;
}

.dashboard-tabs .tab-btn {
    padding: 0.55rem 1.15rem;
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
}

.dashboard-tabs .tab-btn:hover {
    background: rgba(var(--brand), 0.1);
    color: rgb(var(--brand));
    border-color: rgba(var(--brand), 0.25);
}

.dashboard-tabs .tab-btn.active {
    background: rgb(var(--brand));
    color: #ffffff;
    border-color: rgb(var(--brand));
    box-shadow: 0 3px 10px rgba(var(--brand), 0.25);
}

.tab-badge {
    background: rgba(0, 0, 0, 0.2);
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

/* ── Events Grid ── */
.host-events-grid {
    display: grid !important;
    grid-template-columns: repeat(auto-fill, minmax(290px, 1fr)) !important;
    gap: 1.35rem;
}

.host-event-card {
    background: rgb(var(--card));
    border: 1px solid rgba(var(--muted), 0.14);
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.04);
    display: flex;
    flex-direction: column;
    transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
}

.host-event-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 28px rgba(0, 0, 0, 0.08);
    border-color: rgba(var(--brand), 0.3);
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
    font-size: 1.1rem;
    font-weight: 800;
    color: rgb(var(--text));
    line-height: 1.3;
}

.event-card-meta {
    font-size: 0.8rem;
    color: rgb(var(--muted));
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
    margin: 0 0 0.85rem;
}

.event-stats-mini {
    display: flex;
    justify-content: space-between;
    background: rgba(var(--muted), 0.05);
    border-radius: 10px;
    padding: 0.6rem 0.85rem;
    margin-bottom: 0.75rem;
}

.stat-mini .label {
    font-size: 0.72rem;
    color: rgb(var(--muted));
    text-transform: uppercase;
    font-weight: 700;
    display: block;
}

.stat-mini .value {
    font-size: 0.92rem;
    font-weight: 800;
    color: rgb(var(--text));
}

.progress-bar {
    width: 100%;
    height: 6px;
    background: rgba(var(--muted), 0.15);
    border-radius: 3px;
    overflow: hidden;
    margin-bottom: 1rem;
}

.progress-fill {
    height: 100%;
    background: #10b981;
    border-radius: 3px;
}

/* Card Action Buttons */
.card-actions {
    display: flex;
    gap: 0.45rem;
    flex-wrap: wrap;
    margin-top: auto;
    padding-top: 0.75rem;
    border-top: 1px solid rgba(var(--muted), 0.1);
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
    border: 1px solid rgba(var(--muted), 0.18);
    background: rgba(var(--card), 1);
    color: rgb(var(--text));
    cursor: pointer;
    transition: all 0.2s ease;
    flex: 1;
    min-width: 65px;
    white-space: nowrap;
}

.host-action-btn:hover {
    background: rgba(var(--brand), 0.1);
    border-color: rgba(var(--brand), 0.3);
    color: rgb(var(--brand));
}

.host-action-btn.manage-btn {
    background: rgba(var(--brand), 0.1);
    color: rgb(var(--brand));
    border-color: rgba(var(--brand), 0.25);
}

.host-action-btn.verify-btn {
    background: rgba(16, 185, 129, 0.1);
    color: #059669;
    border-color: rgba(16, 185, 129, 0.25);
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

/* ══ Tablet Breakpoint (601px - 900px) ══ */
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

/* ══ Mobile Breakpoint (<= 600px) ══ */
@media (max-width: 600px) {
    .host-hero-bar {
        flex-direction: column;
        align-items: stretch;
    }

    .host-hero-actions {
        width: 100%;
    }

    .host-hero-btn {
        flex: 1;
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
}
</style>

<!-- Page Hero & Quick Action Bar -->
<section class="page-hero" style="margin-bottom:1.5rem;">
    <div class="host-hero-bar">
        <div>
            <span class="panel-badge">Host Dashboard</span>
            <?php if (!empty($isAdmin)): ?>
                <h1 style="margin:0.25rem 0 0.4rem; font-size:clamp(1.6rem, 2.5vw, 2.2rem);">All Platform Events</h1>
                <p class="lede" style="margin:0; font-size:0.92rem;">You're viewing as <strong>Admin</strong>. Manage events, monitor real-time sales, and engage attendees.</p>
            <?php else: ?>
                <h1 style="margin:0.25rem 0 0.4rem; font-size:clamp(1.6rem, 2.5vw, 2.2rem);">Welcome back, <?= e(user_first_name()) ?>! 👋</h1>
                <p class="lede" style="margin:0; font-size:0.92rem;">Monitor your ticket sales, check-in velocity, and event earnings in real time.</p>
            <?php endif; ?>
        </div>

        <div class="host-hero-actions">
            <a href="{{ route('host/event/create') }}" class="host-hero-btn host-hero-btn--primary">
                <span>➕</span> Create Event
            </a>
            <a href="{{ route('host/event/ticket-verification') }}" class="host-hero-btn host-hero-btn--secondary">
                <span>🎟️</span> Scan QR Code
            </a>
            <a href="{{ route('host/financials') }}" class="host-hero-btn host-hero-btn--secondary">
                <span>💳</span> Financials
            </a>
        </div>
    </div>
</section>

<?php if (($totalEventsCount ?? 0) === 0): ?>
<!-- Brand New Host Welcome & Quick Start Onboarding Card -->
<section class="form-card" style="margin-bottom:2rem; padding:1.75rem; background:linear-gradient(135deg, rgba(var(--brand), 0.08) 0%, rgba(var(--card), 1) 100%); border:1.5px dashed rgba(var(--brand), 0.35); border-radius:20px;">
    <div style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:1.25rem; margin-bottom:1.25rem;">
        <div style="max-width:620px;">
            <div style="display:inline-flex; align-items:center; gap:0.4rem; background:rgba(var(--brand), 0.12); color:rgb(var(--brand)); font-weight:700; font-size:0.78rem; padding:0.25rem 0.75rem; border-radius:20px; margin-bottom:0.6rem;">
                🚀 Welcome to the Host Portal
            </div>
            <h2 style="margin:0 0 0.4rem; font-size:clamp(1.2rem, 2vw, 1.5rem); font-weight:800; color:rgb(var(--text));">Ready to host your first event?</h2>
            <p style="margin:0; font-size:0.9rem; color:rgb(var(--muted)); line-height:1.5;">Create customized ticket tiers, accept online payments, track live attendee check-ins with QR codes, and receive direct bank payouts.</p>
        </div>
        <a href="{{ route('host/event/create') }}" class="host-hero-btn host-hero-btn--primary" style="padding:0.75rem 1.5rem; font-size:0.95rem;">
            <span>➕</span> Create Your First Event
        </a>
    </div>

    <!-- 3 Quick Start Steps -->
    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(min(100%, 200px), 1fr)); gap:1rem; margin-top:1.25rem; padding-top:1.25rem; border-top:1px solid rgba(var(--muted), 0.15);">
        <div style="display:flex; gap:0.75rem; align-items:flex-start;">
            <div style="width:32px; height:32px; border-radius:8px; background:rgba(var(--brand), 0.15); color:rgb(var(--brand)); display:flex; align-items:center; justify-content:center; font-weight:800; font-size:0.85rem; flex-shrink:0;">1</div>
            <div>
                <strong style="font-size:0.88rem; color:rgb(var(--text)); display:block; margin-bottom:0.15rem;">Set Up Details</strong>
                <span style="font-size:0.78rem; color:rgb(var(--muted));">Add ticket tiers (VIP, Regular), prices, date &amp; venue.</span>
            </div>
        </div>
        <div style="display:flex; gap:0.75rem; align-items:flex-start;">
            <div style="width:32px; height:32px; border-radius:8px; background:rgba(16, 185, 129, 0.15); color:#10b981; display:flex; align-items:center; justify-content:center; font-weight:800; font-size:0.85rem; flex-shrink:0;">2</div>
            <div>
                <strong style="font-size:0.88rem; color:rgb(var(--text)); display:block; margin-bottom:0.15rem;">Share &amp; Sell Tickets</strong>
                <span style="font-size:0.78rem; color:rgb(var(--muted));">Get your public event page and track referral sales.</span>
            </div>
        </div>
        <div style="display:flex; gap:0.75rem; align-items:flex-start;">
            <div style="width:32px; height:32px; border-radius:8px; background:rgba(59, 130, 246, 0.15); color:#2563eb; display:flex; align-items:center; justify-content:center; font-weight:800; font-size:0.85rem; flex-shrink:0;">3</div>
            <div>
                <strong style="font-size:0.88rem; color:rgb(var(--text)); display:block; margin-bottom:0.15rem;">Check-In &amp; Payouts</strong>
                <span style="font-size:0.78rem; color:rgb(var(--muted));">Scan QR tickets at the door and withdraw your funds.</span>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Next / Live Event Spotlight Card OR Most Recent Completed Event Spotlight -->
<?php if (!empty($nextEvent)): ?>
<section class="form-card host-spotlight-card">
    <div class="host-spotlight-wrap">
        <div class="host-spotlight-info">
            <?php $spotlightImg = !empty($nextEvent['image']) ? $nextEvent['image'] : 'https://images.unsplash.com/photo-1501281668745-f7f57925c3b4?w=800&q=60&auto=format&fit=crop'; ?>
            <div class="host-spotlight-thumb">
                <img src="<?= e($spotlightImg) ?>" alt="<?= e($nextEvent['name']) ?>" />
                <?php if ($nextEvent['is_live_now']): ?>
                    <span style="position:absolute; top:4px; right:4px; background:#ef4444; color:#fff; font-size:0.62rem; font-weight:800; padding:2px 5px; border-radius:4px; text-transform:uppercase;">LIVE</span>
                <?php endif; ?>
            </div>

            <div style="flex:1; min-width:200px;">
                <div style="display:flex; align-items:center; gap:0.5rem; flex-wrap:wrap; margin-bottom:0.25rem;">
                    <?php if ($nextEvent['is_live_now']): ?>
                        <span style="background:rgba(239, 68, 68, 0.15); color:#ef4444; border:1px solid rgba(239, 68, 68, 0.3); font-size:0.75rem; font-weight:800; padding:2px 8px; border-radius:20px; display:inline-flex; align-items:center; gap:4px;">
                            <span style="display:inline-block; width:6px; height:6px; border-radius:50%; background:#ef4444; animation:pulseDot 1.5s infinite;"></span> Live Happening Now
                        </span>
                    <?php else: ?>
                        <span style="background:rgba(59, 130, 246, 0.12); color:#2563eb; border:1px solid rgba(59, 130, 246, 0.25); font-size:0.75rem; font-weight:700; padding:2px 8px; border-radius:20px;">
                            📅 Next Upcoming Event
                        </span>
                    <?php endif; ?>
                    <span style="font-size:0.8rem; color:rgb(var(--muted));">📍 <?= e($nextEvent['location']) ?></span>
                </div>

                <h3 style="margin:0 0 0.3rem; font-size:1.15rem; font-weight:800; color:rgb(var(--text));"><?= e($nextEvent['name']) ?></h3>
                <p style="margin:0; font-size:0.82rem; color:rgb(var(--muted));">
                    🗓️ <?= date('l, M d, Y • g:i A', $nextEvent['timestamp']) ?>
                </p>
            </div>
        </div>

        <!-- Live Metrics / Gauges -->
        <div class="host-spotlight-metrics">
            <!-- Ticket Sales Meter -->
            <div class="host-meter-card">
                <div style="display:flex; justify-content:space-between; font-size:0.75rem; font-weight:700; color:rgb(var(--muted)); margin-bottom:0.25rem;">
                    <span>Ticket Sales</span>
                    <span style="color:rgb(var(--text));"><?= $nextEvent['capacity_pct'] ?>%</span>
                </div>
                <div style="font-size:1rem; font-weight:800; color:rgb(var(--text));"><?= number_format($nextEvent['sold_count']) ?> <span style="font-size:0.75rem; font-weight:500; color:rgb(var(--muted));">/ <?= number_format($nextEvent['total_capacity']) ?></span></div>
                <div style="width:100%; height:5px; background:rgba(var(--muted), 0.15); border-radius:3px; overflow:hidden; margin-top:4px;">
                    <div style="width:<?= $nextEvent['capacity_pct'] ?>%; height:100%; background:#10b981; border-radius:3px;"></div>
                </div>
            </div>

            <!-- Check-In Meter -->
            <div class="host-meter-card">
                <div style="display:flex; justify-content:space-between; font-size:0.75rem; font-weight:700; color:rgb(var(--muted)); margin-bottom:0.25rem;">
                    <span>Check-Ins</span>
                    <span style="color:rgb(var(--text));"><?= $nextEvent['checkin_pct'] ?>%</span>
                </div>
                <div style="font-size:1rem; font-weight:800; color:rgb(var(--text));"><?= number_format($nextEvent['checked_in_count']) ?> <span style="font-size:0.75rem; font-weight:500; color:rgb(var(--muted));">/ <?= number_format($nextEvent['sold_count']) ?></span></div>
                <div style="width:100%; height:5px; background:rgba(var(--muted), 0.15); border-radius:3px; overflow:hidden; margin-top:4px;">
                    <div style="width:<?= $nextEvent['checkin_pct'] ?>%; height:100%; background:#6366f1; border-radius:3px;"></div>
                </div>
            </div>

            <!-- Actions -->
            <div class="host-spotlight-actions">
                <a href="{{ route('host/event/ticket-verification/' . $nextEvent['id']) }}" class="admin-action-btn primary" style="padding:0.5rem 0.85rem; font-size:0.82rem; border-radius:9px; text-decoration:none; text-align:center; font-weight:700;">
                    🎟️ Scan QR
                </a>
                <a href="{{ route('host/event/management/' . $nextEvent['id']) }}" class="admin-action-btn" style="padding:0.5rem 0.85rem; font-size:0.82rem; border-radius:9px; text-decoration:none; text-align:center; font-weight:700;">
                    ⚙️ Manage
                </a>
            </div>
        </div>
    </div>
</section>
<?php elseif (!empty($lastCompletedEvent)): ?>
<!-- Most Recent Completed Event Spotlight -->
<section class="form-card host-spotlight-card" style="background:linear-gradient(135deg, rgba(var(--card), 1) 0%, rgba(16, 185, 129, 0.03) 100%); border-color:rgba(16, 185, 129, 0.25);">
    <div class="host-spotlight-wrap">
        <div class="host-spotlight-info">
            <?php $spotlightImg = !empty($lastCompletedEvent['image']) ? $lastCompletedEvent['image'] : 'https://images.unsplash.com/photo-1501281668745-f7f57925c3b4?w=800&q=60&auto=format&fit=crop'; ?>
            <div class="host-spotlight-thumb">
                <img src="<?= e($spotlightImg) ?>" alt="<?= e($lastCompletedEvent['name']) ?>" />
            </div>

            <div style="flex:1; min-width:200px;">
                <div style="display:flex; align-items:center; gap:0.5rem; flex-wrap:wrap; margin-bottom:0.25rem;">
                    <span style="background:rgba(16, 185, 129, 0.12); color:#059669; border:1px solid rgba(16, 185, 129, 0.25); font-size:0.75rem; font-weight:700; padding:2px 8px; border-radius:20px;">
                        ✓ Latest Completed Event
                    </span>
                    <span style="font-size:0.8rem; color:rgb(var(--muted));">📍 <?= e($lastCompletedEvent['location']) ?></span>
                </div>

                <h3 style="margin:0 0 0.3rem; font-size:1.15rem; font-weight:800; color:rgb(var(--text));"><?= e($lastCompletedEvent['name']) ?></h3>
                <p style="margin:0; font-size:0.82rem; color:rgb(var(--muted));">
                    Held on <?= date('M d, Y', $lastCompletedEvent['timestamp']) ?> · Generated <strong><?= format_price($lastCompletedEvent['net_sales']) ?></strong> net
                </p>
            </div>
        </div>

        <div class="host-spotlight-metrics">
            <div class="host-meter-card" style="text-align:center;">
                <div style="font-size:0.75rem; font-weight:700; color:rgb(var(--muted));">Attendance</div>
                <div style="font-size:1rem; font-weight:800; color:rgb(var(--text));"><?= number_format($lastCompletedEvent['sold_count']) ?> tickets</div>
            </div>

            <div class="host-meter-card" style="text-align:center;">
                <div style="font-size:0.75rem; font-weight:700; color:rgb(var(--muted));">Net Earnings</div>
                <div style="font-size:1rem; font-weight:800; color:#059669;"><?= format_price($lastCompletedEvent['net_sales']) ?></div>
            </div>

            <a href="{{ route('host/event/management/' . $lastCompletedEvent['id']) }}" class="admin-action-btn primary" style="padding:0.55rem 1.15rem; font-size:0.85rem; border-radius:10px; text-decoration:none; font-weight:700;">
                📊 View Summary
            </a>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Primary Financial Summary Cards -->
<section class="host-summary-grid">
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

    <article class="summary-card" style="border-color: rgba(16, 185, 129, 0.28); background: rgba(16, 185, 129, 0.04);">
        <div>
            <span class="card-label" style="color: #10b981;">Available Balance</span>
            <h3 id="available-balance" class="card-value" style="color: #10b981;"><?= format_price($availableBalance ?? 0) ?></h3>
            <p class="card-meta" style="font-size:0.78rem;">From completed events</p>
        </div>
        <form method="POST" action="{{ route('host/withdraw') }}" style="margin-top:0.85rem; width:100%;">
            <?= csrf_field() ?>
            <input type="hidden" name="withdraw_type" value="completed">
            <?php if (($availableBalance ?? 0.00) > 0): ?>
                <button type="submit" class="host-hero-btn host-hero-btn--primary" style="width:100%; justify-content:center; padding:0.5rem 1rem; font-size:0.85rem; cursor:pointer;">
                    💸 Request Payout
                </button>
            <?php else: ?>
                <button type="button" class="admin-action-btn" disabled style="width:100%; justify-content:center; padding:0.5rem 1rem; font-size:0.85rem; opacity:0.5; cursor:not-allowed; border-radius:10px;">
                    No Balance Available
                </button>
            <?php endif; ?>
        </form>
    </article>

    <article class="summary-card" style="border-color: rgba(251, 146, 60, 0.28); background: rgba(251, 146, 60, 0.04);">
        <div>
            <span class="card-label" style="color: #ea580c;">🔒 Escrow (Upcoming)</span>
            <h3 id="escrow-revenue" class="card-value" style="color: #ea580c;"><?= format_price($escrowRevenue ?? 0) ?></h3>
            <p class="card-meta" style="font-size:0.78rem;">Withdrawable: <strong><?= format_price($availableEscrowBalance ?? 0) ?></strong></p>
        </div>
        <div style="margin-top:0.85rem; width:100%;">
            <?php if (($availableEscrowBalance ?? 0.00) > 0): ?>
                <button type="button" id="escrow-payout-btn" class="host-hero-btn host-hero-btn--primary" style="width:100%; justify-content:center; padding:0.5rem 1rem; font-size:0.85rem; background:#ea580c; cursor:pointer;">
                    ⚡ Early Escrow Payout
                </button>
            <?php else: ?>
                <button type="button" class="admin-action-btn" disabled style="width:100%; justify-content:center; padding:0.5rem 1rem; font-size:0.85rem; opacity:0.5; cursor:not-allowed; border-radius:10px;">
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

<!-- Cash Flow & Funds Distribution Visual Health Bar -->
<?php if ($totalTracked > 0): ?>
<section class="host-cashflow-card">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.65rem; flex-wrap:wrap; gap:0.5rem;">
        <span style="font-size:0.82rem; font-weight:800; color:rgb(var(--muted)); text-transform:uppercase; letter-spacing:0.04em;">📊 Funds Distribution &amp; Cash Flow Health</span>
        <span style="font-size:0.82rem; font-weight:800; color:rgb(var(--text));">Total Tracked: <?= format_price($totalTracked) ?></span>
    </div>

    <!-- Segmented Progress Bar -->
    <div class="host-cashflow-bar">
        <?php if ($disbursedPct > 0): ?>
            <div style="width:<?= $disbursedPct ?>%; background:#0284c7;" title="Disbursed: <?= $disbursedPct ?>% (<?= format_price($disbursedPayouts) ?>)"></div>
        <?php endif; ?>
        <?php if ($availablePct > 0): ?>
            <div style="width:<?= $availablePct ?>%; background:#10b981;" title="Available: <?= $availablePct ?>% (<?= format_price($availableBalance) ?>)"></div>
        <?php endif; ?>
        <?php if ($escrowPct > 0): ?>
            <div style="width:<?= $escrowPct ?>%; background:#ea580c;" title="Escrow: <?= $escrowPct ?>% (<?= format_price($escrowRevenue) ?>)"></div>
        <?php endif; ?>
        <?php if ($pendingPct > 0): ?>
            <div style="width:<?= $pendingPct ?>%; background:#d97706;" title="Pending: <?= $pendingPct ?>% (<?= format_price($pendingPayouts) ?>)"></div>
        <?php endif; ?>
    </div>

    <!-- Legend -->
    <div class="host-cashflow-legend">
        <div style="display:flex; align-items:center; gap:0.35rem;">
            <span style="width:9px; height:9px; border-radius:50%; background:#0284c7;"></span>
            <span style="color:rgb(var(--muted));">Disbursed:</span>
            <strong><?= $disbursedPct ?>%</strong>
        </div>
        <div style="display:flex; align-items:center; gap:0.35rem;">
            <span style="width:9px; height:9px; border-radius:50%; background:#10b981;"></span>
            <span style="color:rgb(var(--muted));">Available:</span>
            <strong><?= $availablePct ?>%</strong>
        </div>
        <div style="display:flex; align-items:center; gap:0.35rem;">
            <span style="width:9px; height:9px; border-radius:50%; background:#ea580c;"></span>
            <span style="color:rgb(var(--muted));">In Escrow:</span>
            <strong><?= $escrowPct ?>%</strong>
        </div>
        <?php if ($pendingPct > 0): ?>
            <div style="display:flex; align-items:center; gap:0.35rem;">
                <span style="width:9px; height:9px; border-radius:50%; background:#d97706;"></span>
                <span style="color:rgb(var(--muted));">Pending Review:</span>
                <strong><?= $pendingPct ?>%</strong>
            </div>
        <?php endif; ?>
    </div>
</section>
<?php endif; ?>

<!-- Visual Analytics Grid (2 Columns: Monthly Trends & Tier Breakdown) -->
<section class="host-analytics-grid">
    <!-- Column 1: 6-Month Sales Trend Chart -->
    <article class="form-card" style="padding:1.35rem 1.5rem; border-radius:20px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.25rem;">
            <div>
                <h3 style="margin:0; font-size:1.05rem; font-weight:800; color:rgb(var(--text));">📈 6-Month Sales Trend</h3>
                <p style="margin:0.2rem 0 0; font-size:0.8rem; color:rgb(var(--muted));">Monthly revenue velocity &amp; ticket volume</p>
            </div>
            <span style="font-size:0.75rem; background:rgba(16, 185, 129, 0.12); color:#10b981; font-weight:800; padding:2px 8px; border-radius:20px;">
                <?= $monthlyTicketsSum ?> tickets
            </span>
        </div>

        <?php if (!empty($monthlyTrends)): ?>
            <div style="display:flex; align-items:flex-end; justify-content:space-between; height:140px; gap:0.6rem; padding-top:1.5rem; border-bottom:1px solid rgba(var(--muted), 0.15);">
                <?php foreach ($monthlyTrends as $mt): ?>
                    <?php
                        $heightPct = $maxMonthlyRev > 0 ? max(8, round(($mt['gross'] / $maxMonthlyRev) * 100)) : 8;
                        $isCurrent = $mt['key'] === date('Y-m');
                    ?>
                    <div style="flex:1; display:flex; flex-direction:column; align-items:center; height:100%; justify-content:flex-end;">
                        <span style="font-size:0.68rem; color:rgb(var(--muted)); font-weight:600; margin-bottom:4px;"><?= $mt['tickets'] > 0 ? $mt['tickets'] : '—' ?></span>
                        <div style="width:100%; max-width:36px; height:<?= $heightPct ?>%; background:<?= $isCurrent ? 'linear-gradient(180deg, #10b981, #059669)' : 'rgba(var(--brand), 0.35)' ?>; border-radius:6px 6px 2px 2px; transition:all 0.2s;" title="<?= e($mt['label']) ?>: <?= format_price($mt['gross']) ?> (<?= $mt['tickets'] ?> tickets)"></div>
                        <span style="font-size:0.72rem; font-weight:<?= $isCurrent ? '800' : '600' ?>; color:<?= $isCurrent ? 'rgb(var(--brand))' : 'rgb(var(--muted))' ?>; margin-top:6px;"><?= e($mt['short_label']) ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
            <div style="display:flex; justify-content:space-between; font-size:0.78rem; color:rgb(var(--muted)); margin-top:0.65rem; flex-wrap:wrap; gap:0.4rem;">
                <span>Total 6-Month Gross: <strong><?= format_price($monthlyGrossSum) ?></strong></span>
                <span>Net: <strong><?= format_price($monthlyNetSum) ?></strong></span>
            </div>
        <?php else: ?>
            <p style="color:rgb(var(--muted)); font-size:0.85rem; text-align:center; padding:2rem 0;">No sales recorded in the last 6 months.</p>
        <?php endif; ?>
    </article>

    <!-- Column 2: Ticket Tier Breakdown -->
    <article class="form-card" style="padding:1.35rem 1.5rem; border-radius:20px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.25rem;">
            <div>
                <h3 style="margin:0; font-size:1.05rem; font-weight:800; color:rgb(var(--text));">🏷️ Top Ticket Tiers</h3>
                <p style="margin:0.2rem 0 0; font-size:0.8rem; color:rgb(var(--muted));">Sales distribution across ticket packages</p>
            </div>
        </div>

        <?php if (!empty($tierBreakdown)): ?>
            <div style="display:flex; flex-direction:column; gap:0.95rem;">
                <?php foreach ($tierBreakdown as $tb): ?>
                    <?php $tierPct = $maxTierRev > 0 ? round(($tb['total_revenue'] / $maxTierRev) * 100) : 0; ?>
                    <div>
                        <div style="display:flex; justify-content:space-between; font-size:0.82rem; font-weight:600; margin-bottom:3px; flex-wrap:wrap; gap:0.35rem;">
                            <span style="color:rgb(var(--text)); font-weight:700; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; max-width:200px;"><?= e($tb['tier_name']) ?></span>
                            <span style="color:rgb(var(--muted));"><?= number_format($tb['tickets_sold']) ?> sold · <strong style="color:rgb(var(--text));"><?= format_price($tb['total_revenue']) ?></strong></span>
                        </div>
                        <div style="width:100%; height:6px; background:rgba(var(--muted), 0.12); border-radius:3px; overflow:hidden;">
                            <div style="width:<?= $tierPct ?>%; height:100%; background:linear-gradient(90deg, #6366f1, #a855f7); border-radius:3px;"></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p style="color:rgb(var(--muted)); font-size:0.85rem; text-align:center; padding:2rem 0;">No ticket tier data available yet.</p>
        <?php endif; ?>
    </article>
</section>

<!-- Financials & Transaction History Navigation Banner -->
<section class="host-financials-banner" style="background: linear-gradient(135deg, rgba(16, 185, 129, 0.08) 0%, rgba(59, 130, 246, 0.06) 100%); border: 1px solid rgba(16, 185, 129, 0.22); border-radius: 18px; padding: 1.2rem 1.5rem; margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
    <div style="display:flex; align-items:center; gap:0.95rem;">
        <div style="width:44px; height:44px; border-radius:12px; background:rgba(16, 185, 129, 0.15); display:flex; align-items:center; justify-content:center; font-size:1.4rem; flex-shrink:0;">
            💳
        </div>
        <div>
            <h4 style="margin:0 0 0.2rem; font-size:1.05rem; font-weight:800; color:rgb(var(--text));">Detailed Earnings &amp; Withdrawal History</h4>
            <p style="margin:0; font-size:0.82rem; color:rgb(var(--muted));">Track exact ticket revenues per event, 5% fee deductions, escrow releases, and auto-settlements.</p>
        </div>
    </div>
    <a href="{{ route('host/financials') }}" class="host-hero-btn host-hero-btn--primary">
        <span>📊</span> View Full Financials →
    </a>
</section>

<!-- Events Section -->
<section class="form-card" style="border-radius:22px; padding:1.5rem;">
    <div class="card-header" style="margin-bottom:1.25rem; border-bottom:none;">
        <h2 style="font-size:1.3rem; margin:0;"><?= !empty($isAdmin) ? 'All Platform Events' : 'Your Events' ?></h2>
    </div>

    <!-- Category Filter Tabs -->
    <div class="dashboard-tabs">
        <button class="tab-btn active" data-filter="all">All Events <span class="tab-badge" id="badge-all">0</span></button>
        <button class="tab-btn" data-filter="live">🔴 Live Now <span class="tab-badge" id="badge-live">0</span></button>
        <button class="tab-btn" data-filter="upcoming">📅 Upcoming <span class="tab-badge" id="badge-upcoming">0</span></button>
        <button class="tab-btn" data-filter="past">✓ Past / Completed <span class="tab-badge" id="badge-past">0</span></button>
        <?php if (!empty($isAdmin)): ?>
            <button class="tab-btn" data-filter="mine">🙋 My Events <span class="tab-badge" id="badge-mine">0</span></button>
            <button class="tab-btn" data-filter="others">👥 Other Hosts <span class="tab-badge" id="badge-others">0</span></button>
        <?php endif; ?>
    </div>

    <!-- Redesigned Grid of Cards -->
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
            $percent = $capacity > 0 ? ($sold / $capacity) * 100 : 0;
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
                        <span style="position:absolute; top:0.5rem; right:0.5rem; background:rgba(16,185,129,0.92); color:#fff; font-size:0.7rem; font-weight:700; padding:0.2rem 0.55rem; border-radius:6px; backdrop-filter:blur(4px); letter-spacing:0.02em;">Your Event</span>
                    <?php endif; ?>
                </div>
                <div class="event-card-content">
                    <h3>{{ $event['name'] }}</h3>
                    <?php if (!empty($isAdmin) && !$isOwnEvent): ?>
                        <div style="display:inline-flex; align-items:center; gap:0.35rem; background:rgba(99,102,241,0.08); border:1px solid rgba(99,102,241,0.18); color:#6366f1; font-size:0.75rem; font-weight:600; padding:0.2rem 0.6rem; border-radius:6px; margin-bottom:0.35rem; max-width:100%; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                            <span style="font-size:0.8rem;">👤</span>
                            <?= e($event['organizer_name'] ?? 'Unknown') ?>
                            <span style="opacity:0.5;">·</span>
                            <span style="opacity:0.7; font-weight:500;"><?= e($event['organizer_email'] ?? '') ?></span>
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
                        <div class="progress-fill" style="width: <?= $percent ?>%; {{ $status === 'live' ? 'background: #ef4444;' : ($status === 'past' ? 'background: rgb(var(--muted));' : '') }}"></div>
                    </div>
                    <div class="card-actions">
                        @if($status === 'past')
                            <a href="{{ route('host/event/management/' . $event['id']) }}" class="host-action-btn summary-btn">
                                <span>📊</span> Summary
                            </a>
                            <form method="POST" action="{{ route('host/event/duplicate/' . $event['id']) }}" style="display:contents;" onsubmit="return confirm('Clone this event? A new draft with the same tickets and settings will be created for you.');">
                                <?= csrf_field() ?>
                                <button type="submit" class="host-action-btn" style="background: rgba(99, 102, 241, 0.08); color: #6366f1; border-color: rgba(99, 102, 241, 0.25);" title="Duplicate / Clone this past event">
                                    <span>📋</span> Clone
                                </button>
                            </form>
                        @else
                            <a href="{{ route('host/event/management/' . $event['id']) }}" class="host-action-btn manage-btn <?= $status === 'live' ? 'live-btn' : '' ?>">
                                <span>⚙️</span> Manage
                            </a>
                            <a href="{{ route('host/event/referrals/' . $event['id']) }}" class="host-action-btn" style="background: rgba(16, 185, 129, 0.08); color: #10b981; border-color: rgba(16, 185, 129, 0.25);" title="Referral Links & Promoters">
                                <span>🔗</span> Ref
                            </a>
                            <a href="{{ route('host/event/ticket-verification/' . $event['id']) }}" class="host-action-btn verify-btn <?= $status === 'live' ? 'live-primary-btn' : '' ?>">
                                <span>🎟️</span> Verify
                            </a>
                            <a href="{{ route('host/event/edit/' . $event['id']) }}" class="host-action-btn edit-btn">
                                <span>✏️</span> Edit
                            </a>
                            <form method="POST" action="{{ route('host/event/duplicate/' . $event['id']) }}" style="display:contents;" onsubmit="return confirm('Clone this event? A new draft with the same tickets and settings will be created for you.');">
                                <?= csrf_field() ?>
                                <button type="submit" class="host-action-btn" style="background: rgba(99, 102, 241, 0.08); color: #6366f1; border-color: rgba(99, 102, 241, 0.25);" title="Duplicate / Clone Event">
                                    <span>📋</span> Clone
                                </button>
                            </form>
                            <?php if (($sold ?? 0) == 0 && ($event['status'] ?? '') !== 'completed'): ?>
                                <form method="POST" action="{{ route('host/event/delete/' . $event['id']) }}" style="display:contents;" onsubmit="return confirm('Are you sure you want to delete this event? This action cannot be undone.');">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="host-action-btn delete-btn" title="Delete Event">
                                        <span>🗑️</span> Delete
                                    </button>
                                </form>
                            <?php endif; ?>
                        @endif
                    </div>
                </div>
            </article>
        @endforeach
    </div>

    <!-- Dynamic Pagination Controls -->
    <div id="events-pagination" class="pagination" style="margin-top: 1.75rem;" aria-label="Events pagination">
        <button class="page-button" id="prev-page">← Prev</button>
        <div id="page-numbers" style="display:inline-flex; gap:0.35rem;"></div>
        <button class="page-button" id="next-page">Next →</button>
    </div>

    <div id="empty-events" class="empty-state" style="display:none; text-align:center; padding:3rem 1rem;">
        <p style="color:rgb(var(--muted)); font-size:0.95rem; margin-bottom:1rem;">No events found for this filter category.</p>
        <a href="{{ route('host/event/create') }}" class="host-hero-btn host-hero-btn--primary">Create An Event</a>
    </div>
</section>

<!-- Recent Ticket Orders Activity Stream (Across Live, Upcoming & Completed Events) -->
<?php if (!empty($recentOrders)): ?>
<section class="form-card" style="margin-top: 2rem; margin-bottom: 2rem; padding:1.35rem 1.5rem; border-radius:22px;">
    <div class="card-header" style="margin-bottom:1rem; border-bottom:none; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:0.5rem;">
        <div>
            <h2 style="margin:0; font-size:1.2rem;">⚡ Recent Ticket Orders</h2>
            <p class="section-note" style="margin:0.2rem 0 0; font-size:0.82rem; color:rgb(var(--muted));">Latest attendee registrations across all your hosted events (active &amp; completed).</p>
        </div>
    </div>

    <div class="admin-table-wrap" style="overflow-x:auto;">
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
                    <tr>
                        <td>
                            <div style="display:flex; align-items:center; gap:0.6rem;">
                                <div style="width:34px; height:34px; border-radius:50%; background:linear-gradient(135deg, #6366f1, #a855f7); color:#fff; display:flex; align-items:center; justify-content:center; font-weight:800; font-size:0.78rem; flex-shrink:0;">
                                    <?= strtoupper(substr($ro['attendee_name'] ?: 'U', 0, 1)) ?>
                                </div>
                                <div>
                                    <strong style="color:rgb(var(--text)); font-size:0.9rem; display:block;"><?= e($ro['attendee_name']) ?></strong>
                                    <span style="font-size:0.76rem; color:rgb(var(--muted));"><?= e($ro['attendee_email']) ?></span>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div style="display:flex; align-items:center; gap:0.4rem; flex-wrap:wrap;">
                                <strong style="color:rgb(var(--text)); font-size:0.88rem;"><?= e($ro['event_name']) ?></strong>
                                <?php if ($isCompletedEvent): ?>
                                    <span style="background:rgba(16, 185, 129, 0.1); color:#059669; font-size:0.7rem; font-weight:700; padding:1px 7px; border-radius:4px;">✓ Completed</span>
                                <?php else: ?>
                                    <span style="background:rgba(59, 130, 246, 0.1); color:#2563eb; font-size:0.7rem; font-weight:700; padding:1px 7px; border-radius:4px;">📅 Active</span>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td>
                            <span style="background:rgba(99, 102, 241, 0.1); color:#6366f1; font-weight:700; font-size:0.74rem; padding:2px 8px; border-radius:6px;">
                                <?= e($ro['tier_name']) ?>
                            </span>
                        </td>
                        <td>
                            <strong style="color:rgb(var(--text)); font-size:0.92rem;"><?= format_price($ro['price']) ?></strong>
                        </td>
                        <td>
                            <?php if (!empty($ro['checked_in'])): ?>
                                <span style="background:rgba(16, 185, 129, 0.12); color:#059669; font-size:0.74rem; font-weight:800; padding:2px 8px; border-radius:20px; display:inline-flex; align-items:center; gap:3px;">
                                    ✓ Checked In
                                </span>
                            <?php else: ?>
                                <span style="background:rgba(var(--muted), 0.1); color:rgb(var(--muted)); font-size:0.74rem; font-weight:600; padding:2px 8px; border-radius:20px;">
                                    ⏳ Not Yet
                                </span>
                            <?php endif; ?>
                        </td>
                        <td style="text-align:right; font-size:0.82rem; color:rgb(var(--muted)); white-space:nowrap;">
                            <?= $purchasedTime ? date('M d, Y • g:i A', $purchasedTime) : '—' ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
<?php endif; ?>

<!-- Recent Withdrawals & Payouts Widget -->
<?php if (!empty($recentPayouts)): ?>
<section class="form-card" style="margin-top: 2rem; margin-bottom: 2rem; padding:1.35rem 1.5rem; border-radius:22px;">
    <div class="card-header" style="margin-bottom:1rem; border-bottom:none; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:0.5rem;">
        <div>
            <h2 style="margin:0; font-size:1.2rem;">Recent Withdrawals &amp; Payouts</h2>
            <p class="section-note" style="margin:0.2rem 0 0; font-size:0.82rem; color:rgb(var(--muted));">Your latest withdrawal requests and settlements.</p>
        </div>
        <a href="{{ route('host/financials') }}" style="font-size:0.85rem; font-weight:700; color:rgb(var(--brand)); text-decoration:none; display:inline-flex; align-items:center; gap:0.25rem;">
            View All Financials (<?= count($recentPayouts) ?>) →
        </a>
    </div>

    <div class="admin-table-wrap" style="overflow-x:auto;">
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
                        <td><strong style="font-family:monospace; color:rgb(var(--text)); font-size:0.9rem;">#<?= e($rp['id']) ?></strong></td>
                        <td><strong style="color:rgb(var(--text)); font-size:0.88rem;"><?= e(!empty($rp['event_name']) ? $rp['event_name'] : 'General Balance') ?></strong></td>
                        <td>
                            <?php if (($rp['type'] ?? '') === 'escrow'): ?>
                                <span style="background:rgba(251, 146, 60, 0.15); color:#ea580c; border:1px solid rgba(251, 146, 60, 0.3); font-size:0.72rem; font-weight:800; padding:2px 7px; border-radius:6px;">⚡ Escrow</span>
                            <?php elseif (($rp['type'] ?? '') === 'auto_completed'): ?>
                                <span style="background:rgba(59, 130, 246, 0.12); color:#2563eb; border:1px solid rgba(59, 130, 246, 0.3); font-size:0.72rem; font-weight:800; padding:2px 7px; border-radius:6px;">🤖 Auto</span>
                            <?php else: ?>
                                <span style="background:rgba(16, 185, 129, 0.1); color:#059669; border:1px solid rgba(16, 185, 129, 0.2); font-size:0.72rem; font-weight:800; padding:2px 7px; border-radius:6px;">✅ Standard</span>
                            <?php endif; ?>
                        </td>
                        <td><strong style="color:rgb(var(--text)); font-size:0.95rem;"><?= format_price($rp['amount']) ?></strong></td>
                        <td>
                            <?php if ($isCompleted): ?>
                                <span style="background:rgba(16, 185, 129, 0.12); color:#059669; font-size:0.74rem; font-weight:700; padding:2px 8px; border-radius:20px;">✓ Disbursed</span>
                            <?php elseif ($isFailed): ?>
                                <span style="background:rgba(239, 68, 68, 0.12); color:#dc2626; font-size:0.74rem; font-weight:700; padding:2px 8px; border-radius:20px;">⚠️ Hold</span>
                            <?php else: ?>
                                <span style="background:rgba(245, 158, 11, 0.12); color:#b45309; font-size:0.74rem; font-weight:700; padding:2px 8px; border-radius:20px;">⏳ Pending</span>
                            <?php endif; ?>
                        </td>
                        <td style="text-align:right; font-size:0.82rem; color:rgb(var(--muted));"><?= date('M d, Y', strtotime($rp['created_at'])) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
<?php endif; ?>

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
                <div class="host-escrow-actions" style="display:flex; gap:0.75rem; justify-content:flex-end;">
                    <button type="button" id="escrow-modal-cancel" class="admin-action-btn" style="padding:0.6rem 1.25rem; font-size:0.88rem; border-radius:11px;">
                        Cancel
                    </button>
                    <button type="submit" id="escrow-confirm-btn" class="host-hero-btn host-hero-btn--primary" disabled style="padding:0.6rem 1.25rem; font-size:0.88rem; background:#ea580c; border:none; opacity:0.5; cursor:not-allowed;">
                        Accept &amp; Request Payout
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
@keyframes pulseDot { 0% { transform:scale(0.95); opacity:0.8; } 50% { transform:scale(1.3); opacity:1; } 100% { transform:scale(0.95); opacity:0.8; } }
@keyframes escrowFadeIn { from { opacity: 0; } to { opacity: 1; } }
@keyframes escrowSlideIn { from { transform: translateY(-10px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const escrowBtn = document.getElementById('escrow-payout-btn');
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