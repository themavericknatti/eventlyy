<?php

/**
 * Admin Users
 *
 * This file contains the HTML markup for the admin users page of the application.
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
Admin Users - Manage Your <?= e($siteName) ?> Users
@endsection

@section('content')
<section class="page-hero">
    <div>
        <span class="panel-badge">Admin — Users</span>
        <h1>Registered Accounts</h1>
        <p class="lede">View and manage all registered users and organizer profiles.</p>
    </div>
</section>

<!-- Summary Stats -->
<section class="admin-users-stats">

    <div class="au-stat-card">
        <span class="au-stat-icon">
            <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round">
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
            <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round">
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
            <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round">
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
            <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round">
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
        <span class="au-stat-icon" style="<?= ($unverified_hosts ?? 0) > 0 ? 'background: rgba(245,158,11,0.12); color: #f59e0b;' : 'background: rgba(255,255,255,0.04); color: rgb(var(--muted));' ?>">
            <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round">
                <path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" />
                <line x1="12" y1="9" x2="12" y2="13" />
                <line x1="12" y1="17" x2="12.01" y2="17" />
            </svg>
        </span>
        <div class="au-stat-body">
            <span class="au-stat-num" style="<?= ($unverified_hosts ?? 0) > 0 ? 'color: #f59e0b;' : '' ?>"><?= number_format($unverified_hosts ?? 0) ?></span>
            <span class="au-stat-lbl">Unverified Hosts</span>
            <?php if (($unverified_hosts ?? 0) > 0): ?>
                <a href="?verification=unverified_hosts" style="font-size:0.7rem; color:#f59e0b; text-decoration:underline; margin-top:0.15rem; display:inline-block;">View Blocked →</a>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Toolbar: Search & Filter -->
<section class="admin-users-toolbar">

    <form method="GET" action="" class="au-toolbar-form">
        <div class="au-search-wrap">
            <svg class="au-search-icon" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor"
                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="11" cy="11" r="8" />
                <line x1="21" y1="21" x2="16.65" y2="16.65" />
            </svg>
            <input type="search" name="q" id="users-search" placeholder="Search by name or email..."
                aria-label="Search users" value="<?= e($search ?? '') ?>" />
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
        <button type="submit" class="admin-action-btn primary">
            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5"
                stroke-linecap="round" stroke-linejoin="round">
                <polyline points="22 12 18 12 15 21 9 3 6 12 2 12" />
            </svg>
            Filter
        </button>
    </form>
</section>

<!-- Users Table -->
<section class="admin-users-table-section">
    <div class="au-table-header">
        <h2>Users</h2>
        <!-- PHP: href="export-users.php?format=csv" -->
        <a href="#" class="admin-action-btn">
            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5"
                stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4" />
                <polyline points="7 10 12 15 17 10" />
                <line x1="12" y1="15" x2="12" y2="3" />
            </svg>
            Export CSV
        </a>
    </div>

    <!-- Desktop Table View -->
    <div class="au-table-wrap">
        <table class="au-table" id="users-table">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Joined</th>
                    <th class="au-th-actions">Actions</th>
                </tr>
            </thead>
            <tbody id="users-tbody">
                <?php if (empty($users)): ?>
                    <tr><td colspan="6" style="padding:2rem;text-align:center;">No users found.</td></tr>
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
                                <span class="au-user-name"><?= e($user['name']) ?><?= $isSelf ? ' <small style="color:#10b981; font-weight:600;">(You)</small>' : '' ?></span>
                            </div>
                        </td>
                        <td class="au-email-cell"><?= e($user['email']) ?></td>
                        <td><span class="au-role-badge <?= $roleClass ?>"><?= $roleLabel ?></span></td>
                        <td>
                            <?php if ($isSuspended): ?>
                                <span class="au-status-badge au-status--suspended">Suspended</span>
                            <?php else: ?>
                                <span class="au-status-badge au-status--active">Active</span>
                            <?php endif; ?>
                            <?php if ($isUnverifiedHost): ?>
                                <span class="au-status-badge" style="background:rgba(245,158,11,0.12); color:#f59e0b; font-size:0.7rem; margin-top:0.25rem; display:inline-block;">⚠️ Unverified</span>
                            <?php elseif ($isEmailVerified): ?>
                                <span style="font-size:0.7rem; color:#10b981; display:inline-block; margin-top:0.15rem;">✓ Verified</span>
                            <?php else: ?>
                                <span style="font-size:0.7rem; color:var(--text-muted); display:inline-block; margin-top:0.15rem;">Unverified</span>
                            <?php endif; ?>
                        </td>
                        <td class="au-date-cell"><?= date('M d, Y', strtotime($user['created_at'])) ?></td>
                        <td>
                            <div class="admin-actions">
                                <button type="button" class="admin-action-btn au-view-btn" data-user-id="<?= $user['id'] ?>" data-detail-url="<?= route('admin/users/detail', ['id' => $user['id']]) ?>" title="View user details">
                                    <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                                        <circle cx="12" cy="12" r="3" />
                                    </svg>
                                    View
                                </button>
                                <?php if ($isUnverifiedHost && $canModify): ?>
                                    <button type="button" class="admin-action-btn au-verify-btn" data-user-id="<?= $user['id'] ?>" data-user-name="<?= e($user['name']) ?>" style="color:#f59e0b; border-color:rgba(245,158,11,0.3);" title="Manually verify this host's email">
                                        ✓ Verify
                                    </button>
                                <?php endif; ?>
                                <?php if ($isSelf): ?>
                                    <button type="button" class="admin-action-btn" disabled style="opacity:0.45; cursor:not-allowed;" title="You cannot suspend your own active account">
                                        Self
                                    </button>
                                <?php elseif (!$canModify): ?>
                                    <button type="button" class="admin-action-btn" disabled style="opacity:0.45; cursor:not-allowed;" title="Admin and Super Admin accounts are protected">
                                        🔒 Protected
                                    </button>
                                <?php elseif ($isSuspended): ?>
                                    <button type="button" class="admin-action-btn primary au-reactivate-btn" data-user-id="<?= $user['id'] ?>" data-user-name="<?= e($user['name']) ?>">
                                        <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2">
                                            <polyline points="23 4 23 10 17 10" />
                                            <path d="M20.49 15a9 9 0 11-2.12-9.36L23 10" />
                                        </svg>
                                        Reactivate
                                    </button>
                                <?php else: ?>
                                    <button type="button" class="admin-action-btn danger au-suspend-btn" data-user-id="<?= $user['id'] ?>" data-user-name="<?= e($user['name']) ?>">
                                        <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2">
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

    <!-- Mobile Card View (hidden on desktop, shown on mobile) -->
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
            <article class="au-user-card"<?= $isUnverifiedHost ? ' style="border-color:rgba(245,158,11,0.25);"' : '' ?>>
                <div class="au-card-top">
                    <div class="au-user-cell">
                        <span class="au-avatar" style="--avatar-hue:<?= $hue ?>"><?= $initials ?></span>
                        <div>
                            <span class="au-user-name"><?= e($user['name']) ?><?= $isSelf ? ' <small style="color:#10b981; font-weight:600;">(You)</small>' : '' ?></span>
                            <span class="au-card-email"><?= e($user['email']) ?></span>
                        </div>
                    </div>
                    <div style="display:flex; flex-direction:column; align-items:flex-end; gap:0.25rem;">
                        <?php if ($isSuspended): ?>
                            <span class="au-status-badge au-status--suspended">Suspended</span>
                        <?php else: ?>
                            <span class="au-status-badge au-status--active">Active</span>
                        <?php endif; ?>
                        <?php if ($isUnverifiedHost): ?>
                            <span class="au-status-badge" style="background:rgba(245,158,11,0.12); color:#f59e0b; font-size:0.65rem; padding:0.1rem 0.4rem; height:auto;">⚠️ Unverified Host</span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="au-card-meta">
                    <span class="au-role-badge <?= $roleClass ?>"><?= $roleLabel ?></span>
                    <span class="au-card-date">Joined <?= date('M d, Y', strtotime($user['created_at'])) ?></span>
                </div>
                <div class="admin-actions au-card-actions">
                    <button type="button" class="admin-action-btn au-view-btn" data-user-id="<?= $user['id'] ?>" data-detail-url="<?= route('admin/users/detail', ['id' => $user['id']]) ?>">View</button>
                    <?php if ($isUnverifiedHost && $canModify): ?>
                        <button type="button" class="admin-action-btn au-verify-btn" data-user-id="<?= $user['id'] ?>" data-user-name="<?= e($user['name']) ?>" style="color:#f59e0b; border-color:rgba(245,158,11,0.3);">✓ Verify</button>
                    <?php endif; ?>
                    <?php if ($isSelf): ?>
                        <button type="button" class="admin-action-btn" disabled style="opacity:0.45; cursor:not-allowed;">Self</button>
                    <?php elseif (!$canModify): ?>
                        <button type="button" class="admin-action-btn" disabled style="opacity:0.45; cursor:not-allowed;">🔒 Protected</button>
                    <?php elseif ($isSuspended): ?>
                        <button type="button" class="admin-action-btn primary au-reactivate-btn" data-user-id="<?= $user['id'] ?>" data-user-name="<?= e($user['name']) ?>">Reactivate</button>
                    <?php else: ?>
                        <button type="button" class="admin-action-btn danger au-suspend-btn" data-user-id="<?= $user['id'] ?>" data-user-name="<?= e($user['name']) ?>">Suspend</button>
                    <?php endif; ?>
                </div>
            </article>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Empty State -->
    <div id="users-empty" class="empty-state <?= !empty($users) ? 'hidden' : '' ?>">
        <p>No users found matching your criteria.</p>
    </div>

    <!-- Pagination -->
    <?php if (($totalPages ?? 1) > 1): ?>
    <nav class="au-pagination" aria-label="Users pagination" id="users-pagination">
        <?php if ($page > 1): ?>
            <a href="?page=<?= $page - 1 ?>&q=<?= urlencode($search ?? '') ?>&role=<?= urlencode($role ?? '') ?>&status=<?= urlencode($status ?? '') ?>&verification=<?= urlencode($verification ?? '') ?>" class="au-page-btn">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5"
                    stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="15 18 9 12 15 6" />
                </svg>
                Prev
            </a>
        <?php else: ?>
            <span class="au-page-btn disabled" aria-disabled="true">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5"
                    stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="15 18 9 12 15 6" />
                </svg>
                Prev
            </span>
        <?php endif; ?>

        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <?php if ($i === $page): ?>
                <span class="au-page-num active" aria-current="page"><?= $i ?></span>
            <?php else: ?>
                <a href="?page=<?= $i ?>&q=<?= urlencode($search ?? '') ?>&role=<?= urlencode($role ?? '') ?>&status=<?= urlencode($status ?? '') ?>&verification=<?= urlencode($verification ?? '') ?>" class="au-page-num"><?= $i ?></a>
            <?php endif; ?>
        <?php endfor; ?>

        <?php if ($page < $totalPages): ?>
            <a href="?page=<?= $page + 1 ?>&q=<?= urlencode($search ?? '') ?>&role=<?= urlencode($role ?? '') ?>&status=<?= urlencode($status ?? '') ?>&verification=<?= urlencode($verification ?? '') ?>" class="au-page-btn">
                Next
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5"
                    stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="9 18 15 12 9 6" />
                </svg>
            </a>
        <?php else: ?>
            <span class="au-page-btn disabled" aria-disabled="true">
                Next
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5"
                    stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="9 18 15 12 9 6" />
                </svg>
            </span>
        <?php endif; ?>
    </nav>
    <?php endif; ?>
</section>

<!-- Confirm Suspend Modal -->
<div class="modal-overlay hidden" id="suspend-modal" role="dialog" aria-modal="true"
    aria-labelledby="suspend-modal-title">
    <div class="modal-card">
        <h3 id="suspend-modal-title">Suspend User</h3>
        <p>Are you sure you want to suspend <strong id="suspend-user-name">this user</strong>? They will not be able to
            log in until reactivated.</p>
        <form method="POST" action="{{ route('admin/users') }}">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="suspend" />
            <input type="hidden" name="user_id" id="suspend-user-id" value="" />
            <div class="modal-actions">
                <button type="button" class="admin-action-btn" id="suspend-cancel-btn">Cancel</button>
                <button type="submit" class="admin-action-btn danger">Yes, Suspend</button>
            </div>
        </form>
    </div>
</div>

<!-- Confirm Reactivate Modal -->
<div class="modal-overlay hidden" id="reactivate-modal" role="dialog" aria-modal="true"
    aria-labelledby="reactivate-modal-title">
    <div class="modal-card">
        <h3 id="reactivate-modal-title">Reactivate User</h3>
        <p>Are you sure you want to reactivate <strong id="reactivate-user-name">this user</strong>?</p>
        <form method="POST" action="{{ route('admin/users') }}">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="reactivate" />
            <input type="hidden" name="user_id" id="reactivate-user-id" value="" />
            <div class="modal-actions">
                <button type="button" class="admin-action-btn" id="reactivate-cancel-btn">Cancel</button>
                <button type="submit" class="admin-action-btn primary">Yes, Reactivate</button>
            </div>
        </form>
    </div>
</div>


<!-- Confirm Verify Email Modal -->
<div class="modal-overlay hidden" id="verify-modal" role="dialog" aria-modal="true"
    aria-labelledby="verify-modal-title">
    <div class="modal-card">
        <h3 id="verify-modal-title" style="color:#f59e0b;">✓ Manually Verify Email</h3>
        <p>Manually verify the email for <strong id="verify-user-name">this user</strong>?
            This will allow them to create and manage events immediately.</p>
        <form method="POST" action="{{ route('admin/users') }}">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="verify_email" />
            <input type="hidden" name="user_id" id="verify-user-id" value="" />
            <div class="modal-actions">
                <button type="button" class="admin-action-btn" id="verify-cancel-btn">Cancel</button>
                <button type="submit" class="admin-action-btn primary" style="background:#f59e0b; border-color:#f59e0b;">Yes, Verify Email</button>
            </div>
        </form>
    </div>
</div>

<!-- Confirm Resend Verification Modal -->
<div class="modal-overlay hidden" id="resend-modal" role="dialog" aria-modal="true"
    aria-labelledby="resend-modal-title">
    <div class="modal-card">
        <h3 id="resend-modal-title">✉️ Resend Verification Email</h3>
        <p>Resend a verification email to <strong id="resend-user-name">this user</strong>?
            A new verification link will be generated and sent to their email address.</p>
        <form method="POST" action="{{ route('admin/users') }}">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="resend_verification" />
            <input type="hidden" name="user_id" id="resend-user-id" value="" />
            <div class="modal-actions">
                <button type="button" class="admin-action-btn" id="resend-cancel-btn">Cancel</button>
                <button type="submit" class="admin-action-btn primary">Yes, Resend</button>
            </div>
        </form>
    </div>
</div>


<div class="modal-overlay hidden" id="view-modal" role="dialog" aria-modal="true" aria-labelledby="view-modal-title">
    <div class="modal-card" style="max-width: 750px; width: 100%; max-height: 85vh; display: flex; flex-direction: column; overflow: hidden; padding: 2rem;">
        <!-- Header (Fixed) -->
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem; border-bottom:1px solid rgba(255,255,255,0.08); padding-bottom:1rem; flex-shrink:0;">
            <h3 id="view-modal-title" style="margin:0; font-size:1.4rem;">User Profile Detail</h3>
            <button type="button" id="view-close-x-btn" style="background:none; border:none; font-size:1.75rem; line-height:1; cursor:pointer; color:rgb(var(--muted)); padding:0 0.5rem; transition:color var(--duration-fast) var(--ease-out);" onmouseover="this.style.color='rgb(var(--text))'" onmouseout="this.style.color='rgb(var(--muted))'">&times;</button>
        </div>
        
        <!-- Scrollable content area -->
        <div style="overflow-y:auto; flex-grow:1; padding-right:0.75rem; margin-right:-0.75rem;">
            <div id="view-modal-loading" style="text-align:center; padding:3rem 0;">
                <svg class="animate-spin" viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" style="margin:0 auto 1rem;">
                    <circle cx="12" cy="12" r="10" stroke-opacity="0.25" />
                    <path d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                </svg>
                <p style="margin:0; color:var(--text-muted);">Fetching details...</p>
            </div>

            <div id="view-modal-content" class="hidden">
                <!-- Basic Details -->
                <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:1.25rem; margin-bottom:1.5rem;">
                    <div>
                        <label style="font-size:0.8rem; color:var(--text-muted); text-transform:uppercase; font-weight:600;">Full Name</label>
                        <p id="vd-name" style="margin:0.25rem 0 0; font-weight:600; font-size:1.05rem;"></p>
                    </div>
                    <div>
                        <label style="font-size:0.8rem; color:var(--text-muted); text-transform:uppercase; font-weight:600;">Email Address</label>
                        <p id="vd-email" style="margin:0.25rem 0 0; font-weight:500;"></p>
                    </div>
                    <div>
                        <label style="font-size:0.8rem; color:var(--text-muted); text-transform:uppercase; font-weight:600;">Phone Number</label>
                        <p id="vd-phone" style="margin:0.25rem 0 0;"></p>
                    </div>
                    <div>
                        <label style="font-size:0.8rem; color:var(--text-muted); text-transform:uppercase; font-weight:600;">Joined Date</label>
                        <p id="vd-joined" style="margin:0.25rem 0 0;"></p>
                    </div>
                    <div>
                        <label style="font-size:0.8rem; color:var(--text-muted); text-transform:uppercase; font-weight:600;">Status</label>
                        <p style="margin:0.25rem 0 0;"><span id="vd-status" class="au-status-badge"></span></p>
                    </div>
                    <div>
                        <label style="font-size:0.8rem; color:var(--text-muted); text-transform:uppercase; font-weight:600;">System Role</label>
                        <p style="margin:0.25rem 0 0;"><span id="vd-role" class="au-role-badge"></span></p>
                    </div>
                </div>

                <!-- Email Verification Status -->
                <div id="vd-verification-section" style="background:rgba(255,255,255,0.02); border:1px solid rgba(255,255,255,0.06); border-radius:10px; padding:1rem 1.25rem; margin-bottom:1.5rem;">
                    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:0.75rem;">
                        <div>
                            <label style="font-size:0.8rem; color:var(--text-muted); text-transform:uppercase; font-weight:600;">Email Verification</label>
                            <p style="margin:0.25rem 0 0;">
                                <span id="vd-verification-badge"></span>
                                <span id="vd-verification-date" style="font-size:0.8rem; color:var(--text-muted); margin-left:0.5rem;"></span>
                            </p>
                            <p id="vd-verification-warning" class="hidden" style="margin:0.35rem 0 0; font-size:0.8rem; color:#f59e0b;">
                                ⚠️ This host cannot create events until their email is verified.
                            </p>
                        </div>
                        <div id="vd-verification-actions" class="hidden" style="display:flex; gap:0.5rem; flex-wrap:wrap;">
                            <button type="button" class="admin-action-btn au-modal-verify-btn" style="color:#f59e0b; border-color:rgba(245,158,11,0.3); font-size:0.8rem; padding:0.3rem 0.75rem;">
                                ✓ Verify Email
                            </button>
                            <button type="button" class="admin-action-btn au-modal-resend-btn" style="font-size:0.8rem; padding:0.3rem 0.75rem;">
                                ✉️ Resend Link
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Role-Specific Info Container -->
                <div id="vd-role-info" style="border-top:1px solid rgba(255,255,255,0.08); padding-top:1.5rem; margin-top:1rem;">
                    
                    <!-- Attendee Section -->
                    <div id="vd-section-attendee" class="hidden">
                        <h4 style="margin:0 0 1rem; color:var(--primary); font-size:1.1rem; font-weight:600;">Attendee Activity</h4>
                        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(180px, 1fr)); gap:1rem;">
                            <div class="au-stat-card" style="margin:0; padding:1rem; background:rgba(255,255,255,0.02); border:1px solid rgba(255,255,255,0.05);">
                                <div class="au-stat-body">
                                    <span id="vd-tickets-bought" class="au-stat-num" style="font-size:1.6rem; line-height:1.2;">0</span>
                                    <span class="au-stat-lbl">Tickets Purchased</span>
                                </div>
                            </div>
                            <div class="au-stat-card" style="margin:0; padding:1rem; background:rgba(255,255,255,0.02); border:1px solid rgba(255,255,255,0.05);">
                                <div class="au-stat-body">
                                    <span id="vd-total-spent" class="au-stat-num" style="font-size:1.6rem; line-height:1.2;">$0.00</span>
                                    <span class="au-stat-lbl">Total Spent</span>
                                </div>
                            </div>
                            <div style="grid-column: span 2;">
                                <label style="font-size:0.8rem; color:var(--text-muted); text-transform:uppercase; font-weight:600;">Last Ticket Purchase</label>
                                <p id="vd-last-purchase" style="margin:0.25rem 0 0; font-weight:500;"></p>
                            </div>
                        </div>
                    </div>

                    <!-- Host Section -->
                    <div id="vd-section-host" class="hidden">
                        <h4 style="margin:0 0 1rem; color:var(--primary); font-size:1.1rem; font-weight:600;">Organizer & Payout Metrics</h4>
                        
                        <div style="margin-bottom:1.5rem;">
                            <label style="font-size:0.8rem; color:var(--text-muted); text-transform:uppercase; font-weight:600;">Bio / Description</label>
                            <p id="vd-bio" style="margin:0.25rem 0 0; font-style:italic; line-height:1.5; color:var(--text-muted);"></p>
                        </div>

                        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(130px, 1fr)); gap:0.75rem; margin-bottom:1.5rem;">
                            <div class="au-stat-card" style="margin:0; padding:0.75rem; background:rgba(255,255,255,0.02); border:1px solid rgba(255,255,255,0.05);">
                                <div class="au-stat-body">
                                    <span id="vd-total-events" class="au-stat-num" style="font-size:1.3rem; line-height:1.2;">0</span>
                                    <span class="au-stat-lbl">Events Hosted</span>
                                </div>
                            </div>
                            <div class="au-stat-card" style="margin:0; padding:0.75rem; background:rgba(255,255,255,0.02); border:1px solid rgba(255,255,255,0.05);">
                                <div class="au-stat-body">
                                    <span id="vd-tickets-sold" class="au-stat-num" style="font-size:1.3rem; line-height:1.2;">0</span>
                                    <span class="au-stat-lbl">Tickets Sold</span>
                                </div>
                            </div>
                            <div class="au-stat-card" style="margin:0; padding:0.75rem; background:rgba(255,255,255,0.02); border:1px solid rgba(255,255,255,0.05);">
                                <div class="au-stat-body">
                                    <span id="vd-total-revenue" class="au-stat-num" style="font-size:1.3rem; line-height:1.2;">$0.00</span>
                                    <span class="au-stat-lbl">Total Revenue</span>
                                </div>
                            </div>
                            <div class="au-stat-card" style="margin:0; padding:0.75rem; background:rgba(255,255,255,0.02); border:1px solid rgba(255,255,255,0.05);">
                                <div class="au-stat-body">
                                    <span id="vd-available-balance" class="au-stat-num" style="font-size:1.3rem; line-height:1.2; color:#10b981;">$0.00</span>
                                    <span class="au-stat-lbl">Avail. Balance</span>
                                </div>
                            </div>
                        </div>

                        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(180px, 1fr)); gap:1rem; border-top:1px dashed rgba(255,255,255,0.05); padding-top:1rem;">
                            <div>
                                <label style="font-size:0.8rem; color:var(--text-muted); text-transform:uppercase; font-weight:600;">Payout Bank</label>
                                <p id="vd-bank" style="margin:0.25rem 0 0; font-weight:500;"></p>
                            </div>
                            <div>
                                <label style="font-size:0.8rem; color:var(--text-muted); text-transform:uppercase; font-weight:600;">Account (Truncated)</label>
                                <p id="vd-account" style="margin:0.25rem 0 0; font-family:monospace;"></p>
                            </div>
                            <div>
                                <label style="font-size:0.8rem; color:var(--text-muted); text-transform:uppercase; font-weight:600;">Routing Number</label>
                                <p id="vd-routing" style="margin:0.25rem 0 0; font-family:monospace;"></p>
                            </div>
                            <div>
                                <label style="font-size:0.8rem; color:var(--text-muted); text-transform:uppercase; font-weight:600;">Host Rating</label>
                                <p id="vd-rating" style="margin:0.25rem 0 0; font-weight:600;"></p>
                            </div>
                        </div>

                        <!-- Host Recent Events -->
                        <div style="margin-top:1.5rem; border-top:1px dashed rgba(255,255,255,0.05); padding-top:1rem;">
                            <h5 style="margin:0 0 0.75rem; font-size:0.95rem; font-weight:600; color:var(--text-secondary);">Recent Events Hosted</h5>
                            <div id="vd-host-events-list" style="display:flex; flex-direction:column; gap:0.5rem;">
                                <!-- Dynamic items -->
                            </div>
                        </div>

                        <!-- Host Recent Payout Requests -->
                        <div style="margin-top:1.5rem; border-top:1px dashed rgba(255,255,255,0.05); padding-top:1rem;">
                            <h5 style="margin:0 0 0.75rem; font-size:0.95rem; font-weight:600; color:var(--text-secondary);">Recent Payout History</h5>
                            <div id="vd-host-payouts-list" style="display:flex; flex-direction:column; gap:0.5rem;">
                                <!-- Dynamic items -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer (Fixed) -->
        <div class="modal-actions" style="margin-top:1.5rem; border-top:1px solid rgba(255,255,255,0.08); padding-top:1rem; display:flex; justify-content:flex-end; flex-shrink:0;">
            <button type="button" class="admin-action-btn primary" id="view-close-btn">Close</button>
        </div>
    </div>
</div>
@endsection