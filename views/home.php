<?php

/**
 * Home / Welcome Page
 *
 * Clean, modern, responsive landing page featuring dynamic events discovery,
 * intuitive search and category filters, featured carousel, and paginated event cards.
 *
 * @package Eventlyy
 * @subpackage Views/Home
 * @author Celio Natti
 * @version 2.0.0
 */

?>

@extends('layouts/main')

@section('title')
<?= e($title ?? 'Welcome to Eventlyy - Find live events, concerts & experiences.') ?>
@endsection

@section('style')
<style>
/* ═════════════════════════════════════════════════════════════
   Home Page Component System (.hm-*)
   Clean, responsive, accessible, theme-reactive (Light & Dark).
   ═════════════════════════════════════════════════════════════ */

.hm-wrapper {
    width: 100%;
    margin: 0 auto;
    padding-bottom: 4rem;
}

/* ─── Hero Section ─────────────────────────────────────────── */
.perspective-stage {
    perspective: 1200px;
    position: relative;
    z-index: 2;
    margin-bottom: clamp(2rem, 4vw, 3.25rem);
}

.hm-hero {
    position: relative;
    border-radius: 28px;
    padding: clamp(2.5rem, 5vw, 4rem) clamp(1.5rem, 4vw, 3.5rem);
    background: radial-gradient(circle at 10% 15%, rgba(var(--brand), 0.12) 0%, transparent 45%),
                radial-gradient(circle at 90% 85%, rgba(16, 185, 129, 0.10) 0%, transparent 45%),
                linear-gradient(135deg, rgba(var(--card), 0.95), rgba(var(--card), 0.85));
    border: 1px solid rgba(var(--brand), 0.16);
    box-shadow: 0 16px 45px -15px rgba(var(--brand), 0.12), 0 2px 8px rgba(0, 0, 0, 0.03);
    margin-bottom: 0;
    overflow: hidden;
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
}

.hm-hero::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, rgb(var(--brand)), color-mix(in srgb, rgb(var(--brand)) 70%, rgb(var(--accent))));
}

.hm-hero__content {
    max-width: 780px;
    margin: 0 auto;
    text-align: center;
    position: relative;
    z-index: 1;
}

.hm-eyebrow {
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

.hm-hero__title {
    font-size: clamp(2rem, 5vw, 3.25rem);
    font-weight: 900;
    line-height: 1.12;
    letter-spacing: -0.03em;
    color: rgb(var(--text));
    margin: 0 0 0.85rem;
    position: relative;
    display: inline-block;
    /* Shimmer gradient moving across the entire text */
    background: linear-gradient(
        110deg,
        rgb(var(--text)) 0%,
        rgb(var(--text)) 35%,
        rgba(255, 255, 255, 0.98) 50%,
        rgb(var(--text)) 65%,
        rgb(var(--text)) 100%
    );
    background-size: 250% 100%;
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    animation: hmTitleGlowSweep 4.5s ease-in-out infinite;
}

:root[data-theme='dark'] .hm-hero__title {
    background: linear-gradient(
        110deg,
        rgba(243, 245, 247, 0.78) 0%,
        rgba(243, 245, 247, 0.78) 35%,
        #ffffff 50%,
        rgba(243, 245, 247, 0.78) 65%,
        rgba(243, 245, 247, 0.78) 100%
    );
    background-size: 250% 100%;
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.hm-hero__title-highlight {
    background: linear-gradient(
        110deg,
        rgb(var(--brand)) 0%,
        color-mix(in srgb, rgb(var(--brand)) 70%, rgb(var(--accent))) 35%,
        #86efac 50%,
        color-mix(in srgb, rgb(var(--brand)) 70%, rgb(var(--accent))) 65%,
        rgb(var(--brand)) 100%
    );
    background-size: 250% 100%;
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    filter: drop-shadow(0 0 16px rgba(var(--brand), 0.35));
    animation: hmTitleGlowSweep 4.5s ease-in-out infinite;
}

@keyframes hmTitleGlowSweep {
    0% {
        background-position: 120% 0;
    }
    100% {
        background-position: -120% 0;
    }
}

@media (prefers-reduced-motion: reduce) {
    .hm-hero__title,
    .hm-hero__title-highlight {
        animation: none;
        background-size: 100% 100%;
    }
}

.hm-hero__subtitle {
    font-size: clamp(1rem, 2vw, 1.2rem);
    color: rgb(var(--muted));
    margin: 0 auto 2rem;
    max-width: 580px;
    line-height: 1.55;
}

/* ─── Search Bar ────────────────────────────────────────────── */
.hm-search-form {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    padding: 0.5rem;
    border-radius: 18px;
    background: rgb(var(--card));
    border: 1.5px solid rgba(var(--muted), 0.18);
    box-shadow: 0 12px 36px -8px rgba(0, 0, 0, 0.08), 0 0 0 1px rgba(var(--brand), 0.06);
    transition: all var(--duration-fast, 0.15s) ease-out;
}

.hm-search-form:focus-within {
    border-color: rgb(var(--brand));
    box-shadow: 0 16px 40px -8px rgba(var(--brand), 0.2), 0 0 0 4px rgba(var(--brand), 0.12);
    transform: translateY(-1px);
}

.hm-search-group {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    padding: 0 0.85rem;
    flex: 1;
    min-width: 0;
}

.hm-search-group svg {
    color: rgb(var(--muted));
    flex-shrink: 0;
}

.hm-search-input {
    width: 100%;
    height: 46px;
    border: none;
    background: transparent;
    color: rgb(var(--text));
    font-size: 0.96rem;
    font-family: inherit;
    outline: none;
}

.hm-search-input::placeholder {
    color: rgb(var(--muted));
    opacity: 0.8;
}

.hm-search-divider {
    width: 1px;
    height: 28px;
    background: rgba(var(--muted), 0.2);
    flex-shrink: 0;
}

.hm-search-select-wrap {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0 0.85rem;
    flex: 0 1 200px;
    min-width: 140px;
}

.hm-search-select-wrap svg {
    color: rgb(var(--muted));
    flex-shrink: 0;
}

.hm-search-select {
    width: 100%;
    height: 46px;
    border: none;
    background: transparent;
    color: rgb(var(--text));
    font-size: 0.92rem;
    font-weight: 600;
    font-family: inherit;
    outline: none;
    cursor: pointer;
}

.hm-search-select option {
    background: rgb(var(--card));
    color: rgb(var(--text));
}

.hm-search-btn {
    height: 48px;
    padding: 0 1.5rem;
    border-radius: 14px;
    border: none;
    background: linear-gradient(135deg, rgb(var(--brand)), color-mix(in srgb, rgb(var(--brand)) 80%, rgb(var(--accent))));
    color: #ffffff !important;
    font-size: 0.95rem;
    font-weight: 700;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.45rem;
    flex-shrink: 0;
    box-shadow: 0 6px 18px rgba(var(--brand), 0.35);
    transition: all var(--duration-fast, 0.15s) ease-out;
}

.hm-search-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 26px rgba(var(--brand), 0.45);
    filter: brightness(1.05);
}

/* ─── Hero Trust Metrics ────────────────────────────────────── */
.hm-trust-bar {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.75rem 1.25rem;
    flex-wrap: wrap;
    margin-top: 1.75rem;
    padding-top: 1.5rem;
    border-top: 1px solid rgba(var(--muted), 0.12);
}

.hm-trust-item {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    font-size: 0.8rem;
    font-weight: 600;
    color: rgb(var(--muted));
    background: rgba(var(--muted), 0.06);
    padding: 0.35rem 0.75rem;
    border-radius: 999px;
    border: 1px solid rgba(var(--muted), 0.1);
}

.hm-trust-item span.icon {
    font-size: 0.95rem;
}

/* ─── Categories Navigation ─────────────────────────────────── */
.hm-categories-section {
    position: relative;
    z-index: 1;
    margin-bottom: clamp(2rem, 4vw, 3rem);
}

.hm-section-title-wrap {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: 1rem;
    margin-bottom: 1.15rem;
}

.hm-section-heading {
    font-size: 1.45rem;
    font-weight: 800;
    letter-spacing: -0.02em;
    color: rgb(var(--text));
    margin: 0;
}

.hm-section-subheading {
    font-size: 0.86rem;
    color: rgb(var(--muted));
    margin: 0.25rem 0 0;
}

.hm-chips {
    display: flex;
    gap: 0.5rem;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: none;
    padding-bottom: 0.5rem;
    margin: 0 -0.25rem;
    padding-left: 0.25rem;
    padding-right: 0.25rem;
}

.hm-chips::-webkit-scrollbar {
    display: none;
}

.hm-chip-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    padding: 0.6rem 1.1rem;
    border-radius: 999px;
    font-size: 0.88rem;
    font-weight: 700;
    color: rgb(var(--muted));
    background: rgb(var(--card));
    border: 1.5px solid rgba(var(--muted), 0.18);
    text-decoration: none;
    white-space: nowrap;
    flex-shrink: 0;
    transition: all var(--duration-fast, 0.15s) ease-out;
}

