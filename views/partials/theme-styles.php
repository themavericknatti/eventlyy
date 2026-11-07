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
</style>
