<?php

/**
 * Event Page — Vintage Edition
 *
 * Immersive vintage box office & ticket reservation parlor.
 * Features antique admission stubs, letterpress typography ('Instrument Serif'),
 * clockwork countdown, interactive ticket steppers, receipt summary, and mobile optimization.
 *
 * @package Eventlyy
 * @subpackage Views/Event
 * @author Celio Natti
 * @license MIT License
 * @version 3.1.0
 */

$eventImg = !empty($event->image) ? $event->image : 'https://images.unsplash.com/photo-1506157786151-b8491531f063?w=1200&q=80&auto=format&fit=crop';
$rawDesc = $event->description ?? '';
$heroSummary = (mb_strlen($rawDesc) > 160) ? mb_strimwidth($rawDesc, 0, 160, '…') : $rawDesc;
$eventDate = strtotime($event->date ?? '');
$now = time();
$liveWindowEnd = $eventDate ? $eventDate + (event_completion_hours() * 3600) : 0;
$isCompleted = ($eventDate && $now > $liveWindowEnd) || in_array(strtolower($event->status ?? ''), ['completed', 'past', 'cancelled']);
$isLiveNow = !$isCompleted && ($eventDate && $now >= $eventDate && $now <= $liveWindowEnd);
$isToday = !$isCompleted && ($eventDate && date('Y-m-d', $now) === date('Y-m-d', $eventDate));
$eventIdentifier = !empty($event->custom_url) ? $event->custom_url : ($event->slug ?? $event->id);

// Ticket sales countdown: show when event is < 24 hours away and not yet started/completed
$salesCloseTimestamp = $eventDate ?: 0;
$hoursUntilClose = $salesCloseTimestamp ? ($salesCloseTimestamp - $now) / 3600 : 0;
$showCountdown = !$isCompleted && !$isLiveNow && $salesCloseTimestamp && $hoursUntilClose > 0 && $hoursUntilClose <= 24;

$shareUrl = event_url($event);
$shareText = urlencode("Check out " . ($event->name ?? 'this event') . " on Eventlyy!");
$encodedShareUrl = urlencode($shareUrl);
?>

@extends('layouts/main')

@section('title')
<?= e($event->name) ?> — Eventlyy Box Office
@endsection