.hm-chip-btn:hover {
    color: rgb(var(--text));
    border-color: rgba(var(--brand), 0.4);
    background: rgba(var(--brand), 0.05);
    transform: translateY(-1px);
}

.hm-chip-btn.hm-chip-btn--active {
    background: rgb(var(--brand));
    color: #ffffff !important;
    border-color: rgb(var(--brand));
    box-shadow: 0 4px 14px rgba(var(--brand), 0.3);
}

/* ─── Featured Carousel Section ─────────────────────────────── */
.hm-featured-section {
    margin-bottom: 3rem;
}

.hm-featured-carousel {
    position: relative;
    border-radius: 22px;
    background: rgb(var(--card));
    border: 1px solid rgba(var(--muted), 0.14);
    padding: 1.25rem;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
}

.hm-carousel-nav {
    display: flex;
    align-items: center;
    gap: 0.4rem;
}

.hm-carousel-btn {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    border: 1px solid rgba(var(--muted), 0.18);
    background: rgba(var(--muted), 0.06);
    color: rgb(var(--text));
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    font-weight: 800;
    font-size: 0.85rem;
    transition: all var(--duration-fast, 0.15s) ease-out;
}

.hm-carousel-btn:hover:not(:disabled) {
    background: rgb(var(--brand));
    color: #ffffff;
    border-color: rgb(var(--brand));
}

.hm-carousel-btn:disabled {
    opacity: 0.35;
    cursor: not-allowed;
}

.hm-carousel-slides {
    display: flex;
    gap: 1.25rem;
    overflow-x: auto;
    scroll-snap-type: x mandatory;
    scroll-behavior: smooth;
    scrollbar-width: none;
    padding: 0.5rem 0.25rem 0.75rem;
    margin: 0;
}

.hm-carousel-slides::-webkit-scrollbar {
    display: none;
}

.hm-featured-card-link {
    flex: 0 0 clamp(260px, 32vw, 360px);
    scroll-snap-align: start;
    text-decoration: none;
    color: inherit;
    display: block;
}

.hm-featured-card {
    border-radius: 18px;
    overflow: hidden;
    background: rgba(var(--muted), 0.03);
    border: 1px solid rgba(var(--muted), 0.12);
    transition: all var(--duration-fast, 0.15s) ease-out;
}

.hm-featured-card:hover {
    transform: translateY(-3px);
    border-color: rgba(var(--brand), 0.35);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.06);
}

.hm-featured-img-wrap {
    position: relative;
    width: 100%;
    aspect-ratio: 16 / 9;
    overflow: hidden;
    background: rgba(var(--muted), 0.1);
}

.hm-featured-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform var(--duration-slow, 0.3s) ease-out;
}

