<?php

/**
 * Admin Teams
 *
 * This file contains the HTML markup for the admin teams page of the application.
 *
 * @package Evently
 * @subpackage Views/Admin
 * @author Your Name <Celio Natti>
 * @license MIT License
 * @version 1.2.0
 */

$siteName = site_setting('site_title', 'Eventlyy');

// Dynamic role badge styling helper
$getRoleBadge = function($roleName, $roleSlug = '') {
    $slug = strtolower($roleSlug ?: $roleName);
    if (str_contains($slug, 'scanner')) {
        return ['bg' => 'rgba(2, 132, 199, 0.12)', 'color' => '#0284c7', 'border' => 'rgba(2, 132, 199, 0.28)', 'icon' => '🎫'];
    } elseif (str_contains($slug, 'organizer') && str_contains($slug, 'staff')) {
        return ['bg' => 'rgba(139, 92, 246, 0.12)', 'color' => '#8b5cf6', 'border' => 'rgba(139, 92, 246, 0.28)', 'icon' => '🏷️'];
    } elseif (str_contains($slug, 'organizer')) {
        return ['bg' => 'rgba(16, 185, 129, 0.12)', 'color' => '#059669', 'border' => 'rgba(16, 185, 129, 0.28)', 'icon' => '👑'];
    } elseif (str_contains($slug, 'support')) {
        return ['bg' => 'rgba(245, 158, 11, 0.12)', 'color' => '#d97706', 'border' => 'rgba(245, 158, 11, 0.28)', 'icon' => '🎧'];
    } elseif (str_contains($slug, 'tech') || str_contains($slug, 'ops')) {
        return ['bg' => 'rgba(99, 102, 241, 0.12)', 'color' => '#4f46e5', 'border' => 'rgba(99, 102, 241, 0.28)', 'icon' => '⚙️'];
    } elseif (str_contains($slug, 'finance')) {
        return ['bg' => 'rgba(20, 184, 166, 0.12)', 'color' => '#0d9488', 'border' => 'rgba(20, 184, 166, 0.28)', 'icon' => '💼'];
    } elseif (str_contains($slug, 'usher') || str_contains($slug, 'guide') || str_contains($slug, 'floor')) {
        return ['bg' => 'rgba(168, 85, 247, 0.12)', 'color' => '#9333ea', 'border' => 'rgba(168, 85, 247, 0.28)', 'icon' => '👥'];
    }
    return ['bg' => 'rgba(var(--muted), 0.1)', 'color' => 'rgb(var(--text))', 'border' => 'rgba(var(--muted), 0.2)', 'icon' => '👤'];
};
?>

@extends('layouts/admin')

@section('title')
Admin Teams - Manage Your <?= e($siteName) ?> Teams
@endsection

