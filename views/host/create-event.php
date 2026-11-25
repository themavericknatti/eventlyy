<?php

/**
 * Host Create Event View
 *
 * Provides a responsive, multi-section event creation wizard for hosts.
 *
 * @package Evently
 * @subpackage Views/Host
 * @author Celio Natti
 * @version 2.0.0
 */

$siteName = site_setting('site_title', 'Eventlyy');
?>

@extends('layouts/host')

@section('title')
Create Event - <?= e($siteName) ?> Host
@endsection

@section('content')
<style>
/* ═══════════════════════════════════════════════════════════
   Host Create Event — Responsive Form Polish & System Tokens
   ═══════════════════════════════════════════════════════════ */

.create-event-wrapper {
    max-width: 900px;
    margin: 0 auto 3rem;
    width: 100%;
}

/* Page Hero */
.create-event-hero {
    margin-bottom: 1.75rem;
}

.create-event-hero .panel-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    font-size: 0.78rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    padding: 0.3rem 0.75rem;
    border-radius: 99px;
    background: rgba(var(--brand), 0.1);
    color: rgb(var(--brand));
    border: 1px solid rgba(var(--brand), 0.22);
    margin-bottom: 0.5rem;
}

.create-event-hero h1 {
    font-size: clamp(1.65rem, 3.2vw, 2.35rem);
    font-weight: 900;
    color: rgb(var(--text));
    margin: 0 0 0.45rem;
    letter-spacing: -0.025em;
    line-height: 1.2;
}

.create-event-hero .lede {
    font-size: 0.95rem;
    color: rgb(var(--muted));
    margin: 0;
    line-height: 1.5;
}

/* Main Form Card */
.form-card {
    background: rgb(var(--card));
    border: 1px solid rgba(var(--muted), 0.16);
    border-radius: 24px;
    padding: 2.25rem;
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.04);
}

/* Section Cards */
.event-form-section {
    background: rgba(var(--muted), 0.03);
    border: 1px solid rgba(var(--muted), 0.13);
    border-radius: 18px;
    padding: 1.5rem;
    margin-bottom: 1.75rem;
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
}

.event-form-section:focus-within {
    border-color: rgba(var(--brand), 0.35);
}

.event-section-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.75rem;
    margin-bottom: 1.25rem;
    flex-wrap: wrap;
    padding-bottom: 0.75rem;
    border-bottom: 1px solid rgba(var(--muted), 0.1);
}

.event-section-title-wrap {
    display: flex;
    align-items: center;
    gap: 0.65rem;
}

.event-section-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 36px;
    height: 36px;
    border-radius: 10px;
    background: rgba(var(--brand), 0.1);
    color: rgb(var(--brand));
    font-size: 1.15rem;
    flex-shrink: 0;
}

.event-section-title {
    margin: 0;
    font-size: 1.15rem;
    font-weight: 800;
    color: rgb(var(--text));
}

.event-section-subtitle {
    margin: 0.2rem 0 0;
    font-size: 0.83rem;
    color: rgb(var(--muted));
    line-height: 1.4;
}

/* Form Fields */
.event-field-group {
    display: flex;
    flex-direction: column;
    gap: 0.4rem;
    margin-bottom: 1.15rem;
}

.event-field-group:last-child {
    margin-bottom: 0;
}

.event-label {
    font-size: 0.84rem;
    font-weight: 700;
    color: rgb(var(--text));
    display: flex;
    align-items: center;
    gap: 0.3rem;
}

.event-label .required-star {
    color: #ef4444;
}

.event-label .optional-tag {
    font-size: 0.75rem;
    color: rgb(var(--muted));
    font-weight: 500;
}

.event-input,
.event-select,
.event-textarea,
.ticket-tier-grid input,
.guest-row-grid input {
    width: 100%;
    min-height: 48px;
    padding: 0.75rem 1rem;
    border-radius: 12px;
    border: 1px solid rgba(var(--muted), 0.22);
    background: rgba(var(--bg), 0.85);
    color: rgb(var(--text));
    font-size: 0.95rem;
    font-family: inherit;
    box-sizing: border-box;
    transition: all 0.2s ease;
}

.event-select option {
    background-color: #ffffff;
    color: #0f172a;
}

:root[data-theme='dark'] .event-select option {
    background-color: #1e293b;
    color: #f8fafc;
}

.event-textarea {
    min-height: 120px;
    resize: vertical;
    line-height: 1.5;
}

.event-input:focus,
.event-select:focus,
.event-textarea:focus,
.ticket-tier-grid input:focus,
.guest-row-grid input:focus {
    outline: none;
    border-color: rgb(var(--brand));
    background: rgb(var(--card));
    box-shadow: 0 0 0 3px rgba(var(--brand), 0.14);
}

.event-help-text {
    font-size: 0.8rem;
    color: rgb(var(--muted));
    margin-top: 0.3rem;
    line-height: 1.4;
}

/* Custom URL Input Prefix */
.url-slug-wrapper {
    display: flex;
    align-items: center;
    gap: 0;
    border-radius: 12px;
    border: 1px solid rgba(var(--muted), 0.22);
    background: rgba(var(--bg), 0.85);
    overflow: hidden;
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
}

.url-slug-wrapper:focus-within {
    border-color: rgb(var(--brand));
    background: rgb(var(--card));
    box-shadow: 0 0 0 3px rgba(var(--brand), 0.14);
}

.url-prefix-badge {
    padding: 0.75rem 0.85rem;
    font-size: 0.88rem;
    font-weight: 700;
    color: rgb(var(--muted));
    background: rgba(var(--muted), 0.08);
    border-right: 1px solid rgba(var(--muted), 0.15);
    white-space: nowrap;
    user-select: none;
}

.url-slug-input {
    border: none !important;
    background: transparent !important;
    box-shadow: none !important;
    min-height: 48px;
    padding: 0.75rem 1rem;
    width: 100%;
    color: rgb(var(--text));
    font-size: 0.95rem;
    font-family: inherit;
    box-sizing: border-box;
}

.url-slug-input:focus {
    outline: none !important;
}

/* Responsive Grid Rows */
.event-grid-2 {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.15rem;
    margin-bottom: 1.15rem;
}

.event-grid-3 {
    display: grid;
    grid-template-columns: 1.1fr 1fr 1fr;
    gap: 1.15rem;
    margin-bottom: 1.15rem;
}

/* Image Upload Container */
.image-upload-card {
    border: 2px dashed rgba(var(--muted), 0.25);
    border-radius: 16px;
    padding: 1.25rem;
    background: rgba(var(--muted), 0.02);
    text-align: center;
    transition: all 0.2s ease;
}

.image-upload-card:hover {
    border-color: rgb(var(--brand));
    background: rgba(var(--brand), 0.02);
}

.preview-container {
    position: relative;
    display: inline-block;
    max-width: 100%;
    border-radius: 14px;
    overflow: hidden;
    margin-top: 0.85rem;
    box-shadow: 0 4px 18px rgba(0, 0, 0, 0.08);
}

.preview-container img {
    display: block;
    max-width: 100%;
    max-height: 240px;
    object-fit: cover;
    border-radius: 14px;
}

.preview-close-btn {
    position: absolute;
    top: 8px;
    right: 8px;
    background: rgba(0, 0, 0, 0.78);
    color: #ffffff;
    border: none;
    border-radius: 50%;
    width: 30px;
    height: 30px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    font-weight: 700;
    transition: background 0.2s ease;
}

