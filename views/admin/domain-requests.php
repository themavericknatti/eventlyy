<?php

/**
 * Admin Custom Domain & API Requests
 *
 * This file contains the view for managing host custom domain and headless API requests.
 *
 * @package Evently
 * @subpackage Views/Admin
 * @version 1.1.0
 */

$siteName = site_setting('site_title', 'Eventlyy');
$defaultCname = 'cname.' . ($_SERVER['HTTP_HOST'] ?? 'eventlyy.com');
?>

@extends('layouts/admin')

@section('title')
Custom Domain & API Requests - <?= e($siteName) ?> Admin
@endsection

@section('content')
<section class="page-hero" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:1.25rem; margin-bottom:1.5rem;">
    <div>
        <span class="panel-badge">Admin — Integrations</span>
        <h1 style="margin:0.25rem 0; font-size:1.8rem; font-weight:800;">🌐 Custom Domains &amp; API Requests</h1>
        <p class="lede" style="margin:0; color:rgb(var(--muted)); font-size:0.92rem;">
            Review host domain connection requests, configure DNS targets, and approve headless ticketing access.
        </p>
    </div>
    <div style="display:flex; gap:0.75rem; align-items:center; flex-wrap:wrap;">
        <a href="{{ route('admin/overview') }}" class="admin-action-btn" style="padding:0.6rem 1.1rem; font-size:0.86rem; border-radius:12px; font-weight:600; text-decoration:none; display:inline-flex; align-items:center; gap:0.4rem; background:rgba(var(--text), 0.04); border:1px solid rgba(var(--muted),0.2);">
            <span>←</span> Back to Dashboard
        </a>
        <button type="button" onclick="document.getElementById('dns-guide-box').classList.toggle('hidden')" class="admin-action-btn" style="padding:0.6rem 1.1rem; font-size:0.86rem; border-radius:12px; font-weight:600; background:rgba(var(--text), 0.04); border:1px solid rgba(var(--muted),0.2); cursor:pointer;">
            <span>📖</span> DNS Setup Guide
        </button>
        <a href="{{ route('admin/events') }}" class="admin-action-btn primary" style="padding:0.6rem 1.15rem; font-size:0.86rem; border-radius:12px; font-weight:700; text-decoration:none; display:inline-flex; align-items:center; gap:0.4rem;">
            <span>🎪</span> View All Events
        </a>
    </div>
</section>

<?php $isCustomDomainLive = (site_setting('custom_domain_enabled', '0') === '1'); ?>
<?php if (!$isCustomDomainLive): ?>
<div style="background:rgba(245, 158, 11, 0.08); border:1px solid rgba(245, 158, 11, 0.3); border-radius:16px; padding:0.95rem 1.25rem; margin-bottom:1.5rem; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:0.75rem;">
    <div style="display:flex; align-items:center; gap:0.65rem;">
        <span style="font-size:1.35rem;">🔒</span>
        <div>
            <strong style="color:rgb(var(--text)); font-size:0.9rem; display:block;">Custom Domain &amp; API Feature is Currently Disabled for Hosts</strong>
            <span style="color:rgb(var(--muted)); font-size:0.82rem;">Hosts cannot view the integration tab or submit new domain requests until an admin enables it in Settings. Existing requests can still be reviewed below.</span>
        </div>
    </div>
    <a href="{{ route('admin/settings') }}" class="admin-action-btn primary" style="background:#d97706; border-color:#d97706; text-decoration:none; padding:0.45rem 1rem; font-size:0.82rem; font-weight:700; border-radius:10px;">
        Enable in Settings →
    </a>
</div>
<?php endif; ?>

