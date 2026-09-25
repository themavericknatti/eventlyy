<?php

/**
 * Dynamic Footer Partial
 *
 * Adapts its content based on the current route context:
 *  - Public pages  → Attendee-focused links (browse, register, login)
 *  - Host pages    → Host management links (dashboard, create event, settings)
 *  - Admin pages   → Admin panel links (overview, users, payouts, settings)
 *
 * Shared across all three layouts (main, host, admin).
 * Reads site branding & social handles dynamically from site_setting().
 *
 * @package Evently
 * @subpackage Views/Partials
 * @version 2.1.0
 */

// Detect user role & route context
$u = user();
$isAdmin = ($u && method_exists($u, 'isAdmin') && $u->isAdmin()) || app()->request->is('admin', 'admin/*');
$isHost  = ($u && method_exists($u, 'isHost') && $u->isHost()) || app()->request->is('host', 'host/*');

// Site settings
$siteTitle       = site_setting('site_title', 'Eventlyy');
$siteLogo        = site_setting('site_logo');
$siteDescription = site_setting('site_description');
$supportEmail    = site_setting('support_email', 'support@eventlyy.com');

$socialTwitter   = site_setting('social_twitter');
$socialInstagram = site_setting('social_instagram');
$socialFacebook  = site_setting('social_facebook');
$socialLinkedin  = site_setting('social_linkedin');
$socialYoutube   = site_setting('social_youtube');
$socialTiktok    = site_setting('social_tiktok');

// Current year
$year = date('Y');

// Derive display name for split logo/text presentation if needed
$firstLetter = mb_substr($siteTitle, 0, 1);
$restOfTitle = mb_substr($siteTitle, 1);
?>

