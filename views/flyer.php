<?php

/**
 * Dedicated Event Flyer & Ticket Sales Page
 *
 * Immersive modern flyer experience showcasing the event poster as the centerpiece,
 * ambient background lighting, live ticket selection, order calculation, and responsive design.
 *
 * @package Eventlyy
 * @subpackage Views/Flyer
 * @author Celio Natti
 * @version 7.0.0
 */

$eventImg = !empty($event->image) ? $event->image : 'https://images.unsplash.com/photo-1506157786151-b8491531f063?w=1600&q=85&auto=format&fit=crop';
$eventDate = strtotime($event->date ?? '');
$now = time();
$liveWindowEnd = $eventDate ? $eventDate + (event_completion_hours() * 3600) : 0;
$isLiveNow = !$isCompleted && ($eventDate && $now >= $eventDate && $now <= $liveWindowEnd);
$isToday = !$isCompleted && ($eventDate && date('Y-m-d', $now) === date('Y-m-d', $eventDate));
$isUpcoming = !$isCompleted && !$isLiveNow;

$rawDesc = $event->description ?? '';
$shortDesc = (mb_strlen($rawDesc) > 180) ? mb_strimwidth($rawDesc, 0, 180, '…') : $rawDesc;

$currencySymbol = get_currency_symbol();
$flyerUrl = event_flyer_url($event);
$eventUrl = event_url($event);
$eventSlug = !empty($event->custom_url) ? $event->custom_url : ($event->slug ?? $event->id);
$checkoutUrl = url('f/' . $eventSlug . '/checkout');

