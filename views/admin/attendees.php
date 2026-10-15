<?php

/**
 * Admin Attendees Directory
 *
 * This file contains the HTML markup for the admin attendees & ticket buyers contact directory.
 *
 * @package Evently
 * @subpackage Views/Admin
 * @author Celio Natti
 * @license MIT License
 * @version 1.0.0
 */

// Initialize default variables if not provided
$unique_attendees = $unique_attendees ?? 0;
$guest_attendees = $guest_attendees ?? 0;
$registered_attendees = $registered_attendees ?? 0;
$total_paid_tickets = $total_paid_tickets ?? 0;
$attendees = $attendees ?? [];
$events = $events ?? [];
$search = $search ?? '';
$type = $type ?? '';
$eventId = $eventId ?? '';
$page = $page ?? 1;
$totalPages = $totalPages ?? 1;

?>

@extends('layouts/admin')

@section('title')
Admin Attendees Directory - Deduplicated Contact Records
@endsection

@section('content')
<section class="page-hero">
    <div>
        <a href="{{ route('admin/settings') }}" style="color:var(--brand); text-decoration:none; font-size:0.85rem; font-weight:700; display:inline-block; margin-bottom:0.4rem;">← Back to Settings</a>
        <br>
        <span class="panel-badge">Admin — Attendees & Customers</span>
        <h1>Attendee Contact Directory</h1>
        <p class="lede">Unique contact records grouped by email for all registered users and guest ticket buyers.</p>
    </div>
</section>

<!-- Summary Stats -->
<section class="admin-users-stats">
    <div class="au-stat-card">
        <span class="au-stat-icon au-stat-icon--brand">
            <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round">
                <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4-4v2" />
                <circle cx="9" cy="7" r="4" />
                <path d="M23 21v-2a4 4 0 00-3-3.87" />
                <path d="M16 3.13a4 4 0 010 7.75" />
            </svg>
        </span>
        <div class="au-stat-body">
            <span class="au-stat-num"><?= number_format($unique_attendees) ?></span>
            <span class="au-stat-lbl">Unique Contact Emails</span>
        </div>
    </div>
    <div class="au-stat-card">
        <span class="au-stat-icon au-stat-icon--warning">
            <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round">
                <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2" />
                <circle cx="12" cy="7" r="4" />
            </svg>
        </span>
        <div class="au-stat-body">
            <span class="au-stat-num"><?= number_format($guest_attendees) ?></span>
            <span class="au-stat-lbl">Guest Buyers (Unregistered)</span>
        </div>
    </div>
    <div class="au-stat-card">
        <span class="au-stat-icon au-stat-icon--success">
            <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 11.08V12a10 10 0 11-5.93-9.14" />
                <polyline points="22 4 12 14.01 9 11.01" />
            </svg>
        </span>
        <div class="au-stat-body">
            <span class="au-stat-num"><?= number_format($registered_attendees) ?></span>
            <span class="au-stat-lbl">Registered Accounts</span>
        </div>
    </div>
    <div class="au-stat-card">
        <span class="au-stat-icon">
            <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round">
                <rect x="2" y="7" width="20" height="14" rx="2" ry="2" />
                <path d="M16 21V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v16" />
            </svg>
        </span>
        <div class="au-stat-body">
            <span class="au-stat-num"><?= number_format($total_paid_tickets) ?></span>
            <span class="au-stat-lbl">Total Tickets Purchased</span>
        </div>
    </div>
</section>

<!-- Toolbar: Search & Filter -->
<section class="admin-users-toolbar">
    <form method="GET" action="" class="au-toolbar-form">
        <div class="au-search-wrap">
            <svg class="au-search-icon" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor"
                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="11" cy="11" r="8" />
                <line x1="21" y1="21" x2="16.65" y2="16.65" />
            </svg>
            <input type="search" name="q" id="attendees-search" placeholder="Search by name, email, or event name..."
                aria-label="Search attendees" value="<?= e($search ?? '') ?>" />
        </div>
        <div class="au-filter-group">
            <select name="type" id="attendees-type-filter" aria-label="Filter by buyer type">
                <option value="">All Account Types</option>
                <option value="guest" <?= ($type ?? '') === 'guest' ? 'selected' : '' ?>>Guest Buyers (Unregistered)</option>
                <option value="registered" <?= ($type ?? '') === 'registered' ? 'selected' : '' ?>>Registered Accounts</option>
            </select>

            <select name="event_id" id="attendees-event-filter" aria-label="Filter by event">
                <option value="">All Events</option>
                <?php foreach ($events as $evt): ?>
                    <option value="<?= $evt['id'] ?>" <?= ((string)$eventId === (string)$evt['id']) ? 'selected' : '' ?>>
                        <?= e($evt['name']) ?>
                    </option>
                <?php endforeach; ?>
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

