# T01: 01-base-infraestrutura 01

**Slice:** S01 — **Milestone:** M001

## Description

Build the static skeleton: directory structure, Apache routing, autoloader, router, route table, config files, SQL schema, and the credential placeholder template.

Purpose: Everything in Waves 2 and 3 depends on this skeleton being in place. No application code runs without the front controller entry point, autoloader, and router.
Output: 14 files. No PHP class yet executes — only the infrastructure that makes class loading possible.

## Must-Haves

- [ ] ".htaccess routes all non-file requests to public/index.php"
- [ ] "config.example.php exists with credential placeholder keys"
- [ ] "sql/schema.sql creates users table without errors on MySQL 5.7+"
- [ ] "display_errors defaults to 0 in production (APP_ENV != development)"
- [ ] "Autoloader maps App\\Namespace\\Class to app/Namespace/Class.php"

## Files

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
