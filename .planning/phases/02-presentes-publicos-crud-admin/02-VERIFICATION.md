---
phase: 02-presentes-publicos-crud-admin
verified: 2026-04-22T00:00:00Z
status: human_needed
score: 15/15
overrides_applied: 0
human_verification:
  - test: "Open / in browser — confirm gift cards render with image, name, description, price, status badge"
    expected: "Bootstrap card grid with 3 columns on desktop; cards have category badge, price, available qty, and Escolher/Indisponível button"
    why_human: "Requires live DB data after migrate_phase2.sql is applied; card visual rendering cannot be confirmed by static grep"
  - test: "Type in search bar, change category dropdown, change sort dropdown"
    expected: "Card grid updates without full page reload; 300ms debounce on search; opacity loading state visible briefly"
    why_human: "Requires browser JS execution and live /gifts/filter endpoint"
  - test: "Click 'Escolher este presente' on a card when not logged in"
    expected: "#authModal opens; tab switching between Criar conta / Entrar works; signup with valid data closes modal after 1200ms success message"
    why_human: "Requires browser interaction and live /auth/guest-signup endpoint returning JSON"
  - test: "Login with wrong credentials in the modal"
    expected: "Inline #login-error div shows 'E-mail ou senha incorretos.' message"
    why_human: "Requires browser interaction and live endpoint"
  - test: "Admin at /admin/gifts — create a gift with image upload (JPG under 2MB)"
    expected: "Gift persists to DB; appears in public listing; image shown in admin table thumbnail"
    why_human: "Requires live DB and actual file upload mechanics (move_uploaded_file)"
  - test: "Try to upload a PHP file disguised as image, or file over 2MB"
    expected: "Form re-renders with image error message; no file written to disk"
    why_human: "Requires live form submission with actual malformed file"
  - test: "Admin — delete a gift that has reservations"
    expected: "Flash error 'Este presente possui reservas associadas e não pode ser excluído.' shown; gift remains"
    why_human: "Requires live DB with at least one reservation row linked to the gift"
  - test: "Presente inativo should NOT appear in public listing"
    expected: "Card for inactive gift is absent from GET /"
    why_human: "Requires live DB with at least one inactive gift row"
---

# Phase 2: Presentes Públicos & CRUD Admin — Verification Report

**Phase Goal:** Public gifts listing with Bootstrap card grid, filter bar, and AJAX endpoint; Admin CRUD for gifts with secure image upload; Guest auth (signup/login) via modal with JSON endpoints; all interactive JS wired.
**Verified:** 2026-04-22T00:00:00Z
**Status:** human_needed
**Re-verification:** No — initial verification

---

## Goal Achievement

### Observable Truths

| # | Truth | Status | Evidence |
|---|-------|--------|----------|
| 1 | sql/migrate_phase2.sql adds slug, category, type, featured, available_qty to gifts; phone to users | VERIFIED | File present; all five ALTER TABLE columns confirmed; unique index on slug; backfill UPDATE; users phone column |
| 2 | Router.dispatch() resolves dynamic segments like /admin/gifts/42/edit and passes captured id to controller | VERIFIED | preg_match named-capture regex in Router.php; $params passed to $controller->$action($params) via self::run() |
| 3 | Bootstrap Icons CDN loaded in both main.php and admin.php layouts | VERIFIED | bootstrap-icons@1.11 link present in both layout files |
| 4 | public/uploads/.htaccess denies PHP execution; uploads/gifts/ directory exists | VERIFIED | FilesMatch Deny from all for .php and other script extensions; gifts/ directory confirmed present |
| 5 | Gift card CSS classes exist in app.css (.gift-card--esgotado, .gift-card__featured-badge, .gift-card__placeholder) | VERIFIED | All three classes confirmed in public/assets/css/app.css |
| 6 | Admin can list gifts with filter bar (name search + status select) | VERIFIED | AdminGiftController::index() calls GiftModel::adminFilter(); admin/gifts/index.php has filter form with search input and status select; table-hover table with all required columns |
| 7 | Admin can create and edit a gift with all D-17 fields; gift persists to DB | VERIFIED | AdminGiftController::store()/update() call GiftModel::insert()/update(); form.php has all required fields (name, slug, description, category, type, price, total_qty, available_qty, featured, active, image); enctype="multipart/form-data" present |
| 8 | Image upload rejects files over 2MB, non-whitelisted MIME, non-whitelisted extension | VERIFIED | handleImageUpload(): size check > 2MB throws, Upload::validateMime() (finfo), Upload::validateExtension() — all three checks before move_uploaded_file |
| 9 | Admin cannot delete a gift that has reservations | VERIFIED | AdminGiftController::delete() calls GiftModel::reservationCount($id); blocks with Flash::set('error', ...) if count > 0 |
| 10 | Admin can toggle active/inactive inline | VERIFIED | POST /admin/gifts/{id}/toggle-active → toggleActive(); GiftModel::toggleActive() fetches then flips is_active |
| 11 | GET / renders public gifts page with Bootstrap card grid | VERIFIED | GiftController::index() → View::render('public/gifts/index'); #gifts-container div with row-cols-1/md-2/lg-3; initial server render via PHP require _cards.php |
| 12 | Filter bar (search, category, sort) AJAX endpoint /gifts/filter returns HTML fragment only | VERIFIED | GiftController::filter() uses require ROOT_PATH/_cards.php then exit — no View::render; sort whitelisted against allowed array |
| 13 | Auth modal #authModal with CSRF data attribute and two tab forms present | VERIFIED | #authModal with data-csrf-token=CsrfMiddleware::token(); #tab-signup, #tab-login (d-none initially); #signup-error, #login-error, #auth-success; data-tab nav links |
| 14 | POST /auth/guest-signup creates user, auto-authenticates, returns {success: true}; duplicate email returns {error: ...} | VERIFIED | GuestAuthController::signupPost(): password_hash(PASSWORD_DEFAULT), findByEmail() duplicate check, session_regenerate_id(true), unset csrf_token, UserModel::create() with named params |
| 15 | All JS wired: filter debounce, modal interaction, slug generation, image preview | VERIFIED | GiftPage object in app.js: initFilter() (clearTimeout/setTimeout 300ms, fetch /gifts/filter, innerHTML swap), initChooseGift() (delegated, _pendingGiftId, modal show), initAuthModal() (tabs, FormData CSRF, textContent errors, hide after 1200ms), initSlugGeneration() (normalize NFD, data-auto tracking), initImagePreview() (FileReader) |

