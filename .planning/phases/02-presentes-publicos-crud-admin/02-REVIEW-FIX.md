---
phase: 02-presentes-publicos-crud-admin
fixed_at: 2026-04-22T00:00:00Z
review_path: .planning/phases/02-presentes-publicos-crud-admin/02-REVIEW.md
iteration: 1
findings_in_scope: 5
fixed: 5
skipped: 0
status: all_fixed
---

# Phase 02: Code Review Fix Report

**Fixed at:** 2026-04-22T00:00:00Z
**Source review:** .planning/phases/02-presentes-publicos-crud-admin/02-REVIEW.md
**Iteration:** 1

**Summary:**
- Findings in scope: 5
- Fixed: 5
- Skipped: 0

## Fixed Issues

### CR-01: SQL Injection via Unvalidated Column Names in GiftModel::update()

**Files modified:** `app/Models/GiftModel.php`
**Commit:** e49cb14
**Applied fix:** Added an explicit `$allowed` column allowlist inside `GiftModel::update()`. The `foreach` loop now skips any key not in the list via `in_array(..., true)` before building the SET clause. An additional early-return guard was added for the case where all keys are filtered out.

### WR-01: Guest Logout Does Not Clear Session Data Before Destroying It

**Files modified:** `app/Controllers/GuestAuthController.php`
**Commit:** 6e49f13
**Applied fix:** Replaced the bare `session_destroy()` call with the full three-step sequence: `$_SESSION = []` to clear data in memory, `setcookie()` to expire the session cookie in the browser (guarded by `ini_get('session.use_cookies')`), then `session_destroy()`. Now matches the pattern already used in `AdminAuthController::logout()`.

### WR-02: Delete Race Condition — Reservation Check Not Atomic With Delete

**Files modified:** `app/Models/BaseModel.php`, `app/Controllers/AdminGiftController.php`
**Commit:** 72f898b
**Applied fix:** Added a public `getDb(): \PDO` method to `BaseModel` to expose the PDO connection. Rewrote `AdminGiftController::delete()` to open a transaction before calling `reservationCount()`, roll back and redirect if count > 0, otherwise call `delete()` and commit. A `catch (\Throwable)` block rolls back and re-throws on unexpected errors. The `findById()` check was moved before the transaction to return early on missing gifts.

### WR-03: Duplicate Slug Causes Unhandled PDO Exception (500 Page Leak)

**Files modified:** `app/Controllers/AdminGiftController.php`
**Commit:** 2b317b1
**Applied fix:** Wrapped the `GiftModel::insert()` call in `store()` and the `GiftModel::update()` call in `update()` with `try/catch (\PDOException)`. When the caught exception code starts with `'23'` (integrity constraint violation), the form is re-rendered with `$errors['slug'] = 'Este slug ja esta em uso. Escolha outro.'`. Any other PDOException is re-thrown.

### WR-04: Upload Directory Listing Not Disabled in .htaccess

**Files modified:** `public/uploads/.htaccess`
**Commit:** d49c690
**Applied fix:** Added `Options -Indexes` as the first directive to disable directory listing. Removed the redundant first `<FilesMatch>` block (which only matched `.php`) leaving only the comprehensive block covering all server-side script extensions.

---

_Fixed: 2026-04-22T00:00:00Z_
_Fixer: Claude (gsd-code-fixer)_
_Iteration: 1_
