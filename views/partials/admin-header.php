<?php

/**
 * Admin Header Partial
 *
 * Streamlined, responsive header for the administrative console.
 * Core high-frequency navigation tabs on the left, with an administrative
 * operations and user dropdown menu on the right.
 *
 * @package Eventlyy
 * @subpackage Views/Partials
 * @version 2.0.0
 */

$pendingPayouts = pending_payouts_count();
$siteTitle = site_setting('site_title', 'Eventlyy');
$firstName = user_first_name(16);
$initial = strtoupper(mb_substr($firstName, 0, 1));
$user = \Ace\Application::$app->user;
$isSuperAdmin = function_exists('hasRole') && hasRole('super-admin');
$roleLabel = $isSuperAdmin ? '👑 Super Admin' : '🛡️ Administrator';
?>
<header class="top-nav admin-top-nav" role="banner">
    <div class="admin-nav-left">
        <a href="{{ route('/') }}" class="brand brand-custom-logo" aria-label="<?= e($siteTitle) ?> home">
            <?= site_logo_render('admin') ?>
        </a>

        <!-- Primary Core Administrative Navigation (Clean & Uncluttered) -->
        <nav class="nav-desktop admin-nav-links" role="navigation" aria-label="Top navigation">
            <a href="{{ route('admin/overview') }}" class="nav-link{{ app()->request->is('admin/overview', 'admin', 'admin/dashboard') ? ' active' : '' }}">Overview</a>
            <a href="{{ route('admin/events') }}" class="nav-link{{ app()->request->is('admin/events*') ? ' active' : '' }}">Events</a>
            <a href="{{ route('admin/users') }}" class="nav-link{{ app()->request->is('admin/users*') ? ' active' : '' }}">Users</a>
            <a href="{{ route('admin/payouts') }}" class="nav-link{{ app()->request->is('admin/payouts*') ? ' active' : '' }}" style="display:inline-flex; align-items:center; gap:0.35rem;">
                Payouts
                <?php if ($pendingPayouts > 0): ?>
                    <span class="nav-badge-count" style="background:#ef4444; color:#fff; font-size:0.72rem; font-weight:700; border-radius:999px; padding:0.1rem 0.45rem; line-height:1; min-width:18px; text-align:center; display:inline-block; box-shadow:0 2px 4px rgba(239, 68, 68, 0.4);"><?= $pendingPayouts ?></span>
                <?php endif; ?>
            </a>
            <a href="{{ route('admin/support') }}" class="nav-link{{ app()->request->is('admin/support*') ? ' active' : '' }}">Support</a>
            <a href="{{ route('admin/sponsors') }}" class="nav-link{{ app()->request->is('admin/sponsors*') ? ' active' : '' }}">Sponsors</a>
            <a href="{{ route('admin/settings') }}" class="nav-link{{ app()->request->is('admin/settings*') ? ' active' : '' }}">Settings</a>
        </nav>
    </div>

    <!-- Administrative Actions & Profile Control -->
    <div class="admin-nav-actions">
        <button id="theme-toggle" data-theme-toggle="true" aria-label="Toggle theme" aria-pressed="false" class="admin-theme-btn" title="Toggle theme">🌓</button>

        <!-- Compact User & Operations Dropdown -->
        <div class="admin-user-menu" id="admin-user-menu">
            <button type="button" class="admin-user-pill" id="admin-user-btn" aria-haspopup="true" aria-expanded="false" title="Account & Operations Menu">
                <span class="admin-user-avatar"><?= $initial ?></span>
                <span class="admin-user-name"><?= e($firstName) ?></span>
                <svg class="admin-user-caret" viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"></polyline></svg>
            </button>

            <div class="admin-user-dropdown" id="admin-user-dropdown" role="menu" aria-labelledby="admin-user-btn">
                <div class="admin-dropdown-header">
                    <span class="admin-dropdown-name"><?= e($firstName) ?></span>
                    <span class="admin-dropdown-badge"><?= $roleLabel ?></span>
                </div>

                <div class="admin-dropdown-section">
                    <span class="admin-dropdown-section-title">Operations &amp; Views</span>
                    <a href="{{ route('host/dashboard') }}" class="admin-dropdown-item" role="menuitem">
                        <span class="admin-dropdown-icon">🎪</span>
                        <div>
                            <strong>Host Dashboard</strong>
                            <small>Switch to organizer view</small>
                        </div>
                    </a>
                    <a href="{{ route('admin/teams') }}" class="admin-dropdown-item" role="menuitem">
                        <span class="admin-dropdown-icon">🏢</span>
                        <div>
                            <strong>Teams &amp; Staff</strong>
                            <small>Deployments &amp; staff registry</small>
                        </div>
                    </a>
                    <a href="{{ route('host/event/ticket-verification') }}" class="admin-dropdown-item" role="menuitem">
                        <span class="admin-dropdown-icon">🎟️</span>
                        <div>
                            <strong>Gate Scanner</strong>
                            <small>Attendance &amp; ticket check-in</small>
                        </div>
                    </a>
                    <a href="{{ route('admin/sponsors') }}" class="admin-dropdown-item" role="menuitem">
                        <span class="admin-dropdown-icon">✨</span>
                        <div>
                            <strong>Partners &amp; Sponsors</strong>
                            <small>Marquee tape &amp; brand styling</small>
                        </div>
                    </a>
                </div>

                <div class="admin-dropdown-divider"></div>

                <div class="admin-dropdown-section">
                    <span class="admin-dropdown-section-title">Account</span>
                    <a href="{{ route('profile') }}" class="admin-dropdown-item" role="menuitem">
                        <span class="admin-dropdown-icon">👤</span>
                        <div>
                            <strong>My Profile</strong>
                            <small>Account settings &amp; preferences</small>
                        </div>
                    </a>
                    <a href="/logout" class="admin-dropdown-item admin-dropdown-item--logout" role="menuitem">
                        <span class="admin-dropdown-icon">🚪</span>
                        <div>
                            <strong>Sign Out</strong>
                        </div>
                    </a>
                </div>
            </div>
        </div>

        <button id="admin-mobile-menu" class="mobile-menu-btn" aria-label="Open menu" aria-expanded="false" style="position:relative;">
            ☰
            <?php if ($pendingPayouts > 0): ?>
                <span style="position:absolute; top:4px; right:4px; width:8px; height:8px; background:#ef4444; border-radius:50%; box-shadow:0 0 0 2px rgb(var(--card));"></span>
            <?php endif; ?>
        </button>
    </div>
</header>

<script>
(function() {
    var menu = document.getElementById('admin-user-menu');
    var btn = document.getElementById('admin-user-btn');
    if (!menu || !btn) return;

    btn.addEventListener('click', function(e) {
        e.stopPropagation();
        var isOpen = menu.classList.toggle('open');
        btn.setAttribute('aria-expanded', String(isOpen));
    });

    document.addEventListener('click', function(e) {
        if (!menu.contains(e.target)) {
            menu.classList.remove('open');
            btn.setAttribute('aria-expanded', 'false');
        }
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            menu.classList.remove('open');
            btn.setAttribute('aria-expanded', 'false');
        }
    });
})();
</script>
