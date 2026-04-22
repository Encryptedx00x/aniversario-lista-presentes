# Phase 2: Presentes Públicos & CRUD Admin - Discussion Log

> **Audit trail only.** Do not use as input to planning, research, or execution agents.
> Decisions are captured in CONTEXT.md — this log preserves the alternatives considered.

**Date:** 2026-04-22
**Phase:** 02-presentes-publicos-crud-admin
**Areas discussed:** Cards de presentes, Filtro/busca/ordenação, Modal de auth do convidado, CRUD admin de presentes

---

## Cards de presentes

| Option | Description | Selected |
|--------|-------------|----------|
| Grid de cards | 3 colunas desktop, 2 tablet, 1 mobile | ✓ |
| Lista com imagem à esquerda | Mais denso, menos visual | |
| Grid maior com destaque | Presentes destacados em espaço duplo | |

**User's choice:** Grid de cards responsivo, elegante
**Notes:** Cada card exibe imagem, nome, descrição curta, categoria, preço sugerido, quantidade disponível, status visual (disponível/esgotado/destacado), botão "Escolher presente". Item esgotado visualmente claro com botão desabilitado. Destaque com selo discreto.

---

## Filtro / busca / ordenação

| Option | Description | Selected |
|--------|-------------|----------|
| Fetch + HTML server-side | JS envia filtros, PHP retorna HTML dos cards | ✓ |
| Fetch retorna JSON | PHP retorna JSON, JS monta cards | |
| Tudo carregado, JS filtra | Sem requisição adicional | |

**User's choice:** Fetch + JS, PHP retorna HTML via endpoint `/gifts/filter`
**Notes:** Busca por nome com debounce (~300ms), filtro por categoria, ordenação por disponíveis primeiro / menor preço / maior preço / destacados primeiro. Sem paginação. Volume pequeno/moderado de uma lista de aniversário.

---

## Modal de auth do convidado

| Option | Description | Selected |
|--------|-------------|----------|
| Duas abas | "Criar conta" / "Entrar" alternadas | ✓ |
| Toggle inline | Formulário único que troca | |
| Ambos visíveis | Cadastro + login na mesma tela | |

**User's choice:** Duas abas — "Criar conta" e "Entrar"
**Notes:** Campos de cadastro: nome, e-mail, telefone, senha. Após autenticação: modal fecha, usuário permanece na página, ação de escolha prossegue automaticamente (gift_id preservado no JS). Feedback visual claro no modal para erro e sucesso.

---

## CRUD admin de presentes

| Option | Description | Selected |
|--------|-------------|----------|
| Upload direto no formulário | Imagem enviada junto com POST, preview antes | ✓ |
| Upload separado | Cria presente, depois anexa imagem | |

**User's choice:** Upload direto com preview, validação MIME + tamanho
**Notes:** Campos: nome, slug automático editável, descrição, categoria, preço sugerido, tipo (single/multi), quantidade total, quantidade disponível, destaque, ativo/inativo, imagem. Inativar ≠ excluir. Listagem admin: tabela com ações rápidas (editar, inativar/ativar, excluir com confirmação), busca e filtro por status.

---

## Claude's Discretion

- Estilo exato do badge "destacado"
- Visual do estado esgotado (overlay, cinza, riscado)
- Geração do slug via JS no frontend
- Estrutura do endpoint `/gifts/filter`
- Placeholder para imagem ausente

## Deferred Ideas

- Reserva automática após autenticação → Phase 3
- Paginação da lista pública → avaliar na Phase 6
- Customização de cores/tema → Phase 5
