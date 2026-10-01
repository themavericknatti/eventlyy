<?php

/**
 * Home / Welcome Page
 *
 * Displays public events, search & location filters, category chips, featured carousel, and paginated event list.
 *
 * @package Eventlyy
 * @subpackage Views/Home
 * @author Your Name <Celio Natti>
 * @license MIT License
 * @version 1.0.0
 */

?>

@extends('layouts/main')

@section('title')
<?= e($title ?? 'Welcome to Eventlyy - Find live events, concerts & experiences.') ?>
@endsection

@section('content')
<!-- ═══ Full-Width Waving Flag & Current Background Wrapper ═══ -->
<div class="hero-wrapper-fullwidth">
    <!-- Animated Full-Width SVG Waving Flag Currents -->
    <div class="hero-flag-currents" aria-hidden="true">
        <svg class="flag-wave flag-wave-1" viewBox="0 0 1440 320" preserveAspectRatio="none">
            <defs>
                <linearGradient id="flagGrad1" x1="0%" y1="0%" x2="100%" y2="100%">
                    <stop offset="0%" stop-color="rgba(var(--brand), 0.28)" />
                    <stop offset="50%" stop-color="rgba(16, 185, 129, 0.22)" />
                    <stop offset="100%" stop-color="rgba(52, 211, 153, 0.16)" />
                </linearGradient>
            </defs>
            <path fill="url(#flagGrad1)" d="M0,160 C320,300 420,40 740,180 C1060,320 1180,60 1440,200 L1440,320 L0,320 Z"></path>
        </svg>
        <svg class="flag-wave flag-wave-2" viewBox="0 0 1440 320" preserveAspectRatio="none">
            <defs>
                <linearGradient id="flagGrad2" x1="100%" y1="0%" x2="0%" y2="100%">
                    <stop offset="0%" stop-color="rgba(20, 184, 166, 0.22)" />
                    <stop offset="60%" stop-color="rgba(16, 185, 129, 0.18)" />
                    <stop offset="100%" stop-color="rgba(45, 212, 191, 0.15)" />
                </linearGradient>
            </defs>
            <path fill="url(#flagGrad2)" d="M0,96 C240,220 540,20 840,160 C1140,300 1320,80 1440,120 L1440,320 L0,320 Z"></path>
        </svg>
        <svg class="flag-wave flag-wave-3" viewBox="0 0 1440 320" preserveAspectRatio="none">
            <defs>
                <linearGradient id="flagGrad3" x1="0%" y1="100%" x2="100%" y2="0%">
                    <stop offset="0%" stop-color="rgba(52, 211, 153, 0.18)" />
                    <stop offset="50%" stop-color="rgba(16, 185, 129, 0.18)" />
                    <stop offset="100%" stop-color="rgba(var(--brand), 0.12)" />
                </linearGradient>
            </defs>
            <path fill="url(#flagGrad3)" d="M0,224 C360,100 680,280 1020,140 C1240,50 1380,190 1440,160 L1440,320 L0,320 Z"></path>
        </svg>
        <div class="hero-orb hero-orb-1" aria-hidden="true"></div>
        <div class="hero-orb hero-orb-2" aria-hidden="true"></div>
        <div class="hero-orb hero-orb-3" aria-hidden="true"></div>
    </div>

    <!-- Centered Content Container -->
    <div class="hero-container-inner">
        <section class="hero">
            <div class="hero-inner">
                <p class="hero-eyebrow"><span class="eyebrow-spark">✨</span> Your next night out starts here</p>
                <h1>Find live events, concerts &amp; experiences</h1>
                <p class="lede">Search, compare and buy tickets — fast and secure.</p>

                <!-- Dynamic Server-side Search Form -->
                <form id="search-form" class="search" role="search" aria-label="Search events" method="GET" action="{{ route('/') }}">
                    <input name="q" id="search-input" type="search" placeholder="Search events, artists, venues..."
                        aria-label="Search" value="<?= e($search ?? '') ?>">

                    <select name="location" id="search-location" aria-label="Location">
                        <option value="">All Locations</option>
                        <?php if (!empty($locations)): ?>
                            <?php foreach ($locations as $loc): ?>
                                <option value="<?= e($loc) ?>" <?= ($location ?? '') === $loc ? 'selected' : '' ?>>
                                    📍 <?= e($loc) ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>

                    <?php if (!empty($category)): ?>
                        <input type="hidden" name="category" value="<?= e($category) ?>" />
                    <?php endif; ?>

                    <button type="submit"><span>🔍</span> Search Events</button>
                </form>

                <!-- Hero Trust Metrics Bar -->
                <div class="hero-trust-bar">
                    <div class="trust-badge-item"><span>🎟️</span> 50k+ Passes Issued</div>
                    <div class="trust-badge-item"><span>⚡</span> Instant QR Check-in</div>
                    <div class="trust-badge-item"><span>🔒</span> Paystack Secured</div>
                    <div class="trust-badge-item"><span>⭐</span> 4.9/5 Host Rating</div>
                </div>
            </div>
            <div class="hero-media" aria-hidden>
                <div class="hero-image-wrapper">
                    <img src="{{ asset('img/evently_hero_graphic.png') }}" alt="Evently Brand Graphic" class="hero-brand-img" />
                </div>
            </div>
        </section>
    </div>
