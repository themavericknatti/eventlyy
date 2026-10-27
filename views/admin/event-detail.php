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
<section class="page-hero event-detail-page-hero">
    <div class="event-detail-hero-title">
        <span class="panel-badge">Event detail</span>
        <h1 style="margin:0.25rem 0;">Event performance and host overview</h1>
        <p class="lede" style="margin:0;">Inspect event metrics, ticket status, and organizer payment readiness.</p>
    </div>
    <div class="event-detail-actions-grid">
        <a href="{{ route('admin/events') }}" class="admin-action-btn event-detail-btn event-detail-btn-default">
            <span>←</span> All Events
        </a>
        <a href="{{ route('admin/overview') }}" class="admin-action-btn event-detail-btn event-detail-btn-default">
            <span>🏛️</span> Dashboard
        </a>
        <a href="{{ route('host/event/edit/{id}', ['id' => $event['id']]) }}" class="admin-action-btn primary event-detail-btn event-detail-btn-primary">
            <span>✏️</span> Edit Event
        </a>
        <a href="{{ route('host/event/management/{id}', ['id' => $event['id']]) }}" class="admin-action-btn event-detail-btn event-detail-btn-default">
            <span>⚙️</span> Manage &amp; Staff
        </a>
        <a href="{{ route('host/event/referrals/{id}', ['id' => $event['id']]) }}" class="admin-action-btn event-detail-btn event-detail-btn-success">
            <span>🔗</span> Referrals Hub
        </a>
        <?php 
            $publicSlug = !empty($event['custom_url']) ? $event['custom_url'] : ($event['slug'] ?? $event['id']);
        ?>
        <a href="/event/<?= e($publicSlug) ?>" target="_blank" rel="noopener noreferrer" class="admin-action-btn event-detail-btn event-detail-btn-public">
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
    $early_escrow_disbursed = $early_escrow_disbursed ?? 0;
    $auto_payouts_disbursed = $auto_payouts_disbursed ?? 0;
    $pending_payouts = $pending_payouts ?? 0;
    $remaining_balance = $remaining_balance ?? max(0.00, $host_earnings - $disbursed_payouts);
    $available_to_disburse = $available_to_disburse ?? max(0.00, $host_earnings - $disbursed_payouts - $pending_payouts);
    $event_payouts = $event_payouts ?? [];
    $recurring_occurrences = $recurring_occurrences ?? [];
    $tickets_sold = $tickets_sold ?? 0;
    $total_capacity = $total_capacity ?? 0;
    $hostBankName = $host_bank_name ?? '';
    $hostBankAccount = $host_bank_account ?? '';

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
?>

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
    <!-- Recurring Series Occurrence Quick Switcher Banner -->
    <div class="occurrence-switcher-banner" style="background: linear-gradient(135deg, rgba(99, 102, 241, 0.1), rgba(168, 85, 247, 0.06)); border: 1.5px solid rgba(99, 102, 241, 0.35); border-radius: 14px; padding: 1.1rem 1.4rem; margin-bottom: 1.75rem; display: flex; align-items: center; justify-content: space-between; gap: 1rem; flex-wrap: wrap;">
        <div style="display: flex; align-items: center; gap: 0.85rem;">
            <div style="width: 44px; height: 44px; border-radius: 10px; background: rgba(99, 102, 241, 0.18); display: flex; align-items: center; justify-content: center; font-size: 1.4rem; flex-shrink: 0;">
                🔄
            </div>
            <div>
                <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                    <span style="font-size: 0.72rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; background: #6366f1; color: #fff; padding: 0.15rem 0.55rem; border-radius: 6px;">
                        Recurring Series
                    </span>
                    <strong style="color: rgb(var(--text)); font-size: 1rem;">
                        Viewing Occurrence #<?= e($event['recurrence_sequence'] ?? ($currentIndex + 1)) ?> of <?= $totalOcc ?>
                    </strong>
                </div>
                <div style="font-size: 0.85rem; color: rgb(var(--muted)); margin-top: 0.25rem;">
                    Schedule Date: <strong style="color: rgb(var(--text));"><?= date('D, M j, Y • g:i A', strtotime($event['date'])) ?></strong>
                </div>
            </div>
        </div>

        <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
            <?php if ($prevOcc): ?>
                <a href="{{ route('admin/events/' . $prevOcc['id']) }}" class="admin-action-btn" style="padding: 0.45rem 0.75rem; font-size: 0.82rem; background: rgba(var(--text), 0.06); text-decoration: none; border-radius: 8px; font-weight:600;" title="Go to Previous Date">
                    ← Prev
                </a>
            <?php endif; ?>

            <div style="display: flex; align-items: center; gap: 0.4rem;">
                <label for="admin-occ-jump" style="font-size: 0.82rem; font-weight: 700; color: #818cf8; white-space: nowrap;">
                    Switch Date:
                </label>
                <select id="admin-occ-jump" class="admin-detail-dates-select" style="min-width: 260px;" onchange="if(this.value && this.value != '<?= $event['id'] ?>') window.location.href = '<?= route('admin/events/') ?>' + this.value;">
                    <?php foreach ($recurring_occurrences as $occ): ?>
                        <option value="<?= $occ['id'] ?>" <?= $occ['id'] == $event['id'] ? 'selected' : '' ?>>
                            #<?= e($occ['recurrence_sequence'] ?? '?') ?>  —  <?= date('D, M d, Y • g:i A', strtotime($occ['date'])) ?>  <?= $occ['id'] == $event['id'] ? '  ★ (Viewing)' : (strtotime($occ['date']) < time() ? '  (Past)' : '  (Upcoming)') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <?php if ($nextOcc): ?>
                <a href="{{ route('admin/events/' . $nextOcc['id']) }}" class="admin-action-btn" style="padding: 0.45rem 0.75rem; font-size: 0.82rem; background: rgba(var(--text), 0.06); text-decoration: none; border-radius: 8px; font-weight:600;" title="Go to Next Date">
                    Next →
                </a>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

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
            <?php if (($early_escrow_disbursed ?? 0) > 0): ?>
                <span style="font-size:0.73rem; color:#6366f1; font-weight:700; display:block; margin-top:2px;">
                    🛡️ <?= format_price($early_escrow_disbursed) ?> early escrow
                </span>
            <?php endif; ?>
            <span style="font-size:0.75rem; color:rgb(var(--muted)); display:block; margin-top:2px;">
                <?= format_price($remaining_balance) ?> balance remaining
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
<section class="admin-panel" style="margin-bottom: 1.5rem; border: 1px solid rgba(var(--border), 0.7); background: rgb(var(--card)); border-radius: 18px; overflow: hidden;">
    <div class="section-header" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:0.5rem; padding: 1.25rem 1.5rem; border-bottom: 1px solid rgba(var(--border), 0.5);">
        <div>
            <h2 style="margin:0; font-size: 1.2rem; display: flex; align-items: center; gap: 0.5rem;">
                <span>💰</span> Financial Earnings &amp; Payout Settlement
            </h2>
            <p class="section-note" style="margin:0.25rem 0 0;">Complete revenue breakdown, platform fees, early escrow disbursements, and balance reconciliation.</p>
        </div>
        <div style="display:flex; gap:0.5rem; align-items:center; flex-wrap: wrap;">
            <a href="{{ route('admin/payouts') }}" class="admin-action-btn primary" style="font-size:0.82rem; text-decoration:none; padding:0.45rem 0.95rem; font-weight: 700; border-radius: 10px;">
                💳 Open Payout Center →
            </a>
        </div>
    </div>

    <div class="event-item" style="padding: 1.35rem 1.5rem;">
        <!-- Primary 5-Metric Strip -->
        <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1rem; background: rgba(var(--muted), 0.04); padding: 1.25rem; border-radius: 14px; border: 1px solid rgba(var(--border), 0.5); margin-bottom: 1.25rem;">
            <div>
                <span style="font-size:0.75rem; font-weight:700; color:rgb(var(--muted)); text-transform:uppercase; letter-spacing:0.04em;">Online Gross Sales</span>
                <div style="font-size:1.3rem; font-weight:800; color:rgb(var(--text)); margin-top:0.25rem;"><?= format_price($gross_revenue) ?></div>
                <span style="font-size:0.75rem; color:rgb(var(--muted));"><?= number_format($tickets_sold) ?> tickets purchased online</span>
                <?php if (($tickets_issued ?? 0) > 0): ?>
                    <span style="font-size:0.72rem; color:#6366f1; display:block; margin-top:2px;">+<?= number_format($tickets_issued) ?> issued offline</span>
                <?php endif; ?>
            </div>
            <div>
                <span style="font-size:0.75rem; font-weight:700; color:#8b5cf6; text-transform:uppercase; letter-spacing:0.04em;">Platform Retained Fees</span>
                <div style="font-size:1.3rem; font-weight:800; color:#8b5cf6; margin-top:0.25rem;"><?= format_price($platform_profit) ?></div>
                <span style="font-size:0.75rem; color:rgb(var(--muted));"><?= e(!empty($event['pass_fee']) ? 'Buyer paid fees (+5% & processing)' : 'Host absorbed platform fees') ?></span>
            </div>
            <div>
                <span style="font-size:0.75rem; font-weight:700; color:#10b981; text-transform:uppercase; letter-spacing:0.04em;">Host Net Earnings</span>
                <div style="font-size:1.3rem; font-weight:800; color:#10b981; margin-top:0.25rem;"><?= format_price($host_earnings) ?></div>
                <span style="font-size:0.75rem; color:rgb(var(--muted));">Total payout entitlement for host</span>
            </div>
            <div>
                <span style="font-size:0.75rem; font-weight:700; color:#14b8a6; text-transform:uppercase; letter-spacing:0.04em;">Total Disbursed</span>
                <div style="font-size:1.3rem; font-weight:800; color:#14b8a6; margin-top:0.25rem;"><?= format_price($disbursed_payouts) ?></div>
                <div style="font-size:0.75rem; margin-top:2px;">
                    <?php if (($early_escrow_disbursed ?? 0) > 0): ?>
                        <span style="color:#6366f1; font-weight:700;">🛡️ <?= format_price($early_escrow_disbursed) ?> early escrow</span>
                    <?php else: ?>
                        <span style="color:rgb(var(--muted));">No early escrow disbursed</span>
                    <?php endif; ?>
                </div>
            </div>
            <div style="background: rgba(var(--brand), 0.05); padding: 0.75rem 0.85rem; border-radius: 10px; border: 1.5px solid rgba(var(--brand), 0.2);">
                <span style="font-size:0.75rem; font-weight:700; color:rgb(var(--brand)); text-transform:uppercase; letter-spacing:0.04em;"><?= ($is_event_passed ?? false) ? 'Remaining Unpaid Balance' : 'Escrow Custody Balance' ?></span>
                <div style="font-size:1.35rem; font-weight:900; color:rgb(var(--brand)); margin-top:0.25rem;"><?= format_price($remaining_balance) ?></div>
                <?php if (($pending_payouts ?? 0) > 0): ?>
                    <span style="font-size:0.73rem; color:#f59e0b; font-weight:700; display:block; margin-top:2px;">⚠️ <?= format_price($pending_payouts) ?> pending approval &bull; <?= format_price($available_to_disburse ?? max(0, $remaining_balance - $pending_payouts)) ?> uncommitted</span>
                <?php else: ?>
                    <span style="font-size:0.73rem; color:#10b981; font-weight:600; display:block; margin-top:2px;">✓ <?= format_price($available_to_disburse ?? $remaining_balance) ?> available to disburse</span>
                <?php endif; ?>
            </div>
        </div>

        <!-- Escrow & Readiness Intelligence Banner -->
        <?php
            $eventTimestamp = strtotime($event['date']);
            $isEventPassed = $is_event_passed ?? ($eventTimestamp < time());
            $eventHoursPast = ($isEventPassed) ? round((time() - $eventTimestamp) / 3600, 1) : 0;
        ?>
        <div style="background: rgba(var(--text), 0.02); border: 1px solid rgba(var(--muted), 0.15); border-radius: 12px; padding: 0.9rem 1.15rem; margin-bottom: 1.25rem; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 0.75rem;">
            <div style="display:flex; align-items:center; gap:0.65rem; min-width:0;">
                <span style="font-size: 1.3rem;">🏦</span>
                <div>
                    <div style="font-size: 0.88rem; font-weight: 700; color: rgb(var(--text));">
                        Host Payout Account: <?= e($hostBankName ?: 'Not Linked') ?> 
                        <?php if (!empty($hostBankAccount)): ?>
                            <span style="font-family: monospace; font-weight: 600; color: rgb(var(--muted));">(••••<?= substr($hostBankAccount, -4) ?>)</span>
                        <?php endif; ?>
                    </div>
                    <div style="font-size: 0.78rem; color: rgb(var(--muted)); margin-top: 1px;">
                        Readiness: <strong><?= e($payoutReady) ?></strong> &bull; 
                        <?php if ($isEventPassed): ?>
                            Event completed <?= $eventHoursPast ?>h ago &bull; Funds unlocked for final payout
                        <?php else: ?>
                            Event upcoming &bull; Balance safeguarded in Escrow Custody until completion or early release
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div style="display:flex; align-items:center; gap:0.5rem; flex-wrap: wrap;">
                <?php if (($early_escrow_disbursed ?? 0) > 0): ?>
                    <span style="font-size:0.76rem; font-weight:700; background:rgba(99,102,241,0.12); color:#6366f1; padding:0.25rem 0.65rem; border-radius:8px; border:1px solid rgba(99,102,241,0.25);">
                        🛡️ Early Escrow Released: <?= format_price($early_escrow_disbursed) ?>
                    </span>
                <?php endif; ?>
                <?php if (($pending_payouts ?? 0) > 0): ?>
                    <span style="font-size:0.76rem; font-weight:700; background:rgba(245,158,11,0.12); color:#d97706; padding:0.25rem 0.65rem; border-radius:8px; border:1px solid rgba(245,158,11,0.25);">
                        ⚠️ Pending: <?= format_price($pending_payouts) ?>
                    </span>
                <?php endif; ?>
                <span style="font-size:0.76rem; font-weight:700; background:<?= ($available_to_disburse ?? $remaining_balance) > 0 ? 'rgba(16,185,129,0.12)' : 'rgba(var(--muted),0.1)' ?>; color:<?= ($available_to_disburse ?? $remaining_balance) > 0 ? '#10b981' : 'rgb(var(--muted))' ?>; padding:0.25rem 0.65rem; border-radius:8px;">
                    <?= $isEventPassed ? 'Available to Settle' : 'In Escrow Custody' ?>: <?= format_price($available_to_disburse ?? $remaining_balance) ?>
                </span>
            </div>
        </div>

        <!-- Detailed Payouts & Early Escrow Disbursement Ledger -->
        <div>
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.75rem;">
                <h3 style="margin:0; font-size:0.98rem; font-weight:800; color:rgb(var(--text));">
                    📜 Event Disbursement &amp; Early Escrow Audit Trail
                </h3>
                <span style="font-size:0.78rem; color:rgb(var(--muted));">
                    <?= count($event_payouts) ?> payout record<?= count($event_payouts) === 1 ? '' : 's' ?>
                </span>
            </div>

            <?php if (empty($event_payouts)): ?>
                <div style="padding: 1.5rem; text-align: center; background: rgba(var(--muted), 0.02); border-radius: 12px; border: 1px dashed rgba(var(--muted), 0.2); color: rgb(var(--muted)); font-size: 0.88rem;">
                    No payouts have been processed for this event yet. Total payable balance is <strong><?= format_price($remaining_balance) ?></strong>.
                </div>
            <?php else: ?>
                <div class="ed-payout-table-wrap" style="overflow-x: auto; -webkit-overflow-scrolling: touch; border-radius: 12px; border: 1px solid rgba(var(--border), 0.5);">
                    <table class="au-table" id="eventPayoutsTable" style="margin: 0; width: 100%;">
                        <thead>
                            <tr style="background: rgba(var(--muted), 0.04);">
                                <th>Payout ID</th>
                                <th>Disbursement Type</th>
                                <th>Amount Disbursed</th>
                                <th>Destination Account</th>
                                <th>Status</th>
                                <th>Timestamp</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($event_payouts as $po): 
                                $isEscrow = ($po['type'] === 'escrow');
                                $isAuto = ($po['type'] === 'auto_completed');
                                $isCompleted = in_array($po['status'], ['processed', 'completed']);
                                $isPending = ($po['status'] === 'pending');
                                $statusBadgeClass = $isCompleted ? 'au-status--active' : ($isPending ? 'au-status--unverified' : 'au-status--suspended');
                            ?>
                            <tr>
                                <td style="font-family: monospace; font-weight: 700; font-size: 0.84rem;">#P-<?= e($po['id']) ?></td>
                                <td>
                                    <?php if ($isEscrow): ?>
                                        <span style="font-size:0.75rem; font-weight:700; background:rgba(99,102,241,0.12); color:#6366f1; padding:0.2rem 0.6rem; border-radius:6px; display:inline-flex; align-items:center; gap:0.25rem;">
                                            🛡️ Early Escrow Disbursement
                                        </span>
                                    <?php elseif ($isAuto): ?>
                                        <span style="font-size:0.75rem; font-weight:700; background:rgba(16,185,129,0.12); color:#10b981; padding:0.2rem 0.6rem; border-radius:6px; display:inline-flex; align-items:center; gap:0.25rem;">
                                            ✅ Auto-Completion Settlement
                                        </span>
                                    <?php else: ?>
                                        <span style="font-size:0.75rem; font-weight:700; background:rgba(var(--muted),0.12); color:rgb(var(--text)); padding:0.2rem 0.6rem; border-radius:6px;">
                                            💳 <?= ucfirst(str_replace('_', ' ', $po['type'])) ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong style="font-size:0.92rem; color:rgb(var(--text));"><?= format_price($po['amount']) ?></strong>
                                </td>
                                <td style="font-size:0.84rem; color:rgb(var(--muted));">
                                    <?= e($po['payout_bank'] ?: ($hostBankName ?: 'Host Bank')) ?>
                                    <?php if (!empty($po['payout_account'] ?: $hostBankAccount)): ?>
                                        (••••<?= substr($po['payout_account'] ?: $hostBankAccount, -4) ?>)
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="au-status-badge <?= $statusBadgeClass ?>" style="font-weight:700; font-size:0.75rem;">
                                        <?= ucfirst($po['status']) ?>
                                    </span>
                                </td>
                                <td style="font-size:0.8rem; color:rgb(var(--muted)); white-space:nowrap;">
                                    <?= date('M d, Y • h:i A', strtotime($po['created_at'])) ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Mobile Payout Cards (visible ≤ 768px) -->
                <div class="ed-payout-cards-mobile">
                    <?php foreach ($event_payouts as $po):
                        $isEscrow = ($po['type'] === 'escrow');
                        $isAuto = ($po['type'] === 'auto_completed');
                        $isCompleted = in_array($po['status'], ['processed', 'completed']);
                        $isPending = ($po['status'] === 'pending');
                    ?>
                    <div class="ed-payout-card">
                        <div class="ed-payout-card-head">
                            <span class="ed-payout-card-id">#P-<?= e($po['id']) ?></span>
                            <?php if ($isEscrow): ?>
                                <span class="ed-payout-type-badge ed-payout-type--escrow">🛡️ Escrow</span>
                            <?php elseif ($isAuto): ?>
                                <span class="ed-payout-type-badge ed-payout-type--auto">✅ Auto</span>
                            <?php else: ?>
                                <span class="ed-payout-type-badge">💳 <?= ucfirst(str_replace('_', ' ', $po['type'])) ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="ed-payout-card-amount"><?= format_price($po['amount']) ?></div>
                        <div class="ed-payout-card-details">
                            <div class="ed-payout-card-row">
                                <span class="ed-payout-card-label">Destination</span>
                                <span><?= e($po['payout_bank'] ?: ($hostBankName ?: 'Host Bank')) ?>
                                    <?php if (!empty($po['payout_account'] ?: $hostBankAccount)): ?>
                                        (••••<?= substr($po['payout_account'] ?: $hostBankAccount, -4) ?>)
                                    <?php endif; ?>
                                </span>
                            </div>
                            <div class="ed-payout-card-row">
                                <span class="ed-payout-card-label">Status</span>
                                <span class="au-status-badge <?= $isCompleted ? 'au-status--active' : ($isPending ? 'au-status--unverified' : 'au-status--suspended') ?>" style="font-size:0.72rem;"><?= ucfirst($po['status']) ?></span>
                            </div>
                            <div class="ed-payout-card-row">
                                <span class="ed-payout-card-label">Date</span>
                                <span><?= date('M d, Y • h:i A', strtotime($po['created_at'])) ?></span>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
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
            <table class="au-table" id="eventTiersTable" style="margin:0; width:100%;">
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

            <!-- Mobile Tier Cards (visible ≤ 768px) -->
            <div class="ed-tier-cards-mobile">
                <?php foreach ($ticketTypes as $tt):
                    $tierRevenue = $tt['revenue'] ?? ((int)$tt['sold'] * (float)$tt['price']);
                    $tierPct = $tt['percentage'] ?? ($tt['capacity'] > 0 ? round(($tt['sold'] / $tt['capacity']) * 100) : 0);
                ?>
                <div class="ed-tier-card">
                    <div class="ed-tier-card-head">
                        <div>
                            <div class="ed-tier-card-name"><?= e($tt['name']) ?></div>
                            <?php if (!empty($tt['description'])): ?>
                                <div class="ed-tier-card-desc"><?= e($tt['description']) ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="ed-tier-card-price"><?= format_price($tt['price']) ?></div>
                    </div>
                    <div class="ed-tier-card-progress">
                        <div class="ed-tier-card-progress-labels">
                            <span><?= (int)$tt['sold'] ?> / <?= (int)$tt['capacity'] ?> sold</span>
                            <span style="font-weight:700; color:rgb(var(--brand));"><?= $tierPct ?>%</span>
                        </div>
                        <div class="progress-bar-bg" style="height:6px; background:rgba(var(--muted), 0.15); border-radius:3px; overflow:hidden;">
                            <div class="progress-bar-fill" style="height:100%; width:<?= $tierPct ?>%; background:rgb(var(--brand));"></div>
                        </div>
                    </div>
                    <div class="ed-tier-card-revenue">
                        Revenue: <strong style="color:#10b981;"><?= format_price($tierRevenue) ?></strong>
                    </div>
                </div>
                <?php endforeach; ?>
                <div class="ed-tier-card" style="background:rgba(var(--muted),0.03); border-style:dashed;">
                    <div class="ed-tier-card-head">
                        <div class="ed-tier-card-name" style="text-transform:uppercase; font-size:0.78rem; letter-spacing:0.04em;">Total</div>
                        <div class="ed-tier-card-price" style="color:#10b981; font-size:1.1rem;"><?= format_price($gross_revenue) ?></div>
                    </div>
                    <div class="ed-tier-card-progress">
                        <div class="ed-tier-card-progress-labels">
                            <span><?= number_format($tickets_sold) ?> / <?= number_format($total_capacity) ?></span>
                            <span style="font-weight:700; color:rgb(var(--brand));"><?= $salesPct ?>% Capacity</span>
                        </div>
                    </div>
                </div>
            </div>

        <?php endif; ?>
    </div>
