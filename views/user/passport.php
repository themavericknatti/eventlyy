<?php

/**
 * User Gamified Event Passport & Badges View
 *
 * @package Evently
 * @subpackage Views/User
 * @version 1.0.0
 */

$siteName = site_setting('site_title', 'Eventlyy');
?>

@extends('layouts/main')

@section('title')
My Event Passport &amp; Badges — <?= e($siteName) ?>
@endsection

@section('content')
<div class="vn-wrapper passport-wrapper">

    <!-- Top Breadcrumb -->
    <div style="margin-bottom: 1.5rem;">
        <a href="{{ route('profile') }}" class="back-link">
            <span>←</span> Back to Account Profile
        </a>
    </div>

    <!-- Passport Heritage Cover Hero -->
    <div class="passport-hero-card">
        <div class="passport-hero-left">
            <span class="vn-stamp-badge">✦ Official Eventlyy Citizen ✦</span>
            <h1 class="passport-hero-title"><?= e($user->name) ?>'s Live Passport</h1>
            <p class="passport-hero-sub">
                Your permanent record of live concerts, summits, and festival memories. Earn digital badges and loyalty Event Credits as you explore the scene.
            </p>

            <div class="passport-badges-summary">
                <div class="p-sum-item">
                    <span>Unlocked Badges</span>
                    <strong><?= count($badges) ?> / 6</strong>
                </div>
                <div class="p-sum-item">
                    <span>Tickets Claimed</span>
                    <strong><?= number_format($ticketCount) ?></strong>
                </div>
                <div class="p-sum-item highlight">
                    <span>Event Loyalty Credits</span>
                    <strong style="color:var(--vn-brass);"><?= format_price($totalCredits) ?></strong>
                </div>
            </div>
        </div>

        <div class="passport-emblem-box">
            <div class="passport-gold-seal">
                <span class="seal-fleuron">✦</span>
                <span class="seal-level">
                    <?php if ($ticketCount >= 10): ?>
                        LEGEND
                    <?php elseif ($ticketCount >= 5): ?>
                        VIP GOLD
                    <?php elseif ($ticketCount >= 1): ?>
                        EXPLORER
                    <?php else: ?>
                        CITIZEN
                    <?php endif; ?>
                </span>
                <span class="seal-sub">PASSPORT</span>
            </div>
        </div>
    </div>

    <!-- Active Event Credits Bar (if any) -->
    <?php if ($totalCredits > 0): ?>
        <div class="credits-alert-box">
            <div style="display:flex; align-items:center; gap:0.75rem;">
                <span style="font-size:1.75rem;">💳</span>
                <div>
                    <h4 style="margin:0; font-size:1.05rem;">You have <?= format_price($totalCredits) ?> in Active Event Credits!</h4>
                    <p style="margin:0.2rem 0 0; font-size:0.86rem; color:var(--vn-ink-muted);">
                        Earned from badges and referrals. These credits automatically deduct from your ticket total at checkout.
                    </p>
                </div>
            </div>
            <a href="{{ route('/') }}#events-section" class="vn-btn-primary" style="padding:0.6rem 1.25rem; font-size:0.86rem;">
                Browse Drops &amp; Spend →
            </a>
        </div>
    <?php endif; ?>

    <!-- Badges Showcase Grid -->
    <section style="margin: 3rem 0;">
        <div class="vn-section-head">
            <div class="vn-heading-wrap">
                <span class="vn-category-label">Cultural Milestones</span>
                <h2 class="vn-section-title">Digital Passport Stamps &amp; Badges</h2>
            </div>
            <div class="vn-section-sub">Complete challenges across events to unlock credits and bragging rights</div>
        </div>

        <?php
            $earnedKeys = [];
            foreach ($badges as $b) {
                if (!empty($b['badge_key'])) {
                    $earnedKeys[] = $b['badge_key'];
                }
            }
            $allBadgeDefs = [
                'first_drop' => [
                    'title' => 'First Drop Pioneer',
                    'icon' => '🎟️',
                    'desc' => 'Secured your first live ticket on Eventlyy.',
                    'reward' => '₦500 Credit'
                ],
                'nightlife_explorer' => [
                    'title' => 'Nightlife Explorer',
                    'icon' => '🌙',
                    'desc' => 'Attended 3 or more live events.',
                    'reward' => '₦1,000 Credit'
                ],
                'first_vote' => [
                    'title' => 'Culture Voter',
                    'icon' => '🗳️',
                    'desc' => 'Voted in an event contest or award poll.',
                    'reward' => '₦200 Credit'
                ],
                'trivia_master' => [
                    'title' => 'Trivia Mastermind',
                    'icon' => '🧠',
                    'desc' => 'Scored a winning round on an event trivia game.',
                    'reward' => '₦300 Credit'
                ],
                'song_suggester' => [
                    'title' => 'Setlist Curator',
                    'icon' => '🎵',
                    'desc' => 'Suggested a top song or panel question.',
                    'reward' => '₦150 Credit'
                ],
                'community_voice' => [
                    'title' => 'Hype Starter',
                    'icon' => '📣',
                    'desc' => 'Posted on an event Hype Wall or Squad Finder.',
                    'reward' => '₦200 Credit'
                ]
            ];
        ?>

        <div class="passport-badges-grid">
            <?php foreach ($allBadgeDefs as $key => $b): ?>
                <?php 
                    $isEarned = in_array($key, $earnedKeys);
                ?>
                <div class="passport-badge-card <?= $isEarned ? 'earned' : 'locked' ?>">
                    <div class="badge-icon-box">
                        <span class="badge-icon"><?= $b['icon'] ?></span>
                        <?php if ($isEarned): ?>
                            <span class="badge-status-dot" title="Unlocked">✓</span>
                        <?php else: ?>
                            <span class="badge-lock-icon" title="Locked">🔒</span>
                        <?php endif; ?>
                    </div>
                    <div class="badge-details">
                        <h4 class="badge-title"><?= e($b['title']) ?></h4>
                        <p class="badge-desc"><?= e($b['desc']) ?></p>
                        <div class="badge-reward-pill">
                            <?= $isEarned ? 'Unlocked: ' : 'Reward: ' ?><strong><?= $b['reward'] ?></strong>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

