<?php

/**
 * Team Support Desk Page
 *
 * Workstation task view for guest support & help desk staff.
 * Features attendee lookup, ticket verification, check-in overrides,
 * cross-event pass resolution, instant gate pass issuance, and offline sync.
 * Fully responsive across Mobile, Tablet, and Desktop.
 *
 * @package Evently
 * @subpackage Views/Teams
 * @author Your Name <Celio Natti>
 * @license MIT License
 * @version 2.1.0
 */

$eventTime = !empty($event['date']) ? strtotime($event['date']) : null;
$assignments = $assignments ?? [];
$tickets = $tickets ?? [];
?>

@extends('layouts/team')

@section('title')
Guest & Support Desk - <?= e($event['name'] ?? 'Evently') ?>
@endsection

@section('style')
<style>
/* ═══════════════════════════════════════════════════════════════════
   Support Desk Scoped Component System (.sd-*)
   ═══════════════════════════════════════════════════════════════════ */
.sd-wrapper {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
    width: 100%;
}

/* ── Hero & Event Workspace Banner ── */
.sd-hero {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1.25rem;
    flex-wrap: wrap;
    background: rgb(var(--card));
    border: 1px solid rgba(var(--muted), 0.16);
    border-radius: 20px;
    padding: 1.4rem 1.6rem;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04);
}

.sd-hero-left {
    display: flex;
    flex-direction: column;
    gap: 0.4rem;
    flex: 1;
    min-width: 260px;
}

.sd-badge-row {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    flex-wrap: wrap;
}

.sd-panel-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    background: rgba(var(--brand), 0.12);
    color: rgb(var(--brand));
    border: 1px solid rgba(var(--brand), 0.25);
    padding: 0.3rem 0.75rem;
    border-radius: 9999px;
    font-size: 0.8rem;
    font-weight: 700;
    letter-spacing: 0.02em;
}

.sd-live-status {
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    font-size: 0.78rem;
    font-weight: 600;
    color: #10b981;
}

.sd-pulse-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #10b981;
    animation: sdPulse 2s infinite ease-in-out;
}

@keyframes sdPulse {
    0%, 100% { opacity: 1; transform: scale(1); }
    50% { opacity: 0.4; transform: scale(0.85); }
}

.sd-net-pill {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    font-size: 0.76rem;
    font-weight: 700;
    padding: 0.25rem 0.65rem;
    border-radius: 9999px;
    letter-spacing: 0.02em;
    transition: all 0.2s ease;
}

.sd-net-pill.online {
    background: rgba(16, 185, 129, 0.12);
    color: #10b981;
    border: 1px solid rgba(16, 185, 129, 0.3);
}

.sd-net-pill.offline {
    background: rgba(245, 158, 11, 0.14);
    color: #d97706;
    border: 1px solid rgba(245, 158, 11, 0.35);
}

:root[data-theme='dark'] .sd-net-pill.offline {
    color: #fcd34d;
}

.sd-hero-title {
    margin: 0;
    font-size: clamp(1.3rem, 2.8vw, 1.85rem);
    font-weight: 800;
    color: rgb(var(--text));
    letter-spacing: -0.02em;
    line-height: 1.25;
}

.sd-hero-meta {
    display: flex;
    align-items: center;
    gap: 0.65rem;
    flex-wrap: wrap;
    font-size: 0.88rem;
    color: rgb(var(--muted));
}

.sd-hero-meta span {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
}

.sd-meta-divider {
    opacity: 0.35;
}

.sd-hero-actions {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    flex-wrap: wrap;
}

.sd-btn-issue {
    background: linear-gradient(135deg, #10b981, #059669);
    color: #ffffff;
    font-weight: 800;
    font-size: 0.9rem;
    padding: 0.65rem 1.25rem;
    border-radius: 12px;
    border: none;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    box-shadow: 0 4px 14px rgba(16, 185, 129, 0.35);
    transition: transform 0.18s ease, box-shadow 0.18s ease;
}

.sd-btn-issue:hover {
    transform: translateY(-1px);
    box-shadow: 0 6px 18px rgba(16, 185, 129, 0.45);
}

.sd-btn-issue:active {
    transform: translateY(0);
}

.sd-switcher-box {
    background: rgb(var(--bg));
    border: 1px solid rgba(var(--muted), 0.18);
    border-radius: 12px;
    padding: 0.4rem 0.75rem;
    display: flex;
    flex-direction: column;
    gap: 0.2rem;
}

.sd-switcher-label {
    font-size: 0.68rem;
    text-transform: uppercase;
    font-weight: 800;
    letter-spacing: 0.05em;
    color: rgb(var(--muted));
}

.sd-switcher-select {
    background: transparent;
    border: none;
    font-size: 0.85rem;
    font-weight: 700;
    color: rgb(var(--text));
    outline: none;
    cursor: pointer;
    padding-right: 0.5rem;
}

/* ── Offline Sync Notification Bar ── */
.sd-sync-bar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    background: rgba(245, 158, 11, 0.14);
    border: 1px solid rgba(245, 158, 11, 0.38);
    border-radius: 14px;
    padding: 0.85rem 1.25rem;
    font-size: 0.88rem;
    font-weight: 700;
    color: #b45309;
    animation: sdFadeIn 0.25s ease;
}

:root[data-theme='dark'] .sd-sync-bar {
    background: rgba(245, 158, 11, 0.2);
    color: #fcd34d;
}

.sd-sync-bar-left {
    display: flex;
    align-items: center;
    gap: 0.65rem;
    flex-wrap: wrap;
}

.sd-btn-sync-now {
    background: #d97706;
    color: #ffffff;
    border: none;
    border-radius: 10px;
    padding: 0.45rem 1rem;
    font-size: 0.82rem;
    font-weight: 800;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    transition: background 0.18s ease, transform 0.15s ease;
    box-shadow: 0 2px 8px rgba(217, 119, 6, 0.35);
}

.sd-btn-sync-now:hover {
    background: #b45309;
    transform: translateY(-1px);
}

/* ── Cross-Event Match Banner ── */
.sd-cross-match {
    background: rgba(59, 130, 246, 0.08);
    border: 1px solid rgba(59, 130, 246, 0.28);
    border-radius: 16px;
    padding: 1rem 1.25rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    flex-wrap: wrap;
}

.sd-cross-match-left {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    flex: 1;
    min-width: 260px;
}

.sd-cross-match-icon {
    font-size: 1.5rem;
}

.sd-cross-match-info {
    font-size: 0.88rem;
    color: rgb(var(--text));
    line-height: 1.4;
}

.sd-cross-match-code {
    font-family: monospace;
    font-weight: 800;
    color: #2563eb;
    background: rgba(37, 99, 235, 0.1);
    padding: 0.15rem 0.45rem;
    border-radius: 6px;
}

.sd-cross-match-btn {
    background: #2563eb;
    color: #ffffff;
    border: none;
    border-radius: 10px;
    padding: 0.6rem 1.15rem;
    font-size: 0.84rem;
    font-weight: 700;
    cursor: pointer;
    transition: background 0.18s ease;
    white-space: nowrap;
}

.sd-cross-match-btn:hover {
    background: #1d4ed8;
}

/* ── Quick Stats Grid ── */
.sd-stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1rem;
}

.sd-stat-card {
    background: rgb(var(--card));
    border: 1px solid rgba(var(--muted), 0.14);
    border-radius: 18px;
    padding: 1.25rem 1.35rem;
    display: flex;
    flex-direction: column;
    gap: 0.4rem;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.03);
    position: relative;
    overflow: hidden;
}

.sd-stat-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: rgba(var(--muted), 0.15);
}

