<?php

/**
 * About Page
 *
 * This file contains the HTML markup for the about page of the application.
 *
 * @package Evently
 * @subpackage Views/About
 * @author Your Name <Celio Natti>
 * @license MIT License
 * @version 2.0.0
 */

$siteTitle = site_setting('site_title', 'Eventlyy');
$siteDesc  = site_setting('site_description', 'A modern ticketing and event management ecosystem designed to help hosts and attendees connect seamlessly.');
?>

@extends('layouts/main')

@section('title')
About <?= e($siteTitle) ?> - Learn More About Our Mission and Ecosystem
@endsection

@section('content')
<!-- Hero Section -->
<section class="page-hero" style="text-align: center; max-width: 800px; margin: 0 auto 3rem;">
    <div>
        <span class="panel-badge" style="margin: 0 auto 0.75rem;">Our Story</span>
        <h1>Connecting people through live experiences</h1>
        <p class="lede">We believe in the power of shared moments. <strong><?= e($siteTitle) ?></strong> is <?= e($siteDesc) ?></p>
    </div>
</section>

<!-- Values Grid -->
<section class="about-values-section" style="margin-bottom: 4rem;">
    <div style="text-align: center; margin-bottom: 2rem;">
        <h2>What drives us</h2>
        <p style="color: rgb(var(--muted));">Our core principles guide how we build products and support communities.</p>
    </div>
    <div class="about-values-grid">
        <div class="about-value-card">
            <div class="value-icon">
                <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
                </svg>
            </div>
            <h3>Secure &amp; Trusted</h3>
            <p>We build secure 256-bit ticket verification mechanisms, protected payment gateways, and transparent host validation queues.</p>
        </div>

        <div class="about-value-card">
            <div class="value-icon">
                <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10" />
                    <path d="M8 14s1.5 2 4 2 4-2 4-2" />
                    <line x1="9" y1="9" x2="9.01" y2="9" />
                    <line x1="15" y1="9" x2="15.01" y2="9" />
                </svg>
            </div>
            <h3>Attendee Centric</h3>
            <p>From discovering nearby festivals to checking out in two clicks and verifying gate passes, the attendee experience is paramount.</p>
        </div>

        <div class="about-value-card">
            <div class="value-icon">
                <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <polygon points="12 2 2 7 12 12 22 7 12 2" />
                </svg>
            </div>
            <h3>Host &amp; Organizer First</h3>
            <p>Empowering organizers with live attendee check-ins, automated bank payouts, staff assignments, and real-time revenue analytics.</p>
        </div>
    </div>
</section>

<!-- Stats Section -->
<section class="about-stats-section" style="margin-bottom: 4rem;">
    <div class="about-stats-inner">
        <div class="about-stat-item">
            <strong>150K+</strong>
            <span>Tickets Distributed</span>
        </div>
        <div class="about-stat-item">
            <strong>4.9★</strong>
            <span>Platform Rating</span>
        </div>
        <div class="about-stat-item">
            <strong>20+</strong>
            <span>Verified Hosts</span>
        </div>
        <div class="about-stat-item">
            <strong>99.9%</strong>
            <span>Service Uptime</span>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="about-cta-section"
    style="text-align: center; padding: 3.5rem 2rem; border-radius: 28px; background: linear-gradient(180deg, rgba(var(--brand), 0.06), rgba(var(--accent), 0.04)); border: 1px solid rgba(var(--brand), 0.15); margin-bottom: 4rem;">
    <span class="panel-badge" style="margin: 0 auto 0.75rem;">Get Started Today</span>
    <h2 style="font-size: clamp(22px, 3.5vw, 32px); margin-bottom: 0.5rem;">Ready to host your next event?</h2>
    <p style="color: rgb(var(--muted)); max-width: 520px; margin: 0 auto 1.75rem;">
        Join thousands of organizers managing ticketing, verification, and automated payouts with <strong><?= e($siteTitle) ?></strong>.
    </p>
    <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
        <?php if (app()->user && app()->user->isHost()): ?>
            <a href="{{ route('host/event/create') }}" class="admin-action-btn primary"
                style="padding: 0.75rem 2rem; border-radius: 12px; height: 46px; font-weight:700; text-decoration:none;">
                Create Event Now →
            </a>
        <?php else: ?>
            <a href="{{ route('register') }}" class="admin-action-btn primary"
                style="padding: 0.75rem 2rem; border-radius: 12px; height: 46px; font-weight:700; text-decoration:none;">
                Get Started →
            </a>
        <?php endif; ?>
        <a href="{{ route('/') }}" class="admin-action-btn"
            style="padding: 0.75rem 2rem; border-radius: 12px; height: 46px; font-weight:600; text-decoration:none;">
            Browse Events
        </a>
    </div>
</section>
@endsection