---
phase: 01-base-infraestrutura
plan: "02"
subsystem: security-kernel
tags: [pdo, csrf, auth, session, view, flash, security]
dependency_graph:
  requires: []
  provides:
    - BaseModel PDO singleton with prepared statements
    - UserModel with findByEmail/findById
    - CsrfMiddleware token generation and POST validation
    - AuthMiddleware admin role guard
    - Session hardened startup
    - View template renderer with $e() escape helper
    - Flash one-shot messaging
  affects:
    - Plan 03 controllers (consume all 7 classes)
tech_stack:
  added: []
  patterns:
    - PDO singleton with ATTR_EMULATE_PREPARES=false
    - hash_equals() CSRF timing-safe comparison
    - bin2hex(random_bytes(32)) CSPRNG token
    - extract(EXTR_SKIP) to prevent $e() override
    - Session hardening via ini_set before session_start()
key_files:
  created:
    - app/Models/BaseModel.php
    - app/Models/UserModel.php
    - app/Middleware/CsrfMiddleware.php
    - app/Middleware/AuthMiddleware.php
    - app/Helpers/Session.php
    - app/Helpers/View.php
    - app/Helpers/Flash.php
  modified: []
decisions:
  - Files match PATTERNS.md exactly — no architectural deviations required
metrics:
  duration: "~5 minutes"
  completed: "2026-04-22"
  tasks_completed: 2
  tasks_total: 2
  files_created: 7
  files_modified: 0
---

# Phase 01 Plan 02: Security Kernel — PDO Model Layer, CSRF, Auth, Session, View, Flash Summary

**One-liner:** PDO singleton with emulated-prepares disabled, timing-safe CSRF via hash_equals/random_bytes, admin-role AuthMiddleware, hardened session, and $e()-injecting View helper.

## Tasks Completed

| # | Task | Commit | Files |
|---|------|--------|-------|
| 1 | PDO model layer — BaseModel and UserModel | e6d9964 | app/Models/BaseModel.php, app/Models/UserModel.php |
| 2 | Security middleware and helpers — CSRF, Auth, Session, View, Flash | fbaba4c | app/Middleware/CsrfMiddleware.php, app/Middleware/AuthMiddleware.php, app/Helpers/Session.php, app/Helpers/View.php, app/Helpers/Flash.php |

## Files Created and Their Security Roles

### app/Models/BaseModel.php
PDO singleton providing the only DB access path. `ATTR_EMULATE_PREPARES => false` (SEC-01) ensures real prepared statements — prevents injection bypass via type-juggling. `ATTR_ERRMODE_EXCEPTION` makes all DB errors catchable. `query()` always calls `prepare()` then `execute()` — never raw `query()` or `exec()`.

### app/Models/UserModel.php
Extends BaseModel. Provides `findByEmail()` (used by AdminAuthController for login) and `findById()` (session validation). Named parameters `:email` / `:id` — no string interpolation. Return type `array|false` matches PDO fetch() contract.

### app/Middleware/CsrfMiddleware.php
`token()` generates `bin2hex(random_bytes(32))` — 256 bits of CSPRNG entropy. `validate()` uses `hash_equals()` for timing-safe comparison (SEC-02) — prevents timing oracle attacks. On failure: `http_response_code(403)` + `ErrorController->forbidden()` + `exit`.

### app/Middleware/AuthMiddleware.php
Checks `$_SESSION['role'] === 'admin'` exclusively (SEC-06). Checking only `user_id` would be insufficient — a guest user with a valid session would pass. Redirects to `/admin/login` + `exit` on failure.

### app/Helpers/Session.php
Idempotent — returns early if `PHP_SESSION_ACTIVE`. Sets `cookie_httponly=1` (XSS session theft prevention), `use_strict_mode=1` (session fixation protection), `cookie_samesite=Lax` (CSRF protection layer), and `session_save_path` to project-local storage (isolates from shared `/tmp`).

### app/Helpers/View.php
`extract($data, EXTR_SKIP)` — `EXTR_SKIP` prevents attacker-supplied data from overwriting `$e` (SEC-03). `$e` closure uses `htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8')` — the only safe output path in templates. Layout system via `ob_start()` / `ob_get_clean()`.

### app/Helpers/Flash.php
One-shot flash via `unset($_SESSION['flash'])` inside `get()` — no replay possible. `has()` allows layout to check without consuming. Used by AdminAuthController for login error/success messages.

## Security Confirmation

| Control | Implementation | Status |
|---------|----------------|--------|
| ATTR_EMULATE_PREPARES=false | BaseModel line 17 | CONFIRMED |
| ATTR_ERRMODE_EXCEPTION | BaseModel line 15 | CONFIRMED |
| hash_equals() for CSRF | CsrfMiddleware line 16 | CONFIRMED |
| bin2hex(random_bytes(32)) | CsrfMiddleware line 9 | CONFIRMED |
| $_SESSION['role'] === 'admin' check | AuthMiddleware line 8 | CONFIRMED |
| cookie_httponly | Session line 11 | CONFIRMED |
| use_strict_mode | Session line 12 | CONFIRMED |
| cookie_samesite=Lax | Session line 13 | CONFIRMED |
| session_save_path custom | Session line 14 | CONFIRMED |
| htmlspecialchars ENT_QUOTES UTF-8 | View line 9 | CONFIRMED |
| EXTR_SKIP prevents $e override | View line 8 | CONFIRMED |
| unset on flash get() | Flash line 13 | CONFIRMED |

## Deviations from Plan

None — plan executed exactly as written. All 7 files match PATTERNS.md specifications exactly.

## Known Stubs

None — this plan delivers infrastructure classes, not user-facing data. No stub values flow to UI rendering.

## Threat Flags

No new threat surface beyond what the plan's threat model documents. All 6 threat register entries (T-02-01 through T-02-06) are mitigated as specified.

## Self-Check: PASSED

Files confirmed present:
- app/Models/BaseModel.php — FOUND
- app/Models/UserModel.php — FOUND
- app/Middleware/CsrfMiddleware.php — FOUND
- app/Middleware/AuthMiddleware.php — FOUND
- app/Helpers/Session.php — FOUND
- app/Helpers/View.php — FOUND
- app/Helpers/Flash.php — FOUND

Commits confirmed:
- e6d9964 — FOUND
- fbaba4c — FOUND
