<?php

/**
 * Admin Settings
 *
 * Executive Platform Configuration Suite for Branding, Dynamic Themes,
 * Multi-Gateway Payments, Timezones, Announcements, and System Parameters.
 *
 * @package Evently
 * @subpackage Views/Admin
 * @version 2.1.0
 */

$pendingDomainsCount = 0;
try {
    $pendingDomainsCount = (int)\Ace\Application::$app->db->pdo->query("SELECT COUNT(*) FROM event_domain_requests WHERE status = 'pending'")->fetchColumn();
} catch (\Throwable $e) {}

$themePrimary = function_exists('theme_primary_color') ? theme_primary_color() : '#108a55';
$themeSecondary = function_exists('theme_secondary_color') ? theme_secondary_color() : '#1aae6c';
$themePrimaryRgb = function_exists('theme_primary_color') ? implode(' ', sscanf(ltrim($themePrimary, '#'), '%02x%02x%02x')) : '16 138 85';

$primaryColor = !empty($settings['theme_primary_color']) ? $settings['theme_primary_color'] : $themePrimary;
$secondaryColor = !empty($settings['theme_secondary_color']) ? $settings['theme_secondary_color'] : $themeSecondary;
$currentPreset = !empty($settings['theme_preset']) ? $settings['theme_preset'] : 'emerald';
$isCustomDomainLive = !empty($settings['custom_domain_enabled']) && $settings['custom_domain_enabled'] === '1';
$isAnnLive = !empty($settings['announcement_enabled']) && $settings['announcement_enabled'] === '1';
$enabledGateways = (array)($settings['enabled_gateways'] ?? ['paystack']);
?>

@extends('layouts/admin')

@section('title')
Admin Settings - Manage Site &amp; System Configurations
@endsection

