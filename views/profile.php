<?php

/**
 * Profile & Account Hub
 *
 * Clean, modern, responsive account management hub for Attendees, Hosts, and Admins.
 *
 * @package Eventlyy
 * @subpackage Views/Profile
 * @author Celio Natti
 * @version 3.1.0
 */

// Ensure user object is safely available
if (!isset($user) || (!is_object($user) && !is_array($user))) {
    $user = (object)[
        'name' => '',
        'email' => '',
    ];
} elseif (is_array($user)) {
    $user = (object)$user;
}
if (!isset($user->name)) {
    $user->name = '';
}
if (!isset($user->email)) {
    $user->email = '';
}

// Generate initials from name
$initials = '';
$nameParts = array_values(array_filter(explode(' ', trim($user->name ?? ''))));
if (!empty($nameParts)) {
    $initials .= strtoupper(substr($nameParts[0], 0, 1));
    if (count($nameParts) > 1) {
        $initials .= strtoupper(substr(end($nameParts), 0, 1));
    }
} else {
    $initials = 'U';
}

// Dynamic avatar hue from name
$avatarHue = abs(crc32($user->name ?: 'Eventlyy')) % 360;

// Member since
if (!isset($member_since)) {
    $member_since = '—';
    if (!empty($user->created_at)) {
        $ts = strtotime($user->created_at);
        if ($ts !== false) {
            $member_since = date('M Y', $ts);
        }
    } elseif (!empty($user->joined_at)) {
        $ts = strtotime($user->joined_at);
        if ($ts !== false) {
            $member_since = date('M Y', $ts);
        }
    }
}

// Determine if user is a host
if (!isset($isHost)) {
    $isHost = method_exists($user, 'isHost') && $user->isHost();
}

// Payout configuration check
if (!isset($payout_configured)) {
    $payout_configured = !empty($profile['payout_bank']) && !empty($profile['payout_account']);
}

// Total events hosted
if (!isset($total_events_hosted)) {
    $total_events_hosted = !empty($events) ? count($events) : 0;
}

// Purchased tickets list
if (!isset($purchasedTickets)) {
    $purchasedTickets = [];
}

// Ticket pagination
if (!isset($ticketPage)) {
    $ticketPage = isset($ticket_page) ? (int)$ticket_page : (int)($_GET['ticket_page'] ?? 1);
    if ($ticketPage < 1) $ticketPage = 1;
}

// Role badge label
$roleBadge = 'Attendee';
if (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) {
    $roleBadge = 'Super Admin';
} elseif (method_exists($user, 'isAdmin') && $user->isAdmin()) {
    $roleBadge = 'Admin';
} elseif (method_exists($user, 'isHost') && $user->isHost()) {
    $roleBadge = 'Host';
}

// Event status helper
function pfEventStatus(string $status, string $date): array {
    $eventTime = strtotime($date);
    $now = time();
    $liveWindowEnd = $eventTime + (event_completion_hours() * 3600);

    if ($status === 'cancelled') {
        return ['Cancelled', 'pf-badge--danger'];
    }
    if ($status === 'completed' || ($status === 'published' && $now > $liveWindowEnd)) {
        return ['Completed', 'pf-badge--neutral'];
    }
    if ($status === 'published' && $now >= $eventTime && $now <= $liveWindowEnd) {
        return ['Live Now', 'pf-badge--success'];
    }
    if ($status === 'published') {
        return ['Upcoming', 'pf-badge--brand'];
    }
    return ['Draft', 'pf-badge--neutral'];
}

// Active Tab determination
$reqTab = trim($_GET['tab'] ?? '');
if (!in_array($reqTab, ['profile', 'events', 'tickets', 'security'])) {
    if (isset($_GET['ticket_page'])) {
        $reqTab = 'tickets';
    } elseif ($isHost) {
        $reqTab = 'profile';
    } elseif (!empty($purchasedTickets)) {
        $reqTab = 'tickets';
    } else {
        $reqTab = 'profile';
    }
}
$activeTab = $reqTab;
?>

@extends('layouts/main')

@section('title')
Profile &amp; Account Settings — Eventlyy
@endsection

@section('style')
<style>
/* ═════════════════════════════════════════════════════════════
   Profile Hub Component Styles (.pf-*)
   Scoped, predictable, fully responsive, zero framework clashes.
   ═════════════════════════════════════════════════════════════ */

.pf-wrapper {
    max-width: 960px;
    margin: 0 auto;
    padding: 1.75rem 1rem 4rem;
}

/* ── Profile Identity Banner ── */
.pf-header {
    background: rgb(var(--card));
    border: 1px solid rgba(var(--muted), 0.16);
    border-radius: 20px;
    padding: 1.75rem 2rem;
    margin-bottom: 1.75rem;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
    position: relative;
    overflow: hidden;
}

.pf-header::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, rgb(var(--brand)), color-mix(in srgb, rgb(var(--brand)) 70%, rgb(var(--accent))));
}

.pf-header__body {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1.5rem;
    flex-wrap: wrap;
}

.pf-header__user {
    display: flex;
    align-items: center;
    gap: 1.25rem;
    flex: 1;
    min-width: 260px;
}

.pf-avatar-wrap {
    position: relative;
    flex-shrink: 0;
}

.pf-avatar {
    width: 68px;
    height: 68px;
    border-radius: 18px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.45rem;
    font-weight: 800;
    color: #ffffff;
    background: linear-gradient(135deg, hsl(var(--avatar-hue, 260) 65% 55%), hsl(calc(var(--avatar-hue, 260) + 40) 70% 60%));
    box-shadow: 0 6px 16px hsla(var(--avatar-hue, 260), 60%, 50%, 0.3);
}

.pf-role-badge {
    position: absolute;
    bottom: -4px;
    right: -4px;
    background: rgb(var(--brand));
    color: #ffffff;
    font-size: 0.62rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    padding: 0.15rem 0.45rem;
    border-radius: 6px;
    border: 2px solid rgb(var(--card));
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
}

.pf-header__meta {
    flex: 1;
    min-width: 0;
}

.pf-header__name {
    font-size: 1.45rem;
    font-weight: 800;
    color: rgb(var(--text));
    margin: 0 0 0.2rem;
    letter-spacing: -0.015em;
    line-height: 1.25;
}

.pf-header__email {
    font-size: 0.9rem;
    color: rgb(var(--muted));
    margin: 0 0 0.65rem;
    word-break: break-all;
}

.pf-chips {
    display: flex;
    flex-wrap: wrap;
    gap: 0.45rem;
}

.pf-chip {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    font-size: 0.76rem;
    font-weight: 600;
    color: rgb(var(--muted));
    background: rgba(var(--muted), 0.08);
    padding: 0.3rem 0.65rem;
    border-radius: 999px;
    border: 1px solid rgba(var(--muted), 0.12);
}

.pf-chip svg {
    flex-shrink: 0;
    opacity: 0.8;
}

.pf-chip--success {
    color: #10b981;
    background: rgba(16, 185, 129, 0.08);
    border-color: rgba(16, 185, 129, 0.24);
}

.pf-chip--warning {
    color: #f59e0b;
    background: rgba(245, 158, 11, 0.08);
    border-color: rgba(245, 158, 11, 0.24);
}

.pf-header__actions {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    flex-shrink: 0;
}

/* ── Segmented Tab Navigation ── */
.pf-nav-container {
    margin-bottom: 1.5rem;
    border-bottom: 1px solid rgba(var(--muted), 0.14);
}

.pf-nav {
    display: flex;
    gap: 0.35rem;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: none;
    padding-bottom: 0.5rem;
}

.pf-nav::-webkit-scrollbar {
    display: none;
}

