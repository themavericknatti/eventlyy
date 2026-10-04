<?php

/**
 * Order Success Page — VIP Printable Pass View
 *
 * Displays purchase confirmation, issued ticket passes, QR code visualizer, and print/PDF pass generator.
 *
 * @package Eventlyy
 * @subpackage Views/Checkout
 * @author Celio Natti
 * @version 3.0.0
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

$eventDate = !empty($order['event_date']) ? strtotime($order['event_date']) : null;
$totalAmount = (float)($order['total_amount'] ?? 0);
$isFree = ($totalAmount <= 0.00);
$ticketCount = count($order['tickets'] ?? []);
$orderRef = $order['payment_reference'] ?? ('ORD-' . time());

$calendarEvent = [
    'name' => $order['event_name'] ?? 'Event',
    'date' => $order['event_date'] ?? date('Y-m-d H:i:s'),
    'location' => $order['event_location'] ?? '',
    'description' => 'Tickets confirmed via Eventlyy. Order #' . $orderRef,
    'id' => $order['event_id'] ?? 1,
    'slug' => $order['event_slug'] ?? ($order['event_id'] ?? 1),
];
?>

@extends('layouts/main')

@section('title')
Order Success — <?= e($order['event_name'] ?? 'Purchase Confirmed') ?>
@endsection

@section('content')

<!-- ═══ Hero Success Confirmation ═══ -->
<section class="suc-hero no-print">
    <div class="suc-check-ring">
        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="20 6 9 17 4 12"></polyline>
        </svg>
    </div>
    <span class="suc-eyebrow"><?= $isFree ? '🎉 Registration Confirmed' : '✓ Purchase Confirmed' ?></span>
    <h1 class="suc-title">You're Going to <?= e($order['event_name']) ?>!</h1>
    <p class="suc-subtitle">
        Your order is complete. Digital admission passes have been delivered to <strong><?= e($order['attendee_email']) ?></strong>.
    </p>
</section>

<div class="suc-container">
    
    <!-- ═══ Action Toolbar ═══ -->
    <div class="suc-actions no-print">
        <a href="{{ route('/') }}" class="suc-btn suc-btn--secondary">
            ← Browse More Events
        </a>
        <div class="suc-actions__right">
            <a href="{{ route('profile') }}" class="suc-btn suc-btn--secondary">
                👤 View My Tickets
            </a>
            <button type="button" onclick="window.print()" class="suc-btn suc-btn--primary">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="6 9 6 2 18 2 18 9"></polyline>
                    <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                    <rect x="6" y="14" width="12" height="8"></rect>
                </svg>
                Print Passes &amp; Receipt
            </button>
        </div>
    </div>

    <!-- ═══ Official Transaction Receipt ═══ -->
    <div class="suc-receipt">
        <div class="suc-receipt__top">
            <div>
                <span class="suc-receipt__tag">🧾 Official Transaction Receipt</span>
                <h3 class="suc-receipt__order-no">Order #<?= e($orderRef) ?></h3>
            </div>
            <div class="suc-receipt__status-wrap">
                <span class="suc-receipt__status-badge<?= $isFree ? ' suc-receipt__status-badge--free' : '' ?>">
                    <?= $isFree ? '✓ CONFIRMED (FREE)' : '✓ PAID &amp; CONFIRMED' ?>
                </span>
                <span class="suc-receipt__method">
                    Method: <strong><?= e($order['payment_method'] ?? ($isFree ? 'FREE' : 'CARD')) ?></strong>
                </span>
            </div>
        </div>

        <div class="suc-receipt__grid">
            <div class="suc-receipt__item">
                <span class="suc-receipt__label">Billed To</span>
                <strong class="suc-receipt__val"><?= e(!empty($order['attendee_name']) && $order['attendee_name'] !== 'Attendee' ? $order['attendee_name'] : (!empty($order['tickets'][0]['attendee_name']) ? $order['tickets'][0]['attendee_name'] : 'Attendee')) ?></strong>
                <span class="suc-receipt__sub"><?= e(!empty($order['attendee_email']) ? $order['attendee_email'] : ($order['tickets'][0]['attendee_email'] ?? '')) ?></span>
            </div>
            <div class="suc-receipt__item">
                <span class="suc-receipt__label">Event</span>
                <strong class="suc-receipt__val"><?= e($order['event_name']) ?></strong>
                <span class="suc-receipt__sub" title="<?= e($order['event_location']) ?>">📍 <?= e($order['event_location']) ?></span>
            </div>
            <div class="suc-receipt__item">
                <span class="suc-receipt__label">Date &amp; Time</span>
                <strong class="suc-receipt__val"><?= $eventDate ? date('M d, Y', $eventDate) : 'Date TBA' ?></strong>
                <span class="suc-receipt__sub"><?= $eventDate ? date('g:i A', $eventDate) : '' ?></span>
            </div>
            <div class="suc-receipt__item">
                <span class="suc-receipt__label">Total Amount Paid</span>
                <strong class="suc-receipt__val suc-receipt__val--amount"><?= $isFree ? 'Free' : format_price($totalAmount) ?></strong>
                <span class="suc-receipt__sub"><?= $ticketCount ?> ticket<?= $ticketCount === 1 ? '' : 's' ?> issued</span>
            </div>
        </div>
    </div>

    <!-- ═══ Add to Calendar Bar ═══ -->
    <div class="suc-calendar no-print">
        <div class="suc-calendar__info">
            <div class="suc-calendar__icon">📅</div>
            <div>
                <strong>Add Event to Your Calendar</strong>
                <span>Set automated reminders on your phone or device so you're ready on event day.</span>
            </div>
        </div>
        <div class="suc-calendar__btns">
            <a href="<?= event_google_calendar_url($calendarEvent) ?>" target="_blank" rel="noopener" class="suc-cal-btn" title="Add to Google Calendar">
                Google
            </a>
            <a href="<?= event_ics_url($calendarEvent) ?>" download class="suc-cal-btn" title="Download Apple iCal file">
                Apple iCal
            </a>
            <a href="<?= event_outlook_calendar_url($calendarEvent) ?>" target="_blank" rel="noopener" class="suc-cal-btn" title="Add to Outlook Calendar">
                Outlook
            </a>
        </div>
    </div>

    <!-- ═══ VIP Digital Admission Passes Loop ═══ -->
    <div class="suc-passes-list">
        <?php foreach ($order['tickets'] as $index => $t): 
            $tCode = $t['ticket_code'] ?? ($t['id'] ?? 'EV-' . ($index + 1));
            $qrSrc = ticket_qr_url($tCode, 240);
            $ticketType = !empty($t['ticket_type']) ? $t['ticket_type'] : 'General Admission';
            $holderName = !empty($t['attendee_name']) && $t['attendee_name'] !== 'Attendee' 
                ? $t['attendee_name'] 
                : (!empty($order['attendee_name']) && $order['attendee_name'] !== 'Attendee' ? $order['attendee_name'] : 'Attendee');
        ?>
            <div class="suc-pass-card">
                
                <!-- Pass Header -->
                <div class="suc-pass-header">
                    <div class="suc-pass-header__left">
                        <div class="suc-pass-logo">
                            <?= e(mb_substr($siteTitle, 0, 1)) ?>
                        </div>
                        <div>
                            <span class="suc-pass-eyebrow"><?= e($siteTitle) ?> • OFFICIAL ADMISSION PASS</span>
                            <h2 class="suc-pass-event"><?= e($order['event_name']) ?></h2>
                        </div>
                    </div>
                    <div>
                        <span class="suc-pass-type-badge"><?= e($ticketType) ?></span>
                    </div>
                </div>

                <!-- Pass Body -->
                <div class="suc-pass-body">
                    
                    <!-- Left: Details -->
                    <div class="suc-pass-info">
                        <div class="suc-pass-meta-grid">
                            <div class="suc-pass-meta-item">
                                <span class="suc-pass-meta-label">Date &amp; Time</span>
                                <strong class="suc-pass-meta-val">📅 <?= $eventDate ? date('D, M d, Y • g:i A', $eventDate) : 'Date TBA' ?></strong>
                            </div>
                            <div class="suc-pass-meta-item">
                                <span class="suc-pass-meta-label">Venue Location</span>
                                <strong class="suc-pass-meta-val" title="<?= e($order['event_location']) ?>">📍 <?= e($order['event_location']) ?></strong>
                            </div>
                            <div class="suc-pass-meta-item">
                                <span class="suc-pass-meta-label">Ticket Holder</span>
                                <strong class="suc-pass-meta-val">👤 <?= e($holderName) ?></strong>
                            </div>
                            <div class="suc-pass-meta-item">
                                <span class="suc-pass-meta-label">Payment Reference</span>
                                <strong class="suc-pass-meta-val suc-pass-ref"><?= e($orderRef) ?></strong>
                            </div>
                        </div>

                        <!-- Unique Admission Code Box -->
                        <div class="suc-pass-code-box">
                            <div>
                                <span class="suc-pass-code-label">Unique Admission Code</span>
                                <div class="suc-pass-code-val"><?= e($tCode) ?></div>
                            </div>
                            <div class="suc-pass-code-actions">
                                <button type="button" class="suc-copy-code-btn" data-code="<?= e($tCode) ?>" title="Copy ticket code">
                                    📋 Copy
                                </button>
                                <span class="suc-pass-valid-tag">Valid Pass</span>
                            </div>
                        </div>
                    </div>

                    <!-- Right: QR Code Stub -->
                    <div class="suc-pass-qr-stub">
                        <div class="suc-qr-frame" title="Scan this QR code at the entrance gate">
                            <img src="<?= $qrSrc ?>" 
                                 alt="QR Code for Ticket <?= e($tCode) ?>" 
                                 width="135" height="135" 
                                 class="suc-qr-img" />
                        </div>
                        <span class="suc-qr-label">SCAN AT ENTRY GATE</span>
                        <span class="suc-qr-code-text"><?= e($tCode) ?></span>
                    </div>
                </div>

                <!-- Pass Footer -->
                <div class="suc-pass-footer">
                    <span>🔒 Secure Gate-Verifiable Admission Pass</span>
                    <span>Pass <?= $index + 1 ?> of <?= $ticketCount ?></span>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

</div>

<!-- Copy Toast Notification -->
<div id="suc-toast-box" class="no-print"></div>

<!-- ═══ Success Client Script ═══ -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const copyBtns = document.querySelectorAll('.suc-copy-code-btn');
    const toastBox = document.getElementById('suc-toast-box');

    function showToast(msg) {
        if (!toastBox) return;
        const toast = document.createElement('div');
        toast.className = 'suc-toast';
        toast.textContent = msg;
        toastBox.appendChild(toast);
        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateY(10px)';
            setTimeout(() => toast.remove(), 250);
        }, 2200);
    }

    copyBtns.forEach(btn => {
        btn.addEventListener('click', function () {
            const code = this.getAttribute('data-code');
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(code).then(() => {
                    showToast('📋 Admission code copied: ' + code);
                    this.textContent = '✓ Copied';
                    setTimeout(() => this.textContent = '📋 Copy', 1800);
                }).catch(() => fallback(code, this));
            } else {
                fallback(code, this);
            }
        });
    });

    function fallback(text, btn) {
        const temp = document.createElement('textarea');
        temp.value = text;
        temp.style.position = 'fixed';
        temp.style.left = '-9999px';
        document.body.appendChild(temp);
        temp.select();
        try {
            document.execCommand('copy');
            showToast('📋 Admission code copied: ' + text);
            if (btn) {
                btn.textContent = '✓ Copied';
                setTimeout(() => btn.textContent = '📋 Copy', 1800);
            }
        } catch (e) {}
        document.body.removeChild(temp);
    }
});
</script>

<style>
/* ═══════════════════════════════════════════════════════════
   Success Page — Comprehensive Responsive & Print Styles (.suc-*)
   ═══════════════════════════════════════════════════════════ */

