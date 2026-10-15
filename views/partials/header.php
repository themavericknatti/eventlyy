<?php

/**
 * Header Partial
 *
 * This file contains the HTML markup for the header section of the application.
 * It is included in the main layout and can be customized as needed.
 *
 * @package Evently
 * @subpackage Views/Partials
 * @version 1.0.0
 */

?>
<header class="top-nav" role="banner">
    <?php $siteLogo = site_setting('site_logo'); ?>
    <?php if (!empty($siteLogo)): ?>
        <a href="{{ route('/') }}" class="brand brand-custom-logo" aria-label="<?= e(site_setting('site_title', 'Eventlyy')) ?> home">
            <img src="<?= e(asset($siteLogo)) ?>" alt="<?= e(site_setting('site_title', 'Eventlyy')) ?>" />
        </a>
    <?php else: ?>
        <?php $siteTitle = site_setting('site_title', 'Eventlyy'); ?>
        <a href="{{ route('/') }}" class="brand" aria-label="<?= e($siteTitle) ?> home"><span class="brand-letter"><?= e(strtoupper(mb_substr($siteTitle, 0, 1))) ?></span></a>
    <?php endif; ?>
    <nav class="nav-desktop" role="navigation" aria-label="Top navigation">
        <a href="{{ route('/') }}" class="nav-link{{ app()->request->is('/') ? ' active' : '' }}" data-nav-index="0"
            aria-current="{{ app()->request->is('/') ? " true" : "false" }}">Browse</a>
        <a href="{{ route('about') }}" class="nav-link{{ app()->request->is('about') ? ' active' : '' }}"
            data-nav-index="1" aria-current="{{ app()->request->is('about') ? " true" : "false" }}">About</a>
        @auth
        <a href="{{ route('profile') }}" class="nav-link{{ app()->request->is('profile') ? ' active' : '' }}"
            data-nav-index="2" aria-current="{{ app()->request->is('profile') ? " true" : "false" }}" style="display:inline-flex; align-items:center; gap:0.35rem; font-weight:700;">👤 <?= e(user_first_name()) ?></a>
        @endauth
        <a href="{{ route('support') }}" class="nav-link{{ app()->request->is('support') ? ' active' : '' }}"
            data-nav-index="3" aria-current="{{ app()->request->is('support') ? " true" : "false" }}">Support</a>
        @guest
        <a href="{{ route('login') }}" class="nav-link{{ app()->request->is('login') ? ' active' : '' }}"
            data-nav-index="4" aria-current="{{ app()->request->is('login') ? " true" : "false" }}">Login</a>
        @endguest
        <button id="theme-toggle" data-theme-toggle="true" aria-label="Toggle theme" aria-pressed="false">🌓</button>
    </nav>
</header>