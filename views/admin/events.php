<?php

/**
 * Admin Events
 *
 * This file contains the HTML markup for the admin events page of the application.
 *
 * @package Evently
 * @subpackage Views/Admin
 * @author Your Name <Celio Natti>
 * @license MIT License
 * @version 1.0.0
 */

$siteName = site_setting('site_title', 'Eventlyy');
?>

@extends('layouts/admin')

@section('title')
Admin Events - Manage Your <?= e($siteName) ?> Events
@endsection

@section('content')
<style>
/* Admin Events Responsive & Compact Polish */
.admin-events-stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 0.85rem;
    margin-bottom: 1.5rem;
}

/* ── Compact Table Architecture (Modern Web Guidance responsive-table) ── */
.au-table-wrap {
    background: rgb(var(--card));
    border-radius: 16px;
    border: 1px solid rgba(var(--muted), 0.14);
    overflow-x: auto;
    max-width: 100%;
    -webkit-overflow-scrolling: touch;
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.03);
}

.au-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    font-size: 0.84rem;
}

.au-table thead {
    position: sticky;
    top: 0;
    z-index: 2;
    background: rgb(var(--card));
}

.au-table thead th {
    padding: 0.65rem 0.75rem;
    font-size: 0.72rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: rgb(var(--muted));
    border-bottom: 2px solid rgba(var(--muted), 0.12);
    white-space: nowrap;
    background: rgb(var(--card));
}

.au-table tbody tr {
    border-bottom: 1px solid rgba(var(--muted), 0.07);
    transition: background-color 0.15s ease;
}

.au-table tbody tr:hover {
    background: rgba(var(--brand), 0.03);
}

.au-table tbody tr:last-child td {
    border-bottom: none;
}

.au-table tbody td {
    padding: 0.55rem 0.75rem;
    vertical-align: middle;
    color: rgb(var(--text));
    border-bottom: 1px solid rgba(var(--muted), 0.07);
}

/* Compact Event Info Subline */
.admin-event-meta-line {
    display: flex;
    align-items: center;
    gap: 0.35rem;
    margin-top: 2px;
    font-size: 0.75rem;
    color: rgb(var(--muted));
    max-width: 250px;
    overflow: hidden;
    white-space: nowrap;
}

.admin-event-meta-venue {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    min-width: 0;
}

.admin-event-organizer-sub {
    font-size: 0.71rem;
    color: rgb(var(--muted));
    margin-top: 1px;
    max-width: 250px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.admin-event-category-badge {
    white-space: nowrap !important;
    display: inline-block !important;
    flex-shrink: 0 !important;
    line-height: 1.2 !important;
}

.au-status-badge, .au-role-badge {
    white-space: nowrap !important;
    display: inline-block !important;
    flex-shrink: 0 !important;
}

/* ── Mobile/Tablet Responsive Cards ── */
.au-cards-list {
    display: none;
    flex-direction: column;
    gap: 0.85rem;
}

.admin-event-card {
    background: rgb(var(--card));
    border: 1px solid rgba(var(--muted), 0.14);
    border-radius: 16px;
    padding: 1rem 1.15rem;
    box-shadow: 0 3px 14px rgba(0, 0, 0, 0.03);
    transition: all 0.2s ease;
}

.admin-event-card:hover {
    border-color: rgba(var(--brand), 0.3);
    box-shadow: 0 4px 18px rgba(0, 0, 0, 0.05);
}

.card-metrics-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 0.65rem;
    background: rgba(var(--muted), 0.04);
    border-radius: 12px;
    padding: 0.75rem 0.85rem;
    margin: 0.75rem 0;
}

.card-actions-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 0.4rem;
    margin-top: 0.75rem;
    padding-top: 0.65rem;
    border-top: 1px solid rgba(var(--muted), 0.08);
}

.card-action-feature-row {
    grid-column: 1 / -1;
}

.admin-events-hero {
    display: flex !important;
    flex-direction: row !important;
    justify-content: space-between !important;
    align-items: center !important;
    gap: 1.5rem !important;
    flex-wrap: nowrap !important;
    padding: clamp(0.75rem, 2vw, 1.5rem) 0;
}

.admin-events-hero-title {
    flex: 1 1 auto;
    min-width: 0;
}

.admin-events-hero-actions {
    flex: 0 0 auto;
    display: flex !important;
    flex-direction: row !important;
    align-items: center !important;
    gap: 0.55rem !important;
    flex-wrap: nowrap !important;
}

.admin-events-btn {
    padding: 0.5rem 0.85rem;
    font-size: 0.82rem;
    border-radius: 10px;
    font-weight: 600;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    white-space: nowrap;
}

@media (max-width: 960px) {
    .au-table-wrap {
        display: none !important;
    }
    .au-cards-list {
        display: flex !important;
    }
}

@media (max-width: 768px) {
    .admin-events-hero {
        flex-direction: column !important;
        align-items: stretch !important;
        gap: 1rem !important;
    }
    .admin-events-hero-actions {
        width: 100% !important;
        display: grid !important;
        grid-template-columns: repeat(3, 1fr) !important;
        gap: 0.45rem !important;
    }
    .admin-events-btn {
        justify-content: center !important;
        text-align: center !important;
        padding: 0.5rem 0.4rem !important;
        font-size: 0.78rem !important;
    }
}