<!-- DNS Guide Cheatsheet (Collapsible) -->
<div id="dns-guide-box" class="form-card hidden" style="margin-bottom:1.5rem; padding:1.25rem; background:linear-gradient(135deg, rgba(2,132,199,0.05) 0%, rgba(var(--card),1) 100%); border-left:4px solid #0284c7;">
    <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:0.75rem;">
        <h4 style="margin:0; font-size:1rem; font-weight:800; color:rgb(var(--text)); display:flex; align-items:center; gap:0.4rem;">
            <span>ℹ️</span> Domain Configuration Reference for Hosts &amp; Admins
        </h4>
        <button type="button" onclick="document.getElementById('dns-guide-box').classList.add('hidden')" style="background:none; border:none; font-size:1.1rem; color:rgb(var(--muted)); cursor:pointer;">✕</button>
    </div>
    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(280px, 1fr)); gap:1rem; font-size:0.82rem; line-height:1.5;">
        <div style="background:rgba(var(--text),0.02); padding:0.85rem; border-radius:10px; border:1px solid rgba(var(--muted),0.12);">
            <strong style="color:rgb(var(--text)); display:block; margin-bottom:0.35rem;">1. Subdomains (e.g. <code>tickets.mainland.com</code>)</strong>
            <p style="margin:0 0 0.5rem; color:rgb(var(--muted));">Host adds a <code>CNAME</code> record in their domain DNS management (Cloudflare, GoDaddy, Namecheap):</p>
            <code style="display:block; padding:0.4rem 0.6rem; background:rgba(var(--text),0.05); border-radius:6px; font-weight:700;">Type: CNAME &nbsp;|&nbsp; Name: tickets &nbsp;|&nbsp; Target: <?= e($defaultCname) ?></code>
        </div>
        <div style="background:rgba(var(--text),0.02); padding:0.85rem; border-radius:10px; border:1px solid rgba(var(--muted),0.12);">
            <strong style="color:rgb(var(--text)); display:block; margin-bottom:0.35rem;">2. Apex / Root Domains (e.g. <code>mainland.com</code> or <code>www.mainland.com</code>)</strong>
            <p style="margin:0 0 0.5rem; color:rgb(var(--muted));">Host points their root domain via <code>A</code> record or <code>www</code> via <code>CNAME</code>:</p>
            <code style="display:block; padding:0.4rem 0.6rem; background:rgba(var(--text),0.05); border-radius:6px; font-weight:700;">Type: A &nbsp;|&nbsp; Name: @ &nbsp;|&nbsp; Target: <?= e($_SERVER['SERVER_ADDR'] ?? 'YOUR_SERVER_IP') ?></code>
        </div>
    </div>
</div>

<!-- Stats Counter Grid -->
<section class="admin-users-stats" style="margin-bottom:1.5rem;">
    <div class="au-stat-card">
        <span class="au-stat-icon">
            <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"/>
                <line x1="2" y1="12" x2="22" y2="12"/>
                <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>
            </svg>
        </span>
        <div class="au-stat-body">
            <span class="au-stat-num"><?= number_format($counts['all'] ?? 0) ?></span>
            <span class="au-stat-lbl">Total Requests</span>
        </div>
    </div>

    <div class="au-stat-card">
        <span class="au-stat-icon" style="background:rgba(245,158,11,0.12); color:#d97706;">
            <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"/>
                <polyline points="12 6 12 12 16 14"/>
            </svg>
        </span>
        <div class="au-stat-body">
            <span class="au-stat-num" style="color:#d97706;"><?= number_format($counts['pending'] ?? 0) ?></span>
            <span class="au-stat-lbl">Pending Review</span>
        </div>
    </div>

    <div class="au-stat-card">
        <span class="au-stat-icon au-stat-icon--brand">
            <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                <polyline points="22 4 12 14.01 9 11.01"/>
            </svg>
        </span>
        <div class="au-stat-body">
            <span class="au-stat-num" style="color:rgb(var(--brand));"><?= number_format($counts['approved_active'] ?? 0) ?></span>
            <span class="au-stat-lbl">Active &amp; Live</span>
        </div>
    </div>

    <div class="au-stat-card">
        <span class="au-stat-icon" style="background:rgba(239,68,68,0.12); color:#ef4444;">
            <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"/>
                <line x1="15" y1="9" x2="9" y2="15"/>
                <line x1="9" y1="9" x2="15" y2="15"/>
            </svg>
        </span>
        <div class="au-stat-body">
            <span class="au-stat-num" style="color:#ef4444;"><?= number_format($counts['rejected'] ?? 0) ?></span>
            <span class="au-stat-lbl">Declined</span>
        </div>
    </div>
</section>

