# Phase 3: Reserva Atômica & Meus Presentes - Context

**Gathered:** 2026-04-24
**Status:** Ready for planning

<domain>
## Phase Boundary

Fluxo completo de reserva: usuário clica "Escolher este presente" (já autenticado ou logo após autenticar) → POST para endpoint `/reserve` → transação MySQL com SELECT FOR UPDATE decrementa estoque atomicamente → feedback visual inline na página → página "Meus Presentes" com lista de reservas do usuário logado, incluindo possibilidade de cancelamento.

Não inclui: seletor de quantidade, redirect automático, painel admin de reservas (Phase 5), contribuições Pix (Phase 4).

</domain>

<decisions>
## Implementation Decisions

### Trigger da reserva (fluxo JS)
- **D-01:** O JS em `initChooseGift` (app.js) já tem o hook de Phase 3 para usuário logado (`loggedIn === '1'`). Nesse branch, fazer POST para `/reserve` com `gift_id = window._pendingGiftId` (ou `btn.dataset.giftId`).
- **D-02:** Após `_onAuthSuccess()` re-clicar o botão com `loggedIn='1'`, o mesmo path de reserva é acionado — nenhum estado extra precisa ser preservado além do `gift_id` já capturado.

### Feedback visual da reserva
- **D-03:** Após reserva bem-sucedida, exibir toast/alert de sucesso na própria página (sem redirect automático para "Meus Presentes").
- **D-04:** O card do presente deve atualizar visualmente sem reload de página: decrementar a quantidade exibida, ou marcar o card como esgotado (overlay + botão desabilitado) se `available_qty` chegou a 0.
- **D-05:** O toast de sucesso deve incluir um botão/link "Ver meus presentes" como ação opcional — não obrigatório.
- **D-06:** Nenhum redirect automático; o usuário permanece na página de presentes.

### Quantidade reservada (presentes multi)
- **D-07:** Sempre reservar qty=1 por clique — sem seletor de quantidade nesta fase.
- **D-08:** Mesmo para presentes do tipo `multi` com múltiplas unidades disponíveis, cada ação de reserva registra `quantity=1`. Simplifica o fluxo e evita confusão.

### Página Meus Presentes
- **D-09:** Rota `/my-gifts` — acessível apenas para usuários com role `guest` autenticados.
- **D-10:** Link "Meus Presentes" aparece no menu de navegação público (`layouts/main.php`) apenas quando o usuário está logado (`$_SESSION['role'] === 'guest'`).
- **D-11:** A página exibe lista de reservas do usuário com os seguintes campos por item:
  - Imagem do presente (thumbnail)
  - Nome do presente
  - Categoria
  - Status da reserva (reserved / confirmed / cancelled)
  - Data da reserva
  - Botão "Cancelar reserva" — visível apenas se status for `reserved`
- **D-12:** Cancelamento na página "Meus Presentes": POST para endpoint `/my-gifts/cancel` com `reservation_id` → devolve a quantidade ao estoque (transação) → atualiza status para `cancelled`.
- **D-13:** Se não houver reservas: mostrar estado vazio amigável (mensagem + link para lista de presentes).

### Erro de concorrência / esgotamento
- **D-14:** Se o presente esgotou entre o clique do usuário e a tentativa de reserva (race condition), o backend retorna erro JSON com mensagem: "Esse presente acabou de ficar indisponível."
- **D-15:** O JS deve atualizar o card correspondente para estado esgotado (overlay, botão desabilitado) sem reload — mesma lógica do D-04.
- **D-16:** Nenhuma reserva parcial é criada; a transação faz rollback se `available_qty` já for 0 ao executar o UPDATE.
- **D-17:** Lógica transacional no backend obrigatória: `BEGIN` → `SELECT ... FOR UPDATE` no gift → verificar `available_qty > 0` → `INSERT INTO reservations` + `UPDATE gifts SET available_qty = available_qty - 1` → `COMMIT`. Qualquer falha aciona `ROLLBACK`.

### Schema migration (Phase 3)
- **D-18:** A tabela `reservations` precisa de migration: alterar status enum de `('active','cancelled')` para `('reserved','confirmed','cancelled')` — RESERVE-02 exige `status=reserved` na criação.
- **D-19:** Adicionar FK constraint `user_id → users(id)` na tabela `reservations` (coluna já existe desde migrate_phase2.sql, sem constraint).
- **D-20:** Colunas `guest_name`, `guest_email`, `token` na tabela `reservations` eram do esquema Phase 1 pré-auth. Manter as colunas (nullable) para compatibilidade; novas reservas usarão `user_id`.

