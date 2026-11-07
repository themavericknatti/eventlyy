<?php

/**
 * Home / Welcome Page — Vintage Edition
 *
 * Immersive vintage editorial broadsheet design featuring antique parchment tones,
 * authentic ticket-stub cards, letterpress typography ('Instrument Serif'),
 * tactile brass search console, and seamless mobile-friendly responsiveness.
 *
 * @package Eventlyy
 * @subpackage Views/Home
 * @author Celio Natti
 * @version 4.0.0
 */

?>

@extends('layouts/main')

@section('title')
<?= e($title ?? 'Eventlyy — The Vintage Journal of Live Experiences & Admissions') ?>
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
                Concerts, secret DJ sets, creative pop-ups, and game-changing summits. Instant QR passes on lock, backed by verified direct host payouts.
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

            <!-- Heritage Proof Metrics Strip -->
            <div class="vn-metrics-strip">
                <div class="vn-metric-item"><span class="fleuron">✦</span> 50K+ Passes Secured</div>
                <div class="vn-metric-item"><span class="fleuron">✦</span> Instant QR Check-in</div>
                <div class="vn-metric-item"><span class="fleuron">✦</span> 100% Legit Passes Only</div>
                <div class="vn-metric-item"><span class="fleuron">✦</span> Direct Creator Payouts</div>
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
                    <!-- Ticket Stub Card conforming to Modern Web Guidance -->
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

    <!-- ═══ 6. Host Invitation Salon Notice ═══ -->
    <section class="vn-host-salon">
        <span class="vn-host-eyebrow">✦ For Creators, Hosts &amp; Collectives ✦</span>
        <h2 class="vn-host-title">Got an event in mind? Drop it on Eventlyy.</h2>
        <p class="vn-host-desc">
            Go live in minutes. Set up custom ticket tiers, get fast automated bank payouts, and breeze through the door with optical QR check-in. Build your crowd, your way.
        </p>
        <div class="vn-host-actions">
            <?php if (app()->user && app()->user->isHost()): ?>
                <a href="{{ route('host/event/create') }}" class="vn-btn-primary">
                    Create an Event →
                </a>
            <?php else: ?>
                <a href="{{ route('register') }}" class="vn-btn-primary">
                    Start Hosting for Free →
                </a>
            <?php endif; ?>
            <a href="{{ route('about') }}" class="vn-btn-outline">
                See How It Works
            </a>
        </div>
    </section>


<!-- ═══ 7. Luxury Search & Filter Discovery Modal ═══ -->
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
@endsection

