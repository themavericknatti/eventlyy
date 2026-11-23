<?php

/**
 * Clean, High-Contrast Deluxe Gamified Event Passport & Milestone Badges View
 * Modern styling designed for crisp visual contrast, easy readability, and balanced progression.
 *
 * @package Eventlyy
 * @subpackage Views/User
 * @version 2.2.0
 */

$siteName = site_setting('site_title', 'Eventlyy');
$prog = $progression ?? [
    'xp' => 0,
    'level' => 1,
    'prestige' => 0,
    'rank_title' => 'Eventlyy Citizen',
    'rank_tier_key' => 'citizen',
    'next_rank_title' => 'Culture Scout',
    'progress_percent' => 0,
    'level_xp' => 0,
    'level_span' => 500,
    'xp_needed' => 500,
    'next_level_xp' => 500,
    'current_level_min_xp' => 0,
    'can_prestige' => false
];
$prestigeTier = (int)($prog['prestige'] ?? 0);
$rankTierKey = $prog['rank_tier_key'] ?? 'citizen';

$earnedKeys = [];
foreach ($badges as $b) {
    if (!empty($b['badge_key'])) {
        $earnedKeys[] = $b['badge_key'];
    }
}

// Balanced Milestone Badge Suite (Clean micro-credits & rigorous verified milestones)
$allBadgeDefs = [
    // ── Physical Venue Attendance (Strictly Gate Verified) ──
    'first_drop' => [
        'title' => 'First Drop Pioneer',
        'icon' => '🎟️',
        'desc' => 'Purchased ticket & attended at least 3 physical events (verified at venue gate).',
        'reward' => '₦50 Credit',
        'xp' => '+150 XP',
        'category' => 'Venue Attendance',
        'req' => '3 Gate Check-ins'
    ],
    'nightlife_explorer' => [
        'title' => 'Nightlife Explorer',
        'icon' => '🌙',
        'desc' => 'Purchased tickets & attended 6 or more physical events at the venue.',
        'reward' => '₦100 Credit',
        'xp' => '+350 XP',
        'category' => 'Venue Attendance',
        'req' => '6 Gate Check-ins'
    ],
    'festival_legend' => [
        'title' => 'Festival Veteran',
        'icon' => '👑',
        'desc' => 'Purchased tickets & attended 12 or more physical events at the venue.',
        'reward' => '₦150 Credit',
        'xp' => '+600 XP',
        'category' => 'Venue Attendance',
        'req' => '12 Gate Check-ins'
    ],
    'vip_enthusiast' => [
        'title' => 'VIP Connoisseur',
        'icon' => '💎',
        'desc' => 'Purchased and attended an event with a VIP or Table tier admission pass.',
        'reward' => '₦75 Credit',
        'xp' => '+400 XP',
        'category' => 'Venue Attendance',
        'req' => '1 VIP Admission'
    ],

    // ── Arena & Trivia ──
    'trivia_master' => [
        'title' => 'Trivia Mastermind',
        'icon' => '🧠',
        'desc' => 'Scored a winning round on an event live trivia game.',
        'reward' => '₦50 Credit',
        'xp' => '+200 XP',
        'category' => 'Arena & Trivia',
        'req' => '1 Winning Round'
    ],
    'trivia_genius' => [
        'title' => 'Speed Genius',
        'icon' => '⚡',
        'desc' => 'Achieved winning streaks in 5 or more trivia challenges.',
        'reward' => '₦75 Credit',
        'xp' => '+350 XP',
        'category' => 'Arena & Trivia',
        'req' => '5 Trivia Streaks'
    ],

    // ── Voting & Contests ──
    'first_vote' => [
        'title' => 'Culture Voter',
        'icon' => '🗳️',
        'desc' => 'Cast votes across 3 or more event polls or award contests.',
        'reward' => '₦25 Credit',
        'xp' => '+120 XP',
        'category' => 'Culture Polls',
        'req' => '3 Poll Votes'
    ],
    'poll_regular' => [
        'title' => 'Ballot Champion',
        'icon' => '🏆',
        'desc' => 'Cast 8 or more votes in community award polls.',
        'reward' => '₦50 Credit',
        'xp' => '+250 XP',
        'category' => 'Culture Polls',
        'req' => '8 Poll Votes'
    ],
    'power_voter' => [
        'title' => 'Power Voter',
        'icon' => '⚡',
        'desc' => 'Purchased a Power Vote pack to boost your nominee in official awards.',
        'reward' => '₦50 Credit',
        'xp' => '+250 XP',
        'category' => 'Culture Polls',
        'req' => '1 Power Vote Pack'
    ],

    // ── Social & Stage ──
    'song_suggester' => [
        'title' => 'Setlist Curator',
        'icon' => '🎵',
        'desc' => 'Suggested songs or speaker Q&A topics that received crowd upvotes.',
        'reward' => '₦25 Credit',
        'xp' => '+120 XP',
        'category' => 'Social & Stage',
        'req' => '1 Upvoted Request'
    ],
    'community_voice' => [
        'title' => 'Hype Starter',
        'icon' => '📣',
        'desc' => 'Started verified discussion threads on event hype walls.',
        'reward' => '₦25 Credit',
        'xp' => '+120 XP',
        'category' => 'Social & Stage',
        'req' => '1 Discussion Thread'
    ],
    'social_butterfly' => [
        'title' => 'Hype Conductor',
        'icon' => '🔥',
        'desc' => 'Active community leader: posted 5 or more discussion or carpool threads.',
        'reward' => '₦50 Credit',
        'xp' => '+300 XP',
        'category' => 'Social & Stage',
        'req' => '5 Community Posts'
    ],

    // ── Prestige Mastery ──
    'prestige_ascendant' => [
        'title' => 'Ascendant Star',
        'icon' => '✨',
        'desc' => 'Conquered Level 6 Living Legend and completed Prestige Rebirth.',
        'reward' => '₦250 Credit',
        'xp' => '+800 XP',
        'category' => 'Prestige Mastery',
        'req' => 'Prestige Rebirth'
    ]
];

