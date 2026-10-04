<?php

/**
 * Team Security Scanner Page
 *
 * Workstation task view for gate security and ticket scanners.
 * Implements real camera-based QR code scanning using html5-qrcode
 * with native photo capture fallback, manual code entry, and live log.
 * Optimized for high-speed scanning and outdoor visibility on mobile, tablet, and desktop.
 *
 * @package Evently
 * @subpackage Views/Teams
 * @author Your Name <Celio Natti>
 * @license MIT License
 * @version 2.2.0
 */

$checkedInCount = (int)($stats['checked_in'] ?? 0);
$totalTicketCount = (int)($stats['total_tickets'] ?? 0);
$pctScanned = $totalTicketCount > 0 ? min(100, round(($checkedInCount / $totalTicketCount) * 100, 1)) : 0;
?>

@extends('layouts/team')

@section('title')
Gate Security Scanner - <?= e($event['name'] ?? 'Evently') ?>
@endsection

@section('style')
<style>
/* ═══════════════════════════════════════════════════════════════════
   Security Scanner Scoped Component System (.sc-*)
   ═══════════════════════════════════════════════════════════════════ */
.sc-wrapper {
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
    max-width: 640px;
    margin: 0 auto;
    width: 100%;
}

/* ── Scanner Header ── */
.sc-header {
    background: rgb(var(--card));
    border: 1px solid rgba(var(--muted), 0.16);
    border-radius: 20px;
    padding: 1.25rem 1.4rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
    box-shadow: 0 4px 18px rgba(0, 0, 0, 0.03);
}

.sc-header-left {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
    min-width: 0;
}

.sc-header-title {
    margin: 0;
    font-size: clamp(1.15rem, 2.5vw, 1.4rem);
    font-weight: 800;
    color: rgb(var(--text));
    letter-spacing: -0.01em;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.sc-header-sub {
    margin: 0;
    font-size: 0.82rem;
    color: rgb(var(--muted));
    display: flex;
    align-items: center;
    gap: 0.35rem;
    flex-wrap: wrap;
}

.sc-staff-badge {
    color: rgb(var(--text));
    font-weight: 700;
}

.sc-header-status {
    display: flex;
    align-items: center;
    gap: 0.45rem;
    background: rgba(16, 185, 129, 0.1);
    border: 1px solid rgba(16, 185, 129, 0.25);
    padding: 0.35rem 0.75rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 700;
    color: #10b981;
    flex-shrink: 0;
}

.sc-pulse-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #10b981;
    animation: scPulse 1.8s infinite ease-in-out;
}

@keyframes scPulse {
    0%, 100% { opacity: 1; transform: scale(1); }
    50% { opacity: 0.4; transform: scale(0.8); }
}

/* ── Quick Stats Grid ── */
.sc-stats-card {
    background: rgb(var(--card));
    border: 1px solid rgba(var(--muted), 0.16);
    border-radius: 18px;
    padding: 1.15rem 1.35rem;
    display: flex;
    flex-direction: column;
    gap: 0.85rem;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.03);
}

.sc-stats-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

.sc-stat-col {
    display: flex;
    flex-direction: column;
    gap: 0.2rem;
}

.sc-stat-num {
    font-size: clamp(1.4rem, 3vw, 1.85rem);
    font-weight: 800;
    color: rgb(var(--text));
    line-height: 1.1;
}

.sc-stat-num.sc-green {
    color: #10b981;
}

.sc-stat-lbl {
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: rgb(var(--muted));
}

.sc-progress-wrap {
    display: flex;
    flex-direction: column;
    gap: 0.35rem;
}

.sc-progress-bar-bg {
    width: 100%;
    height: 8px;
    background: rgba(var(--muted), 0.14);
    border-radius: 9999px;
    overflow: hidden;
}

