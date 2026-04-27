# Requirements — Sistema de Aniversário

## v1 Requirements

### AUTH — Autenticação

- [ ] **AUTH-01**: Convidado pode se cadastrar com nome, e-mail, telefone e senha via modal
- [ ] **AUTH-02**: Convidado pode fazer login com e-mail e senha via modal
- [ ] **AUTH-03**: Após cadastro, autenticação acontece automaticamente
- [ ] **AUTH-04**: Admin pode fazer login com e-mail e senha em página protegida
- [ ] **AUTH-05**: Rotas do painel admin são protegidas por verificação de role
- [ ] **AUTH-06**: Convidado pode fazer logout

### GIFT — Presentes

- [ ] **GIFT-01**: Convidado pode visualizar lista de presentes com imagem, nome, descrição, preço sugerido, status
- [ ] **GIFT-02**: Convidado pode filtrar presentes por categoria
- [ ] **GIFT-03**: Convidado pode buscar presentes por nome
- [ ] **GIFT-04**: Convidado pode ordenar presentes por disponibilidade e preço
- [ ] **GIFT-05**: Status visual do presente reflete disponível / esgotado / comprado
- [ ] **GIFT-06**: Botão "Escolher este presente" abre modal de auth/reserva
- [ ] **GIFT-07**: Admin pode criar presente com todos os campos (nome, slug, descrição, imagem, categoria, preço, quantidade, tipo, ativo, destaque)
- [ ] **GIFT-08**: Admin pode editar presente existente
- [ ] **GIFT-09**: Admin pode excluir presente (com confirmação)
- [ ] **GIFT-10**: Admin pode ativar/desativar e destacar presentes
- [ ] **GIFT-11**: Admin pode fazer upload seguro de imagem (validação tipo/extensão/tamanho)
- [ ] **GIFT-12**: Admin pode ajustar estoque manualmente

### STOCK — Controle de Estoque

- [ ] **STOCK-01**: Presente tipo `single` fica indisponível após uma reserva confirmada
- [ ] **STOCK-02**: Presente tipo `multi` decrementa `available_quantity` a cada reserva
- [ ] **STOCK-03**: `available_quantity` nunca fica negativo
- [ ] **STOCK-04**: Baixa de estoque usa transação com SELECT FOR UPDATE (atomicidade)
- [ ] **STOCK-05**: Duas reservas simultâneas da última unidade são bloqueadas pela transação

### RESERVE — Reservas

- [ ] **RESERVE-01**: Após autenticar no modal, reserva é concluída automaticamente
- [ ] **RESERVE-02**: Reserva é registrada com user_id, gift_id, quantity, status=reserved
- [ ] **RESERVE-03**: Convidado autenticado pode ver "Meus Presentes" com suas reservas
- [ ] **RESERVE-04**: Admin pode ver todas as reservas com usuário, presente, quantidade, data, status
- [ ] **RESERVE-05**: Admin pode alterar status de reserva (reserved → confirmed / cancelled)
- [ ] **RESERVE-06**: Cancelamento de reserva devolve estoque automaticamente

### PIX — Contribuição Pix

- [ ] **PIX-01**: Página Pix exibe chave, QR Code, código copia-e-cola configurados no admin
- [ ] **PIX-02**: Convidado pode registrar contribuição com nome, e-mail, telefone, valor e mensagem
- [ ] **PIX-03**: Contribuição é salva com status=pending
- [ ] **PIX-04**: Admin pode ver todas as contribuições com dados completos
- [ ] **PIX-05**: Admin pode alterar status de contribuição (pending → sent → confirmed / cancelled)

### ADMIN — Painel Administrativo

- [ ] **ADMIN-01**: Dashboard exibe totais: presentes, disponíveis, esgotados, reservas, contribuições Pix, últimos registros
- [ ] **ADMIN-02**: Admin pode configurar nome do aniversariante, título/subtítulo hero, mensagem de boas-vindas
- [ ] **ADMIN-03**: Admin pode configurar chave Pix, nome do recebedor, cidade, QR Code
- [ ] **ADMIN-04**: Admin pode configurar cores primária e secundária do tema

### UI — Refatoração Visual Moderna (branco/preto/roxo)

- [ ] **UI-01**: Paleta global aplica branco, preto e roxo como identidade visual principal
- [ ] **UI-02**: Componentes (cards, botões, navbar, modal, formulários) seguem padrão visual elegante e consistente
- [ ] **UI-03**: Animações suaves de interação (hover/focus/entrada) melhoram percepção de qualidade
- [ ] **UI-04**: Layout responsivo e espaçamento tipográfico refinados para mobile e desktop
- [ ] **UI-05**: Sistema respeita acessibilidade visual e `prefers-reduced-motion`

### SEC — Segurança

- [ ] **SEC-01**: Todas as queries usam PDO com prepared statements
- [ ] **SEC-02**: Todos os formulários têm CSRF token validado no backend
- [ ] **SEC-03**: Toda saída para HTML usa escape (htmlspecialchars)
- [ ] **SEC-04**: Senhas armazenadas com password_hash e verificadas com password_verify
- [ ] **SEC-05**: Upload valida tipo MIME, extensão e tamanho máximo
- [ ] **SEC-06**: Validação backend completa em todos os endpoints
- [ ] **SEC-07**: Erros internos não são expostos ao usuário final

### DEPLOY — Entrega e Deploy

- [ ] **DEPLOY-01**: Schema SQL completo em `/sql/schema.sql` para import no cPanel
- [ ] **DEPLOY-02**: Seed SQL em `/sql/seed.sql` com admin padrão, presentes exemplo e configurações
- [ ] **DEPLOY-03**: `config.example.php` com placeholders de credenciais
- [ ] **DEPLOY-04**: `.htaccess` configurado para roteamento via front controller
- [ ] **DEPLOY-05**: README com instruções passo a passo para Hostinger

---

## v2 Requirements (Deferred)

- E-mail automático de confirmação de reserva
- Notificação WhatsApp via API
- Galeria de fotos do aniversário
- Contador regressivo para a data
- Lista de confirmação de presença (RSVP)
- Exportação de relatórios em CSV

---

## Out of Scope

- Node.js, React, TypeScript, Docker, Redis, WebSocket — arquitetura incompatível
- Gateway de pagamento online — apenas Pix manual
- Notificações em tempo real — sem infraestrutura de WebSocket
- Multi-tenancy — sistema para um único evento

---

## Traceability

| Phase | Requirements Covered |
|-------|---------------------|
| Phase 1 | SEC-01..07, DEPLOY-04, DEPLOY-03 — Base, schema, config, roteamento, auth |
| Phase 2 | AUTH-01..06, GIFT-01..06, GIFT-07..12 — Páginas públicas + CRUD admin presentes |
| Phase 3 | STOCK-01..05, RESERVE-01..06 — Reserva atômica + "Meus Presentes" |
| Phase 4 | PIX-01..05 — Fluxo Pix completo |
| Phase 5 | ADMIN-01..04 — Dashboard + configurações |
| Phase 6 | DEPLOY-01..05 — Seed, README, refinamento visual |
| Phase 7 | UI-01..05 — Refatoração visual moderna (branco/preto/roxo) |
