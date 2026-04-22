---
phase: 02-presentes-publicos-crud-admin
plan: "02"
subsystem: admin-gifts-crud
tags: [php, pdo, crud, upload, csrf, bootstrap5]
dependency_graph:
  requires: [02-01]
  provides: [GiftModel, AdminGiftController, admin-gifts-views]
  affects: [02-03-public-gifts-page, 02-04-guest-auth-js]
tech_stack:
  added: []
  patterns: [pdo-named-params, dynamic-update-set, finfo-mime-validation, uniqid-filename, reservationCount-delete-guard]
key_files:
  created:
    - app/Models/GiftModel.php
    - app/Controllers/AdminGiftController.php
    - app/Views/admin/gifts/index.php
    - app/Views/admin/gifts/form.php
  modified:
    - config/routes.php
decisions:
  - GiftModel::update() builds SET clause dynamically from $data keys to avoid NULL-wiping unset fields
  - filter() ORDER BY clause uses PHP match() to map sort param to hardcoded SQL strings — no user input in ORDER BY
  - Delete cleans up image file from filesystem after successful DB delete
  - Old image file deleted on update when a new image is uploaded
metrics:
  duration: "151s"
  completed_date: "2026-04-22"
  tasks_completed: 2
  files_changed: 5
---

# Phase 2 Plan 02: Admin CRUD for Gifts Summary

Admin gift management CRUD — GiftModel (9 data-access methods), AdminGiftController (7 actions, secure upload, delete guard), admin list table, create/edit form, and full route table with dynamic segments.

## Tasks Completed

| Task | Name | Commit | Files |
|------|------|--------|-------|
| 1 | GiftModel — all data access methods | 052ca68 | app/Models/GiftModel.php |
| 2 | AdminGiftController + admin views + routes | 37020bb | app/Controllers/AdminGiftController.php, app/Views/admin/gifts/index.php, app/Views/admin/gifts/form.php, config/routes.php |

## What Was Built

**GiftModel** (`app/Models/GiftModel.php`): Extends BaseModel. Implements getAllActive (active gifts ordered by availability), filter (active gifts with optional name search, category filter, and sort via PHP match — safe from injection), adminFilter (admin search by name/status), findById, insert (returns lastInsertId), update (dynamic SET clause, always sets updated_at), delete (returns true), toggleActive (fetch + flip), reservationCount (COUNT query for delete guard).

**AdminGiftController** (`app/Controllers/AdminGiftController.php`): Full CRUD — index with adminFilter, create form, store with image upload and validation, edit form (pre-filled), update with optional image replacement + old file cleanup, delete gated by reservationCount, toggleActive with contextual flash. Private helpers: extractFormData, validate, handleImageUpload (finfo MIME check + extension whitelist + 2MB size check + uniqid rename + move_uploaded_file).

**admin/gifts/index.php**: Table with filter bar (GET form, name search + status select), table-hover list showing thumbnail, name, category, price, available/total qty, status badge (bg-success/bg-secondary), and action buttons. Inline toggle and delete use POST forms with CSRF tokens. Delete uses browser confirm. Empty state with "+ Novo presente" link.

**admin/gifts/form.php**: Two-column layout (col-md-8 main, col-md-4 sidebar). All D-17 fields: name, slug (data-auto="true"), description, category, price, type select, total_qty, available_qty, image upload, featured checkbox, active checkbox (checked by default on create). Image preview (#image-preview) shown on edit. Inline JS for image preview via FileReader and slug auto-generation from name (with manual-edit detection via data-auto attribute). Validation errors shown with is-invalid + invalid-feedback.

**config/routes.php**: Full admin gift route table — GET /admin/gifts, /admin/gifts/create, /admin/gifts/{id}/edit and POST /admin/gifts/store, /admin/gifts/{id}/update, /admin/gifts/{id}/delete, /admin/gifts/{id}/toggle-active. Replaced old giftsStub route.

## Security Mitigations Applied

| Threat ID | Mitigation |
|-----------|-----------|
| T-02-04 | Upload::validateMime() using finfo (magic bytes, not browser Content-Type) |
| T-02-05 | adminFilter uses named params; ORDER BY is PHP match() hardcoded string |
| T-02-06 | CSRF token in all POST forms in views + CsrfMiddleware in Router |
| T-02-07 | reservationCount() checked before delete; blocks with flash error if > 0 |
| T-02-08 | All view output via $e() closure (htmlspecialchars ENT_QUOTES UTF-8) |
| T-02-09 | All /admin/gifts/* routes have 'auth' middleware |

## Deviations from Plan

**1. [Rule 2 - Missing Functionality] Image file cleanup on delete and update**
- **Found during:** Task 2 implementation
- **Issue:** Plan skeleton did not include filesystem cleanup when deleting a gift or replacing an image on update
- **Fix:** Added @unlink() of old image file in both delete() and update() actions
- **Files modified:** app/Controllers/AdminGiftController.php

None of the other deviations — plan executed as written.

## Known Stubs

None — all data is wired from database via GiftModel.

## Threat Flags

None — no new trust boundaries beyond those in the plan's threat model.

## Self-Check: PASSED

- app/Models/GiftModel.php: FOUND
- app/Controllers/AdminGiftController.php: FOUND
- app/Views/admin/gifts/index.php: FOUND
- app/Views/admin/gifts/form.php: FOUND
- config/routes.php: modified with AdminGiftController routes
- commit 052ca68: GiftModel
- commit 37020bb: AdminGiftController + views + routes
