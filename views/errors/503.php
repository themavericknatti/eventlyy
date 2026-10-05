<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>503 - Under Maintenance</title>
    <style>
        :root {
            --bg-start: #080710;
            --bg-end: #0f172a;
            --text-main: #f8fafc;
            --text-secondary: #94a3b8;
            --primary: #f59e0b;
            --secondary: #d97706;
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
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
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

        .ambient-glow {
            position: absolute;
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, rgba(245, 158, 11, 0.15) 0%, rgba(217, 119, 6, 0.08) 50%, transparent 100%);
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
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border-radius: 1.5rem;
            padding: 3rem 2.5rem;
            max-width: 520px;
            width: 100%;
            text-align: center;
            box-shadow: var(--shadow);
            animation: fadeIn 0.8s ease-out;
        }

        .icon-container {
            margin-bottom: 1.5rem;
            animation: pulse 3s infinite ease-in-out;
        }

        .error-code {
            font-size: 3.5rem;
            font-weight: 800;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 0.5rem;
            letter-spacing: -0.05em;
        }

        h1 {
            font-size: 1.75rem;
            font-weight: 700;
            margin-bottom: 1rem;
            letter-spacing: -0.02em;
        }

        p {
            color: var(--text-secondary);
            font-size: 1rem;
            line-height: 1.6;
            margin-bottom: 2rem;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: #fff;
            text-decoration: none;
            font-weight: 600;
            padding: 0.85rem 1.75rem;
            border-radius: 0.75rem;
            transition: var(--transition);
            box-shadow: 0 4px 15px rgba(245, 158, 11, 0.25);
            border: none;
            cursor: pointer;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(245, 158, 11, 0.4);
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }
    </style>
</head>
<body>
    <div class="ambient-glow"></div>
    <div class="error-card">
        <div class="icon-container">
            <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="#f59e0b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"></path>
            </svg>
        </div>
        <div class="error-code">503</div>
        <h1>We'll Be Back Soon</h1>
        <p><?= htmlspecialchars($message ?? 'We are performing scheduled maintenance. Please check back shortly.', ENT_QUOTES, 'UTF-8') ?></p>
        <button onclick="location.reload()" class="btn">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21.5 2v6h-6M2.5 22v-6h6M2 11.5a10 10 0 0 1 18.8-4.3M22 12.5a10 10 0 0 1-18.8 4.2"/>
            </svg>
            Refresh Page
        </button>
    </div>
</body>
</html>