<!-- Filter Navigation Pills -->
<div class="domain-filters-bar">
    <div class="domain-pills-wrap">
        <a href="?status=all" class="domain-filter-pill {{ $statusFilter === 'all' ? 'active' : '' }}">
            <span>All Requests</span>
            <span class="pill-badge"><?= $counts['all'] ?></span>
        </a>
        <a href="?status=pending" class="domain-filter-pill {{ $statusFilter === 'pending' ? 'active' : '' }}">
            <span style="display:inline-block; width:8px; height:8px; border-radius:50%; background:#f59e0b;"></span>
            <span>Pending</span>
            <span class="pill-badge pill-badge--amber"><?= $counts['pending'] ?></span>
        </a>
        <a href="?status=approved_active" class="domain-filter-pill {{ $statusFilter === 'approved_active' ? 'active' : '' }}">
            <span style="display:inline-block; width:8px; height:8px; border-radius:50%; background:#10b981;"></span>
            <span>Active &amp; Live</span>
            <span class="pill-badge pill-badge--green"><?= $counts['approved_active'] ?></span>
        </a>
        <a href="?status=rejected" class="domain-filter-pill {{ $statusFilter === 'rejected' ? 'active' : '' }}">
            <span style="display:inline-block; width:8px; height:8px; border-radius:50%; background:#ef4444;"></span>
            <span>Declined</span>
            <span class="pill-badge pill-badge--red"><?= $counts['rejected'] ?></span>
        </a>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════════════════
     DESKTOP TABLE VIEW (Visible on >= 900px screens)
     ═══════════════════════════════════════════════════════════════════ -->
