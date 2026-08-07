# AGENTS.md

## Stack

PHP 8.3+ / Laravel 13.8 / Tailwind CSS 4.0 / Vite 8.0 / MySQL 8.0 (Docker) / Redis

## Key Commands

```bash
# Full dev environment (artisan serve + queue + pail + vite)
composer dev

# Setup from scratch (install, key, migrate, build)
composer setup

# Tests (uses SQLite :memory:, no Docker needed)
composer test
# or directly:
php artisan test

# Docker full stack
./commands/run-mvp.sh
```

## Project Structure

- `app/Services/Scanner/SecurityScanner.php` — core scan logic (currently simulated)
- `app/Jobs/ProcessScan.php` — queued job dispatched on scan creation
- `app/Services/Payment/` and `app/Services/Observability/` — empty stubs
- `resources/views/` — Blade templates (auth, dashboard, scans, plans)
- `resources/css/app.css` — Tailwind 4 entry (uses `@import 'tailwindcss'`)
- `packages/` — empty, reserved for local packages

## Testing

- PHPUnit 12 with SQLite in-memory (`:memory:`), forced via `tests/TestCase.php::createApplication()` — no external DB needed
- `phpunit.xml` `<env DB_CONNECTION=sqlite>` alone is NOT enough: `php artisan test` (Collision) spawns a PHPUnit subprocess where the stale `$_SERVER['DB_CONNECTION']` inherited from the container env wins over Laravel's `env()` (it reads `$_SERVER` first). The `createApplication()` override sets `$_SERVER`/`$_ENV`/`putenv` before the test app boots
- `tests/Feature/DbConfigTest.php` guards against regression: asserts the suite runs on sqlite `:memory:`
- CRITICAL: never let tests hit the real MySQL `security_mvp` DB. The `RefreshDatabase` trait runs `migrate:fresh`, and past runs wiped all dev data (users/plans/scans) including real accounts. Keep the sqlite override intact
- Run single test: `php artisan test --filter=AuthTest`

## Conventions

- 4-space indentation, LF line endings, UTF-8 (`.editorconfig`)
- User model uses `#[Fillable]` and `#[Hidden]` PHP attributes (Laravel 13 style)
- Scans dispatched to `scans` queue: `ProcessScan::dispatch($scan)->onQueue('scans')`
  - Queue workers MUST listen on the `scans` queue: `php artisan queue:work --queue=scans,default` (or `queue:listen --queue=scans,default`). A worker on the default queue alone will never process scans
  - Docker: `docker/entrypoint.sh` runs the worker with a restart loop; `composer dev` listens with `--queue=scans,default`
- Frontend fonts loaded via `bunny()` from `laravel-vite-plugin`

## Gotchas

- `Subscription::plan()` requires the FK explicitly: `belongsTo(SubscriptionPlan::class, 'subscription_plan_id')` (column is not `plan_id`)
- `ScanController::show()` calls `$this->authorize('view', $scan)` but no `ScanPolicy` exists — will error at runtime
- Nginx config points `fastcgi_pass` to `app:8687` but Dockerfile exposes port 8000 — port mismatch if using Nginx
- `composer dev` requires `npx concurrently` (installed via npm devDependencies)
- Docker compose maps MySQL to host port 3307 (not default 3306)
- `.env.example` defaults to SQLite; Docker `.env` overrides to MySQL
