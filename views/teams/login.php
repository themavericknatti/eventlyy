<?php

/**
 * Team Login Page
 *
 * Workstation portal login and task selection view.
 *
 * @package Evently
 * @subpackage Views/Teams
 * @author Celio Natti
 * @version 2.2.0
 */

$activeEvent = \Ace\Application::$app->session->get('team_event');
?>

@extends('layouts/team')

@section('title')
Team Workstation Login - Evently
@endsection

@section('style')
<style>
@media (max-width: 850px) {
    .team-auth-split {
        flex-direction: column !important;
        max-width: 540px;
        margin: 0 auto;
        gap: 1.5rem;
    }
    .team-auth-panel {
        padding: 1.5rem !important;
    }
    .team-auth-card {
        padding: 1.5rem !important;
    }
}
@media (max-width: 480px) {
    .team-auth-panel {
        padding: 1.15rem 1rem !important;
    }
    .team-auth-card {
        padding: 1.25rem 1rem !important;
        border-radius: 18px;
    }
}
</style>
@endsection

@section('content')
<div class="auth-split team-auth-split">
    <!-- Left Panel -->
    <section class="auth-panel team-auth-panel">
        <div class="auth-brand-badge">
            <span class="brand-letter">E</span>
            <span>Evently Teams</span>
        </div>
        <div class="auth-panel-hero">
            <span class="panel-badge">Staff Workspace</span>
            <h2>Access your event workstation</h2>
            <p class="lede">Enter your registered staff email address to log in and select from your active event assignments.</p>
        </div>

        <ul class="auth-features-list">
            <li>
                <span class="feature-icon">
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5">
                        <polyline points="20 6 9 17 4 12" />
                    </svg>
                </span>
                <div>
                    <strong>Multi-event RBAC assignments</strong>
                    <span>Hold distinct roles across multiple events simultaneously without changing your staff profile.</span>
                </div>
            </li>
            <li>
                <span class="feature-icon">
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5">
                        <polyline points="20 6 9 17 4 12" />
                    </svg>
                </span>
                <div>
                    <strong>Live check-in &amp; gate sync</strong>
                    <span>Real-time ticket validation across gate lanes, box office sales, and support desks.</span>
                </div>
            </li>
        </ul>
    </section>

    <!-- Right Panel: Form Card -->
    <div class="auth-card team-auth-card">
        <div class="auth-card-header">
            <h1 style="font-size: clamp(1.25rem, 3vw, 1.65rem); margin: 0 0 0.35rem 0; color: rgb(var(--text));">Staff Portal Entry</h1>
            <p style="font-size: 0.88rem; color: rgb(var(--muted)); margin: 0;"><?= !empty($staff) ? 'Select an event assignment below to enter your workstation.' : 'Enter your registered staff email address to log in.' ?></p>
        </div>

        <?php if (!empty($staff)): ?>
            <div style="background:rgba(16, 185, 129, 0.1); border:1px solid rgba(16, 185, 129, 0.2); border-radius:12px; padding:0.85rem 1rem; margin-top:1rem; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:0.5rem;">
                <div>
                    <div style="font-size:0.72rem; color:#10b981; font-weight:700; text-transform:uppercase; letter-spacing:0.05em;">Active Staff Session</div>
                    <strong style="font-size:0.95rem; color:rgb(var(--text));"><?= e($staff['name']) ?></strong>
                    <span style="font-size:0.8rem; color:rgb(var(--muted)); word-break:break-all;"> (<?= e($staff['email']) ?>)</span>
                </div>
                <a href="{{ route('teams/logout') }}" class="admin-action-btn danger" style="padding:0.38rem 0.75rem; font-size:0.78rem; text-decoration:none; border-radius:8px;">Exit</a>
            </div>
        <?php endif; ?>

        <!-- Staff Email Login Form -->
        <?php if (empty($staff)): ?>
            <form method="POST" action="{{ route('teams/login') }}" style="margin-top: 1.25rem;">
                <?= csrf_field() ?>
                <label style="display: block; font-weight: 600; margin-bottom: 0.5rem; font-size: 0.88rem; color: rgb(var(--text));">Registered Staff Email Address
                    <input type="email" id="staff-email-input" name="email" placeholder="e.g. alex@eventlyy.com" required
                        class="team-modal-input" style="margin-top:0.35rem;">
                </label>
                <button type="submit" class="admin-action-btn primary" style="width:100%; height:46px; border-radius:12px; justify-content:center; margin-top:0.85rem; font-size:0.92rem; font-weight:700;">
                    Sign In to Workstation
                </button>
            </form>
        <?php endif; ?>

        <!-- Assigned Events Selector for Logged-In Staff -->
        <?php if (!empty($staff) && !empty($assignments)): ?>
            <div style="margin-top: 1.5rem; border-top: 1px solid rgba(var(--muted),0.15); padding-top: 1.25rem;">
                <div style="font-size:0.82rem; font-weight:700; color:rgb(var(--muted)); text-transform:uppercase; letter-spacing:0.05em; margin-bottom:0.75rem;">
                    My Event Assignments (<?= count($assignments) ?>):
                </div>

                <div style="display:flex; flex-direction:column; gap:0.75rem;">
                    <?php foreach ($assignments as $asg):
                        $isActive = ($activeEvent && $activeEvent['id'] == $asg['event_id']);
                    ?>
                        <form method="POST" action="{{ route('teams/switch-event') }}">
                            <?= csrf_field() ?>
                            <input type="hidden" name="event_id" value="<?= $asg['event_id'] ?>" />
                            <button type="submit" class="staff-select-card"
                                style="width:100%; box-sizing:border-box; text-align:left; border:1px solid <?= $isActive ? 'rgb(var(--brand))' : 'rgba(var(--muted),0.14)' ?>; background:<?= $isActive ? 'rgba(var(--brand),0.06)' : 'rgb(var(--card))' ?>; border-radius:14px; padding:0.85rem 1rem; cursor:pointer; display:flex; justify-content:space-between; align-items:center; gap:0.75rem; flex-wrap:wrap;">
                                <div style="min-width:0; flex:1;">
                                    <div style="display:flex; align-items:center; gap:0.5rem; margin-bottom:0.25rem; flex-wrap:wrap;">
                                        <span class="role-indicator au-role--host" style="background:rgba(var(--brand),0.1); color:rgb(var(--brand)); font-size:0.72rem; font-weight:700; padding:0.15rem 0.45rem; border-radius:6px;">
                                            <?= e($asg['role_name']) ?>
                                        </span>
                                        <?php if ($isActive): ?>
                                            <span style="font-size:0.68rem; background:#10b981; color:#fff; padding:0.12rem 0.45rem; border-radius:6px; font-weight:700;">Active Workspace</span>
                                        <?php endif; ?>
                                    </div>
                                    <strong style="font-size:0.95rem; color:rgb(var(--text)); display:block; word-break:break-word;"><?= e($asg['event_name']) ?></strong>
                                    <div style="font-size:0.78rem; color:rgb(var(--muted)); margin-top:0.2rem;">
                                        📅 <?= date('M d, Y', strtotime($asg['event_date'])) ?> • 📍 <?= e($asg['event_location']) ?>
                                    </div>
                                </div>
                                <span class="admin-action-btn primary" style="padding:0.4rem 0.85rem; font-size:0.8rem; border-radius:8px; flex-shrink:0;">
                                    <?= $isActive ? 'Enter' : 'Switch' ?> →
                                </span>
                            </button>
                        </form>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Workstation Task Modules Preview (When logged out) -->
        <?php if (empty($staff)): ?>
            <div style="margin-top: 1.5rem; border-top: 1px solid rgba(var(--muted),0.15); padding-top: 1.25rem;">
                <div style="font-size:0.8rem; font-weight:700; color:rgb(var(--muted)); text-transform:uppercase; letter-spacing:0.05em; margin-bottom:0.75rem;">
                    Workstation Task Modules:
                </div>

                <div style="display:flex; flex-direction:column; gap:0.65rem;">
                    <div class="staff-select-card" style="padding:0.75rem 1rem; border-radius:12px; background:rgb(var(--card)); border:1px solid rgba(var(--muted),0.14); cursor:pointer;" onclick="document.getElementById('staff-email-input').focus();">
                        <span class="role-indicator" style="background:rgba(99,102,241,0.1); color:#6366f1; font-size:0.7rem; font-weight:700; padding:0.12rem 0.4rem; border-radius:6px;">Box Office</span>
                        <strong style="font-size:0.88rem; margin-left:0.35rem; color:rgb(var(--text));">Event Organiser (Box Office/Finance)</strong>
                        <p style="font-size:0.8rem; color:rgb(var(--muted)); margin:0.2rem 0 0;">Door sales, complimentary tickets & staff check-in roster.</p>
                    </div>

                    <div class="staff-select-card" style="padding:0.75rem 1rem; border-radius:12px; background:rgb(var(--card)); border:1px solid rgba(var(--muted),0.14); cursor:pointer;" onclick="document.getElementById('staff-email-input').focus();">
                        <span class="role-indicator" style="background:rgba(239,68,68,0.1); color:#ef4444; font-size:0.7rem; font-weight:700; padding:0.12rem 0.4rem; border-radius:6px;">Security</span>
                        <strong style="font-size:0.88rem; margin-left:0.35rem; color:rgb(var(--text));">Gate Security Scanner</strong>
                        <p style="font-size:0.8rem; color:rgb(var(--muted)); margin:0.2rem 0 0;">Mobile camera QR ticket validation & manual code entry.</p>
                    </div>

                    <div class="staff-select-card" style="padding:0.75rem 1rem; border-radius:12px; background:rgb(var(--card)); border:1px solid rgba(var(--muted),0.14); cursor:pointer;" onclick="document.getElementById('staff-email-input').focus();">
                        <span class="role-indicator" style="background:rgba(168,85,247,0.1); color:#9333ea; font-size:0.7rem; font-weight:700; padding:0.12rem 0.4rem; border-radius:6px;">Usher</span>
                        <strong style="font-size:0.88rem; margin-left:0.35rem; color:rgb(var(--text));">Usher / Floor Guide</strong>
                        <p style="font-size:0.8rem; color:rgb(var(--muted)); margin:0.2rem 0 0;">Seating row allocations, guest locator & crowd flow management.</p>
                    </div>

                    <div class="staff-select-card" style="padding:0.75rem 1rem; border-radius:12px; background:rgb(var(--card)); border:1px solid rgba(var(--muted),0.14); cursor:pointer;" onclick="document.getElementById('staff-email-input').focus();">
                        <span class="role-indicator" style="background:rgba(14,165,233,0.1); color:#0ea5e9; font-size:0.7rem; font-weight:700; padding:0.12rem 0.4rem; border-radius:6px;">Guest Support</span>
                        <strong style="font-size:0.88rem; margin-left:0.35rem; color:rgb(var(--text));">Guest Support Desk</strong>
                        <p style="font-size:0.8rem; color:rgb(var(--muted)); margin:0.2rem 0 0;">Attendee lookup, pass search & manual check-in overrides.</p>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
@endsection