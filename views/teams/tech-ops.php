<?php

/**
 * Team Tech Ops Page
 *
 * Workstation task view for Technical Operations to manage:
 * - Sound systems, stage lighting, AV equipment & stage hardware
 * - Gate scanner telemetry, latency, and team workstation audits
 *
 * @package Evently
 * @subpackage Views/Teams
 * @author Celio Natti
 * @version 2.3.0
 */

$eventTime = !empty($event['date']) ? strtotime($event['date']) : null;
?>

@extends('layouts/team')

@section('title')
Technical Operations (Sound, Stage & AV) - <?= e($event['name'] ?? 'Evently') ?>
@endsection

@section('style')
<style>
.tech-hardware-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1rem;
    margin-bottom: 1.5rem;
}
.tech-hw-card {
    background: rgb(var(--card));
    border: 1px solid rgba(var(--muted), 0.14);
    border-radius: 18px;
    padding: 1.25rem;
    box-sizing: border-box;
}

@media (max-width: 1024px) {
    .tech-hardware-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 768px) {
    .page-hero { 
        flex-direction: column; 
        align-items: stretch; 
        gap: 0.75rem; 
    }
    .tech-hardware-grid {
        grid-template-columns: 1fr 1fr;
        gap: 0.65rem;
    }
    .tech-hw-card {
        padding: 1rem;
    }
}

@media (max-width: 480px) {
    .tech-hardware-grid {
        grid-template-columns: 1fr;
    }
}
</style>
@endsection

@section('content')
<section class="page-hero" style="margin-bottom:1.5rem;">
    <div>
        <span class="panel-badge" style="background:rgba(16, 185, 129, 0.14); color:#10b981; border:1px solid rgba(16, 185, 129, 0.25); font-weight:700;">
            🛠️ Workstation — Technical Operations
        </span>
        <h1 style="font-size:clamp(1.35rem, 3vw, 1.85rem); margin:0.25rem 0; color:rgb(var(--text));">Sound, Stage Lighting &amp; AV Operations</h1>
        <p class="lede" style="margin:0; font-size:0.92rem; color:rgb(var(--muted));">
            <?php if ($eventTime): ?>
                <span>📅 <?= date('D, M d, Y • g:i A', $eventTime) ?></span> •
            <?php endif; ?>
            <span>📍 <?= e($event['location'] ?? 'Venue') ?></span> •
            <span>Manage sound systems, lighting, stage hardware &amp; workstation mesh telemetry</span>
        </p>
    </div>
</section>

<!-- Sound, Lighting & Stage Hardware Grid -->
<div class="tech-hardware-grid">
    <div class="tech-hw-card">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.6rem;">
            <strong style="font-size:0.95rem; color:rgb(var(--text));">🔊 Sound &amp; PA System</strong>
            <span style="font-size:0.75rem; font-weight:800; background:rgba(16,185,129,0.12); color:#059669; padding:2px 8px; border-radius:6px;">● ONLINE</span>
        </div>
        <p style="font-size:0.8rem; color:rgb(var(--muted)); margin:0 0 0.75rem;">Main line arrays, stage monitors &amp; wireless mics</p>
        <div style="font-size:0.75rem; color:rgb(var(--muted)); background:rgb(var(--bg)); padding:0.4rem 0.6rem; border-radius:8px; border:1px solid rgba(var(--muted),0.1);">
            Input Signal: <strong style="color:rgb(var(--text));">-12 dB (Optimal)</strong> • 4/4 Mics Live
        </div>
    </div>

    <div class="tech-hw-card">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.6rem;">
            <strong style="font-size:0.95rem; color:rgb(var(--text));">💡 Stage Lighting &amp; DMX</strong>
            <span style="font-size:0.75rem; font-weight:800; background:rgba(16,185,129,0.12); color:#059669; padding:2px 8px; border-radius:6px;">● ONLINE</span>
        </div>
        <p style="font-size:0.8rem; color:rgb(var(--muted)); margin:0 0 0.75rem;">Stage follow-spots, beam fixtures &amp; ambient wash</p>
        <div style="font-size:0.75rem; color:rgb(var(--muted)); background:rgb(var(--bg)); padding:0.4rem 0.6rem; border-radius:8px; border:1px solid rgba(var(--muted),0.1);">
            DMX Universe 1: <strong style="color:rgb(var(--text));">Active</strong> • Scene 04 (Main Stage)
        </div>
    </div>

    <div class="tech-hw-card">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.6rem;">
            <strong style="font-size:0.95rem; color:rgb(var(--text));">🖥️ AV &amp; LED Video Wall</strong>
            <span style="font-size:0.75rem; font-weight:800; background:rgba(16,185,129,0.12); color:#059669; padding:2px 8px; border-radius:6px;">● ONLINE</span>
        </div>
        <p style="font-size:0.8rem; color:rgb(var(--muted)); margin:0 0 0.75rem;">Main stage backdrop screen &amp; live video feed</p>
        <div style="font-size:0.75rem; color:rgb(var(--muted)); background:rgb(var(--bg)); padding:0.4rem 0.6rem; border-radius:8px; border:1px solid rgba(var(--muted),0.1);">
            Resolution: <strong style="color:rgb(var(--text));">4K 60fps</strong> • HDMI/SDI Sync OK
        </div>
    </div>

    <div class="tech-hw-card">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.6rem;">
            <strong style="font-size:0.95rem; color:rgb(var(--text));">🌐 Workstation Mesh</strong>
            <span style="font-size:0.75rem; font-weight:800; background:rgba(16,185,129,0.12); color:#059669; padding:2px 8px; border-radius:6px;">● <?= e($network['ping_ms'] ?? '16') ?>ms</span>
        </div>
        <p style="font-size:0.8rem; color:rgb(var(--muted)); margin:0 0 0.75rem;">Gate scanner sync &amp; floor guide telemetry</p>
        <div style="font-size:0.75rem; color:rgb(var(--muted)); background:rgb(var(--bg)); padding:0.4rem 0.6rem; border-radius:8px; border:1px solid rgba(var(--muted),0.1);">
            Packet Loss: <strong style="color:rgb(var(--text));"><?= e($network['packet_loss'] ?? '0.0%') ?></strong> • Sync OK
        </div>
    </div>
