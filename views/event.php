<?php

/**
 * Event Page
 *
 * Displays single event details, dynamic ticket tier selection, order calculation, and checkout redirection.
 *
 * @package Evently
 * @subpackage Views/Event
 * @author Your Name <Celio Natti>
 * @license MIT License
 * @version 1.0.0
 */

?>

<?php
$eventImg = !empty($event->image) ? $event->image : 'https://images.unsplash.com/photo-1506157786151-b8491531f063?w=1200&q=80&auto=format&fit=crop';
$rawDesc = $event->description ?? '';
$heroSummary = (mb_strlen($rawDesc) > 130) ? mb_strimwidth($rawDesc, 0, 130, '...') : $rawDesc;
$eventDate = strtotime($event->date ?? '');
$now = time();
$liveWindowEnd = $eventDate ? $eventDate + (event_completion_hours() * 3600) : 0;
$isCompleted = ($eventDate && $now > $liveWindowEnd) || in_array(strtolower($event->status ?? ''), ['completed', 'past', 'cancelled']);
$isLiveNow = !$isCompleted && ($eventDate && $now >= $eventDate && $now <= $liveWindowEnd);
$isToday = !$isCompleted && ($eventDate && date('Y-m-d', $now) === date('Y-m-d', $eventDate));
?>

@extends('layouts/main')

@section('title')
<?= e($event->name) ?> - Evently
@endsection

