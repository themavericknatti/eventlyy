<?php

/**
 * Admin Sponsorship & Partners Tape Management
 *
 * @package Eventlyy
 * @subpackage Views/Admin
 * @version 1.0.0
 */

$siteTitle = site_setting('site_title', 'Eventlyy');
?>

@extends('layouts/admin')

@section('title')
<?= e($title ?? 'Partners & Sponsors Tape — Admin') ?>
@endsection

@section('content')
<div class="sp-admin-container">

    <!-- ═══ HEADER & CONTROLS ═══ -->
    <div class="sp-header-row">
        <div>
            <div class="sp-badge-pill">✨ Platform Sponsorship Engine</div>
            <h1 class="sp-page-title">Partners &amp; Sponsorship Tape</h1>
            <p class="sp-page-desc">
                Curate official partners and patrons. Customize text sponsors with custom color swatches and typography, or upload brand logos to stream across the live marquee tape.
            </p>
        </div>
        <div class="sp-header-actions">
            <button type="button" class="sp-btn sp-btn-secondary" onclick="openTapeSettingsModal()">
                <span>⚙️</span> Tape Settings
            </button>
            <button type="button" class="sp-btn sp-btn-primary" onclick="openCreateSponsorModal()">
                <span>➕</span> Add Sponsor
            </button>
        </div>
    </div>

    <!-- ═══ STATS STRIP ═══ -->
    <div class="sp-stats-grid">
        <div class="sp-stat-card">
            <span class="sp-stat-icon">✨</span>
            <div>
                <div class="sp-stat-value"><?= count($sponsors) ?></div>
                <div class="sp-stat-label">Total Sponsors</div>
            </div>
        </div>
        <div class="sp-stat-card" style="border-left: 4px solid #10b981;">
            <span class="sp-stat-icon" style="background:rgba(16,185,129,0.12); color:#10b981;">🟢</span>
            <div>
                <div class="sp-stat-value" id="activeSponsorsCount" style="color:#10b981;"><?= $activeCount ?></div>
                <div class="sp-stat-label">Active on Live Tape</div>
            </div>
        </div>
        <div class="sp-stat-card" style="border-left: 4px solid #8b5cf6;">
            <span class="sp-stat-icon" style="background:rgba(139,92,246,0.12); color:#8b5cf6;">🎨</span>
            <div>
                <div class="sp-stat-value" style="color:#8b5cf6;">
                    <?= (int)($textCount ?? 0) ?>
                </div>
                <div class="sp-stat-label">Custom Text Sponsors</div>
            </div>
        </div>
        <div class="sp-stat-card" style="border-left: 4px solid var(--vn-brass, #b8860b);">
            <span class="sp-stat-icon" style="background:rgba(184,134,11,0.12); color:#b8860b;">🖼️</span>
            <div>
                <div class="sp-stat-value" style="color:var(--vn-brass, #b8860b);">
                    <?= (int)($imageCount ?? 0) ?>
                </div>
                <div class="sp-stat-label">Image Logo Sponsors</div>
            </div>
        </div>
    </div>

    <!-- ═══ LIVE TAPE PREVIEW ═══ -->
    <div class="sp-card sp-preview-box">
        <div class="sp-card-header">
            <div>
                <h3 class="sp-card-title">📼 Live Marquee Tape Preview</h3>
                <p class="sp-card-subtitle">
                    Hover over any sponsor to experience the color-pop effect and pause the continuous tape.
                </p>
            </div>
            <span class="sp-tape-status-tag <?= ($tapeSettings['enabled'] ?? '1') === '1' ? 'status-active' : 'status-paused' ?>">
                <?= ($tapeSettings['enabled'] ?? '1') === '1' ? '● TAPE BROADCASTING' : '○ TAPE PAUSED' ?>
            </span>
        </div>

        <div class="sp-preview-canvas">
            @include('partials/sponsors-tape')
        </div>
    </div>

    <!-- ═══ SPONSORS ROSTER / TABLE ═══ -->
    <div class="sp-card">
        <div class="sp-card-header">
            <div>
                <h3 class="sp-card-title">📋 Partner Roster &amp; Visual Style Controls</h3>
                <p class="sp-card-subtitle">
                    Manage sponsor order, customize brand typography colors, or pause/activate partners.
                </p>
            </div>
        </div>

        <?php if (empty($sponsors)): ?>
            <div class="sp-empty-state">
                <span style="font-size:3rem; display:block; margin-bottom:0.75rem;">✨</span>
                <h3>No Sponsors Added Yet</h3>
                <p>Add your first partner or stylized text sponsor to start streaming on the homepage tape.</p>
                <button type="button" class="sp-btn sp-btn-primary" onclick="openCreateSponsorModal()" style="margin-top:1rem;">
                    ➕ Add First Sponsor
                </button>
            </div>
        <?php else: ?>
            <div class="sp-table-responsive">
                <table class="sp-table">
                    <thead>
                        <tr>
                            <th style="width: 50px;">Order</th>
                            <th>Brand / Sponsor</th>
                            <th>Type</th>
                            <th>Visual Representation</th>
                            <th>Category / Badge</th>
                            <th>Website</th>
                            <th>Status</th>
                            <th style="text-align: right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($sponsors as $s): ?>
                            <tr>
                                <td>
                                    <span class="sp-order-badge">#<?= (int)$s['sort_order'] ?></span>
                                </td>
                                <td>
                                    <strong class="sp-brand-name"><?= e($s['name']) ?></strong>
                                </td>
                                <td>
                                    <span class="sp-type-pill sp-type-<?= $s['type'] ?>">
                                        <?= $s['type'] === 'image' ? '🖼️ Logo Image' : '🔤 Stylized Text' ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="sp-preview-chip-cell">
                                        <?php if ($s['type'] === 'image' && !empty($s['image_url'])): ?>
                                            <div class="sp-chip-logo-box">
                                                <img src="<?= e(url($s['image_url'])) ?>" alt="logo" class="sp-chip-img">
                                            </div>
                                        <?php else: ?>
                                            <div class="sp-chip-text-box font-<?= $s['text_font'] ?> style-<?= $s['text_style'] ?>" style="--chip-color: <?= e($s['text_color']) ?>;">
                                                <span><?= e($s['text_content'] ?: $s['name']) ?></span>
                                            </div>
                                            <span class="sp-color-dot" style="background: <?= e($s['text_color']) ?>;" title="Color: <?= e($s['text_color']) ?>"></span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <?php if (!empty($s['badge_text'])): ?>
                                        <span class="sp-badge-tag"><?= e($s['badge_text']) ?></span>
                                    <?php else: ?>
                                        <span class="sp-text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($s['website_url'])): ?>
                                        <a href="<?= e($s['website_url']) ?>" target="_blank" rel="noopener" class="sp-link-btn" title="Visit website">
                                            <span>🔗</span> <?= parse_url($s['website_url'], PHP_URL_HOST) ?: 'Visit' ?>
                                        </a>
                                    <?php else: ?>
                                        <span class="sp-text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <form method="POST" action="{{ route('admin/sponsors/' . $s['id'] . '/toggle') }}" style="display:inline;" onsubmit="event.preventDefault(); toggleSponsorStatus(<?= $s['id'] ?>, this);">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="sp-status-btn <?= $s['is_active'] ? 'status-active' : 'status-paused' ?>">
                                            <?= $s['is_active'] ? '🟢 Active' : '⚪ Paused' ?>
                                        </button>
                                    </form>
                                </td>
                                <td style="text-align: right;">
                                    <div class="sp-actions-cell">
                                        <button type="button" class="sp-action-btn" onclick='editSponsor(<?= json_encode($s, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)' title="Edit sponsor">
                                            ✏️
                                        </button>
                                        <form method="POST" action="{{ route('admin/sponsors/' . $s['id'] . '/delete') }}" onsubmit="return confirm('Are you sure you want to remove this sponsor?');" style="display:inline;">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="sp-action-btn sp-action-btn-danger" title="Delete sponsor">
                                                🗑️
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

