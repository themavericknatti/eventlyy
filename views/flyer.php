<?php
/**
 * Dedicated Event Flyer & Ticket Sales Page
 *
 * Immersive, ultra-modern luxury flyer experience showcasing the event poster
 * as the centerpiece with atmospheric backdrop lighting, interactive 3D tilt,
 * live urgency countdown, instant ticket selection, real-time order calculation,
 * and comprehensive zero-horizontal-overflow multi-device responsiveness.
 *
 * @package Eventlyy
 * @subpackage Views/Flyer
 * @author Celio Natti
 * @version 8.6.0
 */

$siteLogo = site_setting('site_logo');
$siteTitle = site_setting('site_title', 'Eventlyy');
$favicon = site_setting('site_favicon');

$eventImg = !empty($event->image) ? $event->image : 'https://images.unsplash.com/photo-1506157786151-b8491531f063?w=1600&q=85&auto=format&fit=crop';
$eventDate = strtotime($event->date ?? '');
$now = time();
$liveWindowEnd = $eventDate ? $eventDate + (event_completion_hours() * 3600) : 0;
$isLiveNow = !$isCompleted && ($eventDate && $now >= $eventDate && $now <= $liveWindowEnd);
$isToday = !$isCompleted && ($eventDate && date('Y-m-d', $now) === date('Y-m-d', $eventDate));
$isUpcoming = !$isCompleted && !$isLiveNow;

// Ticket sales urgency countdown (show when <= 24 hours until event starts and not yet started)
$salesCloseTimestamp = $eventDate ?: 0;
$hoursUntilClose = $salesCloseTimestamp ? ($salesCloseTimestamp - $now) / 3600 : 0;
$showCountdown = !$isCompleted && !$isLiveNow && $salesCloseTimestamp && $hoursUntilClose > 0 && $hoursUntilClose <= 24;

$rawDesc = $event->description ?? '';
$shortDesc = (mb_strlen($rawDesc) > 220) ? mb_strimwidth($rawDesc, 0, 220, '…') : $rawDesc;

$currencySymbol = get_currency_symbol();
$flyerUrl = event_flyer_url($event);
$eventUrl = event_url($event);
$eventSlug = !empty($event->custom_url) ? $event->custom_url : ($event->slug ?? $event->id);
$checkoutUrl = url('f/' . $eventSlug . '/checkout');

