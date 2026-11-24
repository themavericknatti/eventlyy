<?php

/**
 * Host Event Tickets, Seating & Lineup Studio
 *
 * Dedicated Step 2 workspace for configuring ticket packages, duo/couples passes,
 * hall & stadium seating arrangements (sections, rows, seats), performer lineup, and merchandise.
 *
 * @package Eventlyy
 * @subpackage Views/Host
 * @author Celio Natti
 * @version 3.0.0
 */

$siteName = site_setting('site_title', 'Eventlyy');
$currency = get_currency_symbol();
$publicUrl = url('event/' . (!empty($event->custom_url) ? $event->custom_url : ($event->slug ?? $event->id)));
$flyerUrl = event_flyer_url($event);
$eventImg = !empty($event->image) ? $event->image : 'https://images.unsplash.com/photo-1506157786151-b8491531f063?w=800&q=80&auto=format&fit=crop';
$formattedDate = !empty($event->date) ? date('M d, Y • g:i A', strtotime($event->date)) : 'Date TBA';
?>

@extends('layouts/host')

@section('title')
Tickets &amp; Seating Studio — <?= e($event->name) ?>
@endsection

@section('content')
<div class="tickets-studio-wrapper">

    <!-- ═══ 1. Context Topbar & Navigation ═══ -->
    <div class="studio-topbar">
        <div class="studio-topbar-left">
            <a href="{{ route('host/event/management/' . $event->id) }}" class="studio-back-link">
                <span class="icon">←</span> Back to Event Management
            </a>
            <div class="studio-breadcrumb-sub">
                <span class="studio-event-title-badge"><?= e($event->name) ?></span>
                <span class="studio-event-date"><?= $formattedDate ?></span>
            </div>
        </div>

        <div class="studio-topbar-right">
            <a href="<?= e($publicUrl) ?>" target="_blank" class="studio-action-btn studio-btn-outline" title="Preview public event page">
                <span>👁️</span> Public View
            </a>
            <a href="{{ route('host/event/edit/' . $event->id) }}" class="studio-action-btn studio-btn-outline" title="Edit event title, dates, or venue">
                <span>✏️</span> Edit Essentials
            </a>
            <a href="{{ route('host/event/management/' . $event->id) }}" class="studio-action-btn studio-btn-finish">
                <span>✓</span> Finished Setup
            </a>
        </div>
    </div>

    <!-- ═══ 2. Flow Progress Stepper ═══ -->
    <div class="studio-stepper-card">
        <div class="studio-stepper-track">
            <div class="studio-step-node studio-step-node--done">
                <div class="studio-step-icon">✓</div>
                <div class="studio-step-meta">
                    <span class="studio-step-num">Step 1</span>
                    <strong class="studio-step-title">Event Essentials</strong>
                    <span class="studio-step-sub">Details, Venue &amp; Poster</span>
                </div>
            </div>
            <div class="studio-step-divider studio-step-divider--active"></div>
            <div class="studio-step-node studio-step-node--active">
                <div class="studio-step-icon">2</div>
                <div class="studio-step-meta">
                    <span class="studio-step-num">Step 2 (Active Studio)</span>
                    <strong class="studio-step-title">Tickets, Seating &amp; Lineup</strong>
                    <span class="studio-step-sub">Duo, Sections, Rows &amp; Guests</span>
                </div>
            </div>
            <div class="studio-step-divider"></div>
            <div class="studio-step-node studio-step-node--next">
                <div class="studio-step-icon">3</div>
                <div class="studio-step-meta">
                    <span class="studio-step-num">Step 3</span>
                    <strong class="studio-step-title">Ready for Attendees</strong>
                    <span class="studio-step-sub">Live Checkout &amp; QR Passes</span>
                </div>
            </div>
        </div>
    </div>

    <!-- ═══ 3. Main Form Container ═══ -->
    <form action="{{ route('host/event/' . $event->id . '/tickets-setup') }}" method="POST" enctype="multipart/form-data" id="studio-main-form">
        <?= csrf_field() ?>
        <input type="hidden" name="save_action" id="studio-save-action" value="save">

        <!-- ── Section A: Ticket Packages & Seating Studio ── -->
        <div class="studio-section-card">
            <div class="studio-section-header">
                <div class="studio-section-header-info">
                    <div class="studio-section-icon-badge">🎟️</div>
                    <div>
                        <h2 class="studio-section-heading">Ticket Packages &amp; Seating Allocations</h2>
                        <p class="studio-section-subheading">
                            Create flexible tickets: standard single admissions, couple/duo passes with mandatory plus-one details, group tables, or hall/stadium seated sections with specific rows and seat numbers.
                        </p>
                    </div>
                </div>
                <button type="button" class="studio-btn-add" id="studio-add-tier-btn">
                    <span>➕</span> Add Ticket Package
                </button>
            </div>

            <!-- Tiers Container -->
            <div id="studio-tiers-container" class="studio-tiers-list">
                <?php if (empty($ticketTypes)): ?>
                    <div class="studio-empty-state" id="studio-tiers-empty">
                        <span class="studio-empty-icon">🎟️</span>
                        <h3>No ticket packages created yet</h3>
                        <p>Click "Add Ticket Package" above to set up single tickets, couple/duo passes, or stadium seating rows.</p>
                        <button type="button" class="studio-btn-primary" onclick="addTicketTier('standard')">
                            <span>✨</span> Create First Ticket Package
                        </button>
                    </div>
                <?php else: ?>
                    <?php 
                    $tCount = 0;
                    foreach ($ticketTypes as $tt):
                        $tCount++;
                        $tierId = (int)$tt['id'];
                        $kind = $tt['ticket_kind'] ?? 'standard';
                        $sold = (int)($tt['sold'] ?? 0);
                        $capacity = (int)($tt['capacity'] ?? 0);
                        $admits = max(1, (int)($tt['admits'] ?? 1));
                        $hasSeating = !empty($tt['has_seating']);
                        $sectionName = $tt['section_name'] ?? '';
                        $rowName = $tt['row_name'] ?? '';
                        $seatPrefix = $tt['seat_prefix'] ?? '';
                        $seatStart = $tt['seat_start'] ?? '';
                        $seatEnd = $tt['seat_end'] ?? '';
                        $maxPerPerson = $tt['max_per_person'] ?? '';
                        $desc = $tt['description'] ?? '';
                    ?>
                        <div class="studio-tier-card" data-tier-card>
                            <input type="hidden" name="ticket-id[]" value="<?= $tierId ?>">
                            
                            <div class="studio-tier-card-header">
                                <div class="studio-tier-badge-row">
                                    <span class="studio-tier-counter">Package #<?= $tCount ?></span>
                                    <span class="studio-kind-chip studio-kind-chip--<?= e($kind) ?>" data-kind-badge>
                                        <?php if ($kind === 'duo'): ?>
                                            💑 Couples / Duo
                                        <?php elseif ($kind === 'group'): ?>
                                            👥 Group / Table
                                        <?php elseif ($kind === 'seated'): ?>
                                            💺 Hall / Stadium Seated
                                        <?php else: ?>
                                            🎟️ Standard Single
                                        <?php endif; ?>
                                    </span>
                                    <?php if ($sold > 0): ?>
                                        <span class="studio-sold-chip">🔥 <?= $sold ?> Sold</span>
                                    <?php endif; ?>
                                </div>
                                <button type="button" class="studio-tier-remove-btn" onclick="removeTierCard(this)" title="Remove package" <?= $sold > 0 ? 'disabled title="Cannot remove package with sold tickets"' : '' ?>>
                                    <span>✕</span> Remove
                                </button>
                            </div>

                            <!-- Ticket Kind Selector -->
                            <div class="studio-kind-selector-wrap">
                                <label class="studio-label">Ticket Package Category</label>
                                <div class="studio-kind-grid">
                                    <label class="studio-kind-opt <?= $kind === 'standard' ? 'selected' : '' ?>">
                                        <input type="radio" name="tier_kind_selector_<?= $tCount ?>" value="standard" <?= $kind === 'standard' ? 'checked' : '' ?> onchange="handleKindChange(this)">
                                        <span class="studio-opt-icon">🎟️</span>
                                        <div class="studio-opt-info">
                                            <strong>Standard Single</strong>
                                            <small>1 Person entry</small>
                                        </div>
                                    </label>
                                    <label class="studio-kind-opt <?= $kind === 'duo' ? 'selected' : '' ?>">
                                        <input type="radio" name="tier_kind_selector_<?= $tCount ?>" value="duo" <?= $kind === 'duo' ? 'checked' : '' ?> onchange="handleKindChange(this)">
                                        <span class="studio-opt-icon">💑</span>
                                        <div class="studio-opt-info">
                                            <strong>Couples / Duo</strong>
                                            <small>Requires Plus-One (+1)</small>
                                        </div>
                                    </label>
                                    <label class="studio-kind-opt <?= $kind === 'group' ? 'selected' : '' ?>">
                                        <input type="radio" name="tier_kind_selector_<?= $tCount ?>" value="group" <?= $kind === 'group' ? 'checked' : '' ?> onchange="handleKindChange(this)">
                                        <span class="studio-opt-icon">👥</span>
                                        <div class="studio-opt-info">
                                            <strong>Group / Table</strong>
                                            <small>Squad or table pass</small>
                                        </div>
                                    </label>
                                    <label class="studio-kind-opt <?= $kind === 'seated' ? 'selected' : '' ?>">
                                        <input type="radio" name="tier_kind_selector_<?= $tCount ?>" value="seated" <?= $kind === 'seated' ? 'checked' : '' ?> onchange="handleKindChange(this)">
                                        <span class="studio-opt-icon">💺</span>
                                        <div class="studio-opt-info">
                                            <strong>Hall / Stadium Seated</strong>
                                            <small>Sections, rows &amp; seats</small>
                                        </div>
                                    </label>
                                </div>
                                <input type="hidden" name="ticket-kind[]" class="studio-kind-input" value="<?= e($kind) ?>">
                            </div>

                            <!-- Primary Info Grid -->
                            <div class="studio-field-grid studio-primary-grid">
                                <div class="studio-field-item studio-field-name">
                                    <label class="studio-label">Package / Tier Name <span class="req">*</span></label>
                                    <input type="text" name="ticket-name[]" class="studio-input studio-name-input" value="<?= e($tt['name']) ?>" required placeholder="e.g. VIP Front Row, Couples Date Night, Table of 5">
                                </div>
                                <div class="studio-field-item">
                                    <label class="studio-label">Price (<?= e($currency) ?>) <span class="req">*</span></label>
                                    <input type="number" step="0.01" min="0" name="ticket-price[]" class="studio-input" value="<?= e($tt['price']) ?>" required placeholder="0.00">
                                </div>
                                <div class="studio-field-item">
                                    <label class="studio-label">Total Capacity <span class="req">*</span></label>
                                    <input type="number" min="1" name="ticket-qty[]" class="studio-input studio-qty-input" value="<?= e($capacity) ?>" required placeholder="100">
                                </div>
                            </div>

                            <!-- Advanced Allocation Controls -->
                            <div class="studio-tier-shelves">
                                <!-- Duo & Group Access Controls -->
                                <div class="studio-shelf studio-shelf-access">
                                    <div class="studio-shelf-grid">
                                        <div class="studio-field-item">
                                            <div class="studio-label-with-hint">
                                                <label class="studio-label">👥 Admits (People Per Ticket)</label>
                                                <span class="studio-hint">Single = 1, Duo/Couple = 2, Table = N</span>
                                            </div>
                                            <input type="number" min="1" name="ticket-admits[]" class="studio-input studio-admits-input" value="<?= e($admits) ?>">
                                        </div>
                                        <div class="studio-field-item">
                                            <div class="studio-label-with-hint">
                                                <label class="studio-label">🔒 Max Per Customer</label>
                                                <span class="studio-hint">Leave blank for unlimited</span>
                                            </div>
                                            <input type="number" min="1" name="ticket-max-per-person[]" class="studio-input" value="<?= e($maxPerPerson) ?>" placeholder="Unlimited">
                                        </div>
                                    </div>

                                    <!-- Duo Explanatory Notice -->
                                    <div class="studio-duo-banner" style="display:<?= $kind === 'duo' ? 'flex' : 'none' ?>;">
                                        <span class="studio-banner-icon">💑</span>
                                        <div>
                                            <strong>Couples / Duo Pass Mode Active:</strong>
                                            <span>At checkout, attendees must provide both their own details and their <strong>Plus-One (+1)</strong> partner's name &amp; email. Two independent scannable QR passes will be generated automatically.</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Hall / Stadium Seating Shelf -->
                                <div class="studio-shelf studio-shelf-seating" style="display:<?= ($kind === 'seated' || $hasSeating) ? 'block' : 'none' ?>;">
                                    <div class="studio-seating-shelf-header">
                                        <span>💺</span>
                                        <strong>Hall / Stadium Seating Details</strong>
                                        <span class="studio-pill-tag">Allocated Seating</span>
                                    </div>
                                    <input type="hidden" name="ticket-has-seating[]" class="studio-has-seating-input" value="<?= $hasSeating ? '1' : '0' ?>">

                                    <div class="studio-seating-grid">
                                        <div class="studio-field-item">
                                            <label class="studio-label">Section Name</label>
                                            <input type="text" name="ticket-section[]" class="studio-input" value="<?= e($sectionName) ?>" placeholder="e.g. Main Orchestra, Gold Tier, North Stand">
                                        </div>
                                        <div class="studio-field-item">
                                            <label class="studio-label">Row Identifier</label>
                                            <input type="text" name="ticket-row[]" class="studio-input" value="<?= e($rowName) ?>" placeholder="e.g. Row A, Row 14, Front Row">
                                        </div>
                                        <div class="studio-field-item">
                                            <label class="studio-label">Seat Prefix</label>
                                            <input type="text" name="ticket-seat-prefix[]" class="studio-input" value="<?= e($seatPrefix) ?>" placeholder="e.g. Seat-, A-">
                                        </div>
                                        <div class="studio-field-item">
                                            <label class="studio-label">Start Seat #</label>
                                            <input type="number" min="1" name="ticket-seat-start[]" class="studio-input studio-seat-start" value="<?= e($seatStart ?? 1) ?>" placeholder="1" oninput="calculateSeatedCapacity(this)">
                                        </div>
                                        <div class="studio-field-item">
                                            <label class="studio-label">End Seat #</label>
                                            <input type="number" min="1" name="ticket-seat-end[]" class="studio-input studio-seat-end" value="<?= e($seatEnd) ?>" placeholder="30" oninput="calculateSeatedCapacity(this)">
                                        </div>
                                    </div>
                                    <div class="studio-seating-summary" style="display:<?= (!empty($seatStart) && !empty($seatEnd)) ? 'block' : 'none' ?>;">
                                        💡 Automatically allocates <strong class="seat-calc-count"><?= (!empty($seatStart) && !empty($seatEnd)) ? (($seatEnd - $seatStart) + 1) : 0 ?></strong> seats for this row/section.
                                    </div>
                                </div>

                                <!-- Package Description & Perks -->
                                <div class="studio-shelf studio-shelf-desc">
                                    <label class="studio-label">Perks, Admission Notes &amp; Seat View <span class="opt">(Optional)</span></label>
                                    <textarea name="ticket-description[]" class="studio-textarea" rows="2" placeholder="e.g. Includes welcome champagne, front-row unimpeded stage view, and dedicated fast-track entrance."><?= e($desc) ?></textarea>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- ── Section B: Special Guests & Performer Lineup ── -->
        <div class="studio-section-card" style="margin-top:2rem;">
            <div class="studio-section-header">
                <div class="studio-section-header-info">
                    <div class="studio-section-icon-badge">🌟</div>
                    <div>
                        <h2 class="studio-section-heading">Special Guests &amp; Performer Lineup</h2>
                        <p class="studio-section-subheading">Spotlight keynote speakers, headlining artists, guest DJs, hosts, or panelists on the event page.</p>
                    </div>
                </div>
                <button type="button" class="studio-btn-add" id="studio-add-guest-btn">
                    <span>➕</span> Add Performer / Guest
                </button>
            </div>

            <div id="studio-guests-container" class="studio-guests-list">
                <?php if (empty($eventGuests)): ?>
                    <p class="studio-empty-subtext" id="studio-guests-empty">No special guests added yet. Click "+ Add Performer / Guest" to spotlight your event lineup.</p>
                <?php else: ?>
                    <?php 
                    $gCount = 0;
                    foreach ($eventGuests as $idx => $g):
                        $gCount++;
                    ?>
                        <div class="studio-guest-card">
                            <div class="studio-guest-header">
                                <div class="studio-guest-avatar">👤</div>
                                <span class="studio-guest-title">Performer / Guest #<?= $gCount ?></span>
                                <button type="button" class="studio-guest-remove" onclick="this.closest('.studio-guest-card').remove()">✕ Remove</button>
                            </div>
                            <div class="studio-guest-grid">
                                <div class="studio-field-item">
                                    <label class="studio-label">Full Name <span class="req">*</span></label>
                                    <input type="text" name="guest-name[]" class="studio-input" value="<?= e($g['name']) ?>" required placeholder="Artist or Speaker Name">
                                </div>
                                <div class="studio-field-item">
                                    <label class="studio-label">Role / Headline Title</label>
                                    <input type="text" name="guest-role[]" class="studio-input" value="<?= e($g['role'] ?? '') ?>" placeholder="e.g. Headlining DJ, Keynote Speaker">
                                </div>
                                <div class="studio-field-item">
                                    <label class="studio-label">Contact Phone <span class="opt">(Optional)</span></label>
                                    <input type="tel" name="guest-phone[]" class="studio-input" value="<?= e($g['phone'] ?? '') ?>" placeholder="+1 555 555 5555">
                                </div>
                                <div class="studio-field-item">
                                    <label class="studio-label">Social / Website <span class="opt">(Optional)</span></label>
                                    <input type="text" name="guest-social[]" class="studio-input" value="<?= e($g['social'] ?? '') ?>" placeholder="@handle or https://...">
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- ── Section C: Add-Ons & Merchandise Upselling ── -->
        <div class="studio-section-card" style="margin-top:2rem; margin-bottom:5rem;">
            <div class="studio-section-header">
                <div class="studio-section-header-info">
                    <div class="studio-section-icon-badge">🛍️</div>
                    <div>
                        <h2 class="studio-section-heading">Merchandise &amp; Add-On Upsells <span class="opt">(Optional)</span></h2>
                        <p class="studio-section-subheading">Boost checkout revenue with event merchandise, VIP parking passes, wristbands, or beverage vouchers.</p>
                    </div>
                </div>
                <button type="button" class="studio-btn-add" id="studio-add-addon-btn">
                    <span>➕</span> Add Item
                </button>
            </div>

            <div id="studio-addons-container" class="studio-addons-list">
                <?php if (empty($eventAddons)): ?>
                    <p class="studio-empty-subtext" id="studio-addons-empty">No merchandise or add-ons configured. Click "+ Add Item" to sell merchandise or parking vouchers.</p>
                <?php else: ?>
                    <?php 
                    $aCount = 0;
                    foreach ($eventAddons as $idx => $addon):
                        $aCount++;
                    ?>
                        <div class="studio-addon-card">
                            <input type="hidden" name="addon-id[]" value="<?= (int)$addon['id'] ?>">
                            <input type="hidden" name="addon-image-existing[]" value="<?= e($addon['image'] ?? '') ?>" class="studio-addon-existing-val">
                            
                            <div class="studio-addon-header">
                                <span class="studio-addon-title">🛍️ Item #<?= $aCount ?>: <?= e($addon['name']) ?></span>
                                <button type="button" class="studio-addon-remove" onclick="this.closest('.studio-addon-card').remove()">✕ Remove</button>
                            </div>
                            <div class="studio-addon-grid">
                                <div class="studio-field-item">
                                    <label class="studio-label">Item Name <span class="req">*</span></label>
                                    <input type="text" name="addon-name[]" class="studio-input" value="<?= e($addon['name']) ?>" required placeholder="e.g. Tour T-Shirt, VIP Parking Pass">
                                </div>
                                <div class="studio-field-item">
                                    <label class="studio-label">Price (<?= e($currency) ?>) <span class="req">*</span></label>
                                    <input type="number" step="0.01" min="0" name="addon-price[]" class="studio-input" value="<?= e($addon['price']) ?>" required placeholder="0.00">
                                </div>
                                <div class="studio-field-item">
                                    <label class="studio-label">Available Inventory</label>
                                    <input type="number" min="1" name="addon-quantity[]" class="studio-input" value="<?= e($addon['quantity'] ?? '') ?>" placeholder="Unlimited">
                                </div>
                            </div>

                            <!-- Addon Media Shelf (File Upload or Image Web URL) -->
                            <div class="studio-addon-media-shelf">
                                <div class="studio-addon-thumb-box">
                                    <?php if (!empty($addon['image'])): ?>
                                        <img src="<?= e($addon['image']) ?>" alt="Preview" class="studio-addon-thumb-img">
                                    <?php else: ?>
                                        <span class="studio-addon-thumb-placeholder">🛍️</span>
                                    <?php endif; ?>
                                </div>
                                <div class="studio-addon-media-inputs">
                                    <div class="studio-addon-media-col">
                                        <label class="studio-label" style="margin-bottom:0.25rem;">
                                            Upload Photo <span class="studio-subtext">(JPG, PNG, WebP)</span>
                                        </label>
                                        <input type="file" name="addon-image[]" accept="image/jpeg,image/png,image/webp,image/gif" class="studio-file-input" onchange="previewAddonFile(this)">
                                    </div>
                                    <div class="studio-addon-media-or">OR</div>
                                    <div class="studio-addon-media-col">
                                        <label class="studio-label" style="margin-bottom:0.25rem;">
                                            Image Web URL <span class="studio-subtext">(Paste link)</span>
                                        </label>
                                        <input type="url" name="addon-image-url[]" class="studio-input" value="<?= (!empty($addon['image']) && (str_starts_with($addon['image'], 'http://') || str_starts_with($addon['image'], 'https://'))) ? e($addon['image']) : '' ?>" placeholder="https://... or paste image link" oninput="previewAddonUrl(this)">
                                    </div>
                                </div>
                            </div>

                            <div class="studio-field-item" style="margin-top:0.75rem;">
                                <label class="studio-label">Item Description &amp; Redemption Notes</label>
                                <textarea name="addon-description[]" class="studio-textarea" rows="2" placeholder="e.g. Collect at the VIP booth on arrival with your pass QR code."><?= e($addon['description'] ?? '') ?></textarea>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- ═══ 4. Floating Action Footer ═══ -->
        <div class="studio-sticky-bar">
            <div class="studio-sticky-bar-inner">
                <div class="studio-sticky-meta">
                    <span class="studio-status-dot"></span>
                    <span>Configuration changes for <strong><?= e($event->name) ?></strong></span>
                </div>
                <div class="studio-sticky-actions">
                    <button type="submit" class="studio-btn-save" onclick="document.getElementById('studio-save-action').value='save'">
                        <span>💾</span> Save Changes
                    </button>
                    <button type="submit" class="studio-btn-finish-launch" onclick="document.getElementById('studio-save-action').value='finish'">
                        <span>🚀</span> Save &amp; Finish Setup
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Template for adding dynamic ticket packages -->
<template id="tier-card-template">
    <div class="studio-tier-card" data-tier-card>
        <input type="hidden" name="ticket-id[]" value="0">
        
        <div class="studio-tier-card-header">
            <div class="studio-tier-badge-row">
                <span class="studio-tier-counter">New Package</span>
                <span class="studio-kind-chip studio-kind-chip--standard" data-kind-badge>🎟️ Standard Single</span>
            </div>
            <button type="button" class="studio-tier-remove-btn" onclick="removeTierCard(this)" title="Remove package">
                <span>✕</span> Remove
            </button>
        </div>

        <!-- Ticket Kind Selector -->
        <div class="studio-kind-selector-wrap">
            <label class="studio-label">Ticket Package Category</label>
            <div class="studio-kind-grid">
                <label class="studio-kind-opt selected">
                    <input type="radio" value="standard" checked onchange="handleKindChange(this)">
                    <span class="studio-opt-icon">🎟️</span>
                    <div class="studio-opt-info">
                        <strong>Standard Single</strong>
                        <small>1 Person entry</small>
                    </div>
                </label>
                <label class="studio-kind-opt">
                    <input type="radio" value="duo" onchange="handleKindChange(this)">
                    <span class="studio-opt-icon">💑</span>
                    <div class="studio-opt-info">
                        <strong>Couples / Duo</strong>
                        <small>Requires Plus-One (+1)</small>
                    </div>
                </label>
                <label class="studio-kind-opt">
                    <input type="radio" value="group" onchange="handleKindChange(this)">
                    <span class="studio-opt-icon">👥</span>
                    <div class="studio-opt-info">
                        <strong>Group / Table</strong>
                        <small>Squad or table pass</small>
                    </div>
                </label>
                <label class="studio-kind-opt">
                    <input type="radio" value="seated" onchange="handleKindChange(this)">
                    <span class="studio-opt-icon">💺</span>
                    <div class="studio-opt-info">
                        <strong>Hall / Stadium Seated</strong>
                        <small>Sections, rows &amp; seats</small>
                    </div>
                </label>
            </div>
            <input type="hidden" name="ticket-kind[]" class="studio-kind-input" value="standard">
        </div>

        <!-- Primary Info Grid -->
        <div class="studio-field-grid studio-primary-grid">
            <div class="studio-field-item studio-field-name">
                <label class="studio-label">Package / Tier Name <span class="req">*</span></label>
                <input type="text" name="ticket-name[]" class="studio-input studio-name-input" required placeholder="e.g. VIP Front Row, Couples Date Night, Table of 5">
            </div>
            <div class="studio-field-item">
                <label class="studio-label">Price (<?= e($currency) ?>) <span class="req">*</span></label>
                <input type="number" step="0.01" min="0" name="ticket-price[]" class="studio-input" required placeholder="0.00" value="0.00">
            </div>
            <div class="studio-field-item">
                <label class="studio-label">Total Capacity <span class="req">*</span></label>
                <input type="number" min="1" name="ticket-qty[]" class="studio-input studio-qty-input" required placeholder="100" value="100">
            </div>
        </div>

        <!-- Advanced Allocation Controls -->
        <div class="studio-tier-shelves">
            <!-- Duo & Group Access Controls -->
            <div class="studio-shelf studio-shelf-access">
                <div class="studio-shelf-grid">
                    <div class="studio-field-item">
                        <div class="studio-label-with-hint">
                            <label class="studio-label">👥 Admits (People Per Ticket)</label>
                            <span class="studio-hint">Single = 1, Duo/Couple = 2, Table = N</span>
                        </div>
                        <input type="number" min="1" name="ticket-admits[]" class="studio-input studio-admits-input" value="1">
                    </div>
                    <div class="studio-field-item">
                        <div class="studio-label-with-hint">
                            <label class="studio-label">🔒 Max Per Customer</label>
                            <span class="studio-hint">Leave blank for unlimited</span>
                        </div>
                        <input type="number" min="1" name="ticket-max-per-person[]" class="studio-input" placeholder="Unlimited">
                    </div>
                </div>

                <!-- Duo Explanatory Notice -->
                <div class="studio-duo-banner" style="display:none;">
                    <span class="studio-banner-icon">💑</span>
                    <div>
                        <strong>Couples / Duo Pass Mode Active:</strong>
                        <span>At checkout, attendees must provide both their own details and their <strong>Plus-One (+1)</strong> partner's name &amp; email. Two independent scannable QR passes will be generated automatically.</span>
                    </div>
                </div>
            </div>

            <!-- Hall / Stadium Seating Shelf -->
            <div class="studio-shelf studio-shelf-seating" style="display:none;">
                <div class="studio-seating-shelf-header">
                    <span>💺</span>
                    <strong>Hall / Stadium Seating Details</strong>
                    <span class="studio-pill-tag">Allocated Seating</span>
                </div>
                <input type="hidden" name="ticket-has-seating[]" class="studio-has-seating-input" value="0">

                <div class="studio-seating-grid">
                    <div class="studio-field-item">
                        <label class="studio-label">Section Name</label>
                        <input type="text" name="ticket-section[]" class="studio-input" placeholder="e.g. Main Orchestra, Gold Tier, North Stand">
                    </div>
                    <div class="studio-field-item">
                        <label class="studio-label">Row Identifier</label>
                        <input type="text" name="ticket-row[]" class="studio-input" placeholder="e.g. Row A, Row 14, Front Row">
                    </div>
                    <div class="studio-field-item">
                        <label class="studio-label">Seat Prefix</label>
                        <input type="text" name="ticket-seat-prefix[]" class="studio-input" placeholder="e.g. Seat-, A-">
                    </div>
                    <div class="studio-field-item">
                        <label class="studio-label">Start Seat #</label>
                        <input type="number" min="1" name="ticket-seat-start[]" class="studio-input studio-seat-start" value="1" placeholder="1" oninput="calculateSeatedCapacity(this)">
                    </div>
                    <div class="studio-field-item">
                        <label class="studio-label">End Seat #</label>
                        <input type="number" min="1" name="ticket-seat-end[]" class="studio-input studio-seat-end" placeholder="30" oninput="calculateSeatedCapacity(this)">
                    </div>
                </div>
                <div class="studio-seating-summary" style="display:none;">
                    💡 Automatically allocates <strong class="seat-calc-count">0</strong> seats for this row/section.
                </div>
            </div>

            <!-- Package Description & Perks -->
            <div class="studio-shelf studio-shelf-desc">
                <label class="studio-label">Perks, Admission Notes &amp; Seat View <span class="opt">(Optional)</span></label>
                <textarea name="ticket-description[]" class="studio-textarea" rows="2" placeholder="e.g. Includes welcome champagne, front-row unimpeded stage view, and dedicated fast-track entrance."></textarea>
            </div>
        </div>
    </div>
