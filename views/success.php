<?php

/**
 * Order Success Page — VIP Printable Pass View
 *
 * Displays purchase confirmation, issued ticket passes, QR code visualizer, and print/PDF pass generator.
 *
 * @package Evently
 * @subpackage Views/Checkout
 * @author Your Name <Celio Natti>
 * @license MIT License
 * @version 2.1.0
 */

$siteTitle = site_setting('site_title', 'Eventlyy');
$order = $order ?? [
    'event_name' => '',
    'attendee_email' => '',
    'event_date' => '',
    'event_location' => '',
    'attendee_name' => '',
    'payment_reference' => '',
    'tickets' => [],
];
?>

@extends('layouts/main')

@section('title')
Order Success - <?= e($order['event_name'] ?? 'Purchase Confirmed') ?>
@endsection

@section('content')
<section class="success-hero no-print">
    <div class="success-check-icon">✓</div>
    <span class="eyebrow" style="color:#10b981;">Purchase Confirmed</span>
    <h1 class="success-title">You're Going to <?= e($order['event_name']) ?>!</h1>
    <p class="lede success-subtitle">
        Your order is complete. Digital admission passes have been delivered to <strong><?= e($order['attendee_email']) ?></strong>.
    </p>
</section>

<div class="printable-ticket-wrapper">
    <!-- Action Controls (Hidden on Print / PDF Export) -->
    <div class="no-print success-action-bar">
        <a href="{{ route('/') }}" class="admin-action-btn success-browse-btn">
            ← Browse More Events
        </a>
        <div style="display:flex; gap:0.5rem;">
            <a href="{{ route('profile') }}" class="admin-action-btn" style="text-decoration:none;">
                👤 View in My Tickets
            </a>
            <button onclick="window.print()" class="admin-action-btn primary success-print-btn">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                Print Passes &amp; Receipt
            </button>
        </div>
    </div>

    <!-- Official Order Payment Receipt Card -->
    <div class="ticket-receipt-card" style="background:var(--card-bg, #ffffff); border:1px solid var(--border-color, rgba(0,0,0,0.12)); border-radius:18px; padding:1.5rem 1.75rem; margin-bottom:2rem; box-shadow:0 6px 20px rgba(0,0,0,0.04);">
        <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:0.75rem; border-bottom:1px solid rgba(var(--muted), 0.15); padding-bottom:1rem; margin-bottom:1.25rem;">
            <div>
                <span style="font-size:0.75rem; text-transform:uppercase; font-weight:800; color:rgb(var(--brand)); letter-spacing:0.06em;">🧾 Official Transaction Receipt</span>
                <h3 style="margin:0.25rem 0 0; font-size:1.15rem; color:var(--text);">Order #<?= e($order['payment_reference'] ?? ('ORD-' . time())) ?></h3>
            </div>
            <div style="text-align:right;">
                <span style="display:inline-block; background:rgba(16,185,129,0.12); color:#10b981; font-size:0.78rem; font-weight:700; padding:0.35rem 0.85rem; border-radius:20px; border:1px solid rgba(16,185,129,0.25);">
                    ✓ PAID &amp; CONFIRMED
                </span>
                <div style="font-size:0.75rem; color:var(--text-muted); margin-top:0.25rem;">Payment Method: <strong><?= e($order['payment_method'] ?? 'CARD') ?></strong></div>
            </div>
        </div>

        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(180px, 1fr)); gap:1rem; margin-bottom:1.25rem; font-size:0.88rem;">
            <div>
                <span style="font-size:0.72rem; color:var(--text-muted); text-transform:uppercase; font-weight:700; display:block;">Billed To</span>
                <strong style="color:var(--text);"><?= e($order['attendee_name']) ?></strong>
                <div style="color:var(--text-muted); font-size:0.8rem;"><?= e($order['attendee_email']) ?></div>
            </div>
            <div>
                <span style="font-size:0.72rem; color:var(--text-muted); text-transform:uppercase; font-weight:700; display:block;">Event</span>
                <strong style="color:var(--text);"><?= e($order['event_name']) ?></strong>
                <div style="color:var(--text-muted); font-size:0.8rem;">📍 <?= e($order['event_location']) ?></div>
            </div>
            <div>
                <span style="font-size:0.72rem; color:var(--text-muted); text-transform:uppercase; font-weight:700; display:block;">Total Amount Paid</span>
                <strong style="font-size:1.15rem; color:rgb(var(--brand));"><?= format_price($order['total_amount'] ?? 0) ?></strong>
                <div style="color:var(--text-muted); font-size:0.8rem;"><?= count($order['tickets']) ?> ticket(s) issued</div>
            </div>
        </div>
    </div>

    <!-- Ticket Pass Cards Loop -->
    <?php foreach ($order['tickets'] as $index => $t): 
        $tCode = $t['ticket_code'] ?? ($t['id'] ?? 'EV-' . ($index + 1));
        $qrSrc = ticket_qr_url($tCode, 240);
    ?>
        <div class="ticket-pass-card">
            <!-- Top Pass Header Bar -->
            <div class="ticket-pass-header">
                <div class="pass-header-left">
                    <div class="pass-logo-icon">
                        <?= e(mb_substr($siteTitle, 0, 1)) ?>
                    </div>
                    <div>
                        <span class="pass-header-eyebrow"><?= e($siteTitle) ?> • OFFICIAL ADMISSION PASS</span>
                        <h2 class="pass-header-event"><?= e($order['event_name']) ?></h2>
                    </div>
                </div>
                <div>
                    <span class="pass-type-badge">
                        <?= e($t['ticket_type']) ?>
                    </span>
                </div>
            </div>

            <!-- Pass Body Grid -->
            <div class="ticket-pass-body">
                <!-- Event & Attendee Info -->
                <div class="pass-info-section">
                    <div class="pass-details-grid">
                        <div class="pass-detail-item">
                            <span class="pass-detail-label">Date &amp; Time</span>
                            <strong class="pass-detail-value">📅 <?= date('D, M d, Y • g:i A', strtotime($order['event_date'])) ?></strong>
                        </div>
                        <div class="pass-detail-item">
                            <span class="pass-detail-label">Venue / Location</span>
                            <strong class="pass-detail-value">📍 <?= e($order['event_location']) ?></strong>
                        </div>
                        <div class="pass-detail-item">
                            <span class="pass-detail-label">Ticket Holder</span>
                            <strong class="pass-detail-value">👤 <?= e($order['attendee_name']) ?></strong>
                        </div>
                        <div class="pass-detail-item">
                            <span class="pass-detail-label">Payment Reference</span>
                            <strong class="pass-detail-value pass-ref-code"><?= e($order['payment_reference'] ?? ('ORD-' . ($t['id'] ?? $index))) ?></strong>
                        </div>
                    </div>

                    <!-- Unique Ticket Code Block -->
                    <div class="pass-admission-code-block">
                        <div>
                            <span class="pass-detail-label">Unique Admission Code</span>
                            <div class="pass-admission-code"><?= e($tCode) ?></div>
                        </div>
                        <span class="pass-valid-badge">Valid Pass</span>
                    </div>
                </div>

                <!-- QR Code Stub Side -->
                <div class="ticket-qr-stub">
                    <div class="qr-code-frame" title="Scan this QR code at the entrance gate">
                        <img src="<?= $qrSrc ?>" 
                             alt="QR Code for Ticket <?= e($tCode) ?>" 
                             width="135" height="135" 
                             class="ticket-qr-real-img" 
                             style="display:block; width:135px; height:135px; image-rendering:pixelated; border-radius:6px;" />
                    </div>
                    <span class="qr-scan-label">SCAN AT ENTRY GATE</span>
                    <span style="font-family:monospace; font-size:0.75rem; font-weight:700; color:rgb(var(--brand)); margin-top:2px;"><?= e($tCode) ?></span>
                </div>
            </div>

            <!-- Pass Footer Notice -->
            <div class="ticket-pass-footer">
                <span>🔒 Secure Gate-Verifiable Admission Pass</span>
                <span>Pass <?= $index + 1 ?> of <?= count($order['tickets']) ?></span>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<style>
