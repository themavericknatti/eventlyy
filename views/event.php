<?php

/**
 * Event Page — Vintage Edition
 *
 * Immersive vintage box office & ticket reservation parlor.
 * Features antique admission stubs, letterpress typography ('Instrument Serif'),
 * clockwork countdown, interactive ticket steppers, receipt summary, and mobile optimization.
 *
 * @package Eventlyy
 * @subpackage Views/Event
 * @author Celio Natti
 * @license MIT License
 * @version 3.1.0
 */

$siteTitle = site_setting('site_title', 'Eventlyy');
$siteLogo = site_setting('site_logo');
$absSiteLogo = !empty($siteLogo) ? (str_starts_with($siteLogo, 'http') ? $siteLogo : url($siteLogo)) : url('assets/images/logo.png');

$rawImg = !empty($event->image) ? $event->image : 'https://images.unsplash.com/photo-1506157786151-b8491531f063?w=1200&q=80&auto=format&fit=crop';
$absEventImg = str_starts_with($rawImg, 'http') ? $rawImg : url($rawImg);
$eventImg = $absEventImg;

$rawDesc = $event->description ?? '';
$heroSummary = (mb_strlen($rawDesc) > 160) ? mb_strimwidth($rawDesc, 0, 160, '…') : $rawDesc;
$eventDate = strtotime($event->date ?? '');
$now = time();
$liveWindowEnd = $eventDate ? $eventDate + (event_completion_hours() * 3600) : 0;
$isCompleted = ($eventDate && $now > $liveWindowEnd) || in_array(strtolower($event->status ?? ''), ['completed', 'past', 'cancelled']);
$isLiveNow = !$isCompleted && ($eventDate && $now >= $eventDate && $now <= $liveWindowEnd);
$isToday = !$isCompleted && ($eventDate && date('Y-m-d', $now) === date('Y-m-d', $eventDate));
$eventIdentifier = !empty($event->custom_url) ? $event->custom_url : ($event->slug ?? $event->id);

// Ticket sales countdown: show when event is < 24 hours away and not yet started/completed
$salesCloseTimestamp = $eventDate ?: 0;
$hoursUntilClose = $salesCloseTimestamp ? ($salesCloseTimestamp - $now) / 3600 : 0;
$showCountdown = !$isCompleted && !$isLiveNow && $salesCloseTimestamp && $hoursUntilClose > 0 && $hoursUntilClose <= 24;

$shareUrl = event_url($event);
$encodedShareUrl = urlencode($shareUrl);

$shareMsg = "🎟️ Grab passes for " . ($event->name ?? 'this event') . " on " . $siteTitle . "!\n" . $shareUrl;
$shareText = urlencode($shareMsg);
$waShareText = urlencode("🎟️ *" . ($event->name ?? 'this event') . "* on " . $siteTitle . "\n📅 " . (!empty($eventDate) ? date('D, M d, Y', $eventDate) : '') . (!empty($event->location) ? ' • 📍 ' . $event->location : '') . "\n\n" . $shareUrl);
$tweetText = urlencode("Check out " . ($event->name ?? 'this event') . " on @" . str_replace(' ', '', $siteTitle));
?>

@extends('layouts/main')

@section('meta')
<!-- OpenGraph / Social Share Cards (WhatsApp, Facebook, LinkedIn, iMessage, etc.) -->
<meta property="og:type" content="website">
<meta property="og:site_name" content="<?= e($siteTitle) ?>">
<meta property="og:title" content="<?= e($event->name) ?> — Tickets &amp; Passes | <?= e($siteTitle) ?>">
<meta property="og:description" content="<?= e($heroSummary ?: ($event->name . ' live on ' . $siteTitle)) ?>">
<meta property="og:url" content="<?= e($shareUrl) ?>">
<meta property="og:image" content="<?= e($absEventImg) ?>">
<meta property="og:image:secure_url" content="<?= e($absEventImg) ?>">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta property="og:image:alt" content="<?= e($event->name) ?> Poster">
<?php if (!empty($absSiteLogo)): ?>
<meta property="og:logo" content="<?= e($absSiteLogo) ?>">
<?php endif; ?>

<!-- Twitter Card -->
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:site" content="@<?= e(str_replace(' ', '', $siteTitle)) ?>">
<meta name="twitter:title" content="<?= e($event->name) ?> | <?= e($siteTitle) ?>">
<meta name="twitter:description" content="<?= e($heroSummary ?: ($event->name . ' live on ' . $siteTitle)) ?>">
<meta name="twitter:image" content="<?= e($absEventImg) ?>">
<meta name="twitter:image:alt" content="<?= e($event->name) ?>">

<!-- Schema.org JSON-LD -->
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "Event",
    "name": <?= json_encode($event->name) ?>,
    "description": <?= json_encode($heroSummary ?: $event->name) ?>,
    "image": [<?= json_encode($absEventImg) ?>],
    "url": <?= json_encode($shareUrl) ?>,
    <?php if (!empty($eventDate)): ?>
    "startDate": <?= json_encode(date('c', $eventDate)) ?>,
    <?php endif; ?>
    <?php if (!empty($event->location)): ?>
    "location": {
        "@type": "Place",
        "name": <?= json_encode($event->location) ?>
    },
    <?php endif; ?>
    "organizer": {
        "@type": "Organization",
        "name": <?= json_encode($siteTitle) ?>,
        "url": <?= json_encode(url()) ?><?php if (!empty($absSiteLogo)): ?>,
        "logo": <?= json_encode($absSiteLogo) ?>
        <?php endif; ?>
    }
}
</script>
@endsection

@section('title')
<?= e($event->name) ?> — Eventlyy Box Office
@endsection

@section('content')
<style>
.ve-badge--series-link {
    border-color: var(--ve-brass);
    color: var(--ve-brass);
    text-decoration: none;
}
.ve-min-w-0 { min-width: 0; }

