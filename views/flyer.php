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
$absEventImg = str_starts_with($eventImg, 'http') ? $eventImg : url($eventImg);
$absSiteLogo = !empty($siteLogo) ? (str_starts_with($siteLogo, 'http') ? $siteLogo : url($siteLogo)) : '';
$waShareText = urlencode("🎟️ *" . ($event->name ?? 'this event') . "* on " . $siteTitle . "\n📅 " . (!empty($event->date) ? date('M j, Y · g:i A', strtotime($event->date)) : 'TBA') . "\n📍 " . ($event->location ?? 'TBA') . "\n\n🔗 Get tickets:");
$tweetText = urlencode("Check out " . ($event->name ?? 'this event') . " on @" . str_replace(' ', '', $siteTitle) . " 🎟️");
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
    <meta property="og:image" content="<?= e($absEventImg) ?>">
    <meta property="og:image:secure_url" content="<?= e($absEventImg) ?>">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:alt" content="<?= e($event->name) ?> event poster">
    <meta property="og:url" content="<?= e($flyerUrl) ?>">
    <meta property="og:site_name" content="<?= e($siteTitle) ?>">
    <?php if (!empty($absSiteLogo)): ?>
    <meta property="og:logo" content="<?= e($absSiteLogo) ?>">
    <?php endif; ?>
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= e($event->name) ?>">
    <meta name="twitter:description" content="<?= e($shortDesc) ?>">
    <meta name="twitter:image" content="<?= e($absEventImg) ?>">
    <meta name="twitter:image:alt" content="<?= e($event->name) ?> event poster">

    <?= site_favicon_tags() ?>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&family=Space+Grotesk:wght@600;700&display=swap" rel="stylesheet">

    <?php $flyerVer = asset_version('assets/css/flyer.css'); ?>
    <link rel="stylesheet" href="{{ route('assets/css/flyer.css') }}?v=<?= $flyerVer ?>">

    <?php
    /* ── Admin-Configurable Theme Color Injection ──
       Override the CSS custom properties so flyer pages dynamically adopt
       whatever primary/secondary colors the admin sets in Settings. */
    $thPrimaryHex   = function_exists('theme_primary_color') ? theme_primary_color() : '#108a55';
    $thSecondaryHex = function_exists('theme_secondary_color') ? theme_secondary_color() : '#1aae6c';
    if (!preg_match('/^#[0-9a-fA-F]{6}$/', $thPrimaryHex))   $thPrimaryHex   = '#108a55';
    if (!preg_match('/^#[0-9a-fA-F]{6}$/', $thSecondaryHex)) $thSecondaryHex = '#1aae6c';

    $thToRgb = function (string $hex): string {
        $hex = ltrim($hex, '#');
        return hexdec(substr($hex, 0, 2)) . ', ' . hexdec(substr($hex, 2, 2)) . ', ' . hexdec(substr($hex, 4, 2));
    };

    // Compute a slightly darker shade for hover gradients
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
    <style id="flyer-theme-overrides">
    :root {
        --flyer-brand: <?= $thPrimaryHex ?>;
        --flyer-brand-rgb: <?= $thPrimaryRgb ?>;
        --flyer-brand-glow: rgba(<?= $thPrimaryRgb ?>, 0.4);
        --flyer-accent: <?= $thSecondaryHex ?>;
        --flyer-accent-rgb: <?= $thSecondaryRgb ?>;
        --flyer-accent-glow: rgba(<?= $thSecondaryRgb ?>, 0.35);
        --flyer-brand-dark: <?= $thPrimaryDark ?>;
        --flyer-brand-darker: <?= $thPrimaryDarker ?>;
        --flyer-gradient-duo: linear-gradient(135deg, <?= $thPrimaryHex ?> 0%, <?= $thSecondaryHex ?> 100%);
        --flyer-gradient-duo-h: linear-gradient(90deg, <?= $thPrimaryHex ?> 0%, <?= $thSecondaryHex ?> 100%);
        --flyer-gradient-duo-hover: linear-gradient(135deg, <?= $thSecondaryHex ?> 0%, <?= $thPrimaryHex ?> 100%);
        --flyer-shadow-duo: 0 10px 25px rgba(<?= $thPrimaryRgb ?>, 0.35), 0 4px 12px rgba(<?= $thSecondaryRgb ?>, 0.25);
        --flyer-shadow-duo-hover: 0 14px 32px rgba(<?= $thPrimaryRgb ?>, 0.45), 0 6px 16px rgba(<?= $thSecondaryRgb ?>, 0.35);
    }
    </style>
