# Phase 3: Reserva Atômica & Meus Presentes — Research

**Researched:** 2026-04-24
**Domain:** PHP transactional reservation flow, MySQL SELECT FOR UPDATE, vanilla JS DOM update, Bootstrap 5 Toast
**Confidence:** HIGH — all findings sourced from live codebase inspection

---

<user_constraints>
## User Constraints (from CONTEXT.md)

### Locked Decisions

- **D-01:** JS `initChooseGift` (app.js) already has the Phase 3 hook for logged-in users (`loggedIn === '1'`). In that branch, POST to `/reserve` with `gift_id = window._pendingGiftId` (or `btn.dataset.giftId`).
- **D-02:** After `_onAuthSuccess()` re-clicks the button with `loggedIn='1'`, the same reservation path is triggered — no extra state needs to be preserved beyond the already-captured `gift_id`.
- **D-03:** After a successful reservation, show a toast/alert in-page (no automatic redirect to "My Gifts").
- **D-04:** The gift card MUST update visually without a page reload: decrement quantity displayed, or mark card as exhausted (overlay + disabled button) if `available_qty` reaches 0.
- **D-05:** Success toast MUST include a "Ver meus presentes" button/link as an optional action — not mandatory.
- **D-06:** No automatic redirect; user stays on the gifts page.
- **D-07:** Always reserve qty=1 per click — no quantity selector in this phase.
- **D-08:** Even for `multi`-type gifts with multiple available units, each reservation action records `quantity=1`.
- **D-09:** Route `/my-gifts` — accessible only to authenticated users with role `guest`.
- **D-10:** "Meus Presentes" nav link appears in the public layout (`layouts/main.php`) only when the user is logged in (`$_SESSION['role'] === 'guest'`).
- **D-11:** My Gifts page displays reservation list with: thumbnail, gift name, category, reservation status (reserved/confirmed/cancelled), reservation date, "Cancelar reserva" button (visible only when status is `reserved`).
- **D-12:** Cancellation: POST to `/my-gifts/cancel` with `reservation_id` → return stock transactionally → update status to `cancelled`.
- **D-13:** If no reservations: friendly empty state (message + link to gift list).
- **D-14:** Race condition/exhaustion: backend returns JSON error "Esse presente acabou de ficar indisponível."
- **D-15:** JS MUST update the corresponding card to exhausted state (overlay, disabled button) without reload.
- **D-16:** No partial reservation; transaction rolls back if `available_qty` is already 0 at UPDATE time.
- **D-17:** Mandatory backend transaction logic: `BEGIN` → `SELECT ... FOR UPDATE` on gift → check `available_qty > 0` → `INSERT INTO reservations` + `UPDATE gifts SET available_qty = available_qty - 1` → `COMMIT`. Any failure triggers `ROLLBACK`.
- **D-18:** Migration: alter `reservations.status` enum from `('active','cancelled')` to `('reserved','confirmed','cancelled')`.
- **D-19:** Add FK constraint `user_id → users(id)` on `reservations` table (column added in migrate_phase2.sql without constraint).
- **D-20:** Keep legacy `guest_name`, `guest_email`, `token` columns nullable; new reservations use `user_id`.

### Claude's Discretion

- Exact Bootstrap Toast vs fixed alert style for success feedback
- Card update animation (fade, subtle pulse)
- Exact JSON response structure from `/reserve` endpoint
- Auth middleware for `/my-gifts` route (can reuse `AuthMiddleware` with a `guest` role check)

### Deferred Ideas (OUT OF SCOPE)

- Quantity selector for multi gifts — deferred past Phase 3
- Admin cancellation of reservations — Phase 5 (RESERVE-04..06)
- Email confirmation of reservation — v2 requirements
</user_constraints>

---

<phase_requirements>
## Phase Requirements

| ID | Description | Research Support |
|----|-------------|------------------|
| STOCK-01 | `single` gift becomes unavailable after one confirmed reservation | D-17 transactional UPDATE + `available_qty` decrement to 0 |
| STOCK-02 | `multi` gift decrements `available_qty` on each reservation | D-17 same logic; decrement is always 1 per D-07 |
| STOCK-03 | `available_qty` never goes negative | D-16/D-17: SELECT FOR UPDATE + check qty > 0 before UPDATE; ROLLBACK if 0 |
| STOCK-04 | Stock decrement uses transaction with SELECT FOR UPDATE | D-17: explicit BEGIN/COMMIT pattern via `BaseModel::getDb()` |
| STOCK-05 | Two simultaneous reservations on the last unit are blocked by the transaction | MySQL InnoDB row-level lock via SELECT FOR UPDATE; second transaction waits, then finds qty=0 and rolls back |
| RESERVE-01 | After authenticating in the modal, reservation is completed automatically | D-01/D-02: `_onAuthSuccess()` re-clicks the button with `loggedIn='1'`, which hits the `/reserve` POST path |
| RESERVE-02 | Reservation recorded with user_id, gift_id, quantity, status=reserved | D-18 migration changes enum; INSERT uses these exact columns |
| RESERVE-03 | Authenticated guest can see "Meus Presentes" with their reservations | D-09..D-13: `/my-gifts` route, ReservationModel query, view with list |
</phase_requirements>

