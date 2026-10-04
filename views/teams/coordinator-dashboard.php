<?php

/**
 * Team Event Organiser (Box Office/Finance) Dashboard Page
 *
 * Workstation task view for Event Organisers to manage:
 * - Door/Gate ticket purchases and complimentary ticket issuance
 * - Staff check-in and deployment status
 * - Live check-in rates and gate capacity
 *
 * @package Eventlyy
 * @subpackage Views/Teams
 * @author Celio Natti
 * @version 3.0.0
 */

$eventTime = !empty($event['date']) ? strtotime($event['date']) : null;
$staffRoster = $staff_roster ?? [];
$allTicketTypes = $all_ticket_types ?? [];
$currencySymbol = get_currency_symbol();
?>

@extends('layouts/team')

@section('title')
Event Organiser (Box Office) — <?= e($event['name'] ?? 'Eventlyy') ?>
@endsection

@section('style')
<style>
/* ═════════════════════════════════════════════════════════════
   Coordinator Dashboard Component System (.cd-*)
   Scoped, modern, accessible, responsive across viewports.
   ═════════════════════════════════════════════════════════════ */

.cd-wrapper {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
    padding-bottom: 2rem;
}

/* ─── Hero Header ──────────────────────────────────────────── */
.cd-hero {
    background: radial-gradient(circle at 10% 20%, rgba(99, 102, 241, 0.12) 0%, transparent 45%),
                linear-gradient(135deg, rgba(var(--card), 0.95), rgba(var(--card), 0.85));
    border: 1px solid rgba(99, 102, 241, 0.22);
    border-radius: 22px;
    padding: clamp(1.25rem, 3vw, 1.85rem) clamp(1.25rem, 3vw, 2rem);
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1.25rem;
    flex-wrap: wrap;
    box-shadow: 0 10px 30px -10px rgba(99, 102, 241, 0.12);
}

.cd-hero-text {
    min-width: 0;
    flex: 1;
}

.cd-badge-wrap {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 0.45rem;
    flex-wrap: wrap;
}

.cd-pill-desk {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    padding: 0.25rem 0.75rem;
    border-radius: 999px;
    font-size: 0.76rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    background: rgba(99, 102, 241, 0.14);
    color: #4f46e5;
    border: 1px solid rgba(99, 102, 241, 0.28);
}

.cd-pill-live {
    font-size: 0.72rem;
    font-weight: 800;
    padding: 0.2rem 0.6rem;
    border-radius: 999px;
    background: rgba(16, 185, 129, 0.15);
    color: #10b981;
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
}

.cd-pill-live::before {
    content: '';
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: #10b981;
    display: inline-block;
}

.cd-title {
    font-size: clamp(1.35rem, 3vw, 1.85rem);
    font-weight: 900;
    color: rgb(var(--text));
    margin: 0 0 0.35rem;
    line-height: 1.2;
    letter-spacing: -0.02em;
}

.cd-meta {
    font-size: 0.86rem;
    color: rgb(var(--muted));
    margin: 0;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.cd-btn-box-office {
    background: linear-gradient(135deg, #6366f1, #4f46e5);
    color: #ffffff !important;
    font-weight: 800;
    font-size: 0.92rem;
    padding: 0.75rem 1.45rem;
    border-radius: 14px;
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    border: none;
    cursor: pointer;
    box-shadow: 0 8px 22px rgba(99, 102, 241, 0.35);
    transition: all var(--duration-fast, 0.15s) ease-out;
}

.cd-btn-box-office:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 28px rgba(99, 102, 241, 0.45);
    filter: brightness(1.05);
}

/* ─── 4-Card Stats Grid ────────────────────────────────────── */
.cd-stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1.15rem;
}

.cd-stat-card {
    background: rgb(var(--card));
    border: 1px solid rgba(var(--muted), 0.14);
    border-radius: 18px;
    padding: 1.25rem 1.35rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    box-shadow: 0 4px 18px rgba(0, 0, 0, 0.03);
    transition: transform var(--duration-fast, 0.15s) ease-out, border-color var(--duration-fast, 0.15s) ease-out;
}