</section>

<section class="admin-panel" style="margin-bottom: 1.5rem; border: 1px solid rgba(var(--border), 0.7); background: rgb(var(--card)); border-radius: 18px; overflow: hidden;">
    <div class="section-header" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:0.65rem; padding: 1.25rem 1.5rem; border-bottom: 1px solid rgba(var(--border), 0.5);">
        <div>
            <h2 style="margin:0; font-size:1.15rem; display:flex; align-items:center; gap:0.5rem;">
                <span>🎟️</span> Attendee &amp; Ticket Buyers Stream
            </h2>
            <p class="section-note" style="margin:0.25rem 0 0;">Verified ticket buyers, order amounts, check-in status, and attendee details.</p>
        </div>
        <div style="display:flex; align-items:center; gap:0.6rem; flex-wrap:wrap;">
            <input type="text" id="attendeeFilterInput" placeholder="🔍 Search attendees, code, or tier..." style="padding:0.45rem 0.85rem; font-size:0.82rem; border-radius:10px; border:1px solid rgba(var(--muted),0.25); background:rgb(var(--surface)); color:rgb(var(--text)); min-width: 200px;">
            <?php if (!empty($attendees)): ?>
            <a href="{{ route('admin/attendees/export') }}?event_id=<?= $event['id'] ?>" class="admin-action-btn primary" style="background:#10b981; border-color:#10b981; text-decoration:none; font-size:0.82rem; font-weight:700; border-radius:10px; padding:0.45rem 0.85rem;">
                📥 Export CSV
            </a>
            <?php endif; ?>
        </div>
    </div>
    <div class="event-item" style="padding:0; overflow-x:auto; -webkit-overflow-scrolling: touch;">
        <?php if (empty($attendees)): ?>
            <div style="padding:2.5rem 1.5rem; text-align:center; color:rgb(var(--muted)); font-size:0.9rem;">
                No tickets purchased for this event yet.
            </div>
        <?php else: ?>
            <table class="au-table" id="eventAttendeesTable" style="margin:0; width:100%;">
                <thead>
                    <tr style="background: rgba(var(--muted), 0.04);">
                        <th>Ticket Code</th>
                        <th>Attendee Name &amp; Email</th>
                        <th>Account Type</th>
                        <th>Tier</th>
                        <th>Price Paid</th>
                        <th>Gate Check-in</th>
                        <th>Purchase Timestamp</th>
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
                            <div style="display:inline-flex; align-items:center; gap:0.35rem;">
                                <span style="font-family:monospace; font-weight:700; font-size:0.82rem; background:rgba(var(--text),0.05); padding:0.2rem 0.5rem; border-radius:6px; border:1px solid rgba(var(--muted),0.15);">
                                    <?= e($att['ticket_id']) ?>
                                </span>
                                <button type="button" onclick="navigator.clipboard.writeText('<?= addslashes(e($att['ticket_id'])) ?>'); this.innerText='✓'; setTimeout(() => this.innerText='📋', 1500);" style="background:none; border:none; cursor:pointer; font-size:0.8rem; color:rgb(var(--muted)); padding:2px;" title="Copy Code">📋</button>
                            </div>
                        </td>
                        <td>
                            <div style="display:flex; align-items:center; gap:0.6rem;">
                                <div style="width:32px; height:32px; border-radius:50%; background:hsl(<?= $hue ?>, 70%, 92%); color:hsl(<?= $hue ?>, 60%, 35%); display:inline-flex; align-items:center; justify-content:center; font-weight:800; font-size:0.75rem; flex-shrink:0;">
                                    <?= $initials ?>
                                </div>
                                <div>
                                    <div style="font-weight:700; color:rgb(var(--text)); font-size:0.9rem;"><?= e($att['attendee_name']) ?></div>
                                    <div style="font-size:0.76rem; color:rgb(var(--muted));"><?= e($att['attendee_email']) ?></div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <?php if ($isReg): ?>
                                <span style="background:rgba(16, 185, 129, 0.12); color:#059669; font-size:0.72rem; font-weight:700; padding:2px 7px; border-radius:6px;">👤 Registered</span>
                            <?php else: ?>
                                <span style="background:rgba(245, 158, 11, 0.14); color:#d97706; font-size:0.72rem; font-weight:700; padding:2px 7px; border-radius:6px;" title="Guest attendee email saved from ticket purchase">🌐 Guest</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span style="font-size:0.78rem; font-weight:700; background:rgba(99,102,241,0.1); color:#6366f1; padding:0.2rem 0.55rem; border-radius:6px;">
                                <?= e($att['ticket_type_name'] ?? 'General') ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($price > 0): ?>
                                <strong style="font-size:0.92rem; color:rgb(var(--text));"><?= format_price($price) ?></strong>
                            <?php else: ?>
                                <span style="font-size:0.75rem; font-weight:800; background:rgba(16,185,129,0.12); color:#10b981; padding:0.18rem 0.5rem; border-radius:6px;">FREE</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($isChecked): ?>
                                <span style="font-size:0.75rem; font-weight:700; color:#10b981; background:rgba(16,185,129,0.12); padding:0.2rem 0.55rem; border-radius:8px; display:inline-flex; align-items:center; gap:0.25rem;">
                                    <span>✓</span> Checked In
                                </span>
                            <?php else: ?>
                                <span style="font-size:0.75rem; color:rgb(var(--muted));">⏳ Gate Pending</span>
                            <?php endif; ?>
                        </td>
                        <td style="font-size:0.8rem; color:rgb(var(--muted)); white-space:nowrap;">
                            <?= date('M d, Y • h:i A', strtotime($att['purchase_date'])) ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <!-- Mobile Attendee Cards (visible ≤ 768px) -->
            <div class="ed-attendee-cards-mobile">
                <?php foreach ($attendees as $att):
                    $isReg = !empty($att['user_id']);
                    $hue = (crc32($att['attendee_email'] ?? $att['ticket_id']) % 260) + 100;
                    $initials = strtoupper(substr($att['attendee_name'] ?: 'A', 0, 2));
                    $price = (float)($att['price'] ?? 0);
                    $isChecked = !empty($att['checked_in']);
                ?>
                <div class="ed-attendee-card event-attendee-row">
                    <div class="ed-attendee-card-top">
                        <div style="display:flex; align-items:center; gap:0.6rem; min-width:0;">
                            <div style="width:36px; height:36px; border-radius:50%; background:hsl(<?= $hue ?>, 70%, 92%); color:hsl(<?= $hue ?>, 60%, 35%); display:inline-flex; align-items:center; justify-content:center; font-weight:800; font-size:0.75rem; flex-shrink:0;">
                                <?= $initials ?>
                            </div>
                            <div style="min-width:0;">
                                <div style="font-weight:700; color:rgb(var(--text)); font-size:0.88rem; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;"><?= e($att['attendee_name']) ?></div>
                                <div style="font-size:0.75rem; color:rgb(var(--muted)); white-space:nowrap; overflow:hidden; text-overflow:ellipsis;"><?= e($att['attendee_email']) ?></div>
                            </div>
                        </div>
                        <div>
                            <?php if ($price > 0): ?>
                                <strong style="font-size:0.92rem; color:rgb(var(--text));"><?= format_price($price) ?></strong>
                            <?php else: ?>
                                <span style="font-size:0.75rem; font-weight:800; background:rgba(16,185,129,0.12); color:#10b981; padding:0.18rem 0.5rem; border-radius:6px;">FREE</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="ed-attendee-card-meta">
                        <span class="ed-attendee-card-code" title="<?= e($att['ticket_id']) ?>">
                            <?= e($att['ticket_id']) ?>
                            <button type="button" onclick="navigator.clipboard.writeText('<?= addslashes(e($att['ticket_id'])) ?>'); this.innerText='✓'; setTimeout(() => this.innerText='📋', 1500);" style="background:none; border:none; cursor:pointer; font-size:0.75rem; color:rgb(var(--muted)); padding:1px;">📋</button>
                        </span>
                        <span style="font-size:0.72rem; font-weight:700; background:rgba(99,102,241,0.1); color:#6366f1; padding:0.15rem 0.45rem; border-radius:5px;">
                            <?= e($att['ticket_type_name'] ?? 'General') ?>
                        </span>
                        <?php if ($isReg): ?>
                            <span style="font-size:0.7rem; font-weight:700; background:rgba(16, 185, 129, 0.12); color:#059669; padding:2px 6px; border-radius:5px;">👤 Reg</span>
                        <?php else: ?>
                            <span style="font-size:0.7rem; font-weight:700; background:rgba(245, 158, 11, 0.14); color:#d97706; padding:2px 6px; border-radius:5px;">🌐 Guest</span>
                        <?php endif; ?>
                    </div>
                    <div class="ed-attendee-card-bottom">
                        <span>
                            <?php if ($isChecked): ?>
                                <span style="font-size:0.73rem; font-weight:700; color:#10b981;">✓ Checked In</span>
                            <?php else: ?>
                                <span style="font-size:0.73rem; color:rgb(var(--muted));">⏳ Gate Pending</span>
                            <?php endif; ?>
                        </span>
                        <span style="font-size:0.73rem; color:rgb(var(--muted));">
                            <?= date('M d, Y • h:i A', strtotime($att['purchase_date'])) ?>
                        </span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

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
        <div class="guest-row admin-detail-footer-actions" style="display:flex; gap:0.75rem; margin-top:1rem; align-items:center; flex-wrap:wrap;">
            <a href="{{ route('admin/events') }}" class="admin-action-btn" style="text-decoration:none;">← Back to Events List</a>
            <form method="POST" action="{{ route('admin/events/toggle-featured') }}" style="display:inline-flex; margin:0;">
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