@section('content')
<div class="as-shell">

    <!-- ─── BREADCRUMB & EXECUTIVE HEADER ─── -->
    <header class="as-header-card">
        <div class="as-header-top">
            <nav class="as-breadcrumb" aria-label="Breadcrumb">
                <a href="{{ route('admin/overview') }}">Admin</a>
                <span class="as-bc-sep">/</span>
                <span class="as-bc-current">Settings &amp; Configuration</span>
            </nav>

            <div class="as-header-badges">
                <span class="as-badge as-badge--brand">● System Engine v2.1</span>
                <?php if ($isCustomDomainLive): ?>
                    <span class="as-badge as-badge--success">Domains Active</span>
                <?php endif; ?>
                <?php if ($isAnnLive): ?>
                    <span class="as-badge as-badge--warning">Announcement Live</span>
                <?php endif; ?>
            </div>
        </div>

        <div class="as-header-body">
            <div class="as-header-info">
                <h1 class="as-page-title">Admin System Settings</h1>
                <p class="as-page-desc">Configure brand identity, custom themes, payment gateways, event lifecycle timing, and platform announcements.</p>
            </div>
            <div class="as-header-actions">
                <a href="{{ route('admin/overview') }}" class="as-btn as-btn-ghost">
                    <span>←</span> Dashboard
                </a>
            </div>
        </div>
    </header>

    <!-- ─── QUICK OPERATIONAL SHORTCUTS ─── -->
    <div class="as-shortcuts-grid">
        <a href="{{ route('admin/events') }}" class="as-shortcut-tile as-shortcut--blue">
            <span class="as-shortcut-icon">🎪</span>
            <div>
                <strong>Events Hub</strong>
                <span>All organizer events &amp; audit reports</span>
            </div>
        </a>

        <a href="{{ route('admin/domain-requests') }}" class="as-shortcut-tile as-shortcut--amber">
            <span class="as-shortcut-icon">🌐</span>
            <div>
                <strong>Domain Requests</strong>
                <span><?= $pendingDomainsCount ?> pending DNS approvals</span>
            </div>
        </a>

        <a href="{{ route('admin/attendees') }}" class="as-shortcut-tile as-shortcut--green">
            <span class="as-shortcut-icon">📇</span>
            <div>
                <strong>Attendee Directory</strong>
                <span>Contact histories &amp; CSV export</span>
            </div>
        </a>

        <a href="{{ route('admin/communications') }}" class="as-shortcut-tile as-shortcut--indigo">
            <span class="as-shortcut-icon">📨</span>
            <div>
                <strong>Communications</strong>
                <span>Broadcast emails &amp; host messaging</span>
            </div>
        </a>

        <div class="as-shortcut-tile as-shortcut--rose">
            <span class="as-shortcut-icon">⚡</span>
            <div class="as-w-full">
                <strong>View Cache</strong>
                <form method="POST" action="{{ route('admin/cache/clear') }}" class="as-inline">
                    <?= csrf_field() ?>
                    <button type="submit" class="as-btn-cache-purge" onclick="return confirm('Purge all compiled view cache files now?');">
                        Purge Cache 🧹
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- ─── MAIN SETTINGS FORM WITH TABBED WORKSPACE ─── -->
    <form method="POST" action="{{ route('admin/settings') }}" enctype="multipart/form-data" id="admin-settings-form">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="update_settings" />
        <input type="hidden" name="theme_preset" id="theme-preset-input" value="<?= e($currentPreset) ?>" />

        <!-- Segmented Tab Navigation -->
        <div class="as-tabs-bar" role="tablist">
            <button type="button" class="as-tab-btn active" data-tab="tab-appearance" role="tab" aria-selected="true">
                <span class="as-tab-icon">🎨</span> Appearance &amp; Colors
            </button>
            <button type="button" class="as-tab-btn" data-tab="tab-branding" role="tab" aria-selected="false">
                <span class="as-tab-icon">🏢</span> Identity &amp; Media
            </button>
            <button type="button" class="as-tab-btn" data-tab="tab-payments" role="tab" aria-selected="false">
                <span class="as-tab-icon">💳</span> Payments &amp; Currency
            </button>
            <button type="button" class="as-tab-btn" data-tab="tab-lifecycle" role="tab" aria-selected="false">
                <span class="as-tab-icon">⚙️</span> Timezone &amp; Security
            </button>
            <button type="button" class="as-tab-btn" data-tab="tab-marketing" role="tab" aria-selected="false">
                <span class="as-tab-icon">📣</span> Announcement &amp; SEO
            </button>
        </div>

        <!-- ══════════════════════════════════════════════════════════════
             TAB 1: APPEARANCE & BRAND COLORS
             ══════════════════════════════════════════════════════════════ -->
        <div id="tab-appearance" class="as-tab-pane active" role="tabpanel">
            <div class="as-card">
                <div class="as-card-header">
                    <div class="as-card-title-group">
                        <span class="as-card-icon">🎨</span>
                        <div>
                            <h2 class="as-card-title">Main Colors &amp; Platform Theme</h2>
                            <p class="as-card-sub">Change brand highlights across all client pages, host dashboards, and admin consoles with instant synchronization.</p>
                        </div>
                    </div>
                    <div>
                        <button type="button" class="as-btn as-btn-ghost" id="btn-reset-theme-colors" onclick="resetThemeColors()">
                            ↺ Reset to Default Emerald
                        </button>
                    </div>
                </div>

                <div class="as-card-body">
                    <!-- Presets Grid -->
                    <div class="as-presets-section">
                        <label class="as-field-label">Quick Brand Color Presets</label>
                        <div class="as-presets-grid" id="theme-presets-grid">
                            <!-- Seasonal & Holiday Palettes -->
                            <button type="button" class="as-preset-pill <?= $currentPreset === 'christmas' ? 'is-active' : '' ?>" data-preset="christmas" data-primary="#dc2626" data-secondary="#16a34a" onclick="applyPreset(this)">
                                <span class="as-preset-swatch">
                                    <span style="background:#dc2626;"></span>
                                    <span style="background:#16a34a;"></span>
                                </span>
                                <span class="as-preset-name">🎄 Christmas Festive</span>
                                <span class="as-preset-check">✓</span>
                            </button>

                            <button type="button" class="as-preset-pill <?= $currentPreset === 'halloween' ? 'is-active' : '' ?>" data-preset="halloween" data-primary="#ea580c" data-secondary="#7e22ce" onclick="applyPreset(this)">
                                <span class="as-preset-swatch">
                                    <span style="background:#ea580c;"></span>
                                    <span style="background:#7e22ce;"></span>
                                </span>
                                <span class="as-preset-name">🎃 Halloween Spooky</span>
                                <span class="as-preset-check">✓</span>
                            </button>

                            <button type="button" class="as-preset-pill <?= $currentPreset === 'winter' ? 'is-active' : '' ?>" data-preset="winter" data-primary="#0284c7" data-secondary="#6366f1" onclick="applyPreset(this)">
                                <span class="as-preset-swatch">
                                    <span style="background:#0284c7;"></span>
                                    <span style="background:#6366f1;"></span>
                                </span>
                                <span class="as-preset-name">❄️ Winter Frost</span>
                                <span class="as-preset-check">✓</span>
                            </button>

                            <button type="button" class="as-preset-pill <?= $currentPreset === 'summer' ? 'is-active' : '' ?>" data-preset="summer" data-primary="#f59e0b" data-secondary="#f43f5e" onclick="applyPreset(this)">
                                <span class="as-preset-swatch">
                                    <span style="background:#f59e0b;"></span>
                                    <span style="background:#f43f5e;"></span>
                                </span>
                                <span class="as-preset-name">☀️ Summer Sunset</span>
                                <span class="as-preset-check">✓</span>
                            </button>

                            <!-- Modern & Platform Brands -->
                            <button type="button" class="as-preset-pill <?= $currentPreset === 'emerald' ? 'is-active' : '' ?>" data-preset="emerald" data-primary="#108a55" data-secondary="#1aae6c" onclick="applyPreset(this)">
                                <span class="as-preset-swatch">
                                    <span style="background:#108a55;"></span>
                                    <span style="background:#1aae6c;"></span>
                                </span>
                                <span class="as-preset-name">🌿 Eventlyy Emerald</span>
                                <span class="as-preset-check">✓</span>
                            </button>

                            <button type="button" class="as-preset-pill <?= $currentPreset === 'neon' ? 'is-active' : '' ?>" data-preset="neon" data-primary="#06b6d4" data-secondary="#d946ef" onclick="applyPreset(this)">
                                <span class="as-preset-swatch">
                                    <span style="background:#06b6d4;"></span>
                                    <span style="background:#d946ef;"></span>
                                </span>
                                <span class="as-preset-name">⚡ Cyber Neon</span>
                                <span class="as-preset-check">✓</span>
                            </button>

                            <button type="button" class="as-preset-pill <?= $currentPreset === 'indigo' ? 'is-active' : '' ?>" data-preset="indigo" data-primary="#4f46e5" data-secondary="#a855f7" onclick="applyPreset(this)">
                                <span class="as-preset-swatch">
                                    <span style="background:#4f46e5;"></span>
                                    <span style="background:#a855f7;"></span>
                                </span>
                                <span class="as-preset-name">👑 Royal Indigo</span>
                                <span class="as-preset-check">✓</span>
                            </button>

                            <button type="button" class="as-preset-pill <?= $currentPreset === 'ocean' ? 'is-active' : '' ?>" data-preset="ocean" data-primary="#0284c7" data-secondary="#14b8a6" onclick="applyPreset(this)">
                                <span class="as-preset-swatch">
                                    <span style="background:#0284c7;"></span>
                                    <span style="background:#14b8a6;"></span>
                                </span>
                                <span class="as-preset-name">🌊 Ocean Azure</span>
                                <span class="as-preset-check">✓</span>
                            </button>

                            <button type="button" class="as-preset-pill <?= $currentPreset === 'rose' ? 'is-active' : '' ?>" data-preset="rose" data-primary="#e11d48" data-secondary="#fb7185" onclick="applyPreset(this)">
                                <span class="as-preset-swatch">
                                    <span style="background:#e11d48;"></span>
                                    <span style="background:#fb7185;"></span>
                                </span>
                                <span class="as-preset-name">🌹 Crimson Rose</span>
                                <span class="as-preset-check">✓</span>
                            </button>

                            <button type="button" class="as-preset-pill <?= $currentPreset === 'slate' ? 'is-active' : '' ?>" data-preset="slate" data-primary="#1e293b" data-secondary="#64748b" onclick="applyPreset(this)">
                                <span class="as-preset-swatch">
                                    <span style="background:#1e293b;"></span>
                                    <span style="background:#64748b;"></span>
                                </span>
                                <span class="as-preset-name">🌌 Midnight Slate</span>
                                <span class="as-preset-check">✓</span>
                            </button>
                        </div>
                    </div>

                    <!-- Pickers & Live Preview Layout -->
                    <div class="as-theme-controls-grid">
                        <div class="as-color-inputs-box">
                            <div class="as-color-field">
                                <label for="primary-color-text" class="as-field-label">Primary Brand Color</label>
                                <span class="as-field-hint">Primary action buttons, active navigation pills, badges, and accents.</span>
                                <div class="as-color-input-wrap">
                                    <input type="color" id="primary-color-picker" class="as-swatch-picker" value="<?= e($primaryColor) ?>" oninput="syncFromColorPicker('primary', this.value)" aria-label="Pick primary color">
                                    <input type="text" id="primary-color-text" name="theme_primary_color" class="as-input as-font-mono" value="<?= e($primaryColor) ?>" maxlength="7" pattern="^#[0-9a-fA-F]{6}$" spellcheck="false" oninput="syncFromTextInput('primary', this.value)" placeholder="#108a55" required>
                                </div>
                            </div>

                            <div class="as-color-field">
                                <label for="secondary-color-text" class="as-field-label">Secondary / Accent Color</label>
                                <span class="as-field-hint">Secondary hover states, subtle gradient borders, and supporting chips.</span>
                                <div class="as-color-input-wrap">
                                    <input type="color" id="secondary-color-picker" class="as-swatch-picker" value="<?= e($secondaryColor) ?>" oninput="syncFromColorPicker('secondary', this.value)" aria-label="Pick secondary color">
                                    <input type="text" id="secondary-color-text" name="theme_secondary_color" class="as-input as-font-mono" value="<?= e($secondaryColor) ?>" maxlength="7" pattern="^#[0-9a-fA-F]{6}$" spellcheck="false" oninput="syncFromTextInput('secondary', this.value)" placeholder="#1aae6c" required>
                                </div>
                            </div>
                        </div>

                        <!-- Live Interactive Preview Box (Balanced Duo-Tone Real-Time Mix) -->
                        <div class="as-live-preview-card" id="theme-live-preview-box" style="border-top:3px solid <?= e($primaryColor) ?>; border-bottom:2px solid <?= e($secondaryColor) ?>;">
                            <div class="as-preview-head" style="display:flex; justify-content:space-between; align-items:center;">
                                <span class="as-badge" id="preview-badge" style="background:<?= e($secondaryColor) ?>; color:#ffffff; font-weight:800;">✨ Accent Badge</span>
                                <span class="as-badge" id="preview-badge-primary" style="background:rgba(var(--primary-rgb), 0.14); color:<?= e($primaryColor) ?>; border:1px solid <?= e($primaryColor) ?>; font-weight:700;">● Primary Tag</span>
                            </div>
                            <div class="as-preview-body">
                                <h3 class="as-preview-title" id="preview-title">Elevate Your Events Experience</h3>
                                <p class="as-preview-desc">Buttons, highlights, progress indicators, and focus states immediately reflect your configured brand palette with balanced color mixing.</p>
                                
                                <div style="margin: 0.85rem 0 1.15rem;">
                                    <div style="display:flex; justify-content:space-between; font-size:0.75rem; margin-bottom:0.35rem; font-weight:700;">
                                        <span id="preview-label-1" style="color:<?= e($primaryColor) ?>;">Primary Metric</span>
                                        <span id="preview-label-2" style="color:<?= e($secondaryColor) ?>;">Secondary Accent</span>
                                    </div>
                                    <div style="height:6px; border-radius:999px; background:linear-gradient(90deg, <?= e($primaryColor) ?> 0%, <?= e($secondaryColor) ?> 100%);" id="preview-bar"></div>
                                </div>

                                <div class="as-preview-actions" style="display:flex; flex-wrap:wrap; gap:0.5rem;">
                                    <button type="button" class="as-btn" id="preview-btn-primary" style="background:linear-gradient(135deg, <?= e($primaryColor) ?> 0%, <?= e($secondaryColor) ?> 100%); color:#ffffff; border:none; box-shadow:0 4px 14px rgba(0,0,0,0.18);">Duo-Tone Action →</button>
                                    <button type="button" class="as-btn" id="preview-btn-secondary" style="background:<?= e($secondaryColor) ?>; color:#ffffff; border:none;">Secondary Pill</button>
                                    <button type="button" class="as-btn as-btn-outline" id="preview-btn-outline" style="border-color:<?= e($primaryColor) ?>; color:<?= e($primaryColor) ?>;">Outline</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ══════════════════════════════════════════════════════════════
             TAB 2: IDENTITY & MEDIA BRANDING
             ══════════════════════════════════════════════════════════════ -->
        <div id="tab-branding" class="as-tab-pane" role="tabpanel">
            <div class="as-card">
                <div class="as-card-header">
                    <div class="as-card-title-group">
                        <span class="as-card-icon">🏢</span>
                        <div>
                            <h2 class="as-card-title">Site Identity &amp; Media Assets</h2>
                            <p class="as-card-sub">Platform naming, support contact routing, maintenance screen, and uploaded branding graphics.</p>
                        </div>
                    </div>
                </div>

                <div class="as-card-body">
                    <div class="as-grid-2">
                        <div class="as-field">
                            <label for="site_title" class="as-field-label">Site Title / Brand Name <span class="as-req">*</span></label>
                            <input type="text" id="site_title" name="site_title" class="as-input" value="<?= e($settings['site_title'] ?? 'Eventlyy') ?>" placeholder="e.g. Eventlyy" required>
                        </div>

                        <div class="as-field">
                            <label for="support_email" class="as-field-label">Platform Support Email <span class="as-req">*</span></label>
                            <input type="email" id="support_email" name="support_email" class="as-input" value="<?= e($settings['support_email'] ?? 'support@eventlyy.com') ?>" placeholder="support@eventlyy.com" required>
                        </div>
                    </div>

                    <div class="as-field as-mt-4">
                        <label for="site_description" class="as-field-label">Site Tagline / Description</label>
                        <input type="text" id="site_description" name="site_description" class="as-input" value="<?= e($settings['site_description'] ?? '') ?>" placeholder="e.g. Discover and book amazing events near you.">
                    </div>

                    <div class="as-field as-mt-4">
                        <label for="maintenance_mode" class="as-field-label">Maintenance Mode Splash Screen</label>
                        <select id="maintenance_mode" name="maintenance_mode" class="as-select">
                            <option value="no" <?= ($settings['maintenance_mode'] ?? '') === 'no' ? 'selected' : '' ?>>Disabled (Platform Live for Visitors)</option>
                            <option value="yes" <?= ($settings['maintenance_mode'] ?? '') === 'yes' ? 'selected' : '' ?>>Enabled (Maintenance Screen Active)</option>
                        </select>
                    </div>

                    <!-- Uploads Grid: Logo & Favicon -->
                    <div class="as-grid-2 as-mt-6">
                        <!-- Logo Upload -->
                        <div class="as-upload-box">
                            <div class="as-upload-head">
                                <strong>Brand Logo</strong>
                                <span class="as-text-muted as-text-xs">PNG, JPG, SVG, WEBP (Max 2MB)</span>
                            </div>
                            <?php if (!empty($settings['site_logo'])): ?>
                                <div class="as-thumb-preview-wrap">
                                    <img src="<?= e(asset($settings['site_logo'])) ?>" alt="Current Logo" class="as-logo-preview" />
                                    <label class="as-remove-label">
                                        <input type="checkbox" name="remove_logo" value="1"> Remove Logo
                                    </label>
                                </div>
                            <?php endif; ?>
                            <input type="file" name="site_logo" accept="image/png,image/jpeg,image/webp,image/svg+xml,image/gif" class="as-file-input">
                        </div>

                        <!-- Favicon Upload -->
                        <div class="as-upload-box">
                            <div class="as-upload-head">
                                <strong>Browser Favicon</strong>
                                <span class="as-text-muted as-text-xs">ICO, PNG, SVG (Max 2MB)</span>
                            </div>
                            <?php if (!empty($settings['site_favicon'])): ?>
                                <div class="as-thumb-preview-wrap">
                                    <img src="<?= e(asset($settings['site_favicon'])) ?>" alt="Current Favicon" class="as-favicon-preview" />
                                    <label class="as-remove-label">
                                        <input type="checkbox" name="remove_favicon" value="1"> Remove Favicon
                                    </label>
                                </div>
                            <?php endif; ?>
                            <input type="file" name="site_favicon" accept="image/x-icon,image/vnd.microsoft.icon,image/png,image/svg+xml" class="as-file-input">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ══════════════════════════════════════════════════════════════
             TAB 3: PAYMENTS, FEES & CURRENCY
             ══════════════════════════════════════════════════════════════ -->
        <div id="tab-payments" class="as-tab-pane" role="tabpanel">
            <div class="as-card">
                <div class="as-card-header">
                    <div class="as-card-title-group">
                        <span class="as-card-icon">💳</span>
                        <div>
                            <h2 class="as-card-title">Payment Gateways &amp; Fee Architecture</h2>
                            <p class="as-card-sub">Manage active checkout processors, API integration credentials, currency modes, and platform commission percentages.</p>
                        </div>
                    </div>
                </div>

                <div class="as-card-body">
                    <!-- Gateway Selection Checklist -->
                    <div class="as-field">
                        <label class="as-field-label">Active Checkout Gateways (Buyer Visible)</label>
                        <div class="as-gateways-checklist">
                            <label class="as-checkbox-pill">
                                <input type="checkbox" name="enabled_gateways[]" value="paystack" <?= in_array('paystack', $enabledGateways) ? 'checked' : '' ?>>
                                <span>🟢 Paystack (Cards, NUBAN, USSD)</span>
                            </label>
                            <label class="as-checkbox-pill">
                                <input type="checkbox" name="enabled_gateways[]" value="stripe" <?= in_array('stripe', $enabledGateways) ? 'checked' : '' ?>>
                                <span>🟣 Stripe (International Cards)</span>
                            </label>
                            <label class="as-checkbox-pill">
                                <input type="checkbox" name="enabled_gateways[]" value="flutterwave" <?= in_array('flutterwave', $enabledGateways) ? 'checked' : '' ?>>
                                <span>🟠 Flutterwave Gateway</span>
                            </label>
                        </div>
                    </div>

                    <!-- API Keys Section -->
                    <div class="as-api-keys-wrap as-mt-6">
                        <h3 class="as-subheading">🔐 Payment Gateway API Credentials</h3>
                        <p class="as-subheading-desc">Saved keys supersede <code>.env</code> values. Leave blank to inherit server environment defaults.</p>

                        <div class="as-api-keys-grid">
                            <!-- Paystack -->
                            <div class="as-key-card">
                                <div class="as-key-card-head">
                                    <strong>🟢 Paystack Credentials</strong>
                                </div>
                                <div class="as-field as-mt-2">
                                    <label class="as-field-label">Public Key</label>
                                    <input type="text" name="paystack_public_key" value="<?= e($settings['paystack_public_key'] ?? '') ?>" placeholder="pk_test_xxxxxxxxxxxx" class="as-input as-font-mono" autocomplete="off" spellcheck="false">
                                </div>
                                <div class="as-field as-mt-2">
                                    <label class="as-field-label">Secret Key</label>
                                    <div class="as-secret-wrap">
                                        <input type="password" name="paystack_secret_key" id="paystack_secret_key_input" value="<?= e($settings['paystack_secret_key'] ?? '') ?>" placeholder="sk_test_xxxxxxxxxxxx" class="as-input as-font-mono" autocomplete="off" spellcheck="false">
                                        <button type="button" class="as-secret-toggle" onclick="toggleKeyVisibility('paystack_secret_key_input', this)">Show</button>
                                    </div>
                                </div>
                            </div>

                            <!-- Stripe -->
                            <div class="as-key-card">
                                <div class="as-key-card-head">
                                    <strong>🟣 Stripe Credentials</strong>
                                </div>
                                <div class="as-field as-mt-2">
                                    <label class="as-field-label">Public Key</label>
                                    <input type="text" name="stripe_public_key" value="<?= e($settings['stripe_public_key'] ?? '') ?>" placeholder="pk_test_xxxxxxxxxxxx" class="as-input as-font-mono" autocomplete="off" spellcheck="false">
                                </div>
                                <div class="as-field as-mt-2">
                                    <label class="as-field-label">Secret Key</label>
                                    <div class="as-secret-wrap">
                                        <input type="password" name="stripe_secret_key" id="stripe_secret_key_input" value="<?= e($settings['stripe_secret_key'] ?? '') ?>" placeholder="sk_test_xxxxxxxxxxxx" class="as-input as-font-mono" autocomplete="off" spellcheck="false">
                                        <button type="button" class="as-secret-toggle" onclick="toggleKeyVisibility('stripe_secret_key_input', this)">Show</button>
                                    </div>
                                </div>
                            </div>

                            <!-- Flutterwave -->
                            <div class="as-key-card">
                                <div class="as-key-card-head">
                                    <strong>🟠 Flutterwave Credentials</strong>
                                </div>
                                <div class="as-field as-mt-2">
                                    <label class="as-field-label">Public Key</label>
                                    <input type="text" name="flutterwave_public_key" value="<?= e($settings['flutterwave_public_key'] ?? '') ?>" placeholder="FLWPUBK_TEST-xxxx" class="as-input as-font-mono" autocomplete="off" spellcheck="false">
                                </div>
                                <div class="as-field as-mt-2">
                                    <label class="as-field-label">Secret Key</label>
                                    <div class="as-secret-wrap">
                                        <input type="password" name="flutterwave_secret_key" id="flutterwave_secret_key_input" value="<?= e($settings['flutterwave_secret_key'] ?? '') ?>" placeholder="FLWSECK_TEST-xxxx" class="as-input as-font-mono" autocomplete="off" spellcheck="false">
                                        <button type="button" class="as-secret-toggle" onclick="toggleKeyVisibility('flutterwave_secret_key_input', this)">Show</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Fees & Currency Architecture -->
                    <div class="as-grid-3 as-mt-6">
                        <div class="as-field">
                            <label class="as-field-label">Default Checkout Gateway</label>
                            <select name="default_gateway" class="as-select">
                                <option value="paystack" <?= ($settings['default_gateway'] ?? '') === 'paystack' ? 'selected' : '' ?>>Paystack Gateway</option>
                                <option value="stripe" <?= ($settings['default_gateway'] ?? '') === 'stripe' ? 'selected' : '' ?>>Stripe Payments</option>
                                <option value="flutterwave" <?= ($settings['default_gateway'] ?? '') === 'flutterwave' ? 'selected' : '' ?>>Flutterwave Gateway</option>
                            </select>
                        </div>

                        <div class="as-field">
                            <label class="as-field-label">Platform Commission Fee (%)</label>
                            <input type="number" name="platform_fee" value="<?= e($settings['platform_fee'] ?? '5') ?>" min="0" max="100" step="0.1" class="as-input" required>
                        </div>

                        <div class="as-field">
                            <label class="as-field-label">Extra Service Charge (<?= e(get_currency_symbol()) ?>)</label>
                            <input type="number" name="extra_charge" value="<?= e($settings['extra_charge'] ?? '100.00') ?>" min="0" step="0.01" class="as-input" required>
                        </div>

                        <div class="as-field">
                            <label class="as-field-label">Primary Currency Mode</label>
                            <select name="currency" class="as-select">
                                <option value="NGN" <?= ($settings['currency'] ?? 'NGN') === 'NGN' ? 'selected' : '' ?>>Nigerian Naira (₦ NGN)</option>
                                <option value="USD" <?= ($settings['currency'] ?? 'NGN') === 'USD' ? 'selected' : '' ?>>US Dollar ($ USD)</option>
                                <option value="EUR" <?= ($settings['currency'] ?? 'NGN') === 'EUR' ? 'selected' : '' ?>>Euro (€ EUR)</option>
                                <option value="GBP" <?= ($settings['currency'] ?? 'NGN') === 'GBP' ? 'selected' : '' ?>>British Pound (£ GBP)</option>
                                <option value="BOTH" <?= ($settings['currency'] ?? 'NGN') === 'BOTH' ? 'selected' : '' ?>>Dual Currency (Naira &amp; USD)</option>
                            </select>
                        </div>

                        <div class="as-field">
                            <label class="as-field-label">Exchange Rate (1 USD to NGN)</label>
                            <input type="number" name="exchange_rate" value="<?= e($settings['exchange_rate'] ?? '1500.00') ?>" min="1" step="0.01" class="as-input" required>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ══════════════════════════════════════════════════════════════
             TAB 4: TIMEZONE, LIFECYCLE & SECURITY
             ══════════════════════════════════════════════════════════════ -->
        <div id="tab-lifecycle" class="as-tab-pane" role="tabpanel">
            <div class="as-card">
                <div class="as-card-header">
                    <div class="as-card-title-group">
                        <span class="as-card-icon">⚙️</span>
                        <div>
                            <h2 class="as-card-title">Timezone, Lifecycle &amp; Domain Management</h2>
                            <p class="as-card-sub">Platform clock synchronization, automated event completion windows, auto-payout timers, and custom domain access.</p>
                        </div>
                    </div>
                </div>

                <div class="as-card-body">
                    <div class="as-grid-2">
                        <div class="as-field">
                            <label class="as-field-label">Platform Timezone (Current Server: <?= date('H:i:s T') ?>)</label>
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
                            <select name="timezone" class="as-select">
                                <?php foreach ($commonTimezones as $tz => $label): ?>
                                    <option value="<?= e($tz) ?>" <?= $currentTz === $tz ? 'selected' : '' ?>><?= e($label) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="as-field">
                            <label class="as-field-label">Event Live Window Duration (Hours)</label>
                            <input type="number" name="event_completion_hours" value="<?= e($settings['event_completion_hours'] ?? '12') ?>" min="1" max="168" step="1" class="as-input" required>
                            <span class="as-field-hint">Hours after event start time when an event automatically transitions to Completed.</span>
                        </div>
                    </div>

                    <!-- Auto-Payout Automation Box -->
                    <div class="as-highlight-card as-mt-6">
                        <label class="as-checkbox-pill as-checkbox-pill--lg">
                            <input type="checkbox" name="auto_payout_enabled" value="1" <?= (!isset($settings['auto_payout_enabled']) || $settings['auto_payout_enabled'] === '1' || $settings['auto_payout_enabled'] === true) ? 'checked' : '' ?>>
                            <span>⚡ Auto-Request Host Payouts upon Event Completion</span>
                        </label>
                        <p class="as-text-muted as-text-sm as-mt-2">
                            When enabled, net ticket earnings (after platform commission) are automatically calculated and queued for payout once an event's completion window lapses.
                        </p>
                        <div class="as-field as-mt-3 as-max-w-sm">
                            <label class="as-field-label">Auto-Payout Delay (Minutes)</label>
                            <input type="number" name="auto_payout_delay_minutes" value="<?= e($settings['auto_payout_delay_minutes'] ?? '15') ?>" min="0" max="1440" step="1" class="as-input">
                        </div>
                    </div>

                    <!-- Custom Domains Feature Switch -->
                    <div class="as-highlight-card as-mt-4">
                        <label class="as-checkbox-pill as-checkbox-pill--lg">
                            <input type="checkbox" name="custom_domain_enabled" value="1" <?= (!empty($settings['custom_domain_enabled']) && $settings['custom_domain_enabled'] === '1') ? 'checked' : '' ?>>
                            <span>🌐 Enable Custom Domain &amp; Headless API for Hosts</span>
                        </label>
                        <p class="as-text-muted as-text-sm as-mt-2">
                            Enables organizers to connect custom CNAME domains and access headless JSON checkout endpoints.
                        </p>
                        <div class="as-field as-mt-3 as-max-w-sm">
                            <label class="as-field-label">One-Time Domain Setup Fee (<?= e(get_currency_symbol()) ?>)</label>
                            <input type="number" name="custom_domain_fee" value="<?= e($settings['custom_domain_fee'] ?? '10000.00') ?>" min="0" step="0.01" class="as-input" required>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ══════════════════════════════════════════════════════════════
             TAB 5: ANNOUNCEMENT, SEO & MARKETING
             ══════════════════════════════════════════════════════════════ -->
        <div id="tab-marketing" class="as-tab-pane" role="tabpanel">
            <div class="as-card">
                <div class="as-card-header">
                    <div class="as-card-title-group">
                        <span class="as-card-icon">📣</span>
                        <div>
                            <h2 class="as-card-title">Homepage Announcement &amp; Search Engine Optimization</h2>
                            <p class="as-card-sub">Configure celebratory modal popups, SEO search metadata, and social media channel handles.</p>
                        </div>
                    </div>
                </div>

                <div class="as-card-body">
                    <!-- Announcement Popup Configuration -->
                    <div class="as-ann-config-box">
                        <div class="as-grid-2">
                            <div class="as-field">
                                <label class="as-checkbox-pill as-checkbox-pill--lg">
                                    <input type="checkbox" name="announcement_enabled" id="ann-toggle-checkbox" value="1" <?= $isAnnLive ? 'checked' : '' ?>>
                                    <span>Enable Announcement Celebration Modal</span>
                                </label>
                            </div>
                            <div class="as-field">
                                <label class="as-field-label">Display Scope</label>
                                <select name="announcement_location" class="as-select">
                                    <option value="home" <?= ($settings['announcement_location'] ?? 'home') === 'home' ? 'selected' : '' ?>>Home Page Only (Recommended)</option>
                                    <option value="sitewide" <?= ($settings['announcement_location'] ?? '') === 'sitewide' ? 'selected' : '' ?>>Site-Wide (All Public Pages)</option>
                                </select>
                            </div>
                        </div>

                        <div class="as-grid-2 as-mt-4">
                            <div class="as-field">
                                <label class="as-field-label">Badge Tag (Eyebrow)</label>
                                <input type="text" name="announcement_badge" value="<?= e($settings['announcement_badge'] ?? '') ?>" placeholder="e.g. 🎉 Special Celebration" class="as-input">
                            </div>
                            <div class="as-field">
                                <label class="as-field-label">Headline Title</label>
                                <input type="text" name="announcement_title" value="<?= e($settings['announcement_title'] ?? '') ?>" placeholder="e.g. Happy New Year from Eventlyy!" class="as-input">
                            </div>
                        </div>

                        <div class="as-field as-mt-4">
                            <label class="as-field-label">Announcement Body Message</label>
                            <textarea name="announcement_message" rows="3" class="as-textarea" placeholder="Write your full update, holiday message, or celebratory announcement..."><?= e($settings['announcement_message'] ?? '') ?></textarea>
                        </div>

                        <div class="as-grid-3 as-mt-4">
                            <div class="as-field">
                                <label class="as-field-label">Action Button Text</label>
                                <input type="text" name="announcement_link_text" value="<?= e($settings['announcement_link_text'] ?? '') ?>" placeholder="e.g. Explore Events →" class="as-input">
                            </div>
                            <div class="as-field">
                                <label class="as-field-label">Action Button Link URL</label>
                                <input type="text" name="announcement_link_url" value="<?= e($settings['announcement_link_url'] ?? '') ?>" placeholder="e.g. /events" class="as-input">
                            </div>
                            <div class="as-field">
                                <label class="as-field-label">Dismiss Button Text</label>
                                <input type="text" name="announcement_dismiss_text" value="<?= e($settings['announcement_dismiss_text'] ?? 'Continue to Site') ?>" class="as-input">
                            </div>
                        </div>

                        <div class="as-mt-4">
                            <button type="button" class="as-btn as-btn-outline" onclick="window.openAnnouncementPreview(event)">
                                👁️ Test / Preview Modal Popup
                            </button>
                        </div>
                    </div>

                    <!-- SEO Metadata -->
                    <div class="as-field as-mt-6">
                        <h3 class="as-subheading">🔍 Search Engine Optimization (SEO)</h3>
                        <div class="as-field as-mt-3">
                            <label class="as-field-label">Meta Description</label>
                            <textarea name="meta_description" rows="2" class="as-textarea" placeholder="Default search engine snippet describing Eventlyy..."><?= e($settings['meta_description'] ?? '') ?></textarea>
                        </div>
                        <div class="as-field as-mt-3">
                            <label class="as-field-label">Meta Keywords</label>
                            <input type="text" name="meta_keywords" value="<?= e($settings['meta_keywords'] ?? '') ?>" placeholder="events, concerts, ticketing, festival booking" class="as-input">
                        </div>
                    </div>

                    <!-- Social Channels -->
                    <div class="as-field as-mt-6">
                        <h3 class="as-subheading">🌐 Social Media Channel Links</h3>
                        <div class="as-grid-3 as-mt-3">
                            <div class="as-field">
                                <label class="as-field-label">Twitter / X URL</label>
                                <input type="url" name="social_twitter" value="<?= e($settings['social_twitter'] ?? '') ?>" placeholder="https://x.com/yourhandle" class="as-input">
                            </div>
                            <div class="as-field">
                                <label class="as-field-label">Instagram URL</label>
                                <input type="url" name="social_instagram" value="<?= e($settings['social_instagram'] ?? '') ?>" placeholder="https://instagram.com/yourhandle" class="as-input">
                            </div>
                            <div class="as-field">
                                <label class="as-field-label">Facebook URL</label>
                                <input type="url" name="social_facebook" value="<?= e($settings['social_facebook'] ?? '') ?>" placeholder="https://facebook.com/yourpage" class="as-input">
                            </div>
                            <div class="as-field">
                                <label class="as-field-label">LinkedIn URL</label>
                                <input type="url" name="social_linkedin" value="<?= e($settings['social_linkedin'] ?? '') ?>" placeholder="https://linkedin.com/company/yourbrand" class="as-input">
                            </div>
                            <div class="as-field">
                                <label class="as-field-label">YouTube URL</label>
                                <input type="url" name="social_youtube" value="<?= e($settings['social_youtube'] ?? '') ?>" placeholder="https://youtube.com/@yourchannel" class="as-input">
                            </div>
                            <div class="as-field">
                                <label class="as-field-label">TikTok URL</label>
                                <input type="url" name="social_tiktok" value="<?= e($settings['social_tiktok'] ?? '') ?>" placeholder="https://tiktok.com/@yourhandle" class="as-input">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ─── STICKY BOTTOM SAVE ACTIONS BAR ─── -->
        <div class="as-save-bar">
            <div class="as-save-bar-info">
                <span>Save all configuration updates to platform memory</span>
            </div>
            <button type="submit" class="as-btn as-btn-primary as-btn-lg">
                <span>💾</span> Save All Settings
            </button>
        </div>
    </form>

