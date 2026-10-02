<?php

/**
 * Host Edit Event View
 *
 * This file contains the HTML markup for editing an existing host event.
 *
 * @package Evently
 * @subpackage Views/Host
 * @author Your Name <Celio Natti>
 * @license MIT License
 * @version 1.0.0
 */

?>

@extends('layouts/host')

@section('title')
Edit Event - <?= e($event->name) ?>
@endsection

@section('content')
<section class="page-hero">
    <div>
        <a href="{{ route('host/event/management/' . $event->id) }}" class="back-link" style="color:var(--brand); text-decoration:none; font-size:0.9rem; font-weight:600; display:inline-block; margin-bottom:0.5rem;">← Back to Event Management</a>
        <?php if (!empty($isAdmin) && !empty($organizerName)): ?>
            <div style="display:inline-flex; align-items:center; gap:0.5rem; background:rgba(99,102,241,0.06); border:1px solid rgba(99,102,241,0.18); border-radius:10px; padding:0.45rem 0.85rem; margin-bottom:0.65rem;">
                <span style="font-size:0.95rem;">🛡️</span>
                <span style="font-size:0.82rem; font-weight:700; color:#6366f1;">Admin View</span>
                <span style="font-size:0.82rem; opacity:0.5;">·</span>
                <span style="font-size:0.82rem; font-weight:600;">Event by <?= e($organizerName) ?></span>
                <span style="font-size:0.82rem; opacity:0.5;">·</span>
                <span style="font-size:0.82rem; opacity:0.65;"><?= e($organizerEmail) ?></span>
            </div>
        <?php endif; ?>
        <span class="panel-badge">Host dashboard</span>
        <h1>Edit Event: <?= e($event->name) ?></h1>
        <p class="lede">Update your event details, schedule, venue, or ticket packages.</p>
    </div>
</section>