</div>

<!-- ══════════════════════════════════════════════════════════════════════════
     MODAL: ADD / EDIT SPONSOR
     ══════════════════════════════════════════════════════════════════════════ -->
<div class="sp-modal-backdrop" id="sponsorModalBackdrop" onclick="closeSponsorModal(event)">
    <div class="sp-modal" onclick="event.stopPropagation()">
        <div class="sp-modal-head">
            <h3 id="sponsorModalTitle">➕ Add Platform Sponsor</h3>
            <button type="button" class="sp-modal-close" onclick="closeSponsorModal()">&times;</button>
        </div>

        <form id="sponsorForm" method="POST" action="{{ route('admin/sponsors') }}" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <input type="hidden" name="id" id="sp_id" value="">

            <div class="sp-modal-body">
                <!-- Type Selection Toggle -->
                <div class="sp-field">
                    <label class="sp-label">Sponsorship Representation Mode</label>
                    <div class="sp-type-selector">
                        <label class="sp-type-radio">
                            <input type="radio" name="type" value="text" id="type_text" checked onchange="toggleTypeFields()">
                            <span class="sp-type-box">
                                <span class="sp-type-icon">🔤</span>
                                <strong>Stylized Text Brand</strong>
                                <small>Custom color, typography &amp; glowing style</small>
                            </span>
                        </label>
                        <label class="sp-type-radio">
                            <input type="radio" name="type" value="image" id="type_image" onchange="toggleTypeFields()">
                            <span class="sp-type-box">
                                <span class="sp-type-icon">🖼️</span>
                                <strong>Brand Logo Image</strong>
                                <small>Upload PNG/SVG with black &amp; white to color pop</small>
                            </span>
                        </label>
                    </div>
                </div>

                <!-- Brand Name -->
                <div class="sp-field">
                    <label class="sp-label" for="sp_name">Brand / Sponsor Name <span class="sp-required">*</span></label>
                    <input type="text" name="name" id="sp_name" class="sp-input" placeholder="e.g. Red Bull, Spotify, Heineken" required oninput="updateLivePreview()">
                </div>

                <!-- ── TEXT SPONSOR FIELDS ── -->
                <div id="textFieldsGroup">
                    <div class="sp-field">
                        <label class="sp-label" for="sp_text_content">Display Text (if different from brand name)</label>
                        <input type="text" name="text_content" id="sp_text_content" class="sp-input" placeholder="e.g. RED BULL, Spotify, HEINEKEN ★" oninput="updateLivePreview()">
                    </div>

                    <!-- COLOR PICKER & PRESETS -->
                    <div class="sp-field">
                        <label class="sp-label">Brand Text Color (Pops Out On Hover)</label>
                        <div class="sp-color-picker-row">
                            <input type="color" name="text_color" id="sp_text_color" class="sp-color-native" value="#ff1e42" oninput="syncColorInput(this.value)">
                            <input type="text" id="sp_text_color_hex" class="sp-input sp-input-hex" value="#ff1e42" maxlength="7" oninput="syncColorNative(this.value)">
                        </div>
                        <div class="sp-color-presets">
                            <button type="button" class="sp-swatch" style="background:#ff1e42;" onclick="pickColor('#ff1e42')" title="Crimson Red"></button>
                            <button type="button" class="sp-swatch" style="background:#1db954;" onclick="pickColor('#1db954')" title="Spotify Green"></button>
                            <button type="button" class="sp-swatch" style="background:#00d2ff;" onclick="pickColor('#00d2ff')" title="Cyber Cyan"></button>
                            <button type="button" class="sp-swatch" style="background:#facc15;" onclick="pickColor('#facc15')" title="Electric Yellow"></button>
                            <button type="button" class="sp-swatch" style="background:#9945ff;" onclick="pickColor('#9945ff')" title="Cyber Purple"></button>
                            <button type="button" class="sp-swatch" style="background:#ec4899;" onclick="pickColor('#ec4899')" title="Hot Pink"></button>
                            <button type="button" class="sp-swatch" style="background:#d97706;" onclick="pickColor('#d97706')" title="Burnished Gold"></button>
                            <button type="button" class="sp-swatch" style="background:#ffffff; border:1px solid #ccc;" onclick="pickColor('#ffffff')" title="Pure White"></button>
                        </div>
                    </div>

                    <!-- FONT & STYLE GRIDS -->
                    <div class="sp-grid-2">
                        <div class="sp-field">
                            <label class="sp-label" for="sp_text_font">Typography Preset</label>
                            <select name="text_font" id="sp_text_font" class="sp-select" onchange="updateLivePreview()">
                                <option value="sans">Modern Sans (Clean / Universal)</option>
                                <option value="display">Bold Display (Impact / Urban)</option>
                                <option value="serif">Luxury Serif (Cinzel / Editorial)</option>
                                <option value="mono">Tech Monospace (Cyber / Code)</option>
                                <option value="script">Vintage Script (Playful / Cursive)</option>
                            </select>
                        </div>

                        <div class="sp-field">
                            <label class="sp-label" for="sp_text_style">Text Style &amp; Weight</label>
                            <select name="text_style" id="sp_text_style" class="sp-select" onchange="updateLivePreview()">
                                <option value="bold">Bold (Standard Weight)</option>
                                <option value="black">Extra Black (Ultra Heavy 900)</option>
                                <option value="italic">Italic (Slanted)</option>
                                <option value="black_italic">Black Italic (High Energy)</option>
                                <option value="bold_caps">Uppercase Letterspaced</option>
                                <option value="neon">Neon Illumination Glow</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- ── IMAGE SPONSOR FIELDS ── -->
                <div id="imageFieldsGroup" style="display:none;">
                    <!-- Active logo preview if editing an existing uploaded sponsor -->
                    <div id="sp_current_logo_box" style="display:none;" class="sp-current-logo-card">
                        <div class="sp-current-logo-thumb">
                            <img id="sp_current_logo_img" src="" alt="current logo">
                        </div>
                        <div class="sp-current-logo-details">
                            <strong>Active Logo File</strong>
                            <small id="sp_current_logo_path">assets/img/sponsors/...</small>
                        </div>
                        <span class="sp-current-logo-status">✓ Retained</span>
                    </div>

                    <div class="sp-field">
                        <label class="sp-label" for="sp_logo_file">Upload Brand Logo Image</label>
                        <input type="file" name="logo_file" id="sp_logo_file" class="sp-file-input" accept=".png,.svg,.webp,.jpg,.jpeg" onchange="previewLogoFile(this)">
                        <span class="sp-help-text" id="sp_file_help">Transparent PNG or SVG recommended (Max 3MB). Processed securely via Ace UploadService. Leave blank to keep existing logo.</span>
                    </div>

                    <div class="sp-field">
                        <label class="sp-label" for="sp_image_url">Or Hosted Image Link / URL</label>
                        <input type="text" name="image_url" id="sp_image_url" class="sp-input" placeholder="https://example.com/logo.svg or /assets/..." oninput="previewLogoUrl(this.value)">
                        <span class="sp-help-text">Direct image URL (e.g. CDN or web SVG/PNG). Optional if logo file is uploaded above.</span>
                    </div>
                </div>

                <!-- LIVE PREVIEW CHIP (Common to both Text & Image) -->
                <div class="sp-live-preview-box">
                    <span class="sp-preview-hint">Interactive Marquee Pill Preview (Hover to preview color-pop):</span>
                    <div class="sp-preview-stage">
                        <div class="sp-tape-item font-display style-black" id="livePreviewItem">
                            <div class="sp-item-inner">
                                <span class="sp-item-badge" id="livePreviewBadge" style="display:none;">PARTNER</span>
                                <div class="sp-text-box" id="livePreviewTextBox" style="--sp-color: #ff1e42;">
                                    <span class="sp-text-label" id="livePreviewText">BRAND NAME</span>
                                </div>
                                <div class="sp-img-box" id="livePreviewImgBox" style="display:none;">
                                    <img src="" alt="logo" id="livePreviewImg" class="sp-logo-img">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- COMMON FIELDS -->
                <div class="sp-grid-2">
                    <div class="sp-field">
                        <label class="sp-label" for="sp_badge_text">Category Badge / Subtitle (Optional)</label>
                        <input type="text" name="badge_text" id="sp_badge_text" class="sp-input" placeholder="e.g. OFFICIAL AUDIO, ENERGY PARTNER" oninput="updateLivePreview()">
                    </div>

                    <div class="sp-field">
                        <label class="sp-label" for="sp_sort_order">Display Sort Order</label>
                        <input type="number" name="sort_order" id="sp_sort_order" class="sp-input" value="0" min="0">
                    </div>
                </div>

                <div class="sp-field">
                    <label class="sp-label" for="sp_website_url">Website Destination Link (Optional)</label>
                    <input type="url" name="website_url" id="sp_website_url" class="sp-input" placeholder="https://brand.com">
                    <span class="sp-help-text">Clicking this sponsor on the moving tape will open this link in a new browser tab.</span>
                </div>

                <div class="sp-field">
                    <label class="sp-checkbox-label">
                        <input type="checkbox" name="is_active" id="sp_is_active" value="1" checked>
                        <span>Activate immediately on the live marquee tape</span>
                    </label>
                </div>
            </div>

            <div class="sp-modal-foot">
                <button type="button" class="sp-btn sp-btn-secondary" onclick="closeSponsorModal()">Cancel</button>
                <button type="submit" class="sp-btn sp-btn-primary" id="saveSponsorBtn">💾 Save Sponsor</button>
            </div>
        </form>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════════════════════════
     MODAL: TAPE GLOBAL SETTINGS
     ══════════════════════════════════════════════════════════════════════════ -->