@media (max-width: 680px) {
    .admin-events-hero-actions {
        display: flex !important;
        flex-direction: column !important;
        width: 100% !important;
        gap: 0.45rem !important;
    }
    .admin-events-hero-actions .admin-events-btn {
        width: 100% !important;
        min-height: 40px !important;
        justify-content: center !important;
        text-align: center !important;
        box-sizing: border-box !important;
    }
}

@media (max-width: 520px) {
    .card-metrics-grid {
        grid-template-columns: 1fr;
        gap: 0.5rem;
    }
    .card-actions-grid {
        grid-template-columns: 1fr 1fr;
    }
    .card-action-feature-row {
        grid-column: 1 / -1 !important;
        width: 100% !important;
    }
    .card-action-feature-row form,
    .card-action-feature-row button {
        width: 100% !important;
        box-sizing: border-box !important;
    }
}

@media (max-width: 360px) {
    .card-actions-grid {
        grid-template-columns: 1fr !important;
    }
}
</style>

<section class="page-hero admin-events-hero">
    <div class="admin-events-hero-title">
        <span class="panel-badge">Admin — Events</span>
        <h1 style="margin:0.25rem 0;">Published Events</h1>
        <p class="lede" style="margin:0;">Monitor event performance, ticket sales, platform profits, and organizer accounts.</p>
    </div>
    <div class="admin-events-hero-actions">
        <a href="{{ route('admin/overview') }}" class="admin-action-btn admin-events-btn" style="background:rgba(var(--text), 0.04); border:1px solid rgba(var(--muted),0.2);">
            <span>←</span> Back to Dashboard
        </a>
        <a href="{{ route('host/event/create') }}" class="admin-action-btn primary admin-events-btn" style="box-shadow:0 4px 14px rgba(var(--brand),0.35);">
            <span style="font-size:1.05rem;">➕</span> Create Event
        </a>
        <a href="{{ route('host/dashboard') }}" class="admin-action-btn admin-events-btn" style="background:rgba(var(--text), 0.04); border:1px solid rgba(var(--muted),0.2);">
            <span style="font-size:1.05rem;">🎪</span> Host Dashboard
        </a>
    </div>
</section>

<!-- Summary Stats Cards Grid -->
<section class="admin-users-stats">
    <div class="au-stat-card">
        <span class="au-stat-icon">
            <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
                <line x1="16" y1="2" x2="16" y2="6" />
                <line x1="8" y1="2" x2="8" y2="6" />
                <line x1="3" y1="10" x2="21" y2="10" />
            </svg>
        </span>
        <div class="au-stat-body">
            <span class="au-stat-num"><?= number_format($total_events ?? 0) ?></span>
            <span class="au-stat-lbl">Total Events<?php if (($total_occurrences ?? 0) > ($total_events ?? 0)): ?> <span style="font-weight:500; opacity:0.7;">(<?= (int)$total_occurrences ?> occurrences)</span><?php endif; ?></span>
        </div>
    </div>
    <div class="au-stat-card">
        <span class="au-stat-icon au-stat-icon--brand">
            <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10" />
                <polyline points="12 6 12 12 16 14" />
            </svg>
        </span>
        <div class="au-stat-body">
            <span class="au-stat-num"><?= number_format($active_events ?? 0) ?></span>
            <span class="au-stat-lbl">Active & Live</span>
        </div>
    </div>
    <div class="au-stat-card">
        <span class="au-stat-icon au-stat-icon--success">
            <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round">
                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z" />
                <line x1="2" y1="10" x2="22" y2="10" />
            </svg>
        </span>
        <div class="au-stat-body">
            <span class="au-stat-num"><?= number_format($tickets_sold ?? 0) ?></span>
            <span class="au-stat-lbl">Tickets Sold</span>
        </div>
    </div>
    <div class="au-stat-card">
        <span class="au-stat-icon au-stat-icon--warning">
            <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round">
                <line x1="12" y1="1" x2="12" y2="23" />
                <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" />
            </svg>
        </span>
        <div class="au-stat-body">
            <span class="au-stat-num"><?= format_price($total_gross ?? 0) ?></span>
            <span class="au-stat-lbl">Total Gross</span>
        </div>
    </div>
    <div class="au-stat-card">
        <span class="au-stat-icon" style="background:rgba(35, 182, 107, 0.12); color:rgb(35, 182, 107);;">
            <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" />
            </svg>
        </span>
        <div class="au-stat-body">
            <span class="au-stat-num" style="color:rgb(35, 182, 107)"><?= format_price($total_platform_profit ?? 0) ?></span>
            <span class="au-stat-lbl">Platform Profit</span>
        </div>
    </div>
</section>

<!-- Search & Filter Toolbar -->
<section class="admin-users-toolbar">
    <form method="GET" action="" class="au-toolbar-form">
        <div class="au-search-wrap">
            <svg class="au-search-icon" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor"
                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="11" cy="11" r="8" />
                <line x1="21" y1="21" x2="16.65" y2="16.65" />
            </svg>
            <input type="search" name="q" id="events-search" placeholder="Search by title, venue, or owner..."
                aria-label="Search events" value="<?= e($search ?? '') ?>" />
        </div>
        <div class="au-filter-group">
            <select name="status" id="events-status-filter" aria-label="Filter by status">
                <option value="">All Statuses</option>
                <option value="active" <?= ($status ?? '') === 'active' ? 'selected' : '' ?>>Active / Live</option>
                <option value="draft" <?= ($status ?? '') === 'draft' ? 'selected' : '' ?>>Draft</option>
                <option value="past" <?= ($status ?? '') === 'past' ? 'selected' : '' ?>>Past</option>
            </select>
        </div>
        <button type="submit" class="admin-action-btn primary">
            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5"
                stroke-linecap="round" stroke-linejoin="round">
                <polyline points="22 12 18 12 15 21 9 3 6 12 2 12" />
            </svg>
            Filter
        </button>
    </form>
