# Phase 2: Presentes Públicos & CRUD Admin - Research

**Researched:** 2026-04-22
**Domain:** PHP 8.1 MVC — public gift listing with AJAX filter, guest auth modal, admin CRUD with image upload
**Confidence:** HIGH

---

<user_constraints>
## User Constraints (from CONTEXT.md)

### Locked Decisions

**Cards de presentes (página pública)**
- D-01: Layout em grid responsivo — 3 colunas desktop, 2 tablet, 1 mobile
- D-02: Cada card exibe: imagem, nome, descrição curta, categoria (se houver), preço sugerido (se houver), quantidade disponível, status visual, botão "Escolher presente"
- D-03: Status visual com três estados: disponível (default), esgotado (card visualmente escurecido/opaco, botão desabilitado), destacado (selo/badge discreto no canto do card)
- D-04: Item esgotado: botão "Escolher presente" desabilitado com estilo cinza — visualmente inequívoco
- D-05: Item destacado: badge sutil (ex: fita, estrela ou label no canto superior) — sem exagero visual

**Filtro / busca / ordenação**
- D-06: Funciona via fetch + JS sem reload de página — PHP retorna HTML dos cards prontos (server-side rendering via AJAX) para um endpoint `/gifts/filter`
- D-07: Busca por nome com debounce leve (~300ms)
- D-08: Filtro por categoria (dropdown ou chips)
- D-09: Ordenação por: disponíveis primeiro, menor preço, maior preço, destacados primeiro
- D-10: Sem paginação nesta fase — todos os presentes carregados de uma vez

**Modal de autenticação do convidado**
- D-11: Modal abre ao clicar "Escolher presente" se o usuário não estiver autenticado
- D-12: Modal tem duas abas: "Criar conta" e "Entrar" — alternáveis sem fechar o modal
- D-13: Campos do cadastro: nome, e-mail, telefone, senha
- D-14: Após login/cadastro bem-sucedido: modal fecha, usuário permanece na página de presentes
- D-15: Feedback visual claro no próprio modal: mensagem de erro inline e mensagem de sucesso antes de fechar
- D-16: O presente-alvo (gift_id) deve ser preservado enquanto o modal está aberto para Phase 3

**CRUD admin de presentes**
- D-17: Campos: nome, slug, descrição, categoria, preço sugerido, tipo (single/multi), quantidade total, quantidade disponível, destaque (checkbox), ativo/inativo (toggle), imagem
- D-18: Upload de imagem: preview antes de salvar, validação MIME real via finfo, extensão whitelist (jpg, jpeg, png, webp), tamanho máximo ~2MB, renomear arquivo ao salvar em `public/uploads/gifts/`
- D-19: Inativar ≠ excluir: inativar remove da vitrine pública (active=0), excluir bloqueado se há reservas
- D-20: Listagem admin: tabela com colunas (imagem thumb, nome, categoria, preço, quantidade disponível, status, ações)
- D-21: Ações rápidas: editar, inativar/ativar (toggle inline), excluir (com confirmação)
- D-22: Listagem admin com busca por nome e filtro por status (ativo/inativo/todos)

### Claude's Discretion
- Estilo exato do badge "destacado" (fita, estrela, label — o que ficar mais elegante com Bootstrap 5)
- Cor/ícone do estado esgotado
- Geração do slug automático (JS no frontend ao digitar o nome)
- Estrutura exata do endpoint `/gifts/filter` (query params vs POST body)
- Tratamento de imagem ausente (placeholder ou sem imagem)

### Deferred Ideas (OUT OF SCOPE)
- Reserva automática após autenticação — Phase 3
- Paginação da lista pública — avaliar na Phase 6
- Customização de tema/cores — Phase 5
</user_constraints>

---

<phase_requirements>
## Phase Requirements

| ID | Description | Research Support |
|----|-------------|------------------|
| GIFT-01 | Convidado pode visualizar lista de presentes com imagem, nome, descrição, preço sugerido, status | GiftController::index() renders all active gifts via GiftModel::getAllActive() |
| GIFT-02 | Convidado pode filtrar presentes por categoria | `/gifts/filter?category=X` endpoint, GiftModel::filter() with WHERE category |
| GIFT-03 | Convidado pode buscar presentes por nome | `/gifts/filter?search=X` endpoint, GiftModel with WHERE name LIKE :search |
| GIFT-04 | Convidado pode ordenar presentes por disponibilidade e preço | ORDER BY clause in filter query, sort param mapped to SQL order |
| GIFT-05 | Status visual do presente reflete disponível / esgotado / comprado | available_qty computed in PHP, card CSS class applied in view partial |
| GIFT-06 | Botão "Escolher este presente" abre modal de auth/reserva | JS click handler: if no session → open authModal, set window._pendingGiftId |
| GIFT-07 | Admin pode criar presente com todos os campos | AdminGiftController::create() + createPost() with full validation |
| GIFT-08 | Admin pode editar presente existente | AdminGiftController::edit($id) + editPost($id) |
| GIFT-09 | Admin pode excluir presente (com confirmação) | AdminGiftController::delete($id) — blocked if reservations exist |
| GIFT-10 | Admin pode ativar/desativar e destacar presentes | AdminGiftController::toggleActive($id), toggle featured via edit form |
| GIFT-11 | Admin pode fazer upload seguro de imagem (validação tipo/extensão/tamanho) | Upload::validateMime() + Upload::validateExtension() + size check + uniqid rename |
| GIFT-12 | Admin pode ajustar estoque manualmente | available_qty and total_qty editable fields in admin create/edit form |
| AUTH-01 | Convidado pode se cadastrar com nome, e-mail, telefone e senha via modal | GuestAuthController::signupPost() — JSON response endpoint |
| AUTH-02 | Convidado pode fazer login com e-mail e senha via modal | GuestAuthController::loginPost() — JSON response endpoint |
| AUTH-03 | Após cadastro, autenticação acontece automaticamente | signupPost() sets session immediately after INSERT |
| AUTH-04 | Admin pode fazer login com e-mail e senha em página protegida | Already implemented in Phase 1 (AdminAuthController) |
| AUTH-05 | Rotas do painel admin são protegidas por verificação de role | Already implemented in Phase 1 (AuthMiddleware) |
| AUTH-06 | Convidado pode fazer logout | GuestAuthController::logout() — clear session, redirect to / |
</phase_requirements>