**Score:** 15/15 truths verified

---

### Required Artifacts

| Artifact | Expected | Status | Details |
|----------|----------|--------|---------|
| `sql/migrate_phase2.sql` | Schema migration with 5 gift columns + users.phone | VERIFIED | All columns, unique index, backfill UPDATE present |
| `app/Router.php` | Dynamic {param} routing with preg_match named captures | VERIFIED | preg_match + self::run() passing $params to actions |
| `public/uploads/.htaccess` | Deny PHP execution | VERIFIED | FilesMatch for .php and script extensions |
| `public/assets/css/app.css` | Gift card visual state classes | VERIFIED | .gift-card--esgotado, __featured-badge, __placeholder all present |
| `app/Models/GiftModel.php` | 9 data access methods | VERIFIED | getAllActive, filter, adminFilter, findById, insert, update, delete, toggleActive, reservationCount — all present |
| `app/Controllers/AdminGiftController.php` | Admin CRUD with upload security | VERIFIED | 7 public actions + 3 private helpers; handleImageUpload() with finfo + extension + size checks |
| `app/Views/admin/gifts/index.php` | Admin list table with filter bar | VERIFIED | table-hover, filter GET form, CSRF in toggle/delete forms, empty state |
| `app/Views/admin/gifts/form.php` | Create/edit form with image preview and slug generation | VERIFIED | All D-17 fields, data-auto="true" on slug, #image-preview, enctype="multipart/form-data", CSRF token |
| `app/Controllers/GiftController.php` | Public index + filter partial endpoint | VERIFIED | index() + filter() with require+exit pattern |
| `app/Views/public/gifts/index.php` | Full page with filter bar, #gifts-container, #authModal | VERIFIED | All required IDs present; data-csrf-token; both tab forms; auth-success div |
| `app/Views/public/gifts/_cards.php` | Card grid partial for both initial and AJAX render | VERIFIED | gift-card--esgotado conditional, __featured-badge, __placeholder, btn-choose-gift with data-gift-id and data-logged-in; all output via $e() |
| `app/Controllers/GuestAuthController.php` | JSON auth endpoints | VERIFIED | signupPost, loginPost, logout — all correct session handling and JSON output |
| `app/Models/UserModel.php` | Extended with create() | VERIFIED | create() with named PDO params present; existing methods preserved |
| `public/assets/js/app.js` | GiftPage with all 5 features | VERIFIED | GiftPage object, all initX() methods, DOMContentLoaded init |
| `config/routes.php` | All Phase 2 routes registered | VERIFIED | GET/POST admin gift routes with {id} segments; /auth/guest-* routes; / and /gifts/filter pointing to GiftController |

---

### Key Link Verification