<div class="sp-modal-backdrop" id="tapeSettingsModalBackdrop" onclick="closeTapeSettingsModal(event)">
    <div class="sp-modal sp-modal-sm" onclick="event.stopPropagation()">
        <div class="sp-modal-head">
            <h3>⚙️ Marquee Tape Settings</h3>
            <button type="button" class="sp-modal-close" onclick="closeTapeSettingsModal()">&times;</button>
        </div>

        <form method="POST" action="{{ route('admin/sponsors/settings') }}">
            <?= csrf_field() ?>

            <div class="sp-modal-body">
                <div class="sp-field">
                    <label class="sp-checkbox-label">
                        <input type="checkbox" name="sponsors_tape_enabled" value="1" <?= ($tapeSettings['enabled'] ?? '1') === '1' ? 'checked' : '' ?>>
                        <strong>Broadcast Tape on Homepage &amp; About Page</strong>
                    </label>
                </div>

                <div class="sp-field">
                    <label class="sp-label" for="st_title">Marquee Header Title</label>
                    <input type="text" name="sponsors_tape_title" id="st_title" class="sp-input" value="<?= e($tapeSettings['title'] ?? 'OFFICIAL PARTNERS & PATRONS') ?>">
                </div>

                <div class="sp-field">
                    <label class="sp-label" for="st_speed">Tape Scrolling Speed</label>
                    <select name="sponsors_tape_speed" id="st_speed" class="sp-select">
                        <option value="slow" <?= ($tapeSettings['speed'] ?? '') === 'slow' ? 'selected' : '' ?>>Slow (45s loop — Relaxed)</option>
                        <option value="normal" <?= ($tapeSettings['speed'] ?? '') === 'normal' || empty($tapeSettings['speed']) ? 'selected' : '' ?>>Normal (30s loop — Standard)</option>
                        <option value="fast" <?= ($tapeSettings['speed'] ?? '') === 'fast' ? 'selected' : '' ?>>Fast (20s loop — Dynamic)</option>
                    </select>
                </div>
            </div>

            <div class="sp-modal-foot">
                <button type="button" class="sp-btn sp-btn-secondary" onclick="closeTapeSettingsModal()">Cancel</button>
                <button type="submit" class="sp-btn sp-btn-primary">Save Settings</button>
            </div>
        </form>
    </div>
