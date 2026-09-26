<?php

/**
 * Profile Page
 *
 * Dynamic host/organizer profile management page.
 *
 * @package Evently
 * @subpackage Views/Profile
 * @author Celio Natti
 * @license MIT License
 * @version 1.0.0
 */

// Ensure user object is available
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

// Helper: generate initials from name
$initials = '';
$nameParts = explode(' ', trim($user->name ?? ''));
$initials .= strtoupper(substr($nameParts[0] ?? '', 0, 1));
if (count($nameParts) > 1) {
    $initials .= strtoupper(substr(end($nameParts), 0, 1));
}

// Helper: avatar hue from name
$avatarHue = abs(crc32($user->name ?? 'U')) % 360;

// Member since display (use created_at or joined_at if available)
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
    $payout_configured = !empty($profile['payout_bank']) && !empty($profile['payout_account']) && !empty($profile['payout_routing']);
}

// Total events hosted
if (!isset($total_events_hosted)) {
    $total_events_hosted = !empty($events) ? count($events) : 0;
}

// Purchased tickets (for members)
if (!isset($purchasedTickets)) {
    $purchasedTickets = [];
}

// Tickets pagination current page
if (!isset($ticketPage)) {
    // prefer provided variable, fallback to query param or 1
    $ticketPage = isset($ticket_page) ? (int)$ticket_page : (int)($_GET['ticket_page'] ?? 1);
    if ($ticketPage < 1) $ticketPage = 1;
}

// Helper: event status badge
function profileEventStatus(string $status, string $date): array {
    $eventTime = strtotime($date);
    $now = time();
    $liveWindowEnd = $eventTime + (event_completion_hours() * 3600);

    if ($status === 'cancelled') {
        return ['Cancelled', 'au-status--suspended'];
    }
    if ($status === 'completed' || ($status === 'published' && $now > $liveWindowEnd)) {
        return ['Completed', 'au-status--unverified'];
    }
    if ($status === 'published' && $now >= $eventTime && $now <= $liveWindowEnd) {
        return ['Live Now', 'au-status--active'];
    }
    if ($status === 'published') {
        return ['Upcoming', 'au-status--attendee'];
    }
    return ['Draft', 'au-role-badge au-role--attendee'];
}

?>

@extends('layouts/main')

@section('title')
Evently - Your Profile and Account Settings.
@endsection

@section('content')
<section class="page-hero">
    <div>
        <span class="panel-badge">Your Profile</span>
        <h1>Manage Your Organizer Profile</h1>
        <p class="lede">Update your public contact details, configure payout information, and review events you created.</p>
    </div>
</section>

<?php if ($flashSuccess = \Ace\Application::$app->session->getFlash('success')): ?>
    <div style="background: rgba(16, 185, 129, 0.12); border: 1px solid rgba(16, 185, 129, 0.3); color: #059669; padding: 0.9rem 1.25rem; border-radius: 12px; margin-bottom: 1.5rem; font-weight: 600; display: flex; align-items: center; gap: 0.5rem; animation: fadeIn 0.3s ease;">
        <span style="font-size: 1.1rem;">✓</span> <?= e($flashSuccess) ?>
    </div>
<?php endif; ?>

<?php if ($flashError = \Ace\Application::$app->session->getFlash('error')): ?>
    <div style="background: rgba(239, 68, 68, 0.12); border: 1px solid rgba(239, 68, 68, 0.3); color: #dc2626; padding: 0.9rem 1.25rem; border-radius: 12px; margin-bottom: 1.5rem; font-weight: 600; display: flex; align-items: center; gap: 0.5rem; animation: fadeIn 0.3s ease;">
        <span style="font-size: 1.1rem;">⚠️</span> <?= e($flashError) ?>
    </div>
<?php endif; ?>

