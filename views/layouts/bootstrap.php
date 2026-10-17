<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>@yield('title', 'Ace Application')</title>
    @bootstrap
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        main {
            flex: 1 0 auto;
        }
    </style>
    @yield('style')
</head>
<body class="bg-dark text-light">

    <!-- Top Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-body-tertiary border-bottom border-secondary-subtle sticky-top">
        <div class="container">
            <a class="navbar-brand fw-bold text-primary" href="@url(route('home'))">
                ⚡ <?= e(config('app.name', 'Ace')) ?>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain" aria-controls="navbarMain" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarMain">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link" href="@url(route('home'))">Home</a>
                    </li>
                    @auth
                    <li class="nav-item">
                        <a class="nav-link" href="/dashboard">Dashboard</a>
                    </li>
                    @endauth
                </ul>
                <div class="d-flex align-items-center gap-2">
                    @auth
                    <form method="POST" action="/logout" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-outline-danger btn-sm">Logout</button>
                    </form>
                    @endauth
                    @guest
                    <a href="/login" class="btn btn-outline-light btn-sm">Login</a>
                    <a href="/register" class="btn btn-primary btn-sm">Register</a>
                    @endguest
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="py-4">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="border-top border-secondary-subtle py-4 text-center text-secondary small">
        <div class="container">
            <?= e(config('app.name', 'Ace')) ?> • Powered by Ace PHP MVC Framework (Bootstrap 5 Edition)
        </div>
    </footer>

    <!-- Flash message toasts -->
    @toasts

    @yield('script')
</body>
</html>
