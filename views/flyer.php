<?php

/**
 * Dedicated Event Flyer & Ticket Sales Page
 *
 * Cinematic modern single-page experience with dynamic ambient lighting,
 * subtle breathing zoom, ethereal floating particles, and high-contrast glassmorphism.
 *
 * @package Evently
 * @subpackage Views/Flyer
 * @author Celio Natti
 * @version 6.0.0
 */

$eventImg = !empty($event->image) ? $event->image : 'https://images.unsplash.com/photo-1506157786151-b8491531f063?w=1600&q=85&auto=format&fit=crop';
$eventDate = strtotime($event->date ?? '');
$now = time();
$liveWindowEnd = $eventDate ? $eventDate + (event_completion_hours() * 3600) : 0;
$isLiveNow = !$isCompleted && ($eventDate && $now >= $eventDate && $now <= $liveWindowEnd);
$isUpcoming = !$isCompleted && !$isLiveNow;

$rawDesc = $event->description ?? '';
$shortDesc = (mb_strlen($rawDesc) > 180) ? mb_strimwidth($rawDesc, 0, 180, '...') : $rawDesc;

$currencySymbol = get_currency_symbol();
$flyerUrl = event_flyer_url($event);
$eventSlug = !empty($event->custom_url) ? $event->custom_url : ($event->slug ?? $event->id);
$checkoutUrl = url('f/' . $eventSlug . '/checkout');

