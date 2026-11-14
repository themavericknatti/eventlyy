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
    } elseif (isset($_GET['event_page'])) {
        $reqTab = 'events';
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
Your Account Hub — Eventlyy
@endsection



@section('content')
<style>
/* ── Avatar & Role Badge Precision Sizing & Positioning ── */
.pf-header {
    overflow: visible !important; /* ensures badge never clips */
}
.pf-header__user {
    display: flex;
    align-items: center;
    gap: 1.25rem;
    flex: 1;
    min-width: 0;
}
.pf-avatar-wrap {
    position: relative;
    flex-shrink: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding-bottom: 8px;
    padding-right: 6px;
    margin-bottom: -4px;
}
.pf-avatar {
    width: clamp(54px, 7.5vw, 76px);
    height: clamp(54px, 7.5vw, 76px);
    border-radius: clamp(15px, 2vw, 20px);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: clamp(1.2rem, 2.5vw, 1.65rem);
    font-weight: 800;
    line-height: 1;
    text-align: center;
    letter-spacing: -0.02em;
    color: #ffffff;
    background: linear-gradient(135deg, hsl(var(--avatar-hue, 260) 65% 55%), hsl(calc(var(--avatar-hue, 260) + 40) 70% 60%));
    box-shadow: 0 8px 20px hsla(var(--avatar-hue, 260), 60%, 50%, 0.32);
    user-select: none;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.pf-role-badge {
    position: absolute;
    bottom: 2px;
    right: 0px;
    background: rgb(var(--brand));
    color: #ffffff;
    font-size: clamp(0.58rem, 1.1vw, 0.68rem);
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    padding: 0.2rem 0.55rem;
    border-radius: 999px;
    border: 2px solid rgb(var(--card, 255 255 255));
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.18);
    white-space: nowrap;
    z-index: 2;
    line-height: 1.1;
    pointer-events: none;
}

/* Tablet (max 860px) */
@media (max-width: 860px) {
    .pf-header__user {
        gap: 1.1rem;
        min-width: 0;
    }
    .pf-avatar {
        width: 66px;
        height: 66px;
        font-size: 1.4rem;
        border-radius: 17px;
    }
    .pf-role-badge {
        font-size: 0.62rem;
        padding: 0.18rem 0.5rem;
        bottom: 2px;
        right: 0px;
    }
}

/* Mobile (max 680px) */
@media (max-width: 680px) {
    .pf-header {
        padding: 1.25rem 1rem;
    }
    .pf-header__user {
        gap: 0.85rem;
        min-width: 0;
        width: 100%;
    }
    .pf-avatar {
        width: 58px;
        height: 58px;
        font-size: 1.25rem;
        border-radius: 15px;
    }
    .pf-role-badge {
        font-size: 0.58rem;
        padding: 0.15rem 0.45rem;
        bottom: 2px;
        right: 0px;
        border-width: 2px;
    }
    .pf-header__name {
        font-size: 1.25rem;
    }
    .pf-header__email {
        font-size: 0.82rem;
        margin-bottom: 0.45rem;
    }
}

/* Small mobile (max 420px) */
@media (max-width: 420px) {
    .pf-header__user {
        gap: 0.75rem;
    }
    .pf-avatar {
        width: 50px;
        height: 50px;
        font-size: 1.1rem;
        border-radius: 13px;
    }
    .pf-role-badge {
        font-size: 0.52rem;
        padding: 0.12rem 0.38rem;
        bottom: 2px;
        right: -1px;
        border-width: 1.5px;
    }
}

/* ── Recurring Events Series & Occurrence Drawer ── */
.pf-event-item--group {
    border-left: 3.5px solid #6366f1 !important;
}
.pf-recurring-pill {
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    font-size: 0.75rem;
    font-weight: 700;
    color: #6366f1;
    background: rgba(99, 102, 241, 0.1);
    border: 1px solid rgba(99, 102, 241, 0.24);
    border-radius: 999px;
    padding: 0.2rem 0.6rem;
    margin-left: 0.45rem;
    vertical-align: middle;
}
.pf-occurrences-drawer {
    background: rgba(var(--muted), 0.04);
    border: 1px solid rgba(var(--muted), 0.14);
    border-top: none;
    border-radius: 0 0 16px 16px;
    margin-top: -0.75rem;
    margin-bottom: 1.25rem;
    padding: 1rem 1.25rem;
}
.pf-occurrences-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 0.82rem;
    font-weight: 700;
    color: rgb(var(--text));
    opacity: 0.85;
    padding-bottom: 0.65rem;
    border-bottom: 1px dashed rgba(var(--muted), 0.2);
    margin-bottom: 0.65rem;
}
.pf-occ-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 0.65rem;
    padding: 0.6rem 0.75rem;
    background: rgb(var(--card));
    border: 1px solid rgba(var(--muted), 0.1);
    border-radius: 10px;
    margin-bottom: 0.45rem;
    transition: background 0.15s ease;
}
.pf-occ-row:last-child {
    margin-bottom: 0;
}
.pf-occ-row:hover {
    background: rgba(var(--muted), 0.05);
}
.pf-occ-seq {
    font-size: 0.78rem;
    font-weight: 800;
    color: #6366f1;
    background: rgba(99, 102, 241, 0.1);
    padding: 0.2rem 0.5rem;
    border-radius: 6px;
    min-width: 32px;
    text-align: center;
}
.pf-occ-info {
    flex: 1;
    min-width: 200px;
    display: flex;
    align-items: center;
    gap: 1rem;
    flex-wrap: wrap;
}
.pf-occ-date {
    font-size: 0.88rem;
    color: rgb(var(--text));
}
.pf-occ-stats {
    font-size: 0.78rem;
    color: rgb(var(--muted));
}
.pf-occ-actions {
    display: flex;
    align-items: center;
    gap: 0.4rem;
}
.pf-btn--xs {
    padding: 0.25rem 0.55rem;
    font-size: 0.75rem;
    border-radius: 6px;
}
.pf-badge--sm {
    font-size: 0.72rem;
    padding: 0.15rem 0.45rem;
}
</style>
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
                    Profile &amp; Payouts
                </button>
                <button type="button" class="pf-nav-btn <?= $activeTab === 'events' ? 'pf-nav-btn--active' : '' ?>" data-tab="events" role="tab">
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                        <line x1="16" y1="2" x2="16" y2="6"></line>
                        <line x1="8" y1="2" x2="8" y2="6"></line>
                        <line x1="3" y1="10" x2="21" y2="10"></line>
                    </svg>
                    My Drops
                    <span class="pf-nav-badge"><?= (int)($totalEventItems ?? ($total_events_hosted ?? count($events ?? []))) ?></span>
                </button>
                <button type="button" class="pf-nav-btn <?= $activeTab === 'tickets' ? 'pf-nav-btn--active' : '' ?>" data-tab="tickets" role="tab">
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="2" y="6" width="20" height="12" rx="2"></rect>
                        <circle cx="12" cy="12" r="2"></circle>
                        <path d="M6 12h.01M18 12h.01"></path>
                    </svg>
                    Ticket Wallet
                    <span class="pf-nav-badge"><?= (int)($totalTickets ?? count($purchasedTickets)) ?></span>
                </button>
                <button type="button" class="pf-nav-btn <?= $activeTab === 'security' ? 'pf-nav-btn--active' : '' ?>" data-tab="security" role="tab">
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                    </svg>
                    Security &amp; Settings
                </button>
            <?php else: ?>
                <button type="button" class="pf-nav-btn <?= $activeTab === 'tickets' ? 'pf-nav-btn--active' : '' ?>" data-tab="tickets" role="tab">
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="2" y="6" width="20" height="12" rx="2"></rect>
                        <circle cx="12" cy="12" r="2"></circle>
                        <path d="M6 12h.01M18 12h.01"></path>
                    </svg>
                    Ticket Wallet
                    <span class="pf-nav-badge"><?= (int)($totalTickets ?? count($purchasedTickets)) ?></span>
                </button>
                <button type="button" class="pf-nav-btn <?= $activeTab === 'profile' ? 'pf-nav-btn--active' : '' ?>" data-tab="profile" role="tab">
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                        <circle cx="12" cy="7" r="4"></circle>
                    </svg>
                    My Info
                </button>
                <button type="button" class="pf-nav-btn <?= $activeTab === 'security' ? 'pf-nav-btn--active' : '' ?>" data-tab="security" role="tab">
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                    </svg>
                    Security &amp; Settings
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
                        <h2>Your Info</h2>
                        <p>Update your name, email, and profile details.</p>
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
                            <h2>Your Drops</h2>
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
                            $isGroup = !empty($ev['is_group']);
                            $sold = (int)($ev['tickets_sold'] ?? 0);
                            $capacity = (int)($ev['total_capacity'] ?? 0);
                            $percent = $capacity > 0 ? round(($sold / $capacity) * 100) : 0;
                            [$statusLabel, $statusBadgeClass] = pfEventStatus($ev['status'], $ev['date']);
                            $eventDate = date('M d, Y', strtotime($ev['date']));
                            $eventImage = $ev['image'] ?? '';
                        ?>
                            <?php if ($isGroup): ?>
                                <!-- ── Grouped Recurring Event Series ── -->
                                <div class="pf-event-item pf-event-item--group" id="group-card-<?= e($ev['recurrence_group_id']) ?>">
                                    <?php if ($eventImage): ?>
                                        <div class="pf-event-thumb" style="background-image: url('<?= htmlspecialchars($eventImage) ?>')"></div>
                                    <?php else: ?>
                                        <div class="pf-event-thumb" style="background: rgba(99,102,241,0.1); color: #6366f1; font-size: 1.5rem;">🔄</div>
                                    <?php endif; ?>

                                    <div class="pf-event-info">
                                        <div class="pf-event-title" title="<?= htmlspecialchars($ev['name']) ?>">
                                            <?= htmlspecialchars($ev['name']) ?>
                                            <span class="pf-recurring-pill">
                                                🔄 <?= ucfirst($ev['recurrence_type'] ?? 'Recurring') ?> Series (<?= (int)$ev['total_occurrences'] ?> dates)
                                            </span>
                                        </div>
                                        <div class="pf-event-meta">
                                            <?php if (!empty($ev['next_occurrence'])): ?>
                                                <span class="pf-event-meta-item" style="color: #6366f1; font-weight: 600;">
                                                    <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                                        <line x1="16" y1="2" x2="16" y2="6"></line>
                                                        <line x1="8" y1="2" x2="8" y2="6"></line>
                                                        <line x1="3" y1="10" x2="21" y2="10"></line>
                                                    </svg>
                                                    Next: <?= date('D, M d, Y • g:i A', strtotime($ev['next_occurrence']['date'])) ?>
                                                </span>
                                            <?php endif; ?>
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
                                                Series Total: <?= number_format($sold) ?> / <?= number_format($capacity) ?> sold (<?= $percent ?>%)
                                            </span>
                                        </div>
                                    </div>

                                    <div class="pf-event-controls">
                                        <span class="pf-badge <?= $statusBadgeClass ?>"><?= $statusLabel ?></span>
                                        <div class="pf-event-btns">
                                            <?php if (!empty($ev['next_occurrence'])): ?>
                                                <a href="/host/event/management/<?= $ev['next_occurrence']['id'] ?>" class="pf-btn pf-btn--primary pf-btn--sm">
                                                    Manage Next
                                                </a>
                                            <?php endif; ?>
                                            <button type="button" class="pf-btn pf-btn--secondary pf-btn--sm" onclick="toggleRecurringDrawer('occ-drawer-<?= e($ev['recurrence_group_id']) ?>')">
                                                <span>Dates (<?= $ev['total_occurrences'] ?>) ▾</span>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Expandable Occurrences Drawer -->
                                <div id="occ-drawer-<?= e($ev['recurrence_group_id']) ?>" class="pf-occurrences-drawer" style="display: none;">
                                    <div class="pf-occurrences-header">
                                        <span>Scheduled Occurrences in this Series:</span>
                                        <small><?= $ev['total_occurrences'] ?> total dates</small>
                                    </div>
                                    <div class="pf-occurrences-list">
                                        <?php foreach ($ev['occurrences'] as $occIdx => $occ): 
                                            $occSold = (int)($occ['tickets_sold'] ?? 0);
                                            $occCap = (int)($occ['total_capacity'] ?? 0);
                                            $occPct = $occCap > 0 ? round(($occSold / $occCap) * 100) : 0;
                                            [$occStatusLabel, $occStatusClass] = pfEventStatus($occ['status'], $occ['date']);
                                            $occDateStr = date('D, M d, Y • g:i A', strtotime($occ['date']));
                                        ?>
                                            <div class="pf-occ-row">
                                                <div class="pf-occ-seq">
                                                    #<?= (int)($occ['recurrence_sequence'] ?? ($occIdx + 1)) ?>
                                                </div>
                                                <div class="pf-occ-info">
                                                    <strong class="pf-occ-date"><?= $occDateStr ?></strong>
                                                    <span class="pf-occ-stats"><?= $occSold ?> / <?= $occCap ?> sold (<?= $occPct ?>%)</span>
                                                </div>
                                                <div class="pf-occ-actions">
                                                    <span class="pf-badge pf-badge--sm <?= $occStatusClass ?>"><?= $occStatusLabel ?></span>
                                                    <a href="/host/event/management/<?= $occ['id'] ?>" class="pf-btn pf-btn--secondary pf-btn--xs">
                                                        Manage
                                                    </a>
                                                    <form method="POST" action="<?= route('host/event/duplicate/' . $occ['id']) ?>" onsubmit="return confirm('Duplicate this occurrence as a new draft?');" style="margin:0;">
                                                        <?= csrf_field() ?>
                                                        <button type="submit" class="pf-btn pf-btn--secondary pf-btn--xs" title="Clone occurrence">
                                                            📋
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>

                            <?php else: ?>
                                <!-- ── Standalone Event ── -->
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
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>

                    <!-- Host Events Pagination -->
                    <?php if (isset($totalEventPages) && $totalEventPages > 1): ?>
                        <div class="pf-pagination">
                            <?php if ($eventPage > 1): ?>
                                <a href="/profile?event_page=<?= $eventPage - 1 ?>&tab=events" class="pf-page-btn">&laquo; Prev</a>
                            <?php else: ?>
                                <span class="pf-page-btn disabled">&laquo; Prev</span>
                            <?php endif; ?>

                            <?php foreach (pagination_range($eventPage, $totalEventPages) as $p): ?>
                                <?php if ($p === '...'): ?>
                                    <span class="pf-page-dots" aria-hidden="true">&hellip;</span>
                                <?php elseif ($p == $eventPage): ?>
                                    <span class="pf-page-btn active"><?= $p ?></span>
                                <?php else: ?>
                                    <a href="/profile?event_page=<?= $p ?>&tab=events" class="pf-page-btn"><?= $p ?></a>
                                <?php endif; ?>
                            <?php endforeach; ?>

                            <?php if ($eventPage < $totalEventPages): ?>
                                <a href="/profile?event_page=<?= $eventPage + 1 ?>&tab=events" class="pf-page-btn">Next &raquo;</a>
                            <?php else: ?>
                                <span class="pf-page-btn disabled">Next &raquo;</span>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
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
                        <h2>Your Ticket Wallet</h2>
                        <p>Digital admission passes with gate QR codes and ticket management.</p>
                    </div>
                </div>
            </div>

            <?php 
                $activeLayaways = $activeLayaways ?? [];
                $availableCredits = $availableCredits ?? [];
            ?>
            <?php if (!empty($activeLayaways) || !empty($availableCredits)): ?>
                <div style="margin: 1.25rem 1.5rem 0; padding: 1rem 1.25rem; background: rgba(99, 102, 241, 0.08); border: 1px solid rgba(99, 102, 241, 0.25); border-radius: 12px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem;">
                    <div>
                        <strong style="color: #6366f1; font-size: 0.95rem; display: flex; align-items: center; gap: 0.35rem;">
                            <span>💳</span> <?= count($activeLayaways) ?> Active Layaway Plan<?= count($activeLayaways) === 1 ? '' : 's' ?>
                            <?php if (!empty($availableCredits)): ?>
                                • <?= count($availableCredits) ?> Event Credit<?= count($availableCredits) === 1 ? '' : 's' ?> Available
                            <?php endif; ?>
                        </strong>
                        <p style="margin: 0.25rem 0 0; font-size: 0.85rem; color: rgb(var(--text)); opacity: 0.8;">
                            You have reserved tickets on installment plans. Make payments anytime before the event starts.
                        </p>
                    </div>
                    <a href="/layaway" style="display: inline-block; background: #6366f1; color: #fff; padding: 0.55rem 1rem; border-radius: 8px; font-weight: 700; font-size: 0.85rem; text-decoration: none;">
                        Manage Layaway Plans →
                    </a>
                </div>
            <?php endif; ?>

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
                        <h2>Security & Settings</h2>
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

// ── Recurring Occurrences Drawer ──
function toggleRecurringDrawer(id) {
    const el = document.getElementById(id);
    if (!el) return;
    const isHidden = (el.style.display === 'none' || el.style.display === '');
    el.style.display = isHidden ? 'block' : 'none';
}

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