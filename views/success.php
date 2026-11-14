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
        <?php if (!empty($orderRef)): ?>
            <br/>
            <span style="display: inline-block; margin-top: 6px; font-size: 0.88rem; color: var(--muted, #64748b);">
                Didn't see the email? Check your spam folder or <a href="<?= \url('/order/resend-email/' . urlencode($orderRef)) ?>" style="color: var(--primary, #108a55); font-weight: 700; text-decoration: underline;">click here to resend passes</a>.
            </span>
        <?php endif; ?>
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


@endsection
