<?php

/**
 * Home / Welcome Page — Vintage Broadsheet Edition (Comprehensive & Enhanced)
 *
 * Immersive vintage editorial broadsheet design featuring antique parchment tones,
 * authentic ticket-stub cards, letterpress typography ('Instrument Serif'),
 * tactile brass search console, comprehensive platform education, interactive FAQ,
 * city exploration guides, host superpowers, and responsive touch-first navigation.
 *
 * @package Eventlyy
 * @subpackage Views/Home
 * @author Celio Natti
 * @version 5.0.0
 */

$siteTitle = site_setting('site_title', 'Eventlyy');
?>

@extends('layouts/main')

@section('title')
<?= e($title ?? ($siteTitle . ' — The Curated Journal of Live Experiences, Concerts & Admissions')) ?>
@endsection

@section('content')
<div class="vn-wrapper">

    <!-- ═══ 1. Vintage Masthead Hero Section ═══ -->
    <header class="vn-hero">
        <div class="vn-hero-inner">
            <span class="vn-stamp-badge">
                <span>✦</span> The Curated Guide to What's On <span>✦</span>
            </span>

            <h1 class="vn-hero-title">
                Find your next obsession. <em>Live</em> in the moment.
            </h1>

            <p class="vn-hero-subtitle">
                Concerts, secret DJ sets, creative pop-ups, and game-changing summits. Instant scannable QR passes on lock, backed by verified direct host payouts and flexible layaway plans.
            </p>

            <!-- Bespoke Search Concierge Command Bar Trigger -->
            <?php
                $hasActiveFilters = !empty($search) || !empty($location) || (!empty($category) && strtolower($category) !== 'all');
            ?>
            <div class="vn-search-trigger-box">
                <button type="button" class="vn-search-trigger" id="vn-open-search-modal" aria-haspopup="dialog" aria-controls="vn-search-modal" aria-label="Open search and discovery modal">
                    <div class="vn-trigger-left">
                        <span class="vn-trigger-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="11" cy="11" r="8"></circle>
                                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                            </svg>
                        </span>
                        <div class="vn-trigger-text">
                            <?php if ($hasActiveFilters): ?>
                                <span class="vn-trigger-active-summary">
                                    <?= !empty($search) ? '“' . e($search) . '”' : 'Refined Search' ?>
                                    <?= !empty($location) ? ' &bull; 📍 ' . e($location) : '' ?>
                                    <?= (!empty($category) && strtolower($category) !== 'all') ? ' &bull; 🏷️ ' . e($category) : '' ?>
                                </span>
                            <?php else: ?>
                                <span class="vn-trigger-placeholder">Search vibes, artists, festivals, spots...</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="vn-trigger-right">
                        <span class="vn-trigger-loc-tag" title="Region filter">
                            <span class="vn-loc-dot"></span>
                            <span><?= !empty($location) ? e($location) : 'All Regions' ?></span>
                        </span>
                        <div class="vn-trigger-filter-cta">
                            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <line x1="4" y1="21" x2="4" y2="14"></line>
                                <line x1="4" y1="10" x2="4" y2="3"></line>
                                <line x1="12" y1="21" x2="12" y2="12"></line>
                                <line x1="12" y1="8" x2="12" y2="3"></line>
                                <line x1="20" y1="21" x2="20" y2="16"></line>
                                <line x1="20" y1="12" x2="20" y2="3"></line>
                                <line x1="1" y1="14" x2="7" y2="14"></line>
                                <line x1="9" y1="8" x2="15" y2="8"></line>
                                <line x1="17" y1="16" x2="23" y2="16"></line>
                            </svg>
                            <span>Filter</span>
                            <kbd class="vn-kbd-hint" aria-hidden="true">⌘K</kbd>
                        </div>
                    </div>
                </button>

                <?php if ($hasActiveFilters): ?>
                    <a href="{{ route('/') }}" class="vn-trigger-reset-pill" title="Reset all active search filters" aria-label="Clear active filters">
                        <span>✕</span>
                        <span>Reset</span>
                    </a>
                <?php endif; ?>
            </div>

            <!-- Active Filter Badges Bar for Mobile & Desktop -->
            <?php if ($hasActiveFilters): ?>
                <div class="vn-active-filters-bar" role="region" aria-label="Active filters summary">
                    <span class="vn-active-filters-label">Active:</span>
                    <div class="vn-active-pills">
                        <?php if (!empty($search)): ?>
                            <?php 
                                $clearSearchUrl = route('/') . '?' . http_build_query(array_filter(['location' => $location ?? null, 'category' => $category ?? null]));
                            ?>
                            <a href="<?= $clearSearchUrl ?>" class="vn-active-pill" title="Remove query filter">
                                <span>Query: "<strong><?= e($search) ?></strong>"</span>
                                <span class="vn-pill-x">✕</span>
                            </a>
                        <?php endif; ?>
                        <?php if (!empty($location)): ?>
                            <?php 
                                $clearLocUrl = route('/') . '?' . http_build_query(array_filter(['q' => $search ?? null, 'category' => $category ?? null]));
                            ?>
                            <a href="<?= $clearLocUrl ?>" class="vn-active-pill" title="Remove location filter">
                                <span>📍 <strong><?= e($location) ?></strong></span>
                                <span class="vn-pill-x">✕</span>
                            </a>
                        <?php endif; ?>
                        <?php if (!empty($category) && strtolower($category) !== 'all'): ?>
                            <?php 
                                $clearCatUrl = route('/') . '?' . http_build_query(array_filter(['q' => $search ?? null, 'location' => $location ?? null]));
                            ?>
                            <a href="<?= $clearCatUrl ?>" class="vn-active-pill" title="Remove category filter">
                                <span>🏷️ <strong><?= e($category) ?></strong></span>
                                <span class="vn-pill-x">✕</span>
                            </a>
                        <?php endif; ?>
                        <a href="{{ route('/') }}" class="vn-active-pill vn-active-pill--clear" title="Clear all filters">
                            <span>Clear All</span>
                        </a>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Fast-Jump Platform Navigation Anchors -->
            <nav class="vn-quick-anchors" aria-label="Quick page navigation">
                <a href="#events-section" class="vn-anchor-pill">🎟️ The Lineup</a>
                <a href="#how-it-works" class="vn-anchor-pill">⚡ How It Works</a>
                <a href="#why-eventlyy" class="vn-anchor-pill">🛡️ Platform Perks</a>
                <a href="#cities-section" class="vn-anchor-pill">📍 Cities</a>
                <a href="#host-studio" class="vn-anchor-pill">🎪 For Hosts</a>
                <a href="#faqs-section" class="vn-anchor-pill">❓ FAQs</a>
            </nav>

            <!-- Heritage Proof Metrics Strip -->
            <div class="vn-metrics-strip">
                <div class="vn-metric-item"><span class="fleuron">✦</span> 50K+ Passes Secured</div>
                <div class="vn-metric-item"><span class="fleuron">✦</span> Instant QR Check-in</div>
                <div class="vn-metric-item"><span class="fleuron">✦</span> Layaway Partial Pay</div>
                <div class="vn-metric-item"><span class="fleuron">✦</span> Direct Creator Escrow Payouts</div>
            </div>
        </div>
    </header>

    <!-- ═══ 2. Category Seals Navigation ═══ -->
    <section class="vn-categories-section">
        <div class="vn-section-head">
            <div class="vn-heading-wrap">
                <span class="vn-category-label">Browse by Vibe</span>
                <h2 class="vn-section-title">What's the move?</h2>
            </div>
            <div class="vn-section-sub">Pick your scene and see what's popping this week</div>
        </div>

        <div class="vn-chips-scroll-wrap">
            <nav class="vn-chips-row" id="vn-chips-row" aria-label="Event category seals">
            <?php 
            $catIcons = [
                'All' => '✦',
                'Concerts' => '🎵',
                'Tech' => '⚡',
                'Comedy' => '🎭',
                'Sports' => '🏆',
                'Theater' => '🎪',
                'Family' => '🌿',
                'Art & Wine' => '🍷',
            ];
            foreach ($categories ?? [] as $cat):
                $isCatActive = (empty($category) && $cat === 'All') || (strtolower($category ?? '') === strtolower($cat));
                $catUrl = route('/') . '?category=' . urlencode($cat);
                if (!empty($search)) $catUrl .= '&q=' . urlencode($search);
                if (!empty($location)) $catUrl .= '&location=' . urlencode($location);
                $icon = $catIcons[$cat] ?? '✦';
            ?>
                <a href="<?= $catUrl ?>" class="vn-chip <?= $isCatActive ? 'active' : '' ?>">
                    <span><?= $icon ?></span>
                    <span><?= e($cat) ?></span>
                </a>
            <?php endforeach; ?>
            </nav>
        </div>
    </section>

    <!-- ═══ 3. Featured Highlights Carousel ═══ -->
    <?php 
        $isFiltered = !empty($search) || !empty($location) || (!empty($category) && strtolower($category) !== 'all') || ($page ?? 1) > 1;
        $hasFeatured = !empty($featuredEvents) && count($featuredEvents) >= 1;
        $multiFeatured = !empty($featuredEvents) && count($featuredEvents) > 1;
    ?>
    <?php if ($hasFeatured && !$isFiltered): ?>
        <section class="vn-featured-section">
            <div class="vn-section-head">
                <div class="vn-heading-wrap">
                    <span class="vn-category-label">Main Character Energy</span>
                    <h2 class="vn-section-title">Hot right now 🔥</h2>
                </div>
                <div class="vn-section-sub">The drops and headliners everyone's tapping into</div>
            </div>

            <div class="vn-carousel-wrap" id="vn-carousel-wrap">
                <?php if ($multiFeatured): ?>
                    <button type="button" class="vn-carousel-btn vn-carousel-btn--prev" id="vn-carousel-prev" aria-label="Previous Highlight">
                        <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"></polyline></svg>
                    </button>
                <?php endif; ?>

                <div class="vn-carousel-track" id="vn-carousel-track">
                    <?php foreach ($featuredEvents as $idx => $fe):
                        $feUrl = event_url($fe);
                        $feImg = !empty($fe['image']) ? $fe['image'] : 'https://images.unsplash.com/photo-1501281668745-f7f57925c3b4?w=1200&q=80&auto=format&fit=crop';
                        $feDate = date('M d, Y', strtotime($fe['date']));
                        $feMinPrice = isset($fe['min_price']) && $fe['min_price'] !== null ? format_price($fe['min_price']) : 'Free Entry';
                    ?>
                        <a href="<?= $feUrl ?>" class="vn-featured-stub" data-card-idx="<?= $idx ?>">
                            <div class="vn-featured-img-box">
                                <img src="<?= e($feImg) ?>" alt="<?= e($fe['name']) ?>" class="vn-featured-img" loading="lazy" decoding="async">
                            </div>
                            <div class="vn-featured-details">
                                <div>
                                    <div class="vn-stub-tag-row">
                                        <span class="vn-tag vn-tag--brass">★ Trending Drop</span>
                                        <span class="vn-tag"><?= e($fe['category'] ?? 'Event') ?></span>
                                    </div>
                                    <h3 class="vn-featured-title"><?= e($fe['name']) ?></h3>
                                    <div class="vn-meta-list">
                                        <div class="vn-meta-item">
                                            <span>📅</span> <strong><?= $feDate ?></strong>
                                        </div>
                                        <div class="vn-meta-item">
                                            <span>📍</span> <span><?= e($fe['location']) ?></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="vn-stub-footer">
                                    <div class="vn-price-stamp">
                                        <span class="vn-price-lbl">Tickets from</span>
                                        <span class="vn-price-val"><?= $feMinPrice ?></span>
                                    </div>
                                    <span class="vn-action-badge">Claim Pass →</span>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>

                <?php if ($multiFeatured): ?>
                    <button type="button" class="vn-carousel-btn vn-carousel-btn--next" id="vn-carousel-next" aria-label="Next Highlight">
                        <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
                    </button>
                <?php endif; ?>
            </div>

            <?php if ($multiFeatured): ?>
                <div class="vn-carousel-dots" id="vn-carousel-dots" role="tablist" aria-label="Featured highlight indicators">
                    <?php foreach ($featuredEvents as $idx => $fe): ?>
                        <button type="button" class="vn-carousel-dot <?= $idx === 0 ? 'active' : '' ?>" data-index="<?= $idx ?>" aria-label="Slide <?= $idx + 1 ?>"></button>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    <?php endif; ?>

    <!-- ═══ 4. All Events Admission Roster ═══ -->
    <?php
        $events = $events ?? [];
        $total = $total ?? count($events);
        $page = isset($page) ? max(1, (int) $page) : 1;
        $totalPages = isset($totalPages) ? max(1, (int) $totalPages) : 1;

        $headingTitle = 'Coming up on your radar';
        $headingNote = 'Lock in your passes before doors close';

        if (!empty($search) && !empty($location)) {
            $headingTitle = 'Drops for "' . e($search) . '" in ' . e($location);
        } elseif (!empty($search)) {
            $headingTitle = 'Drops for "' . e($search) . '"';
        } elseif (!empty($location)) {
            $headingTitle = 'What’s happening in ' . e($location);
        } elseif (!empty($category) && strtolower($category) !== 'all') {
            $headingTitle = e($category) . ' vibes';
        }
    ?>
    <section class="vn-roster-section" id="events-section">
        <div class="vn-section-head">
            <div class="vn-heading-wrap">
                <span class="vn-category-label">The Lineup</span>
                <h2 class="vn-section-title"><?= $headingTitle ?></h2>
                <div class="vn-section-sub"><?= $headingNote ?></div>
            </div>

            <div style="font-family: var(--vn-font-mono); font-size: 0.8rem; color: var(--vn-ink-muted);">
                Showing <?= count($events) ?> of <?= $total ?> drop<?= $total !== 1 ? 's' : '' ?>
                <?= $totalPages > 1 ? " &bull; Page {$page} of {$totalPages}" : '' ?>
            </div>
        </div>

        <div class="vn-events-grid">
            <?php if (empty($events)): ?>
                <div class="vn-empty-card">
                    <span class="vn-empty-stamp">👀</span>
                    <h3 class="vn-empty-title">Nothing on the feed yet</h3>
                    <p class="vn-empty-text">
                        No upcoming drops matching those exact filters rn. Switch up your search or check out the full lineup.
                    </p>
                    <a href="{{ route('/') }}" class="vn-btn-primary">
                        Show Me Everything →
                    </a>
                </div>
            <?php else: ?>
                <?php foreach ($events as $ev):
                    $evUrl = event_url($ev);
                    $evImg = !empty($ev['image']) ? $ev['image'] : 'https://images.unsplash.com/photo-1501281668745-f7f57925c3b4?w=1200&q=80&auto=format&fit=crop';
                    $minPrice = isset($ev['min_price']) && $ev['min_price'] !== null ? format_price($ev['min_price']) : 'Free Entry';
                    $evDate = date('M d, Y • g:i A', strtotime($ev['date']));
                    $passId = 'PASS-' . strtoupper(substr(md5($ev['id'] . ($ev['date'] ?? '')), 0, 6));
                ?>
                    <!-- Ticket Stub Card -->
                    <article class="vn-ticket-card">
                        <div class="vn-card-thumb-wrap">
                            <img src="<?= e($evImg) ?>" alt="<?= e($ev['name']) ?>" class="vn-card-thumb" loading="lazy" decoding="async">
                            <div class="vn-card-top-tags">
                                <span class="vn-card-tag"><?= e($ev['category'] ?? 'Event') ?></span>
                                <span class="vn-card-price-pill"><?= $minPrice ?></span>
                            </div>
                        </div>

                        <div class="vn-card-body">
                            <div class="vn-card-content">
                                <div class="vn-card-date-stamp">
                                    <span>🗓️</span> <span><?= $evDate ?></span>
                                </div>
                                <h3 class="vn-card-title" title="<?= e($ev['name']) ?>">
                                    <a href="<?= $evUrl ?>" class="vn-card-link">
                                        <?= e($ev['name']) ?>
                                    </a>
                                </h3>
                                <div class="vn-card-location" title="<?= e($ev['location']) ?>">
                                    <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                        <circle cx="12" cy="10" r="3"></circle>
                                    </svg>
                                    <span><?= e($ev['location']) ?></span>
                                </div>
                            </div>

                            <div class="vn-card-footer">
                                <span class="vn-card-pass-id"><?= $passId ?></span>
                                <a href="<?= $evUrl ?>" class="vn-card-cta-btn">
                                    <span>Get Tickets</span>
                                    <span>→</span>
                                </a>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- ═══ 5. Vintage Windowed Pagination ═══ -->
        <?php if ($totalPages > 1): ?>
            <?php
                $queryParams = [];
                if (!empty($search)) $queryParams['q'] = $search;
                if (!empty($location)) $queryParams['location'] = $location;
                if (!empty($category)) $queryParams['category'] = $category;

                $buildPageUrl = function($p) use ($queryParams) {
                    $queryParams['page'] = $p;
                    return route('/') . '?' . http_build_query($queryParams);
                };

                $range = pagination_range($page, $totalPages);
            ?>
            <nav class="vn-pagination" aria-label="Registry pagination">
                <?php if ($page > 1): ?>
                    <a href="<?= $buildPageUrl($page - 1) ?>" class="vn-page-btn" aria-label="Previous page">&laquo; Prev</a>
                <?php else: ?>
                    <span class="vn-page-btn disabled" aria-disabled="true">&laquo; Prev</span>
                <?php endif; ?>

                <?php foreach ($range as $r): ?>
                    <?php if ($r === '...'): ?>
                        <span class="vn-page-dots">&hellip;</span>
                    <?php elseif ($r == $page): ?>
                        <span class="vn-page-btn active" aria-current="page"><?= $r ?></span>
                    <?php else: ?>
                        <a href="<?= $buildPageUrl($r) ?>" class="vn-page-btn" aria-label="Page <?= $r ?>"><?= $r ?></a>
                    <?php endif; ?>
                <?php endforeach; ?>

                <?php if ($page < $totalPages): ?>
                    <a href="<?= $buildPageUrl($page + 1) ?>" class="vn-page-btn" aria-label="Next page">Next &raquo;</a>
                <?php else: ?>
                    <span class="vn-page-btn disabled" aria-disabled="true">Next &raquo;</span>
                <?php endif; ?>
            </nav>
        <?php endif; ?>
    </section>

    <!-- ═══ 6. How Eventlyy Works (3-Step Effortless Attendee Journey) ═══ -->
    <section class="vn-guide-section" id="how-it-works">
        <div class="vn-section-head" style="text-align: center; max-width: 680px; margin: 0 auto 2.5rem;">
            <span class="vn-host-eyebrow">✦ Effortless Live Access ✦</span>
            <h2 class="vn-section-title">How Eventlyy Works for You</h2>
            <p class="vn-section-sub">We’ve removed the friction from buying event tickets. No fake passes, no gate anxiety, and zero clunky PDF printing.</p>
        </div>

        <div class="vn-steps-grid">
            <!-- Step 1 -->
            <article class="vn-step-card">
                <div class="vn-step-header">
                    <span class="vn-step-number">01</span>
                    <span class="vn-step-icon">🔎</span>
                </div>
                <h3 class="vn-step-title">Discover Authentic Experiences</h3>
                <p class="vn-step-desc">
                    Find verified concerts, underground DJ sets, art festivals, comedy specials, and networking summits with transparent pricing and live venue details.
                </p>
                <div class="vn-step-badge">
                    <span>✓</span> Verified Host Badges
                </div>
            </article>

            <!-- Step 2 -->
            <article class="vn-step-card highlight">
                <div class="vn-step-header">
                    <span class="vn-step-number">02</span>
                    <span class="vn-step-icon">💳</span>
                </div>
                <h3 class="vn-step-title">Book In Full or Use Layaway</h3>
                <p class="vn-step-desc">
                    Secure your spot instantly via Card, Apple Pay, or Bank Transfer. Eyeing a premium VIP table? Put down a deposit with <strong>Layaway</strong> and clear it before showtime.
                </p>
                <div class="vn-step-badge">
                    <span>✓</span> Split Payments Welcome
                </div>
            </article>

            <!-- Step 3 -->
            <article class="vn-step-card">
                <div class="vn-step-header">
                    <span class="vn-step-number">03</span>
                    <span class="vn-step-icon">⚡</span>
                </div>
                <h3 class="vn-step-title">Instant Digital Pass &amp; Fast Gate Check-in</h3>
                <p class="vn-step-desc">
                    Your scannable QR ticket lands in your Email and WhatsApp instantly with a 4-digit security PIN. Just flash your screen at the door and walk straight in.
                </p>
                <div class="vn-step-badge">
                    <span>✓</span> Scans in Under 1s
                </div>
            </article>
        </div>
    </section>

    <!-- ═══ 7. Platform Superpowers Grid (Why Eventlyy?) ═══ -->
    <section class="vn-superpowers-section" id="why-eventlyy">
        <div class="vn-section-head">
            <div class="vn-heading-wrap">
                <span class="vn-category-label">Engineered for Live Culture</span>
                <h2 class="vn-section-title">Everything you need in a modern ticketing platform</h2>
            </div>
            <div class="vn-section-sub">Built with fraud-proof cryptography, seamless payments, and crowd management tools</div>
        </div>

        <div class="vn-perks-grid">
            <div class="vn-perk-card">
                <div class="vn-perk-icon-wrap">⚡</div>
                <h4 class="vn-perk-name">Instant Optical QR Delivery</h4>
                <p class="vn-perk-body">
                    Tickets are delivered in milliseconds to your Email, WhatsApp, and dashboard with high-contrast QR codes designed to scan in low-light night venues.
                </p>
            </div>

            <div class="vn-perk-card">
                <div class="vn-perk-icon-wrap">💳</div>
                <h4 class="vn-perk-name">Layaway Partial Payments</h4>
                <p class="vn-perk-body">
                    Don't let payday timing stop you from locking down Early Bird or VIP passes. Pay a flexible deposit today and clear the rest anytime before showtime.
                </p>
            </div>

            <div class="vn-perk-card">
                <div class="vn-perk-icon-wrap">🛡️</div>
                <h4 class="vn-perk-name">Cryptographic Anti-Counterfeit</h4>
                <p class="vn-perk-body">
                    Every ticket is cryptographically stamped with unique digital signatures and an optional 4-digit Security PIN. No duplicate gate entries, ever.
                </p>
            </div>

            <div class="vn-perk-card">
                <div class="vn-perk-icon-wrap">📱</div>
                <h4 class="vn-perk-name">Hardware-Free Phone Scanner</h4>
                <p class="vn-perk-body">
                    Event organizers and bouncers can check in thousands of guests using any standard smartphone camera. No expensive barcode scanners to rent.
                </p>
            </div>

            <div class="vn-perk-card">
                <div class="vn-perk-icon-wrap">🔁</div>
                <h4 class="vn-perk-name">1-Click Safe Ticket Transfers</h4>
                <p class="vn-perk-body">
                    Can't make the event anymore? Transfer your ticket directly to a friend with 1 tap. Fresh passes are issued to them while your old barcode is retired.
                </p>
            </div>

            <div class="vn-perk-card">
                <div class="vn-perk-icon-wrap">💰</div>
                <h4 class="vn-perk-name">Promoter &amp; Affiliate Hub</h4>
                <p class="vn-perk-body">
                    Track promoter sales, unique clicks, and automated commissions in real time with unique tracking links, 1-click WhatsApp sharing, and CSV exports.
                </p>
            </div>
        </div>
    </section>

    <!-- ═══ 8. Popular Cities Quick Navigation ═══ -->
    <section class="vn-cities-section" id="cities-section">
        <div class="vn-section-head">
            <div class="vn-heading-wrap">
                <span class="vn-category-label">Cultural Hubs</span>
                <h2 class="vn-section-title">Explore by City &amp; Scene</h2>
            </div>
            <div class="vn-section-sub">Tap into the hottest events happening across major destinations</div>
        </div>

        <div class="vn-cities-grid">
            <a href="{{ route('/') }}?location=Lagos#events-section" class="vn-city-card">
                <div class="vn-city-img-box" style="background-image: url('https://images.unsplash.com/photo-1577495508048-b635879837f1?w=800&q=80&auto=format&fit=crop');"></div>
                <div class="vn-city-overlay">
                    <span class="vn-city-tag">Hotspot</span>
                    <h3 class="vn-city-name">Lagos</h3>
                    <p class="vn-city-sub">Concerts, Beach Raves, Tech Summits &amp; Afrobeats</p>
                    <span class="vn-city-arrow">Explore Lagos Drops →</span>
                </div>
            </a>

            <a href="{{ route('/') }}?location=Abuja#events-section" class="vn-city-card">
                <div class="vn-city-img-box" style="background-image: url('https://images.unsplash.com/photo-1543731068-7e0f5beff43a?w=800&q=80&auto=format&fit=crop');"></div>
                <div class="vn-city-overlay">
                    <span class="vn-city-tag">Capital Vibe</span>
                    <h3 class="vn-city-name">Abuja</h3>
                    <p class="vn-city-sub">Live Acoustic Sets, Galas, Outdoor Festivals &amp; Comedy</p>
                    <span class="vn-city-arrow">Explore Abuja Drops →</span>
                </div>
            </a>

            <a href="{{ route('/') }}?location=Port+Harcourt#events-section" class="vn-city-card">
                <div class="vn-city-img-box" style="background-image: url('https://images.unsplash.com/photo-1470225620780-dba8ba36b745?w=800&q=80&auto=format&fit=crop');"></div>
                <div class="vn-city-overlay">
                    <span class="vn-city-tag">Vibrant</span>
                    <h3 class="vn-city-name">Port Harcourt</h3>
                    <p class="vn-city-sub">Music Carnivals, Campus Events &amp; Food Fairs</p>
                    <span class="vn-city-arrow">Explore PH Drops →</span>
                </div>
            </a>

            <a href="{{ route('/') }}?location=Ibadan#events-section" class="vn-city-card">
                <div class="vn-city-img-box" style="background-image: url('https://images.unsplash.com/photo-1514525253161-7a46d19cd819?w=800&q=80&auto=format&fit=crop');"></div>
                <div class="vn-city-overlay">
                    <span class="vn-city-tag">Historic</span>
                    <h3 class="vn-city-name">Ibadan</h3>
                    <p class="vn-city-sub">Creative Meetups, Art Pop-ups &amp; Campus Raves</p>
                    <span class="vn-city-arrow">Explore Ibadan Drops →</span>
                </div>
            </a>
        </div>
    </section>

    <!-- ═══ 9. Host & Creator Studio Spotlight ═══ -->
    <section class="vn-creator-studio-section" id="host-studio">
        <div class="vn-studio-card">
            <div class="vn-studio-content">
                <span class="vn-host-eyebrow">✦ For Creators, Collectives &amp; Event Organizers ✦</span>
                <h2 class="vn-studio-title">Turn Your Creative Vision Into a Sold-Out Reality</h2>
                <p class="vn-studio-desc">
                    Whether you're organizing an intimate 50-person acoustic session or a 10,000-capacity festival, Eventlyy gives you the tools to pack the room and keep 100% control of your revenue.
                </p>

                <div class="vn-studio-feature-list">
                    <div class="vn-s-feature-item">
                        <span class="vn-sf-icon">🎨</span>
                        <div>
                            <strong>Standalone Flyer Pages (<code>/f/your-slug</code>)</strong>
                            <p>Generate high-converting, mobile-first aesthetic flyer pages tailored for Instagram, TikTok, and WhatsApp sharing.</p>
                        </div>
                    </div>

                    <div class="vn-s-feature-item">
                        <span class="vn-sf-icon">📊</span>
                        <div>
                            <strong>Transparent Financial Telemetry &amp; Escrow</strong>
                            <p>Track real-time gross revenue, platform fee breakdowns, and request fast per-event bank withdrawals as sales happen.</p>
                        </div>
                    </div>

                    <div class="vn-s-feature-item">
                        <span class="vn-sf-icon">👥</span>
                        <div>
                            <strong>Multi-Staff Gate Check-in Delegations</strong>
                            <p>Invite door bouncers and check-in personnel with restricted camera scanning access — zero access to your sensitive financial data.</p>
                        </div>
                    </div>

                    <div class="vn-s-feature-item">
                        <span class="vn-sf-icon">🎟️</span>
                        <div>
                            <strong>Custom Ticket Tiers, Add-ons &amp; Merch</strong>
                            <p>Sell Early Bird, VIP Tables, Group passes, and bundle event merchandise or drink vouchers directly at checkout.</p>
                        </div>
                    </div>
                </div>

                <div class="vn-studio-cta-row">
                    <?php if (app()->user && app()->user->isHost()): ?>
                        <a href="{{ route('host/event/create') }}" class="vn-btn-primary">
                            Create Your Next Event →
                        </a>
                        <a href="{{ route('host/dashboard') }}" class="vn-btn-outline">
                            Open Host Dashboard
                        </a>
                    <?php else: ?>
                        <a href="{{ route('register') }}" class="vn-btn-primary">
                            Start Hosting for Free →
                        </a>
                        <a href="{{ route('about') }}" class="vn-btn-outline">
                            Learn How It Works
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Studio Stat Sidebox -->
            <div class="vn-studio-stat-box">
                <div class="vn-s-stat-card">
                    <span class="vn-s-stat-num">0%</span>
                    <span class="vn-s-stat-label">Setup or Monthly Fees</span>
                    <small>Pay only on tickets you sell</small>
                </div>
                <div class="vn-s-stat-card">
                    <span class="vn-s-stat-num">&lt; 3m</span>
                    <span class="vn-s-stat-label">Time to Launch Tickets</span>
                    <small>Zero red tape, go live today</small>
                </div>
                <div class="vn-s-stat-card">
                    <span class="vn-s-stat-num">100%</span>
                    <span class="vn-s-stat-label">Phone Camera Scans</span>
                    <small>Zero hardware rentals needed</small>
                </div>
                <div class="vn-s-stat-card">
                    <span class="vn-s-stat-num">24/7</span>
                    <span class="vn-s-stat-label">Automated Delivery</span>
                    <small>Email &amp; WhatsApp distribution</small>
                </div>
            </div>
        </div>
    </section>

    <!-- ═══ 10. Frequently Asked Questions (FAQ Accordion) ═══ -->
    <section class="vn-faq-section" id="faqs-section">
        <div class="vn-section-head" style="text-align: center; max-width: 680px; margin: 0 auto 2.5rem;">
            <span class="vn-host-eyebrow">✦ Got Questions? ✦</span>
            <h2 class="vn-section-title">Frequently Asked Questions</h2>
            <p class="vn-section-sub">Clear answers about tickets, payments, door scanning, and host payouts.</p>
        </div>

        <div class="vn-faq-accordion" role="region" aria-label="Frequently Asked Questions list">
            <!-- FAQ 1 -->
            <div class="vn-faq-item">
                <button type="button" class="vn-faq-trigger" aria-expanded="false" aria-controls="faq-ans-1">
                    <span class="vn-faq-question">How do I receive and access my ticket after booking?</span>
                    <span class="vn-faq-icon" aria-hidden="true">+</span>
                </button>
                <div id="faq-ans-1" class="vn-faq-answer" hidden>
                    <div class="vn-faq-body">
                        Your digital ticket is generated instantaneously once payment is confirmed. You'll receive a confirmation email with a scannable high-contrast QR pass, plus instant WhatsApp/SMS delivery if selected at checkout. You can also view and download all your passes anytime by logging into your Eventlyy dashboard under "My Tickets". Offline saving is fully supported!
                    </div>
                </div>
            </div>

            <!-- FAQ 2 -->
            <div class="vn-faq-item">
                <button type="button" class="vn-faq-trigger" aria-expanded="false" aria-controls="faq-ans-2">
                    <span class="vn-faq-question">What is Layaway (Partial Payment) and how does it work?</span>
                    <span class="vn-faq-icon" aria-hidden="true">+</span>
                </button>
                <div id="faq-ans-2" class="vn-faq-answer" hidden>
                    <div class="vn-faq-body">
                        For events that enable Layaway, you don't have to pay the full price upfront. You can put down a deposit to lock in your ticket at today's price (protecting you from price increases and sold-out tiers). You can then pay off the remaining balance anytime before the event starts. Once fully paid, your scannable gate QR pass is automatically unlocked.
                    </div>
                </div>
            </div>

            <!-- FAQ 3 -->
            <div class="vn-faq-item">
                <button type="button" class="vn-faq-trigger" aria-expanded="false" aria-controls="faq-ans-3">
                    <span class="vn-faq-question">Can I transfer or give my ticket to a friend if I can't attend?</span>
                    <span class="vn-faq-icon" aria-hidden="true">+</span>
                </button>
                <div id="faq-ans-3" class="vn-faq-answer" hidden>
                    <div class="vn-faq-body">
                        Yes! Eventlyy features a built-in 1-Click Ticket Transfer system. Simply open your ticket in your account, tap "Transfer Ticket", and enter your friend's email address. A brand new QR ticket with fresh security credentials will be issued to them, while your old ticket is securely deactivated to prevent duplicate entries.
                    </div>
                </div>
            </div>

            <!-- FAQ 4 -->
            <div class="vn-faq-item">
                <button type="button" class="vn-faq-trigger" aria-expanded="false" aria-controls="faq-ans-4">
                    <span class="vn-faq-question">How does gate check-in work at the venue entrance?</span>
                    <span class="vn-faq-icon" aria-hidden="true">+</span>
                </button>
                <div id="faq-ans-4" class="vn-faq-answer" hidden>
                    <div class="vn-faq-body">
                        At the door, display your ticket's digital QR pass on your smartphone screen (turn screen brightness up for maximum speed). The door crew or bouncers will scan it with their phone camera scanner. The system verifies the pass in under one second, confirms admission tier, and marks the pass as admitted. No printed paper needed!
                    </div>
                </div>
            </div>

            <!-- FAQ 5 -->
            <div class="vn-faq-item">
                <button type="button" class="vn-faq-trigger" aria-expanded="false" aria-controls="faq-ans-5">
                    <span class="vn-faq-question">How and when do event hosts withdraw their ticket revenue?</span>
                    <span class="vn-faq-icon" aria-hidden="true">+</span>
                </button>
                <div id="faq-ans-5" class="vn-faq-answer" hidden>
                    <div class="vn-faq-body">
                        Eventlyy tracks all ticket revenue in transparent escrow accounts on a per-event basis. Hosts can request early escrow withdrawals for operational costs (such as venue booking and artist deposits) or request full final settlement to their verified bank account once the event concludes. All records, transaction logs, and platform fees are clearly itemized in the host dashboard.
                    </div>
                </div>
            </div>

            <!-- FAQ 6 -->
            <div class="vn-faq-item">
                <button type="button" class="vn-faq-trigger" aria-expanded="false" aria-controls="faq-ans-6">
                    <span class="vn-faq-question">Are Eventlyy tickets authentic and protected against secondary scams?</span>
                    <span class="vn-faq-icon" aria-hidden="true">+</span>
                </button>
                <div id="faq-ans-6" class="vn-faq-answer" hidden>
                    <div class="vn-faq-body">
                        100% yes. Every single ticket is cryptographically generated with unique hash signatures, individual reference codes, and an optional 4-digit Security PIN. Once scanned and checked in at the door, the ticket is permanently marked as used and cannot be reused or duplicated. When you buy on Eventlyy, you are buying authentic passes directly from the event creator.
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ═══ 11. Final Call-to-Action Salon ═══ -->
    <section class="vn-host-salon" style="margin-top: 3.5rem;">
        <span class="vn-host-eyebrow">✦ Be Part of the Culture ✦</span>
        <h2 class="vn-host-title">Ready to experience what's on?</h2>
        <p class="vn-host-desc">
            Join thousands of eventgoers discovering concerts, summits, and nightlife daily. Or host your own crowd and go live in minutes.
        </p>
        <div class="vn-host-actions">
            <a href="#events-section" class="vn-btn-primary">
                Explore All Upcoming Drops ↓
            </a>
            <?php if (app()->user && app()->user->isHost()): ?>
                <a href="{{ route('host/event/create') }}" class="vn-btn-outline">
                    Host an Event →
                </a>
            <?php else: ?>
                <a href="{{ route('register') }}" class="vn-btn-outline">
                    Sign Up to Host →
                </a>
            <?php endif; ?>
        </div>
    </section>

    <!-- ═══ 12. Search & Filter Discovery Modal ═══ -->
    <div id="vn-search-modal" class="vn-modal-backdrop" aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="vn-modal-title">
        <div class="vn-modal-scrim" id="vn-modal-scrim"></div>
        <div class="vn-modal-card" role="document">
            
            <!-- Modal Top Bar -->
            <div class="vn-modal-head">
                <div class="vn-modal-seal-row">
                    <span class="vn-modal-seal-badge">✦ Eventlyy Concierge &bull; Find Your Next Move ✦</span>
                    <button type="button" class="vn-modal-close-btn" id="vn-close-search-modal" aria-label="Close search modal">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="18" y1="6" x2="6" y2="18"></line>
                            <line x1="6" y1="6" x2="18" y2="18"></line>
                        </svg>
                        <span class="vn-modal-esc-hint">ESC</span>
                    </button>
                </div>
                <h3 id="vn-modal-title" class="vn-modal-heading">Filter the scene</h3>
                <p class="vn-modal-sub">Narrow down by keyword, city, or the exact vibe you're chasing.</p>
            </div>

            <!-- Modal Search Form -->
            <form id="vn-modal-form" method="GET" action="{{ route('/') }}" class="vn-modal-body">
                
                <!-- 1. Master Keyword Input -->
                <div class="vn-mfield">
                    <label for="vn-m-input-q" class="vn-mlabel">
                        <span>What are you looking for?</span>
                        <span class="vn-mlabel-sub">Artist, venue, DJ, party or festival name</span>
                    </label>
                    <div class="vn-minput-box">
                        <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="vn-minput-icon">
                            <circle cx="11" cy="11" r="8"></circle>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                        </svg>
                        <input type="search" name="q" id="vn-m-input-q" class="vn-minput" placeholder="e.g. Afrobeats Night, Rooftop Party, AI Summit..." value="<?= e($search ?? '') ?>" autocomplete="off">
                        <button type="button" id="vn-m-clear-q" class="vn-mclear-btn<?= !empty($search) ? ' visible' : '' ?>" aria-label="Clear query input" title="Clear text">
                            ✕
                        </button>
                    </div>
                </div>

                <!-- 2. Location Quick Selector & Dropdown -->
                <div class="vn-mfield">
                    <div class="vn-mlabel-row">
                        <label class="vn-mlabel" for="vn-m-select-loc">
                            <span>Where are you pulling up?</span>
                        </label>
                        <span class="vn-mlabel-badge" id="vn-m-current-loc-badge"><?= !empty($location) ? e($location) : 'All Regions' ?></span>
                    </div>

                    <!-- Fast Location Touch Pills -->
                    <div class="vn-mloc-chips">
                        <button type="button" class="vn-mloc-chip<?= empty($location) ? ' active' : '' ?>" data-loc="">
                            All Regions
                        </button>
                        <?php if (!empty($locations)): ?>
                            <?php 
                            $locCount = 0;
                            foreach ($locations as $loc): 
                                if (++$locCount > 5) break;
                            ?>
                                <button type="button" class="vn-mloc-chip<?= ($location ?? '') === $loc ? ' active' : '' ?>" data-loc="<?= e($loc) ?>">
                                    📍 <?= e($loc) ?>
                                </button>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <!-- Custom Native Select for All Other Locations -->
                    <div class="vn-mselect-wrap">
                        <select name="location" id="vn-m-select-loc" class="vn-mselect">
                            <option value="">Pick a specific city or region...</option>
                            <?php if (!empty($locations)): ?>
                                <?php foreach ($locations as $loc): ?>
                                    <option value="<?= e($loc) ?>" <?= ($location ?? '') === $loc ? 'selected' : '' ?>>
                                        📍 <?= e($loc) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="vn-mselect-arrow" aria-hidden="true">
                            <polyline points="6 9 12 15 18 9"></polyline>
                        </svg>
                    </div>
                </div>

                <!-- 3. Discipline / Category Tiles -->
                <div class="vn-mfield">
                    <label class="vn-mlabel">
                        <span>What's the energy?</span>
                    </label>
                    <div class="vn-mcat-grid">
                        <?php 
                        $modalIcons = [
                            'All' => '✦',
                            'Concerts' => '🎵',
                            'Tech' => '⚡',
                            'Comedy' => '🎭',
                            'Sports' => '🏆',
                            'Theater' => '🎪',
                            'Family' => '🌿',
                            'Art & Wine' => '🍷',
                        ];
                        foreach ($categories ?? [] as $cat):
                            $isCatMatch = (empty($category) && $cat === 'All') || (strtolower($category ?? '') === strtolower($cat));
                        ?>
                            <label class="vn-mcat-item<?= $isCatMatch ? ' active' : '' ?>">
                                <input type="radio" name="category" value="<?= e($cat) ?>" <?= $isCatMatch ? 'checked' : '' ?> class="vn-mcat-radio">
                                <span class="vn-mcat-icon"><?= $modalIcons[$cat] ?? '✦' ?></span>
                                <span class="vn-mcat-title"><?= e($cat) ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Modal Action Footer -->
                <div class="vn-modal-foot">
                    <a href="{{ route('/') }}" class="vn-modal-clear-link">
                        <span>Clear All Filters</span>
                    </a>
                    <div class="vn-modal-cta-group">
                        <button type="button" class="vn-modal-cancel-btn" id="vn-m-cancel-btn">
                            Cancel
                        </button>
                        <button type="submit" class="vn-modal-submit-btn">
                            <span>Find My Plans →</span>
                            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <line x1="5" y1="12" x2="19" y2="12"></line>
                                <polyline points="12 5 19 12 12 19"></polyline>
                            </svg>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════════════════
     ENHANCED VINTAGE BROADSHEET HOMEPAGE COMPONENT STYLES
     ═══════════════════════════════════════════════════════════════════ -->
