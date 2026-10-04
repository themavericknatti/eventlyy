<?php

/**
 * Team Tech Ops Page
 *
 * Workstation task view for Technical Operations to monitor:
 * - Sound systems, stage lighting, AV equipment & stage hardware
 * - Gate scanner telemetry, latency, and team workstation audits
 *
 * @package Eventlyy
 * @subpackage Views/Teams
 * @author Celio Natti
 * @version 3.0.0
 */

$eventTime = !empty($event['date']) ? strtotime($event['date']) : null;
$teamMembers = $team_members ?? [];
$pingMs = e($network['ping_ms'] ?? '16');
$packetLoss = e($network['packet_loss'] ?? '0.0%');
?>

@extends('layouts/team')

@section('title')
Technical Operations (Sound, Stage &amp; AV) — <?= e($event['name'] ?? 'Eventlyy') ?>
@endsection

@section('style')
<style>
/* ═════════════════════════════════════════════════════════════
   Tech Ops Workstation Component System (.to-*)
   Scoped, modern, dark/light reactive, responsive.
   ═════════════════════════════════════════════════════════════ */

.to-wrapper {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
    padding-bottom: 2rem;
}

/* ─── Hero Header ──────────────────────────────────────────── */
.to-hero {
    background: radial-gradient(circle at 10% 20%, rgba(99, 102, 241, 0.12) 0%, transparent 45%),
                linear-gradient(135deg, rgba(var(--card), 0.95), rgba(var(--card), 0.85));
    border: 1px solid rgba(99, 102, 241, 0.22);
    border-radius: 22px;
    padding: clamp(1.25rem, 3vw, 1.85rem) clamp(1.25rem, 3vw, 2rem);
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1.25rem;
    flex-wrap: wrap;
    box-shadow: 0 10px 30px -10px rgba(99, 102, 241, 0.12);
}

.to-hero-text {
    min-width: 0;
    flex: 1;
}

.to-badge-wrap {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 0.45rem;
    flex-wrap: wrap;
}

.to-pill-desk {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    padding: 0.25rem 0.75rem;
    border-radius: 999px;
    font-size: 0.76rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    background: rgba(99, 102, 241, 0.14);
    color: #4f46e5;
    border: 1px solid rgba(99, 102, 241, 0.28);
}

.to-pill-live {
    font-size: 0.72rem;
    font-weight: 800;
    padding: 0.2rem 0.6rem;
    border-radius: 999px;
    background: rgba(16, 185, 129, 0.15);
    color: #10b981;
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
}

.to-pill-live::before {
    content: '';
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: #10b981;
    display: inline-block;
}

.to-title {
    font-size: clamp(1.35rem, 3vw, 1.85rem);
    font-weight: 900;
    color: rgb(var(--text));
    margin: 0 0 0.35rem;
    line-height: 1.2;
    letter-spacing: -0.02em;
}

.to-meta {
    font-size: 0.86rem;
    color: rgb(var(--muted));
    margin: 0;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex-wrap: wrap;
}

/* ─── Hardware Grid ────────────────────────────────────────── */
.to-hardware-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1.15rem;
}

.to-hw-card {
    background: rgb(var(--card));
    border: 1px solid rgba(var(--muted), 0.14);
    border-radius: 18px;
    padding: 1.25rem;
    box-shadow: 0 4px 18px rgba(0, 0, 0, 0.03);
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    gap: 0.85rem;
    transition: transform var(--duration-fast, 0.15s) ease-out, border-color var(--duration-fast, 0.15s) ease-out;
}

.to-hw-card:hover {
    transform: translateY(-2px);
    border-color: rgba(var(--brand), 0.3);
}

.to-hw-head {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 0.5rem;
}

.to-hw-name {
    font-size: 0.95rem;
    font-weight: 800;
    color: rgb(var(--text));
    display: flex;
    align-items: center;
    gap: 0.35rem;
}

.to-status-online {
    font-size: 0.7rem;
    font-weight: 800;
    padding: 0.18rem 0.55rem;
    border-radius: 6px;
    background: rgba(16, 185, 129, 0.12);
    color: #059669;
    border: 1px solid rgba(16, 185, 129, 0.25);
    white-space: nowrap;
}

.to-hw-desc {
    font-size: 0.8rem;
    color: rgb(var(--muted));
    margin: 0;
    line-height: 1.45;
}

.to-hw-metric {
    font-size: 0.78rem;
    color: rgb(var(--muted));
    background: rgb(var(--bg));
    padding: 0.45rem 0.75rem;
    border-radius: 10px;
    border: 1px solid rgba(var(--muted), 0.12);
    line-height: 1.4;
}

