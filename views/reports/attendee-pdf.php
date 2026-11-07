<?php
/**
 * Official A4 Letterheaded Attendee Manifest & PDF Report
 *
 * Professional, executive-ready admission manifest designed for printing and PDF generation.
 * Features Eventlyy official letterhead, dynamic theme palette, event summary metrics,
 * comprehensive attendee check-in roster, and gate verification sign-off blocks.
 *
 * @package Eventlyy
 * @subpackage Views/Reports
 */

$siteTitle    = site_setting('site_title', 'Eventlyy');
$siteLogo     = site_setting('site_logo');
$supportEmail = site_setting('support_email', 'support@eventlyy.com');
$appUrl       = app_base_url();
$primaryHex   = function_exists('theme_primary_color') ? theme_primary_color() : '#108a55';
$secondaryHex = function_exists('theme_secondary_color') ? theme_secondary_color() : '#1aae6c';
$primaryRgb   = function_exists('hex_to_rgb_triple') ? str_replace(' ', ', ', hex_to_rgb_triple($primaryHex)) : '16, 138, 85';

$generationTime = date('F j, Y - g:i A');
$manifestRef    = 'EVT-MAN-' . strtoupper(substr(md5(($event->id ?? 'DIR') . time()), 0, 8));

// Calculations
$totalTickets  = count($attendees ?? []);
$totalCheckedIn = 0;
$totalRevenue   = 0;

$isDetailedEventMode = !empty($event);

