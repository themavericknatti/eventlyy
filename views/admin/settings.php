<?php

/**
 * Admin Settings
 *
 * This file contains the HTML markup for the admin settings page of the application.
 *
 * @package Evently
 * @subpackage Views/Admin
 * @author Your Name <Celio Natti>
 * @license MIT License
 * @version 1.0.0
 */

?>

@extends('layouts/admin')

@section('title')
Admin Settings - Manage Site & System Configurations
@endsection

@section('content')
<?php
$pendingDomainsCount = 0;
try {
    $pendingDomainsCount = (int)\Ace\Application::$app->db->pdo->query("SELECT COUNT(*) FROM event_domain_requests WHERE status = 'pending'")->fetchColumn();
} catch (\Throwable $e) {}
?>

<section class="page-hero" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:1.25rem;">
    <div>
        <span class="panel-badge">Admin — Settings</span>
        <h1 style="margin:0.25rem 0;">Admin Settings</h1>
        <p class="lede" style="margin:0;">Configure site branding, logos, social links, payment gateways, timezone, and maintenance toggles.</p>
    </div>
    <div>
        <a href="{{ route('admin/overview') }}" class="admin-action-btn" style="padding:0.6rem 1.15rem; font-size:0.88rem; border-radius:12px; font-weight:600; text-decoration:none; display:inline-flex; align-items:center; gap:0.45rem; background:rgba(var(--text), 0.04); border:1px solid rgba(var(--muted),0.2);">
            <span>←</span> Back to Dashboard
        </a>
    </div>
</section>

<!-- Quick Action Shortcuts: Events, Domains, Attendees & Communications -->
<div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(280px, 1fr)); gap:1.25rem; margin-bottom: 1.5rem;">
    <!-- Published Events Hub Shortcut -->
    <div class="form-card" style="border-radius:22px; padding:1.5rem 1.75rem; background: linear-gradient(135deg, rgba(2, 132, 199, 0.08) 0%, rgba(16, 185, 129, 0.08) 100%), rgb(var(--card)); border: 1px solid rgba(2, 132, 199, 0.25); display:flex; flex-direction:column; justify-content:space-between; gap:1rem;">
        <div>
            <span style="background:rgba(2, 132, 199, 0.15); color:#0284c7; font-size:0.75rem; font-weight:800; padding:0.25rem 0.65rem; border-radius:20px; text-transform:uppercase; letter-spacing:0.5px;">Platform Events</span>
            <h2 style="margin:0.35rem 0 0.25rem 0; font-size:1.2rem; color:rgb(var(--text));">🎪 Published Events Hub</h2>
            <p class="section-note" style="margin:0; font-size:0.86rem;">Inspect all organizer events, toggle featured status, monitor ticket capacities, and view audit reports.</p>
        </div>
        <div>
            <a href="{{ route('admin/events') }}" class="admin-action-btn primary" style="background:#0284c7; border-color:#0284c7; text-decoration:none; padding:0.6rem 1.2rem; font-weight:700; display:inline-flex; align-items:center; justify-content:center; gap:0.4rem; border-radius:12px; font-size:0.88rem;">
                Manage All Events →
            </a>
        </div>
    </div>

    <!-- Custom Domains & API Shortcut -->
    <div class="form-card" style="border-radius:22px; padding:1.5rem 1.75rem; background: linear-gradient(135deg, rgba(245, 158, 11, 0.08) 0%, rgba(139, 92, 246, 0.08) 100%), rgb(var(--card)); border: 1px solid rgba(245, 158, 11, 0.25); display:flex; flex-direction:column; justify-content:space-between; gap:1rem;">
        <div>
            <?php $isCustomDomainLive = !empty($settings['custom_domain_enabled']) && $settings['custom_domain_enabled'] === '1'; ?>
            <div style="display:flex; justify-content:space-between; align-items:center; gap:0.5rem; flex-wrap:wrap;">
                <div style="display:flex; align-items:center; gap:0.4rem;">
                    <span style="background:rgba(245, 158, 11, 0.15); color:#d97706; font-size:0.75rem; font-weight:800; padding:0.25rem 0.65rem; border-radius:20px; text-transform:uppercase; letter-spacing:0.5px;">Integrations</span>
                    <?php if ($isCustomDomainLive): ?>
                        <span style="background:rgba(16, 185, 129, 0.15); color:#10b981; font-size:0.72rem; font-weight:800; border-radius:999px; padding:0.15rem 0.55rem; border:1px solid rgba(16,185,129,0.3);">● Live for Hosts</span>
                    <?php else: ?>
                        <span style="background:rgba(239, 68, 68, 0.12); color:#ef4444; font-size:0.72rem; font-weight:800; border-radius:999px; padding:0.15rem 0.55rem; border:1px solid rgba(239,68,68,0.25);">🔒 Disabled for Hosts</span>
                    <?php endif; ?>
                </div>
                <?php if ($pendingDomainsCount > 0): ?>
                    <span style="background:#f59e0b; color:#fff; font-size:0.72rem; font-weight:800; border-radius:999px; padding:0.15rem 0.55rem;"><?= $pendingDomainsCount ?> Pending</span>
                <?php endif; ?>
            </div>
            <h2 style="margin:0.35rem 0 0.25rem 0; font-size:1.2rem; color:rgb(var(--text));">🌐 Custom Domains &amp; API</h2>
            <p class="section-note" style="margin:0; font-size:0.86rem;">Review host domain connection requests, configure CNAME DNS routing, and activate headless ticketing.</p>
        </div>
        <div>
            <a href="{{ route('admin/domain-requests') }}" class="admin-action-btn primary" style="background:#d97706; border-color:#d97706; text-decoration:none; padding:0.6rem 1.2rem; font-weight:700; display:inline-flex; align-items:center; justify-content:center; gap:0.4rem; border-radius:12px; font-size:0.88rem;">
                Review Domain Requests →
            </a>
        </div>
    </div>

    <!-- Attendee Contact Directory Shortcut -->
    <div class="form-card" style="border-radius:22px; padding:1.5rem 1.75rem; background: linear-gradient(135deg, rgba(16, 185, 129, 0.08) 0%, rgba(59, 130, 246, 0.08) 100%), rgb(var(--card)); border: 1px solid rgba(16, 185, 129, 0.25); display:flex; flex-direction:column; justify-content:space-between; gap:1rem;">
        <div>
            <span style="background:rgba(16, 185, 129, 0.15); color:#10b981; font-size:0.75rem; font-weight:800; padding:0.25rem 0.65rem; border-radius:20px; text-transform:uppercase; letter-spacing:0.5px;">Customer Records</span>
            <h2 style="margin:0.35rem 0 0.25rem 0; font-size:1.2rem; color:rgb(var(--text));">📇 Attendee Contact Directory</h2>
            <p class="section-note" style="margin:0; font-size:0.86rem;">View deduplicated contact records, purchase histories, and export CSV files for all registered and guest ticket buyers.</p>
        </div>
        <div>
            <a href="{{ route('admin/attendees') }}" class="admin-action-btn primary" style="background:#10b981; border-color:#10b981; text-decoration:none; padding:0.6rem 1.2rem; font-weight:700; display:inline-flex; align-items:center; justify-content:center; gap:0.4rem; border-radius:12px; font-size:0.88rem;">
                Open Attendee Directory →
            </a>
        </div>
    </div>

    <!-- Email Communications Center Shortcut -->
    <div class="form-card" style="border-radius:22px; padding:1.5rem 1.75rem; background: linear-gradient(135deg, rgba(99, 102, 241, 0.08) 0%, rgba(236, 72, 153, 0.08) 100%), rgb(var(--card)); border: 1px solid rgba(99, 102, 241, 0.25); display:flex; flex-direction:column; justify-content:space-between; gap:1rem;">
        <div>
            <span style="background:rgba(99, 102, 241, 0.15); color:#6366f1; font-size:0.75rem; font-weight:800; padding:0.25rem 0.65rem; border-radius:20px; text-transform:uppercase; letter-spacing:0.5px;">Broadcast Mailer</span>
            <h2 style="margin:0.35rem 0 0.25rem 0; font-size:1.2rem; color:rgb(var(--text));">📨 Communications Center</h2>
            <p class="section-note" style="margin:0; font-size:0.86rem;">Compose and dispatch general broadcast emails or targeted messages to all hosts, individual hosts, or platform users.</p>
        </div>
        <div>
            <a href="{{ route('admin/communications') }}" class="admin-action-btn primary" style="background:#6366f1; border-color:#6366f1; text-decoration:none; padding:0.6rem 1.2rem; font-weight:700; display:inline-flex; align-items:center; justify-content:center; gap:0.4rem; border-radius:12px; font-size:0.88rem;">
                Open Communications Center →
            </a>
        </div>
    </div>

    <!-- System Cache & View Optimization Shortcut -->
    <div class="form-card" style="border-radius:22px; padding:1.5rem 1.75rem; background: linear-gradient(135deg, rgba(239, 68, 68, 0.08) 0%, rgba(245, 158, 11, 0.08) 100%), rgb(var(--card)); border: 1px solid rgba(239, 68, 68, 0.25); display:flex; flex-direction:column; justify-content:space-between; gap:1rem;">
        <div>
            <span style="background:rgba(239, 68, 68, 0.15); color:#ef4444; font-size:0.75rem; font-weight:800; padding:0.25rem 0.65rem; border-radius:20px; text-transform:uppercase; letter-spacing:0.5px;">Maintenance</span>
            <h2 style="margin:0.35rem 0 0.25rem 0; font-size:1.2rem; color:rgb(var(--text));">⚡ View Cache &amp; Storage</h2>
            <p class="section-note" style="margin:0; font-size:0.86rem;">Instantly purge all compiled Blade view templates across the application. Useful when new design, auth, or layout updates are deployed.</p>
        </div>
        <div>
            <form method="POST" action="{{ route('admin/cache/clear') }}" style="margin:0;">
                <?= csrf_field() ?>
                <button type="submit" class="admin-action-btn primary" style="background:#ef4444; border-color:#ef4444; padding:0.6rem 1.2rem; font-weight:700; display:inline-flex; align-items:center; justify-content:center; gap:0.4rem; border-radius:12px; font-size:0.88rem; cursor:pointer;" onclick="return confirm('Purge all compiled view cache files now?');">
                    Purge View Cache Now 🧹
                </button>
            </form>
        </div>
    </div>