<style>
/* ════════════════════════════════════════════════════════════════
   ADMIN EVENT DETAIL RESPONSIVE HERO & ACTION LINKS
   ════════════════════════════════════════════════════════════════ */
.event-detail-page-hero {
    display: flex !important;
    flex-direction: row !important;
    justify-content: space-between !important;
    align-items: center !important;
    gap: 1.5rem !important;
    flex-wrap: nowrap !important;
    padding: clamp(1rem, 2vw, 1.75rem) 0;
}

.event-detail-hero-title {
    flex: 1 1 auto;
    min-width: 240px;
}

.event-detail-actions-grid {
    flex: 0 1 auto;
    display: flex !important;
    flex-direction: row !important;
    align-items: center !important;
    justify-content: flex-end !important;
    gap: 0.45rem !important;
    flex-wrap: wrap !important;
}

.event-detail-btn {
    padding: 0.46rem 0.8rem;
    font-size: 0.82rem;
    border-radius: 11px;
    font-weight: 600;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.35rem;
    min-height: 38px;
    box-sizing: border-box;
    white-space: nowrap;
    transition: transform var(--duration-fast, 180ms) ease, background var(--duration-fast, 180ms) ease, border-color var(--duration-fast, 180ms) ease, box-shadow var(--duration-fast, 180ms) ease;
}

