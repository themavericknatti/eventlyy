<?php

/**
 * Team Finance Desk Page
 *
 * Workstation task view for Finance Managers to track live gate realized revenue,
 * ticket tier admission earnings, and verified entrance transactions.
 * Restricted to checked-in attendees to protect overall event sales privacy from staff.
 *
 * @package Evently
 * @subpackage Views/Teams
 * @author Celio Natti
 * @version 2.2.0
 */

$eventTime = !empty($event['date']) ? strtotime($event['date']) : null;
?>

@extends('layouts/team')

@section('title')
Gate Revenue &amp; Finance Desk - <?= e($event['name'] ?? 'Evently') ?>
@endsection

@section('style')
<style>
.fin-stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1rem;
    margin-bottom: 1.5rem;
}

@media (max-width: 1024px) {
    .fin-stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}
@media (max-width: 768px) {
    .page-hero { 
        flex-direction: column; 
        align-items: stretch; 
        gap: 0.75rem; 
    }
    .fin-stats-grid {
        grid-template-columns: 1fr 1fr;
        gap: 0.65rem;
    }
}
@media (max-width: 480px) {
    .fin-stats-grid {
        grid-template-columns: 1fr;
    }
}
</style>
@endsection

@section('content')
<section class="page-hero" style="margin-bottom:1.5rem;">
    <div>
        <span class="panel-badge" style="background:rgba(16, 185, 129, 0.12); color:#10b981; font-weight:700;">Workstation — Finance Desk</span>
        <h1 style="font-size:clamp(1.35rem, 3vw, 1.85rem); margin:0.25rem 0; color:rgb(var(--text));"><?= e($event['name'] ?? 'Gate Revenue Overview') ?></h1>
        <p class="lede" style="margin:0; font-size:0.92rem; color:rgb(var(--muted));">
            <?php if ($eventTime): ?>
                <span>📅 <?= date('D, M d, Y • g:i A', $eventTime) ?></span> •
            <?php endif; ?>
            <span>📍 <?= e($event['location'] ?? 'Venue') ?></span>
        </p>
    </div>
</section>

<!-- Financial High-Level Stat Cards -->
<section class="fin-stats-grid">
    <div class="au-stat-card">
        <span class="au-stat-icon au-stat-icon--success">
            <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="12" y1="1" x2="12" y2="23"/>
                <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
            </svg>
        </span>
        <div class="au-stat-body">
            <span class="au-stat-num" style="color:#10b981;"><?= format_price($finance['gate_revenue'] ?? 0) ?></span>
            <span class="au-stat-lbl">Gate Realized Revenue</span>
        </div>
    </div>
    <div class="au-stat-card">
        <span class="au-stat-icon au-stat-icon--brand">
            <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
                <polyline points="22 4 12 14.01 9 11.01" />
            </svg>
        </span>
        <div class="au-stat-body">
            <span class="au-stat-num"><?= number_format($finance['checked_in_count'] ?? 0) ?></span>
            <span class="au-stat-lbl">Checked-In Admissions</span>
        </div>
    </div>
    <div class="au-stat-card">
        <span class="au-stat-icon">
            <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                <line x1="16" y1="2" x2="16" y2="6"/>
                <line x1="8" y1="2" x2="8" y2="6"/>
                <line x1="3" y1="10" x2="21" y2="10"/>
            </svg>
        </span>
        <div class="au-stat-body">
            <span class="au-stat-num"><?= number_format($stats['total_tickets'] ?? 0) ?></span>
            <span class="au-stat-lbl">Total Capacity</span>
        </div>
    </div>
    <div class="au-stat-card">
        <span class="au-stat-icon au-stat-icon--warning">
            <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="1" y="4" width="22" height="16" rx="2" ry="2"/>
                <line x1="1" y1="10" x2="23" y2="10"/>
            </svg>
        </span>
        <div class="au-stat-body">
            <span class="au-stat-num" style="color:#d97706;"><?= $stats['percentage'] ?? 0 ?>%</span>
            <span class="au-stat-lbl">Door Arrival Rate</span>
        </div>
    </div>
</section>

