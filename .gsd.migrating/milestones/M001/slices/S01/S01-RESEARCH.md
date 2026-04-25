# Phase 1: Base & Infraestrutura - Research

**Researched:** 2026-04-21
**Domain:** PHP 8.1 MVC infrastructure, routing, session auth, CSRF, PDO, Hostinger shared hosting
**Confidence:** HIGH

---

## Summary

Phase 1 establishes the entire skeleton of the application: directory structure, front controller, router, PDO database layer, session-based auth with roles, CSRF middleware, layout templates, and all foundational security controls. Every subsequent phase builds on top of this foundation, so correctness and security here are critical.

The stack is well-understood: PHP 8.1+ with no Composer, no framework, pure OOP with namespaces autoloaded via a simple PSR-4-style `spl_autoload_register`. Apache mod_rewrite is confirmed available on Hostinger shared hosting. The MVC pattern without a framework requires hand-writing a router, a base controller, a base model (PDO wrapper), CSRF middleware, and an auth middleware — all of which have well-established, compact patterns in the PHP community.

**Primary recommendation:** Build a minimal but complete kernel in `app/` with explicit file-based autoloading, a URL-based router array, session-based CSRF (one token per session, regenerated on login), and PDO with `ERRMODE_EXCEPTION`. Keep every component under 100 lines. Do NOT abstract prematurely.

---

## Project Constraints (from CLAUDE.md)

- PHP 8.1+, MySQL, PDO, Bootstrap 5 via CDN
- Deploy on Hostinger shared hosting — no Node.js, no build step
- MVC leve customizado — sem Composer, sem framework externo
- Front controller: `public/index.php` + `.htaccess` com mod_rewrite
- PDO with prepared statements in ALL queries — never string interpolation
- CSRF token in ALL POST forms — validate in backend before processing
- `htmlspecialchars($var, ENT_QUOTES, 'UTF-8')` before any echo of user data
- `password_hash($pass, PASSWORD_DEFAULT)` to store, `password_verify()` to check
- Upload: validate real MIME (finfo), extension whitelist, max size, rename file
- Admin routes: check `$_SESSION['role'] === 'admin'` in middleware
- Namespace: `App\Controllers`, `App\Models`, `App\Middleware`, `App\Helpers`
- Bootstrap 5 for layout — no additional CSS frameworks
- Vanilla JS or minimal jQuery — no bundler

---

<phase_requirements>
## Phase Requirements

| ID | Description | Research Support |
|----|-------------|------------------|
| SEC-01 | All queries use PDO with prepared statements | PDO section — `prepare()`/`execute()` pattern with named parameters |
| SEC-02 | All forms have CSRF token validated in backend | CSRF Middleware section — `bin2hex(random_bytes(32))` + session storage |
| SEC-03 | All HTML output uses `htmlspecialchars` escape | Output Escaping section — `htmlspecialchars($v, ENT_QUOTES, 'UTF-8')` |
| SEC-04 | Passwords stored with `password_hash`, verified with `password_verify` | Auth section — `PASSWORD_DEFAULT` constant |
| SEC-05 | Upload validates MIME type, extension, and max size | Upload section — finfo + whitelist pattern (Phase 2 implementation, scaffold here) |
| SEC-06 | Complete backend validation on all endpoints | Validation section — validate before DB, return errors to view |
| SEC-07 | Internal errors not exposed to end user | Error handling section — `display_errors=0`, log to file |
| DEPLOY-03 | `config.example.php` with credential placeholders | Config section — template file pattern |
| DEPLOY-04 | `.htaccess` configured for front controller routing | mod_rewrite section — RewriteRule pattern for Hostinger |
</phase_requirements>

---

## Architectural Responsibility Map

