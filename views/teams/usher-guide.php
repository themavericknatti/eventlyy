<?php

/**
 * Team Usher & Floor Guide Workstation Page
 *
 * Workstation task view for Ushers and Floor Guides to manage
 * seating rows, VIP areas, guest directions, and crowd flow.
 *
 * @package Evently
 * @subpackage Views/Teams
 * @author Celio Natti
 * @version 2.0.0
 */

$eventTime = !empty($event['date']) ? strtotime($event['date']) : null;
$assignments = $assignments ?? [];
$sections = $sections ?? [];
$attendees = $attendees ?? [];
$total_seated = $total_seated ?? 0;
$total_expected = $total_expected ?? 0;
$remaining = max(0, $total_expected - $total_seated);
$fillRate = $total_expected > 0 ? round(($total_seated / $total_expected) * 100, 1) : 0;
?>

@extends('layouts/team')

@section('title')
Usher & Floor Guide Desk - <?= e($event['name'] ?? 'Evently') ?>
@endsection

@section('style')
<style>
.usher-page-wrapper {
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
}
.usher-event-hero {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: linear-gradient(135deg, rgba(16, 185, 129, 0.12) 0%, rgba(6, 95, 70, 0.08) 100%);
    border: 1px solid rgba(16, 185, 129, 0.25);
    border-radius: 20px;
    padding: 1.25rem 1.5rem;
    flex-wrap: wrap;
    gap: 1rem;
}
.usher-stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1rem;
}
.usher-main-layout {
    display: grid;
    grid-template-columns: 1.15fr 1fr;
    gap: 1.5rem;
    align-items: start;
}
.usher-flow-banner {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: rgb(var(--card));
    border: 1px solid rgba(var(--muted), 0.14);
    border-radius: 18px;
    padding: 1rem 1.35rem;
    flex-wrap: wrap;
    gap: 0.85rem;
}