</div>

<!-- Popular Categories Chips -->
<section class="categories reveal">
    <h2>Popular categories</h2>
    <div class="chips">
        <?php 
        $catIcons = [
            'All' => '🔥',
            'Concerts' => '🎵',
            'Tech' => '💻',
            'Comedy' => '🎭',
            'Sports' => '⚽',
            'Theater' => '🎪',
            'Family' => '👨‍👩‍👧',
            'Art & Wine' => '🎨',
        ];
        foreach ($categories ?? [] as $cat):
            $isCatActive = (empty($category) && $cat === 'All') || (strtolower($category ?? '') === strtolower($cat));
            $catUrl = route('/') . '?category=' . urlencode($cat);
            if (!empty($search)) $catUrl .= '&q=' . urlencode($search);
            if (!empty($location)) $catUrl .= '&location=' . urlencode($location);
            $icon = $catIcons[$cat] ?? '✨';
        ?>
            <a href="<?= $catUrl ?>" class="chip-btn <?= $isCatActive ? 'active' : '' ?>">
                <span><?= $icon ?></span> <?= e($cat) ?>
            </a>
        <?php endforeach; ?>
    </div>
</section>

<!-- Featured Events Carousel (Shown on main landing view when featured events exist) -->
<?php 
    $isFiltered = !empty($search) || !empty($location) || (!empty($category) && strtolower($category) !== 'all') || ($page ?? 1) > 1;
    $hasFeatured = !empty($featuredEvents) && count($featuredEvents) > 1;