---

## Summary

Phase 2 builds on the Phase 1 MVC skeleton — adding the public gift listing page, the guest auth modal, and the admin gift CRUD. All infrastructure (Router, BaseModel, CsrfMiddleware, AuthMiddleware, View, Flash, Session, Upload) is already in place and must be used as-is without modification.

The key architectural pattern new in this phase is the **partial HTML response**: `/gifts/filter` returns a rendered PHP view fragment (the card grid), not JSON. JavaScript replaces `#gifts-container` innerHTML with the response. This is the server-side rendering + AJAX hybrid mandated by D-06 and appropriate for a shared-hosting PHP project with no Node.js.

The **schema already exists** (`sql/schema.sql` from Phase 1) but is missing fields required by Phase 2 decisions: `slug`, `category`, `type` (single/multi), `featured`, and `available_qty` vs. `quantity` rename. The plan must include ALTER TABLE statements or a schema migration note to add these columns before any model code is written.

**Primary recommendation:** Implement in waves — (1) schema delta + GiftModel, (2) admin CRUD views, (3) public gifts page + filter endpoint, (4) guest auth endpoints + modal JS.

---

## Architectural Responsibility Map

| Capability | Primary Tier | Secondary Tier | Rationale |
|------------|-------------|----------------|-----------|
| Gift listing page render | Frontend Server (PHP SSR) | — | PHP renders HTML; no client-side routing |
| Filter/search/sort | Frontend Server (PHP SSR) | Browser (fetch + DOM swap) | PHP executes query; JS triggers and injects result |
| Guest auth (signup/login) | API / Backend (PHP JSON endpoint) | Browser (fetch + modal) | Credentials validated server-side; JSON response drives UI |
| Session management | Frontend Server (PHP sessions) | — | Native PHP sessions, set in GuestAuthController |
| Admin CRUD (gifts) | API / Backend (PHP POST handlers) | Frontend Server (form render) | POST routes process mutations; GET routes render forms |
| Image upload | Frontend Server (PHP file handling) | Browser (FileReader preview) | Server validates and stores; browser shows preview only |
| CSRF protection | API / Backend (CsrfMiddleware) | Browser (hidden input / fetch body) | Server validates; browser must send token |

---

## Standard Stack

### Core

| Library | Version | Purpose | Why Standard |
|---------|---------|---------|--------------|
| PHP | 8.1+ | Server-side logic | Project constraint (CLAUDE.md) |
| MySQL | 5.7 / 8.0 | Persistence | Project constraint; schema already deployed |
| PDO | built-in | DB access | Project constraint (SEC-01) |
| Bootstrap | 5.3.3 (CDN) | UI components + grid | Project constraint (CLAUDE.md); already in layouts |
| Bootstrap Icons | 1.11 (CDN) | Icons (gift, image, etc.) | Specified in UI-SPEC §Design System |

[VERIFIED: codebase — `app/Views/layouts/main.php`, `app/Views/layouts/admin.php` both already load Bootstrap 5.3.3 CDN with SRI hashes]

### Supporting

| Library | Version | Purpose | When to Use |
|---------|---------|---------|-------------|
| finfo (PHP ext) | built-in | Real MIME validation (SEC-05) | Every image upload — NEVER skip |
| FileReader API | browser built-in | Image preview before upload | Admin create/edit form only |
| Fetch API | browser built-in | AJAX filter + auth modal submit | Gifts filter and auth endpoints |

### Alternatives Considered

| Instead of | Could Use | Tradeoff |
|------------|-----------|----------|
| HTML partial response from `/gifts/filter` | JSON + client-side template | JSON would require a JS template engine — adds complexity with no benefit on shared PHP hosting |
| Native PHP sessions for guest auth | JWT tokens | JWT requires a library and stateless setup — overkill for Hostinger shared hosting |
| Browser confirm() for delete | Custom Bootstrap modal | `window.confirm()` specified in UI-SPEC D-21 — simpler, no extra modal needed |

**Installation:** No new dependencies. All packages already loaded via CDN or are PHP built-ins.

---

## Architecture Patterns

### System Architecture Diagram

