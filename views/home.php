@layout('layouts/main')

@section('title', 'Welcome to Ace PHP MVC')

@section('style')
<style>
    :root {
        --primary: #6366f1;
        --primary-hover: #4f46e5;
        --surface: #1e293b;
        --border: #334155;
        --text-muted: #94a3b8;
    }
    .wrapper {
        min-height: 100vh;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        padding: 2.5rem 1.5rem;
        box-sizing: border-box;
    }
    .container {
        max-width: 1080px;
        margin: 0 auto;
        width: 100%;
    }
    .header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 3.5rem;
    }
    .logo-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.35rem 0.85rem;
        background: rgba(99, 102, 241, 0.15);
        border: 1px solid rgba(99, 102, 241, 0.3);
        border-radius: 9999px;
        font-size: 0.85rem;
        color: #818cf8;
        font-weight: 600;
    }
    .hero {
        text-align: center;
        margin-bottom: 4rem;
    }
    .hero h1 {
        font-size: 3rem;
        font-weight: 800;
        letter-spacing: -0.03em;
        line-height: 1.15;
        margin: 0 0 1rem;
        background: linear-gradient(135deg, #ffffff 0%, #cbd5e1 50%, #818cf8 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }
    .hero p {
        font-size: 1.15rem;
        color: var(--text-muted);
        max-width: 650px;
        margin: 0 auto 2rem;
        line-height: 1.6;
    }
    .meta-tags {
        display: inline-flex;
        gap: 1rem;
        justify-content: center;
        font-size: 0.85rem;
        color: var(--text-muted);
        background: var(--surface);
        padding: 0.5rem 1rem;
        border-radius: 0.5rem;
        border: 1px solid var(--border);
    }
    .grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 1.5rem;
        margin-bottom: 3.5rem;
    }
    .card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 1rem;
        padding: 1.75rem;
        transition: transform 0.2s, border-color 0.2s;
    }
    .card:hover {
        transform: translateY(-3px);
        border-color: var(--primary);
    }
    .card-icon {
        width: 2.5rem;
        height: 2.5rem;
        border-radius: 0.5rem;
        background: rgba(99, 102, 241, 0.15);
        color: #818cf8;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        margin-bottom: 1.25rem;
    }
    .card h3 {
        font-size: 1.15rem;
        margin: 0 0 0.5rem;
        color: #f1f5f9;
    }
    .card p {
        font-size: 0.925rem;
        color: var(--text-muted);
        line-height: 1.55;
        margin: 0;
    }
    .cli-box {
        background: #020617;
        border: 1px solid var(--border);
        border-radius: 1rem;
        padding: 1.75rem;
        margin-bottom: 3.5rem;
    }
    .cli-box h3 {
        margin: 0 0 1rem;
        font-size: 1.05rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .code-list {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 0.75rem;
        font-family: 'JetBrains Mono', monospace;
        font-size: 0.85rem;
    }
    .code-item {
        background: #0b1120;
        border: 1px solid #1e293b;
        padding: 0.65rem 0.85rem;
        border-radius: 0.5rem;
        color: #38bdf8;
    }
    .code-item span {
        color: #64748b;
        display: block;
        font-size: 0.75rem;
        font-family: 'Plus Jakarta Sans', sans-serif;
        margin-top: 0.25rem;
    }
    .footer {
        text-align: center;
        font-size: 0.85rem;
        color: #64748b;
        padding-top: 2rem;
        border-top: 1px solid #1e293b;
    }
</style>
@endsection

@section('content')
<div class="wrapper">
    <div class="container">
        <!-- Top Nav -->
        <div class="header">
            <div class="logo-badge">
                <span>⚡</span> Ace PHP MVC Framework
            </div>
            <div style="font-size: 0.85rem; color: #64748b;">
                Production Ready
            </div>
        </div>

        <!-- Hero Section -->
        <div class="hero">
            <h1>Built for Performance & Elegance.</h1>
            <p>
                A lightweight, modern PHP MVC framework inspired by Laravel. Fast request routing, active record database models, blade-like template engine, and powerful CLI tooling.
            </p>
            <div class="meta-tags">
                <span>PHP <?= e($phpVersion) ?></span>
                <span>•</span>
                <span>Framework v<?= e($version) ?></span>
                <span>•</span>
                <span>Front Controller: <code>public/index.php</code></span>
            </div>
        </div>

        <!-- Framework Architecture Cards -->
        <div class="grid">
            <div class="card">
                <div class="card-icon">⚡</div>
                <h3>Expressive Routing</h3>
                <p>Register clean GET, POST, or resource routes with parameters in <code>routes/web.php</code>.</p>
            </div>
            <div class="card">
                <div class="card-icon">🗄️</div>
                <h3>ActiveRecord & Migrations</h3>
                <p>Simple models and schema migrations located in <code>app/Models</code> and <code>migrations/</code>.</p>
            </div>
            <div class="card">
                <div class="card-icon">🎨</div>
                <h3>View Templates</h3>
                <p>Sections, layouts, and components with automatic HTML escaping inside <code>views/</code>.</p>
            </div>
            <div class="card">
                <div class="card-icon">🛡️</div>
                <h3>Built-in Security</h3>
                <p>Automatic CSRF protection, input sanitization, secure sessions, and hardened .htaccess rules.</p>
            </div>
        </div>

        <!-- CLI Cheat Sheet -->
        <div class="cli-box">
            <h3><span>💻</span> Ace CLI Tooling (like artisan)</h3>
            <div class="code-list">
                <div class="code-item">
                    php ace serve
                    <span>Start local dev server</span>
                </div>
                <div class="code-item">
                    php ace make:controller Post
                    <span>Create a Controller</span>
                </div>
                <div class="code-item">
                    php ace make:model Post
                    <span>Create an ActiveRecord Model</span>
                </div>
                <div class="code-item">
                    php ace make:migration create_posts
                    <span>Generate schema migration</span>
                </div>
                <div class="code-item">
                    php ace migrate
                    <span>Run pending migrations</span>
                </div>
                <div class="code-item">
                    php ace view:clear
                    <span>Wipe compiled view cache</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        Ace PHP MVC Framework • MIT License • Crafted for modern web artisans
    </div>
</div>
@endsection