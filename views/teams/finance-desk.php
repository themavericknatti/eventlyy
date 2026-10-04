<?php

/**
 * Team Finance Desk Page
 *
 * Workstation task view for Finance Managers to track live gate realized revenue,
 * ticket tier admission earnings, and verified entrance transactions.
 * Restricted to checked-in attendees to protect overall event sales privacy from staff.
 *
 * @package Eventlyy
 * @subpackage Views/Teams
 * @author Celio Natti
 * @version 3.0.0
 */

$eventTime = !empty($event['date']) ? strtotime($event['date']) : null;
$gateRev = (float)($finance['gate_revenue'] ?? 0);
$checkedInCount = (int)($finance['checked_in_count'] ?? 0);
$totalTickets = (int)($stats['total_tickets'] ?? 0);
$arrivalRate = (float)($stats['percentage'] ?? 0);
?>

@extends('layouts/team')

@section('title')
Finance Desk &amp; Gate Revenue — <?= e($event['name'] ?? 'Eventlyy') ?>
@endsection

@section('style')
<style>
/* ═════════════════════════════════════════════════════════════
   Finance Desk Component System (.fin-*)
   Scoped, modern, accessible, responsive across viewports.
   ═════════════════════════════════════════════════════════════ */

.fin-wrapper {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
    padding-bottom: 2rem;
}

/* ─── Hero Header ──────────────────────────────────────────── */
.fin-hero {
    background: radial-gradient(circle at 10% 20%, rgba(16, 185, 129, 0.12) 0%, transparent 45%),
                linear-gradient(135deg, rgba(var(--card), 0.95), rgba(var(--card), 0.85));
    border: 1px solid rgba(16, 185, 129, 0.22);
    border-radius: 22px;
    padding: clamp(1.25rem, 3vw, 1.85rem) clamp(1.25rem, 3vw, 2rem);
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1.25rem;
    flex-wrap: wrap;
    box-shadow: 0 10px 30px -10px rgba(16, 185, 129, 0.12);
}

.fin-hero-text {
    min-width: 0;
    flex: 1;
}

.fin-badge-wrap {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 0.45rem;
    flex-wrap: wrap;
}

.fin-pill-desk {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    padding: 0.25rem 0.75rem;
    border-radius: 999px;
    font-size: 0.76rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    background: rgba(16, 185, 129, 0.14);
    color: #059669;
    border: 1px solid rgba(16, 185, 129, 0.28);
}

.fin-pill-live {
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

.fin-pill-live::before {
    content: '';
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: #10b981;
    display: inline-block;
}

.fin-title {
    font-size: clamp(1.35rem, 3vw, 1.85rem);
    font-weight: 900;
    color: rgb(var(--text));
    margin: 0 0 0.35rem;
    line-height: 1.2;
    letter-spacing: -0.02em;
}

.fin-meta {
    font-size: 0.86rem;
    color: rgb(var(--muted));
    margin: 0;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex-wrap: wrap;
}

/* ─── 4-Card Stats Grid ────────────────────────────────────── */
.fin-stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1.15rem;
}

