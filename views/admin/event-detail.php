<?php

/**
 * Admin Event Detail
 *
 * Comprehensive Executive Dashboard for Event Performance, Financial Settlement,
 * Host Payout Readiness, Attendee Rosters, and Lifecycle Administration.
 *
 * @package Evently
 * @subpackage Views/Admin
 * @version 2.0.0
 */

$siteName = site_setting('site_title', 'Eventlyy');
$themePrimary = function_exists('theme_primary_color') ? theme_primary_color() : '#108a55';
$themeSecondary = function_exists('theme_secondary_color') ? theme_secondary_color() : '#1aae6c';
$themePrimaryRgb = function_exists('theme_primary_color') ? implode(' ', sscanf(ltrim($themePrimary, '#'), '%02x%02x%02x')) : '16 138 85';

// Fallback & calculation sanitization
$checkedIn = 0;
$gross_revenue = $gross_revenue ?? ($total_revenue ?? 0);
$ticket_sales_subtotal = $ticket_sales_subtotal ?? $gross_revenue;
$host_earnings = $host_earnings ?? $ticket_sales_subtotal;
$platform_profit = $platform_profit ?? 0;
$disbursed_payouts = $disbursed_payouts ?? 0;
$early_escrow_disbursed = $early_escrow_disbursed ?? 0;
$auto_payouts_disbursed = $auto_payouts_disbursed ?? 0;
$pending_payouts = $pending_payouts ?? 0;
$remaining_balance = $remaining_balance ?? max(0.00, $host_earnings - $disbursed_payouts);
$available_to_disburse = $available_to_disburse ?? max(0.00, $host_earnings - $disbursed_payouts - $pending_payouts);
$event_payouts = $event_payouts ?? [];
$recurring_occurrences = $recurring_occurrences ?? [];
$tickets_sold = $tickets_sold ?? 0;
$tickets_issued = $tickets_issued ?? 0;
$issued_nominal = $issued_nominal ?? 0;
$total_capacity = $total_capacity ?? 0;
$hostBankName = $host_bank_name ?? '';
$hostBankAccount = $host_bank_account ?? '';
$attendees = $attendees ?? [];
$ticketTypes = $ticketTypes ?? [];

// Calculate total checked-in count
try {
    $db = \Ace\Application::$app->db->pdo;
    $stmt = $db->prepare("SELECT COUNT(*) FROM tickets WHERE event_id = :eid AND checked_in = 1");
    $stmt->execute(['eid' => $event['id']]);
    $checkedIn = (int)$stmt->fetchColumn();
} catch (\Exception $e) {
    $checkedIn = 0;
}

// Fallback: if total_capacity is 0, check event-level capacity
if ($total_capacity === 0 && isset($event['capacity'])) {
    $total_capacity = (int)$event['capacity'];
}

$salesPct = $total_capacity > 0 ? min(100, round(($tickets_sold / $total_capacity) * 100)) : 0;
$checkInPct = $tickets_sold > 0 ? min(100, round(($checkedIn / $tickets_sold) * 100)) : 0;