```
Browser
  │
  ├─ GET /gifts ──────────────────────────────► GiftController::index()
  │                                                 │
  │                                           GiftModel::getAllActive()
  │                                                 │ SQL SELECT (active=1, ORDER BY sort_order)
  │                                           View::render('public/gifts', $gifts)
  │                                                 │ renders full page with #gifts-container
  │◄──────────────────────────────────── HTML (full page)
  │
  ├─ fetch GET /gifts/filter?search=X&category=Y&sort=Z
  │                                            ► GiftController::filter()
  │                                                 │
  │                                           GiftModel::filter($params)
  │                                                 │ SQL SELECT (active=1 + WHERE clauses)
  │                                           View partial render (cards HTML fragment)
  │◄──────────────────────────────────── HTML fragment (card grid only)
  │  JS: replaces #gifts-container.innerHTML
  │
  ├─ User clicks "Escolher este presente"
  │  JS: if no session → bootstrap.Modal.show(#authModal)
  │       window._pendingGiftId = giftId
  │
  ├─ fetch POST /auth/guest-signup (JSON body + csrf_token)
  │                                            ► GuestAuthController::signupPost()
  │                                                 │ validate → INSERT users → session_regenerate
  │◄──────────────────────────────────── JSON {success: true} or {error: "msg"}
  │  JS: show success → modal.hide() after 1200ms
  │
  ├─ fetch POST /auth/guest-login (JSON body + csrf_token)
  │                                            ► GuestAuthController::loginPost()
  │                                                 │ findByEmail → password_verify → set session
  │◄──────────────────────────────────── JSON {success: true} or {error: "msg"}
  │
  └─ Admin routes (all require AuthMiddleware 'auth')
       GET  /admin/gifts              ► AdminGiftController::index()    [list]
       GET  /admin/gifts/create       ► AdminGiftController::create()   [form]
       POST /admin/gifts/store        ► AdminGiftController::store()    [INSERT]
       GET  /admin/gifts/{id}/edit    ► AdminGiftController::edit()     [form prefilled]
       POST /admin/gifts/{id}/update  ► AdminGiftController::update()   [UPDATE]
       POST /admin/gifts/{id}/delete  ► AdminGiftController::delete()   [DELETE gated]
       POST /admin/gifts/{id}/toggle-active ► AdminGiftController::toggleActive()
```

### Recommended Project Structure (Phase 2 additions)

```
app/
├── Controllers/
│   ├── GiftController.php          # public: index(), filter()
│   ├── GuestAuthController.php     # signup/login/logout JSON endpoints
│   └── AdminGiftController.php     # admin CRUD: index/create/store/edit/update/delete/toggleActive
├── Models/
│   └── GiftModel.php               # getAllActive(), filter(), findById(), insert(), update(), delete()
└── Views/
    ├── public/
    │   ├── gifts/
    │   │   ├── index.php           # full page with filter bar + #gifts-container + authModal
    │   │   └── _cards.php          # partial: just the card grid (returned by filter endpoint)
    └── admin/
        └── gifts/
            ├── index.php           # admin gifts list table
            ├── form.php            # shared create/edit form
public/
└── uploads/
    └── gifts/                      # image upload destination (must exist, writable)
```

### Pattern 1: Filter Endpoint Returning HTML Partial

**What:** `GiftController::filter()` executes a parameterized query and renders only the card grid partial — not a full page.

**When to use:** All AJAX filter/search/sort calls from the public gifts page.

```php
// GiftController::filter()
public function filter(): void {
    $search   = trim($_GET['search']   ?? '');
    $category = trim($_GET['category'] ?? '');
    $sort     = $_GET['sort'] ?? 'available';

    $gifts = (new GiftModel)->filter($search, $category, $sort);
    // Render partial only — no layout wrapper
    $e = fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
    require ROOT_PATH . '/app/Views/public/gifts/_cards.php';
    exit;
}
```

[ASSUMED — pattern derived from D-06 and existing View::render() pattern in codebase]

### Pattern 2: JSON Auth Endpoints

**What:** `GuestAuthController` methods output JSON directly, never HTML.

**When to use:** Modal form submissions via fetch().

```php
public function signupPost(): void {
    header('Content-Type: application/json');
    // 1. CsrfMiddleware::validate() already called by Router for all POST
    // 2. Validate inputs
    $name     = trim($_POST['name']     ?? '');
    $email    = trim($_POST['email']    ?? '');
    $phone    = trim($_POST['phone']    ?? '');
    $password =      $_POST['password'] ?? '';

    if ($name === '' || $email === '' || $password === '') {
        echo json_encode(['error' => 'Preencha todos os campos obrigatórios.']);
        exit;
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['error' => 'E-mail inválido.']);
        exit;
    }

    $userModel = new UserModel;
    if ($userModel->findByEmail($email)) {
        echo json_encode(['error' => 'Este e-mail já está cadastrado. Tente entrar.']);
        exit;
    }

    $id = $userModel->create([
        'name'          => $name,
        'email'         => $email,
        'phone'         => $phone,
        'password_hash' => password_hash($password, PASSWORD_DEFAULT),
        'role'          => 'guest',
    ]);

    session_regenerate_id(true);
    $_SESSION['user_id'] = $id;
    $_SESSION['role']    = 'guest';
    $_SESSION['name']    = $name;
    unset($_SESSION['csrf_token']); // regenerate after privilege change

    echo json_encode(['success' => true]);
    exit;
}
```

[ASSUMED — based on D-14, D-15 and established AuthController pattern from Phase 1]

### Pattern 3: CSRF Token for Modal Fetch Forms

**What:** CSRF token embedded in page HTML via `data-csrf-token` on `#authModal`, read by JS before each fetch POST.

**When to use:** All modal form submissions (signup and login).

```php
// In app/Views/public/gifts/index.php:
<div class="modal fade" id="authModal"
     data-bs-backdrop="static"
     data-csrf-token="<?= htmlspecialchars(\App\Middleware\CsrfMiddleware::token(), ENT_QUOTES, 'UTF-8') ?>">
```

```javascript
// In app.js GiftPage object:
const csrfToken = document.getElementById('authModal').dataset.csrfToken;

async function submitForm(endpoint, formData) {
    formData.append('csrf_token', csrfToken);
    const res = await fetch(endpoint, { method: 'POST', body: formData });
    return res.json();
}
```

[VERIFIED: codebase — CsrfMiddleware::validate() reads from `$_POST['csrf_token']`; fetch with FormData body sends it as form field]

### Pattern 4: Secure Image Upload (extending Upload.php scaffold)

