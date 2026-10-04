<?php

/**
 * About Page
 *
 * Clean, modern, responsive overview of Eventlyy's mission, ecosystem,
 * core principles, and how the platform works for attendees and organizers.
 *
 * @package Eventlyy
 * @subpackage Views/About
 * @author Celio Natti
 * @version 3.0.0
 */

$siteTitle = site_setting('site_title', 'Eventlyy');
$siteDesc  = site_setting('site_description', 'A modern ticketing and event management ecosystem designed to help hosts and attendees connect seamlessly.');
$siteLogo  = site_setting('site_logo');
?>

@extends('layouts/main')

@section('title')
About <?= e($siteTitle) ?> — Our Mission, Story &amp; Ecosystem
@endsection

@section('style')
<style>
/* ═════════════════════════════════════════════════════════════
   About Page Component System (.ab-*)
   Scoped, clean, responsive, theme-reactive (Light & Dark).
   ═════════════════════════════════════════════════════════════ */

.ab-wrapper {
    max-width: 1100px;
    margin: 0 auto;
    padding: 1.5rem 1rem 4rem;
}

/* ─── Hero Section ─────────────────────────────────────────── */
.ab-hero {
    position: relative;
    border-radius: 28px;
    padding: clamp(2.5rem, 5vw, 4rem) clamp(1.5rem, 4vw, 3.5rem);
    background: radial-gradient(circle at 10% 15%, rgba(var(--brand), 0.12) 0%, transparent 45%),
                radial-gradient(circle at 90% 85%, rgba(16, 185, 129, 0.10) 0%, transparent 45%),
                linear-gradient(135deg, rgba(var(--card), 0.95), rgba(var(--card), 0.85));
    border: 1px solid rgba(var(--brand), 0.16);
    box-shadow: 0 16px 45px -15px rgba(var(--brand), 0.12), 0 2px 8px rgba(0, 0, 0, 0.03);
    margin-bottom: 3.5rem;
    overflow: hidden;
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
}

.ab-hero::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, rgb(var(--brand)), color-mix(in srgb, rgb(var(--brand)) 70%, rgb(var(--accent))));
}

.ab-hero-grid {
    display: grid;
    grid-template-columns: 1.15fr 0.85fr;
    gap: 3rem;
    align-items: center;
    position: relative;
    z-index: 1;
}

.ab-eyebrow {
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    padding: 0.35rem 0.9rem;
    border-radius: 999px;
    font-size: 0.78rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: rgb(var(--brand));
    background: rgba(var(--brand), 0.1);
    border: 1px solid rgba(var(--brand), 0.2);
    margin-bottom: 1.15rem;
}

.ab-hero-title {
    font-size: clamp(2rem, 4.5vw, 3rem);
    font-weight: 900;
    line-height: 1.15;
    letter-spacing: -0.03em;
    color: rgb(var(--text));
    margin: 0 0 1rem;
}