| Capability | Primary Tier | Secondary Tier | Rationale |
|------------|-------------|----------------|-----------|
| URL routing | PHP (front controller) | — | All requests pass through `public/index.php`; router maps URI to controller |
| Session auth | PHP (backend) | Browser cookie | Session state lives server-side; cookie is opaque session ID only |
| CSRF protection | PHP middleware | HTML form hidden input | Token generated server-side, validated server-side before any mutation |
| DB access | PHP model layer | MySQL | Models own all PDO calls; views never touch DB |
| HTML rendering | PHP view templates | Bootstrap 5 CDN | Views are PHP files; styles loaded from CDN, no build step |
| Error display | PHP (error handler) | Storage log | Errors written to file, never shown to browser in production |
| Auth middleware | PHP middleware | — | Every admin route checks session role before controller executes |
| Password storage | PHP (password_hash) | MySQL (hash column) | PHP hashes before INSERT; raw password never reaches DB |

---

## Standard Stack

### Core
| Library | Version | Purpose | Why Standard |
|---------|---------|---------|--------------|
| PHP | 8.1+ | Server-side runtime | Required by CLAUDE.md; available on Hostinger |
| MySQL | 5.7+ / 8.0 | Relational database | Available on Hostinger cPanel |
| PDO + PDO_MySQL | built-in | DB abstraction + prepared statements | SEC-01 compliance; exception mode catches all errors |
| Bootstrap 5 | 5.3.x CDN | UI framework | Required by CLAUDE.md; no install needed |

### Supporting
| Library | Version | Purpose | When to Use |
|---------|---------|---------|-------------|
| `spl_autoload_register` | built-in | PSR-4-style autoloading without Composer | Phase 1 — eliminates manual `require` per class |
| `bin2hex(random_bytes(32))` | built-in | Cryptographically secure CSRF token | All POST forms |
| `finfo_open(FILEINFO_MIME_TYPE)` | built-in | Real MIME type detection for uploads | Phase 2 uploads; scaffold in Phase 1 helper |
| `password_hash` / `password_verify` | built-in | Secure password storage | Auth controller |
| `session_regenerate_id(true)` | built-in | Prevent session fixation after login | Auth middleware, on every successful login |

### Alternatives Considered
| Instead of | Could Use | Tradeoff |
|------------|-----------|----------|
| `spl_autoload_register` | Manual `require_once` per file | Manual requires are error-prone and hard to maintain; autoloader is one-time setup |
| Per-session CSRF token | Per-request token | Per-request is more secure but breaks browser back button; per-session is industry standard for non-AJAX forms |
| PHP native sessions | JWT | JWT requires signing library + stateless infra; sessions are simpler and appropriate for this scale |

**Installation:** No installation required — all PHP built-in functions. Bootstrap 5 via CDN link in layout template.

---

## Architecture Patterns

### System Architecture Diagram

```
Browser Request
      |
      v
[.htaccess mod_rewrite]
      | (all non-file requests)
      v
[public/index.php — Front Controller]
      |
      |-- require config/database.php  --> PDO connection
      |-- require app/autoload.php     --> spl_autoload_register
      |-- Session::start()             --> session security settings
      |
      v
[Router::dispatch($_SERVER['REQUEST_URI'])]
      |
      |-- match route to [Controller, method]
      |-- no match --> 404 controller
      |
      v
[Middleware chain]
      |
      |-- CsrfMiddleware (POST requests)  --> 403 if token missing/invalid
      |-- AuthMiddleware (admin routes)   --> redirect to /admin/login if no session
      |
      v
[Controller (e.g., AdminAuthController::loginPost())]
      |
      |-- calls Model methods (PDO prepared statements)
      |-- validates input
      |-- sets flash message
      |
      v
[View template (PHP file)]
      |
      |-- htmlspecialchars() on all output
      |-- CSRF hidden input on all POST forms
      |-- extends layouts/main.php (Bootstrap 5)
      |
      v
Browser Response (HTML)
```