<div class="desktop-domain-view table-wrapper">
    <table>
        <thead>
            <tr>
                <th>Event &amp; Host</th>
                <th>Requested Domain</th>
                <th>Integration Mode</th>
                <th>Setup Fee &amp; Payment</th>
                <th>Status</th>
                <th style="text-align:right;">Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($requests as $req)
            <?php 
                $isSubdomain = substr_count($req['requested_domain'] ?? '', '.') >= 2;
                $paymentPaid = ($req['payment_status'] ?? '') === 'paid';
                $paymentWaived = ($req['payment_status'] ?? '') === 'waived';
            ?>
            <tr>
                <!-- Event & Host -->
                <td>
                    <div style="font-weight:700; font-size:0.92rem; color:rgb(var(--text)); margin-bottom:0.25rem;">
                        <a href="{{ route('admin/events/' . $req['event_id']) }}" style="color:inherit; text-decoration:none;" title="View event details">
                            <?= e($req['event_name'] ?: 'Event #' . $req['event_id']) ?> ↗
                        </a>
                    </div>
                    <div style="font-size:0.78rem; color:rgb(var(--muted)); display:flex; align-items:center; gap:0.4rem;">
                        <span>👤 <?= e($req['host_name'] ?: 'Unknown Host') ?></span>
                        <?php if (!empty($req['host_email'])): ?>
                            <span>• <?= e($req['host_email']) ?></span>
                        <?php endif; ?>
                    </div>
                </td>

                <!-- Domain -->
                <td>
                    <div style="display:inline-flex; align-items:center; gap:0.45rem;">
                        <code style="font-size:0.88rem; font-weight:800; color:rgb(var(--text)); background:rgba(var(--text),0.05); padding:0.25rem 0.55rem; border-radius:6px; font-family:monospace;">
                            <?= e($req['requested_domain'] ?: 'N/A') ?>
                        </code>
                        <a href="https://<?= e($req['requested_domain']) ?>" target="_blank" style="color:rgb(var(--muted)); font-size:0.75rem;" title="Visit external website">↗</a>
                    </div>
                    <div style="margin-top:0.3rem;">
                        <span class="type-pill"><?= $isSubdomain ? 'Subdomain' : 'Root / Apex' ?></span>
                        <?php if (!empty($req['dns_cname_target'])): ?>
                            <span style="font-size:0.75rem; color:rgb(var(--muted)); margin-left:0.35rem;" title="CNAME Target">
                                ↳ <code><?= e($req['dns_cname_target']) ?></code>
                            </span>
                        <?php endif; ?>
                    </div>
                </td>

                <!-- Integration Mode -->
                <td>
                    <span class="mode-badge mode-badge--<?= e($req['integration_type']) ?>">
                        <?= ucwords(str_replace('_', ' ', $req['integration_type'])) ?>
                    </span>
                </td>

                <!-- Fee & Payment -->
                <td>
                    <div style="font-weight:800; font-size:0.9rem; color:rgb(var(--text));">
                        <?= format_price((float)$req['fee_amount']) ?>
                    </div>
                    <div style="margin-top:0.25rem;">
                        <?php if ($paymentPaid): ?>
                            <span class="pay-pill pay-pill--paid">✓ Paid Online</span>
                        <?php elseif ($paymentWaived): ?>
                            <span class="pay-pill pay-pill--waived">Waived</span>
                        <?php else: ?>
                            <span class="pay-pill pay-pill--unpaid">⏳ Unpaid</span>
                            <div style="margin-top:0.35rem; display:flex; gap:0.25rem; flex-wrap:wrap;">
                                <?php if (!empty($req['payment_reference'])): ?>
                                    <form method="POST" action="{{ route('admin/domain-requests/' . $req['id'] . '/verify-payment') }}" style="display:inline;">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="admin-action-btn" title="Re-verify with Paystack" style="padding:0.2rem 0.45rem; font-size:0.7rem; border-radius:6px;">
                                            🔄 Verify Paystack
                                        </button>
                                    </form>
                                <?php endif; ?>
                                <form method="POST" action="{{ route('admin/domain-requests/' . $req['id'] . '/mark-paid') }}" style="display:inline;" onsubmit="return confirm('Mark this setup fee as paid?');">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="admin-action-btn" title="Mark Paid" style="padding:0.2rem 0.45rem; font-size:0.7rem; color:#059669; border-radius:6px;">
                                        ✓ Mark Paid
                                    </button>
                                </form>
                            </div>
                        <?php endif; ?>
                    </div>
                </td>

                <!-- Status -->
                <td>
                    <span class="status-badge status-badge--<?= e($req['status']) ?>">
                        <?= ucwords(str_replace('_', ' ', $req['status'])) ?>
                    </span>
                    <div style="font-size:0.74rem; color:rgb(var(--muted)); margin-top:0.35rem;">
                        <?= date('M d, Y', strtotime($req['created_at'])) ?>
                    </div>
                </td>

                <!-- Actions -->
                <td style="text-align:right;">
                    <?php if ($req['status'] === 'pending'): ?>
                        <div style="display:inline-flex; gap:0.5rem; justify-content:flex-end;">
                            <button type="button" 
                                    class="admin-action-btn primary btn-sm"
                                    onclick="openApproveModal(<?= htmlspecialchars(json_encode($req), ENT_QUOTES, 'UTF-8') ?>)">
                                ✓ Approve &amp; Activate
                            </button>
                            <button type="button" 
                                    class="admin-action-btn danger btn-sm"
                                    onclick="openRejectModal(<?= htmlspecialchars(json_encode($req), ENT_QUOTES, 'UTF-8') ?>)">
                                ✕ Reject
                            </button>
                        </div>
                    <?php else: ?>
                        <div style="font-size:0.78rem; color:rgb(var(--muted)); text-align:right;">
                            <?php if (!empty($req['admin_notes'])): ?>
                                <span title="<?= e($req['admin_notes']) ?>" style="cursor:help; border-bottom:1px dotted;">
                                    📝 Notes Added
                                </span>
                            <?php else: ?>
                                <span>Completed</span>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </td>
            </tr>
            @endforeach

            @if(empty($requests))
            <tr>
                <td colspan="6" style="text-align:center; padding:3rem 1rem; color:rgb(var(--muted));">
                    <div style="font-size:2rem; margin-bottom:0.5rem;">🌐</div>
                    <strong style="font-size:1rem; color:rgb(var(--text)); display:block;">No Domain Requests Found</strong>
                    <p style="margin:0; font-size:0.85rem;">No requests match the selected filter.</p>
                </td>
            </tr>
            @endif
        </tbody>
    </table>
</div>

<!-- ═══════════════════════════════════════════════════════════════════
     MOBILE & TABLET CARDS VIEW (Visible on < 900px screens)
     ═══════════════════════════════════════════════════════════════════ -->