<style>
/* ── Fast Jump Navigation Anchors Bar ── */
.vn-quick-anchors {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.6rem;
    flex-wrap: wrap;
    margin: 1.5rem auto 1.25rem;
    max-width: 780px;
}

.vn-anchor-pill {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    padding: 0.45rem 0.95rem;
    border-radius: 999px;
    background: var(--vn-parchment-subtle);
    border: 1px solid var(--vn-border);
    color: var(--vn-ink-soft);
    font-size: 0.82rem;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.2s ease;
}

.vn-anchor-pill:hover {
    background: var(--vn-parchment-card);
    border-color: var(--vn-brass);
    color: var(--vn-brass);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(180, 83, 9, 0.1);
}

/* ── Section Dividers & General Spacing ── */
.vn-guide-section,
.vn-superpowers-section,
.vn-cities-section,
.vn-creator-studio-section,
.vn-faq-section {
    margin: 4.5rem 0;
    scroll-margin-top: 5rem;
}

/* ── 3-Step Attendee Guide Cards ── */
.vn-steps-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1.5rem;
}

.vn-step-card {
    background: var(--vn-parchment-card);
    border: 1.5px solid var(--vn-border);
    border-radius: var(--vn-radius-lg);
    padding: 2rem 1.75rem;
    box-shadow: var(--vn-shadow-card);
    display: flex;
    flex-direction: column;
    position: relative;
    transition: all 0.25s ease;
}

