<?php

/**
 * Admin & Super Admin Executive Command Hub
 *
 * Streamlined, high-performance executive dashboard with segmented tab navigation,
 * real-time financial audit, operational queues, KPI analytics, and roster management.
 *
 * @package Evently
 * @subpackage Views/Admin
 * @author Celio Natti
 * @version 3.0.0
 */

$siteName = site_setting('site_title', 'Eventlyy');
$isSuperAdmin = !empty($is_super_admin);
$adminUser = $admin_user ?? app()->user;
$userName = $adminUser ? ($adminUser->name ?? user_first_name()) : 'Admin';
$currency = setting('currency', 'NGN');
$currencySymbol = setting('currency_symbols.' . $currency, '₦');
$pendingDomains = 0;
try {
    $pendingDomains = (int)\Ace\Application::$app->db->pdo->query("SELECT COUNT(*) FROM event_domain_requests WHERE status = 'pending'")->fetchColumn();
} catch (\Throwable $e) {}

// Calculate total urgent actions requiring attention
$totalUrgentCount = ($pending_payouts_count ?? 0) + ($pending_staff_requests_count ?? 0) + ($unresolved_support ?? 0) + $pendingDomains;

// Determine active tab from URL query or default to 'overview'
$activeTab = $_GET['tab'] ?? 'overview';
$validTabs = ['overview', 'analytics', 'operations', 'rosters', 'activity'];
if (!in_array($activeTab, $validTabs)) {
    $activeTab = 'overview';
}
?>

@extends('layouts/admin')

@section('title')
<?= $isSuperAdmin ? '👑 Super Admin Executive Hub' : '🛡️ Admin Dashboard' ?> — <?= e($siteName) ?>
@endsection

