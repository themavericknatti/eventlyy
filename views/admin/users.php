<?php

/**
 * Admin Users
 *
 * This file contains the HTML markup for the admin users page of the application.
 *
 * @package Evently
 * @subpackage Views/Admin
 * @author Celio Natti
 * @license MIT License
 * @version 2.0.0
 */

$siteName = site_setting('site_title', 'Eventlyy');
?>

@extends('layouts/admin')

@section('title')
Admin Users - Manage Your <?= e($siteName) ?> Users
@endsection

@section('content')
<style>
/* ═══════════════════════════════════════════════════════════
   Admin Users — Responsive Polish & Layout Refactor
   ═══════════════════════════════════════════════════════════ */

/* Summary Stats: Fluid responsive grid */
.admin-users-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.au-stat-card {
    background: rgb(var(--card));
    border: 1px solid rgba(var(--muted), 0.14);
    border-radius: 18px;
    padding: 1.2rem 1.35rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.03);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.au-stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.06);
}

.au-stat-icon {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(var(--muted), 0.08);
    color: rgb(var(--text));
    flex-shrink: 0;
}

.au-stat-icon--brand {
    background: rgba(var(--brand), 0.12);
    color: rgb(var(--brand));
}

.au-stat-icon--success {
    background: rgba(16, 185, 129, 0.12);
    color: #10b981;
}

.au-stat-icon--warning {
    background: rgba(245, 158, 11, 0.12);
    color: #f59e0b;
}

.au-stat-body {
    display: flex;
    flex-direction: column;
}

.au-stat-num {
    font-size: 1.45rem;
    font-weight: 900;
    line-height: 1.2;
    color: rgb(var(--text));
}

.au-stat-lbl {
    font-size: 0.8rem;
    color: rgb(var(--muted));
    font-weight: 600;
    margin-top: 0.15rem;
}

/* Toolbar: Search & Filter */
.admin-users-toolbar {
    margin-bottom: 1.5rem;
}

.au-toolbar-form {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    flex-wrap: wrap;
    background: rgb(var(--card));
    border: 1px solid rgba(var(--muted), 0.14);
    border-radius: 18px;
    padding: 0.85rem 1.15rem;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.03);
}

.au-search-wrap {
    flex: 1;
    min-width: 220px;
    position: relative;
    display: flex;
    align-items: center;
}

.au-search-wrap input[type="search"] {
    width: 100%;
    padding: 0.55rem 0.85rem 0.55rem 2.3rem;
    border-radius: 11px;
    border: 1px solid rgba(var(--muted), 0.18);
    background: rgba(var(--card), 0.6);
    color: rgb(var(--text));
    font-size: 0.88rem;
    font-family: inherit;
}

.au-search-icon {
    position: absolute;
    left: 0.75rem;
    color: rgb(var(--muted));
    pointer-events: none;
}

.au-filter-group {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.au-filter-group select {
    padding: 0.55rem 0.85rem;
    border-radius: 11px;
    border: 1px solid rgba(var(--muted), 0.18);
    background: rgb(var(--card));
    color: rgb(var(--text));
    font-size: 0.85rem;
    font-family: inherit;
    cursor: pointer;
}

/* Table Section Container */
.admin-users-table-section {
    background: rgb(var(--card));
    border: 1px solid rgba(var(--muted), 0.14);
    border-radius: 22px;
    padding: 1.5rem;
    box-shadow: 0 2px 14px rgba(0, 0, 0, 0.03);
    margin-bottom: 2rem;
}

.au-table-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
    margin-bottom: 1.25rem;
    flex-wrap: wrap;
}

.au-table-wrap {
    width: 100%;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}

.au-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    font-size: 0.9rem;
    text-align: left;
}

.au-table th {
    background: rgba(var(--muted), 0.05);
    color: rgb(var(--muted));
    font-size: 0.76rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    padding: 0.85rem 1rem;
    border-bottom: 1px solid rgba(var(--muted), 0.15);
    white-space: nowrap;
}

.au-table td {
    padding: 0.95rem 1rem;
    border-bottom: 1px solid rgba(var(--muted), 0.08);
    color: rgb(var(--text));
    vertical-align: middle;
}

.au-table tr:hover td {
    background: rgba(var(--muted), 0.03);
}