**What:** Full upload flow using the existing `Upload::validateMime()` and `Upload::validateExtension()` scaffold.

**When to use:** Admin create/edit gift form.

```php
// In AdminGiftController::store() or update():
private function handleImageUpload(): string|null {
    if (empty($_FILES['image']['tmp_name'])) {
        return null; // no image uploaded
    }

    $allowed_mimes = ['image/jpeg', 'image/png', 'image/webp'];
    $allowed_exts  = ['jpg', 'jpeg', 'png', 'webp'];
    $max_size      = 2 * 1024 * 1024; // 2MB

    if ($_FILES['image']['size'] > $max_size) {
        throw new \RuntimeException('A imagem deve ter no máximo 2MB.');
    }
    if (!Upload::validateMime($_FILES['image']['tmp_name'], $allowed_mimes)) {
        throw new \RuntimeException('Formato inválido. Use JPG, PNG ou WebP.');
    }
    if (!Upload::validateExtension($_FILES['image']['name'], $allowed_exts)) {
        throw new \RuntimeException('Formato inválido. Use JPG, PNG ou WebP.');
    }

    $ext      = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
    $filename = uniqid('gift_', true) . '.' . $ext;
    $dest     = ROOT_PATH . '/public/uploads/gifts/' . $filename;

    if (!move_uploaded_file($_FILES['image']['tmp_name'], $dest)) {
        throw new \RuntimeException('Falha ao salvar imagem.');
    }

    return '/uploads/gifts/' . $filename; // stored in DB as web path
}
```

[VERIFIED: codebase — `app/Helpers/Upload.php` provides `validateMime()` and `validateExtension()`; ROOT_PATH defined in index.php]

### Pattern 5: Admin Filter Using GET + Page Reload

**What:** Admin gifts table search/filter is a plain HTML GET form — full page reload, no fetch.

**When to use:** Admin gifts list only (D-22 — no dynamic requirement stated for admin filter).

```php
// AdminGiftController::index():
$search = trim($_GET['search'] ?? '');
$status = $_GET['status'] ?? 'all'; // 'all' | 'active' | 'inactive'
$gifts  = (new GiftModel)->adminFilter($search, $status);
View::render('admin/gifts/index', ['gifts' => $gifts, 'search' => $search, 'status' => $status, 'layout' => 'admin']);
```

[ASSUMED — derived from UI-SPEC admin filter bar note]

### Pattern 6: Inline Toggle Active (POST form, page reload)

**What:** Inativar/Ativar in the admin table is a hidden POST form submitted by a button click — no JS fetch.

```html
<form method="POST" action="/admin/gifts/<?= $e($gift['id']) ?>/toggle-active" class="d-inline">
    <input type="hidden" name="csrf_token"
           value="<?= htmlspecialchars(\App\Middleware\CsrfMiddleware::token(), ENT_QUOTES, 'UTF-8') ?>">
    <button type="submit" class="btn btn-sm btn-outline-secondary ms-1">
        <?= $gift['is_active'] ? 'Inativar' : 'Ativar' ?>
    </button>
</form>
```

[VERIFIED: codebase — CsrfMiddleware pattern confirmed; UI-SPEC specifies inline toggle via POST]

### Anti-Patterns to Avoid

- **Using View::render() for the filter partial:** View::render() wraps content in a layout — the filter endpoint must `require` the partial directly and `exit`, not call `View::render()`.
- **Reading `$_FILES['type']` for MIME validation:** Always use `finfo` via `Upload::validateMime()`. The browser-supplied MIME type is trivially spoofed.
- **Storing absolute file paths in the DB:** Store the web-relative path (`/uploads/gifts/filename.jpg`), not the server path. Makes URL construction trivial.
- **Setting `session_regenerate_id()` without `true`:** Must pass `true` to delete the old session file, preventing session fixation.
- **Rendering JSON endpoints with View::render():** Auth endpoints (`/auth/guest-signup`, `/auth/guest-login`) must output JSON only — `header('Content-Type: application/json')` then `json_encode()` then `exit`.
- **Router dynamic segment handling:** The current Router in Phase 1 does exact-match URI routing. Routes like `/admin/gifts/{id}/edit` with dynamic segments require Router extension or a workaround (see Open Questions).

---

## Schema Delta Required

The existing `sql/schema.sql` gifts table is missing columns required by Phase 2 decisions (D-17).

**Current gifts table columns:** id, name, description, price, image_path, quantity, reserved, is_active, sort_order, created_at, updated_at

**Missing columns needed for Phase 2:**

| Column | Type | Purpose | Decision |
|--------|------|---------|----------|
| `slug` | VARCHAR(255) NOT NULL UNIQUE | URL-friendly identifier, editable | D-17 |
| `category` | VARCHAR(100) DEFAULT NULL | Filter by category | D-08, D-17 |
| `type` | ENUM('single','multi') NOT NULL DEFAULT 'single' | Single vs multi-item | D-17, STOCK-01/02 |
| `featured` | TINYINT(1) NOT NULL DEFAULT 0 | Highlighted badge | D-05, D-17 |
| `available_qty` | INT UNSIGNED NOT NULL DEFAULT 0 | Available stock | D-17; distinct from `quantity` (total) |
| `total_qty` | INT UNSIGNED NOT NULL DEFAULT 1 | Total quantity | D-17; rename of `quantity` OR add alias |

**Note:** The existing `quantity` column maps to `total_qty` (D-17). The plan must add the missing columns via ALTER TABLE or update the schema.sql and note it requires re-import.

**Recommended approach:** Add ALTER TABLE statements to a new `sql/migrate_phase2.sql` file. This preserves existing data if Phase 1 was already deployed to Hostinger. [ASSUMED — safer than schema.sql re-import if data exists]

