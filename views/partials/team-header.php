<?php
/**
 * Team Header Partial
 *
 * Premium, highly responsive header partial for the staff workstation environment.
 * Handles desktop, tablet, and mobile breakpoints with clean name/role truncation,
 * dynamic role badges, event switcher, and slide-down drawer on mobile.
 *
 * @package Evently
 * @subpackage Views/Partials
 * @author Celio Natti
 * @version 2.2.0
 */

$staffSession = \Ace\Application::$app->session->get('team_staff');
$activeEvent = \Ace\Application::$app->session->get('team_event');
$activeRole = \Ace\Application::$app->session->get('team_role');

$staffAssignments = [];
if ($staffSession) {
    $teamService = new \App\Services\TeamService();
    $staffAssignments = $teamService->getStaffAssignments($staffSession['id']);
}

// Role badge & short name helper
$roleSlug = strtolower($activeRole['slug'] ?? '');
$fullRoleName = $activeRole['name'] ?? 'Staff';

$roleBadgeClass = 'team-role-badge--default';
$shortRoleName = $fullRoleName;

if (str_contains($roleSlug, 'scanner') || str_contains($roleSlug, 'security')) {
    $roleBadgeClass = 'team-role-badge--danger';
    $shortRoleName = 'Scanner';
} elseif (str_contains($roleSlug, 'support') || str_contains($roleSlug, 'customer')) {
    $roleBadgeClass = 'team-role-badge--info';
    $shortRoleName = 'Support';
} elseif (str_contains($roleSlug, 'tech') || str_contains($roleSlug, 'technical')) {
    $roleBadgeClass = 'team-role-badge--tech';
    $shortRoleName = 'Tech Ops';
} elseif (str_contains($roleSlug, 'finance')) {
    $roleBadgeClass = 'team-role-badge--finance';
    $shortRoleName = 'Finance';
} elseif (str_contains($roleSlug, 'usher') || str_contains($roleSlug, 'floor') || str_contains($roleSlug, 'guide')) {
    $roleBadgeClass = 'team-role-badge--usher';
    $shortRoleName = 'Usher Guide';
} elseif (str_contains($roleSlug, 'organizer') || str_contains($roleSlug, 'coordinator')) {
    $roleBadgeClass = 'team-role-badge--brand';
    $shortRoleName = 'Box Office / Org';
}
?>

