<?php

/**
 * Privacy Policy
 *
 * This file contains the HTML markup for the privacy policy page of the application.
 *
 * @package Evently
 * @subpackage Views/Privacy
 * @author Your Name <Celio Natti>
 * @license MIT License
 * @version 1.1.0
 */

$siteName = $siteName ?? site_setting('site_title', 'Eventlyy');
$supportEmail = $supportEmail ?? site_setting('support_email', 'support@eventlyy.com');

?>

@extends('layouts/main')

@section('title')
Privacy Policy - <?= e($siteName) ?>
@endsection

@section('content')
<section class="legal-hero">
    <span class="panel-badge" style="margin: 0 auto 0.75rem;">Legal Overview</span>
    <h1>Privacy Policy</h1>
    <p class="meta">Last updated: August 13, 2026</p>
</section>

<div class="legal-content">
    <section class="legal-section">
        <h2>1. Introduction</h2>
        <p><?= e($siteName) ?> ("we," "us," "our," or "Company") is committed to protecting your privacy. This Privacy Policy
            explains how we collect, use, disclose, and safeguard your information when you use our website, mobile interface, ticket purchasing system, and gate-verification services.</p>
    </section>

    <section class="legal-section">
        <h2>2. Information We Collect</h2>
        <p>We collect information you provide directly or automatically through your interaction with our ticketing platform:</p>
        <ul class="detail-list" style="margin-top: 0.5rem;">
            <li><strong>Account & Buyer Details:</strong> Full name, email address, phone number, and password for registered users.</li>
            <li><strong>Guest Checkout Information:</strong> Name, email address, and contact details collected during guest ticket purchases without a user account.</li>
            <li><strong>Event & Host Information:</strong> Organizer details, bank payout credentials, payout verification documents, event descriptions, venue locations, and ticket pricing tiers.</li>
            <li><strong>Gate Pass & Scanner Logs:</strong> Unique ticket QR codes, check-in timestamps, validation status, and scanner device logs collected during venue entry verification.</li>
            <li><strong>Technical & Analytics Data:</strong> IP address, browser type, device identifiers, session cookies, and user navigation logs.</li>
        </ul>
    </section>

    <section class="legal-section">
        <h2>3. How We Use Your Information</h2>
        <p>We use the collected information for the following primary business purposes:</p>
        <ul class="detail-list" style="margin-top: 0.5rem;">
            <li>Generating, issuing, and emailing digital tickets and secure QR gate passes.</li>
            <li>Processing ticket payments, promo code discounts, and issuing host payouts or attendee refunds.</li>
            <li>Facilitating gate check-in verification for event hosts and staff scanners.</li>
            <li>Maintaining attendee records and directory insights for organizers and platform analytics.</li>
            <li>Preventing ticket counterfeiting, unauthorized resale, duplicate entry, and fraudulent transactions.</li>
            <li>Sending transactional emails, order confirmations, event updates, and customer support responses.</li>
        </ul>
    </section>

    <section class="legal-section">
        <h2>4. Data Sharing with Event Organizers (Hosts)</h2>
        <p>When you purchase a ticket or register for an event hosted on <?= e($siteName) ?>, we share limited attendee information (such as your name, email address, ticket quantity, and ticket type) with the designated Event Organizer (Host) and their authorized staff. This enables hosts to:</p>
        <ul class="detail-list" style="margin-top: 0.5rem;">
            <li>Manage event admission and verify tickets at venue gates.</li>
            <li>Communicate critical event logistics, schedule changes, or safety instructions.</li>
            <li>Comply with local venue capacity restrictions and event compliance rules.</li>
        </ul>
        <p style="margin-top: 0.5rem;">We do not sell, trade, or rent attendee or user personal data to third-party advertisers.</p>
    </section>

    <section class="legal-section">
        <h2>5. Payment Processing & Financial Security</h2>
        <p>All online ticket payments and bank payouts are processed securely through PCI-DSS compliant third-party payment service providers (e.g., Paystack). <?= e($siteName) ?> does not store full credit card numbers, CVVs, or sensitive card details on our servers.</p>
    </section>

    <section class="legal-section">
        <h2>6. Gate Check-In & Ticket QR Code Security</h2>
        <p>Each ticket features a unique cryptographic QR code. When scanned at an event, check-in timestamps and verification logs are recorded to prevent duplicate entry or fraudulent pass duplication. Gate scan data is retained to assist organizers and administrators in resolving entry disputes.</p>
    </section>

    <section class="legal-section">
        <h2>7. Cookies and Tracking Technologies</h2>
        <p>We use essential cookies and session storage to maintain authentication states, preserve cart contents, remember user preferences, and secure payment checkout flows. You may adjust your browser settings to refuse cookies, though certain site features may be impaired.</p>
    </section>

    <section class="legal-section">
        <h2>8. Data Retention & Attendee Records</h2>
        <p>We retain attendee records, transaction history, and account data for as long as necessary to fulfill ticket orders, manage refunds, maintain audit trails, enforce platform policies, and comply with tax and legal requirements.</p>
    </section>

    <section class="legal-section">
        <h2>9. Your Privacy Rights & Choices</h2>
        <p>Depending on your location, you have the right to:</p>
        <ul class="detail-list" style="margin-top: 0.5rem;">
            <li>Access a copy of the personal data we hold about you.</li>
            <li>Request corrections to inaccurate personal information.</li>
            <li>Request deletion of your account and personal data (subject to legal or transaction retention rules).</li>
            <li>Opt-out of non-transactional marketing communications.</li>
        </ul>
        <p style="margin-top: 0.5rem;">To submit a data request, please contact us at <a href="mailto:<?= e($supportEmail) ?>" class="auth-link"><?= e($supportEmail) ?></a>.</p>
    </section>

    <section class="legal-section">
        <h2>10. Children's Privacy</h2>
        <p><?= e($siteName) ?> is not intended for children under 13 years of age. We do not knowingly collect personal information from minors. If you believe a child has provided us with personal information, please notify us immediately.</p>
    </section>

    <section class="legal-section">
        <h2>11. Updates to This Policy</h2>
        <p>We may update this Privacy Policy periodically to reflect platform enhancements or legal developments. Significant changes will be announced on our website alongside an updated "Last Updated" date.</p>
    </section>

    <section class="legal-section">
        <h2>12. Contact Us</h2>
        <p>If you have questions or concerns regarding this Privacy Policy, please contact our support team:</p>
        <p style="padding: 1rem; border-radius: 16px; background: rgba(var(--muted), 0.06); border: 1px solid rgba(var(--muted), 0.1); line-height: 1.7;">
            <strong><?= e($siteName) ?> Privacy & Compliance Team</strong><br>
            Email: <a href="mailto:<?= e($supportEmail) ?>" class="auth-link"><?= e($supportEmail) ?></a>
        </p>
    </section>
</div>
@endsection