.preview-close-btn:hover {
    background: #ef4444;
}

/* Special Guests & Performer Cards */
.guest-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.guest-card,
.guest-row {
    background: rgb(var(--card));
    border: 1px solid rgba(var(--muted), 0.18);
    border-radius: 16px;
    padding: 1.25rem 1.35rem;
    box-shadow: 0 4px 14px rgba(0, 0, 0, 0.02);
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
}

.guest-card:focus-within,
.guest-row:focus-within {
    border-color: rgba(var(--brand), 0.45);
    box-shadow: 0 4px 16px rgba(var(--brand), 0.06);
}

.guest-card-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding-bottom: 0.75rem;
    margin-bottom: 0.95rem;
    border-bottom: 1px solid rgba(var(--muted), 0.1);
    gap: 0.75rem;
}

.guest-card-avatar {
    display: inline-flex;
    align-items: center;
    gap: 0.55rem;
    font-size: 0.88rem;
    font-weight: 800;
    color: rgb(var(--text));
}

.guest-card-avatar-circle {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: rgba(var(--brand), 0.12);
    color: rgb(var(--brand));
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 0.95rem;
}

.guest-card-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

.guest-remove,
.guest-card-remove-btn {
    background: rgba(239, 68, 68, 0.08);
    color: #ef4444 !important;
    border: 1px solid rgba(239, 68, 68, 0.2);
    border-radius: 9px;
    padding: 0.45rem 0.85rem;
    font-size: 0.82rem;
    font-weight: 700;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    transition: all 0.2s ease;
    font-family: inherit;
    min-height: 38px;
}

.guest-remove:hover,
.guest-card-remove-btn:hover {
    background: #ef4444;
    color: #ffffff !important;
    border-color: #ef4444;
    transform: translateY(-1px);
}

/* ═══════════════════════════════════════════════════════════
   Executive Ticket Package Cards (Bento Style)
   ═══════════════════════════════════════════════════════════ */
.ticket-list {
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
}

.ticket-card,
.ticket-row-item {
    background: rgb(var(--card));
    border: 1px solid rgba(var(--muted), 0.2);
    border-radius: 18px;
    padding: 1.35rem 1.45rem;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.03);
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
    position: relative;
}

.ticket-card:focus-within,
.ticket-row-item:focus-within {
    border-color: rgba(var(--brand), 0.45);
    box-shadow: 0 6px 20px rgba(var(--brand), 0.08);
}

.ticket-card-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding-bottom: 0.85rem;
    margin-bottom: 1rem;
    border-bottom: 1px solid rgba(var(--muted), 0.12);
    gap: 0.75rem;
}

.ticket-card-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    font-size: 0.82rem;
    font-weight: 800;
    color: rgb(var(--brand));
    background: rgba(var(--brand), 0.08);
    padding: 0.3rem 0.75rem;
    border-radius: 99px;
    border: 1px solid rgba(var(--brand), 0.2);
}

.ticket-card-remove-btn,
.ticket-remove-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.4rem;
    padding: 0.45rem 0.95rem;
    border-radius: 10px;
    background: rgba(239, 68, 68, 0.08);
    color: #ef4444 !important;
    border: 1px solid rgba(239, 68, 68, 0.2);
    font-size: 0.82rem;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.2s ease;
    min-height: 38px;
    font-family: inherit;
}

.ticket-card-remove-btn:hover:not(:disabled),
.ticket-remove-btn:hover:not(:disabled) {
    background: #ef4444;
    color: #ffffff !important;
    border-color: #ef4444;
    transform: translateY(-1px);
}

.ticket-card-remove-btn:disabled,
.ticket-remove-btn:disabled {
    background: rgba(var(--muted), 0.08);
    color: rgba(var(--text), 0.4) !important;
    border-color: rgba(var(--muted), 0.2);
    cursor: not-allowed;
}

.ticket-primary-grid {
    display: grid;
    grid-template-columns: 2fr 1fr 1fr;
    gap: 1rem;
    margin-bottom: 1.15rem;
}

.ticket-access-shelf {
    background: rgba(var(--muted), 0.035);
    border: 1px solid rgba(var(--muted), 0.14);
    border-radius: 14px;
    padding: 1rem 1.15rem;
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.25rem;
}

.ticket-subfield {
    display: flex;
    flex-direction: column;
    gap: 0.4rem;
}

.ticket-subfield-header {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    font-size: 0.84rem;
    font-weight: 700;
    color: rgb(var(--text));
}

.ticket-subfield-hint {
    font-size: 0.74rem;
    color: rgb(var(--muted));
    line-height: 1.35;
    margin-top: 0.25rem;
}

/* Add-Ons & Merchandise Section */
.addon-card,
.addon-row-item {
    background: rgb(var(--card));
    border: 1px solid rgba(var(--muted), 0.2);
    border-radius: 18px;
    padding: 1.35rem 1.45rem;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.03);
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
    position: relative;
    margin-bottom: 1.15rem;
}

.addon-card:focus-within,
.addon-row-item:focus-within {
    border-color: rgba(13, 148, 136, 0.55);
    box-shadow: 0 6px 20px rgba(13, 148, 136, 0.09);
}

.addon-card-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding-bottom: 0.85rem;
    margin-bottom: 1rem;
    border-bottom: 1px solid rgba(var(--muted), 0.12);
    gap: 0.75rem;
}

.addon-card-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    font-size: 0.82rem;
    font-weight: 800;
    color: #0d9488;
    background: rgba(13, 148, 136, 0.1);
    padding: 0.3rem 0.75rem;
    border-radius: 999px;
    border: 1px solid rgba(13, 148, 136, 0.2);
}

.addon-card-remove-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.4rem;
    padding: 0.45rem 0.95rem;
    border-radius: 10px;
    background: rgba(239, 68, 68, 0.08);
    color: #ef4444;
    border: 1px solid rgba(239, 68, 68, 0.2);
    font-size: 0.82rem;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.2s ease;
}

.addon-card-remove-btn:hover:not(:disabled) {
    background: #ef4444;
    color: #ffffff !important;
    border-color: #ef4444;
    transform: translateY(-1px);
}

.addon-primary-grid {
    display: grid;
    grid-template-columns: 2fr 1.2fr 1fr;
    gap: 1rem;
    margin-bottom: 1rem;
}

/* Staffing Assistance Section */
.staff-roles-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
    gap: 1rem;
    margin-bottom: 1.25rem;
}

.staff-role-card {
    background: rgb(var(--card));
    border: 1.5px solid rgba(var(--muted), 0.18);
    border-radius: 14px;
    padding: 1.1rem 1.15rem;
    display: flex;
    flex-direction: column;
    gap: 0.65rem;
    transition: all 0.2s ease;
}

.staff-role-card:hover {
    border-color: rgba(var(--brand), 0.4);
    box-shadow: 0 4px 14px rgba(0, 0, 0, 0.03);
}

.staff-role-card:has(input[type="checkbox"]:checked) {
    border-color: rgba(var(--brand), 0.55);
    background: rgba(var(--brand), 0.025);
}

.staff-role-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 0.5rem;
}

.staff-role-qty-wrap {
    display: flex;
    align-items: center;
    gap: 0.4rem;
}

