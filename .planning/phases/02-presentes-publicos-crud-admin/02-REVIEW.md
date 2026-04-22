---
phase: 02-presentes-publicos-crud-admin
reviewed: 2026-04-22T00:00:00Z
depth: standard
files_reviewed: 20
files_reviewed_list:
  - sql/migrate_phase2.sql
  - public/uploads/.htaccess
  - app/Router.php
  - app/Controllers/HomeController.php
  - app/Controllers/AdminAuthController.php
  - app/Controllers/ErrorController.php
  - app/Views/layouts/main.php
  - app/Views/layouts/admin.php
  - public/assets/css/app.css
  - app/Models/GiftModel.php
  - app/Controllers/AdminGiftController.php
  - app/Views/admin/gifts/index.php
  - app/Views/admin/gifts/form.php
  - config/routes.php
  - app/Controllers/GiftController.php
  - app/Views/public/gifts/index.php
  - app/Views/public/gifts/_cards.php
  - app/Controllers/GuestAuthController.php
  - app/Models/UserModel.php
  - public/assets/js/app.js
findings:
  critical: 1
  warning: 4
  info: 4
  total: 9
status: issues_found
---

# Phase 02: Code Review Report

**Reviewed:** 2026-04-22T00:00:00Z
**Depth:** standard
**Files Reviewed:** 20
**Status:** issues_found

## Summary

Reviewed the full Phase 2 implementation — public gifts listing, admin CRUD, guest authentication, SQL migration, and supporting infrastructure.

Overall the code is well-structured and security-conscious: CSRF tokens are present on all mutating forms, PDO prepared statements are used throughout, `htmlspecialchars(ENT_QUOTES)` is applied consistently, upload validation uses finfo via `Upload::validateMime`, and `password_hash`/`password_verify` are used correctly.

One critical SQL injection vector exists in `GiftModel::update()` where column names from the `$data` array are interpolated into a dynamic SQL string without whitelist validation. The remaining issues are a session-management inconsistency on guest logout, a delete race condition, a missing duplicate-slug error handler, and minor housekeeping items.

---

## Critical Issues

### CR-01: SQL Injection via Unvalidated Column Names in GiftModel::update()

**File:** `app/Models/GiftModel.php:130`

