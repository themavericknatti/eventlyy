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
$subtotal = $subtotal ?? 0.0;
if (empty($subtotal) && !empty($selectedItems) && is_iterable($selectedItems)) {
    foreach ($selectedItems as $item) {
        $subtotal += isset($item['subtotal']) ? floatval($item['subtotal']) : 0.0;
    }
}

$passFee = $passFee ?? false;
$fee = ($passFee && $subtotal > 0) ? ($fee ?? 0.0) : 0.0;
$extraCharge = $subtotal > 0 ? ($extraCharge ?? 100.0) : 0.0;
$totalAmount = $subtotal > 0 ? max(0, $subtotal + $fee + $extraCharge) : 0.0;
$isFreeOrder = ($totalAmount <= 0.00 || $subtotal <= 0.00);

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
        <h1 class="co-title"><?= $isFreeOrder ? 'Complete Your Registration' : 'Confirm Your Order' ?></h1>
        <p class="co-subtitle">
            <?= $isFreeOrder 
                ? 'Finish your free registration for <strong>' . e($eventName) . '</strong> to receive your instant digital ticket QR passes.' 
                : 'Finish your purchase securely for <strong>' . e($eventName) . '</strong>.' ?>
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

                <!-- Attendee Input Fields -->
                <div class="co-field-group">
                    <label class="co-label" for="billing-name">
                        Full Name (Ticket Holder)
                        <span class="co-required">*</span>
                    </label>
                    <input type="text" name="name" id="billing-name" class="co-input" placeholder="e.g. Jane Doe" required
                           value="<?= e($userName) ?>" autocomplete="name" />
                </div>

                <div class="co-field-group">
                    <label class="co-label" for="billing-email">
                        Email Address (Digital QR delivery)
                        <span class="co-required">*</span>
                    </label>
                    <input type="email" name="email" id="billing-email" class="co-input" placeholder="e.g. jane@example.com" required
                           value="<?= e($userEmail) ?>" autocomplete="email" />
                </div>

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

                <!-- Submit CTA -->
                <div class="co-submit-wrap">
                    <button type="submit" id="checkout-submit" class="checkout-btn co-submit-btn">
                        <?= $isFreeOrder ? 'Get Free Tickets →' : 'Pay with Paystack →' ?>
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
                                </span>
                                <strong class="co-ticket-row__price">
                                    <?= $item['subtotal'] > 0 ? format_price($item['subtotal']) : 'Free' ?>
                                </strong>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <div class="summary-divider co-divider"></div>

                <!-- Price Breakdown -->
                <div class="co-breakdown">
                    <div class="summary-row co-breakdown__row">
                        <span>Subtotal</span>
                        <strong id="summary-subtotal"><?= $subtotal > 0 ? format_price($subtotal) : 'Free' ?></strong>
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

                <div class="summary-divider co-divider"></div>

                <!-- Total Amount -->
                <div class="summary-total co-summary-total">
                    <span>Total Amount</span>
                    <strong id="final-total-amount"><?= $totalAmount > 0 ? format_price($totalAmount) : 'Free' ?></strong>
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
            submitBtn.textContent = buttonLabels[method] || 'Complete Purchase →';
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
                    
                    const finalTotalEl = document.getElementById('final-total-amount');
                    if (finalTotalEl && data.formatted_new_total) {
                        finalTotalEl.textContent = data.is_free ? 'Free' : data.formatted_new_total;
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

    // Initialize UI on page load
    updateGatewayUI(selectedPmInput ? selectedPmInput.value : 'paystack');
});
</script>

@endsection