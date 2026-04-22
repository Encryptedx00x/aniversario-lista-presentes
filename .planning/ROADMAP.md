# Roadmap — Sistema de Aniversário

**6 fases** | **37 requirements mapeados** | Cobertura v1 100% ✓

---

## Phase Overview

| # | Phase | Goal | Requirements | Success Criteria |
|---|-------|------|--------------|-----------------|
| 1 | Base & Infraestrutura | App PHP roda, rotas funcionam, auth completa, layout base existe | SEC-01..07, DEPLOY-03, DEPLOY-04 | 5 |
| 2 | Presentes Públicos & CRUD Admin | Convidado vê e busca presentes; admin gerencia CRUD com imagens | GIFT-01..12, AUTH-01..06 | 5 |
| 3 | Reserva Atômica & Meus Presentes | Fluxo completo de reserva com segurança transacional | STOCK-01..05, RESERVE-01..06 | 5 |
| 4 | Fluxo Pix | Página Pix funcional + registro de contribuições | PIX-01..05 | 4 |
| 5 | Painel Admin Completo | Dashboard, gestão de reservas/contribuições, configurações | ADMIN-01..04, RESERVE-04..06, PIX-04..05 | 5 |
| 6 | Seed, Deploy & Refinamento | Projeto pronto para Hostinger com seed e documentação | DEPLOY-01..05 | 4 |

---

## Phase Details

### Phase 1: Base & Infraestrutura
**Goal:** Estrutura MVC leve funcionando, banco criado, configuração de ambiente, roteamento via front controller, autenticação por sessão com roles, layout base (header/footer/nav), proteção CSRF e segurança básica.

**Requirements:** SEC-01, SEC-02, SEC-03, SEC-04, SEC-05, SEC-06, SEC-07, DEPLOY-03, DEPLOY-04

**Success Criteria:**
1. `php -S localhost:8000 -t public` sobe o app sem erros
2. Rota `/` retorna a home, rota inválida retorna 404 amigável
3. Admin faz login em `/admin/login` e é redirecionado para dashboard
4. Acesso a `/admin/gifts` sem login redireciona para `/admin/login`
5. Formulário sem CSRF token retorna erro 403

**Directory Structure:**
```
/app
  /Controllers
  /Models
  /Views
    /layouts
    /public
    /admin
  /Middleware
  /Helpers
/config
/public
  /assets/css
  /assets/js
  /assets/img
  /uploads
  index.php
  .htaccess
/sql
/storage
README.md
config.example.php
```

---

### Phase 2: Presentes Públicos & CRUD Admin
**Goal:** Página pública de presentes com filtro, busca e ordenação. CRUD completo de presentes no admin com upload de imagem. Autenticação de convidado (cadastro/login via modal).

**Requirements:** GIFT-01, GIFT-02, GIFT-03, GIFT-04, GIFT-05, GIFT-06, GIFT-07, GIFT-08, GIFT-09, GIFT-10, GIFT-11, GIFT-12, AUTH-01, AUTH-02, AUTH-03, AUTH-04, AUTH-05, AUTH-06

**Plans:** 4 plans

Plans:
- [x] 02-01-PLAN.md — Schema migration, Router dynamic segments, Bootstrap Icons, upload directory security, gift card CSS
- [x] 02-02-PLAN.md — GiftModel data access, AdminGiftController CRUD, admin gift views, admin routes
- [ ] 02-03-PLAN.md — GiftController (index + filter), public gifts page, _cards.php partial, public routes
- [ ] 02-04-PLAN.md — GuestAuthController JSON endpoints, UserModel::create(), app.js filter/modal/slug/preview

**Success Criteria:**
1. `/gifts` exibe cards de presentes com imagem, nome, descrição, preço, badge de status
2. Filtro por categoria, busca por nome e ordenação funcionam sem reload de página (JS + fetch)
3. Clicar "Escolher este presente" abre modal com formulário de cadastro/login
4. Admin em `/admin/gifts` pode criar presente com imagem — imagem aparece na listagem pública
5. Presente inativo não aparece na listagem pública