</div>

<!-- ═══ LIVE ANNOUNCEMENT PREVIEW MODAL ═══ -->
<div id="admin-announcement-preview-modal" class="as-modal-backdrop" style="display:none;">
    <div class="as-modal-backdrop-hit" onclick="window.closeAnnouncementPreview()"></div>
    <div id="prev-ann-card" class="as-ann-card">
        <button type="button" onclick="window.closeAnnouncementPreview()" class="as-ann-close" aria-label="Close modal">&times;</button>
        
        <div id="prev-ann-media" class="as-ann-media" style="display:none;">
            <img id="prev-ann-img" src="" alt="Banner Preview" class="as-ann-img">
        </div>

        <div id="prev-ann-body" class="as-ann-body">
            <span id="prev-ann-badge" class="as-badge as-badge--brand">🎉 Special Announcement</span>
            <h2 id="prev-ann-title" class="as-ann-title">Announcement Title</h2>
            <div id="prev-ann-msg" class="as-ann-msg">
                Announcement message will appear here.
            </div>
            <div id="prev-ann-actions-wrap" class="as-ann-actions">
                <a href="javascript:void(0)" id="prev-ann-action-btn" class="as-btn as-btn-primary">Explore Events →</a>
                <button type="button" id="btn-dismiss-ann-preview" onclick="window.closeAnnouncementPreview()" class="as-btn as-btn-ghost">Continue to Site</button>
            </div>
        </div>
    </div>
