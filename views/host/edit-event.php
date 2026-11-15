<?php

/**
 * Host Edit Event View
 *
 * Provides a responsive, multi-section event management editing interface.
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
Edit Event - <?= e($event->name) ?> - <?= e($siteName) ?> Host
@endsection

@section('content')
<style>
/* ═══════════════════════════════════════════════════════════
   Host Edit Event — Responsive Form Polish & System Tokens
   ═══════════════════════════════════════════════════════════ */

.edit-event-wrapper {
    max-width: 900px;
    margin: 0 auto 3rem;
    width: 100%;
}

/* Page Top Nav & Admin Banner */
.edit-top-nav {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 0.75rem;
    margin-bottom: 1.25rem;
}

.edit-back-link {
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    font-weight: 700;
    font-size: 0.88rem;
    color: rgb(var(--text));
    text-decoration: none;
    transition: color 0.2s ease;
}

.edit-back-link:hover {
    color: rgb(var(--brand));
}

.admin-edit-notice {
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    background: rgba(99, 102, 241, 0.08);
    border: 1px solid rgba(99, 102, 241, 0.24);
    border-radius: 12px;
    padding: 0.4rem 0.85rem;
    font-size: 0.82rem;
    color: rgb(var(--text));
    flex-wrap: wrap;
}

.admin-notice-title {
    font-weight: 800;
    color: #6366f1;
}

.admin-notice-dot {
    opacity: 0.4;
}

/* Page Hero */
.edit-event-hero {
    margin-bottom: 1.75rem;
}