.hm-featured-card:hover .hm-featured-img {
    transform: scale(1.04);
}

.hm-featured-meta {
    padding: 1.15rem;
}

.hm-featured-tags {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    flex-wrap: wrap;
    margin-bottom: 0.45rem;
}

.hm-tag {
    font-size: 0.72rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    padding: 0.2rem 0.55rem;
    border-radius: 6px;
}

.hm-tag--brand {
    background: rgba(var(--brand), 0.12);
    color: rgb(var(--brand));
}

.hm-tag--featured {
    background: linear-gradient(135deg, #f59e0b, #d97706);
    color: #ffffff;
}

.hm-featured-title {
    font-size: 1.05rem;
    font-weight: 800;
    color: rgb(var(--text));
    margin: 0 0 0.5rem;
    line-height: 1.35;
    overflow: hidden;
    text-overflow: ellipsis;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    min-height: 2.7em;
}

.hm-featured-info {
    font-size: 0.84rem;
    display: flex;
    flex-direction: column;
    gap: 0.35rem;
    align-items: flex-start;
    width: 100%;
}

.hm-featured-info-item {
    display: flex;
    align-items: center;
    gap: 0.45rem;
    width: 100%;
}

.hm-featured-date {
    font-size: 0.84rem;
    font-weight: 700;
    color: rgb(var(--brand));
}

.hm-featured-address {
    font-size: 0.82rem;
    color: rgb(var(--muted));
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.hm-featured-address span:last-child {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.hm-info-icon {
    flex-shrink: 0;
    font-size: 0.88rem;
    line-height: 1;
}

.hm-carousel-indicators {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.4rem;
    margin-top: 1rem;
}

.hm-carousel-dot {
    width: 8px;
    height: 8px;
    border-radius: 99px;
    border: none;
    background: rgba(var(--muted), 0.3);
    cursor: pointer;
    padding: 0;
    transition: all 0.2s ease;
}

.hm-carousel-dot[aria-selected="true"],
.hm-carousel-dot.active {
    width: 24px;
    background: rgb(var(--brand));
}

/* ─── Events Listing Grid ───────────────────────────────────── */
.hm-events-section {
    margin-bottom: 3.5rem;
}

.hm-active-filters-bar {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex-wrap: wrap;
    margin-top: 0.75rem;
}

.hm-filter-tag {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    font-size: 0.78rem;
    font-weight: 700;
    padding: 0.25rem 0.65rem;
    border-radius: 8px;
    background: rgba(var(--brand), 0.1);
    color: rgb(var(--brand));
    border: 1px solid rgba(var(--brand), 0.2);
}

.hm-clear-filters-link {
    font-size: 0.8rem;
    font-weight: 700;
    color: #ef4444;
    text-decoration: underline;
    margin-left: 0.35rem;
}

.hm-list-meta {
    font-size: 0.84rem;
    color: rgb(var(--muted));
    font-weight: 600;
}

.hm-events-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 1.5rem;
    margin-top: 1.25rem;
}

.hm-card {
    background: rgb(var(--card));
    border: 1px solid rgba(var(--muted), 0.16);
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 4px 18px rgba(0, 0, 0, 0.03);
    display: flex;
    flex-direction: column;
    transition: transform var(--duration-fast, 0.15s) ease-out, box-shadow var(--duration-fast, 0.15s) ease-out, border-color var(--duration-fast, 0.15s) ease-out;
}

.hm-card:hover {
    transform: translateY(-4px);
    border-color: rgba(var(--brand), 0.3);
    box-shadow: 0 14px 30px rgba(0, 0, 0, 0.07);
}

.hm-card-thumb-wrap {
    position: relative;
    width: 100%;
    aspect-ratio: 16 / 9;
    overflow: hidden;
    background: rgba(var(--muted), 0.1);
}

.hm-card-thumb {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease-out;
}

.hm-card:hover .hm-card-thumb {
    transform: scale(1.05);
}

.hm-card-badges-top-left {
    position: absolute;
    top: 0.75rem;
    left: 0.75rem;
    display: flex;
    flex-direction: column;
    gap: 0.35rem;
    align-items: flex-start;
    z-index: 2;
}

.hm-card-badge-top-left {
    background: rgba(0, 0, 0, 0.7);
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    color: #ffffff;
    font-size: 0.72rem;
    font-weight: 700;
    padding: 0.25rem 0.6rem;
    border-radius: 8px;
    letter-spacing: 0.03em;
    text-transform: uppercase;
}

.hm-card-badge-recurring {
    background: rgba(99, 102, 241, 0.88);
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    color: #ffffff;
    font-size: 0.68rem;
    font-weight: 700;
    padding: 0.2rem 0.55rem;
    border-radius: 6px;
    letter-spacing: 0.02em;
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    border: 1px solid rgba(255, 255, 255, 0.25);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.25);
}

.hm-card-price-badge {
    position: absolute;
    top: 0.75rem;
    right: 0.75rem;
    background: rgb(var(--brand));
    color: #ffffff;
    font-size: 0.78rem;
    font-weight: 800;
    padding: 0.25rem 0.65rem;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.25);
}

.hm-card-body {
    padding: 1.25rem;
    display: flex;
    flex-direction: column;
    flex: 1;
    justify-content: space-between;
    gap: 1rem;
}

.hm-card-info {
    display: flex;
    flex-direction: column;
    gap: 0.45rem;
}

.hm-card-date {
    display: flex;
    align-items: center;
    gap: 0.35rem;
    font-size: 0.8rem;
    font-weight: 700;
    color: rgb(var(--brand));
}

.hm-card-title {
    font-size: 1.12rem;
    font-weight: 800;
    color: rgb(var(--text));
    margin: 0;
    line-height: 1.35;
    overflow: hidden;
    text-overflow: ellipsis;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
}