**Issue:** `update()` builds a `SET` clause by iterating over all keys in the `$data` array and directly interpolating them into the SQL string with backticks: `` "`{$key}` = :{$key}" ``. The `$data` array is constructed in `AdminGiftController::extractFormData()` today, but the model method is a public API. Any caller — present or future — that passes an attacker-influenced or programmer-typo'd key will corrupt the query. While the backtick quoting prevents classic `'; DROP TABLE` injection, it does NOT prevent injecting additional column names (e.g., `` `role` = :role `` added via a forged POST key that bypasses `extractFormData`). In the current call path a guest cannot reach this method, but the model itself is the vulnerability surface.

**Fix:** Add an explicit allowlist of updatable columns inside the model method and reject any key not in the list:

```php
public function update(int $id, array $data): void {
    if (empty($data)) {
        return;
    }

    $allowed = [
        'name', 'slug', 'description', 'category', 'type', 'price',
        'image_path', 'quantity', 'available_qty', 'featured',
        'is_active', 'sort_order',
    ];

    $setClauses = [];
    $params     = [':id' => $id];

    foreach ($data as $key => $value) {
        if (!in_array($key, $allowed, true)) {
            continue; // silently skip unknown columns
        }
        $setClauses[]      = "`{$key}` = :{$key}";
        $params[":{$key}"] = $value;
    }

    if (empty($setClauses)) {
        return;
    }

    $setClauses[] = 'updated_at = NOW()';
    $setStr       = implode(', ', $setClauses);

    $this->query("UPDATE gifts SET {$setStr} WHERE id = :id", $params);
}
```

---

## Warnings

### WR-01: Guest Logout Does Not Clear Session Data Before Destroying It

**File:** `app/Controllers/GuestAuthController.php:87-91`

**Issue:** `logout()` calls only `session_destroy()` without first clearing `$_SESSION = []` or calling `session_unset()`. PHP's documentation notes that `session_destroy()` alone does not immediately clear the superglobal in memory. More importantly, the cookie is not explicitly invalidated (no `setcookie` call to expire it), which means the old session ID remains in the browser and can be replayed against a new session if the server reuses that ID (rare but possible on short ID spaces). Compare with `AdminAuthController::logout()` which correctly does `$_SESSION = []` then `session_unset()` then `session_destroy()`.

**Fix:**

```php
public function logout(array $params = []): void {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
    header('Location: /');
    exit;
}
```

### WR-02: Delete Race Condition — Reservation Check Not Atomic With Delete

**File:** `app/Controllers/AdminGiftController.php:128-153`

**Issue:** `delete()` calls `reservationCount()` and, if zero, proceeds to `delete()` the row. These are two separate queries with no transaction between them. A concurrent guest reservation created between the count check and the delete will be orphaned (its `gift_id` FK will reference a non-existent row). On Hostinger shared MySQL this is unlikely but becomes a real risk once Phase 3 reservation writes are live.

**Fix:** Wrap the check-and-delete in a transaction, or use a single `DELETE ... WHERE id = :id AND (SELECT COUNT(*) FROM reservations WHERE gift_id = :id) = 0` with a row-count check afterward. Simpler approach:

```php
public function delete(array $params = []): void {
    $id        = (int)($params['id'] ?? 0);
    $giftModel = new GiftModel;
    $gift      = $giftModel->findById($id);
    if (!$gift) {
        Flash::set('error', 'Presente não encontrado.');
        header('Location: /admin/gifts');
        exit;
    }

    // Re-check inside a transaction so no reservation can sneak in
    $db = $giftModel->getDb(); // expose PDO if not already public
    $db->beginTransaction();
    try {
        $count = $giftModel->reservationCount($id);
        if ($count > 0) {
            $db->rollBack();
            Flash::set('error', 'Este presente possui reservas associadas e não pode ser excluído.');
            header('Location: /admin/gifts');
            exit;
        }
        $giftModel->delete($id);
        $db->commit();
    } catch (\Throwable $e) {
        $db->rollBack();
        throw $e;
    }

    // Clean up image …
}
```

### WR-03: Duplicate Slug Causes Unhandled PDO Exception (500 Page Leak)

**File:** `app/Controllers/AdminGiftController.php:62` / `app/Models/GiftModel.php:91`

**Issue:** The `gifts` table has a `UNIQUE INDEX` on `slug` (added in `migrate_phase2.sql` line 14). If an admin submits a slug that already exists, `GiftModel::insert()` will throw a PDO `SQLSTATE[23000]: Integrity constraint violation`. This exception is not caught anywhere between `GiftModel::insert()` and the front controller, so it will propagate as an unhandled exception and render as a 500 error (possibly leaking a PDO error message to the screen depending on `display_errors` config). The same applies to `update()` when the slug is changed to an existing value.

**Fix:** In `AdminGiftController::store()` and `update()`, catch `\PDOException` with SQLSTATE 23000 and redirect back with a validation error:

```php
try {
    (new GiftModel)->insert($data);
} catch (\PDOException $ex) {
    if (str_starts_with($ex->getCode(), '23')) {
        $errors['slug'] = 'Este slug já está em uso. Escolha outro.';
        // re-render form with $errors …
        return;
    }
    throw $ex;
}
```

### WR-04: Upload Directory Listing Not Disabled in .htaccess

**File:** `public/uploads/.htaccess:1`

**Issue:** The `.htaccess` blocks PHP execution (good) but does not disable directory listing. On Apache with `Options +Indexes` enabled (common on Hostinger shared hosting defaults), a request to `/uploads/gifts/` will return a full file listing, exposing all uploaded gift images by enumerable URL. This is a low-severity information disclosure but contradicts the SEC-05 hardening intent of the file.

**Fix:** Add `Options -Indexes` as the first directive:

```apache
Options -Indexes

