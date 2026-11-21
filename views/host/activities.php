<?php

/**
 * Host Event Activities & Engagement Studio
 *
 * Comprehensive command center for voting contests, trivia games,
 * crowd song requests, speaker Q&A, and community hype wall moderation.
 *
 * @package Eventlyy
 * @subpackage Views/Host
 * @version 2.0.0
 */

$siteName = site_setting('site_title', 'Eventlyy');
$eventSlug = !empty($event->custom_url) ? $event->custom_url : ($event->slug ?? $event->id);
$publicHubUrl = url('event/' . $eventSlug . '/activities');
?>

@extends('layouts/host')

@section('title')
Activities &amp; Engagement Studio — <?= e($event->name) ?>
@endsection

@section('content')
<div class="act-studio-wrapper">

    <!-- ═══════════════════════════════════════════════════════════════════
         1. TOP CONTEXT BAR & ACTIONS
         ═══════════════════════════════════════════════════════════════════ -->
    <div class="act-top-bar">
        <a href="{{ route('host/event/management/' . $event->id) }}" class="act-back-link">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="19" y1="12" x2="5" y2="12"></line>
                <polyline points="12 19 5 12 12 5"></polyline>
            </svg>
            <span>Back to Event Management</span>
        </a>
        <div class="act-top-actions">
            <a href="<?= e($publicHubUrl) ?>" target="_blank" rel="noopener" class="act-btn act-btn--outline" title="Open Public Activity Hub in new tab">
                <span>👁️</span> View Public Arena ↗
            </a>
            <button type="button" id="open-poll-modal-btn" class="act-btn act-btn--primary">
                <span>+</span> Create Poll / Contest
            </button>
            <button type="button" id="open-trivia-modal-btn" class="act-btn act-btn--purple">
                <span>+</span> Launch Trivia Game
            </button>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════════════════
         2. HERO EVENT IDENTITY BANNER
         ═══════════════════════════════════════════════════════════════════ -->
    <section class="act-hero-card">
        <div class="act-hero-content">
            <div class="act-badge-row">
                <span class="act-status-pill">
                    <span class="act-pulse-ring"></span>
                    <span>🎉 Interactive Engagement Studio</span>
                </span>
                <span class="act-badge-sub">Polls, Trivia Games, DJ Setlists &amp; Community Wall</span>
            </div>
            <h1 class="act-event-title"><?= e($event->name) ?></h1>
            <div class="act-event-meta">
                <span>📅 <?= date('l, M j, Y • g:i A', strtotime($event->date)) ?></span>
                <span class="act-meta-sep">•</span>
                <span>📍 <?= e($event->location) ?></span>
            </div>
        </div>
    </section>

    <!-- ═══════════════════════════════════════════════════════════════════
         3. KPI METRIC SUMMARY GRID
         ═══════════════════════════════════════════════════════════════════ -->
    <section class="act-summary-grid">
        <!-- Active Polls -->
        <article class="act-summary-card">
            <div class="act-card-icon-wrap" style="background:rgba(99,102,241,0.12); color:#6366f1;">🗳️</div>
            <div>
                <span class="act-card-label">Active Polls</span>
                <h3 class="act-card-value"><?= count($polls) ?></h3>
                <p class="act-card-meta">Live voting contests</p>
            </div>
        </article>

        <!-- Total Votes & Power Vote Revenue -->
        <article class="act-summary-card">
            <div class="act-card-icon-wrap" style="background:rgba(16,185,129,0.12); color:#10b981;">⚡</div>
            <div>
                <span class="act-card-label">Total Votes Cast</span>
                <?php 
                    $totalPollVotes = 0;
                    $totalPollRevenue = 0.00;
                    foreach ($polls as $p) {
                        $totalPollVotes += (int)($p['total_votes'] ?? 0);
                        $totalPollRevenue += (float)($p['total_revenue'] ?? 0.00);
                    }
                ?>
                <h3 class="act-card-value" style="color:#10b981;"><?= number_format($totalPollVotes) ?></h3>
                <p class="act-card-meta">
                    <?php if ($totalPollRevenue > 0): 
                        $activityRate = (float)site_setting('activity_fee_percent', 10.0);
                        $hostNetShare = $totalPollRevenue * (1 - ($activityRate / 100));
                    ?>
                        ₦<?= number_format($hostNetShare, 2) ?> Host Net (₦<?= number_format($totalPollRevenue, 2) ?> raised)
                    <?php else: ?>
                        Fan &amp; attendee votes
                    <?php endif; ?>
                </p>
            </div>
        </article>

        <!-- Trivia Plays & Wins -->
        <article class="act-summary-card">
            <div class="act-card-icon-wrap" style="background:rgba(168,85,247,0.12); color:#a855f7;">🧠</div>
            <div>
                <span class="act-card-label">Trivia Plays &amp; Wins</span>
                <h3 class="act-card-value" style="color:#a855f7;"><?= $trivia ? number_format($trivia['plays_count']) : '0' ?></h3>
                <p class="act-card-meta"><?= $trivia ? number_format($trivia['wins_count']) . ' discount codes won' : 'No active game' ?></p>
            </div>
        </article>

        <!-- Crowd Requests & Posts -->
        <article class="act-summary-card">
            <div class="act-card-icon-wrap" style="background:rgba(245,158,11,0.12); color:#f59e0b;">🎵</div>
            <div>
                <span class="act-card-label">Crowd Requests &amp; Posts</span>
                <h3 class="act-card-value" style="color:#f59e0b;"><?= count($songRequests) + count($qaRequests) + count($communityPosts) ?></h3>
                <p class="act-card-meta"><?= count($songRequests) ?> tracks, <?= count($communityPosts) ?> wall posts</p>
            </div>
        </article>
    </section>

    <!-- ═══════════════════════════════════════════════════════════════════
         4. MAIN CONTENT MODULES
         ═══════════════════════════════════════════════════════════════════ -->
    <div class="act-modules-stack">

        <!-- ── MODULE 1: VOTING CONTESTS & POLLS ── -->
        <section class="act-section-card">
            <div class="act-section-header">
                <div class="act-section-title-wrap">
                    <span class="act-section-icon">🗳️</span>
                    <div>
                        <h2 class="act-section-title">Voting Contests &amp; Polls</h2>
                        <p class="act-section-desc">Create awards, best-dressed contests, or choice polls to drive viral traffic and fan engagement.</p>
                    </div>
                </div>
                <button type="button" class="act-btn act-btn--sm act-btn--primary open-poll-trigger">
                    <span>+</span> New Poll
                </button>
            </div>

            <?php if (empty($polls)): ?>
                <div class="act-empty-box">
                    <span class="act-empty-icon">🗳️</span>
                    <h3>No Polls Created Yet</h3>
                    <p>Launch your first voting contest! Nominees will share their links to bring thousands of votes to your event.</p>
                    <button type="button" class="act-btn act-btn--primary open-poll-trigger">
                        + Create Your First Poll
                    </button>
                </div>
            <?php else: ?>
                <div class="act-polls-grid">
                    <?php foreach ($polls as $poll): 
                        $pType = $poll['poll_type'] ?? 'power';
                        $vCost = (float)($poll['vote_cost'] ?? 100);
                        $pRev = (float)($poll['total_revenue'] ?? 0);
                    ?>
                        <div class="act-poll-card">
                            <div class="act-poll-card-header">
                                <div>
                                    <div style="display:flex; align-items:center; gap:0.4rem; flex-wrap:wrap; margin-bottom:0.35rem;">
                                        <span class="act-pill <?= $poll['status'] === 'active' ? 'act-pill--green' : 'act-pill--muted' ?>">
                                            <?= $poll['status'] === 'active' ? '● Live Voting' : '⏸ Closed' ?>
                                        </span>
                                        <?php if ($pType === 'power'): ?>
                                            <span class="act-pill" style="background:#eef2ff; color:#6366f1; font-weight:700;">
                                                ⚡ Power Vote (₦<?= number_format($vCost) ?>/vote)
                                            </span>
                                        <?php elseif ($pType === 'paid'): ?>
                                            <span class="act-pill" style="background:#fef3c7; color:#d97706; font-weight:700;">
                                                🏆 Paid Contest (₦<?= number_format($vCost) ?>/vote)
                                            </span>
                                        <?php else: ?>
                                            <span class="act-pill" style="background:#f1f5f9; color:#64748b;">
                                                ✦ Free Only
                                            </span>
                                        <?php endif; ?>
                                        <?php if ($pRev > 0): ?>
                                            <span class="act-pill" style="background:#ecfdf5; color:#059669; font-weight:800;">
                                                💰 ₦<?= number_format($pRev, 2) ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    <h4 class="act-poll-card-title"><?= e($poll['title']) ?></h4>
                                    <?php if (!empty($poll['description'])): ?>
                                        <p class="act-poll-card-desc"><?= e($poll['description']) ?></p>
                                    <?php endif; ?>
                                </div>
                                <form method="POST" action="/host/event/<?= $event->id ?>/activities/poll/<?= $poll['id'] ?>/toggle" style="margin:0;">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="act-btn act-btn--xs <?= $poll['status'] === 'active' ? 'act-btn--outline' : 'act-btn--primary' ?>" title="<?= $poll['status'] === 'active' ? 'Pause / Close Poll' : 'Reopen Poll' ?>">
                                        <?= $poll['status'] === 'active' ? '⏸ Close' : '▶ Open' ?>
                                    </button>
                                </form>
                            </div>

                            <div class="act-poll-options-list">
                                <?php foreach ($poll['options'] as $opt): 
                                    $pct = (float)($opt['percentage'] ?? 0);
                                ?>
                                    <div class="act-option-bar">
                                        <div class="act-option-bar-fill" style="width: <?= $pct ?>%;"></div>
                                        <div class="act-option-bar-text">
                                            <span class="act-option-name"><?= e($opt['title']) ?></span>
                                            <span class="act-option-stats"><?= number_format($opt['votes_count']) ?> votes <strong>(<?= $opt['percentage'] ?>%)</strong></span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <div class="act-poll-card-footer">
                                <div>
                                    <span>Total Votes: <strong><?= number_format($poll['total_votes']) ?></strong></span>
                                    <?php if ($pRev > 0): 
                                        $actRate = (float)site_setting('activity_fee_percent', 10.0);
                                        $netShare = $pRev * (1 - ($actRate / 100));
                                    ?>
                                        <span style="margin-left:0.6rem; color:#059669; font-weight:700;">• ₦<?= number_format($netShare, 2) ?> Net (₦<?= number_format($pRev, 2) ?> Raised)</span>
                                    <?php endif; ?>
                                </div>
                                <a href="<?= e($publicHubUrl) ?>?tab=polls" target="_blank" class="act-link-external">
                                    Vote Page ↗
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

        <!-- ── MODULE 2: TRIVIA CHALLENGE & PROMO REWARD DESK ── -->
        <section class="act-section-card">
            <div class="act-section-header">
                <div class="act-section-title-wrap">
                    <span class="act-section-icon">🧠</span>
                    <div>
                        <h2 class="act-section-title">Event Trivia &amp; Reward Desk</h2>
                        <p class="act-section-desc">Test attendee knowledge and automatically reward qualifying players with exclusive ticket promo codes.</p>
                    </div>
                </div>
                <?php if (!$trivia): ?>
                    <button type="button" class="act-btn act-btn--sm act-btn--purple open-trivia-trigger">
                        <span>+</span> Build Trivia
                    </button>
                <?php endif; ?>
            </div>

            <?php if (!$trivia): ?>
                <div class="act-empty-box">
                    <span class="act-empty-icon">🧠</span>
                    <h3>No Trivia Game Launched</h3>
                    <p>Build a fun quiz about your event, artists, or genre. Winners unlock exclusive promo codes that drive ticket sales!</p>
                    <button type="button" class="act-btn act-btn--purple open-trivia-trigger">
                        + Build Event Trivia
                    </button>
                </div>
            <?php else: ?>
                <div class="act-trivia-dashboard">
                    <div class="act-trivia-header">
                        <div>
                            <span class="act-pill act-pill--purple">● Active Trivia Game</span>
                            <h3 class="act-trivia-title"><?= e($trivia['title']) ?></h3>
                            <?php if (!empty($trivia['description'])): ?>
                                <p class="act-trivia-desc"><?= e($trivia['description']) ?></p>
                            <?php endif; ?>
                        </div>
                        <div class="act-trivia-metrics">
                            <div class="act-metric-box">
                                <strong class="act-metric-val" style="color:#6366f1;"><?= number_format($trivia['plays_count']) ?></strong>
                                <span class="act-metric-lbl">Total Attempts</span>
                            </div>
                            <div class="act-metric-box">
                                <strong class="act-metric-val" style="color:#10b981;"><?= number_format($trivia['wins_count']) ?></strong>
                                <span class="act-metric-lbl">Codes Won</span>
                            </div>
                        </div>
                    </div>

                    <div class="act-trivia-config-row">
                        <div class="act-config-item">
                            <span class="act-config-label">Passing Standard</span>
                            <strong class="act-config-val">🎯 <?= (int)$trivia['pass_score'] ?> Correct Answers</strong>
                        </div>
                        <div class="act-config-item">
                            <span class="act-config-label">Reward Promo Code</span>
                            <strong class="act-config-val act-code-chip"><?= e($trivia['reward_promo_code'] ?? 'None Configured') ?></strong>
                        </div>
                        <div class="act-config-item">
                            <span class="act-config-label">Question Bank</span>
                            <strong class="act-config-val">📝 <?= count($trivia['questions'] ?? []) ?> Questions Active</strong>
                        </div>
                        <div class="act-config-item" style="margin-left:auto;">
                            <a href="<?= e($publicHubUrl) ?>?tab=trivia" target="_blank" class="act-btn act-btn--xs act-btn--outline">
                                Test Play in Arena ↗
                            </a>
                        </div>
                    </div>

                    <?php if (!empty($trivia['questions'])): ?>
                        <div class="act-questions-accordion">
                            <h4 style="font-size:0.92rem; font-weight:700; margin-bottom:0.75rem; color:rgb(var(--text));">Question Roster:</h4>
                            <div class="act-questions-list">
                                <?php foreach ($trivia['questions'] as $qIdx => $q): ?>
                                    <div class="act-question-item">
                                        <span class="act-q-num">Q<?= $qIdx + 1 ?></span>
                                        <div class="act-q-content">
                                            <strong><?= e($q['question']) ?></strong>
                                            <div class="act-q-options">
                                                <span class="act-opt-pill <?= ($q['correct_option'] ?? '') === 'A' ? 'is-correct' : '' ?>">A: <?= e($q['option_a']) ?></span>
                                                <span class="act-opt-pill <?= ($q['correct_option'] ?? '') === 'B' ? 'is-correct' : '' ?>">B: <?= e($q['option_b']) ?></span>
                                                <?php if (!empty($q['option_c'])): ?>
                                                    <span class="act-opt-pill <?= ($q['correct_option'] ?? '') === 'C' ? 'is-correct' : '' ?>">C: <?= e($q['option_c']) ?></span>
                                                <?php endif; ?>
                                                <?php if (!empty($q['option_d'])): ?>
                                                    <span class="act-opt-pill <?= ($q['correct_option'] ?? '') === 'D' ? 'is-correct' : '' ?>">D: <?= e($q['option_d']) ?></span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </section>

        <!-- ── MODULE 3: DJ SETLIST REQUESTS & SPEAKER Q&A ── -->
        <section class="act-section-card">
            <div class="act-section-header">
                <div class="act-section-title-wrap">
                    <span class="act-section-icon">🎵</span>
                    <div>
                        <h2 class="act-section-title">DJ Song Requests &amp; Stage Q&amp;A</h2>
                        <p class="act-section-desc">Real-time suggestions and questions submitted by attendees, ranked dynamically by crowd upvotes.</p>
                    </div>
                </div>
            </div>

            <?php 
                $sList = is_array($songRequests) ? $songRequests : (method_exists($songRequests, 'toArray') ? $songRequests->toArray() : (array)$songRequests);
                $qList = is_array($qaRequests) ? $qaRequests : (method_exists($qaRequests, 'toArray') ? $qaRequests->toArray() : (array)$qaRequests);
                $allReqs = array_merge($sList, $qList);
                usort($allReqs, fn($a, $b) => ($b['upvotes_count'] ?? 0) <=> ($a['upvotes_count'] ?? 0));
            ?>
            <?php if (empty($allReqs)): ?>
                <div class="act-empty-box">
                    <span class="act-empty-icon">🎵</span>
                    <h3>No Suggestions Yet</h3>
                    <p>When attendees suggest songs or submit questions in the Activity Hub, they'll appear here ranked in real-time by crowd upvotes.</p>
                </div>
            <?php else: ?>
                <div class="act-requests-list">
                    <?php foreach ($allReqs as $r): ?>
                        <div class="act-req-card <?= ($r['status'] ?? '') === 'played' ? 'is-played' : '' ?>">
                            <div class="act-req-votes">
                                <span class="act-vote-arrow">▲</span>
                                <strong class="act-vote-num"><?= (int)($r['upvotes_count'] ?? 0) ?></strong>
                                <span class="act-vote-lbl">votes</span>
                            </div>
                            <div class="act-req-details">
                                <div class="act-req-meta-top">
                                    <span class="act-pill <?= $r['type'] === 'song' ? 'act-pill--blue' : 'act-pill--amber' ?>">
                                        <?= $r['type'] === 'song' ? '🎵 Song Request' : '🎤 Stage Q&A' ?>
                                    </span>
                                    <?php if (!empty($r['requester_name'])): ?>
                                        <span class="act-req-author">Suggested by <strong><?= e($r['requester_name']) ?></strong></span>
                                    <?php endif; ?>
                                </div>
                                <h4 class="act-req-title"><?= e($r['title']) ?></h4>
                                <?php if (!empty($r['artist_or_speaker'])): ?>
                                    <p class="act-req-sub">Artist / Speaker: <strong><?= e($r['artist_or_speaker']) ?></strong></p>
                                <?php endif; ?>
                            </div>
                            <div class="act-req-actions">
                                <form method="POST" action="/host/event/<?= $event->id ?>/activities/request/<?= $r['id'] ?>/moderate" style="margin:0;">
                                    <?= csrf_field() ?>
                                    <?php if (($r['status'] ?? '') !== 'played'): ?>
                                        <input type="hidden" name="status" value="played">
                                        <button type="submit" class="act-btn act-btn--xs act-btn--green" title="Mark as played by DJ or answered by speaker">
                                            ✓ Mark Done
                                        </button>
                                    <?php else: ?>
                                        <span class="act-pill act-pill--green" style="font-weight:700;">✓ Completed</span>
                                    <?php endif; ?>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

        <!-- ── MODULE 4: HYPE WALL & SQUAD FINDER MODERATION ── -->
        <section class="act-section-card">
            <div class="act-section-header">
                <div class="act-section-title-wrap">
                    <span class="act-section-icon">📣</span>
                    <div>
                        <h2 class="act-section-title">Community Hype Wall Moderation</h2>
                        <p class="act-section-desc">Oversee attendee messages, festival squad announcements, and ride-shares.</p>
                    </div>
                </div>
            </div>

            <?php if (empty($communityPosts)): ?>
                <div class="act-empty-box">
                    <span class="act-empty-icon">📣</span>
                    <h3>No Wall Posts Yet</h3>
                    <p>Attendee shoutouts, festival squad requests, and carpool notices will display here with moderation controls.</p>
                </div>
            <?php else: ?>
                <div class="act-posts-list">
                    <?php foreach ($communityPosts as $cp): ?>
                        <div class="act-post-card">
                            <div class="act-post-avatar">
                                <?= strtoupper(substr($cp['author_name'] ?? 'A', 0, 1)) ?>
                            </div>
                            <div class="act-post-body">
                                <div class="act-post-header">
                                    <div class="act-post-author-row">
                                        <strong class="act-post-author"><?= e($cp['author_name']) ?></strong>
                                        <?php if (!empty($cp['is_ticket_holder'])): ?>
                                            <span class="act-verified-pill" title="Verified Ticket Holder">✓ Ticket Holder</span>
                                        <?php endif; ?>
                                        <span class="act-pill act-pill--muted"><?= ucfirst(e($cp['category'] ?? 'general')) ?></span>
                                    </div>
                                    <span class="act-post-time"><?= date('M j, g:i A', strtotime($cp['created_at'])) ?></span>
                                </div>
                                <p class="act-post-msg"><?= nl2br(e($cp['message'])) ?></p>
                                <div class="act-post-footer">
                                    <?php if (!empty($cp['location_area'])): ?>
                                        <span class="act-post-detail">📍 <?= e($cp['location_area']) ?></span>
                                    <?php endif; ?>
                                    <?php if (!empty($cp['contact_handle'])): ?>
                                        <span class="act-post-detail">💬 <?= e($cp['contact_handle']) ?></span>
                                    <?php endif; ?>
                                    <span class="act-post-likes">❤️ <?= (int)($cp['likes_count'] ?? 0) ?> Likes</span>
                                </div>
                            </div>
                            <div class="act-post-actions">
                                <form method="POST" action="/host/event/<?= $event->id ?>/activities/community/<?= $cp['id'] ?>/delete" style="margin:0;" onsubmit="return confirm('Permanently delete this community post from the wall?');">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="act-btn-delete" title="Delete post from Hype Wall">
                                        🗑️
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

    </div>
