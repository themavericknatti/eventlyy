@extends('layouts/embed')

@section('title')
<?= e($event['name'] ?? 'Get Tickets') ?> — Checkout
@endsection

@section('styles')
<style>
    :root {
        --brand: #108a55;
        --brand-light: #e8f5ee;
        --bg: #ffffff;
        --card: #f8fafc;
        --text: #1e293b;
        --muted: #64748b;
        --border: #e2e8f0;
        --radius: 14px;
        --shadow: 0 4px 24px rgba(0,0,0,0.06);
    }

    body { background: var(--bg); padding: 0; }

    .embed-wrap {
        max-width: 480px;
        margin: 0 auto;
        padding: 1.25rem;
    }

    /* ── Event Header ── */
    .embed-header {
        display: flex;
        gap: 1rem;
        align-items: flex-start;
        margin-bottom: 1.5rem;
        padding-bottom: 1.25rem;
        border-bottom: 1px solid var(--border);
    }
    .embed-header-img {
        width: 72px;
        height: 72px;
        border-radius: 12px;
        object-fit: cover;
        flex-shrink: 0;
        background: var(--card);
    }
    .embed-header-info { flex: 1; min-width: 0; }
    .embed-header-info h1 {
        font-size: 1.1rem;
        font-weight: 800;
        color: var(--text);
        margin: 0 0 .3rem;
        line-height: 1.25;
    }
    .embed-header-meta {
        display: flex;
        flex-direction: column;
        gap: .15rem;
        font-size: .82rem;
        color: var(--muted);
    }
    .embed-closed-badge {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        background: #fef2f2;
        color: #dc2626;
        font-weight: 700;
        font-size: .82rem;
        padding: .45rem .85rem;
        border-radius: 999px;
        margin-top: .75rem;
    }

    /* ── Ticket Tier Cards ── */
    .tier-list { display: flex; flex-direction: column; gap: .75rem; margin-bottom: 1.5rem; }
    .tier-card {
        display: flex;
        align-items: center;
        gap: .85rem;
        background: var(--card);
        border: 1.5px solid var(--border);
        border-radius: var(--radius);
        padding: .85rem 1rem;
        transition: border-color .2s, box-shadow .2s;
    }
    .tier-card:hover { border-color: var(--brand); box-shadow: 0 0 0 3px rgba(16,138,85,.08); }
    .tier-card.sold-out { opacity: .55; pointer-events: none; }
    .tier-info { flex: 1; min-width: 0; }
    .tier-name { font-weight: 700; font-size: .95rem; color: var(--text); }
    .tier-price { font-size: .82rem; color: var(--brand); font-weight: 700; margin-top: .1rem; }
    .tier-avail { font-size: .75rem; color: var(--muted); }
    .tier-qty {
        display: flex;
        align-items: center;
        gap: .5rem;
    }
    .tier-qty button {
        width: 32px; height: 32px;
        border-radius: 50%;
        border: 1.5px solid var(--border);
        background: #fff;
        font-size: 1.1rem;
        font-weight: 700;
        color: var(--text);
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all .15s;
    }
    .tier-qty button:hover { border-color: var(--brand); color: var(--brand); }
    .tier-qty button:disabled { opacity: .3; cursor: default; }
    .tier-qty-val {
        width: 28px;
        text-align: center;
        font-weight: 800;
        font-size: 1rem;
        color: var(--text);
    }

    /* ── Form ── */
    .embed-form { display: flex; flex-direction: column; gap: 1rem; }
    .embed-field label {
        display: block;
        font-size: .82rem;
        font-weight: 600;
        color: var(--text);
        margin-bottom: .3rem;
    }
    .embed-field input {
        width: 100%;
        padding: .65rem .85rem;
        border: 1.5px solid var(--border);
        border-radius: 10px;
        font-size: .9rem;
        color: var(--text);
        background: #fff;
        outline: none;
        transition: border-color .2s;
    }
    .embed-field input:focus { border-color: var(--brand); box-shadow: 0 0 0 3px rgba(16,138,85,.1); }

    /* ── Promo Row ── */
    .promo-row {
        display: flex;
        gap: .5rem;
    }
    .promo-row input { flex: 1; }
    .promo-row button {
        padding: .65rem 1rem;
        border: 1.5px solid var(--brand);
        border-radius: 10px;
        background: transparent;
        color: var(--brand);
        font-weight: 700;
        font-size: .82rem;
        cursor: pointer;
        white-space: nowrap;
        transition: all .15s;
    }
    .promo-row button:hover { background: var(--brand); color: #fff; }
    .promo-msg {
        font-size: .78rem;
        margin-top: .25rem;
        font-weight: 600;
    }
    .promo-msg.success { color: var(--brand); }
    .promo-msg.error { color: #dc2626; }

    /* ── Order Summary ── */
    .order-summary {
        background: var(--card);
        border: 1.5px solid var(--border);
        border-radius: var(--radius);
        padding: 1rem;
        margin-bottom: 1.25rem;
    }
    .order-summary h3 {
        font-size: .88rem;
        font-weight: 800;
        margin-bottom: .75rem;
        color: var(--text);
    }
    .summary-row {
        display: flex;
        justify-content: space-between;
        font-size: .82rem;
        padding: .25rem 0;
        color: var(--muted);
    }
    .summary-row.total {
        border-top: 1.5px solid var(--border);
        margin-top: .5rem;
        padding-top: .65rem;
        font-weight: 800;
        font-size: .95rem;
        color: var(--text);
    }
    .summary-row .discount { color: var(--brand); }

    /* ── Submit Button ── */
    .embed-submit {
        width: 100%;
        padding: .85rem;
        background: var(--brand);
        color: #fff;
        border: none;
        border-radius: 12px;
        font-size: 1rem;
        font-weight: 800;
        cursor: pointer;
        transition: all .2s;
        letter-spacing: .02em;
    }
    .embed-submit:hover { filter: brightness(1.08); transform: translateY(-1px); box-shadow: 0 6px 20px rgba(16,138,85,.25); }
    .embed-submit:disabled { opacity: .5; cursor: not-allowed; transform: none; }

    /* ── Powered By ── */
    .powered-by {
        text-align: center;
        margin-top: 1.25rem;
        font-size: .72rem;
        color: #94a3b8;
    }
    .powered-by a { color: var(--brand); text-decoration: none; font-weight: 700; }

    /* ── Error Alert ── */
    .embed-alert {
        padding: .65rem .85rem;
        border-radius: 10px;
        font-size: .82rem;
        font-weight: 600;
        margin-bottom: 1rem;
        display: none;
    }
    .embed-alert.error { background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; display: block; }
    .embed-alert.success { background: #f0fdf4; color: #16a34a; border: 1px solid #bbf7d0; display: block; }

    /* ── Success State ── */
    .embed-success {
        text-align: center;
        padding: 2rem 1rem;
    }
    .embed-success .check-icon {
        width: 56px;
        height: 56px;
        background: var(--brand);
        color: #fff;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        margin-bottom: 1rem;
    }
    .embed-success h2 { font-size: 1.15rem; font-weight: 800; margin-bottom: .4rem; }
    .embed-success p { font-size: .85rem; color: var(--muted); }
    .embed-success .ticket-codes {
        margin-top: 1rem;
        display: flex;
        flex-wrap: wrap;
        gap: .5rem;
        justify-content: center;
    }
    .embed-success .ticket-code {
        background: var(--brand-light);
        color: var(--brand);
        font-family: monospace;
        font-weight: 700;
        font-size: .82rem;
        padding: .35rem .65rem;
        border-radius: 8px;
    }
</style>
@endsection

@section('content')
<div class="embed-wrap" id="embed-checkout">

    <!-- Event Header -->
    <div class="embed-header">
        <?php if (!empty($imageUrl)): ?>
            <img src="<?= e($imageUrl) ?>" alt="<?= e($event['name']) ?>" class="embed-header-img" />
        <?php endif; ?>
        <div class="embed-header-info">
            <h1><?= e($event['name']) ?></h1>
            <div class="embed-header-meta">
                <?php if ($eventDate): ?>
                    <span>📅 <?= date('D, M d, Y • g:i A', $eventDate) ?></span>
                <?php endif; ?>
                <span>📍 <?= e($event['location'] ?? 'TBA') ?></span>
            </div>

            <?php if ($isCompleted): ?>
                <div class="embed-closed-badge">🚫 Sales Closed</div>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($isCompleted || $event['status'] !== 'published'): ?>
        <div class="embed-alert error" style="display:block;">Ticket sales are no longer available for this event.</div>
    <?php else: ?>

    <!-- Alert Container -->
    <div class="embed-alert" id="embed-alert"></div>

    <!-- Success State (hidden by default) -->
    <div class="embed-success" id="embed-success" style="display:none;"></div>

    <!-- Checkout Form -->
    <form id="embed-form" onsubmit="return false;">
        <!-- Ticket Tiers -->
        <div class="tier-list" id="tier-list">
            <?php foreach ($ticketTypes as $tt):
                $cap = (int)$tt['capacity'];
                $sold = (int)$tt['sold'];
                $remaining = max(0, $cap - $sold);
                $isSoldOut = ($cap > 0 && $remaining <= 0);
                $price = (float)$tt['price'];
            ?>
            <div class="tier-card <?= $isSoldOut ? 'sold-out' : '' ?>" data-tier-id="<?= (int)$tt['id'] ?>" data-price="<?= $price ?>">
                <div class="tier-info">
                    <div class="tier-name"><?= e($tt['name']) ?></div>
                    <div class="tier-price">
                        <?= $price > 0 ? $currencySymbol . number_format($price, 2) : 'FREE' ?>
                    </div>
                    <div class="tier-avail">
                        <?= $isSoldOut ? '❌ Sold Out' : ($cap > 0 ? $remaining . ' remaining' : 'Unlimited') ?>
                    </div>
                </div>
                <?php if (!$isSoldOut): ?>
                <div class="tier-qty">
                    <button type="button" onclick="adjustQty(this, -1)" disabled>−</button>
                    <span class="tier-qty-val" data-qty="0">0</span>
                    <button type="button" onclick="adjustQty(this, 1)">+</button>
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Attendee Info -->
        <div class="embed-field">
            <label for="embed-name">Full Name *</label>
            <input type="text" id="embed-name" name="name" required placeholder="Enter your full name" />
        </div>
        <div class="embed-field">
            <label for="embed-email">Email Address *</label>
            <input type="email" id="embed-email" name="email" required placeholder="you@example.com" />
        </div>

        <!-- Promo Code (collapsible) -->
        <details style="margin-bottom: .5rem;">
            <summary style="font-size:.82rem; font-weight:600; color:var(--brand); cursor:pointer;">🏷️ Have a promo code?</summary>
            <div style="margin-top:.5rem;">
                <div class="promo-row">
                    <input type="text" id="embed-promo" placeholder="Enter code" style="text-transform:uppercase;" />
                    <button type="button" id="promo-apply-btn" onclick="applyPromo()">Apply</button>
                </div>
                <div class="promo-msg" id="promo-msg"></div>
            </div>
        </details>

        <!-- Order Summary -->
        <div class="order-summary" id="order-summary" style="display:none;">
            <h3>Order Summary</h3>
            <div id="summary-lines"></div>
            <div class="summary-row" id="summary-discount" style="display:none;">
                <span>Discount</span>
                <span class="discount" id="summary-discount-val">-₦0.00</span>
            </div>
            <?php if ($passFee): ?>
            <div class="summary-row" id="summary-fee">
                <span>Service Fee (<?= $platformFeePercent ?>%)</span>
                <span id="summary-fee-val"><?= $currencySymbol ?>0.00</span>
            </div>
            <div class="summary-row" id="summary-extra">
                <span>Processing</span>
                <span id="summary-extra-val"><?= $currencySymbol . number_format($extraCharge, 2) ?></span>
            </div>
            <?php endif; ?>
            <div class="summary-row total">
                <span>Total</span>
                <span id="summary-total"><?= $currencySymbol ?>0.00</span>
            </div>
        </div>

        <!-- Submit -->
        <button type="button" class="embed-submit" id="embed-submit-btn" disabled onclick="submitCheckout()">
            Select Tickets to Continue
        </button>
    </form>

    <?php endif; ?>

    <div class="powered-by">
        Powered by <a href="<?= e($baseUrl) ?>" target="_blank"><?= e(site_setting('site_name', 'Evently')) ?></a>
    </div>
</div>
@endsection

@section('scripts')
<script>
(function() {
    const API_CHECKOUT = <?= json_encode($apiCheckoutUrl) ?>;
    const API_PROMO    = <?= json_encode($apiPromoUrl) ?>;
    const EVENT_ID     = <?= json_encode($event['id']) ?>;
    const CURRENCY     = <?= json_encode($currencySymbol) ?>;
    const PASS_FEE     = <?= json_encode($passFee) ?>;
    const FEE_PCT      = <?= json_encode($platformFeePercent) ?>;
    const EXTRA        = <?= json_encode($extraCharge) ?>;

    let appliedDiscount = 0;
    let appliedPromoCode = '';

    window.adjustQty = function(btn, delta) {
        const card = btn.closest('.tier-card');
        const valEl = card.querySelector('.tier-qty-val');
        let qty = parseInt(valEl.dataset.qty) || 0;
        qty = Math.max(0, qty + delta);
        valEl.dataset.qty = qty;
        valEl.textContent = qty;

        // Toggle minus button
        const [minusBtn, plusBtn] = card.querySelectorAll('.tier-qty button');
        minusBtn.disabled = qty <= 0;

        recalculate();
    };

    function recalculate() {
        const cards = document.querySelectorAll('.tier-card');
        let subtotal = 0;
        let hasTickets = false;
        let summaryHtml = '';

        cards.forEach(card => {
            const valEl = card.querySelector('.tier-qty-val');
            if (!valEl) return;
            const qty = parseInt(valEl.dataset.qty) || 0;
            if (qty <= 0) return;

            hasTickets = true;
            const price = parseFloat(card.dataset.price) || 0;
            const lineTotal = price * qty;
            subtotal += lineTotal;

            const name = card.querySelector('.tier-name').textContent;
            summaryHtml += '<div class="summary-row"><span>' + qty + 'x ' + escHtml(name) + '</span><span>' + CURRENCY + lineTotal.toFixed(2) + '</span></div>';
        });

        const summaryEl = document.getElementById('order-summary');
        const submitBtn = document.getElementById('embed-submit-btn');

        if (!hasTickets) {
            summaryEl.style.display = 'none';
            submitBtn.disabled = true;
            submitBtn.textContent = 'Select Tickets to Continue';
            return;
        }

        summaryEl.style.display = '';
        document.getElementById('summary-lines').innerHTML = summaryHtml;

        // Apply discount
        const netSubtotal = Math.max(0, subtotal - appliedDiscount);

        if (appliedDiscount > 0) {
            document.getElementById('summary-discount').style.display = '';
            document.getElementById('summary-discount-val').textContent = '-' + CURRENCY + appliedDiscount.toFixed(2);
        } else {
            document.getElementById('summary-discount').style.display = 'none';
        }

        let fee = 0, extra = 0;
        if (PASS_FEE && netSubtotal > 0) {
            fee = netSubtotal * (FEE_PCT / 100);
            extra = EXTRA;
            const feeEl = document.getElementById('summary-fee-val');
            if (feeEl) feeEl.textContent = CURRENCY + fee.toFixed(2);
        }

        const total = netSubtotal > 0 ? Math.max(0, netSubtotal + fee + extra) : 0;
        document.getElementById('summary-total').textContent = CURRENCY + total.toFixed(2);

        submitBtn.disabled = false;
        submitBtn.textContent = total > 0 ? ('Pay ' + CURRENCY + total.toFixed(2)) : 'Get Free Tickets';
    }

    window.applyPromo = async function() {
        const codeInput = document.getElementById('embed-promo');
        const msgEl = document.getElementById('promo-msg');
        const code = (codeInput.value || '').trim().toUpperCase();

        if (!code) { msgEl.className = 'promo-msg error'; msgEl.textContent = 'Enter a code.'; return; }

        // Calculate current subtotal
        let subtotal = 0;
        document.querySelectorAll('.tier-card').forEach(card => {
            const valEl = card.querySelector('.tier-qty-val');
            if (!valEl) return;
            const qty = parseInt(valEl.dataset.qty) || 0;
            subtotal += (parseFloat(card.dataset.price) || 0) * qty;
        });

        try {
            const res = await fetch(API_PROMO, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ code: code, event_id: EVENT_ID, subtotal: subtotal })
            });
            const data = await res.json();

            if (data.success) {
                appliedDiscount = data.discount_amount || 0;
                appliedPromoCode = code;
                msgEl.className = 'promo-msg success';
                msgEl.textContent = '✅ ' + data.message;
                recalculate();
            } else {
                appliedDiscount = 0;
                appliedPromoCode = '';
                msgEl.className = 'promo-msg error';
                msgEl.textContent = '❌ ' + (data.message || 'Invalid code.');
                recalculate();
            }
        } catch (err) {
            msgEl.className = 'promo-msg error';
            msgEl.textContent = '❌ Network error.';
        }
    };

    window.submitCheckout = async function() {
        const alertEl = document.getElementById('embed-alert');
        const name = document.getElementById('embed-name').value.trim();
        const email = document.getElementById('embed-email').value.trim();

        if (!name || !email) {
            alertEl.className = 'embed-alert error';
            alertEl.textContent = 'Please fill in your name and email.';
            return;
        }

        // Build tickets map
        const tickets = {};
        document.querySelectorAll('.tier-card').forEach(card => {
            const valEl = card.querySelector('.tier-qty-val');
            if (!valEl) return;
            const qty = parseInt(valEl.dataset.qty) || 0;
            if (qty > 0) tickets[card.dataset.tierId] = qty;
        });

        if (Object.keys(tickets).length === 0) {
            alertEl.className = 'embed-alert error';
            alertEl.textContent = 'Please select at least one ticket.';
            return;
        }

        const submitBtn = document.getElementById('embed-submit-btn');
        submitBtn.disabled = true;
        submitBtn.textContent = 'Processing…';
        alertEl.className = 'embed-alert';
        alertEl.style.display = 'none';

        // Get parent page URL for callback
        let parentUrl = '';
        try { parentUrl = window.parent.location.href; } catch(e) {}

        try {
            const res = await fetch(API_CHECKOUT, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    name: name,
                    email: email,
                    tickets: tickets,
                    promo_code: appliedPromoCode,
                    callback_url: parentUrl,
                    ref: new URLSearchParams(window.location.search).get('ref') || ''
                })
            });
            const data = await res.json();

            if (!data.success) {
                alertEl.className = 'embed-alert error';
                alertEl.textContent = data.message || 'Checkout failed.';
                submitBtn.disabled = false;
                submitBtn.textContent = 'Try Again';
                return;
            }

            // FREE order — show success inline
            if (data.is_free && !data.payment_required) {
                showSuccess(data);
                return;
            }

            // PAID order — redirect to Paystack
            if (data.authorization_url) {
                // Try to open in parent window, fallback to current
                try {
                    window.top.location.href = data.authorization_url;
                } catch(e) {
                    window.location.href = data.authorization_url;
                }
                return;
            }

            alertEl.className = 'embed-alert error';
            alertEl.textContent = 'No payment URL received. Please try again.';
            submitBtn.disabled = false;
            submitBtn.textContent = 'Retry';

        } catch (err) {
            alertEl.className = 'embed-alert error';
            alertEl.textContent = 'Network error. Please check your connection.';
            submitBtn.disabled = false;
            submitBtn.textContent = 'Retry';
        }
    };

    function showSuccess(data) {
        document.getElementById('embed-form').style.display = 'none';
        document.getElementById('embed-alert').style.display = 'none';

        let ticketHtml = '';
        if (data.tickets && data.tickets.length) {
            ticketHtml = '<div class="ticket-codes">';
            data.tickets.forEach(t => {
                ticketHtml += '<span class="ticket-code">' + escHtml(t.ticket_code) + '</span>';
            });
            ticketHtml += '</div>';
        }

        document.getElementById('embed-success').innerHTML =
            '<div class="check-icon">✓</div>' +
            '<h2>Tickets Confirmed! 🎉</h2>' +
            '<p>Your tickets for <strong>' + escHtml(data.event?.name || 'this event') + '</strong> have been sent to <strong>' + escHtml(data.attendee?.email || '') + '</strong>.</p>' +
            ticketHtml +
            '<p style="margin-top:1rem;font-size:.78rem;color:#94a3b8;">Reference: ' + escHtml(data.reference || '') + '</p>';
        document.getElementById('embed-success').style.display = '';

        // Notify parent page via postMessage
        try {
            window.parent.postMessage({
                type: 'evently:checkout:success',
                reference: data.reference,
                tickets: data.tickets,
                event: data.event
            }, '*');
        } catch(e) {}
    }

    function escHtml(str) {
        const d = document.createElement('div');
        d.textContent = str || '';
        return d.innerHTML;
    }
})();
</script>
@endsection
