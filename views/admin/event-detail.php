<?php

/**
 * Admin Event Detail
 *
 * This file contains the HTML markup for the admin event detail page of the application.
 *
 * @package Evently
 * @subpackage Views/Admin

 * @author Your Name <Celio Natti>
 * @license MIT License
 * @version 1.0.0
 */

$siteName = site_setting('site_title', 'Eventlyy');
?>

@extends('layouts/admin')

@section('title')
Admin Event Detail - Manage Your <?= e($siteName) ?> Events
@endsection

@section('content')
<section class="page-hero" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:1.25rem;">
    <div>
        <span class="panel-badge">Event detail</span>
        <h1 style="margin:0.25rem 0;">Event performance and host overview</h1>
        <p class="lede" style="margin:0;">Inspect event metrics, ticket status, and organizer payment readiness.</p>
    </div>
    <div style="display:flex; gap:0.65rem; align-items:center; flex-wrap:wrap;">
        <a href="{{ route('host/event/edit/{id}', ['id' => $event['id']]) }}" class="admin-action-btn primary" style="padding:0.55rem 1.05rem; font-size:0.85rem; border-radius:10px; font-weight:700; text-decoration:none; display:inline-flex; align-items:center; gap:0.4rem;">
            <span>✏️</span> Edit Event
        </a>
        <a href="{{ route('host/event/management/{id}', ['id' => $event['id']]) }}" class="admin-action-btn" style="padding:0.55rem 1rem; font-size:0.85rem; border-radius:10px; font-weight:600; text-decoration:none; display:inline-flex; align-items:center; gap:0.4rem; background:rgba(var(--text), 0.04); border:1px solid rgba(var(--muted),0.2);">
            <span>⚙️</span> Manage & Staff
        </a>
        <a href="{{ route('host/event/referrals/{id}', ['id' => $event['id']]) }}" class="admin-action-btn" style="padding:0.55rem 1rem; font-size:0.85rem; border-radius:10px; font-weight:600; text-decoration:none; display:inline-flex; align-items:center; gap:0.4rem; background:rgba(16, 185, 129, 0.1); color:#059669; border:1px solid rgba(16, 185, 129, 0.25);">
            <span>🔗</span> Referrals Hub
        </a>
        <?php 
            $publicSlug = !empty($event['custom_url']) ? $event['custom_url'] : ($event['slug'] ?? $event['id']);
        ?>
        <a href="/event/<?= e($publicSlug) ?>" target="_blank" class="admin-action-btn" style="padding:0.55rem 1rem; font-size:0.85rem; border-radius:10px; font-weight:600; text-decoration:none; display:inline-flex; align-items:center; gap:0.4rem; background:rgba(var(--muted), 0.1); border:1px solid rgba(var(--muted),0.2);">
            <span>🌐</span> Public Page ↗
        </a>
    </div>
</section>

<?php
    $checkedIn = 0;
    $gross_revenue = $gross_revenue ?? ($total_revenue ?? 0);
    $ticket_sales_subtotal = $ticket_sales_subtotal ?? $gross_revenue;
    $host_earnings = $host_earnings ?? $ticket_sales_subtotal;
    $platform_profit = $platform_profit ?? 0;
    $disbursed_payouts = $disbursed_payouts ?? 0;
    $pending_payouts = $pending_payouts ?? 0;
    $remaining_balance = $remaining_balance ?? max(0.00, $host_earnings - $disbursed_payouts);
    $tickets_sold = $tickets_sold ?? 0;
    $total_capacity = $total_capacity ?? 0;

    // Get check-in count
    try {
        $db = \Ace\Application::$app->db->pdo;
        $stmt = $db->prepare("SELECT COUNT(*) FROM tickets WHERE event_id = :eid AND checked_in = 1");
        $stmt->execute(['eid' => $event['id']]);
        $checkedIn = (int)$stmt->fetchColumn();
    } catch (\Exception $e) {
        $checkedIn = 0;
    }

    // Fallback: if total_capacity is 0, try event-level capacity
    if ($total_capacity === 0 && isset($event['capacity'])) {
        $total_capacity = (int)$event['capacity'];
    }

    $salesPct = $total_capacity > 0 ? min(100, round(($tickets_sold / $total_capacity) * 100)) : 0;

    // Get host payout readiness
    $payoutReady = 'Not Set';
    try {
        $db = \Ace\Application::$app->db->pdo;
        $stmt = $db->prepare("SELECT payout_bank, payout_account, is_payout_ready, is_flagged FROM host_profiles WHERE user_id = :uid");
        $stmt->execute(['uid' => $event['organizer_id']]);
        $hp = $stmt->fetch();
        if ($hp) {
            if (!empty($hp['is_flagged'])) {
                $payoutReady = 'Flagged / Frozen';
            } elseif (!empty($hp['payout_bank']) && !empty($hp['payout_account'])) {
                $payoutReady = 'Ready to Disburse';
            } else {
                $payoutReady = 'Incomplete Details';
            }
        }
    } catch (\Exception $e) {
        $payoutReady = 'Unknown';
    }