$unlockedCount = count($badges);
$totalBadgesCount = count($allBadgeDefs);
$attendedCount = (int)($attendedCount ?? 0);
$ticketCount = (int)($ticketCount ?? 0);
?>

@extends('layouts/main')

@section('title')
My Event Passport &amp; Badges — <?= e($siteName) ?>
@endsection

@section('content')
<div class="passport-page-container">

    <!-- Top Navigation Bar -->
    <div class="p-nav-bar">
        <a href="{{ route('profile') }}" class="p-back-link">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="19" y1="12" x2="5" y2="12"></line>
                <polyline points="12 19 5 12 12 5"></polyline>
            </svg>
            <span>Back to Profile</span>
        </a>

        <?php if ($prestigeTier > 0): ?>
            <div class="p-prestige-badge">
                <span>⭐ PRESTIGE <?= $prestigeTier ?> ASCENDANT</span>
            </div>
        <?php endif; ?>
    </div>

    <!-- ═══════════════════════════════════════════════════════════════
         1. CLEAN HERO PASSPORT CARD (Easy on the Eyes & High Contrast)
         ═══════════════════════════════════════════════════════════════ -->
    <div class="p-hero-card">
        <div class="p-hero-content">
            
            <div class="p-tag-row">
                <span class="p-tag p-tag-brand">✦ Verified Citizen Passport ✦</span>
                <span class="p-tag p-tag-lvl">Level <?= (int)$prog['level'] ?></span>
                <?php if ($prestigeTier > 0): ?>
                    <span class="p-tag p-tag-prestige">★ Prestige <?= $prestigeTier ?></span>
                <?php endif; ?>
            </div>

            <h1 class="p-hero-name"><?= e($user->name) ?></h1>
            <div class="p-hero-rank-line">
                <span class="p-rank-label">Rank:</span>
                <span class="p-rank-value"><?= e($prog['rank_title']) ?></span>
            </div>

            <p class="p-hero-summary">
                Your permanent verified event record. Earn XP and loyalty credits by purchasing tickets, physically checking in at venues, voting in award polls, and engaging with the community.
            </p>

            <!-- Dynamic Animated Gamified XP Progress Bar -->
            <div class="p-xp-card">
                <div class="p-xp-header">
                    <div class="p-xp-stat-left">
                        <span class="p-xp-level-badge">Level <?= (int)$prog['level'] ?> &bull; <?= e($prog['rank_title']) ?></span>
                        <div class="p-xp-numbers">
                            <?php if ($prog['level'] >= 6): ?>
                                <strong class="p-xp-accent-gold"><?= number_format($prog['xp']) ?> XP</strong> <span class="p-xp-sub">Pinnacle Mastered</span>
                            <?php else: ?>
                                <strong class="p-xp-accent-green"><?= number_format($prog['level_xp'] ?? 0) ?></strong> <span class="p-xp-divider">/</span> <?= number_format($prog['level_span'] ?? 500) ?> XP
                                <span class="p-xp-lifetime-tag"><?= number_format($prog['xp']) ?> Lifetime Total</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="p-xp-stat-right">
                        <?php if ($prog['level'] >= 6): ?>
                            <span class="p-xp-cap-badge">🏆 Ready for Prestige Rebirth!</span>
                        <?php else: ?>
                            <div class="p-xp-needed-wrap">
                                <span class="p-xp-needed-lbl">Next Rank</span>
                                <span class="p-xp-needed-val"><strong><?= number_format($prog['xp_needed'] ?? 0) ?> XP</strong> to <?= e($prog['next_rank_title'] ?? 'Next Tier') ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- XP Bar Track with Smooth Loading Fill & Shimmer -->
                <div class="p-xp-track" role="progressbar" aria-valuenow="<?= (int)$prog['progress_percent'] ?>" aria-valuemin="0" aria-valuemax="100" title="<?= (int)$prog['progress_percent'] ?>% completed">
                    <div class="p-xp-fill <?= ($prog['level'] >= 6) ? 'is-max' : '' ?>" style="--p-target-width: <?= (int)$prog['progress_percent'] ?>%; width: <?= (int)$prog['progress_percent'] ?>%;">
                        <div class="p-xp-shimmer"></div>
                    </div>
                </div>

                <!-- Range Scale Footnotes (Min Threshold -> Percent -> Target Threshold) -->
                <div class="p-xp-scale-row">
                    <span class="p-xp-scale-item"><?= number_format($prog['current_level_min_xp']) ?> XP</span>
                    <span class="p-xp-scale-current">
                        <?php if ($prog['level'] >= 6): ?>
                            ★ 100% Conquered &bull; Living Legend Rank
                        <?php else: ?>
                            <strong><?= (int)$prog['progress_percent'] ?>%</strong> of Level <?= (int)$prog['level'] ?> Completed
                        <?php endif; ?>
                    </span>
                    <span class="p-xp-scale-item"><?= number_format($prog['next_level_xp']) ?> XP</span>
                </div>
            </div>

            <!-- High-Contrast Clean Stats Ticker -->
            <div class="p-stats-row">
                <div class="p-stat-item">
                    <span class="p-stat-lbl">Events Attended</span>
                    <strong class="p-stat-num p-stat-attended"><?= number_format($attendedCount) ?></strong>
                    <span class="p-stat-sub">Gate Scanned</span>
                </div>
                <div class="p-stat-item">
                    <span class="p-stat-lbl">Tickets Purchased</span>
                    <strong class="p-stat-num"><?= number_format($ticketCount) ?></strong>
                    <span class="p-stat-sub">Verified Orders</span>
                </div>
                <div class="p-stat-item">
                    <span class="p-stat-lbl">Unlocked Badges</span>
                    <strong class="p-stat-num"><?= $unlockedCount ?> <span class="p-stat-sub">/ <?= $totalBadgesCount ?></span></strong>
                    <span class="p-stat-sub">Earned Stamps</span>
                </div>
                <div class="p-stat-item p-stat-credits">
                    <span class="p-stat-lbl">Available Credits</span>
                    <strong class="p-stat-num p-credits-val"><?= format_price($totalCredits) ?></strong>
                    <span class="p-stat-sub">Usable at Checkout</span>
                </div>
            </div>

        </div>

        <!-- High-Contrast Clean Emblem Seal -->
        <div class="p-emblem-wrap">
            <div class="p-emblem-disc">
                <span class="p-emblem-star">★</span>
                <span class="p-emblem-level">LVL <?= (int)$prog['level'] ?></span>
                <span class="p-emblem-tier"><?= strtoupper(e($rankTierKey)) ?></span>
                <?php if ($prestigeTier > 0): ?>
                    <span class="p-emblem-prestige">PRESTIGE <?= $prestigeTier ?></span>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════════════
         2. PRESTIGE REBIRTH BANNER (Level 6 Milestone Unlock)
         ═══════════════════════════════════════════════════════════════ -->
    <?php if (!empty($prog['can_prestige'])): ?>
        <div class="p-prestige-card">
            <div class="p-prestige-info">
                <div class="p-prestige-icon">👑</div>
                <div>
                    <h3 class="p-prestige-title">Living Legend Rank Conquered!</h3>
                    <p class="p-prestige-desc">
                        You have mastered all ranks! Ascend to <strong>Prestige Tier <?= $prestigeTier + 1 ?></strong> to claim the exclusive Ascendant Star badge and an instant <strong>₦<?= number_format(500 * ($prestigeTier + 1)) ?></strong> Event Credit bonus! Your lifetime rank and badges remain celebrated.
                    </p>
                </div>
            </div>
            <form method="POST" action="<?= url('/user/passport/prestige') ?>" onsubmit="return confirm('Ready for Prestige Rebirth? You will ascend to Tier <?= $prestigeTier + 1 ?> and receive loyalty Event Credits!');">
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>" />
                <button type="submit" class="p-prestige-btn">
                    <span>✨</span> Ascend to Prestige <?= $prestigeTier + 1 ?>
                </button>
            </form>
        </div>
    <?php endif; ?>

    <!-- ═══════════════════════════════════════════════════════════════
         3. ACTIVE EVENT CREDITS ALERT (Used for Ticket Purchases)
         ═══════════════════════════════════════════════════════════════ -->
    <?php if ($totalCredits > 0): ?>
        <div class="p-credits-banner">
            <div class="p-credits-text-wrap">
                <span class="p-credits-icon">💳</span>
                <div>
                    <h4 class="p-credits-heading"><?= format_price($totalCredits) ?> in Active Event Loyalty Credits</h4>
                    <p class="p-credits-subheading">
                        Earned from event challenges and verified check-ins. Automatically deducts as a discount at checkout when buying tickets!
                    </p>
                </div>
            </div>
            <a href="{{ route('/') }}#events-section" class="p-credits-action-btn">
                Browse Events &amp; Spend →
            </a>
        </div>
    <?php endif; ?>

    <!-- ═══════════════════════════════════════════════════════════════
         4. BADGES GRID SHOWCASE (Clean Cards & Easy on the Eyes)
         ═══════════════════════════════════════════════════════════════ -->
    <section class="p-badges-section">
        <div class="p-section-top">
            <div>
                <span class="p-section-tag">Milestone Challenges</span>
                <h2 class="p-section-title">Digital Passport Stamps</h2>
                <p class="p-section-desc">Attend physical events, vote in live contests, and engage with the community to unlock stamps and credits.</p>
            </div>
            <div class="p-badge-counter">
                <span><?= $unlockedCount ?> of <?= $totalBadgesCount ?> Unlocked</span>
            </div>
        </div>

        <div class="p-badges-grid">
            <?php foreach ($allBadgeDefs as $key => $b): ?>
                <?php $isEarned = in_array($key, $earnedKeys); ?>
                <div class="p-badge-card <?= $isEarned ? 'is-unlocked' : 'is-pending' ?>">
                    
                    <div class="p-badge-avatar">
                        <span class="p-badge-emoji"><?= $b['icon'] ?></span>
                        <?php if ($isEarned): ?>
                            <span class="p-badge-status-dot p-dot-success" title="Unlocked">✓</span>
                        <?php else: ?>
                            <span class="p-badge-status-dot p-dot-lock" title="Pending Challenge">🔒</span>
                        <?php endif; ?>
                    </div>

                    <div class="p-badge-info">
                        <div class="p-badge-meta-top">
                            <span class="p-badge-category"><?= e($b['category']) ?></span>
                            <?php if ($isEarned): ?>
                                <span class="p-status-pill p-status-unlocked">✓ UNLOCKED</span>
                            <?php else: ?>
                                <span class="p-status-pill p-status-locked"><?= e($b['req'] ?? 'LOCKED') ?></span>
                            <?php endif; ?>
                        </div>

                        <h4 class="p-badge-title"><?= e($b['title']) ?></h4>
                        <p class="p-badge-desc"><?= e($b['desc']) ?></p>

                        <div class="p-badge-meta-row">
                            <span class="p-pill-reward <?= $isEarned ? 'is-earned' : '' ?>">
                                <?= $isEarned ? 'Earned: ' : 'Reward: ' ?><strong><?= e($b['reward']) ?></strong>
                            </span>
                            <span class="p-pill-xp"><?= e($b['xp']) ?></span>
                        </div>
                    </div>

                </div>
            <?php endforeach; ?>
        </div>
    </section>

