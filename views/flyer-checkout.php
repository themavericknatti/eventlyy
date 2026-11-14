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

                        <!-- Dynamic Discount Row (shown when promo applied) -->
                        <div class="charge-row charge-row--discount" id="row-discount" style="display:none;">
                            <span>Discount Applied</span>
                            <strong id="val-discount">-</strong>
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
        const pmBadge = document.getElementById('pm-badge');
        const pmIcon = document.getElementById('pm-icon');
        const pmInput = document.getElementById('input-payment-method');
        const form = document.getElementById('flyer-checkout-form');

        const eventId = '<?= $event->id ?>';
        const subtotal = <?= (float)$subtotal ?>;
        const fee = <?= (float)$fee ?>;
        const extraCharge = <?= (float)$extraCharge ?>;
        const minPercent = <?= (int)$minPercent ?>;
        const currencySymbol = '<?= e($currencySymbol) ?>';

        let currentPaymentType = 'full';
        let formattedTotalFull = '<?= format_price($totalAmount) ?>';
        let formattedLayawayDue = '<?= format_price($layawayDueToday) ?>';
        let formattedDepositTicket = '<?= format_price($depositTicketSubtotal) ?>';
        let formattedLayawayRemaining = '<?= format_price($layawayRemainingBalance) ?>';

        function formatMoney(amount) {
            return currencySymbol + Number(amount || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }

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
                            formattedTotalFull = 'Free';
                            if (valTotal) valTotal.textContent = 'Free';
                            if (payBtnText) payBtnText.textContent = 'Claim Free Tickets';
                            if (payBtnIcon) payBtnIcon.textContent = '⚡';
                            if (pmLabel) pmLabel.textContent = 'Complimentary Registration (No Payment Required)';
                            if (pmBadge) pmBadge.textContent = 'FREE';
                            if (pmIcon) pmIcon.textContent = '🎁';
                            if (pmInput) pmInput.value = 'free';

                            const planSection = document.querySelector('.payment-plan-section');
                            if (planSection) planSection.style.display = 'none';
                        } else {
                            formattedTotalFull = data.formatted_new_total || formattedTotalFull;
                            const discountAmount = parseFloat(data.discount_amount || 0);
                            const netSubtotal = Math.max(0, subtotal - discountAmount);
                            const newDepositTicket = Math.round(netSubtotal * (minPercent / 100) * 100) / 100;
                            const newLayawayDue = Math.round((newDepositTicket + fee + extraCharge) * 100) / 100;
                            const newLayawayRemain = Math.max(0, Math.round((netSubtotal - newDepositTicket) * 100) / 100);

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

                            if (planFullText) planFullText.textContent = formattedTotalFull;
                            if (planDueText) planDueText.textContent = formattedLayawayDue;
                            if (planRemainText) planRemainText.textContent = formattedLayawayRemaining;
                            if (boxDeposit) boxDeposit.textContent = formattedDepositTicket;
                            if (boxDue) boxDue.textContent = formattedLayawayDue;
                            if (boxRemain) boxRemain.textContent = formattedLayawayRemaining;
                            if (valDep) valDep.textContent = formattedDepositTicket;
                            if (valRemain) valRemain.textContent = formattedLayawayRemaining;

                            // Re-sync UI with selected payment type
                            selectFlyerPaymentType(currentPaymentType);
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

        // Prevent double submit
        if (form && payBtn) {
            form.addEventListener('submit', function() {
                payBtn.disabled = true;
                if (payBtnText) payBtnText.textContent = 'Processing Secure Checkout...';
                if (payBtnIcon) payBtnIcon.textContent = '⏳';
            });
        }
    });
    </script>
    <?php echo $this->include('partials/holiday-fx', []); ?>
</body>
</html>
