# S02: Presentes Publicos Crud Admin

**Goal:** Foundation infrastructure for Phase 2: schema migration adding missing gift columns, Router extension for dynamic path segments, Bootstrap Icons in both layouts, upload directory security, and gift card CSS visual states.
**Demo:** Foundation infrastructure for Phase 2: schema migration adding missing gift columns, Router extension for dynamic path segments, Bootstrap Icons in both layouts, upload directory security, and gift card CSS visual states.

## Must-Haves


## Tasks

- [x] **T01: 02-presentes-publicos-crud-admin 01**
  - Foundation infrastructure for Phase 2: schema migration adding missing gift columns, Router extension for dynamic path segments, Bootstrap Icons in both layouts, upload directory security, and gift card CSS visual states.

Purpose: Every subsequent plan in this phase depends on these artifacts. GiftModel cannot reference slug/category/type/featured/available_qty until the migration runs. AdminGiftController cannot use /admin/gifts/{id}/edit routes until Router supports dynamic segments. Image upload fails silently if the directory is missing or PHP can execute inside it.

Output: sql/migrate_phase2.sql, updated app/Router.php, updated layout files, public/uploads/.htaccess, public/uploads/gifts/.gitkeep, updated public/assets/css/app.css.
- [x] **T02: 02-presentes-publicos-crud-admin 02**
  - Admin CRUD for gifts: GiftModel (all DB methods), AdminGiftController (index/create/store/edit/update/delete/toggleActive), admin views (list table + create/edit form), and route registrations.

Purpose: Enables the admin to manage the gift catalog — the data source for the public gifts page. Must be complete with upload security, delete guard, and all D-17 fields before the public page (Plan 03) can display real data.

Output: GiftModel.php, AdminGiftController.php, admin/gifts/index.php, admin/gifts/form.php, updated config/routes.php.
- [x] **T03: 02-presentes-publicos-crud-admin 03**
  - Public gifts page: GiftController (index + filter endpoints), the full public gifts view (filter bar + card grid + auth modal structure), and the _cards.php partial for AJAX filter responses.

Purpose: This is the primary user-facing feature of Phase 2. Guests browse gifts, use the filter/search/sort bar, and see the auth modal when they click "Escolher este presente". Plan 04 wires the JS; this plan delivers the HTML structure those scripts target.

Output: GiftController.php, app/Views/public/gifts/index.php, app/Views/public/gifts/_cards.php, updated config/routes.php.
- [x] **T04: 02-presentes-publicos-crud-admin 04**
  - Guest authentication JSON endpoints (signup, login, logout) and all interactive JavaScript: filter debounce + AJAX swap, auth modal tab switching and form submission, gift ID preservation, slug auto-generation, and image preview.

Purpose: This plan wires the interactivity that turns the static HTML from Plan 03 into a functional gift browsing and authentication experience. The JS targets IDs and classes established in Plan 03 views.

Output: GuestAuthController.php, updated UserModel.php (with create()), updated public/assets/js/app.js, updated config/routes.php.

## Files Likely Touched

- `sql/migrate_phase2.sql`
- `app/Router.php`
- `app/Views/layouts/main.php`
- `app/Views/layouts/admin.php`
- `public/uploads/.htaccess`
- `public/uploads/gifts/.gitkeep`
- `public/assets/css/app.css`
- `app/Models/GiftModel.php`
- `app/Controllers/AdminGiftController.php`
- `app/Views/admin/gifts/index.php`
- `app/Views/admin/gifts/form.php`
- `config/routes.php`
- `app/Controllers/GiftController.php`
- `app/Views/public/gifts/index.php`
- `app/Views/public/gifts/_cards.php`
- `config/routes.php`
- `app/Controllers/GuestAuthController.php`
- `app/Models/UserModel.php`
- `public/assets/js/app.js`
- `config/routes.php`