<style>
    /* ═══════════════════════════════════════════════════════════════════
       Team Navbar – Desktop & Tablet
       ═══════════════════════════════════════════════════════════════════ */
    .team-navbar {
        background: rgba(var(--card), 0.94);
        backdrop-filter: blur(14px);
        -webkit-backdrop-filter: blur(14px);
        border-bottom: 1px solid rgba(var(--muted), 0.15);
        padding: 0.55rem 1rem;
        position: sticky;
        top: 0;
        z-index: 100;
        width: 100%;
        box-sizing: border-box;
    }

    .team-navbar-container {
        display: flex;
        justify-content: space-between;
        align-items: center;
        max-width: 1200px;
        margin: 0 auto;
        width: 100%;
        gap: 0.75rem;
    }

    .team-navbar-brand {
        display: flex;
        align-items: center;
        gap: 0.65rem;
        min-width: 0;
        flex: 1;
    }

    .team-brand-logo {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        text-decoration: none;
        color: rgb(var(--text));
        font-weight: 700;
        font-size: 0.95rem;
        flex-shrink: 0;
    }

    .team-brand-badge-icon {
        width: 30px;
        height: 30px;
        border-radius: 8px;
        background: linear-gradient(135deg, rgb(var(--brand)), #10b981);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 0.9rem;
        box-shadow: 0 2px 8px rgba(var(--brand), 0.3);
        flex-shrink: 0;
    }

    .team-brand-text {
        color: rgb(var(--text));
        white-space: nowrap;
        font-weight: 700;
    }

    .team-event-switcher {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        min-width: 0;
        max-width: 260px;
    }

    .team-event-select {
        padding: 0.3rem 0.65rem;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
        border: 1px solid rgba(var(--brand), 0.35);
        background: rgb(var(--bg));
        color: rgb(var(--text));
        cursor: pointer;
        outline: none;
        max-width: 220px;
        text-overflow: ellipsis;
        white-space: nowrap;
        overflow: hidden;
    }

    .team-event-static-tag {
        font-size: 0.78rem;
        font-weight: 600;
        color: rgb(var(--muted));
        background: rgba(var(--text), 0.04);
        padding: 0.25rem 0.6rem;
        border-radius: 20px;
        border: 1px solid rgba(var(--muted), 0.15);
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        max-width: 220px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    /* Right side - desktop */
    .team-nav-right-desktop {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        flex-shrink: 0;
    }

    .team-user-pill {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        background: rgba(var(--text), 0.04);
        border: 1px solid rgba(var(--muted), 0.15);
        padding: 0.25rem 0.65rem 0.25rem 0.35rem;
        border-radius: 30px;
        max-width: 220px;
    }

    .team-user-avatar {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        background: linear-gradient(135deg, #059669, #10b981);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 0.75rem;
        flex-shrink: 0;
    }

    .team-user-details {
        display: flex;
        flex-direction: column;
        line-height: 1.15;
        min-width: 0;
        max-width: 150px;
    }

    .team-user-name {
        font-size: 0.8rem;
        font-weight: 700;
        color: rgb(var(--text));
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        display: block;
    }

    /* Role Pill Badges */
    .team-role-badge {
        font-size: 0.64rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        padding: 0.1rem 0.4rem;
        border-radius: 6px;
        display: inline-block;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 100%;
    }

    .team-role-badge--danger {
        background: rgba(239, 68, 68, 0.14);
        color: #ef4444;
        border: 1px solid rgba(239, 68, 68, 0.25);
    }

    .team-role-badge--info {
        background: rgba(14, 165, 233, 0.14);
        color: #0ea5e9;
        border: 1px solid rgba(14, 165, 233, 0.25);
    }

    .team-role-badge--tech {
        background: rgba(16, 185, 129, 0.14);
        color: #10b981;
        border: 1px solid rgba(16, 185, 129, 0.25);
    }

    .team-role-badge--finance {
        background: rgba(20, 184, 166, 0.14);
        color: #0d9488;
        border: 1px solid rgba(20, 184, 166, 0.25);
    }

    .team-role-badge--usher {
        background: rgba(168, 85, 247, 0.14);
        color: #9333ea;
        border: 1px solid rgba(168, 85, 247, 0.25);
    }

    .team-role-badge--brand {
        background: rgba(var(--brand), 0.14);
        color: rgb(var(--brand));
        border: 1px solid rgba(var(--brand), 0.25);
    }

    .team-role-badge--default {
        background: rgba(var(--muted), 0.14);
        color: rgb(var(--text));
        border: 1px solid rgba(var(--muted), 0.22);
    }

    /* ─── Hamburger button (mobile only) ─── */
    .team-mobile-hamburger {
        display: none;
        background: rgba(var(--text), 0.06);
        border: 1px solid rgba(var(--muted), 0.2);
        color: rgb(var(--text));
        font-size: 1.25rem;
        cursor: pointer;
        padding: 0.35rem 0.55rem;
        border-radius: 10px;
        line-height: 1;
        flex-shrink: 0;
        transition: all 0.2s ease;
    }
    .team-mobile-hamburger:hover,
    .team-mobile-hamburger:active {
        background: rgba(var(--text), 0.12);
    }

    /* ─── Mobile event label (shown in top bar on mobile only) ─── */
    .team-mobile-event-label {
        display: none;
        font-size: 0.82rem;
        font-weight: 700;
        color: rgb(var(--text));
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        min-width: 0;
        flex: 1;
    }

    /* ─── Mobile slide-down drawer ─── */
    .team-mobile-drawer {
        display: none;
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.3s cubic-bezier(0.16, 1, 0.3, 1), padding 0.3s ease;
        background: rgb(var(--card));
        border-bottom: 1px solid rgba(var(--muted), 0.18);
        padding: 0 1rem;
        box-shadow: 0 12px 30px rgba(0, 0, 0, 0.25);
    }
    .team-mobile-drawer.open {
        max-height: 420px;
        padding: 0.85rem 1rem 1.15rem;
    }

    .drawer-section {
        display: flex;
        flex-direction: column;
        gap: 0.6rem;
    }

    .drawer-section-label {
        font-size: 0.68rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: rgb(var(--muted));
    }

    .drawer-staff-card {
        display: flex;
        align-items: center;
        gap: 0.65rem;
        padding: 0.4rem 0;
    }

    .drawer-staff-avatar {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        background: linear-gradient(135deg, #059669, #10b981);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 0.85rem;
        flex-shrink: 0;
    }

    .drawer-staff-info {
        display: flex;
        flex-direction: column;
        gap: 0.2rem;
        min-width: 0;
        flex: 1;
    }

    .drawer-staff-name {
        font-size: 0.92rem;
        font-weight: 700;
        color: rgb(var(--text));
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .drawer-event-select {
        width: 100%;
        padding: 0.55rem 0.75rem;
        border-radius: 10px;
        font-size: 0.88rem;
        font-weight: 600;
        border: 1px solid rgba(var(--brand), 0.35);
        background: rgb(var(--bg));
        color: rgb(var(--text));
        cursor: pointer;
        box-sizing: border-box;
    }

    .drawer-event-tag {
        font-size: 0.88rem;
        font-weight: 600;
        color: rgb(var(--text));
        padding: 0.55rem 0.75rem;
        background: rgba(var(--text), 0.04);
        border: 1px solid rgba(var(--muted), 0.15);
        border-radius: 10px;
    }

    .drawer-actions {
        display: flex;
        gap: 0.5rem;
        padding-top: 0.35rem;
    }

    .drawer-exit-btn {
        flex: 1;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        font-size: 0.85rem;
        font-weight: 700;
        text-decoration: none;
        box-sizing: border-box;
    }

    .drawer-divider {
        border: none;
        border-top: 1px solid rgba(var(--muted), 0.15);
        margin: 0.35rem 0;
    }

    /* ═══════════════════════════════════════════════════════════════════
       Tablet Breakpoints (769px - 1024px)
       ═══════════════════════════════════════════════════════════════════ */
    @media (max-width: 1024px) {
        .team-brand-text {
            display: none;
        }
        .team-event-switcher, .team-event-static-tag {
            max-width: 160px;
        }
        .team-event-select {
            max-width: 150px;
        }
        .team-user-pill {
            max-width: 170px;
        }
        .team-user-details {
            max-width: 110px;
        }
    }

    /* ═══════════════════════════════════════════════════════════════════
       Mobile Breakpoints (≤ 768px)
       ═══════════════════════════════════════════════════════════════════ */
    @media (max-width: 768px) {
        /* Hide desktop elements */
        .team-brand-text,
        .team-event-switcher,
        .team-event-static-tag,
        .team-nav-right-desktop {
            display: none !important;
        }

        /* Show mobile elements */
        .team-mobile-hamburger {
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .team-mobile-event-label {
            display: block;
        }
        .team-mobile-drawer {
            display: block;
        }

        .team-navbar {
            padding: 0.5rem 0.75rem;
        }

        .team-navbar-container {
            gap: 0.5rem;
        }

        .team-brand-badge-icon {
            width: 28px;
            height: 28px;
            font-size: 0.82rem;
        }
    }
</style>

<header class="team-navbar">
    <div class="team-navbar-container">
        <!-- Brand & Event Context (desktop: logo + text + switcher) -->
        <div class="team-navbar-brand">
            <a href="<?= route('/teams') ?>" class="team-brand-logo">
                <span class="team-brand-badge-icon">E</span>
                <span class="team-brand-text">Workstation</span>
            </a>

            <!-- Mobile: show truncated event name inline -->
            <?php if ($activeEvent): ?>
                <span class="team-mobile-event-label"><?= e($activeEvent['name']) ?></span>
            <?php endif; ?>

            <!-- Desktop: event switcher or static tag -->
            <?php if ($activeEvent): ?>
                <?php if (count($staffAssignments) > 1): ?>
                    <form method="POST" action="<?= route('/teams/switch-event') ?>" class="team-event-switcher">
                        <?= csrf_field() ?>
                        <span style="color:rgb(var(--muted)); font-size:0.75rem;">•</span>
                        <select name="event_id" onchange="this.form.submit()" class="team-event-select" aria-label="Switch Active Event">
                            <?php foreach ($staffAssignments as $asg): ?>
                                <option value="<?= $asg['event_id'] ?>" <?= $asg['event_id'] == $activeEvent['id'] ? 'selected' : '' ?>>
                                    🎟️ <?= e($asg['event_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </form>
                <?php else: ?>
                    <span class="team-event-static-tag" title="<?= e($activeEvent['name']) ?>">
                        🎟️ <?= e($activeEvent['name']) ?>
                    </span>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <!-- Desktop: Staff Profile & Actions -->
        <div class="team-nav-right-desktop">
            <?php if ($staffSession): ?>
                <div class="team-user-pill" title="<?= e($staffSession['name']) ?> • <?= e($fullRoleName) ?>">
                    <span class="team-user-avatar">
                        <?= strtoupper(substr($staffSession['name'], 0, 2)) ?>
                    </span>
                    <div class="team-user-details">
                        <span class="team-user-name"><?= e($staffSession['name']) ?></span>
                        <span class="team-role-badge <?= $roleBadgeClass ?>" title="<?= e($fullRoleName) ?>"><?= e($shortRoleName) ?></span>
                    </div>
                </div>

                <a href="<?= route('/teams/logout') ?>" class="admin-action-btn danger"
                    style="padding:0.35rem 0.65rem; font-size:0.78rem; text-decoration:none; border-radius:16px; height:30px; display:inline-flex; align-items:center;">
                    Exit
                </a>
            <?php else: ?>
                <a href="<?= route('/teams/login') ?>" class="admin-action-btn primary"
                    style="padding:0.35rem 0.75rem; font-size:0.78rem; text-decoration:none; border-radius:16px;">
                    Login
                </a>
            <?php endif; ?>

            <button id="theme-toggle" data-theme-toggle="true" class="auth-theme-btn" aria-label="Toggle theme"
                style="font-size:1rem; cursor:pointer; background:none; border:none; padding:0.15rem; display:inline-flex; align-items:center; color:rgb(var(--text));">🌓</button>
        </div>

        <!-- Mobile: Hamburger Button -->
        <button class="team-mobile-hamburger" id="team-menu-toggle" aria-label="Open menu" type="button">☰</button>
    </div>
</header>

<!-- Mobile Drawer (slides down below header) -->
<div class="team-mobile-drawer" id="team-mobile-drawer">
    <div class="drawer-section">
        <?php if ($staffSession): ?>
            <!-- Staff identity -->
            <div class="drawer-staff-card">
                <span class="drawer-staff-avatar"><?= strtoupper(substr($staffSession['name'], 0, 2)) ?></span>
                <div class="drawer-staff-info">
                    <span class="drawer-staff-name"><?= e($staffSession['name']) ?></span>
                    <span class="team-role-badge <?= $roleBadgeClass ?>" style="max-width: fit-content;"><?= e($fullRoleName) ?></span>
                </div>
            </div>

            <hr class="drawer-divider">

            <!-- Event Switcher -->
            <?php if ($activeEvent): ?>
                <span class="drawer-section-label">Active Event</span>
                <?php if (count($staffAssignments) > 1): ?>
                    <form method="POST" action="<?= route('/teams/switch-event') ?>">
                        <?= csrf_field() ?>
                        <select name="event_id" onchange="this.form.submit()" class="drawer-event-select" aria-label="Switch Event">
                            <?php foreach ($staffAssignments as $asg): ?>
                                <option value="<?= $asg['event_id'] ?>" <?= $asg['event_id'] == $activeEvent['id'] ? 'selected' : '' ?>>
                                    🎟️ <?= e($asg['event_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </form>
                <?php else: ?>
                    <div class="drawer-event-tag">🎟️ <?= e($activeEvent['name']) ?></div>
                <?php endif; ?>
            <?php endif; ?>

            <hr class="drawer-divider">

            <!-- Actions -->
            <div class="drawer-actions">
                <a href="<?= route('/teams/logout') ?>" class="admin-action-btn danger drawer-exit-btn">Exit Workstation</a>
                <button id="theme-toggle-mobile" data-theme-toggle="true" class="auth-theme-btn" aria-label="Toggle theme"
                    style="font-size:1.1rem; cursor:pointer; background:rgba(var(--text),0.06); border:1px solid rgba(var(--muted),0.18); padding:0.4rem 0.65rem; border-radius:10px; display:flex; align-items:center; color:rgb(var(--text));">🌓</button>
            </div>
        <?php else: ?>
            <div class="drawer-actions">
                <a href="<?= route('/teams/login') ?>" class="admin-action-btn primary drawer-exit-btn">Login</a>
                <button id="theme-toggle-mobile" data-theme-toggle="true" class="auth-theme-btn" aria-label="Toggle theme"
                    style="font-size:1.1rem; cursor:pointer; background:rgba(var(--text),0.06); border:1px solid rgba(var(--muted),0.18); padding:0.4rem 0.65rem; border-radius:10px; display:flex; align-items:center; color:rgb(var(--text));">🌓</button>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
(function() {
    var toggle = document.getElementById('team-menu-toggle');
    var drawer = document.getElementById('team-mobile-drawer');
    if (!toggle || !drawer) return;

    toggle.addEventListener('click', function(e) {
        e.stopPropagation();
        var isOpen = drawer.classList.contains('open');
        drawer.classList.toggle('open');
        toggle.textContent = isOpen ? '☰' : '✕';
        toggle.setAttribute('aria-label', isOpen ? 'Open menu' : 'Close menu');
    });

    // Close drawer when clicking outside
    document.addEventListener('click', function(e) {
        if (!drawer.contains(e.target) && !toggle.contains(e.target) && drawer.classList.contains('open')) {
            drawer.classList.remove('open');
            toggle.textContent = '☰';
        }
    });
})();
</script>