@section('content')
<style>
.ve-badge--series-link {
    border-color: var(--ve-brass);
    color: var(--ve-brass);
    text-decoration: none;
}
.ve-min-w-0 { min-width: 0; }
.ve-share-btn--full { grid-column: 1 / -1; }
.ve-series-head {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 0.75rem;
    margin-bottom: 0.75rem;
}
.ve-series-title {
    font-family: var(--ve-font-serif);
    font-size: 1.4rem;
    color: var(--ve-ink);
    margin: 0;
}
.ve-series-sub {
    font-size: 0.85rem;
    color: var(--ve-ink-muted);
    margin: 0.2rem 0 0;
}
.ve-series-select {
    width: 100%;
    height: 44px;
    padding: 0 0.85rem;
    border-radius: var(--ve-radius-sm);
    border: 1px solid var(--ve-border-dark);
    background: var(--ve-parchment-card);
    font-family: var(--ve-font-sans);
    font-size: 0.9rem;
    color: var(--ve-ink);
}
.ve-concluded-icon {
    font-size: 2.75rem;
    display: block;
    margin-bottom: 0.5rem;
}
.ve-concluded-title {
    font-family: var(--ve-font-serif);
    font-size: 2rem;
    color: var(--ve-ink);
    margin: 0 0 0.5rem;
}
.ve-concluded-text {
    color: var(--ve-ink-muted);
    max-width: 500px;
    margin: 0 auto 1.5rem;
}
.ve-concluded-btn { display: inline-flex; }
.ve-empty-tiers-msg {
    color: var(--ve-ink-muted);
    text-align: center;
    padding: 2rem 0;
}
.ve-empty-summary-msg {
    font-size: 0.86rem;
    color: var(--ve-ink-light);
    text-align: center;
    margin: auto 0;
    font-style: italic;
}
.ve-guests-section { margin-bottom: 2rem; }
.ve-guests-title {
    font-family: var(--ve-font-serif);
    font-size: 1.6rem;
    color: var(--ve-ink);
    margin: 0 0 1rem;
}
.ve-guest-avatar {
    width: 38px;
    height: 38px;
    font-size: 0.95rem;
}
.ve-guest-name {
    display: block;
    font-size: 0.92rem;
    color: var(--ve-ink);
}
.ve-guest-role {
    font-size: 0.78rem;
    color: var(--ve-ink-muted);
}
</style>
<div class="ve-wrapper">

    <!-- ═══ 1. Grand Event Hero Stage ═══ -->
    <div class="ve-hero-stage">
        <section class="ve-hero-main">
            <!-- Mobile Poster Showcase (visible on <= 960px) -->
            <div class="ve-mobile-poster" aria-hidden="true">
                <img src="<?= e($eventImg) ?>" alt="<?= e($event->name) ?> Poster" loading="eager" decoding="async">
            </div>

            <!-- Badges -->
            <div class="ve-badges-row">
                <?php if ($isCompleted): ?>
                    <span class="ve-badge ve-badge--completed">🏁 Concluded</span>
                <?php elseif ($isLiveNow): ?>
                    <span class="ve-badge ve-badge--live">🔴 Live Now &bull; Gate Admission</span>
                <?php elseif ($isToday): ?>
                    <span class="ve-badge ve-badge--today">⚡ Today's Gathering</span>
                <?php endif; ?>

                <span class="ve-badge"><?= e($event->category ?? 'Special Gathering') ?></span>

                <?php if (!empty($event->is_recurring)): ?>
                    <a href="#series-date-selector" class="ve-badge ve-badge--series-link">
                        🔄 Recurring Series<?= !empty($recurringOccurrences) ? ' (' . count($recurringOccurrences) . ' Dates)' : '' ?>
                    </a>
                <?php endif; ?>

                <?php if (!empty($event->flyer_page_enabled)): ?>
                    <a href="<?= e(event_flyer_url($event)) ?>" class="ve-badge ve-badge--flyer" title="View dedicated flyer poster">
                        🎨 Flyer Mode
                    </a>
                <?php endif; ?>
            </div>

            <!-- Title -->
            <h1 class="ve-title"><?= e($event->name) ?></h1>
            <p class="ve-summary"><?= e($heroSummary) ?></p>

            <!-- Postmark Meta Chips -->
            <div class="ve-meta-grid">
                <div class="ve-meta-card ve-meta-card--date">
                    <span class="ve-meta-icon" aria-hidden="true">🗓️</span>
                    <div class="ve-meta-content">
                        <span class="ve-meta-label">Date</span>
                        <span class="ve-meta-val"><?= $eventDate ? date('M d, Y', $eventDate) : 'Date TBA' ?></span>
                    </div>
                </div>
                <div class="ve-meta-card ve-meta-card--time">
                    <span class="ve-meta-icon" aria-hidden="true">⏰</span>
                    <div class="ve-meta-content">
                        <span class="ve-meta-label">Time</span>
                        <span class="ve-meta-val"><?= $eventDate ? date('g:i A', $eventDate) : 'Time TBA' ?></span>
                    </div>
                </div>
                <div class="ve-meta-card ve-meta-card--venue">
                    <span class="ve-meta-icon" aria-hidden="true">📍</span>
                    <div class="ve-meta-content">
                        <span class="ve-meta-label">Venue</span>
                        <span class="ve-meta-val" title="<?= e($event->location) ?>"><?= e($event->location) ?></span>
                    </div>
                </div>
            </div>
        </section>

        <!-- 2. Framed Poster & Host Ledger Sidebar -->
        <aside class="ve-sidebar">
            <div class="ve-poster-wrap ve-desktop-only">
                <img src="<?= e($eventImg) ?>" alt="<?= e($event->name) ?> Poster" class="ve-poster-img" loading="eager" decoding="async">
            </div>
            <div class="ve-sidebar-body">
                <!-- Host Ledger -->
                <div class="ve-host-block">
                    <div class="ve-host-avatar">
                        <?= strtoupper(substr($event->organizer_name ?? 'H', 0, 1)) ?>
                    </div>
                    <div class="ve-min-w-0">
                        <span class="ve-host-name"><?= e($event->organizer_name ?? 'Event Organizer') ?></span>
                        <span class="ve-host-rating">★ <?= number_format($event->organizer_rating ?? 5.0, 1) ?> Verified Host Rating</span>
                    </div>
                </div>

                <!-- Share Dispatch -->
                <div class="ve-share-block">
                    <span class="ve-block-label">Share This Experience</span>
                    <div class="ve-share-btns">
                        <a href="https://api.whatsapp.com/send?text=<?= $shareText ?>%20<?= $encodedShareUrl ?>" target="_blank" rel="noopener" class="ve-share-btn">WhatsApp</a>
                        <a href="https://twitter.com/intent/tweet?url=<?= $encodedShareUrl ?>&text=<?= $shareText ?>" target="_blank" rel="noopener" class="ve-share-btn">X / Twitter</a>
                        <button type="button" id="copy-share-link" class="ve-share-btn ve-share-btn--full">
                            🔗 <span id="copy-btn-text">Copy Event Link</span>
                        </button>
                    </div>
                </div>

                <!-- Add to Calendar -->
                <div class="ve-share-block">
                    <span class="ve-block-label">📅 Add to Calendar</span>
                    <div class="ve-cal-btns">
                        <a href="<?= event_google_calendar_url($event) ?>" target="_blank" rel="noopener" class="ve-share-btn">Google</a>
                        <a href="<?= event_ics_url($event) ?>" download class="ve-share-btn">iCal</a>
                        <a href="<?= event_outlook_calendar_url($event) ?>" target="_blank" rel="noopener" class="ve-share-btn">Outlook</a>
                    </div>
                </div>
            </div>
        </aside>
    </div>

    <!-- ═══ 3. Recurring Series Switcher (if applicable) ═══ -->
    <?php if (!empty($recurringOccurrences) && count($recurringOccurrences) > 1): ?>
        <div class="ve-series-card" id="series-date-selector">
            <div class="ve-series-head">
                <div>
                    <h3 class="ve-series-title">Event Series Dates</h3>
                    <p class="ve-series-sub">This event is held across multiple dates. Select an occurrence below:</p>
                </div>
                <span class="ve-badge"><?= count($recurringOccurrences) ?> Dates in Series</span>
            </div>
            <select id="series-occurrence-select" class="ve-series-select" onchange="if (this.value) window.location.href = this.value;">
                <?php foreach ($recurringOccurrences as $occ): 
                    $occTime = strtotime($occ['date']);
                    $isCurrentOcc = ($occ['id'] == $event->id);
                    $occUrl = event_url((object)$occ);
                    $label = 'Occurrence #' . ($occ['recurrence_sequence'] ?? 1) . ' — ' . date('D, M j, Y • g:i A', $occTime) . ($isCurrentOcc ? ' (Current)' : '');
                ?>
                    <option value="<?= e($occUrl) ?>" <?= $isCurrentOcc ? 'selected' : '' ?>>
                        <?= e($label) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    <?php endif; ?>

    <!-- ═══ 4. Box Office Admissions Counter ═══ -->
    <?php if ($isCompleted): ?>
        <div class="ve-completed-box">
            <span class="ve-concluded-icon">🏁</span>
            <h3 class="ve-concluded-title">Admission Sales Concluded</h3>
            <p class="ve-concluded-text">This gathering concluded on <?= date('F j, Y \a\t g:i A', strtotime($event->date)) ?>. We thank all patrons for attending.</p>
            <a href="<?= route('event/' . $eventIdentifier . '/feedback') ?>" class="vn-btn-primary ve-concluded-btn">
                ⭐ Rate &amp; Review This Event
            </a>
        </div>
    <?php else: ?>
        <form id="tickets-form" method="GET" action="{{ route('checkout/{event_id}', ['event_id' => $eventIdentifier]) }}">
            <input type="hidden" name="event_id" value="<?= $eventIdentifier ?>" />
            <input type="hidden" name="occ_id" value="<?= (int)($event->id ?? 0) ?>" />
            <?php if (!empty($event->recurrence_sequence)): ?>
                <input type="hidden" name="occ" value="<?= (int)$event->recurrence_sequence ?>" />
            <?php endif; ?>
            <?php if (!empty($refCode) || !empty($_GET['ref'])): ?>
                <input type="hidden" name="ref" value="<?= e($refCode ?? ($_GET['ref'] ?? '')) ?>" />
            <?php endif; ?>

            <div class="ve-box-office">
                <!-- Left: Ticket Tiers Desk -->
                <div class="ve-desk-card">
                    <?php if ($showCountdown): ?>
                        <!-- Clockwork Urgency Banner -->
                        <div class="ve-clockwork-card" id="ticket-countdown-banner" data-close-timestamp="<?= $salesCloseTimestamp ?>">
                            <div class="ve-clockwork-info">
                                <span class="ve-clockwork-icon">⏳</span>
                                <div>
                                    <span class="ve-clockwork-label">Box Office Closing Soon</span>
                                    <div class="ve-clockwork-sub">Reserve your passes before online sales close</div>
                                </div>
                            </div>
                            <div class="ve-clockwork-timer" id="countdown-timer">
                                <div class="ve-timer-box">
                                    <span class="ve-timer-val" id="cd-hours">--</span>
                                    <span class="ve-timer-lbl">Hours</span>
                                </div>
                                <span class="ve-timer-colon">:</span>
                                <div class="ve-timer-box">
                                    <span class="ve-timer-val" id="cd-minutes">--</span>
                                    <span class="ve-timer-lbl">Mins</span>
                                </div>
                                <span class="ve-timer-colon">:</span>
                                <div class="ve-timer-box">
                                    <span class="ve-timer-val" id="cd-seconds">--</span>
                                    <span class="ve-timer-lbl">Secs</span>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="ve-desk-header">
                        <div>
                            <span class="ve-desk-pre">Admissions Counter</span>
                            <h2 class="ve-desk-title">Select Admission Tiers</h2>
                        </div>
                        <span class="ve-badge">⚡ Instant QR Delivery</span>
                    </div>

                    <div class="ve-tier-list">
                        <?php if (empty($ticketTypes)): ?>
                            <p class="ve-empty-tiers-msg">No ticket tiers are currently available for this gathering.</p>
                        <?php else: ?>
                            <?php foreach ($ticketTypes as $tt):
                                $sold = (int)($tt['sold'] ?? 0);
                                $capacity = (int)($tt['capacity'] ?? 0);
                                $remaining = max(0, $capacity - $sold);
                                $isSoldOut = ($remaining <= 0);
                            ?>
                                <div class="ve-tier-stub<?= $isSoldOut ? ' sold-out' : '' ?>"
                                     data-price="<?= (float)$tt['price'] ?>"
                                     data-label="<?= e($tt['name']) ?>"
                                     data-ticket-id="<?= $tt['id'] ?>">

                                    <!-- Ticket Information Header -->
                                    <div class="ve-tier-info">
                                        <div class="ve-tier-title-row">
                                            <h3 class="ve-tier-name"><?= e($tt['name']) ?></h3>
                                            <div class="ve-tier-badges">
                                                <?php if ($isSoldOut): ?>
                                                    <span class="ve-tier-badge ve-tier-badge--soldout">Sold Out</span>
                                                <?php elseif ($remaining <= 15): ?>
                                                    <span class="ve-tier-badge ve-tier-badge--urgency">🔥 <?= $remaining ?> left</span>
                                                <?php endif; ?>
                                                <?php if (!empty($tt['max_per_person'])): ?>
                                                    <span class="ve-tier-badge ve-tier-badge--limit">Max <?= (int)$tt['max_per_person'] ?>/person</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <?php if (!empty($tt['description'])): ?>
                                            <p class="ve-tier-desc"><?= e($tt['description']) ?></p>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Perforated separator for mobile & tablet -->
                                    <div class="ve-tier-divider" aria-hidden="true"></div>

                                    <!-- Bottom Action Row: Price & Quantity Stepper -->
                                    <div class="ve-tier-bottom-row">
                                        <div class="ve-tier-price-box">
                                            <span class="ve-price-lbl">Per Admission</span>
                                            <div class="ve-tier-price">
                                                <?= ((float)$tt['price'] > 0) ? format_price($tt['price']) : 'Free' ?>
                                            </div>
                                        </div>

                                        <div class="ve-stepper-wrap">
                                            <div class="ve-stepper">
                                                <button type="button" class="ve-stepper-btn" data-action="decrease" <?= $isSoldOut ? 'disabled' : '' ?> aria-label="Decrease quantity">−</button>
                                                <span class="ve-stepper-val" aria-live="polite">0</span>
                                                <input type="hidden" name="qty[<?= $tt['id'] ?>]" class="qty-input" value="0" data-max="<?= $remaining ?>" data-max-per-person="<?= !empty($tt['max_per_person']) ? (int)$tt['max_per_person'] : '' ?>">
                                                <button type="button" class="ve-stepper-btn" data-action="increase" <?= $isSoldOut ? 'disabled' : '' ?> aria-label="Increase quantity">+</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Right: Printed Ledger Receipt -->
                <aside class="ve-receipt-card">
                    <div class="ve-receipt-header">
                        <span class="ve-receipt-seal">✦ Admission Summary ✦</span>
                        <h3 class="ve-receipt-title">Order Ledger</h3>
                    </div>

                    <div class="ve-receipt-lines" id="summary-items">
                        <p class="ve-empty-summary-msg">
                            Select admission passes on the left to tally your order.
                        </p>
                    </div>

                    <div class="ve-receipt-total">
                        <span class="ve-total-lbl">Total Amount</span>
                        <strong class="ve-total-val" id="order-total"><?= format_price(0) ?></strong>
                    </div>

                    <button type="submit" id="checkout-submit-btn" class="ve-checkout-btn" disabled>
                        <span>Reserve Admissions</span>
                        <span>→</span>
                    </button>
                </aside>
            </div>

            <!-- Mobile Floating Bottom Checkout Bar (Only visible when tickets selected on mobile) -->
            <div id="ve-mobile-bar" class="ve-mobile-bar" aria-live="polite">
                <div class="ve-mobile-bar-summary">
                    <span class="ve-mobile-bar-qty" id="mobile-bar-qty">0 Passes Selected</span>
                    <strong class="ve-mobile-bar-total" id="mobile-bar-total"><?= format_price(0) ?></strong>
                </div>
                <button type="submit" class="ve-mobile-bar-btn" id="mobile-bar-btn">
                    <span>Reserve Tickets</span>
                    <span>→</span>
                </button>
            </div>
        </form>
    <?php endif; ?>

    <!-- ═══ 5. Narrative Description & Special Guests ═══ -->
    <section class="ve-narrative-card">
        <h2 class="ve-narrative-head">The Full Chronicle</h2>
        <div class="ve-narrative-body">
            <?= nl2br(e($event->description)) ?>
        </div>

        <?php
        $validGuests = [];
        if (!empty($eventGuests)) {
            foreach ($eventGuests as $guest) {
                $name = !empty($guest->name) ? $guest->name : ($guest['name'] ?? '');
                if (!empty(trim((string)$name))) $validGuests[] = $guest;
            }
        }
        ?>
        <?php if (!empty($validGuests)): ?>
            <div class="ve-guests-section">
                <h3 class="ve-guests-title">
                    ✦ Honored Guests &amp; Performers
                </h3>
                <div class="ve-guests-grid">
                    <?php foreach ($validGuests as $guest):
                        $gName = !empty($guest->name) ? (string)$guest->name : (string)($guest['name'] ?? '');
                        $gRole = !empty($guest->role) ? (string)$guest->role : (string)($guest['role'] ?? '');
                    ?>
                        <div class="ve-guest-item">
                            <div class="ve-host-avatar ve-guest-avatar">
                                <?= strtoupper(substr($gName, 0, 1)) ?>
                            </div>
                            <div class="ve-min-w-0">
                                <strong class="ve-guest-name"><?= e($gName) ?></strong>
                                <?php if (!empty($gRole)): ?>
                                    <span class="ve-guest-role"><?= e($gRole) ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Admissions Policy Strip -->
        <div class="ve-policy-strip">
            <div><strong>✦ Gate Policy:</strong> Present your scannable digital QR pass upon arrival for entry verification.</div>
            <div><strong>✦ Direct Settlement:</strong> Ticket funds are disbursed securely to the event host upon conclusion.</div>
        </div>
    </section>