.event-detail-btn:hover {
    transform: translateY(-1px);
}

.event-detail-btn-default {
    background: rgba(var(--text), 0.04);
    border: 1px solid rgba(var(--muted), 0.2);
    color: rgb(var(--text));
}

.event-detail-btn-default:hover {
    background: rgba(var(--brand), 0.08);
    color: rgb(var(--brand));
    border-color: rgba(var(--brand), 0.3);
}

.event-detail-btn-primary {
    background: rgb(var(--brand)) !important;
    border: 1px solid rgb(var(--brand)) !important;
    color: #fff !important;
    font-weight: 700;
    box-shadow: 0 4px 14px rgba(var(--brand), 0.3);
}

.event-detail-btn-primary:hover {
    filter: brightness(1.08);
    box-shadow: 0 6px 18px rgba(var(--brand), 0.4);
    color: #fff !important;
}

.event-detail-btn-success {
    background: rgba(16, 185, 129, 0.1);
    color: #059669 !important;
    border: 1px solid rgba(16, 185, 129, 0.28);
}

.event-detail-btn-success:hover {
    background: rgba(16, 185, 129, 0.18);
    color: #047857 !important;
    border-color: rgba(16, 185, 129, 0.45);
}

.event-detail-btn-public {
    background: rgba(var(--muted), 0.1);
    border: 1px solid rgba(var(--muted), 0.22);
    color: rgb(var(--text));
}

