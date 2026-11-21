<?php

/**
 * Event Community & Activity Hub (Public / Attendee View)
 *
 * Modern, simple, and intuitive interactive arena featuring:
 * 1. Community Hype Wall & Squad Finder
 * 2. Live Polls & Voting Contests (Free & Paid)
 * 3. Trivia & Guess Game (Play to win Promo Codes)
 * 4. DJ Song Requests & Speaker Q&A Upvoting
 *
 * @package Eventlyy
 * @subpackage Views
 * @version 2.0.0
 */

$siteTitle = site_setting('site_title', 'Eventlyy');
$eventUrl = event_url($event);
$eventSlug = !empty($event->custom_url) ? $event->custom_url : ($event->slug ?? $event->id);
$activeTab = $activeTab ?? 'wall';
if (!in_array($activeTab, ['wall', 'polls', 'trivia', 'requests'])) {
    $activeTab = 'wall';
}
?>

@extends('layouts/main')

@section('title')
<?= e($title ?? ('Live Activity Hub — ' . $event->name)) ?>
@endsection

@section('content')
<div class="hub-container">

    <!-- ═══════════════════════════════════════════════════════════════
         1. TOP NAV BREADCRUMB & CONTEXT
         ═══════════════════════════════════════════════════════════════ -->
    <div class="hub-nav-strip">
        <a href="<?= e($eventUrl) ?>" class="hub-back-btn">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="19" y1="12" x2="5" y2="12"></line>
                <polyline points="12 19 5 12 12 5"></polyline>
            </svg>
            <span>Back to Event Details</span>
        </a>
        <div class="hub-live-pill">
            <span class="hub-live-dot"></span>
            <span>Live Interactive Arena</span>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════════════
         2. EVENT HERO BANNER
         ═══════════════════════════════════════════════════════════════ -->
    <section class="hub-hero">
        <div class="hub-hero-info">
            <div class="hub-badge-row">
                <span class="hub-chip hub-chip--indigo">🎉 Activity Lounge</span>
                <?php if (!empty($event->category)): ?>
                    <span class="hub-chip hub-chip--glass"><?= e($event->category) ?></span>
                <?php endif; ?>
            </div>
            <h1 class="hub-title"><?= e($event->name) ?></h1>
            <div class="hub-meta-row">
                <span>📅 <?= date('l, M j, Y • g:i A', strtotime($event->date)) ?></span>
                <span class="hub-meta-dot">•</span>
                <span>📍 <?= e($event->location) ?></span>
            </div>
        </div>
        <div class="hub-hero-action">
            <a href="<?= e($eventUrl) ?>" class="hub-ticket-btn">
                <span>Get Tickets 🎟️</span>
            </a>
        </div>
    </section>

    <!-- ═══════════════════════════════════════════════════════════════
         3. MODERN SEGMENTED PILL NAVIGATION BAR
         ═══════════════════════════════════════════════════════════════ -->
    <div class="hub-tabs-track-wrap">
        <nav class="hub-tabs-track" role="tablist">
            <button type="button" class="hub-pill-btn <?= $activeTab === 'wall' ? 'active' : '' ?>" data-tab="tab-wall" data-tab-name="wall" role="tab" aria-selected="<?= $activeTab === 'wall' ? 'true' : 'false' ?>">
                <span class="hub-pill-icon">📣</span>
                <span class="hub-pill-label">Hype Wall &amp; Squad</span>
                <span class="hub-pill-count"><?= count($communityPosts) ?></span>
            </button>
            <button type="button" class="hub-pill-btn <?= $activeTab === 'polls' ? 'active' : '' ?>" data-tab="tab-polls" data-tab-name="polls" role="tab" aria-selected="<?= $activeTab === 'polls' ? 'true' : 'false' ?>">
                <span class="hub-pill-icon">🗳️</span>
                <span class="hub-pill-label">Live Polls</span>
                <span class="hub-pill-count"><?= count($polls) ?></span>
            </button>
            <button type="button" class="hub-pill-btn <?= $activeTab === 'trivia' ? 'active' : '' ?>" data-tab="tab-trivia" data-tab-name="trivia" role="tab" aria-selected="<?= $activeTab === 'trivia' ? 'true' : 'false' ?>">
                <span class="hub-pill-icon">🧠</span>
                <span class="hub-pill-label">Trivia Challenge</span>
                <?php if ($trivia && !empty($trivia['reward_promo_code'])): ?>
                    <span class="hub-pill-count hub-pill-count--sparkle">Promo</span>
                <?php endif; ?>
            </button>
            <button type="button" class="hub-pill-btn <?= $activeTab === 'requests' ? 'active' : '' ?>" data-tab="tab-requests" data-tab-name="requests" role="tab" aria-selected="<?= $activeTab === 'requests' ? 'true' : 'false' ?>">
                <span class="hub-pill-icon">🎵</span>
                <span class="hub-pill-label">DJ Song &amp; Q&amp;A</span>
                <span class="hub-pill-count"><?= count($songRequests) + count($qaRequests) ?></span>
            </button>
        </nav>
    </div>

    <!-- ═══════════════════════════════════════════════════════════════
         TAB 1: Hype Wall & Squad Finder
         ═══════════════════════════════════════════════════════════════ -->
    <div id="tab-wall" class="hub-tab-panel <?= $activeTab === 'wall' ? 'active' : '' ?>" role="tabpanel">
        <div class="hub-two-col">
            <!-- Composer Card -->
            <div class="hub-panel-card hub-composer-box">
                <div class="hub-card-head">
                    <span class="hub-card-icon">✍️</span>
                    <div>
                        <h3 class="hub-card-heading">Post on the Wall</h3>
                        <p class="hub-card-desc">Hyped for the night? Looking for concert friends or ride shares? Introduce yourself!</p>
                    </div>
                </div>

                <form method="POST" action="/event/<?= $eventSlug ?>/community" id="wall-post-form">
                    <?= csrf_field() ?>

                    <div class="hub-field">
                        <label class="hub-label">What are you posting?</label>
                        <select name="category" id="post-category" class="hub-select">
                            <option value="hype">🔥 General Hype &amp; Excitement</option>
                            <option value="squad">🤝 Squad Finder (Going Solo / Meet Up)</option>
                            <option value="carpool">🚗 Carpool / Ride Share</option>
                            <option value="general">💬 General Question / Chat</option>
                        </select>
                    </div>

                    <?php if (!$user): ?>
                        <div class="hub-field-row">
                            <div class="hub-field" style="flex:1;">
                                <label class="hub-label">Your Name <span class="hub-req">*</span></label>
                                <input type="text" name="author_name" id="post-name" required placeholder="e.g. Tola M." class="hub-input" />
                            </div>
                            <div class="hub-field" style="flex:1;">
                                <label class="hub-label">Your Email <span class="hub-opt">(For ticket check)</span></label>
                                <input type="email" name="author_email" id="post-email" placeholder="attendee@example.com" class="hub-input" />
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="hub-field-row">
                        <div class="hub-field" style="flex:1;">
                            <label class="hub-label">Area / Location <span class="hub-opt">(Optional)</span></label>
                            <input type="text" name="location_area" id="post-area" placeholder="e.g. Lekki, Ikeja, Yaba" class="hub-input" />
                        </div>
                        <div class="hub-field" style="flex:1;">
                            <label class="hub-label">Social / Contact <span class="hub-opt">(Optional)</span></label>
                            <input type="text" name="contact_handle" id="post-contact" placeholder="e.g. @your_instagram" class="hub-input" />
                        </div>
                    </div>

                    <div class="hub-field">
                        <label class="hub-label">Your Message <span class="hub-req">*</span></label>
                        <textarea name="message" id="post-msg" rows="3" required placeholder="Can't wait for this! Who else is arriving early? Let's meet up and vibe!" class="hub-input hub-textarea"></textarea>
                    </div>

                    <button type="submit" class="hub-submit-btn">
                        <span>Post to Wall 🚀</span>
                    </button>
                </form>
            </div>

            <!-- Feed Column -->
            <div class="hub-feed-col">
                <div class="hub-feed-head">
                    <div>
                        <h3 class="hub-feed-title">Attendee Messages &amp; Squads</h3>
                        <span class="hub-feed-sub">Join the conversation with other guests</span>
                    </div>
                    <div class="hub-filter-pills">
                        <button type="button" class="hub-filter-pill active" data-filter="all">All</button>
                        <button type="button" class="hub-filter-pill" data-filter="hype">🔥 Hype</button>
                        <button type="button" class="hub-filter-pill" data-filter="squad">🤝 Squads</button>
                        <button type="button" class="hub-filter-pill" data-filter="carpool">🚗 Rides</button>
                    </div>
                </div>

                <div class="hub-feed-list" id="community-feed-list">
                    <?php if (empty($communityPosts)): ?>
                        <div class="hub-empty-state">
                            <span class="hub-empty-emoji">📣</span>
                            <h4>The wall is quiet right now!</h4>
                            <p>Be the very first to drop a hype shoutout, find friends to go with, or arrange a ride.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($communityPosts as $post): ?>
                            <article class="hub-card-item" data-cat="<?= e($post['category']) ?>">
                                <div class="hub-post-header">
                                    <div class="hub-avatar">
                                        <?= strtoupper(substr($post['author_name'], 0, 1)) ?>
                                    </div>
                                    <div class="hub-post-meta">
                                        <div class="hub-post-author-row">
                                            <strong class="hub-author-name"><?= e($post['author_name']) ?></strong>
                                            <?php if (!empty($post['is_ticket_holder'])): ?>
                                                <span class="hub-badge-ticket">✓ Ticket Holder</span>
                                            <?php endif; ?>
                                            <span class="hub-category-tag <?= e($post['category']) ?>">
                                                <?= ucfirst(e($post['category'])) ?>
                                            </span>
                                        </div>
                                        <div class="hub-post-subtext">
                                            <?php if (!empty($post['location_area'])): ?>
                                                <span>📍 <?= e($post['location_area']) ?></span>
                                                <span class="hub-dot">•</span>
                                            <?php endif; ?>
                                            <span><?= date('M j, g:i A', strtotime($post['created_at'])) ?></span>
                                        </div>
                                    </div>
                                    <button type="button" class="hub-like-btn <?= !empty($post['user_liked']) ? 'liked' : '' ?>" data-id="<?= $post['id'] ?>" data-slug="<?= $eventSlug ?>" title="<?= !empty($post['user_liked']) ? 'Unlike this post' : 'Love this post' ?>">
                                        <span class="hub-heart-icon"><?= !empty($post['user_liked']) ? '❤️' : '🤍' ?></span>
                                        <span class="like-count"><?= (int)$post['likes_count'] ?></span>
                                    </button>
                                </div>
                                <p class="hub-post-body"><?= nl2br(e($post['message'])) ?></p>
                                <?php if (!empty($post['contact_handle'])): ?>
                                    <div class="hub-post-contact">
                                        <span>💬 Connect:</span>
                                        <strong><?= e($post['contact_handle']) ?></strong>
                                    </div>
                                <?php endif; ?>
                            </article>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════════════
         TAB 2: Live Polls & Voting Contests
         ═══════════════════════════════════════════════════════════════ -->
    <div id="tab-polls" class="hub-tab-panel <?= $activeTab === 'polls' ? 'active' : '' ?>" role="tabpanel">
        <?php if (empty($polls)): ?>
            <div class="hub-empty-state" style="padding:4rem 1.5rem;">
                <span class="hub-empty-emoji">🗳️</span>
                <h3>No Active Voting Contests</h3>
                <p>The host hasn't launched any voting contests for this event yet. Check back closer to showtime!</p>
            </div>
        <?php else: ?>
            <div class="hub-polls-grid">
                <?php foreach ($polls as $poll): 
                    $isPower = in_array($poll['poll_type'], ['power', 'paid']);
                    $voteCost = (float)($poll['vote_cost'] ?: 100);
                ?>
                    <div class="hub-poll-card">
                        <div class="hub-poll-header">
                            <div>
                                <span class="hub-vote-type-pill <?= $isPower ? 'hub-vote-type-pill--power' : '' ?>">
                                    <?php if ($poll['poll_type'] === 'free'): ?>
                                        ✦ 1 Free Vote Every 24h
                                    <?php elseif ($poll['poll_type'] === 'power'): ?>
                                        ⚡ Power Voting Enabled
                                    <?php else: ?>
                                        🏆 Paid Contest Only
                                    <?php endif; ?>
                                </span>
                                <h3 class="hub-poll-title"><?= e($poll['title']) ?></h3>
                                <?php if (!empty($poll['description'])): ?>
                                    <p class="hub-poll-desc"><?= e($poll['description']) ?></p>
                                <?php endif; ?>
                            </div>
                            <div class="hub-poll-total">
                                <strong class="hub-poll-total-num"><?= number_format($poll['total_votes']) ?></strong>
                                <span class="hub-poll-total-lbl">votes cast</span>
                            </div>
                        </div>

                        <!-- Free Vote Section (1 daily free vote for registered & verified users) -->
                        <?php if ($poll['poll_type'] !== 'paid'): 
                            $userVotedToday = ($user && $user->isVerified()) ? (new \App\Services\EventActivityService())->hasVotedFree($poll['id'], (int)$user->id) : false;
                        ?>
                            <?php if ($user && $user->isVerified()): ?>
                                <?php if ($userVotedToday): ?>
                                    <div class="hub-options-list">
                                        <?php foreach ($poll['options'] as $idx => $opt): 
                                            $pct = (float)($opt['percentage'] ?? 0);
                                        ?>
                                            <div class="hub-option-card" style="cursor:default;">
                                                <div class="hub-option-inner">
                                                    <div class="hub-option-bar-fill" style="width: <?= $pct ?>%;"></div>
                                                    <div class="hub-option-content">
                                                        <span class="hub-option-title"><?= e($opt['title']) ?></span>
                                                        <span class="hub-option-stats">
                                                            <strong><?= $opt['percentage'] ?>%</strong>
                                                            <span class="hub-option-votes">(<?= number_format($opt['votes_count']) ?>)</span>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <div style="margin-top:1.15rem; padding:0.9rem 1.15rem; background:#ecfdf5; border:1px solid #a7f3d0; border-radius:12px; display:flex; align-items:center; gap:0.6rem;">
                                        <span style="font-size:1.3rem;">✓</span>
                                        <div style="font-size:0.85rem; color:#065f46;">
                                            <strong>Free Vote Cast Today!</strong> Your vote was counted. Want to give your nominee an extra edge? Use <strong>⚡ Power Vote</strong> below.
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <form method="POST" action="/event/<?= $eventSlug ?>/vote/<?= $poll['id'] ?>" class="hub-poll-form">
                                        <?= csrf_field() ?>

                                        <div class="hub-options-list">
                                            <?php foreach ($poll['options'] as $idx => $opt): 
                                                $pct = (float)($opt['percentage'] ?? 0);
                                            ?>
                                                <label class="hub-option-card">
                                                    <input type="radio" name="option_id" value="<?= $opt['id'] ?>" required class="hub-option-radio">
                                                    <div class="hub-option-inner">
                                                        <div class="hub-option-bar-fill" style="width: <?= $pct ?>%;"></div>
                                                        <div class="hub-option-content">
                                                            <div class="hub-option-radio-dot"></div>
                                                            <span class="hub-option-title"><?= e($opt['title']) ?></span>
                                                            <span class="hub-option-stats">
                                                                <strong><?= $opt['percentage'] ?>%</strong>
                                                                <span class="hub-option-votes">(<?= number_format($opt['votes_count']) ?>)</span>
                                                            </span>
                                                        </div>
                                                    </div>
                                                </label>
                                            <?php endforeach; ?>
                                        </div>

                                        <button type="submit" class="hub-submit-btn" style="margin-top:1.25rem;">
                                            <span>🗳️ Cast Your Free Daily Vote</span>
                                        </button>
                                    </form>
                                <?php endif; ?>
                            <?php elseif ($user && !$user->isVerified()): ?>
                                <div class="hub-options-list">
                                    <?php foreach ($poll['options'] as $idx => $opt): 
                                        $pct = (float)($opt['percentage'] ?? 0);
                                    ?>
                                        <div class="hub-option-card" style="cursor:default;">
                                            <div class="hub-option-inner">
                                                <div class="hub-option-bar-fill" style="width: <?= $pct ?>%;"></div>
                                                <div class="hub-option-content">
                                                    <span class="hub-option-title"><?= e($opt['title']) ?></span>
                                                    <span class="hub-option-stats">
                                                        <strong><?= $opt['percentage'] ?>%</strong>
                                                        <span class="hub-option-votes">(<?= number_format($opt['votes_count']) ?>)</span>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <div style="margin-top:1rem; padding:1.15rem; background:#fffbeb; border:1px solid #fef3c7; border-radius:12px; text-align:center;">
                                    <p style="margin:0 0 0.35rem; font-weight:700; color:#b45309; font-size:0.9rem;">⚠️ Email Verification Required</p>
                                    <p style="margin:0; font-size:0.82rem; color:#92400e;">Please verify your email address to unlock your daily free vote, or boost your nominee directly with a <strong>⚡ Power Vote</strong> below!</p>
                                </div>
                            <?php else: ?>
                                <div class="hub-options-list">
                                    <?php foreach ($poll['options'] as $idx => $opt): 
                                        $pct = (float)($opt['percentage'] ?? 0);
                                    ?>
                                        <div class="hub-option-card" style="cursor:default;">
                                            <div class="hub-option-inner">
                                                <div class="hub-option-bar-fill" style="width: <?= $pct ?>%;"></div>
                                                <div class="hub-option-content">
                                                    <span class="hub-option-title"><?= e($opt['title']) ?></span>
                                                    <span class="hub-option-stats">
                                                        <strong><?= $opt['percentage'] ?>%</strong>
                                                        <span class="hub-option-votes">(<?= number_format($opt['votes_count']) ?>)</span>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <div style="margin-top:1rem; padding:1.25rem; background:#f8fafc; border:1px dashed #cbd5e1; border-radius:12px; text-align:center;">
                                    <p style="margin:0 0 0.4rem; font-weight:700; color:#1e293b; font-size:0.92rem;">🔒 Free Daily Voting is for Verified Attendees</p>
                                    <p style="margin:0 0 0.9rem; font-size:0.82rem; color:#64748b;">Sign in with your verified Eventlyy account to cast your 1 free daily vote, or boost your favorite nominee instantly below with <strong>⚡ Power Vote</strong>!</p>
                                    <div style="display:flex; justify-content:center; gap:0.6rem; flex-wrap:wrap;">
                                        <a href="/login" style="display:inline-flex; align-items:center; gap:0.35rem; background:#6366f1; color:#fff; padding:0.5rem 1.15rem; border-radius:8px; font-weight:700; font-size:0.82rem; text-decoration:none;">
                                            🔑 Sign In to Vote Free
                                        </a>
                                        <a href="/register" style="display:inline-flex; align-items:center; gap:0.35rem; background:#f1f5f9; color:#334155; padding:0.5rem 1.15rem; border-radius:8px; font-weight:700; font-size:0.82rem; text-decoration:none; border:1px solid #e2e8f0;">
                                            Create Account
                                        </a>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>

                        <!-- ⚡ Power Vote Section (for 'power' and 'paid' polls) -->
                        <?php if ($isPower): ?>
                        <div class="pv-section" data-poll-id="<?= $poll['id'] ?>" data-vote-cost="<?= $voteCost ?>">
                            <div class="pv-divider">
                                <span class="pv-divider-line"></span>
                                <span class="pv-divider-text">⚡ Power Vote — Boost Your Nominee</span>
                                <span class="pv-divider-line"></span>
                            </div>

                            <!-- Step 1: Select Nominee -->
                            <div class="pv-step">
                                <label class="pv-step-label">1. Choose Nominee</label>
                                <select class="hub-select pv-option-select">
                                    <option value="">— Select who to boost —</option>
                                    <?php foreach ($poll['options'] as $opt): ?>
                                        <option value="<?= $opt['id'] ?>"><?= e($opt['title']) ?> (<?= number_format($opt['votes_count']) ?> votes)</option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Step 2: Choose Vote Pack -->
                            <div class="pv-step">
                                <label class="pv-step-label">2. Pick Your Vote Pack</label>
                                <div class="pv-packs-grid">
                                    <button type="button" class="pv-pack-btn" data-qty="5">
                                        <span class="pv-pack-icon">⚡</span>
                                        <span class="pv-pack-name">Spark</span>
                                        <span class="pv-pack-votes">5 votes</span>
                                        <span class="pv-pack-price">₦<?= number_format($voteCost * 5) ?></span>
                                    </button>
                                    <button type="button" class="pv-pack-btn pv-pack--popular" data-qty="20">
                                        <span class="pv-pack-badge">POPULAR</span>
                                        <span class="pv-pack-icon">🔥</span>
                                        <span class="pv-pack-name">Flame</span>
                                        <span class="pv-pack-votes">20 votes</span>
                                        <span class="pv-pack-price">₦<?= number_format($voteCost * 20) ?></span>
                                    </button>
                                    <button type="button" class="pv-pack-btn" data-qty="50">
                                        <span class="pv-pack-icon">🚀</span>
                                        <span class="pv-pack-name">Rocket</span>
                                        <span class="pv-pack-votes">50 votes</span>
                                        <span class="pv-pack-price">₦<?= number_format($voteCost * 50) ?></span>
                                    </button>
                                    <button type="button" class="pv-pack-btn" data-qty="100">
                                        <span class="pv-pack-icon">👑</span>
                                        <span class="pv-pack-name">Crown</span>
                                        <span class="pv-pack-votes">100 votes</span>
                                        <span class="pv-pack-price">₦<?= number_format($voteCost * 100) ?></span>
                                    </button>
                                </div>
                                <!-- Custom quantity -->
                                <div class="pv-custom-row">
                                    <label class="pv-custom-label">Or enter custom votes:</label>
                                    <div class="pv-custom-input-wrap">
                                        <input type="number" class="hub-input pv-custom-qty" min="1" max="10000" placeholder="e.g. 35" />
                                        <span class="pv-custom-total">= ₦0</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Step 3: Guest Info (if not logged in) -->
                            <?php if (!$user): ?>
                            <div class="pv-step">
                                <label class="pv-step-label">3. Your Details (for receipt)</label>
                                <div class="hub-field-row">
                                    <input type="text" class="hub-input pv-voter-name" placeholder="Your Name" style="flex:1;" />
                                    <input type="email" class="hub-input pv-voter-email" placeholder="Your Email *" required style="flex:1;" />
                                </div>
                            </div>
                            <?php endif; ?>

                            <!-- Summary & Pay Button -->
                            <div class="pv-summary" style="display:none;">
                                <div class="pv-summary-row">
                                    <span>Nominee:</span> <strong class="pv-sum-nominee">—</strong>
                                </div>
                                <div class="pv-summary-row">
                                    <span>Votes:</span> <strong class="pv-sum-qty">0</strong> × ₦<?= number_format($voteCost) ?>/vote
                                </div>
                                <div class="pv-summary-row pv-summary-total">
                                    <span>Total:</span> <strong class="pv-sum-total">₦0</strong>
                                </div>
                            </div>

                            <button type="button" class="pv-pay-btn" disabled>
                                ⚡ Pay & Cast Power Votes
                            </button>

                            <p class="pv-note">Secure payment via Paystack. Votes are credited instantly after payment.</p>
                        </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- ═══════════════════════════════════════════════════════════════
         TAB 3: Trivia & Guess Game
         ═══════════════════════════════════════════════════════════════ -->
    <div id="tab-trivia" class="hub-tab-panel <?= $activeTab === 'trivia' ? 'active' : '' ?>" role="tabpanel">
        <?php if (!$trivia || empty($trivia['questions'])): ?>
            <div class="hub-empty-state" style="padding:4rem 1.5rem;">
                <span class="hub-empty-emoji">🧠</span>
                <h3>No Trivia Challenge Active</h3>
                <p>Stay tuned! The host will launch quick trivia games with exclusive ticket discount codes soon.</p>
            </div>
        <?php else: ?>
            <div class="hub-trivia-wrapper" id="trivia-container" data-trivia-id="<?= $trivia['id'] ?>" data-slug="<?= $eventSlug ?>">
                
                <!-- Screen 1: Start Challenge -->
                <div id="trivia-start-screen" class="hub-trivia-card">
                    <span class="hub-chip hub-chip--purple">★ Event Knowledge Challenge</span>
                    <h2 class="hub-trivia-main-title"><?= e($trivia['title']) ?></h2>
                    <p class="hub-trivia-subtitle"><?= e($trivia['description'] ?? 'Answer questions correctly against the clock to unlock a mystery promo code discount!') ?></p>

                    <div class="hub-trivia-perks">
                        <div class="hub-perk-item">
                            <span class="hub-perk-icon">🎯</span>
                            <div>
                                <span class="hub-perk-label">Question Bank</span>
                                <strong><?= count($trivia['questions']) ?> Questions</strong>
                            </div>
                        </div>
                        <div class="hub-perk-item">
                            <span class="hub-perk-icon">🏆</span>
                            <div>
                                <span class="hub-perk-label">Passing Target</span>
                                <strong><?= (int)$trivia['pass_score'] ?> Correct Answers</strong>
                            </div>
                        </div>
                        <div class="hub-perk-item">
                            <span class="hub-perk-icon">🎁</span>
                            <div>
                                <span class="hub-perk-label">Winner Reward</span>
                                <strong><?= e($trivia['reward_promo_code'] ? 'Secret Ticket Promo' : 'Eventlyy Credits') ?></strong>
                            </div>
                        </div>
                    </div>

                    <?php if (!$user): ?>
                        <div class="hub-field-row" style="max-width:440px; margin:1.5rem auto 1rem;">
                            <input type="text" id="trivia-player-name" required placeholder="Enter Your Name" class="hub-input" />
                            <input type="email" id="trivia-player-email" required placeholder="Enter Your Email" class="hub-input" />
                        </div>
                    <?php endif; ?>

                    <button type="button" id="start-trivia-btn" class="hub-submit-btn" style="max-width:320px; margin:1.25rem auto 0;">
                        <span>Start Challenge Now ⏱️</span>
                    </button>
                </div>

                <!-- Screen 2: Active Gameplay -->
                <div id="trivia-game-screen" class="hub-trivia-card" style="display:none;">
                    <div class="hub-trivia-progress-track">
                        <div class="hub-trivia-progress-fill" id="trivia-progress-bar" style="width: 0%;"></div>
                    </div>

                    <div class="hub-trivia-game-head">
                        <span id="trivia-q-counter" class="hub-q-badge">Question 1 of <?= count($trivia['questions']) ?></span>
                        <span id="trivia-timer" class="hub-timer-badge">
                            ⏱️ <span id="time-left">60</span>s remaining
                        </span>
                    </div>

                    <div id="trivia-question-content">
                        <!-- Injected by JavaScript -->
                    </div>

                    <div class="hub-trivia-actions">
                        <button type="button" id="trivia-next-btn" class="hub-submit-btn" disabled>
                            <span>Submit Answer →</span>
                        </button>
                    </div>
                </div>

                <!-- Screen 3: Results & Promo Reveal -->
                <div id="trivia-results-screen" class="hub-trivia-card" style="display:none;">
                    <div id="trivia-results-badge"></div>
                    <h2 id="trivia-results-title" style="margin:0.5rem 0; font-size:1.6rem; font-weight:800;"></h2>
                    <p id="trivia-results-score" style="font-size:1.1rem; font-weight:700; color:rgb(var(--text)); margin:0.5rem 0 1.25rem;"></p>

                    <div id="trivia-reward-box" class="hub-reward-voucher" style="display:none;">
                        <span class="hub-reward-tag">🎉 YOUR EXCLUSIVE UNLOCKED REWARD</span>
                        <div class="hub-voucher-code-wrap">
                            <span id="unlocked-promo-code" class="hub-voucher-code"></span>
                            <button type="button" id="copy-promo-btn" class="hub-copy-btn">📋 Copy Code</button>
                        </div>
                        <p id="trivia-reward-desc" style="font-weight:700; color:#10b981; font-size:1.05rem; margin:0.6rem 0 0.2rem;"></p>
                        <p class="hub-voucher-note">Apply this discount code during ticket reservation to claim your savings! Reserved exclusively for your email.</p>
                        
                        <a id="trivia-claim-btn" href="<?= \url('/checkout/' . $event['id']) ?>" class="hub-submit-btn" style="margin-top:1.25rem; max-width:320px; margin-left:auto; margin-right:auto; text-decoration:none; display:flex; align-items:center; justify-content:center;">
                            <span>Claim Discount at Checkout 🎟️</span>
                        </a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- ═══════════════════════════════════════════════════════════════
         TAB 4: DJ Requests & Speaker Q&A Upvoting
         ═══════════════════════════════════════════════════════════════ -->
    <div id="tab-requests" class="hub-tab-panel <?= $activeTab === 'requests' ? 'active' : '' ?>" role="tabpanel">
        <div class="hub-two-col">
            <!-- Left: Request Form -->
            <div class="hub-panel-card hub-composer-box">
                <div class="hub-card-head">
                    <span class="hub-card-icon">🎵</span>
                    <div>
                        <h3 class="hub-card-heading">Suggest a Track or Question</h3>
                        <p class="hub-card-sub">What song should the DJ spin, or what question should be asked on stage? Submit and let the crowd upvote!</p>
                    </div>
                </div>

                <form id="request-submit-form">
                    <?= csrf_field() ?>

                    <div class="hub-field">
                        <label class="hub-label">Submission Category</label>
                        <select id="req-type" class="hub-select">
                            <option value="song">🎵 DJ Song / Track Request</option>
                            <option value="qa">🎤 Speaker / Panel Q&amp;A Question</option>
                        </select>
                    </div>

                    <div class="hub-field">
                        <label class="hub-label" id="req-title-label">Song Title <span class="hub-req">*</span></label>
                        <input type="text" id="req-title" required placeholder="e.g. City Boys, Calm Down, Ojuelegba" class="hub-input" />
                    </div>

                    <div class="hub-field">
                        <label class="hub-label" id="req-artist-label">Artist Name <span class="hub-opt">(Optional)</span></label>
                        <input type="text" id="req-artist" placeholder="e.g. Burna Boy, Rema, Wizkid" class="hub-input" />
                    </div>

                    <?php if (!$user): ?>
                        <div class="hub-field">
                            <label class="hub-label">Your Name <span class="hub-req">*</span></label>
                            <input type="text" id="req-name" required placeholder="e.g. Alex" class="hub-input" />
                        </div>
                    <?php endif; ?>

                    <button type="submit" class="hub-submit-btn">
                        <span>Submit Suggestion 🎶</span>
                    </button>
                </form>
            </div>

            <!-- Right: Ranked Leaderboard List -->
            <div class="hub-feed-col">
                <div class="hub-feed-head">
                    <div>
                        <h3 class="hub-feed-title">Top Upvoted Setlist &amp; Questions</h3>
                        <span class="hub-feed-sub">Sorted in real-time by crowd upvotes</span>
                    </div>
                </div>

                <div class="hub-feed-list" id="requests-list">
                    <?php 
                        $sList = is_array($songRequests) ? $songRequests : (method_exists($songRequests, 'toArray') ? $songRequests->toArray() : (array)$songRequests);
                        $qList = is_array($qaRequests) ? $qaRequests : (method_exists($qaRequests, 'toArray') ? $qaRequests->toArray() : (array)$qaRequests);
                        $allRequests = array_merge($sList, $qList);
                        usort($allRequests, fn($a, $b) => ($b['upvotes_count'] ?? 0) <=> ($a['upvotes_count'] ?? 0));
                    ?>
                    <?php if (empty($allRequests)): ?>
                        <div class="hub-empty-state">
                            <span class="hub-empty-emoji">🎵</span>
                            <h4>No track requests or questions yet!</h4>
                            <p>Be the first to tell the DJ or speaker what you want to experience on stage.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($allRequests as $req): ?>
                            <div class="hub-request-card <?= ($req['status'] ?? '') === 'played' ? 'is-played' : '' ?>">
                                <button type="button" class="hub-upvote-box req-upvote-btn <?= !empty($req['user_voted']) ? 'voted' : '' ?>" data-id="<?= $req['id'] ?>" data-slug="<?= $eventSlug ?>" data-type="<?= $req['type'] ?>" title="<?= !empty($req['user_voted']) ? 'Click to remove your vote' : 'Upvote this track or question' ?>">
                                    <span class="hub-upvote-arrow">▲</span>
                                    <strong class="upvote-count"><?= (int)($req['upvotes_count'] ?? 0) ?></strong>
                                    <span class="hub-upvote-lbl">votes</span>
                                </button>
                                <div class="hub-request-details">
                                    <div class="hub-req-tags">
                                        <span class="hub-tag-badge <?= $req['type'] ?>">
                                            <?= $req['type'] === 'song' ? '🎵 Track' : '🎤 Stage Q&A' ?>
                                        </span>
                                        <?php if (($req['status'] ?? '') === 'played'): ?>
                                            <span class="hub-tag-badge is-done">✓ Played / Answered</span>
                                        <?php endif; ?>
                                    </div>
                                    <h4 class="hub-request-title"><?= e($req['title']) ?></h4>
                                    <?php if (!empty($req['artist_or_speaker'])): ?>
                                        <div class="hub-request-artist">by <strong><?= e($req['artist_or_speaker']) ?></strong></div>
                                    <?php endif; ?>
                                    <?php if (!empty($req['requester_name'])): ?>
                                        <div class="hub-request-author">Suggested by <?= e($req['requester_name']) ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- ═══════════════════════════════════════════════════════════════
     COMPREHENSIVE MODERN STYLESHEET & RESPONSIVENESS
     ═══════════════════════════════════════════════════════════════ -->