</div>

<!-- ═══ Interactive Ticket & Order Calculation JS ═══ -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    var rows = document.querySelectorAll('.ve-tier-stub');
    var summaryItems = document.getElementById('summary-items');
    var orderTotal = document.getElementById('order-total');
    var submitBtn = document.getElementById('checkout-submit-btn');

    // Mobile bar elements
    var mobileBar = document.getElementById('ve-mobile-bar');
    var mobileBarQty = document.getElementById('mobile-bar-qty');
    var mobileBarTotal = document.getElementById('mobile-bar-total');

    var currencySymbol = '<?= e(get_currency_symbol()) ?>';
    function formatCurrencyJS(amount) {
        return currencySymbol + Number(amount || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function updateSummary() {
        var total = 0;
        var totalQty = 0;
        var itemsHtml = '';

        rows.forEach(function(row) {
            var price = parseFloat(row.getAttribute('data-price')) || 0;
            var label = row.getAttribute('data-label') || 'Ticket';
            var qtyInput = row.querySelector('.qty-input');
            var qty = parseInt(qtyInput ? qtyInput.value : 0, 10) || 0;

            if (qty > 0) {
                row.classList.add('is-selected');
                var subtotal = price * qty;
                total += subtotal;
                totalQty += qty;
                itemsHtml += '<div class="ve-receipt-row"><span>' + qty + 'x ' + label + '</span><strong>' + (subtotal > 0 ? formatCurrencyJS(subtotal) : 'Free') + '</strong></div>';
            } else {
                row.classList.remove('is-selected');
            }
        });

        if (totalQty === 0) {
            if (summaryItems) summaryItems.innerHTML = '<p class="ve-empty-summary-msg">Select admission passes on the left to tally your order.</p>';
            if (orderTotal) orderTotal.textContent = formatCurrencyJS(0);
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.textContent = 'Reserve Admissions →';
            }
            if (mobileBar) {
                mobileBar.classList.remove('is-visible');
            }
            if (mobileBarTotal) mobileBarTotal.textContent = formatCurrencyJS(0);
        } else {
            var formattedTotal = (total > 0) ? formatCurrencyJS(total) : 'Free';
            if (summaryItems) summaryItems.innerHTML = itemsHtml;
            if (orderTotal) orderTotal.textContent = formattedTotal;
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.textContent = (total > 0) ? 'Reserve (' + totalQty + ') • ' + formattedTotal + ' →' : 'Reserve Free Passes (' + totalQty + ') →';
            }
            if (mobileBar) {
                mobileBar.classList.add('is-visible');
                if (mobileBarQty) mobileBarQty.textContent = totalQty + (totalQty === 1 ? ' Pass Selected' : ' Passes Selected');
                if (mobileBarTotal) mobileBarTotal.textContent = formattedTotal;
            }
        }
    }

    rows.forEach(function(row) {
        var decreaseBtn = row.querySelector('[data-action="decrease"]');
        var increaseBtn = row.querySelector('[data-action="increase"]');
        var qtyVal = row.querySelector('.ve-stepper-val');
        var qtyInput = row.querySelector('.qty-input');
        var maxCapacity = parseInt(qtyInput ? qtyInput.getAttribute('data-max') : 99, 10) || 99;
        var maxPerPerson = qtyInput ? qtyInput.getAttribute('data-max-per-person') : null;
        maxPerPerson = maxPerPerson ? parseInt(maxPerPerson, 10) : null;
        var effectiveMax = (maxPerPerson && maxPerPerson > 0) ? Math.min(maxCapacity, maxPerPerson) : maxCapacity;

        if (decreaseBtn && increaseBtn && qtyVal && qtyInput) {
            decreaseBtn.addEventListener('click', function () {
                var current = parseInt(qtyInput.value, 10) || 0;
                if (current > 0) {
                    current--;
                    qtyInput.value = current;
                    qtyVal.textContent = current;
                    updateSummary();
                }
            });

            increaseBtn.addEventListener('click', function () {
                var current = parseInt(qtyInput.value, 10) || 0;
                if (current < effectiveMax) {
                    current++;
                    qtyInput.value = current;
                    qtyVal.textContent = current;
                    updateSummary();
                } else if (maxPerPerson && maxPerPerson > 0 && current >= maxPerPerson) {
                    alert('Limit reached: Maximum ' + maxPerPerson + ' ticket(s) per person for this tier.');
                }
            });
        }
    });

    /* ── Live Countdown Timer ── */
    var countdownBanner = document.getElementById('ticket-countdown-banner');
    if (countdownBanner) {
        var closeTimestamp = parseInt(countdownBanner.getAttribute('data-close-timestamp'), 10) * 1000;
        var cdHours = document.getElementById('cd-hours');
        var cdMinutes = document.getElementById('cd-minutes');
        var cdSeconds = document.getElementById('cd-seconds');

        function tick() {
            var diff = closeTimestamp - Date.now();
            if (diff <= 0) {
                if (cdHours) cdHours.textContent = '00';
                if (cdMinutes) cdMinutes.textContent = '00';
                if (cdSeconds) cdSeconds.textContent = '00';
                return;
            }
            var secs = Math.floor(diff / 1000);
            var h = Math.floor(secs / 3600);
            var m = Math.floor((secs % 3600) / 60);
            var s = secs % 60;
            if (cdHours) cdHours.textContent = String(h).padStart(2, '0');
            if (cdMinutes) cdMinutes.textContent = String(m).padStart(2, '0');
            if (cdSeconds) cdSeconds.textContent = String(s).padStart(2, '0');
        }
        tick();
        setInterval(tick, 1000);
    }

    /* ── Copy Link ── */
    var copyBtn = document.getElementById('copy-share-link');
    var copyText = document.getElementById('copy-btn-text');
    if (copyBtn) {
        copyBtn.addEventListener('click', function() {
            var url = '<?= addslashes($shareUrl) ?>';
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(url).then(function() {
                    if (copyText) copyText.textContent = 'Copied to Clipboard!';
                    setTimeout(function() { if (copyText) copyText.textContent = 'Copy Event Link'; }, 2000);
                });
            } else {
                var ta = document.createElement('textarea');
                ta.value = url;
                document.body.appendChild(ta);
                ta.select();
                try {
                    document.execCommand('copy');
                    if (copyText) copyText.textContent = 'Copied!';
                    setTimeout(function() { if (copyText) copyText.textContent = 'Copy Event Link'; }, 2000);
                } catch(e) {}
                document.body.removeChild(ta);
            }
        });
    }
});
</script>
@endsection