.event-detail-btn-public:hover {
    background: rgba(var(--text), 0.08);
}

/* ── Tablet View (769px - 1024px) ── */
@media (max-width: 1024px) and (min-width: 769px) {
    .event-detail-page-hero {
        display: flex !important;
        flex-direction: row !important;
        justify-content: space-between !important;
        align-items: center !important;
        gap: 1rem !important;
        flex-wrap: nowrap !important;
    }
    .event-detail-actions-grid {
        display: flex !important;
        flex-wrap: wrap !important;
        justify-content: flex-end !important;
        gap: 0.35rem !important;
        max-width: 520px;
    }
    .event-detail-btn {
        padding: 0.4rem 0.65rem;
        font-size: 0.78rem;
    }
}

/* ── Mobile View (≤ 768px) ── */
@media (max-width: 768px) {
    .event-detail-page-hero {
        flex-direction: column !important;
        align-items: stretch !important;
        gap: 1rem !important;
        padding: 1rem 0;
    }

    .event-detail-hero-title {
        width: 100%;
    }

    .event-detail-actions-grid {
        display: grid !important;
        grid-template-columns: repeat(3, 1fr) !important;
        gap: 0.45rem !important;
        width: 100% !important;
    }

    .event-detail-btn {
        width: 100%;
        padding: 0.5rem 0.4rem;
        font-size: 0.78rem;
        justify-content: center;
        text-align: center;
    }

    .event-detail-btn span {
        font-size: 0.85rem;
    }

    /* Bottom footer actions on mobile */
    .admin-detail-footer-actions {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
        width: 100%;
    }

    .admin-detail-footer-actions .admin-action-btn,
    .admin-detail-footer-actions form,
    .admin-detail-footer-actions form button {
        width: 100% !important;
        justify-content: center !important;
        text-align: center !important;
        box-sizing: border-box !important;
    }
}