</div>

<form method="POST" action="{{ route('admin/settings') }}" enctype="multipart/form-data" class="admin-settings-layout">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="update_settings" />

    <!-- General Settings & Site Branding -->
    <div class="form-card" style="border-radius:22px; padding:1.5rem 1.75rem;">
        <h2>Site Identity &amp; Branding</h2>
        <p class="section-note" style="margin-bottom:1.25rem;">Customize your platform brand name, tagline, and uploaded media assets.</p>

        <div style="display:grid; gap:1rem;">
            <label>Site Title / Brand Name
                <input type="text" name="site_title" value="<?= e($settings['site_title'] ?? '') ?>" placeholder="e.g. Eventlyy" required>
            </label>

            <label>Site Description / Tagline
                <input type="text" name="site_description" value="<?= e($settings['site_description'] ?? '') ?>" placeholder="e.g. Discover and book amazing events near you.">
            </label>

            <label>Support Email Address
                <input type="email" name="support_email" value="<?= e($settings['support_email'] ?? '') ?>" required>
            </label>

            <label>Maintenance Mode
                <select name="maintenance_mode">
                    <option value="no" <?= ($settings['maintenance_mode'] ?? '') === 'no' ? 'selected' : '' ?>>Disabled (Website Live)</option>
                    <option value="yes" <?= ($settings['maintenance_mode'] ?? '') === 'yes' ? 'selected' : '' ?>>Enabled (Maintenance Splash Screen)</option>
                </select>
            </label>
        </div>

        <!-- Logo & Favicon Upload Cards -->
        <div class="admin-grid-2" style="margin-top:1.5rem; padding-top:1.25rem; border-top:1px solid var(--border-color, rgba(255,255,255,0.08));">
            <!-- Logo Upload -->
            <div style="background:var(--card-bg, rgba(255,255,255,0.03)); border:1px dashed var(--border-color, rgba(255,255,255,0.15)); border-radius:16px; padding:1.25rem; box-sizing:border-box;">
                <h4 style="margin:0 0 0.5rem 0; font-size:0.95rem; font-weight:700;">Site Logo (Max 2MB)</h4>
                <p style="font-size:0.8rem; color:var(--text-muted, #888); margin-bottom:0.75rem;">Supported formats: PNG, JPG, WEBP, SVG, GIF.</p>
                
                <?php if (!empty($settings['site_logo'])): ?>
                    <div style="display:flex; align-items:center; gap:1rem; margin-bottom:0.75rem; background:rgba(0,0,0,0.2); padding:0.5rem 0.75rem; border-radius:10px; flex-wrap:wrap;">
                        <img src="<?= e(asset($settings['site_logo'])) ?>" alt="Current Logo" style="max-height:36px; max-width:120px; object-fit:contain;" />
                        <label style="margin:0; cursor:pointer; font-size:0.8rem; color:#ef4444; display:flex; align-items:center; gap:0.25rem;">
                            <input type="checkbox" name="remove_logo" value="1" style="width:auto; margin:0;"> Remove
                        </label>
                    </div>
                <?php endif; ?>

                <input type="file" name="site_logo" accept="image/png,image/jpeg,image/webp,image/svg+xml,image/gif" style="font-size:0.85rem; width:100%; box-sizing:border-box;" />
            </div>

            <!-- Favicon Upload -->
            <div style="background:var(--card-bg, rgba(255,255,255,0.03)); border:1px dashed var(--border-color, rgba(255,255,255,0.15)); border-radius:16px; padding:1.25rem; box-sizing:border-box;">
                <h4 style="margin:0 0 0.5rem 0; font-size:0.95rem; font-weight:700;">Site Favicon (Max 2MB)</h4>
                <p style="font-size:0.8rem; color:var(--text-muted, #888); margin-bottom:0.75rem;">Supported formats: ICO, PNG, SVG.</p>
                
                <?php if (!empty($settings['site_favicon'])): ?>
                    <div style="display:flex; align-items:center; gap:1rem; margin-bottom:0.75rem; background:rgba(0,0,0,0.2); padding:0.5rem 0.75rem; border-radius:10px; flex-wrap:wrap;">
                        <img src="<?= e(asset($settings['site_favicon'])) ?>" alt="Current Favicon" style="width:24px; height:24px; object-fit:contain;" />
                        <label style="margin:0; cursor:pointer; font-size:0.8rem; color:#ef4444; display:flex; align-items:center; gap:0.25rem;">
                            <input type="checkbox" name="remove_favicon" value="1" style="width:auto; margin:0;"> Remove
                        </label>
                    </div>
                <?php endif; ?>

                <input type="file" name="site_favicon" accept="image/x-icon,image/vnd.microsoft.icon,image/png,image/svg+xml" style="font-size:0.85rem; width:100%; box-sizing:border-box;" />
            </div>
    </div>

    <!-- ═══ Platform Announcement & Holiday Celebration Modal ═══ -->
    <div class="form-card" style="border-radius:22px; padding:1.5rem 1.75rem; border:1px solid rgba(var(--brand), 0.25); background: linear-gradient(135deg, rgba(var(--brand), 0.04) 0%, rgba(245, 158, 11, 0.04) 100%), rgb(var(--card));">
        <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:0.75rem; margin-bottom:0.75rem;">
            <div>
                <span style="background:rgba(var(--brand), 0.15); color:rgb(var(--brand)); font-size:0.75rem; font-weight:800; padding:0.25rem 0.65rem; border-radius:20px; text-transform:uppercase; letter-spacing:0.5px;">Home Page Modal</span>
                <h2 style="margin:0.35rem 0 0.25rem 0; font-size:1.25rem;">📣 Platform Announcement &amp; Celebration Popup</h2>
            </div>
            <div>
                <?php $isAnnLive = !empty($settings['announcement_enabled']) && $settings['announcement_enabled'] === '1'; ?>
                <span id="ann-status-pill" style="font-size:0.8rem; font-weight:800; border-radius:999px; padding:0.3rem 0.8rem; display:inline-flex; align-items:center; gap:0.4rem; <?= $isAnnLive ? 'background:rgba(16,185,129,0.15); color:#10b981; border:1px solid rgba(16,185,129,0.3);' : 'background:rgba(var(--muted),0.12); color:rgb(var(--muted)); border:1px solid rgba(var(--muted),0.2);' ?>">
                    <span style="width:8px; height:8px; border-radius:50%; background:<?= $isAnnLive ? '#10b981' : 'rgb(var(--muted))' ?>; display:inline-block;"></span>
                    <?= $isAnnLive ? 'Active on Platform' : 'Currently Inactive' ?>
                </span>
            </div>
        </div>
        <p class="section-note" style="margin-bottom:1.5rem;">
            Display a centered, high-impact celebration modal card at the home page to inform all users about holidays, anniversaries, promotions, or platform notices. Visitors can read the update, click an action link to proceed, or clear the popup to continue.
        </p>

        <div style="display:grid; gap:1.25rem;">
            <!-- Active Toggle & Scope Grid -->
            <div class="admin-grid-2">
                <div style="background:rgba(var(--muted), 0.04); border:1px solid rgba(var(--muted), 0.12); border-radius:14px; padding:1rem; display:flex; align-items:center; justify-content:space-between; gap:1rem;">
                    <div>
                        <strong style="display:block; font-size:0.95rem; margin-bottom:0.2rem;">Enable Announcement Popup</strong>
                        <span style="font-size:0.8rem; color:rgb(var(--muted));">Show the notification card to visitors</span>
                    </div>
                    <label style="position:relative; display:inline-block; width:54px; height:30px; margin:0; cursor:pointer;" for="ann-toggle-checkbox">
                        <input type="hidden" name="announcement_enabled" value="0">
                        <input type="checkbox" name="announcement_enabled" id="ann-toggle-checkbox" value="1" <?= $isAnnLive ? 'checked' : '' ?> style="position:absolute; width:100%; height:100%; top:0; left:0; opacity:0; margin:0; cursor:pointer; z-index:2;">
                        <span style="position:absolute; inset:0; background:<?= $isAnnLive ? 'rgb(var(--brand))' : 'rgba(var(--muted),0.3)' ?>; transition:background .25s ease; border-radius:30px; pointer-events:none;" id="ann-toggle-slider">
                            <span style="position:absolute; height:24px; width:24px; top:3px; left:<?= $isAnnLive ? '27px' : '3px' ?>; background:white; transition:left .25s cubic-bezier(0.16, 1, 0.3, 1); border-radius:50%; box-shadow:0 2px 6px rgba(0,0,0,0.25);" id="ann-toggle-knob"></span>
                        </span>
                    </label>
                </div>

                <label>Display Scope
                    <select name="announcement_location">
                        <option value="home" <?= ($settings['announcement_location'] ?? 'home') === 'home' ? 'selected' : '' ?>>Home Page Only (Recommended)</option>
                        <option value="sitewide" <?= ($settings['announcement_location'] ?? '') === 'sitewide' ? 'selected' : '' ?>>Site-Wide (All Public Pages)</option>
                    </select>
                </label>
            </div>

            <!-- Badge & Headline Title -->
            <div class="admin-grid-2">
                <label>Badge / Eyebrow Tag (Optional)
                    <input type="text" name="announcement_badge" value="<?= e($settings['announcement_badge'] ?? '') ?>" placeholder="e.g. 🎉 Special Announcement (Leave blank to hide)">
                </label>

                <label>Headline Title (Optional if banner image provided)
                    <input type="text" name="announcement_title" value="<?= e($settings['announcement_title'] ?? '') ?>" placeholder="e.g. Celebrating 5 Years of Eventlyy! 🎉 (Leave blank for image-only)">
                </label>
            </div>

            <!-- Body Message -->
            <label>Announcement Body Message (Optional)
                <textarea name="announcement_message" rows="4" style="width:100%; box-sizing:border-box; border-radius:14px; padding:0.85rem 1rem; font-family:inherit; font-size:0.92rem; line-height:1.5; resize:vertical;" placeholder="Write your full celebration or announcement message here. Leave blank if using a visual flyer/poster only..."><?= e($settings['announcement_message'] ?? '') ?></textarea>
            </label>

            <!-- Media / Image Upload -->
            <div style="background:rgba(var(--muted), 0.03); border:1px dashed rgba(var(--muted), 0.2); border-radius:16px; padding:1.25rem;">
                <h4 style="margin:0 0 0.35rem 0; font-size:0.95rem; font-weight:700;">Announcement Banner Image (Optional, Max 3MB)</h4>
                <p style="font-size:0.8rem; color:rgb(var(--muted)); margin:0 0 0.85rem 0;">Upload a festive graphic, celebration poster, or holiday banner (PNG, JPG, WEBP, GIF, SVG). If only an image is provided without text, it displays as a clean visual flyer.</p>

                <?php if (!empty($settings['announcement_image'])): 
                    $annImgSrc = str_starts_with($settings['announcement_image'], 'http') ? $settings['announcement_image'] : asset($settings['announcement_image']);
                ?>
                    <div style="display:flex; align-items:center; gap:1rem; margin-bottom:0.85rem; background:rgba(0,0,0,0.15); padding:0.6rem 0.85rem; border-radius:12px; flex-wrap:wrap;">
                        <img src="<?= e($annImgSrc) ?>" alt="Announcement Banner" style="max-height:64px; max-width:140px; border-radius:8px; object-fit:cover;" />
                        <label style="margin:0; cursor:pointer; font-size:0.8rem; color:#ef4444; display:flex; align-items:center; gap:0.35rem;">
                            <input type="checkbox" name="remove_announcement_image" value="1" style="width:auto; margin:0;"> Remove Banner Image
                        </label>
                    </div>
                <?php endif; ?>

                <div class="admin-grid-2">
                    <div>
                        <span style="font-size:0.75rem; font-weight:700; color:rgb(var(--muted)); display:block; margin-bottom:0.25rem;">Upload File:</span>
                        <input type="file" name="announcement_image" accept="image/png,image/jpeg,image/webp,image/svg+xml,image/gif" style="font-size:0.85rem; width:100%; box-sizing:border-box;" />
                    </div>
                    <div>
                        <span style="font-size:0.75rem; font-weight:700; color:rgb(var(--muted)); display:block; margin-bottom:0.25rem;">Or Image URL:</span>
                        <input type="url" name="announcement_image_url" placeholder="https://example.com/banner.jpg" style="font-size:0.85rem; width:100%; box-sizing:border-box;" />
                    </div>
                </div>
            </div>

            <!-- Action Buttons Config -->
            <div class="admin-grid-3">
                <label>Action Button Label (Optional)
                    <input type="text" name="announcement_link_text" value="<?= e($settings['announcement_link_text'] ?? '') ?>" placeholder="e.g. Check It Out → (Leave blank to hide)">
                </label>

                <label>Action Button URL (Optional)
                    <input type="text" name="announcement_link_url" value="<?= e($settings['announcement_link_url'] ?? '') ?>" placeholder="e.g. /events or https://... (Leave blank to hide)">
                </label>

                <label>Cancel / Dismiss Button Label (Optional)
                    <input type="text" name="announcement_dismiss_text" value="<?= e($settings['announcement_dismiss_text'] ?? 'Continue to Site') ?>" placeholder="e.g. Continue to Site (Leave blank to hide)">
                </label>
            </div>

            <!-- Live Preview Trigger -->
            <div style="display:flex; justify-content:flex-start; margin-top:0.5rem;">
                <button type="button" class="admin-action-btn" id="btn-preview-announcement" onclick="window.openAnnouncementPreview(event)" style="padding:0.55rem 1.15rem; font-size:0.85rem; border-radius:10px; font-weight:700; display:inline-flex; align-items:center; gap:0.4rem; background:rgba(var(--brand), 0.12); color:rgb(var(--brand)); border:1px solid rgba(var(--brand), 0.25); cursor:pointer;">
                    👁️ Test / Preview Modal Popup
                </button>
            </div>
        </div>
    </div>

    <!-- Social Media Links -->
    <div class="form-card" style="border-radius:22px; padding:1.5rem 1.75rem;">
        <h2>Social Media Handles &amp; Links</h2>
        <p class="section-note" style="margin-bottom:1.25rem;">Provide your official social media URLs. Icons will render dynamically in the footer for non-empty fields.</p>

        <div class="admin-grid-2">
            <label>Twitter / X URL
                <input type="url" name="social_twitter" value="<?= e($settings['social_twitter'] ?? '') ?>" placeholder="https://twitter.com/yourhandle">
            </label>

            <label>Instagram URL
                <input type="url" name="social_instagram" value="<?= e($settings['social_instagram'] ?? '') ?>" placeholder="https://instagram.com/yourhandle">
            </label>

            <label>Facebook URL
                <input type="url" name="social_facebook" value="<?= e($settings['social_facebook'] ?? '') ?>" placeholder="https://facebook.com/yourpage">
            </label>

            <label>LinkedIn URL
                <input type="url" name="social_linkedin" value="<?= e($settings['social_linkedin'] ?? '') ?>" placeholder="https://linkedin.com/company/yourcompany">
            </label>

            <label>YouTube URL
                <input type="url" name="social_youtube" value="<?= e($settings['social_youtube'] ?? '') ?>" placeholder="https://youtube.com/@yourchannel">
            </label>

            <label>TikTok URL
                <input type="url" name="social_tiktok" value="<?= e($settings['social_tiktok'] ?? '') ?>" placeholder="https://tiktok.com/@yourhandle">
            </label>
        </div>
    </div>

    <!-- SEO & Metadata -->
    <div class="form-card" style="border-radius:22px; padding:1.5rem 1.75rem;">
        <h2>SEO &amp; Search Engine Metadata</h2>
        <p class="section-note" style="margin-bottom:1.25rem;">Default metadata for search engines and social card previews.</p>

        <div style="display:grid; gap:1rem;">
            <label>Meta Description
                <textarea name="meta_description" rows="2" style="width:100%; box-sizing:border-box; border-radius:12px; padding:0.75rem; font-family:inherit;" placeholder="Default search engine description for the platform..."><?= e($settings['meta_description'] ?? '') ?></textarea>
            </label>

            <label>Meta Keywords
                <input type="text" name="meta_keywords" value="<?= e($settings['meta_keywords'] ?? '') ?>" placeholder="events, tickets, concert, booking, live shows">
            </label>
        </div>
    </div>

    <!-- Timezone & Event Lifecycle Configuration -->
    <div class="form-card" style="border-radius:22px; padding:1.5rem 1.75rem;">
        <h2>Timezone &amp; Event Lifecycle</h2>
        <p class="section-note" style="margin-bottom:1.25rem;">Set system timezone and configure the live window duration for event completion. Current server time: <strong><?= date('Y-m-d H:i:s T') ?></strong></p>

        <div class="admin-grid-2">
            <label>System Timezone
                <?php
                    $currentTz = $settings['timezone'] ?? config('timezone', 'Africa/Lagos');
                    $commonTimezones = [
                        'Africa/Lagos' => 'Africa/Lagos (WAT, UTC+1)',
                        'Africa/Johannesburg' => 'Africa/Johannesburg (SAST, UTC+2)',
                        'Africa/Nairobi' => 'Africa/Nairobi (EAT, UTC+3)',
                        'Africa/Cairo' => 'Africa/Cairo (EET, UTC+2)',
                        'Africa/Casablanca' => 'Africa/Casablanca (WET, UTC+0)',
                        'America/New_York' => 'America/New_York (EST, UTC-5)',
                        'America/Chicago' => 'America/Chicago (CST, UTC-6)',
                        'America/Denver' => 'America/Denver (MST, UTC-7)',
                        'America/Los_Angeles' => 'America/Los_Angeles (PST, UTC-8)',
                        'America/Sao_Paulo' => 'America/Sao Paulo (BRT, UTC-3)',
                        'Europe/London' => 'Europe/London (GMT, UTC+0)',
                        'Europe/Paris' => 'Europe/Paris (CET, UTC+1)',
                        'Europe/Berlin' => 'Europe/Berlin (CET, UTC+1)',
                        'Europe/Moscow' => 'Europe/Moscow (MSK, UTC+3)',
                        'Asia/Dubai' => 'Asia/Dubai (GST, UTC+4)',
                        'Asia/Kolkata' => 'Asia/Kolkata (IST, UTC+5:30)',
                        'Asia/Shanghai' => 'Asia/Shanghai (CST, UTC+8)',
                        'Asia/Tokyo' => 'Asia/Tokyo (JST, UTC+9)',
                        'Australia/Sydney' => 'Australia/Sydney (AEST, UTC+10)',
                        'Pacific/Auckland' => 'Pacific/Auckland (NZST, UTC+12)',
                        'UTC' => 'UTC (Coordinated Universal Time)',
                    ];
                ?>
                <select name="timezone" style="width:100%; box-sizing:border-box;">
                    <?php foreach ($commonTimezones as $tz => $label): ?>
                        <option value="<?= e($tz) ?>" <?= $currentTz === $tz ? 'selected' : '' ?>><?= e($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label>Event Live Window Duration (Hours)
                <input type="number" name="event_completion_hours" value="<?= e($settings['event_completion_hours'] ?? '12') ?>" min="1" max="168" step="1" required placeholder="12">
                <span style="font-size:0.78rem; color:var(--text-muted, #888); display:block; margin-top:0.35rem;">Hours after event start time when an event automatically transitions to Completed.</span>
            </label>

            <div style="grid-column: 1 / -1; background:rgba(var(--brand), 0.05); border:1px solid rgba(var(--brand), 0.2); border-radius:14px; padding:1.15rem 1.35rem; margin-top:0.5rem;">
                <label style="display:flex; align-items:center; gap:0.65rem; font-weight:700; cursor:pointer; margin-bottom:0.75rem;">
                    <input type="checkbox" name="auto_payout_enabled" value="1" <?= (!isset($settings['auto_payout_enabled']) || $settings['auto_payout_enabled'] === '1' || $settings['auto_payout_enabled'] === true) ? 'checked' : '' ?> style="width:1.15rem; height:1.15rem; cursor:pointer; accent-color:rgb(var(--brand));">
                    <span>⚡ Auto-Request Host Payouts upon Event Completion</span>
                </label>
                <p style="font-size:0.82rem; color:var(--text-muted, #888); margin-bottom:0.85rem; line-height:1.45;">
                    When enabled, if a host has not manually requested a payout after their event is completed, the system will automatically calculate the net ticket earnings (after platform fee deduction) and generate a pending payout request for the admin to review and disburse.
                </p>
                <div style="max-width:320px;">
                    <label style="font-size:0.85rem; font-weight:600;">Auto-Payout Grace Delay (Minutes)
                        <input type="number" name="auto_payout_delay_minutes" value="<?= e($settings['auto_payout_delay_minutes'] ?? '15') ?>" min="0" max="1440" step="1" placeholder="15" style="margin-top:0.35rem;">
                        <span style="font-size:0.75rem; color:var(--text-muted, #888); display:block; margin-top:0.25rem;">Minutes to wait after event completion before automatically creating the payout request (0 for immediate).</span>
                    </label>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment & Currency Configuration -->
    <div class="form-card" style="border-radius:22px; padding:1.5rem 1.75rem;">
        <h2>Payment &amp; Currency</h2>
        <p class="section-note" style="margin-bottom:1.25rem;">Manage merchant integrations, currency configurations, and exchange rates.</p>

        <?php $enabledGateways = (array)($settings['enabled_gateways'] ?? ['paystack']); ?>
        <div style="margin-bottom: 1.25rem; padding-bottom: 1.25rem; border-bottom: 1px solid var(--border-color, rgba(255,255,255,0.08));">
            <label style="font-weight:700; display:block; margin-bottom:0.5rem;">Active Payment Gateways (Buyer Checkout Options)</label>
            <p style="font-size:0.82rem; color:var(--text-muted, #888); margin-bottom:0.75rem;">Select which payment gateways are enabled for buyers during checkout. Unchecked gateways will be hidden.</p>
            <div class="gateway-checkbox-group">
                <label class="gateway-checkbox-item">
                    <input type="checkbox" name="enabled_gateways[]" value="paystack" <?= in_array('paystack', $enabledGateways) ? 'checked' : '' ?> style="width:auto; margin:0;">
                    <span>🟢 Paystack Gateway</span>
                </label>
                <label class="gateway-checkbox-item">
                    <input type="checkbox" name="enabled_gateways[]" value="stripe" <?= in_array('stripe', $enabledGateways) ? 'checked' : '' ?> style="width:auto; margin:0;">
                    <span>🟣 Stripe Payments</span>
                </label>
                <label class="gateway-checkbox-item">
                    <input type="checkbox" name="enabled_gateways[]" value="flutterwave" <?= in_array('flutterwave', $enabledGateways) ? 'checked' : '' ?> style="width:auto; margin:0;">
                    <span>🟠 Flutterwave Gateway</span>
                </label>
            </div>
        </div>

        <!-- Payment Gateway API Keys Sub-Section -->
        <div style="margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid var(--border-color, rgba(255,255,255,0.08));">
            <div style="display:flex; align-items:center; gap:0.65rem; margin-bottom:0.5rem;">
                <h3 style="margin:0; font-size:1.1rem; font-weight:800;">🔐 Payment Gateway API Keys</h3>
            </div>
            <p style="font-size:0.82rem; color:var(--text-muted, #888); margin-bottom:1.25rem;">Enter your API keys for each payment gateway below. Keys saved here take priority over <code style="background:rgba(255,255,255,0.06); padding:0.15rem 0.4rem; border-radius:4px; font-size:0.78rem;">.env</code> values. Leave fields blank to use <code style="background:rgba(255,255,255,0.06); padding:0.15rem 0.4rem; border-radius:4px; font-size:0.78rem;">.env</code> defaults.</p>

            <div style="display:grid; gap:1.25rem;">

                <!-- Paystack Keys -->
                <div style="background:var(--card-bg, rgba(255,255,255,0.03)); border:1px solid var(--border-color, rgba(255,255,255,0.1)); border-radius:16px; padding:1.25rem; box-sizing:border-box;">
                    <div style="display:flex; align-items:center; gap:0.5rem; margin-bottom:1rem; flex-wrap:wrap;">
                        <span style="font-size:1.1rem;">🟢</span>
                        <h4 style="margin:0; font-size:0.95rem; font-weight:700;">Paystack</h4>
                        <?php if (empty($settings['paystack_secret_key']) && !empty($_ENV['PAYSTACK_SECRET_KEY'] ?? '')): ?>
                            <span style="margin-left:auto; font-size:0.72rem; background:rgba(59,130,246,0.12); color:#60a5fa; padding:0.2rem 0.55rem; border-radius:8px; font-weight:600;">Using .env keys</span>
                        <?php elseif (!empty($settings['paystack_secret_key'])): ?>
                            <span style="margin-left:auto; font-size:0.72rem; background:rgba(16,185,129,0.12); color:#34d399; padding:0.2rem 0.55rem; border-radius:8px; font-weight:600;">Using admin keys</span>
                        <?php endif; ?>
                    </div>
                    <div class="admin-grid-2">
                        <label>Public Key
                            <input type="text" name="paystack_public_key" value="<?= e($settings['paystack_public_key'] ?? '') ?>" placeholder="pk_test_xxxxxxxxxxxx" autocomplete="off" spellcheck="false">
                        </label>
                        <label>Secret Key
                            <div style="position:relative; width:100%;">
                                <input type="password" name="paystack_secret_key" value="<?= e($settings['paystack_secret_key'] ?? '') ?>" placeholder="sk_test_xxxxxxxxxxxx" autocomplete="off" spellcheck="false" style="padding-right:3.5rem; width:100%; box-sizing:border-box;" id="paystack_secret_key_input">
                                <button type="button" onclick="toggleKeyVisibility('paystack_secret_key_input', this)" style="position:absolute; right:0.5rem; top:50%; transform:translateY(-50%); background:none; border:none; color:var(--text-muted, #888); cursor:pointer; font-size:0.82rem; padding:0.25rem 0.5rem;" aria-label="Toggle visibility">Show</button>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Stripe Keys -->
                <div style="background:var(--card-bg, rgba(255,255,255,0.03)); border:1px solid var(--border-color, rgba(255,255,255,0.1)); border-radius:16px; padding:1.25rem; box-sizing:border-box;">
                    <div style="display:flex; align-items:center; gap:0.5rem; margin-bottom:1rem; flex-wrap:wrap;">
                        <span style="font-size:1.1rem;">🟣</span>
                        <h4 style="margin:0; font-size:0.95rem; font-weight:700;">Stripe</h4>
                        <?php if (empty($settings['stripe_secret_key']) && !empty($_ENV['STRIPE_SECRET_KEY'] ?? '')): ?>
                            <span style="margin-left:auto; font-size:0.72rem; background:rgba(59,130,246,0.12); color:#60a5fa; padding:0.2rem 0.55rem; border-radius:8px; font-weight:600;">Using .env keys</span>
                        <?php elseif (!empty($settings['stripe_secret_key'])): ?>
                            <span style="margin-left:auto; font-size:0.72rem; background:rgba(16,185,129,0.12); color:#34d399; padding:0.2rem 0.55rem; border-radius:8px; font-weight:600;">Using admin keys</span>
                        <?php endif; ?>
                    </div>
                    <div class="admin-grid-2">
                        <label>Public Key
                            <input type="text" name="stripe_public_key" value="<?= e($settings['stripe_public_key'] ?? '') ?>" placeholder="pk_test_xxxxxxxxxxxx" autocomplete="off" spellcheck="false">
                        </label>
                        <label>Secret Key
                            <div style="position:relative; width:100%;">
                                <input type="password" name="stripe_secret_key" value="<?= e($settings['stripe_secret_key'] ?? '') ?>" placeholder="sk_test_xxxxxxxxxxxx" autocomplete="off" spellcheck="false" style="padding-right:3.5rem; width:100%; box-sizing:border-box;" id="stripe_secret_key_input">
                                <button type="button" onclick="toggleKeyVisibility('stripe_secret_key_input', this)" style="position:absolute; right:0.5rem; top:50%; transform:translateY(-50%); background:none; border:none; color:var(--text-muted, #888); cursor:pointer; font-size:0.82rem; padding:0.25rem 0.5rem;" aria-label="Toggle visibility">Show</button>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Flutterwave Keys -->
                <div style="background:var(--card-bg, rgba(255,255,255,0.03)); border:1px solid var(--border-color, rgba(255,255,255,0.1)); border-radius:16px; padding:1.25rem; box-sizing:border-box;">
                    <div style="display:flex; align-items:center; gap:0.5rem; margin-bottom:1rem; flex-wrap:wrap;">
                        <span style="font-size:1.1rem;">🟠</span>
                        <h4 style="margin:0; font-size:0.95rem; font-weight:700;">Flutterwave</h4>
                        <?php if (empty($settings['flutterwave_secret_key']) && !empty($_ENV['FLUTTERWAVE_SECRET_KEY'] ?? '')): ?>
                            <span style="margin-left:auto; font-size:0.72rem; background:rgba(59,130,246,0.12); color:#60a5fa; padding:0.2rem 0.55rem; border-radius:8px; font-weight:600;">Using .env keys</span>
                        <?php elseif (!empty($settings['flutterwave_secret_key'])): ?>
                            <span style="margin-left:auto; font-size:0.72rem; background:rgba(16,185,129,0.12); color:#34d399; padding:0.2rem 0.55rem; border-radius:8px; font-weight:600;">Using admin keys</span>
                        <?php endif; ?>
                    </div>
                    <div class="admin-grid-2">
                        <label>Public Key
                            <input type="text" name="flutterwave_public_key" value="<?= e($settings['flutterwave_public_key'] ?? '') ?>" placeholder="FLWPUBK_TEST-xxxxxxxxxxxx" autocomplete="off" spellcheck="false">
                        </label>
                        <label>Secret Key
                            <div style="position:relative; width:100%;">
                                <input type="password" name="flutterwave_secret_key" value="<?= e($settings['flutterwave_secret_key'] ?? '') ?>" placeholder="FLWSECK_TEST-xxxxxxxxxxxx" autocomplete="off" spellcheck="false" style="padding-right:3.5rem; width:100%; box-sizing:border-box;" id="flutterwave_secret_key_input">
                                <button type="button" onclick="toggleKeyVisibility('flutterwave_secret_key_input', this)" style="position:absolute; right:0.5rem; top:50%; transform:translateY(-50%); background:none; border:none; color:var(--text-muted, #888); cursor:pointer; font-size:0.82rem; padding:0.25rem 0.5rem;" aria-label="Toggle visibility">Show</button>
                            </div>
                        </label>
                    </div>
                </div>

            </div>
        </div>

        <!-- Currency, Fees & Exchange Rate -->
        <div style="margin-top: 1.5rem; padding-top: 1.25rem; border-top: 1px solid var(--border-color, rgba(255,255,255,0.08));">
            <div class="admin-grid-3">
                <label>Default Selected Gateway
                    <select name="default_gateway" style="width:100%; box-sizing:border-box;">
                        <option value="paystack" <?= ($settings['default_gateway'] ?? '') === 'paystack' ? 'selected' : '' ?>>Paystack Gateway</option>
                        <option value="stripe" <?= ($settings['default_gateway'] ?? '') === 'stripe' ? 'selected' : '' ?>>Stripe Payments</option>
                        <option value="flutterwave" <?= ($settings['default_gateway'] ?? '') === 'flutterwave' ? 'selected' : '' ?>>Flutterwave Gateway</option>
                    </select>
                </label>

                <label>Platform Commission Fee (%)
                    <input type="number" name="platform_fee" value="<?= e($settings['platform_fee'] ?? '5') ?>" min="0" max="100" step="0.1" required>
                </label>

                <label>Checkout Extra Charge (<?= e(get_currency_symbol()) ?>)
                    <input type="number" name="extra_charge" value="<?= e($settings['extra_charge'] ?? '100.00') ?>" min="0" step="0.01" required>
                </label>

                <label>System Currency Mode
                    <select name="currency" style="width:100%; box-sizing:border-box;">
                        <option value="NGN" <?= ($settings['currency'] ?? 'NGN') === 'NGN' ? 'selected' : '' ?>>Nigerian Naira (₦)</option>
                        <option value="USD" <?= ($settings['currency'] ?? 'NGN') === 'USD' ? 'selected' : '' ?>>US Dollars ($)</option>
                        <option value="EUR" <?= ($settings['currency'] ?? 'NGN') === 'EUR' ? 'selected' : '' ?>>Euro (€)</option>
                        <option value="GBP" <?= ($settings['currency'] ?? 'NGN') === 'GBP' ? 'selected' : '' ?>>British Pound (£)</option>
                        <option value="BOTH" <?= ($settings['currency'] ?? 'NGN') === 'BOTH' ? 'selected' : '' ?>>Dual Currency (Naira &amp; USD)</option>
                    </select>
                </label>

                <label>Exchange Rate (1 USD to NGN)
                    <input type="number" name="exchange_rate" value="<?= e($settings['exchange_rate'] ?? '1500.00') ?>" min="1" step="0.01" required>
                </label>
            </div>
        </div>
    </div>

    <!-- Custom Domain & API Feature Availability -->
    <div class="form-card" style="border-radius:22px; padding:1.5rem 1.75rem;">
        <div style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:0.75rem; margin-bottom:0.75rem;">
            <div>
                <h2 style="margin:0;">🌐 Custom Domain &amp; Headless API Integration</h2>
                <p class="section-note" style="margin-top:0.25rem;">Control host access to custom branding domains, DNS routing, and headless REST API endpoints.</p>
            </div>
            <div>
                <?php if ($isCustomDomainLive): ?>
                    <span style="display:inline-flex; align-items:center; gap:0.4rem; background:rgba(16,185,129,0.12); color:#10b981; font-weight:800; font-size:0.78rem; padding:0.35rem 0.75rem; border-radius:999px; border:1px solid rgba(16,185,129,0.3);">
                        <span style="display:inline-block; width:8px; height:8px; border-radius:50%; background:#10b981;"></span> Enabled for Hosts
                    </span>
                <?php else: ?>
                    <span style="display:inline-flex; align-items:center; gap:0.4rem; background:rgba(239,68,68,0.12); color:#ef4444; font-weight:800; font-size:0.78rem; padding:0.35rem 0.75rem; border-radius:999px; border:1px solid rgba(239,68,68,0.25);">
                        <span style="display:inline-block; width:8px; height:8px; border-radius:50%; background:#ef4444;"></span> Currently Unavailable for Hosts
                    </span>
                <?php endif; ?>
            </div>
        </div>

        <div style="background:rgba(var(--brand), 0.05); border:1px solid rgba(var(--brand), 0.2); border-radius:14px; padding:1.25rem 1.35rem; margin-bottom:1.25rem;">
            <label style="display:flex; align-items:center; gap:0.75rem; font-weight:700; cursor:pointer; font-size:0.95rem; margin-bottom:0.5rem;">
                <input type="checkbox" name="custom_domain_enabled" value="1" <?= (!empty($settings['custom_domain_enabled']) && $settings['custom_domain_enabled'] === '1') ? 'checked' : '' ?> style="width:1.25rem; height:1.25rem; cursor:pointer; accent-color:rgb(var(--brand));">
                <span>Enable Custom Domain &amp; Headless API for Hosts</span>
            </label>
            <p style="font-size:0.84rem; color:var(--text-muted, #888); margin:0; line-height:1.5;">
                When disabled, hosts will not see the "Custom Domain &amp; API" tab in their event management dashboard, and cannot submit new domain requests or initiate setup fee payments. Once you are ready, check this box and save configurations to make the feature live for all event hosts.
            </p>
        </div>

        <div class="admin-grid-2">
            <label>One-Time Domain Setup Fee (<?= e(get_currency_symbol()) ?>)
                <input type="number" name="custom_domain_fee" value="<?= e($settings['custom_domain_fee'] ?? '10000.00') ?>" min="0" step="0.01" required>
                <span style="font-size:0.78rem; color:var(--text-muted, #888); display:block; margin-top:0.35rem;">Fee charged to event organizers when submitting a custom domain integration request.</span>
            </label>
            <div style="display:flex; flex-direction:column; justify-content:center; padding-top:0.5rem;">
                <span style="font-size:0.84rem; font-weight:700; color:rgb(var(--text)); margin-bottom:0.25rem;">Manage Existing Requests</span>
                <p style="font-size:0.8rem; color:var(--text-muted, #888); margin:0 0 0.5rem 0;">Admins and Super Admins can always review and test requests regardless of whether the feature is currently live for hosts.</p>
                <a href="{{ route('admin/domain-requests') }}" class="admin-action-btn" style="display:inline-flex; align-items:center; gap:0.4rem; width:fit-content; padding:0.5rem 1rem; font-size:0.84rem;">
                    <span>🌐</span> View Domain Requests (<?= $pendingDomainsCount ?> Pending) →
                </a>
            </div>
        </div>
    </div>

    <!-- Save Actions Row -->
    <div class="form-card" style="border-radius:22px; padding:1.25rem 1.75rem; display:flex; justify-content:flex-end;">
        <button type="submit" class="admin-action-btn primary"
            style="padding:0.75rem 2rem; border-radius:12px; height:48px; font-weight:700; border:none; cursor:pointer;">
            Save Configurations
        </button>
    </div>

    <!-- Admin Navigation Shortcuts -->
    <div class="form-card" style="border-radius:22px; padding:1.5rem 1.75rem;">
        <h2>Admin Panels</h2>
        <p class="section-note" style="margin-bottom:1rem;">Access secondary admin modules:</p>
        <div class="admin-grid-3">
            <a href="{{ route('admin/users') }}" class="admin-action-btn"
                style="display:flex; align-items:center; justify-content:center; gap:0.5rem; text-decoration:none; font-weight:600; padding:0.85rem 1rem; border-radius:12px; box-sizing:border-box;">
                👤 Users Management
            </a>
            <a href="{{ route('admin/payouts') }}" class="admin-action-btn"
                style="display:flex; align-items:center; justify-content:center; gap:0.5rem; text-decoration:none; font-weight:600; padding:0.85rem 1rem; border-radius:12px; box-sizing:border-box;">
                💳 Payout Queue
            </a>
            <a href="{{ route('admin/events') }}" class="admin-action-btn"
                style="display:flex; align-items:center; justify-content:center; gap:0.5rem; text-decoration:none; font-weight:600; padding:0.85rem 1rem; border-radius:12px; box-sizing:border-box;">
                🎪 Events Management
            </a>
            <a href="{{ route('admin/teams') }}" class="admin-action-btn"
                style="display:flex; align-items:center; justify-content:center; gap:0.5rem; text-decoration:none; font-weight:600; padding:0.85rem 1rem; border-radius:12px; box-sizing:border-box;">
                🏢 Team Management
            </a>
            <a href="{{ route('admin/support') }}" class="admin-action-btn"
                style="display:flex; align-items:center; justify-content:center; gap:0.5rem; text-decoration:none; font-weight:600; padding:0.85rem 1rem; border-radius:12px; box-sizing:border-box;">
                📬 Support Inquiries
            </a>
            <a href="{{ route('admin/communications') }}" class="admin-action-btn"
                style="display:flex; align-items:center; justify-content:center; gap:0.5rem; text-decoration:none; font-weight:600; padding:0.85rem 1rem; border-radius:12px; box-sizing:border-box;">
                📨 Communications
            </a>
        </div>
    </div>

    <!-- Preferences Theme Toggler -->
    <div class="form-card" style="border-radius:22px; padding:1.5rem 1.75rem;">
        <h2>Preferences</h2>
        <p class="section-note" style="margin-bottom:1rem;">Choose your visual interface style:</p>
        <div>
            <button type="button" class="admin-action-btn" data-theme-toggle="true" aria-label="Toggle theme"
                style="display:inline-flex; align-items:center; justify-content:center; gap:0.5rem; width:100%; height:48px; padding:0.85rem 1rem; border-radius:12px; cursor:pointer;">
                🌓 Toggle Dark / Light Mode
            </button>
        </div>
    </div>
</form>

<script>
function toggleKeyVisibility(inputId, btn) {
    var input = document.getElementById(inputId);
    if (!input) return;
    if (input.type === 'password') {
        input.type = 'text';
        btn.textContent = 'Hide';
    } else {
        input.type = 'password';
        btn.textContent = 'Show';
    }
}

// ── Globally Accessible Live Announcement Modal Previewer ──
window.openAnnouncementPreview = function(e) {
    if (e && e.preventDefault) e.preventDefault();

    var modal = document.getElementById('admin-announcement-preview-modal');
    if (!modal) {
        console.error('Modal preview element not found');
        return;
    }

    // Ensure modal is directly under document.body to avoid parent container stacking/overflow clipping
    if (modal.parentElement !== document.body) {
        document.body.appendChild(modal);
    }

    var badgeInput = document.querySelector('input[name="announcement_badge"]');
    var titleInput = document.querySelector('input[name="announcement_title"]');
    var msgInput = document.querySelector('textarea[name="announcement_message"]');
    var linkTextInput = document.querySelector('input[name="announcement_link_text"]');
    var linkUrlInput = document.querySelector('input[name="announcement_link_url"]');
    var dismissInput = document.querySelector('input[name="announcement_dismiss_text"]');
    var imgUrlInput = document.querySelector('input[name="announcement_image_url"]');
    var fileInput = document.querySelector('input[name="announcement_image"]');
    var existingThumb = document.querySelector('img[alt="Announcement Banner"]');

    var badgeVal = badgeInput ? badgeInput.value.trim() : '';
    var titleVal = titleInput ? titleInput.value.trim() : '';
    var msgVal = msgInput ? msgInput.value.trim() : '';
    var linkTextVal = linkTextInput ? linkTextInput.value.trim() : '';
    var linkUrlVal = linkUrlInput ? linkUrlInput.value.trim() : '';
    var dismissVal = dismissInput ? dismissInput.value.trim() : '';

    var badgeEl = document.getElementById('prev-ann-badge');
    if (badgeEl) {
        if (badgeVal) {
            badgeEl.textContent = badgeVal;
            badgeEl.style.display = 'inline-flex';
        } else {
            badgeEl.style.display = 'none';
        }
    }

    var titleEl = document.getElementById('prev-ann-title');
    if (titleEl) {
        if (titleVal) {
            titleEl.textContent = titleVal;
            titleEl.style.display = 'block';
        } else {
            titleEl.style.display = 'none';
        }
    }

    var msgEl = document.getElementById('prev-ann-msg');
    if (msgEl) {
        if (msgVal) {
            msgEl.innerHTML = msgVal.replace(/\n/g, '<br>');
            msgEl.style.display = 'block';
        } else {
            msgEl.style.display = 'none';
        }
    }

    var actionBtn = document.getElementById('prev-ann-action-btn');
    if (actionBtn) {
        if (linkUrlVal) {
            actionBtn.textContent = linkTextVal || 'Check It Out →';
            actionBtn.style.display = 'inline-flex';
        } else {
            actionBtn.style.display = 'none';
        }
    }

    var dismissBtn = document.getElementById('btn-dismiss-ann-preview') || document.getElementById('prev-ann-dismiss-btn');
    if (dismissBtn) {
        if (dismissVal) {
            dismissBtn.textContent = dismissVal;
            dismissBtn.style.display = 'inline-flex';
        } else {
            dismissBtn.style.display = 'none';
        }
    }

    var actionsWrap = document.getElementById('prev-ann-actions-wrap');
    if (actionsWrap) {
        actionsWrap.style.display = (linkUrlVal || dismissVal) ? 'flex' : 'none';
    }

    var bodyEl = document.getElementById('prev-ann-body');
    var hasBodyContent = Boolean(badgeVal || titleVal || msgVal || linkUrlVal || dismissVal);
    if (bodyEl) {
        bodyEl.style.display = hasBodyContent ? 'block' : 'none';
    }

    var mediaWrap = document.getElementById('prev-ann-media');
    var mediaImg = document.getElementById('prev-ann-img');
    var mediaBlend = document.getElementById('prev-ann-media-blend');
    var cardEl = document.getElementById('prev-ann-card');
    var hasImg = false;
    
    // Check if user has chosen a file locally or provided a URL or has an existing thumbnail
    if (fileInput && fileInput.files && fileInput.files[0]) {
        try {
            mediaImg.src = URL.createObjectURL(fileInput.files[0]);
            hasImg = true;
        } catch(err) {
            hasImg = false;
        }
    } else if (imgUrlInput && imgUrlInput.value.trim()) {
        mediaImg.src = imgUrlInput.value.trim();
        hasImg = true;
    } else if (existingThumb && existingThumb.src) {
        mediaImg.src = existingThumb.src;
        hasImg = true;
    }

    if (hasImg) {
        mediaWrap.style.display = 'block';
        if (!hasBodyContent) {
            // Image-only poster layout: clean full image without gradient or empty body
            mediaWrap.style.height = 'auto';
            mediaWrap.style.maxHeight = 'min(82vh, 680px)';
            mediaImg.style.maxHeight = 'min(82vh, 680px)';
            mediaImg.style.objectFit = 'contain';
            if (mediaBlend) mediaBlend.style.display = 'none';
            if (cardEl) {
                cardEl.style.background = 'transparent';
                cardEl.style.border = 'none';
                cardEl.style.boxShadow = 'none';
            }
        } else {
            mediaWrap.style.height = 'clamp(160px, 32vw, 220px)';
            mediaWrap.style.maxHeight = '';
            mediaImg.style.maxHeight = '';
            mediaImg.style.objectFit = 'cover';
            if (mediaBlend) mediaBlend.style.display = 'block';
            if (cardEl) {
                cardEl.style.background = 'linear-gradient(145deg, rgba(var(--card), 0.82) 0%, rgba(var(--card), 0.92) 100%)';
                cardEl.style.border = '1px solid rgba(255, 255, 255, 0.2)';
                cardEl.style.boxShadow = '0 28px 70px -15px rgba(0,0,0,0.45)';
            }
        }
    } else {
        mediaWrap.style.display = 'none';
        if (cardEl) {
            cardEl.style.background = 'linear-gradient(145deg, rgba(var(--card), 0.82) 0%, rgba(var(--card), 0.92) 100%)';
            cardEl.style.border = '1px solid rgba(255, 255, 255, 0.2)';
            cardEl.style.boxShadow = '0 28px 70px -15px rgba(0,0,0,0.45)';
        }
        if (!hasBodyContent && bodyEl) {
            bodyEl.style.display = 'block';
            if (titleEl) {
                titleEl.textContent = 'Preview: No Content Entered';
                titleEl.style.display = 'block';
            }
            if (msgEl) {
                msgEl.textContent = 'Add a banner image or enter a title/message above to see how your announcement will look to visitors.';
                msgEl.style.display = 'block';
            }
        }
    }

    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
};

window.closeAnnouncementPreview = function() {
    var modal = document.getElementById('admin-announcement-preview-modal');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = '';
    }
};

