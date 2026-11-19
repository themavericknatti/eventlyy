<?php
/**
 * Mobile-First VIP Admission Pass View (Optimized for WhatsApp delivery)
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
?>

@extends('layouts/main')

@section('title')
<?= $isUnlocked ? ('Pass #' . e($ticketCode)) : 'Protected Admission Pass' ?> — <?= e($ticket['event_name']) ?>
@endsection

@section('style')
<style>
.vip-pass-wrapper {
    min-height: calc(100vh - 80px);
    padding: 2.5rem 1rem 4rem;
    display: flex;
    justify-content: center;
    align-items: flex-start;
    background: radial-gradient(circle at 50% 10%, rgba(var(--primary-rgb, 16, 138, 85), 0.12), transparent 70%),
                var(--bg-body, #0a0e17);
}

.vip-pass-card {
    width: 100%;
    max-width: 480px;
    background: var(--bg-surface, #111827);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 24px;
    box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.5), 0 0 40px rgba(var(--primary-rgb, 16, 138, 85), 0.15);
    overflow: hidden;
    position: relative;
}

.vip-pass-banner {
    position: relative;
    height: 180px;
    background: linear-gradient(135deg, rgba(var(--primary-rgb, 16, 138, 85), 0.4), #111827);
    overflow: hidden;
}

.vip-pass-banner img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    opacity: 0.75;
}

.vip-pass-banner-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(to bottom, rgba(0,0,0,0.2) 0%, rgba(17,24,39,0.95) 100%);
}

.vip-pass-badge-pill {
    position: absolute;
    top: 1rem;
    left: 1rem;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 0.35rem 0.85rem;
    background: rgba(0, 0, 0, 0.65);
    backdrop-filter: blur(8px);
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 700;
    color: #fff;
    letter-spacing: 0.05em;
    text-transform: uppercase;
}

.vip-pass-status-pill {
    position: absolute;
    top: 1rem;
    right: 1rem;
    padding: 0.35rem 0.85rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    backdrop-filter: blur(8px);
}

.vip-pass-status--valid {
    background: rgba(16, 185, 129, 0.2);
    border: 1px solid #10b981;
    color: #34d399;
}

.vip-pass-status--checked {
    background: rgba(59, 130, 246, 0.2);
    border: 1px solid #3b82f6;
    color: #60a5fa;
}

.vip-pass-status--cancelled {
    background: rgba(239, 68, 68, 0.2);
    border: 1px solid #ef4444;
    color: #f87171;
}

.vip-pass-status--locked {
    background: rgba(245, 158, 11, 0.2);
    border: 1px solid #f59e0b;
    color: #fbbf24;
}

.vip-pass-body {
    padding: 1.5rem 1.75rem 2rem;
    text-align: center;
}

.vip-pass-title {
    font-size: 1.45rem;
    font-weight: 800;
    color: #fff;
    margin: 0 0 0.5rem;
    line-height: 1.3;
}

.vip-pass-meta-strip {
    display: flex;
    justify-content: center;
    gap: 1rem;
    flex-wrap: wrap;
    font-size: 0.85rem;
    color: #94a3b8;
    margin-bottom: 1.5rem;
}

.vip-pass-meta-strip span {
    display: inline-flex;
    align-items: center;
    gap: 5px;
}

/* Scannable QR Container (Visible ONLY when Unlocked) */
.vip-qr-box {
    margin: 1.25rem auto 1.5rem;
    width: 250px;
    height: 250px;
    background: #ffffff;
    border-radius: 20px;
    padding: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4), 0 0 25px rgba(var(--primary-rgb, 16, 138, 85), 0.3);
}

.vip-qr-box img {
    width: 100%;
    height: 100%;
    display: block;
    object-fit: contain;
}

