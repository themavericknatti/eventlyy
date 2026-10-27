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
<section class="page-hero" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:1.25rem;">
    <div>
        <span class="panel-badge">Admin — Events</span>
        <h1 style="margin:0.25rem 0;">Published Events</h1>
        <p class="lede" style="margin:0;">Monitor event performance, ticket sales, platform profits, and organizer accounts.</p>
    </div>
    <div style="display:flex; gap:0.75rem; align-items:center; flex-wrap:wrap;">
        <a href="{{ route('admin/overview') }}" class="admin-action-btn" style="padding:0.6rem 1.1rem; font-size:0.88rem; border-radius:12px; font-weight:600; text-decoration:none; display:inline-flex; align-items:center; gap:0.45rem; background:rgba(var(--text), 0.04); border:1px solid rgba(var(--muted),0.2);">
            <span>←</span> Back to Dashboard
        </a>
        <a href="{{ route('host/event/create') }}" class="admin-action-btn primary" style="padding:0.6rem 1.15rem; font-size:0.88rem; border-radius:12px; font-weight:700; text-decoration:none; display:inline-flex; align-items:center; gap:0.45rem; box-shadow:0 4px 14px rgba(var(--brand),0.35);">
            <span style="font-size:1.05rem;">➕</span> Create Event
        </a>
        <a href="{{ route('host/dashboard') }}" class="admin-action-btn" style="padding:0.6rem 1.1rem; font-size:0.88rem; border-radius:12px; font-weight:600; text-decoration:none; display:inline-flex; align-items:center; gap:0.45rem; background:rgba(var(--text), 0.04); border:1px solid rgba(var(--muted),0.2);">
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
                    <th>Event Title</th>
                    <th>Date & Venue</th>
                    <th>Organizer</th>
                    <th>Sales Progress</th>
                    <th>Gross &amp; Host Earned</th>
                    <th>Platform Profit</th>
                    <th>Status</th>
                    <th style="text-align:right;">Actions</th>
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
                    <tr>
                        <td>
                            <div class="au-user-cell">
                                <span class="au-avatar" style="--avatar-hue:<?= $hue ?>"><?= $initials ?></span>
                                <div>
                                    <a href="{{ route('admin/events/{id}', ['id' => $ev['id']]) }}" style="color:var(--text); text-decoration:none; font-weight:700; font-size:0.95rem;" onmouseover="this.style.color='rgb(var(--brand))'" onmouseout="this.style.color='var(--text)'">
                                        <?= e($ev['name']) ?> ↗
                                    </a>
                                    <div style="display:flex; align-items:center; gap:0.4rem; margin-top:2px;">
                                        <span class="au-card-email"><?= e($ev['category'] ?? 'Event') ?></span>
                                        <?php if (!empty($ev['is_recurring'])): ?>
                                            <span style="font-size:0.68rem; font-weight:700; color:#818cf8; background:rgba(99,102,241,0.12); padding:0.1rem 0.4rem; border-radius:4px;" title="Occurrence #<?= e($ev['recurrence_sequence'] ?? 1) ?> of <?= e($ev['recurrence_count'] ?? '') ?>">
                                                🔄 #<?= e($ev['recurrence_sequence'] ?? 1) ?>/<?= e($ev['recurrence_count'] ?? '') ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="au-date-cell">
                            <?= $dateStr ?><br />
                            <span style="font-size:0.8rem;color:rgb(var(--muted))"><?= e($ev['location']) ?></span>
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

    <!-- Mobile Screen Card View (CSS: display:none on desktop, flex on mobile <=768px) -->
    <div class="au-cards-list" id="events-cards">
        <?php if (!empty($events)): ?>
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
            <article class="au-user-card">
                <div class="au-card-top">
                    <div class="au-user-cell">
                        <span class="au-avatar" style="--avatar-hue:<?= $hue ?>"><?= $initials ?></span>
                        <div>
                            <a href="{{ route('admin/events/{id}', ['id' => $ev['id']]) }}" style="color:var(--text); text-decoration:none; font-weight:700; font-size:0.95rem;">
                                <?= e($ev['name']) ?> ↗
                            </a>
                            <div style="display:flex; align-items:center; gap:0.4rem; margin-top:2px;">
                                <span class="au-card-email"><?= e($ev['category'] ?? 'Event') ?> • <?= e($ev['organizer_email']) ?></span>
                                <?php if (!empty($ev['is_recurring'])): ?>
                                    <span style="font-size:0.68rem; font-weight:700; color:#818cf8; background:rgba(99,102,241,0.12); padding:0.1rem 0.4rem; border-radius:4px;">
                                        🔄 #<?= e($ev['recurrence_sequence'] ?? 1) ?>/<?= e($ev['recurrence_count'] ?? '') ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <span class="<?= $evStatusClass ?>"><?= $evStatusLabel ?></span>
                </div>
                <div class="au-card-meta" style="flex-direction: column; align-items: flex-start; gap: 0.25rem; margin: 0.5rem 0;">
                    <span class="au-card-date">🗓 <?= $dateStr ?> | 📍 <?= e($ev['location']) ?></span>
                    <span style="font-weight:600; font-size:0.82rem; color:rgb(var(--text));">Tickets Sold: <?= $sold ?> / <?= $capacity ?> (<?= $pct ?>%)</span>
                    <div style="font-size:0.82rem; display:flex; gap:0.5rem; flex-wrap:wrap; margin-top:2px;">
                        <span style="font-weight:700; color:var(--text);">Gross: <?= format_price($ev['gross_revenue'] ?? 0) ?></span>
                        <span style="font-weight:700; color:#10b981;">Host: <?= format_price($ev['host_earnings'] ?? 0) ?></span>
                        <span style="font-weight:700; color:rgb(35, 182, 107);">Profit: <?= format_price($ev['platform_profit'] ?? 0) ?></span>
                    </div>
                </div>
                <div class="admin-actions au-card-actions" style="margin-top:0.5rem; display:flex; flex-wrap:wrap; gap:0.4rem;">
                    <a href="{{ route('host/event/edit/{id}', ['id' => $ev['id']]) }}" class="admin-action-btn" style="flex:1; text-align:center; text-decoration:none; background:rgba(var(--brand),0.1); color:rgb(var(--brand)); font-weight:700; padding:0.4rem 0.5rem; font-size:0.8rem; border-radius:8px;">
                        ✏️ Edit
                    </a>
                    <a href="{{ route('host/event/management/{id}', ['id' => $ev['id']]) }}" class="admin-action-btn" style="flex:1; text-align:center; text-decoration:none; background:rgba(var(--text),0.06); color:var(--text); font-weight:600; padding:0.4rem 0.5rem; font-size:0.8rem; border-radius:8px;">
                        ⚙️ Manage
                    </a>
                    <a href="{{ route('host/event/referrals/{id}', ['id' => $ev['id']]) }}" class="admin-action-btn" style="flex:1; text-align:center; text-decoration:none; background:rgba(16, 185, 129, 0.12); color:#059669; font-weight:600; padding:0.4rem 0.5rem; font-size:0.8rem; border-radius:8px;">
                        🔗 Ref
                    </a>
                    <form method="POST" action="{{ route('admin/events/duplicate/' . $ev['id']) }}" style="display:inline; flex:1;" onsubmit="return confirm('Clone this event as a new draft?');">
                        <?= csrf_field() ?>
                        <button type="submit" class="admin-action-btn" style="width:100%; text-align:center; background:rgba(147,51,234,0.1); color:#9333ea; border-color:rgba(147,51,234,0.25); font-weight:600; padding:0.4rem 0.5rem; font-size:0.8rem; border-radius:8px;" title="Duplicate / Clone Event">
                            📋 Clone
                        </button>
                    </form>
                    <form method="POST" action="{{ route('admin/events/toggle-featured') }}" style="display:inline; flex:1; min-width:100%;">
                        <?= csrf_field() ?>
                        <input type="hidden" name="event_id" value="<?= $ev['id'] ?>" />
                        <?php if (!empty($ev['is_featured'])): ?>
                            <button type="submit" class="admin-action-btn warning" style="width:100%; font-weight:700; padding:0.4rem 0.5rem; font-size:0.8rem; border-radius:8px;">
                                ⭐ Featured on Carousel
                            </button>
                        <?php else: ?>
                            <button type="submit" class="admin-action-btn" style="width:100%; background:rgba(var(--muted),0.12); color:var(--text); font-weight:600; padding:0.4rem 0.5rem; font-size:0.8rem; border-radius:8px;">
                                ☆ Feature on Carousel
                            </button>
                        <?php endif; ?>
                    </form>
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
@endsection