.hm-card-location {
    display: flex;
    align-items: center;
    gap: 0.35rem;
    font-size: 0.82rem;
    color: rgb(var(--muted));
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.hm-card-btn {
    width: 100%;
    height: 42px;
    border-radius: 12px;
    background: rgba(var(--brand), 0.08);
    color: rgb(var(--brand)) !important;
    border: 1px solid rgba(var(--brand), 0.22);
    font-size: 0.88rem;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.4rem;
    text-decoration: none;
    transition: all var(--duration-fast, 0.15s) ease-out;
}

.hm-card:hover .hm-card-btn {
    background: rgb(var(--brand));
    color: #ffffff !important;
    border-color: rgb(var(--brand));
    box-shadow: 0 4px 14px rgba(var(--brand), 0.3);
}

/* ─── Empty State ───────────────────────────────────────────── */
.hm-empty-card {
    grid-column: 1 / -1;
    background: rgb(var(--card));
    border: 1px solid rgba(var(--muted), 0.16);
    border-radius: 22px;
    padding: 3.5rem 1.5rem;
    text-align: center;
}

.hm-empty-icon {
    font-size: 2.75rem;
    margin-bottom: 0.85rem;
    display: block;
}

.hm-empty-title {
    font-size: 1.25rem;
    font-weight: 800;
    color: rgb(var(--text));
    margin: 0 0 0.4rem;
}

.hm-empty-desc {
    font-size: 0.9rem;
    color: rgb(var(--muted));
    max-width: 440px;
    margin: 0 auto 1.5rem;
    line-height: 1.5;
}

/* ─── Pagination ────────────────────────────────────────────── */
.hm-pagination {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 0.4rem;
    margin-top: 2.5rem;
    flex-wrap: wrap;
}

.hm-page-btn {
    min-width: 38px;
    height: 38px;
    padding: 0 0.75rem;
    border-radius: 10px;
    border: 1px solid rgba(var(--muted), 0.18);
    background: rgb(var(--card));
    color: rgb(var(--text));
    font-size: 0.86rem;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    transition: all var(--duration-fast, 0.15s) ease-out;
}

.hm-page-btn:hover:not(.disabled) {
    background: rgba(var(--brand), 0.08);
    border-color: rgb(var(--brand));
    color: rgb(var(--brand));
}

.hm-page-btn.active {
    background: rgb(var(--brand));
    color: #ffffff;
    border-color: rgb(var(--brand));
    box-shadow: 0 4px 12px rgba(var(--brand), 0.25);
}

.hm-page-btn.disabled {
    opacity: 0.4;
    cursor: not-allowed;
    background: rgba(var(--muted), 0.05);
}

.hm-page-dots {
    color: rgb(var(--muted));
    font-weight: 800;
    padding: 0 0.35rem;
}

/* ─── Host CTA Callout ──────────────────────────────────────── */
.hm-cta {
    position: relative;
    border-radius: 28px;
    padding: clamp(2.5rem, 5vw, 4rem) clamp(1.5rem, 4vw, 3rem);
    text-align: center;
    background: radial-gradient(circle at 15% 25%, rgba(var(--brand), 0.15) 0%, transparent 45%),
                radial-gradient(circle at 85% 75%, rgba(16, 185, 129, 0.12) 0%, transparent 45%),
                linear-gradient(135deg, rgba(var(--card), 0.95), rgba(var(--card), 0.85));
    border: 1px solid rgba(var(--brand), 0.2);
    box-shadow: 0 16px 40px -15px rgba(var(--brand), 0.15);
    margin-top: 2rem;
    overflow: hidden;
}

.hm-cta-content {
    max-width: 640px;
    margin: 0 auto;
    position: relative;
    z-index: 1;
}

.hm-cta-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.35rem 0.85rem;
    border-radius: 999px;
    font-size: 0.78rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: rgb(var(--brand));
    background: rgba(var(--brand), 0.1);
    border: 1px solid rgba(var(--brand), 0.2);
    margin-bottom: 1rem;
}

.hm-cta-title {
    font-size: clamp(1.75rem, 4vw, 2.5rem);
    font-weight: 900;
    line-height: 1.2;
    letter-spacing: -0.025em;
    color: rgb(var(--text));
    margin: 0 0 0.85rem;
}

.hm-cta-desc {
    font-size: clamp(0.95rem, 1.8vw, 1.1rem);
    color: rgb(var(--muted));
    line-height: 1.6;
    margin: 0 auto 1.85rem;
}

.hm-cta-actions {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.85rem;
    flex-wrap: wrap;
}

.hm-btn-primary {
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

.hm-btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 28px rgba(var(--brand), 0.45);
    filter: brightness(1.05);
}

.hm-btn-secondary {
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

.hm-btn-secondary:hover {
    background: rgba(var(--muted), 0.14);
}

/* ═════════════════════════════════════════════════════════════
   Responsive Design Breakpoints
   ═════════════════════════════════════════════════════════════ */

/* Tablet (max 1024px) */
@media (max-width: 1024px) {
    .hm-events-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 1.25rem;
    }
}