</div>

<style>
/* ── Admin Sponsor Management CSS ────────────────────────────────────────── */
.sp-admin-container {
    max-width: 1240px;
    margin: 1.5rem auto 3rem;
    padding: 0 1rem;
}

.sp-header-row {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 1.5rem;
    margin-bottom: 2rem;
    flex-wrap: wrap;
}

.sp-badge-pill {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    background: rgba(139, 92, 246, 0.12);
    color: #8b5cf6;
    font-size: 0.75rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    padding: 0.25rem 0.65rem;
    border-radius: 999px;
    margin-bottom: 0.5rem;
}

.sp-page-title {
    font-size: 2rem;
    font-weight: 900;
    letter-spacing: -0.02em;
    margin: 0 0 0.5rem;
    color: var(--vn-ink, rgb(var(--text)));
}

.sp-page-desc {
    color: var(--vn-ink-muted, rgb(var(--muted)));
    font-size: 0.95rem;
    max-width: 680px;
    margin: 0;
    line-height: 1.5;
}

.sp-header-actions {
    display: flex;
    gap: 0.75rem;
    align-items: center;
}

/* Buttons */
.sp-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    padding: 0.65rem 1.25rem;
    font-size: 0.9rem;
    font-weight: 700;
    border-radius: 10px;
    border: none;
    cursor: pointer;
    text-decoration: none;
    transition: all 0.2s ease;
}

.sp-btn-primary {
    background: var(--vn-forest, #0B7366);
    color: #ffffff;
    box-shadow: 0 4px 12px rgba(11, 115, 102, 0.25);
}

.sp-btn-primary:hover {
    background: #095c52;
    transform: translateY(-1px);
}

.sp-btn-secondary {
    background: var(--vn-card-bg, rgb(var(--card)));
    border: 1px solid var(--vn-border, rgba(var(--muted), 0.18));
    color: var(--vn-ink, rgb(var(--text)));
}

.sp-btn-secondary:hover {
    background: rgba(var(--muted), 0.08);
}

/* Stats */
.sp-stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
    gap: 1rem;
    margin-bottom: 1.75rem;
}