</template>

<style>
/* ══════════════════════════════════════════════════════════════
   Studio Workspace Styling — Clean, Modern & Responsive
   ══════════════════════════════════════════════════════════════ */
.tickets-studio-wrapper {
    max-width: 1100px;
    margin: 0 auto 3rem;
    padding: 0 1rem;
    font-family: inherit;
    color: rgb(var(--text));
}

/* Topbar */
.studio-topbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1rem;
    margin-bottom: 1.5rem;
    padding-bottom: 1.25rem;
    border-bottom: 1px solid rgba(var(--muted), 0.15);
}
.studio-back-link {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    color: rgb(var(--brand));
    font-weight: 700;
    text-decoration: none;
    font-size: 0.9rem;
    margin-bottom: 0.35rem;
}
.studio-breadcrumb-sub {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    flex-wrap: wrap;
}
.studio-event-title-badge {
    font-size: 1.15rem;
    font-weight: 800;
    color: rgb(var(--text));
}
.studio-event-date {
    font-size: 0.85rem;
    color: rgb(var(--muted));
    font-weight: 600;
}
.studio-topbar-right {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    flex-wrap: wrap;
}
.studio-action-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.55rem 0.95rem;
    border-radius: 10px;
    font-size: 0.85rem;
    font-weight: 700;
    text-decoration: none;
    transition: all 0.2s ease;
    cursor: pointer;
}
.studio-btn-outline {
    background: rgba(var(--muted), 0.08);
    color: rgb(var(--text));
    border: 1px solid rgba(var(--muted), 0.25);
}
.studio-btn-outline:hover {
    background: rgba(var(--brand), 0.1);
    color: rgb(var(--brand));
    border-color: rgb(var(--brand));
}
.studio-btn-finish {
    background: rgb(var(--brand));
    color: #ffffff;
    border: none;
}
.studio-btn-finish:hover {
    opacity: 0.92;
    transform: translateY(-1px);
}