</div>

<!-- ─── MODERN RESPONSIVE STYLES ─── -->
<style>
.as-shell {
    --as-primary: var(--primary, <?= $themePrimary ?>);
    --as-primary-hover: var(--primary-hover, <?= $themeSecondary ?>);
    --as-primary-light: rgba(var(--primary-rgb, <?= $themePrimaryRgb ?>), 0.12);
    --as-primary-border: rgba(var(--primary-rgb, <?= $themePrimaryRgb ?>), 0.28);
    
    /* Default / Light Mode */
    --as-bg-card: rgb(var(--card, 255 255 255));
    --as-bg-subtle: #f8fafc;
    --as-bg-subtle-hover: #f1f5f9;
    --as-border: var(--border-color, #e2e8f0);
    --as-text-main: rgb(var(--text, 15 23 42));
    --as-text-muted: rgb(var(--muted, 100 116 139));
    --as-pill-bg: #f1f5f9;
    --as-pill-text: #475569;
    --as-input-bg: #ffffff;
    --as-input-border: var(--border-color, #cbd5e1);
    --as-radius: 12px;
    --as-radius-sm: 8px;
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
    margin-bottom: 4rem;
}

/* ─── FULL DARK MODE SUPPORT ─── */
:root[data-theme='dark'] .as-shell,
[data-theme='dark'] .as-shell,
body[data-theme='dark'] .as-shell {
    --as-bg-card: rgb(var(--card, 18 21 27));
    --as-bg-subtle: rgba(255, 255, 255, 0.04);
    --as-bg-subtle-hover: rgba(255, 255, 255, 0.08);
    --as-border: rgba(255, 255, 255, 0.09);
    --as-text-main: rgb(var(--text, 243 245 247));
    --as-text-muted: rgb(var(--muted, 152 162 173));
    --as-pill-bg: rgba(255, 255, 255, 0.08);
    --as-pill-text: #e2e8f0;
    --as-input-bg: rgba(255, 255, 255, 0.05);
    --as-input-border: rgba(255, 255, 255, 0.12);
    --as-primary-light: rgba(var(--primary-rgb, <?= $themePrimaryRgb ?>), 0.22);
    --as-primary-border: rgba(var(--primary-rgb, <?= $themePrimaryRgb ?>), 0.4);
}

/* Header & Breadcrumbs */
.as-header-card {
    background: var(--as-bg-card);
    border: 1px solid var(--as-border);
    border-radius: var(--as-radius);
    padding: 1.5rem;
    box-shadow: 0 1px 3px rgba(0,0,0,0.04);
}
:root[data-theme='dark'] .as-header-card,
[data-theme='dark'] .as-header-card {
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.35);
}
.as-header-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 0.75rem;
    margin-bottom: 0.75rem;
}
.as-breadcrumb {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    font-size: 0.85rem;
    color: var(--as-text-muted);
}
.as-breadcrumb a {
    color: var(--as-primary);
    text-decoration: none;
    font-weight: 500;
}
.as-breadcrumb a:hover {
    text-decoration: underline;
}
.as-bc-sep {
    color: var(--as-border);
}
.as-bc-current {
    color: var(--as-text-main);
    font-weight: 600;
}
.as-header-badges {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 0.5rem;
}
.as-header-body {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 1.5rem;
    flex-wrap: wrap;
}
.as-header-info {
    flex: 1 1 500px;
}
.as-page-title {
    font-size: 1.75rem;
    font-weight: 800;
    color: var(--as-text-main);
    line-height: 1.25;
    margin: 0 0 0.35rem 0;
}
.as-page-desc {
    font-size: 0.9rem;
    color: var(--as-text-muted);
    margin: 0;
}
.as-header-actions {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex-wrap: wrap;
}

