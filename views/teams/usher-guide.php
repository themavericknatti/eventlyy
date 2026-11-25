<?php

/**
 * Team Usher & Floor Guide Workstation Page
 *
 * Workstation task view for Ushers and Floor Guides to manage
 * real seating rows, VIP areas, guest directions, duo/group seating, and crowd flow.
 *
 * @package Eventlyy
 * @subpackage Views/Teams
 * @author Celio Natti
 * @version 3.1.0
 */

$eventTime = !empty($event['date']) ? strtotime($event['date']) : null;
$assignments = $assignments ?? [];
$sections = $sections ?? [];
$attendees = $attendees ?? [];
$unseatedTickets = $unseated_tickets ?? [];
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
    background: rgba(11, 115, 102, 0.12);
    color: #0B7366;
}

.ug-stat-icon--amber {
    background: rgba(245, 158, 11, 0.12);
    color: #f59e0b;
}

.ug-stat-icon--purple {
    background: rgba(168, 85, 247, 0.12);
    color: #a855f7;
}

.ug-stat-val {
    display: block;
    font-size: 1.55rem;
    font-weight: 900;
    line-height: 1.15;
    color: rgb(var(--text));
    font-family: var(--font-heading);
}

.ug-stat-label {
    display: block;
    font-size: 0.78rem;
    font-weight: 700;
    color: rgb(var(--muted));
    margin-top: 0.2rem;
    text-transform: uppercase;
    letter-spacing: 0.03em;
}

/* ─── Crowd Flow Status Banner ─────────────────────────────── */
.ug-flow-banner {
    background: rgb(var(--card));
    border: 1px solid rgba(var(--muted), 0.14);
    border-radius: 16px;
    padding: 1rem 1.35rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
    flex-wrap: wrap;
}