/* ── Extra Small Mobile (≤ 480px) ── */
@media (max-width: 480px) {
    .event-detail-actions-grid {
        grid-template-columns: repeat(2, 1fr) !important;
        gap: 0.35rem;
    }

    .event-detail-btn {
        padding: 0.48rem 0.25rem;
        font-size: 0.74rem;
        gap: 0.25rem;
    }
}

/* ════════════════════════════════════════════════════════════════
   MOBILE CARD VIEWS — Attendees, Tier Cards, Payout Cards
   Hidden on desktop, shown ≤ 768px
   ════════════════════════════════════════════════════════════════ */

/* --- Hide mobile cards on desktop, hide desktop tables on mobile --- */
.ed-attendee-cards-mobile,
.ed-tier-cards-mobile,
.ed-payout-cards-mobile {
    display: none;
}

@media (max-width: 768px) {
    .ed-attendee-cards-mobile,
    .ed-tier-cards-mobile,
    .ed-payout-cards-mobile {
        display: flex;
        flex-direction: column;
        gap: 0.65rem;
        padding: 0.85rem;
    }

    #eventAttendeesTable,
    #eventTiersTable,
    #eventPayoutsTable,
    .ed-payout-table-wrap {
        display: none !important;
    }

    /* Also hide tfoot within tier table parent on mobile */
    #eventTiersTable + tfoot,
    #eventPayoutsTable ~ * {
        /* handled by parent table hiding */
    }
}