</div>

<!-- ═══════════════════════════════════════════════════════════════════
     MODAL 1: CREATE POLL / CONTEST
     ═══════════════════════════════════════════════════════════════════ -->
<div id="create-poll-modal" class="act-modal-backdrop" role="dialog" aria-modal="true" style="display:none;">
    <div class="act-modal-card">
        <div class="act-modal-header">
            <div>
                <h3 class="act-modal-title">Create Voting Contest or Poll</h3>
                <p class="act-modal-subtitle">Give attendees a say, crown a winner, or run a fan award</p>
            </div>
            <button type="button" class="act-modal-close close-poll-btn" aria-label="Close modal">&times;</button>
        </div>

        <form method="POST" action="/host/event/<?= $event->id ?>/activities/poll/create">
            <?= csrf_field() ?>

            <div class="act-form-group">
                <label class="act-label">Poll / Award Title <span class="act-req">*</span></label>
                <input type="text" name="title" required placeholder="e.g. Best Dressed of the Night, DJ of the Year" class="act-input" />
            </div>

            <div class="act-form-group">
                <label class="act-label">Description / Instructions</label>
                <textarea name="description" rows="2" placeholder="Vote for your favorite nominee once every 24 hours!" class="act-input"></textarea>
            </div>

            <div class="act-form-group">
                <label class="act-label">Nominees / Options (At least 2 required) <span class="act-req">*</span></label>
                <div class="act-options-builder">
                    <input type="text" name="options[]" required placeholder="Option / Nominee 1" class="act-input" />
                    <input type="text" name="options[]" required placeholder="Option / Nominee 2" class="act-input" />
                    <input type="text" name="options[]" placeholder="Option / Nominee 3 (Optional)" class="act-input" />
                    <input type="text" name="options[]" placeholder="Option / Nominee 4 (Optional)" class="act-input" />
                </div>
            </div>

            <!-- ⚡ Power Vote Configuration -->
            <div class="act-form-group" style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:12px; padding:1.25rem;">
                <label class="act-label" style="color:#6366f1; font-weight:700; margin-bottom:0.75rem;">⚡ Voting Mode & Power Votes</label>
                <div class="act-form-row" style="gap:1rem;">
                    <div style="flex:1;">
                        <label class="act-label" style="font-size:0.8rem;">Voting Mode</label>
                        <select name="poll_type" class="act-input" id="poll-type-select">
                            <option value="power" selected>⚡ Power Voting (Free + Paid Packs)</option>
                            <option value="free">✦ Free Only (1 Vote / 24h)</option>
                            <option value="paid">🏆 Paid Contest Only (All Votes Paid)</option>
                        </select>
                    </div>
                    <div style="flex:0.5;" id="vote-cost-group">
                        <label class="act-label" style="font-size:0.8rem;">Price Per Vote (₦)</label>
                        <input type="number" name="vote_cost" value="100" min="50" step="50" class="act-input" id="vote-cost-input" />
                    </div>
                </div>
                <p style="font-size:0.75rem; color:#94a3b8; margin-top:0.6rem;">
                    <strong>Power Voting:</strong> Verified attendees get 1 free daily vote + all fans can purchase extra vote packs (5, 20, 50, 100+) via Paystack to boost their nominee. The host receives 90% net payout credited to your balance (10% platform fee).<br>
                    <strong>Free Only:</strong> Standard poll with 1 free vote per 24 hours for verified attendees.<br>
                    <strong>Paid Contest:</strong> Every vote is paid — ideal for awards, pageants, or talent shows.<br>
                </p>
            </div>

            <div class="act-modal-actions">
                <button type="submit" class="act-btn act-btn--primary" style="flex:1;">
                    Publish Poll 🚀
                </button>
                <button type="button" class="act-btn act-btn--outline close-poll-btn">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════════════════
     MODAL 2: CREATE TRIVIA GAME
     ═══════════════════════════════════════════════════════════════════ -->
