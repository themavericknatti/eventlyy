<?php

/**
 * Auth Layout
 *
 * This layout is used for authentication-related views such as login and registration.
 *
 * @package Evently
 * @subpackage Views/Layouts
 * @author Your Name <Celio Natti>
 * @license MIT License
 * @version 1.0.0
 */

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
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
    @yield('style')
</head>

<body>
    <!-- Floating decorative background elements -->
    <div class="hero-orb hero-orb-1" style="top: -10%; left: -10%; width: 50vw; height: 50vw; opacity: 0.15;"
        aria-hidden="true"></div>
    <div class="hero-orb hero-orb-2" style="bottom: -10%; right: -10%; width: 40vw; height: 40vw; opacity: 0.1;"
        aria-hidden="true"></div>

    <main class="container auth-container">
        <!-- Render Flash Messages -->
        @session('success')
            <div class="alert alert-success">
                <span>✅</span> {!! $value !!}
            </div>
        @endsession

        @session('error')
            <div class="alert alert-danger">
                <span>❌</span> {!! $value !!}
            </div>
        @endsession

        @session('warning')
            <div class="alert alert-warning">
                <span>⚠️</span> {!! $value !!}
            </div>
        @endsession

        @session('info')
            <div class="alert alert-info">
                <span>ℹ️</span> {!! $value !!}
            </div>
        @endsession
        
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