?>

<section class="admin-users-stats" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 1.75rem;">
    <div class="au-stat-card" style="border-left: 4px solid rgb(var(--brand));">
        <span class="au-stat-icon au-stat-icon--brand">
            <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z" />
                <line x1="2" y1="10" x2="22" y2="10" />
            </svg>
        </span>
        <div class="au-stat-body">
            <span class="au-stat-num"><?= number_format($tickets_sold) ?></span>
            <span class="au-stat-lbl">Online Tickets Sold (<?= $salesPct ?>%)</span>
            <span style="font-size:0.75rem; color:rgb(var(--muted)); display:block; margin-top:2px;">of <?= number_format($total_capacity) ?> total capacity</span>
            <?php if (($tickets_issued ?? 0) > 0): ?>
                <span style="font-size:0.72rem; color:#6366f1; display:block; margin-top:2px;">+<?= number_format($tickets_issued) ?> host-issued</span>
            <?php endif; ?>
        </div>
    </div>

    <div class="au-stat-card" style="border-left: 4px solid #3b82f6;">
        <span class="au-stat-icon" style="background: rgba(59, 130, 246, 0.1); color: #3b82f6;">
            <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="2" y="5" width="20" height="14" rx="2" />
                <line x1="2" y1="10" x2="22" y2="10" />
            </svg>
        </span>
        <div class="au-stat-body">
            <span class="au-stat-num"><?= format_price($gross_revenue) ?></span>
            <span class="au-stat-lbl">Online Gross Sales</span>
            <span style="font-size:0.75rem; color:rgb(var(--muted)); display:block; margin-top:2px;">Platform ticket purchases only</span>
            <?php if (($issued_nominal ?? 0) > 0): ?>
                <span style="font-size:0.72rem; color:#6366f1; display:block; margin-top:2px;">+<?= format_price($issued_nominal) ?> issued (offline)</span>
            <?php endif; ?>
        </div>
    </div>

    <div class="au-stat-card" style="border-left: 4px solid #10b981;">
        <span class="au-stat-icon au-stat-icon--success">
            <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" />
            </svg>
        </span>
        <div class="au-stat-body">
            <span class="au-stat-num" style="color:#10b981;"><?= format_price($host_earnings) ?></span>
            <span class="au-stat-lbl">Host Net Earnings (Payout)</span>
            <span style="font-size:0.75rem; color:rgb(var(--muted)); display:block; margin-top:2px;">Online sales payout share only</span>
        </div>
    </div>

    <div class="au-stat-card" style="border-left: 4px solid #8b5cf6;">
        <span class="au-stat-icon" style="background: rgba(139, 92, 246, 0.1); color: #8b5cf6;">
            <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10" />
                <path d="M16 8h-6a2 2 0 1 0 0 4h4a2 2 0 1 1 0 4H8" />
                <path d="M12 6v2m0 8v2" />
            </svg>
        </span>
        <div class="au-stat-body">
            <span class="au-stat-num" style="color:#8b5cf6;"><?= format_price($platform_profit) ?></span>
            <span class="au-stat-lbl">Platform Profit</span>
            <span style="font-size:0.75rem; color:rgb(var(--muted)); display:block; margin-top:2px;">Eventlyy fees &amp; charges</span>
        </div>
    </div>

    <div class="au-stat-card" style="border-left: 4px solid #14b8a6;">
        <span class="au-stat-icon" style="background: rgba(20, 184, 166, 0.1); color: #14b8a6;">
            <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
                <polyline points="22 4 12 14.01 9 11.01" />
            </svg>
        </span>
        <div class="au-stat-body">
            <span class="au-stat-num"><?= format_price($disbursed_payouts) ?></span>
            <span class="au-stat-lbl">Disbursed to Host</span>
            <span style="font-size:0.75rem; color:rgb(var(--muted)); display:block; margin-top:2px;">
                <?= format_price($remaining_balance) ?> remaining
            </span>
        </div>
    </div>

    <div class="au-stat-card" style="border-left: 4px solid #f59e0b;">
        <span class="au-stat-icon au-stat-icon--warning">
            <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M9 11l3 3L22 4" />
                <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11" />
            </svg>
        </span>
        <div class="au-stat-body">
            <span class="au-stat-num"><?= number_format($checkedIn) ?></span>
            <span class="au-stat-lbl">Guest Check-ins</span>
            <span style="font-size:0.75rem; color:rgb(var(--muted)); display:block; margin-top:2px;">Verified at entrance</span>
        </div>
    </div>
