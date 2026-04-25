# Codebase Map

Generated: 2026-04-24T23:57:47Z | Files: 49 | Described: 0/49
<!-- gsd:codebase-meta {"generatedAt":"2026-04-24T23:57:47Z","fingerprint":"0ffac52d06926e3c979385f569d681d957bb1c93","fileCount":49,"truncated":false} -->

### (root)/
- `.gitignore`
- `.htaccess`
- `CLAUDE.md`
- `config.example.php`
- `opencode.json`

### app/
- `app/autoload.php`
- `app/Router.php`

### app/Controllers/
- `app/Controllers/AdminAuthController.php`
- `app/Controllers/AdminGiftController.php`
- `app/Controllers/ErrorController.php`
- `app/Controllers/GiftController.php`
- `app/Controllers/GuestAuthController.php`
- `app/Controllers/HomeController.php`

### app/Helpers/
- `app/Helpers/Flash.php`
- `app/Helpers/Session.php`
- `app/Helpers/Upload.php`
- `app/Helpers/View.php`

### app/Middleware/
- `app/Middleware/AuthMiddleware.php`
- `app/Middleware/CsrfMiddleware.php`

### app/Models/
- `app/Models/BaseModel.php`
- `app/Models/GiftModel.php`
- `app/Models/UserModel.php`

### app/Views/admin/
- `app/Views/admin/dashboard.php`
- `app/Views/admin/login.php`
- `app/Views/admin/stub.php`

### app/Views/admin/gifts/
- `app/Views/admin/gifts/form.php`
- `app/Views/admin/gifts/index.php`

### app/Views/layouts/
- `app/Views/layouts/admin_bare.php`
- `app/Views/layouts/admin.php`
- `app/Views/layouts/main.php`

### app/Views/public/
- `app/Views/public/403.php`
- `app/Views/public/404.php`
- `app/Views/public/500.php`
- `app/Views/public/home.php`

### app/Views/public/gifts/
- `app/Views/public/gifts/_cards.php`
- `app/Views/public/gifts/index.php`

### config/
- `config/app.php`
- `config/routes.php`

### public/
- `public/.htaccess`
- `public/index.php`

### public/assets/css/
- `public/assets/css/app.css`

### public/assets/js/
- `public/assets/js/app.js`

### public/uploads/
- `public/uploads/.gitkeep`
- `public/uploads/.htaccess`

### public/uploads/gifts/
- `public/uploads/gifts/.gitkeep`

### sql/
- `sql/migrate_phase2.sql`
- `sql/schema.sql`

### storage/logs/
- `storage/logs/.gitkeep`

### storage/sessions/
- `storage/sessions/.gitkeep`
