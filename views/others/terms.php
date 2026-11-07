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
 * @version 3.0.0
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
            <span>✦</span> PLATFORM RULES &bull; ISSUE 2026 &bull; NO SURPRISES
        </span>

        <h1 class="vn-lg-title">Terms of Service &amp; Community Guidelines</h1>

        <div class="vn-lg-meta">
            <span class="vn-lg-meta-item">📅 Last refreshed: August 13, 2026</span>
            <span class="vn-lg-meta-item">&bull;</span>
            <span class="vn-lg-meta-item">⏱️ ~6 min read</span>
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
                    <strong>🎟️ One Pass = One Person</strong>
                    <span>Every QR ticket admits exactly one person. Duplicate screenshots, resale scams, or scalping will get flagged at the door.</span>
                </div>
                <div class="vn-lg-summary-card">
                    <strong>💳 Host Refund Policies</strong>
                    <span>Event hosts set their own refund rules. Platform payment gateway processing fees are non-refundable.</span>
                </div>
                <div class="vn-lg-summary-card">
                    <strong>🛡️ Platform vs. Venue</strong>
                    <span><?= e($siteName) ?> powers the ticketing tech. Hosts are directly in charge of running the show, venue safety, and security.</span>
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
                <li><a href="#section-1" class="vn-lg-toc-link">§ 1. Agreement to Terms</a></li>
                <li><a href="#section-2" class="vn-lg-toc-link">§ 2. Intermediary Status</a></li>
                <li><a href="#section-3" class="vn-lg-toc-link">§ 3. User &amp; Host Accounts</a></li>
                <li><a href="#section-4" class="vn-lg-toc-link">§ 4. Ticket Sales &amp; Gate Entry</a></li>
                <li><a href="#section-5" class="vn-lg-toc-link">§ 5. Refunds &amp; Cancellations</a></li>
                <li><a href="#section-6" class="vn-lg-toc-link">§ 6. Host Payouts &amp; Escrow</a></li>
                <li><a href="#section-7" class="vn-lg-toc-link">§ 7. Prohibited Content</a></li>
                <li><a href="#section-8" class="vn-lg-toc-link">§ 8. Intellectual Property</a></li>
                <li><a href="#section-9" class="vn-lg-toc-link">§ 9. Limitation of Liability</a></li>
                <li><a href="#section-10" class="vn-lg-toc-link">§ 10. Indemnification</a></li>
                <li><a href="#section-11" class="vn-lg-toc-link">§ 11. Force Majeure</a></li>
                <li><a href="#section-12" class="vn-lg-toc-link">§ 12. Governing Jurisdiction</a></li>
                <li><a href="#section-13" class="vn-lg-toc-link">§ 13. Account Termination</a></li>
                <li><a href="#section-14" class="vn-lg-toc-link">§ 14. Legal Desk &amp; Notices</a></li>
            </ul>
        </aside>

        <!-- Document Content Codex -->
        <main class="vn-lg-document">

            <section class="vn-lg-section" id="section-1">
                <h2 class="vn-lg-section-heading"><span class="vn-lg-section-num">§ 1.</span> Agreement to Terms</h2>
                <p class="vn-lg-text">
                    By browsing, accessing, or transacting within the <strong><?= e($siteName) ?></strong> public registry, mobile portal, host console, or digital ticketing infrastructure, you enter into a legally enforceable covenant governed by these Terms of Service. If you do not consent to every covenant contained herein, you must immediately cease all interactions with the platform.
                </p>
            </section>

            <section class="vn-lg-section" id="section-2">
                <h2 class="vn-lg-section-heading"><span class="vn-lg-section-num">§ 2.</span> Platform Role &amp; Intermediary Status</h2>
                <p class="vn-lg-text">
                    <?= e($siteName) ?> operates strictly as an independent technology intermediary uniting event organizers ("Hosts") with prospective patrons ("Attendees"). Unless an event is explicitly certified as an official company presentation:
                </p>
                <ul class="vn-lg-list">
                    <li><?= e($siteName) ?> is neither the promoter, producer, creator, nor seller of record for third-party listed occasions.</li>
                    <li><?= e($siteName) ?> bears no liability for venue safety, physical acoustics, schedule postponements, or host representations.</li>
                    <li>All agreements regarding physical admission and experience quality exist solely between the Attendee and the Host.</li>
                </ul>
            </section>

            <section class="vn-lg-section" id="section-3">
                <h2 class="vn-lg-section-heading"><span class="vn-lg-section-num">§ 3.</span> User &amp; Host Accounts</h2>
                <p class="vn-lg-text">
                    Registered users and organizers must provide accurate, verified, and complete records during account formation and ticket checkout. You retain complete responsibility for preserving the sanctity of your authentication credentials and for all operations executed under your ledger.
                </p>
            </section>

            <section class="vn-lg-section" id="section-4">
                <h2 class="vn-lg-section-heading"><span class="vn-lg-section-num">§ 4.</span> Ticket Sales, QR Codes &amp; Gate Admission</h2>
                <p class="vn-lg-text">
                    Admission passes generated across <?= e($siteName) ?> contain high-density 256-bit optical QR codes engineered for rapid turnstile verification. The following strictures apply unconditionally:
                </p>
                <ul class="vn-lg-list">
                    <li><strong>Single Clearance Rule:</strong> Each QR code is uniquely encrypted and admits one individual. Upon scan clearance, the pass is irrevocably marked as admitted.</li>
                    <li><strong>Prohibition of Scalping:</strong> Reselling admission above face value or circulating duplicated passes via secondary speculative markets is unlawful and voids the ticket.</li>
                    <li><strong>Host Gate Discretion:</strong> Venue marshals reserve the right to decline entry for invalid, tampered, or previously admitted passes.</li>
                </ul>

                <div class="vn-lg-callout vn-lg-callout--amber">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                        <line x1="12" y1="9" x2="12" y2="13"></line>
                        <line x1="12" y1="17" x2="12.01" y2="17"></line>
                    </svg>
                    <div>
                        <strong>Vital Patron Warning:</strong> Never display or broadcast your raw ticket QR pass on open social networks prior to an event. Any individual possessing an image of your code may present it at the gate before your arrival.
                    </div>
                </div>
            </section>

            <section class="vn-lg-section" id="section-5">
                <h2 class="vn-lg-section-heading"><span class="vn-lg-section-num">§ 5.</span> Refunds, Cancellations &amp; Service Fees</h2>
                <p class="vn-lg-text">
                    Ticket reservations and cancellations are administered under established criteria:
                </p>
                <ul class="vn-lg-list">
                    <li><strong>Organizer Refund Policy:</strong> Each Host determines the refund parameters applicable to their event. If an occasion is postponed or cancelled, the Host bears sole obligation for issuing patron refunds.</li>
                    <li><strong>Non-Refundable Platform Fees:</strong> Technological processing fees collected at checkout cover cryptographic encryption and merchant clearance and are strictly non-refundable.</li>
                    <li><strong>Dispute Resolution:</strong> Patrons covenant to engage the Host or <?= e($siteName) ?> support desk prior to lodging financial chargebacks. Baseless chargebacks trigger immediate ledger termination.</li>
                </ul>
            </section>

            <section class="vn-lg-section" id="section-6">
                <h2 class="vn-lg-section-heading"><span class="vn-lg-section-num">§ 6.</span> Host Payouts, Escrow Holds &amp; Financial Security</h2>
                <p class="vn-lg-text">
                    Organizers receiving proceeds through <?= e($siteName) ?> acknowledge our settlement procedures:
                </p>
                <ul class="vn-lg-list">
                    <li><strong>Banking Accuracy:</strong> Organizers must supply authentic bank parameters (10-digit NUBAN) for automated Paystack disbursements. We bear no liability for delays stemming from inaccurate account entries.</li>
                    <li><strong>Escrow Safety Holds:</strong> Box office receipts may be held in protective escrow pending the orderly conclusion of an event, or longer if an event is flagged for attendee disputes or irregularities.</li>
                    <li><strong>Clawback Rights:</strong> If an event is cancelled or fraudulent tickets are discovered, <?= e($siteName) ?> reserves the full right to withhold or reverse pending transfers to ensure attendee compensation.</li>
                </ul>
            </section>

            <section class="vn-lg-section" id="section-7">
                <h2 class="vn-lg-section-heading"><span class="vn-lg-section-num">§ 7.</span> Prohibited Content &amp; Event Moderation</h2>
                <p class="vn-lg-text">
                    Hosts are prohibited from publishing events involving unlawful assemblies, deceptive financial schemes, hate speech, unauthorized ticket scalping, or intellectual property infringements. <?= e($siteName) ?> reserves the unconditional authority to remove non-compliant listings without notice.
                </p>
            </section>

            <section class="vn-lg-section" id="section-8">
                <h2 class="vn-lg-section-heading"><span class="vn-lg-section-num">§ 8.</span> Intellectual Property</h2>
                <p class="vn-lg-text">
                    All software, design tokens, marks, graphics, and architectural assets on <?= e($siteName) ?> remain protected under international copyright and intellectual property conventions. Duplication or reverse engineering without prior written consent is strictly barred.
                </p>
            </section>

            <section class="vn-lg-section" id="section-9">
                <h2 class="vn-lg-section-heading"><span class="vn-lg-section-num">§ 9.</span> Limitation of Liability</h2>
                <p class="vn-lg-text">
                    To the maximum threshold permitted by law, <?= e($siteName) ?>, its stewards, officers, and agents shall not be held liable for incidental, indirect, or consequential injuries resulting from physical participation at any listed assembly. In all matters, aggregate liability shall not exceed the net platform service fees retained for the specific transaction in dispute, or \$100 USD, whichever is lesser.
                </p>
            </section>

            <section class="vn-lg-section" id="section-10">
                <h2 class="vn-lg-section-heading"><span class="vn-lg-section-num">§ 10.</span> Indemnification</h2>
                <p class="vn-lg-text">
                    Hosts and attendees agree to defend, indemnify, and hold blameless <?= e($siteName) ?> against any third-party claims, property damages, liabilities, and legal expenditures arising from hosted events or violations of this Accord.
                </p>
            </section>

            <section class="vn-lg-section" id="section-11">
                <h2 class="vn-lg-section-heading"><span class="vn-lg-section-num">§ 11.</span> Force Majeure</h2>
                <p class="vn-lg-text">
                    <?= e($siteName) ?> shall not be held accountable for interruptions or performance failures precipitated by events beyond human anticipation, including extreme climatic disruptions, telecommunications failures, government actions, or civil disturbances.
                </p>
            </section>

            <section class="vn-lg-section" id="section-12">
                <h2 class="vn-lg-section-heading"><span class="vn-lg-section-num">§ 12.</span> Governing Jurisdiction</h2>
                <p class="vn-lg-text">
                    These Terms are interpreted and governed in harmony with the statutes of the operating jurisdiction, without regard to conflict of laws. All legal proceedings shall be submitted exclusively to courts of competent jurisdiction.
                </p>
            </section>

            <section class="vn-lg-section" id="section-13">
                <h2 class="vn-lg-section-heading"><span class="vn-lg-section-num">§ 13.</span> Account Termination</h2>
                <p class="vn-lg-text">
                    <?= e($siteName) ?> reserves the prerogative to freeze, suspend, or terminate any user or host account without advance notice for breach of these covenants, payment misconduct, or endangerment of attendee trust.
                </p>
            </section>

            <section class="vn-lg-section" id="section-14">
                <h2 class="vn-lg-section-heading"><span class="vn-lg-section-num">§ 14.</span> Legal Desk &amp; Notices</h2>
                <p class="vn-lg-text">
                    Inquiries, formal notices, or compliance correspondences concerning these Terms of Service may be addressed to our legal desk:
                </p>

                <div class="vn-lg-contact">
                    <div class="vn-lg-contact-info">
                        <strong><?= e($siteName) ?> Legal &amp; Compliance Bureau</strong>
                        <span>Direct Communications: <?= e($supportEmail) ?></span>
                    </div>
                    <a href="mailto:<?= e($supportEmail) ?>" class="vn-lg-contact-btn">
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                            <polyline points="22,6 12,13 2,6"></polyline>
                        </svg>
                        Inscribe Legal Desk
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
