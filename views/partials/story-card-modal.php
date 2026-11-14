<?php
/**
 * Eventlyy - "I'm Going!" Human Editorial Story Pass Modal
 * Designed for authentic, professional social discovery (Instagram Stories & WhatsApp Status).
 * QR-Protected for secure online social sharing.
 */
$scSiteTitle = site_setting('site_title', 'Eventlyy');
?>
<link rel="stylesheet" href="<?= asset('assets/css/story-card.css') ?>?v=1.0.2">

<!-- Story Card Generator Modal -->
<div id="storyCardModal" class="sc-modal" data-site-title="<?= e($scSiteTitle) ?>" aria-hidden="true" role="dialog" aria-labelledby="scModalTitle">
    <div class="sc-dialog">
        <!-- Modal Header -->
        <div class="sc-header">
            <div class="sc-header-title">
                <h3 id="scModalTitle">Story Pass Studio</h3>
                <span>Export 9:16 HD Card for Instagram &amp; WhatsApp &bull; <strong style="color:var(--sc-brand);">🔒 Safe to share online (No entry QR shown)</strong></span>
            </div>
            <button type="button" class="sc-close-btn" id="scCloseBtn" aria-label="Close dialog">
                <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <!-- Modal Body -->
        <div class="sc-body">
            <!-- Left: Live 9:16 Canvas Preview -->
            <div class="sc-preview-panel">
                <div class="sc-canvas-wrapper">
                    <canvas id="scCanvas" width="1080" height="1920"></canvas>
                </div>
                <div class="sc-preview-badge">1080 &times; 1920 &bull; 9:16 Vertical HD</div>
            </div>

            <!-- Right: Studio Controls -->
            <div class="sc-editor-panel">
                <!-- 1. Photo / Artwork Source -->
                <div class="sc-control-group">
                    <label class="sc-label">
                        <span>Card Imagery</span>
                        <span class="sc-label-hint">Use event artwork or upload your own photo</span>
                    </label>

                    <div class="sc-photo-tabs">
                        <button type="button" class="sc-photo-tab active" data-source="event">
                            <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            Event Artwork
                        </button>
                        <button type="button" class="sc-photo-tab" data-source="user">
                            <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            My Photo / Selfie
                        </button>
                    </div>

                    <!-- Hidden file picker -->
                    <input type="file" id="scFileInput" accept="image/png,image/jpeg,image/webp,image/heic" style="display:none;">

                    <!-- Upload Zone (shown when user clicks "My Photo" or needs to upload) -->
                    <div class="sc-upload-zone" id="scUploadZone" style="display: none;">
                        <svg class="sc-upload-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                        </svg>
                        <div class="sc-upload-text">Upload selfie, outfit or squad photo</div>
                        <div class="sc-upload-subtext">Click to browse or drag & drop (JPG, PNG)</div>
                    </div>

                    <!-- Uploaded Image Status Bar -->
                    <div class="sc-photo-preview-bar" id="scPhotoPreviewBar">
                        <div class="sc-photo-thumb-wrap">
                            <img id="scPhotoThumb" class="sc-photo-thumb" src="" alt="Attendee preview">
                            <div class="sc-photo-thumb-info">
                                <span class="sc-photo-thumb-title">Your photo is loaded</span>
                                <span class="sc-photo-thumb-desc" id="scPhotoFilename">photo.jpg</span>
                            </div>
                        </div>
                        <button type="button" class="sc-photo-remove-btn" id="scRemovePhotoBtn">Remove</button>
                    </div>
                </div>

                <!-- 2. Human Editorial Design Styles -->
                <div class="sc-control-group">
                    <label class="sc-label">
                        <span>Editorial Aesthetic</span>
                    </label>
                    <div class="sc-template-grid">
                        <div class="sc-template-card active" data-template="noir">
                            <div class="sc-template-name">
                                <span>Editorial Noir</span>
                                <svg fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            </div>
                            <span class="sc-template-desc">Cinematic full-bleed frame with Swiss typography</span>
                        </div>

                        <div class="sc-template-card" data-template="vogue">
                            <div class="sc-template-name">
                                <span>Velvet Luxe</span>
                                <svg fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            </div>
                            <span class="sc-template-desc">High-fashion gala portrait with luxury typography</span>
                        </div>

                        <div class="sc-template-card" data-template="vibes">
                            <div class="sc-template-name">
                                <span>Festival Edition</span>
                                <svg fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            </div>
                            <span class="sc-template-desc">Modern Boiler Room &amp; festival poster layout</span>
                        </div>
                    </div>
                </div>

                <!-- 3. Attendee Access Badge -->
                <div class="sc-control-group">
                    <label class="sc-label">
                        <span>Access Badge</span>
                    </label>
                    <div class="sc-badge-grid">
                        <button type="button" class="sc-badge-pill active" data-badge="CONFIRMED ATTENDEE">CONFIRMED ATTENDEE</button>
                        <button type="button" class="sc-badge-pill" data-badge="VIP GUEST ACCESS">VIP GUEST ACCESS</button>
                        <button type="button" class="sc-badge-pill" data-badge="OFFICIAL PASS">OFFICIAL PASS</button>
                        <button type="button" class="sc-badge-pill" data-badge="SEE YOU THERE">SEE YOU THERE</button>
                    </div>
                </div>

                <!-- 4. Attendee Name / Handle -->
                <div class="sc-control-group">
                    <div class="sc-input-wrap">
                        <label class="sc-label" for="scAttendeeInput">
                            <span>Your Name or Handle</span>
                            <span class="sc-label-hint">e.g. @alex or Sarah Jenkins</span>
                        </label>
                        <input type="text" id="scAttendeeInput" class="sc-input" placeholder="Enter your name or Instagram handle" maxlength="32">
                    </div>
                </div>

                <!-- Security Guarantee Note -->
                <div style="display:flex; align-items:center; gap:0.5rem; padding:0.65rem 0.85rem; background:var(--sc-brand-soft); border:1px solid rgba(255,255,255,0.08); border-radius:10px; font-size:0.75rem; color:#f3f4f6;">
                    <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="flex-shrink:0; color:var(--sc-brand);">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                    <span><strong>100% Social Safe:</strong> Real ticket entry QR codes are kept private in your <?= e($scSiteTitle) ?> account so anyone online cannot copy or scan your ticket.</span>
                </div>

                <!-- Actions: Download & Share -->
                <div class="sc-actions-bar">
                    <button type="button" class="sc-btn sc-btn-primary" id="scDownloadBtn">
                        <svg width="17" height="17" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                        Download HD Story
                    </button>
                    <button type="button" class="sc-btn sc-btn-secondary" id="scShareBtn">
                        <svg width="17" height="17" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/>
                        </svg>
                        Share
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="<?= asset('assets/js/story-card-generator.js') ?>?v=1.0.2" defer></script>