**ALTER TABLE statements needed:**

```sql
ALTER TABLE gifts
    ADD COLUMN slug         VARCHAR(255)                    NOT NULL DEFAULT '' AFTER name,
    ADD COLUMN category     VARCHAR(100)                    DEFAULT NULL AFTER description,
    ADD COLUMN type         ENUM('single','multi')          NOT NULL DEFAULT 'single' AFTER category,
    ADD COLUMN featured     TINYINT(1)                      NOT NULL DEFAULT 0 AFTER is_active,
    ADD COLUMN available_qty INT UNSIGNED                   NOT NULL DEFAULT 0 AFTER quantity,
    ADD UNIQUE INDEX idx_gifts_slug (slug);

-- Backfill available_qty from existing quantity (total) for existing rows
UPDATE gifts SET available_qty = quantity - reserved WHERE available_qty = 0;
-- Phone field on users for guest registration (D-13)
ALTER TABLE users
    ADD COLUMN phone VARCHAR(30) DEFAULT NULL AFTER email;
```

[ASSUMED — exact column positions and backfill logic; verify against deployed schema before executing]

---

## Don't Hand-Roll

| Problem | Don't Build | Use Instead | Why |
|---------|-------------|-------------|-----|
| MIME type validation | Custom file extension parsing | `Upload::validateMime()` (already in `app/Helpers/Upload.php`) | Extensions are spoofable; finfo reads magic bytes |
| CSRF timing-safe comparison | `===` token compare | `hash_equals()` in existing `CsrfMiddleware::validate()` | Timing attacks — `===` leaks token length/content |
| Password handling | `md5()`, `sha1()`, custom salt | `password_hash()` / `password_verify()` (PHP built-in) | BCrypt adaptive cost, handles salt automatically |
| Session security settings | Manual cookie flags | `Session::start()` in `app/Helpers/Session.php` | Already sets `httponly`, `samesite`, `use_strict_mode` |
| Output escaping | `strip_tags()`, `addslashes()` | `$e()` closure from `View::render()` / `htmlspecialchars()` | `strip_tags()` doesn't prevent XSS in attribute context |
| Unique file naming | `time()` suffix | `uniqid('gift_', true)` + extension | `time()` collides under concurrent requests |

**Key insight:** Phase 1 built all the security infrastructure. Phase 2 must use it — not reinvent it.

---

## Common Pitfalls

### Pitfall 1: Router Has No Dynamic Segment Support

**What goes wrong:** `/admin/gifts/42/edit` — the current Router does exact-string URI matching. A route for `/admin/gifts/{id}/edit` will never match.

**Why it happens:** Phase 1 Router is a simple array lookup, not a regex router.

**How to avoid:** Two valid approaches —
  (a) Extend `Router::dispatch()` to support `{param}` placeholders with regex matching and pass captured params to the controller action.
  (b) Pass the ID via a query string: `/admin/gifts/edit?id=42` with exact-match route `/admin/gifts/edit`. Simpler but less RESTful.

**Warning signs:** 404 on any admin gifts URL with an ID segment.

**Recommendation:** Extend Router to support one dynamic segment. It is a small change and used throughout Phases 2-5. [ASSUMED — either approach works; planner must pick one and document it]

### Pitfall 2: Schema Mismatch Between Phase 1 Deploy and Phase 2 Code

**What goes wrong:** Phase 1 may have been deployed to Hostinger. The gifts table exists but lacks `slug`, `category`, `type`, `featured`, `available_qty`. GiftModel queries referencing these columns throw PDOException.

**Why it happens:** Phase 1 schema.sql was written as a "complete schema" stub but the gifts table only has basic columns.

**How to avoid:** Create `sql/migrate_phase2.sql` with ALTER TABLE statements. Execute before deploying Phase 2 code.

**Warning signs:** `PDOException: Unknown column 'slug' in 'field list'`

### Pitfall 3: Filter Partial Accidentally Including Layout

**What goes wrong:** `/gifts/filter` returns a full HTML page instead of just the card grid. JS replaces `#gifts-container` with a full `<!DOCTYPE html>` page, breaking the display.

**Why it happens:** Using `View::render()` in the filter action instead of requiring the partial directly.

**How to avoid:** In `GiftController::filter()`, use `require` on the partial template file directly, then `exit`. Do NOT call `View::render()`.

### Pitfall 4: CSRF Validation Failing on Fetch POST Requests

**What goes wrong:** Auth modal fetch POSTs fail with 403 even though the token is in the body.

**Why it happens:** Router calls `CsrfMiddleware::validate()` for ALL POST requests. The token must be in `$_POST['csrf_token']`. Fetch with `Content-Type: application/json` body does NOT populate `$_POST`.

**How to avoid:** Auth modal JS must use `FormData` (not JSON body) for the fetch POST, or use `application/x-www-form-urlencoded`. The CSRF token appended to FormData will be available in `$_POST`.

**Alternative:** If JSON body is preferred, CsrfMiddleware must be updated to also read from `php://input`. However, changing CsrfMiddleware is risky — FormData is simpler and already compatible.

**Warning signs:** 403 response on `/auth/guest-signup` or `/auth/guest-login`.

### Pitfall 5: `uploads/gifts/` Directory Missing or Not Writable

**What goes wrong:** `move_uploaded_file()` returns false silently, image is never saved.

**Why it happens:** The directory `public/uploads/gifts/` does not exist or lacks write permissions on Hostinger.

**How to avoid:** Plan must include a Wave 0 task to `mkdir -p public/uploads/gifts/` and verify permissions (755). Add a `public/uploads/gifts/.gitkeep` to track the directory in git.