</section>

<!-- Events Table Section -->
<section class="admin-users-table-section">
    <div class="au-table-header">
        <h2>Events List (<?= count($events ?? []) ?>)</h2>
        <a href="#" class="admin-action-btn">
            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5"
                stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4" />
                <polyline points="7 10 12 15 17 10" />
                <line x1="12" y1="15" x2="12" y2="3" />
            </svg>
            Export CSV
        </a>
    </div>

    <!-- Desktop & Tablet Data Table (Accessible & Responsive) -->
    <div class="au-table-wrap" tabindex="0" role="region" aria-label="Published Events Directory">
        <table class="au-table">
            <thead>
                <tr>
                    <th style="min-width:230px; text-align:left;">Event</th>
                    <th style="min-width:95px; text-align:left;">Amount</th>
                    <th style="min-width:115px; text-align:left;">Sales Progress</th>
                    <th style="min-width:125px; text-align:left;">Gross &amp; Host Earned</th>
                    <th style="min-width:105px; text-align:left;">Platform Profit</th>
                    <th style="min-width:85px; text-align:left;">Status</th>
                    <th style="min-width:215px; text-align:right;">Actions</th>
                </tr>
            </thead>
            <tbody id="events-tbody">
                <?php if (empty($events)): ?>
                    <tr>
                        <td colspan="7" class="text-center" style="padding: 2.5rem 1rem; color: rgb(var(--muted));">No events found matching your criteria.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($events as $ev): 
                        $hue = rand(100, 360);
                        $initials = strtoupper(substr($ev['name'] ?? 'E', 0, 2));
                        $dateStr = date('M d, Y', strtotime($ev['date']));
                        $timeStr = date('g:i A', strtotime($ev['date']));
                        $sold = (int)$ev['sold'];
                        $capacity = (int)($ev['capacity'] ?: 0);
                        $pct = $capacity > 0 ? min(100, round(($sold / $capacity) * 100)) : 0;
                        $evTime = strtotime($ev['date']);
                        $evNow = time();
                        $evLiveEnd = $evTime + (event_completion_hours() * 3600);
                        if ($ev['status'] === 'completed' || ($ev['status'] === 'published' && $evNow > $evLiveEnd)) {
                            $evStatusLabel = 'Completed';
                            $evStatusClass = 'au-status-badge au-status--unverified';
                        } elseif ($ev['status'] === 'published' && $evNow >= $evTime && $evNow <= $evLiveEnd) {
                            $evStatusLabel = '🔴 Live';
                            $evStatusClass = 'au-status-badge au-status--active';
                        } elseif ($ev['status'] === 'published') {
                            $evStatusLabel = 'Upcoming';
                            $evStatusClass = 'au-role-badge au-role--host';
                        } elseif ($ev['status'] === 'cancelled') {
                            $evStatusLabel = 'Cancelled';
                            $evStatusClass = 'au-status-badge au-status--suspended';
                        } else {
                            $evStatusLabel = ucfirst($ev['status']);
                            $evStatusClass = 'au-role-badge au-role--attendee';
                        }

                        // Pricing calculation
                        $minP = isset($ev['min_price']) && $ev['min_price'] !== null ? (float)$ev['min_price'] : null;
                        $maxP = isset($ev['max_price']) && $ev['max_price'] !== null ? (float)$ev['max_price'] : null;
                        $tierCount = (int)($ev['ticket_type_count'] ?? 0);

                        if ($minP === null && $tierCount === 0) {
                            $amountDisplay = '<span class="au-role-badge au-role--attendee" style="font-size:0.72rem; padding:0.12rem 0.45rem;">Free</span>';
                            $amountSub = 'No paid tiers';
                        } elseif ($minP == 0 && ($maxP == 0 || $maxP === null)) {
                            $amountDisplay = '<span class="au-role-badge au-role--attendee" style="font-size:0.72rem; padding:0.12rem 0.45rem;">Free</span>';
                            $amountSub = $tierCount > 1 ? ($tierCount . ' free tiers') : 'Free entry';
                        } elseif ($minP == 0 && $maxP > 0) {
                            $amountDisplay = '<strong style="font-size:0.84rem; color:rgb(var(--text));">Free – ' . format_price($maxP) . '</strong>';
                            $amountSub = $tierCount . ' tiers';
                        } elseif ($minP !== null && $maxP !== null && $minP == $maxP) {
                            $amountDisplay = '<strong style="font-size:0.86rem; color:rgb(var(--text));">' . format_price($minP) . '</strong>';
                            $amountSub = $tierCount > 1 ? ($tierCount . ' tiers') : 'Single tier';
                        } else {
                            $amountDisplay = '<strong style="font-size:0.82rem; color:rgb(var(--text));">' . format_price($minP ?? 0) . ' – ' . format_price($maxP ?? 0) . '</strong>';
                            $amountSub = $tierCount . ' tiers';
                        }

                        $venueStr = $ev['location'] ?: 'Online / TBD';
                        $fullScheduleVenue = $dateStr . ' at ' . $timeStr . ' • ' . $venueStr;
                    ?>
                    <tr>
                        <!-- 1. Event Column (Title, Category, Truncated Date & Venue, Organizer) -->
                        <td>
                            <div class="au-user-cell" style="align-items: flex-start; gap: 0.65rem;">
                                <span class="au-avatar" style="--avatar-hue:<?= $hue ?>; width:34px; height:34px; font-size:0.8rem; margin-top:2px;"><?= $initials ?></span>
                                <div style="min-width:0; max-width: 250px;">
                                    <a href="{{ route('admin/events/{id}', ['id' => $ev['id']]) }}" style="color:rgb(var(--text)); text-decoration:none; font-weight:700; font-size:0.9rem; display:block; line-height:1.25; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;" onmouseover="this.style.color='rgb(var(--brand))'" onmouseout="this.style.color='rgb(var(--text))'" title="<?= e($ev['name']) ?>">
                                        <?= e($ev['name']) ?> ↗
                                    </a>
                                    <div style="display:flex; align-items:center; gap:0.35rem; margin-top:2px; flex-wrap:nowrap;">
                                        <span class="admin-event-category-badge" style="font-size:0.68rem; font-weight:700; color:rgb(var(--muted)); background:rgba(var(--muted),0.08); padding:0.1rem 0.4rem; border-radius:4px;"><?= e($ev['category'] ?? 'Event') ?></span>
                                        <?php if (!empty($ev['is_recurring'])): ?>
                                            <button type="button" onclick="openSeriesOccurrencesModal('<?= $ev['id'] ?>', '<?= addslashes(e($ev['name'])) ?>')" style="font-size:0.68rem; font-weight:700; color:#818cf8; background:rgba(99,102,241,0.12); border:1px solid rgba(99,102,241,0.25); padding:0.1rem 0.4rem; border-radius:4px; cursor:pointer; display:inline-flex; align-items:center; gap:0.2rem; white-space:nowrap; flex-shrink:0;" title="Click to view all occurrences in this recurring series">
                                                🔄 Series (<?= e($ev['recurrence_count'] ?? '') ?>) ▾
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                    <div class="admin-event-meta-line" title="<?= e($fullScheduleVenue) ?>">
                                        <span style="font-weight:600; color:rgb(var(--text));">🗓️ <?= $dateStr ?></span>
                                        <span>&bull;</span>
                                        <span class="admin-event-meta-venue">📍 <?= e($venueStr) ?></span>
                                    </div>
                                    <div class="admin-event-organizer-sub" title="<?= e($ev['organizer_email']) ?>">
                                        👤 <?= e($ev['organizer_email']) ?>
                                    </div>
                                </div>
                            </div>
                        </td>

                        <!-- 2. Amount Column (Ticket Price / Range) -->
                        <td>
                            <div><?= $amountDisplay ?></div>
                            <div style="font-size:0.68rem; color:rgb(var(--muted)); margin-top:1px;"><?= $amountSub ?></div>
                        </td>

                        <!-- 3. Sales Progress Column -->
                        <td>
                            <div style="min-width: 105px; max-width: 130px;">
                                <div style="display:flex; justify-content:space-between; align-items:baseline;">
                                    <strong style="font-weight:700; font-size:0.8rem; color:rgb(var(--text));"><?= number_format($sold) ?> / <?= number_format($capacity) ?></strong>
                                    <span style="font-size:0.72rem; font-weight:700; color:<?= $pct > 80 ? '#10b981' : 'rgb(var(--brand))' ?>;"><?= $pct ?>%</span>
                                </div>
                                <div class="progress-bar-bg" style="height: 4px; background: rgba(var(--muted), 0.12); border-radius: 2px; margin-top: 3px; overflow: hidden;">
                                    <div class="progress-bar-fill" style="height: 100%; width: <?= $pct ?>%; background: <?= $pct > 80 ? '#10b981' : 'rgb(var(--brand))' ?>;"></div>
                                </div>
                                <?php if (($ev['issued'] ?? 0) > 0): ?>
                                    <span style="font-size:0.68rem; color:#6366f1; font-weight:600; display:block; margin-top:2px;">+<?= number_format($ev['issued']) ?> comped</span>
                                <?php endif; ?>
                            </div>
                        </td>

                        <!-- 4. Gross & Host Earned Column -->
                        <td>
                            <strong style="font-size:0.84rem; color:rgb(var(--text)); display:block; line-height:1.2;"><?= format_price($ev['gross_revenue'] ?? 0) ?></strong>
                            <div style="font-size:0.72rem; color:#10b981; font-weight:700; margin-top:2px;">Host: <?= format_price($ev['host_earnings'] ?? 0) ?></div>
                        </td>

                        <!-- 5. Platform Profit Column -->
                        <td>
                            <strong style="color:rgb(35, 182, 107); font-size:0.86rem; display:block; line-height:1.2;"><?= format_price($ev['platform_profit'] ?? 0) ?></strong>
                            <div style="font-size:0.68rem; color:rgb(var(--muted)); margin-top:2px;">Fees &amp; Net</div>
                        </td>

                        <!-- 6. Status Column -->
                        <td>
                            <span class="<?= $evStatusClass ?>" style="font-size:0.7rem; padding:0.15rem 0.45rem; border-radius:5px; font-weight:700;"><?= $evStatusLabel ?></span>
                        </td>

                        <!-- 7. Actions Column -->
                        <td style="text-align:right; white-space:nowrap;">
                            <div style="display:inline-flex; gap:0.25rem; align-items:center; justify-content:flex-end; flex-wrap:nowrap;">
                                <a href="{{ route('host/event/edit/{id}', ['id' => $ev['id']]) }}" class="admin-action-btn" style="padding:0.28rem 0.5rem; font-size:0.74rem; border-radius:6px; background:rgba(var(--brand),0.1); color:rgb(var(--brand)); font-weight:700; text-decoration:none;" title="Edit Event">
                                    ✏️ Edit
                                </a>
                                <a href="{{ route('host/event/management/{id}', ['id' => $ev['id']]) }}" class="admin-action-btn" style="padding:0.28rem 0.5rem; font-size:0.74rem; border-radius:6px; background:rgba(var(--text),0.06); color:var(--text); font-weight:600; text-decoration:none;" title="Manage Event, Tickets & Staff">
                                    ⚙️ Manage
                                </a>
                                <a href="{{ route('host/event/referrals/{id}', ['id' => $ev['id']]) }}" class="admin-action-btn" style="padding:0.28rem 0.5rem; font-size:0.74rem; border-radius:6px; background:rgba(16, 185, 129, 0.12); color:#059669; font-weight:600; text-decoration:none;" title="Referrals & Affiliates">
                                    🔗 Ref
                                </a>
                                <form method="POST" action="{{ route('admin/events/duplicate/' . $ev['id']) }}" style="display:inline; margin:0;" onsubmit="return confirm('Clone this event as a new draft?');">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="admin-action-btn" style="padding:0.28rem 0.5rem; font-size:0.74rem; border-radius:6px; background:rgba(147,51,234,0.1); color:#9333ea; border-color:rgba(147,51,234,0.25); font-weight:600;" title="Duplicate / Clone Event">
                                        📋 Clone
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('admin/events/toggle-featured') }}" style="display:inline; margin:0;">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="event_id" value="<?= $ev['id'] ?>" />
                                    <?php if (!empty($ev['is_featured'])): ?>
                                        <button type="submit" class="admin-action-btn warning" style="padding:0.28rem 0.5rem; font-size:0.74rem; border-radius:6px; font-weight:700;" title="Remove from homepage carousel">
                                            ⭐ Featured
                                        </button>
                                    <?php else: ?>
                                        <button type="submit" class="admin-action-btn" style="padding:0.28rem 0.5rem; font-size:0.74rem; border-radius:6px; background:rgba(var(--muted),0.12); color:var(--text); font-weight:600;" title="Feature on homepage carousel">
                                            ☆ Feature
                                        </button>
                                    <?php endif; ?>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Mobile & Tablet Screen Card View (CSS: display:none on desktop >960px, flex on mobile/tablet <=960px) -->
    <div class="au-cards-list" id="events-cards">
        <?php if (!empty($events)): ?>
            <?php foreach ($events as $ev): 
                $hue = rand(100, 360);
                $initials = strtoupper(substr($ev['name'] ?? 'E', 0, 2));
                $dateStr = date('M d, Y', strtotime($ev['date']));
                $timeStr = date('g:i A', strtotime($ev['date']));
                $sold = (int)$ev['sold'];
                $capacity = (int)($ev['capacity'] ?: 0);
                $pct = $capacity > 0 ? min(100, round(($sold / $capacity) * 100)) : 0;
                $evTime = strtotime($ev['date']);
                $evNow = time();
                $evLiveEnd = $evTime + (event_completion_hours() * 3600);
                if ($ev['status'] === 'completed' || ($ev['status'] === 'published' && $evNow > $evLiveEnd)) {
                    $evStatusLabel = 'Completed';
                    $evStatusClass = 'au-status--unverified';
                } elseif ($ev['status'] === 'published' && $evNow >= $evTime && $evNow <= $evLiveEnd) {
                    $evStatusLabel = '🔴 Live Now';
                    $evStatusClass = 'au-status--active';
                } elseif ($ev['status'] === 'published') {
                    $evStatusLabel = 'Upcoming';
                    $evStatusClass = 'au-role-badge au-role--host';
                } elseif ($ev['status'] === 'cancelled') {
                    $evStatusLabel = 'Cancelled';
                    $evStatusClass = 'au-status--suspended';
                } else {
                    $evStatusLabel = ucfirst($ev['status']);
                    $evStatusClass = 'au-role-badge au-role--attendee';
                }

                // Pricing calculation for cards
                $minP = isset($ev['min_price']) && $ev['min_price'] !== null ? (float)$ev['min_price'] : null;
                $maxP = isset($ev['max_price']) && $ev['max_price'] !== null ? (float)$ev['max_price'] : null;
                $tierCount = (int)($ev['ticket_type_count'] ?? 0);

                if ($minP === null && $tierCount === 0) {
                    $cardAmount = 'Free';
                } elseif ($minP == 0 && ($maxP == 0 || $maxP === null)) {
                    $cardAmount = 'Free';
                } elseif ($minP == 0 && $maxP > 0) {
                    $cardAmount = 'Free – ' . format_price($maxP);
                } elseif ($minP !== null && $maxP !== null && $minP == $maxP) {
                    $cardAmount = format_price($minP);
                } else {
                    $cardAmount = format_price($minP ?? 0) . ' – ' . format_price($maxP ?? 0);
                }

                $venueStr = $ev['location'] ?: 'Online / TBD';
                $fullScheduleVenue = $dateStr . ' • ' . $timeStr . ' • ' . $venueStr;
            ?>
            <article class="admin-event-card">
                <!-- Card Header -->
                <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:0.65rem;">
                    <div style="display:flex; align-items:flex-start; gap:0.6rem; min-width:0; flex:1;">
                        <span class="au-avatar" style="--avatar-hue:<?= $hue ?>; flex-shrink:0; width:36px; height:36px; font-size:0.82rem; margin-top:2px;"><?= $initials ?></span>
                        <div style="min-width:0; flex:1;">
                            <a href="{{ route('admin/events/' . $ev['id']) }}" style="color:rgb(var(--text)); text-decoration:none; font-weight:800; font-size:0.95rem; line-height:1.25; display:block; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;" title="<?= e($ev['name']) ?>">
                                <?= e($ev['name']) ?> ↗
                            </a>
                            <div style="display:flex; align-items:center; gap:0.35rem; margin-top:3px; flex-wrap:wrap;">
                                <span style="font-size:0.68rem; font-weight:700; color:rgb(var(--muted)); background:rgba(var(--muted),0.08); padding:0.1rem 0.4rem; border-radius:4px; white-space:nowrap;">
                                    <?= e($ev['category'] ?? 'Event') ?>
                                </span>
                                <?php if (!empty($ev['is_recurring'])): ?>
                                    <button type="button" onclick="openSeriesOccurrencesModal('<?= $ev['id'] ?>', '<?= addslashes(e($ev['name'])) ?>')" style="font-size:0.68rem; font-weight:700; color:#818cf8; background:rgba(99,102,241,0.12); border:1px solid rgba(99,102,241,0.25); padding:0.1rem 0.4rem; border-radius:4px; cursor:pointer; display:inline-flex; align-items:center; gap:0.2rem;" title="Click to view all occurrences in this recurring series">
                                        🔄 Series (<?= e($ev['recurrence_count'] ?? '') ?>) ▾
                                    </button>
                                <?php endif; ?>
                            </div>
                            <div style="font-size:0.75rem; color:rgb(var(--muted)); margin-top:3px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;" title="<?= e($fullScheduleVenue) ?>">
                                🗓️ <?= $dateStr ?> &bull; 📍 <?= e($venueStr) ?>
                            </div>
                            <div style="font-size:0.71rem; color:rgb(var(--muted)); margin-top:1px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                                👤 <?= e($ev['organizer_email']) ?>
                            </div>
                        </div>
                    </div>
                    <span class="<?= $evStatusClass ?>" style="flex-shrink:0; font-size:0.72rem; font-weight:700; white-space:nowrap;"><?= $evStatusLabel ?></span>
                </div>

                <!-- Card Metrics Grid (Compact 2x2) -->
                <div class="card-metrics-grid">
                    <div>
                        <span style="font-size:0.68rem; text-transform:uppercase; font-weight:700; color:rgb(var(--muted)); display:block; letter-spacing:0.04em;">Amount</span>
                        <div style="font-size:0.86rem; font-weight:800; color:rgb(var(--text)); margin-top:2px;">
                            <?= $cardAmount ?>
                        </div>
                        <span style="font-size:0.68rem; color:rgb(var(--muted));"><?= $tierCount > 0 ? ($tierCount . ' tier' . ($tierCount > 1 ? 's' : '')) : 'Free admission' ?></span>
                    </div>

                    <div>
                        <span style="font-size:0.68rem; text-transform:uppercase; font-weight:700; color:rgb(var(--muted)); display:block; letter-spacing:0.04em;">Sales Progress</span>
                        <div style="display:flex; justify-content:space-between; align-items:baseline; margin-top:2px;">
                            <strong style="font-size:0.82rem; color:rgb(var(--text));"><?= number_format($sold) ?> / <?= number_format($capacity) ?></strong>
                            <span style="font-size:0.72rem; font-weight:700; color:<?= $pct > 80 ? '#10b981' : 'rgb(var(--brand))' ?>;"><?= $pct ?>%</span>
                        </div>
                        <div style="height:4px; background:rgba(var(--muted),0.15); border-radius:2px; overflow:hidden; margin-top:3px;">
                            <div style="height:100%; width:<?= $pct ?>%; background:<?= $pct > 80 ? '#10b981' : 'rgb(var(--brand))' ?>;"></div>
                        </div>
                    </div>

                    <div>
                        <span style="font-size:0.68rem; text-transform:uppercase; font-weight:700; color:rgb(var(--muted)); display:block; letter-spacing:0.04em;">Gross &amp; Host</span>
                        <div style="font-size:0.84rem; font-weight:800; color:rgb(var(--text)); margin-top:2px;"><?= format_price($ev['gross_revenue'] ?? 0) ?></div>
                        <span style="font-size:0.72rem; color:#10b981; font-weight:700;">Host: <?= format_price($ev['host_earnings'] ?? 0) ?></span>
                    </div>

                    <div>
                        <span style="font-size:0.68rem; text-transform:uppercase; font-weight:700; color:rgb(var(--muted)); display:block; letter-spacing:0.04em;">Platform Profit</span>
                        <div style="font-size:0.86rem; font-weight:800; color:rgb(35, 182, 107); margin-top:2px;"><?= format_price($ev['platform_profit'] ?? 0) ?></div>
                        <span style="font-size:0.68rem; color:rgb(var(--muted));">Fees &amp; Net</span>
                    </div>
                </div>

                <!-- Card Actions Grid -->
                <div class="card-actions-grid">
                    <a href="{{ route('host/event/edit/' . $ev['id']) }}" class="admin-action-btn" style="text-align:center; justify-content:center; text-decoration:none; background:rgba(var(--brand),0.1); color:rgb(var(--brand)); font-weight:700; padding:0.42rem; font-size:0.78rem; border-radius:8px;">
                        ✏️ Edit
                    </a>
                    <a href="{{ route('host/event/management/' . $ev['id']) }}" class="admin-action-btn" style="text-align:center; justify-content:center; text-decoration:none; background:rgba(var(--text),0.06); color:rgb(var(--text)); font-weight:600; padding:0.42rem; font-size:0.78rem; border-radius:8px;">
                        ⚙️ Manage
                    </a>
                    <a href="{{ route('host/event/referrals/' . $ev['id']) }}" class="admin-action-btn" style="text-align:center; justify-content:center; text-decoration:none; background:rgba(16, 185, 129, 0.12); color:#059669; font-weight:600; padding:0.42rem; font-size:0.78rem; border-radius:8px;">
                        🔗 Referrals
                    </a>
                    <form method="POST" action="{{ route('admin/events/duplicate/' . $ev['id']) }}" style="display:inline; margin:0;" onsubmit="return confirm('Clone this event as a new draft?');">
                        <?= csrf_field() ?>
                        <button type="submit" class="admin-action-btn" style="width:100%; text-align:center; justify-content:center; background:rgba(147,51,234,0.1); color:#9333ea; border-color:rgba(147,51,234,0.25); font-weight:600; padding:0.42rem; font-size:0.78rem; border-radius:8px;" title="Duplicate / Clone Event">
                            📋 Clone
                        </button>
                    </form>
                    <div class="card-action-feature-row" style="grid-column: 1 / -1; width: 100%;">
                        <form method="POST" action="{{ route('admin/events/toggle-featured') }}" style="display:block; margin:0;">
                            <?= csrf_field() ?>
                            <input type="hidden" name="event_id" value="<?= $ev['id'] ?>" />
                            <?php if (!empty($ev['is_featured'])): ?>
                                <button type="submit" class="admin-action-btn warning" style="width:100%; text-align:center; justify-content:center; font-weight:700; padding:0.42rem; font-size:0.78rem; border-radius:8px;">
                                    ⭐ Featured on Carousel
                                </button>
                            <?php else: ?>
                                <button type="submit" class="admin-action-btn" style="width:100%; text-align:center; justify-content:center; background:rgba(var(--muted),0.12); color:rgb(var(--text)); font-weight:600; padding:0.42rem; font-size:0.78rem; border-radius:8px;">
                                    ☆ Feature on Carousel
                                </button>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>
            </article>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Empty State -->
    <?php if (empty($events)): ?>
        <div id="events-empty" class="empty-state" style="padding: 2rem; text-align: center;">
            <p>No events found matching your criteria.</p>
        </div>
    <?php endif; ?>

    <!-- Pagination -->
    <nav class="au-pagination" aria-label="Events pagination" id="events-pagination" style="margin-top: 1.5rem;">
        <a href="?page=1" class="au-page-btn disabled" aria-disabled="true">
            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="15 18 9 12 15 6" />
            </svg>
            Prev
        </a>
        <span class="au-page-num active" aria-current="page">1</span>
        <a href="?page=1" class="au-page-btn disabled" aria-disabled="true">
            Next
            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="9 18 15 12 9 6" />
            </svg>
        </a>
    </nav>