.staff-qty-input {
    width: 58px;
    height: 38px;
    border-radius: 8px;
    border: 1px solid rgba(var(--muted), 0.25);
    background: rgba(var(--bg), 0.9);
    color: rgb(var(--text));
    text-align: center;
    font-weight: 700;
    font-size: 0.95rem;
    font-family: inherit;
    box-sizing: border-box;
}

/* ═══════════════════════════════════════════════════════════
   Modern Interactive Settings Tiles (Checkboxes)
   ═══════════════════════════════════════════════════════════ */
.setting-tile,
.setting-card {
    background: rgb(var(--card));
    border: 1.5px solid rgba(var(--muted), 0.18);
    border-radius: 16px;
    padding: 1.25rem 1.35rem;
    margin-bottom: 1.15rem;
    transition: all 0.2s ease;
    position: relative;
}

.setting-tile:hover,
.setting-card:hover {
    border-color: rgba(var(--brand), 0.4);
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.04);
}

.setting-tile:has(input[type="checkbox"]:checked),
.setting-card:has(input[type="checkbox"]:checked) {
    border-color: rgba(var(--brand), 0.55);
    background: rgba(var(--brand), 0.025);
}

.setting-tile--flyer,
.setting-card--flyer {
    background: rgba(99, 102, 241, 0.03);
    border-color: rgba(99, 102, 241, 0.25);
}

.setting-tile--flyer:hover,
.setting-card--flyer:hover {
    border-color: rgba(99, 102, 241, 0.5);
}

.setting-tile--flyer:has(input[type="checkbox"]:checked),
.setting-card--flyer:has(input[type="checkbox"]:checked) {
    border-color: #6366f1;
    background: rgba(99, 102, 241, 0.06);
}

.setting-tile-label,
.setting-card-label {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
    cursor: pointer;
    user-select: none;
    margin: 0;
    width: 100%;
}

.setting-tile-checkbox-wrap {
    flex-shrink: 0;
    margin-top: 0.2rem;
}

.setting-tile-checkbox,
.setting-checkbox {
    width: 22px;
    height: 22px;
    border-radius: 6px;
    accent-color: rgb(var(--brand));
    cursor: pointer;
    flex-shrink: 0;
}

.setting-tile-content {
    flex: 1;
    min-width: 0;
}

.setting-tile-title {
    font-size: 0.98rem;
    font-weight: 800;
    color: rgb(var(--text));
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 0.45rem;
    line-height: 1.35;
}

.setting-tile-desc {
    font-size: 0.83rem;
    color: rgb(var(--muted));
    line-height: 1.48;
    margin-top: 0.35rem;
}

.setting-badge-recommended {
    font-size: 0.7rem;
    font-weight: 800;
    color: #10b981;
    background: rgba(16, 185, 129, 0.12);
    border: 1px solid rgba(16, 185, 129, 0.25);
    padding: 0.2rem 0.55rem;
    border-radius: 99px;
    text-transform: uppercase;
    letter-spacing: 0.03em;
}

.setting-badge-promo {
    font-size: 0.7rem;
    font-weight: 800;
    color: #6366f1;
    background: rgba(99, 102, 241, 0.12);
    border: 1px solid rgba(99, 102, 241, 0.25);
    padding: 0.2rem 0.55rem;
    border-radius: 99px;
    text-transform: uppercase;
    letter-spacing: 0.03em;
}

.setting-badge-flexible {
    font-size: 0.7rem;
    font-weight: 800;
    color: #f59e0b;
    background: rgba(245, 158, 11, 0.12);
    border: 1px solid rgba(245, 158, 11, 0.25);
    padding: 0.2rem 0.55rem;
    border-radius: 99px;
    text-transform: uppercase;
    letter-spacing: 0.03em;
}

/* Primary Action Buttons */
.form-submit-row {
    display: flex;
    gap: 1rem;
    align-items: center;
    margin-top: 2rem;
}

.form-publish-btn {
    flex: 1;
    min-height: 52px;
    background: rgb(var(--brand));
    color: #ffffff !important;
    font-size: 1.05rem;
    font-weight: 800;
    border-radius: 14px;
    border: none;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    box-shadow: 0 4px 18px rgba(var(--brand), 0.35);
    transition: all 0.2s ease;
    font-family: inherit;
}

.form-publish-btn:hover {
    filter: brightness(1.08);
    transform: translateY(-1px);
    box-shadow: 0 6px 22px rgba(var(--brand), 0.42);
}

.form-secondary-btn {
    min-height: 40px;
    padding: 0.45rem 1rem;
    background: rgba(var(--brand), 0.08);
    color: rgb(var(--brand));
    border: 1px solid rgba(var(--brand), 0.25);
    border-radius: 10px;
    font-size: 0.88rem;
    font-weight: 700;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    transition: all 0.2s ease;
    font-family: inherit;
}

.form-secondary-btn:hover {
    background: rgba(var(--brand), 0.15);
    transform: translateY(-1px);
}

/* ══ Tablet Breakpoint (601px - 960px) ══ */
@media (max-width: 960px) {
    .form-card {
        padding: 1.75rem;
        border-radius: 20px;
    }

    .event-form-section {
        padding: 1.25rem;
    }

    .event-grid-3 {
        grid-template-columns: 1fr 1fr;
    }

    .event-grid-3 .event-field-group:first-child {
        grid-column: 1 / -1;
    }

    .staff-roles-grid {
        grid-template-columns: 1fr 1fr;
    }

    /* Tablet ticket primary grid (Name on top, Price & Capacity below) */
    .ticket-primary-grid {
        grid-template-columns: 1fr 1fr !important;
        gap: 0.85rem !important;
    }

    .ticket-primary-grid .event-field-group:first-child {
        grid-column: 1 / -1 !important;
    }

    .ticket-access-shelf {
        grid-template-columns: 1fr 1fr !important;
        gap: 1rem !important;
    }

    .guest-card-grid {
        grid-template-columns: 1fr 1fr !important;
        gap: 0.85rem !important;
    }
}