<!-- Content Layout -->
<div style="display:flex; flex-direction:column; gap:1.5rem; margin-top:1.5rem;">
    <!-- Revenue per Ticket Tier Table -->
    <section class="form-card">
        <div style="margin-bottom:1.25rem; border-bottom:1px solid rgba(var(--muted),0.1); padding-bottom:0.75rem;">
            <h2 style="margin:0; font-size:1.15rem; font-weight:700; color:rgb(var(--text));">Checked-In Ticket Tier Financial Breakdown</h2>
            <p class="section-note" style="margin:0.25rem 0 0; font-size:0.82rem; color:rgb(var(--muted));">Realized gate revenue per ticket tier for attendees at the door.</p>
        </div>

        <div style="width:100%; overflow-x:auto; -webkit-overflow-scrolling:touch;">
            <table class="au-table" style="width:100%; min-width:540px;">
                <thead>
                    <tr>
                        <th>Ticket Tier</th>
                        <th>Unit Price</th>
                        <th>Checked-In / Capacity</th>
                        <th>Arrival Progress</th>
                        <th style="text-align:right;">Gate Realized Revenue</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($finance['tiers'])): ?>
                        <tr><td colspan="5" style="padding:2rem; text-align:center; color:rgb(var(--muted));">No ticket tiers configured for this event.</td></tr>
                    <?php else: ?>
                        <?php foreach ($finance['tiers'] as $tier):
                            $checkedIn = (int)($tier['checked_in_count'] ?? 0);
                            $cap = (int)($tier['capacity'] ?? 0);
                            $pct = $cap > 0 ? min(100, round(($checkedIn / $cap) * 100, 1)) : 0;
                            $rev = (float)($tier['gate_tier_revenue'] ?? 0.00);
                        ?>
                        <tr>
                            <td><strong style="color:rgb(var(--text));"><?= e($tier['type_name']) ?></strong></td>
                            <td><code style="background:rgba(var(--muted),0.1); padding:0.2rem 0.45rem; border-radius:6px; font-weight:600; color:rgb(var(--text));"><?= format_price((float)$tier['price']) ?></code></td>
                            <td><strong style="color:rgb(var(--text));"><?= number_format($checkedIn) ?></strong> / <?= $cap > 0 ? number_format($cap) : '∞' ?></td>
                            <td>
                                <div style="display:flex; align-items:center; gap:0.6rem; min-width:120px;">
                                    <div style="flex:1; height:8px; background:rgba(var(--muted),0.15); border-radius:10px; overflow:hidden;">
                                        <div style="width:<?= $pct ?>%; height:100%; background:linear-gradient(90deg, rgb(var(--brand)), #10b981); border-radius:10px;"></div>
                                    </div>
                                    <span style="font-size:0.78rem; font-weight:700; min-width:36px; text-align:right; color:rgb(var(--text));"><?= $pct ?>%</span>
                                </div>
                            </td>
                            <td style="text-align:right;"><strong style="color:#10b981; font-size:0.95rem;"><?= format_price($rev) ?></strong></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

    <!-- Recent Checked-in Ticket Transactions Log -->
    <section class="form-card">
        <div style="margin-bottom:1.25rem; border-bottom:1px solid rgba(var(--muted),0.1); padding-bottom:0.75rem;">
            <h2 style="margin:0; font-size:1.15rem; font-weight:700; color:rgb(var(--text));">Recent Checked-In Gate Admissions</h2>
            <p class="section-note" style="margin:0.25rem 0 0; font-size:0.82rem; color:rgb(var(--muted));">Stream of recently verified ticket passes at the door.</p>
        </div>

        <div style="width:100%; overflow-x:auto; -webkit-overflow-scrolling:touch;">
            <table class="au-table" style="width:100%; min-width:540px;">
                <thead>
                    <tr>
                        <th>Ticket Code</th>
                        <th>Attendee</th>
                        <th>Tier</th>
                        <th>Ticket Value</th>
                        <th>Gate Status</th>
                        <th style="text-align:right;">Check-In Time</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($finance['transactions'])): ?>
                        <tr><td colspan="6" style="padding:2.5rem; text-align:center; color:rgb(var(--muted));">No checked-in attendees logged yet for this event.</td></tr>
                    <?php else: ?>
                        <?php foreach ($finance['transactions'] as $tx): ?>
                        <tr>
                            <td><code style="background:rgba(var(--muted),0.1); padding:0.2rem 0.45rem; border-radius:6px; font-weight:700; font-family:monospace; color:rgb(var(--text));"><?= e($tx['ticket_code']) ?></code></td>
                            <td>
                                <strong style="color:rgb(var(--text));"><?= e($tx['attendee_name']) ?></strong>
                                <div style="font-size:0.75rem; color:rgb(var(--muted));"><?= e($tx['attendee_email']) ?></div>
                            </td>
                            <td style="color:rgb(var(--text));"><?= e($tx['ticket_type_name']) ?></td>
                            <td><strong style="color:rgb(var(--text));"><?= format_price((float)$tx['price']) ?></strong></td>
                            <td>
                                <span class="au-status-badge au-status--active">Checked In</span>
                            </td>
                            <td style="font-size:0.8rem; color:rgb(var(--muted)); text-align:right;">
                                <?= date('H:i • M d', strtotime($tx['purchase_date'])) ?>
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