.ab-hero-title-highlight {
    background: linear-gradient(135deg, rgb(var(--brand)) 0%, color-mix(in srgb, rgb(var(--brand)) 60%, rgb(var(--accent))) 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.ab-hero-lead {
    font-size: clamp(1rem, 1.8vw, 1.15rem);
    color: rgb(var(--muted));
    line-height: 1.6;
    margin: 0 0 1.75rem;
}

.ab-hero-pills {
    display: flex;
    flex-wrap: wrap;
    gap: 0.6rem;
}

.ab-hero-pill {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.4rem 0.85rem;
    border-radius: 999px;
    font-size: 0.82rem;
    font-weight: 700;
    color: rgb(var(--text));
    background: rgba(var(--muted), 0.07);
    border: 1px solid rgba(var(--muted), 0.14);
}

.ab-hero-pill svg {
    color: rgb(var(--brand));
    flex-shrink: 0;
}

/* Hero Graphic Showcase */
.ab-hero-media {
    position: relative;
    display: flex;
    justify-content: center;
    align-items: center;
}

.ab-graphic-frame {
    position: relative;
    border-radius: 24px;
    background: rgba(var(--card), 0.7);
    border: 1px solid rgba(var(--brand), 0.2);
    padding: 1rem;
    box-shadow: 0 20px 40px -15px rgba(var(--brand), 0.25);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    width: 100%;
    max-width: 420px;
    display: flex;
    justify-content: center;
    align-items: center;
    transition: transform var(--duration-med, 0.25s) ease-out;
}

.ab-graphic-frame:hover {
    transform: translateY(-4px);
    border-color: rgba(var(--brand), 0.35);
}

.ab-graphic-img {
    width: 100%;
    height: auto;
    max-height: 320px;
    object-fit: contain;
    border-radius: 16px;
    display: block;
}

.ab-floating-badge {
    position: absolute;
    bottom: -12px;
    right: 12px;
    background: rgb(var(--card));
    border: 1px solid rgba(var(--brand), 0.25);
    color: rgb(var(--text));
    font-size: 0.78rem;
    font-weight: 800;
    padding: 0.45rem 0.95rem;
    border-radius: 999px;
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
}

.ab-floating-badge span.spark {
    color: #f59e0b;
}

/* ─── Section Header ────────────────────────────────────────── */
.ab-section-head {
    text-align: center;
    max-width: 640px;
    margin: 0 auto 2.5rem;
}

.ab-section-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    padding: 0.3rem 0.75rem;
    border-radius: 999px;
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: rgb(var(--brand));
    background: rgba(var(--brand), 0.1);
    margin-bottom: 0.75rem;
}

.ab-section-title {
    font-size: clamp(1.75rem, 3.5vw, 2.35rem);
    font-weight: 900;
    letter-spacing: -0.025em;
    color: rgb(var(--text));
    margin: 0 0 0.5rem;
    line-height: 1.2;
}

.ab-section-desc {
    font-size: 0.95rem;
    color: rgb(var(--muted));
    line-height: 1.55;
    margin: 0;
}

/* ─── 3-Step "How It Works" ─────────────────────────────────── */
.ab-steps-section {
    margin-bottom: 4.5rem;
}

.ab-steps-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 1.5rem;
}

.ab-step-card {
    background: rgb(var(--card));
    border: 1px solid rgba(var(--muted), 0.16);
    border-radius: 22px;
    padding: 2rem 1.75rem;
    position: relative;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
    transition: transform var(--duration-fast, 0.15s) ease-out, border-color var(--duration-fast, 0.15s) ease-out, box-shadow var(--duration-fast, 0.15s) ease-out;
}

.ab-step-card:hover {
    transform: translateY(-4px);
    border-color: rgba(var(--brand), 0.3);
    box-shadow: 0 12px 30px rgba(0, 0, 0, 0.06);
}

.ab-step-number {
    position: absolute;
    top: 1.5rem;
    right: 1.5rem;
    font-size: 1.75rem;
    font-weight: 900;
    color: rgba(var(--brand), 0.18);
    line-height: 1;
}

.ab-step-icon {
    width: 52px;
    height: 52px;
    border-radius: 14px;
    background: rgba(var(--brand), 0.1);
    color: rgb(var(--brand));
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 1.25rem;
}

.ab-step-title {
    font-size: 1.2rem;
    font-weight: 800;
    color: rgb(var(--text));
    margin: 0 0 0.5rem;
}

.ab-step-desc {
    font-size: 0.9rem;
    color: rgb(var(--muted));
    line-height: 1.6;
    margin: 0;
}

/* ─── Core Values Grid ──────────────────────────────────────── */
.ab-values-section {
    margin-bottom: 4.5rem;
}

.ab-values-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 1.5rem;
}

.ab-value-card {
    background: rgb(var(--card));
    border: 1px solid rgba(var(--muted), 0.16);
    border-radius: 22px;
    padding: 2rem 1.75rem;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
    transition: transform var(--duration-fast, 0.15s) ease-out, border-color var(--duration-fast, 0.15s) ease-out, box-shadow var(--duration-fast, 0.15s) ease-out;
}

.ab-value-card:hover {
    transform: translateY(-4px);
    border-color: rgba(var(--brand), 0.3);
    box-shadow: 0 12px 30px rgba(0, 0, 0, 0.06);
}

.ab-value-icon-box {
    width: 52px;
    height: 52px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 1.25rem;
}

.ab-value-icon-box--brand {
    background: rgba(var(--brand), 0.12);
    color: rgb(var(--brand));
}

.ab-value-icon-box--emerald {
    background: rgba(16, 185, 129, 0.12);
    color: #10b981;
}

.ab-value-icon-box--amber {
    background: rgba(245, 158, 11, 0.12);
    color: #f59e0b;
}

