# Phase 1: Base & Infraestrutura - Pattern Map

**Mapped:** 2026-04-21
**Files analyzed:** 22 new files
**Analogs found:** 0 / 22 (greenfield — all patterns sourced from RESEARCH.md)

> This is a greenfield project. No existing codebase analogs exist. Every pattern
> below is the canonical target pattern that the planner MUST instruct the executor
> to implement exactly as shown. Source citations reference RESEARCH.md sections.

---

## File Classification

| New File | Role | Data Flow | Closest Analog | Match Quality |
|----------|------|-----------|----------------|---------------|
| `public/index.php` | config/bootstrap | request-response | none | no analog |
| `public/.htaccess` | config | request-response | none | no analog |
| `public/assets/css/app.css` | config | — | none | no analog |
| `public/assets/js/app.js` | utility | — | none | no analog |
| `app/autoload.php` | config/bootstrap | — | none | no analog |
| `app/Router.php` | utility | request-response | none | no analog |
| `config/app.php` | config | — | none | no analog |
| `config/database.php` | config | — | none | no analog |
| `config/routes.php` | config | request-response | none | no analog |
| `config.example.php` | config | — | none | no analog |
| `app/Models/BaseModel.php` | model | CRUD | none | no analog |
| `app/Models/UserModel.php` | model | CRUD | none | no analog |
| `app/Middleware/CsrfMiddleware.php` | middleware | request-response | none | no analog |
| `app/Middleware/AuthMiddleware.php` | middleware | request-response | none | no analog |
| `app/Helpers/View.php` | utility | request-response | none | no analog |
| `app/Helpers/Flash.php` | utility | request-response | none | no analog |
| `app/Helpers/Session.php` | utility | request-response | none | no analog |
| `app/Controllers/HomeController.php` | controller | request-response | none | no analog |
| `app/Controllers/AdminAuthController.php` | controller | request-response | none | no analog |
| `app/Controllers/ErrorController.php` | controller | request-response | none | no analog |
| `app/Views/layouts/main.php` | component | request-response | none | no analog |
| `app/Views/layouts/admin_bare.php` | component | request-response | none | no analog |
| `app/Views/public/home.php` | component | request-response | none | no analog |
| `app/Views/public/404.php` | component | request-response | none | no analog |
| `app/Views/public/403.php` | component | request-response | none | no analog |
| `app/Views/admin/login.php` | component | request-response | none | no analog |
| `app/Views/admin/dashboard.php` | component | request-response | none | no analog |
| `sql/schema.sql` | migration | CRUD | none | no analog |

---

## Pattern Assignments

### `public/index.php` (config/bootstrap, request-response)

**Pattern source:** RESEARCH.md — Pattern 1: Front Controller + Router

**Full file pattern:**
```php
<?php
declare(strict_types=1);

define('ROOT_PATH', dirname(__DIR__));
require ROOT_PATH . '/app/autoload.php';
require ROOT_PATH . '/config/app.php';

use App\Helpers\Session;

Session::start();

$uri    = strtok($_SERVER['REQUEST_URI'], '?');
$uri    = '/' . trim($uri, '/');
$method = $_SERVER['REQUEST_METHOD'];

$routes = require ROOT_PATH . '/config/routes.php';

App\Router::dispatch($uri, $method, $routes);
```

**Critical constraints:**
- `Session::start()` MUST be called before any output and before `CsrfMiddleware::token()` is ever invoked
- `ROOT_PATH` constant must be defined before autoloader so all path-resolution in other files uses it
- No output (echo, HTML, whitespace) before this block

---

### `public/.htaccess` (config, request-response)

**Pattern source:** RESEARCH.md — Pattern 7: .htaccess for Hostinger

**Full file pattern:**
```apache
Options -Indexes
RewriteEngine On

# Serve real files and directories directly (CSS, JS, images, uploads)
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d

# Route everything else to the front controller
RewriteRule ^(.*)$ index.php [QSA,L]
```

**Critical constraints:**
- No `RewriteBase` directive when `public/` is the Hostinger document root
- `Options -Indexes` prevents directory listing (security hardening)
- If installed in a subdirectory on another host, add `RewriteBase /subdirname/`

---

### `app/autoload.php` (config/bootstrap)

**Pattern source:** RESEARCH.md — Pattern 2: PSR-4-style Autoloader