<div id="create-trivia-modal" class="act-modal-backdrop" role="dialog" aria-modal="true" style="display:none;">
    <div class="act-modal-card act-modal-card--lg">
        <div class="act-modal-header">
            <div>
                <h3 class="act-modal-title">Create Trivia Challenge</h3>
                <p class="act-modal-subtitle">Reward quiz winners with exclusive ticket promo discounts</p>
            </div>
            <button type="button" class="act-modal-close close-trivia-btn" aria-label="Close modal">&times;</button>
        </div>

        <form method="POST" action="/host/event/<?= $event->id ?>/activities/trivia/create">
            <?= csrf_field() ?>

            <div class="act-form-group">
                <label class="act-label">Trivia Challenge Title <span class="act-req">*</span></label>
                <input type="text" name="title" required placeholder="e.g. Afrobeats History Quiz, VIP Guest Trivia" class="act-input" />
            </div>

            <div class="act-form-row">
                <div class="act-form-group" style="flex:1;">
                    <label class="act-label">Passing Score (Correct answers needed) <span class="act-req">*</span></label>
                    <input type="number" name="pass_score" value="2" min="1" max="10" required class="act-input" />
                </div>
                <div class="act-form-group" style="flex:1;">
                    <label class="act-label">Reward Promo Code</label>
                    <input type="text" name="reward_promo_code" placeholder="e.g. TRIVIA15" class="act-input" />
                </div>
            </div>

            <div class="act-form-group act-q-builder-box">
                <label class="act-label" style="font-weight:700; color:#6366f1;">Question 1 <span class="act-req">*</span></label>
                <input type="text" name="questions[0][question]" required placeholder="e.g. What year did Burna Boy win his first Grammy?" class="act-input" />
                <div class="act-q-grid-options">
                    <input type="text" name="questions[0][option_a]" required placeholder="Option A: 2021" class="act-input" />
                    <input type="text" name="questions[0][option_b]" required placeholder="Option B: 2019" class="act-input" />
                    <input type="text" name="questions[0][option_c]" placeholder="Option C: 2022" class="act-input" />
                    <input type="text" name="questions[0][option_d]" placeholder="Option D: 2020" class="act-input" />
                </div>
                <div style="margin-top:0.6rem; display:flex; align-items:center; gap:0.6rem;">
                    <label class="act-label" style="margin:0; font-size:0.82rem;">Correct Answer:</label>
                    <select name="questions[0][correct_option]" class="act-input" style="width:110px; height:38px; display:inline-block;">
                        <option value="A">Option A</option>
                        <option value="B">Option B</option>
                        <option value="C">Option C</option>
                        <option value="D">Option D</option>
                    </select>
                </div>
            </div>

            <div class="act-form-group act-q-builder-box">
                <label class="act-label" style="font-weight:700; color:#6366f1;">Question 2 <span class="act-req">*</span></label>
                <input type="text" name="questions[1][question]" required placeholder="e.g. What is the dress code for this gathering?" class="act-input" />
                <div class="act-q-grid-options">
                    <input type="text" name="questions[1][option_a]" required placeholder="Option A: All White" class="act-input" />
                    <input type="text" name="questions[1][option_b]" required placeholder="Option B: Beach Casual" class="act-input" />
                    <input type="text" name="questions[1][option_c]" placeholder="Option C: Streetwear" class="act-input" />
                    <input type="text" name="questions[1][option_d]" placeholder="Option D: Formal" class="act-input" />
                </div>
                <div style="margin-top:0.6rem; display:flex; align-items:center; gap:0.6rem;">
                    <label class="act-label" style="margin:0; font-size:0.82rem;">Correct Answer:</label>
                    <select name="questions[1][correct_option]" class="act-input" style="width:110px; height:38px; display:inline-block;">
                        <option value="A">Option A</option>
                        <option value="B">Option B</option>
                        <option value="C">Option C</option>
                        <option value="D">Option D</option>
                    </select>
                </div>
            </div>

            <div class="act-modal-actions">
                <button type="submit" class="act-btn act-btn--purple" style="flex:1;">
                    Launch Trivia Challenge 🧠
                </button>
                <button type="button" class="act-btn act-btn--outline close-trivia-btn">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════════════════
     COMPREHENSIVE DESIGN SYSTEM & RESPONSIVE STYLES
     ═══════════════════════════════════════════════════════════════════ -->
