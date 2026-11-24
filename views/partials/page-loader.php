<?php
/**
 * Global Page Loader Partial - Modern Spinning Green Rings & Center Logo
 *
 * Provides a sleek, high-tech loader with concentric rings rendered in
 * multiple vibrant shades of green spinning smoothly around the central site logo.
 *
 * Performance & Accessibility:
 * - Uses GPU compositor-only properties (transform, opacity).
 * - Inline critical CSS to eliminate Flash of Unstyled Content (FOUC).
 * - Instant pointer-events disengagement so user interaction is never blocked.
 * - Auto-removal from DOM upon completion with hard failsafe timeout.
 * - Full compliance with prefers-reduced-motion.
 */
?>
<div id="app-page-loader" class="app-page-loader" aria-hidden="true" role="presentation">
    <style>
        .app-page-loader {
            position: fixed;
            inset: 0;
            z-index: 999999;
            pointer-events: auto;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgb(var(--bg, 247 249 250));
            will-change: opacity, transform;
            transition: opacity 0.45s ease-out, transform 0.5s cubic-bezier(0.16, 1, 0.3, 1);
        }

        :root[data-theme='dark'] .app-page-loader {
            background: rgb(var(--bg, 15 23 42));
        }

        .app-page-loader.is-loaded {
            opacity: 0 !important;
            transform: scale(1.04) !important;
            pointer-events: none !important;
        }

        /* Ambient glowing green aura in the background */
        .app-loader-aura {
            position: absolute;
            width: 240px;
            height: 240px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(16, 185, 129, 0.28) 0%, rgba(52, 211, 153, 0.12) 45%, transparent 70%);
            filter: blur(16px);
            animation: appGreenAuraPulse 2.8s ease-in-out infinite;
            pointer-events: none;
        }

        /* Centered content wrapper */
        .app-loader-content {
            position: relative;
            z-index: 2;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 1.75rem;
        }

        /* Spinner & Emblem Container */
        .app-loader-spinner-wrap {
            position: relative;
            width: 120px;
            height: 120px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Outer ring: Delicate dashed mint circle rotating slowly */
        .app-loader-ring-outer {
            position: absolute;
            inset: -14px;
            border-radius: 50%;
            border: 1.5px dashed rgba(52, 211, 153, 0.4);
            animation: appGreenRingReverse 10s linear infinite;
        }

        /* Main ring: Multi-shade green gradient sweep */
        .app-loader-ring-main {
            position: absolute;
            inset: -4px;
            border-radius: 50%;
            background: conic-gradient(
                from 0deg,
                transparent 0deg,
                rgba(5, 150, 105, 0.15) 50deg,
                #047857 110deg, /* Deep forest green */
                #059669 170deg, /* Jade green */
                #10b981 230deg, /* Emerald brand green */
                #34d399 290deg, /* Bright mint */
                #6ee7b7 335deg, /* Spring lime */
                #a7f3d0 360deg  /* Soft luminous green */
            );
            -webkit-mask: radial-gradient(farthest-side, transparent calc(100% - 4px), #000 calc(100% - 3.5px));
            mask: radial-gradient(farthest-side, transparent calc(100% - 4px), #000 calc(100% - 3.5px));
            filter: drop-shadow(0 0 6px rgba(52, 211, 153, 0.55));
            animation: appGreenRingSpin 1.15s cubic-bezier(0.5, 0.1, 0.5, 0.9) infinite;
        }

        /* Luminous bead on the leading edge of the main ring */
        .app-loader-ring-main::after {
            content: '';
            position: absolute;
            top: 0;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: #a7f3d0;
            box-shadow: 0 0 8px 2px #34d399;
        }

        /* Inner ring: Two-tone counter-spinning green arcs */
        .app-loader-ring-inner {
            position: absolute;
            inset: 4px;
            border-radius: 50%;
            border: 2px solid transparent;
            border-top-color: #34d399;   /* Bright mint */
            border-bottom-color: #047857;/* Forest green */
            animation: appGreenRingReverse 1.7s ease-in-out infinite;
            opacity: 0.85;
        }

        /* Center Logo Card */
        .app-loader-logo-card {
            position: relative;
            width: 88px;
            height: 88px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgb(var(--card, 255 255 255));
            border: 1px solid rgba(16, 185, 129, 0.22);
            box-shadow: 0 12px 30px -4px rgba(0, 0, 0, 0.12), 0 0 18px -2px rgba(16, 185, 129, 0.18);
        }

        :root[data-theme='dark'] .app-loader-logo-card {
            background: rgb(var(--card, 30 41 59));
            border-color: rgba(52, 211, 153, 0.28);
            box-shadow: 0 14px 34px -4px rgba(0, 0, 0, 0.4), 0 0 20px -2px rgba(16, 185, 129, 0.25);
        }

        /* Inner Logo Breathing Animation */
        .app-loader-logo-inner {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 56px;
            height: 56px;
            animation: appLogoBreath 2.4s ease-in-out infinite;
        }

        .app-loader-logo-inner .brand-logo-wrap {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            height: 42px !important;
            max-height: 42px !important;
        }

        .app-loader-logo-inner img.brand-logo-img {
            max-width: 54px !important;
            max-height: 40px !important;
            width: auto !important;
            height: auto !important;
            object-fit: contain !important;
            display: block !important;
            border-radius: 6px;
        }

        .app-loader-logo-inner .brand-letter {
            font-size: 2.1rem;
            font-weight: 800;
            color: #10b981;
            line-height: 1;
            letter-spacing: -0.04em;
        }

        /* Trio of bouncing green dots */
        .app-loader-dots {
            display: flex;
            align-items: center;
            gap: 7px;
        }

        .app-loader-dots .dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            animation: appDotPulse 1.4s ease-in-out infinite;
        }

        .app-loader-dots .dot-1 {
            background: #059669; /* Forest */
            animation-delay: 0s;
        }

        .app-loader-dots .dot-2 {
            background: #10b981; /* Emerald */
            animation-delay: 0.2s;
        }

        .app-loader-dots .dot-3 {
            background: #34d399; /* Mint */
            animation-delay: 0.4s;
        }

        /* Keyframes */
        @keyframes appGreenRingSpin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        @keyframes appGreenRingReverse {
            0% { transform: rotate(360deg); }
            100% { transform: rotate(0deg); }
        }

        @keyframes appGreenAuraPulse {
            0%, 100% { transform: scale(0.92); opacity: 0.45; }
            50% { transform: scale(1.12); opacity: 0.85; }
        }

        @keyframes appLogoBreath {
            0%, 100% { transform: scale(0.97); }
            50% { transform: scale(1.03); }
        }

        @keyframes appDotPulse {
            0%, 80%, 100% {
                transform: scale(0.75);
                opacity: 0.35;
            }
            40% {
                transform: scale(1.25);
                opacity: 1;
                box-shadow: 0 0 8px currentColor;
            }
        }

        /* Accessibility: prefers-reduced-motion */
        @media (prefers-reduced-motion: reduce) {
            .app-loader-ring-main,
            .app-loader-ring-outer,
            .app-loader-ring-inner,
            .app-loader-aura,
            .app-loader-logo-inner,
            .app-loader-dots .dot {
                animation: none !important;
            }

            .app-page-loader {
                transition: opacity 0.25s ease !important;
            }

            .app-page-loader.is-loaded {
                opacity: 0 !important;
                transform: none !important;
            }
        }
    </style>

    <div class="app-loader-content">
        <div class="app-loader-aura"></div>
        <div class="app-loader-spinner-wrap">
            <div class="app-loader-ring-outer"></div>
            <div class="app-loader-ring-main"></div>
            <div class="app-loader-ring-inner"></div>
            <div class="app-loader-logo-card">
                <div class="app-loader-logo-inner">
                    <?= site_logo_render('loader', 'app-loader-brand-img') ?>
                </div>
            </div>
        </div>
        <div class="app-loader-dots">
            <span class="dot dot-1"></span>
            <span class="dot dot-2"></span>
            <span class="dot dot-3"></span>
        </div>
    </div>

    <script>
        (function() {
            var loader = document.getElementById('app-page-loader');
            if (!loader) return;

            var dismissed = false;
            // Smooth minimum display threshold (800ms) for an optimal, polished production feel
            var minDisplayTime = 800;
            var startTime = Date.now();

            function dismissLoader() {
                if (dismissed) return;
                dismissed = true;

                var elapsed = Date.now() - startTime;
                var remaining = Math.max(0, minDisplayTime - elapsed);

                setTimeout(function() {
                    loader.classList.add('is-loaded');

                    // Completely unmount and clean up from DOM once fade transition completes
                    setTimeout(function() {
                        if (loader && loader.parentNode) {
                            loader.parentNode.removeChild(loader);
                        }
                    }, 520);
                }, remaining);
            }

            // Normal dismiss on DOM ready or window load
            if (document.readyState === 'complete' || document.readyState === 'interactive') {
                dismissLoader();
            } else {
                window.addEventListener('DOMContentLoaded', dismissLoader, { once: true });
                window.addEventListener('load', dismissLoader, { once: true });
            }

            // Hard failsafe timeout: Guarantee loader opens within 2.0s under all circumstances
            setTimeout(dismissLoader, 2000);
        })();
    </script>
</div>