@section('content')
<section class="detail-hero">
    <div class="detail-hero-copy">
        <div style="display:flex; gap:0.5rem; align-items:center; margin-bottom:0.5rem; flex-wrap:wrap;">
            <span class="eyebrow" style="margin:0;">Event Details</span>
            <?php if ($isCompleted): ?>
                <span style="background:rgba(100, 116, 139, 0.15); color:rgb(100, 116, 139); font-size:0.78rem; font-weight:700; padding:0.35rem 0.75rem; border-radius:20px;">🏁 Event Completed</span>
            <?php elseif ($isLiveNow): ?>
                <span style="background:rgba(239, 68, 68, 0.15); color:#ef4444; border:1px solid rgba(239, 68, 68, 0.35); font-size:0.78rem; font-weight:800; padding:0.35rem 0.75rem; border-radius:20px; display:inline-flex; align-items:center; gap:5px;">
                    <span style="width:7px; height:7px; border-radius:50%; background:#ef4444; display:inline-block;"></span>
                    🔴 Live Now — Buy Tickets at the Gate
                </span>
            <?php elseif ($isToday): ?>
                <span style="background:rgba(16, 185, 129, 0.15); color:#10b981; border:1px solid rgba(16, 185, 129, 0.35); font-size:0.78rem; font-weight:800; padding:0.35rem 0.75rem; border-radius:20px; display:inline-flex; align-items:center; gap:5px;">
                    ⚡ Happening Today — Tickets Available
                </span>
            <?php else: ?>
                <span class="role-indicator au-role--host" style="background:rgba(var(--brand),0.12); color:rgb(var(--brand)); font-size:0.78rem; font-weight:700;">
                    <?= e($event->category ?? 'Event') ?>
                </span>
            <?php endif; ?>
            <?php if (!empty($event->flyer_page_enabled)): ?>
                <a href="<?= e(event_flyer_url($event)) ?>" class="role-indicator" style="background:rgba(99,102,241,0.15); color:#6366f1; border:1px solid rgba(99,102,241,0.3); font-size:0.78rem; font-weight:700; text-decoration:none; padding:0.35rem 0.75rem; border-radius:20px; display:inline-flex; align-items:center; gap:0.35rem;" title="View in immersive flyer poster mode">
                    <span>🎨</span> Flyer View
                </a>
            <?php endif; ?>
        </div>
        <h1><?= e($event->name) ?></h1>
        <p class="lede" style="line-height: 1.5; margin-bottom: 1.5rem; color: var(--text-muted); font-size: 1.05rem;">
            <?= e($heroSummary) ?>
        </p>

        <div class="detail-hero-meta">
            <div>
                <strong>Date</strong>
                <span>📅 <?= $eventDate ? date('M d, Y', $eventDate) : 'TBA' ?></span>
            </div>
            <div>
                <strong>Time</strong>
                <span>⏰ <?= $eventDate ? date('g:i A', $eventDate) : 'TBA' ?></span>
            </div>
            <div>
                <strong>Location</strong>
                <span>📍 <?= e($event->location) ?></span>
            </div>
        </div>
    </div>

    <aside class="detail-card">
        <div class="detail-image" id="event-image-container" title="Click to view full image" style="cursor: pointer;">
            <div class="detail-image-blur" style="background-image: url('<?= e($eventImg) ?>');"></div>
            <img src="<?= e($eventImg) ?>" alt="<?= e($event->name) ?>" class="detail-image-full" id="event-image-main" />
        </div>
        <div class="detail-card-content">
            <div style="display:flex; align-items:center; gap:0.6rem; margin-bottom:0.5rem;">
                <span class="au-avatar" style="--avatar-hue:<?= abs(crc32($event->organizer_name ?? 'Host')) % 360 ?>; width:36px; height:36px; font-size:0.85rem;">
                    <?= strtoupper(substr($event->organizer_name ?? 'H', 0, 2)) ?>
                </span>
                <div>
                    <strong style="display:block; font-size:0.92rem; line-height:1.2;"><?= e($event->organizer_name ?? 'Event Organizer') ?></strong>
                    <span style="font-size:0.75rem; color:var(--brand); font-weight:600;">★ <?= number_format($event->organizer_rating ?? 5.0, 1) ?> Host Rating</span>
                </div>
            </div>
            <p class="meta" style="margin-bottom:0.75rem; font-size:0.83rem; color:var(--text-muted);">
                <?= e($event->organizer_bio ?? 'Verified Evently organizer creating memorable live events.') ?>
            </p>
            <div class="detail-tags">
                <span><?= e($event->category ?? 'Live Event') ?></span>
                <span>Verified Host</span>
                <span>Instant Ticket Delivery</span>
            </div>

            <!-- Share Event Links -->
            <?php
            $shareUrl = url(event_url($event));
            $shareText = urlencode("Check out " . ($event->name ?? 'this event') . " on Evently!");
            $encodedShareUrl = urlencode($shareUrl);
            ?>
            <div class="share-event-block" style="margin-top: 1rem; padding-top: 0.85rem; border-top: 1px solid rgba(var(--muted), 0.15);">
                <span style="display:block; font-size: 0.75rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.5rem;">Share Event</span>
                <div style="display: flex; gap: 0.4rem; flex-wrap: wrap;">
                    <a href="https://api.whatsapp.com/send?text=<?= $shareText ?>%20<?= $encodedShareUrl ?>" target="_blank" rel="noopener" class="share-btn share-whatsapp" style="display: inline-flex; align-items: center; gap: 0.35rem; padding: 0.4rem 0.75rem; border-radius: 8px; background: #25D366; color: #fff; text-decoration: none; font-size: 0.8rem; font-weight: 700; transition: opacity 0.2s;" title="Share on WhatsApp">
                        <svg width="14" height="14" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg> WhatsApp
                    </a>
                    <a href="https://twitter.com/intent/tweet?url=<?= $encodedShareUrl ?>&text=<?= $shareText ?>" target="_blank" rel="noopener" class="share-btn share-twitter" style="display: inline-flex; align-items: center; gap: 0.35rem; padding: 0.4rem 0.75rem; border-radius: 8px; background: #000; color: #fff; text-decoration: none; font-size: 0.8rem; font-weight: 700; transition: opacity 0.2s;" title="Share on X (Twitter)">
                        <svg width="13" height="13" fill="currentColor" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg> Twitter
                    </a>
                    <a href="https://www.facebook.com/sharer/sharer.php?u=<?= $encodedShareUrl ?>" target="_blank" rel="noopener" class="share-btn share-facebook" style="display: inline-flex; align-items: center; gap: 0.35rem; padding: 0.4rem 0.75rem; border-radius: 8px; background: #1877F2; color: #fff; text-decoration: none; font-size: 0.8rem; font-weight: 700; transition: opacity 0.2s;" title="Share on Facebook">
                        <svg width="14" height="14" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg> Facebook
                    </a>
                    <button type="button" id="copy-share-link" class="share-btn share-copy" style="display: inline-flex; align-items: center; gap: 0.35rem; padding: 0.4rem 0.75rem; border-radius: 8px; background: rgba(var(--muted), 0.12); color: var(--text); border: 1px solid rgba(var(--muted), 0.25); font-size: 0.8rem; font-weight: 700; cursor: pointer; transition: all 0.2s;" title="Copy Link">
                        🔗 <span id="copy-btn-text">Copy Link</span>
                    </button>
                </div>
            </div>

            <!-- Add to Calendar Links -->
            <div class="calendar-event-block" style="margin-top: 1rem; padding-top: 0.85rem; border-top: 1px solid rgba(var(--muted), 0.15);">
                <span style="display:block; font-size: 0.75rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.5rem;">📅 Add to Calendar</span>
                <div class="calendar-btn-group" style="display: flex; gap: 0.4rem; flex-wrap: wrap;">
                    <a href="<?= event_google_calendar_url($event) ?>" target="_blank" rel="noopener" class="calendar-btn" style="display: inline-flex; align-items: center; gap: 0.35rem; padding: 0.4rem 0.65rem; border-radius: 8px; background: rgba(var(--muted), 0.08); color: var(--text); border: 1px solid rgba(var(--muted), 0.2); font-size: 0.78rem; font-weight: 700; text-decoration: none; transition: all 0.2s;" title="Add to Google Calendar">
                        <span>Google</span>
                    </a>
                    <a href="<?= event_ics_url($event) ?>" download class="calendar-btn" style="display: inline-flex; align-items: center; gap: 0.35rem; padding: 0.4rem 0.65rem; border-radius: 8px; background: rgba(var(--muted), 0.08); color: var(--text); border: 1px solid rgba(var(--muted), 0.2); font-size: 0.78rem; font-weight: 700; text-decoration: none; transition: all 0.2s;" title="Download iCal for Apple Calendar & Outlook">
                        <span>Apple iCal</span>
                    </a>
                    <a href="<?= event_outlook_calendar_url($event) ?>" target="_blank" rel="noopener" class="calendar-btn" style="display: inline-flex; align-items: center; gap: 0.35rem; padding: 0.4rem 0.65rem; border-radius: 8px; background: rgba(var(--muted), 0.08); color: var(--text); border: 1px solid rgba(var(--muted), 0.2); font-size: 0.78rem; font-weight: 700; text-decoration: none; transition: all 0.2s;" title="Add to Outlook Calendar">
                        <span>Outlook</span>
                    </a>
                </div>
            </div>
        </div>
    </aside>