<!-- Grouped Attendees Table Section -->
<section class="admin-users-table-section">
    <div class="au-table-header">
        <h2>Deduplicated Contact Records</h2>
        <a href="{{ route('admin/attendees/export') }}?q=<?= urlencode($search ?? '') ?>&type=<?= urlencode($type ?? '') ?>&event_id=<?= urlencode($eventId ?? '') ?>" class="admin-action-btn primary" style="background:#10b981; border-color:#10b981;">
            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5"
                stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4" />
                <polyline points="7 10 12 15 17 10" />
                <line x1="12" y1="15" x2="12" y2="3" />
            </svg>
            Export CSV Directory
        </a>
    </div>

    <!-- Desktop Table View -->
    <div class="au-table-wrap">
        <table class="au-table" id="attendees-table">
            <thead>
                <tr>
                    <th>Attendee Contact</th>
                    <th>Account Type</th>
                    <th>Events Purchased</th>
                    <th>Tickets</th>
                    <th>Total Spent</th>
                    <th>Last Purchase Date</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($attendees)): ?>
                    <tr>
                        <td colspan="6" class="text-center" style="padding:2.5rem; color:rgb(var(--muted));">
                            No contact records found matching your filters.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($attendees as $att): 
                        $hue = rand(100, 360);
                        $initials = strtoupper(substr($att['attendee_name'] ?? 'A', 0, 2));
                        $isRegistered = !empty($att['user_id']);
                    ?>
                    <tr>
                        <td>
                            <div class="au-user-cell">
                                <span class="au-avatar" style="--avatar-hue:<?= $hue ?>"><?= $initials ?></span>
                                <div>
                                    <span class="au-user-name" style="font-weight:700; color:rgb(var(--text));"><?= e($att['attendee_name']) ?></span>
                                    <span class="au-card-email" style="font-size:0.82rem; color:rgb(var(--muted)); display:block;"><?= e($att['attendee_email']) ?></span>
                                </div>
                            </div>
                        </td>
                        <td>
                            <?php if ($isRegistered): ?>
                                <span style="background:rgba(16, 185, 129, 0.12); color:#059669; border:1px solid rgba(16, 185, 129, 0.25); font-size:0.75rem; font-weight:700; padding:3px 8px; border-radius:6px; display:inline-flex; align-items:center; gap:4px;">
                                    👤 Registered User
                                </span>
                            <?php else: ?>
                                <span style="background:rgba(245, 158, 11, 0.14); color:#d97706; border:1px solid rgba(245, 158, 11, 0.25); font-size:0.75rem; font-weight:700; padding:3px 8px; border-radius:6px; display:inline-flex; align-items:center; gap:4px;" title="Unregistered buyer - contact saved from ticket purchase">
                                    🌐 Guest Buyer
                                </span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <strong style="font-size:0.86rem; color:rgb(var(--text)); font-weight:600; display:block;">
                                <?= e($att['event_names'] ?? 'N/A') ?>
                            </strong>
                            <span style="font-size:0.76rem; color:rgb(var(--muted));"><?= (int)$att['total_events'] ?> event(s)</span>
                        </td>
                        <td>
                            <span style="font-size:0.88rem; font-weight:700; color:rgb(var(--text));"><?= (int)$att['total_tickets'] ?> ticket(s)</span>
                        </td>
                        <td>
                            <strong style="color:#10b981; font-size:0.92rem; font-weight:800;"><?= format_price($att['total_spent']) ?></strong>
                        </td>
                        <td>
                            <span style="font-size:0.82rem; color:rgb(var(--muted));"><?= date('M d, Y • g:i A', strtotime($att['last_purchase_date'])) ?></span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Mobile Card View -->
    <div class="au-cards-list" id="attendees-cards">
        <?php if (!empty($attendees)): ?>
            <?php foreach ($attendees as $att): 
                $hue = rand(100, 360);
                $initials = strtoupper(substr($att['attendee_name'] ?? 'A', 0, 2));
                $isRegistered = !empty($att['user_id']);
            ?>
            <article class="au-user-card">
                <div class="au-card-top">
                    <div class="au-user-cell">
                        <span class="au-avatar" style="--avatar-hue:<?= $hue ?>"><?= $initials ?></span>
                        <div>
                            <span class="au-user-name"><?= e($att['attendee_name']) ?></span>
                            <span class="au-card-email"><?= e($att['attendee_email']) ?></span>
                        </div>
                    </div>
                    <?php if ($isRegistered): ?>
                        <span style="background:rgba(16, 185, 129, 0.12); color:#059669; font-size:0.72rem; font-weight:700; padding:2px 7px; border-radius:6px;">Registered</span>
                    <?php else: ?>
                        <span style="background:rgba(245, 158, 11, 0.14); color:#d97706; font-size:0.72rem; font-weight:700; padding:2px 7px; border-radius:6px;">Guest</span>
                    <?php endif; ?>
                </div>
                <div class="au-card-meta" style="flex-direction: column; align-items: flex-start; gap: 0.35rem;">
                    <span style="font-weight:700; font-size:0.88rem; color:rgb(var(--text));">🎟️ Events: <?= e($att['event_names'] ?? 'N/A') ?></span>
                    <span style="font-size:0.8rem; color:rgb(var(--muted));">Tickets: <?= (int)$att['total_tickets'] ?> • Total Spent: <strong style="color:#10b981;"><?= format_price($att['total_spent']) ?></strong></span>
                    <span style="font-size:0.76rem; color:rgb(var(--muted));">Last Active: <?= date('M d, Y', strtotime($att['last_purchase_date'])) ?></span>
                </div>
            </article>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Pagination -->
    <?php if (($totalPages ?? 1) > 1): ?>
    <nav class="au-pagination" aria-label="Attendees pagination" id="attendees-pagination">
        <?php if ($page > 1): ?>
            <a href="?page=<?= $page - 1 ?>&q=<?= urlencode($search ?? '') ?>&type=<?= urlencode($type ?? '') ?>&event_id=<?= urlencode($eventId ?? '') ?>" class="au-page-btn">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5"
                    stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="15 18 9 12 15 6" />
                </svg>
                Prev
            </a>
        <?php else: ?>
            <span class="au-page-btn disabled" aria-disabled="true">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5"
                    stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="15 18 9 12 15 6" />
                </svg>
                Prev
            </span>
        <?php endif; ?>

        <?php foreach (pagination_range($page, $totalPages) as $pNum): ?>
            <?php if ($pNum === '...'): ?>
                <span class="au-page-ellipsis" aria-hidden="true">&hellip;</span>
            <?php elseif ($pNum === $page): ?>
                <span class="au-page-num active" aria-current="page"><?= $pNum ?></span>
            <?php else: ?>
                <a href="?page=<?= $pNum ?>&q=<?= urlencode($search ?? '') ?>&type=<?= urlencode($type ?? '') ?>&event_id=<?= urlencode($eventId ?? '') ?>" class="au-page-num"><?= $pNum ?></a>
            <?php endif; ?>
        <?php endforeach; ?>

        <?php if ($page < $totalPages): ?>
            <a href="?page=<?= $page + 1 ?>&q=<?= urlencode($search ?? '') ?>&type=<?= urlencode($type ?? '') ?>&event_id=<?= urlencode($eventId ?? '') ?>" class="au-page-btn">
                Next
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5"
                    stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="9 18 15 12 9 6" />
                </svg>
            </a>
        <?php else: ?>
            <span class="au-page-btn disabled" aria-disabled="true">
                Next
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5"
                    stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="9 18 15 12 9 6" />
                </svg>
            </span>
        <?php endif; ?>
    </nav>
    <?php endif; ?>
</section>
@endsection