?>
<?php if ($hasFeatured && !$isFiltered): ?>
<section class="featured-events">
    <h2>Featured Events</h2>
    <div class="carousel" id="carousel">
        <?php if (count($featuredEvents) > 1): ?>
        <div class="carousel-controls-top">
            <button class="carousel-prev" aria-label="Previous">◀</button>
            <button class="carousel-next" aria-label="Next">▶</button>
        </div>
        <?php endif; ?>
        <div class="slides" id="slides" aria-live="polite">

            <?php foreach ($featuredEvents as $idx => $fe):
                $feUrl = event_url($fe);
                $feImg = !empty($fe['image']) ? $fe['image'] : 'https://images.unsplash.com/photo-1501281668745-f7f57925c3b4?w=1200&q=80&auto=format&fit=crop';
            ?>
            <a href="<?= $feUrl ?>" class="event-card-link">
                <article class="event-card">
                    <div class="image">
                        <div class="image-blur" style="background-image: url('<?= e($feImg) ?>')"></div>
                        <img src="<?= e($feImg) ?>" alt="<?= e($fe['name']) ?>" class="image-full" />
                    </div>
                    <div class="meta">
                        <div style="display:flex; align-items:center; gap:0.4rem; flex-wrap:wrap; margin-bottom:0.35rem; overflow:hidden;">
                            <span style="font-size:0.75rem; background:var(--brand); color:#fff; padding:0.15rem 0.5rem; border-radius:10px; text-transform:uppercase; font-weight:700; display:inline-block;">
                                <?= e($fe['category'] ?? 'Event') ?>
                            </span>
                            <?php if (!empty($fe['is_featured'])): ?>
                                <span style="font-size:0.72rem; background:linear-gradient(135deg, #f59e0b, #d97706); color:#fff; padding:0.15rem 0.55rem; border-radius:10px; font-weight:800; display:inline-flex; align-items:center; gap:3px;">
                                    ⭐ Featured
                                </span>
                            <?php elseif (($fe['tickets_sold'] ?? 0) >= 5): ?>
                                <span style="font-size:0.72rem; background:rgba(239, 68, 68, 0.18); color:#ef4444; padding:0.15rem 0.55rem; border-radius:10px; font-weight:800; display:inline-flex; align-items:center; gap:3px;">
                                    🔥 Popular
                                </span>
                            <?php endif; ?>
                            <?php if (($fe['avg_rating'] ?? 0) > 0): ?>
                                <span style="font-size:0.72rem; background:rgba(245, 158, 11, 0.15); color:#d97706; padding:0.15rem 0.5rem; border-radius:10px; font-weight:700;">
                                    ★ <?= number_format($fe['avg_rating'], 1) ?>
                                </span>
                            <?php endif; ?>
                        </div>
                        <h3><?= e($fe['name']) ?></h3>
                        <p><?= date('M d, Y', strtotime($fe['date'])) ?> • <?= e($fe['location']) ?></p>
                    </div>
                </article>
            </a>
            <?php endforeach; ?>

        </div>
        <?php if (count($featuredEvents) > 1): ?>
        <div class="carousel-bottom">
            <div class="indicators" id="carousel-indicators" role="tablist" aria-label="Carousel indicators">
                <?php foreach ($featuredEvents as $idx => $fe): ?>
                    <button class="indicator <?= $idx === 0 ? 'active' : '' ?>" role="tab" aria-selected="<?= $idx === 0 ? 'true' : 'false' ?>" aria-label="Slide <?= $idx + 1 ?>"></button>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>
<?php endif; ?>

<!-- All Events Section -->
<?php
    $events = $events ?? [];
    $total = $total ?? count($events);
    $page = isset($page) ? max(1, (int) $page) : 1;
    $totalPages = isset($totalPages) ? max(1, (int) $totalPages) : 1;

    // Dynamic Title based on active search / filter state
    $headingTitle = 'All Events';
    $headingNote = 'Browse all available upcoming events with live search and filtering.';

    if (!empty($search) && !empty($location)) {
        $headingTitle = 'Search: "' . e($search) . '" in ' . e($location);
        $headingNote = 'Showing matching events for keyword and location.';
    } elseif (!empty($search)) {
        $headingTitle = 'Search Results for "' . e($search) . '"';
        $headingNote = 'Showing events matching your search keyword.';
    } elseif (!empty($location)) {
        $headingTitle = 'Events in ' . e($location);
        $headingNote = 'Showing upcoming events located in ' . e($location) . '.';
    } elseif (!empty($category) && strtolower($category) !== 'all') {
        $headingTitle = e($category) . ' Events';
        $headingNote = 'Showing upcoming ' . strtolower(e($category)) . ' events.';
    }