/* Stepper Card */
.studio-stepper-card {
    background: rgba(var(--card), 0.9);
    border: 1px solid rgba(var(--muted), 0.2);
    border-radius: 16px;
    padding: 1.25rem 1.5rem;
    margin-bottom: 2rem;
    box-shadow: 0 4px 15px rgba(0,0,0,0.03);
}
.studio-stepper-track {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
}
.studio-step-node {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    flex: 1;
}
.studio-step-icon {
    width: 38px;
    height: 38px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 800;
    font-size: 0.95rem;
    background: rgba(var(--muted), 0.15);
    color: rgb(var(--muted));
    flex-shrink: 0;
}
.studio-step-node--done .studio-step-icon {
    background: #10b981;
    color: #ffffff;
}
.studio-step-node--active .studio-step-icon {
    background: rgb(var(--brand));
    color: #ffffff;
    box-shadow: 0 0 0 4px rgba(var(--brand), 0.2);
}
.studio-step-meta {
    display: flex;
    flex-direction: column;
}
.studio-step-num {
    font-size: 0.7rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: rgb(var(--muted));
}
.studio-step-node--active .studio-step-num {
    color: rgb(var(--brand));
}
.studio-step-title {
    font-size: 0.92rem;
    font-weight: 700;
    color: rgb(var(--text));
}
.studio-step-sub {
    font-size: 0.75rem;
    color: rgb(var(--muted));
}
.studio-step-divider {
    height: 2px;
    background: rgba(var(--muted), 0.2);
    flex: 0 0 60px;
}
.studio-step-divider--active {
    background: rgb(var(--brand));
}