---

## Summary

Phase 3 delivers the full reservation lifecycle: clicking "Escolher este presente" when logged in triggers a POST to `/reserve`, which runs a MySQL `BEGIN → SELECT ... FOR UPDATE → check qty → INSERT + UPDATE → COMMIT` transaction. If the gift is already exhausted, the transaction rolls back and JSON error is returned; the JS updates the card to the exhausted state in-DOM. On success, a Bootstrap Toast appears with a "Ver meus presentes" link. A separate `/my-gifts` GET page lists all reservations for the session user, with inline cancel support (POST `/my-gifts/cancel`) that returns stock via the same transactional pattern.

The codebase is well-prepared: `BaseModel::getDb()` exposes the PDO instance needed for manual `beginTransaction()`/`commit()`/`rollBack()`. The JS `initChooseGift` hook and `_onAuthSuccess` re-click flow are already stubbed. The `reservations.user_id` column is already present; only the enum change and FK constraint are missing. AuthMiddleware currently hardcodes `admin` — a minor extension is needed to support `guest` role protection for `/my-gifts`.

**Primary recommendation:** Build `ReservationController` + `ReservationModel` as the core deliverable. The migration, JS wiring, and My Gifts view are well-defined; execution risk is low if the transaction pattern is followed exactly.

---

## Architectural Responsibility Map

| Capability | Primary Tier | Secondary Tier | Rationale |
|------------|-------------|----------------|-----------|
| Atomic stock decrement | API / Backend (PHP) | Database (MySQL InnoDB lock) | Business invariant — must never be in the browser; SELECT FOR UPDATE requires database-level locking |
| CSRF validation on POST /reserve | API / Backend (middleware) | — | CsrfMiddleware::validate() already runs for all POSTs via Router |
| Session auth check on /my-gifts | API / Backend (middleware) | — | Role guard must be server-side; JS-only gates are bypassable |
| Gift card DOM update (exhausted/qty) | Browser / Client (JS) | — | Avoids full-page reload; JSON response from /reserve drives the update |
| Bootstrap Toast display | Browser / Client (JS) | — | Client-side feedback pattern; no server state needed |
| Reservation list render | Frontend Server (PHP SSR) | — | /my-gifts is a standard GET → View::render(); no AJAX needed |
| Cancel reservation | API / Backend (PHP + transaction) | Database | Same transactional stock-return pattern as reserve |

---

## Standard Stack

### Core
| Library/Tool | Version | Purpose | Why Standard |
|---------|---------|---------|--------------|
| PHP PDO + InnoDB | 8.1+ / MySQL | Transactional reservation | Project constraint; `beginTransaction()` + `SELECT FOR UPDATE` is the only correct approach for this concurrency requirement |
| Bootstrap 5 Toast | 5.3.3 (CDN already loaded) | Success feedback | Already in CDN bundle; no additional load; CONTEXT.md specifies this component |
| Vanilla JS fetch | Browser native | POST /reserve from card click | No bundler; project uses vanilla JS already |

### No Additional Libraries Required

This phase uses zero new external dependencies. Everything needed is already present:
- PDO transactions via `BaseModel::getDb()` [VERIFIED: app/Models/BaseModel.php]
- Bootstrap 5.3.3 bundle via CDN (Toast included) [VERIFIED: app/Views/layouts/main.php]
- `CsrfMiddleware::token()` / `::validate()` [VERIFIED: app/Middleware/CsrfMiddleware.php]

---

## Architecture Patterns

### System Architecture Diagram

