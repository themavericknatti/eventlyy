<?php
/**
 * Progressive Web App (PWA) Head Partial
 *
 * Injects web app manifest, theme-color metadata, and mobile app icons.
 */
$themeColor = function_exists('theme_primary_color') ? theme_primary_color() : '#108a55';
$appName = site_setting('site_title', 'Eventlyy');
?>
<link rel="manifest" href="{{ route('manifest.json') }}">
<meta name="theme-color" content="<?= $themeColor ?>">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="default">
<meta name="apple-mobile-web-app-title" content="<?= e($appName) ?>">
<link rel="apple-touch-icon" href="{{ route('assets/img/branding/pwa-icon-192.png') }}">