// Resolve Host Payout Readiness
$payoutReady = $host_payout_ready ?? 'Not Set';
if (empty($hostBankName) || $payoutReady === 'Not Set') {
    try {
        $db = \Ace\Application::$app->db->pdo;
        $stmt = $db->prepare("
            SELECT hp.payout_bank, hp.payout_account, u.status as user_status 
            FROM host_profiles hp
            LEFT JOIN users u ON hp.user_id = u.id
            WHERE hp.user_id = :uid
        ");
        $stmt->execute(['uid' => $event['organizer_id']]);
        $hp = $stmt->fetch();
        if ($hp) {
            if (empty($hostBankName)) {
                $hostBankName = $hp['payout_bank'] ?? '';
            }
            if (empty($hostBankAccount)) {
                $hostBankAccount = $hp['payout_account'] ?? '';
            }
            if (($hp['user_status'] ?? '') === 'suspended') {
                $payoutReady = 'Flagged / Frozen';
            } elseif (!empty($hp['payout_bank']) && !empty($hp['payout_account'])) {
                $payoutReady = 'Ready to Disburse';
            } else {
                $payoutReady = 'Incomplete Details';
            }
        }
    } catch (\Exception $e) {
        if ($payoutReady === 'Not Set') {
            $payoutReady = 'Unknown';
        }
    }
}

// Lifecycle timing
$eventTimestamp = strtotime($event['date']);
$isEventPassed = $is_event_passed ?? ($eventTimestamp < time());
$eventHoursPast = ($isEventPassed) ? round((time() - $eventTimestamp) / 3600, 1) : 0;
$publicSlug = !empty($event['custom_url']) ? $event['custom_url'] : ($event['slug'] ?? $event['id']);

$isCompleted = ($event['status'] === 'completed' || ($isEventPassed && $event['status'] === 'published'));
$isDraft = ($event['status'] === 'draft');
$isCancelled = ($event['status'] === 'cancelled');
$isLive = (!$isCompleted && !$isDraft && !$isCancelled);
?>

@extends('layouts/admin')

@section('title')
<?= e($event['name']) ?> - Admin Event Command Center - <?= e($siteName) ?>
@endsection

@section('content')
<div class="ed-shell">

    <!-- ─── BREADCRUMB & EXECUTIVE HEADER ─── -->
    <header class="ed-header-card">
        <div class="ed-header-top">
            <nav class="ed-breadcrumb" aria-label="Breadcrumb">
                <a href="{{ route('admin/overview') }}">Admin</a>
                <span class="ed-bc-sep">/</span>
                <a href="{{ route('admin/events') }}">Events</a>
                <span class="ed-bc-sep">/</span>
                <span class="ed-bc-current">#<?= e($event['id']) ?></span>
            </nav>

            <div class="ed-header-badges">
                <?php if ($isLive): ?>
                    <span class="ed-status-badge ed-status--live"><span class="ed-pulse-dot"></span> Live / Upcoming</span>
                <?php elseif ($isCompleted): ?>
                    <span class="ed-status-badge ed-status--completed">✓ Completed</span>
                <?php elseif ($isDraft): ?>
                    <span class="ed-status-badge ed-status--draft">📝 Draft</span>
                <?php elseif ($isCancelled): ?>
                    <span class="ed-status-badge ed-status--cancelled">✕ Cancelled</span>
                <?php else: ?>
                    <span class="ed-status-badge ed-status--default"><?= ucfirst(e($event['status'])) ?></span>
                <?php endif; ?>

                <?php if (!empty($event['is_featured'])): ?>
                    <span class="ed-status-badge ed-status--featured">⭐ Featured</span>
                <?php endif; ?>

                <?php if (!empty($event['category'])): ?>
                    <span class="ed-status-badge ed-status--category">🏷️ <?= e($event['category']) ?></span>
                <?php endif; ?>

                <?php if (!empty($event['is_recurring'])): ?>
                    <span class="ed-status-badge ed-status--series">🔄 Series</span>
                <?php endif; ?>
            </div>
        </div>

        <div class="ed-header-body">
            <div class="ed-header-info">
                <h1 class="ed-event-title"><?= e($event['name']) ?></h1>
                
                <div class="ed-header-meta">
                    <div class="ed-meta-item">
                        <span class="ed-meta-icon">📅</span>
                        <div>
                            <strong><?= date('D, M j, Y • g:i A', $eventTimestamp) ?></strong>
                            <span class="ed-meta-sub">
                                <?= $isEventPassed ? "(Completed {$eventHoursPast}h ago)" : '(Upcoming in ' . max(1, round(($eventTimestamp - time()) / 86400)) . ' days)' ?>
                            </span>
                        </div>
                    </div>

                    <div class="ed-meta-item">
                        <span class="ed-meta-icon">📍</span>
                        <div>
                            <strong><?= e($event['location']) ?></strong>
                            <span class="ed-meta-sub">Physical / Online Venue</span>
                        </div>
                    </div>

                    <div class="ed-meta-item">
                        <span class="ed-meta-icon">👤</span>
                        <div>
                            <strong><?= e($event['organizer_name']) ?></strong>
                            <span class="ed-meta-sub"><?= e($event['organizer_email']) ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Header Quick Actions -->
            <div class="ed-header-actions">
                <a href="{{ route('host/event/edit/{id}', ['id' => $event['id']]) }}" class="ed-btn ed-btn-primary" title="Edit Event Details">
                    <span>✏️</span> Edit
                </a>
                <a href="{{ route('host/event/ticket-verification/' . $event['id']) }}" class="ed-btn ed-btn-secondary" title="Gate Scanner & Ticket Verification">
                    <span>🎟️</span> Verify Tickets
                </a>
                <a href="{{ route('host/event/management/{id}', ['id' => $event['id']]) }}" class="ed-btn ed-btn-secondary" title="Event Management & Staff">
                    <span>⚙️</span> Manage &amp; Staff
                </a>
                <a href="{{ route('host/event/referrals/{id}', ['id' => $event['id']]) }}" class="ed-btn ed-btn-secondary" title="Referrals & Affiliates Hub">
                    <span>🔗</span> Referrals
                </a>
                <a href="/event/<?= e($publicSlug) ?>" target="_blank" rel="noopener noreferrer" class="ed-btn ed-btn-outline" title="View Public Landing Page">
                    <span>🌐</span> Public Page ↗
                </a>
                <a href="{{ route('admin/attendees/export-pdf') }}?event_id=<?= $event['id'] ?>" target="_blank" class="ed-btn ed-btn-accent" title="Download Official Letterheaded A4 PDF Manifest">
                    <span>📄</span> Export A4 PDF
                </a>
                <a href="{{ route('admin/events') }}" class="ed-btn ed-btn-ghost" title="Back to All Events">
                    <span>←</span> All Events
                </a>
            </div>
        </div>
    </header>

    <!-- ─── RECURRING SERIES OCCURRENCE SWITCHER (IF RECURRING) ─── -->
    <?php if (!empty($event['is_recurring']) && !empty($recurring_occurrences) && count($recurring_occurrences) > 1): ?>
        <?php
            $totalOcc = count($recurring_occurrences);
            $currentIndex = 0;
            foreach ($recurring_occurrences as $idx => $occ) {
                if ($occ['id'] == $event['id']) {
                    $currentIndex = $idx;
                    break;
                }
            }
            $prevOcc = $currentIndex > 0 ? $recurring_occurrences[$currentIndex - 1] : null;
            $nextOcc = $currentIndex < $totalOcc - 1 ? $recurring_occurrences[$currentIndex + 1] : null;
        ?>
        <div class="ed-series-banner">
            <div class="ed-series-info">
                <span class="ed-series-icon">🔄</span>
                <div>
                    <div class="ed-series-title">
                        <strong>Recurring Series:</strong> Occurrence #<?= e($event['recurrence_sequence'] ?? ($currentIndex + 1)) ?> of <?= $totalOcc ?>
                    </div>
                    <div class="ed-series-sub">
                        Active Schedule: <strong class="ed-text-primary"><?= date('D, M j, Y • g:i A', $eventTimestamp) ?></strong>
                    </div>
                </div>
            </div>

            <div class="ed-series-controls">
                <?php if ($prevOcc): ?>
                    <a href="{{ route('admin/events/' . $prevOcc['id']) }}" class="ed-series-btn" title="Go to Previous Date">
                        ← Prev
                    </a>
                <?php else: ?>
                    <span class="ed-series-btn is-disabled">← Prev</span>
                <?php endif; ?>

                <div class="ed-series-select-wrap">
                    <label for="ed-series-jump" class="sr-only">Switch Date:</label>
                    <select id="ed-series-jump" class="ed-series-select" onchange="if(this.value && this.value != '<?= $event['id'] ?>') window.location.href = '<?= route('admin/events/') ?>' + this.value;">
                        <?php foreach ($recurring_occurrences as $occ): ?>
                            <option value="<?= $occ['id'] ?>" <?= $occ['id'] == $event['id'] ? 'selected' : '' ?>>
                                #<?= e($occ['recurrence_sequence'] ?? '?') ?> &bull; <?= date('M d, Y • g:i A', strtotime($occ['date'])) ?> <?= $occ['id'] == $event['id'] ? ' ★ (Current)' : '' ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <?php if ($nextOcc): ?>
                    <a href="{{ route('admin/events/' . $nextOcc['id']) }}" class="ed-series-btn" title="Go to Next Date">
                        Next →
                    </a>
                <?php else: ?>
                    <span class="ed-series-btn is-disabled">Next →</span>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- ─── TOP 4 EXECUTIVE KPI BENTO GRID ─── -->
    <div class="ed-kpi-grid">
        <!-- KPI 1: Tickets & Admission -->
        <div class="ed-kpi-card ed-kpi--primary">
            <div class="ed-kpi-header">
                <span class="ed-kpi-icon">🎟️</span>
                <span class="ed-kpi-badge"><?= $salesPct ?>% Sold</span>
            </div>
            <div class="ed-kpi-body">
                <div class="ed-kpi-val"><?= number_format($tickets_sold) ?></div>
                <div class="ed-kpi-label">Tickets Sold</div>
                <div class="ed-kpi-progress">
                    <div class="ed-kpi-prog-bar" style="width: <?= $salesPct ?>%;"></div>
                </div>
            </div>
            <div class="ed-kpi-footer">
                <span>Cap: <strong><?= number_format($total_capacity) ?></strong></span>
                <span>Gate: <strong><?= number_format($checkedIn) ?></strong> in (<?= $checkInPct ?>%)</span>
                <?php if ($tickets_issued > 0): ?>
                    <span>+<?= number_format($tickets_issued) ?> issued</span>
                <?php endif; ?>
            </div>
        </div>

        <!-- KPI 2: Gross Sales -->
        <div class="ed-kpi-card ed-kpi--blue">
            <div class="ed-kpi-header">
                <span class="ed-kpi-icon">💳</span>
                <span class="ed-kpi-badge">Gross Volume</span>
            </div>
            <div class="ed-kpi-body">
                <div class="ed-kpi-val"><?= format_price($gross_revenue) ?></div>
                <div class="ed-kpi-label">Online Ticket Sales</div>
            </div>
            <div class="ed-kpi-footer">
                <span>Orders: <strong><?= number_format($tickets_sold) ?></strong></span>
                <?php if ($issued_nominal > 0): ?>
                    <span>Issued: <strong><?= format_price($issued_nominal) ?></strong></span>
                <?php else: ?>
                    <span>Direct platform checkouts</span>
                <?php endif; ?>
            </div>
        </div>

        <!-- KPI 3: Host Net Earnings -->
        <div class="ed-kpi-card ed-kpi--green">
            <div class="ed-kpi-header">
                <span class="ed-kpi-icon">💰</span>
                <span class="ed-kpi-badge <?= $payoutReady === 'Ready to Disburse' ? 'is-ready' : 'is-warning' ?>">
                    <?= e($payoutReady) ?>
                </span>
            </div>
            <div class="ed-kpi-body">
                <div class="ed-kpi-val ed-text-success"><?= format_price($host_earnings) ?></div>
                <div class="ed-kpi-label">Host Net Payout Entitlement</div>
            </div>
            <div class="ed-kpi-footer">
                <span>Disbursed: <strong><?= format_price($disbursed_payouts) ?></strong></span>
                <span>Unpaid: <strong><?= format_price($remaining_balance) ?></strong></span>
            </div>
        </div>

        <!-- KPI 4: Platform Profit -->
        <div class="ed-kpi-card ed-kpi--purple">
            <div class="ed-kpi-header">
                <span class="ed-kpi-icon">📈</span>
                <span class="ed-kpi-badge">Fee Margin</span>
            </div>
            <div class="ed-kpi-body">
                <div class="ed-kpi-val ed-text-purple"><?= format_price($platform_profit) ?></div>
                <div class="ed-kpi-label">Platform Retained Profit</div>
            </div>
            <div class="ed-kpi-footer">
                <span><?= !empty($event['pass_fee']) ? 'Buyer paid fees (+5% fee)' : 'Host absorbed platform fees' ?></span>
            </div>
        </div>
    </div>

    <!-- ─── SEGMENTED WORKSPACE TABS ─── -->
    <div class="ed-tabs-bar" role="tablist">
        <button type="button" class="ed-tab-btn active" data-tab="tab-overview" role="tab" aria-selected="true">
            <span class="ed-tab-icon">📊</span> Overview &amp; Performance
        </button>
        <button type="button" class="ed-tab-btn" data-tab="tab-financials" role="tab" aria-selected="false">
            <span class="ed-tab-icon">💰</span> Financials &amp; Escrow
            <span class="ed-tab-count"><?= format_price($remaining_balance) ?></span>
        </button>
        <button type="button" class="ed-tab-btn" data-tab="tab-attendees" role="tab" aria-selected="false">
            <span class="ed-tab-icon">👥</span> Attendees Roster
            <span class="ed-tab-count"><?= count($attendees) ?></span>
        </button>
        <button type="button" class="ed-tab-btn" data-tab="tab-operations" role="tab" aria-selected="false">
            <span class="ed-tab-icon">⚙️</span> Operations &amp; Settings
        </button>
    </div>

    <!-- ─── TAB 1: OVERVIEW & PERFORMANCE ─── -->
    <div id="tab-overview" class="ed-tab-pane active" role="tabpanel">
        <div class="ed-overview-grid">
            
            <!-- Left Column: Details & Tiers -->
            <div class="ed-overview-left">
                
                <!-- Event Identity & Description Card -->
                <div class="ed-card">
                    <div class="ed-card-header">
                        <div class="ed-card-title-group">
                            <span class="ed-card-icon">📌</span>
                            <div>
                                <h2 class="ed-card-title">Event Overview &amp; Configuration</h2>
                                <p class="ed-card-sub">Event details, venue information, and public story.</p>
                            </div>
                        </div>
                    </div>
                    <div class="ed-card-body">
                        <?php if (!empty($event['image'])): ?>
                            <div class="ed-event-poster-wrap">
                                <img src="<?= e(asset($event['image'])) ?>" alt="<?= e($event['name']) ?> poster" class="ed-event-poster-img" onerror="this.style.display='none';">
                            </div>
                        <?php endif; ?>

                        <div class="ed-details-list">
                            <div class="ed-detail-item">
                                <span class="ed-detail-label">Event Name</span>
                                <span class="ed-detail-value ed-text-bold"><?= e($event['name']) ?></span>
                            </div>
                            <div class="ed-detail-item">
                                <span class="ed-detail-label">Schedule &amp; Time</span>
                                <span class="ed-detail-value"><?= date('l, F j, Y • g:i A', $eventTimestamp) ?></span>
                            </div>
                            <div class="ed-detail-item">
                                <span class="ed-detail-label">Venue / Location</span>
                                <span class="ed-detail-value"><?= e($event['location']) ?></span>
                            </div>
                            <div class="ed-detail-item">
                                <span class="ed-detail-label">Category</span>
                                <span class="ed-detail-value"><span class="ed-pill"><?= e($event['category'] ?? 'General') ?></span></span>
                            </div>
                            <?php if (!empty($event['custom_url'])): ?>
                            <div class="ed-detail-item">
                                <span class="ed-detail-label">Custom Domain / URL</span>
                                <span class="ed-detail-value">
                                    <a href="/event/<?= e($event['custom_url']) ?>" target="_blank" class="ed-link">
                                        eventlyy.com/event/<?= e($event['custom_url']) ?> ↗
                                    </a>
                                </span>
                            </div>
                            <?php endif; ?>
                            <div class="ed-detail-item ed-detail-item--full">
                                <span class="ed-detail-label">Description</span>
                                <div class="ed-detail-desc"><?= nl2br(e($event['description'])) ?></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Ticket Tiers & Sales Performance Table -->
                <div class="ed-card">
                    <div class="ed-card-header">
                        <div class="ed-card-title-group">
                            <span class="ed-card-icon">🎟️</span>
                            <div>
                                <h2 class="ed-card-title">Ticket Tiers &amp; Sales Performance</h2>
                                <p class="ed-card-sub">Pricing, capacity quotas, sales velocity, and revenue contribution per tier.</p>
                            </div>
                        </div>
                    </div>
                    <div class="ed-card-body ed-p-0">
                        <?php if (empty($ticketTypes)): ?>
                            <div class="ed-empty-state">
                                <span>🎟️</span>
                                <p>No ticket tiers configured for this event.</p>
                            </div>
                        <?php else: ?>
                            <!-- Desktop Tiers Table -->
                            <div class="ed-table-wrap">
                                <table class="ed-table">
                                    <thead>
                                        <tr>
                                            <th>Tier Name</th>
                                            <th>Price</th>
                                            <th>Sold / Cap</th>
                                            <th>Sales Progress</th>
                                            <th class="ed-text-right">Tier Revenue</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($ticketTypes as $tt): 
                                            $tierRevenue = $tt['revenue'] ?? ((int)$tt['sold'] * (float)$tt['price']);
                                            $tierPct = $tt['percentage'] ?? ($tt['capacity'] > 0 ? round(($tt['sold'] / $tt['capacity']) * 100) : 0);
                                        ?>
                                        <tr>
                                            <td>
                                                <strong class="ed-tier-name"><?= e($tt['name']) ?></strong>
                                                <?php if (!empty($tt['description'])): ?>
                                                    <div class="ed-tier-desc"><?= e($tt['description']) ?></div>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="ed-price-pill"><?= format_price($tt['price'] ?? 0) ?></span>
                                            </td>
                                            <td>
                                                <strong><?= (int)$tt['sold'] ?></strong>
                                                <span class="ed-text-muted">/ <?= (int)$tt['capacity'] ?></span>
                                            </td>
                                            <td>
                                                <div class="ed-prog-cell">
                                                    <div class="ed-progress-track">
                                                        <div class="ed-progress-fill" style="width: <?= $tierPct ?>%;"></div>
                                                    </div>
                                                    <span class="ed-prog-label"><?= $tierPct ?>%</span>
                                                </div>
                                            </td>
                                            <td class="ed-text-right ed-text-bold ed-text-success">
                                                <?= format_price($tierRevenue) ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                    <tfoot>
                                        <tr class="ed-table-total-row">
                                            <td colspan="2"><strong>Total Sales &amp; Revenue</strong></td>
                                            <td><strong><?= number_format($tickets_sold) ?> / <?= number_format($total_capacity) ?></strong></td>
                                            <td><span class="ed-pill ed-pill--brand"><?= $salesPct ?>% Total Capacity</span></td>
                                            <td class="ed-text-right ed-text-bold ed-text-success"><?= format_price($gross_revenue) ?></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>

                            <!-- Mobile Tiers Card Stack (≤ 768px) -->
                            <div class="ed-mobile-tiers">
                                <?php foreach ($ticketTypes as $tt): 
                                    $tierRevenue = $tt['revenue'] ?? ((int)$tt['sold'] * (float)$tt['price']);
                                    $tierPct = $tt['percentage'] ?? ($tt['capacity'] > 0 ? round(($tt['sold'] / $tt['capacity']) * 100) : 0);
                                ?>
                                <div class="ed-tier-card">
                                    <div class="ed-tier-card-top">
                                        <div>
                                            <div class="ed-tier-name"><?= e($tt['name']) ?></div>
                                            <?php if (!empty($tt['description'])): ?>
                                                <div class="ed-tier-desc"><?= e($tt['description']) ?></div>
                                            <?php endif; ?>
                                        </div>
                                        <span class="ed-price-pill"><?= format_price($tt['price'] ?? 0) ?></span>
                                    </div>
                                    <div class="ed-tier-card-progress">
                                        <div class="ed-tier-prog-header">
                                            <span><?= (int)$tt['sold'] ?> / <?= (int)$tt['capacity'] ?> sold</span>
                                            <strong><?= $tierPct ?>%</strong>
                                        </div>
                                        <div class="ed-progress-track">
                                            <div class="ed-progress-fill" style="width: <?= $tierPct ?>%;"></div>
                                        </div>
                                    </div>
                                    <div class="ed-tier-card-bottom">
                                        <span>Tier Revenue:</span>
                                        <strong class="ed-text-success"><?= format_price($tierRevenue) ?></strong>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

            </div>

            <!-- Right Column: Host & Specs -->
            <div class="ed-overview-right">
                
                <!-- Host & Payout Card -->
                <div class="ed-card">
                    <div class="ed-card-header">
                        <div class="ed-card-title-group">
                            <span class="ed-card-icon">🏛️</span>
                            <div>
                                <h3 class="ed-card-title">Organizer &amp; Host Profile</h3>
                                <p class="ed-card-sub">Event creator contact and banking readiness.</p>
                            </div>
                        </div>
                    </div>
                    <div class="ed-card-body">
                        <div class="ed-host-profile">
                            <div class="ed-host-avatar">
                                <?= strtoupper(substr($event['organizer_name'] ?: 'H', 0, 2)) ?>
                            </div>
                            <div class="ed-host-meta">
                                <strong class="ed-host-name"><?= e($event['organizer_name']) ?></strong>
                                <span class="ed-host-email"><?= e($event['organizer_email']) ?></span>
                                <span class="ed-host-id">User ID: #<?= e($event['organizer_id']) ?></span>
                            </div>
                        </div>

                        <div class="ed-bank-box">
                            <div class="ed-bank-row">
                                <span class="ed-bank-label">Bank Institution</span>
                                <span class="ed-bank-val"><?= e($hostBankName ?: 'Not Linked') ?></span>
                            </div>
                            <div class="ed-bank-row">
                                <span class="ed-bank-label">Account Number</span>
                                <span class="ed-bank-val">
                                    <?php if (!empty($hostBankAccount)): ?>
                                        •••• <?= substr($hostBankAccount, -4) ?>
                                    <?php else: ?>
                                        <span class="ed-text-muted">None Provided</span>
                                    <?php endif; ?>
                                </span>
                            </div>
                            <div class="ed-bank-row">
                                <span class="ed-bank-label">Payout Status</span>
                                <span class="ed-pill <?= $payoutReady === 'Ready to Disburse' ? 'ed-pill--success' : 'ed-pill--warning' ?>">
                                    <?= e($payoutReady) ?>
                                </span>
                            </div>
                        </div>

                        <div class="ed-card-actions">
                            <a href="{{ route('admin/payouts') }}" class="ed-btn ed-btn-outline ed-btn-block">
                                💳 Review Payouts Center →
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Event Specs & Settings Card -->
                <div class="ed-card">
                    <div class="ed-card-header">
                        <div class="ed-card-title-group">
                            <span class="ed-card-icon">⚙️</span>
                            <div>
                                <h3 class="ed-card-title">Event Specifications</h3>
                                <p class="ed-card-sub">System flags &amp; configuration parameters.</p>
                            </div>
                        </div>
                    </div>
                    <div class="ed-card-body ed-p-0">
                        <div class="ed-specs-list">
                            <div class="ed-spec-item">
                                <span>Event Reference</span>
                                <strong>#EVT-<?= e($event['id']) ?></strong>
                            </div>
                            <div class="ed-spec-item">
                                <span>Fee Architecture</span>
                                <strong><?= !empty($event['pass_fee']) ? 'Buyer Pays Fees (+5%)' : 'Host Absorbs Fees' ?></strong>
                            </div>
                            <div class="ed-spec-item">
                                <span>Flyer Page</span>
                                <strong><?= !empty($event['flyer_page_enabled']) ? 'Enabled' : 'Disabled' ?></strong>
                            </div>
                            <div class="ed-spec-item">
                                <span>Series Architecture</span>
                                <strong><?= !empty($event['is_recurring']) ? 'Recurring Series' : 'Single Occurrence' ?></strong>
                            </div>
                            <div class="ed-spec-item">
                                <span>Published On</span>
                                <strong><?= !empty($event['created_at']) ? date('M j, Y', strtotime($event['created_at'])) : 'N/A' ?></strong>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </div>

    <!-- ─── TAB 2: FINANCIALS & ESCROW ─── -->
    <div id="tab-financials" class="ed-tab-pane" role="tabpanel">
        <div class="ed-card">
            <div class="ed-card-header">
                <div class="ed-card-title-group">
                    <span class="ed-card-icon">💰</span>
                    <div>
                        <h2 class="ed-card-title">Financial Settlement &amp; Escrow Reconciliation</h2>
                        <p class="ed-card-sub">Real-time ledger of online gross transactions, platform charges, early escrow releases, and host disbursement balances.</p>
                    </div>
                </div>
                <div>
                    <a href="{{ route('admin/payouts') }}" class="ed-btn ed-btn-primary">
                        💳 Payout Center →
                    </a>
                </div>
            </div>
            
            <div class="ed-card-body">
                
                <!-- 5-Metric Financial Strip -->
                <div class="ed-fin-strip">
                    <div class="ed-fin-tile">
                        <span class="ed-fin-lbl">Online Gross Sales</span>
                        <div class="ed-fin-val"><?= format_price($gross_revenue) ?></div>
                        <span class="ed-fin-sub"><?= number_format($tickets_sold) ?> tickets checked out</span>
                    </div>

                    <div class="ed-fin-tile">
                        <span class="ed-fin-lbl ed-text-purple">Platform Retained Fees</span>
                        <div class="ed-fin-val ed-text-purple"><?= format_price($platform_profit) ?></div>
                        <span class="ed-fin-sub"><?= !empty($event['pass_fee']) ? 'Buyer paid fees' : 'Host absorbed' ?></span>
                    </div>

                    <div class="ed-fin-tile">
                        <span class="ed-fin-lbl ed-text-success">Host Total Entitlement</span>
                        <div class="ed-fin-val ed-text-success"><?= format_price($host_earnings) ?></div>
                        <span class="ed-fin-sub">Net payable to host</span>
                    </div>

                    <div class="ed-fin-tile">
                        <span class="ed-fin-lbl ed-text-teal">Total Disbursed</span>
                        <div class="ed-fin-val ed-text-teal"><?= format_price($disbursed_payouts) ?></div>
                        <span class="ed-fin-sub">
                            <?php if ($early_escrow_disbursed > 0): ?>
                                🛡️ <?= format_price($early_escrow_disbursed) ?> early escrow
                            <?php else: ?>
                                <?= number_format(count($event_payouts)) ?> payment transfers
                            <?php endif; ?>
                        </span>
                    </div>

                    <div class="ed-fin-tile ed-fin-tile--highlight">
                        <span class="ed-fin-lbl ed-text-brand"><?= $isEventPassed ? 'Unpaid Host Balance' : 'Escrow Custody Balance' ?></span>
                        <div class="ed-fin-val ed-text-brand"><?= format_price($remaining_balance) ?></div>
                        <span class="ed-fin-sub">
                            <?php if ($pending_payouts > 0): ?>
                                ⚠️ <?= format_price($pending_payouts) ?> pending approval
                            <?php else: ?>
                                ✓ <?= format_price($available_to_disburse) ?> available to settle
                            <?php endif; ?>
                        </span>
                    </div>
                </div>

                <!-- Escrow & Settlement Intelligence Banner -->
                <div class="ed-escrow-intel">
                    <div class="ed-escrow-intel-left">
                        <span class="ed-escrow-intel-icon">🏦</span>
                        <div>
                            <div class="ed-escrow-intel-title">
                                Host Settlement Account: <strong><?= e($hostBankName ?: 'Not Linked') ?></strong>
                                <?php if (!empty($hostBankAccount)): ?>
                                    <span class="ed-text-muted">(•••• <?= substr($hostBankAccount, -4) ?>)</span>
                                <?php endif; ?>
                            </div>
                            <div class="ed-escrow-intel-desc">
                                Status: <strong><?= e($payoutReady) ?></strong> &bull; 
                                <?php if ($isEventPassed): ?>
                                    Event concluded <?= $eventHoursPast ?>h ago &bull; Funds unlocked for final payout disbursement.
                                <?php else: ?>
                                    Event upcoming &bull; Funds safeguarded under Escrow Custody until completion or early release approval.
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="ed-escrow-intel-right">
                        <?php if ($early_escrow_disbursed > 0): ?>
                            <span class="ed-pill ed-pill--indigo">🛡️ Released Early: <?= format_price($early_escrow_disbursed) ?></span>
                        <?php endif; ?>
                        <?php if ($pending_payouts > 0): ?>
                            <span class="ed-pill ed-pill--warning">⚠️ Pending: <?= format_price($pending_payouts) ?></span>
                        <?php endif; ?>
                        <span class="ed-pill <?= ($available_to_disburse > 0) ? 'ed-pill--success' : 'ed-pill--neutral' ?>">
                            <?= $isEventPassed ? 'Available to Settle' : 'In Custody' ?>: <?= format_price($available_to_disburse > 0 ? $available_to_disburse : $remaining_balance) ?>
                        </span>
                    </div>
                </div>

                <!-- Payout Disbursement Ledger -->
                <div class="ed-ledger-wrap">
                    <div class="ed-ledger-head">
                        <h3 class="ed-ledger-title">Disbursement &amp; Early Escrow Audit Trail</h3>
                        <span class="ed-pill"><?= count($event_payouts) ?> record<?= count($event_payouts) === 1 ? '' : 's' ?></span>
                    </div>

                    <?php if (empty($event_payouts)): ?>
                        <div class="ed-empty-state">
                            <span>💳</span>
                            <p>No payout disbursements have been processed for this event yet.</p>
                            <span class="ed-text-muted">Total payable balance to organizer is <?= format_price($remaining_balance) ?>.</span>
                        </div>
                    <?php else: ?>
                        <div class="ed-table-wrap">
                            <table class="ed-table">
                                <thead>
                                    <tr>
                                        <th>Payout ID</th>
                                        <th>Disbursement Type</th>
                                        <th>Amount</th>
                                        <th>Destination Account</th>
                                        <th>Status</th>
                                        <th>Timestamp</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($event_payouts as $po): 
                                        $isEscrow = ($po['type'] === 'escrow');
                                        $isAuto = ($po['type'] === 'auto_completed');
                                        $isDone = in_array($po['status'], ['processed', 'completed']);
                                        $isPending = ($po['status'] === 'pending');
                                    ?>
                                    <tr>
                                        <td><strong class="ed-code-text">#P-<?= e($po['id']) ?></strong></td>
                                        <td>
                                            <?php if ($isEscrow): ?>
                                                <span class="ed-pill ed-pill--indigo">🛡️ Early Escrow</span>
                                            <?php elseif ($isAuto): ?>
                                                <span class="ed-pill ed-pill--success">✅ Auto Settlement</span>
                                            <?php else: ?>
                                                <span class="ed-pill">💳 <?= ucfirst(str_replace('_', ' ', $po['type'])) ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td><strong class="ed-text-bold ed-text-primary"><?= format_price($po['amount'] ?? 0) ?></strong></td>
                                        <td>
                                            <?= e($po['payout_bank'] ?: ($hostBankName ?: 'Host Bank')) ?>
                                            <?php if (!empty($po['payout_account'] ?: $hostBankAccount)): ?>
                                                <span class="ed-text-muted">(•••• <?= substr($po['payout_account'] ?: $hostBankAccount, -4) ?>)</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="ed-pill <?= $isDone ? 'ed-pill--success' : ($isPending ? 'ed-pill--warning' : 'ed-pill--danger') ?>">
                                                <?= ucfirst(e($po['status'])) ?>
                                            </span>
                                        </td>
                                        <td class="ed-text-muted"><?= date('M d, Y • h:i A', strtotime($po['created_at'])) ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Mobile Payout Cards (≤ 768px) -->
                        <div class="ed-mobile-payouts">
                            <?php foreach ($event_payouts as $po): 
                                $isEscrow = ($po['type'] === 'escrow');
                                $isAuto = ($po['type'] === 'auto_completed');
                                $isDone = in_array($po['status'], ['processed', 'completed']);
                                $isPending = ($po['status'] === 'pending');
                            ?>
                            <div class="ed-payout-card">
                                <div class="ed-payout-card-top">
                                    <strong class="ed-code-text">#P-<?= e($po['id']) ?></strong>
                                    <?php if ($isEscrow): ?>
                                        <span class="ed-pill ed-pill--indigo">🛡️ Early Escrow</span>
                                    <?php elseif ($isAuto): ?>
                                        <span class="ed-pill ed-pill--success">✅ Auto Settlement</span>
                                    <?php else: ?>
                                        <span class="ed-pill">💳 <?= ucfirst(str_replace('_', ' ', $po['type'])) ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="ed-payout-card-amount"><?= format_price($po['amount'] ?? 0) ?></div>
                                <div class="ed-payout-card-meta">
                                    <div class="ed-payout-row">
                                        <span>Bank:</span>
                                        <strong><?= e($po['payout_bank'] ?: ($hostBankName ?: 'Host Bank')) ?></strong>
                                    </div>
                                    <div class="ed-payout-row">
                                        <span>Status:</span>
                                        <span class="ed-pill <?= $isDone ? 'ed-pill--success' : ($isPending ? 'ed-pill--warning' : 'ed-pill--danger') ?>">
                                            <?= ucfirst(e($po['status'])) ?>
                                        </span>
                                    </div>
                                    <div class="ed-payout-row">
                                        <span>Date:</span>
                                        <span class="ed-text-muted"><?= date('M d, Y • h:i A', strtotime($po['created_at'])) ?></span>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

            </div>
        </div>
    </div>

    <!-- ─── TAB 3: ATTENDEES ROSTER ─── -->
    <div id="tab-attendees" class="ed-tab-pane" role="tabpanel">
        <div class="ed-card">
            <div class="ed-card-header ed-attendees-header">
                <div class="ed-card-title-group">
                    <span class="ed-card-icon">👥</span>
                    <div>
                        <h2 class="ed-card-title">Attendees &amp; Admission Roster</h2>
                        <p class="ed-card-sub">Verified ticket buyers, gate check-in verification timestamps, and attendee pass codes.</p>
                    </div>
                </div>

                <!-- Search & Exports -->
                <div class="ed-attendees-toolbar">
                    <div class="ed-search-wrap">
                        <span class="ed-search-icon">🔍</span>
                        <input type="text" id="attendeeFilterInput" placeholder="Search attendee, email, pass ID, tier..." class="ed-search-input" />
                    </div>
                    <?php if (!empty($attendees)): ?>
                        <div class="ed-export-btns">
                            <a href="{{ route('admin/attendees/export-pdf') }}?event_id=<?= $event['id'] ?>" target="_blank" class="ed-btn ed-btn-accent" title="Download Official Letterheaded A4 PDF Manifest">
                                <span>📄</span> Export A4 PDF
                            </a>
                            <a href="{{ route('admin/attendees/export') }}?event_id=<?= $event['id'] ?>" class="ed-btn ed-btn-outline" title="Export CSV Spreadsheet">
                                <span>📥</span> Export CSV
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="ed-card-body ed-p-0">
                <?php if (empty($attendees)): ?>
                    <div class="ed-empty-state">
                        <span>🎟️</span>
                        <p>No tickets purchased or issued for this event yet.</p>
                    </div>
                <?php else: ?>
                    <!-- Desktop Attendees Table -->
                    <div class="ed-table-wrap">
                        <table class="ed-table" id="eventAttendeesTable">
                            <thead>
                                <tr>
                                    <th>Ticket Pass ID</th>
                                    <th>Attendee Name &amp; Email</th>
                                    <th>Account Type</th>
                                    <th>Ticket Tier</th>
                                    <th>Amount Paid</th>
                                    <th>Gate Admission</th>
                                    <th>Purchase Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($attendees as $att): 
                                    $isReg = !empty($att['user_id']);
                                    $hue = (crc32($att['attendee_email'] ?? $att['ticket_id']) % 260) + 100;
                                    $initials = strtoupper(substr($att['attendee_name'] ?: 'A', 0, 2));
                                    $price = (float)($att['price'] ?? 0);
                                    $isChecked = !empty($att['checked_in']);
                                ?>
                                <tr class="event-attendee-row">
                                    <td>
                                        <div class="ed-pass-cell">
                                            <span class="ed-code-pill"><?= e($att['ticket_id']) ?></span>
                                            <button type="button" class="ed-copy-btn" onclick="navigator.clipboard.writeText('<?= addslashes(e($att['ticket_id'])) ?>'); this.innerText='✓'; setTimeout(() => this.innerText='📋', 1500);" title="Copy Ticket Code">📋</button>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="ed-user-cell">
                                            <div class="ed-user-avatar" style="background: hsl(<?= $hue ?>, 65%, var(--ed-avatar-l, 92%)); color: hsl(<?= $hue ?>, 65%, var(--ed-avatar-text-l, 35%));">
                                                <?= $initials ?>
                                            </div>
                                            <div class="ed-user-meta">
                                                <strong class="ed-user-name"><?= e($att['attendee_name']) ?></strong>
                                                <span class="ed-user-email"><?= e($att['attendee_email']) ?></span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if ($isReg): ?>
                                            <span class="ed-pill ed-pill--brand">👤 Registered User</span>
                                        <?php else: ?>
                                            <span class="ed-pill ed-pill--neutral" title="Guest checkout">🌐 Guest</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="ed-pill ed-pill--category"><?= e($att['ticket_type_name'] ?? 'General') ?></span>
                                    </td>
                                    <td>
                                        <?php if ($price > 0): ?>
                                            <strong class="ed-text-bold ed-text-success"><?= format_price($price) ?></strong>
                                        <?php else: ?>
                                            <span class="ed-pill ed-pill--success">FREE</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($isChecked): ?>
                                            <span class="ed-pill ed-pill--success">✓ Checked In</span>
                                        <?php else: ?>
                                            <span class="ed-pill ed-pill--warning">⏳ Gate Pending</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="ed-text-muted">
                                        <?= date('M d, Y • h:i A', strtotime($att['purchase_date'])) ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile Attendee Cards (≤ 768px) -->
                    <div class="ed-mobile-attendees">
                        <?php foreach ($attendees as $att): 
                            $isReg = !empty($att['user_id']);
                            $hue = (crc32($att['attendee_email'] ?? $att['ticket_id']) % 260) + 100;
                            $initials = strtoupper(substr($att['attendee_name'] ?: 'A', 0, 2));
                            $price = (float)($att['price'] ?? 0);
                            $isChecked = !empty($att['checked_in']);
                        ?>
                        <div class="ed-att-card event-attendee-row">
                            <div class="ed-att-card-top">
                                <div class="ed-user-cell">
                                    <div class="ed-user-avatar" style="background: hsl(<?= $hue ?>, 65%, var(--ed-avatar-l, 92%)); color: hsl(<?= $hue ?>, 65%, var(--ed-avatar-text-l, 35%));">
                                        <?= $initials ?>
                                    </div>
                                    <div class="ed-user-meta">
                                        <strong class="ed-user-name"><?= e($att['attendee_name']) ?></strong>
                                        <span class="ed-user-email"><?= e($att['attendee_email']) ?></span>
                                    </div>
                                </div>
                                <div>
                                    <?php if ($price > 0): ?>
                                        <strong class="ed-text-bold ed-text-success"><?= format_price($price) ?></strong>
                                    <?php else: ?>
                                        <span class="ed-pill ed-pill--success">FREE</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="ed-att-card-badges">
                                <div class="ed-pass-cell">
                                    <span class="ed-code-pill"><?= e($att['ticket_id']) ?></span>
                                    <button type="button" class="ed-copy-btn" onclick="navigator.clipboard.writeText('<?= addslashes(e($att['ticket_id'])) ?>'); this.innerText='✓'; setTimeout(() => this.innerText='📋', 1500);" title="Copy Ticket Code">📋</button>
                                </div>
                                <span class="ed-pill ed-pill--category"><?= e($att['ticket_type_name'] ?? 'General') ?></span>
                                <?php if ($isReg): ?>
                                    <span class="ed-pill ed-pill--brand">👤 Reg</span>
                                <?php else: ?>
                                    <span class="ed-pill ed-pill--neutral">🌐 Guest</span>
                                <?php endif; ?>
                            </div>
                            <div class="ed-att-card-bottom">
                                <?php if ($isChecked): ?>
                                    <span class="ed-pill ed-pill--success">✓ Checked In</span>
                                <?php else: ?>
                                    <span class="ed-pill ed-pill--warning">⏳ Gate Pending</span>
                                <?php endif; ?>
                                <span class="ed-text-muted"><?= date('M d, Y • h:i A', strtotime($att['purchase_date'])) ?></span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- ─── TAB 4: OPERATIONS & SETTINGS ─── -->
    <div id="tab-operations" class="ed-tab-pane" role="tabpanel">
        <div class="ed-ops-grid">
            
            <!-- Feature on Carousel Card -->
            <div class="ed-card">
                <div class="ed-card-header">
                    <div class="ed-card-title-group">
                        <span class="ed-card-icon">⭐</span>
                        <div>
                            <h2 class="ed-card-title">Homepage Carousel Curation</h2>
                            <p class="ed-card-sub">Pin this event to the prominent homepage hero carousel to maximize visibility.</p>
                        </div>
                    </div>
                </div>
                <div class="ed-card-body">
                    <p class="ed-text-muted">
                        <?php if (!empty($event['is_featured'])): ?>
                            This event is currently <strong>featured</strong> on the public homepage hero carousel.
                        <?php else: ?>
                            This event is currently displayed under standard search and category listings.
                        <?php endif; ?>
                    </p>

                    <form method="POST" action="{{ route('admin/events/toggle-featured') }}" class="ed-mt-4">
                        <?= csrf_field() ?>
                        <input type="hidden" name="event_id" value="<?= $event['id'] ?>" />
                        <?php if (!empty($event['is_featured'])): ?>
                            <button type="submit" class="ed-btn ed-btn-warning">
                                ⭐ Remove from Homepage Carousel
                            </button>
                        <?php else: ?>
                            <button type="submit" class="ed-btn ed-btn-primary">
                                ⭐ Feature on Homepage Carousel
                            </button>
                        <?php endif; ?>
                    </form>
                </div>
            </div>

            <!-- Quick Management Shortcuts Card -->
            <div class="ed-card">
                <div class="ed-card-header">
                    <div class="ed-card-title-group">
                        <span class="ed-card-icon">🚀</span>
                        <div>
                            <h2 class="ed-card-title">Event Operations &amp; Portals</h2>
                            <p class="ed-card-sub">Direct access to host management tooling and staff admission controls.</p>
                        </div>
                    </div>
                </div>
                <div class="ed-card-body">
                    <div class="ed-shortcuts-grid">
                        <a href="{{ route('host/event/edit/{id}', ['id' => $event['id']]) }}" class="ed-shortcut-item">
                            <span class="ed-shortcut-icon">✏️</span>
                            <div>
                                <strong>Edit Event Details</strong>
                                <span>Update title, dates, venue, tickets, and description</span>
                            </div>
                        </a>
                        <a href="{{ route('host/event/management/{id}', ['id' => $event['id']]) }}" class="ed-shortcut-item">
                            <span class="ed-shortcut-icon">⚙️</span>
                            <div>
                                <strong>Manage &amp; Staff Terminal</strong>
                                <span>Gate check-in scanners, staff permissions, and manual tickets</span>
                            </div>
                        </a>
                        <a href="{{ route('host/event/referrals/{id}', ['id' => $event['id']]) }}" class="ed-shortcut-item">
                            <span class="ed-shortcut-icon">🔗</span>
                            <div>
                                <strong>Referrals &amp; Affiliates Hub</strong>
                                <span>Track promoter commissions and campaign links</span>
                            </div>
                        </a>
                        <a href="{{ route('admin/payouts') }}" class="ed-shortcut-item">
                            <span class="ed-shortcut-icon">💳</span>
                            <div>
                                <strong>Platform Payout Center</strong>
                                <span>Approve escrow requests and monitor bank settlements</span>
                            </div>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Danger Zone Card -->
            <div class="ed-card ed-card--danger">
                <div class="ed-card-header">
                    <div class="ed-card-title-group">
                        <span class="ed-card-icon">⚠️</span>
                        <div>
                            <h2 class="ed-card-title ed-text-danger">Danger Zone</h2>
                            <p class="ed-card-sub">Irreversible event lifecycle actions.</p>
                        </div>
                    </div>
                </div>
                <div class="ed-card-body">
                    <div class="ed-danger-content">
                        <div>
                            <strong>Delete Event Permanently</strong>
                            <p class="ed-text-muted">Deleting this event will purge all ticket definitions, buyer rosters, pass codes, and sales records. This cannot be undone.</p>
                        </div>
                        <button type="button" class="ed-btn ed-btn-danger" id="detail-delete-btn">
                            🗑️ Delete Event
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </div>

</div>

<!-- ─── CONFIRM DELETE MODAL ─── -->
<div class="ed-modal-backdrop hidden" id="delete-modal" role="dialog" aria-modal="true" aria-labelledby="modal-del-title">
    <div class="ed-modal-card">
        <div class="ed-modal-head">
            <span class="ed-modal-icon">⚠️</span>
            <div>
                <h3 id="modal-del-title" class="ed-modal-title">Delete Event Permanently</h3>
                <p class="ed-modal-sub">This action cannot be undone.</p>
            </div>
            <button type="button" class="ed-modal-close" id="delete-cancel-x" aria-label="Close modal">✕</button>
        </div>
        <div class="ed-modal-body">
            <p>Are you sure you want to delete <strong><?= e($event['name']) ?></strong>?</p>
            <p class="ed-text-muted">All registered attendees, sales ledgers, check-in histories, and ticket passes will be permanently removed from <?= e($siteName) ?>.</p>
        </div>
        <div class="ed-modal-foot">
            <button type="button" class="ed-btn ed-btn-ghost" id="delete-cancel-btn">Cancel</button>
            <form method="POST" action="{{ route('admin/events') }}" class="ed-inline">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="delete" />
                <input type="hidden" name="event_id" value="<?= $event['id'] ?>" />
                <button type="submit" class="ed-btn ed-btn-danger">Yes, Delete Event</button>
            </form>
        </div>
    </div>
</div>

<!-- ─── MODERN RESPONSIVE STYLES ─── -->
<style>
/* CSS Variables & Theme Foundations */
.ed-shell {
    --ed-primary: var(--primary, <?= $themePrimary ?>);
    --ed-primary-hover: var(--primary-hover, <?= $themeSecondary ?>);
    --ed-primary-light: rgba(var(--primary-rgb, <?= $themePrimaryRgb ?>), 0.12);
    --ed-primary-border: rgba(var(--primary-rgb, <?= $themePrimaryRgb ?>), 0.28);
    
    /* Default / Light Mode */
    --ed-bg-card: rgb(var(--card, 255 255 255));
    --ed-bg-subtle: #f8fafc;
    --ed-bg-subtle-hover: #f1f5f9;
    --ed-border: var(--border-color, #e2e8f0);
    --ed-border-subtle: var(--border-subtle, #f1f5f9);
    --ed-text-main: rgb(var(--text, 15 23 42));
    --ed-text-muted: rgb(var(--muted, 100 116 139));
    --ed-pill-bg: #f1f5f9;
    --ed-pill-text: #475569;
    --ed-input-bg: #ffffff;
    --ed-input-border: var(--border-color, #cbd5e1);
    --ed-modal-foot-bg: #f8fafc;
    --ed-escrow-bg: #f1f5f9;
    --ed-escrow-border: #cbd5e1;
    --ed-series-bg: #f5f3ff;
    --ed-series-border: #ddd6fe;
    --ed-series-text: #4c1d95;
    --ed-series-subtext: #6b21a8;
    --ed-series-btn-bg: #ffffff;
    --ed-radius: 12px;
    --ed-radius-sm: 8px;
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
    margin-bottom: 3rem;
}

/* ─── FULL DARK MODE SUPPORT ─── */
:root[data-theme='dark'] .ed-shell,
[data-theme='dark'] .ed-shell,
body[data-theme='dark'] .ed-shell {
    --ed-bg-card: rgb(var(--card, 18 21 27));
    --ed-bg-subtle: rgba(255, 255, 255, 0.04);
    --ed-bg-subtle-hover: rgba(255, 255, 255, 0.08);
    --ed-border: rgba(255, 255, 255, 0.09);
    --ed-border-subtle: rgba(255, 255, 255, 0.05);
    --ed-text-main: rgb(var(--text, 243 245 247));
    --ed-text-muted: rgb(var(--muted, 152 162 173));
    --ed-pill-bg: rgba(255, 255, 255, 0.08);
    --ed-pill-text: #e2e8f0;
    --ed-input-bg: rgba(255, 255, 255, 0.05);
    --ed-input-border: rgba(255, 255, 255, 0.12);
    --ed-modal-foot-bg: rgba(0, 0, 0, 0.25);
    --ed-escrow-bg: rgba(30, 41, 59, 0.55);
    --ed-escrow-border: rgba(255, 255, 255, 0.1);
    --ed-series-bg: rgba(109, 40, 217, 0.12);
    --ed-series-border: rgba(167, 139, 250, 0.25);
    --ed-series-text: #e9d5ff;
    --ed-series-subtext: #c4b5fd;
    --ed-series-btn-bg: rgba(255, 255, 255, 0.08);
    --ed-primary: var(--primary, <?= $themePrimary ?>);
    --ed-primary-hover: var(--secondary, <?= $themeSecondary ?>);
    --ed-primary-light: rgba(var(--primary-rgb, <?= $themePrimaryRgb ?>), 0.22);
    --ed-primary-border: rgba(var(--primary-rgb, <?= $themePrimaryRgb ?>), 0.4);
}

/* Header & Breadcrumbs */
.ed-header-card {
    background: var(--ed-bg-card);
    border: 1px solid var(--ed-border);
    border-radius: var(--ed-radius);
    padding: 1.5rem;
    box-shadow: 0 1px 3px rgba(0,0,0,0.04);
}
:root[data-theme='dark'] .ed-header-card,
[data-theme='dark'] .ed-header-card {
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.35);
}
.ed-header-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 0.75rem;
    margin-bottom: 1rem;
}
.ed-breadcrumb {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    font-size: 0.85rem;
    color: var(--ed-text-muted);
}
.ed-breadcrumb a {
    color: var(--ed-primary);
    text-decoration: none;
    font-weight: 500;
}
.ed-breadcrumb a:hover {
    text-decoration: underline;
}
.ed-bc-sep {
    color: var(--ed-border);
}
.ed-bc-current {
    color: var(--ed-text-main);
    font-weight: 600;
}
.ed-header-badges {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 0.5rem;
}

/* Status Badges */
.ed-status-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    padding: 0.25rem 0.65rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.03em;
}
.ed-status--live {
    background: #ecfdf5;
    color: #059669;
    border: 1px solid #a7f3d0;
}
.ed-status--completed {
    background: var(--ed-pill-bg);
    color: var(--ed-text-muted);
    border: 1px solid var(--ed-border);
}
.ed-status--draft {
    background: #fffbeb;
    color: #d97706;
    border: 1px solid #fde68a;
}
.ed-status--cancelled {
    background: #fef2f2;
    color: #dc2626;
    border: 1px solid #fecaca;
}
.ed-status--featured {
    background: #fef9c3;
    color: #a16207;
    border: 1px solid #fef08a;
}
.ed-status--category {
    background: #eff6ff;
    color: #2563eb;
    border: 1px solid #bfdbfe;
}
.ed-status--series {
    background: #faf5ff;
    color: #7c3aed;
    border: 1px solid #ddd6fe;
}

/* Dark mode badge overrides */
:root[data-theme='dark'] .ed-status--live,
[data-theme='dark'] .ed-status--live {
    background: rgba(16, 185, 129, 0.15);
    color: #34d399;
    border-color: rgba(16, 185, 129, 0.3);
}
:root[data-theme='dark'] .ed-status--draft,
[data-theme='dark'] .ed-status--draft {
    background: rgba(245, 158, 11, 0.15);
    color: #fbbf24;
    border-color: rgba(245, 158, 11, 0.3);
}
:root[data-theme='dark'] .ed-status--cancelled,
[data-theme='dark'] .ed-status--cancelled {
    background: rgba(239, 68, 68, 0.15);
    color: #f87171;
    border-color: rgba(239, 68, 68, 0.3);
}
:root[data-theme='dark'] .ed-status--featured,
[data-theme='dark'] .ed-status--featured {
    background: rgba(234, 179, 8, 0.15);
    color: #fde047;
    border-color: rgba(234, 179, 8, 0.3);
}
:root[data-theme='dark'] .ed-status--category,
[data-theme='dark'] .ed-status--category {
    background: rgba(59, 130, 246, 0.15);
    color: #93c5fd;
    border-color: rgba(59, 130, 246, 0.3);
}
:root[data-theme='dark'] .ed-status--series,
[data-theme='dark'] .ed-status--series {
    background: rgba(139, 92, 246, 0.15);
    color: #c4b5fd;
    border-color: rgba(139, 92, 246, 0.3);
}

.ed-pulse-dot {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background: #10b981;
    box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
    animation: edPulse 2s infinite;
}
@keyframes edPulse {
    0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
    70% { transform: scale(1); box-shadow: 0 0 0 6px rgba(16, 185, 129, 0); }
    100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
}

/* Event Title & Meta */
.ed-header-body {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 1.5rem;
    flex-wrap: wrap;
}
.ed-header-info {
    flex: 1 1 500px;
}
.ed-event-title {
    font-size: 1.75rem;
    font-weight: 800;
    color: var(--ed-text-main);
    line-height: 1.25;
    margin: 0 0 1rem 0;
}
.ed-header-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 1.25rem 2rem;
}
.ed-meta-item {
    display: flex;
    align-items: flex-start;
    gap: 0.6rem;
    font-size: 0.88rem;
}
.ed-meta-icon {
    font-size: 1.15rem;
    line-height: 1;
}
.ed-meta-item strong {
    display: block;
    color: var(--ed-text-main);
    font-weight: 600;
}
.ed-meta-sub {
    font-size: 0.78rem;
    color: var(--ed-text-muted);
}

