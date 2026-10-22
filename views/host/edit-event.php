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
    background: rgba(var(--muted), 0.08);
    color: rgba(var(--text), 0.4) !important;
    border-color: rgba(var(--muted), 0.2);
    cursor: not-allowed;
    width: auto !important;
    padding: 0 0.85rem !important;
    font-size: 0.8rem !important;
}

.ticket-remove-btn:disabled .remove-icon {
    display: none;
}

.ticket-remove-btn:disabled .remove-text {
    display: inline !important;
}

.ticket-remove-btn .remove-text {
    display: none;
}

/* Tier Stats Footer */
.tier-stats-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 0.5rem;
    margin-top: 0.65rem;
    padding-top: 0.55rem;
    border-top: 1px dashed rgba(var(--muted), 0.18);
    font-size: 0.82rem;
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
}

/* ══ Mobile Breakpoint (<= 600px) ══ */
@media (max-width: 600px) {
    .edit-event-wrapper {
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

    .ticket-remove-btn .remove-icon {
        display: none !important;
    }

    .form-submit-row {
        flex-direction: column;
        gap: 0.75rem;
    }

    .form-publish-btn,
    .form-cancel-btn {
        width: 100%;
    }
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
                        <input type="text" id="custom-url" name="custom_url" class="url-slug-input" value="<?= e($event->custom_url ?? '') ?>" placeholder="summer-beats-festival" aria-label="Custom URL slug">
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
                <div class="setting-card" style="margin-bottom: 1.5rem; border: 1.5px solid rgba(99, 102, 241, 0.35); background: rgba(99, 102, 241, 0.04); border-radius: 12px; padding: 1.15rem 1.25rem;">
                    <div style="display: flex; align-items: flex-start; gap: 0.75rem;">
                        <span style="font-size: 1.3rem;">🔄</span>
                        <div style="flex: 1;">
                            <strong style="color: rgb(var(--text)); font-size: 0.95rem; display: block;">
                                Recurring Event Series (Occurrence #<?= e($event->recurrence_sequence ?? 1) ?> of <?= e($event->recurrence_count ?? 'multiple') ?> &bull; <?= ucfirst(e($event->recurrence_type ?? 'recurring')) ?>)
                            </strong>
                            <p style="margin: 0.25rem 0 0.85rem; font-size: 0.82rem; color: rgb(var(--muted));">
                                Choose how you want edits to apply across occurrences in this series:
                            </p>
                            <div style="display: flex; flex-direction: column; gap: 0.5rem; font-size: 0.88rem;">
                                <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                                    <input type="radio" name="recurrence_update_scope" value="this_only" checked>
                                    <span><strong>Update this occurrence only</strong> (does not modify other dates in this series)</span>
                                </label>
                                <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                                    <input type="radio" name="recurrence_update_scope" value="all_future">
                                    <span><strong>Update this and all future occurrences</strong> (propagates details, location, and artwork to upcoming occurrences)</span>
                                </label>
                            </div>
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
                        <?php foreach ($eventGuests as $guest): ?>
                            <div class="guest-row">
                                <div class="guest-row-grid">
                                    <label class="event-field-group">
                                        <span class="event-label">Name <span class="required-star">*</span></span>
                                        <input type="text" name="guest-name[]" class="event-input" value="<?= e($guest['name']) ?>" placeholder="Artist / Speaker Name" required>
                                    </label>
                                    <label class="event-field-group">
                                        <span class="event-label">Role <span class="optional-tag">(Optional)</span></span>
                                        <input type="text" name="guest-role[]" class="event-input" value="<?= e($guest['role'] ?? '') ?>" placeholder="Performer / Keynote">
                                    </label>
                                    <label class="event-field-group">
                                        <span class="event-label">Phone <span class="optional-tag">(Optional)</span></span>
                                        <input type="tel" name="guest-phone[]" class="event-input" value="<?= e($guest['phone'] ?? '') ?>" placeholder="+1 555 555 5555">
                                    </label>
                                    <label class="event-field-group">
                                        <span class="event-label">Social <span class="optional-tag">(Optional)</span></span>
                                        <input type="text" name="guest-social[]" class="event-input" value="<?= e($guest['social'] ?? '') ?>" placeholder="@handle or https://...">
                                    </label>
                                </div>
                                <div style="display:flex; justify-content:flex-end; margin-top:0.75rem;">
                                    <button type="button" class="guest-remove">🗑️ Remove Guest</button>
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

                <div id="ticket-list" class="ticket-list scrollable-list">
                    <?php if (!empty($ticketTypes)): ?>
                        <?php foreach ($ticketTypes as $index => $tt): 
                            $sold = (int)($tt['sold'] ?? 0);
                            $issued = (int)($tt['issued'] ?? 0);
                            $taken = $sold + $issued;
                            $remaining = max(0, (int)$tt['capacity'] - $taken);
                        ?>
                            <div class="ticket-row-item">
                                <input type="hidden" name="ticket-id[]" value="<?= $tt['id'] ?>">
                                
                                <div class="ticket-tier-grid">
                                    <label class="tier-name-label event-field-group" style="margin-bottom:0;">
                                        <span class="event-label">Tier Name <span class="required-star">*</span></span>
                                        <input type="text" name="ticket-name[]" class="event-input" required value="<?= e($tt['name']) ?>" placeholder="Tier Name (e.g. VIP, General Admission)">
                                    </label>
                                    <label class="tier-price-label event-field-group" style="margin-bottom:0;">
                                        <span class="event-label">Price (<?= e(get_currency_symbol()) ?>) <span class="required-star">*</span></span>
                                        <input type="number" step="0.01" name="ticket-price[]" class="event-input" required value="<?= (float)$tt['price'] ?>" placeholder="Price">
                                        <?php if ($taken > 0): ?>
                                            <small style="display:block; margin-top:4px; font-size:0.75rem; color:#d97706; font-weight:700;">
                                                ℹ️ Future sales only. <?= number_format($taken) ?> sold ticket(s) retain original price.
                                            </small>
                                        <?php endif; ?>
                                    </label>
                                    <label class="tier-qty-label event-field-group" style="margin-bottom:0;">
                                        <span class="event-label">Total Capacity <span class="required-star">*</span></span>
                                        <input type="number" name="ticket-qty[]" class="event-input" required value="<?= (int)$tt['capacity'] ?>" min="<?= $taken ?>" placeholder="Quantity">
                                    </label>
                                    <label class="tier-admits-label event-field-group" style="margin-bottom:0;">
                                        <span class="event-label">Admits / Table Size</span>
                                        <input type="number" name="ticket-admits[]" class="event-input" value="<?= max(1, (int)($tt['admits'] ?? 1)) ?>" min="1" placeholder="1 (e.g. 5 for Table of 5)">
                                    </label>
                                    <label class="tier-limit-label event-field-group" style="margin-bottom:0;">
                                        <span class="event-label">Max Per Person</span>
                                        <input type="number" name="ticket-max-per-person[]" class="event-input" value="<?= !empty($tt['max_per_person']) ? (int)$tt['max_per_person'] : '' ?>" min="1" placeholder="Unlimited">
                                    </label>
                                    <div class="tier-remove-wrap">
                                        <?php if ($taken == 0): ?>
                                            <button type="button" class="remove-ticket-row-btn ticket-remove-btn" title="Remove ticket tier">
                                                <span class="remove-icon">✕</span>
                                                <span class="remove-text">Remove Tier</span>
                                            </button>
                                        <?php else: ?>
                                            <button type="button" class="remove-ticket-row-btn ticket-remove-btn" disabled title="Cannot delete tier with sold or issued tickets">
                                                <span class="remove-icon">✕</span>
                                                <span class="remove-text">Locked (<?= number_format($taken) ?> Sold)</span>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <?php if ($taken > 0): ?>
                                    <div class="tier-stats-bar">
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
                            <p class="event-section-subtitle">Configure fee absorption and standalone visual flyer landing page.</p>
                        </div>
                    </div>
                </div>

                <!-- Fee Settings -->
                <div class="setting-card">
                    <label class="setting-card-label">
                        <input type="checkbox" id="pass-fee" name="pass_fee" value="1" <?= !empty($event->pass_fee) ? 'checked' : '' ?> class="setting-checkbox">
                        <div>
                            <strong style="color:rgb(var(--text)); font-size:0.95rem; display:block;">
                                Pass <?= e(get_setting('platform_fee', 5)) ?>% platform fee to attendees
                            </strong>
                            <small class="event-help-text" style="display:block; margin-top:0.25rem;">
                                <strong>Checked:</strong> Attendees pay the <?= e(get_setting('platform_fee', 5)) ?>% ticketing fee on checkout. <br>
                                <strong>Unchecked:</strong> You absorb the fee, which is deducted before payout on ticket sales.
                            </small>
                        </div>
                    </label>
                </div>

                <!-- Dedicated Flyer Page -->
                <div class="setting-card setting-card--flyer">
                    <div style="display: flex; align-items: flex-start; gap: 0.85rem;">
                        <input type="hidden" name="flyer_page_enabled" value="0">
                        <input type="checkbox" id="flyer-page-enabled" name="flyer_page_enabled" value="1" <?= !empty(old('flyer_page_enabled', $event->flyer_page_enabled ?? 0)) ? 'checked' : '' ?> class="setting-checkbox">
                        <label for="flyer-page-enabled" style="cursor: pointer; user-select: none; flex: 1; margin: 0;">
                            <strong style="color:#6366f1; font-size:0.95rem; display:flex; align-items:center; gap:0.35rem;">
                                <span>🎨</span> Dedicated flyer landing page enabled (<code><?= e(event_flyer_url($event)) ?></code>)
                            </strong>
                            <small class="event-help-text" style="display:block; margin-top:0.25rem;">
                                Provides an immersive artwork-background ticket booth with a short link. Perfect for linking directly in Instagram bio, TikTok, and WhatsApp broadcasts.
                            </small>
                        </label>
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
