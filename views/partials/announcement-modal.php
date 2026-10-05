<?php

/**
 * Platform Announcement & Holiday Celebration Modal
 *
 * Prominent centered modal card for public visitors to inform about holidays,
 * milestones, anniversaries, promotions, or platform notices.
 * Remembers user dismissal per announcement version via localStorage.
 *
 * @package Eventlyy
 * @subpackage Views/Partials
 */

$announcementActive = (site_setting('announcement_enabled', '0') === '1');
$announcementTitle = trim(site_setting('announcement_title', ''));
$announcementMessage = trim(site_setting('announcement_message', ''));
$announcementBadge = trim(site_setting('announcement_badge', ''));
$announcementImage = trim(site_setting('announcement_image', ''));
$announcementLinkText = trim(site_setting('announcement_link_text', ''));
$announcementLinkUrl = trim(site_setting('announcement_link_url', ''));
$announcementDismissText = trim(site_setting('announcement_dismiss_text', ''));
$announcementUpdatedAt = trim(site_setting('announcement_updated_at', '1'));
$announcementLocation = site_setting('announcement_location', 'home');

// Determine if current page matches target location
$currentUri = trim(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH), '/');
$isHomePage = ($currentUri === '' || $currentUri === 'index.php');

// Active if enabled, on target location, and has at least an image, title, or message
$hasContent = !empty($announcementTitle) || !empty($announcementMessage) || !empty($announcementImage);
$hasBody = !empty($announcementBadge) || !empty($announcementTitle) || !empty($announcementMessage) || !empty($announcementLinkUrl) || !empty($announcementDismissText);
$shouldDisplay = $announcementActive && $hasContent && ($announcementLocation === 'sitewide' || $isHomePage);
?>

<?php if ($shouldDisplay): ?>
<style>
/* ═════════════════════════════════════════════════════════════════
   Platform Announcement / Celebration Modal (.pl-announcement-*)
   Ultra-modern glassmorphic design: Translucent frosted glass,
   ambient page blending, responsive fluid layout (Desktop, Tablet, Mobile).
   ═════════════════════════════════════════════════════════════════ */

.pl-announcement-overlay {
    display: none;
    position: fixed;
    inset: 0;
    z-index: 100000;
    align-items: center;
    justify-content: center;
    padding: clamp(0.75rem, 3vw, 1.75rem);
    box-sizing: border-box;
    opacity: 0;
    transition: opacity 0.28s cubic-bezier(0.16, 1, 0.3, 1);
}

.pl-announcement-overlay.is-visible {
    opacity: 1;
}

/* Translucent ambient backdrop blending smoothly into the underlying page */
.pl-announcement-backdrop {
    position: absolute;
    inset: 0;
    background: radial-gradient(circle at 50% 35%, rgba(var(--brand), 0.22) 0%, rgba(0, 0, 0, 0.55) 100%);
    backdrop-filter: blur(14px) saturate(160%);
    -webkit-backdrop-filter: blur(14px) saturate(160%);
    cursor: pointer;
    transition: backdrop-filter 0.3s ease;
}

/* Glassmorphic floating card with delicate edge highlights & frosted depth */
.pl-announcement-card {
    position: relative;
    width: 100%;
    max-width: min(92vw, 520px);
    max-height: min(88vh, 740px);
    background: linear-gradient(145deg, 
        rgba(var(--card), 0.78) 0%, 
        rgba(var(--card), 0.90) 100%
    );
    color: rgb(var(--text));
    border: 1px solid rgba(255, 255, 255, 0.18);
    border-radius: clamp(20px, 4vw, 30px);
    box-shadow: 
        0 28px 70px -15px rgba(0, 0, 0, 0.45),
        0 0 0 1px rgba(var(--brand), 0.15),
        0 0 45px rgba(var(--brand), 0.12),
        inset 0 1px 1px rgba(255, 255, 255, 0.3);
    backdrop-filter: blur(30px) saturate(190%);
    -webkit-backdrop-filter: blur(30px) saturate(190%);
    overflow-y: auto;
    overflow-x: hidden;
    z-index: 1;
    transform: scale(0.92) translateY(12px);
    transition: transform 0.32s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.3s ease;
    box-sizing: border-box;
    scrollbar-width: thin;
    scrollbar-color: rgba(var(--muted), 0.3) transparent;
}

