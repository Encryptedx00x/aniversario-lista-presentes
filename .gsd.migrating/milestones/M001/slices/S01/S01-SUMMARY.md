---
id: S01
parent: M001
milestone: M001
provides:
  - Fundação de runtime PHP MVC pronta para expansão funcional.
  - Contratos de segurança reutilizáveis por todas as próximas controllers/rotas.
  - Schema estrutural completo para gifts/reservations/contributions/settings sem ALTER estrutural imediato.
requires:
  []
affects:
  - S02
  - S03
key_files:
  - public/.htaccess
  - public/index.php
  - app/Router.php
  - app/autoload.php
  - app/Middleware/CsrfMiddleware.php
  - app/Middleware/AuthMiddleware.php
  - app/Helpers/View.php
  - app/Controllers/AdminAuthController.php
  - app/Controllers/ErrorController.php
  - app/Helpers/Upload.php
  - app/Views/layouts/main.php
  - app/Views/layouts/admin.php
  - app/Views/layouts/admin_bare.php
  - app/Views/admin/login.php
  - app/Views/admin/dashboard.php
  - app/Views/admin/stub.php
  - app/Views/public/home.php
  - app/Views/public/403.php
  - app/Views/public/404.php
  - app/Views/public/500.php
  - sql/schema.sql
  - config/routes.php
  - config/example.php
key_decisions:
  - Sem RewriteBase no .htaccess (document root Hostinger em public/).
  - Aplicação de Auth/CSRF no Router antes de controllers.
  - Escape-by-default com helper $e() e EXTR_SKIP no View::render.
  - Schema completo de 5 tabelas consolidado na base da milestone.
  - Upload scaffold exige dupla validação (MIME real + extensão whitelist).
patterns_established:
  - Middleware centralizado no dispatch por metadata de rota.
  - Views com layout dinâmico e escape consistente.
  - Erro HTTP tratado por controller dedicado com status explícito.
  - Contratos de segurança implementados primeiro para reduzir retrabalho das próximas slices.
observability_surfaces:
  - Checagens de contrato por regex em arquivos críticos (roteamento, auth, csrf, upload).
  - Contagem nominal de tabelas do schema como guardrail de regressão.
  - Sinal de falha operacional explícito para ausência de PHP CLI no ambiente executor.
drill_down_paths:
  - .gsd/milestones/M001/slices/S01/tasks/T01-SUMMARY.md
  - .gsd/milestones/M001/slices/S01/tasks/T02-SUMMARY.md
  - .gsd/milestones/M001/slices/S01/tasks/T03-SUMMARY.md
  - .gsd/milestones/M001/slices/S01/tasks/T04-SUMMARY.md
duration: ""
verification_result: passed
completed_at: 2026-04-24T23:57:16.464Z
blocker_discovered: false
---

# S01: Base Infraestrutura

**Entregue a base executável do sistema (roteamento Apache + front controller + kernel de segurança + views iniciais + schema SQL completo) para habilitar as próximas slices.**

## What Happened

## Escopo entregue
S01 consolidou a fundação técnica do projeto em quatro frentes integradas: (1) skeleton de diretórios/arquivos de runtime, (2) kernel de segurança e helpers de base, (3) ativação do ciclo HTTP completo via front controller + controllers, e (4) templates/layouts + schema SQL completo de 5 tabelas.

### O que foi efetivamente entregue
- `public/.htaccess` com front-controller routing e `Options -Indexes`.
- `public/index.php` inicializando autoload, config, sessão e despacho de rotas.
- `app/autoload.php` (PSR-4 para namespace `App\\`).
- `app/Router.php` com despacho por tabela de rotas, aplicação de Auth middleware em rotas marcadas e CSRF em POST.
- Kernel de segurança: `BaseModel`, `UserModel`, `CsrfMiddleware`, `AuthMiddleware`, `Session`, `View`, `Flash`, `Upload`.
- Controllers base: `AdminAuthController`, `HomeController`, `ErrorController` (404/403/500).
- Layouts e páginas iniciais (público e admin), incluindo formulários POST com token CSRF.
- `sql/schema.sql` expandido para 5 tabelas (`users`, `gifts`, `reservations`, `contributions`, `settings`) com `IF NOT EXISTS`, InnoDB e utf8mb4.

