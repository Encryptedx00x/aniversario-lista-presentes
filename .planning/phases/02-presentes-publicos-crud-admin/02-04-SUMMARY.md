---
phase: 02-presentes-publicos-crud-admin
plan: "04"
subsystem: guest-auth-js
tags: [auth, javascript, ajax, session, csrf]
dependency_graph:
  requires: [02-03]
  provides: [guest-auth-endpoints, interactive-js]
  affects: [public/assets/js/app.js, config/routes.php]
tech_stack:
  added: []
  patterns: [JSON-auth-endpoints, FormData-CSRF, delegated-events, debounce-fetch]
key_files:
  created:
    - app/Controllers/GuestAuthController.php
  modified:
    - app/Models/UserModel.php
    - config/routes.php
    - public/assets/js/app.js
decisions:
  - "Error messages set via textContent (not innerHTML) to prevent XSS — T-02-16 mitigated"
  - "FormData body (not JSON) used for auth fetch so PHP $_POST and CsrfMiddleware work without changes"
  - "Delegated event listener on document for .btn-choose-gift so dynamically-replaced filter cards are covered"
metrics:
  duration: "~15 minutes"
  completed: "2026-04-22"
---

# Phase 02 Plan 04: Guest Auth & Interactive JS Summary

Guest authentication JSON endpoints (signup, login, logout) and all interactive JavaScript wired: filter debounce + AJAX card swap, auth modal tab switching and form submission with CSRF, gift ID preservation, slug auto-generation with accent stripping, and image preview via FileReader.

## Tasks Completed

| Task | Name | Commit | Files |
|------|------|--------|-------|
| 1 | GuestAuthController + UserModel::create() + routes | c579d8d | GuestAuthController.php (new), UserModel.php, config/routes.php |
| 2 | app.js — filter, modal, slug generation, image preview | a6b4239 | public/assets/js/app.js |

## What Was Built

**GuestAuthController.php** — Three JSON endpoints:
- `signupPost()`: validates name/email/password, checks duplicate email via `findByEmail()`, hashes with `password_hash(PASSWORD_DEFAULT)`, inserts via `UserModel::create()`, auto-authenticates with `session_regenerate_id(true)`, returns `{success: true}`
- `loginPost()`: validates credentials with `password_verify()`, sets session, returns `{success: true}` or `{error: "..."}`
- `logout()`: `session_destroy()` then redirect to `/`

**UserModel::create()** — PDO prepared INSERT with named params (`:name`, `:email`, `:phone`, `:password_hash`, `:role`), returns `lastInsertId()` as int.

**config/routes.php** — Three POST routes added: `/auth/guest-signup`, `/auth/guest-login`, `/auth/guest-logout` (no middleware — called unauthenticated).

**public/assets/js/app.js** — `GiftPage` object with:
- `initFilter()`: 300ms debounce on `#search-input`, immediate on `#category-filter`/`#sort-filter`, fetch to `/gifts/filter`, opacity loading state, innerHTML swap of `#gifts-container`
- `initChooseGift()`: delegated click on `.btn-choose-gift`, sets `window._pendingGiftId`, opens `#authModal` if not logged in
- `initAuthModal()`: tab switching via `[data-tab]`, signup/login form fetch with CSRF in FormData, `textContent` for error display, `bootstrap.Modal.getInstance().hide()` after 1200ms on success
- `initSlugGeneration()`: NFD accent stripping, stops on manual slug edit (`data-auto="false"`)
- `initImagePreview()`: FileReader preview in `#image-preview`

## Deviations from Plan

None — plan executed exactly as written.

## Threat Model Coverage

| Threat ID | Status |
|-----------|--------|
| T-02-14 | Mitigated — `findByEmail()` check before INSERT |
| T-02-16 | Mitigated — `textContent` used for all error display |
| T-02-17 | Mitigated — `session_regenerate_id(true)` in both signupPost and loginPost |
| T-02-18 | Mitigated — `csrf_token` appended to FormData; Router calls CsrfMiddleware before controller |
| T-02-15 | Accepted — no rate limiting; BCrypt provides natural slowdown |
| T-02-19 | Accepted — `_pendingGiftId` is UI-only hint; Phase 3 reservation will re-validate server-side |

## Known Stubs

- `initChooseGift()` logs `console.log('Gift selected:', giftId)` when user is already logged in — Phase 3 reservation flow will replace this placeholder.

## Self-Check: PASSED

- app/Controllers/GuestAuthController.php — exists
- app/Models/UserModel.php contains `public function create` — confirmed
- config/routes.php contains `/auth/guest-signup` — confirmed
- public/assets/js/app.js contains `GiftPage` — confirmed
- Commits c579d8d and a6b4239 — confirmed in git log