/* ═══════════════════════════════════════════════════════════
   Success Page — Responsive Styles
   ═══════════════════════════════════════════════════════════ */

.success-hero {
    text-align: center;
    max-width: 700px;
    margin: 0 auto 2.5rem;
    padding: 0 1rem;
    box-sizing: border-box;
}

.success-check-icon {
    width: 64px;
    height: 64px;
    background: rgba(16, 185, 129, 0.12);
    color: #10b981;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    margin-bottom: 1rem;
    border: 1px solid rgba(16, 185, 129, 0.25);
}

.success-title {
    font-size: clamp(1.4rem, 4vw, 2.2rem);
    margin-top: 0.25rem;
    line-height: 1.2;
    word-break: break-word;
}

.success-subtitle {
    font-size: clamp(0.88rem, 2.5vw, 1.05rem);
    color: var(--text-muted);
}

/* Printable Ticket Wrapper */
.printable-ticket-wrapper {
    max-width: 820px;
    margin: 0 auto 3rem;
    padding: 0 1rem;
    box-sizing: border-box;
}

/* Action Bar */
.success-action-bar {
    margin-bottom: 1.75rem;
    display: flex;
    gap: 1rem;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    background: var(--card-bg, rgba(255,255,255,0.03));
    padding: 1rem 1.25rem;
    border-radius: 16px;
    border: 1px solid var(--border-color, rgba(255,255,255,0.1));
    box-sizing: border-box;
}