/* Section Cards */
.studio-section-card {
    background: rgb(var(--card));
    border: 1px solid rgba(var(--muted), 0.2);
    border-radius: 20px;
    padding: 1.85rem;
    box-shadow: 0 6px 20px rgba(0,0,0,0.04);
}
.studio-section-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 1.5rem;
    margin-bottom: 1.75rem;
    flex-wrap: wrap;
}
.studio-section-header-info {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
    flex: 1;
    min-width: 260px;
}
.studio-section-icon-badge {
    width: 48px;
    height: 48px;
    border-radius: 14px;
    background: rgba(var(--brand), 0.1);
    color: rgb(var(--brand));
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.4rem;
    flex-shrink: 0;
}
.studio-section-heading {
    font-size: 1.35rem;
    font-weight: 800;
    margin: 0 0 0.35rem;
    color: rgb(var(--text));
}
.studio-section-subheading {
    font-size: 0.88rem;
    color: rgb(var(--muted));
    margin: 0;
    line-height: 1.5;
}
.studio-btn-add {
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    background: rgba(var(--brand), 0.1);
    color: rgb(var(--brand));
    border: 1.5px solid rgba(var(--brand), 0.35);
    padding: 0.65rem 1.15rem;
    border-radius: 12px;
    font-size: 0.88rem;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.2s ease;
}
.studio-btn-add:hover {
    background: rgb(var(--brand));
    color: #ffffff;
    border-color: rgb(var(--brand));
    transform: translateY(-1px);
}

