<?php

/**
 * Dedicated Flyer Checkout Page
 *
 * Cinematic modern single-page checkout experience with ambient motion,
 * dynamic halo lighting, and high-contrast glassmorphism.
 *
 * @package Evently
 * @subpackage Views/Flyer
 * @author Celio Natti
 * @version 5.0.0
 */

$eventImg = !empty($event->image) ? $event->image : 'https://images.unsplash.com/photo-1506157786151-b8491531f063?w=1600&q=85&auto=format&fit=crop';
$eventDate = strtotime($event->date ?? '');
$currencySymbol = get_currency_symbol();
$flyerUrl = $flyerUrl ?? event_flyer_url($event);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout — <?= e($event->name) ?></title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    <style>
        :root {
            --flyer-dominant-rgb: 99, 102, 241;
            --flyer-dominant: rgb(var(--flyer-dominant-rgb));
            --flyer-dominant-glow: rgba(var(--flyer-dominant-rgb), 0.45);
            --flyer-accent: #10b981;
            --flyer-text-main: #ffffff;
            --flyer-text-muted: #cbd5e1;
            --overlay-glass: rgba(8, 12, 24, 0.78);
            --overlay-border: rgba(255, 255, 255, 0.18);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html, body {
            min-height: 100vh;
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: #030712;
            color: var(--flyer-text-main);
            -webkit-font-smoothing: antialiased;
            overflow-x: hidden;
        }

        /* ─── 1. Cinematic Background Stage with Ambient Motion ─── */
        .cinematic-bg-container {
            position: fixed;
            inset: 0;
            width: 100vw;
            height: 100vh;
            z-index: 0;
            overflow: hidden;
            pointer-events: none;
        }

        .cinematic-halo {
            position: absolute;
            inset: -40px;
            background-image: url('<?= e($eventImg) ?>');
            background-size: cover;
            background-position: center;
            filter: blur(60px) brightness(0.35) saturate(180%);
            transform: scale(1.18);
        }

        .cinematic-canvas {
            position: absolute;
            inset: 0;
            background-image: url('<?= e($eventImg) ?>');
            background-size: cover;
            background-position: center center;
            background-repeat: no-repeat;
            animation: cinematicBreath 18s ease-in-out infinite alternate;
            will-change: transform;
        }

        @keyframes cinematicBreath {
            0% { transform: scale(1.0) translate(0, 0); }
            50% { transform: scale(1.04) translate(-0.5%, -0.5%); }
            100% { transform: scale(1.02) translate(0.5%, 0.5%); }
        }

        .cinematic-scrim {
            position: absolute;
            inset: 0;
            background: 
                radial-gradient(circle at 50% 35%, rgba(var(--flyer-dominant-rgb), 0.18) 0%, transparent 60%),
                linear-gradient(180deg, 
                    rgba(3, 7, 18, 0.45) 0%, 
                    rgba(3, 7, 18, 0.7) 45%, 
                    rgba(3, 7, 18, 0.95) 100%
                );
        }

        .cinematic-grain {
            position: absolute;
            inset: 0;
            background-image: radial-gradient(rgba(255, 255, 255, 0.06) 1px, transparent 0);
            background-size: 28px 28px;
            opacity: 0.6;
        }

        /* ─── 2. Checkout Overlay Container ─── */
        .checkout-single-stage {
            position: relative;
            z-index: 10;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            max-width: 980px;
            margin: 0 auto;
            padding: 2.25rem 1.5rem;
        }

        .checkout-top-nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.25rem;
        }

        .back-pill-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            color: #f1f5f9;
            font-weight: 800;
            font-size: 0.86rem;
            text-decoration: none;
            padding: 0.5rem 1.1rem;
            border-radius: 14px;
            background: rgba(8, 12, 24, 0.65);
            border: 1.5px solid rgba(255, 255, 255, 0.18);
            transition: all 0.15s ease;
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
        }

        .back-pill-btn:hover {
            background: rgba(255, 255, 255, 0.18);
            transform: translateY(-2px);
        }

        /* ─── Checkout Main Glass Card ─── */
        .checkout-glass-pod {
            background: var(--overlay-glass);
            border: 1.5px solid var(--overlay-border);
            border-radius: 28px;
            backdrop-filter: blur(40px) saturate(200%);
            -webkit-backdrop-filter: blur(40px) saturate(200%);
            box-shadow: 0 24px 70px rgba(0, 0, 0, 0.75), 0 0 1px 1px rgba(255,255,255,0.14) inset;
            overflow: hidden;
            display: grid;
            grid-template-columns: 1fr 1.2fr;
        }

        .summary-column {
            padding: 2rem 2.25rem;
            background: linear-gradient(180deg, rgba(255,255,255,0.03) 0%, rgba(0,0,0,0.3) 100%);
            border-right: 1px solid rgba(255, 255, 255, 0.09);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            gap: 1.5rem;
        }

        .summary-header h1 {
            font-size: 1.45rem;
            font-weight: 900;
            line-height: 1.25;
            color: #fff;
            margin-bottom: 0.35rem;
        }

        .summary-meta-text {
            font-size: 0.84rem;
            color: var(--flyer-text-muted);
            display: flex;
            flex-direction: column;
            gap: 0.2rem;
        }

        .selected-tickets-box {
            display: flex;
            flex-direction: column;
            gap: 0.65rem;
            margin-top: 1rem;
        }

        .ticket-item-pill {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 14px;
            padding: 0.7rem 0.95rem;
            font-size: 0.88rem;
        }

        .breakdown-charges-box {
            display: flex;
            flex-direction: column;
            gap: 0.35rem;
            padding-top: 0.95rem;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            font-size: 0.86rem;
            color: var(--flyer-text-muted);
        }

        .charge-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .charge-row.grand-total {
            font-size: 1.3rem;
            font-weight: 900;
            color: #fff;
            margin-top: 0.45rem;
            padding-top: 0.45rem;
            border-top: 1px dashed rgba(255, 255, 255, 0.18);
        }

        /* ─── Form Column ─── */
        .form-column {
            padding: 2rem 2.25rem;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            gap: 1.25rem;
        }

        .form-header h2 {
            font-size: 1.3rem;
            font-weight: 900;
            display: flex;
            align-items: center;
            gap: 0.45rem;
            margin-bottom: 0.25rem;
        }

        .form-header p {
            font-size: 0.82rem;
            color: var(--flyer-text-muted);
        }

        .input-group {
            display: flex;
            flex-direction: column;
            gap: 0.3rem;
            margin-bottom: 0.85rem;
        }

        .input-group label {
            font-size: 0.76rem;
            font-weight: 800;
            color: var(--flyer-text-muted);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .cyber-input {
            width: 100%;
            padding: 0.8rem 1rem;
            border-radius: 14px;
            background: rgba(0, 0, 0, 0.45);
            border: 1.5px solid rgba(255, 255, 255, 0.14);
            color: #fff;
            font-size: 0.92rem;
            font-family: inherit;
            outline: none;
            transition: all 0.2s ease;
        }

        .cyber-input:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.35);
        }

        .promo-flex-wrap {
            display: flex;
            gap: 0.5rem;
        }

        .promo-apply-btn {
            padding: 0.8rem 1.2rem;
            border-radius: 14px;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.18);
            color: #fff;
            font-weight: 800;
            font-size: 0.86rem;
            cursor: pointer;
            transition: all 0.15s ease;
            white-space: nowrap;
        }

        .promo-apply-btn:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        .pay-action-btn {
            width: 100%;
            padding: 1.1rem 1.5rem;
            border-radius: 16px;
            border: none;
            background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
            color: #fff;
            font-size: 1.08rem;
            font-weight: 900;
            cursor: pointer;
            box-shadow: 0 12px 32px var(--flyer-dominant-glow);
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.55rem;
        }

        .pay-action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 16px 40px rgba(99, 102, 241, 0.75);
        }

        @media (max-width: 860px) {
            .checkout-glass-pod {
                grid-template-columns: 1fr;
            }
            .summary-column {
                border-right: none;
                border-bottom: 1px solid rgba(255, 255, 255, 0.09);
                padding: 1.5rem;
            }
            .form-column {
                padding: 1.5rem;
            }
            .checkout-single-stage {
                padding: 1.5rem 1rem;
            }
        }

        @media (max-width: 540px) {
            .checkout-single-stage {
                padding: 1rem 0.75rem 2.5rem;
            }
            .checkout-glass-pod {
                border-radius: 20px;
            }
            .summary-column,
            .form-column {
                padding: 1.25rem 1.1rem;
            }
            .summary-header h1 {
                font-size: 1.25rem;
            }
            .ticket-item-pill {
                padding: 0.6rem 0.8rem;
                font-size: 0.82rem;
            }
            .pay-action-btn {
                padding: 0.95rem 1.25rem;
                font-size: 1rem;
            }
        }
    </style>