/* --- Attendee Cards --- */
.ed-attendee-card {
    background: rgb(var(--card));
    border: 1px solid rgba(var(--border), 0.6);
    border-radius: 12px;
    padding: 0.85rem;
    transition: box-shadow 180ms ease;
}

.ed-attendee-card:hover {
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
}

.ed-attendee-card-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.5rem;
    margin-bottom: 0.55rem;
}

.ed-attendee-card-meta {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    flex-wrap: wrap;
    margin-bottom: 0.45rem;
}

.ed-attendee-card-code {
    font-family: monospace;
    font-weight: 700;
    font-size: 0.75rem;
    background: rgba(var(--text), 0.05);
    padding: 0.15rem 0.4rem;
    border-radius: 5px;
    border: 1px solid rgba(var(--muted), 0.15);
    display: inline-flex;
    align-items: center;
    gap: 0.2rem;
}

.ed-attendee-card-bottom {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.4rem;
    padding-top: 0.4rem;
    border-top: 1px solid rgba(var(--border), 0.3);
}

/* --- Tier Cards --- */
.ed-tier-card {
    background: rgb(var(--card));
    border: 1px solid rgba(var(--border), 0.6);
    border-radius: 12px;
    padding: 0.85rem;
}

.ed-tier-card-head {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 0.5rem;
    margin-bottom: 0.5rem;
}

.ed-tier-card-name {
    font-weight: 700;
    color: rgb(var(--text));
    font-size: 0.9rem;
}

.ed-tier-card-desc {
    font-size: 0.75rem;
    color: rgb(var(--muted));
    margin-top: 0.15rem;
}