**Full file pattern:**
```php
<?php
declare(strict_types=1);

spl_autoload_register(function (string $class): void {
    $base   = dirname(__DIR__) . '/app/';
    $prefix = 'App\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }
    $relative = substr($class, strlen($prefix));
    $file     = $base . str_replace('\\', '/', $relative) . '.php';
    if (file_exists($file)) {
        require $file;
    }
});

// Also autoload Router (lives directly in app/, not in a namespace subdir)
require dirname(__DIR__) . '/app/Router.php';
```

**Critical constraint:**
- Every class file under `app/` MUST declare `namespace App\{Subfolder};` exactly matching the directory path relative to `app/`. A mismatch causes `Class not found` fatal errors (RESEARCH.md Pitfall 4).

---

### `app/Router.php` (utility, request-response)

**Pattern source:** RESEARCH.md — Pattern 1: Front Controller + Router

**Full file pattern:**
```php
<?php
declare(strict_types=1);

use App\Middleware\{AuthMiddleware, CsrfMiddleware};
use App\Controllers\ErrorController;

class Router {
    public static function dispatch(string $uri, string $method, array $routes): void {
        $routeMap = $routes[$method] ?? [];

        if (!isset($routeMap[$uri])) {
            (new ErrorController)->notFound();
            return;
        }

        [$controllerName, $action, $middleware] = array_pad($routeMap[$uri], 3, null);

        // Auth guard — checked before CSRF so unauthenticated users are redirected cleanly
        if ($middleware === 'auth') {
            AuthMiddleware::handle();
        }

        // CSRF validation on every state-mutating request
        if ($method === 'POST') {
            CsrfMiddleware::validate();
        }

        $fqcn = "App\\Controllers\\{$controllerName}";
        $controller = new $fqcn();
        $controller->$action();
    }
}
```

**Note:** Router lives at `app/Router.php` without a namespace because `autoload.php` requires it directly. All controllers and middleware DO use namespaces.

---

### `config/routes.php` (config, request-response)

**Pattern source:** RESEARCH.md — Pattern 1: Front Controller + Router

**Full file pattern:**
```php
<?php
// Route table: METHOD => [URI => [ControllerClass, method, ?middleware]]
// Third element 'auth' triggers AuthMiddleware::handle() before controller runs.
return [
    'GET' => [
        '/'                => ['HomeController',      'index'],
        '/admin/login'     => ['AdminAuthController', 'loginForm'],
        '/admin/dashboard' => ['AdminAuthController', 'dashboard',  'auth'],
        '/admin/gifts'     => ['AdminAuthController', 'giftsStub',  'auth'],
    ],
    'POST' => [
        '/admin/login'     => ['AdminAuthController', 'loginPost'],
        '/admin/logout'    => ['AdminAuthController', 'logout'],
    ],
];
```

**Extension pattern (Phase 2+):** Add new routes by appending to the GET/POST arrays. Do NOT add routing logic here — keep it a pure data array.

---

### `config/app.php` (config)

**Pattern source:** RESEARCH.md — Pattern 9: Error Handling (SEC-07)

**Full file pattern:**
```php
<?php
declare(strict_types=1);

define('APP_NAME', 'Lista de Presentes');
define('APP_URL',  getenv('APP_URL') ?: 'http://localhost:8000');

$env = getenv('APP_ENV') ?: 'production';

if ($env === 'development') {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    error_reporting(0);
    ini_set('log_errors', '1');
    ini_set('error_log', ROOT_PATH . '/storage/logs/errors.log');
}
```

**Critical constraint (SEC-07):** `display_errors` MUST default to `0`. Stack traces with file paths and DB schema details must never reach the browser in production.

---

### `config/database.php` (config)

**Pattern source:** RESEARCH.md — Pattern 8: config.example.php (filled-in version)

**Full file pattern:**
```php
<?php
// NEVER commit this file — it contains real credentials.
// Copy config.example.php to this path and fill in values.
return [
    'host' => getenv('DB_HOST') ?: 'localhost',
    'name' => getenv('DB_NAME') ?: 'YOUR_DATABASE_NAME',
    'user' => getenv('DB_USER') ?: 'YOUR_DATABASE_USER',
    'pass' => getenv('DB_PASS') ?: 'YOUR_DATABASE_PASSWORD',
];
```

**Note:** `config/database.php` should be in `.gitignore`. `config.example.php` (root) is the committed template.

---

### `config.example.php` (config, DEPLOY-03)

