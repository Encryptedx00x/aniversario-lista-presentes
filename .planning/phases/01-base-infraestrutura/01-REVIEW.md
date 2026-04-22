---
phase: 01-base-infraestrutura
reviewed: 2026-04-22T00:00:00Z
depth: standard
files_reviewed: 29
files_reviewed_list:
  - public/.htaccess
  - public/index.php
  - app/autoload.php
  - app/Router.php
  - config/app.php
  - config/routes.php
  - config.example.php
  - sql/schema.sql
  - app/Models/BaseModel.php
  - app/Models/UserModel.php
  - app/Middleware/CsrfMiddleware.php
  - app/Middleware/AuthMiddleware.php
  - app/Helpers/Session.php
  - app/Helpers/View.php
  - app/Helpers/Flash.php
  - app/Controllers/AdminAuthController.php
  - app/Controllers/HomeController.php
  - app/Controllers/ErrorController.php
  - app/Helpers/Upload.php
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
findings:
  critical: 2
  warning: 4
  info: 3
  total: 9
status: issues_found
---

# Phase 01: Code Review Report

**Reviewed:** 2026-04-22T00:00:00Z
**Depth:** standard
**Files Reviewed:** 29
**Status:** issues_found

## Summary

Phase 1 delivers a clean MVC scaffold with strong security fundamentals: PDO with `ATTR_EMULATE_PREPARES=false`, CSRF validated via `hash_equals()`, `htmlspecialchars(ENT_QUOTES)` consistently applied via the `$e` closure, `password_verify()` for auth, `session_regenerate_id(true)` on login, and `finfo_open` for MIME validation. The overall quality is high.

Two critical issues require fixes before any further phase builds on this foundation: the session cookie is missing the `Secure` flag (transmits session IDs over HTTP in plaintext), and the logout route is CSRF-validated but it bypasses the auth middleware, meaning unauthenticated actors can trivially destroy any session if they learn its CSRF token. Four warnings cover missing error handling and logic gaps. Three info items address minor code quality points.

---

## Critical Issues

### CR-01: Session cookie missing `Secure` flag

**File:** `app/Helpers/Session.php:11-14`
**Issue:** `Session::start()` sets `cookie_httponly`, `use_strict_mode`, and `cookie_samesite` but does NOT set `session.cookie_secure`. On Hostinger shared hosting the public-facing site runs over HTTPS; without the `Secure` flag the session cookie can be transmitted over plain HTTP if the user is ever redirected via an HTTP link (e.g., an email link without `https://`). This allows the session ID to be intercepted in transit.
**Fix:**
```php
public static function start(): void {
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    ini_set('session.cookie_httponly', '1');
    ini_set('session.use_strict_mode', '1');
    ini_set('session.cookie_samesite', 'Lax');
    ini_set('session.cookie_secure', '1');   // <-- add this line
    session_save_path(ROOT_PATH . '/storage/sessions');
    session_start();
}
```
If local development uses plain HTTP, gate it: `ini_set('session.cookie_secure', getenv('APP_ENV') === 'production' ? '1' : '0');`

---

### CR-02: Logout route has no auth guard — unauthenticated actors can force-destroy sessions

**File:** `config/routes.php:12` and `app/Router.php:20-23`
**Issue:** The `POST /admin/logout` route carries no `'auth'` middleware key. The Router only applies `AuthMiddleware::handle()` when the third element equals `'auth'`. Any request — authenticated or not — that POSTs to `/admin/logout` with a valid CSRF token will call `session_destroy()`. Because CSRF tokens survive across sessions on shared hosting (they are stored in the session itself), this is a low-bar denial-of-service: an attacker who obtains a valid token (e.g., from a logged-in device they share) can log out an admin repeatedly. More importantly, the absence of the auth guard is an architectural inconsistency that will confuse future maintainers into omitting it on other sensitive routes.
**Fix:**
```php
// config/routes.php
'POST' => [
    '/admin/login'  => ['AdminAuthController', 'loginPost'],
    '/admin/logout' => ['AdminAuthController', 'logout', 'auth'],  // add 'auth'
],
```

---

## Warnings

### WR-01: `Upload::validateMime()` does not check that `finfo_open` succeeded

**File:** `app/Helpers/Upload.php:20-22`
**Issue:** `finfo_open(FILEINFO_MIME_TYPE)` can return `false` if the `fileinfo` extension is not available or misconfigured on the host. Passing `false` to `finfo_file()` raises a `TypeError` (PHP 8.1 strict types). The guard is a security gate — a runtime crash here could be caught somewhere that falls through to allowing the upload.
**Fix:**
```php
public static function validateMime(string $tmpPath, array $allowedMimes): bool {
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    if ($finfo === false) {
        return false; // fail closed — deny upload if finfo unavailable
    }
    $mimeType = finfo_file($finfo, $tmpPath);
    finfo_close($finfo);
    return is_string($mimeType) && in_array($mimeType, $allowedMimes, strict: true);
}
```

