<?php

/**
 * Theme Dynamic Styles Partial
 *
 * Injects CSS custom properties dynamically based on the Admin Settings color configuration.
 * Seamlessly propagates active primary & secondary colors across client, host, admin, and workstation layouts,
 * including vintage components, custom scrollbars, footer links/socials, buttons, and high-contrast navbars.
 *
 * @package Eventlyy
 * @subpackage Views/Partials
 */

$primaryHex = function_exists('theme_primary_color') ? theme_primary_color() : '#108a55';
$secondaryHex = function_exists('theme_secondary_color') ? theme_secondary_color() : '#1aae6c';

if (!preg_match('/^#[0-9a-fA-F]{6}$/', $primaryHex)) {
    $primaryHex = '#108a55';
}
if (!preg_match('/^#[0-9a-fA-F]{6}$/', $secondaryHex)) {
    $secondaryHex = '#1aae6c';
}

$toRgbTriple = function (string $hex): string {
    $hex = ltrim($hex, '#');
    $r = hexdec(substr($hex, 0, 2));
    $g = hexdec(substr($hex, 2, 2));
    $b = hexdec(substr($hex, 4, 2));
    return "$r $g $b";
};

// Compute high-contrast text color (YIQ luminance)
$getContrastYIQ = function (string $hex): string {
    $hex = ltrim($hex, '#');
    $r = hexdec(substr($hex, 0, 2));
    $g = hexdec(substr($hex, 2, 2));
    $b = hexdec(substr($hex, 4, 2));
    $yiq = (($r * 299) + ($g * 587) + ($b * 114)) / 1000;
    return ($yiq >= 170) ? '#0f172a' : '#ffffff';
};

// Compute vibrant contrast-adjusted shade for dark backgrounds
$toDarkVariant = function (string $hex): array {
    $hex = ltrim($hex, '#');
    $r = hexdec(substr($hex, 0, 2)) / 255;
    $g = hexdec(substr($hex, 2, 2)) / 255;
    $b = hexdec(substr($hex, 4, 2)) / 255;

    $max = max($r, $g, $b);
    $min = min($r, $g, $b);
    $l = ($max + $min) / 2;

    if ($max === $min) {
        $h = $s = 0;
    } else {
        $d = $max - $min;
        $s = $l > 0.5 ? $d / (2 - $max - $min) : $d / ($max + $min);
        switch ($max) {
            case $r:
                $h = ($g - $b) / $d + ($g < $b ? 6 : 0);
                break;
            case $g:
                $h = ($b - $r) / $d + 2;
                break;
            case $b:
                $h = ($r - $g) / $d + 4;
                break;
        }
        $h /= 6;
    }

    // Boost lightness for dark mode readability
    $targetL = min(0.68, max(0.48, $l + 0.14));
    $targetS = min(0.95, max(0.55, $s * 1.05));

    $hue2rgb = function ($p, $q, $t) {
        if ($t < 0) $t += 1;
        if ($t > 1) $t -= 1;
        if ($t < 1 / 6) return $p + ($q - $p) * 6 * $t;
        if ($t < 1 / 2) return $q;
        if ($t < 2 / 3) return $p + ($q - $p) * (2 / 3 - $t) * 6;
        return $p;
    };

    $q = $targetL < 0.5 ? $targetL * (1 + $targetS) : $targetL + $targetS - $targetL * $targetS;
    $p = 2 * $targetL - $q;

    $nR = (int)round($hue2rgb($p, $q, $h + 1 / 3) * 255);
    $nG = (int)round($hue2rgb($p, $q, $h) * 255);
    $nB = (int)round($hue2rgb($p, $q, $h - 1 / 3) * 255);

    $darkHex = sprintf('#%02x%02x%02x', $nR, $nG, $nB);
    $darkRgb = "$nR $nG $nB";

    return [$darkHex, $darkRgb];
};

$primaryRgb = $toRgbTriple($primaryHex);
$secondaryRgb = $toRgbTriple($secondaryHex);
$onPrimaryColor = $getContrastYIQ($primaryHex);
$onSecondaryColor = $getContrastYIQ($secondaryHex);

list($darkPrimaryHex, $darkPrimaryRgb) = $toDarkVariant($primaryHex);
list($darkSecondaryHex, $darkSecondaryRgb) = $toDarkVariant($secondaryHex);
$onDarkPrimaryColor = $getContrastYIQ($darkPrimaryHex);
$onDarkSecondaryColor = $getContrastYIQ($darkSecondaryHex);
?>
<style id="eventlyy-theme-styles">
/* ══════════════════════════════════════════════════════════════════════════════
   DYNAMIC THEME VARIABLES (ADMIN CONFIGURABLE DUO-TONE SYSTEM)
   Primary: <?= $primaryHex ?> | Secondary: <?= $secondaryHex ?>
   ══════════════════════════════════════════════════════════════════════════════ */
:root {
  /* Core brand tokens */
  --brand: <?= $primaryRgb ?>;
  --accent: <?= $secondaryRgb ?>;
  --accent-soft: rgba(<?= $secondaryRgb ?>, 0.14);
  --brand-soft: rgba(<?= $primaryRgb ?>, 0.14);
  --gradient-brand: linear-gradient(135deg, <?= $primaryHex ?> 0%, <?= $secondaryHex ?> 100%);
  --gradient-brand-h: linear-gradient(90deg, <?= $primaryHex ?> 0%, <?= $secondaryHex ?> 100%);
  --gradient-brand-hover: linear-gradient(135deg, <?= $secondaryHex ?> 0%, <?= $primaryHex ?> 100%);
  --shadow-brand: 0 12px 36px rgba(<?= $primaryRgb ?>, 0.25);
  --shadow-duo: 0 10px 25px rgba(<?= $primaryRgb ?>, 0.28), 0 4px 12px rgba(<?= $secondaryRgb ?>, 0.2);
  --brand-hex: <?= $primaryHex ?>;
  --accent-hex: <?= $secondaryHex ?>;
  --primary: <?= $primaryHex ?>;
  --primary-color: <?= $primaryHex ?>;
  --primary-rgb: <?= $primaryRgb ?>;
  --primary-hover: <?= $secondaryHex ?>;
  --secondary: <?= $secondaryHex ?>;
  --secondary-color: <?= $secondaryHex ?>;
  --secondary-rgb: <?= $secondaryRgb ?>;
  --on-primary: <?= $onPrimaryColor ?>;
  --on-secondary: <?= $onSecondaryColor ?>;

  /* Bootstrap 5 system variables */
  --bs-primary: <?= $primaryHex ?> !important;
  --bs-primary-rgb: <?= $primaryRgb ?> !important;
  --bs-link-color: <?= $primaryHex ?> !important;
  --bs-link-hover-color: <?= $secondaryHex ?> !important;
  --bs-primary-border-subtle: rgba(<?= $primaryRgb ?>, 0.25) !important;
  --bs-primary-bg-subtle: rgba(<?= $primaryRgb ?>, 0.1) !important;

  /* Vintage & Heritage master component tokens (Balanced Duo-Tone) */
  --vn-brass: <?= $secondaryHex ?> !important;
  --vn-brass-light: <?= $secondaryHex ?> !important;
  --vn-brass-glow: rgba(<?= $secondaryRgb ?>, 0.22) !important;
  --vn-forest: <?= $primaryHex ?> !important;
  --vn-forest-tint: rgba(<?= $primaryRgb ?>, 0.08) !important;
  --ve-brass: <?= $secondaryHex ?> !important;
  --ve-brass-light: <?= $secondaryHex ?> !important;
  --ve-brass-glow: rgba(<?= $secondaryRgb ?>, 0.22) !important;
  --ve-forest: <?= $primaryHex ?> !important;
}

:root[data-theme='dark'],
[data-bs-theme='dark'] {
  /* Core brand tokens (calibrated for dark contrast) */
  --brand: <?= $darkPrimaryRgb ?>;
  --accent: <?= $darkSecondaryRgb ?>;
  --accent-soft: rgba(<?= $darkSecondaryRgb ?>, 0.18);
  --brand-soft: rgba(<?= $darkPrimaryRgb ?>, 0.18);
  --gradient-brand: linear-gradient(135deg, <?= $darkPrimaryHex ?> 0%, <?= $darkSecondaryHex ?> 100%);
  --gradient-brand-h: linear-gradient(90deg, <?= $darkPrimaryHex ?> 0%, <?= $darkSecondaryHex ?> 100%);
  --gradient-brand-hover: linear-gradient(135deg, <?= $darkSecondaryHex ?> 0%, <?= $darkPrimaryHex ?> 100%);
  --shadow-brand: 0 12px 36px rgba(<?= $darkPrimaryRgb ?>, 0.35);
  --shadow-duo: 0 10px 25px rgba(<?= $darkPrimaryRgb ?>, 0.35), 0 4px 12px rgba(<?= $darkSecondaryRgb ?>, 0.25);
  --brand-hex: <?= $darkPrimaryHex ?>;
  --accent-hex: <?= $darkSecondaryHex ?>;
  --primary: <?= $darkPrimaryHex ?>;
  --primary-color: <?= $darkPrimaryHex ?>;
  --primary-rgb: <?= $darkPrimaryRgb ?>;
  --primary-hover: <?= $darkSecondaryHex ?>;
  --secondary: <?= $darkSecondaryHex ?>;
  --secondary-color: <?= $darkSecondaryHex ?>;
  --secondary-rgb: <?= $darkSecondaryRgb ?>;
  --on-primary: <?= $onDarkPrimaryColor ?>;
  --on-secondary: <?= $onDarkSecondaryColor ?>;

  /* Bootstrap 5 system variables in dark mode */
  --bs-primary: <?= $darkPrimaryHex ?> !important;
  --bs-primary-rgb: <?= $darkPrimaryRgb ?> !important;
  --bs-link-color: <?= $darkPrimaryHex ?> !important;
  --bs-link-hover-color: <?= $darkSecondaryHex ?> !important;
  --bs-primary-border-subtle: rgba(<?= $darkPrimaryRgb ?>, 0.35) !important;
  --bs-primary-bg-subtle: rgba(<?= $darkPrimaryRgb ?>, 0.15) !important;

  /* Vintage & Heritage master component tokens in dark mode */
  --vn-brass: <?= $darkSecondaryHex ?> !important;
  --vn-brass-light: <?= $darkSecondaryHex ?> !important;
  --vn-brass-glow: rgba(<?= $darkSecondaryRgb ?>, 0.28) !important;
  --vn-forest: <?= $darkPrimaryHex ?> !important;
  --vn-forest-tint: rgba(<?= $darkPrimaryRgb ?>, 0.14) !important;
  --ve-brass: <?= $darkSecondaryHex ?> !important;
  --ve-brass-light: <?= $darkSecondaryHex ?> !important;
  --ve-brass-glow: rgba(<?= $darkSecondaryRgb ?>, 0.28) !important;
  --ve-forest: <?= $darkPrimaryHex ?> !important;
}

