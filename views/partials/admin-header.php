<?php

/**
 * Admin Header Partial
 *
 * This file contains the HTML markup for the header section of the admin layout.
 * It is included in the main admin layout and can be customized as needed.
 *
 * @package Evently
 * @subpackage Views/Partials
 * @version 1.0.0
 */

?>

<?php $pendingPayouts = pending_payouts_count(); ?>
<header class="top-nav" role="banner">
    <?php 
        $siteTitle = site_setting('site_title', 'Eventlyy');
        $siteLogo  = site_setting('site_logo');
    ?>
    <?php if (!empty($siteLogo)): ?>
        <a href="{{ route('/') }}" class="brand brand-custom-logo" aria-label="<?= e($siteTitle) ?> home">
            <img src="<?= e(asset($siteLogo)) ?>" alt="<?= e($siteTitle) ?>" style="max-height: 36px; max-width: 140px; object-fit: contain; border-radius: 8px;" />
        </a>
    <?php else: ?>
        <a href="{{ route('/') }}" class="brand" aria-label="<?= e($siteTitle) ?> home"><span class="brand-letter"><?= e(strtoupper(mb_substr($siteTitle, 0, 1))) ?></span></a>
    <?php endif; ?>
    <button id="admin-mobile-menu" class="mobile-menu-btn" aria-label="Open menu" aria-expanded="false" style="position:relative;">
        ☰
        <?php if ($pendingPayouts > 0): ?>
            <span style="position:absolute; top:4px; right:4px; width:8px; height:8px; background:#ef4444; border-radius:50%; box-shadow:0 0 0 2px rgb(var(--card));"></span>
        <?php endif; ?>
    </button>
    <nav class="nav-desktop" role="navigation" aria-label="Top navigation">
        <a href="{{ route('admin/overview') }}" class="nav-link{{ app()->request->is('admin/overview', 'admin', 'admin/dashboard') ? ' active' : '' }}">Overview</a>
        <a href="{{ route('admin/users') }}" class="nav-link{{ app()->request->is('admin/users') ? ' active' : '' }}">Users</a>
        <a href="{{ route('admin/payouts') }}" class="nav-link{{ app()->request->is('admin/payouts') ? ' active' : '' }}" style="display:inline-flex; align-items:center; gap:0.35rem;">
            Payouts
            <?php if ($pendingPayouts > 0): ?>
                <span class="nav-badge-count" style="background:#ef4444; color:#fff; font-size:0.72rem; font-weight:700; border-radius:999px; padding:0.1rem 0.45rem; line-height:1; min-width:18px; text-align:center; display:inline-block; box-shadow:0 2px 4px rgba(239, 68, 68, 0.4);"><?= $pendingPayouts ?></span>
            <?php endif; ?>
        </a>
        <a href="{{ route('admin/settings') }}" class="nav-link{{ app()->request->is('admin/settings') ? ' active' : '' }}">Settings</a>
        <a href="{{ route('admin/teams') }}" class="nav-link{{ app()->request->is('admin/teams*') ? ' active' : '' }}">Teams</a>
        <a href="{{ route('host/dashboard') }}" class="nav-link" title="Switch to Host Dashboard to create and manage events" style="display:inline-flex; align-items:center; gap:0.3rem; color:rgb(var(--brand)); font-weight:600;">
            <span>🎪</span> Host View
        </a>
        <a href="{{ route('profile') }}" class="nav-link{{ app()->request->is('profile') ? ' active' : '' }}" style="display:inline-flex; align-items:center; gap:0.35rem; font-weight:700;">👤 <?= e(user_first_name()) ?></a>
        <button id="theme-toggle" data-theme-toggle="true" aria-label="Toggle theme" aria-pressed="false">🌓</button>
    </nav>
</header>