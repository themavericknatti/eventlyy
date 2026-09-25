<?php

/**
 * Bottom Navigation Partial
 *
 * This file contains the HTML markup for the bottom navigation section of the application.
 * It is included in the main layout and can be customized as needed.
 *
 * @package Evently
 * @subpackage Views/Partials
 * @version 1.0.0
 */

?>

<nav class="bottom-nav" aria-label="Mobile navigation" role="navigation">
    <a href="{{ route('/') }}" class="nav-item nav-link{{ app()->request->is('/') ? ' active' : '' }}"
        data-nav-index="0" aria-current="{{ app()->request->is('/') ? " true" : "false" }}">
        <svg viewBox="0 0 24 24" width="20" height="20" aria-hidden>
            <path d="M3 13h8V3H3v10zm0 8h8v-6H3v6zM13 21h8V11h-8v10zm0-18v6h8V3h-8z" />
        </svg>
        <span>Browse</span>
    </a>
    <a href="{{ route('about') }}" class="nav-item nav-link{{ app()->request->is('about') ? ' active' : '' }}"
        data-nav-index="1" aria-current="{{ app()->request->is('about') ? " true" : "false" }}">
        <svg viewBox="0 0 24 24" width="20" height="20" aria-hidden>
            <path d="M12 7a5 5 0 100 10 5 5 0 000-10zM2 20l4-4h12l4 4H2z" />
        </svg>
        <span>About</span>
    </a>
    @auth
    <a href="{{ route('profile') }}" class="nav-item nav-link{{ app()->request->is('profile') ? ' active' : '' }}"
        data-nav-index="2" aria-current="{{ app()->request->is('profile') ? " true" : "false" }}">
        <svg viewBox="0 0 24 24" width="20" height="20" aria-hidden>
            <path
                d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4S8 5.79 8 8s1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z" />
        </svg>
        <span style="max-width:48px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;"><?= e(user_first_name(5)) ?></span>
    </a>
    @endauth
    <button class="nav-item theme-toggle-btn" data-theme-toggle="true" aria-label="Toggle theme">
        <span aria-hidden="true">🌓</span>
        <span>Theme</span>
    </button>
    @guest
    <a href="{{ route('login') }}" class="nav-item nav-link{{ app()->request->is('login') ? ' active' : '' }}"
        data-nav-index="3" aria-current="{{ app()->request->is('login') ? " true" : "false" }}">
        <svg viewBox="0 0 24 24" width="20" height="20" aria-hidden>
            <path
                d="M11 7L9.6 8.4l2.6 2.6H2v2h10.2l-2.6 2.6L11 17l5-5-5-5zm9 12h-8v2h8c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2h-8v2h8v14z" />
        </svg>
        <span>Login</span>
    </a>
    @endguest
</nav>