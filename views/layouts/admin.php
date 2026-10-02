<?php

/**
 * Admin Layout
 *
 * This layout is used for admin-related views.
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
    <meta name="description" content="Overview of registered users, hosts, and payouts." />
	<meta name="csrf-token" content="<?= e(csrf_token()) ?>" />
    <?php
    $styleVer = file_exists(\Ace\Application::$ROOT_DIR . '/public/assets/css/style.css') ? filemtime(\Ace\Application::$ROOT_DIR . '/public/assets/css/style.css') : '1.0.0';
    $scriptVer = file_exists(\Ace\Application::$ROOT_DIR . '/public/assets/js/app.js') ? filemtime(\Ace\Application::$ROOT_DIR . '/public/assets/js/app.js') : '1.0.0';
    ?>
    <link rel="stylesheet" href="{{ route('assets/css/style.css') }}?v=<?= $styleVer ?>">
</head>

<body>
    @include('partials/admin-header')
    <main class="container">
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
    @include('partials/footer')
    @include('partials/admin-bottom-nav')

    <!-- Currency Configuration -->
    <script>
        window.APP_CURRENCY = '<?= setting('currency', 'NGN') ?>';
        window.APP_CURRENCY_SYMBOL = '<?= setting('currency_symbols.' . setting('currency', 'NGN'), '₦') ?>';
    </script>

    <!-- JS Files -->
    <script src="{{ route('assets/js/app.js') }}?v=<?= $scriptVer ?>" defer></script>
    <script src="{{ route('assets/js/admin/admin.js') }}" defer></script>
    <?php if (app()->request->is('admin/users')): ?>
        <script src="{{ route('assets/js/admin/admin-users.js') }}" defer></script>
    <?php elseif(app()->request->is('admin/teams')): ?>
        <script src="{{ route('assets/js/admin/admin-teams.js') }}" defer></script>
    <?php elseif(app()->request->is('admin/payouts')): ?>
        <script src="{{ route('assets/js/admin/admin-payouts.js') }}" defer></script>
    <?php elseif(app()->request->is('admin/events')): ?>
        <script src="{{ route('assets/js/admin/admin-events.js') }}" defer></script>
    <?php endif; ?>
    <div id="theme-announcer" class="visually-hidden" aria-live="polite"></div>
</body>

</html>