<style>
/* ── Reset & Container ── */
.act-studio-wrapper {
    width: 100%;
    max-width: 1260px;
    margin: 0 auto;
    padding: 0 1rem 3rem;
    box-sizing: border-box;
    font-family: inherit;
    color: rgb(var(--text, 15 23 42));
}

/* ── Top Bar ── */
.act-top-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1rem;
    margin-bottom: 1.5rem;
    padding-top: 0.5rem;
}

.act-back-link {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.9rem;
    font-weight: 700;
    color: rgb(var(--brand, 99 102 241));
    text-decoration: none;
    transition: transform 0.15s ease, opacity 0.15s ease;
}
.act-back-link:hover {
    transform: translateX(-3px);
    opacity: 0.85;
}

.act-top-actions {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 0.65rem;
}

/* ── Buttons ── */
.act-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.45rem;
    padding: 0.6rem 1.15rem;
    border-radius: 10px;
    font-size: 0.86rem;
    font-weight: 700;
    text-decoration: none;
    cursor: pointer;
    border: 1px solid transparent;
    transition: all 0.18s cubic-bezier(0.16, 1, 0.3, 1);
    white-space: nowrap;
    min-height: 42px;
    box-sizing: border-box;
}

.act-btn--primary {
    background: linear-gradient(135deg, #6366f1, #4f46e5);
    color: #ffffff !important;
    box-shadow: 0 4px 12px rgba(99, 102, 241, 0.28);
}
.act-btn--primary:hover {
    transform: translateY(-1px);
    box-shadow: 0 6px 16px rgba(99, 102, 241, 0.4);
    filter: brightness(1.06);
}

.act-btn--purple {
    background: linear-gradient(135deg, #a855f7, #7c3aed);
    color: #ffffff !important;
    box-shadow: 0 4px 12px rgba(168, 85, 247, 0.28);
}
.act-btn--purple:hover {
    transform: translateY(-1px);
    box-shadow: 0 6px 16px rgba(168, 85, 247, 0.4);
    filter: brightness(1.06);
}

.act-btn--outline {
    background: rgba(var(--card, 255 255 255));
    color: rgb(var(--text, 15 23 42)) !important;
    border: 1px solid rgba(var(--muted, 100 116 139), 0.25);
}
.act-btn--outline:hover {
    background: rgba(var(--muted, 100 116 139), 0.08);
    border-color: rgba(var(--muted, 100 116 139), 0.4);
}

.act-btn--green {
    background: rgba(16, 185, 129, 0.12);
    color: #10b981 !important;
    border: 1px solid rgba(16, 185, 129, 0.3);
}
.act-btn--green:hover {
    background: rgba(16, 185, 129, 0.22);
}

.act-btn--sm {
    padding: 0.45rem 0.85rem;
    font-size: 0.8rem;
    min-height: 36px;
    border-radius: 8px;
}

.act-btn--xs {
    padding: 0.3rem 0.65rem;
    font-size: 0.75rem;
    min-height: 30px;
    border-radius: 6px;
}

/* ── Hero Card ── */
.act-hero-card {
    background: linear-gradient(135deg, rgba(99, 102, 241, 0.08) 0%, rgba(168, 85, 247, 0.06) 100%);
    border: 1px solid rgba(99, 102, 241, 0.22);
    border-radius: 18px;
    padding: 1.5rem 1.75rem;
    margin-bottom: 1.5rem;
    position: relative;
    overflow: hidden;
}

.act-badge-row {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    flex-wrap: wrap;
    margin-bottom: 0.5rem;
}

.act-status-pill {
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    padding: 0.3rem 0.75rem;
    background: rgba(99, 102, 241, 0.15);
    color: #6366f1;
    border: 1px solid rgba(99, 102, 241, 0.3);
    border-radius: 999px;
    font-size: 0.8rem;
    font-weight: 700;
}

.act-pulse-ring {
    width: 8px;
    height: 8px;
    background: #6366f1;
    border-radius: 50%;
    box-shadow: 0 0 0 0 rgba(99, 102, 241, 0.6);
    animation: actPulse 2s infinite;
}

@keyframes actPulse {
    0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(99, 102, 241, 0.7); }
    70% { transform: scale(1); box-shadow: 0 0 0 8px rgba(99, 102, 241, 0); }
    100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(99, 102, 241, 0); }
}