.pf-nav-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.65rem 1.15rem;
    border-radius: 12px;
    border: 1px solid transparent;
    font-size: 0.88rem;
    font-weight: 700;
    color: rgb(var(--muted));
    background: transparent;
    cursor: pointer;
    white-space: nowrap;
    transition: all var(--duration-fast, 0.15s) ease-out;
    text-decoration: none;
}

.pf-nav-btn:hover {
    color: rgb(var(--text));
    background: rgba(var(--muted), 0.08);
}

.pf-nav-btn.pf-nav-btn--active {
    background: rgb(var(--brand));
    color: #ffffff;
    box-shadow: 0 4px 14px rgba(var(--brand), 0.3);
}

.pf-nav-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0.1rem 0.45rem;
    font-size: 0.72rem;
    font-weight: 800;
    border-radius: 999px;
    background: rgba(var(--muted), 0.15);
    color: inherit;
}

.pf-nav-btn.pf-nav-btn--active .pf-nav-badge {
    background: rgba(255, 255, 255, 0.25);
    color: #ffffff;
}

/* ── Tab Panels ── */
.pf-panel {
    display: none;
}

.pf-panel.pf-panel--active {
    display: block;
    animation: pfFadeIn 0.2s ease-out;
}

@keyframes pfFadeIn {
    from {
        opacity: 0;
        transform: translateY(4px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* ── Content Card ── */
.pf-card {
    background: rgb(var(--card));
    border: 1px solid rgba(var(--muted), 0.16);
    border-radius: 20px;
    padding: 2rem;
    margin-bottom: 1.5rem;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
}

.pf-card-head {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 1rem;
    margin-bottom: 1.5rem;
    padding-bottom: 1.15rem;
    border-bottom: 1px solid rgba(var(--muted), 0.12);
}

.pf-card-head__title-wrap {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.pf-card-head__icon {
    width: 40px;
    height: 40px;
    border-radius: 12px;
    background: rgba(var(--brand), 0.1);
    color: rgb(var(--brand));
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.pf-card-head h2 {
    font-size: 1.2rem;
    font-weight: 800;
    color: rgb(var(--text));
    margin: 0;
    letter-spacing: -0.01em;
}

.pf-card-head p {
    font-size: 0.84rem;
    color: rgb(var(--muted));
    margin: 0.25rem 0 0;
}

/* ── Form Inputs & Layout ── */
.pf-form {
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
}

.pf-form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.25rem;
}

.pf-field {
    display: flex;
    flex-direction: column;
    gap: 0.45rem;
}

.pf-label {
    font-size: 0.86rem;
    font-weight: 700;
    color: rgb(var(--text));
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.pf-label-badge {
    font-size: 0.72rem;
    font-weight: 600;
    color: rgb(var(--muted));
    background: rgba(var(--muted), 0.08);
    padding: 0.1rem 0.45rem;
    border-radius: 6px;
}

.pf-input {
    width: 100%;
    height: 46px;
    padding: 0 1rem;
    border-radius: 12px;
    border: 1.5px solid rgba(var(--muted), 0.2);
    background: rgb(var(--card));
    color: rgb(var(--text));
    font-size: 0.94rem;
    font-family: inherit;
    box-sizing: border-box;
    transition: all var(--duration-fast, 0.15s) ease-out;
}

.pf-input:focus {
    outline: none;
    border-color: rgb(var(--brand));
    box-shadow: 0 0 0 3px rgba(var(--brand), 0.15);
}

.pf-input--readonly {
    background: rgba(var(--muted), 0.05);
    color: rgb(var(--muted));
    cursor: not-allowed;
    border-color: rgba(var(--muted), 0.15);
}

.pf-select {
    cursor: pointer;
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%23888' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 1rem center;
    padding-right: 2.5rem;
}

.pf-field-note {
    font-size: 0.78rem;
    color: rgb(var(--muted));
    margin-top: 0.2rem;
}

/* ── Section Dividers & Notices ── */
.pf-divider {
    display: flex;
    align-items: center;
    gap: 0.65rem;
    margin: 1.25rem 0 0.5rem;
    padding-top: 1.25rem;
    border-top: 1px solid rgba(var(--muted), 0.12);
    color: rgb(var(--text));
    font-size: 0.95rem;
    font-weight: 800;
}

.pf-divider svg {
    color: rgb(var(--brand));
}

.pf-notice-box {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
    padding: 0.9rem 1.15rem;
    border-radius: 12px;
    font-size: 0.84rem;
    line-height: 1.5;
}

.pf-notice-box--info {
    background: rgba(var(--brand), 0.07);
    border: 1px solid rgba(var(--brand), 0.2);
    color: rgb(var(--text));
}

.pf-notice-box--warning {
    background: rgba(245, 158, 11, 0.08);
    border: 1px solid rgba(245, 158, 11, 0.25);
    color: rgb(var(--text));
}

.pf-notice-box svg {
    flex-shrink: 0;
    margin-top: 0.15rem;
}

.pf-card-footer {
    display: flex;
    justify-content: flex-end;
    align-items: center;
    gap: 0.75rem;
    margin-top: 1.5rem;
    padding-top: 1.25rem;
    border-top: 1px solid rgba(var(--muted), 0.12);
}

/* ── Action Buttons ── */
.pf-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.45rem;
    height: 44px;
    padding: 0 1.25rem;
    border-radius: 12px;
    font-size: 0.88rem;
    font-weight: 700;
    cursor: pointer;
    text-decoration: none;
    border: none;
    transition: all var(--duration-fast, 0.15s) ease-out;
    box-sizing: border-box;
}

.pf-btn--primary {
    background: linear-gradient(135deg, rgb(var(--brand)), color-mix(in srgb, rgb(var(--brand)) 80%, rgb(var(--accent))));
    color: #ffffff !important;
    box-shadow: 0 4px 14px rgba(var(--brand), 0.28);
}

.pf-btn--primary:hover:not(:disabled) {
    transform: translateY(-1px);
    box-shadow: 0 6px 20px rgba(var(--brand), 0.38);
    filter: brightness(1.04);
}

.pf-btn--secondary {
    background: rgba(var(--muted), 0.08);
    color: rgb(var(--text)) !important;
    border: 1px solid rgba(var(--muted), 0.18);
}

.pf-btn--secondary:hover {
    background: rgba(var(--muted), 0.14);
}

.pf-btn--danger-ghost {
    background: transparent;
    color: #ef4444 !important;
    border: 1px solid rgba(239, 68, 68, 0.25);
}

.pf-btn--danger-ghost:hover {
    background: #ef4444;
    color: #ffffff !important;
}

.pf-btn--danger {
    background: #ef4444;
    color: #ffffff !important;
    box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
}

.pf-btn--danger:hover {
    background: #dc2626;
    transform: translateY(-1px);
}

.pf-btn--sm {
    height: 36px;
    padding: 0 0.85rem;
    font-size: 0.8rem;
    border-radius: 9px;
}

/* ── Danger Zone Card ── */
.pf-card--danger {
    border-color: rgba(239, 68, 68, 0.25);
    background: rgba(239, 68, 68, 0.02);
}

.pf-danger-icon {
    background: rgba(239, 68, 68, 0.1) !important;
    color: #ef4444 !important;
}

.pf-danger-desc {
    font-size: 0.86rem;
    color: rgb(var(--muted));
    line-height: 1.55;
    margin: 0 0 1.25rem;
}

/* ── Events Managed List ── */
.pf-events-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.pf-event-item {
    display: grid;
    grid-template-columns: 96px 1fr auto;
    gap: 1.25rem;
    align-items: center;
    padding: 1rem;
    border-radius: 16px;
    background: rgba(var(--muted), 0.03);
    border: 1px solid rgba(var(--muted), 0.12);
    transition: all var(--duration-fast, 0.15s) ease-out;
}

.pf-event-item:hover {
    transform: translateY(-2px);
    border-color: rgba(var(--brand), 0.3);
    box-shadow: 0 6px 18px rgba(0, 0, 0, 0.04);
}

.pf-event-thumb {
    width: 96px;
    height: 84px;
    border-radius: 12px;
    background-size: cover;
    background-position: center;
    background-color: rgba(var(--muted), 0.1);
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.75rem;
}

.pf-event-info {
    min-width: 0;
}

.pf-event-title {
    font-size: 1.05rem;
    font-weight: 800;
    color: rgb(var(--text));
    margin: 0 0 0.35rem;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.pf-event-meta {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    font-size: 0.8rem;
    color: rgb(var(--muted));
    flex-wrap: wrap;
    margin-bottom: 0.5rem;
}

.pf-event-meta-item {
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
}

.pf-progress-wrap {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.pf-progress-track {
    flex: 1;
    height: 6px;
    background: rgba(var(--muted), 0.15);
    border-radius: 99px;
    overflow: hidden;
}

.pf-progress-fill {
    height: 100%;
    background: rgb(var(--brand));
    border-radius: 99px;
    transition: width 0.3s ease;
}

.pf-progress-label {
    font-size: 0.76rem;
    font-weight: 700;
    color: rgb(var(--text));
    white-space: nowrap;
}

.pf-event-controls {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 0.6rem;
    flex-shrink: 0;
}

.pf-event-btns {
    display: flex;
    align-items: center;
    gap: 0.4rem;
}

/* Status Badges */
.pf-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    font-size: 0.74rem;
    font-weight: 800;
    padding: 0.25rem 0.65rem;
    border-radius: 999px;
    text-transform: uppercase;
    letter-spacing: 0.04em;
}

.pf-badge--brand {
    background: rgba(var(--brand), 0.12);
    color: rgb(var(--brand));
}

.pf-badge--success {
    background: rgba(16, 185, 129, 0.12);
    color: #10b981;
}

.pf-badge--danger {
    background: rgba(239, 68, 68, 0.12);
    color: #ef4444;
}

.pf-badge--neutral {
    background: rgba(var(--muted), 0.12);
    color: rgb(var(--muted));
}

/* ── Purchased Tickets Pass Layout ── */
.pf-tickets-list {
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
}

.pf-ticket-card {
    border-radius: 20px;
    border: 1px solid rgba(var(--muted), 0.16);
    background: rgb(var(--card));
    box-shadow: 0 4px 18px rgba(0, 0, 0, 0.04);
    overflow: hidden;
}

.pf-ticket-card__header {
    background: rgba(var(--muted), 0.04);
    padding: 1.15rem 1.5rem;
    border-bottom: 1px solid rgba(var(--muted), 0.1);
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
    flex-wrap: wrap;
}

.pf-ticket-event-name {
    font-size: 1.15rem;
    font-weight: 800;
    color: rgb(var(--text));
    margin: 0 0 0.2rem;
}

.pf-ticket-event-meta {
    font-size: 0.82rem;
    color: rgb(var(--muted));
    display: flex;
    align-items: center;
    gap: 0.35rem;
}

.pf-ticket-card__body {
    display: grid;
    grid-template-columns: 1fr 200px;
    padding: 1.5rem;
    gap: 1.5rem;
}

.pf-ticket-main {
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    gap: 1.25rem;
}

.pf-ticket-attendee {
    display: flex;
    align-items: center;
    gap: 0.85rem;
}

.pf-ticket-avatar {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    background: rgba(var(--brand), 0.12);
    color: rgb(var(--brand));
    font-size: 1.05rem;
    font-weight: 800;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.pf-ticket-attendee-name {
    font-size: 1.05rem;
    font-weight: 800;
    color: rgb(var(--text));
    margin: 0;
}

.pf-ticket-type {
    font-size: 0.82rem;
    font-weight: 600;
    color: rgb(var(--brand));
    margin: 0.15rem 0 0;
}

.pf-ticket-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1rem;
    background: rgba(var(--muted), 0.04);
    padding: 0.9rem 1.15rem;
    border-radius: 14px;
    border: 1px solid rgba(var(--muted), 0.08);
}

.pf-ticket-detail-item {
    display: flex;
    flex-direction: column;
    gap: 0.2rem;
}

.pf-ticket-detail-label {
    font-size: 0.72rem;
    font-weight: 700;
    color: rgb(var(--muted));
    text-transform: uppercase;
    letter-spacing: 0.04em;
}

.pf-ticket-detail-value {
    font-size: 0.9rem;
    font-weight: 800;
    color: rgb(var(--text));
}

.pf-ticket-code {
    font-family: monospace;
    color: rgb(var(--brand));
    letter-spacing: 0.05em;
}

/* QR Stub */
.pf-ticket-qr-stub {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    border-left: 1px dashed rgba(var(--muted), 0.25);
    padding-left: 1.5rem;
    text-align: center;
}

.pf-qr-box {
    background: #ffffff !important;
    padding: 0.5rem;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
    display: inline-flex;
}

.pf-qr-img {
    display: block;
    width: 120px;
    height: 120px;
}

.pf-qr-code-text {
    font-family: monospace;
    font-size: 0.82rem;
    font-weight: 800;
    color: rgb(var(--text));
    margin-top: 0.45rem;
}

.pf-qr-hint {
    font-size: 0.72rem;
    color: rgb(var(--muted));
    margin-top: 0.15rem;
}

.pf-ticket-actions {
    background: rgba(var(--muted), 0.03);
    border-top: 1px solid rgba(var(--muted), 0.1);
    padding: 0.85rem 1.5rem;
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 0.6rem;
    flex-wrap: wrap;
}

.pf-checked-in-pill {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    font-size: 0.78rem;
    font-weight: 800;
    color: #10b981;
    margin-right: auto;
}

/* ── Empty State ── */
.pf-empty {
    text-align: center;
    padding: 3.5rem 1.5rem;
}

.pf-empty-icon {
    font-size: 2.75rem;
    display: block;
    margin-bottom: 0.85rem;
}

.pf-empty-title {
    font-size: 1.15rem;
    font-weight: 800;
    color: rgb(var(--text));
    margin: 0 0 0.35rem;
}

.pf-empty-desc {
    font-size: 0.88rem;
    color: rgb(var(--muted));
    margin: 0 0 1.5rem;
}

/* ── Pagination ── */
.pf-pagination {
    display: flex;
    justify-content: center;
    gap: 0.4rem;
    margin-top: 1.5rem;
}

.pf-page-btn {
    min-width: 36px;
    height: 36px;
    padding: 0 0.65rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 10px;
    font-size: 0.84rem;
    font-weight: 700;
    color: rgb(var(--text));
    background: rgba(var(--muted), 0.08);
    text-decoration: none;
    border: 1px solid rgba(var(--muted), 0.15);
    transition: all 0.15s ease;
}

.pf-page-btn:hover:not(.disabled) {
    background: rgba(var(--muted), 0.16);
}

.pf-page-btn.active {
    background: rgb(var(--brand));
    color: #ffffff;
    border-color: rgb(var(--brand));
}

.pf-page-btn.disabled {
    opacity: 0.4;
    cursor: not-allowed;
}

/* ── Modals ── */
.pf-modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.65);
    backdrop-filter: blur(4px);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1.25rem;
    z-index: 10000;
}

.pf-modal-overlay.hidden {
    display: none !important;
}

.pf-modal-card {
    background: rgb(var(--card));
    border: 1px solid rgba(var(--muted), 0.2);
    border-radius: 20px;
    padding: 2rem;
    max-width: 520px;
    width: 100%;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.35);
    position: relative;
    max-height: 90vh;
    overflow-y: auto;
}

.pf-modal-head {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 1rem;
    margin-bottom: 1.25rem;
}

.pf-modal-title {
    font-size: 1.25rem;
    font-weight: 800;
    color: rgb(var(--text));
    margin: 0;
}

.pf-modal-subtitle {
    font-size: 0.82rem;
    color: rgb(var(--muted));
    margin: 0.2rem 0 0;
}

.pf-modal-close {
    background: transparent;
    border: none;
    font-size: 1.35rem;
    cursor: pointer;
    color: rgb(var(--muted));
    padding: 0.2rem;
    line-height: 1;
}

.pf-modal-close:hover {
    color: rgb(var(--text));
}

.pf-modal-summary {
    background: rgba(var(--muted), 0.05);
    border: 1px solid rgba(var(--muted), 0.12);
    border-radius: 12px;
    padding: 0.85rem 1rem;
    margin-bottom: 1.25rem;
    font-size: 0.86rem;
}

.pf-modal-summary strong {
    color: rgb(var(--text));
}

.pf-checkbox-row {
    display: flex;
    align-items: flex-start;
    gap: 0.65rem;
    margin: 1.15rem 0;
}

.pf-checkbox {
    margin-top: 3px;
    width: 18px;
    height: 18px;
    accent-color: rgb(var(--brand));
    cursor: pointer;
}

.pf-checkbox-label {
    font-size: 0.82rem;
    color: rgb(var(--muted));
    line-height: 1.45;
    cursor: pointer;
}

.pf-alert {
    padding: 0.85rem 1rem;
    border-radius: 10px;
    font-size: 0.85rem;
    font-weight: 600;
    margin-bottom: 1rem;
}

.pf-alert--error {
    background: rgba(239, 68, 68, 0.12);
    color: #ef4444;
    border: 1px solid rgba(239, 68, 68, 0.25);
}

/* ═════════════════════════════════════════════════════════════
   Responsive Breakpoints
   ═════════════════════════════════════════════════════════════ */

/* Tablet (max 860px) */
@media (max-width: 860px) {
    .pf-header {
        padding: 1.5rem;
    }

    .pf-header__body {
        flex-direction: column;
        align-items: flex-start;
    }

    .pf-header__actions {
        width: 100%;
    }

    .pf-header__actions .pf-btn {
        flex: 1;
    }

    .pf-ticket-card__body {
        grid-template-columns: 1fr;
    }

    .pf-ticket-qr-stub {
        border-left: none;
        border-top: 1px dashed rgba(var(--muted), 0.25);
        padding-left: 0;
        padding-top: 1.25rem;
    }
}

/* Mobile (max 680px) */
@media (max-width: 680px) {
    .pf-wrapper {
        padding: 1rem 0.75rem 3rem;
    }

    .pf-card {
        padding: 1.25rem;
        border-radius: 16px;
    }

    .pf-form-grid {
        grid-template-columns: 1fr;
        gap: 1rem;
    }

    .pf-input {
        font-size: 16px; /* Prevents iOS auto-zoom */
    }

    .pf-event-item {
        grid-template-columns: 72px 1fr;
        gap: 0.85rem;
    }

    .pf-event-thumb {
        width: 72px;
        height: 68px;
    }

    .pf-event-controls {
        grid-column: 1 / -1;
        flex-direction: row;
        justify-content: space-between;
        align-items: center;
        padding-top: 0.65rem;
        border-top: 1px solid rgba(var(--muted), 0.1);
        width: 100%;
    }

    .pf-ticket-grid {
        grid-template-columns: 1fr;
        gap: 0.65rem;
    }

    .pf-ticket-actions {
        flex-direction: column;
        width: 100%;
    }

    .pf-ticket-actions .pf-btn {
        width: 100%;
    }

    .pf-checked-in-pill {
        margin-right: 0;
        margin-bottom: 0.4rem;
    }

    .pf-modal-card {
        padding: 1.35rem;
        border-radius: 16px;
    }

    .pf-card-footer {
        flex-direction: column-reverse;
    }

    .pf-card-footer .pf-btn {
        width: 100%;
    }
}
</style>
@endsection

@section('content')
<div class="pf-wrapper">

    <!-- ═══ Profile Identity Banner ═══ -->
    <header class="pf-header">
        <div class="pf-header__body">
            <div class="pf-header__user">
                <div class="pf-avatar-wrap">
                    <div class="pf-avatar" style="--avatar-hue: <?= $avatarHue ?>">
                        <?= htmlspecialchars($initials) ?>
                    </div>
                    <span class="pf-role-badge"><?= htmlspecialchars($roleBadge) ?></span>
                </div>

                <div class="pf-header__meta">
                    <h1 class="pf-header__name"><?= htmlspecialchars($user->name ?: 'My Account') ?></h1>
                    <p class="pf-header__email"><?= htmlspecialchars($user->email) ?></p>

                    <div class="pf-chips">
                        <span class="pf-chip">
                            <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                <line x1="16" y1="2" x2="16" y2="6"></line>
                                <line x1="8" y1="2" x2="8" y2="6"></line>
                                <line x1="3" y1="10" x2="21" y2="10"></line>
                            </svg>
                            Member since <?= htmlspecialchars($member_since) ?>
                        </span>

                        <?php if ($isHost): ?>
                            <?php if ($payout_configured): ?>
                                <span class="pf-chip pf-chip--success">
                                    <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="20 6 9 17 4 12"></polyline>
                                    </svg>
                                    Payout Configured
                                </span>
                            <?php else: ?>
                                <span class="pf-chip pf-chip--warning">
                                    <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="12" cy="12" r="10"></circle>
                                        <line x1="12" y1="8" x2="12" y2="12"></line>
                                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                                    </svg>
                                    Payout Not Set
                                </span>
                            <?php endif; ?>

                            <span class="pf-chip">
                                <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"></path>
                                    <circle cx="9" cy="7" r="4"></circle>
                                    <path d="M23 21v-2a4 4 0 00-3-3.87"></path>
                                    <path d="M16 3.13a4 4 0 010 7.75"></path>
                                </svg>
                                <?= (int)$total_events_hosted ?> Event<?= (int)$total_events_hosted !== 1 ? 's' : '' ?>
                            </span>
                        <?php endif; ?>

                        <span class="pf-chip">
                            <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="2" y="6" width="20" height="12" rx="2"></rect>
                                <circle cx="12" cy="12" r="2"></circle>
                                <path d="M6 12h.01M18 12h.01"></path>
                            </svg>
                            <?= (int)($totalTickets ?? count($purchasedTickets)) ?> Ticket<?= (int)($totalTickets ?? count($purchasedTickets)) !== 1 ? 's' : '' ?>
                        </span>
                    </div>
                </div>
            </div>

            <div class="pf-header__actions">
                <?php if ($isHost): ?>
                    <a href="/host/event/create" class="pf-btn pf-btn--primary">
                        <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="12" y1="5" x2="12" y2="19"></line>
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                        </svg>
                        Create Event
                    </a>
                <?php endif; ?>

                <a href="/logout" class="pf-btn pf-btn--danger-ghost" title="Sign out of your account">
                    <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                        <polyline points="16 17 21 12 16 7"></polyline>
                        <line x1="21" y1="12" x2="9" y2="12"></line>
                    </svg>
                    Logout
                </a>
            </div>
        </div>
    </header>

    <!-- ═══ Segmented Tab Navigation ═══ -->
    <div class="pf-nav-container">
        <nav class="pf-nav" role="tablist">
            <?php if ($isHost): ?>
                <button type="button" class="pf-nav-btn <?= $activeTab === 'profile' ? 'pf-nav-btn--active' : '' ?>" data-tab="profile" role="tab">
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                        <circle cx="12" cy="7" r="4"></circle>
                    </svg>
                    Profile &amp; Payout
                </button>
                <button type="button" class="pf-nav-btn <?= $activeTab === 'events' ? 'pf-nav-btn--active' : '' ?>" data-tab="events" role="tab">
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                        <line x1="16" y1="2" x2="16" y2="6"></line>
                        <line x1="8" y1="2" x2="8" y2="6"></line>
                        <line x1="3" y1="10" x2="21" y2="10"></line>
                    </svg>
                    Events Managed
                    <span class="pf-nav-badge"><?= count($events ?? []) ?></span>
                </button>
                <button type="button" class="pf-nav-btn <?= $activeTab === 'tickets' ? 'pf-nav-btn--active' : '' ?>" data-tab="tickets" role="tab">
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="2" y="6" width="20" height="12" rx="2"></rect>
                        <circle cx="12" cy="12" r="2"></circle>
                        <path d="M6 12h.01M18 12h.01"></path>
                    </svg>
                    My Tickets
                    <span class="pf-nav-badge"><?= (int)($totalTickets ?? count($purchasedTickets)) ?></span>
                </button>
                <button type="button" class="pf-nav-btn <?= $activeTab === 'security' ? 'pf-nav-btn--active' : '' ?>" data-tab="security" role="tab">
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                    </svg>
                    Security &amp; Account
                </button>
            <?php else: ?>
                <button type="button" class="pf-nav-btn <?= $activeTab === 'tickets' ? 'pf-nav-btn--active' : '' ?>" data-tab="tickets" role="tab">
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="2" y="6" width="20" height="12" rx="2"></rect>
                        <circle cx="12" cy="12" r="2"></circle>
                        <path d="M6 12h.01M18 12h.01"></path>
                    </svg>
                    My Tickets
                    <span class="pf-nav-badge"><?= (int)($totalTickets ?? count($purchasedTickets)) ?></span>
                </button>
                <button type="button" class="pf-nav-btn <?= $activeTab === 'profile' ? 'pf-nav-btn--active' : '' ?>" data-tab="profile" role="tab">
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                        <circle cx="12" cy="7" r="4"></circle>
                    </svg>
                    Profile Details
                </button>
                <button type="button" class="pf-nav-btn <?= $activeTab === 'security' ? 'pf-nav-btn--active' : '' ?>" data-tab="security" role="tab">
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                    </svg>
                    Security &amp; Account
                </button>
            <?php endif; ?>
        </nav>
    </div>

    <!-- ═══ Tab Panel: Profile & Payout ═══ -->
    <div id="panel-profile" class="pf-panel <?= $activeTab === 'profile' ? 'pf-panel--active' : '' ?>" role="tabpanel">
        <div class="pf-card">
            <div class="pf-card-head">
                <div class="pf-card-head__title-wrap">
                    <div class="pf-card-head__icon">
                        <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                    </div>
                    <div>
                        <h2>Personal Information</h2>
                        <p>Manage your public contact information and profile bio.</p>
                    </div>
                </div>
            </div>

            <form method="POST" action="/profile" class="pf-form" id="profile-form">
                <?= csrf_field() ?>

                <div class="pf-form-grid">
                    <div class="pf-field">
                        <label for="pf-name" class="pf-label">
                            Full Name
                            <span class="pf-label-badge">Required</span>
                        </label>
                        <input type="text" name="name" id="pf-name" class="pf-input" placeholder="e.g. Jane Doe" value="<?= htmlspecialchars($user->name) ?>" required>
                    </div>

                    <div class="pf-field">
                        <label for="pf-email" class="pf-label">
                            Account Email
                            <span class="pf-label-badge">Primary</span>
                        </label>
                        <input type="email" name="email" id="pf-email" class="pf-input pf-input--readonly" value="<?= htmlspecialchars($user->email) ?>" readonly title="Email cannot be changed directly">
                    </div>
                </div>

                <div class="pf-form-grid">
                    <div class="pf-field">
                        <label for="pf-phone" class="pf-label">Phone Number</label>
                        <input type="tel" name="phone" id="pf-phone" class="pf-input" placeholder="+1 (555) 000-0000" value="<?= htmlspecialchars($user->phone ?? '') ?>">
                    </div>

                    <div class="pf-field">
                        <label for="pf-bio" class="pf-label">Bio / About</label>
                        <input type="text" name="bio" id="pf-bio" class="pf-input" placeholder="A brief sentence about yourself" value="<?= htmlspecialchars($profile['bio'] ?? '') ?>">
                    </div>
                </div>

                <?php if ($isHost): ?>
                    <!-- Payout Configuration -->
                    <div class="pf-divider">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect>
                            <line x1="1" y1="10" x2="23" y2="10"></line>
                        </svg>
                        <span>Host Payout Account</span>
                    </div>

                    <div class="pf-notice-box pf-notice-box--info">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="16" x2="12" y2="12"></line>
                            <line x1="12" y1="8" x2="12.01" y2="8"></line>
                        </svg>
                        <div>
                            Revenue from your ticket sales will be directly settled into this bank account via Paystack transfer.
                        </div>
                    </div>

                    <?php 
                    $profileBanksList = $supportedBanks ?? \Ace\PaystackService::getSupportedBanks();
                    $profileCurrentBank = $profile['payout_bank'] ?? '';
                    ?>
                    <div class="pf-form-grid">
                        <div class="pf-field">
                            <label for="pf-bank" class="pf-label">
                                Payout Bank
                                <span class="pf-label-badge">Required</span>
                            </label>
                            <select name="payout_bank" id="pf-bank" class="pf-input pf-select" required>
                                <option value="">-- Choose Your Bank --</option>
                                <?php foreach ($profileBanksList as $b): ?>
                                    <option value="<?= htmlspecialchars($b['name']) ?>" data-code="<?= htmlspecialchars($b['code']) ?>" <?= (stripos($profileCurrentBank, $b['name']) !== false || $profileCurrentBank === $b['name']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($b['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="pf-field">
                            <label for="pf-account" class="pf-label">
                                Account Number
                                <span class="pf-label-badge">10-Digit NUBAN</span>
                            </label>
                            <input type="text" name="payout_account" id="pf-account" class="pf-input" placeholder="0123456789" maxlength="10" value="<?= htmlspecialchars($profile['payout_account'] ?? '') ?>" required>
                        </div>
                    </div>

                    <input type="hidden" name="payout_routing" id="pf-routing" value="<?= htmlspecialchars($profile['payout_routing'] ?? '') ?>">
                <?php endif; ?>

                <div class="pf-card-footer">
                    <button type="submit" class="pf-btn pf-btn--primary">
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                            <polyline points="17 21 17 13 7 13 7 21"></polyline>
                            <polyline points="7 3 7 8 15 8"></polyline>
                        </svg>
                        Save Profile Changes
                    </button>
                </div>
            </form>
        </div>
    </div>

    <?php if ($isHost): ?>
        <!-- ═══ Tab Panel: Events Managed (Host) ═══ -->
        <div id="panel-events" class="pf-panel <?= $activeTab === 'events' ? 'pf-panel--active' : '' ?>" role="tabpanel">
            <div class="pf-card">
                <div class="pf-card-head">
                    <div class="pf-card-head__title-wrap">
                        <div class="pf-card-head__icon">
                            <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                <line x1="16" y1="2" x2="16" y2="6"></line>
                                <line x1="8" y1="2" x2="8" y2="6"></line>
                                <line x1="3" y1="10" x2="21" y2="10"></line>
                            </svg>
                        </div>
                        <div>
                            <h2>Events Managed</h2>
                            <p>Overview of the events you created, attendance statistics, and quick controls.</p>
                        </div>
                    </div>

                    <a href="/host/event/create" class="pf-btn pf-btn--primary pf-btn--sm">
                        + New Event
                    </a>
                </div>

                <?php if (!empty($events)): ?>
                    <div class="pf-events-list">
                        <?php foreach ($events as $ev):
                            $sold = (int)($ev['tickets_sold'] ?? 0);
                            $capacity = (int)($ev['total_capacity'] ?? 0);
                            $percent = $capacity > 0 ? round(($sold / $capacity) * 100) : 0;
                            [$statusLabel, $statusBadgeClass] = pfEventStatus($ev['status'], $ev['date']);
                            $eventDate = date('M d, Y', strtotime($ev['date']));
                            $eventImage = $ev['image'] ?? '';
                        ?>
                            <div class="pf-event-item">
                                <?php if ($eventImage): ?>
                                    <div class="pf-event-thumb" style="background-image: url('<?= htmlspecialchars($eventImage) ?>')"></div>
                                <?php else: ?>
                                    <div class="pf-event-thumb">🎪</div>
                                <?php endif; ?>

                                <div class="pf-event-info">
                                    <div class="pf-event-title" title="<?= htmlspecialchars($ev['name']) ?>">
                                        <?= htmlspecialchars($ev['name']) ?>
                                    </div>
                                    <div class="pf-event-meta">
                                        <span class="pf-event-meta-item">
                                            <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                                <line x1="16" y1="2" x2="16" y2="6"></line>
                                                <line x1="8" y1="2" x2="8" y2="6"></line>
                                                <line x1="3" y1="10" x2="21" y2="10"></line>
                                            </svg>
                                            <?= $eventDate ?>
                                        </span>
                                        <?php if (!empty($ev['location'])): ?>
                                            <span class="pf-event-meta-item">
                                                <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"></path>
                                                    <circle cx="12" cy="10" r="3"></circle>
                                                </svg>
                                                <?= htmlspecialchars($ev['location']) ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>

                                    <div class="pf-progress-wrap">
                                        <div class="pf-progress-track">
                                            <div class="pf-progress-fill" style="width: <?= min($percent, 100) ?>%;"></div>
                                        </div>
                                        <span class="pf-progress-label">
                                            <?= number_format($sold) ?> / <?= number_format($capacity) ?> sold (<?= $percent ?>%)
                                        </span>
                                    </div>
                                </div>

                                <div class="pf-event-controls">
                                    <span class="pf-badge <?= $statusBadgeClass ?>"><?= $statusLabel ?></span>
                                    <div class="pf-event-btns">
                                        <a href="/host/event/management/<?= $ev['id'] ?>" class="pf-btn pf-btn--secondary pf-btn--sm">
                                            Manage
                                        </a>
                                        <form method="POST" action="<?= route('host/event/duplicate/' . $ev['id']) ?>" onsubmit="return confirm('Duplicate this event as a new draft?');" style="margin:0;">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="pf-btn pf-btn--secondary pf-btn--sm" title="Clone event as a new draft">
                                                📋 Clone
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="pf-empty">
                        <span class="pf-empty-icon">🎪</span>
                        <div class="pf-empty-title">No events created yet</div>
                        <p class="pf-empty-desc">You haven't published or organized any events on Eventlyy yet.</p>
                        <a href="/host/event/create" class="pf-btn pf-btn--primary">
                            Create Your First Event
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- ═══ Tab Panel: My Tickets ═══ -->
    <div id="panel-tickets" class="pf-panel <?= $activeTab === 'tickets' ? 'pf-panel--active' : '' ?>" role="tabpanel">
        <div class="pf-card">
            <div class="pf-card-head">
                <div class="pf-card-head__title-wrap">
                    <div class="pf-card-head__icon">
                        <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="2" y="6" width="20" height="12" rx="2"></rect>
                            <circle cx="12" cy="12" r="2"></circle>
                            <path d="M6 12h.01M18 12h.01"></path>
                        </svg>
                    </div>
                    <div>
                        <h2>My Purchased Tickets</h2>
                        <p>Digital admission passes with gate QR codes and ticket management.</p>
                    </div>
                </div>
            </div>

            <?php if (!empty($purchasedTickets)): ?>
                <div class="pf-tickets-list">
                    <?php foreach ($purchasedTickets as $idx => $tk):
                        $tkInitials = strtoupper(substr($tk['attendee_name'] ?? 'U', 0, 2));
                        $tkEventTs = !empty($tk['event_date']) ? strtotime($tk['event_date']) : null;
                        $tkEventDate = $tkEventTs ? date('l, M d, Y', $tkEventTs) : 'TBA';
                        $tkEventTime = $tkEventTs ? date('g:i A', $tkEventTs) : 'TBA';
                        $tkPurchaseTs = !empty($tk['purchase_date']) ? strtotime($tk['purchase_date']) : null;
                        $tkPurchaseDate = $tkPurchaseTs ? date('M d, Y', $tkPurchaseTs) : '—';
                        $tkIsPast = $tkEventTs ? ($tkEventTs < time()) : false;
                    ?>
                        <div class="pf-ticket-card" id="my-ticket-<?= $idx ?>">
                            <div class="pf-ticket-card__header">
                                <div>
                                    <div class="pf-ticket-event-name"><?= htmlspecialchars($tk['event_name']) ?></div>
                                    <div class="pf-ticket-event-meta">
                                        <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"></path>
                                            <circle cx="12" cy="10" r="3"></circle>
                                        </svg>
                                        <?= htmlspecialchars($tk['event_location']) ?>
                                    </div>
                                </div>

                                <div>
                                    <?php if ($tkIsPast): ?>
                                        <span class="pf-badge pf-badge--neutral">✓ Attended</span>
                                    <?php elseif ($tk['ticket_status'] === 'Comped'): ?>
                                        <span class="pf-badge pf-badge--brand">★ Comped</span>
                                    <?php else: ?>
                                        <span class="pf-badge pf-badge--success">● Active Pass</span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="pf-ticket-card__body">
                                <div class="pf-ticket-main">
                                    <div class="pf-ticket-attendee">
                                        <div class="pf-ticket-avatar"><?= $tkInitials ?></div>
                                        <div>
                                            <div class="pf-ticket-attendee-name"><?= htmlspecialchars($tk['attendee_name']) ?></div>
                                            <div class="pf-ticket-type"><?= htmlspecialchars($tk['ticket_type']) ?> Pass</div>
                                        </div>
                                    </div>

                                    <div class="pf-ticket-grid">
                                        <div class="pf-ticket-detail-item">
                                            <span class="pf-ticket-detail-label">Ticket ID</span>
                                            <span class="pf-ticket-detail-value pf-ticket-code"><?= htmlspecialchars($tk['ticket_id']) ?></span>
                                        </div>
                                        <div class="pf-ticket-detail-item">
                                            <span class="pf-ticket-detail-label">Date &amp; Time</span>
                                            <span class="pf-ticket-detail-value"><?= $tkEventDate ?> &bull; <?= $tkEventTime ?></span>
                                        </div>
                                        <div class="pf-ticket-detail-item">
                                            <span class="pf-ticket-detail-label">Purchased On</span>
                                            <span class="pf-ticket-detail-value"><?= $tkPurchaseDate ?></span>
                                        </div>
                                    </div>
                                </div>

                                <div class="pf-ticket-qr-stub">
                                    <div class="pf-qr-box">
                                        <img src="<?= ticket_qr_url($tk['ticket_id'], 200) ?>" 
                                             alt="Admission QR code for <?= htmlspecialchars($tk['ticket_id']) ?>" 
                                             class="pf-qr-img" />
                                    </div>
                                    <div class="pf-qr-code-text"><?= htmlspecialchars($tk['ticket_id']) ?></div>
                                    <div class="pf-qr-hint">Scan for entry at gate</div>
                                </div>
                            </div>

                            <div class="pf-ticket-actions">
                                <?php if (!empty($tk['checked_in'])): ?>
                                    <span class="pf-checked-in-pill">
                                        <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                            <polyline points="20 6 9 17 4 12"></polyline>
                                        </svg>
                                        Checked In at Gate
                                    </span>
                                <?php endif; ?>

                                <?php if (!$tkIsPast && empty($tk['checked_in']) && in_array($tk['ticket_status'], ['Paid', 'Comped'])): ?>
                                    <button type="button" class="pf-btn pf-btn--secondary pf-btn--sm" onclick="openTransferModal('<?= htmlspecialchars($tk['ticket_id'], ENT_QUOTES) ?>', '<?= htmlspecialchars($tk['event_name'], ENT_QUOTES) ?>', '<?= htmlspecialchars($tk['ticket_type'], ENT_QUOTES) ?>')">
                                        <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                            <polyline points="17 1 21 5 17 9"></polyline>
                                            <path d="M3 11V9a4 4 0 0 1 4-4h14"></path>
                                            <polyline points="7 23 3 19 7 15"></polyline>
                                            <path d="M21 13v2a4 4 0 0 1-4 4H3"></path>
                                        </svg>
                                        Transfer Pass
                                    </button>
                                <?php endif; ?>

                                <?php if (!$tkIsPast): ?>
                                    <a href="<?= event_google_calendar_url(['name' => $tk['event_name'], 'date' => $tk['event_date'], 'location' => $tk['event_location'], 'description' => 'Admission Ticket #' . $tk['ticket_id'] . ' for ' . $tk['event_name'], 'id' => $tk['event_id']]) ?>" target="_blank" rel="noopener" class="pf-btn pf-btn--secondary pf-btn--sm" title="Add this event to Google Calendar">
                                        <span>📅 Calendar</span>
                                    </a>
                                <?php endif; ?>

                                <button type="button" class="pf-btn pf-btn--secondary pf-btn--sm" onclick="printTicket(<?= $idx ?>)">
                                    <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="6 9 6 2 18 2 18 9"></polyline>
                                        <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                                        <rect x="6" y="14" width="12" height="8"></rect>
                                    </svg>
                                    Print Ticket
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Pagination -->
                <?php if (isset($totalPages) && $totalPages > 1): ?>
                    <div class="pf-pagination">
                        <?php if ($ticketPage > 1): ?>
                            <a href="/profile?ticket_page=<?= $ticketPage - 1 ?>&tab=tickets" class="pf-page-btn">&laquo; Prev</a>
                        <?php else: ?>
                            <span class="pf-page-btn disabled">&laquo; Prev</span>
                        <?php endif; ?>

                        <?php foreach (pagination_range($ticketPage, $totalPages) as $p): ?>
                            <?php if ($p === '...'): ?>
                                <span class="pf-page-dots" aria-hidden="true">&hellip;</span>
                            <?php elseif ($p == $ticketPage): ?>
                                <span class="pf-page-btn active"><?= $p ?></span>
                            <?php else: ?>
                                <a href="/profile?ticket_page=<?= $p ?>&tab=tickets" class="pf-page-btn"><?= $p ?></a>
                            <?php endif; ?>
                        <?php endforeach; ?>

                        <?php if ($ticketPage < $totalPages): ?>
                            <a href="/profile?ticket_page=<?= $ticketPage + 1 ?>&tab=tickets" class="pf-page-btn">Next &raquo;</a>
                        <?php else: ?>
                            <span class="pf-page-btn disabled">Next &raquo;</span>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

            <?php else: ?>
                <div class="pf-empty">
                    <span class="pf-empty-icon">🎟️</span>
                    <div class="pf-empty-title">No tickets purchased yet</div>
                    <p class="pf-empty-desc">When you book tickets for upcoming events, your digital entry passes will appear here.</p>
                    <a href="/" class="pf-btn pf-btn--primary">
                        Browse Upcoming Events
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- ═══ Tab Panel: Security & Account ═══ -->
    <div id="panel-security" class="pf-panel <?= $activeTab === 'security' ? 'pf-panel--active' : '' ?>" role="tabpanel">
        
        <!-- Password Change Card -->
        <div class="pf-card">
            <div class="pf-card-head">
                <div class="pf-card-head__title-wrap">
                    <div class="pf-card-head__icon">
                        <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                            <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                        </svg>
                    </div>
                    <div>
                        <h2>Account Security</h2>
                        <p>Change your account password to maintain safety.</p>
                    </div>
                </div>
            </div>

            <form method="POST" action="/profile/password" class="pf-form" id="password-form">
                <?= csrf_field() ?>

                <div class="pf-field">
                    <label for="pf-curr-pass" class="pf-label">
                        Current Password
                        <span class="pf-label-badge">Required</span>
                    </label>
                    <input type="password" name="current_password" id="pf-curr-pass" class="pf-input" placeholder="Enter current password" required autocomplete="current-password">
                </div>

                <div class="pf-form-grid">
                    <div class="pf-field">
                        <label for="pf-new-pass" class="pf-label">
                            New Password
                            <span class="pf-label-badge">Min. 6 chars</span>
                        </label>
                        <input type="password" name="new_password" id="pf-new-pass" class="pf-input" placeholder="Create new password" required minlength="6" autocomplete="new-password">
                    </div>

                    <div class="pf-field">
                        <label for="pf-conf-pass" class="pf-label">
                            Confirm New Password
                            <span class="pf-label-badge">Match</span>
                        </label>
                        <input type="password" name="confirm_password" id="pf-conf-pass" class="pf-input" placeholder="Re-enter new password" required minlength="6" autocomplete="new-password">
                    </div>
                </div>

                <div class="pf-card-footer">
                    <button type="submit" class="pf-btn pf-btn--primary">
                        <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                            <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                        </svg>
                        Update Password
                    </button>
                </div>
            </form>
        </div>

        <!-- Danger Zone Card -->
        <div class="pf-card pf-card--danger">
            <div class="pf-card-head">
                <div class="pf-card-head__title-wrap">
                    <div class="pf-card-head__icon pf-danger-icon">
                        <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="3 6 5 6 21 6"></polyline>
                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                            <line x1="10" y1="11" x2="10" y2="17"></line>
                            <line x1="14" y1="11" x2="14" y2="17"></line>
                        </svg>
                    </div>
                    <div>
                        <h2 style="color: #ef4444;">Delete Account (GDPR)</h2>
                        <p>Permanently remove your account and purge personal records.</p>
                    </div>
                </div>
            </div>

            <p class="pf-danger-desc">
                Under GDPR regulations, deleting your account will permanently wipe your profile, organizer settings, and personal data. Financial transaction logs will be anonymized in compliance with statutory accounting standards.
            </p>

            <button type="button" class="pf-btn pf-btn--danger" onclick="document.getElementById('delete-account-modal').classList.remove('hidden')">
                🗑️ Delete My Account Permanently
            </button>
        </div>

    </div>

</div>

<!-- ═══ Modals ═══ -->

<!-- Ticket Transfer Modal -->
<div class="pf-modal-overlay hidden" id="transfer-ticket-modal" role="dialog" aria-modal="true" aria-labelledby="transfer-modal-title">
    <div class="pf-modal-card">
        <div class="pf-modal-head">
            <div>
                <h3 id="transfer-modal-title" class="pf-modal-title">Transfer Admission Pass</h3>
                <p class="pf-modal-subtitle">Reassign this ticket to another attendee</p>
            </div>
            <button type="button" class="pf-modal-close" onclick="closeTransferModal()" aria-label="Close modal">✕</button>
        </div>

        <div class="pf-modal-summary">
            <div><strong>Event:</strong> <span id="transfer-event-name">Event Name</span></div>
            <div style="margin-top: 0.35rem;">
                <strong>Pass:</strong> <span id="transfer-ticket-type">General Pass</span> &bull; 
                <span class="pf-ticket-code" id="transfer-ticket-code">#TKT-XXXX</span>
            </div>
        </div>

        <div class="pf-notice-box pf-notice-box--warning" style="margin-bottom: 1.25rem;">
            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                <line x1="12" y1="9" x2="12" y2="13"></line>
                <line x1="12" y1="17" x2="12.01" y2="17"></line>
            </svg>
            <div>
                <strong>Irreversible Action:</strong> Your current QR pass will be immediately deactivated and cannot be used at the venue gate. A brand new QR pass will be sent to the recipient.
            </div>
        </div>

        <div id="transfer-error-alert" class="pf-alert pf-alert--error" style="display: none;"></div>

        <form id="transfer-ticket-form" method="POST" action="/profile/ticket/transfer" class="pf-form">
            <?= csrf_field() ?>
            <input type="hidden" name="ticket_id" id="transfer-input-ticket-id" value="">
            <input type="hidden" name="is_ajax" value="1">

            <div class="pf-field">
                <label for="transfer-recipient-name" class="pf-label">
                    Recipient Full Name
                    <span class="pf-label-badge">Required</span>
                </label>
                <input type="text" name="recipient_name" id="transfer-recipient-name" class="pf-input" placeholder="e.g. Alex Morgan" required autocomplete="name">
            </div>

            <div class="pf-field">
                <label for="transfer-recipient-email" class="pf-label">
                    Recipient Email Address
                    <span class="pf-label-badge">Required</span>
                </label>
                <input type="email" name="recipient_email" id="transfer-recipient-email" class="pf-input" placeholder="e.g. alex@example.com" required autocomplete="email">
                <span class="pf-field-note">The admission pass and QR code will be emailed here.</span>
            </div>

            <div class="pf-field">
                <label for="transfer-note" class="pf-label">
                    Personal Note
                    <span class="pf-label-badge">Optional</span>
                </label>
                <input type="text" name="transfer_note" id="transfer-note" class="pf-input" placeholder="e.g. See you at the concert!">
            </div>

            <div class="pf-checkbox-row">
                <input type="checkbox" id="transfer-confirm-check" required class="pf-checkbox">
                <label for="transfer-confirm-check" class="pf-checkbox-label">
                    I confirm that I want to transfer this admission pass to the person named above and relinquish ownership.
                </label>
            </div>

            <div class="pf-card-footer" style="margin-top: 0.5rem; padding-top: 1rem;">
                <button type="button" class="pf-btn pf-btn--secondary" onclick="closeTransferModal()">
                    Cancel
                </button>
                <button type="submit" id="transfer-submit-btn" class="pf-btn pf-btn--primary">
                    <span id="transfer-btn-text">Confirm &amp; Transfer</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Delete Account Modal -->
<div class="pf-modal-overlay hidden" id="delete-account-modal" role="dialog" aria-modal="true" aria-labelledby="delete-modal-title">
    <div class="pf-modal-card">
        <div class="pf-modal-head">
            <div>
                <h3 id="delete-modal-title" class="pf-modal-title" style="color: #ef4444;">⚠️ Delete Account Confirmation</h3>
                <p class="pf-modal-subtitle">This action is permanent and cannot be undone.</p>
            </div>
            <button type="button" class="pf-modal-close" onclick="document.getElementById('delete-account-modal').classList.add('hidden')" aria-label="Close modal">✕</button>
        </div>

        <p class="pf-danger-desc">
            To proceed with GDPR account erasure, please authenticate with your password and type <strong>DELETE</strong> below.
        </p>

        <form method="POST" action="/profile/delete-account" class="pf-form">
            <?= csrf_field() ?>

            <div class="pf-field">
                <label class="pf-label">Confirm Current Password</label>
                <input type="password" name="confirm_password" class="pf-input" placeholder="Enter your password" required autocomplete="current-password">
            </div>

            <div class="pf-field">
                <label class="pf-label">Type "DELETE" in capital letters</label>
                <input type="text" name="confirm_text" class="pf-input" placeholder="DELETE" required pattern="DELETE" style="letter-spacing: 0.1em; text-transform: uppercase;">
            </div>

            <div class="pf-card-footer" style="margin-top: 0.5rem; padding-top: 1rem;">
                <button type="button" class="pf-btn pf-btn--secondary" onclick="document.getElementById('delete-account-modal').classList.add('hidden')">
                    Cancel
                </button>
                <button type="submit" class="pf-btn pf-btn--danger">
                    Confirm &amp; Delete
                </button>
            </div>
        </form>
    </div>
</div>

<div id="print-ticket-container" style="display:none;"></div>
@endsection

@section('script')
<script>
// ── Tab Switching ──
function switchTab(tabName) {
    const panels = document.querySelectorAll('.pf-panel');
    const buttons = document.querySelectorAll('.pf-nav-btn');

    panels.forEach(p => {
        if (p.id === 'panel-' + tabName) {
            p.classList.add('pf-panel--active');
        } else {
            p.classList.remove('pf-panel--active');
        }
    });

    buttons.forEach(b => {
        if (b.getAttribute('data-tab') === tabName) {
            b.classList.add('pf-nav-btn--active');
        } else {
            b.classList.remove('pf-nav-btn--active');
        }
    });

    // Update URL query string without reloading page
    const url = new URL(window.location);
    url.searchParams.set('tab', tabName);
    window.history.replaceState({}, '', url);
}

document.querySelectorAll('.pf-nav-btn').forEach(btn => {
    btn.addEventListener('click', function(e) {
        e.preventDefault();
        const tab = this.getAttribute('data-tab');
        if (tab) switchTab(tab);
    });
});

// ── Transfer Modal Controls ──
function openTransferModal(ticketCode, eventName, ticketType) {
    document.getElementById('transfer-input-ticket-id').value = ticketCode;
    document.getElementById('transfer-ticket-code').textContent = '#' + ticketCode;
    document.getElementById('transfer-event-name').textContent = eventName;
    document.getElementById('transfer-ticket-type').textContent = ticketType + ' Pass';
    document.getElementById('transfer-recipient-name').value = '';
    document.getElementById('transfer-recipient-email').value = '';
    document.getElementById('transfer-note').value = '';
    document.getElementById('transfer-confirm-check').checked = false;
    
    const errorAlert = document.getElementById('transfer-error-alert');
    errorAlert.style.display = 'none';
    errorAlert.textContent = '';

    const modal = document.getElementById('transfer-ticket-modal');
    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeTransferModal() {
    const modal = document.getElementById('transfer-ticket-modal');
    modal.classList.add('hidden');
    document.body.style.overflow = '';
}

// ── Transfer Form AJAX Submission ──
document.addEventListener('DOMContentLoaded', function() {
    const transferForm = document.getElementById('transfer-ticket-form');
    if (transferForm) {
        transferForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const submitBtn = document.getElementById('transfer-submit-btn');
            const btnText = document.getElementById('transfer-btn-text');
            const errorAlert = document.getElementById('transfer-error-alert');

            errorAlert.style.display = 'none';
            submitBtn.disabled = true;
            btnText.textContent = 'Processing Transfer...';

            const formData = new FormData(this);

            fetch('/profile/ticket/transfer', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(async response => {
                const data = await response.json().catch(() => ({}));
                if (!response.ok) {
                    throw new Error(data.message || 'Failed to transfer ticket. Please check your inputs.');
                }
                return data;
            })
            .then(() => {
                window.location.href = '/profile?tab=tickets';
            })
            .catch(err => {
                errorAlert.textContent = err.message;
                errorAlert.style.display = 'block';
                submitBtn.disabled = false;
                btnText.textContent = 'Confirm & Transfer';
            });
        });
    }
});

// ── Print Ticket Feature ──
function printTicket(idx) {
    const card = document.getElementById('my-ticket-' + idx);
    if (!card) return;

    const printContainer = document.getElementById('print-ticket-container');
    printContainer.innerHTML = card.outerHTML;

    const actions = printContainer.querySelector('.pf-ticket-actions');
    if (actions) actions.remove();

    document.body.classList.add('printing-ticket');
    window.print();

    window.addEventListener('afterprint', function cleanup() {
        document.body.classList.remove('printing-ticket');
        printContainer.innerHTML = '';
        window.removeEventListener('afterprint', cleanup);
    });
}
</script>
@endsection