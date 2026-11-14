<?php

/**
 * Privacy Policy — Archival Legal Gazette
 *
 * Vintage gazette & legal codex aesthetic: editorial serif typography,
 * parchment index navigation, tactile summary ledger, and responsive layout.
 *
 * @package Eventlyy
 * @subpackage Views/Privacy
 * @author Celio Natti
 * @version 4.0.0
 */

$siteName = $siteName ?? site_setting('site_title', 'Eventlyy');
$supportEmail = $supportEmail ?? site_setting('support_email', 'support@eventlyy.com');
?>

@extends('layouts/main')

@section('title')
Privacy Policy &amp; Your Data &bull; <?= e($siteName) ?>
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
                <span>Privacy Policy</span>
            </nav>

            <div class="vn-lg-tabs">
                <a href="{{ route('terms') }}" class="vn-lg-tab">Terms of Service</a>
                <a href="{{ route('privacy') }}" class="vn-lg-tab active">Privacy Policy</a>
            </div>
        </div>

        <span class="vn-lg-pre">
            <span>✦</span> YOUR DATA, YOUR RULES &bull; NOVEMBER 2026
        </span>

        <h1 class="vn-lg-title">Privacy Policy &amp; How We Handle Your Data</h1>

        <div class="vn-lg-meta">
            <span class="vn-lg-meta-item">📅 Last refreshed: November 14, 2026</span>
            <span class="vn-lg-meta-item">&bull;</span>
            <span class="vn-lg-meta-item">⏱️ ~7 min read</span>
            <span class="vn-lg-meta-item">&bull;</span>
            <span class="vn-lg-meta-item">🌐 <?= e($siteName) ?> Community</span>
        </div>

        <!-- Plain English Summary Ledger -->
        <div class="vn-lg-summary">
            <div class="vn-lg-summary-head">
                <span>🛡️</span> The Straight-Up Privacy Promise
            </div>
            <div class="vn-lg-summary-grid">
                <div class="vn-lg-summary-card">
                    <strong>🚫 We Never Sell Your Data</strong>
                    <span>Period. No ads. No shady data brokers. No selling or renting your personal info to third parties. Ever.</span>
                </div>
                <div class="vn-lg-summary-card">
                    <strong>🎟️ Hosts Only See What They Need</strong>
                    <span>Event hosts and door staff only get the essentials — your name, ticket tier, and scan status. Nothing extra.</span>
                </div>
                <div class="vn-lg-summary-card">
                    <strong>💳 Payments Stay Locked</strong>
                    <span>Your card details are handled by Paystack (PCI-DSS certified). We never see, touch, or store your raw card numbers.</span>
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
                <span style="font-size: 0.85rem; color: var(--vn-brass); font-family: var(--vn-font-serif); font-style: italic;">§ 1–14</span>
            </div>
            <ul class="vn-lg-toc-list">
                <li><a href="#section-1" class="vn-lg-toc-link">§ 1. Introduction</a></li>
                <li><a href="#section-2" class="vn-lg-toc-link">§ 2. What We Collect</a></li>
                <li><a href="#section-3" class="vn-lg-toc-link">§ 3. How We Use Your Data</a></li>
                <li><a href="#section-4" class="vn-lg-toc-link">§ 4. Legal Basis for Processing</a></li>
                <li><a href="#section-5" class="vn-lg-toc-link">§ 5. Who Sees Your Data</a></li>
                <li><a href="#section-6" class="vn-lg-toc-link">§ 6. Payment Security</a></li>
                <li><a href="#section-7" class="vn-lg-toc-link">§ 7. Layaway &amp; Payment Data</a></li>
                <li><a href="#section-8" class="vn-lg-toc-link">§ 8. QR Codes &amp; Check-In</a></li>
                <li><a href="#section-9" class="vn-lg-toc-link">§ 9. Cookies &amp; Sessions</a></li>
                <li><a href="#section-10" class="vn-lg-toc-link">§ 10. How Long We Keep Data</a></li>
                <li><a href="#section-11" class="vn-lg-toc-link">§ 11. Your Rights</a></li>
                <li><a href="#section-12" class="vn-lg-toc-link">§ 12. Children &amp; Minors</a></li>
                <li><a href="#section-13" class="vn-lg-toc-link">§ 13. Updates to This Policy</a></li>
                <li><a href="#section-14" class="vn-lg-toc-link">§ 14. Contact Us</a></li>
            </ul>
        </aside>

        <!-- Document Content Codex -->
        <main class="vn-lg-document">

            <!-- § 1 — Introduction -->
            <section class="vn-lg-section" id="section-1">
                <h2 class="vn-lg-section-heading"><span class="vn-lg-section-num">§ 1.</span> Introduction</h2>
                <p class="vn-lg-text">
                    <strong><?= e($siteName) ?></strong> ("we," "us," or "the Platform") takes your privacy seriously. This Privacy Policy explains what personal data we collect, why we collect it, how we use it, who we share it with, and what rights you have over it.
                </p>
                <p class="vn-lg-text">
                    This policy applies to everyone who uses <?= e($siteName) ?> — whether you're buying tickets, hosting events, browsing the platform, or using our layaway feature. It covers our website, all related services, and any emails we send you.
                </p>
                <p class="vn-lg-text">
                    We comply with the <strong>Nigeria Data Protection Act (NDPA) 2023</strong> and its implementing regulations. If you're outside Nigeria, relevant data protection laws in your jurisdiction may also apply.
                </p>
            </section>

            <!-- § 2 — What We Collect -->
            <section class="vn-lg-section" id="section-2">
                <h2 class="vn-lg-section-heading"><span class="vn-lg-section-num">§ 2.</span> What We Collect</h2>
                <p class="vn-lg-text">
                    Here's a straight-up list of the personal data we collect and how we get it:
                </p>

                <p class="vn-lg-text"><strong>Info you give us directly:</strong></p>
                <ul class="vn-lg-list">
                    <li><strong>Account registration:</strong> Your name, email address, phone number, and a password (which we store as a secure hash — we never see or store your actual password).</li>
                    <li><strong>Event hosting:</strong> Your organization/brand name, event details (title, description, dates, location, ticket tiers, pricing), and bank account info (10-digit NUBAN) for payouts.</li>
                    <li><strong>Ticket purchases:</strong> Your name and email (used for ticket delivery and confirmation).</li>
                    <li><strong>Layaway payments:</strong> Transaction records for each installment payment you make toward a layaway ticket.</li>
                    <li><strong>Support requests:</strong> Any info you include when you email us or submit a support request.</li>
                </ul>

                <p class="vn-lg-text"><strong>Info we collect automatically:</strong></p>
                <ul class="vn-lg-list">
                    <li><strong>Device &amp; browser info:</strong> Your IP address, browser type, operating system, and device type.</li>
                    <li><strong>Usage data:</strong> Pages you visit, events you view, and actions you take on the platform.</li>
                    <li><strong>Check-in data:</strong> When your QR code is scanned at an event, we record the scan timestamp and entry status.</li>
                    <li><strong>Referral data:</strong> If you arrive through a referral link, we track that referral for attribution purposes.</li>
                </ul>

                <div class="vn-lg-callout vn-lg-callout--forest">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                    <div>
                        <strong>What we DON'T collect:</strong> We never collect your credit/debit card numbers, CVVs, PINs, or bank login credentials. All payment processing goes through Paystack — we never see your raw card data.
                    </div>
                </div>
            </section>

            <!-- § 3 — How We Use Your Data -->
            <section class="vn-lg-section" id="section-3">
                <h2 class="vn-lg-section-heading"><span class="vn-lg-section-num">§ 3.</span> How We Use Your Data</h2>
                <p class="vn-lg-text">
                    We only use your data for stuff that actually matters:
                </p>
                <ul class="vn-lg-list">
                    <li><strong>Running your account:</strong> Creating and managing your <?= e($siteName) ?> account, verifying your email, and keeping you logged in.</li>
                    <li><strong>Processing tickets:</strong> Generating your tickets, issuing QR codes, and sending confirmation emails.</li>
                    <li><strong>Processing payments:</strong> Handling ticket purchases, layaway installments, promo code discounts, and host payouts through Paystack.</li>
                    <li><strong>Managing layaway:</strong> Tracking your partial payments, sending payment reminders, notifying you when layaway is complete, and converting expired layaway to event credits.</li>
                    <li><strong>Event check-in:</strong> Verifying your ticket at the door via QR scan and preventing duplicate entries.</li>
                    <li><strong>Ticket transfers:</strong> Facilitating ticket transfers between users when requested.</li>
                    <li><strong>Email communications:</strong> Sending you ticket confirmations, layaway updates, payment receipts, event reminders, and important platform announcements.</li>
                    <li><strong>Referral tracking:</strong> Attributing ticket sales to referral links so hosts can track their marketing.</li>
                    <li><strong>Platform improvement:</strong> Understanding how people use <?= e($siteName) ?> so we can make it better.</li>
                    <li><strong>Safety &amp; security:</strong> Detecting fraud, preventing abuse, and protecting the platform and its users.</li>
                    <li><strong>Legal compliance:</strong> Meeting our obligations under Nigerian law, including tax and financial reporting requirements.</li>
                </ul>
            </section>

            <!-- § 4 — Legal Basis -->
            <section class="vn-lg-section" id="section-4">
                <h2 class="vn-lg-section-heading"><span class="vn-lg-section-num">§ 4.</span> Legal Basis for Processing</h2>
                <p class="vn-lg-text">
                    Under the NDPA 2023, we process your data based on the following lawful grounds:
                </p>
                <ul class="vn-lg-list">
                    <li><strong>Contract performance:</strong> Processing your data is necessary to provide you with our services — creating your account, selling you tickets, processing payments, managing layaway, and issuing QR passes.</li>
                    <li><strong>Consent:</strong> For non-essential communications like marketing emails or promotional updates. You can withdraw consent anytime.</li>
                    <li><strong>Legitimate interest:</strong> Improving our platform, preventing fraud, and ensuring security — as long as it doesn't override your rights.</li>
                    <li><strong>Legal obligation:</strong> When we're required by Nigerian law to keep records (e.g., financial transaction records for tax purposes).</li>
                </ul>
            </section>

            <!-- § 5 — Who Sees Your Data -->
            <section class="vn-lg-section" id="section-5">
                <h2 class="vn-lg-section-heading"><span class="vn-lg-section-num">§ 5.</span> Who Sees Your Data</h2>
                <p class="vn-lg-text">
                    We don't sell, rent, or trade your data. Here's exactly who may access it and why:
                </p>
                <ul class="vn-lg-list">
                    <li><strong>Event hosts:</strong> When you buy a ticket, the host gets your name, email, ticket tier, and check-in status — that's it. They need this to verify your entry and send event updates.</li>
                    <li><strong>Paystack:</strong> Our payment processor handles all financial transactions. They receive the payment info needed to process your purchase securely.</li>
                    <li><strong>Email service provider:</strong> We use email services to send you transactional emails (ticket confirmations, layaway updates, receipts). They process your email address to deliver messages on our behalf.</li>
                    <li><strong>Law enforcement:</strong> We will share data if we're legally required to do so by a valid court order, subpoena, or legal process under Nigerian law.</li>
                </ul>

                <div class="vn-lg-callout vn-lg-callout--forest">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                    <div>
                        <strong>Host responsibility:</strong> Event hosts are <strong>contractually prohibited</strong> from using your data for unsolicited marketing or sharing it with third parties. If a host misuses your data, report it to us at <strong><?= e($supportEmail) ?></strong>.
                    </div>
                </div>
            </section>

            <!-- § 6 — Payment Security -->
            <section class="vn-lg-section" id="section-6">
                <h2 class="vn-lg-section-heading"><span class="vn-lg-section-num">§ 6.</span> Payment Security</h2>
                <p class="vn-lg-text">
                    Your money matters, and so does its security:
                </p>
                <ul class="vn-lg-list">
                    <li>All payments are processed by <strong>Paystack</strong>, which is PCI-DSS Level 1 certified — the highest level of payment security.</li>
                    <li>We <strong>never</strong> see, store, or have access to your card numbers, CVV, expiration dates, or bank PINs.</li>
                    <li>All payment data travels over <strong>TLS-encrypted connections</strong> (the padlock you see in your browser).</li>
                    <li>We store only transaction references, amounts, and payment statuses — just enough to reconcile your purchases and issue receipts.</li>
                </ul>
            </section>

            <!-- § 7 — Layaway Data -->
            <section class="vn-lg-section" id="section-7">
                <h2 class="vn-lg-section-heading"><span class="vn-lg-section-num">§ 7.</span> Layaway &amp; Partial Payment Data</h2>
                <p class="vn-lg-text">
                    If you use our layaway feature, here's what additional data we handle:
                </p>
                <ul class="vn-lg-list">
                    <li><strong>Payment history:</strong> We track each installment you make — the amount, date, and Paystack transaction reference.</li>
                    <li><strong>Balance tracking:</strong> We maintain a running balance showing how much you've paid and how much is left.</li>
                    <li><strong>Status records:</strong> Whether your layaway is active, completed, or expired.</li>
                    <li><strong>Credit records:</strong> If your layaway expires, we record the event credit balance assigned to your account.</li>
                </ul>
                <p class="vn-lg-text">
                    This data is kept for the duration of the layaway period and for a reasonable time afterward for accounting and dispute resolution.
                </p>
            </section>

            <!-- § 8 — QR & Check-in -->
            <section class="vn-lg-section" id="section-8">
                <h2 class="vn-lg-section-heading"><span class="vn-lg-section-num">§ 8.</span> QR Codes &amp; Check-In Data</h2>
                <p class="vn-lg-text">
                    Every ticket gets a unique QR code. When it's scanned at an event:
                </p>
                <ul class="vn-lg-list">
                    <li>We record the <strong>scan timestamp</strong> and <strong>admission status</strong> (admitted or denied).</li>
                    <li>This data is used to prevent duplicate entries — one scan per ticket, no exceptions.</li>
                    <li>Event hosts can see the check-in status of their attendees through their host dashboard.</li>
                    <li>Check-in data is retained as part of the event record and is subject to our standard data retention practices.</li>
                </ul>
            </section>

            <!-- § 9 — Cookies -->
            <section class="vn-lg-section" id="section-9">
                <h2 class="vn-lg-section-heading"><span class="vn-lg-section-num">§ 9.</span> Cookies &amp; Sessions</h2>
                <p class="vn-lg-text">
                    We use cookies — but only the necessary kind. Here's what's running:
                </p>
                <ul class="vn-lg-list">
                    <li><strong>Session cookies:</strong> Keep you logged in while you browse. They expire when you close your browser or after a set period.</li>
                    <li><strong>Remember-me cookies:</strong> If you tick "remember me" at login, we store a secure token so you don't have to re-enter your password every time. Valid for 30 days.</li>
                    <li><strong>CSRF tokens:</strong> Protect your forms from cross-site request forgery attacks. These are security cookies — they keep your actions safe.</li>
                    <li><strong>Theme preference:</strong> If you switch between light/dark mode, we save your preference locally.</li>
                </ul>
                <p class="vn-lg-text">
                    We do <strong>NOT</strong> use advertising cookies, tracking pixels, or third-party analytics cookies. No Google Analytics, no Facebook Pixel, none of that.
                </p>
            </section>

            <!-- § 10 — Data Retention -->
            <section class="vn-lg-section" id="section-10">
                <h2 class="vn-lg-section-heading"><span class="vn-lg-section-num">§ 10.</span> How Long We Keep Your Data</h2>
                <p class="vn-lg-text">
                    We don't keep your data forever. Here's the breakdown:
                </p>
                <ul class="vn-lg-list">
                    <li><strong>Account data:</strong> Kept as long as your account is active. If you delete your account, we remove your personal info within 30 days, except where we're legally required to keep it.</li>
                    <li><strong>Transaction records:</strong> Kept for <strong>6 years</strong> after the transaction to comply with Nigerian tax and financial record-keeping laws.</li>
                    <li><strong>Layaway records:</strong> Kept for the layaway period plus 12 months for dispute resolution, then anonymized or deleted.</li>
                    <li><strong>Check-in logs:</strong> Kept for 12 months after the event, then deleted.</li>
                    <li><strong>Support emails:</strong> Kept for 24 months after your issue is resolved.</li>
                </ul>
                <p class="vn-lg-text">
                    When data is no longer needed, we either delete it permanently or anonymize it so it can't be traced back to you.
                </p>
            </section>

            <!-- § 11 — Your Rights -->
            <section class="vn-lg-section" id="section-11">
                <h2 class="vn-lg-section-heading"><span class="vn-lg-section-num">§ 11.</span> Your Rights Under the NDPA</h2>
                <p class="vn-lg-text">
                    Under the Nigeria Data Protection Act (NDPA) 2023, you have real rights over your data. Here they are:
                </p>
                <ul class="vn-lg-list">
                    <li><strong>Right to access:</strong> You can request a copy of all personal data we hold about you. We'll provide it within 30 days.</li>
                    <li><strong>Right to correction:</strong> If any of your data is wrong or outdated, tell us and we'll fix it.</li>
                    <li><strong>Right to deletion:</strong> You can ask us to delete your account and personal data. We'll do it, except where we're legally required to keep certain records (like financial transactions).</li>
                    <li><strong>Right to data portability:</strong> You can request your data in a structured, machine-readable format so you can take it somewhere else.</li>
                    <li><strong>Right to object:</strong> You can object to us processing your data for certain purposes, like marketing.</li>
                    <li><strong>Right to restrict processing:</strong> In certain situations, you can ask us to limit how we use your data while we resolve a concern.</li>
                    <li><strong>Right to withdraw consent:</strong> Where we process your data based on consent, you can withdraw that consent at any time. This doesn't affect anything we already did before you withdrew.</li>
                </ul>
                <p class="vn-lg-text">
                    To exercise any of these rights, email us at <strong><?= e($supportEmail) ?></strong>. We'll respond within 30 days. If you're not satisfied with our response, you can lodge a complaint with the <strong>Nigeria Data Protection Commission (NDPC)</strong>.
                </p>
            </section>

            <!-- § 12 — Children -->
            <section class="vn-lg-section" id="section-12">
                <h2 class="vn-lg-section-heading"><span class="vn-lg-section-num">§ 12.</span> Children &amp; Minors</h2>
                <p class="vn-lg-text">
                    <?= e($siteName) ?> is designed for users aged <strong>16 and above</strong>. We do not knowingly collect personal data from anyone under 16 without verified parental or guardian consent.
                </p>
                <p class="vn-lg-text">
                    If you're a parent or guardian and you believe your child has created an account or provided personal data without your consent, please contact us at <strong><?= e($supportEmail) ?></strong>. We'll promptly delete the data and close the account.
                </p>
            </section>

            <!-- § 13 — Updates -->
            <section class="vn-lg-section" id="section-13">
                <h2 class="vn-lg-section-heading"><span class="vn-lg-section-num">§ 13.</span> Updates to This Policy</h2>
                <p class="vn-lg-text">
                    We may update this Privacy Policy from time to time — to reflect new features (like layaway), legal requirements, or better ways of explaining things. When we do:
                </p>
                <ul class="vn-lg-list">
                    <li>We'll update the "Last refreshed" date at the top.</li>
                    <li>For significant changes, we'll try to notify you by email or through a platform notice.</li>
                    <li>Your continued use of <?= e($siteName) ?> after updates means you're cool with the changes.</li>
                </ul>
            </section>

            <!-- § 14 — Contact -->
            <section class="vn-lg-section" id="section-14">
                <h2 class="vn-lg-section-heading"><span class="vn-lg-section-num">§ 14.</span> Contact Us</h2>
                <p class="vn-lg-text">
                    Got questions about your data, want to exercise your rights, or just curious about how we handle things? We're here:
                </p>

                <div class="vn-lg-contact">
                    <div class="vn-lg-contact-info">
                        <strong><?= e($siteName) ?> Data Protection &amp; Privacy Team</strong>
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

                <p class="vn-lg-text" style="margin-top: 1.5rem; font-style: italic;">
                    You can also lodge a data protection complaint with the <strong>Nigeria Data Protection Commission (NDPC)</strong> if you believe your rights under the NDPA have been violated.
                </p>
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