</section>

<!-- Confirm Delete Modal -->
<div class="modal-overlay hidden" id="delete-modal" role="dialog" aria-modal="true" aria-labelledby="delete-modal-title">
    <div class="modal-card">
        <h3 id="delete-modal-title">Delete Event</h3>
        <p>Are you sure you want to delete <strong id="delete-event-name">this event</strong>? All attendee records, ticket configurations, and sales logs will be permanently deleted. This action cannot be undone.</p>
        <form method="POST" action="{{ route('admin/events') }}">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="delete" />
            <input type="hidden" name="event_id" id="delete-event-id" value="" />
            <div style="margin: 0.85rem 0; padding: 0.75rem 1rem; background: rgba(239, 68, 68, 0.06); border-radius: 8px; border: 1px dashed rgba(239, 68, 68, 0.3); text-align: left;">
                <label style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.85rem; cursor: pointer; color: #dc2626; font-weight: 600;">
                    <input type="checkbox" name="delete_series" value="1">
                    <span>Delete entire recurring series (if this is a recurring event)</span>
                </label>
            </div>
            <div class="modal-actions">
                <button type="button" class="admin-action-btn" id="delete-cancel-btn">Cancel</button>
                <button type="submit" class="admin-action-btn danger">Yes, Delete Event</button>
            </div>
        </form>
    </div>