/* ── Top Navigation Bar Decorative Pattern ────────────────────────────────── */
.top-nav::before {
  background: repeating-linear-gradient(
    90deg,
    <?= $primaryHex ?> 0px,
    <?= $primaryHex ?> 14px,
    transparent 14px,
    transparent 20px,
    <?= $secondaryHex ?> 20px,
    <?= $secondaryHex ?> 34px,
    transparent 34px,
    transparent 40px
  ) !important;
}

:root[data-theme='dark'] .top-nav::before,
[data-bs-theme='dark'] .top-nav::before {
  background: repeating-linear-gradient(
    90deg,
    <?= $darkPrimaryHex ?> 0px,
    <?= $darkPrimaryHex ?> 14px,
    transparent 14px,
    transparent 20px,
    <?= $darkSecondaryHex ?> 20px,
    <?= $darkSecondaryHex ?> 34px,
    transparent 34px,
    transparent 40px
  ) !important;
}

/* ── Header Navigation Links & Active State (High Contrast) ───────────────── */
/* Inactive links: always clear and legible */
.top-nav .nav-link,
.top-nav .nav-desktop .nav-link,
.vn-shell .top-nav .nav-link,
.nav-desktop .nav-link {
  color: var(--vn-ink, #1e293b) !important;
  background: transparent !important;
  font-weight: 600 !important;
  letter-spacing: 0.01em !important;
  transition: all 0.15s ease-out !important;
}

:root[data-theme='dark'] .top-nav .nav-link,
:root[data-theme='dark'] .top-nav .nav-desktop .nav-link,
:root[data-theme='dark'] .vn-shell .top-nav .nav-link,
:root[data-theme='dark'] .nav-desktop .nav-link,
[data-bs-theme='dark'] .top-nav .nav-link,
[data-bs-theme='dark'] .nav-desktop .nav-link {
  color: var(--vn-ink, #f8fafc) !important;
  background: transparent !important;
}

/* Hovered state: soft background tint with primary text */
.top-nav .nav-link:hover,
.top-nav .nav-desktop .nav-link:hover,
.vn-shell .top-nav .nav-link:hover,
.nav-desktop .nav-link:hover {
  color: <?= $primaryHex ?> !important;
  background: rgba(<?= $primaryRgb ?>, 0.1) !important;
  transform: translateY(-1px);
}

:root[data-theme='dark'] .top-nav .nav-link:hover,
:root[data-theme='dark'] .top-nav .nav-desktop .nav-link:hover,
:root[data-theme='dark'] .vn-shell .top-nav .nav-link:hover,
:root[data-theme='dark'] .nav-desktop .nav-link:hover,
[data-bs-theme='dark'] .top-nav .nav-link:hover,
[data-bs-theme='dark'] .nav-desktop .nav-link:hover {
  color: <?= $darkPrimaryHex ?> !important;
  background: rgba(<?= $darkPrimaryRgb ?>, 0.16) !important;
}

/* Active state: solid primary pill with calibrated high-contrast text */
.top-nav .nav-link.active,
.top-nav .nav-desktop .nav-link.active,
.top-nav .nav-desktop .nav-link[aria-current="true"],
.vn-shell .top-nav .nav-link.active,
.nav-desktop .nav-link.active,
.nav-desktop .nav-link[aria-current="true"] {
  background: <?= $primaryHex ?> !important;
  color: <?= $onPrimaryColor ?> !important;
  box-shadow: 0 2px 8px rgba(<?= $primaryRgb ?>, 0.32) !important;
  font-weight: 700 !important;
}

:root[data-theme='dark'] .top-nav .nav-link.active,
:root[data-theme='dark'] .top-nav .nav-desktop .nav-link.active,
:root[data-theme='dark'] .top-nav .nav-desktop .nav-link[aria-current="true"],
:root[data-theme='dark'] .vn-shell .top-nav .nav-link.active,
:root[data-theme='dark'] .nav-desktop .nav-link.active,
:root[data-theme='dark'] .nav-desktop .nav-link[aria-current="true"],
[data-bs-theme='dark'] .top-nav .nav-link.active,
[data-bs-theme='dark'] .nav-desktop .nav-link.active,
[data-bs-theme='dark'] .nav-desktop .nav-link[aria-current="true"] {
  background: <?= $darkPrimaryHex ?> !important;
  color: <?= $onDarkPrimaryColor ?> !important;
  box-shadow: 0 2px 10px rgba(<?= $darkPrimaryRgb ?>, 0.4) !important;
  font-weight: 700 !important;
}

/* ── Mobile & Bottom Navigation ───────────────────────────────────────────── */
.bottom-nav .nav-link.active,
.bottom-nav .nav-item.active {
  color: <?= $primaryHex ?> !important;
  background: transparent !important;
}

:root[data-theme='dark'] .bottom-nav .nav-link.active,
:root[data-theme='dark'] .bottom-nav .nav-item.active {
  color: <?= $darkPrimaryHex ?> !important;
  background: transparent !important;
}

/* ── Custom Scrollbar Overrides (Light & Dark) ────────────────────────────── */
html {
  scrollbar-color: <?= $primaryHex ?> rgba(0, 0, 0, 0.05) !important;
}

:root[data-theme='dark'],
[data-bs-theme='dark'] {
  scrollbar-color: <?= $darkPrimaryHex ?> rgba(255, 255, 255, 0.06) !important;
}

::-webkit-scrollbar-track {
  background: rgba(<?= $primaryRgb ?>, 0.06) !important;
}

:root[data-theme='dark'] ::-webkit-scrollbar-track,
[data-bs-theme='dark'] ::-webkit-scrollbar-track {
  background: rgba(<?= $darkPrimaryRgb ?>, 0.08) !important;
}

::-webkit-scrollbar-thumb {
  background: linear-gradient(180deg, <?= $primaryHex ?> 0%, <?= $secondaryHex ?> 100%) !important;
  border-radius: 999px !important;
}

::-webkit-scrollbar-thumb:hover {
  background: linear-gradient(180deg, <?= $secondaryHex ?> 0%, <?= $primaryHex ?> 100%) !important;
}

:root[data-theme='dark'] ::-webkit-scrollbar-thumb,
[data-bs-theme='dark'] ::-webkit-scrollbar-thumb {
  background: linear-gradient(180deg, <?= $darkPrimaryHex ?> 0%, <?= $darkSecondaryHex ?> 100%) !important;
  box-shadow: 0 0 10px rgba(<?= $darkPrimaryRgb ?>, 0.35) !important;
}

:root[data-theme='dark'] ::-webkit-scrollbar-thumb:hover,
[data-bs-theme='dark'] ::-webkit-scrollbar-thumb:hover {
  background: linear-gradient(180deg, <?= $darkSecondaryHex ?> 0%, <?= $darkPrimaryHex ?> 100%) !important;
}

.admin-grid .event-list::-webkit-scrollbar-thumb {
  background-color: <?= $primaryHex ?> !important;
}

.admin-grid .event-list::-webkit-scrollbar-thumb:hover {
  background-color: <?= $secondaryHex ?> !important;
}

/* ── Footer Overrides & Hover Effects (Duo-Tone Interplay) ─────────────────── */
.footer::before,
.site-footer::before {
  background: linear-gradient(90deg, <?= $primaryHex ?> 0%, <?= $secondaryHex ?> 50%, <?= $primaryHex ?> 100%) !important;
  height: 3px !important;
  opacity: 0.9 !important;
}

.footer-section h4 {
  color: <?= $primaryHex ?> !important;
}

.footer-section nav a:hover,
.footer-section nav a:focus,
.footer-links a:hover,
.site-footer a:hover,
.footer a:hover {
  color: <?= $secondaryHex ?> !important;
}

.footer-socials a:hover {
  background: <?= $secondaryHex ?> !important;
  border-color: <?= $secondaryHex ?> !important;
  color: #ffffff !important;
  box-shadow: 0 4px 14px rgba(<?= $secondaryRgb ?>, 0.35) !important;
}

:root[data-theme='dark'] .footer::before,
:root[data-theme='dark'] .site-footer::before {
  background: linear-gradient(90deg, <?= $darkPrimaryHex ?> 0%, <?= $darkSecondaryHex ?> 50%, <?= $darkPrimaryHex ?> 100%) !important;
}

:root[data-theme='dark'] .footer-section h4 {
  color: <?= $darkPrimaryHex ?> !important;
}

:root[data-theme='dark'] .footer-section nav a:hover,
:root[data-theme='dark'] .footer-section nav a:focus,
:root[data-theme='dark'] .footer-links a:hover,
:root[data-theme='dark'] .site-footer a:hover,
:root[data-theme='dark'] .footer a:hover {
  color: <?= $darkSecondaryHex ?> !important;
}

:root[data-theme='dark'] .footer-socials a:hover {
  background: <?= $darkSecondaryHex ?> !important;
  border-color: <?= $darkSecondaryHex ?> !important;
  color: #ffffff !important;
}

/* ── Hero & Signature Duo-Tone Action Buttons ─────────────────────────────── */
.hero-btn--primary,
.ve-hero-btn--primary,
.host-hero-btn--primary,
.btn-brand-gradient,
.btn-duo-tone {
  background: linear-gradient(135deg, <?= $primaryHex ?> 0%, <?= $secondaryHex ?> 100%) !important;
  border: none !important;
  color: <?= $onPrimaryColor ?> !important;
  box-shadow: 0 4px 18px rgba(<?= $primaryRgb ?>, 0.28), 0 2px 8px rgba(<?= $secondaryRgb ?>, 0.18) !important;
  transition: all 0.22s ease-out !important;
}

.hero-btn--primary:hover,
.ve-hero-btn--primary:hover,
.host-hero-btn--primary:hover,
.btn-brand-gradient:hover,
.btn-duo-tone:hover {
  background: linear-gradient(135deg, <?= $secondaryHex ?> 0%, <?= $primaryHex ?> 100%) !important;
  box-shadow: 0 6px 24px rgba(<?= $primaryRgb ?>, 0.35), 0 3px 12px rgba(<?= $secondaryRgb ?>, 0.26) !important;
  transform: translateY(-2px) !important;
  color: <?= $onSecondaryColor ?> !important;
}

:root[data-theme='dark'] .hero-btn--primary,
:root[data-theme='dark'] .ve-hero-btn--primary,
:root[data-theme='dark'] .host-hero-btn--primary,
:root[data-theme='dark'] .btn-brand-gradient,
:root[data-theme='dark'] .btn-duo-tone {
  background: linear-gradient(135deg, <?= $darkPrimaryHex ?> 0%, <?= $darkSecondaryHex ?> 100%) !important;
  color: <?= $onDarkPrimaryColor ?> !important;
  box-shadow: 0 4px 20px rgba(<?= $darkPrimaryRgb ?>, 0.35), 0 2px 10px rgba(<?= $darkSecondaryRgb ?>, 0.22) !important;
}

:root[data-theme='dark'] .hero-btn--primary:hover,
:root[data-theme='dark'] .ve-hero-btn--primary:hover,
:root[data-theme='dark'] .host-hero-btn--primary:hover,
:root[data-theme='dark'] .btn-brand-gradient:hover,
:root[data-theme='dark'] .btn-duo-tone:hover {
  background: linear-gradient(135deg, <?= $darkSecondaryHex ?> 0%, <?= $darkPrimaryHex ?> 100%) !important;
  color: <?= $onDarkSecondaryColor ?> !important;
}

/* ── Primary Action Buttons (Standard) ────────────────────────────────────── */
.btn-primary,
.vn-btn-primary,
.ve-btn-primary,
.vn-shell .admin-action-btn.primary,
.vn-shell .admin-events-btn,
.vn-shell .btn-primary,
.admin-action-btn.primary,
.btn-solid-primary,
.ed-btn-primary {
  background: <?= $primaryHex ?> !important;
  border-color: <?= $primaryHex ?> !important;
  color: <?= $onPrimaryColor ?> !important;
  box-shadow: 0 4px 14px rgba(<?= $primaryRgb ?>, 0.28) !important;
}

.btn-primary:hover,
.btn-primary:focus,
.vn-btn-primary:hover,
.ve-btn-primary:hover,
.vn-shell .admin-action-btn.primary:hover,
.vn-shell .admin-events-btn:hover,
.vn-shell .btn-primary:hover,
.admin-action-btn.primary:hover,
.btn-solid-primary:hover,
.ed-btn-primary:hover {
  background: <?= $secondaryHex ?> !important;
  border-color: <?= $secondaryHex ?> !important;
  color: <?= $onSecondaryColor ?> !important;
  box-shadow: 0 6px 20px rgba(<?= $secondaryRgb ?>, 0.38) !important;
}

:root[data-theme='dark'] .btn-primary,
:root[data-theme='dark'] .vn-btn-primary,
:root[data-theme='dark'] .ve-btn-primary,
:root[data-theme='dark'] .vn-shell .admin-action-btn.primary,
:root[data-theme='dark'] .vn-shell .admin-events-btn,
:root[data-theme='dark'] .vn-shell .btn-primary,
:root[data-theme='dark'] .ed-btn-primary,
[data-bs-theme='dark'] .btn-primary {
  background: <?= $darkPrimaryHex ?> !important;
  border-color: <?= $darkPrimaryHex ?> !important;
  color: <?= $onDarkPrimaryColor ?> !important;
  box-shadow: 0 4px 16px rgba(<?= $darkPrimaryRgb ?>, 0.35) !important;
}

:root[data-theme='dark'] .btn-primary:hover,
:root[data-theme='dark'] .vn-btn-primary:hover,
:root[data-theme='dark'] .ve-btn-primary:hover,
:root[data-theme='dark'] .vn-shell .admin-action-btn.primary:hover,
:root[data-theme='dark'] .vn-shell .admin-events-btn:hover,
:root[data-theme='dark'] .vn-shell .btn-primary:hover,
:root[data-theme='dark'] .ed-btn-primary:hover,
[data-bs-theme='dark'] .btn-primary:hover {
  background: <?= $darkSecondaryHex ?> !important;
  border-color: <?= $darkSecondaryHex ?> !important;
  color: <?= $onDarkSecondaryColor ?> !important;
  box-shadow: 0 6px 22px rgba(<?= $darkSecondaryRgb ?>, 0.45) !important;
}

/* ── Secondary / Accent Action Buttons (Distinct Visual Role) ─────────────── */
.btn-secondary,
.ed-btn-accent,
.ed-btn-secondary,
.vn-btn-secondary,
.ve-btn-secondary,
.btn-accent,
.btn-solid-secondary {
  background: <?= $secondaryHex ?> !important;
  border-color: <?= $secondaryHex ?> !important;
  color: <?= $onSecondaryColor ?> !important;
  box-shadow: 0 4px 14px rgba(<?= $secondaryRgb ?>, 0.28) !important;
}

.btn-secondary:hover,
.ed-btn-accent:hover,
.ed-btn-secondary:hover,
.vn-btn-secondary:hover,
.ve-btn-secondary:hover,
.btn-accent:hover,
.btn-solid-secondary:hover {
  background: <?= $primaryHex ?> !important;
  border-color: <?= $primaryHex ?> !important;
  color: <?= $onPrimaryColor ?> !important;
  box-shadow: 0 6px 20px rgba(<?= $primaryRgb ?>, 0.38) !important;
}

:root[data-theme='dark'] .btn-secondary,
:root[data-theme='dark'] .ed-btn-accent,
:root[data-theme='dark'] .ed-btn-secondary,
:root[data-theme='dark'] .vn-btn-secondary,
:root[data-theme='dark'] .ve-btn-secondary,
:root[data-theme='dark'] .btn-accent,
:root[data-theme='dark'] .btn-solid-secondary {
  background: <?= $darkSecondaryHex ?> !important;
  border-color: <?= $darkSecondaryHex ?> !important;
  color: <?= $onDarkSecondaryColor ?> !important;
}

/* Event Detail & Admin Command Center Theme Integration */
.ed-tab-btn.active {
  color: <?= $primaryHex ?> !important;
  border-bottom-color: <?= $primaryHex ?> !important;
}
:root[data-theme='dark'] .ed-tab-btn.active {
  color: <?= $darkPrimaryHex ?> !important;
  border-bottom-color: <?= $darkPrimaryHex ?> !important;
}
.ed-kpi-prog-bar,
.ed-progress-fill,
.progress-bar {
  background: linear-gradient(90deg, <?= $primaryHex ?> 0%, <?= $secondaryHex ?> 100%) !important;
}
:root[data-theme='dark'] .ed-kpi-prog-bar,
:root[data-theme='dark'] .ed-progress-fill,
:root[data-theme='dark'] .progress-bar {
  background: linear-gradient(90deg, <?= $darkPrimaryHex ?> 0%, <?= $darkSecondaryHex ?> 100%) !important;
}
.ed-text-primary,
.ed-text-brand {
  color: <?= $primaryHex ?> !important;
}
:root[data-theme='dark'] .ed-text-primary,
:root[data-theme='dark'] .ed-text-brand {
  color: <?= $darkPrimaryHex ?> !important;
}
.ed-pill--brand,
.badge-brand,
.badge-primary {
  background: rgba(<?= $primaryRgb ?>, 0.14) !important;
  color: <?= $primaryHex ?> !important;
  border: 1px solid rgba(<?= $primaryRgb ?>, 0.28) !important;
}
:root[data-theme='dark'] .ed-pill--brand,
:root[data-theme='dark'] .badge-brand,
:root[data-theme='dark'] .badge-primary {
  background: rgba(<?= $darkPrimaryRgb ?>, 0.22) !important;
  color: <?= $darkPrimaryHex ?> !important;
}
.ed-pill--accent,
.badge-accent,
.badge-secondary,
.ed-tab-btn.active .ed-tab-count {
  background: <?= $secondaryHex ?> !important;
  color: <?= $onSecondaryColor ?> !important;
  font-weight: 800 !important;
  box-shadow: 0 2px 6px rgba(<?= $secondaryRgb ?>, 0.3) !important;
}
:root[data-theme='dark'] .ed-pill--accent,
:root[data-theme='dark'] .badge-accent,
:root[data-theme='dark'] .badge-secondary,
:root[data-theme='dark'] .ed-tab-btn.active .ed-tab-count {
  background: <?= $darkSecondaryHex ?> !important;
  color: <?= $onDarkSecondaryColor ?> !important;
}
.ed-btn-outline {
  color: <?= $primaryHex ?> !important;
  border-color: rgba(<?= $primaryRgb ?>, 0.3) !important;
}
.ed-btn-outline:hover {
  background: rgba(<?= $primaryRgb ?>, 0.1) !important;
  border-color: <?= $primaryHex ?> !important;
}
:root[data-theme='dark'] .ed-btn-outline {
  color: <?= $darkPrimaryHex ?> !important;
  border-color: rgba(<?= $darkPrimaryRgb ?>, 0.35) !important;
}
:root[data-theme='dark'] .ed-btn-outline:hover {
  background: rgba(<?= $darkPrimaryRgb ?>, 0.16) !important;
  border-color: <?= $darkPrimaryHex ?> !important;
}

/* ── Outline Buttons, Badges, and Hover Rings ─────────────────────────────── */
.btn-outline-primary,
.vn-btn-outline:hover,
.ve-btn-outline:hover {
  border-color: <?= $primaryHex ?> !important;
  color: <?= $primaryHex ?> !important;
}

.btn-outline-primary:hover {
  background: <?= $primaryHex ?> !important;
  border-color: <?= $primaryHex ?> !important;
  color: <?= $onPrimaryColor ?> !important;
}

:root[data-theme='dark'] .btn-outline-primary,
:root[data-theme='dark'] .vn-btn-outline:hover,
:root[data-theme='dark'] .ve-btn-outline:hover {
  border-color: <?= $darkPrimaryHex ?> !important;
  color: <?= $darkPrimaryHex ?> !important;
}

:root[data-theme='dark'] .btn-outline-primary:hover {
  background: <?= $darkPrimaryHex ?> !important;
  border-color: <?= $darkPrimaryHex ?> !important;
  color: <?= $onDarkPrimaryColor ?> !important;
}

.btn-outline-secondary,
.btn-outline-accent {
  border-color: <?= $secondaryHex ?> !important;
  color: <?= $secondaryHex ?> !important;
}

.btn-outline-secondary:hover,
.btn-outline-accent:hover {
  background: <?= $secondaryHex ?> !important;
  border-color: <?= $secondaryHex ?> !important;
  color: <?= $onSecondaryColor ?> !important;
}

:root[data-theme='dark'] .btn-outline-secondary,
:root[data-theme='dark'] .btn-outline-accent {
  border-color: <?= $darkSecondaryHex ?> !important;
  color: <?= $darkSecondaryHex ?> !important;
}

:root[data-theme='dark'] .btn-outline-secondary:hover,
:root[data-theme='dark'] .btn-outline-accent:hover {
  background: <?= $darkSecondaryHex ?> !important;
  border-color: <?= $darkSecondaryHex ?> !important;
  color: <?= $onDarkSecondaryColor ?> !important;
}

/* ── General Tabs & Pagination ────────────────────────────────────────────── */
.tab-btn.active,
.pagination .page-item.active .page-link {
  background: <?= $primaryHex ?> !important;
  border-color: <?= $primaryHex ?> !important;
  color: <?= $onPrimaryColor ?> !important;
}

:root[data-theme='dark'] .tab-btn.active,
:root[data-theme='dark'] .pagination .page-item.active .page-link {
  background: <?= $darkPrimaryHex ?> !important;
  border-color: <?= $darkPrimaryHex ?> !important;
  color: <?= $onDarkPrimaryColor ?> !important;
}

/* ── Form Controls Focus Rings ────────────────────────────────────────────── */
input:focus,
select:focus,
textarea:focus,
.form-control:focus,
.form-select:focus {
  border-color: <?= $primaryHex ?> !important;
  box-shadow: 0 0 0 3px rgba(<?= $primaryRgb ?>, 0.2) !important;
}

:root[data-theme='dark'] input:focus,
:root[data-theme='dark'] select:focus,
:root[data-theme='dark'] textarea:focus,
:root[data-theme='dark'] .form-control:focus,
:root[data-theme='dark'] .form-select:focus {
  border-color: <?= $darkPrimaryHex ?> !important;
  box-shadow: 0 0 0 3px rgba(<?= $darkPrimaryRgb ?>, 0.25) !important;
}

/* ── Global Selection Highlights ──────────────────────────────────────────── */
::selection {
  background: rgba(<?= $primaryRgb ?>, 0.28);
  color: inherit;
}

:root[data-theme='dark'] ::selection {
  background: rgba(<?= $darkPrimaryRgb ?>, 0.35);
  color: inherit;
}

/* ══════════════════════════════════════════════════════════════════════════════
   BALANCED DUO-TONE INTEGRATION FOR PLATFORM EXPERIENCES
   (Home, Event Details, Checkout, Success Receipt, Terms & Privacy)
   ══════════════════════════════════════════════════════════════════════════════ */

/* ── 1. Home Page Vintage Journal Experience ── */
.vn-stamp-badge {
  border-color: <?= $secondaryHex ?> !important;
  color: <?= $secondaryHex ?> !important;
}
.vn-tag--brass {
  background: <?= $secondaryHex ?> !important;
  color: <?= $onSecondaryColor ?> !important;
}
.vn-price-val {
  color: <?= $secondaryHex ?> !important;
  font-weight: 800 !important;
}
.vn-action-badge {
  background: linear-gradient(135deg, <?= $primaryHex ?> 0%, <?= $secondaryHex ?> 100%) !important;
  color: #ffffff !important;
  box-shadow: 0 4px 12px rgba(<?= $primaryRgb ?>, 0.3) !important;
}
.vn-action-badge:hover {
  background: linear-gradient(135deg, <?= $secondaryHex ?> 0%, <?= $primaryHex ?> 100%) !important;
}
.vn-trigger-filter-cta {
  background: <?= $secondaryHex ?> !important;
  color: <?= $onSecondaryColor ?> !important;
}
.vn-category-label {
  color: <?= $primaryHex ?> !important;
}
.vn-carousel-dot.active {
  background: <?= $primaryHex ?> !important;
  box-shadow: 0 0 10px rgba(<?= $primaryRgb ?>, 0.45) !important;
}

/* ── 2. Event Details Page ── */
.ve-badge--series-link {
  border-color: <?= $secondaryHex ?> !important;
  color: <?= $secondaryHex ?> !important;
}
.ve-price-val {
  color: <?= $secondaryHex ?> !important;
}
.ve-hero-btn--primary,
.ve-concluded-btn {
  background: linear-gradient(135deg, <?= $primaryHex ?> 0%, <?= $secondaryHex ?> 100%) !important;
  color: <?= $onPrimaryColor ?> !important;
  border: none !important;
}
.ve-hero-btn--primary:hover,
.ve-concluded-btn:hover {
  background: linear-gradient(135deg, <?= $secondaryHex ?> 0%, <?= $primaryHex ?> 100%) !important;
}

/* ── 3. Main Checkout Page ── */
.co-section-badge {
  background: <?= $primaryHex ?> !important;
  color: <?= $onPrimaryColor ?> !important;
  font-weight: 800 !important;
}
.co-summary-total strong,
#final-total-amount {
  color: <?= $secondaryHex ?> !important;
}
.co-promo-btn {
  background: <?= $secondaryHex ?> !important;
  color: <?= $onSecondaryColor ?> !important;
  border-color: <?= $secondaryHex ?> !important;
}
.co-promo-btn:hover {
  background: <?= $primaryHex ?> !important;
  border-color: <?= $primaryHex ?> !important;
  color: <?= $onPrimaryColor ?> !important;
}
.co-submit-btn,
.checkout-btn {
  background: linear-gradient(135deg, <?= $primaryHex ?> 0%, <?= $secondaryHex ?> 100%) !important;
  border: none !important;
  color: #ffffff !important;
  box-shadow: 0 10px 25px rgba(<?= $primaryRgb ?>, 0.3), 0 4px 10px rgba(<?= $secondaryRgb ?>, 0.2) !important;
}
.co-submit-btn:hover:not(:disabled),
.checkout-btn:hover:not(:disabled) {
  background: linear-gradient(135deg, <?= $secondaryHex ?> 0%, <?= $primaryHex ?> 100%) !important;
  transform: translateY(-2px) !important;
}
.co-admits-pill {
  background: rgba(<?= $secondaryRgb ?>, 0.14) !important;
  color: <?= $secondaryHex ?> !important;
  border: 1px solid rgba(<?= $secondaryRgb ?>, 0.28) !important;
}

/* ── 4. Success & Confirmation Receipt Page ── */
.suc-check-ring {
  background: rgba(<?= $secondaryRgb ?>, 0.15) !important;
  color: <?= $secondaryHex ?> !important;
  border: 2px solid <?= $secondaryHex ?> !important;
}
.suc-eyebrow {
  color: <?= $secondaryHex ?> !important;
}
.suc-btn--primary {
  background: linear-gradient(135deg, <?= $primaryHex ?> 0%, <?= $secondaryHex ?> 100%) !important;
  color: #ffffff !important;
  border: none !important;
}
.suc-btn--primary:hover {
  background: linear-gradient(135deg, <?= $secondaryHex ?> 0%, <?= $primaryHex ?> 100%) !important;
}
.suc-btn--secondary {
  border-color: rgba(<?= $primaryRgb ?>, 0.32) !important;
  color: <?= $primaryHex ?> !important;
}
.suc-btn--secondary:hover {
  background: rgba(<?= $primaryRgb ?>, 0.08) !important;
  border-color: <?= $primaryHex ?> !important;
}
.suc-receipt__status-badge {
  background: rgba(<?= $secondaryRgb ?>, 0.15) !important;
  color: <?= $secondaryHex ?> !important;
  border: 1px solid rgba(<?= $secondaryRgb ?>, 0.32) !important;
}
.suc-receipt__val--amount {
  color: <?= $secondaryHex ?> !important;
}

/* ── 5. Legal Terms & Privacy Pages ── */
.vn-lg-tab.active {
  background: <?= $primaryHex ?> !important;
  color: <?= $onPrimaryColor ?> !important;
}
.vn-lg-pre {
  color: <?= $secondaryHex ?> !important;
}
.vn-lg-summary {
  border-top: 3px solid <?= $secondaryHex ?> !important;
}
.vn-lg-summary-head {
  color: <?= $secondaryHex ?> !important;
}
.vn-lg-sidebar-nav a.active {
  color: <?= $primaryHex ?> !important;
  border-left-color: <?= $primaryHex ?> !important;
}

/* ── Dark Mode Overrides for Platform Experiences ── */
:root[data-theme='dark'] .vn-stamp-badge {
  border-color: <?= $darkSecondaryHex ?> !important;
  color: <?= $darkSecondaryHex ?> !important;
}
:root[data-theme='dark'] .vn-tag--brass {
  background: <?= $darkSecondaryHex ?> !important;
  color: <?= $onDarkSecondaryColor ?> !important;
}
:root[data-theme='dark'] .vn-price-val {
  color: <?= $darkSecondaryHex ?> !important;
}
:root[data-theme='dark'] .vn-action-badge {
  background: linear-gradient(135deg, <?= $darkPrimaryHex ?> 0%, <?= $darkSecondaryHex ?> 100%) !important;
}
:root[data-theme='dark'] .co-submit-btn,
:root[data-theme='dark'] .checkout-btn {
  background: linear-gradient(135deg, <?= $darkPrimaryHex ?> 0%, <?= $darkSecondaryHex ?> 100%) !important;
}
:root[data-theme='dark'] .co-summary-total strong,
:root[data-theme='dark'] #final-total-amount {
  color: <?= $darkSecondaryHex ?> !important;
}
:root[data-theme='dark'] .suc-check-ring {
  background: rgba(<?= $darkSecondaryRgb ?>, 0.22) !important;
  color: <?= $darkSecondaryHex ?> !important;
  border-color: <?= $darkSecondaryHex ?> !important;
}
:root[data-theme='dark'] .suc-btn--primary {
  background: linear-gradient(135deg, <?= $darkPrimaryHex ?> 0%, <?= $darkSecondaryHex ?> 100%) !important;
}
:root[data-theme='dark'] .suc-receipt__val--amount {
  color: <?= $darkSecondaryHex ?> !important;
}
:root[data-theme='dark'] .vn-lg-summary {
  border-top-color: <?= $darkSecondaryHex ?> !important;
}
:root[data-theme='dark'] .vn-lg-summary-head {
  color: <?= $darkSecondaryHex ?> !important;
}

