<?php

/**
 * Sponsors & Partners Marquee Tape Partial
 *
 * An interactive, continuously-scrolling sponsorship carousel tape.
 * Features:
 * - Frosted glass blurred background with gradient edge masks
 * - Monochrome / black & white default aesthetic
 * - Hover state pops out vibrant full-color illumination and pauses marquee
 * - Rich typography options for text sponsors with customizable color and styles
 * - Proportion-preserving, distortion-free brand logo image rendering
 * - Fully responsive across mobile, tablet, and desktop screens
 * - Mathematically seamless infinite loop
 * - Clickable external links with audit tracking
 *
 * @package Eventlyy
 * @subpackage Views/Partials
 * @version 1.1.0
 */

// If sponsors not passed directly, fetch active platform sponsors via SponsorService
if (!isset($sponsors)) {
    $sponsorService = new \App\Services\SponsorService();
    $sponsors = $sponsorService->getActiveSponsors();
}

// Convert any iterable / Ace\ViewData safely into a native array
$sponsorsList = [];
if (!empty($sponsors)) {
    foreach ($sponsors as $s) {
        $sponsorsList[] = $s;
    }
}

$tapeEnabled = site_setting('sponsors_tape_enabled', '1');
if (empty($tapeEnabled) || $tapeEnabled === '0' || empty($sponsorsList)) {
    return;
}

$tapeTitle = site_setting('sponsors_tape_title', 'OFFICIAL PARTNERS & PATRONS');
$tapeSpeed = site_setting('sponsors_tape_speed', 'normal');

// Speed duration in seconds
$duration = match ($tapeSpeed) {
    'slow' => 45,
    'fast' => 20,
    default => 30
};

// Build a smooth infinite track:
// We ensure the base list has at least 6 items so the marquee spans wide screens,
// then duplicate the base list once into two identical halves.
// Shifting by translate3d(-50%, 0, 0) therefore resets with 100% seamless continuity.
$baseList = $sponsorsList;
while (count($baseList) < 6) {
    foreach ($sponsorsList as $item) {
        $baseList[] = $item;
    }
}

// Duplicate baseList into two identical halves
$renderItems = [];
foreach ($baseList as $item) {
    $renderItems[] = $item;
}
foreach ($baseList as $item) {
    $renderItems[] = $item;
}
?>

<section class="sp-tape-wrapper" aria-label="Official Partners and Sponsors">
    <?php if (!empty($tapeTitle)): ?>
        <div class="sp-tape-header">
            <span class="sp-tape-star">✦</span>
            <span class="sp-tape-title"><?= e($tapeTitle) ?></span>
            <span class="sp-tape-star">✦</span>
        </div>
    <?php endif; ?>

    <div class="sp-tape-viewport">
        <div class="sp-tape-track" style="--sp-duration: <?= $duration ?>s;">
            <?php foreach ($renderItems as $idx => $sp): ?>
                <?php
                $isLink = !empty($sp['website_url']);
                $tag = $isLink ? 'a' : 'div';
                $linkAttrs = $isLink ? ' href="' . e($sp['website_url']) . '" target="_blank" rel="noopener noreferrer" title="' . e($sp['name']) . '"' : '';
                $type = $sp['type'] ?? 'image';
                $color = !empty($sp['text_color']) ? e($sp['text_color']) : '#ffffff';
                $font = !empty($sp['text_font']) ? e($sp['text_font']) : 'sans';
                $style = !empty($sp['text_style']) ? e($sp['text_style']) : 'bold';
                $hasBadge = !empty($sp['badge_text']);
                ?>
                <<?= $tag ?><?= $linkAttrs ?> class="sp-tape-item sp-item-<?= $type ?> font-<?= $font ?> style-<?= $style ?><?= $hasBadge ? ' has-badge' : '' ?>" data-sponsor-id="<?= $sp['id'] ?>">
                    <div class="sp-item-inner">
                        <?php if ($hasBadge): ?>
                            <span class="sp-item-badge"><?= e($sp['badge_text']) ?></span>
                        <?php endif; ?>

                        <?php if ($type === 'image' && !empty($sp['image_url'])): ?>
                            <div class="sp-img-box">
                                <img src="<?= e(url($sp['image_url'])) ?>" alt="<?= e($sp['name']) ?>" class="sp-logo-img" loading="lazy" decoding="async" draggable="false">
                            </div>
                        <?php else: ?>
                            <div class="sp-text-box" style="--sp-color: <?= $color ?>;">
                                <span class="sp-text-label"><?= e($sp['text_content'] ?: $sp['name']) ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                </<?= $tag ?>>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<style>