/* Tier Card Bento */
.studio-tier-card {
    background: rgba(var(--muted), 0.03);
    border: 1.5px solid rgba(var(--muted), 0.22);
    border-radius: 18px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
    transition: all 0.25s ease;
    position: relative;
}
.studio-tier-card:hover {
    border-color: rgba(var(--brand), 0.5);
    box-shadow: 0 8px 25px rgba(0,0,0,0.06);
}
.studio-tier-card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.25rem;
    gap: 1rem;
    flex-wrap: wrap;
}
.studio-tier-badge-row {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    flex-wrap: wrap;
}
.studio-tier-counter {
    font-size: 0.85rem;
    font-weight: 800;
    color: rgb(var(--text));
}
.studio-kind-chip {
    font-size: 0.75rem;
    font-weight: 700;
    padding: 0.25rem 0.7rem;
    border-radius: 8px;
    background: rgba(var(--muted), 0.15);
    color: rgb(var(--text));
}
.studio-kind-chip--duo {
    background: rgba(236, 72, 153, 0.14);
    color: #db2777;
    border: 1px solid rgba(236, 72, 153, 0.3);
}
.studio-kind-chip--group {
    background: rgba(99, 102, 241, 0.14);
    color: #6366f1;
    border: 1px solid rgba(99, 102, 241, 0.3);
}
.studio-kind-chip--seated {
    background: rgba(16, 185, 129, 0.14);
    color: #059669;
    border: 1px solid rgba(16, 185, 129, 0.3);
}
.studio-sold-chip {
    font-size: 0.72rem;
    font-weight: 700;
    padding: 0.25rem 0.65rem;
    border-radius: 8px;
    background: rgba(239, 68, 68, 0.14);
    color: #dc2626;
    border: 1px solid rgba(239, 68, 68, 0.3);
}
.studio-tier-remove-btn {
    background: transparent;
    border: 1px solid rgba(239, 68, 68, 0.35);
    color: #dc2626;
    border-radius: 8px;
    padding: 0.35rem 0.75rem;
    font-size: 0.78rem;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.2s ease;
}
.studio-tier-remove-btn:hover:not(:disabled) {
    background: #dc2626;
    color: #ffffff;
}
.studio-tier-remove-btn:disabled {
    opacity: 0.45;
    cursor: not-allowed;
}

