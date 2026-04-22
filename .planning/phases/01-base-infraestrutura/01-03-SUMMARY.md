---
phase: 01-base-infraestrutura
plan: 03
subsystem: front-controller-and-controllers
tags: [php, mvc, auth, security, upload]
dependency_graph:
  requires: [01-01, 01-02]
  provides: [front-controller, admin-auth, error-handling, upload-scaffold]
  affects: [all-subsequent-plans]
tech_stack:
  added: []
  patterns: [front-controller, session-fixation-prevention, csrf-token-rotation, finfo-mime-validation]
key_files:
  created:
    - public/index.php
    - app/Controllers/AdminAuthController.php
    - app/Controllers/HomeController.php
    - app/Controllers/ErrorController.php
    - app/Helpers/Upload.php
  modified: []
decisions:
  - "giftsStub renders admin/stub view per D-05 (plan 04 will create the actual view)"
  - "ErrorController includes serverError() (500) beyond PATTERNS.md spec — required for completeness"
metrics:
  duration: "~10 minutes"
  completed: "2026-04-22"
  tasks_completed: 2
  files_created: 5
---

# Phase 01 Plan 03: Front Controller and Controllers Summary

Front controller activated and all core controllers implemented — PHP MVC request cycle now fully operational from `public/index.php` through to `AdminAuthController`, `HomeController`, and `ErrorController`. Upload security scaffold established for Phase 3+.

## Files Created

| File | Role |
|------|------|
| `public/index.php` | Front controller — bootstraps ROOT_PATH, autoload, config, Session::start(), Router::dispatch() |
| `app/Controllers/AdminAuthController.php` | Admin login/logout/dashboard with full security chain |
| `app/Controllers/HomeController.php` | Public home route handler |
| `app/Controllers/ErrorController.php` | HTTP 404/403/500 error handlers with correct status codes |
| `app/Helpers/Upload.php` | SEC-05 compliant MIME + extension validation scaffold |

## Security Chain Confirmed

### AdminAuthController::loginPost()
1. `CsrfMiddleware::validate()` — called by Router before this method runs
2. `(new UserModel)->findByEmail($email)` — PDO prepared statement, no SQL interpolation
3. `password_verify($password, $user['password_hash'])` — bcrypt verification (SEC-04)
4. `session_regenerate_id(true)` — deletes old session file, prevents session fixation (SEC-04)
5. `$_SESSION['role'] = 'admin'` — set from DB row only after verify succeeds AND role confirmed
6. `unset($_SESSION['csrf_token'])` — forces new CSRF token on next request post-login (SEC-02)
7. Generic error: `'Credenciais inválidas.'` — does not reveal which field was wrong (SEC-06)

### ErrorController HTTP Status Codes
- `notFound()`: `http_response_code(404)` called before `View::render()`
- `forbidden()`: `http_response_code(403)` called before `View::render()`
- `serverError()`: `http_response_code(500)` called before `View::render()`

### Upload::validateMime() Contract (for Phase 3+)
```php
// CORRECT usage in Phase 3+ upload handler:
$allowed = ['image/jpeg', 'image/png', 'image/webp'];
if (!Upload::validateMime($_FILES['photo']['tmp_name'], $allowed)) {
    // reject — MIME type not allowed
}
if (!Upload::validateExtension($_FILES['photo']['name'], ['jpg', 'jpeg', 'png', 'webp'])) {
    // reject — extension not in whitelist
}
// Both checks must pass. Extension alone is insufficient.
```
- Uses `finfo_open(FILEINFO_MIME_TYPE)` — reads magic bytes, not user-supplied `$_FILES['type']`
- `finfo_close($finfo)` called to avoid resource leak
- `in_array()` with `strict: true` on both methods

## Deviations from Plan

### Auto-added: ErrorController::serverError() method
- **Found during:** Task 2 planning review
- **Issue:** PATTERNS.md `ErrorController` pattern only showed `notFound()` and `forbidden()`. The plan's acceptance criteria and CONTEXT.md (D-08) call for 404/403/500 error pages, and the plan task spec explicitly includes `serverError()`.
- **Fix:** Added `serverError()` with `http_response_code(500)` and `View::render('public/500', ...)` to match the plan task spec exactly.
- **Rule:** Rule 2 — missing critical functionality
- **Files modified:** `app/Controllers/ErrorController.php`

### giftsStub renders admin/stub (not admin/dashboard)
- The plan task spec uses `admin/stub` view for `giftsStub()`. The PATTERNS.md pattern used `admin/dashboard` as a placeholder. Plan spec takes precedence — `admin/stub` view will be created in Plan 04.

## Known Stubs

| File | Stub | Reason |
|------|------|--------|
| `app/Controllers/AdminAuthController.php::giftsStub()` | Renders `admin/stub` view (does not exist yet) | View created in Plan 04 |

The stub does not prevent this plan's goal (front controller + auth controllers). Plan 04 will create `app/Views/admin/stub.php`.

## Self-Check: PASSED

All 5 files created and verified present. Both task commits confirmed in git log:
- `2006b4c`: feat(01-03): front controller and Upload security scaffold
- `cb8bbe5`: feat(01-03): AdminAuthController, HomeController, and ErrorController
