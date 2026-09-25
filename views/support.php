<?php

/**
 * Support Page
 *
 * This file contains the HTML markup for the support page of the application.
 *
 * @package Evently
 * @subpackage Views/Support
 * @author Your Name <Celio Natti>
 * @license MIT License
 * @version 1.2.0
 */

$siteName = site_setting('site_title', 'Eventlyy');
$supportEmail = site_setting('support_email', 'support@eventlyy.com');

?>

@extends('layouts/main')

@section('title')
<?= e($siteName) ?> - Support Center
@endsection

@section('content')
<!-- ═══ Hero ═══ -->
<section class="page-hero" style="text-align: center; max-width: 800px; margin: 0 auto 3rem;">
    <div>
        <span class="panel-badge" style="margin: 0 auto 0.75rem;">Support Center</span>
        <h1>How can we help you?</h1>
        <p class="lede">Whether you need assistance with your account, want to join our team, sponsor an event, or
            support the platform — we're here for you.</p>
    </div>
</section>

<!-- ═══ Quick Help Cards ═══ -->
<section class="support-quick-grid" style="margin-bottom: 3.5rem;">
    <div class="about-values-grid">

        <div class="about-value-card">
            <div class="value-icon">
                <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" />
                </svg>
            </div>
            <h3>General Support</h3>
            <p>Having trouble with your account, tickets, or events? Reach out and our support team will respond within
                24 hours.</p>
            <a href="mailto:<?= e($supportEmail) ?>?subject=<?= rawurlencode('General Support Inquiry - ' . $siteName) ?>" class="support-card-link">Email Support →</a>
        </div>

        <div class="about-value-card">
            <div class="value-icon">
                <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10" />
                    <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3" />
                    <line x1="12" y1="17" x2="12.01" y2="17" />
                </svg>
            </div>
            <h3>FAQs &amp; Help Center</h3>
            <p>Find answers to common questions about creating events, buying tickets, managing payouts, and verifying
                attendees.</p>
            <a href="#faq-section" class="support-card-link">Browse FAQs →</a>
        </div>

        <div class="about-value-card">
            <div class="value-icon">
                <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z" />
                    <polyline points="22,6 12,13 2,6" />
                </svg>
            </div>
            <h3>Report an Issue</h3>
            <p>Encountered a bug or security concern? Let us know so we can investigate and resolve it as quickly as
                possible.</p>
            <a href="mailto:<?= e($supportEmail) ?>?subject=<?= rawurlencode('Bug Report & Issue - ' . $siteName) ?>" class="support-card-link">Report Bug →</a>
        </div>

    </div>
</section>

