---
phase: 02-presentes-publicos-crud-admin
plan: "03"
subsystem: public-gifts-page
tags: [controller, view, routing, public, gifts, filter, auth-modal]
dependency_graph:
  requires: [02-01, 02-02]
  provides: [public-gift-listing, filter-endpoint, auth-modal-html]
  affects: [02-04]
tech_stack:
  added: []
  patterns: [require+exit partial, filter whitelist, AJAX-ready HTML fragment, data-attr CSRF]
key_files:
  created:
    - app/Controllers/GiftController.php
    - app/Views/public/gifts/index.php
    - app/Views/public/gifts/_cards.php
  modified:
    - config/routes.php
decisions:
  - "GiftController::filter() uses require+exit pattern (not View::render) to return bare HTML fragment for AJAX replacement"
  - "Sort param whitelisted before reaching GiftModel to prevent ORDER BY injection"
  - "GET / now routed to GiftController::index — HomeController::index no longer handles the root URL"
metrics:
  duration: ~10 min
  completed: "2026-04-22"
  tasks_completed: 2
  tasks_total: 2
requirements_addressed: [GIFT-01, GIFT-02, GIFT-03, GIFT-04, GIFT-05, GIFT-06]
---

# Phase 2 Plan 03: Public Gifts Page Summary

**One-liner:** Bootstrap 5 gift card grid with filter bar, AJAX-ready partial endpoint (/gifts/filter), and auth modal HTML structure wired for Plan 04 JS.

## Tasks Completed

| Task | Name | Commit | Files |
|------|------|--------|-------|
| 1 | GiftController — index and filter actions | 7232e12 | app/Controllers/GiftController.php |
| 2 | Public views and route registration | 6ad3123 | app/Views/public/gifts/index.php, app/Views/public/gifts/_cards.php, config/routes.php |

## What Was Built

**GiftController** (`app/Controllers/GiftController.php`):
- `index()`: loads all active gifts via `GiftModel::getAllActive()`, derives unique sorted category list for dropdown, renders full page via `View::render('public/gifts/index', ...)`.
- `filter()`: reads GET params (search, category, sort), whitelists sort against `['available','price_asc','price_desc','featured']`, calls `GiftModel::filter()`, then `require`s `_cards.php` and `exit`s — no layout wrapper in response.

**Public index view** (`app/Views/public/gifts/index.php`):
- Filter bar with `#search-input`, `#category-filter`, `#sort-filter`.
- Card grid `#gifts-container` with initial server-side render of `_cards.php`.
- `#authModal` with `data-csrf-token` attribute, two tab forms (`#tab-signup` / `#tab-login`), error divs (`#signup-error`, `#login-error`), and `#auth-success` message.
- `#page-meta` with `data-logged-in` for JS to detect session state without a separate API call.

**Card partial** (`app/Views/public/gifts/_cards.php`):
- Works in both render contexts (initial page load and AJAX partial).
- Sold-out: `.gift-card--esgotado` class, disabled "Indisponível" button.
- Featured: `.gift-card__featured-badge` with "DESTAQUE" (bg-warning).
- Missing image: `.gift-card__placeholder` with bi-image icon.
- Available: `.btn-choose-gift` button with `data-gift-id` and `data-logged-in` — Plan 04 JS targets these.
- Empty state with bi-gift icon and helper text.
- All DB output escaped via `$e()` closure.

**Routes** (`config/routes.php`):
- `GET /` → `GiftController::index` (replaced HomeController).
- `GET /gifts/filter` → `GiftController::filter`.

## Deviations from Plan

None — plan executed exactly as written.

## Known Stubs

None — no hardcoded data or placeholders. All gift data flows from DB via GiftModel.

## Threat Flags

No new threat surface introduced beyond what the plan's threat model covers (T-02-10 through T-02-13). All mitigations applied:
- `$e()` on all gift fields in `_cards.php` (T-02-10).
- Sort param whitelisted; GiftModel uses named PDO params (T-02-11).
- `data-logged-in` exposes only '1'/'0' (T-02-12).
- `data-csrf-token` from `CsrfMiddleware::token()` on modal div (T-02-13).

## Self-Check: PASSED

- app/Controllers/GiftController.php — FOUND
- app/Views/public/gifts/index.php — FOUND
- app/Views/public/gifts/_cards.php — FOUND
- config/routes.php updated with GiftController — FOUND
- Commits 7232e12 and 6ad3123 — FOUND
