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
    : ('ORD-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 8)));

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

    <!-- ═══ Purchased Event Merchandise & Add-Ons ═══ -->
    <?php if (!empty($order['addons'])): ?>
        <div class="suc-receipt" style="margin-top: 1.5rem; border-color: rgba(13, 148, 136, 0.35); background: linear-gradient(180deg, rgba(13, 148, 136, 0.05) 0%, rgba(var(--card), 1) 100%);">
            <div class="suc-receipt__top" style="border-bottom: 1px solid rgba(13, 148, 136, 0.2);">
                <div>
                    <span class="suc-receipt__tag" style="background: rgba(13, 148, 136, 0.15); color: #0d9488;">🛍️ Official Merchandise &amp; Add-On Voucher</span>
                    <h3 class="suc-receipt__order-no" style="font-size: 1.15rem;">Collection Details</h3>
                </div>
                <div class="suc-receipt__status-wrap">
                    <span class="suc-receipt__status-badge" style="background: #0d9488; color: #fff;">
                        ✓ CONFIRMED FOR PICKUP
                    </span>
                </div>
            </div>

            <div style="padding: 1.25rem 1.5rem;">
                <p style="margin: 0 0 1rem; font-size: 0.88rem; color: rgb(var(--muted));">
                    Show your order confirmation or digital ticket QR code at the event entrance or official merchandise desk to claim your items.
                </p>
                <div style="display: flex; flex-direction: column; gap: 0.85rem;">
                    <?php foreach ($order['addons'] as $addonItem): 
                        $aName = $addonItem['addon_name'] ?? ($addonItem['name'] ?? 'Merchandise Item');
                        $aQty = (int)($addonItem['quantity'] ?? 1);
                        $aPrice = (float)($addonItem['total_price'] ?? ($addonItem['unit_price'] ?? 0));
                        $aDesc = $addonItem['addon_description'] ?? ($addonItem['description'] ?? '');
                    ?>
                        <div style="display: flex; align-items: center; justify-content: space-between; gap: 1rem; padding: 0.85rem 1rem; background: rgba(var(--muted), 0.04); border: 1px solid rgba(var(--muted), 0.15); border-radius: 10px;">
                            <div style="display: flex; align-items: center; gap: 0.85rem; flex: 1; min-width: 0;">
                                <?php $aImg = $addonItem['image'] ?? ($addonItem['addon_image'] ?? null); ?>
                                <?php if (!empty($aImg)): ?>
                                    <img src="<?= e($aImg) ?>" alt="<?= e($aName) ?>" style="width: 48px; height: 48px; border-radius: 8px; object-fit: cover; border: 1px solid rgba(var(--muted), 0.2); flex-shrink: 0;" />
                                <?php else: ?>
                                    <div style="width: 48px; height: 48px; border-radius: 8px; background: rgba(13,148,136,0.1); border: 1px solid rgba(13,148,136,0.25); display: flex; align-items: center; justify-content: center; font-size: 1.3rem; flex-shrink: 0;">🛍️</div>
                                <?php endif; ?>
                                <div>
                                    <strong style="font-size: 0.95rem; color: rgb(var(--text));">
                                        <span style="color:#0d9488; font-weight:800;"><?= $aQty ?>×</span> <?= e($aName) ?>
                                    </strong>
                                    <?php if (!empty($aDesc)): ?>
                                        <p style="margin: 0.25rem 0 0; font-size: 0.78rem; color: rgb(var(--muted));"><?= e($aDesc) ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <strong style="color: #0d9488; font-size: 0.95rem; flex-shrink: 0;">
                                <?= $aPrice > 0 ? format_price($aPrice) : 'Included' ?>
                            </strong>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

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
            $ticketType = !empty($t['ticket_type']) ? $t['ticket_type'] : 'General Admission';
            $holderName = !empty($t['attendee_name']) && $t['attendee_name'] !== 'Attendee' 
                ? $t['attendee_name'] 
                : (!empty($order['attendee_name']) && $order['attendee_name'] !== 'Attendee' ? $order['attendee_name'] : 'Attendee');
            $holderEmail = !empty($t['attendee_email']) ? $t['attendee_email'] : ($order['attendee_email'] ?? '');
            $maskedEmail = !empty($holderEmail) ? preg_replace('/(?<=.{2}).(?=.*@)/', '*', $holderEmail) : 'your email';
            $isTUnlocked = !empty(\Ace\Application::$app->session->get('pass_unlocked_' . $tCode));
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

                        <!-- Admission Code Box -->
                        <div class="suc-pass-code-box" id="code-box-<?= e($tCode) ?>">
                            <div>
                                <span class="suc-pass-code-label">Unique Admission Code</span>
                                <div class="suc-pass-code-val" style="letter-spacing: 0.12em; font-family: Consolas, Monaco, monospace; color: #108a55; font-weight: 800;">
                                    <?php if ($isTUnlocked): ?>
                                        <span class="suc-code-revealed" id="code-revealed-<?= e($tCode) ?>"><?= e($tCode) ?></span>
                                    <?php else: ?>
                                        <span class="suc-code-masked" id="code-masked-<?= e($tCode) ?>" style="color:#64748b; letter-spacing:0.18em;">EV-••••••••</span>
                                        <span class="suc-code-revealed" id="code-revealed-<?= e($tCode) ?>" style="display:none;"><?= e($tCode) ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="suc-pass-code-actions">
                                <button type="button" 
                                        class="suc-copy-code-btn" 
                                        id="copy-btn-<?= e($tCode) ?>" 
                                        data-code="<?= $isTUnlocked ? e($tCode) : '' ?>" 
                                        title="Copy Admission Code"
                                        style="<?= $isTUnlocked ? '' : 'display:none;' ?>">
                                    📋 Copy Code
                                </button>
                                <?php if ($isTUnlocked): ?>
                                    <span class="suc-status-badge-unlocked" id="badge-unlocked-<?= e($tCode) ?>" style="font-size:0.75rem; color:#065f46; background:#d1fae5; border:1px solid #a7f3d0; padding:3px 8px; border-radius:6px; font-weight:700;">
                                        ✓ Unlocked
                                    </span>
                                <?php else: ?>
                                    <span class="suc-status-badge-locked" id="badge-locked-<?= e($tCode) ?>" style="font-size:0.75rem; color:#b45309; background:#fef3c7; border:1px solid #fde68a; padding:3px 8px; border-radius:6px; font-weight:700;">
                                        🔒 PIN Protected
                                    </span>
                                    <span class="suc-status-badge-unlocked" id="badge-unlocked-<?= e($tCode) ?>" style="display:none; font-size:0.75rem; color:#065f46; background:#d1fae5; border:1px solid #a7f3d0; padding:3px 8px; border-radius:6px; font-weight:700;">
                                        ✓ Unlocked
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Inline PIN Reveal Box (Shown when locked) -->
                        <div class="suc-pin-reveal-box" id="pin-box-<?= e($tCode) ?>" style="<?= $isTUnlocked ? 'display:none;' : '' ?> margin-top:0.75rem; padding:0.9rem 1.1rem; background:rgba(245, 158, 11, 0.06); border:1px dashed rgba(245, 158, 11, 0.4); border-radius:12px;">
                            <div style="display:flex; align-items:flex-start; gap:0.65rem; margin-bottom:0.75rem;">
                                <span style="font-size:1.25rem; line-height:1;">🔐</span>
                                <div style="font-size:0.82rem; color:#475569; line-height:1.45;">
                                    <strong style="color:#0f172a; display:block; margin-bottom:2px; font-weight:800;">Pass Protected by Security PIN</strong>
                                    We sent a 4-digit PIN to <strong><?= e($maskedEmail) ?></strong>. Enter it below to reveal your admission code and gate QR pass.
                                </div>
                            </div>
                            <div style="display:flex; flex-wrap:wrap; align-items:center; gap:0.5rem;">
                                <input type="password" 
                                       id="pin-input-<?= e($tCode) ?>" 
                                       class="suc-pin-input-field"
                                       maxlength="4" 
                                       pattern="\d{4}" 
                                       inputmode="numeric" 
                                       autocomplete="one-time-code" 
                                       placeholder="••••" 
                                       style="width:90px; text-align:center; font-size:1.15rem; font-weight:800; letter-spacing:0.25em; padding:6px 10px; border:1.5px solid #cbd5e1; border-radius:8px; font-family:monospace; background:#ffffff; color:#0f172a; outline:none;" />
                                <button type="button" 
                                        class="suc-btn-reveal-code" 
                                        data-code="<?= e($tCode) ?>" 
                                        style="background:linear-gradient(135deg, #108a55 0%, #059669 100%); color:#ffffff; font-weight:800; font-size:0.82rem; padding:8px 16px; border:none; border-radius:8px; cursor:pointer; display:inline-flex; align-items:center; gap:5px; box-shadow:0 2px 6px rgba(16, 138, 85, 0.25);">
                                    <span>🔓</span> Reveal Ticket Code
                                </button>
                                <button type="button" 
                                        class="suc-btn-resend-pin" 
                                        data-code="<?= e($tCode) ?>" 
                                        style="background:none; border:none; color:#0284c7; font-size:0.8rem; font-weight:700; cursor:pointer; text-decoration:underline; padding:4px 6px;">
                                    ✉️ Resend PIN
                                </button>
                            </div>
                            <div id="pin-feedback-<?= e($tCode) ?>" style="margin-top:6px; font-size:0.78rem; font-weight:700; display:none;"></div>
                        </div>
                    </div>

                    <!-- Right: Official Scannable QR Code -->
                    <!-- Locked QR Stub -->
                    <div class="suc-pass-qr-stub" id="qr-locked-stub-<?= e($tCode) ?>" style="<?= $isTUnlocked ? 'display:none;' : 'display:flex;' ?> flex-direction:column; align-items:center; justify-content:center; gap:0.35rem; text-align:center; padding:1rem; min-width:130px;">
                        <div style="width:110px; height:110px; background:#f8fafc; border:1.5px dashed rgba(var(--muted),0.3); border-radius:10px; display:flex; flex-direction:column; align-items:center; justify-content:center; gap:4px; box-shadow:inset 0 1px 3px rgba(0,0,0,0.04);">
                            <span style="font-size:1.8rem;">🔒</span>
                            <span style="font-size:0.68rem; font-weight:800; color:#64748b; text-transform:uppercase; letter-spacing:0.04em;">QR Locked</span>
                        </div>
                        <span style="margin-top:4px; font-size:0.72rem; font-weight:600; color:#64748b;">
                            Enter PIN to reveal
                        </span>
                        <a href="<?= url('/pass/' . urlencode($tCode)) ?>" target="_blank" style="margin-top:4px; font-size:0.75rem; font-weight:700; color:#108a55; text-decoration:none;">
                            📱 Open Pass →
                        </a>
                    </div>

                    <!-- Unlocked QR Stub -->
                    <div class="suc-pass-qr-stub" id="qr-unlocked-stub-<?= e($tCode) ?>" style="<?= $isTUnlocked ? 'display:flex;' : 'display:none;' ?> flex-direction:column; align-items:center; justify-content:center; gap:0.35rem; text-align:center; padding:1rem; min-width:130px;">
                        <div style="background:#ffffff; padding:6px; border-radius:10px; border:1px solid rgba(var(--muted),0.2); box-shadow:0 2px 8px rgba(0,0,0,0.06);">
                            <img src="<?= $isTUnlocked ? url('/ticket/qr/' . urlencode($tCode) . '?s=220') : '' ?>" 
                                 id="qr-img-<?= e($tCode) ?>"
                                 alt="QR Code #<?= e($tCode) ?>" 
                                 width="110" 
                                 height="110" 
                                 style="display:block; border-radius:4px;" />
                        </div>
                        <a href="<?= url('/pass/' . urlencode($tCode)) ?>" target="_blank" style="margin-top:4px; font-size:0.75rem; font-weight:700; color:#108a55; text-decoration:none;">
                            📱 Open Mobile Pass →
                        </a>
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
            if (!code) return;
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(code).then(() => {
                    showToast('📋 Admission code copied: ' + code);
                    this.textContent = '✓ Copied';
                    setTimeout(() => this.textContent = '📋 Copy Code', 1800);
                }).catch(() => fallback(code, this));
            } else {
                fallback(code, this);
            }
        });
    });

    // ═══ Security PIN Reveal Handlers ═══
    document.querySelectorAll('.suc-btn-reveal-code').forEach(btn => {
        btn.addEventListener('click', function () {
            handlePinReveal(this.getAttribute('data-code'));
        });
    });

    document.querySelectorAll('.suc-pin-input-field').forEach(input => {
        input.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                const code = this.id.replace('pin-input-', '');
                handlePinReveal(code);
            }
        });
        input.addEventListener('input', function () {
            this.style.borderColor = '#cbd5e1';
            const code = this.id.replace('pin-input-', '');
            const feedback = document.getElementById('pin-feedback-' + code);
            if (feedback) feedback.style.display = 'none';
        });
    });

    function handlePinReveal(code) {
        if (!code) return;
        const pinInput = document.getElementById('pin-input-' + code);
        const pin = pinInput ? pinInput.value.trim() : '';
        const feedback = document.getElementById('pin-feedback-' + code);
        const revealBtn = document.querySelector('.suc-btn-reveal-code[data-code="' + CSS.escape(code) + '"]');

        if (!pin || pin.length !== 4) {
            if (feedback) {
                feedback.style.display = 'block';
                feedback.style.color = '#dc2626';
                feedback.textContent = 'Please enter your 4-digit security PIN.';
            }
            if (pinInput) {
                pinInput.focus();
                pinInput.style.borderColor = '#dc2626';
            }
            return;
        }

        if (revealBtn) {
            revealBtn.disabled = true;
            revealBtn.style.opacity = '0.7';
            revealBtn.innerHTML = '<span>⏳</span> Verifying...';
        }
        if (feedback) {
            feedback.style.display = 'block';
            feedback.style.color = '#475569';
            feedback.textContent = 'Verifying PIN...';
        }

        const formData = new FormData();
        formData.append('pin', pin);
        formData.append('csrf_token', '<?= e(csrf_token()) ?>');

        fetch('<?= \url('/pass/') ?>' + encodeURIComponent(code) + '/unlock', {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                // Reveal admission code
                const maskedEl = document.getElementById('code-masked-' + code);
                const revealedEl = document.getElementById('code-revealed-' + code);
                if (maskedEl) maskedEl.style.display = 'none';
                if (revealedEl) {
                    revealedEl.textContent = data.ticket_code || code;
                    revealedEl.style.display = 'inline-block';
                    revealedEl.style.color = '#108a55';
                }

                // Show and update copy button
                const copyBtn = document.getElementById('copy-btn-' + code);
                if (copyBtn) {
                    copyBtn.setAttribute('data-code', data.ticket_code || code);
                    copyBtn.style.display = 'inline-flex';
                }

                // Update lock badges
                const badgeLocked = document.getElementById('badge-locked-' + code);
                const badgeUnlocked = document.getElementById('badge-unlocked-' + code);
                if (badgeLocked) badgeLocked.style.display = 'none';
                if (badgeUnlocked) badgeUnlocked.style.display = 'inline-block';

                // Update PIN box
                const pinBox = document.getElementById('pin-box-' + code);
                if (pinBox) {
                    pinBox.style.background = 'rgba(16, 185, 129, 0.08)';
                    pinBox.style.borderColor = 'rgba(16, 185, 129, 0.3)';
                    pinBox.innerHTML = '<span style="color:#047857; font-weight:700; font-size:0.82rem; display:inline-flex; align-items:center; gap:5px;"><span>✓</span> Pass Unlocked with PIN — Ready for Gate Scan</span>';
                }

                // Reveal QR code
                const qrLocked = document.getElementById('qr-locked-stub-' + code);
                const qrUnlocked = document.getElementById('qr-unlocked-stub-' + code);
                const qrImg = document.getElementById('qr-img-' + code);
                if (qrImg && data.qr_url) {
                    qrImg.src = data.qr_url;
                }
                if (qrLocked) qrLocked.style.display = 'none';
                if (qrUnlocked) qrUnlocked.style.display = 'flex';

                showToast('✓ Admission code and QR pass revealed!');
            } else {
                if (revealBtn) {
                    revealBtn.disabled = false;
                    revealBtn.style.opacity = '1';
                    revealBtn.innerHTML = '<span>🔓</span> Reveal Ticket Code';
                }
                if (feedback) {
                    feedback.style.display = 'block';
                    feedback.style.color = '#dc2626';
                    feedback.textContent = '✕ ' + (data.message || 'Incorrect PIN.');
                }
                if (pinInput) {
                    pinInput.style.borderColor = '#dc2626';
                    pinInput.select();
                }
            }
        })
        .catch(() => {
            if (revealBtn) {
                revealBtn.disabled = false;
                revealBtn.style.opacity = '1';
                revealBtn.innerHTML = '<span>🔓</span> Reveal Ticket Code';
            }
            if (feedback) {
                feedback.style.display = 'block';
                feedback.style.color = '#dc2626';
                feedback.textContent = '✕ Error verifying PIN. Please try again.';
            }
        });
    }

    // ═══ Resend PIN Handler ═══
    document.querySelectorAll('.suc-btn-resend-pin').forEach(btn => {
        btn.addEventListener('click', function () {
            const code = this.getAttribute('data-code');
            const feedback = document.getElementById('pin-feedback-' + code);
            this.disabled = true;
            this.style.opacity = '0.6';
            if (feedback) {
                feedback.style.display = 'block';
                feedback.style.color = '#0284c7';
                feedback.textContent = 'Sending PIN to your email...';
            }

            const formData = new FormData();
            formData.append('csrf_token', '<?= e(csrf_token()) ?>');

            fetch('<?= \url('/pass/') ?>' + encodeURIComponent(code) + '/resend-pin', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    if (feedback) {
                        feedback.style.color = '#047857';
                        feedback.textContent = '✓ ' + (data.message || 'Security PIN sent to your email.');
                    }
                    showToast('✉️ Security PIN resent to email!');
                } else {
                    if (feedback) {
                        feedback.style.color = '#dc2626';
                        feedback.textContent = '✕ ' + (data.message || 'Could not resend PIN.');
                    }
                    btn.disabled = false;
                    btn.style.opacity = '1';
                }
            })
            .catch(() => {
                if (feedback) {
                    feedback.style.color = '#dc2626';
                    feedback.textContent = '✕ Error connecting to server.';
                }
                btn.disabled = false;
                btn.style.opacity = '1';
            });
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
            const phoneVal = waPhoneInput ? waPhoneInput.value.trim() : '';

            showToast('📲 Preparing WhatsApp ticket pass...');
            this.disabled = true;
            this.style.opacity = '0.7';

            if (window.fetch && ref) {
                const formData = new FormData();
                formData.append('reference', ref);
                formData.append('phone', phoneVal);
                formData.append('csrf_token', '<?= e(csrf_token()) ?>');

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
                    } else {
                        showToast('✕ ' + (data.message || 'Could not generate WhatsApp pass.'));
                    }
                })
                .catch(() => {
                    waSendBtn.disabled = false;
                    waSendBtn.style.opacity = '1';
                    showToast('✕ Error connecting to WhatsApp service.');
                });
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