.pl-announcement-card::-webkit-scrollbar {
    width: 6px;
}
.pl-announcement-card::-webkit-scrollbar-thumb {
    background: rgba(var(--muted), 0.25);
    border-radius: 10px;
}

.pl-announcement-overlay.is-visible .pl-announcement-card {
    transform: scale(1) translateY(0);
}

/* Frosted glass circular close button */
.pl-announcement-close {
    position: absolute;
    top: 1rem;
    right: 1rem;
    width: 38px;
    height: 38px;
    border-radius: 50%;
    background: rgba(var(--card), 0.7);
    border: 1px solid rgba(255, 255, 255, 0.22);
    color: rgb(var(--text));
    font-size: 1.25rem;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 10;
    backdrop-filter: blur(16px);
    -webkit-backdrop-filter: blur(16px);
    box-shadow: 0 4px 14px rgba(0, 0, 0, 0.2);
    transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    -webkit-tap-highlight-color: transparent;
}

.pl-announcement-close:hover {
    background: rgba(var(--card), 0.95);
    transform: scale(1.08) rotate(90deg);
    border-color: rgba(var(--brand), 0.4);
    color: rgb(var(--brand));
}

/* Visual banner with soft edge blending */
.pl-announcement-media {
    position: relative;
    width: 100%;
    height: clamp(160px, 32vw, 230px);
    overflow: hidden;
    background: rgba(0, 0, 0, 0.12);
}

.pl-announcement-media.is-image-only {
    height: auto;
    max-height: min(84vh, 720px);
    background: transparent;
    display: flex;
    align-items: center;
    justify-content: center;
}

.pl-announcement-media img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
    transition: transform 0.6s ease;
}

.pl-announcement-media.is-image-only img {
    height: auto;
    max-height: min(84vh, 720px);
    width: 100%;
    object-fit: contain;
    border-radius: clamp(20px, 4vw, 30px);
    box-shadow: 0 24px 60px -10px rgba(0, 0, 0, 0.6), 0 0 0 1px rgba(255, 255, 255, 0.15);
}

.pl-announcement-media-link {
    display: block;
    width: 100%;
    height: 100%;
    text-decoration: none;
    cursor: pointer;
}

.pl-announcement-card--image-only {
    background: transparent;
    border: none;
    box-shadow: none;
    overflow: visible;
}

.pl-announcement-overlay.is-visible .pl-announcement-media img {
    transform: scale(1.01);
}

/* Seamless blend gradient between the banner and the card body */
.pl-announcement-media:not(.is-image-only)::after {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(to bottom, 
        rgba(0, 0, 0, 0.2) 0%, 
        transparent 35%, 
        rgba(var(--card), 0.85) 92%,
        rgba(var(--card), 0.95) 100%
    );
    pointer-events: none;
}

/* Card inner content */
.pl-announcement-body {
    padding: clamp(1.4rem, 4vw, 2.25rem) clamp(1.25rem, 3.5vw, 2rem) clamp(1.5rem, 4vw, 2.25rem);
    text-align: center;
    position: relative;
    z-index: 2;
}

/* Floating celebratory badge pill */
.pl-announcement-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    padding: 0.35rem 1rem;
    border-radius: 999px;
    background: linear-gradient(135deg, rgba(var(--brand), 0.18) 0%, rgba(var(--brand), 0.08) 100%);
    color: rgb(var(--brand));
    font-weight: 800;
    font-size: 0.82rem;
    letter-spacing: 0.025em;
    border: 1px solid rgba(var(--brand), 0.32);
    box-shadow: 0 4px 14px rgba(var(--brand), 0.16);
    margin-bottom: 0.85rem;
}

