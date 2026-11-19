<?php

/**
 * Checkout Page
 *
 * Confirms buyer billing information, selected ticket tier quantities, pricing breakdown, and processes order checkout.
 *
 * @package Eventlyy
 * @subpackage Views/Checkout
 * @author Celio Natti
 * @version 2.0.0
 */

$currentUser = $currentUser ?? null;
$userName = $currentUser ? $currentUser->name : '';
$userEmail = $currentUser ? $currentUser->email : '';

$selectedItems = $selectedItems ?? [];
$eventAddons = $eventAddons ?? [];
$subtotal = 0.0;
$totalPasses = 0;
$attendeeSlots = [];

if (!empty($selectedItems) && is_iterable($selectedItems)) {
    foreach ($selectedItems as $item) {
        $subtotal += isset($item['subtotal']) ? floatval($item['subtotal']) : 0.0;
        $tierId = $item['ticket_type_id'];
        $tierName = $item['name'];
        $admits = max(1, (int)($item['admits'] ?? 1));
        $qty = (int)$item['qty'];
        $passesForTier = $qty * $admits;
        $totalPasses += $passesForTier;

        for ($p = 0; $p < $passesForTier; $p++) {
            $isPrimary = (count($attendeeSlots) === 0);
            $tierPassNum = $p + 1;
            $slotLabel = $admits > 1
                ? ($tierName . ' (Guest ' . $tierPassNum . ' of ' . $passesForTier . ')')
                : ($tierName . ($qty > 1 ? ' #' . $tierPassNum : ''));

            $attendeeSlots[] = [
                'ticket_type_id' => $tierId,
                'tier_name' => $tierName,
                'admits' => $admits,
                'pass_index' => $p,
                'global_index' => count($attendeeSlots) + 1,
                'is_primary' => $isPrimary,
                'label' => $slotLabel
            ];
        }
    }
}

$passFee = $passFee ?? false;
$fee = ($passFee && $subtotal > 0) ? ($fee ?? 0.0) : 0.0;
$extraCharge = $subtotal > 0 ? ($extraCharge ?? 100.0) : 0.0;
$totalAmount = $subtotal > 0 ? max(0, $subtotal + $fee + $extraCharge) : 0.0;
$hasSelectedTickets = ($totalPasses > 0);
$isFreeOrder = ($hasSelectedTickets && ($totalAmount <= 0.00 || $subtotal <= 0.00));

if ($isFreeOrder) {
    $fee = 0.0;
    $extraCharge = 0.0;
    $totalAmount = 0.0;
}

$enabledGateways = (array) site_setting('enabled_gateways', ['paystack']);
$defaultGateway  = site_setting('default_gateway', 'paystack');
if (!in_array($defaultGateway, $enabledGateways)) {
    $defaultGateway = $enabledGateways[0] ?? 'paystack';
}

$eventName = value($event, 'name', 'Event');
$eventId = value($event, 'id');
$eventDate = strtotime(value($event, 'date', ''));
$eventLocation = value($event, 'location', 'Venue TBA');
$backUrl = event_url($event);
$platformFeePercent = e($platformFeePercent ?? get_setting('platform_fee', 5));
?>

@extends('layouts/main')

@section('title')
Checkout — <?= e($eventName) ?>
@endsection


@section('content')

<!-- ═══ Checkout Header ═══ -->
<section class="co-header">
    <div class="co-header__nav">
        <a href="<?= e($backUrl) ?>" class="co-back-link" title="Return to event page">
            ← <span>Back to Event</span>
        </a>
        <span class="co-secure-badge">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
            </svg>
            256-Bit SSL Encrypted
        </span>
    </div>

    <div class="co-header__intro">
        <span class="eyebrow">Checkout</span>
        <h1 class="co-title"><?= $isFreeOrder ? 'Complete Your Registration' : ($hasSelectedTickets ? 'Confirm Your Order' : 'Review Your Order') ?></h1>
        <p class="co-subtitle">
            <?= $isFreeOrder 
                ? 'Finish your free registration for <strong>' . e($eventName) . '</strong> to receive your instant digital ticket QR passes.' 
                : ($hasSelectedTickets 
                    ? 'Finish your purchase securely for <strong>' . e($eventName) . '</strong>.' 
                    : 'Select your admission passes to continue.') ?>
        </p>
    </div>
</section>