/* Shortcuts Grid */
.as-shortcuts-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
    gap: 0.75rem;
}
.as-shortcut-tile {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.85rem 1rem;
    background: var(--as-bg-card);
    border: 1px solid var(--as-border);
    border-radius: var(--as-radius-sm);
    text-decoration: none;
    transition: all 0.15s ease;
    box-shadow: 0 1px 2px rgba(0,0,0,0.03);
}
:root[data-theme='dark'] .as-shortcut-tile,
[data-theme='dark'] .as-shortcut-tile {
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.25);
}
.as-shortcut-tile:hover {
    transform: translateY(-2px);
    border-color: var(--as-primary);
}
.as-shortcut-icon {
    font-size: 1.35rem;
}
.as-shortcut-tile strong {
    display: block;
    color: var(--as-text-main);
    font-size: 0.88rem;
}
.as-shortcut-tile span {
    font-size: 0.76rem;
    color: var(--as-text-muted);
}
.as-btn-cache-purge {
    margin-top: 0.25rem;
    padding: 0.25rem 0.55rem;
    font-size: 0.74rem;
    font-weight: 600;
    border-radius: 4px;
    background: #ef4444;
    color: #ffffff;
    border: none;
    cursor: pointer;
}
.as-btn-cache-purge:hover {
    background: #dc2626;
}