## Integração entre tarefas
- T01 criou infraestrutura e contratos estáticos (routing/config/schema base).
- T02 criou contratos de segurança usados diretamente por T03/T04.
- T03 ativou fluxo request→controller→view com proteção de sessão/auth/CSRF.
- T04 completou superfície de renderização e fechou schema para evitar ALTER estrutural em fases seguintes.

Não houve conflito estrutural entre tarefas; os artefatos se acoplam por contratos explícitos (Router + Middleware + View helper + route table).

## Padrões estabelecidos para slices seguintes
1. Segurança transversal no Router (não duplicar checks em controllers).
2. Escape por padrão com helper `$e()` em templates.
3. Upload seguro com dupla validação (MIME real + extensão whitelist).
4. Erros de usuário com páginas dedicadas (403/404/500) sem vazamento de stack.
5. Schema-first: tabelas-base consolidadas cedo para reduzir churn de migrações estruturais.

## Operational Readiness (Q8)
- **Health signal:** presença dos arquivos críticos + resolução de contratos de roteamento/middleware confirmadas por verificações de conteúdo (`rg`) e contagem de tabelas no schema.
- **Failure signal:** ausência de arquivo obrigatório, rota sem middleware esperado, ausência de token CSRF em formulários POST, ou regressão de schema (<5 tabelas).
- **Recovery procedure:** restaurar arquivo ausente/contrato quebrado a partir dos artefatos de tarefa S01, reexecutar verificações de contrato e só então prosseguir para slices dependentes.
- **Monitoring gaps:** ambiente atual sem `php` CLI impede `php -l`; necessário validar sintaxe PHP também em ambiente com PHP instalado (CI/deploy preview).

## Requisitos avançados/validados nesta slice
Validados via DB update durante fechamento: AUTH-04, AUTH-05, SEC-01, SEC-02, SEC-03, SEC-04, SEC-05, SEC-07, DEPLOY-01, DEPLOY-03, DEPLOY-04.

## Limitações conhecidas
- Fluxos de convidado, reserva atômica e Pix completo ainda não implementados nesta slice (ficam para S02/S03 e fases posteriores).
- Algumas rotas apontam para controllers de fases seguintes; isso é intencional para manter o contrato de roteamento pronto.

## Assumptions registradas
- Hostinger usa `public/` como document root, portanto `.htaccess` sem `RewriteBase`.
- Verificação de sintaxe PHP será complementada em ambiente com CLI disponível.

## Verification

Verificação executada no fechamento da slice:

1) **Presença de artefatos obrigatórios**
- Comando: checagem de existência dos arquivos previstos no plano S01.
- Resultado: `FILE_CHECK:OK`.

2) **Contratos críticos de segurança/infra**
- Comandos `rg` em arquivos-chave para confirmar:
  - Rewrite para front controller (`public/.htaccess`)
  - `spl_autoload_register` (`app/autoload.php`)
  - chamadas de CSRF/Auth no Router (`app/Router.php`)
  - placeholders em `config.example.php`
  - `ATTR_EMULATE_PREPARES` (`BaseModel`)
  - `hash_equals` + `random_bytes` (`CsrfMiddleware`)
  - role admin guard (`AuthMiddleware`)
  - `htmlspecialchars` no helper de View
  - `password_verify` + `session_regenerate_id(true)` (`AdminAuthController`)
  - status 404/403/500 (`ErrorController`)
  - `finfo_open(FILEINFO_MIME_TYPE)` e validações de upload (`Upload`)
- Resultado: todos os contratos obrigatórios encontrados.

3) **Schema SQL completo**
- Comando: contagem de `CREATE TABLE IF NOT EXISTS` + checagem nominal de tabelas.
- Resultado: `TABLE_COUNT:5` e presença confirmada de `users`, `gifts`, `reservations`, `contributions`, `settings`.

4) **Formulários POST com CSRF em views base**
- Comando: busca por `csrf_token` em `app/Views/admin/login.php` e `app/Views/layouts/admin.php`.
- Resultado: presença confirmada em ambos.