### Recommended Project Structure
```
/app
  /Controllers
    AdminAuthController.php     # login/logout for admin
    HomeController.php          # public home
    ErrorController.php         # 404, 403
  /Models
    BaseModel.php               # PDO wrapper, prepare/execute helpers
    UserModel.php               # admin user queries
  /Views
    /layouts
      main.php                  # public layout (header + footer + nav)
      admin.php                 # admin layout
    /public
      home.php                  # public home view
      404.php
      403.php
    /admin
      login.php                 # admin login form
      dashboard.php             # stub for Phase 5
  /Middleware
    AuthMiddleware.php          # checks $_SESSION['role'] === 'admin'
    CsrfMiddleware.php          # generates/validates CSRF token
  /Helpers
    View.php                    # render($template, $data) helper
    Flash.php                   # $_SESSION['flash'] get/set
/config
  database.php                  # returns PDO instance
  app.php                       # app name, base URL, env flag
/public
  index.php                     # front controller
  .htaccess                     # mod_rewrite rules
  /assets/css/app.css           # minimal custom CSS
  /assets/js/app.js             # minimal JS stub
  /uploads/.gitkeep
/sql
  schema.sql                    # CREATE TABLE statements
/storage
  logs/.gitkeep
config.example.php              # DEPLOY-03: credential placeholder template
```

### Pattern 1: Front Controller + Router
**What:** Single entry point dispatches all requests to controller methods via a route table.
**When to use:** Always — this is the DEPLOY-04 requirement.

```php
// Source: standard PHP front controller pattern [ASSUMED - well-established]
// public/index.php
define('ROOT_PATH', dirname(__DIR__));
require ROOT_PATH . '/app/autoload.php';
require ROOT_PATH . '/config/app.php';

Session::start();

$uri = strtok($_SERVER['REQUEST_URI'], '?');
$uri = '/' . trim($uri, '/');
$method = $_SERVER['REQUEST_METHOD'];

$routes = require ROOT_PATH . '/config/routes.php';

Router::dispatch($uri, $method, $routes);
```

```php
// config/routes.php — route table (not a class — simple array)
return [
    'GET' => [
        '/'                => ['HomeController', 'index'],
        '/admin/login'     => ['AdminAuthController', 'loginForm'],
        '/admin/dashboard' => ['AdminAuthController', 'dashboard', 'auth'],
        '/admin/gifts'     => ['GiftAdminController', 'index', 'auth'],
    ],
    'POST' => [
        '/admin/login'     => ['AdminAuthController', 'loginPost'],
        '/admin/logout'    => ['AdminAuthController', 'logout'],
    ],
];
```

```php
// app/Router.php
class Router {
    public static function dispatch(string $uri, string $method, array $routes): void {
        $routeMap = $routes[$method] ?? [];
        if (!isset($routeMap[$uri])) {
            (new ErrorController)->notFound();
            return;
        }
        [$controllerName, $action, $middleware] = array_pad($routeMap[$uri], 3, null);
        if ($middleware === 'auth') {
            AuthMiddleware::handle();
        }
        if ($method === 'POST') {
            CsrfMiddleware::validate();
        }
        $controller = new ("App\\Controllers\\{$controllerName}")();
        $controller->$action();
    }
}
```

### Pattern 2: PSR-4-style Autoloader (no Composer)
**What:** Maps namespace to directory so `new App\Controllers\HomeController` auto-requires the file.
**When to use:** Once, in front controller bootstrap.

```php
// app/autoload.php
// Source: PHP manual spl_autoload_register [ASSUMED - standard PHP pattern]
spl_autoload_register(function (string $class): void {
    $base = dirname(__DIR__) . '/app/';
    $prefix = 'App\\';
    if (!str_starts_with($class, $prefix)) return;
    $relative = substr($class, strlen($prefix));
    $file = $base . str_replace('\\', '/', $relative) . '.php';
    if (file_exists($file)) require $file;
});
```

### Pattern 3: PDO Base Model
**What:** Centralized PDO connection with prepared statement helpers.
**When to use:** All Model classes extend this.

