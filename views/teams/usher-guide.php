<?php

/**
 * Team Usher & Floor Guide Workstation Page
 *
 * Workstation task view for Ushers and Floor Guides to manage
 * seating rows, VIP areas, guest directions, and crowd flow.
 *
 * @package Eventlyy
 * @subpackage Views/Teams
 * @author Celio Natti
 * @version 3.0.0
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
Usher &amp; Floor Guide Desk — <?= e($event['name'] ?? 'Eventlyy') ?>
@endsection

@section('style')
<style>
/* ═════════════════════════════════════════════════════════════
   Usher & Floor Guide Component System (.ug-*)
   Scoped, modern, accessible, responsive across viewports.
   ═════════════════════════════════════════════════════════════ */

.ug-wrapper {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
    padding-bottom: 2rem;
}

/* ─── Hero Header ──────────────────────────────────────────── */
.ug-hero {
    background: radial-gradient(circle at 10% 20%, rgba(168, 85, 247, 0.12) 0%, transparent 45%),
                linear-gradient(135deg, rgba(var(--card), 0.95), rgba(var(--card), 0.85));
    border: 1px solid rgba(168, 85, 247, 0.22);
    border-radius: 22px;
    padding: clamp(1.25rem, 3vw, 1.85rem) clamp(1.25rem, 3vw, 2rem);
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1.25rem;
    flex-wrap: wrap;
    box-shadow: 0 10px 30px -10px rgba(168, 85, 247, 0.12);
}

.ug-hero-text {
    min-width: 0;
    flex: 1;
}

.ug-badge-wrap {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 0.45rem;
    flex-wrap: wrap;
}

.ug-pill-desk {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    padding: 0.25rem 0.75rem;
    border-radius: 999px;
    font-size: 0.76rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    background: rgba(168, 85, 247, 0.14);
    color: #9333ea;
    border: 1px solid rgba(168, 85, 247, 0.28);
}

.ug-pill-live {
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

.ug-pill-live::before {
    content: '';
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: #10b981;
    display: inline-block;
}

.ug-title {
    font-size: clamp(1.35rem, 3vw, 1.85rem);
    font-weight: 900;
    color: rgb(var(--text));
    margin: 0 0 0.35rem;
    line-height: 1.2;
    letter-spacing: -0.02em;
}

.ug-meta {
    font-size: 0.86rem;
    color: rgb(var(--muted));
    margin: 0;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex-wrap: wrap;
}

/* ─── 4-Card Stats Grid ────────────────────────────────────── */
.ug-stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1.15rem;
}

