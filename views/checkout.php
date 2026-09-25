<?php

/**
 * Checkout Page
 *
 * Confirms buyer billing information, selected ticket tier quantities, pricing breakdown, and processes order checkout.
 *
 * @package Evently
 * @subpackage Views/Checkout
 * @author Your Name <Celio Natti>
 * @license MIT License
 * @version 1.0.0
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
?>

@extends('layouts/main')

@section('title')
Checkout - <?= e($event['name'] ?? 'Complete Your Purchase') ?>
@endsection

@section('content')
<section class="checkout-intro">
    <span class="eyebrow">Checkout</span>
    <h1>Confirm Your Order</h1>
    <p class="lede">Finish your purchase securely for <strong><?= e($event['name'] ?? site_setting('site_title', 'Eventlyy') . ' Event') ?></strong>.</p>
</section>

<!-- Unified Form Wrapper -->
<form id="checkout-form-main" method="POST" action="{{ route('checkout/{event_id}', ['event_id' => $event['id']]) }}">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="process_checkout" />
    <input type="hidden" name="event_id" value="<?= $event['id'] ?>" />
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

    <div class="checkout-grid">
        <section class="checkout-form">
            <div class="section-header">
                <div>
                    <h2><?= $isFreeOrder ? 'Attendee Details' : 'Payment & Attendee Details' ?></h2>
                    <p class="section-note">Enter your details to receive your digital ticket QR codes.</p>
                </div>
            </div>

            <div class="form-card">
                <!-- Alert message block -->
                <div class="checkout-message" id="checkout-alert-msg" style="display:none;" aria-live="polite"></div>

                <label>
                    Full Name (Attendee Name)
                    <input type="text" name="name" id="billing-name" placeholder="e.g. Jane Doe" required
                        value="<?= e($userName) ?>" autocomplete="name" />
                </label>

                <label>
                    Email Address (Tickets will be delivered here)
                    <input type="email" name="email" id="billing-email" placeholder="e.g. jane@example.com" required
                        value="<?= e($userEmail) ?>" autocomplete="email" />
                </label>

                <!-- Free Ticket Notice Badge -->
                <div id="free-registration-badge" style="margin-top:1.25rem; padding:1rem 1.15rem; border-radius:14px; background:rgba(16, 185, 129, 0.08); border:1px solid rgba(16, 185, 129, 0.25); display:<?= $isFreeOrder ? 'flex' : 'none' ?>; align-items:center; gap:0.85rem; color:#10b981;">
                    <span style="font-size:1.6rem; flex-shrink:0;">🎉</span>
                    <div>
                        <strong style="display:block; font-size:0.95rem; color:var(--text); margin-bottom:0.15rem;">Free Ticket Registration</strong>
                        <span style="font-size:0.82rem; color:var(--text-muted); line-height:1.4;">No payment or credit card required. Your digital QR tickets will be issued directly upon confirmation.</span>
                    </div>
                </div>

                <!-- Payment Provider Selection Toggle (hidden for free tickets) -->
                <div id="payment-provider-section" style="<?= $isFreeOrder ? 'display:none;' : '' ?>">
                    <div style="margin-top:1.25rem; font-weight:600; font-size:0.9rem; margin-bottom:0.5rem; color:var(--text);">Select Payment Provider</div>
                    <div class="payment-methods" role="tablist" aria-label="Payment methods">
                        <?php if (in_array('paystack', $enabledGateways)): ?>
                        <button type="button" class="pm-btn" data-method="paystack" aria-pressed="<?= $defaultGateway === 'paystack' ? 'true' : 'false' ?>">
                            <span class="logo" aria-hidden>
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <circle cx="12" cy="12" r="9" fill="currentColor" />
                                </svg>
                            </span>
                            <span>Paystack</span>
                        </button>
                        <?php endif; ?>

                        <?php if (in_array('stripe', $enabledGateways)): ?>
                        <button type="button" class="pm-btn" data-method="stripe" aria-pressed="<?= $defaultGateway === 'stripe' ? 'true' : 'false' ?>">
                            <span class="logo" aria-hidden>
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <rect x="2" y="6" width="20" height="12" rx="2" fill="currentColor" />
                                </svg>
                            </span>
                            <span>Stripe</span>
                        </button>
                        <?php endif; ?>

                        <?php if (in_array('flutterwave', $enabledGateways)): ?>
                        <button type="button" class="pm-btn" data-method="flutterwave" aria-pressed="<?= $defaultGateway === 'flutterwave' ? 'true' : 'false' ?>">
                            <span class="logo" aria-hidden>
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M4 12h16" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                                </svg>
                            </span>
                            <span>Flutterwave</span>
                        </button>
                        <?php endif; ?>
                    </div>

                    <!-- Conditional Card Input Fields (only for Stripe) -->
                    <div class="card-fields" id="card-fields-section" aria-hidden="true" style="margin-top: 1rem; display: none;">
                        <label class="card-number">
                            Card Number
                            <input type="text" name="card_number" id="card-number" placeholder="4242 •••• •••• 4242"
                                autocomplete="cc-number" />
                        </label>
                        <div class="form-row">
                            <label>
                                Expiry Date
                                <input type="text" name="card_expiry" id="card-expiry" placeholder="MM/YY"
                                    autocomplete="cc-exp" />
                            </label>
                            <label>
                                CVC
                                <input type="text" name="card_cvc" id="card-cvc" placeholder="123" autocomplete="cc-csc" />
                            </label>
                        </div>
                    </div>

                    <!-- Gateway Info Note -->
                    <div id="gateway-info" style="margin-top:1rem; padding:0.85rem 1rem; border-radius:12px; background:rgba(35, 182, 107, 0.06); border:1px solid rgba(35, 182, 107, 0.15); font-size:0.85rem; color:rgb(35, 182, 107); display:flex; align-items:center; gap:0.6rem; width:100%; box-sizing:border-box;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" style="flex-shrink:0;"><path d="M12 2L3 7v5c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V7l-9-5z" fill="currentColor"/></svg>
                        <span id="gateway-info-text" style="line-height:1.4;">You'll be securely redirected to Paystack to complete your payment.</span>
                    </div>
                </div>
            </div>

            <div class="checkout-actions">
                <button type="submit" id="checkout-submit" class="checkout-btn" style="border:none; cursor:pointer; width:100%; height:48px; font-size:1rem; border-radius:12px;">
                    <?= $isFreeOrder ? 'Get Free Tickets →' : 'Pay with Paystack →' ?>
                </button>
                <p class="checkout-note" style="text-align:center; margin-top:0.75rem;">Your tickets will be generated and issued instantly upon confirmation.</p>
            </div>
        </section>

        <!-- Right Sidebar Order Summary -->
        <aside class="order-summary">
            <div class="summary-head">
                <h3>Order Summary</h3>
                <p class="meta">Review selected tickets before purchase.</p>
            </div>

            <div class="summary-card">
                <div class="summary-row" style="font-weight: 700; font-size: 1.05rem; color: var(--text); border-bottom:1px solid rgba(var(--muted),0.12); padding-bottom:0.75rem; margin-bottom:0.75rem; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:0.5rem;">
                    <span>🎟️ <?= e($event['name']) ?></span>
                    <span style="font-size:0.82rem; color:var(--text-muted); font-weight:500; white-space:nowrap;"><?= date('M d, Y', strtotime($event['date'])) ?></span>
                </div>

                <?php if (empty($selectedItems)): ?>
                    <p style="font-size:0.85rem; color:var(--text-muted);">No tickets selected.</p>
                <?php else: ?>
                    <?php foreach ($selectedItems as $item): ?>
                        <div class="summary-row" style="margin-bottom:0.5rem; font-size:0.9rem;">
                            <span><?= e($item['name']) ?> × <?= $item['qty'] ?></span>
                            <strong><?= $item['subtotal'] > 0 ? format_price($item['subtotal']) : 'Free' ?></strong>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>

                <div class="summary-divider" style="margin:0.75rem 0;"></div>

                <div class="summary-row" style="font-size:0.88rem; color:var(--text-muted);">
                    <span>Subtotal</span>
                    <span id="summary-subtotal"><?= $subtotal > 0 ? format_price($subtotal) : 'Free' ?></span>
                </div>

                <div class="summary-row" style="color: var(--brand); font-weight: 600; font-size:0.88rem; margin-top:0.25rem;">
                    <span>Ticketing Fee (<?= e($platformFeePercent ?? get_setting('platform_fee', 5)) ?>%)</span>
                    <span id="summary-fee"><?= ($passFee && $subtotal > 0) ? format_price($fee) : '$0.00 (Covered by Host)' ?></span>
                </div>

                <div class="summary-row" style="font-size:0.88rem; color:var(--text-muted); margin-top:0.25rem;">
                    <span>Service Fee / Extra Charge</span>
                    <span id="summary-extra-charge"><?= $subtotal > 0 ? format_price($extraCharge ?? 100.00) : '$0.00' ?></span>
                </div>

                <div class="promo-box" style="margin: 0.85rem 0; padding: 0.85rem; background: rgba(11, 115, 102, 0.05); border: 1px dashed #0B7366; border-radius: 12px; width:100%; box-sizing:border-box;">
                    <label style="font-size:0.82rem; font-weight:700; color:var(--text); display:block; margin-bottom:0.4rem;">🏷️ Have a Promo Code?</label>
                    <div style="display:flex; gap:0.5rem; width:100%; box-sizing:border-box;">
                        <input type="text" name="promo_code" id="promo-code-input" placeholder="e.g. DISCOUNT10" style="flex:1 1 auto; min-width:0; padding:0.6rem 0.75rem; font-size:0.9rem; font-weight:700; border-radius:8px; border:1px solid #0B7366; background:rgb(var(--card)); color:rgb(var(--text)); text-transform:uppercase; letter-spacing:0.5px; box-sizing:border-box;">
                        <button type="button" id="apply-promo-btn" style="padding:0.6rem 1rem; font-size:0.88rem; font-weight:800; border-radius:8px; background-color:#0B7366; color:#ffffff; border:none; cursor:pointer; flex:0 0 auto; box-shadow: 0 2px 6px rgba(11,115,102,0.3);">Apply</button>
                    </div>
                    <div id="promo-message" style="font-size:0.82rem; font-weight:600; margin-top:0.4rem; display:none; word-break:break-word;"></div>
                </div>

                <div class="summary-row" id="discount-row" style="font-size:0.88rem; color:#10b981; font-weight:700; margin-top:0.25rem; display:none;">
                    <span>Discount Applied</span>
                    <span id="discount-amount-text">-</span>
                </div>

                <div class="summary-divider" style="margin:0.75rem 0;"></div>

                <div class="summary-total" style="display:flex; justify-content:space-between; align-items:center; font-size:1.25rem; font-weight:800; color:var(--text);">
                    <span>Total</span>
                    <strong id="final-total-amount" style="color:var(--brand);"><?= $totalAmount > 0 ? format_price($totalAmount) : 'Free' ?></strong>
                </div>
            </div>

            <div class="summary-info" style="margin-top:1.25rem; background:rgba(var(--muted),0.04); border-radius:12px; padding:1rem;">
                <h4 style="margin:0 0 0.25rem; font-size:0.92rem; font-weight:700;">🔒 Instant Digital Issuance</h4>
                <p class="meta" style="margin:0; font-size:0.8rem; color:var(--text-muted);">Your transaction is protected and instant QR passes will be generated.</p>
            </div>
        </aside>
    </div>
</form>

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
        paystack: { bg: 'rgba(35, 182, 107, 0.06)', border: 'rgba(35, 182, 107, 0.15)', text: 'rgb(35, 182, 107)' },
        stripe: { bg: 'rgba(99, 91, 255, 0.06)', border: 'rgba(99, 91, 255, 0.15)', text: 'rgb(99, 91, 255)' },
        flutterwave: { bg: 'rgba(251, 146, 60, 0.06)', border: 'rgba(251, 146, 60, 0.15)', text: 'rgb(251, 146, 60)' }
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

        // Update submit button
        if (submitBtn) {
            submitBtn.textContent = buttonLabels[method] || 'Complete Purchase →';
        }

        // Update gateway info
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

    // ── Full-Page Blur Processing Overlay Helper ──────────
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

    // ── Form Submission Handling with Loading Effect ──────
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
                    event_id: '<?= $event['id'] ?>',
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

    // Initialize on load
    updateGatewayUI(selectedPmInput ? selectedPmInput.value : 'paystack');
});
</script>
@endsection