@section('content')
<!-- Chart.js CDN for Interactive Analytics -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
 /* Γ-Γ-Γ- Hero Section Styling Γ-Γ-Γ- */ .admin-dashboard-hero {     display: flex;     justify-content: space-between;     align-items: center;     flex-wrap: wrap;     gap: 1.5rem;     padding: 1.75rem 2rem;     border-radius: 24px;     background: rgb(var(--card));     border: 1px solid var(--border-card, rgba(var(--muted), 0.12));     box-shadow: var(--shadow-sm);     position: relative;     overflow: hidden;     transition: background 0.25s ease, border-color 0.25s ease; }  .admin-dashboard-hero--super {     background: radial-gradient(ellipse at 80% -20%, rgba(var(--brand), 0.12), transparent 70%),                 radial-gradient(ellipse at 20% 120%, rgba(16, 185, 129, 0.08), transparent 70%),                 linear-gradient(135deg, rgba(var(--brand), 0.05) 0%, rgba(99, 102, 241, 0.04) 50%, rgb(var(--card)) 100%);     border: 1.5px solid rgba(var(--brand), 0.25);     box-shadow: 0 8px 30px rgba(var(--brand), 0.08); }  :root[data-theme='dark'] .admin-dashboard-hero--super {     background: radial-gradient(ellipse at 80% -20%, rgba(var(--brand), 0.18), transparent 70%),                 radial-gradient(ellipse at 20% 120%, rgba(16, 185, 129, 0.12), transparent 70%),                 linear-gradient(135deg, rgba(var(--brand), 0.1) 0%, rgba(99, 102, 241, 0.06) 50%, rgb(var(--card)) 100%);     border-color: rgba(var(--brand), 0.35);     box-shadow: 0 10px 35px rgba(0, 0, 0, 0.4); }  .hero-content-wrap {     flex: 1 1 340px;     min-width: 0; }  .hero-badge-row {     display: flex;     align-items: center;     gap: 0.5rem;     flex-wrap: wrap;     margin-bottom: 0.6rem; }  .super-admin-crown-badge {     display: inline-flex;     align-items: center;     gap: 0.35rem;     background: linear-gradient(135deg, rgb(var(--brand)) 0%, #10b981 100%);     color: #fff;     font-size: 0.76rem;     font-weight: 800;     padding: 0.3rem 0.75rem;     border-radius: 20px;     letter-spacing: 0.02em;     box-shadow: 0 2px 10px rgba(var(--brand), 0.35); }  .crown-icon {     font-size: 0.95rem; }  .badge-dot-live {     width: 6px;     height: 6px;     border-radius: 50%;     background: #fff;     display: inline-block;     animation: pulseGlow 1.5s infinite; }  .admin-standard-badge {     display: inline-flex;     align-items: center;     gap: 0.35rem;     background: rgba(var(--brand), 0.12);     color: rgb(var(--brand));     font-size: 0.76rem;     font-weight: 800;     padding: 0.3rem 0.75rem;     border-radius: 20px; }  .hero-status-pill {     background: rgba(var(--muted), 0.08);     color: rgb(var(--text));     font-size: 0.72rem;     font-weight: 700;     padding: 0.25rem 0.6rem;     border-radius: 20px;     border: 1px solid rgba(var(--muted), 0.12); }  .hero-clock-pill {     font-size: 0.75rem;     color: rgb(var(--muted));     font-weight: 600;     margin-left: auto; }  .hero-title {     margin: 0.2rem 0 0.4rem;     font-size: clamp(1.4rem, 2.5vw, 1.85rem);     font-weight: 800;     letter-spacing: -0.025em;     color: rgb(var(--text)); }  .hero-subtitle {     margin: 0;     font-size: 0.92rem;     color: rgb(var(--muted));     line-height: 1.5;     max-width: 680px; }  .hero-actions-wrap {     display: grid;     grid-template-columns: repeat(auto-fit, minmax(130px, 1fr));     gap: 0.65rem;     width: 100%; }  .hero-action-btn {     padding: 0.65rem 1.15rem;     font-size: 0.88rem;     border-radius: 12px;     font-weight: 700;     text-decoration: none;     display: inline-flex;     align-items: center;     justify-content: center;     text-align: center;     gap: 0.45rem;     min-height: 42px;     box-sizing: border-box;     transition: transform var(--duration-fast) var(--ease-out), background var(--duration-fast) var(--ease-out), border-color var(--duration-fast) var(--ease-out); }  /* Γ-Γ-Γ- Super Admin Diagnostics Bar Γ-Γ-Γ- */ .super-admin-command-bar {     background: rgb(var(--card));     border: 1.5px solid rgba(var(--brand), 0.22);     border-radius: 20px;     padding: 1.35rem 1.6rem;     box-shadow: var(--shadow-sm);     transition: background 0.2s ease, border-color 0.2s ease; }  :root[data-theme='dark'] .super-admin-command-bar {     border-color: rgba(var(--brand), 0.3);     box-shadow: 0 4px 20px rgba(0, 0, 0, 0.35); }  .command-bar-header {     display: flex;     justify-content: space-between;     align-items: center;     margin-bottom: 1rem;     flex-wrap: wrap;     gap: 0.5rem; }  .command-bar-title {     margin: 0;     font-size: 1rem;     font-weight: 800;     color: rgb(var(--text)); }  .super-diagnostics-grid {     display: grid;     grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));     gap: 0.75rem;     margin-bottom: 1.25rem;     padding-bottom: 1rem;     border-bottom: 1px dashed rgba(var(--muted), 0.15); }  .diag-item {     background: rgba(var(--muted), 0.05);     border: 1px solid var(--border-subtle, rgba(var(--muted), 0.1));     border-radius: 10px;     padding: 0.65rem 0.85rem;     display: flex;     flex-direction: column;     gap: 0.2rem; }  :root[data-theme='dark'] .diag-item {     background: rgba(255, 255, 255, 0.03);     border-color: rgba(255, 255, 255, 0.07); }  .diag-label {     font-size: 0.72rem;     font-weight: 700;     color: rgb(var(--muted));     text-transform: uppercase;     letter-spacing: 0.03em; }  .diag-val {     font-size: 0.85rem;     font-weight: 800;     color: rgb(var(--text)); }  .super-command-actions {     display: grid;     grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));     gap: 0.65rem;     width: 100%; }  .super-cmd-link {     display: inline-flex;     align-items: center;     gap: 0.35rem;     background: rgba(var(--brand), 0.08);     color: rgb(var(--brand));     border: 1px solid rgba(var(--brand), 0.22);     border-radius: 10px;     padding: 0.45rem 0.9rem;     font-size: 0.82rem;     font-weight: 700;     text-decoration: none;     transition: all 0.15s ease; }  .super-cmd-link:hover {     background: rgba(var(--brand), 0.15);     transform: translateY(-1px);     border-color: rgba(var(--brand), 0.4); }  /* Γ-Γ-Γ- Operational Alerts Grid (Gentle, Soothing Tints) Γ-Γ-Γ- */ .section-title-wrap {     margin-bottom: 0.85rem; }  .section-subheading {     font-size: 0.95rem;     font-weight: 800;     color: rgb(var(--muted));     text-transform: uppercase;     letter-spacing: 0.05em;     margin: 0; }  .section-subtext {     font-size: 0.82rem;     color: rgb(var(--muted));     margin-top: 0.15rem;     display: block; }  .alerts-grid {     display: grid;     grid-template-columns: repeat(auto-fit, minmax(230px, 1fr));     gap: 1rem; }  .alert-action-card {     display: flex;     align-items: center;     gap: 1rem;     padding: 1.15rem 1.35rem;     border-radius: 18px;     text-decoration: none;     background: rgb(var(--card));     border: 1px solid var(--border-card, rgba(var(--muted), 0.12));     box-shadow: var(--shadow-sm);     transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease; }  .alert-action-card:hover {     transform: translateY(-2px);     box-shadow: var(--shadow-md); }  .alert-action-card--warning {     border-left: 4px solid #f59e0b;     background: linear-gradient(135deg, rgba(245, 158, 11, 0.06) 0%, rgb(var(--card)) 100%);     border-color: rgba(245, 158, 11, 0.22); }  .alert-action-card--indigo {     border-left: 4px solid #6366f1;     background: linear-gradient(135deg, rgba(99, 102, 241, 0.06) 0%, rgb(var(--card)) 100%);     border-color: rgba(99, 102, 241, 0.22); }  .alert-action-card--rose {     border-left: 4px solid #f43f5e;     background: linear-gradient(135deg, rgba(244, 63, 94, 0.06) 0%, rgb(var(--card)) 100%);     border-color: rgba(244, 63, 94, 0.22); }  .alert-action-card--teal {     border-left: 4px solid #14b8a6;     background: linear-gradient(135deg, rgba(20, 184, 166, 0.06) 0%, rgb(var(--card)) 100%);     border-color: rgba(20, 184, 166, 0.22); }  .alert-action-card--ok {     border-left: 4px solid #10b981;     background: linear-gradient(135deg, rgba(16, 185, 129, 0.05) 0%, rgb(var(--card)) 100%);     border-color: rgba(16, 185, 129, 0.2); }  :root[data-theme='dark'] .alert-action-card--warning {     background: linear-gradient(135deg, rgba(245, 158, 11, 0.1) 0%, rgb(var(--card)) 100%); } :root[data-theme='dark'] .alert-action-card--indigo {     background: linear-gradient(135deg, rgba(99, 102, 241, 0.1) 0%, rgb(var(--card)) 100%); } :root[data-theme='dark'] .alert-action-card--rose {     background: linear-gradient(135deg, rgba(244, 63, 94, 0.1) 0%, rgb(var(--card)) 100%); } :root[data-theme='dark'] .alert-action-card--teal {     background: linear-gradient(135deg, rgba(20, 184, 166, 0.1) 0%, rgb(var(--card)) 100%); } :root[data-theme='dark'] .alert-action-card--ok {     background: linear-gradient(135deg, rgba(16, 185, 129, 0.08) 0%, rgb(var(--card)) 100%); }  .alert-icon-box {     width: 44px;     height: 44px;     border-radius: 12px;     background: rgba(var(--muted), 0.08);     display: flex;     align-items: center;     justify-content: center;     font-size: 1.35rem;     flex-shrink: 0; }  .alert-details {     flex: 1;     min-width: 0; }  .alert-count {     font-size: 1.35rem;     font-weight: 800;     color: rgb(var(--text));     line-height: 1.1; }  .alert-title {     font-size: 0.86rem;     font-weight: 700;     color: rgb(var(--text));     margin-top: 0.15rem; }  .alert-meta {     font-size: 0.76rem;     color: rgb(var(--muted));     margin-top: 0.2rem;     line-height: 1.35; }  /* Γ-Γ-Γ- Metric & Stats Cards Γ-Γ-Γ- */ .dashboard-stats-grid {     grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));     gap: 1rem; }  .stat-growth-row {     display: flex;     align-items: baseline;     justify-content: space-between;     gap: 0.5rem;     flex-wrap: wrap; }  .stat-trend-tag {     font-size: 0.72rem;     font-weight: 800;     padding: 0.15rem 0.45rem;     border-radius: 6px;     line-height: 1; }  .trend-up {     background: rgba(16, 185, 129, 0.14);     color: #10b981; }  .trend-down {     background: rgba(239, 68, 68, 0.14);     color: #ef4444; }  .trend-profit {     background: rgba(var(--brand), 0.14);     color: rgb(var(--brand)); }  .stat-sub-info {     font-size: 0.75rem;     color: rgb(var(--muted));     display: block;     margin-top: 3px;     line-height: 1.35; }  /* Γ-Γ-Γ- Audit Log Filter Box Γ-Γ-Γ- */ .audit-search-box {     padding: 0.5rem 0.95rem;     border: 1px solid var(--border-subtle, rgba(var(--muted), 0.2));     border-radius: 12px;     font-size: 0.85rem;     background: rgba(var(--muted), 0.05);     color: rgb(var(--text));     min-width: 180px;     box-sizing: border-box;     outline: none;     transition: border-color var(--duration-fast) var(--ease-out), box-shadow var(--duration-fast) var(--ease-out); }  .audit-search-box:focus {     border-color: rgb(var(--brand));     box-shadow: 0 0 0 3px rgba(var(--brand), 0.2); }  /* Γ-Γ-Γ- Roster Sections (Events & Hosts) Γ-Γ-Γ- */ .dashboard-roster-section {     position: relative; }  .roster-events-grid, .roster-hosts-grid {     display: grid;     grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));     gap: 1.25rem; }  /* Γ-Γ-Γ- Event Card Γ-Γ-Γ- */ .roster-event-card {     background: rgb(var(--card));     border: 1px solid var(--border-card, rgba(var(--muted), 0.12));     border-radius: 18px;     overflow: hidden;     display: flex;     flex-direction: column;     box-shadow: var(--shadow-sm);     transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease; }  .roster-event-card:hover {     transform: translateY(-2px);     box-shadow: var(--shadow-md);     border-color: rgba(var(--brand), 0.25); }  .roster-event-header {     height: 100px;     position: relative;     padding: 0.75rem;     display: flex;     align-items: flex-start; }  .roster-event-header-overlay {     display: flex;     justify-content: space-between;     align-items: center;     width: 100%; }  .roster-event-status-pill {     font-size: 0.72rem;     font-weight: 700;     padding: 0.25rem 0.6rem;     border-radius: 20px;     backdrop-filter: blur(8px);     -webkit-backdrop-filter: blur(8px); }  .roster-pill--upcoming {     background: rgba(16, 185, 129, 0.9);     color: #fff; }  .roster-pill--past {     background: rgba(100, 116, 139, 0.9);     color: #fff; }  .roster-event-category-pill {     font-size: 0.72rem;     font-weight: 700;     padding: 0.25rem 0.6rem;     border-radius: 20px;     backdrop-filter: blur(8px);     -webkit-backdrop-filter: blur(8px); }  .roster-event-body {     padding: 1.15rem;     flex: 1;     display: flex;     flex-direction: column;     gap: 0.75rem; }  .roster-event-title {     margin: 0;     font-size: 1.02rem;     font-weight: 800;     color: rgb(var(--text));     line-height: 1.35; }  .roster-event-meta-grid {     display: flex;     flex-wrap: wrap;     gap: 0.4rem 1rem;     font-size: 0.78rem;     color: rgb(var(--muted)); }  .roster-meta-item {     display: flex;     align-items: center;     gap: 0.35rem; }  .roster-event-capacity {     background: rgba(var(--muted), 0.05);     border-radius: 12px;     padding: 0.75rem 0.85rem;     display: flex;     flex-direction: column;     gap: 0.35rem; }  .roster-capacity-header, .roster-capacity-footer {     display: flex;     justify-content: space-between;     align-items: center;     font-size: 0.76rem; }  .roster-capacity-label {     font-weight: 700;     color: rgb(var(--text)); }  .roster-capacity-numbers {     font-weight: 800;     color: rgb(var(--text)); }  .roster-progress-track {     height: 6px;     background: rgba(var(--muted), 0.15);     border-radius: 4px;     overflow: hidden; }  .roster-progress-fill {     height: 100%;     border-radius: 4px;     transition: width 0.4s ease; }  .roster-event-organizer {     display: flex;     align-items: center;     gap: 0.65rem;     padding-top: 0.5rem;     border-top: 1px dashed rgba(var(--muted), 0.15); }  .roster-organizer-avatar {     width: 28px;     height: 28px;     border-radius: 8px;     background: rgba(var(--brand), 0.12);     color: rgb(var(--brand));     font-weight: 800;     font-size: 0.75rem;     display: flex;     align-items: center;     justify-content: center;     flex-shrink: 0; }  .roster-organizer-info {     flex: 1;     min-width: 0;     display: flex;     flex-direction: column; }  .roster-organizer-name {     font-size: 0.82rem;     font-weight: 700;     color: rgb(var(--text));     white-space: nowrap;     overflow: hidden;     text-overflow: ellipsis; }  .roster-organizer-email {     font-size: 0.73rem;     color: rgb(var(--muted));     white-space: nowrap;     overflow: hidden;     text-overflow: ellipsis; }  .roster-event-footer {     padding: 0.75rem 1.15rem;     border-top: 1px solid rgba(var(--muted), 0.08);     background: rgba(var(--text), 0.015); }  .roster-detail-link {     font-size: 0.82rem;     font-weight: 700;     color: rgb(var(--brand));     text-decoration: none;     display: inline-flex;     align-items: center;     gap: 0.25rem;     transition: gap 0.15s ease; }  .roster-detail-link:hover {     gap: 0.5rem; }  /* Γ-Γ-Γ- Host Card Γ-Γ-Γ- */ .roster-host-card {     background: rgb(var(--card));     border: 1px solid var(--border-card, rgba(var(--muted), 0.12));     border-radius: 18px;     padding: 1.25rem;     display: flex;     flex-direction: column;     gap: 0.85rem;     box-shadow: var(--shadow-sm);     transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease; }  .roster-host-card:hover {     transform: translateY(-2px);     box-shadow: var(--shadow-md);     border-color: rgba(var(--brand), 0.25); }  .roster-host-identity {     display: flex;     align-items: center;     gap: 0.85rem; }  .roster-host-avatar {     width: 46px;     height: 46px;     border-radius: 14px;     display: flex;     align-items: center;     justify-content: center;     font-size: 1rem;     font-weight: 800;     flex-shrink: 0;     box-shadow: 0 2px 8px rgba(0,0,0,0.05); }  .roster-host-name-block {     flex: 1;     min-width: 0;     display: flex;     flex-direction: column;     gap: 0.15rem; }  .roster-host-name {     margin: 0;     font-size: 0.95rem;     font-weight: 800;     color: rgb(var(--text));     white-space: nowrap;     overflow: hidden;     text-overflow: ellipsis; }  .roster-host-email {     font-size: 0.75rem;     color: rgb(var(--muted));     white-space: nowrap;     overflow: hidden;     text-overflow: ellipsis; }  .roster-host-badges {     display: flex;     align-items: center;     gap: 0.4rem;     flex-wrap: wrap;     margin-top: 0.2rem; }  .roster-host-status-dot {     width: 6px;     height: 6px;     border-radius: 50%;     display: inline-block; }  .roster-dot--active {     background: #10b981;     box-shadow: 0 0 6px rgba(16, 185, 129, 0.5); }  .roster-dot--inactive {     background: #ef4444; }  .roster-host-status-text {     font-size: 0.72rem;     font-weight: 700;     color: rgb(var(--muted)); }  .roster-bank-badge {     font-size: 0.68rem;     font-weight: 800;     padding: 0.15rem 0.45rem;     border-radius: 6px; }  .roster-bank--verified {     background: rgba(16, 185, 129, 0.12);     color: #10b981; }  .roster-bank--pending {     background: rgba(245, 158, 11, 0.12);     color: #f59e0b; }  .roster-host-stats {     display: grid;     grid-template-columns: repeat(3, 1fr);     gap: 0.5rem;     background: rgba(var(--muted), 0.05);     border-radius: 12px;     padding: 0.75rem 0.5rem;     text-align: center; }  .roster-host-stat {     display: flex;     flex-direction: column;     gap: 0.15rem; }  .roster-stat-value {     font-size: 0.92rem;     font-weight: 800;     color: rgb(var(--text));     overflow: hidden;     text-overflow: ellipsis;     white-space: nowrap;     max-width: 100%; }  .roster-stat-label {     font-size: 0.68rem;     font-weight: 700;     color: rgb(var(--muted));     text-transform: uppercase;     letter-spacing: 0.03em; }  .roster-host-bank-info {     font-size: 0.75rem;     color: rgb(var(--muted));     display: flex;     justify-content: space-between;     align-items: center;     padding: 0.45rem 0.65rem;     background: rgba(var(--text), 0.02);     border-radius: 8px; }  .roster-bank-info--pending {     color: #f59e0b;     font-weight: 600;     font-size: 0.73rem; }  .roster-host-action {     padding-top: 0.4rem;     border-top: 1px solid rgba(var(--muted), 0.08); }  .roster-empty-state {     grid-column: 1 / -1;     padding: 3rem 1.5rem;     text-align: center;     color: rgb(var(--muted));     background: rgb(var(--card));     border-radius: 18px;     border: 1px dashed rgba(var(--muted), 0.2); }  /* Γ-Γ-Γ- Keyframe Animations Γ-Γ-Γ- */ @keyframes pulseGlow {     0% { transform: scale(0.9); opacity: 0.7; }     50% { transform: scale(1.3); opacity: 1; }     100% { transform: scale(0.9); opacity: 0.7; } }  /* Γ-Γ-Γ- Ticket Activity Feed & Mobile Cards (Responsive & Accessible) Γ-Γ-Γ- */ .ticket-activity-section, .audit-log-section {     position: relative;     overflow: hidden;     border-radius: 22px;     padding: 1.5rem;     margin-bottom: 2rem;     box-sizing: border-box;     max-width: 100%; }  .audit-header-row {     margin-bottom: 1.25rem;     border-bottom: 1px solid rgba(var(--muted), 0.08);     padding-bottom: 1rem;     display: flex;     justify-content: space-between;     align-items: center;     flex-wrap: wrap;     gap: 1rem; }  .ticket-stream-controls {     display: flex;     align-items: center;     gap: 0.75rem;     flex-wrap: wrap; }  .ticket-stream-table-wrap {     display: block;     width: 100%;     max-width: 100%;     overflow-x: auto;     -webkit-overflow-scrolling: touch;     border-radius: 12px;     box-sizing: border-box; }  .ticket-stream-table-wrap .au-table {     border-collapse: separate;     border-spacing: 0;     width: 100%;     font-size: 0.84rem; }  .ticket-stream-table-wrap .au-table th {     position: sticky;     top: 0;     z-index: 2;     background: rgba(var(--card), 0.98);     backdrop-filter: blur(6px);     -webkit-backdrop-filter: blur(6px);     padding: 0.65rem 0.75rem;     border-bottom: 1px solid rgba(var(--muted), 0.15);     white-space: nowrap; }  .ticket-stream-table-wrap .au-table td {     padding: 0.65rem 0.75rem;     vertical-align: middle; }  .ticket-stream-cards-list {     display: none; }  /* Γ-Γ- Mobile Ticket Card Layout Γ-Γ- */ .ticket-purchase-mobile-card {     background: rgba(var(--text), 0.02);     border: 1px solid rgba(var(--muted), 0.12);     border-radius: 14px;     padding: 0.85rem 1rem;     transition: all 0.2s ease;     display: flex;     flex-direction: column;     gap: 0.45rem;     box-sizing: border-box;     width: 100%;     max-width: 100%;     overflow: hidden;     min-width: 0;     word-break: break-word; }  .ticket-purchase-mobile-card:hover {     border-color: rgba(var(--brand), 0.25);     background: rgba(var(--brand), 0.02); }  .ticket-card-header-row {     display: flex;     justify-content: space-between;     align-items: flex-start;     gap: 0.65rem;     min-width: 0; }  .ticket-card-buyer-wrap {     display: flex;     align-items: center;     gap: 0.6rem;     min-width: 0;     flex: 1; }  .ticket-card-avatar {     width: 34px;     height: 34px;     border-radius: 50%;     display: inline-flex;     align-items: center;     justify-content: center;     font-weight: 800;     font-size: 0.78rem;     flex-shrink: 0; }  .ticket-card-buyer-text {     min-width: 0;     flex: 1; }  .ticket-purchase-mobile-card .ticket-buyer-name {     font-size: 0.88rem;     font-weight: 800;     color: rgb(var(--text));     line-height: 1.25;     overflow: hidden;     text-overflow: ellipsis;     white-space: nowrap;     max-width: 100%; }  .ticket-purchase-mobile-card .ticket-buyer-email {     font-size: 0.74rem;     color: rgb(var(--muted));     overflow: hidden;     text-overflow: ellipsis;     white-space: nowrap;     max-width: 100%;     margin-top: 1px; }  .ticket-card-price-wrap {     display: flex;     flex-direction: column;     align-items: flex-end;     gap: 0.2rem;     flex-shrink: 0; }  .ticket-purchase-mobile-card .au-status-badge {     white-space: nowrap !important;     word-break: keep-all !important;     display: inline-block !important;     flex-shrink: 0 !important;     line-height: 1.2 !important;     padding: 0.18rem 0.5rem !important;     border-radius: 6px !important;     text-transform: uppercase !important;     letter-spacing: 0.03em !important;     font-size: 0.7rem !important;     font-weight: 700 !important; }  .ticket-card-price {     font-size: 0.88rem;     font-weight: 800;     color: rgb(var(--text));     white-space: nowrap; }  .ticket-card-event-row {     min-width: 0;     max-width: 100%;     margin-top: 0.1rem; }  .ticket-card-event-link {     color: rgb(var(--brand));     text-decoration: none;     font-weight: 700;     font-size: 0.82rem;     overflow: hidden;     text-overflow: ellipsis;     white-space: nowrap;     display: block;     max-width: 100%; }  .ticket-card-event-empty {     font-size: 0.82rem;     font-weight: 600;     color: rgb(var(--muted));     overflow: hidden;     text-overflow: ellipsis;     white-space: nowrap;     display: block;     max-width: 100%; }  .ticket-card-footer-row {     display: flex;     justify-content: space-between;     align-items: center;     flex-wrap: wrap;     gap: 0.35rem;     font-size: 0.74rem;     border-top: 1px solid rgba(var(--muted), 0.08);     padding-top: 0.45rem;     margin-top: 0.1rem;     min-width: 0; }  .ticket-card-meta-left {     display: inline-flex;     align-items: center;     gap: 0.35rem;     min-width: 0;     flex-wrap: nowrap; }  .ticket-code-badge {     font-family: monospace;     font-weight: 700;     color: rgb(var(--text));     background: rgba(var(--text), 0.05);     padding: 0.15rem 0.45rem;     border-radius: 5px;     border: 1px solid rgba(var(--muted), 0.15);     font-size: 0.72rem;     max-width: 120px;     overflow: hidden;     text-overflow: ellipsis;     white-space: nowrap;     display: inline-block; }  .ticket-code-copy-btn {     background: none;     border: none;     cursor: pointer;     font-size: 0.75rem;     color: rgb(var(--muted));     padding: 0 2px;     flex-shrink: 0; }  .ticket-tier-badge {     font-weight: 700;     padding: 0.12rem 0.45rem;     border-radius: 5px;     background: rgba(99, 102, 241, 0.1);     color: #6366f1;     font-size: 0.7rem;     white-space: nowrap;     max-width: 90px;     overflow: hidden;     text-overflow: ellipsis;     display: inline-block; }  .ticket-card-meta-right {     display: flex;     align-items: center;     gap: 0.35rem;     color: rgb(var(--muted));     font-size: 0.72rem;     white-space: nowrap;     flex-shrink: 0; }  .ticket-gate-badge {     font-size: 0.7rem;     font-weight: 700;     padding: 0.1rem 0.4rem;     border-radius: 8px;     white-space: nowrap; }  .ticket-gate-badge.gate-in {     color: #10b981;     background: rgba(16, 185, 129, 0.1); }  .ticket-gate-badge.gate-pending {     color: rgb(var(--muted)); }  .ticket-meta-dot {     color: rgba(var(--muted), 0.5); }  .ticket-date-text {     font-size: 0.7rem;     color: rgb(var(--muted)); }  @media (max-width: 900px) {     .ticket-stream-table-wrap {         display: none !important;     }     .ticket-stream-cards-list {         display: flex !important;         flex-direction: column;         gap: 0.75rem;     } }  /* Γ-Γ-Γ- Responsive Media Queries (Desktop, Tablet & Mobile) Γ-Γ-Γ- */ @media (max-width: 1100px) {     .roster-events-grid,     .roster-hosts-grid {         grid-template-columns: repeat(2, 1fr);         gap: 1rem;     }     .super-diagnostics-grid {         grid-template-columns: repeat(3, 1fr);     } }  @media (max-width: 900px) {     .admin-dashboard-hero {         padding: 1.35rem 1.5rem;     }     .hero-title {         font-size: 1.4rem;     }     .hero-clock-pill {         margin-left: 0;         width: 100%;     }     .dashboard-stats-grid {         grid-template-columns: repeat(2, 1fr);     }     .alerts-grid {         grid-template-columns: repeat(2, 1fr);     }     .revenue-reports-grid {         grid-template-columns: repeat(2, 1fr);     }     .admin-charts-grid {         grid-template-columns: 1fr;     }     .chart-canvas-wrapper {         height: 260px !important;     } }  @media (max-width: 768px) {     .hero-actions-wrap {         grid-template-columns: repeat(2, 1fr);     }     .hero-action-btn {         font-size: 0.85rem;     }     .super-diagnostics-grid {         grid-template-columns: repeat(2, 1fr);     }     .super-command-actions {         grid-template-columns: repeat(2, 1fr);         gap: 0.5rem;     }     .super-cmd-link {         justify-content: center;         text-align: center;     }     .roster-events-grid,     .roster-hosts-grid {         grid-template-columns: 1fr;         gap: 1rem;     }     .chart-canvas-wrapper {         height: 240px !important;     }     .ticket-activity-section,     .audit-log-section {         padding: 1rem 0.85rem;         border-radius: 16px;     }     .audit-header-row {         flex-direction: column;         align-items: stretch !important;         gap: 0.75rem;     }     .ticket-stream-controls {         width: 100%;         flex-direction: column;         align-items: stretch !important;         gap: 0.5rem;     }     .ticket-stream-controls .audit-search-box {         width: 100% !important;         min-width: 0 !important;     }     .ticket-stream-controls .admin-action-btn {         width: 100% !important;         justify-content: center;         text-align: center;     } }  @media (max-width: 600px) {     .admin-dashboard-hero {         padding: 1.15rem;         border-radius: 18px;     }     .hero-title {         font-size: 1.3rem;     }     .hero-actions-wrap {         grid-template-columns: 1fr;     }     .hero-action-btn {         font-size: 0.85rem;         min-height: 44px;     }     .super-diagnostics-grid {         grid-template-columns: 1fr;     }     .super-command-actions {         grid-template-columns: 1fr;     }     .alerts-grid {         grid-template-columns: 1fr;     }     .dashboard-stats-grid {         grid-template-columns: 1fr;     }     .revenue-reports-grid {         grid-template-columns: 1fr;     }     .roster-host-card,     .roster-event-card {         border-radius: 16px;     }     .audit-header-row {         flex-direction: column;         align-items: stretch !important;     }     .audit-search-box {         width: 100%;         min-width: 0;     }     .au-table-wrap {         margin: 0;         width: 100%;         max-width: 100%;         overflow-x: auto !important;         -webkit-overflow-scrolling: touch;         box-sizing: border-box;     }     .chart-canvas-wrapper {         height: 220px !important;     }     .chart-card-header h3 {         font-size: 0.9rem !important;     }     .chart-card-header p {         font-size: 0.75rem !important;     } }  /* Γ-Γ- Extra Small Mobile (<= 420px) Γ-Γ- */ @media (max-width: 420px) {     .ticket-activity-section,     .audit-log-section {         padding: 0.75rem 0.65rem;         border-radius: 14px;     }     .ticket-purchase-mobile-card {         padding: 0.75rem 0.8rem;         border-radius: 12px;     }     .ticket-card-avatar {         width: 30px;         height: 30px;         font-size: 0.72rem;     }     .ticket-purchase-mobile-card .ticket-buyer-name {         font-size: 0.82rem;     }     .ticket-purchase-mobile-card .ticket-buyer-email {         font-size: 0.7rem;     }     .ticket-card-price {         font-size: 0.82rem;     }     .ticket-card-footer-row {         flex-direction: column;         align-items: flex-start;         gap: 0.3rem;     }     .ticket-card-meta-left {         width: 100%;         justify-content: flex-start;     }     .ticket-card-meta-right {         width: 100%;         justify-content: flex-start;     } } 

/* --- Vintage Theme Enhancements --- */
.admin-dashboard-hero {
    background: var(--vn-parchment-card) !important;
    border: 1.5px solid var(--vn-border) !important;
    box-shadow: var(--vn-shadow-elevated) !important;
}
.hero-title {
    font-family: var(--vn-font-serif) !important;
    color: var(--vn-ink) !important;
}
.super-admin-command-bar {
    background: var(--vn-parchment-card) !important;
    border: 1.5px solid var(--vn-border) !important;
    box-shadow: var(--vn-shadow-elevated) !important;
}
.alert-action-card {
    background: var(--vn-parchment-card) !important;
    border-color: var(--vn-border) !important;
}
.au-stat-card {
    background: var(--vn-parchment-card) !important;
    border-color: var(--vn-border) !important;
}
.au-stat-num {
    font-family: var(--vn-font-serif) !important;
    color: var(--vn-brass) !important;
}
.roster-event-card, .roster-host-card {
    background: var(--vn-parchment-card) !important;
    border-color: var(--vn-border) !important;
    box-shadow: var(--vn-shadow-elevated) !important;
}
.roster-event-title, .roster-host-name {
    font-family: var(--vn-font-serif) !important;
    color: var(--vn-ink) !important;
}
.ticket-activity-section, .audit-log-section {
    background: var(--vn-parchment-card) !important;
    border: 1.5px solid var(--vn-border) !important;
    box-shadow: var(--vn-shadow-elevated) !important;
}
.au-table-wrap .au-table th {
    background: var(--vn-parchment-warm) !important;
    color: var(--vn-ink-soft) !important;
    font-family: var(--vn-font-sans) !important;
    font-size: 0.78rem !important;
    text-transform: uppercase !important;
    letter-spacing: 0.05em !important;
    border-bottom: 2px solid var(--vn-border-dark) !important;
}
.au-table-wrap .au-table td {
    border-bottom: 1px solid var(--vn-border) !important;
    color: var(--vn-ink) !important;
}
</style>

<!-- ════════════════════════════════════════════════════════════════
     1. EXECUTIVE HERO BANNER
     ════════════════════════════════════════════════════════════════ -->
<section class="admin-dashboard-hero <?= $isSuperAdmin ? 'admin-dashboard-hero--super' : '' ?>" style="margin-bottom: 1.25rem;">
    <div class="hero-content-wrap">
        <div class="hero-badge-row">
            <?php if ($isSuperAdmin): ?>
                <span class="super-admin-crown-badge">
                    <span class="crown-icon">👑</span>
                    <span>Super Administrator</span>
                    <span class="badge-dot-live"></span>
                </span>
                <span class="hero-status-pill">Root Authority</span>
                <span class="hero-status-pill">Financial Custody</span>
            <?php else: ?>
                <span class="admin-standard-badge">
                    <span style="font-size: 0.95rem;">🛡️</span>
                    <span>Administrator Console</span>
                </span>
                <span class="hero-status-pill">Operations</span>
            <?php endif; ?>
            <span class="hero-clock-pill" id="dashboard-live-clock">🕒 <?= date('M d, Y • h:i A') ?></span>
        </div>

        <h1 class="hero-title">
            <?php if ($isSuperAdmin): ?>
                Welcome back, Master <?= e($userName) ?> ⚡
            <?php else: ?>
                Welcome back, <?= e($userName) ?> 👋
            <?php endif; ?>
        </h1>
        <p class="hero-subtitle">
            <?php if ($isSuperAdmin): ?>
                Executive Command Center: High-velocity financial oversight, liquid escrow audit, host infrastructure, and system diagnostics.
            <?php else: ?>
                Platform operations, event lifecycle management, ticket sales velocity, and customer support coordination.
            <?php endif; ?>
        </p>
    </div>

    <!-- Quick Action Hub in Hero -->
    <div class="hero-actions-wrap">
        <a href="{{ route('admin/events') }}" class="admin-action-btn primary hero-action-btn">
            <span>🎪</span>
            <span>All Events (<?= (int)($total_events ?? 0) ?>)</span>
        </a>
        <a href="{{ route('admin/payouts') }}" class="admin-action-btn hero-action-btn" style="background: rgba(var(--text), 0.04); border: 1.5px solid var(--vn-border);">
            <span>💳</span>
            <span>Payouts</span>
            <?php if (($pending_payouts_count ?? 0) > 0): ?>
                <span class="edb-tab-badge edb-tab-badge--alert"><?= (int)$pending_payouts_count ?></span>
            <?php endif; ?>
        </a>
        <a href="{{ route('admin/domain-requests') }}" class="admin-action-btn hero-action-btn" style="background: rgba(var(--text), 0.04); border: 1.5px solid var(--vn-border);">
            <span>🌐</span>
            <span>Domains</span>
            <?php if ($pendingDomains > 0): ?>
                <span class="edb-tab-badge edb-tab-badge--alert"><?= $pendingDomains ?></span>
            <?php endif; ?>
        </a>
        <a href="{{ route('admin/communications') }}" class="admin-action-btn hero-action-btn" style="background: rgba(var(--text), 0.04); border: 1.5px solid var(--vn-border);">
            <span>📨</span>
            <span>Broadcast</span>
        </a>
        <a href="{{ route('host/dashboard') }}" class="admin-action-btn hero-action-btn" style="background: rgba(var(--text), 0.04); border: 1.5px solid var(--vn-border);">
            <span>🎪</span>
            <span>Host View</span>
        </a>
    </div>
</section>

<!-- ════════════════════════════════════════════════════════════════
     2. EXECUTIVE SEGMENTED NAVIGATION TABS
     ════════════════════════════════════════════════════════════════ -->
<div class="edb-tabs-container">
    <nav class="edb-tabs-bar" role="tablist" aria-label="Admin Dashboard Views">
        <button type="button" class="edb-tab-btn <?= $activeTab === 'overview' ? 'active' : '' ?>" data-tab-btn="overview" role="tab" aria-selected="<?= $activeTab === 'overview' ? 'true' : 'false' ?>">
            <span>📊</span>
            <span>Overview &amp; KPIs</span>
        </button>

        <button type="button" class="edb-tab-btn <?= $activeTab === 'analytics' ? 'active' : '' ?>" data-tab-btn="analytics" role="tab" aria-selected="<?= $activeTab === 'analytics' ? 'true' : 'false' ?>">
            <span>📈</span>
            <span>Analytics &amp; Charts</span>
        </button>

        <button type="button" class="edb-tab-btn <?= $activeTab === 'operations' ? 'active' : '' ?>" data-tab-btn="operations" role="tab" aria-selected="<?= $activeTab === 'operations' ? 'true' : 'false' ?>">
            <span>⚡</span>
            <span>Urgent Queues</span>
            <?php if ($totalUrgentCount > 0): ?>
                <span class="edb-tab-badge edb-tab-badge--alert"><?= $totalUrgentCount ?></span>
            <?php endif; ?>
        </button>

        <button type="button" class="edb-tab-btn <?= $activeTab === 'rosters' ? 'active' : '' ?>" data-tab-btn="rosters" role="tab" aria-selected="<?= $activeTab === 'rosters' ? 'true' : 'false' ?>">
            <span>🎪</span>
            <span>Events &amp; Hosts</span>
            <span class="edb-tab-badge"><?= (int)($total_events ?? 0) ?></span>
        </button>

        <button type="button" class="edb-tab-btn <?= $activeTab === 'activity' ? 'active' : '' ?>" data-tab-btn="activity" role="tab" aria-selected="<?= $activeTab === 'activity' ? 'true' : 'false' ?>">
            <span>📜</span>
            <span>Activity &amp; Audit Logs</span>
        </button>
    </nav>
</div>

<!-- ════════════════════════════════════════════════════════════════
     TAB 1: EXECUTIVE OVERVIEW & CORE KPIS
     ════════════════════════════════════════════════════════════════ -->
<div id="tab-overview" class="edb-tab-pane <?= $activeTab === 'overview' ? '' : 'hidden' ?>" data-tab-content="overview" role="tabpanel">

    <!-- Operational Alerts Summary Bar -->
    <section class="operational-alerts-container" style="margin-bottom: 2rem;">
        <div class="edb-section-header">
            <div class="edb-section-title-wrap">
                <h3 class="edb-section-title">⚡ Real-Time Operational Queue</h3>
                <p class="edb-section-subtext">Immediate items requiring administrative review or settlement</p>
            </div>
            <button type="button" class="admin-action-btn" onclick="switchAdminTab('operations')" style="font-size:0.82rem; padding:0.4rem 0.85rem; border-radius:8px;">
                Manage All Queues (<?= $totalUrgentCount ?>) →
            </button>
        </div>

        <div class="alerts-grid">
            <!-- Alert 1: Pending Payouts -->
            <a href="{{ route('admin/payouts') }}" class="alert-action-card <?= ($pending_payouts_count ?? 0) > 0 ? 'alert-action-card--warning' : 'alert-action-card--ok' ?>">
                <div class="alert-icon-box"><span>💳</span></div>
                <div class="alert-details">
                    <div class="alert-count"><?= (int)($pending_payouts_count ?? 0) ?></div>
                    <div class="alert-title">Pending Payout Requests</div>
                    <div class="alert-meta">
                        <?php if (($pending_payouts_count ?? 0) > 0): ?>
                            <?= format_price($pending_requests_amount ?? 0) ?> awaiting approval →
                        <?php else: ?>
                            All payouts cleared ✓
                        <?php endif; ?>
                    </div>
                </div>
            </a>

            <!-- Alert 2: Host Staffing Requests -->
            <a href="{{ route('admin/teams') }}" class="alert-action-card <?= ($pending_staff_requests_count ?? 0) > 0 ? 'alert-action-card--indigo' : 'alert-action-card--ok' ?>">
                <div class="alert-icon-box"><span>🛡️</span></div>
                <div class="alert-details">
                    <div class="alert-count"><?= (int)($pending_staff_requests_count ?? 0) ?></div>
                    <div class="alert-title">Host Staffing Requests</div>
                    <div class="alert-meta">
                        <?php if (($pending_staff_requests_count ?? 0) > 0): ?>
                            Hosts awaiting door staff →
                        <?php else: ?>
                            All deployments active ✓
                        <?php endif; ?>
                    </div>
                </div>
            </a>

            <!-- Alert 3: Support Inquiries -->
            <a href="{{ route('admin/support') }}" class="alert-action-card <?= ($unresolved_support ?? 0) > 0 ? 'alert-action-card--rose' : 'alert-action-card--ok' ?>">
                <div class="alert-icon-box"><span>🎧</span></div>
                <div class="alert-details">
                    <div class="alert-count"><?= (int)($unresolved_support ?? 0) ?></div>
                    <div class="alert-title">Unresolved Support</div>
                    <div class="alert-meta">
                        <?php if (($unresolved_support ?? 0) > 0): ?>
                            Attendee &amp; host inquiries pending →
                        <?php else: ?>
                            Inbox clear ✓
                        <?php endif; ?>
                    </div>
                </div>
            </a>

            <!-- Alert 4: Live Events Active -->
            <a href="{{ route('admin/events') }}" class="alert-action-card alert-action-card--teal">
                <div class="alert-icon-box"><span>🎪</span></div>
                <div class="alert-details">
                    <div class="alert-count"><?= (int)($live_events ?? $total_events ?? 0) ?></div>
                    <div class="alert-title">Active Live Events</div>
                    <div class="alert-meta">
                        <?= (int)($checked_in_tickets ?? 0) ?> checked in (<?= $check_in_rate ?? 0 ?>%) →
                    </div>
                </div>
            </a>

            <!-- Alert 5: Custom Domain Requests -->
            <a href="{{ route('admin/domain-requests') }}" class="alert-action-card <?= $pendingDomains > 0 ? 'alert-action-card--warning' : 'alert-action-card--ok' ?>">
                <div class="alert-icon-box"><span>🌐</span></div>
                <div class="alert-details">
                    <div class="alert-count"><?= (int)$pendingDomains ?></div>
                    <div class="alert-title">Domain &amp; API Requests</div>
                    <div class="alert-meta">
                        <?php if ($pendingDomains > 0): ?>
                            <?= $pendingDomains ?> domain<?= $pendingDomains === 1 ? '' : 's' ?> pending setup →
                        <?php else: ?>
                            All domains provisioned ✓
                        <?php endif; ?>
                    </div>
                </div>
            </a>
        </div>
    </section>

    <!-- Period Revenue Matrix (Today, 7-Day, 30-Day, All-Time) -->
    <section style="margin-bottom: 2rem;">
        <div class="edb-section-header">
            <div class="edb-section-title-wrap">
                <h3 class="edb-section-title">⏱️ Revenue Velocity Across Periods</h3>
                <p class="edb-section-subtext">Gross sales volume and platform transaction margin tracking</p>
            </div>
        </div>

        <div class="edb-period-matrix">
            <div class="edb-period-card">
                <span class="edb-period-badge edb-period-badge--active">Today</span>
                <div class="edb-period-val"><?= format_price($today_revenue ?? 0) ?></div>
                <div class="edb-period-fee">Fees: <?= format_price($today_fees ?? 0) ?></div>
            </div>

            <div class="edb-period-card">
                <span class="edb-period-badge">Last 7 Days</span>
                <div class="edb-period-val"><?= format_price($week_revenue ?? 0) ?></div>
                <div class="edb-period-fee">Fees: <?= format_price($week_fees ?? 0) ?></div>
            </div>

            <div class="edb-period-card">
                <span class="edb-period-badge">This Month</span>
                <div class="edb-period-val"><?= format_price($month_revenue ?? 0) ?></div>
                <div class="edb-period-fee">Fees: <?= format_price($month_fees ?? 0) ?></div>
            </div>

            <div class="edb-period-card">
                <span class="edb-period-badge">All Time GPV</span>
                <div class="edb-period-val"><?= format_price($gross_revenue ?? 0) ?></div>
                <div class="edb-period-fee">Net Profit: <?= format_price($platform_profit ?? 0) ?></div>
            </div>
        </div>
    </section>

    <!-- Primary Financial Analytics & Escrow Liquidity Cards -->
    <section style="margin-bottom: 2rem;">
        <div class="edb-section-header">
            <div class="edb-section-title-wrap">
                <h3 class="edb-section-title">💰 Financial Liquidity &amp; Escrow Reserves</h3>
                <p class="edb-section-subtext">Customer gross volume, platform retained margin, and host escrow liabilities</p>
            </div>
        </div>

        <div class="admin-users-stats dashboard-stats-grid">
            <!-- Stat 1: Gross Platform Volume -->
            <div class="au-stat-card stat-card-highlighted" style="border-left: 4px solid var(--vn-brass);">
                <span class="au-stat-icon" style="background: var(--vn-brass-glow); color: var(--vn-brass);">
                    <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="2" y="5" width="20" height="14" rx="2" />
                        <line x1="2" y1="10" x2="22" y2="10" />
                    </svg>
                </span>
                <div class="au-stat-body">
                    <div class="stat-growth-row">
                        <span class="au-stat-num"><?= format_price($gross_revenue ?? 0) ?></span>
                        <?php if (!empty($revenue_growth)): ?>
                            <span class="stat-trend-tag <?= $revenue_growth >= 0 ? 'trend-up' : 'trend-down' ?>">
                                <?= $revenue_growth >= 0 ? '▲ +' : '▼ ' ?><?= abs($revenue_growth) ?>%
                            </span>
                        <?php endif; ?>
                    </div>
                    <span class="au-stat-lbl">Gross Platform Volume (GPV)</span>
                    <span class="stat-sub-info">All-time customer transactions</span>
                </div>
            </div>

            <!-- Stat 2: Platform Net Profit -->
            <div class="au-stat-card stat-card-highlighted" style="border-left: 4px solid #10b981;">
                <span class="au-stat-icon au-stat-icon--success">
                    <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="12" y1="1" x2="12" y2="23" />
                        <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" />
                    </svg>
                </span>
                <div class="au-stat-body">
                    <div class="stat-growth-row">
                        <span class="au-stat-num" style="color:#10b981;"><?= format_price($platform_profit ?? 0) ?></span>
                        <span class="stat-trend-tag trend-profit">
                            <?= $platform_profit_rate ?? 5 ?>% margin
                        </span>
                    </div>
                    <span class="au-stat-lbl">Platform Net Profit</span>
                    <span class="stat-sub-info">Retained convenience &amp; service fees</span>
                </div>
            </div>

            <!-- Stat 3: Disbursed Host Payouts -->
            <div class="au-stat-card stat-card-highlighted" style="border-left: 4px solid #14b8a6;">
                <span class="au-stat-icon" style="background: rgba(20, 184, 166, 0.12); color: #14b8a6;">
                    <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
                        <polyline points="22 4 12 14.01 9 11.01" />
                    </svg>
                </span>
                <div class="au-stat-body">
                    <span class="au-stat-num"><?= format_price($disbursed_payouts ?? 0) ?></span>
                    <span class="au-stat-lbl">Disbursed Host Payouts</span>
                    <span class="stat-sub-info">Transferred directly to host bank accounts</span>
                </div>
            </div>

            <!-- Stat 4: Escrow & Host Payable -->
            <div class="au-stat-card stat-card-highlighted" style="border-left: 4px solid #f59e0b;">
                <span class="au-stat-icon au-stat-icon--warning">
                    <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2" />
                        <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                    </svg>
                </span>
                <div class="au-stat-body">
                    <span class="au-stat-num"><?= format_price($pending_payouts ?? 0) ?></span>
                    <span class="au-stat-lbl">Escrow &amp; Host Payable</span>
                    <span class="stat-sub-info">
                        <?= format_price($escrow_revenue ?? 0) ?> live escrow · <?= format_price($pending_requests_amount ?? 0) ?> queued
                    </span>
                </div>
            </div>
        </div>
    </section>

    <!-- Platform User, Host & Gate Attendance KPIs -->
    <section style="margin-bottom: 2rem;">
        <div class="edb-section-header">
            <div class="edb-section-title-wrap">
                <h3 class="edb-section-title">👥 Audience, Organizers &amp; Gate Metrics</h3>
                <p class="edb-section-subtext">User adoption velocity, verified hosts, and event check-in ratios</p>
            </div>
        </div>

        <div class="admin-users-stats dashboard-stats-grid">
            <!-- Metric 1: Total Users -->
            <div class="au-stat-card">
                <span class="au-stat-icon">
                    <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                        <circle cx="9" cy="7" r="4" />
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87" />
                        <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                    </svg>
                </span>
                <div class="au-stat-body">
                    <div class="stat-growth-row">
                        <span class="au-stat-num"><?= number_format($total_users ?? 0) ?></span>
                        <?php if (!empty($user_growth)): ?>
                            <span class="stat-trend-tag <?= $user_growth >= 0 ? 'trend-up' : 'trend-down' ?>">
                                <?= $user_growth >= 0 ? '▲ +' : '▼ ' ?><?= abs($user_growth) ?>%
                            </span>
                        <?php endif; ?>
                    </div>
                    <span class="au-stat-lbl">Registered Accounts</span>
                    <span class="stat-sub-info">Buyers, hosts &amp; administrators</span>
                </div>
            </div>

            <!-- Metric 2: Host Funnel -->
            <div class="au-stat-card">
                <span class="au-stat-icon au-stat-icon--brand">
                    <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                        <circle cx="12" cy="7" r="4" />
                    </svg>
                </span>
                <div class="au-stat-body">
                    <div class="stat-growth-row">
                        <span class="au-stat-num"><?= number_format($total_hosts ?? 0) ?></span>
                        <span class="stat-trend-tag trend-profit">
                            <?= $host_verification_rate ?? 0 ?>% Verified
                        </span>
                    </div>
                    <span class="au-stat-lbl">Event Hosts</span>
                    <span class="stat-sub-info"><?= (int)($readyHosts ?? 0) ?> verified · <?= (int)($pendingHosts ?? 0) ?> pending bank</span>
                </div>
            </div>

            <!-- Metric 3: Total Tickets Issued -->
            <div class="au-stat-card">
                <span class="au-stat-icon" style="background: rgba(99, 102, 241, 0.12); color: #6366f1;">
                    <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="2" y="6" width="20" height="12" rx="2" />
                        <circle cx="12" cy="12" r="2" />
                        <path d="M6 12h.01M18 12h.01" />
                    </svg>
                </span>
                <div class="au-stat-body">
                    <div class="stat-growth-row">
                        <span class="au-stat-num"><?= number_format($total_tickets ?? 0) ?></span>
                        <?php if (!empty($ticket_growth)): ?>
                            <span class="stat-trend-tag <?= $ticket_growth >= 0 ? 'trend-up' : 'trend-down' ?>">
                                <?= $ticket_growth >= 0 ? '▲ +' : '▼ ' ?><?= abs($ticket_growth) ?>%
                            </span>
                        <?php endif; ?>
                    </div>
                    <span class="au-stat-lbl">Passes &amp; Tickets</span>
                    <span class="stat-sub-info"><?= (int)($today_tickets ?? 0) ?> issued today</span>
                </div>
            </div>

            <!-- Metric 4: Gate Check-in Clearance -->
            <div class="au-stat-card">
                <span class="au-stat-icon au-stat-icon--success">
                    <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20 6 9 17 4 12" />
                    </svg>
                </span>
                <div class="au-stat-body">
                    <div class="stat-growth-row">
                        <span class="au-stat-num" style="color: #10b981;"><?= number_format($checked_in_tickets ?? 0) ?></span>
                        <span class="stat-trend-tag trend-profit">
                            <?= $check_in_rate ?? 0 ?>% rate
                        </span>
                    </div>
                    <span class="au-stat-lbl">Gate Check-Ins</span>
                    <span class="stat-sub-info">Sub-second verified admissions</span>
                </div>
            </div>
        </div>
    </section>

</div>

<!-- ════════════════════════════════════════════════════════════════
     TAB 2: INTERACTIVE ANALYTICS & CHARTS
     ════════════════════════════════════════════════════════════════ -->
<div id="tab-analytics" class="edb-tab-pane <?= $activeTab === 'analytics' ? '' : 'hidden' ?>" data-tab-content="analytics" role="tabpanel">

    <section style="margin-bottom: 2rem;">
        <div class="edb-section-header">
            <div class="edb-section-title-wrap">
                <h3 class="edb-section-title">📈 Interactive Analytics &amp; Visual Trends</h3>
                <p class="edb-section-subtext">Monthly timeline distribution for sales, profit, tickets, and event lifecycle</p>
            </div>
        </div>

        <div class="admin-charts-grid">
            <!-- Chart 1: Revenue & Profit Timeline -->
            <div class="form-card admin-chart-card admin-chart-card--full">
                <div class="au-table-header chart-card-header">
                    <div>
                        <h3 style="margin:0; font-size:1.05rem; font-weight:800;">Financial Performance (Past 12 Months)</h3>
                        <p class="section-note" style="margin:0.25rem 0 0; font-size:0.82rem;">Gross sales volume vs platform net profit monthly trajectory.</p>
                    </div>
                </div>
                <div class="chart-canvas-wrapper" style="height: 320px; position:relative;">
                    <canvas id="financialTimelineChart"></canvas>
                </div>
            </div>

            <!-- Chart 2: Ticket Sales Volume -->
            <div class="form-card admin-chart-card">
                <div class="au-table-header chart-card-header">
                    <div>
                        <h3 style="margin:0; font-size:1.02rem; font-weight:800;">Ticket Sales Volume</h3>
                        <p class="section-note" style="margin:0.25rem 0 0; font-size:0.8rem;">Monthly paid &amp; comped tickets purchased.</p>
                    </div>
                </div>
                <div class="chart-canvas-wrapper" style="height: 260px; position:relative;">
                    <canvas id="ticketSalesChart"></canvas>
                </div>
            </div>

            <!-- Chart 3: Event Lifecycle Breakdown -->
            <div class="form-card admin-chart-card">
                <div class="au-table-header chart-card-header">
                    <div>
                        <h3 style="margin:0; font-size:1.02rem; font-weight:800;">Event Lifecycle Distribution</h3>
                        <p class="section-note" style="margin:0.25rem 0 0; font-size:0.8rem;">Active published vs completed vs draft vs cancelled.</p>
                    </div>
                </div>
                <div class="chart-canvas-wrapper" style="height: 260px; position:relative; display:flex; align-items:center; justify-content:center;">
                    <canvas id="eventLifecycleChart"></canvas>
                </div>
            </div>

            <!-- Chart 4: Payout Distribution Timeline -->
            <div class="form-card admin-chart-card admin-chart-card--full">
                <div class="au-table-header chart-card-header">
                    <div>
                        <h3 style="margin:0; font-size:1.05rem; font-weight:800;">Host Payout Disbursements vs Platform Fees (Past 12 Months)</h3>
                        <p class="section-note" style="margin:0.25rem 0 0; font-size:0.82rem;">Funds transferred to organizers vs fee margins retained.</p>
                    </div>
                </div>
                <div class="chart-canvas-wrapper" style="height: 280px; position:relative;">
                    <canvas id="payoutTimelineChart"></canvas>
                </div>
            </div>
        </div>
    </section>

</div>

<!-- ════════════════════════════════════════════════════════════════
     TAB 3: URGENT QUEUES & SYSTEM DIAGNOSTICS
     ════════════════════════════════════════════════════════════════ -->
<div id="tab-operations" class="edb-tab-pane <?= $activeTab === 'operations' ? '' : 'hidden' ?>" data-tab-content="operations" role="tabpanel">

    <!-- Super Admin System Health & Diagnostic Console -->
    <?php if ($isSuperAdmin): ?>
    <section class="super-admin-command-bar" style="margin-bottom: 2rem;">
        <div class="command-bar-header">
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <span style="font-size: 1.15rem;">⚙️</span>
                <h3 style="margin: 0; font-size: 1rem; font-weight: 800; color: var(--vn-brass);">Super Admin System Health Diagnostics</h3>
            </div>
            <span style="font-size: 0.78rem; font-weight: 700; background: var(--vn-forest-tint); color: var(--vn-forest); padding: 0.25rem 0.65rem; border-radius: 20px;">
                ● Live Sync Active
            </span>
        </div>

        <div class="super-diagnostics-grid">
            <div class="diag-item">
                <span class="diag-label">Runtime Engine</span>
                <span class="diag-val">PHP <?= e($system_diagnostics['php_version'] ?? PHP_VERSION) ?></span>
            </div>
            <div class="diag-item">
                <span class="diag-label">Database Server</span>
                <span class="diag-val">MariaDB / <?= e(substr($system_diagnostics['db_version'] ?? '10.4', 0, 10)) ?></span>
            </div>
            <div class="diag-item">
                <span class="diag-label">Environment</span>
                <span class="diag-val" style="text-transform: capitalize; color: #10b981;">● <?= e($system_diagnostics['environment'] ?? 'production') ?></span>
            </div>
            <div class="diag-item">
                <span class="diag-label">Schema Migrations</span>
                <span class="diag-val"><?= (int)($system_diagnostics['migrations_count'] ?? 28) ?> Batches Synced</span>
            </div>
            <div class="diag-item">
                <span class="diag-label">Storage Cache</span>
                <span class="diag-val" style="color: #10b981;">✓ Writable</span>
            </div>
            <div class="diag-item">
                <span class="diag-label">Default Gateway</span>
                <span class="diag-val" style="text-transform: capitalize;"><?= e($system_diagnostics['active_gateway'] ?? 'Paystack') ?></span>
            </div>
            <div class="diag-item">
                <span class="diag-label">Maintenance Mode</span>
                <span class="diag-val" style="color: <?= ($system_diagnostics['maintenance_mode'] ?? 'no') === 'yes' ? '#ef4444' : '#10b981' ?>;">
                    <?= ($system_diagnostics['maintenance_mode'] ?? 'no') === 'yes' ? '🔴 Enabled' : '🟢 Live' ?>
                </span>
            </div>
        </div>

        <!-- Super Admin Shortcuts -->
        <div class="super-command-actions">
            <a href="{{ route('admin/events') }}" class="super-cmd-link"><span>🎪 Manage Events (<?= (int)($total_events ?? 0) ?>)</span></a>
            <a href="{{ route('admin/domain-requests') }}" class="super-cmd-link"><span>🌐 Custom Domains &amp; API <?= $pendingDomains > 0 ? "($pendingDomains)" : '' ?></span></a>
            <a href="{{ route('admin/settings') }}" class="super-cmd-link"><span>⚙️ System Settings</span></a>
            <a href="{{ route('admin/users') }}" class="super-cmd-link"><span>👑 RBAC &amp; Privileges</span></a>
            <a href="{{ route('admin/teams') }}" class="super-cmd-link"><span>🏢 Staff Deployments (<?= (int)($active_deployments ?? 0) ?>)</span></a>
            <a href="{{ route('admin/payouts') }}" class="super-cmd-link"><span>💳 Payout Approval Queue</span></a>
            <a href="{{ route('admin/attendees') }}" class="super-cmd-link"><span>📇 Customer Directory</span></a>
        </div>
    </section>
    <?php endif; ?>

    <!-- Action Queues Grid -->
    <section style="margin-bottom: 2rem;">
        <div class="edb-section-header">
            <div class="edb-section-title-wrap">
                <h3 class="edb-section-title">⚡ Operational Action Items</h3>
                <p class="edb-section-subtext">Click any queue to process approvals and staff dispatch</p>
            </div>
        </div>

        <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap:1.25rem;">
            <!-- Queue 1: Pending Payouts -->
            <div class="form-card" style="padding:1.5rem; border-radius:var(--vn-radius-md); border-left:4px solid #f59e0b;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.75rem;">
                    <span style="font-size:1.4rem;">💳</span>
                    <span class="edb-tab-badge <?= ($pending_payouts_count ?? 0) > 0 ? 'edb-tab-badge--alert' : '' ?>"><?= (int)($pending_payouts_count ?? 0) ?> Pending</span>
                </div>
                <h4 style="margin:0 0 0.35rem; font-size:1.1rem; font-family:var(--vn-font-serif);">Host Payout Requests</h4>
                <p style="font-size:0.85rem; color:var(--vn-ink-muted); margin:0 0 1rem;">
                    Total pending: <strong><?= format_price($pending_requests_amount ?? 0) ?></strong> awaiting escrow settlement.
                </p>
                <a href="{{ route('admin/payouts') }}" class="admin-action-btn primary" style="width:100%; justify-content:center; text-align:center;">
                    Open Payout Approval Queue →
                </a>
            </div>

            <!-- Queue 2: Host Staffing Requests -->
            <div class="form-card" style="padding:1.5rem; border-radius:var(--vn-radius-md); border-left:4px solid #6366f1;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.75rem;">
                    <span style="font-size:1.4rem;">🛡️</span>
                    <span class="edb-tab-badge"><?= (int)($pending_staff_requests_count ?? 0) ?> Pending</span>
                </div>
                <h4 style="margin:0 0 0.35rem; font-size:1.1rem; font-family:var(--vn-font-serif);">Event Staffing Deployments</h4>
                <p style="font-size:0.85rem; color:var(--vn-ink-muted); margin:0 0 1rem;">
                    Organizers requesting verified venue check-in personnel and security scanner accounts.
                </p>
                <a href="{{ route('admin/teams') }}" class="admin-action-btn" style="width:100%; justify-content:center; text-align:center; background:var(--vn-parchment-warm); border:1.5px solid var(--vn-border);">
                    Review Staff Requests →
                </a>
            </div>

            <!-- Queue 3: Support Inquiries -->
            <div class="form-card" style="padding:1.5rem; border-radius:var(--vn-radius-md); border-left:4px solid #ec4899;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.75rem;">
                    <span style="font-size:1.4rem;">🎧</span>
                    <span class="edb-tab-badge <?= ($unresolved_support ?? 0) > 0 ? 'edb-tab-badge--alert' : '' ?>"><?= (int)($unresolved_support ?? 0) ?> Unresolved</span>
                </div>
                <h4 style="margin:0 0 0.35rem; font-size:1.1rem; font-family:var(--vn-font-serif);">Customer &amp; Host Support</h4>
                <p style="font-size:0.85rem; color:var(--vn-ink-muted); margin:0 0 1rem;">
                    Direct inquiries regarding tickets, refunds, transfers, and account validation.
                </p>
                <a href="{{ route('admin/support') }}" class="admin-action-btn" style="width:100%; justify-content:center; text-align:center; background:var(--vn-parchment-warm); border:1.5px solid var(--vn-border);">
                    Open Support Desk →
                </a>
            </div>

            <!-- Queue 4: Custom Domain Requests -->
            <div class="form-card" style="padding:1.5rem; border-radius:var(--vn-radius-md); border-left:4px solid #10b981;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.75rem;">
                    <span style="font-size:1.4rem;">🌐</span>
                    <span class="edb-tab-badge <?= $pendingDomains > 0 ? 'edb-tab-badge--alert' : '' ?>"><?= $pendingDomains ?> Pending</span>
                </div>
                <h4 style="margin:0 0 0.35rem; font-size:1.1rem; font-family:var(--vn-font-serif);">Custom Domain Mapping</h4>
                <p style="font-size:0.85rem; color:var(--vn-ink-muted); margin:0 0 1rem;">
                    White-label domain requests and SSL verification for branded host event portals.
                </p>
                <a href="{{ route('admin/domain-requests') }}" class="admin-action-btn" style="width:100%; justify-content:center; text-align:center; background:var(--vn-parchment-warm); border:1.5px solid var(--vn-border);">
                    Provision Domains →
                </a>
            </div>
        </div>
    </section>

</div>

<!-- ════════════════════════════════════════════════════════════════
     TAB 4: EVENTS & HOSTS DIRECTORY
     ════════════════════════════════════════════════════════════════ -->
<div id="tab-rosters" class="edb-tab-pane <?= $activeTab === 'rosters' ? '' : 'hidden' ?>" data-tab-content="rosters" role="tabpanel">

    <!-- Recent Published Events -->
    <section class="dashboard-roster-section" style="margin-bottom: 2.5rem;">
        <div class="edb-section-header">
            <div class="edb-section-title-wrap">
                <h3 class="edb-section-title">📅 Published Events Roster</h3>
                <p class="edb-section-subtext">Monitor event capacity, ticket velocity, and venue check-in progress</p>
            </div>
            <a href="{{ route('admin/events') }}" class="admin-action-btn primary" style="font-size:0.85rem; padding:0.45rem 1rem;">
                View All Events (<?= (int)($total_events ?? 0) ?>) →
            </a>
        </div>

        <div class="roster-events-grid">
            <?php if (empty($recent_events)): ?>
                <div class="roster-empty-state">
                    <span style="font-size:2rem;">📭</span>
                    <p>No published events found yet.</p>
                </div>
            <?php else: ?>
                <?php foreach ($recent_events as $event):
                    $capacity = (int)($event['capacity'] ?? 0);
                    $sold = (int)($event['sold'] ?? 0);
                    $percent = $capacity > 0 ? min(100, round(($sold / $capacity) * 100)) : 0;
                    $eventDate = strtotime($event['date']);
                    $isUpcoming = $eventDate > time();
                    $eventImg = !empty($event['image']) ? $event['image'] : null;
                    $categoryColors = [
                        'Tech' => '#6366f1', 'Music' => '#ec4899', 'Business' => '#3b82f6',
                        'Sports' => '#10b981', 'Art' => '#f59e0b', 'Education' => '#14b8a6',
                        'Food' => '#ef4444', 'Health' => '#22d3ee',
                    ];
                    $catColor = $categoryColors[$event['category'] ?? ''] ?? 'var(--vn-brass)';
                ?>
                <article class="roster-event-card">
                    <div class="roster-event-header" style="background: <?= $eventImg ? 'url(' . e($eventImg) . ') center/cover no-repeat' : 'linear-gradient(135deg, ' . $catColor . '22 0%, ' . $catColor . '44 100%)' ?>;">
                        <div class="roster-event-header-overlay">
                            <span class="roster-event-status-pill <?= $isUpcoming ? 'roster-pill--upcoming' : 'roster-pill--past' ?>">
                                <?= $isUpcoming ? '🟢 Upcoming' : '✅ Completed' ?>
                            </span>
                            <?php if (!empty($event['is_recurring'])): ?>
                                <span class="roster-event-category-pill" style="background: rgba(99,102,241,0.25); color: #a5b4fc; border: 1px solid rgba(99,102,241,0.45); font-weight: 700;">
                                    🔄 Series (<?= e($event['recurrence_count'] ?? '') ?> Dates)
                                </span>
                            <?php endif; ?>
                            <?php if (!empty($event['category'])): ?>
                                <span class="roster-event-category-pill" style="background: <?= $catColor ?>22; color: <?= $catColor ?>; border: 1px solid <?= $catColor ?>44;">
                                    <?= e($event['category']) ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="roster-event-body">
                        <h4 class="roster-event-title"><?= e($event['name']) ?></h4>

                        <div class="roster-event-meta-grid">
                            <div class="roster-meta-item">
                                <span class="roster-meta-icon">📍</span>
                                <span class="roster-meta-text"><?= e($event['location'] ?: 'TBD') ?></span>
                            </div>
                            <div class="roster-meta-item">
                                <span class="roster-meta-icon">🗓️</span>
                                <span class="roster-meta-text"><?= date('M d, Y', $eventDate) ?></span>
                            </div>
                        </div>

                        <!-- Capacity Progress -->
                        <div class="roster-event-capacity">
                            <div class="roster-capacity-header">
                                <span class="roster-capacity-label">🎫 Tickets Sold</span>
                                <span class="roster-capacity-numbers"><?= number_format($sold) ?> / <?= number_format($capacity) ?></span>
                            </div>
                            <div class="roster-progress-track">
                                <div class="roster-progress-fill" style="width: <?= $percent ?>%; background: <?= $percent > 80 ? '#10b981' : ($percent > 40 ? '#3b82f6' : '#f59e0b') ?>;"></div>
                            </div>
                            <div class="roster-capacity-footer">
                                <span><?= $percent ?>% filled</span>
                                <span style="font-weight:700; color:<?= $catColor ?>;"><?= format_price($event['total_sales'] ?? 0) ?></span>
                            </div>
                        </div>

                        <!-- Organizer Row -->
                        <div class="roster-event-organizer">
                            <span class="roster-organizer-avatar"><?= strtoupper(substr($event['organizer_name'] ?? 'H', 0, 1)) ?></span>
                            <div class="roster-organizer-info">
                                <span class="roster-organizer-name"><?= e($event['organizer_name'] ?? 'Organizer') ?></span>
                                <span class="roster-organizer-email"><?= e($event['organizer_email']) ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="roster-event-footer">
                        <a href="{{ route('admin/events/' . $event['id']) }}" class="roster-detail-link">View Details →</a>
                    </div>
                </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>

    <!-- Hosts & Organizers Roster -->
    <section class="dashboard-roster-section" style="margin-bottom: 2rem;">
        <div class="edb-section-header">
            <div class="edb-section-title-wrap">
                <h3 class="edb-section-title">🎤 Hosts &amp; Organizers Roster</h3>
                <p class="edb-section-subtext">Monitor host verification, linked payout banks, and sales volume</p>
            </div>
            <a href="{{ route('admin/users') }}?role=host" class="admin-action-btn primary" style="font-size:0.85rem; padding:0.45rem 1rem;">
                View All Hosts (<?= (int)($total_hosts ?? 0) ?>) →
            </a>
        </div>

        <div class="roster-hosts-grid">
            <?php if (empty($recent_hosts)): ?>
                <div class="roster-empty-state">
                    <span style="font-size:2rem;">👤</span>
                    <p>No hosts found yet.</p>
                </div>
            <?php else: ?>
                <?php foreach ($recent_hosts as $rHost):
                    $hasBank = !empty($rHost['payout_bank']) && !empty($rHost['payout_account']);
                    $avatarText = strtoupper(substr($rHost['name'] ?? 'H', 0, 2));
                    $hue = (crc32($rHost['email'] ?? '') % 260) + 100;
                    $totalEvents = (int)($rHost['total_events_count'] ?? 0);
                    $activeEvents = (int)($rHost['active_events_count'] ?? 0);
                    $hostSales = (float)($rHost['host_total_sales'] ?? 0);
                    $isActive = ($rHost['status'] ?? '') === 'active';
                ?>
                <article class="roster-host-card">
                    <div class="roster-host-identity">
                        <div class="roster-host-avatar" style="background: hsl(<?= $hue ?>, 65%, 92%); color: hsl(<?= $hue ?>, 55%, 40%);">
                            <?= $avatarText ?>
                        </div>
                        <div class="roster-host-name-block">
                            <h4 class="roster-host-name"><?= e($rHost['name']) ?></h4>
                            <span class="roster-host-email"><?= e($rHost['email']) ?></span>
                            <div class="roster-host-badges">
                                <span class="roster-host-status-dot <?= $isActive ? 'roster-dot--active' : 'roster-dot--inactive' ?>"></span>
                                <span class="roster-host-status-text"><?= $isActive ? 'Active' : ucfirst($rHost['status'] ?? 'Unknown') ?></span>
                                <?php if ($hasBank): ?>
                                    <span class="roster-bank-badge roster-bank--verified">🏦 Verified</span>
                                <?php else: ?>
                                    <span class="roster-bank-badge roster-bank--pending">🏦 Pending Bank</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="roster-host-stats">
                        <div class="roster-host-stat">
                            <span class="roster-stat-value"><?= $totalEvents ?></span>
                            <span class="roster-stat-label">Total Events</span>
                        </div>
                        <div class="roster-host-stat">
                            <span class="roster-stat-value" style="color: #10b981;"><?= $activeEvents ?></span>
                            <span class="roster-stat-label">Active</span>
                        </div>
                        <div class="roster-host-stat">
                            <span class="roster-stat-value" style="color: var(--vn-brass);"><?= format_price($hostSales) ?></span>
                            <span class="roster-stat-label">Revenue</span>
                        </div>
                    </div>

                    <?php if ($hasBank): ?>
                        <div class="roster-host-bank-info">
                            <span>🏦 <?= e($rHost['payout_bank']) ?></span>
                            <span>••••<?= substr($rHost['payout_account'], -4) ?></span>
                        </div>
                    <?php else: ?>
                        <div class="roster-host-bank-info roster-bank-info--pending">
                            <span>⚠️ No payout account linked</span>
                        </div>
                    <?php endif; ?>

                    <div class="roster-host-action">
                        <a href="{{ route('admin/users') }}" class="roster-detail-link">View Profile →</a>
                    </div>
                </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>

</div>

<!-- ════════════════════════════════════════════════════════════════
     TAB 5: ACTIVITY & FINANCIAL AUDIT LOGS
     ════════════════════════════════════════════════════════════════ -->
<div id="tab-activity" class="edb-tab-pane <?= $activeTab === 'activity' ? '' : 'hidden' ?>" data-tab-content="activity" role="tabpanel">

    <!-- Real-Time Attendee Ticket Purchases Feed -->
    <section class="form-card ticket-activity-section" style="margin-bottom: 2rem;">
        <div class="au-table-header audit-header-row">
            <div>
                <div class="ticket-header-title-row" style="display:flex; align-items:center; gap:0.5rem; flex-wrap:wrap;">
                    <h2 style="margin:0; font-size:1.15rem; font-weight:800; font-family:var(--vn-font-serif); display:flex; align-items:center; gap:0.4rem;">
                        <span>🎟️</span> Real-Time Attendee Ticket Orders
                    </h2>
                    <span class="badge-live-pulse" style="font-size:0.75rem; font-weight:700; background:rgba(16, 185, 129, 0.12); color:#10b981; padding:0.2rem 0.55rem; border-radius:12px; display:inline-flex; align-items:center; gap:0.35rem;">
                        <span style="width:6px; height:6px; border-radius:50%; background:#10b981; display:inline-block; animation:pulseGlow 1.5s infinite;"></span>
                        Live Feed
                    </span>
                    <?php if (($today_tickets ?? 0) > 0): ?>
                        <span style="font-size:0.75rem; font-weight:800; background:var(--vn-brass-glow); color:var(--vn-brass); padding:0.2rem 0.55rem; border-radius:12px;">
                            <?= (int)$today_tickets ?> purchased today
                        </span>
                    <?php endif; ?>
                </div>
                <p class="section-note" style="margin:0.25rem 0 0; font-size:0.82rem;">Visibility into incoming ticket orders, buyer identities, and door verification state.</p>
            </div>
            <div class="ticket-stream-controls">
                <input type="text" id="ticketPurchasesSearch" placeholder="🔍 Filter attendee, email, tier, or event..." class="audit-search-box">
                <a href="{{ route('admin/attendees') }}" class="admin-action-btn primary" style="font-size:0.82rem; padding:0.45rem 0.95rem; border-radius:8px; font-weight:700; text-decoration:none;">
                    Attendee Directory →
                </a>
            </div>
        </div>

        <div class="au-table-wrap ticket-stream-table-wrap" tabindex="0" role="region" aria-label="Real-Time Attendee Ticket Purchases">
            <table class="au-table" id="ticketPurchasesTable">
                <thead>
                    <tr>
                        <th>Ticket</th>
                        <th>Attendee</th>
                        <th>Event</th>
                        <th>Tier</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Gate</th>
                        <th>Purchased</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recent_ticket_purchases)): ?>
                        <tr><td colspan="8" style="padding:2.5rem; text-align:center; color:var(--vn-ink-muted);">No ticket purchases recorded yet.</td></tr>
                    <?php else: ?>
                        <?php foreach ($recent_ticket_purchases as $tkt): 
                            $hue = (crc32($tkt['attendee_email'] ?? $tkt['id']) % 260) + 100;
                            $initials = strtoupper(substr($tkt['attendee_name'] ?: 'A', 0, 2));
                            $isPaid = ($tkt['status'] === 'Paid');
                            $isComped = ($tkt['status'] === 'Comped');
                            $isCancelled = ($tkt['status'] === 'Cancelled');
                            $statusClass = $isPaid ? 'au-status--active' : ($isComped ? 'au-role--host' : ($isCancelled ? 'au-status--suspended' : 'au-status--unverified'));
                            $statusLabel = $isPaid ? 'Paid ✓' : ($isComped ? 'Comped 🎁' : ($isCancelled ? 'Cancelled' : ucfirst($tkt['status'])));
                            $isCheckedIn = !empty($tkt['checked_in']);
                        ?>
                        <tr class="ticket-purchase-row">
                            <td style="font-weight:700; font-family:monospace; color:var(--vn-brass);">
                                #<?= (int)$tkt['id'] ?>
                            </td>
                            <td>
                                <div style="display:flex; align-items:center; gap:0.6rem;">
                                    <span class="user-avatar-circle" style="background: hsl(<?= $hue ?>, 65%, 90%); color: hsl(<?= $hue ?>, 60%, 35%);">
                                        <?= $initials ?>
                                    </span>
                                    <div>
                                        <div style="font-weight:700;"><?= e($tkt['attendee_name'] ?: ($tkt['buyer_name'] ?: 'Guest Attendee')) ?></div>
                                        <div style="font-size:0.75rem; color:var(--vn-ink-muted);"><?= e($tkt['attendee_email'] ?: '—') ?></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <a href="{{ route('event/' . ($tkt['event_custom_url'] ?: $tkt['event_slug'])) }}" target="_blank" style="color:var(--vn-ink); font-weight:600; text-decoration:none;">
                                    <?= e($tkt['event_name'] ?: 'Event #' . $tkt['event_id']) ?> ↗
                                </a>
                            </td>
                            <td>
                                <span class="au-role-badge au-role--attendee" style="font-size:0.72rem;">
                                    <?= e($tkt['ticket_tier'] ?: 'General Pass') ?>
                                </span>
                            </td>
                            <td style="font-weight:700;">
                                <?= (float)($tkt['price'] ?? 0) > 0 ? format_price($tkt['price'] ?? 0) : '<span style="color:#10b981;">Free</span>' ?>
                            </td>
                            <td>
                                <span class="au-status-badge <?= $statusClass ?>">
                                    <?= $statusLabel ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($isCheckedIn): ?>
                                    <span style="font-size:0.75rem; color:#10b981; font-weight:700; display:inline-flex; align-items:center; gap:0.25rem;">
                                        ✓ In <?= $tkt['checked_in_at'] ? date('H:i', strtotime($tkt['checked_in_at'])) : '' ?>
                                    </span>
                                <?php else: ?>
                                    <span style="font-size:0.75rem; color:var(--vn-ink-faint);">Awaiting</span>
                                <?php endif; ?>
                            </td>
                            <td style="font-size:0.8rem; color:var(--vn-ink-muted); white-space:nowrap;">
                                <?= date('M d, H:i', strtotime($tkt['purchase_date'] ?: 'now')) ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

    <!-- Platform Financial Audit Log Stream -->
    <section class="form-card audit-log-section">
        <div class="au-table-header audit-header-row">
            <div>
                <h2 style="margin:0; font-size:1.15rem; font-weight:800; font-family:var(--vn-font-serif); display:flex; align-items:center; gap:0.4rem;">
                    <span>🛡️</span> System Financial Audit Log
                </h2>
                <p class="section-note" style="margin:0.25rem 0 0; font-size:0.82rem;">Real-time ledger entries, payment processing references, and settlement audit logs.</p>
            </div>
            <div class="ticket-stream-controls">
                <input type="text" id="auditSearchInput" placeholder="🔍 Search reference, type, status..." class="audit-search-box">
                <a href="{{ route('admin/payouts') }}" class="admin-action-btn" style="font-size:0.82rem; padding:0.45rem 0.95rem; border-radius:8px; background:var(--vn-parchment-warm); border:1.5px solid var(--vn-border);">
                    Payouts Ledger →
                </a>
            </div>
        </div>

        <div class="au-table-wrap ticket-stream-table-wrap" tabindex="0" role="region" aria-label="Financial Audit Trail">
            <table class="au-table" id="auditTable">
                <thead>
                    <tr>
                        <th>Reference</th>
                        <th>Type</th>
                        <th>Event</th>
                        <th>Gross</th>
                        <th>Platform Fee</th>
                        <th>Net Disbursed</th>
                        <th>Status</th>
                        <th>Timestamp</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($audit_trail)): ?>
                        <tr><td colspan="8" style="padding:2.5rem; text-align:center; color:var(--vn-ink-muted);">No financial audit events logged yet.</td></tr>
                    <?php else: ?>
                        <?php foreach ($audit_trail as $tx):
                            $isCompleted = ($tx['status'] ?? '') === 'completed';
                            $isPending = ($tx['status'] ?? '') === 'pending';
                            $statusBadge = $isCompleted ? 'au-status--active' : ($isPending ? 'au-status--unverified' : 'au-status--suspended');
                            $typeLabel = ucfirst($tx['type'] ?? 'payment');
                        ?>
                        <tr class="audit-row">
                            <td style="font-family:monospace; font-size:0.82rem; color:var(--vn-brass); font-weight:700;">
                                <?= e($tx['reference'] ?? ('TXN-' . ($tx['id'] ?? ''))) ?>
                            </td>
                            <td>
                                <span class="au-role-badge <?= ($tx['type'] ?? '') === 'payout' ? 'au-role--host' : 'au-role--attendee' ?>" style="font-size:0.72rem;">
                                    <?= e($typeLabel) ?>
                                </span>
                            </td>
                            <td><?= e($tx['event_name'] ?? '—') ?></td>
                            <td style="font-weight:700;"><?= format_price($tx['total_amount'] ?? 0) ?></td>
                            <td style="color:#10b981;"><?= format_price($tx['platform_fee'] ?? 0) ?></td>
                            <td style="font-weight:600;"><?= format_price($tx['net_amount'] ?? 0) ?></td>
                            <td>
                                <span class="au-status-badge <?= $statusBadge ?>">
                                    <?= e(ucfirst($tx['status'] ?? 'pending')) ?>
                                </span>
                            </td>
                            <td style="font-size:0.8rem; color:var(--vn-ink-muted); white-space:nowrap;">
                                <?= date('M d, Y • H:i', strtotime($tx['created_at'] ?? 'now')) ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

