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
 * @version 3.0.0
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
            <span>✦</span> YOUR DATA, YOUR RULES &bull; ISSUE 2026
        </span>

        <h1 class="vn-lg-title">Privacy Policy &amp; How We Handle Your Data</h1>

        <div class="vn-lg-meta">
            <span class="vn-lg-meta-item">📅 Last refreshed: August 13, 2026</span>
            <span class="vn-lg-meta-item">&bull;</span>
            <span class="vn-lg-meta-item">⏱️ ~5 min read</span>
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
                    <span>Period. No ads. No third-party trackers. No selling or renting your personal info to data brokers. Ever.</span>
                </div>
                <div class="vn-lg-summary-card">
                    <strong>🎟️ Hosts Only See What They Need</strong>
                    <span>Door staff and event hosts only get the essentials (name, ticket tier, scan status) to verify your entry. Nothing else.</span>
                </div>
                <div class="vn-lg-summary-card">
                    <strong>💳 Payments Stay Encrypted</strong>
                    <span>Card details are processed exclusively by PCI-DSS certified gateways (Paystack). We never see or store your raw card numbers.</span>
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
                <span style="font-size: 0.85rem; color: var(--vn-brass); font-family: var(--vn-font-serif); font-style: italic;">§ 1–12</span>
            </div>
            <ul class="vn-lg-toc-list">
                <li><a href="#section-1" class="vn-lg-toc-link">§ 1. Introduction &amp; Scope</a></li>
                <li><a href="#section-2" class="vn-lg-toc-link">§ 2. Information Collected</a></li>
                <li><a href="#section-3" class="vn-lg-toc-link">§ 3. Purpose of Processing</a></li>
                <li><a href="#section-4" class="vn-lg-toc-link">§ 4. Organizer Data Sharing</a></li>
                <li><a href="#section-5" class="vn-lg-toc-link">§ 5. Financial Cryptography</a></li>
                <li><a href="#section-6" class="vn-lg-toc-link">§ 6. QR Pass &amp; Gate Logs</a></li>
                <li><a href="#section-7" class="vn-lg-toc-link">§ 7. Cookies &amp; Sessions</a></li>
                <li><a href="#section-8" class="vn-lg-toc-link">§ 8. Archival Data Retention</a></li>
                <li><a href="#section-9" class="vn-lg-toc-link">§ 9. Your Statutory Rights</a></li>
                <li><a href="#section-10" class="vn-lg-toc-link">§ 10. Protection of Minors</a></li>
                <li><a href="#section-11" class="vn-lg-toc-link">§ 11. Amendments &amp; Notices</a></li>
                <li><a href="#section-12" class="vn-lg-toc-link">§ 12. Compliance Desk</a></li>
            </ul>
        </aside>

        <!-- Document Content Codex -->
        <main class="vn-lg-document">

            <section class="vn-lg-section" id="section-1">
                <h2 class="vn-lg-section-heading"><span class="vn-lg-section-num">§ 1.</span> Introduction &amp; Scope</h2>
                <p class="vn-lg-text">
                    <strong><?= e($siteName) ?></strong> ("we," "us," or "the Platform") holds sacred the trust and privacy of all patrons, hosts, and venue personnel. This document articulates our rigorous protocols regarding the collection, safeguarding, and controlled transmission of personal data across our digital registry, ticket checkout desk, and optical gate verification scanners.
                </p>
            </section>

            <section class="vn-lg-section" id="section-2">
                <h2 class="vn-lg-section-heading"><span class="vn-lg-section-num">§ 2.</span> Information Collected</h2>
                <p class="vn-lg-text">
                    We collect personal records provided voluntarily or generated through platform operation:
                </p>
                <ul class="vn-lg-list">
                    <li><strong>Account Credentials:</strong> Full name, electronic mail address, telephone contact, and cryptographically salted password hashes.</li>
                    <li><strong>Guest Checkout Records:</strong> Attendee name and delivery email provided during expedited guest ticket acquisitions.</li>
                    <li><strong>Organizer Ledgers:</strong> Host organization name, banking settlement parameters (10-digit NUBAN), event classifications, and tier inventory.</li>
                    <li><strong>Gate Check-In Telemetry:</strong> Encrypted pass tokens, scan timestamps, operator station identifiers, and entry admission logs.</li>
                    <li><strong>Technical Diagnostics:</strong> IP address, browser signatures, TLS session cookies, and request integrity tokens.</li>
                </ul>
            </section>

            <section class="vn-lg-section" id="section-3">
                <h2 class="vn-lg-section-heading"><span class="vn-lg-section-num">§ 3.</span> Purpose of Processing</h2>
                <p class="vn-lg-text">
                    Personal data is processed strictly for authenticated administrative, security, and contractual requirements:
                </p>
                <ul class="vn-lg-list">
                    <li>Issuing cryptographically sealed digital admission passes with high-contrast optical QR codes.</li>
                    <li>Executing settlement transactions, promotional coupon redemptions, and organizer disbursements.</li>
                    <li>Enabling rapid gate validation and attendance verification by venue marshals.</li>
                    <li>Safeguarding against duplicate ticket counterfeiting, unauthorized scalping, and fraud.</li>
                    <li>Transmitting order receipts, schedule adjustments, and customer care correspondences.</li>
                </ul>
            </section>

            <section class="vn-lg-section" id="section-4">
                <h2 class="vn-lg-section-heading"><span class="vn-lg-section-num">§ 4.</span> Organizer Data Sharing</h2>
                <p class="vn-lg-text">
                    When reserving or purchasing admission for an event on <?= e($siteName) ?>, essential attendee parameters (name, contact email, quantity, and tier classification) are transmitted to the verified Event Organizer and designated door staff. This delegation exists solely to:
                </p>
                <ul class="vn-lg-list">
                    <li>Authenticate physical entry at venue turnstiles and perimeter gates.</li>
                    <li>Communicate safety bulletins, venue parking guidance, or itinerary updates.</li>
                    <li>Fulfill statutory municipal occupancy and fire safety requirements.</li>
                </ul>
                <div class="vn-lg-callout vn-lg-callout--forest">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                    <div>
                        <strong>Solemn Archival Guarantee:</strong> Event hosts are contractually prohibited from deploying attendee records for unsolicited external advertising. We do not license patron records to third-party data conglomerates.
                    </div>
                </div>
            </section>

            <section class="vn-lg-section" id="section-5">
                <h2 class="vn-lg-section-heading"><span class="vn-lg-section-num">§ 5.</span> Financial Cryptography &amp; Settlement</h2>
                <p class="vn-lg-text">
                    All financial operations are routed directly through PCI-DSS Level 1 certified clearinghouses (including Paystack). <?= e($siteName) ?> never stores raw credit/debit card numbers, CVVs, or bank security PINs on local or cloud servers. All transactions traverse TLS 1.3 encrypted conduits.
                </p>
            </section>

            <section class="vn-lg-section" id="section-6">
                <h2 class="vn-lg-section-heading"><span class="vn-lg-section-num">§ 6.</span> Gate Check-In &amp; QR Security</h2>
                <p class="vn-lg-text">
                    Every ticket is stamped with an individualized, tamper-evident cryptographic QR code. When verified at entry doors, the precise validation timestamp and scanner station ID are recorded to ensure each pass permits entry exactly once.
                </p>
            </section>

            <section class="vn-lg-section" id="section-7">
                <h2 class="vn-lg-section-heading"><span class="vn-lg-section-num">§ 7.</span> Cookies &amp; Session Tokens</h2>
                <p class="vn-lg-text">
                    We deploy strictly necessary cookies and local storage tokens to preserve authentication sessions, retain cart state during booking, persist light/dark parchment themes, and maintain Cross-Site Request Forgery (CSRF) defense shields.
                </p>
            </section>

            <section class="vn-lg-section" id="section-8">
                <h2 class="vn-lg-section-heading"><span class="vn-lg-section-num">§ 8.</span> Archival Data Retention</h2>
                <p class="vn-lg-text">
                    Attendee ledgers and transaction journals are preserved only as long as necessary to fulfill ticket orders, reconcile refund periods, maintain audit compliance, and satisfy statutory tax obligations. Expired records are routinely purged or anonymized.
                </p>
            </section>

            <section class="vn-lg-section" id="section-9">
                <h2 class="vn-lg-section-heading"><span class="vn-lg-section-num">§ 9.</span> Your Statutory Rights</h2>
                <p class="vn-lg-text">
                    Under applicable data protection statutes (including GDPR and consumer privacy directives), you possess unalienable rights:
                </p>
                <ul class="vn-lg-list">
                    <li><strong>Right to Inspection:</strong> Request an extracted ledger of all personal data held under your name.</li>
                    <li><strong>Right of Rectification:</strong> Amend or correct incomplete or outdated personal information.</li>
                    <li><strong>Right to Erasure:</strong> Request the irreversible deletion of your account and records via Account Security.</li>
                    <li><strong>Right to Restrict:</strong> Unsubscribe from non-essential promotional bulletins at your discretion.</li>
                </ul>
            </section>

            <section class="vn-lg-section" id="section-10">
                <h2 class="vn-lg-section-heading"><span class="vn-lg-section-num">§ 10.</span> Protection of Minors</h2>
                <p class="vn-lg-text">
                    <?= e($siteName) ?> is engineered for general audiences and does not knowingly collect records from persons under 13 years of age without explicit parental authorization. Inadvertently collected minor records are purged upon notice.
                </p>
            </section>

            <section class="vn-lg-section" id="section-11">
                <h2 class="vn-lg-section-heading"><span class="vn-lg-section-num">§ 11.</span> Amendments &amp; Notices</h2>
                <p class="vn-lg-text">
                    We reserve the prerogative to revise this Privacy Accord to mirror technological enhancements, security protocols, or statutory updates. Material adjustments are posted with an updated inscription timestamp.
                </p>
            </section>

            <section class="vn-lg-section" id="section-12">
                <h2 class="vn-lg-section-heading"><span class="vn-lg-section-num">§ 12.</span> Compliance Desk &amp; Inquiries</h2>
                <p class="vn-lg-text">
                    Should you wish to exercise statutory data prerogatives or present inquiries to our data protection officer, please address our compliance bureau:
                </p>

                <div class="vn-lg-contact">
                    <div class="vn-lg-contact-info">
                        <strong><?= e($siteName) ?> Data Protection &amp; Compliance Bureau</strong>
                        <span>Official Inquiries: <?= e($supportEmail) ?></span>
                    </div>
                    <a href="mailto:<?= e($supportEmail) ?>" class="vn-lg-contact-btn">
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                            <polyline points="22,6 12,13 2,6"></polyline>
                        </svg>
                        Inscribe Compliance Desk
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