</div>

<!-- Series Occurrences Date Picker / Manager Modal -->
<div id="series-occurrences-modal" style="display:none; position:fixed; inset:0; z-index:9999; background:rgba(0,0,0,0.65); backdrop-filter:blur(8px); -webkit-backdrop-filter:blur(8px); justify-content:center; align-items:center; padding:1rem;">
    <div style="width:100%; max-width:min(94vw, 560px); max-height:calc(100dvh - 2rem); overflow-y:auto; background:rgb(var(--card)); border-radius:20px; box-shadow:0 25px 60px rgba(0,0,0,0.3); border:1px solid rgba(var(--muted), 0.2); margin:auto; box-sizing:border-box;">
        <div style="padding:1.35rem 1.6rem 1rem; border-bottom:1px solid rgba(var(--muted), 0.15); display:flex; justify-content:space-between; align-items:center;">
            <div>
                <h3 id="series-modal-title" style="margin:0; font-size:1.1rem; font-weight:800; color:rgb(var(--text));">Recurring Event Dates</h3>
                <p id="series-modal-subtitle" style="margin:0.2rem 0 0; font-size:0.8rem; color:rgb(var(--muted));">Choose any date to edit tickets or manage attendees</p>
            </div>
            <button type="button" onclick="closeSeriesOccurrencesModal()" style="background:none; border:none; font-size:1.5rem; cursor:pointer; color:rgb(var(--muted)); line-height:1;" aria-label="Close">&times;</button>
        </div>
        <div id="series-modal-body" style="padding:1.25rem 1.6rem; min-height: 120px;">
            <div style="text-align: center; color: rgb(var(--muted)); padding: 2rem 0;">Loading dates...</div>
        </div>
        <div style="padding:0.9rem 1.6rem; border-top:1px solid rgba(var(--muted), 0.15); display:flex; justify-content:flex-end;">
            <button type="button" onclick="closeSeriesOccurrencesModal()" class="admin-action-btn" style="padding:0.5rem 1.2rem; font-size:0.85rem; border-radius:8px;">Close</button>
        </div>
    </div>
