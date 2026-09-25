<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($code ?? 500) ?> - Server Error</title>
    <style>
        :root {
            --bg-start: #080710;
            --bg-end: #0f172a;
            --text-main: #f8fafc;
            --text-secondary: #94a3b8;
            --text-muted: #64748b;
            --primary: #f43f5e;
            --secondary: #fb923c;
            --glass-bg: rgba(255, 255, 255, 0.03);
            --glass-border: rgba(255, 255, 255, 0.08);
            --shadow: 0 20px 40px rgba(0, 0, 0, 0.6);
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            
            /* Debugger color scheme */
            --dbg-bg: rgba(15, 23, 42, 0.9);
            --dbg-border: rgba(244, 63, 94, 0.25);
            --dbg-red: #f87171;
            --dbg-accent: #38bdf8;
            --dbg-amber: #fbbf24;
            --dbg-green: #34d399;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background: linear-gradient(135deg, var(--bg-start), var(--bg-end));
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            overflow-x: hidden;
        }

        .error-card {
            background: var(--glass-bg);
            border: 1px solid var(--glass-border);
            border-radius: 1.5rem;
            padding: 3rem 2.5rem;
            max-width: 860px;
            width: 100%;
            text-align: center;
            box-shadow: var(--shadow);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            position: relative;
            animation: fadeIn 0.6s ease-out;
        }

        .glow {
            position: absolute;
            width: 350px;
            height: 350px;
            background: radial-gradient(circle, rgba(244, 63, 94, 0.12) 0%, rgba(251, 146, 60, 0.04) 50%, transparent 100%);
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            pointer-events: none;
            z-index: -1;
            filter: blur(50px);
        }

        .visual-container {
            margin-bottom: 1.5rem;
        }

        svg {
            filter: drop-shadow(0 0 15px rgba(244, 63, 94, 0.3));
            animation: pulse 3s ease-in-out infinite;
        }

        .error-code {
            font-size: 6.5rem;
            font-weight: 900;
            line-height: 1;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 0.75rem;
            letter-spacing: -2px;
        }

        h1 {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.75rem;
            letter-spacing: -0.5px;
        }

        .error-message {
            color: var(--text-secondary);
            font-size: 1.05rem;
            line-height: 1.6;
            margin-bottom: 1.5rem;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }

        /* Incident badge */
        .incident-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.12);
            padding: 0.4rem 0.9rem;
            border-radius: 999px;
            font-size: 0.82rem;
            color: var(--text-secondary);
            margin-bottom: 1.75rem;
            font-family: ui-monospace, monospace;
        }

        .incident-badge code {
            color: var(--dbg-accent);
            font-weight: 700;
        }

        /* Diagnostic hints */
        .hints-card {
            background: rgba(251, 146, 60, 0.06);
            border: 1px solid rgba(251, 146, 60, 0.2);
            border-radius: 1rem;
            padding: 1.25rem 1.5rem;
            text-align: left;
            margin: 1.5rem 0;
        }

        .hints-header {
            font-weight: 700;
            color: var(--dbg-amber);
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 0.75rem;
        }

        .hint-item {
            margin-top: 0.5rem;
            padding-top: 0.5rem;
            border-top: 1px dashed rgba(251, 146, 60, 0.15);
        }

        .hint-item:first-child {
            border-top: none;
            margin-top: 0;
            padding-top: 0;
        }

        .hint-title {
            font-weight: 600;
            font-size: 0.9rem;
            color: var(--text-main);
            margin-bottom: 0.25rem;
        }

        .hint-action {
            font-size: 0.85rem;
            color: var(--text-secondary);
            line-height: 1.5;
        }

        /* Debugger container styling */
        .error-details {
            background: var(--dbg-bg);
            border: 1px solid var(--dbg-border);
            border-radius: 1rem;
            padding: 1.75rem;
            text-align: left;
            margin-top: 1.5rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            width: 100%;
        }

        .details-header {
            font-weight: 700;
            color: var(--dbg-red);
            border-bottom: 1px solid rgba(244, 63, 94, 0.15);
            padding-bottom: 0.85rem;
            margin-bottom: 1.25rem;
            font-size: 1.05rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .details-row {
            margin-bottom: 0.85rem;
            display: flex;
            gap: 0.75rem;
            font-size: 0.92rem;
            line-height: 1.5;
            flex-wrap: wrap;
        }

        .details-label {
            color: var(--text-muted);
            font-weight: 600;
            min-width: 110px;
        }

        .details-value {
            color: var(--dbg-red);
            font-family: SFMono-Regular, Consolas, "Liberation Mono", Menlo, monospace;
            word-break: break-all;
            flex: 1;
        }
        
        .details-value.file-val {
            color: var(--dbg-accent);
        }

        .stack-trace-header {
            font-weight: 600;
            color: var(--text-main);
            margin-top: 1.5rem;
            margin-bottom: 0.75rem;
            font-size: 0.95rem;
        }

        pre {
            white-space: pre-wrap;
            font-family: SFMono-Regular, Consolas, "Liberation Mono", Menlo, monospace;
            font-size: 0.8rem;
            background: rgba(0, 0, 0, 0.45);
            padding: 1.25rem;
            border-radius: 0.75rem;
            color: #cbd5e1;
            max-height: 280px;
            overflow-y: auto;
            border: 1px solid rgba(255, 255, 255, 0.06);
            line-height: 1.6;
        }

        .button-group {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
            margin-top: 2rem;
        }

        .btn {
            font-size: 0.95rem;
            font-weight: 600;
            padding: 0.85rem 1.8rem;
            border-radius: 0.75rem;
            cursor: pointer;
            text-decoration: none;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: #fff;
            border: none;
            box-shadow: 0 4px 15px rgba(244, 63, 94, 0.25);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(244, 63, 94, 0.45);
        }

        .btn-secondary {
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid var(--glass-border);
            color: var(--text-main);
        }

        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.08);
            transform: translateY(-2px);
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(15px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes pulse {
            0% { transform: scale(1); filter: drop-shadow(0 0 15px rgba(244, 63, 94, 0.25)); }
            50% { transform: scale(1.04); filter: drop-shadow(0 0 25px rgba(244, 63, 94, 0.45)); }
            100% { transform: scale(1); filter: drop-shadow(0 0 15px rgba(244, 63, 94, 0.25)); }
        }
    </style>
</head>
<body>
    <div class="error-card">
        <div class="glow"></div>
        <div class="visual-container">
            <svg width="90" height="90" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M50 12L12 78H88L50 12Z" fill="url(#grad)" fill-opacity="0.1"/>
                <path d="M50 12L12 78H88L50 12Z" stroke="url(#grad)" stroke-width="3" stroke-linejoin="round"/>
                <path d="M50 36V62" stroke="#f43f5e" stroke-width="4" stroke-linecap="round"/>
                <circle cx="50" cy="72" r="3" fill="#f43f5e"/>
                <defs>
                    <linearGradient id="grad" x1="12" y1="12" x2="88" y2="78" gradientUnits="userSpaceOnUse">
                        <stop stop-color="#f43f5e"/>
                        <stop offset="1" stop-color="#fb923c"/>
                    </linearGradient>
                </defs>
            </svg>
        </div>
        <div class="error-code"><?= e($code ?? 500) ?></div>
        <h1>System Overload</h1>
        <div class="error-message">
            <?= !empty($isDebug) ? e($message ?? 'An unexpected engine failure occurred on our server.') : 'An internal server error occurred. Our technical team has been logged of this incident.' ?>
        </div>

        <?php if (!empty($errorId)): ?>
            <div class="incident-badge">
                <span>Incident Reference:</span>
                <code><?= e($errorId) ?></code>
            </div>
        <?php endif; ?>

        <?php if (!empty($hints) && is_array($hints)): ?>
            <div class="hints-card">
                <div class="hints-header">
                    <span>💡</span> Deployment Troubleshooting Hints
                </div>
                <?php foreach ($hints as $hint): ?>
                    <div class="hint-item">
                        <div class="hint-title"><?= e($hint['title']) ?></div>
                        <div class="hint-action"><?= e($hint['action']) ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($isDebug) && isset($exception)): ?>
            <div class="error-details">
                <div class="details-header">
                    <span>🐞 Framework Debugger</span>
                    <span style="font-size:0.75rem; background:rgba(244,63,94,0.15); color:var(--dbg-red); padding:0.2rem 0.6rem; border-radius:6px;">Debug Mode Active</span>
                </div>
                
                <div class="details-row">
                    <span class="details-label">Exception:</span>
                    <span class="details-value"><?= is_object($exception) ? e(get_class($exception)) : 'Error' ?></span>
                </div>
                
                <div class="details-row">
                    <span class="details-label">Message:</span>
                    <span class="details-value"><?= is_object($exception) && method_exists($exception, 'getMessage') ? e($exception->getMessage()) : e($message ?? 'Unknown error') ?></span>
                </div>

                <div class="details-row">
                    <span class="details-label">Location:</span>
                    <span class="details-value file-val"><?= e($file ?? (is_object($exception) && method_exists($exception, 'getFile') ? $exception->getFile() : '')) ?> : Line <?= e($line ?? (is_object($exception) && method_exists($exception, 'getLine') ? $exception->getLine() : '')) ?></span>
                </div>

                <div class="stack-trace-header">Stack Trace:</div>
                <pre><?= e($trace ?? (is_object($exception) && method_exists($exception, 'getTraceAsString') ? $exception->getTraceAsString() : '')) ?></pre>
            </div>
        <?php endif; ?>

        <div class="button-group">
            <a href="<?= route('/') ?>" class="btn btn-primary">Go to Homepage</a>
            <button onclick="location.reload()" class="btn btn-secondary">Retry Request</button>
        </div>
    </div>
</body>
</html>
