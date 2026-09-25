<?php

/**
 * Team Support Desk Page
 *
 * Workstation task view for guest support & help desk staff.
 *
 * @package Evently
 * @subpackage Views/Teams
 * @author Your Name <Celio Natti>
 * @license MIT License
 * @version 1.2.0
 */

$eventTime = !empty($event['date']) ? strtotime($event['date']) : null;
$assignments = $assignments ?? [];
?>

@extends('layouts/team')

@section('title')
Guest & Support Desk - <?= e($event['name'] ?? 'Evently') ?>
@endsection

@section('content')
<div class="support-desk-page-wrapper">
    <!-- Active Event Context Banner -->
    <div class="support-event-hero">
        <div class="support-event-info">
            <div class="support-badge-group">
                <span class="panel-badge support-panel-badge">🎧 Workstation — Customer Support</span>
                <span class="support-active-tag">Active Workspace</span>
            </div>
            <h1 class="support-event-title"><?= e($event['name'] ?? 'Event Workspace') ?></h1>
            <p class="lede support-event-meta">
                <?php if ($eventTime): ?>
                    <span>📅 <?= date('D, M d, Y • g:i A', $eventTime) ?></span>
                    <span class="meta-dot">•</span>
                <?php endif; ?>
                <span>📍 <?= e($event['location'] ?? 'Venue') ?></span>
            </p>
        </div>

        <div style="display:flex; align-items:center; gap:0.75rem; flex-wrap:wrap;">
            <button type="button" id="btn-open-box-office-support" class="checkout-btn" style="background:linear-gradient(135deg, #10b981, #059669); color:#fff; font-weight:800; padding:0.65rem 1.25rem; border-radius:12px; display:inline-flex; align-items:center; gap:0.4rem; border:none; cursor:pointer; box-shadow:0 6px 18px rgba(16,185,129,0.35);">
                <span>🎟️</span> Issue Gate Ticket
            </button>
            <?php if (count($assignments) > 1): ?>
                <div class="support-event-switcher-card">
                    <span class="switcher-label">Switch Event Workspace</span>
                    <form method="POST" action="{{ route('teams/switch-event') }}" class="support-switch-form">
                        <?= csrf_field() ?>
                        <select name="event_id" onchange="this.form.submit()" class="support-switch-select">
                            <?php foreach ($assignments as $asg): ?>
                                <option value="<?= $asg['event_id'] ?>" <?= $asg['event_id'] == $event['id'] ? 'selected' : '' ?>>
                                    <?= e($asg['event_name']) ?> (<?= e($asg['role_name']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Quick Stats Grid -->
    <?php if (!empty($stats)): ?>
        <section class="support-stats-grid">
            <article class="summary-card">
                <span class="card-label">Total Issued Passes</span>
                <h3 class="card-value"><?= number_format($stats['total_tickets']) ?></h3>
                <p class="card-meta">Paid &amp; verified tickets</p>
            </article>
            <article class="summary-card stat-checked-in">
                <span class="card-label">Checked In</span>
                <h3 class="card-value"><?= number_format($stats['checked_in']) ?></h3>
                <p class="card-meta">Arrived at venue</p>
            </article>
            <article class="summary-card stat-remaining">
                <span class="card-label">Remaining Expected</span>
                <h3 class="card-value"><?= number_format($stats['remaining']) ?></h3>
                <p class="card-meta">Not checked in yet</p>
            </article>
            <article class="summary-card stat-rate">
                <span class="card-label">Attendance Rate</span>
                <h3 class="card-value"><?= number_format($stats['percentage'], 1) ?>%</h3>
                <p class="card-meta">Of total capacity</p>
            </article>
        </section>
    <?php endif; ?>

    <!-- Cross-Event Match Notification (if attendee has ticket for another assigned event) -->
    <?php if (!empty($other_match)): ?>
        <div class="support-cross-event-alert">
            <span class="cross-alert-icon">💡</span>
            <div class="cross-alert-text">
                <strong>Ticket Found in Another Assigned Event:</strong>
                <span>Pass <code><?= e($other_match['ticket_code']) ?></code> (<?= e($other_match['attendee_name']) ?>) is registered for <strong><?= e($other_match['event_name']) ?></strong>.</span>
            </div>
            <form method="POST" action="{{ route('teams/switch-event') }}" style="margin: 0; flex-shrink: 0;">
                <?= csrf_field() ?>
                <input type="hidden" name="event_id" value="<?= $other_match['event_id'] ?>">
                <button type="submit" class="admin-action-btn primary" style="padding: 0.45rem 0.95rem; font-size: 0.85rem; border-radius: 8px;">
                    Switch to <?= e($other_match['event_name']) ?> →
                </button>
            </form>
        </div>
    <?php endif; ?>

    <!-- Search Toolbar -->
    <section class="form-card support-search-card">
        <form method="GET" action="{{ route('teams/support-desk') }}" class="support-search-form">
            <div class="support-search-input-wrap">
                <input type="text" name="q" value="<?= e($query ?? '') ?>" placeholder="Search attendee by name, email, or ticket code (e.g. EV-A1B2 or Alex)..."
                    class="support-search-input">
            </div>
            <button type="submit" class="admin-action-btn primary support-search-btn">
                🔍 Search Attendee
            </button>
            <?php if (!empty($query)): ?>
                <a href="{{ route('teams/support-desk') }}" class="admin-action-btn support-clear-btn">Clear</a>
            <?php endif; ?>
        </form>
    </section>

    <!-- Attendees / Tickets Section -->
    <section class="form-card support-content-card">
        <div class="support-table-header">
            <div>
                <h2 style="margin: 0; font-size: 1.25rem;">Attendee Ticket Passes</h2>
                <p class="support-header-sub">
                    <?php if (!empty($query)): ?>
                        Showing results matching "<strong><?= e($query) ?></strong>" (<?= count($tickets ?? []) ?> found)
                    <?php else: ?>
                        Displaying latest <?= count($tickets ?? []) ?> purchased admission passes for this event
                    <?php endif; ?>
                </p>
            </div>
        </div>

        <?php if (empty($tickets)): ?>
            <div class="empty-state support-empty-state">
                <span class="empty-icon">🎟️</span>
                <h3>No Attendee Passes Found</h3>
                <p>
                    <?php if (!empty($query)): ?>
                        No tickets match your search query "<strong><?= e($query) ?></strong>" in this event. Double check spelling or ticket code.
                    <?php else: ?>
                        No tickets have been issued or purchased for this event yet.
                    <?php endif; ?>
                </p>
                <?php if (!empty($query)): ?>
                    <a href="{{ route('teams/support-desk') }}" class="admin-action-btn" style="display: inline-block; padding: 0.5rem 1.25rem; border-radius: 10px; text-decoration: none;">
                        View All Attendees
                    </a>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <!-- Desktop Table View (>= 769px) -->
            <div class="support-desktop-table-wrap">
                <table class="au-table" style="width: 100%;">
                    <thead>
                        <tr>
                            <th style="width: 28%;">Attendee</th>
                            <th style="width: 22%;">Ticket Code &amp; Tier</th>
                            <th style="width: 15%;">Payment Status</th>
                            <th style="width: 18%;">Check-in Status</th>
                            <th class="au-th-actions" style="width: 17%; text-align: right;">Support Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tickets as $t):
                            $isCheckedIn = ($t['checked_in'] == 1);
                        ?>
                        <tr>
                            <td>
                                <div style="display: flex; align-items: center; gap: 0.65rem;">
                                    <div class="attendee-avatar">
                                        <?= strtoupper(substr($t['attendee_name'] ?? 'A', 0, 1)) ?>
                                    </div>
                                    <div>
                                        <strong style="color: var(--text); font-size: 0.95rem;"><?= e($t['attendee_name']) ?></strong>
                                        <div style="font-size: 0.8rem; color: var(--text-muted);"><?= e($t['attendee_email']) ?></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <code class="ticket-code-badge"><?= e($t['id']) ?></code>
                                <div style="font-size: 0.78rem; color: var(--text-muted); margin-top: 0.25rem;">
                                    <?= e($t['ticket_type_name'] ?? 'General Admission') ?>
                                    <?php if (!empty($t['price'])): ?>
                                        <span style="opacity: 0.7;">(<?= format_price($t['price']) ?>)</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <?php if (strcasecmp($t['status'], 'Paid') === 0): ?>
                                    <span class="au-status-badge au-status--active">Paid</span>
                                <?php elseif (strcasecmp($t['status'], 'Comped') === 0 || strcasecmp($t['status'], 'Free') === 0): ?>
                                    <span class="au-status-badge au-role--host"><?= e(ucfirst($t['status'])) ?></span>
                                <?php else: ?>
                                    <span class="au-status-badge au-status--suspended"><?= e(ucfirst($t['status'])) ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($isCheckedIn): ?>
                                    <span class="au-status-badge au-status--active">✓ Checked In</span>
                                    <div style="font-size: 0.72rem; color: var(--text-muted); margin-top: 0.25rem;">
                                        <?= date('M d, H:i', strtotime($t['checked_in_at'])) ?>
                                    </div>
                                <?php else: ?>
                                    <span class="au-status-badge au-status--unverified">Not Arrived</span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: right;">
                                <form method="POST" action="{{ route('teams/support-action') }}" style="display: inline; margin: 0;">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="action" value="override_checkin" />
                                    <input type="hidden" name="ticket_id" value="<?= $t['id'] ?>" />
                                    <input type="hidden" name="check_in" value="<?= $isCheckedIn ? '0' : '1' ?>" />
                                    <button type="submit" class="admin-action-btn <?= $isCheckedIn ? 'warning' : 'primary' ?>" style="padding: 0.4rem 0.85rem; font-size: 0.82rem; border-radius: 8px;">
                                        <?= $isCheckedIn ? 'Reset Status' : 'Override Entry' ?>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Mobile & Tablet Cards View (<= 768px) -->
            <div class="support-mobile-cards-list">
                <?php foreach ($tickets as $t):
                    $isCheckedIn = ($t['checked_in'] == 1);
                ?>
                <article class="support-mobile-card <?= $isCheckedIn ? 'card-checked-in' : '' ?>">
                    <div class="sm-card-header">
                        <div style="display: flex; align-items: center; gap: 0.65rem;">
                            <div class="attendee-avatar">
                                <?= strtoupper(substr($t['attendee_name'] ?? 'A', 0, 1)) ?>
                            </div>
                            <div>
                                <h4 class="sm-attendee-name"><?= e($t['attendee_name']) ?></h4>
                                <span class="sm-attendee-email"><?= e($t['attendee_email']) ?></span>
                            </div>
                        </div>
                        <div>
                            <?php if ($isCheckedIn): ?>
                                <span class="au-status-badge au-status--active">Checked In</span>
                            <?php else: ?>
                                <span class="au-status-badge au-status--unverified">Not Arrived</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="sm-card-details">
                        <div class="sm-detail-item">
                            <span class="sm-detail-label">Pass Code</span>
                            <code class="ticket-code-badge"><?= e($t['id']) ?></code>
                        </div>
                        <div class="sm-detail-item">
                            <span class="sm-detail-label">Ticket Tier</span>
                            <span style="font-size: 0.88rem; font-weight: 600;"><?= e($t['ticket_type_name'] ?? 'General Admission') ?></span>
                        </div>
                        <div class="sm-detail-item">
                            <span class="sm-detail-label">Payment</span>
                            <span style="font-weight: 700; font-size: 0.85rem; color: <?= strcasecmp($t['status'], 'Paid') === 0 ? '#10b981' : 'inherit' ?>;">
                                <?= e(ucfirst($t['status'])) ?>
                            </span>
                        </div>
                        <div class="sm-detail-item">
                            <span class="sm-detail-label">Purchase Date</span>
                            <span style="font-size: 0.82rem; color: var(--text-muted);"><?= date('M d, Y', strtotime($t['purchase_date'])) ?></span>
                        </div>
                    </div>

                    <div class="sm-card-action">
                        <form method="POST" action="{{ route('teams/support-action') }}" style="width: 100%; margin: 0;">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="override_checkin" />
                            <input type="hidden" name="ticket_id" value="<?= $t['id'] ?>" />
                            <input type="hidden" name="check_in" value="<?= $isCheckedIn ? '0' : '1' ?>" />
                            <button type="submit" class="admin-action-btn <?= $isCheckedIn ? 'warning' : 'primary' ?> sm-submit-btn">
                                <?= $isCheckedIn ? '↩️ Reset Check-In Status' : '🎟️ Override Check-In &amp; Admit' ?>
                            </button>
                        </form>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</div>

<style>
/* ═══════════════════════════════════════════════════════════════════
   Support Desk Styles & Responsiveness
   ═══════════════════════════════════════════════════════════════════ */
.support-desk-page-wrapper {
    width: 100%;
    max-width: 1200px;
    margin: 0 auto;
}

.support-event-hero {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1.25rem;
    margin-bottom: 1.5rem;
}

.support-badge-group {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    margin-bottom: 0.4rem;
    flex-wrap: wrap;
}

.support-panel-badge {
    background: rgba(59, 130, 246, 0.12);
    color: #3b82f6;
    border: 1px solid rgba(59, 130, 246, 0.25);
    font-weight: 700;
    font-size: 0.8rem;
}

.support-active-tag {
    font-size: 0.8rem;
    color: var(--text-muted);
    font-weight: 600;
}

.support-event-title {
    margin: 0;
    font-size: clamp(1.35rem, 3vw, 1.85rem);
    font-weight: 800;
}

.support-event-meta {
    margin: 0.35rem 0 0;
    font-size: 0.92rem;
    color: var(--text-muted);
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.meta-dot {
    opacity: 0.5;
}

.support-event-switcher-card {
    background: rgb(var(--card));
    border: 1px solid rgba(var(--muted), 0.15);
    border-radius: 14px;
    padding: 0.65rem 1rem;
    display: flex;
    flex-direction: column;
    gap: 0.35rem;
}

.switcher-label {
    font-size: 0.72rem;
    text-transform: uppercase;
    font-weight: 700;
    color: rgb(var(--muted));
    letter-spacing: 0.4px;
}

.support-switch-select {
    padding: 0.4rem 0.75rem;
    border-radius: 8px;
    border: 1px solid rgba(var(--muted), 0.25);
    background: rgb(var(--bg));
    color: rgb(var(--text));
    font-size: 0.88rem;
    font-weight: 600;
    cursor: pointer;
}

/* Stats Grid */
.support-stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.support-stats-grid .summary-card {
    border-radius: 16px;
    padding: 1.15rem;
    background: rgb(var(--card));
    border: 1px solid rgba(var(--muted), 0.12);
}

.stat-checked-in {
    border-color: rgba(16, 185, 129, 0.25) !important;
    background: rgba(16, 185, 129, 0.03) !important;
}
.stat-checked-in .card-value { color: #10b981; }

.stat-remaining {
    border-color: rgba(245, 158, 11, 0.25) !important;
    background: rgba(245, 158, 11, 0.03) !important;
}
.stat-remaining .card-value { color: #d97706; }

.stat-rate .card-value { color: rgb(var(--brand)); }

/* Cross-Event Alert */
.support-cross-event-alert {
    background: rgba(59, 130, 246, 0.08);
    border: 1px solid rgba(59, 130, 246, 0.25);
    border-radius: 14px;
    padding: 0.85rem 1.15rem;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    flex-wrap: wrap;
}

.cross-alert-icon {
    font-size: 1.5rem;
}

.cross-alert-text {
    flex: 1;
    font-size: 0.88rem;
    color: rgb(var(--text));
}

.cross-alert-text code {
    background: rgba(59, 130, 246, 0.15);
    padding: 0.15rem 0.4rem;
    border-radius: 6px;
    font-weight: 700;
    color: #2563eb;
}

/* Search Card */
.support-search-card {
    padding: 1.15rem;
    margin-bottom: 1.5rem;
    background: rgb(var(--card));
    border: 1px solid rgba(var(--muted), 0.14);
    border-radius: 18px;
}
    border-radius: 18px;
    background: var(--surface);
    border: 1px solid rgba(var(--muted), 0.12);
}

.support-search-form {
    display: flex;
    gap: 0.75rem;
    flex-wrap: wrap;
}

.support-search-input-wrap {
    flex: 1;
    min-width: 260px;
}

.support-search-input {
    width: 100%;
    box-sizing: border-box;
    padding: 0.7rem 1.1rem;
    border-radius: 12px;
    border: 1px solid rgba(var(--muted), 0.2);
    background: var(--bg);
    color: var(--text);
    font-size: 0.95rem;
}

.support-search-btn {
    padding: 0.7rem 1.5rem;
    border-radius: 12px;
    font-weight: 700;
    cursor: pointer;
}

.support-clear-btn {
    padding: 0.7rem 1rem;
    border-radius: 12px;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
}

/* Content Section */
.support-content-card {
    padding: 1.5rem;
    border-radius: 20px;
    background: var(--surface);
    border: 1px solid rgba(var(--muted), 0.12);
}

.support-table-header {
    margin-bottom: 1.25rem;
    padding-bottom: 0.85rem;
    border-bottom: 1px solid rgba(var(--muted), 0.1);
}

.support-header-sub {
    margin: 0.25rem 0 0;
    font-size: 0.85rem;
    color: var(--text-muted);
}

.support-empty-state {
    text-align: center;
    padding: 3rem 1.5rem;
    background: rgba(var(--muted), 0.03);
    border-radius: 16px;
    border: 1px dashed rgba(var(--muted), 0.2);
}

.support-empty-state .empty-icon {
    font-size: 2.8rem;
    display: block;
    margin-bottom: 0.75rem;
}

.attendee-avatar {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    background: linear-gradient(135deg, rgba(var(--brand), 0.2), rgba(var(--brand), 0.05));
    border: 1px solid rgba(var(--brand), 0.25);
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 800;
    font-size: 0.88rem;
    color: rgb(var(--brand));
    flex-shrink: 0;
}

.ticket-code-badge {
    background: rgba(var(--muted), 0.1);
    border: 1px solid rgba(var(--muted), 0.15);
    padding: 0.25rem 0.55rem;
    border-radius: 8px;
    font-weight: 700;
    font-family: monospace;
    font-size: 0.85rem;
    letter-spacing: 0.5px;
}

.support-desktop-table-wrap {
    width: 100%;
    overflow-x: auto;
}

.support-mobile-cards-list {
    display: none;
    flex-direction: column;
    gap: 0.85rem;
}

.support-mobile-card {
    background: var(--bg);
    border: 1px solid rgba(var(--muted), 0.15);
    border-radius: 16px;
    padding: 1.15rem;
    display: flex;
    flex-direction: column;
    gap: 0.85rem;
}

.support-mobile-card.card-checked-in {
    border-left: 4px solid #10b981;
}

.sm-card-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 0.5rem;
}

.sm-attendee-name {
    margin: 0;
    font-size: 0.98rem;
    font-weight: 700;
}

.sm-attendee-email {
    font-size: 0.8rem;
    color: var(--text-muted);
}

.sm-card-details {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.65rem;
    padding: 0.75rem;
    background: rgba(var(--muted), 0.04);
    border-radius: 12px;
}

.sm-detail-item {
    display: flex;
    flex-direction: column;
    gap: 0.15rem;
}

.sm-detail-label {
    font-size: 0.7rem;
    font-weight: 700;
    text-transform: uppercase;
    color: var(--text-muted);
    letter-spacing: 0.4px;
}

.sm-submit-btn {
    width: 100%;
    height: 42px;
    justify-content: center;
    border-radius: 10px;
    font-size: 0.88rem;
    font-weight: 700;
}

/* ═══════════════════════════════════════════════════════════════════
   Tablet Responsive (769px – 1024px)
   ═══════════════════════════════════════════════════════════════════ */
@media (min-width: 769px) and (max-width: 1024px) {
    .support-content-card {
        padding: 1rem;
    }
    .support-desktop-table-wrap {
        -webkit-overflow-scrolling: touch;
    }
    .support-desktop-table-wrap table {
        min-width: 680px;
    }
    .support-stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    .support-event-hero {
        gap: 1rem;
    }
    .support-event-title {
        font-size: 1.4rem;
    }
}

/* ═══════════════════════════════════════════════════════════════════
   Mobile Responsive (≤ 768px)
   ═══════════════════════════════════════════════════════════════════ */
@media (max-width: 768px) {
    .support-desktop-table-wrap {
        display: none !important;
    }
    .support-mobile-cards-list {
        display: flex !important;
    }
    .support-event-hero {
        flex-direction: column;
        align-items: stretch;
    }
    .support-event-switcher-card {
        width: 100%;
        box-sizing: border-box;
    }
    .support-switch-select {
        width: 100%;
        font-size: 0.92rem;
        padding: 0.5rem 0.75rem;
    }
    .support-search-form {
        flex-direction: column;
    }
    .support-search-input-wrap {
        min-width: unset;
        width: 100%;
    }
    .support-search-input {
        font-size: 16px; /* prevents iOS zoom */
        padding: 0.75rem 1rem;
    }
    .support-search-btn,
    .support-clear-btn {
        width: 100%;
        justify-content: center;
        text-align: center;
        box-sizing: border-box;
        height: 44px;
    }
    .support-content-card {
        padding: 1rem;
        border-radius: 16px;
    }
    .support-table-header h2 {
        font-size: 1.1rem;
    }
    .support-stats-grid {
        grid-template-columns: 1fr 1fr;
        gap: 0.65rem;
    }
    .support-stats-grid .summary-card {
        padding: 0.85rem;
    }
    .support-stats-grid .card-value {
        font-size: 1.25rem;
    }
    .support-cross-event-alert {
        flex-direction: column;
        align-items: flex-start;
        padding: 0.75rem 1rem;
        font-size: 0.85rem;
    }
    .support-cross-event-alert .admin-action-btn {
        width: 100%;
        text-align: center;
        justify-content: center;
        box-sizing: border-box;
    }
    /* Mobile card refinements */
    .support-mobile-card {
        padding: 1rem;
        gap: 0.75rem;
        border-radius: 14px;
    }
    .sm-card-header {
        flex-wrap: wrap;
        gap: 0.5rem;
    }
    .sm-attendee-name {
        font-size: 0.95rem;
        word-break: break-word;
    }
    .sm-attendee-email {
        font-size: 0.78rem;
        word-break: break-all;
    }
    .sm-card-details {
        gap: 0.5rem;
        padding: 0.65rem;
    }
    .ticket-code-badge {
        font-size: 0.78rem;
        padding: 0.2rem 0.45rem;
    }
    .sm-submit-btn {
        height: 44px;
        font-size: 0.85rem;
    }
    .support-search-card {
        padding: 0.85rem;
        border-radius: 14px;
    }
    .support-empty-state {
        padding: 2rem 1rem;
    }
    .support-empty-state .empty-icon {
        font-size: 2.2rem;
    }
}

/* ═══════════════════════════════════════════════════════════════════
   Small Phone (≤ 400px)
   ═══════════════════════════════════════════════════════════════════ */
@media (max-width: 400px) {
    .support-desk-page-wrapper {
        padding: 0 0.25rem;
    }
    .support-content-card {
        padding: 0.75rem;
        border-radius: 12px;
    }
    .support-stats-grid {
        grid-template-columns: 1fr;
        gap: 0.5rem;
    }
    .support-stats-grid .summary-card {
        padding: 0.75rem;
        border-radius: 12px;
    }
    .support-mobile-card {
        padding: 0.8rem;
        border-radius: 12px;
        gap: 0.6rem;
    }
    .sm-card-header {
        flex-direction: column;
        gap: 0.4rem;
    }
    .sm-card-details {
        grid-template-columns: 1fr;
        gap: 0.4rem;
        padding: 0.55rem;
    }
    .sm-attendee-name {
        font-size: 0.9rem;
    }
    .attendee-avatar {
        width: 32px;
        height: 32px;
        font-size: 0.8rem;
        border-radius: 8px;
    }
    .support-event-title {
        font-size: 1.15rem;
    }
    .support-event-meta {
        font-size: 0.8rem;
    }
    .sm-submit-btn {
        height: 42px;
        font-size: 0.82rem;
    }
    .support-search-input {
        padding: 0.65rem 0.85rem;
    }
}
</style>

<!-- ════════════════════════════════════════════════════════════════
     GATE BOX OFFICE & TICKET SALES MODAL (SUPPORT DESK)
     ════════════════════════════════════════════════════════════════ -->
<div id="modal-box-office-support" class="team-modal-backdrop">
    <div class="team-modal-dialog">
        <div class="team-modal-header">
            <div>
                <h3 class="team-modal-title">
                    <span>🎟️</span> Issue Gate / Support Ticket
                </h3>
                <p class="team-modal-subtitle">Instant ticket issuance for attendee assistance at gate</p>
            </div>
            <button type="button" id="btn-close-box-office-support" class="team-modal-close-btn" aria-label="Close modal">✕</button>
        </div>

        <form id="form-gate-sale-support">
            <?= csrf_field() ?>
            
            <div style="margin-bottom:1rem;">
                <label class="team-modal-label">Ticket Tier *</label>
                <select name="ticket_type_id" required class="team-modal-select">
                    <?php foreach ($all_ticket_types ?? [] as $tt): 
                        $cap = (int)($tt['capacity'] ?? 0);
                        $sold = (int)($tt['sold_count'] ?? 0);
                        $avail = $cap > 0 ? max(0, $cap - $sold) : 'Unlimited';
                    ?>
                        <option value="<?= $tt['id'] ?>">
                            <?= e($tt['name']) ?> — <?= (float)$tt['price'] > 0 ? format_price($tt['price']) : 'Free' ?> (Avail: <?= $avail ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:0.75rem; margin-bottom:1rem;">
                <div>
                    <label class="team-modal-label">Attendee Name *</label>
                    <input type="text" name="attendee_name" required placeholder="Jane Doe" class="team-modal-input" />
                </div>
                <div>
                    <label class="team-modal-label">Attendee Email</label>
                    <input type="email" name="attendee_email" placeholder="jane@example.com" class="team-modal-input" />
                </div>
            </div>

            <div style="display:grid; grid-template-columns:1fr 1.3fr; gap:0.75rem; margin-bottom:1rem;">
                <div>
                    <label class="team-modal-label">Quantity</label>
                    <input type="number" name="quantity" value="1" min="1" max="10" required class="team-modal-input" />
                </div>
                <div>
                    <label class="team-modal-label">Payment Method</label>
                    <select name="payment_method" class="team-modal-select">
                        <option value="cash">💵 Cash at Gate</option>
                        <option value="pos">💳 POS Card Terminal</option>
                        <option value="transfer">📱 Bank Transfer / USSD</option>
                        <option value="comp">🎁 Complimentary / VIP Comp</option>
                    </select>
                </div>
            </div>

            <div class="team-modal-notice">
                <input type="checkbox" name="mark_checked_in" id="chk-support-gate-in" value="1" checked style="width:18px; height:18px; accent-color:rgb(var(--brand)); cursor:pointer;" />
                <label for="chk-support-gate-in" style="font-size:0.85rem; font-weight:700; color:rgb(var(--text)); cursor:pointer; margin:0;">
                    Check attendee in immediately
                </label>
            </div>

            <div id="support-sale-status" style="font-size:0.85rem; margin-bottom:0.75rem; display:none;"></div>

            <div style="display:flex; justify-content:flex-end; gap:0.6rem; margin-top:1.25rem;">
                <button type="button" id="btn-cancel-box-office-support" class="checkout-btn secondary" style="padding:0.75rem 1.25rem; border-radius:12px;">Cancel</button>
                <button type="submit" id="btn-submit-support-sale" class="checkout-btn" style="padding:0.75rem 1.5rem; border-radius:12px; background:#10b981; color:#fff; font-weight:800; border:none; cursor:pointer;">
                    ✓ Issue Ticket
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const modalSupp = document.getElementById('modal-box-office-support');
    const btnOpenSupp = document.getElementById('btn-open-box-office-support');
    const btnCloseSupp = document.getElementById('btn-close-box-office-support');
    const btnCancelSupp = document.getElementById('btn-cancel-box-office-support');
    const formSupp = document.getElementById('form-gate-sale-support');
    const statusSupp = document.getElementById('support-sale-status');
    const btnSubSupp = document.getElementById('btn-submit-support-sale');

    if (btnOpenSupp && modalSupp) {
        btnOpenSupp.addEventListener('click', () => { modalSupp.style.display = 'flex'; });
        btnCloseSupp.addEventListener('click', () => { modalSupp.style.display = 'none'; });
        btnCancelSupp.addEventListener('click', () => { modalSupp.style.display = 'none'; });
        modalSupp.addEventListener('click', (e) => {
            if (e.target === modalSupp) modalSupp.style.display = 'none';
        });
    }

    if (formSupp) {
        formSupp.addEventListener('submit', function(e) {
            e.preventDefault();
            btnSubSupp.disabled = true;
            btnSubSupp.textContent = 'Issuing...';
            statusSupp.style.display = 'none';

            const csrfToken = "<?= csrf_token() ?>";

            fetch('<?= route('/teams/issue-gate-ticket') ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify(payload)
            })
            .then(r => r.json())
            .then(data => {
                btnSubSupp.disabled = false;
                btnSubSupp.textContent = '✓ Issue Ticket';
                statusSupp.style.display = 'block';

                if (data.success) {
                    statusSupp.innerHTML = `<span style="color:#10b981; font-weight:800;">✓ ${data.message}</span>`;
                    setTimeout(() => {
                        modalSupp.style.display = 'none';
                        window.location.reload();
                    }, 1500);
                } else {
                    statusSupp.innerHTML = `<span style="color:#ef4444; font-weight:800;">✕ ${data.message}</span>`;
                }
            })
            .catch(() => {
                btnSubSupp.disabled = false;
                btnSubSupp.textContent = '✓ Issue Ticket';
                statusSupp.style.display = 'block';
                statusSupp.innerHTML = `<span style="color:#ef4444; font-weight:800;">✕ Connection error. Please try again.</span>`;
            });
        });
    }
});
</script>
@endsection