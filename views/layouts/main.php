<?php

/**
 * Main Layout — Archival & Heritage Edition
 *
 * This is the main layout for the application.
 *
 * @package Eventlyy
 * @subpackage Views/Layouts
 * @author Your Name <Celio Natti>
 * @license MIT License
 * @version 2.0.0
 */

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <meta name="apple-mobile-web-app-status-bar-style" content="default" />
    <title>@yield('title')</title>
    <meta name="csrf-token" content="<?= csrf_token() ?>" />
    <?= site_favicon_tags() ?>
    <?php if ($metaDesc = site_setting('meta_description')): ?>
    <meta name="description" content="<?= e($metaDesc) ?>">
    <?php endif; ?>
    <?php if ($metaKeys = site_setting('meta_keywords')): ?>
    <meta name="keywords" content="<?= e($metaKeys) ?>">
    <?php endif; ?>
    @yield('meta')
    <?php if (empty($this->sections['meta'])): ?>
    <?php
        $siteTitle = site_setting('site_title', 'Eventlyy');
        $siteLogo = site_setting('site_logo');
        $siteLogoUrl = !empty($siteLogo) ? (str_starts_with($siteLogo, 'http') ? $siteLogo : url($siteLogo)) : url('assets/images/logo.png');
        $defaultOgDesc = site_setting('meta_description') ?: 'Find your next obsession. Live in the moment with Eventlyy verified tickets.';
        $defaultOgUrl = url(app()->request->getPath());
    ?>
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="<?= e($siteTitle) ?>">
    <meta property="og:title" content="<?= e($siteTitle) ?> — Live Experiences &amp; Verified Passes">
    <meta property="og:description" content="<?= e($defaultOgDesc) ?>">
    <meta property="og:url" content="<?= e($defaultOgUrl) ?>">
    <meta property="og:image" content="<?= e($siteLogoUrl) ?>">
    <meta property="og:logo" content="<?= e($siteLogoUrl) ?>">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= e($siteTitle) ?>">
    <meta name="twitter:description" content="<?= e($defaultOgDesc) ?>">
    <meta name="twitter:image" content="<?= e($siteLogoUrl) ?>">
    <?php endif; ?>
    <?php
    $styleVer = asset_version('assets/css/style.css');
    $successVer = asset_version('assets/css/success.css');
    ?>
    <link rel="stylesheet" href="{{ route('assets/css/style.css') }}?v=<?= $styleVer ?>">
    @include('partials/theme-styles')
    @include('partials/pwa-head')

    <?php if (app()->request->is('success')): ?>
    <link rel="stylesheet" href="{{ route('assets/css/success.css') }}?v=<?= $successVer ?>">
    <?php endif; ?>

    @yield('style')
</head>

<body class="vn-shell">
    @include('partials/page-loader')
    <?php if (app()->request->is('about') || app()->request->is('support')): ?>
        <!-- Floating decorative background elements -->
        <div class="hero-orb hero-orb-1" style="top: -10%; left: -10%; width: 50vw; height: 50vw; opacity: 0.15;"
            aria-hidden="true"></div>
        <div class="hero-orb hero-orb-2" style="bottom: -10%; right: -10%; width: 40vw; height: 40vw; opacity: 0.1;"
            aria-hidden="true"></div>
    <?php endif; ?>

    <?php if (app()->request->is('success')): ?>
        <!-- Confetti celebration -->
        <div class="confetti" aria-hidden="true">
            <span></span><span></span><span></span><span></span><span></span>
            <span></span><span></span><span></span><span></span><span></span>
        </div>

    <?php endif; ?>

    @include('partials/header')

    <main class="container{{ app()->request->is('event/*') ? ' event-detail-page' : '' }}{{ app()->request->is('terms', 'privacy', 'guide', 'support', 'about', 'profile') ? ' legal-codex-page' : '' }}{{ app()->request->is('/') ? ' home-page' : '' }}{{ app()->request->is('about') ? ' about-page' : '' }}{{ app()->request->is('profile') ? ' profile-page' : '' }}{{ app()->request->is('user/passport*') ? ' passport-page' : '' }}">
        @session('success')
        <div class="alert alert-success">
            <span>✅</span> {{ $value }}
        </div>
        @endsession

        @session('error')
        <div class="alert alert-danger">
            <span>❌</span> {{ $value }}
        </div>
        @endsession

        @session('warning')
        <div class="alert alert-warning">
            <span>⚠️</span> {{ $value }}
        </div>
        @endsession

        @session('info')
        <div class="alert alert-info">
            <span>ℹ️</span> {{ $value }}
        </div>
        @endsession

        @yield('content')
    </main>
    @include('partials/footer')
    @include('partials/bottom-nav')
    @include('partials/announcement-modal')

    <!-- Scroll-To-Top Floating Button -->
    <button id="scroll-to-top" class="scroll-to-top-btn" aria-label="Scroll to top">
        <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <line x1="12" y1="19" x2="12" y2="5" />
            <polyline points="5 12 12 5 19 12" />
        </svg>
    </button>

    <!-- JS Files -->
    <?php if (app()->request->is('*activity*', '*checkout*')): ?>
    <script src="https://js.paystack.co/v2/inline.js" defer></script>
    <?php endif; ?>
    <?php
    $jsVer = asset_version('assets/js/app.js');
    ?>
    <script src="{{ route('assets/js/app.js') }}?v=<?= $jsVer ?>" defer></script>
    @yield('script')
    <div id="theme-announcer" class="visually-hidden" aria-live="polite"></div>
</body>

</html>