</section>

<?php 
$eventIdentifier = !empty($event->custom_url) ? $event->custom_url : ($event->slug ?? $event->id);
?>
<?php if ($isCompleted): ?>
    <div style="background: rgba(100, 116, 139, 0.08); border: 1px solid rgba(100, 116, 139, 0.2); border-radius: 16px; padding: 2.5rem 1.5rem; text-align: center; margin: 2rem 0;">
        <span style="font-size: 3rem; display: block; margin-bottom: 0.5rem;">🏁</span>
        <h3 style="font-size: 1.35rem; font-weight: 800; margin: 0 0 0.5rem; color: var(--text);">Ticket Sales Closed</h3>
        <p style="color: var(--text-muted); font-size: 0.95rem; max-width: 500px; margin: 0 auto 1.25rem; line-height: 1.5;">
            This event concluded on <?= date('F j, Y \a\t g:i A', strtotime($event->date)) ?>. Ticket purchasing is no longer available for past events.
        </p>
        <a href="<?= route('event/' . $eventIdentifier . '/feedback') ?>" class="btn-primary" style="display: inline-block; padding: 0.75rem 1.75rem; border-radius: 12px; text-decoration: none; font-weight: 700; background: #0B7366; color: white;">
            ⭐ Rate &amp; Review This Event
        </a>
    </div>