/* Celebratory title */
.pl-announcement-title {
    font-size: clamp(1.35rem, 3.8vw, 1.8rem);
    font-weight: 900;
    line-height: 1.24;
    margin: 0 0 0.85rem;
    color: rgb(var(--text));
    letter-spacing: -0.025em;
}

/* Body message text */
.pl-announcement-text {
    font-size: clamp(0.92rem, 1.8vw, 0.98rem);
    line-height: 1.65;
    color: rgba(var(--text), 0.82);
    margin-bottom: 1.75rem;
    word-break: break-word;
    text-align: center;
}

/* Responsive Action Buttons */
.pl-announcement-actions {
    display: flex;
    gap: 0.75rem;
    justify-content: center;
    align-items: center;
    flex-wrap: wrap;
}

.pl-announcement-btn {
    height: 48px;
    padding: 0 1.65rem;
    border-radius: 14px;
    font-size: 0.94rem;
    font-weight: 800;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    border: none;
    transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    flex: 1;
    min-width: 140px;
    box-sizing: border-box;
    -webkit-tap-highlight-color: transparent;
}

/* Primary CTA button with glowing brand gradient */
.pl-announcement-btn--primary {
    background: linear-gradient(135deg, rgb(var(--brand)) 0%, color-mix(in srgb, rgb(var(--brand)) 75%, #059669) 100%);
    color: #ffffff !important;
    box-shadow: 0 8px 24px -4px rgba(var(--brand), 0.45);
}

.pl-announcement-btn--primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 30px -4px rgba(var(--brand), 0.55);
    filter: brightness(1.06);
}

.pl-announcement-btn--primary:active {
    transform: translateY(0);
}

/* Dismiss / Cancel button with translucent glass aesthetics */
.pl-announcement-btn--dismiss {
    background: rgba(var(--muted), 0.08);
    color: rgb(var(--text)) !important;
    border: 1px solid rgba(var(--muted), 0.22);
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
}

.pl-announcement-btn--dismiss:hover {
    background: rgba(var(--muted), 0.16);
    border-color: rgba(var(--muted), 0.35);
    transform: translateY(-1px);
}

.pl-announcement-btn--dismiss:active {
    transform: translateY(0);
}

/* ── Tablet Optimization (601px - 900px) ── */
@media (min-width: 601px) and (max-width: 900px) {
    .pl-announcement-card {
        max-width: 480px;
    }
}

/* ── Mobile Optimization (<= 600px) ── */
@media (max-width: 600px) {
    .pl-announcement-overlay {
        padding: 0.85rem;
    }
    .pl-announcement-card {
        border-radius: 22px;
        max-width: 100%;
    }
    .pl-announcement-media {
        height: 160px;
    }
    .pl-announcement-body {
        padding: 1.35rem 1.15rem 1.45rem;
    }
    .pl-announcement-title {
        font-size: 1.32rem;
    }
    .pl-announcement-text {
        font-size: 0.90rem;
        margin-bottom: 1.35rem;
    }
    .pl-announcement-actions {
        flex-direction: column;
        gap: 0.65rem;
    }
    .pl-announcement-btn {
        width: 100%;
        min-width: 100%;
        height: 48px;
    }
    .pl-announcement-btn--dismiss {
        order: 2;
    }
    .pl-announcement-btn--primary {
        order: 1;
    }
}
</style>

<div id="platform-announcement-modal" class="pl-announcement-overlay" role="dialog" aria-modal="true" <?= !empty($announcementTitle) ? 'aria-labelledby="pl-announcement-title"' : 'aria-label="Platform Announcement"' ?> data-updated-at="<?= e($announcementUpdatedAt) ?>">
    <div class="pl-announcement-backdrop" id="btn-announcement-backdrop"></div>
    <div class="pl-announcement-card<?= (!$hasBody && !empty($announcementImage)) ? ' pl-announcement-card--image-only' : '' ?>">
        <button type="button" class="pl-announcement-close" id="btn-announcement-close" aria-label="Close Announcement">&times;</button>
        
        <?php if (!empty($announcementImage)): 
            $annImgSrc = str_starts_with($announcementImage, 'http') ? $announcementImage : asset($announcementImage);
        ?>
            <div class="pl-announcement-media<?= !$hasBody ? ' is-image-only' : '' ?>">
                <?php if (!empty($announcementLinkUrl)): ?>
                    <a href="<?= e($announcementLinkUrl) ?>" class="pl-announcement-media-link" title="<?= e($announcementTitle ?: 'View Announcement') ?>">
                        <img src="<?= e($annImgSrc) ?>" alt="<?= e($announcementTitle ?: 'Announcement Banner') ?>">
                    </a>
                <?php else: ?>
                    <img src="<?= e($annImgSrc) ?>" alt="<?= e($announcementTitle ?: 'Announcement Banner') ?>">
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if ($hasBody): ?>
            <div class="pl-announcement-body">
                <?php if (!empty($announcementBadge)): ?>
                    <span class="pl-announcement-badge"><?= e($announcementBadge) ?></span>
                <?php endif; ?>

                <?php if (!empty($announcementTitle)): ?>
                    <h2 id="pl-announcement-title" class="pl-announcement-title"><?= e($announcementTitle) ?></h2>
                <?php endif; ?>

                <?php if (!empty($announcementMessage)): ?>
                    <div class="pl-announcement-text">
                        <?= nl2br(e($announcementMessage)) ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($announcementLinkUrl) || !empty($announcementDismissText)): ?>
                    <div class="pl-announcement-actions">
                        <?php if (!empty($announcementLinkUrl)): ?>
                            <a href="<?= e($announcementLinkUrl) ?>" class="pl-announcement-btn pl-announcement-btn--primary" id="btn-announcement-action">
                                <?= e(!empty($announcementLinkText) ? $announcementLinkText : 'Check It Out →') ?>
                            </a>
                        <?php endif; ?>

                        <?php if (!empty($announcementDismissText)): ?>
                            <button type="button" class="pl-announcement-btn pl-announcement-btn--dismiss" id="btn-announcement-dismiss">
                                <?= e($announcementDismissText) ?>
                            </button>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var modal = document.getElementById('platform-announcement-modal');
    if (!modal) return;

    var updatedAt = modal.getAttribute('data-updated-at') || '1';
    var storageKey = 'eventlyy_announcement_seen';
    var dismissedAt = null;

    try {
        dismissedAt = localStorage.getItem(storageKey);
    } catch (e) {}

    // If user already dismissed this specific announcement version, do not pop up again
    if (dismissedAt === updatedAt) {
        return;
    }

    // Graceful delayed entrance
    setTimeout(function() {
        modal.style.display = 'flex';
        requestAnimationFrame(function() {
            modal.classList.add('is-visible');
        });
        document.body.style.overflow = 'hidden';
    }, 450);

    function dismissModal() {
        modal.classList.remove('is-visible');
        setTimeout(function() {
            modal.style.display = 'none';
            document.body.style.overflow = '';
        }, 250);
        try {
            localStorage.setItem(storageKey, updatedAt);
        } catch (e) {}
    }

    var btnClose = document.getElementById('btn-announcement-close');
    var btnDismiss = document.getElementById('btn-announcement-dismiss');
    var backdrop = document.getElementById('btn-announcement-backdrop');
    var btnAction = document.getElementById('btn-announcement-action');

    if (btnClose) btnClose.addEventListener('click', dismissModal);
    if (btnDismiss) btnDismiss.addEventListener('click', dismissModal);
    if (backdrop) backdrop.addEventListener('click', dismissModal);
    if (btnAction) btnAction.addEventListener('click', function() {
        try {
            localStorage.setItem(storageKey, updatedAt);
        } catch (e) {}
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && modal.classList.contains('is-visible')) {
            dismissModal();
        }
    });
});
</script>
<?php endif; ?>