<!-- Profile Header Card -->
<section class="profile-header-card">
    <div class="profile-header-inner">
        <div class="profile-avatar-wrap">
            <span class="profile-avatar" style="--avatar-hue:<?= $avatarHue ?>"><?= htmlspecialchars($initials) ?></span>
            <?php
                $roleBadge = 'Member';
                if (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) {
                    $roleBadge = 'Super Admin';
                } elseif (method_exists($user, 'isAdmin') && $user->isAdmin()) {
                    $roleBadge = 'Admin';
                } elseif (method_exists($user, 'isHost') && $user->isHost()) {
                    $roleBadge = 'Host';
                }
            ?>
            <span class="profile-avatar-badge"><?= $roleBadge ?></span>
        </div>
        <div class="profile-header-info">
            <h2 class="profile-header-name"><?= htmlspecialchars($user->name) ?></h2>
            <p class="profile-header-email"><?= htmlspecialchars($user->email) ?></p>
            <div class="profile-header-meta">
                <span class="profile-meta-tag">
                    <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
                        <line x1="16" y1="2" x2="16" y2="6" />
                        <line x1="8" y1="2" x2="8" y2="6" />
                        <line x1="3" y1="10" x2="21" y2="10" />
                    </svg>
                    Member since <?= htmlspecialchars($member_since) ?>
                </span>
                <?php if ($isHost): ?>
                <span class="profile-meta-tag">
                    <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round">
                        <?php if ($payout_configured): ?>
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
                        <polyline points="22 4 12 14.01 9 11.01" />
                        <?php else: ?>
                        <circle cx="12" cy="12" r="10" />
                        <line x1="15" y1="9" x2="9" y2="15" />
                        <line x1="9" y1="9" x2="15" y2="15" />
                        <?php endif; ?>
                    </svg>
                    <?= $payout_configured ? 'Payout Configured' : 'Payout Not Set' ?>
                </span>
                <span class="profile-meta-tag">
                    <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2" />
                        <circle cx="9" cy="7" r="4" />
                        <path d="M23 21v-2a4 4 0 00-3-3.87" />
                        <path d="M16 3.13a4 4 0 010 7.75" />
                    </svg>
                    <?= (int)$total_events_hosted ?> Event<?= (int)$total_events_hosted !== 1 ? 's' : '' ?> Hosted
                </span>
                <span class="profile-meta-tag">
                    <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20 12V8H6a2 2 0 0 1-2-2c0-1.1.9-2 2-2h12v4" />
                        <path d="M4 6v12c0 1.1.9 2 2 2h14v-4" />
                        <path d="M18 12a2 2 0 0 0 0 4h4v-4h-4z" />
                    </svg>
                    <?= (int)($totalTickets ?? count($purchasedTickets)) ?> Ticket<?= (int)($totalTickets ?? count($purchasedTickets)) !== 1 ? 's' : '' ?> Purchased
                </span>
                <?php else: ?>
                <span class="profile-meta-tag">
                    <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20 12V8H6a2 2 0 0 1-2-2c0-1.1.9-2 2-2h12v4" />
                        <path d="M4 6v12c0 1.1.9 2 2 2h14v-4" />
                        <path d="M18 12a2 2 0 0 0 0 4h4v-4h-4z" />
                    </svg>
                    <?= count($purchasedTickets) ?> Ticket<?= count($purchasedTickets) !== 1 ? 's' : '' ?>
                </span>
                <?php endif; ?>
            </div>
        </div>
        <div class="profile-header-actions" style="display:flex; gap:0.5rem; flex-wrap:wrap; align-items:center;">
            <?php if ($isHost): ?>
            <a href="/host/event/create" class="admin-action-btn primary">
                <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5"
                    stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" y1="5" x2="12" y2="19" />
                    <line x1="5" y1="12" x2="19" y2="12" />
                </svg>
                Create Event
            </a>
            <?php endif; ?>
            <a href="/logout" class="admin-action-btn danger"
                style="gap:0.35rem; display:inline-flex; align-items:center; height:38px; border-radius:10px; text-decoration:none;">
                <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
                    <polyline points="16 17 21 12 16 7" />
                    <line x1="21" y1="12" x2="9" y2="12" />
                </svg>
                Logout
            </a>
        </div>
    </div>
</section>