?>
<section class="event-listing" id="events-section">
    <div class="section-header">
        <div>
            <h2><?= $headingTitle ?></h2>
            <p class="section-note"><?= $headingNote ?></p>
            <?php if ($isFiltered): ?>
                <div style="display:flex; align-items:center; gap:0.5rem; flex-wrap:wrap; margin-top:0.75rem;">
                    <span style="font-size:0.8rem; color:var(--text-muted); font-weight:600;">Active Filters:</span>
                    <?php if (!empty($search)): ?>
                        <span class="au-status-badge" style="background:rgba(var(--brand),0.12); color:rgb(var(--brand)); font-size:0.75rem; padding:0.2rem 0.6rem;">
                            🔍 "<?= e($search) ?>"
                        </span>
                    <?php endif; ?>
                    <?php if (!empty($location)): ?>
                        <span class="au-status-badge" style="background:rgba(16,185,129,0.12); color:#10b981; font-size:0.75rem; padding:0.2rem 0.6rem;">
                            📍 <?= e($location) ?>
                        </span>
                    <?php endif; ?>
                    <?php if (!empty($category) && strtolower($category) !== 'all'): ?>
                        <span class="au-status-badge" style="background:rgba(245,158,11,0.12); color:#f59e0b; font-size:0.75rem; padding:0.2rem 0.6rem;">
                            🏷️ <?= e($category) ?>
                        </span>
                    <?php endif; ?>
                    <a href="{{ route('/') }}" style="font-size:0.8rem; color:#ef4444; font-weight:600; text-decoration:underline; margin-left:0.25rem;">
                        ✕ Clear Filters
                    </a>
                </div>
            <?php endif; ?>
        </div>
        <div class="list-meta" id="list-meta">
            Showing <?= count($events) ?> of <?= $total ?> event(s) <?= $totalPages > 1 ? "(Page {$page} of {$totalPages})" : '' ?>
        </div>
    </div>

    <div id="event-list" class="event-list">
        <?php if (empty($events)): ?>
            <div class="form-card" style="grid-column: 1 / -1; padding: 3.5rem 2rem; text-align: center; border-radius: 20px;">
                <div style="font-size: 2.5rem; margin-bottom: 0.75rem;">🔍</div>
                <h3 style="margin-bottom:0.5rem; color:var(--text); font-size:1.25rem;">No Events Found</h3>
                <p style="color:var(--text-muted); font-size:0.9rem; margin-bottom:1.5rem; max-width: 420px; margin-left: auto; margin-right: auto;">
                    We couldn't find any published events matching your search or filter criteria. Try adjusting your keywords or location.
                </p>
                <a href="{{ route('/') }}" class="admin-action-btn primary" style="padding:0.65rem 1.4rem; text-decoration:none; display:inline-flex; font-weight: 700;">
                    View All Upcoming Events
                </a>
            </div>
        <?php else: ?>
            <?php foreach ($events as $ev):
                $evUrl = event_url($ev);
                $evImg = !empty($ev['image']) ? $ev['image'] : 'https://images.unsplash.com/photo-1501281668745-f7f57925c3b4?w=1200&q=80&auto=format&fit=crop';
                $minPrice = isset($ev['min_price']) && $ev['min_price'] !== null ? format_price($ev['min_price']) : 'Free';
            ?>
            <article class="event-item">
                <div class="image">
                    <div class="image-blur" style="background-image: url('<?= e($evImg) ?>')"></div>
                    <img src="<?= e($evImg) ?>" alt="<?= e($ev['name']) ?>" class="image-full" />
                </div>
                <div class="details">
                    <div>
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.35rem;">
                            <span class="role-indicator au-role--host" style="background:rgba(var(--brand),0.1); color:rgb(var(--brand)); font-size:0.75rem;">
                                <?= e($ev['category'] ?? 'Event') ?>
                            </span>
                            <span style="font-weight:700; color:var(--brand); font-size:0.9rem;"><?= $minPrice ?></span>
                        </div>
                        <h3 style="margin:0.25rem 0 0.4rem; font-size:1.1rem; color:var(--text);"><?= e($ev['name']) ?></h3>
                        <p class="meta" style="margin:0; font-size:0.82rem; color:var(--text-muted);">
                            📅 <?= date('M d, Y • g:i A', strtotime($ev['date'])) ?><br>
                            📍 <?= e($ev['location']) ?>
                        </p>
                    </div>
                    <a href="<?= $evUrl ?>" class="action" style="margin-top:0.85rem; text-decoration:none; text-align:center;">View Tickets & Details</a>
                </div>
            </article>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Dynamic Smart Windowed Pagination -->
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

            // Calculate smart windowed page numbers with ellipsis
            $range = [];
            $delta = 2; // Show 2 pages on each side of active page
            $left = max(1, $page - $delta);
            $right = min($totalPages, $page + $delta);

            for ($i = 1; $i <= $totalPages; $i++) {
                if ($i == 1 || $i == $totalPages || ($i >= $left && $i <= $right)) {
                    $range[] = $i;
                } elseif (empty($range) || end($range) !== '...') {
                    $range[] = '...';
                }
            }
        ?>
        <nav class="pagination-nav" aria-label="Events pagination" style="margin-top: 2.5rem;">
            <div class="pagination" id="pagination" style="display: flex; justify-content: center; gap: 0.4rem; align-items: center; flex-wrap: wrap;">
                <?php if ($page > 1): ?>
                    <a href="<?= $buildPageUrl($page - 1) ?>" class="page-button" aria-label="Previous page">← Prev</a>
                <?php else: ?>
                    <span class="page-button disabled" aria-disabled="true">← Prev</span>
                <?php endif; ?>

                <?php foreach ($range as $r): ?>
                    <?php if ($r === '...'): ?>
                        <span class="page-ellipsis" style="padding: 0 0.35rem; color: var(--text-muted); font-weight: 700; user-select: none;">…</span>
                    <?php elseif ($r == $page): ?>
                        <span class="page-button active" aria-current="page" style="background: var(--brand); color: #fff; font-weight: 700; border-color: var(--brand);"><?= $r ?></span>
                    <?php else: ?>
                        <a href="<?= $buildPageUrl($r) ?>" class="page-button" aria-label="Page <?= $r ?>"><?= $r ?></a>
                    <?php endif; ?>
                <?php endforeach; ?>

                <?php if ($page < $totalPages): ?>
                    <a href="<?= $buildPageUrl($page + 1) ?>" class="page-button" aria-label="Next page">Next →</a>
                <?php else: ?>
                    <span class="page-button disabled" aria-disabled="true">Next →</span>
                <?php endif; ?>
            </div>
        </nav>
    <?php endif; ?>
