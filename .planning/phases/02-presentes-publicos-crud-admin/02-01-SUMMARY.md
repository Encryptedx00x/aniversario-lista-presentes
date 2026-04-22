---
phase: 02-presentes-publicos-crud-admin
plan: "01"
subsystem: infrastructure
tags: [schema, router, css, security, upload]
dependency_graph:
  requires: [01-base-infraestrutura]
  provides: [migrate_phase2.sql, dynamic-router, bootstrap-icons, gift-card-css, upload-security]
  affects: [02-02, 02-03, 02-04]
tech_stack:
  added: []
  patterns: [dynamic-segment-routing, named-capture-groups, htaccess-execution-denial]
key_files:
  created:
    - sql/migrate_phase2.sql
    - public/uploads/.htaccess
    - public/uploads/gifts/.gitkeep
  modified:
    - app/Router.php
    - app/Controllers/HomeController.php
    - app/Controllers/AdminAuthController.php
    - app/Controllers/ErrorController.php
    - app/Views/layouts/main.php
    - app/Views/layouts/admin.php
    - public/assets/css/app.css
    - .gitignore
decisions:
  - "Router passes $params array to all controller actions; existing controllers use default array $params = [] to avoid breaking changes"
  - "Upload .gitignore updated from public/uploads/* pattern to public/uploads/** with explicit exceptions for .htaccess and gifts/"
metrics:
  duration: "~10 minutes"
  completed: "2026-04-22"
  tasks_completed: 2
  tasks_total: 2
---

# Phase 2 Plan 01: Foundation Infrastructure Summary

**One-liner:** Schema migration adding slug/category/type/featured/available_qty to gifts, dynamic segment Router with named capture groups, Bootstrap Icons in both layouts, upload directory PHP execution denial, and gift card CSS visual states.

## Tasks Completed

| Task | Name | Commit | Files |
|------|------|--------|-------|
| 1 | Schema migration and upload directory | 406b796 | sql/migrate_phase2.sql, public/uploads/.htaccess, public/uploads/gifts/.gitkeep, .gitignore |
| 2 | Router dynamic segments + Bootstrap Icons + gift card CSS | fc9f55d | app/Router.php, 3 controllers, 2 layouts, app.css |

## What Was Built

### Schema Migration (sql/migrate_phase2.sql)
Adds to the Phase 1 schema without modifying schema.sql:
- `gifts`: slug (VARCHAR 255, unique index), category, type (ENUM single/multi), featured, available_qty
- `users`: phone column for guest registration
- `reservations`: user_id column (no FK yet — prepared for Phase 3)
- Backfills available_qty = GREATEST(0, quantity - reserved) for existing rows

### Router Extension (app/Router.php)
- Fast-path exact match preserved (no regex overhead for static routes)
- Dynamic segment support via `{param}` placeholders converted to named capture groups `(?P<param>[^/]+)`
- Extracted params passed as `array $params` to controller action
- Private `self::run()` method handles middleware + controller dispatch
- Enables routes like `/admin/gifts/{id}/edit`, `/admin/gifts/{id}/delete`

### Controller Updates
All public action methods updated to accept `array $params = []` as first argument:
- HomeController::index
- AdminAuthController: loginForm, loginPost, dashboard, giftsStub, logout
- ErrorController: notFound, forbidden, serverError

### Bootstrap Icons
CDN link `https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11/font/bootstrap-icons.min.css` added to both `main.php` and `admin.php` layouts.

### Upload Directory Security (T-02-01)
- `public/uploads/.htaccess` denies PHP/script execution via FilesMatch directives
- `public/uploads/gifts/.gitkeep` tracks directory in git
- `.gitignore` updated to allow .htaccess and gifts/ while still ignoring user-uploaded files

### Gift Card CSS
Three visual state classes appended to app.css:
- `.gift-card--esgotado` — opacity 0.55 + grayscale(30%) for out-of-stock
- `.gift-card__featured-badge` — absolute positioned top-right badge
- `.gift-card__placeholder` — 200px placeholder with centered icon, responsive at 160px on mobile

## Deviations from Plan

### Auto-fixed Issues

**1. [Rule 3 - Blocking] .gitignore excluded public/uploads/.htaccess and gifts/ directory**
- **Found during:** Task 1 commit
- **Issue:** Existing `.gitignore` pattern `public/uploads/*` prevented staging the security .htaccess and .gitkeep files
- **Fix:** Changed pattern to `public/uploads/**` with explicit negation exceptions for `.htaccess` and `gifts/` subdirectory
- **Files modified:** .gitignore
- **Commit:** 406b796

## Known Stubs

None — this plan creates infrastructure only, no UI rendering with data.

## Threat Flags

None — all threat mitigations from the plan's threat model were implemented (T-02-01 via .htaccess, T-02-02 via Router not executing params).

## Self-Check: PASSED

All created files confirmed present on disk. Both task commits verified in git log.