.vn-step-card:hover {
    transform: translateY(-4px);
    border-color: var(--vn-brass);
    box-shadow: var(--vn-shadow-elevated);
}

.vn-step-card.highlight {
    border-color: var(--vn-brass);
    background: linear-gradient(135deg, var(--vn-parchment-card) 0%, rgba(180, 83, 9, 0.05) 100%);
}

.vn-step-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.25rem;
}

.vn-step-number {
    font-family: var(--vn-font-mono);
    font-size: 1.85rem;
    font-weight: 800;
    color: var(--vn-brass);
    opacity: 0.75;
}

.vn-step-icon {
    font-size: 1.75rem;
}

.vn-step-title {
    font-family: var(--vn-font-serif);
    font-size: 1.35rem;
    font-weight: 600;
    color: var(--vn-ink);
    margin: 0 0 0.75rem;
    line-height: 1.25;
}

.vn-step-desc {
    font-size: 0.92rem;
    line-height: 1.6;
    color: var(--vn-ink-muted);
    margin: 0 0 1.5rem;
    flex: 1;
}

.vn-step-badge {
    font-family: var(--vn-font-mono);
    font-size: 0.75rem;
    font-weight: 700;
    color: #10b981;
    background: rgba(16, 185, 129, 0.08);
    border: 1px solid rgba(16, 185, 129, 0.2);
    border-radius: 999px;
    padding: 0.25rem 0.65rem;
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    align-self: flex-start;
}