</head>
<body style="--flyer-bg-image: url('<?= e($eventImg) ?>');">

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
                <!-- Brand Logo (Official Site Anchor) -->
                <a href="<?= url('/') ?>" class="flyer-brand-link brand-custom-logo" title="<?= e($siteTitle) ?> Home" aria-label="<?= e($siteTitle) ?> Home">
                    <?= site_logo_render('flyer', 'flyer-brand-logo-img') ?>
                </a>
                <span class="flyer-nav-mode-badge">
                    <span>🎨</span> Flyer Mode
                </span>
            </div>

            <div class="flyer-nav-right">
                <!-- Standard Event Details Link -->
                <a href="<?= e($eventUrl) ?>" class="flyer-back-btn" title="View standard event details page">
                    <span class="flyer-back-btn-arrow">←</span>
                    <span class="flyer-back-btn-text-full">Event Details</span>
                    <span class="flyer-back-btn-text-short">Details</span>
                </a>

                <!-- Quick jump to tickets -->
                <a href="#tickets-section" class="flyer-nav-jump-btn" title="Jump directly to ticket selection">
                    <span>🎟️</span> <span>Get Tickets</span>
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
                            <a href="https://api.whatsapp.com/send?text=<?= $waShareText ?>%20<?= $encodedFlyerUrl ?>" target="_blank" rel="noopener" class="flyer-share-btn flyer-share-btn--whatsapp" title="Share on WhatsApp">
                                <svg width="13" height="13" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                                <span>WhatsApp</span>
                            </a>
                            <a href="https://twitter.com/intent/tweet?text=<?= $tweetText ?>&url=<?= $encodedFlyerUrl ?>" target="_blank" rel="noopener" class="flyer-share-btn flyer-share-btn--twitter" title="Share on X">
                                <svg width="12" height="12" fill="currentColor" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                                <span>X</span>
                            </a>
                            <a href="https://www.facebook.com/sharer/sharer.php?u=<?= $encodedFlyerUrl ?>" target="_blank" rel="noopener" class="flyer-share-btn flyer-share-btn--facebook" title="Share on Facebook">
                                <svg width="12" height="12" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                                <span>Facebook</span>
                            </a>
                            <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?= $encodedFlyerUrl ?>" target="_blank" rel="noopener" class="flyer-share-btn flyer-share-btn--linkedin" title="Share on LinkedIn">
                                <svg width="12" height="12" fill="currentColor" viewBox="0 0 24 24"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>
                                <span>LinkedIn</span>
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

                        <?php if (!empty($event->allow_partial_payment)): ?>
                            <span class="flyer-status-badge flyer-status-badge--layaway" style="background:rgba(99, 102, 241, 0.16); color:#a5b4fc; border:1px solid rgba(99, 102, 241, 0.35);">💳 Layaway Plan Available</span>
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

                    <!-- Activity & Community Hub Link -->
                    <?php if (!$isCompleted): ?>
                        <a href="<?= route('event/' . $eventSlug . '/activities') ?>" class="flyer-desc-card" style="display:flex; align-items:center; gap:0.75rem; text-decoration:none; color:inherit; border:1px solid rgba(99,102,241,0.3); background:rgba(99,102,241,0.08); transition:border-color 0.2s, transform 0.15s;" onmouseover="this.style.borderColor='rgba(99,102,241,0.5)';this.style.transform='translateY(-1px)'" onmouseout="this.style.borderColor='rgba(99,102,241,0.3)';this.style.transform='translateY(0)'">
                            <span style="font-size:1.5rem;">🎉</span>
                            <div>
                                <strong style="font-size:0.9rem; display:block;">Live Activity Hub</strong>
                                <span style="font-size:0.78rem; opacity:0.75;">Polls · Trivia · Song Requests · Community</span>
                            </div>
                            <span style="margin-left:auto; font-size:1.1rem;">→</span>
                        </a>
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
                                <div style="display:flex; align-items:center; gap:0.5rem; flex-wrap:wrap;">
                                    <?php if (!empty($event->allow_partial_payment)): ?>
                                        <span class="flyer-instant-badge" style="background:rgba(99, 102, 241, 0.18); border-color:rgba(99, 102, 241, 0.35); color:#c7d2fe;">💳 Layaway Available</span>
                                    <?php endif; ?>
                                    <span class="flyer-instant-badge">⚡ Instant QR Delivery</span>
                                </div>
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
                                        $admits = max(1, (int)($tt['admits'] ?? 1));
                                        $soldUnits = $admits > 1 ? (int)floor($ttSold / $admits) : $ttSold;
                                        $remaining = max(0, $ttCap - $soldUnits);
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
                                                    <?php if (!empty($tt['admits']) && (int)$tt['admits'] > 1): ?>
                                                        <span class="flyer-tier-tag" style="background:rgba(99,102,241,0.12); color:#6366f1; border:1px solid rgba(99,102,241,0.25);">👥 Admits <?= (int)$tt['admits'] ?></span>
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
                                        <strong id="flyer-total-val"><?= format_price(0) ?></strong>
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
            <span class="flyer-mobile-bar-total" id="fmb-total"><?= format_price(0) ?></span>
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
            return currencySymbol + Number(val || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
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
                if (totalVal) totalVal.textContent = formatPrice(0);
                if (submitBtn) {
                    submitBtn.disabled = true;
                    if (ctaBtnText) ctaBtnText.textContent = 'Select Tickets to Continue';
                }
                if (mobileBar) mobileBar.classList.remove('active');
                if (fmbTotal) fmbTotal.textContent = formatPrice(0);
            } else {
                const formattedTotal = (total > 0) ? formatPrice(total) : 'Free';
                if (summaryLines) summaryLines.innerHTML = linesHtml;
                if (totalVal) totalVal.textContent = formattedTotal;
                if (submitBtn) {
                    submitBtn.disabled = false;
                    if (ctaBtnText) {
                        ctaBtnText.textContent = (total > 0) ? `Checkout (${totalCount}) • ${formattedTotal}` : `Claim Free Passes (${totalCount})`;
                    }
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
    <div id="flyer-toast-box" class="flyer-toast-box" aria-live="polite"></div>
    <?php echo $this->include('partials/holiday-fx', []); ?>
</body>
</html>