```php
// app/Models/BaseModel.php
// Source: php.net/manual PDO [ASSUMED - standard PDO pattern]
namespace App\Models;

class BaseModel {
    protected \PDO $db;

    public function __construct() {
        static $pdo = null;
        if ($pdo === null) {
            $cfg = require dirname(__DIR__, 2) . '/config/database.php';
            $dsn = "mysql:host={$cfg['host']};dbname={$cfg['name']};charset=utf8mb4";
            $pdo = new \PDO($dsn, $cfg['user'], $cfg['pass'], [
                \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                \PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        }
        $this->db = $pdo;
    }

    protected function query(string $sql, array $params = []): \PDOStatement {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }
}
```

### Pattern 4: CSRF Middleware
**What:** One per-session token; validates on every POST; returns 403 on failure.
**When to use:** Router calls `CsrfMiddleware::validate()` before any POST controller.

```php
// app/Middleware/CsrfMiddleware.php
// Source: OWASP CSRF Cheat Sheet [ASSUMED - standard pattern]
namespace App\Middleware;

class CsrfMiddleware {
    public static function token(): string {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function validate(): void {
        $token = $_POST['csrf_token'] ?? '';
        if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
            http_response_code(403);
            (new \App\Controllers\ErrorController)->forbidden();
            exit;
        }
    }
}
```

```html
<!-- In every POST form (layouts include this automatically) -->
<input type="hidden" name="csrf_token"
       value="<?= htmlspecialchars(\App\Middleware\CsrfMiddleware::token(), ENT_QUOTES, 'UTF-8') ?>">
```

### Pattern 5: Auth Middleware (role check)
**What:** Redirects to `/admin/login` if session role is not `admin`.
**When to use:** Router calls this for all routes tagged `'auth'`.

```php
// app/Middleware/AuthMiddleware.php
namespace App\Middleware;

class AuthMiddleware {
    public static function handle(): void {
        if (($_SESSION['role'] ?? '') !== 'admin') {
            header('Location: /admin/login');
            exit;
        }
    }
}
```

### Pattern 6: Session::start() with Security Settings
**What:** Centralises session startup with hardened INI settings.
**When to use:** Called once in `public/index.php` before any output.

```php
// app/Helpers/Session.php
// Source: php.net/manual/en/session.security.ini.php [ASSUMED]
namespace App\Helpers;

class Session {
    public static function start(): void {
        ini_set('session.cookie_httponly', '1');
        ini_set('session.use_strict_mode', '1');
        ini_set('session.cookie_samesite', 'Lax');
        // In production add: ini_set('session.cookie_secure', '1');
        session_start();
    }
}
```

### Pattern 7: .htaccess for Hostinger
**What:** Routes all non-file/non-directory requests to `public/index.php`.
**When to use:** Placed in `public/` (the document root on Hostinger).

```apache
# public/.htaccess
# Source: Apache mod_rewrite docs [VERIFIED: Hostinger support confirms mod_rewrite enabled]
Options -Indexes
RewriteEngine On

# If the request is for a real file or directory, serve it directly
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d

# Otherwise route to front controller
RewriteRule ^(.*)$ index.php [QSA,L]
```

Note: `RewriteBase` is NOT needed when `public/` is the document root and `RewriteRule` uses `index.php` (relative). If installed in a subdirectory, add `RewriteBase /subdirname/`. [ASSUMED - standard Apache behavior, verified by Hostinger support article]

### Pattern 8: config.example.php (DEPLOY-03)
```php
<?php
// config.example.php — copy to config/database.php and fill in values
return [
    'host' => 'localhost',
    'name' => 'YOUR_DATABASE_NAME',
    'user' => 'YOUR_DATABASE_USER',
    'pass' => 'YOUR_DATABASE_PASSWORD',
];
```

### Pattern 9: Error Handling (SEC-07)
```php
// config/app.php — included by front controller
$env = getenv('APP_ENV') ?: 'production';
if ($env === 'development') {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    error_reporting(0);
    ini_set('log_errors', '1');
    ini_set('error_log', dirname(__DIR__) . '/storage/logs/errors.log');
}
```