.suc-hero {
    text-align: center;
    max-width: 680px;
    margin: 1.5rem auto 2.25rem;
    padding: 0 1rem;
    display: flex;
    flex-direction: column;
    align-items: center;
}

.suc-check-ring {
    width: 64px;
    height: 64px;
    border-radius: 50%;
    background: rgba(16, 185, 129, 0.12);
    border: 2px solid rgba(16, 185, 129, 0.35);
    color: #10b981;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 1.15rem;
    box-shadow: 0 0 24px rgba(16, 185, 129, 0.2);
    animation: sucPulse 2.2s infinite;
}

@keyframes sucPulse {
    0%, 100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.4); }
    50% { transform: scale(1.04); box-shadow: 0 0 0 8px rgba(16, 185, 129, 0); }
}

.suc-eyebrow {
    display: inline-block;
    font-size: 0.8rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: #10b981;
    margin-bottom: 0.35rem;
}

.suc-title {
    font-size: clamp(1.75rem, 3.8vw, 2.5rem);
    font-weight: 900;
    letter-spacing: -0.025em;
    line-height: 1.2;
    color: rgb(var(--text));
    margin: 0 0 0.65rem;
    word-break: break-word;
}

.suc-subtitle {
    font-size: 1.02rem;
    color: rgb(var(--muted));
    line-height: 1.6;
    margin: 0;
}

