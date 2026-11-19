<?php

/**
 * Platform Guide & Knowledge Base — Archival Gazette Edition
 *
 * Comprehensive documentation and guide covering all features and functions
 * for Attendees, Event Hosts, On-Site Staff, and Platform Administrators.
 *
 * @package Eventlyy
 * @subpackage Views/Guide
 * @author Celio Natti
 * @version 1.0.0
 */

$siteName = $siteName ?? site_setting('site_title', 'Eventlyy');
$supportEmail = $supportEmail ?? site_setting('support_email', 'support@eventlyy.com');
?>

@extends('layouts/main')

@section('title')
Platform Guide &amp; Knowledge Base &bull; <?= e($siteName) ?>
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
                <span>Platform Guide</span>
            </nav>

            <div class="vn-lg-tabs">
                <a href="{{ route('terms') }}" class="vn-lg-tab">Terms of Service</a>
                <a href="{{ route('privacy') }}" class="vn-lg-tab">Privacy Policy</a>
                <a href="{{ route('guide') }}" class="vn-lg-tab active">Platform Guide</a>
            </div>
        </div>

        <span class="vn-lg-pre">
            <span>✦</span> COMPLETE GUIDE &bull; NOVEMBER 2026 &bull; EVERY FEATURE EXPLAINED
        </span>

        <h1 class="vn-lg-title">The Complete <?= e($siteName) ?> Platform Guide</h1>

        <div class="vn-lg-meta">
            <span class="vn-lg-meta-item">📅 Last refreshed: November 15, 2026</span>
            <span class="vn-lg-meta-sep">&bull;</span>
            <span class="vn-lg-meta-item">⏱️ ~15 min read</span>
            <span class="vn-lg-meta-sep">&bull;</span>
            <span class="vn-lg-meta-item">🌐 <?= e($siteName) ?> Master Handbook</span>
        </div>

        <!-- Plain English Summary Ledger -->
        <div class="vn-lg-summary">
            <div class="vn-lg-summary-head">
                <span>📖</span> Quick Start: Choose Your Role
            </div>
            <div class="vn-lg-summary-grid">
                <div class="vn-lg-summary-card">
                    <strong>🎟️ For Attendees</strong>
                    <span>Discover events, buy tickets with ease, receive QR passes instantly via Email &amp; WhatsApp, pay bit-by-bit with Layaway, transfer tickets, and recover orders anytime.</span>
                </div>
                <div class="vn-lg-summary-card">
                    <strong>🎪 For Event Hosts</strong>
                    <span>Create events with multi-tier tickets (with admit counts &amp; limits), guest performers, flyer pages, promo codes, referral marketing, automated payouts, and accounting reports.</span>
                </div>
                <div class="vn-lg-summary-card">
                    <strong>👥 For Event Staff</strong>
                    <span>Access dedicated gate workstations: ultra-fast Security QR Scanners, Coordinator dashboards, Usher Guides, Support Desk, Tech Ops, and Finance Desks.</span>
                </div>
                <div class="vn-lg-summary-card">
                    <strong>🛡️ Platform Security</strong>
                    <span>PCI-DSS Level 1 payments via Paystack, cryptographic QR validation, background email queues, anti-scalping controls, and full NDPA 2023 data compliance.</span>
                </div>
            </div>
        </div>
    </header>

    <!-- ═══ Two-Column Codex Layout ═══ -->
    <div class="vn-lg-layout">

        <!-- Sticky Table of Contents -->
        <aside class="vn-lg-toc" id="vnLgToc">
            <div class="vn-lg-toc-title" id="vnLgTocTitle">
                <div class="vn-lg-toc-title-left">
                    <span>Jump To Section</span>
                    <span class="vn-lg-toc-count">§ 1–37</span>
                </div>
                <button type="button" class="vn-lg-toc-toggle" id="vnLgTocToggle" aria-expanded="false" aria-controls="vnLgTocList" aria-label="Toggle Table of Contents">
                    <span class="vn-lg-toc-toggle-text">Index</span>
                    <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="vn-lg-toc-chevron"><polyline points="6 9 12 15 18 9"></polyline></svg>
                </button>
            </div>
            <ul class="vn-lg-toc-list" id="vnLgTocList">
                <li style="font-weight:700; font-size:0.75rem; text-transform:uppercase; letter-spacing:0.05em; color:var(--vn-brass); margin-top:0.5rem;">Part I &bull; For Attendees</li>
                <li><a href="#section-1" class="vn-lg-toc-link">§ 1. Getting Started</a></li>
                <li><a href="#section-2" class="vn-lg-toc-link">§ 2. Discovering Events</a></li>
                <li><a href="#section-3" class="vn-lg-toc-link">§ 3. Buying Tickets</a></li>
                <li><a href="#section-4" class="vn-lg-toc-link">§ 4. Your Ticket &amp; QR Pass</a></li>
                <li><a href="#section-5" class="vn-lg-toc-link">§ 5. Layaway (Pay Over Time)</a></li>
                <li><a href="#section-6" class="vn-lg-toc-link">§ 6. Transferring Tickets</a></li>
                <li><a href="#section-7" class="vn-lg-toc-link">§ 7. Event Day &amp; Gate Entry</a></li>
                <li><a href="#section-8" class="vn-lg-toc-link">§ 8. Post-Event Reviews</a></li>
                <li><a href="#section-9" class="vn-lg-toc-link">§ 9. Order Recovery</a></li>
                <li><a href="#section-10" class="vn-lg-toc-link">§ 10. Your Profile &amp; History</a></li>

                <li style="font-weight:700; font-size:0.75rem; text-transform:uppercase; letter-spacing:0.05em; color:var(--vn-brass); margin-top:0.75rem;">Part II &bull; For Hosts</li>
                <li><a href="#section-11" class="vn-lg-toc-link">§ 11. Becoming a Host</a></li>
                <li><a href="#section-12" class="vn-lg-toc-link">§ 12. Creating an Event</a></li>
                <li><a href="#section-13" class="vn-lg-toc-link">§ 13. Managing Your Event</a></li>
                <li><a href="#section-14" class="vn-lg-toc-link">§ 14. Ticket Verification</a></li>
                <li><a href="#section-15" class="vn-lg-toc-link">§ 15. Promo Codes</a></li>
                <li><a href="#section-16" class="vn-lg-toc-link">§ 16. Referral Tracking</a></li>
                <li><a href="#section-17" class="vn-lg-toc-link">§ 17. Financials &amp; Withdrawals</a></li>
                <li><a href="#section-18" class="vn-lg-toc-link">§ 18. Accounting Reports</a></li>
                <li><a href="#section-19" class="vn-lg-toc-link">§ 19. Social Flyer Pages</a></li>
                <li><a href="#section-20" class="vn-lg-toc-link">§ 20. Event Staffing</a></li>
                <li><a href="#section-21" class="vn-lg-toc-link">§ 21. Custom Domains</a></li>
                <li><a href="#section-22" class="vn-lg-toc-link">§ 22. Editing &amp; Duplication</a></li>

                <li style="font-weight:700; font-size:0.75rem; text-transform:uppercase; letter-spacing:0.05em; color:var(--vn-brass); margin-top:0.75rem;">Part III &bull; Event Staff</li>
                <li><a href="#section-23" class="vn-lg-toc-link">§ 23. Staff Authentication</a></li>
                <li><a href="#section-24" class="vn-lg-toc-link">§ 24. Security Scanner</a></li>
                <li><a href="#section-25" class="vn-lg-toc-link">§ 25. Coordinator Dashboard</a></li>
                <li><a href="#section-26" class="vn-lg-toc-link">§ 26. Specialist Workstations</a></li>

                <li style="font-weight:700; font-size:0.75rem; text-transform:uppercase; letter-spacing:0.05em; color:var(--vn-brass); margin-top:0.75rem;">Part IV &bull; Administrators</li>
                <li><a href="#section-27" class="vn-lg-toc-link">§ 27. Operations Overview</a></li>
                <li><a href="#section-28" class="vn-lg-toc-link">§ 28. User Governance</a></li>
                <li><a href="#section-29" class="vn-lg-toc-link">§ 29. Event Moderation</a></li>
                <li><a href="#section-30" class="vn-lg-toc-link">§ 30. Payout Approvals</a></li>
                <li><a href="#section-31" class="vn-lg-toc-link">§ 31. System Broadcasts</a></li>
                <li><a href="#section-32" class="vn-lg-toc-link">§ 32. Domain Approvals</a></li>
                <li><a href="#section-33" class="vn-lg-toc-link">§ 33. Platform Configuration</a></li>

                <li style="font-weight:700; font-size:0.75rem; text-transform:uppercase; letter-spacing:0.05em; color:var(--vn-brass); margin-top:0.75rem;">Part V &bull; Security &amp; Trust</li>
                <li><a href="#section-34" class="vn-lg-toc-link">§ 34. Payment Architecture</a></li>
                <li><a href="#section-35" class="vn-lg-toc-link">§ 35. QR Pass Cryptography</a></li>
                <li><a href="#section-36" class="vn-lg-toc-link">§ 36. Account Protection</a></li>
                <li><a href="#section-37" class="vn-lg-toc-link">§ 37. Data Rights &amp; Privacy</a></li>
            </ul>
        </aside>

        <!-- Document Content Codex -->
        <main class="vn-lg-document">

            <!-- ========================================================= -->
            <!-- PART I — FOR ATTENDEES -->
            <!-- ========================================================= -->
            <div class="vn-lg-part-banner">
                <span class="vn-lg-part-label">Part I of V</span>
                <h2 class="vn-lg-part-title">Attendee Experience Guide</h2>
            </div>

            <!-- § 1 -->
            <section class="vn-lg-section" id="section-1">
                <h2 class="vn-lg-section-heading"><span class="vn-lg-section-num">§ 1.</span> Getting Started on <?= e($siteName) ?></h2>
                <p class="vn-lg-text">
                    Welcome to <strong><?= e($siteName) ?></strong>. Getting started takes less than two minutes:
                </p>
                <ul class="vn-lg-list">
                    <li><strong>Create an account:</strong> Click <strong>Register</strong> in the top navigation. Enter your full name, email address, phone number, and a secure password.</li>
                    <li><strong>Verify your email:</strong> We immediately dispatch a cryptographic verification email. Click the link inside to activate your account. If you miss it, you can request a new one at <code>/resend-verification</code>.</li>
                    <li><strong>Sign in:</strong> Use your email and password. Tick "Remember me" on personal devices for a 30-day seamless session.</li>
                    <li><strong>Manage your profile:</strong> Visit your profile page anytime to update your personal details, change your password, or review your complete order history.</li>
                </ul>
            </section>

            <!-- § 2 -->
            <section class="vn-lg-section" id="section-2">
                <h2 class="vn-lg-section-heading"><span class="vn-lg-section-num">§ 2.</span> Discovering Events</h2>
                <p class="vn-lg-text">
                    Finding concerts, tech conferences, festivals, and parties on <?= e($siteName) ?> is intuitive and fast:
                </p>
                <ul class="vn-lg-list">
                    <li><strong>Keyword search:</strong> Search by artist name, event title, organizer, or venue.</li>
                    <li><strong>Location filtering:</strong> Filter events taking place in Lagos, Abuja, Port Harcourt, London, or choose online events.</li>
                    <li><strong>Category pills:</strong> Explore curated categories such as Concerts, Tech, Comedy, Sports, Theater, Family, and Art &amp; Wine.</li>
                    <li><strong>Featured carousel:</strong> The homepage spotlight carousel showcases verified, high-profile experiences.</li>
                </ul>
            </section>

            <!-- § 3 -->
            <section class="vn-lg-section" id="section-3">
                <h2 class="vn-lg-section-heading"><span class="vn-lg-section-num">§ 3.</span> Buying Tickets &amp; Checkout</h2>
                <p class="vn-lg-text">
                    Checkout on <?= e($siteName) ?> is designed for zero friction:
                </p>
                <ul class="vn-lg-list">
                    <li><strong>Select your tier:</strong> Choose your ticket tier (e.g., General Admission, VIP, Table of 5). Pay attention to the <strong>Admits</strong> count (how many guests one ticket permits) and the <strong>Max Per Person</strong> limit set by the host.</li>
                    <li><strong>Promo codes:</strong> If you have an organizer discount code, enter it in the checkout field and click <strong>Apply</strong> to instantly calculate your savings before payment.</li>
                    <li><strong>Redeem Event Credits:</strong> Have credits from a previous layaway plan? Enter your email or apply your available balance at checkout to reduce your total or get tickets completely free!</li>
                    <li><strong>Payment method:</strong> Complete your payment securely via <strong>Paystack</strong> using debit/credit card, bank transfer, USSD, or mobile money.</li>
                    <li><strong>Fee transparency:</strong> You will always see the exact breakdown of ticket price, platform processing fee, and total amount before you authorize payment.</li>
                </ul>
            </section>

            <!-- § 4 -->
            <section class="vn-lg-section" id="section-4">
                <h2 class="vn-lg-section-heading"><span class="vn-lg-section-num">§ 4.</span> Your Ticket &amp; QR Pass (Email + WhatsApp)</h2>
                <p class="vn-lg-text">
                    Never worry about lost tickets at the gate. <?= e($siteName) ?> provides multi-channel delivery:
                </p>
                <ul class="vn-lg-list">
                    <li><strong>Instant email confirmation:</strong> Dispatched via our asynchronous background email queue with your high-resolution QR code, transaction receipt, and calendar link.</li>
                    <li><strong>Direct WhatsApp delivery:</strong> Receive your ticket pass and scannable QR link directly in WhatsApp on your phone. With over 95% open rates, your pass is always one tap away in your chats.</li>
                    <li><strong>Digital Ticket Pass (<code>/pass/{code}</code>):</strong> Every ticket generates an interactive, mobile-optimized digital pass. It features a live scannable QR code, event countdown, venue directions, entry instructions, and admit count.</li>
                </ul>

                <div class="vn-lg-callout vn-lg-callout--amber">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                    <div>
                        <strong>Keep your pass private:</strong> Do not post full screenshots of your QR code on public social media. The first scan at the gate grants admission; keep your QR link safe in your WhatsApp or email.
                    </div>
                </div>
            </section>

            <!-- § 5 -->
            <section class="vn-lg-section" id="section-5">
                <h2 class="vn-lg-section-heading"><span class="vn-lg-section-num">§ 5.</span> Layaway &bull; Pay Bit by Bit &amp; Event Credits</h2>
                <p class="vn-lg-text">
                    Want VIP or Table tickets but prefer spreading payments over time? Look for the <strong>Layaway</strong> badge:
                </p>
                <ul class="vn-lg-list">
                    <li><strong>Pay deposit:</strong> Lock in your ticket by paying an initial deposit (percentage set by the host) at checkout.</li>
                    <li><strong>Layaway dashboard (<code>/layaway</code>):</strong> Track your outstanding balance, payment history, and deadline anytime in your account dashboard.</li>
                    <li><strong>Flexible installments:</strong> Pay increments at your own pace anytime before the event starts.</li>
                    <li><strong>Pass Lockdown for Security:</strong> While your plan is incomplete, your ticket pass is locked. Scannable QR codes are not generated and door check-in is strictly blocked until your final installment is paid.</li>
                    <li><strong>Pass Activation:</strong> Once your final installment is confirmed (100% paid), your QR admission pass is instantly activated and dispatched via Email and WhatsApp.</li>
                    <li><strong>Event Credit Policy:</strong> If an event begins before you finish paying, you do not lose your money! Your installment payments automatically convert into <strong>Event Credits</strong> tied to your email address.</li>
                    <li><strong>Redeem on ANY Event:</strong> Use your Event Credits during checkout on any future event on the platform.</li>
                    <li><strong>Public Credits Hub:</strong> Check your credit balance anytime without logging in at <code>/credits</code> or <code>/layaway/credits</code>.</li>
                    <li><strong>Auto-Account Linking:</strong> When you register or log in with the email used as a guest, your Event Credits, layaway payment history, and tickets automatically link to your profile.</li>
                </ul>
            </section>

            <!-- § 6 -->
            <section class="vn-lg-section" id="section-6">
                <h2 class="vn-lg-section-heading"><span class="vn-lg-section-num">§ 6.</span> Transferring Tickets to Friends</h2>
                <p class="vn-lg-text">
                    Plans change? You can easily transfer any fully paid ticket to a friend:
                </p>
                <ul class="vn-lg-list">
                    <li>Go to your <strong>Profile</strong> &rarr; find the ticket &rarr; click <strong>Transfer Ticket</strong>.</li>
                    <li>Enter the recipient's full name and email address.</li>
                    <li>Upon confirmation, your old QR code is immediately voided. A fresh, unique QR pass is generated and emailed to your recipient.</li>
                </ul>
            </section>

            <!-- § 7 -->
            <section class="vn-lg-section" id="section-7">
                <h2 class="vn-lg-section-heading"><span class="vn-lg-section-num">§ 7.</span> Event Day &amp; Gate Entry</h2>
                <p class="vn-lg-text">
                    Arriving at the venue:
                </p>
                <ul class="vn-lg-list">
                    <li>Open your WhatsApp message or email and tap your <strong>Digital Pass Link</strong> (or open your Apple/Google wallet or saved screenshot).</li>
                    <li>Present the QR code to the door staff or security scanner.</li>
                    <li>The scanner validates the pass in under a second. If your ticket is a group pass (e.g., Admits 5), your entire party is admitted together in that single scan.</li>
                </ul>
            </section>

            <!-- § 8 -->
            <section class="vn-lg-section" id="section-8">
                <h2 class="vn-lg-section-heading"><span class="vn-lg-section-num">§ 8.</span> Post-Event Reviews &amp; Feedback</h2>
                <p class="vn-lg-text">
                    After the event concludes, the host can send a feedback request. You'll receive a link to rate the event from 1 to 5 stars and leave written feedback. This helps organizers improve and rewards top-tier creators.
                </p>
            </section>

            <!-- § 9 -->
            <section class="vn-lg-section" id="section-9">
                <h2 class="vn-lg-section-heading"><span class="vn-lg-section-num">§ 9.</span> Order Recovery &amp; Self-Service</h2>
                <p class="vn-lg-text">
                    Lost connection during checkout? Phone died before you saw the confirmation? We have built-in self-service:
                </p>
                <ul class="vn-lg-list">
                    <li>Visit <code>/order/verify/{reference}</code> or use the <strong>Verify Order</strong> tool.</li>
                    <li>Enter your Paystack payment reference or email.</li>
                    <li>Instantly view your order status, re-trigger your confirmation email, or send the ticket directly to your WhatsApp.</li>
                </ul>
            </section>

            <!-- § 10 -->
            <section class="vn-lg-section" id="section-10">
                <h2 class="vn-lg-section-heading"><span class="vn-lg-section-num">§ 10.</span> Your Profile &amp; Account Settings</h2>
                <p class="vn-lg-text">
                    From your central profile (<code>/profile</code>), you can manage personal information, update your phone number for WhatsApp alerts, change your account password, view your full ticket wallet, and exercise your privacy rights including account deletion.
                </p>
            </section>


            <!-- ========================================================= -->
            <!-- PART II — FOR HOSTS -->
            <!-- ========================================================= -->
            <div class="vn-lg-part-banner">
                <span class="vn-lg-part-label">Part II of V</span>
                <h2 class="vn-lg-part-title">Event Host &amp; Organizer Guide</h2>
            </div>

            <!-- § 11 -->
            <section class="vn-lg-section" id="section-11">
                <h2 class="vn-lg-section-heading"><span class="vn-lg-section-num">§ 11.</span> Becoming a Host</h2>
                <p class="vn-lg-text">
                    Any registered <?= e($siteName) ?> member can become an event host. Navigate to <strong>Host Dashboard</strong> (or click <em>Become a Host</em>). Before publishing paid events, head to <strong>Host Settings</strong> and enter your settlement bank account details (10-digit NUBAN and bank name) to ensure automated payouts.
                </p>
            </section>

            <!-- § 12 -->
            <section class="vn-lg-section" id="section-12">
                <h2 class="vn-lg-section-heading"><span class="vn-lg-section-num">§ 12.</span> Creating an Event (Step-by-Step)</h2>
                <p class="vn-lg-text">
                    The newly refactored Event Creator gives you unmatched control and modern UI/UX:
                </p>
                <ul class="vn-lg-list">
                    <li><strong>1. Event Essentials:</strong> Title, category, summary, detailed description, cover image, and event gallery.</li>
                    <li><strong>2. Schedule &amp; Recurrence:</strong> Single event date/time or recurring event series (Daily, Weekly, Monthly) with occurrence managers.</li>
                    <li><strong>3. Location &amp; Venue:</strong> Physical address, city, Google Maps venue coordinates, or Virtual Webinar link.</li>
                    <li><strong>4. Guest Performers &amp; Speakers:</strong> Add featured artists, DJs, keynote speakers, and hosts with photos, roles, phone numbers, and social handles.</li>
                    <li><strong>5. Executive Ticket Tiers:</strong>
                        <ul style="margin-top:0.25rem;">
                            <li><strong>Price &amp; Capacity:</strong> Free tickets (₦0) or paid tiers with designated quotas.</li>
                            <li><strong>Admits (Group Size):</strong> Number of guests admitted per single ticket (e.g. 1 for Regular, 5 for VIP Table).</li>
                            <li><strong>Max Per Person:</strong> Restrict bulk buying per order (e.g. max 2 VIP tickets per attendee).</li>
                        </ul>
                    </li>
                    <li><strong>6. Professional Event Settings:</strong>
                        <ul style="margin-top:0.25rem;">
                            <li><strong>Absorb Fees:</strong> Choose whether you pay the small platform fee or pass it on to attendees.</li>
                            <li><strong>Social Flyer Page:</strong> Automatically generate a branded mobile flyer page at <code>/f/{id}</code>.</li>
                            <li><strong>Layaway Option:</strong> Allow attendees to pay in installments for high-value tiers.</li>
                            <li><strong>Platform Staffing Request:</strong> Request professional door ushers, scanners, and coordinators.</li>
                        </ul>
                    </li>
                </ul>
            </section>

            <!-- § 13 -->
            <section class="vn-lg-section" id="section-13">
                <h2 class="vn-lg-section-heading"><span class="vn-lg-section-num">§ 13.</span> Managing Your Event &amp; Attendees</h2>
                <p class="vn-lg-text">
                    The <strong>Event Management Console</strong> (<code>/host/event/management/{id}</code>) is your command center:
                </p>
                <ul class="vn-lg-list">
                    <li><strong>Real-time stats:</strong> Track tickets sold, revenue generated, gate check-in percentage, and remaining inventory.</li>
                    <li><strong>Attendee roster:</strong> Search attendees by name, email, or ticket code. Filter by check-in status.</li>
                    <li><strong>Exports:</strong> Download comprehensive CSV attendee lists or print-ready PDF gate manifests.</li>
                    <li><strong>Manual ticket issuance:</strong> Issue complimentary or cash tickets directly to VIPs and sponsors.</li>
                    <li><strong>Event communications:</strong> Send email reminders, trigger <strong>Event Day WhatsApp Alerts</strong> with gate passes, or send post-event feedback requests with one click.</li>
                </ul>
            </section>

            <!-- § 14 -->
            <section class="vn-lg-section" id="section-14">
                <h2 class="vn-lg-section-heading"><span class="vn-lg-section-num">§ 14.</span> Ticket Verification &amp; Gate Control</h2>
                <p class="vn-lg-text">
                    Check in attendees swiftly and prevent ticket fraud:
                </p>
                <ul class="vn-lg-list">
                    <li>Open <strong>Verify Tickets</strong> on any smartphone or tablet browser.</li>
                    <li>Scan attendee QR codes using your device camera or enter the ticket reference code manually.</li>
                    <li>The system verifies ticket validity, admits count, and confirms entry with instant visual and audio cues.</li>
                    <li>Duplicate scans are instantly flagged with the exact time and workstation of the previous check-in.</li>
                </ul>
            </section>

            <!-- § 15 -->
            <section class="vn-lg-section" id="section-15">
                <h2 class="vn-lg-section-heading"><span class="vn-lg-section-num">§ 15.</span> Promo Codes &amp; Flash Discounts</h2>
                <p class="vn-lg-text">
                    Drive ticket sales with tailored marketing campaigns:
                </p>
                <ul class="vn-lg-list">
                    <li>Create discount codes with percentage discounts (e.g., 20% off) or fixed amounts (e.g., ₦5,000 off).</li>
                    <li>Set maximum total redemptions (e.g., first 50 buyers) and expiration dates/times.</li>
                    <li>Track usage counts and revenue generated per code.</li>
                </ul>
            </section>

            <!-- § 16 -->
            <section class="vn-lg-section" id="section-16">
                <h2 class="vn-lg-section-heading"><span class="vn-lg-section-num">§ 16.</span> Referral Marketing &amp; Influencer Tracking</h2>
                <p class="vn-lg-text">
                    Reward promoters, ambassadors, and influencers:
                </p>
                <ul class="vn-lg-list">
                    <li>Generate custom referral codes and tracking links for each ambassador.</li>
                    <li>Track real-time page visits, completed ticket purchases, and total revenue driven by each promoter.</li>
                    <li>Toggle referral links active or inactive at any time.</li>
                </ul>
            </section>

            <!-- § 17 -->
            <section class="vn-lg-section" id="section-17">
                <h2 class="vn-lg-section-heading"><span class="vn-lg-section-num">§ 17.</span> Financials, Payouts &amp; Withdrawals</h2>
                <p class="vn-lg-text">
                    We believe in fast, transparent financial settlements:
                </p>
                <ul class="vn-lg-list">
                    <li><strong>Settlement window:</strong> Payouts are reconciled and disbursed after your event concludes.</li>
                    <li><strong>Bank transfers:</strong> Funds are deposited directly to your verified Nigerian bank account via Paystack Transfer.</li>
                    <li><strong>Instant transparency:</strong> Review gross sales, platform fees, taxes (if applicable), and net payout in your Financials console.</li>
                </ul>
            </section>

            <!-- § 18 -->
            <section class="vn-lg-section" id="section-18">
                <h2 class="vn-lg-section-heading"><span class="vn-lg-section-num">§ 18.</span> Financial Accounting Reports</h2>
                <p class="vn-lg-text">
                    Every event comes with a comprehensive accounting audit report (<code>/host/event/accounting/{id}</code>). View ticket breakdown by tier, payment references, payment channels, service charges, layaway receipts, and download audit-ready CSV exports.
                </p>
            </section>

            <!-- § 19 -->
            <section class="vn-lg-section" id="section-19">
                <h2 class="vn-lg-section-heading"><span class="vn-lg-section-num">§ 19.</span> Social Flyer Pages (<code>/f/{id}</code>)</h2>
                <p class="vn-lg-text">
                    Need a slick, distraction-free landing page for Instagram, TikTok, or Twitter? Toggle the <strong>Flyer Page</strong> feature. Your event gets a dedicated ultra-fast mobile landing page showcasing your flyer, headline info, guest performers, and an instant checkout drawer.
                </p>
            </section>

            <!-- § 20 -->
            <section class="vn-lg-section" id="section-20">
                <h2 class="vn-lg-section-heading"><span class="vn-lg-section-num">§ 20.</span> Professional Event Staffing</h2>
                <p class="vn-lg-text">
                    Running a major production? <?= e($siteName) ?> provides trained event operations personnel. Request Security Scanners, Gate Coordinators, Ushers, and Support Specialists directly from your event management dashboard.
                </p>
            </section>

            <!-- § 21 -->
            <section class="vn-lg-section" id="section-21">
                <h2 class="vn-lg-section-heading"><span class="vn-lg-section-num">§ 21.</span> Custom Domains &amp; Headless Ticketing</h2>
                <p class="vn-lg-text">
                    Brand your checkout experience with your own domain (e.g. <code>tickets.yourfestival.com</code>). Submit a domain request in your management console, pay the setup fee, and connect via our headless API or embeddable iframe widget.
                </p>
            </section>

            <!-- § 22 -->
            <section class="vn-lg-section" id="section-22">
                <h2 class="vn-lg-section-heading"><span class="vn-lg-section-num">§ 22.</span> Editing &amp; Duplicating Events</h2>
                <p class="vn-lg-text">
                    Update event descriptions, add new ticket tiers, or change venue directions at any time. For consumer protection, ticket tiers that have already sold tickets have locked prices and capacities. Want to host the same event again? Click <strong>Duplicate Event</strong> to clone all settings instantly.
                </p>
            </section>


            <!-- ========================================================= -->
            <!-- PART III — FOR EVENT STAFF -->
            <!-- ========================================================= -->
            <div class="vn-lg-part-banner">
                <span class="vn-lg-part-label">Part III of V</span>
                <h2 class="vn-lg-part-title">Event Team &amp; Gate Staff Guide</h2>
            </div>

            <!-- § 23 -->
            <section class="vn-lg-section" id="section-23">
                <h2 class="vn-lg-section-heading"><span class="vn-lg-section-num">§ 23.</span> Staff Authentication Portal</h2>
                <p class="vn-lg-text">
                    Event staff members log in via the specialized Team Portal at <code>/teams/login</code> using their secure staff access code. Staff members do not require personal accounts; access is locked to the specific event and assigned role.
                </p>
            </section>

            <!-- § 24 -->
            <section class="vn-lg-section" id="section-24">
                <h2 class="vn-lg-section-heading"><span class="vn-lg-section-num">§ 24.</span> Security Scanner Workstation</h2>
                <p class="vn-lg-text">
                    Built for rapid door processing under low light and heavy crowds:
                </p>
                <ul class="vn-lg-list">
                    <li>Camera autofocus with sound and haptic vibration feedback on successful scan.</li>
                    <li>Displays attendee name, ticket tier, and <strong>Admits Count</strong> prominently.</li>
                    <li>Rejects invalid, already scanned, or refunded passes with clear red warnings.</li>
                </ul>
            </section>

            <!-- § 25 -->
            <section class="vn-lg-section" id="section-25">
                <h2 class="vn-lg-section-heading"><span class="vn-lg-section-num">§ 25.</span> Gate Coordinator Dashboard</h2>
                <p class="vn-lg-text">
                    The Gate Coordinator oversees the entire entry perimeter. From this dashboard, coordinators monitor gate traffic across all scanner lanes, issue emergency gate tickets for VIPs, and toggle staff check-in capabilities.
                </p>
            </section>

            <!-- § 26 -->
            <section class="vn-lg-section" id="section-26">
                <h2 class="vn-lg-section-heading"><span class="vn-lg-section-num">§ 26.</span> Specialist Workstations</h2>
                <ul class="vn-lg-list">
                    <li><strong>Usher Guide:</strong> Visual seat/table map guide to assist guests to their designated VIP areas.</li>
                    <li><strong>Support Desk:</strong> Attendee lookup by email or phone to resolve lost passes or misplaced tickets.</li>
                    <li><strong>Finance Desk:</strong> Gate cash reconciliation and door ticket sales tracking.</li>
                    <li><strong>Tech Ops:</strong> Real-time latency, webhook health, and scanner hardware diagnostics.</li>
                </ul>
            </section>


            <!-- ========================================================= -->
            <!-- PART IV — FOR ADMINISTRATORS -->
            <!-- ========================================================= -->
            <div class="vn-lg-part-banner">
                <span class="vn-lg-part-label">Part IV of V</span>
                <h2 class="vn-lg-part-title">Platform Administration Guide</h2>
            </div>

            <!-- § 27 -->
            <section class="vn-lg-section" id="section-27">
                <h2 class="vn-lg-section-heading"><span class="vn-lg-section-num">§ 27.</span> Operations Overview</h2>
                <p class="vn-lg-text">
                    The Admin Dashboard (<code>/admin/overview</code>) provides platform-wide metrics: Gross Merchandise Value (GMV), platform fee revenue, total active hosts, monthly active attendees, and server health.
                </p>
            </section>

            <!-- § 28 -->
            <section class="vn-lg-section" id="section-28">
                <h2 class="vn-lg-section-heading"><span class="vn-lg-section-num">§ 28.</span> User Governance &amp; Moderation</h2>
                <p class="vn-lg-text">
                    Admins manage user accounts at <code>/admin/users</code>. Tools include role assignment (User, Host, Team, Admin, Super Admin), manual email verification, status enforcement (Active, Suspended, Banned), and detailed audit logs.
                </p>
            </section>

            <!-- § 29 -->
            <section class="vn-lg-section" id="section-29">
                <h2 class="vn-lg-section-heading"><span class="vn-lg-section-num">§ 29.</span> Event Moderation &amp; Curation</h2>
                <p class="vn-lg-text">
                    At <code>/admin/events</code>, admins curate the homepage by toggling <strong>Featured</strong> status, reviewing reported events, removing prohibited listings, and examining ticket sales performance.
                </p>
            </section>

            <!-- § 30 -->
            <section class="vn-lg-section" id="section-30">
                <h2 class="vn-lg-section-heading"><span class="vn-lg-section-num">§ 30.</span> Payouts &amp; Settlements Management</h2>
                <p class="vn-lg-text">
                    Manage host disbursements at <code>/admin/payouts</code>. Review bank details, verify event completion, check chargeback history, and execute batch transfers via Paystack automated payouts.
                </p>
            </section>

            <!-- § 31 -->
            <section class="vn-lg-section" id="section-31">
                <h2 class="vn-lg-section-heading"><span class="vn-lg-section-num">§ 31.</span> Communications &amp; Broadcasts</h2>
                <p class="vn-lg-text">
                    Dispatch platform announcements, policy updates, or holiday notices to all users, active hosts, or specific event attendees using the Broadcast Center (<code>/admin/communications</code>).
                </p>
            </section>

            <!-- § 32 -->
            <section class="vn-lg-section" id="section-32">
                <h2 class="vn-lg-section-heading"><span class="vn-lg-section-num">§ 32.</span> Domain Requests Approval</h2>
                <p class="vn-lg-text">
                    Review incoming host custom domain applications at <code>/admin/domain-requests</code>, verify DNS configuration, confirm payment status, and activate custom domain routing.
                </p>
            </section>

            <!-- § 33 -->
            <section class="vn-lg-section" id="section-33">
                <h2 class="vn-lg-section-heading"><span class="vn-lg-section-num">§ 33.</span> Platform Settings &amp; Cache Management</h2>
                <p class="vn-lg-text">
                    Configure site branding, logos, social links, support contact email, currency exchange rates, platform fee percentages, and clear application view caches at <code>/admin/settings</code>.
                </p>
            </section>


            <!-- ========================================================= -->
            <!-- PART V — SECURITY & TRUST -->
            <!-- ========================================================= -->
            <div class="vn-lg-part-banner">
                <span class="vn-lg-part-label">Part V of V</span>
                <h2 class="vn-lg-part-title">Platform Security &amp; Trust Architecture</h2>
            </div>

            <!-- § 34 -->
            <section class="vn-lg-section" id="section-34">
                <h2 class="vn-lg-section-heading"><span class="vn-lg-section-num">§ 34.</span> Payment Architecture &amp; PCI-DSS Compliance</h2>
                <p class="vn-lg-text">
                    <?= e($siteName) ?> partners with <strong>Paystack</strong>, a certified PCI-DSS Level 1 service provider:
                </p>
                <ul class="vn-lg-list">
                    <li>Cardholder numbers, CVVs, and bank PINs never touch or enter our servers.</li>
                    <li>All transactions take place across 256-bit TLS encrypted channels.</li>
                    <li>Automated server-to-server HMAC webhooks verify all payment confirmations.</li>
                </ul>
            </section>

            <!-- § 35 -->
            <section class="vn-lg-section" id="section-35">
                <h2 class="vn-lg-section-heading"><span class="vn-lg-section-num">§ 35.</span> Cryptographic QR Code Validation</h2>
                <p class="vn-lg-text">
                    Ticket codes use cryptographically strong pseudo-random strings that cannot be guessed or forged. Gate scanners validate codes against the central database with optimistic locking to prevent double-entry race conditions.
                </p>
            </section>

            <!-- § 36 -->
            <section class="vn-lg-section" id="section-36">
                <h2 class="vn-lg-section-heading"><span class="vn-lg-section-num">§ 36.</span> Account Protection &amp; Platform Hygiene</h2>
                <ul class="vn-lg-list">
                    <li><strong>Password Hashing:</strong> Passwords are protected using modern Argon2id/Bcrypt adaptive hashing.</li>
                    <li><strong>CSRF Defense:</strong> All form actions and mutation requests require cryptographic Cross-Site Request Forgery tokens.</li>
                    <li><strong>Rate Limiting:</strong> Intelligent rate limiters protect checkout endpoints, login forms, and WhatsApp notification dispatch from abusive traffic.</li>
                    <li><strong>Asynchronous Email Queues:</strong> Ticket delivery is handled in a decoupled background queue, ensuring checkout completes instantly even during major traffic spikes.</li>
                </ul>
            </section>

            <!-- § 37 -->
            <section class="vn-lg-section" id="section-37">
                <h2 class="vn-lg-section-heading"><span class="vn-lg-section-num">§ 37.</span> Data Rights &amp; NDPA 2023 Compliance</h2>
                <p class="vn-lg-text">
                    We strictly adhere to the <strong>Nigeria Data Protection Act (NDPA) 2023</strong>:
                </p>
                <ul class="vn-lg-list">
                    <li>We never sell or lease your personal information to third-party data brokers.</li>
                    <li>You have the right to request access, correction, portability, and complete deletion of your account data.</li>
                    <li>Hosts only receive the minimum necessary attendee data required for safety and gate admission.</li>
                </ul>
                <div class="vn-lg-contact" style="margin-top: 1.5rem;">
                    <div class="vn-lg-contact-info">
                        <strong><?= e($siteName) ?> Support &amp; Privacy Officer</strong>
                        <span>Email: <?= e($supportEmail) ?></span>
                    </div>
                    <a href="mailto:<?= e($supportEmail) ?>" class="vn-lg-contact-btn">
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                        Get Help &amp; Support
                    </a>
                </div>
            </section>

        </main>
    </div>