/* Header Action Toolbar */
.ed-header-actions {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 0.5rem;
}
.ed-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.4rem;
    padding: 0.55rem 1rem;
    border-radius: var(--ed-radius-sm);
    font-size: 0.85rem;
    font-weight: 600;
    text-decoration: none;
    border: 1px solid transparent;
    cursor: pointer;
    transition: all 0.15s ease-in-out;
    white-space: nowrap;
}
.ed-btn-primary {
    background: var(--ed-primary);
    color: #ffffff !important;
}
.ed-btn-primary:hover {
    background: var(--ed-primary-hover);
    box-shadow: 0 2px 6px rgba(var(--primary-rgb, 2, 132, 199), 0.35);
}
.ed-btn-secondary {
    background: var(--ed-bg-subtle);
    color: var(--ed-text-main);
    border-color: var(--ed-border);
}
.ed-btn-secondary:hover {
    background: var(--ed-bg-subtle-hover);
}
.ed-btn-outline {
    background: transparent;
    color: var(--ed-primary);
    border-color: var(--ed-primary-border);
}
.ed-btn-outline:hover {
    background: var(--ed-primary-light);
}
.ed-btn-accent {
    background: var(--ed-primary);
    color: #ffffff !important;
}
.ed-btn-accent:hover {
    background: var(--ed-primary-hover);
    box-shadow: 0 2px 6px rgba(var(--primary-rgb, <?= $themePrimaryRgb ?>), 0.35);
}
.ed-btn-warning {
    background: #f59e0b;
    color: #ffffff;
}
.ed-btn-warning:hover {
    background: #d97706;
}
.ed-btn-danger {
    background: #ef4444;
    color: #ffffff;
}
.ed-btn-danger:hover {
    background: #dc2626;
}
.ed-btn-ghost {
    background: transparent;
    color: var(--ed-text-muted);
}
.ed-btn-ghost:hover {
    background: var(--ed-bg-subtle);
    color: var(--ed-text-main);
}
.ed-btn-block {
    width: 100%;
}