.suc-subtitle strong {
    color: rgb(var(--text));
}

.suc-container {
    max-width: 860px;
    margin: 0 auto 4rem;
    padding: 0 1rem;
    box-sizing: border-box;
}

/* ── Action Toolbar ── */
.suc-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 0.85rem;
    background: rgb(var(--card));
    padding: 1rem 1.35rem;
    border-radius: 18px;
    border: 1.5px solid rgba(var(--muted), 0.14);
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.03);
    margin-bottom: 1.75rem;
}

.suc-actions__right {
    display: flex;
    align-items: center;
    gap: 0.65rem;
    flex-wrap: wrap;
}

.suc-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    padding: 0.65rem 1.15rem;
    border-radius: 12px;
    font-size: 0.88rem;
    font-weight: 700;
    text-decoration: none;
    cursor: pointer;
    transition: all var(--duration-fast) var(--ease-out);
    white-space: nowrap;
    border: none;
}

.suc-btn--secondary {
    background: rgba(var(--muted), 0.08);
    color: rgb(var(--text));
    border: 1px solid rgba(var(--muted), 0.18);
}

.suc-btn--secondary:hover {
    background: rgba(var(--brand), 0.1);
    color: rgb(var(--brand));
    border-color: rgba(var(--brand), 0.3);
    transform: translateY(-1px);
}