/* ── 6. Modern Authentication Suite (Dynamic Duo-Tone & Responsive) ── */
.auth-body {
  min-height: 100vh;
  min-height: 100dvh;
  margin: 0;
  padding: 0;
  position: relative;
  overflow-x: hidden;
  background-color: rgb(var(--bg));
  color: rgb(var(--text));
  font-family: var(--font-sans);
  display: flex;
  flex-direction: column;
}

.auth-ambient {
  position: fixed;
  inset: 0;
  pointer-events: none;
  z-index: 0;
  overflow: hidden;
}

.auth-orb {
  position: absolute;
  border-radius: 50%;
  pointer-events: none;
  will-change: transform;
}

.auth-orb-primary {
  top: -12vw;
  left: -8vw;
  width: 48vw;
  height: 48vw;
  max-width: 680px;
  max-height: 680px;
  background: radial-gradient(circle, rgba(<?= $primaryRgb ?>, 0.16) 0%, rgba(<?= $primaryRgb ?>, 0.04) 50%, transparent 70%);
  filter: blur(55px);
  animation: authOrbPulse 14s ease-in-out infinite alternate;
}

.auth-orb-secondary {
  bottom: -15vw;
  right: -10vw;
  width: 52vw;
  height: 52vw;
  max-width: 720px;
  max-height: 720px;
  background: radial-gradient(circle, rgba(<?= $secondaryRgb ?>, 0.14) 0%, rgba(<?= $secondaryRgb ?>, 0.03) 50%, transparent 70%);
  filter: blur(65px);
  animation: authOrbPulse 18s ease-in-out infinite alternate-reverse;
}