@section('content')
<div class="admin-teams-page-container">
    <!-- Hero Header -->
    <section class="page-hero">
        <div>
            <span class="panel-badge">Admin — Teams &amp; Staff</span>
            <h1>Team Coordination &amp; Assignments</h1>
            <p class="lede">Register organization staff and assign them as coordinators, scanners, technical leads, or helpers to upcoming events with defined RBAC roles.</p>
        </div>
    </section>

    <!-- Summary Stats -->
    <section class="admin-users-stats">
        <div class="au-stat-card">
            <span class="au-stat-icon">
                <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                    <circle cx="9" cy="7" r="4" />
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87" />
                    <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                </svg>
            </span>
            <div class="au-stat-body">
                <span class="au-stat-num" id="total-staff"><?= number_format($total_staff) ?></span>
                <span class="au-stat-lbl">Total Registered Staff</span>
            </div>
        </div>
        <div class="au-stat-card">
            <span class="au-stat-icon au-stat-icon--brand">
                <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
                    <polyline points="22 4 12 14.01 9 11.01" />
                </svg>
            </span>
            <div class="au-stat-body">
                <div style="display:flex; align-items:baseline; gap:0.4rem;">
                    <span class="au-stat-num" id="assigned-staff"><?= number_format($assigned_staff) ?></span>
                    <span style="font-size:0.8rem; font-weight:700; color:#10b981;">(<?= (int)($assigned_percent ?? 0) ?>%)</span>
                </div>
                <span class="au-stat-lbl">Staff Deployed</span>
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
                <div style="display:flex; align-items:baseline; gap:0.4rem;">
                    <span class="au-stat-num" id="unassigned-staff"><?= number_format($unassigned_staff) ?></span>
                    <span style="font-size:0.8rem; font-weight:700; color:#d97706;">(<?= (int)($unassigned_percent ?? 0) ?>%)</span>
                </div>
                <span class="au-stat-lbl">Available / Unassigned</span>
            </div>
        </div>
        <div class="au-stat-card">
            <span class="au-stat-icon au-stat-icon--success">
                <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
                    <line x1="16" y1="2" x2="16" y2="6" />
                    <line x1="8" y1="2" x2="8" y2="6" />
                    <line x1="3" y1="10" x2="21" y2="10" />
                </svg>
            </span>
            <div class="au-stat-body">
                <span class="au-stat-num" id="active-events-staff"><?= number_format($active_events_staff) ?></span>
                <span class="au-stat-lbl">Upcoming Events Covered</span>
            </div>
        </div>
    </section>

    <!-- Visual Aid: Staff Deployment & Role Distribution Bar -->
    <section class="form-card team-distribution-card" style="margin-bottom: 2rem; padding: 1.35rem 1.6rem; border-radius: 20px;">
        <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:0.75rem; margin-bottom:1rem;">
            <div>
                <h3 style="margin:0; font-size:1.05rem; font-weight:800; display:flex; align-items:center; gap:0.5rem;">
                    <span>📊</span> Staff Deployment &amp; Role Allocation
                </h3>
                <p style="margin:0.25rem 0 0; font-size:0.82rem; color:rgb(var(--muted));">
                    Overview of active staff utilization across upcoming events and specialized RBAC roles.
                </p>
            </div>
            <div style="display:flex; gap:0.5rem; align-items:center; font-size:0.8rem; font-weight:700;">
                <span style="display:inline-flex; align-items:center; gap:0.35rem; color:#10b981;">
                    <span style="width:10px; height:10px; border-radius:50%; background:#10b981;"></span>
                    Assigned: <?= (int)$assigned_staff ?>
                </span>
                <span style="display:inline-flex; align-items:center; gap:0.35rem; color:#f59e0b; margin-left:0.5rem;">
                    <span style="width:10px; height:10px; border-radius:50%; background:#f59e0b;"></span>
                    Unassigned: <?= (int)$unassigned_staff ?>
                </span>
                <?php if (($inactive_staff ?? 0) > 0): ?>
                <span style="display:inline-flex; align-items:center; gap:0.35rem; color:#ef4444; margin-left:0.5rem;">
                    <span style="width:10px; height:10px; border-radius:50%; background:#ef4444;"></span>
                    Inactive: <?= (int)$inactive_staff ?>
                </span>
                <?php endif; ?>
            </div>
        </div>

        <!-- Segmented Progress Bar -->
        <div style="width:100%; height:12px; border-radius:99px; background:rgba(var(--muted),0.12); display:flex; overflow:hidden; margin-bottom:1rem;">
            <div style="width:<?= max(0, min(100, $assigned_percent ?? 0)) ?>%; background:linear-gradient(90deg, #10b981, #059669); transition:width 0.4s ease;" title="Assigned Staff (<?= (int)$assigned_percent ?>%)"></div>
            <div style="width:<?= max(0, min(100, $unassigned_percent ?? 0)) ?>%; background:linear-gradient(90deg, #f59e0b, #d97706); transition:width 0.4s ease;" title="Unassigned Staff (<?= (int)$unassigned_percent ?>%)"></div>
            <div style="width:<?= max(0, min(100, $inactive_percent ?? 0)) ?>%; background:linear-gradient(90deg, #ef4444, #dc2626); transition:width 0.4s ease;" title="Inactive Staff (<?= (int)$inactive_percent ?>%)"></div>
        </div>

        <!-- Role Badges Breakdown -->
        <div style="display:flex; flex-wrap:wrap; gap:0.5rem; align-items:center; padding-top:0.35rem; border-top:1px dashed rgba(var(--muted),0.15);">
            <span style="font-size:0.75rem; font-weight:700; color:rgb(var(--muted)); text-transform:uppercase; letter-spacing:0.5px; margin-right:0.25rem;">Active Roles:</span>
            <?php foreach (($roles_breakdown ?? []) as $rb): 
                $badge = $getRoleBadge($rb['name'], $rb['slug']);
            ?>
                <a href="{{ route('admin/teams', ['role' => $rb['id']]) }}" class="team-role-pill" style="background:<?= $badge['bg'] ?>; color:<?= $badge['color'] ?>; border:1px solid <?= $badge['border'] ?>; font-size:0.78rem; font-weight:700; padding:3px 10px; border-radius:8px; text-decoration:none; display:inline-flex; align-items:center; gap:0.35rem; transition:transform 0.15s ease;">
                    <span><?= $badge['icon'] ?></span>
                    <span><?= e($rb['name']) ?></span>
                    <span style="background:rgba(0,0,0,0.12); padding:1px 6px; border-radius:99px; font-size:0.7rem;"><?= (int)$rb['assignment_count'] ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </section>

    <?php if (!empty($pending_staff_requests)): ?>
    <!-- Pending Event Staffing Assistance Requests from Hosts -->
    <section class="form-card" style="margin-bottom: 2rem; border-left: 4px solid #6366f1; border-radius: 20px; padding: 1.4rem 1.6rem; background: var(--bg-card, #fff);">
        <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:0.75rem; margin-bottom:1.25rem;">
            <div>
                <h3 style="margin:0; font-size:1.12rem; font-weight:800; display:flex; align-items:center; gap:0.5rem; color:#4f46e5;">
                    <span>🛡️</span> Host Staffing Assistance Requests
                    <span style="background:rgba(99,102,241,0.12); color:#4f46e5; font-size:0.75rem; font-weight:800; padding:0.2rem 0.6rem; border-radius:999px;"><?= count($pending_staff_requests) ?> Pending</span>
                </h3>
                <p style="margin:0.25rem 0 0; font-size:0.83rem; color:rgb(var(--muted));">
                    Hosts requesting organization staff support &amp; specialized event roles.
                </p>
            </div>
        </div>

        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(320px, 1fr)); gap:1rem;">
            <?php foreach ($pending_staff_requests as $req): ?>
                <div style="border:1px solid rgba(99,102,241,0.18); background:rgba(99,102,241,0.02); border-radius:14px; padding:1.15rem; display:flex; flex-direction:column; justify-content:space-between; gap:0.85rem;">
                    <div>
                        <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:0.5rem; margin-bottom:0.4rem;">
                            <h4 style="margin:0; font-size:0.95rem; font-weight:700; color:var(--text-primary);">
                                <?= e($req['event_name']) ?>
                            </h4>
                            <span style="font-size:0.72rem; font-weight:700; background:rgba(245,158,11,0.12); color:#d97706; padding:0.15rem 0.5rem; border-radius:6px; white-space:nowrap;">
                                ⏳ <?= ucfirst($req['status']) ?>
                            </span>
                        </div>
                        <div style="font-size:0.8rem; color:rgb(var(--muted)); margin-bottom:0.75rem; line-height:1.45;">
                            <span>🎪 Host: <strong><?= e($req['host_name']) ?></strong> (<?= e($req['host_email']) ?>)</span>
                            <?php if (!empty($req['event_date'])): ?>
                                <br><span>📅 <?= date('M d, Y h:i A', strtotime($req['event_date'])) ?></span>
                            <?php endif; ?>
                            <?php if (!empty($req['event_location'])): ?>
                                <br><span>📍 <?= e($req['event_location']) ?></span>
                            <?php endif; ?>
                        </div>

                        <!-- Requested Roles Badges -->
                        <div style="display:flex; flex-wrap:wrap; gap:0.4rem; margin-bottom:0.6rem;">
                            <?php foreach ($req['roles_array'] as $r): ?>
                                <span style="display:inline-flex; align-items:center; gap:0.3rem; background:var(--bg-card, #fff); border:1px solid rgba(var(--muted),0.15); border-radius:6px; padding:0.25rem 0.55rem; font-size:0.75rem; font-weight:600;">
                                    <span><?= $r['icon'] ?? '👤' ?></span>
                                    <span><?= e($r['name'] ?? $r['slug']) ?></span>
                                    <span style="background:rgba(99,102,241,0.12); color:#4f46e5; font-weight:800; padding:0.05rem 0.35rem; border-radius:4px;">×<?= (int)($r['quantity'] ?? 1) ?></span>
                                </span>
                            <?php endforeach; ?>
                        </div>

                        <?php if (!empty($req['notes'])): ?>
                            <p style="margin:0; font-size:0.78rem; color:rgb(var(--muted)); font-style:italic; background:rgba(0,0,0,0.02); padding:0.4rem 0.6rem; border-radius:6px; line-height:1.4;">
                                "<?= e($req['notes']) ?>"
                            </p>
                        <?php endif; ?>
                    </div>

                    <div style="display:flex; gap:0.5rem; align-items:center; border-top:1px dashed rgba(var(--muted),0.15); padding-top:0.65rem; margin-top:0.25rem;">
                        <button type="button" class="admin-action-btn primary" style="font-size:0.8rem; padding:0.45rem 0.9rem; border-radius:8px; display:inline-flex; align-items:center; gap:0.35rem;" onclick="prefillAssignForm('<?= $req['event_id'] ?>', '<?= !empty($req['roles_array'][0]['role_id']) ? $req['roles_array'][0]['role_id'] : '' ?>')">
                            <span>⚡</span> Assign Staff to This Event
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

    <!-- Forms Section: Side-by-Side on Desktop & Tablet, Stacked on Mobile -->
    <section class="admin-teams-forms-row">
        <!-- Add Staff Form -->
        <div class="form-card team-form-card">
            <div style="display:flex; align-items:center; gap:0.5rem; margin-bottom:0.25rem;">
                <span style="font-size:1.3rem;">👤</span>
                <h2 style="margin:0; font-size:1.2rem; font-weight:800;">Register Team Member</h2>
            </div>
            <p class="section-note">Add new organization staff to the registry.</p>
            <div id="add-message" class="auth-message" style="margin-bottom:1rem; display:none;" aria-live="polite"></div>

            <form id="add-team-form" method="POST" action="{{ route('admin/teams') }}">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="add_member" />
                <label class="team-form-label">Full Name
                    <input type="text" name="name" id="member-name" required placeholder="e.g. Alex Carter" class="team-form-input">
                </label>
                <label class="team-form-label">Email Address
                    <input type="email" name="email" id="member-email" required placeholder="alex@eventlyy.com" class="team-form-input">
                </label>
                <button type="submit" class="admin-action-btn primary team-submit-btn">
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="12" y1="5" x2="12" y2="19"></line>
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                    </svg>
                    Register Member
                </button>
            </form>
        </div>

        <!-- Assign Event Form -->
        <div class="form-card team-form-card">
            <div style="display:flex; align-items:center; gap:0.5rem; margin-bottom:0.25rem;">
                <span style="font-size:1.3rem;">⚡</span>
                <h2 style="margin:0; font-size:1.2rem; font-weight:800;">Assign to Upcoming Event</h2>
            </div>
            <p class="section-note">Deploy an active staff member to an upcoming event.</p>
            <div id="assign-message" class="auth-message" style="margin-bottom:1rem; display:none;" aria-live="polite"></div>

            <form id="assign-team-form" method="POST" action="{{ route('admin/teams') }}">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="assign_member" />
                <label class="team-form-label">Select Team Member
                    <select name="member_id" id="assign-member-select" required class="team-form-select">
                        <option value="">-- Choose staff member --</option>
                        <?php foreach ($teams as $m): ?>
                            <option value="<?= $m['id'] ?>" <?= $m['status'] === 'inactive' ? 'disabled' : '' ?>>
                                <?= e($m['name']) ?> (<?= $m['assignment_count'] ?> active events) <?= $m['status'] === 'inactive' ? '[Inactive]' : '' ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <div class="team-assign-split-row">
                    <label class="team-form-label">Select Upcoming Event
                        <select name="event_id" id="assign-event-select" required class="team-form-select">
                            <?php if (empty($events)): ?>
                                <option value="">-- No upcoming events available --</option>
                            <?php else: ?>
                                <option value="">-- Choose upcoming event --</option>
                                <?php foreach ($events as $ev): ?>
                                    <option value="<?= $ev['id'] ?>">
                                        <?= e($ev['name']) ?><?= !empty($ev['date']) ? ' (' . date('M d, Y', strtotime($ev['date'])) . ')' : '' ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </label>
                    <label class="team-form-label">Assign RBAC Role
                        <select name="role_id" id="assign-role-select" required class="team-form-select">
                            <option value="">-- Choose role --</option>
                            <?php foreach ($roles as $r): ?>
                                <option value="<?= $r['id'] ?>"><?= e($r['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                </div>
                <button type="submit" class="admin-action-btn primary team-submit-btn" <?= empty($events) ? 'disabled style="opacity:0.6;cursor:not-allowed;"' : '' ?>>
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                        <circle cx="9" cy="7" r="4" />
                        <polyline points="16 11 18 13 22 9" />
                    </svg>
                    Assign Staff Member
                </button>
            </form>
        </div>
    </section>

    <!-- Full-Width Staff Registry Section -->
    <section class="admin-teams-registry-section">
        <div class="form-card team-registry-card">
            <div class="au-table-header" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:1rem;">
                <div>
                    <h2 class="registry-title">Staff Registry &amp; Deployments</h2>
                    <p class="section-note registry-subtitle">
                        All organization staff profiles and active event assignments. Showing <strong><?= count($teams) ?></strong> of <strong><?= $total_staff ?></strong> total staff.
                    </p>
                </div>
                <a href="<?= route('admin/teams/export', ['search' => $search ?? '', 'role' => $role ?? '', 'status' => $status ?? '', 'event' => $event_filter ?? '']) ?>" class="admin-action-btn" title="Export matching staff to CSV">
                    <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4" />
                        <polyline points="7 10 12 15 17 10" />
                        <line x1="12" y1="15" x2="12" y2="3" />
                    </svg>
                    Export CSV
                </a>
            </div>

            <!-- Toolbar / Filters -->
            <form method="GET" action="{{ route('admin/teams') }}" class="au-toolbar team-toolbar-form">
                <div class="au-search-box team-search-box">
                    <input type="text" name="search" value="<?= e($search ?? '') ?>" placeholder="Search staff by name or email..." class="team-filter-input">
                </div>
                <div class="team-filters-group">
                    <select name="event" class="team-filter-select" aria-label="Filter by Event">
                        <option value="">All Events &amp; Unassigned</option>
                        <option value="unassigned" <?= ($event_filter ?? '') === 'unassigned' ? 'selected' : '' ?>>⚠️ Unassigned Staff Only</option>
                        <?php if (!empty($events)): ?>
                            <optgroup label="Upcoming Events">
                                <?php foreach ($events as $ev): ?>
                                    <option value="<?= $ev['id'] ?>" <?= ($event_filter ?? '') == $ev['id'] ? 'selected' : '' ?>>
                                        <?= e($ev['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </optgroup>
                        <?php endif; ?>
                    </select>
                    <select name="role" class="team-filter-select" aria-label="Filter by Role">
                        <option value="">All Roles</option>
                        <?php foreach ($roles as $r): ?>
                            <option value="<?= $r['id'] ?>" <?= ($role ?? '') == $r['id'] ? 'selected' : '' ?>><?= e($r['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <select name="status" class="team-filter-select" aria-label="Filter by Status">
                        <option value="">All Statuses</option>
                        <option value="active" <?= ($status ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
                        <option value="inactive" <?= ($status ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                    </select>
                    <button type="submit" class="admin-action-btn primary team-filter-btn">Filter</button>
                    <?php if (!empty($search) || !empty($role) || !empty($status) || !empty($event_filter)): ?>
                        <a href="{{ route('admin/teams') }}" class="admin-action-btn team-reset-btn">Reset</a>
                    <?php endif; ?>
                </div>
            </form>

            <!-- Staff Table (Desktop & Tablet Wide) -->
            <div class="au-table-wrap team-desktop-table">
                <table class="au-table">
                    <thead>
                        <tr>
                            <th style="width: 30%;">Staff Member</th>
                            <th style="width: 14%;">Status</th>
                            <th style="width: 36%;">Event Deployments</th>
                            <th class="au-th-actions" style="width: 20%; text-align: right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="team-tbody">
                        <?php if (empty($teams)): ?>
                            <tr>
                                <td colspan="4" style="padding:3.5rem 1rem; text-align:center; color:rgb(var(--muted));">
                                    <div style="max-width:360px; margin:0 auto;">
                                        <div style="font-size:2.5rem; margin-bottom:0.75rem;">👥</div>
                                        <h3 style="font-size:1.1rem; font-weight:700; margin:0 0 0.25rem 0; color:rgb(var(--text));">No staff members found</h3>
                                        <p style="font-size:0.85rem; margin:0; line-height:1.5;">No staff members match the selected filter criteria. Try adjusting your search or register a new team member.</p>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($teams as $m):
                                $hue = abs(crc32($m['name'])) % 360;
                                $initials = strtoupper(substr($m['name'], 0, 2));
                                $isActive = ($m['status'] === 'active');
                                $assignCount = (int)($m['assignment_count'] ?? 0);
                            ?>
                            <tr>
                                <td>
                                    <div class="au-user-cell">
                                        <span class="au-avatar" style="--avatar-hue:<?= $hue ?>; flex-shrink:0;"><?= $initials ?></span>
                                        <div>
                                            <span class="au-user-name" style="font-weight:700;"><?= e($m['name']) ?></span>
                                            <span class="au-card-email"><?= e($m['email']) ?></span>
                                            <span style="font-size:0.72rem; color:rgb(var(--muted)); font-family:monospace;">ID: #<?= (int)$m['id'] ?></span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <?php if ($isActive): ?>
                                        <span class="au-status-badge au-status--active" style="background:rgba(16, 185, 129, 0.12); color:#059669; border:1px solid rgba(16, 185, 129, 0.28); font-weight:700;">✓ Active</span>
                                    <?php else: ?>
                                        <span class="au-status-badge au-status--suspended" style="font-weight:700;">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($assignCount > 0): ?>
                                        <div style="display:flex; align-items:center; gap:0.4rem; flex-wrap:wrap; margin-bottom:0.25rem;">
                                            <span class="au-role-badge au-role--host" style="font-weight:800; padding:2px 8px; font-size:0.75rem;">
                                                ⚡ <?= $assignCount ?> Event<?= $assignCount > 1 ? 's' : '' ?>
                                            </span>
                                        </div>
                                        <div class="assignment-summary-text" title="<?= e($m['assignments_summary'] ?? '') ?>">
                                            <?= e($m['assignments_summary'] ?? '') ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="au-status-badge au-status--unverified" style="background:rgba(245, 158, 11, 0.1); color:#d97706; border:1px solid rgba(245, 158, 11, 0.25); font-weight:700;">
                                            Available (Unassigned)
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align: right;">
                                    <div class="admin-actions" style="justify-content: flex-end;">
                                        <button type="button" class="admin-action-btn au-view-staff-btn" data-staff-id="<?= $m['id'] ?>" title="View staff profile and assignments">
                                            👤 View
                                        </button>
                                        <form method="POST" action="{{ route('admin/teams') }}" style="display:inline;">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="action" value="toggle_status" />
                                            <input type="hidden" name="member_id" value="<?= $m['id'] ?>" />
                                            <button type="submit" class="admin-action-btn <?= $isActive ? 'warning' : 'primary' ?>" title="<?= $isActive ? 'Deactivate staff member' : 'Activate staff member' ?>">
                                                <?= $isActive ? 'Deactivate' : 'Activate' ?>
                                            </button>
                                        </form>
                                        <button type="button" class="admin-action-btn danger au-remove-staff-btn"
                                            data-member-id="<?= $m['id'] ?>" data-member-name="<?= e($m['name']) ?>" title="Remove staff member from registry">
                                            Remove
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Staff Cards (Mobile & Small Screen View) -->
            <div class="au-cards-list" id="team-cards">
                <?php if (!empty($teams)): ?>
                    <?php foreach ($teams as $m):
                        $hue = abs(crc32($m['name'])) % 360;
                        $initials = strtoupper(substr($m['name'], 0, 2));
                        $isActive = ($m['status'] === 'active');
                        $assignCount = (int)($m['assignment_count'] ?? 0);
                    ?>
                    <article class="au-user-card team-mobile-card">
                        <div class="au-card-top">
                            <div class="au-user-cell">
                                <span class="au-avatar" style="--avatar-hue:<?= $hue ?>"><?= $initials ?></span>
                                <div>
                                    <span class="au-user-name" style="font-weight:700;"><?= e($m['name']) ?></span>
                                    <span class="au-card-email"><?= e($m['email']) ?></span>
                                    <span style="font-size:0.72rem; color:rgb(var(--muted)); font-family:monospace;">ID: #<?= (int)$m['id'] ?></span>
                                </div>
                            </div>
                            <div>
                                <?php if ($isActive): ?>
                                    <span class="au-status-badge au-status--active" style="background:rgba(16, 185, 129, 0.12); color:#059669; border:1px solid rgba(16, 185, 129, 0.28); font-weight:700;">✓ Active</span>
                                <?php else: ?>
                                    <span class="au-status-badge au-status--suspended" style="font-weight:700;">Inactive</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="au-card-meta team-mobile-meta">
                            <?php if ($assignCount > 0): ?>
                                <span class="au-role-badge au-role--host" style="font-weight:800;">⚡ <?= $assignCount ?> Event(s)</span>
                                <span class="au-card-date" style="line-height:1.4;"><?= e($m['assignments_summary'] ?? '') ?></span>
                            <?php else: ?>
                                <span class="au-status-badge au-status--unverified" style="background:rgba(245, 158, 11, 0.1); color:#d97706; border:1px solid rgba(245, 158, 11, 0.25); font-weight:700;">
                                    Available (Unassigned)
                                </span>
                            <?php endif; ?>
                        </div>
                        <div class="admin-actions au-card-actions team-mobile-actions">
                            <button type="button" class="admin-action-btn au-view-staff-btn team-action-btn-flex" data-staff-id="<?= $m['id'] ?>">
                                👤 View Profile
                            </button>
                            <form method="POST" action="{{ route('admin/teams') }}" class="team-action-form-flex">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="toggle_status" />
                                <input type="hidden" name="member_id" value="<?= $m['id'] ?>" />
                                <button type="submit" class="admin-action-btn <?= $isActive ? 'warning' : 'primary' ?>" style="width:100%;">
                                    <?= $isActive ? '⏸️ Deactivate' : '▶️ Activate' ?>
                                </button>
                            </form>
                            <button type="button" class="admin-action-btn danger au-remove-staff-btn team-action-btn-flex"
                                data-member-id="<?= $m['id'] ?>" data-member-name="<?= e($m['name']) ?>">
                                🗑️ Remove
                            </button>
                        </div>
                    </article>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Dynamic Pagination -->
            <?php if (($total_pages ?? 1) > 1): ?>
            <nav class="au-pagination" aria-label="Team pagination" id="team-pagination">
                <?php
                    $queryParams = $_GET;
                    $prevPage = max(1, $current_page - 1);
                    $nextPage = min($total_pages, $current_page + 1);
                    $queryParams['page'] = $prevPage;
                    $prevUrl = '?' . http_build_query($queryParams);
                    $queryParams['page'] = $nextPage;
                    $nextUrl = '?' . http_build_query($queryParams);
                ?>
                <a href="<?= $current_page > 1 ? $prevUrl : '#' ?>" class="au-page-btn <?= $current_page <= 1 ? 'disabled' : '' ?>">
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="15 18 9 12 15 6" />
                    </svg>
                    Prev
                </a>

                <?php for ($p = 1; $p <= $total_pages; $p++):
                    $queryParams['page'] = $p;
                    $pUrl = '?' . http_build_query($queryParams);
                ?>
                    <a href="<?= $pUrl ?>" class="au-page-num <?= $p === $current_page ? 'active' : '' ?>"><?= $p ?></a>
                <?php endfor; ?>

                <a href="<?= $current_page < $total_pages ? $nextUrl : '#' ?>" class="au-page-btn <?= $current_page >= $total_pages ? 'disabled' : '' ?>">
                    Next
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="9 18 15 12 9 6" />
                    </svg>
                </a>
            </nav>
            <?php endif; ?>
        </div>
    </section>
</div>

<!-- Staff Detail Modal -->
<div class="modal-overlay hidden" id="staff-detail-modal" role="dialog" aria-modal="true" aria-labelledby="staff-detail-title">
    <div class="modal-card staff-modal-card">
        <div class="staff-modal-header">
            <h3 id="staff-detail-title" style="margin:0; font-size:1.15rem; font-weight:800;">Staff Profile &amp; Deployments</h3>
            <button type="button" id="staff-detail-close" class="admin-action-btn staff-modal-close-btn" aria-label="Close modal">&times;</button>
        </div>
        <div id="staff-detail-content">
            <div style="text-align:center; padding:2.5rem 1rem; color:rgb(var(--muted));">
                <div style="font-size:2rem; margin-bottom:0.5rem;">⏳</div>
                <div>Loading staff profile...</div>
            </div>
        </div>
    </div>
</div>

<!-- Confirm Remove Staff Modal -->
<div class="modal-overlay hidden" id="remove-modal" role="dialog" aria-modal="true" aria-labelledby="remove-modal-title">
    <div class="modal-card">
        <h3 id="remove-modal-title" style="margin:0 0 0.5rem; font-size:1.15rem; font-weight:800; color:#ef4444;">🗑️ Remove Team Member</h3>
        <p style="margin:0 0 1.25rem; font-size:0.9rem; color:rgb(var(--muted)); line-height:1.5;">
            Are you sure you want to remove <strong id="remove-member-name" style="color:rgb(var(--text));">this staff member</strong> from the registry?
            They will permanently lose access to all active assignments, mobile ticket scanners, and coordinator stations.
        </p>
        <form method="POST" action="{{ route('admin/teams') }}">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="remove_member" />
            <input type="hidden" name="member_id" id="remove-member-id" value="" />
            <div class="modal-actions">
                <button type="button" class="admin-action-btn" id="remove-cancel-btn">Cancel</button>
                <button type="submit" class="admin-action-btn danger">Yes, Remove Member</button>
            </div>
        </form>
    </div>
</div>

<!-- Confirm Unassign Staff Modal -->
<div class="modal-overlay hidden" id="unassign-modal" role="dialog" aria-modal="true" aria-labelledby="unassign-modal-title">
    <div class="modal-card">
        <h3 id="unassign-modal-title" style="margin:0 0 0.5rem; font-size:1.15rem; font-weight:800; color:#f59e0b;">⚠️ Unassign Staff Member</h3>
        <p style="margin:0 0 1.25rem; font-size:0.9rem; color:rgb(var(--muted)); line-height:1.5;">
            Are you sure you want to unassign <strong id="unassign-member-name" style="color:rgb(var(--text));">this staff member</strong> from
            <strong id="unassign-event-name" style="color:rgb(var(--text));">this event</strong>?
        </p>
        <form method="POST" action="{{ route('admin/teams') }}">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="unassign_member" />
            <input type="hidden" name="assignment_id" id="unassign-assignment-id" value="" />
            <input type="hidden" name="member_id" id="unassign-member-id" value="" />
            <input type="hidden" name="event_id" id="unassign-event-id" value="" />
            <div class="modal-actions">
                <button type="button" class="admin-action-btn" id="unassign-cancel-btn">Cancel</button>
                <button type="submit" class="admin-action-btn primary">Yes, Unassign</button>
            </div>
        </form>
    </div>
</div>

<style>
/* ═══════════════════════════════════════════════════════════════════
   Admin Teams Page Responsive Styles & Component Polish
   ═══════════════════════════════════════════════════════════════════ */
.admin-teams-page-container {
    width: 100%;
    max-width: 1200px;
    margin: 0 auto;
}

.team-distribution-card {
    background: var(--surface);
    border: 1px solid rgba(var(--muted), 0.12);
    box-shadow: 0 10px 30px rgba(0,0,0,0.03);
}

.team-role-pill:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.06);
}

.admin-teams-forms-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(min(100%, 340px), 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.team-form-card {
    margin-bottom: 0;
    border-radius: 22px;
    padding: 1.5rem 1.75rem;
    background: var(--surface);
    border: 1px solid rgba(var(--muted), 0.12);
    box-shadow: 0 10px 30px rgba(0,0,0,0.03);
}

.team-form-label {
    display: block;
    margin-bottom: 0.85rem;
    font-weight: 600;
    font-size: 0.88rem;
    color: rgb(var(--text));
}

.team-form-input,
.team-form-select {
    width: 100%;
    margin-top: 0.35rem;
    padding: 0.7rem 1rem;
    border-radius: 12px;
    border: 1px solid rgba(var(--muted), 0.2);
    background: var(--bg);
    box-sizing: border-box;
    font-size: 0.92rem;
    color: var(--text);
    transition: border-color 0.2s, box-shadow 0.2s;
}

.team-form-input:focus,
.team-form-select:focus {
    outline: none;
    border-color: rgb(var(--brand));
    box-shadow: 0 0 0 3px rgba(var(--brand), 0.15);
}

.team-assign-split-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.85rem;
}

.team-submit-btn {
    width: 100%;
    margin-top: 0.85rem;
    height: 46px;
    border-radius: 12px;
    font-weight: 700;
    justify-content: center;
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
}

.team-registry-card {
    margin-bottom: 0;
    border-radius: 22px;
    padding: 1.75rem;
    background: var(--surface);
    border: 1px solid rgba(var(--muted), 0.12);
    box-shadow: 0 10px 30px rgba(0,0,0,0.03);
}

.registry-title {
    margin: 0;
    font-size: 1.35rem;
    font-weight: 800;
}

.registry-subtitle {
    margin: 0.25rem 0 0;
    font-size: 0.88rem;
    color: rgb(var(--muted));
}

.team-toolbar-form {
    display: flex;
    gap: 0.75rem;
    flex-wrap: wrap;
    margin-bottom: 1.5rem;
    align-items: center;
}

.team-search-box {
    flex: 1;
    min-width: 220px;
}

.team-filter-input,
.team-filter-select {
    padding: 0.65rem 1rem;
    border-radius: 12px;
    border: 1px solid rgba(var(--muted), 0.2);
    background: var(--bg);
    font-size: 0.9rem;
    box-sizing: border-box;
    color: var(--text);
}

.team-filter-input {
    width: 100%;
}

.team-filters-group {
    display: flex;
    gap: 0.75rem;
    flex-wrap: wrap;
    align-items: center;
}

.team-filter-btn {
    padding: 0.65rem 1.35rem;
    border-radius: 12px;
    font-weight: 700;
}

.team-reset-btn {
    padding: 0.65rem 1rem;
    border-radius: 12px;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
}

.assignment-summary-text {
    font-size: 0.78rem;
    color: rgb(var(--muted));
    margin-top: 0.25rem;
    max-width: 320px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.team-mobile-card {
    padding: 1.25rem 1rem;
    border-radius: 16px;
    background: var(--surface);
    border: 1px solid rgba(var(--muted), 0.12);
    margin-bottom: 0.75rem;
    box-shadow: 0 4px 15px rgba(0,0,0,0.02);
}

.team-mobile-meta {
    margin: 0.75rem 0 0.85rem;
    padding: 0.65rem 0.85rem;
    background: rgba(var(--muted), 0.05);
    border-radius: 10px;
    display: flex;
    align-items: center;
    gap: 0.65rem;
    flex-wrap: wrap;
}

.team-mobile-actions {
    display: grid;
    grid-template-columns: 1fr 1.2fr 1fr;
    gap: 0.4rem;
}

.team-action-btn-flex {
    flex: 1;
    height: 38px;
    justify-content: center;
    font-size: 0.82rem;
    padding: 0 0.5rem;
}

.team-action-form-flex {
    display: inline;
    margin: 0;
}

.staff-modal-card {
    max-width: 680px;
    width: 100%;
    max-height: calc(100vh - 2rem);
    max-height: calc(100dvh - 2rem);
    overflow-y: auto;
    -webkit-overflow-scrolling: touch;
    border-radius: 22px;
    padding: 1.75rem;
    box-sizing: border-box;
}

.staff-modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid rgba(var(--muted), 0.15);
    padding-bottom: 1rem;
    margin-bottom: 1.25rem;
}

.staff-modal-close-btn {
    padding: 0.35rem 0.65rem;
    font-size: 1.35rem;
    line-height: 1;
    border-radius: 8px;
    cursor: pointer;
    background: rgba(var(--muted), 0.08);
    border: none;
}

/* ═══════════════════════════════════════════════════════════════════
   Responsive Breakpoints: Tablet & Mobile
   ═══════════════════════════════════════════════════════════════════ */
@media (max-width: 900px) {
    .admin-users-stats {
        grid-template-columns: repeat(2, 1fr);
    }
    .admin-teams-forms-row {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 768px) {
    .team-form-card,
    .team-registry-card,
    .team-distribution-card {
        padding: 1.25rem 1rem;
        border-radius: 18px;
    }
    .staff-modal-card {
        padding: 1.25rem 1rem;
        border-radius: 18px;
        max-width: 100% !important;
    }
    .team-toolbar-form {
        flex-direction: column;
        align-items: stretch;
    }
    .team-filters-group {
        display: grid;
        grid-template-columns: 1fr 1fr;
        width: 100%;
        gap: 0.5rem;
    }
    .team-filters-group select {
        width: 100%;
    }
    .team-filter-btn,
    .team-reset-btn {
        width: 100%;
        justify-content: center;
        text-align: center;
        box-sizing: border-box;
    }
    .team-assign-split-row {
        grid-template-columns: 1fr;
        gap: 0.5rem;
    }
}

@media (max-width: 520px) {
    .admin-users-stats {
        grid-template-columns: 1fr;
        gap: 0.75rem;
    }
    .team-filters-group {
        grid-template-columns: 1fr;
    }
    .team-mobile-actions {
        grid-template-columns: 1fr;
        gap: 0.5rem;
    }
    .team-action-btn-flex,
    .team-action-form-flex button {
        width: 100%;
        height: 40px;
    }
    .modal-actions {
        flex-direction: column-reverse;
        gap: 0.5rem;
    }
    .modal-actions button {
        width: 100%;
        justify-content: center;
    }
}
</style>

<script>
function prefillAssignForm(eventId, roleId) {
    var eventSelect = document.getElementById('assign-event-select');
    var roleSelect = document.getElementById('assign-role-select');
    var assignForm = document.getElementById('assign-team-form');
    if (eventSelect && eventId) {
        eventSelect.value = eventId;
    }
    if (roleSelect && roleId) {
        roleSelect.value = roleId;
    }
    if (assignForm) {
        assignForm.scrollIntoView({ behavior: 'smooth', block: 'center' });
        var memberSelect = document.getElementById('assign-member-select');
        if (memberSelect) memberSelect.focus();
    }
}
</script>
@endsection