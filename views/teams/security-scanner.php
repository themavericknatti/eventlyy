<?php

/**
 * Team Security Scanner Page
 *
 * Workstation task view for gate security and ticket scanners.
 * Implements real camera-based QR code scanning using html5-qrcode
 * with native photo capture fallback and manual code entry.
 *
 * @package Evently
 * @subpackage Views/Teams
 * @author Your Name <Celio Natti>
 * @license MIT License
 * @version 2.1.0
 */

?>

@extends('layouts/team')

@section('style')
<style>
    .scanner-header-compact {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
        border-bottom: 1px solid rgba(var(--muted), 0.12);
        padding-bottom: 0.75rem;
    }

    .sec-stats-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0.75rem;
        margin-bottom: 1.25rem;
    }

    .sec-stat-box {
        padding: 0.85rem;
        border-radius: 12px;
        background: rgba(var(--muted), 0.05);
        text-align: center;
        border: 1px solid rgba(var(--muted), 0.08);
    }

    .sec-stat-box .num {
        display: block;
        font-size: 1.4rem;
        font-weight: 700;
        color: rgb(var(--text));
    }

    .sec-stat-box .lbl {
        font-size: 0.75rem;
        color: rgb(var(--muted));
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    /* Scan Alert Result */
    .scanner-badge-alert {
        padding: 1rem;
        border-radius: 14px;
        text-align: center;
        margin-bottom: 1rem;
        font-weight: 600;
        animation: alertPopup 200ms ease;
        display: none;
    }

    .scanner-badge-alert.alert-success {
        background: rgba(16, 185, 129, 0.12);
        color: #10b981;
        border: 1px solid rgba(16, 185, 129, 0.25);
    }

    .scanner-badge-alert.alert-error {
        background: rgba(239, 68, 68, 0.12);
        color: #ef4444;
        border: 1px solid rgba(239, 68, 68, 0.25);
    }

    @keyframes alertPopup {
        0% { transform: scale(0.95); opacity: 0; }
        100% { transform: scale(1); opacity: 1; }
    }

    /* Mode Toggle */
    .scan-mode-toggle {
        display: flex;
        background: rgba(var(--muted), 0.08);
        border-radius: 12px;
        padding: 4px;
        gap: 4px;
        margin-bottom: 1rem;
    }

    .scan-mode-btn {
        flex: 1;
        padding: 0.6rem 0.75rem;
        border: none;
        border-radius: 10px;
        background: transparent;
        color: rgb(var(--muted));
        font-size: 0.85rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.25s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.4rem;
    }

    .scan-mode-btn.active {
        background: rgb(var(--card-rgb, 255, 255, 255));
        color: rgb(var(--text));
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    }

    /* Camera Viewfinder */
    .scanner-viewfinder-wrap {
        position: relative;
        width: 100%;
        max-width: 380px;
        margin: 0 auto 0.75rem;
    }

    #qr-reader {
        width: 100%;
        border-radius: 16px;
        overflow: hidden;
        border: 2px solid rgba(var(--brand), 0.3);
        background: #0a0a0a;
        min-height: 280px;
    }

    #qr-reader video {
        border-radius: 14px;
        width: 100% !important;
    }

    /* Override html5-qrcode default styles */
    #qr-reader__scan_region {
        min-height: 260px;
        background: #0a0a0a;
    }

    #qr-reader__dashboard {
        padding: 0.5rem !important;
    }

    #qr-reader__dashboard_section {
        padding: 0.5rem !important;
    }

    #qr-reader__dashboard_section_csr button,
    #qr-reader__dashboard_section_swaplink {
        background: var(--brand) !important;
        color: #fff !important;
        border: none !important;
        border-radius: 8px !important;
        padding: 0.5rem 1rem !important;
        font-weight: 600 !important;
        cursor: pointer !important;
        font-size: 0.85rem !important;
    }

    #qr-reader select {
        border-radius: 8px !important;
        padding: 0.4rem 0.6rem !important;
        border: 1px solid rgba(var(--muted), 0.2) !important;
        background: rgb(var(--card-rgb, 255, 255, 255)) !important;
        color: rgb(var(--text)) !important;
    }

    .camera-placeholder {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        min-height: 280px;
        background: #0a0a0a;
        border-radius: 16px;
        border: 2px dashed rgba(var(--brand), 0.35);
        color: rgba(255, 255, 255, 0.8);
        text-align: center;
        padding: 1.5rem;
    }

    .camera-placeholder svg {
        opacity: 0.5;
        margin-bottom: 0.75rem;
    }

    .camera-placeholder p {
        font-size: 0.82rem;
        margin: 0.35rem 0 1rem;
        opacity: 0.7;
        line-height: 1.4;
    }

    /* Manual entry section */
    .manual-entry-section {
        display: none;
    }

    .manual-entry-section.active {
        display: block;
    }

    .manual-big-input {
        text-align: center;
        font-size: 1.5rem !important;
        letter-spacing: 0.08em !important;
        padding: 1rem !important;
    }

    /* Photo Scan Action Buttons */
    .photo-scan-actions {
        display: flex;
        gap: 0.5rem;
        justify-content: center;
        margin-top: 0.75rem;
    }

    /* Scan Log */
    .log-list {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
        max-height: 300px;
        overflow-y: auto;
    }

    .log-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.65rem 0.85rem;
        border-radius: 10px;
        background: rgba(var(--muted), 0.03);
        border: 1px solid rgba(var(--muted), 0.06);
        font-size: 0.85rem;
        animation: slideIn 0.2s ease;
    }

    @keyframes slideIn {
        0% { transform: translateY(-8px); opacity: 0; }
        100% { transform: translateY(0); opacity: 1; }
    }

    .log-item .log-status {
        font-weight: 700;
        font-size: 0.75rem;
        padding: 0.2rem 0.5rem;
        border-radius: 6px;
        flex-shrink: 0;
    }

    .log-status-success {
        color: #10b981;
        background: rgba(16, 185, 129, 0.1);
    }

    .log-status-error {
        color: #ef4444;
        background: rgba(239, 68, 68, 0.1);
    }

    /* Processing overlay */
    .scan-processing {
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.6);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 500;
        backdrop-filter: blur(4px);
    }

    .scan-processing-inner {
        background: rgb(var(--card-rgb, 255, 255, 255));
        border-radius: 16px;
        padding: 2rem 2.5rem;
        text-align: center;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
    }

    .scan-processing-inner .spinner {
        width: 40px;
        height: 40px;
        border: 3px solid rgba(var(--muted), 0.2);
        border-top-color: rgb(var(--brand));
        border-radius: 50%;
        animation: spin 0.8s linear infinite;
        margin: 0 auto 1rem;
    }

    @keyframes spin {
        to { transform: rotate(360deg); }
    }

    /* ═══════════════════════════════════════════════════════════════════
       Mobile Responsive (≤ 768px)
       ═══════════════════════════════════════════════════════════════════ */
    @media (max-width: 768px) {
        .scanner-header-compact {
            flex-wrap: wrap;
            gap: 0.5rem;
        }
        .scanner-header-compact > div:first-child {
            flex: 1;
            min-width: 0;
        }
        .scanner-header-compact h2 {
            font-size: 1.1rem;
            word-break: break-word;
        }
        .sec-stats-row {
            gap: 0.5rem;
        }
        .sec-stat-box {
            padding: 0.65rem;
        }
        .sec-stat-box .num {
            font-size: 1.2rem;
        }
        .sec-stat-box .lbl {
            font-size: 0.68rem;
        }
        .scan-mode-btn {
            font-size: 0.8rem;
            padding: 0.5rem 0.5rem;
            gap: 0.25rem;
        }
        .scanner-viewfinder-wrap {
            max-width: 100%;
        }
        #qr-reader {
            min-height: 240px;
            border-radius: 12px;
        }
        .camera-placeholder {
            min-height: 220px;
            padding: 1rem;
            border-radius: 12px;
        }
        .manual-big-input {
            font-size: 1.15rem !important;
            letter-spacing: 0.05em !important;
            padding: 0.85rem !important;
        }
        .photo-scan-actions {
            flex-direction: column;
        }
        .photo-scan-actions .admin-action-btn {
            width: 100%;
            justify-content: center;
            box-sizing: border-box;
        }
        .log-item {
            padding: 0.5rem 0.65rem;
            font-size: 0.8rem;
            border-radius: 8px;
            gap: 0.35rem;
        }
        .log-item .log-status {
            font-size: 0.68rem;
            padding: 0.15rem 0.4rem;
        }
        .scan-processing-inner {
            padding: 1.5rem;
            margin: 0 1rem;
            border-radius: 14px;
        }
        .form-card {
            padding: 1rem;
            border-radius: 14px;
        }
    }

    /* ═══════════════════════════════════════════════════════════════════
       Small Phone (≤ 400px)
       ═══════════════════════════════════════════════════════════════════ */
    @media (max-width: 400px) {
        .scanner-header-compact h2 {
            font-size: 1rem;
        }
        .sec-stats-row {
            grid-template-columns: 1fr;
            gap: 0.4rem;
        }
        .sec-stat-box {
            padding: 0.55rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            text-align: left;
        }
        .sec-stat-box .num {
            font-size: 1.1rem;
            order: 2;
        }
        .sec-stat-box .lbl {
            order: 1;
        }
        .scan-mode-btn {
            font-size: 0.75rem;
            padding: 0.45rem 0.4rem;
        }
        .manual-big-input {
            font-size: 1rem !important;
            padding: 0.75rem !important;
        }
        .form-card {
            padding: 0.75rem;
            border-radius: 12px;
        }
        .log-list {
            max-height: 220px;
        }
    }