@keyframes authOrbPulse {
  0% { transform: scale(1) translate(0, 0); }
  50% { transform: scale(1.06) translate(2vw, 1.5vw); }
  100% { transform: scale(0.96) translate(-1.5vw, -1vw); }
}

.auth-grid-overlay {
  position: absolute;
  inset: 0;
  background-image: radial-gradient(rgba(var(--text), 0.04) 1px, transparent 1px);
  background-size: 28px 28px;
  opacity: 0.75;
}

:root[data-theme='dark'] .auth-grid-overlay {
  background-image: radial-gradient(rgba(255, 255, 255, 0.06) 1px, transparent 1px);
  opacity: 0.5;
}

.auth-container {
  position: relative;
  z-index: 1;
  width: 100%;
  max-width: 1140px;
  margin: 0 auto;
  padding: 2.25rem 1.5rem 3.5rem;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  min-height: 100vh;
  min-height: 100dvh;
  box-sizing: border-box;
}

/* Flash Alerts */
.auth-flash-wrapper {
  width: 100%;
  max-width: 1040px;
  margin-bottom: 1.25rem;
  display: flex;
  flex-direction: column;
  gap: 0.65rem;
}

.auth-alert {
  display: flex;
  align-items: center;
  gap: 0.85rem;
  padding: 0.9rem 1.25rem;
  border-radius: 14px;
  border: 1px solid transparent;
  font-size: 0.92rem;
  line-height: 1.5;
  font-weight: 500;
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.04);
}