### Anti-Patterns to Avoid
- **String interpolation in SQL:** `"SELECT * FROM users WHERE id = $id"` — never. Always `prepare()`/`execute()`.
- **echo without escape:** `echo $_GET['name']` — always wrap with `htmlspecialchars`.
- **Storing plain passwords:** `INSERT INTO users SET password = '$pass'` — always `password_hash`.
- **Global PDO object:** Using `$GLOBALS['db']` — use static singleton in `BaseModel`.
- **Checking `$_SESSION['user']` alone for admin:** Must check `$_SESSION['role'] === 'admin'`.
- **Naked `die()` with error details:** `die($e->getMessage())` — never in production; log and show generic message.
- **RewriteBase in wrong place:** Adding `RewriteBase /` to a non-root install breaks routing silently.

---

## Don't Hand-Roll

| Problem | Don't Build | Use Instead | Why |
|---------|-------------|-------------|-----|
| Secure random token | Custom entropy collection | `bin2hex(random_bytes(32))` | Built-in CSPRNG; custom entropy is always weaker |
| Password hashing | MD5/SHA1/custom hash | `password_hash($p, PASSWORD_DEFAULT)` | bcrypt with auto-cost; SEC-04 requirement |
| SQL injection prevention | String escaping | PDO prepared statements | `addslashes` and `mysql_real_escape_string` miss edge cases |
| MIME type detection | Extension-only check | `finfo_open(FILEINFO_MIME_TYPE)` | Extensions are user-controlled; finfo reads magic bytes |
| CSRF timing attacks | `$token === $input` | `hash_equals($token, $input)` | Prevents timing-based token guessing |
| Session fixation | Manual session destroy | `session_regenerate_id(true)` on login | Built-in handles all edge cases |

**Key insight:** PHP's built-in security functions cover every attack vector this project faces. Custom implementations introduce bugs that the standard library already solved.

---

## Common Pitfalls

### Pitfall 1: `.htaccess` not applying on Hostinger
**What goes wrong:** Routes return 404 or the raw PHP source is served.
**Why it happens:** `AllowOverride None` in Apache config, or `public/` is not the document root.
**How to avoid:** Confirm document root points to `public/` in Hostinger cPanel. `mod_rewrite` IS enabled by default on Hostinger (verified by support article). [VERIFIED: Hostinger support article]
**Warning signs:** `/admin/login` returns 404 but `/?route=admin/login` works.

### Pitfall 2: Session data lost between requests
**What goes wrong:** Login succeeds but next page request sees no session.
**Why it happens:** `session_start()` called after headers sent (output before PHP start tag), or session save path is not writable on shared hosting.
**How to avoid:** Call `Session::start()` as the first thing in `index.php`, before any output. Add `session_save_path(ROOT_PATH . '/storage/sessions')` if default path is problematic.
**Warning signs:** `headers already sent by... on line 1` PHP notice.

### Pitfall 3: CSRF token mismatch after login
**What goes wrong:** Login POST returns 403 token invalid even though form has token.
**Why it happens:** Login form renders token, but `session_start()` hasn't been called yet when token is generated, OR the session was regenerated between form render and submit.
**How to avoid:** Always call `Session::start()` before calling `CsrfMiddleware::token()`. Per-session (not per-request) tokens avoid this class of bug.
**Warning signs:** Token in `$_POST` doesn't match `$_SESSION['csrf_token']`.

### Pitfall 4: Namespace/autoloader mismatch
**What goes wrong:** `Class 'App\Controllers\HomeController' not found` fatal error.
**Why it happens:** File is at `app/Controllers/HomeController.php` but namespace declared as `namespace Controllers;` (missing `App` prefix).
**How to avoid:** Every class file MUST have `namespace App\{subfolder};` exactly matching the directory path relative to `app/`.
**Warning signs:** Fatal `Class not found` immediately after adding a new controller.

