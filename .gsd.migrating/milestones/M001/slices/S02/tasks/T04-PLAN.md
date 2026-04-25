# T04: 02-presentes-publicos-crud-admin 04

**Slice:** S02 — **Milestone:** M001

## Description

Guest authentication JSON endpoints (signup, login, logout) and all interactive JavaScript: filter debounce + AJAX swap, auth modal tab switching and form submission, gift ID preservation, slug auto-generation, and image preview.

Purpose: This plan wires the interactivity that turns the static HTML from Plan 03 into a functional gift browsing and authentication experience. The JS targets IDs and classes established in Plan 03 views.

Output: GuestAuthController.php, updated UserModel.php (with create()), updated public/assets/js/app.js, updated config/routes.php.

## Must-Haves

- [ ] "POST /auth/guest-signup with valid data creates a user row, sets session (role=guest), returns JSON {success: true}"
- [ ] "POST /auth/guest-signup with duplicate email returns JSON {error: 'Este e-mail já está cadastrado. Tente entrar.'}"
- [ ] "POST /auth/guest-login with correct credentials sets session and returns JSON {success: true}"
- [ ] "POST /auth/guest-login with wrong credentials returns JSON {error: 'E-mail ou senha incorretos.'} — no session set"
- [ ] "POST /auth/guest-logout clears session and redirects to /"
- [ ] "Filter bar search (300ms debounce), category select, and sort select trigger fetch to /gifts/filter and swap #gifts-container innerHTML"
- [ ] "Clicking Escolher este presente sets window._pendingGiftId; if not logged in, opens #authModal"
- [ ] "Auth modal tabs switch between signup/login forms without closing the modal"
- [ ] "On signup/login success, modal shows #auth-success message then hides after 1200ms"
- [ ] "On signup/login error, inline error div shows the error message from JSON response"
- [ ] "Slug field auto-generates from name input (lowercase, hyphens) unless manually edited by user"
- [ ] "Image file input shows FileReader preview in #image-preview before upload"

## Files

- `app/Controllers/GuestAuthController.php`
- `app/Models/UserModel.php`
- `public/assets/js/app.js`
- `config/routes.php`