.suc-btn--primary {
    background: linear-gradient(135deg, rgb(var(--brand)), color-mix(in srgb, rgb(var(--brand)) 80%, rgb(var(--accent))));
    color: #fff;
    box-shadow: var(--shadow-brand);
}

.suc-btn--primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 24px rgba(var(--brand), 0.35);
    filter: brightness(1.05);
}

/* ── Official Transaction Receipt ── */
.suc-receipt {
    background: rgb(var(--card));
    border: 1.5px solid rgba(var(--muted), 0.14);
    border-radius: 20px;
    padding: 1.6rem 1.85rem;
    margin-bottom: 2rem;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.04);
}

.suc-receipt__top {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 0.85rem;
    border-bottom: 1px solid rgba(var(--muted), 0.12);
    padding-bottom: 1.1rem;
    margin-bottom: 1.35rem;
}

.suc-receipt__tag {
    font-size: 0.72rem;
    text-transform: uppercase;
    font-weight: 800;
    color: rgb(var(--brand));
    letter-spacing: 0.06em;
    display: block;
    margin-bottom: 0.2rem;
}

.suc-receipt__order-no {
    margin: 0;
    font-size: 1.2rem;
    font-weight: 800;
    color: rgb(var(--text));
    word-break: break-all;
}

.suc-receipt__status-wrap {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 0.25rem;
}

.suc-receipt__status-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    background: rgba(16, 185, 129, 0.12);
    color: #10b981;
    font-size: 0.78rem;
    font-weight: 800;
    padding: 0.35rem 0.85rem;
    border-radius: 999px;
    border: 1px solid rgba(16, 185, 129, 0.3);
}

.suc-receipt__status-badge--free {
    background: rgba(99, 102, 241, 0.12);
    color: #6366f1;
    border-color: rgba(99, 102, 241, 0.3);
}

.suc-receipt__method {
    font-size: 0.75rem;
    color: rgb(var(--muted));
}

.suc-receipt__method strong {
    color: rgb(var(--text));
}

.suc-receipt__grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 1.25rem;
}

.suc-receipt__item {
    min-width: 0;
}

.suc-receipt__label {
    display: block;
    font-size: 0.7rem;
    color: rgb(var(--muted));
    text-transform: uppercase;
    font-weight: 700;
    letter-spacing: 0.05em;
    margin-bottom: 0.25rem;
}