### Pitfall 5: PDO emulated prepares enabled (SQL injection bypass)
**What goes wrong:** Prepared statements silently emulate on old drivers, potentially allowing injection.
**Why it happens:** Default `PDO::ATTR_EMULATE_PREPARES => true` on some MySQL driver versions.
**How to avoid:** Explicitly set `PDO::ATTR_EMULATE_PREPARES => false` in PDO constructor options. [ASSUMED - documented PHP pitfall]
**Warning signs:** No warning — this is a silent security regression.

### Pitfall 6: `display_errors` left on in production
**What goes wrong:** Stack traces with file paths and DB schema details shown to attackers.
**Why it happens:** Development config accidentally deployed, or `php.ini` default on some hosts.
**How to avoid:** `config/app.php` sets `display_errors=0` unless `APP_ENV=development`. [SEC-07]
**Warning signs:** PHP notices/warnings visible in browser on production.

---

## Code Examples

### View render helper with automatic escaping
```php
// app/Helpers/View.php
namespace App\Helpers;

class View {
    public static function render(string $template, array $data = []): void {
        // Make $data keys available as variables in the template
        extract($data);
        // Helper function for templates: <?= e($var) ?>
        $e = fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
        $layout = $data['layout'] ?? 'main';
        ob_start();
        require dirname(__DIR__) . "/Views/{$template}.php";
        $content = ob_get_clean();
        require dirname(__DIR__) . "/Views/layouts/{$layout}.php";
    }
}
```

### Admin login controller
```php
// app/Controllers/AdminAuthController.php
namespace App\Controllers;

use App\Models\UserModel;
use App\Helpers\{View, Session};

class AdminAuthController {
    public function loginForm(): void {
        View::render('admin/login', ['title' => 'Admin Login', 'layout' => 'admin_bare']);
    }

    public function loginPost(): void {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $user = (new UserModel)->findByEmail($email);

        if ($user && $user['role'] === 'admin' && password_verify($password, $user['password_hash'])) {
            session_regenerate_id(true);  // prevent session fixation
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role']    = 'admin';
            $_SESSION['name']    = $user['name'];
            // Regenerate CSRF token after privilege change
            unset($_SESSION['csrf_token']);
            header('Location: /admin/dashboard');
            exit;
        }
        // Generic error — don't reveal whether email or password was wrong
        $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Credenciais inválidas.'];
        header('Location: /admin/login');
        exit;
    }

    public function logout(): void {
        session_destroy();
        header('Location: /admin/login');
        exit;
    }
}
```