---

### Phase 3: Reserva Atômica & Meus Presentes
**Goal:** Fluxo completo de reserva: autenticar no modal → reserva concluída automaticamente → estoque baixado com transação + SELECT FOR UPDATE → feedback visual → página "Meus Presentes".

**Requirements:** STOCK-01, STOCK-02, STOCK-03, STOCK-04, STOCK-05, RESERVE-01, RESERVE-02, RESERVE-03

**Success Criteria:**
1. Após cadastro no modal, reserva é criada com status=reserved sem ação extra do usuário
2. `available_quantity` é decrementado; presente `single` fica com status esgotado
3. Teste de concorrência: duas requests simultâneas para o último item — apenas uma reserva criada
4. `available_quantity` nunca fica negativo no banco
5. `/my-gifts` exibe reservas do usuário logado com nome do presente e status

---

### Phase 4: Fluxo Pix
**Goal:** Página Pix exibe chave/QR/copia-e-cola configurados pelo admin. Formulário de registro de contribuição salva com status pending.

**Requirements:** PIX-01, PIX-02, PIX-03

**Success Criteria:**
1. `/pix` exibe chave Pix, QR Code (imagem ou gerado), e botão copia-e-cola funcional
2. Formulário de contribuição valida campos e salva no banco com status=pending
3. Usuário logado tem nome/e-mail pré-preenchidos no formulário
4. Mensagem de sucesso é exibida após envio do formulário

---

### Phase 5: Painel Admin Completo
**Goal:** Dashboard com métricas, gestão completa de reservas (alterar status, cancelar com devolução de estoque), gestão de contribuições Pix, configurações do site e tema.

**Requirements:** ADMIN-01, ADMIN-02, ADMIN-03, ADMIN-04, RESERVE-04, RESERVE-05, RESERVE-06, PIX-04, PIX-05

**Success Criteria:**
1. Dashboard exibe cards com totais corretos: presentes, disponíveis, esgotados, reservas, Pix
2. Admin altera reserva de `reserved` para `cancelled` → estoque é devolvido no banco
3. Admin altera contribuição Pix de `pending` para `confirmed` → status atualizado
4. Admin salva configurações (nome, hero, Pix) → home pública reflete as mudanças
5. Admin altera cor primária do tema → CSS custom property atualiza visual do site

---

### Phase 6: Seed, Deploy & Refinamento
**Goal:** SQL de schema + seed completo, README com instruções Hostinger passo a passo, refinamento visual (UX, mobile, acessibilidade básica), config.example.php, todo o projeto pronto para upload via FTP/cPanel.

**Requirements:** DEPLOY-01, DEPLOY-02, DEPLOY-03, DEPLOY-04, DEPLOY-05

**Success Criteria:**
1. `sql/schema.sql` importado no cPanel cria todas as tabelas sem erros
2. `sql/seed.sql` cria admin padrão, 5+ presentes exemplo e configurações iniciais
3. README tem seção "Deploy na Hostinger" com no mínimo 10 passos claros
4. Site abre corretamente com subdomínio/domínio Hostinger sem erros 404/500

---

## Requirement Coverage Validation

**Total v1 requirements:** 37
**Mapped:** 37
**Coverage:** 100% ✓

| Requirement | Phase |
|-------------|-------|
| SEC-01..07 | 1 |
| DEPLOY-03, DEPLOY-04 | 1 |
| GIFT-01..12 | 2 |
| AUTH-01..06 | 2 |
| STOCK-01..05 | 3 |
| RESERVE-01..03 | 3 |
| PIX-01..03 | 4 |
| ADMIN-01..04 | 5 |
| RESERVE-04..06 | 5 |
| PIX-04..05 | 5 |
| DEPLOY-01..05 | 6 |
