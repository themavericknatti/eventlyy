<?php

/**
 * Team Workstation Login Page
 *
 * Clean, responsive workstation portal entry and task selection view.
 * Enables staff members to sign in via registered email and launch into
 * their assigned role desks (Scanner, Box Office, Usher, Support, Tech, Finance).
 *
 * @package Eventlyy
 * @subpackage Views/Teams
 * @author Celio Natti
 * @version 3.0.0
 */

$siteTitle = site_setting('site_title', 'Eventlyy');
$siteLogo  = site_setting('site_logo');
$activeEvent = \Ace\Application::$app->session->get('team_event');
$firstLetter = mb_substr($siteTitle, 0, 1);
?>

@extends('layouts/team')

@section('title')
Staff Workstation Portal — <?= e($siteTitle) ?>
@endsection

@section('style')
<style>
/* ═════════════════════════════════════════════════════════════
   Team Portal Login Component System (.tm-auth-*)
   Clean, responsive, accessible, theme-reactive (Light & Dark).
   ═════════════════════════════════════════════════════════════ */

.tm-auth-container {
    max-width: 1080px;
    margin: 0 auto;
    padding: clamp(1rem, 3vw, 2.5rem) 1rem 3.5rem;
}

.tm-auth-split {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 2.5rem;
    align-items: start;
}

/* ─── Left Hero / Feature Panel ────────────────────────────── */
.tm-auth-panel {
    background: radial-gradient(circle at 10% 15%, rgba(var(--brand), 0.12) 0%, transparent 45%),
                radial-gradient(circle at 90% 85%, rgba(16, 185, 129, 0.10) 0%, transparent 45%),
                linear-gradient(135deg, rgba(var(--card), 0.95), rgba(var(--card), 0.85));
    border: 1px solid rgba(var(--brand), 0.16);
    border-radius: 24px;
    padding: clamp(2rem, 4vw, 3rem) clamp(1.5rem, 3vw, 2.25rem);
    box-shadow: 0 16px 40px -15px rgba(var(--brand), 0.12);
    position: relative;
    overflow: hidden;
}

.tm-auth-panel::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, rgb(var(--brand)), color-mix(in srgb, rgb(var(--brand)) 70%, rgb(var(--accent))));
}

.tm-auth-brand-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.65rem;
    padding: 0.4rem 0.85rem;
    border-radius: 999px;
    background: rgb(var(--card));
    border: 1px solid rgba(var(--muted), 0.15);
    margin-bottom: 1.5rem;
}

.tm-auth-brand-logo {
    max-height: 24px;
    max-width: 90px;
    object-fit: contain;
}

.tm-auth-brand-letter {
    width: 26px;
    height: 26px;
    border-radius: 8px;
    background: linear-gradient(135deg, rgb(var(--brand)), #10b981);
    color: #ffffff;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-weight: 800;
    font-size: 0.85rem;
}

.tm-auth-brand-title {
    font-size: 0.82rem;
    font-weight: 800;
    color: rgb(var(--text));
    letter-spacing: 0.02em;
}

.tm-auth-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    padding: 0.3rem 0.75rem;
    border-radius: 999px;
    font-size: 0.74rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: rgb(var(--brand));
    background: rgba(var(--brand), 0.1);
    margin-bottom: 0.85rem;
}

.tm-auth-panel-title {
    font-size: clamp(1.65rem, 3.5vw, 2.25rem);
    font-weight: 900;
    line-height: 1.18;
    letter-spacing: -0.025em;
    color: rgb(var(--text));
    margin: 0 0 0.85rem;
}

.tm-auth-panel-lead {
    font-size: 0.95rem;
    color: rgb(var(--muted));
    line-height: 1.6;
    margin: 0 0 2rem;
}

.tm-auth-features {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
}

.tm-auth-feature-item {
    display: flex;
    align-items: flex-start;
    gap: 0.85rem;
}