</section>

<!-- Financial Settlement & Host Balance Panel -->
<section class="admin-panel" style="margin-bottom: 1.5rem; border: 1px solid rgba(var(--border), 0.7); background: var(--bg-card);">
    <div class="section-header" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:0.5rem;">
        <div>
            <h2 style="margin:0;">💰 Financial Earnings & Payout Settlement</h2>
            <p class="section-note" style="margin:0.25rem 0 0;">Complete revenue breakdown, platform fees, and host disbursement reconciliation for this event.</p>
        </div>
        <div style="display:flex; gap:0.5rem; align-items:center;">
            <a href="{{ route('admin/payouts') }}" class="admin-action-btn primary" style="font-size:0.82rem; text-decoration:none; padding:0.45rem 0.85rem;">
                💳 Manage Payouts
            </a>
        </div>
    </div>
    <div class="event-item" style="padding: 1.25rem;">
        <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1rem; background: rgba(var(--muted), 0.04); padding: 1.25rem; border-radius: 12px; border: 1px solid rgba(var(--border), 0.5);">
            <div>
                <span style="font-size:0.78rem; font-weight:700; color:rgb(var(--muted)); text-transform:uppercase; letter-spacing:0.04em;">Online Gross Sales</span>
                <div style="font-size:1.25rem; font-weight:800; color:var(--text); margin-top:0.25rem;"><?= format_price($gross_revenue) ?></div>
                <span style="font-size:0.75rem; color:rgb(var(--muted));"><?= number_format($tickets_sold) ?> online tickets</span>
                <?php if (($tickets_issued ?? 0) > 0): ?>
                    <span style="font-size:0.72rem; color:#6366f1; display:block; margin-top:2px;">+<?= number_format($tickets_issued) ?> issued offline</span>
                <?php endif; ?>
            </div>
            <div>
                <span style="font-size:0.78rem; font-weight:700; color:#8b5cf6; text-transform:uppercase; letter-spacing:0.04em;">Platform Fees (Profit)</span>
                <div style="font-size:1.25rem; font-weight:800; color:#8b5cf6; margin-top:0.25rem;"><?= format_price($platform_profit) ?></div>
                <span style="font-size:0.75rem; color:rgb(var(--muted));"><?= e(!empty($event['pass_fee']) ? 'Buyer paid fee (+5% & extra charge)' : 'Host absorbed fee') ?></span>
            </div>
            <div>
                <span style="font-size:0.78rem; font-weight:700; color:#10b981; text-transform:uppercase; letter-spacing:0.04em;">Host Net Earnings (Payout)</span>
                <div style="font-size:1.25rem; font-weight:800; color:#10b981; margin-top:0.25rem;"><?= format_price($host_earnings) ?></div>
                <span style="font-size:0.75rem; color:rgb(var(--muted));">Online sales payout entitlement</span>
            </div>
            <div>
                <span style="font-size:0.78rem; font-weight:700; color:#14b8a6; text-transform:uppercase; letter-spacing:0.04em;">Disbursed Payouts</span>
                <div style="font-size:1.25rem; font-weight:800; color:#14b8a6; margin-top:0.25rem;"><?= format_price($disbursed_payouts) ?></div>
                <span style="font-size:0.75rem; color:rgb(var(--muted));">Transferred to host bank</span>
            </div>
            <div>
                <span style="font-size:0.78rem; font-weight:700; color:#f59e0b; text-transform:uppercase; letter-spacing:0.04em;">Pending / Unpaid Balance</span>
                <div style="font-size:1.25rem; font-weight:800; color:#f59e0b; margin-top:0.25rem;"><?= format_price($remaining_balance) ?></div>
                <span style="font-size:0.75rem; color:rgb(var(--muted));"><?= format_price($pending_payouts) ?> in payout queue</span>
            </div>
        </div>
    </div>
</section>

<section class="admin-panel">
    <div class="section-header">
        <div>
            <h2>Event information</h2>
            <p class="section-note">Review event configuration, venue, and description.</p>
        </div>
    </div>
    <div class="event-item">
        <p class="meta">Event name</p>
        <h3><?= e($event['name']) ?></h3>
        <p class="meta">Date: <?= date('M d, Y h:i A', strtotime($event['date'])) ?> • Location: <?= e($event['location']) ?> • Category: <?= e($event['category'] ?? 'General') ?></p>
        <p><?= e($event['description']) ?></p>
    </div>