/* User Avatar & Name */
.au-user-cell {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.au-avatar {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.8rem;
    font-weight: 800;
    color: #ffffff;
    background: hsl(var(--avatar-hue, 200), 65%, 45%);
    flex-shrink: 0;
}

.au-user-name {
    font-weight: 800;
    color: rgb(var(--text));
    font-size: 0.92rem;
    display: block;
    line-height: 1.25;
}

.au-user-id {
    font-size: 0.72rem;
    color: rgb(var(--muted));
    font-family: monospace;
    display: block;
    margin-top: 1px;
}

/* Role Pills */
.au-role-badge {
    font-size: 0.73rem;
    font-weight: 800;
    padding: 0.25rem 0.65rem;
    border-radius: 20px;
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    white-space: nowrap;
}

.au-role--admin {
    background: rgba(239, 68, 68, 0.12);
    color: #ef4444;
    border: 1px solid rgba(239, 68, 68, 0.25);
}

.au-role--host {
    background: rgba(139, 92, 246, 0.12);
    color: #8b5cf6;
    border: 1px solid rgba(139, 92, 246, 0.25);
}

.au-role--attendee {
    background: rgba(var(--muted), 0.1);
    color: rgb(var(--text));
    border: 1px solid rgba(var(--muted), 0.18);
}

/* Status Badges */
.au-status-badge {
    font-size: 0.73rem;
    font-weight: 800;
    padding: 0.24rem 0.65rem;
    border-radius: 20px;
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    white-space: nowrap;
}

.au-status--active {
    background: rgba(16, 185, 129, 0.12);
    color: #10b981;
    border: 1px solid rgba(16, 185, 129, 0.28);
}

.au-status--suspended {
    background: rgba(239, 68, 68, 0.12);
    color: #dc2626;
    border: 1px solid rgba(239, 68, 68, 0.28);
}

/* Action Buttons */
.admin-actions {
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    flex-wrap: nowrap;
}

.au-view-btn {
    padding: 0.42rem 0.75rem;
    font-size: 0.8rem;
    font-weight: 700;
    border-radius: 9px;
    background: rgba(var(--brand), 0.1);
    color: rgb(var(--brand)) !important;
    border: 1px solid rgba(var(--brand), 0.25);
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    transition: all 0.2s ease;
}

.au-view-btn:hover {
    background: rgba(var(--brand), 0.18);
}

.au-verify-btn {
    padding: 0.42rem 0.75rem;
    font-size: 0.8rem;
    font-weight: 700;
    border-radius: 9px;
    background: rgba(245, 158, 11, 0.12);
    color: #d97706 !important;
    border: 1px solid rgba(245, 158, 11, 0.3);
    cursor: pointer;
    transition: all 0.2s ease;
}

.au-verify-btn:hover {
    background: rgba(245, 158, 11, 0.2);
}

.au-reactivate-btn {
    padding: 0.42rem 0.75rem;
    font-size: 0.8rem;
    font-weight: 700;
    border-radius: 9px;
    background: rgba(16, 185, 129, 0.12);
    color: #10b981 !important;
    border: 1px solid rgba(16, 185, 129, 0.28);
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    transition: all 0.2s ease;
}

.au-reactivate-btn:hover {
    background: rgba(16, 185, 129, 0.2);
}

.au-suspend-btn {
    padding: 0.42rem 0.75rem;
    font-size: 0.8rem;
    font-weight: 700;
    border-radius: 9px;
    background: rgba(239, 68, 68, 0.1);
    color: #ef4444 !important;
    border: 1px solid rgba(239, 68, 68, 0.22);
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    transition: all 0.2s ease;
}

.au-suspend-btn:hover {
    background: rgba(239, 68, 68, 0.18);
    border-color: rgba(239, 68, 68, 0.35);
}

.au-btn-disabled {
    padding: 0.42rem 0.75rem;
    font-size: 0.8rem;
    font-weight: 600;
    border-radius: 9px;
    background: rgba(var(--muted), 0.08);
    color: rgb(var(--muted));
    border: 1px solid rgba(var(--muted), 0.15);
    opacity: 0.65;
    cursor: not-allowed;
}

/* ── Mobile Card Polish ── */
.au-user-card {
    background: rgba(var(--card), 0.7);
    border: 1px solid rgba(var(--muted), 0.14);
    border-radius: 18px;
    padding: 1.15rem;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.03);
}

.au-card-top {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 0.5rem;
    margin-bottom: 0.75rem;
}

.au-card-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 0.5rem;
    margin-top: 0.55rem;
    font-size: 0.85rem;
    flex-wrap: wrap;
}

.au-card-actions {
    display: flex;
    gap: 0.5rem;
    margin-top: 0.95rem;
    padding-top: 0.85rem;
    border-top: 1px solid rgba(var(--muted), 0.1);
}

.au-card-actions button,
.au-card-actions span {
    flex: 1;
    justify-content: center;
    text-align: center;
    padding: 0.55rem 0.75rem;
}

/* Modals */
.modal-card {
    width: 100%;
    max-width: min(92vw, 520px);
    background: rgb(var(--card));
    color: rgb(var(--text));
    border: 1px solid rgba(var(--muted), 0.2);
    border-radius: 22px;
    padding: 1.5rem;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.4);
    box-sizing: border-box;
}

/* ══ Tablet (<= 768px) ══ */
@media (max-width: 768px) {
    .au-table-wrap {
        display: none;
    }
    .au-cards-list {
        display: flex;
        flex-direction: column;
        gap: 0.85rem;
    }
    .au-toolbar-form {
        flex-direction: column;
        align-items: stretch;
    }
    .au-search-wrap,
    .au-filter-group select,
    .au-toolbar-form button {
        width: 100%;
    }
    .au-filter-group {
        flex-direction: column;
        width: 100%;
    }
}

/* ══ Desktop (> 768px) ══ */
@media (min-width: 769px) {
    .au-cards-list {
        display: none;
    }
}
</style>

<section class="page-hero">
    <div>
        <span class="panel-badge">Admin — Users</span>
        <h1>Registered Accounts</h1>
        <p class="lede">View and manage all registered user accounts, organizer profiles, and role privileges.</p>
    </div>
</section>