**Pattern source:** RESEARCH.md — Pattern 8: config.example.php

**Full file pattern:**
```php
<?php
// config.example.php — Copy to config/database.php and replace placeholder values.
// This file IS committed to version control. config/database.php is NOT.
return [
    'host' => 'localhost',
    'name' => 'YOUR_DATABASE_NAME',
    'user' => 'YOUR_DATABASE_USER',
    'pass' => 'YOUR_DATABASE_PASSWORD',
];
```

---

### `app/Models/BaseModel.php` (model, CRUD)

**Pattern source:** RESEARCH.md — Pattern 3: PDO Base Model

**Full file pattern:**
```php
<?php
declare(strict_types=1);

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
                \PDO::ATTR_EMULATE_PREPARES   => false,   // SEC-01: prevent injection bypass
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

**Critical constraints (SEC-01):**
- `ATTR_EMULATE_PREPARES => false` MUST be set (see RESEARCH.md Pitfall 5 — silent security regression if omitted)
- `ATTR_ERRMODE_EXCEPTION` MUST be set so all DB errors throw catchable exceptions
- NEVER interpolate user data into SQL strings — always use named parameters via `$this->query($sql, ['param' => $value])`

---

### `app/Models/UserModel.php` (model, CRUD)

**Pattern source:** RESEARCH.md — BaseModel extension pattern

**Full file pattern:**
```php
<?php
declare(strict_types=1);

namespace App\Models;

class UserModel extends BaseModel {
    public function findByEmail(string $email): array|false {
        return $this->query(
            'SELECT id, name, email, password_hash, role FROM users WHERE email = :email LIMIT 1',
            [':email' => $email]
        )->fetch();
    }

    public function findById(int $id): array|false {
        return $this->query(
            'SELECT id, name, email, role FROM users WHERE id = :id LIMIT 1',
            [':id' => $id]
        )->fetch();
    }
}
```

**Pattern rules for ALL model methods:**
- Named parameters (`:param`) over positional (`?`) — more readable and less error-prone
- Return `array|false` from `->fetch()` — callers must check falsy return
- No business logic in models — data access only

---

### `app/Middleware/CsrfMiddleware.php` (middleware, request-response)

**Pattern source:** RESEARCH.md — Pattern 4: CSRF Middleware (SEC-02)

**Full file pattern:**
```php
<?php
declare(strict_types=1);

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

**Critical constraints (SEC-02):**
- `hash_equals()` MUST be used — not `===`. Timing-safe comparison prevents token-guessing attacks (see RESEARCH.md "Don't Hand-Roll").
- `bin2hex(random_bytes(32))` MUST be used for generation — not `uniqid()` or `mt_rand()`.
- Token is regenerated after login by unsetting `$_SESSION['csrf_token']` in `AdminAuthController::loginPost()`.
- `Session::start()` MUST have been called before any call to `token()` (RESEARCH.md Pitfall 3).

**Form inclusion pattern (in every POST form in Views):**
```html
<input type="hidden" name="csrf_token"
       value="<?= htmlspecialchars(\App\Middleware\CsrfMiddleware::token(), ENT_QUOTES, 'UTF-8') ?>">
```

---

### `app/Middleware/AuthMiddleware.php` (middleware, request-response)

**Pattern source:** RESEARCH.md — Pattern 5: Auth Middleware

**Full file pattern:**
```php
<?php
declare(strict_types=1);

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

**Critical constraint (SEC-06 / CLAUDE.md):**
- MUST check `$_SESSION['role'] === 'admin'` — checking only `$_SESSION['user_id']` is insufficient. A guest user could have a valid session without the admin role.

---

### `app/Helpers/Session.php` (utility, request-response)

**Pattern source:** RESEARCH.md — Pattern 6: Session::start() with Security Settings

**Full file pattern:**
```php
<?php
declare(strict_types=1);

namespace App\Helpers;

class Session {
    public static function start(): void {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return; // Already started — idempotent guard
        }
        ini_set('session.cookie_httponly', '1');
        ini_set('session.use_strict_mode', '1');
        ini_set('session.cookie_samesite', 'Lax');
        // In production with HTTPS, also set: ini_set('session.cookie_secure', '1');
        // Use project storage to avoid shared /tmp on some hosts (RESEARCH.md Open Question 2)
        session_save_path(ROOT_PATH . '/storage/sessions');
        session_start();
    }
}
```

**Critical constraint:** Called as the FIRST thing in `public/index.php`, before any output, before any call to `CsrfMiddleware::token()` (RESEARCH.md Pitfall 2 and Pitfall 3).

---

### `app/Helpers/View.php` (utility, request-response)

**Pattern source:** RESEARCH.md — Code Examples: View render helper

**Full file pattern:**
```php
<?php
declare(strict_types=1);