/* Tabs Bar */
.as-tabs-bar {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    border-bottom: 2px solid var(--as-border);
    overflow-x: auto;
    padding-bottom: 2px;
    -webkit-overflow-scrolling: touch;
}
.as-tab-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.25rem;
    background: transparent;
    border: none;
    border-bottom: 3px solid transparent;
    font-size: 0.92rem;
    font-weight: 600;
    color: var(--as-text-muted);
    cursor: pointer;
    white-space: nowrap;
    transition: all 0.2s ease;
    margin-bottom: -2px;
}
.as-tab-btn:hover {
    color: var(--as-text-main);
    background: var(--as-bg-subtle);
    border-radius: var(--as-radius-sm) var(--as-radius-sm) 0 0;
}
.as-tab-btn.active {
    color: var(--as-primary);
    border-bottom-color: var(--as-primary);
}
.as-tab-icon {
    font-size: 1.1rem;
}
.as-tab-pane {
    display: none;
}
.as-tab-pane.active {
    display: block;
    animation: asFadeIn 0.2s ease-in-out;
}
@keyframes asFadeIn {
    from { opacity: 0; transform: translateY(4px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Cards */
.as-card {
    background: var(--as-bg-card);
    border: 1px solid var(--as-border);
    border-radius: var(--as-radius);
    box-shadow: 0 1px 3px rgba(0,0,0,0.03);
    overflow: hidden;
}
:root[data-theme='dark'] .as-card,
[data-theme='dark'] .as-card {
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.35);
}
.as-card-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 1rem;
    padding: 1.25rem 1.5rem;
    border-bottom: 1px solid var(--as-border);
    background: var(--as-bg-subtle);
}
.as-card-title-group {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}
.as-card-icon {
    font-size: 1.35rem;
}
.as-card-title {
    font-size: 1.15rem;
    font-weight: 700;
    color: var(--as-text-main);
    margin: 0;
}
.as-card-sub {
    font-size: 0.82rem;
    color: var(--as-text-muted);
    margin: 0.2rem 0 0 0;
}
.as-card-body {
    padding: 1.5rem;
}

/* Presets */
.as-presets-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
    gap: 0.65rem;
    margin-top: 0.5rem;
}
.as-preset-pill {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.55rem 0.85rem;
    background: var(--as-bg-subtle);
    border: 1px solid var(--as-border);
    border-radius: var(--as-radius-sm);
    font-size: 0.82rem;
    font-weight: 600;
    color: var(--as-text-main);
    cursor: pointer;
    transition: all 0.15s ease;
}
.as-preset-pill:hover {
    background: var(--as-bg-subtle-hover);
    border-color: var(--as-primary);
}
.as-preset-pill.is-active {
    border-color: var(--as-primary);
    background: var(--as-primary-light);
    color: var(--as-primary);
}
.as-preset-swatch {
    display: flex;
    width: 22px;
    height: 22px;
    border-radius: 50%;
    overflow: hidden;
    flex-shrink: 0;
    border: 1px solid rgba(0,0,0,0.1);
}
.as-preset-swatch span {
    flex: 1;
    height: 100%;
}
.as-preset-check {
    margin-left: auto;
    font-size: 0.85rem;
    opacity: 0;
}
.as-preset-pill.is-active .as-preset-check {
    opacity: 1;
}

/* Theme Controls Grid */
.as-theme-controls-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.5rem;
    margin-top: 1.5rem;
    align-items: start;
}
.as-color-inputs-box {
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
}
.as-color-field {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}
.as-color-input-wrap {
    display: flex;
    align-items: center;
    gap: 0.65rem;
    margin-top: 0.35rem;
}
.as-swatch-picker {
    width: 44px;
    height: 44px;
    padding: 2px;
    border: 1px solid var(--as-border);
    border-radius: var(--as-radius-sm);
    background: var(--as-bg-card);
    cursor: pointer;
}