</head>
<body>

    <!-- 1. Cinematic Background Stage with Ambient Motion -->
    <div class="cinematic-bg-container" aria-hidden="true">
        <div class="cinematic-halo"></div>
        <div class="cinematic-canvas"></div>
        <div class="cinematic-scrim"></div>
        <div class="cinematic-grain"></div>
    </div>

    <!-- 2. Centered Checkout Flow -->
    <main class="checkout-single-stage">
        
        <div class="checkout-top-nav">
            <a href="<?= e($flyerUrl) ?>" class="back-pill-btn">
                ← Back to Flyer
            </a>
            <span style="font-size:0.8rem; font-weight:800; color:var(--flyer-accent); background:rgba(16,185,129,0.14); padding:0.4rem 0.95rem; border-radius:12px; border:1px solid rgba(16,185,129,0.35);">
                🔒 Secure SSL Checkout
            </span>
        </div>

        <div class="checkout-glass-pod">
            
            <!-- Left: Order Summary Pane -->
            <section class="summary-column">
                <div>
                    <div class="summary-header">
                        <h1><?= e($event->name) ?></h1>
                        <div class="summary-meta-text">
                            <span>🗓️ <?= $eventDate ? date('l, M d, Y • g:i A', $eventDate) : 'Date TBA' ?></span>
                            <span>📍 <?= e($event->location ?? 'Venue TBA') ?></span>
                        </div>
                    </div>

                    <div style="margin-top:1.25rem;">
                        <h3 style="font-size:0.8rem; font-weight:800; color:var(--flyer-text-muted); text-transform:uppercase; letter-spacing:0.05em; margin-bottom:0.6rem;">
                            Selected Tickets
                        </h3>

                        <div class="selected-tickets-box">
                            <?php foreach ($selectedItems as $item): ?>
                                <div class="ticket-item-pill">
                                    <div>
                                        <strong style="color:#fff;"><?= e($item['name']) ?></strong>
                                        <span style="color:var(--flyer-text-muted); font-size:0.75rem; display:block;">Qty: <?= (int)$item['qty'] ?></span>
                                    </div>
                                    <strong style="color:var(--flyer-accent);">
                                        <?= (float)$item['subtotal'] > 0 ? format_price($item['subtotal']) : 'Free' ?>
                                    </strong>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <div class="breakdown-charges-box">
                    <div class="charge-row">
                        <span>Subtotal</span>
                        <strong><?= $subtotal > 0 ? format_price($subtotal) : '0.00' ?></strong>
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

                    <div class="charge-row grand-total">
                        <span>Total Due</span>
                        <strong style="color:var(--flyer-accent);">
                            <?= $totalAmount > 0 ? format_price($totalAmount) : '0.00' ?>
                        </strong>
                    </div>
                </div>
            </section>

            <!-- Right: Attendee & Payment Action Pane -->
            <section class="form-column">
                <div>
                    <div class="form-header">
                        <h2><span>🎟️</span> Attendee Details</h2>
                        <p>Your tickets and digital QR code will be sent to this email.</p>
                    </div>

                    <form method="POST" action="<?= e(url('f/' . (!empty($event->custom_url) ? $event->custom_url : ($event->slug ?? $event->id)) . '/checkout')) ?>" id="flyer-checkout-form" style="margin-top:1.15rem;">
                        <?= csrf_field() ?>
                        <input type="hidden" name="event_id" value="<?= e($event->id) ?>">
                        <?php if (!empty($refCode)): ?>
                            <input type="hidden" name="ref" value="<?= e($refCode) ?>">
                        <?php endif; ?>

                        <!-- Ticket Quantities Hidden Payload -->
                        <?php foreach ($qtyMap as $ttId => $q): ?>
                            <input type="hidden" name="qty[<?= (int)$ttId ?>]" value="<?= (int)$q ?>">
                        <?php endforeach; ?>

                        <div class="input-group">
                            <label for="att-name">Full Name *</label>
                            <input type="text" id="att-name" name="name" class="cyber-input" required placeholder="Jane Doe" value="<?= e($currentUser->name ?? '') ?>">
                        </div>

                        <div class="input-group">
                            <label for="att-email">Email Address *</label>
                            <input type="email" id="att-email" name="email" class="cyber-input" required placeholder="jane@example.com" value="<?= e($currentUser->email ?? '') ?>">
                        </div>

                        <div class="input-group">
                            <label for="promo-code">Promo Code</label>
                            <div class="promo-flex-wrap">
                                <input type="text" id="promo-code" name="promo_code" class="cyber-input" placeholder="DISCOUNT20" style="text-transform:uppercase;">
                                <button type="button" class="promo-apply-btn" id="btn-apply-code">Apply</button>
                            </div>
                            <span id="promo-status-msg" style="font-size:0.75rem; margin-top:0.25rem; display:block;"></span>
                        </div>

                        <input type="hidden" name="payment_method" value="paystack">

                        <div style="margin-top:1.25rem;">
                            <button type="submit" class="pay-action-btn" id="btn-pay-action">
                                <span>🔒</span> <?= $totalAmount > 0 ? 'Pay ' . format_price($totalAmount) . ' with Paystack' : 'Claim Free Tickets' ?>
                            </button>
                        </div>
                    </form>
                </div>
            </section>

        </div>
    </main>

    <!-- Promo Code AJAX Validation Script -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const promoBtn = document.getElementById('btn-apply-code');
        const promoInput = document.getElementById('promo-code');
        const promoMsg = document.getElementById('promo-status-msg');
        const eventId = '<?= $event->id ?>';
        const subtotal = <?= (float)$subtotal ?>;

        if (promoBtn && promoInput) {
            promoBtn.addEventListener('click', function() {
                const code = promoInput.value.trim();
                if (!code) {
                    promoMsg.textContent = 'Please enter a code.';
                    promoMsg.style.color = '#ef4444';
                    return;
                }

                fetch('/checkout/validate-promo', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ code: code, event_id: eventId, subtotal: subtotal })
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        promoMsg.textContent = '✓ ' + data.message;
                        promoMsg.style.color = '#10b981';
                        promoInput.style.borderColor = '#10b981';
                    } else {
                        promoMsg.textContent = '✕ ' + data.message;
                        promoMsg.style.color = '#ef4444';
                    }
                })
                .catch(() => {
                    promoMsg.textContent = 'Could not validate promo code.';
                    promoMsg.style.color = '#ef4444';
                });
            });
        }
    });
    </script>
</body>
</html>