</div>

<script>
function openSeriesOccurrencesModal(eventId, eventName) {
    const modal = document.getElementById('series-occurrences-modal');
    const title = document.getElementById('series-modal-title');
    const body = document.getElementById('series-modal-body');
    if (!modal) return;

    title.textContent = eventName ? 'Series Dates: ' + eventName : 'Recurring Event Dates';
    body.innerHTML = '<div style="text-align: center; color: rgb(var(--muted)); padding: 2rem 0;">⏳ Loading dates...</div>';
    modal.style.display = 'flex';

    fetch('/host/event/' + eventId + '/occurrences')
        .then(res => res.json())
        .then(data => {
            if (!data.occurrences || data.occurrences.length === 0) {
                body.innerHTML = '<div style="text-align: center; color: rgb(var(--muted)); padding: 1.5rem 0;">No other occurrences found for this series.</div>';
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
                    <div style="display:flex; align-items:center; justify-content:space-between; gap:0.75rem; padding:0.75rem 0.95rem; background: ${isCurrent ? 'rgba(99,102,241,0.08)' : 'rgba(var(--text), 0.03)'}; border: 1.5px solid ${isCurrent ? 'rgba(99,102,241,0.3)' : 'rgba(var(--muted), 0.15)'}; border-radius:10px; flex-wrap:wrap;">
                        <div>
                            <div style="display:flex; align-items:center; gap:0.4rem;">
                                <strong style="font-size:0.88rem; color:rgb(var(--text));">#${occ.recurrence_sequence || '?'} — ${dateFormatted}</strong>
                                ${isCurrent ? '<span style="font-size:0.7rem; font-weight:700; background:#6366f1; color:#fff; padding:0.1rem 0.4rem; border-radius:4px;">Next / Active</span>' : ''}
                                ${isPast ? '<span style="font-size:0.7rem; color:rgb(var(--muted)); background:rgba(var(--muted),0.15); padding:0.1rem 0.4rem; border-radius:4px;">Past</span>' : '<span style="font-size:0.7rem; color:#10b981; background:rgba(16,185,129,0.12); padding:0.1rem 0.4rem; border-radius:4px; font-weight:600;">Upcoming</span>'}
                            </div>
                            <div style="font-size:0.78rem; color:rgb(var(--muted)); margin-top:2px;">
                                ⏰ ${timeFormatted} &bull; Status: ${occ.status || 'Active'}
                            </div>
                        </div>
                        <div style="display:inline-flex; align-items:center; gap:0.4rem; flex-wrap:wrap;">
                            <a href="/admin/events/${occ.id}" class="admin-action-btn" style="padding:0.35rem 0.75rem; font-size:0.78rem; font-weight:700; background:rgba(16,185,129,0.12); color:#10b981; border:1px solid rgba(16,185,129,0.3); border-radius:7px; text-decoration:none;">
                                👁️ Details
                            </a>
                            <a href="/host/event/edit/${occ.id}" class="admin-action-btn" style="padding:0.35rem 0.75rem; font-size:0.78rem; font-weight:700; background:rgba(99,102,241,0.12); color:#6366f1; border:1px solid rgba(99,102,241,0.3); border-radius:7px; text-decoration:none;">
                                ✏️ Edit
                            </a>
                            <a href="/host/event/management/${occ.id}" class="admin-action-btn" style="padding:0.35rem 0.75rem; font-size:0.78rem; font-weight:600; background:rgba(var(--text),0.06); color:rgb(var(--text)); border:1px solid rgba(var(--muted),0.2); border-radius:7px; text-decoration:none;">
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