### Pitfall 6: Deleting Gift with Reservations Breaks FK Constraint

**What goes wrong:** `DELETE FROM gifts WHERE id = :id` throws `PDOException: Cannot delete or update a parent row: a foreign key constraint fails`.

**Why it happens:** `reservations.gift_id` has `ON DELETE CASCADE` in Phase 1 schema — but D-19 says deletion should be BLOCKED if reservations exist, not cascaded.

**How to avoid:** AdminGiftController::delete() must first `SELECT COUNT(*) FROM reservations WHERE gift_id = :id`. If count > 0, flash error and redirect. Do NOT rely on FK cascade for this business rule check.

**Warning signs:** In Phase 1, `ON DELETE CASCADE` was set on reservations FK — this means if the check is skipped, the delete will SUCCEED and cascade-delete reservations, which violates D-19.

---

## Code Examples

### GiftModel Filter Query

```php
// Source: derived from BaseModel pattern (app/Models/BaseModel.php)
public function filter(string $search, string $category, string $sort): array {
    $where  = ['g.is_active = 1'];
    $params = [];

    if ($search !== '') {
        $where[]          = 'g.name LIKE :search';
        $params[':search'] = '%' . $search . '%';
    }
    if ($category !== '') {
        $where[]            = 'g.category = :category';
        $params[':category'] = $category;
    }

    $orderBy = match($sort) {
        'price_asc'   => 'g.price ASC',
        'price_desc'  => 'g.price DESC',
        'featured'    => 'g.featured DESC, g.sort_order ASC',
        default       => 'g.available_qty DESC, g.sort_order ASC', // available first
    };

    $sql = 'SELECT * FROM gifts g WHERE ' . implode(' AND ', $where) . " ORDER BY $orderBy";
    return $this->query($sql, $params)->fetchAll();
}
```

[ASSUMED — query structure; ORDER BY clause uses hardcoded enum values, not user input, so no injection risk]

### Slug Generation (JavaScript)

```javascript
// Source: UI-SPEC §Admin gift create/edit form
// In public/assets/js/app.js — GiftPage object
const nameInput = document.querySelector('[name="name"]');
const slugInput = document.querySelector('[name="slug"]');
let slugManuallyEdited = false;

if (nameInput && slugInput) {
    slugInput.addEventListener('input', () => { slugManuallyEdited = true; });
    nameInput.addEventListener('input', () => {
        if (!slugManuallyEdited) {
            slugInput.value = nameInput.value
                .toLowerCase()
                .normalize('NFD').replace(/[̀-ͯ]/g, '') // strip accents
                .replace(/[^a-z0-9\s-]/g, '')
                .trim()
                .replace(/\s+/g, '-');
        }
    });
}
```

[ASSUMED — normalize/NFD accent stripping is the correct approach for Portuguese gift names]

### Image Preview (JavaScript)

```javascript
// Source: UI-SPEC §Admin gift create/edit form
const fileInput  = document.querySelector('[name="image"]');
const imgPreview = document.getElementById('image-preview');

if (fileInput && imgPreview) {
    fileInput.addEventListener('change', () => {
        const file = fileInput.files[0];
        if (!file) return;
        const reader = new FileReader();
        reader.onload = (e) => {
            imgPreview.src   = e.target.result;
            imgPreview.style.display = 'block';
        };
        reader.readAsDataURL(file);
    });
}
```

[VERIFIED: UI-SPEC §Admin gift create/edit form specifies FileReader.readAsDataURL()]

### Auth Modal Tab Switching (JavaScript)

```javascript
// Source: UI-SPEC §Guest authentication modal
document.querySelectorAll('[data-tab]').forEach(tab => {
    tab.addEventListener('click', (e) => {
        e.preventDefault();
        const target = e.currentTarget.dataset.tab;
        document.getElementById('tab-signup').classList.toggle('d-none', target !== 'signup');
        document.getElementById('tab-login').classList.toggle('d-none',  target !== 'login');
        document.querySelectorAll('[data-tab]').forEach(t => t.classList.remove('active'));
        e.currentTarget.classList.add('active');
    });
});
```

[VERIFIED: UI-SPEC specifies vanilla JS toggles d-none on tab divs]

### Gift ID Preservation Pattern

```javascript
// Source: UI-SPEC §Gift ID preservation
// Set when "Escolher este presente" is clicked
document.querySelectorAll('.btn-choose-gift').forEach(btn => {
    btn.addEventListener('click', () => {
        const giftId = btn.dataset.giftId;
        const isLoggedIn = btn.dataset.loggedIn === '1';
        if (!isLoggedIn) {
            window._pendingGiftId = giftId;
            new bootstrap.Modal(document.getElementById('authModal')).show();
        } else {
            // Phase 3: proceed to reserve directly
            window._pendingGiftId = giftId;
        }
    });
});
```

[ASSUMED — `data-logged-in` attribute rendered by PHP based on session state]

---

## State of the Art

| Old Approach | Current Approach | When Changed | Impact |
|--------------|------------------|--------------|--------|
| Full page reload for filter | fetch + innerHTML swap (D-06) | Phase 2 decision | No reload needed; PHP still renders HTML server-side |
| Inline `echo $_GET['x']` | `$e($var)` closure from View::render() | Phase 1 established | All views must use `$e()` |

**Deprecated/outdated:**
- `app/Views/public/home.php` stub: Phase 2 replaces this with the gifts listing page (GiftController::index() now handles `/` or `/gifts` route — confirm with planner).
- `AdminAuthController::giftsStub()`: Phase 2 replaces with full AdminGiftController.

---

## Assumptions Log