.auth-alert-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.auth-alert-content {
  flex: 1;
}

.auth-alert-dismiss {
  background: transparent;
  border: none;
  font-size: 1.35rem;
  line-height: 1;
  cursor: pointer;
  color: inherit;
  opacity: 0.65;
  padding: 0 0.25rem;
  transition: opacity 0.15s ease;
}

.auth-alert-dismiss:hover {
  opacity: 1;
}

.auth-alert-success {
  background: rgba(16, 185, 129, 0.1);
  border-color: rgba(16, 185, 129, 0.32);
  color: #059669;
}
:root[data-theme='dark'] .auth-alert-success {
  background: rgba(16, 185, 129, 0.16);
  border-color: rgba(16, 185, 129, 0.4);
  color: #34d399;
}

.auth-alert-danger {
  background: rgba(239, 68, 68, 0.1);
  border-color: rgba(239, 68, 68, 0.32);
  color: #dc2626;
}
:root[data-theme='dark'] .auth-alert-danger {
  background: rgba(239, 68, 68, 0.16);
  border-color: rgba(239, 68, 68, 0.4);
  color: #f87171;
}

.auth-alert-warning {
  background: rgba(245, 158, 11, 0.1);
  border-color: rgba(245, 158, 11, 0.32);
  color: #d97706;
}
:root[data-theme='dark'] .auth-alert-warning {
  background: rgba(245, 158, 11, 0.16);
  border-color: rgba(245, 158, 11, 0.4);
  color: #fbbf24;
}

.auth-alert-info {
  background: rgba(59, 130, 246, 0.1);
  border-color: rgba(59, 130, 246, 0.32);
  color: #2563eb;
}
:root[data-theme='dark'] .auth-alert-info {
  background: rgba(59, 130, 246, 0.16);
  border-color: rgba(59, 130, 246, 0.4);
  color: #60a5fa;
}

/* Top Bar */
.auth-top-bar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  width: 100%;
  max-width: 1040px;
  margin-bottom: 1.5rem;
  gap: 1rem;
}

.auth-back-link {
  display: inline-flex;
  align-items: center;
  gap: 0.55rem;
  padding: 0.5rem 1rem;
  border-radius: 999px;
  background: rgba(var(--card), 0.75);
  border: 1px solid var(--border-color);
  color: rgb(var(--text));
  font-weight: 600;
  font-size: 0.88rem;
  text-decoration: none;
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
  transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.03);
}

.auth-back-link svg {
  transition: transform 0.2s ease;
  color: rgb(var(--muted));
}

.auth-back-link:hover {
  border-color: <?= $primaryHex ?> !important;
  color: <?= $primaryHex ?> !important;
  transform: translateX(-3px);
  box-shadow: 0 4px 14px rgba(<?= $primaryRgb ?>, 0.15);
}

.auth-back-link:hover svg {
  color: <?= $primaryHex ?> !important;
  transform: translateX(-2px);
}

.auth-top-brand {
  display: none;
  align-items: center;
  gap: 0.55rem;
  font-weight: 800;
  font-size: 1.1rem;
  color: rgb(var(--text));
}

.auth-top-logo {
  max-height: 36px;
  width: auto;
  object-fit: contain;
}

.auth-top-monogram {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 32px;
  height: 32px;
  border-radius: 9px;
  background: linear-gradient(135deg, <?= $primaryHex ?>, <?= $secondaryHex ?>);
  color: <?= $onPrimaryColor ?>;
  font-weight: 900;
  font-size: 0.95rem;
}

.auth-top-actions {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.auth-theme-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 40px;
  height: 40px;
  border-radius: 50%;
  border: 1px solid var(--border-color);
  background: rgba(var(--card), 0.75);
  color: rgb(var(--text));
  cursor: pointer;
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
  font-size: 1.15rem;
  transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.03);
}

.auth-theme-btn:hover {
  transform: scale(1.08) rotate(15deg);
  border-color: <?= $secondaryHex ?> !important;
  box-shadow: 0 4px 14px rgba(<?= $secondaryRgb ?>, 0.25);
}

/* 2-Column Split Grid */
.auth-split {
  display: grid !important;
  grid-template-columns: 1fr 1fr !important;
  gap: 2.25rem !important;
  width: 100% !important;
  max-width: 1040px !important;
  align-items: stretch !important;
  margin: 0 auto !important;
  background: transparent !important;
  border: none !important;
  box-shadow: none !important;
}

/* Left Showcase Panel */
.auth-showcase {
  position: relative;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  padding: 2.75rem 2.5rem;
  border-radius: 28px;
  background: linear-gradient(160deg, rgba(<?= $primaryRgb ?>, 0.06) 0%, rgba(<?= $secondaryRgb ?>, 0.03) 40%, rgba(var(--card), 0.9) 100%);
  border: 1px solid var(--border-color);
  box-shadow: 0 20px 45px -15px rgba(0, 0, 0, 0.06), 0 0 0 1px rgba(<?= $primaryRgb ?>, 0.05);
  backdrop-filter: blur(16px);
  -webkit-backdrop-filter: blur(16px);
  overflow: hidden;
  box-sizing: border-box;
}

:root[data-theme='dark'] .auth-showcase {
  background: linear-gradient(160deg, rgba(<?= $darkPrimaryRgb ?>, 0.12) 0%, rgba(<?= $darkSecondaryRgb ?>, 0.07) 40%, rgba(20, 24, 33, 0.94) 100%);
  border-color: rgba(255, 255, 255, 0.08);
  box-shadow: 0 25px 60px -20px rgba(0, 0, 0, 0.6), 0 0 0 1px rgba(255, 255, 255, 0.05);
}

.auth-showcase-content {
  display: flex;
  flex-direction: column;
  height: 100%;
}

.auth-brand-badge {
  display: inline-flex;
  align-items: center;
  gap: 0.65rem;
  margin-bottom: 1.75rem;
}

.auth-brand-logo {
  max-height: 44px;
  max-width: 170px;
  width: auto;
  height: auto;
  object-fit: contain;
  border-radius: 8px;
}

.auth-brand-badge .brand-letter {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 40px;
  height: 40px;
  border-radius: 12px;
  background: linear-gradient(135deg, <?= $primaryHex ?> 0%, <?= $secondaryHex ?> 100%);
  color: <?= $onPrimaryColor ?>;
  font-weight: 900;
  font-size: 1.2rem;
  box-shadow: 0 6px 16px rgba(<?= $primaryRgb ?>, 0.35);
}

.auth-brand-badge .brand-title {
  font-size: 1.3rem;
  font-weight: 800;
  letter-spacing: -0.02em;
  color: rgb(var(--text));
}

.auth-showcase-hero {
  margin-bottom: 2rem;
}

.showcase-pill {
  display: inline-flex;
  align-items: center;
  gap: 0.45rem;
  font-size: 0.78rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  padding: 0.38rem 0.85rem;
  border-radius: 999px;
  background: rgba(<?= $primaryRgb ?>, 0.09);
  color: <?= $primaryHex ?>;
  border: 1px solid rgba(<?= $primaryRgb ?>, 0.22);
  margin-bottom: 0.85rem;
}