</div>

<!-- Team Members & Devices Audit Table -->
<section class="form-card">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.25rem; border-bottom:1px solid rgba(var(--muted),0.1); padding-bottom:0.75rem; flex-wrap:wrap; gap:0.5rem;">
        <div>
            <h2 style="margin:0; font-size:1.15rem; font-weight:700; color:rgb(var(--text));">Assigned Event Team Members &amp; Workstations</h2>
            <p class="section-note" style="margin:0.25rem 0 0; font-size:0.82rem; color:rgb(var(--muted));">Live connection status, devices, and check-in audit metrics per team member.</p>
        </div>
        <span style="font-size:0.82rem; color:rgb(var(--muted)); font-weight:700; background:rgba(var(--muted),0.06); padding:0.25rem 0.65rem; border-radius:20px;">
            <?= count($team_members ?? []) ?> Member(s)
        </span>
    </div>

    <div style="width:100%; overflow-x:auto; -webkit-overflow-scrolling:touch;">
        <table class="au-table" style="width:100%; min-width:620px;">
            <thead>
                <tr>
                    <th>Staff Member</th>
                    <th>Assigned Role</th>
                    <th>Device &amp; Browser</th>
                    <th>Workstation IP</th>
                    <th style="text-align:center;">Scans / Action</th>
                    <th>Session Status</th>
                    <th style="text-align:right;">Last Active</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($team_members)): ?>
                    <tr><td colspan="7" style="padding:2.5rem; text-align:center; color:rgb(var(--muted));">No staff members assigned to this event yet.</td></tr>
                <?php else: ?>
                    <?php foreach ($team_members as $member):
                        $isOnline = ($member['status'] === 'online');
                        $roleSlug = strtolower($member['role_slug'] ?? '');
                        
                        $badgeClass = 'au-role--host';
                        if (str_contains($roleSlug, 'scanner') || str_contains($roleSlug, 'security')) {
                            $badgeClass = 'au-role--admin';
                        } elseif (str_contains($roleSlug, 'support') || str_contains($roleSlug, 'customer')) {
                            $badgeClass = 'au-role--attendee';
                        } elseif (str_contains($roleSlug, 'finance')) {
                            $badgeClass = 'au-status--active';
                        } elseif (str_contains($roleSlug, 'usher') || str_contains($roleSlug, 'floor')) {
                            $badgeClass = 'au-role--attendee';
                        }
                    ?>
                    <tr>
                        <td>
                            <strong style="color:rgb(var(--text));"><?= e($member['name']) ?></strong>
                            <div style="font-size:0.75rem; color:rgb(var(--muted));"><?= e($member['email']) ?></div>
                        </td>
                        <td>
                            <span class="role-indicator <?= $badgeClass ?>" style="font-size:0.75rem;">
                                <?= e($member['role_name']) ?>
                            </span>
                        </td>
                        <td>
                            <span style="font-size:0.82rem; color:rgb(var(--text));"><?= e($member['device']) ?></span>
                        </td>
                        <td>
                            <code style="font-size:0.8rem; background:rgba(var(--muted),0.08); padding:2px 6px; border-radius:4px; color:rgb(var(--text));"><?= e($member['ip_address']) ?></code>
                        </td>
                        <td style="text-align:center;">
                            <span style="font-weight:700; color:rgb(var(--brand));"><?= (int)$member['scans_count'] ?></span>
                        </td>
                        <td>
                            <span class="status-indicator <?= $isOnline ? 'status-indicator--active' : 'status-indicator--inactive' ?>" style="font-size:0.75rem;">
                                <?= e($member['status_label']) ?>
                            </span>
                        </td>
                        <td style="text-align:right; font-size:0.8rem; color:rgb(var(--muted));">
                            <?= e($member['last_login']) ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>
@endsection