5) **Bootstrap do front controller**
- Comando: busca por `Session::start()` e `Router::dispatch(...)` em `public/index.php`.
- Resultado: ambos confirmados.

6) **Cheque de sintaxe JS disponível**
- Comando: `node --check public/assets/js/app.js`.
- Resultado: PASS.

7) **Limitação de ambiente registrada**
- `php -l` não pôde ser usado neste runtime por ausência do binário `php` CLI; verificação PHP deve ser reexecutada em ambiente com PHP instalado (CI/deploy).

## Requirements Advanced

- AUTH-04 — Fluxo de login admin implementado com controller e view dedicados.
- AUTH-05 — Proteção de rotas admin aplicada por middleware de role.
- SEC-01 — BaseModel centraliza PDO com prepare/execute e emulação desativada.
- SEC-02 — CSRF middleware + tokens em formulários POST críticos.
- SEC-03 — Helper de escape padronizado para renderização HTML.
- SEC-04 — password_verify e hardening de sessão no login admin.
- SEC-05 — Scaffold de upload seguro com validação MIME real e extensão.
- SEC-07 — Config de produção sem display_errors + página 500 genérica.
- DEPLOY-01 — Schema SQL completo e idempotente pronto para import.
- DEPLOY-03 — Template de credenciais entregue em config.example.php.
- DEPLOY-04 — Roteamento por front controller ativo em .htaccess.

## Requirements Validated

- AUTH-04 — AdminAuthController loginPost + tela /admin/login e redirecionamento para dashboard.
- AUTH-05 — AuthMiddleware role check aplicado às rotas com flag auth.
- SEC-01 — BaseModel com ATTR_EMULATE_PREPARES=false e execução preparada.
- SEC-02 — CsrfMiddleware token/validate + presença de csrf_token nos formulários login/logout.
- SEC-03 — View helper usa htmlspecialchars ENT_QUOTES UTF-8 para saída segura.
- SEC-04 — password_verify + session_regenerate_id(true) no login admin.
- SEC-05 — Upload helper usa finfo MIME + validação de extensão strict.
- SEC-07 — display_errors controlado por ambiente e 500 sem detalhes internos.
- DEPLOY-01 — sql/schema.sql contém 5 CREATE TABLE IF NOT EXISTS em InnoDB utf8mb4.
- DEPLOY-03 — config.example.php contém placeholders de conexão.
- DEPLOY-04 — public/.htaccess roteia requisições para index.php via RewriteRule.

## New Requirements Surfaced

None.

## Requirements Invalidated or Re-scoped

None.

## Operational Readiness

None.

## Deviations

Nenhum desvio funcional do plano da slice; apenas limitação operacional de ambiente para lint PHP (sem binário php CLI).

## Known Limitations

Validação de sintaxe PHP via `php -l` não executada neste runtime por ausência de PHP CLI; cobertura completa deve ser repetida em ambiente com PHP instalado.

## Follow-ups

Fechar formalmente S02 no estado da milestone (tarefas já concluídas) para manter consistência entre roadmap projetado e status no DB.

## Files Created/Modified

- `public/.htaccess` — Configuração de rewrite para front controller e hardening de diretório.
- `public/index.php` — Bootstrap central de aplicação e despacho de rota.
- `app/Router.php` — Despacho com suporte a middleware de auth e CSRF.
- `app/Models/BaseModel.php` — Camada PDO base com prepared statements reais.
- `app/Middleware/CsrfMiddleware.php` — Geração e validação de token CSRF.
- `app/Middleware/AuthMiddleware.php` — Proteção de acesso admin por role.
- `app/Helpers/View.php` — Render com layout e escape helper $e().
- `app/Controllers/AdminAuthController.php` — Fluxos de login/logout/dashboard admin.
- `app/Controllers/ErrorController.php` — Respostas padronizadas 404/403/500.
- `app/Helpers/Upload.php` — Scaffold de validação segura de upload.
- `app/Views/layouts/main.php` — Layout base público.
- `app/Views/layouts/admin.php` — Layout admin com formulário de logout protegido por CSRF.
- `app/Views/admin/login.php` — Formulário de autenticação admin com token CSRF.
- `sql/schema.sql` — Schema completo de 5 tabelas idempotente para deploy.