<!-- ═══ Get Involved — Opportunities Grid ═══ -->
<section style="margin-bottom: 4rem;">
    <div style="text-align: center; margin-bottom: 2.5rem;">
        <span class="panel-badge" style="margin: 0 auto 0.75rem;">Get Involved</span>
        <h2 style="font-size: clamp(22px, 3.5vw, 32px); margin-bottom: 0.5rem;">Be part of the <?= e($siteName) ?> mission</h2>
        <p style="color: rgb(var(--muted)); max-width: 580px; margin: 0 auto;">There are many ways to contribute to our
            growing platform — whether you bring your skills, your network, or your resources.</p>
    </div>

    <div class="support-involve-grid">

        <!-- Join the Team -->
        <article class="support-involve-card">
            <div class="support-involve-icon" style="background: rgba(16, 185, 129, 0.1); color: #10b981;">
                <svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                    <circle cx="9" cy="7" r="4" />
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87" />
                    <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                </svg>
            </div>
            <h3>Join Our Team</h3>
            <p>We're always looking for passionate individuals who share our vision of connecting people through live
                experiences. We hire across engineering, design, marketing, and community ops.</p>
            <div class="support-involve-details">
                <div class="support-involve-tag">Remote Friendly</div>
                <div class="support-involve-tag">Flexible Hours</div>
            </div>
            <a href="mailto:<?= e($supportEmail) ?>?subject=<?= rawurlencode('Career Application - Join Our Team - ' . $siteName) ?>" class="support-involve-btn">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z" />
                    <polyline points="22,6 12,13 2,6" />
                </svg>
                Apply Now
            </a>
        </article>

        <!-- Sponsor Events -->
        <article class="support-involve-card">
            <div class="support-involve-icon" style="background: rgba(var(--brand), 0.08); color: rgb(var(--brand));">
                <svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <polygon
                        points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" />
                </svg>
            </div>
            <h3>Become a Sponsor</h3>
            <p>Get your brand in front of thousands of engaged attendees. We offer tiered sponsorship packages that
                include logo placement, booth access, branded experiences, and promotional slots.</p>
            <div class="support-involve-details">
                <div class="support-involve-tag">Brand Visibility</div>
                <div class="support-involve-tag">Custom Packages</div>
            </div>
            <a href="mailto:<?= e($supportEmail) ?>?subject=<?= rawurlencode('Sponsorship Inquiry - ' . $siteName) ?>" class="support-involve-btn">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <polygon
                        points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" />
                </svg>
                Inquire About Sponsorship
            </a>
        </article>

        <!-- Donate / Fund -->
        <article class="support-involve-card">
            <div class="support-involve-icon" style="background: rgba(234, 88, 12, 0.08); color: #ea580c;">
                <svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path
                        d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z" />
                </svg>
            </div>
            <h3>Donate &amp; Fund Us</h3>
            <p>Your generous contributions help us keep the platform accessible, maintain free-tier ticketing for
                community events, and invest in features that empower small organizers worldwide.</p>
            <div class="support-involve-details">
                <div class="support-involve-tag">Tax Deductible</div>
                <div class="support-involve-tag">Transparent Reporting</div>
            </div>
            <a href="mailto:<?= e($supportEmail) ?>?subject=<?= rawurlencode('Donation & Funding Inquiry - ' . $siteName) ?>" class="support-involve-btn">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path
                        d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z" />
                </svg>
                Make a Donation
            </a>
        </article>

        <!-- Volunteer -->
        <article class="support-involve-card">
            <div class="support-involve-icon" style="background: rgba(16, 185, 129, 0.08); color: #10b981;">
                <svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14 9V5a3 3 0 0 0-3-3l-4 9v11h11.28a2 2 0 0 0 2-1.7l1.38-9a2 2 0 0 0-2-2.3H14z" />
                    <path d="M7 22H4a2 2 0 0 1-2-2v-7a2 2 0 0 1 2-2h3" />
                </svg>
            </div>
            <h3>Volunteer</h3>
            <p>Lend your time and skills to community events. Volunteers help with check-ins, coordination, and guest
                services — and earn badges and perks on the platform.</p>
            <div class="support-involve-details">
                <div class="support-involve-tag">Earn Perks</div>
                <div class="support-involve-tag">Community Impact</div>
            </div>
            <a href="mailto:<?= e($supportEmail) ?>?subject=<?= rawurlencode('Volunteer Application - ' . $siteName) ?>" class="support-involve-btn">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14 9V5a3 3 0 0 0-3-3l-4 9v11h11.28a2 2 0 0 0 2-1.7l1.38-9a2 2 0 0 0-2-2.3H14z" />
                </svg>
                Sign Up to Volunteer
            </a>
        </article>

    </div>
</section>

<!-- ═══ FAQ Section ═══ -->
<section id="faq-section" style="margin-bottom: 4rem;">
    <div style="text-align: center; margin-bottom: 2.5rem;">
        <span class="panel-badge" style="margin: 0 auto 0.75rem;">FAQs</span>
        <h2 style="font-size: clamp(22px, 3.5vw, 32px); margin-bottom: 0.5rem;">Frequently Asked Questions</h2>
        <p style="color: rgb(var(--muted)); max-width: 500px; margin: 0 auto;">Quick answers to common questions about
            using the platform.</p>
    </div>

    <div class="support-faq-list">
        <details class="support-faq-item">
            <summary>How do I create an event on <?= e($siteName) ?>?</summary>
            <div class="support-faq-body">
                <p>After registering a host account, navigate to the <strong>Create Event</strong> page from your
                    dashboard. Fill in your event details — name, date, venue, description, and ticket tiers — then
                    publish. Your event will be live on the browse page within minutes.</p>
            </div>
        </details>

        <details class="support-faq-item">
            <summary>What payment methods do you support?</summary>
            <div class="support-faq-body">
                <p>We currently support <strong>Paystack</strong> and online bank card payment gateways. During checkout, attendees can pay securely. All transactions are processed securely through encrypted channels.</p>
            </div>
        </details>

        <details class="support-faq-item">
            <summary>How do payouts work for event hosts?</summary>
            <div class="support-faq-body">
                <p>Event revenue is collected and held securely. After your event concludes, payouts are processed to
                    the bank account you configured in your host settings. The admin team reviews and approves
                    disbursements on a scheduled cycle.</p>
            </div>
        </details>

        <details class="support-faq-item">
            <summary>Can I get a refund for a ticket?</summary>
            <div class="support-faq-body">
                <p>Refund policies are set by each event organizer. If you need a refund, contact the event host
                    directly through your ticket confirmation email. For disputes, you can reach our support team at <a
                        href="mailto:<?= e($supportEmail) ?>?subject=<?= rawurlencode('Ticket Refund Inquiry - ' . $siteName) ?>" class="auth-link"><?= e($supportEmail) ?></a>.</p>
            </div>
        </details>

        <details class="support-faq-item">
            <summary>How does ticket verification work at the gate?</summary>
            <div class="support-faq-body">
                <p>Each ticket comes with a unique QR code. At the event venue, hosts or assigned staff use the
                    <strong>Ticket Verification</strong> scanner on their dashboard to scan attendee codes. The system
                    validates the ticket in real-time, preventing duplicates and unauthorized entry.</p>
            </div>
        </details>

        <details class="support-faq-item">
            <summary>Is <?= e($siteName) ?> free to use?</summary>
            <div class="support-faq-body">
                <p>Creating an account and browsing events is completely free. For event hosts, we offer a free tier for
                    small community events. A small ticketing fee is applied to paid ticket sales to cover payment
                    processing and platform maintenance costs.</p>
            </div>
        </details>
    </div>