<footer class="footer reveal" role="contentinfo">
    <div class="footer-wave" aria-hidden="true"></div>
    <div class="footer-content">
        <!-- Brand Column -->
        <div class="footer-section footer-brand-col">
            <div class="footer-brand-row">
                <?php if (!empty($siteLogo)): ?>
                    <a href="{{ route('/') }}" class="brand footer-brand-icon" aria-label="<?= e($siteTitle) ?> home">
                        <img src="<?= e(asset($siteLogo)) ?>" alt="<?= e($siteTitle) ?>" style="max-height: 28px; max-width: 100px; object-fit: contain; border-radius: 6px;" />
                    </a>
                <?php else: ?>
                    <a href="{{ route('/') }}" class="brand footer-brand-icon" aria-label="<?= e($siteTitle) ?> home"><span class="brand-letter"><?= e($firstLetter) ?></span></a>
                    <span class="footer-brand-name"><?= e($restOfTitle) ?></span>
                <?php endif; ?>
            </div>
            <p class="footer-tagline">
                <?php if (!empty($siteDescription)): ?>
                    <?= e($siteDescription) ?>
                <?php elseif ($isAdmin): ?>
                    Powering seamless event management &amp; financial operations.
                <?php elseif ($isHost): ?>
                    Create unforgettable events. Grow your audience.
                <?php else: ?>
                    Discover events that move you. Connect with moments that matter.
                <?php endif; ?>
            </p>
            <div class="footer-socials">
                <?php if (!empty($socialTwitter)): ?>
                    <a href="<?= e($socialTwitter) ?>" target="_blank" rel="noopener noreferrer" aria-label="Twitter / X">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                    </a>
                <?php endif; ?>

                <?php if (!empty($socialInstagram)): ?>
                    <a href="<?= e($socialInstagram) ?>" target="_blank" rel="noopener noreferrer" aria-label="Instagram">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/></svg>
                    </a>
                <?php endif; ?>

                <?php if (!empty($socialFacebook)): ?>
                    <a href="<?= e($socialFacebook) ?>" target="_blank" rel="noopener noreferrer" aria-label="Facebook">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                    </a>
                <?php endif; ?>

                <?php if (!empty($socialLinkedin)): ?>
                    <a href="<?= e($socialLinkedin) ?>" target="_blank" rel="noopener noreferrer" aria-label="LinkedIn">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor"><path d="M19 3a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h14m-.5 15.5v-5.3a3.26 3.26 0 0 0-3.26-3.26c-.85 0-1.84.52-2.28 1.3v-1.11h-2.79v8.37h2.79v-4.93c0-.77.62-1.4 1.39-1.4a1.4 1.4 0 0 1 1.4 1.4v4.93h2.75M6.88 8.56a1.68 1.68 0 0 0 1.68-1.68c0-.93-.75-1.69-1.68-1.69a1.69 1.69 0 0 0-1.69 1.69c0 .93.76 1.68 1.69 1.68m1.39 9.94v-8.37H5.5v8.37h2.77z"/></svg>
                    </a>
                <?php endif; ?>

                <?php if (!empty($socialYoutube)): ?>
                    <a href="<?= e($socialYoutube) ?>" target="_blank" rel="noopener noreferrer" aria-label="YouTube">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
                    </a>
                <?php endif; ?>

                <?php if (!empty($socialTiktok)): ?>
                    <a href="<?= e($socialTiktok) ?>" target="_blank" rel="noopener noreferrer" aria-label="TikTok">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor"><path d="M19.59 6.69a4.83 4.83 0 0 1-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 0 1-5.2 1.74 2.89 2.89 0 0 1 2.31-4.64c.29 0 .56.04.83.1v-3.5a6.37 6.37 0 0 0-.83-.05A6.34 6.34 0 0 0 3.15 15.7a6.34 6.34 0 0 0 10.86 4.46V11a8.3 8.3 0 0 0 5.58 2.16V9.71a4.84 4.84 0 0 1-3.32-1.35 4.8 4.8 0 0 1-1.3-3.37h.02z"/></svg>
                    </a>
                <?php endif; ?>

                <?php if (!empty($supportEmail)): ?>
                    <a href="mailto:<?= e($supportEmail) ?>" aria-label="Email support">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor"><path d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/></svg>
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Dynamic Links Column 1 -->
        <div class="footer-section">
            <?php if ($isAdmin): ?>
                <h4>Admin Panel</h4>
                <nav>
                    <a href="{{ route('admin/overview') }}">Overview</a>
                    <a href="{{ route('admin/users') }}">Manage Users</a>
                    <a href="{{ route('admin/events') }}">Manage Events</a>
                    <a href="{{ route('admin/payouts') }}">Payouts</a>
                    <a href="{{ route('admin/support') }}">Support Inquiries</a>
                </nav>
            <?php elseif ($isHost): ?>
                <h4>Host Tools</h4>
                <nav>
                    <a href="{{ route('host/dashboard') }}">Dashboard</a>
                    <a href="{{ route('host/event/create') }}">Create Event</a>
                    <a href="{{ route('host/event/ticket-verification') }}">Verify Tickets</a>
                    <a href="{{ route('host/settings') }}">Settings</a>
                </nav>
            <?php else: ?>
                <h4>Discover</h4>
                <nav>
                    <a href="{{ route('/') }}">Browse Events</a>
                    <a href="{{ route('about') }}">About <?= e($siteTitle) ?></a>
                    <a href="{{ route('support') }}">Help &amp; Support</a>
                </nav>
            <?php endif; ?>
        </div>

        <!-- Dynamic Links Column 2 -->
        <div class="footer-section">
            <?php if ($isAdmin): ?>
                <h4>System</h4>
                <nav>
                    <a href="{{ route('admin/teams') }}">Teams</a>
                    <a href="{{ route('admin/settings') }}">Settings</a>
                    <a href="{{ route('/') }}">View Site</a>
                    <a href="{{ route('support') }}">Help &amp; Support</a>
                </nav>
            <?php elseif ($isHost): ?>
                <h4>Account</h4>
                <nav>
                    <a href="{{ route('profile') }}">👤 <?= e(user_first_name()) ?></a>
                    <a href="{{ route('host/dashboard') }}">Host Dashboard</a>
                    <a href="{{ route('/') }}">Browse Events</a>
                    <a href="{{ route('support') }}">Help &amp; Support</a>
                </nav>
            <?php else: ?>
                <h4>Get Started</h4>
                <nav>
                    @guest
                    <a href="{{ route('register') }}">Create Account</a>
                    <a href="{{ route('login') }}">Sign In</a>
                    @endguest
                    <a href="{{ route('register') }}">Become a Host</a>
                    @auth
                    <a href="{{ route('profile') }}">👤 <?= e(user_first_name()) ?></a>
                    @endauth
                    <a href="{{ route('support') }}">Help &amp; Support</a>
                </nav>
            <?php endif; ?>
        </div>

        <!-- Legal & Account Column -->
        <div class="footer-section">
            <h4>Legal &amp; Help</h4>
            <nav>
                <a href="{{ route('privacy') }}">Privacy Policy</a>
                <a href="{{ route('terms') }}">Terms of Service</a>
                <a href="{{ route('support') }}">Help &amp; Support</a>
            </nav>
            @auth
            <div class="footer-logout">
                <a href="{{ route('logout') }}" class="footer-logout-link">
                    <svg viewBox="0 0 24 24" width="15" height="15" fill="currentColor"><path d="M17 7l-1.41 1.41L18.17 11H8v2h10.17l-2.58 2.58L17 17l5-5zM4 5h8V3H4c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h8v-2H4V5z"/></svg>
                    <span>Sign Out</span>
                </a>
            </div>
            @endauth
        </div>
    </div>

    <div class="footer-bottom">
        <p>&copy; <?= $year ?> <?= e($siteTitle) ?>. All rights reserved.</p>
    </div>
</footer>