<?php else: ?>
    <!-- Dynamic Tickets selection form -->
    <form id="tickets-form" method="GET" action="{{ route('checkout/{event_id}', ['event_id' => $eventIdentifier]) }}">
        <input type="hidden" name="event_id" value="<?= $eventIdentifier ?>" />
        <?php if (!empty($refCode) || !empty($_GET['ref'])): ?>
            <input type="hidden" name="ref" value="<?= e($refCode ?? ($_GET['ref'] ?? '')) ?>" />
        <?php endif; ?>

        <section class="tickets-section">
            <div class="tickets-main">
                <div class="section-header">
                    <div>
                        <h2>
                            Select Tickets
                            <?php if ($isLiveNow): ?>
                                <span style="font-size:0.75rem; background:rgba(239, 68, 68, 0.15); color:#ef4444; border:1px solid rgba(239, 68, 68, 0.35); padding:3px 8px; border-radius:8px; vertical-align:middle; margin-left:6px; font-weight:800;">🔴 Live Gate Sales</span>
                            <?php endif; ?>
                        </h2>
                        <p class="section-note">
                            <?= $isLiveNow ? 'Event is currently live! Choose ticket tiers below for instant gate entry with digital QR code.' : 'Choose your desired ticket tiers below and preview your order summary.' ?>
                        </p>
                    </div>
                </div>

                <div class="ticket-card">
                    <?php if (empty($ticketTypes)): ?>
                        <div style="padding: 2rem; text-align: center; color: var(--text-muted);">
                            No ticket tiers available for this event currently.
                        </div>
                    <?php else: ?>
                        <?php foreach ($ticketTypes as $index => $tt):
                            $sold = (int)($tt['sold'] ?? 0);
                            $capacity = (int)($tt['capacity'] ?? 0);
                            $remaining = max(0, $capacity - $sold);
                            $isSoldOut = ($remaining <= 0);
                            // All ticket tiers default to 0 until user explicitly selects
                            $defaultQty = 0;
                        ?>
                            <div class="ticket-row" 
                                 data-price="<?= (float)$tt['price'] ?>" 
                                 data-label="<?= e($tt['name']) ?>" 
                                 data-ticket-id="<?= $tt['id'] ?>"
                                 style="<?= $isSoldOut ? 'opacity:0.6;' : '' ?>">
                                <div>
                                    <div style="display:flex; align-items:center; gap:0.5rem;">
                                        <h3 style="margin:0;"><?= e($tt['name']) ?></h3>
                                        <?php if ($isSoldOut): ?>
                                            <span style="font-size:0.7rem; background:#ef4444; color:#fff; padding:0.1rem 0.4rem; border-radius:6px; font-weight:700;">Sold Out</span>
                                        <?php elseif ($remaining <= 15): ?>
                                            <span style="font-size:0.7rem; background:rgba(245, 158, 11, 0.12); color:#d97706; border:1px solid rgba(245, 158, 11, 0.25); padding:0.1rem 0.45rem; border-radius:6px; font-weight:700;">
                                                <?= $remaining ?> left
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    <p class="meta" style="margin-top:0.25rem;">Includes full access to <?= e($event->name) ?>.</p>
                                </div>

                                <div class="ticket-controls">
                                    <button type="button" class="qty-btn" data-action="decrease" <?= $isSoldOut ? 'disabled' : '' ?>
                                        aria-label="Decrease <?= e($tt['name']) ?> quantity">−</button>
                                    <span class="qty-value" aria-live="polite"><?= $defaultQty ?></span>
                                    <input type="hidden" name="qty[<?= $tt['id'] ?>]" class="qty-input" value="<?= $defaultQty ?>" data-max="<?= $remaining ?>">
                                    <button type="button" class="qty-btn" data-action="increase" <?= $isSoldOut ? 'disabled' : '' ?>
                                        aria-label="Increase <?= e($tt['name']) ?> quantity">+</button>
                                </div>

                                <div class="ticket-price" style="<?= ((float)$tt['price'] <= 0) ? 'color:#10b981; font-weight:800;' : '' ?>">
                                    <?= ((float)$tt['price'] > 0) ? format_price($tt['price']) : 'Free' ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Order summary sidebar -->
            <aside class="summary-card">
                <div class="summary-head">
                    <h3>Your Order Summary</h3>
                    <p class="meta">Review ticket selection and total cost.</p>
                </div>
                <div class="summary-items" id="summary-items" style="min-height: 50px;">
                    <!-- Dynamically updated by JS -->
                </div>
                <div class="summary-total">
                    <span>Total Amount</span>
                    <strong id="order-total">Free</strong>
                </div>
                <button type="submit" id="checkout-submit-btn" class="checkout-btn" style="border:none; width:100%; cursor:pointer;">
                    Continue to Checkout →
                </button>
            </aside>
        </section>
    </form>
<?php endif; ?>

