# S01: Base Infraestrutura

**Goal:** Build the static skeleton: directory structure, Apache routing, autoloader, router, route table, config files, SQL schema, and the credential placeholder template.
**Demo:** Build the static skeleton: directory structure, Apache routing, autoloader, router, route table, config files, SQL schema, and the credential placeholder template.

## Must-Haves


## Tasks

- [x] **T01: 01-base-infraestrutura 01**
  - Build the static skeleton: directory structure, Apache routing, autoloader, router, route table, config files, SQL schema, and the credential placeholder template.

Purpose: Everything in Waves 2 and 3 depends on this skeleton being in place. No application code runs without the front controller entry point, autoloader, and router.
Output: 14 files. No PHP class yet executes — only the infrastructure that makes class loading possible.
- [x] **T02: 01-base-infraestrutura 02**
  - Create the security kernel: PDO model layer, CSRF middleware, auth middleware, and the three helpers (Session, View, Flash). No controllers or views yet — this plan delivers the contracts that Plan 03 controllers will call.

Purpose: Controllers in Plan 03 are correct by construction only if the middleware and model layer they call are built to spec. Building them in a parallel wave ensures Plan 03 has no ambiguity about what these classes do.
Output: 7 PHP class files. No user-facing routes yet.
- [x] **T03: 01-base-infraestrutura 03**
  - Wire the front controller and implement all controllers: AdminAuthController (login/logout/dashboard), HomeController (public home), ErrorController (404/403/500), and the Upload security scaffold.

Purpose: Plans 01 and 02 built the skeleton and kernel. This plan activates them — requests can now be received, routed, authenticated, and responded to. Without public/index.php the application cannot start.
Output: 5 PHP files. After this plan, `php -S localhost:8000 -t public` serves the application.
- [x] **T04: 01-base-infraestrutura 04**
  - Create all view files (layouts, public pages, admin pages) and extend sql/schema.sql with the full table set.

Purpose: Plan 03 controllers call View::render() but the templates do not exist yet — this plan creates them. The schema extension (D-11) prevents structural ALTER TABLE in later phases.
Output: 10 view files + 1 updated SQL file. After this plan, the full request cycle renders HTML to the browser.

## Files Likely Touched

- `public/.htaccess`
- `public/assets/css/app.css`
- `public/assets/js/app.js`
- `public/uploads/.gitkeep`
- `app/autoload.php`
- `app/Router.php`
- `config/app.php`
- `config/database.php`
- `config/routes.php`
- `config.example.php`
- `sql/schema.sql`
- `storage/logs/.gitkeep`
- `storage/sessions/.gitkeep`
- `.gitignore`
- `app/Models/BaseModel.php`
- `app/Models/UserModel.php`
- `app/Middleware/CsrfMiddleware.php`
- `app/Middleware/AuthMiddleware.php`
- `app/Helpers/Session.php`
- `app/Helpers/View.php`
- `app/Helpers/Flash.php`
- `public/index.php`
- `app/Controllers/AdminAuthController.php`
- `app/Controllers/HomeController.php`
- `app/Controllers/ErrorController.php`
- `app/Helpers/Upload.php`
- `app/Views/layouts/main.php`
- `app/Views/layouts/admin.php`
- `app/Views/layouts/admin_bare.php`
- `app/Views/public/home.php`
- `app/Views/public/404.php`
- `app/Views/public/403.php`
- `app/Views/public/500.php`
- `app/Views/admin/login.php`
- `app/Views/admin/dashboard.php`
- `app/Views/admin/stub.php`
- `sql/schema.sql`