</style>
@endsection

@section('title')
Gate Security Scanner - <?= e($event['name'] ?? 'Evently') ?>
@endsection

@section('content')
<!-- Scanner Header Compact -->
<div class="scanner-header-compact">
    <div>
        <h1 style="font-size:1.25rem; margin:0;"><?= e($event['name'] ?? 'Gate Security Scanner') ?></h1>
        <p style="font-size:0.82rem; color:rgb(var(--muted)); margin:2px 0 0;">
            Assigned: <strong><?= e($staff['name'] ?? 'Staff') ?></strong> (<?= e($role['name'] ?? 'Scanner') ?>)
        </p>
    </div>
    <div style="display:flex; align-items:center; gap:0.5rem;">
        <span id="connection-dot" style="width:8px; height:8px; border-radius:50%; background:#10b981; display:inline-block;"></span>
        <span style="font-size:0.75rem; color:rgb(var(--muted));">Online</span>
    </div>
</div>

<!-- Quick Entry Stats -->
<div class="sec-stats-row">
    <div class="sec-stat-box">
        <span class="num" id="my-scan-count"><?= number_format($stats['checked_in'] ?? 0) ?></span>
        <span class="lbl">Checked-in</span>
    </div>
    <div class="sec-stat-box">
        <span class="num"><?= number_format($stats['checked_in'] ?? 0) ?> / <?= number_format($stats['total_tickets'] ?? 0) ?></span>
        <span class="lbl">Total Capacity</span>
    </div>