</div>

<!-- ════════════════════════════════════════════════════════════════
     INTERACTIVE JAVASCRIPT: TAB SWITCHING, CHARTS & LIVE SEARCH
     ════════════════════════════════════════════════════════════════ -->
<script>
// ─── Executive Tab Switching ───
function switchAdminTab(tabId) {
    const panes = document.querySelectorAll('[data-tab-content]');
    const btns = document.querySelectorAll('[data-tab-btn]');

    panes.forEach(p => p.classList.add('hidden'));
    btns.forEach(b => {
        b.classList.remove('active');
        b.setAttribute('aria-selected', 'false');
    });

    const targetPane = document.getElementById('tab-' + tabId);
    const targetBtn = document.querySelector(`[data-tab-btn="${tabId}"]`);

    if (targetPane) targetPane.classList.remove('hidden');
    if (targetBtn) {
        targetBtn.classList.add('active');
        targetBtn.setAttribute('aria-selected', 'true');
    }

    // Trigger Chart.js recalculation on tab switch
    window.dispatchEvent(new Event('resize'));

    // Update browser URL query parameter without page refresh
    const url = new URL(window.location);
    url.searchParams.set('tab', tabId);
    window.history.replaceState({}, '', url);
}

document.addEventListener('DOMContentLoaded', function() {
    // ─── Bind Tab Click Handlers ───
    document.querySelectorAll('[data-tab-btn]').forEach(btn => {
        btn.addEventListener('click', function() {
            const tabId = this.getAttribute('data-tab-btn');
            switchAdminTab(tabId);
        });
    });

    // ─── Live Clock Updater ───
    const clockEl = document.getElementById('dashboard-live-clock');
    if (clockEl) {
        setInterval(() => {
            const now = new Date();
            clockEl.textContent = '🕒 ' + now.toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' }) + ' • ' + now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: true });
        }, 30000);
    }

    // ─── Attendee Ticket Purchases Live Filter ───
    const ticketSearchInput = document.getElementById('ticketPurchasesSearch');
    const ticketTableRows = document.querySelectorAll('.ticket-purchase-row');
    if (ticketSearchInput && ticketTableRows.length) {
        ticketSearchInput.addEventListener('input', function() {
            const query = this.value.toLowerCase().trim();
            ticketTableRows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(query) ? '' : 'none';
            });
        });
    }

    // ─── Audit Log Live Filter ───
    const searchInput = document.getElementById('auditSearchInput');
    const tableRows = document.querySelectorAll('.audit-row');
    if (searchInput && tableRows.length) {
        searchInput.addEventListener('input', function() {
            const query = this.value.toLowerCase().trim();
            tableRows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(query) ? '' : 'none';
            });
        });
    }

    // ─── Chart.js Theming Setup ───
    const isDark = document.documentElement.getAttribute('data-theme') === 'dark' || 
                   window.matchMedia('(prefers-color-scheme: dark)').matches;

    const textColor = isDark ? '#a69888' : '#6b5e52';
    const gridColor = isDark ? 'rgba(255, 255, 255, 0.06)' : 'rgba(0, 0, 0, 0.06)';
    const tooltipBg = isDark ? '#1c1815' : '#ffffff';
    const tooltipBorder = isDark ? 'rgba(217, 119, 6, 0.3)' : 'rgba(180, 83, 9, 0.2)';
    const tooltipText = isDark ? '#f7f3ec' : '#1a1614';

    const sharedTooltip = {
        backgroundColor: tooltipBg,
        titleColor: tooltipText,
        bodyColor: tooltipText,
        borderColor: tooltipBorder,
        borderWidth: 1.5,
        cornerRadius: 10,
        padding: 12,
        titleFont: { family: 'Plus Jakarta Sans', size: 13, weight: '700' },
        bodyFont: { family: 'Plus Jakarta Sans', size: 12 },
        displayColors: true,
        boxPadding: 4
    };

    const sharedScales = {
        x: {
            grid: { color: gridColor, drawBorder: false },
            ticks: { color: textColor, font: { family: 'Plus Jakarta Sans', size: 11 } }
        },
        y: {
            grid: { color: gridColor, drawBorder: false },
            ticks: { color: textColor, font: { family: 'Plus Jakarta Sans', size: 11 } },
            beginAtZero: true
        }
    };

    // ─── Chart 1: Financial Performance (12 months) ───
    const timelineCtx = document.getElementById('financialTimelineChart');
    if (timelineCtx) {
        new Chart(timelineCtx, {
            type: 'bar',
            data: {
                labels: <?= json_encode($monthly_labels ?? []) ?>,
                datasets: [
                    {
                        label: 'Gross Sales (<?= setting('currency', 'NGN') ?>)',
                        data: <?= json_encode($monthly_revenue ?? []) ?>,
                        backgroundColor: 'rgba(180, 83, 9, 0.65)',
                        borderColor: '#b45309',
                        borderWidth: 1.5,
                        borderRadius: 6,
                        borderSkipped: false,
                        order: 2
                    },
                    {
                        label: 'Platform Profit (<?= setting('currency', 'NGN') ?>)',
                        data: <?= json_encode($monthly_profit ?? []) ?>,
                        type: 'line',
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16, 185, 129, 0.12)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: '#10b981',
                        pointBorderColor: isDark ? '#1c1815' : '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 5,
                        pointHoverRadius: 7,
                        order: 1
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    tooltip: sharedTooltip,
                    legend: {
                        labels: { color: textColor, font: { family: 'Plus Jakarta Sans', size: 12 }, usePointStyle: true, pointStyle: 'circle', padding: 20 }
                    }
                },
                scales: sharedScales
            }
        });
    }

    // ─── Chart 2: Ticket Sales Volume ───
    const ticketCtx = document.getElementById('ticketSalesChart');
    if (ticketCtx) {
        new Chart(ticketCtx, {
            type: 'line',
            data: {
                labels: <?= json_encode($monthly_labels ?? []) ?>,
                datasets: [
                    {
                        label: 'Tickets Issued',
                        data: <?= json_encode($monthly_tickets ?? []) ?>,
                        borderColor: '#6366f1',
                        backgroundColor: 'rgba(99, 102, 241, 0.12)',
                        borderWidth: 2.5,
                        fill: true,
                        tension: 0.35,
                        pointBackgroundColor: '#6366f1',
                        pointRadius: 4,
                        pointHoverRadius: 6
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    tooltip: sharedTooltip,
                    legend: { display: false }
                },
                scales: sharedScales
            }
        });
    }

    // ─── Chart 3: Event Lifecycle Breakdown ───
    const lifecycleCtx = document.getElementById('eventLifecycleChart');
    if (lifecycleCtx) {
        new Chart(lifecycleCtx, {
            type: 'doughnut',
            data: {
                labels: ['Active Published', 'Completed', 'Draft', 'Cancelled'],
                datasets: [
                    {
                        data: [
                            <?= (int)($live_events ?? 0) ?>,
                            <?= (int)($completed_events ?? 0) ?>,
                            <?= (int)($draft_events ?? 0) ?>,
                            <?= (int)($cancelled_events ?? 0) ?>
                        ],
                        backgroundColor: ['#10b981', '#3b82f6', '#94a3b8', '#ef4444'],
                        borderWidth: 2,
                        borderColor: isDark ? '#1c1815' : '#ffffff'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    tooltip: sharedTooltip,
                    legend: {
                        position: 'bottom',
                        labels: { color: textColor, font: { family: 'Plus Jakarta Sans', size: 11 }, usePointStyle: true, padding: 12 }
                    }
                },
                cutout: '70%'
            }
        });
    }

    // ─── Chart 4: Host Payout Disbursements Timeline ───
    const payoutCtx = document.getElementById('payoutTimelineChart');
    if (payoutCtx) {
        new Chart(payoutCtx, {
            type: 'bar',
            data: {
                labels: <?= json_encode($monthly_labels ?? []) ?>,
                datasets: [
                    {
                        label: 'Disbursed Payouts (<?= setting('currency', 'NGN') ?>)',
                        data: <?= json_encode($monthly_payouts ?? []) ?>,
                        backgroundColor: 'rgba(20, 184, 166, 0.65)',
                        borderColor: '#14b8a6',
                        borderWidth: 1.5,
                        borderRadius: 6
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    tooltip: sharedTooltip,
                    legend: {
                        labels: { color: textColor, font: { family: 'Plus Jakarta Sans', size: 12 }, usePointStyle: true, pointStyle: 'circle' }
                    }
                },
                scales: sharedScales
            }
        });
    }
});
</script>
@endsection
