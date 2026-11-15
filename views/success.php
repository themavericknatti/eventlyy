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
$orderRef = !empty($order['payment_reference']) 
    ? $order['payment_reference'] 
    : (!empty($order['tickets'][0]['ticket_code']) 
        ? $order['tickets'][0]['ticket_code'] 
        : (!empty($order['tickets'][0]['id']) 
            ? $order['tickets'][0]['id'] 
            : ('ORD-' . time())));

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

    <?php if ($flashSuccess = \Ace\Application::$app->session->getFlash('success')): ?>
        <div style="margin: 1rem auto 0; max-width: 620px; padding: 0.75rem 1.25rem; background: rgba(16, 185, 129, 0.12); border: 1px solid rgba(16, 185, 129, 0.35); border-radius: 12px; color: #047857; font-size: 0.92rem; font-weight: 600; text-align: center; display: flex; align-items: center; justify-content: center; gap: 8px;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
            <span><?= e($flashSuccess) ?></span>
        </div>
    <?php endif; ?>

    <?php if ($flashError = \Ace\Application::$app->session->getFlash('error')): ?>
        <div style="margin: 1rem auto 0; max-width: 620px; padding: 0.75rem 1.25rem; background: rgba(239, 68, 68, 0.12); border: 1px solid rgba(239, 68, 68, 0.35); border-radius: 12px; color: #b91c1c; font-size: 0.92rem; font-weight: 600; text-align: center; display: flex; align-items: center; justify-content: center; gap: 8px;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
            <span><?= e($flashError) ?></span>
        </div>
    <?php endif; ?>
</section>