/* ── Platform Superpowers Grid (6 Cards) ── */
.vn-perks-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
    gap: 1.5rem;
    margin-top: 1.75rem;
}

.vn-perk-card {
    background: var(--vn-parchment-card);
    border: 1.5px solid var(--vn-border);
    border-radius: 16px;
    padding: 1.65rem;
    transition: all 0.25s ease;
    box-shadow: var(--vn-shadow-card);
}

.vn-perk-card:hover {
    border-color: var(--vn-brass);
    transform: translateY(-3px);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.06);
}

.vn-perk-icon-wrap {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    background: rgba(180, 83, 9, 0.1);
    color: var(--vn-brass);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.35rem;
    margin-bottom: 1rem;
    border: 1px solid rgba(180, 83, 9, 0.2);
}

.vn-perk-name {
    font-family: var(--vn-font-serif);
    font-size: 1.25rem;
    color: var(--vn-ink);
    margin: 0 0 0.5rem;
    font-weight: 600;
}

.vn-perk-body {
    font-size: 0.9rem;
    line-height: 1.55;
    color: var(--vn-ink-muted);
    margin: 0;
}

/* ── Cities Quick Navigation Grid ── */
.vn-cities-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1.25rem;
    margin-top: 1.5rem;
}

.vn-city-card {
    position: relative;
    border-radius: 16px;
    overflow: hidden;
    min-height: 260px;
    display: flex;
    flex-direction: column;
    justify-content: flex-end;
    text-decoration: none;
    border: 1.5px solid var(--vn-border);
    box-shadow: var(--vn-shadow-card);
    transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
}