.act-badge-sub {
    font-size: 0.85rem;
    color: rgb(var(--muted, 100 116 139));
    font-weight: 500;
}

.act-event-title {
    font-size: 1.75rem;
    font-weight: 800;
    line-height: 1.25;
    margin: 0.35rem 0 0.5rem;
    color: rgb(var(--text, 15 23 42));
    letter-spacing: -0.02em;
}

.act-event-meta {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    font-size: 0.88rem;
    color: rgb(var(--muted, 100 116 139));
    flex-wrap: wrap;
}

.act-meta-sep {
    opacity: 0.4;
}

/* ── Summary Grid ── */
.act-summary-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1rem;
    margin-bottom: 2rem;
}

.act-summary-card {
    background: rgb(var(--card, 255 255 255));
    border: 1px solid rgba(var(--muted, 100 116 139), 0.16);
    border-radius: 16px;
    padding: 1.25rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.03);
}

.act-summary-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.06);
    border-color: rgba(99, 102, 241, 0.3);
}

.act-card-icon-wrap {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.45rem;
    flex-shrink: 0;
}

.act-card-label {
    font-size: 0.78rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: rgb(var(--muted, 100 116 139));
    display: block;
    margin-bottom: 0.15rem;
}

.act-card-value {
    font-size: 1.65rem;
    font-weight: 800;
    line-height: 1.1;
    margin: 0;
    color: rgb(var(--text, 15 23 42));
    letter-spacing: -0.02em;
}