<style>
/* ── Reset & Container ── */
.hub-container {
    width: 100%;
    max-width: 1200px;
    margin: 0 auto;
    padding: 1rem 1rem 4rem;
    box-sizing: border-box;
    font-family: inherit;
    color: rgb(var(--text, 15 23 42));
}

/* ── Top Nav Strip ── */
.hub-nav-strip {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 0.75rem;
    margin-bottom: 1.25rem;
}

.hub-back-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    font-size: 0.88rem;
    font-weight: 700;
    color: rgb(var(--brand, 99 102 241));
    text-decoration: none;
    transition: transform 0.15s ease;
}
.hub-back-btn:hover {
    transform: translateX(-3px);
}

.hub-live-pill {
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    padding: 0.28rem 0.75rem;
    border-radius: 999px;
    background: rgba(16, 185, 129, 0.12);
    color: #059669;
    border: 1px solid rgba(16, 185, 129, 0.25);
    font-size: 0.78rem;
    font-weight: 700;
}

.hub-live-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #10b981;
    box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
    animation: hubPulse 2s infinite;
}

@keyframes hubPulse {
    0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
    70% { transform: scale(1); box-shadow: 0 0 0 8px rgba(16, 185, 129, 0); }
    100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
}

/* ── Hero Banner ── */
.hub-hero {
    background: linear-gradient(135deg, rgba(99, 102, 241, 0.08) 0%, rgba(236, 72, 153, 0.05) 100%);
    border: 1px solid rgba(99, 102, 241, 0.2);
    border-radius: 20px;
    padding: 1.75rem 2rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1.25rem;
    margin-bottom: 2rem;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
}