.vn-city-card:hover {
    transform: translateY(-4px);
    border-color: var(--vn-brass);
    box-shadow: var(--vn-shadow-elevated);
}

.vn-city-card:hover .vn-city-img-box {
    transform: scale(1.06);
}

.vn-city-img-box {
    position: absolute;
    inset: 0;
    background-size: cover;
    background-position: center;
    transition: transform 0.4s ease;
}

.vn-city-overlay {
    position: relative;
    z-index: 2;
    padding: 1.5rem 1.25rem;
    background: linear-gradient(180deg, transparent 0%, rgba(15, 23, 42, 0.88) 60%, rgba(15, 23, 42, 0.98) 100%);
    color: #ffffff;
}

.vn-city-tag {
    font-family: var(--vn-font-mono);
    font-size: 0.68rem;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    font-weight: 700;
    background: rgba(245, 158, 11, 0.2);
    border: 1px solid rgba(245, 158, 11, 0.4);
    color: #fbbf24;
    padding: 0.15rem 0.5rem;
    border-radius: 4px;
    display: inline-block;
    margin-bottom: 0.4rem;
}

.vn-city-name {
    font-family: var(--vn-font-serif);
    font-size: 1.5rem;
    margin: 0 0 0.35rem;
    color: #ffffff;
}