.sp-stat-card {
    background: var(--vn-card-bg, rgb(var(--card)));
    border: 1px solid var(--vn-border, rgba(var(--muted), 0.14));
    border-radius: 12px;
    padding: 1.15rem 1.25rem;
    display: flex;
    align-items: center;
    gap: 1rem;
}

.sp-stat-icon {
    width: 44px;
    height: 44px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(var(--muted), 0.1);
    font-size: 1.3rem;
}

.sp-stat-value {
    font-size: 1.55rem;
    font-weight: 900;
    line-height: 1.1;
}

.sp-stat-label {
    font-size: 0.78rem;
    font-weight: 700;
    color: var(--vn-ink-muted, rgb(var(--muted)));
    margin-top: 0.2rem;
}

/* Cards */
.sp-card {
    background: var(--vn-card-bg, rgb(var(--card)));
    border: 1px solid var(--vn-border, rgba(var(--muted), 0.14));
    border-radius: 14px;
    padding: 1.5rem;
    margin-bottom: 1.75rem;
}

.sp-card-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 1rem;
    margin-bottom: 1.25rem;
}

.sp-card-title {
    font-size: 1.15rem;
    font-weight: 800;
    margin: 0 0 0.25rem;
}

.sp-card-subtitle {
    font-size: 0.82rem;
    color: var(--vn-ink-muted, rgb(var(--muted)));
    margin: 0;
}

.sp-tape-status-tag {
    font-size: 0.72rem;
    font-weight: 800;
    letter-spacing: 0.08em;
    padding: 0.25rem 0.65rem;
    border-radius: 999px;
}

.status-active {
    background: rgba(16, 185, 129, 0.12);
    color: #10b981;
}

.status-paused {
    background: rgba(245, 158, 11, 0.12);
    color: #f59e0b;
}

.sp-preview-canvas {
    background: rgba(0, 0, 0, 0.2);
    border-radius: 12px;
    overflow: hidden;
    padding: 0.5rem 0;
}

/* Table */
.sp-table-responsive {
    overflow-x: auto;
}

.sp-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.88rem;
}

.sp-table th {
    text-align: left;
    padding: 0.8rem 1rem;
    font-size: 0.74rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: var(--vn-ink-muted, rgb(var(--muted)));
    border-bottom: 1.5px solid var(--vn-border, rgba(var(--muted), 0.14));
}

.sp-table td {
    padding: 0.9rem 1rem;
    border-bottom: 1px solid var(--vn-border, rgba(var(--muted), 0.08));
    vertical-align: middle;
}

.sp-order-badge {
    font-family: monospace;
    font-weight: 800;
    color: var(--vn-ink-muted, rgb(var(--muted)));
}

.sp-brand-name {
    font-weight: 800;
    color: var(--vn-ink, rgb(var(--text)));
}

.sp-type-pill {
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    font-size: 0.75rem;
    font-weight: 700;
    padding: 0.2rem 0.55rem;
    border-radius: 6px;
    background: rgba(var(--muted), 0.1);
}

.sp-preview-chip-cell {
    display: flex;
    align-items: center;
    gap: 0.6rem;
}

.sp-chip-logo-box {
    width: 70px;
    height: 32px;
    display: flex;
    align-items: center;
    background: rgba(var(--muted), 0.06);
    padding: 2px 6px;
    border-radius: 6px;
}

.sp-chip-img {
    max-height: 28px;
    max-width: 60px;
    object-fit: contain;
}

.sp-chip-text-box {
    display: inline-flex;
    padding: 0.3rem 0.75rem;
    border-radius: 999px;
    background: rgba(15, 23, 42, 0.8);
    color: var(--chip-color, #ffffff);
    font-size: 0.82rem;
    font-weight: 800;
}

.sp-color-dot {
    width: 14px;
    height: 14px;
    border-radius: 50%;
    border: 1.5px solid rgba(255, 255, 255, 0.4);
    box-shadow: 0 1px 3px rgba(0,0,0,0.2);
}

.sp-badge-tag {
    font-size: 0.68rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    padding: 0.18rem 0.45rem;
    border-radius: 4px;
    background: rgba(234, 179, 8, 0.12);
    color: #d97706;
}

.sp-link-btn {
    font-size: 0.8rem;
    color: #3b82f6;
    text-decoration: none;
    font-weight: 600;
}

.sp-status-btn {
    border: none;
    font-size: 0.75rem;
    font-weight: 800;
    padding: 0.25rem 0.6rem;
    border-radius: 999px;
    cursor: pointer;
}

.sp-actions-cell {
    display: inline-flex;
    gap: 0.35rem;
}

.sp-action-btn {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    border: 1px solid var(--vn-border, rgba(var(--muted), 0.15));
    background: var(--vn-card-bg, rgb(var(--card)));
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 0.85rem;
    transition: all 0.15s ease;
}

.sp-action-btn:hover {
    background: rgba(var(--muted), 0.1);
    transform: scale(1.05);
}

.sp-action-btn-danger:hover {
    background: rgba(239, 68, 68, 0.15);
    border-color: #ef4444;
}

/* Modals */
.sp-modal-backdrop {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.65);
    backdrop-filter: blur(5px);
    -webkit-backdrop-filter: blur(5px);
    z-index: 10000;
    display: none;
    align-items: center;
    justify-content: center;
    padding: 1.25rem;
}

.sp-modal-backdrop.open {
    display: flex;
}

