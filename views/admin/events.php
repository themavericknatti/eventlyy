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
/* Admin Events Responsive Polish */
.admin-events-stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(190px, 1fr));
    gap: 1rem;
    margin-bottom: 1.75rem;
}

.au-table th {
    white-space: nowrap;
}

.admin-event-category-badge {
    white-space: nowrap !important;
    word-break: keep-all !important;
    display: inline-block !important;
    flex-shrink: 0 !important;
    line-height: 1.2 !important;
}

.au-status-badge, .au-role-badge {
    white-space: nowrap !important;
    word-break: keep-all !important;
    display: inline-block !important;
    flex-shrink: 0 !important;
}

.au-table-wrap {
    background: rgb(var(--card));
    border-radius: 18px;
    border: 1px solid rgba(var(--muted), 0.12);
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}

.au-cards-list {
    display: none;
    flex-direction: column;
    gap: 1rem;
}

.admin-event-card {
    background: rgb(var(--card));
    border: 1px solid rgba(var(--muted), 0.15);
    border-radius: 18px;
    padding: 1.25rem;
    box-shadow: 0 4px 18px rgba(0, 0, 0, 0.03);
    transition: all 0.2s ease;
}

.admin-event-card:hover {
    border-color: rgba(var(--brand), 0.3);
    box-shadow: 0 6px 22px rgba(0, 0, 0, 0.06);
}

.card-metrics-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 0.75rem;
    background: rgba(var(--muted), 0.04);
    border-radius: 12px;
    padding: 0.85rem 1rem;
    margin: 0.85rem 0;
}

.card-actions-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 0.45rem;
    margin-top: 0.85rem;
    padding-top: 0.75rem;
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
    padding: clamp(1rem, 2vw, 1.75rem) 0;
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
    gap: 0.65rem !important;
    flex-wrap: nowrap !important;
}

/* High-Contrast Accessible Dates Dropdown */
.admin-table-dates-dropdown-wrap {
    margin-top: 0.45rem;
    position: relative;
    max-width: 260px;
}

.admin-table-dates-select {
    width: 100%;
    padding: 0.42rem 1.85rem 0.42rem 0.75rem;
    font-size: 0.8rem;
    font-weight: 700;
    font-family: inherit;
    border-radius: 8px;
    border: 1.5px solid rgba(99, 102, 241, 0.45);
    background-color: #ffffff;
    color: #0f172a;
    cursor: pointer;
    outline: none;
    appearance: none;
    -webkit-appearance: none;
    -moz-appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' viewBox='0 0 24 24' fill='none' stroke='%236366f1' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 0.55rem center;
    background-size: 13px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    transition: all 0.2s ease;
}

.admin-table-dates-select:hover {
    border-color: #6366f1;
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15);
}

.admin-table-dates-select:focus {
    border-color: #4f46e5;
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.28);
}

.admin-table-dates-select option {
    background-color: #ffffff !important;
    color: #0f172a !important;
    font-weight: 600;
    padding: 8px 12px;
}

:root[data-theme='dark'] .admin-table-dates-select {
    background-color: #1e293b !important;
    color: #f8fafc !important;
    border-color: rgba(129, 140, 248, 0.5) !important;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' viewBox='0 0 24 24' fill='none' stroke='%23818cf8' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.35);
}

:root[data-theme='dark'] .admin-table-dates-select option {
    background-color: #1e293b !important;
    color: #f8fafc !important;
}

.admin-events-btn {
    padding: 0.55rem 1rem;
    font-size: 0.85rem;
    border-radius: 12px;
    font-weight: 600;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
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
        gap: 0.5rem !important;
    }
    .admin-events-btn {
        justify-content: center !important;
        text-align: center !important;
        padding: 0.55rem 0.5rem !important;
        font-size: 0.8rem !important;
    }
}

@media (max-width: 680px) {
    .admin-events-hero-actions {
        display: flex !important;
        flex-direction: column !important;
        width: 100% !important;
        gap: 0.5rem !important;
    }
    .admin-events-hero-actions .admin-events-btn {
        width: 100% !important;
        min-height: 42px !important;
        justify-content: center !important;
        text-align: center !important;
        box-sizing: border-box !important;
    }
}

