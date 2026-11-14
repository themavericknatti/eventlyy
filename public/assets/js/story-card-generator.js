/**
 * Eventlyy - "I'm Going!" Human Editorial Story Pass Generator
 * Refined typography-first 9:16 canvas engine with custom attendee photo upload.
 * 
 * Integrates dynamically with platform Admin Settings Theme Colors.
 * SECURITY: Real ticket QR codes and scannable entry barcodes are deliberately EXCLUDED
 * to prevent ticket theft and fraud when cards are posted publicly on social media.
 */

(function() {
    'use strict';

    // Application State (Defaults to the loved 'noir' template!)
    const state = {
        template: 'noir', // 'noir' | 'vogue' | 'vibes'
        badge: 'CONFIRMED ATTENDEE',
        attendeeName: '',
        siteTitle: 'Eventlyy',
        imageSource: 'event', // 'event' | 'user'
        eventImageSrc: '',
        userImageSrc: null,
        eventData: {
            title: 'Live Experience',
            date: 'Saturday, Dec 14',
            time: '8:00 PM',
            venue: 'Main Arena',
            city: 'Lagos, NG',
            ticketType: 'Official Pass',
            url: window.location.origin
        },
        cachedImages: {
            event: null,
            user: null
        }
    };

    // DOM Elements
    let modal, canvas, ctx;
    let closeBtn, downloadBtn, shareBtn;
    let attendeeInput, fileInput, uploadZone, removePhotoBtn;
    let photoPreviewBar, photoThumb, photoFilename;
    let sourceTabs, templateCards, badgePills;

    /**
     * Retrieve active admin theme colors from CSS variables
     */
    function getThemeColors() {
        const rootStyle = window.getComputedStyle(document.documentElement);
        const primary = rootStyle.getPropertyValue('--primary').trim() || '#108a55';
        const secondary = rootStyle.getPropertyValue('--secondary').trim() || '#1aae6c';
        const onPrimary = rootStyle.getPropertyValue('--on-primary').trim() || '#ffffff';
        return { primary, secondary, onPrimary };
    }

    /**
     * Initialize on DOM Ready
     */
    document.addEventListener('DOMContentLoaded', init);

    function init() {
        modal = document.getElementById('storyCardModal');
        if (!modal) return;

        if (modal.dataset && modal.dataset.siteTitle) {
            state.siteTitle = modal.dataset.siteTitle;
        }

        canvas = document.getElementById('scCanvas');
        if (!canvas) return;
        ctx = canvas.getContext('2d');

        // Buttons & inputs
        closeBtn = document.getElementById('scCloseBtn');
        downloadBtn = document.getElementById('scDownloadBtn');
        shareBtn = document.getElementById('scShareBtn');
        attendeeInput = document.getElementById('scAttendeeInput');
        fileInput = document.getElementById('scFileInput');
        uploadZone = document.getElementById('scUploadZone');
        removePhotoBtn = document.getElementById('scRemovePhotoBtn');
        photoPreviewBar = document.getElementById('scPhotoPreviewBar');
        photoThumb = document.getElementById('scPhotoThumb');
        photoFilename = document.getElementById('scPhotoFilename');

        // Collections
        sourceTabs = document.querySelectorAll('.sc-photo-tab');
        templateCards = document.querySelectorAll('.sc-template-card');
        badgePills = document.querySelectorAll('.sc-badge-pill');

        attachEventListeners();
    }

    /**
     * Wire up UI interactions
     */
    function attachEventListeners() {
        // Modal trigger buttons across the site
        document.addEventListener('click', function(e) {
            const trigger = e.target.closest('[data-story-trigger]');
            if (trigger) {
                e.preventDefault();
                openModalWithData(trigger.dataset);
            }
        });

        // Close modal
        if (closeBtn) closeBtn.addEventListener('click', closeModal);
        modal.addEventListener('click', function(e) {
            if (e.target === modal) closeModal();
        });
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && modal.classList.contains('active')) {
                closeModal();
            }
        });

        // Photo Source Switcher
        sourceTabs.forEach(tab => {
            tab.addEventListener('click', function() {
                sourceTabs.forEach(t => t.classList.remove('active'));
                this.classList.add('active');
                const src = this.dataset.source;
                state.imageSource = src;

                if (src === 'user') {
                    if (state.userImageSrc) {
                        uploadZone.style.display = 'none';
                        photoPreviewBar.classList.add('active');
                    } else {
                        uploadZone.style.display = 'flex';
                        photoPreviewBar.classList.remove('active');
                        fileInput.click();
                    }
                } else {
                    uploadZone.style.display = 'none';
                    if (state.userImageSrc) {
                        photoPreviewBar.classList.add('active');
                    }
                }
                render();
            });
        });

        // Photo Upload Trigger & Drag-Drop
        if (uploadZone) {
            uploadZone.addEventListener('click', () => fileInput.click());
            uploadZone.addEventListener('dragover', (e) => {
                e.preventDefault();
                uploadZone.classList.add('dragover');
            });
            uploadZone.addEventListener('dragleave', () => uploadZone.classList.remove('dragover'));
            uploadZone.addEventListener('drop', (e) => {
                e.preventDefault();
                uploadZone.classList.remove('dragover');
                if (e.dataTransfer.files && e.dataTransfer.files[0]) {
                    handleUserPhoto(e.dataTransfer.files[0]);
                }
            });
        }

        if (fileInput) {
            fileInput.addEventListener('change', function() {
                if (this.files && this.files[0]) {
                    handleUserPhoto(this.files[0]);
                }
            });
        }

        if (removePhotoBtn) {
            removePhotoBtn.addEventListener('click', function() {
                state.userImageSrc = null;
                state.cachedImages.user = null;
                state.imageSource = 'event';
                fileInput.value = '';
                photoPreviewBar.classList.remove('active');
                uploadZone.style.display = 'none';
                sourceTabs.forEach(t => {
                    t.classList.toggle('active', t.dataset.source === 'event');
                });
                render();
            });
        }

        // Template Selection
        templateCards.forEach(card => {
            card.addEventListener('click', function() {
                templateCards.forEach(c => c.classList.remove('active'));
                this.classList.add('active');
                state.template = this.dataset.template;
                render();
            });
        });

        // Badge Selection
        badgePills.forEach(pill => {
            pill.addEventListener('click', function() {
                badgePills.forEach(p => p.classList.remove('active'));
                this.classList.add('active');
                state.badge = this.dataset.badge;
                render();
            });
        });

        // Attendee Name Input
        if (attendeeInput) {
            attendeeInput.addEventListener('input', function() {
                state.attendeeName = this.value.trim();
                render();
            });
        }

        // Export Actions
        if (downloadBtn) downloadBtn.addEventListener('click', downloadCard);
        if (shareBtn) shareBtn.addEventListener('click', shareCard);
    }

    /**
     * Handle user uploaded image file
     */
    function handleUserPhoto(file) {
        if (!file.type.startsWith('image/')) {
            alert('Please select an image file (JPG, PNG, WebP).');
            return;
        }

        const reader = new FileReader();
        reader.onload = function(e) {
            const dataUrl = e.target.result;
            state.userImageSrc = dataUrl;
            state.imageSource = 'user';

            // Update UI preview bar
            if (photoThumb) photoThumb.src = dataUrl;
            if (photoFilename) photoFilename.textContent = file.name || 'custom-photo.jpg';
            if (uploadZone) uploadZone.style.display = 'none';
            if (photoPreviewBar) photoPreviewBar.classList.add('active');

            // Switch tab
            sourceTabs.forEach(t => {
                t.classList.toggle('active', t.dataset.source === 'user');
            });

            // Load into image cache
            const img = new Image();
            img.onload = function() {
                state.cachedImages.user = img;
                render();
            };
            img.src = dataUrl;
        };
        reader.readAsDataURL(file);
    }

    // High-res curated fallback artwork so the card is NEVER blank
    const DEFAULT_EVENT_IMAGE = 'https://images.unsplash.com/photo-1514525253161-7a46d19cd819?w=1600&q=85&auto=format&fit=crop';

    /**
     * Open modal and populate initial event metadata
     * (NO QR code or sensitive ticket codes are stored)
     */
    function openModalWithData(data) {
        if (!modal) return;

        state.eventData.title = data.eventName || data.eventTitle || 'Exclusive Experience';
        state.eventData.date = data.eventDate || 'Coming Soon';
        state.eventData.time = data.eventTime || '';
        state.eventData.venue = data.eventLocation || data.eventVenue || 'Venue Announced Soon';
        state.eventData.city = data.eventCity || '';
        state.eventData.ticketType = data.ticketType || 'Official Pass';
        state.eventData.url = data.eventUrl || window.location.href;

        if (data.siteTitle) {
            state.siteTitle = data.siteTitle;
        } else if (modal && modal.dataset && modal.dataset.siteTitle) {
            state.siteTitle = modal.dataset.siteTitle;
        }

        if (data.attendeeName && !state.attendeeName) {
            state.attendeeName = data.attendeeName;
            if (attendeeInput) attendeeInput.value = data.attendeeName;
        }

        let imgSrc = (data.eventImage || '').trim();
        // If relative URL without protocol, make sure it's valid
        if (imgSrc && imgSrc.startsWith('/') && !imgSrc.startsWith('//')) {
            imgSrc = window.location.origin + imgSrc;
        }
        if (!imgSrc) {
            imgSrc = DEFAULT_EVENT_IMAGE;
        }

        state.eventImageSrc = imgSrc;
        // Immediate render with whatever state is available, then load image
        render();
        loadEventImage(imgSrc);

        modal.classList.add('active');
        modal.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
    }

    function closeModal() {
        if (!modal) return;
        modal.classList.remove('active');
        modal.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
    }

    /**
     * Robust Event Image Loader
     * Handles same-origin vs cross-origin, retries without CORS if localhost blocks,
     * and automatically falls back to curated concert visual if file is missing.
     */
    function loadEventImage(src) {
        if (!src) src = DEFAULT_EVENT_IMAGE;

        // If this image is already loaded and complete, just render
        if (state.cachedImages.event && state.cachedImages.event.src === src && state.cachedImages.event.complete) {
            render();
            return;
        }

        const isSameOrigin = src.startsWith(window.location.origin) || src.startsWith('/');

        const img = new Image();
        // Only request anonymous CORS if external
        if (!isSameOrigin) {
            img.crossOrigin = 'anonymous';
        }

        img.onload = function() {
            state.cachedImages.event = img;
            render();
        };

        img.onerror = function() {
            console.warn('[Story Studio] Primary image load failed for:', src);
            // If failed with crossOrigin, retry without crossOrigin
            if (img.crossOrigin) {
                const retryImg = new Image();
                retryImg.onload = function() {
                    state.cachedImages.event = retryImg;
                    render();
                };
                retryImg.onerror = function() {
                    // Fall back to default concert image if not already tried
                    if (src !== DEFAULT_EVENT_IMAGE) {
                        loadEventImage(DEFAULT_EVENT_IMAGE);
                    } else {
                        state.cachedImages.event = null;
                        render();
                    }
                };
                retryImg.src = src;
            } else if (src !== DEFAULT_EVENT_IMAGE) {
                loadEventImage(DEFAULT_EVENT_IMAGE);
            } else {
                state.cachedImages.event = null;
                render();
            }
        };

        img.src = src;
    }

    /**
     * Get active image to display (User photo or Event poster)
     */
    function getActiveImage() {
        if (state.imageSource === 'user' && state.cachedImages.user) {
            return state.cachedImages.user;
        }
        return state.cachedImages.event;
    }

    /**
     * Main Render Dispatcher
     */
    function render() {
        if (!canvas || !ctx) return;

        ctx.clearRect(0, 0, canvas.width, canvas.height);
        const theme = getThemeColors();

        switch (state.template) {
            case 'vogue':
                renderVelvetLuxeTemplate(theme);
                break;
            case 'vibes':
                renderFestivalEditionTemplate(theme);
                break;
            case 'noir':
            default:
                renderNoirTemplate(theme);
                break;
        }
    }

    /* =========================================================================
     * TEMPLATE 1: EDITORIAL NOIR (The User's Favorite!)
     * Cinematic full-bleed artwork, moody duotone gradient, Swiss typography
     * Subtly accented with the platform's active admin brand color.
     * ========================================================================= */
    function renderNoirTemplate(theme) {
        const w = 1080;
        const h = 1920;

        // Base black
        ctx.fillStyle = '#05070c';
        ctx.fillRect(0, 0, w, h);

        const activeImg = getActiveImage();

        // 1. Full Bleed Top Photo with Vignette Fade
        const photoH = 1260;
        if (activeImg) {
            ctx.save();
            drawImageCover(ctx, activeImg, 0, 0, w, photoH);

            // Cinematic Duotone Vignette
            const noirGrad = ctx.createLinearGradient(0, 0, 0, photoH);
            noirGrad.addColorStop(0, 'rgba(5, 7, 12, 0.3)');
            noirGrad.addColorStop(0.5, 'rgba(5, 7, 12, 0.45)');
            noirGrad.addColorStop(0.85, 'rgba(5, 7, 12, 0.88)');
            noirGrad.addColorStop(1, '#05070c');
            ctx.fillStyle = noirGrad;
            ctx.fillRect(0, 0, w, photoH);
            ctx.restore();
        } else {
            drawPlaceholderVisual(ctx, 0, 0, w, photoH, theme);
        }

        // 2. Magazine Editorial Header
        const brand = (state.siteTitle || 'Eventlyy').toUpperCase();
        ctx.save();
        ctx.fillStyle = 'rgba(255, 255, 255, 0.75)';
        ctx.font = '700 20px -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif';
        ctx.letterSpacing = '6px';
        ctx.textAlign = 'left';
        ctx.fillText(`${brand} LIVE`, 72, 100);

        ctx.textAlign = 'right';
        ctx.letterSpacing = '3px';
        ctx.fillText('NO. 2026 // ADMISSION CONFIRMED', w - 72, 100);

        ctx.lineWidth = 1;
        ctx.strokeStyle = 'rgba(255, 255, 255, 0.25)';
        ctx.beginPath();
        ctx.moveTo(72, 124);
        ctx.lineTo(w - 72, 124);
        ctx.stroke();
        ctx.restore();

        // 3. Central Badge Pill (White with subtle brand border glow)
        ctx.save();
        const badgeY = 740;
        ctx.font = '700 24px -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif';
        const bw = ctx.measureText(state.badge).width + 56;
        drawRoundedRect(ctx, 72, badgeY, bw, 48, 24);
        ctx.fillStyle = '#ffffff';
        ctx.fill();

        ctx.fillStyle = '#05070c';
        ctx.textAlign = 'left';
        ctx.textBaseline = 'middle';
        ctx.fillText(state.badge, 100, badgeY + 24);
        ctx.restore();

        // 4. Large Headline
        const textStartY = 830;
        ctx.save();
        ctx.fillStyle = '#ffffff';
        ctx.font = '800 68px -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif';
        ctx.letterSpacing = '-0.5px';
        ctx.textAlign = 'left';
        ctx.textBaseline = 'top';

        const lines = wrapText(ctx, state.eventData.title, w - 144, 3);
        let currY = textStartY;
        lines.forEach(l => {
            ctx.fillText(l, 72, currY);
            currY += 78;
        });
        ctx.restore();

        // 5. Attendee Line
        if (state.attendeeName) {
            ctx.save();
            ctx.fillStyle = 'rgba(255, 255, 255, 0.55)';
            ctx.font = '600 22px -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif';
            ctx.letterSpacing = '2px';
            ctx.fillText('ATTENDING AS', 72, currY + 28);

            ctx.fillStyle = '#ffffff';
            ctx.font = '700 40px -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif';
            ctx.letterSpacing = '0px';
            ctx.fillText(state.attendeeName, 72, currY + 74);
            ctx.restore();
            currY += 120;
        } else {
            currY += 40;
        }

        // 6. Editorial Details Section
        const footerY = Math.max(currY + 20, 1460);

        // Thin separator rule
        ctx.strokeStyle = 'rgba(255, 255, 255, 0.15)';
        ctx.lineWidth = 1;
        ctx.beginPath();
        ctx.moveTo(72, footerY);
        ctx.lineTo(w - 72, footerY);
        ctx.stroke();

        // 3 Columns: WHEN, WHERE, PASS
        const colWidth = (w - 144) / 3;

        drawEditorialColumn(ctx, 72, footerY + 36, colWidth, 'WHEN', state.eventData.date, state.eventData.time);
        drawEditorialColumn(ctx, 72 + colWidth, footerY + 36, colWidth, 'WHERE', state.eventData.venue, state.eventData.city);
        drawEditorialColumn(ctx, 72 + (colWidth * 2), footerY + 36, colWidth, 'PASS', state.eventData.ticketType, 'VALID ADMISSION');

        // Security Stamp at bottom right with theme brand tint
        drawVerificationStamp(ctx, w - 420, 1730, 348, 68, 'OFFICIAL SOCIAL PASS', 'ENTRY QR KEPT IN ACCOUNT', theme.primary);

        ctx.save();
        ctx.fillStyle = 'rgba(255, 255, 255, 0.45)';
        ctx.font = '600 18px -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif';
        ctx.letterSpacing = '3px';
        ctx.fillText(`${brand}.COM // SECURE SOCIAL SHARE`, 72, 1780);
        ctx.restore();
    }

    /* =========================================================================
     * TEMPLATE 2: VELVET LUXE (High-Fashion Gala & Editorial Portrait)
     * Warm luxury undertone, elegant editorial framing, serif display headline
     * ========================================================================= */
    function renderVelvetLuxeTemplate(theme) {
        const w = 1080;
        const h = 1920;

        // Base luxury deep charcoal/onyx
        ctx.fillStyle = '#07090e';
        ctx.fillRect(0, 0, w, h);

        // Subtle ambient brand glow at center
        const glow = ctx.createRadialGradient(w / 2, 600, 50, w / 2, 600, 700);
        glow.addColorStop(0, hexToRgba(theme.primary, 0.12));
        glow.addColorStop(1, 'transparent');
        ctx.fillStyle = glow;
        ctx.fillRect(0, 0, w, h);

        const brand = (state.siteTitle || 'Eventlyy').toUpperCase();

        // Header Kicker
        ctx.save();
        ctx.fillStyle = 'rgba(255, 255, 255, 0.55)';
        ctx.font = '600 18px -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif';
        ctx.letterSpacing = '7px';
        ctx.textAlign = 'center';
        ctx.fillText(`AN EXCLUSIVE ${brand} EXPERIENCE  //  EDITION 2026`, w / 2, 88);
        ctx.restore();

        // 1. Framed Portrait Photo (3:4 Golden Ratio Editorial Frame)
        const frameX = 72;
        const frameY = 124;
        const frameW = w - 144;
        const frameH = 880;
        const frameR = 18;

        const activeImg = getActiveImage();

        ctx.save();
        drawRoundedRect(ctx, frameX, frameY, frameW, frameH, frameR);
        ctx.clip();

        if (activeImg) {
            drawImageCover(ctx, activeImg, frameX, frameY, frameW, frameH);

            // Subtle dark bottom vignette on photo
            const vGrad = ctx.createLinearGradient(0, frameY + (frameH * 0.4), 0, frameY + frameH);
            vGrad.addColorStop(0, 'transparent');
            vGrad.addColorStop(1, 'rgba(7, 9, 14, 0.92)');
            ctx.fillStyle = vGrad;
            ctx.fillRect(frameX, frameY, frameW, frameH);
        } else {
            drawPlaceholderVisual(ctx, frameX, frameY, frameW, frameH, theme);
        }
        ctx.restore();

        // Double Hairline Border on Frame (Tinted with brand theme)
        drawRoundedRect(ctx, frameX, frameY, frameW, frameH, frameR);
        ctx.lineWidth = 1.5;
        ctx.strokeStyle = hexToRgba(theme.primary, 0.35);
        ctx.stroke();

        // Inner micro-hairline
        drawRoundedRect(ctx, frameX + 8, frameY + 8, frameW - 16, frameH - 16, frameR - 6);
        ctx.lineWidth = 0.75;
        ctx.strokeStyle = 'rgba(255, 255, 255, 0.12)';
        ctx.stroke();

        // 2. Access Badge Pill inside bottom of frame
        ctx.save();
        const badgeY = frameY + frameH - 66;
        ctx.font = '700 20px -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif';
        const badgeW = ctx.measureText(state.badge).width + 48;
        drawRoundedRect(ctx, frameX + 28, badgeY, badgeW, 42, 21);
        ctx.fillStyle = theme.primary;
        ctx.fill();

        ctx.fillStyle = theme.onPrimary;
        ctx.textAlign = 'left';
        ctx.textBaseline = 'middle';
        ctx.fillText(state.badge, frameX + 52, badgeY + 21);
        ctx.restore();

        if (state.imageSource === 'user') {
            ctx.save();
            ctx.font = '600 17px -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif';
            ctx.fillStyle = 'rgba(255, 255, 255, 0.8)';
            ctx.textAlign = 'right';
            ctx.fillText('FEATURED GUEST PHOTO', frameX + frameW - 28, badgeY + 21);
            ctx.restore();
        }

        // 3. Luxurious Editorial Headline (Serif elegance)
        const textStartY = frameY + frameH + 64;
        ctx.save();
        ctx.fillStyle = '#ffffff';
        ctx.font = '700 66px "Playfair Display", Georgia, "Times New Roman", serif';
        ctx.letterSpacing = '-0.5px';
        ctx.textAlign = 'left';
        ctx.textBaseline = 'top';

        const lines = wrapText(ctx, state.eventData.title, frameW, 3);
        let currY = textStartY;
        lines.forEach(l => {
            ctx.fillText(l, frameX, currY);
            currY += 76;
        });
        ctx.restore();

        // 4. Attendee Line
        if (state.attendeeName) {
            ctx.save();
            ctx.fillStyle = 'rgba(255, 255, 255, 0.5)';
            ctx.font = '600 19px -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif';
            ctx.letterSpacing = '3px';
            ctx.fillText('RESERVED FOR', frameX, currY + 20);

            ctx.fillStyle = '#ffffff';
            ctx.font = '600 38px "Playfair Display", Georgia, serif';
            ctx.fillText(state.attendeeName, frameX, currY + 64);
            ctx.restore();
            currY += 105;
        } else {
            currY += 25;
        }

        // 5. Archival Column Details
        const footerY = Math.max(currY + 20, 1470);
        ctx.lineWidth = 1;
        ctx.strokeStyle = 'rgba(255, 255, 255, 0.15)';
        ctx.beginPath();
        ctx.moveTo(frameX, footerY);
        ctx.lineTo(frameX + frameW, footerY);
        ctx.stroke();

        const colW = frameW / 3;
        drawEditorialColumn(ctx, frameX, footerY + 36, colW, 'DATE & HOUR', state.eventData.date, state.eventData.time);
        drawEditorialColumn(ctx, frameX + colW, footerY + 36, colW, 'VENUE / CITY', state.eventData.venue, state.eventData.city);
        drawEditorialColumn(ctx, frameX + (colW * 2), footerY + 36, colW, 'ADMISSION', state.eventData.ticketType, 'CONFIRMED PASS');

        // Security Stamp Seal
        drawVerificationStamp(ctx, frameX, 1730, 420, 68, '✦ OFFICIAL SOCIAL INVITATION ✦', 'ADMISSION QR PROTECTED IN ACCOUNT', theme.primary);

        ctx.save();
        ctx.fillStyle = 'rgba(255, 255, 255, 0.4)';
        ctx.font = '600 17px -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif';
        ctx.letterSpacing = '2px';
        ctx.textAlign = 'right';
        ctx.fillText(`${brand}.COM`, frameX + frameW, 1772);
        ctx.restore();
    }

    /* =========================================================================
     * TEMPLATE 3: FESTIVAL EDITION (Boiler Room / Pitchfork Poster)
     * Heavy contemporary typography, panoramic photo block, bold coordinate grid
     * ========================================================================= */
    function renderFestivalEditionTemplate(theme) {
        const w = 1080;
        const h = 1920;

        // Base dark
        ctx.fillStyle = '#06080d';
        ctx.fillRect(0, 0, w, h);

        const pad = 64;
        const innerW = w - (pad * 2);

        // 1. Bold Top Header Bar
        const brand = (state.siteTitle || 'Eventlyy').toUpperCase();
        ctx.save();
        ctx.fillStyle = '#ffffff';
        ctx.font = '900 32px -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif';
        ctx.letterSpacing = '3px';
        ctx.fillText(`${brand} // LIVE STAGE`, pad, 96);

        ctx.fillStyle = theme.primary;
        ctx.font = '700 20px monospace';
        ctx.textAlign = 'right';
        ctx.fillText('CONFIRMED ACCESS', w - pad, 96);

        ctx.lineWidth = 2;
        ctx.strokeStyle = '#ffffff';
        ctx.beginPath();
        ctx.moveTo(pad, 120);
        ctx.lineTo(w - pad, 120);
        ctx.stroke();
        ctx.restore();

        // 2. High-Impact Photo Crop (Tall 9:10 Cinematic Aspect)
        const photoY = 150;
        const photoH = 920;
        const activeImg = getActiveImage();

        ctx.save();
        drawRoundedRect(ctx, pad, photoY, innerW, photoH, 16);
        ctx.clip();

        if (activeImg) {
            drawImageCover(ctx, activeImg, pad, photoY, innerW, photoH);

            // Moody atmospheric gradient
            const grad = ctx.createLinearGradient(0, photoY + (photoH * 0.4), 0, photoY + photoH);
            grad.addColorStop(0, 'transparent');
            grad.addColorStop(1, '#06080d');
            ctx.fillStyle = grad;
            ctx.fillRect(pad, photoY, innerW, photoH);
        } else {
            drawPlaceholderVisual(ctx, pad, photoY, innerW, photoH, theme);
        }
        ctx.restore();

        // Photo Border
        drawRoundedRect(ctx, pad, photoY, innerW, photoH, 16);
        ctx.lineWidth = 1;
        ctx.strokeStyle = 'rgba(255, 255, 255, 0.18)';
        ctx.stroke();

        // Badge on photo
        ctx.save();
        const badgeY = photoY + photoH - 68;
        ctx.font = '800 22px -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif';
        const bw = ctx.measureText(state.badge).width + 52;
        drawRoundedRect(ctx, pad + 24, badgeY, bw, 44, 10);
        ctx.fillStyle = '#ffffff';
        ctx.fill();

        ctx.fillStyle = '#06080d';
        ctx.textAlign = 'left';
        ctx.textBaseline = 'middle';
        ctx.fillText(state.badge, pad + 50, badgeY + 22);
        ctx.restore();

        // 3. Huge Bold Staggered Headline
        const textStartY = photoY + photoH + 54;
        ctx.save();
        ctx.fillStyle = '#ffffff';
        ctx.font = '900 68px -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif';
        ctx.letterSpacing = '-1px';
        ctx.textBaseline = 'top';

        const lines = wrapText(ctx, state.eventData.title.toUpperCase(), innerW, 3);
        let currY = textStartY;
        lines.forEach(l => {
            ctx.fillText(l, pad, currY);
            currY += 76;
        });
        ctx.restore();

        // 4. Attendee Line
        if (state.attendeeName) {
            ctx.save();
            ctx.fillStyle = theme.primary;
            ctx.font = '800 20px monospace';
            ctx.letterSpacing = '1px';
            ctx.fillText('PASSHOLDER //', pad, currY + 22);

            ctx.fillStyle = '#ffffff';
            ctx.font = '800 38px -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif';
            ctx.fillText(state.attendeeName, pad, currY + 66);
            ctx.restore();
            currY += 105;
        } else {
            currY += 25;
        }

        // 5. Architectural 2-Row Modular Details
        const footerY = Math.max(currY + 20, 1480);
        ctx.lineWidth = 1;
        ctx.strokeStyle = 'rgba(255, 255, 255, 0.2)';
        ctx.strokeRect(pad, footerY, innerW, 180);

        // Vertical divider
        const halfW = innerW / 2;
        ctx.beginPath();
        ctx.moveTo(pad + halfW, footerY);
        ctx.lineTo(pad + halfW, footerY + 180);
        ctx.moveTo(pad, footerY + 90);
        ctx.lineTo(pad + innerW, footerY + 90);
        ctx.stroke();

        // Cell 1: Date
        drawModularCell(ctx, pad + 20, footerY + 14, halfW - 40, 'DATE', state.eventData.date);
        // Cell 2: Time
        drawModularCell(ctx, pad + halfW + 20, footerY + 14, halfW - 40, 'TIME', state.eventData.time || 'DOORS OPEN 8PM');
        // Cell 3: Venue
        drawModularCell(ctx, pad + 20, footerY + 104, halfW - 40, 'VENUE', state.eventData.venue);
        // Cell 4: Access
        drawModularCell(ctx, pad + halfW + 20, footerY + 104, halfW - 40, 'PASS TIER', state.eventData.ticketType);

        // Security Stamp (Brand Accent)
        drawVerificationStamp(ctx, pad, 1720, innerW, 64, '✦ SECURE SOCIAL DISCOVERY PASS ✦', 'OFFICIAL ADMISSION QR PROTECTED IN ACCOUNT', theme.primary);
    }

    /* =========================================================================
     * HELPER CANVAS DRAWING PRIMITIVES
     * ========================================================================= */

    function drawPlaceholderVisual(ctx, x, y, w, h, theme) {
        ctx.save();
        // Rich duotone background
        const bg = ctx.createLinearGradient(x, y, x + w, y + h);
        bg.addColorStop(0, '#131b2c');
        bg.addColorStop(0.5, '#0b0f19');
        bg.addColorStop(1, '#05070c');
        ctx.fillStyle = bg;
        ctx.fillRect(x, y, w, h);

        // Ambient brand light beam
        const beam = ctx.createRadialGradient(x + (w / 2), y + (h * 0.45), 20, x + (w / 2), y + (h * 0.45), w * 0.7);
        beam.addColorStop(0, hexToRgba(theme.primary, 0.28));
        beam.addColorStop(1, 'transparent');
        ctx.fillStyle = beam;
        ctx.fillRect(x, y, w, h);

        // Architectural geometric rings
        ctx.strokeStyle = 'rgba(255, 255, 255, 0.06)';
        ctx.lineWidth = 1.2;
        for (let i = 1; i <= 5; i++) {
            ctx.beginPath();
            ctx.arc(x + (w / 2), y + (h * 0.45), 60 + (i * 70), 0, Math.PI * 2);
            ctx.stroke();
        }

        // Micro status label
        ctx.fillStyle = 'rgba(255, 255, 255, 0.55)';
        ctx.font = '700 18px -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif';
        ctx.letterSpacing = '5px';
        ctx.textAlign = 'center';
        ctx.fillText('✦ LIVE EVENT EXPERIENCE ✦', x + (w / 2), y + (h * 0.45));
        ctx.restore();
    }

    function drawImageCover(ctx, img, x, y, w, h) {
        if (!img || !img.width || !img.height) return;
        const imgRatio = img.width / img.height;
        const boxRatio = w / h;
        let sX = 0, sY = 0, sW = img.width, sH = img.height;

        if (imgRatio > boxRatio) {
            sW = img.height * boxRatio;
            sX = (img.width - sW) / 2;
        } else {
            sH = img.width / boxRatio;
            sY = (img.height - sH) / 2;
        }

        ctx.drawImage(img, sX, sY, sW, sH, x, y, w, h);
    }

    function drawRoundedRect(ctx, x, y, width, height, radius) {
        ctx.beginPath();
        if (ctx.roundRect) {
            ctx.roundRect(x, y, width, height, radius);
        } else {
            ctx.moveTo(x + radius, y);
            ctx.lineTo(x + width - radius, y);
            ctx.quadraticCurveTo(x + width, y, x + width, y + radius);
            ctx.lineTo(x + width, y + height - radius);
            ctx.quadraticCurveTo(x + width, y + height, x + width - radius, y + height);
            ctx.lineTo(x + radius, y + height);
            ctx.quadraticCurveTo(x, y + height, x, y + height - radius);
            ctx.lineTo(x, y + radius);
            ctx.quadraticCurveTo(x, y, x + radius, y);
            ctx.closePath();
        }
    }

    function drawEditorialColumn(ctx, x, y, w, label, val1, val2) {
        ctx.save();
        ctx.fillStyle = 'rgba(255, 255, 255, 0.45)';
        ctx.font = '600 17px -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif';
        ctx.letterSpacing = '2px';
        ctx.fillText(label, x, y);

        ctx.fillStyle = '#ffffff';
        ctx.font = '700 24px -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif';
        ctx.letterSpacing = '0px';

        let displayVal = val1 || '—';
        if (ctx.measureText(displayVal).width > w - 24) {
            while (ctx.measureText(displayVal + '...').width > w - 24 && displayVal.length > 0) {
                displayVal = displayVal.slice(0, -1);
            }
            displayVal += '...';
        }
        ctx.fillText(displayVal, x, y + 36);

        if (val2) {
            ctx.fillStyle = 'rgba(255, 255, 255, 0.6)';
            ctx.font = '500 20px -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif';
            ctx.fillText(val2, x, y + 68);
        }
        ctx.restore();
    }

    function drawModularCell(ctx, x, y, w, label, val) {
        ctx.save();
        ctx.fillStyle = 'rgba(255, 255, 255, 0.5)';
        ctx.font = '700 15px monospace';
        ctx.fillText(label, x, y + 16);

        ctx.fillStyle = '#ffffff';
        ctx.font = '700 22px -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif';

        let display = val || '—';
        if (ctx.measureText(display).width > w) {
            while (ctx.measureText(display + '...').width > w && display.length > 0) {
                display = display.slice(0, -1);
            }
            display += '...';
        }
        ctx.fillText(display, x, y + 50);
        ctx.restore();
    }

    /**
     * Official Social Pass Stamp & Verification Badge (Replaces sensitive barcodes and QR codes)
     */
    function drawVerificationStamp(ctx, x, y, width, height, title, subtitle, accentColor) {
        ctx.save();
        drawRoundedRect(ctx, x, y, width, height, 10);
        ctx.fillStyle = 'rgba(255, 255, 255, 0.03)';
        ctx.fill();
        ctx.lineWidth = 1.2;
        ctx.strokeStyle = accentColor ? hexToRgba(accentColor, 0.45) : 'rgba(255, 255, 255, 0.18)';
        ctx.stroke();

        ctx.fillStyle = '#ffffff';
        ctx.font = '700 17px -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif';
        ctx.letterSpacing = '2px';
        ctx.textAlign = 'center';
        ctx.fillText(title, x + (width / 2), y + 26);

        ctx.fillStyle = accentColor || 'rgba(52, 211, 153, 0.9)';
        ctx.font = '600 13px monospace';
        ctx.letterSpacing = '1px';
        ctx.fillText(subtitle, x + (width / 2), y + 48);
        ctx.restore();
    }

    function hexToRgba(hex, alpha) {
        if (!hex || !hex.startsWith('#')) return `rgba(16, 138, 85, ${alpha})`;
        const clean = hex.slice(1);
        const r = parseInt(clean.substring(0, 2), 16) || 0;
        const g = parseInt(clean.substring(2, 4), 16) || 0;
        const b = parseInt(clean.substring(4, 6), 16) || 0;
        return `rgba(${r}, ${g}, ${b}, ${alpha})`;
    }

    function wrapText(ctx, text, maxWidth, maxLines) {
        const words = text.split(' ');
        const lines = [];
        let currentLine = words[0] || '';

        for (let i = 1; i < words.length; i++) {
            const word = words[i];
            const width = ctx.measureText(currentLine + ' ' + word).width;
            if (width < maxWidth) {
                currentLine += ' ' + word;
            } else {
                lines.push(currentLine);
                currentLine = word;
                if (lines.length === maxLines - 1) {
                    break;
                }
            }
        }
        lines.push(currentLine);
        return lines.slice(0, maxLines);
    }

    /* =========================================================================
     * EXPORT & SHARE LOGIC
     * ========================================================================= */

    function downloadCard() {
        if (!canvas) return;

        downloadBtn.disabled = true;
        downloadBtn.innerHTML = 'Generating HD File...';

        canvas.toBlob(function(blob) {
            downloadBtn.disabled = false;
            downloadBtn.innerHTML = `
                <svg width="17" height="17" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                Download HD Story
            `;

            if (!blob) {
                alert('Could not export story card. Please try again.');
                return;
            }

            const brandSlug = (state.siteTitle || 'eventlyy').toLowerCase().replace(/[^a-z0-9]/g, '-');
            const slug = (state.eventData.title || 'event').toLowerCase().replace(/[^a-z0-9]/g, '-');
            const filename = `${brandSlug}-${slug}-story-pass.png`;

            const link = document.createElement('a');
            link.download = filename;
            link.href = URL.createObjectURL(blob);
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            URL.revokeObjectURL(link.href);
        }, 'image/png', 0.95);
    }

    function shareCard() {
        if (!canvas) return;

        canvas.toBlob(async function(blob) {
            if (!blob) return;

            const brandSlug = (state.siteTitle || 'eventlyy').toLowerCase().replace(/[^a-z0-9]/g, '-');
            const slug = (state.eventData.title || 'event').toLowerCase().replace(/[^a-z0-9]/g, '-');
            const file = new File([blob], `${brandSlug}-${slug}-pass.png`, { type: 'image/png' });

            // Try Web Share API with File (Mobile Chrome, Safari iOS)
            if (navigator.canShare && navigator.canShare({ files: [file] })) {
                try {
                    await navigator.share({
                        title: `I'm going to ${state.eventData.title}!`,
                        text: `Check out ${state.eventData.title} on ${state.siteTitle || 'Eventlyy'}:`,
                        url: state.eventData.url,
                        files: [file]
                    });
                } catch (err) {
                    if (err.name !== 'AbortError') {
                        downloadCard();
                    }
                }
            } else {
                // Fallback: download card and copy event link
                downloadCard();
                if (navigator.clipboard) {
                    navigator.clipboard.writeText(state.eventData.url);
                    alert('Story pass downloaded! Real entry QR is safely kept in your account. Open Instagram or WhatsApp to post your story.');
                }
            }
        }, 'image/png', 0.95);
    }

})();