// Social Share links
$shareText = urlencode("🎟️ Grab tickets for " . ($event->name ?? 'this event') . "!");
$encodedFlyerUrl = urlencode($flyerUrl);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?= e($event->name) ?> — Tickets &amp; Flyer</title>
    
    <!-- OpenGraph & Social Cards -->
    <meta property="og:type" content="website">
    <meta property="og:title" content="<?= e($event->name) ?>">
    <meta property="og:description" content="<?= e($shortDesc) ?>">
    <meta property="og:image" content="<?= e($eventImg) ?>">
    <meta property="og:url" content="<?= e($flyerUrl) ?>">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= e($event->name) ?>">
    <meta name="twitter:description" content="<?= e($shortDesc) ?>">
    <meta name="twitter:image" content="<?= e($eventImg) ?>">

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
            --overlay-glass: rgba(8, 12, 24, 0.76);
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

        /* Deep Ambient Halo Layer */
        .cinematic-halo {
            position: absolute;
            inset: -40px;
            background-image: url('<?= e($eventImg) ?>');
            background-size: cover;
            background-position: center;
            filter: blur(60px) brightness(0.35) saturate(180%);
            transform: scale(1.18);
        }

        /* Sharp Hero Canvas with Gentle Cinematic Breathing Zoom */
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
            0% {
                transform: scale(1.0) translate(0, 0);
            }
            50% {
                transform: scale(1.04) translate(-0.5%, -0.5%);
            }
            100% {
                transform: scale(1.02) translate(0.5%, 0.5%);
            }
        }

        /* Dynamic Spotlight & Gradient Lighting Scrim */
        .cinematic-scrim {
            position: absolute;
            inset: 0;
            background: 
                radial-gradient(circle at 50% 35%, rgba(var(--flyer-dominant-rgb), 0.18) 0%, transparent 60%),
                linear-gradient(180deg, 
                    rgba(3, 7, 18, 0.45) 0%, 
                    rgba(3, 7, 18, 0.65) 45%, 
                    rgba(3, 7, 18, 0.94) 100%
                );
        }

        /* Modern Subtle Grid / Grain Texture Overlay */
        .cinematic-grain {
            position: absolute;
            inset: 0;
            background-image: radial-gradient(rgba(255, 255, 255, 0.06) 1px, transparent 0);
            background-size: 28px 28px;
            opacity: 0.6;
        }

        /* ─── 2. Single-Page Modern Overlay Stage ─── */
        .flyer-single-stage {
            position: relative;
            z-index: 10;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            max-width: 1140px;
            margin: 0 auto;
            padding: 2.75rem 1.75rem 2.25rem;
        }

        /* ─── Top Hero Overlay ─── */
        .hero-showcase {
            max-width: 860px;
            margin-bottom: 2rem;
            animation: fadeInHero 0.5s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes fadeInHero {
            from { opacity: 0; transform: translateY(-18px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .badges-cluster {
            display: flex;
            align-items: center;
            gap: 0.65rem;
            flex-wrap: wrap;
            margin-bottom: 1rem;
        }

        .cyber-badge {
            padding: 0.45rem 1rem;
            border-radius: 30px;
            font-size: 0.78rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.45);
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .cyber-badge--live {
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.95), rgba(220, 38, 38, 0.95));
            color: #fff;
            box-shadow: 0 0 24px rgba(239, 68, 68, 0.75);
        }

        .live-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: #fff;
            box-shadow: 0 0 8px #fff;
            animation: livePulse 1.5s infinite;
        }

        @keyframes livePulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.4; transform: scale(0.8); }
        }

        .cyber-badge--upcoming {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.92), rgba(5, 150, 105, 0.92));
            color: #fff;
            box-shadow: 0 0 22px rgba(16, 185, 129, 0.6);
        }

        .cyber-badge--completed {
            background: rgba(100, 116, 139, 0.9);
            color: #fff;
        }

        .cyber-badge--category {
            background: rgba(0, 0, 0, 0.65);
            color: #f8fafc;
            border: 1px solid rgba(255, 255, 255, 0.22);
        }

        .hero-event-name {
            font-size: 3.1rem;
            font-weight: 900;
            line-height: 1.1;
            letter-spacing: -0.04em;
            color: #ffffff;
            text-shadow: 0 4px 30px rgba(0, 0, 0, 0.9), 0 0 40px rgba(var(--flyer-dominant-rgb), 0.35);
            margin-bottom: 1.25rem;
        }

        .meta-chips-cluster {
            display: flex;
            align-items: center;
            gap: 0.8rem;
            flex-wrap: wrap;
        }

        .meta-chip {
            display: inline-flex;
            align-items: center;
            gap: 0.55rem;
            background: rgba(8, 12, 24, 0.65);
            border: 1.5px solid rgba(255, 255, 255, 0.18);
            border-radius: 16px;
            padding: 0.6rem 1.15rem;
            font-size: 0.92rem;
            font-weight: 700;
            color: #f8fafc;
            backdrop-filter: blur(24px) saturate(180%);
            -webkit-backdrop-filter: blur(24px) saturate(180%);
            box-shadow: 0 8px 24px rgba(0,0,0,0.4);
            transition: transform 0.2s ease, border-color 0.2s ease;
        }

        .meta-chip:hover {
            transform: translateY(-2px);
            border-color: rgba(255, 255, 255, 0.35);
        }

        /* ─── Bottom Glassmorphic Overlay Grid ─── */
        .bottom-overlay-grid {
            width: 100%;
            display: grid;
            grid-template-columns: 1.35fr 1fr;
            gap: 1.75rem;
            align-items: flex-end;
            margin-top: auto;
        }

        .glass-station-pod {
            background: var(--overlay-glass);
            border: 1.5px solid var(--overlay-border);
            border-radius: 28px;
            padding: 1.75rem 2rem;
            backdrop-filter: blur(40px) saturate(200%);
            -webkit-backdrop-filter: blur(40px) saturate(200%);
            box-shadow: 0 24px 70px rgba(0, 0, 0, 0.7), 0 0 1px 1px rgba(255,255,255,0.14) inset;
        }

        .station-top-strip {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-bottom: 0.85rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            margin-bottom: 1.1rem;
        }

        .station-top-strip h2 {
            font-size: 1.3rem;
            font-weight: 900;
            letter-spacing: -0.02em;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .station-delivery-badge {
            font-size: 0.75rem;
            font-weight: 800;
            color: var(--flyer-accent);
            background: rgba(16, 185, 129, 0.14);
            border: 1px solid rgba(16, 185, 129, 0.35);
            padding: 0.25rem 0.7rem;
            border-radius: 12px;
        }

        /* ─── Ticket Tiers Rows with Glowing Hover ─── */
        .tier-items-list {
            display: flex;
            flex-direction: column;
            gap: 0.85rem;
            max-height: 280px;
            overflow-y: auto;
            padding-right: 4px;
        }

        .tier-row-item {
            background: rgba(255, 255, 255, 0.04);
            border: 1.5px solid rgba(255, 255, 255, 0.1);
            border-radius: 18px;
            padding: 0.95rem 1.15rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all 0.25s ease;
        }

        .tier-row-item:hover {
            background: rgba(255, 255, 255, 0.09);
            border-color: rgba(var(--flyer-dominant-rgb), 0.55);
            box-shadow: 0 6px 20px var(--flyer-dominant-glow);
            transform: translateY(-2px);
        }

        .tier-label-name {
            font-size: 1rem;
            font-weight: 800;
            color: #fff;
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }

        .tier-price-highlight {
            font-size: 1.15rem;
            font-weight: 900;
            color: var(--flyer-accent);
            margin: 0.15rem 0;
        }

        .stepper-control-wrap {
            display: flex;
            align-items: center;
            gap: 0.45rem;
            background: rgba(0, 0, 0, 0.55);
            border: 1px solid rgba(255, 255, 255, 0.16);
            border-radius: 14px;
            padding: 0.25rem 0.4rem;
        }

        .stepper-btn {
            width: 32px;
            height: 32px;
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.1);
            border: none;
            color: #fff;
            font-size: 1.2rem;
            font-weight: 800;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.15s ease;
        }

        .stepper-btn:hover:not(:disabled) {
            background: var(--flyer-dominant);
            transform: scale(1.08);
        }

        .stepper-btn:disabled {
            opacity: 0.3;
            cursor: not-allowed;
        }

        .stepper-display-val {
            font-size: 1rem;
            font-weight: 800;
            width: 24px;
            text-align: center;
            color: #fff;
        }

        /* ─── Right Column: Order Summary & Checkout CTA ─── */
        .glass-action-pod {
            background: var(--overlay-glass);
            border: 1.5px solid var(--overlay-border);
            border-radius: 28px;
            padding: 1.75rem 2rem;
            backdrop-filter: blur(40px) saturate(200%);
            -webkit-backdrop-filter: blur(40px) saturate(200%);
            box-shadow: 0 24px 70px rgba(0, 0, 0, 0.7), 0 0 1px 1px rgba(255,255,255,0.14) inset;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            gap: 1.35rem;
        }

        .summary-calculation-box {
            background: rgba(0, 0, 0, 0.48);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 18px;
            padding: 1rem 1.2rem;
        }

        .summary-line-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.86rem;
            margin-bottom: 0.35rem;
            color: var(--flyer-text-muted);
        }

        .summary-line-item.grand-total {
            font-size: 1.25rem;
            font-weight: 900;
            color: #fff;
            margin-top: 0.5rem;
            padding-top: 0.5rem;
            border-top: 1px dashed rgba(255, 255, 255, 0.18);
        }

        .checkout-main-cta {
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

        .checkout-main-cta:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 16px 40px rgba(99, 102, 241, 0.75);
        }

        .checkout-main-cta:disabled {
            opacity: 0.45;
            cursor: not-allowed;
            box-shadow: none;
            transform: none;
        }

        /* ─── Social & Copy Link Strip (Overlaid on Image) ─── */
        .social-share-strip {
            display: flex;
            align-items: center;
            gap: 0.55rem;
            flex-wrap: wrap;
            padding-top: 0.6rem;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        .social-pill-btn {
            padding: 0.5rem 0.95rem;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 800;
            text-decoration: none;
            color: #f1f5f9;
            background: rgba(0, 0, 0, 0.55);
            border: 1px solid rgba(255, 255, 255, 0.18);
            cursor: pointer;
            transition: all 0.15s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
        }

        .social-pill-btn:hover {
            color: #fff;
            background: rgba(255, 255, 255, 0.2);
            border-color: rgba(255, 255, 255, 0.35);
            transform: translateY(-1px);
        }

        .social-pill-btn.copy-link-highlight {
            background: rgba(99, 102, 241, 0.28);
            border-color: rgba(99, 102, 241, 0.55);
            color: #fff;
        }

        /* ─── Toast Feedback ─── */
        #flyer-toast-box {
            position: fixed;
            bottom: 24px;
            right: 24px;
            z-index: 1000;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .toast-bubble-card {
            background: #111827;
            color: #fff;
            border: 1px solid rgba(255,255,255,0.22);
            padding: 0.85rem 1.35rem;
            border-radius: 14px;
            font-size: 0.9rem;
            font-weight: 700;
            box-shadow: 0 14px 40px rgba(0,0,0,0.75);
            animation: toastPop 0.3s ease;
        }

        @keyframes toastPop {
            from { transform: translateY(20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        /* ─── Multi-Device Responsive Breakpoints ─── */
        @media (max-width: 960px) {
            .bottom-overlay-grid {
                grid-template-columns: 1fr;
                gap: 1.35rem;
            }
            .hero-event-name {
                font-size: 2.3rem;
            }
            .flyer-single-stage {
                padding: 2rem 1.25rem;
            }
        }

        @media (max-width: 600px) {
            .hero-event-name {
                font-size: 1.85rem;
            }
            .flyer-single-stage {
                padding: 1.5rem 0.9rem;
            }
            .glass-station-pod, .glass-action-pod {
                padding: 1.35rem 1.15rem;
                border-radius: 22px;
            }
            .meta-chip {
                font-size: 0.82rem;
                padding: 0.5rem 0.85rem;
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

    <!-- 2. Single-Page Modern Overlay Stage Directly On Top of The Image -->
    <main class="flyer-single-stage">
        
        <!-- Top Hero Overlay: Event Name & Meta Badges -->
        <header class="hero-showcase">
            <div class="badges-cluster">
                <?php if ($isCompleted): ?>
                    <span class="cyber-badge cyber-badge--completed">🏁 Concluded</span>
                <?php elseif ($isLiveNow): ?>
                    <span class="cyber-badge cyber-badge--live">
                        <span class="live-dot"></span>
                        Live Now
                    </span>
                <?php else: ?>
                    <span class="cyber-badge cyber-badge--upcoming">📅 Upcoming Event</span>
                <?php endif; ?>

                <?php if (!empty($event->category)): ?>
                    <span class="cyber-badge cyber-badge--category"><?= e($event->category) ?></span>
                <?php endif; ?>
            </div>

            <h1 class="hero-event-name"><?= e($event->name) ?></h1>

            <div class="meta-chips-cluster">
                <div class="meta-chip">
                    <span>🗓️</span>
                    <span><?= $eventDate ? date('l, M d, Y • g:i A', $eventDate) : 'Date TBA' ?></span>
                </div>
                <div class="meta-chip">
                    <span>📍</span>
                    <span style="white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:340px;" title="<?= e($event->location) ?>">
                        <?= e($event->location ?? 'Venue TBA') ?>
                    </span>
                </div>
                <?php if (!empty($event->organizer_name)): ?>
                    <div class="meta-chip">
                        <span>🎙️</span>
                        <span><?= e($event->organizer_name) ?></span>
                    </div>
                <?php endif; ?>
            </div>
        </header>

        <!-- Bottom Ticket Station & Checkout Overlay -->
        <form method="GET" action="<?= e($checkoutUrl) ?>" id="flyer-tickets-form">
            <?php if (!empty($refCode)): ?>
                <input type="hidden" name="ref" value="<?= e($refCode) ?>">
            <?php endif; ?>

            <div class="bottom-overlay-grid">

                <!-- Left: Ticket Selection Overlay Card -->
                <section class="glass-station-pod">
                    <div class="station-top-strip">
                        <h2><span>🎟️</span> Select Tickets</h2>
                        <span class="station-delivery-badge">Instant Delivery</span>
                    </div>

                    <?php if ($isCompleted): ?>
                        <div style="text-align:center; padding:2.5rem 1rem;">
                            <span style="font-size:2.2rem; display:block; margin-bottom:0.35rem;">🏁</span>
                            <strong style="font-size:1.1rem; color:#fff;">Ticket Sales Closed</strong>
                            <p style="font-size:0.84rem; color:var(--flyer-text-muted); margin-top:0.25rem;">This event has concluded.</p>
                        </div>
                    <?php else: ?>
                        <div class="tier-items-list">
                            <?php foreach ($ticketTypes as $tt): 
                                $ttPrice = (float)($tt['price'] ?? 0);
                                $ttCap = (int)($tt['capacity'] ?? 0);
                                $ttSold = (int)($tt['sold'] ?? 0);
                                $isSoldOut = ($ttCap > 0 && $ttSold >= $ttCap);
                            ?>
                                <div class="tier-row-item" data-tier-id="<?= $tt['id'] ?>" data-price="<?= $ttPrice ?>" data-label="<?= e($tt['name']) ?>">
                                    <div style="flex:1; min-width:0; padding-right:0.5rem;">
                                        <div class="tier-label-name">
                                            <span><?= e($tt['name']) ?></span>
                                            <?php if ($isSoldOut): ?>
                                                <span style="font-size:0.68rem; background:#ef4444; color:#fff; padding:2px 6px; border-radius:6px;">Sold Out</span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="tier-price-highlight">
                                            <?= $ttPrice > 0 ? format_price($ttPrice) : 'Free Admission' ?>
                                        </div>
                                        <?php if (!empty($tt['description'])): ?>
                                            <p style="font-size:0.75rem; color:var(--flyer-text-muted); line-height:1.25;"><?= e($tt['description']) ?></p>
                                        <?php endif; ?>
                                    </div>

                                    <div class="stepper-control-wrap">
                                        <button type="button" class="stepper-btn" data-action="dec" <?= $isSoldOut ? 'disabled' : '' ?>>−</button>
                                        <span class="stepper-display-val">0</span>
                                        <input type="hidden" name="qty[<?= $tt['id'] ?>]" value="0" class="tier-qty-input" data-max="<?= $isSoldOut ? 0 : max(1, $ttCap - $ttSold) ?>">
                                        <button type="button" class="stepper-btn" data-action="inc" <?= $isSoldOut ? 'disabled' : '' ?>>+</button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </section>

                <!-- Right: Real-Time Order Calculation, CTA & Social Share Overlay -->
                <section class="glass-action-pod">
                    <div>
                        <div class="summary-calculation-box">
                            <div id="dynamic-summary-lines">
                                <p style="font-size:0.85rem; color:var(--flyer-text-muted); margin:0;">Select tickets on the left to proceed.</p>
                            </div>
                            <div class="summary-line-item grand-total">
                                <span>Estimated Total</span>
                                <strong id="dynamic-total-val" style="color:var(--flyer-accent);">0.00</strong>
                            </div>
                        </div>

                        <div style="margin-top:1.15rem;">
                            <button type="submit" id="btn-proceed-order" class="checkout-main-cta" disabled>
                                <span>🎟️</span> Continue to Checkout →
                            </button>
                        </div>
                    </div>

                    <!-- Overlaid Social & Copy Link Strip -->
                    <div class="social-share-strip">
                        <button type="button" class="social-pill-btn copy-link-highlight" id="btn-copy-flyer-link" data-url="<?= e($flyerUrl) ?>" title="Copy dedicated ticket link">
                            📋 Copy Link
                        </button>
                        <a href="https://api.whatsapp.com/send?text=<?= $shareText ?>%20<?= $encodedFlyerUrl ?>" target="_blank" rel="noopener" class="social-pill-btn" title="Share on WhatsApp">
                            💬 WhatsApp
                        </a>
                        <a href="https://twitter.com/intent/tweet?text=<?= $shareText ?>&url=<?= $encodedFlyerUrl ?>" target="_blank" rel="noopener" class="social-pill-btn" title="Share on X">
                            🐦 X
                        </a>
                    </div>
                </section>

            </div>
        </form>

    </main>

    <!-- Toast Feedback Container -->
    <div id="flyer-toast-box"></div>

    <!-- Stepper & Dynamic Calculation Script -->
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const tierRows = document.querySelectorAll('.tier-row-item');
        const summaryLines = document.getElementById('dynamic-summary-lines');
        const dynamicTotal = document.getElementById('dynamic-total-val');
        const submitBtn = document.getElementById('btn-proceed-order');
        const currencySymbol = '<?= e($currencySymbol) ?>';

        function formatPrice(val) {
            return currencySymbol + val.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }

        function refreshOrder() {
            let total = 0;
            let totalCount = 0;
            let linesHtml = '';

            tierRows.forEach(row => {
                const price = parseFloat(row.getAttribute('data-price')) || 0;
                const label = row.getAttribute('data-label') || 'Ticket';
                const input = row.querySelector('.tier-qty-input');
                const qty = parseInt(input ? input.value : 0, 10) || 0;

                if (qty > 0) {
                    const subtotal = price * qty;
                    total += subtotal;
                    totalCount += qty;
                    linesHtml += `
                        <div class="summary-line-item">
                            <span>${qty}x ${label}</span>
                            <strong style="color:#fff;">${(subtotal > 0) ? formatPrice(subtotal) : 'Free'}</strong>
                        </div>
                    `;
                }
            });

            if (totalCount === 0) {
                summaryLines.innerHTML = '<p style="font-size:0.85rem; color:var(--flyer-text-muted); margin:0;">Select tickets on the left to proceed.</p>';
                dynamicTotal.textContent = '0.00';
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.textContent = 'Continue to Checkout →';
                }
            } else {
                summaryLines.innerHTML = linesHtml;
                dynamicTotal.textContent = (total > 0) ? formatPrice(total) : 'Free';
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.textContent = (total > 0) ? 'Continue to Checkout →' : 'Get Free Tickets →';
                }
            }
        }

        tierRows.forEach(row => {
            const decBtn = row.querySelector('[data-action="dec"]');
            const incBtn = row.querySelector('[data-action="inc"]');
            const numSpan = row.querySelector('.stepper-display-val');
            const input = row.querySelector('.tier-qty-input');
            const max = parseInt(input.getAttribute('data-max'), 10) || 20;

            if (decBtn && incBtn && numSpan && input) {
                decBtn.addEventListener('click', function () {
                    let cur = parseInt(input.value, 10) || 0;
                    if (cur > 0) {
                        cur--;
                        input.value = cur;
                        numSpan.textContent = cur;
                        refreshOrder();
                    }
                });

                incBtn.addEventListener('click', function () {
                    let cur = parseInt(input.value, 10) || 0;
                    if (cur < max) {
                        cur++;
                        input.value = cur;
                        numSpan.textContent = cur;
                        refreshOrder();
                    }
                });
            }
        });

        // Copy Link Action
        const copyBtn = document.getElementById('btn-copy-flyer-link');
        if (copyBtn) {
            copyBtn.addEventListener('click', function () {
                const url = this.getAttribute('data-url');
                if (navigator.clipboard && window.isSecureContext) {
                    navigator.clipboard.writeText(url).then(() => {
                        toast('📋 Dedicated ticket link copied!');
                    }).catch(() => fallback(url));
                } else {
                    fallback(url);
                }
            });
        }

        function fallback(text) {
            const temp = document.createElement('input');
            temp.value = text;
            document.body.appendChild(temp);
            temp.select();
            document.execCommand('copy');
            document.body.removeChild(temp);
            toast('📋 Dedicated ticket link copied!');
        }

        function toast(msg) {
            const container = document.getElementById('flyer-toast-box');
            if (!container) return;
            const bubble = document.createElement('div');
            bubble.className = 'toast-bubble-card';
            bubble.textContent = msg;
            container.appendChild(bubble);
            setTimeout(() => {
                bubble.style.opacity = '0';
                setTimeout(() => bubble.remove(), 300);
            }, 3000);
        }
    });
    </script>
</body>
</html>
