<?php

/**
 * Terms of Service
 *
 * Clean, modern, responsive legal documentation for Eventlyy's platform rules,
 * ticket purchasing, host obligations, gate admission, and refund terms.
 *
 * @package Eventlyy
 * @subpackage Views/Terms
 * @author Celio Natti
 * @version 2.0.0
 */

$siteName = $siteName ?? site_setting('site_title', 'Eventlyy');
$supportEmail = $supportEmail ?? site_setting('support_email', 'support@eventlyy.com');
?>

@extends('layouts/main')

@section('title')
Terms of Service — <?= e($siteName) ?>
@endsection

@section('style')
<style>
/* ═════════════════════════════════════════════════════════════
   Legal Documentation Component System (.lg-*)
   Scoped, highly legible, accessible, fully responsive.
   ═════════════════════════════════════════════════════════════ */

.lg-wrapper {
    max-width: 1080px;
    margin: 0 auto;
    padding: 1.5rem 1rem 4rem;
}

/* ─── Hero Header Card ─────────────────────────────────────── */
.lg-hero {
    position: relative;
    border-radius: 28px;
    padding: clamp(2rem, 4vw, 3.25rem) clamp(1.5rem, 3.5vw, 3rem);
    background: radial-gradient(circle at 10% 15%, rgba(var(--brand), 0.12) 0%, transparent 45%),
                radial-gradient(circle at 90% 85%, rgba(16, 185, 129, 0.10) 0%, transparent 45%),
                linear-gradient(135deg, rgba(var(--card), 0.95), rgba(var(--card), 0.85));
    border: 1px solid rgba(var(--brand), 0.16);
    box-shadow: 0 16px 45px -15px rgba(var(--brand), 0.12), 0 2px 8px rgba(0, 0, 0, 0.03);
    margin-bottom: 2.25rem;
    overflow: hidden;
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
}

.lg-hero::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, rgb(var(--brand)), color-mix(in srgb, rgb(var(--brand)) 70%, rgb(var(--accent))));
}

.lg-top-bar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    flex-wrap: wrap;
    margin-bottom: 1.25rem;
}

.lg-breadcrumb {
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    font-size: 0.82rem;
    font-weight: 600;
    color: rgb(var(--muted));
}

.lg-breadcrumb a {
    color: rgb(var(--brand));
    text-decoration: none;
}

.lg-breadcrumb a:hover {
    text-decoration: underline;
}

.lg-tab-switcher {
    display: inline-flex;
    background: rgba(var(--muted), 0.08);
    border: 1px solid rgba(var(--muted), 0.16);
    border-radius: 999px;
    padding: 0.25rem;
    gap: 0.25rem;
}

.lg-tab-btn {
    padding: 0.35rem 0.85rem;
    border-radius: 999px;
    font-size: 0.78rem;
    font-weight: 700;
    color: rgb(var(--muted));
    text-decoration: none;
    transition: all var(--duration-fast, 0.15s) ease-out;
}

.lg-tab-btn:hover {
    color: rgb(var(--text));
}

.lg-tab-btn.active {
    background: rgb(var(--brand));
    color: #ffffff !important;
    box-shadow: 0 2px 8px rgba(var(--brand), 0.25);
}

.lg-eyebrow {
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    padding: 0.35rem 0.85rem;
    border-radius: 999px;
    font-size: 0.76rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: rgb(var(--brand));
    background: rgba(var(--brand), 0.1);
    border: 1px solid rgba(var(--brand), 0.2);
    margin-bottom: 0.75rem;
}

.lg-title {
    font-size: clamp(2rem, 4vw, 2.75rem);
    font-weight: 900;
    line-height: 1.15;
    letter-spacing: -0.03em;
    color: rgb(var(--text));
    margin: 0 0 0.85rem;
}

.lg-meta-row {
    display: flex;
    align-items: center;
    gap: 1.25rem;
    font-size: 0.85rem;
    color: rgb(var(--muted));
    font-weight: 600;
    flex-wrap: wrap;
    margin-bottom: 1.5rem;
}

.lg-meta-item {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
}

