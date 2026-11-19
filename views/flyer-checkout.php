<?php

/**
 * Dedicated Flyer Checkout Page
 *
 * Cinematic modern single-page checkout experience with ambient motion,
 * dynamic halo lighting, and high-contrast glassmorphism.
 *
 * @package Eventlyy
 * @subpackage Views/Flyer
 * @author Celio Natti
 * @version 6.0.0
 */

$eventImg = !empty($event->image) ? $event->image : 'https://images.unsplash.com/photo-1506157786151-b8491531f063?w=1600&q=85&auto=format&fit=crop';
$eventDate = strtotime($event->date ?? '');
$currencySymbol = get_currency_symbol();
$flyerUrl = $flyerUrl ?? event_flyer_url($event);
$totalPassesCount = 0;
if (!empty($selectedItems) && is_array($selectedItems)) {
    foreach ($selectedItems as $item) {
        $totalPassesCount += (int)($item['qty'] ?? 0);
    }
}
$isFreeOrder = (!empty($selectedItems) && $totalAmount <= 0.00);

// Layaway (Partial Payment / Installment Plan) Configuration
$allowLayaway = !empty($event->allow_partial_payment);
$minPercent = (int)($event->partial_payment_min_percent ?? 25);
if ($minPercent < 10 || $minPercent > 90) {
    $minPercent = 25;
}
$depositTicketSubtotal = round($subtotal * ($minPercent / 100), 2);
$totalCharges = ($passFee && $subtotal > 0 ? $fee : 0.00) + ($subtotal > 0 ? $extraCharge : 0.00);
$layawayDueToday = $depositTicketSubtotal + $totalCharges;
$layawayRemainingBalance = max(0, $subtotal - $depositTicketSubtotal);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, viewport-fit=cover">
    <title>Express Checkout — <?= e($event->name) ?></title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&family=Space+Grotesk:wght@600;700&display=swap" rel="stylesheet">

    <?php $flyerCheckoutVer = asset_version('assets/css/flyer-checkout.css'); ?>
    <link rel="stylesheet" href="{{ route('assets/css/flyer-checkout.css') }}?v=<?= $flyerCheckoutVer ?>">

    <?php
    /* ── Admin-Configurable Theme Color Injection ──
       Override CSS custom properties so checkout follows admin theme colors. */
    $thPrimaryHex   = function_exists('theme_primary_color') ? theme_primary_color() : '#108a55';
    $thSecondaryHex = function_exists('theme_secondary_color') ? theme_secondary_color() : '#1aae6c';
    if (!preg_match('/^#[0-9a-fA-F]{6}$/', $thPrimaryHex))   $thPrimaryHex   = '#108a55';
    if (!preg_match('/^#[0-9a-fA-F]{6}$/', $thSecondaryHex)) $thSecondaryHex = '#1aae6c';

    $thToRgb = function (string $hex): string {
        $hex = ltrim($hex, '#');
        return hexdec(substr($hex, 0, 2)) . ', ' . hexdec(substr($hex, 2, 2)) . ', ' . hexdec(substr($hex, 4, 2));
    };

    $thDarken = function (string $hex, float $factor = 0.12): string {
        $hex = ltrim($hex, '#');
        $r = max(0, (int)round(hexdec(substr($hex, 0, 2)) * (1 - $factor)));
        $g = max(0, (int)round(hexdec(substr($hex, 2, 2)) * (1 - $factor)));
        $b = max(0, (int)round(hexdec(substr($hex, 4, 2)) * (1 - $factor)));
        return sprintf('#%02x%02x%02x', $r, $g, $b);
    };

    $thPrimaryRgb     = $thToRgb($thPrimaryHex);
    $thSecondaryRgb   = $thToRgb($thSecondaryHex);
    $thPrimaryDark    = $thDarken($thPrimaryHex);
    $thPrimaryDarker  = $thDarken($thPrimaryHex, 0.22);
    ?>
    <style id="flyer-checkout-theme-overrides">
    :root {
        --flyer-dominant-rgb: <?= $thPrimaryRgb ?>;
        --flyer-dominant: <?= $thPrimaryHex ?>;
        --flyer-dominant-glow: rgba(<?= $thPrimaryRgb ?>, 0.45);
        --flyer-accent: <?= $thSecondaryHex ?>;
        --flyer-accent-rgb: <?= $thSecondaryRgb ?>;
        --flyer-accent-glow: rgba(<?= $thSecondaryRgb ?>, 0.35);
        --flyer-dominant-dark: <?= $thPrimaryDark ?>;
        --flyer-dominant-darker: <?= $thPrimaryDarker ?>;
        --flyer-gradient-duo: linear-gradient(135deg, <?= $thPrimaryHex ?> 0%, <?= $thSecondaryHex ?> 100%);
        --flyer-gradient-duo-h: linear-gradient(90deg, <?= $thPrimaryHex ?> 0%, <?= $thSecondaryHex ?> 100%);
        --flyer-gradient-duo-hover: linear-gradient(135deg, <?= $thSecondaryHex ?> 0%, <?= $thPrimaryHex ?> 100%);
        --flyer-shadow-duo: 0 10px 25px rgba(<?= $thPrimaryRgb ?>, 0.35), 0 4px 12px rgba(<?= $thSecondaryRgb ?>, 0.25);
        --flyer-shadow-duo-hover: 0 16px 36px rgba(<?= $thPrimaryRgb ?>, 0.45), 0 6px 16px rgba(<?= $thSecondaryRgb ?>, 0.35);
    }
    </style>