.auth-showcase-hero h2 {
  margin: 0 0 0.85rem;
  font-size: clamp(1.75rem, 2.3vw, 2.3rem);
  line-height: 1.18;
  font-weight: 800;
  letter-spacing: -0.03em;
  color: rgb(var(--text));
  font-family: inherit !important;
}

.showcase-lede {
  margin: 0;
  font-size: 0.96rem;
  line-height: 1.6;
  color: rgb(var(--muted));
}

/* Digital Ticket Pass Mockup */
.auth-ticket-preview {
  position: relative;
  background: rgba(var(--card), 0.98);
  border: 1px solid var(--border-color);
  border-radius: 18px;
  overflow: hidden;
  box-shadow: 0 16px 36px -12px rgba(0, 0, 0, 0.08), 0 0 0 1px rgba(<?= $primaryRgb ?>, 0.08);
  margin-bottom: 2rem;
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}

:root[data-theme='dark'] .auth-ticket-preview {
  background: rgba(24, 29, 41, 0.96);
  border-color: rgba(255, 255, 255, 0.1);
  box-shadow: 0 20px 45px -15px rgba(0, 0, 0, 0.55);
}

.auth-ticket-preview:hover {
  transform: translateY(-3px);
  box-shadow: 0 22px 45px -12px rgba(<?= $primaryRgb ?>, 0.16);
}

.ticket-header-strip {
  background: linear-gradient(135deg, <?= $primaryHex ?> 0%, <?= $secondaryHex ?> 100%);
  color: <?= $onPrimaryColor ?>;
  padding: 0.65rem 1.15rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 0.74rem;
  font-weight: 800;
  letter-spacing: 0.06em;
}

.ticket-live-dot {
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
  background: rgba(0, 0, 0, 0.2);
  padding: 0.2rem 0.55rem;
  border-radius: 999px;
  font-size: 0.68rem;
}

.pulsing-dot {
  width: 7px;
  height: 7px;
  border-radius: 50%;
  background: #10b981;
  box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
  animation: pulseDot 2s infinite;
}

.ticket-body {
  padding: 1.15rem 1.25rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 1rem;
}

.ticket-eyebrow {
  display: block;
  font-size: 0.72rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  color: <?= $secondaryHex ?>;
  margin-bottom: 0.25rem;
}

.ticket-event-title {
  margin: 0 0 0.35rem;
  font-size: 1.05rem;
  font-weight: 800;
  color: rgb(var(--text));
  letter-spacing: -0.01em;
}

.ticket-venue {
  margin: 0;
  font-size: 0.82rem;
  color: rgb(var(--muted));
  display: flex;
  align-items: center;
  gap: 0.35rem;
}

.ticket-qr-box {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.35rem;
  flex-shrink: 0;
}

.ticket-qr-matrix {
  position: relative;
  width: 48px;
  height: 48px;
  background: rgba(var(--bg), 0.85);
  border: 1px solid var(--border-color);
  border-radius: 8px;
  padding: 4px;
  box-sizing: border-box;
}

.qr-corner {
  position: absolute;
  width: 10px;
  height: 10px;
  border-style: solid;
  border-color: <?= $primaryHex ?>;
}
.qr-corner.top-left { top: 4px; left: 4px; border-width: 2.5px 0 0 2.5px; }
.qr-corner.top-right { top: 4px; right: 4px; border-width: 2.5px 2.5px 0 0; }
.qr-corner.bottom-left { bottom: 4px; left: 4px; border-width: 0 0 2.5px 2.5px; }

.qr-pattern-grid {
  position: absolute;
  inset: 12px;
  background-image: radial-gradient(<?= $secondaryHex ?> 1.5px, transparent 1.5px);
  background-size: 5px 5px;
}

.ticket-qr-label {
  font-size: 0.65rem;
  font-weight: 700;
  text-transform: uppercase;
  color: rgb(var(--muted));
  letter-spacing: 0.05em;
}

.ticket-stub-perforation {
  position: relative;
  display: flex;
  align-items: center;
  height: 16px;
  overflow: hidden;
}

.perforation-notch {
  position: absolute;
  width: 18px;
  height: 18px;
  border-radius: 50%;
  background-color: rgb(var(--bg));
  border: 1px solid var(--border-color);
  top: 50%;
  transform: translateY(-50%);
  z-index: 2;
}
.perforation-notch.left { left: -9px; }
.perforation-notch.right { right: -9px; }

.perforation-line {
  flex: 1;
  border-top: 1.5px dashed rgba(var(--muted), 0.35);
  margin: 0 12px;
}

.ticket-footer-strip {
  padding: 0.6rem 1.25rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 0.74rem;
  font-weight: 600;
  color: rgb(var(--muted));
  background: rgba(var(--bg), 0.4);
}

/* Organizer Metric Mockup */
.organizer-metric-row {
  display: flex;
  align-items: center;
  gap: 1.25rem;
  margin-bottom: 0.85rem;
}
.org-metric {
  display: flex;
  flex-direction: column;
}
.metric-label {
  font-size: 0.72rem;
  font-weight: 700;
  text-transform: uppercase;
  color: rgb(var(--muted));
  letter-spacing: 0.05em;
}
.metric-val {
  font-size: 1.15rem;
  font-weight: 800;
  color: rgb(var(--text));
}
.metric-val small {
  font-size: 0.76rem;
  font-weight: 600;
  color: rgb(var(--muted));
}
.metric-val.status-green {
  color: <?= $secondaryHex ?>;
}
.org-metric-divider {
  width: 1px;
  height: 32px;
  background: var(--border-color);
}
.organizer-feature-badges {
  display: flex;
  flex-wrap: wrap;
  gap: 0.45rem;
}
.org-pill {
  display: inline-flex;
  align-items: center;
  gap: 0.3rem;
  font-size: 0.72rem;
  font-weight: 600;
  padding: 0.25rem 0.6rem;
  border-radius: 999px;
  background: rgba(<?= $primaryRgb ?>, 0.08);
  color: rgb(var(--text));
  border: 1px solid rgba(<?= $primaryRgb ?>, 0.16);
}

/* Trust Metrics Strip */
.auth-trust-strip {
  margin-top: auto;
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1rem 1.25rem;
  background: rgba(var(--card), 0.75);
  border: 1px solid var(--border-color);
  border-radius: 16px;
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
}

.trust-stat {
  display: flex;
  flex-direction: column;
}

.trust-stat strong {
  font-size: 1.05rem;
  font-weight: 800;
  color: <?= $primaryHex ?>;
  letter-spacing: -0.01em;
}

.trust-stat span {
  font-size: 0.74rem;
  font-weight: 600;
  color: rgb(var(--muted));
  text-transform: uppercase;
  letter-spacing: 0.04em;
  margin-top: 0.15rem;
}

.trust-divider {
  width: 1px;
  height: 28px;
  background: var(--border-color);
}

/* Right Panel: Form Card */
.auth-card {
  width: 100% !important;
  background: rgb(var(--card)) !important;
  padding: 2.75rem 2.5rem !important;
  border-radius: 28px !important;
  border: 1px solid var(--border-color) !important;
  box-shadow: 0 24px 50px -15px rgba(0, 0, 0, 0.07), 0 0 0 1px rgba(0, 0, 0, 0.02) !important;
  backdrop-filter: blur(16px);
  -webkit-backdrop-filter: blur(16px);
  box-sizing: border-box;
  display: flex !important;
  flex-direction: column !important;
  justify-content: center !important;
  position: relative;
}

:root[data-theme='dark'] .auth-card {
  background: rgba(22, 27, 38, 0.95) !important;
  border-color: rgba(255, 255, 255, 0.08) !important;
  box-shadow: 0 30px 70px -20px rgba(0, 0, 0, 0.6), 0 0 0 1px rgba(255, 255, 255, 0.05) !important;
}

.auth-card-header {
  margin-bottom: 1.65rem;
}

.auth-card-header h1 {
  margin: 0 0 0.35rem !important;
  font-size: clamp(1.75rem, 2.2vw, 2.2rem) !important;
  font-weight: 800 !important;
  letter-spacing: -0.025em !important;
  color: rgb(var(--text)) !important;
  line-height: 1.2 !important;
  font-family: inherit !important;
}

.auth-card-header p {
  margin: 0;
  font-size: 0.95rem;
  color: rgb(var(--muted));
  line-height: 1.5;
}

/* Form Controls & Field Groups */
.auth-field-group {
  display: flex;
  flex-direction: column;
  gap: 0.45rem;
  margin-bottom: 1.25rem;
  position: relative;
}

.auth-label {
  font-size: 0.88rem;
  font-weight: 600;
  color: rgb(var(--text));
  letter-spacing: -0.01em;
}

.auth-input-wrapper {
  position: relative;
  display: flex;
  align-items: center;
  width: 100%;
}

.auth-input-icon {
  position: absolute;
  left: 1.15rem;
  color: rgb(var(--muted));
  pointer-events: none;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: color 0.2s ease;
  z-index: 2;
}

.auth-input {
  width: 100% !important;
  height: 50px !important;
  padding: 0 1.1rem 0 3.2rem !important;
  border-radius: 14px !important;
  border: 1.5px solid var(--border-color) !important;
  background: rgba(var(--bg), 0.7) !important;
  color: rgb(var(--text)) !important;
  font-family: inherit !important;
  font-size: 1rem !important;
  box-sizing: border-box !important;
  transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1) !important;
}

.auth-input::placeholder {
  color: rgba(var(--muted), 0.65);
}

.auth-input:focus {
  outline: none !important;
  background: rgb(var(--card)) !important;
  border-color: <?= $primaryHex ?> !important;
  box-shadow: 0 0 0 4px rgba(<?= $primaryRgb ?>, 0.16), 0 4px 16px rgba(<?= $primaryRgb ?>, 0.08) !important;
}

:root[data-theme='dark'] .auth-input {
  background: rgba(14, 18, 25, 0.75) !important;
  border-color: rgba(255, 255, 255, 0.12) !important;
}

:root[data-theme='dark'] .auth-input:focus {
  background: rgba(18, 23, 33, 0.95) !important;
  border-color: <?= $darkPrimaryHex ?> !important;
  box-shadow: 0 0 0 4px rgba(<?= $darkPrimaryRgb ?>, 0.28) !important;
}

.auth-input-wrapper:focus-within .auth-input-icon {
  color: <?= $primaryHex ?> !important;
}

.auth-input.has-toggle {
  padding-right: 3.3rem !important;
}