.ve-series-head {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 0.75rem;
    margin-bottom: 0.75rem;
}
.ve-series-title {
    font-family: var(--ve-font-serif);
    font-size: 1.4rem;
    color: var(--ve-ink);
    margin: 0;
}
.ve-series-sub {
    font-size: 0.85rem;
    color: var(--ve-ink-muted);
    margin: 0.2rem 0 0;
}
.ve-series-select {
    width: 100%;
    height: 44px;
    padding: 0 0.85rem;
    border-radius: var(--ve-radius-sm);
    border: 1px solid var(--ve-border-dark);
    background: var(--ve-parchment-card);
    font-family: var(--ve-font-sans);
    font-size: 0.9rem;
    color: var(--ve-ink);
}
.ve-concluded-icon {
    font-size: 2.75rem;
    display: block;
    margin-bottom: 0.5rem;
}
.ve-concluded-title {
    font-family: var(--ve-font-serif);
    font-size: 2rem;
    color: var(--ve-ink);
    margin: 0 0 0.5rem;
}
.ve-concluded-text {
    color: var(--ve-ink-muted);
    max-width: 500px;
    margin: 0 auto 1.5rem;
}
.ve-concluded-btn { display: inline-flex; }
.ve-empty-tiers-msg {
    color: var(--ve-ink-muted);
    text-align: center;
    padding: 2rem 0;
}
.ve-empty-summary-msg {
    font-size: 0.86rem;
    color: var(--ve-ink-light);
    text-align: center;
    margin: auto 0;
    font-style: italic;
}
.ve-guests-section { margin-bottom: 2rem; }
.ve-guests-title {
    font-family: var(--ve-font-serif);
    font-size: 1.6rem;
    color: var(--ve-ink);
    margin: 0 0 1rem;
}
.ve-guest-avatar {
    width: 38px;
    height: 38px;
    font-size: 0.95rem;
}
.ve-guest-name {
    display: block;
    font-size: 0.92rem;
    color: var(--ve-ink);
}
.ve-guest-role {
    font-size: 0.78rem;
    color: var(--ve-ink-muted);
}

/* ── Activity Showcase Section ── */
.ve-activity-showcase {
    margin: 2rem 0;
    padding: 1.75rem;
    background: var(--ve-parchment-card, #fbf8f2);
    border: 1px solid var(--ve-border-dark, rgba(30, 27, 22, 0.18));
    border-radius: var(--ve-radius-md, 16px);
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04);
    position: relative;
    overflow: hidden;
}

.ve-act-header {
    text-align: center;
    max-width: 680px;
    margin: 0 auto 1.75rem;
}

.ve-act-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    padding: 0.28rem 0.85rem;
    background: rgba(99, 102, 241, 0.1);
    color: #4f46e5;
    border: 1px solid rgba(99, 102, 241, 0.28);
    border-radius: 999px;
    font-size: 0.76rem;
    font-weight: 700;
    letter-spacing: 0.04em;
    text-transform: uppercase;
    margin-bottom: 0.6rem;
}