.hub-hero-info {
    flex: 1;
    min-width: 280px;
}

.hub-badge-row {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex-wrap: wrap;
    margin-bottom: 0.5rem;
}

.hub-chip {
    display: inline-flex;
    align-items: center;
    padding: 0.22rem 0.65rem;
    border-radius: 999px;
    font-size: 0.76rem;
    font-weight: 700;
}

.hub-chip--indigo {
    background: rgba(99, 102, 241, 0.15);
    color: #6366f1;
    border: 1px solid rgba(99, 102, 241, 0.3);
}

.hub-chip--glass {
    background: rgba(var(--card, 255 255 255), 0.8);
    color: rgb(var(--text, 15 23 42));
    border: 1px solid rgba(var(--muted, 100 116 139), 0.2);
}

.hub-chip--purple {
    background: rgba(168, 85, 247, 0.15);
    color: #a855f7;
    border: 1px solid rgba(168, 85, 247, 0.3);
}

.hub-title {
    font-size: clamp(1.6rem, 3.2vw, 2.3rem);
    font-weight: 800;
    line-height: 1.2;
    margin: 0.3rem 0 0.5rem;
    letter-spacing: -0.02em;
    color: rgb(var(--text, 15 23 42));
}

.hub-meta-row {
    display: flex;
    align-items: center;
    gap: 0.55rem;
    font-size: 0.88rem;
    color: rgb(var(--muted, 100 116 139));
    flex-wrap: wrap;
}