```
[Browser: btn-choose-gift click]
        |
        | loggedIn === '1' (set by _onAuthSuccess re-click)
        v
[JS: fetch POST /reserve  +  csrf_token  +  gift_id]
        |
        v
[Router: CsrfMiddleware::validate()]
        |
        v
[ReservationController::store()]
        |
        +-- validate gift_id, user session
        |
        v
[ReservationModel::reserve(giftId, userId)]
        |
   PDO beginTransaction()
        |
   SELECT id, available_qty FROM gifts WHERE id=? FOR UPDATE   <-- row lock
        |
   if available_qty == 0  --> ROLLBACK --> return error JSON
        |
   INSERT INTO reservations (user_id, gift_id, quantity, status) VALUES (?,?,1,'reserved')
   UPDATE gifts SET available_qty = available_qty - 1 WHERE id = ?
        |
   PDO commit()
        |
        v
[JSON response: {success:true, available_qty:N, is_exhausted:bool}]
        |
        v
[JS: show Bootstrap Toast + update card DOM]
        |
        +-- if is_exhausted: replace button with disabled, add overlay
        +-- else: update qty counter on card

--- separate flow ---

[Browser: GET /my-gifts]
        |
[Router: guest auth check -> redirect /gifts if not logged in]
        |
[ReservationController::myGifts()]
        |
[ReservationModel::findByUserId(userId)]  -- JOIN gifts
        |
[View::render('public/my-gifts/index', $reservations)]

--- cancel flow ---

[Browser: POST /my-gifts/cancel  +  reservation_id  +  csrf_token]
        |
[ReservationController::cancel()]
        |
[ReservationModel::cancel(reservationId, userId)]
        |
   PDO beginTransaction()
   SELECT r.id, r.status, r.gift_id WHERE r.id=? AND r.user_id=? FOR UPDATE
   if status != 'reserved' -> ROLLBACK -> error
   UPDATE reservations SET status='cancelled' WHERE id=?
   UPDATE gifts SET available_qty = available_qty + 1 WHERE id=?
   PDO commit()
        |
[Flash::set('success', ...) + redirect /my-gifts]
```

### Recommended Project Structure (new files only)

```
app/
├── Controllers/
│   └── ReservationController.php   — store(), myGifts(), cancel()
├── Models/
│   └── ReservationModel.php        — reserve(), findByUserId(), cancel(), findById()
└── Views/
    └── public/
        └── my-gifts/
            └── index.php           — reservation list view
sql/
└── migrate_phase3.sql              — enum change + FK constraint
```

### Pattern 1: SELECT FOR UPDATE Transaction

**What:** Acquires a row-level InnoDB lock for the duration of the transaction, preventing concurrent decrement below zero.

**When to use:** Every stock-decrement operation in `ReservationModel::reserve()`.

```php
// Source: VERIFIED from BaseModel::getDb() + MySQL InnoDB docs [ASSUMED for exact FOR UPDATE syntax]
public function reserve(int $giftId, int $userId): array {
    $db = $this->getDb();
    try {
        $db->beginTransaction();

        // Lock the gift row for this transaction
        $stmt = $db->prepare(
            'SELECT id, available_qty FROM gifts WHERE id = :id FOR UPDATE'
        );
        $stmt->execute([':id' => $giftId]);
        $gift = $stmt->fetch();

        if (!$gift || (int)$gift['available_qty'] <= 0) {
            $db->rollBack();
            return ['success' => false, 'error' => 'Esse presente acabou de ficar indisponível.'];
        }

        $db->prepare(
            'INSERT INTO reservations (user_id, gift_id, quantity, status, created_at)
             VALUES (:user_id, :gift_id, 1, :status, NOW())'
        )->execute([':user_id' => $userId, ':gift_id' => $giftId, ':status' => 'reserved']);

        $db->prepare(
            'UPDATE gifts SET available_qty = available_qty - 1, updated_at = NOW()
             WHERE id = :id'
        )->execute([':id' => $giftId]);

        $db->commit();

        $newQty = (int)$gift['available_qty'] - 1;
        return [
            'success'      => true,
            'available_qty' => $newQty,
            'is_exhausted' => $newQty <= 0,
        ];
    } catch (\Exception $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        // SEC-07: never expose $e->getMessage() in production
        return ['success' => false, 'error' => 'Erro ao processar reserva.'];
    }
}
```

**Note on `$this->query()` vs manual PDO:** `BaseModel::query()` is a convenience wrapper but does NOT manage transactions. For transactional work, call `$this->getDb()` directly and use `$db->prepare()` as shown above. [VERIFIED: BaseModel.php]

### Pattern 2: Guest Auth Middleware Extension

**What:** `AuthMiddleware::handle()` currently only allows `admin`. A new route-level check for `guest` is needed for `/my-gifts` without breaking admin routes.

