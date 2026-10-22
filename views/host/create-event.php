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

/* Special Guests List */
.guest-list {
    display: flex;
    flex-direction: column;
    gap: 0.85rem;
}

.guest-row {
    background: rgb(var(--card));
    border: 1px solid rgba(var(--muted), 0.16);
    border-radius: 14px;
    padding: 1.15rem;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);
}

.guest-row-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 0.85rem;
}

.guest-remove {
    background: rgba(239, 68, 68, 0.1);
    color: #ef4444 !important;
    border: 1px solid rgba(239, 68, 68, 0.25);
    border-radius: 8px;
    padding: 0.45rem 0.95rem;
    font-size: 0.82rem;
    font-weight: 700;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    transition: all 0.2s ease;
    font-family: inherit;
}

.guest-remove:hover {
    background: rgba(239, 68, 68, 0.2);
    border-color: rgba(239, 68, 68, 0.45);
    transform: translateY(-1px);
}

/* Ticket Tiers */
.ticket-list {
    display: flex;
    flex-direction: column;
    gap: 0.85rem;
}

.ticket-row-item {
    background: rgb(var(--card));
    border: 1px solid rgba(var(--muted), 0.16);
    border-radius: 14px;
    padding: 1.15rem;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);
}

.ticket-tier-grid {
    display: grid;
    grid-template-columns: 2.2fr 1fr 1fr 0.9fr 0.9fr auto;
    gap: 0.85rem;
    align-items: flex-end;
}

.tier-remove-wrap {
    display: flex;
    align-items: center;
    height: 48px;
}

.ticket-remove-btn {
    width: 44px;
    height: 44px;
    min-width: 44px;
    border-radius: 10px;
    background: rgba(239, 68, 68, 0.1);
    color: #ef4444 !important;
    border: 1px solid rgba(239, 68, 68, 0.25);
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
    transition: all 0.2s ease;
}

.ticket-remove-btn:hover:not(:disabled) {
    background: #ef4444;
    color: #ffffff !important;
}

.ticket-remove-btn:disabled {
    background: rgba(var(--muted), 0.1);
    color: rgba(var(--text), 0.35) !important;
    border-color: rgba(var(--muted), 0.2);
    cursor: not-allowed;
}

.ticket-remove-btn .remove-text {
    display: none;
}

/* Staffing Assistance Section */
.staffing-toggle-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    cursor: pointer;
    font-weight: 700;
    font-size: 0.88rem;
    user-select: none;
    background: rgba(var(--brand), 0.08);
    padding: 0.45rem 0.95rem;
    border-radius: 99px;
    border: 1px solid rgba(var(--brand), 0.25);
    transition: all 0.2s ease;
}

.staffing-toggle-badge:hover {
    background: rgba(var(--brand), 0.15);
}

.staff-roles-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
    gap: 1rem;
    margin-bottom: 1.25rem;
}

.staff-role-card {
    background: rgb(var(--card));
    border: 1px solid rgba(var(--muted), 0.18);
    border-radius: 14px;
    padding: 1rem 1.15rem;
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    transition: all 0.2s ease;
}

.staff-role-card:hover {
    border-color: rgba(var(--brand), 0.4);
    box-shadow: 0 4px 14px rgba(0, 0, 0, 0.03);
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
    gap: 0.35rem;
}