.to-hw-metric strong {
    color: rgb(var(--text));
}

/* ─── Telemetry Audit Card ─────────────────────────────────── */
.to-card {
    background: rgb(var(--card));
    border: 1px solid rgba(var(--muted), 0.14);
    border-radius: 22px;
    padding: clamp(1.25rem, 3vw, 1.75rem);
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
}

.to-card-head {
    margin-bottom: 1.25rem;
    padding-bottom: 0.85rem;
    border-bottom: 1px solid rgba(var(--muted), 0.12);
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
    flex-wrap: wrap;
}

.to-card-title {
    font-size: 1.15rem;
    font-weight: 800;
    color: rgb(var(--text));
    margin: 0 0 0.25rem;
    letter-spacing: -0.015em;
    display: flex;
    align-items: center;
    gap: 0.45rem;
}

.to-card-sub {
    font-size: 0.84rem;
    color: rgb(var(--muted));
    margin: 0;
}

.to-card-count {
    font-size: 0.8rem;
    font-weight: 800;
    color: rgb(var(--brand));
    background: rgba(var(--brand), 0.1);
    padding: 0.3rem 0.75rem;
    border-radius: 999px;
}

/* ─── Table Component ──────────────────────────────────────── */
.to-table-wrap {
    width: 100%;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    border-radius: 14px;
    border: 1px solid rgba(var(--muted), 0.12);
}

.to-table {
    width: 100%;
    border-collapse: collapse;
    text-align: left;
    min-width: 680px;
}

.to-table th {
    background: rgba(var(--muted), 0.06);
    color: rgb(var(--muted));
    font-size: 0.78rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    padding: 0.85rem 1rem;
    border-bottom: 1px solid rgba(var(--muted), 0.14);
}

.to-table td {
    padding: 0.95rem 1rem;
    font-size: 0.88rem;
    border-bottom: 1px solid rgba(var(--muted), 0.08);
    color: rgb(var(--text));
}

.to-table tr:last-child td {
    border-bottom: none;
}

.to-table tr:hover td {
    background: rgba(var(--muted), 0.02);
}

.to-role-tag {
    font-size: 0.72rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    padding: 0.2rem 0.6rem;
    border-radius: 6px;
    background: rgba(var(--brand), 0.12);
    color: rgb(var(--brand));
    display: inline-block;
}

.to-ip-badge {
    font-family: monospace;
    font-size: 0.8rem;
    background: rgba(var(--muted), 0.08);
    padding: 0.2rem 0.5rem;
    border-radius: 6px;
    color: rgb(var(--text));
    border: 1px solid rgba(var(--muted), 0.12);
}

.to-active-pill {
    font-size: 0.72rem;
    font-weight: 800;
    padding: 0.2rem 0.6rem;
    border-radius: 6px;
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
}

.to-active-pill--online {
    background: rgba(16, 185, 129, 0.12);
    color: #10b981;
}

.to-active-pill--offline {
    background: rgba(var(--muted), 0.12);
    color: rgb(var(--muted));
}

.to-empty-state {
    text-align: center;
    padding: 2.5rem 1.5rem;
    color: rgb(var(--muted));
    font-size: 0.9rem;
}

/* ═════════════════════════════════════════════════════════════
   Responsive Breakpoints
   ═════════════════════════════════════════════════════════════ */