.hub-meta-dot {
    opacity: 0.4;
}

.hub-ticket-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0.75rem 1.6rem;
    background: linear-gradient(135deg, #6366f1, #4f46e5);
    color: #ffffff !important;
    font-weight: 700;
    font-size: 0.92rem;
    border-radius: 12px;
    text-decoration: none;
    box-shadow: 0 4px 16px rgba(99, 102, 241, 0.35);
    transition: all 0.2s ease;
    min-height: 44px;
    white-space: nowrap;
}
.hub-ticket-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(99, 102, 241, 0.45);
    filter: brightness(1.05);
}

/* ── Modern Segmented Pill Nav ── */
.hub-tabs-track-wrap {
    margin-bottom: 2rem;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: none;
}
.hub-tabs-track-wrap::-webkit-scrollbar {
    display: none;
}

.hub-tabs-track {
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    padding: 0.35rem;
    background: rgba(var(--muted, 100 116 139), 0.08);
    border: 1px solid rgba(var(--muted, 100 116 139), 0.16);
    border-radius: 16px;
    min-width: 100%;
    box-sizing: border-box;
}

.hub-pill-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.55rem;
    padding: 0.65rem 1.15rem;
    border-radius: 12px;
    border: none;
    background: transparent;
    color: rgb(var(--muted, 100 116 139));
    font-size: 0.9rem;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    white-space: nowrap;
    min-height: 42px;
}

.hub-pill-btn:hover {
    color: rgb(var(--text, 15 23 42));
    background: rgba(var(--card, 255 255 255), 0.5);
}

.hub-pill-btn.active {
    background: rgb(var(--card, 255 255 255));
    color: rgb(var(--brand, 99 102 241));
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
}

.hub-pill-icon {
    font-size: 1.1rem;
    line-height: 1;
}

.hub-pill-count {
    padding: 0.12rem 0.5rem;
    border-radius: 999px;
    background: rgba(var(--muted, 100 116 139), 0.14);
    color: inherit;
    font-size: 0.75rem;
    font-weight: 800;
}

.hub-pill-count--sparkle {
    background: rgba(168, 85, 247, 0.2);
    color: #a855f7;
}

/* ── Tab Panels ── */
.hub-tab-panel {
    display: none;
}
.hub-tab-panel.active {
    display: block;
    animation: hubFadeIn 0.25s ease;
}

@keyframes hubFadeIn {
    from { opacity: 0; transform: translateY(6px); }
    to { opacity: 1; transform: translateY(0); }
}

/* ── 2-Column Balanced Layout ── */
.hub-two-col {
    display: grid;
    grid-template-columns: 380px 1fr;
    gap: 1.75rem;
    align-items: flex-start;
}

/* ── Cards & Panels ── */
.hub-panel-card {
    background: rgb(var(--card, 255 255 255));
    border: 1px solid rgba(var(--muted, 100 116 139), 0.18);
    border-radius: 18px;
    padding: 1.5rem;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.02);
}

.hub-card-head {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
    margin-bottom: 1.25rem;
}

.hub-card-icon {
    font-size: 1.5rem;
    line-height: 1;
    margin-top: 0.1rem;
}

.hub-card-heading {
    font-size: 1.15rem;
    font-weight: 800;
    margin: 0;
    color: rgb(var(--text, 15 23 42));
}

.hub-card-desc {
    font-size: 0.84rem;
    color: rgb(var(--muted, 100 116 139));
    margin: 0.25rem 0 0;
    line-height: 1.4;
}

/* ── Form Inputs ── */
.hub-field {
    margin-bottom: 1.1rem;
}

.hub-field-row {
    display: flex;
    gap: 0.75rem;
}

.hub-label {
    display: block;
    font-size: 0.82rem;
    font-weight: 700;
    color: rgb(var(--text, 15 23 42));
    margin-bottom: 0.35rem;
}

.hub-req {
    color: #ef4444;
}

.hub-opt {
    font-size: 0.75rem;
    font-weight: 500;
    color: rgb(var(--muted, 100 116 139));
}

.hub-input,
.hub-select {
    width: 100%;
    padding: 0.65rem 0.85rem;
    border: 1px solid rgba(var(--muted, 100 116 139), 0.25);
    background: rgba(var(--muted, 100 116 139), 0.04);
    border-radius: 10px;
    font-size: 0.88rem;
    color: rgb(var(--text, 15 23 42));
    box-sizing: border-box;
    transition: all 0.18s ease;
    min-height: 42px;
}
.hub-input:focus,
.hub-select:focus {
    outline: none;
    border-color: #6366f1;
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.18);
    background: rgb(var(--card, 255 255 255));
}

.hub-textarea {
    min-height: 85px;
    resize: vertical;
}

.hub-submit-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    width: 100%;
    padding: 0.75rem 1.4rem;
    background: linear-gradient(135deg, #6366f1, #4f46e5);
    color: #ffffff !important;
    font-size: 0.92rem;
    font-weight: 700;
    border-radius: 11px;
    border: none;
    cursor: pointer;
    box-shadow: 0 4px 14px rgba(99, 102, 241, 0.3);
    transition: all 0.2s ease;
    min-height: 44px;
}
.hub-submit-btn:hover:not(:disabled) {
    transform: translateY(-1px);
    box-shadow: 0 6px 18px rgba(99, 102, 241, 0.45);
    filter: brightness(1.06);
}
.hub-submit-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

/* ── Feed Section ── */
.hub-feed-col {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.hub-feed-head {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 0.75rem;
    padding-bottom: 0.5rem;
}

.hub-feed-title {
    font-size: 1.2rem;
    font-weight: 800;
    margin: 0;
    color: rgb(var(--text, 15 23 42));
}

.hub-feed-sub {
    font-size: 0.82rem;
    color: rgb(var(--muted, 100 116 139));
    display: block;
    margin-top: 0.15rem;
}

.hub-filter-pills {
    display: flex;
    gap: 0.4rem;
    flex-wrap: wrap;
}

.hub-filter-pill {
    padding: 0.28rem 0.65rem;
    border-radius: 8px;
    border: 1px solid rgba(var(--muted, 100 116 139), 0.2);
    background: transparent;
    color: rgb(var(--muted, 100 116 139));
    font-size: 0.78rem;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.15s ease;
}
.hub-filter-pill:hover {
    color: rgb(var(--text, 15 23 42));
    background: rgba(var(--muted, 100 116 139), 0.08);
}
.hub-filter-pill.active {
    background: rgb(var(--brand, 99 102 241));
    color: #ffffff;
    border-color: rgb(var(--brand, 99 102 241));
}

.hub-feed-list {
    display: flex;
    flex-direction: column;
    gap: 0.85rem;
}

/* ── Community Post Item ── */
.hub-card-item {
    background: rgb(var(--card, 255 255 255));
    border: 1px solid rgba(var(--muted, 100 116 139), 0.16);
    border-radius: 14px;
    padding: 1.15rem;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);
    transition: border-color 0.15s ease, box-shadow 0.15s ease;
}
.hub-card-item:hover {
    border-color: rgba(99, 102, 241, 0.3);
    box-shadow: 0 4px 14px rgba(0, 0, 0, 0.04);
}

.hub-post-header {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
    margin-bottom: 0.65rem;
}