/* Scanner Corners Effect */
.vip-qr-box::before, .vip-qr-box::after {
    content: '';
    position: absolute;
    width: 24px;
    height: 24px;
    border-color: var(--primary, #108a55);
    border-style: solid;
    pointer-events: none;
}
.vip-qr-box::before {
    top: -4px;
    left: -4px;
    border-width: 4px 0 0 4px;
    border-top-left-radius: 12px;
}
.vip-qr-box::after {
    bottom: -4px;
    right: -4px;
    border-width: 0 4px 4px 0;
    border-bottom-right-radius: 12px;
}

/* Dedicated High-Contrast PIN Lock Container */
.vip-pin-lock-container {
    margin: 1.25rem auto 1.5rem;
    width: 100%;
    max-width: 380px;
    background: #1e293b;
    border: 1.5px solid rgba(255, 255, 255, 0.15);
    border-radius: 20px;
    padding: 1.75rem 1.4rem;
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    box-shadow: 0 16px 40px rgba(0, 0, 0, 0.45), 0 0 35px rgba(99, 102, 241, 0.15);
    box-sizing: border-box;
}

.vip-pin-lock-icon {
    width: 62px;
    height: 62px;
    border-radius: 50%;
    background: rgba(99, 102, 241, 0.15);
    border: 1.5px solid rgba(99, 102, 241, 0.4);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.85rem;
    margin-bottom: 0.75rem;
}

.vip-pin-heading {
    font-size: 1.25rem;
    font-weight: 800;
    color: #ffffff;
    margin: 0 0 0.35rem;
}

.vip-pin-subheading {
    font-size: 0.85rem;
    color: #94a3b8;
    margin: 0 0 1.25rem;
    line-height: 1.45;
    max-width: 290px;
}

.vip-pin-input-field {
    width: 160px;
    text-align: center;
    font-size: 2rem;
    font-weight: 800;
    letter-spacing: 0.35em;
    padding: 0.65rem 0.75rem;
    border: 2px solid #334155;
    border-radius: 12px;
    background: #0f172a;
    color: #34d399;
    outline: none;
    font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
    transition: all 0.2s ease;
    box-shadow: inset 0 2px 4px rgba(0,0,0,0.3);
}

.vip-pin-input-field:focus {
    border-color: #10b981;
    background: #090d16;
    box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.25), inset 0 2px 4px rgba(0,0,0,0.3);
}

.vip-pin-input-field::placeholder {
    color: #475569;
    letter-spacing: 0.35em;
}

.vip-pin-submit-btn {
    width: 100%;
    max-width: 260px;
    margin-top: 1rem;
    padding: 0.85rem 1.5rem;
    border: none;
    border-radius: 12px;
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: #ffffff;
    font-weight: 800;
    font-size: 0.95rem;
    cursor: pointer;
    box-shadow: 0 4px 14px rgba(16, 185, 129, 0.35);
    transition: all 0.15s ease;
}