/* Recurring Series Banner */
.ed-series-banner {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 1rem;
    background: var(--ed-series-bg);
    border: 1px solid var(--ed-series-border);
    border-radius: var(--ed-radius);
    padding: 0.85rem 1.25rem;
}
.ed-series-info {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}
.ed-series-icon {
    font-size: 1.4rem;
}
.ed-series-title {
    font-size: 0.92rem;
    color: var(--ed-series-text);
}
.ed-series-sub {
    font-size: 0.82rem;
    color: var(--ed-series-subtext);
}
.ed-series-controls {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}
.ed-series-btn {
    padding: 0.4rem 0.75rem;
    background: var(--ed-series-btn-bg);
    border: 1px solid var(--ed-series-border);
    border-radius: 6px;
    font-size: 0.8rem;
    font-weight: 600;
    color: var(--ed-series-text);
    text-decoration: none;
    transition: all 0.15s ease;
}
.ed-series-btn:hover:not(.is-disabled) {
    background: var(--ed-bg-subtle-hover);
}
.ed-series-btn.is-disabled {
    opacity: 0.45;
    cursor: not-allowed;
}
.ed-series-select {
    padding: 0.4rem 0.6rem;
    border-radius: 6px;
    border: 1px solid var(--ed-series-border);
    font-size: 0.8rem;
    font-weight: 500;
    background: var(--ed-series-btn-bg);
    color: var(--ed-series-text);
    max-width: 250px;
}