.ve-act-title {
    font-family: var(--ve-font-serif, 'Instrument Serif', Georgia, serif);
    font-size: 2rem;
    line-height: 1.2;
    color: var(--ve-ink, #1e1b16);
    margin: 0 0 0.5rem;
    letter-spacing: -0.01em;
}

.ve-act-subtitle {
    font-size: 0.92rem;
    color: var(--ve-ink-muted, #645e54);
    line-height: 1.5;
    margin: 0;
}

.ve-act-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1rem;
    margin-bottom: 1.75rem;
}

.ve-act-card {
    background: var(--ve-parchment, #fdfbf7);
    border: 1px solid rgba(30, 27, 22, 0.12);
    border-radius: 12px;
    padding: 1.25rem 1rem;
    display: flex;
    flex-direction: column;
    text-decoration: none;
    color: inherit;
    transition: all 0.22s cubic-bezier(0.16, 1, 0.3, 1);
    position: relative;
}

.ve-act-card:hover {
    transform: translateY(-3px);
    border-color: rgba(99, 102, 241, 0.4);
    box-shadow: 0 8px 24px rgba(99, 102, 241, 0.1);
}

.ve-act-card-top {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.75rem;
}

.ve-act-card-icon {
    font-size: 1.6rem;
    line-height: 1;
}

.ve-act-card-tag {
    font-size: 0.68rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    padding: 0.2rem 0.5rem;
    border-radius: 6px;
    background: rgba(99, 102, 241, 0.1);
    color: #4f46e5;
    border: 1px solid rgba(99, 102, 241, 0.2);
}

.ve-act-card-name {
    font-family: var(--ve-font-serif, 'Instrument Serif', Georgia, serif);
    font-size: 1.25rem;
    color: var(--ve-ink, #1e1b16);
    margin: 0 0 0.4rem;
    line-height: 1.25;
}

.ve-act-card-desc {
    font-size: 0.8rem;
    color: var(--ve-ink-muted, #645e54);
    line-height: 1.45;
    margin: 0;
    flex: 1;
}

/* Dynamic Live Activity Engagement / Discussion Badges */
.ve-act-engagement-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.35rem 0.65rem;
    margin: 0.75rem 0 0.25rem;
    background: rgba(15, 23, 42, 0.05);
    border: 1px solid rgba(15, 23, 42, 0.12);
    border-radius: 8px;
    font-size: 0.74rem;
    font-weight: 700;
    line-height: 1.25;
    color: var(--ve-ink, #1e1b16);
    transition: all 0.2s ease;
}

.ve-act-engagement-badge.is-active {
    background: rgba(99, 102, 241, 0.08);
    border-color: rgba(99, 102, 241, 0.25);
    color: #4338ca;
}

.ve-act-engagement-dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: #6366f1;
    flex-shrink: 0;
    box-shadow: 0 0 6px rgba(99, 102, 241, 0.5);
}

.ve-act-engagement-dot.is-hot {
    background: #10b981;
    box-shadow: 0 0 8px rgba(16, 185, 129, 0.8);
    animation: vePulse 1.8s infinite;
}

.ve-act-card:hover .ve-act-engagement-badge {
    background: rgba(99, 102, 241, 0.12);
    border-color: rgba(99, 102, 241, 0.35);
}

.ve-act-card-arrow {
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    font-size: 0.78rem;
    font-weight: 700;
    color: #4f46e5;
    margin-top: 0.75rem;
    transition: transform 0.15s ease;
}
.ve-act-card:hover .ve-act-card-arrow {
    transform: translateX(3px);
}

.ve-act-cta-bar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 1rem;
    padding-top: 1.25rem;
    border-top: 1px solid rgba(30, 27, 22, 0.1);
}

.ve-act-cta-left {
    display: flex;
    align-items: center;
    gap: 0.65rem;
}

.ve-act-pulse {
    width: 9px;
    height: 9px;
    background: #10b981;
    border-radius: 50%;
    box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
    animation: vePulse 2s infinite;
}

@keyframes vePulse {
    0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
    70% { transform: scale(1); box-shadow: 0 0 0 8px rgba(16, 185, 129, 0); }
    100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
}

.ve-act-cta-text {
    font-size: 0.86rem;
    font-weight: 600;
    color: var(--ve-ink, #1e1b16);
}

.ve-act-cta-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 0.65rem 1.4rem;
    background: linear-gradient(135deg, #4f46e5, #7c3aed);
    color: #ffffff !important;
    font-weight: 700;
    font-size: 0.88rem;
    border-radius: 10px;
    text-decoration: none;
    box-shadow: 0 4px 14px rgba(79, 70, 229, 0.3);
    transition: all 0.2s ease;
    min-height: 44px;
}

.ve-act-cta-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 6px 18px rgba(79, 70, 229, 0.45);
    filter: brightness(1.06);
}

@media (max-width: 992px) {
    .ve-act-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 1rem;
    }
}

@media (max-width: 640px) {
    .ve-activity-showcase {
        padding: 1.25rem 1rem;
        margin: 1.5rem 0;
    }
    .ve-act-title {
        font-size: 1.5rem;
    }
    .ve-act-grid {
        grid-template-columns: 1fr;
        gap: 0.75rem;
    }
    .ve-act-cta-bar {
        flex-direction: column;
        align-items: stretch;
        text-align: center;
    }
    .ve-act-cta-left {
        justify-content: center;
    }
    .ve-act-cta-btn {
        width: 100%;
    }
}
</style>
<div class="ve-wrapper">

    <!-- ═══ 1. Grand Event Hero Stage ═══ -->
    <div class="ve-hero-stage">
        <section class="ve-hero-main">
            <!-- Mobile Poster Showcase (visible on <= 960px) -->
            <div class="ve-mobile-poster" aria-hidden="true">
                <img src="<?= e($eventImg) ?>" alt="<?= e($event->name) ?> Poster" loading="eager" decoding="async">
            </div>

            <!-- Badges -->
            <div class="ve-badges-row">
                <?php if ($isCompleted): ?>
                    <span class="ve-badge ve-badge--completed">🏁 Concluded</span>
                <?php elseif ($isLiveNow): ?>
                    <span class="ve-badge ve-badge--live">🔴 Live Now &bull; Gate Admission</span>
                <?php elseif ($isToday): ?>
                    <span class="ve-badge ve-badge--today">⚡ Today's Gathering</span>
                <?php endif; ?>

                <span class="ve-badge"><?= e($event->category ?? 'Special Gathering') ?></span>

                <?php if (!empty($event->is_recurring)): ?>
                    <a href="#series-date-selector" class="ve-badge ve-badge--series-link">
                        🔄 Recurring Series<?= !empty($recurringOccurrences) ? ' (' . count($recurringOccurrences) . ' Dates)' : '' ?>
                    </a>
                <?php endif; ?>

                <?php if (!empty($event->flyer_page_enabled)): ?>
                    <a href="<?= e(event_flyer_url($event)) ?>" class="ve-badge ve-badge--flyer" title="View dedicated flyer poster">
                        🎨 Flyer Mode
                    </a>
                <?php endif; ?>
            </div>

            <!-- Title -->
            <h1 class="ve-title"><?= e($event->name) ?></h1>
            <p class="ve-summary"><?= e($heroSummary) ?></p>

            <!-- Postmark Meta Chips -->
            <div class="ve-meta-grid">
                <div class="ve-meta-card ve-meta-card--date">
                    <span class="ve-meta-icon" aria-hidden="true">🗓️</span>
                    <div class="ve-meta-content">
                        <span class="ve-meta-label">Date</span>
                        <span class="ve-meta-val"><?= $eventDate ? date('M d, Y', $eventDate) : 'Date TBA' ?></span>
                    </div>
                </div>
                <div class="ve-meta-card ve-meta-card--time">
                    <span class="ve-meta-icon" aria-hidden="true">⏰</span>
                    <div class="ve-meta-content">
                        <span class="ve-meta-label">Time</span>
                        <span class="ve-meta-val"><?= $eventDate ? date('g:i A', $eventDate) : 'Time TBA' ?></span>
                    </div>
                </div>
                <div class="ve-meta-card ve-meta-card--venue">
                    <span class="ve-meta-icon" aria-hidden="true">📍</span>
                    <div class="ve-meta-content">
                        <span class="ve-meta-label">Venue</span>
                        <span class="ve-meta-val" title="<?= e($event->location) ?>"><?= e($event->location) ?></span>
                    </div>
                </div>
            </div>
        </section>

        <!-- 2. Framed Poster & Host Ledger Sidebar -->
        <aside class="ve-sidebar">
            <div class="ve-poster-wrap ve-desktop-only">
                <img src="<?= e($eventImg) ?>" alt="<?= e($event->name) ?> Poster" class="ve-poster-img" loading="eager" decoding="async">
            </div>
            <div class="ve-sidebar-body">
                <!-- Host Ledger -->
                <div class="ve-host-block">
                    <div class="ve-host-avatar">
                        <?= strtoupper(substr($event->organizer_name ?? 'H', 0, 1)) ?>
                    </div>
                    <div class="ve-min-w-0">
                        <span class="ve-host-name"><?= e($event->organizer_name ?? 'Event Organizer') ?></span>
                        <span class="ve-host-rating">★ <?= number_format($event->organizer_rating ?? 5.0, 1) ?> Verified Host Rating</span>
                    </div>
                </div>

                <!-- Share Dispatch -->
                <div class="ve-share-block">
                    <span class="ve-block-label">Share This Experience</span>
                    <div class="ve-share-btns">
                        <a href="https://api.whatsapp.com/send?text=<?= $waShareText ?>" target="_blank" rel="noopener" class="ve-share-btn ve-share-btn--whatsapp" title="Share on WhatsApp">
                            <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                            <span>WhatsApp</span>
                        </a>
                        <a href="https://twitter.com/intent/tweet?url=<?= $encodedShareUrl ?>&text=<?= $tweetText ?>" target="_blank" rel="noopener" class="ve-share-btn ve-share-btn--twitter" title="Share on X">
                            <svg width="14" height="14" fill="currentColor" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                            <span>X / Twitter</span>
                        </a>
                        <a href="https://www.facebook.com/sharer/sharer.php?u=<?= $encodedShareUrl ?>" target="_blank" rel="noopener" class="ve-share-btn ve-share-btn--facebook" title="Share on Facebook">
                            <svg width="14" height="14" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                            <span>Facebook</span>
                        </a>
                        <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?= $encodedShareUrl ?>" target="_blank" rel="noopener" class="ve-share-btn ve-share-btn--linkedin" title="Share on LinkedIn">
                            <svg width="14" height="14" fill="currentColor" viewBox="0 0 24 24"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>
                            <span>LinkedIn</span>
                        </a>
                        <button type="button" id="copy-share-link" class="ve-share-btn ve-share-btn--copy ve-share-btn--full" title="Copy event link">
                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
                            <span id="copy-btn-text">Copy Event Link</span>
                        </button>
                    </div>
                </div>

                <!-- Add to Calendar -->
                <div class="ve-share-block">
                    <span class="ve-block-label">📅 Add to Calendar</span>
                    <div class="ve-cal-btns">
                        <a href="<?= event_google_calendar_url($event) ?>" target="_blank" rel="noopener" class="ve-share-btn ve-share-btn--gcal" title="Add to Google Calendar">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M18 3h-1V1h-2v2H9V1H7v2H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2z" fill="#4285F4"/><rect x="4" y="8" width="16" height="13" rx="1" fill="#fff"/><path d="M8 12h3v3H8zm5 0h3v3h-3zM8 16.5h3v2.5H8zm5 0h3v2.5h-3z" fill="#4285F4" opacity=".5"/></svg>
                            <span>Google</span>
                        </a>
                        <a href="<?= event_ics_url($event) ?>" download class="ve-share-btn ve-share-btn--ical" title="Download iCal file">
                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/><path d="M8 14h.01M12 14h.01M16 14h.01M8 18h.01M12 18h.01"/></svg>
                            <span>iCal</span>
                        </a>
                        <a href="<?= event_outlook_calendar_url($event) ?>" target="_blank" rel="noopener" class="ve-share-btn ve-share-btn--outlook" title="Add to Outlook Calendar">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M24 7.387v10.478c0 .23-.08.424-.238.576-.16.154-.352.23-.578.23h-8.26v-12.6h8.26c.226 0 .418.077.578.232.158.154.238.348.238.577v.507zM13.924 3.076V21.23l-13.924-2.11V5.187l13.924-2.11zM8.17 15.565c.79-.03 1.467-.356 2.026-.977.56-.62.84-1.39.84-2.31 0-.99-.273-1.788-.817-2.395-.544-.608-1.244-.92-2.1-.94-.84.02-1.543.335-2.105.945-.562.61-.843 1.393-.843 2.35 0 .93.272 1.705.817 2.325.545.62 1.244.94 2.1.97l.082.032zm-.194-5.14c.54.01.98.23 1.316.656.337.427.505.974.505 1.642 0 .698-.168 1.265-.505 1.7-.337.434-.774.656-1.316.664-.55-.008-.994-.23-1.333-.664-.34-.435-.51-1.002-.51-1.7 0-.668.17-1.215.51-1.642.34-.426.784-.646 1.333-.655z"/></svg>
                            <span>Outlook</span>
                        </a>
                    </div>
                </div>
            </div>
        </aside>
    </div>

    <!-- ═══ Activity & Community Hub Showcase ═══ -->
    <?php if (!$isCompleted): ?>
        <section class="ve-activity-showcase">
            <div class="ve-act-header">
                <span class="ve-act-badge">
                    <span class="ve-act-pulse"></span>
                    ★ Pre-Event Lounge &amp; Arena ★
                </span>
                <h2 class="ve-act-title">Join The Live Activities &amp; Crowd Arena</h2>
                <p class="ve-act-subtitle">
                    Cast your daily votes, play trivia to unlock mystery ticket discounts, suggest tracks for the DJ, and connect with other verified attendees.
                </p>
            </div>

            <div class="ve-act-grid">
                <?php
                    $actMetrics = $activityMetrics ?? [];
                    $pollBadge = $actMetrics['polls']['badge'] ?? null;
                    $pollVotes = (int)($actMetrics['polls']['total_votes'] ?? 0);
                    $triviaBadge = $actMetrics['trivia']['badge'] ?? null;
                    $triviaPlays = (int)($actMetrics['trivia']['plays_count'] ?? 0);
                    $reqBadge = $actMetrics['requests']['badge'] ?? null;
                    $reqCount = (int)($actMetrics['requests']['total_requests'] ?? 0);
                    $wallBadge = $actMetrics['wall']['badge'] ?? null;
                    $wallPosts = (int)($actMetrics['wall']['total_posts'] ?? 0);
                ?>

                <!-- 1. Live Polls -->
                <a href="<?= route('event/' . $eventIdentifier . '/activities') ?>?tab=polls" class="ve-act-card">
                    <div class="ve-act-card-top">
                        <span class="ve-act-card-icon">🗳️</span>
                        <span class="ve-act-card-tag" style="color:#6366f1; background:rgba(99,102,241,0.1); border-color:rgba(99,102,241,0.25);">Free Daily Vote</span>
                    </div>
                    <h3 class="ve-act-card-name">Live Award Polls</h3>
                    <p class="ve-act-card-desc">Crown best dressed, pick DJ of the night, or decide stage choices with 1 free vote every 24 hours.</p>
                    
                    <div class="ve-act-engagement-badge <?= $pollVotes > 0 ? 'is-active' : '' ?>">
                        <span class="ve-act-engagement-dot <?= $pollVotes > 0 ? 'is-hot' : '' ?>"></span>
                        <span><?= e($pollBadge ?? 'Open for voting') ?></span>
                    </div>

                    <span class="ve-act-card-arrow">Cast Vote →</span>
                </a>

                <!-- 2. Trivia -->
                <a href="<?= route('event/' . $eventIdentifier . '/activities') ?>?tab=trivia" class="ve-act-card">
                    <div class="ve-act-card-top">
                        <span class="ve-act-card-icon">🧠</span>
                        <span class="ve-act-card-tag" style="color:#a855f7; background:rgba(168,85,247,0.1); border-color:rgba(168,85,247,0.25);">Win Promo Code</span>
                    </div>
                    <h3 class="ve-act-card-name">Trivia Challenge</h3>
                    <p class="ve-act-card-desc">Answer quick questions against the clock to instantly unlock secret ticket discount promo codes.</p>
                    
                    <div class="ve-act-engagement-badge <?= $triviaPlays > 0 ? 'is-active' : '' ?>">
                        <span class="ve-act-engagement-dot <?= $triviaPlays > 0 ? 'is-hot' : '' ?>"></span>
                        <span><?= e($triviaBadge ?? 'Win ticket discount') ?></span>
                    </div>

                    <span class="ve-act-card-arrow">Play Quiz →</span>
                </a>

                <!-- 3. DJ Requests -->
                <a href="<?= route('event/' . $eventIdentifier . '/activities') ?>?tab=requests" class="ve-act-card">
                    <div class="ve-act-card-top">
                        <span class="ve-act-card-icon">🎵</span>
                        <span class="ve-act-card-tag" style="color:#3b82f6; background:rgba(59,130,246,0.1); border-color:rgba(59,130,246,0.25);">Crowd Ranked</span>
                    </div>
                    <h3 class="ve-act-card-name">DJ Requests &amp; Q&amp;A</h3>
                    <p class="ve-act-card-desc">Tell the DJ what anthem to spin or submit questions for panel speakers. Top upvoted tracks get played!</p>
                    
                    <div class="ve-act-engagement-badge <?= $reqCount > 0 ? 'is-active' : '' ?>">
                        <span class="ve-act-engagement-dot <?= $reqCount > 0 ? 'is-hot' : '' ?>"></span>
                        <span><?= e($reqBadge ?? 'Suggest a song') ?></span>
                    </div>

                    <span class="ve-act-card-arrow">Suggest Song →</span>
                </a>

                <!-- 4. Hype Wall -->
                <a href="<?= route('event/' . $eventIdentifier . '/activities') ?>?tab=wall" class="ve-act-card">
                    <div class="ve-act-card-top">
                        <span class="ve-act-card-icon">📣</span>
                        <span class="ve-act-card-tag" style="color:#10b981; background:rgba(168,85,247,0.1); border-color:rgba(16,185,129,0.25);">Find Your Squad</span>
                    </div>
                    <h3 class="ve-act-card-name">Community Hype Wall</h3>
                    <p class="ve-act-card-desc">See who is attending, arrange rides and carpools, share outfit vibes, and build your festival squad.</p>
                    
                    <div class="ve-act-engagement-badge <?= $wallPosts > 0 ? 'is-active' : '' ?>">
                        <span class="ve-act-engagement-dot <?= $wallPosts > 0 ? 'is-hot' : '' ?>"></span>
                        <span><?= e($wallBadge ?? 'Start a discussion') ?></span>
                    </div>

                    <span class="ve-act-card-arrow">Join Wall →</span>
                </a>
            </div>

            <div class="ve-act-cta-bar">
                <div class="ve-act-cta-left">
                    <span class="ve-act-pulse"></span>
                    <span class="ve-act-cta-text">Interactive Lounge is open • Free for all attendees &amp; guests</span>
                </div>
                <a href="<?= route('event/' . $eventIdentifier . '/activities') ?>" class="ve-act-cta-btn">
                    <span>🚀</span> Enter The Activity Arena
                </a>
            </div>
        </section>
    <?php endif; ?>

    <!-- ═══ 3. Recurring Series Switcher (if applicable) ═══ -->
    <?php if (!empty($recurringOccurrences) && count($recurringOccurrences) > 1): ?>
        <div class="ve-series-card" id="series-date-selector">
            <div class="ve-series-head">
                <div>
                    <h3 class="ve-series-title">Event Series Dates</h3>
                    <p class="ve-series-sub">This event is held across multiple dates. Select an occurrence below:</p>
                </div>
                <span class="ve-badge"><?= count($recurringOccurrences) ?> Dates in Series</span>
            </div>
            <select id="series-occurrence-select" class="ve-series-select" onchange="if (this.value) window.location.href = this.value;">
                <?php foreach ($recurringOccurrences as $occ): 
                    $occTime = strtotime($occ['date']);
                    $isCurrentOcc = ($occ['id'] == $event->id);
                    $occUrl = event_url((object)$occ);
                    $label = 'Occurrence #' . ($occ['recurrence_sequence'] ?? 1) . ' — ' . date('D, M j, Y • g:i A', $occTime) . ($isCurrentOcc ? ' (Current)' : '');
                ?>
                    <option value="<?= e($occUrl) ?>" <?= $isCurrentOcc ? 'selected' : '' ?>>
                        <?= e($label) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    <?php endif; ?>

    <!-- ═══ 4. Box Office Admissions Counter ═══ -->
    <?php if ($isCompleted): ?>
        <div class="ve-completed-box">
            <span class="ve-concluded-icon">🏁</span>
            <h3 class="ve-concluded-title">Admission Sales Concluded</h3>
            <p class="ve-concluded-text">This gathering concluded on <?= date('F j, Y \a\t g:i A', strtotime($event->date)) ?>. We thank all patrons for attending.</p>
            <a href="<?= route('event/' . $eventIdentifier . '/feedback') ?>" class="vn-btn-primary ve-concluded-btn">
                ⭐ Rate &amp; Review This Event
            </a>
        </div>
    <?php else: ?>
        <form id="tickets-form" method="GET" action="{{ route('checkout/{event_id}', ['event_id' => $eventIdentifier]) }}">
            <input type="hidden" name="event_id" value="<?= $eventIdentifier ?>" />
            <input type="hidden" name="occ_id" value="<?= (int)($event->id ?? 0) ?>" />
            <?php if (!empty($event->recurrence_sequence)): ?>
                <input type="hidden" name="occ" value="<?= (int)$event->recurrence_sequence ?>" />
            <?php endif; ?>
            <?php if (!empty($refCode) || !empty($_GET['ref'])): ?>
                <input type="hidden" name="ref" value="<?= e($refCode ?? ($_GET['ref'] ?? '')) ?>" />
            <?php endif; ?>

            <div class="ve-box-office">
                <!-- Left: Ticket Tiers Desk -->
                <div class="ve-desk-card">
                    <?php if ($showCountdown): ?>
                        <!-- Clockwork Urgency Banner -->
                        <div class="ve-clockwork-card" id="ticket-countdown-banner" data-close-timestamp="<?= $salesCloseTimestamp ?>">
                            <div class="ve-clockwork-info">
                                <span class="ve-clockwork-icon">⏳</span>
                                <div>
                                    <span class="ve-clockwork-label">Box Office Closing Soon</span>
                                    <div class="ve-clockwork-sub">Reserve your passes before online sales close</div>
                                </div>
                            </div>
                            <div class="ve-clockwork-timer" id="countdown-timer">
                                <div class="ve-timer-box">
                                    <span class="ve-timer-val" id="cd-hours">--</span>
                                    <span class="ve-timer-lbl">Hours</span>
                                </div>
                                <span class="ve-timer-colon">:</span>
                                <div class="ve-timer-box">
                                    <span class="ve-timer-val" id="cd-minutes">--</span>
                                    <span class="ve-timer-lbl">Mins</span>
                                </div>
                                <span class="ve-timer-colon">:</span>
                                <div class="ve-timer-box">
                                    <span class="ve-timer-val" id="cd-seconds">--</span>
                                    <span class="ve-timer-lbl">Secs</span>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="ve-desk-header">
                        <div>
                            <span class="ve-desk-pre">Admissions Counter</span>
                            <h2 class="ve-desk-title">Select Admission Tiers</h2>
                        </div>
                        <span class="ve-badge">⚡ Instant QR Delivery</span>
                    </div>

                    <div class="ve-tier-list">
                        <?php if (empty($ticketTypes)): ?>
                            <p class="ve-empty-tiers-msg">No ticket tiers are currently available for this gathering.</p>
                        <?php else: ?>
                            <?php foreach ($ticketTypes as $tt):
                                $sold = (int)($tt['sold'] ?? 0);
                                $capacity = (int)($tt['capacity'] ?? 0);
                                $admits = max(1, (int)($tt['admits'] ?? 1));
                                $soldUnits = $admits > 1 ? (int)floor($sold / $admits) : $sold;
                                $remaining = max(0, $capacity - $soldUnits);
                                $isSoldOut = ($capacity > 0 && $remaining <= 0);
                            ?>
                                <div class="ve-tier-stub<?= $isSoldOut ? ' sold-out' : '' ?>"
                                     data-price="<?= (float)$tt['price'] ?>"
                                     data-label="<?= e($tt['name']) ?>"
                                     data-ticket-id="<?= $tt['id'] ?>">

                                    <!-- Ticket Information Header -->
                                    <div class="ve-tier-info">
                                        <div class="ve-tier-title-row">
                                            <h3 class="ve-tier-name"><?= e($tt['name']) ?></h3>
                                            <div class="ve-tier-badges">
                                                <?php if ($isSoldOut): ?>
                                                    <span class="ve-tier-badge ve-tier-badge--soldout">Sold Out</span>
                                                <?php elseif ($remaining <= 15): ?>
                                                    <span class="ve-tier-badge ve-tier-badge--urgency">🔥 <?= $remaining ?> left</span>
                                                <?php endif; ?>
                                                <?php if (!empty($tt['admits']) && (int)$tt['admits'] > 1): ?>
                                                    <span class="ve-tier-badge" style="background:rgba(99,102,241,0.12); color:#6366f1; border-color:rgba(99,102,241,0.3);">👥 Admits <?= (int)$tt['admits'] ?></span>
                                                <?php endif; ?>
                                                <?php if (!empty($tt['max_per_person'])): ?>
                                                    <span class="ve-tier-badge ve-tier-badge--limit">Max <?= (int)$tt['max_per_person'] ?>/person</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <?php if (!empty($tt['description'])): ?>
                                            <p class="ve-tier-desc"><?= e($tt['description']) ?></p>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Perforated separator for mobile & tablet -->
                                    <div class="ve-tier-divider" aria-hidden="true"></div>

                                    <!-- Bottom Action Row: Price & Quantity Stepper -->
                                    <div class="ve-tier-bottom-row">
                                        <div class="ve-tier-price-box">
                                            <span class="ve-price-lbl">Per Admission</span>
                                            <div class="ve-tier-price">
                                                <?= ((float)$tt['price'] > 0) ? format_price($tt['price']) : 'Free' ?>
                                            </div>
                                        </div>

                                        <div class="ve-stepper-wrap">
                                            <div class="ve-stepper">
                                                <button type="button" class="ve-stepper-btn" data-action="decrease" <?= $isSoldOut ? 'disabled' : '' ?> aria-label="Decrease quantity">−</button>
                                                <span class="ve-stepper-val" aria-live="polite">0</span>
                                                <input type="hidden" name="qty[<?= $tt['id'] ?>]" class="qty-input" value="0" data-max="<?= $remaining ?>" data-max-per-person="<?= !empty($tt['max_per_person']) ? (int)$tt['max_per_person'] : '' ?>">
                                                <button type="button" class="ve-stepper-btn" data-action="increase" <?= $isSoldOut ? 'disabled' : '' ?> aria-label="Increase quantity">+</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Right: Printed Ledger Receipt -->
                <aside class="ve-receipt-card">
                    <div class="ve-receipt-header">
                        <span class="ve-receipt-seal">✦ Admission Summary ✦</span>
                        <h3 class="ve-receipt-title">Order Ledger</h3>
                    </div>

                    <div class="ve-receipt-lines" id="summary-items">
                        <p class="ve-empty-summary-msg">
                            Select admission passes on the left to tally your order.
                        </p>
                    </div>

                    <div class="ve-receipt-total">
                        <span class="ve-total-lbl">Total Amount</span>
                        <strong class="ve-total-val" id="order-total"><?= format_price(0) ?></strong>
                    </div>

                    <button type="submit" id="checkout-submit-btn" class="ve-checkout-btn" disabled>
                        <span>Reserve Admissions</span>
                        <span>→</span>
                    </button>
                </aside>
            </div>

            <!-- Mobile Floating Bottom Checkout Bar (Only visible when tickets selected on mobile) -->
            <div id="ve-mobile-bar" class="ve-mobile-bar" aria-live="polite">
                <div class="ve-mobile-bar-summary">
                    <span class="ve-mobile-bar-qty" id="mobile-bar-qty">0 Passes Selected</span>
                    <strong class="ve-mobile-bar-total" id="mobile-bar-total"><?= format_price(0) ?></strong>
                </div>
                <button type="submit" class="ve-mobile-bar-btn" id="mobile-bar-btn">
                    <span>Reserve Tickets</span>
                    <span>→</span>
                </button>
            </div>
        </form>
    <?php endif; ?>

    <!-- ═══ 5. Narrative Description & Special Guests ═══ -->
    <section class="ve-narrative-card">
        <h2 class="ve-narrative-head">The Full Chronicle</h2>
        <div class="ve-narrative-body">
            <?= nl2br(e($event->description)) ?>
        </div>

        <?php
        $validGuests = [];
        if (!empty($eventGuests)) {
            foreach ($eventGuests as $guest) {
                $name = !empty($guest->name) ? $guest->name : ($guest['name'] ?? '');
                if (!empty(trim((string)$name))) $validGuests[] = $guest;
            }
        }
        ?>
        <?php if (!empty($validGuests)): ?>
            <div class="ve-guests-section">
                <h3 class="ve-guests-title">
                    ✦ Honored Guests &amp; Performers
                </h3>
                <div class="ve-guests-grid">
                    <?php foreach ($validGuests as $guest):
                        $gName = !empty($guest->name) ? (string)$guest->name : (string)($guest['name'] ?? '');
                        $gRole = !empty($guest->role) ? (string)$guest->role : (string)($guest['role'] ?? '');
                    ?>
                        <div class="ve-guest-item">
                            <div class="ve-host-avatar ve-guest-avatar">
                                <?= strtoupper(substr($gName, 0, 1)) ?>
                            </div>
                            <div class="ve-min-w-0">
                                <strong class="ve-guest-name"><?= e($gName) ?></strong>
                                <?php if (!empty($gRole)): ?>
                                    <span class="ve-guest-role"><?= e($gRole) ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Admissions Policy Strip -->
        <div class="ve-policy-strip">
            <div><strong>✦ Gate Policy:</strong> Present your scannable digital QR pass upon arrival for entry verification.</div>
            <div><strong>✦ Direct Settlement:</strong> Ticket funds are disbursed securely to the event host upon conclusion.</div>
        </div>
    </section>

</div>

<!-- ═══ Interactive Ticket & Order Calculation JS ═══ -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    var rows = document.querySelectorAll('.ve-tier-stub');
    var summaryItems = document.getElementById('summary-items');
    var orderTotal = document.getElementById('order-total');
    var submitBtn = document.getElementById('checkout-submit-btn');

    // Mobile bar elements
    var mobileBar = document.getElementById('ve-mobile-bar');
    var mobileBarQty = document.getElementById('mobile-bar-qty');
    var mobileBarTotal = document.getElementById('mobile-bar-total');

    var currencySymbol = '<?= e(get_currency_symbol()) ?>';
    function formatCurrencyJS(amount) {
        return currencySymbol + Number(amount || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function updateSummary() {
        var total = 0;
        var totalQty = 0;
        var itemsHtml = '';

        rows.forEach(function(row) {
            var price = parseFloat(row.getAttribute('data-price')) || 0;
            var label = row.getAttribute('data-label') || 'Ticket';
            var qtyInput = row.querySelector('.qty-input');
            var qty = parseInt(qtyInput ? qtyInput.value : 0, 10) || 0;

            if (qty > 0) {
                row.classList.add('is-selected');
                var subtotal = price * qty;
                total += subtotal;
                totalQty += qty;
                itemsHtml += '<div class="ve-receipt-row"><span>' + qty + 'x ' + label + '</span><strong>' + (subtotal > 0 ? formatCurrencyJS(subtotal) : 'Free') + '</strong></div>';
            } else {
                row.classList.remove('is-selected');
            }
        });

        if (totalQty === 0) {
            if (summaryItems) summaryItems.innerHTML = '<p class="ve-empty-summary-msg">Select admission passes on the left to tally your order.</p>';
            if (orderTotal) orderTotal.textContent = formatCurrencyJS(0);
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.textContent = 'Reserve Admissions →';
            }
            if (mobileBar) {
                mobileBar.classList.remove('is-visible');
            }
            if (mobileBarTotal) mobileBarTotal.textContent = formatCurrencyJS(0);
        } else {
            var formattedTotal = (total > 0) ? formatCurrencyJS(total) : 'Free';
            if (summaryItems) summaryItems.innerHTML = itemsHtml;
            if (orderTotal) orderTotal.textContent = formattedTotal;
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.textContent = (total > 0) ? 'Reserve (' + totalQty + ') • ' + formattedTotal + ' →' : 'Reserve Free Passes (' + totalQty + ') →';
            }
            if (mobileBar) {
                mobileBar.classList.add('is-visible');
                if (mobileBarQty) mobileBarQty.textContent = totalQty + (totalQty === 1 ? ' Pass Selected' : ' Passes Selected');
                if (mobileBarTotal) mobileBarTotal.textContent = formattedTotal;
            }
        }
    }

    rows.forEach(function(row) {
        var decreaseBtn = row.querySelector('[data-action="decrease"]');
        var increaseBtn = row.querySelector('[data-action="increase"]');
        var qtyVal = row.querySelector('.ve-stepper-val');
        var qtyInput = row.querySelector('.qty-input');
        var maxCapacity = parseInt(qtyInput ? qtyInput.getAttribute('data-max') : 99, 10) || 99;
        var maxPerPerson = qtyInput ? qtyInput.getAttribute('data-max-per-person') : null;
        maxPerPerson = maxPerPerson ? parseInt(maxPerPerson, 10) : null;
        var effectiveMax = (maxPerPerson && maxPerPerson > 0) ? Math.min(maxCapacity, maxPerPerson) : maxCapacity;

        if (decreaseBtn && increaseBtn && qtyVal && qtyInput) {
            decreaseBtn.addEventListener('click', function () {
                var current = parseInt(qtyInput.value, 10) || 0;
                if (current > 0) {
                    current--;
                    qtyInput.value = current;
                    qtyVal.textContent = current;
                    updateSummary();
                }
            });

            increaseBtn.addEventListener('click', function () {
                var current = parseInt(qtyInput.value, 10) || 0;
                if (current < effectiveMax) {
                    current++;
                    qtyInput.value = current;
                    qtyVal.textContent = current;
                    updateSummary();
                } else if (maxPerPerson && maxPerPerson > 0 && current >= maxPerPerson) {
                    alert('Limit reached: Maximum ' + maxPerPerson + ' ticket(s) per person for this tier.');
                }
            });
        }
    });

    /* ── Live Countdown Timer ── */
    var countdownBanner = document.getElementById('ticket-countdown-banner');
    if (countdownBanner) {
        var closeTimestamp = parseInt(countdownBanner.getAttribute('data-close-timestamp'), 10) * 1000;
        var cdHours = document.getElementById('cd-hours');
        var cdMinutes = document.getElementById('cd-minutes');
        var cdSeconds = document.getElementById('cd-seconds');

        function tick() {
            var diff = closeTimestamp - Date.now();
            if (diff <= 0) {
                if (cdHours) cdHours.textContent = '00';
                if (cdMinutes) cdMinutes.textContent = '00';
                if (cdSeconds) cdSeconds.textContent = '00';
                return;
            }
            var secs = Math.floor(diff / 1000);
            var h = Math.floor(secs / 3600);
            var m = Math.floor((secs % 3600) / 60);
            var s = secs % 60;
            if (cdHours) cdHours.textContent = String(h).padStart(2, '0');
            if (cdMinutes) cdMinutes.textContent = String(m).padStart(2, '0');
            if (cdSeconds) cdSeconds.textContent = String(s).padStart(2, '0');
        }
        tick();
        setInterval(tick, 1000);
    }

    /* ── Copy Link ── */
    var copyBtn = document.getElementById('copy-share-link');
    var copyText = document.getElementById('copy-btn-text');
    if (copyBtn) {
        copyBtn.addEventListener('click', function() {
            var url = '<?= addslashes($shareUrl) ?>';
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(url).then(function() {
                    if (copyText) copyText.textContent = 'Copied to Clipboard!';
                    setTimeout(function() { if (copyText) copyText.textContent = 'Copy Event Link'; }, 2000);
                });
            } else {
                var ta = document.createElement('textarea');
                ta.value = url;
                document.body.appendChild(ta);
                ta.select();
                try {
                    document.execCommand('copy');
                    if (copyText) copyText.textContent = 'Copied!';
                    setTimeout(function() { if (copyText) copyText.textContent = 'Copy Event Link'; }, 2000);
                } catch(e) {}
                document.body.removeChild(ta);
            }
        });
    }
});
</script>
@endsection
