<?php

/**
 * About Page — Platform Story & Creator Ethos
 *
 * Vintage gazette & heritage aesthetic: letterpress typography,
 * antique parchment card structures, brass-burnished accents,
 * paired with a fresh, modern cultural flow.
 *
 * @package Eventlyy
 * @subpackage Views/About
 * @author Celio Natti
 * @version 4.1.0
 */

$siteTitle = site_setting('site_title', 'Eventlyy');
$siteDesc  = site_setting('site_description', 'A modern ticketing and event management ecosystem designed to help hosts and attendees connect seamlessly.');
$siteLogo  = site_setting('site_logo');
?>

@extends('layouts/main')

@section('title')
About <?= e($siteTitle) ?> — Real Moments. Unforgettable Drops.
@endsection

@section('content')
<div class="vn-ab-wrapper">

    <!-- ═══ Archival Gazette Hero Stage ═══ -->
    <header class="vn-ab-hero">
        <div class="vn-ab-masthead">
            <span class="vn-ab-masthead-vol">
                <span>✦</span> ISSUE NO. 26 &bull; THE CULTURE GAZETTE &bull; REAL EXPERIENCES ONLY
            </span>
            <span class="vn-ab-masthead-stamp">
                <span>EST. 2024</span> &bull; VERIFIED TICKETING
            </span>
        </div>

        <div class="vn-ab-hero-grid">
            <div class="vn-ab-hero-text">
                <span class="vn-ab-chapter">Drop 01 &mdash; Why We're Here</span>

                <h1 class="vn-ab-title">
                    Less Screen Time. <em>More Core Memories.</em>
                </h1>

                <p class="vn-ab-lead">
                    <span class="vn-ab-dropcap">W</span>e believe the most unforgettable moments happen IRL &mdash; front row at a concert, inside a secret DJ warehouse set, or vibing at a creative pop-up with your people. 
                    <strong><?= e($siteTitle) ?></strong> was built because locking in tickets shouldn't feel like a scam or an obstacle course. No hidden fees at checkout, zero gate drama &mdash; just instant passes on lock and direct payouts for creators.
                </p>

                <div class="vn-ab-seals">
                    <span class="vn-ab-seal">
                        <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                        Instant QR Passes On Lock
                    </span>
                    <span class="vn-ab-seal">
                        <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                        Direct &amp; Fast Host Payouts
                    </span>
                    <span class="vn-ab-seal">
                        <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                        100% Legit Drops &amp; Safe Checkout
                    </span>
                </div>
            </div>

            <!-- Archival Portrait Frame -->
            <div class="vn-ab-media">
                <div class="vn-ab-frame">
                    <div class="vn-ab-frame-inner">
                        <img src="{{ asset('img/evently_hero_graphic.png') }}" 
                             alt="<?= e($siteTitle) ?> Community In Action" 
                             class="vn-ab-frame-img"
                             loading="lazy">
                        <div class="vn-ab-frame-caption">
                            Fig. 1 &mdash; Pulling up to the moments that hit different
                        </div>
                    </div>
                    <div class="vn-ab-wax-stamp">
                        <span>★</span> 4.9 / 5.0 Vibe Rating
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- ═══ 3-Step Archival "Chronicle of Admission" ═══ -->
    <section class="vn-ab-steps-section">
        <div class="vn-ab-head">
            <span class="vn-ab-pre">The Blueprint</span>
            <h2 class="vn-ab-head-title">How <?= e($siteTitle) ?> Moves</h2>
            <div class="vn-ab-ornament">✦ ✦ ✦</div>
            <p class="vn-ab-head-desc">From discovering your next obsession to door check-in, here is how we make the whole experience effortless.</p>
        </div>

        <div class="vn-ab-steps-grid">
            <div class="vn-ab-ticket-card">
                <span class="vn-ab-ticket-num">I</span>
                <div class="vn-ab-ticket-icon">
                    <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                </div>
                <h3 class="vn-ab-ticket-title">Find Your Move</h3>
                <p class="vn-ab-ticket-desc">
                    Explore curated raves, intimate creative panels, food festivals, and summits &mdash; or publish your own branded drop in under three minutes.
                </p>
            </div>

            <div class="vn-ab-ticket-card">
                <span class="vn-ab-ticket-num">II</span>
                <div class="vn-ab-ticket-icon">
                    <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="2" y="6" width="20" height="12" rx="2"></rect>
                        <circle cx="12" cy="12" r="2"></circle>
                        <path d="M6 12h.01M18 12h.01"></path>
                    </svg>
                </div>
                <h3 class="vn-ab-ticket-title">Lock In Your Pass</h3>
                <p class="vn-ab-ticket-desc">
                    Grab your tickets with zero friction. Instant encrypted QR pass delivered straight to your wallet, email, and ready for offline screenshotting.
                </p>
            </div>

            <div class="vn-ab-ticket-card">
                <span class="vn-ab-ticket-num">III</span>
                <div class="vn-ab-ticket-icon">
                    <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                </div>
                <h3 class="vn-ab-ticket-title">Pull Up &amp; Vibe</h3>
                <p class="vn-ab-ticket-desc">
                    Door security verifies entry with sub-second camera scans and real-time fraud deterrence. No paper tickets, no line holdups.
                </p>
            </div>
        </div>
    </section>

    <!-- ═══ Core Principles (Archival Ledger Cards) ═══ -->
    <section class="vn-ab-values-section">
        <div class="vn-ab-head">
            <span class="vn-ab-pre">Our Standards</span>
            <h2 class="vn-ab-head-title">The Principles We Stand On</h2>
            <div class="vn-ab-ornament">✦ ✦ ✦</div>
            <p class="vn-ab-head-desc">We build tools designed with real respect for creators, fans, and community culture.</p>
        </div>

        <div class="vn-ab-values-grid">
            <div class="vn-ab-value-card">
                <div class="vn-ab-value-icon vn-ab-value-icon--brass">
                    <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                    </svg>
                </div>
                <h3 class="vn-ab-value-title">Zero Fake Passes</h3>
                <p class="vn-ab-value-desc">
                    Every ticket is cryptographically verified and backed by Paystack merchant protection. Scalpers and duplicate screenshots get turned away at the door.
                </p>
            </div>

            <div class="vn-ab-value-card">
                <div class="vn-ab-value-icon vn-ab-value-icon--forest">
                    <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <path d="M8 14s1.5 2 4 2 4-2 4-2"></path>
                        <line x1="9" y1="9" x2="9.01" y2="9"></line>
                        <line x1="15" y1="9" x2="15.01" y2="9"></line>
                    </svg>
                </div>
                <h3 class="vn-ab-value-title">Built for the Culture</h3>
                <p class="vn-ab-value-desc">
                    Speedy single-tap checkout without bloated account walls. Easy ticket transfers to your group chat, calendar sync, and offline Apple/Google-ready cards.
                </p>
            </div>

            <div class="vn-ab-value-card">
                <div class="vn-ab-value-icon vn-ab-value-icon--ink">
                    <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polygon points="12 2 2 7 12 12 22 7 12 2"></polygon>
                    </svg>
                </div>
                <h3 class="vn-ab-value-title">Creators Get Paid</h3>
                <p class="vn-ab-value-desc">
                    Total control over ticket tiers, real-time sales velocity graphs, trusted door scanner roles, and fast payouts sent directly to your bank account.
                </p>
            </div>
        </div>
    </section>

    <!-- ═══ Archival Ledger Metrics Ribbon ═══ -->
    <section class="vn-ab-stats-section">
        <div class="vn-ab-ledger">
            <div class="vn-ab-ledger-grid">
                <div class="vn-ab-ledger-item">
                    <span class="vn-ab-ledger-val">50,000+</span>
                    <span class="vn-ab-ledger-label">Passes Secured</span>
                </div>
                <div class="vn-ab-ledger-item">
                    <span class="vn-ab-ledger-val">99.9%</span>
                    <span class="vn-ab-ledger-label">Gate Uptime</span>
                </div>
                <div class="vn-ab-ledger-item">
                    <span class="vn-ab-ledger-val">4.9 ★</span>
                    <span class="vn-ab-ledger-label">Host Satisfaction</span>
                </div>
                <div class="vn-ab-ledger-item">
                    <span class="vn-ab-ledger-val">&lt; 2.5s</span>
                    <span class="vn-ab-ledger-label">Door Scan Clearance</span>
                </div>
            </div>
        </div>
    </section>

    <!-- ═══ Grand Invitation (Call to Action) ═══ -->
    <section class="vn-ab-cta">
        <div class="vn-ab-cta-inner">
            <span class="vn-ab-pre">✦ The Move Is Yours ✦</span>
            <h2 class="vn-ab-cta-title">Ready to Host Something Unforgettable?</h2>
            <p class="vn-ab-cta-desc">
                Join forward-thinking creators, DJs, organizers, and thousands of attendees who trust <strong><?= e($siteTitle) ?></strong> for tickets, admissions, and verified door check-ins.
            </p>

            <div class="vn-ab-cta-actions">
                <?php if (app()->user && app()->user->isHost()): ?>
                    <a href="{{ route('host/event/create') }}" class="vn-btn-brass">
                        Drop an Event &rarr;
                    </a>
                <?php else: ?>
                    <a href="{{ route('register') }}" class="vn-btn-brass">
                        Start Hosting for Free &rarr;
                    </a>
                <?php endif; ?>
                <a href="{{ route('/') }}" class="vn-btn-outline">
                    Explore What's Live
                </a>
            </div>
        </div>
    </section>

</div>
@endsection