.staff-qty-input {
    width: 58px;
    height: 36px;
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

/* Settings Cards (Fee & Flyer) */
.setting-card {
    background: rgba(var(--muted), 0.04);
    border: 1px solid rgba(var(--muted), 0.14);
    border-radius: 16px;
    padding: 1.25rem 1.35rem;
    margin-bottom: 1.25rem;
    transition: all 0.2s ease;
}

.setting-card--flyer {
    background: rgba(99, 102, 241, 0.04);
    border: 1.5px solid rgba(99, 102, 241, 0.25);
}

.setting-card-label {
    display: flex;
    align-items: flex-start;
    gap: 0.85rem;
    cursor: pointer;
    user-select: none;
}

.setting-checkbox {
    width: 20px;
    height: 20px;
    margin-top: 0.2rem;
    accent-color: rgb(var(--brand));
    flex-shrink: 0;
    cursor: pointer;
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
}

/* ══ Mobile Breakpoint (<= 600px) ══ */
@media (max-width: 600px) {
    .create-event-wrapper {
        padding: 0;
    }

    .form-card {
        padding: 1.25rem 1rem;
        border-radius: 18px;
    }

    .event-form-section {
        padding: 1.1rem 0.9rem;
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
    .event-textarea {
        font-size: 16px !important; /* Prevents auto-zoom on iOS */
        min-height: 46px;
    }

    /* Guest rows stack vertically */
    .guest-row-grid {
        grid-template-columns: 1fr !important;
        gap: 0.75rem;
    }

    .guest-remove {
        width: 100%;
        justify-content: center;
        padding: 0.6rem;
    }

    /* Ticket tiers stack vertically */
    .ticket-tier-grid {
        display: flex !important;
        flex-direction: column !important;
        gap: 0.75rem !important;
        align-items: stretch !important;
    }

    .tier-remove-wrap {
        height: auto;
    }

    .ticket-remove-btn {
        width: 100% !important;
        height: 44px !important;
        border-radius: 10px !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 0.4rem !important;
        font-weight: 700 !important;
        font-size: 0.9rem !important;
    }

    .ticket-remove-btn .remove-text {
        display: inline !important;
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
    }
}
</style>

<div class="create-event-wrapper">
    <!-- Page Hero -->
    <header class="create-event-hero">
        <span class="panel-badge">✨ Host Dashboard</span>
        <h1>Create Your Event &amp; Start Selling</h1>
        <p class="lede">
            Configure your event information, launch tiered ticket packages, and showcase special guests with a seamless experience for attendees.
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
                <div class="setting-card" style="margin-bottom: 1.5rem; border: 1.5px dashed rgba(var(--brand), 0.35); background: rgba(var(--brand), 0.03); border-radius: 12px; padding: 1.1rem 1.25rem;">
                    <label class="setting-card-label" style="margin: 0; cursor: pointer;">
                        <input type="checkbox" id="is-recurring-toggle" name="is_recurring" value="1" <?= !empty(old('is_recurring')) ? 'checked' : '' ?> class="setting-checkbox">
                        <div style="flex: 1;">
                            <strong style="color:rgb(var(--text)); font-size:0.95rem; display:flex; align-items:center; gap:0.4rem;">
                                <span>🔄</span> Repeat Event (Recurring Series)
                            </strong>
                            <small class="event-help-text" style="display:block; margin-top:0.25rem;">
                                Automatically generate linked occurrences with independent ticket pools for daily, weekly, bi-weekly, or monthly dates.
                            </small>
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
                            <?php foreach ($categories as $cat): ?>
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

            <!-- Section 5: Special Guests & Performers -->
            <div class="event-form-section guest-section">
                <div class="event-section-header">
                    <div class="event-section-title-wrap">
                        <span class="event-section-icon">🌟</span>
                        <div>
                            <h3 class="event-section-title">Special Guests &amp; Lineup</h3>
                            <p class="event-section-subtitle">Spotlight featured artists, speakers, panelists, and hosts.</p>
                        </div>
                    </div>
                    <button type="button" id="add-guest-btn" class="form-secondary-btn">
                        <span>➕</span> Add Guest
                    </button>
                </div>

                <div id="guest-list" class="guest-list scrollable-list">
                    <?php 
                    $oldGuestNames = old('guest-name', []);
                    $oldGuestRoles = old('guest-role', []);
                    $oldGuestPhones = old('guest-phone', []);
                    $oldGuestSocials = old('guest-social', []);
                    if (is_array($oldGuestNames) && count($oldGuestNames) > 0):
                        foreach ($oldGuestNames as $idx => $gName):
                            if (empty($gName)) continue;
                    ?>
                        <div class="guest-row">
                            <div class="guest-row-grid">
                                <label class="event-field-group">
                                    <span class="event-label">Name <span class="required-star">*</span></span>
                                    <input type="text" name="guest-name[]" class="event-input" value="<?= e($gName) ?>" placeholder="Artist / Speaker Name" required>
                                </label>
                                <label class="event-field-group">
                                    <span class="event-label">Role <span class="optional-tag">(Optional)</span></span>
                                    <input type="text" name="guest-role[]" class="event-input" value="<?= e($oldGuestRoles[$idx] ?? '') ?>" placeholder="Performer / Keynote">
                                </label>
                                <label class="event-field-group">
                                    <span class="event-label">Phone <span class="optional-tag">(Optional)</span></span>
                                    <input type="tel" name="guest-phone[]" class="event-input" value="<?= e($oldGuestPhones[$idx] ?? '') ?>" placeholder="+1 555 555 5555">
                                </label>
                                <label class="event-field-group">
                                    <span class="event-label">Social <span class="optional-tag">(Optional)</span></span>
                                    <input type="text" name="guest-social[]" class="event-input" value="<?= e($oldGuestSocials[$idx] ?? '') ?>" placeholder="@handle or https://...">
                                </label>
                            </div>
                            <div style="display:flex; justify-content:flex-end; margin-top:0.75rem;">
                                <button type="button" class="guest-remove">🗑️ Remove Guest</button>
                            </div>
                        </div>
                    <?php 
                        endforeach;
                    endif; 
                    ?>
                </div>
            </div>

            <!-- Section 6: Ticket Types & Pricing -->
            <div class="event-form-section ticket-section">
                <div class="event-section-header">
                    <div class="event-section-title-wrap">
                        <span class="event-section-icon">🎟️</span>
                        <div>
                            <h3 class="event-section-title">Ticket Packages &amp; Tiers</h3>
                            <p class="event-section-subtitle">Set prices, quantities, and admission tiers for your attendees.</p>
                        </div>
                    </div>
                    <button type="button" id="add-ticket-btn" class="form-secondary-btn">
                        <span>➕</span> Add Ticket Tier
                    </button>
                </div>

                <div id="ticket-list" class="ticket-list scrollable-list">
                    <?php 
                    $oldTicketNames = old('ticket-name', []);
                    $oldTicketPrices = old('ticket-price', []);
                    $oldTicketQtys = old('ticket-qty', []);
                    $oldTicketAdmits = old('ticket-admits', []);
                    $oldTicketMaxPerPerson = old('ticket-max-per-person', []);
                    if (is_array($oldTicketNames) && count($oldTicketNames) > 0):
                        foreach ($oldTicketNames as $idx => $tName):
                            if (empty($tName)) continue;
                    ?>
                        <div class="ticket-row-item">
                            <input type="hidden" name="ticket-id[]" value="0">
                            <div class="ticket-tier-grid">
                                <label class="tier-name-label event-field-group" style="margin-bottom:0;">
                                    <span class="event-label">Tier Name <span class="required-star">*</span></span>
                                    <input type="text" name="ticket-name[]" class="event-input" value="<?= e($tName) ?>" required placeholder="Tier Name (e.g. VIP, Table for 5, General)">
                                </label>
                                <label class="tier-price-label event-field-group" style="margin-bottom:0;">
                                    <span class="event-label">Price (<?= e(get_currency_symbol()) ?>) <span class="required-star">*</span></span>
                                    <input type="number" step="0.01" name="ticket-price[]" class="event-input" value="<?= e($oldTicketPrices[$idx] ?? '0.00') ?>" required placeholder="0.00" min="0">
                                </label>
                                <label class="tier-qty-label event-field-group" style="margin-bottom:0;">
                                    <span class="event-label">Total Capacity <span class="required-star">*</span></span>
                                    <input type="number" name="ticket-qty[]" class="event-input" value="<?= e($oldTicketQtys[$idx] ?? '100') ?>" required placeholder="Quantity" min="1">
                                </label>
                                <label class="tier-admits-label event-field-group" style="margin-bottom:0;">
                                    <span class="event-label">Admits / Table Size</span>
                                    <input type="number" name="ticket-admits[]" class="event-input" value="<?= e($oldTicketAdmits[$idx] ?? '1') ?>" min="1" placeholder="1 (e.g. 5 for Table of 5)">
                                </label>
                                <label class="tier-limit-label event-field-group" style="margin-bottom:0;">
                                    <span class="event-label">Max Per Person</span>
                                    <input type="number" name="ticket-max-per-person[]" class="event-input" value="<?= e($oldTicketMaxPerPerson[$idx] ?? '') ?>" min="1" placeholder="Unlimited">
                                </label>
                                <div class="tier-remove-wrap">
                                    <button type="button" class="remove-ticket-row-btn ticket-remove-btn" title="Remove ticket tier">
                                        <span class="remove-icon">✕</span>
                                        <span class="remove-text">Remove Tier</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php 
                        endforeach;
                    endif; 
                    ?>
                </div>
            </div>

            <!-- Section 7: Organization Staffing Assistance Request -->
            <div class="event-form-section staffing-request-section">
                <div class="event-section-header" style="align-items:flex-start;">
                    <div class="event-section-title-wrap">
                        <span class="event-section-icon">🤝</span>
                        <div>
                            <h3 class="event-section-title">Organization Staffing Support</h3>
                            <p class="event-section-subtitle">
                                Need on-site assistance? Request trained gate scanners, ushers, support leads, or box office staff.
                            </p>
                        </div>
                    </div>
                    <label class="staffing-toggle-badge">
                        <input type="checkbox" id="request-staffing-toggle" name="request_staffing" value="1" <?= old('request_staffing') ? 'checked' : '' ?> onchange="toggleStaffingOptions(this.checked)" style="accent-color:rgb(var(--brand));">
                        <span style="color:rgb(var(--brand));">Request Event Staff</span>
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
                                    <input type="checkbox" name="staff_roles[ticket-scanner][selected]" value="1" <?= old('staff_roles.ticket-scanner.selected') ? 'checked' : '' ?> onchange="updateStaffSummary()" style="accent-color:rgb(var(--brand));">
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
                                    <input type="checkbox" name="staff_roles[organizer-staff][selected]" value="1" <?= old('staff_roles.organizer-staff.selected') ? 'checked' : '' ?> onchange="updateStaffSummary()" style="accent-color:rgb(var(--brand));">
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
                                    <input type="checkbox" name="staff_roles[customer-support][selected]" value="1" <?= old('staff_roles.customer-support.selected') ? 'checked' : '' ?> onchange="updateStaffSummary()" style="accent-color:rgb(var(--brand));">
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
                                    <input type="checkbox" name="staff_roles[technical-ops][selected]" value="1" <?= old('staff_roles.technical-ops.selected') ? 'checked' : '' ?> onchange="updateStaffSummary()" style="accent-color:rgb(var(--brand));">
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
                                    <input type="checkbox" name="staff_roles[finance-manager][selected]" value="1" <?= old('staff_roles.finance-manager.selected') ? 'checked' : '' ?> onchange="updateStaffSummary()" style="accent-color:rgb(var(--brand));">
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

            <!-- Section 8: Ticketing & Marketing Settings -->
            <div class="event-form-section">
                <div class="event-section-header">
                    <div class="event-section-title-wrap">
                        <span class="event-section-icon">⚙️</span>
                        <div>
                            <h3 class="event-section-title">Ticketing &amp; Marketing Settings</h3>
                            <p class="event-section-subtitle">Configure fee pass-through and standalone marketing URLs.</p>
                        </div>
                    </div>
                </div>

                <!-- Fee Settings -->
                <div class="setting-card">
                    <label class="setting-card-label">
                        <input type="checkbox" id="pass-fee" name="pass_fee" value="1" <?= old('pass_fee', '1') ? 'checked' : '' ?> class="setting-checkbox">
                        <div>
                            <strong style="color:rgb(var(--text)); font-size:0.95rem; display:block;">
                                Pass <?= e(get_setting('platform_fee', 5)) ?>% platform fee to attendees
                            </strong>
                            <small class="event-help-text" style="display:block; margin-top:0.25rem;">
                                <strong>Checked:</strong> Attendees pay the <?= e(get_setting('platform_fee', 5)) ?>% ticketing fee on checkout (recommended). <br>
                                <strong>Unchecked:</strong> You absorb the fee, which is deducted before payout so attendees see transparent round figures.
                            </small>
                        </div>
                    </label>
                </div>

                <!-- Dedicated Flyer Page -->
                <div class="setting-card setting-card--flyer">
                    <div style="display: flex; align-items: flex-start; gap: 0.85rem;">
                        <input type="hidden" name="flyer_page_enabled" value="0">
                        <input type="checkbox" id="flyer-page-enabled" name="flyer_page_enabled" value="1" <?= !empty(old('flyer_page_enabled', '')) ? 'checked' : '' ?> class="setting-checkbox">
                        <label for="flyer-page-enabled" style="cursor: pointer; user-select: none; flex: 1; margin: 0;">
                            <strong style="color:#6366f1; font-size:0.95rem; display:flex; align-items:center; gap:0.35rem;">
                                <span>🎨</span> Enable dedicated visual flyer landing page (<code>/f/event-slug</code>)
                            </strong>
                            <small class="event-help-text" style="display:block; margin-top:0.25rem;">
                                Creates an immersive, artwork-centered ticket booth with a short link. Perfect for linking directly in Instagram bio, TikTok, and WhatsApp broadcasts.
                            </small>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Submit Row -->
            <div class="form-submit-row">
                <button type="submit" class="form-publish-btn">
                    <span>🚀</span> Publish Event &amp; Launch Tickets
                </button>
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