</div>

<style>
/* ═══════════════════════════════════════════════════════════════
   PASSPORT HIGH-CONTRAST EYE-FRIENDLY STYLING
   Engineered for maximum legibility, comfortable contrast, and clean layout.
   ═══════════════════════════════════════════════════════════════ */
.passport-page-container {
    max-width: 1080px;
    margin: 0 auto;
    padding: 1.5rem 1rem 4rem;
}

.p-nav-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.25rem;
}

.p-back-link {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 0.88rem;
    font-weight: 700;
    color: #334155;
    text-decoration: none;
    transition: color 0.15s ease;
}
.p-back-link:hover { color: #0f172a; }

.p-prestige-badge {
    padding: 0.35rem 0.85rem;
    background: #fef3c7;
    border: 1.5px solid #d97706;
    border-radius: 999px;
    font-size: 0.76rem;
    font-weight: 800;
    color: #92400e;
    letter-spacing: 0.05em;
}

/* ── Hero Passport Card (Deep navy-slate, high contrast, readable) ── */
.p-hero-card {
    background: linear-gradient(145deg, #1e293b 0%, #0f172a 100%);
    border: 1.5px solid #334155;
    border-radius: 20px;
    padding: clamp(1.75rem, 3.5vw, 2.5rem);
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 2rem;
    color: #ffffff;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.12);
}

.p-hero-content {
    flex: 1;
    min-width: 0;
    max-width: 680px;
}

.p-tag-row {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex-wrap: wrap;
    margin-bottom: 0.75rem;
}

.p-tag {
    padding: 0.25rem 0.75rem;
    border-radius: 999px;
    font-size: 0.74rem;
    font-weight: 800;
    letter-spacing: 0.04em;
    text-transform: uppercase;
}

.p-tag-brand {
    background: rgba(245, 158, 11, 0.2);
    border: 1px solid rgba(245, 158, 11, 0.5);
    color: #fcd34d;
}

.p-tag-lvl {
    background: rgba(99, 102, 241, 0.2);
    border: 1px solid rgba(99, 102, 241, 0.45);
    color: #c7d2fe;
}

.p-tag-prestige {
    background: rgba(236, 72, 153, 0.2);
    border: 1px solid rgba(236, 72, 153, 0.45);
    color: #fbcfe8;
}

.p-hero-name {
    font-family: inherit;
    font-size: clamp(1.8rem, 4vw, 2.5rem);
    font-weight: 900;
    color: #ffffff;
    margin: 0 0 0.35rem;
    line-height: 1.2;
}

.p-hero-rank-line {
    font-size: 1.05rem;
    color: #cbd5e1;
    margin-bottom: 0.85rem;
}
.p-rank-label { color: #94a3b8; font-weight: 600; }
.p-rank-value { color: #38bdf8; font-weight: 800; margin-left: 5px; }

.p-hero-summary {
    font-size: 0.92rem;
    color: #e2e8f0;
    line-height: 1.5;
    margin: 0 0 1.25rem;
}

/* XP Card */
.p-xp-card {
    background: rgba(15, 23, 42, 0.75);
    border: 1.5px solid #334155;
    border-radius: 14px;
    padding: 1rem 1.15rem;
    margin-bottom: 1.25rem;
    box-shadow: 0 4px 14px rgba(0, 0, 0, 0.2);
}

.p-xp-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    gap: 0.75rem;
    margin-bottom: 0.65rem;
    flex-wrap: wrap;
}

.p-xp-stat-left {
    display: flex;
    flex-direction: column;
    gap: 0.2rem;
}

.p-xp-level-badge {
    font-size: 0.74rem;
    font-weight: 800;
    color: #38bdf8;
    text-transform: uppercase;
    letter-spacing: 0.04em;
}

.p-xp-numbers {
    font-size: 1.05rem;
    color: #e2e8f0;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 0.35rem;
    flex-wrap: wrap;
}

.p-xp-accent-green {
    color: #34d399;
    font-size: 1.2rem;
    font-weight: 900;
}

.p-xp-accent-gold {
    color: #fbbf24;
    font-size: 1.2rem;
    font-weight: 900;
}

.p-xp-divider {
    color: #64748b;
    font-weight: 400;
}

.p-xp-sub {
    font-size: 0.82rem;
    color: #94a3b8;
    font-weight: 600;
}

.p-xp-lifetime-tag {
    font-size: 0.75rem;
    font-weight: 700;
    color: #94a3b8;
    background: rgba(255, 255, 255, 0.08);
    border: 1px solid rgba(255, 255, 255, 0.12);
    padding: 0.15rem 0.5rem;
    border-radius: 6px;
    margin-left: 0.25rem;
}

.p-xp-stat-right {
    text-align: right;
}

.p-xp-needed-wrap {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 0.15rem;
}

.p-xp-needed-lbl {
    font-size: 0.68rem;
    font-weight: 700;
    text-transform: uppercase;
    color: #94a3b8;
    letter-spacing: 0.04em;
}

.p-xp-needed-val {
    font-size: 0.88rem;
    color: #cbd5e1;
}
.p-xp-needed-val strong {
    color: #ffffff;
    font-size: 0.95rem;
}

.p-xp-cap-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    background: rgba(245, 158, 11, 0.2);
    border: 1px solid rgba(245, 158, 11, 0.5);
    color: #fcd34d;
    padding: 0.3rem 0.75rem;
    border-radius: 999px;
    font-size: 0.8rem;
    font-weight: 800;
    letter-spacing: 0.03em;
}

/* XP Loading Bar Track */
.p-xp-track {
    width: 100%;
    height: 14px;
    background: #090d16;
    border: 1px solid #1e293b;
    border-radius: 999px;
    overflow: hidden;
    position: relative;
    box-shadow: inset 0 2px 5px rgba(0, 0, 0, 0.6);
}

/* Dynamic Animated XP Fill with Page-Load Progression */
.p-xp-fill {
    height: 100%;
    background: linear-gradient(90deg, #10b981 0%, #06b6d4 40%, #6366f1 80%, #8b5cf6 100%);
    border-radius: 999px;
    position: relative;
    overflow: hidden;
    box-shadow: 0 0 12px rgba(16, 185, 129, 0.45);
    animation: xpBarLoad 1.2s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

.p-xp-fill.is-max {
    background: linear-gradient(90deg, #f59e0b 0%, #fbbf24 50%, #d97706 100%);
    box-shadow: 0 0 16px rgba(245, 158, 11, 0.6);
}

@keyframes xpBarLoad {
    0% {
        width: 0%;
        opacity: 0.6;
    }
    100% {
        width: var(--p-target-width, 100%);
        opacity: 1;
    }
}

/* Polished Shimmer Wave across XP Bar */
.p-xp-shimmer {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(
        90deg,
        rgba(255, 255, 255, 0) 0%,
        rgba(255, 255, 255, 0.35) 50%,
        rgba(255, 255, 255, 0) 100%
    );
    animation: xpShimmerWave 2.4s infinite ease-in-out;
}

@keyframes xpShimmerWave {
    0% { transform: translateX(-100%); }
    100% { transform: translateX(200%); }
}

/* Range Scale Footnotes (Min -> Current -> Max) */
.p-xp-scale-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 0.74rem;
    color: #94a3b8;
    margin-top: 0.45rem;
}

.p-xp-scale-item {
    font-family: ui-monospace, SFMono-Regular, monospace;
    font-size: 0.72rem;
    font-weight: 600;
    color: #64748b;
}

.p-xp-scale-current {
    font-size: 0.76rem;
    color: #cbd5e1;
    text-align: center;
}
.p-xp-scale-current strong {
    color: #38bdf8;
}

/* Stats Row */
.p-stats-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(130px, 1fr));
    gap: 0.75rem;
}

.p-stat-item {
    background: #0f172a;
    border: 1.5px solid #334155;
    border-radius: 10px;
    padding: 0.65rem 0.85rem;
    display: flex;
    flex-direction: column;
    gap: 0.15rem;
}

.p-stat-lbl {
    font-size: 0.68rem;
    font-weight: 700;
    text-transform: uppercase;
    color: #94a3b8;
    letter-spacing: 0.04em;
}

.p-stat-num {
    font-size: 1.2rem;
    font-weight: 800;
    color: #ffffff;
    line-height: 1.2;
}
.p-stat-attended { color: #38bdf8; }
.p-credits-val { color: #fbbf24; }
.p-stat-sub { font-size: 0.72rem; color: #94a3b8; font-weight: 500; }

/* Emblem Seal */
.p-emblem-wrap {
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.p-emblem-disc {
    width: 135px;
    height: 135px;
    border-radius: 50%;
    background: #0f172a;
    border: 3px solid #d97706;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
    box-shadow: 0 0 25px rgba(217, 119, 6, 0.3);
    padding: 8px;
    box-sizing: border-box;
}

.p-emblem-star { font-size: 1.3rem; color: #fbbf24; line-height: 1; margin-bottom: 2px; }
.p-emblem-level { font-family: ui-monospace, SFMono-Regular, monospace; font-size: 0.95rem; font-weight: 900; color: #ffffff; letter-spacing: 0.05em; }
.p-emblem-tier { font-size: 0.68rem; font-weight: 800; color: #fbbf24; letter-spacing: 0.1em; text-transform: uppercase; margin-top: 2px; }
.p-emblem-prestige { font-size: 0.62rem; font-weight: 800; color: #f472b6; letter-spacing: 0.06em; margin-top: 2px; }

/* ── Prestige Rebirth Card ── */
.p-prestige-card {
    margin-top: 1.25rem;
    background: #fffbeb;
    border: 1.5px solid #f59e0b;
    border-radius: 16px;
    padding: 1.25rem 1.5rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1rem;
}

.p-prestige-info {
    display: flex;
    align-items: center;
    gap: 1rem;
    max-width: 650px;
}

.p-prestige-icon {
    font-size: 2.2rem;
    line-height: 1;
}

.p-prestige-title {
    margin: 0 0 0.25rem;
    font-size: 1.15rem;
    font-weight: 800;
    color: #92400e;
}

.p-prestige-desc {
    margin: 0;
    font-size: 0.88rem;
    color: #78350f;
    line-height: 1.45;
}

.p-prestige-btn {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    padding: 0.75rem 1.35rem;
    background: #d97706;
    color: #ffffff;
    font-weight: 800;
    font-size: 0.9rem;
    border: none;
    border-radius: 10px;
    cursor: pointer;
    box-shadow: 0 4px 12px rgba(217, 119, 6, 0.35);
    transition: transform 0.15s ease, background 0.15s ease;
}
.p-prestige-btn:hover {
    background: #b45309;
    transform: translateY(-1px);
}

/* ── Credits Banner ── */
.p-credits-banner {
    margin-top: 1.25rem;
    background: #ecfdf5;
    border: 1.5px solid #10b981;
    border-radius: 14px;
    padding: 1rem 1.35rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1rem;
}

.p-credits-text-wrap {
    display: flex;
    align-items: center;
    gap: 0.85rem;
}

.p-credits-icon {
    font-size: 1.8rem;
}

.p-credits-heading {
    margin: 0;
    font-size: 1.05rem;
    font-weight: 800;
    color: #065f46;
}

.p-credits-subheading {
    margin: 0.2rem 0 0;
    font-size: 0.86rem;
    color: #047857;
}

.p-credits-action-btn {
    padding: 0.6rem 1.15rem;
    background: #10b981;
    color: #ffffff;
    font-weight: 700;
    font-size: 0.85rem;
    border-radius: 8px;
    text-decoration: none;
    transition: background 0.15s ease;
}
.p-credits-action-btn:hover { background: #059669; }

/* ── Badges Section (Crisp, High-Contrast Cards, Easy on the Eyes) ── */
.p-badges-section {
    margin-top: 2.75rem;
}

.p-section-top {
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    flex-wrap: wrap;
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.p-section-tag {
    font-size: 0.74rem;
    font-weight: 800;
    text-transform: uppercase;
    color: #4f46e5;
    letter-spacing: 0.05em;
}

.p-section-title {
    font-family: inherit;
    font-size: 1.85rem;
    font-weight: 800;
    color: #0f172a;
    margin: 0.25rem 0 0.35rem;
    line-height: 1.2;
}

.p-section-desc {
    font-size: 0.92rem;
    color: #334155;
    margin: 0;
}

.p-badge-counter {
    padding: 0.4rem 0.9rem;
    background: #f1f5f9;
    border: 1.5px solid #cbd5e1;
    border-radius: 999px;
    font-size: 0.82rem;
    font-weight: 700;
    color: #1e293b;
}

/* Badges Grid */
.p-badges-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 1.15rem;
}

/* Individual Badge Card (High Contrast, Deep Readable Typography) */
.p-badge-card {
    background: #ffffff;
    border: 1.5px solid #cbd5e1;
    border-radius: 16px;
    padding: 1.35rem;
    display: flex;
    gap: 1.15rem;
    align-items: flex-start;
    transition: transform 0.15s ease, box-shadow 0.15s ease, border-color 0.15s ease;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
}

.p-badge-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 18px rgba(0, 0, 0, 0.08);
}

/* Unlocked State */
.p-badge-card.is-unlocked {
    border-color: #f59e0b;
    background: #fffdfa;
    box-shadow: 0 4px 14px rgba(245, 158, 11, 0.08);
}

/* Locked / Challenge State (Clear, distinct, high-contrast text) */
.p-badge-card.is-pending {
    background: #f8fafc;
    border-color: #e2e8f0;
}

/* Badge Icon Avatar */
.p-badge-avatar {
    position: relative;
    width: 60px;
    height: 60px;
    min-width: 60px;
    border-radius: 16px;
    background: #f1f5f9;
    border: 1.5px solid #cbd5e1;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.p-badge-card.is-unlocked .p-badge-avatar {
    background: #fef3c7;
    border-color: #f59e0b;
}

.p-badge-emoji {
    font-size: 1.9rem;
    line-height: 1;
}

.p-badge-status-dot {
    position: absolute;
    width: 22px;
    height: 22px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.75rem;
    font-weight: 800;
    border: 2px solid #ffffff;
}

.p-dot-success {
    top: -6px;
    right: -6px;
    background: #10b981;
    color: #ffffff;
}

.p-dot-lock {
    bottom: -6px;
    right: -6px;
    background: #64748b;
    color: #ffffff;
    font-size: 0.7rem;
}

/* Badge Content */
.p-badge-info {
    flex: 1;
    min-width: 0;
}

.p-badge-meta-top {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 0.35rem;
}

.p-badge-category {
    font-size: 0.7rem;
    font-weight: 800;
    text-transform: uppercase;
    color: #475569;
    letter-spacing: 0.04em;
}

.p-status-pill {
    font-size: 0.65rem;
    font-weight: 800;
    padding: 0.15rem 0.45rem;
    border-radius: 4px;
    letter-spacing: 0.03em;
}

.p-status-unlocked {
    background: #dcfce7;
    color: #166534;
    border: 1px solid #bbf7d0;
}

.p-status-locked {
    background: #f1f5f9;
    color: #475569;
    border: 1px solid #cbd5e1;
}

.p-badge-title {
    font-size: 1.1rem;
    font-weight: 800;
    color: #0f172a;
    margin: 0 0 0.35rem;
    line-height: 1.3;
}

.p-badge-card.is-unlocked .p-badge-title {
    color: #92400e;
}

.p-badge-desc {
    font-size: 0.86rem;
    font-weight: 500;
    color: #334155;
    line-height: 1.5;
    margin: 0 0 0.75rem;
}

/* Reward & XP Pills */
.p-badge-meta-row {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.p-pill-reward {
    font-size: 0.75rem;
    font-weight: 700;
    color: #92400e;
    background: #fef3c7;
    border: 1px solid #fde68a;
    padding: 0.22rem 0.6rem;
    border-radius: 6px;
}
.p-pill-reward.is-earned {
    color: #065f46;
    background: #d1fae5;
    border-color: #a7f3d0;
}

.p-pill-xp {
    font-size: 0.75rem;
    font-weight: 800;
    color: #4338ca;
    background: #e0e7ff;
    border: 1px solid #c7d2fe;
    padding: 0.22rem 0.6rem;
    border-radius: 6px;
}

/* ── Dark Theme High-Contrast Overrides ── */
:root[data-theme='dark'] .p-back-link { color: #cbd5e1; }
:root[data-theme='dark'] .p-back-link:hover { color: #ffffff; }

:root[data-theme='dark'] .p-section-title { color: #f8fafc; }
:root[data-theme='dark'] .p-section-desc { color: #cbd5e1; }
:root[data-theme='dark'] .p-badge-counter { background: #1e293b; border-color: #334155; color: #f8fafc; }

:root[data-theme='dark'] .p-badge-card {
    background: #1e293b;
    border-color: #334155;
}
:root[data-theme='dark'] .p-badge-card.is-unlocked {
    background: #1e2538;
    border-color: #f59e0b;
}
:root[data-theme='dark'] .p-badge-card.is-pending {
    background: #111827;
    border-color: #1f2937;
}

:root[data-theme='dark'] .p-badge-avatar {
    background: #0f172a;
    border-color: #334155;
}
:root[data-theme='dark'] .p-badge-card.is-unlocked .p-badge-avatar {
    background: #3b2a1a;
    border-color: #f59e0b;
}

:root[data-theme='dark'] .p-badge-title { color: #f8fafc; }
:root[data-theme='dark'] .p-badge-card.is-unlocked .p-badge-title { color: #fbbf24; }
:root[data-theme='dark'] .p-badge-desc { color: #cbd5e1; }
:root[data-theme='dark'] .p-badge-category { color: #94a3b8; }

:root[data-theme='dark'] .p-status-locked {
    background: #1f2937;
    color: #cbd5e1;
    border-color: #374151;
}

/* ── Mobile Responsive ── */
@media (max-width: 768px) {
    .p-hero-card {
        flex-direction: column;
        align-items: flex-start;
        padding: 1.5rem 1.25rem;
    }
    .p-emblem-wrap {
        align-self: center;
        margin-top: 0.5rem;
    }
    .p-badges-grid {
        grid-template-columns: 1fr;
    }
    .p-stats-row {
        grid-template-columns: 1fr 1fr;
    }
}
</style>
@endsection