.ug-stat-card {
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

.ug-stat-card:hover {
    transform: translateY(-2px);
    border-color: rgba(var(--brand), 0.3);
}

.ug-stat-icon {
    width: 48px;
    height: 48px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.ug-stat-icon--emerald {
    background: rgba(16, 185, 129, 0.12);
    color: #10b981;
}

.ug-stat-icon--brand {
    background: rgba(var(--brand), 0.12);
    color: rgb(var(--brand));
}

.ug-stat-icon--amber {
    background: rgba(245, 158, 11, 0.12);
    color: #f59e0b;
}

.ug-stat-icon--purple {
    background: rgba(168, 85, 247, 0.12);
    color: #9333ea;
}

.ug-stat-val {
    font-size: clamp(1.35rem, 2.5vw, 1.75rem);
    font-weight: 900;
    line-height: 1.15;
    letter-spacing: -0.02em;
    color: rgb(var(--text));
    display: block;
}

.ug-stat-label {
    font-size: 0.78rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: rgb(var(--muted));
    margin-top: 0.2rem;
    display: block;
}

/* ─── Crowd Flow Status Banner ─────────────────────────────── */
.ug-flow-banner {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: rgb(var(--card));
    border: 1px solid rgba(var(--muted), 0.14);
    border-radius: 18px;
    padding: 1.1rem 1.35rem;
    flex-wrap: wrap;
    gap: 1rem;
    box-shadow: 0 4px 18px rgba(0, 0, 0, 0.03);
}

.ug-flow-left {
    display: flex;
    align-items: center;
    gap: 0.85rem;
    min-width: 0;
    flex: 1;
}

.ug-flow-icon {
    font-size: 1.75rem;
    flex-shrink: 0;
}

.ug-flow-title {
    font-size: 0.95rem;
    font-weight: 800;
    color: rgb(var(--text));
    display: block;
    margin-bottom: 0.15rem;
}

.ug-flow-desc {
    font-size: 0.84rem;
    color: rgb(var(--muted));
}

.ug-flow-badges {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.ug-flow-tag {
    font-size: 0.78rem;
    font-weight: 800;
    padding: 0.3rem 0.75rem;
    border-radius: 8px;
}

.ug-flow-tag--active {
    background: rgba(16, 185, 129, 0.12);
    color: #059669;
    border: 1px solid rgba(16, 185, 129, 0.25);
}

.ug-flow-tag--routing {
    background: rgba(99, 102, 241, 0.12);
    color: #6366f1;
    border: 1px solid rgba(99, 102, 241, 0.25);
}

/* ─── Main Content Dual Column ─────────────────────────────── */
.ug-main-layout {
    display: grid;
    grid-template-columns: 1.15fr 0.85fr;
    gap: 1.5rem;
    align-items: start;
}

.ug-card {
    background: rgb(var(--card));
    border: 1px solid rgba(var(--muted), 0.14);
    border-radius: 22px;
    padding: clamp(1.25rem, 3vw, 1.75rem);
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
}

.ug-card-head {
    margin-bottom: 1.25rem;
    padding-bottom: 0.75rem;
    border-bottom: 1px solid rgba(var(--muted), 0.12);
}

.ug-card-title {
    font-size: 1.15rem;
    font-weight: 800;
    color: rgb(var(--text));
    margin: 0 0 0.25rem;
    letter-spacing: -0.015em;
    display: flex;
    align-items: center;
    gap: 0.45rem;
}

.ug-card-sub {
    font-size: 0.84rem;
    color: rgb(var(--muted));
    margin: 0;
}

/* Search Bar */
.ug-search-form {
    display: flex;
    gap: 0.5rem;
    margin-bottom: 1.25rem;
}

.ug-search-input {
    flex: 1;
    height: 46px;
    padding: 0 1rem;
    border-radius: 12px;
    border: 1.5px solid rgba(var(--muted), 0.22);
    background: rgb(var(--bg));
    color: rgb(var(--text));
    font-size: 16px; /* Prevents iOS auto-zoom */
    outline: none;
    box-sizing: border-box;
}

.ug-search-input:focus {
    border-color: rgb(var(--brand));
    box-shadow: 0 0 0 3px rgba(var(--brand), 0.14);
}

.ug-search-btn {
    padding: 0 1.25rem;
    height: 46px;
    border-radius: 12px;
    background: rgb(var(--brand));
    color: #ffffff !important;
    font-weight: 800;
    font-size: 0.9rem;
    border: none;
    cursor: pointer;
    box-shadow: 0 4px 14px rgba(var(--brand), 0.3);
    white-space: nowrap;
}

.ug-clear-btn {
    padding: 0 1rem;
    height: 46px;
    border-radius: 12px;
    background: rgba(var(--muted), 0.1);
    color: rgb(var(--text));
    font-weight: 700;
    font-size: 0.88rem;
    border: 1px solid rgba(var(--muted), 0.2);
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    white-space: nowrap;
}

/* Attendee List */
.ug-attendee-list {
    display: flex;
    flex-direction: column;
    gap: 0.65rem;
    max-height: 480px;
    overflow-y: auto;
}

.ug-attendee-item {
    background: rgb(var(--bg));
    border: 1px solid rgba(var(--muted), 0.14);
    border-radius: 14px;
    padding: 0.85rem 1.15rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 0.75rem;
    flex-wrap: wrap;
    transition: border-color var(--duration-fast, 0.15s) ease-out;
}

.ug-attendee-item:hover {
    border-color: rgba(var(--brand), 0.3);
}

.ug-seated-pill {
    font-size: 0.7rem;
    font-weight: 800;
    padding: 0.15rem 0.5rem;
    border-radius: 6px;
}

.ug-seated-pill--yes {
    background: rgba(16, 185, 129, 0.12);
    color: #059669;
}

.ug-seated-pill--no {
    background: rgba(245, 158, 11, 0.12);
    color: #d97706;
}

.ug-row-badge {
    font-size: 0.8rem;
    font-weight: 800;
    background: rgba(99, 102, 241, 0.12);
    color: #4f46e5;
    border: 1px solid rgba(99, 102, 241, 0.25);
    padding: 0.3rem 0.75rem;
    border-radius: 10px;
    white-space: nowrap;
}

/* Section Blocks */
.ug-sections-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.ug-section-block {
    background: rgb(var(--bg));
    border: 1px solid rgba(var(--muted), 0.14);
    border-radius: 14px;
    padding: 1rem 1.15rem;
}

.ug-section-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.45rem;
    flex-wrap: wrap;
    gap: 0.25rem;
}

.ug-progress-wrap {
    width: 100%;
    height: 8px;
    background: rgba(var(--muted), 0.12);
    border-radius: 10px;
    overflow: hidden;
}

.ug-progress-bar {
    height: 100%;
    background: linear-gradient(90deg, #10b981, #6366f1);
    border-radius: 10px;
    transition: width 0.4s ease;
}

/* Floor Protocol */
.ug-protocol-card {
    background: linear-gradient(180deg, rgb(var(--card)) 0%, rgba(99, 102, 241, 0.03) 100%);
    border: 1px solid rgba(99, 102, 241, 0.2);
    border-radius: 20px;
    padding: 1.25rem 1.35rem;
}

.ug-protocol-list {
    margin: 0;
    padding-left: 1.25rem;
    font-size: 0.84rem;
    color: rgb(var(--muted));
    line-height: 1.6;
    display: flex;
    flex-direction: column;
    gap: 0.4rem;
}

.ug-protocol-list strong {
    color: rgb(var(--text));
}

/* ═════════════════════════════════════════════════════════════
   Responsive Breakpoints
   ═════════════════════════════════════════════════════════════ */

/* Tablet (max 1024px) */
@media (max-width: 1024px) {
    .ug-stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    .ug-main-layout {
        grid-template-columns: 1fr;
    }
}

/* Mobile (max 680px) */
@media (max-width: 680px) {
    .ug-hero {
        flex-direction: column;
        align-items: stretch;
        padding: 1.25rem 1.15rem;
    }

    .ug-stats-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 0.75rem;
    }

    .ug-stat-card {
        padding: 1rem;
        flex-direction: column;
        align-items: flex-start;
        gap: 0.65rem;
    }

    .ug-stat-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
    }

    .ug-stat-val {
        font-size: 1.25rem;
    }

    .ug-stat-label {
        font-size: 0.72rem;
    }

    .ug-flow-banner {
        flex-direction: column;
        align-items: flex-start;
        padding: 1rem;
    }

    .ug-search-form {
        flex-direction: column;
    }

    .ug-search-btn,
    .ug-clear-btn {
        width: 100%;
        justify-content: center;
    }

    .ug-card {
        padding: 1.25rem 1rem;
        border-radius: 18px;
    }
}

/* Very Small Mobile (< 400px) */
@media (max-width: 400px) {
    .ug-stats-grid {
        grid-template-columns: 1fr;
    }
}
</style>
@endsection

@section('content')
<div class="ug-wrapper">

    <!-- ═══ Hero Header ═══ -->
    <header class="ug-hero">
        <div class="ug-hero-text">
            <div class="ug-badge-wrap">
                <span class="ug-pill-desk">👥 Workstation — Usher / Floor Guide</span>
                <span class="ug-pill-live">Floor Active</span>
            </div>
            <h1 class="ug-title"><?= e($event['name'] ?? 'Event Workspace') ?></h1>
            <p class="ug-meta">
                <?php if ($eventTime): ?>
                    <span>📅 <?= date('D, M d, Y • g:i A', $eventTime) ?></span>
                    <span>&bull;</span>
                <?php endif; ?>
                <span>📍 <?= e($event['location'] ?? 'Venue Seating Floor') ?></span>
                <span>&bull;</span>
                <span>Real-Time Guest Direction</span>
            </p>
        </div>
    </header>

    <!-- ═══ 4-Card Stats Grid ═══ -->
    <section class="ug-stats-grid">
        <div class="ug-stat-card">
            <div class="ug-stat-icon ug-stat-icon--emerald">
                <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                    <polyline points="16 11 18 13 22 9"></polyline>
                </svg>
            </div>
            <div>
                <span class="ug-stat-val" style="color: #059669;"><?= number_format($total_seated) ?></span>
                <span class="ug-stat-label">Seated / Arrived</span>
            </div>
        </div>

        <div class="ug-stat-card">
            <div class="ug-stat-icon ug-stat-icon--brand">
                <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                </svg>
            </div>
            <div>
                <span class="ug-stat-val"><?= number_format($total_expected) ?></span>
                <span class="ug-stat-label">Total Expected Guests</span>
            </div>
        </div>

        <div class="ug-stat-card">
            <div class="ug-stat-icon ug-stat-icon--amber">
                <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="8" x2="12" y2="12"></line>
                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                </svg>
            </div>
            <div>
                <span class="ug-stat-val" style="color: #d97706;"><?= number_format($remaining) ?></span>
                <span class="ug-stat-label">Awaiting Arrival</span>
            </div>
        </div>

        <div class="ug-stat-card">
            <div class="ug-stat-icon ug-stat-icon--purple">
                <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="20" x2="18" y2="10"></line>
                    <line x1="12" y1="20" x2="12" y2="4"></line>
                    <line x1="6" y1="20" x2="6" y2="14"></line>
                </svg>
            </div>
            <div>
                <span class="ug-stat-val" style="color: #9333ea;"><?= $fillRate ?>%</span>
                <span class="ug-stat-label">Floor Occupancy Rate</span>
            </div>
        </div>
    </section>

    <!-- ═══ Crowd Flow Status Banner ═══ -->
    <div class="ug-flow-banner">
        <div class="ug-flow-left">
            <span class="ug-flow-icon">🚦</span>
            <div>
                <strong class="ug-flow-title">Gate Ingress &amp; Crowd Flow Status</strong>
                <span class="ug-flow-desc">
                    <?= $fillRate >= 85 ? '🔴 High Traffic / Near Capacity — Guide guests to remaining rear/balcony rows.' : ($fillRate >= 50 ? '🟡 Steady Ingress Flow — Direct guests to designated tier rows.' : '🟢 Smooth Flow — Main floor and VIP front aisles open.') ?>
                </span>
            </div>
        </div>
        <div class="ug-flow-badges">
            <span class="ug-flow-tag ug-flow-tag--active">✓ Aisle Marshals Active</span>
            <span class="ug-flow-tag ug-flow-tag--routing">⚡ Fast Row Routing</span>
        </div>
    </div>

    <!-- ═══ Main Dual Column Layout ═══ -->
    <div class="ug-main-layout">

        <!-- Left: Attendee Seating Locator -->
        <section class="ug-card">
            <div class="ug-card-head">
                <h2 class="ug-card-title">
                    <span>🔎</span> Guest Seating &amp; Row Locator
                </h2>
                <p class="ug-card-sub">Look up attendee by name or ticket code to find their row and section</p>
            </div>

            <!-- Search Form -->
            <form method="GET" action="{{ route('teams/usher-guide') }}" class="ug-search-form">
                <input type="text" name="q" value="<?= e($search ?? '') ?>" placeholder="Type attendee name, email, or ticket code..." class="ug-search-input" />
                <button type="submit" class="ug-search-btn">Find Row</button>
                <?php if (!empty($search)): ?>
                    <a href="{{ route('teams/usher-guide') }}" class="ug-clear-btn">Clear</a>
                <?php endif; ?>
            </form>

            <!-- Results List -->
            <div class="ug-attendee-list">
                <?php if (empty($attendees)): ?>
                    <div style="text-align:center; padding: 2.5rem 1rem; color: rgb(var(--muted));">
                        <span style="font-size: 2rem; display: block; margin-bottom: 0.35rem;">👤</span>
                        <p style="margin: 0; font-weight: 700; color: rgb(var(--text));">No matching attendees found.</p>
                        <span style="font-size: 0.8rem;">Enter a name or ticket code above to direct guests to their seating rows.</span>
                    </div>
                <?php else: ?>
                    <?php foreach ($attendees as $att): ?>
                        <div class="ug-attendee-item">
                            <div style="flex: 1; min-width: 0;">
                                <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                                    <strong style="color: rgb(var(--text)); font-size: 0.95rem;"><?= e($att['attendee_name']) ?></strong>
                                    <?php if ($att['checked_in']): ?>
                                        <span class="ug-seated-pill ug-seated-pill--yes">✓ Seated</span>
                                    <?php else: ?>
                                        <span class="ug-seated-pill ug-seated-pill--no">○ Not Checked In</span>
                                    <?php endif; ?>
                                </div>
                                <div style="font-size: 0.78rem; color: rgb(var(--muted)); margin-top: 0.2rem;">
                                    <code style="font-family: monospace;"><?= e($att['ticket_code']) ?></code> &bull;
                                    <span style="font-weight: 700; color: rgb(var(--brand));"><?= e($att['ticket_type']) ?></span>
                                </div>
                            </div>
                            <div>
                                <span class="ug-row-badge">
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

        <!-- Right: Seating Allocations & Protocol -->
        <div style="display: flex; flex-direction: column; gap: 1.25rem;">
            
            <section class="ug-card">
                <div class="ug-card-head">
                    <h2 class="ug-card-title">
                        <span>🗺️</span> Seating Row Allocations
                    </h2>
                    <p class="ug-card-sub">Venue seating blocks &amp; real-time occupancy rates</p>
                </div>

                <div class="ug-sections-list">
                    <?php if (empty($sections)): ?>
                        <p style="color: rgb(var(--muted)); text-align: center; padding: 1.5rem 0;">No tier sections configured.</p>
                    <?php else: ?>
                        <?php foreach ($sections as $sec): ?>
                            <div class="ug-section-block">
                                <div class="ug-section-meta">
                                    <div>
                                        <strong style="font-size: 0.92rem; color: rgb(var(--text));"><?= e($sec['section']) ?></strong>
                                        <span style="font-size: 0.78rem; color: rgb(var(--muted)); display: block;"><?= e($sec['rows']) ?> &bull; <?= e($sec['tier_name']) ?></span>
                                    </div>
                                    <div style="text-align: right;">
                                        <strong style="font-size: 0.92rem; color: #059669;"><?= $sec['checked_in'] ?></strong>
                                        <span style="font-size: 0.78rem; color: rgb(var(--muted));">/ <?= $sec['sold'] ?> seated (<?= $sec['occupancy_pct'] ?>%)</span>
                                    </div>
                                </div>
                                <div class="ug-progress-wrap">
                                    <div class="ug-progress-bar" style="width: <?= $sec['occupancy_pct'] ?>%;"></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </section>

            <!-- Usher Floor Protocol Quick Reference -->
            <section class="ug-protocol-card">
                <h3 style="margin: 0 0 0.65rem; font-size: 0.95rem; font-weight: 800; color: rgb(var(--text));">📋 Floor Guide Quick Protocol</h3>
                <ul class="ug-protocol-list">
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