</section>

<!-- ═══ Spread-Out Colorful Host CTA Section ═══ -->
<section class="home-cta-section reveal">
    <!-- Ambient colorful background orbs -->
    <div class="cta-orb cta-orb-1" aria-hidden="true"></div>
    <div class="cta-orb cta-orb-2" aria-hidden="true"></div>
    <div class="cta-orb cta-orb-3" aria-hidden="true"></div>

    <div class="home-cta-content">
        <span class="panel-badge" style="margin: 0 auto 0.85rem; background: rgba(var(--brand), 0.12); color: rgb(var(--brand)); border: 1px solid rgba(var(--brand), 0.2);">
            ✨ Host &amp; Organizer Hub
        </span>
        <h2>Planning an event? Sell tickets in minutes</h2>
        <p class="home-cta-lede">
            Join thousands of organizers using <strong><?= e(site_setting('site_title', 'Eventlyy')) ?></strong> to create custom ticket tiers, scan attendee QR passes at the gate, and receive automated payouts.
        </p>

        <div class="home-cta-actions">
            <?php if (app()->user && app()->user->isHost()): ?>
                <a href="{{ route('host/event/create') }}" class="admin-action-btn primary cta-btn-glow">
                    Create Event Now →
                </a>
            <?php else: ?>
                <a href="{{ route('register') }}" class="admin-action-btn primary cta-btn-glow">
                    Become a Host &amp; Start Free →
                </a>
            <?php endif; ?>
            <a href="{{ route('about') }}" class="admin-action-btn cta-btn-outline">
                Learn How It Works
            </a>
        </div>
    </div>
</section>

<?php if ($isFiltered && (!empty($search) || !empty($location))): ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var el = document.getElementById('events-section');
        if (el) {
            el.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    });
</script>
<?php endif; ?>
@endsection