</section>

<section class="admin-panel">
    <div class="section-header">
        <div>
            <h2>Ticket Tiers & Sales Performance</h2>
            <p class="section-note">See performance, tickets sold, capacity, and revenue earned per ticket type.</p>
        </div>
    </div>
    <div class="event-item" style="padding:0; overflow-x:auto;">
        <?php if (empty($ticketTypes)): ?>
            <div style="padding:1.5rem; text-align:center; color:rgb(var(--muted)); font-size:0.9rem;">
                No ticket tiers configured for this event.
            </div>
        <?php else: ?>
            <table class="au-table" style="margin:0; width:100%;">
                <thead>
                    <tr>
                        <th>Tier Name</th>
                        <th>Price</th>
                        <th>Sold / Capacity</th>
                        <th>Sales Progress</th>
                        <th style="text-align:right;">Tier Revenue</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($ticketTypes as $tt): 
                        $tierRevenue = $tt['revenue'] ?? ((int)$tt['sold'] * (float)$tt['price']);
                        $tierPct = $tt['percentage'] ?? ($tt['capacity'] > 0 ? round(($tt['sold'] / $tt['capacity']) * 100) : 0);
                    ?>
                    <tr>
                        <td style="font-weight:700; color:rgb(var(--text));">
                            <?= e($tt['name']) ?>
                            <?php if (!empty($tt['description'])): ?>
                                <div style="font-size:0.75rem; color:rgb(var(--muted)); font-weight:normal;"><?= e($tt['description']) ?></div>
                            <?php endif; ?>
                        </td>
                        <td style="font-weight:700;"><?= format_price($tt['price']) ?></td>
                        <td>
                            <span style="font-weight:600; font-size:0.88rem;"><?= (int)$tt['sold'] ?></span> 
                            <span style="color:rgb(var(--muted)); font-size:0.8rem;">/ <?= (int)$tt['capacity'] ?></span>
                        </td>
                        <td style="width: 180px;">
                            <div style="display:flex; align-items:center; gap:0.5rem;">
                                <div class="progress-bar-bg" style="flex:1; height:6px; background:rgba(var(--muted), 0.15); border-radius:3px; overflow:hidden;">
                                    <div class="progress-bar-fill" style="height:100%; width:<?= $tierPct ?>%; background:rgb(var(--brand));"></div>
                                </div>
                                <span style="font-size:0.78rem; font-weight:700; color:rgb(var(--muted)); min-width:32px;"><?= $tierPct ?>%</span>
                            </div>
                        </td>
                        <td style="text-align:right; font-weight:800; color:#10b981; font-size:0.95rem;">
                            <?= format_price($tierRevenue) ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr style="background:rgba(var(--muted),0.04); font-weight:800;">
                        <td colspan="2" style="font-size:0.9rem; text-transform:uppercase; letter-spacing:0.04em;">Total Sales & Revenue</td>
                        <td style="font-size:0.95rem;"><?= number_format($tickets_sold) ?> / <?= number_format($total_capacity) ?></td>
                        <td><span style="font-size:0.82rem; font-weight:700; color:rgb(var(--brand));"><?= $salesPct ?>% Capacity</span></td>
                        <td style="text-align:right; font-size:1.05rem; color:#10b981;"><?= format_price($gross_revenue) ?></td>
                    </tr>
                </tfoot>
            </table>
        <?php endif; ?>
    </div>
</section>