/* Password Toggle Button */
.password-toggle-btn {
  position: absolute;
  right: 0.55rem;
  top: 50%;
  transform: translateY(-50%);
  background: transparent;
  border: none;
  padding: 0.5rem;
  color: rgb(var(--muted));
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 10px;
  transition: color 0.2s ease, background-color 0.2s ease;
  width: 38px;
  height: 38px;
  z-index: 2;
}

.password-toggle-btn:hover {
  color: rgb(var(--text));
  background: rgba(var(--muted), 0.12);
}

.password-toggle-btn:focus-visible {
  outline: 2px solid <?= $primaryHex ?>;
  outline-offset: 1px;
}

/* Interactive Role Picker */
.auth-role-picker {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 0.85rem;
  margin-top: 0.25rem;
}

.role-card-option {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  text-align: left;
  padding: 1rem 1.1rem;
  border-radius: 16px;
  border: 2px solid var(--border-color);
  background: rgba(var(--bg), 0.55);
  cursor: pointer;
  position: relative;
  transition: all 0.22s cubic-bezier(0.16, 1, 0.3, 1);
  font-family: inherit;
  width: 100%;
  box-sizing: border-box;
}

.role-card-option:hover {
  border-color: rgba(<?= $primaryRgb ?>, 0.45);
  background: rgba(var(--card), 0.9);
  transform: translateY(-2px);
  box-shadow: 0 6px 18px rgba(0, 0, 0, 0.05);
}

.role-card-option.active {
  border-color: <?= $primaryHex ?> !important;
  background: rgba(<?= $primaryRgb ?>, 0.06) !important;
  box-shadow: 0 0 0 1px <?= $primaryHex ?>, 0 8px 22px -6px rgba(<?= $primaryRgb ?>, 0.3) !important;
}

:root[data-theme='dark'] .role-card-option {
  background: rgba(14, 18, 25, 0.7);
  border-color: rgba(255, 255, 255, 0.1);
}

:root[data-theme='dark'] .role-card-option.active {
  background: rgba(<?= $darkPrimaryRgb ?>, 0.12) !important;
  border-color: <?= $darkPrimaryHex ?> !important;
}

.role-icon-box {
  font-size: 1.45rem;
  line-height: 1;
  margin-bottom: 0.5rem;
}

.role-text-box strong {
  display: block;
  font-size: 0.94rem;
  font-weight: 700;
  color: rgb(var(--text));
  margin-bottom: 0.2rem;
}

.role-text-box span {
  display: block;
  font-size: 0.76rem;
  color: rgb(var(--muted));
  line-height: 1.4;
}

.role-check-indicator {
  position: absolute;
  top: 0.75rem;
  right: 0.75rem;
  width: 22px;
  height: 22px;
  border-radius: 50%;
  background: var(--border-color);
  color: transparent;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
}

.role-card-option.active .role-check-indicator {
  background: linear-gradient(135deg, <?= $primaryHex ?> 0%, <?= $secondaryHex ?> 100%) !important;
  color: <?= $onPrimaryColor ?> !important;
  box-shadow: 0 2px 8px rgba(<?= $primaryRgb ?>, 0.35);
  transform: scale(1.05);
}

/* Password Strength Meter */
.pw-strength-container {
  margin-top: 0.45rem;
  display: none;
  flex-direction: column;
  gap: 0.35rem;
}

.pw-strength-container.active {
  display: flex;
}

.pw-strength-bars {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 0.35rem;
  height: 5px;
}

.pw-bar {
  border-radius: 999px;
  background: rgba(var(--muted), 0.2);
  transition: background-color 0.3s ease;
}

.pw-bar.fill-1 { background-color: #ef4444; }
.pw-bar.fill-2 { background-color: #f59e0b; }
.pw-bar.fill-3 { background-color: <?= $secondaryHex ?> !important; }
.pw-bar.fill-4 { background-color: <?= $primaryHex ?> !important; }

.pw-strength-caption {
  font-size: 0.76rem;
  color: rgb(var(--muted));
  font-weight: 600;
}

/* Password Match Badge */
.pw-match-badge {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  font-size: 0.78rem;
  font-weight: 600;
  margin-top: 0.35rem;
  padding: 0.22rem 0.65rem;
  border-radius: 6px;
  width: fit-content;
}

.pw-match-badge.match {
  background: rgba(16, 185, 129, 0.12);
  color: #10b981;
}

.pw-match-badge.mismatch {
  background: rgba(239, 68, 68, 0.12);
  color: #ef4444;
}

/* Checkbox & Remember Options */
.auth-options {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin: 0.25rem 0 1.5rem;
  flex-wrap: wrap;
  gap: 0.75rem;
}

.auth-checkbox-label {
  display: inline-flex;
  align-items: center;
  gap: 0.6rem;
  cursor: pointer;
  user-select: none;
  font-size: 0.88rem;
  color: rgb(var(--text));
  font-weight: 500;
}

.auth-checkbox-label input[type="checkbox"] {
  appearance: none;
  -webkit-appearance: none;
  width: 18px;
  height: 18px;
  border-radius: 6px;
  border: 1.5px solid var(--border-color);
  background: rgba(var(--bg), 0.6);
  cursor: pointer;
  display: grid;
  place-content: center;
  transition: all 0.2s ease;
  margin: 0;
}

.auth-checkbox-label input[type="checkbox"]::before {
  content: "";
  width: 10px;
  height: 10px;
  transform: scale(0);
  transition: 0.15s transform ease-in-out;
  box-shadow: inset 1em 1em <?= $onPrimaryColor ?>;
  transform-origin: center;
  clip-path: polygon(14% 44%, 0 65%, 50% 100%, 100% 16%, 80% 0%, 43% 62%);
}

.auth-checkbox-label input[type="checkbox"]:checked {
  background: linear-gradient(135deg, <?= $primaryHex ?> 0%, <?= $secondaryHex ?> 100%) !important;
  border-color: <?= $primaryHex ?> !important;
}

.auth-checkbox-label input[type="checkbox"]:checked::before {
  transform: scale(1);
}

.auth-link-forgot {
  font-size: 0.88rem;
  font-weight: 600;
  color: <?= $primaryHex ?>;
  text-decoration: none;
  transition: color 0.2s ease;
}

.auth-link-forgot:hover {
  color: <?= $secondaryHex ?>;
  text-decoration: underline;
}

.auth-legal-note {
  font-size: 0.78rem;
  color: rgb(var(--muted));
  line-height: 1.5;
  margin: 0 0 1.25rem;
}

.auth-inline-link {
  color: <?= $primaryHex ?>;
  text-decoration: none;
  font-weight: 600;
}

.auth-inline-link:hover {
  text-decoration: underline;
}

/* Submit & Secondary Buttons */
.auth-submit-btn {
  width: 100% !important;
  height: 50px !important;
  border-radius: 14px !important;
  border: none !important;
  background: linear-gradient(135deg, <?= $primaryHex ?> 0%, <?= $secondaryHex ?> 100%) !important;
  color: <?= $onPrimaryColor ?> !important;
  font-size: 0.98rem !important;
  font-weight: 700 !important;
  letter-spacing: -0.01em !important;
  cursor: pointer !important;
  display: flex !important;
  align-items: center !important;
  justify-content: center !important;
  gap: 0.65rem !important;
  box-shadow: 0 10px 24px -5px rgba(<?= $primaryRgb ?>, 0.38), 0 4px 10px -2px rgba(<?= $secondaryRgb ?>, 0.22) !important;
  transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1) !important;
  position: relative !important;
  overflow: hidden !important;
}

.auth-submit-btn svg {
  transition: transform 0.2s ease;
}

.auth-submit-btn:hover {
  transform: translateY(-2px) !important;
  box-shadow: 0 14px 30px -5px rgba(<?= $primaryRgb ?>, 0.48), 0 6px 14px -2px rgba(<?= $secondaryRgb ?>, 0.35) !important;
}

.auth-submit-btn:hover svg {
  transform: translateX(3px);
}

.auth-submit-btn:active {
  transform: translateY(0) !important;
}

.auth-submit-btn.is-submitting {
  opacity: 0.85 !important;
  pointer-events: none !important;
}

.auth-card-divider {
  display: flex;
  align-items: center;
  text-align: center;
  margin: 1.5rem 0 1rem;
  color: rgb(var(--muted));
  font-size: 0.78rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.06em;
}

.auth-card-divider::before,
.auth-card-divider::after {
  content: '';
  flex: 1;
  border-bottom: 1px solid var(--border-color);
}

.auth-card-divider span {
  padding: 0 0.85rem;
}

.auth-secondary-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 100%;
  height: 48px;
  border-radius: 14px;
  border: 1.5px solid var(--border-color);
  background: rgba(var(--bg), 0.5);
  color: rgb(var(--text));
  font-weight: 600;
  font-size: 0.94rem;
  text-decoration: none;
  transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
  box-sizing: border-box;
}

.auth-secondary-btn:hover {
  border-color: <?= $primaryHex ?> !important;
  color: <?= $primaryHex ?> !important;
  background: rgba(<?= $primaryRgb ?>, 0.06) !important;
  transform: translateY(-1px);
}

.auth-foot {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  width: 100%;
}

/* ── Responsive Behavior Across Devices ── */
@media (max-width: 991px) {
  .auth-split {
    grid-template-columns: 1fr !important;
    max-width: 580px !important;
    gap: 1.75rem !important;
  }

  .auth-showcase {
    order: 2;
    padding: 2.25rem 2rem;
    border-radius: 22px;
  }

  .auth-card {
    order: 1 !important;
    padding: 2.25rem 2rem !important;
    border-radius: 22px !important;
  }

  .auth-top-bar {
    max-width: 580px;
  }

  .auth-top-brand {
    display: flex;
  }

  .auth-showcase .auth-brand-badge {
    display: none;
  }
}

