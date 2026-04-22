# Phase 2: Presentes Públicos & CRUD Admin - Context

**Gathered:** 2026-04-22
**Status:** Ready for planning

<domain>
## Phase Boundary

Página pública de presentes com filtro, busca e ordenação via JS/fetch sem reload. CRUD completo de presentes no painel admin com upload de imagem. Modal de autenticação de convidado (cadastro/login) na página pública — a reserva em si é Phase 3.

Funcionalidades de reserva, estoque transacional e Pix são fases posteriores.

</domain>

<decisions>
## Implementation Decisions

### Cards de presentes (página pública)
- **D-01:** Layout em grid responsivo — 3 colunas desktop, 2 tablet, 1 mobile
- **D-02:** Cada card exibe: imagem, nome, descrição curta, categoria (se houver), preço sugerido (se houver), quantidade disponível, status visual, botão "Escolher presente"
- **D-03:** Status visual com três estados: disponível (default), esgotado (card visualmente escurecido/opaco, botão desabilitado), destacado (selo/badge discreto no canto do card)
- **D-04:** Item esgotado: botão "Escolher presente" desabilitado com estilo cinza — visualmente inequívoco
- **D-05:** Item destacado: badge sutil (ex: fita, estrela ou label no canto superior) — sem exagero visual

### Filtro / busca / ordenação
- **D-06:** Funciona via fetch + JS sem reload de página — PHP retorna HTML dos cards prontos (server-side rendering via AJAX) para um endpoint `/gifts/filter`
- **D-07:** Busca por nome com debounce leve (~300ms)
- **D-08:** Filtro por categoria (dropdown ou chips)
- **D-09:** Ordenação por: disponíveis primeiro, menor preço, maior preço, destacados primeiro
- **D-10:** Sem paginação nesta fase — todos os presentes carregados de uma vez (adequado para volume pequeno/moderado de uma lista de aniversário)

### Modal de autenticação do convidado
- **D-11:** Modal abre ao clicar "Escolher presente" se o usuário não estiver autenticado
- **D-12:** Modal tem duas abas: "Criar conta" e "Entrar" — alternáveis sem fechar o modal
- **D-13:** Campos do cadastro: nome, e-mail, telefone, senha
- **D-14:** Após login/cadastro bem-sucedido: modal fecha, usuário permanece na página de presentes, ação de escolha prossegue automaticamente (Phase 3 vai usar o contexto do presente escolhido)
- **D-15:** Feedback visual claro no próprio modal: mensagem de erro inline (campo inválido, e-mail já cadastrado, senha errada) e mensagem de sucesso antes de fechar
- **D-16:** O presente-alvo (gift_id) deve ser preservado enquanto o modal está aberto para que Phase 3 possa usar após autenticação

### CRUD admin de presentes
- **D-17:** Campos do formulário de presente: nome, slug (gerado automaticamente a partir do nome, editável), descrição, categoria, preço sugerido, tipo (single/multi), quantidade total, quantidade disponível, destaque (checkbox), ativo/inativo (toggle), imagem
- **D-18:** Upload de imagem: preview antes de salvar, validação de MIME real via finfo, extensão whitelist (jpg, jpeg, png, webp), tamanho máximo razoável (~2MB), arquivo renomeado com nome único ao salvar em `public/uploads/gifts/`
- **D-19:** Inativar ≠ excluir: inativar apenas remove da vitrine pública (campo `active=0`), registro permanece no banco; excluir é ação restrita — idealmente bloqueada ou com aviso se o presente já tiver reservas associadas
- **D-20:** Listagem admin: tabela com colunas (imagem thumb, nome, categoria, preço, quantidade disponível, status, ações)
- **D-21:** Ações rápidas na listagem: editar, inativar/ativar (toggle inline), excluir (com confirmação)
- **D-22:** Listagem admin com busca por nome e filtro por status (ativo/inativo/todos)