<!-- Detailed Description & Event Information Section -->
<section class="detail-copy">
    <div>
        <h2>About This Event</h2>
        <div style="font-size: 1.05rem; line-height: 1.8; color: var(--text); background: rgba(var(--muted), 0.03); border-left: 4px solid var(--brand); padding: 1.25rem 1.5rem; border-radius: 0 12px 12px 0; margin-bottom: 1.5rem; white-space: pre-line;">
            <?= e($event->description) ?>
        </div>

        <?php if (!empty($eventGuests)): ?>
        <h3 style="font-size: 1.15rem; font-weight: 700; margin-top: 1.75rem; margin-bottom: 0.75rem;">Special Guests</h3>
        <div style="display:grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap:1rem; margin-bottom:1.5rem;">
            <?php foreach ($eventGuests as $guest): ?>
                <div style="background: rgba(var(--muted), 0.05); border:1px solid rgba(var(--muted), 0.12); border-radius:12px; padding:1rem; display:flex; flex-direction:column; gap:0.25rem;">
                    <div style="display:flex; align-items:center; gap:0.5rem; margin-bottom:0.25rem;">
                        <span class="au-avatar" style="--avatar-hue:<?= abs(crc32($guest['name'])) % 360 ?>; width:32px; height:32px; font-size:0.75rem;">
                            <?= strtoupper(substr($guest['name'], 0, 2)) ?>
                        </span>
                        <div>
                            <strong style="font-size:0.92rem; display:block; line-height:1.2;"><?= e($guest['name']) ?></strong>
                            <?php if (!empty($guest['role'])): ?>
                                <span style="font-size:0.75rem; color:var(--brand); font-weight:600;"><?= e($guest['role']) ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php if (!empty($guest['social'])): ?>
                        <span style="font-size:0.78rem; color:var(--text-muted); word-break:break-all;"><?= e($guest['social']) ?></span>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <h3 style="font-size: 1.15rem; font-weight: 700; margin-top: 1.75rem; margin-bottom: 0.75rem;">Event Highlights &amp; Access Info</h3>
        <ul class="detail-list" style="margin-top: 0.75rem;">
            <li><strong>Instant Ticket Delivery:</strong> Receive digital tickets with QR code validation immediately upon purchase.</li>
            <li><strong>Venue Location:</strong> <?= e($event->location) ?></li>
            <li><strong>Organizer:</strong> <?= e($event->organizer_name ?? 'Evently Host') ?> (<?= e($event->organizer_email ?? '') ?>)</li>
            <li><strong>Ticketing Policy:</strong> <?= !empty($event->pass_fee) ? e(get_setting('platform_fee', 5)) . '% platform fee is applied to attendee purchases.' : 'Platform fees are covered by the event organizer.' ?></li>
        </ul>
    </div>
</section>

