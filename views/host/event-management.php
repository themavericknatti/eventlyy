<?php

/**
 * Host Event Management View
 *
 * Comprehensive event analytics, attendee management, staff deployment, and unified control center.
 *
 * @package Evently
 * @subpackage Views/Host
 * @author Celio Natti
 * @version 2.0.0
 */

$siteName = site_setting('site_title', 'Eventlyy');

if (!isset($event)) {
    $event = (object) [
        'id' => null,
        'name' => '',
        'date' => '',
        'location' => '',
        'image' => '',
        'status' => '',
        'slug' => '',
        'custom_url' => '',
    ];
} elseif (is_array($event)) {
    $event = (object) $event;
}

$eventDate = strtotime($event->date ?? '');
$now = time();
$liveWindowEnd = $eventDate ? $eventDate + (event_completion_hours() * 3600) : 0;
$isCompleted = ($eventDate && $now > $liveWindowEnd) || in_array($event->status ?? '', ['completed', 'past']);
$isLiveNow = !$isCompleted && ($eventDate && $now >= $eventDate && $now <= $liveWindowEnd);
$isUpcoming = !$isCompleted && !$isLiveNow;
$eventImg = !empty($event->image) ? $event->image : 'https://images.unsplash.com/photo-1506157786151-b8491531f063?w=800&q=80&auto=format&fit=crop';

// Public Event URL & Dedicated Flyer URL
$publicUrl = url('event/' . (!empty($event->custom_url) ? $event->custom_url : ($event->slug ?? $event->id)));
$flyerUrl = event_flyer_url($event);
$isFlyerEnabled = !empty($event->flyer_page_enabled);

// Event statistics
$totalRevenue = $totalRevenue ?? 0;
$ticketsSold = $ticketsSold ?? 0;
$ticketsIssued = $ticketsIssued ?? 0;
$totalCapacity = $totalCapacity ?? 0;
$occupancyPercentage = $occupancyPercentage ?? 0;
$avgPrice = $avgPrice ?? 0;
$canDelete = ($ticketsSold == 0) && !$isCompleted && (($event->status ?? '') !== 'completed');
?>

@extends('layouts/host')

@section('title')
Manage <?= e($event->name) ?> - <?= e($siteName) ?> Host
@endsection