### Claude's Discretion
- Estilo exato do badge "destacado" (fita, estrela, label — o que ficar mais elegante com Bootstrap 5)
- Cor/ícone do estado esgotado (cinza, overlay, riscado — o que for mais legível)
- Geração do slug automático (JS no frontend ao digitar o nome)
- Estrutura exata do endpoint `/gifts/filter` (query params vs POST body)
- Tratamento de imagem ausente (placeholder ou sem imagem)

</decisions>

<canonical_refs>
## Canonical References

**Downstream agents MUST read these before planning or implementing.**

### Arquitetura e segurança
- `CLAUDE.md` — MVC, requisitos de segurança não-negociáveis (PDO, CSRF, htmlspecialchars, upload seguro), estrutura de diretórios
- `.planning/REQUIREMENTS.md` — Requisitos GIFT-01..12, AUTH-01..06 com critérios de aceitação
- `.planning/ROADMAP.md` §Phase 2 — Goal, success criteria

### Contexto da Phase 1
- `.planning/phases/01-base-infraestrutura/01-CONTEXT.md` — Decisões de layout, padrões estabelecidos (View::render, $e(), BaseModel, CSRF)
- `.planning/phases/01-base-infraestrutura/01-PATTERNS.md` — Padrões de código concretos a seguir

No external specs beyond the above.

</canonical_refs>

<code_context>
## Existing Code Insights

### Reusable Assets
- `app/Helpers/View.php` — `View::render($template, $data, $layout)` com `$e()` closure para escape
- `app/Helpers/Upload.php` — scaffold de upload seguro (finfo, whitelist, rename) — usar como base
- `app/Helpers/Flash.php` — mensagens flash entre requests
- `app/Helpers/Session.php` — `Session::start()`, `Session::get()`, `Session::set()`
- `app/Middleware/CsrfMiddleware.php` — `::token()` para formulários, `::validate()` no POST
- `app/Middleware/AuthMiddleware.php` — verificação de `$_SESSION['role'] === 'admin'`
- `app/Models/BaseModel.php` — `query()`, `findById()`, prepared statements padrão
- `app/Models/UserModel.php` — `findByEmail()` — reusar para login do convidado
- `app/Views/layouts/main.php` — layout público base
- `app/Views/layouts/admin.php` — layout admin com sidebar

### Established Patterns
- Controllers herdam de `BaseController`, usam `View::render()`
- Todas as queries via `BaseModel` — nunca interpolação de variáveis em SQL
- Toda saída HTML usa `$e($var)` — nunca `echo $var` direto
- Formulários POST incluem `CsrfMiddleware::token()` como hidden input
- `config/routes.php` define todas as rotas da aplicação

### Integration Points
- Novas rotas adicionadas em `config/routes.php`
- Novos controllers em `app/Controllers/`
- Novos models em `app/Models/` herdando de `BaseModel`
- Novas views em `app/Views/public/` (área pública) e `app/Views/admin/` (painel)
- Uploads de imagem salvos em `public/uploads/gifts/`

</code_context>

<specifics>
## Specific Ideas

- Cards elegantes e responsivos são prioridade visual — Bootstrap 5 cards com sombra suave
- Modal de auth com duas abas "Criar conta" / "Entrar" — sem redirecionar, tudo inline
- Gift ID do presente selecionado deve ser preservado no JS enquanto modal está aberto (para Phase 3 usar após autenticação)
- Slug gerado automaticamente no frontend ao digitar o nome, mas editável manualmente

</specifics>

<deferred>
## Deferred Ideas

- Reserva automática após autenticação — Phase 3 (usa o gift_id preservado pelo modal desta fase)
- Paginação da lista pública — avaliar na Phase 6 se o volume justificar
- Customização de tema/cores — Phase 5

</deferred>

---

*Phase: 02-presentes-publicos-crud-admin*
*Context gathered: 2026-04-22*
