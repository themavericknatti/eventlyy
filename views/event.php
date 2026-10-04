<?php

/**
 * Event Page
 *
 * Displays single event details, dynamic ticket tier selection, order calculation, and checkout redirection.
 *
 * @package Eventlyy
 * @subpackage Views/Event
 * @author Celio Natti
 * @license MIT License
 * @version 2.0.0
 */

?>

<?php
$eventImg = !empty($event->image) ? $event->image : 'https://images.unsplash.com/photo-1506157786151-b8491531f063?w=1200&q=80&auto=format&fit=crop';
$rawDesc = $event->description ?? '';
$heroSummary = (mb_strlen($rawDesc) > 150) ? mb_strimwidth($rawDesc, 0, 150, '…') : $rawDesc;
$eventDate = strtotime($event->date ?? '');
$now = time();
$liveWindowEnd = $eventDate ? $eventDate + (event_completion_hours() * 3600) : 0;
$isCompleted = ($eventDate && $now > $liveWindowEnd) || in_array(strtolower($event->status ?? ''), ['completed', 'past', 'cancelled']);
$isLiveNow = !$isCompleted && ($eventDate && $now >= $eventDate && $now <= $liveWindowEnd);
$isToday = !$isCompleted && ($eventDate && date('Y-m-d', $now) === date('Y-m-d', $eventDate));
$eventIdentifier = !empty($event->custom_url) ? $event->custom_url : ($event->slug ?? $event->id);
$shareUrl = url(event_url($event));
$shareText = urlencode("Check out " . ($event->name ?? 'this event') . " on Eventlyy!");
$encodedShareUrl = urlencode($shareUrl);
?>

@extends('layouts/main')

@section('title')
<?= e($event->name) ?> - Eventlyy
@endsection

@section('content')

<!-- ═══ Event Hero ═══ -->
<section class="ep-hero">
    <div class="ep-hero__content">
        <!-- Status & Category Badges -->
        <div class="ep-badges">
            <?php if ($isCompleted): ?>
                <span class="ep-badge ep-badge--completed">🏁 Event Completed</span>
            <?php elseif ($isLiveNow): ?>
                <span class="ep-badge ep-badge--live">
                    <span class="ep-badge__dot"></span> Live Now — Gate Tickets Available
                </span>
            <?php elseif ($isToday): ?>
                <span class="ep-badge ep-badge--today">⚡ Happening Today</span>
            <?php endif; ?>
            <span class="ep-badge ep-badge--category"><?= e($event->category ?? 'Event') ?></span>
            <?php if (!empty($event->flyer_page_enabled)): ?>
                <a href="<?= e(event_flyer_url($event)) ?>" class="ep-badge ep-badge--flyer" title="View immersive flyer">
                    🎨 Flyer View
                </a>
            <?php endif; ?>
        </div>

        <!-- Event Title -->
        <h1 class="ep-title"><?= e($event->name) ?></h1>
        <p class="ep-summary"><?= e($heroSummary) ?></p>

        <!-- Quick Info Cards -->
        <div class="ep-meta">
            <div class="ep-meta__item">
                <span class="ep-meta__icon">📅</span>
                <div class="ep-meta__text">
                    <span class="ep-meta__label">Date</span>
                    <span class="ep-meta__value"><?= $eventDate ? date('M d, Y', $eventDate) : 'TBA' ?></span>
                </div>
            </div>
            <div class="ep-meta__item">
                <span class="ep-meta__icon">⏰</span>
                <div class="ep-meta__text">
                    <span class="ep-meta__label">Time</span>
                    <span class="ep-meta__value"><?= $eventDate ? date('g:i A', $eventDate) : 'TBA' ?></span>
                </div>
            </div>
            <div class="ep-meta__item">
                <span class="ep-meta__icon">📍</span>
                <div class="ep-meta__text">
                    <span class="ep-meta__label">Location</span>
                    <span class="ep-meta__value" title="<?= e($event->location) ?>"><?= e($event->location) ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Event Image & Organizer Sidebar -->
    <aside class="ep-sidebar-card">
        <div class="ep-image" id="event-image-container" title="Click to view full image">
            <div class="ep-image__blur" style="background-image: url('<?= e($eventImg) ?>');"></div>
            <img src="<?= e($eventImg) ?>" alt="<?= e($event->name) ?>" class="ep-image__full" id="event-image-main" />
        </div>
        <div class="ep-card-body">
            <!-- Organizer Info -->
            <div class="ep-organizer">
                <span class="au-avatar" style="--avatar-hue:<?= abs(crc32($event->organizer_name ?? 'Host')) % 360 ?>;">
                    <?= strtoupper(substr($event->organizer_name ?? 'H', 0, 2)) ?>
                </span>
                <div class="ep-organizer__info">
                    <strong><?= e($event->organizer_name ?? 'Event Organizer') ?></strong>
                    <span class="ep-organizer__rating">★ <?= number_format($event->organizer_rating ?? 5.0, 1) ?> Host Rating</span>
                </div>
            </div>
            <p class="ep-organizer__bio">
                <?= e($event->organizer_bio ?? 'Verified Eventlyy organizer creating memorable live events.') ?>
            </p>

            <!-- Tags -->
            <div class="ep-tags">
                <span><?= e($event->category ?? 'Live Event') ?></span>
                <span>✓ Verified Host</span>
                <span>⚡ Instant Delivery</span>
            </div>

            <!-- Share Event -->
            <div class="ep-action-block">
                <span class="ep-action-block__label">Share Event</span>
                <div class="ep-action-block__buttons">
                    <a href="https://api.whatsapp.com/send?text=<?= $shareText ?>%20<?= $encodedShareUrl ?>" target="_blank" rel="noopener" class="ep-share-btn ep-share-btn--whatsapp" title="Share on WhatsApp">
                        <svg width="14" height="14" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                        WhatsApp
                    </a>
                    <a href="https://twitter.com/intent/tweet?url=<?= $encodedShareUrl ?>&text=<?= $shareText ?>" target="_blank" rel="noopener" class="ep-share-btn ep-share-btn--twitter" title="Share on X">
                        <svg width="13" height="13" fill="currentColor" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                        Twitter
                    </a>
                    <a href="https://www.facebook.com/sharer/sharer.php?u=<?= $encodedShareUrl ?>" target="_blank" rel="noopener" class="ep-share-btn ep-share-btn--facebook" title="Share on Facebook">
                        <svg width="14" height="14" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                        Facebook
                    </a>
                    <button type="button" id="copy-share-link" class="ep-share-btn ep-share-btn--copy" title="Copy Link">
                        🔗 <span id="copy-btn-text">Copy Link</span>
                    </button>
                </div>
            </div>

            <!-- Add to Calendar -->
            <div class="ep-action-block">
                <span class="ep-action-block__label">📅 Add to Calendar</span>
                <div class="ep-action-block__buttons">
                    <a href="<?= event_google_calendar_url($event) ?>" target="_blank" rel="noopener" class="ep-calendar-btn" title="Add to Google Calendar">Google</a>
                    <a href="<?= event_ics_url($event) ?>" download class="ep-calendar-btn" title="Download iCal">Apple / iCal</a>
                    <a href="<?= event_outlook_calendar_url($event) ?>" target="_blank" rel="noopener" class="ep-calendar-btn" title="Add to Outlook">Outlook</a>
                </div>
            </div>
        </div>
    </aside>