.sc-progress-bar-fill {
    height: 100%;
    background: linear-gradient(90deg, #10b981, #059669);
    border-radius: 9999px;
    transition: width 0.3s ease;
}

.sc-progress-meta {
    display: flex;
    justify-content: space-between;
    font-size: 0.72rem;
    font-weight: 700;
    color: rgb(var(--muted));
}

/* ── Result Alert Banner ── */
.sc-alert {
    display: none;
    padding: 1rem 1.25rem;
    border-radius: 16px;
    text-align: center;
    font-weight: 700;
    font-size: 0.95rem;
    line-height: 1.4;
    animation: scAlertPop 0.22s cubic-bezier(0.16, 1, 0.3, 1);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
}

.sc-alert.alert-success {
    background: rgba(16, 185, 129, 0.14);
    color: #059669;
    border: 2px solid #10b981;
}

.sc-alert.alert-error {
    background: rgba(239, 68, 68, 0.14);
    color: #dc2626;
    border: 2px solid #ef4444;
}

@keyframes scAlertPop {
    from { transform: scale(0.94); opacity: 0; }
    to { transform: scale(1); opacity: 1; }
}

/* ── Workstation Mode Toggle ── */
.sc-mode-nav {
    display: flex;
    background: rgba(var(--muted), 0.08);
    border-radius: 14px;
    padding: 4px;
    gap: 6px;
    border: 1px solid rgba(var(--muted), 0.12);
}

.sc-mode-btn {
    flex: 1;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.45rem;
    padding: 0.75rem 1rem;
    border-radius: 10px;
    border: none;
    background: transparent;
    color: rgb(var(--muted));
    font-size: 0.88rem;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.2s ease;
}

.sc-mode-btn:hover {
    color: rgb(var(--text));
}

.sc-mode-btn.active {
    background: rgb(var(--card));
    color: rgb(var(--text));
    box-shadow: 0 3px 10px rgba(0, 0, 0, 0.08);
}

/* ── Scanner Main Container ── */
.sc-scanner-card {
    background: rgb(var(--card));
    border: 1px solid rgba(var(--muted), 0.16);
    border-radius: 20px;
    padding: 1.5rem;
    box-shadow: 0 4px 18px rgba(0, 0, 0, 0.03);
}

/* Viewfinder Wrap */
.sc-viewfinder-wrap {
    position: relative;
    width: 100%;
    margin: 0 auto;
}

#qr-reader {
    width: 100%;
    border-radius: 18px;
    overflow: hidden;
    border: 2px solid rgba(var(--brand), 0.35);
    background: #09090b;
    min-height: 290px;
    position: relative;
}

#qr-reader video {
    border-radius: 16px;
    width: 100% !important;
    object-fit: cover;
}

#qr-reader__scan_region {
    min-height: 270px;
    background: #09090b;
}

#qr-reader__dashboard {
    padding: 0.5rem !important;
}

#qr-reader__dashboard_section_csr button,
#qr-reader__dashboard_section_swaplink {
    background: rgb(var(--brand)) !important;
    color: #ffffff !important;
    border: none !important;
    border-radius: 10px !important;
    padding: 0.55rem 1.1rem !important;
    font-weight: 700 !important;
    cursor: pointer !important;
    font-size: 0.85rem !important;
}

#qr-reader select {
    border-radius: 8px !important;
    padding: 0.4rem 0.6rem !important;
    border: 1px solid rgba(var(--muted), 0.25) !important;
    background: rgb(var(--card)) !important;
    color: rgb(var(--text)) !important;
    font-size: 0.85rem !important;
}

