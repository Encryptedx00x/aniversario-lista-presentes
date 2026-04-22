---
status: all_fixed
phase: 01-base-infraestrutura
iteration: 1
findings_in_scope: 6
fixed: 6
skipped: 0
date: 2026-04-22
---

# Phase 01: Code Review Fix Report

**Fixed at:** 2026-04-22
**Source review:** .planning/phases/01-base-infraestrutura/01-REVIEW.md
**Iteration:** 1

**Summary:**
- Findings in scope: 6
- Fixed: 6
- Skipped: 0

## Fixed Issues

### CR-01: Session cookie missing `Secure` flag

**File modified:** `app/Helpers/Session.php`
**Commit:** 4838d99
**Applied fix:** Added `ini_set('session.cookie_secure', getenv('APP_ENV') !== 'development' ? '1' : '0');` alongside the other ini_set calls in `Session::start()`, before `session_start()`. Gated by APP_ENV so local HTTP dev still works.

### CR-02: Logout route has no auth guard

**File modified:** `config/routes.php`
**Commit:** 9e4d888
**Applied fix:** Added `'auth'` as third element to the `POST /admin/logout` route entry so AuthMiddleware runs before the logout controller method.

### WR-01: `Upload::validateMime()` does not check that `finfo_open` succeeded

**File modified:** `app/Helpers/Upload.php`
**Commit:** 47f61c6
**Applied fix:** Added `if ($finfo === false) { return false; }` immediately after `finfo_open()`. Also added `is_string($mimeType) &&` guard before the `in_array` check to handle the case where `finfo_file()` itself returns false.

### WR-02: CSRF token not regenerated after validation

**File modified:** `app/Middleware/CsrfMiddleware.php`
**Commit:** 7d90650
**Applied fix:** Added `unset($_SESSION['csrf_token']);` at the end of `validate()` after successful validation, forcing a fresh token to be generated on the next `token()` call.

### WR-03: `$layout` value not whitelisted in `View::render()`

**File modified:** `app/Helpers/View.php`
**Commit:** 8a307cf
**Applied fix:** Replaced bare `$layout = $data['layout'] ?? 'main';` with a whitelist check against `['main', 'admin', 'admin_bare']`, defaulting to `'main'` if the value is not in the list.

### WR-04: `AdminAuthController::logout()` calls `session_destroy()` without clearing `$_SESSION`

**File modified:** `app/Controllers/AdminAuthController.php`
**Commit:** 4481bbe
**Applied fix:** Added `$_SESSION = [];` and `session_unset();` before `session_destroy()` in the `logout()` method, following the standard PHP safe-logout pattern.

## Skipped Issues

None — all findings were fixed.

---

_Fixed: 2026-04-22_
_Fixer: Claude (gsd-code-fixer)_
_Iteration: 1_