<div class="mobile-domain-view">
    @foreach($requests as $req)
    <?php 
        $isSubdomain = substr_count($req['requested_domain'] ?? '', '.') >= 2;
        $paymentPaid = ($req['payment_status'] ?? '') === 'paid';
        $paymentWaived = ($req['payment_status'] ?? '') === 'waived';
    ?>
    <div class="m-domain-card">
        <!-- Top row: Domain & Status -->
        <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:0.5rem; margin-bottom:0.75rem;">
            <div>
                <code style="font-size:0.95rem; font-weight:800; color:rgb(var(--text)); background:rgba(var(--text),0.05); padding:0.25rem 0.5rem; border-radius:6px; font-family:monospace; display:inline-block;">
                    <?= e($req['requested_domain'] ?: 'N/A') ?>
                </code>
                <div style="margin-top:0.35rem; display:flex; gap:0.4rem; align-items:center;">
                    <span class="type-pill"><?= $isSubdomain ? 'Subdomain' : 'Root Domain' ?></span>
                    <span class="mode-badge mode-badge--<?= e($req['integration_type']) ?>">
                        <?= ucwords(str_replace('_', ' ', $req['integration_type'])) ?>
                    </span>
                </div>
            </div>
            <span class="status-badge status-badge--<?= e($req['status']) ?>">
                <?= ucwords(str_replace('_', ' ', $req['status'])) ?>
            </span>
        </div>

        <!-- Details list -->
        <div class="m-domain-info-list">
            <div class="m-info-row">
                <span class="m-info-lbl">Event:</span>
                <a href="{{ route('admin/events/' . $req['event_id']) }}" style="font-weight:700; color:rgb(var(--text)); text-decoration:none;">
                    <?= e($req['event_name'] ?: 'Event #' . $req['event_id']) ?> ↗
                </a>
            </div>
            <div class="m-info-row">
                <span class="m-info-lbl">Host:</span>
                <span><?= e($req['host_name'] ?: 'Unknown') ?> <?= !empty($req['host_email']) ? '(' . e($req['host_email']) . ')' : '' ?></span>
            </div>
            <div class="m-info-row">
                <span class="m-info-lbl">Fee &amp; Payment:</span>
                <span style="display:flex; align-items:center; gap:0.4rem;">
                    <strong><?= format_price((float)$req['fee_amount']) ?></strong>
                    <?php if ($paymentPaid): ?>
                        <span class="pay-pill pay-pill--paid">✓ Paid</span>
                    <?php elseif ($paymentWaived): ?>
                        <span class="pay-pill pay-pill--waived">Waived</span>
                    <?php else: ?>
                        <span class="pay-pill pay-pill--unpaid">⏳ Unpaid</span>
                    <?php endif; ?>
                </span>
            </div>
            <?php if (!empty($req['dns_cname_target'])): ?>
                <div class="m-info-row">
                    <span class="m-info-lbl">DNS Target:</span>
                    <code><?= e($req['dns_cname_target']) ?></code>
                </div>
            <?php endif; ?>
            <?php if (!empty($req['host_notes'])): ?>
                <div class="m-info-row" style="flex-direction:column; align-items:flex-start; gap:0.25rem;">
                    <span class="m-info-lbl">Host Notes:</span>
                    <p style="margin:0; font-size:0.8rem; color:rgb(var(--muted)); font-style:italic;">"<?= e($req['host_notes']) ?>"</p>
                </div>
            <?php endif; ?>
            <?php if (!empty($req['admin_notes'])): ?>
                <div class="m-info-row" style="flex-direction:column; align-items:flex-start; gap:0.25rem;">
                    <span class="m-info-lbl">Admin Notes:</span>
                    <p style="margin:0; font-size:0.8rem; color:rgb(var(--text));">"<?= e($req['admin_notes']) ?>"</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Mobile Actions -->
        <?php if ($req['status'] === 'pending'): ?>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:0.5rem; margin-top:1rem; padding-top:0.75rem; border-top:1px solid rgba(var(--muted),0.12);">
                <button type="button" 
                        class="admin-action-btn primary" 
                        style="width:100%; justify-content:center; padding:0.65rem; font-weight:700;"
                        onclick="openApproveModal(<?= htmlspecialchars(json_encode($req), ENT_QUOTES, 'UTF-8') ?>)">
                    ✓ Approve
                </button>
                <button type="button" 
                        class="admin-action-btn danger" 
                        style="width:100%; justify-content:center; padding:0.65rem; font-weight:700;"
                        onclick="openRejectModal(<?= htmlspecialchars(json_encode($req), ENT_QUOTES, 'UTF-8') ?>)">
                    ✕ Reject
                </button>
            </div>
        <?php endif; ?>
    </div>
    @endforeach

    @if(empty($requests))
    <div class="m-domain-card" style="text-align:center; padding:2.5rem 1rem;">
        <div style="font-size:2rem; margin-bottom:0.5rem;">🌐</div>
        <strong style="font-size:1rem; color:rgb(var(--text)); display:block;">No Domain Requests Found</strong>
        <p style="margin:0; font-size:0.85rem; color:rgb(var(--muted));">No requests match the selected filter.</p>
    </div>
    @endif
</div>