### Claude's Discretion
- Estilo exato do toast de sucesso (Bootstrap toast ou alert fixo no topo)
- Animação de atualização do card (fade, pulso discreto)
- Estrutura exata do JSON de resposta do `/reserve` endpoint
- Middleware de autenticação para a rota `/my-gifts` (pode reutilizar `AuthMiddleware` com check de role `guest`)

</decisions>

<canonical_refs>
## Canonical References

**Downstream agents MUST read these before planning or implementing.**

### Arquitetura e segurança
- `CLAUDE.md` — MVC, requisitos de segurança não-negociáveis (PDO, CSRF, htmlspecialchars), estrutura de diretórios
- `.planning/REQUIREMENTS.md` — Requisitos STOCK-01..05, RESERVE-01..03 com critérios de aceitação
- `.planning/ROADMAP.md` §Phase 3 — Goal, success criteria

### Contexto das fases anteriores
- `.planning/phases/01-base-infraestrutura/01-CONTEXT.md` — Padrões estabelecidos (View::render, $e(), BaseModel, CSRF, Router)
- `.planning/phases/01-base-infraestrutura/01-PATTERNS.md` — Padrões de código concretos a seguir
- `.planning/phases/02-presentes-publicos-crud-admin/02-CONTEXT.md` — Decisões D-11..D-16 sobre modal de auth, gift_id preservado, fluxo _onAuthSuccess()

### Schema e código existente
- `sql/schema.sql` — Estrutura das tabelas `reservations` (status enum atual) e `gifts` (campos `available_qty`, `type`, `quantity`)
- `sql/migrate_phase2.sql` — Coluna `user_id` já adicionada em `reservations` (sem FK constraint ainda)
- `public/assets/js/app.js` — `initChooseGift()` e `_onAuthSuccess()` — hooks Phase 3 já presentes
- `app/Models/BaseModel.php` — `getDb()` expõe PDO para transações manuais
- `app/Models/GiftModel.php` — `findById()`, `update()` — padrões para o ReservationModel seguir
- `app/Controllers/GuestAuthController.php` — Padrão de JSON response e validação de entrada

</canonical_refs>

<code_context>
## Existing Code Insights

### Reusable Assets
- `app/Models/BaseModel.php` — `getDb(): PDO` para `beginTransaction()`, `commit()`, `rollBack()`
- `app/Models/GiftModel.php` — `findById()` como referência para ReservationModel
- `app/Middleware/AuthMiddleware.php` — verificação de role; adaptar para aceitar `guest` além de `admin`
- `app/Middleware/CsrfMiddleware.php` — `::token()` / `::validate()` já chamado pelo Router para POST
- `app/Helpers/View.php` — `View::render()` com `$e()` closure
- `app/Views/layouts/main.php` — adicionar link "Meus Presentes" condicional ao `$_SESSION['role']`
- `public/assets/js/app.js` — `initChooseGift()` tem stub Phase 3 na linha ~79; `_onAuthSuccess()` já re-clica o botão logado

### Established Patterns
- Controllers retornam JSON via `header('Content-Type: application/json')` + `echo json_encode()`
- Toda saída HTML usa `$e($var)` — nunca `echo $var` direto
- Rotas em `config/routes.php`
- Novos models herdam de `BaseModel` e usam prepared statements via `$this->query()`

### Integration Points
- `config/routes.php` — adicionar rotas: `POST /reserve`, `GET /my-gifts`, `POST /my-gifts/cancel`
- `app/Controllers/` — novo `ReservationController`
- `app/Models/` — novo `ReservationModel`
- `app/Views/public/` — nova view `my-gifts/index.php`
- `sql/` — novo arquivo `migrate_phase3.sql`
- `public/assets/js/app.js` — implementar branch logado em `initChooseGift()` e lógica de atualização de card

</code_context>

<specifics>
## Specific Ideas

- Toast de sucesso com botão "Ver meus presentes" — Bootstrap Toast component (já disponível via CDN)
- Card update sem reload: o JSON de resposta do `/reserve` deve retornar o `available_qty` atualizado e `is_exhausted` para o JS atualizar o DOM diretamente
- A transação SELECT FOR UPDATE garante atomicidade mesmo em concorrência alta; Hostinger MySQL suporta InnoDB + FOR UPDATE
- Estado vazio em "Meus Presentes": mensagem amigável + link "Ver lista de presentes" para `/gifts`

</specifics>

<deferred>
## Deferred Ideas

- Seletor de quantidade para presentes multi — avaliado e descartado para Phase 3; pode ser revisado em fases futuras
- Cancelamento de reserva pelo admin — Phase 5 (RESERVE-04..06)
- E-mail de confirmação de reserva — v2 requirements (fora de escopo)

</deferred>

---

*Phase: 03-reserva-atomica-meus-presentes*
*Context gathered: 2026-04-24*