</section>

<!-- ═══ Tickets or Completed Banner ═══ -->
<?php if ($isCompleted): ?>
    <div class="ep-completed-banner">
        <span class="ep-completed-banner__icon">🏁</span>
        <h3>Ticket Sales Closed</h3>
        <p>This event concluded on <?= date('F j, Y \a\t g:i A', strtotime($event->date)) ?>. Ticket purchasing is no longer available for past events.</p>
        <a href="<?= route('event/' . $eventIdentifier . '/feedback') ?>" class="ep-completed-banner__btn">
            ⭐ Rate &amp; Review This Event
        </a>
    </div>
<?php else: ?>
    <form id="tickets-form" method="GET" action="{{ route('checkout/{event_id}', ['event_id' => $eventIdentifier]) }}">
        <input type="hidden" name="event_id" value="<?= $eventIdentifier ?>" />
        <?php if (!empty($refCode) || !empty($_GET['ref'])): ?>
            <input type="hidden" name="ref" value="<?= e($refCode ?? ($_GET['ref'] ?? '')) ?>" />
        <?php endif; ?>

        <section class="ep-tickets">
            <div class="ep-tickets__main">
                <div class="ep-tickets__header">
                    <h2>
                        Select Tickets
                        <?php if ($isLiveNow): ?>
                            <span class="ep-badge ep-badge--live-sm">🔴 Live Gate Sales</span>
                        <?php endif; ?>
                    </h2>
                    <p class="ep-tickets__note">
                        <?= $isLiveNow ? 'Event is currently live! Choose ticket tiers below for instant gate entry with digital QR code.' : 'Choose your desired ticket tiers below and preview your order summary.' ?>
                    </p>
                </div>

                <div class="ep-ticket-list">
                    <?php if (empty($ticketTypes)): ?>
                        <div class="ep-ticket-list__empty">
                            No ticket tiers available for this event currently.
                        </div>
                    <?php else: ?>
                        <?php foreach ($ticketTypes as $index => $tt):
                            $sold = (int)($tt['sold'] ?? 0);
                            $capacity = (int)($tt['capacity'] ?? 0);
                            $remaining = max(0, $capacity - $sold);
                            $isSoldOut = ($remaining <= 0);
                            $defaultQty = 0;
                        ?>
                            <div class="ep-ticket<?= $isSoldOut ? ' ep-ticket--soldout' : '' ?>"
                                 data-price="<?= (float)$tt['price'] ?>"
                                 data-label="<?= e($tt['name']) ?>"
                                 data-ticket-id="<?= $tt['id'] ?>">
                                <div class="ep-ticket__info">
                                    <div class="ep-ticket__name-row">
                                        <h3><?= e($tt['name']) ?></h3>
                                        <?php if ($isSoldOut): ?>
                                            <span class="ep-ticket-badge ep-ticket-badge--soldout">Sold Out</span>
                                        <?php elseif ($remaining <= 15): ?>
                                            <span class="ep-ticket-badge ep-ticket-badge--low"><?= $remaining ?> left</span>
                                        <?php endif; ?>
                                    </div>
                                    <?php if (!empty($tt['description'])): ?>
                                        <p class="ep-ticket__desc"><?= e($tt['description']) ?></p>
                                    <?php else: ?>
                                        <p class="ep-ticket__desc">
                                            <a href="#about-event" class="ep-ticket__desc-link" title="See event description for details">
                                                ℹ️ Read description for ticket details
                                            </a>
                                        </p>
                                    <?php endif; ?>
                                </div>

                                <div class="ep-ticket__controls">
                                    <button type="button" class="qty-btn" data-action="decrease" <?= $isSoldOut ? 'disabled' : '' ?>
                                        aria-label="Decrease <?= e($tt['name']) ?> quantity">−</button>
                                    <span class="qty-value" aria-live="polite"><?= $defaultQty ?></span>
                                    <input type="hidden" name="qty[<?= $tt['id'] ?>]" class="qty-input" value="<?= $defaultQty ?>" data-max="<?= $remaining ?>">
                                    <button type="button" class="qty-btn" data-action="increase" <?= $isSoldOut ? 'disabled' : '' ?>
                                        aria-label="Increase <?= e($tt['name']) ?> quantity">+</button>
                                </div>

                                <div class="ep-ticket__price<?= ((float)$tt['price'] <= 0) ? ' ep-ticket__price--free' : '' ?>">
                                    <?= ((float)$tt['price'] > 0) ? format_price($tt['price']) : 'Free' ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Order Summary Sidebar -->
            <aside class="ep-summary">
                <div class="ep-summary__head">
                    <h3>Your Order Summary</h3>
                    <p>Review ticket selection and total cost.</p>
                </div>
                <div class="ep-summary__items" id="summary-items"></div>
                <div class="ep-summary__total">
                    <span>Total Amount</span>
                    <strong id="order-total">Free</strong>
                </div>
                <button type="submit" id="checkout-submit-btn" class="ep-checkout-btn">
                    Continue to Checkout →
                </button>
            </aside>
        </section>
    </form>