| From | To | Via | Status | Details |
|------|----| ----|--------|---------|
| config/routes.php | AdminGiftController | Router dynamic dispatch | WIRED | All 7 admin gift routes (4 POST dynamic, 1 GET dynamic) confirmed |
| AdminGiftController::store() | GiftModel::insert() | handleImageUpload() + insert() | WIRED | handleImageUpload() called before insert(); insert() returns lastInsertId |
| AdminGiftController::delete() | reservations table | GiftModel::reservationCount() | WIRED | COUNT(*) FROM reservations WHERE gift_id = :id with named param |
| GiftController::filter() | _cards.php | require ROOT_PATH . '/app/Views/public/gifts/_cards.php'; exit | WIRED | Exact require+exit pattern confirmed |
| app.js fetch POST /auth/guest-signup | GuestAuthController::signupPost() | FormData with csrf_token from #authModal.dataset.csrfToken | WIRED | formData.append('csrf_token', csrfToken) confirmed; CsrfMiddleware::validate() fires via Router on every POST |
| app.js #search-input | GET /gifts/filter | debounce 300ms + URLSearchParams + innerHTML swap on #gifts-container | WIRED | clearTimeout/setTimeout 300ms, fetch /gifts/filter, container.innerHTML = html |
| GuestAuthController::signupPost() | UserModel::create() | password_hash then insert | WIRED | password_hash(PASSWORD_DEFAULT) passed to create(); create() returns lastInsertId |

---

### Data-Flow Trace (Level 4)

| Artifact | Data Variable | Source | Produces Real Data | Status |
|----------|---------------|--------|--------------------|--------|
| `_cards.php` | $gifts | GiftController::index() → GiftModel::getAllActive() → SELECT * WHERE is_active=1 | Yes — full DB query with ORDER BY | FLOWING |
| `admin/gifts/index.php` | $gifts | AdminGiftController::index() → GiftModel::adminFilter() → SELECT with WHERE and named params | Yes — DB query | FLOWING |
| `admin/gifts/form.php` | $gift | AdminGiftController::edit() → GiftModel::findById() → SELECT * WHERE id=:id | Yes — DB query | FLOWING |
| `GiftController::filter()` | $gifts | GiftModel::filter() → SELECT with named params for search/category, hardcoded ORDER BY | Yes — DB query | FLOWING |

---

### Behavioral Spot-Checks

Step 7b: SKIPPED — no runnable entry point available without a live database and Apache/PHP server. All spot-checks require live HTTP endpoints.

---

### Requirements Coverage

| Requirement | Source Plan | Description | Status | Evidence |
|-------------|------------|-------------|--------|---------|
| GIFT-01 | 02-01, 02-03 | Public gift listing page exists | SATISFIED | GET / → GiftController::index → public gifts view |
| GIFT-02 | 02-03 | Gift cards with image, name, description, price, status | SATISFIED | _cards.php renders all fields conditionally |
| GIFT-03 | 02-03 | Filter by category | SATISFIED | GiftModel::filter() category param; #category-filter in view; JS change event wired |
| GIFT-04 | 02-03 | Search by name | SATISFIED | GiftModel::filter() LIKE search; #search-input in view; JS debounce wired |
| GIFT-05 | 02-03 | Sort (availability, price, featured) | SATISFIED | GiftModel::filter() match() ORDER BY; #sort-filter in view; JS change wired |
| GIFT-06 | 02-03 | "Escolher este presente" opens auth/reserva modal | SATISFIED | .btn-choose-gift + initChooseGift() delegates click, opens #authModal if not logged in |
| GIFT-07 | 02-02 | Admin gift list at /admin/gifts | SATISFIED | AdminGiftController::index() wired to GET /admin/gifts |
| GIFT-08 | 02-02 | Admin can create gift | SATISFIED | POST /admin/gifts/store → store() → GiftModel::insert() |
| GIFT-09 | 02-02 | Admin can edit gift | SATISFIED | GET/POST /admin/gifts/{id}/edit+update → edit()/update() → GiftModel::findById()/update() |
| GIFT-10 | 02-02 | Admin can delete gift (blocked if reservations) | SATISFIED | POST /admin/gifts/{id}/delete → reservationCount() guard |
| GIFT-11 | 02-02 | Secure image upload | SATISFIED | handleImageUpload(): finfo MIME, extension whitelist, 2MB size limit, uniqid rename |
| GIFT-12 | 02-02 | Admin filter by name/status | SATISFIED | adminFilter() + filter bar GET form in admin index view |
| AUTH-01 | 02-04 | Guest signup | SATISFIED | POST /auth/guest-signup → GuestAuthController::signupPost() + UserModel::create() |
| AUTH-02 | 02-04 | Guest login | SATISFIED | POST /auth/guest-login → GuestAuthController::loginPost() + password_verify() |
| AUTH-03 | 02-04 | Auto-login after signup | SATISFIED | session_regenerate_id(true) + SESSION vars set in signupPost() |
| AUTH-05 | 02-04 | Duplicate email rejected at signup | SATISFIED | findByEmail() check before insert; JSON error returned |
| AUTH-06 | 02-04 | Guest logout | SATISFIED | POST /auth/guest-logout → session_destroy() + redirect to / |