</div>

<style>
.passport-wrapper {
    max-width: 1040px;
    margin: 0 auto;
    padding: 1.5rem 1rem 4rem;
}

.passport-hero-card {
    background: linear-gradient(135deg, var(--vn-parchment-warm) 0%, var(--vn-parchment-card) 60%, var(--vn-parchment-warm) 100%);
    border: 2px solid var(--vn-border-dark);
    border-radius: 20px;
    padding: clamp(2rem, 4vw, 3.25rem);
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 2rem;
    box-shadow: var(--vn-shadow-elevated);
    position: relative;
    overflow: hidden;
}

.passport-hero-left {
    max-width: 620px;
}

.passport-hero-title {
    font-family: var(--vn-font-serif);
    font-size: clamp(2rem, 4.5vw, 3rem);
    color: var(--vn-ink);
    margin: 0.5rem 0 0.75rem;
    line-height: 1.1;
}

.passport-hero-sub {
    font-size: 0.95rem;
    color: var(--vn-ink-muted);
    line-height: 1.6;
    margin-bottom: 1.75rem;
}

.passport-badges-summary {
    display: flex;
    gap: 1.25rem;
    flex-wrap: wrap;
}

.p-sum-item {
    background: var(--vn-parchment-card);
    border: 1px solid var(--vn-border);
    border-radius: 12px;
    padding: 0.75rem 1.15rem;
    display: flex;
    flex-direction: column;
    gap: 0.2rem;
}
.p-sum-item span { font-size: 0.75rem; color: var(--vn-ink-muted); text-transform: uppercase; font-weight: 700; }
.p-sum-item strong { font-size: 1.25rem; color: var(--vn-ink); }

.passport-emblem-box {
    display: flex;
    align-items: center;
    justify-content: center;
}

.passport-gold-seal {
    width: 140px;
    height: 140px;
    border-radius: 50%;
    background: radial-gradient(circle, var(--vn-parchment-warm) 0%, var(--vn-parchment-card) 100%);
    border: 3px double var(--vn-brass);
    box-shadow: 0 8px 24px var(--vn-brass-glow);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
}
.seal-fleuron { font-size: 1.2rem; color: var(--vn-brass); }
.seal-level { font-family: var(--vn-font-mono); font-size: 0.9rem; font-weight: 800; color: var(--vn-brass); letter-spacing: 1px; }
.seal-sub { font-size: 0.68rem; color: var(--vn-ink-muted); letter-spacing: 2px; }

.credits-alert-box {
    margin-top: 1.5rem;
    background: rgba(245, 158, 11, 0.08);
    border: 1.5px solid rgba(245, 158, 11, 0.3);
    border-radius: 16px;
    padding: 1.25rem 1.5rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1rem;
}

.passport-badges-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.25rem;
    margin-top: 1.5rem;
}

.passport-badge-card {
    background: var(--vn-parchment-card);
    border: 1.5px solid var(--vn-border);
    border-radius: 16px;
    padding: 1.5rem;
    display: flex;
    gap: 1.25rem;
    align-items: center;
    transition: all 0.25s ease;
    box-shadow: var(--vn-shadow-card);
}

.passport-badge-card.earned {
    border-color: var(--vn-brass);
}

.passport-badge-card.locked {
    opacity: 0.65;
    background: rgba(var(--muted), 0.03);
}

.badge-icon-box {
    width: 60px;
    height: 60px;
    border-radius: 16px;
    background: var(--vn-parchment-warm);
    border: 1.5px solid var(--vn-border);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.85rem;
    position: relative;
    flex-shrink: 0;
}

.badge-status-dot {
    position: absolute;
    top: -4px;
    right: -4px;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    background: #10b981;
    color: #fff;
    font-size: 0.72rem;
    font-weight: 800;
    display: flex;
    align-items: center;
    justify-content: center;
}

.badge-lock-icon {
    position: absolute;
    bottom: -4px;
    right: -4px;
    font-size: 0.85rem;
}

.badge-title {
    font-family: var(--vn-font-serif);
    font-size: 1.15rem;
    color: var(--vn-ink);
    margin: 0 0 0.35rem;
}

.badge-desc {
    font-size: 0.84rem;
    color: var(--vn-ink-muted);
    line-height: 1.45;
    margin: 0 0 0.5rem;
}

.badge-reward-pill {
    font-size: 0.75rem;
    color: var(--vn-brass);
    background: rgba(180, 83, 9, 0.08);
    padding: 0.15rem 0.55rem;
    border-radius: 4px;
    display: inline-block;
    font-weight: 600;
}
</style>
@endsection
