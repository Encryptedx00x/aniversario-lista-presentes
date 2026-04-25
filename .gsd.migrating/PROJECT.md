# Sistema de Aniversário — Lista de Presentes

## What This Is

Site de aniversário com lista de presentes e contribuição via Pix. Convidados escolhem presentes; o aniversariante gerencia tudo no painel admin. Deploy alvo: Hostinger shared hosting (PHP + MySQL), sem build step em produção.

## Core Value

**Permitir que um convidado reserve um presente rapidamente, com segurança de sessão/CSRF e base de dados preparada para evolução das próximas fases.**

## Context

- **Plataforma alvo:** Hostinger shared hosting (PHP 8.1+, MySQL/MariaDB, cPanel)
- **Stack obrigatória:** PHP 8.1+, MySQL, PDO, Bootstrap 5 via CDN
- **Sem:** Node.js em produção, React, TypeScript, Docker, Redis, WebSocket

## Current Milestone State

- **Milestone ativa:** M001 — Migration
- **Slice S01 (Base Infraestrutura):** implementação concluída (infra, kernel de segurança, front controller, controllers base, views base, schema completo)
- **Slice S02:** tarefas concluídas; fechamento formal pendente no estado da milestone
- **Slice S03:** ainda não iniciada

## Requirements

### Validated

- [x] AUTH-04 — Admin pode fazer login com e-mail e senha em página protegida
- [x] AUTH-05 — Rotas do painel admin são protegidas por verificação de role
- [x] SEC-01 — Todas as queries usam PDO com prepared statements
- [x] SEC-02 — Todos os formulários têm CSRF token validado no backend
- [x] SEC-03 — Toda saída para HTML usa escape (htmlspecialchars)
- [x] SEC-04 — Senhas armazenadas com password_hash e verificadas com password_verify
- [x] SEC-05 — Upload valida tipo MIME, extensão e tamanho máximo (scaffold implementado)
- [x] SEC-07 — Erros internos não são expostos ao usuário final
- [x] DEPLOY-01 — Schema SQL completo em `/sql/schema.sql` para import no cPanel
- [x] DEPLOY-03 — `config.example.php` com placeholders de credenciais
- [x] DEPLOY-04 — `.htaccess` configurado para roteamento via front controller

### Active (resumo)

- Fluxos de convidado (cadastro/login modal, reserva automática, meus presentes)
- CRUD completo de presentes no admin
- Reserva atômica com controle de estoque em concorrência
- Fluxo Pix (público + admin)
- Dashboard admin com dados reais
- Seed SQL e README de deploy

## Key Decisions (até agora)

1. **Front controller com mod_rewrite** (`public/.htaccess` → `public/index.php`).
2. **Roteamento com tabela estática + middleware por contrato** (auth/CSRF aplicados antes do controller).
3. **Escape por padrão em views** via helper `$e()` + `EXTR_SKIP`.
4. **Validação de upload com dupla checagem** (MIME real por `finfo` + whitelist de extensão).
5. **Schema completo já na fase inicial** para reduzir churn de migração estrutural nas próximas slices.

## Risks / Gaps

- Ambiente atual pode não ter `php` CLI para `php -l`; validações locais precisam fallback por checagens estáticas + execução em ambiente com PHP instalado.
- Slice S02 aparece como pendente no status de milestone apesar de tarefas finalizadas (fechamento formal ainda necessário via fluxo GSD).

---
*Last updated: 2026-04-24 (auto-mode, fechamento S01)*
