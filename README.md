# ⚡ Ace PHP MVC Framework (Starter Boilerplate)

A clean, modern, and lightweight PHP MVC framework inspired by Laravel. Built for rapid application development, security, and effortless production deployment.

---

## 🚀 Quick Start: Creating a New Project

To spin up a new project using this clean framework skeleton without any existing application code, run:

```bash
# Clone only the clean framework starter branch
git clone -b framework https://github.com/themavericknatti/eventlyy.git my-new-project

# Navigate into the project
cd my-new-project

# Copy environment file
cp .env.example .env

# Install dependencies
composer install

# Start development server
php ace serve
```

Your app will be live at `http://127.0.0.1:8000`!

---

## 📁 Directory Structure

```text
├── app/
│   ├── Controllers/       # Application Controllers
│   ├── Services/          # Domain Business Logic Layer
│   ├── Repositories/      # Data Access Abstraction Layer
│   ├── Requests/          # FormRequest Validation Classes
│   ├── Middlewares/       # Custom Middlewares
│   └── Models/            # ActiveRecord Models
├── config/
│   ├── app.php            # App configuration
│   ├── cors.php           # CORS settings (React / Next / Nuxt)
│   ├── database.php       # Database configuration
│   └── settings.json      # Dynamic settings
├── migrations/            # Database schema migrations
├── public/                # Web server DocumentRoot (Front Controller)
│   ├── assets/            # CSS, JavaScript, and Static Files
│   ├── uploads/           # User uploads
│   ├── .htaccess          # Production URL rewrites
│   └── index.php          # Single application entry point
├── routes/
│   ├── web.php            # Web HTTP routes (HTML views)
│   └── api.php            # API routes (/api prefix, CORS, API key auth)
├── src/                   # Core Ace Framework source code
├── storage/               # Logs and compiled view cache
├── .env.example           # Environment template
├── .htaccess              # Root Apache bridge & directory protection
├── ace                    # CLI command runner (like artisan)
└── composer.json          # Dependencies & autoloading
```

---

## 💻 Ace CLI Command Runner

Ace includes a built-in CLI tool to streamline development:

| Command | Description |
| :--- | :--- |
| `php ace serve` | Starts the built-in development server (default port 8000) |
| `php ace test` | Runs the full PHPUnit test suite |
| `php ace make:controller <Name>` | Generates a Controller in `app/Controllers/` |
| `php ace make:model <Name>` | Generates an ActiveRecord Model in `app/Models/` |
| `php ace make:service <Name>` | Generates a Service class in `app/Services/` |
| `php ace make:repository <Name>` | Generates a Repository in `app/Repositories/` |
| `php ace make:request <Name>` | Generates a FormRequest class in `app/Requests/` |
| `php ace make:layout <Name>` | Generates a layout template (`--framework=tailwind\|bootstrap`) |
| `php ace make:apikey <Name>` | Generates secure API key for frontends (React/Next/Nuxt) |
| `php ace apikey:list` | Lists all registered API keys |
| `php ace apikey:revoke <prefix>` | Deactivates an API key by prefix |
| `php ace make:middleware <Name>` | Generates a Middleware in `app/Middlewares/` |
| `php ace make:migration <name>` | Generates a timestamped migration in `migrations/` |
| `php ace migrate` | Runs all pending database migrations |
| `php ace migrate:rollback` | Rolls back the last migration batch |
| `php ace migrate:status` | Displays migration status |
| `php ace view:clear` | Wipes compiled template view cache |
| `php ace help` | Displays list of available commands |

---

## 🛠️ Routing (`routes/web.php`)

```php
use App\Controllers\HomeController;
use App\Controllers\UserController;

// Basic GET route
$router->get('/', [HomeController::class, 'index'])->name('home');

// Parameterized route
$router->get('/users/{id}', [UserController::class, 'show'])->name('users.show');

// POST request with middleware
$router->post('/users', [UserController::class, 'store']);
```

---

## 🎨 Views & Templates

Views are located in `views/` and support layouts, sections, components, and automatic escaping:

```php
@layout('layouts/main')

@section('title', 'Dashboard')

@section('content')
<div class="card">
    <h1>Welcome, <?= e($userName) ?></h1>
</div>
@endsection
```