/* Live Preview Card */
.as-live-preview-card {
    background: var(--as-bg-card);
    border: 1px solid var(--as-primary);
    border-radius: var(--as-radius);
    padding: 1.25rem;
    box-shadow: 0 10px 25px -5px rgba(0,0,0,0.08);
}
.as-preview-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 0.75rem;
}
.as-preview-title {
    font-size: 1.15rem;
    font-weight: 700;
    color: var(--as-text-main);
    margin: 0 0 0.4rem 0;
}
.as-preview-desc {
    font-size: 0.84rem;
    color: var(--as-text-muted);
    line-height: 1.5;
    margin: 0 0 1rem 0;
}
.as-preview-actions {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    flex-wrap: wrap;
}

/* Form Controls */
.as-grid-2 {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.25rem;
}
.as-grid-3 {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 1.25rem;
}
.as-field {
    display: flex;
    flex-direction: column;
    gap: 0.35rem;
}
.as-field-label {
    font-size: 0.84rem;
    font-weight: 600;
    color: var(--as-text-main);
}
.as-field-hint {
    font-size: 0.76rem;
    color: var(--as-text-muted);
}
.as-input,
.as-select,
.as-textarea {
    width: 100%;
    padding: 0.65rem 0.95rem;
    border-radius: var(--as-radius-sm);
    border: 1px solid var(--as-input-border);
    background: var(--as-input-bg);
    color: var(--as-text-main);
    font-size: 0.9rem;
    font-family: inherit;
    box-sizing: border-box;
    transition: all 0.15s ease;
}
.as-input:focus,
.as-select:focus,
.as-textarea:focus {
    outline: none;
    border-color: var(--as-primary);
    box-shadow: 0 0 0 3px var(--as-primary-light);
}

/* Uploads */
.as-upload-box {
    background: var(--as-bg-subtle);
    border: 1px solid var(--as-border);
    border-radius: var(--as-radius-sm);
    padding: 1.25rem;
}
.as-upload-head {
    display: flex;
    flex-direction: column;
    gap: 0.2rem;
    margin-bottom: 0.85rem;
}
.as-upload-head strong {
    color: var(--as-text-main);
    font-size: 0.92rem;
}
.as-thumb-preview-wrap {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 0.85rem;
    background: var(--as-bg-card);
    padding: 0.5rem;
    border-radius: 6px;
    border: 1px solid var(--as-border);
}
.as-logo-preview {
    max-height: 48px;
    max-width: 160px;
    object-fit: contain;
}
.as-favicon-preview {
    width: 32px;
    height: 32px;
    object-fit: contain;
}
.as-remove-label {
    font-size: 0.8rem;
    color: #ef4444;
    cursor: pointer;
}
.as-file-input {
    font-size: 0.84rem;
    color: var(--as-text-muted);
}

/* Gateways & Keys */
.as-gateways-checklist {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
    margin-top: 0.5rem;
}
.as-checkbox-pill {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.65rem 1rem;
    background: var(--as-bg-subtle);
    border: 1px solid var(--as-border);
    border-radius: var(--as-radius-sm);
    font-size: 0.86rem;
    font-weight: 600;
    color: var(--as-text-main);
    cursor: pointer;
}
.as-checkbox-pill input {
    accent-color: var(--as-primary);
}
.as-api-keys-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 1rem;
    margin-top: 0.75rem;
}
.as-key-card {
    background: var(--as-bg-subtle);
    border: 1px solid var(--as-border);
    border-radius: var(--as-radius-sm);
    padding: 1.15rem;
}
.as-key-card-head strong {
    font-size: 0.92rem;
    color: var(--as-text-main);
}
.as-secret-wrap {
    display: flex;
    align-items: center;
    gap: 0.4rem;
}
.as-secret-toggle {
    padding: 0.65rem 0.85rem;
    border: 1px solid var(--as-input-border);
    background: var(--as-bg-card);
    color: var(--as-text-main);
    border-radius: var(--as-radius-sm);
    font-size: 0.8rem;
    font-weight: 600;
    cursor: pointer;
}

/* Highlight Cards */
.as-highlight-card {
    background: var(--as-bg-subtle);
    border: 1px solid var(--as-border);
    border-radius: var(--as-radius-sm);
    padding: 1.25rem;
}
.as-subheading {
    font-size: 1rem;
    font-weight: 700;
    color: var(--as-text-main);
    margin: 0 0 0.2rem 0;
}
.as-subheading-desc {
    font-size: 0.8rem;
    color: var(--as-text-muted);
    margin: 0;
}

/* Sticky Save Actions Bar */
.as-save-bar {
    position: sticky;
    bottom: 1rem;
    z-index: 100;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    padding: 1rem 1.5rem;
    background: var(--as-bg-card);
    border: 1px solid var(--as-border);
    border-radius: var(--as-radius);
    box-shadow: 0 10px 25px -5px rgba(0,0,0,0.15), 0 8px 10px -6px rgba(0,0,0,0.1);
    margin-top: 1.5rem;
}
:root[data-theme='dark'] .as-save-bar,
[data-theme='dark'] .as-save-bar {
    box-shadow: 0 12px 30px rgba(0, 0, 0, 0.5);
}
.as-save-bar-info {
    font-size: 0.86rem;
    color: var(--as-text-muted);
    font-weight: 500;
}

/* Buttons */
.as-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.4rem;
    padding: 0.55rem 1rem;
    border-radius: var(--as-radius-sm);
    font-size: 0.85rem;
    font-weight: 600;
    text-decoration: none;
    border: 1px solid transparent;
    cursor: pointer;
    transition: all 0.15s ease-in-out;
    white-space: nowrap;
}
.as-btn-primary {
    background: var(--as-primary);
    color: #ffffff !important;
}
.as-btn-primary:hover {
    background: var(--as-primary-hover);
    box-shadow: 0 2px 8px rgba(var(--primary-rgb, <?= $themePrimaryRgb ?>), 0.35);
}
.as-btn-lg {
    padding: 0.75rem 1.75rem;
    font-size: 0.95rem;
    border-radius: 10px;
}
.as-btn-outline {
    background: transparent;
    color: var(--as-primary);
    border-color: var(--as-primary-border);
}
.as-btn-outline:hover {
    background: var(--as-primary-light);
}
.as-btn-ghost {
    background: transparent;
    color: var(--as-text-muted);
}
.as-btn-ghost:hover {
    background: var(--as-bg-subtle);
    color: var(--as-text-main);
}

/* Badges */
.as-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    padding: 0.2rem 0.55rem;
    border-radius: 9999px;
    font-size: 0.74rem;
    font-weight: 600;
}
.as-badge--brand {
    background: var(--as-primary-light);
    color: var(--as-primary);
}
.as-badge--success {
    background: #ecfdf5;
    color: #059669;
}
:root[data-theme='dark'] .as-badge--success,
[data-theme='dark'] .as-badge--success {
    background: rgba(16, 185, 129, 0.15);
    color: #34d399;
}
.as-badge--warning {
    background: #fffbeb;
    color: #d97706;
}
:root[data-theme='dark'] .as-badge--warning,
[data-theme='dark'] .as-badge--warning {
    background: rgba(245, 158, 11, 0.15);
    color: #fbbf24;
}

/* Utilities */
.as-mt-2 { margin-top: 0.5rem; }
.as-mt-3 { margin-top: 0.75rem; }
.as-mt-4 { margin-top: 1rem; }
.as-mt-6 { margin-top: 1.5rem; }
.as-inline { display: inline; }
.as-w-full { width: 100%; }
.as-max-w-sm { max-width: 320px; }
.as-text-xs { font-size: 0.74rem; }
.as-text-sm { font-size: 0.82rem; }
.as-text-muted { color: var(--as-text-muted); }
.as-font-mono { font-family: ui-monospace, monospace; }