@media (max-width: 600px) {
    .card-metrics-grid {
        grid-template-columns: 1fr;
        gap: 0.6rem;
    }
    .card-actions-grid {
        grid-template-columns: 1fr 1fr;
    }
    .card-actions-grid .admin-action-btn {
        min-height: 40px;
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

@media (max-width: 380px) {
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

    <!-- Desktop & Tablet Data Table -->
    <div class="au-table-wrap">
        <table class="au-table">
            <thead>
                <tr>
                    <th style="min-width:230px;">Event Title</th>
                    <th style="min-width:180px;">Date &amp; Venue</th>
                    <th style="min-width:160px;">Organizer</th>
                    <th style="min-width:140px;">Sales Progress</th>
                    <th style="min-width:140px;">Gross &amp; Host Earned</th>
                    <th style="min-width:120px;">Platform Profit</th>
                    <th style="min-width:110px;">Status</th>
                    <th style="min-width:240px; text-align:right;">Actions</th>
                </tr>
            </thead>
            <tbody id="events-tbody">
                <?php if (empty($events)): ?>
                    <tr>
                        <td colspan="8" class="text-center" style="padding: 2rem;">No events found matching your criteria.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($events as $ev): 
                        $hue = rand(100, 360);
                        $initials = strtoupper(substr($ev['name'] ?? 'E', 0, 2));
                        $dateStr = date('M d, Y', strtotime($ev['date']));
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
                            $evStatusLabel = '🔴 Live Now';
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
                    ?>
                    <tr>
                        <td>
                            <div class="au-user-cell">
                                <span class="au-avatar" style="--avatar-hue:<?= $hue ?>"><?= $initials ?></span>
                                <div>
                                    <a href="{{ route('admin/events/{id}', ['id' => $ev['id']]) }}" style="color:var(--text); text-decoration:none; font-weight:700; font-size:0.95rem;" onmouseover="this.style.color='rgb(var(--brand))'" onmouseout="this.style.color='var(--text)'">
                                        <?= e($ev['name']) ?> ↗
                                    </a>
                                    <div style="display:flex; align-items:center; gap:0.4rem; margin-top:2px; flex-wrap:nowrap;">
                                        <span class="admin-event-category-badge" style="font-size:0.75rem; font-weight:700; color:rgb(var(--muted)); background:rgba(var(--muted),0.08); padding:0.12rem 0.5rem; border-radius:5px; white-space:nowrap; flex-shrink:0;"><?= e($ev['category'] ?? 'Event') ?></span>
                                        <?php if (!empty($ev['is_recurring'])): ?>
                                            <button type="button" onclick="openSeriesOccurrencesModal('<?= $ev['id'] ?>', '<?= addslashes(e($ev['name'])) ?>')" style="font-size:0.72rem; font-weight:700; color:#818cf8; background:rgba(99,102,241,0.12); border:1px solid rgba(99,102,241,0.25); padding:0.12rem 0.45rem; border-radius:5px; cursor:pointer; display:inline-flex; align-items:center; gap:0.25rem; white-space:nowrap; flex-shrink:0;" title="Click to view all dates in this series to edit or manage">
                                                🔄 Recurring (<?= e($ev['recurrence_count'] ?? '') ?> Dates &bull; #<?= e($ev['recurrence_sequence'] ?? 1) ?>) ▾
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="au-date-cell">
                            <div style="font-weight: 700; color: rgb(var(--text)); font-size: 0.9rem;">
                                <?= $dateStr ?>
                            </div>
                            <span style="font-size:0.8rem;color:rgb(var(--muted))"><?= e($ev['location'] ?: 'Online / TBD') ?></span>
                            <?php if (!empty($ev['is_recurring']) && !empty($ev['series_occurrences']) && count($ev['series_occurrences']) > 1): ?>
                                <div class="admin-table-dates-dropdown-wrap">
                                    <select class="admin-table-dates-select" onchange="if(this.value) window.location.href = this.value;" title="Quick Switch Date">
                                        <option value="" disabled selected>📅 Series Dates (<?= count($ev['series_occurrences']) ?> Occurrences) ▾</option>
                                        <?php foreach ($ev['series_occurrences'] as $occ): 
                                            $occDate = strtotime($occ['date']);
                                            $isPast = $occDate < time();
                                            $isCurrent = ($occ['id'] == $ev['id']);
                                        ?>
                                            <option value="{{ route('admin/events/' . $occ['id']) }}">
                                                #<?= e($occ['recurrence_sequence'] ?? '?') ?>: <?= date('D, M d, Y • g:i A', $occDate) ?> <?= $isCurrent ? '★ (Active Next)' : ($isPast ? '(Past)' : '(Upcoming)') ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td class="au-email-cell"><?= e($ev['organizer_email']) ?></td>
                        <td>
                            <div style="width: 140px;">
                                <span style="font-weight:600;font-size:0.85rem"><?= $sold ?> / <?= $capacity ?> sold</span>
                                <?php if (($ev['issued'] ?? 0) > 0): ?>
                                    <span style="font-size:0.72rem; color:#6366f1; display:block;">+<?= number_format($ev['issued']) ?> issued</span>
                                <?php endif; ?>
                                <div class="progress-bar-bg"
                                    style="height: 6px; background: rgba(var(--muted), 0.1); border-radius: 3px; margin-top: 4px; overflow: hidden;">
                                    <div class="progress-bar-fill"
                                        style="height: 100%; width: <?= $pct ?>%; background: rgb(var(--brand));"></div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <strong style="font-size:0.92rem; color:var(--text);"><?= format_price($ev['gross_revenue'] ?? 0) ?></strong>
                            <div style="font-size:0.75rem; color:#10b981; font-weight:600;">Host: <?= format_price($ev['host_earnings'] ?? 0) ?></div>
                        </td>
                        <td>
                            <strong style="color:rgb(35, 182, 107); font-size:0.92rem;"><?= format_price($ev['platform_profit'] ?? 0) ?></strong>
                            <div style="font-size:0.75rem; color:var(--text-muted);">Fees + Charges</div>
                        </td>
                        <td>
                            <span class="<?= $evStatusClass ?>"><?= $evStatusLabel ?></span>
                        </td>
                        <td style="text-align:right; white-space:nowrap;">
                            <div style="display:inline-flex; gap:0.35rem; align-items:center; justify-content:flex-end;">
                                <a href="{{ route('host/event/edit/{id}', ['id' => $ev['id']]) }}" class="admin-action-btn" style="padding:0.35rem 0.6rem; font-size:0.78rem; border-radius:8px; background:rgba(var(--brand),0.1); color:rgb(var(--brand)); font-weight:700; text-decoration:none;" title="Edit Event">
                                    ✏️ Edit
                                </a>
                                <a href="{{ route('host/event/management/{id}', ['id' => $ev['id']]) }}" class="admin-action-btn" style="padding:0.35rem 0.6rem; font-size:0.78rem; border-radius:8px; background:rgba(var(--text),0.06); color:var(--text); font-weight:600; text-decoration:none;" title="Manage Event, Tickets & Staff">
                                    ⚙️ Manage
                                </a>
                                <a href="{{ route('host/event/referrals/{id}', ['id' => $ev['id']]) }}" class="admin-action-btn" style="padding:0.35rem 0.6rem; font-size:0.78rem; border-radius:8px; background:rgba(16, 185, 129, 0.12); color:#059669; font-weight:600; text-decoration:none;" title="Referrals & Affiliates">
                                    🔗 Ref
                                </a>
                                <form method="POST" action="{{ route('admin/events/duplicate/' . $ev['id']) }}" style="display:inline; margin:0;" onsubmit="return confirm('Clone this event as a new draft?');">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="admin-action-btn" style="padding:0.35rem 0.6rem; font-size:0.78rem; border-radius:8px; background:rgba(147,51,234,0.1); color:#9333ea; border-color:rgba(147,51,234,0.25); font-weight:600;" title="Duplicate / Clone Event">
                                        📋 Clone
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('admin/events/toggle-featured') }}" style="display:inline; margin:0;">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="event_id" value="<?= $ev['id'] ?>" />
                                    <?php if (!empty($ev['is_featured'])): ?>
                                        <button type="submit" class="admin-action-btn warning" style="padding:0.35rem 0.6rem; font-size:0.78rem; border-radius:8px; font-weight:700;" title="Remove from homepage carousel">
                                            ⭐ Featured
                                        </button>
                                    <?php else: ?>
                                        <button type="submit" class="admin-action-btn" style="padding:0.35rem 0.6rem; font-size:0.78rem; border-radius:8px; background:rgba(var(--muted),0.12); color:var(--text); font-weight:600;" title="Feature on homepage carousel">
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
                $timeStr = date('h:i A', strtotime($ev['date']));
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
            ?>
            <article class="admin-event-card">
                <!-- Card Header -->
                <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:0.75rem;">
                    <div style="display:flex; align-items:center; gap:0.65rem; min-width:0;">
                        <span class="au-avatar" style="--avatar-hue:<?= $hue ?>; flex-shrink:0;"><?= $initials ?></span>
                        <div style="min-width:0;">
                            <a href="{{ route('admin/events/' . $ev['id']) }}" style="color:rgb(var(--text)); text-decoration:none; font-weight:800; font-size:1.02rem; line-height:1.25; display:inline-block; max-width:100%; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                                <?= e($ev['name']) ?> ↗
                            </a>
                            <div style="display:flex; align-items:center; gap:0.4rem; margin-top:3px; flex-wrap:wrap;">
                                <span style="font-size:0.72rem; font-weight:700; color:rgb(var(--muted)); background:rgba(var(--muted),0.08); padding:0.12rem 0.45rem; border-radius:5px; white-space:nowrap;">
                                    <?= e($ev['category'] ?? 'Event') ?>
                                </span>
                                <?php if (!empty($ev['is_recurring'])): ?>
                                    <button type="button" onclick="openSeriesOccurrencesModal('<?= $ev['id'] ?>', '<?= addslashes(e($ev['name'])) ?>')" style="font-size:0.72rem; font-weight:700; color:#818cf8; background:rgba(99,102,241,0.12); border:1px solid rgba(99,102,241,0.25); padding:0.12rem 0.45rem; border-radius:5px; cursor:pointer; display:inline-flex; align-items:center; gap:0.25rem;" title="Click to view all dates in this series">
                                        🔄 Recurring (<?= e($ev['recurrence_count'] ?? '') ?> Dates &bull; #<?= e($ev['recurrence_sequence'] ?? 1) ?>) ▾
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <span class="<?= $evStatusClass ?>" style="flex-shrink:0; font-size:0.76rem; font-weight:700; white-space:nowrap;"><?= $evStatusLabel ?></span>
                </div>

                <!-- Card Metrics Grid -->
                <div class="card-metrics-grid">
                    <div>
                        <span style="font-size:0.72rem; color:rgb(var(--muted)); display:block;">Schedule &amp; Location</span>
                        <div style="font-size:0.84rem; font-weight:700; color:rgb(var(--text)); margin-top:2px;">
                            🗓️ <?= $dateStr ?> • <?= $timeStr ?>
                        </div>
                        <div style="font-size:0.76rem; color:rgb(var(--muted)); overflow:hidden; text-overflow:ellipsis; white-space:nowrap; margin-top:1px;">
                            📍 <?= e($ev['location'] ?: 'Online / TBD') ?>
                        </div>
                        <?php if (!empty($ev['is_recurring']) && !empty($ev['series_occurrences']) && count($ev['series_occurrences']) > 1): ?>
                            <div class="admin-table-dates-dropdown-wrap" style="max-width:100%; margin-top:0.4rem;">
                                <select class="admin-table-dates-select" onchange="if(this.value) window.location.href = this.value;" title="Quick Switch Date">
                                    <option value="" disabled selected>📅 Switch Series Date (<?= count($ev['series_occurrences']) ?>) ▾</option>
                                    <?php foreach ($ev['series_occurrences'] as $occ): 
                                        $occDate = strtotime($occ['date']);
                                        $isPast = $occDate < time();
                                        $isCurrent = ($occ['id'] == $ev['id']);
                                    ?>
                                        <option value="{{ route('admin/events/' . $occ['id']) }}">
                                            #<?= e($occ['recurrence_sequence'] ?? '?') ?>: <?= date('D, M d, Y', $occDate) ?> <?= $isCurrent ? '★ (Active)' : ($isPast ? '(Past)' : '(Upcoming)') ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div>
                        <span style="font-size:0.72rem; color:rgb(var(--muted)); display:block;">Tickets Sold</span>
                        <div style="display:flex; justify-content:space-between; align-items:baseline; margin-top:2px;">
                            <strong style="font-size:0.86rem; color:rgb(var(--text));"><?= number_format($sold) ?> / <?= number_format($capacity) ?></strong>
                            <span style="font-size:0.75rem; font-weight:700; color:rgb(var(--brand));"><?= $pct ?>%</span>
                        </div>
                        <div style="height:5px; background:rgba(var(--muted),0.15); border-radius:3px; overflow:hidden; margin-top:4px;">
                            <div style="height:100%; width:<?= $pct ?>%; background:<?= $pct > 80 ? '#10b981' : 'rgb(var(--brand))' ?>;"></div>
                        </div>
                    </div>

                    <div>
                        <span style="font-size:0.72rem; color:rgb(var(--muted)); display:block;">Organizer</span>
                        <div style="font-size:0.82rem; font-weight:600; color:rgb(var(--text)); overflow:hidden; text-overflow:ellipsis; white-space:nowrap; margin-top:2px;">
                            👤 <?= e($ev['organizer_email']) ?>
                        </div>
                    </div>

                    <div>
                        <span style="font-size:0.72rem; color:rgb(var(--muted)); display:block;">Financials</span>
                        <div style="display:flex; flex-direction:column; gap:2px; margin-top:2px; font-size:0.78rem;">
                            <div style="display:flex; justify-content:space-between; gap:0.4rem; white-space:nowrap;">
                                <span style="color:rgb(var(--muted));">Gross:</span>
                                <strong style="color:rgb(var(--text));"><?= format_price($ev['gross_revenue'] ?? 0) ?></strong>
                            </div>
                            <div style="display:flex; justify-content:space-between; gap:0.4rem; white-space:nowrap;">
                                <span style="color:rgb(var(--muted));">Host:</span>
                                <strong style="color:#10b981;"><?= format_price($ev['host_earnings'] ?? 0) ?></strong>
                            </div>
                            <div style="display:flex; justify-content:space-between; gap:0.4rem; white-space:nowrap;">
                                <span style="color:rgb(var(--muted));">Profit:</span>
                                <strong style="color:rgb(35, 182, 107);"><?= format_price($ev['platform_profit'] ?? 0) ?></strong>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card Actions Grid -->
                <div class="card-actions-grid">
                    <a href="{{ route('host/event/edit/' . $ev['id']) }}" class="admin-action-btn" style="text-align:center; justify-content:center; text-decoration:none; background:rgba(var(--brand),0.1); color:rgb(var(--brand)); font-weight:700; padding:0.5rem; font-size:0.82rem; border-radius:10px;">
                        ✏️ Edit
                    </a>
                    <a href="{{ route('host/event/management/' . $ev['id']) }}" class="admin-action-btn" style="text-align:center; justify-content:center; text-decoration:none; background:rgba(var(--text),0.06); color:rgb(var(--text)); font-weight:600; padding:0.5rem; font-size:0.82rem; border-radius:10px;">
                        ⚙️ Manage
                    </a>
                    <a href="{{ route('host/event/referrals/' . $ev['id']) }}" class="admin-action-btn" style="text-align:center; justify-content:center; text-decoration:none; background:rgba(16, 185, 129, 0.12); color:#059669; font-weight:600; padding:0.5rem; font-size:0.82rem; border-radius:10px;">
                        🔗 Referrals
                    </a>
                    <form method="POST" action="{{ route('admin/events/duplicate/' . $ev['id']) }}" style="display:inline; margin:0;" onsubmit="return confirm('Clone this event as a new draft?');">
                        <?= csrf_field() ?>
                        <button type="submit" class="admin-action-btn" style="width:100%; text-align:center; justify-content:center; background:rgba(147,51,234,0.1); color:#9333ea; border-color:rgba(147,51,234,0.25); font-weight:600; padding:0.5rem; font-size:0.82rem; border-radius:10px;" title="Duplicate / Clone Event">
                            📋 Clone
                        </button>
                    </form>
                    <div class="card-action-feature-row" style="grid-column: 1 / -1; width: 100%;">
                        <form method="POST" action="{{ route('admin/events/toggle-featured') }}" style="display:block; margin:0;">
                            <?= csrf_field() ?>
                            <input type="hidden" name="event_id" value="<?= $ev['id'] ?>" />
                            <?php if (!empty($ev['is_featured'])): ?>
                                <button type="submit" class="admin-action-btn warning" style="width:100%; text-align:center; justify-content:center; font-weight:700; padding:0.5rem; font-size:0.82rem; border-radius:10px;">
                                    ⭐ Featured on Carousel
                                </button>
                            <?php else: ?>
                                <button type="submit" class="admin-action-btn" style="width:100%; text-align:center; justify-content:center; background:rgba(var(--muted),0.12); color:rgb(var(--text)); font-weight:600; padding:0.5rem; font-size:0.82rem; border-radius:10px;">
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