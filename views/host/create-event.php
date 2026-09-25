<?php

/**
 * Host Create Event View
 *
 * This file contains the HTML markup for the host create event page.
 *
 * @package Evently
 * @subpackage Views/Host
 * @author Your Name <Celio Natti>
 * @license MIT License
 * @version 1.0.0
 */

$siteName = site_setting('site_title', 'Eventlyy');
?>

@extends('layouts/host')

@section('title')
Host Create Event - Create Your <?= e($siteName) ?> Event
@endsection

@section('content')
<section class="page-hero">
    <div>
        <span class="panel-badge">Host dashboard</span>
        <h1>Create your event and start selling tickets</h1>
        <p class="lede">Set up event details, add ticket packages, and launch an experience that feels polished and
            ready for attendees.</p>
    </div>
</section>

<section class="create-event-page">
    <div class="form-card">
        <h2>Create event</h2>
        
        <!-- Error / Feedback Messages -->
        <div class="auth-message" aria-live="polite" style="display: none;"></div>

        <?php if (isset($model) && $model->hasErrors()): ?>
            <div class="alert alert-danger" style="margin-bottom: 1.25rem; border-radius: 14px; padding: 1rem 1.25rem; background: rgba(239, 68, 68, 0.12); border: 1px solid rgba(239, 68, 68, 0.3); color: #ef4444;">
                <h4 style="margin: 0 0 0.5rem; font-size: 0.95rem; font-weight: 700; display: flex; align-items: center; gap: 0.4rem;">
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
            
            <label>Event name
                <input type="text" id="event-name" name="event_name" value="<?= e(old('event_name', $model->name ?? '')) ?>" required placeholder="Summer Beats Festival">
            </label>

            <label>Event Cover Image (optional)
                <input type="file" id="event-image" name="event_image" accept="image/jpeg,image/png,image/webp,image/gif" style="padding: 0.5rem; font-size: 0.9rem;">
                <div id="image-preview-container" class="hidden" style="margin-top: 0.75rem; position: relative; display: inline-block;">
                    <img id="image-preview" src="" alt="Event Image Preview" style="max-width: 100%; max-height: 220px; border-radius: 12px; border: 1px solid rgba(var(--muted), 0.2); object-fit: cover; display: block;">
                    <button type="button" id="remove-image-btn" style="position: absolute; top: 8px; right: 8px; background: rgba(0,0,0,0.75); color: #fff; border: none; border-radius: 50%; width: 28px; height: 28px; cursor: pointer; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 14px;" title="Remove image">✕</button>
                </div>
                <small class="muted" style="display:block; margin-top: 4px; color:rgb(var(--muted)); font-size:0.8rem;">
                    Upload a banner/cover image for your event page. Allowed formats: JPG, PNG, WEBP, GIF (Max size: 5MB).
                </small>
            </label>

            <label>Custom URL (optional)
                <div style="display:flex;gap:.5rem;align-items:center;">
                    <span style="white-space:nowrap;color:rgb(var(--muted));">/events/</span>
                    <input type="text" id="custom-url" name="custom_url" value="<?= e(old('custom_url', $model->custom_url ?? '')) ?>" placeholder="summer-beats-festival"
                        aria-label="Custom URL slug">
                </div>
                <small class="muted" style="margin-bottom: 8px; color:var(--text-muted);">Starts with the event title; left empty to auto-generate.</small>
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
                        <input type="text" id="event-date" name="event_date" value="<?= e(old('event_date', '')) ?>" required placeholder="Select a date"
                            readonly>
                    </div>
                </label>
                <label for="event-time">Start time
                    <div class="picker-wrapper">
                        <svg class="picker-icon" viewBox="0 0 24 24" width="18" height="18" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10" />
                            <polyline points="12 6 12 12 16 14" />
                        </svg>
                        <input type="text" id="event-time" name="event_time" value="<?= e(old('event_time', '')) ?>" required placeholder="Select a time"
                            readonly>
                    </div>
                </label>
            </div>

            <div class="field-grid">
                <label for="event-category">Event Category
                    <select id="event-category" name="event_category" required style="width:100%; padding:0.75rem 1rem; border-radius:12px; border:1px solid rgba(var(--muted), 0.2); background:rgb(var(--card)); color:rgb(var(--text)); font-size:0.95rem;">
                        <option value="" disabled <?= empty(old('event_category', $model->category ?? '')) ? 'selected' : '' ?>>Select category</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= e($cat) ?>" <?= old('event_category', $model->category ?? '') === $cat ? 'selected' : '' ?>><?= e($cat) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>Venue
                    <input type="text" id="event-venue" name="event_venue" value="<?= e(old('event_venue', '')) ?>" required placeholder="Central Park">
                </label>
                <label>City
                    <input type="text" id="event-city" name="event_city" value="<?= e(old('event_city', '')) ?>" required placeholder="New York">
                </label>
            </div>

            <div class="contact-row">
                <label>Organizer phone
                    <input type="tel" id="organizer-phone" name="organizer_phone" value="<?= e(old('organizer_phone', '')) ?>" placeholder="+1 555 555 5555">
                </label>
                <label>Organizer website / social
                    <input type="url" id="organizer-social" name="organizer_social" value="<?= e(old('organizer_social', '')) ?>"
                        placeholder="https://... or @handle">
                </label>
            </div>

            <label>Description
                <textarea id="event-description" name="event_description" rows="4" required
                    placeholder="Share what makes this event special."><?= e(old('event_description', $model->description ?? '')) ?></textarea>
            </label>

            <div class="guest-section">
                <div class="ticket-header">
                    <h3>Special guests</h3>
                    <button type="button" id="add-guest-btn" class="checkout-btn secondary">Add guest</button>
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
                                <label>
                                    <span>Name</span>
                                    <input type="text" name="guest-name[]" value="<?= e($gName) ?>" placeholder="Artist / Speaker Name" required>
                                </label>
                                <label>
                                    <span>Role</span>
                                    <input type="text" name="guest-role[]" value="<?= e($oldGuestRoles[$idx] ?? '') ?>" placeholder="Performer / Keynote">
                                </label>
                                <label>
                                    <span>Phone</span>
                                    <input type="tel" name="guest-phone[]" value="<?= e($oldGuestPhones[$idx] ?? '') ?>" placeholder="+1 555 555 5555">
                                </label>
                                <label>
                                    <span>Social</span>
                                    <input type="text" name="guest-social[]" value="<?= e($oldGuestSocials[$idx] ?? '') ?>" placeholder="@handle or https://...">
                                </label>
                            </div>
                            <div style="display:flex; justify-content:flex-end; margin-top:0.5rem;">
                                <button type="button" class="guest-remove">Remove Guest</button>
                            </div>
                        </div>
                    <?php 
                        endforeach;
                    endif; 
                    ?>
                </div>
            </div>

            <div class="ticket-section">
                <div class="ticket-header">
                    <h3>Ticket types</h3>
                    <button type="button" id="add-ticket-btn" class="checkout-btn secondary">Add ticket</button>
                </div>
                <div id="ticket-list" class="ticket-list scrollable-list">
                    <?php 
                    $oldTicketNames = old('ticket-name', []);
                    $oldTicketPrices = old('ticket-price', []);
                    $oldTicketQtys = old('ticket-qty', []);
                    if (is_array($oldTicketNames) && count($oldTicketNames) > 0):
                        foreach ($oldTicketNames as $idx => $tName):
                            if (empty($tName)) continue;
                    ?>
                        <div class="ticket-row-item">
                            <input type="hidden" name="ticket-id[]" value="0">
                            <div class="ticket-tier-grid">
                                <label class="tier-name-label">
                                    <span>Tier Name</span>
                                    <input type="text" name="ticket-name[]" value="<?= e($tName) ?>" required placeholder="Tier Name (e.g. VIP, General Admission)">
                                </label>
                                <label class="tier-price-label">
                                    <span>Price</span>
                                    <input type="number" step="0.01" name="ticket-price[]" value="<?= e($oldTicketPrices[$idx] ?? '0.00') ?>" required placeholder="0.00" min="0">
                                </label>
                                <label class="tier-qty-label">
                                    <span>Total Capacity</span>
                                    <input type="number" name="ticket-qty[]" value="<?= e($oldTicketQtys[$idx] ?? '100') ?>" required placeholder="Quantity" min="1">
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

            <!-- Organization Staffing Assistance Request Section -->
            <div class="staffing-request-section" style="margin: 1.5rem 0; padding: 1.35rem 1.5rem; border-radius: 16px; background: rgba(var(--muted), 0.05); border: 1px solid rgba(var(--muted), 0.12);">
                <div style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:1rem; margin-bottom:1rem;">
                    <div>
                        <div style="display:flex; align-items:center; gap:0.5rem;">
                            <span style="font-size:1.3rem;">🤝</span>
                            <h3 style="margin:0; font-size:1.15rem; font-weight:700;">Organization Staffing Support (Optional)</h3>
                        </div>
                        <p style="margin:0.25rem 0 0; font-size:0.85rem; color:rgb(var(--muted)); line-height:1.4;">
                            Need help running your event? Request trained organization staff &amp; coordinators to be assigned to your event by the platform administration.
                        </p>
                    </div>
                    <label style="display:flex; align-items:center; gap:0.5rem; cursor:pointer; font-weight:700; font-size:0.92rem; user-select:none; background:rgba(var(--brand),0.08); padding:0.4rem 0.85rem; border-radius:99px; border:1px solid rgba(var(--brand),0.2);">
                        <input type="checkbox" id="request-staffing-toggle" name="request_staffing" value="1" <?= old('request_staffing') ? 'checked' : '' ?> onchange="toggleStaffingOptions(this.checked)">
                        <span style="color:rgb(var(--brand));">Request Event Staff</span>
                    </label>
                </div>

                <div id="staffing-options-container" style="display: <?= old('request_staffing') ? 'block' : 'none' ?>; margin-top: 1.25rem; border-top: 1px dashed rgba(var(--muted), 0.2); padding-top: 1.25rem;">
                    <p style="margin:0 0 1rem 0; font-size:0.88rem; font-weight:600; color:rgb(var(--text));">
                        Select the specialized roles and quantities needed for your event:
                    </p>

                    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(280px, 1fr)); gap:1rem; margin-bottom:1.25rem;">
                        <!-- Role 1: Ticket Scanner -->
                        <div class="staff-role-card" style="background:var(--surface); border:1px solid rgba(var(--muted),0.15); border-radius:14px; padding:1rem 1.15rem;">
                            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.5rem;">
                                <label style="display:flex; align-items:center; gap:0.5rem; font-weight:700; font-size:0.95rem; cursor:pointer;">
                                    <input type="checkbox" name="staff_roles[ticket-scanner][selected]" value="1" <?= old('staff_roles.ticket-scanner.selected') ? 'checked' : '' ?> onchange="updateStaffSummary()">
                                    <span>🎫 Ticket Scanner</span>
                                </label>
                                <div style="display:flex; align-items:center; gap:0.35rem;">
                                    <span style="font-size:0.75rem; color:rgb(var(--muted));">Qty:</span>
                                    <input type="number" name="staff_roles[ticket-scanner][qty]" value="<?= old('staff_roles.ticket-scanner.qty', '1') ?>" min="1" max="20" style="width:52px; padding:0.25rem 0.4rem; border-radius:8px; border:1px solid rgba(var(--muted),0.25); background:var(--bg); text-align:center; font-weight:700;" onchange="updateStaffSummary()">
                                </div>
                            </div>
                            <p style="margin:0; font-size:0.8rem; color:rgb(var(--muted)); line-height:1.4;">
                                <strong>Role &amp; Function:</strong> Operates gate scanner app, validates QR ticket codes, manages check-in queues, and prevents duplicate entries.
                            </p>
                        </div>

                        <!-- Role 2: Organizer Staff / Usher -->
                        <div class="staff-role-card" style="background:var(--surface); border:1px solid rgba(var(--muted),0.15); border-radius:14px; padding:1rem 1.15rem;">
                            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.5rem;">
                                <label style="display:flex; align-items:center; gap:0.5rem; font-weight:700; font-size:0.95rem; cursor:pointer;">
                                    <input type="checkbox" name="staff_roles[organizer-staff][selected]" value="1" <?= old('staff_roles.organizer-staff.selected') ? 'checked' : '' ?> onchange="updateStaffSummary()">
                                    <span>🏷️ Organizer Staff / Usher</span>
                                </label>
                                <div style="display:flex; align-items:center; gap:0.35rem;">
                                    <span style="font-size:0.75rem; color:rgb(var(--muted));">Qty:</span>
                                    <input type="number" name="staff_roles[organizer-staff][qty]" value="<?= old('staff_roles.organizer-staff.qty', '1') ?>" min="1" max="20" style="width:52px; padding:0.25rem 0.4rem; border-radius:8px; border:1px solid rgba(var(--muted),0.25); background:var(--bg); text-align:center; font-weight:700;" onchange="updateStaffSummary()">
                                </div>
                            </div>
                            <p style="margin:0; font-size:0.8rem; color:rgb(var(--muted)); line-height:1.4;">
                                <strong>Role &amp; Function:</strong> Floor coordination, attendee direction, VIP seating escort, wristband issuance, and crowd management.
                            </p>
                        </div>

                        <!-- Role 3: Customer Support -->
                        <div class="staff-role-card" style="background:var(--surface); border:1px solid rgba(var(--muted),0.15); border-radius:14px; padding:1rem 1.15rem;">
                            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.5rem;">
                                <label style="display:flex; align-items:center; gap:0.5rem; font-weight:700; font-size:0.95rem; cursor:pointer;">
                                    <input type="checkbox" name="staff_roles[customer-support][selected]" value="1" <?= old('staff_roles.customer-support.selected') ? 'checked' : '' ?> onchange="updateStaffSummary()">
                                    <span>🎧 Customer Support Lead</span>
                                </label>
                                <div style="display:flex; align-items:center; gap:0.35rem;">
                                    <span style="font-size:0.75rem; color:rgb(var(--muted));">Qty:</span>
                                    <input type="number" name="staff_roles[customer-support][qty]" value="<?= old('staff_roles.customer-support.qty', '1') ?>" min="1" max="10" style="width:52px; padding:0.25rem 0.4rem; border-radius:8px; border:1px solid rgba(var(--muted),0.25); background:var(--bg); text-align:center; font-weight:700;" onchange="updateStaffSummary()">
                                </div>
                            </div>
                            <p style="margin:0; font-size:0.8rem; color:rgb(var(--muted)); line-height:1.4;">
                                <strong>Role &amp; Function:</strong> Front-of-house help desk, attendee inquiries, ticket email lookup, name change queries, and lost &amp; found.
                            </p>
                        </div>

                        <!-- Role 4: Technical Operations -->
                        <div class="staff-role-card" style="background:var(--surface); border:1px solid rgba(var(--muted),0.15); border-radius:14px; padding:1rem 1.15rem;">
                            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.5rem;">
                                <label style="display:flex; align-items:center; gap:0.5rem; font-weight:700; font-size:0.95rem; cursor:pointer;">
                                    <input type="checkbox" name="staff_roles[technical-ops][selected]" value="1" <?= old('staff_roles.technical-ops.selected') ? 'checked' : '' ?> onchange="updateStaffSummary()">
                                    <span>⚙️ Technical Operations</span>
                                </label>
                                <div style="display:flex; align-items:center; gap:0.35rem;">
                                    <span style="font-size:0.75rem; color:rgb(var(--muted));">Qty:</span>
                                    <input type="number" name="staff_roles[technical-ops][qty]" value="<?= old('staff_roles.technical-ops.qty', '1') ?>" min="1" max="10" style="width:52px; padding:0.25rem 0.4rem; border-radius:8px; border:1px solid rgba(var(--muted),0.25); background:var(--bg); text-align:center; font-weight:700;" onchange="updateStaffSummary()">
                                </div>
                            </div>
                            <p style="margin:0; font-size:0.8rem; color:rgb(var(--muted)); line-height:1.4;">
                                <strong>Role &amp; Function:</strong> Scanner device provisioning, WiFi/offline network sync reliability, entrance power stations, and stage AV liaison.
                            </p>
                        </div>

                        <!-- Role 5: Finance & Box Office -->
                        <div class="staff-role-card" style="background:var(--surface); border:1px solid rgba(var(--muted),0.15); border-radius:14px; padding:1rem 1.15rem;">
                            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.5rem;">
                                <label style="display:flex; align-items:center; gap:0.5rem; font-weight:700; font-size:0.95rem; cursor:pointer;">
                                    <input type="checkbox" name="staff_roles[finance-manager][selected]" value="1" <?= old('staff_roles.finance-manager.selected') ? 'checked' : '' ?> onchange="updateStaffSummary()">
                                    <span>💼 Finance &amp; Box Office</span>
                                </label>
                                <div style="display:flex; align-items:center; gap:0.35rem;">
                                    <span style="font-size:0.75rem; color:rgb(var(--muted));">Qty:</span>
                                    <input type="number" name="staff_roles[finance-manager][qty]" value="<?= old('staff_roles.finance-manager.qty', '1') ?>" min="1" max="5" style="width:52px; padding:0.25rem 0.4rem; border-radius:8px; border:1px solid rgba(var(--muted),0.25); background:var(--bg); text-align:center; font-weight:700;" onchange="updateStaffSummary()">
                                </div>
                            </div>
                            <p style="margin:0; font-size:0.8rem; color:rgb(var(--muted)); line-height:1.4;">
                                <strong>Role &amp; Function:</strong> On-site door ticket cash &amp; POS payments, sales receipts, gate financial reconciliation, and refund validation.
                            </p>
                        </div>
                    </div>

                    <!-- Host Instructions / Notes for Admin -->
                    <label style="display:block; margin-bottom:1rem;">
                        <span style="display:block; font-size:0.85rem; font-weight:700; margin-bottom:0.35rem; color:rgb(var(--text));">Special Instructions / Requirements for Team Staff:</span>
                        <textarea name="staff_notes" rows="2" placeholder="e.g. Staff should report by 4:00 PM for briefing at Gate 2. Dress code: Black polo shirt." style="width:100%; border-radius:12px; border:1px solid rgba(var(--muted),0.2); background:var(--bg); padding:0.65rem 0.85rem; box-sizing:border-box; font-size:0.88rem; color:rgb(var(--text)); font-family:inherit;"><?= e(old('staff_notes', '')) ?></textarea>
                    </label>

                    <!-- Dynamic Staffing Summary Box -->
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

            <div class="fee-settings-section"
                style="margin: 1.5rem 0; padding: 1.25rem; border-radius: 16px; background: rgba(var(--muted), 0.05); border: 1px solid rgba(var(--muted), 0.12);">
                <h3 style="margin: 0 0 0.75rem; font-size: 1.1rem; font-weight: 600;">Ticketing Fee Settings</h3>
                <label
                    style="display: flex; align-items: flex-start; gap: 0.75rem; cursor: pointer; font-weight: 500; font-size: 0.95rem; user-select: none;">
                    <input type="checkbox" id="pass-fee" name="pass_fee" value="1" <?= old('pass_fee', '1') ? 'checked' : '' ?> style="margin-top: 0.15rem;">
                    <span>
                        Pass <?= e(get_setting('platform_fee', 5)) ?>% platform fee to attendees
                        <small class="muted"
                            style="display: block; margin-top: 0.25rem; font-size: 0.8rem; font-weight: 400; color: rgb(var(--muted)); line-height: 1.4;">
                            <strong>Checked:</strong> Attendees pay the <?= e(get_setting('platform_fee', 5)) ?>% ticketing fee on top of their purchase (recommended). <br>
                            <strong>Unchecked:</strong> The <?= e(get_setting('platform_fee', 5)) ?>% fee is absorbed by you and charged before payout on each ticket sale, so attendees do not pay the fee.
                        </small>
                    </span>
                </label>
            </div>

            <div class="flyer-settings-section"
                style="margin: 1.5rem 0; padding: 1.25rem; border-radius: 16px; background: rgba(99, 102, 241, 0.05); border: 1.5px solid rgba(99, 102, 241, 0.2);">
                <h3 style="margin: 0 0 0.75rem; font-size: 1.1rem; font-weight: 700; color: #6366f1; display:flex; align-items:center; gap:0.4rem;">
                    <span>🎨</span> Dedicated Flyer Page (Optional)
                </h3>
                <label
                    style="display: flex; align-items: flex-start; gap: 0.75rem; cursor: pointer; font-weight: 500; font-size: 0.95rem; user-select: none;">
                    <input type="checkbox" id="flyer-page-enabled" name="flyer_page_enabled" value="1" <?= old('flyer_page_enabled', '1') ? 'checked' : '' ?> style="margin-top: 0.15rem;">
                    <span>
                        Enable dedicated visual flyer landing page (<code>/f/your-event-slug</code>)
                        <small class="muted"
                            style="display: block; margin-top: 0.25rem; font-size: 0.8rem; font-weight: 400; color: rgb(var(--muted)); line-height: 1.4;">
                            Provides an immersive artwork-background ticket booth with a clean, short URL. Perfect for linking directly in Instagram bio, WhatsApp broadcasts, and TikTok. You can turn this on or off anytime from your Event Management dashboard.
                        </small>
                    </span>
                </label>
            </div>

            <button type="submit" class="checkout-btn" style="width: 100%; height: 48px; font-size: 1rem; border-radius: 12px; border: none; cursor: pointer;">Publish event</button>
        </form>
    </div>
</section>

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