<!-- ═══════════════════════════════════════════════════════════════════
     APPROVE & ACTIVATE MODAL
     ═══════════════════════════════════════════════════════════════════ -->
<div id="modal-approve" class="custom-modal-backdrop hidden" aria-hidden="true">
    <div class="custom-modal-dialog">
        <div class="custom-modal-header">
            <h3 style="margin:0; font-size:1.15rem; font-weight:800; display:flex; align-items:center; gap:0.5rem;">
                <span>🟢</span> Approve &amp; Activate Custom Domain
            </h3>
            <button type="button" onclick="closeApproveModal()" class="custom-modal-close">✕</button>
        </div>

        <form id="form-approve-domain" method="POST" action="">
            <?= csrf_field() ?>
            <div class="custom-modal-body">
                <div style="background:rgba(var(--brand),0.05); border:1px solid rgba(var(--brand),0.2); border-radius:10px; padding:0.85rem 1rem; margin-bottom:1.25rem;">
                    <div style="font-size:0.82rem; color:rgb(var(--muted));">Event / Host:</div>
                    <strong id="modal-approve-event" style="font-size:0.92rem; color:rgb(var(--text)); display:block;"></strong>
                    <div style="margin-top:0.4rem; font-size:0.82rem; color:rgb(var(--muted));">Domain to Activate:</div>
                    <code id="modal-approve-domain" style="font-weight:800; font-size:1rem; color:rgb(var(--brand)); font-family:monospace;"></code>
                </div>

                <div style="margin-bottom:1rem;">
                    <label for="approve-dns" style="display:block; font-size:0.84rem; font-weight:700; margin-bottom:0.35rem; color:rgb(var(--text));">
                        DNS CNAME Routing Target
                    </label>
                    <input type="text" id="approve-dns" name="dns_cname_target" value="<?= e($defaultCname) ?>" 
                           style="width:100%; padding:0.65rem 0.85rem; border:1.5px solid rgba(var(--muted),0.25); border-radius:8px; font-size:0.88rem; background:rgb(var(--card)); color:rgb(var(--text)); outline:none;" />
                    <span style="font-size:0.75rem; color:rgb(var(--muted)); display:block; margin-top:0.3rem;">
                        The host will point their domain CNAME record to this target.
                    </span>
                </div>

                <div style="margin-bottom:1rem;">
                    <label for="approve-notes" style="display:block; font-size:0.84rem; font-weight:700; margin-bottom:0.35rem; color:rgb(var(--text));">
                        Admin Notes / Setup Instructions (Optional)
                    </label>
                    <textarea id="approve-notes" name="admin_notes" rows="2" placeholder="e.g. SSL certificate provisioned. Ready for launch." 
                              style="width:100%; padding:0.65rem 0.85rem; border:1.5px solid rgba(var(--muted),0.25); border-radius:8px; font-size:0.84rem; background:rgb(var(--card)); color:rgb(var(--text)); outline:none; resize:vertical; font-family:inherit;"></textarea>
                </div>

                <div style="padding:0.75rem; background:rgba(var(--text),0.02); border:1px solid rgba(var(--muted),0.12); border-radius:8px;">
                    <label style="display:flex; align-items:center; gap:0.5rem; cursor:pointer; font-size:0.84rem; font-weight:700; color:rgb(var(--text)); margin:0;">
                        <input type="checkbox" name="waive_fee" value="1" style="width:16px; height:16px; accent-color:rgb(var(--brand));" />
                        <span>Mark Setup Fee as Waived / Confirmed</span>
                    </label>
                </div>
            </div>

            <div class="custom-modal-footer">
                <button type="button" onclick="closeApproveModal()" class="admin-action-btn" style="padding:0.6rem 1.25rem;">Cancel</button>
                <button type="submit" class="admin-action-btn primary" style="padding:0.6rem 1.5rem; font-weight:800;">✓ Confirm &amp; Activate Integration</button>
            </div>
        </form>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════════════════
     REJECT MODAL
     ═══════════════════════════════════════════════════════════════════ -->