</div>

<!-- Scanner Alert Box -->
<div id="scan-alert" class="scanner-badge-alert"></div>

<!-- Scanner Section -->
<section class="form-card" style="padding: 1.25rem;">
    <!-- Mode Toggle -->
    <div class="scan-mode-toggle">
        <button type="button" class="scan-mode-btn active" id="btn-mode-camera" onclick="switchMode('camera')">
            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/>
                <circle cx="12" cy="13" r="4"/>
            </svg>
            Live Camera Scan
        </button>
        <button type="button" class="scan-mode-btn" id="btn-mode-manual" onclick="switchMode('manual')">
            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="4 7 4 4 20 4 20 7"/>
                <line x1="9" y1="20" x2="15" y2="20"/>
                <line x1="12" y1="4" x2="12" y2="20"/>
            </svg>
            Manual Entry
        </button>
    </div>

    <!-- Camera QR Scanner Mode -->
    <div id="camera-mode">
        <div class="scanner-viewfinder-wrap">
            <div id="qr-reader">
                <div class="camera-placeholder" id="camera-init-box">
                    <svg viewBox="0 0 24 24" width="48" height="48" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/>
                        <circle cx="12" cy="13" r="4"/>
                    </svg>
                    <strong>Enable Camera Scanner</strong>
                    <p>Tap below to grant camera access and start scanning ticket QR codes automatically</p>
                    <button type="button" class="admin-action-btn primary" onclick="startQrScanner()" style="border-radius:10px; padding:0.6rem 1.25rem; font-size:0.9rem;">
                        📷 Start Camera Scan
                    </button>
                </div>
            </div>
        </div>

        <!-- Snap Photo / Upload Image Fallback -->
        <div class="photo-scan-actions">
            <label class="admin-action-btn" style="border-radius:10px; padding:0.5rem 1rem; font-size:0.82rem; cursor:pointer; background:rgba(var(--muted),0.08); border:1px solid rgba(var(--muted),0.15); display:inline-flex; align-items:center; gap:0.4rem;">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                    <circle cx="8.5" cy="8.5" r="1.5"/>
                    <polyline points="21 15 16 10 5 21"/>
                </svg>
                Snap Photo / Upload QR
                <input type="file" id="qr-file-input" accept="image/*" capture="environment" onchange="scanQrFromPhoto(this)" style="display:none;">
            </label>
        </div>
    </div>

    <!-- Manual Entry Mode -->
    <div id="manual-mode" class="manual-entry-section">
        <div style="text-align:center; margin-bottom:1rem;">
            <p style="font-size:0.85rem; color:rgb(var(--muted)); margin:0;">
                Type or paste the ticket code printed on the attendee's pass
            </p>
        </div>
        <form id="sec-manual-form" onsubmit="verifyManualCode(event)">
            <input id="sec-code-input" name="code" type="text"
                class="manual-big-input"
                placeholder="e.g. EV-1001 or 1001"
                required autocomplete="off"
                style="width:100%; border-radius:12px; padding:1rem; border:2px solid rgba(var(--muted),0.15); background:rgb(var(--card-rgb, 255, 255, 255)); color:rgb(var(--text)); font-size:1.5rem; font-weight:700; text-align:center; letter-spacing:0.08em; outline:none; margin-bottom:0.75rem;"
                aria-label="Ticket code input" />
            <button type="submit" class="admin-action-btn primary"
                style="width:100%; border-radius:12px; height:50px; font-size:1rem; justify-content:center; display:flex; align-items:center; gap:0.5rem;">
                <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20 6 9 17 4 12"/>
                </svg>
                Verify &amp; Check In
            </button>
        </form>
    </div>
