# Phase 3: Reserva Atômica & Meus Presentes - Pattern Map

**Mapped:** 2026-04-24
**Files analyzed:** 8
**Analogs found:** 8 / 8

---

## File Classification

| New/Modified File | Role | Data Flow | Closest Analog | Match Quality |
|---|---|---|---|---|
| `sql/migrate_phase3.sql` | migration | batch | `sql/schema.sql` + `sql/migrate_phase2.sql` | role-match |
| `app/Models/ReservationModel.php` | model | CRUD + transaction | `app/Models/GiftModel.php` | role-match |
| `app/Controllers/ReservationController.php` | controller | request-response (JSON + redirect) | `app/Controllers/GuestAuthController.php` (JSON methods) + `app/Controllers/AdminGiftController.php` (view render + redirect) | exact split |
| `app/Views/public/my-gifts/index.php` | view | request-response | `app/Views/public/gifts/index.php` | role-match |
| `public/assets/js/app.js` | utility/frontend | event-driven | self (existing `initChooseGift` + `initAuthModal` patterns) | exact |
| `config/routes.php` | config | — | self (existing route table) | exact |
| `app/Middleware/AuthMiddleware.php` | middleware | request-response | self (existing `handle()` method) | exact |
| `app/Views/layouts/main.php` | view/layout | request-response | self (existing nav block) | exact |

---

## Pattern Assignments

---

### `sql/migrate_phase3.sql` (migration, batch)

**Analog:** `sql/schema.sql` lines 32-42, `sql/migrate_phase2.sql` (not shown — consulted for column additions pattern)

**Schema state to migrate from** (`sql/schema.sql` lines 32-42):
```sql
CREATE TABLE IF NOT EXISTS reservations (
    id            INT UNSIGNED     AUTO_INCREMENT PRIMARY KEY,
    gift_id       INT UNSIGNED     NOT NULL,
    guest_name    VARCHAR(120)     NOT NULL,
    guest_email   VARCHAR(255)     NOT NULL,
    token         VARCHAR(64)      NOT NULL UNIQUE,
    status        ENUM('active','cancelled') NOT NULL DEFAULT 'active',
    created_at    TIMESTAMP        DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_res_gift FOREIGN KEY (gift_id)
        REFERENCES gifts (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**What the migration must fix (derived from schema above + RESEARCH.md Pitfall 4 + D-18/D-19):**
1. `guest_name` and `guest_email` are `NOT NULL` — must become nullable (Pitfall 4)
2. `token` is `NOT NULL UNIQUE` — must become nullable and unique index dropped (Pitfall 4)
3. `status` enum is `('active','cancelled')` — must become `('reserved','confirmed','cancelled')` (D-18)
4. `user_id` column already added by migrate_phase2.sql but without FK — add FK constraint (D-19)

**Migration pattern to follow** (RESEARCH.md Code Examples):
```sql
-- 1. Allow nulls on legacy Phase 1 columns
ALTER TABLE reservations
    MODIFY guest_name  VARCHAR(120) DEFAULT NULL,
    MODIFY guest_email VARCHAR(255) DEFAULT NULL,
    MODIFY token       VARCHAR(64)  DEFAULT NULL;

-- 2. Drop UNIQUE index on token (multiple NULLs are fine in MySQL 5.7+ unique index,
--    but explicit drop avoids ambiguity)
ALTER TABLE reservations DROP INDEX token;

-- 3. Change status enum (RESERVE-02, D-18)
ALTER TABLE reservations
    MODIFY status ENUM('reserved','confirmed','cancelled') NOT NULL DEFAULT 'reserved';

-- 4. Add FK constraint on user_id (D-19)
ALTER TABLE reservations
    ADD CONSTRAINT fk_res_user
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE;
```

---

### `app/Models/ReservationModel.php` (model, CRUD + transaction)

**Analog:** `app/Models/GiftModel.php` (role-match — same BaseModel inheritance, same `$this->query()` pattern) + `app/Models/BaseModel.php` (provides `getDb()` for transactions)

**Imports / class declaration pattern** (`app/Models/GiftModel.php` lines 1-6):
```php
<?php
declare(strict_types=1);

namespace App\Models;

