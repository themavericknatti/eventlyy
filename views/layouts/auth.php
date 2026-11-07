<?php

/**
 * Auth Layout
 *
 * Polished, high-converting layout for authentication flows (Login, Register, Recovery).
 * Fully responsive, theme-adaptive with duo-tone color dynamics and human-crafted UX.
 *
 * @package Evently
 * @subpackage Views/Layouts
 * @author Celio Natti
 * @license MIT License
 * @version 2.0.0
 */

$siteName = site_setting('site_title', 'Eventlyy');
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=5,viewport-fit=cover" />
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <meta name="apple-mobile-web-app-status-bar-style" content="default" />
    <meta name="mobile-web-app-capable" content="yes" />
    <title>@yield('title')</title>
    <?php if ($favicon = site_setting('site_favicon')): ?>
    <link rel="icon" href="<?= e(asset($favicon)) ?>">
    <?php endif; ?>
    <?php
    $styleVer = asset_version('assets/css/style.css');
    ?>
    <script>
        (function() {
            try {
                var saved = localStorage.getItem('theme');
                if (saved) {
                    document.documentElement.setAttribute('data-theme', saved);
                } else if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
                    document.documentElement.setAttribute('data-theme', 'dark');
                }
            } catch (e) {}
        })();
    </script>
    <link rel="stylesheet" href="{{ route('assets/css/style.css') }}?v=<?= $styleVer ?>">
    @include('partials/theme-styles')
    @include('partials/pwa-head')
    @yield('style')
</head>

<body class="auth-body">
    <!-- Atmospheric Ambient Duo-Tone Orbs -->
    <div class="auth-ambient" aria-hidden="true">
        <div class="auth-orb auth-orb-primary"></div>
        <div class="auth-orb auth-orb-secondary"></div>
        <div class="auth-grid-overlay"></div>
    </div>

    <main class="auth-container">
        <!-- Modern Flash Messages Banner -->
        <div class="auth-flash-wrapper">
            @session('success')
                <div class="auth-alert auth-alert-success" role="alert">
                    <span class="auth-alert-icon">
                        <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                    </span>
                    <div class="auth-alert-content">{!! $value !!}</div>
                    <button type="button" class="auth-alert-dismiss" aria-label="Dismiss alert" onclick="this.closest('.auth-alert').remove();">&times;</button>
                </div>
            @endsession

            @session('error')
                <div class="auth-alert auth-alert-danger" role="alert">
                    <span class="auth-alert-icon">
                        <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                    </span>
                    <div class="auth-alert-content">{!! $value !!}</div>
                    <button type="button" class="auth-alert-dismiss" aria-label="Dismiss alert" onclick="this.closest('.auth-alert').remove();">&times;</button>
                </div>
            @endsession

            @session('warning')
                <div class="auth-alert auth-alert-warning" role="alert">
                    <span class="auth-alert-icon">
                        <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                    </span>
                    <div class="auth-alert-content">{!! $value !!}</div>
                    <button type="button" class="auth-alert-dismiss" aria-label="Dismiss alert" onclick="this.closest('.auth-alert').remove();">&times;</button>
                </div>
            @endsession

            @session('info')
                <div class="auth-alert auth-alert-info" role="alert">
                    <span class="auth-alert-icon">
                        <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                    </span>
                    <div class="auth-alert-content">{!! $value !!}</div>
                    <button type="button" class="auth-alert-dismiss" aria-label="Dismiss alert" onclick="this.closest('.auth-alert').remove();">&times;</button>
                </div>
            @endsession
        </div>

        @yield('content')
    </main>

    <!-- JS Files -->
    <?php
    $jsVer = asset_version('assets/js/app.js');
    $authJsVer = asset_version('assets/js/auth.js');
    ?>
    <script src="{{ route('assets/js/app.js') }}?v=<?= $jsVer ?>" defer></script>
    <script src="{{ route('assets/js/auth.js') }}?v=<?= $authJsVer ?>" defer></script>
    @yield('script')
    <div id="theme-announcer" class="visually-hidden" aria-live="polite"></div>
</body>

</html>
