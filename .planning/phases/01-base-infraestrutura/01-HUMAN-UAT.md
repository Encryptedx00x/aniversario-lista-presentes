---
status: partial
phase: 01-base-infraestrutura
source: [01-VERIFICATION.md]
started: 2026-04-22T00:00:00Z
updated: 2026-04-22T00:00:00Z
---

## Current Test

[awaiting human testing]

## Tests

### 1. Full request cycle renders correctly
expected: Start PHP built-in server (`php -S localhost:8000 -t public`), open http://localhost:8000 — Bootstrap 5 layout loads, public navbar shows "Presentes" and "Pix" links, hero section shows "Aniversário de Maria"
result: [pending]

### 2. Admin login flow end-to-end
expected: Seed the DB with an admin user via `sql/schema.sql`, navigate to http://localhost:8000/admin/login, submit correct credentials → redirected to /admin/dashboard with sidebar; submit wrong credentials → stays on login with "Credenciais inválidas." flash; visit /admin/logout (POST) → redirected to login page
result: [pending]

### 3. Hostinger .htaccess compatibility
expected: Deploy to Hostinger shared hosting with `public/` as document root, confirm Apache mod_rewrite correctly routes all non-file requests through `public/index.php` (no 404 on clean URLs)
result: [pending]

## Summary

total: 3
passed: 0
issues: 0
pending: 3
skipped: 0
blocked: 0

## Gaps