<div id="modal-reject" class="custom-modal-backdrop hidden" aria-hidden="true">
    <div class="custom-modal-dialog">
        <div class="custom-modal-header">
            <h3 style="margin:0; font-size:1.15rem; font-weight:800; display:flex; align-items:center; gap:0.5rem; color:#ef4444;">
                <span>✕</span> Reject Domain Request
            </h3>
            <button type="button" onclick="closeRejectModal()" class="custom-modal-close">✕</button>
        </div>

        <form id="form-reject-domain" method="POST" action="">
            <?= csrf_field() ?>
            <div class="custom-modal-body">
                <p style="margin:0 0 1rem; font-size:0.88rem; color:rgb(var(--muted)); line-height:1.45;">
                    Are you sure you want to decline this domain integration request? The reason provided will be shown directly to the event organizer.
                </p>

                <div style="margin-bottom:1rem;">
                    <label for="reject-notes" style="display:block; font-size:0.84rem; font-weight:700; margin-bottom:0.35rem; color:rgb(var(--text));">
                        Reason for Rejection *
                    </label>
                    <textarea id="reject-notes" name="admin_notes" required rows="3" placeholder="e.g. Domain name violates acceptable use policy / invalid DNS record." 
                              style="width:100%; padding:0.65rem 0.85rem; border:1.5px solid rgba(239,68,68,0.3); border-radius:8px; font-size:0.84rem; background:rgb(var(--card)); color:rgb(var(--text)); outline:none; resize:vertical; font-family:inherit;"></textarea>
                </div>
            </div>

            <div class="custom-modal-footer">
                <button type="button" onclick="closeRejectModal()" class="admin-action-btn" style="padding:0.6rem 1.25rem;">Cancel</button>
                <button type="submit" class="admin-action-btn danger" style="padding:0.6rem 1.5rem; font-weight:800;">✕ Confirm Rejection</button>
            </div>
        </form>
    </div>
</div>