<?php endif; ?>

<!-- ═══ About This Event ═══ -->
<section class="ep-about" id="about-event">
    <h2>About This Event</h2>
    <div class="ep-description"><?= e($event->description) ?></div>

    <?php
    $validGuests = [];
    if (!empty($eventGuests)) {
        foreach ($eventGuests as $guest) {
            $name = !empty($guest->name) ? $guest->name : ($guest['name'] ?? '');
            if (!empty(trim((string)$name))) {
                $validGuests[] = $guest;
            }
        }
    }
    ?>
    <?php if (!empty($validGuests)): ?>
    <div class="ep-guests">
        <h3><span>✨</span> Special Guests</h3>
        <div class="guest-cards-grid">
            <?php foreach ($validGuests as $guest):
                $gName = !empty($guest->name) ? (string)$guest->name : (string)($guest['name'] ?? '');
                $gRole = !empty($guest->role) ? (string)$guest->role : (string)($guest['role'] ?? '');
                $gSocial = !empty($guest->social) ? (string)$guest->social : (string)($guest['social'] ?? '');
                $socialLinks = parse_guest_socials($gSocial);
            ?>
                <div class="guest-card-item">
                    <div class="guest-card-header">
                        <span class="au-avatar guest-avatar" style="--avatar-hue:<?= abs(crc32($gName)) % 360 ?>;">
                            <?= strtoupper(substr($gName, 0, 2)) ?>
                        </span>
                        <div class="guest-info">
                            <strong class="guest-name"><?= e($gName) ?></strong>
                            <?php if (!empty($gRole)): ?>
                                <span class="guest-role"><?= e($gRole) ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php if (!empty($socialLinks)): ?>
                        <div class="guest-social-list">
                            <?php foreach ($socialLinks as $soc): ?>
                                <a href="<?= e($soc['url']) ?>" target="_blank" rel="noopener noreferrer"
                                   class="guest-social-btn guest-social-<?= e($soc['type']) ?>" title="<?= e($soc['title']) ?>">
                                    <?= $soc['icon'] ?>
                                    <span class="guest-social-label"><?= e($soc['label']) ?></span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <h3 class="ep-highlights__title">Event Highlights &amp; Access Info</h3>
    <ul class="ep-highlights">
        <li><strong>Instant Ticket Delivery:</strong> Receive digital tickets with QR code validation immediately upon purchase.</li>
        <li><strong>Venue Location:</strong> <?= e($event->location) ?></li>
        <li><strong>Organizer:</strong> <?= e($event->organizer_name ?? 'Eventlyy Host') ?> (<?= e($event->organizer_email ?? '') ?>)</li>
        <li><strong>Ticketing Policy:</strong> <?= !empty($event->pass_fee) ? e(get_setting('platform_fee', 5)) . '% platform fee is applied to attendee purchases.' : 'Platform fees are covered by the event organizer.' ?></li>
    </ul>