/* Mobile & Small Tablets (max 768px) */
@media (max-width: 768px) {
    .perspective-stage {
        perspective: none !important;
        margin-bottom: 2rem !important;
    }

    .hm-hero {
        padding: clamp(1.75rem, 5vw, 2.25rem) clamp(1rem, 4vw, 1.5rem);
        border-radius: 20px;
        margin-bottom: 0;
    }

    .hm-hero-3d {
        transform: none !important;
    }

    .hm-search-form {
        flex-direction: column;
        align-items: stretch;
        gap: 0.65rem;
        padding: 0.75rem;
        border-radius: 16px;
    }

    .hm-search-group,
    .hm-search-select-wrap {
        padding: 0 0.75rem;
        flex: none;
        width: 100%;
        height: 48px;
        background: rgba(var(--muted), 0.05);
        border: 1px solid rgba(var(--muted), 0.15);
        border-radius: 12px;
        box-sizing: border-box;
    }

    .hm-search-input,
    .hm-search-select {
        height: 100%;
        font-size: 16px; /* Prevents iOS Safari auto-zoom */
    }

    .hm-search-divider {
        display: none;
    }

    .hm-search-btn {
        width: 100%;
        height: 48px;
        border-radius: 12px;
        font-size: 1rem;
    }

    .hm-trust-bar {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.5rem;
        margin-top: 1.35rem;
        padding-top: 1.25rem;
    }

    .hm-trust-item {
        justify-content: center;
        font-size: 0.76rem;
        padding: 0.45rem 0.5rem;
        text-align: center;
        white-space: normal;
        line-height: 1.25;
    }

    .hm-categories-section {
        margin-bottom: 2rem;
    }

    .hm-chips {
        margin: 0 -0.5rem;
        padding: 0.25rem 0.5rem 0.5rem;
        gap: 0.45rem;
    }

    .hm-chip-btn {
        padding: 0.5rem 0.9rem;
        font-size: 0.82rem;
    }

    .hm-featured-section {
        margin-bottom: 2.25rem;
    }

    .hm-featured-carousel {
        padding: 1rem 0.75rem;
        border-radius: 18px;
    }

    .hm-featured-card-link {
        flex: 0 0 clamp(240px, 80vw, 320px);
    }

    .hm-carousel-slides {
        gap: 0.85rem;
        padding: 0.35rem 0.2rem 0.5rem;
    }

    .hm-section-title-wrap {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.35rem;
    }

    .hm-events-grid {
        grid-template-columns: 1fr;
        gap: 1.15rem;
    }

    .hm-cta {
        padding: 2.25rem 1.15rem;
        border-radius: 20px;
    }

    .hm-cta-actions {
        flex-direction: column;
        width: 100%;
        gap: 0.65rem;
    }

    .hm-btn-primary,
    .hm-btn-secondary {
        width: 100%;
        height: 48px;
    }

    .reveal-on-scroll {
        opacity: 1 !important;
        transform: none !important;
        transition: none !important;
    }

    .card-3d, .hm-card, .hm-featured-card {
        transform: none !important;
    }

    .card-glare {
        display: none !important;
    }
}

/* Small Mobile (max 480px) */
@media (max-width: 480px) {
    .hm-hero__title {
        font-size: clamp(1.65rem, 7vw, 2.25rem);
    }

    .hm-hero__subtitle {
        font-size: 0.92rem;
        margin-bottom: 1.35rem;
    }

    .hm-eyebrow {
        font-size: 0.72rem;
        padding: 0.25rem 0.65rem;
    }

    .hm-card-body {
        padding: 1rem;
    }

    .hm-card-btn {
        height: 44px;
        font-size: 0.86rem;
    }

    .hm-pagination {
        gap: 0.3rem;
    }

    .hm-page-btn {
        min-width: 34px;
        height: 34px;
        padding: 0 0.45rem;
        font-size: 0.8rem;
        border-radius: 8px;
    }
}

/* Very Small Mobile (< 380px) */
@media (max-width: 380px) {
    .hm-trust-bar {
        grid-template-columns: 1fr;
        gap: 0.35rem;
    }

    .hm-featured-card-link {
        flex: 0 0 calc(90vw - 1rem);
        min-width: 220px;
    }

    .hm-hero {
        padding: 1.5rem 0.75rem;
    }
}

/* ─── Modern 3D Perspective, Physics Tilt & Progressive Loading ─── */
.perspective-stage {
    perspective: 1200px;
    transform-style: preserve-3d;
}

.hm-hero-3d {
    transform-style: preserve-3d;
    transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.4s ease;
    will-change: transform;
}

.hm-hero-3d:hover {
    box-shadow: 0 24px 60px -15px rgba(var(--brand), 0.22), 0 4px 16px rgba(0, 0, 0, 0.05);
}

.card-3d, .hm-card, .hm-featured-card {
    transform-style: preserve-3d;
    position: relative;
    will-change: transform;
    backface-visibility: hidden;
    transition: transform 0.25s cubic-bezier(0.2, 0.8, 0.2, 1), box-shadow 0.25s cubic-bezier(0.2, 0.8, 0.2, 1), border-color 0.2s ease;
}

/* Dynamic Specular Sheen Glare */
.card-glare {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    pointer-events: none;
    border-radius: inherit;
    z-index: 5;
    opacity: 0;
    background: radial-gradient(circle 300px at var(--glare-x, 50%) var(--glare-y, 50%), rgba(255, 255, 255, 0.28), transparent 75%);
    transition: opacity 0.3s ease;
}

:root[data-theme='dark'] .card-glare {
    background: radial-gradient(circle 300px at var(--glare-x, 50%) var(--glare-y, 50%), rgba(255, 255, 255, 0.15), transparent 70%);
}

.card-3d:hover .card-glare,
.hm-card:hover .card-glare,
.hm-featured-card:hover .card-glare {
    opacity: 1;
}

/* 3D Elevated Inner Elements */
.hm-card:hover .hm-card-badge-top-left {
    transform: translateZ(16px);
}
.hm-card:hover .hm-card-price-badge {
    transform: translateZ(18px);
}
.hm-card:hover .hm-card-title {
    transform: translateZ(10px);
}

/* ─── Scroll-Driven Smooth Entrance ────────────────────────── */
.reveal-on-scroll {
    opacity: 0;
    transform: translateY(16px);
    transition: opacity 0.45s cubic-bezier(0.16, 1, 0.3, 1), transform 0.45s cubic-bezier(0.16, 1, 0.3, 1);
    will-change: opacity, transform;
}

.reveal-on-scroll.revealed {
    opacity: 1;
    transform: translateY(0);
}

@media (prefers-reduced-motion: reduce) {
    .reveal-on-scroll {
        opacity: 1 !important;
        transform: none !important;
        transition: none !important;
    }
    .card-3d, .hm-card, .hm-featured-card {
        transform: none !important;
    }
    .card-glare {
        display: none !important;
    }
}

