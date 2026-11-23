<?php

/**
 * Modern Deluxe Gamified Event Passport & Milestone Badges View
 * Features:
 * - Dynamic Leveling & XP Progress Bar (Citizen -> Scout -> Explorer -> Headliner -> VIP Icon -> Living Legend)
 * - Prestige Rebirth System with Ascension Stars & exclusive Crown Badges for endless replayability
 * - Categorized Digital Passport Stamps (Concerts & Festivals, Community Action, Trivia & Knowledge, Stage & DJ)
 * - Active Loyalty Event Credits wallet breakdown
 *
 * @package Eventlyy
 * @subpackage Views/User
 * @version 2.0.0
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

// 13 Full Expanded Badge Milestones across 4 distinct cultural categories
$allBadgeDefs = [
    // ── Category 1: Live Attendance & Tickets ──
    'first_drop' => [
        'title' => 'First Drop Pioneer',
        'icon' => '🎟️',
        'desc' => 'Secured your first live event pass on Eventlyy.',
        'reward' => '₦500 Credit',
        'xp' => '+300 XP',
        'category' => 'Live Attendance'
    ],
    'nightlife_explorer' => [
        'title' => 'Nightlife Explorer',
        'icon' => '🌙',
        'desc' => 'Attended 3 or more live concert or festival events.',
        'reward' => '₦1,000 Credit',
        'xp' => '+600 XP',
        'category' => 'Live Attendance'
    ],
    'festival_legend' => [
        'title' => 'Festival Veteran',
        'icon' => '👑',
        'desc' => 'Conquered 5 or more live events on Eventlyy.',
        'reward' => '₦1,500 Credit',
        'xp' => '+1,000 XP',
        'category' => 'Live Attendance'
    ],
    'vip_enthusiast' => [
        'title' => 'VIP Connoisseur',
        'icon' => '💎',
        'desc' => 'Acquired a VIP or Table tier admission pass.',
        'reward' => '₦800 Credit',
        'xp' => '+700 XP',
        'category' => 'Live Attendance'
    ],

    // ── Category 2: Arena Trivia & Knowledge ──
    'trivia_master' => [
        'title' => 'Trivia Mastermind',
        'icon' => '🧠',
        'desc' => 'Scored a winning round on an event trivia game.',
        'reward' => '₦300 Credit',
        'xp' => '+250 XP',
        'category' => 'Arena & Trivia'
    ],
    'trivia_genius' => [
        'title' => 'Speed Genius',
        'icon' => '⚡',
        'desc' => 'Played 3 or more fast-paced trivia games.',
        'reward' => '₦500 Credit',
        'xp' => '+450 XP',
        'category' => 'Arena & Trivia'
    ],

    // ── Category 3: Voting & Contests ──
    'first_vote' => [
        'title' => 'Culture Voter',
        'icon' => '🗳️',
        'desc' => 'Voted in an event contest or award poll.',
        'reward' => '₦200 Credit',
        'xp' => '+150 XP',
        'category' => 'Culture Polls'
    ],
    'poll_regular' => [
        'title' => 'Ballot Champion',
        'icon' => '🏆',
        'desc' => 'Cast 5 or more votes in community award polls.',
        'reward' => '₦400 Credit',
        'xp' => '+350 XP',
        'category' => 'Culture Polls'
    ],
    'power_voter' => [
        'title' => 'Power Voter',
        'icon' => '⚡',
        'desc' => 'Purchased a Power Vote pack to boost your nominee.',
        'reward' => '₦500 Credit',
        'xp' => '+500 XP',
        'category' => 'Culture Polls'
    ],

    // ── Category 4: Community, Social & Stage ──
    'song_suggester' => [
        'title' => 'Setlist Curator',
        'icon' => '🎵',
        'desc' => 'Suggested a top anthem or speaker Q&A topic.',
        'reward' => '₦150 Credit',
        'xp' => '+150 XP',
        'category' => 'Social & Stage'
    ],
    'community_voice' => [
        'title' => 'Hype Starter',
        'icon' => '📣',
        'desc' => 'Posted on an event Hype Wall or Squad Finder.',
        'reward' => '₦200 Credit',
        'xp' => '+150 XP',
        'category' => 'Social & Stage'
    ],
    'social_butterfly' => [
        'title' => 'Hype Conductor',
        'icon' => '🔥',
        'desc' => 'Shared 3 or more posts or squad finder threads.',
        'reward' => '₦450 Credit',
        'xp' => '+400 XP',
        'category' => 'Social & Stage'
    ],

    // ── Category 5: Prestige Rebirth Endless Trophy ──
    'prestige_ascendant' => [
        'title' => 'Ascendant Star',
        'icon' => '✨',
        'desc' => 'Conquered Level 6 Living Legend and completed Prestige Rebirth.',
        'reward' => '₦2,500 Credit',
        'xp' => '+1,500 XP',
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
<div class="vn-wrapper passport-v2-container">

    <!-- Top Breadcrumb & Quick Link -->
    <div class="p2-top-bar">
        <a href="{{ route('profile') }}" class="p2-back-btn">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="19" y1="12" x2="5" y2="12"></line>
                <polyline points="12 19 5 12 12 5"></polyline>
            </svg>
            <span>Back to Profile</span>
        </a>

        <?php if ($prestigeTier > 0): ?>
            <div class="p2-prestige-indicator">
                <span>⭐ PRESTIGE <?= $prestigeTier ?> ASCENDANT</span>
            </div>
        <?php endif; ?>
    </div>

    <!-- ═══════════════════════════════════════════════════════════════
         1. DELUXE PASSPORT COVER & PROGRESSION HERO
         ═══════════════════════════════════════════════════════════════ -->
    <div class="p2-hero-card tier-<?= e($rankTierKey) ?>">
        <div class="p2-hero-body">
            
            <div class="p2-badge-pill-row">
                <span class="p2-pill-gold">✦ Official Citizen Passport ✦</span>
                <span class="p2-pill-level">LEVEL <?= (int)$prog['level'] ?></span>
                <?php if ($prestigeTier > 0): ?>
                    <span class="p2-pill-prestige">★ PRESTIGE <?= $prestigeTier ?></span>
                <?php endif; ?>
            </div>

            <h1 class="p2-hero-title"><?= e($user->name) ?></h1>
            <div class="p2-hero-rank">
                <span>Rank Title:</span>
                <strong><?= e($prog['rank_title']) ?></strong>
            </div>

            <p class="p2-hero-desc">
                Your permanent verified citizen pass. Earn XP by securing event drops, attending festivals, voting on award polls, and connecting on community hype walls.
            </p>

            <!-- Interactive XP Progression Bar -->
            <div class="p2-progress-wrap">
                <div class="p2-progress-meta">
                    <span class="p2-xp-label">
                        <strong><?= number_format($prog['xp']) ?> XP</strong> Earned
                    </span>
                    <span class="p2-next-label">
                        <?php if ($prog['level'] >= 6): ?>
                            🏆 Max Level Conquered!
                        <?php else: ?>
                            Next Rank at <strong><?= number_format($prog['next_level_xp']) ?> XP</strong>
                        <?php endif; ?>
                    </span>
                </div>
                <div class="p2-progress-track">
                    <div class="p2-progress-fill" style="width: <?= (int)$prog['progress_percent'] ?>%;"></div>
                </div>
                <div class="p2-progress-hint">
                    <?= (int)$prog['progress_percent'] ?>% to next ranking elevation
                </div>
            </div>

            <!-- Quick Stats Ticker -->
            <div class="p2-stats-grid">
                <div class="p2-stat-box">
                    <span class="p2-stat-label">Unlocked Stamps</span>
                    <strong class="p2-stat-val"><?= $unlockedCount ?> / <?= $totalBadgesCount ?></strong>
                </div>
                <div class="p2-stat-box">
                    <span class="p2-stat-label">Tickets Claimed</span>
                    <strong class="p2-stat-val"><?= number_format($ticketCount) ?></strong>
                </div>
                <div class="p2-stat-box highlight">
                    <span class="p2-stat-label">Loyalty Credits</span>
                    <strong class="p2-stat-val p2-gold-val"><?= format_price($totalCredits) ?></strong>
                </div>
            </div>

        </div>

        <!-- Gold Embossed Crest Seal -->
        <div class="p2-hero-crest">
            <div class="p2-crest-seal">
                <div class="p2-crest-inner">
                    <span class="p2-crest-star">★</span>
                    <span class="p2-crest-level">LVL <?= (int)$prog['level'] ?></span>
                    <span class="p2-crest-sub"><?= strtoupper(e($rankTierKey)) ?></span>
                    <?php if ($prestigeTier > 0): ?>
                        <span class="p2-crest-prest">TIER <?= $prestigeTier ?></span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════════════
         2. PRESTIGE REBIRTH BANNER (Appears when Level 6 is reached)
         ═══════════════════════════════════════════════════════════════ -->
    <?php if (!empty($prog['can_prestige'])): ?>
        <div class="p2-prestige-box">
            <div class="p2-prestige-left">
                <div class="p2-prestige-icon">👑</div>
                <div>
                    <h3 class="p2-prestige-title">Living Legend Rank Mastered!</h3>
                    <p class="p2-prestige-desc">
                        You've reached Level 6! Ascend to <strong>Prestige Tier <?= $prestigeTier + 1 ?></strong> to unlock an exclusive Prestige Star badge and an instant <strong>₦<?= number_format(2500 * ($prestigeTier + 1)) ?></strong> Event Credit bonus!
                    </p>
                </div>
            </div>
            <form method="POST" action="<?= url('/user/passport/prestige') ?>" onsubmit="return confirm('Ready for Prestige Rebirth? You will ascend to Tier <?= $prestigeTier + 1 ?> and receive massive bonus Event Credits!');">
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>" />
                <button type="submit" class="p2-prestige-btn">
                    <span>✨</span> Ascend to Prestige Tier <?= $prestigeTier + 1 ?>
                </button>
            </form>
        </div>
    <?php endif; ?>

    <!-- ═══════════════════════════════════════════════════════════════
         3. ACTIVE EVENT CREDITS BAR
         ═══════════════════════════════════════════════════════════════ -->
    <?php if ($totalCredits > 0): ?>
        <div class="p2-credits-strip">
            <div class="p2-credits-info">
                <span class="p2-credits-icon">💳</span>
                <div>
                    <h4 class="p2-credits-title"><?= format_price($totalCredits) ?> Available in Event Loyalty Credits</h4>
                    <p class="p2-credits-sub">
                        Earned from badge challenges &amp; promotions. Automatically deducted from your next ticket order at checkout.
                    </p>
                </div>
            </div>
            <a href="{{ route('/') }}#events-section" class="p2-spend-btn">
                Browse Events &amp; Spend →
            </a>
        </div>
    <?php endif; ?>

    <!-- ═══════════════════════════════════════════════════════════════
         4. BADGE MILESTONES SHOWCASE
         ═══════════════════════════════════════════════════════════════ -->
    <section class="p2-badges-section">
        <div class="p2-section-header">
            <div>
                <span class="p2-category-tag">Cultural Milestones &amp; Challenges</span>
                <h2 class="p2-section-heading">Digital Passport Stamps</h2>
                <p class="p2-section-subheading">Complete live challenges across concerts and community lounges to unlock loyalty rewards and rank XP.</p>
            </div>
            <div class="p2-counter-pill">
                <span><?= $unlockedCount ?> of <?= $totalBadgesCount ?> Collected</span>
            </div>
        </div>

        <div class="p2-badges-grid">
            <?php foreach ($allBadgeDefs as $key => $b): ?>
                <?php $isEarned = in_array($key, $earnedKeys); ?>
                <div class="p2-badge-card <?= $isEarned ? 'is-earned' : 'is-locked' ?>">
                    
                    <div class="p2-badge-icon-wrap">
                        <span class="p2-badge-emoji"><?= $b['icon'] ?></span>
                        <?php if ($isEarned): ?>
                            <span class="p2-badge-check" title="Badge Unlocked">✓</span>
                        <?php else: ?>
                            <span class="p2-badge-lock" title="Challenge Locked">🔒</span>
                        <?php endif; ?>
                    </div>

                    <div class="p2-badge-content">
                        <div class="p2-badge-cat"><?= e($b['category']) ?></div>
                        <h4 class="p2-badge-name"><?= e($b['title']) ?></h4>
                        <p class="p2-badge-desc"><?= e($b['desc']) ?></p>

                        <div class="p2-badge-footer">
                            <span class="p2-reward-chip">
                                <?= $isEarned ? 'Unlocked: ' : 'Reward: ' ?><strong><?= e($b['reward']) ?></strong>
                            </span>
                            <span class="p2-xp-chip"><?= e($b['xp']) ?></span>
                        </div>
                    </div>

                </div>
            <?php endforeach; ?>
        </div>
    </section>

</div>

<style>
/* ═══════════════════════════════════════════════════════════════
   PASSPORT V2 DELUXE DESIGN SYSTEM
   ═══════════════════════════════════════════════════════════════ */
