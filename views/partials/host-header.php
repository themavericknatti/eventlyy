<?php

/**
 * Host Header Partial
 *
 * Streamlined, responsive header for the event host console.
 * Core high-frequency navigation tabs on the left, standout "+ Create Event" action,
 * and host operations & user dropdown menu on the right.
 *
 * @package Eventlyy
 * @subpackage Views/Partials
 * @version 2.0.0
 */

$siteTitle = site_setting('site_title', 'Eventlyy');
$firstName = user_first_name(16);
$initial = strtoupper(mb_substr($firstName, 0, 1));
$user = \Ace\Application::$app->user;
$isAdmin = function_exists('hasRole') && (hasRole('admin') || hasRole('super-admin'));
$roleLabel = $isAdmin ? '👑 Host & Admin' : '🎪 Event Host';
?>

<header class="top-nav host-top-nav" role="banner">
    <div class="host-nav-left">
        <a href="{{ route('/') }}" class="brand brand-custom-logo" aria-label="<?= e($siteTitle) ?> home">
            <?= site_logo_render('host') ?>
        </a>

        <!-- Primary Core Host Navigation (Uncluttered & Clean) -->
        <nav class="nav-desktop host-nav-links" role="navigation" aria-label="Host top navigation">
            <a href="{{ route('host/dashboard') }}" class="nav-link{{ app()->request->is('host/dashboard', 'host') ? ' active' : '' }}" aria-current="{{ app()->request->is('host/dashboard', 'host') ? 'true' : 'false' }}">Overview</a>
            <a href="{{ route('host/financials') }}" class="nav-link{{ app()->request->is('host/financials*', 'host/payouts*') ? ' active' : '' }}" aria-current="{{ app()->request->is('host/financials*', 'host/payouts*') ? 'true' : 'false' }}">Financials</a>
            <a href="{{ route('host/promo-codes') }}" class="nav-link{{ app()->request->is('host/promo-codes*') ? ' active' : '' }}" aria-current="{{ app()->request->is('host/promo-codes*') ? 'true' : 'false' }}">Promo Codes</a>
            <a href="{{ route('host/settings') }}" class="nav-link{{ app()->request->is('host/settings*') ? ' active' : '' }}" aria-current="{{ app()->request->is('host/settings*') ? 'true' : 'false' }}">Settings</a>
        </nav>
    </div>

    <!-- Host Actions, Create CTA & Profile Control -->
    <div class="host-nav-actions">
        <!-- Prominent Create Event CTA -->
        <a href="{{ route('host/event/create') }}" class="host-create-btn" title="Create a new event experience">
            <span class="host-create-icon">+</span>
            <span class="host-create-text">Create Event</span>
        </a>

        <button id="theme-toggle" data-theme-toggle="true" aria-label="Toggle theme" aria-pressed="false" class="host-theme-btn" title="Toggle theme">🌓</button>

        <!-- Host User & Operations Dropdown -->
        <div class="host-user-menu" id="host-user-menu">
            <button type="button" class="host-user-pill" id="host-user-btn" aria-haspopup="true" aria-expanded="false" title="Account & Operations Menu">
                <span class="host-user-avatar"><?= $initial ?></span>
                <span class="host-user-name"><?= e($firstName) ?></span>
                <svg class="host-user-caret" viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"></polyline></svg>
            </button>

            <div class="host-user-dropdown" id="host-user-dropdown" role="menu" aria-labelledby="host-user-btn">
                <div class="host-dropdown-header">
                    <span class="host-dropdown-name"><?= e($firstName) ?></span>
                    <span class="host-dropdown-badge"><?= $roleLabel ?></span>
                </div>

                <div class="host-dropdown-section">
                    <span class="host-dropdown-section-title">Host Operations</span>
                    <a href="{{ route('host/event/ticket-verification') }}" class="host-dropdown-item" role="menuitem">
                        <span class="host-dropdown-icon">🎟️</span>
                        <div>
                            <strong>Verify Tickets</strong>
                            <small>Gate attendance &amp; check-in</small>
                        </div>
                    </a>
                    <a href="{{ route('host/event/create') }}" class="host-dropdown-item" role="menuitem">
                        <span class="host-dropdown-icon">➕</span>
                        <div>
                            <strong>Create Event</strong>
                            <small>Launch new event experience</small>
                        </div>
                    </a>
                    <a href="{{ route('host/promo-codes') }}" class="host-dropdown-item" role="menuitem">
                        <span class="host-dropdown-icon">🏷️</span>
                        <div>
                            <strong>Promo Codes</strong>
                            <small>Discounts &amp; ticket perks</small>
                        </div>
                    </a>
                    <a href="{{ route('host/financials') }}" class="host-dropdown-item" role="menuitem">
                        <span class="host-dropdown-icon">💳</span>
                        <div>
                            <strong>Financials &amp; Settlements</strong>
                            <small>Revenue and bank transfers</small>
                        </div>
                    </a>
                    <?php if ($isAdmin): ?>
                    <a href="{{ route('admin/overview') }}" class="host-dropdown-item host-dropdown-item--admin" role="menuitem">
                        <span class="host-dropdown-icon">🛡️</span>
                        <div>
                            <strong>Admin Console</strong>
                            <small>Switch to site administration</small>
                        </div>
                    </a>
                    <?php endif; ?>
                </div>

                <div class="host-dropdown-divider"></div>

                <div class="host-dropdown-section">
                    <span class="host-dropdown-section-title">Account &amp; Mode</span>
                    <a href="{{ route('profile') }}" class="host-dropdown-item" role="menuitem">
                        <span class="host-dropdown-icon">👤</span>
                        <div>
                            <strong>My Profile</strong>
                            <small>Account settings &amp; preferences</small>
                        </div>
                    </a>
                    <a href="{{ route('/') }}" class="host-dropdown-item" role="menuitem">
                        <span class="host-dropdown-icon">🌐</span>
                        <div>
                            <strong>Explore Events</strong>
                            <small>Switch to attendee view</small>
                        </div>
                    </a>
                    <a href="/logout" class="host-dropdown-item host-dropdown-item--logout" role="menuitem">
                        <span class="host-dropdown-icon">🚪</span>
                        <div>
                            <strong>Sign Out</strong>
                        </div>
                    </a>
                </div>
            </div>
        </div>

        <button id="host-mobile-menu" class="mobile-menu-btn" aria-label="Open menu" aria-expanded="false">☰</button>
    </div>
</header>

<script>
(function() {
    var menu = document.getElementById('host-user-menu');
    var btn = document.getElementById('host-user-btn');
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