.act-card-meta {
    font-size: 0.78rem;
    color: rgb(var(--muted, 100 116 139));
    margin: 0.2rem 0 0;
}

/* ── Section Cards ── */
.act-modules-stack {
    display: flex;
    flex-direction: column;
    gap: 2rem;
}

.act-section-card {
    background: rgb(var(--card, 255 255 255));
    border: 1px solid rgba(var(--muted, 100 116 139), 0.18);
    border-radius: 18px;
    padding: 1.5rem;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.02);
}

.act-section-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    flex-wrap: wrap;
    gap: 1rem;
    margin-bottom: 1.25rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid rgba(var(--muted, 100 116 139), 0.12);
}

.act-section-title-wrap {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
}

.act-section-icon {
    font-size: 1.6rem;
    line-height: 1;
    margin-top: 0.1rem;
}

.act-section-title {
    font-size: 1.25rem;
    font-weight: 800;
    margin: 0;
    color: rgb(var(--text, 15 23 42));
}

.act-section-desc {
    font-size: 0.86rem;
    color: rgb(var(--muted, 100 116 139));
    margin: 0.25rem 0 0;
    line-height: 1.4;
}

/* ── Empty Box ── */
.act-empty-box {
    text-align: center;
    padding: 3rem 1.5rem;
    background: rgba(var(--muted, 100 116 139), 0.03);
    border: 2px dashed rgba(var(--muted, 100 116 139), 0.2);
    border-radius: 14px;
}

.act-empty-icon {
    font-size: 2.75rem;
    display: block;
    margin-bottom: 0.75rem;
}

.act-empty-box h3 {
    font-size: 1.15rem;
    font-weight: 700;
    margin: 0 0 0.35rem;
    color: rgb(var(--text, 15 23 42));
}

.act-empty-box p {
    font-size: 0.88rem;
    color: rgb(var(--muted, 100 116 139));
    max-width: 440px;
    margin: 0 auto 1.25rem;
    line-height: 1.45;
}

/* ── Polls Grid & Cards ── */
.act-polls-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
    gap: 1.25rem;
}

.act-poll-card {
    background: rgba(var(--muted, 100 116 139), 0.04);
    border: 1px solid rgba(var(--muted, 100 116 139), 0.18);
    border-radius: 14px;
    padding: 1.25rem;
    display: flex;
    flex-direction: column;
}

.act-poll-card-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 0.75rem;
    margin-bottom: 1rem;
}

.act-poll-card-title {
    font-size: 1.05rem;
    font-weight: 700;
    margin: 0.4rem 0 0;
    color: rgb(var(--text, 15 23 42));
    line-height: 1.3;
}

.act-poll-card-desc {
    font-size: 0.8rem;
    color: rgb(var(--muted, 100 116 139));
    margin: 0.25rem 0 0;
}

.act-poll-options-list {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    margin-bottom: 1rem;
    flex: 1;
}

.act-option-bar {
    position: relative;
    background: rgb(var(--card, 255 255 255));
    border: 1px solid rgba(var(--muted, 100 116 139), 0.15);
    border-radius: 8px;
    overflow: hidden;
    padding: 0.55rem 0.75rem;
    min-height: 38px;
    display: flex;
    align-items: center;
}

.act-option-bar-fill {
    position: absolute;
    top: 0;
    left: 0;
    bottom: 0;
    background: linear-gradient(90deg, rgba(99, 102, 241, 0.12), rgba(168, 85, 247, 0.15));
    border-right: 2px solid #6366f1;
    transition: width 0.4s ease;
    z-index: 1;
}

.act-option-bar-text {
    position: relative;
    z-index: 2;
    width: 100%;
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 0.84rem;
}

.act-option-name {
    font-weight: 600;
    color: rgb(var(--text, 15 23 42));
}

.act-option-stats {
    font-size: 0.8rem;
    color: rgb(var(--brand, 99 102 241));
}

.act-poll-card-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: 0.75rem;
    border-top: 1px solid rgba(var(--muted, 100 116 139), 0.12);
    font-size: 0.82rem;
    color: rgb(var(--muted, 100 116 139));
}

.act-link-external {
    color: rgb(var(--brand, 99 102 241));
    text-decoration: none;
    font-weight: 700;
}
.act-link-external:hover {
    text-decoration: underline;
}

/* ── Trivia Dashboard ── */
.act-trivia-dashboard {
    background: rgba(var(--muted, 100 116 139), 0.03);
    border: 1px solid rgba(var(--muted, 100 116 139), 0.18);
    border-radius: 14px;
    padding: 1.5rem;
}

.act-trivia-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    flex-wrap: wrap;
    gap: 1.25rem;
    margin-bottom: 1.25rem;
}

.act-trivia-title {
    font-size: 1.3rem;
    font-weight: 800;
    margin: 0.4rem 0 0.25rem;
    color: rgb(var(--text, 15 23 42));
}

.act-trivia-desc {
    font-size: 0.88rem;
    color: rgb(var(--muted, 100 116 139));
    margin: 0;
}

.act-trivia-metrics {
    display: flex;
    gap: 1.25rem;
}

.act-metric-box {
    text-align: right;
    padding-left: 1rem;
    border-left: 2px solid rgba(var(--muted, 100 116 139), 0.15);
}

.act-metric-val {
    font-size: 1.5rem;
    font-weight: 800;
    display: block;
    line-height: 1;
}

.act-metric-lbl {
    font-size: 0.75rem;
    color: rgb(var(--muted, 100 116 139));
    text-transform: uppercase;
    letter-spacing: 0.04em;
    margin-top: 0.25rem;
    display: block;
}

.act-trivia-config-row {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 1.5rem;
    background: rgb(var(--card, 255 255 255));
    border: 1px solid rgba(var(--muted, 100 116 139), 0.15);
    border-radius: 12px;
    padding: 1rem 1.25rem;
    margin-bottom: 1.5rem;
}

.act-config-item {
    display: flex;
    flex-direction: column;
    gap: 0.2rem;
}

.act-config-label {
    font-size: 0.75rem;
    color: rgb(var(--muted, 100 116 139));
    text-transform: uppercase;
    letter-spacing: 0.04em;
    font-weight: 600;
}