**Options (Claude's Discretion):**
- **Option A (recommended):** Add a second static method `AuthMiddleware::requireGuest()` that checks `$_SESSION['role'] === 'guest'` and redirects to `/gifts` if not authenticated.
- **Option B:** Add a second middleware string `'auth_guest'` in routes.php and handle it in `Router::run()`.

Either option avoids modifying the existing `handle()` method and its admin routes. Option A is simpler (no Router change).

```php
// Option A — add to AuthMiddleware.php
public static function requireGuest(): void {
    if (($_SESSION['role'] ?? '') !== 'guest') {
        header('Location: /gifts');
        exit;
    }
}
```

The Router currently only dispatches `'auth'` middleware. If Option A is chosen, `ReservationController::myGifts()` calls `AuthMiddleware::requireGuest()` at the top of the method instead of relying on the router. This is consistent with how `GuestAuthController` validates session role inline. [VERIFIED: Router.php, AuthMiddleware.php]

**Alternatively:** Add `'auth_guest'` as a second middleware key in `Router::run()` — this keeps the auth concern in the middleware layer. Either approach is valid; document the choice in the plan.

### Pattern 3: ReservationController JSON Response (POST /reserve)

**Follows GuestAuthController pattern exactly.** [VERIFIED: GuestAuthController.php]

```php
public function store(array $params = []): void {
    header('Content-Type: application/json');

    // Session check (guest required)
    if (($_SESSION['role'] ?? '') !== 'guest') {
        echo json_encode(['error' => 'É necessário estar autenticado.']);
        exit;
    }

    $giftId = (int)($_POST['gift_id'] ?? 0);
    if ($giftId <= 0) {
        echo json_encode(['error' => 'Presente inválido.']);
        exit;
    }

    $result = (new ReservationModel)->reserve($giftId, (int)$_SESSION['user_id']);

    echo json_encode($result);
    exit;
}
```

### Pattern 4: JS Card Update After Reserve

**Wires into the existing `initChooseGift` stub at line ~79 of app.js.** [VERIFIED: app.js]

```javascript
// Replace: console.log('Gift selected:', giftId);
// With:
const csrfToken = document.getElementById('authModal')?.dataset.csrfToken
               ?? document.getElementById('page-meta')?.dataset.csrfToken
               ?? '';
const formData = new FormData();
formData.append('gift_id', giftId);
formData.append('csrf_token', csrfToken);

fetch('/reserve', { method: 'POST', body: formData })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            GiftPage._onReserveSuccess(giftId, data.available_qty, data.is_exhausted);
        } else {
            GiftPage._onReserveError(giftId, data.error);
        }
    })
    .catch(() => GiftPage._onReserveError(giftId, 'Erro inesperado. Tente novamente.'));
```

**CSRF token source note:** The modal's `data-csrf-token` attribute on `#authModal` is populated from `CsrfMiddleware::token()`. After `_onAuthSuccess()` hides the modal, the `data-csrf-token` value is still present in the DOM because the modal element is not removed — just hidden. The JS can safely read it. [VERIFIED: gifts/index.php lines 59-60]

**However,** after login the CSRF token is invalidated (`unset($_SESSION['csrf_token'])`) in `GuestAuthController`. This means the token baked into `data-csrf-token` at page-load is stale after login. The `_onAuthSuccess` flow reloads-via-click, so the token in `authModal.dataset.csrfToken` was set when the page was rendered before login — it will NOT match the new session's token.

**This is a critical pitfall** — see Pitfall 1 below.

### Pattern 5: Bootstrap Toast (Claude's Discretion)

```javascript
_onReserveSuccess(giftId, availableQty, isExhausted) {
    // Update card
    const card = document.querySelector(`.btn-choose-gift[data-gift-id="${giftId}"]`)
                          ?.closest('.card');
    if (card) {
        const qtyEl = card.querySelector('[data-qty]');
        if (qtyEl) qtyEl.textContent = availableQty;
        if (isExhausted) {
            const footer = card.querySelector('.card-footer');
            if (footer) {
                footer.innerHTML = '<button class="btn btn-secondary w-100" disabled>Indisponível</button>';
            }
            card.classList.add('gift-card--esgotado');
        }
    }
    // Show toast
    const toastEl = document.getElementById('reserveToast');
    if (toastEl) {
        bootstrap.Toast.getOrCreateInstance(toastEl).show();
    }
},
```

**Toast HTML** (add to `gifts/index.php` or layout, outside modal):
```html
<div class="toast-container position-fixed bottom-0 end-0 p-3">
    <div id="reserveToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header">
            <strong class="me-auto">Presente reservado!</strong>
            <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
        </div>
        <div class="toast-body">
            Reserva feita com sucesso.
            <a href="/my-gifts" class="btn btn-sm btn-outline-primary ms-2">Ver meus presentes</a>
        </div>
    </div>
</div>
```

### Pattern 6: Cancel Reservation (POST-Redirect-GET)

Cancellation uses a standard HTML form POST (not AJAX) — simpler for a full-page My Gifts view.

```php
// In my-gifts/index.php — per reservation row
<?php if ($reservation['status'] === 'reserved'): ?>
<form method="POST" action="/my-gifts/cancel">
    <input type="hidden" name="csrf_token"
           value="<?= htmlspecialchars(\App\Middleware\CsrfMiddleware::token(), ENT_QUOTES, 'UTF-8') ?>">
    <input type="hidden" name="reservation_id"
           value="<?= $e($reservation['id']) ?>">
    <button type="submit" class="btn btn-outline-danger btn-sm">Cancelar reserva</button>
</form>
<?php endif; ?>
```

After cancellation, redirect back to `/my-gifts` with a Flash success message.

### Anti-Patterns to Avoid

- **Using `$this->query()` inside a transaction block:** `query()` uses the same connection but doesn't participate in the `beginTransaction()` state tracking. Use `$db->prepare()` directly inside transactions for clarity and to avoid accidental auto-commit behavior.
- **Reading `available_qty` without FOR UPDATE then decrementing:** Any gap between read and write is a TOCTOU race. Always read inside the same transaction that will write.
- **Exposing PDO exception messages:** `$e->getMessage()` may contain table names or query details. Catch all exceptions, log internally, return generic error to user.
- **Stale CSRF token after login (see Pitfall 1):** The token in the gifts page's `authModal.dataset.csrfToken` was generated before login; after `_onAuthSuccess` the session has a new token. The `/reserve` POST will fail CSRF validation.

---

## Don't Hand-Roll

| Problem | Don't Build | Use Instead | Why |
|---------|-------------|-------------|-----|
| Row-level mutex for concurrency | Custom lock table, file locks | MySQL InnoDB SELECT FOR UPDATE | InnoDB natively supports row-level locks in transactions; host supports it |
| Toast notifications | Custom JS notification system | Bootstrap 5 Toast (already loaded) | Zero extra bytes; WCAG compliant; already on CDN |
| CSRF token for fetch requests | Custom nonce system | Existing `CsrfMiddleware::token()` from DOM | Already solved; just read from `authModal.dataset.csrfToken` or a dedicated meta tag |

---

## Common Pitfalls

### Pitfall 1: Stale CSRF Token After Login (CRITICAL)

**What goes wrong:** The gift list page renders with a CSRF token in `authModal.dataset.csrfToken`. The user authenticates via the modal; `GuestAuthController::signupPost()` / `loginPost()` calls `unset($_SESSION['csrf_token'])` to regenerate a fresh token. Now `_onAuthSuccess()` re-clicks the button, triggering the `/reserve` POST — but the JS reads the OLD token from `authModal.dataset.csrfToken`. The backend has a NEW token in `$_SESSION['csrf_token']`. Result: 403 Forbidden on every first-time reservation.

**Why it happens:** Token rotation on login is correct security practice (RESEARCH.md Phase 1, Pattern 4). The problem is the DOM still holds the pre-login token.

**How to avoid:** After successful auth, fetch a fresh CSRF token before the `/reserve` POST. Best approach: add a `/csrf-token` GET endpoint that returns `json_encode(['token' => CsrfMiddleware::token()])`, call it inside `_onAuthSuccess()` before re-clicking, store the new token in a module-level variable that `initChooseGift` reads. Alternatively, the `_onAuthSuccess` flow could do a `window.location.reload()` (already the fallback when `btn` is not found — line 199 of app.js) instead of re-clicking, at the cost of UX smoothness.

**Warning signs:** First reservation after login always returns 403; subsequent page-reload reservations work fine.

**Resolution (recommended):** `_onAuthSuccess()` fetches `/csrf-token` first, updates `authModal.dataset.csrfToken`, then re-clicks. The `/csrf-token` GET endpoint is trivial to add: one route + one 3-line controller method.

### Pitfall 2: `available_qty` Underflow on Concurrent Requests

**What goes wrong:** Two users POST to `/reserve` simultaneously for the last unit. If `SELECT FOR UPDATE` is not used, both transactions read `available_qty = 1`, both pass the > 0 check, both decrement → `available_qty = -1`.

**Why it happens:** Standard `SELECT` in MySQL does not acquire a lock, so concurrent transactions can read the same value before either has committed.

**How to avoid:** The `SELECT ... FOR UPDATE` inside `beginTransaction()` acquires an exclusive row lock. The second transaction blocks until the first commits or rolls back, then reads the updated (0) qty and aborts.

**Warning signs:** `available_qty` column going negative in the database; more reservations than `quantity` for a gift.

### Pitfall 3: Forgetting `inTransaction()` Check Before Rollback

**What goes wrong:** An exception is thrown before `beginTransaction()` is called (e.g., during connection setup), then `rollBack()` is called in the catch block → PHP warning "There is no active transaction."

**How to avoid:** Always guard rollback with `if ($db->inTransaction()) { $db->rollBack(); }`. [ASSUMED: standard PDO best practice]

### Pitfall 4: `reservations.token` NOT NULL Constraint

**What goes wrong:** The original schema has `token VARCHAR(64) NOT NULL UNIQUE` on the `reservations` table. Inserting a new reservation with `user_id` but without a `token` value will fail with a NOT NULL constraint error.

**Why it happens:** Phase 1 schema designed `token` as the primary guest identifier. Phase 3 uses `user_id` instead.

**How to avoid:** `migrate_phase3.sql` MUST alter the `token` column to `DEFAULT NULL` and drop/modify the `UNIQUE` constraint if the column remains, OR ensure the INSERT always provides a dummy token. Best: `ALTER TABLE reservations MODIFY token VARCHAR(64) DEFAULT NULL;` and `ALTER TABLE reservations DROP INDEX token;`. [VERIFIED: sql/schema.sql line 37]

Also: `guest_name VARCHAR(120) NOT NULL` and `guest_email VARCHAR(255) NOT NULL` in the original schema — these must also be made nullable for new user_id-based reservations. [VERIFIED: sql/schema.sql lines 35-36]

### Pitfall 5: Auth Check in Controller vs Middleware for `/my-gifts`

**What goes wrong:** If the route is registered without middleware and only the controller checks the session, a developer may accidentally remove the controller-level check. Alternatively, if `AuthMiddleware::handle()` is extended carelessly, admin routes could be broken.

**How to avoid:** Use Option A (a new `AuthMiddleware::requireGuest()` method called at the top of `ReservationController::myGifts()`). This is explicit, auditable, and does not modify any existing middleware behavior.

### Pitfall 6: CSRF Token Source for JS Fetch

**What goes wrong:** After the modal is hidden, `document.getElementById('authModal')` still exists in the DOM but `dataset.csrfToken` may be stale (Pitfall 1). Code that reads from `authModal.dataset.csrfToken` will silently send the wrong token.

**How to avoid:** Either refresh the token as described in Pitfall 1, or add a `<meta name="csrf-token">` tag in the layout that is refreshed on the new page state. The most robust solution for this project: fetch `/csrf-token` endpoint right before the POST.

---

## Code Examples

### migrate_phase3.sql — Complete Migration
```sql
-- migrate_phase3.sql
-- Run AFTER migrate_phase2.sql.
-- Hostinger MySQL: InnoDB engine required for FK and transactions (already set in schema.sql).

-- 1. Allow nulls on legacy Phase 1 columns (Pitfall 4)
ALTER TABLE reservations
    MODIFY guest_name  VARCHAR(120) DEFAULT NULL,
    MODIFY guest_email VARCHAR(255) DEFAULT NULL,
    MODIFY token       VARCHAR(64)  DEFAULT NULL;

-- Drop the UNIQUE index on token so NULL values are permitted for multiple rows
-- (MySQL allows multiple NULLs in a UNIQUE index since 5.7, but explicit drop avoids confusion)
ALTER TABLE reservations DROP INDEX token;

-- 2. Change status enum to include 'reserved' (RESERVE-02, D-18)
ALTER TABLE reservations
    MODIFY status ENUM('reserved','confirmed','cancelled') NOT NULL DEFAULT 'reserved';

-- 3. Add FK constraint on user_id (D-19)
-- user_id column already exists from migrate_phase2.sql
ALTER TABLE reservations
    ADD CONSTRAINT fk_res_user
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE;
```

**Note on `ON DELETE SET NULL`:** If a user account is ever deleted, reservation records are preserved with `user_id = NULL` rather than cascade-deleted. This is the safer default for an audit trail. `ON DELETE CASCADE` would be incorrect here. [ASSUMED: standard FK design for audit-preserving systems]

### ReservationModel skeleton
```php
<?php
declare(strict_types=1);

namespace App\Models;

class ReservationModel extends BaseModel {

    public function reserve(int $giftId, int $userId): array {
        $db = $this->getDb();
        try {
            $db->beginTransaction();
            $stmt = $db->prepare(
                'SELECT id, available_qty FROM gifts WHERE id = :id AND is_active = 1 FOR UPDATE'
            );
            $stmt->execute([':id' => $giftId]);
            $gift = $stmt->fetch();

            if (!$gift || (int)$gift['available_qty'] <= 0) {
                $db->rollBack();
                return ['success' => false, 'error' => 'Esse presente acabou de ficar indisponível.'];
            }

            $db->prepare(
                'INSERT INTO reservations (user_id, gift_id, quantity, status, created_at)
                 VALUES (:uid, :gid, 1, :status, NOW())'
            )->execute([':uid' => $userId, ':gid' => $giftId, ':status' => 'reserved']);

            $db->prepare(
                'UPDATE gifts SET available_qty = available_qty - 1, updated_at = NOW()
                 WHERE id = :id'
            )->execute([':id' => $giftId]);

            $db->commit();

            $newQty = (int)$gift['available_qty'] - 1;
            return ['success' => true, 'available_qty' => $newQty, 'is_exhausted' => $newQty <= 0];

        } catch (\Exception) {
            if ($db->inTransaction()) $db->rollBack();
            return ['success' => false, 'error' => 'Erro ao processar reserva.'];
        }
    }

    public function findByUserId(int $userId): array {
        return $this->query(
            'SELECT r.id, r.gift_id, r.quantity, r.status, r.created_at,
                    g.name AS gift_name, g.category, g.image_path
             FROM reservations r
             JOIN gifts g ON g.id = r.gift_id
             WHERE r.user_id = :uid
             ORDER BY r.created_at DESC',
            [':uid' => $userId]
        )->fetchAll();
    }

    public function findByIdAndUser(int $id, int $userId): array|false {
        return $this->query(
            'SELECT * FROM reservations WHERE id = :id AND user_id = :uid LIMIT 1',
            [':id' => $id, ':uid' => $userId]
        )->fetch();
    }

    public function cancel(int $id, int $userId): array {
        $db = $this->getDb();
        try {
            $db->beginTransaction();

            $stmt = $db->prepare(
                'SELECT id, gift_id, status FROM reservations
                 WHERE id = :id AND user_id = :uid FOR UPDATE'
            );
            $stmt->execute([':id' => $id, ':uid' => $userId]);
            $res = $stmt->fetch();

            if (!$res || $res['status'] !== 'reserved') {
                $db->rollBack();
                return ['success' => false, 'error' => 'Reserva não encontrada ou não cancelável.'];
            }

            $db->prepare(
                'UPDATE reservations SET status = :status WHERE id = :id'
            )->execute([':status' => 'cancelled', ':id' => $id]);

            $db->prepare(
                'UPDATE gifts SET available_qty = available_qty + 1, updated_at = NOW()
                 WHERE id = :gid'
            )->execute([':gid' => $res['gift_id']]);

            $db->commit();
            return ['success' => true];

        } catch (\Exception) {
            if ($db->inTransaction()) $db->rollBack();
            return ['success' => false, 'error' => 'Erro ao cancelar reserva.'];
        }
    }
}
```

### New routes to add to config/routes.php
```php
// GET
'/my-gifts'         => ['ReservationController', 'myGifts'],
// POST
'/reserve'          => ['ReservationController', 'store'],
'/my-gifts/cancel'  => ['ReservationController', 'cancel'],
```

Note: `/my-gifts` does not use the `'auth'` middleware string because the current `AuthMiddleware::handle()` is admin-only. The controller calls `AuthMiddleware::requireGuest()` directly (Option A).

---

## State of the Art

| Old Approach | Current Approach | When Changed | Impact |
|--------------|------------------|--------------|--------|
| `reservations.status = 'active'` | `status = 'reserved'` (entry state) | Phase 3 migration | INSERT must use `'reserved'`, not `'active'` |
| `token` column NOT NULL UNIQUE | `token` column nullable, index dropped | Phase 3 migration | New reservations do not require a token |
| `guest_name`/`guest_email` NOT NULL | Both nullable | Phase 3 migration | New reservations do not require these fields |

---

## Open Questions

1. **CSRF token after login**
   - What we know: `_onAuthSuccess` re-clicks the button immediately after hiding modal; the session token was rotated during login; the DOM token is stale.
   - What's unclear: Whether to add a `/csrf-token` endpoint (adds complexity) or accept a page reload fallback.
   - Recommendation: Add `/csrf-token` GET endpoint (3-line method, 1 route). It is the correct solution and prevents confusing 403 errors for first-time reservers.

2. **Guest middleware approach**
   - What we know: Two valid options (new method vs new Router middleware string).
   - Recommendation: Option A (`AuthMiddleware::requireGuest()` called in controller) — zero Router changes, auditable.

3. **`ON DELETE` behavior for `fk_res_user`**
   - What we know: D-20 says keep columns nullable; if a user is deleted, no Phase 3 requirement dictates what happens to their reservations.
   - Recommendation: `ON DELETE SET NULL` — preserves reservation history without FK violation.

---

## Environment Availability

Step 2.6: SKIPPED (no new external dependencies — all capabilities rely on MySQL InnoDB + PHP PDO already in use, and Bootstrap 5 already on CDN).

---

## Validation Architecture

### Test Framework

| Property | Value |
|----------|-------|
| Framework | Manual browser testing + SQL inspection (no automated test framework configured) |
| Config file | none |
| Quick run command | `php -S localhost:8000 -t public` |
| Full suite command | Manual verification against Phase 3 success criteria |

### Phase Requirements → Test Map

| Req ID | Behavior | Test Type | Command / Method | Automated? |
|--------|----------|-----------|-----------------|-----------|
| STOCK-01 | Single gift becomes unavailable after 1 reservation | Manual | Reserve single gift; verify card updates to disabled | Manual only |
| STOCK-02 | Multi gift decrements available_qty | Manual | Reserve multi gift; verify qty counter decrements | Manual only |
| STOCK-03 | available_qty never negative | SQL inspection | `SELECT * FROM gifts WHERE available_qty < 0` | Manual SQL |
| STOCK-04 | Transaction uses SELECT FOR UPDATE | Code review | Verify `FOR UPDATE` clause in ReservationModel::reserve() | Code review |
| STOCK-05 | Concurrent reservation of last unit: only one succeeds | Manual concurrency | Open two browser tabs, reserve same last-unit gift simultaneously | Manual only |
| RESERVE-01 | Post-auth reservation completes automatically | Manual | Register/login in modal; verify reservation created without extra click | Manual only |
| RESERVE-02 | Reservation saved with correct fields | SQL inspection | `SELECT * FROM reservations ORDER BY id DESC LIMIT 1` | Manual SQL |
| RESERVE-03 | /my-gifts shows user's reservations | Manual | Navigate to /my-gifts as logged-in guest; verify list | Manual only |

### Phase Success Criteria

1. After signup in modal, reservation is created with `status=reserved` without extra user action
2. `available_qty` is decremented; single gift shows as exhausted
3. Concurrency test: two simultaneous requests for last unit → exactly one reservation created
4. `available_qty` never goes negative in the database
5. `/my-gifts` shows logged-in user's reservations with name and status

---

## Security Domain

### Applicable ASVS Categories

| ASVS Category | Applies | Standard Control |
|---------------|---------|-----------------|
| V2 Authentication | yes | Session check `$_SESSION['role'] === 'guest'` before reserve; `AuthMiddleware::requireGuest()` |
| V3 Session Management | yes | `session_regenerate_id(true)` already called on login (Phase 2); no new session ops in Phase 3 |
| V4 Access Control | yes | User can only cancel their own reservation (`WHERE user_id = :uid` in cancel query) |
| V5 Input Validation | yes | `gift_id` and `reservation_id` cast to `(int)`, validated > 0 before DB query |
| V6 Cryptography | no | No new crypto operations |

### Known Threat Patterns

| Pattern | STRIDE | Standard Mitigation |
|---------|--------|---------------------|
| IDOR on cancel (cancel another user's reservation) | Tampering | `WHERE id = :id AND user_id = :uid` — ownership always verified |
| Negative stock via race condition | Tampering | SELECT FOR UPDATE + ROLLBACK if qty = 0 |
| CSRF on POST /reserve and /my-gifts/cancel | Tampering | `CsrfMiddleware::validate()` via Router (all POSTs) |
| SQL injection via gift_id | Tampering | Cast to `(int)`, used only in prepared statement parameter |
| Unauthenticated /reserve call | Elevation of Privilege | Session role check at top of `store()` before any DB operation |

---

## Assumptions Log

| # | Claim | Section | Risk if Wrong |
|---|-------|---------|---------------|
| A1 | `ON DELETE SET NULL` is preferred FK behavior when user accounts are deleted | migrate_phase3.sql example | Low — this can be changed in migration without code impact |
| A2 | `if ($db->inTransaction()) $db->rollBack()` is the correct guard pattern | Pattern 1, Pitfall 3 | Low — PDO docs confirm this is the standard defensive pattern |
| A3 | MySQL 5.7+ (Hostinger) supports ENUM modification without data loss when adding a new value | migrate_phase3.sql | Low — MySQL ALTER TABLE MODIFY for ENUM is non-destructive for existing valid values |

---

## Sources

### Primary (HIGH confidence — verified from live codebase)
- `app/Models/BaseModel.php` — `getDb()` PDO singleton, `query()` helper
- `app/Models/GiftModel.php` — findById, update patterns, available_qty column confirmed
- `app/Controllers/GuestAuthController.php` — JSON response pattern, CSRF handling, session setup
- `app/Router.php` — middleware dispatch, dynamic segment support
- `config/routes.php` — existing route shape to extend
- `app/Middleware/AuthMiddleware.php` — admin-only; guest variant needed
- `app/Middleware/CsrfMiddleware.php` — token rotation on login confirmed
- `app/Views/public/gifts/index.php` — CSRF token in `authModal.dataset`, `#page-meta` element
- `app/Views/public/gifts/_cards.php` — `btn-choose-gift` data attributes confirmed
- `public/assets/js/app.js` — Phase 3 stub at line ~79, `_onAuthSuccess` flow confirmed
- `sql/schema.sql` — reservations table structure: `token NOT NULL`, `guest_name NOT NULL`, `guest_email NOT NULL`, status enum `('active','cancelled')`
- `sql/migrate_phase2.sql` — `user_id` column added without FK

### Secondary (MEDIUM confidence — standard MySQL/PHP knowledge, consistent with codebase patterns)
- MySQL InnoDB SELECT FOR UPDATE row locking behavior
- Bootstrap 5 Toast API (`bootstrap.Toast.getOrCreateInstance()`)
- PDO `beginTransaction()` / `commit()` / `rollBack()` / `inTransaction()` API

---

## Metadata

**Confidence breakdown:**
- Standard stack: HIGH — all libraries already in the project
- Architecture: HIGH — verified from live codebase; no guesswork on integration points
- Transaction pattern: HIGH — BaseModel exposes getDb() exactly as needed; MySQL InnoDB confirmed
- CSRF stale-token pitfall: HIGH — traced through GuestAuthController login flow + app.js _onAuthSuccess
- Pitfall about schema NOT NULL constraints: HIGH — verified from sql/schema.sql

**Research date:** 2026-04-24
**Valid until:** 2026-10-24 (stable PHP/MySQL APIs; Bootstrap 5.3 LTS)