| # | Claim | Section | Risk if Wrong |
|---|-------|---------|---------------|
| A1 | Filter partial rendered via `require` + `exit` instead of `View::render()` | Architecture Patterns - Pattern 1 | If View::render() is somehow made to not apply layout, could work — but the "no layout" intent is clear |
| A2 | Auth endpoints use FormData body (not JSON) so CSRF lands in `$_POST` | Common Pitfalls - Pitfall 4 | If CsrfMiddleware is updated to read php://input, JSON body would work — but that requires changing Phase 1 code |
| A3 | Router requires extension to support dynamic path segments | Common Pitfalls - Pitfall 1 | If query string approach chosen instead, no Router change needed |
| A4 | Schema migration delivered as `sql/migrate_phase2.sql` not schema.sql rewrite | Schema Delta section | If fresh deploy (no existing data), a full schema.sql rewrite is cleaner |
| A5 | `window._pendingGiftId` gift ID preservation via JS global | Code Examples - Gift ID Preservation | Phase 3 must read this global — if page navigates or reloads, value is lost. Acceptable for Phase 2 since no navigation occurs |
| A6 | `available_qty` backfill via `UPDATE gifts SET available_qty = quantity - reserved` | Schema Delta section | If `reserved` count is inaccurate, backfill will be wrong — manual review needed |
| A7 | GuestAuthController::logout() clears session and redirects to `/` | Phase Requirements AUTH-06 | Redirect target not specified in CONTEXT.md — `/` is most logical for a public page app |

---

## Open Questions

1. **Router dynamic segments — which approach?**
   - What we know: Current Router does exact-string matching. Phase 2 needs `/admin/gifts/{id}/edit`.
   - What's unclear: Extend Router with regex matching, or use query string `/admin/gifts/edit?id={id}`?
   - Recommendation: Extend Router — it's a one-time ~20 line change, cleaner URLs for all future phases.

2. **Public gift listing URL — `/` or `/gifts`?**
   - What we know: Phase 1 stub uses `/` for home page. Phase 2 makes gift listing the main content.
   - What's unclear: Should `/` redirect to `/gifts`, or should GiftController::index() handle `/`?
   - Recommendation: Make `GiftController::index()` handle `/` directly — replace HomeController for this route. Simpler.

3. **Guest auth check on page render — session or not?**
   - What we know: Buttons must show "Escolher este presente" or "Indisponível" based on gift state; auth check happens on click.
   - What's unclear: Does PHP embed `data-logged-in="1"` on buttons, or does JS check for a cookie/session indicator?
   - Recommendation: PHP renders `data-logged-in="<?= isset($_SESSION['user_id']) ? '1' : '0' ?>"` on the page-level container or each button. JS reads this. No JS cookie parsing needed.

---

## Environment Availability

| Dependency | Required By | Available | Version | Fallback |
|------------|------------|-----------|---------|----------|
| PHP finfo extension | Upload::validateMime() (SEC-05) | ✓ [ASSUMED] | built-in PHP 8.1+ | None — deny upload if unavailable (fail-closed already coded) |
| `public/uploads/gifts/` dir | Image upload destination | ✗ — does not exist yet | — | Create in Wave 0: `mkdir -p public/uploads/gifts/` |
| Bootstrap Icons CDN | UI icons (bi-gift, bi-image) | ✗ — not in layouts yet | 1.11 | Add `<link>` to both main.php and admin.php layouts |

**Missing dependencies with no fallback:**
- `public/uploads/gifts/` directory — must be created with correct permissions before image upload works.

**Missing dependencies with fallback:**
- Bootstrap Icons CDN link — add to both layout files as first task of implementation.

---

## Validation Architecture

### Test Framework

| Property | Value |
|----------|-------|
| Framework | None detected — PHP project on shared hosting, no test runner configured |
| Config file | none |
| Quick run command | manual browser testing (no automated runner) |
| Full suite command | manual browser testing |

### Phase Requirements → Test Map

| Req ID | Behavior | Test Type | Automated Command | File Exists? |
|--------|----------|-----------|-------------------|-------------|
| GIFT-01 | Gift list renders with correct fields | manual | — | — |
| GIFT-02 | Category filter returns only matching gifts | manual | — | — |
| GIFT-03 | Name search returns matching gifts | manual | — | — |
| GIFT-04 | Sort order changes correctly | manual | — | — |
| GIFT-05 | Sold-out card shows correct visual state | manual | — | — |
| GIFT-06 | Auth modal opens when unauthenticated user clicks | manual | — | — |
| GIFT-07 | Admin create form saves gift with all fields | manual | — | — |
| GIFT-08 | Admin edit form updates existing gift | manual | — | — |
| GIFT-09 | Delete blocked when reservations exist | manual | — | — |
| GIFT-10 | Toggle active/inactive updates status | manual | — | — |
| GIFT-11 | Invalid MIME upload rejected (test with renamed .exe → .jpg) | manual | — | — |
| GIFT-12 | Stock fields editable in admin form | manual | — | — |
| AUTH-01 | Guest signup creates user, sets session | manual | — | — |
| AUTH-02 | Guest login with correct credentials succeeds | manual | — | — |
| AUTH-03 | Auto-login after signup verified via session | manual | — | — |
| AUTH-04 | Admin login (Phase 1) already working | manual | — | — |
| AUTH-05 | Admin routes redirect when not authenticated | manual | — | — |
| AUTH-06 | Logout clears session and redirects | manual | — | — |

### Wave 0 Gaps