/* Camera Placeholder / Activation Box */
.sc-camera-placeholder {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    min-height: 290px;
    background: radial-gradient(circle at center, #18181b 0%, #09090b 100%);
    border-radius: 16px;
    color: #f4f4f5;
    text-align: center;
    padding: 2rem 1.5rem;
    box-sizing: border-box;
}

.sc-camera-icon {
    font-size: 2.8rem;
    margin-bottom: 0.75rem;
    opacity: 0.9;
}

.sc-camera-title {
    font-size: 1.15rem;
    font-weight: 800;
    margin: 0 0 0.35rem;
}

.sc-camera-desc {
    font-size: 0.84rem;
    color: #a1a1aa;
    margin: 0 0 1.25rem;
    max-width: 320px;
    line-height: 1.45;
}

.sc-btn-cam-start {
    background: linear-gradient(135deg, rgb(var(--brand)), rgba(var(--brand), 0.85));
    color: #ffffff;
    border: none;
    border-radius: 12px;
    padding: 0.75rem 1.6rem;
    font-weight: 800;
    font-size: 0.92rem;
    cursor: pointer;
    box-shadow: 0 6px 18px rgba(var(--brand), 0.35);
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    transition: transform 0.18s ease;
}

.sc-btn-cam-start:hover {
    transform: translateY(-1px);
}

/* Photo Upload / Camera Roll Fallback */
.sc-photo-actions {
    display: flex;
    justify-content: center;
    margin-top: 1rem;
}

.sc-btn-upload {
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    padding: 0.65rem 1.2rem;
    border-radius: 12px;
    font-size: 0.85rem;
    font-weight: 700;
    cursor: pointer;
    background: rgba(var(--muted), 0.08);
    border: 1px solid rgba(var(--muted), 0.18);
    color: rgb(var(--text));
    transition: background 0.18s ease;
}

.sc-btn-upload:hover {
    background: rgba(var(--muted), 0.15);
}

/* ── Manual Code Entry Form ── */
.sc-manual-box {
    display: none;
    flex-direction: column;
    gap: 1rem;
    text-align: center;
}

.sc-manual-box.active {
    display: flex;
}

.sc-manual-info {
    font-size: 0.85rem;
    color: rgb(var(--muted));
    margin: 0;
}

.sc-manual-input {
    width: 100%;
    box-sizing: border-box;
    border-radius: 14px;
    padding: 1.1rem 1rem;
    border: 2px solid rgba(var(--muted), 0.2);
    background: rgb(var(--bg));
    color: rgb(var(--text));
    font-size: 1.5rem !important; /* 16px+ prevents iOS Safari zoom */
    font-weight: 800;
    text-align: center;
    letter-spacing: 0.08em;
    outline: none;
    text-transform: uppercase;
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
}

.sc-manual-input:focus {
    border-color: rgb(var(--brand));
    box-shadow: 0 0 0 3px rgba(var(--brand), 0.15);
}

.sc-btn-verify {
    width: 100%;
    height: 52px;
    border-radius: 14px;
    background: rgb(var(--brand));
    color: #ffffff;
    font-size: 1rem;
    font-weight: 800;
    border: none;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    transition: opacity 0.18s ease;
}

.sc-btn-verify:hover {
    opacity: 0.92;
}

/* ── Scan History Log ── */
.sc-log-card {
    background: rgb(var(--card));
    border: 1px solid rgba(var(--muted), 0.16);
    border-radius: 20px;
    padding: 1.25rem 1.4rem;
    box-shadow: 0 4px 18px rgba(0, 0, 0, 0.03);
}

.sc-log-head {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.85rem;
    padding-bottom: 0.5rem;
    border-bottom: 1px solid rgba(var(--muted), 0.1);
}

.sc-log-title {
    margin: 0;
    font-size: 0.95rem;
    font-weight: 800;
    color: rgb(var(--text));
}

.sc-btn-clear-log {
    background: transparent;
    border: none;
    font-size: 0.75rem;
    color: rgb(var(--muted));
    font-weight: 600;
    cursor: pointer;
    text-decoration: underline;
}

.sc-log-list {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    max-height: 280px;
    overflow-y: auto;
}

.sc-log-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 0.75rem;
    padding: 0.7rem 0.9rem;
    border-radius: 12px;
    background: rgba(var(--muted), 0.04);
    border: 1px solid rgba(var(--muted), 0.08);
    font-size: 0.85rem;
    animation: scSlideIn 0.2s ease;
}