/* Tablet (max 1024px) */
@media (max-width: 1024px) {
    .to-hardware-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

/* Mobile (max 680px) */
@media (max-width: 680px) {
    .to-hero {
        flex-direction: column;
        align-items: stretch;
        padding: 1.25rem 1.15rem;
    }

    .to-hardware-grid {
        grid-template-columns: 1fr;
        gap: 0.75rem;
    }

    .to-card {
        padding: 1.25rem 1rem;
        border-radius: 18px;
    }
}
</style>
@endsection

@section('content')
<div class="to-wrapper">

    <!-- ═══ Hero Header ═══ -->
    <header class="to-hero">
        <div class="to-hero-text">
            <div class="to-badge-wrap">
                <span class="to-pill-desk">🛠️ Workstation — Technical Operations</span>
                <span class="to-pill-live">Telemetry Live</span>
            </div>
            <h1 class="to-title">Sound, Stage Lighting &amp; AV Operations</h1>
            <p class="to-meta">
                <?php if ($eventTime): ?>
                    <span>📅 <?= date('D, M d, Y • g:i A', $eventTime) ?></span>
                    <span>&bull;</span>
                <?php endif; ?>
                <span>📍 <?= e($event['location'] ?? 'Venue Master Control') ?></span>
                <span>&bull;</span>
                <span>Workstation Mesh Telemetry</span>
            </p>
        </div>
    </header>

    <!-- ═══ 4-Card Hardware Status Grid ═══ -->
    <section class="to-hardware-grid">
        <div class="to-hw-card">
            <div>
                <div class="to-hw-head">
                    <span class="to-hw-name">🔊 Sound &amp; PA System</span>
                    <span class="to-status-online">● ONLINE</span>
                </div>
                <p class="to-hw-desc">Main line arrays, stage monitors &amp; wireless mics</p>
            </div>
            <div class="to-hw-metric">
                Input Signal: <strong>-12 dB (Optimal)</strong> &bull; 4/4 Mics Live
            </div>
        </div>

        <div class="to-hw-card">
            <div>
                <div class="to-hw-head">
                    <span class="to-hw-name">💡 Stage Lighting &amp; DMX</span>
                    <span class="to-status-online">● ONLINE</span>
                </div>
                <p class="to-hw-desc">Stage follow-spots, beam fixtures &amp; ambient wash</p>
            </div>
            <div class="to-hw-metric">
                DMX Universe 1: <strong>Active</strong> &bull; Scene 04 (Main Stage)
            </div>
        </div>

        <div class="to-hw-card">
            <div>
                <div class="to-hw-head">
                    <span class="to-hw-name">🖥️ AV &amp; LED Video Wall</span>
                    <span class="to-status-online">● ONLINE</span>
                </div>
                <p class="to-hw-desc">Main stage backdrop screen &amp; live video feed</p>
            </div>
            <div class="to-hw-metric">
                Resolution: <strong>4K 60fps</strong> &bull; HDMI/SDI Sync OK
            </div>
        </div>

        <div class="to-hw-card">
            <div>
                <div class="to-hw-head">
                    <span class="to-hw-name">🌐 Workstation Mesh</span>
                    <span class="to-status-online">● <?= $pingMs ?>ms</span>
                </div>
                <p class="to-hw-desc">Gate scanner sync &amp; floor guide telemetry</p>
            </div>
            <div class="to-hw-metric">
                Packet Loss: <strong><?= $packetLoss ?></strong> &bull; Sync OK
            </div>
        </div>
    </section>

    <!-- ═══ Assigned Team Members & Workstation Devices Audit ═══ -->
    <section class="to-card">
        <div class="to-card-head">
            <div>
                <h2 class="to-card-title">
                    <span>👥</span> Assigned Event Team Members &amp; Workstations
                </h2>
                <p class="to-card-sub">Live connection status, devices, and check-in audit metrics per team member.</p>
            </div>
            <span class="to-card-count">
                <?= count($teamMembers) ?> Personnel
            </span>
        </div>

        <div class="to-table-wrap">
            <table class="to-table">
                <thead>
                    <tr>
                        <th>Staff Member</th>
                        <th>Assigned Role</th>
                        <th>Device &amp; Browser</th>
                        <th>Workstation IP</th>
                        <th style="text-align: center;">Scans / Action</th>
                        <th>Session Status</th>
                        <th style="text-align: right;">Last Active</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($teamMembers)): ?>
                        <tr>
                            <td colspan="7" class="to-empty-state">No staff members assigned to this event yet.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($teamMembers as $member):
                            $isOnline = ($member['status'] === 'online');
                        ?>
                        <tr>
                            <td>
                                <strong style="display: block; color: rgb(var(--text));"><?= e($member['name']) ?></strong>
                                <span style="font-size: 0.75rem; color: rgb(var(--muted));"><?= e($member['email']) ?></span>
                            </td>
                            <td>
                                <span class="to-role-tag">
                                    <?= e($member['role_name']) ?>
                                </span>
                            </td>
                            <td>
                                <span style="font-size: 0.82rem; color: rgb(var(--text));"><?= e($member['device']) ?></span>
                            </td>
                            <td>
                                <span class="to-ip-badge"><?= e($member['ip_address']) ?></span>
                            </td>
                            <td style="text-align: center;">
                                <strong style="color: rgb(var(--brand)); font-size: 0.95rem;"><?= (int)$member['scans_count'] ?></strong>
                            </td>
                            <td>
                                <span class="to-active-pill <?= $isOnline ? 'to-active-pill--online' : 'to-active-pill--offline' ?>">
                                    <span>●</span> <?= e($member['status_label']) ?>
                                </span>
                            </td>
                            <td style="text-align: right; font-size: 0.82rem; color: rgb(var(--muted));">
                                <?= e($member['last_login']) ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

</div>
@endsection