// Bind keyboard & toggle handlers safely
function initAdminAnnouncementHelpers() {
    var btnPreview = document.getElementById('btn-preview-announcement');
    if (btnPreview) {
        btnPreview.onclick = window.openAnnouncementPreview;
    }

    var annCheckbox = document.getElementById('ann-toggle-checkbox');
    var annSlider = document.getElementById('ann-toggle-slider');
    var annKnob = document.getElementById('ann-toggle-knob');
    var annStatusPill = document.getElementById('ann-status-pill');

    if (annCheckbox && annSlider && annKnob) {
        annCheckbox.addEventListener('change', function() {
            if (this.checked) {
                annSlider.style.background = 'rgb(var(--brand))';
                annKnob.style.left = '27px';
                if (annStatusPill) {
                    annStatusPill.innerHTML = '<span style="width:8px; height:8px; border-radius:50%; background:#10b981; display:inline-block;"></span> Active on Platform';
                    annStatusPill.style.background = 'rgba(16,185,129,0.15)';
                    annStatusPill.style.color = '#10b981';
                    annStatusPill.style.borderColor = 'rgba(16,185,129,0.3)';
                }
            } else {
                annSlider.style.background = 'rgba(var(--muted),0.3)';
                annKnob.style.left = '3px';
                if (annStatusPill) {
                    annStatusPill.innerHTML = '<span style="width:8px; height:8px; border-radius:50%; background:rgb(var(--muted)); display:inline-block;"></span> Currently Inactive';
                    annStatusPill.style.background = 'rgba(var(--muted),0.12)';
                    annStatusPill.style.color = 'rgb(var(--muted))';
                    annStatusPill.style.borderColor = 'rgba(var(--muted),0.2)';
                }
            }
        });
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            window.closeAnnouncementPreview();
        }
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initAdminAnnouncementHelpers);
} else {
    initAdminAnnouncementHelpers();
}
</script>