.hub-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: linear-gradient(135deg, #6366f1, #a855f7);
    color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 800;
    font-size: 0.95rem;
    flex-shrink: 0;
}

.hub-post-meta {
    flex: 1;
    min-width: 0;
}

.hub-post-author-row {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    flex-wrap: wrap;
}

.hub-author-name {
    font-size: 0.92rem;
    font-weight: 700;
    color: rgb(var(--text, 15 23 42));
}

.hub-badge-ticket {
    padding: 0.12rem 0.5rem;
    background: rgba(16, 185, 129, 0.15);
    color: #059669;
    border: 1px solid rgba(16, 185, 129, 0.3);
    border-radius: 999px;
    font-size: 0.7rem;
    font-weight: 800;
}

.hub-category-tag {
    padding: 0.12rem 0.45rem;
    border-radius: 6px;
    font-size: 0.72rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.03em;
    background: rgba(var(--muted, 100 116 139), 0.1);
    color: rgb(var(--muted, 100 116 139));
}

.hub-category-tag.hype { background: rgba(239, 68, 68, 0.12); color: #dc2626; }
.hub-category-tag.squad { background: rgba(99, 102, 241, 0.12); color: #4f46e5; }
.hub-category-tag.carpool { background: rgba(16, 185, 129, 0.12); color: #059669; }

.hub-post-subtext {
    font-size: 0.75rem;
    color: rgb(var(--muted, 100 116 139));
    margin-top: 0.15rem;
}

.hub-dot {
    margin: 0 0.25rem;
    opacity: 0.5;
}

.hub-like-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    padding: 0.35rem 0.65rem;
    border-radius: 999px;
    border: 1px solid rgba(var(--muted, 100 116 139), 0.2);
    background: transparent;
    cursor: pointer;
    font-size: 0.8rem;
    font-weight: 700;
    color: rgb(var(--muted, 100 116 139));
    transition: all 0.15s ease;
}
.hub-like-btn:hover {
    border-color: #ef4444;
    color: #ef4444;
    background: rgba(239, 68, 68, 0.08);
}
.hub-like-btn.liked {
    border-color: #ef4444;
    color: #ef4444;
    background: rgba(239, 68, 68, 0.12);
}
.hub-like-btn.liked .hub-heart-icon {
    display: inline-block;
    transform: scale(1.15);
    animation: heartPulse 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}
@keyframes heartPulse {
    0% { transform: scale(0.8); }
    50% { transform: scale(1.35); }
    100% { transform: scale(1.15); }
}
.hub-heart-icon {
    font-size: 0.88rem;
    line-height: 1;
}

.hub-post-body {
    font-size: 0.9rem;
    line-height: 1.5;
    color: rgb(var(--text, 15 23 42));
    margin: 0 0 0.5rem;
    word-break: break-word;
}

.hub-post-contact {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    font-size: 0.78rem;
    padding: 0.3rem 0.65rem;
    background: rgba(var(--muted, 100 116 139), 0.06);
    border-radius: 8px;
    color: rgb(var(--muted, 100 116 139));
}
.hub-post-contact strong {
    color: rgb(var(--brand, 99 102 241));
}

/* ── Polls Grid ── */
.hub-polls-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(360px, 1fr));
    gap: 1.5rem;
}

.hub-poll-card {
    background: rgb(var(--card, 255 255 255));
    border: 1px solid rgba(var(--muted, 100 116 139), 0.18);
    border-radius: 18px;
    padding: 1.6rem;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.02);
}

.hub-poll-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 1rem;
    margin-bottom: 1.25rem;
}

.hub-vote-type-pill {
    display: inline-block;
    padding: 0.2rem 0.55rem;
    border-radius: 6px;
    font-size: 0.74rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.03em;
    background: rgba(99, 102, 241, 0.12);
    color: #4f46e5;
    margin-bottom: 0.4rem;
}

.hub-poll-title {
    font-size: 1.2rem;
    font-weight: 800;
    margin: 0;
    color: rgb(var(--text, 15 23 42));
    line-height: 1.3;
}

.hub-poll-desc {
    font-size: 0.84rem;
    color: rgb(var(--muted, 100 116 139));
    margin: 0.25rem 0 0;
}

.hub-poll-total {
    text-align: right;
    flex-shrink: 0;
}

.hub-poll-total-num {
    font-size: 1.35rem;
    font-weight: 800;
    color: rgb(var(--text, 15 23 42));
    display: block;
    line-height: 1;
}

.hub-poll-total-lbl {
    font-size: 0.72rem;
    text-transform: uppercase;
    color: rgb(var(--muted, 100 116 139));
    letter-spacing: 0.04em;
    display: block;
    margin-top: 0.15rem;
}

.hub-options-list {
    display: flex;
    flex-direction: column;
    gap: 0.65rem;
}

.hub-option-card {
    display: block;
    cursor: pointer;
    position: relative;
}

.hub-option-radio {
    position: absolute;
    opacity: 0;
    pointer-events: none;
}

.hub-option-inner {
    position: relative;
    border: 1.5px solid rgba(var(--muted, 100 116 139), 0.2);
    border-radius: 12px;
    background: rgba(var(--muted, 100 116 139), 0.03);
    overflow: hidden;
    padding: 0.75rem 1rem;
    transition: all 0.2s ease;
    min-height: 48px;
    display: flex;
    align-items: center;
    box-sizing: border-box;
}

.hub-option-card:hover .hub-option-inner {
    border-color: rgba(99, 102, 241, 0.4);
    background: rgba(99, 102, 241, 0.02);
}

.hub-option-card input:checked + .hub-option-inner {
    border-color: #6366f1;
    background: rgba(99, 102, 241, 0.04);
    box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.2);
}

.hub-option-bar-fill {
    position: absolute;
    top: 0;
    left: 0;
    bottom: 0;
    background: linear-gradient(90deg, rgba(99, 102, 241, 0.12), rgba(168, 85, 247, 0.14));
    border-right: 2px solid #6366f1;
    transition: width 0.4s ease;
    z-index: 1;
}

.hub-option-content {
    position: relative;
    z-index: 2;
    width: 100%;
    display: flex;
    align-items: center;
    gap: 0.65rem;
}

.hub-option-radio-dot {
    width: 18px;
    height: 18px;
    border-radius: 50%;
    border: 2px solid rgba(var(--muted, 100 116 139), 0.4);
    background: rgb(var(--card, 255 255 255));
    flex-shrink: 0;
    transition: all 0.18s ease;
}

.hub-option-card input:checked + .hub-option-inner .hub-option-radio-dot {
    border-color: #6366f1;
    background: #6366f1;
    box-shadow: inset 0 0 0 3px #ffffff;
}

.hub-option-title {
    font-size: 0.92rem;
    font-weight: 700;
    color: rgb(var(--text, 15 23 42));
    flex: 1;
}

.hub-option-stats {
    font-size: 0.85rem;
    color: rgb(var(--brand, 99 102 241));
}

.hub-option-votes {
    font-size: 0.78rem;
    color: rgb(var(--muted, 100 116 139));
    font-weight: 500;
}

/* ── Trivia Styles ── */
.hub-trivia-wrapper {
    max-width: 680px;
    margin: 0 auto;
}

.hub-trivia-card {
    background: rgb(var(--card, 255 255 255));
    border: 1px solid rgba(var(--muted, 100 116 139), 0.18);
    border-radius: 20px;
    padding: 2.25rem 2rem;
    text-align: center;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
}

.hub-trivia-main-title {
    font-size: 1.75rem;
    font-weight: 800;
    margin: 0.5rem 0 0.35rem;
    color: rgb(var(--text, 15 23 42));
}

.hub-trivia-subtitle {
    font-size: 0.92rem;
    color: rgb(var(--muted, 100 116 139));
    line-height: 1.5;
    max-width: 520px;
    margin: 0 auto 1.5rem;
}

.hub-trivia-perks {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1rem;
    padding: 1.15rem;
    background: rgba(var(--muted, 100 116 139), 0.04);
    border: 1px solid rgba(var(--muted, 100 116 139), 0.14);
    border-radius: 14px;
    margin-bottom: 1.5rem;
    text-align: left;
}

.hub-perk-item {
    display: flex;
    align-items: center;
    gap: 0.65rem;
}

.hub-perk-icon {
    font-size: 1.4rem;
    line-height: 1;
}

.hub-perk-label {
    font-size: 0.72rem;
    text-transform: uppercase;
    color: rgb(var(--muted, 100 116 139));
    display: block;
    letter-spacing: 0.03em;
}

.hub-perk-item strong {
    font-size: 0.88rem;
    color: rgb(var(--text, 15 23 42));
}

.hub-trivia-progress-track {
    width: 100%;
    height: 8px;
    background: rgba(var(--muted, 100 116 139), 0.12);
    border-radius: 999px;
    overflow: hidden;
    margin-bottom: 1.25rem;
}

.hub-trivia-progress-fill {
    height: 100%;
    background: linear-gradient(90deg, #6366f1, #a855f7);
    transition: width 0.3s ease;
}

.hub-trivia-game-head {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
}

.hub-q-badge {
    font-size: 0.85rem;
    font-weight: 700;
    color: rgb(var(--text, 15 23 42));
}

.hub-timer-badge {
    font-size: 0.85rem;
    font-weight: 700;
    color: #ef4444;
    padding: 0.25rem 0.65rem;
    border-radius: 999px;
    background: rgba(239, 68, 68, 0.1);
}

.hub-trivia-question-text {
    font-size: 1.35rem;
    font-weight: 800;
    line-height: 1.35;
    margin: 1rem 0 1.5rem;
    color: rgb(var(--text, 15 23 42));
}

.hub-trivia-options-box {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    max-width: 520px;
    margin: 0 auto 1.5rem;
    text-align: left;
}

.hub-trivia-option-label {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.85rem 1.15rem;
    border: 1.5px solid rgba(var(--muted, 100 116 139), 0.2);
    border-radius: 12px;
    background: rgba(var(--muted, 100 116 139), 0.03);
    cursor: pointer;
    transition: all 0.18s ease;
    min-height: 48px;
}
.hub-trivia-option-label:hover {
    border-color: #6366f1;
    background: rgba(99, 102, 241, 0.04);
}
.hub-trivia-option-label input:checked + .hub-q-opt-letter {
    background: #6366f1;
    color: #ffffff;
}

.hub-q-opt-letter {
    width: 26px;
    height: 26px;
    border-radius: 50%;
    background: rgba(var(--muted, 100 116 139), 0.14);
    color: rgb(var(--text, 15 23 42));
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.78rem;
    font-weight: 800;
    flex-shrink: 0;
}

.hub-q-opt-text {
    font-size: 0.92rem;
    font-weight: 600;
    color: rgb(var(--text, 15 23 42));
}

.hub-trivia-actions {
    max-width: 320px;
    margin: 1.5rem auto 0;
}

.hub-reward-voucher {
    background: linear-gradient(135deg, rgba(16, 185, 129, 0.08) 0%, rgba(99, 102, 241, 0.05) 100%);
    border: 2px dashed #10b981;
    border-radius: 16px;
    padding: 1.75rem;
    margin-top: 1.5rem;
}

.hub-reward-tag {
    font-size: 0.78rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: #059669;
    display: block;
    margin-bottom: 0.75rem;
}

.hub-voucher-code-wrap {
    display: inline-flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.5rem 1rem;
    background: rgb(var(--card, 255 255 255));
    border: 1px solid rgba(16, 185, 129, 0.3);
    border-radius: 10px;
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.15);
}

