@extends('layouts/host')

@section('title')
Promo Codes &amp; Discounts - Host Console
@endsection

@section('content')
<div class="event-mgmt-header">
    <a href="{{ route('host/dashboard') }}" class="back-link">← Back to Dashboard</a>
    <div class="page-hero">
        <h1>Promo Codes &amp; Discounts</h1>
        <p class="lede">Create custom discount coupons for your event tickets.</p>
    </div>
</div>

<div class="promo-page-grid">
    <!-- Create Promo Code Form -->
    <div class="form-card promo-form-section">
        <h3 style="margin-top:0;">Create New Coupon</h3>
        <form method="POST" action="{{ route('host/promo-codes/create') }}">
            @csrf
            <label style="display:block; margin-bottom:0.75rem;">Coupon Code
                <input type="text" name="code" placeholder="e.g. EARLYBIRD10" required style="text-transform:uppercase;" autocomplete="off">
            </label>

            <label style="display:block; margin-bottom:0.75rem;">Apply to Event
                <select name="event_id">
                    <option value=""><?= !empty($isAdmin) ? 'All Platform Events (Global)' : 'All My Events (Global)' ?></option>
                    <?php foreach ($events as $e): ?>
                        <?php 
                            $isMine = !empty($currentUserId) && ($e['organizer_id'] ?? '') == $currentUserId;
                            $label = e($e['name']);
                            if (!empty($isAdmin)) {
                                $label .= $isMine ? ' 👑 (My Event)' : ' — Host: ' . e($e['organizer_name'] ?? 'Organizer');
                            }
                        ?>
                        <option value="<?= $e['id'] ?>"><?= $label ?></option>
                    <?php endforeach; ?>
                </select>
            </label>

            <div class="promo-form-row">
                <label>Discount Type
                    <select name="discount_type">
                        <option value="percentage">Percentage (%)</option>
                        <option value="fixed">Fixed Amount (<?= e(get_currency_symbol()) ?>)</option>
                    </select>
                </label>
                <label>Discount Value
                    <input type="number" step="0.01" name="discount_value" placeholder="10" required>
                </label>
            </div>

            <div class="promo-form-row">
                <label>Max Uses
                    <input type="number" name="max_uses" value="100">
                </label>
                <label>Expiry Date (Optional)
                    <input type="date" name="expires_at">
                </label>
            </div>

            <button type="submit" class="admin-action-btn primary" style="width:100%; height:44px; border-radius:12px; justify-content:center; font-weight:700;">
                Create Promo Code →
            </button>
        </form>
    </div>

    <!-- Active Promo Codes List -->
    <div class="form-card promo-list-section">
        <h3 style="margin-top:0;">Existing Promo Codes</h3>
        <?php if (empty($promoCodes)): ?>
            <div class="promo-empty-state">
                <span style="font-size:2.5rem;">🏷️</span>
                <p>No promo codes created yet.</p>
                <p style="font-size:0.82rem; color:rgba(var(--text),0.45);">Create your first coupon to start offering discounts.</p>
            </div>
        <?php else: ?>
            <!-- Desktop Table (hidden on mobile) -->
            <div class="promo-table-wrap">
                <table class="admin-table" style="width:100%;">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <?php if (!empty($isAdmin)): ?>
                                <th>Host / Organizer</th>
                            <?php endif; ?>
                            <th>Event</th>
                            <th>Discount</th>
                            <th>Used / Max</th>
                            <th>Expires</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($promoCodes as $p): ?>
                            <?php 
                                $isMine = !empty($currentUserId) && (($p['host_id'] ?? '') == $currentUserId || ($p['organizer_id'] ?? '') == $currentUserId);
                            ?>
                            <tr>
                                <td>
                                    <strong class="promo-code-pill"><?= e($p['code']) ?></strong>
                                </td>
                                <?php if (!empty($isAdmin)): ?>
                                    <td style="font-size:0.82rem;">
                                        <?php if ($isMine): ?>
                                            <span style="display:inline-flex; align-items:center; gap:0.25rem; font-weight:700; color:#10b981;">👑 You</span>
                                        <?php else: ?>
                                            <span style="display:inline-flex; align-items:center; gap:0.25rem; font-weight:600; color:var(--text);">👤 <?= e($p['host_name'] ?? 'Organizer') ?></span>
                                        <?php endif; ?>
                                    </td>
                                <?php endif; ?>
                                <td style="font-size:0.85rem;"><?= e($p['event_name'] ?: 'All Events (Global)') ?></td>
                                <td>
                                    <strong>
                                        <?= $p['discount_type'] === 'percentage' ? (float)$p['discount_value'] . '%' : format_price($p['discount_value']) ?>
                                    </strong>
                                </td>
                                <td><?= $p['used_count'] ?> / <?= $p['max_uses'] ?></td>
                                <td style="font-size:0.82rem; color:rgba(var(--text),0.55);">
                                    <?= $p['expires_at'] ? date('M d, Y', strtotime($p['expires_at'])) : 'No Expiry' ?>
                                </td>
                                <td>
                                    <form method="POST" action="{{ route('host/promo-codes/delete') }}" onsubmit="return confirm('Delete this promo code?');">
                                        @csrf
                                        <input type="hidden" name="id" value="<?= $p['id'] ?>">
                                        <button type="submit" class="admin-action-btn danger" style="padding:0.3rem 0.6rem; font-size:0.75rem;">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Mobile Card List (hidden on desktop) -->
            <div class="promo-cards-list">
                <?php foreach ($promoCodes as $p): ?>
                    <div class="promo-card">
                        <div class="promo-card-header">
                            <strong class="promo-code-pill"><?= e($p['code']) ?></strong>
                            <span class="promo-card-discount">
                                <?= $p['discount_type'] === 'percentage' ? (float)$p['discount_value'] . '%' : format_price($p['discount_value']) ?> off
                            </span>
                        </div>
                        <div class="promo-card-body">
                            <div class="promo-card-detail">
                                <span class="promo-card-label">Event</span>
                                <span class="promo-card-value"><?= e($p['event_name'] ?: 'All Events') ?></span>
                            </div>
                            <div class="promo-card-detail">
                                <span class="promo-card-label">Usage</span>
                                <span class="promo-card-value"><?= $p['used_count'] ?> / <?= $p['max_uses'] ?></span>
                            </div>
                            <div class="promo-card-detail">
                                <span class="promo-card-label">Expires</span>
                                <span class="promo-card-value"><?= $p['expires_at'] ? date('M d, Y', strtotime($p['expires_at'])) : 'No Expiry' ?></span>
                            </div>
                        </div>
                        <div class="promo-card-footer">
                            <form method="POST" action="{{ route('host/promo-codes/delete') }}" onsubmit="return confirm('Delete this promo code?');">
                                @csrf
                                <input type="hidden" name="id" value="<?= $p['id'] ?>">
                                <button type="submit" class="admin-action-btn danger" style="width:100%; justify-content:center; font-size:0.82rem; padding:0.45rem 0.75rem; border-radius:10px;">🗑️ Delete Coupon</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
@endsection