.ed-tier-card-price {
    font-weight: 800;
    font-size: 0.95rem;
    color: rgb(var(--text));
    white-space: nowrap;
}

.ed-tier-card-progress {
    margin-bottom: 0.4rem;
}

.ed-tier-card-progress-labels {
    display: flex;
    justify-content: space-between;
    font-size: 0.78rem;
    color: rgb(var(--muted));
    margin-bottom: 0.3rem;
}

.ed-tier-card-revenue {
    font-size: 0.82rem;
    color: rgb(var(--muted));
    font-weight: 600;
}

/* --- Payout Cards --- */
.ed-payout-card {
    background: rgb(var(--card));
    border: 1px solid rgba(var(--border), 0.6);
    border-radius: 12px;
    padding: 0.85rem;
}

.ed-payout-card-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.4rem;
    margin-bottom: 0.45rem;
}

.ed-payout-card-id {
    font-family: monospace;
    font-weight: 700;
    font-size: 0.82rem;
    color: rgb(var(--text));
}

.ed-payout-type-badge {
    font-size: 0.72rem;
    font-weight: 700;
    padding: 0.18rem 0.55rem;
    border-radius: 6px;
    background: rgba(var(--muted), 0.12);
    color: rgb(var(--text));
    display: inline-flex;
    align-items: center;
    gap: 0.2rem;
}

.ed-payout-type--escrow {
    background: rgba(99, 102, 241, 0.12);
    color: #6366f1;
}

.ed-payout-type--auto {
    background: rgba(16, 185, 129, 0.12);
    color: #10b981;
}

.ed-payout-card-amount {
    font-size: 1.15rem;
    font-weight: 800;
    color: rgb(var(--text));
    margin-bottom: 0.55rem;
}

.ed-payout-card-details {
    display: flex;
    flex-direction: column;
    gap: 0.35rem;
    border-top: 1px solid rgba(var(--border), 0.3);
    padding-top: 0.5rem;
}

.ed-payout-card-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 0.78rem;
}

.ed-payout-card-label {
    color: rgb(var(--muted));
    font-weight: 600;
}

/* ════════════════════════════════════════════════════════════════
   HIGH-CONTRAST OCCURRENCE DATE SWITCHER DROPDOWN
   ════════════════════════════════════════════════════════════════ */
.admin-detail-dates-select {
    width: 100%;
    padding: 0.5rem 2rem 0.5rem 0.85rem;
    font-size: 0.84rem;
    font-weight: 700;
    font-family: inherit;
    border-radius: 8px;
    border: 1.5px solid rgba(99, 102, 241, 0.5);
    background-color: #ffffff;
    color: #0f172a;
    cursor: pointer;
    outline: none;
    appearance: none;
    -webkit-appearance: none;
    -moz-appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' viewBox='0 0 24 24' fill='none' stroke='%236366f1' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 0.6rem center;
    background-size: 14px;
    box-shadow: 0 1px 4px rgba(0, 0, 0, 0.06);
    transition: all 0.2s ease;
}

.admin-detail-dates-select:hover {
    border-color: #6366f1;
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.18);
}

.admin-detail-dates-select:focus {
    border-color: #4f46e5;
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.3);
}

.admin-detail-dates-select option {
    background-color: #ffffff !important;
    color: #0f172a !important;
    font-weight: 600;
    padding: 10px 14px;
}

:root[data-theme='dark'] .admin-detail-dates-select {
    background-color: #1e293b !important;
    color: #f8fafc !important;
    border-color: rgba(129, 140, 248, 0.55) !important;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' viewBox='0 0 24 24' fill='none' stroke='%23818cf8' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.4);
}

:root[data-theme='dark'] .admin-detail-dates-select option {
    background-color: #1e293b !important;
    color: #f8fafc !important;
}

/* ════════════════════════════════════════════════════════════════
   FINANCIAL SETTLEMENT RESPONSIVE
   ════════════════════════════════════════════════════════════════ */
@media (max-width: 768px) {
    /* Occurrence switcher banner */
    .occurrence-switcher-banner {
        flex-direction: column !important;
        align-items: stretch !important;
        gap: 0.75rem !important;
        padding: 0.85rem 1rem !important;
    }

    .occurrence-switcher-banner select {
        min-width: 0 !important;
        width: 100% !important;
    }

    /* Section headers */
    .section-header {
        flex-direction: column !important;
        align-items: stretch !important;
        gap: 0.65rem !important;
        padding: 1rem 1rem !important;
    }

    /* Financial settlement 5-metric grid */
    .admin-panel .event-item > div:first-child {
        grid-template-columns: 1fr 1fr !important;
        gap: 0.75rem !important;
        padding: 0.85rem !important;
    }

    /* Escrow readiness banner */
    .admin-panel .event-item > div:nth-child(2) {
        flex-direction: column !important;
        align-items: stretch !important;
        gap: 0.55rem !important;
        padding: 0.75rem 0.85rem !important;
    }

    /* Stat cards row */
    .admin-users-stats {
        grid-template-columns: 1fr 1fr !important;
        gap: 0.65rem !important;
    }
}

@media (max-width: 480px) {
    .admin-users-stats {
        grid-template-columns: 1fr !important;
    }

    /* Financial 5-metric grid stacks fully */
    .admin-panel .event-item > div:first-child {
        grid-template-columns: 1fr !important;
    }

    .ed-attendee-card-bottom {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.25rem;
    }
}
</style>

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

    // ─── Attendee & Ticket Buyers Live Filter ───
    const attendeeSearchInput = document.getElementById('attendeeFilterInput');
    const attendeeRows = document.querySelectorAll('.event-attendee-row');
    if (attendeeSearchInput && attendeeRows.length) {
        attendeeSearchInput.addEventListener('input', function() {
            const query = this.value.toLowerCase().trim();
            attendeeRows.forEach(row => {
                row.style.display = row.textContent.toLowerCase().includes(query) ? '' : 'none';
            });
        });
    }
});
</script>
@endsection