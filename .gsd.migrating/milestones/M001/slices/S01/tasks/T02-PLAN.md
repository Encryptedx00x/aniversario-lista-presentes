# T02: 01-base-infraestrutura 02

**Slice:** S01 — **Milestone:** M001

## Description

Create the security kernel: PDO model layer, CSRF middleware, auth middleware, and the three helpers (Session, View, Flash). No controllers or views yet — this plan delivers the contracts that Plan 03 controllers will call.

Purpose: Controllers in Plan 03 are correct by construction only if the middleware and model layer they call are built to spec. Building them in a parallel wave ensures Plan 03 has no ambiguity about what these classes do.
Output: 7 PHP class files. No user-facing routes yet.

## Must-Haves

- [ ] "All DB queries go through BaseModel::query() which always calls prepare() + execute()"
- [ ] "CSRF token uses bin2hex(random_bytes(32)) and is validated with hash_equals()"
- [ ] "AuthMiddleware redirects to /admin/login when $_SESSION['role'] is not 'admin'"
- [ ] "Session::start() sets cookie_httponly, use_strict_mode, and samesite=Lax"
- [ ] "View::render() makes $e() closure available in every template for safe output escaping"

## Files

- `app/Models/BaseModel.php`
- `app/Models/UserModel.php`
- `app/Middleware/CsrfMiddleware.php`
- `app/Middleware/AuthMiddleware.php`
- `app/Helpers/Session.php`
- `app/Helpers/View.php`
- `app/Helpers/Flash.php`