if ($isDetailedEventMode) {
    foreach ($attendees as $att) {
        if (!empty($att['checked_in'])) {
            $totalCheckedIn++;
        }
        $totalRevenue += (float)($att['price'] ?? 0);
    }
    $checkInRate = $totalTickets > 0 ? round(($totalCheckedIn / $totalTickets) * 100, 1) : 0;
} else {
    foreach ($attendees as $att) {
        $totalRevenue += (float)($att['total_spent'] ?? 0);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($isDetailedEventMode ? ($event->name . ' - Attendee Manifest') : 'Attendee Directory Manifest') ?> | <?= e($siteTitle) ?></title>
    <link rel="icon" href="<?= e(asset($siteLogo ?: 'assets/img/branding/pwa-icon-192.png')) ?>">
    <style>
        /* ── Typography & Variables ─────────────────────────────────────── */
        :root {
            --primary: <?= $primaryHex ?>;
            --secondary: <?= $secondaryHex ?>;
            --primary-rgb: <?= $primaryRgb ?>;
            --ink: #0f172a;
            --ink-muted: #475569;
            --ink-light: #94a3b8;
            --border: #e2e8f0;
            --border-dark: #cbd5e1;
            --bg-subtle: #f8fafc;
            --bg-zebra: #f1f5f9;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            color: var(--ink);
            background-color: #cbd5e1;
            padding: 30px 15px;
            font-size: 13px;
            line-height: 1.45;
            -webkit-font-smoothing: antialiased;
        }

        /* ── A4 Page Sheet Canvas (Screen Preview) ───────────────────────── */
        .page-sheet {
            background: #ffffff;
            width: 210mm;
            min-height: 297mm;
            margin: 0 auto 30px auto;
            padding: 20mm 20mm 22mm 20mm;
            border-radius: 4px;
            box-shadow: 0 12px 36px rgba(0, 0, 0, 0.18);
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        /* ── Action Toolbar (Hidden during print) ────────────────────────── */
        .action-toolbar {
            position: sticky;
            top: 15px;
            z-index: 1000;
            max-width: 210mm;
            margin: 0 auto 20px auto;
            background: rgba(15, 23, 42, 0.94);
            backdrop-filter: blur(12px);
            border-radius: 14px;
            padding: 12px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 10px 25px rgba(0,0,0,0.25);
            color: #ffffff;
        }

        .toolbar-info {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 0.9rem;
            font-weight: 600;
        }

        .toolbar-pill {
            background: rgba(255, 255, 255, 0.15);
            padding: 3px 10px;
            border-radius: 999px;
            font-size: 0.78rem;
            letter-spacing: 0.03em;
        }

        .toolbar-actions {
            display: flex;
            gap: 10px;
        }

        .btn-tool {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 0.85rem;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
            border: none;
            transition: all 0.15s ease;
        }

        .btn-tool-primary {
            background: var(--primary);
            color: #ffffff;
            box-shadow: 0 4px 12px rgba(var(--primary-rgb), 0.4);
        }

        .btn-tool-primary:hover {
            opacity: 0.92;
            transform: translateY(-1px);
        }

        .btn-tool-secondary {
            background: rgba(255, 255, 255, 0.12);
            color: #ffffff;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .btn-tool-secondary:hover {
            background: rgba(255, 255, 255, 0.22);
        }

        /* ── Official Letterhead ─────────────────────────────────────────── */
        .letterhead-header {
            border-bottom: 2px solid var(--ink);
            padding-bottom: 14px;
            margin-bottom: 18px;
        }

        .letterhead-top-row {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            margin-bottom: 12px;
        }

        .letterhead-brand {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .letterhead-logo-img {
            max-height: 48px;
            max-width: 160px;
            object-fit: contain;
            border-radius: 8px;
        }

        .letterhead-fallback-logo {
            width: 44px;
            height: 44px;
            border-radius: 10px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            font-weight: 800;
            font-family: serif;
        }

        .letterhead-company-title {
            font-size: 22px;
            font-weight: 900;
            letter-spacing: -0.02em;
            color: var(--ink);
            line-height: 1.1;
        }

        .letterhead-company-tagline {
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--primary);
            margin-top: 2px;
        }

        .letterhead-doc-meta {
            text-align: right;
            font-size: 11px;
            color: var(--ink-muted);
            line-height: 1.4;
        }

        .letterhead-doc-title {
            font-size: 15px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: var(--ink);
            margin-bottom: 3px;
        }

        .letterhead-accent-strip {
            height: 4px;
            width: 100%;
            background: linear-gradient(90deg, var(--primary) 0%, var(--secondary) 100%);
            border-radius: 2px;
        }

        /* ── Event & Overview Summary Card ──────────────────────────────── */
        .summary-card {
            background-color: var(--bg-subtle);
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 14px 18px;
            margin-bottom: 20px;
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 16px;
        }

        .summary-main h2 {
            font-size: 17px;
            font-weight: 800;
            color: var(--ink);
            margin-bottom: 6px;
            letter-spacing: -0.01em;
        }

        .summary-details {
            font-size: 11.5px;
            color: var(--ink-muted);
            display: flex;
            flex-direction: column;
            gap: 3px;
        }

        .summary-details strong {
            color: var(--ink);
        }

        .summary-stats-box {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
        }

        .stat-badge {
            background: #ffffff;
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 8px 10px;
            text-align: center;
        }

        .stat-badge-val {
            font-size: 16px;
            font-weight: 800;
            color: var(--primary);
            line-height: 1.1;
        }

        .stat-badge-lbl {
            font-size: 9.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--ink-muted);
            margin-top: 3px;
        }

        /* ── Roster Table ───────────────────────────────────────────────── */
        .table-wrap {
            margin-bottom: 20px;
            flex-grow: 1;
        }

        table.manifest-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
            text-align: left;
        }

        table.manifest-table th {
            background-color: var(--ink);
            color: #ffffff;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 9.5px;
            letter-spacing: 0.05em;
            padding: 7px 8px;
            border: 1px solid var(--ink);
        }

        table.manifest-table td {
            padding: 6px 8px;
            border-bottom: 1px solid var(--border);
            border-right: 1px solid var(--border);
            border-left: 1px solid var(--border);
            vertical-align: middle;
            color: var(--ink);
        }

        table.manifest-table tbody tr:nth-child(even) {
            background-color: var(--bg-subtle);
        }

        .code-cell {
            font-family: Consolas, Monaco, "Courier New", monospace;
            font-weight: 700;
            font-size: 10.5px;
            color: var(--primary);
            letter-spacing: 0.04em;
        }

        .check-pill {
            display: inline-block;
            padding: 2px 7px;
            border-radius: 4px;
            font-size: 9.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }

        .check-pill.checked {
            background: rgba(16, 185, 129, 0.15);
            color: #047857;
            border: 1px solid rgba(16, 185, 129, 0.4);
        }

        .check-pill.pending {
            background: rgba(245, 158, 11, 0.12);
            color: #b45309;
            border: 1px solid rgba(245, 158, 11, 0.35);
        }

        .status-badge {
            font-weight: 600;
            font-size: 10px;
            color: var(--ink-muted);
        }

        /* ── Official Gate & Security Verification Sign-off ─────────────── */
        .signoff-section {
            border-top: 1.5px solid var(--border-dark);
            padding-top: 14px;
            margin-top: 20px;
            page-break-inside: avoid;
        }

        .signoff-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 20px;
            margin-bottom: 14px;
        }

        .signoff-box {
            border-top: 1px dashed var(--border-dark);
            padding-top: 6px;
            font-size: 10px;
            color: var(--ink-muted);
        }

        .signoff-box strong {
            color: var(--ink);
            display: block;
            margin-bottom: 2px;
        }

        .letterhead-footer-note {
            font-size: 9px;
            color: var(--ink-light);
            text-align: center;
            border-top: 1px solid var(--border);
            padding-top: 8px;
            line-height: 1.4;
        }

        /* ── Print Media Format (A4 Strict Layout) ───────────────────────── */
        @media print {
            body {
                background: #ffffff !important;
                padding: 0 !important;
                margin: 0 !important;
                font-size: 10.5pt !important;
            }

            .action-toolbar {
                display: none !important;
            }

            .page-sheet {
                box-shadow: none !important;
                margin: 0 !important;
                width: 100% !important;
                min-height: auto !important;
                padding: 0 !important;
                border-radius: 0 !important;
            }

            @page {
                size: A4 portrait;
                margin: 12mm 15mm 15mm 15mm;
            }

            table.manifest-table th {
                background-color: #0f172a !important;
                color: #ffffff !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .letterhead-accent-strip {
                background: <?= $primaryHex ?> !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .stat-badge {
                border: 1px solid #cbd5e1 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .check-pill.checked {
                background-color: #d1fae5 !important;
                color: #047857 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .check-pill.pending {
                background-color: #fef3c7 !important;
                color: #b45309 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            tr {
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>

    <!-- On-Screen Action Toolbar -->
    <aside class="action-toolbar no-print" aria-label="Manifest Actions">
        <div class="toolbar-info">
            <span>📄 Official Admission Manifest</span>
            <span class="toolbar-pill"><?= $totalTickets ?> Total Records</span>
            <?php if ($isDetailedEventMode): ?>
                <span class="toolbar-pill" style="background:rgba(16, 185, 129, 0.25); color:#34d399;">
                    <?= $totalCheckedIn ?> (<?= $checkInRate ?>%) Checked In
                </span>
            <?php endif; ?>
        </div>
        <div class="toolbar-actions">
            <button onclick="window.print()" class="btn-tool btn-tool-primary">
                🖨️ Print / Save as PDF
            </button>
            <?php if ($isDetailedEventMode): ?>
                <a href="{{ route('host/event/management/' . $event->id . '/export') }}" class="btn-tool btn-tool-secondary">
                    📥 Download CSV
                </a>
            <?php else: ?>
                <a href="{{ route('admin/attendees/export') }}" class="btn-tool btn-tool-secondary">
                    📥 Download CSV
                </a>
            <?php endif; ?>
            <button onclick="window.close()" class="btn-tool btn-tool-secondary">
                ✕ Close
            </button>
        </div>
    </aside>

    <!-- A4 Document Canvas -->
    <main class="page-sheet" role="document">
        <div>
            <!-- Letterhead Header -->
            <header class="letterhead-header">
                <div class="letterhead-top-row">
                    <div class="letterhead-brand">
                        <?php if (!empty($siteLogo)): ?>
                            <img src="<?= e(asset($siteLogo)) ?>" alt="<?= e($siteTitle) ?>" class="letterhead-logo-img">
                        <?php else: ?>
                            <div class="letterhead-fallback-logo"><?= strtoupper(substr($siteTitle, 0, 1)) ?></div>
                        <?php endif; ?>
                        <div>
                            <div class="letterhead-company-title"><?= e($siteTitle) ?></div>
                            <div class="letterhead-company-tagline">Event Management &amp; Official Ticketing</div>
                        </div>
                    </div>
                    <div class="letterhead-doc-meta">
                        <div class="letterhead-doc-title">Official Attendee Manifest</div>
                        <div><strong>Doc Ref:</strong> <?= e($manifestRef) ?></div>
                        <div><strong>Generated:</strong> <?= $generationTime ?></div>
                        <div><strong>Platform:</strong> <?= e($appUrl) ?></div>
                    </div>
                </div>
                <div class="letterhead-accent-strip"></div>
            </header>

            <!-- Event & Operations Summary -->
            <section class="summary-card">
                <div class="summary-main">
                    <?php if ($isDetailedEventMode): ?>
                        <h2><?= e($event->name) ?></h2>
                        <div class="summary-details">
                            <div><strong>Host / Organizer:</strong> <?= e($host['name'] ?? ($organizerName ?? 'Event Host')) ?> (<?= e($host['email'] ?? ($organizerEmail ?? $supportEmail)) ?>)</div>
                            <div><strong>Venue / Location:</strong> <?= e(!empty($event->location) ? $event->location : 'Online / Digital Stream') ?></div>
                            <div><strong>Event Schedule:</strong> <?= date('l, F j, Y', strtotime($event->start_date ?? $event->date ?? 'now')) ?><?= !empty($event->start_time) ? ' at ' . date('g:i A', strtotime($event->start_time)) : '' ?></div>
                        </div>
                    <?php else: ?>
                        <h2>Master Attendee Directory Manifest</h2>
                        <div class="summary-details">
                            <div><strong>Scope:</strong> Filtered Cross-Event Attendee Audit Directory</div>
                            <div><strong>Exported By:</strong> Platform Administration</div>
                            <div><strong>Support Contact:</strong> <?= e($supportEmail) ?></div>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="summary-stats-box">
                    <div class="stat-badge">
                        <div class="stat-badge-val"><?= $totalTickets ?></div>
                        <div class="stat-badge-lbl">Total Passes</div>
                    </div>
                    <?php if ($isDetailedEventMode): ?>
                        <div class="stat-badge">
                            <div class="stat-badge-val" style="color:#059669;"><?= $totalCheckedIn ?></div>
                            <div class="stat-badge-lbl">Checked In</div>
                        </div>
                        <div class="stat-badge">
                            <div class="stat-badge-val" style="color:#d97706;"><?= $totalTickets - $totalCheckedIn ?></div>
                            <div class="stat-badge-lbl">Pending Entry</div>
                        </div>
                        <div class="stat-badge">
                            <div class="stat-badge-val" style="color:var(--ink);"><?= format_price($totalRevenue) ?></div>
                            <div class="stat-badge-lbl">Gross Value</div>
                        </div>
                    <?php else: ?>
                        <div class="stat-badge">
                            <div class="stat-badge-val" style="color:var(--ink);"><?= format_price($totalRevenue) ?></div>
                            <div class="stat-badge-lbl">Total Spent</div>
                        </div>
                    <?php endif; ?>
                </div>
            </section>

            <!-- Roster Table -->
            <section class="table-wrap">
                <table class="manifest-table">
                    <thead>
                        <tr>
                            <th style="width:32px; text-align:center;">#</th>
                            <th style="width:90px;">Pass Ref</th>
                            <th>Attendee Name</th>
                            <th>Email Address</th>
                            <th style="width:110px;">Tier / Category</th>
                            <th style="width:75px; text-align:right;">Amount</th>
                            <th style="width:95px; text-align:center;">Check-In</th>
                            <th style="width:85px;">Purchased</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($attendees)): ?>
                            <tr>
                                <td colspan="8" style="text-align:center; padding: 25px; color:#64748b;">
                                    No attendee records found matching the specified parameters.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($attendees as $idx => $row): ?>
                                <tr>
                                    <td style="text-align:center; font-weight:700; color:var(--ink-muted);">
                                        <?= $idx + 1 ?>
                                    </td>
                                    <td class="code-cell">
                                        <?= e($row['id'] ?? ('REF-' . str_pad($idx + 1, 4, '0', STR_PAD_LEFT))) ?>
                                    </td>
                                    <td>
                                        <strong style="color:var(--ink);"><?= e($row['attendee_name'] ?? 'Guest Attendee') ?></strong>
                                    </td>
                                    <td style="color:var(--ink-muted);">
                                        <?= e($row['attendee_email'] ?? 'N/A') ?>
                                    </td>
                                    <td>
                                        <span class="status-badge"><?= e($row['ticket_tier'] ?? ($row['attendee_type'] ?? 'General Admission')) ?></span>
                                    </td>
                                    <td style="text-align:right; font-weight:700;">
                                        <?= isset($row['price']) ? format_price((float)$row['price']) : format_price((float)($row['total_spent'] ?? 0)) ?>
                                    </td>
                                    <td style="text-align:center;">
                                        <?php if (!empty($row['checked_in'])): ?>
                                            <span class="check-pill checked">✓ In<?= !empty($row['checked_in_at']) ? ' ' . date('H:i', strtotime($row['checked_in_at'])) : '' ?></span>
                                        <?php else: ?>
                                            <span class="check-pill pending">Pending</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="font-size:10px; color:var(--ink-muted);">
                                        <?= !empty($row['purchase_date']) ? date('M j, Y', strtotime($row['purchase_date'])) : (!empty($row['last_purchase_date']) ? date('M j, Y', strtotime($row['last_purchase_date'])) : '—') ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </section>
        </div>

        <!-- Official Sign-off and Gate Security Footer -->
        <footer class="signoff-section">
            <div class="signoff-grid">
                <div class="signoff-box">
                    <strong>Gate Check-In Supervisor</strong>
                    Signature &amp; Date
                </div>
                <div class="signoff-box">
                    <strong>Event Organizer / Host</strong>
                    Signature &amp; Date
                </div>
                <div class="signoff-box">
                    <strong>Audit Verification Status</strong>
                    <span>Validated Official Manifest &bull; Sealed</span>
                </div>
            </div>
            <div class="letterhead-footer-note">
                CONFIDENTIAL: This admission manifest is an official operational document generated by <?= e($siteTitle) ?> (<?= e($appUrl) ?>).
                Contains protected attendee personal data. Authorized for physical event gate administration, capacity control, and financial auditing only.
            </div>
        </footer>
    </main>

    <script>
        // Auto-trigger print dialog if requested
        if (window.location.search.indexOf('autoprint=1') !== -1) {
            window.addEventListener('load', () => {
                setTimeout(() => window.print(), 350);
            });
        }
    </script>
</body>
</html>