<!-- Profile Edit Grid -->
<div class="profile-grid">

    <!-- Left Column: Forms -->
    <div class="profile-forms-col">

        <!-- Personal Information -->
        <form method="POST" action="/profile" class="form-card profile-form-card" id="profile-form">
            @csrf
            <div class="profile-form-header">
                <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2" />
                    <circle cx="12" cy="7" r="4" />
                </svg>
                <div>
                    <h2>Personal Information</h2>
                    <p class="section-note">Your public-facing organizer details.</p>
                </div>
            </div>

            <label>Full Name
                <input type="text" name="name" id="profile-name" placeholder="Your name" value="<?= htmlspecialchars($user->name) ?>" required>
            </label>
            <label>Email (Account)
                <input type="email" name="email" id="profile-email" placeholder="you@example.com"
                    value="<?= htmlspecialchars($user->email) ?>" readonly style="opacity:0.6; cursor:not-allowed;">
            </label>

            <div class="profile-field-row">
                <label>Phone
                    <input type="tel" name="phone" id="profile-phone" placeholder="+1 555 555 5555"
                        value="<?= htmlspecialchars($user->phone ?? '') ?>">
                </label>
                <label>Bio / About
                    <input type="text" name="bio" id="profile-bio" placeholder="Tell attendees about yourself"
                        value="<?= htmlspecialchars($profile['bio'] ?? '') ?>">
                </label>
            </div>

            <?php if ($isHost): ?>
            <!-- Bank / Payout Details -->
            <div class="profile-form-divider">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <rect x="1" y="4" width="22" height="16" rx="2" ry="2" />
                    <line x1="1" y1="10" x2="23" y2="10" />
                </svg>
                <span>Payout Details</span>
            </div>

            <?php 
            $profileBanksList = $supportedBanks ?? \Ace\PaystackService::getSupportedBanks();
            $profileCurrentBank = $profile['payout_bank'] ?? '';
            ?>
            <div class="profile-field-row">
                <label>Payout Bank (Paystack Supported)
                    <select name="payout_bank" id="profile-bank-name" required style="width:100%; padding:0.75rem; border-radius:10px; border:1px solid rgba(var(--muted),0.2); background:rgb(var(--surface)); color:rgb(var(--text)); font-size:0.95rem;">
                        <option value="">-- Select Your Bank --</option>
                        <?php foreach ($profileBanksList as $b): ?>
                            <option value="<?= htmlspecialchars($b['name']) ?>" data-code="<?= htmlspecialchars($b['code']) ?>" <?= (stripos($profileCurrentBank, $b['name']) !== false || $profileCurrentBank === $b['name']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($b['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>Account Number (10-Digit NUBAN)
                    <input type="text" name="payout_account" id="profile-bank-account" placeholder="e.g. 0123456789" maxlength="10"
                        value="<?= htmlspecialchars($profile['payout_account'] ?? '') ?>" required>
                </label>
            </div>
            <input type="hidden" name="payout_routing" id="profile-bank-routing" value="<?= htmlspecialchars($profile['payout_routing'] ?? '') ?>">
            <?php endif; ?>

            <div class="profile-form-footer">
                <button type="submit" class="admin-action-btn primary"
                    style="padding:0.75rem 2rem; border-radius:12px; height:46px;">
                    <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z" />
                        <polyline points="17 21 17 13 7 13 7 21" />
                        <polyline points="7 3 7 8 15 8" />
                    </svg>
                    Save Profile
                </button>
            </div>
        </form>

        <!-- Change Password -->
        <form method="POST" action="/profile/password" class="form-card profile-form-card" id="password-form">
            @csrf
            <div class="profile-form-header">
                <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2" />
                    <path d="M7 11V7a5 5 0 0110 0v4" />
                </svg>
                <div>
                    <h2>Change Password</h2>
                    <p class="section-note">Update your account password for security.</p>
                </div>
            </div>

            <label>Current Password
                <input type="password" name="current_password" id="current-password" placeholder="Enter current password" required>
            </label>

            <div class="profile-field-row">
                <label>New Password
                    <input type="password" name="new_password" id="new-password" placeholder="Min. 6 characters" required minlength="6">
                </label>
                <label>Confirm New Password
                    <input type="password" name="confirm_password" id="confirm-password" placeholder="Re-enter new password" required minlength="6">
                </label>
            </div>

            <div class="profile-form-footer">
                <button type="submit" class="admin-action-btn primary"
                    style="padding:0.75rem 2rem; border-radius:12px; height:46px;">
                    <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5"
                        stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2" />
                        <path d="M7 11V7a5 5 0 0110 0v4" />
                    </svg>
                    Update Password
                </button>
            </div>
        </form>

        <!-- Danger Zone: Account Deletion (GDPR) -->
        <div class="form-card profile-form-card" style="border: 1px solid rgba(239, 68, 68, 0.3); background: rgba(239, 68, 68, 0.02); margin-top: 1.5rem;">
            <div class="profile-form-header">
                <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="#ef4444" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="3 6 5 6 21 6"></polyline>
                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                    <line x1="10" y1="11" x2="10" y2="17"></line>
                    <line x1="14" y1="11" x2="14" y2="17"></line>
                </svg>
                <div>
                    <h2 style="color: #ef4444; margin:0;">Delete Account (GDPR)</h2>
                    <p class="section-note" style="margin:0.2rem 0 0;">Permanently delete your account &amp; personal data.</p>
                </div>
            </div>

            <p style="font-size:0.85rem; color:rgba(var(--text),0.7); line-height:1.5; margin: 0.75rem 0 1.25rem;">
                Under GDPR regulations, deleting your account will permanently purge your profile, role assignments, and personal records. Financial ledger entries (if any) will be anonymized to comply with statutory accounting requirements.
            </p>

            <button type="button" class="admin-action-btn danger" onclick="document.getElementById('delete-account-modal').classList.remove('hidden')" style="padding:0.75rem 1.5rem; border-radius:12px; height:44px; font-weight:700; width:100%; justify-content:center; cursor:pointer;">
                🗑️ Delete My Account
            </button>
        </div>
    </div>

    <!-- Right Column: Events / Tickets -->
    <div class="profile-events-col">
        <div class="form-card profile-form-card">
            <?php if ($isHost): ?>
                <div class="profile-tabs" style="display:flex; gap:0.5rem; margin-bottom:1.25rem; border-bottom:1px solid rgba(var(--muted),0.12); padding-bottom:0.75rem;">
                    <button type="button" id="tab-btn-events" class="profile-tab-btn active" onclick="switchProfileTab('events')" style="padding:0.5rem 1rem; border-radius:10px; border:none; font-weight:600; font-size:0.88rem; cursor:pointer; background:rgb(var(--brand)); color:#fff;">
                        Events Managed (<?= count($events ?? []) ?>)
                    </button>
                    <button type="button" id="tab-btn-tickets" class="profile-tab-btn" onclick="switchProfileTab('tickets')" style="padding:0.5rem 1rem; border-radius:10px; border:none; font-weight:600; font-size:0.88rem; cursor:pointer; background:rgba(var(--muted),0.1); color:rgb(var(--text));">
                        My Purchased Tickets (<?= (int)($totalTickets ?? count($purchasedTickets)) ?>)
                    </button>
                </div>
            <?php endif; ?>

            <div id="section-events-managed" style="<?= $isHost ? '' : 'display:none;' ?>">
                <div class="profile-form-header">
                    <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
                        <line x1="16" y1="2" x2="16" y2="6" />
                        <line x1="8" y1="2" x2="8" y2="6" />
                        <line x1="3" y1="10" x2="21" y2="10" />
                    </svg>
                    <div>
                        <h2>Events Managed</h2>
                        <p class="section-note">Events you've created and are managing.</p>
                    </div>
                </div>

                <?php if ($isHost): ?>
                    <?php if (!empty($events)): ?>
                    <div id="host-events" class="profile-events-list">
                        <?php foreach ($events as $ev):
                            $sold = (int)($ev['tickets_sold'] ?? 0);
                            $capacity = (int)($ev['total_capacity'] ?? 0);
                            $percent = $capacity > 0 ? round(($sold / $capacity) * 100) : 0;
                            [$statusLabel, $statusClass] = profileEventStatus($ev['status'], $ev['date']);
                            $eventDate = date('M d, Y', strtotime($ev['date']));
                            $eventImage = $ev['image'] ?? '';
                        ?>
                        <article class="profile-event-card">
                            <?php if ($eventImage): ?>
                            <div class="profile-event-img"
                                style="background-image:linear-gradient(0deg,rgba(0,0,0,.4),rgba(0,0,0,.05)), url('<?= htmlspecialchars($eventImage) ?>')">
                            </div>
                            <?php endif; ?>
                            <div class="profile-event-body">
                                <h4><?= htmlspecialchars($ev['name']) ?></h4>
                                <p class="profile-event-meta">
                                    <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2">
                                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
                                        <line x1="16" y1="2" x2="16" y2="6" />
                                        <line x1="8" y1="2" x2="8" y2="6" />
                                        <line x1="3" y1="10" x2="21" y2="10" />
                                    </svg>
                                    <?= $eventDate ?>
                                </p>
                                <?php if (!empty($ev['location'])): ?>
                                <p class="profile-event-meta">
                                    <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z" />
                                        <circle cx="12" cy="10" r="3" />
                                    </svg>
                                    <?= htmlspecialchars($ev['location']) ?>
                                </p>
                                <?php endif; ?>
                                <div style="margin-top:0.5rem;">
                                    <span style="font-weight:600;font-size:0.75rem;color:rgb(var(--text))"><?= number_format($sold) ?> / <?= number_format($capacity) ?> sold</span>
                                    <div style="height:4px;background:rgba(var(--muted),0.1);border-radius:2px;margin-top:3px;overflow:hidden;">
                                        <div style="height:100%;width:<?= min($percent, 100) ?>%;background:rgb(var(--brand));border-radius:2px;"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="profile-event-actions">
                                <span class="au-status-badge <?= $statusClass ?>"><?= $statusLabel ?></span>
                                <a href="/host/event/management/<?= $ev['id'] ?>" class="admin-action-btn" style="font-size:0.78rem;padding:0.35rem 0.65rem;border-radius:8px;">Manage</a>
                            </div>
                        </article>
                        <?php endforeach; ?>
                    </div>
                    <?php else: ?>
                    <div id="profile-events-empty" class="empty-state" style="text-align:center; padding:2rem 1rem;">
                        <p style="color:rgb(var(--muted)); margin-bottom:1rem;">You haven't created any events yet.</p>
                        <a href="/host/event/create" class="admin-action-btn primary" style="border-radius:12px; padding:0.65rem 1.5rem;">Create Your First Event</a>
                    </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>

            <div id="section-my-tickets" style="<?= $isHost ? 'display:none;' : '' ?>">
                <div class="profile-form-header">
                    <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20 12V8H6a2 2 0 0 1-2-2c0-1.1.9-2 2-2h12v4" />
                        <path d="M4 6v12c0 1.1.9 2 2 2h14v-4" />
                        <path d="M18 12a2 2 0 0 0 0 4h4v-4h-4z" />
                    </svg>
                    <div>
                        <h2>My Purchased Tickets</h2>
                        <p class="section-note">Your purchased event tickets. Available up to 5 days after the event.</p>
                    </div>
                </div>

                <?php if (!empty($purchasedTickets)): ?>
                <div class="my-tickets-list">
                    <?php foreach ($purchasedTickets as $idx => $tk):
                        $tkInitials = strtoupper(substr($tk['attendee_name'] ?? 'U', 0, 2));
                        $tkEventTs = !empty($tk['event_date']) ? strtotime($tk['event_date']) : null;
                        $tkEventDate = $tkEventTs ? date('M d, Y', $tkEventTs) : 'TBA';
                        $tkEventTime = $tkEventTs ? date('g:i A', $tkEventTs) : 'TBA';
                        $tkPurchaseTs = !empty($tk['purchase_date']) ? strtotime($tk['purchase_date']) : null;
                        $tkPurchaseDate = $tkPurchaseTs ? date('M d, Y', $tkPurchaseTs) : '—';
                        $tkIsPast = $tkEventTs ? ($tkEventTs < time()) : false;
                    ?>
                    <div class="my-ticket-card" id="my-ticket-<?= $idx ?>">
                        <div class="my-ticket-header">
                            <div>
                                <div class="my-ticket-event-name"><?= htmlspecialchars($tk['event_name']) ?></div>
                                <div class="my-ticket-event-meta">
                                    <svg viewBox="0 0 24 24" width="11" height="11" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z" />
                                        <circle cx="12" cy="10" r="3" />
                                    </svg>
                                    <?= htmlspecialchars($tk['event_location']) ?>
                                </div>
                            </div>
                            <span class="my-ticket-status-badge <?= $tk['ticket_status'] === 'Comped' ? 'badge-comped' : ($tkIsPast ? 'badge-past' : 'badge-active') ?>">
                                <?= $tkIsPast ? '✓ Attended' : ($tk['ticket_status'] === 'Comped' ? '★ Comped' : '● Active') ?>
                            </span>
                        </div>
                        <hr class="ticket-perforation" />
                        <div class="my-ticket-body">
                            <div class="ticket-attendee-row">
                                <div class="ticket-avatar"><?= $tkInitials ?></div>
                                <div class="ticket-attendee-info">
                                    <h3><?= htmlspecialchars($tk['attendee_name']) ?></h3>
                                    <p><?= htmlspecialchars($tk['ticket_type']) ?> Ticket</p>
                                </div>
                            </div>
                            <div class="ticket-details-grid">
                                <div class="ticket-detail-item">
                                    <div class="detail-label">Ticket Code</div>
                                    <div class="detail-value" style="font-family:monospace;font-size:.8rem;"><?= htmlspecialchars($tk['ticket_id']) ?></div>
                                </div>
                                <div class="ticket-detail-item">
                                    <div class="detail-label">Event Date</div>
                                    <div class="detail-value"><?= $tkEventDate ?></div>
                                </div>
                                <div class="ticket-detail-item">
                                    <div class="detail-label">Event Time</div>
                                    <div class="detail-value"><?= $tkEventTime ?></div>
                                </div>
                                <div class="ticket-detail-item">
                                    <div class="detail-label">Purchased</div>
                                    <div class="detail-value"><?= $tkPurchaseDate ?></div>
                                </div>
                            </div>
                        </div>
                        <div class="ticket-qr-section" style="text-align:center; padding:1rem 0 0.5rem; border-top:1px dashed rgba(var(--muted),0.2);">
                            <div class="ticket-qr-frame" style="background:#ffffff; padding:0.5rem; border-radius:12px; display:inline-block; border:1px solid #e2e8f0; box-shadow:0 4px 12px rgba(0,0,0,0.05);">
                                <img src="<?= ticket_qr_url($tk['ticket_id'], 200) ?>" 
                                     alt="QR Code for <?= htmlspecialchars($tk['ticket_id']) ?>" 
                                     width="130" height="130" 
                                     class="ticket-qr-img" 
                                     style="display:block; width:130px; height:130px; image-rendering:pixelated; border-radius:4px;" />
                            </div>
                            <div class="ticket-qr-code" style="font-family:monospace; font-weight:800; font-size:0.95rem; color:rgb(var(--brand)); margin-top:0.4rem;"><?= htmlspecialchars($tk['ticket_id']) ?></div>
                            <span class="ticket-qr-hint" style="font-size:0.72rem; color:rgb(var(--muted)); font-weight:600; text-transform:uppercase; letter-spacing:0.04em;">Scan at Entry Gate</span>
                        </div>
                        <div class="my-ticket-actions" style="display:flex; justify-content:flex-end; align-items:center; gap:0.5rem; flex-wrap:wrap;">
                            <?php if (!$tkIsPast && empty($tk['checked_in']) && in_array($tk['ticket_status'], ['Paid', 'Comped'])): ?>
                                <button type="button" class="transfer-ticket-btn" onclick="openTransferModal('<?= htmlspecialchars($tk['ticket_id'], ENT_QUOTES) ?>', '<?= htmlspecialchars($tk['event_name'], ENT_QUOTES) ?>', '<?= htmlspecialchars($tk['ticket_type'], ENT_QUOTES) ?>')" title="Transfer this ticket to another attendee">
                                    <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="17 1 21 5 17 9" />
                                        <path d="M3 11V9a4 4 0 0 1 4-4h14" />
                                        <polyline points="7 23 3 19 7 15" />
                                        <path d="M21 13v2a4 4 0 0 1-4 4H3" />
                                    </svg>
                                    Transfer Pass
                                </button>
                            <?php elseif (!empty($tk['checked_in'])): ?>
                                <span style="font-size:0.75rem; color:#10b981; font-weight:700; display:inline-flex; align-items:center; gap:0.25rem;">
                                    ✓ Checked In at Gate
                                </span>
                            <?php endif; ?>

                            <?php if (!$tkIsPast): ?>
                                <a href="<?= event_google_calendar_url(['name' => $tk['event_name'], 'date' => $tk['event_date'], 'location' => $tk['event_location'], 'description' => 'Admission Ticket #' . $tk['ticket_id'] . ' for ' . $tk['event_name'], 'id' => $tk['event_id']]) ?>" target="_blank" rel="noopener" class="admin-action-btn calendar-btn-profile" style="text-decoration:none; padding:0.48rem 0.85rem; font-size:0.82rem; border-radius:10px; font-weight:700; background:rgba(var(--muted),0.08); color:rgb(var(--text)); border:1px solid rgba(var(--muted),0.2); display:inline-flex; align-items:center; gap:0.35rem; min-height:38px;" title="Add event to your Calendar">
                                    <span>📅 Calendar</span>
                                </a>
                            <?php endif; ?>

                            <button type="button" class="print-ticket-btn" onclick="printTicket(<?= $idx ?>)">
                                <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="6 9 6 2 18 2 18 9" />
                                    <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2" />
                                    <rect x="6" y="14" width="12" height="8" />
                                </svg>
                                Print Ticket
                            </button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Tickets Pagination -->
                <?php if (isset($totalPages) && $totalPages > 1): ?>
                <div class="pagination" style="margin-top: 1.5rem; display: flex; justify-content: center; gap: 0.5rem; align-items: center;">
                    <?php if ($ticketPage > 1): ?>
                        <a href="/profile?ticket_page=<?= $ticketPage - 1 ?>" class="page-button" style="padding: 0.4rem 0.85rem; border-radius: 8px; background: rgba(var(--muted),0.08); text-decoration: none; color: rgb(var(--text)); font-weight: 600;">&laquo; Prev</a>
                    <?php else: ?>
                        <span class="page-button disabled" style="padding: 0.4rem 0.85rem; border-radius: 8px; background: rgba(var(--muted),0.04); color: rgba(var(--text),0.3); font-weight: 600; cursor: not-allowed;">&laquo; Prev</span>
                    <?php endif; ?>

                    <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                        <?php if ($p == $ticketPage): ?>
                            <span class="page-button active" style="padding: 0.4rem 0.85rem; border-radius: 8px; background: rgb(var(--brand)); color: #fff; font-weight: 700;"><?= $p ?></span>
                        <?php else: ?>
                            <a href="/profile?ticket_page=<?= $p ?>" class="page-button" style="padding: 0.4rem 0.85rem; border-radius: 8px; background: rgba(var(--muted),0.08); text-decoration: none; color: rgb(var(--text)); font-weight: 600;"><?= $p ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>

                    <?php if ($ticketPage < $totalPages): ?>
                        <a href="/profile?ticket_page=<?= $ticketPage + 1 ?>" class="page-button" style="padding: 0.4rem 0.85rem; border-radius: 8px; background: rgba(var(--muted),0.08); text-decoration: none; color: rgb(var(--text)); font-weight: 600;">Next &raquo;</a>
                    <?php else: ?>
                        <span class="page-button disabled" style="padding: 0.4rem 0.85rem; border-radius: 8px; background: rgba(var(--muted),0.04); color: rgba(var(--text),0.3); font-weight: 600; cursor: not-allowed;">Next &raquo;</span>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <?php else: ?>
                <div class="empty-state" style="text-align:center; padding:2rem 1rem;">
                    <p style="color:rgb(var(--muted)); margin-bottom:1rem;">You haven't purchased any tickets yet.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div id="print-ticket-container" style="display:none;"></div>
@endsection

@section('script')
<script>
function switchProfileTab(tab) {
    const eventsSec = document.getElementById('section-events-managed');
    const ticketsSec = document.getElementById('section-my-tickets');
    const btnEvents = document.getElementById('tab-btn-events');
    const btnTickets = document.getElementById('tab-btn-tickets');

    if (!eventsSec || !ticketsSec) return;

    if (tab === 'events') {
        eventsSec.style.display = 'block';
        ticketsSec.style.display = 'none';
        if (btnEvents) {
            btnEvents.style.background = 'rgb(var(--brand))';
            btnEvents.style.color = '#fff';
        }
        if (btnTickets) {
            btnTickets.style.background = 'rgba(var(--muted),0.1)';
            btnTickets.style.color = 'rgb(var(--text))';
        }
    } else {
        eventsSec.style.display = 'none';
        ticketsSec.style.display = 'block';
        if (btnTickets) {
            btnTickets.style.background = 'rgb(var(--brand))';
            btnTickets.style.color = '#fff';
        }
        if (btnEvents) {
            btnEvents.style.background = 'rgba(var(--muted),0.1)';
            btnEvents.style.color = 'rgb(var(--text))';
        }
    }
}

if (window.location.search.includes('ticket_page') || window.location.search.includes('tab=tickets')) {
    switchProfileTab('tickets');
}

function openTransferModal(ticketCode, eventName, ticketType) {
    document.getElementById('transfer-input-ticket-id').value = ticketCode;
    document.getElementById('transfer-ticket-code').textContent = ticketCode;
    document.getElementById('transfer-event-name').textContent = eventName;
    document.getElementById('transfer-ticket-type').textContent = ticketType + ' Pass';
    document.getElementById('transfer-recipient-name').value = '';
    document.getElementById('transfer-recipient-email').value = '';
    document.getElementById('transfer-note').value = '';
    document.getElementById('transfer-confirm-check').checked = false;
    document.getElementById('transfer-error-alert').style.display = 'none';
    document.getElementById('transfer-error-alert').textContent = '';

    const modal = document.getElementById('transfer-ticket-modal');
    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeTransferModal() {
    const modal = document.getElementById('transfer-ticket-modal');
    modal.classList.add('hidden');
    document.body.style.overflow = '';
}

// Handle Form Submission with AJAX
document.addEventListener('DOMContentLoaded', function() {
    const transferForm = document.getElementById('transfer-ticket-form');
    if (transferForm) {
        transferForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const form = this;
            const submitBtn = document.getElementById('transfer-submit-btn');
            const btnText = document.getElementById('transfer-btn-text');
            const errorAlert = document.getElementById('transfer-error-alert');

            errorAlert.style.display = 'none';
            submitBtn.disabled = true;
            btnText.textContent = 'Processing Transfer...';

            const formData = new FormData(form);

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
            .then(data => {
                // Success! Redirect to reload the ticket list with flash message
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

function printTicket(idx) {
    const card = document.getElementById('my-ticket-' + idx);
    if (!card) return;

    const printContainer = document.getElementById('print-ticket-container');
    printContainer.innerHTML = card.outerHTML;

    // Remove the print button and transfer button from the cloned content
    const btn = printContainer.querySelector('.my-ticket-actions');
    if (btn) btn.remove();

    // Add print class to body
    document.body.classList.add('printing-ticket');
    window.print();

    // Cleanup after print
    window.addEventListener('afterprint', function cleanup() {
        document.body.classList.remove('printing-ticket');
        printContainer.innerHTML = '';
        window.removeEventListener('afterprint', cleanup);
    });
}
</script>

<style>
/* Ticket Actions & Responsive Buttons */
.my-ticket-actions {
    display: flex;
    justify-content: flex-end;
    align-items: center;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.transfer-ticket-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.48rem 0.95rem;
    border-radius: 10px;
    background: rgba(11, 115, 102, 0.08);
    color: #0B7366;
    border: 1px solid rgba(11, 115, 102, 0.25);
    font-size: 0.84rem;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.2s ease;
    min-height: 38px;
    box-sizing: border-box;
}

.transfer-ticket-btn:hover {
    background: #0B7366;
    color: #ffffff;
    border-color: #0B7366;
    box-shadow: 0 4px 14px rgba(11, 115, 102, 0.25);
}

.transfer-ticket-btn:focus-visible {
    outline: 2px solid #0B7366;
    outline-offset: 2px;
}

/* Tablet & Mobile Responsiveness for Ticket Cards */
@media (max-width: 640px) {
    .my-ticket-actions {
        flex-direction: column-reverse;
        width: 100%;
        gap: 0.6rem;
    }
    .my-ticket-actions .transfer-ticket-btn,
    .my-ticket-actions .calendar-btn-profile,
    .my-ticket-actions .print-ticket-btn {
        width: 100% !important;
        justify-content: center;
        padding: 0.7rem 1rem !important;
        font-size: 0.88rem !important;
        min-height: 44px; /* Mobile touch target standard */
    }
}

/* Responsive Modal Styling */
.transfer-modal-card {
    max-width: 500px;
    width: 100%;
    transition: all 0.25s ease;
}

.transfer-modal-input,
.transfer-modal-textarea {
    width: 100%;
    padding: 0.75rem 0.9rem;
    border-radius: 10px;
    border: 1px solid rgba(var(--muted), 0.25);
    background: rgb(var(--card));
    color: rgb(var(--text));
    font-size: 0.9rem;
    min-height: 44px;
    box-sizing: border-box;
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
}

.transfer-modal-textarea {
    min-height: 64px;
    resize: vertical;
}

.transfer-modal-input:focus,
.transfer-modal-textarea:focus {
    outline: none;
    border-color: #0B7366;
    box-shadow: 0 0 0 3px rgba(11, 115, 102, 0.15);
}

.transfer-modal-actions {
    display: flex;
    gap: 0.75rem;
    justify-content: flex-end;
    align-items: center;
}

.transfer-modal-actions .btn-cancel,
.transfer-modal-actions .btn-submit {
    min-height: 44px;
    padding: 0.6rem 1.25rem;
    font-size: 0.88rem;
    border-radius: 10px;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    box-sizing: border-box;
}

@media (max-width: 480px) {
    .transfer-modal-card {
        padding: 1.25rem 1rem !important;
        border-radius: 16px !important;
    }
    .transfer-modal-actions {
        flex-direction: column-reverse;
        width: 100%;
        gap: 0.5rem;
    }
    .transfer-modal-actions .btn-cancel,
    .transfer-modal-actions .btn-submit {
        width: 100%;
        justify-content: center;
    }
}
</style>

<!-- Ticket Transfer Modal -->
<div class="modal-overlay hidden" id="transfer-ticket-modal" role="dialog" aria-modal="true" aria-labelledby="transfer-modal-title">
    <div class="modal-card transfer-modal-card">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.25rem; gap: 0.75rem;">
            <div>
                <span style="display: inline-block; background: rgba(11, 115, 102, 0.12); color: #0B7366; font-size: 0.75rem; font-weight: 800; padding: 2px 8px; border-radius: 6px; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px;">
                    🔄 Ticket Re-assignment
                </span>
                <h3 id="transfer-modal-title" style="margin: 0; font-size: clamp(1.1rem, 2.5vw, 1.25rem); font-weight: 800; color: rgb(var(--text));">Transfer Admission Pass</h3>
            </div>
            <button type="button" class="admin-action-btn" onclick="closeTransferModal()" style="border: none; background: transparent; font-size: 1.25rem; cursor: pointer; color: rgb(var(--muted)); padding: 0.25rem 0.5rem; line-height: 1; min-height: 36px; min-width: 36px;" aria-label="Close modal">✕</button>
        </div>

        <!-- Summary of ticket being transferred -->
        <div style="background: rgba(var(--muted), 0.06); border: 1px solid rgba(var(--muted), 0.15); border-radius: 12px; padding: 0.85rem 1rem; margin-bottom: 1.25rem;">
            <div style="font-weight: 700; color: rgb(var(--text)); font-size: 0.95rem; word-break: break-word;" id="transfer-event-name">Event Name</div>
            <div style="display: flex; gap: 0.5rem; align-items: center; margin-top: 4px; font-size: 0.8rem; color: rgb(var(--muted)); flex-wrap: wrap;">
                <span id="transfer-ticket-type" style="font-weight: 600; color: #0B7366;">General Pass</span>
                <span>•</span>
                <span>Code: <strong style="font-family: monospace; color: rgb(var(--text));" id="transfer-ticket-code">TKT-XXXXXX</strong></span>
            </div>
        </div>

        <!-- Security Notice Alert -->
        <div style="background: #fffbeb; border: 1px solid #fef3c7; border-left: 4px solid #f59e0b; border-radius: 10px; padding: 0.85rem 1rem; margin-bottom: 1.25rem;">
            <strong style="display: block; font-size: 0.8rem; color: #92400e; margin-bottom: 2px;">⚠️ Security Notice:</strong>
            <p style="margin: 0; font-size: 0.78rem; color: #78350f; line-height: 1.45;">
                Upon transfer, your current ticket pass and QR code will be <strong>permanently deactivated</strong> and cannot be used at the gate. A brand new QR pass will be emailed to the recipient.
            </p>
        </div>

        <form id="transfer-ticket-form" method="POST" action="/profile/ticket/transfer">
            <?= csrf_field() ?>
            <input type="hidden" name="ticket_id" id="transfer-input-ticket-id" value="">
            <input type="hidden" name="is_ajax" value="1">

            <div style="margin-bottom: 1rem;">
                <label style="display: block; font-size: 0.85rem; font-weight: 700; margin-bottom: 0.35rem; color: rgb(var(--text));">
                    Recipient Full Name <span style="color: #ef4444;">*</span>
                </label>
                <input type="text" name="recipient_name" id="transfer-recipient-name" required placeholder="e.g. Jane Doe" class="transfer-modal-input" autocomplete="name">
            </div>

            <div style="margin-bottom: 1rem;">
                <label style="display: block; font-size: 0.85rem; font-weight: 700; margin-bottom: 0.35rem; color: rgb(var(--text));">
                    Recipient Email Address <span style="color: #ef4444;">*</span>
                </label>
                <input type="email" name="recipient_email" id="transfer-recipient-email" required placeholder="e.g. jane@example.com" class="transfer-modal-input" autocomplete="email">
                <span style="font-size: 0.72rem; color: rgb(var(--muted)); display: block; margin-top: 3px;">The digital ticket and entry QR code will be sent to this email.</span>
            </div>

            <div style="margin-bottom: 1.25rem;">
                <label style="display: block; font-size: 0.85rem; font-weight: 700; margin-bottom: 0.35rem; color: rgb(var(--text));">
                    Personal Note <span style="font-size: 0.75rem; font-weight: normal; color: rgb(var(--muted));">(Optional)</span>
                </label>
                <textarea name="transfer_note" id="transfer-note" rows="2" placeholder="e.g. Enjoy the event! See you there!" class="transfer-modal-textarea"></textarea>
            </div>

            <div style="margin-bottom: 1.25rem; display: flex; align-items: flex-start; gap: 0.6rem;">
                <input type="checkbox" id="transfer-confirm-check" required style="margin-top: 3px; width: 18px; height: 18px; accent-color: #0B7366; cursor: pointer; flex-shrink: 0;">
                <label for="transfer-confirm-check" style="font-size: 0.8rem; color: rgb(var(--muted)); line-height: 1.45; cursor: pointer;">
                    I confirm that I want to transfer this admission pass to the person named above and relinquish ownership.
                </label>
            </div>

            <!-- Error message placeholder -->
            <div id="transfer-error-alert" style="display: none; background: #fef2f2; border: 1px solid #fecaca; color: #dc2626; font-size: 0.82rem; padding: 0.65rem 0.85rem; border-radius: 8px; margin-bottom: 1rem;"></div>

            <div class="transfer-modal-actions">
                <button type="button" class="admin-action-btn btn-cancel" onclick="closeTransferModal()">
                    Cancel
                </button>
                <button type="submit" id="transfer-submit-btn" class="admin-action-btn primary btn-submit" style="background: #0B7366; border-color: #0B7366; color: #fff;">
                    <span id="transfer-btn-text">Confirm &amp; Transfer</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Delete Account Modal -->
<div class="modal-overlay hidden" id="delete-account-modal" role="dialog" aria-modal="true">
    <div class="modal-card" style="max-width:480px;">
        <h3 style="color:#ef4444; margin-top:0;">⚠️ Permanently Delete Account</h3>
        <p style="font-size:0.88rem; color:rgba(var(--text),0.75); line-height:1.5; margin-bottom:1.25rem;">
            Are you sure you want to delete your account? Your profile and personal data will be permanently purged in compliance with GDPR.
        </p>

        <form method="POST" action="/profile/delete-account">
            <?= csrf_field() ?>
            <label style="display:block; margin-bottom:0.85rem; font-size:0.85rem; font-weight:600;">
                Confirm Current Password
                <input type="password" name="confirm_password" placeholder="Enter your password" required style="width:100%; padding:0.75rem; border-radius:10px; border:1px solid rgba(var(--muted),0.2); margin-top:0.35rem;">
            </label>

            <label style="display:block; margin-bottom:1.25rem; font-size:0.85rem; font-weight:600;">
                Type "DELETE" to confirm
                <input type="text" name="confirm_text" placeholder="DELETE" required style="width:100%; padding:0.75rem; border-radius:10px; border:1px solid rgba(var(--muted),0.2); margin-top:0.35rem; text-transform:uppercase;">
            </label>

            <div style="display:flex; gap:0.75rem; justify-content:flex-end;">
                <button type="button" class="admin-action-btn" onclick="document.getElementById('delete-account-modal').classList.add('hidden')" style="cursor:pointer;">
                    Cancel
                </button>
                <button type="submit" class="admin-action-btn danger" style="font-weight:700; cursor:pointer;">
                    Confirm &amp; Delete Permanently
                </button>
            </div>
        </form>
    </div>
</div>
@endsection