@media (max-width: 767px) {
  .auth-container {
    padding: 1.5rem 1rem 2.5rem;
  }

  .auth-split {
    gap: 1.25rem !important;
  }

  .auth-card {
    padding: 1.85rem 1.35rem !important;
    border-radius: 20px !important;
  }

  .auth-card-header h1 {
    font-size: 1.6rem !important;
  }

  .auth-card-header p {
    font-size: 0.88rem;
  }

  .auth-showcase {
    padding: 1.75rem 1.35rem;
    border-radius: 20px;
  }

  .auth-showcase-hero h2 {
    font-size: 1.45rem !important;
  }

  .showcase-lede {
    font-size: 0.88rem;
  }

  .ticket-body {
    flex-direction: column;
    align-items: flex-start;
    gap: 0.85rem;
  }

  .ticket-qr-box {
    align-self: flex-start;
    flex-direction: row;
    gap: 0.65rem;
  }

  .auth-trust-strip {
    padding: 0.85rem 1rem;
  }

  .trust-stat strong {
    font-size: 0.95rem;
  }

  .trust-stat span {
    font-size: 0.68rem;
  }

  .auth-input {
    font-size: 16px !important; /* Prevents auto-zoom in iOS Safari */
    height: 48px !important;
  }

  .auth-submit-btn {
    height: 48px !important;
    font-size: 0.95rem !important;
  }
}

@media (max-width: 520px) {
  .auth-role-picker {
    grid-template-columns: 1fr;
  }

  .role-card-option {
    padding: 0.9rem 1rem;
  }

  .auth-top-bar {
    margin-bottom: 1rem;
  }

  .auth-back-link span {
    font-size: 0.82rem;
  }
}

/* ── 7. Universal Brand Logo & Favicon Display Engine (Transparent & Theme-Adaptive) ── */
.brand-custom-logo,
.brand-logo-wrap {
  display: inline-flex !important;
  align-items: center !important;
  justify-content: flex-start !important;
  height: clamp(34px, 4.2vw, 42px);
  max-height: 44px;
  max-width: clamp(140px, 22vw, 240px);
  position: relative;
  overflow: visible !important;
  vertical-align: middle;
  text-decoration: none;
  transition: transform 0.2s cubic-bezier(0.16, 1, 0.3, 1), filter 0.2s ease;
}

.brand-custom-logo:hover,
.brand-logo-wrap:hover {
  transform: translateY(-1px);
}

.brand-logo-img {
  display: block;
  width: auto;
  height: auto;
  max-height: 40px;
  max-width: 100%;
  object-fit: contain;
  object-position: left center;
  border-radius: 8px;
  transition: filter 0.25s ease, opacity 0.2s ease, transform 0.2s ease;
}

/* Dual Logo Switching: Light vs Dark */
.brand-logo-wrap.logo-has-variants .brand-logo-light {
  display: block !important;
}
.brand-logo-wrap.logo-has-variants .brand-logo-dark {
  display: none !important;
}

:root[data-theme='dark'] .brand-logo-wrap.logo-has-variants .brand-logo-light {
  display: none !important;
}
:root[data-theme='dark'] .brand-logo-wrap.logo-has-variants .brand-logo-dark {
  display: block !important;
}

/* Dark Surface Contexts (always show dark logo if available) */
.auth-showcase .brand-logo-wrap.logo-has-variants .brand-logo-light {
  display: none !important;
}
.auth-showcase .brand-logo-wrap.logo-has-variants .brand-logo-dark {
  display: block !important;
}

/* Intelligent Dynamic Contrast for Transparent Logos (logo-backdrop-auto) */
.logo-backdrop-auto.logo-single .brand-logo-img,
.logo-backdrop-auto .brand-logo-light {
  filter: drop-shadow(0 1px 2px rgba(0, 0, 0, 0.08));
}

:root[data-theme='dark'] .logo-backdrop-auto.logo-single .brand-logo-img {
  /* Soft luminous contour: guarantees pure black/dark transparent logos remain 100% visible on dark mode */
  filter: drop-shadow(0 0 1px rgba(255, 255, 255, 0.8)) drop-shadow(0 2px 8px rgba(0, 0, 0, 0.45)) !important;
}

.auth-showcase .logo-backdrop-auto.logo-single .brand-logo-img {
  filter: drop-shadow(0 0 1px rgba(255, 255, 255, 0.85)) drop-shadow(0 3px 10px rgba(0, 0, 0, 0.5)) !important;
}

/* Frosted Glass Capsule Container */
.logo-backdrop-capsule {
  padding: 4px 12px !important;
  border-radius: 14px !important;
  background: rgba(var(--card), 0.78) !important;
  border: 1px solid var(--border-color) !important;
  backdrop-filter: blur(12px) !important;
  -webkit-backdrop-filter: blur(12px) !important;
  box-shadow: 0 4px 14px rgba(0, 0, 0, 0.05) !important;
}
.logo-backdrop-capsule:hover {
  background: rgba(var(--card), 0.95) !important;
  border-color: rgba(<?= $primaryRgb ?>, 0.35) !important;
  box-shadow: 0 6px 18px rgba(<?= $primaryRgb ?>, 0.12) !important;
}
:root[data-theme='dark'] .logo-backdrop-capsule {
  background: rgba(22, 27, 38, 0.85) !important;
  border-color: rgba(255, 255, 255, 0.1) !important;
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.4) !important;
}

/* Crisp White Pill Container */
.logo-backdrop-solid-white {
  padding: 4px 12px !important;
  border-radius: 14px !important;
  background: #ffffff !important;
  border: 1px solid rgba(0, 0, 0, 0.08) !important;
  box-shadow: 0 4px 14px rgba(0, 0, 0, 0.08) !important;
}
.logo-backdrop-solid-white .brand-logo-img {
  filter: none !important;
}

/* Dark Slate Pill Container */
.logo-backdrop-solid-dark {
  padding: 4px 12px !important;
  border-radius: 14px !important;
  background: #11141c !important;
  border: 1px solid rgba(255, 255, 255, 0.12) !important;
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.4) !important;
}
.logo-backdrop-solid-dark .brand-logo-img {
  filter: none !important;
}

/* Raw Transparent Container */
.logo-backdrop-none {
  padding: 0 !important;
  background: transparent !important;
  border: none !important;
  box-shadow: none !important;
}
.logo-backdrop-none .brand-logo-img {
  filter: none !important;
}

/* Context Specific Sizes */
.brand-logo-wrap.context-footer {
  height: clamp(28px, 3.6vw, 34px);
  max-height: 36px;
}
.brand-logo-wrap.context-footer .brand-logo-img {
  max-height: 32px;
}

.brand-logo-wrap.context-auth-badge {
  height: clamp(38px, 4.5vw, 48px);
  max-height: 50px;
}
.brand-logo-wrap.context-auth-badge .brand-logo-img {
  max-height: 46px;
}

.brand-logo-wrap.context-auth-top {
  height: 34px;
  max-height: 34px;
}
.brand-logo-wrap.context-auth-top .brand-logo-img {
  max-height: 32px;
}

.brand-logo-wrap.context-flyer {
  height: clamp(32px, 4vw, 40px);
  max-height: 40px;
}
.brand-logo-wrap.context-flyer .brand-logo-img {
  max-height: 38px;
}

/* ── Admin Settings Branding Suite UI ── */
.as-dual-preview-wrap {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 0.75rem;
  margin-bottom: 0.75rem;
}

.as-preview-box {
  position: relative;
  padding: 1.25rem 1rem;
  border-radius: 12px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  min-height: 80px;
  border: 1px solid var(--border-color);
  overflow: hidden;
}

.as-preview-light {
  background: #ffffff;
  background-image: linear-gradient(45deg, #f0f0f0 25%, transparent 25%), linear-gradient(-45deg, #f0f0f0 25%, transparent 25%), linear-gradient(45deg, transparent 75%, #f0f0f0 75%), linear-gradient(-45deg, transparent 75%, #f0f0f0 75%);
  background-size: 16px 16px;
  background-position: 0 0, 0 8px, 8px -8px, -8px 0px;
}

.as-preview-dark {
  background: #12161f;
  background-image: linear-gradient(45deg, #1a202c 25%, transparent 25%), linear-gradient(-45deg, #1a202c 25%, transparent 25%), linear-gradient(45deg, transparent 75%, #1a202c 75%), linear-gradient(-45deg, transparent 75%, #1a202c 75%);
  background-size: 16px 16px;
  background-position: 0 0, 0 8px, 8px -8px, -8px 0px;
}

.as-preview-tag {
  position: absolute;
  top: 4px;
  left: 6px;
  font-size: 0.65rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  padding: 0.15rem 0.45rem;
  border-radius: 4px;
  background: rgba(0, 0, 0, 0.65);
  color: #ffffff;
}

.as-preview-light .as-preview-tag {
  background: rgba(255, 255, 255, 0.85);
  color: #1a202c;
  border: 1px solid rgba(0, 0, 0, 0.1);
}

.as-logo-preview {
  max-height: 48px;
  max-width: 160px;
  width: auto;
  height: auto;
  object-fit: contain;
}

.as-tab-simulator {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.45rem 1rem;
  border-radius: 10px 10px 0 0;
  font-size: 0.78rem;
  font-weight: 600;
  border: 1px solid var(--border-color);
  border-bottom: none;
}

.as-tab-sim-light {
  background: #ffffff;
  color: #1a202c;
  box-shadow: 0 -2px 6px rgba(0, 0, 0, 0.04);
}

.as-tab-sim-dark {
  background: #1e2430;
  color: #e2e8f0;
  border-color: rgba(255, 255, 255, 0.12);
  box-shadow: 0 -2px 6px rgba(0, 0, 0, 0.3);
}

.as-tab-simulator img {
  width: 18px;
  height: 18px;
  object-fit: contain;
}

.as-radio-pill-card {
  display: flex;
  align-items: flex-start;
  gap: 0.75rem;
  padding: 0.85rem 1rem;
  border-radius: 12px;
  border: 2px solid var(--border-color);
  background: rgba(var(--card), 0.7);
  cursor: pointer;
  transition: all 0.2s ease;
}

.as-radio-pill-card:hover {
  border-color: rgba(<?= $primaryRgb ?>, 0.4);
  background: rgb(var(--card));
  transform: translateY(-1px);
}

.as-radio-pill-card.active {
  border-color: <?= $primaryHex ?> !important;
  background: rgba(<?= $primaryRgb ?>, 0.06) !important;
  box-shadow: 0 0 0 1px <?= $primaryHex ?>, 0 4px 12px rgba(<?= $primaryRgb ?>, 0.15) !important;
}

.as-radio-pill-icon {
  font-size: 1.35rem;
  line-height: 1;
}

.as-radio-pill-info strong {
  display: block;
  font-size: 0.88rem;
  font-weight: 700;
  color: rgb(var(--text));
  margin-bottom: 0.15rem;
}

.as-radio-pill-info small {
  display: block;
  font-size: 0.74rem;
  color: rgb(var(--muted));
  line-height: 1.4;
}

</style>