.vn-city-sub {
    font-size: 0.8rem;
    line-height: 1.4;
    color: rgba(255, 255, 255, 0.75);
    margin: 0 0 0.85rem;
}

.vn-city-arrow {
    font-size: 0.82rem;
    font-weight: 700;
    color: #fbbf24;
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
}

/* ── Creator Studio Spotlight (2-Column) ── */
.vn-creator-studio-section {
    background: var(--vn-parchment-warm);
    border: 2px solid var(--vn-border-dark);
    border-radius: var(--vn-radius-lg);
    box-shadow: var(--vn-shadow-elevated);
    padding: clamp(2rem, 4vw, 3.5rem);
    position: relative;
    overflow: hidden;
}

.vn-studio-card {
    display: grid;
    grid-template-columns: 1.4fr 1fr;
    gap: 2.5rem;
    align-items: center;
}

.vn-studio-title {
    font-family: var(--vn-font-serif);
    font-size: clamp(1.85rem, 3.5vw, 2.75rem);
    font-weight: 400;
    color: var(--vn-ink);
    margin: 0 0 1rem;
    line-height: 1.15;
}

.vn-studio-desc {
    font-size: 1rem;
    line-height: 1.6;
    color: var(--vn-ink-muted);
    margin-bottom: 2rem;
}