.sp-modal {
    background: var(--vn-card-bg, rgb(var(--card)));
    border: 1px solid var(--vn-border, rgba(var(--muted), 0.22));
    border-radius: 18px;
    width: 100%;
    max-width: 640px;
    max-height: 90vh;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    box-shadow: 0 25px 60px -10px rgba(0, 0, 0, 0.45);
    animation: spModalSlide 0.25s cubic-bezier(0.16, 1, 0.3, 1);
}

.sp-modal form {
    display: flex;
    flex-direction: column;
    flex: 1 1 auto;
    min-height: 0;
    overflow: hidden;
}

.sp-modal-sm {
    max-width: 480px;
}

@keyframes spModalSlide {
    from {
        opacity: 0;
        transform: translateY(12px) scale(0.98);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

.sp-modal-head {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.15rem 1.5rem;
    border-bottom: 1px solid var(--vn-border, rgba(var(--muted), 0.15));
    background: var(--vn-card-bg, rgb(var(--card)));
    flex-shrink: 0;
}

.sp-modal-head h3 {
    margin: 0;
    font-size: 1.15rem;
    font-weight: 800;
}

.sp-modal-close {
    background: none;
    border: none;
    font-size: 1.5rem;
    line-height: 1;
    cursor: pointer;
    color: var(--vn-ink-muted, rgb(var(--muted)));
    padding: 0.2rem 0.5rem;
    border-radius: 6px;
    transition: background 0.15s ease, color 0.15s ease;
}

.sp-modal-close:hover {
    background: rgba(var(--muted), 0.1);
}

.sp-modal-body {
    padding: 1.5rem;
    overflow-y: auto;
    flex: 1 1 auto;
    min-height: 0;
    scrollbar-width: thin;
}

.sp-modal-body::-webkit-scrollbar {
    width: 6px;
}
.sp-modal-body::-webkit-scrollbar-track {
    background: transparent;
}
.sp-modal-body::-webkit-scrollbar-thumb {
    background: rgba(var(--muted), 0.25);
    border-radius: 999px;
}

.sp-modal-foot {
    display: flex;
    justify-content: flex-end;
    align-items: center;
    gap: 0.85rem;
    padding: 1rem 1.5rem;
    border-top: 1px solid var(--vn-border, rgba(var(--muted), 0.15));
    background: var(--vn-card-bg, rgb(var(--card)));
    flex-shrink: 0;
    box-shadow: 0 -4px 14px rgba(0, 0, 0, 0.08);
}

#saveSponsorBtn {
    background: linear-gradient(135deg, #0B7366 0%, #10b981 100%);
    color: #ffffff !important;
    font-size: 0.92rem;
    font-weight: 700;
    padding: 0.65rem 1.4rem;
    border-radius: 10px;
    border: none;
    cursor: pointer;
    box-shadow: 0 4px 14px rgba(11, 115, 102, 0.35);
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    transition: transform 0.15s ease, box-shadow 0.15s ease;
}

#saveSponsorBtn:hover {
    transform: translateY(-1px);
    box-shadow: 0 6px 18px rgba(16, 185, 129, 0.45);
}

.sp-field {
    margin-bottom: 1.2rem;
}

.sp-label {
    display: block;
    font-size: 0.82rem;
    font-weight: 700;
    margin-bottom: 0.4rem;
    color: var(--vn-ink, rgb(var(--text)));
}

.sp-required {
    color: #ef4444;
}

.sp-input, .sp-select {
    width: 100%;
    padding: 0.65rem 0.85rem;
    font-size: 0.9rem;
    border-radius: 8px;
    border: 1px solid var(--vn-border, rgba(var(--muted), 0.2));
    background: var(--vn-parchment-card, rgb(var(--card)));
    color: var(--vn-ink, rgb(var(--text)));
    outline: none;
    transition: border-color 0.15s ease;
}

.sp-input:focus, .sp-select:focus {
    border-color: var(--vn-forest, #0B7366);
}

.sp-help-text {
    display: block;
    font-size: 0.74rem;
    color: var(--vn-ink-muted, rgb(var(--muted)));
    margin-top: 0.35rem;
}

.sp-grid-2 {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

/* Type selector radio cards */
.sp-type-selector {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.75rem;
}

.sp-type-radio {
    cursor: pointer;
}

.sp-type-radio input {
    display: none;
}

.sp-type-box {
    display: flex;
    flex-direction: column;
    padding: 0.9rem;
    border-radius: 10px;
    border: 2px solid var(--vn-border, rgba(var(--muted), 0.16));
    background: rgba(var(--muted), 0.03);
    transition: all 0.2s ease;
}

.sp-type-box .sp-type-icon {
    font-size: 1.35rem;
    margin-bottom: 0.3rem;
}

.sp-type-box strong {
    font-size: 0.86rem;
    margin-bottom: 0.2rem;
}

.sp-type-box small {
    font-size: 0.72rem;
    color: var(--vn-ink-muted, rgb(var(--muted)));
}

.sp-type-radio input:checked + .sp-type-box {
    border-color: var(--vn-forest, #0B7366);
    background: rgba(11, 115, 102, 0.08);
}

/* Color Picker Row */
.sp-color-picker-row {
    display: flex;
    gap: 0.75rem;
    align-items: center;
    margin-bottom: 0.5rem;
}

.sp-color-native {
    width: 44px;
    height: 40px;
    border: 1px solid var(--vn-border, rgba(var(--muted), 0.2));
    border-radius: 8px;
    padding: 2px;
    background: none;
    cursor: pointer;
}

.sp-input-hex {
    max-width: 140px;
    font-family: monospace;
    font-weight: 700;
}

.sp-color-presets {
    display: flex;
    gap: 0.45rem;
    flex-wrap: wrap;
}

.sp-swatch {
    width: 26px;
    height: 26px;
    border-radius: 6px;
    border: 1.5px solid rgba(0, 0, 0, 0.15);
    cursor: pointer;
    transition: transform 0.15s ease;
}

.sp-swatch:hover {
    transform: scale(1.15);
}

/* Live Preview Box inside Modal */
.sp-live-preview-box {
    background: rgba(15, 23, 42, 0.7);
    border: 1px solid rgba(255, 255, 255, 0.12);
    border-radius: 12px;
    padding: 1.15rem;
    margin-top: 1rem;
    user-select: none;
}

.sp-preview-hint {
    display: block;
    font-size: 0.72rem;
    color: rgba(255, 255, 255, 0.6);
    margin-bottom: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.06em;
}

.sp-preview-stage {
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 0.5rem;
}

.sp-checkbox-label {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.88rem;
    cursor: pointer;
}

/* Active Logo Card in Edit Modal */
.sp-current-logo-card {
    display: flex;
    align-items: center;
    gap: 0.85rem;
    padding: 0.75rem 1rem;
    border-radius: 10px;
    background: rgba(16, 185, 129, 0.08);
    border: 1px solid rgba(16, 185, 129, 0.28);
    margin-bottom: 1rem;
}

.sp-current-logo-thumb {
    width: 48px;
    height: 32px;
    background: rgba(0, 0, 0, 0.2);
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 2px;
    overflow: hidden;
    flex-shrink: 0;
}

.sp-current-logo-thumb img {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
}

.sp-current-logo-details {
    display: flex;
    flex-direction: column;
    flex: 1;
    min-width: 0;
}

.sp-current-logo-details strong {
    font-size: 0.82rem;
    color: var(--vn-ink, rgb(var(--text)));
}

.sp-current-logo-details small {
    font-size: 0.72rem;
    color: var(--vn-ink-muted, rgb(var(--muted)));
    word-break: break-all;
}

.sp-current-logo-status {
    font-size: 0.72rem;
    font-weight: 700;
    color: #10b981;
    background: rgba(16, 185, 129, 0.15);
    padding: 0.25rem 0.6rem;
    border-radius: 999px;
    white-space: nowrap;
}
</style>

<script>
const SPONSORS_BASE_URL = '{{ route("admin/sponsors") }}';
const CSRF_TOKEN = '<?= csrf_token() ?>';

// ── Modal & Form Handlers ────────────────────────────────────────────────
function openCreateSponsorModal() {
    document.getElementById('sponsorModalTitle').textContent = '➕ Add Platform Sponsor';
    document.getElementById('saveSponsorBtn').innerHTML = '➕ Create Sponsor';
    document.getElementById('sponsorForm').action = SPONSORS_BASE_URL;
    document.getElementById('sp_id').value = '';
    document.getElementById('sp_name').value = '';
    document.getElementById('sp_text_content').value = '';
    document.getElementById('sp_text_color').value = '#ff1e42';
    document.getElementById('sp_text_color_hex').value = '#ff1e42';
    document.getElementById('sp_text_font').value = 'sans';
    document.getElementById('sp_text_style').value = 'bold';
    document.getElementById('sp_badge_text').value = '';
    document.getElementById('sp_website_url').value = '';
    document.getElementById('sp_sort_order').value = '0';
    document.getElementById('sp_is_active').checked = true;
    document.getElementById('type_text').checked = true;
    document.getElementById('sp_logo_file').value = '';
    document.getElementById('sp_image_url').value = '';
    document.getElementById('livePreviewImg').src = '';
    document.getElementById('sp_current_logo_box').style.display = 'none';
    toggleTypeFields();
    updateLivePreview();
    document.getElementById('sponsorModalBackdrop').classList.add('open');
}

function editSponsor(sp) {
    document.getElementById('sponsorModalTitle').textContent = '✏️ Edit Sponsor — ' + sp.name;
    document.getElementById('saveSponsorBtn').innerHTML = '💾 Save Changes';
    document.getElementById('sponsorForm').action = SPONSORS_BASE_URL + '/' + sp.id + '/update';
    document.getElementById('sp_id').value = sp.id;
    document.getElementById('sp_name').value = sp.name || '';
    document.getElementById('sp_text_content').value = sp.text_content || '';
    var color = sp.text_color || '#ffffff';
    document.getElementById('sp_text_color').value = color;
    document.getElementById('sp_text_color_hex').value = color;
    document.getElementById('sp_text_font').value = sp.text_font || 'sans';
    document.getElementById('sp_text_style').value = sp.text_style || 'bold';
    document.getElementById('sp_badge_text').value = sp.badge_text || '';
    document.getElementById('sp_website_url').value = sp.website_url || '';
    document.getElementById('sp_sort_order').value = sp.sort_order || 0;
    document.getElementById('sp_is_active').checked = !!sp.is_active;
    document.getElementById('sp_logo_file').value = '';

    var img = document.getElementById('livePreviewImg');
    var currentLogoBox = document.getElementById('sp_current_logo_box');
    var currentLogoImg = document.getElementById('sp_current_logo_img');
    var currentLogoPath = document.getElementById('sp_current_logo_path');

    if (sp.type === 'image') {
        document.getElementById('type_image').checked = true;
        var isUploadedFile = sp.image_url && !sp.image_url.startsWith('http://') && !sp.image_url.startsWith('https://');

        if (isUploadedFile) {
            // It is an uploaded asset file! Show current logo badge and keep URL input blank so browser doesn't complain
            currentLogoBox.style.display = 'flex';
            currentLogoImg.src = sp.image_url.startsWith('/') ? sp.image_url : ('/' + sp.image_url);
            currentLogoPath.textContent = sp.image_url;
            document.getElementById('sp_image_url').value = '';
        } else {
            // It is an external URL
            currentLogoBox.style.display = 'none';
            document.getElementById('sp_image_url').value = sp.image_url || '';
        }

        if (sp.image_url) {
            img.src = sp.image_url.startsWith('http') ? sp.image_url : ('/' + sp.image_url.replace(/^\/+/, ''));
        } else {
            img.src = '';
        }
    } else {
        document.getElementById('type_text').checked = true;
        document.getElementById('sp_image_url').value = '';
        currentLogoBox.style.display = 'none';
        img.src = '';
    }

    toggleTypeFields();
    updateLivePreview();
    document.getElementById('sponsorModalBackdrop').classList.add('open');
}

function toggleSponsorStatus(id, form) {
    const btn = form.querySelector('button');
    const token = form.querySelector('input[name="csrf_token"]')?.value || CSRF_TOKEN;
    if (btn.disabled) return;
    btn.disabled = true;

    fetch(SPONSORS_BASE_URL + '/' + id + '/toggle', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': token
        },
        body: JSON.stringify({ csrf_token: token })
    })
    .then(r => r.json())
    .then(data => {
        btn.disabled = false;
        if (data.success) {
            if (data.is_active) {
                btn.className = 'sp-status-btn status-active';
                btn.textContent = '🟢 Active';
            } else {
                btn.className = 'sp-status-btn status-paused';
                btn.textContent = '⚪ Paused';
            }
            const activeCountEl = document.getElementById('activeSponsorsCount');
            if (activeCountEl) {
                let current = parseInt(activeCountEl.textContent, 10) || 0;
                activeCountEl.textContent = data.is_active ? (current + 1) : Math.max(0, current - 1);
            }
        } else {
            alert(data.message || 'Error toggling sponsor status');
        }
    })
    .catch(() => {
        btn.disabled = false;
        form.submit();
    });
}

function closeSponsorModal(e) {
    if (!e || e.target.id === 'sponsorModalBackdrop' || e.type === 'click') {
        document.getElementById('sponsorModalBackdrop').classList.remove('open');
    }
}

function openTapeSettingsModal() {
    document.getElementById('tapeSettingsModalBackdrop').classList.add('open');
}

function closeTapeSettingsModal(e) {
    if (!e || e.target.id === 'tapeSettingsModalBackdrop' || e.type === 'click') {
        document.getElementById('tapeSettingsModalBackdrop').classList.remove('open');
    }
}

function toggleTypeFields() {
    var isImage = document.getElementById('type_image').checked;
    document.getElementById('textFieldsGroup').style.display = isImage ? 'none' : 'block';
    document.getElementById('imageFieldsGroup').style.display = isImage ? 'block' : 'none';
    updateLivePreview();
}

function previewLogoFile(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            var img = document.getElementById('livePreviewImg');
            img.src = e.target.result;
            updateLivePreview();
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function previewLogoUrl(val) {
    var img = document.getElementById('livePreviewImg');
    if (val && val.trim()) {
        img.src = val.trim();
    }
    updateLivePreview();
}

function syncColorInput(val) {
    document.getElementById('sp_text_color_hex').value = val;
    updateLivePreview();
}

function syncColorNative(val) {
    if (/^#[0-9a-fA-F]{6}$/.test(val)) {
        document.getElementById('sp_text_color').value = val;
        updateLivePreview();
    }
}

function pickColor(hex) {
    document.getElementById('sp_text_color').value = hex;
    document.getElementById('sp_text_color_hex').value = hex;
    updateLivePreview();
}

function updateLivePreview() {
    var isImage = document.getElementById('type_image').checked;
    var name = document.getElementById('sp_name').value.trim();
    var customText = document.getElementById('sp_text_content').value.trim();
    var badge = document.getElementById('sp_badge_text').value.trim();
    var color = document.getElementById('sp_text_color').value;
    var font = document.getElementById('sp_text_font').value;
    var style = document.getElementById('sp_text_style').value;

    var item = document.getElementById('livePreviewItem');
    var textBox = document.getElementById('livePreviewTextBox');
    var imgBox = document.getElementById('livePreviewImgBox');
    var img = document.getElementById('livePreviewImg');
    var textLabel = document.getElementById('livePreviewText');
    var badgeLabel = document.getElementById('livePreviewBadge');

    if (badge) {
        badgeLabel.style.display = 'block';
        badgeLabel.textContent = badge;
    } else {
        badgeLabel.style.display = 'none';
    }

    if (isImage) {
        textBox.style.display = 'none';
        imgBox.style.display = 'flex';
        var urlInput = document.getElementById('sp_image_url').value.trim();
        if (!img.getAttribute('src') && urlInput) {
            img.src = urlInput;
        }
        item.className = 'sp-tape-item sp-item-image' + (badge ? ' has-badge' : '');
    } else {
        textBox.style.display = 'inline-flex';
        imgBox.style.display = 'none';
        var displayText = customText || name || 'BRAND NAME';
        textLabel.textContent = displayText;
        textBox.style.setProperty('--sp-color', color);
        item.className = 'sp-tape-item sp-item-text font-' + font + ' style-' + style + (badge ? ' has-badge' : '');
    }
}
</script>
@endsection
