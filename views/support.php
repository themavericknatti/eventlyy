<?php

/**
 * Support Page — Heritage Bureau
 *
 * This file contains the HTML markup for the support page of the application.
 *
 * @package Evently
 * @subpackage Views/Support
 * @author Celio Natti
 * @license MIT License
 * @version 2.0.0
 */

$siteName = site_setting('site_title', 'Eventlyy');
$supportEmail = site_setting('support_email', 'support@eventlyy.com');

?>

@extends('layouts/main')

@section('title')
<?= e($siteName) ?> — Support Bureau &amp; Help Desk
@endsection

@section('content')
<div class="vn-sp-wrapper">
    <!-- ═══ Hero ═══ -->
    <header class="vn-sp-hero">
        <span class="vn-badge">Patron Assistance &amp; Inquiries</span>
        <h1 class="vn-sp-title">How May We Assist You?</h1>
        <p class="vn-sp-lead">
            Whether you require counsel regarding your account, wish to partner with our assembly, sponsor a gathering, or champion our community — our dispatch desk stands ready.
        </p>
    </header>

    <!-- ═══ Quick Help Cards ═══ -->
    <section class="vn-sp-grid">
        <article class="vn-sp-card">
            <div>
                <div class="vn-sp-card-icon">
                    <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" />
                    </svg>
                </div>
                <h3 class="vn-sp-card-title">General Support</h3>
                <p class="vn-sp-card-desc">
                    Experiencing difficulties with your admission pass, account records, or hosted events? Dispatch an inquiry and our stewards will attend to you within 24 hours.
                </p>
            </div>
            <a href="mailto:<?= e($supportEmail) ?>?subject=<?= rawurlencode('General Support Inquiry - ' . $siteName) ?>" class="vn-sp-card-link">
                Dispatch Electronic Letter →
            </a>
        </article>

        <article class="vn-sp-card">
            <div>
                <div class="vn-sp-card-icon">
                    <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10" />
                        <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3" />
                        <line x1="12" y1="17" x2="12.01" y2="17" />
                    </svg>
                </div>
                <h3 class="vn-sp-card-title">Archival FAQs</h3>
                <p class="vn-sp-card-desc">
                    Examine verified answers regarding the orchestration of events, admission acquisition, revenue disbursements, and gatekeeper verification.
                </p>
            </div>
            <a href="#faq-section" class="vn-sp-card-link">
                Consult Knowledge Base →
            </a>
        </article>

        <article class="vn-sp-card">
            <div>
                <div class="vn-sp-card-icon">
                    <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z" />
                        <polyline points="22,6 12,13 2,6" />
                    </svg>
                </div>
                <h3 class="vn-sp-card-title">Report an Anomaly</h3>
                <p class="vn-sp-card-desc">
                    Encountered a malfunction, ledger inconsistency, or security concern? Notify our engineering guild immediately for rapid remediation.
                </p>
            </div>
            <a href="mailto:<?= e($supportEmail) ?>?subject=<?= rawurlencode('Bug Report & Issue - ' . $siteName) ?>" class="vn-sp-card-link">
                Report Technical Incident →
            </a>
        </article>
    </section>

    <!-- ═══ Get Involved — Opportunities Grid ═══ -->
    <section style="margin-bottom: 4rem;">
        <div class="vn-sp-section-head">
            <span class="vn-badge">Fellowship &amp; Alliance</span>
            <h2 class="vn-sp-section-title">Champion the <?= e($siteName) ?> Mission</h2>
            <p class="vn-sp-section-lead">
                There exist myriad avenues to contribute to our flourishing cultural register — whether through craft, network, patronage, or stewardship.
            </p>
        </div>

        <div class="vn-sp-involve-grid">
            <!-- Join the Team -->
            <article class="vn-sp-involve-card">
                <div>
                    <div class="vn-sp-involve-icon">
                        <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                            <circle cx="9" cy="7" r="4" />
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87" />
                            <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                        </svg>
                    </div>
                    <h3 class="vn-sp-involve-title">Join Our Guild</h3>
                    <p class="vn-sp-involve-desc">
                        We actively seek visionary artisans and systems craftsmen dedicated to elevating live cultural communion across the globe.
                    </p>
                    <div class="vn-sp-involve-details">
                        <span class="vn-sp-involve-tag">Remote Friendly</span>
                        <span class="vn-sp-involve-tag">Flexible Hours</span>
                    </div>
                </div>
                <a href="mailto:<?= e($supportEmail) ?>?subject=<?= rawurlencode('Career Application - Join Our Team - ' . $siteName) ?>" class="vn-sp-involve-btn">
                    Submit Credentials →
                </a>
            </article>

            <!-- Sponsor Events -->
            <article class="vn-sp-involve-card">
                <div>
                    <div class="vn-sp-involve-icon">
                        <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" />
                        </svg>
                    </div>
                    <h3 class="vn-sp-involve-title">Become a Patron Sponsor</h3>
                    <p class="vn-sp-involve-desc">
                        Feature your institution prominently before thousands of devoted patrons with custom exhibition and promotional packages.
                    </p>
                    <div class="vn-sp-involve-details">
                        <span class="vn-sp-involve-tag">Eminent Visibility</span>
                        <span class="vn-sp-involve-tag">Bespoke Tiers</span>
                    </div>
                </div>
                <a href="mailto:<?= e($supportEmail) ?>?subject=<?= rawurlencode('Sponsorship Inquiry - ' . $siteName) ?>" class="vn-sp-involve-btn">
                    Inquire on Patronage →
                </a>
            </article>

            <!-- Donate / Fund -->
            <article class="vn-sp-involve-card">
                <div>
                    <div class="vn-sp-involve-icon">
                        <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z" />
                        </svg>
                    </div>
                    <h3 class="vn-sp-involve-title">Endowment &amp; Grants</h3>
                    <p class="vn-sp-involve-desc">
                        Your philanthropic support preserves complimentary community gatherings and advances open infrastructure for organizers worldwide.
                    </p>
                    <div class="vn-sp-involve-details">
                        <span class="vn-sp-involve-tag">Transparent Ledger</span>
                        <span class="vn-sp-involve-tag">Impact Focus</span>
                    </div>
                </div>
                <a href="mailto:<?= e($supportEmail) ?>?subject=<?= rawurlencode('Donation & Funding Inquiry - ' . $siteName) ?>" class="vn-sp-involve-btn">
                    Offer Endowment →
                </a>
            </article>

            <!-- Volunteer -->
            <article class="vn-sp-involve-card">
                <div>
                    <div class="vn-sp-involve-icon">
                        <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M14 9V5a3 3 0 0 0-3-3l-4 9v11h11.28a2 2 0 0 0 2-1.7l1.38-9a2 2 0 0 0-2-2.3H14z" />
                            <path d="M7 22H4a2 2 0 0 1-2-2v-7a2 2 0 0 1 2-2h3" />
                        </svg>
                    </div>
                    <h3 class="vn-sp-involve-title">Join as Event Steward</h3>
                    <p class="vn-sp-involve-desc">
                        Lend your presence to cultural gatherings. Stewards facilitate reception, guest reception, and coordination while earning archival accolades.
                    </p>
                    <div class="vn-sp-involve-details">
                        <span class="vn-sp-involve-tag">Perks &amp; Passes</span>
                        <span class="vn-sp-involve-tag">Civic Impact</span>
                    </div>
                </div>
                <a href="mailto:<?= e($supportEmail) ?>?subject=<?= rawurlencode('Volunteer Application - ' . $siteName) ?>" class="vn-sp-involve-btn">
                    Enlist as Steward →
                </a>
            </article>
        </div>
    </section>

    <!-- ═══ FAQ Section ═══ -->
    <section id="faq-section" style="margin-bottom: 4rem;">
        <div class="vn-sp-section-head">
            <span class="vn-badge">Knowledge Archives</span>
            <h2 class="vn-sp-section-title">Frequently Consulted Inquiries</h2>
            <p class="vn-sp-section-lead">Expeditious answers to common questions regarding our registry and tools.</p>
        </div>

        <div class="vn-sp-faq-list">
            <details class="vn-sp-faq-item">
                <summary>How do I register an event on <?= e($siteName) ?>?</summary>
                <div class="vn-sp-faq-body">
                    <p>After creating and certifying your host account, proceed to the <strong>Create Event</strong> salon from your host chamber. Define your celebration details — title, calendar date, sanctuary venue, narrative synopsis, and admission tiers — then publish. Your event will be indexed on the main registry within minutes.</p>
                </div>
            </details>

            <details class="vn-sp-faq-item">
                <summary>What payment channels are honoured?</summary>
                <div class="vn-sp-faq-body">
                    <p>We officially partner with <strong>Paystack</strong> and major card settlement networks. During checkout, attendees may tender payment seamlessly through encrypted and tokenized channels.</p>
                </div>
            </details>

            <details class="vn-sp-faq-item">
                <summary>How are revenues disbursed to event hosts?</summary>
                <div class="vn-sp-faq-body">
                    <p>All ticket revenues are held in escrow. Following the conclusion of your event, disbursements are routed directly to your verified banking ledger according to our regular accounting schedule.</p>
                </div>
            </details>

            <details class="vn-sp-faq-item">
                <summary>What is the protocol for ticket refunds?</summary>
                <div class="vn-sp-faq-body">
                    <p>Refund policies are determined independently by each event host. If you require reimbursement, communicate directly with the organizer via your ticket receipt. For formal disputes, contact our support desk at <a href="mailto:<?= e($supportEmail) ?>?subject=<?= rawurlencode('Ticket Refund Inquiry - ' . $siteName) ?>" style="color:var(--vn-brass); font-weight:700;"><?= e($supportEmail) ?></a>.</p>
                </div>
            </details>

            <details class="vn-sp-faq-item">
                <summary>How does gate validation function upon arrival?</summary>
                <div class="vn-sp-faq-body">
                    <p>Every admission folio carries a cryptographically unique QR seal. At the entrance, host ushers and gatekeepers employ the <strong>Ticket Verification Scanner</strong> to validate credentials in real time, preventing unauthorized entry.</p>
                </div>
            </details>

            <details class="vn-sp-faq-item">
                <summary>Is <?= e($siteName) ?> free of charge to use?</summary>
                <div class="vn-sp-faq-body">
                    <p>Account creation and exploring public gatherings is entirely complimentary. For event organizers, small community events are free. A modest ticketing fee applies to paid admissions to sustain our servers and payment gateways.</p>
                </div>
            </details>
        </div>
    </section>

    <!-- ═══ Contact Form Section ═══ -->
    <section style="margin-bottom: 2rem;">
        <div class="vn-sp-contact-grid">
            <div class="vn-sp-contact-info">
                <span class="vn-badge">Dispatch Office</span>
                <h2>Still in Need of Counsel?</h2>
                <p>
                    Compose a transmission directly to our secretariat. Whether your inquiry concerns technical issues, partnership proposals, or archival feedback, we ensure prompt reply.
                </p>

                <div class="vn-sp-channel">
                    <div class="vn-sp-channel-icon">
                        <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z" />
                            <polyline points="22,6 12,13 2,6" />
                        </svg>
                    </div>
                    <div class="vn-sp-channel-content">
                        <strong>Official Inquiries Desk</strong>
                        <a href="mailto:<?= e($supportEmail) ?>?subject=<?= rawurlencode('General Support Inquiry - ' . $siteName) ?>"><?= e($supportEmail) ?></a>
                    </div>
                </div>
            </div>

            <div class="vn-sp-form-card">
                <h3>Transmit a Message</h3>
                <p>Our dispatch officers will reply to your registered correspondence address.</p>

                <?php
                $usr = app()->user;
                $usrName = $usr ? $usr->name : '';
                $usrEmail = $usr ? $usr->email : '';
                ?>

                <form id="support-contact-form" method="POST" action="{{ route('support') }}">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="send_support_message" />
                    
                    <div style="display: grid; gap: 1.25rem;">
                        <div>
                            <label for="contact-name">Full Name</label>
                            <input type="text" name="name" id="contact-name" placeholder="Your full name" required value="<?= e($usrName) ?>" autocomplete="name" />
                        </div>

                        <div>
                            <label for="contact-email">Electronic Mail</label>
                            <input type="email" name="email" id="contact-email" placeholder="you@example.com" required value="<?= e($usrEmail) ?>" autocomplete="email" />
                        </div>

                        <div>
                            <label for="contact-subject">Topic of Inquiry</label>
                            <select name="subject" id="contact-subject" required>
                                <option value="">— Select a Topic —</option>
                                <option value="general">General Inquiry</option>
                                <option value="account">Account &amp; Ledger Issue</option>
                                <option value="tickets">Admission Tickets &amp; Refunds</option>
                                <option value="hosting">Event Orchestration</option>
                                <option value="sponsorship">Sponsorship &amp; Patronage</option>
                                <option value="donation">Endowments &amp; Funding</option>
                                <option value="careers">Guild Affiliation (Careers)</option>
                                <option value="bug">Technical Anomaly Report</option>
                            </select>
                        </div>

                        <div>
                            <label for="contact-message">Narrative Message</label>
                            <textarea name="message" id="contact-message" rows="5" placeholder="Detail your inquiry or circumstances here..." required></textarea>
                        </div>

                        <button type="submit" class="vn-sp-dispatch-btn">
                            Transmit Dispatch to Bureau →
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </section>
</div>
@endsection