.edit-event-hero .panel-badge {
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

.edit-event-hero h1 {
    font-size: clamp(1.65rem, 3.2vw, 2.35rem);
    font-weight: 900;
    color: rgb(var(--text));
    margin: 0 0 0.45rem;
    letter-spacing: -0.025em;
    line-height: 1.2;
}

.edit-event-hero .lede {
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

/* Image Upload & Existing Thumbnail */
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

.current-cover-bar {
    display: flex;
    align-items: center;
    gap: 1rem;
    background: rgb(var(--card));
    border: 1px solid rgba(var(--muted), 0.16);
    border-radius: 14px;
    padding: 0.75rem 1rem;
    margin-bottom: 0.85rem;
}

.current-cover-thumb {
    width: 100px;
    height: 60px;
    object-fit: cover;
    border-radius: 8px;
    border: 1px solid rgba(var(--muted), 0.2);
    flex-shrink: 0;
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

.form-cancel-btn {
    min-height: 52px;
    padding: 0 1.75rem;
    background: rgba(var(--muted), 0.08);
    color: rgb(var(--text));
    border: 1px solid rgba(var(--muted), 0.2);
    border-radius: 14px;
    font-size: 0.95rem;
    font-weight: 700;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;
    font-family: inherit;
}

.form-cancel-btn:hover {
    background: rgba(var(--muted), 0.15);
    color: rgb(var(--text));
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
/* ════════════════════════════════════════════════════════════════
   HIGH-CONTRAST ACCESSIBLE SELECT & DROPDOWNS IN EDIT EVENT
   ════════════════════════════════════════════════════════════════ */
.event-select,
.edit-series-dates-select {
    width: 100%;
    min-height: 46px;
    padding: 0.6rem 2.2rem 0.6rem 0.95rem;
    border-radius: 10px;
    border: 1.5px solid rgba(99, 102, 241, 0.45);
    background-color: #ffffff !important;
    color: #0f172a !important;
    font-size: 0.9rem;
    font-weight: 600;
    font-family: inherit;
    cursor: pointer;
    outline: none;
    appearance: none;
    -webkit-appearance: none;
    -moz-appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%236366f1' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 0.75rem center;
    background-size: 15px;
    box-shadow: 0 1px 4px rgba(0, 0, 0, 0.05);
    transition: all 0.2s ease;
}

.event-select:hover,
.edit-series-dates-select:hover {
    border-color: #6366f1;
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15);
}

.event-select:focus,
.edit-series-dates-select:focus {
    border-color: #4f46e5;
    background-color: #ffffff !important;
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.28);
}

.event-select option,
.edit-series-dates-select option {
    background-color: #ffffff !important;
    color: #0f172a !important;
    font-weight: 600;
    padding: 10px 14px;
}

:root[data-theme='dark'] .event-select,
:root[data-theme='dark'] .edit-series-dates-select {
    background-color: #1e293b !important;
    color: #f8fafc !important;
    border-color: rgba(129, 140, 248, 0.5) !important;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%23818cf8' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.4);
}

:root[data-theme='dark'] .event-select option,
:root[data-theme='dark'] .edit-series-dates-select option {
    background-color: #1e293b !important;
    color: #f8fafc !important;
}

/* ══ Occurrence Switcher & Recurrence Scope Polish ══ */
.occurrence-switcher-banner {
    background: linear-gradient(135deg, rgba(99, 102, 241, 0.1), rgba(168, 85, 247, 0.06));
    border: 1.5px solid rgba(99, 102, 241, 0.35);
    border-radius: 16px;
    padding: 1.15rem 1.4rem;
    margin-bottom: 1.75rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    flex-wrap: wrap;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.04);
}

.occurrence-switcher-header {
    display: flex;
    align-items: center;
    gap: 0.85rem;
}

.occurrence-switcher-controls {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.scope-radio-card {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
    padding: 0.95rem 1.15rem;
    border-radius: 12px;
    border: 1.5px solid rgba(99, 102, 241, 0.22);
    background: rgba(99, 102, 241, 0.03);
    cursor: pointer;
    transition: all 0.2s ease;
}

.scope-radio-card:hover {
    border-color: rgba(99, 102, 241, 0.45);
    background: rgba(99, 102, 241, 0.06);
}

.scope-radio-card:has(input:checked) {
    border-color: #6366f1;
    background: rgba(99, 102, 241, 0.09);
    box-shadow: 0 0 0 1px #6366f1;
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

    /* Tablet ticket primary grid */
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

    .occurrence-switcher-banner {
        flex-direction: column !important;
        align-items: stretch !important;
        gap: 1rem !important;
    }

    .occurrence-switcher-controls {
        width: 100% !important;
        justify-content: space-between !important;
    }

    .occurrence-switcher-controls .series-date-select-wrap {
        flex: 1 !important;
    }

    .occurrence-switcher-controls select {
        width: 100% !important;
    }
}

/* ══ Mobile Breakpoint (<= 600px) ══ */
@media (max-width: 600px) {
    .edit-event-wrapper {
        padding: 0;
    }

    .form-card {
        padding: 1.25rem 0.95rem;
        border-radius: 18px;
    }

    .event-form-section {
        padding: 1.15rem 0.85rem;
        border-radius: 14px;
        margin-bottom: 1.25rem;
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

    /* Occurrence Switcher Banner Mobile Layout */
    .occurrence-switcher-banner {
        padding: 1.1rem 1rem !important;
        border-radius: 14px !important;
    }

    .occurrence-switcher-controls {
        display: grid !important;
        grid-template-columns: 1fr 1fr !important;
        gap: 0.5rem !important;
    }

    .occurrence-switcher-controls .series-date-select-wrap {
        grid-column: 1 / -1 !important;
        width: 100% !important;
    }

    .occurrence-switcher-controls select {
        width: 100% !important;
        min-height: 46px !important;
    }

    .occurrence-switcher-controls .admin-action-btn {
        min-height: 44px !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        text-align: center !important;
    }

    .scope-date-jump-row {
        flex-direction: column !important;
        align-items: stretch !important;
        gap: 0.6rem !important;
    }

    .scope-select-wrapper {
        width: 100% !important;
        flex-direction: column !important;
        align-items: stretch !important;
    }

    .scope-select-wrapper select {
        width: 100% !important;
        min-width: 0 !important;
    }

    /* Guest cards stack cleanly on mobile */
    .guest-card-grid {
        grid-template-columns: 1fr !important;
        gap: 0.75rem;
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
    .ticket-remove-btn {
        min-height: 40px !important;
        padding: 0.45rem 0.75rem !important;
        font-size: 0.82rem !important;
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

    .form-submit-row {
        flex-direction: column;
        gap: 0.75rem;
    }

    .form-publish-btn,
    .form-cancel-btn {
        width: 100%;
        min-height: 52px;
    }
}

/* --- Vintage Theme Enhancements for Edit Event --- */
.edit-event-hero h1 {
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

<div class="edit-event-wrapper">
    <!-- Top Nav & Admin Notice -->
    <div class="edit-top-nav">
        <a href="{{ route('host/event/management/' . $event->id) }}" class="edit-back-link">
            ← Back to Event Management
        </a>
        <?php if (!empty($isAdmin) && !empty($organizerName)): ?>
            <div class="admin-edit-notice">
                <span>🛡️</span>
                <span class="admin-notice-title">Admin View</span>
                <span class="admin-notice-dot">·</span>
                <span>Event by <strong><?= e($organizerName) ?></strong></span>
                <span class="admin-notice-dot">·</span>
                <span><?= e($organizerEmail) ?></span>
            </div>
        <?php endif; ?>
    </div>

    <!-- Page Hero -->
    <header class="edit-event-hero">
        <span class="panel-badge">✏️ Editing Mode</span>
        <h1>Edit Event: <?= e($event->name) ?></h1>
        <p class="lede">
            Update your event schedule, venue address, ticket tiers, special guests, or custom marketing URLs.
        </p>
    </header>

    <?php if (!empty($event->is_recurring) && !empty($recurringOccurrences) && count($recurringOccurrences) > 1): ?>
        <?php
            $totalOcc = count($recurringOccurrences);
            $currentIndex = 0;
            foreach ($recurringOccurrences as $idx => $occ) {
                if ($occ['id'] == $event->id) {
                    $currentIndex = $idx;
                    break;
                }
            }
            $prevOcc = $currentIndex > 0 ? $recurringOccurrences[$currentIndex - 1] : null;
            $nextOcc = $currentIndex < $totalOcc - 1 ? $recurringOccurrences[$currentIndex + 1] : null;
        ?>
        <!-- Occurrence Quick Switcher Banner -->
        <div class="occurrence-switcher-banner">
            <div class="occurrence-switcher-header">
                <div style="width: 44px; height: 44px; border-radius: 12px; background: rgba(99, 102, 241, 0.18); display: flex; align-items: center; justify-content: center; font-size: 1.4rem; flex-shrink: 0;">
                    🔄
                </div>
                <div>
                    <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                        <span style="font-size: 0.72rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; background: #6366f1; color: #fff; padding: 0.15rem 0.55rem; border-radius: 6px;">
                            Recurring Event Series
                        </span>
                        <strong style="color: rgb(var(--text)); font-size: 1rem;">
                            Editing Occurrence #<?= e($event->recurrence_sequence ?? ($currentIndex + 1)) ?> of <?= $totalOcc ?>
                        </strong>
                    </div>
                    <div style="font-size: 0.85rem; color: rgb(var(--muted)); margin-top: 0.25rem;">
                        This Occurrence Date: <strong style="color: rgb(var(--text));"><?= date('D, M j, Y • g:i A', strtotime($event->date)) ?></strong>
                    </div>
                </div>
            </div>

            <div class="occurrence-switcher-controls">
                <?php if ($prevOcc): ?>
                    <a href="{{ route('host/event/edit/' . $prevOcc['id']) }}" class="admin-action-btn" style="padding: 0.5rem 0.85rem; font-size: 0.82rem; background: rgba(var(--text), 0.06); text-decoration: none; border-radius: 8px; font-weight: 600;" title="Go to Previous Occurrence (<?= date('M j', strtotime($prevOcc['date'])) ?>)">
                        ← Prev
                    </a>
                <?php endif; ?>

                <div class="series-date-select-wrap" style="display: flex; align-items: center; gap: 0.4rem;">
                    <label for="series-date-jump" style="font-size: 0.82rem; font-weight: 700; color: #818cf8; white-space: nowrap;">
                        Switch Date:
                    </label>
                    <select id="series-date-jump" class="edit-series-dates-select" style="min-width: 260px;" onchange="if(this.value && this.value != '<?= $event->id ?>') window.location.href = '<?= route('host/event/edit/') ?>' + this.value;" aria-label="Switch occurrence date">
                        <?php foreach ($recurringOccurrences as $occ): ?>
                            <?php
                                $occTime = strtotime($occ['date']);
                                $isCur = ($occ['id'] == $event->id);
                                $isPast = $occTime < time();
                            ?>
                            <option value="<?= $occ['id'] ?>" <?= $isCur ? 'selected' : '' ?>>
                                #<?= e($occ['recurrence_sequence'] ?? '?') ?>  —  <?= date('D, M d, Y • g:i A', $occTime) ?> <?= $isCur ? '★ (Current Editing)' : ($isPast ? '(Past)' : '(Upcoming)') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <?php if ($nextOcc): ?>
                    <a href="{{ route('host/event/edit/' . $nextOcc['id']) }}" class="admin-action-btn" style="padding: 0.5rem 0.85rem; font-size: 0.82rem; background: rgba(var(--text), 0.06); text-decoration: none; border-radius: 8px; font-weight: 600;" title="Go to Next Occurrence (<?= date('M j', strtotime($nextOcc['date'])) ?>)">
                        Next →
                    </a>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <div class="form-card">
        <!-- Error / Feedback Messages -->
        <div class="auth-message" aria-live="polite" style="display: none;"></div>

        <?php $errorModel = $model ?? $event ?? null; ?>
        <?php if (isset($errorModel) && $errorModel->hasErrors()): ?>
            <div class="alert alert-danger" style="margin-bottom: 1.5rem; border-radius: 14px; padding: 1.15rem 1.35rem; background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.28); color: #ef4444;">
                <h4 style="margin: 0 0 0.5rem; font-size: 0.95rem; font-weight: 800; display: flex; align-items: center; gap: 0.4rem;">
                    <span>⚠️</span> Please correct the following errors:
                </h4>
                <ul style="margin: 0; padding-left: 1.25rem; font-size: 0.88rem; line-height: 1.6;">
                    <?php foreach ($errorModel->getErrorMessages() as $errorMessage): ?>
                        <li><?= e($errorMessage) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form id="edit-event-form" method="POST" action="{{ route('host/event/edit/' . $event->id) }}" enctype="multipart/form-data" novalidate>
            <?= csrf_field() ?>

            <!-- Section 1: General Event Details -->
            <div class="event-form-section">
                <div class="event-section-header">
                    <div class="event-section-title-wrap">
                        <span class="event-section-icon">🎉</span>
                        <div>
                            <h3 class="event-section-title">General Event Information</h3>
                            <p class="event-section-subtitle">Event title, cover image, and short URL slug.</p>
                        </div>
                    </div>
                </div>

                <div class="event-field-group">
                    <label class="event-label" for="event-name">
                        Event Title / Name <span class="required-star">*</span>
                    </label>
                    <input type="text" id="event-name" name="event_name" class="event-input" required value="<?= e($event->name) ?>" placeholder="e.g. Summer Beats Festival 2026">
                </div>

                <div class="event-field-group">
                    <label class="event-label" for="event-image">
                        Event Cover Banner <span class="optional-tag">(Optional)</span>
                    </label>

                    <?php if (!empty($event->image)): ?>
                        <div id="current-image-wrapper" class="current-cover-bar">
                            <img src="<?= e($event->image) ?>" alt="Current Event Cover" class="current-cover-thumb">
                            <div>
                                <strong style="font-size:0.88rem; color:rgb(var(--text)); display:block;">Current Cover Banner</strong>
                                <span style="font-size:0.78rem; color:rgb(var(--muted));">Upload a new image below if you wish to replace it.</span>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="image-upload-card">
                        <input type="file" id="event-image" name="event_image" accept="image/jpeg,image/png,image/webp,image/gif" style="width:100%; font-size:0.9rem;">
                        <div id="image-preview-container" class="hidden preview-container">
                            <img id="image-preview" src="" alt="Event Image Preview">
                            <button type="button" id="remove-image-btn" class="preview-close-btn" title="Remove image">✕</button>
                        </div>
                        <small class="event-help-text" style="display:block; margin-top:0.5rem;">
                            Upload to replace the current banner (JPG, PNG, WEBP, GIF. Max: 5MB). Leave blank to keep current image.
                        </small>
                    </div>
                </div>

                <div class="event-field-group">
                    <label class="event-label" for="custom-url">
                        Custom Event URL <span class="optional-tag">(Optional)</span>
                    </label>
                    <div class="url-slug-wrapper">
                        <span class="url-prefix-badge">/events/</span>
                        <?php $cleanCustomUrl = preg_replace('/-\d{4}-\d{2}-\d{2}$/', '', $event->custom_url ?? ''); ?>
                        <input type="text" id="custom-url" name="custom_url" class="url-slug-input" value="<?= e($cleanCustomUrl) ?>" placeholder="summer-beats-festival" aria-label="Custom URL slug">
                    </div>
                    <small class="event-help-text">
                        Short link for your event page. Leave empty to automatically generate from event title.
                    </small>
                </div>
            </div>

            <!-- Section 2: Date, Time & Venue -->
            <div class="event-form-section">
                <div class="event-section-header">
                    <div class="event-section-title-wrap">
                        <span class="event-section-icon">📅</span>
                        <div>
                            <h3 class="event-section-title">Schedule &amp; Location</h3>
                            <p class="event-section-subtitle">Updated dates, timings, category, and venue coordinates.</p>
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
                            <input type="text" id="event-date" name="event_date" class="event-input" required value="<?= e($eventDate ?? '') ?>" placeholder="Select a date" readonly>
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
                            <input type="text" id="event-time" name="event_time" class="event-input" required value="<?= e($eventTime ?? '') ?>" placeholder="Select a time" readonly>
                        </div>
                    </div>
                </div>

                <?php if (!empty($event->is_recurring) && !empty($event->recurrence_group_id)): ?>
                <!-- Recurring Series Scope Box -->
                <div class="setting-card" style="margin-bottom: 1.5rem; border: 1.5px solid rgba(99, 102, 241, 0.35); background: rgba(99, 102, 241, 0.04); border-radius: 16px; padding: 1.25rem 1.35rem;">
                    <div style="display: flex; align-items: flex-start; gap: 0.85rem;">
                        <span style="font-size: 1.35rem; flex-shrink: 0; margin-top: 2px;">🔄</span>
                        <div style="flex: 1; min-width: 0;">
                            <strong style="color: rgb(var(--text)); font-size: 0.98rem; display: block; line-height: 1.3;">
                                Recurring Event Series (Occurrence #<?= e($event->recurrence_sequence ?? 1) ?> of <?= e($event->recurrence_count ?? 'multiple') ?> &bull; <?= ucfirst(e($event->recurrence_type ?? 'recurring')) ?>)
                            </strong>
                            <p style="margin: 0.25rem 0 0.85rem; font-size: 0.82rem; color: rgb(var(--muted));">
                                Choose how you want edits to apply across occurrences in this series:
                            </p>
                            
                            <div class="scope-options-group" style="display: flex; flex-direction: column; gap: 0.65rem;">
                                <label class="scope-radio-card">
                                    <input type="radio" name="recurrence_update_scope" value="this_only" checked style="margin-top: 3px; accent-color: #6366f1;">
                                    <div>
                                        <strong style="color: rgb(var(--text)); font-size: 0.92rem; display: block;">Update this occurrence only</strong>
                                        <span style="font-size: 0.8rem; color: rgb(var(--muted)); display: block; margin-top: 2px;">Modifications will apply strictly to #<?= e($event->recurrence_sequence ?? 1) ?> (<?= date('M j, Y', strtotime($event->date)) ?>). Other occurrences remain untouched.</span>
                                    </div>
                                </label>
                                <label class="scope-radio-card">
                                    <input type="radio" name="recurrence_update_scope" value="all_future" style="margin-top: 3px; accent-color: #6366f1;">
                                    <div>
                                        <strong style="color: rgb(var(--text)); font-size: 0.92rem; display: block;">Update this and all future occurrences</strong>
                                        <span style="font-size: 0.8rem; color: rgb(var(--muted)); display: block; margin-top: 2px;">Propagates changes (title, description, venue, artwork, custom slug) to this and all upcoming occurrences in this series.</span>
                                    </div>
                                </label>
                            </div>

                            <?php if (!empty($recurringOccurrences) && count($recurringOccurrences) > 1): ?>
                            <div class="scope-date-jump-row" style="margin-top: 1.15rem; padding-top: 1rem; border-top: 1px dashed rgba(99, 102, 241, 0.25); display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 0.65rem;">
                                <span style="font-size: 0.82rem; color: rgb(var(--muted)); font-weight: 600;">
                                    Need to edit a different date?
                                </span>
                                <div class="scope-select-wrapper" style="display: flex; align-items: center; gap: 0.5rem;">
                                    <label for="scope-date-jump" style="font-size: 0.8rem; font-weight: 700; color: #818cf8; white-space: nowrap;">
                                        Select Date:
                                    </label>
                                    <select id="scope-date-jump" class="edit-series-dates-select" style="min-width: 260px;" onchange="if(this.value && this.value != '<?= $event->id ?>') window.location.href = '<?= route('host/event/edit/') ?>' + this.value;" aria-label="Select date to edit">
                                        <?php foreach ($recurringOccurrences as $occ): ?>
                                            <option value="<?= $occ['id'] ?>" <?= $occ['id'] == $event->id ? 'selected' : '' ?>>
                                                #<?= e($occ['recurrence_sequence'] ?? '?') ?>: <?= date('D, M j, Y @ g:i A', strtotime($occ['date'])) ?> <?= $occ['id'] == $event->id ? '★ (Current)' : '' ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php else: ?>
                <!-- Option to convert this single event into a recurring series -->
                <div class="setting-tile" style="margin-bottom: 1.5rem; border: 1.5px dashed rgba(var(--brand), 0.35); background: rgba(var(--brand), 0.03);">
                    <label class="setting-tile-label" for="is-recurring-toggle">
                        <div class="setting-tile-checkbox-wrap">
                            <input type="checkbox" id="is-recurring-toggle" name="is_recurring" value="1" <?= !empty(old('is_recurring')) ? 'checked' : '' ?> class="setting-tile-checkbox">
                        </div>
                        <div class="setting-tile-content">
                            <div class="setting-tile-title">
                                <span>🔄</span> Convert to Recurring Event Series
                            </div>
                            <div class="setting-tile-desc">
                                Want this event to repeat? Turn this on to automatically generate upcoming occurrences with independent ticket pools starting from this event's date.
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
                <?php endif; ?>

                <div class="event-grid-3">
                    <div class="event-field-group">
                        <label class="event-label" for="event-category">
                            Category <span class="required-star">*</span>
                        </label>
                        <select id="event-category" name="event_category" class="event-select" required>
                            <?php foreach (($categories ?? []) as $cat): ?>
                                <option value="<?= e($cat) ?>" <?= ($event->category ?? 'Concerts') === $cat ? 'selected' : '' ?>><?= e($cat) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="event-field-group">
                        <label class="event-label" for="event-venue">
                            Venue Address <span class="required-star">*</span>
                        </label>
                        <input type="text" id="event-venue" name="event_venue" class="event-input" required value="<?= e($eventVenue ?? '') ?>" placeholder="e.g. Central Park">
                    </div>

                    <div class="event-field-group">
                        <label class="event-label" for="event-city">
                            City <span class="required-star">*</span>
                        </label>
                        <input type="text" id="event-city" name="event_city" class="event-input" required value="<?= e($eventCity ?? '') ?>" placeholder="e.g. New York">
                    </div>
                </div>
            </div>

            <!-- Section 3: Description -->
            <div class="event-form-section">
                <div class="event-section-header">
                    <div class="event-section-title-wrap">
                        <span class="event-section-icon">📝</span>
                        <div>
                            <h3 class="event-section-title">Event Story &amp; Details</h3>
                            <p class="event-section-subtitle">Updated description, agenda, instructions, and FAQs.</p>
                        </div>
                    </div>
                </div>

                <div class="event-field-group">
                    <label class="event-label" for="event-description">
                        Description &amp; Highlights <span class="required-star">*</span>
                    </label>
                    <textarea id="event-description" name="event_description" class="event-textarea" rows="5" required placeholder="Share what makes this event special."><?= e($event->description) ?></textarea>
                </div>
            </div>

            <!-- Section 4: Special Guests & Performers -->
            <div class="event-form-section guest-section">
                <div class="event-section-header">
                    <div class="event-section-title-wrap">
                        <span class="event-section-icon">🌟</span>
                        <div>
                            <h3 class="event-section-title">Special Guests &amp; Lineup</h3>
                            <p class="event-section-subtitle">Manage guest performers, speakers, and VIP attendees.</p>
                        </div>
                    </div>
                    <button type="button" id="add-guest-btn" class="form-secondary-btn">
                        <span>➕</span> Add Guest
                    </button>
                </div>

                <div id="guest-list" class="guest-list scrollable-list">
                    <?php if (!empty($eventGuests)): ?>
                        <?php foreach ($eventGuests as $index => $guest): ?>
                            <div class="guest-row guest-card">
                                <div class="guest-card-header">
                                    <div class="guest-card-avatar">
                                        <span class="guest-card-avatar-circle">👤</span>
                                        <span class="guest-card-title">Guest / Performer #<?= $index + 1 ?></span>
                                    </div>
                                    <button type="button" class="guest-remove guest-card-remove-btn" title="Remove guest">
                                        <span>✕</span> Remove
                                    </button>
                                </div>
                                <div class="guest-card-grid">
                                    <div class="event-field-group">
                                        <label class="event-label">Name <span class="required-star">*</span></label>
                                        <input type="text" name="guest-name[]" class="event-input" value="<?= e($guest['name']) ?>" placeholder="Artist or Speaker Name" required>
                                    </div>
                                    <div class="event-field-group">
                                        <label class="event-label">Role / Title <span class="optional-tag">(Optional)</span></label>
                                        <input type="text" name="guest-role[]" class="event-input" value="<?= e($guest['role'] ?? '') ?>" placeholder="e.g. Headliner, Keynote, Panelist">
                                    </div>
                                    <div class="event-field-group">
                                        <label class="event-label">Contact Phone <span class="optional-tag">(Optional)</span></label>
                                        <input type="tel" name="guest-phone[]" class="event-input" value="<?= e($guest['phone'] ?? '') ?>" placeholder="+1 555 555 5555">
                                    </div>
                                    <div class="event-field-group">
                                        <label class="event-label">Social / Website <span class="optional-tag">(Optional)</span></label>
                                        <input type="text" name="guest-social[]" class="event-input" value="<?= e($guest['social'] ?? '') ?>" placeholder="@handle or https://...">
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Section 5: Ticket Types & Pricing -->
            <div class="event-form-section ticket-section">
                <div class="event-section-header">
                    <div class="event-section-title-wrap">
                        <span class="event-section-icon">🎟️</span>
                        <div>
                            <h3 class="event-section-title">Ticket Packages &amp; Tiers</h3>
                            <p class="event-section-subtitle">Manage pricing tiers, capacities, and live ticket statuses.</p>
                        </div>
                    </div>
                    <button type="button" id="add-ticket-btn" class="form-secondary-btn">
                        <span>➕</span> Add Ticket Tier
                    </button>
                </div>

                <div id="ticket-list" class="ticket-list scrollable-list" data-currency="<?= e(get_currency_symbol()) ?>">
                    <?php if (!empty($ticketTypes)): ?>
                        <?php foreach ($ticketTypes as $index => $tt): 
                            $sold = (int)($tt['sold'] ?? 0);
                            $issued = (int)($tt['issued'] ?? 0);
                            $taken = $sold + $issued;
                            $remaining = max(0, (int)$tt['capacity'] - $taken);
                        ?>
                            <div class="ticket-row-item ticket-card">
                                <input type="hidden" name="ticket-id[]" value="<?= $tt['id'] ?>">
                                
                                <div class="ticket-card-header">
                                    <div class="ticket-card-badge">
                                        <span>🎟️</span>
                                        <span class="ticket-tier-number">Tier #<?= $index + 1 ?>: <?= e($tt['name']) ?></span>
                                    </div>
                                    <div>
                                        <?php if ($taken == 0): ?>
                                            <button type="button" class="remove-ticket-row-btn ticket-card-remove-btn" title="Remove ticket tier">
                                                <span class="remove-icon">✕</span>
                                                <span class="remove-text">Remove Tier</span>
                                            </button>
                                        <?php else: ?>
                                            <button type="button" class="remove-ticket-row-btn ticket-card-remove-btn" disabled title="Cannot delete tier with sold or issued tickets">
                                                <span class="remove-icon">🔒</span>
                                                <span class="remove-text">Locked (<?= number_format($taken) ?> Sold)</span>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="ticket-primary-grid">
                                    <div class="event-field-group">
                                        <label class="event-label">Tier Name <span class="required-star">*</span></label>
                                        <input type="text" name="ticket-name[]" class="event-input" required value="<?= e($tt['name']) ?>" placeholder="Tier Name (e.g. VIP, General Admission)">
                                    </div>
                                    <div class="event-field-group">
                                        <label class="event-label">Price (<?= e(get_currency_symbol()) ?>) <span class="required-star">*</span></label>
                                        <input type="number" step="0.01" name="ticket-price[]" class="event-input" required value="<?= (float)$tt['price'] ?>" placeholder="0.00" min="0">
                                        <?php if ($taken > 0): ?>
                                            <small style="display:block; margin-top:4px; font-size:0.75rem; color:#d97706; font-weight:700;">
                                                ℹ️ Future sales only. <?= number_format($taken) ?> sold ticket(s) retain original price.
                                            </small>
                                        <?php endif; ?>
                                    </div>
                                    <div class="event-field-group">
                                        <label class="event-label">Total Capacity <span class="required-star">*</span></label>
                                        <input type="number" name="ticket-qty[]" class="event-input" required value="<?= (int)$tt['capacity'] ?>" min="<?= $taken ?>" placeholder="Capacity">
                                    </div>
                                </div>

                                <div class="ticket-access-shelf">
                                    <div class="ticket-subfield">
                                        <div class="ticket-subfield-header">
                                            <span>👥</span>
                                            <label class="event-label" style="margin:0;">Admits (Per Ticket)</label>
                                        </div>
                                        <input type="number" name="ticket-admits[]" class="event-input" value="<?= max(1, (int)($tt['admits'] ?? 1)) ?>" min="1" placeholder="1 (people/ticket)">
                                        <span class="ticket-subfield-hint">People entering per 1 ticket (1 = Single, 2 = Couple, 5 = Table).</span>
                                    </div>
                                    <div class="ticket-subfield">
                                        <div class="ticket-subfield-header">
                                            <span>🔒</span>
                                            <label class="event-label" style="margin:0;">Max Per Person</label>
                                        </div>
                                        <input type="number" name="ticket-max-per-person[]" class="event-input" value="<?= !empty($tt['max_per_person']) ? (int)$tt['max_per_person'] : '' ?>" min="1" placeholder="Unlimited">
                                        <span class="ticket-subfield-hint">Purchase limit per customer email (leave blank for unlimited).</span>
                                    </div>
                                </div>

                                <?php if ($taken > 0): ?>
                                    <div class="tier-stats-bar" style="margin-top:0.85rem; padding-top:0.75rem; border-top:1px dashed rgba(var(--muted),0.2); display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:0.5rem; font-size:0.82rem;">
                                        <span style="color:#10b981; font-weight:700;">
                                            🎟️ <?= number_format($taken) ?> Tickets Taken (<?= number_format($sold) ?> sold, <?= number_format($issued) ?> issued)
                                        </span>
                                        <span style="color:rgb(var(--brand)); font-weight:700;">
                                            📊 <?= number_format($remaining) ?> Remaining Available
                                        </span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Section 6: Ticketing & Marketing Settings -->
            <div class="event-form-section">
                <div class="event-section-header">
                    <div class="event-section-title-wrap">
                        <span class="event-section-icon">⚙️</span>
                        <div>
                            <h3 class="event-section-title">Ticketing &amp; Marketing Settings</h3>
                            <p class="event-section-subtitle">Configure fee absorption, landing pages, and flexible payments.</p>
                        </div>
                    </div>
                </div>

                <!-- Fee Settings -->
                <div class="setting-tile">
                    <label class="setting-tile-label" for="pass-fee">
                        <div class="setting-tile-checkbox-wrap">
                            <input type="checkbox" id="pass-fee" name="pass_fee" value="1" <?= !empty($event->pass_fee) ? 'checked' : '' ?> class="setting-tile-checkbox">
                        </div>
                        <div class="setting-tile-content">
                            <div class="setting-tile-title">
                                <span>💸</span> Pass <?= e(get_setting('platform_fee', 5)) ?>% Platform Fee to Attendees
                                <span class="setting-badge-recommended">Recommended</span>
                            </div>
                            <div class="setting-tile-desc">
                                <strong>Checked:</strong> Attendees pay the <?= e(get_setting('platform_fee', 5)) ?>% ticketing fee on checkout.<br>
                                <strong>Unchecked:</strong> You absorb the fee, which is deducted before payout on ticket sales.
                            </div>
                        </div>
                    </label>
                </div>

                <!-- Dedicated Flyer Page -->
                <div class="setting-tile setting-tile--flyer">
                    <input type="hidden" name="flyer_page_enabled" value="0">
                    <label class="setting-tile-label" for="flyer-page-enabled">
                        <div class="setting-tile-checkbox-wrap">
                            <input type="checkbox" id="flyer-page-enabled" name="flyer_page_enabled" value="1" <?= !empty(old('flyer_page_enabled', $event->flyer_page_enabled ?? 0)) ? 'checked' : '' ?> class="setting-tile-checkbox">
                        </div>
                        <div class="setting-tile-content">
                            <div class="setting-tile-title" style="color: #6366f1;">
                                <span>🎨</span> Dedicated Visual Flyer Landing Page Enabled (<code><?= e(event_flyer_url($event)) ?></code>)
                                <span class="setting-badge-promo">Social Ready</span>
                            </div>
                            <div class="setting-tile-desc">
                                Provides an immersive artwork-background ticket booth with a short link. Perfect for linking directly in Instagram bio, TikTok, and WhatsApp broadcasts.
                            </div>
                        </div>
                    </label>
                </div>

                <!-- Partial Payment (Layaway Plan) Setting -->
                <div class="setting-tile">
                    <input type="hidden" name="allow_partial_payment" value="0">
                    <label class="setting-tile-label" for="allow-partial-payment">
                        <div class="setting-tile-checkbox-wrap">
                            <input type="checkbox" id="allow-partial-payment" name="allow_partial_payment" value="1" <?= !empty(old('allow_partial_payment', $event->allow_partial_payment ?? 0)) ? 'checked' : '' ?> class="setting-tile-checkbox" onchange="document.getElementById('partial-payment-options').style.display = this.checked ? 'block' : 'none'">
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
                    <div id="partial-payment-options" style="display:<?= !empty(old('allow_partial_payment', $event->allow_partial_payment ?? 0)) ? 'block' : 'none' ?>; margin-top:1rem; padding-top:1rem; border-top:1px dashed rgba(var(--muted),0.2);">
                        <label class="event-label" style="font-size:0.85rem; font-weight:700;">Minimum Deposit Percentage</label>
                        <div style="display:flex; align-items:center; gap:0.6rem; margin-top:0.35rem;">
                            <input type="number" name="partial_payment_min_percent" class="event-input" value="<?= e(old('partial_payment_min_percent', $event->partial_payment_min_percent ?? 25)) ?>" min="10" max="90" step="5" style="width:110px;">
                            <span style="font-size:0.85rem; color:rgb(var(--muted)); font-weight:600;">% of ticket total (default 25%)</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submit Row -->
            <div class="form-submit-row">
                <button type="submit" class="form-publish-btn">
                    <span>💾</span> Save Changes
                </button>
                <a href="{{ route('host/event/management/' . $event->id) }}" class="form-cancel-btn">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