/* Plain English Summary Callout */
.lg-summary-box {
    background: rgba(var(--brand), 0.06);
    border: 1px solid rgba(var(--brand), 0.2);
    border-left: 4px solid rgb(var(--brand));
    border-radius: 14px;
    padding: 1.15rem 1.35rem;
}

.lg-summary-title {
    font-size: 0.85rem;
    font-weight: 800;
    color: rgb(var(--brand));
    text-transform: uppercase;
    letter-spacing: 0.04em;
    display: flex;
    align-items: center;
    gap: 0.45rem;
    margin-bottom: 0.5rem;
}

.lg-summary-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1rem;
    font-size: 0.85rem;
    color: rgb(var(--text));
    line-height: 1.5;
}

.lg-summary-item strong {
    color: rgb(var(--text));
    display: block;
    margin-bottom: 0.15rem;
}

/* ─── Two-Column Legal Layout ──────────────────────────────── */
.lg-layout {
    display: grid;
    grid-template-columns: 260px 1fr;
    gap: 2.25rem;
    align-items: start;
}

/* Sticky Table of Contents */
.lg-toc {
    position: sticky;
    top: 90px;
    background: rgb(var(--card));
    border: 1px solid rgba(var(--muted), 0.16);
    border-radius: 20px;
    padding: 1.25rem;
    box-shadow: 0 4px 18px rgba(0, 0, 0, 0.03);
    max-height: calc(100vh - 120px);
    overflow-y: auto;
}

.lg-toc-title {
    font-size: 0.8rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: rgb(var(--muted));
    margin: 0 0 0.85rem;
    padding-bottom: 0.5rem;
    border-bottom: 1px solid rgba(var(--muted), 0.12);
    display: flex;
    align-items: center;
    gap: 0.4rem;
}

.lg-toc-list {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.lg-toc-link {
    display: block;
    padding: 0.4rem 0.65rem;
    border-radius: 8px;
    font-size: 0.82rem;
    font-weight: 600;
    color: rgb(var(--muted));
    text-decoration: none;
    line-height: 1.35;
    transition: all var(--duration-fast, 0.15s) ease-out;
}

.lg-toc-link:hover {
    color: rgb(var(--brand));
    background: rgba(var(--brand), 0.08);
}

.lg-toc-link.active {
    color: rgb(var(--brand));
    background: rgba(var(--brand), 0.12);
    font-weight: 700;
}

/* ─── Legal Document Body ──────────────────────────────────── */
.lg-document {
    background: rgb(var(--card));
    border: 1px solid rgba(var(--muted), 0.16);
    border-radius: 24px;
    padding: 2.5rem clamp(1.5rem, 3.5vw, 3rem);
    box-shadow: 0 4px 24px rgba(0, 0, 0, 0.03);
}

.lg-section {
    padding-bottom: 2rem;
    margin-bottom: 2rem;
    border-bottom: 1px solid rgba(var(--muted), 0.1);
    scroll-margin-top: 100px;
}

.lg-section:last-of-type {
    border-bottom: none;
    padding-bottom: 0;
    margin-bottom: 0;
}

.lg-section-heading {
    font-size: 1.35rem;
    font-weight: 800;
    letter-spacing: -0.02em;
    color: rgb(var(--text));
    margin: 0 0 0.85rem;
    display: flex;
    align-items: baseline;
    gap: 0.5rem;
}

.lg-section-num {
    font-size: 1.15rem;
    color: rgb(var(--brand));
}

.lg-text {
    font-size: 0.95rem;
    line-height: 1.75;
    color: rgb(var(--text));
    margin: 0 0 1rem;
}

.lg-text:last-child {
    margin-bottom: 0;
}

.lg-list {
    margin: 0.75rem 0 1rem;
    padding-left: 0;
    list-style: none;
    display: flex;
    flex-direction: column;
    gap: 0.65rem;
}

.lg-list li {
    position: relative;
    padding-left: 1.5rem;
    font-size: 0.93rem;
    line-height: 1.7;
    color: rgb(var(--text));
}

.lg-list li::before {
    content: '•';
    position: absolute;
    left: 0.35rem;
    color: rgb(var(--brand));
    font-size: 1.25rem;
    line-height: 1.2;
}

.lg-list li strong {
    color: rgb(var(--text));
}

/* Callout Notice */
.lg-callout {
    padding: 0.95rem 1.2rem;
    border-radius: 12px;
    font-size: 0.88rem;
    line-height: 1.6;
    margin: 1.15rem 0;
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
}

.lg-callout svg {
    flex-shrink: 0;
    margin-top: 0.2rem;
}

.lg-callout--warning {
    background: rgba(245, 158, 11, 0.08);
    border: 1px solid rgba(245, 158, 11, 0.25);
    color: rgb(var(--text));
}

.lg-callout--info {
    background: rgba(var(--brand), 0.08);
    border: 1px solid rgba(var(--brand), 0.25);
    color: rgb(var(--text));
}

/* Contact Desk Card */
.lg-contact-card {
    background: rgba(var(--muted), 0.05);
    border: 1px solid rgba(var(--muted), 0.14);
    border-radius: 16px;
    padding: 1.25rem 1.5rem;
    margin-top: 1rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    flex-wrap: wrap;
}

.lg-contact-info strong {
    font-size: 1rem;
    color: rgb(var(--text));
    display: block;
    margin-bottom: 0.2rem;
}

.lg-contact-info span {
    font-size: 0.85rem;
    color: rgb(var(--muted));
}

.lg-contact-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    height: 42px;
    padding: 0 1.25rem;
    border-radius: 12px;
    font-size: 0.88rem;
    font-weight: 700;
    background: rgb(var(--brand));
    color: #ffffff !important;
    text-decoration: none;
    box-shadow: 0 4px 12px rgba(var(--brand), 0.25);
    transition: all var(--duration-fast, 0.15s) ease-out;
}