/* ─── Progressive Blur-Up Lazy Loading ─────────────────────── */
.progressive-img-wrap {
    position: relative;
    overflow: hidden;
    background: rgba(var(--muted), 0.08);
}

.progressive-img-wrap img {
    opacity: 0;
    filter: blur(14px);
    transform: scale(1.04);
    transition: opacity 0.4s ease-out, filter 0.4s ease-out, transform 0.4s ease-out;
}

.progressive-img-wrap img.is-loaded {
    opacity: 1;
    filter: blur(0);
    transform: scale(1);
}

.progressive-skeleton {
    position: absolute;
    inset: 0;
    background: linear-gradient(90deg, rgba(var(--muted), 0.04) 25%, rgba(var(--muted), 0.12) 50%, rgba(var(--muted), 0.04) 75%);
    background-size: 200% 100%;
    animation: progressiveShimmer 1.8s infinite;
    transition: opacity 0.3s ease-out;
    pointer-events: none;
    z-index: 2;
}

.progressive-img-wrap.has-loaded .progressive-skeleton {
    opacity: 0;
}

@keyframes progressiveShimmer {
    0% { background-position: 200% 0; }
    100% { background-position: -200% 0; }
}
</style>
@endsection

@section('content')
<div class="hm-wrapper">

    <!-- ═══ Hero Section ═══ -->
    <div class="perspective-stage">
        <header class="hm-hero hm-hero-3d">
        <div class="hm-hero__content">
            <span class="hm-eyebrow">
                <span class="icon">✨</span> Discover &bull; Experience &bull; Celebrate
            </span>

            <h1 class="hm-hero__title">
                Find live events, concerts &amp; <span class="hm-hero__title-highlight">experiences</span>
            </h1>

            <p class="hm-hero__subtitle">
                Search, compare and secure admission tickets in seconds with verified digital passes.
            </p>

            <!-- Dynamic Search Form -->
            <form id="search-form" class="hm-search-form" role="search" aria-label="Search events" method="GET" action="{{ route('/') }}">
                <div class="hm-search-group">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                    <input name="q" id="search-input" type="search" class="hm-search-input" placeholder="Search events, artists, venues..." aria-label="Search keywords" value="<?= e($search ?? '') ?>" autocomplete="off">
                </div>

                <div class="hm-search-divider" aria-hidden="true"></div>

                <div class="hm-search-select-wrap">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                        <circle cx="12" cy="10" r="3"></circle>
                    </svg>
                    <select name="location" id="search-location" class="hm-search-select" aria-label="Event location filter">
                        <option value="">All Locations</option>
                        <?php if (!empty($locations)): ?>
                            <?php foreach ($locations as $loc): ?>
                                <option value="<?= e($loc) ?>" <?= ($location ?? '') === $loc ? 'selected' : '' ?>>
                                    <?= e($loc) ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <?php if (!empty($category)): ?>
                    <input type="hidden" name="category" value="<?= e($category) ?>">
                <?php endif; ?>

                <button type="submit" class="hm-search-btn">
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                    <span>Find Events</span>
                </button>
            </form>

            <!-- Trust Metrics Bar -->
            <div class="hm-trust-bar">
                <div class="hm-trust-item"><span class="icon">🎟️</span> 50k+ Passes Issued</div>
                <div class="hm-trust-item"><span class="icon">⚡</span> Instant QR Gate Entry</div>
                <div class="hm-trust-item"><span class="icon">🔒</span> Paystack Protected</div>
                <div class="hm-trust-item"><span class="icon">⭐</span> 4.9/5 Organizer Rating</div>
            </div>
        </div>
        </header>
    </div>

    <!-- ═══ Popular Categories Navigation ═══ -->
    <section class="hm-categories-section reveal-on-scroll">
        <div class="hm-section-title-wrap">
            <div>
                <h2 class="hm-section-heading">Explore by Category</h2>
                <p class="hm-section-subheading">Browse experiences that match your vibe</p>
            </div>
        </div>

        <nav class="hm-chips" aria-label="Event categories">
            <?php 
            $catIcons = [
                'All' => '🔥',
                'Concerts' => '🎵',
                'Tech' => '💻',
                'Comedy' => '🎭',
                'Sports' => '⚽',
                'Theater' => '🎪',
                'Family' => '👨‍👩‍👧',
                'Art & Wine' => '🎨',
            ];
            foreach ($categories ?? [] as $cat):
                $isCatActive = (empty($category) && $cat === 'All') || (strtolower($category ?? '') === strtolower($cat));
                $catUrl = route('/') . '?category=' . urlencode($cat);
                if (!empty($search)) $catUrl .= '&q=' . urlencode($search);
                if (!empty($location)) $catUrl .= '&location=' . urlencode($location);
                $icon = $catIcons[$cat] ?? '✨';
            ?>
                <a href="<?= $catUrl ?>" class="hm-chip-btn <?= $isCatActive ? 'hm-chip-btn--active' : '' ?>">
                    <span><?= $icon ?></span>
                    <span><?= e($cat) ?></span>
                </a>
            <?php endforeach; ?>
        </nav>
    </section>

    <!-- ═══ Featured Events Carousel ═══ -->
    <?php 
        $isFiltered = !empty($search) || !empty($location) || (!empty($category) && strtolower($category) !== 'all') || ($page ?? 1) > 1;
        $hasFeatured = !empty($featuredEvents) && count($featuredEvents) > 1;
    ?>
    <?php if ($hasFeatured && !$isFiltered): ?>
        <section class="hm-featured-section reveal-on-scroll">
            <div class="hm-featured-carousel" id="carousel">
                <div class="hm-section-title-wrap" style="margin-bottom: 0.85rem;">
                    <div>
                        <h2 class="hm-section-heading">Featured Highlights</h2>
                        <p class="hm-section-subheading">Hand-picked events and premium experiences</p>
                    </div>

                    <div class="hm-carousel-nav">
                        <button type="button" class="hm-carousel-btn carousel-prev" aria-label="Previous slide">◀</button>
                        <button type="button" class="hm-carousel-btn carousel-next" aria-label="Next slide">▶</button>
                    </div>
                </div>

                <div class="hm-carousel-slides slides" id="slides" aria-live="polite">
                    <?php foreach ($featuredEvents as $idx => $fe):
                        $feUrl = event_url($fe);
                        $feImg = !empty($fe['image']) ? $fe['image'] : 'https://images.unsplash.com/photo-1501281668745-f7f57925c3b4?w=1200&q=80&auto=format&fit=crop';
                        $feDate = date('M d, Y', strtotime($fe['date']));
                    ?>
                        <a href="<?= $feUrl ?>" class="hm-featured-card-link event-card-link">
                            <article class="hm-featured-card card-3d">
                                <div class="card-glare"></div>
                                <div class="hm-featured-img-wrap progressive-img-wrap">
                                    <div class="progressive-skeleton"></div>
                                    <img src="<?= e($feImg) ?>" alt="<?= e($fe['name']) ?>" class="hm-featured-img" loading="lazy" decoding="async">
                                </div>
                                <div class="hm-featured-meta">
                                    <div class="hm-featured-tags">
                                        <span class="hm-tag hm-tag--brand"><?= e($fe['category'] ?? 'Event') ?></span>
                                        <?php if (!empty($fe['is_recurring'])): ?>
                                            <span class="hm-tag" style="background: rgba(99, 102, 241, 0.2); color: #818cf8; border: 1px solid rgba(99, 102, 241, 0.4);">🔄 Recurring</span>
                                        <?php endif; ?>
                                        <?php if (!empty($fe['is_featured'])): ?>
                                            <span class="hm-tag hm-tag--featured">⭐ Featured</span>
                                        <?php endif; ?>
                                    </div>
                                    <h3 class="hm-featured-title" title="<?= e($fe['name']) ?>"><?= e($fe['name']) ?></h3>
                                    <div class="hm-featured-info">
                                        <div class="hm-featured-info-item hm-featured-date">
                                            <span class="hm-info-icon" aria-hidden="true">📅</span>
                                            <span><?= $feDate ?></span>
                                        </div>
                                        <div class="hm-featured-info-item hm-featured-address" title="<?= e($fe['location']) ?>">
                                            <span class="hm-info-icon" aria-hidden="true">📍</span>
                                            <span><?= e($fe['location']) ?></span>
                                        </div>
                                    </div>
                                </div>
                            </article>
                        </a>
                    <?php endforeach; ?>
                </div>

                <div class="hm-carousel-indicators indicators" id="carousel-indicators" role="tablist" aria-label="Carousel pagination">
                    <?php foreach ($featuredEvents as $idx => $fe): ?>
                        <button class="hm-carousel-dot indicator <?= $idx === 0 ? 'active' : '' ?>" role="tab" aria-selected="<?= $idx === 0 ? 'true' : 'false' ?>" aria-label="Slide <?= $idx + 1 ?>"></button>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <!-- ═══ All Events Listing Section ═══ -->
    <?php
        $events = $events ?? [];
        $total = $total ?? count($events);
        $page = isset($page) ? max(1, (int) $page) : 1;
        $totalPages = isset($totalPages) ? max(1, (int) $totalPages) : 1;

        // Dynamic Heading & Context
        $headingTitle = 'Upcoming Events';
        $headingNote = 'Browse all available public experiences with real-time pass booking.';

        if (!empty($search) && !empty($location)) {
            $headingTitle = 'Search: "' . e($search) . '" in ' . e($location);
            $headingNote = 'Showing matching events for your search keywords and location.';
        } elseif (!empty($search)) {
            $headingTitle = 'Search Results for "' . e($search) . '"';
            $headingNote = 'Showing events matching your keyword.';
        } elseif (!empty($location)) {
            $headingTitle = 'Events in ' . e($location);
            $headingNote = 'Showing upcoming experiences happening in ' . e($location) . '.';
        } elseif (!empty($category) && strtolower($category) !== 'all') {
            $headingTitle = e($category) . ' Events';
            $headingNote = 'Showing upcoming ' . strtolower(e($category)) . ' experiences.';
        }
    ?>
    <section class="hm-events-section reveal-on-scroll" id="events-section">
        <div class="hm-section-title-wrap">
            <div>
                <h2 class="hm-section-heading"><?= $headingTitle ?></h2>
                <p class="hm-section-subheading"><?= $headingNote ?></p>

                <?php if ($isFiltered): ?>
                    <div class="hm-active-filters-bar">
                        <span style="font-size:0.8rem; color:rgb(var(--muted)); font-weight:700;">Filters:</span>
                        <?php if (!empty($search)): ?>
                            <span class="hm-filter-tag">🔍 "<?= e($search) ?>"</span>
                        <?php endif; ?>
                        <?php if (!empty($location)): ?>
                            <span class="hm-filter-tag">📍 <?= e($location) ?></span>
                        <?php endif; ?>
                        <?php if (!empty($category) && strtolower($category) !== 'all'): ?>
                            <span class="hm-filter-tag">🏷️ <?= e($category) ?></span>
                        <?php endif; ?>
                        <a href="{{ route('/') }}" class="hm-clear-filters-link">✕ Clear All</a>
                    </div>
                <?php endif; ?>
            </div>

            <div class="hm-list-meta">
                Showing <?= count($events) ?> of <?= $total ?> event<?= $total !== 1 ? 's' : '' ?>
                <?= $totalPages > 1 ? "(Page {$page} of {$totalPages})" : '' ?>
            </div>
        </div>

        <div class="hm-events-grid">
            <?php if (empty($events)): ?>
                <div class="hm-empty-card">
                    <span class="hm-empty-icon">🔍</span>
                    <h3 class="hm-empty-title">No Events Found</h3>
                    <p class="hm-empty-desc">
                        We couldn't find any published events matching your search or filter criteria. Try adjusting your keyword or location.
                    </p>
                    <a href="{{ route('/') }}" class="hm-btn-primary" style="display:inline-flex;">
                        Browse All Upcoming Events
                    </a>
                </div>
            <?php else: ?>
                <?php foreach ($events as $ev):
                    $evUrl = event_url($ev);
                    $evImg = !empty($ev['image']) ? $ev['image'] : 'https://images.unsplash.com/photo-1501281668745-f7f57925c3b4?w=1200&q=80&auto=format&fit=crop';
                    $minPrice = isset($ev['min_price']) && $ev['min_price'] !== null ? format_price($ev['min_price']) : 'Free';
                    $evDate = date('M d, Y • g:i A', strtotime($ev['date']));
                ?>
                    <article class="hm-card card-3d reveal-on-scroll">
                        <div class="card-glare"></div>
                        <div class="hm-card-thumb-wrap progressive-img-wrap">
                            <div class="progressive-skeleton"></div>
                            <img src="<?= e($evImg) ?>" alt="<?= e($ev['name']) ?>" class="hm-card-thumb" loading="lazy" decoding="async">
                            <div class="hm-card-badges-top-left">
                                <span class="hm-card-badge-top-left"><?= e($ev['category'] ?? 'Event') ?></span>
                                <?php if (!empty($ev['is_recurring'])): ?>
                                    <span class="hm-card-badge-recurring" title="Recurring event series">🔄 Recurring</span>
                                <?php endif; ?>
                            </div>
                            <span class="hm-card-price-badge"><?= $minPrice ?></span>
                        </div>

                        <div class="hm-card-body">
                            <div class="hm-card-info">
                                <span class="hm-card-date">
                                    <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                        <line x1="16" y1="2" x2="16" y2="6"></line>
                                        <line x1="8" y1="2" x2="8" y2="6"></line>
                                        <line x1="3" y1="10" x2="21" y2="10"></line>
                                    </svg>
                                    <?= $evDate ?>
                                </span>

                                <h3 class="hm-card-title" title="<?= e($ev['name']) ?>">
                                    <?= e($ev['name']) ?>
                                </h3>

                                <div class="hm-card-location" title="<?= e($ev['location']) ?>">
                                    <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                        <circle cx="12" cy="10" r="3"></circle>
                                    </svg>
                                    <span><?= e($ev['location']) ?></span>
                                </div>
                            </div>

                            <a href="<?= $evUrl ?>" class="hm-card-btn">
                                <span>View Tickets &amp; Details</span>
                                <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <line x1="5" y1="12" x2="19" y2="12"></line>
                                    <polyline points="12 5 19 12 12 19"></polyline>
                                </svg>
                            </a>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- ═══ Smart Windowed Pagination ═══ -->
        <?php if ($totalPages > 1): ?>
            <?php
                $queryParams = [];
                if (!empty($search)) $queryParams['q'] = $search;
                if (!empty($location)) $queryParams['location'] = $location;
                if (!empty($category)) $queryParams['category'] = $category;

                $buildPageUrl = function($p) use ($queryParams) {
                    $queryParams['page'] = $p;
                    return route('/') . '?' . http_build_query($queryParams);
                };

                // Smart pagination window
                $range = pagination_range($page, $totalPages);
            ?>
            <nav class="hm-pagination" aria-label="Events pagination">
                <?php if ($page > 1): ?>
                    <a href="<?= $buildPageUrl($page - 1) ?>" class="hm-page-btn" aria-label="Previous page">&laquo; Prev</a>
                <?php else: ?>
                    <span class="hm-page-btn disabled" aria-disabled="true">&laquo; Prev</span>
                <?php endif; ?>

                <?php foreach ($range as $r): ?>
                    <?php if ($r === '...'): ?>
                        <span class="hm-page-dots">&hellip;</span>
                    <?php elseif ($r == $page): ?>
                        <span class="hm-page-btn active" aria-current="page"><?= $r ?></span>
                    <?php else: ?>
                        <a href="<?= $buildPageUrl($r) ?>" class="hm-page-btn" aria-label="Page <?= $r ?>"><?= $r ?></a>
                    <?php endif; ?>
                <?php endforeach; ?>

                <?php if ($page < $totalPages): ?>
                    <a href="<?= $buildPageUrl($page + 1) ?>" class="hm-page-btn" aria-label="Next page">Next &raquo;</a>
                <?php else: ?>
                    <span class="hm-page-btn disabled" aria-disabled="true">Next &raquo;</span>
                <?php endif; ?>
            </nav>
        <?php endif; ?>
    </section>

    <!-- ═══ Host / Organizer Callout ═══ -->
    <section class="hm-cta reveal-on-scroll">
        <div class="hm-cta-content">
            <span class="hm-cta-badge">
                ✨ Host &amp; Organizer Hub
            </span>
            <h2 class="hm-cta-title">Planning an event? Sell tickets in minutes</h2>
            <p class="hm-cta-desc">
                Join thousands of organizers using <strong><?= e(site_setting('site_title', 'Eventlyy')) ?></strong> to create custom ticket tiers, scan attendee gate passes instantly with optical QR codes, and receive direct bank disbursements.
            </p>

            <div class="hm-cta-actions">
                <?php if (app()->user && app()->user->isHost()): ?>
                    <a href="{{ route('host/event/create') }}" class="hm-btn-primary">
                        Create an Event →
                    </a>
                <?php else: ?>
                    <a href="{{ route('register') }}" class="hm-btn-primary">
                        Become a Host &amp; Start Free →
                    </a>
                <?php endif; ?>
                <a href="{{ route('about') }}" class="hm-btn-secondary">
                    Learn How It Works
                </a>
            </div>
        </div>
    </section>

</div>

<?php if ($isFiltered && (!empty($search) || !empty($location))): ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var el = document.getElementById('events-section');
        if (el) {
            el.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    });
</script>
<?php endif; ?>
@endsection