---
phase: 01-base-infraestrutura
plan: "04"
subsystem: views
tags: [views, layouts, templates, sql, bootstrap, csrf, xss]
dependency_graph:
  requires: [01-01, 01-02, 01-03]
  provides: [view-templates, complete-schema]
  affects: [all-future-plans-that-render-html]
tech_stack:
  added: [Bootstrap 5.3.3 CDN]
  patterns: [PHP output buffering layout, CSRF in forms, htmlspecialchars escaping, $e() closure]
key_files:
  created:
    - app/Views/layouts/main.php
    - app/Views/layouts/admin.php
    - app/Views/layouts/admin_bare.php
    - app/Views/public/home.php
    - app/Views/public/404.php
    - app/Views/public/403.php
    - app/Views/public/500.php
    - app/Views/admin/login.php
    - app/Views/admin/dashboard.php
    - app/Views/admin/stub.php
  modified:
    - sql/schema.sql
decisions:
  - "home.php uses 'Aniversário de Maria' as placeholder name — site owner replaces via config or direct edit in Phase 5"
  - "admin.php sidebar uses strpos on REQUEST_URI for active link highlighting — simple and correct for this routing structure"
  - "$content injected raw into layouts — trusted template output from ob_get_clean(), not user input"
  - "schema.sql replaced entirely with 5-table version — avoids ALTER TABLE in later phases (D-11)"
metrics:
  duration: "~10 minutes"
  completed: "2026-04-22"
  tasks_completed: 2
  files_changed: 11
---

# Phase 01 Plan 04: View Templates and Complete Schema Summary

**One-liner:** Bootstrap 5.3.3 layouts with CSRF-protected forms, escaped output, and full 5-table InnoDB schema replacing the single-table stub.

## Tasks Completed

| Task | Name | Commit | Files |
|------|------|--------|-------|
| 1 | Layouts and public views | b29ba76 | main.php, admin.php, admin_bare.php, home.php, 404.php, 403.php, 500.php |
| 2 | Admin views and schema | 594e4ad | login.php, dashboard.php, stub.php, schema.sql |

## View Files Created

### Layouts

| File | Layout | Used By |
|------|--------|---------|
| app/Views/layouts/main.php | Public pages | HomeController, ErrorController |
| app/Views/layouts/admin.php | Admin pages (with sidebar) | AdminAuthController dashboard/stubs |
| app/Views/layouts/admin_bare.php | Login page (no sidebar) | AdminAuthController loginForm |

### Public Views

| File | Description | Decision Addressed |
|------|-------------|-------------------|
| app/Views/public/home.php | Hero section with "Aniversário de Maria" | D-01 |
| app/Views/public/404.php | "Ir para home" + "Ver presentes" buttons | D-10 |
| app/Views/public/403.php | Exact text "Acesso negado. Token inválido ou sessão expirada." + Voltar | D-09 |
| app/Views/public/500.php | Generic error, no internals exposed | SEC-07 |

### Admin Views

| File | Description | Decision Addressed |
|------|-------------|-------------------|
| app/Views/admin/login.php | Login form with CSRF, $e() prefill | SEC-02, SEC-03 |
| app/Views/admin/dashboard.php | 4 stat cards at 0: Presentes, Disponíveis, Reservas, Contribuições Pix | D-04 |
| app/Views/admin/stub.php | Reusable stub with $e($section), "Funcionalidade disponível em breve" | D-05 |

## CSRF Confirmation

CSRF hidden input present in all POST forms:

1. **app/Views/admin/login.php** — login form POST to /admin/login
2. **app/Views/layouts/admin.php** — logout form POST to /admin/logout

Both use: `htmlspecialchars(\App\Middleware\CsrfMiddleware::token(), ENT_QUOTES, 'UTF-8')`

## SQL Schema: All 5 Tables

| Table | Key Columns | Notes |
|-------|-------------|-------|
| users | id, name, email, password_hash, role, created_at | Was already present — unchanged |
| gifts | id, name, description, price, image_path, quantity, reserved, is_active, sort_order | New |
| reservations | id, gift_id, guest_name, guest_email, token, status | FK to gifts ON DELETE CASCADE |
| contributions | id, guest_name, guest_email, amount, message, confirmed | New |
| settings | id, key_name, value, updated_at | key_name UNIQUE |

All tables: ENGINE=InnoDB, DEFAULT CHARSET=utf8mb4, COLLATE=utf8mb4_unicode_ci (D-13).
No DROP TABLE, no INSERT INTO, no TRUNCATE (D-12). All CREATE TABLE use IF NOT EXISTS (idempotent).

## Decision Map (D-01 through D-13)

| Decision | File | How Addressed |
|----------|------|---------------|
| D-01 | home.php | "Aniversário de Maria" hero — no "em breve" placeholder |
| D-02 | main.php | Bootstrap default blue (no custom color overrides) |
| D-03 | main.php | Nav links href="/gifts" (Presentes) and href="/pix" (Pix) |
| D-04 | dashboard.php | 4 stat cards all showing 0 |
| D-05 | admin.php, stub.php | Sidebar has Dashboard, Presentes, Reservas, Pix, Configurações; stub shows "disponível em breve" |
| D-06 | admin.php | Logout form top-right, POST with CSRF |
| D-07 | admin.php | $_SESSION['name'] in header with htmlspecialchars |
| D-08 | 404.php, 403.php, 500.php | Bootstrap layout for all error pages |
| D-09 | 403.php | Exact text "Acesso negado. Token inválido ou sessão expirada." + Voltar button |
| D-10 | 404.php | Both "Ir para home" (href="/") and "Ver presentes" (href="/gifts") |
| D-11 | schema.sql | All 5 tables in Phase 1 — no ALTER TABLE needed in later phases |
| D-12 | schema.sql | No INSERT INTO — seed data goes in sql/seed.sql |
| D-13 | schema.sql | All tables use InnoDB + utf8mb4_unicode_ci |

## Security (SEC) Compliance

| Requirement | Status | Details |
|-------------|--------|---------|
| SEC-01 (PDO prepared) | N/A | No DB queries in views |
| SEC-02 (CSRF in POST forms) | PASS | login.php and admin.php logout both have hidden csrf_token input |
| SEC-03 (htmlspecialchars) | PASS | All user/session data escaped; $e() used in templates, htmlspecialchars() in layouts |
| SEC-07 (no stack trace) | PASS | 500.php contains only generic message, no PHP variables |

## Deviations from Plan

None — plan executed exactly as written.

## Known Stubs

| File | Stub | Reason |
|------|------|--------|
| app/Views/public/home.php | "Aniversário de Maria" hardcoded name | Intentional placeholder per D-01; Phase 5 settings system will make this configurable |
| app/Views/admin/dashboard.php | All stats show hardcoded 0 | Intentional zero-state per D-04; Phase 5 will wire live counts from DB |

## Self-Check: PASSED

All created files verified:
- app/Views/layouts/main.php: EXISTS
- app/Views/layouts/admin.php: EXISTS
- app/Views/layouts/admin_bare.php: EXISTS
- app/Views/public/home.php: EXISTS
- app/Views/public/404.php: EXISTS
- app/Views/public/403.php: EXISTS
- app/Views/public/500.php: EXISTS
- app/Views/admin/login.php: EXISTS
- app/Views/admin/dashboard.php: EXISTS
- app/Views/admin/stub.php: EXISTS
- sql/schema.sql: UPDATED (5 tables)

Commits verified:
- b29ba76: Task 1 — layouts and public views
- 594e4ad: Task 2 — admin views and schema
