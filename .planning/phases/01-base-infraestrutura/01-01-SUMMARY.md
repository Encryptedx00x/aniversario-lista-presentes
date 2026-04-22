---
phase: 01-base-infraestrutura
plan: 01
subsystem: infrastructure
tags: [htaccess, autoloader, router, sql, config, gitignore]
dependency_graph:
  requires: []
  provides:
    - mod_rewrite front controller routing (public/.htaccess)
    - PSR-4 autoloader without Composer (app/autoload.php)
    - URL dispatcher with auth and CSRF enforcement (app/Router.php)
    - static route table (config/routes.php)
    - error display suppression (config/app.php)
    - credential placeholder template (config.example.php)
    - users table schema (sql/schema.sql)
  affects:
    - All subsequent plans depend on autoloader and router being in place
tech_stack:
  added: []
  patterns:
    - PSR-4-style spl_autoload_register without Composer
    - Static front controller dispatch via Router::dispatch()
    - Apache mod_rewrite front controller on Hostinger shared hosting
    - InnoDB utf8mb4 idempotent schema (MySQL 5.7/8.0 compatible)
key_files:
  created:
    - public/.htaccess
    - public/assets/css/app.css
    - public/assets/js/app.js
    - public/uploads/.gitkeep
    - storage/logs/.gitkeep
    - storage/sessions/.gitkeep
    - config/app.php
    - config/database.php
    - config/routes.php
    - config.example.php
    - .gitignore
    - app/autoload.php
    - app/Router.php
    - sql/schema.sql
  modified: []
decisions:
  - "No RewriteBase directive in .htaccess — public/ is Hostinger document root"
  - "display_errors defaults to 0 (production path); development only with APP_ENV=development"
  - "Static route table in config/routes.php — no dynamic routing logic"
  - "Router requires Router.php directly (no namespace); all other classes use App\\ namespace"
  - "config/database.php excluded from git via .gitignore; config.example.php is the committed template"
metrics:
  duration: "~10 minutes"
  completed: "2026-04-22T04:26:13Z"
  tasks_completed: 2
  files_created: 14
---

# Phase 01 Plan 01: Directory Skeleton, Config, Autoloader, Router, SQL Schema

One-liner: Static infrastructure skeleton — Apache mod_rewrite front controller, PSR-4 autoloader, URL dispatcher with CSRF/auth enforcement, and idempotent MySQL users table.

## Tasks Completed

| Task | Name | Commit | Files |
|------|------|--------|-------|
| 1 | Directory skeleton, .htaccess, config files, config.example.php | 0e3c622 | 10 files |
| 2 | Autoloader, Router, and SQL schema | 9f9466f | 3 files |

## Files Created

### Task 1 (commit 0e3c622)
- `public/.htaccess` — mod_rewrite front controller, Options -Indexes, no RewriteBase (DEPLOY-04)
- `public/assets/css/app.css` — Phase 1 stub
- `public/assets/js/app.js` — Phase 1 stub
- `public/uploads/.gitkeep` — directory placeholder
- `storage/logs/.gitkeep` — directory placeholder
- `storage/sessions/.gitkeep` — directory placeholder
- `config/app.php` — display_errors=0 by default, dev mode via APP_ENV (SEC-07)
- `config/routes.php` — static route table, auth middleware flag on admin routes
- `config.example.php` — credential placeholder template (DEPLOY-03)
- `.gitignore` — excludes config/database.php, logs, sessions, uploads

Note: `config/database.php` was created locally but is correctly excluded from git by `.gitignore`.

### Task 2 (commit 9f9466f)
- `app/autoload.php` — PSR-4 spl_autoload_register mapping App\\ prefix to app/ directory
- `app/Router.php` — static dispatch: AuthMiddleware on 'auth' routes, CsrfMiddleware on POST
- `sql/schema.sql` — users table CREATE IF NOT EXISTS, InnoDB, utf8mb4, MySQL 5.7+ compatible

## Key Decisions

1. **No RewriteBase in .htaccess** — Hostinger uses `public/` as document root, so no subdirectory prefix is needed. Adding RewriteBase would break routing.

2. **display_errors=0 as production default** — `config/app.php` defaults to production (APP_ENV not set = production). Only sets display_errors=1 explicitly when APP_ENV=development.

3. **Static route table** — `config/routes.php` is a pure data array, no routing logic. Router::dispatch() in Router.php handles all dispatch logic.

4. **Router without namespace** — Router.php declares `class Router` without a namespace because autoload.php requires it directly via filesystem path. All other classes use `App\\` namespace.

5. **config/database.php gitignored** — The file exists locally for development use but is excluded from version control. config.example.php is the committed credential template.

## Threat Mitigations Applied

| Threat ID | Component | Mitigation |
|-----------|-----------|------------|
| T-01-01 | Router URI dispatch | URI matched against fixed route table only — no eval, no dynamic include |
| T-01-02 | config/app.php error display | display_errors=0 in production else branch (SEC-07) |
| T-01-03 | config/database.php | .gitignore excludes it; config.example.php is committed template |
| T-01-04 | public/.htaccess | Options -Indexes prevents directory listing |
| T-01-05 | config/routes.php auth flag | Router calls AuthMiddleware::handle() for every 'auth' tagged route |

## Deviations from Plan

None — plan executed exactly as written.

## Self-Check: PASSED

- [x] public/.htaccess exists and contains RewriteRule
- [x] config.example.php contains YOUR_DATABASE_NAME
- [x] config/app.php contains display_errors=0
- [x] app/autoload.php contains spl_autoload_register
- [x] app/Router.php contains CsrfMiddleware::validate and AuthMiddleware::handle
- [x] sql/schema.sql contains CREATE TABLE IF NOT EXISTS users with password_hash column
- [x] Commits 0e3c622 and 9f9466f confirmed in git log