---

### WR-02: `CsrfMiddleware::validate()` does not regenerate the token after consumption

**File:** `app/Middleware/CsrfMiddleware.php:14-20`
**Issue:** After a POST is validated the CSRF token remains identical in the session. This means if a response is cached (browser back-button, proxy) and replayed the old token still passes. While `SameSite=Lax` mitigates cross-origin replays, best practice for state-mutating flows (login, logout) is to issue a new token after each successful validation. `AdminAuthController::loginPost()` correctly calls `unset($_SESSION['csrf_token'])` post-login, but that logic is in the controller rather than enforced uniformly in the middleware, and logout does not do the same.
**Fix:** In `CsrfMiddleware::validate()`, unset the token after a successful check so `token()` will regenerate it on the next render:
```php
public static function validate(): void {
    $token = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        http_response_code(403);
        (new \App\Controllers\ErrorController)->forbidden();
        exit;
    }
    unset($_SESSION['csrf_token']); // force fresh token for next form render
}
```

---

### WR-03: `View::render()` does not validate the `$layout` value before including it

**File:** `app/Helpers/View.php:10-14`
**Issue:** `$layout` is taken directly from `$data['layout']` (which comes from controller code), then used in `require ".../layouts/{$layout}.php"`. There is no sanitisation of `$layout`. If any future controller passes user-supplied data into the `layout` key — or a typo puts path-separator characters in it — this becomes a local file inclusion. Currently all call sites are controlled, but the pattern is fragile.
**Fix:** Validate `$layout` against a whitelist before using it:
```php
$allowed = ['main', 'admin', 'admin_bare'];
$layout  = in_array($data['layout'] ?? 'main', $allowed, true)
    ? ($data['layout'] ?? 'main')
    : 'main';
```

---

### WR-04: `AdminAuthController::logout()` calls `session_destroy()` without clearing `$_SESSION` first

**File:** `app/Controllers/AdminAuthController.php:61-64`
**Issue:** Calling `session_destroy()` alone marks the session file for deletion but leaves the `$_SESSION` superglobal populated for the remainder of the current request. If any code after `logout()` were to read session data before `exit`, it would see stale values. The standard PHP safe-logout pattern explicitly clears the array first.
**Fix:**
```php
public function logout(): void {
    $_SESSION = [];
    session_destroy();
    header('Location: /admin/login');
    exit;
}
```

---

## Info

### IN-01: `.htaccess` has no `X-Content-Type-Options` or security headers

**File:** `public/.htaccess:1-5`
**Issue:** The `.htaccess` only handles URL rewriting. It does not set `X-Content-Type-Options: nosniff`, `X-Frame-Options: DENY`, or `Referrer-Policy`. These are straightforward hardening headers available via `mod_headers` on Hostinger.
**Fix:** Add a `<IfModule mod_headers.c>` block:
```apache
<IfModule mod_headers.c>
    Header always set X-Content-Type-Options "nosniff"
    Header always set X-Frame-Options "DENY"
    Header always set Referrer-Policy "strict-origin-when-cross-origin"
</IfModule>
```

---

### IN-02: `BaseModel` singleton pattern uses `static $pdo` inside a constructor — obscure lifecycle

**File:** `app/Models/BaseModel.php:10-20`
**Issue:** The PDO singleton is stored as `static $pdo` inside the constructor. This works correctly but is unconventional — the variable persists across instances due to PHP static variable scoping inside a function, not because of a class-level static property. This makes the lifetime invisible to subclasses and future readers, and makes it impossible to mock the connection in tests.
**Fix (low priority):** Promote to a class-level static property for clarity:
```php
protected static ?\PDO $pdo = null;

public function __construct() {
    if (self::$pdo === null) {
        $cfg = require dirname(__DIR__, 2) . '/config/database.php';
        $dsn = "mysql:host={$cfg['host']};dbname={$cfg['name']};charset=utf8mb4";
        self::$pdo = new \PDO($dsn, $cfg['user'], $cfg['pass'], [...]);
    }
    $this->db = self::$pdo;
}
```

---

### IN-03: `app/Views/admin/login.php` re-populates `email` field from `$_POST` after a failed login

**File:** `app/Views/admin/login.php:10`
**Issue:** `value="<?= $e($_POST['email'] ?? '') ?>"` — the template reads directly from `$_POST`. The `$e` closure correctly escapes it, so there is no XSS. However, re-populating email after a failed admin login subtly aids credential-stuffing timing analysis (the attacker can confirm which email was tried). This is a minor UX/security trade-off common in admin panels. Consider removing the value repopulation for the admin login specifically (public login forms typically do keep it for UX).
**Fix:** Remove the `value` attribute, or pass it through flash state so it is cleared after one redirect:
```php
<input type="email" class="form-control" id="email" name="email" required autofocus>
```

---

_Reviewed: 2026-04-22T00:00:00Z_
_Reviewer: Claude (gsd-code-reviewer)_
_Depth: standard_