### Useful Helper Functions
- `route('route.name')` - Generates clean URL path.
- `asset('css/style.css')` - Generates static asset URL path.
- `upload('avatars/pic.jpg')` - Generates user upload URL path.
- `csrf_field()` - Emits a secure CSRF token input tag.
- `redirect('/login')` - Safely redirects requests.
- `paginator($items, $total, $perPage)` - Creates a Paginator instance.
- `paginate_links($paginator, $options)` - Renders responsive pagination display.
- `css_framework()` - Renders the configured CSS framework CDN tags.
- `tailwind_cdn()` - Renders the official Tailwind CSS Play CDN script tag.
- `bootstrap_cdn()` - Renders the official Bootstrap 5.3 CDN tags.

---

## 🎨 Zero-NPM Custom CSS Frameworks (Tailwind CSS & Bootstrap 5)

Ace provides built-in support for **Tailwind CSS** and **Bootstrap 5** with **zero npm packages, zero Node.js setup, and zero build step required**. Simply use template directives or ready-made layouts:

### 1. Template Directives
```html
<head>
    <!-- Use Tailwind CSS instantly -->
    @tailwind

    <!-- OR use Bootstrap 5 instantly -->
    @bootstrap

    <!-- OR load the framework configured in your .env (CSS_FRAMEWORK=tailwind|bootstrap) -->
    @css
</head>
```

### 2. Ready-to-Use Layouts
```html
<!-- In any view: extend the Tailwind layout -->
@extends('layouts/tailwind')

<!-- OR extend the Bootstrap layout -->
@extends('layouts/bootstrap')
```

### 3. Generate Custom Layouts
```bash
# Generate a Tailwind layout
php ace make:layout admin --framework=tailwind

# Generate a Bootstrap layout
php ace make:layout admin --framework=bootstrap
```

---

## 📄 Responsive Pagination System

Ace includes an out-of-the-box responsive pagination system designed for all screen sizes (Desktop, Tablet, and Mobile).

### 1. Paginating Data in Controllers
```php
// Active Record Model pagination
$users = User::paginate(15);

// Query Builder pagination
$articles = Article::query()
    ->where('status', 'published')
    ->orderBy('created_at', 'DESC')
    ->paginate(10);

// Custom Array pagination
$paginator = paginator($customList, $totalCount, 10);

return view('users/index', ['users' => $users]);
```

### 2. Rendering in Views
```blade
<!-- Blade directive -->
@paginate($users)

<!-- Or method call -->
{!! $users->links() !!}
```

### 3. Responsive Screen Adaptability
- **Desktop (>= 768px)**: Displays full pagination controls, item count info (*"Showing 1 to 15 of 120 results"*), first/last shortcuts, and smart sliding window numbers with ellipses (`1 2 ... 5 6 7 ... 20`).
- **Tablet (481px - 768px)**: Balanced touch-friendly padding (40px touch targets) with centered wrapping.
- **Mobile (<= 480px)**: Automatically switches to a streamlined thumb-friendly Prev/Next bar with a page status pill (*"Page 2 of 8"*) to prevent clutter and horizontal overflow.

### 4. Customizing Classes, Styles & Themes
Developers can use the defaults or customize completely:

```blade
{{-- 1. Using Default Theme with Custom Accent Colors --}}
{!! $users->links([
    'styles' => [
        'primary' => '#10b981', // emerald green accent
        'bg'      => '#1e293b',
        'border'  => '#334155',
    ]
]) !!}

{{-- 2. Bootstrap 5 Theme --}}
{!! $users->links(['theme' => 'bootstrap', 'size' => 'sm']) !!}

{{-- 3. Tailwind CSS Theme --}}
{!! $users->links(['theme' => 'tailwind']) !!}

{{-- 4. Custom CSS Classes --}}
{!! $users->links([
    'with_styles' => false, // omit inline styles if using custom CSS
    'classes' => [
        'wrapper'   => 'my-pagination-box',
        'link'      => 'page-btn',
        'active'    => 'is-active',
        'disabled'  => 'is-disabled',
        'info'      => 'text-muted text-sm',
    ]
]) !!}
```

A standalone stylesheet is also available at `public/assets/css/pagination.css`.

---

## 🚢 Production Deployment

This framework follows standard Laravel architecture:
- **No `index.php` in the root folder**; all HTTP requests are directed through `public/index.php`.
- Fully compatible with **Whogohost Go52**, cPanel, DirectAdmin, Apache, and Nginx.
- Refer to [how-to-upload.md](how-to-upload.md) for detailed deployment steps.

---

## 📄 License

The Ace PHP MVC Framework is open-sourced software licensed under the [MIT license](LICENSE).
