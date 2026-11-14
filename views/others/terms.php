<?php

/**
 * Terms of Service — Archival Legal Gazette
 *
 * Vintage gazette & legal codex aesthetic: editorial serif typography,
 * parchment index navigation, tactile summary ledger, and responsive layout.
 *
 * @package Eventlyy
 * @subpackage Views/Terms
 * @author Celio Natti
 * @version 4.0.0
 */

$siteName = $siteName ?? site_setting('site_title', 'Eventlyy');
$supportEmail = $supportEmail ?? site_setting('support_email', 'support@eventlyy.com');
?>

@extends('layouts/main')

@section('title')
Terms of Service &amp; Community Guidelines &bull; <?= e($siteName) ?>
@endsection

@section('content')
<div class="vn-lg-wrapper">

    <!-- ═══ Hero Header Stage ═══ -->
    <header class="vn-lg-hero">
        <div class="vn-lg-top-bar">
            <nav class="vn-lg-breadcrumb" aria-label="Breadcrumb">
                <a href="{{ route('/') }}">Home</a>
                <span>&bull;</span>
                <span>Platform Guidelines</span>
                <span>&bull;</span>
                <span>Terms of Service</span>
            </nav>

            <div class="vn-lg-tabs">
                <a href="{{ route('terms') }}" class="vn-lg-tab active">Terms of Service</a>
                <a href="{{ route('privacy') }}" class="vn-lg-tab">Privacy Policy</a>
            </div>
        </div>

        <span class="vn-lg-pre">
            <span>✦</span> PLATFORM RULES &bull; NOVEMBER 2026 &bull; NO SURPRISES
        </span>

        <h1 class="vn-lg-title">Terms of Service &amp; Community Guidelines</h1>

        <div class="vn-lg-meta">
            <span class="vn-lg-meta-item">📅 Last refreshed: November 14, 2026</span>
            <span class="vn-lg-meta-item">&bull;</span>
            <span class="vn-lg-meta-item">⏱️ ~8 min read</span>
            <span class="vn-lg-meta-item">&bull;</span>
            <span class="vn-lg-meta-item">🌐 <?= e($siteName) ?> Community</span>
        </div>

        <!-- Plain English Summary Ledger -->
        <div class="vn-lg-summary">
            <div class="vn-lg-summary-head">
                <span>⚡</span> The Real-Talk TL;DR (No Legal Jargon)
            </div>
            <div class="vn-lg-summary-grid">
                <div class="vn-lg-summary-card">
                    <strong>🎟️ One Ticket = One Entry</strong>
                    <span>Every QR ticket is unique and admits exactly the number of people printed on it. Screenshotting someone else's code or reselling above face value = instant void.</span>
                </div>
                <div class="vn-lg-summary-card">
                    <strong>💰 Layaway? Finish Before the Event</strong>
                    <span>You can pay for tickets in installments (layaway). But if you haven't paid in full before the event starts, no refund — your payments become event credits for a future event.</span>
                </div>
                <div class="vn-lg-summary-card">
                    <strong>🛡️ We're the Tech, Not the Venue</strong>
                    <span><?= e($siteName) ?> powers the ticketing. Hosts run the show — they handle the venue, the vibes, the safety, and their own refund policies.</span>
                </div>
            </div>
        </div>
    </header>

    <!-- ═══ Two-Column Codex Layout ═══ -->
    <div class="vn-lg-layout">

        <!-- Sticky Table of Contents -->
        <aside class="vn-lg-toc">
            <div class="vn-lg-toc-title">
                <span>Jump To Section</span>
                <span style="font-size: 0.85rem; color: var(--vn-brass); font-family: var(--vn-font-serif); font-style: italic;">§ 1–18</span>
            </div>
            <ul class="vn-lg-toc-list">
                <li><a href="#section-1" class="vn-lg-toc-link">§ 1. Agreement to Terms</a></li>
                <li><a href="#section-2" class="vn-lg-toc-link">§ 2. Platform Role</a></li>
                <li><a href="#section-3" class="vn-lg-toc-link">§ 3. Your Account</a></li>
                <li><a href="#section-4" class="vn-lg-toc-link">§ 4. Hosting Events</a></li>
                <li><a href="#section-5" class="vn-lg-toc-link">§ 5. Tickets &amp; QR Codes</a></li>
                <li><a href="#section-6" class="vn-lg-toc-link">§ 6. Payments &amp; Fees</a></li>
                <li><a href="#section-7" class="vn-lg-toc-link">§ 7. Layaway (Pay Bit by Bit)</a></li>
                <li><a href="#section-8" class="vn-lg-toc-link">§ 8. Refunds &amp; Cancellations</a></li>
                <li><a href="#section-9" class="vn-lg-toc-link">§ 9. Ticket Transfers</a></li>
                <li><a href="#section-10" class="vn-lg-toc-link">§ 10. Promo Codes &amp; Referrals</a></li>
                <li><a href="#section-11" class="vn-lg-toc-link">§ 11. Host Payouts</a></li>
                <li><a href="#section-12" class="vn-lg-toc-link">§ 12. Prohibited Content</a></li>
                <li><a href="#section-13" class="vn-lg-toc-link">§ 13. Intellectual Property</a></li>
                <li><a href="#section-14" class="vn-lg-toc-link">§ 14. Limitation of Liability</a></li>
                <li><a href="#section-15" class="vn-lg-toc-link">§ 15. Indemnification</a></li>
                <li><a href="#section-16" class="vn-lg-toc-link">§ 16. Force Majeure</a></li>
                <li><a href="#section-17" class="vn-lg-toc-link">§ 17. Governing Law (Nigeria)</a></li>
                <li><a href="#section-18" class="vn-lg-toc-link">§ 18. Account Termination</a></li>
                <li><a href="#section-19" class="vn-lg-toc-link">§ 19. Changes to These Terms</a></li>
                <li><a href="#section-20" class="vn-lg-toc-link">§ 20. Contact Us</a></li>
            </ul>
        </aside>

        <!-- Document Content Codex -->
        <main class="vn-lg-document">

            <!-- § 1 — Agreement -->
            <section class="vn-lg-section" id="section-1">
                <h2 class="vn-lg-section-heading"><span class="vn-lg-section-num">§ 1.</span> Agreement to Terms</h2>
                <p class="vn-lg-text">
                    By using <strong><?= e($siteName) ?></strong> — whether you're browsing events, buying tickets, hosting a party, or just checking things out — you're agreeing to these Terms of Service. They form a legally binding agreement between you and <?= e($siteName) ?>.
                </p>
                <p class="vn-lg-text">
                    If you don't agree with anything here, that's totally fine — but please don't use the platform. By continuing to use <?= e($siteName) ?>, you confirm that you're at least 16 years old (or have a parent/guardian's permission) and that you have the legal capacity to enter into this agreement.
                </p>
            </section>

            <!-- § 2 — Platform Role -->
            <section class="vn-lg-section" id="section-2">
                <h2 class="vn-lg-section-heading"><span class="vn-lg-section-num">§ 2.</span> Platform Role &amp; What We Actually Do</h2>
                <p class="vn-lg-text">
                    Let's be super clear about this: <strong><?= e($siteName) ?> is a technology platform</strong>. We connect event hosts ("Hosts") with people who want to attend events ("Attendees"). We handle the ticketing tech, payment processing, and digital infrastructure. That's our lane.
                </p>
                <p class="vn-lg-text">
                    Here's what we are <strong>NOT</strong>:
                </p>
                <ul class="vn-lg-list">
                    <li>We are <strong>not</strong> the event organizer, promoter, or venue owner for third-party events listed on our platform.</li>
                    <li>We are <strong>not</strong> responsible for the quality of any event — the vibes, the sound, the food, the safety, the timing, none of it.</li>
                    <li>We are <strong>not</strong> liable for anything that happens at the physical venue. That's between you and the host.</li>
                </ul>
                <p class="vn-lg-text">
                    Think of us like the middleman tech — we built the bridge, but the host owns the destination.
                </p>
            </section>

            <!-- § 3 — Accounts -->
            <section class="vn-lg-section" id="section-3">
                <h2 class="vn-lg-section-heading"><span class="vn-lg-section-num">§ 3.</span> Your Account</h2>
                <p class="vn-lg-text">
                    When you create an account on <?= e($siteName) ?>, here's the deal:
                </p>
                <ul class="vn-lg-list">
                    <li><strong>Keep it real:</strong> Use accurate, up-to-date info (name, email, phone). Fake accounts get removed.</li>
                    <li><strong>Your login = your responsibility:</strong> Don't share your password. Everything that happens under your account is on you.</li>
                    <li><strong>Verify your email:</strong> We send a verification link when you sign up. Until you verify, some features may be restricted.</li>
                    <li><strong>One person, one account:</strong> Don't create multiple accounts to game the system.</li>
                </ul>
                <p class="vn-lg-text">
                    If you think someone got into your account without permission, hit us up at <strong><?= e($supportEmail) ?></strong> immediately.
                </p>
            </section>

            <!-- § 4 — Hosting Events -->
            <section class="vn-lg-section" id="section-4">
                <h2 class="vn-lg-section-heading"><span class="vn-lg-section-num">§ 4.</span> Hosting Events</h2>
                <p class="vn-lg-text">
                    If you're creating and publishing events on <?= e($siteName) ?>, you're a "Host." Here's what that means:
                </p>
                <ul class="vn-lg-list">
                    <li><strong>You own your event:</strong> You're fully responsible for your event — the content, the venue, the safety, the experience, everything.</li>
                    <li><strong>Accurate listings only:</strong> Event details (date, time, location, description, pricing) must be truthful. Misleading info = your event gets taken down.</li>
                    <li><strong>Recurring events:</strong> If you set up recurring events, each occurrence is treated as its own event for ticketing and payment purposes.</li>
                    <li><strong>Compliance is on you:</strong> Make sure your event complies with all applicable Nigerian laws, local government regulations, and venue requirements.</li>
                    <li><strong>Bank details:</strong> To receive payouts, you need to provide valid bank account details (NUBAN). Wrong details = delayed payments, and that's on you.</li>
                </ul>
            </section>

            <!-- § 5 — Tickets & QR Codes -->
            <section class="vn-lg-section" id="section-5">
                <h2 class="vn-lg-section-heading"><span class="vn-lg-section-num">§ 5.</span> Tickets &amp; QR Codes</h2>
                <p class="vn-lg-text">
                    Every ticket purchased on <?= e($siteName) ?> comes with a unique QR code. Here's how it works:
                </p>
                <ul class="vn-lg-list">
                    <li><strong>One scan, one entry:</strong> Each QR code is unique. Once it's scanned at the door, it's marked as used — permanently. Group tickets admit the number of people specified on the ticket.</li>
                    <li><strong>No scalping:</strong> Reselling tickets above face value or through unauthorized channels is not allowed and will void the ticket.</li>
                    <li><strong>Host's call at the gate:</strong> The event host (or their door team) has the final say on entry. Invalid, tampered, or already-scanned tickets will be denied.</li>
                </ul>

                <div class="vn-lg-callout vn-lg-callout--amber">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                        <line x1="12" y1="9" x2="12" y2="13"></line>
                        <line x1="12" y1="17" x2="12.01" y2="17"></line>
                    </svg>
                    <div>
                        <strong>Pro tip:</strong> Never post your QR code on social media or send it to someone you don't trust. If someone else screenshots your QR and gets scanned in first, they're in and you're out. Keep it locked. 🔒
                    </div>
                </div>
            </section>

            <!-- § 6 — Payments & Fees -->
            <section class="vn-lg-section" id="section-6">
                <h2 class="vn-lg-section-heading"><span class="vn-lg-section-num">§ 6.</span> Payments &amp; Fees</h2>
                <p class="vn-lg-text">
                    Here's how money works on <?= e($siteName) ?>:
                </p>
                <ul class="vn-lg-list">
                    <li><strong>Payment processing:</strong> All payments are handled securely through <strong>Paystack</strong> (PCI-DSS Level 1 certified). We never see or store your card details.</li>
                    <li><strong>Platform fee:</strong> We charge a percentage-based platform fee on each ticket sale. The exact rate is displayed at checkout.</li>
                    <li><strong>Service charge:</strong> A small flat service/processing charge may apply per transaction to cover infrastructure costs.</li>
                    <li><strong>Who pays the fee?</strong> The host chooses whether to absorb fees or pass them to the attendee. Either way, you'll see the full breakdown before you pay.</li>
                    <li><strong>Currency:</strong> Our primary currency is Nigerian Naira (₦/NGN). Some events may also support USD, EUR, or GBP at the host's discretion, using our configured exchange rates.</li>
                    <li><strong>Free events:</strong> Free tickets have no fees. Zero. Nada.</li>
                </ul>
            </section>

            <!-- § 7 — Layaway -->
            <section class="vn-lg-section" id="section-7">
                <h2 class="vn-lg-section-heading"><span class="vn-lg-section-num">§ 7.</span> Layaway — Pay Bit by Bit</h2>
                <p class="vn-lg-text">
                    We get it — not everyone can drop the full amount at once. That's why we offer <strong>Layaway</strong> (partial payments). Here's how it works and the rules you need to know:
                </p>
                <ul class="vn-lg-list">
                    <li><strong>How it works:</strong> Choose a ticket, pay a minimum deposit at checkout (including applicable fees), and make additional payments over time until you've paid the full ticket price.</li>
                    <li><strong>Your ticket is reserved:</strong> While you're paying it off, your ticket slot is held for you. But it's <strong>not active</strong> — you can't check in or use it until it's fully paid.</li>
                    <li><strong>No check-in until fully paid:</strong> Even if you show up at the venue, a layaway ticket that isn't fully paid will be <strong>denied at the door</strong>. No exceptions.</li>
                    <li><strong>No ticket transfer until fully paid:</strong> You can't transfer a layaway ticket to someone else until the balance is cleared.</li>
                </ul>

                <div class="vn-lg-callout vn-lg-callout--amber">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                        <line x1="12" y1="9" x2="12" y2="13"></line>
                        <line x1="12" y1="17" x2="12.01" y2="17"></line>
                    </svg>
                    <div>
                        <strong>Real talk — no refunds on layaway:</strong> If the event starts and you haven't finished paying, your layaway expires. <strong>You will NOT get a refund.</strong> Instead, the amount you've paid so far becomes <strong>event credit</strong> that you can use toward a future event on <?= e($siteName) ?>. Credits are non-transferable and non-cashable.
                    </div>
                </div>

                <p class="vn-lg-text">
                    <strong>Bottom line:</strong> Layaway is a convenience feature. It's on you to finish paying before the event. Set reminders, check your layaway dashboard, and stay on top of it. We'll send you email updates along the way, but the responsibility is yours.
                </p>
            </section>

            <!-- § 8 — Refunds & Cancellations -->
            <section class="vn-lg-section" id="section-8">
                <h2 class="vn-lg-section-heading"><span class="vn-lg-section-num">§ 8.</span> Refunds &amp; Cancellations</h2>
                <p class="vn-lg-text">
                    Refund rules are straightforward:
                </p>
                <ul class="vn-lg-list">
                    <li><strong>Host sets the refund policy:</strong> Each event host decides their own refund rules. Check the event page before buying.</li>
                    <li><strong>Platform fees are non-refundable:</strong> Even if you get a ticket refund, the platform fee and service charge we collected at checkout are not refundable. These cover processing costs that have already been incurred.</li>
                    <li><strong>Cancelled events:</strong> If a host cancels their event, the host is responsible for refunding attendees. <?= e($siteName) ?> may facilitate this but is not obligated to fund refunds from our own pocket.</li>
                    <li><strong>Layaway refunds:</strong> Incomplete layaway payments are <strong>never refunded</strong>. They convert to event credits (see § 7 above).</li>
                    <li><strong>Talk to us first:</strong> If you have a payment dispute, contact us at <strong><?= e($supportEmail) ?></strong> before filing a chargeback with your bank. Filing false or premature chargebacks may result in your account being suspended or terminated.</li>
                </ul>
            </section>

            <!-- § 9 — Ticket Transfers -->
            <section class="vn-lg-section" id="section-9">
                <h2 class="vn-lg-section-heading"><span class="vn-lg-section-num">§ 9.</span> Ticket Transfers</h2>
                <p class="vn-lg-text">
                    Can't make it to the event? You can transfer your ticket to someone else — but there are rules:
                </p>
                <ul class="vn-lg-list">
                    <li><strong>Fully paid tickets only:</strong> You can only transfer tickets that are fully paid. Layaway tickets still being paid off cannot be transferred.</li>
                    <li><strong>One transfer:</strong> Transfers are final. The new ticket holder becomes the rightful owner.</li>
                    <li><strong>Not a resale platform:</strong> Transfers are meant for genuine "can't attend" situations. Using this feature to run a resale operation is not allowed.</li>
                </ul>
            </section>

            <!-- § 10 — Promo Codes & Referrals -->
            <section class="vn-lg-section" id="section-10">
                <h2 class="vn-lg-section-heading"><span class="vn-lg-section-num">§ 10.</span> Promo Codes &amp; Referrals</h2>
                <p class="vn-lg-text">
                    Hosts can create promo codes (percentage or fixed discounts) for their events. Here's the fine print:
                </p>
                <ul class="vn-lg-list">
                    <li><strong>One code per checkout:</strong> You can only apply one promo code per transaction.</li>
                    <li><strong>Host's terms apply:</strong> Promo codes are created and managed by hosts. Expiry dates, usage limits, and eligible tickets are all set by the host.</li>
                    <li><strong>Non-stackable:</strong> Promo codes cannot be combined with other offers unless the host explicitly allows it.</li>
                </ul>
                <p class="vn-lg-text">
                    We also have a <strong>referral system</strong> — you can share referral links for events. If someone buys a ticket through your link, the host can track that referral. Referral rewards (if any) are determined entirely by the host, not by <?= e($siteName) ?>.
                </p>
            </section>

            <!-- § 11 — Host Payouts -->
            <section class="vn-lg-section" id="section-11">
                <h2 class="vn-lg-section-heading"><span class="vn-lg-section-num">§ 11.</span> Host Payouts</h2>
                <p class="vn-lg-text">
                    If you're a host receiving money through <?= e($siteName) ?>, here's what to know:
                </p>
                <ul class="vn-lg-list">
                    <li><strong>Accurate bank details:</strong> You must provide valid Nigerian bank account details (10-digit NUBAN) for payouts via Paystack. If your details are wrong or outdated, your payout will be delayed — and that's on you.</li>
                    <li><strong>Payout timing:</strong> Payouts are processed after your event concludes. We may hold funds temporarily if there are pending disputes, refund requests, or if the event is flagged for review.</li>
                    <li><strong>Platform deductions:</strong> Our platform fee is deducted from your ticket revenue before payout. You'll always see a clear breakdown in your host dashboard.</li>
                    <li><strong>Clawback rights:</strong> If an event is cancelled, flagged for fraud, or generates excessive chargebacks, we reserve the right to withhold, delay, or reverse your payout to cover attendee refunds and platform losses.</li>
                </ul>
            </section>

            <!-- § 12 — Prohibited Content -->
            <section class="vn-lg-section" id="section-12">
                <h2 class="vn-lg-section-heading"><span class="vn-lg-section-num">§ 12.</span> Prohibited Content &amp; Behavior</h2>
                <p class="vn-lg-text">
                    Keep it clean and legal. The following are not allowed on <?= e($siteName) ?>:
                </p>
                <ul class="vn-lg-list">
                    <li>Events involving illegal activities, fraud, scams, or Ponzi/pyramid schemes.</li>
                    <li>Hate speech, discrimination, harassment, or threats of violence.</li>
                    <li>Events that infringe on someone else's intellectual property, trademarks, or copyrights.</li>
                    <li>Unauthorized reselling or scalping of tickets.</li>
                    <li>Misleading event descriptions designed to deceive attendees.</li>
                    <li>Using bots, scripts, or automated tools to buy tickets in bulk or manipulate the platform.</li>
                    <li>Attempting to hack, exploit, or reverse-engineer any part of the platform.</li>
                </ul>
                <p class="vn-lg-text">
                    We reserve the right to remove any content or event listing, and suspend or terminate any account that violates these rules — without prior notice.
                </p>
            </section>

            <!-- § 13 — Intellectual Property -->
            <section class="vn-lg-section" id="section-13">
                <h2 class="vn-lg-section-heading"><span class="vn-lg-section-num">§ 13.</span> Intellectual Property</h2>
                <p class="vn-lg-text">
                    Everything that makes <?= e($siteName) ?> work — the code, the design, the logo, the brand name, the UI — is our intellectual property, protected under Nigerian copyright law and applicable international treaties.
                </p>
                <ul class="vn-lg-list">
                    <li><strong>Don't copy our stuff:</strong> You may not reproduce, modify, distribute, or reverse-engineer any part of the platform without written permission.</li>
                    <li><strong>Your content stays yours:</strong> Any content you upload (event descriptions, flyers, images) remains your property. But by uploading it, you grant us a non-exclusive, royalty-free license to display, distribute, and promote it within the platform.</li>
                    <li><strong>Takedown requests:</strong> If you believe content on <?= e($siteName) ?> infringes your IP rights, email us at <strong><?= e($supportEmail) ?></strong> with details and we'll investigate.</li>
                </ul>
            </section>

            <!-- § 14 — Limitation of Liability -->
            <section class="vn-lg-section" id="section-14">
                <h2 class="vn-lg-section-heading"><span class="vn-lg-section-num">§ 14.</span> Limitation of Liability</h2>
                <p class="vn-lg-text">
                    To the maximum extent allowed by Nigerian law:
                </p>
                <ul class="vn-lg-list">
                    <li><?= e($siteName) ?>, its founders, team members, and affiliates are <strong>not liable</strong> for any indirect, incidental, special, or consequential damages — including lost profits, data loss, personal injury at events, or missed experiences.</li>
                    <li>We provide the platform <strong>"as is"</strong> and <strong>"as available."</strong> We don't guarantee uninterrupted service, bug-free code, or that every event will be amazing.</li>
                    <li>If we are found liable for anything, our total liability is capped at the <strong>platform fees we actually collected</strong> from the specific transaction in question, or <strong>₦50,000</strong>, whichever is less.</li>
                </ul>
                <p class="vn-lg-text">
                    This isn't us being shady — it's standard practice. We build the tech; hosts run the events. Each party is responsible for their own lane.
                </p>
            </section>

            <!-- § 15 — Indemnification -->
            <section class="vn-lg-section" id="section-15">
                <h2 class="vn-lg-section-heading"><span class="vn-lg-section-num">§ 15.</span> Indemnification</h2>
                <p class="vn-lg-text">
                    You agree to defend, indemnify, and hold harmless <?= e($siteName) ?>, its team, and its affiliates from any claims, damages, losses, or legal fees arising from:
                </p>
                <ul class="vn-lg-list">
                    <li>Your use (or misuse) of the platform.</li>
                    <li>Any event you host on the platform.</li>
                    <li>Your violation of these Terms or any applicable law.</li>
                    <li>Any dispute between you and another user, host, or attendee.</li>
                </ul>
                <p class="vn-lg-text">
                    In plain English: if your actions cause us legal trouble, you're covering the bill.
                </p>
            </section>

            <!-- § 16 — Force Majeure -->
            <section class="vn-lg-section" id="section-16">
                <h2 class="vn-lg-section-heading"><span class="vn-lg-section-num">§ 16.</span> Force Majeure</h2>
                <p class="vn-lg-text">
                    We're not responsible for things genuinely beyond anyone's control — natural disasters, pandemics, government orders, internet outages, power failures, civil unrest, or acts of God. If something like this happens and it affects our service, we'll do our best to keep you informed, but we won't be held liable for the disruption.
                </p>
            </section>

            <!-- § 17 — Governing Law -->
            <section class="vn-lg-section" id="section-17">
                <h2 class="vn-lg-section-heading"><span class="vn-lg-section-num">§ 17.</span> Governing Law &amp; Jurisdiction</h2>
                <p class="vn-lg-text">
                    These Terms are governed by and interpreted under the <strong>laws of the Federal Republic of Nigeria</strong>, including but not limited to:
                </p>
                <ul class="vn-lg-list">
                    <li>The <strong>Nigeria Data Protection Act (NDPA) 2023</strong> and its implementing regulations.</li>
                    <li>The <strong>Federal Competition and Consumer Protection Act (FCCPA) 2018</strong>.</li>
                    <li>The <strong>Cybercrimes (Prohibition, Prevention, etc.) Act 2015</strong>.</li>
                    <li>Any other applicable federal or state legislation.</li>
                </ul>
                <p class="vn-lg-text">
                    Any disputes arising from these Terms shall first be resolved through good-faith negotiation. If that doesn't work, disputes shall be submitted to <strong>mediation or arbitration</strong> under the Arbitration and Mediation Act 2023, before resorting to litigation. Any legal proceedings shall be filed in the courts of competent jurisdiction in Nigeria.
                </p>
            </section>

            <!-- § 18 — Account Termination -->
            <section class="vn-lg-section" id="section-18">
                <h2 class="vn-lg-section-heading"><span class="vn-lg-section-num">§ 18.</span> Account Termination</h2>
                <p class="vn-lg-text">
                    We can suspend or terminate your account if:
                </p>
                <ul class="vn-lg-list">
                    <li>You violate these Terms of Service.</li>
                    <li>You engage in fraudulent activity or payment misconduct.</li>
                    <li>You receive excessive complaints from attendees or other users.</li>
                    <li>We're required to do so by law or a court order.</li>
                    <li>Your account has been inactive for an extended period.</li>
                </ul>
                <p class="vn-lg-text">
                    We'll try to give you a heads-up when possible, but in cases of fraud or serious violations, we may act immediately without notice. If your account is terminated, any pending layaway payments convert to event credits (subject to § 7), and pending host payouts may be held pending review.
                </p>
                <p class="vn-lg-text">
                    You can also delete your own account at any time by contacting us at <strong><?= e($supportEmail) ?></strong>.
                </p>
            </section>

            <!-- § 19 — Changes to Terms -->
            <section class="vn-lg-section" id="section-19">
                <h2 class="vn-lg-section-heading"><span class="vn-lg-section-num">§ 19.</span> Changes to These Terms</h2>
                <p class="vn-lg-text">
                    We may update these Terms from time to time — to reflect new features, legal requirements, or just to make things clearer. When we make significant changes:
                </p>
                <ul class="vn-lg-list">
                    <li>We'll update the "Last refreshed" date at the top of this page.</li>
                    <li>For major changes, we may notify you by email or through a banner on the platform.</li>
                    <li>Your continued use of <?= e($siteName) ?> after changes are posted means you accept the updated Terms.</li>
                </ul>
                <p class="vn-lg-text">
                    We recommend checking back here occasionally. No one loves reading legal docs, but staying informed is always a W. ✌️
                </p>
            </section>

            <!-- § 20 — Contact -->
            <section class="vn-lg-section" id="section-20">
                <h2 class="vn-lg-section-heading"><span class="vn-lg-section-num">§ 20.</span> Contact Us</h2>
                <p class="vn-lg-text">
                    Got questions, concerns, or just want to say hi? Reach out to our team:
                </p>

                <div class="vn-lg-contact">
                    <div class="vn-lg-contact-info">
                        <strong><?= e($siteName) ?> Legal &amp; Support Team</strong>
                        <span>Email: <?= e($supportEmail) ?></span>
                    </div>
                    <a href="mailto:<?= e($supportEmail) ?>" class="vn-lg-contact-btn">
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                            <polyline points="22,6 12,13 2,6"></polyline>
                        </svg>
                        Send Us a Message
                    </a>
                </div>
            </section>

        </main>
    </div>
</div>
@endsection

@section('script')
<script>
// Archival TOC Scrollspy
document.addEventListener('DOMContentLoaded', function() {
    const links = document.querySelectorAll('.vn-lg-toc-link');
    const sections = document.querySelectorAll('.vn-lg-section');

    function updateActiveToc() {
        let current = '';
        sections.forEach(sec => {
            const top = sec.offsetTop - 120;
            if (window.pageYOffset >= top) {
                current = sec.getAttribute('id');
            }
        });

        links.forEach(l => {
            l.classList.remove('active');
            if (l.getAttribute('href') === '#' + current) {
                l.classList.add('active');
            }
        });
    }

    window.addEventListener('scroll', updateActiveToc, { passive: true });
    updateActiveToc();
});
</script>
@endsection