@section('script')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // ── Search Concierge Modal Controller ──
    const searchModal = document.getElementById('vn-search-modal');
    const openModalBtn = document.getElementById('vn-open-search-modal');
    const closeModalBtn = document.getElementById('vn-close-search-modal');
    const cancelModalBtn = document.getElementById('vn-m-cancel-btn');
    const modalScrim = document.getElementById('vn-modal-scrim');
    const modalQueryInput = document.getElementById('vn-m-input-q');
    const modalClearQueryBtn = document.getElementById('vn-m-clear-query');
    const modalLocSelect = document.getElementById('vn-m-select-loc');
    const locPills = document.querySelectorAll('.vn-mloc-chip');
    const catLabels = document.querySelectorAll('.vn-mcat-item');

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

    if (modalLocSelect) {
        modalLocSelect.addEventListener('change', function() {
            const val = this.value;
            locPills.forEach(p => {
                p.classList.toggle('active', (p.getAttribute('data-loc') || '') === val);
            });
            if (locBadge) locBadge.textContent = val ? val : 'All Regions';
        });
    }

    // Category Radio Active Styling Sync
    catLabels.forEach(lbl => {
        const radio = lbl.querySelector('input[type="radio"]');
        if (radio) {
            radio.addEventListener('change', function() {
                catLabels.forEach(l => l.classList.remove('active'));
                if (this.checked) lbl.classList.add('active');
            });
        }
    });

    // Auto-scroll active category chip into view on mobile
    const activeChip = document.querySelector('.vn-chip.active');
    const chipsRow = document.getElementById('vn-chips-row');
    if (activeChip && chipsRow && activeChip.offsetLeft > (chipsRow.clientWidth / 2)) {
        chipsRow.scrollTo({
            left: activeChip.offsetLeft - 32,
            behavior: 'smooth'
        });
    }

    // ── Search Auto-Scroll on Filter ──
    <?php if ($isFiltered && (!empty($search) || !empty($location))): ?>
        var eventsEl = document.getElementById('events-section');
        if (eventsEl) {
            eventsEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    <?php endif; ?>

    // ── Featured Highlights Carousel Controller ──
    const track = document.getElementById('vn-carousel-track');
    if (!track) return;

    const prevBtn = document.getElementById('vn-carousel-prev');
    const nextBtn = document.getElementById('vn-carousel-next');
    const dots = document.querySelectorAll('.vn-carousel-dot');
    const items = track.querySelectorAll('.vn-featured-stub');

    if (items.length <= 1) {
        if (prevBtn) prevBtn.style.display = 'none';
        if (nextBtn) nextBtn.style.display = 'none';
        const dotsWrap = document.getElementById('vn-carousel-dots');
        if (dotsWrap) dotsWrap.style.display = 'none';
        return;
    }

    let currentIndex = 0;
    let autoPlayTimer = null;
    let isInteracting = false;

    function scrollToIndex(idx, smooth = true) {
        if (idx < 0) idx = items.length - 1;
        if (idx >= items.length) idx = 0;
        currentIndex = idx;

        const targetCard = items[idx];
        if (!targetCard) return;

        const targetLeft = targetCard.offsetLeft - track.offsetLeft;
        track.scrollTo({
            left: targetLeft,
            behavior: smooth ? 'smooth' : 'auto'
        });
        updateDots(idx);
    }

    function updateDots(idx) {
        dots.forEach((dot, dIdx) => {
            dot.classList.toggle('active', dIdx === idx);
        });
    }

    if (nextBtn) {
        nextBtn.addEventListener('click', function(e) {
            e.preventDefault();
            scrollToIndex(currentIndex + 1);
            resetAutoPlay();
        });
    }

    if (prevBtn) {
        prevBtn.addEventListener('click', function(e) {
            e.preventDefault();
            scrollToIndex(currentIndex - 1);
            resetAutoPlay();
        });
    }

    dots.forEach(dot => {
        dot.addEventListener('click', function() {
            const idx = parseInt(this.getAttribute('data-index'), 10);
            scrollToIndex(idx);
            resetAutoPlay();
        });
    });

    // Sync active dot on manual scroll / touch swipe
    let scrollDebounce;
    track.addEventListener('scroll', function() {
        clearTimeout(scrollDebounce);
        scrollDebounce = setTimeout(function() {
            const currentScroll = track.scrollLeft;
            let closestIdx = 0;
            let minDistance = Infinity;

            items.forEach((item, idx) => {
                const dist = Math.abs((item.offsetLeft - track.offsetLeft) - currentScroll);
                if (dist < minDistance) {
                    minDistance = dist;
                    closestIdx = idx;
                }
            });

            currentIndex = closestIdx;
            updateDots(closestIdx);
        }, 50);
    }, { passive: true });

    // Smooth Auto-Play (Advances every 4.5 seconds)
    function startAutoPlay() {
        stopAutoPlay();
        autoPlayTimer = setInterval(function() {
            if (!isInteracting) {
                scrollToIndex(currentIndex + 1);
            }
        }, 4500);
    }

    function stopAutoPlay() {
        if (autoPlayTimer) {
            clearInterval(autoPlayTimer);
            autoPlayTimer = null;
        }
    }

    function resetAutoPlay() {
        stopAutoPlay();
        startAutoPlay();
    }

    const wrap = document.getElementById('vn-carousel-wrap') || track;
    wrap.addEventListener('mouseenter', () => { isInteracting = true; });
    wrap.addEventListener('mouseleave', () => { isInteracting = false; });
    wrap.addEventListener('focusin', () => { isInteracting = true; });
    wrap.addEventListener('focusout', () => { isInteracting = false; });
    track.addEventListener('touchstart', () => { isInteracting = true; }, { passive: true });
    track.addEventListener('touchend', () => {
        setTimeout(() => { isInteracting = false; }, 3000);
    }, { passive: true });

    startAutoPlay();
});
</script>
@endsection