.suc-receipt__val {
    display: block;
    font-size: 0.95rem;
    font-weight: 700;
    color: rgb(var(--text));
    line-height: 1.35;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.suc-receipt__val--amount {
    font-size: 1.25rem;
    font-weight: 900;
    color: rgb(var(--brand));
}

.suc-receipt__sub {
    display: block;
    font-size: 0.8rem;
    color: rgb(var(--muted));
    margin-top: 0.15rem;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

/* ── Add to Calendar Card ── */
.suc-calendar {
    background: rgba(var(--brand), 0.05);
    border: 1.5px solid rgba(var(--brand), 0.22);
    border-radius: 18px;
    padding: 1.2rem 1.5rem;
    margin-bottom: 2rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1rem;
}

.suc-calendar__info {
    display: flex;
    align-items: center;
    gap: 0.85rem;
    min-width: 240px;
    flex: 1;
}

.suc-calendar__icon {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    background: rgba(var(--brand), 0.12);
    color: rgb(var(--brand));
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.45rem;
    flex-shrink: 0;
}

.suc-calendar__info strong {
    display: block;
    font-size: 0.96rem;
    font-weight: 700;
    color: rgb(var(--text));
    margin-bottom: 0.15rem;
}

.suc-calendar__info span {
    font-size: 0.82rem;
    color: rgb(var(--muted));
    line-height: 1.45;
}

.suc-calendar__btns {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
    align-items: center;
}

.suc-cal-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.5rem 1rem;
    border-radius: 10px;
    font-size: 0.82rem;
    font-weight: 700;
    background: rgb(var(--card));
    color: rgb(var(--text));
    border: 1px solid rgba(var(--muted), 0.22);
    text-decoration: none;
    transition: all var(--duration-fast) var(--ease-out);
}

.suc-cal-btn:hover {
    background: rgba(var(--brand), 0.1);
    color: rgb(var(--brand));
    border-color: rgba(var(--brand), 0.35);
    transform: translateY(-1px);
}

/* ── VIP Digital Admission Passes ── */
.suc-passes-list {
    display: flex;
    flex-direction: column;
    gap: 2rem;
}

.suc-pass-card {
    background: rgb(var(--card));
    border: 1.5px solid rgba(var(--muted), 0.14);
    border-radius: 22px;
    overflow: hidden;
    box-shadow: 0 12px 35px rgba(0, 0, 0, 0.05);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.suc-pass-card:hover {
    box-shadow: 0 18px 45px rgba(0, 0, 0, 0.08);
}

.suc-pass-header {
    background: linear-gradient(135deg, rgb(var(--brand)) 0%, color-mix(in srgb, rgb(var(--brand)) 40%, #1e1b4b) 100%);
    color: #ffffff;
    padding: 1.25rem 1.75rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 0.85rem;
}

.suc-pass-header__left {
    display: flex;
    align-items: center;
    gap: 0.85rem;
    min-width: 0;
}

.suc-pass-logo {
    width: 40px;
    height: 40px;
    background: rgba(255, 255, 255, 0.2);
    backdrop-filter: blur(4px);
    border-radius: 11px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-weight: 800;
    font-size: 1.2rem;
    color: #ffffff;
    flex-shrink: 0;
}

.suc-pass-eyebrow {
    font-size: 0.7rem;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    opacity: 0.9;
    font-weight: 700;
    display: block;
    line-height: 1.2;
}

.suc-pass-event {
    margin: 0.2rem 0 0;
    font-size: clamp(1.05rem, 3vw, 1.35rem);
    font-weight: 800;
    color: #ffffff;
    line-height: 1.25;
    word-break: break-word;
}

.suc-pass-type-badge {
    display: inline-block;
    background: rgba(255, 255, 255, 0.22);
    backdrop-filter: blur(6px);
    padding: 0.35rem 0.95rem;
    border-radius: 999px;
    font-size: 0.78rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: #ffffff;
    white-space: nowrap;
}

/* Pass Body Grid */
.suc-pass-body {
    padding: 1.75rem;
    display: grid;
    grid-template-columns: 1fr 200px;
    gap: 2rem;
    align-items: center;
}

.suc-pass-info {
    min-width: 0;
}

.suc-pass-meta-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 1.25rem 1.5rem;
    margin-bottom: 1.5rem;
}

.suc-pass-meta-item {
    min-width: 0;
}

.suc-pass-meta-label {
    display: block;
    font-size: 0.72rem;
    color: rgb(var(--muted));
    text-transform: uppercase;
    font-weight: 700;
    letter-spacing: 0.05em;
    margin-bottom: 0.25rem;
}

.suc-pass-meta-val {
    display: block;
    font-size: 0.95rem;
    color: rgb(var(--text));
    font-weight: 700;
    word-break: break-word;
    overflow: hidden;
    text-overflow: ellipsis;
}

.suc-pass-ref {
    font-family: monospace;
    font-size: 0.88rem;
}

/* Unique Admission Code Box */
.suc-pass-code-box {
    background: rgba(var(--brand), 0.06);
    border: 1.5px dashed rgba(var(--brand), 0.35);
    border-radius: 14px;
    padding: 0.95rem 1.25rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.85rem;
    flex-wrap: wrap;
}

.suc-pass-code-label {
    display: block;
    font-size: 0.7rem;
    text-transform: uppercase;
    font-weight: 700;
    letter-spacing: 0.05em;
    color: rgb(var(--muted));
}

.suc-pass-code-val {
    font-size: clamp(1.1rem, 3.2vw, 1.45rem);
    font-family: monospace;
    font-weight: 900;
    color: rgb(var(--brand));
    letter-spacing: 0.08em;
    word-break: break-all;
    margin-top: 0.15rem;
}

.suc-pass-code-actions {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    flex-shrink: 0;
}

.suc-copy-code-btn {
    padding: 0.35rem 0.75rem;
    border-radius: 8px;
    background: rgba(var(--card), 0.9);
    border: 1px solid rgba(var(--muted), 0.25);
    color: rgb(var(--text));
    font-size: 0.78rem;
    font-weight: 700;
    cursor: pointer;
    transition: all var(--duration-fast) var(--ease-out);
}

.suc-copy-code-btn:hover {
    background: rgba(var(--brand), 0.12);
    color: rgb(var(--brand));
    border-color: rgba(var(--brand), 0.35);
}

.suc-pass-valid-tag {
    font-size: 0.75rem;
    background: #10b981;
    color: #ffffff;
    padding: 0.3rem 0.8rem;
    border-radius: 999px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    white-space: nowrap;
}

/* QR Code Stub */
.suc-pass-qr-stub {
    text-align: center;
    padding-left: 1.75rem;
    border-left: 2px dashed rgba(var(--muted), 0.2);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
}

.suc-qr-frame {
    background: #ffffff !important;
    padding: 0.65rem;
    border-radius: 16px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 6px 18px rgba(0, 0, 0, 0.07);
    display: inline-block;
}

.suc-qr-img {
    display: block;
    width: 135px;
    height: 135px;
    image-rendering: pixelated;
    border-radius: 6px;
}

.suc-qr-label {
    font-size: 0.68rem;
    font-weight: 800;
    color: rgb(var(--muted));
    letter-spacing: 0.08em;
    margin-top: 0.6rem;
    display: block;
}

.suc-qr-code-text {
    font-family: monospace;
    font-size: 0.78rem;
    font-weight: 800;
    color: rgb(var(--brand));
    margin-top: 0.2rem;
    display: block;
}

/* Pass Footer */
.suc-pass-footer {
    background: rgba(0, 0, 0, 0.025);
    border-top: 1px solid rgba(var(--muted), 0.1);
    padding: 0.75rem 1.75rem;
    font-size: 0.78rem;
    color: rgb(var(--muted));
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 0.5rem;
}

/* ── Toast Feedback ── */
#suc-toast-box {
    position: fixed;
    bottom: 24px;
    right: 24px;
    z-index: 10000;
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.suc-toast {
    background: #0f172a;
    color: #fff;
    border: 1px solid rgba(255, 255, 255, 0.2);
    padding: 0.85rem 1.35rem;
    border-radius: 12px;
    font-size: 0.88rem;
    font-weight: 700;
    box-shadow: 0 12px 36px rgba(0, 0, 0, 0.55);
    animation: sucToastPop 0.25s ease;
    transition: opacity 0.25s ease, transform 0.25s ease;
}

@keyframes sucToastPop {
    from { transform: translateY(14px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}

/* ── Dark Mode Overrides for Success (.suc-*) ── */
:root[data-theme='dark'] .suc-actions,
:root[data-theme='dark'] .suc-receipt,
:root[data-theme='dark'] .suc-pass-card {
    background: rgb(var(--card));
    border-color: rgba(255, 255, 255, 0.08);
    box-shadow: 0 12px 35px rgba(0, 0, 0, 0.35);
}

:root[data-theme='dark'] .suc-pass-footer {
    background: rgba(255, 255, 255, 0.02);
    border-color: rgba(255, 255, 255, 0.06);
}

:root[data-theme='dark'] .suc-cal-btn,
:root[data-theme='dark'] .suc-copy-code-btn {
    background: rgba(255, 255, 255, 0.06);
    border-color: rgba(255, 255, 255, 0.12);
    color: rgb(var(--text));
}

/* ═══════════════════════════════════════════════════════════
   Multi-Device Responsive Breakpoints
   ═══════════════════════════════════════════════════════════ */
@media (max-width: 820px) {
    .suc-receipt__grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 1.15rem;
    }
}

@media (max-width: 680px) {
    .suc-actions {
        flex-direction: column;
        align-items: stretch;
        padding: 0.95rem;
    }

    .suc-actions__right {
        flex-direction: column;
        width: 100%;
    }

    .suc-btn {
        width: 100%;
        justify-content: center;
        padding: 0.75rem;
    }

    .suc-receipt {
        padding: 1.25rem 1.15rem;
    }

    .suc-receipt__grid {
        grid-template-columns: 1fr;
        gap: 1rem;
    }

    .suc-receipt__status-wrap {
        align-items: flex-start;
    }

    .suc-calendar {
        flex-direction: column;
        align-items: flex-start;
        padding: 1.15rem;
    }

    .suc-calendar__btns {
        width: 100%;
    }

    .suc-cal-btn {
        flex: 1;
        justify-content: center;
        min-width: 80px;
    }

    .suc-pass-card {
        border-radius: 18px;
    }

    .suc-pass-header {
        padding: 1rem 1.15rem;
    }

    .suc-pass-body {
        grid-template-columns: 1fr;
        gap: 1.5rem;
        padding: 1.25rem;
    }

    .suc-pass-meta-grid {
        grid-template-columns: 1fr;
        gap: 0.95rem;
    }

    .suc-pass-qr-stub {
        padding-left: 0;
        padding-top: 1.5rem;
        border-left: none;
        border-top: 2px dashed rgba(var(--muted), 0.2);
        width: 100%;
    }

    .suc-pass-footer {
        padding: 0.65rem 1.15rem;
        flex-direction: column;
        align-items: center;
        text-align: center;
    }
}

@media (max-width: 440px) {
    .suc-title {
        font-size: 1.6rem;
    }

    .suc-pass-code-box {
        flex-direction: column;
        align-items: flex-start;
    }

    .suc-pass-code-actions {
        width: 100%;
        justify-content: space-between;
    }

    .suc-qr-img {
        width: 120px;
        height: 120px;
    }
}

/* ═══════════════════════════════════════════════════════════
   Dedicated Print & PDF Stylesheet
   ═══════════════════════════════════════════════════════════ */
@media print {
    .no-print,
    header,
    footer,
    nav,
    .mobile-bottom-nav,
    .evently-processing-overlay {
        display: none !important;
    }

    body,
    html {
        background: #ffffff !important;
        color: #000000 !important;
    }

    .suc-container {
        max-width: 100% !important;
        padding: 0 !important;
        margin: 0 !important;
    }

    .suc-receipt {
        border: 1px solid #cccccc !important;
        box-shadow: none !important;
        background: #ffffff !important;
        color: #000000 !important;
        page-break-inside: avoid;
    }

    .suc-receipt__val,
    .suc-receipt__order-no {
        color: #000000 !important;
    }

    .suc-pass-card {
        border: 2px solid #000000 !important;
        box-shadow: none !important;
        background: #ffffff !important;
        color: #000000 !important;
        margin-bottom: 2rem !important;
        page-break-inside: avoid;
    }

    .suc-pass-header {
        background: #111827 !important;
        color: #ffffff !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }

    .suc-pass-meta-val {
        color: #000000 !important;
    }

    .suc-qr-frame {
        border: 1px solid #000000 !important;
    }

    .suc-pass-code-box {
        border: 1.5px dashed #000000 !important;
        background: #f8fafc !important;
    }

    .suc-copy-code-btn {
        display: none !important;
    }
}
</style>
@endsection