.ab-value-title {
    font-size: 1.2rem;
    font-weight: 800;
    color: rgb(var(--text));
    margin: 0 0 0.5rem;
}

.ab-value-desc {
    font-size: 0.9rem;
    color: rgb(var(--muted));
    line-height: 1.6;
    margin: 0;
}

/* ─── Stats Ribbon ──────────────────────────────────────────── */
.ab-stats-section {
    margin-bottom: 4.5rem;
}

.ab-stats-ribbon {
    background: rgb(var(--card));
    border: 1px solid rgba(var(--muted), 0.16);
    border-radius: 24px;
    padding: 2.25rem 2rem;
    box-shadow: 0 4px 24px rgba(0, 0, 0, 0.04);
}

.ab-stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1.5rem;
    text-align: center;
}

.ab-stat-item {
    display: flex;
    flex-direction: column;
    gap: 0.35rem;
}

.ab-stat-val {
    font-size: clamp(2rem, 3.5vw, 2.75rem);
    font-weight: 900;
    color: rgb(var(--brand));
    letter-spacing: -0.03em;
    line-height: 1.1;
}

.ab-stat-label {
    font-size: 0.85rem;
    font-weight: 700;
    color: rgb(var(--muted));
    text-transform: uppercase;
    letter-spacing: 0.04em;
}

/* ─── Call To Action ────────────────────────────────────────── */
.ab-cta {
    position: relative;
    border-radius: 28px;
    padding: clamp(2.5rem, 5vw, 4rem) clamp(1.5rem, 4vw, 3rem);
    text-align: center;
    background: radial-gradient(circle at 15% 25%, rgba(var(--brand), 0.15) 0%, transparent 45%),
                radial-gradient(circle at 85% 75%, rgba(16, 185, 129, 0.12) 0%, transparent 45%),
                linear-gradient(135deg, rgba(var(--card), 0.95), rgba(var(--card), 0.85));
    border: 1px solid rgba(var(--brand), 0.2);
    box-shadow: 0 16px 40px -15px rgba(var(--brand), 0.15);
    overflow: hidden;
}

.ab-cta-content {
    max-width: 620px;
    margin: 0 auto;
    position: relative;
    z-index: 1;
}

.ab-cta-title {
    font-size: clamp(1.75rem, 4vw, 2.5rem);
    font-weight: 900;
    line-height: 1.2;
    letter-spacing: -0.025em;
    color: rgb(var(--text));
    margin: 0 0 0.85rem;
}

.ab-cta-desc {
    font-size: clamp(0.95rem, 1.8vw, 1.1rem);
    color: rgb(var(--muted));
    line-height: 1.6;
    margin: 0 auto 1.85rem;
}

.ab-cta-actions {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.85rem;
    flex-wrap: wrap;
}

.ab-btn-primary {
    height: 48px;
    padding: 0 1.85rem;
    border-radius: 14px;
    background: linear-gradient(135deg, rgb(var(--brand)), color-mix(in srgb, rgb(var(--brand)) 80%, rgb(var(--accent))));
    color: #ffffff !important;
    font-size: 0.95rem;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    border: none;
    box-shadow: 0 6px 20px rgba(var(--brand), 0.35);
    transition: all var(--duration-fast, 0.15s) ease-out;
}

.ab-btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 28px rgba(var(--brand), 0.45);
    filter: brightness(1.05);
}

.ab-btn-secondary {
    height: 48px;
    padding: 0 1.5rem;
    border-radius: 14px;
    background: rgba(var(--muted), 0.08);
    color: rgb(var(--text)) !important;
    border: 1px solid rgba(var(--muted), 0.2);
    font-size: 0.95rem;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    transition: all var(--duration-fast, 0.15s) ease-out;
}

.ab-btn-secondary:hover {
    background: rgba(var(--muted), 0.14);
}

/* ═════════════════════════════════════════════════════════════
   Responsive Design Breakpoints
   ═════════════════════════════════════════════════════════════ */

/* Tablet (max 960px) */
@media (max-width: 960px) {
    .ab-hero-grid {
        grid-template-columns: 1fr;
        gap: 2.5rem;
        text-align: center;
    }

    .ab-hero-lead {
        margin-left: auto;
        margin-right: auto;
    }

    .ab-hero-pills {
        justify-content: center;
    }

    .ab-steps-grid,
    .ab-values-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .ab-stats-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 2rem;
    }
}