</div>
@endsection

@section('script')
<script>
// Archival TOC Scrollspy & Mobile Drawer
document.addEventListener('DOMContentLoaded', function() {
    const toc = document.getElementById('vnLgToc');
    const tocToggle = document.getElementById('vnLgTocToggle');
    const tocTitle = document.getElementById('vnLgTocTitle');
    const links = document.querySelectorAll('.vn-lg-toc-link');
    const sections = document.querySelectorAll('.vn-lg-section');

    function toggleToc(e) {
        if (window.innerWidth <= 960 && toc) {
            if (e) e.stopPropagation();
            const isOpen = toc.classList.toggle('is-open');
            if (tocToggle) {
                tocToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            }
        }
    }

    if (tocToggle) {
        tocToggle.addEventListener('click', toggleToc);
    }
    if (tocTitle) {
        tocTitle.addEventListener('click', function(e) {
            if (e.target !== tocToggle && !tocToggle.contains(e.target)) {
                toggleToc(e);
            }
        });
    }

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

    links.forEach(l => {
        l.addEventListener('click', function() {
            if (window.innerWidth <= 960 && toc) {
                toc.classList.remove('is-open');
                if (tocToggle) {
                    tocToggle.setAttribute('aria-expanded', 'false');
                }
            }
        });
    });

    window.addEventListener('scroll', updateActiveToc, { passive: true });
    updateActiveToc();
});
</script>
@endsection