.ug-flow-left {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.ug-flow-icon {
    font-size: 1.4rem;
}

.ug-flow-title {
    font-size: 0.92rem;
    font-weight: 800;
    color: rgb(var(--text));
    display: block;
}

.ug-flow-desc {
    font-size: 0.8rem;
    color: rgb(var(--muted));
    display: block;
}

.ug-flow-badges {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.ug-flow-tag {
    font-size: 0.74rem;
    font-weight: 800;
    padding: 0.25rem 0.65rem;
    border-radius: 999px;
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
}

.ug-flow-tag--active {
    background: rgba(16, 185, 129, 0.12);
    color: #059669;
    border: 1px solid rgba(16, 185, 129, 0.25);
}

.ug-flow-tag--routing {
    background: rgba(99, 102, 241, 0.12);
    color: #4f46e5;
    border: 1px solid rgba(99, 102, 241, 0.25);
}

/* ─── Main Dual Column Layout ──────────────────────────────── */
.ug-main-layout {
    display: grid;
    grid-template-columns: 1.35fr 1fr;
    gap: 1.5rem;
    align-items: start;
}

.ug-card {
    background: rgb(var(--card));
    border: 1px solid rgba(var(--muted), 0.14);
    border-radius: 20px;
    padding: 1.35rem 1.5rem;
    box-shadow: 0 4px 18px rgba(0, 0, 0, 0.03);
}

.ug-card-head {
    margin-bottom: 1.15rem;
}

.ug-card-title {
    font-size: 1.12rem;
    font-weight: 800;
    color: rgb(var(--text));
    margin: 0 0 0.25rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.ug-card-sub {
    font-size: 0.82rem;
    color: rgb(var(--muted));
    margin: 0;
}

/* Search Form & Filter Tabs */
.ug-search-wrap {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    margin-bottom: 1rem;
}

.ug-search-form {
    display: flex;
    gap: 0.5rem;
}

.ug-search-input {
    flex: 1;
    min-width: 0;
    background: rgb(var(--bg));
    border: 1px solid rgba(var(--muted), 0.22);
    border-radius: 12px;
    padding: 0.65rem 1rem;
    font-size: 0.88rem;
    color: rgb(var(--text));
    outline: none;
    transition: border-color var(--duration-fast, 0.15s) ease-out;
}

.ug-search-input:focus {
    border-color: #0B7366;
    box-shadow: 0 0 0 3px rgba(11, 115, 102, 0.15);
}

.ug-search-btn {
    background: #0B7366;
    color: #ffffff;
    border: none;
    border-radius: 12px;
    padding: 0.65rem 1.15rem;
    font-weight: 800;
    font-size: 0.86rem;
    cursor: pointer;
    white-space: nowrap;
    transition: background 0.15s;
}

.ug-search-btn:hover {
    background: #095c52;
}

.ug-filter-tabs {
    display: flex;
    gap: 0.4rem;
    flex-wrap: wrap;
}

.ug-filter-tab {
    padding: 0.3rem 0.7rem;
    border-radius: 999px;
    font-size: 0.74rem;
    font-weight: 700;
    background: rgba(var(--muted), 0.1);
    color: rgb(var(--muted));
    border: 1px solid transparent;
    cursor: pointer;
    transition: all 0.15s ease;
}

.ug-filter-tab:hover {
    color: rgb(var(--text));
    background: rgba(var(--muted), 0.18);
}

.ug-filter-tab.active {
    background: rgba(11, 115, 102, 0.15);
    color: #0B7366;
    border-color: rgba(11, 115, 102, 0.3);
    font-weight: 800;
}

/* Attendee List */
.ug-attendee-list {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    max-height: 540px;
    overflow-y: auto;
    padding-right: 0.25rem;
}

.ug-attendee-item {
    background: rgb(var(--bg));
    border: 1px solid rgba(var(--muted), 0.14);
    border-radius: 14px;
    padding: 0.9rem 1.15rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 0.85rem;
    flex-wrap: wrap;
    transition: border-color var(--duration-fast, 0.15s) ease-out, transform 0.15s ease-out;
}

.ug-attendee-item:hover {
    border-color: rgba(11, 115, 102, 0.35);
    transform: translateY(-1px);
}

.ug-attendee-item.is-seated {
    border-left: 3.5px solid #10b981;
}

.ug-attendee-item.is-unseated {
    border-left: 3.5px solid #f59e0b;
}

.ug-seated-pill {
    font-size: 0.7rem;
    font-weight: 800;
    padding: 0.18rem 0.55rem;
    border-radius: 6px;
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
}

.ug-seated-pill--yes {
    background: rgba(16, 185, 129, 0.14);
    color: #059669;
}

.ug-seated-pill--no {
    background: rgba(245, 158, 11, 0.14);
    color: #d97706;
}

.ug-kind-pill {
    font-size: 0.68rem;
    font-weight: 800;
    padding: 0.18rem 0.5rem;
    border-radius: 6px;
    text-transform: uppercase;
    letter-spacing: 0.03em;
}

.ug-kind--standard { background: rgba(100, 116, 139, 0.12); color: #475569; }
.ug-kind--duo { background: rgba(236, 72, 153, 0.12); color: #db2777; }
.ug-kind--group { background: rgba(249, 115, 22, 0.12); color: #ea580c; }
.ug-kind--seated { background: rgba(16, 185, 129, 0.14); color: #059669; }

.ug-role-pill {
    font-size: 0.68rem;
    font-weight: 800;
    padding: 0.15rem 0.45rem;
    border-radius: 6px;
    background: rgba(14, 165, 233, 0.12);
    color: #0284c7;
}

.ug-seat-badge {
    font-size: 0.84rem;
    font-weight: 800;
    background: rgba(16, 185, 129, 0.12);
    color: #065f46;
    border: 1px solid rgba(16, 185, 129, 0.3);
    padding: 0.35rem 0.85rem;
    border-radius: 10px;
    white-space: nowrap;
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
}

.ug-open-floor-badge {
    font-size: 0.8rem;
    font-weight: 700;
    background: rgba(var(--muted), 0.12);
    color: rgb(var(--muted));
    border: 1px solid rgba(var(--muted), 0.2);
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
    border-radius: 16px;
    padding: 1.1rem 1.25rem;
    transition: border-color var(--duration-fast, 0.15s) ease;
}

.ug-section-block:hover {
    border-color: rgba(var(--brand), 0.3);
}

.ug-section-meta {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 0.55rem;
    flex-wrap: wrap;
    gap: 0.4rem;
}

.ug-progress-wrap {
    width: 100%;
    height: 8px;
    background: rgba(var(--muted), 0.12);
    border-radius: 10px;
    overflow: hidden;
    margin-bottom: 0.65rem;
}

.ug-progress-bar {
    height: 100%;
    background: linear-gradient(90deg, #10b981, #0B7366);
    border-radius: 10px;
    transition: width 0.4s ease;
}

/* Interactive Mini Seat Map Grid */
.ug-seat-grid-toggle {
    background: none;
    border: none;
    color: #0B7366;
    font-size: 0.78rem;
    font-weight: 800;
    cursor: pointer;
    padding: 0;
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
}

.ug-seat-grid-toggle:hover {
    text-decoration: underline;
}

.ug-seat-grid-box {
    margin-top: 0.65rem;
    padding-top: 0.65rem;
    border-top: 1px dashed rgba(var(--muted), 0.2);
}

.ug-seat-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(36px, 1fr));
    gap: 0.35rem;
    max-height: 180px;
    overflow-y: auto;
    padding: 0.25rem 0.1rem;
}

.ug-seat-dot {
    aspect-ratio: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.68rem;
    font-weight: 800;
    border-radius: 6px;
    border: 1px solid transparent;
    cursor: pointer;
    transition: transform 0.15s ease, box-shadow 0.15s ease;
    position: relative;
}

.ug-seat-dot:hover {
    transform: scale(1.15);
    z-index: 2;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.14);
}

.ug-seat-dot--checked {
    background: rgba(16, 185, 129, 0.2);
    color: #065f46;
    border-color: #10b981;
}

.ug-seat-dot--reserved {
    background: rgba(245, 158, 11, 0.18);
    color: #92400e;
    border-color: #f59e0b;
}

.ug-seat-dot--vip {
    background: rgba(168, 85, 247, 0.22);
    color: #7e22ce;
    border-color: #9333ea;
}

.ug-btn-vip {
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    padding: 0.65rem 1.15rem;
    border-radius: 12px;
    background: linear-gradient(135deg, #9333ea, #7e22ce);
    color: #ffffff;
    font-size: 0.85rem;
    font-weight: 800;
    border: none;
    cursor: pointer;
    box-shadow: 0 6px 18px rgba(147, 51, 234, 0.25);
    transition: transform 0.15s ease, box-shadow 0.15s ease;
}
.ug-btn-vip:hover {
    transform: translateY(-1px);
    box-shadow: 0 8px 22px rgba(147, 51, 234, 0.35);
}

.ug-btn-seat-action {
    padding: 0.32rem 0.65rem;
    border-radius: 8px;
    border: 1px solid rgba(11, 115, 102, 0.25);
    background: rgba(11, 115, 102, 0.08);
    color: #0B7366;
    font-size: 0.74rem;
    font-weight: 800;
    cursor: pointer;
    transition: background 0.15s ease;
}
.ug-btn-seat-action:hover {
    background: rgba(11, 115, 102, 0.18);
}

.ug-seat-dot--available {
    background: rgba(var(--muted), 0.08);
    color: rgb(var(--muted));
    border-color: rgba(var(--muted), 0.2);
}

.ug-seat-legend {
    display: flex;
    gap: 0.75rem;
    margin-top: 0.45rem;
    flex-wrap: wrap;
    font-size: 0.72rem;
    color: rgb(var(--muted));
}

.ug-legend-item {
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
}

.ug-legend-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
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

@media (max-width: 1024px) {
    .ug-stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    .ug-main-layout {
        grid-template-columns: 1fr;
    }
}

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

    .ug-flow-banner {
        flex-direction: column;
        align-items: flex-start;
        padding: 1rem;
    }

    .ug-search-form {
        flex-direction: column;
    }

    .ug-search-btn {
        width: 100%;
        justify-content: center;
    }

    .ug-card {
        padding: 1.25rem 1rem;
        border-radius: 18px;
    }
}

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
                <span class="ug-pill-desk">👥 Workstation — Usher &amp; Floor Guide</span>
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
                <span>Real-Time Seating &amp; Guest Direction</span>
            </p>
        </div>
        <div style="display:flex; align-items:center; gap:0.65rem; flex-wrap:wrap; position:relative; z-index:2;">
            <button type="button" class="ug-btn-vip" onclick="openQuickVipModal()">
                <span>⭐</span> Reserve VIP / Special Guest Seat
            </button>
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
                <span class="ug-stat-val" id="ug-stat-seated" style="color: #059669;"><?= number_format($total_seated) ?></span>
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
                <span class="ug-stat-val" id="ug-stat-expected"><?= number_format($total_expected) ?></span>
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
                <span class="ug-stat-val" id="ug-stat-remaining" style="color: #d97706;"><?= number_format($remaining) ?></span>
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
                <span class="ug-stat-val" id="ug-stat-fill" style="color: #9333ea;"><?= $fillRate ?>%</span>
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
                <span class="ug-flow-desc" id="ug-flow-desc">
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
                <p class="ug-card-sub">Instant search by attendee name, email, or pass code to guide guests to their exact section &amp; seat</p>
            </div>

            <!-- Search Form & Quick Filters -->
            <div class="ug-search-wrap">
                <form id="ug-search-form" onsubmit="handleUsherSearch(event)" class="ug-search-form">
                    <input type="text" id="ug-search-input" value="<?= e($search ?? '') ?>" placeholder="Search attendee name, ticket code, seat number..." class="ug-search-input" autocomplete="off" />
                    <button type="submit" class="ug-search-btn">Find Guest</button>
                </form>

                <div class="ug-filter-tabs">
                    <button type="button" class="ug-filter-tab active" onclick="setUsherFilter('all', this)">All (<?= count($attendees) ?>)</button>
                    <button type="button" class="ug-filter-tab" onclick="setUsherFilter('seated', this)">✓ Seated</button>
                    <button type="button" class="ug-filter-tab" onclick="setUsherFilter('unseated', this)">⏳ Pending Arrival</button>
                    <button type="button" class="ug-filter-tab" onclick="setUsherFilter('reserved_seat', this)">📍 Assigned Seats</button>
                    <button type="button" class="ug-filter-tab" onclick="setUsherFilter('vip_guest', this)">⭐ VIP / Special Guests</button>
                    <button type="button" class="ug-filter-tab" onclick="setUsherFilter('duo_group', this)">👥 Duo / Group</button>
                </div>
            </div>

            <!-- Results List -->
            <div class="ug-attendee-list" id="ug-attendees-container">
                <?php if (empty($attendees)): ?>
                    <div style="text-align:center; padding: 2.5rem 1rem; color: rgb(var(--muted));">
                        <span style="font-size: 2rem; display: block; margin-bottom: 0.35rem;">👤</span>
                        <p style="margin: 0; font-weight: 700; color: rgb(var(--text));">No matching attendees found.</p>
                        <span style="font-size: 0.8rem;">Enter a name or ticket code above to locate attendee seating.</span>
                    </div>
                <?php else: ?>
                    <?php foreach ($attendees as $att): 
                        $isSeated = !empty($att['checked_in']);
                        $hasAssignedSeat = !empty($att['assigned_seat']);
                        $tKind = $att['ticket_kind'] ?? 'standard';
                        $tRole = $att['attendee_role'] ?? 'primary';

                        $seatLabel = '';
                        if (!empty($att['section_name'])) $seatLabel .= "Sec " . e($att['section_name']) . " • ";
                        if (!empty($att['row_name'])) $seatLabel .= "Row " . e($att['row_name']) . " • ";
                        if (!empty($att['assigned_seat'])) $seatLabel .= "Seat " . e($att['assigned_seat']);
                        $seatLabel = trim($seatLabel, " • ");
                    ?>
                        <div class="ug-attendee-item <?= $isSeated ? 'is-seated' : 'is-unseated' ?>"
                             data-seated="<?= $isSeated ? '1' : '0' ?>"
                             data-has-seat="<?= $hasAssignedSeat ? '1' : '0' ?>"
                             data-kind="<?= e($tKind) ?>"
                             data-role="<?= e($tRole) ?>">
                            <div style="flex: 1; min-width: 0;">
                                <div style="display: flex; align-items: center; gap: 0.45rem; flex-wrap: wrap;">
                                    <strong style="color: rgb(var(--text)); font-size: 0.95rem;"><?= e($att['attendee_name']) ?></strong>
                                    
                                    <?php if ($isSeated): ?>
                                        <span class="ug-seated-pill ug-seated-pill--yes">✓ In Venue</span>
                                    <?php else: ?>
                                        <span class="ug-seated-pill ug-seated-pill--no">⏳ Pending</span>
                                    <?php endif; ?>

                                    <!-- Ticket Kind Badge -->
                                    <span class="ug-kind-pill ug-kind--<?= e($tKind) ?>">
                                        <?= e(strtoupper($tKind)) ?>
                                    </span>

                                    <!-- Attendee Role if duo, group, or VIP -->
                                    <?php if ($tRole === 'vip_guest'): ?>
                                        <span class="ug-role-pill" style="background:rgba(168,85,247,0.16); color:#7e22ce; border-color:rgba(168,85,247,0.35);">⭐ Special Guest / VIP</span>
                                    <?php elseif ($tRole === 'plus_one'): ?>
                                        <span class="ug-role-pill">💑 Plus-One</span>
                                    <?php elseif ($tRole === 'group_member'): ?>
                                        <span class="ug-role-pill">👥 Group Guest</span>
                                    <?php endif; ?>
                                </div>
                                <div style="font-size: 0.78rem; color: rgb(var(--muted)); margin-top: 0.25rem;">
                                    <code style="font-family: monospace; font-weight:700; color:rgb(var(--text));">#<?= e($att['ticket_code']) ?></code> &bull;
                                    <span style="font-weight: 700; color: #0B7366;"><?= e($att['ticket_type']) ?></span>
                                    <?php if (!empty($att['attendee_email'])): ?>
                                        &bull; <span><?= e($att['attendee_email']) ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div style="display:flex; align-items:center; gap:0.45rem; flex-wrap:wrap;">
                                <?php if (!empty($seatLabel)): ?>
                                    <span class="ug-seat-badge">
                                        <span>📍</span> <?= $seatLabel ?>
                                    </span>
                                <?php else: ?>
                                    <span class="ug-open-floor-badge">
                                        General Standing / Open Floor
                                    </span>
                                <?php endif; ?>
                                <button type="button" class="ug-btn-seat-action"
                                        onclick="openAssignForTicket('<?= e($att['ticket_code']) ?>', '<?= e($att['attendee_name']) ?>')">
                                    🪑 <?= $hasAssignedSeat ? 'Move' : 'Seat' ?>
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>

        <!-- Right: Real Seating Allocations & Protocol -->
        <div style="display: flex; flex-direction: column; gap: 1.25rem;">
            
            <section class="ug-card">
                <div class="ug-card-head">
                    <h2 class="ug-card-title">
                        <span>🗺️</span> Section &amp; Seat Matrix
                    </h2>
                    <p class="ug-card-sub">Real-time floor sections &amp; seat occupancy status</p>
                </div>

                <div class="ug-sections-list" id="ug-sections-container">
                    <?php if (empty($sections)): ?>
                        <p style="color: rgb(var(--muted)); text-align: center; padding: 1.5rem 0;">No venue sections configured.</p>
                    <?php else: ?>
                        <?php foreach ($sections as $idx => $sec): 
                            $isSeatedTier = !empty($sec['is_seated']);
                            $seatMap = $sec['seat_map'] ?? [];
                        ?>
                            <div class="ug-section-block">
                                <div class="ug-section-meta">
                                    <div>
                                        <div style="display:flex; align-items:center; gap:0.4rem; flex-wrap:wrap;">
                                            <strong style="font-size: 0.95rem; color: rgb(var(--text));"><?= e($sec['section']) ?></strong>
                                            <?php if ($isSeatedTier): ?>
                                                <span class="ug-kind-pill ug-kind--seated">Reserved Seats</span>
                                            <?php else: ?>
                                                <span class="ug-kind-pill ug-kind--standard"><?= e(strtoupper($sec['ticket_kind'] ?? 'STANDARD')) ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <span style="font-size: 0.78rem; color: rgb(var(--muted)); display: block; margin-top: 0.2rem;">
                                            <?= e($sec['rows']) ?> &bull; <?= e($sec['tier_name']) ?>
                                        </span>
                                    </div>
                                    <div style="text-align: right;">
                                        <strong style="font-size: 0.92rem; color: #059669;"><?= $sec['checked_in'] ?></strong>
                                        <span style="font-size: 0.78rem; color: rgb(var(--muted));">/ <?= $sec['sold'] ?> seated (<?= $sec['occupancy_pct'] ?>%)</span>
                                    </div>
                                </div>
                                <div class="ug-progress-wrap">
                                    <div class="ug-progress-bar" style="width: <?= $sec['occupancy_pct'] ?>%;"></div>
                                </div>

                                <?php if ($isSeatedTier && !empty($seatMap)): ?>
                                    <button type="button" class="ug-seat-grid-toggle" onclick="toggleSeatGrid('sec-grid-<?= $idx ?>')">
                                        <span>💺 View &amp; Manage Seat Map (<?= count($seatMap) ?> seats)</span> &darr;
                                    </button>
                                    <div id="sec-grid-<?= $idx ?>" class="ug-seat-grid-box" style="display: none;">
                                        <div class="ug-seat-grid">
                                            <?php foreach ($seatMap as $s): 
                                                $dotClass = 'ug-seat-dot--available';
                                                $title = "Seat " . ($s['seat_label'] ?? $s['seat_number']) . ": Available (Click to assign or reserve VIP)";
                                                if ($s['status'] === 'checked_in') {
                                                    $dotClass = 'ug-seat-dot--checked';
                                                    $title = "Seat " . ($s['seat_label'] ?? $s['seat_number']) . ": " . ($s['attendee_name'] ?? 'Occupied') . " (In Venue)";
                                                } elseif ($s['status'] === 'vip_reserved') {
                                                    $dotClass = 'ug-seat-dot--vip';
                                                    $title = "Seat " . ($s['seat_label'] ?? $s['seat_number']) . ": ⭐ VIP / Special Guest — " . ($s['attendee_name'] ?? 'Reserved');
                                                } elseif ($s['status'] === 'reserved') {
                                                    $dotClass = 'ug-seat-dot--reserved';
                                                    $title = "Seat " . ($s['seat_label'] ?? $s['seat_number']) . ": " . ($s['attendee_name'] ?? 'Reserved') . " (Awaiting)";
                                                }
                                            ?>
                                                <div class="ug-seat-dot <?= $dotClass ?>" 
                                                     title="<?= e($title) ?>"
                                                     onclick='openSeatModal(<?= (int)$sec['tier_id'] ?>, <?= json_encode($sec['section']) ?>, <?= json_encode((string)$s['seat_number']) ?>, <?= json_encode($s['status']) ?>, <?= json_encode($s['attendee_name'] ?? '') ?>, <?= json_encode($s['ticket_code'] ?? '') ?>, <?= json_encode($s['attendee_role'] ?? '') ?>)'>
                                                    <?= e($s['seat_number']) ?>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                        <div class="ug-seat-legend">
                                            <div class="ug-legend-item">
                                                <span class="ug-legend-dot" style="background:#10b981;"></span> In Venue
                                            </div>
                                            <div class="ug-legend-item">
                                                <span class="ug-legend-dot" style="background:#f59e0b;"></span> Awaiting
                                            </div>
                                            <div class="ug-legend-item">
                                                <span class="ug-legend-dot" style="background:#9333ea;"></span> VIP / Special Guest
                                            </div>
                                            <div class="ug-legend-item">
                                                <span class="ug-legend-dot" style="background:rgba(var(--muted), 0.4);"></span> Empty (Click to Reserve)
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </section>

            <!-- Usher Floor Protocol Quick Reference -->
            <section class="ug-protocol-card">
                <h3 style="margin: 0 0 0.65rem; font-size: 0.95rem; font-weight: 800; color: rgb(var(--text));">📋 Floor Guide Protocol &amp; Seating Etiquette</h3>
                <ul class="ug-protocol-list">
                    <li><strong>Reserved Seating:</strong> Match guest pass with designated Section, Row, and Seat number before seating.</li>
                    <li><strong>Duo / Couples Passes:</strong> Escort pairs together to their adjacent assigned seats.</li>
                    <li><strong>VIP Areas:</strong> Escort directly to Stage Front Section. Verify priority access badge.</li>
                    <li><strong>Aisle Clearance:</strong> Keep center gangways and emergency fire exits completely unobstructed at all times.</li>
                    <li><strong>Assisted Accessibility:</strong> Escort wheelchair and mobility-assisted guests to ground-level designated bays.</li>
                </ul>
            </section>

        </div>
    </div>
</div>

<!-- ════════════════════════════════════════════════════════════════
     USHER SEAT ASSIGNMENT & VIP / SPECIAL GUEST MODAL
     ════════════════════════════════════════════════════════════════ -->
<div id="modal-usher-seat" class="team-modal-backdrop">
    <div class="team-modal-dialog">
        <div class="team-modal-header">
            <div>
                <h3 class="team-modal-title" id="ug-modal-title">
                    <span>💺</span> Manage Seat &amp; Special Guest
                </h3>
                <p class="team-modal-subtitle" id="ug-modal-subtitle">Assign an attendee or reserve a VIP / Special Guest seat</p>
            </div>
            <button type="button" class="team-modal-close-btn" onclick="closeSeatModal()" aria-label="Close modal">✕</button>
        </div>

        <div id="ug-modal-feedback" style="display:none; padding:0.75rem 1rem; border-radius:12px; font-size:0.84rem; font-weight:700; margin-bottom:1rem;"></div>

        <!-- Occupied Seat Info Panel -->
        <div id="ug-modal-occupied-panel" style="display:none; background:rgba(var(--muted),0.06); border:1px solid rgba(var(--muted),0.18); border-radius:14px; padding:1rem; margin-bottom:1rem;">
            <div style="font-size:0.72rem; font-weight:800; text-transform:uppercase; color:rgb(var(--muted)); margin-bottom:0.35rem;">Current Seat Occupant</div>
            <div style="font-size:1.05rem; font-weight:800; color:rgb(var(--text));" id="ug-occ-name">—</div>
            <div style="font-size:0.8rem; color:rgb(var(--muted)); margin-top:0.2rem;" id="ug-occ-meta">—</div>
            <div style="display:flex; gap:0.6rem; flex-wrap:wrap; margin-top:1rem;">
                <button type="button" id="ug-btn-checkin-seat" onclick="submitSeatAction('checkin_seat')" style="flex:1; padding:0.65rem 1rem; border-radius:10px; border:none; background:#10b981; color:#fff; font-weight:800; cursor:pointer;">
                    ✓ Mark Seated / Arrived
                </button>
                <button type="button" id="ug-btn-release-seat" onclick="submitSeatAction('release_seat')" style="flex:1; padding:0.65rem 1rem; border-radius:10px; border:1px solid rgba(239,68,68,0.3); background:rgba(239,68,68,0.1); color:#ef4444; font-weight:800; cursor:pointer;">
                    🔓 Release Seat
                </button>
            </div>
        </div>

        <!-- Available Seat Assignment / VIP Reservation Form -->
        <div id="ug-modal-assign-panel">
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:0.75rem; margin-bottom:1rem;">
                <div>
                    <label class="team-modal-label">Section / Tier</label>
                    <select id="ug-modal-tier" class="team-modal-select" onchange="syncSeatNumberOptions()">
                        <?php foreach ($sections as $sec): ?>
                            <option value="<?= (int)$sec['tier_id'] ?>" data-seated="<?= !empty($sec['is_seated']) ? '1' : '0' ?>">
                                <?= e($sec['section']) ?> (<?= e($sec['tier_name']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="team-modal-label">Seat Number *</label>
                    <input type="text" id="ug-modal-seat" class="team-modal-input" placeholder="e.g. 12" />
                </div>
            </div>

            <div style="display:flex; gap:0.5rem; margin-bottom:1rem; border-bottom:1px solid rgba(var(--muted),0.14); padding-bottom:0.75rem;">
                <button type="button" id="ug-tab-vip" onclick="switchSeatModalMode('vip')" style="flex:1; padding:0.55rem; border-radius:10px; border:1px solid rgba(147,51,234,0.3); background:rgba(147,51,234,0.12); color:#7e22ce; font-weight:800; font-size:0.82rem; cursor:pointer;">
                    ⭐ Reserve VIP / Special Guest
                </button>
                <button type="button" id="ug-tab-assign" onclick="switchSeatModalMode('assign')" style="flex:1; padding:0.55rem; border-radius:10px; border:1px solid rgba(var(--muted),0.2); background:transparent; color:rgb(var(--text)); font-weight:800; font-size:0.82rem; cursor:pointer;">
                    🪑 Assign Existing Attendee
                </button>
            </div>

            <!-- VIP / Special Guest Fields -->
            <div id="ug-mode-vip">
                <div style="margin-bottom:0.85rem;">
                    <label class="team-modal-label">Special Guest / VIP Full Name *</label>
                    <input type="text" id="ug-vip-name" class="team-modal-input" placeholder="e.g. Hon. Chief Guest / Keynote Speaker" />
                </div>
                <div style="margin-bottom:1rem;">
                    <label class="team-modal-label">Guest Email / Note (Optional)</label>
                    <input type="email" id="ug-vip-email" class="team-modal-input" placeholder="vip@example.com" />
                </div>
                <button type="button" onclick="submitSeatAction('reserve_vip')" style="width:100%; padding:0.8rem; border-radius:12px; border:none; background:linear-gradient(135deg, #9333ea, #7e22ce); color:#fff; font-weight:800; cursor:pointer;">
                    ⭐ Confirm VIP Seat Reservation
                </button>
            </div>

            <!-- Assign Existing Ticket Fields -->
            <div id="ug-mode-assign" style="display:none;">
                <div style="margin-bottom:0.85rem;">
                    <label class="team-modal-label">Select Unseated Attendee</label>
                    <select id="ug-assign-select" class="team-modal-select" onchange="if(this.value) document.getElementById('ug-assign-code').value = this.value;">
                        <option value="">-- Choose an attendee or enter code below --</option>
                        <?php foreach ($unseatedTickets as $ut): ?>
                            <option value="<?= e($ut['ticket_code']) ?>">
                                <?= e($ut['attendee_name']) ?> (#<?= e($ut['ticket_code']) ?> — <?= e($ut['ticket_type']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div style="margin-bottom:1rem;">
                    <label class="team-modal-label">Or Enter Ticket Code *</label>
                    <input type="text" id="ug-assign-code" class="team-modal-input" placeholder="e.g. EV-8F2A91B0" />
                </div>
                <button type="button" onclick="submitSeatAction('assign_ticket')" style="width:100%; padding:0.8rem; border-radius:12px; border:none; background:#0B7366; color:#fff; font-weight:800; cursor:pointer;">
                    🪑 Assign Attendee to Seat
                </button>
            </div>
        </div>
    </div>
</div>

<script>
var searchDebounceTimer = null;
var activeUsherFilter = 'all';

function toggleSeatGrid(gridId) {
    var el = document.getElementById(gridId);
    if (!el) return;
    el.style.display = (el.style.display === 'none' || el.style.display === '') ? 'block' : 'none';
}

function handleUsherSearch(e) {
    if (e) e.preventDefault();
    performUsherSearch();
}

document.getElementById('ug-search-input').addEventListener('input', function() {
    clearTimeout(searchDebounceTimer);
    searchDebounceTimer = setTimeout(performUsherSearch, 280);
});

function performUsherSearch() {
    var query = document.getElementById('ug-search-input').value.trim();
    var container = document.getElementById('ug-attendees-container');

    fetch('<?= route('teams/usher-guide') ?>?ajax=1&q=' + encodeURIComponent(query), {
        headers: { 'Accept': 'application/json' }
    })
    .then(function(res) { return res.json(); })
    .then(function(data) {
        if (!data || !data.attendees) return;

        // Render attendee cards
        if (data.attendees.length === 0) {
            container.innerHTML = '<div style="text-align:center; padding: 2.5rem 1rem; color: rgb(var(--muted));">' +
                '<span style="font-size: 2rem; display: block; margin-bottom: 0.35rem;">👤</span>' +
                '<p style="margin: 0; font-weight: 700; color: rgb(var(--text));">No matching attendees found.</p>' +
                '<span style="font-size: 0.8rem;">Try searching by different name, email, or ticket code.</span>' +
                '</div>';
            return;
        }

        var html = '';
        data.attendees.forEach(function(att) {
            var isSeated = att.checked_in == 1;
            var hasSeat = Boolean(att.assigned_seat);
            var tKind = att.ticket_kind || 'standard';
            var tRole = att.attendee_role || 'primary';

            var seatLabel = '';
            if (att.section_name) seatLabel += 'Sec ' + escapeHtml(att.section_name) + ' • ';
            if (att.row_name) seatLabel += 'Row ' + escapeHtml(att.row_name) + ' • ';
            if (att.assigned_seat) seatLabel += 'Seat ' + escapeHtml(att.assigned_seat);
            seatLabel = seatLabel.replace(/ • $/, '');

            var rolePill = '';
            if (tRole === 'vip_guest') {
                rolePill = '<span class="ug-role-pill" style="background:rgba(168,85,247,0.16); color:#7e22ce; border-color:rgba(168,85,247,0.35);">⭐ Special Guest / VIP</span>';
            } else if (tRole === 'plus_one') {
                rolePill = '<span class="ug-role-pill">💑 Plus-One</span>';
            } else if (tRole === 'group_member') {
                rolePill = '<span class="ug-role-pill">👥 Group Guest</span>';
            }

            var seatBadgeHtml = seatLabel 
                ? '<span class="ug-seat-badge"><span>📍</span> ' + seatLabel + '</span>'
                : '<span class="ug-open-floor-badge">General Standing / Open Floor</span>';

            html += '<div class="ug-attendee-item ' + (isSeated ? 'is-seated' : 'is-unseated') + '" ' +
                    'data-seated="' + (isSeated ? '1' : '0') + '" ' +
                    'data-has-seat="' + (hasSeat ? '1' : '0') + '" ' +
                    'data-kind="' + escapeHtml(tKind) + '" ' +
                    'data-role="' + escapeHtml(tRole) + '">' +
                    '<div style="flex: 1; min-width: 0;">' +
                    '<div style="display: flex; align-items: center; gap: 0.45rem; flex-wrap: wrap;">' +
                    '<strong style="color: rgb(var(--text)); font-size: 0.95rem;">' + escapeHtml(att.attendee_name) + '</strong>' +
                    (isSeated ? '<span class="ug-seated-pill ug-seated-pill--yes">✓ In Venue</span>' : '<span class="ug-seated-pill ug-seated-pill--no">⏳ Pending</span>') +
                    '<span class="ug-kind-pill ug-kind--' + escapeHtml(tKind) + '">' + escapeHtml(tKind.toUpperCase()) + '</span>' +
                    rolePill +
                    '</div>' +
                    '<div style="font-size: 0.78rem; color: rgb(var(--muted)); margin-top: 0.25rem;">' +
                    '<code style="font-family: monospace; font-weight:700; color:rgb(var(--text));">#' + escapeHtml(att.ticket_code) + '</code> &bull; ' +
                    '<span style="font-weight: 700; color: #0B7366;">' + escapeHtml(att.ticket_type) + '</span>' +
                    (att.attendee_email ? ' &bull; <span>' + escapeHtml(att.attendee_email) + '</span>' : '') +
                    '</div>' +
                    '</div>' +
                    '<div style="display:flex; align-items:center; gap:0.45rem; flex-wrap:wrap;">' +
                    seatBadgeHtml +
                    '<button type="button" class="ug-btn-seat-action" onclick="openAssignForTicket(\'' + escapeHtml(att.ticket_code) + '\', \'' + escapeHtml(att.attendee_name).replace(/\'/g, "\\'") + '\')">🪑 ' + (hasSeat ? 'Move' : 'Seat') + '</button>' +
                    '</div>' +
                    '</div>';
        });

        container.innerHTML = html;
        applyUsherFilter();
    })
    .catch(function(err) {
        console.error('Usher search error:', err);
    });
}

function setUsherFilter(filter, btn) {
    activeUsherFilter = filter;
    document.querySelectorAll('.ug-filter-tab').forEach(function(b) {
        b.classList.remove('active');
    });
    if (btn) btn.classList.add('active');
    applyUsherFilter();
}

function applyUsherFilter() {
    var items = document.querySelectorAll('.ug-attendee-item');
    items.forEach(function(item) {
        var isSeated = item.getAttribute('data-seated') === '1';
        var hasSeat = item.getAttribute('data-has-seat') === '1';
        var kind = item.getAttribute('data-kind') || 'standard';
        var role = item.getAttribute('data-role') || 'primary';

        var show = true;
        if (activeUsherFilter === 'seated') {
            show = isSeated;
        } else if (activeUsherFilter === 'unseated') {
            show = !isSeated;
        } else if (activeUsherFilter === 'reserved_seat') {
            show = hasSeat;
        } else if (activeUsherFilter === 'vip_guest') {
            show = (role === 'vip_guest');
        } else if (activeUsherFilter === 'duo_group') {
            show = (kind === 'duo' || kind === 'group' || role === 'plus_one' || role === 'group_member');
        }

        item.style.display = show ? 'flex' : 'none';
    });
}

var usherCsrfToken = <?= json_encode(csrf_token()) ?>;

function openSeatModal(tierId, sectionName, seatNum, status, occupantName, ticketCode, occupantRole) {
    var modal = document.getElementById('modal-usher-seat');
    var fb = document.getElementById('ug-modal-feedback');
    if (fb) fb.style.display = 'none';

    document.getElementById('ug-modal-tier').value = tierId;
    document.getElementById('ug-modal-seat').value = seatNum;

    var occPanel = document.getElementById('ug-modal-occupied-panel');
    var assignPanel = document.getElementById('ug-modal-assign-panel');

    if (status === 'checked_in' || status === 'reserved' || status === 'vip_reserved') {
        occPanel.style.display = 'block';
        assignPanel.style.display = 'none';
        var roleTag = occupantRole === 'vip_guest' ? ' ⭐ (VIP / Special Guest)' : '';
        document.getElementById('ug-occ-name').textContent = (occupantName || 'Guest') + roleTag;
        document.getElementById('ug-occ-meta').textContent = sectionName + ' • Seat ' + seatNum + ' • Pass #' + (ticketCode || '—');
        document.getElementById('ug-btn-checkin-seat').style.display = (status === 'checked_in') ? 'none' : 'inline-block';
    } else {
        occPanel.style.display = 'none';
        assignPanel.style.display = 'block';
        switchSeatModalMode('vip');
    }

    modal.style.display = 'flex';
}

function openQuickVipModal() {
    var modal = document.getElementById('modal-usher-seat');
    var fb = document.getElementById('ug-modal-feedback');
    if (fb) fb.style.display = 'none';
    document.getElementById('ug-modal-occupied-panel').style.display = 'none';
    document.getElementById('ug-modal-assign-panel').style.display = 'block';
    document.getElementById('ug-modal-seat').value = '';
    document.getElementById('ug-vip-name').value = '';
    document.getElementById('ug-vip-email').value = '';
    switchSeatModalMode('vip');
    modal.style.display = 'flex';
}

function openAssignForTicket(ticketCode, attendeeName) {
    var modal = document.getElementById('modal-usher-seat');
    var fb = document.getElementById('ug-modal-feedback');
    if (fb) fb.style.display = 'none';
    document.getElementById('ug-modal-occupied-panel').style.display = 'none';
    document.getElementById('ug-modal-assign-panel').style.display = 'block';
    document.getElementById('ug-modal-seat').value = '';
    document.getElementById('ug-assign-code').value = ticketCode;
    switchSeatModalMode('assign');
    modal.style.display = 'flex';
}

function closeSeatModal() {
    document.getElementById('modal-usher-seat').style.display = 'none';
}

function switchSeatModalMode(mode) {
    var vipDiv = document.getElementById('ug-mode-vip');
    var assignDiv = document.getElementById('ug-mode-assign');
    var tabVip = document.getElementById('ug-tab-vip');
    var tabAssign = document.getElementById('ug-tab-assign');

    if (mode === 'vip') {
        vipDiv.style.display = 'block';
        assignDiv.style.display = 'none';
        tabVip.style.background = 'rgba(147,51,234,0.14)';
        tabVip.style.color = '#7e22ce';
        tabAssign.style.background = 'transparent';
        tabAssign.style.color = 'rgb(var(--text))';
    } else {
        vipDiv.style.display = 'none';
        assignDiv.style.display = 'block';
        tabAssign.style.background = 'rgba(11,115,102,0.14)';
        tabAssign.style.color = '#0B7366';
        tabVip.style.background = 'transparent';
        tabVip.style.color = 'rgb(var(--text))';
    }
}

function submitSeatAction(action) {
    var fb = document.getElementById('ug-modal-feedback');
    var tierId = document.getElementById('ug-modal-tier').value;
    var seatNum = document.getElementById('ug-modal-seat').value.trim();
    var vipName = document.getElementById('ug-vip-name').value.trim();
    var vipEmail = document.getElementById('ug-vip-email').value.trim();
    var ticketCode = document.getElementById('ug-assign-code').value.trim();
    var searchQ = document.getElementById('ug-search-input').value.trim();

    if (!seatNum) {
        fb.style.display = 'block';
        fb.style.background = 'rgba(239,68,68,0.12)';
        fb.style.color = '#ef4444';
        fb.textContent = 'Please enter a seat number.';
        return;
    }

    var body = new FormData();
    body.append('csrf_token', usherCsrfToken);
    body.append('ticket_type_id', tierId);
    body.append('seat_number', seatNum);
    body.append('action', action);
    body.append('guest_name', vipName);
    body.append('guest_email', vipEmail);
    body.append('ticket_code', ticketCode);
    body.append('q', searchQ);

    fetch('<?= route('teams/assign-seat') ?>', {
        method: 'POST',
        headers: { 'Accept': 'application/json' },
        body: body
    })
    .then(function(res) { return res.json(); })
    .then(function(data) {
        fb.style.display = 'block';
        if (data.success) {
            fb.style.background = 'rgba(16,185,129,0.12)';
            fb.style.color = '#059669';
            fb.textContent = data.message || 'Seat updated successfully.';
            if (data.sections) renderSectionsMatrix(data.sections);
            if (data.attendees) performUsherSearch();
            if (typeof data.total_seated !== 'undefined') {
                var elSeated = document.getElementById('ug-stat-seated');
                var elExpected = document.getElementById('ug-stat-expected');
                var elRemaining = document.getElementById('ug-stat-remaining');
                var elFill = document.getElementById('ug-stat-fill');
                if (elSeated) elSeated.textContent = Number(data.total_seated).toLocaleString();
                if (elExpected) elExpected.textContent = Number(data.total_expected).toLocaleString();
                if (elRemaining) elRemaining.textContent = Math.max(0, data.total_expected - data.total_seated).toLocaleString();
                if (elFill) elFill.textContent = (data.total_expected > 0 ? Math.round((data.total_seated / data.total_expected) * 1000) / 10 : 0) + '%';
            }
            setTimeout(closeSeatModal, 900);
        } else {
            fb.style.background = 'rgba(239,68,68,0.12)';
            fb.style.color = '#ef4444';
            fb.textContent = data.message || 'Unable to update seat.';
        }
    })
    .catch(function() {
        fb.style.display = 'block';
        fb.style.background = 'rgba(239,68,68,0.12)';
        fb.style.color = '#ef4444';
        fb.textContent = 'Network error while updating seat.';
    });
}

function renderSectionsMatrix(sections) {
    var container = document.getElementById('ug-sections-container');
    if (!container) return;
    if (!sections || sections.length === 0) {
        container.innerHTML = '<p style="color: rgb(var(--muted)); text-align: center; padding: 1.5rem 0;">No venue sections configured.</p>';
        return;
    }

    var openGrids = {};
    container.querySelectorAll('.ug-seat-grid-box').forEach(function(box) {
        if (box.style.display === 'block') openGrids[box.id] = true;
    });

    var html = '';
    sections.forEach(function(sec, idx) {
        var isSeated = Boolean(sec.is_seated);
        var seatMap = sec.seat_map || [];
        var gridId = 'sec-grid-' + idx;
        var kindPill = isSeated
            ? '<span class="ug-kind-pill ug-kind--seated">Reserved Seats</span>'
            : '<span class="ug-kind-pill ug-kind--standard">' + escapeHtml((sec.ticket_kind || 'STANDARD').toUpperCase()) + '</span>';

        html += '<div class="ug-section-block">' +
            '<div class="ug-section-meta">' +
                '<div>' +
                    '<div style="display:flex; align-items:center; gap:0.4rem; flex-wrap:wrap;">' +
                        '<strong style="font-size: 0.95rem; color: rgb(var(--text));">' + escapeHtml(sec.section) + '</strong>' +
                        kindPill +
                    '</div>' +
                    '<span style="font-size: 0.78rem; color: rgb(var(--muted)); display: block; margin-top: 0.2rem;">' +
                        escapeHtml(sec.rows) + ' &bull; ' + escapeHtml(sec.tier_name) +
                    '</span>' +
                '</div>' +
                '<div style="text-align: right;">' +
                    '<strong style="font-size: 0.92rem; color: #059669;">' + sec.checked_in + '</strong>' +
                    '<span style="font-size: 0.78rem; color: rgb(var(--muted));">/ ' + sec.sold + ' seated (' + sec.occupancy_pct + '%)</span>' +
                '</div>' +
            '</div>' +
            '<div class="ug-progress-wrap"><div class="ug-progress-bar" style="width: ' + sec.occupancy_pct + '%;"></div></div>';

        if (isSeated && seatMap.length > 0) {
            var isOpen = openGrids[gridId] ? 'block' : 'none';
            html += '<button type="button" class="ug-seat-grid-toggle" onclick="toggleSeatGrid(\'' + gridId + '\')">' +
                    '<span>💺 View &amp; Manage Seat Map (' + seatMap.length + ' seats)</span> &darr;</button>' +
                    '<div id="' + gridId + '" class="ug-seat-grid-box" style="display:' + isOpen + ';"><div class="ug-seat-grid">';
            seatMap.forEach(function(s) {
                var dotClass = 'ug-seat-dot--available';
                if (s.status === 'checked_in') dotClass = 'ug-seat-dot--checked';
                else if (s.status === 'vip_reserved') dotClass = 'ug-seat-dot--vip';
                else if (s.status === 'reserved') dotClass = 'ug-seat-dot--reserved';

                html += '<div class="ug-seat-dot ' + dotClass + '" ' +
                        'title="Seat ' + escapeHtml(s.seat_label || s.seat_number) + ': ' + escapeHtml(s.attendee_name || 'Available') + '" ' +
                        'onclick="openSeatModal(' + Number(sec.tier_id) + ', ' + JSON.stringify(sec.section).replace(/"/g, '&quot;') + ', ' + JSON.stringify(String(s.seat_number)).replace(/"/g, '&quot;') + ', ' + JSON.stringify(s.status).replace(/"/g, '&quot;') + ', ' + JSON.stringify(s.attendee_name || '').replace(/"/g, '&quot;') + ', ' + JSON.stringify(s.ticket_code || '').replace(/"/g, '&quot;') + ', ' + JSON.stringify(s.attendee_role || '').replace(/"/g, '&quot;') + ')">' +
                        escapeHtml(s.seat_number) + '</div>';
            });
            html += '</div><div class="ug-seat-legend">' +
                    '<div class="ug-legend-item"><span class="ug-legend-dot" style="background:#10b981;"></span> In Venue</div>' +
                    '<div class="ug-legend-item"><span class="ug-legend-dot" style="background:#f59e0b;"></span> Awaiting</div>' +
                    '<div class="ug-legend-item"><span class="ug-legend-dot" style="background:#9333ea;"></span> VIP / Special Guest</div>' +
                    '<div class="ug-legend-item"><span class="ug-legend-dot" style="background:rgba(var(--muted), 0.4);"></span> Empty (Click to Reserve)</div>' +
                    '</div></div>';
        }
        html += '</div>';
    });
    container.innerHTML = html;
}

function escapeHtml(str) {
    if (!str) return '';
    return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}
</script>
@endsection