.hub-voucher-code {
    font-family: monospace;
    font-size: 1.35rem;
    font-weight: 800;
    letter-spacing: 2px;
    color: #059669;
}

.hub-copy-btn {
    padding: 0.35rem 0.75rem;
    border-radius: 6px;
    border: 1px solid rgba(var(--muted, 100 116 139), 0.25);
    background: rgba(var(--muted, 100 116 139), 0.08);
    font-size: 0.8rem;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.15s ease;
}
.hub-copy-btn:hover {
    background: #10b981;
    color: #ffffff;
    border-color: #10b981;
}

.hub-voucher-note {
    font-size: 0.85rem;
    color: rgb(var(--muted, 100 116 139));
    margin: 0.75rem 0 0;
}

/* ── DJ Requests List ── */
.hub-request-card {
    background: rgb(var(--card, 255 255 255));
    border: 1px solid rgba(var(--muted, 100 116 139), 0.18);
    border-radius: 14px;
    padding: 1rem 1.25rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    transition: border-color 0.15s ease, box-shadow 0.15s ease;
}
.hub-request-card:hover {
    border-color: rgba(99, 102, 241, 0.35);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
}

.hub-request-card.is-played {
    opacity: 0.65;
    background: rgba(var(--muted, 100 116 139), 0.02);
}

.hub-upvote-box {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    width: 48px;
    height: 48px;
    border-radius: 10px;
    border: 1.5px solid rgba(99, 102, 241, 0.25);
    background: rgba(99, 102, 241, 0.04);
    cursor: pointer;
    flex-shrink: 0;
    transition: all 0.18s ease;
    padding: 0;
}
.hub-upvote-box:hover {
    background: #6366f1;
    border-color: #6366f1;
    color: #ffffff !important;
}
.hub-upvote-box:hover .hub-upvote-arrow,
.hub-upvote-box:hover .upvote-count,
.hub-upvote-box:hover .hub-upvote-lbl {
    color: #ffffff !important;
}

.hub-upvote-box.voted {
    background: #6366f1 !important;
    border-color: #6366f1 !important;
    color: #ffffff !important;
    box-shadow: 0 4px 14px rgba(99, 102, 241, 0.35);
}
.hub-upvote-box.voted .hub-upvote-arrow,
.hub-upvote-box.voted .upvote-count,
.hub-upvote-box.voted .hub-upvote-lbl {
    color: #ffffff !important;
}

.hub-upvote-arrow {
    font-size: 0.7rem;
    line-height: 1;
    color: #6366f1;
}
.upvote-count {
    font-size: 1rem;
    font-weight: 800;
    line-height: 1;
    color: #6366f1;
}
.hub-upvote-lbl {
    font-size: 0.6rem;
    text-transform: uppercase;
    color: rgb(var(--muted, 100 116 139));
    margin-top: 1px;
}

.hub-request-details {
    flex: 1;
    min-width: 0;
}

.hub-req-tags {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    margin-bottom: 0.25rem;
    flex-wrap: wrap;
}

.hub-tag-badge {
    font-size: 0.7rem;
    font-weight: 700;
    padding: 0.12rem 0.5rem;
    border-radius: 6px;
    text-transform: uppercase;
    letter-spacing: 0.03em;
}

.hub-tag-badge.song {
    background: rgba(59, 130, 246, 0.12);
    color: #2563eb;
}

.hub-tag-badge.qa {
    background: rgba(245, 158, 11, 0.12);
    color: #d97706;
}

.hub-tag-badge.is-done {
    background: rgba(16, 185, 129, 0.12);
    color: #059669;
}

.hub-request-title {
    font-size: 1.05rem;
    font-weight: 700;
    margin: 0;
    color: rgb(var(--text, 15 23 42));
    word-break: break-word;
}

.hub-request-artist {
    font-size: 0.85rem;
    color: rgb(var(--muted, 100 116 139));
    margin-top: 0.15rem;
}

.hub-request-author {
    font-size: 0.75rem;
    color: rgb(var(--muted, 100 116 139));
    margin-top: 0.2rem;
}

/* ── Empty State ── */
.hub-empty-state {
    text-align: center;
    padding: 3rem 1.5rem;
    background: rgba(var(--muted, 100 116 139), 0.03);
    border: 2px dashed rgba(var(--muted, 100 116 139), 0.2);
    border-radius: 16px;
}

.hub-empty-emoji {
    font-size: 2.75rem;
    display: block;
    margin-bottom: 0.75rem;
}

.hub-empty-state h3,
.hub-empty-state h4 {
    font-size: 1.15rem;
    font-weight: 800;
    margin: 0 0 0.35rem;
    color: rgb(var(--text, 15 23 42));
}

.hub-empty-state p {
    font-size: 0.88rem;
    color: rgb(var(--muted, 100 116 139));
    max-width: 420px;
    margin: 0 auto;
    line-height: 1.45;
}

/* ═══════════════════════════════════════════════════════════════
   RESPONSIVE MEDIA QUERIES
   ═══════════════════════════════════════════════════════════════ */

/* Tablet (max 992px) */
@media (max-width: 992px) {
    .hub-two-col {
        grid-template-columns: 1fr;
        gap: 1.5rem;
    }
    .hub-polls-grid {
        grid-template-columns: 1fr;
    }
}

/* Mobile (max 680px) */
@media (max-width: 680px) {
    .hub-container {
        padding: 0.75rem 0.75rem 3.5rem;
    }

    .hub-hero {
        padding: 1.25rem 1rem;
        flex-direction: column;
        align-items: stretch;
    }

    .hub-hero-action {
        width: 100%;
    }

    .hub-ticket-btn {
        width: 100%;
    }

    .hub-tabs-track {
        width: 100%;
    }

    .hub-pill-btn {
        padding: 0.55rem 0.85rem;
        font-size: 0.82rem;
    }

    .hub-panel-card {
        padding: 1.25rem 1rem;
    }

    .hub-field-row {
        flex-direction: column;
        gap: 0;
    }

    .hub-trivia-card {
        padding: 1.5rem 1rem;
    }

    .hub-trivia-perks {
        grid-template-columns: 1fr;
        gap: 0.75rem;
    }

    .hub-voucher-code-wrap {
        flex-direction: column;
        width: 100%;
        box-sizing: border-box;
    }

    .hub-copy-btn {
        width: 100%;
    }

    .hub-feed-head {
        flex-direction: column;
        align-items: flex-start;
    }
}

/* ═══════════════════════════════════════════════════════════════
   ⚡ POWER VOTE STYLES
   ═══════════════════════════════════════════════════════════════ */

.hub-vote-type-pill--power {
    background: linear-gradient(135deg, #6366f1, #8b5cf6);
    color: #fff;
}

/* Power Vote Section */
.pv-section {
    margin-top: 1.5rem;
    padding-top: 0;
}

.pv-divider {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 1.5rem;
}

.pv-divider-line {
    flex: 1;
    height: 1px;
    background: linear-gradient(90deg, transparent, rgba(99,102,241,0.3), transparent);
}

.pv-divider-text {
    font-size: 0.8rem;
    font-weight: 700;
    color: #6366f1;
    white-space: nowrap;
    letter-spacing: 0.02em;
}

.pv-step {
    margin-bottom: 1.25rem;
}

.pv-step-label {
    display: block;
    font-size: 0.8rem;
    font-weight: 700;
    color: #64748b;
    margin-bottom: 0.5rem;
    text-transform: uppercase;
    letter-spacing: 0.04em;
}

/* Vote Packs Grid */
.pv-packs-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 0.65rem;
}

.pv-pack-btn {
    position: relative;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.2rem;
    padding: 1rem 0.5rem 0.85rem;
    border: 2px solid #e2e8f0;
    border-radius: 12px;
    background: #fff;
    cursor: pointer;
    transition: all 0.2s ease;
    text-align: center;
}

.pv-pack-btn:hover {
    border-color: #6366f1;
    transform: translateY(-2px);
    box-shadow: 0 4px 16px rgba(99,102,241,0.15);
}