class GiftModel extends BaseModel {
```

Copy exactly — replace `GiftModel` with `ReservationModel`.

**`getDb()` accessor for manual transactions** (`app/Models/BaseModel.php` lines 23-25):
```php
public function getDb(): \PDO {
    return $this->db;
}
```

Call as `$db = $this->getDb();` then use `$db->beginTransaction()` / `$db->commit()` / `$db->rollBack()`. Never use `$this->query()` inside a transaction block — call `$db->prepare()` directly.

**`$this->query()` helper pattern for non-transactional reads** (`app/Models/BaseModel.php` lines 27-31):
```php
protected function query(string $sql, array $params = []): \PDOStatement {
    $stmt = $this->db->prepare($sql);
    $stmt->execute($params);
    return $stmt;
}
```

Use this for `findByUserId()` and `findByIdAndUser()` (read-only, no transaction needed).

**`findById` named-parameter pattern** (`app/Models/GiftModel.php` lines 77-82):
```php
public function findById(int $id): array|false {
    return $this->query(
        'SELECT * FROM gifts WHERE id = :id LIMIT 1',
        [':id' => $id]
    )->fetch();
}
```

Mirror for `findByIdAndUser(int $id, int $userId): array|false` — add `AND user_id = :uid`.

**Transaction + SELECT FOR UPDATE core pattern** (RESEARCH.md Pattern 1, verified against BaseModel):
```php
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
```

**Critical:** `if ($db->inTransaction()) $db->rollBack()` — always guard rollback (RESEARCH.md Pitfall 3).

**Cancel transaction pattern** (same structure as `reserve()`, stock returned):
```php
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

        $db->prepare('UPDATE reservations SET status = :status WHERE id = :id')
           ->execute([':status' => 'cancelled', ':id' => $id]);

        $db->prepare(
            'UPDATE gifts SET available_qty = available_qty + 1, updated_at = NOW() WHERE id = :gid'
        )->execute([':gid' => $res['gift_id']]);

        $db->commit();
        return ['success' => true];

    } catch (\Exception) {
        if ($db->inTransaction()) $db->rollBack();
        return ['success' => false, 'error' => 'Erro ao cancelar reserva.'];
    }
}
```

**`findByUserId` with JOIN** (uses `$this->query()` — no transaction):
```php
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
```

---

### `app/Controllers/ReservationController.php` (controller, request-response)

**Analog A (JSON methods):** `app/Controllers/GuestAuthController.php` lines 1-99
**Analog B (view render + Flash + redirect):** `app/Controllers/AdminGiftController.php` lines 1-24

**Imports / class declaration pattern** (`app/Controllers/GuestAuthController.php` lines 1-8):
```php
<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\UserModel;