/* ══ Mobile Breakpoint (<= 768px & Full-Width Display) ══ */
@media (max-width: 768px) {
    .create-event-wrapper {
        max-width: 100% !important;
        width: 100% !important;
        margin-left: 0 !important;
        margin-right: 0 !important;
        padding: 0 !important;
    }

    .create-event-hero {
        padding: 0 0.35rem;
        margin-bottom: 1.25rem;
    }

    .form-card {
        padding: 1.25rem 0.75rem !important;
        border-radius: 16px !important;
        width: 100% !important;
        box-sizing: border-box !important;
    }

    .event-form-section {
        padding: 1.15rem 0.65rem !important;
        border-radius: 12px !important;
        margin-bottom: 1.25rem !important;
        width: 100% !important;
        box-sizing: border-box !important;
    }

    .event-section-header {
        margin-bottom: 1rem;
    }

    .event-section-title {
        font-size: 1.05rem;
    }

    .event-section-subtitle {
        font-size: 0.78rem;
    }

    .event-grid-2,
    .event-grid-3 {
        grid-template-columns: 1fr !important;
        gap: 0;
    }

    .event-input,
    .event-select,
    .event-textarea,
    .ticket-primary-grid input,
    .ticket-access-shelf input,
    .guest-card-grid input {
        font-size: 16px !important; /* Prevents auto-zoom on iOS */
        min-height: 48px;
    }

    /* URL Slug wrapper stacks gracefully on small phones */
    .url-slug-wrapper {
        flex-direction: column !important;
        align-items: stretch !important;
    }

    .url-prefix-badge {
        border-right: none !important;
        border-bottom: 1px solid rgba(var(--muted), 0.15) !important;
        font-size: 0.82rem !important;
        padding: 0.55rem 0.85rem !important;
    }

    /* Guest cards stack cleanly on mobile */
    .guest-card-grid {
        grid-template-columns: 1fr !important;
        gap: 0.75rem;
    }

    .guest-card-header {
        align-items: center;
    }

    .guest-remove,
    .guest-card-remove-btn {
        min-height: 40px;
        padding: 0.45rem 0.75rem;
        font-size: 0.82rem;
    }

    /* Ticket cards stack vertically on mobile */
    .ticket-card {
        padding: 1.15rem 0.95rem !important;
        border-radius: 16px !important;
    }

    .ticket-primary-grid {
        display: flex !important;
        flex-direction: column !important;
        gap: 0.85rem !important;
    }

    .ticket-access-shelf {
        display: flex !important;
        flex-direction: column !important;
        gap: 0.95rem !important;
        padding: 0.95rem !important;
    }

    .ticket-card-remove-btn,
    .ticket-remove-btn,
    .addon-card-remove-btn {
        min-height: 40px !important;
        padding: 0.45rem 0.75rem !important;
        font-size: 0.82rem !important;
    }

    .addon-card {
        padding: 1.15rem 0.95rem !important;
        border-radius: 16px !important;
    }

    .addon-primary-grid {
        display: flex !important;
        flex-direction: column !important;
        gap: 0.85rem !important;
    }

    /* Setting tiles on mobile */
    .setting-tile,
    .setting-card {
        padding: 1.1rem 0.95rem !important;
        border-radius: 14px !important;
    }

    .setting-tile-label {
        gap: 0.75rem !important;
    }

    .setting-tile-checkbox,
    .setting-checkbox {
        width: 20px !important;
        height: 20px !important;
    }

    .setting-tile-title {
        font-size: 0.92rem !important;
    }

    /* Staff role cards stack */
    .staff-roles-grid {
        grid-template-columns: 1fr !important;
    }

    .form-submit-row {
        flex-direction: column;
        gap: 0.75rem;
    }

    .form-publish-btn {
        width: 100%;
        min-height: 52px;
    }
}

/* ══ Small Screen Phones (<= 480px Full-Edge Optimization) ══ */
@media (max-width: 480px) {
    .create-event-hero {
        padding: 0 0.15rem;
        margin-bottom: 1rem;
    }

    .form-card {
        padding: 0.95rem 0.45rem !important;
        border-radius: 12px !important;
    }

    .event-form-section {
        padding: 0.85rem 0.45rem !important;
        border-radius: 10px !important;
        margin-bottom: 0.95rem !important;
    }

    .ticket-card,
    .addon-card,
    .guest-card {
        padding: 0.85rem 0.45rem !important;
        border-radius: 10px !important;
    }

    .ticket-access-shelf {
        padding: 0.65rem 0.45rem !important;
        border-radius: 8px !important;
    }

    .setting-tile,
    .setting-card {
        padding: 0.85rem 0.5rem !important;
        border-radius: 10px !important;
    }

    .event-section-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.45rem;
    }

    .event-input,
    .event-select,
    .event-textarea {
        padding: 0.65rem 0.65rem !important;
    }
}

/* --- Vintage Theme Enhancements for Create Event --- */
.create-event-hero h1 {
    font-family: var(--vn-font-serif) !important;
    color: var(--vn-ink) !important;
}
.form-card, .tier-row, .wizard-step-card {
    background: var(--vn-parchment-card) !important;
    border: 1.5px solid var(--vn-border) !important;
    box-shadow: var(--vn-shadow-elevated) !important;
}
.form-card h2, .form-card h3 {
    font-family: var(--vn-font-serif) !important;
    color: var(--vn-ink) !important;
}
.form-publish-btn {
    background: var(--vn-brass) !important;
    box-shadow: 0 4px 14px var(--vn-brass-glow) !important;
}
</style>