- [ ] `public/uploads/gifts/` — directory must exist and be writable (755)
- [ ] `public/uploads/gifts/.gitkeep` — track directory in git
- [ ] Bootstrap Icons CDN `<link>` added to `app/Views/layouts/main.php`
- [ ] Bootstrap Icons CDN `<link>` added to `app/Views/layouts/admin.php`
- [ ] `sql/migrate_phase2.sql` — schema delta (ALTER TABLE gifts, ALTER TABLE users)
- [ ] Router extension for dynamic path segments (if chosen approach)

---

## Security Domain

### Applicable ASVS Categories

| ASVS Category | Applies | Standard Control |
|---------------|---------|-----------------|
| V2 Authentication | yes | `password_hash()` / `password_verify()` (existing pattern), `session_regenerate_id(true)` |
| V3 Session Management | yes | `Session::start()` with httponly + samesite (existing), regenerate on login |
| V4 Access Control | yes | `AuthMiddleware::handle()` — `$_SESSION['role'] === 'admin'` (existing) |
| V5 Input Validation | yes | `filter_var(FILTER_VALIDATE_EMAIL)`, trim + empty checks, PDO prepared statements |
| V6 Cryptography | no | No new crypto — password handled by existing pattern |

### Known Threat Patterns for PHP Upload + Auth Stack

| Pattern | STRIDE | Standard Mitigation |
|---------|--------|---------------------|
| Malicious file upload (PHP shell as .jpg) | Tampering | `Upload::validateMime()` + extension whitelist + store outside webroot or in uploads/ with no PHP execution |
| SQL injection via filter params | Tampering | PDO prepared statements with named params (existing BaseModel::query()) |
| CSRF on admin mutations | Spoofing | `CsrfMiddleware::validate()` called by Router on all POST (existing) |
| Session fixation on login | Elevation | `session_regenerate_id(true)` + unset csrf_token (existing pattern) |
| XSS via gift name/description in cards | Tampering | `$e()` closure in all view templates (existing) |
| Brute force on guest login | Spoofing | No rate limiting in scope — acceptable for small event system [ASSUMED] |
| Directory traversal via image path | Information Disclosure | `uniqid()` rename + no user-controlled path components |

**Upload storage note:** `public/uploads/gifts/` is under the document root. PHP execution in this directory must be disabled. On Hostinger shared hosting, `.php` files in uploads/ may execute by default. The plan MUST include a `.htaccess` in `public/uploads/` to deny PHP execution:

```apache
# public/uploads/.htaccess
<FilesMatch "\.php$">
    Deny from all
</FilesMatch>
```

[ASSUMED — Hostinger shared hosting behavior; this is standard security hardening for upload directories]

---

## Project Constraints (from CLAUDE.md)

| Directive | Type | Impact on Phase 2 |
|-----------|------|-------------------|
| PDO prepared statements — never variable interpolation | Non-negotiable security | All GiftModel and GuestAuthController queries must use named params |
| CSRF token in ALL POST forms | Non-negotiable security | All admin forms + inline toggle forms + delete forms must include hidden csrf_token |
| `htmlspecialchars()` on ALL HTML output | Non-negotiable security | All gift cards, admin table cells, form values must use `$e()` |
| `password_hash()` / `password_verify()` | Non-negotiable security | GuestAuthController::signupPost() and loginPost() |
| Upload: finfo MIME, extension whitelist, size limit, rename | Non-negotiable security | AdminGiftController image upload — use Upload.php scaffold |
| Admin routes: `$_SESSION['role'] === 'admin'` check | Non-negotiable security | All /admin/gifts/* routes must have 'auth' middleware in routes.php |
| MVC: no DB logic in Views | Architecture | GiftModel does all queries; Views only render |
| Namespace App\Controllers, App\Models, etc. | Architecture | GiftController, GuestAuthController, AdminGiftController, GiftModel all need correct namespaces |
| No Node.js, no build step | Deployment | All JS is vanilla, loaded from public/assets/js/app.js |
| Bootstrap 5 CDN only | UI | No additional CSS frameworks |
| Deploy on Hostinger shared hosting | Deployment | File paths via ROOT_PATH, session_save_path, no symlinks |

---

## Sources

### Primary (HIGH confidence)
- `app/Helpers/Upload.php` — MIME/extension validation scaffold verified
- `app/Models/BaseModel.php` — query pattern with named params verified
- `app/Middleware/CsrfMiddleware.php` — token + validate pattern verified
- `app/Views/layouts/admin.php` — Bootstrap 5.3.3 CDN confirmed, admin layout structure confirmed
- `sql/schema.sql` — existing gifts table columns confirmed; missing columns identified
- `config/routes.php` — exact-match Router confirmed; giftsStub confirmed as placeholder
- `.planning/phases/02-presentes-publicos-crud-admin/02-CONTEXT.md` — all D-01..D-22 decisions
- `.planning/phases/02-presentes-publicos-crud-admin/02-UI-SPEC.md` — Bootstrap classes, copywriting, interaction states

### Secondary (MEDIUM confidence)
- `.planning/phases/01-base-infraestrutura/01-PATTERNS.md` — full Router, View, Session, Flash patterns verified as reference

### Tertiary (LOW confidence)
- Hostinger PHP execution behavior in uploads/ directory — standard practice but not verified against Hostinger docs

---

## Metadata

**Confidence breakdown:**
- Standard stack: HIGH — codebase verified, all libraries confirmed in existing layouts
- Architecture: HIGH — patterns derived directly from existing Phase 1 code
- Schema delta: HIGH — existing schema.sql read and compared against D-17 field list
- Pitfalls: MEDIUM — Router limitation verified; upload pitfall standard practice; CSRF/FormData behavior is PHP spec

**Research date:** 2026-04-22
**Valid until:** 2026-10-22 (stable PHP/Bootstrap stack; Hostinger behavior assumptions should be re-verified at deploy time)