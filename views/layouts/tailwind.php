<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-900 text-slate-100">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>@yield('title', 'Ace Application')</title>
    @tailwind
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'system-ui', 'sans-serif'],
                        mono: ['"JetBrains Mono"', 'monospace'],
                    },
                    colors: {
                        brand: {
                            50: '#eef2ff',
                            100: '#e0e7ff',
                            500: '#6366f1',
                            600: '#4f46e5',
                            700: '#4338ca',
                        }
                    }
                }
            }
        }
    </script>
    @yield('style')
</head>
<body class="min-h-full flex flex-col font-sans antialiased bg-slate-950 text-slate-100">

    <!-- Top Navigation -->
    <header class="border-b border-slate-800 bg-slate-900/60 backdrop-blur sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <!-- Brand / Logo -->
                <div class="flex items-center gap-3">
                    <a href="@url(route('home'))" class="flex items-center gap-2 text-indigo-400 font-bold text-lg hover:text-indigo-300 transition">
                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-indigo-600/20 text-indigo-400 border border-indigo-500/30">⚡</span>
                        <span><?= e(config('app.name', 'Ace')) ?></span>
                    </a>
                </div>

                <!-- Navigation Links -->
                <nav class="hidden md:flex items-center gap-6 text-sm font-medium text-slate-300">
                    <a href="@url(route('home'))" class="hover:text-white transition">Home</a>
                    @auth
                        <a href="/dashboard" class="hover:text-white transition">Dashboard</a>
                        <form method="POST" action="/logout" class="inline">
                            @csrf
                            <button type="submit" class="hover:text-red-400 transition">Logout</button>
                        </form>
                    @endauth
                    @guest
                        <a href="/login" class="text-slate-300 hover:text-white transition">Login</a>
                        <a href="/register" class="px-4 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white font-semibold transition">Get Started</a>
                    @endguest
                </nav>

                <!-- Mobile Menu Button -->
                <div class="md:hidden flex items-center">
                    <button type="button" onclick="document.getElementById('mobile-menu').classList.toggle('hidden')" class="p-2 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 focus:outline-none">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Menu Dropdown -->
        <div id="mobile-menu" class="hidden md:hidden border-t border-slate-800 bg-slate-900 px-4 pt-2 pb-4 space-y-1">
            <a href="@url(route('home'))" class="block px-3 py-2 rounded-md text-base font-medium text-slate-300 hover:bg-slate-800 hover:text-white">Home</a>
            @auth
                <a href="/dashboard" class="block px-3 py-2 rounded-md text-base font-medium text-slate-300 hover:bg-slate-800 hover:text-white">Dashboard</a>
            @endauth
            @guest
                <a href="/login" class="block px-3 py-2 rounded-md text-base font-medium text-slate-300 hover:bg-slate-800 hover:text-white">Login</a>
                <a href="/register" class="block px-3 py-2 rounded-md text-base font-medium text-indigo-400 hover:bg-slate-800">Register</a>
            @endguest
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-grow">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="border-t border-slate-800 py-6 text-center text-xs text-slate-500">
        <div class="max-w-7xl mx-auto px-4">
            <?= e(config('app.name', 'Ace')) ?> • Powered by Ace PHP MVC Framework (Tailwind CSS Edition)
        </div>
    </footer>

    <!-- Flash message toasts -->
    @toasts

    @yield('script')
</body>
</html>