<div class="create-event-wrapper">
    <!-- Page Hero -->
    <header class="create-event-hero">
        <span class="panel-badge">✨ Host Dashboard</span>
        <h1>Create Your Event &amp; Start Selling</h1>
        <p class="lede">
            Configure your event essentials — title, venue, date, and description. Tickets, seating, and guest lineup are set up on the next page.
        </p>
    </header>

    <div class="form-card">
        <!-- Error / Feedback Messages -->
        <div class="auth-message" aria-live="polite" style="display: none;"></div>

        <?php if (isset($model) && $model->hasErrors()): ?>
            <div class="alert alert-danger" style="margin-bottom: 1.5rem; border-radius: 14px; padding: 1.15rem 1.35rem; background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.28); color: #ef4444;">
                <h4 style="margin: 0 0 0.5rem; font-size: 0.95rem; font-weight: 800; display: flex; align-items: center; gap: 0.4rem;">
                    <span>⚠️</span> Please correct the following errors:
                </h4>
                <ul style="margin: 0; padding-left: 1.25rem; font-size: 0.88rem; line-height: 1.6;">
                    <?php foreach ($model->getErrorMessages() as $errorMessage): ?>
                        <li><?= e($errorMessage) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <!-- Flow Stepper Banner -->
        <div class="studio-stepper-card" style="background:rgba(var(--card),0.9); border:1px solid rgba(var(--muted),0.2); border-radius:16px; padding:1.15rem 1.4rem; margin-bottom:1.75rem; box-shadow:0 4px 15px rgba(0,0,0,0.03);">
            <div style="display:flex; align-items:center; justify-content:space-between; gap:1rem; flex-wrap:wrap;">
                <div style="display:flex; align-items:center; gap:0.75rem;">
                    <div style="width:36px; height:36px; border-radius:50%; background:rgb(var(--brand)); color:#fff; display:flex; align-items:center; justify-content:center; font-weight:800; font-size:0.9rem; box-shadow:0 0 0 4px rgba(var(--brand),0.2);">1</div>
                    <div>
                        <span style="font-size:0.7rem; font-weight:800; text-transform:uppercase; letter-spacing:0.06em; color:rgb(var(--brand));">Step 1 of 2 (Current)</span>
                        <strong style="display:block; font-size:0.95rem; color:rgb(var(--text));">Event Essentials &amp; Venue</strong>
                    </div>
                </div>
                <div style="display:flex; align-items:center; gap:0.75rem; opacity:0.7;">
                    <div style="width:36px; height:36px; border-radius:50%; background:rgba(var(--muted),0.2); color:rgb(var(--muted)); display:flex; align-items:center; justify-content:center; font-weight:800; font-size:0.9rem;">2</div>
                    <div>
                        <span style="font-size:0.7rem; font-weight:800; text-transform:uppercase; letter-spacing:0.06em; color:rgb(var(--muted));">Step 2 (Next Page)</span>
                        <strong style="display:block; font-size:0.95rem; color:rgb(var(--muted));">Tickets, Seating Studio &amp; Lineup</strong>
                    </div>
                </div>
            </div>
        </div>

        <form id="create-event-form" method="POST" action="{{ route('host/event/create') }}" enctype="multipart/form-data" novalidate>
            <?= csrf_field() ?>

            <!-- Section 1: Basic Event Details -->
            <div class="event-form-section">
                <div class="event-section-header">
                    <div class="event-section-title-wrap">
                        <span class="event-section-icon">🎉</span>
                        <div>
                            <h3 class="event-section-title">General Event Information</h3>
                            <p class="event-section-subtitle">Name, custom web address, and promotional cover imagery.</p>
                        </div>
                    </div>
                </div>

                <div class="event-field-group">
                    <label class="event-label" for="event-name">
                        Event Title / Name <span class="required-star">*</span>
                    </label>
                    <input type="text" id="event-name" name="event_name" class="event-input" value="<?= e(old('event_name', $model->name ?? '')) ?>" required placeholder="e.g. Summer Beats Music Festival 2026">
                </div>

                <div class="event-field-group">
                    <label class="event-label" for="event-image">
                        Event Cover Banner <span class="optional-tag">(Optional)</span>
                    </label>
                    <div class="image-upload-card">
                        <input type="file" id="event-image" name="event_image" accept="image/jpeg,image/png,image/webp,image/gif" style="width:100%; font-size:0.9rem;">
                        <div id="image-preview-container" class="hidden preview-container">
                            <img id="image-preview" src="" alt="Event Image Preview">
                            <button type="button" id="remove-image-btn" class="preview-close-btn" title="Remove image">✕</button>
                        </div>
                        <small class="event-help-text" style="display:block; margin-top:0.5rem;">
                            Upload a high-resolution banner image. Formats: JPG, PNG, WEBP, GIF (Max size: 5MB).
                        </small>
                    </div>
                </div>

                <div class="event-field-group">
                    <label class="event-label" for="custom-url">
                        Custom Event URL <span class="optional-tag">(Optional)</span>
                    </label>
                    <div class="url-slug-wrapper">
                        <span class="url-prefix-badge">/events/</span>
                        <input type="text" id="custom-url" name="custom_url" class="url-slug-input" value="<?= e(old('custom_url', $model->custom_url ?? '')) ?>" placeholder="summer-beats-festival" aria-label="Custom URL slug">
                    </div>
                    <small class="event-help-text">
                        Auto-generated from your event title. Attendees can access your page directly with this link.
                    </small>
                </div>
            </div>

            <!-- Section 2: Date, Time & Location -->
            <div class="event-form-section">
                <div class="event-section-header">
                    <div class="event-section-title-wrap">
                        <span class="event-section-icon">📅</span>
                        <div>
                            <h3 class="event-section-title">Schedule &amp; Location</h3>
                            <p class="event-section-subtitle">When and where will this experience take place?</p>
                        </div>
                    </div>
                </div>

                <div class="event-grid-2">
                    <div class="event-field-group">
                        <label class="event-label" for="event-date">
                            Event Date <span class="required-star">*</span>
                        </label>
                        <div class="picker-wrapper">
                            <svg class="picker-icon" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
                                <line x1="16" y1="2" x2="16" y2="6" />
                                <line x1="8" y1="2" x2="8" y2="6" />
                                <line x1="3" y1="10" x2="21" y2="10" />
                            </svg>
                            <input type="text" id="event-date" name="event_date" class="event-input" value="<?= e(old('event_date', '')) ?>" required placeholder="Select a date" readonly>
                        </div>
                    </div>

                    <div class="event-field-group">
                        <label class="event-label" for="event-time">
                            Start Time <span class="required-star">*</span>
                        </label>
                        <div class="picker-wrapper">
                            <svg class="picker-icon" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10" />
                                <polyline points="12 6 12 12 16 14" />
                            </svg>
                            <input type="text" id="event-time" name="event_time" class="event-input" value="<?= e(old('event_time', '')) ?>" required placeholder="Select a time" readonly>
                        </div>
                    </div>
                </div>

                <!-- Recurrence Settings -->
                <div class="setting-tile" style="margin-bottom: 1.5rem; border: 1.5px dashed rgba(var(--brand), 0.35); background: rgba(var(--brand), 0.03);">
                    <label class="setting-tile-label" for="is-recurring-toggle">
                        <div class="setting-tile-checkbox-wrap">
                            <input type="checkbox" id="is-recurring-toggle" name="is_recurring" value="1" <?= !empty(old('is_recurring')) ? 'checked' : '' ?> class="setting-tile-checkbox">
                        </div>
                        <div class="setting-tile-content">
                            <div class="setting-tile-title">
                                <span>🔄</span> Repeat Event (Recurring Series)
                            </div>
                            <div class="setting-tile-desc">
                                Automatically generate linked occurrences with independent ticket pools for daily, weekly, bi-weekly, or monthly dates.
                            </div>
                        </div>
                    </label>

                    <div id="recurrence-options-panel" style="display: <?= !empty(old('is_recurring')) ? 'block' : 'none' ?>; margin-top: 1.15rem; padding-top: 1.15rem; border-top: 1px solid rgba(var(--brand), 0.15);">
                        <div class="event-grid-3" style="margin-bottom: 1rem;">
                            <div class="event-field-group">
                                <label class="event-label" for="recurrence-type">Frequency</label>
                                <select id="recurrence-type" name="recurrence_type" class="event-select">
                                    <option value="weekly" <?= old('recurrence_type') === 'weekly' ? 'selected' : '' ?>>Weekly</option>
                                    <option value="biweekly" <?= old('recurrence_type') === 'biweekly' ? 'selected' : '' ?>>Every 2 Weeks (Bi-weekly)</option>
                                    <option value="monthly" <?= old('recurrence_type') === 'monthly' ? 'selected' : '' ?>>Monthly</option>
                                    <option value="daily" <?= old('recurrence_type') === 'daily' ? 'selected' : '' ?>>Daily</option>
                                </select>
                            </div>

                            <div class="event-field-group">
                                <label class="event-label" for="recurrence-interval">Repeat Every</label>
                                <div style="display: flex; align-items: center; gap: 0.5rem;">
                                    <input type="number" id="recurrence-interval" name="recurrence_interval" class="event-input" min="1" max="52" value="<?= e(old('recurrence_interval', '1')) ?>" style="width: 80px;">
                                    <span id="recurrence-interval-unit" style="font-size: 0.85rem; color: rgb(var(--muted));">week(s)</span>
                                </div>
                            </div>

                            <div class="event-field-group">
                                <label class="event-label" for="recurrence-end-type">End Condition</label>
                                <select id="recurrence-end-type" name="recurrence_end_type" class="event-select">
                                    <option value="count" <?= old('recurrence_end_type') !== 'date' ? 'selected' : '' ?>>After set occurrences</option>
                                    <option value="date" <?= old('recurrence_end_type') === 'date' ? 'selected' : '' ?>>On a specific date</option>
                                </select>
                            </div>
                        </div>

                        <div class="event-grid-2" style="align-items: flex-end; margin-bottom: 1rem;">
                            <div class="event-field-group" id="group-recurrence-count">
                                <label class="event-label" for="recurrence-count">Total Occurrences (2 to 52)</label>
                                <input type="number" id="recurrence-count" name="recurrence_count" class="event-input" min="2" max="52" value="<?= e(old('recurrence_count', '4')) ?>" placeholder="e.g. 4">
                            </div>

                            <div class="event-field-group" id="group-recurrence-end-date" style="display: <?= old('recurrence_end_type') === 'date' ? 'block' : 'none' ?>;">
                                <label class="event-label" for="recurrence-end-date">Repeat Until Date</label>
                                <div class="picker-wrapper">
                                    <svg class="picker-icon" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
                                        <line x1="16" y1="2" x2="16" y2="6" />
                                        <line x1="8" y1="2" x2="8" y2="6" />
                                        <line x1="3" y1="10" x2="21" y2="10" />
                                    </svg>
                                    <input type="text" id="recurrence-end-date" name="recurrence_end_date" class="event-input" value="<?= e(old('recurrence_end_date', '')) ?>" placeholder="Select end date" readonly>
                                </div>
                            </div>
                        </div>

                        <div id="recurrence-preview-box" style="padding: 0.75rem 1rem; background: rgba(var(--brand), 0.08); border-radius: 10px; font-size: 0.82rem; color: rgb(var(--text)); display: flex; align-items: center; gap: 0.6rem;">
                            <span style="font-size: 1.1rem;">🗓️</span>
                            <span id="recurrence-preview-text">Configure schedule above to preview recurring occurrences.</span>
                        </div>
                    </div>
                </div>

                <div class="event-grid-3">
                    <div class="event-field-group">
                        <label class="event-label" for="event-category">
                            Category <span class="required-star">*</span>
                        </label>
                        <select id="event-category" name="event_category" class="event-select" required>
                            <option value="" disabled <?= empty(old('event_category', $model->category ?? '')) ? 'selected' : '' ?>>Select category</option>
                            <?php
                            $categoryList = !empty($categories) ? $categories : [
                                'Concerts', 'Tech', 'Comedy', 'Sports', 'Theater', 'Family', 'Art & Wine',
                                'Parties & Festivals', 'Workshops & Classes', 'Networking', 'Business & Finance',
                                'Conferences & Summits', 'Food & Drink', 'Health & Wellness', 'Fashion & Beauty',
                                'Film & Media', 'Gaming & Esports', 'Education & Academia', 'Charity & Fundraising',
                                'Religion & Spirituality', 'Travel & Outdoor', 'Nightlife & Clubbing',
                                'Cultural & Heritage', 'Exhibitions & Trade Shows', 'Other'
                            ];
                            foreach ($categoryList as $cat): ?>
                                <option value="<?= e($cat) ?>" <?= old('event_category', $model->category ?? '') === $cat ? 'selected' : '' ?>><?= e($cat) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="event-field-group">
                        <label class="event-label" for="event-venue">
                            Venue Address <span class="required-star">*</span>
                        </label>
                        <input type="text" id="event-venue" name="event_venue" class="event-input" value="<?= e(old('event_venue', '')) ?>" required placeholder="e.g. Grand Exhibition Hall">
                    </div>

                    <div class="event-field-group">
                        <label class="event-label" for="event-city">
                            City <span class="required-star">*</span>
                        </label>
                        <input type="text" id="event-city" name="event_city" class="event-input" value="<?= e(old('event_city', '')) ?>" required placeholder="e.g. New York">
                    </div>
                </div>
            </div>

            <!-- Section 3: Contact Details -->
            <div class="event-form-section">
                <div class="event-section-header">
                    <div class="event-section-title-wrap">
                        <span class="event-section-icon">📞</span>
                        <div>
                            <h3 class="event-section-title">Organizer Contacts</h3>
                            <p class="event-section-subtitle">How attendees and staff can reach you for inquiries.</p>
                        </div>
                    </div>
                </div>

                <div class="event-grid-2">
                    <div class="event-field-group">
                        <label class="event-label" for="organizer-phone">
                            Phone Number <span class="optional-tag">(Optional)</span>
                        </label>
                        <input type="tel" id="organizer-phone" name="organizer_phone" class="event-input" value="<?= e(old('organizer_phone', '')) ?>" placeholder="+1 555 555 5555">
                    </div>

                    <div class="event-field-group">
                        <label class="event-label" for="organizer-social">
                            Website or Social Link <span class="optional-tag">(Optional)</span>
                        </label>
                        <input type="url" id="organizer-social" name="organizer_social" class="event-input" value="<?= e(old('organizer_social', '')) ?>" placeholder="https://... or @instagram">
                    </div>
                </div>
            </div>

            <!-- Section 4: Description -->
            <div class="event-form-section">
                <div class="event-section-header">
                    <div class="event-section-title-wrap">
                        <span class="event-section-icon">📝</span>
                        <div>
                            <h3 class="event-section-title">Event Story &amp; Details</h3>
                            <p class="event-section-subtitle">Share what makes this experience memorable and unique.</p>
                        </div>
                    </div>
                </div>

                <div class="event-field-group">
                    <label class="event-label" for="event-description">
                        Description &amp; Highlights <span class="required-star">*</span>
                    </label>
                    <textarea id="event-description" name="event_description" class="event-textarea" rows="5" required placeholder="Describe the program schedule, age restrictions, dress code, and what attendees should expect."><?= e(old('event_description', $model->description ?? '')) ?></textarea>
                </div>
            </div>

            <!-- Step 2 Preview: Tickets, Seating & Lineup (configured after event creation) -->
            <div class="event-form-section" style="background: linear-gradient(135deg, rgba(99,102,241,0.06), rgba(16,185,129,0.04)); border: 1.5px dashed rgba(99,102,241,0.3); text-align: center; padding: 2.5rem 1.5rem;">
                <div style="display:flex; flex-direction:column; align-items:center; gap:1rem;">
                    <div style="display:flex; align-items:center; gap:0.6rem; flex-wrap:wrap; justify-content:center;">
                        <span style="font-size:2rem;">🎟️</span>
                        <span style="font-size:2rem;">🌟</span>
                        <span style="font-size:2rem;">🛍️</span>
                    </div>
                    <div>
                        <h3 style="margin:0 0 0.4rem; font-size:1.1rem; font-weight:800; color:rgb(var(--text));">Tickets, Seating & Guest Lineup</h3>
                        <p style="margin:0; font-size:0.88rem; color:rgb(var(--muted)); line-height:1.55; max-width:520px;">
                            After saving your event essentials, you'll be taken to the <strong style="color:#6366f1;">Tickets & Seating Studio</strong> where you can configure ticket tiers (Standard, Duo/Couples, Group, Seated), seating arrangements, special guests, and add-on merchandise.
                        </p>
                    </div>
                    <div style="display:flex; align-items:center; gap:0.5rem; margin-top:0.5rem; padding:0.6rem 1.2rem; background:rgba(99,102,241,0.1); border:1px solid rgba(99,102,241,0.2); border-radius:10px;">
                        <span style="font-size:0.85rem; font-weight:700; color:#6366f1;">📋 Step 2 of 2 — Configured on the next page</span>
                    </div>
                </div>
            </div>

            <!-- Section 8: Organization Staffing Assistance Request -->
            <div class="event-form-section staffing-request-section">
                <div class="event-section-header">
                    <div class="event-section-title-wrap">
                        <span class="event-section-icon">🤝</span>
                        <div>
                            <h3 class="event-section-title">Organization Staffing Support</h3>
                            <p class="event-section-subtitle">
                                Need on-site assistance? Request trained gate scanners, ushers, support leads, or box office staff.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="setting-tile" style="margin-bottom: 1.25rem;">
                    <label class="setting-tile-label" for="request-staffing-toggle">
                        <div class="setting-tile-checkbox-wrap">
                            <input type="checkbox" id="request-staffing-toggle" name="request_staffing" value="1" <?= old('request_staffing') ? 'checked' : '' ?> onchange="toggleStaffingOptions(this.checked)" class="setting-tile-checkbox">
                        </div>
                        <div class="setting-tile-content">
                            <div class="setting-tile-title">
                                <span>🤝</span> Request On-Site Event Staffing Assistance
                                <span class="setting-badge-promo">Staff On-Demand</span>
                            </div>
                            <div class="setting-tile-desc">
                                Request vetted personnel to manage ticket scanning, ushering, customer support, or gate cashiers.
                            </div>
                        </div>
                    </label>
                </div>

                <div id="staffing-options-container" style="display: <?= old('request_staffing') ? 'block' : 'none' ?>; margin-top: 1rem; border-top: 1px dashed rgba(var(--muted), 0.2); padding-top: 1.25rem;">
                    <p style="margin:0 0 1rem 0; font-size:0.88rem; font-weight:700; color:rgb(var(--text));">
                        Select the specialized personnel roles and quantities needed:
                    </p>

                    <div class="staff-roles-grid">
                        <!-- Role 1: Ticket Scanner -->
                        <div class="staff-role-card">
                            <div class="staff-role-header">
                                <label style="display:flex; align-items:center; gap:0.5rem; font-weight:700; font-size:0.92rem; cursor:pointer;">
                                    <input type="checkbox" name="staff_roles[ticket-scanner][selected]" value="1" <?= old('staff_roles.ticket-scanner.selected') ? 'checked' : '' ?> onchange="updateStaffSummary()" class="setting-tile-checkbox">
                                    <span>🎫 Ticket Scanner</span>
                                </label>
                                <div class="staff-role-qty-wrap">
                                    <span style="font-size:0.75rem; color:rgb(var(--muted));">Qty:</span>
                                    <input type="number" name="staff_roles[ticket-scanner][qty]" class="staff-qty-input" value="<?= old('staff_roles.ticket-scanner.qty', '1') ?>" min="1" max="20" onchange="updateStaffSummary()">
                                </div>
                            </div>
                            <p style="margin:0; font-size:0.8rem; color:rgb(var(--muted)); line-height:1.4;">
                                Operates gate scanners, validates QR codes, and keeps attendee check-in queues moving rapidly.
                            </p>
                        </div>

                        <!-- Role 2: Organizer Staff / Usher -->
                        <div class="staff-role-card">
                            <div class="staff-role-header">
                                <label style="display:flex; align-items:center; gap:0.5rem; font-weight:700; font-size:0.92rem; cursor:pointer;">
                                    <input type="checkbox" name="staff_roles[organizer-staff][selected]" value="1" <?= old('staff_roles.organizer-staff.selected') ? 'checked' : '' ?> onchange="updateStaffSummary()" class="setting-tile-checkbox">
                                    <span>🏷️ Usher / Coordination</span>
                                </label>
                                <div class="staff-role-qty-wrap">
                                    <span style="font-size:0.75rem; color:rgb(var(--muted));">Qty:</span>
                                    <input type="number" name="staff_roles[organizer-staff][qty]" class="staff-qty-input" value="<?= old('staff_roles.organizer-staff.qty', '1') ?>" min="1" max="20" onchange="updateStaffSummary()">
                                </div>
                            </div>
                            <p style="margin:0; font-size:0.8rem; color:rgb(var(--muted)); line-height:1.4;">
                                Floor directions, VIP seating guidance, wristband verification, and crowd flow management.
                            </p>
                        </div>

                        <!-- Role 3: Customer Support -->
                        <div class="staff-role-card">
                            <div class="staff-role-header">
                                <label style="display:flex; align-items:center; gap:0.5rem; font-weight:700; font-size:0.92rem; cursor:pointer;">
                                    <input type="checkbox" name="staff_roles[customer-support][selected]" value="1" <?= old('staff_roles.customer-support.selected') ? 'checked' : '' ?> onchange="updateStaffSummary()" class="setting-tile-checkbox">
                                    <span>🎧 Support Lead</span>
                                </label>
                                <div class="staff-role-qty-wrap">
                                    <span style="font-size:0.75rem; color:rgb(var(--muted));">Qty:</span>
                                    <input type="number" name="staff_roles[customer-support][qty]" class="staff-qty-input" value="<?= old('staff_roles.customer-support.qty', '1') ?>" min="1" max="10" onchange="updateStaffSummary()">
                                </div>
                            </div>
                            <p style="margin:0; font-size:0.8rem; color:rgb(var(--muted)); line-height:1.4;">
                                Info desk operations, attendee assistance, email receipt lookup, and lost &amp; found management.
                            </p>
                        </div>

                        <!-- Role 4: Technical Operations -->
                        <div class="staff-role-card">
                            <div class="staff-role-header">
                                <label style="display:flex; align-items:center; gap:0.5rem; font-weight:700; font-size:0.92rem; cursor:pointer;">
                                    <input type="checkbox" name="staff_roles[technical-ops][selected]" value="1" <?= old('staff_roles.technical-ops.selected') ? 'checked' : '' ?> onchange="updateStaffSummary()" class="setting-tile-checkbox">
                                    <span>⚙️ Technical Ops</span>
                                </label>
                                <div class="staff-role-qty-wrap">
                                    <span style="font-size:0.75rem; color:rgb(var(--muted));">Qty:</span>
                                    <input type="number" name="staff_roles[technical-ops][qty]" class="staff-qty-input" value="<?= old('staff_roles.technical-ops.qty', '1') ?>" min="1" max="10" onchange="updateStaffSummary()">
                                </div>
                            </div>
                            <p style="margin:0; font-size:0.8rem; color:rgb(var(--muted)); line-height:1.4;">
                                Scanner hardware provisioning, entrance WiFi sync, power stations, and stage AV liaison.
                            </p>
                        </div>

                        <!-- Role 5: Finance & Box Office -->
                        <div class="staff-role-card">
                            <div class="staff-role-header">
                                <label style="display:flex; align-items:center; gap:0.5rem; font-weight:700; font-size:0.92rem; cursor:pointer;">
                                    <input type="checkbox" name="staff_roles[finance-manager][selected]" value="1" <?= old('staff_roles.finance-manager.selected') ? 'checked' : '' ?> onchange="updateStaffSummary()" class="setting-tile-checkbox">
                                    <span>💼 Box Office Cashier</span>
                                </label>
                                <div class="staff-role-qty-wrap">
                                    <span style="font-size:0.75rem; color:rgb(var(--muted));">Qty:</span>
                                    <input type="number" name="staff_roles[finance-manager][qty]" class="staff-qty-input" value="<?= old('staff_roles.finance-manager.qty', '1') ?>" min="1" max="5" onchange="updateStaffSummary()">
                                </div>
                            </div>
                            <p style="margin:0; font-size:0.8rem; color:rgb(var(--muted)); line-height:1.4;">
                                Door cash &amp; card ticket purchases, physical receipts, gate reconciliation, and refund handling.
                            </p>
                        </div>
                    </div>

                    <div class="event-field-group">
                        <label class="event-label" for="staff-notes">
                            Special Instructions / Requirements for Team Staff:
                        </label>
                        <textarea id="staff-notes" name="staff_notes" class="event-textarea" rows="2" placeholder="e.g. Report by 4:00 PM for gate briefing at Gate 2. Dress code: Black polo shirt."><?= e(old('staff_notes', '')) ?></textarea>
                    </div>

                    <div id="staffing-summary-box" style="background:rgba(var(--brand),0.06); border:1px solid rgba(var(--brand),0.2); border-radius:12px; padding:0.85rem 1.15rem; font-size:0.85rem;">
                        <div style="font-weight:700; color:rgb(var(--brand)); margin-bottom:0.25rem; display:flex; align-items:center; gap:0.4rem;">
                            <span>📋</span> Staffing Request Summary: <span id="staffing-total-count" style="background:rgb(var(--brand)); color:#fff; padding:1px 8px; border-radius:99px; font-size:0.75rem;">0 Staff</span>
                        </div>
                        <div id="staffing-roles-summary" style="color:rgb(var(--muted)); font-size:0.82rem;">
                            Select one or more roles above to generate your staffing assistance package.
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section 9: Ticketing & Marketing Settings -->
            <div class="event-form-section">
                <div class="event-section-header">
                    <div class="event-section-title-wrap">
                        <span class="event-section-icon">⚙️</span>
                        <div>
                            <h3 class="event-section-title">Ticketing &amp; Marketing Settings</h3>
                            <p class="event-section-subtitle">Configure fee pass-through, landing pages, and flexible payments.</p>
                        </div>
                    </div>
                </div>

                <!-- Fee Settings -->
                <div class="setting-tile">
                    <label class="setting-tile-label" for="pass-fee">
                        <div class="setting-tile-checkbox-wrap">
                            <input type="checkbox" id="pass-fee" name="pass_fee" value="1" <?= old('pass_fee', '1') ? 'checked' : '' ?> class="setting-tile-checkbox">
                        </div>
                        <div class="setting-tile-content">
                            <div class="setting-tile-title">
                                <span>💸</span> Pass <?= e(get_setting('platform_fee', 5)) ?>% Platform Fee to Attendees
                                <span class="setting-badge-recommended">Recommended</span>
                            </div>
                            <div class="setting-tile-desc">
                                <strong>Checked:</strong> Attendees pay the <?= e(get_setting('platform_fee', 5)) ?>% ticketing fee on checkout.<br>
                                <strong>Unchecked:</strong> You absorb the fee, which is deducted before payout so attendees see transparent round figures.
                            </div>
                        </div>
                    </label>
                </div>

                <!-- Dedicated Flyer Page -->
                <div class="setting-tile setting-tile--flyer">
                    <input type="hidden" name="flyer_page_enabled" value="0">
                    <label class="setting-tile-label" for="flyer-page-enabled">
                        <div class="setting-tile-checkbox-wrap">
                            <input type="checkbox" id="flyer-page-enabled" name="flyer_page_enabled" value="1" <?= !empty(old('flyer_page_enabled', '')) ? 'checked' : '' ?> class="setting-tile-checkbox">
                        </div>
                        <div class="setting-tile-content">
                            <div class="setting-tile-title" style="color: #6366f1;">
                                <span>🎨</span> Enable Dedicated Visual Flyer Landing Page (<code>/f/event-slug</code>)
                                <span class="setting-badge-promo">Social Ready</span>
                            </div>
                            <div class="setting-tile-desc">
                                Creates an immersive, artwork-centered ticket booth with a short link. Perfect for linking directly in Instagram bio, TikTok, and WhatsApp broadcasts.
                            </div>
                        </div>
                    </label>
                </div>

                <!-- Partial Payment (Layaway Plan) Setting -->
                <div class="setting-tile">
                    <input type="hidden" name="allow_partial_payment" value="0">
                    <label class="setting-tile-label" for="allow-partial-payment">
                        <div class="setting-tile-checkbox-wrap">
                            <input type="checkbox" id="allow-partial-payment" name="allow_partial_payment" value="1" <?= !empty(old('allow_partial_payment', '')) ? 'checked' : '' ?> class="setting-tile-checkbox" onchange="document.getElementById('partial-payment-options').style.display = this.checked ? 'block' : 'none'">
                        </div>
                        <div class="setting-tile-content">
                            <div class="setting-tile-title">
                                <span>💳</span> Allow Partial Payment (Layaway Plan)
                                <span class="setting-badge-flexible">Flexible Checkout</span>
                            </div>
                            <div class="setting-tile-desc">
                                Let attendees reserve tickets with an initial deposit and pay the remaining balance bit-by-bit before the event starts.
                            </div>
                        </div>
                    </label>
                    <div id="partial-payment-options" style="display:<?= !empty(old('allow_partial_payment', '')) ? 'block' : 'none' ?>; margin-top:1rem; padding-top:1rem; border-top:1px dashed rgba(var(--muted),0.2);">
                        <label class="event-label" style="font-size:0.85rem; font-weight:700;">Minimum Deposit Percentage</label>
                        <div style="display:flex; align-items:center; gap:0.6rem; margin-top:0.35rem;">
                            <input type="number" name="partial_payment_min_percent" class="event-input" value="<?= e(old('partial_payment_min_percent', '25')) ?>" min="10" max="90" step="5" style="width:110px;">
                            <span style="font-size:0.85rem; color:rgb(var(--muted)); font-weight:600;">% of ticket total (default 25%)</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submit Row -->
            <div class="form-submit-row" style="display:flex; flex-direction:column; align-items:center; gap:0.6rem;">
                <button type="submit" class="form-publish-btn" style="width:100%; max-width:480px; justify-content:center;">
                    <span>Next: Set Up Tickets &amp; Seating</span>
                    <span>➔</span>
                </button>
                <span style="font-size:0.82rem; color:rgb(var(--muted)); font-weight:600;">Proceed to Step 2 to configure single, couple/duo passes, or hall/stadium seating rows</span>
            </div>
        </form>
    </div>