</section>

<!-- ═══ Interactive Ticket & Order JS ═══ -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    var rows = document.querySelectorAll('.ep-ticket');
    var summaryItems = document.getElementById('summary-items');
    var orderTotal = document.getElementById('order-total');
    var submitBtn = document.getElementById('checkout-submit-btn');

    var currencySymbol = '<?= e(get_currency_symbol()) ?>';
    function formatCurrencyJS(amount) {
        if (amount <= 0) return 'Free';
        return currencySymbol + amount.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
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
                var subtotal = price * qty;
                total += subtotal;
                totalQty += qty;
                itemsHtml += '<div class="ep-summary__row"><span>' + qty + 'x ' + label + '</span><strong>' + formatCurrencyJS(subtotal) + '</strong></div>';
            }
        });

        if (totalQty === 0) {
            summaryItems.innerHTML = '<p class="ep-summary__empty">Select at least 1 ticket to proceed.</p>';
            orderTotal.textContent = 'Free';
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.style.opacity = '0.5';
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

    rows.forEach(function(row) {
        var decreaseBtn = row.querySelector('[data-action="decrease"]');
        var increaseBtn = row.querySelector('[data-action="increase"]');
        var qtyVal = row.querySelector('.qty-value');
        var qtyInput = row.querySelector('.qty-input');
        var maxCapacity = parseInt(qtyInput.getAttribute('data-max'), 10) || 99;

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
                if (current < maxCapacity) {
                    current++;
                    qtyInput.value = current;
                    qtyVal.textContent = current;
                    updateSummary();
                }
            });
        }
    });

    /* ── Copy Link (cross-device) ── */
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
        try { successful = document.execCommand('copy'); } catch (err) { successful = false; }
        document.body.removeChild(textArea);
        callback(successful);
    }

    var copyBtn = document.getElementById('copy-share-link');
    var copyText = document.getElementById('copy-btn-text');
    if (copyBtn && copyText) {
        copyBtn.addEventListener('click', function () {
            copyTextToClipboard(window.location.href, function (success) {
                if (success) {
                    copyText.textContent = 'Copied!';
                    copyBtn.style.borderColor = '#10b981';
                    copyBtn.style.color = '#10b981';
                    setTimeout(function () {
                        copyText.textContent = 'Copy Link';
                        copyBtn.style.borderColor = '';
                        copyBtn.style.color = '';
                    }, 2000);
                } else {
                    copyText.textContent = 'Copy Failed';
                    setTimeout(function () { copyText.textContent = 'Copy Link'; }, 2000);
                }
            });
        });
    }

    /* ── Image Lightbox ── */
    var imageContainer = document.getElementById('event-image-container');
    var lightboxModal = document.getElementById('image-lightbox-modal');
    var lightboxImg = document.getElementById('lightbox-img');
    var lightboxClose = document.getElementById('lightbox-close');

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
        if (lightboxClose) lightboxClose.addEventListener('click', closeLightbox);
        lightboxModal.addEventListener('click', function (e) {
            if (e.target === lightboxModal) closeLightbox();
        });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && lightboxModal.style.display === 'flex') closeLightbox();
        });
    }

    updateSummary();
});
</script>

<!-- Fullscreen Image Lightbox -->
<div id="image-lightbox-modal" class="image-lightbox-modal" style="display:none;">
    <span class="lightbox-close" id="lightbox-close">&times;</span>
    <img class="lightbox-content" id="lightbox-img" alt="<?= e($event->name) ?> full cover image">
</div>
@endsection