/* Executive KPI Bento Grid */
.ed-kpi-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(230px, 1fr));
    gap: 1rem;
}
.ed-kpi-card {
    background: var(--ed-bg-card);
    border: 1px solid var(--ed-border);
    border-radius: var(--ed-radius);
    padding: 1.25rem;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    box-shadow: 0 1px 3px rgba(0,0,0,0.03);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
:root[data-theme='dark'] .ed-kpi-card,
[data-theme='dark'] .ed-kpi-card {
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.35);
}
.ed-kpi-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.06);
}
:root[data-theme='dark'] .ed-kpi-card:hover,
[data-theme='dark'] .ed-kpi-card:hover {
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.5);
}
.ed-kpi-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 0.75rem;
}
.ed-kpi-icon {
    font-size: 1.4rem;
}
.ed-kpi-badge {
    font-size: 0.72rem;
    font-weight: 700;
    padding: 0.2rem 0.5rem;
    border-radius: 9999px;
    background: var(--ed-pill-bg);
    color: var(--ed-pill-text);
}
.ed-kpi-badge.is-ready {
    background: rgba(16, 185, 129, 0.15);
    color: #059669;
}
:root[data-theme='dark'] .ed-kpi-badge.is-ready,
[data-theme='dark'] .ed-kpi-badge.is-ready {
    color: #34d399;
}
.ed-kpi-badge.is-warning {
    background: rgba(245, 158, 11, 0.15);
    color: #d97706;
}
:root[data-theme='dark'] .ed-kpi-badge.is-warning,
[data-theme='dark'] .ed-kpi-badge.is-warning {
    color: #fbbf24;
}
.ed-kpi-val {
    font-size: 1.85rem;
    font-weight: 800;
    color: var(--ed-text-main);
    line-height: 1.1;
    margin-bottom: 0.25rem;
}
.ed-kpi-label {
    font-size: 0.82rem;
    color: var(--ed-text-muted);
    font-weight: 500;
}
.ed-kpi-progress {
    height: 6px;
    background: var(--ed-bg-subtle);
    border-radius: 9999px;
    overflow: hidden;
    margin-top: 0.75rem;
}
.ed-kpi-prog-bar {
    height: 100%;
    background: var(--ed-primary);
    border-radius: 9999px;
}
.ed-kpi-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 0.35rem;
    margin-top: 1rem;
    padding-top: 0.75rem;
    border-top: 1px solid var(--ed-border);
    font-size: 0.76rem;
    color: var(--ed-text-muted);
}
.ed-kpi-footer strong {
    color: var(--ed-text-main);
}