class GuestAuthController {
```

For `ReservationController`:
```php
<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\ReservationModel;
use App\Middleware\AuthMiddleware;
use App\Helpers\View;
use App\Helpers\Flash;
```

**JSON response pattern — `store()` method** (`app/Controllers/GuestAuthController.php` lines 10-57):
```php
public function signupPost(array $params = []): void {
    header('Content-Type: application/json');
    // CsrfMiddleware::validate() already called by Router for all POST requests

    $name = trim($_POST['name'] ?? '');
    // ... validate ...
    if ($name === '') {
        echo json_encode(['error' => 'Preencha todos os campos obrigatórios.']);
        exit;
    }
    // ... model call ...
    echo json_encode(['success' => true]);
    exit;
}
```

Apply same structure for `store()`:
- `header('Content-Type: application/json');` — first line
- Session role check before any DB work: `if (($_SESSION['role'] ?? '') !== 'guest') { echo json_encode([...]); exit; }`
- Cast and validate `$giftId = (int)($_POST['gift_id'] ?? 0); if ($giftId <= 0) {...}`
- Delegate to model, echo result, exit

**Guest auth guard pattern** — new `AuthMiddleware::requireGuest()` (see Middleware section below). Call at top of `myGifts()` and `cancel()`:
```php
public function myGifts(array $params = []): void {
    AuthMiddleware::requireGuest();
    // ... rest of method
}
```

**View render pattern** (`app/Controllers/AdminGiftController.php` lines 13-24):
```php
public function index(array $params = []): void {
    $gifts = (new GiftModel)->adminFilter($search, $status);
    View::render('admin/gifts/index', [
        'title'  => 'Presentes',
        'layout' => 'admin',
        'gifts'  => $gifts,
    ]);
}
```

For `myGifts()`:
```php
$reservations = (new ReservationModel)->findByUserId((int)$_SESSION['user_id']);
View::render('public/my-gifts/index', [
    'title'        => 'Meus Presentes',
    'reservations' => $reservations,
]);
```

**Flash + redirect pattern** (`app/Controllers/AdminGiftController.php` — store/update methods):
```php
Flash::set('success', 'Reserva cancelada com sucesso.');
header('Location: /my-gifts');
exit;
```

---

### `app/Views/public/my-gifts/index.php` (view, request-response)

**Analog:** `app/Views/public/gifts/index.php` (structure) + `app/Views/public/gifts/_cards.php` (card/loop pattern)

**Layout wrapper convention** — the view file does NOT include the layout tag; `View::render()` wraps it in `layouts/main.php` automatically. The view just outputs inner HTML.

**`$e()` closure usage** (`app/Views/public/gifts/_cards.php` lines 29, 42):
```php
<?= $e($gift['name']) ?>
<?= $e($gift['image_path']) ?>
```

Use `$e()` for every user-data echo. Never raw `echo`.

**Empty state pattern** (`app/Views/public/gifts/_cards.php` lines 12-17):
```php
<?php if (empty($gifts)): ?>
<div class="col-12 text-center py-5 text-muted">
    <i class="bi bi-gift fs-1 d-block mb-3"></i>
    <p class="fs-5 fw-semibold">Nenhum presente encontrado</p>
    <p class="small">Tente ajustar os filtros ou a busca.</p>
</div>
<?php else: ?>
```

For "Meus Presentes" empty state (D-13):
```php
<?php if (empty($reservations)): ?>
<div class="text-center py-5 text-muted">
    <i class="bi bi-gift fs-1 d-block mb-3"></i>
    <p class="fs-5 fw-semibold">Você ainda não reservou nenhum presente</p>
    <a href="/gifts" class="btn btn-primary mt-2">Ver lista de presentes</a>
</div>
<?php else: ?>
```

**CSRF token in form pattern** (`app/Views/public/gifts/index.php` line 59):
```php
data-csrf-token="<?= htmlspecialchars(\App\Middleware\CsrfMiddleware::token(), ENT_QUOTES, 'UTF-8') ?>"
```

For cancel form (RESEARCH.md Pattern 6):
```php
<form method="POST" action="/my-gifts/cancel">
    <input type="hidden" name="csrf_token"
           value="<?= htmlspecialchars(\App\Middleware\CsrfMiddleware::token(), ENT_QUOTES, 'UTF-8') ?>">
    <input type="hidden" name="reservation_id" value="<?= $e($reservation['id']) ?>">
    <button type="submit" class="btn btn-outline-danger btn-sm">Cancelar reserva</button>
</form>
```

**Status badge pattern** — derive from `_cards.php` badge pattern (`_cards.php` line 50):
```php
<span class="badge bg-secondary mb-2"><?= $e($gift['category']) ?></span>
```

For reservation status:
```php
<?php $badgeClass = match($reservation['status']) {
    'reserved'  => 'bg-warning text-dark',
    'confirmed' => 'bg-success',
    'cancelled' => 'bg-secondary',
    default     => 'bg-light text-dark',
}; ?>
<span class="badge <?= $badgeClass ?>"><?= $e($reservation['status']) ?></span>
```

---

### `public/assets/js/app.js` (utility/frontend, event-driven — modify)

**Analog:** self — existing `initChooseGift()`, `initAuthModal()`, `_onAuthSuccess()` in the same file.

**Existing Phase 3 stub to replace** (`app.js` lines 78-81):
```javascript
} else {
    // Phase 3 hook — reservation will be wired here
    console.log('Gift selected:', giftId);
}
```

**Fetch + FormData POST pattern** (`app.js` lines 121-144 — `initAuthModal` signup):
```javascript
const csrfToken = modalEl.dataset.csrfToken;
const formData  = new FormData(formSignup);
formData.append('csrf_token', csrfToken);

