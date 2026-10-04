<?php

/**
 * Privacy Policy
 *
 * Clean, modern, responsive legal documentation explaining data collection,
 * storage, gate QR scanner logs, host data sharing, and user privacy rights.
 *
 * @package Eventlyy
 * @subpackage Views/Privacy
 * @author Celio Natti
 * @version 2.0.0
 */

$siteName = $siteName ?? site_setting('site_title', 'Eventlyy');
$supportEmail = $supportEmail ?? site_setting('support_email', 'support@eventlyy.com');
?>

@extends('layouts/main')

@section('title')
Privacy Policy — <?= e($siteName) ?>
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
    background: rgba(16, 185, 129, 0.06);
    border: 1px solid rgba(16, 185, 129, 0.2);
    border-left: 4px solid #10b981;
    border-radius: 14px;
    padding: 1.15rem 1.35rem;
}

.lg-summary-title {
    font-size: 0.85rem;
    font-weight: 800;
    color: #10b981;
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

.lg-callout--success {
    background: rgba(16, 185, 129, 0.08);
    border: 1px solid rgba(16, 185, 129, 0.25);
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
                <span>Privacy Policy</span>
            </nav>

            <div class="lg-tab-switcher">
                <a href="{{ route('terms') }}" class="lg-tab-btn">Terms of Service</a>
                <a href="{{ route('privacy') }}" class="lg-tab-btn active">Privacy Policy</a>
            </div>
        </div>

        <span class="lg-eyebrow">
            <span>🔒</span> Privacy &amp; Data Protection
        </span>

        <h1 class="lg-title">Privacy Policy</h1>

        <div class="lg-meta-row">
            <span class="lg-meta-item">📅 Last updated: August 13, 2026</span>
            <span class="lg-meta-item">&bull;</span>
            <span class="lg-meta-item">⏱️ ~5 min read</span>
            <span class="lg-meta-item">&bull;</span>
            <span class="lg-meta-item">🌐 <?= e($siteName) ?> Ecosystem</span>
        </div>

        <!-- Plain English Summary -->
        <div class="lg-summary-box">
            <div class="lg-summary-title">
                <span>🛡️</span> Your Privacy in Plain English
            </div>
            <div class="lg-summary-grid">
                <div class="lg-summary-item">
                    <strong>🚫 No Data Selling</strong>
                    <span>We never sell, rent, or trade your personal information to third-party ad networks.</span>
                </div>
                <div class="lg-summary-item">
                    <strong>🎟️ Limited Host Sharing</strong>
                    <span>Only basic ticket details (name, pass type) are shared with event organizers for venue check-in.</span>
                </div>
                <div class="lg-summary-item">
                    <strong>💳 Encrypted Checkout</strong>
                    <span>Payments are processed directly by certified PCI-DSS gateways (Paystack). We never store raw card numbers.</span>
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
                <li><a href="#section-1" class="lg-toc-link">1. Introduction</a></li>
                <li><a href="#section-2" class="lg-toc-link">2. Information We Collect</a></li>
                <li><a href="#section-3" class="lg-toc-link">3. How We Use Information</a></li>
                <li><a href="#section-4" class="lg-toc-link">4. Sharing with Organizers</a></li>
                <li><a href="#section-5" class="lg-toc-link">5. Payment Security</a></li>
                <li><a href="#section-6" class="lg-toc-link">6. Gate QR Verification</a></li>
                <li><a href="#section-7" class="lg-toc-link">7. Cookies &amp; Storage</a></li>
                <li><a href="#section-8" class="lg-toc-link">8. Data Retention</a></li>
                <li><a href="#section-9" class="lg-toc-link">9. Your Privacy Rights</a></li>
                <li><a href="#section-10" class="lg-toc-link">10. Children's Privacy</a></li>
                <li><a href="#section-11" class="lg-toc-link">11. Policy Updates</a></li>
                <li><a href="#section-12" class="lg-toc-link">12. Contact &amp; Compliance</a></li>
            </ul>
        </aside>

        <!-- Document Content Card -->
        <main class="lg-document">

            <section class="lg-section" id="section-1">
                <h2 class="lg-section-heading"><span class="lg-section-num">1.</span> Introduction</h2>
                <p class="lg-text">
                    <strong><?= e($siteName) ?></strong> ("we," "us," or "our") values and respects your personal privacy. This Privacy Policy details how we collect, process, safeguard, and disclose your personal records when you use our website, mobile interface, ticket purchasing checkout, host management portal, and venue gate verification scanner apps.
                </p>
            </section>

            <section class="lg-section" id="section-2">
                <h2 class="lg-section-heading"><span class="lg-section-num">2.</span> Information We Collect</h2>
                <p class="lg-text">
                    We collect personal information that you provide directly or that is generated automatically when interacting with our ecosystem:
                </p>
                <ul class="lg-list">
                    <li><strong>Account &amp; Registration Details:</strong> Full name, verified email address, phone number, and encrypted passwords.</li>
                    <li><strong>Guest Checkout Records:</strong> Name, contact email, and telephone provided during rapid guest ticket booking without an account.</li>
                    <li><strong>Host &amp; Organizer Credentials:</strong> Organization name, payout bank details (10-digit NUBAN), event descriptions, venue locations, and ticket tier specifications.</li>
                    <li><strong>Gate Pass &amp; Scanner Telemetry:</strong> Unique ticket QR codes, gate entry timestamps, validation status, and scanner operator device IDs.</li>
                    <li><strong>Technical &amp; Analytics Data:</strong> IP address, device fingerprints, browser version, session state cookies, and navigational flow logs.</li>
                </ul>
            </section>

            <section class="lg-section" id="section-3">
                <h2 class="lg-section-heading"><span class="lg-section-num">3.</span> How We Use Your Information</h2>
                <p class="lg-text">
                    We process personal data strictly for legitimate operational, security, and contractual purposes:
                </p>
                <ul class="lg-list">
                    <li>Generating, issuing, and dispatching digital admission passes with encrypted optical QR codes.</li>
                    <li>Processing ticket payments, coupon discounts, organizer disbursements, and authorized buyer refunds.</li>
                    <li>Facilitating rapid gate check-in verification for event staff and security personnel.</li>
                    <li>Preventing ticket scalping, counterfeit passes, duplicate entry attempts, and payment fraud.</li>
                    <li>Delivering transactional emails, admission confirmations, event schedule notices, and customer support responses.</li>
                </ul>
            </section>

            <section class="lg-section" id="section-4">
                <h2 class="lg-section-heading"><span class="lg-section-num">4.</span> Data Sharing with Event Organizers (Hosts)</h2>
                <p class="lg-text">
                    When you purchase a ticket or register for an event on <?= e($siteName) ?>, we share limited attendee information (attendee name, email address, quantity, and ticket tier) with the designated Event Organizer and their assigned staff. This data exchange enables hosts to:
                </p>
                <ul class="lg-list">
                    <li>Verify attendee admission at venue turnstiles and entry doors.</li>
                    <li>Communicate essential event logistics, parking guidance, schedule changes, or safety guidelines.</li>
                    <li>Comply with municipal fire code safety regulations and capacity headcount limits.</li>
                </ul>
                <div class="lg-callout lg-callout--success">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="#10b981" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                    <div>
                        <strong>Our Commitment:</strong> We strictly prohibit event hosts from using attendee contact information for third-party spam. We never sell or license attendee registries to external marketing brokers.
                    </div>
                </div>
            </section>

            <section class="lg-section" id="section-5">
                <h2 class="lg-section-heading"><span class="lg-section-num">5.</span> Payment Processing &amp; Financial Security</h2>
                <p class="lg-text">
                    All payment processing is handled through certified PCI-DSS Level 1 compliant financial payment gateways (including Paystack). <?= e($siteName) ?> does not store complete debit or credit card numbers, CVVs, or card PINs on our servers. Transactions are transmitted using TLS 1.3 encryption.
                </p>
            </section>

            <section class="lg-section" id="section-6">
                <h2 class="lg-section-heading"><span class="lg-section-num">6.</span> Gate Check-In &amp; Ticket QR Code Security</h2>
                <p class="lg-text">
                    Every ticket is protected with a unique, cryptographically signed QR code. When scanned at the gate, check-in timestamps and scanner device identifiers are recorded to ensure that each pass is admitted only once. These audit logs protect ticket buyers against fraud and provide organizers with verifiable attendance telemetry.
                </p>
            </section>

            <section class="lg-section" id="section-7">
                <h2 class="lg-section-heading"><span class="lg-section-num">7.</span> Cookies and Tracking Technologies</h2>
                <p class="lg-text">
                    We use strictly necessary cookies and local storage tokens to preserve authentication sessions, maintain cart items during checkout, remember dark/light theme preferences, and protect against Cross-Site Request Forgery (CSRF). You can control cookies through browser settings, though disabling them may impair core checkout functionality.
                </p>
            </section>

            <section class="lg-section" id="section-8">
                <h2 class="lg-section-heading"><span class="lg-section-num">8.</span> Data Retention &amp; Attendee Records</h2>
                <p class="lg-text">
                    We retain attendee records, transaction logs, and account data for as long as necessary to administer ticket orders, manage refund windows, maintain audit compliance, and fulfill statutory tax and legal requirements. When data is no longer required, it is securely deleted or anonymized.
                </p>
            </section>

            <section class="lg-section" id="section-9">
                <h2 class="lg-section-heading"><span class="lg-section-num">9.</span> Your Privacy Rights &amp; Choices</h2>
                <p class="lg-text">
                    Under applicable data privacy regulations (including GDPR and consumer data protection laws), you possess the following rights:
                </p>
                <ul class="lg-list">
                    <li><strong>Right to Access:</strong> Request a copy of the personal data we hold about your profile and purchases.</li>
                    <li><strong>Right to Rectification:</strong> Request correction of inaccurate, outdated, or incomplete records.</li>
                    <li><strong>Right to Erasure (GDPR):</strong> Request the permanent deletion of your account and personal records via your Profile Security settings.</li>
                    <li><strong>Right to Opt-Out:</strong> Unsubscribe from non-essential promotional communications at any time.</li>
                </ul>
            </section>

            <section class="lg-section" id="section-10">
                <h2 class="lg-section-heading"><span class="lg-section-num">10.</span> Children's Privacy</h2>
                <p class="lg-text">
                    <?= e($siteName) ?> is designed for general audiences and is not directed at children under the age of 13. We do not knowingly collect personal information from minors without verified parental consent. If we discover that personal data from a child has been gathered without authorization, we will purge the records immediately.
                </p>
            </section>

            <section class="lg-section" id="section-11">
                <h2 class="lg-section-heading"><span class="lg-section-num">11.</span> Policy Updates</h2>
                <p class="lg-text">
                    We may revise this Privacy Policy periodically to reflect platform enhancements, operational changes, or new regulatory requirements. Any material adjustments will be highlighted on our homepage along with an updated "Last Updated" timestamp.
                </p>
            </section>

            <section class="lg-section" id="section-12">
                <h2 class="lg-section-heading"><span class="lg-section-num">12.</span> Contact &amp; Compliance</h2>
                <p class="lg-text">
                    If you have questions, data inquiries, or wish to exercise your privacy rights, please contact our data compliance team:
                </p>

                <div class="lg-contact-card">
                    <div class="lg-contact-info">
                        <strong><?= e($siteName) ?> Data Protection &amp; Compliance Officer</strong>
                        <span>Privacy inquiries: <?= e($supportEmail) ?></span>
                    </div>
                    <a href="mailto:<?= e($supportEmail) ?>" class="lg-contact-btn">
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                            <polyline points="22,6 12,13 2,6"></polyline>
                        </svg>
                        Contact Privacy Team
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