.lg-contact-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 18px rgba(var(--brand), 0.35);
}

/* ═════════════════════════════════════════════════════════════
   Responsive Design Breakpoints
   ═════════════════════════════════════════════════════════════ */

/* Tablet & Below (max 960px) */
@media (max-width: 960px) {
    .lg-layout {
        grid-template-columns: 1fr;
        gap: 1.5rem;
    }

    .lg-toc {
        position: static;
        max-height: none;
    }

    .lg-summary-grid {
        grid-template-columns: 1fr;
        gap: 0.75rem;
    }
}

/* Mobile (max 680px) */
@media (max-width: 680px) {
    .lg-wrapper {
        padding: 1rem 0.75rem 3rem;
    }

    .lg-hero {
        padding: 1.75rem 1.15rem;
        border-radius: 22px;
        margin-bottom: 1.75rem;
    }

    .lg-document {
        padding: 1.5rem 1.15rem;
        border-radius: 20px;
    }

    .lg-section-heading {
        font-size: 1.18rem;
    }

    .lg-text,
    .lg-list li {
        font-size: 0.92rem;
    }

    .lg-contact-card {
        flex-direction: column;
        align-items: stretch;
        text-align: center;
    }

    .lg-contact-btn {
        justify-content: center;
        width: 100%;
    }
}
</style>
@endsection