<div class="suc-container">
    
    <!-- ═══ Viral "I'm Going!" Story Card Launch Banner ═══ -->
    <?php
    $firstTicket = $order['tickets'][0] ?? [];
    $firstTicketCode = $firstTicket['ticket_code'] ?? ($firstTicket['id'] ?? $orderRef);
    $firstTicketType = !empty($firstTicket['ticket_type']) ? $firstTicket['ticket_type'] : 'General Admission';
    $primaryAttendeeName = !empty($order['attendee_name']) && $order['attendee_name'] !== 'Attendee' 
        ? $order['attendee_name'] 
        : (!empty($firstTicket['attendee_name']) && $firstTicket['attendee_name'] !== 'Attendee' ? $firstTicket['attendee_name'] : 'Attendee');
    $evDateStr = $eventDate ? date('D, M d, Y • g:i A', $eventDate) : 'Date TBA';
    $rawImg = $order['event_image'] ?? '';
    if (empty($rawImg)) {
        try {
            $dbInstance = \Ace\Application::$app->db->pdo ?? null;
            if ($dbInstance) {
                if (!empty($order['event_id'])) {
                    $fallbackStmt = $dbInstance->prepare("SELECT image FROM events WHERE id = :id LIMIT 1");
                    $fallbackStmt->execute(['id' => $order['event_id']]);
                } elseif (!empty($order['event_name'])) {
                    $fallbackStmt = $dbInstance->prepare("SELECT image FROM events WHERE name = :name ORDER BY id DESC LIMIT 1");
                    $fallbackStmt->execute(['name' => $order['event_name']]);
                }
                if (!empty($fallbackStmt)) {
                    $imgRow = $fallbackStmt->fetch(\PDO::FETCH_ASSOC);
                    if (!empty($imgRow['image'])) {
                        $rawImg = $imgRow['image'];
                    }
                }
            }
        } catch (\Throwable $th) {}
    }

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
    if (empty($evImg)) {
        $evImg = 'https://images.unsplash.com/photo-1514525253161-7a46d19cd819?w=1600&q=85&auto=format&fit=crop';
    }
    $evShareUrl = url('/e/' . ($order['event_slug'] ?? ($order['event_id'] ?? '')));

    // ═══ WhatsApp Delivery Configuration ═══
    $buyerPhone = $order['attendee_phone'] ?? '';
    $waDefaultMsg = \App\Services\WhatsAppService::buildTicketPassMessage(
        ['name' => $order['event_name'], 'date' => $order['event_date'], 'location' => $order['event_location']],
        ['attendee_name' => $primaryAttendeeName, 'ticket_code' => $firstTicketCode, 'ticket_type' => $firstTicketType],
        $siteTitle
    );
    $initialWaUrl = \App\Services\WhatsAppService::generateDirectWhatsAppUrl($buyerPhone, $waDefaultMsg);
    ?>

    <!-- ═══ WhatsApp Instant Delivery & Gate Alerts Banner ═══ -->
    <div class="suc-whatsapp-banner no-print" style="margin-bottom: 1.5rem; background: linear-gradient(135deg, rgba(37, 211, 102, 0.12) 0%, rgba(18, 140, 126, 0.08) 100%); border: 1px solid rgba(37, 211, 102, 0.35); border-radius: 18px; padding: 1.35rem 1.6rem; display: flex; align-items: center; justify-content: space-between; gap: 1.5rem; flex-wrap: wrap;">
        <div style="flex: 1; min-width: 280px;">
            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 0.35rem;">
                <span style="display: inline-flex; align-items: center; justify-content: center; width: 28px; height: 28px; border-radius: 50%; background: #25D366; color: #fff; box-shadow: 0 4px 12px rgba(37, 211, 102, 0.4);">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.711 2.598 2.664-.698c.969.586 1.861.947 3.018.947 3.174 0 5.79-2.586 5.79-5.767.001-3.18-2.617-5.767-5.789-5.767m7.859 5.766c0 4.331-3.523 7.854-7.859 7.854-1.328 0-2.56-.344-3.644-.949l-4.048 1.061 1.08-3.945c-.714-1.156-1.127-2.518-1.127-3.971 0-4.331 3.523-7.854 7.859-7.854 4.336 0 7.859 3.523 7.859 7.854"/></svg>
                </span>
                <strong style="font-size: 1.05rem; color: #f1f5f9; font-weight: 800;">WhatsApp Ticket Delivery &amp; Gate Alerts</strong>
                <span style="background: rgba(37, 211, 102, 0.2); color: #25D366; padding: 2px 8px; border-radius: 9999px; font-size: 0.72rem; font-weight: 800; letter-spacing: 0.04em;">95%+ OPEN RATE</span>
            </div>
            <p style="margin: 0; font-size: 0.88rem; color: #cbd5e1; line-height: 1.45;">
                Get your digital pass with scannable QR code and event-day gate alerts delivered directly to WhatsApp so you never misplace your ticket at the entrance.
            </p>
        </div>

        <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
            <div style="display: flex; align-items: center; background: rgba(0, 0, 0, 0.4); border: 1px solid rgba(255, 255, 255, 0.15); border-radius: 12px; padding: 2px 10px;">
                <span style="font-size: 0.85rem; color: #94a3b8; margin-right: 4px;">📱</span>
                <input type="tel" id="suc-wa-phone" placeholder="e.g. +234..." value="<?= e($buyerPhone) ?>"
                       style="background: transparent; border: none; color: #fff; font-size: 0.88rem; padding: 0.55rem 0.25rem; outline: none; width: 155px; font-weight: 600;"
                       title="Enter your WhatsApp phone number" />
            </div>

            <button type="button" id="btn-suc-send-whatsapp"
                    data-ref="<?= e($orderRef) ?>"
                    data-wa-url="<?= e($initialWaUrl) ?>"
                    style="display: inline-flex; align-items: center; gap: 7px; background: #25D366; color: #fff; padding: 0.65rem 1.25rem; border-radius: 12px; font-weight: 800; font-size: 0.9rem; border: none; cursor: pointer; transition: all 0.15s ease; box-shadow: 0 4px 15px rgba(37, 211, 102, 0.35);">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.711 2.598 2.664-.698c.969.586 1.861.947 3.018.947 3.174 0 5.79-2.586 5.79-5.767.001-3.18-2.617-5.767-5.789-5.767m7.859 5.766c0 4.331-3.523 7.854-7.859 7.854-1.328 0-2.56-.344-3.644-.949l-4.048 1.061 1.08-3.945c-.714-1.156-1.127-2.518-1.127-3.971 0-4.331 3.523-7.854 7.859-7.854 4.336 0 7.859 3.523 7.859 7.854"/></svg>
                <span>Send to WhatsApp</span>
            </button>
        </div>
    </div>
    <div class="suc-story-banner no-print">
        <div class="suc-story-banner__content">
            <span class="suc-story-banner__badge">✦ OFFICIAL SOCIAL PASS</span>
            <h2 class="suc-story-banner__title">Share Your Story Pass</h2>
            <p class="suc-story-banner__text">
                Generate a 9:16 vertical card for your <strong>Instagram Story &amp; WhatsApp Status</strong>. Feature the official artwork or upload your own selfie.
                <span style="display:block; margin-top:6px; font-size:0.82rem; color:var(--primary, #108a55); font-weight:500;">
                    🔒 Safe to share online &bull; Your entry QR code is kept private in your tickets below.
                </span>
            </p>
        </div>
        <div class="suc-story-banner__action">
            <button type="button" class="suc-story-trigger-btn"
                    data-story-trigger
                    data-event-name="<?= e($order['event_name']) ?>"
                    data-event-date="<?= e($evDateStr) ?>"
                    data-event-location="<?= e($order['event_location']) ?>"
                    data-event-image="<?= e($evImg) ?>"
                    data-attendee-name="<?= e($primaryAttendeeName) ?>"
                    data-ticket-type="<?= e($firstTicketType) ?>"
                    data-site-title="<?= e($siteTitle) ?>"
                    data-event-url="<?= e($evShareUrl) ?>">
                <span>📸</span> <span>Generate Story Pass</span>
            </button>
        </div>
    </div>

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
                    <div style="display:flex; align-items:center; gap:0.5rem; flex-wrap:wrap;">
                        <span class="suc-pass-type-badge"><?= e($ticketType) ?></span>
                        <button type="button" class="btn-ticket-story no-print"
                                data-story-trigger
                                data-event-name="<?= e($order['event_name']) ?>"
                                data-event-date="<?= e($evDateStr) ?>"
                                data-event-location="<?= e($order['event_location']) ?>"
                                data-event-image="<?= e($evImg) ?>"
                                data-attendee-name="<?= e($holderName) ?>"
                                data-ticket-type="<?= e($ticketType) ?>"
                                data-site-title="<?= e($siteTitle) ?>"
                                data-event-url="<?= e($evShareUrl) ?>"
                                title="Generate Instagram &amp; WhatsApp Story Card for this ticket">
                            <span>📸</span> Story Pass
                        </button>
                        <?php
                            $ticketPhone = $t['attendee_phone'] ?? $buyerPhone;
                            $indivWaMsg = \App\Services\WhatsAppService::buildTicketPassMessage(
                                ['name' => $order['event_name'], 'date' => $order['event_date'], 'location' => $order['event_location']],
                                ['attendee_name' => $holderName, 'ticket_code' => $tCode, 'ticket_type' => $ticketType],
                                $siteTitle
                            );
                            $indivWaUrl = \App\Services\WhatsAppService::generateDirectWhatsAppUrl($ticketPhone, $indivWaMsg);
                        ?>
                        <a href="<?= e($indivWaUrl) ?>" target="_blank" rel="noopener" class="no-print"
                           style="display:inline-flex; align-items:center; gap:5px; padding:0.4rem 0.85rem; border-radius:9999px; background:rgba(37,211,102,0.15); border:1px solid rgba(37,211,102,0.35); color:#25D366; font-size:0.78rem; font-weight:700; text-decoration:none; transition:all 0.15s ease;"
                           title="Send this ticket's QR pass directly via WhatsApp">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.711 2.598 2.664-.698c.969.586 1.861.947 3.018.947 3.174 0 5.79-2.586 5.79-5.767.001-3.18-2.617-5.767-5.789-5.767m7.859 5.766c0 4.331-3.523 7.854-7.859 7.854-1.328 0-2.56-.344-3.644-.949l-4.048 1.061 1.08-3.945c-.714-1.156-1.127-2.518-1.127-3.971 0-4.331 3.523-7.854 7.859-7.854 4.336 0 7.859 3.523 7.859 7.854"/></svg>
                            <span>WhatsApp Pass</span>
                        </a>
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

    // Non-blocking trigger to flush email queue in the background
    try {
        if (window.fetch) {
            fetch('<?= \url('/cron/process-email-queue') ?>', { cache: 'no-store' }).catch(function () {});
        }
    } catch (e) {}

    // WhatsApp Pass Sender & Direct Link Trigger
    const waSendBtn = document.getElementById('btn-suc-send-whatsapp');
    const waPhoneInput = document.getElementById('suc-wa-phone');
    if (waSendBtn) {
        waSendBtn.addEventListener('click', function () {
            const ref = this.getAttribute('data-ref');
            const fallbackUrl = this.getAttribute('data-wa-url');
            const phoneVal = waPhoneInput ? waPhoneInput.value.trim() : '';

            showToast('📲 Preparing WhatsApp ticket pass...');
            this.disabled = true;
            this.style.opacity = '0.7';

            if (window.fetch && ref) {
                const formData = new FormData();
                formData.append('reference', ref);
                formData.append('phone', phoneVal);

                fetch('<?= \url('/order/send-whatsapp') ?>', {
                    method: 'POST',
                    body: formData
                })
                .then(r => r.json())
                .then(data => {
                    waSendBtn.disabled = false;
                    waSendBtn.style.opacity = '1';
                    if (data.whatsapp_url) {
                        window.open(data.whatsapp_url, '_blank');
                        showToast('✓ WhatsApp pass opened!');
                    } else if (fallbackUrl) {
                        window.open(fallbackUrl, '_blank');
                    }
                })
                .catch(() => {
                    waSendBtn.disabled = false;
                    waSendBtn.style.opacity = '1';
                    if (fallbackUrl) {
                        window.open(fallbackUrl, '_blank');
                    }
                });
            } else if (fallbackUrl) {
                waSendBtn.disabled = false;
                waSendBtn.style.opacity = '1';
                window.open(fallbackUrl, '_blank');
            }
        });
    }

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

<?php echo $this->include('partials/story-card-modal', []); ?>

@endsection