<!-- Interactive Order Summary & Quantity JS Logic -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const rows = document.querySelectorAll('.ticket-row');
    const summaryItems = document.getElementById('summary-items');
    const orderTotal = document.getElementById('order-total');
    const submitBtn = document.getElementById('checkout-submit-btn');

    // Currency symbol synced from PHP settings
    const currencySymbol = '<?= e(get_currency_symbol()) ?>';
    function formatCurrencyJS(amount) {
        if (amount <= 0) return 'Free';
        return currencySymbol + amount.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function updateSummary() {
        let total = 0;
        let totalQty = 0;
        let itemsHtml = '';

        rows.forEach(row => {
            const price = parseFloat(row.getAttribute('data-price')) || 0;
            const label = row.getAttribute('data-label') || 'Ticket';
            const qtyInput = row.querySelector('.qty-input');
            const qty = parseInt(qtyInput ? qtyInput.value : 0, 10) || 0;

            if (qty > 0) {
                const subtotal = price * qty;
                total += subtotal;
                totalQty += qty;
                itemsHtml += `
                    <div style="display:flex; justify-content:space-between; margin-bottom:0.5rem; font-size:0.88rem;">
                        <span>${qty}x ${label}</span>
                        <strong>${formatCurrencyJS(subtotal)}</strong>
                    </div>
                `;
            }
        });

        if (totalQty === 0) {
            summaryItems.innerHTML = '<p style="font-size:0.85rem; color:var(--text-muted); margin:0;">Please select at least 1 ticket to proceed.</p>';
            orderTotal.textContent = 'Free';
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.style.opacity = '0.6';
                submitBtn.style.cursor = 'not-allowed';
                submitBtn.textContent = 'Continue to Checkout →';
            }
        } else {
            summaryItems.innerHTML = itemsHtml;
            orderTotal.textContent = (total > 0) ? formatCurrencyJS(total) : 'Free';
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.style.opacity = '1';
                submitBtn.style.cursor = 'pointer';
                submitBtn.textContent = (total > 0) ? 'Continue to Checkout →' : 'Get Free Tickets →';
            }
        }
    }

    rows.forEach(row => {
        const decreaseBtn = row.querySelector('[data-action="decrease"]');
        const increaseBtn = row.querySelector('[data-action="increase"]');
        const qtyVal = row.querySelector('.qty-value');
        const qtyInput = row.querySelector('.qty-input');
        const maxCapacity = parseInt(qtyInput.getAttribute('data-max'), 10) || 99;

        if (decreaseBtn && increaseBtn && qtyVal && qtyInput) {
            decreaseBtn.addEventListener('click', function () {
                let current = parseInt(qtyInput.value, 10) || 0;
                if (current > 0) {
                    current--;
                    qtyInput.value = current;
                    qtyVal.textContent = current;
                    updateSummary();
                }
            });

            increaseBtn.addEventListener('click', function () {
                let current = parseInt(qtyInput.value, 10) || 0;
                if (current < maxCapacity) {
                    current++;
                    qtyInput.value = current;
                    qtyVal.textContent = current;
                    updateSummary();
                }
            });
        }
    });

    // Robust Cross-Device & Mobile Copy Link Helper
    function copyTextToClipboard(textToCopy, callback) {
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(textToCopy)
                .then(function () { callback(true); })
                .catch(function () { fallbackCopyText(textToCopy, callback); });
        } else {
            fallbackCopyText(textToCopy, callback);
        }
    }

    function fallbackCopyText(textToCopy, callback) {
        var textArea = document.createElement("textarea");
        textArea.value = textToCopy;
        textArea.style.position = "fixed";
        textArea.style.top = "-9999px";
        textArea.style.left = "-9999px";
        textArea.style.opacity = "0";
        textArea.setAttribute("readonly", "");

        document.body.appendChild(textArea);

        if (navigator.userAgent.match(/ipad|iphone/i)) {
            var range = document.createRange();
            range.selectNodeContents(textArea);
            var selection = window.getSelection();
            selection.removeAllRanges();
            selection.addRange(range);
            textArea.setSelectionRange(0, 999999);
        } else {
            textArea.focus();
            textArea.select();
        }

        var successful = false;
        try {
            successful = document.execCommand('copy');
        } catch (err) {
            successful = false;
        }

        document.body.removeChild(textArea);
        callback(successful);
    }

    // Copy Share Link JS
    const copyBtn = document.getElementById('copy-share-link');
    const copyText = document.getElementById('copy-btn-text');
    if (copyBtn && copyText) {
        copyBtn.addEventListener('click', function () {
            copyTextToClipboard(window.location.href, function (success) {
                if (success) {
                    copyText.textContent = 'Copied!';
                    copyBtn.style.borderColor = '#10b981';
                    copyBtn.style.color = '#10b981';
                    setTimeout(function () {
                        copyText.textContent = 'Copy Link';
                        copyBtn.style.borderColor = 'rgba(var(--muted), 0.25)';
                        copyBtn.style.color = 'var(--text)';
                    }, 2000);
                } else {
                    copyText.textContent = 'Copy Failed';
                    setTimeout(function () {
                        copyText.textContent = 'Copy Link';
                    }, 2000);
                }
            });
        });
    }

    // Lightbox modal handler
    const imageContainer = document.getElementById('event-image-container');
    const lightboxModal = document.getElementById('image-lightbox-modal');
    const lightboxImg = document.getElementById('lightbox-img');
    const lightboxClose = document.getElementById('lightbox-close');

    if (imageContainer && lightboxModal && lightboxImg) {
        imageContainer.addEventListener('click', function () {
            lightboxImg.src = '<?= e($eventImg) ?>';
            lightboxModal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        });

        function closeLightbox() {
            lightboxModal.style.display = 'none';
            document.body.style.overflow = '';
        }

        if (lightboxClose) {
            lightboxClose.addEventListener('click', closeLightbox);
        }

        lightboxModal.addEventListener('click', function (e) {
            if (e.target === lightboxModal) {
                closeLightbox();
            }
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && lightboxModal.style.display === 'flex') {
                closeLightbox();
            }
        });
    }

    updateSummary();
});
</script>

<!-- Fullscreen Image Lightbox Modal -->
<div id="image-lightbox-modal" class="image-lightbox-modal" style="display:none;">
    <span class="lightbox-close" id="lightbox-close">&times;</span>
    <img class="lightbox-content" id="lightbox-img" alt="<?= e($event->name) ?> full cover image">
</div>
@endsection