</section>

<!-- Scan Log history -->
<section class="form-card" style="padding: 1.25rem; margin-top: 1rem;">
    <h2 style="font-size:1rem; margin:0 0 0.85rem; display:flex; justify-content:space-between; align-items:center;">
        <span>Scan History</span>
        <button type="button" onclick="clearScanLog()" style="font-size:0.72rem; font-weight:normal; color:rgb(var(--muted)); background:none; border:none; cursor:pointer; text-decoration:underline;">Clear Log</button>
    </h2>
    <div class="log-list" id="scan-log-container">
        <div class="log-item">
            <div>
                <strong>Workstation Active</strong>
                <div style="font-size:0.7rem; color:rgb(var(--muted));">Ready for scanning • <?= date('H:i') ?></div>
            </div>
            <span class="log-status log-status-success">READY</span>
        </div>
    </div>
</section>

<!-- Processing Overlay (hidden) -->
<div id="scan-processing" class="scan-processing" style="display:none;">
    <div class="scan-processing-inner">
        <div class="spinner"></div>
        <div style="font-weight:600; font-size:0.9rem;">Verifying ticket...</div>
    </div>
</div>
@endsection

@section('script')
<script>
// ── CSRF Token & Routes ──
var CSRF_TOKEN = '<?= csrf_token() ?>';
var VERIFY_URL = '<?= route("/teams/verify-ticket") ?>';

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
        manualMode.style.display = 'block';
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

    // Check if html5-qrcode library is loaded
    if (typeof Html5Qrcode === 'undefined') {
        renderCameraError('QR Scanner library not loaded. Please switch to Manual Entry mode.');
        return;
    }

    // Check secure context for getUserMedia (HTTPS or localhost required by modern browsers)
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
        // Fallback to any available camera if exact environment camera fails
        html5QrcodeScanner.start(
            { facingMode: "user" },
            config,
            onQrCodeScanned,
            function(err) { /* ignore frame scan errors */ }
        ).catch(function(err2) {
            console.warn('Camera start error:', err2);
            var reason = 'Camera permission denied or camera not found.';
            if (!isSecure) {
                reason = 'Browser blocks camera access over unsecure HTTP. Please use HTTPS or use the "Snap Photo / Upload QR" button below.';
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

    readerEl.innerHTML = '<div class="camera-placeholder">' +
        '<svg viewBox="0 0 24 24" width="44" height="44" fill="none" stroke="currentColor" stroke-width="1.5" style="color:#ef4444;">' +
        '<circle cx="12" cy="12" r="10"/>' +
        '<line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/>' +
        '</svg>' +
        '<strong style="color:#ef4444; font-size:0.95rem;">Camera Access Issue</strong>' +
        '<p>' + escapeHtml(msg) + '</p>' +
        '<div style="display:flex; gap:0.5rem; flex-wrap:wrap; justify-content:center;">' +
        '<button type="button" class="admin-action-btn primary" onclick="startQrScanner()" style="border-radius:8px; padding:0.4rem 0.85rem; font-size:0.8rem;">Retry Camera</button>' +
        '<button type="button" class="admin-action-btn" onclick="switchMode(\'manual\')" style="border-radius:8px; padding:0.4rem 0.85rem; font-size:0.8rem; background:rgba(var(--muted),0.15);">Manual Entry</button>' +
        '</div></div>';
}

function onQrCodeScanned(decodedText) {
    // Debounce: prevent re-scanning same code within 3 seconds
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
                alertBox.className = 'scanner-badge-alert alert-error';
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
        alertBox.className = 'scanner-badge-alert ' + (data.success ? 'alert-success' : 'alert-error');
        alertBox.textContent = data.message;

        if (data.success) {
            if (navigator.vibrate) navigator.vibrate([100, 50, 100]);
            playTone(800, 150);

            if (countEl) {
                var current = parseInt(countEl.textContent.replace(/,/g, '') || '0') || 0;
                countEl.textContent = formatNumber(current + 1);
            }

            if (logContainer && data.ticket) {
                var logHtml = '<div class="log-item">' +
                    '<div><strong>' + escapeHtml(data.ticket.attendee_name) + '</strong>' +
                    '<div style="font-size:0.7rem; color:rgb(var(--muted));">#' + escapeHtml(String(data.ticket.id)) + ' • ' + escapeHtml(data.ticket.ticket_type_name) + ' • ' + new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit', second:'2-digit'}) + '</div></div>' +
                    '<span class="log-status log-status-success">✓ IN</span></div>';
                logContainer.insertAdjacentHTML('afterbegin', logHtml);
            }
        } else {
            if (navigator.vibrate) navigator.vibrate(300);
            playTone(300, 300);

            if (logContainer) {
                var errHtml = '<div class="log-item">' +
                    '<div><strong>Code: ' + escapeHtml(code) + '</strong>' +
                    '<div style="font-size:0.7rem; color:rgb(var(--muted));">' + escapeHtml(data.message) + ' • ' + new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit', second:'2-digit'}) + '</div></div>' +
                    '<span class="log-status log-status-error">✗ DENY</span></div>';
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
            alertBox.className = 'scanner-badge-alert alert-error';
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
        logContainer.innerHTML = '<div class="log-item">' +
            '<div><strong>Log Cleared</strong>' +
            '<div style="font-size:0.7rem; color:rgb(var(--muted));">Ready for scanning • ' + new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}) + '</div></div>' +
            '<span class="log-status log-status-success">READY</span></div>';
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