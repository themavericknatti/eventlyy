<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>@yield('title', 'Ace - Creative Template')</title>
    @css
    @yield('style')
</head>
<body class="template-creative">
    <header class="template-header p-4 border-b">
        <div class="container mx-auto flex justify-between items-center">
            <h1 class="text-xl font-bold">Ace Storefront (Creative)</h1>
            <span class="text-sm text-gray-500">Active Template: @activeTemplate</span>
        </div>
    </header>
    <main class="container mx-auto py-8">
        @yield('content')
    </main>
    @toasts
    @yield('script')
</body>
</html>
