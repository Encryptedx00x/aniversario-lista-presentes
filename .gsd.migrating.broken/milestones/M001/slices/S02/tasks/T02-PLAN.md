# T02: 02-presentes-publicos-crud-admin 02

**Slice:** S02 — **Milestone:** M001

## Description

Admin CRUD for gifts: GiftModel (all DB methods), AdminGiftController (index/create/store/edit/update/delete/toggleActive), admin views (list table + create/edit form), and route registrations.

Purpose: Enables the admin to manage the gift catalog — the data source for the public gifts page. Must be complete with upload security, delete guard, and all D-17 fields before the public page (Plan 03) can display real data.

Output: GiftModel.php, AdminGiftController.php, admin/gifts/index.php, admin/gifts/form.php, updated config/routes.php.

## Must-Haves

- [ ] "Admin can list all gifts at /admin/gifts with name, category, price, available_qty, status, and action buttons"
- [ ] "Admin can create a gift with all D-17 fields including image upload — gift persists to DB"
- [ ] "Admin can edit an existing gift — changes persist to DB and image can be replaced"
- [ ] "Admin can toggle active/inactive status inline without leaving the list"
- [ ] "Admin cannot delete a gift that has reservations — error flash is shown instead"
- [ ] "Image upload rejects files over 2MB, non-whitelisted MIME types, and non-whitelisted extensions"
- [ ] "Admin can filter the gifts list by name and status (all/active/inactive) via GET form"

## Files

- `app/Models/GiftModel.php`
- `app/Controllers/AdminGiftController.php`
- `app/Views/admin/gifts/index.php`
- `app/Views/admin/gifts/form.php`
- `config/routes.php`