/* Mobile (max 680px) */
@media (max-width: 680px) {
    .ab-wrapper {
        padding: 1rem 0.75rem 3rem;
    }

    .ab-hero {
        padding: 2rem 1.15rem;
        border-radius: 22px;
        margin-bottom: 2.5rem;
    }

    .ab-graphic-frame {
        max-width: 100%;
        padding: 0.75rem;
    }

    .ab-floating-badge {
        font-size: 0.72rem;
        padding: 0.35rem 0.75rem;
        bottom: -10px;
        right: 8px;
    }

    .ab-steps-grid,
    .ab-values-grid {
        grid-template-columns: 1fr;
        gap: 1.15rem;
    }

    .ab-step-card,
    .ab-value-card {
        padding: 1.5rem 1.25rem;
        border-radius: 18px;
    }

    .ab-stats-ribbon {
        padding: 1.75rem 1.25rem;
        border-radius: 20px;
    }

    .ab-stats-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 1.25rem 0.75rem;
    }

    .ab-stat-val {
        font-size: 1.85rem;
    }

    .ab-stat-label {
        font-size: 0.75rem;
    }

    .ab-cta {
        padding: 2.25rem 1.15rem;
        border-radius: 22px;
    }

    .ab-cta-actions {
        flex-direction: column;
        width: 100%;
    }

    .ab-btn-primary,
    .ab-btn-secondary {
        width: 100%;
    }
}

/* Very Small Mobile (< 380px) */
@media (max-width: 380px) {
    .ab-stats-grid {
        grid-template-columns: 1fr;
        gap: 1.25rem;
    }
}
</style>
@endsection