<!-- Live Announcement Preview Modal Dialog (Glassmorphic Admin Preview) -->
<div id="admin-announcement-preview-modal" style="display:none; position:fixed; inset:0; background:radial-gradient(circle at 50% 35%, rgba(var(--brand), 0.22) 0%, rgba(0, 0, 0, 0.55) 100%); backdrop-filter:blur(14px) saturate(160%); -webkit-backdrop-filter:blur(14px) saturate(160%); z-index:99999999 !important; align-items:center; justify-content:center; padding:clamp(0.75rem, 3vw, 1.75rem); box-sizing:border-box;">
    <div id="admin-ann-preview-backdrop" onclick="window.closeAnnouncementPreview()" style="position:absolute; inset:0; cursor:pointer;"></div>
    <div id="prev-ann-card" style="position:relative; width:100%; max-width:min(92vw, 520px); max-height:min(88vh, 740px); background:linear-gradient(145deg, rgba(var(--card), 0.82) 0%, rgba(var(--card), 0.92) 100%); color:rgb(var(--text)); border:1px solid rgba(255, 255, 255, 0.2); border-radius:clamp(20px, 4vw, 30px); box-shadow:0 28px 70px -15px rgba(0,0,0,0.45), 0 0 0 1px rgba(var(--brand), 0.15), 0 0 45px rgba(var(--brand), 0.12), inset 0 1px 1px rgba(255, 255, 255, 0.3); backdrop-filter:blur(30px) saturate(190%); -webkit-backdrop-filter:blur(30px) saturate(190%); overflow-y:auto; overflow-x:hidden; z-index:1; animation:previewScale 0.28s cubic-bezier(0.16, 1, 0.3, 1);">
        <button type="button" id="btn-close-ann-preview" onclick="window.closeAnnouncementPreview()" style="position:absolute; top:1rem; right:1rem; width:38px; height:38px; border-radius:50%; background:rgba(var(--card), 0.7); border:1px solid rgba(255,255,255,0.22); color:rgb(var(--text)); font-size:1.25rem; font-weight:600; cursor:pointer; display:flex; align-items:center; justify-content:center; z-index:10; backdrop-filter:blur(16px); -webkit-backdrop-filter:blur(16px); box-shadow:0 4px 14px rgba(0,0,0,0.2);">&times;</button>
        
        <div id="prev-ann-media" style="display:none; position:relative; width:100%; height:clamp(160px, 32vw, 220px); overflow:hidden;">
            <img id="prev-ann-img" src="" alt="Banner Preview" style="width:100%; height:100%; object-fit:cover; display:block;">
            <div id="prev-ann-media-blend" style="position:absolute; inset:0; background:linear-gradient(to bottom, rgba(0,0,0,0.2) 0%, transparent 35%, rgba(var(--card),0.85) 92%, rgba(var(--card),0.95) 100%); pointer-events:none;"></div>
        </div>

        <div id="prev-ann-body" style="padding:clamp(1.4rem, 4vw, 2.25rem) clamp(1.25rem, 3.5vw, 2rem) clamp(1.5rem, 4vw, 2.25rem); text-align:center; position:relative; z-index:2;">
            <span id="prev-ann-badge" style="display:inline-flex; align-items:center; gap:0.45rem; padding:0.35rem 1rem; border-radius:999px; background:linear-gradient(135deg, rgba(var(--brand), 0.18) 0%, rgba(var(--brand), 0.08) 100%); color:rgb(var(--brand)); font-weight:800; font-size:0.82rem; letter-spacing:0.025em; border:1px solid rgba(var(--brand), 0.32); box-shadow:0 4px 14px rgba(var(--brand), 0.16); margin-bottom:0.85rem;">🎉 Special Announcement</span>
            <h2 id="prev-ann-title" style="font-size:clamp(1.35rem, 3.8vw, 1.8rem); font-weight:900; line-height:1.24; margin:0 0 0.85rem; color:rgb(var(--text)); letter-spacing:-0.025em;">Announcement Title</h2>
            <div id="prev-ann-msg" style="font-size:clamp(0.92rem, 1.8vw, 0.98rem); line-height:1.65; color:rgba(var(--text), 0.82); margin-bottom:1.75rem; text-align:center;">
                Announcement message will appear here.
            </div>
            <div id="prev-ann-actions-wrap" style="display:flex; gap:0.75rem; justify-content:center; align-items:center; flex-wrap:wrap;">
                <button type="button" id="prev-ann-action-btn" class="admin-action-btn primary" style="height:48px; padding:0 1.65rem; border-radius:14px; font-weight:800; cursor:pointer; flex:1; min-width:140px; background:linear-gradient(135deg, rgb(var(--brand)), color-mix(in srgb, rgb(var(--brand)) 75%, #059669)); color:#fff; border:none; box-shadow:0 8px 24px -4px rgba(var(--brand), 0.45);">Check It Out →</button>
                <button type="button" id="btn-dismiss-ann-preview" onclick="window.closeAnnouncementPreview()" class="admin-action-btn" style="height:48px; padding:0 1.5rem; border-radius:14px; font-weight:700; cursor:pointer; flex:1; min-width:130px; background:rgba(var(--muted),0.08); color:rgb(var(--text)); border:1px solid rgba(var(--muted),0.22); backdrop-filter:blur(8px);">Continue to Site</button>
            </div>
        </div>
    </div>
</div>
<style>
@keyframes previewScale {
    from { transform: scale(0.92) translateY(12px); opacity: 0; }
    to { transform: scale(1) translateY(0); opacity: 1; }
}
</style>
@endsection