/* ══════════════════════════════════════════════════════════════════════════
   SPONSORSHIP MINI CAROUSEL TAPE (RESPONSIVE & SHAPE-STABLE)
   ══════════════════════════════════════════════════════════════════════════ */
.sp-tape-wrapper {
    position: relative;
    width: 100%;
    margin: 2rem 0;
    padding: 1.15rem 0 1.35rem;
    background: rgba(15, 23, 42, 0.5);
    backdrop-filter: blur(16px);
    -webkit-backdrop-filter: blur(16px);
    border-top: 1px solid rgba(255, 255, 255, 0.08);
    border-bottom: 1px solid rgba(255, 255, 255, 0.08);
    overflow: hidden;
    user-select: none;
    box-sizing: border-box;
    transition: background 0.3s ease, border-color 0.3s ease;
}

[data-theme="light"] .sp-tape-wrapper {
    background: rgba(248, 250, 252, 0.78);
    border-top: 1px solid rgba(15, 23, 42, 0.08);
    border-bottom: 1px solid rgba(15, 23, 42, 0.08);
}

.sp-tape-header {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.6rem;
    margin-bottom: 0.85rem;
    font-size: 0.72rem;
    font-weight: 800;
    letter-spacing: 0.22em;
    text-transform: uppercase;
    color: rgba(255, 255, 255, 0.5);
}

[data-theme="light"] .sp-tape-header {
    color: rgba(15, 23, 42, 0.55);
}

.sp-tape-header .sp-tape-star {
    font-size: 0.65rem;
    color: #eab308;
    opacity: 0.85;
}

/* Gradient edge fade mask for smooth cinematic entrance/exit */
.sp-tape-viewport {
    position: relative;
    width: 100%;
    overflow: hidden;
    mask-image: linear-gradient(to right, transparent 0%, black 7%, black 93%, transparent 100%);
    -webkit-mask-image: linear-gradient(to right, transparent 0%, black 7%, black 93%, transparent 100%);
}

.sp-tape-track {
    display: flex;
    align-items: center;
    gap: 2rem;
    width: max-content;
    animation: spTapeMarquee var(--sp-duration, 30s) linear infinite;
    will-change: transform;
}

/* Pause animation on hover so attendees can inspect or click */
.sp-tape-wrapper:hover .sp-tape-track {
    animation-play-state: paused;
}

@keyframes spTapeMarquee {
    0% {
        transform: translate3d(0, 0, 0);
    }
    100% {
        transform: translate3d(-50%, 0, 0);
    }
}

/* ── Sponsor Item Base (Unified Dimensions & Silhouette) ──────────────── */
.sp-tape-item {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    height: 52px;
    padding: 0 1.25rem;
    border-radius: 999px;
    background: rgba(255, 255, 255, 0.04);
    border: 1px solid rgba(255, 255, 255, 0.08);
    box-sizing: border-box;
    overflow: hidden;
    flex-shrink: 0;
    cursor: pointer;
    /* White and Black / Grayscale Muted Default State */
    filter: grayscale(100%) opacity(0.7) contrast(0.95);
    transition: filter 0.35s cubic-bezier(0.16, 1, 0.3, 1),
                transform 0.35s cubic-bezier(0.16, 1, 0.3, 1),
                background 0.35s ease,
                border-color 0.35s ease,
                box-shadow 0.35s ease;
}

[data-theme="light"] .sp-tape-item {
    background: rgba(15, 23, 42, 0.035);
    border: 1px solid rgba(15, 23, 42, 0.08);
}