/* Tabs Bar */
.ed-tabs-bar {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    border-bottom: 2px solid var(--ed-border);
    overflow-x: auto;
    padding-bottom: 2px;
    -webkit-overflow-scrolling: touch;
}
.ed-tab-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.25rem;
    background: transparent;
    border: none;
    border-bottom: 3px solid transparent;
    font-size: 0.92rem;
    font-weight: 600;
    color: var(--ed-text-muted);
    cursor: pointer;
    white-space: nowrap;
    transition: all 0.2s ease;
    margin-bottom: -2px;
}
.ed-tab-btn:hover {
    color: var(--ed-text-main);
    background: var(--ed-bg-subtle);
    border-radius: var(--ed-radius-sm) var(--ed-radius-sm) 0 0;
}
.ed-tab-btn.active {
    color: var(--ed-primary);
    border-bottom-color: var(--ed-primary);
}
.ed-tab-icon {
    font-size: 1.1rem;
}
.ed-tab-count {
    font-size: 0.75rem;
    font-weight: 700;
    padding: 0.15rem 0.45rem;
    background: var(--ed-pill-bg);
    color: var(--ed-pill-text);
    border-radius: 9999px;
}
.ed-tab-btn.active .ed-tab-count {
    background: var(--ed-primary-light);
    color: var(--ed-primary);
}
.ed-tab-pane {
    display: none;
}
.ed-tab-pane.active {
    display: block;
    animation: edFadeIn 0.2s ease-in-out;
}
@keyframes edFadeIn {
    from { opacity: 0; transform: translateY(4px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Card & Container Layouts */
.ed-card {
    background: var(--ed-bg-card);
    border: 1px solid var(--ed-border);
    border-radius: var(--ed-radius);
    box-shadow: 0 1px 3px rgba(0,0,0,0.03);
    overflow: hidden;
}
:root[data-theme='dark'] .ed-card,
[data-theme='dark'] .ed-card {
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.35);
}
.ed-card-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 1rem;
    padding: 1.25rem 1.5rem;
    border-bottom: 1px solid var(--ed-border);
    background: var(--ed-bg-subtle);
}
.ed-card-title-group {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}
.ed-card-icon {
    font-size: 1.35rem;
}
.ed-card-title {
    font-size: 1.15rem;
    font-weight: 700;
    color: var(--ed-text-main);
    margin: 0;
}
.ed-card-sub {
    font-size: 0.82rem;
    color: var(--ed-text-muted);
    margin: 0.2rem 0 0 0;
}
.ed-card-body {
    padding: 1.5rem;
}
.ed-card--danger {
    border-color: #fecaca;
    background: #fffafa;
}
:root[data-theme='dark'] .ed-card--danger,
[data-theme='dark'] .ed-card--danger {
    border-color: rgba(239, 68, 68, 0.3);
    background: rgba(239, 68, 68, 0.06);
}

/* Overview Layout */
.ed-overview-grid {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 1.5rem;
    align-items: start;
}
.ed-overview-left,
.ed-overview-right {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

/* Event Poster & Details */
.ed-event-poster-wrap {
    width: 100%;
    max-height: 280px;
    border-radius: var(--ed-radius-sm);
    overflow: hidden;
    margin-bottom: 1.5rem;
    border: 1px solid var(--ed-border);
}
.ed-event-poster-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}
.ed-details-list {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem 1.5rem;
}
.ed-detail-item {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}
.ed-detail-item--full {
    grid-column: 1 / -1;
}
.ed-detail-label {
    font-size: 0.76rem;
    font-weight: 600;
    text-transform: uppercase;
    color: var(--ed-text-muted);
    letter-spacing: 0.04em;
}
.ed-detail-value {
    font-size: 0.95rem;
    color: var(--ed-text-main);
}
.ed-detail-desc {
    font-size: 0.92rem;
    line-height: 1.6;
    color: var(--ed-text-main);
    background: var(--ed-bg-subtle);
    padding: 1rem;
    border-radius: var(--ed-radius-sm);
    border: 1px solid var(--ed-border);
}

/* Host Profile & Bank Box */
.ed-host-profile {
    display: flex;
    align-items: center;
    gap: 0.85rem;
    margin-bottom: 1.25rem;
}
.ed-host-avatar {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: var(--ed-primary-light);
    color: var(--ed-primary);
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 800;
    font-size: 1.1rem;
    border: 1px solid var(--ed-primary-border);
}
.ed-host-meta {
    display: flex;
    flex-direction: column;
    gap: 0.15rem;
    min-width: 0;
}
.ed-host-name {
    font-size: 1rem;
    color: var(--ed-text-main);
}
.ed-host-email {
    font-size: 0.82rem;
    color: var(--ed-text-muted);
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.ed-host-id {
    font-size: 0.74rem;
    color: var(--ed-text-muted);
}
.ed-bank-box {
    background: var(--ed-bg-subtle);
    border: 1px solid var(--ed-border);
    border-radius: var(--ed-radius-sm);
    padding: 1rem;
    display: flex;
    flex-direction: column;
    gap: 0.65rem;
    margin-bottom: 1.25rem;
}
.ed-bank-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-size: 0.84rem;
}
.ed-bank-label {
    color: var(--ed-text-muted);
}
.ed-bank-val {
    font-weight: 600;
    color: var(--ed-text-main);
}
.ed-card-actions {
    margin-top: 0.5rem;
}

/* Specs List */
.ed-specs-list {
    display: flex;
    flex-direction: column;
}
.ed-spec-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0.85rem 1.5rem;
    border-bottom: 1px solid var(--ed-border);
    font-size: 0.85rem;
}
.ed-spec-item:last-child {
    border-bottom: none;
}
.ed-spec-item span {
    color: var(--ed-text-muted);
}
.ed-spec-item strong {
    color: var(--ed-text-main);
}