### SQL schema stub for Phase 1
```sql
-- sql/schema.sql (Phase 1 creates users table and settings table)
CREATE TABLE IF NOT EXISTS users (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name          VARCHAR(120) NOT NULL,
    email         VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role          ENUM('guest','admin') NOT NULL DEFAULT 'guest',
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

---

## State of the Art

| Old Approach | Current Approach | When Changed | Impact |
|--------------|------------------|--------------|--------|
| `mysql_*` functions | PDO or MySQLi | PHP 7.0 removed mysql_ | Project already uses PDO — no action |
| MD5/SHA1 passwords | `password_hash(PASSWORD_DEFAULT)` | PHP 5.5 (bcrypt) | Project already specifies password_hash |
| `rand()` for tokens | `random_bytes()` | PHP 7.0 | Project already specifies random_bytes |
| `ereg()` / `split()` | `preg_*` functions | PHP 7.0 | No impact — we use preg_ |
| Session token per-request | Session token per-session | N/A | Per-session is standard for non-SPA forms |

**Deprecated/outdated:**
- `mysql_real_escape_string`: removed in PHP 7 — PDO prepared statements are the answer.
- `mcrypt_*`: removed in PHP 7.2 — use `openssl_*` or `sodium_*` if crypto needed (not in scope for Phase 1).

---

## Assumptions Log

| # | Claim | Section | Risk if Wrong |
|---|-------|---------|---------------|
| A1 | mod_rewrite is enabled on Hostinger shared hosting | .htaccess Pattern | Routes break; workaround is PHP-based query string routing |
| A2 | `public/` is set as document root on Hostinger | .htaccess Pattern | `.htaccess` may need to be in web root instead |
| A3 | `PDO::ATTR_EMULATE_PREPARES => false` prevents all injection on MySQL driver | PDO BaseModel | Low risk — defense in depth still applies |
| A4 | `session.cookie_samesite=Lax` is sufficient without `Strict` for admin login | Session::start() | `Strict` would break OAuth flows, not relevant here; LOW risk |
| A5 | PHP 8.1 is available on Hostinger shared hosting | Entire Phase | If only 8.0 available, `str_starts_with` and named args still work; minimal impact |

---

## Open Questions

1. **Document root path on Hostinger**
   - What we know: Hostinger typically uses `public_html/` as document root for shared hosting
   - What's unclear: Does the project deploy with `public/` mapped directly to `public_html/`, or is the whole repo uploaded and `public_html/` aliased to `public/`?
   - Recommendation: Plan for both; add note in DEPLOY-03 `config.example.php` and in Phase 6 README

2. **Session save path permissions**
   - What we know: `/tmp` is default session path on shared hosting; other tenants may read it
   - What's unclear: Whether Hostinger isolates `/tmp` per account
   - Recommendation: Add `session_save_path(ROOT_PATH . '/storage/sessions')` in `Session::start()` and create `/storage/sessions/` directory

3. **MySQL version on Hostinger**
   - What we know: Hostinger provides MySQL 5.7 or 8.0 depending on plan
   - What's unclear: Which version the target account has
   - Recommendation: Write schema to be compatible with both (no MySQL 8-only features in Phase 1)

---

## Environment Availability

| Dependency | Required By | Available | Version | Fallback |
|------------|------------|-----------|---------|----------|
| PHP 8.1+ | Entire app | Unknown (dev machine) | — | PHP 8.0 acceptable with minimal changes |
| MySQL | Database layer | Unknown (dev machine) | — | SQLite for local dev only (schema must target MySQL) |
| Apache mod_rewrite | DEPLOY-04 | Confirmed on Hostinger | — | Query-string routing fallback |
| Bootstrap 5 CDN | UI layout | CDN (no install) | 5.3.x | Self-host CSS file if CDN unavailable |

**Missing dependencies with no fallback:**
- PHP 8.1+ and MySQL are required for production deployment; local dev can use XAMPP/Laragon/WAMP.

**Missing dependencies with fallback:**
- mod_rewrite: If disabled, the front controller can parse `?route=path` from `$_GET`; but Hostinger confirms mod_rewrite is on.

---

## Validation Architecture

### Test Framework
| Property | Value |
|----------|-------|
| Framework | None (manual browser testing + PHP built-in server) |
| Config file | none — Phase 1 success criteria are browser-verifiable |
| Quick run command | `php -S localhost:8000 -t public` |
| Full suite command | Manual checklist (see Success Criteria) |

### Phase Requirements → Test Map
| Req ID | Behavior | Test Type | Automated Command | File Exists? |
|--------|----------|-----------|-------------------|-------------|
| SEC-01 | All queries use PDO prepare/execute | code review | grep -r "prepare(" app/Models/ | ❌ Wave 0 |
| SEC-02 | CSRF token validated on POST | manual + smoke | curl -X POST /admin/login (no token) → 403 | ❌ Wave 0 |
| SEC-03 | htmlspecialchars on all output | code review | grep -r "echo \$" app/Views/ | ❌ Wave 0 |
| SEC-04 | password_hash used for storage | code review | grep "password_hash" app/Controllers/ | ❌ Wave 0 |
| SEC-05 | Upload scaffold in Helpers | code review | File exists check | ❌ Wave 0 |
| SEC-06 | Backend validation on login | manual | Submit empty login form → error shown | ❌ Wave 0 |
| SEC-07 | Errors not exposed to browser | manual | Trigger DB error → no stack trace | ❌ Wave 0 |
| DEPLOY-03 | config.example.php exists | file check | `test -f config.example.php` | ❌ Wave 0 |
| DEPLOY-04 | .htaccess routes to index.php | manual | `curl localhost:8000/admin/login` → 200 | ❌ Wave 0 |

### Sampling Rate
- **Per task commit:** `php -S localhost:8000 -t public` — manually hit `/` and `/admin/login`
- **Per wave merge:** Run all 5 success criteria from ROADMAP.md manually
- **Phase gate:** All 5 success criteria pass before advancing to Phase 2

### Wave 0 Gaps
- All test infrastructure is manual for Phase 1 (no test framework installed)
- Automated grep checks are built into task verification steps, not a framework

---

## Security Domain

### Applicable ASVS Categories

| ASVS Category | Applies | Standard Control |
|---------------|---------|-----------------|
| V2 Authentication | yes | `password_hash`/`password_verify`, session_regenerate_id |
| V3 Session Management | yes | `session.cookie_httponly`, `session.use_strict_mode`, `session.cookie_samesite` |
| V4 Access Control | yes | `$_SESSION['role'] === 'admin'` check in AuthMiddleware |
| V5 Input Validation | yes | `trim()`, `filter_var()` for email, type checks before DB |
| V6 Cryptography | no | No cryptography beyond password hashing (built-in) |

### Known Threat Patterns for PHP MVC

| Pattern | STRIDE | Standard Mitigation |
|---------|--------|---------------------|
| SQL injection | Tampering | PDO prepared statements, `ATTR_EMULATE_PREPARES => false` |
| XSS (reflected) | Tampering | `htmlspecialchars($v, ENT_QUOTES, 'UTF-8')` on all output |
| CSRF | Spoofing | `bin2hex(random_bytes(32))` token, `hash_equals()` validation |
| Session fixation | Elevation of privilege | `session_regenerate_id(true)` on successful login |
| Password exposure | Information disclosure | `password_hash(PASSWORD_DEFAULT)`, never log raw passwords |
| Path traversal | Tampering | Never use user input in file paths (Phase 2 concern, scaffold now) |
| Error information leakage | Information disclosure | `display_errors=0` in production, log to file |

---

## Sources

### Primary (HIGH confidence)
- [Hostinger Support — mod_rewrite enabled](https://support.hostinger.com/en/articles/1583636-is-mod_rewrite-enabled) — confirms mod_rewrite on shared hosting
- [OWASP CSRF Cheat Sheet](https://cheatsheetseries.owasp.org/cheatsheets/Cross-Site_Request_Forgery_Prevention_Cheat_Sheet.html) — CSRF token pattern
- [PHP Manual — Session Security INI](https://www.php.net/manual/en/session.security.ini.php) — `cookie_httponly`, `use_strict_mode`

### Secondary (MEDIUM confidence)
- [WebSearch: PHP CSRF token implementation 2024](https://dev.to/parzival_computer/csrf-protection-in-php-5g6p) — confirmed against OWASP
- [WebSearch: PHP 8.1 MVC front controller pattern](https://dev.to/mvinhas/simple-routing-system-for-a-php-mvc-application-16f7) — confirmed against standard pattern

### Tertiary (LOW confidence — training knowledge)
- `PDO::ATTR_EMULATE_PREPARES => false` best practice — [ASSUMED], consistent with community consensus
- `spl_autoload_register` PSR-4-style pattern — [ASSUMED], universally documented

---

## Metadata

**Confidence breakdown:**
- Standard stack: HIGH — all PHP built-in functions, no third-party libraries
- Architecture: HIGH — standard PHP front controller pattern, well-documented
- Security patterns: HIGH — OWASP-documented patterns
- Hostinger specifics: MEDIUM — confirmed mod_rewrite; document root setup depends on account config

**Research date:** 2026-04-21
**Valid until:** 2026-10-21 (stable domain — PHP core APIs don't change)