/* ── COLOR POPS OUT ON HOVER ────────────────────────────────────────────── */
.sp-tape-item:hover {
    filter: grayscale(0%) opacity(1) contrast(1.05);
    transform: translateY(-2px) scale(1.06);
    background: rgba(255, 255, 255, 0.12);
    border-color: rgba(255, 255, 255, 0.28);
    box-shadow: 0 10px 24px -4px rgba(0, 0, 0, 0.42), 0 0 18px -2px rgba(255, 255, 255, 0.18);
}

[data-theme="light"] .sp-tape-item:hover {
    background: #ffffff;
    border-color: rgba(15, 23, 42, 0.18);
    box-shadow: 0 10px 22px -4px rgba(0, 0, 0, 0.12), 0 0 14px -2px rgba(0, 0, 0, 0.06);
}

.sp-item-inner {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 2px;
    height: 100%;
    width: 100%;
    box-sizing: border-box;
    pointer-events: none;
}

/* Optional Category / Tier Badge */
.sp-item-badge {
    font-size: 0.54rem;
    font-weight: 800;
    letter-spacing: 0.14em;
    text-transform: uppercase;
    line-height: 1;
    white-space: nowrap;
    color: rgba(255, 255, 255, 0.45);
    transition: color 0.3s ease;
}

[data-theme="light"] .sp-item-badge {
    color: rgba(15, 23, 42, 0.48);
}

.sp-tape-item:hover .sp-item-badge {
    color: #eab308;
}

/* ── Image Logo Styling (Distortion-Free & Constrained) ─────────────────── */
.sp-item-image {
    padding: 0 1.15rem;
}

.sp-img-box {
    display: flex;
    align-items: center;
    justify-content: center;
    height: 32px;
    max-width: 140px;
    min-width: 55px;
    padding: 0 2px;
    box-sizing: border-box;
}

/* When badge is present, slightly constrain logo height so the pill remains 52px */
.sp-tape-item.has-badge .sp-img-box {
    height: 24px;
    max-width: 130px;
}

.sp-logo-img {
    display: block;
    max-height: 100%;
    max-width: 100%;
    width: auto;
    height: auto;
    object-fit: contain;
    object-position: center;
    pointer-events: none;
    transition: transform 0.35s cubic-bezier(0.16, 1, 0.3, 1);
}

.sp-tape-item:hover .sp-logo-img {
    transform: scale(1.05);
}

/* ── Text Sponsor Styling & Typography Presets ─────────────────────────── */
.sp-text-box {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    line-height: 1;
    white-space: nowrap;
    color: #ffffff;
    transition: color 0.35s ease, text-shadow 0.35s ease;
}

[data-theme="light"] .sp-text-box {
    color: #0f172a;
}

