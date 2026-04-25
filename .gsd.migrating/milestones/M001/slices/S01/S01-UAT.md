# S01: Base Infraestrutura — UAT

**Milestone:** M001
**Written:** 2026-04-24T23:57:16.465Z

# UAT — S01 Base Infraestrutura

## Preconditions
1. Repositório na branch com artefatos S01.
2. Ambiente com PHP 8.1+ e MySQL disponível para execução end-to-end.
3. Document root apontando para `public/`.
4. Banco vazio (ou schema resetado) para importar `sql/schema.sql`.

---

## Caso 1 — Front controller e roteamento básico
**Objetivo:** garantir que o ciclo HTTP inicia em `public/index.php` e despacha pela tabela de rotas.

**Passos**
1. Iniciar servidor PHP local: `php -S localhost:8000 -t public`.
2. Abrir `http://localhost:8000/`.
3. Abrir `http://localhost:8000/admin/login`.
4. Abrir URL inexistente, ex.: `http://localhost:8000/nao-existe`.

**Esperado**
- Passo 2: página pública renderiza layout principal.
- Passo 3: tela de login admin renderiza layout `admin_bare`.
- Passo 4: página 404 com botões “Ir para home” e “Ver presentes”.

---

## Caso 2 — Proteção de rota admin por role
**Objetivo:** validar enforcement de `AuthMiddleware`.

**Passos**
1. Sem sessão autenticada, acessar `http://localhost:8000/admin/dashboard`.
2. Observar resposta.

**Esperado**
- Redirecionamento para `/admin/login`.
- Dashboard não deve ser exibido para usuário não-admin.

---

## Caso 3 — CSRF obrigatório em POST admin
**Objetivo:** validar contrato CSRF no login/logout.

**Passos**
1. Abrir `/admin/login` e confirmar campo hidden `csrf_token` no formulário.
2. Submeter POST de login sem `csrf_token` (via devtools/curl).
3. Em sessão autenticada admin, localizar formulário de logout e confirmar hidden `csrf_token`.

**Esperado**
- Passo 1/3: token presente nos formulários.
- Passo 2: resposta 403 com página de acesso negado (sem stack trace).

---

## Caso 4 — Login admin com sessão segura
**Objetivo:** validar fluxo de autenticação base e hardening de sessão.

**Passos**
1. Importar `sql/schema.sql` e inserir usuário admin de teste com `password_hash` válido.
2. Acessar `/admin/login` com credenciais corretas.
3. Confirmar redirecionamento para `/admin/dashboard`.
4. Repetir com senha inválida.

**Esperado**
- Credenciais válidas: autentica e abre dashboard.
- Credenciais inválidas: mensagem genérica “Credenciais inválidas.” (sem revelar campo incorreto).
- Sessão regenerada no login (validável por mudança de session id).

---

## Caso 5 — Integridade do schema para próximas fases
**Objetivo:** confirmar que schema base está completo e idempotente.

**Passos**
1. Executar `sql/schema.sql` em banco vazio.
2. Executar novamente o mesmo script.
3. Verificar existência das tabelas: `users`, `gifts`, `reservations`, `contributions`, `settings`.

**Esperado**
- Script executa sem erro em ambas as execuções.
- Todas as 5 tabelas existem com engine InnoDB e charset/collation utf8mb4.

---

## Edge Cases
1. **POST sem CSRF token:** deve falhar com 403 consistentemente.
2. **Acesso direto a rota admin sem sessão:** deve sempre redirecionar para login.
3. **URL inexistente:** deve renderizar 404 amigável sem erro técnico exposto.
4. **Reimport de schema:** não deve quebrar (uso de `IF NOT EXISTS`).
