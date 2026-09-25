<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - lost in space</title>
    <style>
        /* CSS resets & variables */
        :root {
            --bg-start: #080710;
            --bg-end: #0f172a;
            --text-main: #f8fafc;
            --text-secondary: #94a3b8;
            --primary: #10b981;
            --secondary: #14b8a6;
            --glass-bg: rgba(255, 255, 255, 0.03);
            --glass-border: rgba(255, 255, 255, 0.08);
            --shadow: 0 15px 35px rgba(0, 0, 0, 0.5);
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background: linear-gradient(135deg, var(--bg-start), var(--bg-end));
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-main);
            overflow: hidden;
            position: relative;
            padding: 1.5rem;
        }

        /* Ambient Glow & Grid */
        .ambient-glow {
            position: absolute;
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, rgba(16, 185, 129, 0.15) 0%, rgba(20, 184, 166, 0.08) 50%, transparent 100%);
            border-radius: 50%;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 0;
            pointer-events: none;
            filter: blur(80px);
        }

        .error-card {
            position: relative;
            z-index: 1;
            background: var(--glass-bg);
            border: 1px solid var(--glass-border);
            border-radius: 24px;
            padding: 3.5rem 2.5rem;
            max-width: 500px;
            width: 100%;
            text-align: center;
            box-shadow: var(--shadow);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            animation: fadeIn 0.8s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .visual-container {
            margin-bottom: 2rem;
            position: relative;
            display: inline-block;
            animation: float 6s ease-in-out infinite;
        }

        .visual-container svg {
            filter: drop-shadow(0 0 15px rgba(16, 185, 129, 0.3));
        }

        .error-code {
            font-size: 5.5rem;
            font-weight: 900;
            line-height: 1;
            letter-spacing: -0.05em;
            background: linear-gradient(180deg, #fff 0%, rgba(255, 255, 255, 0.4) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 0.5rem;
        }

        h1 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.75rem;
            color: var(--text-main);
            letter-spacing: -0.02em;
        }

        p {
            color: var(--text-secondary);
            font-size: 0.95rem;
            line-height: 1.6;
            margin-bottom: 2rem;
        }

        .actions {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn {
            padding: 0.8rem 1.5rem;
            border-radius: 12px;
            font-weight: 600;
            font-size: 0.9rem;
            text-decoration: none;
            cursor: pointer;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: #fff;
            border: none;
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(16, 185, 129, 0.5);
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

        /* Animations */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes float {
            0% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-12px) rotate(3deg); }
            100% { transform: translateY(0px) rotate(0deg); }
        }
    </style>
</head>
<body>
    <div class="ambient-glow"></div>
    <div class="error-card">
        <div class="visual-container">
            <svg width="120" height="120" viewBox="0 0 120 120" fill="none" xmlns="http://www.w3.org/2000/svg">
                <circle cx="60" cy="60" r="50" fill="url(#grad)" fill-opacity="0.1" stroke="url(#grad)" stroke-width="2.5" stroke-dasharray="8 4"/>
                <circle cx="60" cy="60" r="35" fill="url(#gradInner)" fill-opacity="0.15"/>
                <path d="M42 60C42 50.0589 50.0589 42 60 42C69.9411 42 78 50.0589 78 60" stroke="#10b981" stroke-width="3" stroke-linecap="round"/>
                <circle cx="60" cy="60" r="6" fill="#f8fafc"/>
                <defs>
                    <linearGradient id="grad" x1="10" y1="10" x2="110" y2="110" gradientUnits="userSpaceOnUse">
                        <stop stop-color="#10b981"/>
                        <stop offset="1" stop-color="#14b8a6"/>
                    </linearGradient>
                    <linearGradient id="gradInner" x1="25" y1="25" x2="95" y2="95" gradientUnits="userSpaceOnUse">
                        <stop stop-color="#34d399"/>
                        <stop offset="1" stop-color="#10b981"/>
                    </linearGradient>
                </defs>
            </svg>
        </div>
        <div class="error-code">404</div>
        <h1>Lost in Deep Space</h1>
        <p>The page you are looking for has drifted out of orbit or was never created. Let's get you back to safety.</p>
        <div class="button-group">
            <a href="<?= route('/') ?>" class="btn btn-primary">Go to Homepage</a>
            <button onclick="window.history.back()" class="btn btn-secondary">Go Back</button>
        </div>
    </div>
</body>
</html>