@section('content')
<div class="ab-wrapper">

    <!-- ═══ Hero Section (Story + Brand Graphic) ═══ -->
    <header class="ab-hero">
        <div class="ab-hero-grid">
            <div class="ab-hero-text">
                <span class="ab-eyebrow">
                    <span>✨</span> Our Story &amp; Purpose
                </span>

                <h1 class="ab-hero-title">
                    Connecting people through <span class="ab-hero-title-highlight">live experiences</span>
                </h1>

                <p class="ab-hero-lead">
                    We believe the most memorable moments happen in person. <strong><?= e($siteTitle) ?></strong> is <?= e($siteDesc) ?>
                </p>

                <div class="ab-hero-pills">
                    <span class="ab-hero-pill">
                        <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                        Instant QR Gate Passes
                    </span>
                    <span class="ab-hero-pill">
                        <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                        Direct Bank Payouts
                    </span>
                    <span class="ab-hero-pill">
                        <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                        Verified Hosts &amp; Venues
                    </span>
                </div>
            </div>

            <!-- Graphic Display (Using the brand illustration from public/assets/img/) -->
            <div class="ab-hero-media">
                <div class="ab-graphic-frame">
                    <img src="{{ asset('img/evently_hero_graphic.png') }}" 
                         alt="<?= e($siteTitle) ?> Experience Graphic" 
                         class="ab-graphic-img"
                         loading="lazy">
                    <div class="ab-floating-badge">
                        <span class="spark">★</span> 4.9/5 Host Rating
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- ═══ How It Works (3 Simple Steps) ═══ -->
    <section class="ab-steps-section">
        <div class="ab-section-head">
            <span class="ab-section-badge">Simple &amp; Seamless</span>
            <h2 class="ab-section-title">How <?= e($siteTitle) ?> Works</h2>
            <p class="ab-section-desc">From discovering your favorite festival to optical gate check-in, everything is frictionless.</p>
        </div>

        <div class="ab-steps-grid">
            <div class="ab-step-card">
                <span class="ab-step-number">01</span>
                <div class="ab-step-icon">
                    <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                </div>
                <h3 class="ab-step-title">Discover or Create</h3>
                <p class="ab-step-desc">
                    Browse upcoming concerts, tech conferences, workshops, and sports, or launch your own branded event in under 3 minutes.
                </p>
            </div>

            <div class="ab-step-card">
                <span class="ab-step-number">02</span>
                <div class="ab-step-icon">
                    <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="2" y="6" width="20" height="12" rx="2"></rect>
                        <circle cx="12" cy="12" r="2"></circle>
                        <path d="M6 12h.01M18 12h.01"></path>
                    </svg>
                </div>
                <h3 class="ab-step-title">Instant Digital Pass</h3>
                <p class="ab-step-desc">
                    Attendees receive unique admission passes with encrypted optical QR codes immediately via email and their account hub.
                </p>
            </div>

            <div class="ab-step-card">
                <span class="ab-step-number">03</span>
                <div class="ab-step-icon">
                    <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                </div>
                <h3 class="ab-step-title">Fast Gate Check-In</h3>
                <p class="ab-step-desc">
                    Event staff scan QR passes at the entrance in seconds with real-time attendance validation and automated host bank settlements.
                </p>
            </div>
        </div>
    </section>

    <!-- ═══ Core Values / Principles ═══ -->
    <section class="ab-values-section">
        <div class="ab-section-head">
            <span class="ab-section-badge">Core Principles</span>
            <h2 class="ab-section-title">What Drives Us</h2>
            <p class="ab-section-desc">We build technology designed to put event organizers and attendees first.</p>
        </div>

        <div class="ab-values-grid">
            <div class="ab-value-card">
                <div class="ab-value-icon-box ab-value-icon-box--brand">
                    <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                    </svg>
                </div>
                <h3 class="ab-value-title">Secure &amp; Trusted</h3>
                <p class="ab-value-desc">
                    Every ticket is backed by tamper-proof QR verification, Paystack payment protection, and transparent host validation queues.
                </p>
            </div>

            <div class="ab-value-card">
                <div class="ab-value-icon-box ab-value-icon-box--emerald">
                    <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <path d="M8 14s1.5 2 4 2 4-2 4-2"></path>
                        <line x1="9" y1="9" x2="9.01" y2="9"></line>
                        <line x1="15" y1="9" x2="15.01" y2="9"></line>
                    </svg>
                </div>
                <h3 class="ab-value-title">Attendee Centric</h3>
                <p class="ab-value-desc">
                    Instant checkout without bloated registration walls, easy 1-click pass transfers, Apple/Google Calendar sync, and print passes.
                </p>
            </div>

            <div class="ab-value-card">
                <div class="ab-value-icon-box ab-value-icon-box--amber">
                    <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polygon points="12 2 2 7 12 12 22 7 12 2"></polygon>
                    </svg>
                </div>
                <h3 class="ab-value-title">Organizer Empowered</h3>
                <p class="ab-value-desc">
                    Comprehensive event controls: custom ticket tiers, sales progress bars, team staff management, and automated bank payouts.
                </p>
            </div>
        </div>
    </section>

    <!-- ═══ Stats Ribbon ═══ -->
    <section class="ab-stats-section">
        <div class="ab-stats-ribbon">
            <div class="ab-stats-grid">
                <div class="ab-stat-item">
                    <span class="ab-stat-val">50K+</span>
                    <span class="ab-stat-label">Passes Issued</span>
                </div>
                <div class="ab-stat-item">
                    <span class="ab-stat-val">99.9%</span>
                    <span class="ab-stat-label">Platform Uptime</span>
                </div>
                <div class="ab-stat-item">
                    <span class="ab-stat-val">4.9 ★</span>
                    <span class="ab-stat-label">Host Rating</span>
                </div>
                <div class="ab-stat-item">
                    <span class="ab-stat-val">&lt; 3s</span>
                    <span class="ab-stat-label">Gate Scan Speed</span>
                </div>
            </div>
        </div>
    </section>

    <!-- ═══ Call To Action ═══ -->
    <section class="ab-cta">
        <div class="ab-cta-content">
            <span class="ab-section-badge">Get Started</span>
            <h2 class="ab-cta-title">Ready to experience or host your next event?</h2>
            <p class="ab-cta-desc">
                Join thousands of attendees and organizers managing ticketing, verification, and admission seamlessly with <strong><?= e($siteTitle) ?></strong>.
            </p>

            <div class="ab-cta-actions">
                <?php if (app()->user && app()->user->isHost()): ?>
                    <a href="{{ route('host/event/create') }}" class="ab-btn-primary">
                        Create an Event →
                    </a>
                <?php else: ?>
                    <a href="{{ route('register') }}" class="ab-btn-primary">
                        Become a Host &amp; Start Free →
                    </a>
                <?php endif; ?>
                <a href="{{ route('/') }}" class="ab-btn-secondary">
                    Browse Live Events
                </a>
            </div>
        </div>
    </section>

</div>
@endsection