.pv-pack-btn.active {
    border-color: #6366f1;
    background: linear-gradient(135deg, #eef2ff, #f5f3ff);
    box-shadow: 0 0 0 3px rgba(99,102,241,0.15);
}

.pv-pack-badge {
    position: absolute;
    top: -8px;
    left: 50%;
    transform: translateX(-50%);
    background: linear-gradient(135deg, #f59e0b, #f97316);
    color: #fff;
    font-size: 0.55rem;
    font-weight: 800;
    padding: 0.1rem 0.5rem;
    border-radius: 100px;
    letter-spacing: 0.08em;
    white-space: nowrap;
}

.pv-pack-icon {
    font-size: 1.5rem;
    line-height: 1;
}

.pv-pack-name {
    font-size: 0.7rem;
    font-weight: 700;
    color: #334155;
    text-transform: uppercase;
    letter-spacing: 0.04em;
}

.pv-pack-votes {
    font-size: 0.75rem;
    color: #64748b;
    font-weight: 500;
}

.pv-pack-price {
    font-size: 0.8rem;
    font-weight: 800;
    color: #6366f1;
}

/* Custom Quantity */
.pv-custom-row {
    margin-top: 0.85rem;
}

.pv-custom-label {
    display: block;
    font-size: 0.75rem;
    color: #94a3b8;
    margin-bottom: 0.35rem;
}

.pv-custom-input-wrap {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.pv-custom-qty {
    max-width: 120px;
    text-align: center;
    font-weight: 700;
}

.pv-custom-total {
    font-size: 0.85rem;
    font-weight: 700;
    color: #6366f1;
    white-space: nowrap;
}

/* Summary Box */
.pv-summary {
    background: linear-gradient(135deg, #f8fafc, #eef2ff);
    border: 1px solid #e0e7ff;
    border-radius: 12px;
    padding: 1rem 1.15rem;
    margin-bottom: 1rem;
}

.pv-summary-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.3rem 0;
    font-size: 0.85rem;
    color: #475569;
}

.pv-summary-total {
    border-top: 2px solid #c7d2fe;
    margin-top: 0.4rem;
    padding-top: 0.6rem;
    font-size: 1rem;
}

.pv-summary-total strong {
    color: #6366f1;
    font-size: 1.15rem;
}

/* Pay Button */
.pv-pay-btn {
    width: 100%;
    padding: 1rem;
    border: none;
    border-radius: 12px;
    background: linear-gradient(135deg, #6366f1, #8b5cf6);
    color: #fff;
    font-size: 1rem;
    font-weight: 800;
    cursor: pointer;
    transition: all 0.2s ease;
    letter-spacing: 0.02em;
}

.pv-pay-btn:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 6px 24px rgba(99,102,241,0.35);
}

.pv-pay-btn:disabled {
    background: #cbd5e1;
    cursor: not-allowed;
    color: #94a3b8;
}

.pv-pay-btn.pv-pay-loading {
    pointer-events: none;
    opacity: 0.7;
}

.pv-note {
    text-align: center;
    font-size: 0.7rem;
    color: #94a3b8;
    margin-top: 0.75rem;
}

/* Responsive */
@media (max-width: 600px) {
    .pv-packs-grid {
        grid-template-columns: repeat(2, 1fr);
    }

    .pv-pack-btn {
        padding: 0.85rem 0.4rem 0.7rem;
    }
}

@media (max-width: 380px) {
    .pv-packs-grid {
        grid-template-columns: 1fr 1fr;
        gap: 0.5rem;
    }
}
</style>

<!-- ═══════════════════════════════════════════════════════════════
     CLIENT INTERACTION JAVASCRIPT
     ═══════════════════════════════════════════════════════════════ -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // ── Helper: CSRF Token ──
    function getCsrfToken() {
        const meta = document.querySelector('meta[name="csrf-token"]');
        if (meta && meta.getAttribute('content')) return meta.getAttribute('content');
        const inp = document.querySelector('input[name="csrf_token"]');
        if (inp && inp.value) return inp.value;
        return <?= json_encode(csrf_token()) ?>;
    }

    // ── 1. Tab Switching with URL Sync ──
    const tabBtns = document.querySelectorAll('.hub-pill-btn');
    const tabPanes = document.querySelectorAll('.hub-tab-panel');

    function activateTab(tabName) {
        tabBtns.forEach(btn => {
            const isMatch = btn.getAttribute('data-tab-name') === tabName;
            btn.classList.toggle('active', isMatch);
            btn.setAttribute('aria-selected', isMatch ? 'true' : 'false');
        });
        tabPanes.forEach(pane => {
            pane.classList.toggle('active', pane.id === `tab-${tabName}`);
        });

        // Update URL query without page reload
        const url = new URL(window.location);
        url.searchParams.set('tab', tabName);
        window.history.replaceState({}, '', url);
    }

    tabBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const tabName = this.getAttribute('data-tab-name');
            activateTab(tabName);
        });
    });

    // ── 2. Dynamic Q&A vs Song field label toggle ──
    const reqTypeSelect = document.getElementById('req-type');
    const reqTitleLabel = document.getElementById('req-title-label');
    const reqTitleInput = document.getElementById('req-title');
    const reqArtistLabel = document.getElementById('req-artist-label');
    const reqArtistInput = document.getElementById('req-artist');

    if (reqTypeSelect && reqTitleLabel && reqTitleInput) {
        reqTypeSelect.addEventListener('change', function() {
            if (this.value === 'qa') {
                reqTitleLabel.innerHTML = 'Your Question <span class="hub-req">*</span>';
                reqTitleInput.placeholder = 'e.g. What inspired the creative direction for this tour?';
                if (reqArtistLabel) reqArtistLabel.innerHTML = 'Speaker / Panelist Name <span class="hub-opt">(Optional)</span>';
                if (reqArtistInput) reqArtistInput.placeholder = 'e.g. Main Speaker, Host, Keynote';
            } else {
                reqTitleLabel.innerHTML = 'Song Title <span class="hub-req">*</span>';
                reqTitleInput.placeholder = 'e.g. City Boys, Calm Down, Ojuelegba';
                if (reqArtistLabel) reqArtistLabel.innerHTML = 'Artist Name <span class="hub-opt">(Optional)</span>';
                if (reqArtistInput) reqArtistInput.placeholder = 'e.g. Burna Boy, Rema, Wizkid';
            }
        });
    }

    // ── 3. Hype Wall Feed Filter ──
    const filterBtns = document.querySelectorAll('.hub-filter-pill');
    const postCards = document.querySelectorAll('.hub-card-item');

    filterBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            filterBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            const f = this.getAttribute('data-filter');

            postCards.forEach(card => {
                if (f === 'all' || card.getAttribute('data-cat') === f) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    });

    // ── 4. Hype Wall Post Like / Unlike (AJAX Toggle) ──
    document.querySelectorAll('.hub-like-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            if (this.dataset.busy === '1') return;
            this.dataset.busy = '1';

            const id = this.getAttribute('data-id');
            const slug = this.getAttribute('data-slug');
            const countEl = this.querySelector('.like-count');
            const heartEl = this.querySelector('.hub-heart-icon');
            const token = getCsrfToken();

            fetch(`/event/${slug}/community/${id}/like`, {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': token
                },
                body: JSON.stringify({ csrf_token: token })
            })
            .then(r => r.json())
            .then(data => {
                this.dataset.busy = '0';
                if (data.success) {
                    if (countEl) countEl.textContent = data.likes;
                    if (data.liked) {
                        this.classList.add('liked');
                        if (heartEl) heartEl.textContent = '❤️';
                        this.title = 'Unlike this post';
                    } else {
                        this.classList.remove('liked');
                        if (heartEl) heartEl.textContent = '🤍';
                        this.title = 'Love this post';
                    }
                }
            })
            .catch(() => {
                this.dataset.busy = '0';
            });
        });
    });

    // ── 5. DJ Request Upvoting (AJAX Toggle & Switch) ──
    document.querySelectorAll('.req-upvote-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const slug = this.getAttribute('data-slug');
            const countEl = this.querySelector('.upvote-count');
            const token = getCsrfToken();

            fetch(`/event/${slug}/request/${id}/upvote`, {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': token
                },
                body: JSON.stringify({ csrf_token: token })
            })
            .then(r => r.json())
            .then(data => {
                if (!data.success) {
                    if (data.message) alert(data.message);
                    return;
                }

                if (data.action === 'unvoted') {
                    // Toggled off current vote
                    this.classList.remove('voted');
                    this.setAttribute('title', 'Upvote this track or question');
                    if (countEl) countEl.textContent = data.new_count;
                } else if (data.action === 'switched') {
                    // Remove active state and update count on previously voted item
                    if (data.switched_from) {
                        const oldBtn = document.querySelector(`.req-upvote-btn[data-id="${data.switched_from}"]`);
                        if (oldBtn) {
                            oldBtn.classList.remove('voted');
                            oldBtn.setAttribute('title', 'Upvote this track or question');
                            const oldCountEl = oldBtn.querySelector('.upvote-count');
                            if (oldCountEl) oldCountEl.textContent = data.old_count;
                        }
                    }
                    // Activate this new item
                    this.classList.add('voted');
                    this.setAttribute('title', 'Click to remove your vote');
                    if (countEl) countEl.textContent = data.new_count;
                } else if (data.action === 'voted') {
                    // Fresh vote cast
                    this.classList.add('voted');
                    this.setAttribute('title', 'Click to remove your vote');
                    if (countEl) countEl.textContent = data.new_count;
                }
            })
            .catch(err => {
                console.error('Upvote error:', err);
            });
        });
    });

    // ── 6. Request Submission (AJAX) ──
    const reqForm = document.getElementById('request-submit-form');
    if (reqForm) {
        reqForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const slug = <?= json_encode($eventSlug) ?>;
            const type = document.getElementById('req-type').value;
            const title = document.getElementById('req-title').value.trim();
            const artist = document.getElementById('req-artist').value.trim();
            const nameEl = document.getElementById('req-name');
            const name = nameEl ? nameEl.value.trim() : '';
            const token = getCsrfToken();

            fetch(`/event/${slug}/request`, {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': token
                },
                body: JSON.stringify({ 
                    csrf_token: token,
                    type, 
                    title, 
                    artist_or_speaker: artist, 
                    requester_name: name 
                })
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    location.reload();
                } else {
                    alert(data.message || 'Failed to submit request.');
                }
            });
        });
    }

    // ── 7. Trivia Engine ──
    const triviaBox = document.getElementById('trivia-container');
    if (triviaBox) {
        const triviaQuestions = <?= json_encode($trivia['questions'] ?? []) ?>;
        const triviaId = triviaBox.getAttribute('data-trivia-id');
        const slug = triviaBox.getAttribute('data-slug');
        const startBtn = document.getElementById('start-trivia-btn');
        const startScreen = document.getElementById('trivia-start-screen');
        const gameScreen = document.getElementById('trivia-game-screen');
        const resultsScreen = document.getElementById('trivia-results-screen');
        const qContent = document.getElementById('trivia-question-content');
        const qCounter = document.getElementById('trivia-q-counter');
        const nextBtn = document.getElementById('trivia-next-btn');
        const progressBar = document.getElementById('trivia-progress-bar');
        const timeLeftEl = document.getElementById('time-left');

        let currentQIndex = 0;
        let userAnswers = {};
        let timer = null;
        let secondsLeft = <?= (int)($trivia['time_limit_seconds'] ?? 60) ?>;

        if (startBtn) {
            startBtn.addEventListener('click', function() {
                const nameInp = document.getElementById('trivia-player-name');
                const emailInp = document.getElementById('trivia-player-email');
                if (nameInp && !nameInp.value.trim()) {
                    alert('Please enter your name to start the trivia.');
                    nameInp.focus();
                    return;
                }
                if (emailInp && !emailInp.value.trim()) {
                    alert('Please enter your email address so your winner promo discount can be verified at checkout.');
                    emailInp.focus();
                    return;
                }

                startScreen.style.display = 'none';
                gameScreen.style.display = 'block';
                renderQuestion(0);

                // Start countdown
                timer = setInterval(() => {
                    secondsLeft--;
                    if (timeLeftEl) timeLeftEl.textContent = secondsLeft;
                    if (secondsLeft <= 0) {
                        clearInterval(timer);
                        finishTrivia();
                    }
                }, 1000);
            });
        }

        function renderQuestion(idx) {
            if (idx >= triviaQuestions.length) {
                finishTrivia();
                return;
            }

            const q = triviaQuestions[idx];
            if (qCounter) qCounter.textContent = `Question ${idx + 1} of ${triviaQuestions.length}`;
            if (progressBar) progressBar.style.width = `${((idx) / triviaQuestions.length) * 100}%`;

            let html = `
                <h3 class="hub-trivia-question-text">
                    ${q.question}
                </h3>
                <div class="hub-trivia-options-box">
            `;

            ['A', 'B', 'C', 'D'].forEach(opt => {
                const optText = q[`option_${opt.toLowerCase()}`];
                if (optText) {
                    html += `
                        <label class="hub-trivia-option-label">
                            <input type="radio" name="trivia_choice" value="${opt}" style="display:none;">
                            <span class="hub-q-opt-letter">${opt}</span>
                            <span class="hub-q-opt-text">${optText}</span>
                        </label>
                    `;
                }
            });

            html += `</div>`;
            qContent.innerHTML = html;

            if (nextBtn) {
                nextBtn.disabled = true;
                nextBtn.querySelector('span').textContent = idx === triviaQuestions.length - 1 ? 'Finish Challenge ✓' : 'Next Question →';
            }

            document.querySelectorAll('input[name="trivia_choice"]').forEach(radio => {
                radio.addEventListener('change', function() {
                    userAnswers[q.id] = this.value;
                    document.querySelectorAll('.hub-trivia-option-label').forEach(lbl => lbl.style.borderColor = 'rgba(100, 116, 139, 0.2)');
                    this.closest('label').style.borderColor = '#6366f1';
                    if (nextBtn) nextBtn.disabled = false;
                });
            });
        }

        if (nextBtn) {
            nextBtn.addEventListener('click', function() {
                currentQIndex++;
                renderQuestion(currentQIndex);
            });
        }

        function finishTrivia() {
            if (timer) clearInterval(timer);
            gameScreen.style.display = 'none';
            resultsScreen.style.display = 'block';

            const nameInp = document.getElementById('trivia-player-name');
            const emailInp = document.getElementById('trivia-player-email');
            const name = nameInp ? nameInp.value.trim() : '';
            const email = emailInp ? emailInp.value.trim() : '';
            const token = getCsrfToken();

            fetch(`/event/${slug}/trivia/submit`, {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': token
                },
                body: JSON.stringify({
                    csrf_token: token,
                    trivia_id: triviaId,
                    answers: userAnswers,
                    name: name,
                    email: email,
                    time_taken: <?= (int)($trivia['time_limit_seconds'] ?? 60) ?> - secondsLeft
                })
            })
            .then(r => r.json())
            .then(data => {
                const badgeEl = document.getElementById('trivia-results-badge');
                const titleEl = document.getElementById('trivia-results-title');
                const scoreEl = document.getElementById('trivia-results-score');
                const rewardBox = document.getElementById('trivia-reward-box');
                const promoEl = document.getElementById('unlocked-promo-code');
                const rewardDescEl = document.getElementById('trivia-reward-desc');
                const claimBtn = document.getElementById('trivia-claim-btn');

                if (data.is_winner) {
                    badgeEl.innerHTML = '<span style="font-size:3.5rem;">🏆</span>';
                    titleEl.textContent = 'Challenge Won! Mastermind!';
                    titleEl.style.color = '#10b981';
                    scoreEl.textContent = `Score: ${data.score} / ${data.total} (Target: ${data.pass_score})`;

                    if (data.reward_promo_code) {
                        rewardBox.style.display = 'block';
                        promoEl.textContent = data.reward_promo_code;

                        if (rewardDescEl) {
                            const valText = data.reward_discount_type === 'percentage'
                                ? `${data.reward_discount_value}% Discount Unlocked!`
                                : `Discount Unlocked!`;
                            rewardDescEl.textContent = data.reward_description || valText;
                        }

                        if (claimBtn) {
                            claimBtn.href = `/checkout/<?= $event['id'] ?>?promo=${encodeURIComponent(data.reward_promo_code)}`;
                        }
                    }
                } else {
                    badgeEl.innerHTML = '<span style="font-size:3.5rem;">👏</span>';
                    titleEl.textContent = 'Good Effort! Almost Made It!';
                    titleEl.style.color = '#6366f1';
                    scoreEl.textContent = `Score: ${data.score} / ${data.total} (Needed ${data.pass_score} to unlock code)`;
                }
            });
        }

        // Robust Copy promo code with clipboard API & execCommand fallback
        const copyPromoBtn = document.getElementById('copy-promo-btn');
        if (copyPromoBtn) {
            copyPromoBtn.addEventListener('click', function(e) {
                e.preventDefault();
                const codeEl = document.getElementById('unlocked-promo-code');
                const code = codeEl ? codeEl.textContent.trim() : '';
                if (!code) return;

                const notifySuccess = () => {
                    const origHtml = copyPromoBtn.innerHTML;
                    copyPromoBtn.innerHTML = '✓ Copied!';
                    copyPromoBtn.style.background = '#10b981';
                    copyPromoBtn.style.borderColor = '#10b981';
                    copyPromoBtn.style.color = '#ffffff';
                    setTimeout(() => {
                        copyPromoBtn.innerHTML = origHtml;
                        copyPromoBtn.style.background = '';
                        copyPromoBtn.style.borderColor = '';
                        copyPromoBtn.style.color = '';
                    }, 2500);
                };

                if (navigator.clipboard && window.isSecureContext) {
                    navigator.clipboard.writeText(code).then(notifySuccess).catch(() => {
                        fallbackCopy(code, notifySuccess);
                    });
                } else {
                    fallbackCopy(code, notifySuccess);
                }
            });
        }

        function fallbackCopy(text, onSuccess) {
            try {
                const ta = document.createElement('textarea');
                ta.value = text;
                ta.setAttribute('readonly', '');
                ta.style.position = 'fixed';
                ta.style.left = '-9999px';
                ta.style.top = '0';
                document.body.appendChild(ta);
                ta.focus();
                ta.select();
                const successful = document.execCommand('copy');
                document.body.removeChild(ta);
                if (successful && onSuccess) {
                    onSuccess();
                    return;
                }
            } catch (err) {
                console.warn('Fallback copy failed', err);
            }
            prompt('Copy your winning promo code:', text);
        }
    }

    // ══════════════════════════════════════════════════════════════════
    // ⚡ 8. POWER VOTE ENGINE
    // ══════════════════════════════════════════════════════════════════
    document.querySelectorAll('.pv-section').forEach(section => {
        const pollId = section.getAttribute('data-poll-id');
        const voteCost = parseFloat(section.getAttribute('data-vote-cost')) || 100;
        const slug = <?= json_encode($eventSlug) ?>;

        const optionSelect = section.querySelector('.pv-option-select');
        const packBtns = section.querySelectorAll('.pv-pack-btn');
        const customQtyInput = section.querySelector('.pv-custom-qty');
        const customTotalEl = section.querySelector('.pv-custom-total');
        const summaryBox = section.querySelector('.pv-summary');
        const sumNominee = section.querySelector('.pv-sum-nominee');
        const sumQty = section.querySelector('.pv-sum-qty');
        const sumTotal = section.querySelector('.pv-sum-total');
        const payBtn = section.querySelector('.pv-pay-btn');

        // Guest fields
        const guestNameInput = section.querySelector('.pv-voter-name');
        const guestEmailInput = section.querySelector('.pv-voter-email');

        let selectedOptionId = '';
        let selectedQty = 0;

        function formatMoney(n) {
            return '₦' + n.toLocaleString('en-NG');
        }

        function updateSummary() {
            const nomineeName = optionSelect ? optionSelect.options[optionSelect.selectedIndex]?.text : '—';
            selectedOptionId = optionSelect ? optionSelect.value : '';

            if (selectedOptionId && selectedQty > 0) {
                summaryBox.style.display = 'block';
                sumNominee.textContent = nomineeName;
                sumQty.textContent = selectedQty;
                sumTotal.textContent = formatMoney(selectedQty * voteCost);
                payBtn.disabled = false;
            } else {
                summaryBox.style.display = 'none';
                payBtn.disabled = true;
            }
        }

        // Option select
        if (optionSelect) {
            optionSelect.addEventListener('change', updateSummary);
        }

        // Pack buttons
        packBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                packBtns.forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                selectedQty = parseInt(this.getAttribute('data-qty')) || 0;
                if (customQtyInput) customQtyInput.value = '';
                updateSummary();
            });
        });

        // Custom quantity
        if (customQtyInput) {
            customQtyInput.addEventListener('input', function() {
                const val = parseInt(this.value) || 0;
                selectedQty = Math.max(0, Math.min(10000, val));
                packBtns.forEach(b => b.classList.remove('active'));
                if (customTotalEl) {
                    customTotalEl.textContent = val > 0 ? '= ' + formatMoney(val * voteCost) : '= ₦0';
                }
                updateSummary();
            });
        }

        // Pay button — initialize Paystack
        if (payBtn) {
            payBtn.addEventListener('click', function() {
                if (!selectedOptionId) {
                    alert('Please select a nominee first.');
                    return;
                }
                if (selectedQty < 1) {
                    alert('Please choose a vote pack or enter a custom quantity.');
                    return;
                }

                const voterName = guestNameInput ? guestNameInput.value.trim() : '';
                const voterEmail = guestEmailInput ? guestEmailInput.value.trim() : '';

                // For guest users, require email
                if (guestEmailInput && !voterEmail) {
                    alert('Please enter your email address for the payment receipt.');
                    guestEmailInput.focus();
                    return;
                }

                payBtn.classList.add('pv-pay-loading');
                payBtn.textContent = '⏳ Initializing Payment...';

                const token = getCsrfToken();

                fetch(`/event/${slug}/vote/${pollId}/power/init`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': token
                    },
                    body: JSON.stringify({
                        csrf_token: token,
                        option_id: parseInt(selectedOptionId),
                        quantity: selectedQty,
                        voter_name: voterName,
                        voter_email: voterEmail
                    })
                })
                .then(r => r.json())
                .then(data => {
                    if (!data.success) {
                        alert(data.message || 'Failed to initialize payment.');
                        payBtn.classList.remove('pv-pay-loading');
                        payBtn.textContent = '⚡ Pay & Cast Power Votes';
                        return;
                    }

                    // Try Paystack Inline popup first
                    if (typeof PaystackPop !== 'undefined' && data.public_key && data.access_code) {
                        const handler = PaystackPop.setup({
                            key: data.public_key,
                            email: data.voter_email,
                            amount: data.amount_kobo,
                            ref: data.reference,
                            currency: 'NGN',
                            metadata: {
                                type: 'power_vote',
                                poll_id: pollId,
                                option_id: selectedOptionId,
                                quantity: selectedQty
                            },
                            callback: function(response) {
                                // Verify inline
                                payBtn.textContent = '⏳ Verifying Payment...';
                                fetch(`/event/${slug}/vote/power/verify`, {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-Requested-With': 'XMLHttpRequest',
                                        'X-CSRF-TOKEN': getCsrfToken()
                                    },
                                    body: JSON.stringify({
                                        csrf_token: getCsrfToken(),
                                        reference: response.reference || data.reference
                                    })
                                })
                                .then(r => r.json())
                                .then(result => {
                                    if (result.success) {
                                        alert(result.message || '⚡ Power Votes cast successfully!');
                                        location.reload();
                                    } else {
                                        alert(result.message || 'Verification failed. Contact support with ref: ' + data.reference);
                                        payBtn.classList.remove('pv-pay-loading');
                                        payBtn.textContent = '⚡ Pay & Cast Power Votes';
                                    }
                                });
                            },
                            onClose: function() {
                                payBtn.classList.remove('pv-pay-loading');
                                payBtn.textContent = '⚡ Pay & Cast Power Votes';
                            }
                        });
                        handler.openIframe();
                    } else if (data.authorization_url) {
                        // Fallback: redirect to Paystack checkout
                        window.location.href = data.authorization_url;
                    } else {
                        alert('Payment gateway unavailable. Please try again.');
                        payBtn.classList.remove('pv-pay-loading');
                        payBtn.textContent = '⚡ Pay & Cast Power Votes';
                    }
                })
                .catch(err => {
                    console.error('Power Vote error:', err);
                    alert('Network error. Please check your connection and try again.');
                    payBtn.classList.remove('pv-pay-loading');
                    payBtn.textContent = '⚡ Pay & Cast Power Votes';
                });
            });
        }
    });
});
</script>
@endsection