// Social Share links
$shareText = urlencode("🎟️ Grab tickets for " . ($event->name ?? 'this event') . " on Eventlyy!");
$encodedFlyerUrl = urlencode($flyerUrl);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($event->name) ?> — Event Flyer &amp; Tickets</title>
    
    <!-- OpenGraph & Social Cards -->
    <meta property="og:type" content="website">
    <meta property="og:title" content="<?= e($event->name) ?> — Event Flyer">
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
            --flyer-dominant-rgb: 16, 185, 129;
            --flyer-dominant: #10b981;
            --flyer-dominant-glow: rgba(16, 185, 129, 0.4);
            --flyer-accent: #10b981;
            --flyer-brand: #6366f1;
            --flyer-bg: #030712;
            --flyer-card: rgba(15, 23, 42, 0.82);
            --flyer-border: rgba(255, 255, 255, 0.12);
            --flyer-text-main: #ffffff;
            --flyer-text-muted: #94a3b8;
            --radius-lg: 24px;
            --radius-md: 16px;
            --radius-sm: 10px;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html, body {
            min-height: 100vh;
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: var(--flyer-bg);
            color: var(--flyer-text-main);
            -webkit-font-smoothing: antialiased;
            overflow-x: hidden;
        }

        /* ─── 1. Ambient Background Stage ─── */
        .flyer-ambient-stage {
            position: fixed;
            inset: 0;
            width: 100vw;
            height: 100vh;
            z-index: 0;
            overflow: hidden;
            pointer-events: none;
        }

        .flyer-ambient-halo {
            position: absolute;
            inset: -60px;
            background-image: url('<?= e($eventImg) ?>');
            background-size: cover;
            background-position: center;
            filter: blur(80px) brightness(0.28) saturate(180%);
            transform: scale(1.2);
            opacity: 0.85;
        }

        .flyer-ambient-scrim {
            position: absolute;
            inset: 0;
            background: 
                radial-gradient(circle at 50% 25%, rgba(99, 102, 241, 0.12) 0%, transparent 60%),
                linear-gradient(180deg, 
                    rgba(3, 7, 18, 0.45) 0%, 
                    rgba(3, 7, 18, 0.8) 50%, 
                    rgba(3, 7, 18, 0.98) 100%
                );
        }

        /* ─── 2. Top Navigation Bar ─── */
        .flyer-nav-bar {
            position: sticky;
            top: 0;
            z-index: 50;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.9rem 1.75rem;
            background: rgba(8, 12, 24, 0.75);
            backdrop-filter: blur(24px) saturate(180%);
            -webkit-backdrop-filter: blur(24px) saturate(180%);
            border-bottom: 1px solid var(--flyer-border);
        }

        .flyer-nav-left {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .flyer-back-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: #f1f5f9;
            font-size: 0.85rem;
            font-weight: 700;
            text-decoration: none;
            padding: 0.5rem 1rem;
            border-radius: var(--radius-sm);
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid var(--flyer-border);
            transition: all 0.2s ease;
        }

        .flyer-back-btn:hover {
            background: rgba(255, 255, 255, 0.18);
            color: #fff;
            transform: translateX(-2px);
        }

        .flyer-nav-brand {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.9rem;
            font-weight: 800;
            color: #fff;
            text-decoration: none;
        }

        .flyer-nav-brand span {
            background: linear-gradient(135deg, #10b981, #6366f1);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .flyer-nav-badge {
            font-size: 0.72rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            padding: 0.3rem 0.7rem;
            border-radius: 999px;
            background: rgba(99, 102, 241, 0.15);
            border: 1px solid rgba(99, 102, 241, 0.35);
            color: #a5b4fc;
        }

        /* ─── 3. Main Stage Container ─── */
        .flyer-main-stage {
            position: relative;
            z-index: 10;
            max-width: 1220px;
            margin: 0 auto;
            padding: 2.25rem 1.75rem 4rem;
        }

        .flyer-grid {
            display: grid;
            grid-template-columns: 460px 1fr;
            gap: 2.5rem;
            align-items: start;
        }

        /* ─── Left Column: Poster Showcase ─── */
        .flyer-poster-column {
            position: sticky;
            top: 5rem;
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
        }

        .flyer-poster-card {
            position: relative;
            border-radius: var(--radius-lg);
            overflow: hidden;
            background: var(--flyer-card);
            border: 1.5px solid var(--flyer-border);
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.75), 0 0 1px 1px rgba(255, 255, 255, 0.12) inset;
            cursor: pointer;
            transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.3s ease, border-color 0.3s ease;
        }

        .flyer-poster-card:hover {
            transform: translateY(-4px);
            border-color: rgba(99, 102, 241, 0.45);
            box-shadow: 0 26px 80px rgba(0, 0, 0, 0.85), 0 0 30px rgba(99, 102, 241, 0.25);
        }

        .flyer-poster-image-wrap {
            position: relative;
            width: 100%;
            height: 580px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            background: rgba(10, 14, 26, 0.9);
        }

        .flyer-poster-blur {
            position: absolute;
            inset: -20px;
            background-size: cover;
            background-position: center;
            filter: blur(28px) brightness(0.5);
            opacity: 0.75;
            transform: scale(1.15);
            pointer-events: none;
        }

        .flyer-poster-img {
            position: relative;
            z-index: 1;
            width: 100%;
            height: 100%;
            object-fit: contain;
            display: block;
            transition: transform 0.35s ease;
        }

        .flyer-poster-card:hover .flyer-poster-img {
            transform: scale(1.02);
        }

        .flyer-poster-zoom-hint {
            position: absolute;
            bottom: 1rem;
            right: 1rem;
            z-index: 2;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.45rem 0.9rem;
            border-radius: 999px;
            background: rgba(0, 0, 0, 0.75);
            backdrop-filter: blur(12px);
            color: #fff;
            font-size: 0.75rem;
            font-weight: 700;
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.5);
            transition: all 0.2s ease;
        }

        .flyer-poster-card:hover .flyer-poster-zoom-hint {
            background: rgba(99, 102, 241, 0.9);
            border-color: rgba(255, 255, 255, 0.35);
        }

        /* Quick Share Bar Below Poster */
        .flyer-share-card {
            background: var(--flyer-card);
            border: 1px solid var(--flyer-border);
            border-radius: var(--radius-md);
            padding: 1.15rem 1.35rem;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.4);
        }

        .flyer-share-label {
            display: block;
            font-size: 0.75rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: var(--flyer-text-muted);
            margin-bottom: 0.65rem;
        }

        .flyer-share-btns {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .flyer-share-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            padding: 0.5rem 0.9rem;
            border-radius: var(--radius-sm);
            font-size: 0.8rem;
            font-weight: 700;
            text-decoration: none;
            color: #fff;
            border: none;
            cursor: pointer;
            transition: transform 0.15s ease, filter 0.15s ease;
        }

        .flyer-share-btn:hover {
            transform: translateY(-1px);
            filter: brightness(1.1);
        }

        .flyer-share-btn--copy {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: #f1f5f9;
        }

        .flyer-share-btn--copy:hover {
            background: rgba(255, 255, 255, 0.18);
        }

        .flyer-share-btn--whatsapp {
            background: #25D366;
        }

        .flyer-share-btn--twitter {
            background: #000;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        /* ─── Right Column: Event Hub & Tickets Desk ─── */
        .flyer-content-column {
            display: flex;
            flex-direction: column;
            gap: 1.75rem;
            min-width: 0;
        }

        /* Status Badges */
        .flyer-badges {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            flex-wrap: wrap;
        }

        .flyer-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.4rem 0.9rem;
            border-radius: 999px;
            font-size: 0.8rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            backdrop-filter: blur(16px);
        }

        .flyer-badge--live {
            background: rgba(239, 68, 68, 0.9);
            color: #fff;
            box-shadow: 0 0 20px rgba(239, 68, 68, 0.65);
        }

        .flyer-pulse-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #fff;
            animation: flyerDotPulse 1.6s infinite;
        }

        @keyframes flyerDotPulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.35; transform: scale(0.8); }
        }

        .flyer-badge--today {
            background: rgba(16, 185, 129, 0.9);
            color: #fff;
            box-shadow: 0 0 18px rgba(16, 185, 129, 0.5);
        }

        .flyer-badge--upcoming {
            background: rgba(99, 102, 241, 0.22);
            color: #a5b4fc;
            border: 1px solid rgba(99, 102, 241, 0.4);
        }

        .flyer-badge--completed {
            background: rgba(100, 116, 139, 0.7);
            color: #fff;
        }

        .flyer-badge--category {
            background: rgba(255, 255, 255, 0.08);
            color: #f1f5f9;
            border: 1px solid rgba(255, 255, 255, 0.16);
        }

        /* Event Title & Summary */
        .flyer-title {
            font-size: clamp(2.2rem, 3.8vw, 3.2rem);
            font-weight: 900;
            line-height: 1.12;
            letter-spacing: -0.03em;
            color: #fff;
            text-shadow: 0 4px 30px rgba(0, 0, 0, 0.8);
            word-break: break-word;
        }

        /* Meta Chips Grid */
        .flyer-meta-chips {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 0.85rem;
        }

        .flyer-chip {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.85rem 1rem;
            border-radius: var(--radius-md);
            background: var(--flyer-card);
            border: 1px solid var(--flyer-border);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.35);
            min-width: 0;
            overflow: hidden;
        }

        .flyer-chip-icon {
            font-size: 1.35rem;
            width: 38px;
            height: 38px;
            min-width: 38px;
            border-radius: var(--radius-sm);
            background: rgba(255, 255, 255, 0.06);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .flyer-chip-text {
            min-width: 0;
            flex: 1;
            overflow: hidden;
        }

        .flyer-chip-label {
            display: block;
            font-size: 0.7rem;
            text-transform: uppercase;
            font-weight: 800;
            letter-spacing: 0.06em;
            color: var(--flyer-text-muted);
            margin-bottom: 0.15rem;
        }

        .flyer-chip-val {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            font-size: 0.88rem;
            font-weight: 700;
            color: #fff;
            line-height: 1.35;
            word-break: break-word;
        }

        /* About / Description snippet */
        .flyer-desc-card {
            background: var(--flyer-card);
            border: 1px solid var(--flyer-border);
            border-radius: var(--radius-md);
            padding: 1.35rem 1.6rem;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.35);
        }

        .flyer-desc-card h3 {
            font-size: 0.95rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: var(--flyer-text-muted);
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }

        .flyer-desc-text {
            font-size: 0.95rem;
            line-height: 1.65;
            color: #e2e8f0;
            white-space: pre-line;
            word-break: break-word;
        }

        /* ─── Tickets Desk ─── */
        .flyer-tickets-desk {
            background: var(--flyer-card);
            border: 1.5px solid var(--flyer-border);
            border-radius: var(--radius-lg);
            padding: 1.75rem 2rem;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.65), 0 0 1px 1px rgba(255, 255, 255, 0.12) inset;
        }

        .flyer-tickets-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-bottom: 1rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            margin-bottom: 1.35rem;
        }

        .flyer-tickets-header h2 {
            font-size: 1.35rem;
            font-weight: 900;
            letter-spacing: -0.02em;
            display: flex;
            align-items: center;
            gap: 0.55rem;
        }

        .flyer-instant-badge {
            font-size: 0.75rem;
            font-weight: 800;
            color: var(--flyer-accent);
            background: rgba(16, 185, 129, 0.14);
            border: 1px solid rgba(16, 185, 129, 0.35);
            padding: 0.25rem 0.75rem;
            border-radius: 999px;
        }

        /* Tiers List */
        .flyer-tiers-list {
            display: flex;
            flex-direction: column;
            gap: 0.85rem;
            margin-bottom: 1.5rem;
        }

        .flyer-tier-row {
            display: grid;
            grid-template-columns: 1fr auto auto;
            align-items: center;
            gap: 1.25rem;
            background: rgba(255, 255, 255, 0.04);
            border: 1.5px solid rgba(255, 255, 255, 0.08);
            border-radius: var(--radius-md);
            padding: 1.15rem 1.35rem;
            transition: all 0.2s ease;
        }

        .flyer-tier-row:hover {
            background: rgba(255, 255, 255, 0.08);
            border-color: rgba(99, 102, 241, 0.45);
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.4);
        }

        .flyer-tier-row--soldout {
            opacity: 0.5;
            background: rgba(255, 255, 255, 0.02);
        }

        .flyer-tier-row--soldout:hover {
            transform: none;
            border-color: rgba(255, 255, 255, 0.08);
            box-shadow: none;
        }

        .flyer-tier-info {
            min-width: 0;
        }

        .flyer-tier-name-row {
            display: flex;
            align-items: center;
            gap: 0.55rem;
            flex-wrap: wrap;
        }

        .flyer-tier-name {
            font-size: 1.05rem;
            font-weight: 800;
            color: #fff;
        }

        .flyer-tier-tag {
            font-size: 0.7rem;
            font-weight: 800;
            padding: 0.15rem 0.55rem;
            border-radius: 6px;
            line-height: 1.2;
        }

        .flyer-tier-tag--soldout {
            background: #ef4444;
            color: #fff;
        }

        .flyer-tier-tag--low {
            background: rgba(245, 158, 11, 0.15);
            color: #f59e0b;
            border: 1px solid rgba(245, 158, 11, 0.3);
        }

        .flyer-tier-desc {
            font-size: 0.82rem;
            color: var(--flyer-text-muted);
            margin-top: 0.25rem;
            line-height: 1.4;
        }

        /* Stepper */
        .flyer-stepper {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            background: rgba(0, 0, 0, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: var(--radius-sm);
            padding: 0.25rem 0.45rem;
        }

        .flyer-stepper-btn {
            width: 34px;
            height: 34px;
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.09);
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

        .flyer-stepper-btn:hover:not(:disabled) {
            background: var(--flyer-brand);
            transform: scale(1.08);
        }

        .flyer-stepper-btn:disabled {
            opacity: 0.3;
            cursor: not-allowed;
        }

        .flyer-stepper-val {
            font-size: 1.05rem;
            font-weight: 800;
            width: 28px;
            text-align: center;
            color: #fff;
        }

        .flyer-tier-price {
            font-size: 1.25rem;
            font-weight: 900;
            color: #fff;
            white-space: nowrap;
            text-align: right;
            min-width: 80px;
        }

        .flyer-tier-price--free {
            color: var(--flyer-accent);
        }

        /* Order Calculation Box */
        .flyer-summary-box {
            background: rgba(0, 0, 0, 0.45);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: var(--radius-md);
            padding: 1.2rem 1.4rem;
            display: flex;
            flex-direction: column;
            gap: 0.85rem;
        }

        .flyer-summary-lines {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            min-height: 40px;
        }

        .flyer-summary-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.9rem;
            color: var(--flyer-text-muted);
        }

        .flyer-summary-row strong {
            color: #fff;
        }

        .flyer-summary-empty {
            font-size: 0.86rem;
            color: var(--flyer-text-muted);
            margin: auto 0;
            text-align: center;
        }

        .flyer-summary-total {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            padding-top: 0.75rem;
            border-top: 1px dashed rgba(255, 255, 255, 0.18);
        }

        .flyer-summary-total span {
            font-size: 1rem;
            font-weight: 700;
            color: #fff;
        }

        .flyer-summary-total strong {
            font-size: 1.55rem;
            font-weight: 900;
            color: var(--flyer-accent);
        }

        .flyer-checkout-cta {
            width: 100%;
            padding: 1.15rem 1.5rem;
            border-radius: var(--radius-md);
            border: none;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: #fff;
            font-size: 1.1rem;
            font-weight: 900;
            cursor: pointer;
            box-shadow: 0 10px 30px rgba(16, 185, 129, 0.35);
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.6rem;
            text-decoration: none;
            margin-top: 1.25rem;
        }

        .flyer-checkout-cta:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 16px 40px rgba(16, 185, 129, 0.55);
            filter: brightness(1.06);
        }

        .flyer-checkout-cta:disabled {
            opacity: 0.45;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        /* Calendar Options */
        .flyer-calendar-block {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-top: 1.15rem;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            margin-top: 1.35rem;
            flex-wrap: wrap;
            gap: 0.75rem;
        }

        .flyer-calendar-label {
            font-size: 0.78rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--flyer-text-muted);
        }

        .flyer-calendar-btns {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .flyer-calendar-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.4rem 0.8rem;
            border-radius: var(--radius-sm);
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.15);
            color: #f1f5f9;
            font-size: 0.78rem;
            font-weight: 700;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .flyer-calendar-pill:hover {
            background: rgba(99, 102, 241, 0.25);
            border-color: rgba(99, 102, 241, 0.5);
            color: #fff;
            transform: translateY(-1px);
        }

        /* ─── 4. Fullscreen Lightbox Modal ─── */
        .flyer-lightbox-modal {
            position: fixed;
            inset: 0;
            z-index: 10000;
            background: rgba(0, 0, 0, 0.94);
            backdrop-filter: blur(16px);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            animation: flyerFadeIn 0.2s ease-out;
        }

        @keyframes flyerFadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .flyer-lightbox-img {
            max-width: 92vw;
            max-height: 90vh;
            object-fit: contain;
            border-radius: var(--radius-md);
            box-shadow: 0 30px 80px rgba(0, 0, 0, 0.9);
        }

        .flyer-lightbox-close {
            position: absolute;
            top: 1.5rem;
            right: 2rem;
            color: #fff;
            font-size: 2.5rem;
            font-weight: 700;
            cursor: pointer;
            line-height: 1;
            z-index: 10001;
            transition: color 0.2s;
        }

        .flyer-lightbox-close:hover {
            color: var(--flyer-accent);
        }

        /* ─── 5. Toast Feedback Bubble ─── */
        #flyer-toast-box {
            position: fixed;
            bottom: 24px;
            right: 24px;
            z-index: 100000;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .flyer-toast {
            background: #0f172a;
            color: #fff;
            border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 0.85rem 1.35rem;
            border-radius: var(--radius-sm);
            font-size: 0.88rem;
            font-weight: 700;
            box-shadow: 0 14px 40px rgba(0, 0, 0, 0.75);
            animation: flyerToastPop 0.25s ease;
        }

        @keyframes flyerToastPop {
            from { transform: translateY(16px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        /* ─── 6. Multi-Device Responsive Breakpoints ─── */
        @media (max-width: 1080px) {
            .flyer-grid {
                grid-template-columns: 380px 1fr;
                gap: 2rem;
            }
            .flyer-poster-image-wrap {
                height: 480px;
            }
        }

        @media (max-width: 880px) {
            .flyer-grid {
                grid-template-columns: 1fr;
                gap: 2rem;
            }
            .flyer-poster-column {
                position: static;
                max-width: 540px;
                margin: 0 auto;
                width: 100%;
            }
            .flyer-poster-image-wrap {
                height: 440px;
            }
            .flyer-meta-chips {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
        }

        @media (max-width: 680px) {
            .flyer-nav-bar {
                padding: 0.75rem 1rem;
            }
            .flyer-main-stage {
                padding: 1.25rem 1rem 3.5rem;
            }
            .flyer-title {
                font-size: 1.85rem;
            }
            .flyer-poster-image-wrap {
                height: 360px;
            }
            .flyer-meta-chips {
                grid-template-columns: 1fr;
                gap: 0.65rem;
            }
            .flyer-tickets-desk {
                padding: 1.25rem 1.15rem;
                border-radius: var(--radius-md);
            }
            .flyer-tier-row {
                grid-template-columns: 1fr auto;
                gap: 0.85rem 1rem;
                padding: 1rem 1.15rem;
            }
            .flyer-tier-info {
                grid-column: 1 / -1;
            }
            .flyer-stepper {
                justify-self: start;
            }
            .flyer-tier-price {
                justify-self: end;
                text-align: right;
                min-width: auto;
            }
            .flyer-calendar-block {
                flex-direction: column;
                align-items: flex-start;
            }
        }

        @media (max-width: 420px) {
            .flyer-title {
                font-size: 1.6rem;
            }
            .flyer-poster-image-wrap {
                height: 300px;
            }
            .flyer-badges {
                gap: 0.4rem;
            }
            .flyer-badge {
                font-size: 0.72rem;
                padding: 0.35rem 0.7rem;
            }
            .flyer-share-btns {
                gap: 0.4rem;
            }
            .flyer-share-btn {
                font-size: 0.75rem;
                padding: 0.45rem 0.75rem;
            }
        }
    </style>
</head>
<body>

    <!-- 1. Ambient Background Stage -->
    <div class="flyer-ambient-stage" aria-hidden="true">
        <div class="flyer-ambient-halo"></div>
        <div class="flyer-ambient-scrim"></div>
    </div>

    <!-- 2. Sticky Glass Top Navigation -->
    <header class="flyer-nav-bar">
        <div class="flyer-nav-left">
            <a href="<?= e($eventUrl) ?>" class="flyer-back-btn" title="View standard event details page">
                ← <span>Full Details</span>
            </a>
            <span class="flyer-nav-badge">🎨 Flyer Mode</span>
        </div>
        <a href="<?= url('/') ?>" class="flyer-nav-brand">
            <span>Eventlyy</span>
        </a>
    </header>

    <!-- 3. Main Stage -->
    <main class="flyer-main-stage">
        <div class="flyer-grid">
            
            <!-- Left Column: The Poster Centerpiece -->
            <div class="flyer-poster-column">
                <div class="flyer-poster-card" id="flyer-poster-trigger" title="Click to view full flyer poster">
                    <div class="flyer-poster-image-wrap">
                        <div class="flyer-poster-blur" style="background-image: url('<?= e($eventImg) ?>');"></div>
                        <img src="<?= e($eventImg) ?>" alt="<?= e($event->name) ?> Poster" class="flyer-poster-img" id="flyer-main-img">
                    </div>
                    <div class="flyer-poster-zoom-hint">
                        <span>🔍</span> Tap to Zoom Poster
                    </div>
                </div>

                <!-- Quick Share Bar -->
                <div class="flyer-share-card">
                    <span class="flyer-share-label">Share This Flyer</span>
                    <div class="flyer-share-btns">
                        <button type="button" class="flyer-share-btn flyer-share-btn--copy" id="btn-copy-link" data-url="<?= e($flyerUrl) ?>" title="Copy flyer ticket link">
                            🔗 <span id="copy-btn-label">Copy Link</span>
                        </button>
                        <a href="https://api.whatsapp.com/send?text=<?= $shareText ?>%20<?= $encodedFlyerUrl ?>" target="_blank" rel="noopener" class="flyer-share-btn flyer-share-btn--whatsapp" title="Share on WhatsApp">
                            <svg width="13" height="13" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                            WhatsApp
                        </a>
                        <a href="https://twitter.com/intent/tweet?text=<?= $shareText ?>&url=<?= $encodedFlyerUrl ?>" target="_blank" rel="noopener" class="flyer-share-btn flyer-share-btn--twitter" title="Share on X">
                            <svg width="12" height="12" fill="currentColor" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                            Twitter
                        </a>
                    </div>
                </div>
            </div>

            <!-- Right Column: Event Info & Ticket Selection -->
            <div class="flyer-content-column">
                
                <!-- Status & Category Badges -->
                <div class="flyer-badges">
                    <?php if ($isCompleted): ?>
                        <span class="flyer-badge flyer-badge--completed">🏁 Concluded</span>
                    <?php elseif ($isLiveNow): ?>
                        <span class="flyer-badge flyer-badge--live">
                            <span class="flyer-pulse-dot"></span> Live Gate Sales
                        </span>
                    <?php elseif ($isToday): ?>
                        <span class="flyer-badge flyer-badge--today">⚡ Happening Today</span>
                    <?php else: ?>
                        <span class="flyer-badge flyer-badge--upcoming">📅 Upcoming Event</span>
                    <?php endif; ?>

                    <?php if (!empty($event->category)): ?>
                        <span class="flyer-badge flyer-badge--category"><?= e($event->category) ?></span>
                    <?php endif; ?>
                </div>

                <!-- Event Name -->
                <h1 class="flyer-title"><?= e($event->name) ?></h1>

                <!-- Meta Chips -->
                <div class="flyer-meta-chips">
                    <div class="flyer-chip">
                        <span class="flyer-chip-icon">🗓️</span>
                        <div class="flyer-chip-text">
                            <span class="flyer-chip-label">Date &amp; Time</span>
                            <span class="flyer-chip-val"><?= $eventDate ? date('M d, Y • g:i A', $eventDate) : 'Date TBA' ?></span>
                        </div>
                    </div>
                    <div class="flyer-chip">
                        <span class="flyer-chip-icon">📍</span>
                        <div class="flyer-chip-text">
                            <span class="flyer-chip-label">Venue</span>
                            <span class="flyer-chip-val" title="<?= e($event->location ?? 'Venue TBA') ?>">
                                <?= e($event->location ?? 'Venue TBA') ?>
                            </span>
                        </div>
                    </div>
                    <?php if (!empty($event->organizer_name)): ?>
                        <div class="flyer-chip">
                            <span class="flyer-chip-icon">🎙️</span>
                            <div class="flyer-chip-text">
                                <span class="flyer-chip-label">Organizer</span>
                                <span class="flyer-chip-val"><?= e($event->organizer_name) ?></span>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Short Description Card -->
                <?php if (!empty($event->description)): ?>
                    <div class="flyer-desc-card">
                        <h3><span>ℹ️</span> About This Event</h3>
                        <p class="flyer-desc-text"><?= e($shortDesc) ?></p>
                    </div>
                <?php endif; ?>

                <!-- Ticket Selection Desk -->
                <form method="GET" action="<?= e($checkoutUrl) ?>" id="flyer-tickets-form">
                    <?php if (!empty($refCode)): ?>
                        <input type="hidden" name="ref" value="<?= e($refCode) ?>">
                    <?php endif; ?>

                    <section class="flyer-tickets-desk">
                        <div class="flyer-tickets-header">
                            <h2><span>🎟️</span> Select Tickets</h2>
                            <span class="flyer-instant-badge">⚡ Instant QR Delivery</span>
                        </div>

                        <?php if ($isCompleted): ?>
                            <div style="text-align:center; padding:2.5rem 1.5rem;">
                                <span style="font-size:2.8rem; display:block; margin-bottom:0.5rem;">🏁</span>
                                <strong style="font-size:1.25rem; color:#fff; display:block; margin-bottom:0.35rem;">Ticket Sales Closed</strong>
                                <p style="font-size:0.9rem; color:var(--flyer-text-muted);">This event has concluded. Thank you for your interest!</p>
                            </div>
                        <?php elseif (empty($ticketTypes)): ?>
                            <div style="text-align:center; padding:2.5rem 1.5rem; color:var(--flyer-text-muted);">
                                No ticket tiers currently available for this event.
                            </div>
                        <?php else: ?>
                            <div class="flyer-tiers-list">
                                <?php foreach ($ticketTypes as $tt): 
                                    $ttPrice = (float)($tt['price'] ?? 0);
                                    $ttCap = (int)($tt['capacity'] ?? 0);
                                    $ttSold = (int)($tt['sold'] ?? 0);
                                    $remaining = max(0, $ttCap - $ttSold);
                                    $isSoldOut = ($ttCap > 0 && $remaining <= 0);
                                ?>
                                    <div class="flyer-tier-row<?= $isSoldOut ? ' flyer-tier-row--soldout' : '' ?>" 
                                         data-tier-id="<?= $tt['id'] ?>" 
                                         data-price="<?= $ttPrice ?>" 
                                         data-label="<?= e($tt['name']) ?>">
                                        <div class="flyer-tier-info">
                                            <div class="flyer-tier-name-row">
                                                <span class="flyer-tier-name"><?= e($tt['name']) ?></span>
                                                <?php if ($isSoldOut): ?>
                                                    <span class="flyer-tier-tag flyer-tier-tag--soldout">Sold Out</span>
                                                <?php elseif ($remaining <= 15): ?>
                                                    <span class="flyer-tier-tag flyer-tier-tag--low"><?= $remaining ?> left</span>
                                                <?php endif; ?>
                                            </div>
                                            <?php if (!empty($tt['description'])): ?>
                                                <p class="flyer-tier-desc"><?= e($tt['description']) ?></p>
                                            <?php endif; ?>
                                        </div>

                                        <div class="flyer-stepper">
                                            <button type="button" class="flyer-stepper-btn" data-action="dec" <?= $isSoldOut ? 'disabled' : '' ?> aria-label="Decrease quantity">−</button>
                                            <span class="flyer-stepper-val" aria-live="polite">0</span>
                                            <input type="hidden" name="qty[<?= $tt['id'] ?>]" value="0" class="flyer-qty-input" data-max="<?= $isSoldOut ? 0 : ($remaining > 0 ? $remaining : 20) ?>">
                                            <button type="button" class="flyer-stepper-btn" data-action="inc" <?= $isSoldOut ? 'disabled' : '' ?> aria-label="Increase quantity">+</button>
                                        </div>

                                        <div class="flyer-tier-price<?= $ttPrice <= 0 ? ' flyer-tier-price--free' : '' ?>">
                                            <?= $ttPrice > 0 ? format_price($ttPrice) : 'Free' ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <!-- Real-Time Order Summary Box -->
                            <div class="flyer-summary-box">
                                <div class="flyer-summary-lines" id="flyer-summary-lines">
                                    <p class="flyer-summary-empty">Select tickets above to proceed.</p>
                                </div>
                                <div class="flyer-summary-total">
                                    <span>Total Amount</span>
                                    <strong id="flyer-total-val">Free</strong>
                                </div>
                            </div>

                            <button type="submit" id="btn-proceed-order" class="flyer-checkout-cta" disabled>
                                <span>🎟️</span> Continue to Checkout →
                            </button>
                        <?php endif; ?>

                        <!-- Calendar Sync Links -->
                        <div class="flyer-calendar-block">
                            <span class="flyer-calendar-label">📅 Add to Calendar</span>
                            <div class="flyer-calendar-btns">
                                <a href="<?= event_google_calendar_url($event) ?>" target="_blank" rel="noopener" class="flyer-calendar-pill" title="Add to Google Calendar">Google</a>
                                <a href="<?= event_ics_url($event) ?>" download class="flyer-calendar-pill" title="Download iCal">Apple / iCal</a>
                                <a href="<?= event_outlook_calendar_url($event) ?>" target="_blank" rel="noopener" class="flyer-calendar-pill" title="Add to Outlook">Outlook</a>
                            </div>
                        </div>
                    </section>
                </form>

            </div>
        </div>
    </main>

    <!-- Fullscreen Poster Lightbox -->
    <div id="flyer-lightbox-modal" class="flyer-lightbox-modal" style="display:none;">
        <span class="flyer-lightbox-close" id="flyer-lightbox-close">&times;</span>
        <img class="flyer-lightbox-img" id="flyer-lightbox-img" src="<?= e($eventImg) ?>" alt="<?= e($event->name) ?> Full Poster">
    </div>

    <!-- Toast Notification Container -->
    <div id="flyer-toast-box"></div>

    <!-- Stepper, Lightbox, and Real-Time Calculation Logic -->
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const tierRows = document.querySelectorAll('.flyer-tier-row');
        const summaryLines = document.getElementById('flyer-summary-lines');
        const totalVal = document.getElementById('flyer-total-val');
        const submitBtn = document.getElementById('btn-proceed-order');
        const currencySymbol = '<?= e($currencySymbol) ?>';

        function formatPrice(val) {
            if (val <= 0) return 'Free';
            return currencySymbol + val.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }

        function refreshOrder() {
            let total = 0;
            let totalCount = 0;
            let linesHtml = '';

            tierRows.forEach(row => {
                const price = parseFloat(row.getAttribute('data-price')) || 0;
                const label = row.getAttribute('data-label') || 'Ticket';
                const input = row.querySelector('.flyer-qty-input');
                const qty = parseInt(input ? input.value : 0, 10) || 0;

                if (qty > 0) {
                    const subtotal = price * qty;
                    total += subtotal;
                    totalCount += qty;
                    linesHtml += `
                        <div class="flyer-summary-row">
                            <span>${qty}x ${label}</span>
                            <strong>${(subtotal > 0) ? formatPrice(subtotal) : 'Free'}</strong>
                        </div>
                    `;
                }
            });

            if (totalCount === 0) {
                if (summaryLines) summaryLines.innerHTML = '<p class="flyer-summary-empty">Select tickets above to proceed.</p>';
                if (totalVal) totalVal.textContent = 'Free';
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.textContent = 'Continue to Checkout →';
                }
            } else {
                if (summaryLines) summaryLines.innerHTML = linesHtml;
                if (totalVal) totalVal.textContent = (total > 0) ? formatPrice(total) : 'Free';
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.textContent = (total > 0) ? 'Continue to Checkout →' : 'Get Free Tickets →';
                }
            }
        }

        tierRows.forEach(row => {
            const decBtn = row.querySelector('[data-action="dec"]');
            const incBtn = row.querySelector('[data-action="inc"]');
            const numSpan = row.querySelector('.flyer-stepper-val');
            const input = row.querySelector('.flyer-qty-input');
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

        /* ─── Poster Lightbox ─── */
        const posterTrigger = document.getElementById('flyer-poster-trigger');
        const lightboxModal = document.getElementById('flyer-lightbox-modal');
        const lightboxClose = document.getElementById('flyer-lightbox-close');

        if (posterTrigger && lightboxModal) {
            posterTrigger.addEventListener('click', function () {
                lightboxModal.style.display = 'flex';
                document.body.style.overflow = 'hidden';
            });

            function closeLightbox() {
                lightboxModal.style.display = 'none';
                document.body.style.overflow = '';
            }

            if (lightboxClose) lightboxClose.addEventListener('click', closeLightbox);
            lightboxModal.addEventListener('click', function (e) {
                if (e.target === lightboxModal) closeLightbox();
            });

            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape' && lightboxModal.style.display === 'flex') closeLightbox();
            });
        }

        /* ─── Toast Feedback ─── */
        function toast(msg) {
            const container = document.getElementById('flyer-toast-box');
            if (!container) return;
            const bubble = document.createElement('div');
            bubble.className = 'flyer-toast';
            bubble.textContent = msg;
            container.appendChild(bubble);
            setTimeout(() => {
                bubble.style.opacity = '0';
                bubble.style.transition = 'opacity 0.25s ease';
                setTimeout(() => bubble.remove(), 260);
            }, 2600);
        }

        /* ─── Copy Link Action ─── */
        const copyBtn = document.getElementById('btn-copy-link');
        const copyLabel = document.getElementById('copy-btn-label');
        if (copyBtn) {
            copyBtn.addEventListener('click', function () {
                const url = this.getAttribute('data-url') || window.location.href;
                if (navigator.clipboard && window.isSecureContext) {
                    navigator.clipboard.writeText(url).then(() => {
                        toast('📋 Flyer ticket link copied to clipboard!');
                        if (copyLabel) {
                            copyLabel.textContent = 'Copied!';
                            setTimeout(() => copyLabel.textContent = 'Copy Link', 2000);
                        }
                    }).catch(() => fallbackCopy(url));
                } else {
                    fallbackCopy(url);
                }
            });
        }

        function fallbackCopy(text) {
            const temp = document.createElement('textarea');
            temp.value = text;
            temp.style.position = 'fixed';
            temp.style.top = '-9999px';
            temp.style.left = '-9999px';
            document.body.appendChild(temp);
            temp.focus();
            temp.select();
            try {
                document.execCommand('copy');
                toast('📋 Flyer ticket link copied!');
                if (copyLabel) {
                    copyLabel.textContent = 'Copied!';
                    setTimeout(() => copyLabel.textContent = 'Copy Link', 2000);
                }
            } catch (err) {}
            document.body.removeChild(temp);
        }

        refreshOrder();
    });
    </script>
</body>
</html>
