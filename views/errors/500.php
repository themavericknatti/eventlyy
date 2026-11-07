<?php
/**
 * 500 Error Page — System Ledger & Server Anomaly
 *
 * @package Eventlyy
 * @subpackage Views/Errors
 */
$siteName = function_exists('site_setting') ? site_setting('site_title', 'Eventlyy') : 'Eventlyy';
$favicon = function_exists('site_setting') ? site_setting('site_favicon') : null;
$styleVer = function_exists('asset_version') ? asset_version('assets/css/style.css') : time();
$errorCode = htmlspecialchars($code ?? 500, ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $errorCode ?> — Server Anomaly | <?= htmlspecialchars($siteName, ENT_QUOTES, 'UTF-8') ?></title>
    <?php if ($favicon && function_exists('asset')): ?>
        <link rel="icon" href="<?= htmlspecialchars(asset($favicon), ENT_QUOTES, 'UTF-8') ?>">
    <?php endif; ?>
    <link rel="stylesheet" href="/assets/css/style.css?v=<?= $styleVer ?>">
</head>
<body class="vn-shell" style="min-height: 100vh; display: flex; align-items: center; justify-content: center; margin: 0; padding: 2rem 1rem;">
    <main class="vn-err-wrapper" style="width: 100%; max-width: 760px; min-height: auto; padding: 1rem 0;">
        <div class="vn-err-card" style="max-width: 100%;">
            <span class="vn-badge" style="margin-bottom: 0.75rem;">Server Ledger Notice</span>
            <div class="vn-err-numeral"><?= $errorCode ?></div>
            <h1 class="vn-err-title">Unexpected Server Complication</h1>
            <p class="vn-err-lead">
                An unforeseen complication interrupted the execution of your request. Our engineering guild has logged this anomaly for immediate audit.
            </p>

            <div class="vn-err-actions" style="margin-bottom: 1.5rem;">
                <a href="/" class="vn-err-btn vn-err-btn-primary">
                    Return to Main Hall →
                </a>
                <button type="button" onclick="window.location.reload()" class="vn-err-btn vn-err-btn-secondary">
                    ↻ Refresh Record
                </button>
            </div>

            <?php if (!empty($hints)): ?>
            <div class="vn-err-hints">
                <div style="font-weight: 700; margin-bottom: 0.5rem;">Troubleshooting Prescriptions:</div>
                <?php foreach ($hints as $hint): ?>
                <div class="vn-err-hint-item">
                    <div class="vn-err-hint-title"><?= htmlspecialchars($hint['title'] ?? 'Notice', ENT_QUOTES, 'UTF-8') ?></div>
                    <div class="vn-err-hint-action"><?= htmlspecialchars($hint['action'] ?? '', ENT_QUOTES, 'UTF-8') ?></div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <?php if (!empty($isDebug)): ?>
            <div class="vn-err-dbg">
                <div class="vn-err-dbg-header">
                    <span>Developer Audit Diagnostic</span>
                    <?php if (!empty($errorId)): ?>
                    <span style="font-family: monospace; font-size: 0.8rem;">REF: <?= htmlspecialchars($errorId, ENT_QUOTES, 'UTF-8') ?></span>
                    <?php endif; ?>
                </div>

                <div class="vn-err-dbg-row">
                    <span class="vn-err-dbg-label">Message:</span>
                    <span class="vn-err-dbg-val"><?= htmlspecialchars($message ?? 'Unknown exception', ENT_QUOTES, 'UTF-8') ?></span>
                </div>

                <?php if (!empty($file)): ?>
                <div class="vn-err-dbg-row">
                    <span class="vn-err-dbg-label">File:</span>
                    <span class="vn-err-dbg-val" style="color: var(--vn-brass);"><?= htmlspecialchars($file, ENT_QUOTES, 'UTF-8') ?><?= !empty($line) ? ':' . (int)$line : '' ?></span>
                </div>
                <?php endif; ?>

                <?php if (!empty($trace)): ?>
                <div style="font-weight: 700; margin-top: 1rem; margin-bottom: 0.5rem; font-size: 0.85rem; color: var(--vn-ink);">
                    Execution Call Stack:
                </div>
                <pre class="vn-err-dbg-trace"><?= htmlspecialchars($trace, ENT_QUOTES, 'UTF-8') ?></pre>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>
