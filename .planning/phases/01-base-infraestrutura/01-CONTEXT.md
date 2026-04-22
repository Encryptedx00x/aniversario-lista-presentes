# Phase 1: Base & Infraestrutura - Context

**Gathered:** 2026-04-22
**Status:** Ready for planning

<domain>
## Phase Boundary

Toda a infraestrutura necessária para o app rodar: estrutura de diretórios, front controller com mod_rewrite, autoloader PSR-4 sem Composer, router baseado em array, schema SQL completo, configuração de ambiente, autenticação por sessão PHP nativa com roles, middleware CSRF e auth, layout base (header/footer/nav público + admin), páginas de erro estilizadas.

Funcionalidades de negócio (presentes, reservas, Pix) são fases posteriores.

</domain>

<decisions>
## Implementation Decisions

### Layout base — Home pública
- **D-01:** A home da Phase 1 já deve ter o hero real com nome do aniversariante, mesmo com dados hardcoded temporariamente (não uma página "em breve")
- **D-02:** Cor primária Bootstrap 5 padrão (`#0d6efd`) — customização de tema fica para Phase 5
- **D-03:** Header público já inclui links visíveis para "Presentes" e "Pix" mesmo que as páginas ainda não existam (links apontam para URLs que serão implementadas nas fases seguintes)

### Admin dashboard stub
- **D-04:** Dashboard admin da Phase 1 exibe cards zerados no layout final esperado (0 presentes, 0 reservas, 0 contribuições Pix) — não uma mensagem simples de boas-vindas
- **D-05:** Todos os itens do sidebar admin (Presentes, Reservas, Pix, Configurações) já visíveis e clicáveis na Phase 1, apontando para páginas stub com mensagem "Funcionalidade disponível em breve"
- **D-06:** Botão de logout no topo do header do admin, no canto direito
- **D-07:** Nome do usuário admin logado visível no header do admin (ao lado do botão de logout)

### Páginas de erro
- **D-08:** Páginas 404, 403 e 500 com layout Bootstrap completo (mesmo header/footer do site), não HTML mínimo
- **D-09:** Página 403 exibe: "Acesso negado. Token inválido ou sessão expirada." com botão "Voltar"
- **D-10:** Página 404 exibe: "Página não encontrada." com dois botões: "Ir para home" e "Ver presentes"

### Schema SQL
- **D-11:** Schema completo criado na Phase 1 — todas as tabelas principais (users, gifts, reservations, contributions, settings) em `sql/schema.sql` para evitar ALTER TABLE estrutural nas fases seguintes
- **D-12:** Admin padrão NÃO incluído no schema.sql — criado via `sql/seed.sql` separado na Phase 6
- **D-13:** Todas as tabelas usam `ENGINE=InnoDB`, `CHARSET=utf8mb4`, `COLLATE=utf8mb4_unicode_ci`

### Claude's Discretion
- Conteúdo exato do hero hardcoded (nome do aniversariante placeholder)
- Ícones Bootstrap Icons ou Font Awesome para o nav/sidebar
- Estilos CSS específicos além do Bootstrap padrão
- Estrutura interna das páginas stub do admin
- Mensagem exata de erro 500

</decisions>

<specifics>
## Specific Ideas

- Hero da home pública deve já ter a estrutura final (nome do aniversariante, título/subtítulo) mesmo com dados hardcoded — não um placeholder "em breve"
- Admin sidebar com todos os links já presentes e clicáveis desde Phase 1 (stub pages), para que o admin já consiga navegar pelo sistema mesmo antes das funcionalidades estarem prontas
- Dashboard com cards zerados no layout correto é preferível a uma mensagem simples — estabelece o padrão visual desde cedo

</specifics>

<canonical_refs>
## Canonical References

**Downstream agents MUST read these before planning or implementing.**

### Project requirements e arquitetura
- `CLAUDE.md` — Arquitetura MVC, requisitos de segurança não-negociáveis, estrutura de diretórios, padrões de código
- `.planning/REQUIREMENTS.md` — Requisitos SEC-01..07, DEPLOY-03, DEPLOY-04 com critérios de aceitação
- `.planning/ROADMAP.md` §Phase 1 — Goal, success criteria, directory structure esperada

### Research e patterns da Phase 1
- `.planning/phases/01-base-infraestrutura/01-RESEARCH.md` — Padrões técnicos validados: front controller, autoloader, PDO setup, CSRF, session hardening, segurança OWASP
- `.planning/phases/01-base-infraestrutura/01-PATTERNS.md` — Código concreto para todos os 28 arquivos da fase (greenfield)

No external specs beyond the above — all requirements are captured in decisions and CLAUDE.md.

</canonical_refs>

<code_context>
## Existing Code Insights

### Reusable Assets
- Projeto greenfield — sem código existente. Todos os arquivos criados do zero.

### Established Patterns (definidos na Phase 1 para fases seguintes)
- Todos os controllers herdam de `BaseController` (a definir) e usam `View::render()`
- Todas as queries passam por `BaseModel::query()` — nunca interpolação de variáveis em SQL
- Todos os templates usam `$e($var)` para output — nunca `echo $var` direto
- Formulários POST incluem `<?= \App\Middleware\CsrfMiddleware::token() ?>` como hidden input

### Integration Points
- `public/index.php` (Plan 03) conecta autoloader → Session::start() → Router::dispatch()
- Controllers conectam AuthMiddleware + CsrfMiddleware via Router
- Views conectam View::render() + layout base

</code_context>

<deferred>
## Deferred Ideas

- Customização de cores do tema Bootstrap — Phase 5 (admin configurações)
- Funcionalidades reais das páginas stub do admin (Presentes, Reservas, Pix, Configurações) — Phases 2-5

</deferred>

---

*Phase: 01-base-infraestrutura*
*Context gathered: 2026-04-22*