.fin-stat-card {
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

.fin-stat-card:hover {
    transform: translateY(-2px);
    border-color: rgba(var(--brand), 0.3);
}

.fin-stat-icon {
    width: 48px;
    height: 48px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.fin-stat-icon--emerald {
    background: rgba(16, 185, 129, 0.12);
    color: #10b981;
}

.fin-stat-icon--brand {
    background: rgba(var(--brand), 0.12);
    color: rgb(var(--brand));
}

.fin-stat-icon--blue {
    background: rgba(14, 165, 233, 0.12);
    color: #0ea5e9;
}

.fin-stat-icon--amber {
    background: rgba(245, 158, 11, 0.12);
    color: #f59e0b;
}

.fin-stat-val {
    font-size: clamp(1.35rem, 2.5vw, 1.75rem);
    font-weight: 900;
    line-height: 1.15;
    letter-spacing: -0.02em;
    color: rgb(var(--text));
    display: block;
}

.fin-stat-label {
    font-size: 0.78rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: rgb(var(--muted));
    margin-top: 0.2rem;
    display: block;
}

/* ─── Card Container ───────────────────────────────────────── */
.fin-card {
    background: rgb(var(--card));
    border: 1px solid rgba(var(--muted), 0.14);
    border-radius: 22px;
    padding: clamp(1.25rem, 3vw, 1.75rem);
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
}

.fin-card-head {
    margin-bottom: 1.25rem;
    padding-bottom: 0.85rem;
    border-bottom: 1px solid rgba(var(--muted), 0.12);
}

.fin-card-title {
    font-size: 1.15rem;
    font-weight: 800;
    color: rgb(var(--text));
    margin: 0 0 0.25rem;
    letter-spacing: -0.015em;
    display: flex;
    align-items: center;
    gap: 0.45rem;
}

.fin-card-sub {
    font-size: 0.84rem;
    color: rgb(var(--muted));
    margin: 0;
}

/* ─── Table Component ──────────────────────────────────────── */
.fin-table-wrap {
    width: 100%;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    border-radius: 14px;
    border: 1px solid rgba(var(--muted), 0.12);
}

.fin-table {
    width: 100%;
    border-collapse: collapse;
    text-align: left;
    min-width: 580px;
}

.fin-table th {
    background: rgba(var(--muted), 0.06);
    color: rgb(var(--muted));
    font-size: 0.78rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    padding: 0.85rem 1rem;
    border-bottom: 1px solid rgba(var(--muted), 0.14);
}

.fin-table td {
    padding: 0.95rem 1rem;
    font-size: 0.88rem;
    border-bottom: 1px solid rgba(var(--muted), 0.08);
    color: rgb(var(--text));
}

.fin-table tr:last-child td {
    border-bottom: none;
}

.fin-table tr:hover td {
    background: rgba(var(--muted), 0.02);
}

.fin-price-badge {
    background: rgba(var(--muted), 0.08);
    color: rgb(var(--text));
    font-family: inherit;
    font-size: 0.82rem;
    font-weight: 700;
    padding: 0.2rem 0.55rem;
    border-radius: 6px;
    border: 1px solid rgba(var(--muted), 0.15);
}

.fin-progress-bar {
    width: 100%;
    height: 8px;
    background: rgba(var(--muted), 0.15);
    border-radius: 99px;
    overflow: hidden;
}

.fin-progress-fill {
    height: 100%;
    background: linear-gradient(90deg, rgb(var(--brand)), #10b981);
    border-radius: 99px;
    transition: width 0.4s ease;
}

.fin-code-badge {
    font-family: monospace;
    font-size: 0.82rem;
    font-weight: 700;
    background: rgba(var(--muted), 0.08);
    padding: 0.2rem 0.55rem;
    border-radius: 6px;
    border: 1px solid rgba(var(--muted), 0.14);
    color: rgb(var(--text));
}

.fin-status-pill {
    font-size: 0.72rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    padding: 0.2rem 0.6rem;
    border-radius: 6px;
    background: rgba(16, 185, 129, 0.12);
    color: #10b981;
    border: 1px solid rgba(16, 185, 129, 0.25);
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
}

.fin-empty-state {
    text-align: center;
    padding: 2.5rem 1.5rem;
    color: rgb(var(--muted));
    font-size: 0.9rem;
}

/* ═════════════════════════════════════════════════════════════
   Responsive Breakpoints
   ═════════════════════════════════════════════════════════════ */

/* Tablet (max 1024px) */
@media (max-width: 1024px) {
    .fin-stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

/* Mobile (max 680px) */
@media (max-width: 680px) {
    .fin-hero {
        flex-direction: column;
        align-items: stretch;
        padding: 1.25rem 1.15rem;
    }

    .fin-stats-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 0.75rem;
    }

    .fin-stat-card {
        padding: 1rem;
        flex-direction: column;
        align-items: flex-start;
        gap: 0.65rem;
    }

    .fin-stat-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
    }

    .fin-stat-val {
        font-size: 1.25rem;
    }

    .fin-stat-label {
        font-size: 0.72rem;
    }

    .fin-card {
        padding: 1.25rem 1rem;
        border-radius: 18px;
    }
}

/* Very Small Mobile (< 400px) */
@media (max-width: 400px) {
    .fin-stats-grid {
        grid-template-columns: 1fr;
    }
}
</style>
@endsection

@section('content')
<div class="fin-wrapper">

    <!-- ═══ Hero Header ═══ -->
    <header class="fin-hero">
        <div class="fin-hero-text">
            <div class="fin-badge-wrap">
                <span class="fin-pill-desk">💼 Workstation — Finance Desk</span>
                <span class="fin-pill-live">Realized Revenue</span>
            </div>
            <h1 class="fin-title"><?= e($event['name'] ?? 'Gate Revenue Overview') ?></h1>
            <p class="fin-meta">
                <?php if ($eventTime): ?>
                    <span>📅 <?= date('D, M d, Y • g:i A', $eventTime) ?></span>
                    <span>&bull;</span>
                <?php endif; ?>
                <span>📍 <?= e($event['location'] ?? 'Venue Turnstiles') ?></span>
                <span>&bull;</span>
                <span>Verified Door Admissions</span>
            </p>
        </div>
    </header>

    <!-- ═══ 4-Card Stats Grid ═══ -->
    <section class="fin-stats-grid">
        <div class="fin-stat-card">
            <div class="fin-stat-icon fin-stat-icon--emerald">
                <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" y1="1" x2="12" y2="23"></line>
                    <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                </svg>
            </div>
            <div>
                <span class="fin-stat-val" style="color: #10b981;"><?= format_price($gateRev) ?></span>
                <span class="fin-stat-label">Gate Realized Revenue</span>
            </div>
        </div>

        <div class="fin-stat-card">
            <div class="fin-stat-icon fin-stat-icon--brand">
                <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                </svg>
            </div>
            <div>
                <span class="fin-stat-val" style="color: rgb(var(--brand));"><?= number_format($checkedInCount) ?></span>
                <span class="fin-stat-label">Admitted Attendees</span>
            </div>
        </div>

        <div class="fin-stat-card">
            <div class="fin-stat-icon fin-stat-icon--blue">
                <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                    <line x1="16" y1="2" x2="16" y2="6"></line>
                    <line x1="8" y1="2" x2="8" y2="6"></line>
                    <line x1="3" y1="10" x2="21" y2="10"></line>
                </svg>
            </div>
            <div>
                <span class="fin-stat-val"><?= number_format($totalTickets) ?></span>
                <span class="fin-stat-label">Total Event Capacity</span>
            </div>
        </div>

        <div class="fin-stat-card">
            <div class="fin-stat-icon fin-stat-icon--amber">
                <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="20" x2="18" y2="10"></line>
                    <line x1="12" y1="20" x2="12" y2="4"></line>
                    <line x1="6" y1="20" x2="6" y2="14"></line>
                </svg>
            </div>
            <div>
                <span class="fin-stat-val" style="color: #f59e0b;"><?= $arrivalRate ?>%</span>
                <span class="fin-stat-label">Door Arrival Rate</span>
            </div>
        </div>
    </section>

    <!-- ═══ Checked-In Ticket Tier Breakdown ═══ -->
    <section class="fin-card">
        <div class="fin-card-head">
            <h2 class="fin-card-title">
                <span>📊</span> Checked-In Ticket Tier Breakdown
            </h2>
            <p class="fin-card-sub">Realized gate earnings per ticket tier calculated from physically verified passes at the door.</p>
        </div>

        <div class="fin-table-wrap">
            <table class="fin-table">
                <thead>
                    <tr>
                        <th>Ticket Tier</th>
                        <th>Unit Price</th>
                        <th>Checked-In / Capacity</th>
                        <th>Arrival Progress</th>
                        <th style="text-align: right;">Realized Revenue</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($finance['tiers'])): ?>
                        <tr>
                            <td colspan="5" class="fin-empty-state">No ticket tiers configured for this event.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($finance['tiers'] as $tier):
                            $checkedIn = (int)($tier['checked_in_count'] ?? 0);
                            $cap = (int)($tier['capacity'] ?? 0);
                            $pct = $cap > 0 ? min(100, round(($checkedIn / $cap) * 100, 1)) : 0;
                            $rev = (float)($tier['gate_tier_revenue'] ?? 0.00);
                        ?>
                        <tr>
                            <td><strong><?= e($tier['type_name']) ?></strong></td>
                            <td><span class="fin-price-badge"><?= format_price((float)$tier['price']) ?></span></td>
                            <td><strong><?= number_format($checkedIn) ?></strong> / <?= $cap > 0 ? number_format($cap) : '∞' ?></td>
                            <td>
                                <div style="display: flex; align-items: center; gap: 0.65rem; min-width: 140px;">
                                    <div class="fin-progress-bar">
                                        <div class="fin-progress-fill" style="width: <?= $pct ?>%;"></div>
                                    </div>
                                    <span style="font-size: 0.78rem; font-weight: 800; min-width: 36px; text-align: right;"><?= $pct ?>%</span>
                                </div>
                            </td>
                            <td style="text-align: right;"><strong style="color: #10b981; font-size: 0.98rem;"><?= format_price($rev) ?></strong></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

    <!-- ═══ Recent Checked-In Gate Admissions ═══ -->
    <section class="fin-card">
        <div class="fin-card-head">
            <h2 class="fin-card-title">
                <span>⚡</span> Recent Checked-In Gate Admissions
            </h2>
            <p class="fin-card-sub">Real-time audit log of ticket passes validated by scanners at venue turnstiles.</p>
        </div>

        <div class="fin-table-wrap">
            <table class="fin-table">
                <thead>
                    <tr>
                        <th>Pass Code</th>
                        <th>Attendee Name</th>
                        <th>Ticket Tier</th>
                        <th>Ticket Value</th>
                        <th>Gate Status</th>
                        <th style="text-align: right;">Admission Timestamp</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($finance['transactions'])): ?>
                        <tr>
                            <td colspan="6" class="fin-empty-state">No checked-in attendees logged yet for this event.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($finance['transactions'] as $tx): ?>
                        <tr>
                            <td><span class="fin-code-badge"><?= e($tx['ticket_code']) ?></span></td>
                            <td>
                                <strong style="display: block; color: rgb(var(--text));"><?= e($tx['attendee_name']) ?></strong>
                                <span style="font-size: 0.75rem; color: rgb(var(--muted));"><?= e($tx['attendee_email']) ?></span>
                            </td>
                            <td><strong><?= e($tx['ticket_type_name']) ?></strong></td>
                            <td><strong><?= format_price((float)$tx['price']) ?></strong></td>
                            <td>
                                <span class="fin-status-pill">
                                    <span>●</span> Admitted
                                </span>
                            </td>
                            <td style="font-size: 0.82rem; color: rgb(var(--muted)); text-align: right;">
                                <?= date('H:i:s • M d', strtotime($tx['purchase_date'])) ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

</div>
@endsection