.act-config-val {
    font-size: 0.95rem;
    font-weight: 700;
    color: rgb(var(--text, 15 23 42));
}

.act-code-chip {
    display: inline-block;
    padding: 0.2rem 0.6rem;
    background: rgba(16, 185, 129, 0.12);
    color: #10b981;
    border-radius: 6px;
    border: 1px dashed rgba(16, 185, 129, 0.35);
    font-family: monospace;
    font-size: 0.9rem;
}

.act-questions-list {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.act-question-item {
    display: flex;
    align-items: flex-start;
    gap: 0.85rem;
    padding: 0.85rem 1rem;
    background: rgb(var(--card, 255 255 255));
    border: 1px solid rgba(var(--muted, 100 116 139), 0.12);
    border-radius: 10px;
}

.act-q-num {
    padding: 0.2rem 0.5rem;
    background: rgba(99, 102, 241, 0.12);
    color: #6366f1;
    border-radius: 6px;
    font-size: 0.78rem;
    font-weight: 800;
    flex-shrink: 0;
}

.act-q-content {
    flex: 1;
}

.act-q-content strong {
    font-size: 0.92rem;
    color: rgb(var(--text, 15 23 42));
}

.act-q-options {
    display: flex;
    flex-wrap: wrap;
    gap: 0.4rem;
    margin-top: 0.45rem;
}

.act-opt-pill {
    font-size: 0.78rem;
    padding: 0.2rem 0.55rem;
    background: rgba(var(--muted, 100 116 139), 0.08);
    border: 1px solid rgba(var(--muted, 100 116 139), 0.15);
    border-radius: 6px;
    color: rgb(var(--text, 15 23 42));
}

.act-opt-pill.is-correct {
    background: rgba(16, 185, 129, 0.15);
    border-color: rgba(16, 185, 129, 0.4);
    color: #059669;
    font-weight: 700;
}

/* ── Requests List ── */
.act-requests-list {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.act-req-card {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    padding: 0.9rem 1.2rem;
    background: rgba(var(--muted, 100 116 139), 0.04);
    border: 1px solid rgba(var(--muted, 100 116 139), 0.15);
    border-radius: 12px;
    transition: background 0.15s ease;
}

.act-req-card.is-played {
    opacity: 0.65;
    background: rgba(var(--muted, 100 116 139), 0.02);
}

.act-req-votes {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    width: 48px;
    height: 48px;
    background: rgb(var(--card, 255 255 255));
    border: 1px solid rgba(99, 102, 241, 0.25);
    border-radius: 10px;
    flex-shrink: 0;
}

.act-vote-arrow {
    font-size: 0.7rem;
    color: #6366f1;
    line-height: 1;
}

.act-vote-num {
    font-size: 1.05rem;
    font-weight: 800;
    color: #6366f1;
    line-height: 1;
}

.act-vote-lbl {
    font-size: 0.62rem;
    text-transform: uppercase;
    color: rgb(var(--muted, 100 116 139));
    margin-top: 1px;
}

.act-req-details {
    flex: 1;
    min-width: 0;
}

.act-req-meta-top {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 0.25rem;
    flex-wrap: wrap;
}

.act-req-author {
    font-size: 0.78rem;
    color: rgb(var(--muted, 100 116 139));
}

.act-req-title {
    font-size: 0.98rem;
    font-weight: 700;
    margin: 0;
    color: rgb(var(--text, 15 23 42));
    word-break: break-word;
}

.act-req-sub {
    font-size: 0.82rem;
    color: rgb(var(--muted, 100 116 139));
    margin: 0.15rem 0 0;
}

/* ── Hype Wall List ── */
.act-posts-list {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.act-post-card {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
    padding: 1rem 1.25rem;
    background: rgba(var(--muted, 100 116 139), 0.04);
    border: 1px solid rgba(var(--muted, 100 116 139), 0.15);
    border-radius: 12px;
}

.act-post-avatar {
    width: 42px;
    height: 42px;
    border-radius: 50%;
    background: linear-gradient(135deg, #6366f1, #a855f7);
    color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 800;
    font-size: 1.05rem;
    flex-shrink: 0;
}

.act-post-body {
    flex: 1;
    min-width: 0;
}

.act-post-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 0.5rem;
    margin-bottom: 0.35rem;
}

.act-post-author-row {
    display: flex;
    align-items: center;
    gap: 0.45rem;
    flex-wrap: wrap;
}

.act-post-author {
    font-size: 0.92rem;
    color: rgb(var(--text, 15 23 42));
}

.act-verified-pill {
    padding: 0.15rem 0.5rem;
    background: rgba(16, 185, 129, 0.15);
    color: #059669;
    border: 1px solid rgba(16, 185, 129, 0.3);
    border-radius: 999px;
    font-size: 0.72rem;
    font-weight: 700;
}

.act-post-time {
    font-size: 0.75rem;
    color: rgb(var(--muted, 100 116 139));
}

.act-post-msg {
    font-size: 0.9rem;
    color: rgb(var(--text, 15 23 42));
    margin: 0 0 0.5rem;
    line-height: 1.45;
    word-break: break-word;
}

.act-post-footer {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 0.85rem;
    font-size: 0.78rem;
    color: rgb(var(--muted, 100 116 139));
}

.act-post-likes {
    color: #ef4444;
    font-weight: 600;
}

.act-btn-delete {
    background: none;
    border: none;
    cursor: pointer;
    font-size: 1.15rem;
    padding: 0.4rem;
    border-radius: 6px;
    transition: background 0.15s ease;
    opacity: 0.6;
}
.act-btn-delete:hover {
    opacity: 1;
    background: rgba(239, 68, 68, 0.1);
}

/* ── Generic Pills ── */
.act-pill {
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    padding: 0.2rem 0.55rem;
    border-radius: 6px;
    font-size: 0.74rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.03em;
}

.act-pill--green {
    background: rgba(16, 185, 129, 0.12);
    color: #10b981;
    border: 1px solid rgba(16, 185, 129, 0.25);
}

.act-pill--purple {
    background: rgba(168, 85, 247, 0.12);
    color: #a855f7;
    border: 1px solid rgba(168, 85, 247, 0.25);
}

.act-pill--blue {
    background: rgba(59, 130, 246, 0.12);
    color: #3b82f6;
    border: 1px solid rgba(59, 130, 246, 0.25);
}

.act-pill--amber {
    background: rgba(245, 158, 11, 0.12);
    color: #d97706;
    border: 1px solid rgba(245, 158, 11, 0.25);
}

.act-pill--muted {
    background: rgba(var(--muted, 100 116 139), 0.12);
    color: rgb(var(--muted, 100 116 139));
    border: 1px solid rgba(var(--muted, 100 116 139), 0.2);
}

/* ── Modals ── */
.act-modal-backdrop {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.6);
    backdrop-filter: blur(6px);
    -webkit-backdrop-filter: blur(6px);
    z-index: 9999;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1rem;
    box-sizing: border-box;
}

.act-modal-card {
    background: rgb(var(--card, 255 255 255));
    border: 1px solid rgba(var(--muted, 100 116 139), 0.2);
    border-radius: 20px;
    width: 100%;
    max-width: 520px;
    max-height: 90vh;
    overflow-y: auto;
    padding: 1.75rem;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
    animation: actModalIn 0.22s cubic-bezier(0.16, 1, 0.3, 1);
}

.act-modal-card--lg {
    max-width: 650px;
}

@keyframes actModalIn {
    from { opacity: 0; transform: scale(0.95) translateY(8px); }
    to { opacity: 1; transform: scale(1) translateY(0); }
}

.act-modal-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1.25rem;
    padding-bottom: 0.85rem;
    border-bottom: 1px solid rgba(var(--muted, 100 116 139), 0.15);
}

.act-modal-title {
    font-size: 1.25rem;
    font-weight: 800;
    margin: 0;
    color: rgb(var(--text, 15 23 42));
}

.act-modal-subtitle {
    font-size: 0.82rem;
    color: rgb(var(--muted, 100 116 139));
    margin: 0.2rem 0 0;
}

.act-modal-close {
    background: none;
    border: none;
    font-size: 1.75rem;
    line-height: 1;
    color: rgb(var(--muted, 100 116 139));
    cursor: pointer;
    padding: 0 0.4rem;
}
.act-modal-close:hover {
    color: rgb(var(--text, 15 23 42));
}

.act-form-group {
    margin-bottom: 1.15rem;
}

.act-form-row {
    display: flex;
    gap: 0.85rem;
}

.act-label {
    display: block;
    font-size: 0.82rem;
    font-weight: 700;
    color: rgb(var(--text, 15 23 42));
    margin-bottom: 0.35rem;
}

.act-req {
    color: #ef4444;
}

.act-input {
    width: 100%;
    padding: 0.65rem 0.85rem;
    border: 1px solid rgba(var(--muted, 100 116 139), 0.25);
    background: rgba(var(--muted, 100 116 139), 0.04);
    border-radius: 9px;
    font-size: 0.88rem;
    color: rgb(var(--text, 15 23 42));
    box-sizing: border-box;
    transition: border-color 0.15s ease, box-shadow 0.15s ease;
    min-height: 40px;
}
.act-input:focus {
    outline: none;
    border-color: #6366f1;
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.18);
    background: rgb(var(--card, 255 255 255));
}