<section class="admin-panel">
    <div class="section-header" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:0.5rem;">
        <div>
            <h2>Attendee & Ticket Buyers</h2>
            <p class="section-note">Contact details saved for all registered users and guest ticket purchasers.</p>
        </div>
        <?php if (!empty($attendees)): ?>
        <a href="{{ route('admin/attendees/export') }}?event_id=<?= $event['id'] ?>" class="admin-action-btn primary" style="background:#10b981; border-color:#10b981; text-decoration:none; font-size:0.82rem;">
            📥 Export Event Attendees CSV
        </a>
        <?php endif; ?>
    </div>
    <div class="event-item" style="padding:0; overflow-x:auto;">
        <?php if (empty($attendees)): ?>
            <div style="padding:1.5rem; text-align:center; color:rgb(var(--muted)); font-size:0.9rem;">
                No tickets purchased for this event yet.
            </div>
        <?php else: ?>
            <table class="au-table" style="margin:0; width:100%;">
                <thead>
                    <tr>
                        <th>Attendee Name</th>
                        <th>Email</th>
                        <th>Account Type</th>
                        <th>Tier</th>
                        <th>Price</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($attendees as $att): 
                        $isReg = !empty($att['user_id']);
                    ?>
                    <tr>
                        <td style="font-weight:700; color:rgb(var(--text));"><?= e($att['attendee_name']) ?></td>
                        <td><?= e($att['attendee_email']) ?></td>
                        <td>
                            <?php if ($isReg): ?>
                                <span style="background:rgba(16, 185, 129, 0.12); color:#059669; font-size:0.72rem; font-weight:700; padding:2px 7px; border-radius:6px;">👤 Registered</span>
                            <?php else: ?>
                                <span style="background:rgba(245, 158, 11, 0.14); color:#d97706; font-size:0.72rem; font-weight:700; padding:2px 7px; border-radius:6px;" title="Guest attendee email saved from ticket purchase">🌐 Guest</span>
                            <?php endif; ?>
                        </td>
                        <td><?= e($att['ticket_type_name'] ?? 'General') ?></td>
                        <td style="font-weight:700;"><?= format_price($att['price']) ?></td>
                        <td style="font-size:0.8rem; color:rgb(var(--muted));"><?= date('M d, Y', strtotime($att['purchase_date'])) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</section>

<section class="admin-panel">
    <div class="section-header">
        <div>
            <h2>Host contact</h2>
            <p class="section-note">Organizer information for payout and support.</p>
        </div>
    </div>
    <div class="event-item">
        <p class="meta">Host</p>
        <h3><?= e($event['organizer_name']) ?></h3>
        <p class="meta">Email: <?= e($event['organizer_email']) ?></p>
        <p class="meta">Payout status: <?= $payoutReady ?></p>
        <a href="{{ route('admin/payouts') }}" class="action">Review payouts</a>
    </div>
</section>

<section class="admin-panel">
    <div class="section-header">
        <div>
            <h2>Admin Management & Actions</h2>
            <p class="section-note">Manage status or remove this event from the platform.</p>
        </div>
    </div>
    <div class="event-item">
        <p class="meta">Event Status: <strong><?= ucfirst($event['status']) ?></strong></p>
        <div class="guest-row" style="display:flex; gap:1rem; margin-top:1rem; align-items:center; flex-wrap:wrap;">
            <a href="{{ route('admin/events') }}" class="admin-action-btn" style="text-decoration:none;">← Back to Events List</a>
            <form method="POST" action="{{ route('admin/events/toggle-featured') }}" style="display:inline;">
                <?= csrf_field() ?>
                <input type="hidden" name="event_id" value="<?= $event['id'] ?>" />
                <?php if (!empty($event['is_featured'])): ?>
                    <button type="submit" class="admin-action-btn warning" style="cursor:pointer; font-weight:700;">
                        ⭐ Featured on Carousel (Click to Remove)
                    </button>
                <?php else: ?>
                    <button type="submit" class="admin-action-btn primary" style="cursor:pointer; font-weight:700;">
                        ⭐ Feature on Homepage Carousel
                    </button>
                <?php endif; ?>
            </form>
            <button type="button" class="admin-action-btn danger" id="detail-delete-btn" style="cursor:pointer;">
                🗑️ Delete Event
            </button>
        </div>
    </div>
</section>

<!-- Confirm Delete Modal -->
<div class="modal-overlay hidden" id="delete-modal" role="dialog" aria-modal="true">
    <div class="modal-card">
        <h3>Delete Event</h3>
        <p>Are you sure you want to delete <strong><?= e($event['name']) ?></strong>? All attendee records, ticket configurations, and sales logs for this event will be permanently deleted. This action cannot be undone.</p>
        <form method="POST" action="{{ route('admin/events') }}">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="delete" />
            <input type="hidden" name="event_id" value="<?= $event['id'] ?>" />
            <div class="modal-actions" style="margin-top: 1.5rem; display: flex; gap: 1rem; justify-content: flex-end;">
                <button type="button" class="admin-action-btn" id="delete-cancel-btn">Cancel</button>
                <button type="submit" class="admin-action-btn danger">Yes, Delete Event</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const deleteBtn = document.getElementById('detail-delete-btn');
    const deleteModal = document.getElementById('delete-modal');
    const cancelBtn = document.getElementById('delete-cancel-btn');

    if (deleteBtn && deleteModal) {
        deleteBtn.addEventListener('click', function() {
            deleteModal.classList.remove('hidden');
        });
    }
    if (cancelBtn && deleteModal) {
        cancelBtn.addEventListener('click', function() {
            deleteModal.classList.add('hidden');
        });
    }
});
</script>
@endsection