/* Modern Data Tables */
.ed-table-wrap {
    width: 100%;
    overflow-x: auto;
}
.ed-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.88rem;
    text-align: left;
}
.ed-table th {
    background: var(--ed-bg-subtle);
    padding: 0.85rem 1.25rem;
    font-size: 0.78rem;
    font-weight: 700;
    text-transform: uppercase;
    color: var(--ed-text-muted);
    letter-spacing: 0.04em;
    border-bottom: 1px solid var(--ed-border);
}
.ed-table td {
    padding: 1rem 1.25rem;
    border-bottom: 1px solid var(--ed-border);
    color: var(--ed-text-main);
    vertical-align: middle;
}
.ed-table tbody tr:hover {
    background: var(--ed-bg-subtle-hover);
}
.ed-table-total-row td {
    background: var(--ed-bg-subtle);
    border-top: 2px solid var(--ed-border);
    font-size: 0.92rem;
}

/* Tiers Table Elements */
.ed-tier-name {
    display: block;
    color: var(--ed-text-main);
}
.ed-tier-desc {
    font-size: 0.76rem;
    color: var(--ed-text-muted);
    margin-top: 0.15rem;
}
.ed-price-pill {
    display: inline-block;
    padding: 0.25rem 0.55rem;
    border-radius: 6px;
    background: var(--ed-pill-bg);
    font-weight: 700;
    color: var(--ed-text-main);
}
.ed-prog-cell {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    min-width: 130px;
}
.ed-progress-track {
    flex: 1;
    height: 6px;
    background: var(--ed-bg-subtle);
    border-radius: 9999px;
    overflow: hidden;
}
.ed-progress-fill {
    height: 100%;
    background: var(--ed-primary);
    border-radius: 9999px;
}
.ed-prog-label {
    font-size: 0.76rem;
    font-weight: 600;
    color: var(--ed-text-muted);
    min-width: 32px;
}

/* Mobile Tiers Cards */
.ed-mobile-tiers,
.ed-mobile-payouts,
.ed-mobile-attendees {
    display: none;
    flex-direction: column;
    gap: 0.75rem;
    padding: 1rem;
}
.ed-tier-card,
.ed-payout-card,
.ed-att-card {
    background: var(--ed-bg-card);
    border: 1px solid var(--ed-border);
    border-radius: var(--ed-radius-sm);
    padding: 1rem;
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    box-shadow: 0 1px 2px rgba(0,0,0,0.03);
}
.ed-tier-card-top,
.ed-payout-card-top,
.ed-att-card-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.ed-tier-card-progress {
    display: flex;
    flex-direction: column;
    gap: 0.35rem;
}
.ed-tier-prog-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-size: 0.78rem;
    color: var(--ed-text-muted);
}
.ed-tier-card-bottom {
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-size: 0.85rem;
    padding-top: 0.5rem;
    border-top: 1px solid var(--ed-border);
}

/* Financials Strip */
.ed-fin-strip {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    margin-bottom: 1.5rem;
}
.ed-fin-tile {
    background: var(--ed-bg-subtle);
    border: 1px solid var(--ed-border);
    border-radius: var(--ed-radius-sm);
    padding: 1.15rem;
    display: flex;
    flex-direction: column;
    gap: 0.35rem;
}
.ed-fin-tile--highlight {
    background: var(--ed-primary-light);
    border-color: var(--ed-primary-border);
}
.ed-fin-lbl {
    font-size: 0.78rem;
    font-weight: 600;
    color: var(--ed-text-muted);
}
.ed-fin-val {
    font-size: 1.6rem;
    font-weight: 800;
    color: var(--ed-text-main);
    line-height: 1.15;
}
.ed-fin-sub {
    font-size: 0.76rem;
    color: var(--ed-text-muted);
}

/* Escrow Intel Banner */
.ed-escrow-intel {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 1rem;
    background: var(--ed-escrow-bg);
    border: 1px solid var(--ed-escrow-border);
    border-radius: var(--ed-radius-sm);
    padding: 1.15rem 1.35rem;
    margin-bottom: 2rem;
}
.ed-escrow-intel-left {
    display: flex;
    align-items: center;
    gap: 0.85rem;
}
.ed-escrow-intel-icon {
    font-size: 1.6rem;
}
.ed-escrow-intel-title {
    font-size: 0.95rem;
    color: var(--ed-text-main);
}
.ed-escrow-intel-desc {
    font-size: 0.82rem;
    color: var(--ed-text-muted);
    margin-top: 0.15rem;
}
.ed-escrow-intel-right {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 0.5rem;
}

/* Ledger Header */
.ed-ledger-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 1rem;
}
.ed-ledger-title {
    font-size: 1.05rem;
    font-weight: 700;
    color: var(--ed-text-main);
    margin: 0;
}
.ed-payout-card-amount {
    font-size: 1.3rem;
    font-weight: 800;
    color: var(--ed-primary);
}
.ed-payout-card-meta {
    display: flex;
    flex-direction: column;
    gap: 0.35rem;
    font-size: 0.82rem;
}
.ed-payout-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
}

