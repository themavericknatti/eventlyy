<?php

/**
 * Host Bottom Navigation Partial
 *
 * This file contains the HTML markup for the bottom navigation section of the host layout.
 * It is included in the main host layout and can be customized as needed.
 *
 * @package Evently
 * @subpackage Views/Partials
 * @version 1.0.0
 */

?>

<nav class="bottom-nav host-bottom" aria-label="Mobile navigation" role="navigation">
    <a href="{{ route('host/dashboard') }}" class="nav-item nav-link{{ app()->request->is('host/dashboard', 'host') ? ' active' : '' }}" data-nav-index="0" aria-current="true">
        <svg viewBox="0 0 24 24" width="20" height="20" aria-hidden>
            <path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z" fill="currentColor" />
        </svg>
        <span>Overview</span>
    </a>
    <a href="{{ route('host/event/create') }}" class="nav-item nav-link{{ app()->request->is('host/event/create') ? ' active' : '' }}" data-nav-index="1">
        <svg viewBox="0 0 24 24" width="20" height="20" aria-hidden>
            <path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z" fill="currentColor" />
        </svg>
        <span>Create</span>
    </a>
    <a href="{{ route('host/event/ticket-verification') }}" class="nav-item nav-link{{ app()->request->is('host/event/ticket-verification') ? ' active' : '' }}" data-nav-index="2">
        <svg viewBox="0 0 24 24" width="20" height="20" aria-hidden>
            <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z" fill="currentColor" />
        </svg>
        <span>Verify</span>
    </a>
    <a href="{{ route('host/settings') }}" class="nav-item nav-link{{ app()->request->is('host/settings') ? ' active' : '' }}" data-nav-index="3">
        <svg viewBox="0 0 24 24" width="20" height="20" aria-hidden>
            <path
                d="M19.14 12.94c.04-.3.06-.61.06-.94 0-.32-.02-.64-.07-.94l2.03-1.58c.18-.14.23-.41.12-.61l-1.92-3.32c-.12-.22-.37-.29-.59-.22l-2.39.96c-.5-.38-1.03-.7-1.62-.94l-.36-2.54c-.04-.24-.24-.41-.48-.41h-3.84c-.24 0-.43.17-.47.41l-.36 2.54c-.59.24-1.13.57-1.62.94l-2.39-.96c-.22-.08-.47 0-.59.22L2.74 8.87c-.12.21-.08.47.12.61l2.03 1.58c-.05.3-.09.63-.09.94s.02.64.07.94l-2.03 1.58c-.18.14-.23.41-.12.61l1.92 3.32c.12.22.37.29.59.22l2.39-.96c.5.38 1.03.7 1.62.94l.36 2.54c.05.24.24.41.48.41h3.84c.24 0 .44-.17.47-.41l.36-2.54c.59-.24 1.13-.56 1.62-.94l2.39.96c.22.08.47 0 .59-.22l1.92-3.32c.12-.22.07-.47-.12-.61l-2.01-1.58zM12 15.6c-1.98 0-3.6-1.62-3.6-3.6s1.62-3.6 3.6-3.6 3.6 1.62 3.6 3.6-1.62 3.6-3.6 3.6z"
                fill="currentColor" />
        </svg>
        <span>Settings</span>
    </a>
    <a href="{{ route('profile') }}" class="nav-item nav-link{{ app()->request->is('profile') ? ' active' : '' }}" data-nav-index="4">
        <svg viewBox="0 0 24 24" width="20" height="20" aria-hidden>
            <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4S8 5.79 8 8s1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z" fill="currentColor" />
        </svg>
        <span style="max-width:48px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;"><?= e(user_first_name(5)) ?></span>
    </a>
</nav>