<style>
/* Filter Pills */
.domain-filters-bar {
    margin-bottom: 1.25rem;
}
.domain-pills-wrap {
    display: flex;
    gap: 0.6rem;
    overflow-x: auto;
    padding-bottom: 0.25rem;
    -webkit-overflow-scrolling: touch;
}
.domain-filter-pill {
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    padding: 0.5rem 0.95rem;
    background: rgb(var(--card));
    border: 1px solid rgba(var(--muted), 0.15);
    border-radius: 999px;
    color: rgb(var(--text));
    text-decoration: none;
    font-size: 0.84rem;
    font-weight: 600;
    white-space: nowrap;
    transition: all 0.2s;
}
.domain-filter-pill:hover {
    border-color: rgb(var(--brand));
    color: rgb(var(--brand));
}
.domain-filter-pill.active {
    background: rgb(var(--brand));
    border-color: rgb(var(--brand));
    color: #fff;
    box-shadow: 0 4px 12px rgba(var(--brand), 0.3);
}
.domain-filter-pill .pill-badge {
    background: rgba(var(--text), 0.08);
    font-size: 0.72rem;
    font-weight: 700;
    padding: 0.1rem 0.45rem;
    border-radius: 999px;
}
.domain-filter-pill.active .pill-badge {
    background: rgba(255, 255, 255, 0.25);
    color: #fff;
}
.pill-badge--amber { background: rgba(245, 158, 11, 0.15) !important; color: #d97706; }
.pill-badge--green { background: rgba(16, 185, 129, 0.15) !important; color: #059669; }
.pill-badge--red { background: rgba(239, 68, 68, 0.15) !important; color: #ef4444; }

/* Status Badges */
.status-badge {
    display: inline-block;
    padding: 0.25rem 0.6rem;
    border-radius: 999px;
    font-size: 0.72rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.4px;
}
.status-badge--pending { background: rgba(245, 158, 11, 0.14); color: #d97706; }
.status-badge--approved_active { background: rgba(16, 185, 129, 0.14); color: #059669; }
.status-badge--rejected { background: rgba(239, 68, 68, 0.14); color: #ef4444; }

/* Pill details */
.type-pill {
    font-size: 0.7rem;
    background: rgba(var(--muted), 0.12);
    color: rgb(var(--muted));
    font-weight: 700;
    padding: 0.12rem 0.4rem;
    border-radius: 4px;
}
.mode-badge {
    font-size: 0.72rem;
    font-weight: 700;
    padding: 0.2rem 0.5rem;
    border-radius: 6px;
    background: rgba(var(--text), 0.04);
    border: 1px solid rgba(var(--muted), 0.14);
    color: rgb(var(--text));
}
.mode-badge--both { background: rgba(139, 92, 246, 0.08); color: #7c3aed; border-color: rgba(139, 92, 246, 0.25); }
.mode-badge--custom_domain { background: rgba(2, 132, 199, 0.08); color: #0284c7; border-color: rgba(2, 132, 199, 0.25); }
.mode-badge--api_access { background: rgba(16, 185, 129, 0.08); color: #059669; border-color: rgba(16, 185, 129, 0.25); }

.pay-pill {
    font-size: 0.72rem;
    font-weight: 800;
    padding: 0.15rem 0.45rem;
    border-radius: 4px;
}
.pay-pill--paid { background: rgba(16, 185, 129, 0.12); color: #059669; }
.pay-pill--waived { background: rgba(139, 92, 246, 0.12); color: #7c3aed; }
.pay-pill--unpaid { background: rgba(245, 158, 11, 0.12); color: #d97706; }

.btn-sm {
    padding: 0.4rem 0.75rem !important;
    font-size: 0.76rem !important;
    border-radius: 8px !important;
}

/* Responsive Table vs Cards View */
.desktop-domain-view { display: block; }
.mobile-domain-view { display: none; }

@media (max-width: 899px) {
    .desktop-domain-view { display: none; }
    .mobile-domain-view { display: flex; flex-direction: column; gap: 1rem; }
}

/* Mobile Card Styling */
.m-domain-card {
    background: rgb(var(--card));
    border: 1px solid rgba(var(--muted), 0.14);
    border-radius: 14px;
    padding: 1.25rem;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
}
.m-domain-info-list {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    font-size: 0.84rem;
}
.m-info-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 0.5rem;
    padding: 0.25rem 0;
    border-bottom: 1px dashed rgba(var(--muted), 0.1);
}
.m-info-row:last-child {
    border-bottom: none;
}
.m-info-lbl {
    color: rgb(var(--muted));
    font-size: 0.8rem;
}

/* Custom Modal Styles */
.custom-modal-backdrop {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.55);
    backdrop-filter: blur(4px);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
    padding: 1rem;
}
.custom-modal-backdrop.hidden {
    display: none !important;
}
.custom-modal-dialog {
    background: rgb(var(--card));
    width: 100%;
    max-width: 500px;
    border-radius: 16px;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.25);
    overflow: hidden;
    animation: modalSlide 0.2s ease-out;
}
@keyframes modalSlide {
    from { opacity: 0; transform: translateY(12px) scale(0.98); }
    to { opacity: 1; transform: translateY(0) scale(1); }
}
.custom-modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.25rem 1.5rem;
    border-bottom: 1px solid rgba(var(--muted), 0.12);
}
.custom-modal-close {
    background: none;
    border: none;
    font-size: 1.25rem;
    color: rgb(var(--muted));
    cursor: pointer;
    line-height: 1;
}
.custom-modal-body {
    padding: 1.5rem;
}
.custom-modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 0.75rem;
    padding: 1rem 1.5rem;
    border-top: 1px solid rgba(var(--muted), 0.12);
    background: rgba(var(--text), 0.02);
}
</style>

<script>
function openApproveModal(req) {
    var form = document.getElementById('form-approve-domain');
    form.action = "{{ route('admin/domain-requests/') }}" + req.id + '/approve';
    
    document.getElementById('modal-approve-event').textContent = (req.event_name || 'Event #' + req.event_id) + ' (' + (req.host_name || 'Host') + ')';
    document.getElementById('modal-approve-domain').textContent = req.requested_domain;
    
    var modal = document.getElementById('modal-approve');
    modal.classList.remove('hidden');
    modal.setAttribute('aria-hidden', 'false');
}

function closeApproveModal() {
    var modal = document.getElementById('modal-approve');
    modal.classList.add('hidden');
    modal.setAttribute('aria-hidden', 'true');
}

function openRejectModal(req) {
    var form = document.getElementById('form-reject-domain');
    form.action = "{{ route('admin/domain-requests/') }}" + req.id + '/reject';
    
    var modal = document.getElementById('modal-reject');
    modal.classList.remove('hidden');
    modal.setAttribute('aria-hidden', 'false');
}

function closeRejectModal() {
    var modal = document.getElementById('modal-reject');
    modal.classList.add('hidden');
    modal.setAttribute('aria-hidden', 'true');
}

// Close modals when clicking backdrop
window.addEventListener('click', function(e) {
    var mApprove = document.getElementById('modal-approve');
    var mReject = document.getElementById('modal-reject');
    if (e.target === mApprove) closeApproveModal();
    if (e.target === mReject) closeRejectModal();
});
</script>
@endsection