<!-- ═══ Unified Checkout Form ═══ -->
<form id="checkout-form-main" method="POST" action="{{ route('checkout/{event_id}', ['event_id' => $eventId]) }}">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="process_checkout" />
    <input type="hidden" name="event_id" value="<?= $eventId ?>" />
    <input type="hidden" name="payment_method" id="selected-payment-method" value="<?= $isFreeOrder ? 'free' : e($defaultGateway) ?>" />
    <?php if (!empty($_GET['ref']) || !empty($_POST['ref']) || !empty($refCode)): ?>
        <input type="hidden" name="ref" value="<?= e($refCode ?? ($_GET['ref'] ?? ($_POST['ref'] ?? ''))) ?>" />
    <?php endif; ?>

    <!-- Pass through selected ticket quantities -->
    <?php if (!empty($selectedItems)): ?>
        <?php foreach ($selectedItems as $item): ?>
            <input type="hidden" name="qty[<?= $item['ticket_type_id'] ?>]" value="<?= $item['qty'] ?>" />
        <?php endforeach; ?>
    <?php endif; ?>

    <div class="co-grid">
        
        <!-- Left Column: Attendee & Payment Information -->
        <section class="co-form-section">
            <div class="co-card">
                <div class="co-card__header">
                    <h2><?= $isFreeOrder ? 'Attendee Details' : 'Attendee &amp; Payment' ?></h2>
                    <p>Enter your details below. Your digital QR tickets will be issued instantly upon completion.</p>
                </div>

                <!-- Alert Message Block -->
                <div class="checkout-message" id="checkout-alert-msg" style="display:none;" aria-live="polite"></div>

                <!-- Order Contact Information -->
                <div class="co-buyer-section">
                    <div class="co-section-title-row">
                        <span class="co-section-badge">1</span>
                        <h3 class="co-section-heading">Billing &amp; Order Contact</h3>
                    </div>
                    <p class="co-section-hint">Order confirmation, master payment receipt, and summary of all passes will be sent here.</p>

                    <div class="co-buyer-fields-grid">
                        <div class="co-field-group">
                            <label class="co-label" for="billing-name">
                                Full Name (Primary Buyer)
                                <span class="co-required">*</span>
                            </label>
                            <input type="text" name="name" id="billing-name" class="co-input" placeholder="e.g. Jane Doe" required
                                   value="<?= e($userName) ?>" autocomplete="name" />
                        </div>

                        <div class="co-field-group">
                            <label class="co-label" for="billing-email">
                                Email Address (Billing Receipt)
                                <span class="co-required">*</span>
                            </label>
                            <input type="email" name="email" id="billing-email" class="co-input" placeholder="e.g. jane@example.com" required
                                   value="<?= e($userEmail) ?>" autocomplete="email" />
                        </div>

                        <div class="co-field-group" style="grid-column: 1 / -1;">
                            <label class="co-label" for="billing-phone">
                                <span>📱 WhatsApp / Mobile Number</span>
                                <span style="font-size:0.75rem; color:#25D366; font-weight:700; margin-left:6px;">● Instant QR Pass Delivery</span>
                            </label>
                            <input type="tel" name="phone" id="billing-phone" class="co-input" placeholder="e.g. +234 801 234 5678"
                                   value="<?= e($currentUser->phone ?? '') ?>" autocomplete="tel" />
                        </div>
                    </div>

                    <div style="margin-top:0.85rem; padding:0.85rem 1rem; background:rgba(37,211,102,0.08); border:1px solid rgba(37,211,102,0.25); border-radius:12px; display:flex; align-items:flex-start; gap:10px;">
                        <input type="checkbox" name="whatsapp_delivery" id="chk-whatsapp-delivery" value="1" checked style="margin-top:3px; accent-color:#25D366; width:16px; height:16px; cursor:pointer;" />
                        <label for="chk-whatsapp-delivery" style="font-size:0.84rem; color:var(--text, #f1f5f9); cursor:pointer; line-height:1.45;">
                            <strong style="color:#25D366;">📲 Deliver ticket &amp; scannable QR pass to WhatsApp</strong><br/>
                            <span style="font-size:0.78rem; color:var(--muted, #94a3b8);">Receive your ticket pass, instant QR code, and event-day gate alerts directly on WhatsApp (95%+ open rate, fastest gate entry).</span>
                        </label>
                    </div>
                </div>

                <?php if ($totalPasses > 1): ?>
                    <!-- Individual Attendee Assignments for Group / Table Tickets -->
                    <div class="co-attendees-section" id="co-attendees-section">
                        <div class="co-attendees-header">
                            <div>
                                <div class="co-section-title-row">
                                    <span class="co-section-badge">2</span>
                                    <h3 class="co-section-heading">
                                        Attendee Ticket Passes (<?= $totalPasses ?> Total)
                                    </h3>
                                </div>
                                <p class="co-attendees-subtitle">Enter the attendee name and email for each seat. A unique ticket code and QR pass will be issued and emailed to each person at no extra cost.</p>
                            </div>
                            <button type="button" id="btn-copy-buyer-to-all" class="co-copy-buyer-btn" title="Fill all tickets with primary buyer details">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                                    <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
                                </svg>
                                <span>Copy Buyer to All</span>
                            </button>
                        </div>

                        <div class="co-attendee-cards-list">
                            <?php foreach ($attendeeSlots as $slot): ?>
                                <div class="co-attendee-card <?= $slot['is_primary'] ? 'co-attendee-card--primary' : '' ?>" data-slot-index="<?= $slot['global_index'] ?>">
                                    <div class="co-attendee-card__top">
                                        <div class="co-attendee-card__info">
                                            <span class="co-pass-badge">Pass #<?= $slot['global_index'] ?></span>
                                            <strong class="co-pass-tier"><?= e($slot['label']) ?></strong>
                                        </div>
                                        <?php if ($slot['is_primary']): ?>
                                            <span class="co-primary-tag">Ticket #1 (Primary Buyer)</span>
                                        <?php else: ?>
                                            <span class="co-guest-tag">Guest Pass #<?= $slot['global_index'] ?></span>
                                        <?php endif; ?>
                                    </div>

                                    <div class="co-attendee-card__grid">
                                        <div class="co-field-group">
                                            <label class="co-label" for="att-name-<?= $slot['ticket_type_id'] ?>-<?= $slot['pass_index'] ?>">
                                                Attendee Name <?= $slot['is_primary'] ? '<span class="co-required">*</span>' : '' ?>
                                            </label>
                                            <input type="text"
                                                   name="attendees[<?= $slot['ticket_type_id'] ?>][<?= $slot['pass_index'] ?>][name]"
                                                   id="att-name-<?= $slot['ticket_type_id'] ?>-<?= $slot['pass_index'] ?>"
                                                   class="co-input att-name-input <?= $slot['is_primary'] ? 'att-primary-name' : 'att-guest-name' ?>"
                                                   placeholder="<?= $slot['is_primary'] ? 'Primary holder full name' : 'Guest full name' ?>"
                                                   value="<?= $slot['is_primary'] ? e($userName) : '' ?>"
                                                   <?= $slot['is_primary'] ? 'required' : '' ?> />
                                        </div>

                                        <div class="co-field-group">
                                            <label class="co-label" for="att-email-<?= $slot['ticket_type_id'] ?>-<?= $slot['pass_index'] ?>">
                                                Email Address (Pass Delivery) <?= $slot['is_primary'] ? '<span class="co-required">*</span>' : '' ?>
                                            </label>
                                            <input type="email"
                                                   name="attendees[<?= $slot['ticket_type_id'] ?>][<?= $slot['pass_index'] ?>][email]"
                                                   id="att-email-<?= $slot['ticket_type_id'] ?>-<?= $slot['pass_index'] ?>"
                                                   class="co-input att-email-input <?= $slot['is_primary'] ? 'att-primary-email' : 'att-guest-email' ?>"
                                                   placeholder="<?= $slot['is_primary'] ? 'primary@example.com' : 'guest@example.com' ?>"
                                                   value="<?= $slot['is_primary'] ? e($userEmail) : '' ?>"
                                                   <?= $slot['is_primary'] ? 'required' : '' ?> />
                                        </div>
                                    </div>
                                    <?php if (!$slot['is_primary']): ?>
                                        <span class="co-attendee-hint">Leave blank to default to primary buyer</span>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- ═══ Ticket Add-Ons & Merchandise Upselling Shelf ═══ -->
                <?php if (!empty($eventAddons)): ?>
                    <div class="co-card co-addons-section" style="margin-bottom: 2rem;">
                        <div class="co-card__header" style="border-bottom: 1px solid rgba(var(--muted), 0.15); padding-bottom: 0.85rem; margin-bottom: 1.25rem;">
                            <div style="display:flex; align-items:center; gap:0.6rem;">
                                <span style="font-size:1.4rem;">🛍️</span>
                                <div>
                                    <h3 style="margin:0; font-size:1.15rem; font-weight:800; color:rgb(var(--text));">Ticket Add-Ons &amp; Event Merchandise</h3>
                                    <p style="margin:0.2rem 0 0; font-size:0.84rem; color:rgb(var(--muted));">Enhance your event experience with official merchandise and passes before completing payment.</p>
                                </div>
                            </div>
                        </div>

                        <div class="co-addons-list" style="display:flex; flex-direction:column; gap:1rem;">
                            <?php foreach ($eventAddons as $addon): 
                                $aId = (int)$addon['id'];
                                $aPrice = (float)$addon['price'];
                                $aStock = $addon['quantity'] !== null ? max(0, (int)$addon['quantity'] - (int)($addon['total_sold'] ?? $addon['sold_count'] ?? 0)) : null;
                                $isSoldOut = ($aStock !== null && $aStock <= 0);
                            ?>
                                <div class="co-addon-item" data-addon-id="<?= $aId ?>" data-price="<?= $aPrice ?>" style="display:flex; align-items:center; justify-content:space-between; gap:1rem; padding:1.1rem 1.25rem; border:1px solid rgba(var(--muted), 0.18); border-radius:14px; background:rgba(var(--card), 0.7); transition:all 0.2s ease;">
                                    <!-- Add-on Image Thumbnail -->
                                    <div class="co-addon-thumb-wrap" style="width:58px; height:58px; border-radius:12px; overflow:hidden; flex-shrink:0; background:rgba(var(--muted),0.08); border:1px solid rgba(var(--muted),0.18); display:flex; align-items:center; justify-content:center;">
                                        <?php if (!empty($addon['image'])): ?>
                                            <img src="<?= e($addon['image']) ?>" alt="<?= e($addon['name']) ?>" style="width:100%; height:100%; object-fit:cover; cursor:pointer;" onclick="openAddonImageModal('<?= e($addon['image']) ?>', '<?= e(addslashes($addon['name'])) ?>')" title="Click to view full image" />
                                        <?php else: ?>
                                            <span style="font-size:1.6rem; opacity:0.7;">🛍️</span>
                                        <?php endif; ?>
                                    </div>

                                    <div style="flex:1; min-width:0;">
                                        <div style="display:flex; align-items:center; gap:0.5rem; flex-wrap:wrap; margin-bottom:0.25rem;">
                                            <strong style="font-size:0.98rem; color:rgb(var(--text));"><?= e($addon['name']) ?></strong>
                                            <span style="font-size:0.88rem; font-weight:800; color:#0d9488; background:rgba(13,148,136,0.1); padding:2px 8px; border-radius:99px;">
                                                <?= $aPrice > 0 ? format_price($aPrice) : 'Free' ?>
                                            </span>
                                            <?php if ($isSoldOut): ?>
                                                <span style="font-size:0.75rem; font-weight:700; color:#ef4444; background:rgba(239,68,68,0.1); padding:2px 8px; border-radius:99px;">Sold Out</span>
                                            <?php elseif ($aStock !== null && $aStock <= 10): ?>
                                                <span style="font-size:0.75rem; font-weight:700; color:#d97706; background:rgba(217,119,6,0.1); padding:2px 8px; border-radius:99px;">Only <?= $aStock ?> left</span>
                                            <?php endif; ?>
                                        </div>
                                        <?php if (!empty($addon['description'])): ?>
                                            <p style="margin:0; font-size:0.82rem; color:rgb(var(--muted)); line-height:1.4;"><?= e($addon['description']) ?></p>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Add-On Quantity Stepper -->
                                    <div class="co-addon-stepper" style="display:flex; align-items:center; gap:0.4rem;">
                                        <?php if ($isSoldOut): ?>
                                            <span style="font-size:0.85rem; color:rgb(var(--muted)); font-weight:600;">Sold Out</span>
                                        <?php else: ?>
                                            <button type="button" class="addon-step-btn addon-step-minus" data-id="<?= $aId ?>" style="width:36px; height:36px; border-radius:8px; border:1px solid rgba(var(--muted),0.25); background:rgba(var(--muted),0.06); color:rgb(var(--text)); font-weight:800; font-size:1.1rem; cursor:pointer; display:flex; align-items:center; justify-content:center; transition:all 0.15s ease;" aria-label="Decrease quantity">−</button>
                                            <input type="number" 
                                                   name="addons[<?= $aId ?>]" 
                                                   id="addon-qty-<?= $aId ?>" 
                                                   class="addon-qty-input" 
                                                   value="0" 
                                                   min="0" 
                                                   <?= $aStock !== null ? 'max="' . $aStock . '"' : '' ?> 
                                                   data-price="<?= $aPrice ?>" 
                                                   data-name="<?= e($addon['name']) ?>"
                                                   style="width:48px; height:36px; text-align:center; font-weight:700; font-size:0.95rem; border:1px solid rgba(var(--muted),0.25); border-radius:8px; background:rgb(var(--card)); color:rgb(var(--text));" />
                                            <button type="button" class="addon-step-btn addon-step-plus" data-id="<?= $aId ?>" style="width:36px; height:36px; border-radius:8px; border:1px solid rgba(var(--muted),0.25); background:rgba(var(--muted),0.06); color:rgb(var(--text)); font-weight:800; font-size:1.1rem; cursor:pointer; display:flex; align-items:center; justify-content:center; transition:all 0.15s ease;" aria-label="Increase quantity">+</button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Free Ticket Notice Banner -->
                <div id="free-registration-badge" class="co-free-banner" style="display:<?= $isFreeOrder ? 'flex' : 'none' ?>;">
                    <span class="co-free-banner__icon">🎉</span>
                    <div class="co-free-banner__content">
                        <strong>Free Ticket Registration</strong>
                        <span>No credit card or payment required. Your digital QR tickets will be generated directly upon confirmation.</span>
                    </div>
                </div>

                <!-- Payment Provider Selection Section (hidden for free orders) -->
                <div id="payment-provider-section" class="co-provider-section" style="<?= $isFreeOrder ? 'display:none;' : '' ?>">
                    <span class="co-provider-label">Select Payment Provider</span>
                    
                    <div class="payment-methods co-provider-grid" role="tablist" aria-label="Payment methods">
                        <?php if (in_array('paystack', $enabledGateways)): ?>
                            <button type="button" class="pm-btn" data-method="paystack" aria-pressed="<?= $defaultGateway === 'paystack' ? 'true' : 'false' ?>">
                                <span class="logo" aria-hidden="true">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                        <circle cx="12" cy="12" r="9" />
                                    </svg>
                                </span>
                                <span>Paystack</span>
                            </button>
                        <?php endif; ?>

                        <?php if (in_array('stripe', $enabledGateways)): ?>
                            <button type="button" class="pm-btn" data-method="stripe" aria-pressed="<?= $defaultGateway === 'stripe' ? 'true' : 'false' ?>">
                                <span class="logo" aria-hidden="true">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                        <rect x="2" y="6" width="20" height="12" rx="2" />
                                    </svg>
                                </span>
                                <span>Stripe</span>
                            </button>
                        <?php endif; ?>

                        <?php if (in_array('flutterwave', $enabledGateways)): ?>
                            <button type="button" class="pm-btn" data-method="flutterwave" aria-pressed="<?= $defaultGateway === 'flutterwave' ? 'true' : 'false' ?>">
                                <span class="logo" aria-hidden="true">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                                        <path d="M4 12h16" />
                                    </svg>
                                </span>
                                <span>Flutterwave</span>
                            </button>
                        <?php endif; ?>
                    </div>

                    <!-- Card Fields for Direct Stripe Checkout -->
                    <div class="card-fields co-card-fields" id="card-fields-section" aria-hidden="true" style="display: none;">
                        <div class="co-field-group">
                            <label class="co-label" for="card-number">Card Number</label>
                            <input type="text" name="card_number" id="card-number" class="co-input" placeholder="4242 •••• •••• 4242" autocomplete="cc-number" />
                        </div>
                        <div class="form-row co-form-row">
                            <div class="co-field-group">
                                <label class="co-label" for="card-expiry">Expiry Date</label>
                                <input type="text" name="card_expiry" id="card-expiry" class="co-input" placeholder="MM/YY" autocomplete="cc-exp" />
                            </div>
                            <div class="co-field-group">
                                <label class="co-label" for="card-cvc">CVC</label>
                                <input type="text" name="card_cvc" id="card-cvc" class="co-input" placeholder="123" autocomplete="cc-csc" />
                            </div>
                        </div>
                    </div>

                    <!-- Gateway Informational Note -->
                    <div id="gateway-info" class="co-gateway-note">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="co-gateway-note__icon">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                        </svg>
                        <span id="gateway-info-text">You'll be securely redirected to Paystack to complete your payment.</span>
                    </div>
                </div>

                <!-- Payment Plan (Full vs Layaway Option) -->
                <?php
                $allowLayaway = !empty(value($event, 'allow_partial_payment', 0));
                $minPercent = (int)value($event, 'partial_payment_min_percent', 25);
                
                // Calculate precise layaway ticket deposit and charges
                $depositTicketSubtotal = round($subtotal * ($minPercent / 100), 2);
                $totalCharges = ($passFee && $subtotal > 0 ? $fee : 0.00) + ($subtotal > 0 ? $extraCharge : 0.00);
                $layawayDueToday = $depositTicketSubtotal + $totalCharges;
                $layawayRemainingBalance = max(0, $subtotal - $depositTicketSubtotal);
                ?>
                <?php if ($allowLayaway && $hasSelectedTickets && !$isFreeOrder): ?>
                <div class="co-payment-type-section" style="margin-top:1.5rem; margin-bottom:1.5rem;">
                    <label class="co-label" style="font-size:0.95rem; font-weight:700; margin-bottom:0.75rem; display:block;">Payment Plan Option</label>
                    <input type="hidden" name="payment_type" id="payment-type-input" value="full">
                    <div style="display:flex; gap:0.75rem; flex-wrap:wrap;">
                        <button type="button" class="co-ptype-btn co-ptype-btn--active" id="ptype-btn-full" onclick="selectPaymentType('full')" style="flex:1; min-width:200px; padding:0.9rem 1rem; border:2px solid rgb(var(--brand)); border-radius:12px; background:rgba(var(--brand), 0.06); cursor:pointer; text-align:left; transition:all 0.2s;">
                            <strong style="display:block; font-size:0.95rem; color:rgb(var(--text));">💰 Pay in Full (Instant Ticket)</strong>
                            <small style="color:rgb(var(--text)); opacity:0.7; display:block; margin-top:0.2rem;">Pay <?= format_price($totalAmount) ?> now and get your admission pass immediately.</small>
                        </button>
                        <button type="button" class="co-ptype-btn" id="ptype-btn-layaway" onclick="selectPaymentType('layaway')" style="flex:1; min-width:200px; padding:0.9rem 1rem; border:2px solid rgba(var(--text), 0.15); border-radius:12px; background:transparent; cursor:pointer; text-align:left; transition:all 0.2s;">
                            <strong style="display:block; font-size:0.95rem; color:#6366f1;">💳 Reserve with Deposit (Layaway)</strong>
                            <small style="color:rgb(var(--text)); opacity:0.7; display:block; margin-top:0.2rem;">Pay <?= format_price($layawayDueToday) ?> today (<?= format_price($depositTicketSubtotal) ?> deposit + <?= format_price($totalCharges) ?> charges). Pay remaining <?= format_price($layawayRemainingBalance) ?> before the event.</small>
                        </button>
                    </div>
                    <div id="layaway-plan-details" style="display:none; margin-top:0.85rem; padding:1.1rem; background:rgba(99, 102, 241, 0.06); border:1px solid rgba(99, 102, 241, 0.25); border-radius:12px; font-size:0.88rem; color:rgb(var(--text)); line-height:1.6;">
                        <div style="font-weight:700; color:#6366f1; margin-bottom:0.45rem; font-size:0.92rem; display:flex; align-items:center; gap:0.4rem;">
                            <span>✨</span> Layaway Deposit &amp; Charges Breakdown:
                        </div>
                        <div style="display:grid; grid-template-columns:1fr auto; gap:0.35rem 1rem; padding:0.6rem 0; border-top:1px dashed rgba(99, 102, 241, 0.25); border-bottom:1px dashed rgba(99, 102, 241, 0.25); margin-bottom:0.6rem;">
                            <span>• Ticket Reservation Deposit (<?= $minPercent ?>%):</span>
                            <strong id="layaway-box-deposit"><?= format_price($depositTicketSubtotal) ?></strong>
                            
                            <span>• Ticketing Platform Fee (<?= $platformFeePercent ?>%):</span>
                            <strong id="layaway-box-fee"><?= ($passFee && $subtotal > 0) ? format_price($fee) : '$0.00 (Covered by Host)' ?></strong>
                            
                            <span>• Service / Processing Charge:</span>
                            <strong id="layaway-box-extra"><?= $subtotal > 0 ? format_price($extraCharge) : '$0.00' ?></strong>
                            
                            <span style="font-weight:700; color:rgb(var(--brand)); padding-top:0.35rem; border-top:1px solid rgba(99, 102, 241, 0.15);">💳 Total Due Today (Deposit + Charges):</span>
                            <strong style="font-weight:800; color:rgb(var(--brand)); padding-top:0.35rem; border-top:1px solid rgba(99, 102, 241, 0.15);" id="layaway-box-due-today"><?= format_price($layawayDueToday) ?></strong>
                        </div>
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.45rem;">
                            <span style="font-weight:600;">• Remaining Balance to Pay:</span>
                            <strong style="color:#6366f1;" id="layaway-box-remaining"><?= format_price($layawayRemainingBalance) ?></strong>
                        </div>
                        <div style="margin-top:0.35rem; font-size:0.8rem; opacity:0.82;">🔒 Your spot is reserved immediately. Your official entry pass is activated once the remaining balance is paid.</div>
                        <div style="margin-top:0.25rem; font-size:0.78rem; opacity:0.75; color:#b45309;">⚠️ If payment is not completed before event start, payments are non-refundable, but automatically converted into Event Credit for future events.</div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Submit CTA -->
                <div class="co-submit-wrap">
                    <button type="submit" id="checkout-submit" class="checkout-btn co-submit-btn" <?= !$hasSelectedTickets ? 'disabled' : '' ?>>
                        <?= $isFreeOrder ? 'Get Free Tickets →' : ($hasSelectedTickets ? 'Pay with ' . ucfirst($defaultGateway) . ' →' : 'Select Tickets to Continue') ?>
                    </button>
                    <p class="co-trust-note">
                        🔒 Safe &amp; encrypted transaction. Digital passes delivered instantly to your email.
                    </p>
                </div>
            </div>
        </section>

        <!-- Right Column: Sticky Order Summary -->
        <aside class="co-summary-section order-summary">
            <div class="co-summary-card summary-card">
                
                <!-- Event Header in Summary -->
                <div class="co-summary-event">
                    <div class="co-summary-event__title">
                        <span class="co-summary-event__icon">🎟️</span>
                        <strong><?= e($eventName) ?></strong>
                    </div>
                    <div class="co-summary-event__meta">
                        <?php if ($eventDate): ?>
                            <span>📅 <?= date('M d, Y • g:i A', $eventDate) ?></span>
                        <?php endif; ?>
                        <span title="<?= e($eventLocation) ?>">📍 <?= e($eventLocation) ?></span>
                    </div>
                </div>

                <div class="summary-divider co-divider"></div>

                <!-- Selected Tickets Itemized -->
                <div class="co-summary-items">
                    <span class="co-summary-subhead">Selected Tickets</span>
                    <?php if (empty($selectedItems)): ?>
                        <p class="co-summary-empty">No tickets selected.</p>
                    <?php else: ?>
                        <?php foreach ($selectedItems as $item): ?>
                            <div class="summary-row co-ticket-row">
                                <span class="co-ticket-row__name">
                                    <strong><?= (int)$item['qty'] ?>×</strong> <?= e($item['name']) ?>
                                    <?php if (!empty($item['admits']) && (int)$item['admits'] > 1): ?>
                                        <span class="co-admits-pill">👥 Admits <?= (int)$item['admits'] ?></span>
                                    <?php endif; ?>
                                </span>
                                <strong class="co-ticket-row__price">
                                    <?= $item['subtotal'] > 0 ? format_price($item['subtotal']) : 'Free' ?>
                                </strong>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Selected Add-Ons Itemized -->
                <div id="co-summary-addons-container" style="display:none;">
                    <div class="summary-divider co-divider"></div>
                    <div class="co-summary-items">
                        <span class="co-summary-subhead" style="color:#0d9488;">🛍️ Merchandise &amp; Add-Ons</span>
                        <div id="co-summary-addons-list"></div>
                    </div>
                </div>

                <div class="summary-divider co-divider"></div>

                <!-- Price Breakdown -->
                <div class="co-breakdown">
                    <div class="summary-row co-breakdown__row" id="row-summary-subtotal">
                        <span id="label-summary-subtotal">Ticket Subtotal</span>
                        <strong id="summary-subtotal"><?= ($hasSelectedTickets && $subtotal <= 0) ? 'Free' : format_price($subtotal) ?></strong>
                    </div>

                    <div class="summary-row co-breakdown__row" id="row-addons-subtotal" style="display:none; color:#0d9488;">
                        <span>Merchandise &amp; Add-Ons</span>
                        <strong id="summary-addons-subtotal">$0.00</strong>
                    </div>

                    <!-- Layaway Deposit Row (shown only when Layaway is active) -->
                    <div class="summary-row co-breakdown__row" id="row-layaway-deposit" style="display:none; color:#6366f1; font-weight:600;">
                        <span>Deposit Portion (<?= $minPercent ?>%)</span>
                        <strong id="summary-layaway-deposit"><?= format_price($depositTicketSubtotal) ?></strong>
                    </div>

                    <div class="summary-row co-breakdown__row co-breakdown__row--fee">
                        <span>Ticketing Fee (<?= $platformFeePercent ?>%)</span>
                        <strong id="summary-fee"><?= ($passFee && $subtotal > 0) ? format_price($fee) : '$0.00 (Covered by Host)' ?></strong>
                    </div>

                    <div class="summary-row co-breakdown__row">
                        <span>Service / Processing</span>
                        <strong id="summary-extra-charge"><?= $subtotal > 0 ? format_price($extraCharge) : '$0.00' ?></strong>
                    </div>

                    <!-- Discount Row (revealed on valid promo code) -->
                    <div class="summary-row co-breakdown__row co-breakdown__row--discount" id="discount-row" style="display:none;">
                        <span>Discount Applied</span>
                        <strong id="discount-amount-text">-</strong>
                    </div>

                    <!-- Event Credit Row (revealed on valid credit applied) -->
                    <div class="summary-row co-breakdown__row" id="credit-row" style="display:none; color:#d97706; font-weight:700;">
                        <span>Event Credit Applied</span>
                        <strong id="credit-amount-text">-</strong>
                    </div>

                    <!-- Layaway Remaining Balance Row (shown only when Layaway is active) -->
                    <div class="summary-row co-breakdown__row" id="row-layaway-future" style="display:none; padding-top:0.4rem; border-top:1px dashed rgba(var(--text), 0.15); font-size:0.85rem; color:rgb(var(--text)); opacity:0.85;">
                        <span>Remaining Balance Later</span>
                        <strong id="summary-layaway-future"><?= format_price($layawayRemainingBalance) ?></strong>
                    </div>
                </div>

                <!-- Promo Code Accordion / Box -->
                <div class="promo-box co-promo-box">
                    <label for="promo-code-input" class="co-promo-label">🏷️ Have a Promo Code?</label>
                    <div class="co-promo-input-wrap">
                        <input type="text" name="promo_code" id="promo-code-input" class="co-promo-input" placeholder="e.g. DISCOUNT10" autocomplete="off" />
                        <button type="button" id="apply-promo-btn" class="co-promo-btn">Apply</button>
                    </div>
                    <div id="promo-message" class="co-promo-msg" style="display:none;" aria-live="polite"></div>
                </div>

                <!-- Event Credits Box -->
                <div class="promo-box co-promo-box" style="margin-top:0.75rem; border-color:rgba(245, 158, 11, 0.35); background:rgba(245, 158, 11, 0.04);">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.4rem;">
                        <label class="co-promo-label" style="color:#b45309; margin-bottom:0;">
                            ✨ Have Event Credits?
                        </label>
                        <a href="<?= \url('/layaway/credits') ?>" target="_blank" style="font-size:0.75rem; color:#b45309; text-decoration:underline;">
                            Check balance ↗
                        </a>
                    </div>
                    <?php if (!empty($totalCreditBalance) && $totalCreditBalance > 0): ?>
                        <div style="font-size:0.82rem; color:#92400e; margin-bottom:0.5rem;">
                            You have <strong><?= format_price($totalCreditBalance) ?></strong> available in your account!
                        </div>
                        <button type="button" id="apply-credit-btn" class="co-promo-btn" style="width:100%; justify-content:center; background:#d97706; border-color:#d97706; color:#fff;">
                            Apply <?= format_price(min($totalCreditBalance, $totalAmount)) ?> Credit
                        </button>
                    <?php else: ?>
                        <div style="font-size:0.8rem; color:rgb(var(--text)); opacity:0.75; margin-bottom:0.5rem;">
                            Credits from past layaways can be applied here to reduce or cover your ticket cost.
                        </div>
                        <button type="button" id="apply-credit-btn" class="co-promo-btn" style="width:100%; justify-content:center; background:#d97706; border-color:#d97706; color:#fff;">
                            Apply Available Credits
                        </button>
                    <?php endif; ?>
                    <input type="hidden" name="use_credit" id="use-credit-input" value="0">
                    <div id="credit-message" class="co-promo-msg" style="display:none;" aria-live="polite"></div>
                </div>

                <div class="summary-divider co-divider"></div>

                <!-- Total Amount -->
                <div class="summary-total co-summary-total">
                    <span id="label-final-total">Total Amount</span>
                    <strong id="final-total-amount"><?= ($hasSelectedTickets && $totalAmount <= 0) ? 'Free' : format_price($totalAmount) ?></strong>
                </div>
            </div>

            <!-- Trust / Guarantee Badge -->
            <div class="co-trust-card">
                <span class="co-trust-card__icon">⚡</span>
                <div>
                    <strong>Instant Digital Delivery</strong>
                    <p>Tickets are issued immediately with QR code validation for effortless gate check-in.</p>
                </div>
            </div>
        </aside>
    </div>