<!-- Summary Stats -->
<section class="admin-users-stats">
    <div class="au-stat-card">
        <span class="au-stat-icon">
            <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4-4v2" />
                <circle cx="9" cy="7" r="4" />
                <path d="M23 21v-2a4 4 0 00-3-3.87" />
                <path d="M16 3.13a4 4 0 010 7.75" />
            </svg>
        </span>
        <div class="au-stat-body">
            <span class="au-stat-num"><?= number_format($total_users) ?></span>
            <span class="au-stat-lbl">Total Users</span>
        </div>
    </div>
    <div class="au-stat-card">
        <span class="au-stat-icon au-stat-icon--brand">
            <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M16 21v-2a4 4 0 00-4-4H5a4 4 0 00-4-4v2" />
                <circle cx="8.5" cy="7" r="4" />
                <path d="M20 8v6" />
                <path d="M23 11h-6" />
            </svg>
        </span>
        <div class="au-stat-body">
            <span class="au-stat-num"><?= number_format($total_hosts) ?></span>
            <span class="au-stat-lbl">Hosts / Organizers</span>
        </div>
    </div>
    <div class="au-stat-card">
        <span class="au-stat-icon au-stat-icon--success">
            <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 11.08V12a10 10 0 11-5.93-9.14" />
                <polyline points="22 4 12 14.01 9 11.01" />
            </svg>
        </span>
        <div class="au-stat-body">
            <span class="au-stat-num"><?= number_format($verified_accounts) ?></span>
            <span class="au-stat-lbl">Verified Accounts</span>
        </div>
    </div>
    <div class="au-stat-card">
        <span class="au-stat-icon au-stat-icon--warning">
            <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10" />
                <line x1="12" y1="8" x2="12" y2="12" />
                <line x1="12" y1="16" x2="12.01" y2="16" />
            </svg>
        </span>
        <div class="au-stat-body">
            <span class="au-stat-num"><?= number_format($suspended_users) ?></span>
            <span class="au-stat-lbl">Suspended</span>
        </div>
    </div>
    <div class="au-stat-card" style="<?= ($unverified_hosts ?? 0) > 0 ? 'border-color: #f59e0b; background: rgba(245,158,11,0.04);' : '' ?>">
        <span class="au-stat-icon" style="<?= ($unverified_hosts ?? 0) > 0 ? 'background: rgba(245,158,11,0.12); color: #f59e0b;' : 'background: rgba(var(--muted),0.08); color: rgb(var(--muted));' ?>">
            <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" />
                <line x1="12" y1="9" x2="12" y2="13" />
                <line x1="12" y1="17" x2="12.01" y2="17" />
            </svg>
        </span>
        <div class="au-stat-body">
            <span class="au-stat-num" style="<?= ($unverified_hosts ?? 0) > 0 ? 'color: #f59e0b;' : '' ?>"><?= number_format($unverified_hosts ?? 0) ?></span>
            <span class="au-stat-lbl">Unverified Hosts</span>
            <?php if (($unverified_hosts ?? 0) > 0): ?>
                <a href="?verification=unverified_hosts" style="font-size:0.72rem; color:#f59e0b; text-decoration:underline; font-weight:700; margin-top:0.15rem; display:inline-block;">View Blocked →</a>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Toolbar: Search & Filter -->