.tm-auth-feature-icon {
    width: 38px;
    height: 38px;
    border-radius: 10px;
    background: rgba(var(--brand), 0.1);
    color: rgb(var(--brand));
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.tm-auth-feature-text strong {
    display: block;
    font-size: 0.92rem;
    font-weight: 700;
    color: rgb(var(--text));
    margin-bottom: 0.15rem;
}

.tm-auth-feature-text span {
    font-size: 0.84rem;
    color: rgb(var(--muted));
    line-height: 1.5;
}

/* ─── Right Form Card ──────────────────────────────────────── */
.tm-auth-card {
    background: rgb(var(--card));
    border: 1px solid rgba(var(--muted), 0.16);
    border-radius: 24px;
    padding: clamp(1.75rem, 4vw, 2.75rem) clamp(1.5rem, 3vw, 2.25rem);
    box-shadow: 0 16px 40px -15px rgba(0, 0, 0, 0.08);
}

.tm-auth-card-head {
    margin-bottom: 1.5rem;
}

.tm-auth-card-title {
    font-size: clamp(1.35rem, 2.8vw, 1.75rem);
    font-weight: 800;
    color: rgb(var(--text));
    margin: 0 0 0.35rem;
    letter-spacing: -0.02em;
}

.tm-auth-card-desc {
    font-size: 0.88rem;
    color: rgb(var(--muted));
    margin: 0;
    line-height: 1.5;
}

/* Active Session Badge */
.tm-session-badge {
    background: rgba(16, 185, 129, 0.08);
    border: 1px solid rgba(16, 185, 129, 0.22);
    border-radius: 16px;
    padding: 1rem 1.15rem;
    margin-bottom: 1.5rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 0.85rem;
    flex-wrap: wrap;
}

.tm-session-staff-info {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.tm-session-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: linear-gradient(135deg, #059669, #10b981);
    color: #ffffff;
    font-weight: 800;
    font-size: 0.9rem;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.tm-session-status-tag {
    font-size: 0.7rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: #10b981;
}

.tm-session-name {
    font-size: 0.95rem;
    font-weight: 800;
    color: rgb(var(--text));
    line-height: 1.2;
}

.tm-session-email {
    font-size: 0.78rem;
    color: rgb(var(--muted));
}

.tm-session-exit-btn {
    padding: 0.45rem 0.9rem;
    font-size: 0.8rem;
    font-weight: 700;
    border-radius: 10px;
    background: rgba(239, 68, 68, 0.1);
    color: #ef4444;
    border: 1px solid rgba(239, 68, 68, 0.25);
    text-decoration: none;
    transition: all var(--duration-fast, 0.15s) ease-out;
}

.tm-session-exit-btn:hover {
    background: #ef4444;
    color: #ffffff;
}

/* Email Form */
.tm-form-field {
    margin-bottom: 1.25rem;
}

.tm-form-label {
    display: block;
    font-size: 0.86rem;
    font-weight: 700;
    color: rgb(var(--text));
    margin-bottom: 0.5rem;
}

.tm-input-wrap {
    position: relative;
    display: flex;
    align-items: center;
}

.tm-input-icon {
    position: absolute;
    left: 1rem;
    color: rgb(var(--muted));
    pointer-events: none;
}

.tm-input-field {
    width: 100%;
    height: 48px;
    padding: 0 1rem 0 2.75rem;
    border-radius: 14px;
    border: 1.5px solid rgba(var(--muted), 0.2);
    background: rgb(var(--bg));
    color: rgb(var(--text));
    font-size: 16px; /* Prevents iOS auto-zoom */
    font-family: inherit;
    outline: none;
    transition: border-color var(--duration-fast, 0.15s) ease-out, box-shadow var(--duration-fast, 0.15s) ease-out;
    box-sizing: border-box;
}

.tm-input-field:focus {
    border-color: rgb(var(--brand));
    box-shadow: 0 0 0 3px rgba(var(--brand), 0.14);
}

.tm-btn-submit {
    width: 100%;
    height: 48px;
    border-radius: 14px;
    background: linear-gradient(135deg, rgb(var(--brand)), color-mix(in srgb, rgb(var(--brand)) 80%, rgb(var(--accent))));
    color: #ffffff !important;
    font-size: 0.95rem;
    font-weight: 800;
    border: none;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.45rem;
    box-shadow: 0 6px 20px rgba(var(--brand), 0.35);
    transition: all var(--duration-fast, 0.15s) ease-out;
}

.tm-btn-submit:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 26px rgba(var(--brand), 0.45);
    filter: brightness(1.05);
}

/* Event Assignment Cards */
.tm-assignments-title {
    font-size: 0.8rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: rgb(var(--muted));
    margin: 1.5rem 0 0.85rem;
}

.tm-assignments-list {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.tm-assignment-card {
    width: 100%;
    box-sizing: border-box;
    text-align: left;
    border-radius: 16px;
    padding: 1rem 1.15rem;
    cursor: pointer;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
    border: 1.5px solid rgba(var(--muted), 0.16);
    background: rgb(var(--card));
    transition: all var(--duration-fast, 0.15s) ease-out;
}

.tm-assignment-card:hover {
    transform: translateY(-2px);
    border-color: rgba(var(--brand), 0.4);
    box-shadow: 0 6px 18px rgba(0, 0, 0, 0.05);
}

.tm-assignment-card.tm-assignment-card--active {
    border-color: rgb(var(--brand));
    background: rgba(var(--brand), 0.04);
}

.tm-assignment-content {
    min-width: 0;
    flex: 1;
}

.tm-assignment-meta-top {
    display: flex;
    align-items: center;
    gap: 0.45rem;
    margin-bottom: 0.35rem;
    flex-wrap: wrap;
}

.tm-assignment-role {
    font-size: 0.72rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    padding: 0.15rem 0.5rem;
    border-radius: 6px;
    background: rgba(var(--brand), 0.12);
    color: rgb(var(--brand));
}

.tm-assignment-active-badge {
    font-size: 0.68rem;
    font-weight: 800;
    background: #10b981;
    color: #ffffff;
    padding: 0.15rem 0.5rem;
    border-radius: 6px;
}

.tm-assignment-name {
    font-size: 0.98rem;
    font-weight: 800;
    color: rgb(var(--text));
    margin: 0;
    line-height: 1.3;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.tm-assignment-info {
    font-size: 0.8rem;
    color: rgb(var(--muted));
    margin-top: 0.25rem;
}

.tm-assignment-btn {
    height: 38px;
    padding: 0 1rem;
    border-radius: 10px;
    font-size: 0.84rem;
    font-weight: 700;
    background: rgba(var(--brand), 0.1);
    color: rgb(var(--brand));
    border: 1px solid rgba(var(--brand), 0.25);
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    flex-shrink: 0;
}

.tm-assignment-card:hover .tm-assignment-btn,
.tm-assignment-card--active .tm-assignment-btn {
    background: rgb(var(--brand));
    color: #ffffff;
    border-color: rgb(var(--brand));
}

/* Task Module Previews */
.tm-modules-section {
    margin-top: 2rem;
    padding-top: 1.5rem;
    border-top: 1px solid rgba(var(--muted), 0.14);
}

.tm-modules-title {
    font-size: 0.8rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: rgb(var(--muted));
    margin: 0 0 0.85rem;
}

.tm-modules-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 0.65rem;
}

.tm-module-card {
    padding: 0.85rem 1rem;
    border-radius: 14px;
    background: rgba(var(--muted), 0.04);
    border: 1px solid rgba(var(--muted), 0.12);
    cursor: pointer;
    transition: all var(--duration-fast, 0.15s) ease-out;
}

.tm-module-card:hover {
    background: rgba(var(--brand), 0.04);
    border-color: rgba(var(--brand), 0.3);
    transform: translateY(-1px);
}

.tm-module-header {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 0.2rem;
}

.tm-module-badge {
    font-size: 0.7rem;
    font-weight: 800;
    text-transform: uppercase;
    padding: 0.15rem 0.45rem;
    border-radius: 6px;
}

.tm-module-badge--boxoffice { background: rgba(99, 102, 241, 0.12); color: #6366f1; }
.tm-module-badge--security { background: rgba(239, 68, 68, 0.12); color: #ef4444; }
.tm-module-badge--usher { background: rgba(168, 85, 247, 0.12); color: #9333ea; }
.tm-module-badge--support { background: rgba(14, 165, 233, 0.12); color: #0ea5e9; }

.tm-module-name {
    font-size: 0.9rem;
    font-weight: 800;
    color: rgb(var(--text));
}

.tm-module-desc {
    font-size: 0.8rem;
    color: rgb(var(--muted));
    margin: 0;
    line-height: 1.4;
}

/* ═════════════════════════════════════════════════════════════
   Responsive Design Breakpoints
   ═════════════════════════════════════════════════════════════ */

/* Tablet (max 900px) */
@media (max-width: 900px) {
    .tm-auth-split {
        grid-template-columns: 1fr;
        gap: 2rem;
        max-width: 580px;
        margin: 0 auto;
    }
}

/* Mobile (max 600px) */
@media (max-width: 600px) {
    .tm-auth-container {
        padding: 0.75rem 0.5rem 3rem;
    }

    .tm-auth-panel {
        padding: 1.75rem 1.15rem;
        border-radius: 20px;
    }

    .tm-auth-card {
        padding: 1.75rem 1.15rem;
        border-radius: 20px;
    }

    .tm-assignment-card {
        flex-direction: column;
        align-items: stretch;
        gap: 0.75rem;
    }

    .tm-assignment-btn {
        justify-content: center;
        width: 100%;
    }
}
</style>
@endsection

@section('content')
<div class="tm-auth-container">
    <div class="tm-auth-split">

        <!-- ═══ Left Panel: Hero Overview & Features ═══ -->
        <section class="tm-auth-panel">
            <div class="tm-auth-brand-badge">
                <?php if (!empty($siteLogo)): ?>
                    <img src="<?= e(asset($siteLogo)) ?>" alt="<?= e($siteTitle) ?>" class="tm-auth-brand-logo" />
                <?php else: ?>
                    <span class="tm-auth-brand-letter"><?= e($firstLetter) ?></span>
                <?php endif; ?>
                <span class="tm-auth-brand-title"><?= e($siteTitle) ?> Teams</span>
            </div>

            <span class="tm-auth-badge">
                <span>🛡️</span> Event Operations Workstation
            </span>

            <h1 class="tm-auth-panel-title">
                Access your event task workstation
            </h1>

            <p class="tm-auth-panel-lead">
                Sign in with your registered organization staff email to unlock role-specific live tools, gate pass verification, and box office controls.
            </p>

            <ul class="tm-auth-features">
                <li class="tm-auth-feature-item">
                    <div class="tm-auth-feature-icon">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                            <circle cx="8.5" cy="7" r="4"></circle>
                            <line x1="20" y1="8" x2="20" y2="14"></line>
                            <line x1="23" y1="11" x2="17" y2="11"></line>
                        </svg>
                    </div>
                    <div class="tm-auth-feature-text">
                        <strong>Role-Based Access (RBAC)</strong>
                        <span>Seamlessly switch between Scanner, Box Office, Usher, Support, or Tech roles across different events.</span>
                    </div>
                </li>

                <li class="tm-auth-feature-item">
                    <div class="tm-auth-feature-icon">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                            <polyline points="22 4 12 14.01 9 11.01"></polyline>
                        </svg>
                    </div>
                    <div class="tm-auth-feature-text">
                        <strong>Real-Time Gate Synchronization</strong>
                        <span>Live optical QR ticket validation instantly updates door counts and attendance telemetry.</span>
                    </div>
                </li>

                <li class="tm-auth-feature-item">
                    <div class="tm-auth-feature-icon">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect>
                            <line x1="8" y1="21" x2="16" y2="21"></line>
                            <line x1="12" y1="17" x2="12" y2="21"></line>
                        </svg>
                    </div>
                    <div class="tm-auth-feature-text">
                        <strong>Mobile Camera Ready</strong>
                        <span>Engineered for responsive handheld use on mobile turnstiles, tablet stands, or desktop control desks.</span>
                    </div>
                </li>
            </ul>
        </section>

        <!-- ═══ Right Panel: Sign-In / Assignment Picker Card ═══ -->
        <main class="tm-auth-card">
            <div class="tm-auth-card-head">
                <h2 class="tm-auth-card-title">Staff Portal Entry</h2>
                <p class="tm-auth-card-desc">
                    <?= !empty($staff) ? 'Select an active event assignment below to launch your workstation.' : 'Enter your registered staff email address to log in.' ?>
                </p>
            </div>

            <!-- Active Staff Session Indicator -->
            <?php if (!empty($staff)): ?>
                <div class="tm-session-badge">
                    <div class="tm-session-staff-info">
                        <div class="tm-session-avatar">
                            <?= strtoupper(mb_substr($staff['name'], 0, 2)) ?>
                        </div>
                        <div>
                            <span class="tm-session-status-tag">Active Session</span>
                            <div class="tm-session-name"><?= e($staff['name']) ?></div>
                            <span class="tm-session-email"><?= e($staff['email']) ?></span>
                        </div>
                    </div>
                    <a href="{{ route('teams/logout') }}" class="tm-session-exit-btn">Exit</a>
                </div>
            <?php endif; ?>

            <!-- Login Form (When Logged Out) -->
            <?php if (empty($staff)): ?>
                <form method="POST" action="{{ route('teams/login') }}">
                    <?= csrf_field() ?>
                    <div class="tm-form-field">
                        <label for="staff-email-input" class="tm-form-label">Registered Staff Email Address</label>
                        <div class="tm-input-wrap">
                            <svg class="tm-input-icon" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                                <polyline points="22,6 12,13 2,6"></polyline>
                            </svg>
                            <input type="email" id="staff-email-input" name="email" placeholder="e.g. staff@eventlyy.com" required class="tm-input-field" autocomplete="email">
                        </div>
                    </div>

                    <button type="submit" class="tm-btn-submit">
                        <span>Sign In to Workstation</span>
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                            <polyline points="12 5 19 12 12 19"></polyline>
                        </svg>
                    </button>
                </form>
            <?php endif; ?>

            <!-- Assigned Events Selector for Logged-In Staff -->
            <?php if (!empty($staff) && !empty($assignments)): ?>
                <div>
                    <div class="tm-assignments-title">
                        Active Event Assignments (<?= count($assignments) ?>):
                    </div>

                    <div class="tm-assignments-list">
                        <?php foreach ($assignments as $asg):
                            $isActive = ($activeEvent && $activeEvent['id'] == $asg['event_id']);
                        ?>
                            <form method="POST" action="{{ route('teams/switch-event') }}">
                                <?= csrf_field() ?>
                                <input type="hidden" name="event_id" value="<?= $asg['event_id'] ?>" />
                                <button type="submit" class="tm-assignment-card <?= $isActive ? 'tm-assignment-card--active' : '' ?>">
                                    <div class="tm-assignment-content">
                                        <div class="tm-assignment-meta-top">
                                            <span class="tm-assignment-role"><?= e($asg['role_name']) ?></span>
                                            <?php if ($isActive): ?>
                                                <span class="tm-assignment-active-badge">Active Workspace</span>
                                            <?php endif; ?>
                                        </div>
                                        <h3 class="tm-assignment-name"><?= e($asg['event_name']) ?></h3>
                                        <div class="tm-assignment-info">
                                            <span>📅 <?= date('M d, Y', strtotime($asg['event_date'])) ?></span> &bull;
                                            <span>📍 <?= e($asg['event_location']) ?></span>
                                        </div>
                                    </div>

                                    <span class="tm-assignment-btn">
                                        <span><?= $isActive ? 'Launch Desk' : 'Switch' ?></span>
                                        <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                            <polyline points="9 18 15 12 9 6"></polyline>
                                        </svg>
                                    </span>
                                </button>
                            </form>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Task Modules Guide (When logged out) -->
            <?php if (empty($staff)): ?>
                <div class="tm-modules-section">
                    <div class="tm-modules-title">Available Workstation Desks:</div>
                    <div class="tm-modules-grid">
                        <div class="tm-module-card" onclick="document.getElementById('staff-email-input').focus();">
                            <div class="tm-module-header">
                                <span class="tm-module-badge tm-module-badge--boxoffice">Box Office</span>
                                <span class="tm-module-name">Organizer &amp; Box Office</span>
                            </div>
                            <p class="tm-module-desc">Gate ticket issuance, door cash sales, staff check-in roster &amp; door telemetry.</p>
                        </div>

                        <div class="tm-module-card" onclick="document.getElementById('staff-email-input').focus();">
                            <div class="tm-module-header">
                                <span class="tm-module-badge tm-module-badge--security">Gate Security</span>
                                <span class="tm-module-name">Security QR Scanner</span>
                            </div>
                            <p class="tm-module-desc">Camera-based optical QR admission verification with photo upload &amp; manual code entry.</p>
                        </div>

                        <div class="tm-module-card" onclick="document.getElementById('staff-email-input').focus();">
                            <div class="tm-module-header">
                                <span class="tm-module-badge tm-module-badge--usher">Floor Guide</span>
                                <span class="tm-module-name">Usher &amp; Seating Guide</span>
                            </div>
                            <p class="tm-module-desc">Real-time zone occupancy, guest seat locator, and crowd direction assistance.</p>
                        </div>

                        <div class="tm-module-card" onclick="document.getElementById('staff-email-input').focus();">
                            <div class="tm-module-header">
                                <span class="tm-module-badge tm-module-badge--support">Guest Desk</span>
                                <span class="tm-module-name">Customer Support Desk</span>
                            </div>
                            <p class="tm-module-desc">Attendee lookup, resending passes, resolution of duplicates, and gate overrides.</p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </main>
    </div>
</div>
@endsection