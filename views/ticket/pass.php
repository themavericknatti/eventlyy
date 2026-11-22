<?php
/**
 * Modern Full-Height Flyer Admission Pass View
 * The event image covers the full height of the pass (poster-first flyer style).
 * Scannable QR code & details are elegantly layered over the image with glassmorphism.
 * Supports instant image download (PNG picture), WhatsApp sharing, and printing.
 *
 * @package Eventlyy
 * @subpackage Views/Ticket
 */

$siteTitle = site_setting('site_title', 'Eventlyy');
$eventDate = !empty($ticket['event_date']) ? strtotime($ticket['event_date']) : null;
$isCheckedIn = !empty($ticket['checked_in']);
$isCancelled = in_array($ticket['status'] ?? '', ['Cancelled', 'Refunded']);
$isLayaway = (($ticket['status'] ?? '') === 'Layaway');
$ticketCode = $ticket['id'];
$admits = (int)($ticket['ticket_admits'] ?? 1);
$isUnlocked = !empty($isUnlocked);
$maskedEmail = !empty($ticket['attendee_email']) ? preg_replace('/(?<=.{2}).(?=.*@)/', '*', $ticket['attendee_email']) : 'your email';

$rawImg = $ticket['event_image'] ?? '';
$evImg = '';
if (!empty($rawImg)) {
    if (preg_match('#^https?://#i', $rawImg)) {
        $evImg = $rawImg;
    } elseif (str_starts_with(ltrim($rawImg, '/\\'), 'assets/')) {
        $evImg = asset($rawImg);
    } else {
        $evImg = url($rawImg);
    }
}

// Fallback high-contrast flyer poster backdrop if no image exists
$defaultPosterGrad = 'linear-gradient(145deg, #0f172a 0%, #1e1b4b 50%, #0f172a 100%)';
$qrUrl = url('/ticket/qr/' . urlencode($ticketCode) . '?s=360');
?>

@extends('layouts/main')

@section('title')
<?= $isUnlocked ? ('Pass #' . e($ticketCode)) : 'Protected Admission Pass' ?> — <?= e($ticket['event_name']) ?>
@endsection

@section('style')
<style>
/* ═══════════════════════════════════════════════════════
   FULL-HEIGHT FLYER ADMISSION PASS STYLES
   ═══════════════════════════════════════════════════════ */
:root {
    --flyer-radius: 26px;
    --flyer-accent: var(--primary, #108a55);
    --flyer-accent-rgb: var(--primary-rgb, 16 138 85);
}

.flyer-pass-page {
    min-height: calc(100vh - 75px);
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    padding: 2rem 1rem 3.5rem;
    background: radial-gradient(circle at 50% 15%, rgba(var(--flyer-accent-rgb), 0.16), transparent 75%),
                #070a10;
    box-sizing: border-box;
}

/* Outer layout container (Flyer + Quick Action Toolbar) */
.flyer-pass-container {
    width: 100%;
    max-width: 440px;
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
}

/* ── The Flyer Pass Element (Target for Download & Print) ── */
.flyer-card {
    position: relative;
    width: 100%;
    min-height: 640px;
    border-radius: var(--flyer-radius);
    overflow: hidden;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    box-shadow: 0 35px 85px -15px rgba(0, 0, 0, 0.75),
                0 0 45px rgba(var(--flyer-accent-rgb), 0.2),
                0 0 0 1px rgba(255, 255, 255, 0.12);
    background: #0f172a;
    box-sizing: border-box;
}

/* ── Full-Height Background Event Image Cover ── */
.flyer-bg-layer {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    z-index: 1;
    overflow: hidden;
}

.flyer-bg-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    object-position: center;
    display: block;
    transform: scale(1.02);
    filter: brightness(0.92);
}

/* Full-height cinematic gradient overlays to guarantee pristine contrast */
.flyer-bg-vignette {
    position: absolute;
    inset: 0;
    background: linear-gradient(
        180deg,
        rgba(7, 10, 16, 0.55) 0%,
        rgba(7, 10, 16, 0.20) 25%,
        rgba(7, 10, 16, 0.50) 55%,
        rgba(7, 10, 16, 0.88) 82%,
        rgba(7, 10, 16, 0.98) 100%
    );
    pointer-events: none;
}

/* Top Header Bar inside the flyer */
.flyer-header {
    position: relative;
    z-index: 2;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1.25rem 1.25rem 0.5rem;
}