.act-options-builder {
    display: flex;
    flex-direction: column;
    gap: 0.45rem;
}

.act-q-builder-box {
    padding: 1rem;
    background: rgba(var(--muted, 100 116 139), 0.04);
    border: 1px solid rgba(var(--muted, 100 116 139), 0.18);
    border-radius: 12px;
}

.act-q-grid-options {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.5rem;
    margin-top: 0.5rem;
}

.act-modal-actions {
    display: flex;
    gap: 0.75rem;
    margin-top: 1.5rem;
}

/* ═══════════════════════════════════════════════════════════════════
   RESPONSIVE BREAKPOINTS (Desktop, Tablet, Mobile)
   ═══════════════════════════════════════════════════════════════════ */

/* Tablet (max 1024px) */
@media (max-width: 1024px) {
    .act-summary-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

/* Mobile (max 768px) */
@media (max-width: 768px) {
    .act-studio-wrapper {
        padding: 0 0.75rem 2.5rem;
    }

    .act-top-bar {
        flex-direction: column;
        align-items: stretch;
        gap: 0.85rem;
    }

    .act-top-actions {
        display: grid;
        grid-template-columns: 1fr;
        width: 100%;
        gap: 0.5rem;
    }

    .act-top-actions .act-btn {
        width: 100%;
    }

    .act-hero-card {
        padding: 1.25rem 1rem;
    }

    .act-event-title {
        font-size: 1.45rem;
    }

    .act-summary-grid {
        grid-template-columns: 1fr;
        gap: 0.75rem;
    }

    .act-section-card {
        padding: 1.15rem;
    }

    .act-section-header {
        flex-direction: column;
        align-items: stretch;
    }

    .act-section-header .act-btn {
        width: 100%;
    }

    .act-polls-grid {
        grid-template-columns: 1fr;
    }

    .act-trivia-metrics {
        width: 100%;
        justify-content: space-between;
        margin-top: 0.75rem;
    }

    .act-trivia-config-row {
        flex-direction: column;
        align-items: stretch;
        gap: 0.85rem;
    }

    .act-q-grid-options {
        grid-template-columns: 1fr;
    }

    .act-form-row {
        flex-direction: column;
        gap: 0;
    }

    .act-req-card {
        flex-direction: column;
        align-items: stretch;
        gap: 0.75rem;
    }

    .act-req-votes {
        align-self: flex-start;
    }

    .act-req-actions {
        width: 100%;
    }

    .act-req-actions .act-btn {
        width: 100%;
    }

    .act-post-card {
        flex-direction: column;
    }

    .act-post-actions {
        align-self: flex-end;
    }

    .act-modal-card {
        padding: 1.25rem;
        max-height: 95vh;
    }

    .act-modal-actions {
        flex-direction: column;
    }
}

/* Small Mobile (max 480px) */
@media (max-width: 480px) {
    .act-event-title {
        font-size: 1.3rem;
    }

    .act-card-value {
        font-size: 1.4rem;
    }
}
</style>

<!-- ═══════════════════════════════════════════════════════════════════
     CLIENT INTERACTION JAVASCRIPT
     ═══════════════════════════════════════════════════════════════════ -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Poll Modal
    const pollModal = document.getElementById('create-poll-modal');
    const openPollBtns = [document.getElementById('open-poll-modal-btn'), ...document.querySelectorAll('.open-poll-trigger')].filter(Boolean);
    const closePollBtns = document.querySelectorAll('.close-poll-btn');

    openPollBtns.forEach(btn => btn.addEventListener('click', () => {
        pollModal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }));
    closePollBtns.forEach(btn => btn.addEventListener('click', () => {
        pollModal.style.display = 'none';
        document.body.style.overflow = '';
    }));

    // Trivia Modal
    const triviaModal = document.getElementById('create-trivia-modal');
    const openTriviaBtns = [document.getElementById('open-trivia-modal-btn'), ...document.querySelectorAll('.open-trivia-trigger')].filter(Boolean);
    const closeTriviaBtns = document.querySelectorAll('.close-trivia-btn');

    openTriviaBtns.forEach(btn => btn.addEventListener('click', () => {
        triviaModal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }));
    closeTriviaBtns.forEach(btn => btn.addEventListener('click', () => {
        triviaModal.style.display = 'none';
        document.body.style.overflow = '';
    }));

    // Close on backdrop click
    [pollModal, triviaModal].forEach(m => {
        m.addEventListener('click', function(e) {
            if (e.target === m) {
                m.style.display = 'none';
                document.body.style.overflow = '';
            }
        });
    });
});
</script>
@endsection