</head>
<body style="--event-bg-image: url('<?= e($eventImg) ?>');">

    <!-- 1. Cinematic Background Stage with Ambient Motion -->
    <div class="cinematic-bg-container" aria-hidden="true">
        <div class="cinematic-halo"></div>
        <div class="cinematic-canvas"></div>
        <div class="cinematic-scrim"></div>
        <div class="cinematic-grain"></div>
    </div>

    <!-- 2. Centered Checkout Flow -->
    <main class="checkout-single-stage">
        
        <!-- Top Navigation -->
        <header class="checkout-top-nav">
            <a href="<?= e($flyerUrl) ?>" class="back-pill-btn" title="Return to flyer poster">
                <span>←</span> <span>Back to Flyer</span>
            </a>
            <span class="checkout-security-pill">
                <span>🔒</span> 256-Bit SSL Checkout
            </span>
        </header>

        <!-- Glass Pod Container -->
        <div class="checkout-glass-pod">
            
            <!-- Left: Order Summary Pane -->
            <section class="summary-column" aria-label="Order Summary">
                <div>
                    <div class="summary-header">
                        <h1><?= e($event->name) ?></h1>
                        <div class="summary-meta-text">
                            <span>🗓️ <?= $eventDate ? date('l, M d, Y • g:i A', $eventDate) : 'Date TBA' ?></span>
                            <span>📍 <?= e($event->location ?? 'Venue TBA') ?></span>
                        </div>
                    </div>

                    <div style="margin-top:1.5rem;">
                        <span class="summary-section-label">Selected Passes (<?= $totalPassesCount ?>)</span>

                        <div class="selected-tickets-box">
                            <?php if (empty($selectedItems)): ?>
                                <p style="font-size:0.86rem; color:var(--flyer-text-muted); padding:1rem 0;">No tickets currently selected.</p>
                            <?php else: ?>
                                <?php foreach ($selectedItems as $item): ?>
                                    <div class="ticket-item-pill">
                                        <div>
                                            <strong class="ticket-item-title"><?= e($item['name']) ?></strong>
                                            <div class="ticket-item-meta">
                                                <span>Qty: <strong><?= (int)$item['qty'] ?></strong></span>
                                                <?php if (!empty($item['admits']) && (int)$item['admits'] > 1): ?>
                                                    <span>•</span>
                                                    <span>Admits <?= (int)$item['admits'] ?></span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <strong class="ticket-item-price">
                                            <?= (float)$item['subtotal'] > 0 ? format_price($item['subtotal']) : 'Free' ?>
                                        </strong>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div>
                    <!-- Financial Ledger Breakdown -->
                    <div class="breakdown-charges-box">
                        <div class="charge-row">
                            <span>Subtotal</span>
                            <strong id="val-subtotal"><?= (!empty($selectedItems) && $subtotal <= 0) ? 'Free' : format_price($subtotal) ?></strong>
                        </div>

                        <?php if ($fee > 0): ?>
                            <div class="charge-row">
                                <span>Platform Fee (<?= $platformFeePercent ?>%)</span>
                                <strong><?= format_price($fee) ?></strong>
                            </div>
                        <?php endif; ?>

                        <?php if ($extraCharge > 0): ?>
                            <div class="charge-row">
                                <span>Processing</span>
                                <strong><?= format_price($extraCharge) ?></strong>
                            </div>
                        <?php endif; ?>

                        <!-- Dynamic Add-ons Row -->
                        <div class="charge-row charge-row--addons" id="row-addons" style="display:none;">
                            <span>Add-Ons &amp; Merch</span>
                            <strong id="val-addons" style="color: #14b8a6;">+<?= format_price(0) ?></strong>
                        </div>

                        <!-- Dynamic Discount Row (shown when promo applied) -->
                        <div class="charge-row charge-row--discount" id="row-discount" style="display:none;">
                            <span>Discount Applied</span>
                            <strong id="val-discount">-</strong>
                        </div>

                        <!-- Event Credit Row (shown when credit applied) -->
                        <div class="charge-row charge-row--discount" id="row-credit" style="display:none; color: #f59e0b;">
                            <span>Event Credit Applied</span>
                            <strong id="val-credit">-</strong>
                        </div>

                        <!-- Dynamic Layaway Rows (shown when Layaway is active) -->
                        <div class="charge-row charge-row--layaway-dep" id="row-layaway-deposit" style="display:none;">
                            <span>Deposit Portion (<?= $minPercent ?>%)</span>
                            <strong id="val-layaway-deposit"><?= format_price($depositTicketSubtotal) ?></strong>
                        </div>
                        <div class="charge-row charge-row--layaway-remain" id="row-layaway-remaining" style="display:none;">
                            <span>Remaining Balance Later</span>
                            <strong id="val-layaway-remaining"><?= format_price($layawayRemainingBalance) ?></strong>
                        </div>

                        <div class="charge-row grand-total">
                            <span id="label-total-due">Total Due</span>
                            <strong id="val-total">
                                <?= (!empty($selectedItems) && $totalAmount <= 0) ? 'Free' : format_price($totalAmount) ?>
                            </strong>
                        </div>
                    </div>

                    <!-- Trust Footnote -->
                    <div class="summary-trust-badges">
                        <span><span>🔒</span> SSL Encrypted</span>
                        <span><span>⚡</span> Instant QR Issuance</span>
                        <span><span>✓</span> Verified Host</span>
                    </div>
                </div>
            </section>

            <!-- Right: Attendee & Payment Action Pane -->
            <section class="form-column" aria-label="Attendee Details and Payment">
                <div>
                    <div class="form-header">
                        <h2><span>🎟️</span> Attendee Details</h2>
                        <p>Your tickets and digital QR code will be delivered instantly to this address.</p>
                    </div>

                    <form method="POST" action="<?= e(url('f/' . (!empty($event->custom_url) ? $event->custom_url : ($event->slug ?? $event->id)) . '/checkout')) ?>" id="flyer-checkout-form" style="margin-top:1.25rem;">
                        <?= csrf_field() ?>
                        <input type="hidden" name="event_id" value="<?= e($event->id) ?>">
                        <?php if (!empty($refCode)): ?>
                            <input type="hidden" name="ref" value="<?= e($refCode) ?>">
                        <?php endif; ?>

                        <!-- Ticket Quantities Hidden Payload -->
                        <?php foreach ($qtyMap as $ttId => $q): ?>
                            <input type="hidden" name="qty[<?= (int)$ttId ?>]" value="<?= (int)$q ?>">
                        <?php endforeach; ?>

                        <!-- Full Name Input -->
                        <div class="input-group">
                            <label for="att-name">Full Name *</label>
                            <input 
                                type="text" 
                                id="att-name" 
                                name="name" 
                                class="cyber-input" 
                                required 
                                autocomplete="name"
                                placeholder="e.g. Jane Doe" 
                                value="<?= e($currentUser->name ?? '') ?>">
                        </div>

                        <!-- Email Address Input -->
                        <div class="input-group">
                            <label for="att-email">Email Address *</label>
                            <input 
                                type="email" 
                                id="att-email" 
                                name="email" 
                                class="cyber-input" 
                                required 
                                autocomplete="email"
                                autocorrect="off" 
                                autocapitalize="off" 
                                spellcheck="false"
                                placeholder="e.g. jane@example.com" 
                                value="<?= e($currentUser->email ?? '') ?>">
                        </div>

                        <!-- WhatsApp Phone Number Input -->
                        <div class="input-group">
                            <label for="att-phone">
                                WhatsApp / Mobile Number
                                <span style="font-size:0.75rem; color:#25D366; font-weight:700; margin-left:4px;">● Recommended</span>
                            </label>
                            <input 
                                type="tel" 
                                id="att-phone" 
                                name="phone" 
                                class="cyber-input" 
                                autocomplete="tel"
                                placeholder="e.g. +234 801 234 5678" 
                                value="<?= e($currentUser->phone ?? '') ?>">
                        </div>

                        <!-- WhatsApp Delivery Checkbox -->
                        <div style="margin: 0.5rem 0 1.25rem; padding: 0.75rem 1rem; background: rgba(37, 211, 102, 0.08); border: 1px solid rgba(37, 211, 102, 0.25); border-radius: 12px; display: flex; align-items: flex-start; gap: 10px;">
                            <input type="checkbox" name="whatsapp_delivery" id="chk-flyer-whatsapp" value="1" checked style="margin-top: 3px; accent-color: #25D366; width: 16px; height: 16px; cursor: pointer;" />
                            <label for="chk-flyer-whatsapp" style="font-size: 0.82rem; color: #f1f5f9; cursor: pointer; line-height: 1.45;">
                                <strong style="color: #25D366;">📲 Send ticket QR pass to WhatsApp</strong><br/>
                                <span style="font-size: 0.76rem; color: var(--flyer-text-muted, #94a3b8);">Deliver admission QR pass and event-day gate alerts directly to WhatsApp.</span>
                            </label>
                        </div>

                        <!-- Promo Code Accordion/Input -->
                        <div class="input-group">
                            <label for="promo-code">Promo Code</label>
                            <div class="promo-flex-wrap">
                                <input 
                                    type="text" 
                                    id="promo-code" 
                                    name="promo_code" 
                                    class="cyber-input" 
                                    placeholder="e.g. DISCOUNT20" 
                                    autocomplete="off"
                                    style="text-transform:uppercase;">
                                <button type="button" class="promo-apply-btn" id="btn-apply-code">Apply</button>
                            </div>
                            <span id="promo-status-msg" class="promo-status-msg" aria-live="polite"></span>
                        </div>

                        <!-- Event Credits Box -->
                        <div class="input-group" style="margin-top:0.75rem; background:rgba(245, 158, 11, 0.08); border:1px solid rgba(245, 158, 11, 0.3); border-radius:12px; padding:0.85rem;">
                            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.35rem;">
                                <label style="margin:0; font-size:0.82rem; font-weight:700; color:#fbbf24;">✨ Have Event Credits?</label>
                                <a href="<?= \url('/layaway/credits') ?>" target="_blank" style="font-size:0.72rem; color:#f59e0b; text-decoration:underline;">Check balance ↗</a>
                            </div>
                            <?php if (!empty($totalCreditBalance) && $totalCreditBalance > 0): ?>
                                <p style="margin:0 0 0.5rem; font-size:0.78rem; color:#cbd5e1;">Available credit: <strong style="color:#fbbf24;"><?= format_price($totalCreditBalance) ?></strong></p>
                                <button type="button" class="promo-apply-btn" id="btn-apply-credit" style="width:100%; justify-content:center; background:#d97706; border-color:#d97706; padding:0.6rem; color:#fff;">
                                    Apply <?= format_price(min($totalCreditBalance, $totalAmount)) ?> Credit
                                </button>
                            <?php else: ?>
                                <p style="margin:0 0 0.5rem; font-size:0.75rem; color:#94a3b8;">Redeem credits from uncompleted layaways on this purchase.</p>
                                <button type="button" class="promo-apply-btn" id="btn-apply-credit" style="width:100%; justify-content:center; background:#d97706; border-color:#d97706; padding:0.6rem; color:#fff;">
                                    Apply Available Credits
                                </button>
                            <?php endif; ?>
                            <input type="hidden" name="use_credit" id="flyer-use-credit-input" value="0">
                            <span id="credit-status-msg" class="promo-status-msg" style="display:none;" aria-live="polite"></span>
                        </div>

                        <!-- Ticket Add-Ons & Event Merchandise Shelf -->
                        <?php if (!empty($eventAddons)): ?>
                        <div class="flyer-addons-section" style="margin: 1.25rem 0; padding: 1.15rem; background: rgba(15, 23, 42, 0.7); border: 1px solid rgba(20, 184, 166, 0.35); border-radius: 14px; backdrop-filter: blur(12px);">
                            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.85rem; padding-bottom: 0.65rem; border-bottom: 1px solid rgba(255, 255, 255, 0.08);">
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <span style="font-size: 1.25rem;">🛍️</span>
                                    <div>
                                        <h3 style="margin: 0; font-size: 0.95rem; font-weight: 700; color: #fff;">Ticket Add-Ons &amp; Merch</h3>
                                        <p style="margin: 2px 0 0; font-size: 0.75rem; color: #94a3b8;">Official merchandise and event perks</p>
                                    </div>
                                </div>
                                <span style="font-size: 0.72rem; font-weight: 700; color: #14b8a6; background: rgba(20, 184, 166, 0.15); border: 1px solid rgba(20, 184, 166, 0.3); padding: 2px 8px; border-radius: 99px;">Optional</span>
                            </div>

                            <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                                <?php foreach ($eventAddons as $addon): 
                                    $aId = (int)$addon['id'];
                                    $aPrice = (float)$addon['price'];
                                    $aStock = $addon['quantity'] !== null ? max(0, (int)$addon['quantity'] - (int)($addon['total_sold'] ?? $addon['sold_count'] ?? 0)) : null;
                                    $isSoldOut = ($aStock !== null && $aStock <= 0);
                                ?>
                                    <div class="flyer-addon-item" data-addon-id="<?= $aId ?>" data-price="<?= $aPrice ?>" style="display: flex; align-items: center; justify-content: space-between; gap: 10px; padding: 0.75rem 0.9rem; background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(255, 255, 255, 0.07); border-radius: 10px;">
                                        <!-- Add-on Image Thumbnail -->
                                        <div class="flyer-addon-thumb-wrap" style="width: 48px; height: 48px; border-radius: 10px; overflow: hidden; flex-shrink: 0; background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1); display: flex; align-items: center; justify-content: center;">
                                            <?php if (!empty($addon['image'])): ?>
                                                <img src="<?= e($addon['image']) ?>" alt="<?= e($addon['name']) ?>" style="width: 100%; height: 100%; object-fit: cover; cursor: pointer;" onclick="openFlyerAddonImageModal('<?= e($addon['image']) ?>', '<?= e(addslashes($addon['name'])) ?>')" title="Click to view image" />
                                            <?php else: ?>
                                                <span style="font-size: 1.3rem; opacity: 0.7;">🛍️</span>
                                            <?php endif; ?>
                                        </div>

                                        <div style="flex: 1; min-width: 0;">
                                            <div style="display: flex; align-items: center; gap: 6px; flex-wrap: wrap;">
                                                <strong style="font-size: 0.88rem; color: #f8fafc;"><?= e($addon['name']) ?></strong>
                                                <span style="font-size: 0.8rem; font-weight: 800; color: #14b8a6; background: rgba(20, 184, 166, 0.12); padding: 1px 6px; border-radius: 6px;">
                                                    <?= $aPrice > 0 ? format_price($aPrice) : 'Free' ?>
                                                </span>
                                                <?php if ($isSoldOut): ?>
                                                    <span style="font-size: 0.7rem; font-weight: 700; color: #f87171; background: rgba(239, 68, 68, 0.12); padding: 1px 6px; border-radius: 6px;">Sold Out</span>
                                                <?php elseif ($aStock !== null && $aStock <= 10): ?>
                                                    <span style="font-size: 0.7rem; font-weight: 700; color: #fbbf24; background: rgba(245, 158, 11, 0.12); padding: 1px 6px; border-radius: 6px;">Only <?= $aStock ?> left</span>
                                                <?php endif; ?>
                                            </div>
                                            <?php if (!empty($addon['description'])): ?>
                                                <p style="margin: 3px 0 0; font-size: 0.75rem; color: #94a3b8; line-height: 1.35;"><?= e($addon['description']) ?></p>
                                            <?php endif; ?>
                                        </div>

                                        <!-- Stepper -->
                                        <div style="display: flex; align-items: center; gap: 4px; flex-shrink: 0;">
                                            <?php if ($isSoldOut): ?>
                                                <span style="font-size: 0.78rem; color: #64748b; font-weight: 600;">Sold Out</span>
                                            <?php else: ?>
                                                <button type="button" class="flyer-addon-minus" data-id="<?= $aId ?>" style="width: 30px; height: 30px; border-radius: 7px; border: 1px solid rgba(255, 255, 255, 0.15); background: rgba(255, 255, 255, 0.08); color: #fff; font-size: 1.1rem; font-weight: 700; cursor: pointer; display: flex; align-items: center; justify-content: center; line-height: 1;" aria-label="Decrease quantity">−</button>
                                                <input type="number" 
                                                       name="addons[<?= $aId ?>]" 
                                                       id="flyer-addon-qty-<?= $aId ?>" 
                                                       class="flyer-addon-qty" 
                                                       value="0" 
                                                       min="0" 
                                                       <?= $aStock !== null ? 'max="' . $aStock . '"' : '' ?> 
                                                       data-price="<?= $aPrice ?>" 
                                                       data-name="<?= e($addon['name']) ?>"
                                                       style="width: 42px; height: 30px; text-align: center; font-weight: 700; font-size: 0.88rem; border: 1px solid rgba(255, 255, 255, 0.15); border-radius: 7px; background: rgba(15, 23, 42, 0.9); color: #fff;" />
                                                <button type="button" class="flyer-addon-plus" data-id="<?= $aId ?>" style="width: 30px; height: 30px; border-radius: 7px; border: 1px solid rgba(255, 255, 255, 0.15); background: rgba(255, 255, 255, 0.08); color: #fff; font-size: 1.1rem; font-weight: 700; cursor: pointer; display: flex; align-items: center; justify-content: center; line-height: 1;" aria-label="Increase quantity">+</button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Payment Plan Option (Full vs Layaway Option) -->
                        <?php if ($allowLayaway && !$isFreeOrder && !empty($selectedItems)): ?>
                        <div class="payment-plan-section">
                            <label class="payment-plan-label">Payment Plan Option</label>
                            <input type="hidden" name="payment_type" id="input-payment-type" value="full">
                            <input type="hidden" name="deposit_percent" id="input-deposit-percent" value="<?= (int)$minPercent ?>">

                            <div class="payment-plan-grid">
                                <!-- Full Payment Option -->
                                <button type="button" class="payment-plan-card payment-plan-card--active" id="plan-btn-full" onclick="selectFlyerPaymentType('full')">
                                    <div class="plan-card-header">
                                        <div class="plan-card-title-wrap">
                                            <span class="plan-card-icon">💰</span>
                                            <strong class="plan-card-title">Pay in Full</strong>
                                        </div>
                                        <span class="plan-card-badge plan-card-badge--full" id="plan-badge-full">Active</span>
                                    </div>
                                    <p class="plan-card-desc">
                                        Pay <strong id="plan-full-text"><?= format_price($totalAmount) ?></strong> today &amp; receive your instant digital ticket passes.
                                    </p>
                                </button>

                                <!-- Layaway / Installment Option -->
                                <button type="button" class="payment-plan-card" id="plan-btn-layaway" onclick="selectFlyerPaymentType('layaway')">
                                    <div class="plan-card-header">
                                        <div class="plan-card-title-wrap">
                                            <span class="plan-card-icon">💳</span>
                                            <strong class="plan-card-title">Reserve with Deposit</strong>
                                        </div>
                                        <span class="plan-card-badge plan-card-badge--layaway"><?= $minPercent ?>% Min</span>
                                    </div>
                                    <p class="plan-card-desc">
                                        Pay <strong id="plan-layaway-due-text"><?= format_price($layawayDueToday) ?></strong> today to lock your spot. Pay remaining <span id="plan-layaway-remain-text"><?= format_price($layawayRemainingBalance) ?></span> before the event.
                                    </p>
                                </button>
                            </div>

                            <!-- Layaway Plan Breakdown Card -->
                            <div class="layaway-plan-details" id="layaway-plan-details" style="display:none;">
                                <div class="layaway-details-title">
                                    <span>✨</span> <strong>Layaway Reservation Breakdown:</strong>
                                </div>
                                <div class="layaway-details-grid">
                                    <div class="layaway-details-row">
                                        <span>• Ticket Reservation Deposit (<?= $minPercent ?>%):</span>
                                        <strong id="layaway-box-deposit"><?= format_price($depositTicketSubtotal) ?></strong>
                                    </div>
                                    <?php if ($fee > 0): ?>
                                    <div class="layaway-details-row">
                                        <span>• Ticketing Platform Fee (<?= $platformFeePercent ?>%):</span>
                                        <strong><?= format_price($fee) ?></strong>
                                    </div>
                                    <?php endif; ?>
                                    <?php if ($extraCharge > 0): ?>
                                    <div class="layaway-details-row">
                                        <span>• Service / Processing Charge:</span>
                                        <strong><?= format_price($extraCharge) ?></strong>
                                    </div>
                                    <?php endif; ?>
                                    <div class="layaway-details-row layaway-details-row--total">
                                        <span>💳 Total Due Today (Deposit + Fees):</span>
                                        <strong id="layaway-box-due-today"><?= format_price($layawayDueToday) ?></strong>
                                    </div>
                                    <div class="layaway-details-row layaway-details-row--remain">
                                        <span>⏳ Remaining Balance Due Later:</span>
                                        <strong id="layaway-box-remaining"><?= format_price($layawayRemainingBalance) ?></strong>
                                    </div>
                                </div>
                                <div class="layaway-details-note">
                                    🔒 Your admission pass is locked in instantly. Your digital QR ticket activates once your remaining balance is paid before the event.
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Payment Method Indicator -->
                        <div class="payment-method-indicator">
                            <div class="payment-method-info">
                                <span id="pm-icon"><?= $isFreeOrder ? '🎁' : '💳' ?></span>
                                <span id="pm-label"><?= $isFreeOrder ? 'Complimentary Registration (No Payment Required)' : 'Secured via Paystack Gateway' ?></span>
                            </div>
                            <span class="payment-badge" id="pm-badge"><?= $isFreeOrder ? 'FREE' : 'SECURE' ?></span>
                        </div>

                        <input type="hidden" name="payment_method" id="input-payment-method" value="<?= $isFreeOrder ? 'free' : 'paystack' ?>">

                        <!-- Submit CTA -->
                        <div style="margin-top:1.25rem;">
                            <button type="submit" class="pay-action-btn" id="btn-pay-action" <?= empty($selectedItems) ? 'disabled' : '' ?>>
                                <span id="pay-btn-icon"><?= $isFreeOrder ? '⚡' : '🔒' ?></span>
                                <span id="pay-btn-text">
                                    <?php if (empty($selectedItems)): ?>
                                        Select Tickets to Continue
                                    <?php elseif ($isFreeOrder): ?>
                                        Claim Free Tickets
                                    <?php else: ?>
                                        Pay <?= format_price($totalAmount) ?> with Paystack
                                    <?php endif; ?>
                                </span>
                            </button>
                            <p class="form-trust-note">
                                🔒 By completing this order, you agree to the Terms of Service. Your admission passes will be generated instantly.
                            </p>
                        </div>
                    </form>
                </div>
            </section>

        </div>
    </main>

    <!-- Layaway & Promo Code Interactive Logic -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const promoBtn = document.getElementById('btn-apply-code');
        const promoInput = document.getElementById('promo-code');
        const promoMsg = document.getElementById('promo-status-msg');
        const rowDiscount = document.getElementById('row-discount');
        const valDiscount = document.getElementById('val-discount');
        const valTotal = document.getElementById('val-total');
        const labelTotal = document.getElementById('label-total-due');
        const payBtn = document.getElementById('btn-pay-action');
        const payBtnText = document.getElementById('pay-btn-text');
        const payBtnIcon = document.getElementById('pay-btn-icon');
        const pmLabel = document.getElementById('pm-label');
        const rowAddons = document.getElementById('row-addons');
        const valAddons = document.getElementById('val-addons');
        const valSubtotal = document.getElementById('val-subtotal');
        const pmBadge = document.getElementById('pm-badge');
        const pmIcon = document.getElementById('pm-icon');
        const pmInput = document.getElementById('input-payment-method');
        const form = document.getElementById('flyer-checkout-form');

        const eventId = '<?= $event->id ?>';
        const baseTicketSubtotal = <?= (float)$subtotal ?>;
        const passFee = <?= $passFee ? 'true' : 'false' ?>;
        const platformFeePercent = <?= (float)$platformFeePercent ?>;
        const baseExtraCharge = <?= (float)$extraCharge ?>;
        const minPercent = <?= (int)$minPercent ?>;
        const currencySymbol = '<?= e($currencySymbol) ?>';

        let appliedDiscount = 0.0;
        let currentPaymentType = 'full';
        let formattedTotalFull = '<?= format_price($totalAmount) ?>';
        let formattedLayawayDue = '<?= format_price($layawayDueToday) ?>';
        let formattedDepositTicket = '<?= format_price($depositTicketSubtotal) ?>';
        let formattedLayawayRemaining = '<?= format_price($layawayRemainingBalance) ?>';

        function formatMoney(amount) {
            return currencySymbol + Number(amount || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }

        function recalcFlyerAll() {
            let addonsSubtotal = 0.0;
            const addonInputs = document.querySelectorAll('.flyer-addon-qty');
            addonInputs.forEach(input => {
                const qty = parseInt(input.value || 0, 10);
                const price = parseFloat(input.dataset.price || 0);
                if (qty > 0 && price > 0) {
                    addonsSubtotal += (qty * price);
                }
            });

            if (rowAddons && valAddons) {
                if (addonsSubtotal > 0) {
                    rowAddons.style.display = 'flex';
                    valAddons.textContent = '+' + formatMoney(addonsSubtotal);
                } else {
                    rowAddons.style.display = 'none';
                }
            }

            const combinedSubtotal = baseTicketSubtotal + addonsSubtotal;
            if (valSubtotal) {
                valSubtotal.textContent = combinedSubtotal > 0 ? formatMoney(combinedSubtotal) : 'Free';
            }

            const calcFee = (passFee && combinedSubtotal > 0) ? Math.round(combinedSubtotal * (platformFeePercent / 100) * 100) / 100 : 0.00;
            const netSubtotal = Math.max(0, combinedSubtotal - appliedDiscount);
            const calcExtraCharge = netSubtotal > 0 ? baseExtraCharge : 0.00;
            const finalTotal = netSubtotal > 0 ? Math.max(0, Math.round((netSubtotal + calcFee + calcExtraCharge) * 100) / 100) : 0.00;

            const isFree = (finalTotal <= 0 && combinedSubtotal <= 0);

            // Layaway recalculated values
            const newDepositTicket = Math.round(netSubtotal * (minPercent / 100) * 100) / 100;
            const newLayawayDue = Math.round((newDepositTicket + calcFee + calcExtraCharge) * 100) / 100;
            const newLayawayRemain = Math.max(0, Math.round((netSubtotal - newDepositTicket) * 100) / 100);

            formattedTotalFull = finalTotal > 0 ? formatMoney(finalTotal) : 'Free';
            formattedLayawayDue = formatMoney(newLayawayDue);
            formattedDepositTicket = formatMoney(newDepositTicket);
            formattedLayawayRemaining = formatMoney(newLayawayRemain);

            const planFullText = document.getElementById('plan-full-text');
            const planDueText = document.getElementById('plan-layaway-due-text');
            const planRemainText = document.getElementById('plan-layaway-remain-text');
            const boxDeposit = document.getElementById('layaway-box-deposit');
            const boxDue = document.getElementById('layaway-box-due-today');
            const boxRemain = document.getElementById('layaway-box-remaining');
            const valDep = document.getElementById('val-layaway-deposit');
            const valRemain = document.getElementById('val-layaway-remaining');
            const planSection = document.querySelector('.payment-plan-section');

            if (planFullText) planFullText.textContent = formattedTotalFull;
            if (planDueText) planDueText.textContent = formattedLayawayDue;
            if (planRemainText) planRemainText.textContent = formattedLayawayRemaining;
            if (boxDeposit) boxDeposit.textContent = formattedDepositTicket;
            if (boxDue) boxDue.textContent = formattedLayawayDue;
            if (boxRemain) boxRemain.textContent = formattedLayawayRemaining;
            if (valDep) valDep.textContent = formattedDepositTicket;
            if (valRemain) valRemain.textContent = formattedLayawayRemaining;

            if (isFree) {
                if (planSection) planSection.style.display = 'none';
                if (valTotal) valTotal.textContent = 'Free';
                if (payBtnText) payBtnText.textContent = 'Claim Free Tickets';
                if (payBtnIcon) payBtnIcon.textContent = '⚡';
                if (pmLabel) pmLabel.textContent = 'Complimentary Registration (No Payment Required)';
                if (pmBadge) pmBadge.textContent = 'FREE';
                if (pmIcon) pmIcon.textContent = '🎁';
                if (pmInput) pmInput.value = 'free';
            } else {
                if (planSection) planSection.style.display = 'block';
                if (pmBadge) pmBadge.textContent = 'SECURE';
                if (pmIcon) pmIcon.textContent = '💳';
                if (pmInput) pmInput.value = 'paystack';
                if (payBtnIcon) payBtnIcon.textContent = '🔒';
                selectFlyerPaymentType(currentPaymentType);
            }
        }

        // Stepper buttons for Add-ons
        document.querySelectorAll('.flyer-addon-minus').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const id = this.dataset.id;
                const input = document.getElementById('flyer-addon-qty-' + id);
                if (input) {
                    let val = parseInt(input.value || 0, 10);
                    if (val > 0) {
                        input.value = val - 1;
                        recalcFlyerAll();
                    }
                }
            });
        });

        document.querySelectorAll('.flyer-addon-plus').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const id = this.dataset.id;
                const input = document.getElementById('flyer-addon-qty-' + id);
                if (input) {
                    let val = parseInt(input.value || 0, 10);
                    const maxAttr = input.getAttribute('max');
                    const max = maxAttr ? parseInt(maxAttr, 10) : 9999;
                    if (val < max) {
                        input.value = val + 1;
                        recalcFlyerAll();
                    }
                }
            });
        });

        document.querySelectorAll('.flyer-addon-qty').forEach(input => {
            input.addEventListener('input', function() {
                let val = parseInt(this.value || 0, 10);
                if (isNaN(val) || val < 0) val = 0;
                const maxAttr = this.getAttribute('max');
                if (maxAttr && val > parseInt(maxAttr, 10)) {
                    val = parseInt(maxAttr, 10);
                }
                this.value = val;
                recalcFlyerAll();
            });
        });

        // ── Layaway / Full Payment Selection ──
        window.selectFlyerPaymentType = function(type) {
            currentPaymentType = type;
            const input = document.getElementById('input-payment-type');
            const details = document.getElementById('layaway-plan-details');
            const btnFull = document.getElementById('plan-btn-full');
            const btnLayaway = document.getElementById('plan-btn-layaway');
            const badgeFull = document.getElementById('plan-badge-full');
            const rowDeposit = document.getElementById('row-layaway-deposit');
            const rowRemaining = document.getElementById('row-layaway-remaining');

            if (input) input.value = type;

            if (type === 'layaway') {
                if (details) details.style.display = 'block';
                if (btnLayaway) btnLayaway.classList.add('payment-plan-card--active');
                if (btnFull) btnFull.classList.remove('payment-plan-card--active');
                if (badgeFull) badgeFull.style.display = 'none';
                if (rowDeposit) rowDeposit.style.display = 'flex';
                if (rowRemaining) rowRemaining.style.display = 'flex';
                if (labelTotal) labelTotal.textContent = 'Due Today (Deposit + Fees)';
                if (valTotal) valTotal.textContent = formattedLayawayDue;
                if (payBtnText) payBtnText.textContent = `Pay Deposit (${formattedLayawayDue}) with Paystack`;
                if (pmLabel) pmLabel.textContent = 'Secured Deposit via Paystack Gateway';
            } else {
                if (details) details.style.display = 'none';
                if (btnFull) btnFull.classList.add('payment-plan-card--active');
                if (btnLayaway) btnLayaway.classList.remove('payment-plan-card--active');
                if (badgeFull) badgeFull.style.display = 'inline-block';
                if (rowDeposit) rowDeposit.style.display = 'none';
                if (rowRemaining) rowRemaining.style.display = 'none';
                if (labelTotal) labelTotal.textContent = 'Total Due';
                if (valTotal) valTotal.textContent = formattedTotalFull;
                if (payBtnText) payBtnText.textContent = `Pay ${formattedTotalFull} with Paystack`;
                if (pmLabel) pmLabel.textContent = 'Secured via Paystack Gateway';
            }
        };

        if (promoBtn && promoInput) {
            promoBtn.addEventListener('click', function() {
                const code = promoInput.value.trim();
                if (!code) {
                    promoMsg.textContent = 'Please enter a coupon code.';
                    promoMsg.style.color = '#ef4444';
                    return;
                }

                promoBtn.disabled = true;
                promoBtn.textContent = 'Verifying...';

                fetch('/checkout/validate-promo', {
                    method: 'POST',
                    headers: { 
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ code: code, event_id: eventId, subtotal: subtotal })
                })
                .then(r => r.json())
                .then(data => {
                    promoBtn.disabled = false;
                    promoBtn.textContent = 'Apply';

                    if (data.success) {
                        promoMsg.textContent = '✓ ' + (data.message || 'Coupon code applied successfully!');
                        promoMsg.style.color = '#10b981';
                        promoInput.style.borderColor = '#10b981';

                        // Reveal discount in summary ledger
                        if (rowDiscount && valDiscount) {
                            rowDiscount.style.display = 'flex';
                            valDiscount.textContent = '-' + data.formatted_discount;
                        }

                        if (data.is_free) {
                            appliedDiscount = baseTicketSubtotal + 999999;
                            recalcFlyerAll();
                        } else {
                            appliedDiscount = parseFloat(data.discount_amount || 0);
                            recalcFlyerAll();
                        }
                    } else {
                        promoMsg.textContent = '✕ ' + (data.message || 'Invalid promo code.');
                        promoMsg.style.color = '#ef4444';
                        promoInput.style.borderColor = '#ef4444';
                    }
                })
                .catch(() => {
                    promoBtn.disabled = false;
                    promoBtn.textContent = 'Apply';
                    promoMsg.textContent = 'Network error while validating promo code.';
                    promoMsg.style.color = '#ef4444';
                });
            });
        }

        // ── Event Credit Validation & Application (Flyer Checkout) ──
        const creditBtn = document.getElementById('btn-apply-credit');
        const creditMsg = document.getElementById('credit-status-msg');
        const rowCredit = document.getElementById('row-credit');
        const valCredit = document.getElementById('val-credit');
        const useCreditInput = document.getElementById('flyer-use-credit-input');

        if (creditBtn) {
            creditBtn.addEventListener('click', function() {
                const emailInput = document.querySelector('input[name="email"]') || document.getElementById('buyer-email');
                const email = emailInput ? emailInput.value.trim() : '<?= !empty($currentUser->email) ? e($currentUser->email) : '' ?>';

                if (!email) {
                    if (creditMsg) {
                        creditMsg.style.display = 'block';
                        creditMsg.style.color = '#ef4444';
                        creditMsg.textContent = 'Please enter your email in the attendee info above first.';
                    }
                    if (emailInput) emailInput.focus();
                    return;
                }

                creditBtn.disabled = true;
                creditBtn.textContent = 'Checking credits...';

                fetch('/checkout/validate-credit', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({
                        email: email,
                        current_total: '<?= $totalAmount ?>',
                        csrf_token: '<?= csrf_token() ?>'
                    })
                })
                .then(r => r.json())
                .then(data => {
                    if (creditMsg) creditMsg.style.display = 'block';
                    if (data.success) {
                        if (creditMsg) {
                            creditMsg.style.color = '#10b981';
                            creditMsg.textContent = '✓ ' + data.message;
                        }
                        if (rowCredit && valCredit) {
                            rowCredit.style.display = 'flex';
                            valCredit.textContent = '-' + data.formatted_discount;
                        }
                        if (useCreditInput) useCreditInput.value = '1';

                        if (data.is_free) {
                            appliedDiscount = baseTicketSubtotal + 999999;
                            recalcFlyerAll();
                        } else {
                            appliedDiscount = parseFloat(data.applied_credit || 0);
                            recalcFlyerAll();
                        }
                        creditBtn.textContent = '✓ Credit Applied (' + data.formatted_discount + ')';
                    } else {
                        if (creditMsg) {
                            creditMsg.style.color = '#ef4444';
                            creditMsg.textContent = '✕ ' + (data.message || 'No credits found.');
                        }
                        if (rowCredit) rowCredit.style.display = 'none';
                        if (useCreditInput) useCreditInput.value = '0';
                        creditBtn.disabled = false;
                        creditBtn.textContent = 'Apply Available Credits';
                    }
                })
                .catch(() => {
                    creditBtn.disabled = false;
                    creditBtn.textContent = 'Apply Available Credits';
                    if (creditMsg) {
                        creditMsg.style.display = 'block';
                        creditMsg.style.color = '#ef4444';
                        creditMsg.textContent = 'Failed to check event credits. Please try again.';
                    }
                });
            });
        }

        // Prevent double submit
        if (form && payBtn) {
            form.addEventListener('submit', function() {
                payBtn.disabled = true;
                if (payBtnText) payBtnText.textContent = 'Processing Secure Checkout...';
                if (payBtnIcon) payBtnIcon.textContent = '⏳';
            });
        }
    });

    window.openFlyerAddonImageModal = function(src, title) {
        let modal = document.getElementById('flyer-addon-lightbox');
        if (!modal) {
            modal = document.createElement('div');
            modal.id = 'flyer-addon-lightbox';
            modal.style.cssText = 'position:fixed; inset:0; z-index:99999; background:rgba(0,0,0,0.85); backdrop-filter:blur(8px); display:flex; align-items:center; justify-content:center; padding:1.5rem; cursor:pointer;';
            modal.innerHTML = `
                <div style="position:relative; max-width:500px; width:100%; background:rgba(15,23,42,0.95); border:1px solid rgba(255,255,255,0.15); border-radius:18px; overflow:hidden; box-shadow:0 25px 50px -12px rgba(0,0,0,0.7); cursor:default;" onclick="event.stopPropagation()">
                    <div style="display:flex; align-items:center; justify-content:space-between; padding:0.85rem 1.25rem; border-bottom:1px solid rgba(255,255,255,0.08);">
                        <strong id="flyer-lightbox-title" style="font-size:0.95rem; color:#f8fafc;"></strong>
                        <button type="button" onclick="closeFlyerAddonImageModal()" style="background:none; border:none; font-size:1.3rem; cursor:pointer; color:#94a3b8; padding:2px 6px;">✕</button>
                    </div>
                    <div style="padding:1rem; text-align:center; background:rgba(0,0,0,0.2);">
                        <img id="flyer-lightbox-img" src="" alt="Preview" style="max-width:100%; max-height:420px; border-radius:10px; object-fit:contain;" />
                    </div>
                </div>
            `;
            modal.onclick = closeFlyerAddonImageModal;
            document.body.appendChild(modal);
        }
        document.getElementById('flyer-lightbox-title').textContent = title || 'Merchandise Preview';
        document.getElementById('flyer-lightbox-img').src = src;
        modal.style.display = 'flex';
    };

    window.closeFlyerAddonImageModal = function() {
        const modal = document.getElementById('flyer-addon-lightbox');
        if (modal) modal.style.display = 'none';
    };
    </script>
    <?php echo $this->include('partials/holiday-fx', []); ?>
</body>
</html>
