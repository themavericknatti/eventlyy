<?php

/**
 * Terms of Service
 *
 * This file contains the HTML markup for the terms of service page of the application.
 *
 * @package Evently
 * @subpackage Views/Terms
 * @author Your Name <Celio Natti>
 * @license MIT License
 * @version 1.1.0
 */

$siteName = $siteName ?? site_setting('site_title', 'Eventlyy');
$supportEmail = $supportEmail ?? site_setting('support_email', 'support@eventlyy.com');

?>

@extends('layouts/main')

@section('title')
Terms of Service - <?= e($siteName) ?>
@endsection

@section('content')
<section class="legal-hero">
    <span class="panel-badge" style="margin: 0 auto 0.75rem;">Platform Rules</span>
    <h1>Terms of Service</h1>
    <p class="meta">Last updated: August 13, 2026</p>
</section>

<div class="legal-content">
    <section class="legal-section">
        <h2>1. Agreement to Terms</h2>
        <p>By accessing or using the <?= e($siteName) ?> website, mobile application, host dashboard, or ticketing services, you agree to be bound by these Terms of Service and all applicable laws. If you do not agree, you may not access or use our services.</p>
    </section>

    <section class="legal-section">
        <h2>2. Platform Role & Intermediary Status</h2>
        <p><?= e($siteName) ?> operates strictly as an online marketplace technology platform connecting Event Organizers ("Hosts") with Event Attendees ("Buyers"). Unless explicitly specified on the event page as an official company event:</p>
        <ul class="detail-list" style="margin-top: 0.5rem;">
            <li><?= e($siteName) ?> is not the organizer, promoter, seller of record, or owner of third-party listed events.</li>
            <li><?= e($siteName) ?> is not responsible for event logistics, venue safety, quality, cancellation, performance, or representations made by Event Organizers.</li>
            <li>All contracts for ticket purchases and event attendance are directly between the Buyer and the respective Event Organizer.</li>
        </ul>
    </section>

    <section class="legal-section">
        <h2>3. User & Host Accounts</h2>
        <p>Users and Organizers must provide accurate, current, and complete details during registration and ticket checkout. You are responsible for safeguarding your account credentials and for all activities under your account. You agree to notify us immediately of any unauthorized access.</p>
    </section>

    <section class="legal-section">
        <h2>4. Ticket Sales, QR Codes & Gate Admission</h2>
        <p>Purchased tickets are issued as digital gate passes containing cryptographic QR codes. The following conditions apply:</p>
        <ul class="detail-list" style="margin-top: 0.5rem;">
            <li><strong>Single Entry Pass:</strong> Each QR code is valid for one-time admission scan at the venue gate. Once scanned, the pass is marked as used and invalidated.</li>
            <li><strong>Unauthorized Resale & Duplication:</strong> Reselling tickets above face value, duplicating QR codes, or sharing pass images via unauthorized third-party channels is strictly prohibited.</li>
            <li><strong>Entry Rights:</strong> Event Organizers reserve the right to refuse admission for invalid, duplicate, or tampered QR passes, or for venue policy violations. <?= e($siteName) ?> is not liable for entry denial resulting from duplicated passes or buyer resale.</li>
        </ul>
    </section>

    <section class="legal-section">
        <h2>5. Refunds, Cancellations & Service Fees</h2>
        <p>Ticket purchasing and refund conditions are governed by the following terms:</p>
        <ul class="detail-list" style="margin-top: 0.5rem;">
            <li><strong>Host Refund Responsibility:</strong> Event Organizers establish their own refund policies. If an event is cancelled, postponed, or materially altered, the Organizer is solely responsible for authorizing and issuing attendee refunds.</li>
            <li><strong>Non-Refundable Platform Fees:</strong> Platform service fees, payment gateway processing fees, or administrative charges collected by <?= e($siteName) ?> are non-refundable, except where required by law or agreed by platform administration.</li>
            <li><strong>Chargebacks & Disputes:</strong> Buyers must contact the Event Organizer or <?= e($siteName) ?> support prior to initiating payment card chargebacks. Fraudulent chargeback claims will result in immediate account suspension.</li>
        </ul>
    </section>

    <section class="legal-section">
        <h2>6. Host Payouts, Escrow Holds & Financial Security</h2>
        <p>Event Organizers who collect ticket sales through <?= e($siteName) ?> agree to the following financial terms:</p>
        <ul class="detail-list" style="margin-top: 0.5rem;">
            <li><strong>Banking Accuracy:</strong> Organizers must supply verified, accurate bank account details for revenue disbursements. <?= e($siteName) ?> is not liable for failed or delayed transfers caused by incorrect host bank info.</li>
            <li><strong>Escrow Holds:</strong> <?= e($siteName) ?> reserves the right to hold ticket revenue in escrow until after the successful completion of an event, or longer if an event is flagged for safety review, organizer disputes, high refund rates, or suspected fraud.</li>
            <li><strong>Revenue Clawbacks:</strong> In cases of cancelled events, fraudulent ticket sales, or buyer refunds, <?= e($siteName) ?> reserves the right to deduct outstanding amounts from host balances or withhold pending payouts.</li>
        </ul>
    </section>

    <section class="legal-section">
        <h2>7. Prohibited Content & Event Moderation</h2>
        <p>Organizers agree not to publish events that involve illegal activities, hate speech, unauthorized ticket scalping, misleading claims, dangerous gatherings, or copyright infringement. <?= e($siteName) ?> reserves the right to unpublish events, cancel ticket sales, and ban organizers without prior notice for policy violations.</p>
    </section>

    <section class="legal-section">
        <h2>8. Intellectual Property</h2>
        <p>All software, brand logos, code, layout designs, and graphics on <?= e($siteName) ?> are owned by or licensed to <?= e($siteName) ?>. Users may not copy, reverse engineer, scrape, or commercially exploit platform assets without prior written consent.</p>
    </section>

    <section class="legal-section">
        <h2>9. Limitation of Liability</h2>
        <p>To the maximum extent permitted by law, <?= e($siteName) ?>, its directors, employees, and partners shall not be liable for any indirect, incidental, special, consequential, or punitive damages (including loss of profits, data, or goodwill) arising out of or related to your use of the platform or attendance at any event. In all cases, <?= e($siteName) ?>'s maximum total liability shall be limited to the amount of platform service fees retained by us for the specific transaction in question, or $100 USD, whichever is less.</p>
    </section>

    <section class="legal-section">
        <h2>10. Indemnification</h2>
        <p>Event Organizers and Users agree to defend, indemnify, and hold harmless <?= e($siteName) ?> from any claims, damages, liabilities, costs, or expenses (including legal fees) arising from listed events, attendee injuries, property damage, contract breaches, or violation of applicable laws.</p>
    </section>

    <section class="legal-section">
        <h2>11. Force Majeure</h2>
        <p><?= e($siteName) ?> is not liable for failure or delay in platform performance resulting from causes beyond reasonable control, including acts of God, severe weather, natural disasters, epidemics, venue closures, telecommunications outages, or government actions.</p>
    </section>

    <section class="legal-section">
        <h2>12. Governing Law & Dispute Resolution</h2>
        <p>These Terms shall be governed by and construed in accordance with the applicable laws of the operating jurisdiction, without regard to conflict of law principles. Any legal action or dispute arising under these terms shall be submitted to the exclusive jurisdiction of competent local courts.</p>
    </section>

    <section class="legal-section">
        <h2>13. Termination</h2>
        <p><?= e($siteName) ?> may terminate or suspend access to your account immediately, without prior notice, for conduct that violates these Terms or is harmful to other users, hosts, or platform integrity.</p>
    </section>

    <section class="legal-section">
        <h2>14. Contact Information</h2>
        <p>For questions or legal inquiries regarding these Terms of Service, please reach out to our legal and support team:</p>
        <p style="padding: 1rem; border-radius: 16px; background: rgba(var(--muted), 0.06); border: 1px solid rgba(var(--muted), 0.1); line-height: 1.7;">
            <strong><?= e($siteName) ?> Legal & Support Desk</strong><br>
            Email: <a href="mailto:<?= e($supportEmail) ?>" class="auth-link"><?= e($supportEmail) ?></a>
        </p>
    </section>
</div>
@endsection