</div>

<script>
function toggleStaffingOptions(checked) {
    var container = document.getElementById('staffing-options-container');
    if (container) {
        container.style.display = checked ? 'block' : 'none';
    }
    if (checked) {
        updateStaffSummary();
    }
}

function updateStaffSummary() {
    var cards = document.querySelectorAll('.staff-role-card');
    var totalStaff = 0;
    var selectedRoles = [];

    cards.forEach(function(card) {
        var checkbox = card.querySelector('input[type="checkbox"]');
        var qtyInput = card.querySelector('input[type="number"]');
        if (checkbox && checkbox.checked) {
            var qty = parseInt(qtyInput ? qtyInput.value : 1, 10) || 1;
            totalStaff += qty;
            var roleTitle = card.querySelector('label span') ? card.querySelector('label span').textContent.trim() : 'Role';
            selectedRoles.push('<strong>' + qty + 'x</strong> ' + roleTitle);
        }
    });

    var countBadge = document.getElementById('staffing-total-count');
    var summaryEl = document.getElementById('staffing-roles-summary');

    if (countBadge) {
        countBadge.textContent = totalStaff + ' Staff Member' + (totalStaff !== 1 ? 's' : '');
    }

    if (summaryEl) {
        if (selectedRoles.length === 0) {
            summaryEl.innerHTML = 'Select one or more roles above to generate your staffing assistance package.';
        } else {
            summaryEl.innerHTML = 'Requesting: ' + selectedRoles.join(', ') + '. Admin coordination will review and deploy qualified team members.';
        }
    }
}

document.addEventListener('DOMContentLoaded', function() {
    var toggle = document.getElementById('request-staffing-toggle');
    if (toggle && toggle.checked) {
        updateStaffSummary();
    }
});
</script>
@endsection