</section>

<!-- ═══ Contact Form Section ═══ -->
<section style="margin-bottom: 4rem;">
    <div class="support-contact-grid">
        <div class="support-contact-info">
            <span class="panel-badge" style="margin-bottom: 0.75rem;">Reach Out</span>
            <h2>Still need help?</h2>
            <p style="color: rgb(var(--muted)); line-height: 1.6; margin-bottom: 2rem;">Send us a message and we'll get
                back to you within 24 hours. Whether it's a question, partnership inquiry, or feedback — we'd love to
                hear from you.</p>

            <div class="support-contact-channels">
                <div class="support-channel">
                    <div class="support-channel-icon">
                        <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z" />
                            <polyline points="22,6 12,13 2,6" />
                        </svg>
                    </div>
                    <div>
                        <strong>Email</strong>
                        <a href="mailto:<?= e($supportEmail) ?>?subject=<?= rawurlencode('General Support Inquiry - ' . $siteName) ?>" style="color:inherit; text-decoration:none;"><?= e($supportEmail) ?></a>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-card" style="margin: 0;">
            <h3 style="margin: 0 0 0.25rem;">Send a Message</h3>
            <p style="color: rgb(var(--muted)); font-size: 0.88rem; margin: 0 0 1rem;">We'll respond as quickly as
                possible.</p>

            <?php
            $usr = app()->user;
            $usrName = $usr ? $usr->name : '';
            $usrEmail = $usr ? $usr->email : '';
            ?>

            <form id="support-contact-form" method="POST" action="{{ route('support') }}">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="send_support_message" />
                <div style="display: grid; gap: 1rem;">
                    <label>Full Name
                        <input type="text" name="name" id="contact-name" placeholder="Your full name" required
                            value="<?= e($usrName) ?>" autocomplete="name" />
                    </label>
                    <label>Email Address
                        <input type="email" name="email" id="contact-email" placeholder="you@example.com" required
                            value="<?= e($usrEmail) ?>" autocomplete="email" />
                    </label>
                    <label>Subject
                        <select name="subject" id="contact-subject" required>
                            <option value="">-- Select a topic --</option>
                            <option value="general">General Inquiry</option>
                            <option value="account">Account Issue</option>
                            <option value="tickets">Tickets &amp; Refunds</option>
                            <option value="hosting">Event Hosting</option>
                            <option value="sponsorship">Sponsorship Inquiry</option>
                            <option value="donation">Donation &amp; Funding</option>
                            <option value="careers">Join the Team</option>
                            <option value="bug">Bug Report</option>
                        </select>
                    </label>
                    <label>Message
                        <textarea name="message" id="contact-message" rows="5" placeholder="Tell us how we can help..."
                            required
                            style="width:100%; padding:0.85rem 1rem; border-radius:14px; border:1px solid rgba(var(--muted),0.18); background:rgb(var(--card)); color:rgb(var(--text)); font-family:inherit; font-size:0.95rem; resize:vertical;"></textarea>
                    </label>
                    <button type="submit" class="admin-action-btn primary"
                        style="width:100%; height:48px; border-radius:12px; border:none; cursor:pointer; font-size:0.95rem; font-weight:700;">
                        Send Support Message →
                    </button>
                </div>
            </form>
        </div>
    </div>
</section>
@endsection