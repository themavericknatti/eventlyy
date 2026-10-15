<?php

/**
 * Host Header Partial
 *
 * This file contains the HTML markup for the header section of the host layout.
 * It is included in the main host layout and can be customized as needed.
 *
 * @package Evently
 * @subpackage Views/Partials
 * @version 1.0.0
 */

?>

<header class="top-nav" role="banner">
    <?php 
        $siteTitle = site_setting('site_title', 'Eventlyy');
        $siteLogo  = site_setting('site_logo');
    ?>
    <?php if (!empty($siteLogo)): ?>
        <a href="{{ route('/') }}" class="brand brand-custom-logo" aria-label="<?= e($siteTitle) ?> home">
            <img src="<?= e(asset($siteLogo)) ?>" alt="<?= e($siteTitle) ?>" />
        </a>
    <?php else: ?>
        <a href="{{ route('/') }}" class="brand" aria-label="<?= e($siteTitle) ?> home"><span class="brand-letter"><?= e(strtoupper(mb_substr($siteTitle, 0, 1))) ?></span></a>
    <?php endif; ?>
    <button id="host-mobile-menu" class="mobile-menu-btn" aria-label="Open menu" aria-expanded="false">☰</button>
    <nav class="nav-desktop" role="navigation" aria-label="Top navigation">
        <a href="{{ route('host/dashboard') }}" class="nav-link{{ app()->request->is('host/dashboard', 'host') ? ' active' : '' }}" aria-current="{{ app()->request->is('host/dashboard', 'host') ? 'true' : 'false' }}">Overview</a>
        <a href="{{ route('host/financials') }}" class="nav-link{{ app()->request->is('host/financials', 'host/payouts') ? ' active' : '' }}" aria-current="{{ app()->request->is('host/financials', 'host/payouts') ? 'true' : 'false' }}">Financials</a>
        <a href="{{ route('host/event/ticket-verification') }}" class="nav-link{{ app()->request->is('host/event/ticket-verification') ? ' active' : '' }}" aria-current="{{ app()->request->is('host/event/ticket-verification') ? 'true' : 'false' }}">Verify Tickets</a>
        <a href="{{ route('host/event/create') }}" class="nav-link{{ app()->request->is('host/event/create') ? ' active' : '' }}" aria-current="{{ app()->request->is('host/event/create') ? 'true' : 'false' }}">Create Event</a>
        <a href="{{ route('host/promo-codes') }}" class="nav-link{{ app()->request->is('host/promo-codes') ? ' active' : '' }}" aria-current="{{ app()->request->is('host/promo-codes') ? 'true' : 'false' }}">Promo Codes</a>
        <a href="{{ route('host/settings') }}" class="nav-link{{ app()->request->is('host/settings') ? ' active' : '' }}" aria-current="{{ app()->request->is('host/settings') ? 'true' : 'false' }}">Settings</a>
        <?php if (hasRole('admin') || hasRole('super-admin')): ?>
        <a href="{{ route('admin/overview') }}" class="nav-link" style="display:inline-flex; align-items:center; gap:0.3rem; color:rgb(var(--brand)); font-weight:700;">
            <span>🛡️</span> Admin Panel
        </a>
        <?php endif; ?>
        <a href="{{ route('profile') }}" class="nav-link{{ app()->request->is('profile') ? ' active' : '' }}" aria-current="{{ app()->request->is('profile') ? 'true' : 'false' }}" style="display:inline-flex; align-items:center; gap:0.35rem; font-weight:700;">👤 <?= e(user_first_name()) ?></a>
        <button id="theme-toggle" data-theme-toggle="true" aria-label="Toggle theme" aria-pressed="false">🌓</button>
    </nav>
</header>