.flyer-brand-tag {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    padding: 0.38rem 0.85rem;
    background: rgba(10, 14, 23, 0.72);
    backdrop-filter: blur(14px);
    -webkit-backdrop-filter: blur(14px);
    border: 1px solid rgba(255, 255, 255, 0.18);
    border-radius: 9999px;
    font-size: 0.74rem;
    font-weight: 800;
    letter-spacing: 0.06em;
    color: #ffffff;
    text-transform: uppercase;
}

.flyer-brand-dot {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background: var(--flyer-accent);
    box-shadow: 0 0 10px var(--flyer-accent);
}

.flyer-status-badge {
    padding: 0.38rem 0.85rem;
    border-radius: 9999px;
    font-size: 0.72rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    backdrop-filter: blur(14px);
    -webkit-backdrop-filter: blur(14px);
}
.flyer-status--valid { background: rgba(16, 185, 129, 0.25); border: 1px solid #10b981; color: #34d399; }
.flyer-status--checked { background: rgba(59, 130, 246, 0.25); border: 1px solid #3b82f6; color: #60a5fa; }
.flyer-status--cancelled { background: rgba(239, 68, 68, 0.25); border: 1px solid #ef4444; color: #f87171; }
.flyer-status--locked { background: rgba(245, 158, 11, 0.25); border: 1px solid #f59e0b; color: #fbbf24; }
.flyer-status--layaway { background: rgba(99, 102, 241, 0.30); border: 1px solid #6366f1; color: #a5b4fc; }

/* Event Hero Typography (Floats over the image) */
.flyer-center-info {
    position: relative;
    z-index: 2;
    padding: 1.5rem 1.4rem 0.75rem;
    margin-top: auto;
}

.flyer-title {
    font-size: 1.65rem;
    font-weight: 900;
    color: #ffffff;
    margin: 0 0 0.5rem;
    line-height: 1.22;
    letter-spacing: -0.01em;
    text-shadow: 0 2px 14px rgba(0, 0, 0, 0.85);
    word-break: break-word;
}

.flyer-meta-row {
    display: flex;
    flex-direction: column;
    gap: 0.35rem;
    font-size: 0.86rem;
    color: rgba(241, 245, 249, 0.95);
    text-shadow: 0 2px 8px rgba(0, 0, 0, 0.8);
    font-weight: 600;
}

.flyer-meta-row span {
    display: inline-flex;
    align-items: center;
    gap: 7px;
}

/* ── Middle/Bottom Overlay Deck: Scannable QR & Attendee Details ── */
.flyer-glass-deck {
    position: relative;
    z-index: 2;
    margin: 0.5rem 1rem 1rem;
    padding: 1.15rem 1.25rem;
    background: rgba(10, 14, 23, 0.78);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border: 1px solid rgba(255, 255, 255, 0.16);
    border-radius: 20px;
    box-shadow: 0 16px 40px rgba(0, 0, 0, 0.45);
}

/* Unlocked QR Hero Card */
.flyer-qr-section {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1.1rem;
    padding-bottom: 0.95rem;
    margin-bottom: 0.95rem;
    border-bottom: 1px dashed rgba(255, 255, 255, 0.14);
}

.flyer-qr-holder {
    position: relative;
    width: 125px;
    height: 125px;
    min-width: 125px;
    background: #ffffff;
    border-radius: 16px;
    padding: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 10px 28px rgba(0, 0, 0, 0.55), 0 0 25px rgba(var(--flyer-accent-rgb), 0.28);
    box-sizing: border-box;
}

.flyer-qr-holder img {
    width: 100%;
    height: 100%;
    object-fit: contain;
    display: block;
    image-rendering: -webkit-optimize-contrast;
    image-rendering: crisp-edges;
    image-rendering: pixelated;
}

/* Corner bracket targets around QR */
.flyer-qr-holder::before, .flyer-qr-holder::after {
    content: '';
    position: absolute;
    width: 16px;
    height: 16px;
    border-color: var(--flyer-accent);
    border-style: solid;
    pointer-events: none;
}
.flyer-qr-holder::before { top: -3px; left: -3px; border-width: 3px 0 0 3px; border-top-left-radius: 10px; }
.flyer-qr-holder::after { bottom: -3px; right: -3px; border-width: 0 3px 3px 0; border-bottom-right-radius: 10px; }

.flyer-qr-caption {
    flex: 1;
    min-width: 0;
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.flyer-code-label {
    font-size: 0.68rem;
    text-transform: uppercase;
    font-weight: 800;
    letter-spacing: 0.08em;
    color: #94a3b8;
    margin-bottom: 3px;
}

.flyer-code-display {
    font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
    font-size: 1.15rem;
    font-weight: 900;
    color: #34d399;
    letter-spacing: 0.08em;
    margin-bottom: 6px;
}

.flyer-code-hint {
    font-size: 0.74rem;
    color: #cbd5e1;
    line-height: 1.4;
    margin: 0;
}

/* Pass Details Grid */
.flyer-specs-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.75rem;
}

.flyer-spec-item label {
    display: block;
    font-size: 0.64rem;
    text-transform: uppercase;
    font-weight: 800;
    color: #94a3b8;
    letter-spacing: 0.05em;
    margin-bottom: 2px;
}

.flyer-spec-item strong {
    font-size: 0.9rem;
    color: #f8fafc;
    font-weight: 700;
    word-break: break-word;
}

/* Addons / Merch section inside flyer */
.flyer-addons-strip {
    margin-top: 0.85rem;
    padding-top: 0.75rem;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
}

.flyer-addons-title {
    font-size: 0.68rem;
    font-weight: 800;
    text-transform: uppercase;
    color: #2dd4bf;
    letter-spacing: 0.05em;
    display: flex;
    align-items: center;
    gap: 5px;
    margin-bottom: 6px;
}

.flyer-addon-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 0.8rem;
    color: #f1f5f9;
    padding: 2px 0;
}

/* Watermark platform footer inside card */
.flyer-watermark {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 7px;
    padding-top: 0.85rem;
    margin-top: 0.85rem;
    border-top: 1px solid rgba(255, 255, 255, 0.08);
    font-size: 0.72rem;
    font-weight: 700;
    color: rgba(255, 255, 255, 0.55);
    letter-spacing: 0.04em;
    text-transform: uppercase;
}

/* ── PIN Locked Card State ── */
.flyer-locked-card {
    text-align: center;
    padding: 1.25rem 0.5rem;
}

.flyer-locked-icon {
    width: 52px;
    height: 52px;
    border-radius: 50%;
    background: rgba(245, 158, 11, 0.15);
    border: 1.5px solid rgba(245, 158, 11, 0.35);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    margin-bottom: 0.65rem;
}

.flyer-locked-title {
    font-size: 1.15rem;
    font-weight: 800;
    color: #ffffff;
    margin: 0 0 0.35rem;
}

.flyer-locked-desc {
    font-size: 0.8rem;
    color: #94a3b8;
    margin: 0 0 1rem;
    line-height: 1.45;
}

.flyer-pin-input {
    width: 145px;
    text-align: center;
    font-size: 1.85rem;
    font-weight: 900;
    letter-spacing: 0.35em;
    padding: 0.55rem 0.65rem;
    border: 2px solid #334155;
    border-radius: 12px;
    background: #090d16;
    color: #34d399;
    outline: none;
    font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
    transition: all 0.2s;
    box-shadow: inset 0 2px 4px rgba(0,0,0,0.4);
}
.flyer-pin-input:focus {
    border-color: #10b981;
    box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.25), inset 0 2px 4px rgba(0,0,0,0.4);
}

.flyer-pin-submit {
    width: 100%;
    max-width: 250px;
    margin-top: 0.85rem;
    padding: 0.75rem 1.25rem;
    border: none;
    border-radius: 12px;
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: #ffffff;
    font-weight: 800;
    font-size: 0.92rem;
    cursor: pointer;
    box-shadow: 0 4px 14px rgba(16, 185, 129, 0.35);
    transition: all 0.15s;
}
.flyer-pin-submit:hover {
    background: linear-gradient(135deg, #059669 0%, #047857 100%);
    transform: translateY(-1px);
}

.flyer-pin-resend {
    margin-top: 0.75rem;
    background: none;
    border: none;
    color: #38bdf8;
    font-size: 0.8rem;
    font-weight: 700;
    cursor: pointer;
    text-decoration: underline;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}

.flyer-pin-alert {
    width: 100%;
    border-radius: 8px;
    padding: 0.55rem 0.75rem;
    font-size: 0.8rem;
    font-weight: 600;
    margin-bottom: 0.75rem;
    text-align: center;
}
.flyer-pin-alert--error { background: rgba(239, 68, 68, 0.2); border: 1px solid rgba(239, 68, 68, 0.4); color: #fca5a5; }
.flyer-pin-alert--success { background: rgba(16, 185, 129, 0.2); border: 1px solid rgba(16, 185, 129, 0.4); color: #6ee7b7; }

/* ── Layaway State inside flyer ── */
.flyer-layaway-card {
    text-align: center;
    padding: 1.25rem 0.5rem;
}
.flyer-layaway-btn {
    text-decoration: none;
    display: inline-block;
    padding: 0.75rem 1.6rem;
    border-radius: 12px;
    background: linear-gradient(135deg, #6366f1, #4f46e5);
    color: #ffffff;
    font-weight: 800;
    font-size: 0.9rem;
    box-shadow: 0 4px 15px rgba(99, 102, 241, 0.35);
}

/* ═══════════════════════════════════════════════════════
   ACTION TOOLBAR (Underneath Flyer — Download, Share, Print)
   ═══════════════════════════════════════════════════════ */
.flyer-actions-panel {
    display: flex;
    flex-direction: column;
    gap: 0.65rem;
}

/* Primary "Download Pass Picture" Button */
.btn-flyer-download {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 9px;
    width: 100%;
    padding: 0.9rem 1.25rem;
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: #ffffff;
    font-weight: 800;
    font-size: 0.95rem;
    border-radius: 14px;
    border: none;
    cursor: pointer;
    text-decoration: none;
    box-shadow: 0 6px 20px rgba(16, 185, 129, 0.35);
    transition: transform 0.15s ease, box-shadow 0.15s ease;
}
.btn-flyer-download:hover {
    background: linear-gradient(135deg, #059669 0%, #047857 100%);
    transform: translateY(-1px);
    box-shadow: 0 8px 25px rgba(16, 185, 129, 0.45);
    color: #ffffff;
}

/* WhatsApp Share Button */
.btn-flyer-whatsapp {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 9px;
    width: 100%;
    padding: 0.85rem 1.25rem;
    background: #25D366;
    color: #ffffff;
    font-weight: 800;
    font-size: 0.93rem;
    border-radius: 14px;
    border: none;
    cursor: pointer;
    text-decoration: none;
    transition: transform 0.15s ease, box-shadow 0.15s ease;
}
.btn-flyer-whatsapp:hover {
    background: #20bd5a;
    transform: translateY(-1px);
    box-shadow: 0 6px 20px rgba(37, 211, 102, 0.35);
    color: #ffffff;
}

/* Secondary Button Row (Print & Story Card) */
.flyer-secondary-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.6rem;
}

.btn-flyer-sub {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 7px;
    padding: 0.75rem 1rem;
    background: rgba(255, 255, 255, 0.08);
    border: 1px solid rgba(255, 255, 255, 0.15);
    color: #e2e8f0;
    font-weight: 700;
    font-size: 0.84rem;
    border-radius: 12px;
    cursor: pointer;
    text-decoration: none;
    transition: all 0.15s ease;
}
.btn-flyer-sub:hover {
    background: rgba(255, 255, 255, 0.14);
    color: #ffffff;
}

/* ═══════════════════════════════════════════════════════
   RESPONSIVENESS (Mobile, Tablet, Desktop)
   ═══════════════════════════════════════════════════════ */

/* Mobile Phones (Compact) */
@media (max-width: 480px) {
    .flyer-pass-page {
        padding: 1rem 0.65rem 2.5rem;
    }
    .flyer-card {
        min-height: 580px;
        border-radius: 20px;
    }
    .flyer-title {
        font-size: 1.45rem;
    }
    .flyer-qr-holder {
        width: 105px;
        height: 105px;
        min-width: 105px;
        border-radius: 12px;
    }
    .flyer-code-display {
        font-size: 1.05rem;
    }
    .flyer-glass-deck {
        margin: 0.35rem 0.65rem 0.75rem;
        padding: 1rem 1rem;
    }
}

/* Tablet (Portrait & Landscape: 600px - 899px) */
@media (min-width: 600px) and (max-width: 899px) {
    .flyer-pass-container {
        max-width: 480px;
    }
    .flyer-card {
        min-height: 700px;
        border-radius: 28px;
    }
    .flyer-title {
        font-size: 1.85rem;
    }
    .flyer-qr-holder {
        width: 135px;
        height: 135px;
        min-width: 135px;
    }
}

/* Desktop Monitors (900px+) */
@media (min-width: 900px) {
    .flyer-pass-page {
        padding: 3rem 1.5rem 4rem;
    }
    .flyer-pass-container {
        max-width: 460px;
    }
    .flyer-card {
        min-height: 680px;
        border-radius: 28px;
    }
    .flyer-title {
        font-size: 1.8rem;
    }
}

/* ═══════════════════════════════════════════════════════
   PRINT STYLES — Crisp Physical Flyer Output
   ═══════════════════════════════════════════════════════ */
@media print {
    * {
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
    body {
        background: #ffffff !important;
        color: #000000 !important;
        margin: 0 !important;
        padding: 0 !important;
    }
    /* Hide navigation, footers, and download buttons */
    header, footer, nav, .site-header, .site-footer, .flyer-actions-panel, .no-print, #storyCardModal {
        display: none !important;
    }
    .flyer-pass-page {
        min-height: auto !important;
        padding: 0 !important;
        background: none !important;
        display: block !important;
    }
    .flyer-pass-container {
        max-width: 100% !important;
        width: 100% !important;
    }
    .flyer-card {
        width: 100% !important;
        max-width: 520px !important;
        margin: 0 auto !important;
        border: 2px solid #cbd5e1 !important;
        box-shadow: none !important;
        page-break-inside: avoid;
    }
    .flyer-bg-layer {
        position: absolute !important;
    }
    .flyer-bg-img {
        opacity: 0.95 !important;
    }
    .flyer-glass-deck {
        background: rgba(10, 14, 23, 0.92) !important;
        border: 1px solid #475569 !important;
    }
    .flyer-title, .flyer-spec-item strong {
        color: #ffffff !important;
    }
}
</style>

<!-- Local html2canvas with CDN fallback -->
<script src="<?= asset('assets/js/html2canvas.min.js') ?>?v=1.4.1"></script>
@endsection

@section('content')
<div class="flyer-pass-page">
    <div class="flyer-pass-container">

        <!-- ══════════════════════════════════════════════════
             THE FLYER PASS (Full Height Image Cover)
             ══════════════════════════════════════════════════ -->
        <div class="flyer-card" id="flyerTicketPass" data-ticket-code="<?= e($ticketCode) ?>">

            <!-- Full-Height Background Image Layer -->
            <div class="flyer-bg-layer" style="<?= empty($evImg) ? "background: {$defaultPosterGrad};" : '' ?>">
                <?php if (!empty($evImg)): ?>
                    <img class="flyer-bg-img" 
                         src="<?= e($evImg) ?>" 
                         alt="<?= e($ticket['event_name']) ?>" 
                         crossorigin="anonymous" />
                <?php endif; ?>
                <div class="flyer-bg-vignette"></div>
            </div>

            <!-- Top Header: Platform Identity & Gate Status -->
            <div class="flyer-header">
                <div class="flyer-brand-tag">
                    <span class="flyer-brand-dot"></span>
                    <span><?= e($siteTitle) ?> Pass</span>
                </div>

                <?php if ($isCheckedIn): ?>
                    <div class="flyer-status-badge flyer-status--checked">✓ Checked In</div>
                <?php elseif ($isCancelled): ?>
                    <div class="flyer-status-badge flyer-status--cancelled">Cancelled</div>
                <?php elseif ($isLayaway): ?>
                    <div class="flyer-status-badge flyer-status--layaway">⏳ Layaway</div>
                <?php elseif (!$isUnlocked): ?>
                    <div class="flyer-status-badge flyer-status--locked">🔒 PIN Locked</div>
                <?php else: ?>
                    <div class="flyer-status-badge flyer-status--valid">● Ready For Scan</div>
                <?php endif; ?>
            </div>

            <!-- Center/Hero Event Typography -->
            <div class="flyer-center-info">
                <h1 class="flyer-title"><?= e($ticket['event_name']) ?></h1>
                <div class="flyer-meta-row">
                    <span>🗓️ <?= $eventDate ? date('D, M d, Y • g:i A', $eventDate) : 'Date TBA' ?></span>
                    <span>📍 <?= e($ticket['event_location'] ?? 'Venue TBA') ?></span>
                </div>
            </div>

            <!-- Glassmorphism Admission Deck (QR + Specs) -->
            <div class="flyer-glass-deck">
                <?php if ($isLayaway): ?>
                    <!-- Incomplete Layaway Warning -->
                    <div class="flyer-layaway-card">
                        <div style="font-size:2rem; margin-bottom:6px;">⏳</div>
                        <h2 style="font-size:1.1rem; color:#a5b4fc; margin:0 0 6px;">Layaway Plan Active</h2>
                        <p style="font-size:0.8rem; color:#94a3b8; margin:0 0 12px; line-height:1.4;">
                            Your ticket is reserved! The entrance QR pass unlocks automatically upon completion of your layaway payments.
                        </p>
                        <a href="<?= \url('/layaway') ?>" class="flyer-layaway-btn">Complete Layaway Plan &rarr;</a>
                    </div>
                <?php elseif (!$isUnlocked): ?>
                    <!-- Dedicated High-Contrast PIN Lock State -->
                    <div class="flyer-locked-card">
                        <div class="flyer-locked-icon"><span>🔒</span></div>
                        <h2 class="flyer-locked-title">Protected Admission Pass</h2>
                        <p class="flyer-locked-desc">
                            Enter the 4-digit PIN sent to <strong><?= e($maskedEmail) ?></strong> to reveal your entrance QR pass.
                        </p>

                        <?php if ($flashErr = \Ace\Application::$app->session->getFlash('error')): ?>
                            <div class="flyer-pin-alert flyer-pin-alert--error"><?= e($flashErr) ?></div>
                        <?php endif; ?>
                        <?php if ($flashSucc = \Ace\Application::$app->session->getFlash('success')): ?>
                            <div class="flyer-pin-alert flyer-pin-alert--success"><?= e($flashSucc) ?></div>
                        <?php endif; ?>

                        <form method="POST" action="<?= \url('/pass/' . urlencode($ticketCode) . '/unlock') ?>" style="display:flex; flex-direction:column; align-items:center;">
                            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>" />
                            <input type="password"
                                   name="pin"
                                   maxlength="4"
                                   pattern="\d{4}"
                                   inputmode="numeric"
                                   autocomplete="one-time-code"
                                   placeholder="••••"
                                   required
                                   class="flyer-pin-input"
                                   autofocus />
                            <button type="submit" class="flyer-pin-submit">🔓 Reveal QR Pass</button>
                        </form>

                        <button type="button" class="flyer-pin-resend" id="btn-pass-resend-pin" data-code="<?= e($ticketCode) ?>">
                            <span>✉️</span> Resend PIN to my email
                        </button>
                        <div id="pass-pin-resend-msg" style="margin-top:6px; font-size:0.76rem; font-weight:600; display:none;"></div>
                    </div>
                <?php else: ?>
                    <!-- Scannable High-Speed Gate QR Code -->
                    <div class="flyer-qr-section">
                        <div class="flyer-qr-holder" title="Present this QR at the venue entrance gate">
                            <img src="<?= $qrUrl ?>" 
                                 id="flyerQrCodeImg"
                                 alt="QR Pass #<?= e($ticketCode) ?>" 
                                 crossorigin="anonymous" />
                        </div>
                        <div class="flyer-qr-caption">
                            <span class="flyer-code-label">Admission Code</span>
                            <div class="flyer-code-display"><?= e($ticketCode) ?></div>
                            <p class="flyer-code-hint">
                                Scan at gate for instant verification. Please set screen brightness to 100%.
                            </p>
                        </div>
                    </div>

                    <!-- Pass Holder & Admission Details Grid -->
                    <div class="flyer-specs-grid">
                        <div class="flyer-spec-item">
                            <label>Pass Holder</label>
                            <strong><?= e($ticket['attendee_name']) ?></strong>
                        </div>
                        <div class="flyer-spec-item">
                            <label>Ticket Tier</label>
                            <strong><?= e($ticket['ticket_tier'] ?? 'General Admission') ?><?= $admits > 1 ? " ({$admits} Admits)" : "" ?></strong>
                        </div>
                        <div class="flyer-spec-item">
                            <label>Gate Status</label>
                            <strong style="color: <?= $isCheckedIn ? '#60a5fa' : '#34d399' ?>;">
                                <?= $isCheckedIn ? 'Checked In' : 'Active Pass' ?>
                            </strong>
                        </div>
                        <div class="flyer-spec-item">
                            <label>Verification</label>
                            <strong style="color: #f8fafc;"><?= e($siteTitle) ?> Verified</strong>
                        </div>
                    </div>

                    <!-- Add-Ons / Merch Section (If applicable) -->
                    <?php if (!empty($addons)): ?>
                        <div class="flyer-addons-strip">
                            <div class="flyer-addons-title">
                                <span>🛍️</span> <span>Add-ons &amp; Merch Voucher</span>
                            </div>
                            <?php foreach ($addons as $addon): ?>
                                <div class="flyer-addon-item">
                                    <span><?= (int)$addon['quantity'] ?>&times; <?= e($addon['addon_name'] ?? 'Merchandise') ?></span>
                                    <span style="color:#2dd4bf; font-weight:700;"><?= ucfirst($addon['redemption_status'] ?? 'Pending') ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>

                <!-- Platform Branding Watermark (Non-intrusive) -->
                <div class="flyer-watermark">
                    <span><?= e($siteTitle) ?> Official Admission Flyer</span>
                </div>
            </div>

        </div>

        <!-- ══════════════════════════════════════════════════
             ACTION TOOLBAR (Download Image, WhatsApp, Print)
             ══════════════════════════════════════════════════ -->
        <?php if ($isUnlocked): ?>
            <div class="flyer-actions-panel no-print">

                <!-- 1-Click Download Picture Button -->
                <button type="button" class="btn-flyer-download" id="btn-download-pass-picture">
                    <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    <span id="btn-download-text">Download Pass as Picture</span>
                </button>

                <!-- WhatsApp Share Direct -->
                <?php
                    $tData = ($ticket instanceof \Ace\ViewData) ? $ticket->toArray() : (array)$ticket;
                    $waMsg = \App\Services\WhatsAppService::buildTicketPassMessage(
                        ['name' => $tData['event_name'] ?? '', 'date' => $tData['event_date'] ?? null, 'location' => $tData['event_location'] ?? ''],
                        $tData,
                        $siteTitle
                    );
                    $waUrl = \App\Services\WhatsAppService::generateDirectWhatsAppUrl('', $waMsg);
                ?>
                <a href="<?= e($waUrl) ?>" target="_blank" rel="noopener" class="btn-flyer-whatsapp">
                    <svg width="19" height="19" viewBox="0 0 24 24" fill="currentColor"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.711 2.598 2.664-.698c.969.586 1.861.947 3.018.947 3.174 0 5.79-2.586 5.79-5.767.001-3.18-2.617-5.767-5.789-5.767m7.859 5.766c0 4.331-3.523 7.854-7.859 7.854-1.328 0-2.56-.344-3.644-.949l-4.048 1.061 1.08-3.945c-.714-1.156-1.127-2.518-1.127-3.971 0-4.331 3.523-7.854 7.859-7.854 4.336 0 7.859 3.523 7.859 7.854"/></svg>
                    <span>Share Pass to WhatsApp</span>
                </a>

                <!-- Print & Story Card -->
                <div class="flyer-secondary-grid">
                    <button type="button" onclick="window.print()" class="btn-flyer-sub">
                        <span>🖨️</span> Print Pass
                    </button>
                    <button type="button" id="btn-trigger-story-pass" class="btn-flyer-sub" style="color:var(--flyer-accent);"
                            data-event-id="<?= e($ticket['event_id']) ?>"
                            data-event-title="<?= e($ticket['event_name']) ?>"
                            data-event-date="<?= e($ticket['event_date']) ?>"
                            data-event-location="<?= e($ticket['event_location']) ?>"
                            data-event-image="<?= e($evImg) ?>"
                            data-site-title="<?= e($siteTitle) ?>">
                        <span>📸</span> Story Card
                    </button>
                </div>
            </div>
        <?php else: ?>
            <div style="display:flex; justify-content:center; margin-top:0.5rem;" class="no-print">
                <button type="button" id="btn-trigger-story-pass" class="btn-flyer-sub" style="max-width:240px; color:var(--flyer-accent);"
                        data-event-id="<?= e($ticket['event_id']) ?>"
                        data-event-title="<?= e($ticket['event_name']) ?>"
                        data-event-date="<?= e($ticket['event_date']) ?>"
                        data-event-location="<?= e($ticket['event_location']) ?>"
                        data-event-image="<?= e($evImg) ?>"
                        data-site-title="<?= e($siteTitle) ?>">
                    <span>📸</span> Share Story Card
                </button>
            </div>
        <?php endif; ?>

    </div>
</div>

<?php echo $this->include('partials/story-card-modal', []); ?>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // ── 1. Download Pass as Picture (html2canvas with Dynamic Loader) ──────
    var downloadBtn = document.getElementById('btn-download-pass-picture');
    var downloadText = document.getElementById('btn-download-text');
    var flyerCard = document.getElementById('flyerTicketPass');

    // Helper: Ensure html2canvas is available (from local asset or cdn fallback)
    function ensureHtml2Canvas(callback) {
        if (typeof window.html2canvas !== 'undefined') {
            return callback(null, window.html2canvas);
        }

        // Dynamically inject script tag if not yet loaded
        var script = document.createElement('script');
        script.src = '<?= asset('assets/js/html2canvas.min.js') ?>?v=1.4.1';
        script.onload = function () {
            if (typeof window.html2canvas !== 'undefined') {
                callback(null, window.html2canvas);
            } else {
                fallbackCdn();
            }
        };
        script.onerror = function () {
            fallbackCdn();
        };

        function fallbackCdn() {
            var cdnScript = document.createElement('script');
            cdnScript.src = 'https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js';
            cdnScript.onload = function () {
                if (typeof window.html2canvas !== 'undefined') {
                    callback(null, window.html2canvas);
                } else {
                    callback(new Error('html2canvas unavailable'));
                }
            };
            cdnScript.onerror = function (e) {
                callback(new Error('html2canvas failed to load'));
            };
            document.head.appendChild(cdnScript);
        }

        document.head.appendChild(script);
    }

    if (downloadBtn && flyerCard) {
        downloadBtn.addEventListener('click', function () {
            var originalText = downloadText ? downloadText.textContent : 'Download Pass as Picture';
            if (downloadText) downloadText.textContent = 'Preparing Picture...';
            downloadBtn.disabled = true;
            downloadBtn.style.opacity = '0.75';

            ensureHtml2Canvas(function (err, h2c) {
                if (err || !h2c) {
                    alert('Could not initialize the image generator. You can use "Print Pass" to save as a PDF or picture.');
                    if (downloadText) downloadText.textContent = originalText;
                    downloadBtn.disabled = false;
                    downloadBtn.style.opacity = '1';
                    return;
                }

                if (downloadText) downloadText.textContent = 'Generating HD Picture...';

                var ticketCode = flyerCard.getAttribute('data-ticket-code') || 'Pass';
                var safeFilename = 'Eventlyy_Pass_' + ticketCode.replace(/[^a-zA-Z0-9_-]/g, '_') + '.png';

                // High-resolution capture at 2x scale for sharp output on mobile & desktop
                h2c(flyerCard, {
                    scale: 2,
                    useCORS: true,
                    allowTaint: true,
                    backgroundColor: '#070a10',
                    logging: false,
                    imageTimeout: 10000
                }).then(function (canvas) {
                    try {
                        var dataUrl = canvas.toDataURL('image/png');
                        var a = document.createElement('a');
                        a.href = dataUrl;
                        a.download = safeFilename;
                        document.body.appendChild(a);
                        a.click();
                        document.body.removeChild(a);

                        if (downloadText) downloadText.textContent = '✓ Saved to Pictures!';
                        setTimeout(function () {
                            if (downloadText) downloadText.textContent = originalText;
                            downloadBtn.disabled = false;
                            downloadBtn.style.opacity = '1';
                        }, 2500);
                    } catch (e) {
                        console.warn('Canvas export failed, falling back to print:', e);
                        window.print();
                        if (downloadText) downloadText.textContent = originalText;
                        downloadBtn.disabled = false;
                        downloadBtn.style.opacity = '1';
                    }
                }).catch(function (error) {
                    console.error('html2canvas rendering error:', error);
                    alert('Could not render image directly. Opening print dialog to save as PDF or image.');
                    window.print();
                    if (downloadText) downloadText.textContent = originalText;
                    downloadBtn.disabled = false;
                    downloadBtn.style.opacity = '1';
                });
            });
        });
    }

    // ── 2. Resend PIN Ajax ──────────────────────────────────────────
    var resendBtn = document.getElementById('btn-pass-resend-pin');
    var resendMsg = document.getElementById('pass-pin-resend-msg');
    if (resendBtn) {
        resendBtn.addEventListener('click', function () {
            var code = this.getAttribute('data-code');
            this.disabled = true;
            this.style.opacity = '0.6';
            if (resendMsg) {
                resendMsg.style.display = 'block';
                resendMsg.style.color = '#94a3b8';
                resendMsg.textContent = 'Sending PIN to your email...';
            }

            fetch('<?= \url('/pass/') ?>' + encodeURIComponent(code) + '/resend-pin', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: 'csrf_token=<?= e(csrf_token()) ?>'
            })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data.success) {
                    if (resendMsg) {
                        resendMsg.style.color = '#34d399';
                        resendMsg.textContent = '✓ ' + (data.message || 'Security PIN resent successfully.');
                    }
                } else {
                    if (resendMsg) {
                        resendMsg.style.color = '#f87171';
                        resendMsg.textContent = '✕ ' + (data.message || 'Could not resend PIN.');
                    }
                    resendBtn.disabled = false;
                    resendBtn.style.opacity = '1';
                }
            })
            .catch(function () {
                if (resendMsg) {
                    resendMsg.style.color = '#f87171';
                    resendMsg.textContent = '✕ Error connecting to server.';
                }
                resendBtn.disabled = false;
                resendBtn.style.opacity = '1';
            });
        });
    }
});
</script>
@endsection