<section class="admin-users-toolbar">
    <form method="GET" action="" class="au-toolbar-form">
        <div class="au-search-wrap">
            <svg class="au-search-icon" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="11" cy="11" r="8" />
                <line x1="21" y1="21" x2="16.65" y2="16.65" />
            </svg>
            <input type="search" name="q" id="users-search" placeholder="Search by name or email..." aria-label="Search users" value="<?= e($search ?? '') ?>" />
        </div>
        <div class="au-filter-group">
            <select name="role" id="users-role-filter" aria-label="Filter by role">
                <option value="">All Roles</option>
                <option value="attendee" <?= ($role ?? '') === 'attendee' ? 'selected' : '' ?>>Attendee</option>
                <option value="host" <?= ($role ?? '') === 'host' ? 'selected' : '' ?>>Host / Organizer</option>
                <option value="admin" <?= ($role ?? '') === 'admin' ? 'selected' : '' ?>>Admin</option>
            </select>

            <select name="status" id="users-status-filter" aria-label="Filter by status">
                <option value="">All Statuses</option>
                <option value="active" <?= ($status ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
                <option value="suspended" <?= ($status ?? '') === 'suspended' ? 'selected' : '' ?>>Suspended</option>
            </select>

            <select name="verification" id="users-verification-filter" aria-label="Filter by verification">
                <option value="">All Verification</option>
                <option value="verified" <?= ($verification ?? '') === 'verified' ? 'selected' : '' ?>>✅ Verified</option>
                <option value="unverified" <?= ($verification ?? '') === 'unverified' ? 'selected' : '' ?>>❌ All Unverified</option>
                <option value="unverified_hosts" <?= ($verification ?? '') === 'unverified_hosts' ? 'selected' : '' ?>>⚠️ Unverified Hosts (Blocked)</option>
            </select>
        </div>
        <button type="submit" class="admin-action-btn primary" style="padding: 0.55rem 1.15rem; border-radius: 11px; font-weight: 700; cursor: pointer;">
            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="22 12 18 12 15 21 9 3 6 12 2 12" />
            </svg>
            Filter
        </button>
    </form>
</section>

<!-- Users Table Section -->
<section class="admin-users-table-section">
    <div class="au-table-header">
        <h2 style="font-size: 1.25rem; margin: 0;">User Accounts</h2>
    </div>

    <!-- Desktop Table View -->
    <div class="au-table-wrap">
        <table class="au-table" id="users-table">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Email Address</th>
                    <th>Role</th>
                    <th>Account Status</th>
                    <th>Joined</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody id="users-tbody">
                <?php if (empty($users)): ?>
                    <tr><td colspan="6" style="padding:3rem; text-align:center; color:rgb(var(--muted));">No users found matching your criteria.</td></tr>
                <?php else: ?>
                    <?php 
                    $currentUserId = \Ace\Application::$app->user->id ?? 0;
                    $isCurrentSuperAdmin = method_exists(\Ace\Application::$app->user, 'isSuperAdmin') && \Ace\Application::$app->user->isSuperAdmin();

                    foreach ($users as $user):
                        $hue = abs(crc32($user['name'])) % 360;
                        $initials = strtoupper(substr($user['name'], 0, 2));
                        $isSuspended = ($user['status'] === 'suspended');
                        $isEmailVerified = !empty($user['email_verified_at']);
                        
                        $roleSlug = $user['role_slug'] ?? '';
                        $roleLabel = 'Attendee';
                        $roleClass = 'au-role--attendee';

                        if ($roleSlug === 'super-admin') {
                            $roleLabel = '👑 Super Admin';
                            $roleClass = 'au-role--admin';
                        } elseif ($roleSlug === 'admin') {
                            $roleLabel = '🛡️ Admin';
                            $roleClass = 'au-role--admin';
                        } elseif (in_array($roleSlug, ['event-organizer', 'host'])) {
                            $roleLabel = 'Host';
                            $roleClass = 'au-role--host';
                        }

                        $isHost = in_array($roleSlug, ['event-organizer', 'host']);
                        $isUnverifiedHost = $isHost && !$isEmailVerified;

                        $isSelf = ($user['id'] == $currentUserId);
                        $isTargetSuperAdmin = ($roleSlug === 'super-admin');
                        $isTargetAdmin = in_array($roleSlug, ['admin', 'super-admin']);
                        $canModify = !$isSelf && ($isCurrentSuperAdmin || !$isTargetAdmin);
                    ?>
                    <tr<?= $isUnverifiedHost ? ' style="background:rgba(245,158,11,0.03);"' : '' ?>>
                        <td>
                            <div class="au-user-cell">
                                <span class="au-avatar" style="--avatar-hue:<?= $hue ?>"><?= $initials ?></span>
                                <div>
                                    <span class="au-user-name">
                                        <?= e($user['name']) ?>
                                        <?= $isSelf ? ' <small style="color:#10b981; font-weight:700;">(You)</small>' : '' ?>
                                    </span>
                                    <span class="au-user-id">ID: #<?= (int)$user['id'] ?></span>
                                </div>
                            </div>
                        </td>
                        <td class="au-email-cell" style="font-size:0.88rem;">
                            <span style="color:rgb(var(--text)); font-weight:600;"><?= e($user['email']) ?></span>
                            <?php if ($isUnverifiedHost): ?>
                                <span class="au-status-badge" style="background:rgba(245,158,11,0.12); color:#f59e0b; font-size:0.68rem; margin-left:0.4rem;">⚠️ Unverified Host</span>
                            <?php elseif ($isEmailVerified): ?>
                                <span style="font-size:0.7rem; color:#10b981; font-weight:700; margin-left:0.4rem;">✓ Verified</span>
                            <?php else: ?>
                                <span style="font-size:0.7rem; color:rgb(var(--muted)); margin-left:0.4rem;">Unverified</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="au-role-badge <?= $roleClass ?>"><?= $roleLabel ?></span>
                        </td>
                        <td>
                            <?php if ($isSuspended): ?>
                                <span class="au-status-badge au-status--suspended">Suspended</span>
                            <?php else: ?>
                                <span class="au-status-badge au-status--active">Active</span>
                            <?php endif; ?>
                        </td>
                        <td class="au-date-cell" style="font-size:0.84rem; white-space:nowrap; color:rgb(var(--muted));">
                            <?= date('M d, Y', strtotime($user['created_at'])) ?>
                        </td>
                        <td style="text-align: right;">
                            <div class="admin-actions" style="justify-content: flex-end;">
                                <button type="button" class="au-view-btn" data-user-id="<?= $user['id'] ?>" data-detail-url="<?= route('admin/users/detail', ['id' => $user['id']]) ?>" title="View user profile">
                                    <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                                        <circle cx="12" cy="12" r="3" />
                                    </svg>
                                    View
                                </button>
                                <?php if ($isUnverifiedHost && $canModify): ?>
                                    <button type="button" class="au-verify-btn" data-user-id="<?= $user['id'] ?>" data-user-name="<?= e($user['name']) ?>" title="Manually verify this host's email">
                                        ✓ Verify
                                    </button>
                                <?php endif; ?>
                                <?php if ($isSelf): ?>
                                    <span class="au-btn-disabled" title="You cannot suspend your own active account">Self</span>
                                <?php elseif (!$canModify): ?>
                                    <span class="au-btn-disabled" title="Admin and Super Admin accounts are protected">🔒 Protected</span>
                                <?php elseif ($isSuspended): ?>
                                    <button type="button" class="au-reactivate-btn" data-user-id="<?= $user['id'] ?>" data-user-name="<?= e($user['name']) ?>">
                                        <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5">
                                            <polyline points="23 4 23 10 17 10" />
                                            <path d="M20.49 15a9 9 0 11-2.12-9.36L23 10" />
                                        </svg>
                                        Reactivate
                                    </button>
                                <?php else: ?>
                                    <button type="button" class="au-suspend-btn" data-user-id="<?= $user['id'] ?>" data-user-name="<?= e($user['name']) ?>">
                                        <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5">
                                            <circle cx="12" cy="12" r="10" />
                                            <line x1="4.93" y1="4.93" x2="19.07" y2="19.07" />
                                        </svg>
                                        Suspend
                                    </button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Mobile Card View (<= 768px) -->
    <div class="au-cards-list" id="users-cards">
        <?php if (!empty($users)): ?>
            <?php foreach ($users as $user):
                $hue = abs(crc32($user['name'])) % 360;
                $initials = strtoupper(substr($user['name'], 0, 2));
                $isSuspended = ($user['status'] === 'suspended');
                $isEmailVerified = !empty($user['email_verified_at']);

                $roleSlug = $user['role_slug'] ?? '';
                $roleLabel = 'Attendee';
                $roleClass = 'au-role--attendee';

                if ($roleSlug === 'super-admin') {
                    $roleLabel = '👑 Super Admin';
                    $roleClass = 'au-role--admin';
                } elseif ($roleSlug === 'admin') {
                    $roleLabel = '🛡️ Admin';
                    $roleClass = 'au-role--admin';
                } elseif (in_array($roleSlug, ['event-organizer', 'host'])) {
                    $roleLabel = 'Host';
                    $roleClass = 'au-role--host';
                }

                $isHost = in_array($roleSlug, ['event-organizer', 'host']);
                $isUnverifiedHost = $isHost && !$isEmailVerified;

                $isSelf = ($user['id'] == $currentUserId);
                $isTargetSuperAdmin = ($roleSlug === 'super-admin');
                $isTargetAdmin = in_array($roleSlug, ['admin', 'super-admin']);
                $canModify = !$isSelf && ($isCurrentSuperAdmin || !$isTargetAdmin);
            ?>
            <article class="au-user-card"<?= $isUnverifiedHost ? ' style="border-color:rgba(245,158,11,0.3);"' : '' ?>>
                <div class="au-card-top">
                    <div class="au-user-cell">
                        <span class="au-avatar" style="--avatar-hue:<?= $hue ?>"><?= $initials ?></span>
                        <div>
                            <span class="au-user-name">
                                <?= e($user['name']) ?>
                                <?= $isSelf ? ' <small style="color:#10b981; font-weight:700;">(You)</small>' : '' ?>
                            </span>
                            <span style="font-size:0.8rem; color:rgb(var(--muted));"><?= e($user['email']) ?></span>
                        </div>
                    </div>
                    <div style="display:flex; flex-direction:column; align-items:flex-end; gap:0.25rem;">
                        <?php if ($isSuspended): ?>
                            <span class="au-status-badge au-status--suspended">Suspended</span>
                        <?php else: ?>
                            <span class="au-status-badge au-status--active">Active</span>
                        <?php endif; ?>
                        <?php if ($isUnverifiedHost): ?>
                            <span class="au-status-badge" style="background:rgba(245,158,11,0.12); color:#f59e0b; font-size:0.65rem;">⚠️ Unverified</span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="au-card-meta">
                    <span class="au-role-badge <?= $roleClass ?>"><?= $roleLabel ?></span>
                    <span style="font-size:0.78rem; color:rgb(var(--muted));">Joined <?= date('M d, Y', strtotime($user['created_at'])) ?></span>
                </div>

                <div class="au-card-actions">
                    <button type="button" class="au-view-btn" data-user-id="<?= $user['id'] ?>" data-detail-url="<?= route('admin/users/detail', ['id' => $user['id']]) ?>">View</button>
                    <?php if ($isUnverifiedHost && $canModify): ?>
                        <button type="button" class="au-verify-btn" data-user-id="<?= $user['id'] ?>" data-user-name="<?= e($user['name']) ?>">✓ Verify</button>
                    <?php endif; ?>
                    <?php if ($isSelf): ?>
                        <span class="au-btn-disabled">Self</span>
                    <?php elseif (!$canModify): ?>
                        <span class="au-btn-disabled">🔒 Protected</span>
                    <?php elseif ($isSuspended): ?>
                        <button type="button" class="au-reactivate-btn" data-user-id="<?= $user['id'] ?>" data-user-name="<?= e($user['name']) ?>">Reactivate</button>
                    <?php else: ?>
                        <button type="button" class="au-suspend-btn" data-user-id="<?= $user['id'] ?>" data-user-name="<?= e($user['name']) ?>">Suspend</button>
                    <?php endif; ?>
                </div>
            </article>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Empty State -->
    <div id="users-empty" class="empty-state <?= !empty($users) ? 'hidden' : '' ?>" style="text-align:center; padding:2rem 1rem; color:rgb(var(--muted));">
        <p>No users found matching your search.</p>
    </div>

    <!-- Pagination -->
    <?php if (($totalPages ?? 1) > 1): ?>
    <nav class="au-pagination" aria-label="Users pagination" id="users-pagination">
        <?php if ($page > 1): ?>
            <a href="?page=<?= $page - 1 ?>&q=<?= urlencode($search ?? '') ?>&role=<?= urlencode($role ?? '') ?>&status=<?= urlencode($status ?? '') ?>&verification=<?= urlencode($verification ?? '') ?>" class="au-page-btn">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="15 18 9 12 15 6" />
                </svg>
                Prev
            </a>
        <?php else: ?>
            <span class="au-page-btn disabled" aria-disabled="true">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="15 18 9 12 15 6" />
                </svg>
                Prev
            </span>
        <?php endif; ?>

        <?php foreach (pagination_range($page, $totalPages) as $pNum): ?>
            <?php if ($pNum === '...'): ?>
                <span class="au-page-ellipsis" aria-hidden="true">&hellip;</span>
            <?php elseif ($pNum === $page): ?>
                <span class="au-page-num active" aria-current="page"><?= $pNum ?></span>
            <?php else: ?>
                <a href="?page=<?= $pNum ?>&q=<?= urlencode($search ?? '') ?>&role=<?= urlencode($role ?? '') ?>&status=<?= urlencode($status ?? '') ?>&verification=<?= urlencode($verification ?? '') ?>" class="au-page-num"><?= $pNum ?></a>
            <?php endif; ?>
        <?php endforeach; ?>

        <?php if ($page < $totalPages): ?>
            <a href="?page=<?= $page + 1 ?>&q=<?= urlencode($search ?? '') ?>&role=<?= urlencode($role ?? '') ?>&status=<?= urlencode($status ?? '') ?>&verification=<?= urlencode($verification ?? '') ?>" class="au-page-btn">
                Next
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="9 18 15 12 9 6" />
                </svg>
            </a>
        <?php else: ?>
            <span class="au-page-btn disabled" aria-disabled="true">
                Next
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="9 18 15 12 9 6" />
                </svg>
            </span>
        <?php endif; ?>
    </nav>
    <?php endif; ?>
</section>

<!-- Confirm Suspend Modal -->
<div class="modal-overlay hidden" id="suspend-modal" role="dialog" aria-modal="true" aria-labelledby="suspend-modal-title">
    <div class="modal-card">
        <h3 id="suspend-modal-title" style="margin: 0 0 0.5rem; font-size: 1.25rem;">Suspend User</h3>
        <p style="margin: 0 0 1.25rem; font-size: 0.9rem; color: rgb(var(--muted)); line-height: 1.5;">
            Are you sure you want to suspend <strong id="suspend-user-name" style="color:rgb(var(--text));">this user</strong>? They will not be able to log in until reactivated.
        </p>
        <form method="POST" action="{{ route('admin/users') }}">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="suspend" />
            <input type="hidden" name="user_id" id="suspend-user-id" value="" />
            <div class="modal-actions" style="display: flex; gap: 0.75rem; justify-content: flex-end;">
                <button type="button" class="admin-action-btn" id="suspend-cancel-btn" style="padding: 0.55rem 1.15rem; border-radius: 11px;">Cancel</button>
                <button type="submit" class="admin-action-btn danger" style="padding: 0.55rem 1.15rem; border-radius: 11px; font-weight: 800;">Yes, Suspend</button>
            </div>
        </form>
    </div>
</div>

<!-- Confirm Reactivate Modal -->
<div class="modal-overlay hidden" id="reactivate-modal" role="dialog" aria-modal="true" aria-labelledby="reactivate-modal-title">
    <div class="modal-card">
        <h3 id="reactivate-modal-title" style="margin: 0 0 0.5rem; font-size: 1.25rem;">Reactivate User</h3>
        <p style="margin: 0 0 1.25rem; font-size: 0.9rem; color: rgb(var(--muted)); line-height: 1.5;">
            Are you sure you want to reactivate <strong id="reactivate-user-name" style="color:rgb(var(--text));">this user</strong>? Their account status will be restored to active.
        </p>
        <form method="POST" action="{{ route('admin/users') }}">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="reactivate" />
            <input type="hidden" name="user_id" id="reactivate-user-id" value="" />
            <div class="modal-actions" style="display: flex; gap: 0.75rem; justify-content: flex-end;">
                <button type="button" class="admin-action-btn" id="reactivate-cancel-btn" style="padding: 0.55rem 1.15rem; border-radius: 11px;">Cancel</button>
                <button type="submit" class="admin-action-btn primary" style="padding: 0.55rem 1.15rem; border-radius: 11px; font-weight: 800;">Yes, Reactivate</button>
            </div>
        </form>
    </div>
</div>

<!-- Confirm Verify Email Modal -->
<div class="modal-overlay hidden" id="verify-modal" role="dialog" aria-modal="true" aria-labelledby="verify-modal-title">
    <div class="modal-card">
        <h3 id="verify-modal-title" style="margin: 0 0 0.5rem; font-size: 1.25rem; color:#f59e0b;">✓ Manually Verify Email</h3>
        <p style="margin: 0 0 1.25rem; font-size: 0.9rem; color: rgb(var(--muted)); line-height: 1.5;">
            Manually verify the email for <strong id="verify-user-name" style="color:rgb(var(--text));">this user</strong>? This will allow them to create and manage events immediately.
        </p>
        <form method="POST" action="{{ route('admin/users') }}">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="verify_email" />
            <input type="hidden" name="user_id" id="verify-user-id" value="" />
            <div class="modal-actions" style="display: flex; gap: 0.75rem; justify-content: flex-end;">
                <button type="button" class="admin-action-btn" id="verify-cancel-btn" style="padding: 0.55rem 1.15rem; border-radius: 11px;">Cancel</button>
                <button type="submit" class="admin-action-btn primary" style="background:#f59e0b; border-color:#f59e0b; padding: 0.55rem 1.15rem; border-radius: 11px; font-weight: 800;">Yes, Verify Email</button>
            </div>
        </form>
    </div>
</div>

<!-- Confirm Resend Verification Modal -->
<div class="modal-overlay hidden" id="resend-modal" role="dialog" aria-modal="true" aria-labelledby="resend-modal-title">
    <div class="modal-card">
        <h3 id="resend-modal-title" style="margin: 0 0 0.5rem; font-size: 1.25rem;">✉️ Resend Verification Email</h3>
        <p style="margin: 0 0 1.25rem; font-size: 0.9rem; color: rgb(var(--muted)); line-height: 1.5;">
            Resend a verification email to <strong id="resend-user-name" style="color:rgb(var(--text));">this user</strong>? A new verification link will be generated and sent to their email address.
        </p>
        <form method="POST" action="{{ route('admin/users') }}">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="resend_verification" />
            <input type="hidden" name="user_id" id="resend-user-id" value="" />
            <div class="modal-actions" style="display: flex; gap: 0.75rem; justify-content: flex-end;">
                <button type="button" class="admin-action-btn" id="resend-cancel-btn" style="padding: 0.55rem 1.15rem; border-radius: 11px;">Cancel</button>
                <button type="submit" class="admin-action-btn primary" style="padding: 0.55rem 1.15rem; border-radius: 11px; font-weight: 800;">Yes, Resend</button>
            </div>
        </form>
    </div>
</div>

<!-- Profile Detail View Modal -->
<div class="modal-overlay hidden" id="view-modal" role="dialog" aria-modal="true" aria-labelledby="view-modal-title">
    <div class="modal-card" style="max-width: min(92vw, 720px); max-height: 85vh; display: flex; flex-direction: column; overflow: hidden; padding: 1.75rem;">
        <!-- Header (Fixed) -->
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.25rem; border-bottom:1px solid rgba(var(--muted),0.15); padding-bottom:0.85rem; flex-shrink:0;">
            <h3 id="view-modal-title" style="margin:0; font-size:1.3rem;">User Profile Detail</h3>
            <button type="button" id="view-close-x-btn" style="background:none; border:none; font-size:1.6rem; line-height:1; cursor:pointer; color:rgb(var(--muted)); padding:0 0.4rem; transition:color 0.2s ease;">&times;</button>
        </div>
        
        <!-- Scrollable content area -->
        <div style="overflow-y:auto; flex-grow:1; padding-right:0.5rem;">
            <div id="view-modal-loading" style="text-align:center; padding:3rem 0;">
                <svg class="animate-spin" viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="2" style="margin:0 auto 1rem; color:rgb(var(--brand));">
                    <circle cx="12" cy="12" r="10" stroke-opacity="0.25" />
                    <path d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                </svg>
                <p style="margin:0; color:rgb(var(--muted)); font-size:0.9rem;">Fetching profile details...</p>
            </div>

            <div id="view-modal-content" class="hidden">
                <!-- Basic Details -->
                <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(180px, 1fr)); gap:1.25rem; margin-bottom:1.5rem;">
                    <div>
                        <label style="font-size:0.75rem; color:rgb(var(--muted)); text-transform:uppercase; font-weight:700; letter-spacing:0.03em;">Full Name</label>
                        <p id="vd-name" style="margin:0.25rem 0 0; font-weight:800; font-size:1.05rem; color:rgb(var(--text));"></p>
                    </div>
                    <div>
                        <label style="font-size:0.75rem; color:rgb(var(--muted)); text-transform:uppercase; font-weight:700; letter-spacing:0.03em;">Email Address</label>
                        <p id="vd-email" style="margin:0.25rem 0 0; font-weight:600; color:rgb(var(--text));"></p>
                    </div>
                    <div>
                        <label style="font-size:0.75rem; color:rgb(var(--muted)); text-transform:uppercase; font-weight:700; letter-spacing:0.03em;">Phone Number</label>
                        <p id="vd-phone" style="margin:0.25rem 0 0; color:rgb(var(--text));"></p>
                    </div>
                    <div>
                        <label style="font-size:0.75rem; color:rgb(var(--muted)); text-transform:uppercase; font-weight:700; letter-spacing:0.03em;">Joined Date</label>
                        <p id="vd-joined" style="margin:0.25rem 0 0; color:rgb(var(--text));"></p>
                    </div>
                    <div>
                        <label style="font-size:0.75rem; color:rgb(var(--muted)); text-transform:uppercase; font-weight:700; letter-spacing:0.03em;">Status</label>
                        <p style="margin:0.25rem 0 0;"><span id="vd-status" class="au-status-badge"></span></p>
                    </div>
                    <div>
                        <label style="font-size:0.75rem; color:rgb(var(--muted)); text-transform:uppercase; font-weight:700; letter-spacing:0.03em;">System Role</label>
                        <p style="margin:0.25rem 0 0;"><span id="vd-role" class="au-role-badge"></span></p>
                    </div>
                </div>

                <!-- Email Verification Status -->
                <div id="vd-verification-section" style="background:rgba(var(--muted), 0.05); border:1px solid rgba(var(--muted), 0.14); border-radius:14px; padding:1rem 1.25rem; margin-bottom:1.5rem;">
                    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:0.75rem;">
                        <div>
                            <label style="font-size:0.75rem; color:rgb(var(--muted)); text-transform:uppercase; font-weight:700;">Email Verification</label>
                            <p style="margin:0.25rem 0 0;">
                                <span id="vd-verification-badge"></span>
                                <span id="vd-verification-date" style="font-size:0.8rem; color:rgb(var(--muted)); margin-left:0.5rem;"></span>
                            </p>
                            <p id="vd-verification-warning" class="hidden" style="margin:0.35rem 0 0; font-size:0.8rem; color:#f59e0b; font-weight:600;">
                                ⚠️ This host cannot create events until their email is verified.
                            </p>
                        </div>
                        <div id="vd-verification-actions" class="hidden" style="display:flex; gap:0.5rem; flex-wrap:wrap;">
                            <button type="button" class="au-verify-btn au-modal-verify-btn" style="font-size:0.8rem; padding:0.35rem 0.85rem;">
                                ✓ Verify Email
                            </button>
                            <button type="button" class="admin-action-btn au-modal-resend-btn" style="font-size:0.8rem; padding:0.35rem 0.85rem; border-radius:9px;">
                                ✉️ Resend Link
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Role-Specific Info Container -->
                <div id="vd-role-info" style="border-top:1px solid rgba(var(--muted), 0.12); padding-top:1.25rem;">
                    
                    <!-- Attendee Section -->
                    <div id="vd-section-attendee" class="hidden">
                        <h4 style="margin:0 0 1rem; color:rgb(var(--text)); font-size:1.05rem; font-weight:700;">Attendee Activity</h4>
                        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(180px, 1fr)); gap:1rem;">
                            <div class="au-stat-card" style="margin:0; padding:1rem; background:rgba(var(--muted), 0.04); border:1px solid rgba(var(--muted), 0.1);">
                                <div class="au-stat-body">
                                    <span id="vd-tickets-bought" class="au-stat-num" style="font-size:1.6rem; line-height:1.2;">0</span>
                                    <span class="au-stat-lbl">Tickets Purchased</span>
                                </div>
                            </div>
                            <div class="au-stat-card" style="margin:0; padding:1rem; background:rgba(var(--muted), 0.04); border:1px solid rgba(var(--muted), 0.1);">
                                <div class="au-stat-body">
                                    <span id="vd-total-spent" class="au-stat-num" style="font-size:1.6rem; line-height:1.2; color:rgb(var(--brand));">$0.00</span>
                                    <span class="au-stat-lbl">Total Spent</span>
                                </div>
                            </div>
                            <div style="grid-column: 1 / -1;">
                                <label style="font-size:0.75rem; color:rgb(var(--muted)); text-transform:uppercase; font-weight:700;">Last Ticket Purchase</label>
                                <p id="vd-last-purchase" style="margin:0.25rem 0 0; font-weight:600; color:rgb(var(--text));"></p>
                            </div>
                        </div>
                    </div>

                    <!-- Host Section -->
                    <div id="vd-section-host" class="hidden">
                        <h4 style="margin:0 0 1rem; color:rgb(var(--text)); font-size:1.05rem; font-weight:700;">Organizer &amp; Payout Metrics</h4>
                        
                        <div style="margin-bottom:1.25rem;">
                            <label style="font-size:0.75rem; color:rgb(var(--muted)); text-transform:uppercase; font-weight:700;">Bio / Description</label>
                            <p id="vd-bio" style="margin:0.25rem 0 0; font-style:italic; line-height:1.5; color:rgb(var(--text)); font-size:0.9rem;"></p>
                        </div>

                        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(130px, 1fr)); gap:0.75rem; margin-bottom:1.5rem;">
                            <div class="au-stat-card" style="margin:0; padding:0.85rem; background:rgba(var(--muted), 0.04); border:1px solid rgba(var(--muted), 0.1);">
                                <div class="au-stat-body">
                                    <span id="vd-total-events" class="au-stat-num" style="font-size:1.3rem; line-height:1.2;">0</span>
                                    <span class="au-stat-lbl">Events Hosted</span>
                                </div>
                            </div>
                            <div class="au-stat-card" style="margin:0; padding:0.85rem; background:rgba(var(--muted), 0.04); border:1px solid rgba(var(--muted), 0.1);">
                                <div class="au-stat-body">
                                    <span id="vd-tickets-sold" class="au-stat-num" style="font-size:1.3rem; line-height:1.2;">0</span>
                                    <span class="au-stat-lbl">Tickets Sold</span>
                                </div>
                            </div>
                            <div class="au-stat-card" style="margin:0; padding:0.85rem; background:rgba(var(--muted), 0.04); border:1px solid rgba(var(--muted), 0.1);">
                                <div class="au-stat-body">
                                    <span id="vd-total-revenue" class="au-stat-num" style="font-size:1.3rem; line-height:1.2;">$0.00</span>
                                    <span class="au-stat-lbl">Total Revenue</span>
                                </div>
                            </div>
                            <div class="au-stat-card" style="margin:0; padding:0.85rem; background:rgba(var(--muted), 0.04); border:1px solid rgba(var(--muted), 0.1);">
                                <div class="au-stat-body">
                                    <span id="vd-available-balance" class="au-stat-num" style="font-size:1.3rem; line-height:1.2; color:#10b981;">$0.00</span>
                                    <span class="au-stat-lbl">Avail. Balance</span>
                                </div>
                            </div>
                        </div>

                        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(180px, 1fr)); gap:1rem; border-top:1px dashed rgba(var(--muted), 0.15); padding-top:1rem;">
                            <div>
                                <label style="font-size:0.75rem; color:rgb(var(--muted)); text-transform:uppercase; font-weight:700;">Payout Bank</label>
                                <p id="vd-bank" style="margin:0.25rem 0 0; font-weight:700; color:rgb(var(--text));"></p>
                            </div>
                            <div>
                                <label style="font-size:0.75rem; color:rgb(var(--muted)); text-transform:uppercase; font-weight:700;">Account (Masked)</label>
                                <p id="vd-account" style="margin:0.25rem 0 0; font-family:monospace; font-weight:700; color:rgb(var(--text));"></p>
                            </div>
                            <div>
                                <label style="font-size:0.75rem; color:rgb(var(--muted)); text-transform:uppercase; font-weight:700;">Routing / Sort Code</label>
                                <p id="vd-routing" style="margin:0.25rem 0 0; font-family:monospace; color:rgb(var(--text));"></p>
                            </div>
                            <div>
                                <label style="font-size:0.75rem; color:rgb(var(--muted)); text-transform:uppercase; font-weight:700;">Host Rating</label>
                                <p id="vd-rating" style="margin:0.25rem 0 0; font-weight:700; color:rgb(var(--text));"></p>
                            </div>
                        </div>

                        <!-- Host Recent Events -->
                        <div style="margin-top:1.25rem; border-top:1px dashed rgba(var(--muted), 0.15); padding-top:1rem;">
                            <h5 style="margin:0 0 0.75rem; font-size:0.92rem; font-weight:700; color:rgb(var(--text));">Recent Events Hosted</h5>
                            <div id="vd-host-events-list" style="display:flex; flex-direction:column; gap:0.5rem;">
                                <!-- Dynamic items populated by JS -->
                            </div>
                        </div>

                        <!-- Host Recent Payout Requests -->
                        <div style="margin-top:1.25rem; border-top:1px dashed rgba(var(--muted), 0.15); padding-top:1rem;">
                            <h5 style="margin:0 0 0.75rem; font-size:0.92rem; font-weight:700; color:rgb(var(--text));">Recent Payout History</h5>
                            <div id="vd-host-payouts-list" style="display:flex; flex-direction:column; gap:0.5rem;">
                                <!-- Dynamic items populated by JS -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer (Fixed) -->
        <div class="modal-actions" style="margin-top:1.25rem; border-top:1px solid rgba(var(--muted), 0.15); padding-top:0.85rem; display:flex; justify-content:flex-end; flex-shrink:0;">
            <button type="button" class="admin-action-btn primary" id="view-close-btn" style="padding: 0.55rem 1.35rem; border-radius: 11px; font-weight: 800;">Close</button>
        </div>
    </div>
</div>
@endsection