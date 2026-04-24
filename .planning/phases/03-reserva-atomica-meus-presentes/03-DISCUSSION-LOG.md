# Phase 3: Reserva Atômica & Meus Presentes - Discussion Log

> **Audit trail only.** Do not use as input to planning, research, or execution agents.
> Decisions are captured in CONTEXT.md — this log preserves the alternatives considered.

**Date:** 2026-04-24
**Phase:** 03-reserva-atomica-meus-presentes
**Areas discussed:** Feedback visual da reserva, Quantidade em presentes multi, Página Meus Presentes, Erro de concorrência / esgotamento

---

## Feedback visual da reserva

| Option | Description | Selected |
|--------|-------------|----------|
| Toast/alert inline | Exibir toast na página de presentes sem redirect | ✓ |
| Redirect para Meus Presentes | Redirecionar automaticamente após reservar | |
| Banner no card | Mensagem inline no próprio card do presente | |

**User's choice:** Toast de sucesso na própria página, sem redirect automático. Card atualiza visualmente (quantidade decrementada ou esgotado). Toast inclui botão/link opcional "Ver meus presentes".

---

## Quantidade em presentes multi

| Option | Description | Selected |
|--------|-------------|----------|
| Sempre qty=1 | Cada clique reserva 1 unidade, sem seletor | ✓ |
| Seletor de quantidade | Input numérico para escolher quantas unidades reservar | |

**User's choice:** Sempre qty=1 por ação de reserva. Sem seletor de quantidade nesta fase — reduz complexidade e evita confusão mesmo para presentes tipo `multi`.

---

## Página Meus Presentes

| Option | Description | Selected |
|--------|-------------|----------|
| Página completa /my-gifts | Lista de reservas com imagem, nome, categoria, status, data, cancelamento | ✓ |
| Página simples sem ações | Apenas listagem, sem cancelamento | |

**User's choice:** Página `/my-gifts` com imagem, nome, categoria, status, data da reserva e botão "Cancelar reserva" (visível apenas se status=reserved). Aparecer no menu de navegação quando usuário logado. Estado vazio amigável com link para lista de presentes.

**Notes:** Cancelamento devolve estoque (transacional).

---

## Erro de concorrência / esgotamento

| Option | Description | Selected |
|--------|-------------|----------|
| Mensagem clara + update do card | "Esse presente acabou de ficar indisponível." + card esgotado | ✓ |
| Reload de página | Recarregar para refletir estado atual | |
| Reserva parcial | Registrar tentativa de reserva como "waitlist" | |

**User's choice:** Mensagem inline clara, atualização do card para esgotado/desabilitado sem reload. Nenhuma reserva parcial criada. Backend usa transação com SELECT FOR UPDATE — rollback se `available_qty = 0` na hora do UPDATE.

---

## Claude's Discretion

- Estilo exato do toast de sucesso (Bootstrap Toast ou alert fixo)
- Animação de atualização do card
- Estrutura JSON do response `/reserve`
- Middleware de autenticação para `/my-gifts`

## Deferred Ideas

- Seletor de quantidade para presentes multi — descartado para Phase 3
- Cancelamento admin de reservas — Phase 5
- E-mail de confirmação — v2