namespace App\Helpers;

class View {
    public static function render(string $template, array $data = []): void {
        // Expose $data keys as variables inside the template
        extract($data, EXTR_SKIP);
        // e() helper available in every template: <?= e($var) ?>
        $e      = fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
        $layout = $data['layout'] ?? 'main';
        ob_start();
        require dirname(__DIR__) . "/Views/{$template}.php";
        $content = ob_get_clean();
        require dirname(__DIR__) . "/Views/layouts/{$layout}.php";
    }
}
```

**Critical constraint (SEC-03):** The `$e()` closure is available in EVERY template via `extract()`. Templates MUST use `<?= $e($var) ?>` for all user-originated data. Direct `echo $var` is forbidden in views.

---

### `app/Helpers/Flash.php` (utility, request-response)

**Pattern source:** RESEARCH.md — Flash message pattern (used in AdminAuthController)

**Full file pattern:**
```php
<?php
declare(strict_types=1);

namespace App\Helpers;

class Flash {
    public static function set(string $type, string $message): void {
        $_SESSION['flash'] = ['type' => $type, 'msg' => $message];
    }

    public static function get(): ?array {
        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);
        return $flash;
    }

    public static function has(): bool {
        return isset($_SESSION['flash']);
    }
}
```

**Usage in layout:** `$flash = \App\Helpers\Flash::get();` — display once then discard.

---

### `app/Controllers/AdminAuthController.php` (controller, request-response)

**Pattern source:** RESEARCH.md — Code Examples: Admin login controller

**Full file pattern:**
```php
<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\UserModel;
use App\Helpers\{View, Flash};

class AdminAuthController {
    public function loginForm(): void {
        View::render('admin/login', ['title' => 'Admin Login', 'layout' => 'admin_bare']);
    }

    public function loginPost(): void {
        $email    = trim($_POST['email']    ?? '');
        $password =      $_POST['password'] ?? '';

        // SEC-06: validate inputs before DB query
        if ($email === '' || $password === '') {
            Flash::set('error', 'Preencha e-mail e senha.');
            header('Location: /admin/login');
            exit;
        }

        $user = (new UserModel)->findByEmail($email);

        // SEC-04: password_verify against stored hash
        if ($user && $user['role'] === 'admin' && password_verify($password, $user['password_hash'])) {
            session_regenerate_id(true);          // Prevent session fixation
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role']    = 'admin';
            $_SESSION['name']    = $user['name'];
            unset($_SESSION['csrf_token']);        // Regenerate CSRF token after privilege change
            header('Location: /admin/dashboard');
            exit;
        }

        // Generic error — do NOT reveal whether email or password was wrong
        Flash::set('error', 'Credenciais inválidas.');
        header('Location: /admin/login');
        exit;
    }

    public function dashboard(): void {
        // AuthMiddleware already verified session — safe to render
        View::render('admin/dashboard', ['title' => 'Dashboard', 'layout' => 'admin']);
    }

    public function giftsStub(): void {
        // Phase 2 stub — satisfies Success Criteria 4 (redirect without login)
        View::render('admin/dashboard', ['title' => 'Presentes', 'layout' => 'admin']);
    }

    public function logout(): void {
        session_destroy();
        header('Location: /admin/login');
        exit;
    }
}
```

**Security chain enforced here:**
1. `Session::start()` — already called in `index.php`
2. `CsrfMiddleware::validate()` — called by Router before `loginPost()`
3. `password_verify()` — SEC-04
4. `session_regenerate_id(true)` — prevent session fixation
5. `unset($_SESSION['csrf_token'])` — force new CSRF token post-login

---

### `app/Controllers/HomeController.php` (controller, request-response)

**Full file pattern:**
```php
<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\View;

class HomeController {
    public function index(): void {
        View::render('public/home', ['title' => APP_NAME, 'layout' => 'main']);
    }
}
```

---

### `app/Controllers/ErrorController.php` (controller, request-response)

**Full file pattern:**
```php
<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\View;