.passport-v2-container {
    max-width: 1080px;
    margin: 0 auto;
    padding: 1.5rem 1rem 4.5rem;
}

.p2-top-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
}

.p2-back-btn {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    font-size: 0.88rem;
    font-weight: 700;
    color: var(--vn-ink-muted, #64748b);
    text-decoration: none;
    transition: color 0.15s ease;
}
.p2-back-btn:hover { color: var(--vn-ink, #0f172a); }

.p2-prestige-indicator {
    padding: 0.35rem 0.85rem;
    background: linear-gradient(135deg, rgba(245, 158, 11, 0.15), rgba(217, 119, 6, 0.25));
    border: 1px solid rgba(245, 158, 11, 0.4);
    border-radius: 999px;
    font-size: 0.76rem;
    font-weight: 800;
    color: #d97706;
    letter-spacing: 0.05em;
}

/* ── Hero Passport Cover ── */
.p2-hero-card {
    position: relative;
    background: linear-gradient(145deg, #1e293b 0%, #0f172a 60%, #020617 100%);
    border: 1.5px solid rgba(255, 255, 255, 0.14);
    border-radius: 24px;
    padding: clamp(1.75rem, 4vw, 3rem);
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 2rem;
    color: #ffffff;
    box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.6), 0 0 40px rgba(99, 102, 241, 0.12);
    overflow: hidden;
}

/* Tier Ambient Accents */
.p2-hero-card.tier-legend {
    border-color: rgba(245, 158, 11, 0.4);
    box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.7), 0 0 50px rgba(245, 158, 11, 0.25);
}

.p2-hero-body {
    flex: 1;
    min-width: 0;
    max-width: 650px;
    z-index: 2;
}

.p2-badge-pill-row {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex-wrap: wrap;
    margin-bottom: 0.85rem;
}

.p2-pill-gold {
    padding: 0.3rem 0.8rem;
    background: rgba(245, 158, 11, 0.15);
    border: 1px solid rgba(245, 158, 11, 0.35);
    border-radius: 999px;
    font-size: 0.74rem;
    font-weight: 800;
    color: #fbbf24;
    text-transform: uppercase;
    letter-spacing: 0.04em;
}

.p2-pill-level {
    padding: 0.3rem 0.8rem;
    background: rgba(99, 102, 241, 0.2);
    border: 1px solid rgba(99, 102, 241, 0.4);
    border-radius: 999px;
    font-size: 0.74rem;
    font-weight: 800;
    color: #a5b4fc;
    letter-spacing: 0.04em;
}

.p2-pill-prestige {
    padding: 0.3rem 0.8rem;
    background: rgba(236, 72, 153, 0.2);
    border: 1px solid rgba(236, 72, 153, 0.4);
    border-radius: 999px;
    font-size: 0.74rem;
    font-weight: 800;
    color: #f472b6;
    letter-spacing: 0.04em;
}

.p2-hero-title {
    font-family: var(--vn-font-serif, Georgia, serif);
    font-size: clamp(2rem, 4.5vw, 3.2rem);
    color: #ffffff;
    margin: 0 0 0.4rem;
    line-height: 1.15;
    letter-spacing: -0.01em;
}

.p2-hero-rank {
    font-size: 1.05rem;
    color: #94a3b8;
    margin-bottom: 0.85rem;
}
.p2-hero-rank strong {
    color: #38bdf8;
    margin-left: 5px;
}

.p2-hero-desc {
    font-size: 0.92rem;
    color: #cbd5e1;
    line-height: 1.55;
    margin-bottom: 1.5rem;
}

/* XP Progression Bar */
.p2-progress-wrap {
    background: rgba(255, 255, 255, 0.06);
    border: 1px solid rgba(255, 255, 255, 0.12);
    border-radius: 14px;
    padding: 1rem 1.15rem;
    margin-bottom: 1.5rem;
}

.p2-progress-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 0.82rem;
    margin-bottom: 0.5rem;
}
.p2-xp-label { color: #f8fafc; }
.p2-xp-label strong { color: #34d399; font-size: 0.95rem; }
.p2-next-label { color: #94a3b8; }
.p2-next-label strong { color: #f8fafc; }

.p2-progress-track {
    width: 100%;
    height: 9px;
    background: rgba(0, 0, 0, 0.4);
    border-radius: 99px;
    overflow: hidden;
}

.p2-progress-fill {
    height: 100%;
    background: linear-gradient(90deg, #10b981 0%, #3b82f6 50%, #8b5cf6 100%);
    border-radius: 99px;
    transition: width 0.4s ease;
}

.p2-progress-hint {
    font-size: 0.72rem;
    color: #64748b;
    margin-top: 0.4rem;
    text-align: right;
}

/* Stats Grid */
.p2-stats-grid {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
}

.p2-stat-box {
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 12px;
    padding: 0.7rem 1.1rem;
    display: flex;
    flex-direction: column;
    gap: 0.15rem;
}

.p2-stat-label {
    font-size: 0.72rem;
    font-weight: 700;
    text-transform: uppercase;
    color: #94a3b8;
    letter-spacing: 0.05em;
}

.p2-stat-val {
    font-size: 1.25rem;
    font-weight: 800;
    color: #ffffff;
}
.p2-gold-val { color: #fbbf24; }

/* Gold Crest Seal */
.p2-hero-crest {
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    z-index: 2;
}

.p2-crest-seal {
    width: 145px;
    height: 145px;
    border-radius: 50%;
    background: radial-gradient(circle, #1e293b 0%, #0f172a 100%);
    border: 3px double #d97706;
    box-shadow: 0 0 35px rgba(217, 119, 6, 0.35);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 8px;
    box-sizing: border-box;
}

.p2-crest-inner {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
}
.p2-crest-star { font-size: 1.4rem; color: #fbbf24; line-height: 1; margin-bottom: 2px; }
.p2-crest-level { font-family: ui-monospace, SFMono-Regular, monospace; font-size: 0.95rem; font-weight: 900; color: #ffffff; letter-spacing: 0.06em; }
.p2-crest-sub { font-size: 0.65rem; font-weight: 800; color: #fbbf24; letter-spacing: 0.12em; text-transform: uppercase; }
.p2-crest-prest { font-size: 0.6rem; font-weight: 800; color: #f472b6; letter-spacing: 0.08em; margin-top: 2px; }

/* ── Prestige Rebirth Box ── */
.p2-prestige-box {
    margin-top: 1.5rem;
    background: linear-gradient(135deg, rgba(245, 158, 11, 0.12), rgba(217, 119, 6, 0.22));
    border: 2px solid rgba(245, 158, 11, 0.4);
    border-radius: 18px;
    padding: 1.35rem 1.6rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1.25rem;
}

.p2-prestige-left {
    display: flex;
    align-items: center;
    gap: 1rem;
    max-width: 650px;
}

.p2-prestige-icon {
    font-size: 2.2rem;
    line-height: 1;
}

.p2-prestige-title {
    margin: 0 0 0.3rem;
    font-size: 1.2rem;
    font-weight: 800;
    color: #fbbf24;
}

.p2-prestige-desc {
    margin: 0;
    font-size: 0.86rem;
    color: #fde68a;
    line-height: 1.45;
}

.p2-prestige-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 0.75rem 1.4rem;
    background: linear-gradient(135deg, #d97706 0%, #b45309 100%);
    color: #ffffff;
    font-weight: 800;
    font-size: 0.92rem;
    border: none;
    border-radius: 12px;
    cursor: pointer;
    box-shadow: 0 6px 20px rgba(217, 119, 6, 0.4);
    transition: transform 0.15s ease;
}
.p2-prestige-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(217, 119, 6, 0.55);
}

/* ── Credits Strip ── */
.p2-credits-strip {
    margin-top: 1.5rem;
    background: rgba(16, 185, 129, 0.08);
    border: 1.5px solid rgba(16, 185, 129, 0.28);
    border-radius: 16px;
    padding: 1.15rem 1.5rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1rem;
}

.p2-credits-info {
    display: flex;
    align-items: center;
    gap: 0.85rem;
}

.p2-credits-icon {
    font-size: 1.8rem;
}

.p2-credits-title {
    margin: 0;
    font-size: 1.05rem;
    font-weight: 800;
    color: var(--vn-ink, #0f172a);
}

.p2-credits-sub {
    margin: 0.2rem 0 0;
    font-size: 0.85rem;
    color: var(--vn-ink-muted, #64748b);
}

.p2-spend-btn {
    padding: 0.65rem 1.25rem;
    background: #10b981;
    color: #ffffff;
    font-weight: 700;
    font-size: 0.86rem;
    border-radius: 10px;
    text-decoration: none;
    transition: background 0.15s ease;
}
.p2-spend-btn:hover { background: #059669; }

/* ── Badges Section ── */
.p2-badges-section {
    margin-top: 3.5rem;
}

.p2-section-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    flex-wrap: wrap;
    gap: 1rem;
    margin-bottom: 2rem;
}

.p2-category-tag {
    font-size: 0.74rem;
    font-weight: 800;
    text-transform: uppercase;
    color: #6366f1;
    letter-spacing: 0.06em;
}

.p2-section-heading {
    font-family: var(--vn-font-serif, Georgia, serif);
    font-size: 2.1rem;
    color: var(--vn-ink, #0f172a);
    margin: 0.3rem 0 0.4rem;
    line-height: 1.2;
}

.p2-section-subheading {
    font-size: 0.92rem;
    color: var(--vn-ink-muted, #64748b);
    margin: 0;
    max-width: 620px;
}

.p2-counter-pill {
    padding: 0.4rem 0.95rem;
    background: rgba(15, 23, 42, 0.05);
    border: 1px solid rgba(15, 23, 42, 0.12);
    border-radius: 999px;
    font-size: 0.82rem;
    font-weight: 800;
    color: var(--vn-ink, #0f172a);
}

.p2-badges-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(310px, 1fr));
    gap: 1.25rem;
}

.p2-badge-card {
    background: var(--vn-parchment-card, #ffffff);
    border: 1.5px solid var(--vn-border, rgba(0, 0, 0, 0.08));
    border-radius: 18px;
    padding: 1.35rem;
    display: flex;
    gap: 1.15rem;
    align-items: flex-start;
    transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.03);
}

.p2-badge-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.06);
}

.p2-badge-card.is-earned {
    border-color: rgba(245, 158, 11, 0.4);
    background: linear-gradient(180deg, #ffffff 0%, rgba(245, 158, 11, 0.03) 100%);
}

.p2-badge-card.is-locked {
    opacity: 0.65;
    background: rgba(15, 23, 42, 0.02);
}

.p2-badge-icon-wrap {
    position: relative;
    width: 62px;
    height: 62px;
    border-radius: 16px;
    background: rgba(15, 23, 42, 0.04);
    border: 1px solid rgba(15, 23, 42, 0.08);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.p2-badge-card.is-earned .p2-badge-icon-wrap {
    background: rgba(245, 158, 11, 0.1);
    border-color: rgba(245, 158, 11, 0.3);
}

.p2-badge-emoji {
    font-size: 1.9rem;
    line-height: 1;
}

.p2-badge-check {
    position: absolute;
    top: -5px;
    right: -5px;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    background: #10b981;
    color: #ffffff;
    font-size: 0.72rem;
    font-weight: 800;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 2px solid #ffffff;
}

.p2-badge-lock {
    position: absolute;
    bottom: -5px;
    right: -5px;
    font-size: 0.85rem;
}

.p2-badge-content {
    flex: 1;
    min-width: 0;
}

.p2-badge-cat {
    font-size: 0.65rem;
    font-weight: 800;
    text-transform: uppercase;
    color: #64748b;
    letter-spacing: 0.05em;
    margin-bottom: 2px;
}

.p2-badge-name {
    font-family: var(--vn-font-serif, Georgia, serif);
    font-size: 1.15rem;
    color: var(--vn-ink, #0f172a);
    margin: 0 0 0.3rem;
    line-height: 1.25;
}

.p2-badge-desc {
    font-size: 0.82rem;
    color: var(--vn-ink-muted, #64748b);
    line-height: 1.45;
    margin: 0 0 0.65rem;
}

.p2-badge-footer {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.p2-reward-chip {
    font-size: 0.72rem;
    font-weight: 700;
    color: #b45309;
    background: rgba(245, 158, 11, 0.12);
    padding: 0.18rem 0.55rem;
    border-radius: 6px;
}

.p2-xp-chip {
    font-size: 0.72rem;
    font-weight: 800;
    color: #4f46e5;
    background: rgba(99, 102, 241, 0.1);
    padding: 0.18rem 0.55rem;
    border-radius: 6px;
}

/* ── Responsive adjustments ── */
@media (max-width: 768px) {
    .p2-hero-card {
        flex-direction: column;
        align-items: flex-start;
    }
    .p2-hero-crest {
        align-self: center;
    }
    .p2-badges-grid {
        grid-template-columns: 1fr;
    }
}
</style>
@endsection