@media (max-width: 1024px) {
    .usher-stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    .usher-main-layout {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 768px) {
    .usher-event-hero {
        flex-direction: column;
        align-items: stretch;
        padding: 1rem;
    }
    .usher-stats-grid {
        grid-template-columns: 1fr 1fr;
        gap: 0.65rem;
    }
    .usher-flow-banner {
        flex-direction: column;
        align-items: flex-start;
        padding: 1rem;
    }
}

@media (max-width: 480px) {
    .usher-stats-grid {
        grid-template-columns: 1fr;
    }
    .usher-search-row {
        flex-direction: column;
    }
}
</style>
@endsection

@section('content')
<div class="usher-page-wrapper">
    <!-- Active Event Context Banner -->
    <div class="usher-event-hero">
        <div class="support-event-info">
            <div style="display:flex; align-items:center; gap:0.45rem; margin-bottom:0.25rem; flex-wrap:wrap;">
                <span class="panel-badge" style="background: rgba(16, 185, 129, 0.18); color: #059669; font-weight: 800;">
                    👥 Workstation — Usher / Floor Guide
                </span>
                <span style="font-size:0.75rem; font-weight:800; background: #10b981; color: #fff; padding:0.2rem 0.6rem; border-radius:8px;">Floor Active</span>
            </div>
            <h1 style="font-size:clamp(1.3rem, 3vw, 1.75rem); margin:0.25rem 0; color:rgb(var(--text));"><?= e($event['name'] ?? 'Event Workspace') ?></h1>
            <p class="lede" style="margin:0; font-size:0.88rem; color:rgb(var(--muted));">
                <?php if ($eventTime): ?>
                    <span>📅 <?= date('D, M d, Y • g:i A', $eventTime) ?></span>
                    <span>•</span>
                <?php endif; ?>
                <span>📍 <?= e($event['location'] ?? 'Venue') ?></span>
            </p>
        </div>

        <?php if (count($assignments) > 1): ?>
            <div style="background:rgb(var(--card)); border:1px solid rgba(var(--muted),0.15); border-radius:14px; padding:0.65rem 1rem; display:flex; flex-direction:column; gap:0.35rem;">
                <span style="font-size:0.72rem; text-transform:uppercase; font-weight:700; color:rgb(var(--muted)); letter-spacing:0.4px;">Switch Event Workspace</span>
                <form method="POST" action="{{ route('teams/switch-event') }}">
                    <?= csrf_field() ?>
                    <select name="event_id" onchange="this.form.submit()" style="padding:0.4rem 0.75rem; border-radius:8px; border:1px solid rgba(var(--muted),0.25); background:rgb(var(--bg)); color:rgb(var(--text)); font-size:0.88rem; font-weight:600; cursor:pointer;">
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

    <!-- Quick Stats Grid -->
    <section class="usher-stats-grid">
        <article class="au-stat-card" style="border-color:rgba(16,185,129,0.3);">
            <div class="au-stat-body">
                <span class="au-stat-num" style="color: #059669;"><?= number_format($total_seated) ?></span>
                <span class="au-stat-lbl">Seated / Arrived Guests</span>
            </div>
        </article>
        <article class="au-stat-card">
            <div class="au-stat-body">
                <span class="au-stat-num"><?= number_format($total_expected) ?></span>
                <span class="au-stat-lbl">Total Expected Guests</span>
            </div>
        </article>
        <article class="au-stat-card" style="border-color:rgba(217,119,6,0.3);">
            <div class="au-stat-body">
                <span class="au-stat-num" style="color: #d97706;"><?= number_format($remaining) ?></span>
                <span class="au-stat-lbl">Awaiting Arrival</span>
            </div>
        </article>
        <article class="au-stat-card" style="border-color:rgba(99,102,241,0.3);">
            <div class="au-stat-body">
                <span class="au-stat-num" style="color: #6366f1;"><?= $fillRate ?>%</span>
                <span class="au-stat-lbl">Floor Seating Occupancy</span>
            </div>
        </article>
    </section>

    <!-- Crowd Flow Velocity Banner -->
    <div class="usher-flow-banner">
        <div style="display:flex; align-items:center; gap:0.75rem;">
            <span style="font-size:1.5rem;">🚦</span>
            <div>
                <strong style="font-size:0.95rem; color:rgb(var(--text)); display:block;">Gate Ingress &amp; Crowd Flow Status</strong>
                <span style="font-size:0.82rem; color:rgb(var(--muted));">
                    <?= $fillRate >= 85 ? '🔴 High Traffic / Near Capacity — Guide guests to remaining rear/balcony rows.' : ($fillRate >= 50 ? '🟡 Steady Ingress Flow — Direct guests to designated tier rows.' : '🟢 Smooth Flow — Main floor and VIP front aisles open.') ?>
                </span>
            </div>
        </div>
        <div style="display:flex; align-items:center; gap:0.5rem; flex-wrap:wrap;">
            <span style="font-size:0.78rem; font-weight:800; background:rgba(16,185,129,0.12); color:#059669; border:1px solid rgba(16,185,129,0.25); padding:0.35rem 0.8rem; border-radius:10px;">
                ✓ Aisle Marshals Active
            </span>
            <span style="font-size:0.78rem; font-weight:800; background:rgba(99,102,241,0.12); color:#6366f1; border:1px solid rgba(99,102,241,0.25); padding:0.35rem 0.8rem; border-radius:10px;">
                ⚡ Fast Row Routing
            </span>
        </div>
    </div>

    <!-- Main Content Dual-Column Layout -->
    <div class="usher-main-layout">
        
        <!-- Left: Fast Attendee Seating & Row Locator -->
        <section class="form-card" style="margin-bottom:0;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem; flex-wrap:wrap; gap:0.5rem;">
                <div>
                    <h2 style="margin:0; font-size:1.15rem; font-weight:800; color:rgb(var(--text));">🔎 Guest Seating &amp; Row Locator</h2>
                    <p style="margin:0.2rem 0 0; font-size:0.82rem; color:rgb(var(--muted));">Look up attendee by name or ticket code to find their row and section</p>
                </div>
            </div>

            <!-- Search Form -->
            <form method="GET" action="{{ route('teams/usher-guide') }}" style="margin-bottom:1.25rem;">
                <div class="usher-search-row" style="display:flex; gap:0.5rem;">
                    <input type="text" name="q" value="<?= e($search ?? '') ?>" placeholder="Type attendee name, email, or ticket code..." class="team-modal-input" style="flex:1;" />
                    <button type="submit" class="checkout-btn" style="padding:0.75rem 1.35rem; border-radius:12px; background:rgb(var(--brand)); color:#fff; font-weight:800; border:none; cursor:pointer; white-space:nowrap;">
                        Search
                    </button>
                    <?php if (!empty($search)): ?>
                        <a href="{{ route('teams/usher-guide') }}" class="checkout-btn secondary" style="padding:0.75rem 1rem; border-radius:12px; text-decoration:none; display:flex; align-items:center; justify-content:center; white-space:nowrap;">
                            Clear
                        </a>
                    <?php endif; ?>
                </div>
            </form>

            <!-- Results List -->
            <div style="display:flex; flex-direction:column; gap:0.65rem; max-height:480px; overflow-y:auto;">
                <?php if (empty($attendees)): ?>
                    <div style="text-align:center; padding:2.5rem 1rem; color:rgb(var(--muted)); background:rgba(var(--muted),0.04); border-radius:16px; border:1px dashed rgba(var(--muted),0.18);">
                        <span style="font-size:2rem; display:block; margin-bottom:0.35rem;">👤</span>
                        <p style="margin:0; font-weight:700; color:rgb(var(--text));">No matching attendees found.</p>
                        <span style="font-size:0.78rem;">Enter a name or ticket code above to direct guests to their seating rows.</span>
                    </div>
                <?php else: ?>
                    <?php foreach ($attendees as $att): ?>
                        <div style="background:rgb(var(--bg)); border:1px solid rgba(var(--muted),0.14); border-radius:14px; padding:0.85rem 1.1rem; display:flex; justify-content:space-between; align-items:center; gap:0.75rem; flex-wrap:wrap;">
                            <div style="flex:1; min-width:0;">
                                <div style="display:flex; align-items:center; gap:0.5rem;">
                                    <strong style="font-size:0.95rem; color:rgb(var(--text));"><?= e($att['attendee_name']) ?></strong>
                                    <?php if ($att['checked_in']): ?>
                                        <span style="font-size:0.72rem; font-weight:800; background:rgba(16,185,129,0.12); color:#059669; padding:2px 6px; border-radius:6px;">✓ Seated</span>
                                    <?php else: ?>
                                        <span style="font-size:0.72rem; font-weight:800; background:rgba(217,119,6,0.12); color:#d97706; padding:2px 6px; border-radius:6px;">○ Not Checked In</span>
                                    <?php endif; ?>
                                </div>
                                <div style="font-size:0.8rem; color:rgb(var(--muted)); margin-top:0.2rem;">
                                    <code><?= e($att['ticket_code']) ?></code> • <span style="font-weight:700; color:rgb(var(--brand));"><?= e($att['ticket_type']) ?></span>
                                </div>
                            </div>
                            <div style="text-align:right;">
                                <span style="font-size:0.82rem; font-weight:800; background:rgba(99,102,241,0.12); color:#6366f1; border:1px solid rgba(99,102,241,0.25); padding:0.3rem 0.75rem; border-radius:10px; display:inline-block;">
                                    <?php
                                        $tName = strtolower($att['ticket_type'] ?? '');
                                        if (str_contains($tName, 'vip') || str_contains($tName, 'table')) {
                                            echo '📍 Section A • Rows 1-5 (VIP Front)';
                                        } elseif (str_contains($tName, 'prem') || str_contains($tName, 'gold')) {
                                            echo '📍 Section B • Rows 6-15 (Center)';
                                        } elseif (str_contains($tName, 'balcony') || str_contains($tName, 'deck')) {
                                            echo '📍 Section D • Balcony Deck';
                                        } else {
                                            echo '📍 Section C • Rows 16-30 (General)';
                                        }
                                    ?>
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>

        <!-- Right: Seating & Row Section Layout Guide -->
        <div style="display:flex; flex-direction:column; gap:1.25rem;">
            
            <section class="form-card" style="margin-bottom:0;">
                <h2 style="margin:0 0 0.25rem; font-size:1.15rem; font-weight:800; color:rgb(var(--text));">🗺️ Seating Row Allocations</h2>
                <p style="margin:0 0 1.25rem; font-size:0.82rem; color:rgb(var(--muted));">Venue seating blocks &amp; real-time occupancy rates</p>

                <div style="display:flex; flex-direction:column; gap:1.1rem;">
                    <?php if (empty($sections)): ?>
                        <p style="color:rgb(var(--muted)); text-align:center; padding:1.5rem 0;">No tier sections configured.</p>
                    <?php else: ?>
                        <?php foreach ($sections as $sec): ?>
                            <div style="background:rgb(var(--bg)); border:1px solid rgba(var(--muted),0.14); border-radius:14px; padding:1rem 1.15rem;">
                                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.4rem; flex-wrap:wrap; gap:0.25rem;">
                                    <div>
                                        <strong style="font-size:0.92rem; color:rgb(var(--text));"><?= e($sec['section']) ?></strong>
                                        <span style="font-size:0.78rem; color:rgb(var(--muted)); display:block;"><?= e($sec['rows']) ?> • <?= e($sec['tier_name']) ?></span>
                                    </div>
                                    <div style="text-align:right;">
                                        <strong style="font-size:0.92rem; color:#059669;"><?= $sec['checked_in'] ?></strong>
                                        <span style="font-size:0.8rem; color:rgb(var(--muted));">/ <?= $sec['sold'] ?> seated (<?= $sec['occupancy_pct'] ?>%)</span>
                                    </div>
                                </div>
                                <div style="width:100%; height:8px; background:rgba(var(--muted),0.12); border-radius:10px; overflow:hidden;">
                                    <div style="width:<?= $sec['occupancy_pct'] ?>%; height:100%; background:linear-gradient(90deg, #10b981, #6366f1); border-radius:10px; transition:width 0.4s ease;"></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </section>

            <!-- Usher Floor Protocol Quick Reference -->
            <section class="form-card" style="margin-bottom:0; background:linear-gradient(180deg, rgb(var(--card)) 0%, rgba(99,102,241,0.03) 100%);">
                <h3 style="margin:0 0 0.65rem; font-size:0.95rem; font-weight:800; color:rgb(var(--text));">📋 Floor Guide Quick Protocol</h3>
                <ul style="margin:0; padding-left:1.2rem; font-size:0.82rem; color:rgb(var(--muted)); line-height:1.55; display:flex; flex-direction:column; gap:0.35rem;">
                    <li><strong>VIP Guests:</strong> Escort to Section A (Front stage rows 1-5). Offer priority access pass.</li>
                    <li><strong>Aisle Clearance:</strong> Keep main center gangways and emergency exit routes completely clear.</li>
                    <li><strong>Accessibility:</strong> Wheelchair and assisted seating is located on Ground Floor Left Section C.</li>
                    <li><strong>Lost Attendee:</strong> Search their name above to quickly redirect them to their allocated row.</li>
                </ul>
            </section>

        </div>
    </div>
</div>
@endsection
