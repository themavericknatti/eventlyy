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
	<meta name="csrf-token" content="<?= csrf_token() ?>" />
    <?php if ($favicon = site_setting('site_favicon')): ?>
    <link rel="icon" href="<?= e(asset($favicon)) ?>">
    <?php endif; ?>


    <?php if (app()->request->is('host/event/create', 'host/event/edit/*')): ?>
         <link rel="stylesheet" href="{{ route('assets/css/flatpickr.min.css') }}?v=<?= filemtime(\Ace\Application::$ROOT_DIR . '/public/assets/css/flatpickr.min.css') ?>">
    <?php endif; ?>

    <?php
    $styleVer = file_exists(\Ace\Application::$ROOT_DIR . '/public/assets/css/style.css') ? filemtime(\Ace\Application::$ROOT_DIR . '/public/assets/css/style.css') : '1.0.0';
    $appJsVer = file_exists(\Ace\Application::$ROOT_DIR . '/public/assets/js/app.js') ? filemtime(\Ace\Application::$ROOT_DIR . '/public/assets/js/app.js') : '1.0.0';
    $createEventJsVer = file_exists(\Ace\Application::$ROOT_DIR . '/public/assets/js/host/create-event.js') ? filemtime(\Ace\Application::$ROOT_DIR . '/public/assets/js/host/create-event.js') : '1.0.0';
    $ticketDetailJsVer = file_exists(\Ace\Application::$ROOT_DIR . '/public/assets/js/host/host-ticket-detail.js') ? filemtime(\Ace\Application::$ROOT_DIR . '/public/assets/js/host/host-ticket-detail.js') : '1.0.0';
    $eventMgmtJsVer = file_exists(\Ace\Application::$ROOT_DIR . '/public/assets/js/host/host-event-management.js') ? filemtime(\Ace\Application::$ROOT_DIR . '/public/assets/js/host/host-event-management.js') : '1.0.0';
    $ticketVerifyJsVer = file_exists(\Ace\Application::$ROOT_DIR . '/public/assets/js/host/ticket-verification.js') ? filemtime(\Ace\Application::$ROOT_DIR . '/public/assets/js/host/ticket-verification.js') : '1.0.0';
    $html5QrcodeJsVer = file_exists(\Ace\Application::$ROOT_DIR . '/public/assets/js/html5qrcode.min.js') ? filemtime(\Ace\Application::$ROOT_DIR . '/public/assets/js/html5qrcode.min.js') : '1.0.0';
    ?>

    <link rel="stylesheet" href="{{ route('assets/css/style.css') }}?v=<?= $styleVer ?>">

    <?php if (app()->request->is('host/event/ticket-verification', 'host/event/ticket-verification/*')): ?>
        <script src="{{ route('assets/js/html5qrcode.min.js') }}?v=<?= $html5QrcodeJsVer ?>"></script>
    <?php endif; ?>
</head>

<body>
    @include('partials/host-header')
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
    @include('partials/host-bottom-nav')

    <!-- JS Files -->
    <?php if (app()->request->is('host', 'host/dashboard')): ?>
        <script src="{{ route('assets/js/host/host-dashboard.js') }}"
            defer></script>
    <?php endif; ?>
    <?php if (app()->request->is('host/event/create', 'host/event/edit/*')): ?>
         <script src="{{ route('assets/js/flatpickr.min.js') }}" defer></script>
        <script src="{{ route('assets/js/host/create-event.js') }}?v=<?= $createEventJsVer ?>"
            defer></script>
    <?php elseif (app()->request->is('host/event/ticket/*')): ?>
        <script
            src="{{ route('assets/js/host/host-ticket-detail.js') }}?v=<?= $ticketDetailJsVer ?>"
            defer></script>
    <?php elseif (app()->request->is('host/event/management/*')): ?>
        <script
            src="{{ route('assets/js/host/host-event-management.js') }}?v=<?= $eventMgmtJsVer ?>"
            defer></script>
    <?php elseif (app()->request->is('host/event/ticket-verification', 'host/event/ticket-verification/*')): ?>
        <script
            src="{{ route('assets/js/host/ticket-verification.js') }}?v=<?= $ticketVerifyJsVer ?>"
            defer></script>
    <?php endif; ?>
    <script src="{{ route('assets/js/app.js') }}?v=<?= $appJsVer ?>" defer></script>
    <div id="theme-announcer" class="visually-hidden" aria-live="polite"></div>
</body>

</html>