@keyframes scSlideIn {
    from { transform: translateY(-6px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}

.sc-log-item-left {
    display: flex;
    flex-direction: column;
    gap: 0.15rem;
    min-width: 0;
}

.sc-log-name {
    font-weight: 700;
    color: rgb(var(--text));
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.sc-log-sub {
    font-size: 0.72rem;
    color: rgb(var(--muted));
}

.sc-log-badge {
    padding: 0.22rem 0.55rem;
    border-radius: 6px;
    font-size: 0.72rem;
    font-weight: 800;
    flex-shrink: 0;
    letter-spacing: 0.03em;
}

.sc-log-in {
    background: rgba(16, 185, 129, 0.12);
    color: #10b981;
}

.sc-log-deny {
    background: rgba(239, 68, 68, 0.12);
    color: #ef4444;
}

/* ── Processing Overlay ── */
.sc-processing {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.65);
    backdrop-filter: blur(5px);
    -webkit-backdrop-filter: blur(5px);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 10000;
}

.sc-processing-modal {
    background: rgb(var(--card));
    color: rgb(var(--text));
    border-radius: 18px;
    padding: 2rem 2.5rem;
    text-align: center;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.35);
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 1rem;
}

.sc-spinner {
    width: 42px;
    height: 42px;
    border: 3px solid rgba(var(--muted), 0.25);
    border-top-color: rgb(var(--brand));
    border-radius: 50%;
    animation: scSpin 0.75s linear infinite;
}

@keyframes scSpin {
    to { transform: rotate(360deg); }
}

/* ═══════════════════════════════════════════════════════════════════
   RESPONSIVENESS (Desktop, Tablet, Mobile)
   ═══════════════════════════════════════════════════════════════════ */
@media (max-width: 480px) {
    .sc-header {
        padding: 1rem;
        border-radius: 16px;
    }
    .sc-header-title {
        font-size: 1.15rem;
    }
    .sc-scanner-card {
        padding: 1.15rem;
        border-radius: 16px;
    }
    .sc-stats-card {
        padding: 1rem;
        border-radius: 16px;
    }
    .sc-stat-num {
        font-size: 1.35rem;
    }
    .sc-manual-input {
        font-size: 1.25rem !important;
        padding: 0.95rem 0.75rem;
    }
    .sc-camera-placeholder {
        padding: 1.5rem 1rem;
        min-height: 250px;
    }
    .sc-camera-icon {
        font-size: 2.2rem;
    }
    .sc-btn-cam-start {
        width: 100%;
        justify-content: center;
        height: 46px;
    }
    .sc-btn-upload {
        width: 100%;
        justify-content: center;
        height: 44px;
        box-sizing: border-box;
    }
    .sc-log-card {
        padding: 1rem;
        border-radius: 16px;
    }
}
</style>
@endsection

@section('content')
<div class="sc-wrapper">
    <!-- Scanner Header Compact -->
    <header class="sc-header">
        <div class="sc-header-left">
            <h1 class="sc-header-title"><?= e($event['name'] ?? 'Gate Security Scanner') ?></h1>
            <p class="sc-header-sub">
                Staff: <span class="sc-staff-badge"><?= e($staff['name'] ?? 'Staff') ?></span>
                (<?= e($role['name'] ?? 'Gate Scanner') ?>)
            </p>
        </div>
        <div class="sc-header-status">
            <span class="sc-pulse-dot"></span>
            <span>Online</span>
        </div>
    </header>

    <!-- Quick Entry Stats & Attendance Velocity -->
    <section class="sc-stats-card">
        <div class="sc-stats-row">
            <div class="sc-stat-col">
                <span class="sc-stat-num sc-green" id="my-scan-count"><?= number_format($checkedInCount) ?></span>
                <span class="sc-stat-lbl">Checked-In</span>
            </div>
            <div class="sc-stat-col">
                <span class="sc-stat-num"><?= number_format($totalTicketCount) ?></span>
                <span class="sc-stat-lbl">Total Capacity</span>
            </div>
        </div>
        <div class="sc-progress-wrap">
            <div class="sc-progress-bar-bg">
                <div class="sc-progress-bar-fill" id="scan-progress-bar" style="width: <?= $pctScanned ?>%;"></div>
            </div>
            <div class="sc-progress-meta">
                <span>Gate Flow</span>
                <span id="scan-pct-lbl"><?= $pctScanned ?>% checked-in</span>
            </div>
        </div>
    </section>

    <!-- Scanner Alert Box -->
    <div id="scan-alert" class="sc-alert"></div>

    <!-- Scanner Module Card -->
    <section class="sc-scanner-card">
        <!-- Mode Toggle -->
        <nav class="sc-mode-nav">
            <button type="button" class="sc-mode-btn active" id="btn-mode-camera" onclick="switchMode('camera')">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/>
                    <circle cx="12" cy="13" r="4"/>
                </svg>
                Live Camera
            </button>
            <button type="button" class="sc-mode-btn" id="btn-mode-manual" onclick="switchMode('manual')">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="4 7 4 4 20 4 20 7"/>
                    <line x1="9" y1="20" x2="15" y2="20"/>
                    <line x1="12" y1="4" x2="12" y2="20"/>
                </svg>
                Manual Entry
            </button>
        </nav>

        <!-- Camera QR Scanner Mode -->
        <div id="camera-mode" style="margin-top:1.25rem;">
            <div class="sc-viewfinder-wrap">
                <div id="qr-reader">
                    <div class="sc-camera-placeholder" id="camera-init-box">
                        <div class="sc-camera-icon">📷</div>
                        <h2 class="sc-camera-title">Live QR Scanner</h2>
                        <p class="sc-camera-desc">Tap below to activate camera access and scan guest admission tickets automatically</p>
                        <button type="button" class="sc-btn-cam-start" onclick="startQrScanner()">
                            <span>📷</span> Activate Camera
                        </button>
                    </div>
                </div>
            </div>

            <!-- Snap Photo / Upload Image Fallback -->
            <div class="sc-photo-actions">
                <label class="sc-btn-upload">
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                        <circle cx="8.5" cy="8.5" r="1.5"/>
                        <polyline points="21 15 16 10 5 21"/>
                    </svg>
                    Snap Photo / Upload QR Image
                    <input type="file" id="qr-file-input" accept="image/*" capture="environment" onchange="scanQrFromPhoto(this)" style="display:none;">
                </label>
            </div>
        </div>

        <!-- Manual Code Entry Mode -->
        <div id="manual-mode" class="sc-manual-box" style="margin-top:1.25rem;">
            <p class="sc-manual-info">
                Type the ticket pass code printed on the attendee ticket or digital pass
            </p>
            <form id="sec-manual-form" onsubmit="verifyManualCode(event)">
                <input id="sec-code-input" name="code" type="text"
                    class="sc-manual-input"
                    placeholder="e.g. EV-1001"
                    required autocomplete="off"
                    aria-label="Ticket code input" />
                <button type="submit" class="sc-btn-verify" style="margin-top: 0.85rem;">
                    <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20 6 9 17 4 12"/>
                    </svg>
                    Verify &amp; Admit Guest
                </button>
            </form>
        </div>
    </section>

    <!-- Scan History Log -->
    <section class="sc-log-card">
        <div class="sc-log-head">
            <h2 class="sc-log-title">Live Gate Scan History</h2>
            <button type="button" class="sc-btn-clear-log" onclick="clearScanLog()">Clear Log</button>
        </div>
        <div class="sc-log-list" id="scan-log-container">
            <div class="sc-log-item">
                <div class="sc-log-item-left">
                    <span class="sc-log-name">Workstation Scanner Active</span>
                    <span class="sc-log-sub">Ready to verify admissions • <?= date('H:i') ?></span>
                </div>
                <span class="sc-log-badge sc-log-in">READY</span>
            </div>
        </div>
    </section>

    <!-- Processing Overlay (hidden) -->
    <div id="scan-processing" class="sc-processing" style="display:none;">
        <div class="sc-processing-modal">
            <div class="sc-spinner"></div>
            <div style="font-weight:700; font-size:0.95rem;">Verifying admission ticket...</div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
// ── CSRF Token & Routes ──
var CSRF_TOKEN = '<?= csrf_token() ?>';
var VERIFY_URL = '<?= route("/teams/verify-ticket") ?>';
var TOTAL_CAPACITY = <?= (int)($stats['total_tickets'] ?? 0) ?>;

// ── State ──
var html5QrcodeScanner = null;
var isProcessing = false;
var lastScannedCode = '';
var lastScanTime = 0;

// ── Mode Switching ──
function switchMode(mode) {
    var cameraMode = document.getElementById('camera-mode');
    var manualMode = document.getElementById('manual-mode');
    var btnCamera = document.getElementById('btn-mode-camera');
    var btnManual = document.getElementById('btn-mode-manual');

    if (mode === 'manual') {
        cameraMode.style.display = 'none';
        manualMode.style.display = 'flex';
        manualMode.classList.add('active');
        btnCamera.classList.remove('active');
        btnManual.classList.add('active');
        stopQrScanner();
        var input = document.getElementById('sec-code-input');
        if (input) setTimeout(function() { input.focus(); }, 150);
    } else {
        cameraMode.style.display = 'block';
        manualMode.style.display = 'none';
        manualMode.classList.remove('active');
        btnCamera.classList.add('active');
        btnManual.classList.remove('active');
    }
}

// ── Live Camera QR Scanner ──
function startQrScanner() {
    var readerEl = document.getElementById('qr-reader');
    if (!readerEl) return;

    if (typeof Html5Qrcode === 'undefined') {
        renderCameraError('QR Scanner library is loading or unavailable. Switch to Manual Entry.');
        return;
    }

    var isSecure = window.isSecureContext || window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1';

    if (html5QrcodeScanner) {
        stopQrScanner();
    }

    html5QrcodeScanner = new Html5Qrcode("qr-reader");

    var config = {
        fps: 10,
        qrbox: { width: 240, height: 240 },
        aspectRatio: 1.0
    };

    // Try back camera first
    html5QrcodeScanner.start(
        { facingMode: { exact: "environment" } },
        config,
        onQrCodeScanned,
        function(err) { /* ignore frame scan errors */ }
    ).catch(function(err1) {
        // Fallback to user camera
        html5QrcodeScanner.start(
            { facingMode: "user" },
            config,
            onQrCodeScanned,
            function(err) { /* ignore frame scan errors */ }
        ).catch(function(err2) {
            console.warn('Camera start error:', err2);
            var reason = 'Camera permission denied or camera not found.';
            if (!isSecure) {
                reason = 'Browser blocks live camera over unsecure HTTP. Use HTTPS or the "Snap Photo / Upload QR Image" option below.';
            }
            renderCameraError(reason);
        });
    });
}

function stopQrScanner() {
    if (html5QrcodeScanner) {
        try {
            html5QrcodeScanner.stop().then(function() {
                html5QrcodeScanner.clear();
                html5QrcodeScanner = null;
            }).catch(function() {
                html5QrcodeScanner = null;
            });
        } catch(e) {
            html5QrcodeScanner = null;
        }
    }
}

function renderCameraError(msg) {
    var readerEl = document.getElementById('qr-reader');
    if (!readerEl) return;

    readerEl.innerHTML = '<div class="sc-camera-placeholder">' +
        '<div style="font-size:2.5rem; margin-bottom:0.5rem; color:#ef4444;">⚠️</div>' +
        '<strong style="color:#ef4444; font-size:1rem; margin-bottom:0.35rem;">Camera Access Notice</strong>' +
        '<p class="sc-camera-desc">' + escapeHtml(msg) + '</p>' +
        '<div style="display:flex; gap:0.6rem; flex-wrap:wrap; justify-content:center;">' +
        '<button type="button" class="sc-btn-cam-start" onclick="startQrScanner()" style="padding:0.5rem 1.1rem; font-size:0.85rem;">Retry Camera</button>' +
        '<button type="button" class="sc-btn-upload" onclick="switchMode(\'manual\')">Manual Entry</button>' +
        '</div></div>';
}

function onQrCodeScanned(decodedText) {
    var now = Date.now();
    if (decodedText === lastScannedCode && (now - lastScanTime) < 3000) return;
    lastScannedCode = decodedText;
    lastScanTime = now;

    performCheckIn(decodedText.trim());
}

// ── Scan QR Code from Photo / File Capture ──
function scanQrFromPhoto(input) {
    if (!input.files || input.files.length === 0) return;

    var file = input.files[0];
    if (typeof Html5Qrcode === 'undefined') {
        alert('QR library not ready.');
        return;
    }

    var processingOverlay = document.getElementById('scan-processing');
    if (processingOverlay) processingOverlay.style.display = 'flex';

    var tempScanner = new Html5Qrcode("qr-reader");
    tempScanner.scanFile(file, true)
        .then(function(decodedText) {
            if (processingOverlay) processingOverlay.style.display = 'none';
            performCheckIn(decodedText.trim());
            input.value = '';
        })
        .catch(function(err) {
            if (processingOverlay) processingOverlay.style.display = 'none';
            var alertBox = document.getElementById('scan-alert');
            if (alertBox) {
                alertBox.style.display = 'block';
                alertBox.className = 'sc-alert alert-error';
                alertBox.textContent = 'Could not detect a valid QR code in that photo. Please try again or type the ticket code manually.';
                setTimeout(function() { alertBox.style.display = 'none'; }, 4000);
            }
            input.value = '';
        });
}

// ── Manual Code Entry ──
function verifyManualCode(e) {
    e.preventDefault();
    var input = document.getElementById('sec-code-input');
    var code = input ? input.value.trim() : '';
    if (!code) return;

    performCheckIn(code);
    if (input) input.value = '';
}

// ── Core Check-In API Request ──
function performCheckIn(code) {
    if (isProcessing) return;
    isProcessing = true;

    var alertBox = document.getElementById('scan-alert');
    var logContainer = document.getElementById('scan-log-container');
    var countEl = document.getElementById('my-scan-count');
    var barFill = document.getElementById('scan-progress-bar');
    var pctLbl = document.getElementById('scan-pct-lbl');
    var processingOverlay = document.getElementById('scan-processing');

    if (processingOverlay) processingOverlay.style.display = 'flex';

    fetch(VERIFY_URL, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': CSRF_TOKEN
        },
        body: 'code=' + encodeURIComponent(code) + '&csrf_token=' + encodeURIComponent(CSRF_TOKEN)
    })
    .then(function(res) { return res.json(); })
    .then(function(data) {
        if (processingOverlay) processingOverlay.style.display = 'none';

        if (!alertBox) { isProcessing = false; return; }

        alertBox.style.display = 'block';
        alertBox.className = 'sc-alert ' + (data.success ? 'alert-success' : 'alert-error');
        alertBox.textContent = data.message;

        if (data.success) {
            if (navigator.vibrate) navigator.vibrate([100, 50, 100]);
            playTone(880, 180);

            if (countEl) {
                var current = parseInt(countEl.textContent.replace(/,/g, '') || '0') || 0;
                var nextCount = current + 1;
                countEl.textContent = formatNumber(nextCount);
                if (TOTAL_CAPACITY > 0 && barFill && pctLbl) {
                    var newPct = Math.min(100, Math.round((nextCount / TOTAL_CAPACITY) * 100));
                    barFill.style.width = newPct + '%';
                    pctLbl.textContent = newPct + '% checked-in';
                }
            }

            if (logContainer && data.ticket) {
                var logHtml = '<div class="sc-log-item">' +
                    '<div class="sc-log-item-left"><span class="sc-log-name">' + escapeHtml(data.ticket.attendee_name) + '</span>' +
                    '<span class="sc-log-sub">#' + escapeHtml(String(data.ticket.id)) + ' • ' + escapeHtml(data.ticket.ticket_type_name) + ' • ' + new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit', second:'2-digit'}) + '</span></div>' +
                    '<span class="sc-log-badge sc-log-in">✓ ADMIT</span></div>';
                logContainer.insertAdjacentHTML('afterbegin', logHtml);
            }
        } else {
            if (navigator.vibrate) navigator.vibrate(300);
            playTone(280, 280);

            if (logContainer) {
                var errHtml = '<div class="sc-log-item">' +
                    '<div class="sc-log-item-left"><span class="sc-log-name">Code: ' + escapeHtml(code) + '</span>' +
                    '<span class="sc-log-sub">' + escapeHtml(data.message) + ' • ' + new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit', second:'2-digit'}) + '</span></div>' +
                    '<span class="sc-log-badge sc-log-deny">✗ DENY</span></div>';
                logContainer.insertAdjacentHTML('afterbegin', errHtml);
            }
        }

        setTimeout(function() {
            alertBox.style.display = 'none';
        }, 4000);

        isProcessing = false;
    })
    .catch(function(err) {
        console.error('Scan error:', err);
        if (processingOverlay) processingOverlay.style.display = 'none';

        if (alertBox) {
            alertBox.style.display = 'block';
            alertBox.className = 'sc-alert alert-error';
            alertBox.textContent = 'Network error. Please check your connection and try again.';
            setTimeout(function() { alertBox.style.display = 'none'; }, 4000);
        }

        isProcessing = false;
    });
}