.cd-stat-card:hover {
    transform: translateY(-2px);
    border-color: rgba(var(--brand), 0.3);
}

.cd-stat-icon {
    width: 48px;
    height: 48px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.cd-stat-icon--brand {
    background: rgba(var(--brand), 0.12);
    color: rgb(var(--brand));
}

.cd-stat-icon--blue {
    background: rgba(14, 165, 233, 0.12);
    color: #0ea5e9;
}

.cd-stat-icon--amber {
    background: rgba(245, 158, 11, 0.12);
    color: #f59e0b;
}

.cd-stat-icon--emerald {
    background: rgba(16, 185, 129, 0.12);
    color: #10b981;
}

.cd-stat-val {
    font-size: clamp(1.35rem, 2.5vw, 1.75rem);
    font-weight: 900;
    line-height: 1.15;
    letter-spacing: -0.02em;
    color: rgb(var(--text));
    display: block;
}

.cd-stat-label {
    font-size: 0.78rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: rgb(var(--muted));
    margin-top: 0.2rem;
    display: block;
}

/* ─── Card Container ───────────────────────────────────────── */
.cd-card {
    background: rgb(var(--card));
    border: 1px solid rgba(var(--muted), 0.14);
    border-radius: 22px;
    padding: clamp(1.25rem, 3vw, 1.75rem);
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
}

.cd-card-head {
    margin-bottom: 1.25rem;
    padding-bottom: 0.85rem;
    border-bottom: 1px solid rgba(var(--muted), 0.12);
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
    flex-wrap: wrap;
}

.cd-card-title {
    font-size: 1.15rem;
    font-weight: 800;
    color: rgb(var(--text));
    margin: 0 0 0.25rem;
    letter-spacing: -0.015em;
    display: flex;
    align-items: center;
    gap: 0.45rem;
}

.cd-card-sub {
    font-size: 0.84rem;
    color: rgb(var(--muted));
    margin: 0;
}

.cd-card-count {
    font-size: 0.8rem;
    font-weight: 800;
    color: rgb(var(--brand));
    background: rgba(var(--brand), 0.1);
    padding: 0.3rem 0.75rem;
    border-radius: 999px;
}

/* ─── Staff Roster Items ───────────────────────────────────── */
.cd-roster-list {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.cd-roster-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: rgb(var(--bg));
    border: 1px solid rgba(var(--muted), 0.14);
    border-radius: 16px;
    padding: 0.9rem 1.15rem;
    flex-wrap: wrap;
    gap: 0.85rem;
    transition: border-color var(--duration-fast, 0.15s) ease-out;
}

.cd-roster-item:hover {
    border-color: rgba(var(--brand), 0.3);
}

.cd-roster-staff {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    flex: 1;
    min-width: 0;
}

.cd-roster-avatar {
    width: 40px;
    height: 40px;
    border-radius: 12px;
    background: linear-gradient(135deg, #6366f1, #a855f7);
    color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 800;
    font-size: 0.95rem;
    flex-shrink: 0;
}

.cd-roster-name {
    font-size: 0.95rem;
    font-weight: 800;
    color: rgb(var(--text));
    line-height: 1.2;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.cd-roster-email {
    font-size: 0.78rem;
    color: rgb(var(--muted));
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.cd-roster-actions {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    flex-wrap: wrap;
}

.cd-role-badge {
    font-size: 0.74rem;
    font-weight: 800;
    background: rgba(99, 102, 241, 0.12);
    color: #4f46e5;
    border: 1px solid rgba(99, 102, 241, 0.25);
    padding: 0.25rem 0.65rem;
    border-radius: 8px;
    white-space: nowrap;
}

.cd-checkin-badge {
    font-size: 0.74rem;
    font-weight: 800;
    padding: 0.25rem 0.75rem;
    border-radius: 8px;
    white-space: nowrap;
}

.cd-btn-toggle {
    padding: 0.45rem 0.95rem;
    border-radius: 10px;
    font-size: 0.8rem;
    font-weight: 800;
    cursor: pointer;
    border: none;
    transition: all var(--duration-fast, 0.15s) ease-out;
    white-space: nowrap;
}

/* ─── Content Dual Column Layout ───────────────────────────── */
.cd-content-grid {
    display: grid;
    grid-template-columns: 1.15fr 0.85fr;
    gap: 1.5rem;
    align-items: start;
}

.cd-tier-list {
    display: flex;
    flex-direction: column;
    gap: 1.15rem;
}

.cd-tier-item {
    background: rgb(var(--bg));
    border: 1px solid rgba(var(--muted), 0.12);
    border-radius: 14px;
    padding: 1rem 1.15rem;
}

.cd-tier-head {
    display: flex;
    justify-content: space-between;
    margin-bottom: 0.45rem;
    font-size: 0.88rem;
    flex-wrap: wrap;
    gap: 0.35rem;
}

.cd-progress-wrap {
    width: 100%;
    height: 8px;
    background: rgba(var(--muted), 0.14);
    border-radius: 99px;
    overflow: hidden;
}

.cd-progress-fill {
    height: 100%;
    background: linear-gradient(90deg, rgb(var(--brand)), #10b981);
    border-radius: 99px;
    transition: width 0.4s ease;
}

.cd-activity-list {
    display: flex;
    flex-direction: column;
    gap: 0.65rem;
    max-height: 400px;
    overflow-y: auto;
}

.cd-activity-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem 0.95rem;
    border-radius: 12px;
    background: rgb(var(--bg));
    border: 1px solid rgba(var(--muted), 0.12);
    font-size: 0.85rem;
    gap: 0.5rem;
    flex-wrap: wrap;
}

/* ═════════════════════════════════════════════════════════════
   Responsive Breakpoints
   ═════════════════════════════════════════════════════════════ */

/* Tablet (max 1024px) */
@media (max-width: 1024px) {
    .cd-stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    .cd-content-grid {
        grid-template-columns: 1fr;
    }
}

/* Mobile (max 680px) */
@media (max-width: 680px) {
    .cd-hero {
        flex-direction: column;
        align-items: stretch;
        padding: 1.25rem 1.15rem;
    }

    .cd-btn-box-office {
        width: 100%;
        justify-content: center;
    }

    .cd-stats-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 0.75rem;
    }

    .cd-stat-card {
        padding: 1rem;
        flex-direction: column;
        align-items: flex-start;
        gap: 0.65rem;
    }

    .cd-stat-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
    }

    .cd-stat-val {
        font-size: 1.25rem;
    }

    .cd-stat-label {
        font-size: 0.72rem;
    }

    .cd-roster-item {
        flex-direction: column;
        align-items: stretch;
        padding: 1rem;
        gap: 0.75rem;
    }

    .cd-roster-actions {
        justify-content: space-between;
        width: 100%;
        padding-top: 0.5rem;
        border-top: 1px dashed rgba(var(--muted), 0.14);
    }

    .cd-card {
        padding: 1.25rem 1rem;
        border-radius: 18px;
    }
}

/* Very Small Mobile (< 400px) */
@media (max-width: 400px) {
    .cd-stats-grid {
        grid-template-columns: 1fr;
    }
}
</style>
@endsection

@section('content')
<div class="cd-wrapper">

    <!-- ═══ Hero Header ═══ -->
    <header class="cd-hero">
        <div class="cd-hero-text">
            <div class="cd-badge-wrap">
                <span class="cd-pill-desk">🎧 Workstation — Event Organiser (Box Office)</span>
                <span class="cd-pill-live">Box Office Active</span>
            </div>
            <h1 class="cd-title"><?= e($event['name'] ?? 'Event Organizer Desk') ?></h1>
            <p class="cd-meta">
                <?php if ($eventTime): ?>
                    <span>📅 <?= date('D, M d, Y • g:i A', $eventTime) ?></span>
                    <span>&bull;</span>
                <?php endif; ?>
                <span>📍 <?= e($event['location'] ?? 'Venue Box Office') ?></span>
                <span>&bull;</span>
                <span>Door Admission &amp; Roster Controls</span>
            </p>
        </div>

        <div>
            <button type="button" id="btn-open-box-office" class="cd-btn-box-office">
                <span>🎟️</span>
                <span>Sell / Issue Gate Ticket</span>
            </button>
        </div>
    </header>

    <!-- ═══ 4-Card Stats Grid ═══ -->
    <section class="cd-stats-grid">
        <div class="cd-stat-card">
            <div class="cd-stat-icon cd-stat-icon--brand">
                <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                </svg>
            </div>
            <div>
                <span class="cd-stat-val" style="color: rgb(var(--brand));"><?= number_format($stats['checked_in'] ?? 0) ?></span>
                <span class="cd-stat-label">Checked-In Attendees</span>
            </div>
        </div>

        <div class="cd-stat-card">
            <div class="cd-stat-icon cd-stat-icon--blue">
                <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                    <line x1="16" y1="2" x2="16" y2="6"></line>
                    <line x1="8" y1="2" x2="8" y2="6"></line>
                    <line x1="3" y1="10" x2="21" y2="10"></line>
                </svg>
            </div>
            <div>
                <span class="cd-stat-val"><?= number_format($stats['total_tickets'] ?? 0) ?></span>
                <span class="cd-stat-label">Total Issued Passes</span>
            </div>
        </div>

        <div class="cd-stat-card">
            <div class="cd-stat-icon cd-stat-icon--amber">
                <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="8" x2="12" y2="12"></line>
                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                </svg>
            </div>
            <div>
                <span class="cd-stat-val" style="color: #f59e0b;"><?= number_format($stats['remaining'] ?? 0) ?></span>
                <span class="cd-stat-label">Awaiting Arrival</span>
            </div>
        </div>

        <div class="cd-stat-card">
            <div class="cd-stat-icon cd-stat-icon--emerald">
                <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="20" x2="18" y2="10"></line>
                    <line x1="12" y1="20" x2="12" y2="4"></line>
                    <line x1="6" y1="20" x2="6" y2="14"></line>
                </svg>
            </div>
            <div>
                <span class="cd-stat-val" style="color: #10b981;"><?= $stats['percentage'] ?? 0 ?>%</span>
                <span class="cd-stat-label">Check-In Velocity</span>
            </div>
        </div>
    </section>

    <!-- ═══ Staff Check-In & Deployment Roster ═══ -->
    <section class="cd-card">
        <div class="cd-card-head">
            <div>
                <h2 class="cd-card-title">
                    <span>👥</span> Staff Check-In &amp; Deployment Roster
                </h2>
                <p class="cd-card-sub">Maintain and verify which team members are currently on duty and checked-in at the venue.</p>
            </div>
            <span class="cd-card-count">
                <?= count($staffRoster) ?> Assigned Personnel
            </span>
        </div>

        <?php if (empty($staffRoster)): ?>
            <div style="text-align:center; padding: 2.5rem 1rem; color: rgb(var(--muted));">
                <span style="font-size: 2rem; display: block; margin-bottom: 0.35rem;">👥</span>
                <p style="margin: 0; font-weight: 700;">No team members assigned to this event yet.</p>
            </div>
        <?php else: ?>
            <div class="cd-roster-list">
                <?php foreach ($staffRoster as $st): 
                    $isChecked = !empty($st['checked_in']);
                ?>
                    <div class="cd-roster-item" id="staff-row-<?= (int)$st['assignment_id'] ?>">
                        <div class="cd-roster-staff">
                            <div class="cd-roster-avatar">
                                <?= strtoupper(mb_substr($st['staff_name'], 0, 1)) ?>
                            </div>
                            <div style="min-width: 0; flex: 1;">
                                <strong class="cd-roster-name"><?= e($st['staff_name']) ?></strong>
                                <span class="cd-roster-email"><?= e($st['staff_email']) ?></span>
                            </div>
                        </div>

                        <div class="cd-roster-actions">
                            <span class="cd-role-badge">
                                <?= e($st['role_name']) ?>
                            </span>

                            <span class="cd-checkin-badge" id="badge-checkin-<?= (int)$st['assignment_id'] ?>" style="<?= $isChecked ? 'background:rgba(16,185,129,0.14); color:#059669; border:1px solid rgba(16,185,129,0.3);' : 'background:rgba(100,116,139,0.12); color:rgb(100,116,139);' ?>">
                                <?= $isChecked ? '● Checked In' . (!empty($st['checked_in_at']) ? ' (' . date('H:i', strtotime($st['checked_in_at'])) . ')' : '') : '○ Not Checked In' ?>
                            </span>

                            <button type="button" class="btn-toggle-checkin cd-btn-toggle" data-assignment-id="<?= (int)$st['assignment_id'] ?>" data-current="<?= $isChecked ? '1' : '0' ?>" style="<?= $isChecked ? 'background:rgba(239,68,68,0.12); color:#ef4444; border:1px solid rgba(239,68,68,0.25);' : 'background:#10b981; color:#fff;' ?>">
                                <?= $isChecked ? 'Check Out' : '✓ Check In Staff' ?>
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>

    <!-- ═══ Content Dual Column Layout ═══ -->
    <div class="cd-content-grid">
        
        <!-- Ticket Tier Capacity Breakdown -->
        <section class="cd-card">
            <div class="cd-card-head">
                <div>
                    <h2 class="cd-card-title">
                        <span>📊</span> Ticket Tier Attendance
                    </h2>
                    <p class="cd-card-sub">Live admission progress broken down by ticket tier.</p>
                </div>
            </div>

            <div class="cd-tier-list">
                <?php if (empty($ticket_types)): ?>
                    <p style="color: rgb(var(--muted)); text-align: center; padding: 1.5rem 0;">No ticket tier data available.</p>
                <?php else: ?>
                    <?php foreach ($ticket_types as $type):
                        $sold = (int)($type['tickets_sold'] ?? 0);
                        $checked = (int)($type['checked_in_count'] ?? 0);
                        $pct = $sold > 0 ? round(($checked / $sold) * 100, 1) : 0;
                    ?>
                    <div class="cd-tier-item">
                        <div class="cd-tier-head">
                            <strong style="color: rgb(var(--text)); font-size: 0.92rem;"><?= e($type['type_name']) ?></strong>
                            <span style="font-size: 0.8rem; color: rgb(var(--muted));">
                                <strong style="color: rgb(var(--text));"><?= $checked ?></strong> / <?= $sold ?> checked in (<?= $pct ?>%)
                            </span>
                        </div>
                        <div class="cd-progress-wrap">
                            <div class="cd-progress-fill" style="width: <?= $pct ?>%;"></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>

        <!-- Live Scan Stream / Recent Activity -->
        <section class="cd-card">
            <div class="cd-card-head">
                <div>
                    <h2 class="cd-card-title">
                        <span>⚡</span> Recent Gate Activity Log
                    </h2>
                    <p class="cd-card-sub">Real-time stream of verified admission passes.</p>
                </div>
            </div>

            <div class="cd-activity-list">
                <?php if (empty($recent_activity)): ?>
                    <div style="text-align: center; padding: 2.5rem 1rem; color: rgb(var(--muted));">
                        No check-in activity logged yet.
                    </div>
                <?php else: ?>
                    <?php foreach ($recent_activity as $act): ?>
                    <div class="cd-activity-item">
                        <div style="min-width: 0; flex: 1;">
                            <strong style="display: block; color: rgb(var(--text));"><?= e($act['attendee_name']) ?></strong>
                            <div style="font-size: 0.76rem; color: rgb(var(--muted)); margin-top: 0.15rem;">
                                <code style="font-family: monospace;"><?= e($act['ticket_code']) ?></code> &bull; <?= e($act['ticket_type']) ?>
                            </div>
                        </div>
                        <span style="font-size: 0.76rem; color: #10b981; font-weight: 800; background: rgba(16,185,129,0.12); padding: 0.2rem 0.55rem; border-radius: 6px; flex-shrink: 0;">
                            <?= date('H:i:s', strtotime($act['checked_in_at'])) ?>
                        </span>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>

    </div>

</div>

<!-- ════════════════════════════════════════════════════════════════
     GATE BOX OFFICE & TICKET SALES MODAL
     ════════════════════════════════════════════════════════════════ -->
<div id="modal-box-office" class="team-modal-backdrop">
    <div class="team-modal-dialog">
        <div class="team-modal-header">
            <div>
                <h3 class="team-modal-title">
                    <span>🎟️</span> Door / Gate Box Office
                </h3>
                <p class="team-modal-subtitle">Issue tickets on the spot for walk-in attendees</p>
            </div>
            <button type="button" id="btn-close-box-office" class="team-modal-close-btn" aria-label="Close modal">✕</button>
        </div>

        <form id="form-gate-sale">
            <?= csrf_field() ?>
            
            <div style="margin-bottom: 1rem;">
                <label class="team-modal-label">Ticket Tier *</label>
                <select name="ticket_type_id" id="gate-ticket-tier" required class="team-modal-select">
                    <?php foreach ($allTicketTypes as $tt): 
                        $cap = (int)($tt['capacity'] ?? 0);
                        $sold = (int)($tt['sold_count'] ?? 0);
                        $avail = $cap > 0 ? max(0, $cap - $sold) : 'Unlimited';
                    ?>
                        <option value="<?= $tt['id'] ?>" data-price="<?= (float)$tt['price'] ?>">
                            <?= e($tt['name']) ?> — <?= (float)$tt['price'] > 0 ? format_price($tt['price']) : 'Free' ?> (Avail: <?= $avail ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="gate-sale-grid-2">
                <div>
                    <label class="team-modal-label">Attendee Full Name *</label>
                    <input type="text" name="attendee_name" required placeholder="John Doe" class="team-modal-input" />
                </div>
                <div>
                    <label class="team-modal-label">Attendee Email</label>
                    <input type="email" name="attendee_email" placeholder="john@example.com" class="team-modal-input" />
                </div>
            </div>

            <div class="gate-sale-grid-2">
                <div>
                    <label class="team-modal-label">Quantity</label>
                    <input type="number" name="quantity" value="1" min="1" max="10" required class="team-modal-input" />
                </div>
                <div>
                    <label class="team-modal-label">Payment Method *</label>
                    <select name="payment_method" class="team-modal-select">
                        <option value="cash">💵 Cash at Gate</option>
                        <option value="pos">💳 POS Card Terminal</option>
                        <option value="transfer">📱 Bank Transfer / USSD</option>
                        <option value="comp">🎁 Complimentary / VIP Comp</option>
                    </select>
                </div>
            </div>

            <div class="team-modal-notice">
                <input type="checkbox" name="mark_checked_in" id="chk-gate-in" value="1" checked style="width:18px; height:18px; accent-color:rgb(var(--brand)); cursor:pointer;" />
                <label for="chk-gate-in" style="font-size:0.85rem; font-weight:700; color:rgb(var(--text)); cursor:pointer; margin:0;">
                    Check attendee into event immediately upon issue
                </label>
            </div>

            <div id="gate-sale-status" style="font-size:0.85rem; margin-bottom:0.75rem; display:none;"></div>

            <div style="display:flex; justify-content:flex-end; gap:0.6rem; margin-top:1.25rem;">
                <button type="button" id="btn-cancel-box-office" class="checkout-btn secondary" style="padding:0.75rem 1.25rem; border-radius:12px;">Cancel</button>
                <button type="submit" id="btn-submit-gate-sale" class="checkout-btn" style="padding:0.75rem 1.5rem; border-radius:12px; background:#10b981; color:#fff; font-weight:800; border:none; cursor:pointer;">
                    ✓ Issue Gate Ticket
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const csrfToken = "<?= csrf_token() ?>";

    // 1. Staff Check-In Toggle Logic
    const checkinBtns = document.querySelectorAll('.btn-toggle-checkin');
    checkinBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const asgId = this.getAttribute('data-assignment-id');
            const curState = this.getAttribute('data-current');
            this.disabled = true;
            this.textContent = 'Updating...';

            fetch('<?= route('/teams/toggle-staff-checkin') ?>', {
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
                    this.textContent = isChecked ? 'Check Out' : '✓ Check In Staff';
                    this.style.background = isChecked ? 'rgba(239,68,68,0.12)' : '#10b981';
                    this.style.color = isChecked ? '#ef4444' : '#fff';
                    this.style.border = isChecked ? '1px solid rgba(239,68,68,0.25)' : 'none';

                    const badge = document.getElementById('badge-checkin-' + asgId);
                    if (badge) {
                        badge.textContent = isChecked ? ('● Checked In' + (data.checked_in_at ? ' (' + data.checked_in_at + ')' : '')) : '○ Not Checked In';
                        badge.style.background = isChecked ? 'rgba(16,185,129,0.14)' : 'rgba(100,116,139,0.12)';
                        badge.style.color = isChecked ? '#059669' : 'rgb(100,116,139)';
                        badge.style.border = isChecked ? '1px solid rgba(16,185,129,0.3)' : 'none';
                    }
                } else {
                    alert(data.message || 'Error updating staff check-in.');
                    this.textContent = curState == '1' ? 'Check Out' : '✓ Check In Staff';
                }
            })
            .catch(() => {
                this.disabled = false;
                this.textContent = curState == '1' ? 'Check Out' : '✓ Check In Staff';
                alert('Could not update staff check-in.');
            });
        });
    });

    // 2. Gate Box Office Modal Actions
    const modalBoxOffice = document.getElementById('modal-box-office');
    const btnOpen = document.getElementById('btn-open-box-office');
    const btnClose = document.getElementById('btn-close-box-office');
    const btnCancel = document.getElementById('btn-cancel-box-office');
    const formSale = document.getElementById('form-gate-sale');
    const saleStatus = document.getElementById('gate-sale-status');
    const btnSubmit = document.getElementById('btn-submit-gate-sale');

    if (btnOpen && modalBoxOffice) {
        btnOpen.addEventListener('click', () => { modalBoxOffice.style.display = 'flex'; });
        btnClose.addEventListener('click', () => { modalBoxOffice.style.display = 'none'; });
        btnCancel.addEventListener('click', () => { modalBoxOffice.style.display = 'none'; });
        modalBoxOffice.addEventListener('click', (e) => {
            if (e.target === modalBoxOffice) modalBoxOffice.style.display = 'none';
        });
    }

    if (formSale) {
        formSale.addEventListener('submit', function(e) {
            e.preventDefault();
            btnSubmit.disabled = true;
            btnSubmit.textContent = 'Issuing...';
            saleStatus.style.display = 'none';

            const formData = new FormData(formSale);
            const payload = Object.fromEntries(formData.entries());

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
                btnSubmit.disabled = false;
                btnSubmit.textContent = '✓ Issue Gate Ticket';
                saleStatus.style.display = 'block';

                if (data.success) {
                    saleStatus.innerHTML = `<span style="color:#10b981; font-weight:800;">✓ ${data.message}</span>`;
                    setTimeout(() => {
                        modalBoxOffice.style.display = 'none';
                        window.location.reload();
                    }, 1500);
                } else {
                    saleStatus.innerHTML = `<span style="color:#ef4444; font-weight:800;">✕ ${data.message}</span>`;
                }
            })
            .catch(() => {
                btnSubmit.disabled = false;
                btnSubmit.textContent = '✓ Issue Gate Ticket';
                saleStatus.style.display = 'block';
                saleStatus.innerHTML = `<span style="color:#ef4444; font-weight:800;">✕ Connection error. Please try again.</span>`;
            });
        });
    }
});
</script>
@endsection