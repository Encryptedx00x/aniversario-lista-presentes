# T03: 01-base-infraestrutura 03

**Slice:** S01 — **Milestone:** M001

## Description

Wire the front controller and implement all controllers: AdminAuthController (login/logout/dashboard), HomeController (public home), ErrorController (404/403/500), and the Upload security scaffold.

Purpose: Plans 01 and 02 built the skeleton and kernel. This plan activates them — requests can now be received, routed, authenticated, and responded to. Without public/index.php the application cannot start.
Output: 5 PHP files. After this plan, `php -S localhost:8000 -t public` serves the application.

## Must-Haves

- [ ] "public/index.php bootstraps the full request cycle: ROOT_PATH → autoload → config → Session::start() → dispatch"
- [ ] "Successful admin login sets $_SESSION['role'] = 'admin' and calls session_regenerate_id(true)"
- [ ] "Failed login does not reveal whether email or password was wrong"
- [ ] "Logout destroys session and redirects to /admin/login"
- [ ] "ErrorController sets correct HTTP status codes before rendering views"
- [ ] "Upload::validateMime() uses finfo_open(FILEINFO_MIME_TYPE) — never trusts $_FILES['type']"

## Files

- `public/index.php`
- `app/Controllers/AdminAuthController.php`
- `app/Controllers/HomeController.php`
- `app/Controllers/ErrorController.php`
- `app/Helpers/Upload.php`