/* Kind Selector Tabs */
.studio-kind-selector-wrap {
    margin-bottom: 1.25rem;
}
.studio-kind-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
    gap: 0.75rem;
    margin-top: 0.45rem;
}
.studio-kind-opt {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem 0.95rem;
    border-radius: 12px;
    border: 1.5px solid rgba(var(--muted), 0.25);
    background: rgba(var(--card), 0.7);
    cursor: pointer;
    transition: all 0.2s ease;
}
.studio-kind-opt input[type="radio"] {
    display: none;
}
.studio-kind-opt:hover {
    border-color: rgba(var(--brand), 0.4);
    background: rgba(var(--brand), 0.04);
}
.studio-kind-opt.selected {
    border-color: rgb(var(--brand));
    background: rgba(var(--brand), 0.08);
    box-shadow: 0 0 0 2px rgba(var(--brand), 0.2);
}
.studio-opt-icon {
    font-size: 1.4rem;
}
.studio-opt-info {
    display: flex;
    flex-direction: column;
}
.studio-opt-info strong {
    font-size: 0.88rem;
    color: rgb(var(--text));
}
.studio-opt-info small {
    font-size: 0.74rem;
    color: rgb(var(--muted));
}

/* Inputs & Grids */
.studio-field-grid {
    display: grid;
    gap: 1rem;
}
.studio-primary-grid {
    grid-template-columns: 2fr 1fr 1fr;
    margin-bottom: 1.25rem;
}
.studio-field-item {
    display: flex;
    flex-direction: column;
    gap: 0.35rem;
}
.studio-label {
    font-size: 0.82rem;
    font-weight: 700;
    color: rgb(var(--text));
}
.studio-label .req {
    color: #ef4444;
}
.studio-label .opt {
    font-weight: 500;
    font-size: 0.75rem;
    color: rgb(var(--muted));
}
.studio-label-with-hint {
    display: flex;
    justify-content: space-between;
    align-items: baseline;
    gap: 0.5rem;
}
.studio-hint {
    font-size: 0.72rem;
    color: rgb(var(--muted));
    font-weight: 500;
}
.studio-input, .studio-textarea {
    width: 100%;
    box-sizing: border-box;
    padding: 0.7rem 0.9rem;
    border-radius: 10px;
    border: 1.5px solid rgba(var(--muted), 0.25);
    background: rgb(var(--card));
    color: rgb(var(--text));
    font-size: 0.9rem;
    outline: none;
    transition: all 0.2s ease;
}
.studio-input:focus, .studio-textarea:focus {
    border-color: rgb(var(--brand));
    box-shadow: 0 0 0 3px rgba(var(--brand), 0.15);
}

/* Shelves */
.studio-tier-shelves {
    display: flex;
    flex-direction: column;
    gap: 1rem;
    border-top: 1px dashed rgba(var(--muted), 0.25);
    padding-top: 1rem;
}
.studio-shelf-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}
.studio-duo-banner {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
    margin-top: 0.75rem;
    padding: 0.85rem 1.1rem;
    border-radius: 12px;
    background: rgba(236, 72, 153, 0.08);
    border: 1px solid rgba(236, 72, 153, 0.25);
    font-size: 0.82rem;
    line-height: 1.5;
    color: rgb(var(--text));
}
.studio-banner-icon {
    font-size: 1.3rem;
}
.studio-duo-banner strong {
    color: #db2777;
    display: block;
    margin-bottom: 0.2rem;
}

/* Seating Shelf */
.studio-shelf-seating {
    background: rgba(16, 185, 129, 0.05);
    border: 1px solid rgba(16, 185, 129, 0.25);
    border-radius: 14px;
    padding: 1.15rem;
}
.studio-seating-shelf-header {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 0.95rem;
    font-size: 0.92rem;
    color: #059669;
}
.studio-pill-tag {
    font-size: 0.7rem;
    font-weight: 800;
    text-transform: uppercase;
    background: #059669;
    color: #ffffff;
    padding: 0.2rem 0.55rem;
    border-radius: 999px;
}
.studio-seating-grid {
    display: grid;
    grid-template-columns: 2fr 1.5fr 1fr 1fr 1fr;
    gap: 0.75rem;
}
.studio-seating-summary {
    margin-top: 0.75rem;
    font-size: 0.8rem;
    color: #059669;
    font-weight: 600;
}

/* Guests & Addons Cards */
.studio-guest-card, .studio-addon-card {
    background: rgba(var(--muted), 0.04);
    border: 1px solid rgba(var(--muted), 0.2);
    border-radius: 14px;
    padding: 1.15rem;
    margin-bottom: 1rem;
}
.studio-guest-header, .studio-addon-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.85rem;
}
.studio-guest-avatar {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    background: rgba(var(--muted), 0.15);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 0.85rem;
    margin-right: 0.35rem;
}
.studio-guest-title, .studio-addon-title {
    font-size: 0.88rem;
    font-weight: 700;
    color: rgb(var(--text));
    flex: 1;
}
.studio-guest-remove, .studio-addon-remove {
    background: transparent;
    border: none;
    color: #ef4444;
    font-size: 0.78rem;
    font-weight: 700;
    cursor: pointer;
}
.studio-guest-grid, .studio-addon-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 0.85rem;
}