</form>

<!-- ═══ Checkout Client-Side Logic ═══ -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    let isFreeOrder = <?= $isFreeOrder ? 'true' : 'false' ?>;
    let currentPaymentType = 'full';
    let formattedTotalFull = '<?= format_price($totalAmount) ?>';
    let formattedDepositDueToday = '<?= format_price($layawayDueToday) ?>';
    let formattedDepositTicket = '<?= format_price($depositTicketSubtotal) ?>';
    let formattedLayawayRemaining = '<?= format_price($layawayRemainingBalance) ?>';

    const pmBtns = document.querySelectorAll('.pm-btn');
    const selectedPmInput = document.getElementById('selected-payment-method');
    const cardFields = document.getElementById('card-fields-section');
    const submitBtn = document.getElementById('checkout-submit');
    const gatewayInfo = document.getElementById('gateway-info');
    const gatewayInfoText = document.getElementById('gateway-info-text');
    const providerSection = document.getElementById('payment-provider-section');
    const freeBadge = document.getElementById('free-registration-badge');

    const gatewayMessages = {
        paystack: "You'll be securely redirected to Paystack to complete your payment.",
        stripe: "Enter your card details below to pay directly via Stripe.",
        flutterwave: "You'll be securely redirected to Flutterwave to complete your payment."
    };

    const buttonLabels = {
        paystack: 'Pay with Paystack →',
        stripe: 'Complete Purchase & Get Tickets →',
        flutterwave: 'Pay with Flutterwave →'
    };

    const gatewayColors = {
        paystack: { bg: 'rgba(16, 185, 129, 0.08)', border: 'rgba(16, 185, 129, 0.25)', text: '#10b981' },
        stripe: { bg: 'rgba(99, 91, 255, 0.08)', border: 'rgba(99, 91, 255, 0.25)', text: '#635bff' },
        flutterwave: { bg: 'rgba(245, 158, 11, 0.08)', border: 'rgba(245, 158, 11, 0.25)', text: '#f59e0b' }
    };

    function updateGatewayUI(method) {
        if (isFreeOrder) {
            if (submitBtn) submitBtn.textContent = 'Get Free Tickets →';
            return;
        }

        // Card fields visible only for stripe
        if (cardFields) {
            cardFields.style.display = method === 'stripe' ? 'block' : 'none';
            cardFields.setAttribute('aria-hidden', method === 'stripe' ? 'false' : 'true');
        }

        // Update submit button text
        if (submitBtn) {
            const gatewayName = method === 'stripe' ? 'Stripe' : (method === 'flutterwave' ? 'Flutterwave' : 'Paystack');
            if (currentPaymentType === 'layaway') {
                submitBtn.textContent = `Pay Deposit (${formattedDepositDueToday}) with ${gatewayName} →`;
            } else {
                submitBtn.textContent = buttonLabels[method] || `Complete Purchase (${formattedTotalFull}) →`;
            }
        }

        // Update gateway information capsule
        if (gatewayInfo && gatewayInfoText) {
            const colors = gatewayColors[method] || gatewayColors.paystack;
            gatewayInfo.style.background = colors.bg;
            gatewayInfo.style.borderColor = colors.border;
            gatewayInfo.style.color = colors.text;
            gatewayInfoText.textContent = gatewayMessages[method] || '';
        }
    }

    pmBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            pmBtns.forEach(b => b.setAttribute('aria-pressed', 'false'));
            this.setAttribute('aria-pressed', 'true');
            const method = this.getAttribute('data-method');
            if (selectedPmInput) {
                selectedPmInput.value = method;
            }
            updateGatewayUI(method);
        });
    });

    // ── Full-Page Processing Overlay Helper ──────────
    function showProcessingOverlay(title, desc) {
        let overlay = document.querySelector('.evently-processing-overlay');
        if (!overlay) {
            overlay = document.createElement('div');
            overlay.className = 'evently-processing-overlay';
            overlay.innerHTML = `
                <div class="evently-processing-card">
                    <div class="evently-spinner-ring"></div>
                    <h3 class="evently-processing-title">${title || 'Processing Request...'}</h3>
                    <p class="evently-processing-desc">${desc || 'Please wait a moment while we process your request.'}</p>
                </div>
            `;
            document.body.appendChild(overlay);
        } else {
            const titleEl = overlay.querySelector('.evently-processing-title');
            const descEl = overlay.querySelector('.evently-processing-desc');
            if (titleEl) titleEl.textContent = title || 'Processing Request...';
            if (descEl) descEl.textContent = desc || 'Please wait a moment.';
        }
        requestAnimationFrame(() => {
            overlay.classList.add('active');
        });
    }

    // ── Form Submission Handling ─────────────────────
    const checkoutForm = document.getElementById('checkout-form-main');
    if (checkoutForm) {
        checkoutForm.addEventListener('submit', function(e) {
            const nameInput = document.getElementById('billing-name');
            const emailInput = document.getElementById('billing-email');

            if (nameInput && !nameInput.value.trim()) {
                e.preventDefault();
                nameInput.classList.add('invalid');
                nameInput.focus();
                return;
            }

            if (emailInput && !emailInput.value.trim()) {
                e.preventDefault();
                emailInput.classList.add('invalid');
                emailInput.focus();
                return;
            }

            if (isFreeOrder) {
                if (selectedPmInput) selectedPmInput.value = 'free';
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = `<span class="btn-spinner"></span> Issuing Free Tickets...`;
                }
                showProcessingOverlay(
                    'Issuing Your Tickets',
                    'Generating your digital QR ticket passes. Please wait a moment.'
                );
                return;
            }

            const method = selectedPmInput ? selectedPmInput.value : 'paystack';
            const gatewayName = method === 'stripe' ? 'Stripe' : (method === 'flutterwave' ? 'Flutterwave' : 'Paystack');

            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = `<span class="btn-spinner"></span> Connecting to ${gatewayName}...`;
            }

            showProcessingOverlay(
                `Connecting to ${gatewayName}`,
                `Securing your transaction and redirecting to ${gatewayName}. Please do not close or refresh this page.`
            );
        });
    }

    // ── Promo Code Validation ─────────────────────────
    const applyPromoBtn = document.getElementById('apply-promo-btn');
    const promoInput = document.getElementById('promo-code-input');
    const promoMsg = document.getElementById('promo-message');
    const discountRow = document.getElementById('discount-row');
    const discountText = document.getElementById('discount-amount-text');

    if (applyPromoBtn && promoInput) {
        applyPromoBtn.addEventListener('click', function() {
            const code = promoInput.value.trim();
            if (!code) {
                promoMsg.style.display = 'block';
                promoMsg.style.color = '#ef4444';
                promoMsg.textContent = 'Please enter a coupon code.';
                return;
            }

            applyPromoBtn.disabled = true;
            applyPromoBtn.textContent = '...';

            fetch('<?= \url('/checkout/validate-promo') ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    code: code,
                    event_id: '<?= $eventId ?>',
                    subtotal: '<?= $subtotal ?>',
                    csrf_token: '<?= csrf_token() ?>'
                })
            })
            .then(res => {
                if (!res.ok) throw new Error('Request failed');
                return res.json();
            })
            .then(data => {
                promoMsg.style.display = 'block';
                if (data.success) {
                    promoMsg.style.color = '#10b981';
                    promoMsg.textContent = data.message;
                    discountRow.style.display = 'flex';
                    discountText.textContent = '-' + data.formatted_discount;
                    
                    formattedTotalFull = data.formatted_new_total || formattedTotalFull;
                    const finalTotalEl = document.getElementById('final-total-amount');
                    if (finalTotalEl && data.formatted_new_total) {
                        if (currentPaymentType === 'full') {
                            finalTotalEl.textContent = data.is_free ? 'Free' : data.formatted_new_total;
                        }
                    }

                    if (data.is_free) {
                        isFreeOrder = true;
                        if (providerSection) providerSection.style.display = 'none';
                        if (freeBadge) freeBadge.style.display = 'flex';
                        if (submitBtn) submitBtn.textContent = 'Get Free Tickets →';
                        if (selectedPmInput) selectedPmInput.value = 'free';
                        const feeEl = document.getElementById('summary-fee');
                        const extraEl = document.getElementById('summary-extra-charge');
                        if (feeEl) feeEl.textContent = '$0.00';
                        if (extraEl) extraEl.textContent = '$0.00';
                    }
                    
                    promoInput.readOnly = true;
                    applyPromoBtn.textContent = '✓ Applied';
                } else {
                    promoMsg.style.color = '#ef4444';
                    promoMsg.textContent = data.message;
                    discountRow.style.display = 'none';
                    applyPromoBtn.disabled = false;
                    applyPromoBtn.textContent = 'Apply';
                }
            })
            .catch(err => {
                promoMsg.style.display = 'block';
                promoMsg.style.color = '#ef4444';
                promoMsg.textContent = 'Failed to validate coupon. Please try again.';
                applyPromoBtn.disabled = false;
                applyPromoBtn.textContent = 'Apply';
                console.error(err);
            });
        });
    }

    // ── Event Credit Validation & Application ─────────────────────
    const applyCreditBtn = document.getElementById('apply-credit-btn');
    const creditMsg = document.getElementById('credit-message');
    const creditRow = document.getElementById('credit-row');
    const creditText = document.getElementById('credit-amount-text');
    const useCreditInput = document.getElementById('use-credit-input');

    // OTP Modal (injected dynamically)
    let otpModal = null;
    function createOtpModal() {
        if (otpModal) return;
        const div = document.createElement('div');
        div.id = 'credit-otp-modal';
        div.innerHTML = `
            <div style="position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:9999; display:flex; align-items:center; justify-content:center; padding:1rem;">
                <div style="background:#fff; border-radius:16px; padding:2rem; max-width:400px; width:100%; box-shadow:0 20px 60px rgba(0,0,0,0.2); text-align:center;">
                    <div style="font-size:2rem; margin-bottom:0.5rem;">🔐</div>
                    <h3 style="margin:0 0 0.5rem; font-size:1.1rem; color:#0f172a;">Email Verification Required</h3>
                    <p id="otp-modal-desc" style="font-size:0.85rem; color:#64748b; margin:0 0 1.25rem;">A 6-digit code has been sent to your email. Enter it below to authorize credit usage.</p>
                    <div id="otp-modal-msg" style="display:none; margin-bottom:0.75rem; padding:0.5rem; border-radius:8px; font-size:0.82rem; font-weight:600;"></div>
                    <input type="text" id="otp-code-input" maxlength="6" pattern="[0-9]{6}" inputmode="numeric" placeholder="••••••"
                           style="width:100%; text-align:center; font-size:1.5rem; font-weight:700; letter-spacing:0.3em; padding:0.75rem; border:2px solid #e2e8f0; border-radius:10px; outline:none; font-family:Consolas,monospace; box-sizing:border-box;" />
                    <button type="button" id="otp-verify-btn" style="width:100%; margin-top:0.75rem; padding:0.75rem; border:none; border-radius:10px; background:#10b981; color:#fff; font-weight:700; font-size:0.95rem; cursor:pointer;">
                        Verify & Apply Credits
                    </button>
                    <button type="button" id="otp-resend-btn" style="margin-top:0.5rem; background:none; border:none; color:#6366f1; font-size:0.82rem; font-weight:600; cursor:pointer; text-decoration:underline;">
                        Resend Code
                    </button>
                    <button type="button" id="otp-close-btn" style="margin-top:0.25rem; background:none; border:none; color:#94a3b8; font-size:0.82rem; cursor:pointer;">
                        Cancel
                    </button>
                </div>
            </div>`;
        document.body.appendChild(div);
        otpModal = div;

        document.getElementById('otp-close-btn').addEventListener('click', () => { otpModal.style.display = 'none'; applyCreditBtn.disabled = false; applyCreditBtn.textContent = 'Apply Available Credits'; });
        document.getElementById('otp-verify-btn').addEventListener('click', verifyOtp);
        document.getElementById('otp-resend-btn').addEventListener('click', () => { sendOtp(getCreditEmail()); });
        document.getElementById('otp-code-input').addEventListener('keydown', (e) => { if (e.key === 'Enter') verifyOtp(); });
    }

    function getCreditEmail() {
        const emailInput = document.getElementById('billing-email') || document.querySelector('input[name="email"]');
        return emailInput ? emailInput.value.trim() : '<?= $currentUser ? e($currentUser->email) : '' ?>';
    }

    function showOtpMsg(msg, isError) {
        const el = document.getElementById('otp-modal-msg');
        if (!el) return;
        el.style.display = 'block';
        el.style.background = isError ? '#fef2f2' : '#f0fdf4';
        el.style.color = isError ? '#dc2626' : '#16a34a';
        el.textContent = msg;
    }

    function sendOtp(email) {
        const resendBtn = document.getElementById('otp-resend-btn');
        if (resendBtn) { resendBtn.disabled = true; resendBtn.textContent = 'Sending...'; }
        fetch('<?= \url('/checkout/credit/send-otp') ?>', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ email: email, csrf_token: '<?= csrf_token() ?>' })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                showOtpMsg(data.message, false);
                if (document.getElementById('otp-modal-desc')) {
                    document.getElementById('otp-modal-desc').textContent = 'A 6-digit code has been sent to ' + (data.masked_email || email) + '. Enter it below.';
                }
            } else {
                showOtpMsg(data.message, true);
            }
            if (resendBtn) { resendBtn.disabled = false; resendBtn.textContent = 'Resend Code'; }
        })
        .catch(() => { showOtpMsg('Failed to send code. Try again.', true); if (resendBtn) { resendBtn.disabled = false; resendBtn.textContent = 'Resend Code'; } });
    }

    function verifyOtp() {
        const code = document.getElementById('otp-code-input').value.trim();
        if (code.length !== 6) { showOtpMsg('Please enter a 6-digit code.', true); return; }
        const btn = document.getElementById('otp-verify-btn');
        btn.disabled = true; btn.textContent = 'Verifying...';
        fetch('<?= \url('/checkout/credit/verify-otp') ?>', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ otp: code, csrf_token: '<?= csrf_token() ?>' })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                showOtpMsg('✓ ' + data.message, false);
                setTimeout(() => {
                    otpModal.style.display = 'none';
                    // Now re-trigger credit validation which will succeed
                    applyCreditBtn.disabled = true;
                    applyCreditBtn.textContent = 'Applying credits...';
                    fetchCredits(getCreditEmail());
                }, 800);
            } else {
                showOtpMsg(data.message, true);
                btn.disabled = false; btn.textContent = 'Verify & Apply Credits';
                if (data.locked || data.expired) {
                    document.getElementById('otp-code-input').disabled = true;
                    btn.disabled = true;
                }
            }
        })
        .catch(() => { showOtpMsg('Verification failed. Try again.', true); btn.disabled = false; btn.textContent = 'Verify & Apply Credits'; });
    }

    function fetchCredits(email) {
        fetch('<?= \url('/checkout/validate-credit') ?>', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ email: email, current_total: '<?= $totalAmount ?>', csrf_token: '<?= csrf_token() ?>' })
        })
        .then(res => res.json())
        .then(data => {
            creditMsg.style.display = 'block';
            if (data.requires_otp) {
                // Show OTP modal
                createOtpModal();
                otpModal.style.display = 'block';
                sendOtp(email);
                document.getElementById('otp-code-input').value = '';
                document.getElementById('otp-code-input').disabled = false;
                const vBtn = document.getElementById('otp-verify-btn');
                if (vBtn) { vBtn.disabled = false; vBtn.textContent = 'Verify & Apply Credits'; }
                creditMsg.style.color = '#6366f1';
                creditMsg.textContent = 'Email verification required — check your inbox for a 6-digit code.';
                return;
            }
            if (data.success) {
                creditMsg.style.color = '#10b981';
                creditMsg.textContent = data.message;
                if (creditRow) creditRow.style.display = 'flex';
                if (creditText) creditText.textContent = '-' + data.formatted_discount;
                if (useCreditInput) useCreditInput.value = '1';

                const finalTotalEl = document.getElementById('final-total-amount');
                if (finalTotalEl && data.formatted_new_total) {
                    finalTotalEl.textContent = data.is_free ? 'Free (Covered by Credits)' : data.formatted_new_total;
                }

                if (data.is_free) {
                    isFreeOrder = true;
                    if (providerSection) providerSection.style.display = 'none';
                    if (freeBadge) freeBadge.style.display = 'flex';
                    if (submitBtn) submitBtn.textContent = 'Get Tickets with Credits →';
                    if (selectedPmInput) selectedPmInput.value = 'free';
                    const feeEl = document.getElementById('summary-fee');
                    const extraEl = document.getElementById('summary-extra-charge');
                    if (feeEl) feeEl.textContent = '$0.00';
                    if (extraEl) extraEl.textContent = '$0.00';
                }

                applyCreditBtn.textContent = '✓ Credit Applied (' + data.formatted_discount + ')';
            } else {
                creditMsg.style.color = '#ef4444';
                creditMsg.textContent = data.message;
                if (creditRow) creditRow.style.display = 'none';
                if (useCreditInput) useCreditInput.value = '0';
                applyCreditBtn.disabled = false;
                applyCreditBtn.textContent = 'Apply Available Credits';
            }
        })
        .catch(err => {
            creditMsg.style.display = 'block';
            creditMsg.style.color = '#ef4444';
            creditMsg.textContent = 'Failed to check credits. Please try again.';
            applyCreditBtn.disabled = false;
            applyCreditBtn.textContent = 'Apply Available Credits';
            console.error(err);
        });
    }

    if (applyCreditBtn) {
        applyCreditBtn.addEventListener('click', function() {
            const email = getCreditEmail();

            if (!email) {
                creditMsg.style.display = 'block';
                creditMsg.style.color = '#ef4444';
                creditMsg.textContent = 'Please enter your email address in the contact details first.';
                const emailInput = document.getElementById('billing-email') || document.querySelector('input[name="email"]');
                if (emailInput) emailInput.focus();
                return;
            }

            applyCreditBtn.disabled = true;
            applyCreditBtn.textContent = 'Checking credits...';
            fetchCredits(email);
        });
    }

    // ── Attendee Sync & Copy Buyer ────────────────────
    const billingName = document.getElementById('billing-name');
    const billingEmail = document.getElementById('billing-email');
    const primaryAttName = document.querySelector('.att-primary-name');
    const primaryAttEmail = document.querySelector('.att-primary-email');
    const copyBuyerBtn = document.getElementById('btn-copy-buyer-to-all');

    if (billingName && primaryAttName) {
        billingName.addEventListener('input', function() {
            if (!primaryAttName.dataset.customized) {
                primaryAttName.value = this.value;
            }
        });
        primaryAttName.addEventListener('input', function() {
            this.dataset.customized = 'true';
        });
    }

    if (billingEmail && primaryAttEmail) {
        billingEmail.addEventListener('input', function() {
            if (!primaryAttEmail.dataset.customized) {
                primaryAttEmail.value = this.value;
            }
        });
        primaryAttEmail.addEventListener('input', function() {
            this.dataset.customized = 'true';
        });
    }

    if (copyBuyerBtn && billingName && billingEmail) {
        copyBuyerBtn.addEventListener('click', function() {
            const bName = billingName.value.trim();
            const bEmail = billingEmail.value.trim();

            if (!bName && !bEmail) {
                alert('Please enter your primary name and email first.');
                billingName.focus();
                return;
            }

            document.querySelectorAll('.att-guest-name').forEach((input, idx) => {
                input.value = bName ? `${bName} (Guest ${idx + 2})` : '';
                input.dataset.customized = 'true';
            });

            document.querySelectorAll('.att-guest-email').forEach(input => {
                input.value = bEmail;
                input.dataset.customized = 'true';
            });

            const originalText = copyBuyerBtn.innerHTML;
            copyBuyerBtn.innerHTML = '<span>✓ Copied to All</span>';
            copyBuyerBtn.style.background = '#0B7366';
            copyBuyerBtn.style.color = '#ffffff';
            setTimeout(() => {
                copyBuyerBtn.innerHTML = originalText;
                copyBuyerBtn.style.background = '';
                copyBuyerBtn.style.color = '';
            }, 2000);
        });
    }

    // ── Payment Type Selection (Full vs Layaway) ──
    window.selectPaymentType = function(type) {
        currentPaymentType = type;
        const input = document.getElementById('payment-type-input');
        const details = document.getElementById('layaway-plan-details');
        const btnFull = document.getElementById('ptype-btn-full');
        const btnLayaway = document.getElementById('ptype-btn-layaway');
        const finalTotalEl = document.getElementById('final-total-amount');
        const labelTotalEl = document.getElementById('label-final-total');
        const rowDeposit = document.getElementById('row-layaway-deposit');
        const rowFuture = document.getElementById('row-layaway-future');

        if (input) input.value = type;
        if (details) details.style.display = type === 'layaway' ? 'block' : 'none';

        if (btnFull && btnLayaway) {
            if (type === 'layaway') {
                btnLayaway.style.borderColor = '#6366f1';
                btnLayaway.style.background = 'rgba(99, 102, 241, 0.08)';
                btnFull.style.borderColor = 'rgba(var(--text), 0.15)';
                btnFull.style.background = 'transparent';
                if (labelTotalEl) labelTotalEl.textContent = 'Due Today (Deposit + Charges)';
                if (finalTotalEl) finalTotalEl.textContent = formattedDepositDueToday;
                if (rowDeposit) rowDeposit.style.display = 'flex';
                if (rowFuture) rowFuture.style.display = 'flex';
            } else {
                btnFull.style.borderColor = 'rgb(var(--brand))';
                btnFull.style.background = 'rgba(var(--brand), 0.06)';
                btnLayaway.style.borderColor = 'rgba(var(--text), 0.15)';
                btnLayaway.style.background = 'transparent';
                if (labelTotalEl) labelTotalEl.textContent = 'Total Amount';
                if (finalTotalEl) finalTotalEl.textContent = formattedTotalFull;
                if (rowDeposit) rowDeposit.style.display = 'none';
                if (rowFuture) rowFuture.style.display = 'none';
            }
        }
        const method = selectedPmInput ? selectedPmInput.value : 'paystack';
        updateGatewayUI(method);
    };

    // ── Add-Ons & Event Merchandise Recalculation ──
    const currencySymbol = '<?= e(get_currency_symbol()) ?>';
    const baseTicketSubtotal = <?= (float)$subtotal ?>;
    const passFeeEnabled = <?= $passFee ? 'true' : 'false' ?>;
    const platformFeePct = <?= (float)($platformFeePercent ?? 5) ?>;
    const baseExtraCharge = <?= (float)$extraCharge ?>;
    let currentDiscount = 0.0;

    function formatMoney(amount) {
        return currencySymbol + Number(amount).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function recalculateAddonsAndTotals() {
        let addonsSubtotal = 0.0;
        const addonsListEl = document.getElementById('co-summary-addons-list');
        const addonsContainerEl = document.getElementById('co-summary-addons-container');
        const addonsRowEl = document.getElementById('row-addons-subtotal');
        const addonsTotalEl = document.getElementById('summary-addons-subtotal');

        let summaryHtml = '';
        let hasActiveAddons = false;

        document.querySelectorAll('.addon-qty-input').forEach(input => {
            const qty = parseInt(input.value) || 0;
            const price = parseFloat(input.dataset.price) || 0.0;
            const name = input.dataset.name || 'Merchandise';

            if (qty > 0) {
                hasActiveAddons = true;
                const itemTotal = qty * price;
                addonsSubtotal += itemTotal;
                summaryHtml += `
                    <div class="summary-row co-ticket-row" style="padding:0.35rem 0;">
                        <span class="co-ticket-row__name">
                            <strong>${qty}×</strong> ${name}
                        </span>
                        <strong class="co-ticket-row__price" style="color:#0d9488;">
                            ${itemTotal > 0 ? formatMoney(itemTotal) : 'Free'}
                        </strong>
                    </div>
                `;
            }
        });

        if (addonsContainerEl) {
            addonsContainerEl.style.display = hasActiveAddons ? 'block' : 'none';
        }
        if (addonsListEl) {
            addonsListEl.innerHTML = summaryHtml;
        }
        if (addonsRowEl && addonsTotalEl) {
            addonsRowEl.style.display = hasActiveAddons ? 'flex' : 'none';
            addonsTotalEl.textContent = formatMoney(addonsSubtotal);
        }

        const combinedSubtotal = baseTicketSubtotal + addonsSubtotal;
        const feeAmount = (passFeeEnabled && combinedSubtotal > 0) ? (combinedSubtotal * (platformFeePct / 100)) : 0.0;
        const netSub = Math.max(0, combinedSubtotal - currentDiscount);
        const extraChargeAmount = netSub > 0 ? baseExtraCharge : 0.0;
        const newTotal = netSub > 0 ? Math.max(0, netSub + feeAmount + extraChargeAmount) : 0.0;

        // Update fee & extra displays
        const feeEl = document.getElementById('summary-fee');
        if (feeEl) {
            feeEl.textContent = (passFeeEnabled && combinedSubtotal > 0) ? formatMoney(feeAmount) : '$0.00 (Covered by Host)';
        }
        const extraEl = document.getElementById('summary-extra-charge');
        if (extraEl) {
            extraEl.textContent = netSub > 0 ? formatMoney(extraChargeAmount) : '$0.00';
        }

        // Update final total display
        const finalTotalEl = document.getElementById('final-total-amount');
        formattedTotalFull = (combinedSubtotal <= 0 && isFreeOrder) ? 'Free' : formatMoney(newTotal);
        if (finalTotalEl && currentPaymentType === 'full') {
            finalTotalEl.textContent = formattedTotalFull;
        }

        // Update payment provider UI & submit button
        const currentMethod = selectedPmInput ? selectedPmInput.value : 'paystack';
        updateGatewayUI(currentMethod);
    }

    document.querySelectorAll('.addon-step-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const addonId = this.dataset.id;
            const input = document.getElementById('addon-qty-' + addonId);
            if (!input) return;
            let val = parseInt(input.value) || 0;
            const maxAttr = input.getAttribute('max');
            const max = maxAttr !== null && maxAttr !== '' ? parseInt(maxAttr) : Infinity;

            if (this.classList.contains('addon-step-plus')) {
                if (val < max) {
                    input.value = val + 1;
                }
            } else if (this.classList.contains('addon-step-minus')) {
                if (val > 0) {
                    input.value = val - 1;
                }
            }
            recalculateAddonsAndTotals();
        });
    });

    document.querySelectorAll('.addon-qty-input').forEach(input => {
        input.addEventListener('input', function() {
            let val = parseInt(this.value) || 0;
            if (val < 0) this.value = 0;
            const maxAttr = this.getAttribute('max');
            const max = maxAttr !== null && maxAttr !== '' ? parseInt(maxAttr) : Infinity;
            if (val > max) this.value = max;
            recalculateAddonsAndTotals();
        });
    });

    // Initialize UI on page load
    updateGatewayUI(selectedPmInput ? selectedPmInput.value : 'paystack');
});

