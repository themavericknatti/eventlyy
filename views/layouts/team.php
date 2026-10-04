<?php

/**
 * Team Layout
 *
 * This is the team layout for the application workstation environment.
 *
 * @package Evently
 * @subpackage Views/Layouts
 * @author Your Name <Celio Natti>
 * @license MIT License
 * @version 1.2.0
 */

$isLoginPage = app()->request->is('teams/login') || app()->request->is('teams');
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=5" />
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <meta name="apple-mobile-web-app-status-bar-style" content="default" />
    <meta name="mobile-web-app-capable" content="yes" />
    <meta name="csrf-token" content="<?= csrf_token() ?>" />
    <title>@yield('title')</title>
    <?php if ($favicon = site_setting('site_favicon')): ?>
    <link rel="icon" href="<?= e(asset($favicon)) ?>">
    <?php endif; ?>
    <link rel="stylesheet" href="{{ route('assets/css/style.css') }}?v=<?= asset_version('assets/css/style.css') ?>">

    <script src="{{ route('assets/js/html5qrcode.min.js') }}?v=<?= asset_version('assets/js/html5qrcode.min.js') ?>"></script>

    <style>
        .team-workstation-body {
            background: rgb(var(--bg));
            color: rgb(var(--text));
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .team-main-container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 1.5rem 1rem 3rem;
            box-sizing: border-box;
            flex: 1;
        }

        /* ─── Shared Team Modal Styles (Crystal Clear Light & Dark Mode) ─── */
        .team-modal-backdrop {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.55);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            z-index: 10000;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            box-sizing: border-box;
        }
        .team-modal-dialog {
            background: rgb(var(--card));
            color: rgb(var(--text));
            border: 1px solid rgba(var(--muted), 0.22);
            border-radius: 20px;
            max-width: 520px;
            width: 100%;
            max-height: 90vh;
            overflow-y: auto;
            padding: 1.75rem;
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.35);
            box-sizing: border-box;
            animation: teamModalScale 0.22s ease-out;
        }
        @keyframes teamModalScale {
            from { transform: scale(0.96); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }
        .team-modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.25rem;
            padding-bottom: 0.75rem;
            border-bottom: 1px solid rgba(var(--muted), 0.12);
        }
        .team-modal-title {
            margin: 0;
            font-size: 1.2rem;
            font-weight: 800;
            color: rgb(var(--text));
            display: flex;
            align-items: center;
            gap: 0.45rem;
        }
        .team-modal-subtitle {
            margin: 0.2rem 0 0;
            font-size: 0.82rem;
            color: rgb(var(--muted));
        }
        .team-modal-close-btn {
            background: rgba(var(--text), 0.05);
            border: 1px solid rgba(var(--muted), 0.15);
            border-radius: 10px;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            color: rgb(var(--muted));
            cursor: pointer;
            transition: all 0.15s ease;
        }
        .team-modal-close-btn:hover {
            background: rgba(var(--text), 0.1);
            color: rgb(var(--text));
        }
        .team-modal-label {
            font-size: 0.76rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: rgb(var(--muted));
            display: block;
            margin-bottom: 0.4rem;
        }
        .team-modal-input, .team-modal-select {
            width: 100%;
            box-sizing: border-box;
            padding: 0.75rem 1rem;
            border-radius: 12px;
            border: 1.5px solid rgba(var(--muted), 0.25);
            background: rgb(var(--bg));
            color: rgb(var(--text));
            font-size: 0.92rem;
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .team-modal-input:focus, .team-modal-select:focus {
            border-color: rgb(var(--brand));
            box-shadow: 0 0 0 3px rgba(var(--brand), 0.15);
        }
        .team-modal-notice {
            background: rgba(var(--brand), 0.07);
            border: 1px solid rgba(var(--brand), 0.2);
            border-radius: 12px;
            padding: 0.75rem 1rem;
            margin-bottom: 1.25rem;
            display: flex;
            align-items: center;
            gap: 0.6rem;
        }

        /* Responsive Form Card Overrides */
        .form-card {
            background: rgb(var(--card));
            color: rgb(var(--text));
            border: 1px solid rgba(var(--muted), 0.14);
            border-radius: 20px;
            padding: 1.5rem;
            box-sizing: border-box;
        }

        @media (max-width: 768px) {
            .team-main-container {
                padding: 1rem 0.85rem 2.5rem;
            }
            .form-card {
                padding: 1rem !important;
                border-radius: 16px !important;
            }
            .team-modal-dialog {
                padding: 1.25rem;
                border-radius: 16px;
            }
        }
        @media (max-width: 480px) {
            .team-main-container {
                padding: 0.75rem 0.6rem 2rem;
            }
            .form-card {
                padding: 0.85rem !important;
                border-radius: 14px !important;
            }
        }
    </style>

    @yield('style')
</head>

<body class="<?= $isLoginPage ? 'auth-body' : 'team-workstation-body' ?>">
    <?php if ($isLoginPage): ?>
        <div class="hero-orb hero-orb-1" style="top: -10%; left: -10%; width: 50vw; height: 50vw; opacity: 0.12;"
            aria-hidden="true"></div>
        <div class="hero-orb hero-orb-2" style="bottom: -10%; right: -10%; width: 40vw; height: 40vw; opacity: 0.08;"
            aria-hidden="true"></div>
    <?php endif; ?>

    @include('partials/team-header')

    <main class="container <?= $isLoginPage ? 'auth-container' : 'team-main-container' ?>">
        @session('success')
        <div class="alert alert-success" style="margin-bottom:1.25rem;">
            <span>✅</span> {!! $value !!}
        </div>
        @endsession

        @session('error')
        <div class="alert alert-danger" style="margin-bottom:1.25rem;">
            <span>❌</span> {!! $value !!}
        </div>
        @endsession

        @session('warning')
        <div class="alert alert-warning" style="margin-bottom:1.25rem;">
            <span>⚠️</span> {!! $value !!}
        </div>
        @endsession

        @session('info')
        <div class="alert alert-info" style="margin-bottom:1.25rem;">
            <span>ℹ️</span> {!! $value !!}
        </div>
        @endsession

        @yield('content')
    </main>

    <!-- JS Files -->
    <script src="{{ route('assets/js/app.js') }}?v=<?= asset_version('assets/js/app.js') ?>" defer></script>
    @yield('script')
    <div id="theme-announcer" class="visually-hidden" aria-live="polite"></div>
</body>

</html>