@section('content')
<div class="event-mgmt-wrapper" style="margin-bottom: 2.5rem;">

    <!-- ════════════════════════════════════════════════════════════════
         1. TOP CONTEXT BAR & BREADCRUMB
         ════════════════════════════════════════════════════════════════ -->
    <div class="mgmt-topbar">
        <a href="{{ route('host/dashboard') }}" class="back-link" style="display:inline-flex; align-items:center; gap:0.4rem; font-weight:700; text-decoration:none; color:rgb(var(--brand)); font-size:0.88rem;">
            ← Back to Dashboard
        </a>
        <div class="mgmt-topbar-actions">
            <a href="{{ route('host/event/accounting/' . $event->id) }}" class="admin-action-btn" style="background:rgba(11,115,102,0.12); color:#0B7366; border-color:rgba(11,115,102,0.3); font-weight:700;" title="View official financial accounting & settlement report">
                <span>📊</span> Accounting Report
            </a>
            <a href="<?= e($publicUrl) ?>" target="_blank" class="admin-action-btn" title="Open public event listing in new tab">
                <span>👁️</span> Public Page
            </a>
            <button type="button" class="admin-action-btn" id="copy-event-link-btn" data-url="<?= e($publicUrl) ?>" title="Copy main event link">
                <span>🔗</span> Copy Link
            </button>
            <?php if ($isFlyerEnabled): ?>
                <a href="<?= e($flyerUrl) ?>" target="_blank" class="admin-action-btn" style="background:rgba(99,102,241,0.12); color:#6366f1; border-color:rgba(99,102,241,0.3);" title="Open dedicated flyer ticket page">
                    <span>🎨</span> Flyer Page
                </a>
                <button type="button" class="admin-action-btn" id="copy-flyer-link-btn" data-url="<?= e($flyerUrl) ?>" title="Copy dedicated short flyer link">
                    <span>📋</span> Copy Flyer Link
                </button>
            <?php endif; ?>
        </div>
    </div>

    <!-- Admin Impersonation Notice (if Admin is managing another host's event) -->
    <?php if (!empty($isAdmin) && !empty($organizerName)): ?>
        <div class="mgmt-admin-notice" style="display:flex; align-items:center; gap:0.6rem; background:rgba(99,102,241,0.08); border:1px solid rgba(99,102,241,0.22); border-radius:14px; padding:0.65rem 1.1rem; margin-bottom:1.25rem;">
            <span style="font-size:1.15rem;">🛡️</span>
            <div style="font-size:0.85rem; line-height:1.45;">
                <strong style="color:#6366f1;">Super Admin Mode:</strong>
                <span> Managing event owned by <strong><?= e($organizerName) ?></strong> (<?= e($organizerEmail) ?>)</span>
            </div>
        </div>
    <?php endif; ?>

    <!-- ════════════════════════════════════════════════════════════════
         2. HERO EVENT IDENTITY & ACTION HUB
         ════════════════════════════════════════════════════════════════ -->
    <section class="form-card mgmt-hero-card">
        <div class="mgmt-hero-grid">
            <!-- Event Image Preview -->
            <div class="mgmt-hero-media">
                <img src="<?= e($eventImg) ?>" alt="<?= e($event->name) ?>" class="mgmt-hero-thumb" />
                <?php if ($isLiveNow): ?>
                    <span class="mgmt-live-chip">LIVE NOW</span>
                <?php endif; ?>
            </div>

            <!-- Event Details -->
            <div class="mgmt-hero-details">
                <div class="mgmt-badges-row">
                    <?php if ($isCompleted): ?>
                        <span class="mgmt-status-pill mgmt-pill--completed">🏁 Concluded &amp; Settled</span>
                    <?php elseif ($isLiveNow): ?>
                        <span class="mgmt-status-pill mgmt-pill--live">
                            <span class="pulse-dot"></span> 🔴 Live Happening Now
                        </span>
                    <?php else: ?>
                        <span class="mgmt-status-pill mgmt-pill--upcoming">📅 Upcoming Event</span>
                    <?php endif; ?>

                    <?php if (!empty($event->category)): ?>
                        <span class="mgmt-category-pill"><?= e($event->category) ?></span>
                    <?php endif; ?>

                    <!-- Countdown or Time capsule -->
                    <?php if ($isUpcoming && $eventDate): ?>
                        <?php 
                            $diffDays = ceil(($eventDate - $now) / 86400);
                            $countdownText = $diffDays <= 1 ? "Starts Today / Tomorrow" : "Starts in {$diffDays} days";
                        ?>
                        <span class="mgmt-countdown-pill">⏱️ <?= $countdownText ?></span>
                    <?php endif; ?>
                </div>

                <h1 class="mgmt-event-title"><?= e($event->name) ?></h1>
                
                <div class="mgmt-meta-items">
                    <span>🗓️ <?= $eventDate ? date('l, M d, Y • g:i A', $eventDate) : 'Date TBA' ?></span>
                    <span>📍 <?= e($event->location ?? 'Venue TBA') ?></span>
                </div>

                <!-- Primary Management Action Buttons -->
                <div class="mgmt-action-buttons">
                    <?php if (!$isCompleted): ?>
                        <a href="{{ route('host/event/edit/' . $event->id) }}" class="admin-action-btn primary" style="text-decoration:none;">
                            <span>✏️</span> Edit Details
                        </a>
                    <?php else: ?>
                        <button class="admin-action-btn" disabled style="opacity:0.5; cursor:not-allowed;" title="Completed events cannot be edited">
                            <span>✏️</span> Edit (Locked)
                        </button>
                    <?php endif; ?>

                    <a href="{{ route('host/event/ticket-verification/' . $event->id) }}" class="admin-action-btn" style="text-decoration:none; background:rgba(59,130,246,0.1); color:#2563eb; border-color:rgba(59,130,246,0.25);">
                        <span>🎟️</span> Ticket Scanner
                    </a>

                    <a href="{{ route('host/event/referrals/' . $event->id) }}" class="admin-action-btn" style="text-decoration:none; background:rgba(16,185,129,0.1); color:#10b981; border-color:rgba(16,185,129,0.25);">
                        <span>🔗</span> Promoters &amp; Ref
                    </a>

                    <a href="{{ route('host/event/accounting/' . $event->id) }}" class="admin-action-btn" style="text-decoration:none; background:rgba(11,115,102,0.1); color:#0B7366; border-color:rgba(11,115,102,0.25); font-weight:700;" title="View official financial accounting & settlement report">
                        <span>📊</span> Accounting Report
                    </a>

                    <form method="POST" action="{{ route('host/event/duplicate/' . $event->id) }}" style="display:inline;" onsubmit="return confirm('Duplicate this event? A new draft copy will be created with all ticket tiers and guests.');">
                        <?= csrf_field() ?>
                        <button type="submit" class="admin-action-btn" style="background:rgba(147,51,234,0.1); color:#9333ea; border-color:rgba(147,51,234,0.25);" title="Clone this event as a new draft">
                            <span>📋</span> Duplicate
                        </button>
                    </form>

                    <?php if ($isCompleted): ?>
                        <form method="POST" action="{{ route('host/event/management/' . $event->id . '/send-feedback') }}" style="display:inline;" onsubmit="return confirm('Send review and rating request email to all verified attendees?');">
                            <?= csrf_field() ?>
                            <button type="submit" class="admin-action-btn" style="background:rgba(245,158,11,0.12); color:#d97706; border-color:rgba(245,158,11,0.3);">
                                <span>⭐</span> Request Reviews
                            </button>
                        </form>
                    <?php else: ?>
                        <form method="POST" action="{{ route('host/event/management/' . $event->id . '/send-reminders') }}" style="display:inline;" onsubmit="return confirm('Broadcast an event reminder email to all registered attendees?');">
                            <?= csrf_field() ?>
                            <button type="submit" class="admin-action-btn" style="background:rgba(99,102,241,0.1); color:#6366f1; border-color:rgba(99,102,241,0.25);">
                                <span>📧</span> Send Reminder
                            </button>
                        </form>
                    <?php endif; ?>

                    <?php if ($eventDate && $now >= $eventDate && !$isCompleted): ?>
                        <form method="POST" action="{{ route('host/event/management/' . $event->id . '/complete') }}" style="display:inline;" onsubmit="return confirm('Mark this event as Completed? This will close sales and release funds.');">
                            <?= csrf_field() ?>
                            <button type="submit" class="admin-action-btn" style="background:rgba(16,185,129,0.15); color:#059669; border-color:rgba(16,185,129,0.3); font-weight:700;">
                                <span>✅</span> Mark Completed
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- ════════════════════════════════════════════════════════════════
         3. FOUR CORE KPIS & FINANCIAL METRICS STRIP
         ════════════════════════════════════════════════════════════════ -->
    <section class="mgmt-kpi-grid" style="margin-bottom:1.75rem;">
        <article class="mgmt-kpi-card" style="border-left:4px solid #10b981;">
            <span class="mgmt-kpi-label">Gross Revenue</span>
            <div class="mgmt-kpi-val" style="color:#10b981;"><?= format_price($totalRevenue) ?></div>
            <span class="mgmt-kpi-sub">From paid ticket orders</span>
        </article>

        <article class="mgmt-kpi-card" style="border-left:4px solid #3b82f6;">
            <span class="mgmt-kpi-label">Tickets Sold &amp; Capacity</span>
            <div class="mgmt-kpi-val"><?= number_format($ticketsSold) ?> <span style="font-size:0.9rem; color:rgb(var(--muted));">/ <?= number_format($totalCapacity) ?></span></div>
            <span class="mgmt-kpi-sub">
                Organic sales <?php if ($ticketsIssued > 0): ?><strong style="color:#10b981;">(+<?= number_format($ticketsIssued) ?> comped)</strong><?php endif; ?>
            </span>
        </article>

        <article class="mgmt-kpi-card" style="border-left:4px solid #8b5cf6;">
            <span class="mgmt-kpi-label">Seat Occupancy</span>
            <div class="mgmt-kpi-val"><?= number_format($occupancyPercentage, 1) ?>%</div>
            <div class="mgmt-kpi-progress">
                <div class="mgmt-kpi-progress-fill" style="width:<?= min(100, $occupancyPercentage) ?>%;"></div>
            </div>
        </article>

        <article class="mgmt-kpi-card" style="border-left:4px solid #f59e0b;">
            <span class="mgmt-kpi-label">Average Ticket Price</span>
            <div class="mgmt-kpi-val"><?= format_price($avgPrice) ?></div>
            <span class="mgmt-kpi-sub">Weighted tier average</span>
        </article>
    </section>

    <!-- ════════════════════════════════════════════════════════════════
         4. ORGANIZED TAB NAVIGATION
         ════════════════════════════════════════════════════════════════ -->
    <?php 
        $currentTab = $activeTab ?? ($_GET['tab'] ?? (!empty($search) || $page > 1 ? 'attendees' : 'overview'));
    ?>
    <div class="mgmt-tab-nav" style="margin-bottom:1.5rem;">
        <button type="button" class="mgmt-tab-btn <?= $currentTab === 'overview' ? 'active' : '' ?>" data-tab="overview">
            <span>📊</span> Overview &amp; Tiers
        </button>
        <button type="button" class="mgmt-tab-btn <?= $currentTab === 'attendees' ? 'active' : '' ?>" data-tab="attendees">
            <span>🎟️</span> Attendees &amp; Orders <span class="mgmt-tab-counter"><?= count($tickets) ?></span>
        </button>
        <button type="button" class="mgmt-tab-btn <?= $currentTab === 'staffing' ? 'active' : '' ?>" data-tab="staffing">
            <span>🛡️</span> Staff Support <span class="mgmt-tab-counter"><?= count($teamMembers ?? []) ?></span>
        </button>
        <button type="button" class="mgmt-tab-btn <?= $currentTab === 'reviews' ? 'active' : '' ?>" data-tab="reviews">
            <span>⭐</span> Reviews <span class="mgmt-tab-counter"><?= count($reviews ?? []) ?></span>
        </button>
        <button type="button" class="mgmt-tab-btn <?= $currentTab === 'settings' ? 'active' : '' ?>" data-tab="settings">
            <span>⚙️</span> Controls &amp; Danger Zone
        </button>
    </div>

    <!-- ════════════════════════════════════════════════════════════════
         TAB 1: OVERVIEW & TICKET TIERS BREAKDOWN
         ════════════════════════════════════════════════════════════════ -->
    <div class="mgmt-tab-panel <?= $currentTab === 'overview' ? 'active' : '' ?>" id="tab-overview">
        <div class="mgmt-grid-2col">
            <!-- Left: Occupancy Radial Gauge -->
            <section class="form-card" style="margin-bottom:0; padding:1.5rem; display:flex; flex-direction:column; justify-content:space-between;">
                <div style="margin-bottom:1rem;">
                    <h3 style="margin:0; font-size:1.1rem; font-weight:800;">Ticket Occupancy &amp; Fill Rate</h3>
                    <p style="margin:0.25rem 0 0; font-size:0.82rem; color:rgb(var(--muted));">Total seats booked vs total venue capacity</p>
                </div>

                <div class="occupancy-chart-grid" style="align-items:center; margin:1rem 0;">
                    <div class="radial-chart-container">
                        <svg class="radial-chart-svg" viewBox="0 0 100 100">
                            <circle class="radial-chart-bg" cx="50" cy="50" r="45"></circle>
                            <circle class="radial-chart-fill" cx="50" cy="50" r="45" style="stroke-dashoffset: <?= 283 - (283 * (min(100, $occupancyPercentage) / 100)) ?>;"></circle>
                        </svg>
                        <div class="radial-chart-center">
                            <span class="radial-chart-percent"><?= number_format($occupancyPercentage, 1) ?>%</span>
                            <span class="radial-chart-label">Occupancy</span>
                        </div>
                    </div>

                    <div style="flex:1; min-width:180px; display:flex; flex-direction:column; gap:0.65rem;">
                        <div class="mgmt-stat-mini-row">
                            <span style="color:rgb(var(--muted));">Total Capacity:</span>
                            <strong><?= number_format($totalCapacity) ?> seats</strong>
                        </div>
                        <div class="mgmt-stat-mini-row">
                            <span style="color:rgb(var(--muted));">Tickets Sold:</span>
                            <strong style="color:#10b981;"><?= number_format($ticketsSold) ?></strong>
                        </div>
                        <?php if ($ticketsIssued > 0): ?>
                            <div class="mgmt-stat-mini-row">
                                <span style="color:rgb(var(--muted));">Issued Comp Tickets:</span>
                                <strong style="color:#6366f1;"><?= number_format($ticketsIssued) ?></strong>
                            </div>
                        <?php endif; ?>
                        <div class="mgmt-stat-mini-row">
                            <span style="color:rgb(var(--muted));">Available Remaining:</span>
                            <strong><?= number_format(max(0, $totalCapacity - ($ticketsSold + $ticketsIssued))) ?> seats</strong>
                        </div>
                    </div>
                </div>

                <div style="padding-top:0.75rem; border-top:1px solid rgba(var(--muted), 0.1); display:flex; justify-content:space-between; align-items:center;">
                    <?php if (!$isCompleted): ?>
                        <button type="button" id="add-ticket-manual-btn" class="admin-action-btn primary" style="width:100%; justify-content:center; padding:0.6rem 1rem;">
                            <span>🎟️</span> Issue Complimentary / Offline Ticket
                        </button>
                    <?php else: ?>
                        <button type="button" class="admin-action-btn" disabled style="width:100%; justify-content:center; opacity:0.5; cursor:not-allowed;">
                            <span>🔒</span> Ticket Issuance Closed
                        </button>
                    <?php endif; ?>
                </div>
            </section>

            <!-- Right: Tier by Tier Breakdown -->
            <section class="form-card" style="margin-bottom:0; padding:1.5rem;">
                <div style="margin-bottom:1rem;">
                    <h3 style="margin:0; font-size:1.1rem; font-weight:800;">Ticket Tiers &amp; Pricing</h3>
                    <p style="margin:0.25rem 0 0; font-size:0.82rem; color:rgb(var(--muted));">Sales performance and revenue generated per tier</p>
                </div>

                <div style="display:flex; flex-direction:column; gap:0.85rem;">
                    <?php foreach ($ticketTypes as $tt): 
                        $tCap = (int)($tt['capacity'] ?? 0);
                        $tSold = (int)($tt['sold'] ?? 0);
                        $tIssued = (int)($tt['issued'] ?? 0);
                        $tPrice = (float)($tt['price'] ?? 0);
                        $tPercent = $tCap > 0 ? min(100, round((($tSold + $tIssued) / $tCap) * 100)) : 0;
                        $tRev = isset($tt['revenue']) ? (float)$tt['revenue'] : ($tSold * $tPrice);
                    ?>
                        <div class="mgmt-tier-item">
                            <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:0.4rem;">
                                <div>
                                    <strong style="font-size:0.95rem; color:var(--text-primary);"><?= e($tt['name']) ?></strong>
                                    <span style="font-size:0.82rem; font-weight:700; color:<?= $tPrice > 0 ? '#10b981' : 'rgb(var(--muted))' ?>; margin-left:0.4rem;">
                                        <?= $tPrice > 0 ? format_price($tPrice) : 'Free' ?>
                                    </span>
                                </div>
                                <div style="text-align:right;">
                                    <span style="font-size:0.88rem; font-weight:800; color:var(--text-primary);"><?= format_price($tRev) ?></span>
                                    <span style="font-size:0.75rem; color:rgb(var(--muted)); display:block;"><?= number_format($tSold) ?> / <?= number_format($tCap) ?> sold</span>
                                </div>
                            </div>
                            <div class="mgmt-tier-bar">
                                <div class="mgmt-tier-fill" style="width:<?= $tPercent ?>%;"></div>
                            </div>
                            <div style="display:flex; justify-content:space-between; font-size:0.75rem; color:rgb(var(--muted)); margin-top:0.3rem;">
                                <span><?= $tPercent ?>% occupancy</span>
                                <?php if ($tIssued > 0): ?>
                                    <span style="color:#6366f1; font-weight:600;">+<?= number_format($tIssued) ?> issued</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
        </div>
    </div>

    <!-- ════════════════════════════════════════════════════════════════
         TAB 2: ATTENDEE LIST & ORDERS
         ════════════════════════════════════════════════════════════════ -->
    <div class="mgmt-tab-panel <?= $currentTab === 'attendees' ? 'active' : '' ?>" id="tab-attendees">
        <section class="form-card" style="padding:1.5rem; border-radius:20px;">
            <!-- Header Toolbar -->
            <div class="mgmt-table-toolbar" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:1rem; margin-bottom:1.25rem; padding-bottom:1rem; border-bottom:1px solid rgba(var(--muted), 0.1);">
                <div>
                    <h3 style="margin:0; font-size:1.15rem; font-weight:800;">Registered Attendees</h3>
                    <p style="margin:0.25rem 0 0; font-size:0.82rem; color:rgb(var(--muted));">Manage admissions, gate check-ins, and ticket refunds</p>
                </div>
                <div style="display:flex; align-items:center; gap:0.65rem; flex-wrap:wrap;">
                    <!-- Dedicated Accounting Report Page -->
                    <a href="{{ route('host/event/accounting/' . $event->id) }}" class="admin-action-btn" style="text-decoration:none; font-size:0.82rem; padding:0.45rem 0.95rem; border-radius:10px; font-weight:700; background:rgba(11,115,102,0.12); color:#0B7366; border:1px solid rgba(11,115,102,0.3);" title="View official financial accounting & settlement report">
                        <span>📊</span> Accounting Report
                    </a>
                    <!-- Direct CSV Export -->
                    <a href="{{ route('host/event/management/' . $event->id . '/export') }}" class="admin-action-btn primary" style="text-decoration:none; font-size:0.82rem; padding:0.45rem 0.95rem; border-radius:10px; font-weight:700;">
                        <span>📥</span> Export CSV
                    </a>
                </div>
            </div>

            <!-- Pending Transactions / Payment Protection Alert -->
            <?php if (!empty($pendingTransactions)): ?>
                <div style="background: rgba(245, 158, 11, 0.08); border: 1.5px solid rgba(245, 158, 11, 0.35); border-radius: 14px; padding: 1rem 1.25rem; margin-bottom: 1.25rem;">
                    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:0.5rem; margin-bottom:0.75rem;">
                        <div style="display:flex; align-items:center; gap:0.5rem;">
                            <span style="font-size:1.2rem;">⚡</span>
                            <div>
                                <strong style="font-size:0.92rem; color:#b45309;">Pending Orders Detected (<?= count($pendingTransactions) ?>)</strong>
                                <span style="font-size:0.78rem; color:rgb(var(--muted)); display:block;">If an attendee completed checkout on Paystack, click verify below to instantly confirm and issue their tickets.</span>
                            </div>
                        </div>
                    </div>
                    <div style="display:flex; flex-direction:column; gap:0.5rem;">
                        <?php foreach ($pendingTransactions as $pTx): ?>
                            <div class="mgmt-pending-item" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:0.5rem; background:var(--bg-card, #fff); padding:0.6rem 0.85rem; border-radius:10px; border:1px solid rgba(var(--muted), 0.12);">
                                <div class="mgmt-pending-info" style="display:flex; align-items:center; gap:0.4rem; flex-wrap:wrap; min-width:0;">
                                    <strong style="font-size:0.86rem;"><?= e($pTx['payer_name'] ?: 'Guest') ?></strong>
                                    <span style="font-size:0.76rem; color:rgb(var(--muted));">(<?= e($pTx['payer_email']) ?>)</span>
                                    <span style="font-size:0.75rem; font-family:monospace; color:#6366f1; word-break:break-all;">#<?= e($pTx['reference']) ?></span>
                                    <span style="font-size:0.8rem; font-weight:700; color:rgb(var(--brand));"><?= format_price($pTx['total_amount']) ?></span>
                                </div>
                                <form method="POST" action="<?= route('host/event/transaction/' . $pTx['reference'] . '/verify') ?>" class="mgmt-pending-form" style="margin:0;">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="tab" value="attendees">
                                    <button type="submit" class="admin-action-btn" style="font-size:0.76rem; padding:0.35rem 0.75rem; background:rgba(99,102,241,0.12); color:#4f46e5; border:1px solid rgba(99,102,241,0.3); font-weight:700; border-radius:6px; cursor:pointer;">
                                        🔄 Verify with Paystack
                                    </button>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Search and Filter Bar -->
            <div class="mgmt-filter-row" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:0.75rem; margin-bottom:1.25rem;">
                <div style="flex:1; min-width:240px; max-width:400px;">
                    <form method="GET" action="" id="tickets-search-form" data-server-search="true" style="margin:0;">
                        <input type="hidden" name="tab" value="attendees">
                        <input id="search-tickets" name="search" type="text" placeholder="🔍 Search attendee name, email, or tier..." value="<?= e($search ?? '') ?>" class="mgmt-search-input" />
                    </form>
                </div>

                <!-- Status Filter Pills -->
                <div class="mgmt-status-filter-pills" id="attendee-status-pills">
                    <button type="button" class="status-pill-btn active" data-filter="all">All</button>
                    <button type="button" class="status-pill-btn" data-filter="checked">Checked In</button>
                    <button type="button" class="status-pill-btn" data-filter="awaiting">Awaiting Entry</button>
                    <button type="button" class="status-pill-btn" data-filter="comped">Comped (Free)</button>
                    <button type="button" class="status-pill-btn" data-filter="refunded">Refunded</button>
                </div>
            </div>

            <!-- Responsive Attendees Table / Cards Container -->
            <div class="mgmt-attendees-container">
                <?php if (empty($tickets)): ?>
                    <div class="empty-state" style="padding:3rem 1.5rem; text-align:center; color:rgb(var(--muted));">
                        <span style="font-size:2.5rem; display:block; margin-bottom:0.5rem;">🎟️</span>
                        <p style="font-weight:700; margin:0 0 0.25rem;">No attendee tickets found.</p>
                        <span style="font-size:0.82rem;">Tickets purchased by attendees will appear here automatically.</span>
                    </div>
                <?php else: ?>
                    <!-- Desktop / Large Tablet Table View -->
                    <div class="au-table-wrap mgmt-desktop-table">
                        <table class="au-table" id="tickets-table">
                            <thead>
                                <tr>
                                    <th>Attendee</th>
                                    <th>Ticket Tier</th>
                                    <th>Amount</th>
                                    <th>Purchase Date</th>
                                    <th>Gate Status</th>
                                    <th style="text-align:right;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($tickets as $t): 
                                    $isEventStarted = !empty($event->date) && strtotime($event->date) <= time();
                                    $isCheckedIn = !empty($t['checked_in']);
                                    $isRefunded = in_array($t['status'] ?? '', ['Cancelled', 'Refunded']);
                                    $isComped = ($t['status'] === 'Comped') || !empty($t['is_issued']);
                                    
                                    $filterAttr = $isRefunded ? 'refunded' : ($isCheckedIn ? 'checked' : ($isComped ? 'comped' : 'awaiting'));
                                ?>
                                <tr class="attendee-row" data-status="<?= $filterAttr ?>" data-ticket-id="<?= e($t['id']) ?>">
                                    <td>
                                        <div style="display:flex; align-items:center; gap:0.65rem;">
                                            <div class="attendee-avatar">
                                                <?= strtoupper(substr($t['attendee_name'] ?: 'A', 0, 1)) ?>
                                            </div>
                                            <div>
                                                <strong style="font-size:0.9rem; color:var(--text-primary); display:block;"><?= e($t['attendee_name']) ?></strong>
                                                <span style="font-size:0.75rem; color:rgb(var(--muted));"><?= e($t['attendee_email']) ?></span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="mgmt-tier-badge"><?= e($t['ticket_tier']) ?></span>
                                    </td>
                                    <td>
                                        <strong style="font-size:0.88rem;"><?= format_price($t['price']) ?></strong>
                                    </td>
                                    <td style="font-size:0.8rem; color:rgb(var(--muted)); white-space:nowrap;">
                                        <?= date('M d, Y', strtotime($t['purchase_date'])) ?>
                                    </td>
                                    <td>
                                        <?php if ($isRefunded): ?>
                                            <span class="au-status-badge au-status--suspended" id="attendee-status-badge-<?= e($t['id']) ?>">Refunded</span>
                                        <?php elseif ($isCheckedIn): ?>
                                            <span class="au-status-badge au-status--active" id="attendee-status-badge-<?= e($t['id']) ?>">
                                                ✓ In (<?= date('H:i', strtotime($t['checked_in_at'])) ?>)
                                            </span>
                                        <?php else: ?>
                                            <span class="au-status-badge au-status--attendee" id="attendee-status-badge-<?= e($t['id']) ?>">Awaiting Entry</span>
                                        <?php endif; ?>
                                        <?php if (!empty($t['is_issued'])): ?>
                                            <span style="font-size:0.68rem; font-weight:700; color:#6366f1; display:block; margin-top:2px;">Issued Comp</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="text-align:right;">
                                        <div style="display:inline-flex; gap:0.35rem; align-items:center; justify-content:flex-end;">
                                            <a href="{{ route('host/event/ticket/' . $t['id']) }}" class="admin-action-btn" style="padding:0.3rem 0.65rem; font-size:0.76rem; border-radius:6px; font-weight:700;">
                                                View
                                            </a>

                                            <?php if (!$isRefunded): ?>
                                                <?php if ($isCheckedIn): ?>
                                                    <form method="POST" action="<?= route('host/event/ticket/' . $t['id'] . '/action') ?>" class="attendee-checkin-form" data-ticket-id="<?= e($t['id']) ?>" style="display:inline;">
                                                        <?= csrf_field() ?>
                                                        <input type="hidden" name="tab" value="attendees">
                                                        <input type="hidden" name="action" value="uncheck">
                                                        <button type="submit" class="admin-action-btn" style="padding:0.3rem 0.65rem; font-size:0.76rem; border-radius:6px; background:rgba(245,158,11,0.12); color:#d97706; font-weight:700; border:1px solid rgba(245,158,11,0.25);" title="Undo check-in">
                                                            ↩️ Uncheck
                                                        </button>
                                                    </form>
                                                <?php else: ?>
                                                    <form method="POST" action="<?= route('host/event/ticket/' . $t['id'] . '/action') ?>" class="attendee-checkin-form" data-ticket-id="<?= e($t['id']) ?>" style="display:inline;">
                                                        <?= csrf_field() ?>
                                                        <input type="hidden" name="tab" value="attendees">
                                                        <input type="hidden" name="action" value="check_in">
                                                        <button type="submit" class="admin-action-btn primary" style="padding:0.3rem 0.65rem; font-size:0.76rem; border-radius:6px; font-weight:700;" title="Check in this attendee">
                                                            🎟️ Check In
                                                        </button>
                                                    </form>
                                                <?php endif; ?>

                                                <?php if (!$isCheckedIn && !$isEventStarted): ?>
                                                    <form method="POST" action="<?= route('host/event/ticket/' . $t['id'] . '/action') ?>" class="attendee-refund-form" data-ticket-id="<?= e($t['id']) ?>" style="display:inline;" onsubmit="return confirm('Are you sure you want to refund this ticket?');">
                                                        <?= csrf_field() ?>
                                                        <input type="hidden" name="tab" value="attendees">
                                                        <input type="hidden" name="action" value="refund">
                                                        <button type="submit" class="admin-action-btn danger" style="padding:0.3rem 0.65rem; font-size:0.76rem; border-radius:6px; font-weight:700;">
                                                            Refund
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Tablet & Mobile Cards View -->
                    <div class="au-cards-list mgmt-mobile-cards">
                        <?php foreach ($tickets as $t): 
                            $isEventStarted = !empty($event->date) && strtotime($event->date) <= time();
                            $isCheckedIn = !empty($t['checked_in']);
                            $isRefunded = in_array($t['status'] ?? '', ['Cancelled', 'Refunded']);
                            $isComped = ($t['status'] === 'Comped') || !empty($t['is_issued']);
                            
                            $filterAttr = $isRefunded ? 'refunded' : ($isCheckedIn ? 'checked' : ($isComped ? 'comped' : 'awaiting'));
                        ?>
                        <div class="attendee-card attendee-row" data-status="<?= $filterAttr ?>" data-ticket-id="<?= e($t['id']) ?>">
                            <!-- Card Header: Avatar, Name, Email, Tier -->
                            <div class="attendee-card-header">
                                <div class="attendee-card-user">
                                    <div class="attendee-avatar">
                                        <?= strtoupper(substr($t['attendee_name'] ?: 'A', 0, 1)) ?>
                                    </div>
                                    <div class="attendee-user-info">
                                        <strong class="attendee-user-name"><?= e($t['attendee_name']) ?></strong>
                                        <span class="attendee-user-email"><?= e($t['attendee_email']) ?></span>
                                    </div>
                                </div>
                                <span class="mgmt-tier-badge"><?= e($t['ticket_tier']) ?></span>
                            </div>

                            <!-- Card Meta Grid: Code, Price, Date, Gate Status -->
                            <div class="attendee-card-meta-grid">
                                <div class="attendee-meta-cell">
                                    <span class="meta-label">Ticket Code</span>
                                    <span class="meta-value font-mono">#<?= e($t['id']) ?></span>
                                </div>
                                <div class="attendee-meta-cell">
                                    <span class="meta-label">Amount</span>
                                    <span class="meta-value highlight"><?= format_price($t['price']) ?></span>
                                </div>
                                <div class="attendee-meta-cell">
                                    <span class="meta-label">Date</span>
                                    <span class="meta-value"><?= date('M d, Y', strtotime($t['purchase_date'])) ?></span>
                                </div>
                                <div class="attendee-meta-cell">
                                    <span class="meta-label">Gate Status</span>
                                    <div class="meta-value">
                                        <?php if ($isRefunded): ?>
                                            <span class="au-status-badge au-status--suspended" id="mobile-status-badge-<?= e($t['id']) ?>">Refunded</span>
                                        <?php elseif ($isCheckedIn): ?>
                                            <span class="au-status-badge au-status--active" id="mobile-status-badge-<?= e($t['id']) ?>">
                                                ✓ In (<?= date('H:i', strtotime($t['checked_in_at'])) ?>)
                                            </span>
                                        <?php else: ?>
                                            <span class="au-status-badge au-status--attendee" id="mobile-status-badge-<?= e($t['id']) ?>">Awaiting Entry</span>
                                        <?php endif; ?>
                                        <?php if (!empty($t['is_issued'])): ?>
                                            <span class="issued-comp-pill">Issued Comp</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Card Actions -->
                            <div class="attendee-card-actions">
                                <a href="{{ route('host/event/ticket/' . $t['id']) }}" class="admin-action-btn flex-1 text-center" style="justify-content:center; padding:0.45rem 0.75rem; font-size:0.8rem; font-weight:700; text-decoration:none;">
                                    👁️ View
                                </a>

                                <?php if (!$isRefunded): ?>
                                    <?php if ($isCheckedIn): ?>
                                        <form method="POST" action="<?= route('host/event/ticket/' . $t['id'] . '/action') ?>" class="flex-1 attendee-checkin-form" data-ticket-id="<?= e($t['id']) ?>" style="margin:0;">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="tab" value="attendees">
                                            <input type="hidden" name="action" value="uncheck">
                                            <button type="submit" class="admin-action-btn w-full text-center" style="width:100%; justify-content:center; padding:0.45rem 0.75rem; font-size:0.8rem; background:rgba(245,158,11,0.12); color:#d97706; font-weight:700; border:1px solid rgba(245,158,11,0.25);" title="Undo check-in">
                                                ↩️ Uncheck
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <form method="POST" action="<?= route('host/event/ticket/' . $t['id'] . '/action') ?>" class="flex-1 attendee-checkin-form" data-ticket-id="<?= e($t['id']) ?>" style="margin:0;">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="tab" value="attendees">
                                            <input type="hidden" name="action" value="check_in">
                                            <button type="submit" class="admin-action-btn primary w-full text-center" style="width:100%; justify-content:center; padding:0.45rem 0.75rem; font-size:0.8rem; font-weight:700;" title="Check in this attendee">
                                                🎟️ Check In
                                            </button>
                                        </form>
                                    <?php endif; ?>

                                    <?php if (!$isCheckedIn && !$isEventStarted): ?>
                                        <form method="POST" action="<?= route('host/event/ticket/' . $t['id'] . '/action') ?>" class="attendee-refund-form" data-ticket-id="<?= e($t['id']) ?>" style="margin:0;" onsubmit="return confirm('Are you sure you want to refund this ticket?');">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="tab" value="attendees">
                                            <input type="hidden" name="action" value="refund">
                                            <button type="submit" class="admin-action-btn danger" style="padding:0.45rem 0.75rem; font-size:0.8rem; font-weight:700;">
                                                Refund
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Pagination -->
                    <?php if ($totalPages > 1): ?>
                    <div class="pagination" id="attendees-pagination" style="margin-top:1.25rem;">
                        <?php if ($page > 1): ?>
                            <a href="?tab=attendees&page=<?= $page - 1 ?>&search=<?= urlencode($search ?? '') ?>#tab-attendees" class="page-button" data-page="<?= $page - 1 ?>">← Prev</a>
                        <?php else: ?>
                            <button class="page-button" disabled>← Prev</button>
                        <?php endif; ?>

                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <?php if ($i === $page): ?>
                                <button class="page-button" aria-current="true"><?= $i ?></button>
                            <?php else: ?>
                                <a href="?tab=attendees&page=<?= $i ?>&search=<?= urlencode($search ?? '') ?>#tab-attendees" class="page-button" data-page="<?= $i ?>"><?= $i ?></a>
                            <?php endif; ?>
                        <?php endfor; ?>

                        <?php if ($page < $totalPages): ?>
                            <a href="?tab=attendees&page=<?= $page + 1 ?>&search=<?= urlencode($search ?? '') ?>#tab-attendees" class="page-button" data-page="<?= $page + 1 ?>">Next →</a>
                        <?php else: ?>
                            <button class="page-button" disabled>Next →</button>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </section>
    </div>

    <!-- ════════════════════════════════════════════════════════════════
         TAB 3: STAFF SUPPORT & ASSIGNED TEAM
         ════════════════════════════════════════════════════════════════ -->
    <div class="mgmt-tab-panel <?= $currentTab === 'staffing' ? 'active' : '' ?>" id="tab-staffing">
        <div class="mgmt-grid-2col">
            <!-- Staff Support Request Card -->
            <section class="form-card" style="margin-bottom:0; padding:1.5rem;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.25rem; flex-wrap:wrap; gap:0.75rem;">
                    <div>
                        <h3 style="margin:0; font-size:1.1rem; font-weight:800;">🛡️ Organization Staff Support</h3>
                        <p style="margin:0.25rem 0 0; font-size:0.82rem; color:rgb(var(--muted));">Request trained platform team members for your event</p>
                    </div>
                    <?php if (!empty($staffRequest)): ?>
                        <?php
                            $statusColors = [
                                'pending' => ['bg' => 'rgba(245, 158, 11, 0.12)', 'color' => '#f59e0b', 'label' => '⏳ Pending Review'],
                                'fulfilled' => ['bg' => 'rgba(16, 185, 129, 0.12)', 'color' => '#10b981', 'label' => '✅ Fulfilled'],
                                'partially_fulfilled' => ['bg' => 'rgba(59, 130, 246, 0.12)', 'color' => '#3b82f6', 'label' => '🔄 Partially Fulfilled'],
                                'declined' => ['bg' => 'rgba(239, 68, 68, 0.12)', 'color' => '#ef4444', 'label' => '❌ Declined'],
                            ];
                            $st = $statusColors[$staffRequest['status']] ?? $statusColors['pending'];
                        ?>
                        <span style="background:<?= $st['bg'] ?>; padding:0.35rem 0.8rem; border-radius:10px; font-size:0.8rem; font-weight:700; color:<?= $st['color'] ?>;">
                            <?= $st['label'] ?>
                        </span>
                    <?php endif; ?>
                </div>

                <?php if ($isCompleted): ?>
                    <p style="color:rgb(var(--muted)); font-size:0.88rem; text-align:center; padding:1.5rem 0;">Staffing requests are closed for completed events.</p>
                <?php else: ?>
                    <?php
                        $existingRoles = [];
                        if (!empty($staffRequest['roles_array'])) {
                            foreach ($staffRequest['roles_array'] as $r) {
                                $existingRoles[$r['slug']] = $r;
                            }
                        }
                        $allRoles = [
                            ['slug' => 'ticket-scanner', 'name' => 'Ticket Scanner', 'icon' => '🎫', 'desc' => 'Scan and validate attendee tickets at entry doors'],
                            ['slug' => 'organizer-staff', 'name' => 'Usher / Floor Guide', 'icon' => '🏷️', 'desc' => 'Guide attendees and manage seating/crowd flow'],
                            ['slug' => 'customer-support', 'name' => 'Customer Support Lead', 'icon' => '🎧', 'desc' => 'Resolve inquiries and help guests on-site'],
                            ['slug' => 'technical-ops', 'name' => 'Technical Operations', 'icon' => '⚙️', 'desc' => 'Manage sound, lighting, and stage equipment'],
                            ['slug' => 'finance-manager', 'name' => 'Box Office & Finance', 'icon' => '💼', 'desc' => 'Handle door cash ticket sales and reconciliation'],
                        ];
                    ?>
                    <form method="POST" action="/host/event/management/<?= $event->id ?>/request-staff">
                        <?= csrf_field() ?>
                        <input type="hidden" name="tab" value="staffing">
                        
                        <div style="display:flex; flex-direction:column; gap:0.65rem; margin-bottom:1rem;">
                            <?php foreach ($allRoles as $role): ?>
                                <?php
                                    $fieldKey = str_replace('-', '_', $role['slug']);
                                    $isChecked = isset($existingRoles[$role['slug']]);
                                    $qty = $isChecked ? (int)($existingRoles[$role['slug']]['quantity'] ?? 1) : 1;
                                ?>
                                <label class="mgmt-role-card <?= $isChecked ? 'active' : '' ?>">
                                    <input type="checkbox" name="role_<?= $fieldKey ?>" value="1" <?= $isChecked ? 'checked' : '' ?> onchange="let p=this.closest('.mgmt-role-card'); p.classList.toggle('active', this.checked); p.querySelector('.qty-box').style.display=this.checked?'flex':'none';">
                                    <div style="flex:1; min-width:0;">
                                        <div style="display:flex; justify-content:space-between; align-items:center;">
                                            <span style="font-weight:700; font-size:0.88rem;"><?= $role['icon'] ?> <?= e($role['name']) ?></span>
                                            <div class="qty-box" style="display:<?= $isChecked ? 'flex' : 'none' ?>; align-items:center; gap:0.35rem;">
                                                <span style="font-size:0.76rem; color:rgb(var(--muted));">Qty:</span>
                                                <input type="number" name="qty_<?= $fieldKey ?>" value="<?= $qty ?>" min="1" max="20" class="mgmt-qty-input" />
                                            </div>
                                        </div>
                                        <p style="margin:0.2rem 0 0; font-size:0.76rem; color:rgb(var(--muted)); line-height:1.35;"><?= e($role['desc']) ?></p>
                                    </div>
                                </label>
                            <?php endforeach; ?>
                        </div>

                        <div style="margin-bottom:1rem;">
                            <label style="font-size:0.82rem; font-weight:700; color:rgb(var(--muted)); display:block; margin-bottom:0.35rem;">Special Instructions / Venue Notes</label>
                            <textarea name="staff_notes" rows="2" placeholder="Provide any special instructions, arrival time, or equipment for assigned staff..." class="mgmt-notes-input"><?= e($staffRequest['notes'] ?? '') ?></textarea>
                        </div>

                        <button type="submit" class="admin-action-btn primary" style="width:100%; justify-content:center; padding:0.65rem 1rem;">
                            <?= !empty($staffRequest) ? '🔄 Update Staff Request' : '📨 Submit Staff Support Request' ?>
                        </button>
                    </form>
                <?php endif; ?>
            </section>

            <!-- Assigned Team Roster Table -->
            <section class="form-card" style="margin-bottom:0; padding:1.5rem;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.25rem;">
                    <div>
                        <h3 style="margin:0; font-size:1.1rem; font-weight:800;">👥 Assigned Team Members</h3>
                        <p style="margin:0.25rem 0 0; font-size:0.82rem; color:rgb(var(--muted));">Active personnel deployed to your event</p>
                    </div>
                    <span class="mgmt-tab-counter"><?= count($teamMembers ?? []) ?> members</span>
                </div>

                <?php if (empty($teamMembers ?? [])): ?>
                    <div class="empty-state" style="padding:2.5rem 1rem; text-align:center; color:rgb(var(--muted));">
                        <span style="font-size:2rem; display:block; margin-bottom:0.4rem;">👥</span>
                        <p style="margin:0; font-weight:600; font-size:0.88rem;">No team members assigned yet.</p>
                        <span style="font-size:0.78rem;">Submit a staff support request on the left to get platform team members assigned.</span>
                    </div>
                <?php else: ?>
                    <div style="display:flex; flex-direction:column; gap:0.65rem;">
                        <?php foreach ($teamMembers as $tm): 
                            $isChecked = !empty($tm['checked_in']);
                        ?>
                            <div class="mgmt-team-item" style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:0.75rem;">
                                <div style="display:flex; align-items:center; gap:0.65rem; flex:1; min-width:200px;">
                                    <div class="attendee-avatar" style="background:#10b981; color:#fff;">
                                        <?= strtoupper(substr($tm['staff_name'], 0, 1)) ?>
                                    </div>
                                    <div style="flex:1; min-width:0;">
                                        <strong style="font-size:0.88rem; color:var(--text-primary); display:block; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;"><?= e($tm['staff_name']) ?></strong>
                                        <span style="font-size:0.75rem; color:rgb(var(--muted)); display:block; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;"><?= e($tm['staff_email']) ?></span>
                                    </div>
                                </div>
                                <div style="display:flex; align-items:center; gap:0.5rem; flex-wrap:wrap;">
                                    <span class="mgmt-tier-badge" style="background:rgba(99,102,241,0.12); color:#6366f1;"><?= e($tm['role_name']) ?></span>
                                    
                                    <span class="host-staff-checkin-badge" id="host-badge-checkin-<?= (int)$tm['assignment_id'] ?>" style="font-size:0.72rem; font-weight:800; padding:0.25rem 0.65rem; border-radius:8px; <?= $isChecked ? 'background:rgba(16,185,129,0.14); color:#059669; border:1px solid rgba(16,185,129,0.3);' : 'background:rgba(100,116,139,0.12); color:rgb(100,116,139);' ?>">
                                        <?= $isChecked ? '● Checked In' . (!empty($tm['checked_in_at']) ? ' (' . date('H:i', strtotime($tm['checked_in_at'])) . ')' : '') : '○ Not Checked In' ?>
                                    </span>

                                    <button type="button" class="btn-host-toggle-checkin" data-assignment-id="<?= (int)$tm['assignment_id'] ?>" data-current="<?= $isChecked ? '1' : '0' ?>" style="padding:0.35rem 0.75rem; border-radius:8px; font-size:0.75rem; font-weight:800; cursor:pointer; border:none; transition:all 0.15s ease; <?= $isChecked ? 'background:rgba(239,68,68,0.12); color:#ef4444; border:1px solid rgba(239,68,68,0.25);' : 'background:#10b981; color:#fff;' ?>">
                                        <?= $isChecked ? 'Check Out' : '✓ Check In' ?>
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>
        </div>
    </div>

    <!-- ════════════════════════════════════════════════════════════════
         TAB 4: REVIEWS & ATTENDEE FEEDBACK
         ════════════════════════════════════════════════════════════════ -->
    <div class="mgmt-tab-panel <?= $currentTab === 'reviews' ? 'active' : '' ?>" id="tab-reviews">
        <section class="form-card" style="padding:1.5rem; border-radius:20px;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem; flex-wrap:wrap; gap:1rem; padding-bottom:1rem; border-bottom:1px solid rgba(var(--muted), 0.1);">
                <div>
                    <h3 style="margin:0; font-size:1.15rem; font-weight:800;">⭐ Attendee Ratings &amp; Feedback</h3>
                    <p style="margin:0.25rem 0 0; font-size:0.82rem; color:rgb(var(--muted));">Post-event feedback from verified ticket holders</p>
                </div>
                <?php if (!empty($reviews)): ?>
                    <div style="display:flex; align-items:center; gap:0.5rem; background:rgba(245,158,11,0.12); padding:0.45rem 0.95rem; border-radius:12px;">
                        <span style="font-size:1.25rem;">⭐</span>
                        <span style="font-size:1.15rem; font-weight:800; color:#d97706;"><?= number_format($avgRating ?? 0, 1) ?></span>
                        <span style="font-size:0.8rem; color:rgb(var(--muted));">/ 5.0 (<?= count($reviews) ?> reviews)</span>
                    </div>
                <?php endif; ?>
            </div>

            <?php if (empty($reviews)): ?>
                <div class="empty-state" style="text-align:center; padding:3rem 1.5rem; color:rgb(var(--muted));">
                    <span style="font-size:2.5rem; display:block; margin-bottom:0.5rem;">⭐</span>
                    <p style="font-weight:700; margin:0 0 0.25rem;">No reviews received yet.</p>
                    <span style="font-size:0.82rem;">
                        <?= $isCompleted ? 'Click "Request Reviews" in the top bar to email all attendees a feedback form.' : 'Once the event is marked completed, you can request ratings from attendees.' ?>
                    </span>
                </div>
            <?php else: ?>
                <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(300px, 1fr)); gap:1rem;">
                    <?php foreach ($reviews as $rev): ?>
                        <div class="mgmt-review-card">
                            <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:0.5rem;">
                                <div>
                                    <strong style="font-size:0.92rem; color:var(--text-primary);"><?= e($rev['attendee_name']) ?></strong>
                                    <span style="font-size:0.75rem; color:rgb(var(--muted)); display:block;"><?= date('M d, Y', strtotime($rev['created_at'])) ?></span>
                                </div>
                                <div style="color:#f59e0b; font-size:0.95rem; letter-spacing:1px;">
                                    <?= str_repeat('★', (int)$rev['rating']) ?><?= str_repeat('☆', 5 - (int)$rev['rating']) ?>
                                </div>
                            </div>
                            <?php if (!empty($rev['comment'])): ?>
                                <p style="margin:0; font-size:0.85rem; color:var(--text-primary); line-height:1.45; font-style:italic;">
                                    "<?= e($rev['comment']) ?>"
                                </p>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </div>

    <!-- ════════════════════════════════════════════════════════════════
         TAB 5: CONTROLS & SINGLE DANGER ZONE
         ════════════════════════════════════════════════════════════════ -->
    <div class="mgmt-tab-panel <?= $currentTab === 'settings' ? 'active' : '' ?>" id="tab-settings">
        <div class="mgmt-grid-2col">
            <!-- Dedicated Flyer Page & Lifecycle Controls -->
            <section class="form-card" style="margin-bottom:0; padding:1.5rem;">
                <h3 style="margin:0 0 0.5rem; font-size:1.1rem; font-weight:800;">⚙️ Event Controls &amp; Flyer Mode</h3>
                <p style="margin:0 0 1.25rem; font-size:0.82rem; color:rgb(var(--muted));">Manage dedicated flyer ticket pages, event configuration, and links.</p>

                <div style="display:flex; flex-direction:column; gap:0.85rem;">
                    <!-- Dedicated Flyer Page Toggle Box -->
                    <div style="padding:1rem; background:<?= $isFlyerEnabled ? 'rgba(99,102,241,0.06)' : 'rgba(var(--text), 0.02)' ?>; border-radius:14px; border:1.5px solid <?= $isFlyerEnabled ? 'rgba(99,102,241,0.3)' : 'rgba(var(--muted), 0.12)' ?>;">
                        <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:0.75rem; margin-bottom:0.65rem; flex-wrap:wrap;">
                            <div>
                                <div style="display:flex; align-items:center; gap:0.4rem;">
                                    <strong style="font-size:0.92rem; color:var(--text-primary);">🎨 Dedicated Flyer Page</strong>
                                    <span style="font-size:0.72rem; font-weight:800; padding:0.15rem 0.5rem; border-radius:10px; <?= $isFlyerEnabled ? 'background:rgba(16,185,129,0.15); color:#059669;' : 'background:rgba(var(--muted),0.15); color:rgb(var(--muted));' ?>">
                                        <?= $isFlyerEnabled ? '● Active &amp; Live' : '○ Disabled' ?>
                                    </span>
                                </div>
                                <p style="margin:0.25rem 0 0; font-size:0.78rem; color:rgb(var(--muted)); line-height:1.4;">
                                    A cinematic artwork-background ticket booth with clean short URL, optimized for sharing directly on WhatsApp, Instagram, TikTok, and X.
                                </p>
                            </div>

                            <form method="POST" action="{{ route('host/event/management/' . $event->id . '/toggle-flyer') }}" style="display:inline;">
                                <?= csrf_field() ?>
                                <input type="hidden" name="tab" value="settings">
                                <button type="submit" class="admin-action-btn <?= $isFlyerEnabled ? '' : 'primary' ?>" style="font-size:0.8rem; padding:0.4rem 0.85rem; border-radius:8px;">
                                    <?= $isFlyerEnabled ? 'Turn Off Flyer' : '⚡ Activate Flyer' ?>
                                </button>
                            </form>
                        </div>

                        <?php if ($isFlyerEnabled): ?>
                            <div style="display:flex; align-items:center; justify-content:space-between; gap:0.5rem; background:rgba(0,0,0,0.15); padding:0.5rem 0.75rem; border-radius:10px; border:1px solid rgba(99,102,241,0.2); flex-wrap:wrap;">
                                <span style="font-size:0.8rem; font-family:monospace; color:#818cf8; word-break:break-all;">
                                    <?= e($flyerUrl) ?>
                                </span>
                                <div style="display:inline-flex; gap:0.35rem;">
                                    <a href="<?= e($flyerUrl) ?>" target="_blank" class="admin-action-btn" style="padding:0.25rem 0.6rem; font-size:0.75rem; border-radius:6px; text-decoration:none;">
                                        👁️ View
                                    </a>
                                    <button type="button" class="admin-action-btn" onclick="navigator.clipboard.writeText('<?= addslashes($flyerUrl) ?>'); showToast('📋 Dedicated flyer link copied!');" style="padding:0.25rem 0.6rem; font-size:0.75rem; border-radius:6px;">
                                        📋 Copy
                                    </button>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div style="display:flex; justify-content:space-between; align-items:center; padding:0.85rem; background:rgba(var(--text), 0.02); border-radius:12px; border:1px solid rgba(var(--muted), 0.1);">
                        <div>
                            <strong style="font-size:0.88rem; display:block;">Edit Event Configuration</strong>
                            <span style="font-size:0.75rem; color:rgb(var(--muted));">Update venue, tiers, pricing, and guest lineups</span>
                        </div>
                        <?php if (!$isCompleted): ?>
                            <a href="{{ route('host/event/edit/' . $event->id) }}" class="admin-action-btn" style="text-decoration:none;">
                                ✏️ Edit
                            </a>
                        <?php else: ?>
                            <span style="font-size:0.78rem; color:rgb(var(--muted)); font-weight:700;">Locked</span>
                        <?php endif; ?>
                    </div>

                    <div style="display:flex; justify-content:space-between; align-items:center; padding:0.85rem; background:rgba(var(--text), 0.02); border-radius:12px; border:1px solid rgba(var(--muted), 0.1);">
                        <div>
                            <strong style="font-size:0.88rem; display:block;">Promoter &amp; Referral Program</strong>
                            <span style="font-size:0.75rem; color:rgb(var(--muted));">Generate commission tracking links for promoters</span>
                        </div>
                        <a href="{{ route('host/event/referrals/' . $event->id) }}" class="admin-action-btn" style="text-decoration:none;">
                            🔗 Promoters
                        </a>
                    </div>

                    <div style="display:flex; justify-content:space-between; align-items:center; padding:0.85rem; background:rgba(var(--text), 0.02); border-radius:12px; border:1px solid rgba(var(--muted), 0.1);">
                        <div>
                            <strong style="font-size:0.88rem; display:block;">Duplicate / Clone Event</strong>
                            <span style="font-size:0.75rem; color:rgb(var(--muted));">Create a new draft copy with all ticket tiers and guests</span>
                        </div>
                        <form method="POST" action="{{ route('host/event/duplicate/' . $event->id) }}" style="display:inline; margin:0;" onsubmit="return confirm('Duplicate this event? A new draft copy will be created with all ticket tiers and guests.');">
                            <?= csrf_field() ?>
                            <button type="submit" class="admin-action-btn" style="background:rgba(147,51,234,0.1); color:#9333ea; border-color:rgba(147,51,234,0.25); font-weight:700;">
                                📋 Clone Event
                            </button>
                        </form>
                    </div>
                </div>
            </section>

            <!-- SINGLE DANGER ZONE (THE ONLY DELETE BUTTON ON THIS PAGE) -->
            <section class="form-card" style="margin-bottom:0; padding:1.5rem; border:1.5px solid rgba(239,68,68,0.25); background:linear-gradient(135deg, rgba(239,68,68,0.02) 0%, rgba(var(--card), 1) 100%);">
                <div style="display:flex; align-items:center; gap:0.5rem; margin-bottom:0.5rem;">
                    <span style="font-size:1.2rem;">⚠️</span>
                    <h3 style="margin:0; font-size:1.1rem; font-weight:800; color:#ef4444;">Danger Zone</h3>
                </div>
                <p style="margin:0 0 1rem; font-size:0.82rem; color:rgb(var(--muted)); line-height:1.45;">
                    Permanent actions for this event. Event deletion is only available when no paid ticket orders have been placed and the event is not completed.
                </p>

                <?php if ($canDelete): ?>
                    <div style="padding:1rem; background:rgba(239,68,68,0.06); border-radius:12px; border:1px dashed rgba(239,68,68,0.3);">
                        <strong style="font-size:0.88rem; color:#dc2626; display:block; margin-bottom:0.25rem;">Delete This Event</strong>
                        <p style="margin:0 0 0.75rem; font-size:0.78rem; color:rgb(var(--muted));">Once deleted, all event details, custom URLs, and draft tiers will be permanently removed.</p>
                        
                        <form method="POST" action="{{ route('host/event/delete/' . $event->id) }}" onsubmit="return confirm('⚠️ ARE YOU ABSOLUTELY SURE?\n\nThis will permanently delete <?= addslashes($event->name) ?>. This action cannot be undone.');">
                            <?= csrf_field() ?>
                            <button type="submit" class="admin-action-btn danger" style="padding:0.6rem 1.25rem; font-weight:800; border-radius:10px; cursor:pointer;">
                                🗑️ Delete Event
                            </button>
                        </form>
                    </div>
                <?php else: ?>
                    <div style="padding:0.85rem 1rem; background:rgba(var(--muted),0.06); border-radius:12px; font-size:0.82rem; color:rgb(var(--muted)); line-height:1.45;">
                        🔒 <strong>Event Deletion Protected:</strong> This event cannot be deleted because 
                        <?= $isCompleted ? 'it has already been marked as completed.' : 'tickets have already been sold to registered attendees.' ?>
                    </div>
                <?php endif; ?>
            </section>
        </div>
    </div>

</div>

<!-- ════════════════════════════════════════════════════════════════
     5. MODAL (USED FOR MANUAL TICKET ISSUANCE & CONFIRMATIONS)
     ════════════════════════════════════════════════════════════════ -->
<div id="modal-backdrop" class="modal-backdrop hidden" aria-hidden="true">
    <div class="modal">
        <div class="modal-header">
            <h3 class="modal-title" id="modal-title">Modal Title</h3>
            <button type="button" id="modal-close" class="btn-link" aria-label="Close modal" style="font-size:1.4rem;line-height:1;padding:0;">✕</button>
        </div>
        <div class="modal-body" id="modal-body"></div>
        <div class="modal-actions">
            <button type="button" id="modal-cancel" class="admin-action-btn">Cancel</button>
            <button type="button" id="modal-confirm" class="admin-action-btn primary">Confirm</button>
        </div>
    </div>
</div>

<!-- Template: Issue Ticket Form -->
<template id="manual-ticket-modal-template">
    <form id="manual-ticket-form" method="POST" action="{{ route('host/event/management/' . ($event->id ?? '')) }}">
        <?= csrf_field() ?>
        <input type="hidden" name="tab" value="attendees">
        <p style="font-size:0.82rem; color:#64748b; margin:0 0 1rem; padding:0.65rem 0.85rem; background:rgba(16, 185, 129, 0.08); border:1px solid rgba(16, 185, 129, 0.2); border-radius:10px; line-height:1.45;">
            🏷️ This ticket will be marked as <strong style="color:#10b981;">Issued by Host</strong> and will provide instant door QR admission.
        </p>
        <label style="display:block; margin-bottom:0.75rem; font-size:0.85rem; font-weight:700;">Attendee Full Name
            <input type="text" name="attendee_name" required placeholder="Jane Doe" autocomplete="name" style="width:100%; padding:0.55rem 0.75rem; border:1px solid rgba(var(--muted),0.2); border-radius:8px; margin-top:0.25rem; box-sizing:border-box;">
        </label>
        <label style="display:block; margin-bottom:0.75rem; font-size:0.85rem; font-weight:700;">Attendee Email Address
            <input type="email" name="attendee_email" required placeholder="attendee@example.com" autocomplete="email" style="width:100%; padding:0.55rem 0.75rem; border:1px solid rgba(var(--muted),0.2); border-radius:8px; margin-top:0.25rem; box-sizing:border-box;">
        </label>
        <label style="display:block; margin-bottom:0.75rem; font-size:0.85rem; font-weight:700;">Select Ticket Tier
            <select name="ticket_type" required style="width:100%; padding:0.55rem 0.75rem; border:1px solid rgba(var(--muted),0.2); border-radius:8px; margin-top:0.25rem; box-sizing:border-box; background:var(--bg-card, #fff); color:var(--text-primary, #1e293b);">
                <option value="">Select tier…</option>
                <?php foreach($ticketTypes as $tt): 
                    $optPrice = ((float)$tt['price'] > 0) ? format_price($tt['price']) : 'Free';
                ?>
                    <option value="<?= e($tt['name']) ?>"><?= e($tt['name']) ?> (<?= $optPrice ?>)</option>
                <?php endforeach; ?>
            </select>
        </label>
        <label style="display:block; margin-bottom:0.5rem; font-size:0.85rem; font-weight:700;">Issuance Type
            <select name="payment_status" style="width:100%; padding:0.55rem 0.75rem; border:1px solid rgba(var(--muted),0.2); border-radius:8px; margin-top:0.25rem; box-sizing:border-box; background:var(--bg-card, #fff); color:var(--text-primary, #1e293b);">
                <option value="Comped">Complimentary (Free Admission)</option>
                <option value="Paid">Offline Cash Payment (Mark Paid)</option>
            </select>
        </label>
    </form>
</template>

<div id="toast-container" aria-live="polite" aria-atomic="true"></div>

<!-- ════════════════════════════════════════════════════════════════
     6. CSS STYLES FOR THE EVENT MANAGEMENT OVERHAUL
     ════════════════════════════════════════════════════════════════ -->
<style>
/* ════════════════════════════════════════════════════════════════
   COMPREHENSIVE RESPONSIVE & MOBILE-CENTERING ARCHITECTURE
   ════════════════════════════════════════════════════════════════ */

/* ─── Desktop Button Width Fix ───
   The global style.css sets .admin-action-btn { width: 100% } which is
   intended for full-width standalone buttons. Inside the event management
   page's table rows, topbar, hero actions, and inline contexts the buttons
   must be compact (width: auto). These overrides scope the fix. */
.event-mgmt-wrapper .admin-action-btn {
    width: auto;
}
.mgmt-desktop-table .admin-action-btn {
    width: auto;
    padding: 0.3rem 0.65rem;
    font-size: 0.76rem;
    border-radius: 6px;
}
.mgmt-topbar-actions .admin-action-btn {
    width: auto;
    padding: 0.45rem 0.85rem;
    font-size: 0.82rem;
    border-radius: 10px;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
}
.mgmt-action-buttons .admin-action-btn {
    width: auto;
}
.attendee-card-actions .admin-action-btn {
    width: auto;
}

/* Prevent horizontal overflow within the wrapper only.
   html/body already have overflow-x:clip in the global stylesheet. */

.event-mgmt-wrapper {
    width: 100%;
    max-width: 100%;
    min-width: 0;
    box-sizing: border-box;
    overflow-x: hidden;
    margin-left: auto;
    margin-right: auto;
}

.event-mgmt-wrapper * {
    box-sizing: border-box;
    min-width: 0;
}

/* ─── Hero Card Styling ─── */
.mgmt-hero-card {
    background: var(--bg-card, #ffffff);
    border: 1px solid rgba(var(--muted), 0.12);
    border-radius: 20px;
    padding: 1.5rem 1.75rem;
    position: relative;
    overflow: hidden;
    width: 100%;
    max-width: 100%;
    margin-bottom: 1.5rem;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
}

.mgmt-hero-grid {
    display: flex;
    align-items: center;
    gap: 1.5rem;
    flex-wrap: wrap;
    width: 100%;
}

.mgmt-hero-media {
    position: relative;
    width: 130px;
    height: 130px;
    border-radius: 18px;
    overflow: hidden;
    flex-shrink: 0;
    box-shadow: 0 8px 20px rgba(0,0,0,0.12);
    border: 1px solid rgba(var(--muted), 0.15);
}

.mgmt-hero-thumb {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}

.mgmt-live-chip {
    position: absolute;
    top: 6px;
    right: 6px;
    background: #ef4444;
    color: #fff;
    font-size: 0.65rem;
    font-weight: 800;
    padding: 2px 6px;
    border-radius: 4px;
    letter-spacing: 0.04em;
}

.mgmt-hero-details {
    flex: 1 1 300px;
    min-width: 0;
    width: 100%;
}

.mgmt-badges-row {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex-wrap: wrap;
    margin-bottom: 0.4rem;
}

.mgmt-status-pill {
    font-size: 0.74rem;
    font-weight: 700;
    padding: 0.25rem 0.65rem;
    border-radius: 20px;
    white-space: nowrap;
}

.mgmt-pill--upcoming {
    background: rgba(16, 185, 129, 0.15);
    color: #059669;
}

.mgmt-pill--live {
    background: rgba(239, 68, 68, 0.15);
    color: #ef4444;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}

.mgmt-pill--completed {
    background: rgba(100, 116, 139, 0.15);
    color: #64748b;
}

.mgmt-category-pill {
    background: rgba(99, 102, 241, 0.12);
    color: #6366f1;
    font-size: 0.74rem;
    font-weight: 700;
    padding: 0.25rem 0.65rem;
    border-radius: 20px;
    white-space: nowrap;
}

.mgmt-countdown-pill {
    background: rgba(var(--muted), 0.08);
    color: rgb(var(--muted));
    font-size: 0.74rem;
    font-weight: 700;
    padding: 0.25rem 0.65rem;
    border-radius: 20px;
    white-space: nowrap;
}

.mgmt-event-title {
    margin: 0.15rem 0 0.4rem;
    font-size: 1.6rem;
    font-weight: 800;
    letter-spacing: -0.02em;
    word-break: break-word;
    line-height: 1.25;
}

.mgmt-meta-items {
    display: flex;
    align-items: center;
    gap: 1.25rem;
    flex-wrap: wrap;
    font-size: 0.85rem;
    color: rgb(var(--muted));
    margin-bottom: 1rem;
    word-break: break-word;
}

.mgmt-action-buttons {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex-wrap: wrap;
    width: 100%;
}

/* ─── Topbar Actions ─── */
.mgmt-topbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 0.75rem;
    margin-bottom: 1rem;
    width: 100%;
}

.mgmt-topbar-actions {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex-wrap: wrap;
}

/* ─── KPI Strip ─── */
.mgmt-kpi-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    width: 100%;
    margin-bottom: 1.75rem;
}

.mgmt-kpi-card {
    background: var(--bg-card, #fff);
    border: 1px solid rgba(var(--muted), 0.12);
    border-radius: 16px;
    padding: 1.15rem 1.35rem;
    box-shadow: 0 1px 3px rgba(0,0,0,0.03);
    min-width: 0;
    width: 100%;
}

.mgmt-kpi-label {
    font-size: 0.74rem;
    font-weight: 700;
    color: rgb(var(--muted));
    text-transform: uppercase;
    letter-spacing: 0.04em;
    display: block;
    margin-bottom: 0.25rem;
}

.mgmt-kpi-val {
    font-size: 1.45rem;
    font-weight: 800;
    color: var(--text-primary, #1e293b);
    line-height: 1.2;
    word-break: break-word;
}

.mgmt-kpi-sub {
    font-size: 0.74rem;
    color: rgb(var(--muted));
    display: block;
    margin-top: 0.25rem;
    word-break: break-word;
}

.mgmt-kpi-progress {
    height: 5px;
    background: rgba(var(--muted), 0.15);
    border-radius: 4px;
    overflow: hidden;
    margin-top: 0.4rem;
}

.mgmt-kpi-progress-fill {
    height: 100%;
    background: #8b5cf6;
    border-radius: 4px;
}

/* ─── Tab Navigation ─── */
.mgmt-tab-nav {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    border-bottom: 2px solid rgba(var(--muted), 0.1);
    overflow-x: auto;
    padding: 0.2rem 0.2rem 0.5rem 0.2rem;
    margin-bottom: 1.5rem;
    scrollbar-width: none;
    width: 100%;
    max-width: 100%;
    -webkit-overflow-scrolling: touch;
}

.mgmt-tab-nav::-webkit-scrollbar {
    display: none;
}

.mgmt-tab-btn {
    padding: 0.65rem 1rem;
    font-size: 0.86rem;
    font-weight: 700;
    border: none;
    background: transparent;
    color: rgb(var(--muted));
    border-bottom: 3px solid transparent;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    white-space: nowrap;
    transition: all 0.2s ease;
    flex-shrink: 0;
}

.mgmt-tab-btn:hover {
    color: var(--text-primary, #1e293b);
}

.mgmt-tab-btn.active {
    color: rgb(var(--brand));
    border-bottom-color: rgb(var(--brand));
}

.mgmt-tab-counter {
    background: rgba(var(--muted), 0.12);
    font-size: 0.72rem;
    font-weight: 800;
    padding: 0.15rem 0.45rem;
    border-radius: 12px;
}

.mgmt-tab-panel {
    display: none;
    width: 100%;
}

.mgmt-tab-panel.active {
    display: block;
}

/* ─── 2 Column Layout ─── */
.mgmt-grid-2col {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(min(100%, 300px), 1fr));
    gap: 1.25rem;
    width: 100%;
}

.mgmt-stat-mini-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 0.82rem;
    flex-wrap: wrap;
    gap: 0.35rem;
}

.mgmt-tier-item {
    background: rgba(var(--muted), 0.04);
    border: 1px solid rgba(var(--muted), 0.1);
    border-radius: 12px;
    padding: 0.85rem 1rem;
    width: 100%;
}

.mgmt-tier-bar {
    height: 6px;
    background: rgba(var(--muted), 0.12);
    border-radius: 4px;
    overflow: hidden;
}

.mgmt-tier-fill {
    height: 100%;
    background: rgb(var(--brand));
    border-radius: 4px;
}

/* ─── Attendee Table & Mobile Cards ─── */
.mgmt-desktop-table {
    display: block;
    width: 100%;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}

.mgmt-mobile-cards {
    display: none;
    width: 100%;
}

.attendee-card {
    background: var(--bg-card, #ffffff);
    border: 1px solid rgba(var(--muted), 0.14);
    border-radius: 14px;
    padding: 1rem;
    box-shadow: 0 1px 4px rgba(0, 0, 0, 0.04);
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    transition: all 0.2s ease;
    width: 100%;
    max-width: 100%;
    overflow: hidden;
}

.attendee-card:hover {
    border-color: rgba(var(--brand), 0.35);
}

.attendee-card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 0.5rem;
    flex-wrap: wrap;
    width: 100%;
}

.attendee-card-user {
    display: flex;
    align-items: center;
    gap: 0.65rem;
    flex: 1 1 180px;
    min-width: 0;
}

.attendee-user-info {
    min-width: 0;
    flex: 1;
}

.attendee-user-name {
    font-size: 0.92rem;
    color: var(--text-primary, #0f172a);
    display: block;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.attendee-user-email {
    font-size: 0.76rem;
    color: rgb(var(--muted));
    display: block;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.attendee-card-meta-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 0.5rem 0.75rem;
    background: rgba(var(--muted), 0.04);
    padding: 0.75rem;
    border-radius: 10px;
    border: 1px solid rgba(var(--muted), 0.08);
    width: 100%;
}

.attendee-meta-cell {
    display: flex;
    flex-direction: column;
    gap: 0.15rem;
    min-width: 0;
}

.meta-label {
    font-size: 0.68rem;
    font-weight: 700;
    text-transform: uppercase;
    color: rgb(var(--muted));
    letter-spacing: 0.03em;
}

.meta-value {
    font-size: 0.82rem;
    color: var(--text-primary, #1e293b);
    font-weight: 600;
    word-break: break-word;
}

.meta-value.font-mono {
    font-family: Consolas, Monaco, monospace;
    font-size: 0.78rem;
    word-break: break-all;
}

.meta-value.highlight {
    font-weight: 800;
    color: rgb(var(--brand));
}

.issued-comp-pill {
    font-size: 0.65rem;
    font-weight: 800;
    color: #6366f1;
    background: rgba(99, 102, 241, 0.12);
    padding: 1px 5px;
    border-radius: 4px;
    display: inline-block;
    margin-top: 2px;
}

.attendee-card-actions {
    display: flex;
    align-items: center;
    gap: 0.45rem;
    flex-wrap: wrap;
    padding-top: 0.35rem;
    border-top: 1px solid rgba(var(--muted), 0.08);
    width: 100%;
}

.flex-1 {
    flex: 1 1 auto;
}

.w-full {
    width: 100%;
}

/* ─── Attendee Filters & Inputs ─── */
.mgmt-search-input {
    width: 100%;
    padding: 0.55rem 0.85rem;
    border: 1.5px solid rgba(var(--muted), 0.2);
    border-radius: 10px;
    font-size: 0.84rem;
    background: var(--bg-card, #fff);
    color: var(--text-primary, #1e293b);
    box-sizing: border-box;
}

.mgmt-status-filter-pills {
    display: flex;
    align-items: center;
    gap: 0.35rem;
    flex-wrap: wrap;
    width: 100%;
}

.status-pill-btn {
    padding: 0.35rem 0.75rem;
    border-radius: 20px;
    font-size: 0.78rem;
    font-weight: 700;
    border: 1px solid rgba(var(--muted), 0.2);
    background: transparent;
    color: rgb(var(--muted));
    cursor: pointer;
    transition: all 0.15s ease;
    white-space: nowrap;
}

.status-pill-btn.active {
    background: rgb(var(--brand));
    color: #fff;
    border-color: rgb(var(--brand));
}

.attendee-avatar {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: linear-gradient(135deg, #6366f1, #a855f7);
    color: #fff;
    font-weight: 800;
    font-size: 0.78rem;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.mgmt-tier-badge {
    font-size: 0.75rem;
    font-weight: 700;
    padding: 0.2rem 0.55rem;
    border-radius: 6px;
    background: rgba(var(--muted), 0.1);
    color: var(--text-primary);
    white-space: nowrap;
}

/* ─── Staffing & Roles ─── */
.mgmt-role-card {
    display: flex;
    gap: 0.75rem;
    padding: 0.75rem;
    border: 1.5px solid rgba(var(--muted), 0.12);
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.2s ease;
    width: 100%;
}

.mgmt-role-card.active {
    border-color: #6366f1;
    background: rgba(99, 102, 241, 0.04);
}

.mgmt-qty-input {
    width: 55px;
    padding: 0.25rem 0.4rem;
    border: 1px solid rgba(var(--muted), 0.2);
    border-radius: 6px;
    font-size: 0.8rem;
    text-align: center;
}

.mgmt-notes-input {
    width: 100%;
    padding: 0.6rem 0.8rem;
    border: 1.5px solid rgba(var(--muted), 0.15);
    border-radius: 10px;
    font-size: 0.85rem;
    resize: vertical;
    font-family: inherit;
    background: var(--bg-card, #fff);
    color: var(--text-primary, #1e293b);
    box-sizing: border-box;
}

.mgmt-team-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem 0.9rem;
    border-radius: 12px;
    background: rgba(var(--muted), 0.04);
    border: 1px solid rgba(var(--muted), 0.08);
    width: 100%;
}

/* ─── Reviews ─── */
.mgmt-review-card {
    background: rgba(var(--muted), 0.03);
    border: 1px solid rgba(var(--muted), 0.12);
    border-radius: 14px;
    padding: 1.1rem;
    width: 100%;
}

/* ─── Pulse Dot Animation ─── */
.pulse-dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: #ef4444;
    display: inline-block;
    animation: pulseDot 1.5s infinite;
}

@keyframes pulseDot {
    0% { transform: scale(0.9); opacity: 0.8; }
    50% { transform: scale(1.4); opacity: 1; }
    100% { transform: scale(0.9); opacity: 0.8; }
}

/* ════════════════════════════════════════════════════════════════
   TABLET & iPAD RESPONSIVE RULES (≤ 900px)
   ════════════════════════════════════════════════════════════════ */
@media (max-width: 900px) {
    .mgmt-desktop-table {
        display: none !important;
    }
    .mgmt-mobile-cards {
        display: flex !important;
        flex-direction: column;
        gap: 0.75rem;
    }
    .mgmt-kpi-grid {
        grid-template-columns: repeat(2, 1fr) !important;
        gap: 0.75rem;
    }
    .mgmt-grid-2col {
        grid-template-columns: 1fr !important;
    }
}

/* ════════════════════════════════════════════════════════════════
   MOBILE RESPONSIVE & CENTERING OVERHAUL (≤ 768px & ≤ 640px)
   ════════════════════════════════════════════════════════════════ */
@media (max-width: 768px) {
    /* Form Cards Padding and Width Constraint */
    .mgmt-hero-card,
    .form-card {
        padding: 1.15rem 1rem !important;
        border-radius: 16px !important;
        width: 100% !important;
        max-width: 100% !important;
        margin-left: 0 !important;
        margin-right: 0 !important;
    }

    /* Topbar Stacking & Action Buttons */
    .mgmt-topbar {
        flex-direction: column;
        align-items: stretch !important;
        gap: 0.65rem;
        width: 100% !important;
    }
    .mgmt-topbar-actions {
        display: grid !important;
        grid-template-columns: repeat(2, 1fr) !important;
        width: 100% !important;
        gap: 0.45rem !important;
    }
    .mgmt-topbar-actions .admin-action-btn {
        width: 100% !important;
        justify-content: center !important;
        padding: 0.45rem 0.5rem !important;
        font-size: 0.78rem !important;
        text-align: center;
    }

    /* Hero Section Stacking & Full-Width Media */
    .mgmt-hero-grid {
        flex-direction: column;
        align-items: stretch !important;
        gap: 1rem !important;
        width: 100% !important;
    }
    .mgmt-hero-media {
        width: 100% !important;
        height: 190px !important;
        border-radius: 14px !important;
    }
    .mgmt-hero-details {
        width: 100% !important;
        min-width: 0 !important;
    }
    .mgmt-event-title {
        font-size: 1.35rem !important;
    }
    .mgmt-meta-items {
        flex-direction: column;
        align-items: flex-start !important;
        gap: 0.35rem !important;
        font-size: 0.82rem !important;
    }
    
    /* Action Buttons 2-Column Grid on Mobile */
    .mgmt-action-buttons {
        display: grid !important;
        grid-template-columns: repeat(2, 1fr) !important;
        width: 100% !important;
        gap: 0.45rem !important;
    }
    .mgmt-action-buttons .admin-action-btn,
    .mgmt-action-buttons form {
        width: 100% !important;
        margin: 0 !important;
    }
    .mgmt-action-buttons .admin-action-btn,
    .mgmt-action-buttons form button {
        width: 100% !important;
        justify-content: center !important;
        padding: 0.5rem 0.6rem !important;
        font-size: 0.78rem !important;
        text-align: center;
    }

    /* Tab Navigation Smooth Touch Scroll */
    .mgmt-tab-nav {
        padding-bottom: 4px;
        gap: 0.35rem;
    }
    .mgmt-tab-btn {
        padding: 0.55rem 0.85rem;
        font-size: 0.82rem;
    }

    /* Occupancy Chart Grid Centering */
    .occupancy-chart-grid {
        display: flex !important;
        flex-direction: column !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 1.25rem !important;
        width: 100% !important;
    }
    .occupancy-chart-grid > div:last-child {
        width: 100% !important;
        min-width: 0 !important;
    }

    /* Filter Row Stacking */
    .mgmt-filter-row {
        flex-direction: column;
        align-items: stretch !important;
        gap: 0.65rem !important;
        width: 100% !important;
    }
    .mgmt-filter-row > div {
        max-width: 100% !important;
        width: 100% !important;
    }
    .mgmt-status-filter-pills {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        padding-bottom: 4px;
        flex-wrap: nowrap;
        scrollbar-width: none;
        width: 100% !important;
    }
    .mgmt-status-filter-pills::-webkit-scrollbar {
        display: none;
    }
    .status-pill-btn {
        flex-shrink: 0;
    }

    /* Pending Transactions Box Responsive Stacking */
    .mgmt-pending-item {
        flex-direction: column !important;
        align-items: stretch !important;
        gap: 0.5rem !important;
    }
    .mgmt-pending-form,
    .mgmt-pending-form button {
        width: 100% !important;
        justify-content: center !important;
    }

    /* Staff Roster Stacking */
    .mgmt-team-item {
        flex-direction: column;
        align-items: flex-start !important;
        gap: 0.65rem !important;
    }
    .mgmt-team-item > div:first-child {
        width: 100%;
    }
    .mgmt-team-item > div:last-child {
        width: 100%;
        justify-content: space-between;
    }

    /* Attendee Mobile Card Actions Grid */
    .attendee-card-actions {
        display: grid !important;
        grid-template-columns: repeat(2, 1fr) !important;
        gap: 0.45rem !important;
        width: 100% !important;
    }
    .attendee-card-actions .admin-action-btn,
    .attendee-card-actions form,
    .attendee-card-actions form button {
        width: 100% !important;
        justify-content: center !important;
        text-align: center;
    }

    /* Pagination */
    .pagination {
        flex-wrap: wrap;
        justify-content: center;
        gap: 0.35rem;
        width: 100%;
    }
}

/* ════════════════════════════════════════════════════════════════
   EXTRA-SMALL MOBILE SCREENS (≤ 420px)
   ════════════════════════════════════════════════════════════════ */
@media (max-width: 420px) {
    .mgmt-topbar-actions {
        grid-template-columns: 1fr !important;
    }
    .mgmt-action-buttons {
        grid-template-columns: 1fr !important;
    }
    .mgmt-kpi-grid {
        grid-template-columns: 1fr !important;
        gap: 0.6rem;
    }
    .attendee-card-meta-grid {
        grid-template-columns: 1fr !important;
    }
    .attendee-card-actions {
        grid-template-columns: 1fr !important;
    }
}
</style>

<!-- ════════════════════════════════════════════════════════════════
     7. JAVASCRIPT: TAB SWITCHING, COPY LINK & ATTENDEE FILTER
     ════════════════════════════════════════════════════════════════ -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const eventId = "<?= (int)($event->id ?? 0) ?>";
    const csrfToken = "<?= csrf_token() ?>";

    // ─── 0. Toast Notification Helper ───
    function triggerToast(message, type) {
        type = type || 'info';
        if (typeof window.showToast === 'function') {
            window.showToast(message, type);
            return;
        }
        var container = document.getElementById('toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'toast-container';
            container.style.cssText = 'position:fixed;bottom:1.5rem;right:1.5rem;z-index:9999;display:flex;flex-direction:column;gap:0.5rem;';
            document.body.appendChild(container);
        }
        var toast = document.createElement('div');
        toast.className = 'toast ' + type;
        toast.style.cssText = 'padding:0.75rem 1.25rem;border-radius:10px;color:#fff;font-weight:600;font-size:0.88rem;box-shadow:0 8px 24px rgba(0,0,0,0.2);';
        if (type === 'success') toast.style.background = '#059669';
        else if (type === 'error') toast.style.background = '#dc2626';
        else if (type === 'warning') toast.style.background = '#d97706';
        else toast.style.background = '#2563eb';
        toast.textContent = message;
        container.appendChild(toast);
        setTimeout(function() {
            toast.style.opacity = '0';
            toast.style.transition = 'opacity 0.3s ease';
            setTimeout(function() { toast.remove(); }, 300);
        }, 3000);
    }

    // ─── 1. Tab Navigation & State Persistence ───
    const tabBtns = document.querySelectorAll('.mgmt-tab-btn');
    const tabPanels = document.querySelectorAll('.mgmt-tab-panel');

    function activateTab(tabName, updateHistory, scrollIntoView) {
        if (!tabName) return;
        const targetBtn = document.querySelector(`.mgmt-tab-btn[data-tab="${tabName}"]`);
        const targetPanel = document.getElementById('tab-' + tabName);
        if (!targetPanel) return;

        tabBtns.forEach(b => b.classList.remove('active'));
        tabPanels.forEach(p => p.classList.remove('active'));

        if (targetBtn) targetBtn.classList.add('active');
        targetPanel.classList.add('active');

        try {
            sessionStorage.setItem('eventlyy_mgmt_tab_' + eventId, tabName);
        } catch (e) {}

        if (updateHistory) {
            const url = new URL(window.location.href);
            url.searchParams.set('tab', tabName);
            url.hash = 'tab-' + tabName;
            window.history.replaceState(null, '', url.toString());
        }

        if (scrollIntoView) {
            const nav = document.querySelector('.mgmt-tab-nav');
            if (nav) {
                nav.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        }
    }

    tabBtns.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const tabName = this.getAttribute('data-tab');
            activateTab(tabName, true, false);
        });
    });

    // Detect initial tab on load (URL param > Hash > SessionStorage > PHP active)
    const urlParams = new URLSearchParams(window.location.search);
    const hashTab = (window.location.hash || '').replace('#tab-', '').replace('#', '');
    let initialTab = urlParams.get('tab') || hashTab;

    if (!initialTab) {
        try {
            initialTab = sessionStorage.getItem('eventlyy_mgmt_tab_' + eventId);
        } catch (e) {}
    }

    if (initialTab && document.getElementById('tab-' + initialTab)) {
        activateTab(initialTab, false, false);
        // If navigating specifically to attendees tab, scroll to it smoothly
        if (initialTab === 'attendees' && (urlParams.get('tab') || hashTab || urlParams.get('page') || urlParams.get('search'))) {
            setTimeout(() => {
                const attendeesTab = document.getElementById('tab-attendees');
                if (attendeesTab) {
                    attendeesTab.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            }, 100);
        }
    }

    // ─── 2. Copy Public & Flyer Links ───
    const copyBtn = document.getElementById('copy-event-link-btn');
    if (copyBtn) {
        copyBtn.addEventListener('click', function() {
            const url = this.getAttribute('data-url');
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(url).then(() => {
                    triggerToast('Public link copied to clipboard! 📋', 'success');
                }).catch(() => {
                    fallbackCopy(url, 'Public link copied to clipboard! 📋');
                });
            } else {
                fallbackCopy(url, 'Public link copied to clipboard! 📋');
            }
        });
    }

    const copyFlyerBtn = document.getElementById('copy-flyer-link-btn');
    if (copyFlyerBtn) {
        copyFlyerBtn.addEventListener('click', function() {
            const url = this.getAttribute('data-url');
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(url).then(() => {
                    triggerToast('Dedicated flyer ticket link copied! 📋', 'success');
                }).catch(() => {
                    fallbackCopy(url, 'Dedicated flyer ticket link copied! 📋');
                });
            } else {
                fallbackCopy(url, 'Dedicated flyer ticket link copied! 📋');
            }
        });
    }

    function fallbackCopy(text, msg) {
        const temp = document.createElement('input');
        temp.value = text;
        document.body.appendChild(temp);
        temp.select();
        document.execCommand('copy');
        document.body.removeChild(temp);
        triggerToast(msg || 'Copied to clipboard! 📋', 'success');
    }

    // ─── 3. AJAX Check-In / Uncheck Action (No Page Reload) ───
    function bindAttendeeCheckins() {
        const checkinForms = document.querySelectorAll('.attendee-checkin-form');
        checkinForms.forEach(form => {
            if (form.dataset.ajaxBound === 'true') return;
            form.dataset.ajaxBound = 'true';

            form.addEventListener('submit', function(e) {
                e.preventDefault();

                const ticketId = this.getAttribute('data-ticket-id');
                const actionInput = this.querySelector('input[name="action"]');
                const currentAction = actionInput ? actionInput.value : 'check_in';
                const submitBtn = this.querySelector('button[type="submit"]');

                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.dataset.origHtml = submitBtn.innerHTML;
                    submitBtn.innerHTML = '⏳ Updating...';
                }

                const targetUrl = this.getAttribute('action') || (typeof this.action === 'string' ? this.action : '');
                const formData = new FormData(this);

                fetch(targetUrl, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: formData
                })
                .then(async res => {
                    const contentType = res.headers.get('content-type') || '';
                    let data = null;
                    if (contentType.includes('application/json')) {
                        try {
                            data = await res.json();
                        } catch (e) {
                            console.error('Failed to parse JSON response:', e);
                        }
                    }

                    if (!res.ok) {
                        const errMsg = (data && data.message) ? data.message : ('Server returned status ' + res.status);
                        throw new Error(errMsg);
                    }

                    if (!data) {
                        throw new Error('Server returned an invalid response format.');
                    }

                    return data;
                })
                .then(data => {
                    if (data.success) {
                        const isChecked = data.checked_in == 1;
                        const formattedTime = data.formatted_time || (data.checked_in_at ? data.checked_in_at.substring(11, 16) : 'Now');

                        // 1. Update status badge in desktop table
                        const desktopBadge = document.getElementById('attendee-status-badge-' + ticketId);
                        if (desktopBadge) {
                            desktopBadge.className = 'au-status-badge ' + (isChecked ? 'au-status--active' : 'au-status--attendee');
                            desktopBadge.textContent = isChecked ? `✓ In (${formattedTime})` : 'Awaiting Entry';
                        }

                        // 2. Update status badge in mobile card
                        const mobileBadge = document.getElementById('mobile-status-badge-' + ticketId);
                        if (mobileBadge) {
                            mobileBadge.className = 'au-status-badge ' + (isChecked ? 'au-status--active' : 'au-status--attendee');
                            mobileBadge.textContent = isChecked ? `✓ In (${formattedTime})` : 'Awaiting Entry';
                        }

                        // 3. Update data-status on all row elements (desktop & mobile)
                        document.querySelectorAll(`.attendee-row[data-ticket-id="${ticketId}"]`).forEach(el => {
                            el.setAttribute('data-status', isChecked ? 'checked' : 'awaiting');
                        });

                        // 4. Update all check-in forms for this ticket (desktop and mobile)
                        document.querySelectorAll(`.attendee-checkin-form[data-ticket-id="${ticketId}"]`).forEach(f => {
                            const actInp = f.querySelector('input[name="action"]');
                            const btn = f.querySelector('button[type="submit"]');
                            if (btn) btn.disabled = false;

                            if (isChecked) {
                                if (actInp) actInp.value = 'uncheck';
                                if (btn) {
                                    const isMobile = btn.classList.contains('w-full') || f.classList.contains('flex-1');
                                    btn.className = isMobile ? 'admin-action-btn w-full text-center' : 'admin-action-btn';
                                    btn.style.padding = isMobile ? '0.45rem 0.75rem' : '0.3rem 0.65rem';
                                    btn.style.fontSize = isMobile ? '0.8rem' : '0.76rem';
                                    btn.style.borderRadius = '6px';
                                    btn.style.background = 'rgba(245,158,11,0.12)';
                                    btn.style.color = '#d97706';
                                    btn.style.fontWeight = '700';
                                    btn.style.border = '1px solid rgba(245,158,11,0.25)';
                                    btn.title = 'Undo check-in';
                                    btn.innerHTML = '↩️ Uncheck';
                                }
                            } else {
                                if (actInp) actInp.value = 'check_in';
                                if (btn) {
                                    const isMobile = btn.classList.contains('w-full') || f.classList.contains('flex-1');
                                    btn.className = isMobile ? 'admin-action-btn primary w-full text-center' : 'admin-action-btn primary';
                                    btn.style.padding = isMobile ? '0.45rem 0.75rem' : '0.3rem 0.65rem';
                                    btn.style.fontSize = isMobile ? '0.8rem' : '0.76rem';
                                    btn.style.borderRadius = '6px';
                                    btn.style.background = '';
                                    btn.style.color = '';
                                    btn.style.fontWeight = '700';
                                    btn.style.border = '';
                                    btn.title = 'Check in this attendee';
                                    btn.innerHTML = '🎟️ Check In';
                                }
                            }
                        });

                        // 5. Toggle visibility of refund buttons (checked-in tickets cannot be refunded)
                        document.querySelectorAll(`.attendee-refund-form[data-ticket-id="${ticketId}"]`).forEach(rf => {
                            rf.style.display = isChecked ? 'none' : 'inline';
                        });

                        // 6. Refresh active pill filtering
                        applyCurrentFilter();

                        triggerToast(data.message || (isChecked ? 'Attendee checked in!' : 'Check-in reverted.'), isChecked ? 'success' : 'info');
                    } else {
                        throw new Error(data.message || 'Operation failed.');
                    }
                })
                .catch(err => {
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = submitBtn.dataset.origHtml || (currentAction === 'uncheck' ? '↩️ Uncheck' : '🎟️ Check In');
                    }
                    triggerToast(err.message || 'An error occurred while updating check-in.', 'error');
                });
            });
        });
    }

    // ─── 4. Attendee Status Filter & Search ───
    function applyCurrentFilter() {
        const activePill = document.querySelector('#attendee-status-pills .status-pill-btn.active');
        const filter = activePill ? activePill.getAttribute('data-filter') : 'all';
        const searchInput = document.getElementById('search-tickets');
        const q = searchInput ? searchInput.value.toLowerCase().trim() : '';

        const attendeeRows = document.querySelectorAll('.attendee-row');
        attendeeRows.forEach(row => {
            const status = row.getAttribute('data-status');
            const statusMatches = (filter === 'all' || status === filter);
            const textMatches = !q || row.textContent.toLowerCase().includes(q);

            row.style.display = (statusMatches && textMatches) ? '' : 'none';
        });
    }

    const filterPills = document.querySelectorAll('#attendee-status-pills .status-pill-btn');
    filterPills.forEach(pill => {
        pill.addEventListener('click', function() {
            filterPills.forEach(p => p.classList.remove('active'));
            this.classList.add('active');
            applyCurrentFilter();
        });
    });

    const searchInput = document.getElementById('search-tickets');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            applyCurrentFilter();
        });
    }

    // ─── 5. AJAX Smooth Pagination for Attendees ───
    function bindPagination() {
        const paginationLinks = document.querySelectorAll('.mgmt-attendees-container .pagination a.page-button');
        paginationLinks.forEach(link => {
            if (link.dataset.ajaxBound === 'true') return;
            link.dataset.ajaxBound = 'true';

            link.addEventListener('click', function(e) {
                e.preventDefault();
                const targetUrl = this.getAttribute('href');
                if (!targetUrl) return;

                const container = document.querySelector('.mgmt-attendees-container');
                if (container) {
                    container.style.opacity = '0.4';
                    container.style.pointerEvents = 'none';
                    container.style.transition = 'opacity 0.2s ease';
                }

                fetch(targetUrl, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(res => {
                    if (!res.ok) throw new Error('Failed to load page');
                    return res.text();
                })
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const newContainer = doc.querySelector('.mgmt-attendees-container');

                    if (newContainer && container) {
                        container.innerHTML = newContainer.innerHTML;
                        window.history.pushState(null, '', targetUrl);
                        activateTab('attendees', false, false);
                        
                        // Re-bind check-in forms & pagination on new DOM elements
                        bindAttendeeCheckins();
                        bindPagination();
                        applyCurrentFilter();

                        // Smoothly scroll back to the top of the attendees container
                        const attendeesTab = document.getElementById('tab-attendees');
                        if (attendeesTab) {
                            attendeesTab.scrollIntoView({ behavior: 'smooth', block: 'start' });
                        }
                    } else {
                        window.location.href = targetUrl;
                    }
                })
                .catch(() => {
                    window.location.href = targetUrl;
                })
                .finally(() => {
                    if (container) {
                        container.style.opacity = '1';
                        container.style.pointerEvents = 'auto';
                    }
                });
            });
        });
    }

    // Listen for browser forward/backward navigation
    window.addEventListener('popstate', function() {
        const params = new URLSearchParams(window.location.search);
        const tab = params.get('tab') || 'overview';
        activateTab(tab, false, false);
    });

    // ─── 6. Host Staff Check-In Toggle ───
    const hostCheckinBtns = document.querySelectorAll('.btn-host-toggle-checkin');
    hostCheckinBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const asgId = this.getAttribute('data-assignment-id');
            const cur = this.getAttribute('data-current');
            this.disabled = true;
            this.textContent = 'Updating...';

            fetch(`<?= route('/host/event/management/' . ($event->id ?? 0) . '/toggle-staff-checkin') ?>`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ 
                    assignment_id: asgId,
                    csrf_token: csrfToken 
                })
            })
            .then(r => r.json())
            .then(data => {
                this.disabled = false;
                if (data.success) {
                    const isChecked = data.checked_in == 1;
                    this.setAttribute('data-current', isChecked ? '1' : '0');
                    this.textContent = isChecked ? 'Check Out' : '✓ Check In';
                    this.style.background = isChecked ? 'rgba(239,68,68,0.12)' : '#10b981';
                    this.style.color = isChecked ? '#ef4444' : '#fff';
                    this.style.border = isChecked ? '1px solid rgba(239,68,68,0.25)' : 'none';

                    const badge = document.getElementById('host-badge-checkin-' + asgId);
                    if (badge) {
                        badge.textContent = isChecked ? ('● Checked In' + (data.checked_in_at ? ' (' + data.checked_in_at + ')' : '')) : '○ Not Checked In';
                        badge.style.background = isChecked ? 'rgba(16,185,129,0.14)' : 'rgba(100,116,139,0.12)';
                        badge.style.color = isChecked ? '#059669' : 'rgb(100,116,139)';
                        badge.style.border = isChecked ? '1px solid rgba(16,185,129,0.3)' : 'none';
                    }
                    triggerToast(data.message, 'success');
                } else {
                    alert(data.message || 'Error updating staff check-in.');
                    this.textContent = cur == '1' ? 'Check Out' : '✓ Check In';
                }
            })
            .catch(() => {
                this.disabled = false;
                this.textContent = cur == '1' ? 'Check Out' : '✓ Check In';
                alert('Connection error.');
            });
        });
    });

    // Initial binding
    bindAttendeeCheckins();
    bindPagination();
});
</script>
@endsection