.vn-studio-feature-list {
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
    margin-bottom: 2rem;
}

.vn-s-feature-item {
    display: flex;
    gap: 1rem;
    align-items: flex-start;
}

.vn-sf-icon {
    font-size: 1.35rem;
    flex-shrink: 0;
    margin-top: 0.15rem;
}

.vn-s-feature-item strong {
    display: block;
    font-size: 0.95rem;
    color: var(--vn-ink);
    margin-bottom: 0.2rem;
}

.vn-s-feature-item p {
    font-size: 0.86rem;
    color: var(--vn-ink-muted);
    margin: 0;
    line-height: 1.45;
}

.vn-studio-cta-row {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
    align-items: center;
}

.vn-studio-stat-box {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

.vn-s-stat-card {
    background: var(--vn-parchment-card);
    border: 1.5px solid var(--vn-border);
    border-radius: 14px;
    padding: 1.5rem 1.25rem;
    text-align: center;
    box-shadow: var(--vn-shadow-card);
}

.vn-s-stat-num {
    display: block;
    font-family: var(--vn-font-serif);
    font-size: 2.25rem;
    color: var(--vn-brass);
    line-height: 1;
    margin-bottom: 0.45rem;
}

.vn-s-stat-label {
    display: block;
    font-size: 0.85rem;
    font-weight: 700;
    color: var(--vn-ink);
    margin-bottom: 0.25rem;
}

.vn-s-stat-card small {
    font-size: 0.76rem;
    color: var(--vn-ink-muted);
}

/* ── FAQ Accordion ── */
.vn-faq-accordion {
    max-width: 820px;
    margin: 0 auto;
    display: flex;
    flex-direction: column;
    gap: 0.85rem;
}

.vn-faq-item {
    background: var(--vn-parchment-card);
    border: 1.5px solid var(--vn-border);
    border-radius: 14px;
    overflow: hidden;
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
}

.vn-faq-item.is-open {
    border-color: var(--vn-brass);
    box-shadow: var(--vn-shadow-card);
}

.vn-faq-trigger {
    width: 100%;
    padding: 1.25rem 1.5rem;
    background: transparent;
    border: none;
    text-align: left;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
    cursor: pointer;
    color: var(--vn-ink);
    font-family: var(--vn-font-serif);
    font-size: 1.15rem;
    font-weight: 600;
}

.vn-faq-icon {
    font-size: 1.4rem;
    line-height: 1;
    font-family: var(--vn-font-mono);
    color: var(--vn-brass);
    transition: transform 0.25s ease;
    flex-shrink: 0;
}

.vn-faq-item.is-open .vn-faq-icon {
    transform: rotate(45deg);
}

.vn-faq-answer {
    padding: 0 1.5rem 1.35rem;
}

.vn-faq-body {
    font-family: var(--vn-font-sans);
    font-size: 0.94rem;
    line-height: 1.65;
    color: var(--vn-ink-muted);
    border-top: 1px dashed var(--vn-border);
    padding-top: 1rem;
}

/* ═══════════════════════════════════════════════════════════════════
   RESPONSIVE BREAKPOINTS (Tablets & Mobile)
   ═══════════════════════════════════════════════════════════════════ */
@media (max-width: 1024px) {
    .vn-cities-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    .vn-studio-card {
        grid-template-columns: 1fr;
        gap: 2rem;
    }
    .vn-steps-grid {
        grid-template-columns: repeat(3, 1fr);
        gap: 1rem;
    }
}

@media (max-width: 820px) {
    .vn-steps-grid {
        grid-template-columns: 1fr;
        gap: 1.25rem;
    }
    .vn-perks-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 650px) {
    .vn-quick-anchors {
        justify-content: flex-start;
        overflow-x: auto;
        white-space: nowrap;
        padding-bottom: 0.4rem;
        scrollbar-width: none;
    }
    .vn-quick-anchors::-webkit-scrollbar { display: none; }

    .vn-cities-grid {
        grid-template-columns: 1fr;
    }

    .vn-studio-stat-box {
        grid-template-columns: 1fr;
    }

    .vn-studio-cta-row,
    .vn-host-actions {
        flex-direction: column;
        width: 100%;
    }

    .vn-btn-primary,
    .vn-btn-outline {
        width: 100%;
        text-align: center;
        justify-content: center;
        box-sizing: border-box;
    }

    .vn-faq-trigger {
        font-size: 1.05rem;
        padding: 1rem 1.15rem;
    }

    .vn-faq-answer {
        padding: 0 1.15rem 1.15rem;
    }
}
</style>
@endsection

@section('script')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // ── 1. Search Concierge Modal Controller ──
    const searchModal = document.getElementById('vn-search-modal');
    const openModalBtn = document.getElementById('vn-open-search-modal');
    const closeModalBtn = document.getElementById('vn-close-search-modal');
    const cancelModalBtn = document.getElementById('vn-m-cancel-btn');
    const modalScrim = document.getElementById('vn-modal-scrim');
    const modalQueryInput = document.getElementById('vn-m-input-q');
    const modalClearQueryBtn = document.getElementById('vn-m-clear-q');
    const modalLocSelect = document.getElementById('vn-m-select-loc');
    const locPills = document.querySelectorAll('.vn-mloc-chip');

    function openSearchModal() {
        if (!searchModal) return;
        searchModal.classList.add('is-open');
        searchModal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('vn-modal-lock');
        setTimeout(() => {
            if (modalQueryInput) {
                modalQueryInput.focus();
                if (modalQueryInput.value) modalQueryInput.select();
            }
        }, 150);
    }

    function closeSearchModal() {
        if (!searchModal) return;
        searchModal.classList.remove('is-open');
        searchModal.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('vn-modal-lock');
        if (openModalBtn) openModalBtn.focus();
    }

    if (openModalBtn) openModalBtn.addEventListener('click', openSearchModal);
    if (closeModalBtn) closeModalBtn.addEventListener('click', closeSearchModal);
    if (cancelModalBtn) cancelModalBtn.addEventListener('click', closeSearchModal);
    if (modalScrim) modalScrim.addEventListener('click', closeSearchModal);

    // Keyboard Shortcuts: Command/Ctrl + K or / to open, ESC to close
    document.addEventListener('keydown', function(e) {
        if ((e.metaKey || e.ctrlKey) && e.key.toLowerCase() === 'k') {
            e.preventDefault();
            if (searchModal && searchModal.classList.contains('is-open')) {
                closeSearchModal();
            } else {
                openSearchModal();
            }
        } else if (e.key === '/' && !['INPUT', 'TEXTAREA', 'SELECT'].includes(document.activeElement.tagName)) {
            e.preventDefault();
            openSearchModal();
        } else if (e.key === 'Escape' && searchModal && searchModal.classList.contains('is-open')) {
            e.preventDefault();
            closeSearchModal();
        }
    });

    // Query Clear Button inside Modal
    if (modalQueryInput && modalClearQueryBtn) {
        modalQueryInput.addEventListener('input', function() {
            modalClearQueryBtn.classList.toggle('visible', this.value.trim().length > 0);
        });
        modalClearQueryBtn.addEventListener('click', function(e) {
            e.preventDefault();
            modalQueryInput.value = '';
            modalClearQueryBtn.classList.remove('visible');
            modalQueryInput.focus();
        });
    }

    // Location Pill Sync to Modal Select & Badge
    const locBadge = document.getElementById('vn-m-current-loc-badge');
    locPills.forEach(pill => {
        pill.addEventListener('click', function(e) {
            e.preventDefault();
            locPills.forEach(p => p.classList.remove('active'));
            this.classList.add('active');
            const chosenLoc = this.getAttribute('data-loc') || '';
            if (modalLocSelect) modalLocSelect.value = chosenLoc;
            if (locBadge) locBadge.textContent = chosenLoc ? chosenLoc : 'All Regions';
        });
    });

    // ── 2. Featured Highlights Carousel ──
    const carouselTrack = document.getElementById('vn-carousel-track');
    const prevBtn = document.getElementById('vn-carousel-prev');
    const nextBtn = document.getElementById('vn-carousel-next');
    const dots = document.querySelectorAll('.vn-carousel-dot');

    if (carouselTrack && dots.length > 1) {
        let currentIdx = 0;
        const totalCards = dots.length;

        function updateCarousel(idx) {
            currentIdx = (idx + totalCards) % totalCards;
            const card = carouselTrack.querySelector(`[data-card-idx="${currentIdx}"]`);
            if (card) {
                card.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'start' });
            }
            dots.forEach((dot, i) => {
                dot.classList.toggle('active', i === currentIdx);
            });
        }

        if (prevBtn) prevBtn.addEventListener('click', () => updateCarousel(currentIdx - 1));
        if (nextBtn) nextBtn.addEventListener('click', () => updateCarousel(currentIdx + 1));
        dots.forEach((dot, idx) => {
            dot.addEventListener('click', () => updateCarousel(idx));
        });
    }

    // ── 3. Smooth Anchor Scrolling for Quick-Jump Links ──
    document.querySelectorAll('.vn-anchor-pill').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            const targetId = this.getAttribute('href');
            if (targetId && targetId.startsWith('#')) {
                const targetEl = document.querySelector(targetId);
                if (targetEl) {
                    e.preventDefault();
                    targetEl.scrollIntoView({ behavior: 'smooth' });
                }
            }
        });
    });

    // ── 4. Interactive FAQ Accordion ──
    const faqItems = document.querySelectorAll('.vn-faq-item');
    faqItems.forEach(item => {
        const trigger = item.querySelector('.vn-faq-trigger');
        const answer = item.querySelector('.vn-faq-answer');

        if (trigger && answer) {
            trigger.addEventListener('click', function() {
                const isOpen = item.classList.contains('is-open');

                // Close all other open items
                faqItems.forEach(other => {
                    if (other !== item && other.classList.contains('is-open')) {
                        other.classList.remove('is-open');
                        const otherTrigger = other.querySelector('.vn-faq-trigger');
                        const otherAnswer = other.querySelector('.vn-faq-answer');
                        if (otherTrigger) otherTrigger.setAttribute('aria-expanded', 'false');
                        if (otherAnswer) otherAnswer.hidden = true;
                    }
                });

                // Toggle current item
                if (isOpen) {
                    item.classList.remove('is-open');
                    trigger.setAttribute('aria-expanded', 'false');
                    answer.hidden = true;
                } else {
                    item.classList.add('is-open');
                    trigger.setAttribute('aria-expanded', 'true');
                    answer.hidden = false;
                }
            });
        }
    });
});
</script>
@endsection