<section class="create-event-page">
    <div class="form-card">
        <h2>Edit event details</h2>
        <div class="auth-message" aria-live="polite" style="display: none;"></div>

        <?php $errorModel = $model ?? $event ?? null; ?>
        <?php if (isset($errorModel) && $errorModel->hasErrors()): ?>
            <div class="alert alert-danger" style="margin-bottom: 1.25rem; border-radius: 14px; padding: 1rem 1.25rem; background: rgba(239, 68, 68, 0.12); border: 1px solid rgba(239, 68, 68, 0.3); color: #ef4444;">
                <h4 style="margin: 0 0 0.5rem; font-size: 0.95rem; font-weight: 700; display: flex; align-items: center; gap: 0.4rem;">
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

            <label>Event name
                <input type="text" id="event-name" name="event_name" required value="<?= e($event->name) ?>" placeholder="Summer Beats Festival">
            </label>

            <label>Event Cover Image
                <?php if (!empty($event->image)): ?>
                    <div id="current-image-wrapper" style="margin: 0.5rem 0; display:flex; align-items:center; gap:1rem;">
                        <img src="<?= e($event->image) ?>" alt="Event Cover" style="width: 120px; height: 70px; object-fit: cover; border-radius: 8px; border: 1px solid rgba(var(--muted), 0.2);">
                        <span style="font-size: 0.8rem; color: rgb(var(--muted));">Current Cover Image</span>
                    </div>
                <?php endif; ?>
                <input type="file" id="event-image" name="event_image" accept="image/jpeg,image/png,image/webp,image/gif" style="padding: 0.5rem; font-size: 0.9rem;">
                <div id="image-preview-container" class="hidden" style="margin-top: 0.75rem; position: relative; display: inline-block;">
                    <img id="image-preview" src="" alt="Event Image Preview" style="max-width: 100%; max-height: 220px; border-radius: 12px; border: 1px solid rgba(var(--muted), 0.2); object-fit: cover; display: block;">
                    <button type="button" id="remove-image-btn" style="position: absolute; top: 8px; right: 8px; background: rgba(0,0,0,0.75); color: #fff; border: none; border-radius: 50%; width: 28px; height: 28px; cursor: pointer; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 14px;" title="Remove image">✕</button>
                </div>
                <small class="muted" style="display:block; margin-top: 4px; color:rgb(var(--muted)); font-size:0.8rem;">
                    Upload a new cover image to update (JPG, PNG, WEBP, GIF. Max: 5MB). Leave empty to keep current image.
                </small>
            </label>
            
            <label>Custom URL (optional)
                <div style="display:flex;gap:.5rem;align-items:center;">
                    <span style="white-space:nowrap;color:rgb(var(--muted));">/e/</span>
                    <input type="text" id="custom-url" name="custom_url" value="<?= e($event->custom_url ?? '') ?>" placeholder="summer-beats-festival"
                        aria-label="Custom URL slug">
                </div>
                <small class="muted" style="margin-bottom: 8px; color:var(--text-muted);">Short link for your event page.</small>
            </label>
            
            <div class="field-grid">
                <label for="event-date">Event date
                    <div class="picker-wrapper">
                        <svg class="picker-icon" viewBox="0 0 24 24" width="18" height="18" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
                            <line x1="16" y1="2" x2="16" y2="6" />
                            <line x1="8" y1="2" x2="8" y2="6" />
                            <line x1="3" y1="10" x2="21" y2="10" />
                        </svg>
                        <input type="text" id="event-date" name="event_date" required value="<?= e($eventDate ?? '') ?>" placeholder="Select a date">
                    </div>
                </label>
                <label for="event-time">Start time
                    <div class="picker-wrapper">
                        <svg class="picker-icon" viewBox="0 0 24 24" width="18" height="18" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10" />
                            <polyline points="12 6 12 12 16 14" />
                        </svg>
                        <input type="text" id="event-time" name="event_time" required value="<?= e($eventTime ?? '') ?>" placeholder="Select a time">
                    </div>
                </label>
            </div>

            <div class="field-grid">
                <label for="event-category">Event Category
                    <select id="event-category" name="event_category" required style="width:100%; padding:0.75rem 2.6rem 0.75rem 1rem; border-radius:12px; border:1px solid rgba(var(--muted), 0.2); background-color:rgb(var(--card)); color:rgb(var(--text)); font-size:0.95rem;">
                        <?php foreach (($categories ?? []) as $cat): ?>
                            <option value="<?= e($cat) ?>" <?= ($event->category ?? 'Concerts') === $cat ? 'selected' : '' ?>><?= e($cat) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>Venue
                    <input type="text" id="event-venue" name="event_venue" required value="<?= e($eventVenue ?? '') ?>" placeholder="Central Park">
                </label>
                <label>City
                    <input type="text" id="event-city" name="event_city" required value="<?= e($eventCity ?? '') ?>" placeholder="New York">
                </label>
            </div>

            <label>Description
                <textarea id="event-description" name="event_description" rows="5" required
                    placeholder="Share what makes this event special."><?= e($event->description) ?></textarea>
            </label>

            <!-- Special Guests Section -->
            <div class="guest-section">
                <div class="ticket-header" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem;">
                    <h3>Special guests</h3>
                    <button type="button" id="add-guest-btn" class="checkout-btn secondary" style="width:auto; padding:0.5rem 1rem;">Add guest</button>
                </div>
                <div id="guest-list" class="guest-list scrollable-list" style="display:flex; flex-direction:column; gap:0.75rem;">
                    <?php if (!empty($eventGuests)): ?>
                        <?php foreach ($eventGuests as $guest): ?>
                            <div class="guest-row">
                                <div class="guest-row-grid">
                                    <label>
                                        <span>Name</span>
                                        <input type="text" name="guest-name[]" value="<?= e($guest['name']) ?>" placeholder="Artist / Speaker Name" required>
                                    </label>
                                    <label>
                                        <span>Role</span>
                                        <input type="text" name="guest-role[]" value="<?= e($guest['role'] ?? '') ?>" placeholder="Performer / Keynote">
                                    </label>
                                    <label>
                                        <span>Phone</span>
                                        <input type="tel" name="guest-phone[]" value="<?= e($guest['phone'] ?? '') ?>" placeholder="+1 555 555 5555">
                                    </label>
                                    <label>
                                        <span>Social</span>
                                        <input type="text" name="guest-social[]" value="<?= e($guest['social'] ?? '') ?>" placeholder="@handle or https://...">
                                    </label>
                                </div>
                                <div style="display:flex; justify-content:flex-end; margin-top:0.5rem;">
                                    <button type="button" class="guest-remove" style="background:#ef4444; color:#fff; border:none; border-radius:8px; padding:0.45rem 0.9rem; cursor:pointer; font-weight:600; font-size:0.82rem;">Remove Guest</button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Ticket Tiers Section -->
            <div class="ticket-section">
                <div class="ticket-header" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem;">
                    <h3>Ticket types</h3>
                    <button type="button" id="add-ticket-btn" class="checkout-btn secondary" style="width:auto; padding:0.5rem 1rem;">+ Add Ticket Tier</button>
                </div>
                <div id="ticket-list" class="ticket-list scrollable-list" style="display:flex; flex-direction:column; gap:0.75rem;">
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
                                    <label class="tier-name-label">
                                        <span>Tier Name</span>
                                        <input type="text" name="ticket-name[]" required value="<?= e($tt['name']) ?>" placeholder="Tier Name (e.g. VIP, General Admission)">
                                    </label>
                                    <label class="tier-price-label">
                                        <span>Price (<?= e(get_currency_symbol()) ?>)</span>
                                        <input type="number" step="0.01" name="ticket-price[]" required value="<?= (float)$tt['price'] ?>" placeholder="Price">
                                        <?php if ($taken > 0): ?>
                                            <small style="display:block; margin-top:4px; font-size:0.75rem; color:#d97706; font-weight:600;">
                                                ℹ️ Future sales only. <?= number_format($taken) ?> sold ticket(s) keep original price.
                                            </small>
                                        <?php endif; ?>
                                    </label>
                                    <label class="tier-qty-label">
                                        <span>Total Capacity</span>
                                        <input type="number" name="ticket-qty[]" required value="<?= (int)$tt['capacity'] ?>" min="<?= $taken ?>" placeholder="Quantity">
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
                                                <span class="remove-text">Cannot Remove (Tickets Sold)</span>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <?php if ($taken > 0): ?>
                                    <div style="display:flex; justify-content:space-between; align-items:center; margin-top:0.5rem; padding-top:0.4rem; border-top:1px dashed rgba(var(--muted),0.18); font-size:0.78rem;">
                                        <span style="color:#10b981; font-weight:600;">
                                            🎟️ <?= number_format($taken) ?> Tickets Taken (<?= number_format($sold) ?> sold, <?= number_format($issued) ?> issued)
                                        </span>
                                        <span style="color:rgb(var(--brand)); font-weight:600;">
                                            📊 <?= number_format($remaining) ?> Remaining Available
                                        </span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Fee Settings Section -->
            <div class="fee-settings-section"
                style="margin: 1.5rem 0; padding: 1.25rem; border-radius: 16px; background: rgba(var(--muted), 0.05); border: 1px solid rgba(var(--muted), 0.12);">
                <h3 style="margin: 0 0 0.75rem; font-size: 1.1rem; font-weight: 600;">Ticketing Fee Settings</h3>
                <label style="display: flex; align-items: flex-start; gap: 0.75rem; cursor: pointer; font-weight: 500; font-size: 0.95rem; user-select: none;">
                    <input type="checkbox" id="pass-fee" name="pass_fee" value="1" <?= !empty($event->pass_fee) ? 'checked' : '' ?> style="margin-top: 0.15rem;">
                    <span>
                        Pass <?= e(get_setting('platform_fee', 5)) ?>% platform fee to attendees
                        <small class="muted" style="display: block; margin-top: 0.25rem; font-size: 0.8rem; font-weight: 400; color: rgb(var(--muted)); line-height: 1.4;">
                            <strong>Checked:</strong> Attendees pay the <?= e(get_setting('platform_fee', 5)) ?>% ticketing fee on top of their purchase. <br>
                            <strong>Unchecked:</strong> The <?= e(get_setting('platform_fee', 5)) ?>% fee is absorbed by you and charged before payout on each ticket sale, so attendees do not pay the fee.
                        </small>
                    </span>
                </label>
            </div>

            <!-- Flyer Page Settings Section -->
            <div class="flyer-settings-section"
                style="margin: 1.5rem 0; padding: 1.25rem; border-radius: 16px; background: rgba(99, 102, 241, 0.05); border: 1.5px solid rgba(99, 102, 241, 0.2);">
                <h3 style="margin: 0 0 0.75rem; font-size: 1.1rem; font-weight: 700; color: #6366f1; display:flex; align-items:center; gap:0.4rem;">
                    <span>🎨</span> Dedicated Flyer Page (Optional)
                </h3>
                <label style="display: flex; align-items: flex-start; gap: 0.75rem; cursor: pointer; font-weight: 500; font-size: 0.95rem; user-select: none;">
                    <input type="checkbox" id="flyer-page-enabled" name="flyer_page_enabled" value="1" <?= !empty($event->flyer_page_enabled) ? 'checked' : '' ?> style="margin-top: 0.15rem;">
                    <span>
                        Enable dedicated visual flyer landing page (<code><?= e(event_flyer_url($event)) ?></code>)
                        <small class="muted" style="display: block; margin-top: 0.25rem; font-size: 0.8rem; font-weight: 400; color: rgb(var(--muted)); line-height: 1.4;">
                            Provides an immersive artwork-background ticket booth with a clean, short URL. Perfect for linking directly in Instagram bio, WhatsApp broadcasts, and TikTok.
                        </small>
                    </span>
                </label>
            </div>

            <div style="display:flex; gap:1rem; margin-top:1.5rem;">
                <button type="submit" class="checkout-btn" style="flex:1;">Save Changes</button>
                <a href="{{ route('host/event/management/' . $event->id) }}" class="checkout-btn secondary" style="flex:0 0 auto; text-decoration:none; text-align:center; padding:0 1.5rem; display:inline-flex; align-items:center;">Cancel</a>
            </div>
        </form>
    </div>
</section>

@endsection
