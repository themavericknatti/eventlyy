<?php
/**
 * 404 Error Page — Lost Folio in Archives
 *
 * @package Eventlyy
 * @subpackage Views/Errors
 */
$siteName = function_exists('site_setting') ? site_setting('site_title', 'Eventlyy') : 'Eventlyy';
$favicon = function_exists('site_setting') ? site_setting('site_favicon') : null;
$styleVer = function_exists('asset_version') ? asset_version('assets/css/style.css') : time();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 — Folio Not Found | <?= htmlspecialchars($siteName, ENT_QUOTES, 'UTF-8') ?></title>
    <?php if ($favicon && function_exists('asset')): ?>
        <link rel="icon" href="<?= htmlspecialchars(asset($favicon), ENT_QUOTES, 'UTF-8') ?>">
    <?php endif; ?>
    <link rel="stylesheet" href="/assets/css/style.css?v=<?= $styleVer ?>">
</head>
<body class="vn-shell" style="min-height: 100vh; display: flex; align-items: center; justify-content: center; margin: 0;">
    <main class="vn-err-wrapper">
        <div class="vn-err-card">
            <span class="vn-badge" style="margin-bottom: 0.75rem;">Archive Register Notice</span>
            <div class="vn-err-numeral">404</div>
            <h1 class="vn-err-title">Folio Not Found in Archives</h1>
            <p class="vn-err-lead">
                The document, gathering, or record you seek has been misplaced, expunged, or was never catalogued in the master registry.
            </p>
            <div class="vn-err-actions">
                <a href="/" class="vn-err-btn vn-err-btn-primary">
                    Return to Main Hall →
                </a>
                <button type="button" onclick="window.history.back()" class="vn-err-btn vn-err-btn-secondary">
                    ← Retrace Previous Steps
                </button>
            </div>
        </div>
    </main>
</body>
</html>