class ErrorController {
    public function notFound(): void {
        http_response_code(404);
        View::render('public/404', ['title' => '404 — Página não encontrada', 'layout' => 'main']);
    }

    public function forbidden(): void {
        http_response_code(403);
        View::render('public/403', ['title' => '403 — Acesso negado', 'layout' => 'main']);
    }
}
```

---

### `app/Views/layouts/main.php` (component, request-response)

**Pattern source:** RESEARCH.md — Bootstrap 5 CDN layout pattern

**Full file pattern:**
```php
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($title ?? APP_NAME, ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH"
          crossorigin="anonymous">
    <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container">
            <a class="navbar-brand" href="/"><?= htmlspecialchars(APP_NAME, ENT_QUOTES, 'UTF-8') ?></a>
        </div>
    </nav>
    <main class="container py-4">
        <?php
        $flash = \App\Helpers\Flash::get();
        if ($flash): ?>
            <div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : 'success' ?> alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($flash['msg'], ENT_QUOTES, 'UTF-8') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        <?= $content ?>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-YvpcrYf0tY3lHB60NNkmXc4s9bIOgUxi8T/jzmDGYiMj9gdA2Jk0Xt9rZpQB5qm"
            crossorigin="anonymous"></script>
    <script src="/assets/js/app.js"></script>
</body>
</html>
```

**Critical constraint (SEC-03):** `$content` is injected raw (it comes from `ob_get_clean()` on a trusted PHP template file). All user data inside templates MUST be escaped with `$e()` or `htmlspecialchars()` before that point.

---

### `app/Views/layouts/admin_bare.php` (component, request-response)

Minimal layout for the login page (no navbar, no admin sidebar — renders before session is confirmed).

**Full file pattern:**
```php
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($title ?? 'Admin', ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH"
          crossorigin="anonymous">
    <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body class="bg-light d-flex align-items-center" style="min-height:100vh">
    <div class="container" style="max-width:400px">
        <?php $flash = \App\Helpers\Flash::get(); if ($flash): ?>
            <div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : 'success' ?>">
                <?= htmlspecialchars($flash['msg'], ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>
        <?= $content ?>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-YvpcrYf0tY3lHB60NNkmXc4s9bIOgUxi8T/jzmDGYiMj9gdA2Jk0Xt9rZpQB5qm"
            crossorigin="anonymous"></script>
</body>
</html>
```

---

### `app/Views/admin/login.php` (component, request-response)

**Pattern source:** RESEARCH.md — CSRF hidden input pattern (SEC-02)

**Full file pattern:**
```php
<div class="card shadow-sm">
    <div class="card-body p-4">
        <h1 class="h4 mb-4 text-center">Acesso Admin</h1>
        <form method="POST" action="/admin/login" novalidate>
            <input type="hidden" name="csrf_token"
                   value="<?= htmlspecialchars(\App\Middleware\CsrfMiddleware::token(), ENT_QUOTES, 'UTF-8') ?>">
            <div class="mb-3">
                <label for="email" class="form-label">E-mail</label>
                <input type="email" class="form-control" id="email" name="email"
                       value="<?= $e($_POST['email'] ?? '') ?>" required autofocus>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Senha</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Entrar</button>
        </form>
    </div>
</div>
```

**Critical constraint (SEC-02):** The `csrf_token` hidden input MUST be present in every POST form. This is the canonical example to copy for all future forms.

---

### `app/Views/public/home.php` (component, request-response)

Minimal Phase 1 stub — Phase 2 replaces with gift listing.

```php
<div class="text-center py-5">
    <h1><?= $e(APP_NAME) ?></h1>
    <p class="lead">Lista de presentes em breve.</p>
</div>
```

---

### `app/Views/public/404.php` and `app/Views/public/403.php`

```php
<!-- 404.php -->
<div class="text-center py-5">
    <h1 class="display-4">404</h1>
    <p class="lead">Página não encontrada.</p>
    <a href="/" class="btn btn-primary">Voltar ao início</a>
</div>

<!-- 403.php -->
<div class="text-center py-5">
    <h1 class="display-4">403</h1>
    <p class="lead">Acesso negado.</p>
    <a href="/" class="btn btn-primary">Voltar ao início</a>
</div>
```

---

### `app/Views/admin/dashboard.php` (component — Phase 1 stub)

```php
<h1 class="h3 mb-4">Dashboard</h1>
<div class="alert alert-info">
    Painel admin — funcionalidades completas chegam na Fase 5.
</div>
<nav>
    <ul class="list-unstyled">
        <li><a href="/admin/gifts">Gerenciar Presentes</a></li>
    </ul>
</nav>
<form method="POST" action="/admin/logout">
    <input type="hidden" name="csrf_token"
           value="<?= htmlspecialchars(\App\Middleware\CsrfMiddleware::token(), ENT_QUOTES, 'UTF-8') ?>">
    <button type="submit" class="btn btn-outline-secondary btn-sm mt-3">Sair</button>
</form>
```

---

### `sql/schema.sql` (migration, CRUD)

**Pattern source:** RESEARCH.md — SQL schema stub

```sql
-- sql/schema.sql
-- Phase 1: users table only. All other tables added in Phases 2-5.
-- Compatible with MySQL 5.7 and 8.0.

CREATE TABLE IF NOT EXISTS users (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name          VARCHAR(120)  NOT NULL,
    email         VARCHAR(255)  NOT NULL UNIQUE,
    password_hash VARCHAR(255)  NOT NULL,
    role          ENUM('guest','admin') NOT NULL DEFAULT 'guest',
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

## Shared Patterns

### Authentication (SEC-04)
**Source:** RESEARCH.md — Pattern 5 + Code Examples: Admin login controller
**Apply to:** `AdminAuthController::loginPost()`, any future password-related controller
```php
// Store
$hash = password_hash($plainPassword, PASSWORD_DEFAULT);

// Verify
if (password_verify($plainPassword, $storedHash)) { /* success */ }

// Never: md5(), sha1(), or custom hash functions
```

### CSRF Token in Every POST Form (SEC-02)
**Source:** RESEARCH.md — Pattern 4: CSRF Middleware
**Apply to:** ALL view files containing `<form method="POST">`
```html
<input type="hidden" name="csrf_token"
       value="<?= htmlspecialchars(\App\Middleware\CsrfMiddleware::token(), ENT_QUOTES, 'UTF-8') ?>">
```

### Output Escaping (SEC-03)
**Source:** RESEARCH.md — View.php helper
**Apply to:** Every PHP `echo` / `<?= ?>` in any view template
```php
// Using the $e() closure provided by View::render() via extract():
<?= $e($userSuppliedVariable) ?>

// When $e() is not in scope (e.g., layouts):
<?= htmlspecialchars($var, ENT_QUOTES, 'UTF-8') ?>

// NEVER:
<?= $var ?>          // raw echo of user data
echo $_GET['x'];     // direct superglobal echo
```

### PDO Query Pattern (SEC-01)
**Source:** RESEARCH.md — Pattern 3: PDO Base Model
**Apply to:** ALL model methods in every phase
```php
// Correct — named parameters, prepared statement:
$this->query('SELECT * FROM gifts WHERE id = :id', [':id' => $id])->fetch();

// NEVER — string interpolation:
$this->db->query("SELECT * FROM gifts WHERE id = $id");
```

### Session Regeneration on Privilege Change
**Source:** RESEARCH.md — Code Examples: Admin login controller
**Apply to:** Any controller that elevates privileges (login, role change)
```php
session_regenerate_id(true);       // Must pass true to delete old session file
$_SESSION['role'] = 'admin';
unset($_SESSION['csrf_token']);     // Force CSRF token regeneration
```

### Error Display Suppression (SEC-07)
**Source:** RESEARCH.md — Pattern 9: Error Handling
**Apply to:** `config/app.php` (controls all PHP error output)
```php
// Production (default):
ini_set('display_errors', '0');
ini_set('log_errors', '1');
ini_set('error_log', ROOT_PATH . '/storage/logs/errors.log');

// Never expose $e->getMessage() to browser in production
```

---

## No Analog Found

All files in Phase 1 are new (greenfield). Patterns are sourced from RESEARCH.md which is itself sourced from PHP manual, OWASP documentation, and verified Hostinger hosting constraints.

| File | Role | Data Flow | Reason |
|------|------|-----------|--------|
| All 28 files | various | various | Greenfield project — first commit only initializes config/requirements files |

---

## Metadata

**Analog search scope:** Entire working directory (greenfield — only config files exist)
**Files scanned:** 0 source files (no application code yet)
**Pattern extraction date:** 2026-04-21
**Pattern sources:** RESEARCH.md (RESEARCH confidence: HIGH for all core PHP patterns)
**Valid until:** 2026-10-21 (stable PHP built-in APIs)