/* Addon Media Shelf */
.studio-addon-media-shelf {
    display: flex;
    align-items: center;
    gap: 1.15rem;
    margin-top: 0.85rem;
    padding: 0.85rem 1rem;
    background: rgba(var(--card), 0.7);
    border: 1px dashed rgba(var(--muted), 0.28);
    border-radius: 12px;
}
.studio-addon-thumb-box {
    width: 68px;
    height: 68px;
    border-radius: 10px;
    background: rgba(var(--muted), 0.08);
    border: 1px solid rgba(var(--muted), 0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    flex-shrink: 0;
}
.studio-addon-thumb-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.studio-addon-thumb-placeholder {
    font-size: 1.8rem;
    opacity: 0.6;
}
.studio-addon-media-inputs {
    flex: 1;
    min-width: 0;
    display: flex;
    align-items: center;
    gap: 0.85rem;
    flex-wrap: wrap;
}
.studio-addon-media-col {
    flex: 1;
    min-width: 180px;
}
.studio-addon-media-or {
    font-size: 0.72rem;
    font-weight: 850;
    color: rgb(var(--muted));
    text-transform: uppercase;
    letter-spacing: 0.06em;
    padding: 0 0.2rem;
    align-self: flex-end;
    margin-bottom: 0.7rem;
}
.studio-file-input {
    width: 100%;
    font-size: 0.82rem;
    padding: 0.45rem 0.6rem;
    border: 1px solid rgba(var(--muted), 0.3);
    border-radius: 8px;
    background: rgba(var(--card), 0.9);
    color: rgb(var(--text));
    cursor: pointer;
    box-sizing: border-box;
}
.studio-subtext {
    font-size: 0.72rem;
    color: rgb(var(--muted));
    font-weight: normal;
}

/* Empty State */
.studio-empty-state {
    text-align: center;
    padding: 3.5rem 1.5rem;
    border: 2px dashed rgba(var(--muted), 0.3);
    border-radius: 18px;
    background: rgba(var(--muted), 0.02);
}
.studio-empty-icon {
    font-size: 3rem;
    display: block;
    margin-bottom: 0.75rem;
}
.studio-empty-state h3 {
    font-size: 1.25rem;
    font-weight: 800;
    margin: 0 0 0.45rem;
}
.studio-empty-state p {
    font-size: 0.88rem;
    color: rgb(var(--muted));
    max-width: 480px;
    margin: 0 auto 1.5rem;
}
.studio-btn-primary {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    background: rgb(var(--brand));
    color: #ffffff;
    border: none;
    padding: 0.75rem 1.45rem;
    border-radius: 12px;
    font-size: 0.92rem;
    font-weight: 700;
    cursor: pointer;
    box-shadow: 0 4px 15px rgba(var(--brand), 0.3);
}
.studio-empty-subtext {
    font-size: 0.85rem;
    color: rgb(var(--muted));
    margin: 0.5rem 0;
}

/* Sticky Action Footer */
.studio-sticky-bar {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    background: rgba(var(--card), 0.95);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    border-top: 1px solid rgba(var(--muted), 0.25);
    padding: 1rem 1.5rem;
    z-index: 100;
    box-shadow: 0 -10px 25px rgba(0,0,0,0.06);
}
.studio-sticky-bar-inner {
    max-width: 1100px;
    margin: 0 auto;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
    flex-wrap: wrap;
}
.studio-sticky-meta {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    font-size: 0.85rem;
    color: rgb(var(--muted));
}
.studio-status-dot {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background: #10b981;
    box-shadow: 0 0 8px rgba(16, 185, 129, 0.5);
}
.studio-sticky-actions {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    flex-wrap: wrap;
}
.studio-btn-save {
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    background: rgba(var(--muted), 0.12);
    color: rgb(var(--text));
    border: 1px solid rgba(var(--muted), 0.3);
    padding: 0.75rem 1.35rem;
    border-radius: 12px;
    font-size: 0.9rem;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.2s ease;
}
.studio-btn-save:hover {
    background: rgba(var(--brand), 0.1);
    color: rgb(var(--brand));
    border-color: rgb(var(--brand));
}
.studio-btn-finish-launch {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    background: rgb(var(--brand));
    color: #ffffff;
    border: none;
    padding: 0.75rem 1.65rem;
    border-radius: 12px;
    font-size: 0.92rem;
    font-weight: 800;
    cursor: pointer;
    box-shadow: 0 4px 15px rgba(var(--brand), 0.35);
    transition: all 0.2s ease;
}
.studio-btn-finish-launch:hover {
    opacity: 0.94;
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(var(--brand), 0.45);
}

/* ══════════════════════════════════════════════════════════════
   Responsive Breakpoints: Desktop, Tablet, Mobile
   ══════════════════════════════════════════════════════════════ */
@media (max-width: 900px) {
    .studio-primary-grid {
        grid-template-columns: 1fr 1fr;
    }
    .studio-field-name {
        grid-column: 1 / -1;
    }
    .studio-seating-grid {
        grid-template-columns: 1fr 1fr;
    }
}

@media (max-width: 640px) {
    .studio-stepper-track {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.85rem;
    }
    .studio-step-divider {
        display: none;
    }
    .studio-primary-grid {
        grid-template-columns: 1fr;
    }
    .studio-shelf-grid {
        grid-template-columns: 1fr;
    }
    .studio-seating-grid {
        grid-template-columns: 1fr;
    }
    .studio-kind-grid {
        grid-template-columns: 1fr;
    }
    .studio-addon-media-shelf {
        flex-direction: column;
        align-items: stretch;
    }
    .studio-addon-thumb-box {
        align-self: center;
    }
    .studio-addon-media-or {
        align-self: center;
        margin: 0.25rem 0;
    }
    .studio-sticky-bar-inner {
        flex-direction: column;
        align-items: stretch;
    }
    .studio-sticky-actions {
        width: 100%;
    }
    .studio-btn-save, .studio-btn-finish-launch {
        flex: 1;
        justify-content: center;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let tierIndexCounter = <?= count($ticketTypes) ?>;

    // Handle ticket kind change on radio button click
    window.handleKindChange = function(radioEl) {
        const card = radioEl.closest('[data-tier-card]');
        if (!card) return;

        const val = radioEl.value;
        const kindInput = card.querySelector('.studio-kind-input');
        if (kindInput) kindInput.value = val;

        // Update selected style on options
        card.querySelectorAll('.studio-kind-opt').forEach(opt => {
            opt.classList.remove('selected');
        });
        radioEl.closest('.studio-kind-opt').classList.add('selected');

        // Update top badge chip
        const badge = card.querySelector('[data-kind-badge]');
        if (badge) {
            badge.className = 'studio-kind-chip studio-kind-chip--' + val;
            if (val === 'duo') {
                badge.textContent = '💑 Couples / Duo';
            } else if (val === 'group') {
                badge.textContent = '👥 Group / Table';
            } else if (val === 'seated') {
                badge.textContent = '💺 Hall / Stadium Seated';
            } else {
                badge.textContent = '🎟️ Standard Single';
            }
        }

        // Shelf visibility
        const duoBanner = card.querySelector('.studio-duo-banner');
        const seatingShelf = card.querySelector('.studio-shelf-seating');
        const hasSeatingInput = card.querySelector('.studio-has-seating-input');
        const admitsInput = card.querySelector('.studio-admits-input');

        if (val === 'duo') {
            if (duoBanner) duoBanner.style.display = 'flex';
            if (seatingShelf) seatingShelf.style.display = 'none';
            if (hasSeatingInput) hasSeatingInput.value = '0';
            if (admitsInput && parseInt(admitsInput.value, 10) < 2) {
                admitsInput.value = '2';
            }
        } else if (val === 'seated') {
            if (duoBanner) duoBanner.style.display = 'none';
            if (seatingShelf) seatingShelf.style.display = 'block';
            if (hasSeatingInput) hasSeatingInput.value = '1';
        } else if (val === 'group') {
            if (duoBanner) duoBanner.style.display = 'none';
            if (seatingShelf) seatingShelf.style.display = 'none';
            if (hasSeatingInput) hasSeatingInput.value = '0';
            if (admitsInput && parseInt(admitsInput.value, 10) < 2) {
                admitsInput.value = '4';
            }
        } else {
            // Standard
            if (duoBanner) duoBanner.style.display = 'none';
            if (seatingShelf) seatingShelf.style.display = 'none';
            if (hasSeatingInput) hasSeatingInput.value = '0';
            if (admitsInput) admitsInput.value = '1';
        }
    };

    // Calculate capacity automatically when seat start/end are typed
    window.calculateSeatedCapacity = function(inputEl) {
        const card = inputEl.closest('[data-tier-card]');
        if (!card) return;

        const startInput = card.querySelector('.studio-seat-start');
        const endInput = card.querySelector('.studio-seat-end');
        const qtyInput = card.querySelector('.studio-qty-input');
        const summaryBox = card.querySelector('.studio-seating-summary');
        const countSpan = card.querySelector('.seat-calc-count');

        const sVal = parseInt(startInput?.value, 10);
        const eVal = parseInt(endInput?.value, 10);

        if (!isNaN(sVal) && !isNaN(eVal) && eVal >= sVal) {
            const count = (eVal - sVal) + 1;
            if (qtyInput) qtyInput.value = count;
            if (countSpan) countSpan.textContent = count;
            if (summaryBox) summaryBox.style.display = 'block';
        } else {
            if (summaryBox) summaryBox.style.display = 'none';
        }
    };

    // Remove tier card
    window.removeTierCard = function(btn) {
        const card = btn.closest('[data-tier-card]');
        if (card) {
            card.style.opacity = '0';
            card.style.transform = 'scale(0.96)';
            setTimeout(() => {
                card.remove();
                renumberTiers();
            }, 200);
        }
    };

    function renumberTiers() {
        const cards = document.querySelectorAll('#studio-tiers-container [data-tier-card]');
        const emptyState = document.getElementById('studio-tiers-empty');
        if (cards.length === 0 && emptyState) {
            emptyState.style.display = 'block';
        }
        cards.forEach((card, idx) => {
            const counter = card.querySelector('.studio-tier-counter');
            if (counter) counter.textContent = 'Package #' + (idx + 1);
        });
    }

    // Add Ticket Package Button
    const addBtn = document.getElementById('studio-add-tier-btn');
    if (addBtn) {
        addBtn.addEventListener('click', () => addTicketTier('standard'));
    }

    window.addTicketTier = function(defaultKind = 'standard') {
        const emptyState = document.getElementById('studio-tiers-empty');
        if (emptyState) emptyState.style.display = 'none';

        tierIndexCounter++;
        const template = document.getElementById('tier-card-template');
        const clone = template.content.cloneNode(true);
        const card = clone.querySelector('[data-tier-card]');

        // Update radio group name so each tier has its own independent radio group
        const groupName = 'tier_kind_selector_new_' + tierIndexCounter;
        card.querySelectorAll('input[type="radio"]').forEach(radio => {
            radio.setAttribute('name', groupName);
        });

        document.getElementById('studio-tiers-container').appendChild(card);
        renumberTiers();

        if (defaultKind !== 'standard') {
            const targetRadio = card.querySelector(`input[type="radio"][value="${defaultKind}"]`);
            if (targetRadio) {
                targetRadio.checked = true;
                handleKindChange(targetRadio);
            }
        }

        // Scroll into view
        card.scrollIntoView({ behavior: 'smooth', block: 'center' });
    };

    // Add Guest Button
    const addGuestBtn = document.getElementById('studio-add-guest-btn');
    if (addGuestBtn) {
        addGuestBtn.addEventListener('click', function() {
            const emptySub = document.getElementById('studio-guests-empty');
            if (emptySub) emptySub.style.display = 'none';

            const container = document.getElementById('studio-guests-container');
            const count = container.querySelectorAll('.studio-guest-card').length + 1;

            const div = document.createElement('div');
            div.className = 'studio-guest-card';
            div.innerHTML = `
                <div class="studio-guest-header">
                    <div class="studio-guest-avatar">👤</div>
                    <span class="studio-guest-title">Performer / Guest #${count}</span>
                    <button type="button" class="studio-guest-remove" onclick="this.closest('.studio-guest-card').remove()">✕ Remove</button>
                </div>
                <div class="studio-guest-grid">
                    <div class="studio-field-item">
                        <label class="studio-label">Full Name <span class="req">*</span></label>
                        <input type="text" name="guest-name[]" class="studio-input" required placeholder="Artist or Speaker Name">
                    </div>
                    <div class="studio-field-item">
                        <label class="studio-label">Role / Headline Title</label>
                        <input type="text" name="guest-role[]" class="studio-input" placeholder="e.g. Headlining DJ, Keynote Speaker">
                    </div>
                    <div class="studio-field-item">
                        <label class="studio-label">Contact Phone <span class="opt">(Optional)</span></label>
                        <input type="tel" name="guest-phone[]" class="studio-input" placeholder="+1 555 555 5555">
                    </div>
                    <div class="studio-field-item">
                        <label class="studio-label">Social / Website <span class="opt">(Optional)</span></label>
                        <input type="text" name="guest-social[]" class="studio-input" placeholder="@handle or https://...">
                    </div>
                </div>
            `;
            container.appendChild(div);
            div.scrollIntoView({ behavior: 'smooth', block: 'center' });
        });
    }

    // Add Addon Button
    const addAddonBtn = document.getElementById('studio-add-addon-btn');
    if (addAddonBtn) {
        addAddonBtn.addEventListener('click', function() {
            const emptySub = document.getElementById('studio-addons-empty');
            if (emptySub) emptySub.style.display = 'none';

            const container = document.getElementById('studio-addons-container');
            const count = container.querySelectorAll('.studio-addon-card').length + 1;

            const div = document.createElement('div');
            div.className = 'studio-addon-card';
            div.innerHTML = `
                <input type="hidden" name="addon-id[]" value="0">
                <input type="hidden" name="addon-image-existing[]" value="" class="studio-addon-existing-val">
                <div class="studio-addon-header">
                    <span class="studio-addon-title">🛍️ Item #${count}</span>
                    <button type="button" class="studio-addon-remove" onclick="this.closest('.studio-addon-card').remove()">✕ Remove</button>
                </div>
                <div class="studio-addon-grid">
                    <div class="studio-field-item">
                        <label class="studio-label">Item Name <span class="req">*</span></label>
                        <input type="text" name="addon-name[]" class="studio-input" required placeholder="e.g. Tour T-Shirt, VIP Parking Pass">
                    </div>
                    <div class="studio-field-item">
                        <label class="studio-label">Price (<?= e($currency) ?>) <span class="req">*</span></label>
                        <input type="number" step="0.01" min="0" name="addon-price[]" class="studio-input" required placeholder="0.00" value="0.00">
                    </div>
                    <div class="studio-field-item">
                        <label class="studio-label">Available Inventory</label>
                        <input type="number" min="1" name="addon-quantity[]" class="studio-input" placeholder="Unlimited">
                    </div>
                </div>

                <!-- Addon Media Shelf (File Upload or Image Web URL) -->
                <div class="studio-addon-media-shelf">
                    <div class="studio-addon-thumb-box">
                        <span class="studio-addon-thumb-placeholder">🛍️</span>
                    </div>
                    <div class="studio-addon-media-inputs">
                        <div class="studio-addon-media-col">
                            <label class="studio-label" style="margin-bottom:0.25rem;">
                                Upload Photo <span class="studio-subtext">(JPG, PNG, WebP)</span>
                            </label>
                            <input type="file" name="addon-image[]" accept="image/jpeg,image/png,image/webp,image/gif" class="studio-file-input" onchange="previewAddonFile(this)">
                        </div>
                        <div class="studio-addon-media-or">OR</div>
                        <div class="studio-addon-media-col">
                            <label class="studio-label" style="margin-bottom:0.25rem;">
                                Image Web URL <span class="studio-subtext">(Paste link)</span>
                            </label>
                            <input type="url" name="addon-image-url[]" class="studio-input" placeholder="https://... or paste image link" oninput="previewAddonUrl(this)">
                        </div>
                    </div>
                </div>

                <div class="studio-field-item" style="margin-top:0.75rem;">
                    <label class="studio-label">Item Description &amp; Redemption Notes</label>
                    <textarea name="addon-description[]" class="studio-textarea" rows="2" placeholder="e.g. Collect at the VIP booth on arrival with your pass QR code."></textarea>
                </div>
            `;
            container.appendChild(div);
            div.scrollIntoView({ behavior: 'smooth', block: 'center' });
        });
    }
});

function previewAddonFile(input) {
    const card = input.closest('.studio-addon-card');
    const thumbBox = card.querySelector('.studio-addon-thumb-box');
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            thumbBox.innerHTML = '<img src="' + e.target.result + '" class="studio-addon-thumb-img" alt="Preview">';
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function previewAddonUrl(input) {
    const card = input.closest('.studio-addon-card');
    const thumbBox = card.querySelector('.studio-addon-thumb-box');
    const url = input.value.trim();
    if (url) {
        thumbBox.innerHTML = '<img src="' + url + '" class="studio-addon-thumb-img" alt="Preview" onerror="this.onerror=null; this.parentElement.innerHTML=\'<span class=\\\'studio-addon-thumb-placeholder\\\'>🛍️</span>\';">';
    } else {
        const existing = card.querySelector('.studio-addon-existing-val')?.value;
        if (existing) {
            thumbBox.innerHTML = '<img src="' + existing + '" class="studio-addon-thumb-img" alt="Preview">';
        } else {
            thumbBox.innerHTML = '<span class="studio-addon-thumb-placeholder">🛍️</span>';
        }
    }
}
</script>
@endsection