.vip-pin-submit-btn:hover {
    background: linear-gradient(135deg, #059669 0%, #047857 100%);
    transform: translateY(-1px);
    box-shadow: 0 6px 20px rgba(16, 185, 129, 0.45);
}

.vip-pin-resend-link {
    margin-top: 1rem;
    background: none;
    border: none;
    color: #38bdf8;
    font-size: 0.84rem;
    font-weight: 700;
    cursor: pointer;
    text-decoration: underline;
    transition: color 0.15s ease;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}

.vip-pin-resend-link:hover {
    color: #7dd3fc;
}

.vip-pin-alert {
    width: 100%;
    border-radius: 10px;
    padding: 0.6rem 0.85rem;
    font-size: 0.82rem;
    font-weight: 600;
    margin-bottom: 0.85rem;
    box-sizing: border-box;
    text-align: center;
}

.vip-pin-alert--error {
    background: rgba(239, 68, 68, 0.18);
    border: 1px solid rgba(239, 68, 68, 0.4);
    color: #fca5a5;
}

.vip-pin-alert--success {
    background: rgba(16, 185, 129, 0.18);
    border: 1px solid rgba(16, 185, 129, 0.4);
    color: #6ee7b7;
}

.vip-pass-code-pill {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: rgba(255, 255, 255, 0.05);
    border: 1px dashed rgba(255, 255, 255, 0.2);
    padding: 0.45rem 1.25rem;
    border-radius: 12px;
    font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
    font-size: 1.15rem;
    font-weight: 800;
    color: var(--primary, #108a55);
    letter-spacing: 0.1em;
    margin-bottom: 1.5rem;
}

.vip-pass-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.75rem;
    background: rgba(255, 255, 255, 0.03);
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 14px;
    padding: 1rem;
    margin-bottom: 1.5rem;
    text-align: left;
}

.vip-pass-item label {
    display: block;
    font-size: 0.7rem;
    text-transform: uppercase;
    font-weight: 700;
    color: #64748b;
    margin-bottom: 2px;
}

.vip-pass-item strong {
    font-size: 0.92rem;
    color: #f1f5f9;
}

.vip-pass-actions {
    display: flex;
    flex-direction: column;
    gap: 0.65rem;
}

.vip-btn-whatsapp {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    width: 100%;
    padding: 0.85rem 1rem;
    background: #25D366;
    color: #ffffff;
    font-weight: 800;
    font-size: 0.95rem;
    border-radius: 12px;
    border: none;
    cursor: pointer;
    text-decoration: none;
    transition: transform 0.15s ease, box-shadow 0.15s ease;
}
.vip-btn-whatsapp:hover {
    background: #20bd5a;
    transform: translateY(-1px);
    box-shadow: 0 6px 20px rgba(37, 211, 102, 0.35);
    color: #fff;
}

.vip-btn-secondary {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    width: 100%;
    padding: 0.75rem 1rem;
    background: rgba(255, 255, 255, 0.08);
    border: 1px solid rgba(255, 255, 255, 0.15);
    color: #e2e8f0;
    font-weight: 700;
    font-size: 0.88rem;
    border-radius: 12px;
    cursor: pointer;
    text-decoration: none;
    transition: all 0.15s ease;
}
.vip-btn-secondary:hover {
    background: rgba(255, 255, 255, 0.12);
    color: #fff;
}

.vip-pass-footer-note {
    margin-top: 1.5rem;
    font-size: 0.78rem;
    color: #64748b;
    line-height: 1.5;
}

@media print {
    body { background: #fff !important; color: #000 !important; }
    .vip-pass-wrapper { padding: 0 !important; background: none !important; }
    .vip-pass-card { max-width: 100% !important; border: 1px solid #ccc !important; box-shadow: none !important; background: #fff !important; }
    .vip-pass-title, .vip-pass-item strong { color: #000 !important; }
    .vip-pass-meta-strip { color: #333 !important; }
    .vip-pass-actions, .vip-pass-badge-pill, .no-print { display: none !important; }
}
</style>
@endsection

@section('content')
<div class="vip-pass-wrapper">
    <div class="vip-pass-card">
        
        <!-- Top Event Poster Banner -->
        <div class="vip-pass-banner">
            <?php if (!empty($evImg)): ?>
                <img src="<?= e($evImg) ?>" alt="<?= e($ticket['event_name']) ?>" />
            <?php endif; ?>
            <div class="vip-pass-banner-overlay"></div>

            <div class="vip-pass-badge-pill">
                <span>🎟️</span>
                <span><?= e($siteTitle) ?> VIP PASS</span>
            </div>

            <?php if ($isCheckedIn): ?>
                <div class="vip-pass-status-pill vip-pass-status--checked">✓ CHECKED IN</div>
            <?php elseif ($isCancelled): ?>
                <div class="vip-pass-status-pill vip-pass-status--cancelled">CANCELLED</div>
            <?php elseif ($isLayaway): ?>
                <div class="vip-pass-status-pill" style="background:#6366f1; color:#ffffff;">⏳ INCOMPLETE LAYAWAY</div>
            <?php elseif (!$isUnlocked): ?>
                <div class="vip-pass-status-pill vip-pass-status--locked">🔒 PIN LOCKED</div>
            <?php else: ?>
                <div class="vip-pass-status-pill vip-pass-status--valid">● READY FOR SCAN</div>
            <?php endif; ?>
        </div>

        <!-- Pass Body -->
        <div class="vip-pass-body">
            <h1 class="vip-pass-title"><?= e($ticket['event_name']) ?></h1>
            
            <div class="vip-pass-meta-strip">
                <span>🗓️ <?= $eventDate ? date('D, M d, Y • g:i A', $eventDate) : 'Date TBA' ?></span>
                <span>📍 <?= e($ticket['event_location'] ?? 'Venue TBA') ?></span>
            </div>

            <!-- Scannable High-Res QR Code -->
            <?php if ($isLayaway): ?>
                <div class="vip-pin-lock-container">
                    <div class="vip-pin-lock-icon" style="background:rgba(99,102,241,0.15); border-color:rgba(99,102,241,0.35);">
                        <span>⏳</span>
                    </div>
                    <h2 class="vip-pin-heading" style="color:#818cf8;">Layaway Plan Inactive</h2>
                    <p class="vip-pin-subheading">
                        This admission ticket is reserved under an active layaway installment plan. Your gate QR code is automatically released upon final payment.
                    </p>
                    <a href="<?= \url('/layaway') ?>" class="vip-pin-submit-btn" style="text-decoration:none; display:inline-block; background:linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);">
                        Complete Layaway Plan →
                    </a>
                </div>
            <?php else: ?>
                <div class="vip-qr-box" title="Gate Admission QR Code — Scan at entrance">
                    <img src="<?= url('/ticket/qr/' . urlencode($ticketCode) . '?s=300') ?>" alt="Ticket QR Code #<?= e($ticketCode) ?>" />
                </div>
                <div class="vip-pass-code-pill" title="Official admission code">
                    <span><?= e($ticketCode) ?></span>
                </div>
                <?php if (!empty($ticket['security_pin'])): ?>
                    <div style="margin: 0 auto 0.75rem; font-size: 0.78rem; color: rgba(255,255,255,0.7); display: inline-flex; align-items: center; gap: 5px; background: rgba(255,255,255,0.06); padding: 4px 12px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.12);">
                        <span>🔐 Security PIN:</span> <strong style="font-family: monospace; letter-spacing: 0.12em; color: #34d399; font-size: 0.88rem;"><?= e($ticket['security_pin']) ?></strong>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <!-- Pass Holder & Tier Details Grid -->
            <div class="vip-pass-grid">
                <div class="vip-pass-item">
                    <label>Attendee Name</label>
                    <strong><?= e($ticket['attendee_name']) ?></strong>
                </div>
                <div class="vip-pass-item">
                    <label>Ticket Tier</label>
                    <strong><?= e($ticket['ticket_tier'] ?? 'Admission') ?><?= $admits > 1 ? " ({$admits} Admits)" : "" ?></strong>
                </div>
                <div class="vip-pass-item">
                    <label>Gate Status</label>
                    <?php if ($isLayaway): ?>
                        <strong style="color: #818cf8;">Payment Required</strong>
                    <?php else: ?>
                        <strong style="color: <?= $isCheckedIn ? '#60a5fa' : '#34d399' ?>;">
                            <?= $isCheckedIn ? 'Checked In' : 'Ready for Scan' ?>
                        </strong>
                    <?php endif; ?>
                </div>
                <div class="vip-pass-item">
                    <label>Issued Via</label>
                    <strong><?= e($siteTitle) ?> Verified</strong>
                </div>
            </div>

            <!-- Merchandise & Add-Ons Package -->
            <?php if (!empty($addons)): ?>
                <div style="background: rgba(13, 148, 136, 0.08); border: 1px solid rgba(13, 148, 136, 0.25); border-radius: 14px; padding: 0.95rem 1rem; margin-bottom: 1.5rem; text-align: left;">
                    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:0.6rem; border-bottom: 1px solid rgba(13, 148, 136, 0.15); padding-bottom: 0.4rem;">
                        <span style="font-size:0.75rem; font-weight:800; color:#0d9488; text-transform:uppercase; letter-spacing:0.05em; display:flex; align-items:center; gap:5px;">
                            <span>🛍️</span> Merchandise &amp; Add-Ons
                        </span>
                        <span style="font-size:0.7rem; font-weight:700; color:#0d9488; background:rgba(13, 148, 136, 0.15); padding:2px 7px; border-radius:99px;">
                            Official Voucher
                        </span>
                    </div>
                    <div style="display:flex; flex-direction:column; gap:0.5rem;">
                        <?php foreach ($addons as $addon): 
                            $adImg = $addon['addon_image'] ?? ($addon['image'] ?? null);
                        ?>
                            <div style="display:flex; justify-content:space-between; align-items:center; gap:0.75rem; font-size:0.85rem; padding:4px 0;">
                                <div style="display:flex; align-items:center; gap:0.65rem; min-width:0;">
                                    <?php if (!empty($adImg)): ?>
                                        <img src="<?= e($adImg) ?>" alt="<?= e($addon['addon_name'] ?? 'Merch') ?>" style="width:36px; height:36px; border-radius:7px; object-fit:cover; border:1px solid rgba(13,148,136,0.3); flex-shrink:0;" />
                                    <?php else: ?>
                                        <div style="width:36px; height:36px; border-radius:7px; background:rgba(13,148,136,0.12); display:flex; align-items:center; justify-content:center; font-size:1.1rem; flex-shrink:0;">🛍️</div>
                                    <?php endif; ?>
                                    <strong style="color:#f1f5f9; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                                        <span style="color:#0d9488;"><?= (int)$addon['quantity'] ?>×</span> <?= e($addon['addon_name'] ?? 'Merchandise') ?>
                                    </strong>
                                </div>
                                <span style="font-size:0.72rem; font-weight:700; color:#0d9488; background:rgba(13,148,136,0.15); padding:2px 6px; border-radius:6px; flex-shrink:0;">
                                    <?= ucfirst($addon['redemption_status'] ?? 'Pending') ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Action Buttons: WhatsApp & Print (Only visible when Unlocked) -->
            <?php if ($isUnlocked): ?>
                <div class="vip-pass-actions no-print">
                    <?php
                        $tData = ($ticket instanceof \Ace\ViewData) ? $ticket->toArray() : (array)$ticket;
                        $waMsg = \App\Services\WhatsAppService::buildTicketPassMessage(
                            ['name' => $tData['event_name'] ?? '', 'date' => $tData['event_date'] ?? null, 'location' => $tData['event_location'] ?? ''],
                            $tData,
                            $siteTitle
                        );
                        $waUrl = \App\Services\WhatsAppService::generateDirectWhatsAppUrl('', $waMsg);
                    ?>
                    <a href="<?= e($waUrl) ?>" target="_blank" rel="noopener" class="vip-btn-whatsapp">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.711 2.598 2.664-.698c.969.586 1.861.947 3.018.947 3.174 0 5.79-2.586 5.79-5.767.001-3.18-2.617-5.767-5.789-5.767m7.859 5.766c0 4.331-3.523 7.854-7.859 7.854-1.328 0-2.56-.344-3.644-.949l-4.048 1.061 1.08-3.945c-.714-1.156-1.127-2.518-1.127-3.971 0-4.331 3.523-7.854 7.859-7.854 4.336 0 7.859 3.523 7.859 7.854"/></svg>
                        <span>Share Pass to WhatsApp</span>
                    </a>

                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:0.5rem;">
                        <button type="button" onclick="window.print()" class="vip-btn-secondary">
                            <span>🖨️</span> Print / Save PDF
                        </button>
                        <button type="button" id="btn-trigger-story-pass" class="vip-btn-secondary" style="color:var(--primary,#108a55);"
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

                <p class="vip-pass-footer-note">
                    💡 <strong>Gate Instructions:</strong> Please present this QR pass at the entrance gate. Set your screen brightness to maximum for fast optical verification.
                </p>
            <?php else: ?>
                <!-- Locked State Security Notice -->
                <div style="background: rgba(255, 255, 255, 0.04); border: 1px dashed rgba(255, 255, 255, 0.15); border-radius: 14px; padding: 1.1rem 1.25rem; font-size: 0.82rem; color: #94a3b8; line-height: 1.5; margin-bottom: 1.5rem; text-align: center;">
                    🔒 <strong>Pass Secured:</strong> Your admission QR code and sharing options remain protected until unlocked. Check your email for your 4-digit Security PIN, or click <em>"Send PIN to my Email"</em> above.
                </div>

                <div style="display:flex; justify-content:center;">
                    <button type="button" id="btn-trigger-story-pass" class="vip-btn-secondary" style="max-width:240px; color:var(--primary,#108a55);"
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
</div>

<?php echo $this->include('partials/story-card-modal', []); ?>
@endsection
