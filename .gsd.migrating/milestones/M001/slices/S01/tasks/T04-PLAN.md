# T04: 01-base-infraestrutura 04

**Slice:** S01 — **Milestone:** M001

## Description

Create all view files (layouts, public pages, admin pages) and extend sql/schema.sql with the full table set.

Purpose: Plan 03 controllers call View::render() but the templates do not exist yet — this plan creates them. The schema extension (D-11) prevents structural ALTER TABLE in later phases.
Output: 10 view files + 1 updated SQL file. After this plan, the full request cycle renders HTML to the browser.

## Must-Haves

- [ ] "Public layout renders Bootstrap 5 via CDN with header links to Presentes and Pix (D-03)"
- [ ] "Admin layout has sidebar with all four sections (Presentes, Reservas, Pix, Configurações) visible and clickable (D-05)"
- [ ] "Admin header shows logged-in user name and logout button top-right (D-06, D-07)"
- [ ] "Home hero displays aniversariante name placeholder — not an 'em breve' message (D-01)"
- [ ] "404 page has two buttons: Ir para home and Ver presentes (D-10)"
- [ ] "403 page shows exact text from D-09 with Voltar button"
- [ ] "Admin dashboard has four stat cards all showing 0 (D-04)"
- [ ] "sql/schema.sql creates all five tables: users, gifts, reservations, contributions, settings (D-11)"
- [ ] "Every POST form in views has CSRF hidden input (SEC-02)"
- [ ] "Every user-data output in views uses $e() or htmlspecialchars (SEC-03)"

## Files

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
