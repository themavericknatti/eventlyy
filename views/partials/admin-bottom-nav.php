<?php

/**
 * Admin Bottom Navigation Partial
 *
 * This file contains the HTML markup for the bottom navigation section of the admin layout.
 * It is included in the main admin layout and can be customized as needed.
 *
 * @package Evently
 * @subpackage Views/Partials
 * @version 1.0.0
 */

?>

<nav class="bottom-nav admin-bottom" aria-label="Mobile navigation" role="navigation">
    <a href="{{ route('admin/overview') }}" class="nav-item nav-link{{ app()->request->is('admin/overview', 'admin', 'admin/dashboard') ? ' active' : '' }}" data-nav-index="0" aria-current="true">
        <svg viewBox="0 0 24 24" width="20" height="20" aria-hidden>
            <path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z" fill="currentColor" />
        </svg>
        <span>Overview</span>
    </a>
    <a href="{{ route('admin/payouts') }}" class="nav-item nav-link{{ app()->request->is('admin/payouts') ? ' active' : '' }}" data-nav-index="2" style="position:relative;">
        <svg viewBox="0 0 24 24" width="20" height="20" aria-hidden>
            <path d="M21 18v1c0 1.1-.9 2-2 2H5c-1.11 0-2-.9-2-2V5c0-1.1.89-2 2-2h14c1.1 0 2 .9 2 2v1h-9c-1.11 0-2 .9-2 2v8c0 1.1.89 2 2 2h9zm-9-2h10V8H12v8zm4-2.5c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5z" fill="currentColor" />
        </svg>
        <span>Payouts</span>
        <?php $pendingPayouts = pending_payouts_count(); ?>
        <?php if ($pendingPayouts > 0): ?>
            <span class="bottom-nav-badge" style="position:absolute; top:2px; right:calc(50% - 18px); background:#ef4444; color:#fff; font-size:0.65rem; font-weight:800; border-radius:999px; padding:0.05rem 0.35rem; min-width:16px; height:16px; display:flex; align-items:center; justify-content:center; line-height:1; box-shadow:0 2px 4px rgba(0,0,0,0.3); z-index:2;"><?= $pendingPayouts ?></span>
        <?php endif; ?>
    </a>
    <a href="{{ route('admin/teams') }}" class="nav-item nav-link{{ app()->request->is('admin/teams') ? ' active' : '' }}" data-nav-index="3">
        <svg viewBox="0 0 24 24" width="20" height="20" aria-hidden>
            <path
                d="M20 6h-4V4c0-1.11-.89-2-2-2h-4c-1.11 0-2 .89-2 2v2H4c-1.11 0-1.99.89-1.99 2L2 19c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V8c0-1.11-.89-2-2-2zm-6 0h-4V4h4v2z"
                fill="currentColor" />
        </svg>
        <span>Teams</span>
    </a>
    <a href="{{ route('admin/settings') }}" class="nav-item nav-link{{ app()->request->is('admin/settings') ? ' active' : '' }}" data-nav-index="4">
        <svg viewBox="0 0 24 24" width="20" height="20" aria-hidden>
            <path
                d="M19.14 12.94c.04-.3.06-.61.06-.94 0-.32-.02-.64-.07-.94l2.03-1.58c.18-.14.23-.41.12-.61l-1.92-3.32c-.12-.22-.37-.29-.59-.22l-2.39.96c-.5-.38-1.03-.7-1.62-.94l-.36-2.54c-.04-.24-.24-.41-.48-.41h-3.84c-.24 0-.43.17-.47.41l-.36 2.54c-.59.24-1.13.57-1.62.94l-2.39-.96c-.22-.08-.47 0-.59.22L2.74 8.87c-.12.21-.08.47.12.61l2.03 1.58c-.05.3-.09.63-.09.94s.02.64.07.94l-2.03 1.58c-.18.14-.23.41-.12.61l1.92 3.32c.12.22.37.29.59.22l2.39-.96c.5.38 1.03.7 1.62.94l.36 2.54c.05.24.24.41.48.41h3.84c.24 0 .44-.17.47-.41l.36-2.54c.59-.24 1.13-.56 1.62-.94l2.39.96c.22.08.47 0 .59-.22l1.92-3.32c.12-.22.07-.47-.12-.61l-2.01-1.58zM12 15.6c-1.98 0-3.6-1.62-3.6-3.6s1.62-3.6 3.6-3.6 3.6 1.62 3.6 3.6-1.62 3.6-3.6 3.6z"
                fill="currentColor" />
        </svg>
        <span>Settings</span>
    </a>
    <a href="{{ route('profile') }}" class="nav-item nav-link{{ app()->request->is('profile') ? ' active' : '' }}" data-nav-index="5">
        <svg viewBox="0 0 24 24" width="20" height="20" aria-hidden>
            <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4S8 5.79 8 8s1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z" fill="currentColor" />
        </svg>
        <span style="max-width:48px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;"><?= e(user_first_name(5)) ?></span>
    </a>
</nav>