<FilesMatch "\.(php|php3|php4|php5|phtml|pl|py|cgi|sh)$">
    Deny from all
</FilesMatch>
```

Also note that the current file has two `<FilesMatch>` blocks where the first (line 2-4) is a subset of the second (line 6-8). The first block is redundant and can be removed.

---

## Info

### IN-01: Redundant Session Cleanup in AdminAuthController::logout()

**File:** `app/Controllers/AdminAuthController.php:61-63`

**Issue:** `logout()` calls both `$_SESSION = []` and `session_unset()` before `session_destroy()`. `session_unset()` clears the `$_SESSION` superglobal; `$_SESSION = []` does the same thing. One call is redundant. The PHP manual recommends `$_SESSION = []` over `session_unset()` for modern PHP.

**Fix:**

```php
public function logout(array $params = []): void {
    $_SESSION = [];
    session_destroy();
    header('Location: /admin/login');
    exit;
}
```

### IN-02: Slug Auto-Generation in app.js Has Duplicate Logic vs. form.php Inline Script

**File:** `public/assets/js/app.js:186-207` and `app/Views/admin/gifts/form.php:222-239`

**Issue:** Slug auto-generation logic is implemented twice — once in the inline `<script>` block at the bottom of `form.php` (lines 222-239) and again in `GiftPage.initSlugGeneration()` in `app.js`. Both listeners attach to the same DOM elements. On pages that load `app.js`, both event listeners will fire on each `input` event, running the normalization twice. The second pass is idempotent for most inputs, but the dual-registration is wasteful and could cause unexpected behavior if the implementations diverge.

**Fix:** Remove the inline `<script>` block from `form.php` entirely — `app.js` already handles this functionality via `GiftPage.initSlugGeneration()`.

### IN-03: `container.innerHTML` Assignment After AJAX Filter Is Potential XSS Surface

**File:** `public/assets/js/app.js:37`

**Issue:** `container.innerHTML = html` replaces the gifts container with the raw HTML returned by `/gifts/filter`. The server-side `_cards.php` partial correctly escapes all output with `$e()`. However, if an attacker ever compromised the server-side partial or a future contributor forgets to escape a new field, the innerHTML assignment would immediately deliver XSS to every visitor. This is a defense-in-depth concern — the current code is safe, but the pattern warrants a note for future contributors.

**Fix:** No immediate code change required. Add a comment documenting the trust assumption:

```js
// Safe: server renders _cards.php which escapes all user data via htmlspecialchars.
// Any new field added to _cards.php MUST use $e() before this assignment is safe.
container.innerHTML = html;
```

### IN-04: `data-logged-in` Attribute in `_cards.php` Reflects Stale Session State After AJAX Reload

**File:** `app/Views/public/gifts/_cards.php:67`

**Issue:** Each gift card button emits `data-logged-in="<?= isset($_SESSION['user_id']) ? '1' : '0' ?>"`. When the card grid is replaced via AJAX (`/gifts/filter`), the new HTML is rendered server-side with the current session state — which is correct. However, the `index.php` page-level `<div id="page-meta" data-logged-in="...">` (line 50) is NOT refreshed by the AJAX call, so it can diverge from the card buttons after the user logs in via the modal and the cards are subsequently filtered. The JS currently reads `data-logged-in` from the card button (`btn.dataset.loggedIn`) which is correct and always fresh after an AJAX re-render, so this is not a live bug today. It is an IN-level note for the Phase 3 reservation wiring to be aware of.

**Fix:** No immediate change needed. When Phase 3 wires the reservation button, drive the logged-in check from `page-meta` only and refresh it via JS after a successful auth, rather than relying on the per-card attribute.

---

_Reviewed: 2026-04-22T00:00:00Z_
_Reviewer: Claude (gsd-code-reviewer)_
_Depth: standard_