// ── Audio Feedback ──
function playTone(frequency, duration) {
    try {
        var audioCtx = new (window.AudioContext || window.webkitAudioContext)();
        var oscillator = audioCtx.createOscillator();
        var gainNode = audioCtx.createGain();
        oscillator.connect(gainNode);
        gainNode.connect(audioCtx.destination);
        oscillator.frequency.value = frequency;
        oscillator.type = 'sine';
        gainNode.gain.value = 0.15;
        oscillator.start();
        setTimeout(function() {
            oscillator.stop();
            audioCtx.close();
        }, duration);
    } catch(e) {}
}

// ── Utilities ──
function escapeHtml(str) {
    if (!str) return '';
    return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

function formatNumber(n) {
    return n.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
}

function clearScanLog() {
    var logContainer = document.getElementById('scan-log-container');
    if (logContainer) {
        logContainer.innerHTML = '<div class="sc-log-item">' +
            '<div class="sc-log-item-left"><span class="sc-log-name">Log Cleared</span>' +
            '<span class="sc-log-sub">Ready for scanning • ' + new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}) + '</span></div>' +
            '<span class="sc-log-badge sc-log-in">READY</span></div>';
    }
}

// ── Auto-start camera if secure ──
document.addEventListener('DOMContentLoaded', function() {
    var isSecure = window.isSecureContext || window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1';
    if (isSecure) {
        startQrScanner();
    }
});

// ── Cleanup ──
window.addEventListener('beforeunload', function() {
    stopQrScanner();
});
</script>
@endsection