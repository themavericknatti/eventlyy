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
/* ═══════════════════════════════════════════════════════════════════════
   Admin Events — Human-Crafted UI/UX Design System (.ae-*)
   Desktop Table (> 1024px), Tablet 2-Col Card Grid (601–1024px),
   Mobile Single-Column Cards (<= 600px). Light & Dark mode native.
   ═══════════════════════════════════════════════════════════════════════ */

.ae-page {
    display: flex;
    flex-direction: column;
    gap: 1.35rem;
    padding-bottom: 2.5rem;
    width: 100%;
    max-width: 100%;
    min-width: 0;
    box-sizing: border-box;
    overflow-x: clip;
}

/* ─── Hero Header ──────────────────────────────────────────────────────── */
.ae-hero {
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    gap: 1.25rem;
    flex-wrap: wrap;
    padding: 1.5rem 1.75rem;
    border-radius: 22px;
    background: linear-gradient(135deg, rgba(var(--brand), 0.08) 0%, rgba(var(--card), 0.96) 55%, rgba(var(--card), 1) 100%);
    border: 1px solid rgba(var(--muted), 0.16);
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
    max-width: 100%;
    min-width: 0;
    box-sizing: border-box;
}

.ae-hero-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.28rem 0.72rem;
    border-radius: 999px;
    font-size: 0.72rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    background: rgba(var(--brand), 0.12);
    color: rgb(var(--brand));
    margin-bottom: 0.65rem;
}

.ae-hero-title {
    margin: 0 0 0.35rem;
    font-size: clamp(1.45rem, 2.6vw, 1.95rem);
    font-weight: 900;
    letter-spacing: -0.025em;
    color: rgb(var(--text));
    line-height: 1.15;
}

.ae-hero-sub {
    margin: 0;
    font-size: 0.9rem;
    color: rgb(var(--muted));
    line-height: 1.5;
    max-width: 620px;
}

.ae-hero-actions {
    display: flex;
    align-items: center;
    gap: 0.55rem;
    flex-wrap: wrap;
}

.ae-hero-btn {
    height: 40px;
    padding: 0 1rem;
    border-radius: 12px;
    font-size: 0.83rem;
    font-weight: 700;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.4rem;
    white-space: nowrap;
    border: 1px solid rgba(var(--muted), 0.2);
    background: rgb(var(--card));
    color: rgb(var(--text));
    transition: transform 0.15s ease, border-color 0.15s ease, box-shadow 0.15s ease;
}
.ae-hero-btn:hover { transform: translateY(-1px); border-color: rgba(var(--brand), 0.4); }
.ae-hero-btn--primary {
    background: rgb(var(--brand));
    border-color: rgb(var(--brand));
    color: #ffffff;
    box-shadow: 0 6px 16px rgba(var(--brand), 0.28);
}
.ae-hero-btn--primary:hover { opacity: 0.94; color: #ffffff; }

/* ─── Summary Stats Grid ───────────────────────────────────────────────── */
.ae-stats-grid {
    display: grid;
    grid-template-columns: repeat(5, minmax(0, 1fr));
    gap: 0.9rem;
}

.ae-stat-card {
    background: rgb(var(--card));
    border: 1px solid rgba(var(--muted), 0.14);
    border-radius: 18px;
    padding: 1.1rem 1.15rem;
    display: flex;
    align-items: center;
    gap: 0.9rem;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.025);
    transition: transform 0.18s ease, box-shadow 0.18s ease, border-color 0.18s ease;
    min-width: 0;
}
.ae-stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 24px rgba(0, 0, 0, 0.06);
    border-color: rgba(var(--brand), 0.28);
}