fetch('/auth/guest-signup', { method: 'POST', body: formData })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            GiftPage._onAuthSuccess();
        } else if (signupError) {
            signupError.textContent = data.error;
            signupError.classList.remove('d-none');
        }
    })
    .catch(() => {
        if (signupError) {
            signupError.textContent = 'Erro inesperado. Tente novamente.';
            signupError.classList.remove('d-none');
        }
    });
```

**CRITICAL — CSRF token refresh before `/reserve` POST** (RESEARCH.md Pitfall 1):

After `_onAuthSuccess()` the session CSRF token has been rotated (see `GuestAuthController` line 53: `unset($_SESSION['csrf_token'])`). The `authModal.dataset.csrfToken` is stale. Solution: fetch `/csrf-token` GET endpoint first, store result in a module variable, then POST.

```javascript
// Add a module-level variable at top of GiftPage object or before init():
_freshCsrfToken: null,

// In _onAuthSuccess(), before re-clicking btn:
fetch('/csrf-token')
    .then(r => r.json())
    .then(data => {
        GiftPage._freshCsrfToken = data.token;
        // then proceed with btn.click() or direct reserve call
    });
```

**Card DOM update pattern** (RESEARCH.md Pattern 5):
```javascript
_onReserveSuccess(giftId, availableQty, isExhausted) {
    const card = document.querySelector(`.btn-choose-gift[data-gift-id="${giftId}"]`)
                          ?.closest('.card');
    if (card) {
        // qty counter — matches _cards.php "Disponível: X" text node
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
    const toastEl = document.getElementById('reserveToast');
    if (toastEl) bootstrap.Toast.getOrCreateInstance(toastEl).show();
},

_onReserveError(giftId, message) {
    // Mark card exhausted if it was a stock error
    const card = document.querySelector(`.btn-choose-gift[data-gift-id="${giftId}"]`)
                          ?.closest('.card');
    if (card) {
        const footer = card.querySelector('.card-footer');
        if (footer) {
            footer.innerHTML = '<button class="btn btn-secondary w-100" disabled>Indisponível</button>';
        }
        card.classList.add('gift-card--esgotado');
    }
},
```

**Note on `data-qty` attribute:** `_cards.php` currently renders the qty as plain text (`Disponível: <?= $e($gift['available_qty']) ?>`). To support JS DOM update, add `data-qty` to that `<p>` element: `<p class="small text-muted mt-auto mb-0" data-qty="...">`. This is a minor coordinated change between `_cards.php` and `app.js`.

**Bootstrap Toast HTML** to add to `app/Views/public/gifts/index.php` (outside modal, before closing `</div>` of main container):
```html
<div class="toast-container position-fixed bottom-0 end-0 p-3">
    <div id="reserveToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header">
            <strong class="me-auto">Presente reservado!</strong>
            <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Fechar"></button>
        </div>
        <div class="toast-body">
            Reserva feita com sucesso.
            <a href="/my-gifts" class="btn btn-sm btn-outline-primary ms-2">Ver meus presentes</a>
        </div>
    </div>
</div>
```

---

### `config/routes.php` (config — modify)

**Analog:** self (`config/routes.php` lines 1-26)

**Existing route shape** (lines 5-26):
```php
return [
    'GET' => [
        '/gifts/filter' => ['GiftController', 'filter'],
        // ...
    ],
    'POST' => [
        '/auth/guest-signup' => ['GuestAuthController', 'signupPost'],
        // ...
    ],
];
```

**Three routes to add:**
```php
// GET section — add:
'/my-gifts'        => ['ReservationController', 'myGifts'],
'/csrf-token'      => ['ReservationController', 'csrfToken'],

// POST section — add:
'/reserve'         => ['ReservationController', 'store'],
'/my-gifts/cancel' => ['ReservationController', 'cancel'],
```

Note: `/my-gifts` does NOT use the `'auth'` middleware string because `AuthMiddleware::handle()` is admin-only. Auth is handled by `AuthMiddleware::requireGuest()` called at the top of the controller method (Option A from RESEARCH.md Pattern 2). `/csrf-token` is a public GET — no middleware.

---

### `app/Middleware/AuthMiddleware.php` (middleware — modify)

**Analog:** self (`app/Middleware/AuthMiddleware.php` lines 1-13)

**Existing pattern** (lines 1-13):
```php
<?php
declare(strict_types=1);

namespace App\Middleware;

class AuthMiddleware {
    public static function handle(): void {
        if (($_SESSION['role'] ?? '') !== 'admin') {
            header('Location: /admin/login');
            exit;
        }
    }
}
```

**New method to add** — do not modify `handle()`:
```php
public static function requireGuest(): void {
    if (($_SESSION['role'] ?? '') !== 'guest') {
        header('Location: /gifts');
        exit;
    }
}
```

---

### `app/Views/layouts/main.php` (layout — modify)

**Analog:** self (`app/Views/layouts/main.php` lines 22-30)

**Existing nav items block** (lines 22-30):
```php
<ul class="navbar-nav ms-auto">
    <li class="nav-item">
        <a class="nav-link" href="/gifts">Presentes</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="/pix">Pix</a>
    </li>
</ul>
```

**Add conditional "Meus Presentes" link** (D-10) after existing nav items:
```php
<?php if (($_SESSION['role'] ?? '') === 'guest'): ?>
<li class="nav-item">
    <a class="nav-link" href="/my-gifts">Meus Presentes</a>
</li>
<?php endif; ?>
```

---

## Shared Patterns

### CSRF Token in Forms (HTML views)
**Source:** `app/Views/public/gifts/index.php` line 59 + `app/Middleware/CsrfMiddleware.php` line 7
**Apply to:** Cancel form in `my-gifts/index.php`
```php
value="<?= htmlspecialchars(\App\Middleware\CsrfMiddleware::token(), ENT_QUOTES, 'UTF-8') ?>"
```

### Output Escaping
**Source:** `app/Helpers/View.php` line 9, used throughout `_cards.php`
**Apply to:** Every echo of user/DB data in `my-gifts/index.php`
```php
$e = fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
// Usage: <?= $e($reservation['gift_name']) ?>
```

### JSON Response Termination
**Source:** `app/Controllers/GuestAuthController.php` lines 55-56
**Apply to:** `ReservationController::store()` and `ReservationController::csrfToken()`
```php
echo json_encode($result);
exit;
```

### Flash + Redirect (POST-Redirect-GET)
**Source:** `app/Helpers/Flash.php` + `app/Controllers/AdminGiftController.php` (store/update methods)
**Apply to:** `ReservationController::cancel()` after successful cancellation
```php
Flash::set('success', 'Reserva cancelada com sucesso.');
header('Location: /my-gifts');
exit;
```
The layout (`main.php` lines 34-39) already renders Flash messages automatically.

### Session Role Check (inline)
**Source:** `app/Controllers/GuestAuthController.php` lines 10-12 (implicit — CSRF already done by Router)
**Apply to:** `ReservationController::store()` before any DB operation
```php
if (($_SESSION['role'] ?? '') !== 'guest') {
    echo json_encode(['error' => 'É necessário estar autenticado.']);
    exit;
}
```

### Transaction Guard Pattern
**Source:** `app/Models/BaseModel.php` line 23 (`getDb()`)
**Apply to:** Both `ReservationModel::reserve()` and `ReservationModel::cancel()`
```php
$db = $this->getDb();
try {
    $db->beginTransaction();
    // ... work ...
    $db->commit();
} catch (\Exception) {
    if ($db->inTransaction()) $db->rollBack();
    return ['success' => false, 'error' => 'Erro genérico.'];
}
```

---

## No Analog Found

No files in Phase 3 are entirely without analog. All files have either an exact or role-match analog in the existing codebase.

| File | Role | Data Flow | Note |
|------|------|-----------|------|
| `sql/migrate_phase3.sql` | migration | batch | `migrate_phase2.sql` exists but was not visible during analysis — follow same ALTER TABLE pattern as `schema.sql` |

---

## Metadata

**Analog search scope:** `app/Controllers/`, `app/Models/`, `app/Middleware/`, `app/Helpers/`, `app/Views/`, `config/`, `public/assets/js/`, `sql/`
**Files scanned:** 15
**Pattern extraction date:** 2026-04-24
