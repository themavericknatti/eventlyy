<?php

/**
 * Clean, High-Contrast Deluxe Gamified Event Passport & Milestone Badges View
 * Modern styling designed for crisp visual contrast, easy readability, and balanced progression.
 *
 * @package Eventlyy
 * @subpackage Views/User
 * @version 2.1.0
 */

$siteName = site_setting('site_title', 'Eventlyy');
$prog = $progression ?? [
    'xp' => 0,
    'level' => 1,
    'prestige' => 0,
    'rank_title' => 'Eventlyy Citizen',
    'rank_tier_key' => 'citizen',
    'progress_percent' => 0,
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

// Balanced Milestone Badge Suite (Clean values & achievable yet rewarding milestones)
$allBadgeDefs = [
    // ── Live Attendance ──
    'first_drop' => [
        'title' => 'First Drop Pioneer',
        'icon' => '🎟️',
        'desc' => 'Claimed and verified at least 2 live event tickets.',
        'reward' => '₦150 Credit',
        'xp' => '+150 XP',
        'category' => 'Live Attendance'
    ],
    'nightlife_explorer' => [
        'title' => 'Nightlife Explorer',
        'icon' => '🌙',
        'desc' => 'Attended and experienced 5 or more live events.',
        'reward' => '₦300 Credit',
        'xp' => '+350 XP',
        'category' => 'Live Attendance'
    ],
    'festival_legend' => [
        'title' => 'Festival Veteran',
        'icon' => '👑',
        'desc' => 'True live event regular: attended 10 or more live events.',
        'reward' => '₦500 Credit',
        'xp' => '+600 XP',
        'category' => 'Live Attendance'
    ],
    'vip_enthusiast' => [
        'title' => 'VIP Connoisseur',
        'icon' => '💎',
        'desc' => 'Acquired a VIP or Table tier admission pass.',
        'reward' => '₦300 Credit',
        'xp' => '+400 XP',
        'category' => 'Live Attendance'
    ],

    // ── Arena & Trivia ──
    'trivia_master' => [
        'title' => 'Trivia Mastermind',
        'icon' => '🧠',
        'desc' => 'Scored a winning round on an event trivia game.',
        'reward' => '₦150 Credit',
        'xp' => '+200 XP',
        'category' => 'Arena & Trivia'
    ],
    'trivia_genius' => [
        'title' => 'Speed Genius',
        'icon' => '⚡',
        'desc' => 'Achieved winning streaks in 5 or more trivia challenges.',
        'reward' => '₦250 Credit',
        'xp' => '+350 XP',
        'category' => 'Arena & Trivia'
    ],

    // ── Voting & Contests ──
    'first_vote' => [
        'title' => 'Culture Voter',
        'icon' => '🗳️',
        'desc' => 'Cast votes across 3 or more event polls or award contests.',
        'reward' => '₦100 Credit',
        'xp' => '+120 XP',
        'category' => 'Culture Polls'
    ],
    'poll_regular' => [
        'title' => 'Ballot Champion',
        'icon' => '🏆',
        'desc' => 'Cast 8 or more votes in community award polls.',
        'reward' => '₦200 Credit',
        'xp' => '+250 XP',
        'category' => 'Culture Polls'
    ],
    'power_voter' => [
        'title' => 'Power Voter',
        'icon' => '⚡',
        'desc' => 'Purchased a Power Vote pack to boost your nominee.',
        'reward' => '₦200 Credit',
        'xp' => '+250 XP',
        'category' => 'Culture Polls'
    ],

    // ── Social & Stage ──
    'song_suggester' => [
        'title' => 'Setlist Curator',
        'icon' => '🎵',
        'desc' => 'Suggested songs or speaker Q&A topics that received crowd upvotes.',
        'reward' => '₦100 Credit',
        'xp' => '+120 XP',
        'category' => 'Social & Stage'
    ],
    'community_voice' => [
        'title' => 'Hype Starter',
        'icon' => '📣',
        'desc' => 'Started verified discussion threads on event hype walls.',
        'reward' => '₦100 Credit',
        'xp' => '+120 XP',
        'category' => 'Social & Stage'
    ],
    'social_butterfly' => [
        'title' => 'Hype Conductor',
        'icon' => '🔥',
        'desc' => 'Active community leader: posted 5 or more discussion or carpool threads.',
        'reward' => '₦250 Credit',
        'xp' => '+300 XP',
        'category' => 'Social & Stage'
    ],

    // ── Prestige Mastery ──
    'prestige_ascendant' => [
        'title' => 'Ascendant Star',
        'icon' => '✨',
        'desc' => 'Conquered Level 6 Living Legend and completed Prestige Rebirth.',
        'reward' => '₦1,000 Credit',
        'xp' => '+800 XP',
        'category' => 'Prestige Mastery'
    ]
];

$unlockedCount = count($badges);
$totalBadgesCount = count($allBadgeDefs);
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
                <span class="p-tag p-tag-brand">✦ Citizen Passport ✦</span>
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
                Your permanent verified event record. Earn XP and loyalty credits by claiming passes, voting in live award polls, and interacting on community hype walls.
            </p>

            <!-- Clean XP Progress Bar -->
            <div class="p-xp-card">
                <div class="p-xp-header">
                    <span class="p-xp-current">
                        <strong><?= number_format($prog['xp']) ?> XP</strong> Lifetime Earned
                    </span>
                    <span class="p-xp-goal">
                        <?php if ($prog['level'] >= 6): ?>
                            🏆 Max Level Conquered!
                        <?php else: ?>
                            Next Level: <strong><?= number_format($prog['next_level_xp']) ?> XP</strong>
                        <?php endif; ?>
                    </span>
                </div>
                <div class="p-xp-track">
                    <div class="p-xp-fill" style="width: <?= (int)$prog['progress_percent'] ?>%;"></div>
                </div>
                <div class="p-xp-footer">
                    <span><?= (int)$prog['progress_percent'] ?>% to next ranking</span>
                </div>
            </div>

            <!-- Stats Ticker -->
            <div class="p-stats-row">
                <div class="p-stat-item">
                    <span class="p-stat-lbl">Unlocked Badges</span>
                    <strong class="p-stat-num"><?= $unlockedCount ?> <span class="p-stat-sub">/ <?= $totalBadgesCount ?></span></strong>
                </div>
                <div class="p-stat-item">
                    <span class="p-stat-lbl">Tickets Claimed</span>
                    <strong class="p-stat-num"><?= number_format($ticketCount) ?></strong>
                </div>
                <div class="p-stat-item p-stat-credits">
                    <span class="p-stat-lbl">Available Credits</span>
                    <strong class="p-stat-num p-credits-val"><?= format_price($totalCredits) ?></strong>
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
                    <span class="p-emblem-prestige">TIER <?= $prestigeTier ?></span>
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
                        You have mastered all ranks! Ascend to <strong>Prestige Tier <?= $prestigeTier + 1 ?></strong> to claim the exclusive Ascendant Star badge and an instant <strong>₦<?= number_format(1000 * ($prestigeTier + 1)) ?></strong> Event Credit bonus!
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
         3. ACTIVE EVENT CREDITS ALERT
         ═══════════════════════════════════════════════════════════════ -->
    <?php if ($totalCredits > 0): ?>
        <div class="p-credits-banner">
            <div class="p-credits-text-wrap">
                <span class="p-credits-icon">💳</span>
                <div>
                    <h4 class="p-credits-heading"><?= format_price($totalCredits) ?> in Active Event Loyalty Credits</h4>
                    <p class="p-credits-subheading">
                        Earned from challenges and activity milestones. Automatically deducts from ticket purchases at checkout.
                    </p>
                </div>
            </div>
            <a href="{{ route('/') }}#events-section" class="p-credits-action-btn">
                Browse Events &amp; Spend →
            </a>
        </div>
    <?php endif; ?>

    <!-- ═══════════════════════════════════════════════════════════════
         4. BADGES GRID SHOWCASE (Clean Cards & Easy to Read)
         ═══════════════════════════════════════════════════════════════ -->
    <section class="p-badges-section">
        <div class="p-section-top">
            <div>
                <span class="p-section-tag">Milestone Challenges</span>
                <h2 class="p-section-title">Digital Passport Stamps</h2>
                <p class="p-section-desc">Complete activities across event lounges to unlock loyalty rewards and rank XP.</p>
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
                        <div class="p-badge-category"><?= e($b['category']) ?></div>
                        <h4 class="p-badge-title"><?= e($b['title']) ?></h4>
                        <p class="p-badge-desc"><?= e($b['desc']) ?></p>

                        <div class="p-badge-meta-row">
                            <span class="p-pill-reward <?= $isEarned ? 'is-earned' : '' ?>">
                                <?= $isEarned ? 'Unlocked: ' : 'Reward: ' ?><strong><?= e($b['reward']) ?></strong>
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
   PASSPORT HIGH-CONTRAST CLEAN STYLING (Easy on the Eyes)
   ═══════════════════════════════════════════════════════════════ */
.passport-page-container {
    max-width: 1060px;
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
    color: #475569;
    text-decoration: none;
    transition: color 0.15s ease;
}
.p-back-link:hover { color: #0f172a; }

.p-prestige-badge {
    padding: 0.35rem 0.85rem;
    background: #fef3c7;
    border: 1px solid #f59e0b;
    border-radius: 999px;
    font-size: 0.76rem;
    font-weight: 800;
    color: #b45309;
    letter-spacing: 0.05em;
}

/* ── Hero Passport Card (High Contrast & Clear) ── */
.p-hero-card {
    background: #0f172a;
    border: 1px solid #334155;
    border-radius: 20px;
    padding: clamp(1.75rem, 3.5vw, 2.75rem);
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 2rem;
    color: #ffffff;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
}

.p-hero-content {
    flex: 1;
    min-width: 0;
    max-width: 660px;
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
    background: rgba(245, 158, 11, 0.15);
    border: 1px solid rgba(245, 158, 11, 0.4);
    color: #fbbf24;
}

.p-tag-lvl {
    background: rgba(99, 102, 241, 0.15);
    border: 1px solid rgba(99, 102, 241, 0.35);
    color: #a5b4fc;
}

.p-tag-prestige {
    background: rgba(236, 72, 153, 0.15);
    border: 1px solid rgba(236, 72, 153, 0.35);
    color: #f472b6;
}

.p-hero-name {
    font-family: inherit;
    font-size: clamp(1.8rem, 4vw, 2.6rem);
    font-weight: 900;
    color: #ffffff;
    margin: 0 0 0.35rem;
    line-height: 1.2;
}

.p-hero-rank-line {
    font-size: 1.05rem;
    color: #94a3b8;
    margin-bottom: 0.85rem;
}
.p-rank-label { color: #64748b; font-weight: 600; }
.p-rank-value { color: #38bdf8; font-weight: 800; margin-left: 5px; }

.p-hero-summary {
    font-size: 0.92rem;
    color: #cbd5e1;
    line-height: 1.5;
    margin: 0 0 1.25rem;
}

/* XP Card */
.p-xp-card {
    background: #1e293b;
    border: 1px solid #334155;
    border-radius: 12px;
    padding: 0.85rem 1rem;
    margin-bottom: 1.25rem;
}

.p-xp-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 0.82rem;
    margin-bottom: 0.5rem;
}
.p-xp-current { color: #cbd5e1; }
.p-xp-current strong { color: #34d399; font-size: 0.95rem; }
.p-xp-goal { color: #94a3b8; }
.p-xp-goal strong { color: #ffffff; }

.p-xp-track {
    width: 100%;
    height: 8px;
    background: #090d16;
    border-radius: 99px;
    overflow: hidden;
}

.p-xp-fill {
    height: 100%;
    background: linear-gradient(90deg, #10b981 0%, #06b6d4 50%, #6366f1 100%);
    border-radius: 99px;
    transition: width 0.3s ease;
}

.p-xp-footer {
    display: flex;
    justify-content: flex-end;
    font-size: 0.72rem;
    color: #64748b;
    margin-top: 0.35rem;
}

/* Stats Row */
.p-stats-row {
    display: flex;
    gap: 0.85rem;
    flex-wrap: wrap;
}

.p-stat-item {
    background: #1e293b;
    border: 1px solid #334155;
    border-radius: 10px;
    padding: 0.65rem 1rem;
    display: flex;
    flex-direction: column;
    gap: 0.2rem;
}

.p-stat-lbl {
    font-size: 0.7rem;
    font-weight: 700;
    text-transform: uppercase;
    color: #94a3b8;
    letter-spacing: 0.04em;
}

.p-stat-num {
    font-size: 1.15rem;
    font-weight: 800;
    color: #ffffff;
}
.p-stat-sub { font-size: 0.82rem; color: #64748b; font-weight: 600; }
.p-credits-val { color: #fbbf24; }

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
    background: #1e293b;
    border: 2px solid #d97706;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
    box-shadow: 0 0 25px rgba(217, 119, 6, 0.25);
    padding: 8px;
    box-sizing: border-box;
}

.p-emblem-star { font-size: 1.3rem; color: #fbbf24; line-height: 1; margin-bottom: 2px; }
.p-emblem-level { font-family: ui-monospace, SFMono-Regular, monospace; font-size: 0.92rem; font-weight: 900; color: #ffffff; letter-spacing: 0.05em; }
.p-emblem-tier { font-size: 0.65rem; font-weight: 800; color: #fbbf24; letter-spacing: 0.1em; text-transform: uppercase; margin-top: 2px; }
.p-emblem-prestige { font-size: 0.6rem; font-weight: 800; color: #f472b6; letter-spacing: 0.06em; margin-top: 2px; }

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
    max-width: 640px;
}

.p-prestige-icon {
    font-size: 2rem;
    line-height: 1;
}

.p-prestige-title {
    margin: 0 0 0.25rem;
    font-size: 1.1rem;
    font-weight: 800;
    color: #92400e;
}

.p-prestige-desc {
    margin: 0;
    font-size: 0.86rem;
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
    font-size: 1.6rem;
}

.p-credits-heading {
    margin: 0;
    font-size: 1rem;
    font-weight: 800;
    color: #065f46;
}

.p-credits-subheading {
    margin: 0.2rem 0 0;
    font-size: 0.84rem;
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

/* ── Badges Section (Crisp, High-Contrast Cards) ── */
.p-badges-section {
    margin-top: 3rem;
}

.p-section-top {
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    flex-wrap: wrap;
    gap: 1rem;
    margin-bottom: 1.75rem;
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
    font-size: 0.9rem;
    color: #475569;
    margin: 0;
}

.p-badge-counter {
    padding: 0.35rem 0.85rem;
    background: #f1f5f9;
    border: 1px solid #cbd5e1;
    border-radius: 999px;
    font-size: 0.8rem;
    font-weight: 700;
    color: #334155;
}

/* Badges Grid */
.p-badges-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(310px, 1fr));
    gap: 1.15rem;
}

/* Individual Badge Card (Clean white surface, strong typography) */
.p-badge-card {
    background: #ffffff;
    border: 1.5px solid #e2e8f0;
    border-radius: 16px;
    padding: 1.25rem;
    display: flex;
    gap: 1.1rem;
    align-items: flex-start;
    transition: transform 0.15s ease, box-shadow 0.15s ease, border-color 0.15s ease;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
}

.p-badge-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
}

/* Unlocked State */
.p-badge-card.is-unlocked {
    border-color: #f59e0b;
    background: #ffffff;
}

/* Locked / Pending State (Visible, crisp text without muddy washouts) */
.p-badge-card.is-pending {
    background: #f8fafc;
    border-color: #e2e8f0;
}
.p-badge-card.is-pending .p-badge-title {
    color: #475569;
}
.p-badge-card.is-pending .p-badge-desc {
    color: #64748b;
}

/* Badge Icon Avatar */
.p-badge-avatar {
    position: relative;
    width: 56px;
    height: 56px;
    min-width: 56px;
    border-radius: 14px;
    background: #f1f5f9;
    border: 1px solid #cbd5e1;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.p-badge-card.is-unlocked .p-badge-avatar {
    background: #fef3c7;
    border-color: #fbbf24;
}

.p-badge-emoji {
    font-size: 1.8rem;
    line-height: 1;
}

.p-badge-status-dot {
    position: absolute;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.72rem;
    font-weight: 800;
    border: 2px solid #ffffff;
}

.p-dot-success {
    top: -5px;
    right: -5px;
    background: #10b981;
    color: #ffffff;
}

.p-dot-lock {
    bottom: -5px;
    right: -5px;
    background: #94a3b8;
    color: #ffffff;
    font-size: 0.68rem;
}

/* Badge Content */
.p-badge-info {
    flex: 1;
    min-width: 0;
}

.p-badge-category {
    font-size: 0.68rem;
    font-weight: 800;
    text-transform: uppercase;
    color: #64748b;
    letter-spacing: 0.04em;
    margin-bottom: 2px;
}

.p-badge-title {
    font-size: 1.05rem;
    font-weight: 800;
    color: #0f172a;
    margin: 0 0 0.25rem;
    line-height: 1.3;
}

.p-badge-desc {
    font-size: 0.82rem;
    color: #475569;
    line-height: 1.45;
    margin: 0 0 0.6rem;
}

/* Reward & XP Pills */
.p-badge-meta-row {
    display: flex;
    align-items: center;
    gap: 0.45rem;
    flex-wrap: wrap;
}

.p-pill-reward {
    font-size: 0.72rem;
    font-weight: 700;
    color: #92400e;
    background: #fef3c7;
    border: 1px solid #fde68a;
    padding: 0.18rem 0.55rem;
    border-radius: 6px;
}
.p-pill-reward.is-earned {
    color: #065f46;
    background: #d1fae5;
    border-color: #a7f3d0;
}

.p-pill-xp {
    font-size: 0.72rem;
    font-weight: 800;
    color: #4338ca;
    background: #e0e7ff;
    border: 1px solid #c7d2fe;
    padding: 0.18rem 0.55rem;
    border-radius: 6px;
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
}
</style>
@endsection