// Lightbox modal for merchandise preview
window.openAddonImageModal = function(src, title) {
    let modal = document.getElementById('addon-image-lightbox');
    if (!modal) {
        modal = document.createElement('div');
        modal.id = 'addon-image-lightbox';
        modal.style.cssText = 'position:fixed; inset:0; z-index:99999; background:rgba(0,0,0,0.8); backdrop-filter:blur(6px); display:flex; align-items:center; justify-content:center; padding:1.5rem; cursor:pointer;';
        modal.innerHTML = `
            <div style="position:relative; max-width:540px; width:100%; background:rgb(var(--card)); border-radius:18px; overflow:hidden; border:1px solid rgba(var(--muted),0.2); box-shadow:0 20px 40px rgba(0,0,0,0.3); cursor:default;" onclick="event.stopPropagation()">
                <div style="display:flex; align-items:center; justify-content:space-between; padding:0.85rem 1.25rem; border-bottom:1px solid rgba(var(--muted),0.15);">
                    <strong id="addon-lightbox-title" style="font-size:0.95rem; color:rgb(var(--text));"></strong>
                    <button type="button" onclick="closeAddonImageModal()" style="background:none; border:none; font-size:1.3rem; cursor:pointer; color:rgb(var(--muted)); padding:2px 6px;">✕</button>
                </div>
                <div style="padding:1rem; text-align:center; background:rgba(var(--muted),0.04);">
                    <img id="addon-lightbox-img" src="" alt="Preview" style="max-width:100%; max-height:440px; border-radius:10px; object-fit:contain;" />
                </div>
            </div>
        `;
        modal.onclick = closeAddonImageModal;
        document.body.appendChild(modal);
    }
    document.getElementById('addon-lightbox-title').textContent = title || 'Item Preview';
    document.getElementById('addon-lightbox-img').src = src;
    modal.style.display = 'flex';
};

window.closeAddonImageModal = function() {
    const modal = document.getElementById('addon-image-lightbox');
    if (modal) modal.style.display = 'none';
};
</script>

@endsection