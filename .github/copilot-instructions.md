# Ace PHP MVC Framework — AI Coding Instructions
#
# This file instructs GitHub Copilot and similar AI assistants on how to
# generate code for this project. Read GUIDE.md for the full reference.

## Architecture: MVC + Service + Repository
- Controllers orchestrate only. No business logic, no DB queries.
- Services contain business logic. No DB queries, no rendering.
- Repositories contain all DB queries. No logic, no rendering.
- Models map to tables. Define fillable, hidden, rules, relationships.
- FormRequests validate input. Define rules(), authorize(), messages().
- Views use Blade-like syntax: {{ }}, @if, @foreach, @extends, @section.

## Mandatory Data Flow
Request → Route → Middleware → Controller → Validate → Service → Repository → Model → Response

## When creating a feature, ALWAYS generate:
1. Migration  2. Model  3. Repository  4. Service  5. FormRequest  6. Controller  7. Routes  8. Views

## API-First Development & Frontend Integration:
- Define API routes in `routes/api.php` (auto `/api` prefix, CORS enabled, CSRF bypassed).
- Frontends (React/Next/Nuxt) authenticate via API keys (`Authorization: Bearer <key>` or `X-Api-Key`).
- Generate keys: `php ace make:apikey "React App"`.
- Middleware: `Ace\Middlewares\CorsMiddleware`, `Ace\Middlewares\ApiKeyMiddleware`.

## Full Reference: GUIDE.md