.ae-stat-icon {
    width: 44px;
    height: 44px;
    border-radius: 13px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(var(--muted), 0.09);
    color: rgb(var(--text));
    flex-shrink: 0;
}
.ae-stat-icon--brand { background: rgba(var(--brand), 0.12); color: rgb(var(--brand)); }
.ae-stat-icon--indigo { background: rgba(99, 102, 241, 0.12); color: #6366f1; }
.ae-stat-icon--amber { background: rgba(245, 158, 11, 0.14); color: #d97706; }
.ae-stat-icon--emerald { background: rgba(16, 185, 129, 0.12); color: #10b981; }

.ae-stat-body {
    display: flex;
    flex-direction: column;
    min-width: 0;
}

.ae-stat-num {
    font-size: 1.35rem;
    font-weight: 900;
    line-height: 1.15;
    color: rgb(var(--text));
    font-variant-numeric: tabular-nums;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.ae-stat-lbl {
    font-size: 0.77rem;
    color: rgb(var(--muted));
    font-weight: 600;
    margin-top: 0.15rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* ─── Toolbar: Search & Filter ─────────────────────────────────────────── */
.ae-toolbar-card {
    background: rgb(var(--card));
    border: 1px solid rgba(var(--muted), 0.14);
    border-radius: 18px;
    padding: 0.9rem 1.15rem;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.025);
    max-width: 100%;
    min-width: 0;
    box-sizing: border-box;
}

.ae-toolbar-form {
    display: flex;
    align-items: center;
    gap: 0.65rem;
    flex-wrap: wrap;
}

.ae-search-wrap {
    flex: 1.5;
    min-width: 230px;
    position: relative;
    display: flex;
    align-items: center;
}

.ae-search-wrap input[type="search"] {
    width: 100%;
    height: 42px;
    padding: 0 0.9rem 0 2.45rem;
    border-radius: 12px;
    border: 1px solid rgba(var(--muted), 0.22);
    background: rgb(var(--bg));
    color: rgb(var(--text));
    font-size: 0.88rem;
    font-family: inherit;
    box-sizing: border-box;
    transition: border-color 0.18s ease, box-shadow 0.18s ease;
}

.ae-search-wrap input[type="search"]:focus,
.ae-filter-select:focus {
    outline: none;
    border-color: rgb(var(--brand));
    box-shadow: 0 0 0 3px rgba(var(--brand), 0.14);
}

.ae-search-icon {
    position: absolute;
    left: 0.82rem;
    color: rgb(var(--muted));
    pointer-events: none;
}

.ae-filter-select {
    height: 42px;
    padding: 0 0.9rem;
    border-radius: 12px;
    border: 1px solid rgba(var(--muted), 0.22);
    background: rgb(var(--bg));
    color: rgb(var(--text));
    font-size: 0.84rem;
    font-weight: 600;
    font-family: inherit;
    cursor: pointer;
    box-sizing: border-box;
}

.ae-filter-submit {
    height: 42px;
    padding: 0 1.2rem;
    border-radius: 12px;
    border: none;
    background: rgb(var(--brand));
    color: #ffffff;
    font-size: 0.85rem;
    font-weight: 800;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.4rem;
    transition: opacity 0.15s ease, transform 0.15s ease;
}
.ae-filter-submit:hover { opacity: 0.92; transform: translateY(-1px); }

.ae-filter-reset {
    height: 42px;
    padding: 0 0.9rem;
    border-radius: 12px;
    border: 1px solid rgba(var(--muted), 0.22);
    background: transparent;
    color: rgb(var(--muted));
    font-size: 0.82rem;
    font-weight: 700;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.3rem;
}
.ae-filter-reset:hover { color: rgb(var(--text)); border-color: rgba(var(--muted), 0.4); }

/* ─── Directory Container ──────────────────────────────────────────────── */
.ae-directory-card {
    background: rgb(var(--card));
    border: 1px solid rgba(var(--muted), 0.14);
    border-radius: 22px;
    padding: 1.35rem 1.5rem;
    box-shadow: 0 2px 14px rgba(0, 0, 0, 0.03);
    max-width: 100%;
    min-width: 0;
    box-sizing: border-box;
}

.ae-table-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
    margin-bottom: 1.15rem;
    flex-wrap: wrap;
}

.ae-table-title {
    margin: 0;
    font-size: 1.15rem;
    font-weight: 800;
    color: rgb(var(--text));
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.ae-count-chip {
    font-size: 0.76rem;
    font-weight: 800;
    padding: 0.25rem 0.65rem;
    border-radius: 999px;
    background: rgba(var(--muted), 0.1);
    color: rgb(var(--muted));
}

.ae-export-csv-btn {
    height: 36px;
    padding: 0 0.9rem;
    border-radius: 10px;
    border: 1px solid rgba(var(--muted), 0.22);
    background: rgb(var(--bg));
    color: rgb(var(--text));
    font-size: 0.8rem;
    font-weight: 700;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    transition: border-color 0.15s ease, color 0.15s ease;
}
.ae-export-csv-btn:hover { border-color: rgb(var(--brand)); color: rgb(var(--brand)); }

/* ─── Desktop Table (> 1024px) ─────────────────────────────────────────── */
.ae-table-wrap {
    width: 100%;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    border: 1px solid rgba(var(--muted), 0.12);
    border-radius: 16px;
}

.ae-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    font-size: 0.85rem;
    text-align: left;
}

.ae-table th {
    background: rgba(var(--muted), 0.05);
    color: rgb(var(--muted));
    font-size: 0.72rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    padding: 0.8rem 0.9rem;
    border-bottom: 1px solid rgba(var(--muted), 0.14);
    white-space: nowrap;
}

.ae-table td {
    padding: 0.85rem 0.9rem;
    border-bottom: 1px solid rgba(var(--muted), 0.08);
    color: rgb(var(--text));
    vertical-align: middle;
}

.ae-table tbody tr:last-child td { border-bottom: none; }
.ae-table tbody tr:hover td { background: rgba(var(--brand), 0.025); }

.ae-event-cell {
    display: flex;
    align-items: flex-start;
    gap: 0.7rem;
    min-width: 0;
}

.ae-avatar {
    width: 38px;
    height: 38px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.82rem;
    font-weight: 800;
    color: #ffffff;
    background: hsl(var(--avatar-hue, 200), 62%, 44%);
    flex-shrink: 0;
    margin-top: 2px;
}

.ae-event-title-link {
    color: rgb(var(--text));
    text-decoration: none;
    font-weight: 800;
    font-size: 0.92rem;
    display: block;
    line-height: 1.25;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    max-width: 240px;
}
.ae-event-title-link:hover { color: rgb(var(--brand)); }

.ae-tag-row {
    display: flex;
    align-items: center;
    gap: 0.35rem;
    margin-top: 3px;
    flex-wrap: wrap;
}

.ae-cat-badge {
    font-size: 0.68rem;
    font-weight: 700;
    color: rgb(var(--muted));
    background: rgba(var(--muted), 0.09);
    padding: 0.12rem 0.45rem;
    border-radius: 6px;
    white-space: nowrap;
}

.ae-series-btn {
    font-size: 0.68rem;
    font-weight: 700;
    color: #6366f1;
    background: rgba(99, 102, 241, 0.12);
    border: 1px solid rgba(99, 102, 241, 0.25);
    padding: 0.12rem 0.45rem;
    border-radius: 6px;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 0.2rem;
    white-space: nowrap;
}

.ae-meta-line {
    display: flex;
    align-items: center;
    gap: 0.35rem;
    margin-top: 3px;
    font-size: 0.74rem;
    color: rgb(var(--muted));
    max-width: 245px;
    overflow: hidden;
    white-space: nowrap;
}
.ae-meta-venue {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    min-width: 0;
}

.ae-org-sub {
    font-size: 0.71rem;
    color: rgb(var(--muted));
    margin-top: 2px;
    max-width: 245px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

/* Status Pills */
.ae-status-pill {
    font-size: 0.72rem;
    font-weight: 800;
    padding: 0.22rem 0.62rem;
    border-radius: 999px;
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    white-space: nowrap;
}
.ae-status--live { background: rgba(16, 185, 129, 0.12); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.28); }
.ae-status--upcoming { background: rgba(139, 92, 246, 0.12); color: #8b5cf6; border: 1px solid rgba(139, 92, 246, 0.25); }
.ae-status--completed { background: rgba(var(--muted), 0.1); color: rgb(var(--muted)); border: 1px solid rgba(var(--muted), 0.2); }
.ae-status--cancelled { background: rgba(239, 68, 68, 0.12); color: #dc2626; border: 1px solid rgba(239, 68, 68, 0.28); }
.ae-status--draft { background: rgba(245, 158, 11, 0.12); color: #d97706; border: 1px solid rgba(245, 158, 11, 0.28); }

/* Action Buttons */
.ae-actions-row {
    display: inline-flex;
    align-items: center;
    justify-content: flex-end;
    gap: 0.3rem;
    flex-wrap: wrap;
    max-width: 260px;
}

.ae-btn-sm {
    height: 30px;
    padding: 0 0.55rem;
    font-size: 0.74rem;
    font-weight: 700;
    border-radius: 8px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.25rem;
    text-decoration: none;
    white-space: nowrap;
    cursor: pointer;
    border: 1px solid transparent;
    transition: opacity 0.15s ease, transform 0.15s ease;
    box-sizing: border-box;
}
.ae-btn-sm:hover { opacity: 0.88; transform: translateY(-1px); }
.ae-btn--edit { background: rgba(var(--brand), 0.1); color: rgb(var(--brand)); border-color: rgba(var(--brand), 0.22); }
.ae-btn--manage { background: rgba(var(--text), 0.06); color: rgb(var(--text)); border-color: rgba(var(--muted), 0.18); }
.ae-btn--ref { background: rgba(16, 185, 129, 0.12); color: #059669; border-color: rgba(16, 185, 129, 0.24); }
.ae-btn--clone { background: rgba(147, 51, 234, 0.1); color: #9333ea; border-color: rgba(147, 51, 234, 0.24); }
.ae-btn--feat-on { background: rgba(245, 158, 11, 0.16); color: #d97706; border-color: rgba(245, 158, 11, 0.35); }
.ae-btn--feat-off { background: rgba(var(--muted), 0.1); color: rgb(var(--text)); border-color: rgba(var(--muted), 0.2); }

/* ─── Unified Responsive Event Card Grid (Desktop, Tablet & Mobile) ──── */
.ae-cards-list {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 1.1rem;
    width: 100%;
    max-width: 100%;
    min-width: 0;
    box-sizing: border-box;
}

.ae-event-card {
    background: rgb(var(--bg));
    border: 1px solid rgba(var(--muted), 0.16);
    border-radius: 18px;
    padding: 1.15rem;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    gap: 0.8rem;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.02);
    transition: border-color 0.18s ease, box-shadow 0.18s ease;
    min-width: 0;
    max-width: 100%;
    box-sizing: border-box;
    overflow: hidden;
}
.ae-event-card:hover {
    border-color: rgba(var(--brand), 0.32);
    box-shadow: 0 6px 18px rgba(0, 0, 0, 0.05);
}

.ae-card-metrics {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 0.65rem;
    background: rgba(var(--muted), 0.05);
    border-radius: 12px;
    padding: 0.75rem 0.85rem;
    min-width: 0;
    max-width: 100%;
    box-sizing: border-box;
}
.ae-card-metrics > div {
    min-width: 0;
    overflow-wrap: anywhere;
}

.ae-card-metric-lbl {
    font-size: 0.66rem;
    text-transform: uppercase;
    font-weight: 800;
    color: rgb(var(--muted));
    letter-spacing: 0.04em;
    display: block;
}

.ae-card-actions {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 0.38rem;
    padding-top: 0.65rem;
    border-top: 1px solid rgba(var(--muted), 0.12);
    min-width: 0;
    max-width: 100%;
    box-sizing: border-box;
}
.ae-card-actions > *,
.ae-card-actions form {
    min-width: 0;
    max-width: 100%;
    box-sizing: border-box;
}
.ae-card-actions .ae-btn-sm {
    min-height: 30px;
    height: auto;
    padding: 0.26rem 0.4rem;
    width: 100%;
    font-size: 0.73rem;
    min-width: 0;
    white-space: normal;
    text-align: center;
    line-height: 1.2;
}
.ae-card-feature-row {
    grid-column: span 2;
}

/* ─── Pagination ───────────────────────────────────────────────────────── */
.ae-pagination {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 0.4rem;
    flex-wrap: wrap;
    margin-top: 1.5rem;
    padding-top: 1.25rem;
    border-top: 1px solid rgba(var(--muted), 0.12);
}

.ae-page-btn, .ae-page-num {
    min-width: 36px;
    height: 36px;
    padding: 0 0.75rem;
    border-radius: 10px;
    border: 1px solid rgba(var(--muted), 0.18);
    background: rgb(var(--bg));
    color: rgb(var(--text));
    font-size: 0.84rem;
    font-weight: 700;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.3rem;
}
.ae-page-num.active { background: rgb(var(--brand)); border-color: rgb(var(--brand)); color: #ffffff; }
.ae-page-btn.disabled { opacity: 0.45; cursor: not-allowed; }

/* ─── Responsive Breakpoints ───────────────────────────────────────────── */
@media (max-width: 1180px) {
    .ae-stats-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); }
}

@media (max-width: 1220px) {
    .ae-cards-list { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .ae-directory-card { padding: 1.2rem; border-radius: 18px; }
}

@media (max-width: 768px) {
    .ae-hero { padding: 1.25rem 1.35rem; align-items: stretch; }
    .ae-hero-actions { width: 100%; display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); }
    .ae-hero-btn { width: 100%; padding: 0 0.5rem; font-size: 0.78rem; }
    .ae-stats-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .ae-toolbar-form { flex-direction: column; align-items: stretch; }
    .ae-search-wrap { min-width: 0; width: 100%; }
    .ae-filter-select, .ae-filter-submit, .ae-filter-reset { width: 100%; }
}

@media (max-width: 680px) {
    .ae-cards-list { grid-template-columns: minmax(0, 1fr); }
    .ae-hero-actions { grid-template-columns: minmax(0, 1fr); }
    .ae-directory-card { padding: 0.9rem; border-radius: 16px; }
    .ae-event-card { padding: 0.95rem; border-radius: 14px; }
}

@media (max-width: 420px) {
    .ae-stats-grid { grid-template-columns: minmax(0, 1fr); }
    .ae-card-metrics { grid-template-columns: minmax(0, 1fr); }
    .ae-card-actions { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .ae-card-feature-row { grid-column: 1 / -1; }
}
</style>

<div class="ae-page">

<header class="ae-hero">
    <div>
        <span class="ae-hero-badge">🎪 Admin — Events</span>
        <h1 class="ae-hero-title">Published Events</h1>
        <p class="ae-hero-sub">Monitor event performance, ticket sales velocity, platform profits, and organizer accounts.</p>
    </div>
    <div class="ae-hero-actions">
        <a href="{{ route('admin/overview') }}" class="ae-hero-btn">
            <span>&larr;</span> Dashboard
        </a>
        <a href="{{ route('host/dashboard') }}" class="ae-hero-btn">
            <span>🎪</span> Host View
        </a>
        <a href="{{ route('host/event/create') }}" class="ae-hero-btn ae-hero-btn--primary">
            <span>➕</span> Create Event
        </a>
    </div>
</header>

<!-- Summary Stats Cards Grid -->
<section class="ae-stats-grid">
    <div class="ae-stat-card">
        <span class="ae-stat-icon">
            <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
                <line x1="16" y1="2" x2="16" y2="6" />
                <line x1="8" y1="2" x2="8" y2="6" />
                <line x1="3" y1="10" x2="21" y2="10" />
            </svg>
        </span>
        <div class="ae-stat-body">
            <span class="ae-stat-num"><?= number_format($total_events ?? 0) ?></span>
            <span class="ae-stat-lbl">Total Events<?php if (($total_occurrences ?? 0) > ($total_events ?? 0)): ?> (<?= (int)$total_occurrences ?> dates)<?php endif; ?></span>
        </div>
    </div>
    <div class="ae-stat-card">
        <span class="ae-stat-icon ae-stat-icon--brand">
            <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10" />
                <polyline points="12 6 12 12 16 14" />
            </svg>
        </span>
        <div class="ae-stat-body">
            <span class="ae-stat-num"><?= number_format($active_events ?? 0) ?></span>
            <span class="ae-stat-lbl">Active &amp; Live</span>
        </div>
    </div>
    <div class="ae-stat-card">
        <span class="ae-stat-icon ae-stat-icon--indigo">
            <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z" />
                <line x1="2" y1="10" x2="22" y2="10" />
            </svg>
        </span>
        <div class="ae-stat-body">
            <span class="ae-stat-num"><?= number_format($tickets_sold ?? 0) ?></span>
            <span class="ae-stat-lbl">Tickets Sold</span>
        </div>
    </div>
    <div class="ae-stat-card">
        <span class="ae-stat-icon ae-stat-icon--amber">
            <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="12" y1="1" x2="12" y2="23" />
                <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" />
            </svg>
        </span>
        <div class="ae-stat-body">
            <span class="ae-stat-num"><?= format_price($total_gross ?? 0) ?></span>
            <span class="ae-stat-lbl">Total Gross</span>
        </div>
    </div>
    <div class="ae-stat-card">
        <span class="ae-stat-icon ae-stat-icon--emerald">
            <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" />
            </svg>
        </span>
        <div class="ae-stat-body">
            <span class="ae-stat-num" style="color:#10b981;"><?= format_price($total_platform_profit ?? 0) ?></span>
            <span class="ae-stat-lbl">Platform Profit</span>
        </div>
    </div>
</section>

<!-- Search & Filter Toolbar -->
<?php $hasEventFilters = !empty($search) || !empty($status); ?>
<section class="ae-toolbar-card">
    <form method="GET" action="" class="ae-toolbar-form">
        <div class="ae-search-wrap">
            <svg class="ae-search-icon" viewBox="0 0 24 24" width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="11" cy="11" r="8" />
                <line x1="21" y1="21" x2="16.65" y2="16.65" />
            </svg>
            <input type="search" name="q" id="events-search" placeholder="Search by title, venue, or organizer email..." aria-label="Search events" value="<?= e($search ?? '') ?>" />
        </div>
        <select name="status" id="events-status-filter" class="ae-filter-select" aria-label="Filter by status">
            <option value="">All Statuses</option>
            <option value="active" <?= ($status ?? '') === 'active' ? 'selected' : '' ?>>Active / Live</option>
            <option value="draft" <?= ($status ?? '') === 'draft' ? 'selected' : '' ?>>Draft</option>
            <option value="past" <?= ($status ?? '') === 'past' ? 'selected' : '' ?>>Past</option>
        </select>
        <button type="submit" class="ae-filter-submit">
            <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon>
            </svg>
            Apply Filters
        </button>
        <?php if ($hasEventFilters): ?>
            <a href="{{ route('admin/events') }}" class="ae-filter-reset">✕ Clear</a>
        <?php endif; ?>
    </form>
</section>

<!-- Events Directory -->
<section class="ae-directory-card">
    <div class="ae-table-header">
        <h2 class="ae-table-title">
            Events Directory
            <span class="ae-count-chip"><?= count($events ?? []) ?> listed</span>
        </h2>
        <button type="button" class="ae-export-csv-btn" onclick="exportEventsCsv()">
            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4" />
                <polyline points="7 10 12 15 17 10" />
                <line x1="12" y1="15" x2="12" y2="3" />
            </svg>
            Export CSV
        </button>
    </div>

    <!-- Unified Responsive Card Grid (Desktop, Tablet & Mobile) -->
    <div class="ae-cards-list" id="events-cards">
        <?php if (!empty($events)): ?>
            <?php foreach ($events as $ev): 
                $hue = abs(crc32($ev['name'] ?? 'Event')) % 360;
                $initials = strtoupper(substr($ev['name'] ?? 'E', 0, 2));
                $dateStr = date('M d, Y', strtotime($ev['date']));
                $timeStr = date('g:i A', strtotime($ev['date']));
                $sold = (int)$ev['sold'];
                $capacity = (int)($ev['capacity'] ?: 0);
                $pct = $capacity > 0 ? min(100, round(($sold / $capacity) * 100)) : 0;
                $evTime = strtotime($ev['date']);
                $evNow = time();
                $evLiveEnd = $evTime + (event_completion_hours() * 3600);
                if ($ev['status'] === 'completed' || ($ev['status'] === 'published' && $evNow > $evLiveEnd)) {
                    $evStatusLabel = 'Completed';
                    $evStatusClass = 'ae-status--completed';
                } elseif ($ev['status'] === 'published' && $evNow >= $evTime && $evNow <= $evLiveEnd) {
                    $evStatusLabel = '🔴 Live Now';
                    $evStatusClass = 'ae-status--live';
                } elseif ($ev['status'] === 'published') {
                    $evStatusLabel = 'Upcoming';
                    $evStatusClass = 'ae-status--upcoming';
                } elseif ($ev['status'] === 'cancelled') {
                    $evStatusLabel = 'Cancelled';
                    $evStatusClass = 'ae-status--cancelled';
                } else {
                    $evStatusLabel = ucfirst($ev['status']);
                    $evStatusClass = 'ae-status--draft';
                }

                // Pricing calculation for cards
                $minP = isset($ev['min_price']) && $ev['min_price'] !== null ? (float)$ev['min_price'] : null;
                $maxP = isset($ev['max_price']) && $ev['max_price'] !== null ? (float)$ev['max_price'] : null;
                $tierCount = (int)($ev['ticket_type_count'] ?? 0);

                if ($minP === null && $tierCount === 0) {
                    $cardAmount = 'Free';
                } elseif ($minP == 0 && ($maxP == 0 || $maxP === null)) {
                    $cardAmount = 'Free';
                } elseif ($minP == 0 && $maxP > 0) {
                    $cardAmount = 'Free – ' . format_price($maxP);
                } elseif ($minP !== null && $maxP !== null && $minP == $maxP) {
                    $cardAmount = format_price($minP);
                } else {
                    $cardAmount = format_price($minP ?? 0) . ' – ' . format_price($maxP ?? 0);
                }

                $venueStr = $ev['location'] ?: 'Online / TBD';
                $fullScheduleVenue = $dateStr . ' • ' . $timeStr . ' • ' . $venueStr;
            ?>
            <article class="ae-event-card"
                     data-event-name="<?= e($ev['name']) ?>"
                     data-event-date="<?= e($dateStr) ?>"
                     data-event-venue="<?= e($venueStr) ?>"
                     data-event-owner="<?= e($ev['organizer_email']) ?>"
                     data-event-sold="<?= $sold ?>/<?= $capacity ?>"
                     data-event-gross="<?= e(format_price($ev['gross_revenue'] ?? 0)) ?>"
                     data-event-profit="<?= e(format_price($ev['platform_profit'] ?? 0)) ?>"
                     data-event-status="<?= e($evStatusLabel) ?>">
                <!-- Card Header -->
                <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:0.65rem; min-width:0;">
                    <div class="ae-event-cell" style="flex:1; min-width:0;">
                        <span class="ae-avatar" style="--avatar-hue:<?= $hue ?>"><?= e($initials) ?></span>
                        <div style="min-width:0; flex:1;">
                            <a href="{{ route('admin/events/' . $ev['id']) }}" class="ae-event-title-link" style="max-width:100%;" title="<?= e($ev['name']) ?>">
                                <?= e($ev['name']) ?> &nearr;
                            </a>
                            <div class="ae-tag-row">
                                <span class="ae-cat-badge"><?= e($ev['category'] ?? 'Event') ?></span>
                                <?php if (!empty($ev['is_recurring'])): ?>
                                    <button type="button" class="ae-series-btn" onclick="openSeriesOccurrencesModal('<?= $ev['id'] ?>', '<?= addslashes(e($ev['name'])) ?>')" title="Click to view all occurrences in this recurring series">
                                        🔄 Series (<?= e($ev['recurrence_count'] ?? '') ?>) ▾
                                    </button>
                                <?php endif; ?>
                            </div>
                            <div class="ae-meta-line" style="max-width:100%;" title="<?= e($fullScheduleVenue) ?>">
                                <span>🗓️ <?= $dateStr ?></span>
                                <span>&bull;</span>
                                <span class="ae-meta-venue">📍 <?= e($venueStr) ?></span>
                            </div>
                            <div class="ae-org-sub" style="max-width:100%;">
                                👤 <?= e($ev['organizer_email']) ?>
                            </div>
                        </div>
                    </div>
                    <span class="ae-status-pill <?= $evStatusClass ?>" style="flex-shrink:0;"><?= $evStatusLabel ?></span>
                </div>

                <!-- Card Metrics Grid (2x2) -->
                <div class="ae-card-metrics">
                    <div>
                        <span class="ae-card-metric-lbl">Amount</span>
                        <div style="font-size:0.86rem; font-weight:800; color:rgb(var(--text)); margin-top:2px;">
                            <?= $cardAmount ?>
                        </div>
                        <span style="font-size:0.68rem; color:rgb(var(--muted));"><?= $tierCount > 0 ? ($tierCount . ' tier' . ($tierCount > 1 ? 's' : '')) : 'Free admission' ?></span>
                    </div>

                    <div>
                        <span class="ae-card-metric-lbl">Sales Progress</span>
                        <div style="display:flex; justify-content:space-between; align-items:baseline; margin-top:2px;">
                            <strong style="font-size:0.82rem; font-variant-numeric:tabular-nums;"><?= number_format($sold) ?> / <?= number_format($capacity) ?></strong>
                            <span style="font-size:0.72rem; font-weight:800; color:<?= $pct > 80 ? '#10b981' : 'rgb(var(--brand))' ?>;"><?= $pct ?>%</span>
                        </div>
                        <div style="height:5px; background:rgba(var(--muted),0.15); border-radius:999px; overflow:hidden; margin-top:4px;">
                            <div style="height:100%; width:<?= $pct ?>%; background:<?= $pct > 80 ? '#10b981' : 'rgb(var(--brand))' ?>;"></div>
                        </div>
                    </div>

                    <div>
                        <span class="ae-card-metric-lbl">Gross &amp; Host</span>
                        <div style="font-size:0.84rem; font-weight:800; margin-top:2px; font-variant-numeric:tabular-nums;"><?= format_price($ev['gross_revenue'] ?? 0) ?></div>
                        <span style="font-size:0.72rem; color:#10b981; font-weight:700;">Host: <?= format_price($ev['host_earnings'] ?? 0) ?></span>
                    </div>

                    <div>
                        <span class="ae-card-metric-lbl">Platform Profit</span>
                        <div style="font-size:0.86rem; font-weight:800; color:#10b981; margin-top:2px; font-variant-numeric:tabular-nums;"><?= format_price($ev['platform_profit'] ?? 0) ?></div>
                        <span style="font-size:0.68rem; color:rgb(var(--muted));">Fees &amp; Net</span>
                    </div>
                </div>

                <!-- Card Actions Grid -->
                <div class="ae-card-actions">
                    <a href="{{ route('host/event/edit/' . $ev['id']) }}" class="ae-btn-sm ae-btn--edit">✏️ Edit</a>
                    <a href="{{ route('host/event/management/' . $ev['id']) }}" class="ae-btn-sm ae-btn--manage">⚙️ Manage</a>
                    <a href="{{ route('host/event/referrals/' . $ev['id']) }}" class="ae-btn-sm ae-btn--ref">🔗 Referrals</a>
                    <form method="POST" action="{{ route('admin/events/duplicate/' . $ev['id']) }}" style="display:block; margin:0;" onsubmit="return confirm('Clone this event as a new draft?');">
                        <?= csrf_field() ?>
                        <button type="submit" class="ae-btn-sm ae-btn--clone" title="Duplicate / Clone Event">📋 Clone</button>
                    </form>
                    <div class="ae-card-feature-row">
                        <form method="POST" action="{{ route('admin/events/toggle-featured') }}" style="display:block; margin:0;">
                            <?= csrf_field() ?>
                            <input type="hidden" name="event_id" value="<?= $ev['id'] ?>" />
                            <?php if (!empty($ev['is_featured'])): ?>
                                <button type="submit" class="ae-btn-sm ae-btn--feat-on">⭐ Featured on Carousel</button>
                            <?php else: ?>
                                <button type="submit" class="ae-btn-sm ae-btn--feat-off">☆ Feature on Carousel</button>
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
        <div id="events-empty" class="empty-state" style="padding: 2rem; text-align: center; color: rgb(var(--muted));">
            <p style="margin:0;">No events found matching your criteria.</p>
        </div>
    <?php endif; ?>

    <!-- Pagination -->
    <nav class="ae-pagination" aria-label="Events pagination" id="events-pagination">
        <a href="?page=1" class="ae-page-btn disabled" aria-disabled="true">
            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="15 18 9 12 15 6" />
            </svg>
            Prev
        </a>
        <span class="ae-page-num active" aria-current="page">1</span>
        <a href="?page=1" class="ae-page-btn disabled" aria-disabled="true">
            Next
            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="9 18 15 12 9 6" />
            </svg>
        </a>
    </nav>
</section>
</div>

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
    const delModal = document.getElementById('delete-modal');
    if (delModal && e.target === delModal) delModal.classList.add('hidden');
});

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeSeriesOccurrencesModal();
        const delModal = document.getElementById('delete-modal');
        if (delModal) delModal.classList.add('hidden');
    }
});

const delCancel = document.getElementById('delete-cancel-btn');
if (delCancel) {
    delCancel.addEventListener('click', function() {
        const delModal = document.getElementById('delete-modal');
        if (delModal) delModal.classList.add('hidden');
    });
}

function exportEventsCsv() {
    const cards = document.querySelectorAll('#events-cards .ae-event-card[data-event-name]');
    if (!cards.length) return;
    const csvRows = [['Event Name', 'Date', 'Venue', 'Organizer', 'Sold/Capacity', 'Gross Revenue', 'Platform Profit', 'Status']];
    cards.forEach(card => {
        const vals = [
            card.dataset.eventName || '',
            card.dataset.eventDate || '',
            card.dataset.eventVenue || '',
            card.dataset.eventOwner || '',
            card.dataset.eventSold || '',
            card.dataset.eventGross || '',
            card.dataset.eventProfit || '',
            card.dataset.eventStatus || ''
        ].map(v => '"' + String(v).replace(/"/g, '""') + '"');
        csvRows.push(vals.join(','));
    });
    const blob = new Blob([csvRows.join('\n')], { type: 'text/csv;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'eventlyy-admin-events-' + new Date().toISOString().slice(0, 10) + '.csv';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
}

const aeSearchInput = document.getElementById('events-search');
if (aeSearchInput) {
    aeSearchInput.addEventListener('input', function() {
        const q = (this.value || '').toLowerCase().trim();
        const cards = document.querySelectorAll('#events-cards .ae-event-card');
        let visible = 0;
        cards.forEach(card => {
            const hay = ((card.dataset.eventName || '') + ' ' + (card.dataset.eventVenue || '') + ' ' + (card.dataset.eventOwner || '')).toLowerCase();
            const match = !q || hay.includes(q);
            card.style.display = match ? '' : 'none';
            if (match) visible++;
        });
        const emptyEl = document.getElementById('events-empty');
        if (emptyEl) emptyEl.style.display = visible === 0 ? 'block' : 'none';
    });
}
</script>
@endsection