/* Attendees Stream Toolbar */
.ed-attendees-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 1rem;
}
.ed-attendees-toolbar {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 0.75rem;
}
.ed-search-wrap {
    position: relative;
    min-width: 280px;
}
.ed-search-icon {
    position: absolute;
    left: 0.85rem;
    top: 50%;
    transform: translateY(-50%);
    font-size: 0.9rem;
    pointer-events: none;
    opacity: 0.5;
}
.ed-search-input {
    width: 100%;
    padding: 0.55rem 0.85rem 0.55rem 2.2rem;
    border: 1px solid var(--ed-input-border);
    border-radius: var(--ed-radius-sm);
    font-size: 0.85rem;
    background: var(--ed-input-bg);
    color: var(--ed-text-main);
}
.ed-search-input:focus {
    outline: none;
    border-color: var(--ed-primary);
    box-shadow: 0 0 0 3px var(--ed-primary-light);
}
.ed-export-btns {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

/* Attendee Cells */
.ed-pass-cell {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
}
.ed-code-pill {
    font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
    font-size: 0.78rem;
    padding: 0.2rem 0.5rem;
    background: var(--ed-pill-bg);
    border-radius: 4px;
    font-weight: 600;
    color: var(--ed-pill-text);
}
.ed-copy-btn {
    background: transparent;
    border: none;
    font-size: 0.85rem;
    cursor: pointer;
    padding: 0.15rem 0.35rem;
    border-radius: 4px;
    color: var(--ed-text-muted);
    transition: background 0.15s ease;
}
.ed-copy-btn:hover {
    background: var(--ed-bg-subtle-hover);
    color: var(--ed-text-main);
}
.ed-user-cell {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}
.ed-user-avatar {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 0.85rem;
    flex-shrink: 0;
}
.ed-user-meta {
    display: flex;
    flex-direction: column;
    min-width: 0;
}
.ed-user-name {
    font-size: 0.88rem;
    color: var(--ed-text-main);
}
.ed-user-email {
    font-size: 0.76rem;
    color: var(--ed-text-muted);
}
.ed-att-card-badges {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 0.4rem;
}
.ed-att-card-bottom {
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-size: 0.78rem;
    padding-top: 0.5rem;
    border-top: 1px solid var(--ed-border);
}

/* Generic Pills */
.ed-pill {
    display: inline-flex;
    align-items: center;
    padding: 0.2rem 0.6rem;
    border-radius: 9999px;
    font-size: 0.74rem;
    font-weight: 600;
    background: var(--ed-pill-bg);
    color: var(--ed-pill-text);
}
.ed-pill--brand {
    background: var(--ed-primary-light);
    color: var(--ed-primary);
}
.ed-pill--success {
    background: #ecfdf5;
    color: #059669;
}
:root[data-theme='dark'] .ed-pill--success,
[data-theme='dark'] .ed-pill--success {
    background: rgba(16, 185, 129, 0.15);
    color: #34d399;
}
.ed-pill--warning {
    background: #fffbeb;
    color: #d97706;
}
:root[data-theme='dark'] .ed-pill--warning,
[data-theme='dark'] .ed-pill--warning {
    background: rgba(245, 158, 11, 0.15);
    color: #fbbf24;
}
.ed-pill--danger {
    background: #fef2f2;
    color: #dc2626;
}
:root[data-theme='dark'] .ed-pill--danger,
[data-theme='dark'] .ed-pill--danger {
    background: rgba(239, 68, 68, 0.15);
    color: #f87171;
}
.ed-pill--indigo {
    background: #eef2ff;
    color: #4f46e5;
}
:root[data-theme='dark'] .ed-pill--indigo,
[data-theme='dark'] .ed-pill--indigo {
    background: rgba(99, 102, 241, 0.15);
    color: #a5b4fc;
}
.ed-pill--category {
    background: #eff6ff;
    color: #2563eb;
}
:root[data-theme='dark'] .ed-pill--category,
[data-theme='dark'] .ed-pill--category {
    background: rgba(59, 130, 246, 0.15);
    color: #93c5fd;
}
.ed-pill--neutral {
    background: var(--ed-bg-subtle);
    color: var(--ed-text-muted);
    border: 1px solid var(--ed-border);
}

/* Operations Tab */
.ed-ops-grid {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}
.ed-shortcuts-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 1rem;
}
.ed-shortcut-item {
    display: flex;
    align-items: flex-start;
    gap: 0.85rem;
    padding: 1rem;
    border: 1px solid var(--ed-border);
    border-radius: var(--ed-radius-sm);
    text-decoration: none;
    background: var(--ed-bg-subtle);
    transition: all 0.15s ease;
}
.ed-shortcut-item:hover {
    background: var(--ed-bg-card);
    border-color: var(--ed-primary);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
}
:root[data-theme='dark'] .ed-shortcut-item:hover,
[data-theme='dark'] .ed-shortcut-item:hover {
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.4);
}
.ed-shortcut-icon {
    font-size: 1.35rem;
}
.ed-shortcut-item strong {
    display: block;
    color: var(--ed-text-main);
    font-size: 0.92rem;
    margin-bottom: 0.2rem;
}
.ed-shortcut-item span {
    font-size: 0.78rem;
    color: var(--ed-text-muted);
    line-height: 1.4;
}
.ed-danger-content {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 1rem;
}

/* Modal */
.ed-modal-backdrop {
    position: fixed;
    inset: 0;
    background: rgba(15, 23, 42, 0.65);
    backdrop-filter: blur(4px);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
    padding: 1rem;
}
.ed-modal-backdrop.hidden {
    display: none;
}
.ed-modal-card {
    background: var(--ed-bg-card);
    border: 1px solid var(--ed-border);
    border-radius: var(--ed-radius);
    max-width: 480px;
    width: 100%;
    box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2), 0 10px 10px -5px rgba(0,0,0,0.1);
    overflow: hidden;
    animation: edModalPop 0.2s cubic-bezier(0.16, 1, 0.3, 1);
}
@keyframes edModalPop {
    from { opacity: 0; transform: scale(0.95); }
    to { opacity: 1; transform: scale(1); }
}
.ed-modal-head {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 1.25rem 1.5rem;
    border-bottom: 1px solid var(--ed-border);
    position: relative;
    background: var(--ed-bg-subtle);
}
.ed-modal-icon {
    font-size: 1.5rem;
}
.ed-modal-title {
    margin: 0;
    font-size: 1.15rem;
    color: #dc2626;
}
.ed-modal-sub {
    margin: 0.15rem 0 0 0;
    font-size: 0.8rem;
    color: var(--ed-text-muted);
}
.ed-modal-close {
    position: absolute;
    right: 1.25rem;
    top: 1.25rem;
    background: transparent;
    border: none;
    font-size: 1.1rem;
    color: var(--ed-text-muted);
    cursor: pointer;
}
.ed-modal-close:hover {
    color: var(--ed-text-main);
}
.ed-modal-body {
    padding: 1.5rem;
    font-size: 0.9rem;
    line-height: 1.5;
    color: var(--ed-text-main);
}
.ed-modal-foot {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 0.75rem;
    padding: 1rem 1.5rem;
    background: var(--ed-modal-foot-bg);
    border-top: 1px solid var(--ed-border);
}

/* Empty State */
.ed-empty-state {
    text-align: center;
    padding: 3rem 1.5rem;
    color: var(--ed-text-muted);
}
.ed-empty-state span {
    font-size: 2.2rem;
    display: block;
    margin-bottom: 0.5rem;
}
.ed-empty-state p {
    font-size: 0.95rem;
    font-weight: 500;
    margin: 0;
}

/* Helper Utilities */
.ed-p-0 { padding: 0 !important; }
.ed-mt-4 { margin-top: 1rem; }
.ed-text-bold { font-weight: 700; }
.ed-text-right { text-align: right; }
.ed-text-primary { color: var(--ed-primary) !important; }
.ed-text-success { color: #059669 !important; }
:root[data-theme='dark'] .ed-text-success,
[data-theme='dark'] .ed-text-success { color: #34d399 !important; }
.ed-text-purple { color: #7c3aed !important; }
:root[data-theme='dark'] .ed-text-purple,
[data-theme='dark'] .ed-text-purple { color: #a78bfa !important; }
.ed-text-teal { color: #0d9488 !important; }
:root[data-theme='dark'] .ed-text-teal,
[data-theme='dark'] .ed-text-teal { color: #2dd4bf !important; }
.ed-text-brand { color: var(--ed-primary) !important; }
.ed-text-danger { color: #dc2626 !important; }
:root[data-theme='dark'] .ed-text-danger,
[data-theme='dark'] .ed-text-danger { color: #f87171 !important; }
.ed-text-muted { color: var(--ed-text-muted) !important; }
.ed-link { color: var(--ed-primary); text-decoration: none; font-weight: 500; }
.ed-link:hover { text-decoration: underline; }
.ed-code-text { font-family: ui-monospace, monospace; }
.ed-inline { display: inline; }
.sr-only { position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px; overflow: hidden; clip: rect(0,0,0,0); border: 0; }

/* ─── RESPONSIVE BREAKPOINTS ─── */
@media (max-width: 1024px) {
    .ed-overview-grid {
        grid-template-columns: 1fr;
    }
    .ed-header-body {
        flex-direction: column;
    }
    .ed-header-actions {
        width: 100%;
    }
}

@media (max-width: 768px) {
    .ed-table-wrap {
        display: none !important;
    }
    .ed-mobile-tiers,
    .ed-mobile-payouts,
    .ed-mobile-attendees {
        display: flex !important;
    }
    .ed-event-title {
        font-size: 1.4rem;
    }
    .ed-details-list {
        grid-template-columns: 1fr;
    }
    .ed-attendees-header {
        flex-direction: column;
        align-items: stretch;
    }
    .ed-attendees-toolbar {
        flex-direction: column;
        align-items: stretch;
    }
    .ed-search-wrap {
        width: 100%;
    }
    .ed-export-btns {
        width: 100%;
        justify-content: stretch;
    }
    .ed-export-btns .ed-btn {
        flex: 1;
    }
    .ed-fin-strip {
        grid-template-columns: 1fr 1fr;
    }
    .ed-danger-content {
        flex-direction: column;
        align-items: stretch;
    }
    .ed-danger-content .ed-btn {
        width: 100%;
    }
    .ed-header-actions .ed-btn {
        flex: 1 1 45%;
    }
}

@media (max-width: 480px) {
    .ed-kpi-grid {
        grid-template-columns: 1fr;
    }
    .ed-fin-strip {
        grid-template-columns: 1fr;
    }
    .ed-header-actions .ed-btn {
        flex: 1 1 100%;
    }
}
</style>

<!-- ─── TAB NAVIGATION & CLIENT INTERACTIVITY ─── -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // ─── 1. Persistent Tab Navigation ───
    const tabBtns = document.querySelectorAll('.ed-tab-btn');
    const tabPanes = document.querySelectorAll('.ed-tab-pane');

    function activateTab(targetId) {
        if (!targetId) return;
        const targetBtn = document.querySelector(`.ed-tab-btn[data-tab="${targetId}"]`);
        const targetPane = document.getElementById(targetId);

        if (targetBtn && targetPane) {
            tabBtns.forEach(btn => {
                btn.classList.remove('active');
                btn.setAttribute('aria-selected', 'false');
            });
            tabPanes.forEach(pane => pane.classList.remove('active'));

            targetBtn.classList.add('active');
            targetBtn.setAttribute('aria-selected', 'true');
            targetPane.classList.add('active');
        }
    }

    tabBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const target = this.getAttribute('data-tab');
            activateTab(target);
            history.replaceState(null, null, '#' + target.replace('tab-', ''));
        });
    });

    // Check hash on page load (e.g. #financials, #attendees, #operations)
    if (window.location.hash) {
        const hash = window.location.hash.replace('#', '');
        const mappedTabId = 'tab-' + hash;
        if (document.getElementById(mappedTabId)) {
            activateTab(mappedTabId);
        }
    }

    // ─── 2. Attendee Realtime Filter ───
    const attendeeSearch = document.getElementById('attendeeFilterInput');
    const attendeeRows = document.querySelectorAll('.event-attendee-row');

    if (attendeeSearch && attendeeRows.length) {
        attendeeSearch.addEventListener('input', function() {
            const q = this.value.toLowerCase().trim();
            attendeeRows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(q) ? '' : 'none';
            });
        });
    }

    // ─── 3. Delete Confirmation Modal ───
    const deleteBtn = document.getElementById('detail-delete-btn');
    const deleteModal = document.getElementById('delete-modal');
    const cancelBtn = document.getElementById('delete-cancel-btn');
    const cancelX = document.getElementById('delete-cancel-x');

    function openModal() {
        if (deleteModal) deleteModal.classList.remove('hidden');
    }
    function closeModal() {
        if (deleteModal) deleteModal.classList.add('hidden');
    }

    if (deleteBtn) deleteBtn.addEventListener('click', openModal);
    if (cancelBtn) cancelBtn.addEventListener('click', closeModal);
    if (cancelX) cancelX.addEventListener('click', closeModal);

    if (deleteModal) {
        deleteModal.addEventListener('click', function(e) {
            if (e.target === deleteModal) closeModal();
        });
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && !deleteModal.classList.contains('hidden')) {
                closeModal();
            }
        });
    }
});
</script>
@endsection