// Social Share links
$shareText = urlencode("🎟️ Grab tickets for " . ($event->name ?? 'this event') . " on " . $siteTitle . "!");
$encodedFlyerUrl = urlencode($flyerUrl);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, viewport-fit=cover">
    <title><?= e($event->name) ?> — Flyer &amp; Tickets | <?= e($siteTitle) ?></title>
    
    <!-- OpenGraph & Social Cards -->
    <meta property="og:type" content="website">
    <meta property="og:title" content="<?= e($event->name) ?> — Event Flyer">
    <meta property="og:description" content="<?= e($shortDesc) ?>">
    <meta property="og:image" content="<?= e($eventImg) ?>">
    <meta property="og:url" content="<?= e($flyerUrl) ?>">
    <meta property="og:site_name" content="<?= e($siteTitle) ?>">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= e($event->name) ?>">
    <meta name="twitter:description" content="<?= e($shortDesc) ?>">
    <meta name="twitter:image" content="<?= e($eventImg) ?>">

    <?php if ($favicon): ?>
        <link rel="icon" href="<?= e(asset($favicon)) ?>">
    <?php endif; ?>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&family=Space+Grotesk:wght@600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --flyer-brand: #6366f1;
            --flyer-brand-glow: rgba(99, 102, 241, 0.4);
            --flyer-accent: #10b981;
            --flyer-accent-glow: rgba(16, 185, 129, 0.35);
            --flyer-amber: #f59e0b;
            --flyer-amber-glow: rgba(245, 158, 11, 0.35);
            --flyer-bg: #030712;
            --flyer-card: rgba(15, 23, 42, 0.78);
            --flyer-card-solid: #0b1120;
            --flyer-border: rgba(255, 255, 255, 0.1);
            --flyer-border-hover: rgba(255, 255, 255, 0.22);
            --flyer-text-main: #ffffff;
            --flyer-text-muted: #94a3b8;
            --radius-xl: 26px;
            --radius-lg: 18px;
            --radius-md: 14px;
            --radius-sm: 10px;
        }

        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html {
            scroll-behavior: smooth;
            width: 100%;
            max-width: 100%;
            overflow-x: hidden;
            -webkit-text-size-adjust: 100%;
        }

        body {
            min-height: 100vh;
            width: 100%;
            max-width: 100%;
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: var(--flyer-bg);
            color: var(--flyer-text-main);
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            overflow-x: hidden;
            overflow-x: clip;
            position: relative;
        }

        /* Root Page Wrapper - Impermeable anti-overflow container */
        .flyer-page-wrapper {
            width: 100%;
            max-width: 100%;
            overflow-x: hidden;
            overflow-x: clip;
            position: relative;
            z-index: 10;
        }

        /* ─── 1. Immersive Ambient Atmospheric Lighting ─── */
        .flyer-ambient-stage {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            max-width: 100%;
            max-height: 100%;
            z-index: 0;
            overflow: hidden;
            contain: strict;
            clip-path: inset(0);
            pointer-events: none;
        }

        .flyer-ambient-halo {
            position: absolute;
            inset: -40px;
            background-image: url('<?= e($eventImg) ?>');
            background-size: cover;
            background-position: center;
            filter: blur(80px) brightness(0.24) saturate(220%);
            transform: scale(1.15);
            opacity: 0.85;
            transition: opacity 0.5s ease;
        }

        .flyer-ambient-orb-1 {
            position: absolute;
            top: 10%;
            left: 20%;
            width: 480px;
            height: 480px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(99, 102, 241, 0.18) 0%, transparent 70%);
            filter: blur(60px);
            animation: orbFloat1 18s ease-in-out infinite alternate;
        }

        .flyer-ambient-orb-2 {
            position: absolute;
            bottom: 15%;
            right: 15%;
            width: 520px;
            height: 520px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(16, 185, 129, 0.15) 0%, transparent 70%);
            filter: blur(70px);
            animation: orbFloat2 22s ease-in-out infinite alternate;
        }

        @keyframes orbFloat1 {
            0% { transform: translate(0, 0) scale(1); }
            100% { transform: translate(60px, 40px) scale(1.15); }
        }

        @keyframes orbFloat2 {
            0% { transform: translate(0, 0) scale(1); }
            100% { transform: translate(-50px, -60px) scale(1.1); }
        }

        .flyer-ambient-scrim {
            position: absolute;
            inset: 0;
            background: 
                radial-gradient(ellipse at 50% 15%, rgba(99, 102, 241, 0.08) 0%, transparent 60%),
                linear-gradient(180deg, 
                    rgba(3, 7, 18, 0.5) 0%, 
                    rgba(3, 7, 18, 0.82) 40%, 
                    rgba(3, 7, 18, 0.98) 100%
                );
        }

        /* ─── 2. Ultra-Sleek Glass Top Navigation Bar ─── */
        .flyer-nav-bar {
            position: sticky;
            top: 0;
            z-index: 60;
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
            max-width: 100%;
            padding: 0.85rem 2rem;
            background: rgba(8, 12, 24, 0.85);
            backdrop-filter: blur(28px) saturate(190%);
            -webkit-backdrop-filter: blur(28px) saturate(190%);
            border-bottom: 1px solid var(--flyer-border);
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.35);
            transition: all 0.3s ease;
        }

        .flyer-nav-left {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            min-width: 0;
            flex-shrink: 1;
        }

        .flyer-back-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: #f1f5f9;
            font-size: 0.84rem;
            font-weight: 700;
            text-decoration: none;
            padding: 0.52rem 1.05rem;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.07);
            border: 1px solid var(--flyer-border);
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
            white-space: nowrap;
            flex-shrink: 0;
        }

        .flyer-back-btn:hover {
            background: rgba(255, 255, 255, 0.16);
            color: #ffffff;
            border-color: rgba(255, 255, 255, 0.25);
            transform: translateX(-3px);
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.25);
        }

        .flyer-back-btn-arrow {
            font-size: 1rem;
            line-height: 1;
            transition: transform 0.2s ease;
        }

        .flyer-back-btn:hover .flyer-back-btn-arrow {
            transform: translateX(-2px);
        }

        .flyer-back-btn-text-full {
            display: inline;
        }

        .flyer-back-btn-text-short {
            display: none;
        }

        .flyer-nav-mode-badge {
            font-size: 0.72rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            padding: 0.3rem 0.75rem;
            border-radius: 999px;
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.18) 0%, rgba(16, 185, 129, 0.15) 100%);
            border: 1px solid rgba(99, 102, 241, 0.32);
            color: #c7d2fe;
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            white-space: nowrap;
        }

        .flyer-nav-right {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            flex-shrink: 0;
            min-width: 0;
        }

        .flyer-nav-jump-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: #ffffff;
            font-size: 0.82rem;
            font-weight: 800;
            text-decoration: none;
            padding: 0.48rem 1.1rem;
            border-radius: 999px;
            box-shadow: 0 4px 18px rgba(16, 185, 129, 0.35);
            transition: all 0.25s ease;
            white-space: nowrap;
            flex-shrink: 0;
        }

        .flyer-nav-jump-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 24px rgba(16, 185, 129, 0.5);
            filter: brightness(1.08);
        }

        /* Upgraded Premium Brand Emblem in Top Right */
        .flyer-brand-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.55rem;
            text-decoration: none;
            padding: 0.35rem 0.85rem;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.25s ease;
            white-space: nowrap;
            flex-shrink: 0;
        }

        .flyer-brand-badge:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: rgba(255, 255, 255, 0.25);
            transform: translateY(-1px);
        }

        .flyer-brand-logo-img {
            max-height: 24px;
            max-width: 130px;
            width: auto;
            object-fit: contain;
            display: block;
        }

        .flyer-brand-emblem {
            width: 24px;
            height: 24px;
            border-radius: 7px;
            background: linear-gradient(135deg, #10b981 0%, #6366f1 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.78rem;
            font-weight: 900;
            color: #ffffff;
            box-shadow: 0 2px 10px rgba(99, 102, 241, 0.35);
            flex-shrink: 0;
        }

        .flyer-brand-text {
            font-size: 0.88rem;
            font-weight: 900;
            letter-spacing: -0.01em;
            background: linear-gradient(135deg, #ffffff 0%, #cbd5e1 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .flyer-brand-verified {
            font-size: 0.65rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: #10b981;
            background: rgba(16, 185, 129, 0.14);
            border: 1px solid rgba(16, 185, 129, 0.3);
            padding: 0.12rem 0.45rem;
            border-radius: 6px;
            line-height: 1;
        }

        /* ─── 3. Main Stage Container & Grid ─── */
        .flyer-main-stage {
            position: relative;
            z-index: 10;
            max-width: 1240px;
            width: 100%;
            margin: 0 auto;
            padding: 2.25rem 2rem 5rem;
            box-sizing: border-box;
        }

        .flyer-grid {
            display: grid;
            grid-template-columns: 460px minmax(0, 1fr);
            gap: 2.5rem;
            align-items: start;
            width: 100%;
            max-width: 100%;
            min-width: 0;
        }

        /* ─── Left Column: The Poster Centerpiece ─── */
        .flyer-poster-column {
            position: sticky;
            top: 5.5rem;
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
            width: 100%;
            max-width: 100%;
            min-width: 0;
        }

        /* 3D Perspective Card */
        .flyer-poster-perspective {
            perspective: 1200px;
            width: 100%;
            max-width: 100%;
        }

        .flyer-poster-card {
            position: relative;
            border-radius: var(--radius-xl);
            overflow: hidden;
            background: var(--flyer-card-solid);
            border: 1px solid rgba(255, 255, 255, 0.14);
            box-shadow: 
                0 30px 80px -15px rgba(0, 0, 0, 0.9),
                0 0 50px -10px var(--flyer-brand-glow),
                0 0 1px 1px rgba(255, 255, 255, 0.15) inset;
            cursor: pointer;
            transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.4s ease, border-color 0.4s ease;
            transform-style: preserve-3d;
            width: 100%;
            max-width: 100%;
        }

        .flyer-poster-card:hover {
            border-color: rgba(99, 102, 241, 0.55);
            box-shadow: 
                0 36px 100px -15px rgba(0, 0, 0, 0.95),
                0 0 60px -5px var(--flyer-brand-glow),
                0 0 1px 1.5px rgba(255, 255, 255, 0.25) inset;
        }

        .flyer-poster-image-wrap {
            position: relative;
            width: 100%;
            max-width: 100%;
            height: 580px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            background: #060913;
        }

        .flyer-poster-blur {
            position: absolute;
            inset: -30px;
            background-size: cover;
            background-position: center;
            filter: blur(35px) brightness(0.4) saturate(160%);
            opacity: 0.8;
            transform: scale(1.2);
            pointer-events: none;
        }

        .flyer-poster-img {
            position: relative;
            z-index: 1;
            width: 100%;
            height: 100%;
            max-width: 100%;
            object-fit: contain;
            display: block;
            transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .flyer-poster-card:hover .flyer-poster-img {
            transform: scale(1.025);
        }

        /* Glass Poster Corner Badges */
        .flyer-poster-overlay-top {
            position: absolute;
            top: 1rem;
            left: 1rem;
            right: 1rem;
            max-width: calc(100% - 2rem);
            z-index: 3;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 0.5rem;
            pointer-events: none;
        }

        .flyer-poster-pill {
            font-size: 0.72rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            padding: 0.32rem 0.75rem;
            border-radius: 999px;
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            background: rgba(0, 0, 0, 0.65);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: #ffffff;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.4);
            pointer-events: auto;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 48%;
        }

        .flyer-poster-overlay-actions {
            position: absolute;
            bottom: 1rem;
            left: 1rem;
            right: 1rem;
            max-width: calc(100% - 2rem);
            z-index: 3;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 0.5rem;
            pointer-events: none;
        }

        .flyer-action-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.45rem 0.9rem;
            border-radius: 999px;
            background: rgba(8, 12, 24, 0.85);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            color: #ffffff;
            font-size: 0.76rem;
            font-weight: 800;
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 4px 18px rgba(0, 0, 0, 0.5);
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s ease;
            pointer-events: auto;
            white-space: nowrap;
            max-width: 48%;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .flyer-action-pill:hover {
            background: rgba(99, 102, 241, 0.9);
            border-color: rgba(255, 255, 255, 0.38);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(99, 102, 241, 0.45);
        }

        /* Quick Share Bar */
        .flyer-share-card {
            background: var(--flyer-card);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid var(--flyer-border);
            border-radius: var(--radius-lg);
            padding: 1.15rem 1.35rem;
            box-shadow: 0 10px 35px rgba(0, 0, 0, 0.35);
            width: 100%;
            max-width: 100%;
            box-sizing: border-box;
            overflow: hidden;
        }

        .flyer-share-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.75rem;
        }

        .flyer-share-label {
            font-size: 0.74rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--flyer-text-muted);
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }

        .flyer-share-btns {
            display: flex;
            align-items: center;
            gap: 0.55rem;
            flex-wrap: wrap;
            width: 100%;
        }

        .flyer-share-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.45rem;
            padding: 0.5rem 0.95rem;
            border-radius: var(--radius-sm);
            font-size: 0.8rem;
            font-weight: 700;
            text-decoration: none;
            color: #ffffff;
            border: 1px solid transparent;
            cursor: pointer;
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
            flex: 1 1 0;
            min-width: 0;
            text-align: center;
            white-space: nowrap;
        }

        .flyer-share-btn:hover {
            transform: translateY(-2px);
            filter: brightness(1.12);
        }

        .flyer-share-btn--copy {
            background: rgba(255, 255, 255, 0.08);
            border-color: rgba(255, 255, 255, 0.16);
            color: #f8fafc;
        }

        .flyer-share-btn--copy:hover {
            background: rgba(255, 255, 255, 0.18);
            border-color: rgba(255, 255, 255, 0.3);
        }

        .flyer-share-btn--whatsapp {
            background: #25D366;
            color: #ffffff;
        }

        .flyer-share-btn--twitter {
            background: #000000;
            border-color: rgba(255, 255, 255, 0.2);
            color: #ffffff;
        }

        /* ─── Right Column: Event Hub & Tickets Desk ─── */
        .flyer-content-column {
            display: flex;
            flex-direction: column;
            gap: 1.75rem;
            min-width: 0;
            width: 100%;
            max-width: 100%;
        }

        /* Status & Category Badges */
        .flyer-badges-row {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            flex-wrap: wrap;
            width: 100%;
        }

        .flyer-status-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            padding: 0.42rem 0.95rem;
            border-radius: 999px;
            font-size: 0.78rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            backdrop-filter: blur(16px);
            white-space: nowrap;
        }

        .flyer-status-badge--live {
            background: rgba(239, 68, 68, 0.92);
            color: #ffffff;
            box-shadow: 0 0 24px rgba(239, 68, 68, 0.7);
        }

        .flyer-pulse-ring {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #ffffff;
            position: relative;
        }

        .flyer-pulse-ring::after {
            content: '';
            position: absolute;
            inset: -4px;
            border-radius: 50%;
            border: 2px solid #ffffff;
            animation: ringPulse 1.6s cubic-bezier(0.24, 0, 0.38, 1) infinite;
        }

        @keyframes ringPulse {
            0% { transform: scale(0.6); opacity: 1; }
            100% { transform: scale(2.2); opacity: 0; }
        }

        .flyer-status-badge--today {
            background: rgba(16, 185, 129, 0.92);
            color: #ffffff;
            box-shadow: 0 0 20px rgba(16, 185, 129, 0.55);
        }

        .flyer-status-badge--upcoming {
            background: rgba(99, 102, 241, 0.22);
            color: #c7d2fe;
            border: 1px solid rgba(99, 102, 241, 0.45);
        }

        .flyer-status-badge--completed {
            background: rgba(100, 116, 139, 0.75);
            color: #ffffff;
        }

        .flyer-status-badge--category {
            background: rgba(255, 255, 255, 0.08);
            color: #f1f5f9;
            border: 1px solid rgba(255, 255, 255, 0.16);
        }

        .flyer-status-badge--recurring {
            background: rgba(147, 51, 234, 0.2);
            color: #d8b4fe;
            border: 1px solid rgba(147, 51, 234, 0.4);
        }

        /* Event Title */
        .flyer-title {
            font-size: clamp(2rem, 3.8vw, 3.3rem);
            font-weight: 900;
            line-height: 1.12;
            letter-spacing: -0.03em;
            color: #ffffff;
            text-shadow: 0 4px 30px rgba(0, 0, 0, 0.8);
            word-break: break-word;
            overflow-wrap: break-word;
            max-width: 100%;
        }

        /* ── Urgency Countdown Banner (Sales Closing) ── */
        .flyer-countdown-card {
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.15) 0%, rgba(217, 119, 6, 0.25) 100%);
            border: 1.5px solid rgba(245, 158, 11, 0.5);
            border-radius: var(--radius-lg);
            padding: 1.25rem 1.5rem;
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1.25rem;
            box-shadow: 0 0 35px rgba(245, 158, 11, 0.22);
            animation: countdownGlowPulse 3s ease-in-out infinite;
            width: 100%;
            max-width: 100%;
            min-width: 0;
            box-sizing: border-box;
            overflow: hidden;
        }

        @keyframes countdownGlowPulse {
            0%, 100% { box-shadow: 0 0 25px rgba(245, 158, 11, 0.18); }
            50% { box-shadow: 0 0 45px rgba(245, 158, 11, 0.4); }
        }

        .flyer-countdown-info {
            display: flex;
            align-items: center;
            gap: 0.85rem;
            min-width: 0;
            flex: 1;
        }

        .flyer-countdown-icon {
            font-size: 2rem;
            line-height: 1;
            flex-shrink: 0;
            animation: timerTock 2s infinite ease-in-out;
        }

        @keyframes timerTock {
            0%, 100% { transform: rotate(0deg); }
            50% { transform: rotate(15deg); }
        }

        .flyer-countdown-label {
            font-size: 0.74rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #fbbf24;
            display: block;
        }

        .flyer-countdown-sub {
            font-size: 0.88rem;
            font-weight: 700;
            color: #ffffff;
            margin-top: 2px;
            word-break: break-word;
        }

        .flyer-countdown-timer {
            display: flex;
            align-items: center;
            gap: 0.45rem;
            flex-shrink: 0;
        }

        .flyer-cd-unit {
            display: flex;
            flex-direction: column;
            align-items: center;
            background: rgba(0, 0, 0, 0.6);
            border: 1px solid rgba(245, 158, 11, 0.4);
            border-radius: 10px;
            padding: 0.35rem 0.65rem;
            min-width: 48px;
        }

        .flyer-cd-val {
            font-family: 'Space Grotesk', monospace;
            font-size: 1.3rem;
            font-weight: 800;
            color: #fef08a;
            line-height: 1.1;
        }

        .flyer-cd-lbl {
            font-size: 0.62rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #d97706;
        }

        .flyer-cd-sep {
            font-size: 1.15rem;
            font-weight: 800;
            color: #f59e0b;
        }

        /* ── Meta Chips Grid ── */
        .flyer-meta-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 0.85rem;
            width: 100%;
            max-width: 100%;
            min-width: 0;
        }

        .flyer-meta-card {
            background: var(--flyer-card);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid var(--flyer-border);
            border-radius: var(--radius-md);
            padding: 0.95rem 1.1rem;
            display: flex;
            align-items: center;
            gap: 0.85rem;
            transition: all 0.25s ease;
            min-width: 0;
            max-width: 100%;
            overflow: hidden;
            box-sizing: border-box;
        }

        .flyer-meta-card:hover {
            border-color: var(--flyer-border-hover);
            transform: translateY(-2px);
            background: rgba(20, 29, 52, 0.85);
        }

        .flyer-meta-icon {
            font-size: 1.4rem;
            flex-shrink: 0;
            line-height: 1;
        }

        .flyer-meta-text {
            min-width: 0;
            flex: 1;
            overflow: hidden;
        }

        .flyer-meta-label {
            display: block;
            font-size: 0.72rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: var(--flyer-text-muted);
            margin-bottom: 2px;
        }

        .flyer-meta-value {
            display: block;
            font-size: 0.88rem;
            font-weight: 700;
            color: #ffffff;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 100%;
        }

        /* About Event Card */
        .flyer-desc-card {
            background: var(--flyer-card);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid var(--flyer-border);
            border-radius: var(--radius-lg);
            padding: 1.25rem 1.45rem;
            width: 100%;
            max-width: 100%;
            min-width: 0;
            box-sizing: border-box;
            overflow: hidden;
        }

        .flyer-desc-card h3 {
            font-size: 0.95rem;
            font-weight: 800;
            color: #ffffff;
            margin-bottom: 0.45rem;
            display: flex;
            align-items: center;
            gap: 0.45rem;
        }

        .flyer-desc-text {
            font-size: 0.9rem;
            line-height: 1.6;
            color: #cbd5e1;
            word-break: break-word;
            overflow-wrap: break-word;
        }

        /* ── Ticket Selection Desk (Centerpiece of Action) ── */
        .flyer-tickets-desk {
            background: var(--flyer-card);
            backdrop-filter: blur(28px) saturate(190%);
            -webkit-backdrop-filter: blur(28px) saturate(190%);
            border: 1.5px solid var(--flyer-border);
            border-radius: var(--radius-xl);
            padding: 1.75rem 2rem;
            box-shadow: 
                0 25px 70px rgba(0, 0, 0, 0.65),
                0 0 1px 1px rgba(255, 255, 255, 0.12) inset;
            display: flex;
            flex-direction: column;
            gap: 1.35rem;
            width: 100%;
            max-width: 100%;
            min-width: 0;
            box-sizing: border-box;
            overflow: hidden;
        }

        .flyer-tickets-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 0.75rem;
            padding-bottom: 1.15rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            width: 100%;
            max-width: 100%;
        }

        .flyer-tickets-header h2 {
            font-size: 1.3rem;
            font-weight: 900;
            color: #ffffff;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .flyer-instant-badge {
            font-size: 0.75rem;
            font-weight: 800;
            padding: 0.3rem 0.75rem;
            border-radius: 999px;
            background: rgba(16, 185, 129, 0.15);
            border: 1px solid rgba(16, 185, 129, 0.35);
            color: #34d399;
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            white-space: nowrap;
        }

        /* Tier Rows */
        .flyer-tiers-list {
            display: flex;
            flex-direction: column;
            gap: 0.85rem;
            width: 100%;
            max-width: 100%;
            min-width: 0;
        }

        .flyer-tier-row {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto 130px;
            gap: 1.25rem;
            align-items: center;
            background: rgba(255, 255, 255, 0.035);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: var(--radius-md);
            padding: 1.15rem 1.35rem;
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
            width: 100%;
            max-width: 100%;
            min-width: 0;
            box-sizing: border-box;
            overflow: hidden;
        }

        .flyer-tier-row:hover:not(.flyer-tier-row--soldout) {
            background: rgba(255, 255, 255, 0.07);
            border-color: rgba(99, 102, 241, 0.45);
            transform: translateX(3px);
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.3);
        }

        .flyer-tier-row--soldout {
            opacity: 0.5;
            background: rgba(255, 255, 255, 0.02);
            cursor: not-allowed;
        }

        .flyer-tier-info {
            min-width: 0;
            width: 100%;
            overflow: hidden;
        }

        .flyer-tier-name-row {
            display: flex;
            align-items: center;
            gap: 0.55rem;
            flex-wrap: wrap;
            width: 100%;
        }

        .flyer-tier-name {
            font-size: 1.05rem;
            font-weight: 800;
            color: #ffffff;
            word-break: break-word;
            overflow-wrap: break-word;
        }

        .flyer-tier-tag {
            font-size: 0.7rem;
            font-weight: 800;
            padding: 0.15rem 0.55rem;
            border-radius: 6px;
            line-height: 1.2;
            white-space: nowrap;
        }

        .flyer-tier-tag--soldout {
            background: #ef4444;
            color: #ffffff;
        }

        .flyer-tier-tag--low {
            background: rgba(245, 158, 11, 0.15);
            color: #f59e0b;
            border: 1px solid rgba(245, 158, 11, 0.3);
        }

        .flyer-tier-tag--limit {
            background: rgba(99, 102, 241, 0.15);
            color: #a5b4fc;
            border: 1px solid rgba(99, 102, 241, 0.3);
        }

        .flyer-tier-desc {
            font-size: 0.82rem;
            color: var(--flyer-text-muted);
            margin-top: 0.25rem;
            line-height: 1.45;
            word-break: break-word;
            overflow-wrap: break-word;
        }

        /* Stepper */
        .flyer-stepper {
            display: flex;
            align-items: center;
            gap: 0.45rem;
            background: rgba(0, 0, 0, 0.55);
            border: 1px solid rgba(255, 255, 255, 0.16);
            border-radius: var(--radius-sm);
            padding: 0.25rem 0.45rem;
            flex-shrink: 0;
        }

        .flyer-stepper-btn {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.08);
            border: none;
            color: #ffffff;
            font-size: 1.25rem;
            font-weight: 800;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.15s ease;
            touch-action: manipulation;
            user-select: none;
        }

        .flyer-stepper-btn:hover:not(:disabled) {
            background: var(--flyer-brand);
            transform: scale(1.08);
        }

        .flyer-stepper-btn:disabled {
            opacity: 0.25;
            cursor: not-allowed;
        }

        .flyer-stepper-val {
            font-family: 'Space Grotesk', monospace;
            font-size: 1.05rem;
            font-weight: 800;
            width: 32px;
            text-align: center;
            color: #ffffff;
        }

        .flyer-tier-price {
            font-size: 1.25rem;
            font-weight: 900;
            color: #ffffff;
            white-space: nowrap;
            text-align: right;
            font-family: 'Space Grotesk', sans-serif;
            flex-shrink: 0;
        }

        .flyer-tier-price--free {
            color: #10b981;
        }

        /* Order Summary Box */
        .flyer-summary-box {
            background: rgba(0, 0, 0, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.14);
            border-radius: var(--radius-md);
            padding: 1.25rem 1.45rem;
            display: flex;
            flex-direction: column;
            gap: 0.85rem;
            width: 100%;
            max-width: 100%;
            box-sizing: border-box;
            overflow: hidden;
        }

        .flyer-summary-lines {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            min-height: 36px;
            width: 100%;
        }

        .flyer-summary-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.9rem;
            color: var(--flyer-text-muted);
            gap: 0.5rem;
            width: 100%;
        }

        .flyer-summary-row span {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            flex: 1;
        }

        .flyer-summary-row strong {
            color: #ffffff;
            font-family: 'Space Grotesk', sans-serif;
            font-weight: 700;
            white-space: nowrap;
            flex-shrink: 0;
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
            padding-top: 0.85rem;
            border-top: 1px dashed rgba(255, 255, 255, 0.16);
            gap: 0.5rem;
            width: 100%;
        }

        .flyer-summary-total span {
            font-size: 0.95rem;
            font-weight: 800;
            color: #ffffff;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .flyer-summary-total strong {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 1.6rem;
            font-weight: 900;
            color: #10b981;
            text-shadow: 0 0 25px rgba(16, 185, 129, 0.45);
            white-space: nowrap;
        }

        /* Primary Checkout Button */
        .flyer-checkout-cta {
            width: 100%;
            max-width: 100%;
            padding: 1.15rem 1.5rem;
            font-size: 1.05rem;
            font-weight: 900;
            letter-spacing: -0.01em;
            color: #ffffff;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            border: none;
            border-radius: var(--radius-md);
            cursor: pointer;
            box-shadow: 0 10px 35px rgba(16, 185, 129, 0.4), 0 0 1px 1px rgba(255, 255, 255, 0.25) inset;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.6rem;
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            touch-action: manipulation;
            box-sizing: border-box;
        }

        .flyer-checkout-cta:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 14px 45px rgba(16, 185, 129, 0.55), 0 0 1px 1px rgba(255, 255, 255, 0.4) inset;
            filter: brightness(1.08);
        }

        .flyer-checkout-cta:disabled {
            background: rgba(255, 255, 255, 0.08);
            color: var(--flyer-text-muted);
            box-shadow: none;
            cursor: not-allowed;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        /* Calendar Options */
        .flyer-calendar-block {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-top: 1.15rem;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            flex-wrap: wrap;
            gap: 0.75rem;
            width: 100%;
            max-width: 100%;
        }

        .flyer-calendar-label {
            font-size: 0.78rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: var(--flyer-text-muted);
        }

        .flyer-calendar-btns {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
            max-width: 100%;
        }

        .flyer-calendar-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.4rem 0.85rem;
            border-radius: var(--radius-sm);
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.15);
            color: #f1f5f9;
            font-size: 0.78rem;
            font-weight: 700;
            text-decoration: none;
            transition: all 0.2s ease;
            white-space: nowrap;
        }

        .flyer-calendar-pill:hover {
            background: rgba(99, 102, 241, 0.25);
            border-color: rgba(99, 102, 241, 0.5);
            color: #ffffff;
            transform: translateY(-1px);
        }

        /* ─── 4. Fullscreen Lightbox Modal ─── */
        .flyer-lightbox-modal {
            position: fixed;
            inset: 0;
            width: 100%;
            height: 100%;
            max-width: 100%;
            max-height: 100%;
            z-index: 10000;
            background: rgba(0, 0, 0, 0.94);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            animation: flyerFadeIn 0.25s ease-out;
            overflow: hidden;
            box-sizing: border-box;
        }

        @keyframes flyerFadeIn {
            from { opacity: 0; transform: scale(0.98); }
            to { opacity: 1; transform: scale(1); }
        }

        .flyer-lightbox-img {
            max-width: 90vw;
            max-height: 75vh;
            object-fit: contain;
            border-radius: var(--radius-md);
            box-shadow: 0 35px 90px rgba(0, 0, 0, 0.95);
        }

        .flyer-lightbox-toolbar {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-top: 1rem;
            max-width: 100%;
        }

        .flyer-lightbox-close {
            position: absolute;
            top: 1.25rem;
            right: 1.75rem;
            color: #ffffff;
            font-size: 2.5rem;
            font-weight: 700;
            cursor: pointer;
            line-height: 1;
            z-index: 10001;
            transition: color 0.2s, transform 0.2s;
            width: 44px;
            height: 44px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .flyer-lightbox-close:hover {
            color: #10b981;
            transform: scale(1.1);
        }

        /* ─── 5. Mobile Sticky Bottom Checkout Bar ─── */
        .flyer-mobile-bar {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            width: 100%;
            max-width: 100%;
            z-index: 55;
            background: rgba(8, 12, 24, 0.95);
            backdrop-filter: blur(24px) saturate(190%);
            -webkit-backdrop-filter: blur(24px) saturate(190%);
            border-top: 1px solid rgba(255, 255, 255, 0.15);
            padding: 0.75rem 1.25rem calc(0.75rem + env(safe-area-inset-bottom));
            display: none;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            box-shadow: 0 -10px 40px rgba(0, 0, 0, 0.65);
            transform: translateY(110%);
            transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            box-sizing: border-box;
            overflow: hidden;
        }

        .flyer-mobile-bar.active {
            transform: translateY(0);
        }

        .flyer-mobile-bar-info {
            display: flex;
            flex-direction: column;
            min-width: 0;
        }

        .flyer-mobile-bar-count {
            font-size: 0.74rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--flyer-text-muted);
        }

        .flyer-mobile-bar-total {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 1.35rem;
            font-weight: 900;
            color: #10b981;
            white-space: nowrap;
        }

        .flyer-mobile-bar-btn {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: #ffffff;
            font-size: 0.92rem;
            font-weight: 800;
            padding: 0.75rem 1.45rem;
            border-radius: 999px;
            border: none;
            cursor: pointer;
            box-shadow: 0 4px 18px rgba(16, 185, 129, 0.4);
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            white-space: nowrap;
            touch-action: manipulation;
            flex-shrink: 0;
        }

        /* ─── 6. Toast Notification ─── */
        #flyer-toast-box {
            position: fixed;
            bottom: 24px;
            right: 24px;
            z-index: 100000;
            display: flex;
            flex-direction: column;
            gap: 8px;
            pointer-events: none;
            max-width: calc(100vw - 48px);
        }

        .flyer-toast {
            background: rgba(15, 23, 42, 0.95);
            backdrop-filter: blur(16px);
            color: #ffffff;
            border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 0.85rem 1.35rem;
            border-radius: var(--radius-sm);
            font-size: 0.88rem;
            font-weight: 700;
            box-shadow: 0 16px 45px rgba(0, 0, 0, 0.75);
            animation: flyerToastPop 0.25s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            pointer-events: auto;
            word-break: break-word;
        }

        @keyframes flyerToastPop {
            from { transform: translateY(16px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        /* ══════════════════════════════════════════════════════════════
           7. Comprehensive Responsive Breakpoints (Zero Horizontal Overflow)
           ══════════════════════════════════════════════════════════════ */

        /* ── Large Tablet / Small Laptops (901px – 1080px) ── */
        @media (max-width: 1080px) and (min-width: 901px) {
            .flyer-grid {
                grid-template-columns: 380px minmax(0, 1fr);
                gap: 2rem;
            }
            .flyer-poster-image-wrap {
                height: 500px;
            }
            .flyer-meta-grid {
                grid-template-columns: repeat(3, minmax(0, 1fr));
                gap: 0.75rem;
            }
        }

        /* ── Tablet Portrait (<= 900px) ── */
        @media (max-width: 900px) {
            .flyer-main-stage {
                padding: 1.75rem 1.25rem 5.5rem;
            }
            .flyer-grid {
                grid-template-columns: minmax(0, 1fr);
                gap: 2rem;
            }
            .flyer-poster-column {
                position: static;
                max-width: 520px;
                margin: 0 auto;
                width: 100%;
            }
            .flyer-poster-perspective {
                perspective: none !important;
            }
            .flyer-poster-card {
                transform-style: flat !important;
                transform: none !important;
            }
            .flyer-poster-blur {
                inset: 0 !important;
                transform: none !important;
                filter: blur(25px) brightness(0.35) !important;
            }
            .flyer-poster-image-wrap {
                height: clamp(360px, 55vw, 480px);
            }
            .flyer-meta-grid {
                grid-template-columns: repeat(3, minmax(0, 1fr));
                gap: 0.75rem;
            }
            .flyer-mobile-bar {
                display: flex;
            }
        }

        /* ── Phablet & Mobile Landscape (<= 768px) ── */
        @media (max-width: 768px) {
            .flyer-ambient-orb-1,
            .flyer-ambient-orb-2 {
                display: none !important; /* Eliminate heavy blurred orbs to stop overflow & save mobile GPU */
            }
            .flyer-nav-bar {
                padding: 0.75rem 1rem;
            }
            .flyer-meta-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 0.65rem;
            }
            .flyer-meta-card:nth-child(3) {
                grid-column: 1 / -1;
            }
        }

        /* ── Standard Mobile (<= 680px) ── */
        @media (max-width: 680px) {
            .flyer-nav-bar {
                padding: 0.65rem 0.85rem;
                gap: 0.5rem;
            }
            .flyer-nav-mode-badge {
                display: none !important;
            }
            .flyer-brand-text,
            .flyer-brand-verified {
                display: none !important;
            }
            .flyer-brand-logo-img {
                max-height: 20px;
                max-width: 85px;
            }
            .flyer-main-stage {
                padding: 1.25rem 0.85rem 6rem;
            }
            .flyer-title {
                font-size: 2rem;
            }
            .flyer-poster-image-wrap {
                height: clamp(300px, 66vw, 390px);
            }
            .flyer-share-card {
                padding: 1rem;
            }
            .flyer-share-btns {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 0.5rem;
            }
            .flyer-share-btn--copy {
                grid-column: 1 / -1;
                width: 100%;
            }
            .flyer-countdown-card {
                flex-direction: column;
                align-items: stretch;
                gap: 0.85rem;
                padding: 1rem 1.15rem;
            }
            .flyer-countdown-timer {
                width: 100%;
                justify-content: center;
            }
            .flyer-tickets-desk {
                padding: 1.25rem 1rem;
                border-radius: var(--radius-lg);
            }
            .flyer-tier-row {
                grid-template-columns: minmax(0, 1fr) auto;
                gap: 0.85rem 0.75rem;
                padding: 1rem;
            }
            .flyer-tier-info {
                grid-column: 1 / -1;
            }
            .flyer-stepper {
                grid-column: 1 / 2;
                justify-self: start;
            }
            .flyer-tier-price {
                grid-column: 2 / 3;
                justify-self: end;
                text-align: right;
                font-size: 1.15rem;
            }
            .flyer-calendar-block {
                flex-direction: column;
                align-items: stretch;
                gap: 0.75rem;
            }
            .flyer-calendar-btns {
                display: grid;
                grid-template-columns: repeat(3, minmax(0, 1fr));
                gap: 0.45rem;
                width: 100%;
            }
            .flyer-calendar-pill {
                padding: 0.45rem 0.35rem;
                font-size: 0.75rem;
                text-align: center;
                justify-content: center;
                min-width: 0;
            }
        }

        /* ── Phones (<= 480px) ── */
        @media (max-width: 480px) {
            .flyer-nav-bar {
                padding: 0.6rem 0.75rem;
                gap: 0.4rem;
            }
            .flyer-back-btn {
                padding: 0.42rem 0.75rem;
                font-size: 0.78rem;
                gap: 0.35rem;
            }
            .flyer-back-btn-text-full {
                display: none !important;
            }
            .flyer-back-btn-text-short {
                display: inline !important;
            }
            .flyer-nav-jump-btn {
                display: none !important; /* Eliminates header collision so header never expands past 100vw */
            }
            .flyer-brand-badge {
                padding: 0.3rem 0.5rem;
                gap: 0.4rem;
            }
            .flyer-main-stage {
                padding: 1rem 0.75rem 6.5rem;
            }
            .flyer-title {
                font-size: 1.7rem;
                line-height: 1.18;
            }
            .flyer-poster-image-wrap {
                height: clamp(270px, 74vw, 340px);
            }
            .flyer-poster-overlay-top,
            .flyer-poster-overlay-actions {
                top: 0.75rem;
                left: 0.75rem;
                right: 0.75rem;
                bottom: 0.75rem;
                max-width: calc(100% - 1.5rem);
            }
            .flyer-action-pill {
                padding: 0.38rem 0.7rem;
                font-size: 0.72rem;
            }
            .flyer-meta-grid {
                grid-template-columns: minmax(0, 1fr);
                gap: 0.55rem;
            }
            .flyer-meta-card {
                padding: 0.8rem 0.95rem;
                gap: 0.7rem;
            }
            .flyer-meta-icon {
                font-size: 1.25rem;
            }
            .flyer-cd-unit {
                min-width: 42px;
                padding: 0.3rem 0.45rem;
            }
            .flyer-cd-val {
                font-size: 1.15rem;
            }
            .flyer-tickets-desk {
                padding: 1.1rem 0.75rem;
            }
            .flyer-tier-row {
                padding: 0.85rem 0.75rem;
            }
            .flyer-tier-name {
                font-size: 0.96rem;
            }
            .flyer-stepper-btn {
                width: 32px;
                height: 32px;
            }
            .flyer-stepper-val {
                width: 26px;
                font-size: 0.95rem;
            }
            .flyer-summary-box {
                padding: 0.95rem 0.85rem;
            }
            .flyer-summary-total strong {
                font-size: 1.35rem;
            }
            .flyer-checkout-cta {
                padding: 0.95rem 1rem;
                font-size: 0.95rem;
            }
            .flyer-mobile-bar {
                padding: 0.65rem 0.85rem calc(0.65rem + env(safe-area-inset-bottom));
            }
            .flyer-mobile-bar-total {
                font-size: 1.2rem;
            }
            .flyer-mobile-bar-btn {
                padding: 0.65rem 1.1rem;
                font-size: 0.85rem;
            }
        }

        /* ── Compact Phones (<= 360px: iPhone SE, Galaxy Z Fold Cover) ── */
        @media (max-width: 360px) {
            .flyer-title {
                font-size: 1.5rem;
            }
            .flyer-poster-image-wrap {
                height: 250px;
            }
            .flyer-action-pill span:last-child {
                display: none;
            }
            .flyer-action-pill {
                padding: 0.35rem 0.55rem;
            }
            .flyer-cd-unit {
                min-width: 36px;
                padding: 0.25rem 0.3rem;
            }
            .flyer-cd-val {
                font-size: 1rem;
            }
            .flyer-cd-lbl {
                font-size: 0.56rem;
            }
            .flyer-calendar-btns {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

    <!-- 1. Ambient Background Stage (Clipped to Viewport) -->
    <div class="flyer-ambient-stage" aria-hidden="true">
        <div class="flyer-ambient-halo"></div>
        <div class="flyer-ambient-orb-1"></div>
        <div class="flyer-ambient-orb-2"></div>
        <div class="flyer-ambient-scrim"></div>
    </div>

    <!-- Root Impermeable Page Wrapper -->
    <div class="flyer-page-wrapper">

        <!-- 2. Sticky Glass Top Navigation -->
        <header class="flyer-nav-bar">
            <div class="flyer-nav-left">
                <a href="<?= e($eventUrl) ?>" class="flyer-back-btn" title="View standard event details page">
                    <span class="flyer-back-btn-arrow">←</span>
                    <span class="flyer-back-btn-text-full">Full Details</span>
                    <span class="flyer-back-btn-text-short">Back</span>
                </a>
                <span class="flyer-nav-mode-badge">
                    <span>🎨</span> Flyer Mode
                </span>
            </div>

            <div class="flyer-nav-right">
                <!-- Quick jump to tickets -->
                <a href="#tickets-section" class="flyer-nav-jump-btn" title="Jump directly to ticket selection">
                    <span>🎟️</span> <span>Get Tickets</span>
                </a>

                <!-- Upgraded Premium Brand Emblem & Logo -->
                <a href="<?= url('/') ?>" class="flyer-brand-badge" title="<?= e($siteTitle) ?> Home">
                    <?php if (!empty($siteLogo)): ?>
                        <img src="<?= e(asset($siteLogo)) ?>" alt="<?= e($siteTitle) ?>" class="flyer-brand-logo-img">
                    <?php else: ?>
                        <span class="flyer-brand-emblem">⚡</span>
                        <span class="flyer-brand-text"><?= e($siteTitle) ?></span>
                    <?php endif; ?>
                    <span class="flyer-brand-verified">✓</span>
                </a>
            </div>
        </header>

        <!-- 3. Main Stage -->
        <main class="flyer-main-stage">
            <div class="flyer-grid">
                
                <!-- Left Column: The Poster Centerpiece -->
                <div class="flyer-poster-column">
                    <div class="flyer-poster-perspective">
                        <div class="flyer-poster-card" id="flyer-poster-trigger" title="Click to inspect full flyer poster">
                            <!-- Poster Top Badges -->
                            <div class="flyer-poster-overlay-top">
                                <?php if (!empty($event->category)): ?>
                                    <span class="flyer-poster-pill"><?= e($event->category) ?></span>
                                <?php else: ?>
                                    <span class="flyer-poster-pill">Official Flyer</span>
                                <?php endif; ?>
                                <?php if (!empty($event->is_recurring)): ?>
                                    <span class="flyer-poster-pill" style="background:rgba(99, 102, 241, 0.75);">🔄 Series</span>
                                <?php endif; ?>
                            </div>

                            <!-- Main Poster Display -->
                            <div class="flyer-poster-image-wrap">
                                <div class="flyer-poster-blur" style="background-image: url('<?= e($eventImg) ?>');"></div>
                                <img src="<?= e($eventImg) ?>" alt="<?= e($event->name) ?> Poster" class="flyer-poster-img" id="flyer-main-img" loading="eager">
                            </div>

                            <!-- Poster Action Pills -->
                            <div class="flyer-poster-overlay-actions">
                                <button type="button" class="flyer-action-pill" id="btn-zoom-poster" title="View fullscreen poster">
                                    <span>🔍</span> <span>Tap to Zoom</span>
                                </button>
                                <a href="<?= e($eventImg) ?>" download="<?= e($event->name) ?>_Flyer" target="_blank" rel="noopener" class="flyer-action-pill" title="Save / Download flyer poster">
                                    <span>⬇️</span> <span>Save Poster</span>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Share Bar -->
                    <div class="flyer-share-card">
                        <div class="flyer-share-header">
                            <span class="flyer-share-label">
                                <span>🚀</span> Share This Event
                            </span>
                        </div>
                        <div class="flyer-share-btns">
                            <button type="button" class="flyer-share-btn flyer-share-btn--copy" id="btn-copy-link" data-url="<?= e($flyerUrl) ?>" title="Copy flyer ticket link">
                                <span>🔗</span> <span id="copy-btn-label">Copy Link</span>
                            </button>
                            <a href="https://api.whatsapp.com/send?text=<?= $shareText ?>%20<?= $encodedFlyerUrl ?>" target="_blank" rel="noopener" class="flyer-share-btn flyer-share-btn--whatsapp" title="Share on WhatsApp">
                                <svg width="13" height="13" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                                <span>WhatsApp</span>
                            </a>
                            <a href="https://twitter.com/intent/tweet?text=<?= $shareText ?>&url=<?= $encodedFlyerUrl ?>" target="_blank" rel="noopener" class="flyer-share-btn flyer-share-btn--twitter" title="Share on X">
                                <svg width="12" height="12" fill="currentColor" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                                <span>X</span>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Event Info & Ticket Selection -->
                <div class="flyer-content-column">
                    
                    <!-- Status Badges Row -->
                    <div class="flyer-badges-row">
                        <?php if ($isCompleted): ?>
                            <span class="flyer-status-badge flyer-status-badge--completed">🏁 Concluded</span>
                        <?php elseif ($isLiveNow): ?>
                            <span class="flyer-status-badge flyer-status-badge--live">
                                <span class="flyer-pulse-ring"></span> Live Gate Sales Open
                            </span>
                        <?php elseif ($isToday): ?>
                            <span class="flyer-status-badge flyer-status-badge--today">⚡ Happening Today</span>
                        <?php else: ?>
                            <span class="flyer-status-badge flyer-status-badge--upcoming">📅 Upcoming Event</span>
                        <?php endif; ?>

                        <?php if (!empty($event->category)): ?>
                            <span class="flyer-status-badge flyer-status-badge--category"><?= e($event->category) ?></span>
                        <?php endif; ?>

                        <?php if (!empty($event->is_recurring)): ?>
                            <span class="flyer-status-badge flyer-status-badge--recurring">🔄 Recurring Event</span>
                        <?php endif; ?>
                    </div>

                    <!-- Event Title -->
                    <h1 class="flyer-title"><?= e($event->name) ?></h1>

                    <!-- Live Urgency Countdown Banner (Sales Closing) -->
                    <?php if ($showCountdown): ?>
                        <div class="flyer-countdown-card" id="flyer-countdown-card" data-close-timestamp="<?= $salesCloseTimestamp ?>">
                            <div class="flyer-countdown-info">
                                <span class="flyer-countdown-icon">⏳</span>
                                <div>
                                    <span class="flyer-countdown-label">Online Ticket Sales Closing Soon</span>
                                    <div class="flyer-countdown-sub">Grab your tickets before sales end!</div>
                                </div>
                            </div>

                            <div class="flyer-countdown-timer">
                                <div class="flyer-cd-unit">
                                    <span class="flyer-cd-val" id="fcd-hours">--</span>
                                    <span class="flyer-cd-lbl">Hours</span>
                                </div>
                                <span class="flyer-cd-sep">:</span>
                                <div class="flyer-cd-unit">
                                    <span class="flyer-cd-val" id="fcd-mins">--</span>
                                    <span class="flyer-cd-lbl">Mins</span>
                                </div>
                                <span class="flyer-cd-sep">:</span>
                                <div class="flyer-cd-unit">
                                    <span class="flyer-cd-val" id="fcd-secs">--</span>
                                    <span class="flyer-cd-lbl">Secs</span>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Meta Details Grid -->
                    <div class="flyer-meta-grid">
                        <div class="flyer-meta-card">
                            <span class="flyer-meta-icon">🗓️</span>
                            <div class="flyer-meta-text">
                                <span class="flyer-meta-label">Date &amp; Time</span>
                                <span class="flyer-meta-value"><?= $eventDate ? date('M d, Y • g:i A', $eventDate) : 'Date TBA' ?></span>
                            </div>
                        </div>
                        <div class="flyer-meta-card">
                            <span class="flyer-meta-icon">📍</span>
                            <div class="flyer-meta-text">
                                <span class="flyer-meta-label">Venue / Location</span>
                                <span class="flyer-meta-value" title="<?= e($event->location ?? 'Venue TBA') ?>">
                                    <?= e($event->location ?? 'Venue TBA') ?>
                                </span>
                            </div>
                        </div>
                        <?php if (!empty($event->organizer_name)): ?>
                            <div class="flyer-meta-card">
                                <span class="flyer-meta-icon">🎙️</span>
                                <div class="flyer-meta-text">
                                    <span class="flyer-meta-label">Host / Organizer</span>
                                    <span class="flyer-meta-value" title="<?= e($event->organizer_name) ?>">
                                        <?= e($event->organizer_name) ?>
                                    </span>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Short Description Card -->
                    <?php if (!empty($event->description)): ?>
                        <div class="flyer-desc-card">
                            <h3><span>ℹ️</span> About This Event</h3>
                            <p class="flyer-desc-text"><?= nl2br(e($shortDesc)) ?></p>
                        </div>
                    <?php endif; ?>

                    <!-- Ticket Selection Desk -->
                    <form method="GET" action="<?= e($checkoutUrl) ?>" id="flyer-tickets-form">
                        <input type="hidden" name="occ_id" value="<?= (int)($event->id ?? 0) ?>">
                        <?php if (!empty($event->recurrence_sequence)): ?>
                            <input type="hidden" name="occ" value="<?= (int)$event->recurrence_sequence ?>">
                        <?php endif; ?>
                        <?php if (!empty($refCode)): ?>
                            <input type="hidden" name="ref" value="<?= e($refCode) ?>">
                        <?php endif; ?>

                        <section class="flyer-tickets-desk" id="tickets-section">
                            <div class="flyer-tickets-header">
                                <h2><span>🎟️</span> Select Your Tickets</h2>
                                <span class="flyer-instant-badge">⚡ Instant QR Delivery</span>
                            </div>

                            <?php if ($isCompleted): ?>
                                <div style="text-align:center; padding:3rem 1.5rem;">
                                    <span style="font-size:3rem; display:block; margin-bottom:0.6rem;">🏁</span>
                                    <strong style="font-size:1.35rem; color:#ffffff; display:block; margin-bottom:0.35rem;">Ticket Sales Closed</strong>
                                    <p style="font-size:0.92rem; color:var(--flyer-text-muted);">This event has concluded. Thank you for your interest!</p>
                                </div>
                            <?php elseif (empty($ticketTypes)): ?>
                                <div style="text-align:center; padding:3rem 1.5rem; color:var(--flyer-text-muted);">
                                    <span style="font-size:2.5rem; display:block; margin-bottom:0.5rem;">🎫</span>
                                    <p>No ticket tiers are currently available for this event.</p>
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
                                                        <span class="flyer-tier-tag flyer-tier-tag--low">🔥 <?= $remaining ?> left</span>
                                                    <?php endif; ?>
                                                    <?php if (!empty($tt['max_per_person'])): ?>
                                                        <span class="flyer-tier-tag flyer-tier-tag--limit">🔒 Max <?= (int)$tt['max_per_person'] ?>/person</span>
                                                    <?php endif; ?>
                                                </div>
                                                <?php if (!empty($tt['description'])): ?>
                                                    <p class="flyer-tier-desc"><?= e($tt['description']) ?></p>
                                                <?php endif; ?>
                                            </div>

                                            <div class="flyer-stepper">
                                                <button type="button" class="flyer-stepper-btn" data-action="dec" <?= $isSoldOut ? 'disabled' : '' ?> aria-label="Decrease quantity">−</button>
                                                <span class="flyer-stepper-val" aria-live="polite">0</span>
                                                <input type="hidden" name="qty[<?= $tt['id'] ?>]" value="0" class="flyer-qty-input" data-max="<?= $isSoldOut ? 0 : ($remaining > 0 ? $remaining : 20) ?>" data-max-per-person="<?= !empty($tt['max_per_person']) ? (int)$tt['max_per_person'] : '' ?>">
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
                                    <span>🎟️</span> <span id="cta-btn-text">Select Tickets to Continue</span>
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

    </div><!-- /.flyer-page-wrapper -->

    <!-- Mobile Sticky Floating Checkout Drawer (Appears when tickets selected) -->
    <div class="flyer-mobile-bar" id="flyer-mobile-bar">
        <div class="flyer-mobile-bar-info">
            <span class="flyer-mobile-bar-count" id="fmb-count">0 Tickets</span>
            <span class="flyer-mobile-bar-total" id="fmb-total">Free</span>
        </div>
        <button type="button" class="flyer-mobile-bar-btn" id="fmb-btn">
            <span>Checkout</span> <span>→</span>
        </button>
    </div>

    <!-- Fullscreen Poster Lightbox -->
    <div id="flyer-lightbox-modal" class="flyer-lightbox-modal" style="display:none;" role="dialog" aria-modal="true" aria-label="Flyer Poster Lightbox">
        <span class="flyer-lightbox-close" id="flyer-lightbox-close" aria-label="Close poster viewer">&times;</span>
        <img class="flyer-lightbox-img" id="flyer-lightbox-img" src="<?= e($eventImg) ?>" alt="<?= e($event->name) ?> Full Poster">
        <div class="flyer-lightbox-toolbar">
            <a href="<?= e($eventImg) ?>" download="<?= e($event->name) ?>_Flyer" target="_blank" rel="noopener" class="flyer-action-pill">
                <span>⬇️</span> Download Full High-Res Flyer
            </a>
        </div>
    </div>

    <!-- Toast Notification Container -->
    <div id="flyer-toast-box"></div>

    <!-- Interactive Logic: 3D Tilt, Live Calculation, Lightbox, Countdown -->
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const tierRows = document.querySelectorAll('.flyer-tier-row');
        const summaryLines = document.getElementById('flyer-summary-lines');
        const totalVal = document.getElementById('flyer-total-val');
        const submitBtn = document.getElementById('btn-proceed-order');
        const ctaBtnText = document.getElementById('cta-btn-text');
        const mobileBar = document.getElementById('flyer-mobile-bar');
        const fmbCount = document.getElementById('fmb-count');
        const fmbTotal = document.getElementById('fmb-total');
        const fmbBtn = document.getElementById('fmb-btn');
        const flyerForm = document.getElementById('flyer-tickets-form');
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

            const formattedTotal = (total > 0) ? formatPrice(total) : 'Free';

            if (totalCount === 0) {
                if (summaryLines) summaryLines.innerHTML = '<p class="flyer-summary-empty">Select tickets above to proceed.</p>';
                if (totalVal) totalVal.textContent = 'Free';
                if (submitBtn) {
                    submitBtn.disabled = true;
                    if (ctaBtnText) ctaBtnText.textContent = 'Select Tickets to Continue';
                }
                if (mobileBar) mobileBar.classList.remove('active');
            } else {
                if (summaryLines) summaryLines.innerHTML = linesHtml;
                if (totalVal) totalVal.textContent = formattedTotal;
                if (submitBtn) {
                    submitBtn.disabled = false;
                    if (ctaBtnText) ctaBtnText.textContent = `Checkout (${totalCount}) • ${formattedTotal}`;
                }
                if (mobileBar) {
                    if (fmbCount) fmbCount.textContent = `${totalCount} Ticket${totalCount > 1 ? 's' : ''}`;
                    if (fmbTotal) fmbTotal.textContent = formattedTotal;
                    mobileBar.classList.add('active');
                }
            }
        }

        // Stepper micro-interactions
        tierRows.forEach(row => {
            const decBtn = row.querySelector('[data-action="dec"]');
            const incBtn = row.querySelector('[data-action="inc"]');
            const numSpan = row.querySelector('.flyer-stepper-val');
            const input = row.querySelector('.flyer-qty-input');
            const max = parseInt(input ? input.getAttribute('data-max') : 20, 10) || 20;
            const maxPP = input ? input.getAttribute('data-max-per-person') : '';
            const maxPerPerson = maxPP ? parseInt(maxPP, 10) : null;
            const effectiveMax = (maxPerPerson && maxPerPerson > 0) ? Math.min(max, maxPerPerson) : max;

            if (decBtn && incBtn && numSpan && input) {
                decBtn.addEventListener('click', function (e) {
                    e.preventDefault();
                    let cur = parseInt(input.value, 10) || 0;
                    if (cur > 0) {
                        cur--;
                        input.value = cur;
                        numSpan.textContent = cur;
                        refreshOrder();
                    }
                });

                incBtn.addEventListener('click', function (e) {
                    e.preventDefault();
                    let cur = parseInt(input.value, 10) || 0;
                    if (cur < effectiveMax) {
                        cur++;
                        input.value = cur;
                        numSpan.textContent = cur;
                        refreshOrder();
                    } else if (maxPerPerson && maxPerPerson > 0 && cur >= maxPerPerson) {
                        toast('Limit reached: Maximum ' + maxPerPerson + ' ticket(s) per person for this tier.');
                    }
                });
            }
        });

        // Mobile drawer submit trigger
        if (fmbBtn && flyerForm) {
            fmbBtn.addEventListener('click', function () {
                flyerForm.submit();
            });
        }

        /* ─── Interactive 3D Perspective Tilt for Poster (Desktop Only) ─── */
        const posterCard = document.querySelector('.flyer-poster-card');
        if (posterCard && window.matchMedia('(min-width: 901px)').matches) {
            posterCard.addEventListener('mousemove', function (e) {
                const rect = posterCard.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const y = e.clientY - rect.top;
                const centerX = rect.width / 2;
                const centerY = rect.height / 2;
                const rotateX = ((y - centerY) / centerY) * -6;
                const rotateY = ((x - centerX) / centerX) * 6;

                posterCard.style.transform = `rotateX(${rotateX.toFixed(2)}deg) rotateY(${rotateY.toFixed(2)}deg) translateY(-4px)`;
            });

            posterCard.addEventListener('mouseleave', function () {
                posterCard.style.transform = 'rotateX(0deg) rotateY(0deg) translateY(0)';
            });
        }

        /* ─── Poster Lightbox ─── */
        const posterTrigger = document.getElementById('flyer-poster-trigger');
        const zoomBtn = document.getElementById('btn-zoom-poster');
        const lightboxModal = document.getElementById('flyer-lightbox-modal');
        const lightboxClose = document.getElementById('flyer-lightbox-close');

        function openLightbox() {
            if (!lightboxModal) return;
            lightboxModal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        function closeLightbox() {
            if (!lightboxModal) return;
            lightboxModal.style.display = 'none';
            document.body.style.overflow = '';
        }

        if (posterTrigger) posterTrigger.addEventListener('click', openLightbox);
        if (zoomBtn) zoomBtn.addEventListener('click', function(e) { e.stopPropagation(); openLightbox(); });
        if (lightboxClose) lightboxClose.addEventListener('click', closeLightbox);
        if (lightboxModal) {
            lightboxModal.addEventListener('click', function (e) {
                if (e.target === lightboxModal) closeLightbox();
            });
        }

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && lightboxModal && lightboxModal.style.display === 'flex') {
                closeLightbox();
            }
        });

        /* ─── Live Sales Closing Countdown Timer ─── */
        const countdownCard = document.getElementById('flyer-countdown-card');
        if (countdownCard) {
            const closeTimestamp = parseInt(countdownCard.getAttribute('data-close-timestamp'), 10) * 1000;
            const hoursEl = document.getElementById('fcd-hours');
            const minsEl = document.getElementById('fcd-mins');
            const secsEl = document.getElementById('fcd-secs');

            function updateCountdown() {
                const nowMs = Date.now();
                const diff = closeTimestamp - nowMs;

                if (diff <= 0) {
                    if (hoursEl) hoursEl.textContent = '00';
                    if (minsEl) minsEl.textContent = '00';
                    if (secsEl) secsEl.textContent = '00';
                    return;
                }

                const totalSecs = Math.floor(diff / 1000);
                const hours = Math.floor(totalSecs / 3600);
                const mins = Math.floor((totalSecs % 3600) / 60);
                const secs = totalSecs % 60;

                if (hoursEl) hoursEl.textContent = String(hours).padStart(2, '0');
                if (minsEl) minsEl.textContent = String(mins).padStart(2, '0');
                if (secsEl) secsEl.textContent = String(secs).padStart(2, '0');
            }

            updateCountdown();
            setInterval(updateCountdown, 1000);
        }

        /* ─── Toast Notifications ─── */
        function toast(msg) {
            const box = document.getElementById('flyer-toast-box');
            if (!box) return;
            const el = document.createElement('div');
            el.className = 'flyer-toast';
            el.innerHTML = `<span>✨</span> <span>${msg}</span>`;
            box.appendChild(el);
            setTimeout(() => {
                el.style.opacity = '0';
                el.style.transform = 'translateY(10px)';
                el.style.transition = 'all 0.3s ease';
                setTimeout(() => el.remove(), 300);
            }, 3000);
        }

        /* ─── Share Button Copy Link ─── */
        const copyBtn = document.getElementById('btn-copy-link');
        const copyLabel = document.getElementById('copy-btn-label');
        if (copyBtn) {
            copyBtn.addEventListener('click', function () {
                const url = copyBtn.getAttribute('data-url') || window.location.href;
                if (navigator.clipboard && window.isSecureContext) {
                    navigator.clipboard.writeText(url).then(() => {
                        toast('Flyer link copied to clipboard!');
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
                toast('Flyer link copied!');
                if (copyLabel) {
                    copyLabel.textContent = 'Copied!';
                    setTimeout(() => copyLabel.textContent = 'Copy Link', 2000);
                }
            } catch (err) {}
            document.body.removeChild(temp);
        }

        // Initialize state
        refreshOrder();
    });
    </script>
</body>
</html>