.success-browse-btn {
    text-decoration: none;
    padding: 0.65rem 1.25rem;
    border-radius: 10px;
    font-weight: 600;
    white-space: nowrap;
}

.success-print-btn {
    padding: 0.7rem 1.75rem;
    font-weight: 700;
    border-radius: 10px;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    white-space: nowrap;
}

/* Ticket Pass Card */
.ticket-pass-card {
    background: var(--card-bg, #ffffff);
    border: 1px solid var(--border-color, rgba(0,0,0,0.12));
    border-radius: 20px;
    overflow: hidden;
    margin-bottom: 2rem;
    box-shadow: 0 12px 35px rgba(0,0,0,0.07);
    position: relative;
}

/* Pass Header */
.ticket-pass-header {
    background: linear-gradient(135deg, rgb(var(--brand)) 0%, #1e1b4b 100%);
    color: #ffffff;
    padding: 1.25rem 1.75rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 0.75rem;
}

.pass-header-left {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    min-width: 0;
}

.pass-logo-icon {
    width: 38px;
    height: 38px;
    background: rgba(255,255,255,0.2);
    backdrop-filter: blur(4px);
    border-radius: 10px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-weight: 800;
    font-size: 1.15rem;
    color: #ffffff;
    flex-shrink: 0;
}

.pass-header-eyebrow {
    font-size: 0.7rem;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    opacity: 0.88;
    font-weight: 700;
    display: block;
    word-break: break-word;
}

.pass-header-event {
    margin: 0;
    font-size: clamp(1rem, 3vw, 1.3rem);
    font-weight: 700;
    color: #ffffff;
    line-height: 1.2;
    word-break: break-word;
}

.pass-type-badge {
    display: inline-block;
    background: rgba(255,255,255,0.22);
    backdrop-filter: blur(6px);
    padding: 0.35rem 0.85rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: #ffffff;
    white-space: nowrap;
}

/* Pass Body */
.ticket-pass-body {
    padding: 1.6rem 1.75rem;
    display: grid;
    grid-template-columns: 1fr 190px;
    gap: 1.75rem;
    align-items: center;
}

.pass-info-section {
    min-width: 0;
}

.pass-details-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 1.15rem 1.5rem;
    margin-bottom: 1.35rem;
}

.pass-detail-item {
    min-width: 0;
}

.pass-detail-label {
    font-size: 0.72rem;
    color: var(--text-muted, #64748b);
    text-transform: uppercase;
    font-weight: 700;
    letter-spacing: 0.05em;
    display: block;
    margin-bottom: 0.25rem;
}

.pass-detail-value {
    font-size: 0.95rem;
    color: var(--text, #0f172a);
    font-weight: 600;
    word-break: break-word;
}

.pass-ref-code {
    font-size: 0.85rem;
    font-family: monospace;
}

/* Admission Code Block */
.pass-admission-code-block {
    background: rgba(var(--brand), 0.06);
    border: 1px dashed rgba(var(--brand), 0.35);
    border-radius: 12px;
    padding: 0.85rem 1.15rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.75rem;
    flex-wrap: wrap;
}

.pass-admission-code {
    font-size: clamp(1rem, 3vw, 1.35rem);
    font-family: monospace;
    font-weight: 800;
    color: rgb(var(--brand));
    letter-spacing: 0.08em;
    margin-top: 0.15rem;
    word-break: break-all;
}

.pass-valid-badge {
    font-size: 0.75rem;
    background: #10b981;
    color: #ffffff;
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    white-space: nowrap;
    flex-shrink: 0;
}

/* QR Code Stub */
.ticket-qr-stub {
    text-align: center;
    padding-left: 1.5rem;
    border-left: 2px dashed var(--border-color, rgba(0,0,0,0.12));
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
}

.qr-code-frame {
    background: #ffffff;
    padding: 0.65rem;
    border-radius: 14px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 6px 16px rgba(0,0,0,0.06);
    display: inline-block;
}

.qr-scan-label {
    font-size: 0.68rem;
    font-weight: 800;
    color: var(--text-muted, #64748b);
    letter-spacing: 0.06em;
    margin-top: 0.5rem;
    display: block;
}

/* Pass Footer */
.ticket-pass-footer {
    background: rgba(0,0,0,0.025);
    border-top: 1px solid var(--border-color, rgba(0,0,0,0.08));
    padding: 0.65rem 1.75rem;
    font-size: 0.75rem;
    color: var(--text-muted, #64748b);
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 0.5rem;
}

/* ═══════════════════════════════════════════════════════════
   Responsive: Tablet (≤ 768px)
   ═══════════════════════════════════════════════════════════ */
@media (max-width: 768px) {
    .success-hero {
        margin-bottom: 1.75rem;
    }

    .success-action-bar {
        flex-direction: column;
        align-items: stretch;
        gap: 0.75rem;
        padding: 0.85rem 1rem;
    }

    .success-action-bar > div {
        flex-direction: column;
        width: 100%;
    }

    .success-browse-btn,
    .success-print-btn,
    .success-action-bar a,
    .success-action-bar button {
        width: 100%;
        text-align: center;
        justify-content: center;
        box-sizing: border-box;
    }

    .ticket-receipt-card {
        padding: 1.25rem !important;
        border-radius: 16px !important;
    }

    .ticket-pass-header {
        padding: 1rem 1.25rem;
    }

    .ticket-pass-body {
        grid-template-columns: 1fr;
        gap: 1.5rem;
        padding: 1.25rem;
    }

    .ticket-qr-stub {
        padding-left: 0;
        padding-top: 1.25rem;
        border-left: none;
        border-top: 2px dashed var(--border-color, rgba(0,0,0,0.12));
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
        gap: 0.5rem;
    }

    .qr-code-frame img,
    .ticket-qr-real-img {
        width: 125px !important;
        height: 125px !important;
    }

    .pass-details-grid {
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
    }

    .ticket-pass-footer {
        padding: 0.6rem 1.25rem;
    }

    .ticket-pass-card {
        border-radius: 16px;
        margin-bottom: 1.5rem;
    }
}

/* ═══════════════════════════════════════════════════════════
   Responsive: Mobile (≤ 480px)
   ═══════════════════════════════════════════════════════════ */
@media (max-width: 480px) {
    .printable-ticket-wrapper {
        padding: 0 0.5rem;
    }

    .success-hero {
        padding: 0 0.75rem;
        margin-bottom: 1.25rem;
    }

    .success-check-icon {
        width: 52px;
        height: 52px;
        font-size: 1.6rem;
    }

    .success-action-bar {
        padding: 0.75rem;
        border-radius: 12px;
        margin-bottom: 1.25rem;
    }

    .ticket-receipt-card {
        padding: 1rem !important;
        border-radius: 14px !important;
    }

    .ticket-pass-header {
        padding: 0.85rem 1rem;
        gap: 0.5rem;
    }

    .pass-logo-icon {
        width: 32px;
        height: 32px;
        font-size: 0.95rem;
        border-radius: 8px;
    }

    .pass-header-eyebrow {
        font-size: 0.62rem;
    }

    .pass-type-badge {
        font-size: 0.68rem;
        padding: 0.25rem 0.65rem;
    }

    .ticket-pass-body {
        padding: 1rem;
        gap: 1rem;
    }

    .pass-details-grid {
        grid-template-columns: 1fr;
        gap: 0.85rem;
    }

    .pass-admission-code-block {
        padding: 0.75rem;
        flex-direction: column;
        align-items: flex-start;
        gap: 0.6rem;
    }

    .ticket-qr-stub {
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
        gap: 0.4rem;
        padding-top: 1rem;
    }

    .qr-code-frame {
        padding: 0.5rem;
    }

    .qr-code-frame img,
    .ticket-qr-real-img {
        width: 120px !important;
        height: 120px !important;
    }

    .ticket-pass-footer {
        padding: 0.55rem 1rem;
        font-size: 0.7rem;
        flex-direction: column;
        align-items: center;
        text-align: center;
        gap: 0.25rem;
    }

    .ticket-pass-card {
        border-radius: 14px;
        margin-bottom: 1.25rem;
    }
}
</style>
@endsection