/* Announcement Modal Backdrop */
.as-modal-backdrop {
    position: fixed;
    inset: 0;
    z-index: 10000;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1rem;
    background: rgba(15, 23, 42, 0.7);
    backdrop-filter: blur(4px);
}
.as-modal-backdrop-hit {
    position: absolute;
    inset: 0;
}
.as-ann-card {
    position: relative;
    background: var(--as-bg-card);
    border: 1px solid var(--as-border);
    border-radius: var(--as-radius);
    max-width: 520px;
    width: 100%;
    box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25);
    overflow: hidden;
    z-index: 1;
}
.as-ann-close {
    position: absolute;
    right: 1rem;
    top: 1rem;
    background: rgba(0,0,0,0.3);
    color: #ffffff;
    border: none;
    border-radius: 50%;
    width: 30px;
    height: 30px;
    font-size: 1.1rem;
    cursor: pointer;
    z-index: 2;
}
.as-ann-media {
    width: 100%;
    height: 200px;
}
.as-ann-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.as-ann-body {
    padding: 1.5rem;
}
.as-ann-title {
    font-size: 1.25rem;
    font-weight: 800;
    color: var(--as-text-main);
    margin: 0.5rem 0;
}
.as-ann-msg {
    font-size: 0.9rem;
    line-height: 1.6;
    color: var(--as-text-muted);
    margin-bottom: 1.25rem;
}
.as-ann-actions {
    display: flex;
    align-items: center;
    gap: 0.65rem;
    flex-wrap: wrap;
}

/* Responsive */
@media (max-width: 1024px) {
    .as-theme-controls-grid {
        grid-template-columns: 1fr;
    }
}
@media (max-width: 768px) {
    .as-grid-2,
    .as-grid-3 {
        grid-template-columns: 1fr;
    }
    .as-header-body {
        flex-direction: column;
    }
    .as-header-actions {
        width: 100%;
    }
    .as-save-bar {
        flex-direction: column;
        align-items: stretch;
    }
    .as-save-bar .as-btn {
        width: 100%;
    }
}
</style>

<!-- ─── JAVASCRIPT: TABS, PRESETS & LIVE PREVIEW ─── -->
<script>
// ─── 1. Tab Navigation ───
document.addEventListener('DOMContentLoaded', function() {
    const tabBtns = document.querySelectorAll('.as-tab-btn');
    const tabPanes = document.querySelectorAll('.as-tab-pane');

    function activateTab(targetId) {
        if (!targetId) return;
        const targetBtn = document.querySelector(`.as-tab-btn[data-tab="${targetId}"]`);
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

    if (window.location.hash) {
        const hash = window.location.hash.replace('#', '');
        const mappedTabId = 'tab-' + hash;
        if (document.getElementById(mappedTabId)) {
            activateTab(mappedTabId);
        }
    }
});

// ─── 2. Password Secret Key Visibility Toggle ───
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

// ─── 3. Palette Presets & Color Synchronizer ───
function syncFromColorPicker(type, hex) {
    var textInput = document.getElementById(type + '-color-text');
    if (textInput) textInput.value = hex.toLowerCase();
    markCustomPreset();
    updateThemeLivePreview();
}

function syncFromTextInput(type, hex) {
    var picker = document.getElementById(type + '-color-picker');
    if (/^#[0-9a-fA-F]{6}$/.test(hex)) {
        if (picker) picker.value = hex;
        markCustomPreset();
        updateThemeLivePreview();
    }
}

function applyPreset(btn) {
    var primary = btn.getAttribute('data-primary');
    var secondary = btn.getAttribute('data-secondary');
    var preset = btn.getAttribute('data-preset');

    var pPicker = document.getElementById('primary-color-picker');
    var pText = document.getElementById('primary-color-text');
    var sPicker = document.getElementById('secondary-color-picker');
    var sText = document.getElementById('secondary-color-text');
    var presetInput = document.getElementById('theme-preset-input');

    if (pPicker) pPicker.value = primary;
    if (pText) pText.value = primary;
    if (sPicker) sPicker.value = secondary;
    if (sText) sText.value = secondary;
    if (presetInput) presetInput.value = preset;

    var pills = document.querySelectorAll('.as-preset-pill');
    pills.forEach(p => p.classList.remove('is-active'));
    btn.classList.add('is-active');

    updateThemeLivePreview();
}

function markCustomPreset() {
    var presetInput = document.getElementById('theme-preset-input');
    if (presetInput) presetInput.value = 'custom';
    var pills = document.querySelectorAll('.as-preset-pill');
    pills.forEach(p => p.classList.remove('is-active'));
}

function resetThemeColors() {
    var defaultEmeraldBtn = document.querySelector('.as-preset-pill[data-preset="emerald"]');
    if (defaultEmeraldBtn) applyPreset(defaultEmeraldBtn);
}

function updateThemeLivePreview() {
    var pText = document.getElementById('primary-color-text');
    var sText = document.getElementById('secondary-color-text');
    var previewCard = document.getElementById('theme-live-preview-box');
    var previewBadge = document.getElementById('preview-badge');
    var previewBadgePrimary = document.getElementById('preview-badge-primary');
    var previewBtn = document.getElementById('preview-btn-primary');
    var previewBtnSec = document.getElementById('preview-btn-secondary');
    var previewOutline = document.getElementById('preview-btn-outline');
    var previewBar = document.getElementById('preview-bar');
    var previewLbl1 = document.getElementById('preview-label-1');
    var previewLbl2 = document.getElementById('preview-label-2');

    var primary = (pText && /^#[0-9a-fA-F]{6}$/.test(pText.value)) ? pText.value : '#108a55';
    var secondary = (sText && /^#[0-9a-fA-F]{6}$/.test(sText.value)) ? sText.value : '#1aae6c';

    if (previewCard) {
        previewCard.style.borderTop = '3px solid ' + primary;
        previewCard.style.borderBottom = '2px solid ' + secondary;
    }
    if (previewBadge) previewBadge.style.background = secondary;
    if (previewBadgePrimary) {
        previewBadgePrimary.style.color = primary;
        previewBadgePrimary.style.borderColor = primary;
    }
    if (previewBar) {
        previewBar.style.background = 'linear-gradient(90deg, ' + primary + ' 0%, ' + secondary + ' 100%)';
    }
    if (previewLbl1) previewLbl1.style.color = primary;
    if (previewLbl2) previewLbl2.style.color = secondary;
    if (previewBtn) {
        previewBtn.style.background = 'linear-gradient(135deg, ' + primary + ' 0%, ' + secondary + ' 100%)';
        previewBtn.style.boxShadow = '0 4px 14px rgba(0,0,0,0.18)';
    }
    if (previewBtnSec) {
        previewBtnSec.style.background = secondary;
    }
    if (previewOutline) {
        previewOutline.style.color = primary;
        previewOutline.style.borderColor = primary;
    }
}

// ─── 4. Live Announcement Modal Previewer ───
window.openAnnouncementPreview = function(e) {
    if (e && e.preventDefault) e.preventDefault();
    var modal = document.getElementById('admin-announcement-preview-modal');
    if (!modal) return;

    var badgeInput = document.querySelector('input[name="announcement_badge"]');
    var titleInput = document.querySelector('input[name="announcement_title"]');
    var msgInput = document.querySelector('textarea[name="announcement_message"]');
    var linkTextInput = document.querySelector('input[name="announcement_link_text"]');
    var linkUrlInput = document.querySelector('input[name="announcement_link_url"]');
    var dismissInput = document.querySelector('input[name="announcement_dismiss_text"]');

    var badgeEl = document.getElementById('prev-ann-badge');
    var titleEl = document.getElementById('prev-ann-title');
    var msgEl = document.getElementById('prev-ann-msg');
    var actionBtn = document.getElementById('prev-ann-action-btn');
    var dismissBtn = document.getElementById('btn-dismiss-ann-preview');

    if (badgeEl) badgeEl.textContent = (badgeInput && badgeInput.value.trim()) || '🎉 Special Announcement';
    if (titleEl) titleEl.textContent = (titleInput && titleInput.value.trim()) || 'Announcement Title';
    if (msgEl) msgEl.innerHTML = (msgInput && msgInput.value.trim().replace(/\n/g, '<br>')) || 'Your announcement message will appear here.';
    if (actionBtn) actionBtn.textContent = (linkTextInput && linkTextInput.value.trim()) || 'Explore Events →';
    if (dismissBtn) dismissBtn.textContent = (dismissInput && dismissInput.value.trim()) || 'Continue to Site';

    modal.style.display = 'flex';
};

window.closeAnnouncementPreview = function() {
    var modal = document.getElementById('admin-announcement-preview-modal');
    if (modal) modal.style.display = 'none';
};

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') window.closeAnnouncementPreview();
});
</script>
@endsection