**Note on AUTH-04:** AUTH-04 ("Admin pode fazer login") appears in ROADMAP.md's Phase 2 requirements list but was implemented in Phase 1 (AdminAuthController). It is not in the plan requirements for any Phase 2 plan and was not broken by this phase — all admin routes still require auth middleware and AdminAuthController was not modified other than adding `array $params = []` to action signatures.

---

### Anti-Patterns Found

| File | Line | Pattern | Severity | Impact |
|------|------|---------|----------|--------|
| `app/Views/admin/gifts/index.php` | 91 | `number_format((float)$gift['price'], ...)` without $e() | Info | Price is cast to float before formatting — numeric output cannot carry XSS payload; not a real risk |
| `public/assets/js/app.js` | 72 | `console.log('Gift selected:', giftId)` when user is already logged in | Info | Placeholder for Phase 3 reservation hook; documented as known stub in SUMMARY; does not block Phase 2 goal |

No blocker or warning-level anti-patterns found.

---

### Human Verification Required

#### 1. Public gift card grid visual rendering

**Test:** Navigate to `/` with at least one active gift in the DB (after running migrate_phase2.sql).
**Expected:** Bootstrap card grid renders with 3 columns on desktop, 2 on tablet, 1 on mobile. Each card shows image or placeholder icon, name, short description, category badge (if set), formatted price (if > 0), available qty, and Escolher/Indisponível button.
**Why human:** Requires live database + web server; static grep cannot verify card pixel layout or responsive breakpoints.

#### 2. Filter bar AJAX behavior

**Test:** With gift cards visible, type a partial name in the search bar; change the category dropdown; change the sort dropdown.
**Expected:** Search triggers fetch after 300ms debounce (container dims then updates). Category and sort fire immediately on change. No full page reload occurs. Card grid updates correctly.
**Why human:** Requires browser JS execution against a live /gifts/filter endpoint.

#### 3. Auth modal interaction flow

**Test:** Click "Escolher este presente" when not logged in. Fill signup form with valid data and submit.
**Expected:** Modal opens; tab switching between "Criar conta" and "Entrar" works without closing modal; successful signup shows "Tudo certo! Continuando..." then closes modal after ~1200ms.
**Why human:** Requires browser interaction and live /auth/guest-signup endpoint.

#### 4. Auth modal error display

**Test:** In the login tab, enter wrong credentials.
**Expected:** #login-error shows "E-mail ou senha incorretos." via textContent (no HTML injection risk). Error disappears on next submit attempt.
**Why human:** Requires live /auth/guest-login endpoint returning JSON error.

#### 5. Admin gift create with image upload

**Test:** Log in as admin, go to /admin/gifts/create, fill all fields, upload a valid JPG under 2MB.
**Expected:** Gift appears in admin list with thumbnail; gift also appears in public card grid at / (if is_active = 1).
**Why human:** Requires live DB and actual PHP move_uploaded_file() execution.

#### 6. Upload security validation

**Test:** Try uploading (a) a file larger than 2MB, (b) a PHP file renamed to .jpg, (c) a file with non-image MIME magic bytes.
**Expected:** Form re-renders with the error "A imagem deve ter no máximo 2MB." or "Formato inválido. Use JPG, PNG ou WebP." No file written to public/uploads/gifts/.
**Why human:** Requires actual file system and finfo extension running under PHP.

#### 7. Delete guard against reservations

**Test:** Create a reservation for a gift in the DB, then attempt to delete that gift from /admin/gifts.
**Expected:** Flash error "Este presente possui reservas associadas e não pode ser excluído." Gift remains in the list.
**Why human:** Requires live DB with a reservation row and the delete action executing SQL.

#### 8. Inactive gift absent from public listing

**Test:** Toggle a gift to inactive via the admin list (click "Inativar"), then open / in a guest browser session.
**Expected:** The inactivated gift does not appear in the card grid.
**Why human:** Requires live DB and GiftModel::getAllActive() WHERE is_active=1 query execution.

---

### Gaps Summary

No programmatically verifiable gaps found. All 15 must-have truths verified at all four levels (existence, substantive content, wiring, data flow). The 8 human verification items above cover live-runtime behaviors that require a browser, active database, and PHP server — they cannot be resolved by static code analysis.

---

_Verified: 2026-04-22T00:00:00Z_
_Verifier: Claude (gsd-verifier)_