.sp-tape-item:hover .sp-text-box {
    color: var(--sp-color, #ffffff) !important;
}

.sp-text-label {
    display: inline-block;
    white-space: nowrap;
    line-height: 1.1;
}

/* Font Families */
.sp-tape-item.font-sans .sp-text-label {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
}
.sp-tape-item.font-serif .sp-text-label {
    font-family: 'Playfair Display', 'Cinzel', Georgia, 'Times New Roman', serif;
    letter-spacing: 0.06em;
}
.sp-tape-item.font-display .sp-text-label {
    font-family: 'Syne', 'Outfit', 'Impact', sans-serif;
    letter-spacing: 0.08em;
}
.sp-tape-item.font-mono .sp-text-label {
    font-family: 'JetBrains Mono', 'Fira Code', 'Courier New', monospace;
    letter-spacing: 0.04em;
}
.sp-tape-item.font-script .sp-text-label {
    font-family: 'Caveat', 'Brush Script MT', cursive, sans-serif;
    font-size: 1.25rem;
}

/* Text Styles & Weights */
.sp-tape-item.style-bold .sp-text-label {
    font-weight: 700;
    font-size: 1rem;
}
.sp-tape-item.style-black .sp-text-label {
    font-weight: 900;
    font-size: 1.08rem;
    letter-spacing: 0.05em;
}
.sp-tape-item.style-italic .sp-text-label {
    font-weight: 700;
    font-style: italic;
    font-size: 1rem;
}
.sp-tape-item.style-black_italic .sp-text-label {
    font-weight: 900;
    font-style: italic;
    font-size: 1.08rem;
    letter-spacing: 0.05em;
}
.sp-tape-item.style-bold_caps .sp-text-label {
    font-weight: 800;
    font-size: 0.95rem;
    text-transform: uppercase;
    letter-spacing: 0.16em;
}
.sp-tape-item.style-neon .sp-text-label {
    font-weight: 800;
    font-size: 1.08rem;
    letter-spacing: 0.08em;
}

.sp-tape-item.style-neon:hover .sp-text-box {
    text-shadow: 0 0 10px var(--sp-color), 0 0 22px var(--sp-color);
}

/* ══════════════════════════════════════════════════════════════════════════
   RESPONSIVE DESIGN BREAKPOINTS
   ══════════════════════════════════════════════════════════════════════════ */
@media (max-width: 1024px) {
    .sp-tape-track {
        gap: 1.6rem;
    }
}

@media (max-width: 768px) {
    .sp-tape-wrapper {
        margin: 1.5rem 0;
        padding: 0.95rem 0 1.15rem;
    }
    .sp-tape-header {
        font-size: 0.65rem;
        margin-bottom: 0.7rem;
        gap: 0.45rem;
        letter-spacing: 0.18em;
    }
    .sp-tape-track {
        gap: 1.25rem;
    }
    .sp-tape-item {
        height: 44px;
        padding: 0 1rem;
    }
    .sp-item-image {
        padding: 0 0.95rem;
    }
    .sp-img-box {
        height: 26px;
        max-width: 110px;
        min-width: 45px;
    }
    .sp-tape-item.has-badge .sp-img-box {
        height: 20px;
        max-width: 95px;
    }
    .sp-item-badge {
        font-size: 0.5rem;
        letter-spacing: 0.11em;
    }
    .sp-tape-item.style-bold .sp-text-label,
    .sp-tape-item.style-italic .sp-text-label {
        font-size: 0.92rem;
    }
    .sp-tape-item.style-black .sp-text-label,
    .sp-tape-item.style-black_italic .sp-text-label,
    .sp-tape-item.style-neon .sp-text-label {
        font-size: 0.98rem;
    }
    .sp-tape-item.style-bold_caps .sp-text-label {
        font-size: 0.88rem;
        letter-spacing: 0.13em;
    }
    .sp-tape-item.font-script .sp-text-label {
        font-size: 1.1rem;
    }
}

@media (max-width: 480px) {
    .sp-tape-wrapper {
        margin: 1.15rem 0;
        padding: 0.8rem 0 0.95rem;
    }
    .sp-tape-viewport {
        mask-image: linear-gradient(to right, transparent 0%, black 9%, black 91%, transparent 100%);
        -webkit-mask-image: linear-gradient(to right, transparent 0%, black 9%, black 91%, transparent 100%);
    }
    .sp-tape-header {
        font-size: 0.58rem;
        margin-bottom: 0.55rem;
        letter-spacing: 0.14em;
    }
    .sp-tape-header .sp-tape-star {
        font-size: 0.55rem;
    }
    .sp-tape-track {
        gap: 0.95rem;
    }
    .sp-tape-item {
        height: 38px;
        padding: 0 0.8rem;
    }
    .sp-item-image {
        padding: 0 0.75rem;
    }
    .sp-img-box {
        height: 22px;
        max-width: 90px;
        min-width: 38px;
    }
    .sp-tape-item.has-badge .sp-img-box {
        height: 16px;
        max-width: 80px;
    }
    .sp-item-badge {
        font-size: 0.44rem;
        letter-spacing: 0.08em;
    }
    .sp-tape-item.style-bold .sp-text-label,
    .sp-tape-item.style-italic .sp-text-label {
        font-size: 0.84rem;
    }
    .sp-tape-item.style-black .sp-text-label,
    .sp-tape-item.style-black_italic .sp-text-label,
    .sp-tape-item.style-neon .sp-text-label {
        font-size: 0.88rem;
    }
    .sp-tape-item.style-bold_caps .sp-text-label {
        font-size: 0.8rem;
        letter-spacing: 0.1em;
    }
    .sp-tape-item.font-script .sp-text-label {
        font-size: 0.98rem;
    }
}
</style>
