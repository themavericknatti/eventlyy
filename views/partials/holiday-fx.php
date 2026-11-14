<?php

/**
 * Seasonal Atmospheric Floating Effects Partial
 *
 * Renders an ultra-smooth, hardware-accelerated seasonal particle effect canvas
 * across all client, host, and admin pages without any lag or interaction blockage.
 *
 * Supported Themes:
 * - Christmas (Snowflakes, subtle Santa heads/hats, peaceful doves, gold stars)
 * - Winter (Crystal snowflakes & soft ice particles)
 * - New Year (Celebratory tumbling 3D confetti, streamers & golden sparkles)
 * - Valentine (Floating pastel hearts & drifting rose petals)
 * - Easter / Spring (Peaceful white doves, cherry blossom petals & fresh spring flora)
 * - Halloween (Flying silhouette bats, glowing embers & jack-o'-lanterns)
 * - Summer (Shimmering iridescent bubbles & warm golden sun bokeh)
 * - National / Independence (Emerald green & white celebratory ribbons & confetti)
 * - Ramadan & Eid (Golden crescent moons, ornate glowing lanterns & Islamic stars)
 * - Autumn (Gently swaying maple leaves & golden embers)
 *
 * @package Eventlyy
 * @subpackage Views/Partials
 */

$fxConfig = function_exists('holiday_fx_config') ? holiday_fx_config() : ['active' => false];

if (empty($fxConfig['active']) || empty($fxConfig['mode']) || $fxConfig['mode'] === 'disabled') {
    return;
}

$jsVer = function_exists('asset_version') ? asset_version('assets/js/holiday-fx.js') : '1.0.0';
?>
<!-- ═══════════════════════════════════════════════════════════════════
     EVENTLYY SEASONAL ATMOSPHERIC HOLIDAY FX
     ═══════════════════════════════════════════════════════════════════ -->
<style>
    .holiday-fx-container {
        position: fixed;
        inset: 0;
        width: 100vw;
        height: 100vh;
        pointer-events: none !important;
        z-index: 99990;
        overflow: hidden;
        contain: strict;
        will-change: transform;
    }
    .holiday-fx-canvas {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        pointer-events: none !important;
        display: block;
    }
    .holiday-fx-toggle {
        position: fixed;
        bottom: 1.25rem;
        left: 1.25rem;
        z-index: 99995;
        pointer-events: auto !important;
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.35rem 0.65rem;
        font-family: inherit;
        font-size: 0.72rem;
        font-weight: 600;
        color: #ffffff;
        background: rgba(15, 23, 42, 0.72);
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
        border: 1px solid rgba(255, 255, 255, 0.18);
        border-radius: 999px;
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.22);
        cursor: pointer;
        opacity: 0.42;
        transition: opacity 0.22s ease, transform 0.22s ease, background 0.22s ease;
        user-select: none;
        -webkit-user-select: none;
    }
    .holiday-fx-toggle:hover {
        opacity: 0.95;
        transform: translateY(-2px);
        background: rgba(15, 23, 42, 0.92);
        border-color: rgba(255, 255, 255, 0.35);
    }
    .holiday-fx-toggle.is-paused {
        background: rgba(30, 41, 59, 0.85);
        opacity: 0.6;
        border-color: rgba(251, 191, 36, 0.4);
    }
    @media (max-width: 640px) {
        .holiday-fx-toggle {
            bottom: 4.65rem; /* Above mobile bottom navigation */
            left: 0.75rem;
            padding: 0.28rem 0.52rem;
            font-size: 0.68rem;
        }
    }
    @media (prefers-reduced-motion: reduce) {
        .holiday-fx-container {
            display: none !important;
        }
    }
</style>

<div id="holiday-fx-root" class="holiday-fx-container" aria-hidden="true" data-mode="<?= e($fxConfig['mode']) ?>" data-intensity="<?= e($fxConfig['intensity']) ?>" data-toggleable="<?= $fxConfig['allow_toggle'] ? '1' : '0' ?>">
    <canvas id="holiday-fx-canvas" class="holiday-fx-canvas"></canvas>
    <?php if ($fxConfig['allow_toggle']): ?>
    <button type="button" id="holiday-fx-toggle-btn" class="holiday-fx-toggle" aria-label="Toggle seasonal atmospheric effects" title="Pause seasonal atmospheric effects" onclick="if(window.HolidayFX)window.HolidayFX.toggle();">
        <span class="hfx-icon">❄️</span>
        <span class="hfx-text">Pause FX</span>
    </button>
    <?php endif; ?>
</div>

<script src="{{ route('assets/js/holiday-fx.js') }}?v=<?= $jsVer ?>" defer></script>