.sd-stat-card.stat-green::before { background: #10b981; }
.sd-stat-card.stat-amber::before { background: #f59e0b; }
.sd-stat-card.stat-brand::before { background: rgb(var(--brand)); }

.sd-stat-label {
    font-size: 0.78rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: rgb(var(--muted));
}

.sd-stat-val {
    font-size: clamp(1.4rem, 2.5vw, 1.85rem);
    font-weight: 800;
    color: rgb(var(--text));
    line-height: 1.2;
    transition: color 0.25s ease;
}

.sd-stat-card.stat-green .sd-stat-val { color: #10b981; }
.sd-stat-card.stat-amber .sd-stat-val { color: #d97706; }
.sd-stat-card.stat-brand .sd-stat-val { color: rgb(var(--brand)); }

.sd-stat-sub {
    font-size: 0.78rem;
    color: rgb(var(--muted));
}

/* ── Search Toolbar ── */
.sd-search-card {
    background: rgb(var(--card));
    border: 1px solid rgba(var(--muted), 0.16);
    border-radius: 18px;
    padding: 1.25rem 1.4rem;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.03);
}

.sd-search-form {
    display: flex;
    align-items: center;
    gap: 0.85rem;
    flex-wrap: wrap;
}

.sd-search-input-wrap {
    flex: 1;
    min-width: 280px;
    position: relative;
}

.sd-search-input {
    width: 100%;
    box-sizing: border-box;
    padding: 0.75rem 2.8rem 0.75rem 2.6rem;
    border-radius: 12px;
    border: 1px solid rgba(var(--muted), 0.24);
    background: rgb(var(--bg));
    color: rgb(var(--text));
    font-size: 16px;
    font-weight: 500;
    outline: none;
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
}

.sd-search-input:focus {
    border-color: rgb(var(--brand));
    box-shadow: 0 0 0 3px rgba(var(--brand), 0.15);
}

.sd-search-icon {
    position: absolute;
    left: 0.95rem;
    top: 50%;
    transform: translateY(-50%);
    opacity: 0.5;
    pointer-events: none;
    font-size: 0.95rem;
}

.sd-search-spinner {
    position: absolute;
    right: 0.95rem;
    top: 50%;
    transform: translateY(-50%);
    font-size: 0.95rem;
    color: rgb(var(--brand));
    animation: sdSpin 0.75s linear infinite;
}

.sd-clear-btn {
    background: rgba(var(--muted), 0.14);
    color: rgb(var(--text));
    border: none;
    border-radius: 12px;
    padding: 0.75rem 1.25rem;
    font-size: 0.88rem;
    font-weight: 700;
    cursor: pointer;
    transition: background 0.18s ease;
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
}

.sd-clear-btn:hover {
    background: rgba(var(--muted), 0.22);
}

.sd-search-meta-bar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-top: 0.75rem;
    padding-top: 0.65rem;
    border-top: 1px solid rgba(var(--muted), 0.12);
    font-size: 0.78rem;
    color: rgb(var(--muted));
    flex-wrap: wrap;
    gap: 0.5rem;
}

.sd-search-mode-pill {
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
}

.sd-search-mode-pill.online { color: #10b981; }
.sd-search-mode-pill.offline { color: #d97706; }

/* ── Content Card (Table & Cards) ── */
.sd-content-card {
    background: rgb(var(--card));
    border: 1px solid rgba(var(--muted), 0.16);
    border-radius: 20px;
    padding: 1.5rem;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
}

.sd-card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.25rem;
    flex-wrap: wrap;
    gap: 0.75rem;
}

.sd-card-title {
    margin: 0;
    font-size: 1.25rem;
    font-weight: 800;
    color: rgb(var(--text));
    letter-spacing: -0.01em;
}

.sd-card-subtitle {
    margin: 0.25rem 0 0;
    font-size: 0.84rem;
    color: rgb(var(--muted));
}

/* ── Table System ── */
.sd-table-wrap {
    overflow-x: auto;
    border: 1px solid rgba(var(--muted), 0.16);
    border-radius: 14px;
}

.sd-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.88rem;
    text-align: left;
}

.sd-table th {
    background: rgba(var(--muted), 0.06);
    color: rgb(var(--muted));
    font-weight: 800;
    text-transform: uppercase;
    font-size: 0.74rem;
    letter-spacing: 0.04em;
    padding: 0.9rem 1.15rem;
    border-bottom: 1px solid rgba(var(--muted), 0.16);
    white-space: nowrap;
}

.sd-table td {
    padding: 1rem 1.15rem;
    border-bottom: 1px solid rgba(var(--muted), 0.12);
    vertical-align: middle;
    color: rgb(var(--text));
    transition: background 0.2s ease;
}

.sd-table tr:last-child td {
    border-bottom: none;
}

.sd-table tr:hover td {
    background: rgba(var(--muted), 0.03);
}

.sd-table tr.row-checked-in td {
    background: rgba(16, 185, 129, 0.02);
}

.sd-attendee-cell {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.sd-avatar {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: rgba(var(--brand), 0.14);
    color: rgb(var(--brand));
    font-weight: 800;
    font-size: 0.95rem;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.sd-attendee-name {
    font-weight: 800;
    color: rgb(var(--text));
    line-height: 1.3;
}

.sd-attendee-email {
    font-size: 0.78rem;
    color: rgb(var(--muted));
    line-height: 1.2;
}

.sd-code-badge {
    font-family: monospace;
    font-weight: 800;
    font-size: 0.85rem;
    background: rgba(var(--muted), 0.12);
    color: rgb(var(--text));
    padding: 0.25rem 0.55rem;
    border-radius: 6px;
    display: inline-block;
}

.sd-tier-sub {
    font-size: 0.78rem;
    color: rgb(var(--muted));
    margin-top: 0.2rem;
}

.sd-kind-badge {
    font-size: 0.7rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    padding: 0.16rem 0.45rem;
    border-radius: 6px;
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
}

.sd-kind--standard { background: rgba(100, 116, 139, 0.12); color: #475569; }
.sd-kind--duo { background: rgba(236, 72, 153, 0.12); color: #db2777; }
.sd-kind--group { background: rgba(249, 115, 22, 0.12); color: #ea580c; }
.sd-kind--seated { background: rgba(16, 185, 129, 0.14); color: #059669; }

.sd-seat-badge {
    font-size: 0.74rem;
    font-weight: 800;
    color: #065f46;
    background: rgba(16, 185, 129, 0.12);
    border: 1px solid rgba(16, 185, 129, 0.3);
    padding: 0.15rem 0.5rem;
    border-radius: 6px;
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
}

.sd-role-pill {
    font-size: 0.68rem;
    font-weight: 800;
    padding: 0.12rem 0.45rem;
    border-radius: 6px;
    background: rgba(14, 165, 233, 0.12);
    color: #0284c7;
}

/* ── Badges ── */
.sd-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    padding: 0.3rem 0.65rem;
    border-radius: 9999px;
    font-size: 0.78rem;
    font-weight: 800;
    letter-spacing: 0.02em;
    white-space: nowrap;
}

.sd-badge-paid {
    background: rgba(16, 185, 129, 0.12);
    color: #10b981;
    border: 1px solid rgba(16, 185, 129, 0.25);
}

.sd-badge-free {
    background: rgba(59, 130, 246, 0.12);
    color: #2563eb;
    border: 1px solid rgba(59, 130, 246, 0.25);
}

.sd-badge-pending {
    background: rgba(245, 158, 11, 0.12);
    color: #d97706;
    border: 1px solid rgba(245, 158, 11, 0.25);
}

.sd-badge-in {
    background: rgba(16, 185, 129, 0.15);
    color: #059669;
    border: 1px solid rgba(16, 185, 129, 0.3);
}

.sd-badge-not-in {
    background: rgba(var(--muted), 0.1);
    color: rgb(var(--muted));
    border: 1px solid rgba(var(--muted), 0.2);
}

.sd-badge-offline {
    background: rgba(245, 158, 11, 0.16) !important;
    color: #d97706 !important;
    border: 1px solid rgba(245, 158, 11, 0.38) !important;
}

:root[data-theme='dark'] .sd-badge-offline {
    color: #fcd34d !important;
}

/* ── Check-in Action Buttons ── */
.sd-btn-action {
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    padding: 0.55rem 1rem;
    border-radius: 10px;
    font-size: 0.82rem;
    font-weight: 800;
    cursor: pointer;
    border: none;
    transition: transform 0.15s ease, background 0.15s ease, opacity 0.15s ease;
    white-space: nowrap;
}

.sd-btn-action:hover:not(:disabled) {
    transform: translateY(-1px);
}

.sd-btn-action:disabled {
    opacity: 0.65;
    cursor: not-allowed;
    transform: none;
}

.sd-btn-override {
    background: linear-gradient(135deg, #10b981, #059669);
    color: #ffffff;
    box-shadow: 0 2px 10px rgba(16, 185, 129, 0.3);
}

.sd-btn-override:hover:not(:disabled) {
    background: linear-gradient(135deg, #059669, #047857);
}

.sd-btn-reset {
    background: rgba(239, 68, 68, 0.1);
    color: #ef4444;
    border: 1px solid rgba(239, 68, 68, 0.25);
}

.sd-btn-reset:hover:not(:disabled) {
    background: rgba(239, 68, 68, 0.18);
}

.sd-spinner {
    display: inline-block;
    width: 12px;
    height: 12px;
    border: 2px solid currentColor;
    border-right-color: transparent;
    border-radius: 50%;
    animation: sdSpin 0.7s linear infinite;
    vertical-align: middle;
}

/* ── Mobile Cards List (≤ 768px) ── */
.sd-cards-list {
    display: none;
    flex-direction: column;
    gap: 0.85rem;
}

.sd-mobile-card {
    background: rgb(var(--card));
    border: 1px solid rgba(var(--muted), 0.18);
    border-radius: 14px;
    padding: 1rem 1.15rem;
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    transition: border-color 0.2s ease, background 0.2s ease;
}

.sd-mobile-card.card-checked-in {
    border-color: rgba(16, 185, 129, 0.35);
    background: rgba(16, 185, 129, 0.02);
}

.sd-card-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.65rem;
}

.sd-card-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.65rem;
    padding: 0.75rem;
    background: rgba(var(--muted), 0.05);
    border-radius: 12px;
}

.sd-grid-item {
    display: flex;
    flex-direction: column;
    gap: 0.15rem;
}

.sd-grid-label {
    font-size: 0.68rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: rgb(var(--muted));
}

.sd-mobile-action {
    width: 100%;
}

.sd-mobile-action .sd-btn-action {
    width: 100%;
    height: 44px;
    justify-content: center;
    border-radius: 12px;
    font-size: 0.88rem;
    font-weight: 700;
}

/* ── Empty State ── */
.sd-empty-state {
    text-align: center;
    padding: 3.5rem 1.5rem;
    background: rgba(var(--muted), 0.03);
    border-radius: 16px;
    border: 1px dashed rgba(var(--muted), 0.2);
}

.sd-empty-icon {
    font-size: 3rem;
    display: block;
    margin-bottom: 0.75rem;
}

.sd-empty-title {
    margin: 0 0 0.4rem;
    font-size: 1.2rem;
    font-weight: 800;
    color: rgb(var(--text));
}

.sd-empty-desc {
    margin: 0 0 1.25rem;
    font-size: 0.88rem;
    color: rgb(var(--muted));
    max-width: 480px;
    margin-left: auto;
    margin-right: auto;
}

/* ── Toast Notification Container ── */
.sd-toast-container {
    position: fixed;
    bottom: 1.5rem;
    right: 1.5rem;
    z-index: 999999;
    display: flex;
    flex-direction: column;
    gap: 0.65rem;
    pointer-events: none;
    max-width: 380px;
    width: calc(100% - 3rem);
}

.sd-toast {
    pointer-events: auto;
    background: rgb(var(--card));
    border: 1px solid rgba(var(--muted), 0.2);
    color: rgb(var(--text));
    padding: 0.85rem 1.15rem;
    border-radius: 12px;
    font-size: 0.86rem;
    font-weight: 700;
    box-shadow: 0 10px 25px -4px rgba(0, 0, 0, 0.15);
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.75rem;
    animation: sdToastSlideIn 0.25s cubic-bezier(0.16, 1, 0.3, 1);
    transition: opacity 0.25s ease, transform 0.25s ease;
}

.sd-toast.toast-success { border-left: 4px solid #10b981; }
.sd-toast.toast-warning { border-left: 4px solid #f59e0b; }
.sd-toast.toast-error { border-left: 4px solid #ef4444; }

@keyframes sdToastSlideIn {
    from { opacity: 0; transform: translateY(12px) scale(0.96); }
    to { opacity: 1; transform: translateY(0) scale(1); }
}

@keyframes sdFadeIn {
    from { opacity: 0; transform: translateY(-4px); }
    to { opacity: 1; transform: translateY(0); }
}

@keyframes sdSpin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

/* ═══════════════════════════════════════════════════════════════════
   RESPONSIVENESS (Desktop, Tablet, Mobile)
   ═══════════════════════════════════════════════════════════════════ */
@media (max-width: 1024px) {
    .sd-stats-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 0.85rem;
    }
    .sd-hero {
        padding: 1.25rem;
    }
}

@media (max-width: 768px) {
    .sd-table-wrap {
        display: none !important;
    }
    .sd-cards-list {
        display: flex !important;
    }
    .sd-hero {
        flex-direction: column;
        align-items: stretch;
        gap: 1rem;
    }
    .sd-hero-actions {
        width: 100%;
        flex-direction: column;
        align-items: stretch;
    }
    .sd-btn-issue {
        width: 100%;
        justify-content: center;
        height: 46px;
    }
    .sd-switcher-box {
        width: 100%;
        box-sizing: border-box;
    }
    .sd-search-form {
        flex-direction: column;
        align-items: stretch;
    }
    .sd-search-input-wrap {
        min-width: 100%;
    }
    .sd-clear-btn {
        width: 100%;
        justify-content: center;
        height: 46px;
        box-sizing: border-box;
    }
    .sd-content-card {
        padding: 1rem;
        border-radius: 16px;
    }
    .sd-stats-grid {
        grid-template-columns: 1fr 1fr;
        gap: 0.65rem;
    }
    .sd-stat-card {
        padding: 1rem;
        border-radius: 14px;
    }
    .sd-stat-val {
        font-size: 1.35rem;
    }
    .sd-cross-match {
        flex-direction: column;
        align-items: stretch;
    }
    .sd-cross-match-btn {
        width: 100%;
        justify-content: center;
        height: 42px;
    }
}

@media (max-width: 420px) {
    .sd-stats-grid {
        grid-template-columns: 1fr;
        gap: 0.5rem;
    }
    .sd-stat-card {
        padding: 0.85rem;
    }
    .sd-card-grid {
        grid-template-columns: 1fr;
    }
    .sd-hero-title {
        font-size: 1.25rem;
    }
}
</style>
@endsection

@section('content')
<div class="sd-wrapper">
    <!-- Active Event Context Banner -->
    <header class="sd-hero">
        <div class="sd-hero-left">
            <div class="sd-badge-row">
                <span class="sd-panel-badge">🎧 Workstation — Customer Support</span>
                <span class="sd-live-status">
                    <span class="sd-pulse-dot"></span>
                    Live Workspace
                </span>
                <span id="sd-net-pill" class="sd-net-pill online">🟢 Online</span>
            </div>
            <h1 class="sd-hero-title"><?= e($event['name'] ?? 'Event Workspace') ?></h1>
            <div class="sd-hero-meta">
                <?php if ($eventTime): ?>
                    <span>📅 <?= date('D, M d, Y • g:i A', $eventTime) ?></span>
                    <span class="sd-meta-divider">•</span>
                <?php endif; ?>
                <span>📍 <?= e($event['location'] ?? 'Venue') ?></span>
            </div>
        </div>

        <div class="sd-hero-actions">
            <button type="button" id="btn-open-box-office-support" class="sd-btn-issue">
                <span>🎟️</span> Issue Gate Ticket
            </button>
            <?php if (count($assignments) > 1): ?>
                <div class="sd-switcher-box">
                    <span class="sd-switcher-label">Switch Event</span>
                    <form method="POST" action="{{ route('teams/switch-event') }}" style="margin:0;">
                        <?= csrf_field() ?>
                        <select name="event_id" onchange="this.form.submit()" class="sd-switcher-select">
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
    </header>

    <!-- Offline Sync Banner (Appears when offline updates are queued) -->
    <div id="sd-sync-bar" class="sd-sync-bar" style="display:none;">
        <div class="sd-sync-bar-left">
            <span id="sd-sync-icon">⚡</span>
            <span id="sd-sync-msg">1 offline check-in pending sync...</span>
        </div>
        <button type="button" id="sd-btn-sync-now" class="sd-btn-sync-now">
            Sync Now ↻
        </button>
    </div>

    <!-- Quick Stats Grid -->
    <?php if (!empty($stats)): ?>
        <section class="sd-stats-grid">
            <article class="sd-stat-card">
                <span class="sd-stat-label">Total Issued</span>
                <span class="sd-stat-val" id="stat-total-tickets"><?= number_format($stats['total_tickets']) ?></span>
                <span class="sd-stat-sub">Paid &amp; verified tickets</span>
            </article>
            <article class="sd-stat-card stat-green">
                <span class="sd-stat-label">Checked In</span>
                <span class="sd-stat-val" id="stat-checked-in"><?= number_format($stats['checked_in']) ?></span>
                <span class="sd-stat-sub">Admitted at gate</span>
            </article>
            <article class="sd-stat-card stat-amber">
                <span class="sd-stat-label">Remaining Expected</span>
                <span class="sd-stat-val" id="stat-remaining"><?= number_format($stats['remaining']) ?></span>
                <span class="sd-stat-sub">Not checked in yet</span>
            </article>
            <article class="sd-stat-card stat-brand">
                <span class="sd-stat-label">Attendance Rate</span>
                <span class="sd-stat-val" id="stat-percentage"><?= number_format($stats['percentage'], 1) ?>%</span>
                <span class="sd-stat-sub">Of total passes</span>
            </article>
        </section>
    <?php endif; ?>

    <!-- Cross-Event Match Notification -->
    <?php if (!empty($other_match)): ?>
        <div class="sd-cross-match">
            <div class="sd-cross-match-left">
                <span class="sd-cross-match-icon">💡</span>
                <div class="sd-cross-match-info">
                    <strong>Pass Registered for Another Assigned Event:</strong><br />
                    Ticket <span class="sd-cross-match-code"><?= e($other_match['ticket_code']) ?></span> for <strong><?= e($other_match['attendee_name']) ?></strong> is active on <strong><?= e($other_match['event_name']) ?></strong>.
                </div>
            </div>
            <form method="POST" action="{{ route('teams/switch-event') }}" style="margin:0; flex-shrink:0;">
                <?= csrf_field() ?>
                <input type="hidden" name="event_id" value="<?= $other_match['event_id'] ?>">
                <button type="submit" class="sd-cross-match-btn">
                    Switch to <?= e($other_match['event_name']) ?> →
                </button>
            </form>
        </div>
    <?php endif; ?>

    <!-- Instant Search Toolbar -->
    <section class="sd-search-card">
        <form id="sd-search-form" class="sd-search-form" onsubmit="return false;">
            <div class="sd-search-input-wrap">
                <span class="sd-search-icon">🔍</span>
                <input type="text" id="sd-search-input" name="q" value="<?= e($query ?? '') ?>" 
                    placeholder="Search attendee by name, email, or pass code (e.g. EV-1001 or Alex)..."
                    class="sd-search-input" autocomplete="off" spellcheck="false">
                <span id="sd-search-spinner" class="sd-search-spinner" style="display:none;">↻</span>
            </div>
            <button type="button" id="sd-clear-btn" class="sd-clear-btn" style="<?= empty($query) ? 'display:none;' : '' ?>">
                ✕ Clear
            </button>
        </form>
        <div class="sd-search-meta-bar">
            <span id="sd-search-mode-pill" class="sd-search-mode-pill online">🟢 Live Search &amp; Offline Cache Ready</span>
            <span id="sd-cache-sync-time" class="sd-cache-sync-time">Instant Filtering Active</span>
        </div>
    </section>

    <!-- Attendees / Tickets Section -->
    <section class="sd-content-card">
        <div class="sd-card-header">
            <div>
                <h2 class="sd-card-title">Attendee Admission Passes</h2>
                <p class="sd-card-subtitle" id="sd-card-subtitle">
                    <?php if (!empty($query)): ?>
                        Showing results matching "<strong><?= e($query) ?></strong>" (<span id="sd-count-span"><?= count($tickets) ?></span> found)
                    <?php else: ?>
                        Displaying latest <span id="sd-count-span"><?= count($tickets) ?></span> purchased admission passes for this event
                    <?php endif; ?>
                </p>
            </div>
        </div>

        <!-- Empty State Container -->
        <div id="sd-empty-state" class="sd-empty-state" style="<?= empty($tickets) ? '' : 'display:none;' ?>">
            <span class="sd-empty-icon">🎟️</span>
            <h3 class="sd-empty-title">No Admission Passes Found</h3>
            <p class="sd-empty-desc" id="sd-empty-desc">
                <?php if (!empty($query)): ?>
                    No tickets match "<strong><?= e($query) ?></strong>" in this event. Check spelling or try searching by ticket code.
                <?php else: ?>
                    No tickets have been issued or purchased for this event yet.
                <?php endif; ?>
            </p>
            <button type="button" id="sd-btn-view-all" class="sd-clear-btn" style="display:inline-flex;">
                View All Attendees
            </button>
        </div>

        <!-- Desktop Table View (>= 769px) -->
        <div id="sd-table-wrap" class="sd-table-wrap" style="<?= empty($tickets) ? 'display:none;' : '' ?>">
            <table class="sd-table">
                <thead>
                    <tr>
                        <th style="width: 28%;">Attendee</th>
                        <th style="width: 22%;">Ticket Code &amp; Tier</th>
                        <th style="width: 15%;">Payment</th>
                        <th style="width: 18%;">Check-In Status</th>
                        <th style="width: 17%; text-align: right;">Gate Action</th>
                    </tr>
                </thead>
                <tbody id="sd-tickets-tbody">
                    <?php foreach ($tickets as $t):
                        $isCheckedIn = ($t['checked_in'] == 1);
                    ?>
                    <tr id="ticket-row-<?= e($t['id']) ?>" data-ticket-id="<?= e($t['id']) ?>" class="<?= $isCheckedIn ? 'row-checked-in' : '' ?>">
                        <td>
                            <div class="sd-attendee-cell">
                                <div class="sd-avatar">
                                    <?= strtoupper(substr($t['attendee_name'] ?? 'A', 0, 1)) ?>
                                </div>
                                <div>
                                    <div class="sd-attendee-name" style="display:flex; align-items:center; gap:0.35rem; flex-wrap:wrap;">
                                        <?= e($t['attendee_name']) ?>
                                        <?php if (($t['attendee_role'] ?? '') === 'plus_one'): ?>
                                            <span class="sd-role-pill">💑 Plus-One</span>
                                        <?php elseif (($t['attendee_role'] ?? '') === 'group_member'): ?>
                                            <span class="sd-role-pill">👥 Group</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="sd-attendee-email"><?= e($t['attendee_email']) ?></div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div style="display:flex; align-items:center; gap:0.35rem; flex-wrap:wrap;">
                                <span class="sd-code-badge"><?= e($t['id']) ?></span>
                                <span class="sd-kind-badge sd-kind--<?= e($t['ticket_kind'] ?? 'standard') ?>">
                                    <?= e(strtoupper($t['ticket_kind'] ?? 'standard')) ?>
                                </span>
                                <?php if (!empty($t['assigned_seat'])): ?>
                                    <span class="sd-seat-badge">📍 Seat <?= e($t['assigned_seat']) ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="sd-tier-sub">
                                <?= e($t['ticket_type_name'] ?? 'General Admission') ?>
                                <?php if (!empty($t['price'])): ?>
                                    <span>(<?= format_price($t['price']) ?>)</span>
                                <?php endif; ?>
                                <?php if (!empty($t['section_name']) || !empty($t['row_name'])): ?>
                                    &bull; <span>Sec <?= e($t['section_name'] ?: 'Reserved') ?> <?= !empty($t['row_name']) ? ('Row ' . e($t['row_name'])) : '' ?></span>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td>
                            <?php 
                                $tStatLower = strtolower(trim($t['status'] ?? ''));
                                $isLayawayBlocked = in_array($tStatLower, ['layaway', 'layaway expired', 'cancelled', 'refunded']);
                            ?>
                            <?php if ($tStatLower === 'paid'): ?>
                                <span class="sd-badge sd-badge-paid">Paid</span>
                            <?php elseif ($tStatLower === 'comped' || $tStatLower === 'free'): ?>
                                <span class="sd-badge sd-badge-free"><?= e(ucfirst($t['status'])) ?></span>
                            <?php elseif ($tStatLower === 'layaway'): ?>
                                <span class="sd-badge sd-badge-pending" title="Installment balance not yet completed">⏳ Layaway Unpaid</span>
                            <?php else: ?>
                                <span class="sd-badge sd-badge-pending"><?= e(ucfirst($t['status'])) ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="sd-status-cell">
                            <?php if ($isCheckedIn): ?>
                                <span class="sd-badge sd-badge-in">✓ Checked In</span>
                                <?php if (!empty($t['checked_in_at'])): ?>
                                    <div class="sd-tier-sub sd-checkin-time">
                                        <?= date('M d, H:i', strtotime($t['checked_in_at'])) ?>
                                    </div>
                                <?php endif; ?>
                            <?php elseif ($tStatLower === 'layaway'): ?>
                                <span class="sd-badge sd-badge-pending">🔒 Balance Due</span>
                            <?php else: ?>
                                <span class="sd-badge sd-badge-not-in">Not Arrived</span>
                            <?php endif; ?>
                        </td>
                        <td style="text-align: right;" class="sd-action-cell">
                            <?php if ($isLayawayBlocked && !$isCheckedIn): ?>
                                <button type="button" class="sd-btn-action" disabled style="opacity:0.55; cursor:not-allowed; background:rgba(var(--muted),0.12); color:rgb(var(--muted));" title="Cannot admit until payment is completed">
                                    🔒 Payment Incomplete
                                </button>
                            <?php else: ?>
                                <button type="button" 
                                    class="sd-btn-action <?= $isCheckedIn ? 'sd-btn-reset' : 'sd-btn-override' ?>"
                                    data-ticket-id="<?= e($t['id']) ?>"
                                    data-check-in="<?= $isCheckedIn ? '0' : '1' ?>"
                                    data-attendee-name="<?= e($t['attendee_name']) ?>">
                                    <?= $isCheckedIn ? '↩ Reset' : '✓ Override Entry' ?>
                                </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Mobile & Tablet Cards View (<= 768px) -->
        <div id="sd-cards-container" class="sd-cards-list" style="<?= empty($tickets) ? 'display:none;' : '' ?>">
            <?php foreach ($tickets as $t):
                $isCheckedIn = ($t['checked_in'] == 1);
            ?>
            <article id="ticket-card-<?= e($t['id']) ?>" class="sd-mobile-card <?= $isCheckedIn ? 'card-checked-in' : '' ?>" data-ticket-id="<?= e($t['id']) ?>">
                <div class="sd-card-top">
                    <div class="sd-attendee-cell">
                        <div class="sd-avatar">
                            <?= strtoupper(substr($t['attendee_name'] ?? 'A', 0, 1)) ?>
                        </div>
                        <div>
                            <div class="sd-attendee-name"><?= e($t['attendee_name']) ?></div>
                            <div class="sd-attendee-email"><?= e($t['attendee_email']) ?></div>
                        </div>
                    </div>
                    <div class="sd-status-cell">
                        <?php if ($isCheckedIn): ?>
                            <span class="sd-badge sd-badge-in">Checked In</span>
                        <?php else: ?>
                            <span class="sd-badge sd-badge-not-in">Not Arrived</span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="sd-card-grid">
                    <div class="sd-grid-item">
                        <span class="sd-grid-label">Pass Code</span>
                        <span class="sd-code-badge"><?= e($t['id']) ?></span>
                    </div>
                    <div class="sd-grid-item">
                        <span class="sd-grid-label">Tier</span>
                        <span style="font-weight:700; font-size:0.85rem; color:rgb(var(--text));">
                            <?= e($t['ticket_type_name'] ?? 'General Admission') ?>
                        </span>
                    </div>
                    <div class="sd-grid-item">
                        <span class="sd-grid-label">Payment</span>
                        <span style="font-weight:700; font-size:0.82rem; color:<?= strcasecmp($t['status'], 'Paid') === 0 ? '#10b981' : 'inherit' ?>;">
                            <?= e(ucfirst($t['status'])) ?>
                        </span>
                    </div>
                    <div class="sd-grid-item">
                        <span class="sd-grid-label">Purchased</span>
                        <span style="font-size:0.82rem; color:rgb(var(--muted));">
                            <?= !empty($t['purchase_date']) ? date('M d, Y', strtotime($t['purchase_date'])) : '—' ?>
                        </span>
                    </div>
                </div>

                <div class="sd-mobile-action">
                    <?php 
                        $mStatLower = strtolower(trim($t['status'] ?? ''));
                        $mLayawayBlocked = in_array($mStatLower, ['layaway', 'layaway expired', 'cancelled', 'refunded']);
                    ?>
                    <?php if ($mLayawayBlocked && !$isCheckedIn): ?>
                        <button type="button" class="sd-btn-action" disabled style="opacity:0.55; cursor:not-allowed; background:rgba(var(--muted),0.12); color:rgb(var(--muted));">
                            🔒 Payment Incomplete (<?= e(ucfirst($t['status'])) ?>)
                        </button>
                    <?php else: ?>
                        <button type="button" 
                            class="sd-btn-action <?= $isCheckedIn ? 'sd-btn-reset' : 'sd-btn-override' ?>"
                            data-ticket-id="<?= e($t['id']) ?>"
                            data-check-in="<?= $isCheckedIn ? '0' : '1' ?>"
                            data-attendee-name="<?= e($t['attendee_name']) ?>">
                            <?= $isCheckedIn ? '↩ Reset Check-In Status' : '🎟️ Override Check-In &amp; Admit' ?>
                        </button>
                    <?php endif; ?>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
    </section>
</div>

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

<!-- Toast Notification Container -->
<div id="sd-toast-container" class="sd-toast-container"></div>

<script>
(function() {
    // ═════════════════════════════════════════════════════════════════
    // Core Support Desk Workstation State & Offline Engine
    // ═════════════════════════════════════════════════════════════════
    const EVENT_ID = <?= (int)($event['id'] ?? 0) ?>;
    const CSRF_TOKEN = "<?= csrf_token() ?>";
    const CACHE_KEY = 'sd_attendees_cache_' + EVENT_ID;
    const QUEUE_KEY = 'sd_offline_queue_' + EVENT_ID;

    // Local in-memory attendees dictionary (keyed by ticket ID)
    let attendeesMap = new Map();
    let isSyncingQueue = false;
    let searchDebounceTimer = null;
    let activeSearchQuery = <?= json_encode($query ?? '') ?>;

    // DOM Elements
    const searchInput = document.getElementById('sd-search-input');
    const searchSpinner = document.getElementById('sd-search-spinner');
    const clearBtn = document.getElementById('sd-clear-btn');
    const netPill = document.getElementById('sd-net-pill');
    const syncBar = document.getElementById('sd-sync-bar');
    const syncMsg = document.getElementById('sd-sync-msg');
    const btnSyncNow = document.getElementById('sd-btn-sync-now');
    const modePill = document.getElementById('sd-search-mode-pill');
    const tableWrap = document.getElementById('sd-table-wrap');
    const tbody = document.getElementById('sd-tickets-tbody');
    const cardsContainer = document.getElementById('sd-cards-container');
    const emptyState = document.getElementById('sd-empty-state');
    const emptyDesc = document.getElementById('sd-empty-desc');
    const countSpan = document.getElementById('sd-count-span');
    const subtitleEl = document.getElementById('sd-card-subtitle');
    const toastContainer = document.getElementById('sd-toast-container');
    const btnViewAll = document.getElementById('sd-btn-view-all');

    // Stats elements
    const statCheckedIn = document.getElementById('stat-checked-in');
    const statRemaining = document.getElementById('stat-remaining');
    const statPercentage = document.getElementById('stat-percentage');

    // ── Helper: Escape HTML ──
    function esc(str) {
        if (str === null || str === undefined) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    // ── Toast Notification System ──
    function showToast(message, type = 'success', duration = 3200) {
        if (!toastContainer) return;
        const toast = document.createElement('div');
        toast.className = `sd-toast toast-${type}`;
        toast.innerHTML = `<span>${esc(message)}</span><span style="opacity:0.6; cursor:pointer;" onclick="this.parentNode.remove()">✕</span>`;
        toastContainer.appendChild(toast);

        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateY(10px) scale(0.95)';
            setTimeout(() => toast.remove(), 260);
        }, duration);
    }

    // ── Network Status Monitoring ──
    function updateNetworkStatus() {
        const isOnline = navigator.onLine;
        if (netPill) {
            netPill.className = `sd-net-pill ${isOnline ? 'online' : 'offline'}`;
            netPill.innerHTML = isOnline ? '🟢 Online' : '⚡ Offline Mode';
        }
        if (modePill) {
            modePill.className = `sd-search-mode-pill ${isOnline ? 'online' : 'offline'}`;
            modePill.innerHTML = isOnline 
                ? '🟢 Live Search &amp; Offline Cache Ready' 
                : '⚡ Offline Search (Local Cache Active)';
        }
    }

    // ── Local Storage Cache Management ──
    function loadLocalCache() {
        try {
            const raw = localStorage.getItem(CACHE_KEY);
            if (raw) {
                const list = JSON.parse(raw);
                if (Array.isArray(list)) {
                    list.forEach(t => {
                        if (t && t.id) attendeesMap.set(String(t.id), t);
                    });
                }
            }
        } catch (e) {}

        // Merge initial PHP tickets into cache
        const initialTickets = <?= json_encode($tickets) ?>;
        if (Array.isArray(initialTickets)) {
            initialTickets.forEach(t => {
                if (t && t.id) attendeesMap.set(String(t.id), t);
            });
            saveLocalCache();
        }
    }

    function saveLocalCache() {
        try {
            const list = Array.from(attendeesMap.values());
            localStorage.setItem(CACHE_KEY, JSON.stringify(list));
        } catch (e) {}
    }

    // Pre-cache all attendees in the background on load
    function prefetchFullAttendeeCache() {
        if (!navigator.onLine) return;
        fetch('<?= route('/teams/support-desk') ?>?ajax=1&all=1', {
            headers: { 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(res => {
            if (res.success && Array.isArray(res.tickets)) {
                res.tickets.forEach(t => {
                    if (t && t.id) {
                        // Keep any pending local offline check-in status
                        const existing = attendeesMap.get(String(t.id));
                        if (existing && existing._offline_pending) {
                            t.checked_in = existing.checked_in;
                            t.checked_in_at = existing.checked_in_at;
                            t._offline_pending = true;
                        }
                        attendeesMap.set(String(t.id), t);
                    }
                });
                saveLocalCache();
            }
        })
        .catch(() => {});
    }

    // ── Offline Queue Management ──
    function getOfflineQueue() {
        try {
            return JSON.parse(localStorage.getItem(QUEUE_KEY) || '[]');
        } catch (e) {
            return [];
        }
    }

    function setOfflineQueue(queue) {
        try {
            localStorage.setItem(QUEUE_KEY, JSON.stringify(queue));
        } catch (e) {}
        updateSyncBar();
    }

    function enqueueOfflineCheckin(ticketId, checkInState, attendeeName) {
        const queue = getOfflineQueue();
        const existingIdx = queue.findIndex(item => String(item.ticket_id) === String(ticketId));
        const item = {
            ticket_id: String(ticketId),
            check_in: checkInState ? 1 : 0,
            attendee_name: attendeeName,
            queued_at: new Date().toISOString()
        };

        if (existingIdx >= 0) {
            queue[existingIdx] = item;
        } else {
            queue.push(item);
        }
        setOfflineQueue(queue);
    }

    function updateSyncBar() {
        if (!syncBar) return;
        const queue = getOfflineQueue();
        if (queue.length > 0) {
            syncBar.style.display = 'flex';
            if (syncMsg) {
                syncMsg.textContent = `${queue.length} offline check-in update${queue.length > 1 ? 's' : ''} pending sync with server`;
            }
        } else {
            syncBar.style.display = 'none';
        }
    }

    // ── Batch Sync Offline Check-Ins ──
    function syncOfflineQueue() {
        if (isSyncingQueue || !navigator.onLine) return;
        const queue = getOfflineQueue();
        if (queue.length === 0) {
            updateSyncBar();
            return;
        }

        isSyncingQueue = true;
        if (btnSyncNow) {
            btnSyncNow.disabled = true;
            btnSyncNow.innerHTML = '<span class="sd-spinner"></span> Syncing...';
        }

        fetch('<?= route('/teams/support-action') ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN
            },
            body: JSON.stringify({
                action: 'batch_override_sync',
                items: queue,
                ajax: 1,
                csrf_token: CSRF_TOKEN
            })
        })
        .then(r => r.json())
        .then(res => {
            isSyncingQueue = false;
            if (btnSyncNow) {
                btnSyncNow.disabled = false;
                btnSyncNow.textContent = 'Sync Now ↻';
            }

            if (res.success) {
                setOfflineQueue([]);
                // Clear offline pending flag in local memory
                attendeesMap.forEach(t => {
                    delete t._offline_pending;
                });
                saveLocalCache();

                // Convert all pending offline badges to clean green badges
                document.querySelectorAll('.sd-badge-offline').forEach(badge => {
                    badge.classList.remove('sd-badge-offline');
                    badge.textContent = '✓ Checked In';
                });

                if (res.stats) updateStatsDom(res.stats);
                showToast(`✓ ${res.message || 'Offline updates synced successfully!'}`, 'success', 3800);
            } else {
                showToast(`✕ Sync failed: ${res.message}`, 'error');
            }
        })
        .catch(() => {
            isSyncingQueue = false;
            if (btnSyncNow) {
                btnSyncNow.disabled = false;
                btnSyncNow.textContent = 'Sync Now ↻';
            }
        });
    }

    // ── Stats DOM Updating ──
    function updateStatsDom(stats) {
        if (!stats) return;
        if (statCheckedIn && stats.checked_in !== undefined) {
            statCheckedIn.textContent = Number(stats.checked_in).toLocaleString();
        }
        if (statRemaining && stats.remaining !== undefined) {
            statRemaining.textContent = Number(stats.remaining).toLocaleString();
        }
        if (statPercentage && stats.percentage !== undefined) {
            statPercentage.textContent = Number(stats.percentage).toFixed(1) + '%';
        }
    }

    function adjustStatsOptimistically(isCheckingIn) {
        if (!statCheckedIn || !statRemaining) return;
        let cIn = parseInt(statCheckedIn.textContent.replace(/,/g, ''), 10) || 0;
        let rem = parseInt(statRemaining.textContent.replace(/,/g, ''), 10) || 0;

        if (isCheckingIn) {
            cIn += 1;
            rem = Math.max(0, rem - 1);
        } else {
            cIn = Math.max(0, cIn - 1);
            rem += 1;
        }

        statCheckedIn.textContent = cIn.toLocaleString();
        statRemaining.textContent = rem.toLocaleString();

        const totalEl = document.getElementById('stat-total-tickets');
        if (totalEl && statPercentage) {
            const tot = parseInt(totalEl.textContent.replace(/,/g, ''), 10) || (cIn + rem);
            if (tot > 0) {
                statPercentage.textContent = ((cIn / tot) * 100).toFixed(1) + '%';
            }
        }
    }

    // ── Apply Check-In UI DOM Changes in Place (No Reload!) ──
    function applyCheckinDomUpdate(ticketId, isCheckedIn, timeStr, isOffline = false) {
        const strId = String(ticketId);

        // Update Desktop Row
        const row = document.getElementById('ticket-row-' + strId);
        if (row) {
            if (isCheckedIn) {
                row.classList.add('row-checked-in');
            } else {
                row.classList.remove('row-checked-in');
            }

            const statusCell = row.querySelector('.sd-status-cell');
            if (statusCell) {
                if (isCheckedIn) {
                    const badgeClass = isOffline ? 'sd-badge sd-badge-in sd-badge-offline' : 'sd-badge sd-badge-in';
                    const badgeText = isOffline ? '⚡ Checked In (Offline)' : '✓ Checked In';
                    statusCell.innerHTML = `
                        <span class="${badgeClass}">${badgeText}</span>
                        <div class="sd-tier-sub sd-checkin-time">${esc(timeStr || 'Just now')}</div>
                    `;
                } else {
                    statusCell.innerHTML = `<span class="sd-badge sd-badge-not-in">Not Arrived</span>`;
                }
            }

            const btn = row.querySelector('.sd-btn-action');
            if (btn) {
                btn.className = `sd-btn-action ${isCheckedIn ? 'sd-btn-reset' : 'sd-btn-override'}`;
                btn.dataset.checkIn = isCheckedIn ? '0' : '1';
                btn.innerHTML = isCheckedIn ? '↩ Reset' : '✓ Override Entry';
                btn.disabled = false;
            }
        }

        // Update Mobile Card
        const card = document.getElementById('ticket-card-' + strId);
        if (card) {
            if (isCheckedIn) {
                card.classList.add('card-checked-in');
            } else {
                card.classList.remove('card-checked-in');
            }

            const statusCell = card.querySelector('.sd-status-cell');
            if (statusCell) {
                if (isCheckedIn) {
                    const badgeClass = isOffline ? 'sd-badge sd-badge-in sd-badge-offline' : 'sd-badge sd-badge-in';
                    const badgeText = isOffline ? '⚡ Checked In (Offline)' : 'Checked In';
                    statusCell.innerHTML = `<span class="${badgeClass}">${badgeText}</span>`;
                } else {
                    statusCell.innerHTML = `<span class="sd-badge sd-badge-not-in">Not Arrived</span>`;
                }
            }

            const btn = card.querySelector('.sd-btn-action');
            if (btn) {
                btn.className = `sd-btn-action ${isCheckedIn ? 'sd-btn-reset' : 'sd-btn-override'}`;
                btn.dataset.checkIn = isCheckedIn ? '0' : '1';
                btn.innerHTML = isCheckedIn ? '↩ Reset Check-In Status' : '🎟️ Override Check-In &amp; Admit';
                btn.disabled = false;
            }
        }
    }

    function updateLocalTicketState(ticketId, isCheckedIn, timeStr, isOffline = false) {
        const strId = String(ticketId);
        const ticket = attendeesMap.get(strId);
        if (ticket) {
            ticket.checked_in = isCheckedIn ? 1 : 0;
            ticket.checked_in_at = isCheckedIn ? (timeStr || new Date().toISOString()) : null;
            if (isOffline) {
                ticket._offline_pending = true;
            } else {
                delete ticket._offline_pending;
            }
            attendeesMap.set(strId, ticket);
            saveLocalCache();
        }
    }

    // ── Check-In Override Action Handler (AJAX + Offline Fallback) ──
    function handleCheckinClick(btn) {
        if (!btn || btn.disabled) return;

        const ticketId = btn.dataset.ticketId;
        const currentCheckIn = btn.dataset.checkIn === '0'; // If '0', currently checked in and will reset
        const nextCheckIn = btn.dataset.checkIn === '1';    // If '1', currently not checked in and will admit
        const attendeeName = btn.dataset.attendeeName || 'Attendee';

        // Set pending UI state on button
        btn.disabled = true;
        const originalHtml = btn.innerHTML;
        btn.innerHTML = '<span class="sd-spinner"></span> Updating...';

        // Check network status
        if (!navigator.onLine) {
            // Instant Offline Processing
            setTimeout(() => {
                applyCheckinDomUpdate(ticketId, nextCheckIn, 'Offline Pending', true);
                updateLocalTicketState(ticketId, nextCheckIn, 'Offline Pending', true);
                enqueueOfflineCheckin(ticketId, nextCheckIn, attendeeName);
                adjustStatsOptimistically(nextCheckIn);
                showToast(`⚡ Check-in saved offline. Will sync when reconnected.`, 'warning');
            }, 120);
            return;
        }

        // Online AJAX request with 3.5s timeout for resilient bad network recovery
        const controller = new AbortController();
        const timeoutTimer = setTimeout(() => controller.abort(), 3500);

        fetch('<?= route('/teams/support-action') ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN
            },
            body: JSON.stringify({
                action: 'override_checkin',
                ticket_id: ticketId,
                check_in: nextCheckIn ? 1 : 0,
                ajax: 1,
                csrf_token: CSRF_TOKEN
            }),
            signal: controller.signal
        })
        .then(r => r.json())
        .then(res => {
            clearTimeout(timeoutTimer);
            btn.disabled = false;

            if (res.success) {
                applyCheckinDomUpdate(ticketId, nextCheckIn, res.checked_in_at || 'Just now', false);
                updateLocalTicketState(ticketId, nextCheckIn, res.checked_in_at, false);
                if (res.stats) updateStatsDom(res.stats);
                showToast(`✓ ${nextCheckIn ? 'Admitted' : 'Reset check-in for'} ${attendeeName}`, 'success');
            } else {
                btn.innerHTML = originalHtml;
                showToast(`✕ ${res.message || 'Operation failed'}`, 'error');
            }
        })
        .catch(() => {
            clearTimeout(timeoutTimer);
            // Bad network / timeout -> Fallback to Offline Queue!
            applyCheckinDomUpdate(ticketId, nextCheckIn, 'Offline Pending', true);
            updateLocalTicketState(ticketId, nextCheckIn, 'Offline Pending', true);
            enqueueOfflineCheckin(ticketId, nextCheckIn, attendeeName);
            adjustStatsOptimistically(nextCheckIn);
            showToast(`⚡ Network slow/offline. Saved locally &amp; queued for sync.`, 'warning');
        });
    }

    // ── Render Tickets (Desktop Table & Mobile Cards) ──
    function renderTicketsList(list, query = '') {
        if (!tbody || !cardsContainer) return;

        if (!list || list.length === 0) {
            if (tableWrap) tableWrap.style.display = 'none';
            if (cardsContainer) cardsContainer.style.display = 'none';
            if (emptyState) {
                emptyState.style.display = 'block';
                if (emptyDesc) {
                    emptyDesc.innerHTML = query 
                        ? `No tickets match "<strong>${esc(query)}</strong>" in this event. Check spelling or try searching by pass code.`
                        : `No tickets have been issued or purchased for this event yet.`;
                }
            }
            if (countSpan) countSpan.textContent = '0';
            if (subtitleEl) {
                subtitleEl.innerHTML = query 
                    ? `Showing results matching "<strong>${esc(query)}</strong>" (0 found)`
                    : `Displaying 0 purchased admission passes for this event`;
            }
            return;
        }

        if (emptyState) emptyState.style.display = 'none';
        if (tableWrap) tableWrap.style.display = 'block';
        if (cardsContainer) cardsContainer.style.display = 'flex';

        if (countSpan) countSpan.textContent = String(list.length);
        if (subtitleEl) {
            subtitleEl.innerHTML = query
                ? `Showing results matching "<strong>${esc(query)}</strong>" (${list.length} found)`
                : `Displaying latest ${list.length} purchased admission passes for this event`;
        }

        // Render Desktop Table Rows
        let tableHtml = '';
        list.forEach(t => {
            const isCheckedIn = (t.checked_in == 1);
            const isOffline = !!t._offline_pending;
            const initial = esc(String(t.attendee_name || 'A').charAt(0).toUpperCase());
            const priceHtml = t.price ? `<span>(${esc(t.price)})</span>` : '';
            
            let statusBadge = '<span class="sd-badge sd-badge-pending">Pending</span>';
            const statLower = String(t.status || '').toLowerCase();
            const isLayawayBlocked = ['layaway', 'layaway expired', 'cancelled', 'refunded'].includes(statLower);
            if (statLower === 'paid') {
                statusBadge = '<span class="sd-badge sd-badge-paid">Paid</span>';
            } else if (statLower === 'comped' || statLower === 'free') {
                statusBadge = `<span class="sd-badge sd-badge-free">${esc(t.status)}</span>`;
            } else if (statLower === 'layaway') {
                statusBadge = '<span class="sd-badge sd-badge-pending" title="Installment balance not yet completed">⏳ Layaway Unpaid</span>';
            } else if (statLower) {
                statusBadge = `<span class="sd-badge sd-badge-pending">${esc(t.status)}</span>`;
            }

            let checkinBadge = '<span class="sd-badge sd-badge-not-in">Not Arrived</span>';
            if (isCheckedIn) {
                const bClass = isOffline ? 'sd-badge sd-badge-in sd-badge-offline' : 'sd-badge sd-badge-in';
                const bText = isOffline ? '⚡ Checked In (Offline)' : '✓ Checked In';
                const timeStr = t.checked_in_at ? `<div class="sd-tier-sub sd-checkin-time">${esc(t.checked_in_at)}</div>` : '';
                checkinBadge = `<span class="${bClass}">${bText}</span>${timeStr}`;
            } else if (statLower === 'layaway') {
                checkinBadge = '<span class="sd-badge sd-badge-pending">🔒 Balance Due</span>';
            }

            const kind = esc(String(t.ticket_kind || 'standard').toLowerCase());
            const role = String(t.attendee_role || 'primary').toLowerCase();
            let rolePill = '';
            if (role === 'plus_one') rolePill = '<span class="sd-role-pill">💑 Plus-One</span>';
            else if (role === 'group_member') rolePill = '<span class="sd-role-pill">👥 Group</span>';

            let seatBadge = t.assigned_seat ? `<span class="sd-seat-badge">📍 Seat ${esc(t.assigned_seat)}</span>` : '';
            let secRow = (t.section_name || t.row_name) ? ` &bull; <span>Sec ${esc(t.section_name || 'Reserved')}${t.row_name ? ' Row ' + esc(t.row_name) : ''}</span>` : '';

            tableHtml += `
            <tr id="ticket-row-${esc(t.id)}" data-ticket-id="${esc(t.id)}" class="${isCheckedIn ? 'row-checked-in' : ''}">
                <td>
                    <div class="sd-attendee-cell">
                        <div class="sd-avatar">${initial}</div>
                        <div>
                            <div class="sd-attendee-name" style="display:flex; align-items:center; gap:0.35rem; flex-wrap:wrap;">
                                ${esc(t.attendee_name)} ${rolePill}
                            </div>
                            <div class="sd-attendee-email">${esc(t.attendee_email)}</div>
                        </div>
                    </div>
                </td>
                <td>
                    <div style="display:flex; align-items:center; gap:0.35rem; flex-wrap:wrap;">
                        <span class="sd-code-badge">${esc(t.id)}</span>
                        <span class="sd-kind-badge sd-kind--${kind}">${esc(kind.toUpperCase())}</span>
                        ${seatBadge}
                    </div>
                    <div class="sd-tier-sub">
                        ${esc(t.ticket_type_name || 'General Admission')} ${priceHtml}${secRow}
                    </div>
                </td>
                <td>${statusBadge}</td>
                <td class="sd-status-cell">${checkinBadge}</td>
                <td style="text-align: right;" class="sd-action-cell">
                    ${isLayawayBlocked && !isCheckedIn ? `
                        <button type="button" class="sd-btn-action" disabled style="opacity:0.55; cursor:not-allowed; background:rgba(var(--muted),0.12); color:rgb(var(--muted));">
                            🔒 Payment Incomplete
                        </button>
                    ` : `
                        <button type="button" 
                            class="sd-btn-action ${isCheckedIn ? 'sd-btn-reset' : 'sd-btn-override'}"
                            data-ticket-id="${esc(t.id)}"
                            data-check-in="${isCheckedIn ? '0' : '1'}"
                            data-attendee-name="${esc(t.attendee_name)}">
                            ${isCheckedIn ? '↩ Reset' : '✓ Override Entry'}
                        </button>
                    `}
                </td>
            </tr>`;
        });
        tbody.innerHTML = tableHtml;

        // Render Mobile Cards
        let cardsHtml = '';
        list.forEach(t => {
            const isCheckedIn = (t.checked_in == 1);
            const isOffline = !!t._offline_pending;
            const initial = esc(String(t.attendee_name || 'A').charAt(0).toUpperCase());
            const bClass = isOffline ? 'sd-badge sd-badge-in sd-badge-offline' : 'sd-badge sd-badge-in';
            const bText = isOffline ? '⚡ Checked In' : 'Checked In';

            cardsHtml += `
            <article id="ticket-card-${esc(t.id)}" class="sd-mobile-card ${isCheckedIn ? 'card-checked-in' : ''}" data-ticket-id="${esc(t.id)}">
                <div class="sd-card-top">
                    <div class="sd-attendee-cell">
                        <div class="sd-avatar">${initial}</div>
                        <div>
                            <div class="sd-attendee-name">${esc(t.attendee_name)}</div>
                            <div class="sd-attendee-email">${esc(t.attendee_email)}</div>
                        </div>
                    </div>
                    <div class="sd-status-cell">
                        ${isCheckedIn ? `<span class="${bClass}">${bText}</span>` : '<span class="sd-badge sd-badge-not-in">Not Arrived</span>'}
                    </div>
                </div>

                <div class="sd-card-grid">
                    <div class="sd-grid-item">
                        <span class="sd-grid-label">Pass Code</span>
                        <span class="sd-code-badge">${esc(t.id)}</span>
                    </div>
                    <div class="sd-grid-item">
                        <span class="sd-grid-label">Tier</span>
                        <span style="font-weight:700; font-size:0.85rem; color:rgb(var(--text));">
                            ${esc(t.ticket_type_name || 'General Admission')}
                        </span>
                    </div>
                    <div class="sd-grid-item">
                        <span class="sd-grid-label">Payment</span>
                        <span style="font-weight:700; font-size:0.82rem; color:${String(t.status || '').toLowerCase() === 'paid' ? '#10b981' : 'inherit'};">
                            ${esc(t.status || 'Paid')}
                        </span>
                    </div>
                    <div class="sd-grid-item">
                        <span class="sd-grid-label">Purchased</span>
                        <span style="font-size:0.82rem; color:rgb(var(--muted));">
                            ${esc(t.purchase_date || '—')}
                        </span>
                    </div>
                </div>

                <div class="sd-mobile-action">
                    ${['layaway', 'layaway expired', 'cancelled', 'refunded'].includes(String(t.status || '').toLowerCase()) && !isCheckedIn ? `
                        <button type="button" class="sd-btn-action" disabled style="opacity:0.55; cursor:not-allowed; background:rgba(var(--muted),0.12); color:rgb(var(--muted));">
                            🔒 Payment Incomplete (${esc(t.status)})
                        </button>
                    ` : `
                        <button type="button" 
                            class="sd-btn-action ${isCheckedIn ? 'sd-btn-reset' : 'sd-btn-override'}"
                            data-ticket-id="${esc(t.id)}"
                            data-check-in="${isCheckedIn ? '0' : '1'}"
                            data-attendee-name="${esc(t.attendee_name)}">
                            ${isCheckedIn ? '↩ Reset Check-In Status' : '🎟️ Override Check-In &amp; Admit'}
                        </button>
                    `}
                </div>
            </article>`;
        });
        cardsContainer.innerHTML = cardsHtml;
    }

    // ── Local In-Memory / Cache Search ──
    function filterLocalTickets(term) {
        const all = Array.from(attendeesMap.values());
        if (!term) return all.slice(0, 100);

        const clean = term.toLowerCase().replace(/^ev-/i, '').trim();
        return all.filter(t => {
            const name = String(t.attendee_name || '').toLowerCase();
            const email = String(t.attendee_email || '').toLowerCase();
            const code = String(t.id || '').toLowerCase();
            const tier = String(t.ticket_type_name || '').toLowerCase();
            const seat = String(t.assigned_seat || '').toLowerCase();
            const kind = String(t.ticket_kind || '').toLowerCase();
            const sec = String(t.section_name || '').toLowerCase();
            return name.includes(clean) || email.includes(clean) || code.includes(clean) || tier.includes(clean) || seat.includes(clean) || kind.includes(clean) || sec.includes(clean);
        });
    }

    // ── Live Instant Search Execution ──
    function performSearch(term) {
        activeSearchQuery = term;
        if (clearBtn) {
            clearBtn.style.display = term ? 'inline-flex' : 'none';
        }

        // 1. Instant local zero-latency render first!
        const localMatches = filterLocalTickets(term);
        renderTicketsList(localMatches, term);

        // 2. If online, fetch fresh server results
        if (!navigator.onLine) {
            updateNetworkStatus();
            return;
        }

        if (searchSpinner) searchSpinner.style.display = 'inline-block';

        const searchController = new AbortController();
        const searchTimeout = setTimeout(() => searchController.abort(), 3000);

        fetch('<?= route('/teams/support-desk') ?>?q=' + encodeURIComponent(term) + '&ajax=1', {
            headers: { 'Accept': 'application/json' },
            signal: searchController.signal
        })
        .then(r => r.json())
        .then(res => {
            clearTimeout(searchTimeout);
            if (searchSpinner) searchSpinner.style.display = 'none';

            if (res.success && Array.isArray(res.tickets)) {
                // Merge server results into cache
                res.tickets.forEach(t => {
                    if (t && t.id) {
                        const existing = attendeesMap.get(String(t.id));
                        if (existing && existing._offline_pending) {
                            t.checked_in = existing.checked_in;
                            t.checked_in_at = existing.checked_in_at;
                            t._offline_pending = true;
                        }
                        attendeesMap.set(String(t.id), t);
                    }
                });
                saveLocalCache();

                // If user didn't type something else while request was in-flight, display
                if (activeSearchQuery === term) {
                    renderTicketsList(res.tickets, term);
                }
                if (res.stats) updateStatsDom(res.stats);
            }
        })
        .catch(() => {
            clearTimeout(searchTimeout);
            if (searchSpinner) searchSpinner.style.display = 'none';
            // Seamlessly keep displaying local matches
            updateNetworkStatus();
        });
    }

    // ── Setup Event Listeners ──
    document.addEventListener('DOMContentLoaded', function() {
        loadLocalCache();
        updateNetworkStatus();
        updateSyncBar();
        prefetchFullAttendeeCache();

        // Check if there are queued offline check-ins to sync on load
        if (navigator.onLine && getOfflineQueue().length > 0) {
            setTimeout(syncOfflineQueue, 1200);
        }

        // Live instant search as user types
        if (searchInput) {
            searchInput.addEventListener('input', function(e) {
                const term = e.target.value.trim();
                clearTimeout(searchDebounceTimer);
                searchDebounceTimer = setTimeout(() => performSearch(term), 240);
            });

            searchInput.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    searchInput.value = '';
                    performSearch('');
                }
            });
        }

        if (clearBtn) {
            clearBtn.addEventListener('click', function() {
                if (searchInput) searchInput.value = '';
                performSearch('');
                if (searchInput) searchInput.focus();
            });
        }

        if (btnViewAll) {
            btnViewAll.addEventListener('click', function() {
                if (searchInput) searchInput.value = '';
                performSearch('');
            });
        }

        // Sync Now button click
        if (btnSyncNow) {
            btnSyncNow.addEventListener('click', syncOfflineQueue);
        }

        // Event delegation for all Check-in Override buttons (No reload!)
        document.addEventListener('click', function(e) {
            const btn = e.target.closest('.sd-btn-action');
            if (btn && btn.dataset.ticketId) {
                e.preventDefault();
                e.stopPropagation();
                handleCheckinClick(btn);
            }
        });

        // Online & Offline Network Listeners
        window.addEventListener('online', function() {
            updateNetworkStatus();
            showToast('🟢 Connection restored. Auto-syncing...', 'success', 2500);
            syncOfflineQueue();
        });

        window.addEventListener('offline', function() {
            updateNetworkStatus();
            showToast('⚡ Network lost. Offline Mode active.', 'warning', 3000);
        });

        // Periodic background sync heartbeat (every 8 seconds)
        setInterval(function() {
            if (navigator.onLine && getOfflineQueue().length > 0 && !isSyncingQueue) {
                syncOfflineQueue();
            }
        }, 8000);

        // Box Office Ticket Issuance Modal Handling
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

                const formData = new FormData(formSupp);
                const payload = {
                    ticket_type_id: formData.get('ticket_type_id'),
                    attendee_name: formData.get('attendee_name'),
                    attendee_email: formData.get('attendee_email'),
                    quantity: formData.get('quantity'),
                    payment_method: formData.get('payment_method'),
                    mark_checked_in: formData.get('mark_checked_in') ? 1 : 0,
                    csrf_token: CSRF_TOKEN
                };

                fetch('<?= route('/teams/issue-gate-ticket') ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': CSRF_TOKEN
                    },
                    body: JSON.stringify(payload)
                })
                .then(r => r.json())
                .then(data => {
                    btnSubSupp.disabled = false;
                    btnSubSupp.textContent = '✓ Issue Ticket';
                    statusSupp.style.display = 'block';

                    if (data.success) {
                        statusSupp.innerHTML = `<span style="color:#10b981; font-weight:800;">✓ ${esc(data.message)}</span>`;
                        showToast(`✓ Ticket issued for ${payload.attendee_name}`, 'success');
                        setTimeout(() => {
                            modalSupp.style.display = 'none';
                            formSupp.reset();
                            // Refresh list and cache
                            performSearch(searchInput ? searchInput.value : '');
                        }, 1200);
                    } else {
                        statusSupp.innerHTML = `<span style="color:#ef4444; font-weight:800;">✕ ${esc(data.message)}</span>`;
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
})();
</script>
@endsection
