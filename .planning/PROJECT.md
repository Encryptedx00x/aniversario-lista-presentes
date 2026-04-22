# Sistema de Aniversário — Lista de Presentes

## What This Is

Site de aniversário completo com lista de presentes e contribuição via Pix. Convidados visualizam presentes, fazem cadastro/login rápido em modal e reservam itens. O aniversariante gerencia tudo via painel administrativo. Deploy simples em hospedagem compartilhada Hostinger — PHP + MySQL puro, sem Node.js, sem build step em produção.

## Core Value

**Um convidado consegue escolher um presente e registrar a reserva em menos de 2 minutos, sem fricção, e o estoque é baixado com segurança transacional.**

## Context

- **Para:** Aniversariante + convidados
- **Plataforma alvo:** Hostinger shared hosting (PHP 8.1+, MySQL/MariaDB, cPanel)
- **Stack obrigatória:** PHP 8.1+, MySQL, PDO, HTML/CSS/JS, Bootstrap 5 via CDN
- **Sem:** Node.js em produção, React, TypeScript, Docker, Redis, WebSocket, npm build

## Why It Needs to Exist

O aniversariante quer uma forma organizada, bonita e digital de gerenciar lista de presentes — evitando duplicatas, permitindo contribuição via Pix e tendo controle total pelo painel admin.

## Who It's For

- **Convidados:** Pessoas não-técnicas que acessam pelo celular para escolher um presente
- **Aniversariante (admin):** Gerencia presentes, reservas, contribuições e configurações do site

## Requirements

### Validated

(Nenhum ainda — greenfield)

### Active

**Área Pública:**
- [ ] Convidado pode visualizar lista de presentes com imagem, nome, descrição, preço, status
- [ ] Convidado pode filtrar presentes por categoria e ordenar por disponibilidade/preço
- [ ] Convidado pode buscar presentes por nome
- [ ] Convidado pode clicar "Escolher este presente" e ver modal de login/cadastro
- [ ] Convidado pode se cadastrar com nome, e-mail, telefone, senha no modal
- [ ] Convidado pode fazer login no modal
- [ ] Após autenticar, a reserva é concluída automaticamente sem repetir fluxo
- [ ] Convidado pode ver página "Meus Presentes" com suas reservas
- [ ] Convidado pode visualizar chave Pix, QR Code e código copia-e-cola
- [ ] Convidado pode registrar contribuição Pix com nome, e-mail, valor e mensagem
- [ ] Hero da home exibe nome do aniversariante, boas-vindas e explicação

**Lógica de Presentes:**
- [ ] Presente tipo `single`: ao reservar, fica indisponível (qty=1)
- [ ] Presente tipo `multi`: ao reservar, decrementa available_quantity
- [ ] Estoque nunca fica negativo
- [ ] Baixa de estoque é atômica com transação + SELECT FOR UPDATE
- [ ] Duas reservas simultâneas da última unidade são bloqueadas

**Painel Admin:**
- [ ] Admin pode fazer login com credenciais seguras
- [ ] Dashboard exibe totais: presentes, disponíveis, esgotados, reservas, contribuições Pix
- [ ] Admin pode criar, editar, excluir, ativar/desativar e destacar presentes
- [ ] Admin pode fazer upload seguro de imagem para cada presente
- [ ] Admin pode ajustar estoque manualmente
- [ ] Admin pode ver e alterar status de reservas (reserved, confirmed, cancelled)
- [ ] Cancelamento de reserva devolve estoque automaticamente
- [ ] Admin pode ver e alterar status de contribuições Pix (pending, sent, confirmed, cancelled)
- [ ] Admin pode configurar: nome, hero, Pix (chave, recebedor, cidade, QR), cores do tema

**Segurança:**
- [ ] PDO com prepared statements em todas as queries
- [ ] CSRF token em todos os formulários
- [ ] Escape de output (XSS prevention)
- [ ] password_hash / password_verify
- [ ] Validação backend completa
- [ ] Upload seguro (validação de tipo/extensão/tamanho)
- [ ] Rotas admin protegidas por role

**Deploy:**
- [ ] Schema SQL completo pronto para import no cPanel
- [ ] Seed com admin padrão, presentes de exemplo e configurações iniciais
- [ ] README com instruções claras para Hostinger
- [ ] .htaccess configurado para roteamento via front controller

### Out of Scope

- Node.js, React, TypeScript, Docker, Redis, WebSocket — incompatíveis com hospedagem compartilhada
- Pagamento online integrado (gateway) — apenas Pix manual confirmado pelo admin
- Notificações em tempo real — sem WebSocket
- E-mail automático — pode ser adicionado em v2

## Key Decisions

| Decision | Rationale | Outcome |
|----------|-----------|---------|
| PHP 8.1+ puro sem framework | Compatibilidade com Hostinger shared, sem dependências de build | MVC leve custom |
| Bootstrap 5 via CDN | Zero build step, boa UX responsiva | Sem etapa de compilação |
| PDO + prepared statements | Segurança SQL injection | Padrão obrigatório |
| SELECT FOR UPDATE em transações | Atomicidade na baixa de estoque | Evita race condition |
| Sessões PHP nativas | Sem dependência externa de auth | Simples e compatível |
| Front controller (index.php + .htaccess) | Roteamento sem mod_rewrite complexo | URLs limpas |

## Evolution

Este documento evolui a cada fase executada.

**Após cada fase:** Atualizar Requirements (Validated), Key Decisions e Context conforme necessário.

---
*Last updated: 2026-04-21 após inicialização*