@section('content')
<div class="lg-wrapper">

    <!-- ═══ Hero Header Card ═══ -->
    <header class="lg-hero">
        <div class="lg-top-bar">
            <nav class="lg-breadcrumb" aria-label="Breadcrumb">
                <a href="{{ route('/') }}">Home</a>
                <span>&bull;</span>
                <span>Legal</span>
                <span>&bull;</span>
                <span>Terms of Service</span>
            </nav>

            <div class="lg-tab-switcher">
                <a href="{{ route('terms') }}" class="lg-tab-btn active">Terms of Service</a>
                <a href="{{ route('privacy') }}" class="lg-tab-btn">Privacy Policy</a>
            </div>
        </div>

        <span class="lg-eyebrow">
            <span>📜</span> Platform Rules &amp; Agreements
        </span>

        <h1 class="lg-title">Terms of Service</h1>

        <div class="lg-meta-row">
            <span class="lg-meta-item">📅 Last updated: August 13, 2026</span>
            <span class="lg-meta-item">&bull;</span>
            <span class="lg-meta-item">⏱️ ~6 min read</span>
            <span class="lg-meta-item">&bull;</span>
            <span class="lg-meta-item">🌐 <?= e($siteName) ?> Ecosystem</span>
        </div>

        <!-- Plain English Summary -->
        <div class="lg-summary-box">
            <div class="lg-summary-title">
                <span>⚡</span> Key Takeaways in Plain English
            </div>
            <div class="lg-summary-grid">
                <div class="lg-summary-item">
                    <strong>🎟️ Single-Use QR Passes</strong>
                    <span>Each pass admits one attendee. Duplicating or scalping passes is strictly prohibited.</span>
                </div>
                <div class="lg-summary-item">
                    <strong>💳 Organizer Refund Policies</strong>
                    <span>Event hosts set refund rules for their events. Platform ticketing fees are non-refundable.</span>
                </div>
                <div class="lg-summary-item">
                    <strong>🛡️ Technology Intermediary</strong>
                    <span><?= e($siteName) ?> provides ticketing tech. Hosts are responsible for venue safety &amp; event production.</span>
                </div>
            </div>
        </div>
    </header>

    <!-- ═══ Main Legal Layout ═══ -->
    <div class="lg-layout">

        <!-- Sticky Table of Contents -->
        <aside class="lg-toc">
            <div class="lg-toc-title">
                <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="8" y1="6" x2="21" y2="6"></line>
                    <line x1="8" y1="12" x2="21" y2="12"></line>
                    <line x1="8" y1="18" x2="21" y2="18"></line>
                    <line x1="3" y1="6" x2="3.01" y2="6"></line>
                    <line x1="3" y1="12" x2="3.01" y2="12"></line>
                    <line x1="3" y1="18" x2="3.01" y2="18"></line>
                </svg>
                Table of Contents
            </div>
            <ul class="lg-toc-list">
                <li><a href="#section-1" class="lg-toc-link">1. Agreement to Terms</a></li>
                <li><a href="#section-2" class="lg-toc-link">2. Platform Role &amp; Status</a></li>
                <li><a href="#section-3" class="lg-toc-link">3. User &amp; Host Accounts</a></li>
                <li><a href="#section-4" class="lg-toc-link">4. Ticket Sales &amp; Gate Entry</a></li>
                <li><a href="#section-5" class="lg-toc-link">5. Refunds &amp; Cancellations</a></li>
                <li><a href="#section-6" class="lg-toc-link">6. Host Payouts &amp; Escrow</a></li>
                <li><a href="#section-7" class="lg-toc-link">7. Prohibited Content</a></li>
                <li><a href="#section-8" class="lg-toc-link">8. Intellectual Property</a></li>
                <li><a href="#section-9" class="lg-toc-link">9. Limitation of Liability</a></li>
                <li><a href="#section-10" class="lg-toc-link">10. Indemnification</a></li>
                <li><a href="#section-11" class="lg-toc-link">11. Force Majeure</a></li>
                <li><a href="#section-12" class="lg-toc-link">12. Governing Law</a></li>
                <li><a href="#section-13" class="lg-toc-link">13. Account Termination</a></li>
                <li><a href="#section-14" class="lg-toc-link">14. Contact &amp; Support</a></li>
            </ul>
        </aside>

        <!-- Document Content Card -->
        <main class="lg-document">

            <section class="lg-section" id="section-1">
                <h2 class="lg-section-heading"><span class="lg-section-num">1.</span> Agreement to Terms</h2>
                <p class="lg-text">
                    By accessing or using the <strong><?= e($siteName) ?></strong> website, mobile applications, organizer dashboard, or digital ticketing infrastructure, you enter into a legally binding agreement governed by these Terms of Service and all applicable statutory regulations. If you do not agree with any provision herein, you must cease using our services immediately.
                </p>
            </section>

            <section class="lg-section" id="section-2">
                <h2 class="lg-section-heading"><span class="lg-section-num">2.</span> Platform Role &amp; Intermediary Status</h2>
                <p class="lg-text">
                    <?= e($siteName) ?> operates strictly as an independent technology marketplace connecting event organizers ("Hosts") with event attendees ("Buyers"). Unless an event is explicitly badged as an official company gathering:
                </p>
                <ul class="lg-list">
                    <li><?= e($siteName) ?> is not the organizer, promoter, seller of record, or creator of third-party listed events.</li>
                    <li><?= e($siteName) ?> is not liable for venue safety, sound quality, performance changes, cancellations, or representations made by organizers.</li>
                    <li>All contracts for ticket admission and event access exist solely between the Attendee and the respective Event Organizer.</li>
                </ul>
            </section>

            <section class="lg-section" id="section-3">
                <h2 class="lg-section-heading"><span class="lg-section-num">3.</span> User &amp; Host Accounts</h2>
                <p class="lg-text">
                    Registered users and organizers must provide true, complete, and updated information during account setup and ticket checkout. You are solely responsible for maintaining the confidentiality of your authentication credentials and for all actions taken under your account. You agree to notify us immediately of any unauthorized breach.
                </p>
            </section>

            <section class="lg-section" id="section-4">
                <h2 class="lg-section-heading"><span class="lg-section-num">4.</span> Ticket Sales, QR Codes &amp; Gate Admission</h2>
                <p class="lg-text">
                    Admission passes issued on <?= e($siteName) ?> contain cryptographic 256-bit QR codes designed for rapid optical scanning at entry gates. The following conditions strictly apply:
                </p>
                <ul class="lg-list">
                    <li><strong>Single Entry Rule:</strong> Each QR code is uniquely encrypted and valid for a one-time gate scan. Upon successful check-in, the pass is marked as used and cannot be reused.</li>
                    <li><strong>No Unauthorized Resale:</strong> Reselling tickets above face value, duplicating passes, or distributing duplicated screenshots through unauthorized secondary channels is prohibited and void.</li>
                    <li><strong>Admission Rights:</strong> Event hosts reserve the right to refuse entry for invalid, tampered, or already-checked-in QR passes.</li>
                </ul>

                <div class="lg-callout lg-callout--warning">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="#f59e0b" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                        <line x1="12" y1="9" x2="12" y2="13"></line>
                        <line x1="12" y1="17" x2="12.01" y2="17"></line>
                    </svg>
                    <div>
                        <strong>Important Security Reminder:</strong> Never share your raw QR pass on public social media before an event. Anyone possessing a clear photo of your QR code could scan it at the gate before you arrive.
                    </div>
                </div>
            </section>

            <section class="lg-section" id="section-5">
                <h2 class="lg-section-heading"><span class="lg-section-num">5.</span> Refunds, Cancellations &amp; Service Fees</h2>
                <p class="lg-text">
                    Ticketing transactions and refund resolutions are subject to the following rules:
                </p>
                <ul class="lg-list">
                    <li><strong>Organizer Refund Policy:</strong> Event Organizers set their own cancellation and refund terms. If an event is postponed or cancelled, the host is solely responsible for authorizing and distributing attendee refunds.</li>
                    <li><strong>Non-Refundable Platform Fees:</strong> Administrative ticketing fees and payment gateway fees collected during checkout cover encrypted processing and cannot be refunded.</li>
                    <li><strong>Dispute Resolution:</strong> Attendees agree to contact the Event Organizer or <?= e($siteName) ?> support desk prior to initiating credit card chargebacks. Fraudulent chargeback filings will result in permanent account suspension.</li>
                </ul>
            </section>

            <section class="lg-section" id="section-6">
                <h2 class="lg-section-heading"><span class="lg-section-num">6.</span> Host Payouts, Escrow Holds &amp; Financial Security</h2>
                <p class="lg-text">
                    Organizers who collect revenue via <?= e($siteName) ?> agree to the following settlement mechanisms:
                </p>
                <ul class="lg-list">
                    <li><strong>Banking Accuracy:</strong> Hosts must supply valid bank account details (10-digit NUBAN) for Paystack automated disbursements. <?= e($siteName) ?> is not liable for transfer delays caused by inaccurate host bank info.</li>
                    <li><strong>Escrow Safety Holds:</strong> Ticket proceeds may be held in escrow until the successful conclusion of an event, or longer if an event is flagged for organizer disputes, high refund velocity, or venue complaints.</li>
                    <li><strong>Clawback Rights:</strong> If an event is cancelled or fraudulent tickets are sold, <?= e($siteName) ?> reserves the legal right to reverse pending payouts or withhold future balances to cover buyer refunds.</li>
                </ul>
            </section>

            <section class="lg-section" id="section-7">
                <h2 class="lg-section-heading"><span class="lg-section-num">7.</span> Prohibited Content &amp; Event Moderation</h2>
                <p class="lg-text">
                    Organizers are prohibited from publishing events involving fraudulent schemes, hate speech, unauthorized ticket scalping, dangerous illegal gatherings, or copyright infringement. <?= e($siteName) ?> reserves the right to unpublish events, freeze ticketing, and ban organizers without prior notice.
                </p>
            </section>

            <section class="lg-section" id="section-8">
                <h2 class="lg-section-heading"><span class="lg-section-num">8.</span> Intellectual Property</h2>
                <p class="lg-text">
                    All software, design tokens, brand marks, codebase, graphics, and UI layouts on <?= e($siteName) ?> are protected under intellectual property laws. You may not scrape, decompile, duplicate, or commercially exploit platform software without express written authorization.
                </p>
            </section>

            <section class="lg-section" id="section-9">
                <h2 class="lg-section-heading"><span class="lg-section-num">9.</span> Limitation of Liability</h2>
                <p class="lg-text">
                    To the maximum extent permitted by applicable law, <?= e($siteName) ?>, its officers, directors, and employees shall not be liable for any indirect, incidental, or consequential damages resulting from your attendance at any listed event. In all circumstances, our maximum aggregate liability shall not exceed the net platform service fees retained by us for the specific transaction in dispute, or $100 USD, whichever is less.
                </p>
            </section>

            <section class="lg-section" id="section-10">
                <h2 class="lg-section-heading"><span class="lg-section-num">10.</span> Indemnification</h2>
                <p class="lg-text">
                    Organizers and attendees agree to indemnify, defend, and hold harmless <?= e($siteName) ?> from any claims, liabilities, damages, and legal expenses arising from listed events, attendee bodily injuries, venue property damage, or contractual disputes.
                </p>
            </section>

            <section class="lg-section" id="section-11">
                <h2 class="lg-section-heading"><span class="lg-section-num">11.</span> Force Majeure</h2>
                <p class="lg-text">
                    <?= e($siteName) ?> shall not be held liable for platform interruptions or event delivery failures caused by events outside reasonable control, including natural disasters, severe weather, grid outages, government restrictions, or pandemics.
                </p>
            </section>

            <section class="lg-section" id="section-12">
                <h2 class="lg-section-heading"><span class="lg-section-num">12.</span> Governing Law &amp; Jurisdiction</h2>
                <p class="lg-text">
                    These Terms are governed by and construed in accordance with the laws of the operational jurisdiction, without regard to conflict of law principles. Any legal proceedings shall be resolved in the competent courts having jurisdiction over the company.
                </p>
            </section>

            <section class="lg-section" id="section-13">
                <h2 class="lg-section-heading"><span class="lg-section-num">13.</span> Account Termination</h2>
                <p class="lg-text">
                    <?= e($siteName) ?> reserves the right to suspend or permanently terminate any user or host account immediately, without prior notice, for breach of these terms, fraudulent transactions, or abuse of attendees.
                </p>
            </section>

            <section class="lg-section" id="section-14">
                <h2 class="lg-section-heading"><span class="lg-section-num">14.</span> Contact &amp; Support</h2>
                <p class="lg-text">
                    If you have questions, feedback, or legal inquiries concerning these Terms of Service, please contact our compliance and support team:
                </p>

                <div class="lg-contact-card">
                    <div class="lg-contact-info">
                        <strong><?= e($siteName) ?> Legal &amp; Compliance Desk</strong>
                        <span>Direct inquiries: <?= e($supportEmail) ?></span>
                    </div>
                    <a href="mailto:<?= e($supportEmail) ?>" class="lg-contact-btn">
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                            <polyline points="22,6 12,13 2,6"></polyline>
                        </svg>
                        Contact Legal Team
                    </a>
                </div>
            </section>

        </main>
    </div>
</div>
@endsection

@section('script')
<script>
// Active TOC Scrollspy
document.addEventListener('DOMContentLoaded', function() {
    const links = document.querySelectorAll('.lg-toc-link');
    const sections = document.querySelectorAll('.lg-section');

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