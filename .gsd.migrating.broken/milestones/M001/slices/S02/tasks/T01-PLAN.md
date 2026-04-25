# T01: 02-presentes-publicos-crud-admin 01

**Slice:** S02 — **Milestone:** M001

## Description

Foundation infrastructure for Phase 2: schema migration adding missing gift columns, Router extension for dynamic path segments, Bootstrap Icons in both layouts, upload directory security, and gift card CSS visual states.

Purpose: Every subsequent plan in this phase depends on these artifacts. GiftModel cannot reference slug/category/type/featured/available_qty until the migration runs. AdminGiftController cannot use /admin/gifts/{id}/edit routes until Router supports dynamic segments. Image upload fails silently if the directory is missing or PHP can execute inside it.

Output: sql/migrate_phase2.sql, updated app/Router.php, updated layout files, public/uploads/.htaccess, public/uploads/gifts/.gitkeep, updated public/assets/css/app.css.

## Must-Haves

- [ ] "sql/migrate_phase2.sql runs without error against the Phase 1 schema and adds all required columns"
- [ ] "Router.dispatch() resolves dynamic segments like /admin/gifts/42/edit and passes the captured id to the controller"
- [ ] "Bootstrap Icons CDN link is present in both main.php and admin.php layouts"
- [ ] "public/uploads/gifts/ directory exists in git and PHP execution is denied inside public/uploads/"
- [ ] "Gift card CSS classes (.gift-card--esgotado, .gift-card__featured-badge, .gift-card__placeholder) exist in app.css"

## Files

- `sql/migrate_phase2.sql`
- `app/Router.php`
- `app/Views/layouts/main.php`
- `app/Views/layouts/admin.php`
- `public/uploads/.htaccess`
- `public/uploads/gifts/.gitkeep`
- `public/assets/css/app.css`
